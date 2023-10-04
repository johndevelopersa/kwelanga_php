# # ScheduledVisit

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **int** | The unique identifier of the scheduled visit | [optional]
**comment** | **string** | A place for a user to place a comment about the scheduled visit | [optional]
**create_date** | [**\DateTime**](\DateTime.md) | The date when the scheduled visit was created | [optional]
**due_date** | [**\DateTime**](\DateTime.md) | The date when the scheduled visit is due (the time should be ignored if all_day is set to true) | [optional]
**customer_id** | **int** | The unique identifier of the customer where the scheduled visit should be completed | [optional]
**customer_code** | **string** | The unique code of the customer where the scheduled visit should be completed | [optional]
**customer_name** | **string** | The name of the customer where the scheduled visit should be completed | [optional]
**row_version** | **float** | An automatically generated, unique number used to version-stamp table rows in the database | [optional]
**last_modified_time** | [**\DateTime**](\DateTime.md) | The last time the scheduled visit was modified | [optional]
**reminder_offset** | **string** | A timespan indicating when the reminder for this scheduled visit will be sent out (it is the duration between the reminder and the due date) | [optional]
**completer_visit_id** | **int** | The unique identifier of the visit that was done to complete this scheduled visit (the scheduled visit is not complete if this field is empty) | [optional]
**completed_date** | [**\DateTime**](\DateTime.md) | The date the scheduled visit was completed (the scheduled visit is not completed if this field is empty) | [optional]
**assigned_user_id** | **int** | The unique identifier of the user that must complete this scheduled visit | [optional]
**assigned_user_name** | **string** | The user name of the the user that must complete this scheduled visit | [optional]
**creator_user_id** | **int** | The unique identifier of the user that created this scheduled visit | [optional]
**creator_user_name** | **string** | The user name of the user that created this scheduled visit | [optional]
**all_day** | **bool** | True if the scheduled visit can be completed at any time on the due_date (ignore the time segment of due_date and the end_time if true) | [optional]
**end_time** | [**\DateTime**](\DateTime.md) | The end time of the scheduled visit (ignore if all_day is set to true) | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
