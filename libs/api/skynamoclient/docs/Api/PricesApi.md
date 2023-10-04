# SkynamoClientAPI\PricesApi

All URIs are relative to https://api.za.skynamo.me/v1.

Method | HTTP request | Description
------------- | ------------- | -------------
[**pricesGet()**](PricesApi.md#pricesGet) | **GET** /prices | List prices
[**pricesPost()**](PricesApi.md#pricesPost) | **POST** /prices | Create/Update order unit price


## `pricesGet()`

```php
pricesGet($x_api_client, $page_number, $page_size, $flags): \SkynamoClientAPI\Model\InlineResponse20019
```

List prices

Returns a list of products order units prices

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKey('x-api-key', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKeyPrefix('x-api-key', 'Bearer');


$apiInstance = new SkynamoClientAPI\Api\PricesApi(
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
    $result = $apiInstance->pricesGet($x_api_client, $page_number, $page_size, $flags);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling PricesApi->pricesGet: ', $e->getMessage(), PHP_EOL;
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

[**\SkynamoClientAPI\Model\InlineResponse20019**](../Model/InlineResponse20019.md)

### Authorization

[api_key](../../README.md#api_key)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `pricesPost()`

```php
pricesPost($x_api_client, $prices): \SkynamoClientAPI\Model\InlineResponse2002
```

Create/Update order unit price

Creates or updates order unit prices. If the price are not specified then the price will be deleted. The product id is required unless the product code is specified. The order unit id is required unless the order unit name is specified. A price list id is required unless the price list name is specified.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKey('x-api-key', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKeyPrefix('x-api-key', 'Bearer');


$apiInstance = new SkynamoClientAPI\Api\PricesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$x_api_client = 'x_api_client_example'; // string | The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.<br><br>Example: <strong>demo</strong>.za.skynamo.me
$prices = array(new \SkynamoClientAPI\Model\OrderUnitPricePost()); // \SkynamoClientAPI\Model\OrderUnitPricePost[] | A list of order unit price request data

try {
    $result = $apiInstance->pricesPost($x_api_client, $prices);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling PricesApi->pricesPost: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **x_api_client** | **string**| The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.&lt;br&gt;&lt;br&gt;Example: &lt;strong&gt;demo&lt;/strong&gt;.za.skynamo.me |
 **prices** | [**\SkynamoClientAPI\Model\OrderUnitPricePost[]**](../Model/OrderUnitPricePost.md)| A list of order unit price request data | [optional]

### Return type

[**\SkynamoClientAPI\Model\InlineResponse2002**](../Model/InlineResponse2002.md)

### Authorization

[api_key](../../README.md#api_key)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)
