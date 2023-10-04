# # IntegrationRequest

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**action** | **string** | The integration action to be executed | [optional]
**enum_grow_data** | [**\SkynamoClientAPI\Model\EnumGrowData[]**](EnumGrowData.md) | Used in conjunction with the action &#39;AutoGrowEnums&#39; | [optional]
**fields_to_add** | [**\SkynamoClientAPI\Model\FieldGrowData[]**](FieldGrowData.md) | Used in conjunction with the action &#39;AddCustomFields&#39; | [optional]
**document_ids** | **int[]** | Used in conjunction with the action &#39;ResubmitOrderItemDocuments&#39; | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
