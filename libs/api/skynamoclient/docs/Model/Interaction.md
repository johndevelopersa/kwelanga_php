# # Interaction

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **int** | The unique ID of the interaction | [optional]
**customer_id** | **int** | The unique ID of the customer associated with the interaction | [optional]
**customer_code** | **string** | The unique code of the customer associated with the interaction | [optional]
**customer_name** | **string** | The name of the customer associated with the interaction | [optional]
**comment** | **string** | A comment about the interaction (if applicable) | [optional]
**date** | [**\DateTime**](\DateTime.md) | The date and time when the interaction occurred | [optional]
**end_time** | [**\DateTime**](\DateTime.md) | The date and time when the interaction ended (if applicable) | [optional]
**is_visit** | **bool** | True if the interaction is a visit | [optional]
**location** | [**\SkynamoClientAPI\Model\Location**](Location.md) |  | [optional]
**last_modified_time** | [**\DateTime**](\DateTime.md) | The last time the interaction was modified | [optional]
**user_id** | **int** | The unique ID of the user that did the interaction | [optional]
**user_name** | **string** | The user name of the user that did the interaction | [optional]
**credit_request_id** | **int** | The unique ID of the credit request that was placed at the interaction (if applicable) | [optional]
**order_id** | **int** | The unique ID of the order that was placed at the interaction (if applicable) | [optional]
**quote_id** | **int** | The unique ID of the quote that was placed at the interaction (if applicable) | [optional]
**product_numbers_survey_id** | **int** | The unique ID of the product numbers survey that was placed at the interaction (if applicable) | [optional]
**completed_form_ids** | **int[]** | A list of the unique IDs of the completed forms that were placed at the interaction (if applicable) | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
