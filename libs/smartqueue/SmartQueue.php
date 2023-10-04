<?php

require_once 'ROOT.php';
require_once $ROOT . 'PHPINI.php';
require_once $ROOT . $PHPFOLDER . "libs/aws/aws-autoloader.php";
require_once $ROOT . $PHPFOLDER . "TO/SmartEventTO.php";
require_once $ROOT . $PHPFOLDER . "TO/ErrorTO.php";
require_once $ROOT . $PHPFOLDER . 'properties/Constants.php';

// internal types
require_once __DIR__ . "/internal/SmartQueueMessage.php";
require_once __DIR__ . "/internal/SmartQueueReceiveResponse.php";
require_once __DIR__ . "/QueueName.php";

use Aws\Exception\AwsException;
use Aws\Sqs\SqsClient;

class SmartQueue
{

    public static function getClient($region = DEFAULT_SQS_REGION): SqsClient
    {
        return new SqsClient([
            'region' => $region,
            'version' => 'latest',
            'credentials' => [
                'key' => SQS_ACCESS_ID,
                'secret' => SQS_SECRET_KEY,
            ],
        ]);
    }

    public static function Publish(string $queueName, SmartEventTO $event, $groupID = null): ErrorTO
    {
        if (!$groupID) {
            $groupID = uniqid('MessageGroupId_');
        }
        $deduplicationId = uniqid('MessageDeduplicationId_') . '_' . md5(time() * rand(0, 100000));

        try {
            $result = SmartQueue::getClient()->sendMessage([
                'MessageGroupId' => $groupID,
                'MessageDeduplicationId' => $deduplicationId,
                'MessageBody' => $event->toJSON(),
                'QueueUrl' => $queueName,
            ]);

            if ($result->hasKey("SequenceNumber") && $result->hasKey("MessageId")) {
                return ErrorTO::NewSuccess(
                    "message successfully queued",
                    $result->get("SequenceNumber"),
                    $result->get("MessageId")
                )->setObject($result->get('@metadata'));
            }
            return ErrorTO::NewError("result from sendMessage is missing critical response keys")
                ->setObject($result);

        } catch (AwsException $e) {
            return ErrorTO::NewError($e->getMessage());
        }
    }

    // $limit Valid values: 1 to 10. Default: 1.
    public static function Receive(string $queueURL, int $limit = 1, int $visibilityTimeout = null, int $waitTimeout = null): SmartQueueReceiveResponse
    {
        if ($limit > 10) {
            return (new SmartQueueReceiveResponse)->setError(ErrorTO::NewError("maximum limit is 10 messages"));
        }

        $params = [
            'AttributeNames' => ['All'],
            'MaxNumberOfMessages' => $limit,
            'MessageAttributeNames' => ['All'],
            'QueueUrl' => $queueURL
        ];
        if ($visibilityTimeout !== null) {
            $params['VisibilityTimeout'] = $visibilityTimeout;
        }
        if ($waitTimeout !== null) {
            $params['WaitTimeSeconds'] = $waitTimeout;
        }

        try {
            $result = SmartQueue::getClient()->receiveMessage($params);
            return new SmartQueueReceiveResponse($queueURL, $result);

        } catch (AwsException $e) {
            $err = ErrorTO::NewError($e->getMessage());
            return (new SmartQueueReceiveResponse)->setError($err);
        }

    }

