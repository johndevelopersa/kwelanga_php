# # ProductPost

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**code** | **string** | The unique code associated with this product (automatically generated if not supplied) | [optional]
**name** | **string** | The name of the product |
**active** | **bool** | Whether or not the product is active | [optional] [default to true]
**order_units** | [**\SkynamoClientAPI\Model\OrderUnit[]**](OrderUnit.md) | List of all the order units associated with a product | [optional]
**custom_fields** | [**\SkynamoClientAPI\Model\CustomField[]**](CustomField.md) | Certain custom fields may be required depending on the custom fields that have been set up | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
