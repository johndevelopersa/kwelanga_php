<?php


use Aws\Exception\AwsException;

class SmartQueueMessage
{
    private $queueURL;
    private $data;

    public function __construct($queueURL, $message)
    {
        $this->queueURL = $queueURL;
        $this->data = $message;
    }

    public function getSmartEvent(): SmartEventTO
    {
        return SmartEventTO::fromJSON($this->data['Body']);
    }

    public function getMessageID(): ?string
    {
        return $this->data['MessageId'] ?? null;
    }

    public function getMessageGroupID(): ?string
    {
        return $this->data['Attributes']['MessageGroupId'] ?? null;
    }

    public function getSequenceNumber(): int
    {
        return (int)$this->data['Attributes']['SequenceNumber'] ?? -1;
    }

    public function getApproxReceiveCount(): int
    {
        return (int)$this->data['Attributes']['ApproximateReceiveCount'] ?? -1;
    }

    public function getSentTimestamp(): ?DateTime
    {
        $timestamp = $this->data['Attributes']['SentTimestamp'] ?? null;
        if ($timestamp) {
            return (new DateTime)->setTimestamp((int)($timestamp / 1000));
        }
        return $timestamp;
    }

    public function getReceiptHandle(): string
    {
        return $this->data['ReceiptHandle'];
    }

    public function getAttr(): array
    {
        return $this->data;
    }

    public function commitMessage(): ErrorTO
    {
        try {
            $result = SmartQueue::getClient()->deleteMessage([
                'QueueUrl' => $this->queueURL,
                'ReceiptHandle' => $this->getReceiptHandle()
            ]);

            return ErrorTO::NewSuccess("successfully committed message as completed")
                ->setObject($result->get("@metadata"));

        } catch (AwsException $e) {
            return ErrorTO::NewError($e->getMessage());
        }
    }

}