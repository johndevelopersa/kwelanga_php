# # DealGroup

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **int** | The unique id of the deal group | [optional]
**group_name** | **string** | The name of the deal group | [optional]
**order_price_editable** | **bool** | A boolean that indicates whether the deal prices are editable when an order, quote or credit request is placed | [optional]
**last_modified_time** | [**\DateTime**](\DateTime.md) | The last time the deal group was modified | [optional]
**deals** | [**\SkynamoClientAPI\Model\DealGroupItem[]**](DealGroupItem.md) | A list of deals included in the deal group | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