    //TODO: create batching send and receive methods
    //ref: https://docs.aws.amazon.com/aws-sdk-php/v3/api/api-sqs-2012-11-05.html#sendmessagebatch
    /*
     * $result = $client->sendMessageBatch([
    'Entries' => [ // REQUIRED
        [
            'DelaySeconds' => <integer>,
            'Id' => '<string>', // REQUIRED
            'MessageAttributes' => [
                '<String>' => [
                    'BinaryListValues' => [<string || resource || Psr\Http\Message\StreamInterface>, ...],
                    'BinaryValue' => <string || resource || Psr\Http\Message\StreamInterface>,
                    'DataType' => '<string>', // REQUIRED
                    'StringListValues' => ['<string>', ...],
                    'StringValue' => '<string>',
                ],
                // ...
            ],
            'MessageBody' => '<string>', // REQUIRED
            'MessageDeduplicationId' => '<string>',
            'MessageGroupId' => '<string>',
            'MessageSystemAttributes' => [
                '<MessageSystemAttributeNameForSends>' => [
                    'BinaryListValues' => [<string || resource || Psr\Http\Message\StreamInterface>, ...],
                    'BinaryValue' => <string || resource || Psr\Http\Message\StreamInterface>,
                    'DataType' => '<string>', // REQUIRED
                    'StringListValues' => ['<string>', ...],
                    'StringValue' => '<string>',
                ],
                // ...
            ],
        ],
        // ...
    ],
    'QueueUrl' => '<string>', // REQUIRED
    ]);
     */


    public static function getQueue(string $queueURL): array
    {
        $result = SmartQueue::getClient()->getQueueAttributes([
            'AttributeNames' => ["All"],
            'QueueUrl' => $queueURL,
        ]);

        return $result->get("Attributes");
    }

    public static function purgeQueue(string $queueURL): array
    {
        $result = SmartQueue::getClient()->purgeQueue([
            'QueueUrl' => $queueURL,
        ]);

        return $result->get("@metadata");
    }

    public static function createQueue(
        string $queueURL,
        int    $delaySeconds = null,
        int    $maximumMessageSize = null,
        int    $messageRetentionPeriod = null,
        int    $visibilityTimeout = null,
        int    $receiveMessageWaitTimeout = null,
        array  $tags = []
    )
    {

        $attr = self::buildAttr($delaySeconds, $maximumMessageSize, $messageRetentionPeriod, $visibilityTimeout, $receiveMessageWaitTimeout);
        $attr['SqsManagedSseEnabled'] = "true";
        $attr['FifoQueue'] = "true";

        try {
            $result = SmartQueue::getClient()->createQueue([
                'Attributes' => $attr,
                'QueueName' => $queueURL, // REQUIRED
                'tags' => $tags,
            ]);
            if (!$result->hasKey("@metadata")) {
                return ErrorTO::NewError("missing metadata response key");
            }

            $statusCode = $result->get("@metadata")['statusCode'] ?? -1;
            if ($statusCode == 200) {
                return ErrorTO::NewSuccess("successfully created smartqueue: $queueURL")
                    ->setObject($result->get("@metadata"));
            }

            return ErrorTO::NewError("missing metadata response key")
                ->setObject($result);

        } catch (AwsException $e) {
            return ErrorTO::NewError($e->getMessage());
        }
    }

    public static function setQueueAttr(
        string $queueURL,
        int    $delaySeconds = null,
        int    $maximumMessageSize = null,
        int    $messageRetentionPeriod = null,
        int    $visibilityTimeout = null,
        int    $receiveMessageWaitTimeout = null
    ): ErrorTO
    {

        $attr = self::buildAttr($delaySeconds, $maximumMessageSize, $messageRetentionPeriod, $visibilityTimeout, $receiveMessageWaitTimeout);

        //validate
        $err = SmartQueue::ValidQueueAttr($attr);
        if ($err->isError()) {
            return $err;
        }

        $result = SmartQueue::getClient()->setQueueAttributes([
            'Attributes' => $attr,
            'QueueUrl' => $queueURL,
        ]);

        $meta = [
            'metadata' => $result->get("@metadata"),
            'new_attributes' => SmartQueue::getQueue($queueURL)
        ];

        return ErrorTO::NewSuccess("attributes successfully updated")->setObject($meta);
    }

