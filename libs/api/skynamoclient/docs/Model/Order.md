# # Order

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **int** | The unique id of the order | [optional]
**date** | [**\DateTime**](\DateTime.md) | The date when the order was issued | [optional]
**customer_id** | **int** | The unique id of the customer that placed the order | [optional]
**customer_code** | **string** | The unique code of the customer that placed the order | [optional]
**customer_name** | **string** | The name of the customer that placed the order | [optional]
**reference** | **string** | The reference used to identify the order by a human or external system | [optional]
**interaction_id** | **int** | The unique id of the interaction of the order | [optional]
**discount** | **float** | The discount percentage on the order | [optional]
**discount_amount** | **float** | The discount amount on the order | [optional]
**total_amount** | **float** | The total amount on the order | [optional]
**prices_include_vat** | **bool** | Indicates whether the price is vat inclusive or not | [optional]
**warehouse_id** | **int** | The unique identifier of the warehouse associated with the stock level | [optional]
**warehouse_name** | **string** | The name of the warehouse associated with the stock level | [optional]
**email_recipients** | **string** | The email recipients on the order | [optional]
**last_modified_time** | [**\DateTime**](\DateTime.md) | The last time this order was modified | [optional]
**items** | [**\SkynamoClientAPI\Model\OrderItem[]**](OrderItem.md) | A list of items included in the order | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
