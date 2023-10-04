# SkynamoClientAPI\CustomersApi

All URIs are relative to https://api.za.skynamo.me/v1.

Method | HTTP request | Description
------------- | ------------- | -------------
[**customersGet()**](CustomersApi.md#customersGet) | **GET** /customers | List customers
[**customersIdGet()**](CustomersApi.md#customersIdGet) | **GET** /customers/{id} | Get a customer
[**customersPatch()**](CustomersApi.md#customersPatch) | **PATCH** /customers | Update customers
[**customersPost()**](CustomersApi.md#customersPost) | **POST** /customers | Create customers
[**customersPut()**](CustomersApi.md#customersPut) | **PUT** /customers | Replace customers


## `customersGet()`

```php
customersGet($x_api_client, $page_number, $page_size, $flags, $filters): \SkynamoClientAPI\Model\InlineResponse2009
```

List customers

Returns a list of customers

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKey('x-api-key', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKeyPrefix('x-api-key', 'Bearer');


$apiInstance = new SkynamoClientAPI\Api\CustomersApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$x_api_client = 'x_api_client_example'; // string | The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.<br><br>Example: <strong>demo</strong>.za.skynamo.me
$page_number = 1; // int | Defines the page number.
$page_size = 50; // int | Defines the size of each page. (Maximum = 200)
$flags = 'flags_example'; // string | Defines display configurations.<br><i>Availiable values</i> :<br> - show_nulls
$filters = 'filters_example'; // string | Used to filter information.<br><br><i>Filters available</i> : <br>- less_than<br>- less_than_equals<br>-  greater_than<br>- greater_than_equals<br>- equals<br>- starts_with<br> <br><i>Available filter parameters</i>:<br>- id<br>- code<br>- active<br>- create_date<br>- row_version<br><br><i>Examples</i>:<br>- [\"less_than(id,5)\"]<br>- [\"starts_with(code,HB_)\"]<br>- [\"greater_than_equals(create_date, 2018-01-01)\"]

try {
    $result = $apiInstance->customersGet($x_api_client, $page_number, $page_size, $flags, $filters);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CustomersApi->customersGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **x_api_client** | **string**| The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.&lt;br&gt;&lt;br&gt;Example: &lt;strong&gt;demo&lt;/strong&gt;.za.skynamo.me |
 **page_number** | **int**| Defines the page number. | [optional] [default to 1]
 **page_size** | **int**| Defines the size of each page. (Maximum &#x3D; 200) | [optional] [default to 50]
 **flags** | **string**| Defines display configurations.&lt;br&gt;&lt;i&gt;Availiable values&lt;/i&gt; :&lt;br&gt; - show_nulls | [optional]
 **filters** | **string**| Used to filter information.&lt;br&gt;&lt;br&gt;&lt;i&gt;Filters available&lt;/i&gt; : &lt;br&gt;- less_than&lt;br&gt;- less_than_equals&lt;br&gt;-  greater_than&lt;br&gt;- greater_than_equals&lt;br&gt;- equals&lt;br&gt;- starts_with&lt;br&gt; &lt;br&gt;&lt;i&gt;Available filter parameters&lt;/i&gt;:&lt;br&gt;- id&lt;br&gt;- code&lt;br&gt;- active&lt;br&gt;- create_date&lt;br&gt;- row_version&lt;br&gt;&lt;br&gt;&lt;i&gt;Examples&lt;/i&gt;:&lt;br&gt;- [\&quot;less_than(id,5)\&quot;]&lt;br&gt;- [\&quot;starts_with(code,HB_)\&quot;]&lt;br&gt;- [\&quot;greater_than_equals(create_date, 2018-01-01)\&quot;] | [optional]

### Return type

[**\SkynamoClientAPI\Model\InlineResponse2009**](../Model/InlineResponse2009.md)

### Authorization

[api_key](../../README.md#api_key)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `customersIdGet()`

```php
customersIdGet($x_api_client, $id, $flags): \SkynamoClientAPI\Model\Customer
```

Get a customer

Fetch information about a specfic customer from their id

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKey('x-api-key', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKeyPrefix('x-api-key', 'Bearer');


$apiInstance = new SkynamoClientAPI\Api\CustomersApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$x_api_client = 'x_api_client_example'; // string | The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.<br><br>Example: <strong>demo</strong>.za.skynamo.me
$id = 56; // int | The unique identifier of a specific entity.
$flags = 'flags_example'; // string | Defines display configurations.<br><i>Availiable values</i> :<br> - show_nulls

try {
    $result = $apiInstance->customersIdGet($x_api_client, $id, $flags);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CustomersApi->customersIdGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **x_api_client** | **string**| The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.&lt;br&gt;&lt;br&gt;Example: &lt;strong&gt;demo&lt;/strong&gt;.za.skynamo.me |
 **id** | **int**| The unique identifier of a specific entity. |
 **flags** | **string**| Defines display configurations.&lt;br&gt;&lt;i&gt;Availiable values&lt;/i&gt; :&lt;br&gt; - show_nulls | [optional]

### Return type

[**\SkynamoClientAPI\Model\Customer**](../Model/Customer.md)

### Authorization

[api_key](../../README.md#api_key)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `customersPatch()`

```php
customersPatch($x_api_client, $customers): \SkynamoClientAPI\Model\InlineResponse2002
```

Update customers

Updates a set of customers with the provided list of customers

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKey('x-api-key', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKeyPrefix('x-api-key', 'Bearer');


$apiInstance = new SkynamoClientAPI\Api\CustomersApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$x_api_client = 'x_api_client_example'; // string | The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.<br><br>Example: <strong>demo</strong>.za.skynamo.me
$customers = array(new \SkynamoClientAPI\Model\CustomerPatch()); // \SkynamoClientAPI\Model\CustomerPatch[] | Update the provided customers in Skynamo

try {
    $result = $apiInstance->customersPatch($x_api_client, $customers);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CustomersApi->customersPatch: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **x_api_client** | **string**| The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.&lt;br&gt;&lt;br&gt;Example: &lt;strong&gt;demo&lt;/strong&gt;.za.skynamo.me |
 **customers** | [**\SkynamoClientAPI\Model\CustomerPatch[]**](../Model/CustomerPatch.md)| Update the provided customers in Skynamo | [optional]

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

## `customersPost()`

```php
customersPost($x_api_client, $customers): \SkynamoClientAPI\Model\InlineResponse20010
```

Create customers

Creates new customers

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKey('x-api-key', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKeyPrefix('x-api-key', 'Bearer');


$apiInstance = new SkynamoClientAPI\Api\CustomersApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$x_api_client = 'x_api_client_example'; // string | The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.<br><br>Example: <strong>demo</strong>.za.skynamo.me
$customers = array(new \SkynamoClientAPI\Model\CustomerPost()); // \SkynamoClientAPI\Model\CustomerPost[] | Add the provided customers to Skynamo

try {
    $result = $apiInstance->customersPost($x_api_client, $customers);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CustomersApi->customersPost: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **x_api_client** | **string**| The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.&lt;br&gt;&lt;br&gt;Example: &lt;strong&gt;demo&lt;/strong&gt;.za.skynamo.me |
 **customers** | [**\SkynamoClientAPI\Model\CustomerPost[]**](../Model/CustomerPost.md)| Add the provided customers to Skynamo | [optional]

### Return type

[**\SkynamoClientAPI\Model\InlineResponse20010**](../Model/InlineResponse20010.md)

### Authorization

[api_key](../../README.md#api_key)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `customersPut()`

```php
customersPut($x_api_client, $customers): \SkynamoClientAPI\Model\InlineResponse2002
```

Replace customers

Replaces a set of customers with the provided list of customers

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKey('x-api-key', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKeyPrefix('x-api-key', 'Bearer');


$apiInstance = new SkynamoClientAPI\Api\CustomersApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$x_api_client = 'x_api_client_example'; // string | The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.<br><br>Example: <strong>demo</strong>.za.skynamo.me
$customers = array(new \SkynamoClientAPI\Model\CustomerPut()); // \SkynamoClientAPI\Model\CustomerPut[] | Replace the provided customers in Skynamo

try {
    $result = $apiInstance->customersPut($x_api_client, $customers);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CustomersApi->customersPut: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **x_api_client** | **string**| The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.&lt;br&gt;&lt;br&gt;Example: &lt;strong&gt;demo&lt;/strong&gt;.za.skynamo.me |
 **customers** | [**\SkynamoClientAPI\Model\CustomerPut[]**](../Model/CustomerPut.md)| Replace the provided customers in Skynamo | [optional]

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
