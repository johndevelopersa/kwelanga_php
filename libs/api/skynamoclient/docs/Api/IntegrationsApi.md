# SkynamoClientAPI\IntegrationsApi

All URIs are relative to https://api.za.skynamo.me/v1.

Method | HTTP request | Description
------------- | ------------- | -------------
[**integrationsPost()**](IntegrationsApi.md#integrationsPost) | **POST** /integrations | Execute an integration action


## `integrationsPost()`

```php
integrationsPost($x_api_client, $integration_request): \SkynamoClientAPI\Model\InlineResponse20014
```

Execute an integration action

Executes an integration action based on the action requested.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKey('x-api-key', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = SkynamoClientAPI\Configuration::getDefaultConfiguration()->setApiKeyPrefix('x-api-key', 'Bearer');


$apiInstance = new SkynamoClientAPI\Api\IntegrationsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$x_api_client = 'x_api_client_example'; // string | The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.<br><br>Example: <strong>demo</strong>.za.skynamo.me
$integration_request = new \SkynamoClientAPI\Model\IntegrationRequest(); // \SkynamoClientAPI\Model\IntegrationRequest | Execute the provided integration action in Skynamo

try {
    $result = $apiInstance->integrationsPost($x_api_client, $integration_request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling IntegrationsApi->integrationsPost: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **x_api_client** | **string**| The name of the Skynamo Instance the request is sent to. This is typically the company name or the first part of the URL used to access the Skynamo Instance.&lt;br&gt;&lt;br&gt;Example: &lt;strong&gt;demo&lt;/strong&gt;.za.skynamo.me |
 **integration_request** | [**\SkynamoClientAPI\Model\IntegrationRequest**](../Model/IntegrationRequest.md)| Execute the provided integration action in Skynamo | [optional]

### Return type

[**\SkynamoClientAPI\Model\InlineResponse20014**](../Model/InlineResponse20014.md)

### Authorization

[api_key](../../README.md#api_key)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)
