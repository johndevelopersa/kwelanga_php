# # Customer

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **int** | The unique id of the customer | [optional]
**code** | **string** | The unique code associated with this customer | [optional]
**name** | **string** | The name of the customer | [optional]
**active** | **bool** | Whether or not the customer is active | [optional] [default to true]
**location** | [**\SkynamoClientAPI\Model\Location**](Location.md) |  | [optional]
**price_list_id** | **int** | The unique identifier of the price list associated with this customer | [optional]
**price_list_name** | **string** | The name of the price list associated with this customer | [optional]
**assigned_users** | **int[]** | List of user ids that are assigned to this customer | [optional]
**default_discount** | **float** | The default discount applied for this customer when creating orders | [optional] [default to 0.0]
**default_warehouse_id** | **int** | The unique identifier of the warehouse associated with this customer | [optional]
**default_warehouse_name** | **int** | The name of the warehouse associated with this customer | [optional]
**row_version** | **float** | An automatically generated, unique number used to version-stamp table rows in the database | [optional]
**last_modified_time** | [**\DateTime**](\DateTime.md) | The last time this customer was modified | [optional]
**create_date** | [**\DateTime**](\DateTime.md) | The time at which this customer was created | [optional]
**custom_fields** | [**\SkynamoClientAPI\Model\CustomField[]**](CustomField.md) | Certain custom fields may be required depending on the custom fields that have been set up | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
