# # CustomerPut

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **int** | The unique id of the customer |
**code** | **string** | The unique code associated with this customer | [optional]
**name** | **string** | The name of the customer | [optional]
**active** | **bool** | Whether or not the customer is active | [optional] [default to true]
**location** | [**\SkynamoClientAPI\Model\Location**](Location.md) |  | [optional]
**default_discount** | **float** | The default discount applied for this customer when creating orders | [optional] [default to 0.0]
**price_list_id** | **int** | The unique identifier of the price list associated with this customer (Alternative to priceListName) | [optional]
**price_list_name** | **string** | The name of the price list associated with this customer (Alternative to priceListID - ignored if priceListID is specified) | [optional]
**assigned_users** | **int[]** | List of user ids that are assigned to this customer | [optional]
**default_warehouse_id** | **int** | The unique identifier of the warehouse associated with this customer | [optional]
**custom_fields** | [**\SkynamoClientAPI\Model\CustomField[]**](CustomField.md) | Certain custom fields may be required depending on the custom fields that have been set up | [optional]
**transaction_id** | **int** | The transaction id associated with files in order to link files to a customer | [optional]
**files** | **string[]** | List of file Guids | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
