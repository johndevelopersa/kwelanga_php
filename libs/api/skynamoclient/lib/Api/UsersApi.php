<?php
/**
 * UsersApi
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
 * UsersApi Class Doc Comment
 *
 * @category Class
 * @package  SkynamoClientAPI
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 */
class UsersApi
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
     * Operation usersGet
     *
     * List users
     *
     * @param  string $x_api_client The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.&lt;br&gt;&lt;br&gt;Example: &lt;strong&gt;demo&lt;/strong&gt;.za.skynamo.me (required)
     * @param  int $page_number Defines the page number. (optional, default to 1)
     * @param  int $page_size Defines the size of each page. (Maximum &#x3D; 200) (optional, default to 50)
     * @param  string $flags Defines display configurations.&lt;br&gt;&lt;i&gt;Availiable values&lt;/i&gt; :&lt;br&gt; - show_nulls (optional)
     *
     * @throws \SkynamoClientAPI\ApiException on non-2xx response
     * @throws \InvalidArgumentException
     * @return \SkynamoClientAPI\Model\InlineResponse20028|\SkynamoClientAPI\Model\ErrorModel
     */
    public function usersGet($x_api_client, $page_number = 1, $page_size = 50, $flags = null)
    {
        list($response) = $this->usersGetWithHttpInfo($x_api_client, $page_number, $page_size, $flags);
        return $response;
    }

    /**
     * Operation usersGetWithHttpInfo
     *
     * List users
     *
     * @param  string $x_api_client The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.&lt;br&gt;&lt;br&gt;Example: &lt;strong&gt;demo&lt;/strong&gt;.za.skynamo.me (required)
     * @param  int $page_number Defines the page number. (optional, default to 1)
     * @param  int $page_size Defines the size of each page. (Maximum &#x3D; 200) (optional, default to 50)
     * @param  string $flags Defines display configurations.&lt;br&gt;&lt;i&gt;Availiable values&lt;/i&gt; :&lt;br&gt; - show_nulls (optional)
     *
     * @throws \SkynamoClientAPI\ApiException on non-2xx response
     * @throws \InvalidArgumentException
     * @return array of \SkynamoClientAPI\Model\InlineResponse20028|\SkynamoClientAPI\Model\ErrorModel, HTTP status code, HTTP response headers (array of strings)
     */
    public function usersGetWithHttpInfo($x_api_client, $page_number = 1, $page_size = 50, $flags = null)
    {
        $request = $this->usersGetRequest($x_api_client, $page_number, $page_size, $flags);

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
                    if ('\SkynamoClientAPI\Model\InlineResponse20028' === '\SplFileObject') {
                        $content = $response->getBody(); //stream goes to serializer
                    } else {
                        $content = (string) $response->getBody();
                    }

                    return [
                        ObjectSerializer::deserialize($content, '\SkynamoClientAPI\Model\InlineResponse20028', []),
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

            $returnType = '\SkynamoClientAPI\Model\InlineResponse20028';
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
                        '\SkynamoClientAPI\Model\InlineResponse20028',
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
     * Operation usersGetAsync
     *
     * List users
     *
     * @param  string $x_api_client The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.&lt;br&gt;&lt;br&gt;Example: &lt;strong&gt;demo&lt;/strong&gt;.za.skynamo.me (required)
     * @param  int $page_number Defines the page number. (optional, default to 1)
     * @param  int $page_size Defines the size of each page. (Maximum &#x3D; 200) (optional, default to 50)
     * @param  string $flags Defines display configurations.&lt;br&gt;&lt;i&gt;Availiable values&lt;/i&gt; :&lt;br&gt; - show_nulls (optional)
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function usersGetAsync($x_api_client, $page_number = 1, $page_size = 50, $flags = null)
    {
        return $this->usersGetAsyncWithHttpInfo($x_api_client, $page_number, $page_size, $flags)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation usersGetAsyncWithHttpInfo
     *
     * List users
     *
     * @param  string $x_api_client The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.&lt;br&gt;&lt;br&gt;Example: &lt;strong&gt;demo&lt;/strong&gt;.za.skynamo.me (required)
     * @param  int $page_number Defines the page number. (optional, default to 1)
     * @param  int $page_size Defines the size of each page. (Maximum &#x3D; 200) (optional, default to 50)
     * @param  string $flags Defines display configurations.&lt;br&gt;&lt;i&gt;Availiable values&lt;/i&gt; :&lt;br&gt; - show_nulls (optional)
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function usersGetAsyncWithHttpInfo($x_api_client, $page_number = 1, $page_size = 50, $flags = null)
    {
        $returnType = '\SkynamoClientAPI\Model\InlineResponse20028';
        $request = $this->usersGetRequest($x_api_client, $page_number, $page_size, $flags);

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
     * Create request for operation 'usersGet'
     *
     * @param  string $x_api_client The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.&lt;br&gt;&lt;br&gt;Example: &lt;strong&gt;demo&lt;/strong&gt;.za.skynamo.me (required)
     * @param  int $page_number Defines the page number. (optional, default to 1)
     * @param  int $page_size Defines the size of each page. (Maximum &#x3D; 200) (optional, default to 50)
     * @param  string $flags Defines display configurations.&lt;br&gt;&lt;i&gt;Availiable values&lt;/i&gt; :&lt;br&gt; - show_nulls (optional)
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function usersGetRequest($x_api_client, $page_number = 1, $page_size = 50, $flags = null)
    {
        // verify the required parameter 'x_api_client' is set
        if ($x_api_client === null || (is_array($x_api_client) && count($x_api_client) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $x_api_client when calling usersGet'
            );
        }

        $resourcePath = '/users';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;

        // query params
        if (is_array($page_number)) {
            $page_number = ObjectSerializer::serializeCollection($page_number, '', true);
        }
        if ($page_number !== null) {
            $queryParams['page_number'] = $page_number;
        }
        // query params
        if (is_array($page_size)) {
            $page_size = ObjectSerializer::serializeCollection($page_size, '', true);
        }
        if ($page_size !== null) {
            $queryParams['page_size'] = $page_size;
        }
        // query params
        if (is_array($flags)) {
            $flags = ObjectSerializer::serializeCollection($flags, '', true);
        }
        if ($flags !== null) {
            $queryParams['flags'] = $flags;
        }

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
        if (count($formParams) > 0) {
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
            'GET',
            $this->config->getHost() . $resourcePath . ($query ? "?{$query}" : ''),
            $headers,
            $httpBody
        );
    }

    /**
     * Operation usersIdGet
     *
     * Get an user
     *
     * @param  string $x_api_client The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.&lt;br&gt;&lt;br&gt;Example: &lt;strong&gt;demo&lt;/strong&gt;.za.skynamo.me (required)
     * @param  int $id The unique identifier of a specific entity. (required)
     * @param  string $flags Defines display configurations.&lt;br&gt;&lt;i&gt;Availiable values&lt;/i&gt; :&lt;br&gt; - show_nulls (optional)
     *
     * @throws \SkynamoClientAPI\ApiException on non-2xx response
     * @throws \InvalidArgumentException
     * @return \SkynamoClientAPI\Model\User|\SkynamoClientAPI\Model\ErrorModel|\SkynamoClientAPI\Model\ErrorModel
     */
    public function usersIdGet($x_api_client, $id, $flags = null)
    {
        list($response) = $this->usersIdGetWithHttpInfo($x_api_client, $id, $flags);
        return $response;
    }

    /**
     * Operation usersIdGetWithHttpInfo
     *
     * Get an user
     *
     * @param  string $x_api_client The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.&lt;br&gt;&lt;br&gt;Example: &lt;strong&gt;demo&lt;/strong&gt;.za.skynamo.me (required)
     * @param  int $id The unique identifier of a specific entity. (required)
     * @param  string $flags Defines display configurations.&lt;br&gt;&lt;i&gt;Availiable values&lt;/i&gt; :&lt;br&gt; - show_nulls (optional)
     *
     * @throws \SkynamoClientAPI\ApiException on non-2xx response
     * @throws \InvalidArgumentException
     * @return array of \SkynamoClientAPI\Model\User|\SkynamoClientAPI\Model\ErrorModel|\SkynamoClientAPI\Model\ErrorModel, HTTP status code, HTTP response headers (array of strings)
     */
    public function usersIdGetWithHttpInfo($x_api_client, $id, $flags = null)
    {
        $request = $this->usersIdGetRequest($x_api_client, $id, $flags);

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
                    if ('\SkynamoClientAPI\Model\User' === '\SplFileObject') {
                        $content = $response->getBody(); //stream goes to serializer
                    } else {
                        $content = (string) $response->getBody();
                    }

                    return [
                        ObjectSerializer::deserialize($content, '\SkynamoClientAPI\Model\User', []),
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
                case 404:
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

            $returnType = '\SkynamoClientAPI\Model\User';
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
                        '\SkynamoClientAPI\Model\User',
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
                case 404:
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
     * Operation usersIdGetAsync
     *
     * Get an user
     *
     * @param  string $x_api_client The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.&lt;br&gt;&lt;br&gt;Example: &lt;strong&gt;demo&lt;/strong&gt;.za.skynamo.me (required)
     * @param  int $id The unique identifier of a specific entity. (required)
     * @param  string $flags Defines display configurations.&lt;br&gt;&lt;i&gt;Availiable values&lt;/i&gt; :&lt;br&gt; - show_nulls (optional)
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function usersIdGetAsync($x_api_client, $id, $flags = null)
    {
        return $this->usersIdGetAsyncWithHttpInfo($x_api_client, $id, $flags)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation usersIdGetAsyncWithHttpInfo
     *
     * Get an user
     *
     * @param  string $x_api_client The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.&lt;br&gt;&lt;br&gt;Example: &lt;strong&gt;demo&lt;/strong&gt;.za.skynamo.me (required)
     * @param  int $id The unique identifier of a specific entity. (required)
     * @param  string $flags Defines display configurations.&lt;br&gt;&lt;i&gt;Availiable values&lt;/i&gt; :&lt;br&gt; - show_nulls (optional)
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function usersIdGetAsyncWithHttpInfo($x_api_client, $id, $flags = null)
    {
        $returnType = '\SkynamoClientAPI\Model\User';
        $request = $this->usersIdGetRequest($x_api_client, $id, $flags);

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
     * Create request for operation 'usersIdGet'
     *
     * @param  string $x_api_client The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.&lt;br&gt;&lt;br&gt;Example: &lt;strong&gt;demo&lt;/strong&gt;.za.skynamo.me (required)
     * @param  int $id The unique identifier of a specific entity. (required)
     * @param  string $flags Defines display configurations.&lt;br&gt;&lt;i&gt;Availiable values&lt;/i&gt; :&lt;br&gt; - show_nulls (optional)
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function usersIdGetRequest($x_api_client, $id, $flags = null)
    {
        // verify the required parameter 'x_api_client' is set
        if ($x_api_client === null || (is_array($x_api_client) && count($x_api_client) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $x_api_client when calling usersIdGet'
            );
        }
        // verify the required parameter 'id' is set
        if ($id === null || (is_array($id) && count($id) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $id when calling usersIdGet'
            );
        }

        $resourcePath = '/users/{id}';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;

        // query params
        if (is_array($flags)) {
            $flags = ObjectSerializer::serializeCollection($flags, '', true);
        }
        if ($flags !== null) {
            $queryParams['flags'] = $flags;
        }

        // header params
        if ($x_api_client !== null) {
            $headerParams['X-API-CLIENT'] = ObjectSerializer::toHeaderValue($x_api_client);
        }

        // path params
        if ($id !== null) {
            $resourcePath = str_replace(
                '{' . 'id' . '}',
                ObjectSerializer::toPathValue($id),
                $resourcePath
            );
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
        if (count($formParams) > 0) {
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
            'GET',
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