    public static function ValidQueueAttr(array $arr): ErrorTO
    {
        //  DelaySeconds
        //  The length of time, in seconds, for which the delivery of all messages in the queue is delayed.
        //  Valid values: An integer from 0 to 900 (15 minutes). Default: 0.
        if (isset($arr['DelaySeconds'])) {
            $value = $arr['DelaySeconds'];
            if ($value < 0 || $value > 900) {
                return ErrorTO::NewError("DelaySeconds can only range from 0 to 900 (15 minutes)");
            }
        }

        //  MaximumMessageSize
        //  The limit of how many bytes a message can contain before Amazon SQS rejects it.
        //  Valid values: An integer from 1,024 bytes (1 KiB) up to 262,144 bytes (256 KiB). Default: 262,144 (256 KiB).
        if (isset($arr['MaximumMessageSize'])) {
            $value = $arr['MaximumMessageSize'];
            if ($value < 0 || $value > 262144) {
                return ErrorTO::NewError("MaximumMessageSize can only range from 1,024 bytes (1 KiB) up to 262,144 bytes (256 KiB)");
            }
        }

        //  MessageRetentionPeriod
        //  The length of time, in seconds, for which Amazon SQS retains a message.
        //  Valid values: An integer representing seconds, from 60 (1 minute) to 1,209,600 (14 days). Default: 345,600 (4 days).
        if (isset($arr['MessageRetentionPeriod'])) {
            $value = $arr['MessageRetentionPeriod'];
            if ($value < 60 || $value > 1209600) {
                return ErrorTO::NewError("MessageRetentionPeriod can only range from 60 (1 minute) to 1,209,600 (14 days)");
            }
        }

        //  ReceiveMessageWaitTimeSeconds
        //  The length of time, in seconds, for which a ReceiveMessage action waits for a message to arrive.
        //  Valid values: An integer from 0 to 20 (seconds). Default: 0.
        if (isset($arr['ReceiveMessageWaitTimeSeconds'])) {
            $value = $arr['ReceiveMessageWaitTimeSeconds'];
            if ($value < 0 || $value > 20) {
                return ErrorTO::NewError("ReceiveMessageWaitTimeSeconds can only range from 0 to 20 (seconds)");
            }
        }

        //  VisibilityTimeout
        //  The visibility timeout for the queue, in seconds.
        //  Valid values: An integer from 0 to 43,200 (12 hours).
        //  Default: 30. For more information about the visibility timeout, see Visibility Timeout in the Amazon SQS Developer Guide.
        if (isset($arr['VisibilityTimeout'])) {
            $value = $arr['VisibilityTimeout'];
            if ($value < 0 || $value > 43200) {
                return ErrorTO::NewError("VisibilityTimeout can only range from 0 to 43,200 (12 hours)");
            }
        }

        return ErrorTO::NewSuccess("valid parameters");
    }

    /**
     * @param int|null $delaySeconds
     * @param int|null $maximumMessageSize
     * @param int|null $messageRetentionPeriod
     * @param int|null $visibilityTimeout
     * @param int|null $receiveMessageWaitTimeout
     * @return array
     */
    public static function buildAttr(?int $delaySeconds, ?int $maximumMessageSize, ?int $messageRetentionPeriod, ?int $visibilityTimeout, ?int $receiveMessageWaitTimeout): array
    {
        $attr = [];
        if (!is_null($delaySeconds)) {
            $attr['DelaySeconds'] = $delaySeconds;
        }
        if (!is_null($maximumMessageSize)) {
            $attr['MaximumMessageSize'] = $maximumMessageSize;
        }
        if (!is_null($messageRetentionPeriod)) {
            $attr['MessageRetentionPeriod'] = $messageRetentionPeriod;
        }
        if (!is_null($visibilityTimeout)) {
            $attr['VisibilityTimeout'] = $visibilityTimeout;
        }
        if (!is_null($receiveMessageWaitTimeout)) {
            $attr['ReceiveMessageWaitTimeSeconds'] = $receiveMessageWaitTimeout;
        }
        return $attr;
    }

}