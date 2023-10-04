# SkynamoClientAPI\WarehousesApi

All URIs are relative to https://api.za.skynamo.me/v1.

Method | HTTP request | Description
------------- | ------------- | -------------
[**warehousesGet()**](WarehousesApi.md#warehousesGet) | **GET** /warehouses | List warehouses
[**warehousesIdGet()**](WarehousesApi.md#warehousesIdGet) | **GET** /warehouses/{id} | Get a warehouse
[**warehousesPatch()**](WarehousesApi.md#warehousesPatch) | **PATCH** /warehouses | Update warehouses
[**warehousesPost()**](WarehousesApi.md#warehousesPost) | **POST** /warehouses | Create warehouses
[**warehousesPut()**](WarehousesApi.md#warehousesPut) | **PUT** /warehouses | Replace warehouses


## `warehousesGet()`

```php
warehousesGet($x_api_client, $page_number, $page_size, $flags): \SkynamoClientAPI\Model\InlineResponse20031
```

List warehouses

Returns a list of warehouses

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKey('x-api-key', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKeyPrefix('x-api-key', 'Bearer');


$apiInstance = new SkynamoClientAPI\Api\WarehousesApi(
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
    $result = $apiInstance->warehousesGet($x_api_client, $page_number, $page_size, $flags);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling WarehousesApi->warehousesGet: ', $e->getMessage(), PHP_EOL;
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

[**\SkynamoClientAPI\Model\InlineResponse20031**](../Model/InlineResponse20031.md)

### Authorization

[api_key](../../README.md#api_key)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `warehousesIdGet()`

```php
warehousesIdGet($x_api_client, $id, $flags): \SkynamoClientAPI\Model\Warehouse
```

Get a warehouse

Fetch information about a specfic warehouse using an id

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKey('x-api-key', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKeyPrefix('x-api-key', 'Bearer');


$apiInstance = new SkynamoClientAPI\Api\WarehousesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$x_api_client = 'x_api_client_example'; // string | The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.<br><br>Example: <strong>demo</strong>.za.skynamo.me
$id = 56; // int | The unique identifier of a specific entity.
$flags = 'flags_example'; // string | Defines display configurations.<br><i>Availiable values</i> :<br> - show_nulls

try {
    $result = $apiInstance->warehousesIdGet($x_api_client, $id, $flags);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling WarehousesApi->warehousesIdGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **x_api_client** | **string**| The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.&lt;br&gt;&lt;br&gt;Example: &lt;strong&gt;demo&lt;/strong&gt;.za.skynamo.me |
 **id** | **int**| The unique identifier of a specific entity. |
 **flags** | **string**| Defines display configurations.&lt;br&gt;&lt;i&gt;Availiable values&lt;/i&gt; :&lt;br&gt; - show_nulls | [optional]

### Return type

[**\SkynamoClientAPI\Model\Warehouse**](../Model/Warehouse.md)

### Authorization

[api_key](../../README.md#api_key)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `warehousesPatch()`

```php
warehousesPatch($x_api_client, $warehouses): \SkynamoClientAPI\Model\InlineResponse2002
```

Update warehouses

Update warehouses

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKey('x-api-key', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKeyPrefix('x-api-key', 'Bearer');


$apiInstance = new SkynamoClientAPI\Api\WarehousesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$x_api_client = 'x_api_client_example'; // string | The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.<br><br>Example: <strong>demo</strong>.za.skynamo.me
$warehouses = array(new \SkynamoClientAPI\Model\WarehousePatch()); // \SkynamoClientAPI\Model\WarehousePatch[] | A list of warehouses request data

try {
    $result = $apiInstance->warehousesPatch($x_api_client, $warehouses);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling WarehousesApi->warehousesPatch: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **x_api_client** | **string**| The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.&lt;br&gt;&lt;br&gt;Example: &lt;strong&gt;demo&lt;/strong&gt;.za.skynamo.me |
 **warehouses** | [**\SkynamoClientAPI\Model\WarehousePatch[]**](../Model/WarehousePatch.md)| A list of warehouses request data | [optional]

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

## `warehousesPost()`

```php
warehousesPost($x_api_client, $warehouses): \SkynamoClientAPI\Model\InlineResponse2002
```

Create warehouses

Creates warehouses

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKey('x-api-key', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKeyPrefix('x-api-key', 'Bearer');


$apiInstance = new SkynamoClientAPI\Api\WarehousesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$x_api_client = 'x_api_client_example'; // string | The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.<br><br>Example: <strong>demo</strong>.za.skynamo.me
$warehouses = array(new \SkynamoClientAPI\Model\WarehousePost()); // \SkynamoClientAPI\Model\WarehousePost[] | A list of warehouses request data

try {
    $result = $apiInstance->warehousesPost($x_api_client, $warehouses);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling WarehousesApi->warehousesPost: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **x_api_client** | **string**| The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.&lt;br&gt;&lt;br&gt;Example: &lt;strong&gt;demo&lt;/strong&gt;.za.skynamo.me |
 **warehouses** | [**\SkynamoClientAPI\Model\WarehousePost[]**](../Model/WarehousePost.md)| A list of warehouses request data | [optional]

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

## `warehousesPut()`

```php
warehousesPut($x_api_client, $warehouses): \SkynamoClientAPI\Model\InlineResponse2002
```

Replace warehouses

Replace warehouses

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKey('x-api-key', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKeyPrefix('x-api-key', 'Bearer');


$apiInstance = new SkynamoClientAPI\Api\WarehousesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$x_api_client = 'x_api_client_example'; // string | The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.<br><br>Example: <strong>demo</strong>.za.skynamo.me
$warehouses = array(new \SkynamoClientAPI\Model\WarehousePut()); // \SkynamoClientAPI\Model\WarehousePut[] | A list of warehouses request data

try {
    $result = $apiInstance->warehousesPut($x_api_client, $warehouses);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling WarehousesApi->warehousesPut: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **x_api_client** | **string**| The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.&lt;br&gt;&lt;br&gt;Example: &lt;strong&gt;demo&lt;/strong&gt;.za.skynamo.me |
 **warehouses** | [**\SkynamoClientAPI\Model\WarehousePut[]**](../Model/WarehousePut.md)| A list of warehouses request data | [optional]

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
