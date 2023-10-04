# SkynamoClientAPI\CustomfieldsApi

All URIs are relative to https://api.za.skynamo.me/v1.

Method | HTTP request | Description
------------- | ------------- | -------------
[**customfieldsPatch()**](CustomfieldsApi.md#customfieldsPatch) | **PATCH** /customfields | Update customfields


## `customfieldsPatch()`

```php
customfieldsPatch($x_api_client, $customfields): \SkynamoClientAPI\Model\InlineResponse2002
```

Update customfields

Update existing customfields

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKey('x-api-key', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKeyPrefix('x-api-key', 'Bearer');


$apiInstance = new SkynamoClientAPI\Api\CustomfieldsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$x_api_client = 'x_api_client_example'; // string | The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.<br><br>Example: <strong>demo</strong>.za.skynamo.me
$customfields = array(new \SkynamoClientAPI\Model\CustomfieldPatch()); // \SkynamoClientAPI\Model\CustomfieldPatch[] | A list of customfields request data

try {
    $result = $apiInstance->customfieldsPatch($x_api_client, $customfields);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CustomfieldsApi->customfieldsPatch: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **x_api_client** | **string**| The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.&lt;br&gt;&lt;br&gt;Example: &lt;strong&gt;demo&lt;/strong&gt;.za.skynamo.me |
 **customfields** | [**\SkynamoClientAPI\Model\CustomfieldPatch[]**](../Model/CustomfieldPatch.md)| A list of customfields request data | [optional]

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
