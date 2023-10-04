# SkynamoClientAPI\InvoicesApi

All URIs are relative to https://api.za.skynamo.me/v1.

Method | HTTP request | Description
------------- | ------------- | -------------
[**invoicesDelete()**](InvoicesApi.md#invoicesDelete) | **DELETE** /invoices | Delete existing invoice
[**invoicesGet()**](InvoicesApi.md#invoicesGet) | **GET** /invoices | List invoices
[**invoicesIdGet()**](InvoicesApi.md#invoicesIdGet) | **GET** /invoices/{id} | Get an invoice
[**invoicesPatch()**](InvoicesApi.md#invoicesPatch) | **PATCH** /invoices | Update invoices
[**invoicesPost()**](InvoicesApi.md#invoicesPost) | **POST** /invoices | Create invoices
[**invoicesPut()**](InvoicesApi.md#invoicesPut) | **PUT** /invoices | Replace invoices


## `invoicesDelete()`

```php
invoicesDelete($x_api_client, $ids): \SkynamoClientAPI\Model\InlineResponse2002
```

Delete existing invoice

Delete existing invoice

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKey('x-api-key', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKeyPrefix('x-api-key', 'Bearer');


$apiInstance = new SkynamoClientAPI\Api\InvoicesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$x_api_client = 'x_api_client_example'; // string | The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.<br><br>Example: <strong>demo</strong>.za.skynamo.me
$ids = array(56); // int[] | A list of invoice IDs that should be deleted

try {
    $result = $apiInstance->invoicesDelete($x_api_client, $ids);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling InvoicesApi->invoicesDelete: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **x_api_client** | **string**| The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.&lt;br&gt;&lt;br&gt;Example: &lt;strong&gt;demo&lt;/strong&gt;.za.skynamo.me |
 **ids** | [**int[]**](../Model/int.md)| A list of invoice IDs that should be deleted |

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

## `invoicesGet()`

```php
invoicesGet($x_api_client, $page_number, $page_size, $flags): \SkynamoClientAPI\Model\InlineResponse20015
```

List invoices

Returns a list of invoices

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKey('x-api-key', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKeyPrefix('x-api-key', 'Bearer');


$apiInstance = new SkynamoClientAPI\Api\InvoicesApi(
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
    $result = $apiInstance->invoicesGet($x_api_client, $page_number, $page_size, $flags);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling InvoicesApi->invoicesGet: ', $e->getMessage(), PHP_EOL;
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

[**\SkynamoClientAPI\Model\InlineResponse20015**](../Model/InlineResponse20015.md)

### Authorization

[api_key](../../README.md#api_key)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `invoicesIdGet()`

```php
invoicesIdGet($x_api_client, $id, $flags): \SkynamoClientAPI\Model\Invoice
```

Get an invoice

Fetch information about a specfic invoice from the unique identifier

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKey('x-api-key', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKeyPrefix('x-api-key', 'Bearer');


$apiInstance = new SkynamoClientAPI\Api\InvoicesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$x_api_client = 'x_api_client_example'; // string | The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.<br><br>Example: <strong>demo</strong>.za.skynamo.me
$id = 56; // int | The unique identifier of a specific entity.
$flags = 'flags_example'; // string | Defines display configurations.<br><i>Availiable values</i> :<br> - show_nulls

try {
    $result = $apiInstance->invoicesIdGet($x_api_client, $id, $flags);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling InvoicesApi->invoicesIdGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **x_api_client** | **string**| The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.&lt;br&gt;&lt;br&gt;Example: &lt;strong&gt;demo&lt;/strong&gt;.za.skynamo.me |
 **id** | **int**| The unique identifier of a specific entity. |
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

## `invoicesPatch()`

```php
invoicesPatch($x_api_client, $invoices): \SkynamoClientAPI\Model\InlineResponse2002
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


$apiInstance = new SkynamoClientAPI\Api\InvoicesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$x_api_client = 'x_api_client_example'; // string | The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.<br><br>Example: <strong>demo</strong>.za.skynamo.me
$invoices = array(new \SkynamoClientAPI\Model\InvoicePatch()); // \SkynamoClientAPI\Model\InvoicePatch[] | Update the provided invoices in Skynamo

try {
    $result = $apiInstance->invoicesPatch($x_api_client, $invoices);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling InvoicesApi->invoicesPatch: ', $e->getMessage(), PHP_EOL;
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

## `invoicesPost()`

```php
invoicesPost($x_api_client, $invoices): \SkynamoClientAPI\Model\InlineResponse20016
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


$apiInstance = new SkynamoClientAPI\Api\InvoicesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$x_api_client = 'x_api_client_example'; // string | The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.<br><br>Example: <strong>demo</strong>.za.skynamo.me
$invoices = array(new \SkynamoClientAPI\Model\InvoicePost()); // \SkynamoClientAPI\Model\InvoicePost[] | Add the provided invoices to Skynamo

try {
    $result = $apiInstance->invoicesPost($x_api_client, $invoices);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling InvoicesApi->invoicesPost: ', $e->getMessage(), PHP_EOL;
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

## `invoicesPut()`

```php
invoicesPut($x_api_client, $invoices): \SkynamoClientAPI\Model\InlineResponse2002
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


$apiInstance = new SkynamoClientAPI\Api\InvoicesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$x_api_client = 'x_api_client_example'; // string | The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.<br><br>Example: <strong>demo</strong>.za.skynamo.me
$invoices = array(new \SkynamoClientAPI\Model\InvoicePut()); // \SkynamoClientAPI\Model\InvoicePut[] | Replace the provided invoices in Skynamo

try {
    $result = $apiInstance->invoicesPut($x_api_client, $invoices);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling InvoicesApi->invoicesPut: ', $e->getMessage(), PHP_EOL;
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
