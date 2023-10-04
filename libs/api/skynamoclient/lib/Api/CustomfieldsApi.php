<?php
/**
 * CustomfieldsApi
 * PHP version 7.2
 *
 * @category Class
 * @package  SkynamoClientAPI
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 */

/**
 * Skynamo Public API
 *
 * The specification for Skynamo's public API <br><br>Helpful links<br> <a href=\"https://support.skynamo.com/hc/en-us/articles/6671335262749-Creating-a-Public-API-Key\" id=\"hint_box\">Creating a Public API Key</a><br> <a href=\"https://support.skynamo.com/hc/en-us/articles/6671463933597-Postman-Examples\" id=\"hint_box\">Postman examples</a><br> <a href=\"https://support.skynamo.com/hc/en-us/articles/6671240071453-How-to-upload-customer-images-using-Postman\" id=\"hint_box\">How to upload customer images</a>
 *
 * The version of the OpenAPI document: 1.0.18
 * Generated by: https://openapi-generator.tech
 * OpenAPI Generator version: 5.1.1
 */

/**
 * NOTE: This class is auto generated by OpenAPI Generator (https://openapi-generator.tech).
 * https://openapi-generator.tech
 * Do not edit the class manually.
 */

namespace SkynamoClientAPI\Api;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\MultipartStream;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\RequestOptions;
use SkynamoClientAPI\ApiException;
use SkynamoClientAPI\Configuration;
use SkynamoClientAPI\HeaderSelector;
use SkynamoClientAPI\ObjectSerializer;

/**
 * CustomfieldsApi Class Doc Comment
 *
 * @category Class
 * @package  SkynamoClientAPI
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 */
class CustomfieldsApi
{
    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * @var Configuration
     */
    protected $config;

    /**
     * @var HeaderSelector
     */
    protected $headerSelector;

    /**
     * @var int Host index
     */
    protected $hostIndex;

    /**
     * @param ClientInterface $client
     * @param Configuration   $config
     * @param HeaderSelector  $selector
     * @param int             $hostIndex (Optional) host index to select the list of hosts if defined in the OpenAPI spec
     */
    public function __construct(
        ClientInterface $client = null,
        Configuration $config = null,
        HeaderSelector $selector = null,
        $hostIndex = 0
    ) {
        $this->client = $client ?: new Client();
        $this->config = $config ?: new Configuration();
        $this->headerSelector = $selector ?: new HeaderSelector();
        $this->hostIndex = $hostIndex;
    }

    /**
     * Set the host index
     *
     * @param int $hostIndex Host index (required)
     */
    public function setHostIndex($hostIndex): void
    {
        $this->hostIndex = $hostIndex;
    }

    /**
     * Get the host index
     *
     * @return int Host index
     */
    public function getHostIndex()
    {
        return $this->hostIndex;
    }

    /**
     * @return Configuration
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Operation customfieldsPatch
     *
     * Update customfields
     *
     * @param  string $x_api_client The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.&lt;br&gt;&lt;br&gt;Example: &lt;strong&gt;demo&lt;/strong&gt;.za.skynamo.me (required)
     * @param  \SkynamoClientAPI\Model\CustomfieldPatch[] $customfields A list of customfields request data (optional)
     *
     * @throws \SkynamoClientAPI\ApiException on non-2xx response
     * @throws \InvalidArgumentException
     * @return \SkynamoClientAPI\Model\InlineResponse2002|\SkynamoClientAPI\Model\ErrorModel
     */
    public function customfieldsPatch($x_api_client, $customfields = null)
    {
        list($response) = $this->customfieldsPatchWithHttpInfo($x_api_client, $customfields);
        return $response;
    }

