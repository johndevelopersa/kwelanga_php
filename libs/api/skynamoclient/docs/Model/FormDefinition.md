# # FormDefinition

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **int** | The unique ID of the form definition | [optional]
**name** | **string** | The name of the form | [optional]
**type** | **string** | The type of the form | [optional]
**recipients** | **string** | The email recipients | [optional]
**users** | **int[]** | The user ids that can complete this form. If empty all user can use the form | [optional]
**last_modified_time** | [**\DateTime**](\DateTime.md) | The last time the form definition was modified | [optional]
**active** | **bool** | Indicates whether the form is active or not | [optional]
**custom_fields** | [**\SkynamoClientAPI\Model\FormDefinitionCustomField[]**](FormDefinitionCustomField.md) | List of custom fields of the form definition | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
