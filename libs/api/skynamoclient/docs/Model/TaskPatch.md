# # TaskPatch

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **int** | The unique identifier of the task |
**description** | **string** | The description of what the task entails | [optional]
**due_date** | [**\DateTime**](\DateTime.md) | The date when the task is due (the time will be ignored if anytime is set to true) | [optional]
**customer_id** | **int** | The unique identifier of the customer where the task should be completed (if applicable) | [optional]
**customer_code** | **string** | The unique code of the customer where the task should be completed (if applicable) | [optional]
**reminder_offset** | **string** | A timespan indicating when the reminder for this task will be sent out (it is the duration between the reminder and the due date) | [optional]
**completed_date** | [**\DateTime**](\DateTime.md) | The date the task was completed (the task is not completed if this field is empty) | [optional]
**assigned_user_id** | **int** | The unique identifier of the user that must complete this task | [optional]
**assigned_user_name** | **string** | The user name of the the user that must complete this task | [optional]
**anytime** | **bool** | True if the task can be completed at any time on the due_date (ignore the time segment of due_date if true) | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
