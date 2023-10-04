# # CustomerCommentPost

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**comment** | **string** | The comment |
**customer_id** | **int** | The unique identifier of the customer where the comment is to be logged (required if customer_code is not provided) |
**customer_code** | **string** | The unique code of the customer where the comment is to be logged (required if customer_id is not provided) |
**date** | [**\DateTime**](\DateTime.md) | The date when the customer comment interaction is to be logged at the customer (defaults to the current date if not specified) | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
