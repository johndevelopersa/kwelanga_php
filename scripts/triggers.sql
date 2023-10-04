/*
 * NOTE: icdsoft host disallows grants so i've removed definer, but if needed in future :
 * 		syntax is -> CREATE DEFINER='auditorinsert' TRIGGER rehoboth_centura.trg...
 * 		permissions -> grant Select,Trigger on rehoboth_centura.location_material to auditorinsert;
 *  				   grant Insert on rehoboth_centura_auditor.location_material to auditorinsert;
 * 
 * 		For the moment, the trigger will have permissions of user that they are created under. Should use rehoboth user.
 * 
 * 
 *      RT:
 *      the definer is naturally set to root@'localhost'
 */

/*
 * PRICING
 * REMEMBER : change the database name when moving between DOPS and LIVE !!!
 */
DROP TRIGGER IF EXISTS retailtr_dops.trg_ins_pricing;
delimiter $$
CREATE TRIGGER retailtr_dops.trg_ins_pricing AFTER INSERT ON retailtr_dops.pricing
FOR EACH ROW BEGIN
  insert into retailtr_dops_auditor.pricing(change_by,change_date,change_type,change_by_userid,
  											uid,customer_type_uid,chain_store,principal_product_uid,
  											principal_uid,list_price,deal_type_uid,discount_value,start_date,
											end_date,status_uid,excl_incl,active,deleted,reference,product_level)
  values (user(),now(),'I',NEW.last_change_by_userid,
  		  NEW.uid,NEW.customer_type_uid,NEW.chain_store,NEW.principal_product_uid,
		  NEW.principal_uid,NEW.list_price,NEW.deal_type_uid,NEW.discount_value,NEW.start_date,
		  NEW.end_date,NEW.status_uid,NEW.excl_incl,NEW.active,NEW.deleted,NEW.reference,NEW.product_level);
END;
$$

DROP TRIGGER IF EXISTS retailtr_dops.trg_del_pricing;
delimiter $$
CREATE TRIGGER retailtr_dops.trg_del_pricing BEFORE DELETE ON retailtr_dops.pricing
FOR EACH ROW BEGIN
  insert into retailtr_dops_auditor.pricing(change_by,change_date,change_type,change_by_userid,
  											uid,customer_type_uid,chain_store,principal_product_uid,
  											principal_uid,list_price,deal_type_uid,discount_value,start_date,
											end_date,status_uid,excl_incl,active,deleted,reference,product_level)
  values (user(),now(),'D',OLD.last_change_by_userid,
          OLD.uid,OLD.customer_type_uid,OLD.chain_store,OLD.principal_product_uid,
		  OLD.principal_uid,OLD.list_price,OLD.deal_type_uid,OLD.discount_value,OLD.start_date,
		  OLD.end_date,OLD.status_uid,OLD.excl_incl,OLD.active,OLD.deleted,OLD.reference,OLD.product_level);
END;
$$

DROP TRIGGER IF EXISTS retailtr_dops.trg_upd_pricing;
delimiter $$
CREATE TRIGGER retailtr_dops.trg_upd_pricing BEFORE UPDATE ON retailtr_dops.pricing
FOR EACH ROW BEGIN
  if (OLD.uid!=NEW.uid) or
  (OLD.customer_type_uid!=NEW.customer_type_uid) or
  (OLD.chain_store!=NEW.chain_store) or
  (OLD.principal_product_uid!=NEW.principal_product_uid) or
  (OLD.principal_uid!=NEW.principal_uid) or
  (OLD.list_price!=NEW.list_price) or
  (OLD.deal_type_uid!=NEW.deal_type_uid) or
  (OLD.discount_value!=NEW.discount_value) or
  (OLD.start_date!=NEW.start_date) or 
  (OLD.end_date!=NEW.end_date) or
  (OLD.status_uid!=NEW.status_uid) or
  (OLD.excl_incl!=NEW.excl_incl) or
  (OLD.active!=NEW.active) or
  (OLD.deleted!=NEW.deleted) or
  (OLD.reference!=NEW.reference)or
  (OLD.product_level!=NEW.product_level) then
  insert into retailtr_dops_auditor.pricing(change_by,change_date,change_type,change_by_userid,
  											uid,customer_type_uid,chain_store,principal_product_uid,
  											principal_uid,list_price,deal_type_uid,discount_value,start_date,
											end_date,status_uid,excl_incl,active,deleted,reference,product_level)
  values (user(),now(),'U',NEW.last_change_by_userid,
  		  OLD.uid,OLD.customer_type_uid,OLD.chain_store,OLD.principal_product_uid,
		  OLD.principal_uid,OLD.list_price,OLD.deal_type_uid,OLD.discount_value,OLD.start_date,
		  OLD.end_date,OLD.status_uid,OLD.excl_incl,OLD.active,OLD.deleted,OLD.reference,OLD.product_level);
  end if;
