# # CompletedForm

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **int** | The unique ID of the completed form | [optional]
**date** | [**\DateTime**](\DateTime.md) | The date and time when this form was completed | [optional]
**customer_id** | **int** | The unique ID of the customer where the form was completed | [optional]
**customer_code** | **string** | The unique code of the customer where the form was completed | [optional]
**customer_name** | **string** | The name of the customer where the form was completed | [optional]
**user_id** | **int** | The unique ID of the user that completed the form | [optional]
**user_name** | **string** | The user name of the user that completed the form | [optional]
**interaction_id** | **int** | The unique ID of the interaction in which this form was completed | [optional]
**form_id** | **int** | The unique ID of the form definition that was completed | [optional]
**form_name** | **string** | The name of the form definition that was completed | [optional]
**custom_fields** | [**\SkynamoClientAPI\Model\CustomField[]**](CustomField.md) | Certain custom fields may be required depending on the custom fields that have been set up | [optional]
**last_modified_time** | [**\DateTime**](\DateTime.md) | The last time the completed form was modified | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
