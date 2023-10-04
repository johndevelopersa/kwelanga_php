select * from principal_store_master where principal_uid = 3;
select * from special_field_fields where principal_uid = 3;
select * from special_field_details a where exists (select 1 from special_field_fields b where a.field_uid = b.uid and b.principal_uid = 3);
select * from principal_product where principal_uid = 3;
select * from principal_product_depot_gtin a where exists (select 1 from principal_product b where a.principal_product_uid = b.uid and b.principal_uid = 3);
select * from pricing where principal_uid = 3 and deleted = 0;
select * from pricing_document where principal_uid = 3;
select * from principal_chain_master where principal_uid = 3;
(4,66,104,70,35,48,45)
-- setup manually :
-- principal_preference
-- import_preference
-- online_file_processing
-- online_file_mappint
-- principal_vendor
-- notification
-- notification_recipients
-- principal_contact