END;
$$

/*
 * DOCUMENT
 * REMEMBER : change the database name when moving between DOPS and LIVE !!!
 * #2 : also remember that the document triggers are NOT standard ! Read them carefully !
 */
DROP TRIGGER IF EXISTS retailtr_dops.trg_ins_dm;
delimiter $$
CREATE TRIGGER retailtr_dops.trg_ins_dm AFTER INSERT ON retailtr_dops.document_master
FOR EACH ROW BEGIN
  insert into retailtr_dops_auditor.document(change_by,change_date,change_type,change_by_userid,
  											table_name,uid,principal_store_uid,principal_uid,document_number)
  values (user(),now(),'I',null,
  		  'document_master',NEW.uid,null,NEW.principal_uid,NEW.document_number);
END;
$$

-- remember the detail tables switch the UIDs to be Foreign Keys...
DROP TRIGGER IF EXISTS retailtr_dops.trg_ins_dh;
delimiter $$
CREATE TRIGGER retailtr_dops.trg_ins_dh AFTER INSERT ON retailtr_dops.document_header
FOR EACH ROW BEGIN
  insert into retailtr_dops_auditor.document(change_by,change_date,change_type,change_by_userid,
  											table_name,uid,principal_store_uid,principal_uid,document_number)
  values (user(),now(),'I',null,
  		  'document_header',NEW.document_master_uid,NEW.principal_store_uid,null,'');
END;
$$

DROP TRIGGER IF EXISTS retailtr_dops.trg_del_dm;
delimiter $$
CREATE TRIGGER retailtr_dops.trg_del_dm BEFORE DELETE ON retailtr_dops.document_master
FOR EACH ROW BEGIN
  insert into retailtr_dops_auditor.document(change_by,change_date,change_type,change_by_userid,
  											table_name,uid,principal_store_uid,principal_uid,document_number)
  values (user(),now(),'D',null,
          'document_master',OLD.uid,null,OLD.principal_uid,OLD.document_number);
END;
$$

-- remember the detail tables switch the UIDs to be Foreign Keys...
DROP TRIGGER IF EXISTS retailtr_dops.trg_del_dh;
delimiter $$
CREATE TRIGGER retailtr_dops.trg_del_dh BEFORE DELETE ON retailtr_dops.document_header
FOR EACH ROW BEGIN
  insert into retailtr_dops_auditor.document(change_by,change_date,change_type,change_by_userid,
  											table_name,uid,principal_store_uid,principal_uid,document_number)
  values (user(),now(),'D',null,
          'document_header',OLD.document_master_uid,OLD.principal_store_uid,null,'');
END;
$$

DROP TRIGGER IF EXISTS retailtr_dops.trg_upd_dh;
delimiter $$
CREATE TRIGGER retailtr_dops.trg_upd_dh BEFORE UPDATE ON retailtr_dops.document_header
FOR EACH ROW BEGIN
  if (OLD.principal_store_uid!=NEW.principal_store_uid) then
  insert into retailtr_dops_auditor.document(change_by,change_date,change_type,change_by_userid,
  											table_name,uid,principal_store_uid,principal_uid,document_number)
  values (user(),now(),'U',null,
  		  'document_header',OLD.document_master_uid,OLD.principal_store_uid,null,'');
  end if;
END;
$$

/*
 *PRINCIPAL_STORE_MASTER
 * REMEMBER : change the database name when moving between DOPS and LIVE !!!
 */
DROP TRIGGER IF EXISTS retailtr_dops.trg_ins_principal_store_master;
delimiter $$
CREATE TRIGGER retailtr_dops.trg_ins_principal_store_master AFTER INSERT ON retailtr_dops.principal_store_master
FOR EACH ROW BEGIN
  insert into retailtr_dops_auditor.principal_store_master(change_by,change_date,change_type,change_by_userid,
  											uid,status,depot_uid, principal_chain_uid, no_vat, on_hold, deliver_name)
  values (user(),now(),'I',NEW.last_change_by_userid,
  		  NEW.uid,NEW.status,NEW.depot_uid,NEW.principal_chain_uid,NEW.no_vat,NEW.on_hold,NEW.deliver_name);
