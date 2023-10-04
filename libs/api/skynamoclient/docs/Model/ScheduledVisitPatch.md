# # ScheduledVisitPatch

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **int** | The unique identifier of the scheduled visit |
**comment** | **string** | A place for a user to place a comment about the scheduled visit | [optional]
**due_date** | [**\DateTime**](\DateTime.md) | The date when the scheduled visit is due (required if end_time is provided; the time segment will be ignored if all_day is set to true) | [optional]
**customer_id** | **int** | The unique identifier of the customer where the scheduled visit should be completed | [optional]
**customer_code** | **string** | The unique code of the customer where the scheduled visit should be completed | [optional]
**reminder_offset** | **string** | A timespan indicating when the reminder for this scheduled visit will be sent out (it is the duration between the reminder and the due date) | [optional]
**assigned_user_id** | **int** | The unique identifier of the user that must complete this scheduled visit | [optional]
**assigned_user_name** | **string** | The user name of the the user that must complete this scheduled visit | [optional]
**all_day** | **bool** | True if the scheduled visit can be completed at any time on the due_date (automatically set to false if an end_time is provided; will ignore the time segment of due_date if true) | [optional]
**end_time** | [**\DateTime**](\DateTime.md) | The end time of the scheduled visit (must be empty if all_day is set to true) | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
