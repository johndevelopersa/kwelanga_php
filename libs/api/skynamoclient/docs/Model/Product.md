# # Product

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **int** | The unique id of the product | [optional]
**row_version** | **float** | A sequence number for changes to the product (if the number changes then the product has changed) | [optional]
**code** | **string** | The unique code associated with this product | [optional]
**name** | **string** | The name of the product | [optional]
**active** | **bool** | Whether or not the product is active | [optional] [default to true]
**order_units** | [**\SkynamoClientAPI\Model\OrderUnit[]**](OrderUnit.md) | List of all the order units associated with a product | [optional]
**last_modified_time** | [**\DateTime**](\DateTime.md) | The last time this product was modified | [optional]
**custom_fields** | [**\SkynamoClientAPI\Model\CustomField[]**](CustomField.md) | Certain custom fields may be required depending on the custom fields that have been set up | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
