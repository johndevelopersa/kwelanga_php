# # VisitFrequencyPatch

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **int** | A unique ID assigned to the visit frequency |
**customer_id** | **int** | The unique identifier of the customer where the visit frequency should be used | [optional]
**customer_code** | **string** | The unique code of the customer where the visit frequency should be used&lt;br&gt;(customer_code must correspond with customer_id and customer_name) | [optional]
**user_id** | **int** | The unique identifier of the user that the visit frequency is assigned to | [optional]
**user_name** | **string** | The user name of the user that the visit frequency is assigned to&lt;br&gt;(user_name must correspond with user_id) | [optional]
**cycle** | **int** | Number of cycles per period (\&quot;2\&quot; in the example at the top) | [optional]
**frequency** | **int** | Number of visits per cycle (\&quot;once(1)\&quot; in example at the top) | [optional]
**period** | **string** | The duration of a period. (\&quot;weeks\&quot; in example at the top)&lt;br&gt;Contains one of the following values: week, month or year | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