END;
$$


DROP TRIGGER IF EXISTS retailtr_dops.trg_del_principal_store_master;
delimiter $$
CREATE TRIGGER retailtr_dops.trg_del_principal_store_master BEFORE DELETE ON retailtr_dops.principal_store_master
FOR EACH ROW BEGIN
  insert into retailtr_dops_auditor.principal_store_master(change_by,change_date,change_type,change_by_userid,
  											uid,status,depot_uid, principal_chain_uid, no_vat, on_hold, deliver_name)
  values (user(),now(),'D',OLD.last_change_by_userid,
          OLD.uid,OLD.status,OLD.depot_uid,OLD.principal_chain_uid,OLD.no_vat,OLD.on_hold,OLD.deliver_name);
END;
$$

DROP TRIGGER IF EXISTS retailtr_dops.trg_upd_principal_store_master;
delimiter $$
CREATE TRIGGER retailtr_dops.trg_upd_principal_store_master BEFORE UPDATE ON retailtr_dops.principal_store_master
FOR EACH ROW BEGIN
  if (OLD.uid!=NEW.uid) or
  (OLD.depot_uid!=NEW.depot_uid) or
  (OLD.principal_chain_uid!=NEW.principal_chain_uid) or
  (OLD.no_vat!=NEW.no_vat) or
  (OLD.on_hold!=NEW.on_hold) or
  (OLD.status!=NEW.status) or
  (OLD.deliver_name!=NEW.deliver_name) then
  insert into retailtr_dops_auditor.principal_store_master(change_by,change_date,change_type,change_by_userid,
  											uid,status,depot_uid, principal_chain_uid, no_vat, on_hold, deliver_name)
  values (user(),now(),'U',NEW.last_change_by_userid,
  		  OLD.uid,OLD.status,OLD.depot_uid,OLD.principal_chain_uid,OLD.no_vat,OLD.on_hold,OLD.deliver_name);
  end if;
END;
$$

/*
 * SPECIAL_FIELD_DETAILS
 * REMEMBER : change the database name when moving between DOPS and LIVE !!!
 */
DROP TRIGGER IF EXISTS retailtr_dops.trg_ins_special_field_details;
delimiter $$
CREATE TRIGGER retailtr_dops.trg_ins_special_field_details AFTER INSERT ON retailtr_dops.special_field_details
FOR EACH ROW BEGIN
  insert into retailtr_dops_auditor.special_field_details(change_by,change_date,change_type,change_by_userid,
  											uid,field_uid,value, entity_uid)
  values (user(),now(),'I',NEW.last_change_by_userid,
  		  NEW.uid,NEW.field_uid,NEW.value,NEW.entity_uid);
END;
$$


DROP TRIGGER IF EXISTS retailtr_dops.trg_del_special_field_details;
delimiter $$
CREATE TRIGGER retailtr_dops.trg_del_special_field_details BEFORE DELETE ON retailtr_dops.special_field_details
FOR EACH ROW BEGIN
  insert into retailtr_dops_auditor.special_field_details(change_by,change_date,change_type,change_by_userid,
  											uid,field_uid,value, entity_uid)
  values (user(),now(),'D',OLD.last_change_by_userid,
          OLD.uid,OLD.field_uid,OLD.value,OLD.entity_uid);
END;
$$

DROP TRIGGER IF EXISTS retailtr_dops.trg_upd_special_field_details;
delimiter $$
CREATE TRIGGER retailtr_dops.trg_upd_special_field_details BEFORE UPDATE ON retailtr_dops.special_field_details
FOR EACH ROW BEGIN
  if (OLD.uid!=NEW.uid) or
  (OLD.field_uid!=NEW.field_uid) or
  (OLD.value!=NEW.value) or
  (OLD.entity_uid!=NEW.entity_uid) then
  insert into retailtr_dops_auditor.special_field_details(change_by,change_date,change_type,change_by_userid,
  											uid,field_uid,value, entity_uid)
  values (user(),now(),'U',NEW.last_change_by_userid,
  		  OLD.uid,OLD.field_uid,OLD.value,OLD.entity_uid);
  end if;
