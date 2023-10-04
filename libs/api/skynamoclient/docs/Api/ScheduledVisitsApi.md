# SkynamoClientAPI\ScheduledVisitsApi

All URIs are relative to https://api.za.skynamo.me/v1.

Method | HTTP request | Description
------------- | ------------- | -------------
[**scheduledvisitsDelete()**](ScheduledVisitsApi.md#scheduledvisitsDelete) | **DELETE** /scheduledvisits | Delete existing scheduled visits
[**scheduledvisitsGet()**](ScheduledVisitsApi.md#scheduledvisitsGet) | **GET** /scheduledvisits | List scheduled visits
[**scheduledvisitsIdGet()**](ScheduledVisitsApi.md#scheduledvisitsIdGet) | **GET** /scheduledvisits/{id} | Get a scheduled visit
[**scheduledvisitsPatch()**](ScheduledVisitsApi.md#scheduledvisitsPatch) | **PATCH** /scheduledvisits | Update scheduled visits
[**scheduledvisitsPost()**](ScheduledVisitsApi.md#scheduledvisitsPost) | **POST** /scheduledvisits | Create scheduled visits
[**scheduledvisitsPut()**](ScheduledVisitsApi.md#scheduledvisitsPut) | **PUT** /scheduledvisits | Replace scheduled visits


## `scheduledvisitsDelete()`

```php
scheduledvisitsDelete($x_api_client, $ids): \SkynamoClientAPI\Model\InlineResponse2002
```

Delete existing scheduled visits

Delete existing scheduled visits

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKey('x-api-key', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKeyPrefix('x-api-key', 'Bearer');


$apiInstance = new SkynamoClientAPI\Api\ScheduledVisitsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$x_api_client = 'x_api_client_example'; // string | The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.<br><br>Example: <strong>demo</strong>.za.skynamo.me
$ids = array(56); // int[] | A list of scheduled visit IDs that should be deleted

try {
    $result = $apiInstance->scheduledvisitsDelete($x_api_client, $ids);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ScheduledVisitsApi->scheduledvisitsDelete: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **x_api_client** | **string**| The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.&lt;br&gt;&lt;br&gt;Example: &lt;strong&gt;demo&lt;/strong&gt;.za.skynamo.me |
 **ids** | [**int[]**](../Model/int.md)| A list of scheduled visit IDs that should be deleted |

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

## `scheduledvisitsGet()`

```php
scheduledvisitsGet($x_api_client, $page_number, $page_size, $flags, $filters): \SkynamoClientAPI\Model\InlineResponse20023
```

List scheduled visits

Returns a list of scheduled visits

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKey('x-api-key', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKeyPrefix('x-api-key', 'Bearer');


$apiInstance = new SkynamoClientAPI\Api\ScheduledVisitsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$x_api_client = 'x_api_client_example'; // string | The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.<br><br>Example: <strong>demo</strong>.za.skynamo.me
$page_number = 1; // int | Defines the page number.
$page_size = 50; // int | Defines the size of each page. (Maximum = 200)
$flags = 'flags_example'; // string | Defines display configurations.<br><i>Availiable values</i> :<br> - show_nulls
$filters = 'filters_example'; // string | Used to filter information.<br><br><i>Filters available</i> : <br>- less_than<br>- less_than_equals<br>-  greater_than<br>- greater_than_equals<br>- equals<br>- starts_with<br> <br><i>Available filter parameters</i>:<br>- id<br>- create_date<br>- row_version<br><br><i>Examples</i>:<br>- [\"less_than(id,5)\"]<br>- [\"greater_than_equals(create_date, 2018-01-01)\"]

try {
    $result = $apiInstance->scheduledvisitsGet($x_api_client, $page_number, $page_size, $flags, $filters);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ScheduledVisitsApi->scheduledvisitsGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **x_api_client** | **string**| The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.&lt;br&gt;&lt;br&gt;Example: &lt;strong&gt;demo&lt;/strong&gt;.za.skynamo.me |
 **page_number** | **int**| Defines the page number. | [optional] [default to 1]
 **page_size** | **int**| Defines the size of each page. (Maximum &#x3D; 200) | [optional] [default to 50]
 **flags** | **string**| Defines display configurations.&lt;br&gt;&lt;i&gt;Availiable values&lt;/i&gt; :&lt;br&gt; - show_nulls | [optional]
 **filters** | **string**| Used to filter information.&lt;br&gt;&lt;br&gt;&lt;i&gt;Filters available&lt;/i&gt; : &lt;br&gt;- less_than&lt;br&gt;- less_than_equals&lt;br&gt;-  greater_than&lt;br&gt;- greater_than_equals&lt;br&gt;- equals&lt;br&gt;- starts_with&lt;br&gt; &lt;br&gt;&lt;i&gt;Available filter parameters&lt;/i&gt;:&lt;br&gt;- id&lt;br&gt;- create_date&lt;br&gt;- row_version&lt;br&gt;&lt;br&gt;&lt;i&gt;Examples&lt;/i&gt;:&lt;br&gt;- [\&quot;less_than(id,5)\&quot;]&lt;br&gt;- [\&quot;greater_than_equals(create_date, 2018-01-01)\&quot;] | [optional]

### Return type

[**\SkynamoClientAPI\Model\InlineResponse20023**](../Model/InlineResponse20023.md)

### Authorization

[api_key](../../README.md#api_key)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `scheduledvisitsIdGet()`

```php
scheduledvisitsIdGet($x_api_client, $id, $flags): \SkynamoClientAPI\Model\ScheduledVisit
```

Get a scheduled visit

Fetch information about a specfic scheduled visit from the unique identifier

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKey('x-api-key', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKeyPrefix('x-api-key', 'Bearer');


$apiInstance = new SkynamoClientAPI\Api\ScheduledVisitsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$x_api_client = 'x_api_client_example'; // string | The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.<br><br>Example: <strong>demo</strong>.za.skynamo.me
$id = 56; // int | The unique identifier of a specific entity.
$flags = 'flags_example'; // string | Defines display configurations.<br><i>Availiable values</i> :<br> - show_nulls

try {
    $result = $apiInstance->scheduledvisitsIdGet($x_api_client, $id, $flags);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ScheduledVisitsApi->scheduledvisitsIdGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **x_api_client** | **string**| The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.&lt;br&gt;&lt;br&gt;Example: &lt;strong&gt;demo&lt;/strong&gt;.za.skynamo.me |
 **id** | **int**| The unique identifier of a specific entity. |
 **flags** | **string**| Defines display configurations.&lt;br&gt;&lt;i&gt;Availiable values&lt;/i&gt; :&lt;br&gt; - show_nulls | [optional]

### Return type

[**\SkynamoClientAPI\Model\ScheduledVisit**](../Model/ScheduledVisit.md)

### Authorization

[api_key](../../README.md#api_key)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `scheduledvisitsPatch()`

```php
scheduledvisitsPatch($x_api_client, $scheduledvisits): \SkynamoClientAPI\Model\InlineResponse2002
```

Update scheduled visits

Update existing scheduled visits

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKey('x-api-key', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKeyPrefix('x-api-key', 'Bearer');


$apiInstance = new SkynamoClientAPI\Api\ScheduledVisitsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$x_api_client = 'x_api_client_example'; // string | The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.<br><br>Example: <strong>demo</strong>.za.skynamo.me
$scheduledvisits = array(new \SkynamoClientAPI\Model\ScheduledVisitPatch()); // \SkynamoClientAPI\Model\ScheduledVisitPatch[] | A list of scheduled visits request data

try {
    $result = $apiInstance->scheduledvisitsPatch($x_api_client, $scheduledvisits);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ScheduledVisitsApi->scheduledvisitsPatch: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **x_api_client** | **string**| The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.&lt;br&gt;&lt;br&gt;Example: &lt;strong&gt;demo&lt;/strong&gt;.za.skynamo.me |
 **scheduledvisits** | [**\SkynamoClientAPI\Model\ScheduledVisitPatch[]**](../Model/ScheduledVisitPatch.md)| A list of scheduled visits request data | [optional]

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

## `scheduledvisitsPost()`

```php
scheduledvisitsPost($x_api_client, $scheduledvisits): \SkynamoClientAPI\Model\InlineResponse2008
```

Create scheduled visits

Create new scheduled visits

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKey('x-api-key', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKeyPrefix('x-api-key', 'Bearer');


$apiInstance = new SkynamoClientAPI\Api\ScheduledVisitsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$x_api_client = 'x_api_client_example'; // string | The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.<br><br>Example: <strong>demo</strong>.za.skynamo.me
$scheduledvisits = array(new \SkynamoClientAPI\Model\ScheduledVisitPost()); // \SkynamoClientAPI\Model\ScheduledVisitPost[] | A list of scheduled visits request data

try {
    $result = $apiInstance->scheduledvisitsPost($x_api_client, $scheduledvisits);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ScheduledVisitsApi->scheduledvisitsPost: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **x_api_client** | **string**| The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.&lt;br&gt;&lt;br&gt;Example: &lt;strong&gt;demo&lt;/strong&gt;.za.skynamo.me |
 **scheduledvisits** | [**\SkynamoClientAPI\Model\ScheduledVisitPost[]**](../Model/ScheduledVisitPost.md)| A list of scheduled visits request data | [optional]

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

## `scheduledvisitsPut()`

```php
scheduledvisitsPut($x_api_client, $scheduledvisits): \SkynamoClientAPI\Model\InlineResponse2002
```

Replace scheduled visits

Replace existing scheduled visits

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKey('x-api-key', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKeyPrefix('x-api-key', 'Bearer');


$apiInstance = new SkynamoClientAPI\Api\ScheduledVisitsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$x_api_client = 'x_api_client_example'; // string | The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.<br><br>Example: <strong>demo</strong>.za.skynamo.me
$scheduledvisits = array(new \SkynamoClientAPI\Model\ScheduledVisitPut()); // \SkynamoClientAPI\Model\ScheduledVisitPut[] | A list of scheduled visits request data

try {
    $result = $apiInstance->scheduledvisitsPut($x_api_client, $scheduledvisits);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ScheduledVisitsApi->scheduledvisitsPut: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **x_api_client** | **string**| The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.&lt;br&gt;&lt;br&gt;Example: &lt;strong&gt;demo&lt;/strong&gt;.za.skynamo.me |
 **scheduledvisits** | [**\SkynamoClientAPI\Model\ScheduledVisitPut[]**](../Model/ScheduledVisitPut.md)| A list of scheduled visits request data | [optional]

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
