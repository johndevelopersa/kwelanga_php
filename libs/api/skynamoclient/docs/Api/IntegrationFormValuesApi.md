# SkynamoClientAPI\IntegrationFormValuesApi

All URIs are relative to https://api.za.skynamo.me/v1.

Method | HTTP request | Description
------------- | ------------- | -------------
[**integrationformvaluesGet()**](IntegrationFormValuesApi.md#integrationformvaluesGet) | **GET** /integrationformvalues | Get integration form values


## `integrationformvaluesGet()`

```php
integrationformvaluesGet($x_api_client): \SkynamoClientAPI\Model\IntegrationFormValues
```

Get integration form values

Fetches the active integration form values.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKey('x-api-key', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKeyPrefix('x-api-key', 'Bearer');


$apiInstance = new SkynamoClientAPI\Api\IntegrationFormValuesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$x_api_client = 'x_api_client_example'; // string | The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.<br><br>Example: <strong>demo</strong>.za.skynamo.me

try {
    $result = $apiInstance->integrationformvaluesGet($x_api_client);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling IntegrationFormValuesApi->integrationformvaluesGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **x_api_client** | **string**| The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.&lt;br&gt;&lt;br&gt;Example: &lt;strong&gt;demo&lt;/strong&gt;.za.skynamo.me |

### Return type

[**\SkynamoClientAPI\Model\IntegrationFormValues**](../Model/IntegrationFormValues.md)

### Authorization

[api_key](../../README.md#api_key)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)
