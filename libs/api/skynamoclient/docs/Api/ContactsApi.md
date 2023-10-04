# SkynamoClientAPI\ContactsApi

All URIs are relative to https://api.za.skynamo.me/v1.

Method | HTTP request | Description
------------- | ------------- | -------------
[**contactsGet()**](ContactsApi.md#contactsGet) | **GET** /contacts | List contacts
[**contactsIdGet()**](ContactsApi.md#contactsIdGet) | **GET** /contacts/{id} | Get a contact
[**contactsPatch()**](ContactsApi.md#contactsPatch) | **PATCH** /contacts | Update contacts
[**contactsPost()**](ContactsApi.md#contactsPost) | **POST** /contacts | Create contacts
[**contactsPut()**](ContactsApi.md#contactsPut) | **PUT** /contacts | Replace contacts


## `contactsGet()`

```php
contactsGet($x_api_client, $page_number, $page_size, $flags, $filters): \SkynamoClientAPI\Model\InlineResponse2001
```

List contacts

Returns a list of contacts

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKey('x-api-key', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKeyPrefix('x-api-key', 'Bearer');


$apiInstance = new SkynamoClientAPI\Api\ContactsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$x_api_client = 'x_api_client_example'; // string | The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.<br><br>Example: <strong>demo</strong>.za.skynamo.me
$page_number = 1; // int | Defines the page number.
$page_size = 50; // int | Defines the size of each page. (Maximum = 200)
$flags = 'flags_example'; // string | Defines display configurations.<br><i>Availiable values</i> :<br> - show_nulls
$filters = 'filters_example'; // string | Used to filter information.<br><br><i>Filters available</i> : <br>- less_than<br>- less_than_equals<br>-  greater_than<br>- greater_than_equals<br>- equals<br>- starts_with<br> <br><i>Available filter parameters</i>:<br>- id<br>- active<br>- create_date<br>- row_version<br>- customer_id?<br>- customer_code?<br><br><i>Examples</i>:<br>- [\"less_than(id,5)\"]<br>- [\"starts_with(code,HB_)\"]<br>- [\"greater_than_equals(create_date, 2018-01-01)\"]

try {
    $result = $apiInstance->contactsGet($x_api_client, $page_number, $page_size, $flags, $filters);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ContactsApi->contactsGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **x_api_client** | **string**| The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.&lt;br&gt;&lt;br&gt;Example: &lt;strong&gt;demo&lt;/strong&gt;.za.skynamo.me |
 **page_number** | **int**| Defines the page number. | [optional] [default to 1]
 **page_size** | **int**| Defines the size of each page. (Maximum &#x3D; 200) | [optional] [default to 50]
 **flags** | **string**| Defines display configurations.&lt;br&gt;&lt;i&gt;Availiable values&lt;/i&gt; :&lt;br&gt; - show_nulls | [optional]
 **filters** | **string**| Used to filter information.&lt;br&gt;&lt;br&gt;&lt;i&gt;Filters available&lt;/i&gt; : &lt;br&gt;- less_than&lt;br&gt;- less_than_equals&lt;br&gt;-  greater_than&lt;br&gt;- greater_than_equals&lt;br&gt;- equals&lt;br&gt;- starts_with&lt;br&gt; &lt;br&gt;&lt;i&gt;Available filter parameters&lt;/i&gt;:&lt;br&gt;- id&lt;br&gt;- active&lt;br&gt;- create_date&lt;br&gt;- row_version&lt;br&gt;- customer_id?&lt;br&gt;- customer_code?&lt;br&gt;&lt;br&gt;&lt;i&gt;Examples&lt;/i&gt;:&lt;br&gt;- [\&quot;less_than(id,5)\&quot;]&lt;br&gt;- [\&quot;starts_with(code,HB_)\&quot;]&lt;br&gt;- [\&quot;greater_than_equals(create_date, 2018-01-01)\&quot;] | [optional]

### Return type

[**\SkynamoClientAPI\Model\InlineResponse2001**](../Model/InlineResponse2001.md)

### Authorization

[api_key](../../README.md#api_key)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `contactsIdGet()`

```php
contactsIdGet($x_api_client, $id, $flags): \SkynamoClientAPI\Model\Contact
```

Get a contact

Fetch information about a specfic contact using an id

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKey('x-api-key', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKeyPrefix('x-api-key', 'Bearer');


$apiInstance = new SkynamoClientAPI\Api\ContactsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$x_api_client = 'x_api_client_example'; // string | The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.<br><br>Example: <strong>demo</strong>.za.skynamo.me
$id = 56; // int | The unique identifier of a specific entity.
$flags = 'flags_example'; // string | Defines display configurations.<br><i>Availiable values</i> :<br> - show_nulls

try {
    $result = $apiInstance->contactsIdGet($x_api_client, $id, $flags);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ContactsApi->contactsIdGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **x_api_client** | **string**| The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.&lt;br&gt;&lt;br&gt;Example: &lt;strong&gt;demo&lt;/strong&gt;.za.skynamo.me |
 **id** | **int**| The unique identifier of a specific entity. |
 **flags** | **string**| Defines display configurations.&lt;br&gt;&lt;i&gt;Availiable values&lt;/i&gt; :&lt;br&gt; - show_nulls | [optional]

### Return type

[**\SkynamoClientAPI\Model\Contact**](../Model/Contact.md)

### Authorization

[api_key](../../README.md#api_key)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `contactsPatch()`

```php
contactsPatch($x_api_client, $contacts): \SkynamoClientAPI\Model\InlineResponse2002
```

Update contacts

Update existing contacts

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKey('x-api-key', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKeyPrefix('x-api-key', 'Bearer');


$apiInstance = new SkynamoClientAPI\Api\ContactsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$x_api_client = 'x_api_client_example'; // string | The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.<br><br>Example: <strong>demo</strong>.za.skynamo.me
$contacts = array(new \SkynamoClientAPI\Model\ContactPatch()); // \SkynamoClientAPI\Model\ContactPatch[] | A list of contacts request data

try {
    $result = $apiInstance->contactsPatch($x_api_client, $contacts);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ContactsApi->contactsPatch: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **x_api_client** | **string**| The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.&lt;br&gt;&lt;br&gt;Example: &lt;strong&gt;demo&lt;/strong&gt;.za.skynamo.me |
 **contacts** | [**\SkynamoClientAPI\Model\ContactPatch[]**](../Model/ContactPatch.md)| A list of contacts request data | [optional]

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

## `contactsPost()`

```php
contactsPost($x_api_client, $contacts): \SkynamoClientAPI\Model\InlineResponse2003
```

Create contacts

Create new contacts

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKey('x-api-key', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKeyPrefix('x-api-key', 'Bearer');


$apiInstance = new SkynamoClientAPI\Api\ContactsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$x_api_client = 'x_api_client_example'; // string | The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.<br><br>Example: <strong>demo</strong>.za.skynamo.me
$contacts = array(new \SkynamoClientAPI\Model\ContactPost()); // \SkynamoClientAPI\Model\ContactPost[] | A list of contacts request data

try {
    $result = $apiInstance->contactsPost($x_api_client, $contacts);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ContactsApi->contactsPost: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **x_api_client** | **string**| The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.&lt;br&gt;&lt;br&gt;Example: &lt;strong&gt;demo&lt;/strong&gt;.za.skynamo.me |
 **contacts** | [**\SkynamoClientAPI\Model\ContactPost[]**](../Model/ContactPost.md)| A list of contacts request data | [optional]

### Return type

[**\SkynamoClientAPI\Model\InlineResponse2003**](../Model/InlineResponse2003.md)

### Authorization

[api_key](../../README.md#api_key)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `contactsPut()`

```php
contactsPut($x_api_client, $contacts): \SkynamoClientAPI\Model\InlineResponse2002
```

Replace contacts

Replace existing contacts

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKey('x-api-key', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKeyPrefix('x-api-key', 'Bearer');


$apiInstance = new SkynamoClientAPI\Api\ContactsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$x_api_client = 'x_api_client_example'; // string | The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.<br><br>Example: <strong>demo</strong>.za.skynamo.me
$contacts = array(new \SkynamoClientAPI\Model\ContactPut()); // \SkynamoClientAPI\Model\ContactPut[] | A list of contacts request data

try {
    $result = $apiInstance->contactsPut($x_api_client, $contacts);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ContactsApi->contactsPut: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **x_api_client** | **string**| The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.&lt;br&gt;&lt;br&gt;Example: &lt;strong&gt;demo&lt;/strong&gt;.za.skynamo.me |
 **contacts** | [**\SkynamoClientAPI\Model\ContactPut[]**](../Model/ContactPut.md)| A list of contacts request data | [optional]

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
