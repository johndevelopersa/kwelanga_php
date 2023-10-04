----- PRICING
Left Join user_principal_store ON user_principal_store.principal_store_uid = a.chain_store AND user_principal_store.user_uid='&userId' AND a.customer_type_uid = &CONSTANT_CT_STORE&
Left Join user_principal_chain ON user_principal_chain.principal_chain_uid = a.chain_store AND user_principal_chain.user_uid='&userId' AND a.customer_type_uid = &CONSTANT_CT_CHAIN&,

AND   if(a.customer_type_uid = &CONSTANT_CT_CHAIN&,user_principal_chain.uid,user_principal_store.uid) is not null

------ product
LEFT JOIN user_principal_product ON d.uid = user_principal_product.principal_product_uid and user_principal_product.user_uid = '&userId',
AND   if( hasRole(&userId,&principalId,&CONSTANT_ROLE_BYPASS_USER_PRODUCT_RESTRICTION&) ,1,user_principal_product.uid) is not null
-- or u can do it faster :
LEFT JOIN user_principal_product ON c.product_uid = user_principal_product.principal_product_uid and user_principal_product.user_uid = '&userId'
LEFT JOIN user_role ON user_role.user_id='&userId' and user_role.role_id='&CONSTANT_ROLE_BYPASS_USER_PRODUCT_RESTRICTION&' and (user_role.entity_uid='&principalId' or user_role.entity_uid is null) 
AND (user_principal_product.uid is not null or user_role.uid is not null)

------ depot
INNER JOIN user_principal_depot ON user_principal_depot.principal_id = '&principalId' AND user_principal_depot.depot_id = b.uid AND user_principal_depot.user_id = '&userId', 

------ store
INNER Join user_principal_store ON user_principal_store.principal_store_uid = e.uid AND user_principal_store.user_uid='&userId'
INNER Join user_principal_chain ON user_principal_chain.principal_chain_uid = e.principal_chain_uid AND user_principal_chain.user_uid='&userId',

-- document type
INNER JOIN user_role ON user_role.user_id = '&userId' AND user_role.role_id = c.role_id and user_role.entity_uid = '&principalId', 