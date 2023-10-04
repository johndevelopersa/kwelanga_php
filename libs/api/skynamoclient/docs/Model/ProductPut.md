# # ProductPut

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **int** | The unique id of the product |
**code** | **string** | The unique code associated with this product | [optional]
**name** | **string** | The name of the product | [optional]
**active** | **bool** | Whether or not the product is active | [optional] [default to true]
**order_units** | [**\SkynamoClientAPI\Model\OrderUnit[]**](OrderUnit.md) | List of all the order units associated with a product | [optional]
**custom_fields** | [**\SkynamoClientAPI\Model\CustomField[]**](CustomField.md) | Certain custom fields may be required depending on the custom fields that have been set up | [optional]
**transaction_id** | **int** | The transaction id associated with files in order to link files to a product | [optional]
**files** | **string[]** | List of file Guids | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