END;
$$


/*
 *PRINCIPAL_PRODUCT
 * REMEMBER : change the database name when moving between DOPS and LIVE !!!
 */
DROP TRIGGER IF EXISTS retailtr_dops.trg_ins_principal_product;
delimiter $$
CREATE TRIGGER retailtr_dops.trg_ins_principal_product AFTER INSERT ON retailtr_dops.principal_product
FOR EACH ROW BEGIN
  insert into retailtr_dops_auditor.principal_product(change_by,change_date,change_type,change_by_userid,
  											uid,deleted,product_code, major_category, minor_category, enforce_pallet_consignment, units_per_pallet, product_description,items_per_case,
  											vat_rate)
  values (user(),now(),'I',NEW.last_change_by_userid,
  		  NEW.uid,NEW.deleted,NEW.product_code,NEW.major_category,NEW.minor_category,NEW.enforce_pallet_consignment,NEW.units_per_pallet,NEW.product_description,NEW.items_per_case,
  		  NEW.vat_rate);
END;
$$


DROP TRIGGER IF EXISTS retailtr_dops.trg_del_principal_product;
delimiter $$
CREATE TRIGGER retailtr_dops.trg_del_principal_product BEFORE DELETE ON retailtr_dops.principal_product
FOR EACH ROW BEGIN
  insert into retailtr_dops_auditor.principal_product(change_by,change_date,change_type,change_by_userid,
  											uid,deleted,product_code, major_category, minor_category, enforce_pallet_consignment, units_per_pallet, product_description,
  											vat_rate)
  values (user(),now(),'D',OLD.last_change_by_userid,
          OLD.uid,OLD.deleted,OLD.product_code,OLD.major_category,OLD.minor_category,OLD.enforce_pallet_consignment,OLD.units_per_pallet,OLD.product_description,
          OLD.vat_rate);
END;
$$

DROP TRIGGER IF EXISTS retailtr_dops.trg_upd_principal_product;
delimiter $$
CREATE TRIGGER retailtr_dops.trg_upd_principal_product BEFORE UPDATE ON retailtr_dops.principal_product
FOR EACH ROW BEGIN
  if (OLD.uid!=NEW.uid) or
  (OLD.deleted!=NEW.deleted) or
  (OLD.product_code!=NEW.product_code) or
  (OLD.major_category!=NEW.major_category) or
  (OLD.minor_category!=NEW.minor_category) or
  (OLD.enforce_pallet_consignment!=NEW.enforce_pallet_consignment) or
  (OLD.units_per_pallet!=NEW.units_per_pallet) or
  (OLD.product_description!=NEW.product_description) or
  (OLD.items_per_case!=NEW.items_per_case) or
  (OLD.vat_rate!=NEW.vat_rate) then
  insert into retailtr_dops_auditor.principal_product(change_by,change_date,change_type,change_by_userid,
  											uid,deleted,product_code, major_category, minor_category, enforce_pallet_consignment, units_per_pallet,product_description,items_per_case,
  											vat_rate)
  values (user(),now(),'U',NEW.last_change_by_userid,
  		  OLD.uid,OLD.deleted,OLD.product_code,OLD.major_category,OLD.minor_category,OLD.enforce_pallet_consignment,OLD.units_per_pallet,OLD.product_description,OLD.items_per_case,
  		  OLD.vat_rate);
  end if;
END;
$$


/*
 *PRINCIPAL_CHAIN_MASTER
 * REMEMBER : change the database name when moving between DOPS and LIVE !!!
 */
DROP TRIGGER IF EXISTS retailtr_dops.trg_ins_principal_chain_master;
delimiter $$
CREATE TRIGGER retailtr_dops.trg_ins_principal_chain_master AFTER INSERT ON retailtr_dops.principal_chain_master
FOR EACH ROW BEGIN
  insert into retailtr_dops_auditor.principal_chain_master(change_by,change_date,change_type,change_by_userid,
  											uid,status)
  values (user(),now(),'I',NEW.last_change_by_userid,
  		  NEW.uid,NEW.status);
END;
$$


