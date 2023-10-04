# # StockLevelPost

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**product_id** | **int** | The product id associated with this stocklevel | [optional]
**product_code** | **string** | The product code associated with this stocklevel (required if you do not specify product_id) | [optional]
**order_unit_id** | **int** | The order unit id associated with this stocklevel | [optional]
**order_unit_name** | **string** | The order unit name associated with this stocklevel (required if you do not specify order_unit_id) | [optional]
**warehouse_id** | **int** | The warehouse id associated with this stocklevel | [optional]
**warehouse_name** | **string** | The warehouse name associated with this stocklevel (required if you do not specify warehouse_id) | [optional]
**level** | **float** | The quantity value of this stocklevel | [optional]
**label** | **string** | The quantity label of this stocklevel (should match a label on insights) | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
