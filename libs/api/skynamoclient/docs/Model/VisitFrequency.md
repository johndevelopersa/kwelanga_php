# # VisitFrequency

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **int** | The unique identifier of the visit frequency | [optional]
**customer_id** | **int** | The unique identifier of the customer where the visit frequency should be used | [optional]
**customer_code** | **string** | The unique code of the customer where the visit frequency should be used&lt;br&gt;(customer_code must correspond with customer_id and customer_name) | [optional]
**customer_name** | **string** | The name of the customer where the visit frequency should be used&lt;br&gt;(customer_name must correspond with customer_id and customer_code) | [optional]
**user_id** | **int** | The unique identifier of the user that the visit frequency is assigned to | [optional]
**user_name** | **string** | The user name of the user that the visit frequency is assigned to&lt;br&gt;(user_name must correspond with user_id) | [optional]
**cycle** | **int** | Number of cycles per period (\&quot;2\&quot; in the example at the top) | [optional]
**frequency** | **int** | Number of visits per cycle (\&quot;once(1)\&quot; in example at the top) | [optional]
**period** | **string** | The duration of a period. (\&quot;weeks\&quot; in example at the top)&lt;br&gt;Contains one of the following values: week, month or year | [optional]
**row_version** | **float** | An automatically generated, unique number used to version-stamp table rows in the database | [optional]
**last_modified_time** | [**\DateTime**](\DateTime.md) | The last time the visit frequency was modified | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
