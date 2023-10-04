# # InvoicePut

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **int** | The unique id of the invoice |
**date** | [**\DateTime**](\DateTime.md) | The date when the invoice was issued |
**customer_id** | **int** | The unique id of the customer that was invoiced - required if customer_code is not specified |
**customer_code** | **string** | The unique code of the customer that was invoiced - required if customer_id is not specified | [optional]
**reference** | **string** | The reference used to identify the invoice by a human or external system | [optional]
**status** | **string** | The status of the invoice | [optional]
**due_date** | [**\DateTime**](\DateTime.md) | The invoice due date | [optional]
**external_id** | **string** | The external id of the invoice | [optional]
**tax_inclusion** | **string** | States if the invoice is tax-inclusive ot tax-exclusive | [optional]
**tax** | **double** | The total tax amount of the invoice | [optional]
**total** | **double** | The total amount of the invoice | [optional]
**outstanding_balance** | **double** | The total outstanding balance of the invoice | [optional]
**items** | [**\SkynamoClientAPI\Model\InvoiceItem[]**](InvoiceItem.md) | A list of items included in the invoice | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
