# SkynamoClientAPI\CustomerCommentsApi

All URIs are relative to https://api.za.skynamo.me/v1.

Method | HTTP request | Description
------------- | ------------- | -------------
[**customercommentsGet()**](CustomerCommentsApi.md#customercommentsGet) | **GET** /customercomments | List customer comments
[**customercommentsIdGet()**](CustomerCommentsApi.md#customercommentsIdGet) | **GET** /customercomments/{id} | Get a customer comment
[**customercommentsPost()**](CustomerCommentsApi.md#customercommentsPost) | **POST** /customercomments | Create customer comments


## `customercommentsGet()`

```php
customercommentsGet($x_api_client, $page_number, $page_size, $flags, $filters): \SkynamoClientAPI\Model\InlineResponse2007
```

List customer comments

Returns a list of customer comments

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKey('x-api-key', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKeyPrefix('x-api-key', 'Bearer');


$apiInstance = new SkynamoClientAPI\Api\CustomerCommentsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$x_api_client = 'x_api_client_example'; // string | The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.<br><br>Example: <strong>demo</strong>.za.skynamo.me
$page_number = 1; // int | Defines the page number.
$page_size = 50; // int | Defines the size of each page. (Maximum = 200)
$flags = 'flags_example'; // string | Defines display configurations.<br><i>Availiable values</i> :<br> - show_nulls
$filters = 'filters_example'; // string | Used to filter information.<br><br><i>Filters available</i> : <br>- less_than<br>- less_than_equals<br>-  greater_than<br>- greater_than_equals<br>- equals<br>- starts_with<br> <br><i>Available filter parameters</i>:<br>- id<br>- row_version<br>- customer_id?<br>- customer_code?<br><br><i>Examples</i>:<br>- [\"less_than(id,5)\"]<br>- [\"greater_than_equals(row_version, 13421541)\"]<br>- [\"equals(customer_code, SKY_1234)\"]

try {
    $result = $apiInstance->customercommentsGet($x_api_client, $page_number, $page_size, $flags, $filters);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CustomerCommentsApi->customercommentsGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **x_api_client** | **string**| The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.&lt;br&gt;&lt;br&gt;Example: &lt;strong&gt;demo&lt;/strong&gt;.za.skynamo.me |
 **page_number** | **int**| Defines the page number. | [optional] [default to 1]
 **page_size** | **int**| Defines the size of each page. (Maximum &#x3D; 200) | [optional] [default to 50]
 **flags** | **string**| Defines display configurations.&lt;br&gt;&lt;i&gt;Availiable values&lt;/i&gt; :&lt;br&gt; - show_nulls | [optional]
 **filters** | **string**| Used to filter information.&lt;br&gt;&lt;br&gt;&lt;i&gt;Filters available&lt;/i&gt; : &lt;br&gt;- less_than&lt;br&gt;- less_than_equals&lt;br&gt;-  greater_than&lt;br&gt;- greater_than_equals&lt;br&gt;- equals&lt;br&gt;- starts_with&lt;br&gt; &lt;br&gt;&lt;i&gt;Available filter parameters&lt;/i&gt;:&lt;br&gt;- id&lt;br&gt;- row_version&lt;br&gt;- customer_id?&lt;br&gt;- customer_code?&lt;br&gt;&lt;br&gt;&lt;i&gt;Examples&lt;/i&gt;:&lt;br&gt;- [\&quot;less_than(id,5)\&quot;]&lt;br&gt;- [\&quot;greater_than_equals(row_version, 13421541)\&quot;]&lt;br&gt;- [\&quot;equals(customer_code, SKY_1234)\&quot;] | [optional]

### Return type

[**\SkynamoClientAPI\Model\InlineResponse2007**](../Model/InlineResponse2007.md)

### Authorization

[api_key](../../README.md#api_key)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `customercommentsIdGet()`

```php
customercommentsIdGet($x_api_client, $id, $flags): \SkynamoClientAPI\Model\CustomerComment
```

Get a customer comment

Fetch information about a specfic customer comment from the unique identifier

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKey('x-api-key', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKeyPrefix('x-api-key', 'Bearer');


$apiInstance = new SkynamoClientAPI\Api\CustomerCommentsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$x_api_client = 'x_api_client_example'; // string | The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.<br><br>Example: <strong>demo</strong>.za.skynamo.me
$id = 56; // int | The unique identifier of a specific entity.
$flags = 'flags_example'; // string | Defines display configurations.<br><i>Availiable values</i> :<br> - show_nulls

try {
    $result = $apiInstance->customercommentsIdGet($x_api_client, $id, $flags);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CustomerCommentsApi->customercommentsIdGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **x_api_client** | **string**| The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.&lt;br&gt;&lt;br&gt;Example: &lt;strong&gt;demo&lt;/strong&gt;.za.skynamo.me |
 **id** | **int**| The unique identifier of a specific entity. |
 **flags** | **string**| Defines display configurations.&lt;br&gt;&lt;i&gt;Availiable values&lt;/i&gt; :&lt;br&gt; - show_nulls | [optional]

### Return type

[**\SkynamoClientAPI\Model\CustomerComment**](../Model/CustomerComment.md)

### Authorization

[api_key](../../README.md#api_key)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `customercommentsPost()`

```php
customercommentsPost($x_api_client, $customer_comments): \SkynamoClientAPI\Model\InlineResponse2008
```

Create customer comments

Creates new customer comments

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKey('x-api-key', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKeyPrefix('x-api-key', 'Bearer');


$apiInstance = new SkynamoClientAPI\Api\CustomerCommentsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$x_api_client = 'x_api_client_example'; // string | The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.<br><br>Example: <strong>demo</strong>.za.skynamo.me
$customer_comments = array(new \SkynamoClientAPI\Model\CustomerCommentPost()); // \SkynamoClientAPI\Model\CustomerCommentPost[] | Add the provided customer comments to Skynamo

try {
    $result = $apiInstance->customercommentsPost($x_api_client, $customer_comments);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CustomerCommentsApi->customercommentsPost: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **x_api_client** | **string**| The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.&lt;br&gt;&lt;br&gt;Example: &lt;strong&gt;demo&lt;/strong&gt;.za.skynamo.me |
 **customer_comments** | [**\SkynamoClientAPI\Model\CustomerCommentPost[]**](../Model/CustomerCommentPost.md)| Add the provided customer comments to Skynamo | [optional]

### Return type

[**\SkynamoClientAPI\Model\InlineResponse2008**](../Model/InlineResponse2008.md)

### Authorization

[api_key](../../README.md#api_key)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)