DROP TRIGGER IF EXISTS retailtr_dops.trg_del_principal_chain_master;
delimiter $$
CREATE TRIGGER retailtr_dops.trg_del_principal_chain_master BEFORE DELETE ON retailtr_dops.principal_chain_master
FOR EACH ROW BEGIN
  insert into retailtr_dops_auditor.principal_chain_master(change_by,change_date,change_type,change_by_userid,
  											uid,status)
  values (user(),now(),'D',OLD.last_change_by_userid,
          OLD.uid,OLD.status);
END;
$$

DROP TRIGGER IF EXISTS retailtr_dops.trg_upd_principal_chain_master;
delimiter $$
CREATE TRIGGER retailtr_dops.trg_upd_principal_chain_master BEFORE UPDATE ON retailtr_dops.principal_chain_master
FOR EACH ROW BEGIN
  if (OLD.uid!=NEW.uid) or
  (OLD.status!=NEW.status) then
  insert into retailtr_dops_auditor.principal_chain_master(change_by,change_date,change_type,change_by_userid,
  											uid,status)
  values (user(),now(),'U',NEW.last_change_by_userid,
  		  OLD.uid,OLD.status);
  end if;
END;
$$



/*
 *PRICING_DOCUMENT
 * REMEMBER : change the database name when moving between DOPS and LIVE !!!
 */
DROP TRIGGER IF EXISTS retailtr_dops.trg_ins_pricing_document;
delimiter $$
CREATE TRIGGER retailtr_dops.trg_ins_pricing_document AFTER INSERT ON retailtr_dops.pricing_document
FOR EACH ROW BEGIN
  insert into retailtr_dops_auditor.pricing_document(change_by,change_date,change_type,change_by_userid,
  											uid,grouping,description,customer_type_uid,store_chain_uid,unit_price_type_uid,quantity,
											deal_type_uid,value,status,start_date,end_date,apply_level,apply_per_unit,cumulative_type)
  values (user(),now(),'I',NEW.last_change_by_userid,
  		  NEW.uid,NEW.grouping,NEW.description,NEW.customer_type_uid,NEW.store_chain_uid,NEW.unit_price_type_uid,NEW.quantity,
		  NEW.deal_type_uid,NEW.value,NEW.status,NEW.start_date,NEW.end_date,NEW.apply_level,NEW.apply_per_unit,NEW.cumulative_type);
END;
$$


DROP TRIGGER IF EXISTS retailtr_dops.trg_del_pricing_document;
delimiter $$
CREATE TRIGGER retailtr_dops.trg_del_pricing_document BEFORE DELETE ON retailtr_dops.pricing_document
FOR EACH ROW BEGIN
  insert into retailtr_dops_auditor.pricing_document(change_by,change_date,change_type,change_by_userid,
  											uid,grouping,description,customer_type_uid,store_chain_uid,unit_price_type_uid,quantity,
											deal_type_uid,value,status,start_date,end_date,apply_level,apply_per_unit,cumulative_type)
  values (user(),now(),'D',OLD.last_change_by_userid,
          OLD.uid,OLD.grouping,OLD.description,OLD.customer_type_uid,OLD.store_chain_uid,OLD.unit_price_type_uid,OLD.quantity,
		    OLD.deal_type_uid,OLD.value,OLD.status,OLD.start_date,OLD.end_date,OLD.apply_level,OLD.apply_per_unit,OLD.cumulative_type);
END;
$$

DROP TRIGGER IF EXISTS retailtr_dops.trg_upd_pricing_document;
delimiter $$
CREATE TRIGGER retailtr_dops.trg_upd_pricing_document BEFORE UPDATE ON retailtr_dops.pricing_document
FOR EACH ROW BEGIN
  if (OLD.uid!=NEW.uid) or
  (OLD.status!=NEW.status) then
  insert into retailtr_dops_auditor.pricing_document(change_by,change_date,change_type,change_by_userid,
  											uid,grouping,description,customer_type_uid,store_chain_uid,unit_price_type_uid,quantity,
											deal_type_uid,value,status,start_date,end_date,apply_level,apply_per_unit,cumulative_type)
  values (user(),now(),'U',NEW.last_change_by_userid,
  		    OLD.uid,OLD.grouping,OLD.description,OLD.customer_type_uid,OLD.store_chain_uid,OLD.unit_price_type_uid,OLD.quantity,
		    OLD.deal_type_uid,OLD.value,OLD.status,OLD.start_date,OLD.end_date,OLD.apply_level,OLD.apply_per_unit,OLD.cumulative_type);
  end if;
