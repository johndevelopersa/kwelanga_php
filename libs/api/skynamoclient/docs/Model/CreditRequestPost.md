# # CreditRequestPost

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**date** | [**\DateTime**](\DateTime.md) | The date when the credit request was issued |
**customer_id** | **int** | The unique id of the customer that placed the credit request |
**user_id** | **int** | The unique id of the user that placed the credit request |
**discount** | **float** | The discount percentage on the credit request | [optional]
**prices_include_vat** | **bool** | Indicates whether the price is vat inclusive or not | [optional]
**warehouse_id** | **int** | The unique identifier of the warehouse associated with the stock level | [optional]
**transaction_id** | **int** | The transaction id associated with files in order to link files | [optional]
**items** | [**\SkynamoClientAPI\Model\CreditRequestItemPost[]**](CreditRequestItemPost.md) | A list of items included in the credit request |
**forms** | [**\SkynamoClientAPI\Model\CreditRequestForms[]**](CreditRequestForms.md) | Certain custom fields may be required depending on the custom fields that have been set up | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
