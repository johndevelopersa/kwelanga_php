# # ScheduledVisitPost

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**comment** | **string** | A place for a user to place a comment about the scheduled visit | [optional]
**due_date** | [**\DateTime**](\DateTime.md) | The date when the scheduled visit is due (the time segment will be ignored if all_day is set to true) |
**customer_id** | **int** | The unique identifier of the customer where the scheduled visit should be completed (required if customer_code is not provided) |
**customer_code** | **string** | The unique code of the customer where the scheduled visit should be completed (required if customer_id is not provided) |
**reminder_offset** | **string** | A timespan indicating when the reminder for this scheduled visit will be sent out (it is the duration between the reminder and the due date) | [optional]
**assigned_user_id** | **int** | The unique identifier of the user that must complete this scheduled visit (required if assigned_user_name is not provided) |
**assigned_user_name** | **string** | The user name of the the user that must complete this scheduled visit (required if assigned_user_id is not provided) |
**all_day** | **bool** | True if the scheduled visit can be completed at any time on the due_date (automatically set to true if no end_time is provided; automatically set to false if an end_time is provided; will ignore the time segment of due_date if true) | [optional]
**end_time** | [**\DateTime**](\DateTime.md) | The end time of the scheduled visit (required if all_day is set to false; must be empty if all_day is set to true) | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
