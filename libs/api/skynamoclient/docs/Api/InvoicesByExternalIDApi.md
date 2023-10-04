# SkynamoClientAPI\InvoicesByExternalIDApi

All URIs are relative to https://api.za.skynamo.me/v1.

Method | HTTP request | Description
------------- | ------------- | -------------
[**invoicesbyexternalidDelete()**](InvoicesByExternalIDApi.md#invoicesbyexternalidDelete) | **DELETE** /invoicesbyexternalid | Delete existing invoices
[**invoicesbyexternalidExternalIDGet()**](InvoicesByExternalIDApi.md#invoicesbyexternalidExternalIDGet) | **GET** /invoicesbyexternalid/{ExternalID} | Get an invoice
[**invoicesbyexternalidPatch()**](InvoicesByExternalIDApi.md#invoicesbyexternalidPatch) | **PATCH** /invoicesbyexternalid | Update invoices
[**invoicesbyexternalidPost()**](InvoicesByExternalIDApi.md#invoicesbyexternalidPost) | **POST** /invoicesbyexternalid | Create invoices
[**invoicesbyexternalidPut()**](InvoicesByExternalIDApi.md#invoicesbyexternalidPut) | **PUT** /invoicesbyexternalid | Replace invoices


## `invoicesbyexternalidDelete()`

```php
invoicesbyexternalidDelete($x_api_client, $ids): \SkynamoClientAPI\Model\InlineResponse2002
```

Delete existing invoices

Delete existing invoices

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKey('x-api-key', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKeyPrefix('x-api-key', 'Bearer');


$apiInstance = new SkynamoClientAPI\Api\InvoicesByExternalIDApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$x_api_client = 'x_api_client_example'; // string | The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.<br><br>Example: <strong>demo</strong>.za.skynamo.me
$ids = array(56); // int[] | A list of external invoice IDs that should be deleted

try {
    $result = $apiInstance->invoicesbyexternalidDelete($x_api_client, $ids);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling InvoicesByExternalIDApi->invoicesbyexternalidDelete: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **x_api_client** | **string**| The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.&lt;br&gt;&lt;br&gt;Example: &lt;strong&gt;demo&lt;/strong&gt;.za.skynamo.me |
 **ids** | [**int[]**](../Model/int.md)| A list of external invoice IDs that should be deleted |

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

## `invoicesbyexternalidExternalIDGet()`

```php
invoicesbyexternalidExternalIDGet($x_api_client, $external_id, $flags): \SkynamoClientAPI\Model\Invoice
```

Get an invoice

Fetch information about a specfic invoice from using the external id

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKey('x-api-key', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKeyPrefix('x-api-key', 'Bearer');


$apiInstance = new SkynamoClientAPI\Api\InvoicesByExternalIDApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$x_api_client = 'x_api_client_example'; // string | The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.<br><br>Example: <strong>demo</strong>.za.skynamo.me
$external_id = 'external_id_example'; // string | The unique external identifier of a specific entity.
$flags = 'flags_example'; // string | Defines display configurations.<br><i>Availiable values</i> :<br> - show_nulls

try {
    $result = $apiInstance->invoicesbyexternalidExternalIDGet($x_api_client, $external_id, $flags);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling InvoicesByExternalIDApi->invoicesbyexternalidExternalIDGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **x_api_client** | **string**| The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.&lt;br&gt;&lt;br&gt;Example: &lt;strong&gt;demo&lt;/strong&gt;.za.skynamo.me |
 **external_id** | **string**| The unique external identifier of a specific entity. |
 **flags** | **string**| Defines display configurations.&lt;br&gt;&lt;i&gt;Availiable values&lt;/i&gt; :&lt;br&gt; - show_nulls | [optional]

### Return type

[**\SkynamoClientAPI\Model\Invoice**](../Model/Invoice.md)

### Authorization

[api_key](../../README.md#api_key)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `invoicesbyexternalidPatch()`

```php
invoicesbyexternalidPatch($x_api_client, $invoices): \SkynamoClientAPI\Model\InlineResponse2002
```

Update invoices

Updates a set of invoices with the provided list of invoices

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKey('x-api-key', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKeyPrefix('x-api-key', 'Bearer');


$apiInstance = new SkynamoClientAPI\Api\InvoicesByExternalIDApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$x_api_client = 'x_api_client_example'; // string | The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.<br><br>Example: <strong>demo</strong>.za.skynamo.me
$invoices = array(new \SkynamoClientAPI\Model\InvoicePatch()); // \SkynamoClientAPI\Model\InvoicePatch[] | Update the provided invoices in Skynamo

try {
    $result = $apiInstance->invoicesbyexternalidPatch($x_api_client, $invoices);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling InvoicesByExternalIDApi->invoicesbyexternalidPatch: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **x_api_client** | **string**| The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.&lt;br&gt;&lt;br&gt;Example: &lt;strong&gt;demo&lt;/strong&gt;.za.skynamo.me |
 **invoices** | [**\SkynamoClientAPI\Model\InvoicePatch[]**](../Model/InvoicePatch.md)| Update the provided invoices in Skynamo | [optional]

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

## `invoicesbyexternalidPost()`

```php
invoicesbyexternalidPost($x_api_client, $invoices): \SkynamoClientAPI\Model\InlineResponse20016
```

Create invoices

Creates new invoices

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKey('x-api-key', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKeyPrefix('x-api-key', 'Bearer');


$apiInstance = new SkynamoClientAPI\Api\InvoicesByExternalIDApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$x_api_client = 'x_api_client_example'; // string | The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.<br><br>Example: <strong>demo</strong>.za.skynamo.me
$invoices = array(new \SkynamoClientAPI\Model\InvoicePost()); // \SkynamoClientAPI\Model\InvoicePost[] | Add the provided invoices to Skynamo

try {
    $result = $apiInstance->invoicesbyexternalidPost($x_api_client, $invoices);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling InvoicesByExternalIDApi->invoicesbyexternalidPost: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **x_api_client** | **string**| The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.&lt;br&gt;&lt;br&gt;Example: &lt;strong&gt;demo&lt;/strong&gt;.za.skynamo.me |
 **invoices** | [**\SkynamoClientAPI\Model\InvoicePost[]**](../Model/InvoicePost.md)| Add the provided invoices to Skynamo | [optional]

### Return type

[**\SkynamoClientAPI\Model\InlineResponse20016**](../Model/InlineResponse20016.md)

### Authorization

[api_key](../../README.md#api_key)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `invoicesbyexternalidPut()`

```php
invoicesbyexternalidPut($x_api_client, $invoices): \SkynamoClientAPI\Model\InlineResponse2002
```

Replace invoices

Replaces a set of invoices with the provided list of products

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKey('x-api-key', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKeyPrefix('x-api-key', 'Bearer');


$apiInstance = new SkynamoClientAPI\Api\InvoicesByExternalIDApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$x_api_client = 'x_api_client_example'; // string | The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.<br><br>Example: <strong>demo</strong>.za.skynamo.me
$invoices = array(new \SkynamoClientAPI\Model\InvoicePut()); // \SkynamoClientAPI\Model\InvoicePut[] | Replace the provided invoices in Skynamo

try {
    $result = $apiInstance->invoicesbyexternalidPut($x_api_client, $invoices);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling InvoicesByExternalIDApi->invoicesbyexternalidPut: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **x_api_client** | **string**| The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.&lt;br&gt;&lt;br&gt;Example: &lt;strong&gt;demo&lt;/strong&gt;.za.skynamo.me |
 **invoices** | [**\SkynamoClientAPI\Model\InvoicePut[]**](../Model/InvoicePut.md)| Replace the provided invoices in Skynamo | [optional]

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