END;
$$

/*
 *PRICING_DOCUMENT_PRODUCT
 * REMEMBER : change the database name when moving between DOPS and LIVE !!!
 */
DROP TRIGGER IF EXISTS retailtr_dops.trg_ins_pricing_document_product;
delimiter $$
CREATE TRIGGER retailtr_dops.trg_ins_pricing_document_product AFTER INSERT ON retailtr_dops.pricing_document_product
FOR EACH ROW BEGIN
  insert into retailtr_dops_auditor.pricing_document_product(change_by,change_date,change_type,change_by_userid,
  											uid,pricing_document_uid,product_entity_uid)
  values (user(),now(),'I',@userId,
  		  NEW.uid,NEW.pricing_document_uid,NEW.product_entity_uid);
END;
$$


DROP TRIGGER IF EXISTS retailtr_dops.trg_del_pricing_document_product;
delimiter $$
CREATE TRIGGER retailtr_dops.trg_del_pricing_document_product BEFORE DELETE ON retailtr_dops.pricing_document_product
FOR EACH ROW BEGIN
  insert into retailtr_dops_auditor.pricing_document_product(change_by,change_date,change_type,change_by_userid,
  											uid,pricing_document_uid,product_entity_uid)
  values (user(),now(),'D',@userId,
          OLD.uid,OLD.pricing_document_uid,OLD.product_entity_uid);
END;
$$

DROP TRIGGER IF EXISTS retailtr_dops.trg_upd_pricing_document_product;
delimiter $$
CREATE TRIGGER retailtr_dops.trg_upd_pricing_document_product BEFORE UPDATE ON retailtr_dops.pricing_document_product
FOR EACH ROW BEGIN
  if (OLD.uid!=NEW.uid) or
  (OLD.product_entity_uid!=NEW.product_entity_uid) then
  insert into retailtr_dops_auditor.pricing_document_product(change_by,change_date,change_type,change_by_userid,
  											uid,pricing_document_uid,product_entity_uid)
  values (user(),now(),'U',@userId,
  		    OLD.uid,OLD.pricing_document_uid,OLD.product_entity_uid);
  end if;
END;
$$


/*
* ORDERS_HOLDING
* At the moment there is only an update trigger.
* Remember : this uses a sql var @userId, and not a database field for the lastchangedby
*/
DROP TRIGGER IF EXISTS retailtr_dops.trg_upd_orders_holding;
delimiter $$
CREATE TRIGGER retailtr_dops.trg_upd_orders_holding BEFORE UPDATE ON retailtr_dops.orders_holding
FOR EACH ROW BEGIN
  if (OLD.status!=NEW.status) or
  (OLD.status_msg!=NEW.status_msg) or
  (OLD.principal_store_uid!=NEW.principal_store_uid) or
  (OLD.force_skip_unique_order_no!=NEW.force_skip_unique_order_no) then
  insert into retailtr_dops_auditor.orders_holding(change_by,change_date,change_type,change_by_userid,
  											uid,status,status_msg,principal_store_uid,force_skip_unique_order_no)
  values (user(),now(),'U',@userId,
  		    OLD.uid,OLD.status,OLD.status_msg,OLD.principal_store_uid,OLD.force_skip_unique_order_no);
  end if;
END;
$$

/*
* ORDERS_HOLDING_DETAIL
* At the moment there is only an update trigger.
* Remember : this uses a sql var @userId, and not a database field for the lastchangedby
*/
DROP TRIGGER IF EXISTS retailtr_dops.trg_upd_orders_holding_detail;
delimiter $$
CREATE TRIGGER retailtr_dops.trg_upd_orders_holding_detail BEFORE UPDATE ON retailtr_dops.orders_holding_detail
FOR EACH ROW BEGIN
  if (OLD.status!=NEW.status) or
  (OLD.override_price_type!=NEW.override_price_type) or
  (OLD.principal_product_uid!=NEW.principal_product_uid) then
  insert into retailtr_dops_auditor.orders_holding_detail(change_by,change_date,change_type,change_by_userid,
  											uid,orders_holding_uid,status,override_price_type, principal_product_uid)
  values (user(),now(),'U',@userId,
  		    OLD.uid,OLD.orders_holding_uid,OLD.status,OLD.override_price_type,OLD.principal_product_uid);
  end if;
END;
$$