    /**
     * Operation customfieldsPatchWithHttpInfo
     *
     * Update customfields
     *
     * @param  string $x_api_client The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.&lt;br&gt;&lt;br&gt;Example: &lt;strong&gt;demo&lt;/strong&gt;.za.skynamo.me (required)
     * @param  \SkynamoClientAPI\Model\CustomfieldPatch[] $customfields A list of customfields request data (optional)
     *
     * @throws \SkynamoClientAPI\ApiException on non-2xx response
     * @throws \InvalidArgumentException
     * @return array of \SkynamoClientAPI\Model\InlineResponse2002|\SkynamoClientAPI\Model\ErrorModel, HTTP status code, HTTP response headers (array of strings)
     */
    public function customfieldsPatchWithHttpInfo($x_api_client, $customfields = null)
    {
        $request = $this->customfieldsPatchRequest($x_api_client, $customfields);

        try {
            $options = $this->createHttpClientOption();
            try {
                $response = $this->client->send($request, $options);
            } catch (RequestException $e) {
                throw new ApiException(
                    "[{$e->getCode()}] {$e->getMessage()}",
                    (int) $e->getCode(),
                    $e->getResponse() ? $e->getResponse()->getHeaders() : null,
                    $e->getResponse() ? (string) $e->getResponse()->getBody() : null
                );
            }

            $statusCode = $response->getStatusCode();

            if ($statusCode < 200 || $statusCode > 299) {
                throw new ApiException(
                    sprintf(
                        '[%d] Error connecting to the API (%s)',
                        $statusCode,
                        (string) $request->getUri()
                    ),
                    $statusCode,
                    $response->getHeaders(),
                    (string) $response->getBody()
                );
            }

            switch($statusCode) {
                case 200:
                    if ('\SkynamoClientAPI\Model\InlineResponse2002' === '\SplFileObject') {
                        $content = $response->getBody(); //stream goes to serializer
                    } else {
                        $content = (string) $response->getBody();
                    }

                    return [
                        ObjectSerializer::deserialize($content, '\SkynamoClientAPI\Model\InlineResponse2002', []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
                case 400:
                    if ('\SkynamoClientAPI\Model\ErrorModel' === '\SplFileObject') {
                        $content = $response->getBody(); //stream goes to serializer
                    } else {
                        $content = (string) $response->getBody();
                    }

                    return [
                        ObjectSerializer::deserialize($content, '\SkynamoClientAPI\Model\ErrorModel', []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
            }

            $returnType = '\SkynamoClientAPI\Model\InlineResponse2002';
            if ($returnType === '\SplFileObject') {
                $content = $response->getBody(); //stream goes to serializer
            } else {
                $content = (string) $response->getBody();
            }

            return [
                ObjectSerializer::deserialize($content, $returnType, []),
                $response->getStatusCode(),
                $response->getHeaders()
            ];

        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\SkynamoClientAPI\Model\InlineResponse2002',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    break;
                case 400:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\SkynamoClientAPI\Model\ErrorModel',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    break;
            }
            throw $e;
        }
    }

    /**
     * Operation customfieldsPatchAsync
     *
     * Update customfields
     *
     * @param  string $x_api_client The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.&lt;br&gt;&lt;br&gt;Example: &lt;strong&gt;demo&lt;/strong&gt;.za.skynamo.me (required)
     * @param  \SkynamoClientAPI\Model\CustomfieldPatch[] $customfields A list of customfields request data (optional)
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function customfieldsPatchAsync($x_api_client, $customfields = null)
    {
        return $this->customfieldsPatchAsyncWithHttpInfo($x_api_client, $customfields)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation customfieldsPatchAsyncWithHttpInfo
     *
     * Update customfields
     *
     * @param  string $x_api_client The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.&lt;br&gt;&lt;br&gt;Example: &lt;strong&gt;demo&lt;/strong&gt;.za.skynamo.me (required)
     * @param  \SkynamoClientAPI\Model\CustomfieldPatch[] $customfields A list of customfields request data (optional)
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function customfieldsPatchAsyncWithHttpInfo($x_api_client, $customfields = null)
    {
        $returnType = '\SkynamoClientAPI\Model\InlineResponse2002';
        $request = $this->customfieldsPatchRequest($x_api_client, $customfields);

        return $this->client
            ->sendAsync($request, $this->createHttpClientOption())
            ->then(
                function ($response) use ($returnType) {
                    if ($returnType === '\SplFileObject') {
                        $content = $response->getBody(); //stream goes to serializer
                    } else {
                        $content = (string) $response->getBody();
                    }

                    return [
                        ObjectSerializer::deserialize($content, $returnType, []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
                },
                function ($exception) {
                    $response = $exception->getResponse();
                    $statusCode = $response->getStatusCode();
                    throw new ApiException(
                        sprintf(
                            '[%d] Error connecting to the API (%s)',
                            $statusCode,
                            $exception->getRequest()->getUri()
                        ),
                        $statusCode,
                        $response->getHeaders(),
                        (string) $response->getBody()
                    );
                }
            );
    }

    /**
     * Create request for operation 'customfieldsPatch'
     *
     * @param  string $x_api_client The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.&lt;br&gt;&lt;br&gt;Example: &lt;strong&gt;demo&lt;/strong&gt;.za.skynamo.me (required)
     * @param  \SkynamoClientAPI\Model\CustomfieldPatch[] $customfields A list of customfields request data (optional)
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function customfieldsPatchRequest($x_api_client, $customfields = null)
    {
        // verify the required parameter 'x_api_client' is set
        if ($x_api_client === null || (is_array($x_api_client) && count($x_api_client) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $x_api_client when calling customfieldsPatch'
            );
        }

        $resourcePath = '/customfields';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;


        // header params
        if ($x_api_client !== null) {
            $headerParams['X-API-CLIENT'] = ObjectSerializer::toHeaderValue($x_api_client);
        }



        if ($multipart) {
            $headers = $this->headerSelector->selectHeadersForMultipart(
                ['application/json']
            );
        } else {
            $headers = $this->headerSelector->selectHeaders(
                ['application/json'],
                []
            );
        }

        // for model (json/xml)
        if (isset($customfields)) {
            if ($headers['Content-Type'] === 'application/json') {
                $httpBody = \GuzzleHttp\json_encode(ObjectSerializer::sanitizeForSerialization($customfields));
            } else {
                $httpBody = $customfields;
            }
        } elseif (count($formParams) > 0) {
            if ($multipart) {
                $multipartContents = [];
                foreach ($formParams as $formParamName => $formParamValue) {
                    $formParamValueItems = is_array($formParamValue) ? $formParamValue : [$formParamValue];
                    foreach ($formParamValueItems as $formParamValueItem) {
                        $multipartContents[] = [
                            'name' => $formParamName,
                            'contents' => $formParamValueItem
                        ];
                    }
                }
                // for HTTP post (form)
                $httpBody = new MultipartStream($multipartContents);

            } elseif ($headers['Content-Type'] === 'application/json') {
                $httpBody = \GuzzleHttp\json_encode($formParams);

            } else {
                // for HTTP post (form)
                $httpBody = \GuzzleHttp\Psr7\build_query($formParams);
            }
        }

        // this endpoint requires API key authentication
        $apiKey = $this->config->getApiKeyWithPrefix('x-api-key');
        if ($apiKey !== null) {
            $headers['x-api-key'] = $apiKey;
        }

        $defaultHeaders = [];
        if ($this->config->getUserAgent()) {
            $defaultHeaders['User-Agent'] = $this->config->getUserAgent();
        }

        $headers = array_merge(
            $defaultHeaders,
            $headerParams,
            $headers
        );

        $query = \GuzzleHttp\Psr7\build_query($queryParams);
        return new Request(
            'PATCH',
            $this->config->getHost() . $resourcePath . ($query ? "?{$query}" : ''),
            $headers,
            $httpBody
        );
    }

    /**
     * Create http client option
     *
     * @throws \RuntimeException on file opening failure
     * @return array of http client options
     */
    protected function createHttpClientOption()
    {
        $options = [];
        if ($this->config->getDebug()) {
            $options[RequestOptions::DEBUG] = fopen($this->config->getDebugFile(), 'a');
            if (!$options[RequestOptions::DEBUG]) {
                throw new \RuntimeException('Failed to open the debug file: ' . $this->config->getDebugFile());
            }
        }

        return $options;
    }
}
