# SkynamoClientAPI\VisitFrequenciesApi

All URIs are relative to https://api.za.skynamo.me/v1.

Method | HTTP request | Description
------------- | ------------- | -------------
[**visitfrequenciesGet()**](VisitFrequenciesApi.md#visitfrequenciesGet) | **GET** /visitfrequencies | List visit frequencies
[**visitfrequenciesIdGet()**](VisitFrequenciesApi.md#visitfrequenciesIdGet) | **GET** /visitfrequencies/{id} | Get a visit frequency
[**visitfrequenciesPatch()**](VisitFrequenciesApi.md#visitfrequenciesPatch) | **PATCH** /visitfrequencies | Update visit frequencies
[**visitfrequenciesPost()**](VisitFrequenciesApi.md#visitfrequenciesPost) | **POST** /visitfrequencies | Create visit frequencies
[**visitfrequenciesPut()**](VisitFrequenciesApi.md#visitfrequenciesPut) | **PUT** /visitfrequencies | Replace visit frequencies


## `visitfrequenciesGet()`

```php
visitfrequenciesGet($x_api_client, $page_number, $page_size, $flags, $filters): \SkynamoClientAPI\Model\InlineResponse20029
```

List visit frequencies

Returns a list of visit frequencies

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKey('x-api-key', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKeyPrefix('x-api-key', 'Bearer');


$apiInstance = new SkynamoClientAPI\Api\VisitFrequenciesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$x_api_client = 'x_api_client_example'; // string | The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.<br><br>Example: <strong>demo</strong>.za.skynamo.me
$page_number = 1; // int | Defines the page number.
$page_size = 50; // int | Defines the size of each page. (Maximum = 200)
$flags = 'flags_example'; // string | Defines display configurations.<br><i>Availiable values</i> :<br> - show_nulls
$filters = 'filters_example'; // string | Used to filter information.<br><br><i>Filters available</i> : <br>- less_than<br>- less_than_equals<br>-  greater_than<br>- greater_than_equals<br>- equals<br>- starts_with<br> <br><i>Available filter parameters</i>:<br>- id<br>- create_date<br>- row_version<br><br><i>Examples</i>:<br>- [\"less_than(id,5)\"]<br>- [\"greater_than(row_version, 13421541)\"]

try {
    $result = $apiInstance->visitfrequenciesGet($x_api_client, $page_number, $page_size, $flags, $filters);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling VisitFrequenciesApi->visitfrequenciesGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **x_api_client** | **string**| The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.&lt;br&gt;&lt;br&gt;Example: &lt;strong&gt;demo&lt;/strong&gt;.za.skynamo.me |
 **page_number** | **int**| Defines the page number. | [optional] [default to 1]
 **page_size** | **int**| Defines the size of each page. (Maximum &#x3D; 200) | [optional] [default to 50]
 **flags** | **string**| Defines display configurations.&lt;br&gt;&lt;i&gt;Availiable values&lt;/i&gt; :&lt;br&gt; - show_nulls | [optional]
 **filters** | **string**| Used to filter information.&lt;br&gt;&lt;br&gt;&lt;i&gt;Filters available&lt;/i&gt; : &lt;br&gt;- less_than&lt;br&gt;- less_than_equals&lt;br&gt;-  greater_than&lt;br&gt;- greater_than_equals&lt;br&gt;- equals&lt;br&gt;- starts_with&lt;br&gt; &lt;br&gt;&lt;i&gt;Available filter parameters&lt;/i&gt;:&lt;br&gt;- id&lt;br&gt;- create_date&lt;br&gt;- row_version&lt;br&gt;&lt;br&gt;&lt;i&gt;Examples&lt;/i&gt;:&lt;br&gt;- [\&quot;less_than(id,5)\&quot;]&lt;br&gt;- [\&quot;greater_than(row_version, 13421541)\&quot;] | [optional]

### Return type

[**\SkynamoClientAPI\Model\InlineResponse20029**](../Model/InlineResponse20029.md)

### Authorization

[api_key](../../README.md#api_key)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `visitfrequenciesIdGet()`

```php
visitfrequenciesIdGet($x_api_client, $id, $flags): \SkynamoClientAPI\Model\VisitFrequency
```

Get a visit frequency

Fetch information about a specific visit frequency using an id

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKey('x-api-key', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKeyPrefix('x-api-key', 'Bearer');


$apiInstance = new SkynamoClientAPI\Api\VisitFrequenciesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$x_api_client = 'x_api_client_example'; // string | The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.<br><br>Example: <strong>demo</strong>.za.skynamo.me
$id = 56; // int | The unique identifier of a specific entity.
$flags = 'flags_example'; // string | Defines display configurations.<br><i>Availiable values</i> :<br> - show_nulls

try {
    $result = $apiInstance->visitfrequenciesIdGet($x_api_client, $id, $flags);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling VisitFrequenciesApi->visitfrequenciesIdGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **x_api_client** | **string**| The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.&lt;br&gt;&lt;br&gt;Example: &lt;strong&gt;demo&lt;/strong&gt;.za.skynamo.me |
 **id** | **int**| The unique identifier of a specific entity. |
 **flags** | **string**| Defines display configurations.&lt;br&gt;&lt;i&gt;Availiable values&lt;/i&gt; :&lt;br&gt; - show_nulls | [optional]

### Return type

[**\SkynamoClientAPI\Model\VisitFrequency**](../Model/VisitFrequency.md)

### Authorization

[api_key](../../README.md#api_key)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `visitfrequenciesPatch()`

```php
visitfrequenciesPatch($x_api_client, $visitfrequencies): \SkynamoClientAPI\Model\InlineResponse2002
```

Update visit frequencies

Update existing visit frequencies

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKey('x-api-key', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKeyPrefix('x-api-key', 'Bearer');


$apiInstance = new SkynamoClientAPI\Api\VisitFrequenciesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$x_api_client = 'x_api_client_example'; // string | The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.<br><br>Example: <strong>demo</strong>.za.skynamo.me
$visitfrequencies = array(new \SkynamoClientAPI\Model\VisitFrequencyPatch()); // \SkynamoClientAPI\Model\VisitFrequencyPatch[] | A list of visit frequency request data

try {
    $result = $apiInstance->visitfrequenciesPatch($x_api_client, $visitfrequencies);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling VisitFrequenciesApi->visitfrequenciesPatch: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **x_api_client** | **string**| The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.&lt;br&gt;&lt;br&gt;Example: &lt;strong&gt;demo&lt;/strong&gt;.za.skynamo.me |
 **visitfrequencies** | [**\SkynamoClientAPI\Model\VisitFrequencyPatch[]**](../Model/VisitFrequencyPatch.md)| A list of visit frequency request data | [optional]

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

## `visitfrequenciesPost()`

```php
visitfrequenciesPost($x_api_client, $visitfrequencies): \SkynamoClientAPI\Model\InlineResponse20030
```

Create visit frequencies

Create new visit frequencies

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKey('x-api-key', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKeyPrefix('x-api-key', 'Bearer');


$apiInstance = new SkynamoClientAPI\Api\VisitFrequenciesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$x_api_client = 'x_api_client_example'; // string | The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.<br><br>Example: <strong>demo</strong>.za.skynamo.me
$visitfrequencies = array(new \SkynamoClientAPI\Model\VisitFrequencyPost()); // \SkynamoClientAPI\Model\VisitFrequencyPost[] | A list of visit frequency request data

try {
    $result = $apiInstance->visitfrequenciesPost($x_api_client, $visitfrequencies);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling VisitFrequenciesApi->visitfrequenciesPost: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **x_api_client** | **string**| The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.&lt;br&gt;&lt;br&gt;Example: &lt;strong&gt;demo&lt;/strong&gt;.za.skynamo.me |
 **visitfrequencies** | [**\SkynamoClientAPI\Model\VisitFrequencyPost[]**](../Model/VisitFrequencyPost.md)| A list of visit frequency request data | [optional]

### Return type

[**\SkynamoClientAPI\Model\InlineResponse20030**](../Model/InlineResponse20030.md)

### Authorization

[api_key](../../README.md#api_key)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `visitfrequenciesPut()`

```php
visitfrequenciesPut($x_api_client, $visitfrequencies): \SkynamoClientAPI\Model\InlineResponse2002
```

Replace visit frequencies

Replace existing visit frequencies

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKey('x-api-key', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKeyPrefix('x-api-key', 'Bearer');


$apiInstance = new SkynamoClientAPI\Api\VisitFrequenciesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$x_api_client = 'x_api_client_example'; // string | The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.<br><br>Example: <strong>demo</strong>.za.skynamo.me
$visitfrequencies = array(new \SkynamoClientAPI\Model\VisitFrequencyPut()); // \SkynamoClientAPI\Model\VisitFrequencyPut[] | A list of visit frequency request data

try {
    $result = $apiInstance->visitfrequenciesPut($x_api_client, $visitfrequencies);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling VisitFrequenciesApi->visitfrequenciesPut: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **x_api_client** | **string**| The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.&lt;br&gt;&lt;br&gt;Example: &lt;strong&gt;demo&lt;/strong&gt;.za.skynamo.me |
 **visitfrequencies** | [**\SkynamoClientAPI\Model\VisitFrequencyPut[]**](../Model/VisitFrequencyPut.md)| A list of visit frequency request data | [optional]

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
