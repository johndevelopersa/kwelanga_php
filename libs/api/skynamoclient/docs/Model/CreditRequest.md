# # CreditRequest

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **int** | The unique id of the credit request | [optional]
**date** | [**\DateTime**](\DateTime.md) | The date when the credit request was issued | [optional]
**customer_id** | **int** | The unique id of the customer that placed the credit request | [optional]
**customer_code** | **string** | The unique code of the customer that placed the credit request | [optional]
**customer_name** | **string** | The name of the customer that placed the credit request | [optional]
**reference** | **string** | The reference used to identify the credit request by a human or external system | [optional]
**discount** | **float** | The discount percentage on the credit request | [optional]
**discount_amount** | **float** | The discount amount on the credit request | [optional]
**total_amount** | **float** | The total amount on the credit request | [optional]
**prices_include_vat** | **bool** | Indicates whether the price is vat inclusive or not | [optional]
**warehouse_id** | **int** | The unique identifier of the warehouse associated with the credit request | [optional]
**warehouse_name** | **string** | The name of the warehouse associated with the credit request | [optional]
**email_recipients** | **string** | The email recipients on the credit request | [optional]
**last_modified_time** | [**\DateTime**](\DateTime.md) | The last time this credit request was modified | [optional]
**items** | [**\SkynamoClientAPI\Model\CreditRequestItem[]**](CreditRequestItem.md) | A list of items included in the credit request | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
