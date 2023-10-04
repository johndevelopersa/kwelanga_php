# # Invoice

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **int** | The unique id of the invoice | [optional]
**date** | [**\DateTime**](\DateTime.md) | The date when the invoice was issued | [optional]
**customer_id** | **int** | The unique id of the customer that was invoiced | [optional]
**customer_code** | **string** | The unique code of the customer that was invoiced | [optional]
**reference** | **string** | The reference used to identify the invoice by a human or external system | [optional]
**row_version** | **float** | A sequence number for changes to the invoice (if the number changes then the invoice has changed) | [optional]
**last_modified_time** | [**\DateTime**](\DateTime.md) | The last time this invoice was modified | [optional]
**status** | **string** | The status of the invoice | [optional]
**due_date** | [**\DateTime**](\DateTime.md) | The invoice due date | [optional]
**external_id** | **string** | The external id of the invoice | [optional]
**tax_inclusion** | **string** | States if the invoice is tax-inclusive ot tax-exclusive | [optional]
**tax** | **double** | The total tax amount of the invoice | [optional]
**total** | **double** | The total amount of the invoice | [optional]
**outstanding_balance** | **double** | The total outstanding balance of the invoice | [optional]
**items** | [**\SkynamoClientAPI\Model\InvoiceItem[]**](InvoiceItem.md) | A list of items included in the invoice | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
