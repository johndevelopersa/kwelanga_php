# # Quote

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **int** | The unique id of the quote | [optional]
**date** | [**\DateTime**](\DateTime.md) | The date when the quote was issued | [optional]
**customer_id** | **int** | The unique id of the customer that placed the quote | [optional]
**customer_code** | **string** | The unique code of the customer that placed the quote | [optional]
**customer_name** | **string** | The name of the customer that placed the quote | [optional]
**reference** | **string** | The reference used to identify the quote by a human or external system | [optional]
**discount** | **float** | The discount percentage on the quote | [optional]
**discount_amount** | **float** | The discount amount on the quote | [optional]
**total_amount** | **float** | The total amount on the quote | [optional]
**prices_include_vat** | **bool** | Indicates whether the price is vat inclusive or not | [optional]
**warehouse_id** | **int** | The unique identifier of the warehouse associated with the quote | [optional]
**warehouse_name** | **string** | The name of the warehouse associated with the quote | [optional]
**email_recipients** | **string** | The email recipients on the quote | [optional]
**last_modified_time** | [**\DateTime**](\DateTime.md) | The last time this quote was modified | [optional]
**items** | [**\SkynamoClientAPI\Model\QuoteItem[]**](QuoteItem.md) | A list of items included in the quote | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
