# SkynamoClientAPI\QuotesApi

All URIs are relative to https://api.za.skynamo.me/v1.

Method | HTTP request | Description
------------- | ------------- | -------------
[**quotesGet()**](QuotesApi.md#quotesGet) | **GET** /quotes | List quotes
[**quotesIdGet()**](QuotesApi.md#quotesIdGet) | **GET** /quotes/{id} | Get a quote
[**quotesPost()**](QuotesApi.md#quotesPost) | **POST** /quotes | Create quotes


## `quotesGet()`

```php
quotesGet($x_api_client, $page_number, $page_size, $flags): \SkynamoClientAPI\Model\InlineResponse20022
```

List quotes

Returns a list of quotes

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKey('x-api-key', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKeyPrefix('x-api-key', 'Bearer');


$apiInstance = new SkynamoClientAPI\Api\QuotesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$x_api_client = 'x_api_client_example'; // string | The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.<br><br>Example: <strong>demo</strong>.za.skynamo.me
$page_number = 1; // int | Defines the page number.
$page_size = 50; // int | Defines the size of each page. (Maximum = 200)
$flags = 'flags_example'; // string | Defines display configurations.<br><i>Availiable values</i> :<br> - show_nulls

try {
    $result = $apiInstance->quotesGet($x_api_client, $page_number, $page_size, $flags);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling QuotesApi->quotesGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **x_api_client** | **string**| The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.&lt;br&gt;&lt;br&gt;Example: &lt;strong&gt;demo&lt;/strong&gt;.za.skynamo.me |
 **page_number** | **int**| Defines the page number. | [optional] [default to 1]
 **page_size** | **int**| Defines the size of each page. (Maximum &#x3D; 200) | [optional] [default to 50]
 **flags** | **string**| Defines display configurations.&lt;br&gt;&lt;i&gt;Availiable values&lt;/i&gt; :&lt;br&gt; - show_nulls | [optional]

### Return type

[**\SkynamoClientAPI\Model\InlineResponse20022**](../Model/InlineResponse20022.md)

### Authorization

[api_key](../../README.md#api_key)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `quotesIdGet()`

```php
quotesIdGet($x_api_client, $id, $flags): \SkynamoClientAPI\Model\Quote
```

Get a quote

Fetch information about a specfic quote from the unique identifier

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKey('x-api-key', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKeyPrefix('x-api-key', 'Bearer');


$apiInstance = new SkynamoClientAPI\Api\QuotesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$x_api_client = 'x_api_client_example'; // string | The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.<br><br>Example: <strong>demo</strong>.za.skynamo.me
$id = 56; // int | The unique identifier of a specific entity.
$flags = 'flags_example'; // string | Defines display configurations.<br><i>Availiable values</i> :<br> - show_nulls

try {
    $result = $apiInstance->quotesIdGet($x_api_client, $id, $flags);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling QuotesApi->quotesIdGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **x_api_client** | **string**| The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.&lt;br&gt;&lt;br&gt;Example: &lt;strong&gt;demo&lt;/strong&gt;.za.skynamo.me |
 **id** | **int**| The unique identifier of a specific entity. |
 **flags** | **string**| Defines display configurations.&lt;br&gt;&lt;i&gt;Availiable values&lt;/i&gt; :&lt;br&gt; - show_nulls | [optional]

### Return type

[**\SkynamoClientAPI\Model\Quote**](../Model/Quote.md)

### Authorization

[api_key](../../README.md#api_key)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `quotesPost()`

```php
quotesPost($x_api_client, $quotes): \SkynamoClientAPI\Model\InlineResponse2006
```

Create quotes

Creates new quotes

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKey('x-api-key', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKeyPrefix('x-api-key', 'Bearer');


$apiInstance = new SkynamoClientAPI\Api\QuotesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$x_api_client = 'x_api_client_example'; // string | The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.<br><br>Example: <strong>demo</strong>.za.skynamo.me
$quotes = array(new \SkynamoClientAPI\Model\QuotePost()); // \SkynamoClientAPI\Model\QuotePost[] | Add the provided quotes to Skynamo

try {
    $result = $apiInstance->quotesPost($x_api_client, $quotes);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling QuotesApi->quotesPost: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **x_api_client** | **string**| The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.&lt;br&gt;&lt;br&gt;Example: &lt;strong&gt;demo&lt;/strong&gt;.za.skynamo.me |
 **quotes** | [**\SkynamoClientAPI\Model\QuotePost[]**](../Model/QuotePost.md)| Add the provided quotes to Skynamo | [optional]

### Return type

[**\SkynamoClientAPI\Model\InlineResponse2006**](../Model/InlineResponse2006.md)

### Authorization

[api_key](../../README.md#api_key)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)
