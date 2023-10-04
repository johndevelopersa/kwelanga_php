<?php
//const CONST_VALUE = 'A constant value'; used in a class

// server specific constants that change according to what server you running from
include_once('ServerConstants.php');

class Constants
{

    public const GUI_PHP_INTEGER_REGEX = '/^[0-9]+$/';
    public const GUI_PHP_TIME_VALIDATION = '/^([01]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9]){0,1}$/';
    public const GUI_PHP_DATETIME_VALIDATION = '/^([0-9]{4})\-([0-9]{2})\-([0-9]{2})[ ]([01]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9]){0,1}$/';
    public const GUI_PHP_DATETIME_FORMAT = 'Y-m-d H:i:s';
    public const GUI_PHP_FLOAT_REGEX = '/^[0-9]+[.]?[0-9]*$/';
    public const GUI_PHP_SIGNED_FLOAT_REGEX = '/^[-+]{0,1}[0-9]+[.]?[0-9]*$/';
    public const GUI_PHP_DATE_VALIDATION = '/^([0-9]{4})\-([0-9]{2})\-([0-9]{2})$/';
    public const GUI_PHP_INT_CSV_VALIDATION = '/^([0-9]+[,]{0,1})+$/';
    public const GUI_PHP_TIME_REGEX = '/^([0-1][0-9]|[2][0-3])[:][0-5][0-9](:[0-5][0-9])?$/';
    public const GUI_PHP_EMAIL_REGEX = '/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/';
    public const ENVIRONMENT = 'LIVE';

}

// Session / System
define('SESSION_ADMIN_USERID', '000');
define('SESSION_SYSTEM_USERID_PARAM_NAME', 'KOSSYSTEMUSER');

define('RT_GLN', '6001651048339'); // this is the proper RT one and you may come across 6001007802929 hardcoded which is actually PnP's test GLN which they lent us before we had ours)
define('RT_DEFAULT_CONFIRMATIONS_EMAIL', 'confirmation@retailtrading.net');

//GUI
define('GUI_PHP_INTEGER_REGEX', '/^[0-9]+$/');
define('GUI_PHP_FLOAT_REGEX', '/^[0-9]+[.]?[0-9]*$/');
define('GUI_PHP_SIGNED_FLOAT_REGEX', '/^[-+]?[0-9]+[.]?[0-9]*$/');
define('GUI_PHP_EMAIL_REGEX', '/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/');
define('GUI_PHP_MOBILE_REGEX', '/^[0-9]{10}$/');
define('GUI_PHP_DATE_FORMAT', 'Y-m-d');
define('GUI_PHP_TIME_FORMAT', 'H:i:s');
define('GUI_PHP_DATETIME_FORMAT', 'Y-m-d H:i:s');
define('GUI_PHP_DATETIME_FORMAT_COMPRESSED', 'YmdHis');
define('GUI_PHP_DATE_VALIDATION', '/^([0-9]{4})\-([0-9]{2})\-([0-9]{2})$/');
define('GUI_PHP_CHAR_REGEX', '/[a-zA-Z]/');

// Colors
define('COLOR_UNOBTRUSIVE_INFO', '#008080');
define('COLOR_STANDARD_TEXT', '#101010');
define('COLOR_URGENT_TEXT', '#dd2323');

// Fonts
define('FONT_UNOBTRUSIVE_INFO', 'font-family:Calibri, Verdana, Ariel, sans-serif; font-size:12px; color:' . COLOR_UNOBTRUSIVE_INFO);
define('FONT_STANDARD', 'font-family:Calibri, Verdana, Ariel, sans-serif; font-size:12px; color:' . COLOR_STANDARD_TEXT);

// principals
define('RETAIL_TR', '171');

//Delimiters
define('DELIMITER_REPORTING_LOCATION_1', '-'); // the first delimiter in line
define('DELIMITER_REPORTING_LOCATION_2', ','); // the 2nd delimiter in line
define('DELIMITER_TRADING_PARTNER_1', '-'); // the first delimiter in line
define('DELIMITER_TRADING_PARTNER_2', ','); // the 2nd delimiter in line
define('DELIMITER_TRANSPORTER_1', '-'); // the first delimiter in line
define('DELIMITER_TRANSPORTER_2', ','); // the 2nd delimiter in line
define('DELIMITER_SPROC_FIELD_SEPARATOR', '|||'); // separates fields when calling an sproc
define('DELIMITER_SPROC_ROW_SEPARATOR', '###'); // separates fields when calling an sproc
define('DELIMITER_OTHER_1', ' - '); // the first delimiter in line
define('DELIMITER_OTHER_2', ','); // the 2nd delimiter in line
define('DELIMITER_EDI_COLUMN', ',');
define('DELIMITER_EDI_FIELD', '"');
define('DELIMITER_REP_COLUMN', ',');
define('DELIMITER_REP_FIELD', '"');

//Flags
define('FLAG_STATUS_ACTIVE', 'A');
define('FLAG_STATUS_DELETED', 'D');
define('FLAG_STATUS_QUEUED', 'Q');
define('FLAG_STATUS_ERROR', 'E');
define('FLAG_STATUS_SUSPENDED', 'S');
define('FLAG_STATUS_CLOSED', 'C');
define('FLAG_ERRORTO_SUCCESS', 'S');
define('FLAG_ERRORTO_WARNING', 'W');
define('FLAG_ERRORTO_ERROR', 'E');
define('FLAG_ERRORTO_NOT_EXTRACTED', 'N');
define('FLAG_ERRORTO_INFO', 'I');
define('FLAG_ERRORTO_REJECTED', 'R');
define('FLAG_ROLE_RESTRICTEDTO_ADMIN', 'A');
define('FLAG_ROLE_RESTRICTEDTO_GENERAL', 'G');
define('FLAG_PRINCIPAL_USER', 'P');
define('FLAG_DEPOT_USER', 'D');
define('FLAG_SALESAGENT_USER', 'A');
define('FLAG_TRUCKDRIVER_USER', 'T');

// values
define('VAL_GUI_MAX_ROWS_RETURNED', '10');
define('VAL_DEALTYPE_NETT_PRICE', '1');
define('VAL_DEALTYPE_AMOUNT_OFF', '2');
define('VAL_DEALTYPE_PERCENTAGE', '4');
define('VAL_VAT_RATE_TBLSTD', '15.00'); // backwards compatibility, standardised db table value
define('VAL_VAT_RATE', '0.15');
define('VAL_VAT_RATE_ADD', '1.15');
define('VAL_VAT_OLD_RATE_ADD', '1.14');
define('VAL_PRICE_VARIATION_ALLOWED', '0.015');
define('VAL_UNKNOWN_STORE_OLD_ACCOUNT', 'UNK STORE');
define('VAL_PSM_OLD_ACCOUNT_PREFIX', 'SYSPR');
define('VAL_PRODUCTCODE_NOT_ON_MF', 'SYSNONMF');
define('VAL_PRODUCTCODE_CONSOLIDATED', 'SYSCONSOL');
define('VAL_UNKNOWN_DEPOT', '99');
define('VAL_NON_RT_DEPOT', '106');

//Literals
define('LITERAL_SEQ_STORE', 'STOREM');
define('LITERAL_SEQ_ORDER', 'ORDERSEQ'); // the sequence batching lines together (orders.order_sequence_no)
define('LITERAL_SEQ_DOCUMENT_NUMBER', 'DOCNUM'); // the sequence batching lines together (orders.order_sequence_no)
define('LITERAL_SEQ_EXPORT_FILE', 'EXPORTFILE');
define('LITERAL_DEAL_EXCLUSIVE', 'EXCL');
define('LITERAL_SEQ_TRIPSHEET', 'TRIPS');
define('LITERAL_SEQ_TRIPSHEET_DISPATCH', 'TRIPOUT');
define('LITERAL_SEQ_PICKLIST', 'PICKS');
define('LITERAL_SEQ_WAYBILL', 'WAYBILL');
define('LITERAL_SEQ_CONSOLIDATED', 'CONDOC');
define('LITERAL_SEQ_VOQADO', 'VOQADOFSEQ');
define('LITERAL_SEQ_KOS_EXTRACT', 'KOSEXTRACT');
define('LITERAL_SEQ_PAYMENT', 'PAYMENT');
define('LITERAL_SEQ_REPORTS', 'REPORTS');
define('LITERAL_SEQ_PAYMENTTO', 'PAYMENTTO');
define('LITERAL_SEQ_SGX_FILE', 'SGXFILE');
define('LITERAL_SEQ_RVLBOX', 'RVLBOX');
define('LITERAL_SEQ_RVLDISPATCH', 'RVLDISPATCH');
define('LITERAL_SEQ_PALLETCONTROL', 'PALLETCONTROL');
define('LITERAL_SEQ_ALTERNATE_DOCUMENT_NUMBER', 'ALTDOCNUM');
define('LITERAL_SEQ_API_FILE_NUMBER', 'APIFILE');

//object names
define('OBJ_NAME_GLOBAL_MSGBOX_AJAX', 'mainMsgBoxAjax');
define('OBJ_NAME_GLOBAL_MSGBOX_INFO', 'mainMsgBoxInfo');
define('OBJ_NAME_GLOBAL_MSGBOX_ERROR', 'mainMsgBoxError');
define('OBJ_NAME_GLOBAL_MSGBOX_YESNO', 'mainMsgBoxYesNo');
define('OBJ_NAME_GLOBAL_MSGBOX_INPUT', 'mainMsgBoxInput');
define('OBJ_NAME_GLOBAL_MSGBOX_CONTENT', 'mainMsgBoxContent');

// roles
define('ROLE_SUPERUSER', '61'); // role_id
define('ROLE_MAINTAIN_STORES', '37'); // role_id
define('ROLE_USER_MNT_AD', '1');
define('ROLE_USER_MNT_MF', '23');
define('ROLE_CREATE_USER', '2');
define('ROLE_MODIFY_SU', '3');
define('ROLE_DELETE_SU', '4');
define('ROLE_MODIFY_GU', '25');
define('ROLE_DELETE_GU', '26');
define('ROLE_ADD_PRINCIPAL_TO_USER', '63');
define('ROLE_REMOVE_PRINCIPAL_FROM_USER', '64');
define('ROLE_ADD_ROLE_TO_USER', '27');
define('ROLE_REMOVE_ROLE_FROM_USER', '28');
define('ROLE_MAINTAIN_STORE_USERS', '44');
define('ROLE_MAINTAIN_CHAIN_USERS', '65'); // role_id
define('ROLE_ADD_CHAIN', '67');
define('ROLE_MODIFY_CHAIN', '68');
define('ROLE_ADD_STORE', '38');
define('ROLE_MODIFY_STORE_DETAILS', '39');
define('ROLE_ADD_PRICE', '48');
define('ROLE_DELETE_PRICE', '49');
define('ROLE_MODIFY_PRICE', '69');
define('ROLE_VIEW_PRICE', '51');
define('ROLE_EMAIL_DOCUMENT', '282');
define('ROLE_SIGNITURE', '264');
define('ROLE_EXTEND_ENDDATE_PRICE', '50');
define('ROLE_REPORTS', '53');
define('ROLE_SOR_REPORT', '70');
define('ROLE_STOCK_REPORT', '87');
define('ROLE_TRANSACTION_TRACKING', '72');
define('ROLE_ORDER_CAPTURE', '73');
define('ROLE_QUOTATION_CAPTURE', '237');
define('ROLE_PAYMENTTO_CAPTURE', '316');
define('ROLE_PURCHASE_ORDER_CAPTURE', '261');
define('ROLE_SUPPLIER_INVOICE_CAPTURE', '279');
define('ROLE_AUTO_STOCK_ADJ', '455');
define('ROLE_AMEND_GRV', '438');
define('ROLE_AMEND_CLAIM', '439');
define('ROLE_OFF_INVOICE_DISCOUNT', '440');
define('ROLE_RESET_AN_INVOICE', '426');
define('ROLE_CHANGE_PO_NUMBER', '441');
define('ROLE_MANAGE_WAREHOUSE', '442');
define('ROLE_UN_CANCEL_ORDER', '443');
define('ROLE_RESET_DELIVERY_POD_OK', '444');
define('ROLE_AMEND_ORDER', '445');
define('ROLE_ACCEPTED_STATUS', '470');
define('ROLE_API_ADMIN', '476');

define('ROLE_DOCTYPE_ORDINV', '75');
define('ROLE_DOCTYPE_UPLIFTS', '76');
define('ROLE_DOCTYPE_STKTRF', '77');
define('ROLE_DOCTYPE_CN', '78');
define('ROLE_DOCTYPE_ARRIVAL', '79');
define('ROLE_DOCTYPE_DELNOTE', '80');
define('ROLE_DOCTYPE_UPLIFTCR', '81');
define('ROLE_DOCTYPE_DEBITNOTE', '82');
define('ROLE_DOCTYPE_REDEL', '83');
define('ROLE_DOCTYPE_UPLIFTREDEL', '84');
define('ROLE_DOCTYPE_CANCELLEDNOTE', '101');
define('ROLE_DOCTYPE_ORDINV_ZERO_PRICE', '102');
define('ROLE_DOCTYPE_DAMAGES', '123');
define('ROLE_DOCTYPE_ASN', '124');
define('ROLE_DOCTYPE_ARRIVAL_CORRECTION', '162');
define('ROLE_DOCTYPE_UPLIFTCR_REVERSAL', '164');
define('ROLE_DOCTYPE_CN_REVERSAL', '166');
define('ROLE_DOCTYPE_BUYER_GOODS_INWARD', '178');
define('ROLE_DOCTYPE_REMITTANCE', '203');
define('ROLE_DOCTYPE_WALKIN_INVOICE', '329');

define('ROLE_DOCTYPE_REPORTS_ORDINV', '140');
define('ROLE_DOCTYPE_REPORTS_UPLIFTS', '141');
define('ROLE_DOCTYPE_REPORTS_STKTRF', '142');
define('ROLE_DOCTYPE_REPORTS_CN', '104');
define('ROLE_DOCTYPE_REPORTS_ARRIVAL', '144');
define('ROLE_DOCTYPE_REPORTS_DELNOTE', '145');
define('ROLE_DOCTYPE_REPORTS_UPLIFTCR', '146');
define('ROLE_DOCTYPE_REPORTS_DEBITNOTE', '147');
define('ROLE_DOCTYPE_REPORTS_REDEL', '148');
define('ROLE_DOCTYPE_REPORTS_UPLIFTREDEL', '149');
define('ROLE_DOCTYPE_REPORTS_CANCELLEDNOTE', '150');
define('ROLE_DOCTYPE_REPORTS_ORDINV_ZERO_PRICE', '151');
define('ROLE_DOCTYPE_REPORTS_DAMAGES', '153');
define('ROLE_DOCTYPE_REPORTS_ASN', '154');
define('ROLE_DOCTYPE_REPORTS_ARRIVAL_CORRECTION', '163');
define('ROLE_DOCTYPE_REPORTS_UPLIFTCR_REVERSAL', '165');
define('ROLE_DOCTYPE_REPORTS_CN_REVERSAL', '167');
define('ROLE_DOCTYPE_REPORTS_BUYER_GOODS_INWARD', '179');
define('ROLE_DOCTYPE_REPORTS_REMITTANCE', '204');

define('ROLE_ADD_PRODUCT', '32');
define('ROLE_MODIFY_PRODUCT', '33');
define('ROLE_DELETE_PRODUCT', '34');
define('ROLE_VIEW_PRODUCT', '35');
define('ROLE_VIEW_STOCK', '86');
define('ROLE_TT_REMOVE_STORE_LIMIT', '94');
define('ROLE_MAINTAIN_PRODUCT_USERS', '97');
define('ROLE_OC_CAN_MODIFY_DELDATE', '98');
define('ROLE_BYPASS_USER_PRODUCT_RESTRICTION', '95');
define('ROLE_BYPASS_USER_STORE_RESTRICTION', '99');
define('ROLE_BYPASS_USER_CHAIN_RESTRICTION', '112');
define('ROLE_ALLOW_PRICE_OVERRIDE', '100');
define('ROLE_ADD_PRINCIPAL', '12');
define('ROLE_MODIFY_PRINCIPAL', '13');
define('ROLE_VIEW_PRINCIPAL', '14');
define('ROLE_ADD_DEPOT', '16');
define('ROLE_MODIFY_DEPOT', '17');
define('ROLE_VIEW_DEPOT', '18');
define('ROLE_ORDERS_HOLDING_EXCEPTIONS', '103');
define('ROLE_ADD_PRINCIPAL_CONTACT', '109');
define('ROLE_MODIFY_PRINCIPAL_CONTACT', '110');
define('ROLE_VIEW_PRINCIPAL_CONTACT', '111');
define('ROLE_MODIFY_STORE_EPOD', '130');
define('ROLE_EPOD_TRANSACTION_TRACKING', '131');
define('ROLE_REPRINT_DOCUMENT', '137');
define('ROLE_STOCK_TAKE', '158');
define('ROLE_MAY_APPROVE_FOR_RELEASE', '161');
define('ROLE_AGENT_DOCMENT_CONFIRMATION', '171');
define('ROLE_MODIFY_DEPOT_CALENDAR', '172');
define('ROLE_ADD_PRINCIPAL_SALES_REP', '174');
define('ROLE_MODIFY_PRINCIPAL_SALES_REP', '175');
define('ROLE_VIEW_PRINCIPAL_SALES_REP', '176');
define('ROLE_ELECTRONIC_RECONCILIATION', '183');
define('ROLE_TASKMAN_SYSTEM', '217');
define('ROLE_TASKMAN_CREATE_USERS', '219');
define('ROLE_TASKMAN_ADMINISTER_ROLES', '220');
define('ROLE_MANAGE_QUOTATION', '237');
define('ROLE_MANAGE_ORDERS', '292');
define('ROLE_DOCUMENT_DISPATCH_CONTROL', '430');

// table lists (for filtering subselections of all items available in a screen)
define('TBLLIST_MENU_USER', '18,19'); // userDetails.php
define('TBLLIST_MENU_STORE', '53,54,55,67'); // userPrincipalStores.php

// menu items
define('MENU_USER_STORE_ALLOCATIONS', '53');
define('MENU_ADD_STORE_BYCHAIN_USER', '55');
define('MENU_ADD_STORE_BYUSER_USER', '67');

// files
define('LOCK_FILENAME_SEQUENCE', 'lockSequence.lock');
define('FILE_ARCHIVE_REPORTS_PATH', 'archives/reports/');
define('FILE_ARCHIVE_NOTIFICATIONS_PATH', 'archives/notifications/');
define('FILE_ARCHIVE_EXPORTS_PATH', 'archives/exports/');
define('FILE_ARCHIVE_EXTRACTS_PATH', 'archives/extracts/');
define('FILE_ARCHIVE_LOGS_PATH', 'archives/logs/');

// dirs on the php backend server itself
define('DIR_PHPBACKEND_DATA_SURESERVER_POSTINGS_TO', 'data/sureserverpostings/');
define('DIR_PHPBACKEND_DATA_SURESERVER_POSTINGS_TO_SUCCESS', 'processedSuccess/');
define('DIR_PHPBACKEND_DATA_COLLECTION', 'data/collection/');
define('DIR_PHPBACKEND_DATA_IMPORT', 'data/import/');
define('DIR_PHPBACKEND_DATA_IMPORT_PROCESSED_SUCCESS', 'data/import/processedSuccess/');
define('DIR_PHPBACKEND_DATA_IMPORT_PROCESSED_ERROR', 'data/import/processedError/');
define('DIR_PHPBACKEND_DATA_IMPORT_ZIPS', 'data/import/zipsAutoExtracted/');
define('DIR_SUCCESS_FOLDER', 'processedSuccess/');
define('DIR_ERROR_FOLDER', 'processedError/');

// customer types
define('CT_CHAIN', '1');
define('CT_CHAIN_SHORTCODE', 'C');
define('CT_STORE', '2');
define('CT_STORE_SHORTCODE', 'S');
define('CT_DEPOT_SHORTCODE', 'D');

// reports
define('REP_SOR', '1');
define('REP_STOREREPORT', '9');

// document types
define('DT_ORDINV', '1');
define('DT_UPLIFTS', '2');
define('DT_STOCKTRANSFER', '3');
define('DT_CREDITNOTE', '4'); // for orders
define('DT_MCREDIT_DAMAGES', '30');
define('DT_MCREDIT_OTHER', '31');
define('DT_MCREDIT_VALUE', '47');
define('DT_MCREDIT_PRICING', '32');
define('DT_MCREDIT_PROMOTIONS', '35');
define('DT_MCREDIT_STORE', '36');
define('DT_MDEBIT_NOTE', '33');
define('DT_MINVOICE', '34');
define('DT_ARRIVAL', '5');
define('DT_DELIVERYNOTE', '6');
define('DT_UPLIFT_CREDIT', '7');
define('DT_DEBITNOTE', '8');
define('DT_CANCELLEDNOTE', '12');
define('DT_ORDINV_ZERO_PRICE', '13');
define('DT_ARRIVAL_CORRECTION', '14');
define('DT_ASN', '15');
define('DT_FREEFORM_DOCTYPE_1', '17');
define('DT_FREEFORM_DOCTYPE_2', '18');
define('DT_FREEFORM_DOCTYPE_3', '19');
define('DT_STOCKADJUST_POS', '20');
define('DT_STOCKADJUST_NEG', '21');
define('DT_UPLIFT_DEBIT', '22');
define('DT_BUYER_GOODS_INWARD', '23');
define('DT_DESTRUCTION_DISPOSAL', '24');
define('DT_BUYER_ORIGINATED_CREDIT_CLAIM', '25');
define('DT_BUYER_ORIGINATED_DEBIT_CLAIM', '26');
define('DT_QUOTATION', '27');
define('DT_STOCK_ORDER', '45');
define('DT_SALES_ORDER', '40');
define('DT_ORDER', '44');
define('DT_PURCHASE_ORDER', '37');
define('DT_REMITTANCE', '28');
define('DT_SUPPLIER_INVOICE', '41');
define('DT_PAYMENT', '46');
define('DT_PAYMENTTO', '48');
define('DT_STATEMENT', '50');
define('DT_WALKIN_INVOICE', '52');
define('DT_REDELIVERY_INVOICE', '90');
// chains
define('CHAIN_GENERIC_OLD_CODE', '999');
define('DT_GOODS_IN_TRANSIT', '97');

// email Objects
define('EO_STORE_CARD', '1');
define('EO_PRODUCT_CARD', '2');
define('EO_DOC_CARD', '3');
define('EO_ORDER_CARD', '4');
define('EO_DOC_CARD_TI', '5');
define('EO_DOC_CARD_CR', '6');
define('EO_DOC_CARD_NCRD', '7');
define('EO_DOC_CARD_NINV', '8');
define('EO_QUOTATION_CARD', '9');
define('EO_QUOTATION_TEMP', '10');
define('EO_JOB_CARD', '11');
define('EO_PROFORMAINV_CARD', '12');

// scheduler types
define('SCD_DT_EMAIL', '1');
define('SCD_DT_FTP', '2');
define('SCD_JT_REPORT', 'R');
define('SCD_JT_SYSTEM_REPORT', 'SR');
define('SCD_OT_CSV', '1');
define('SCD_OT_HTML', '2');
define('SCD_OT_XML', '3');
define('SCD_OT_PDF', '4');

// output types
define('OT_CSV', '1');
define('OT_HTML', '2');
define('OT_XML', '3');
define('OT_ADAPTOR_SCRIPT_DECIDES', '4');
define('OT_EXPORT_FILE', '5');
define('OT_SMS_STANDARD_TEXT', '6');

// broadcast types (delivery)
define('BT_EMAIL', '1');
define('BT_SMS', '2');
define('BT_SCREEN', '3');
define('BT_FTP', '4');
define('BT_FTP_LOCALDIR', '5');

// notification types
define('NT_PRICE_DEAL_EXPIRY', '1');
define('NT_DOCKET_CAPTURE_DUPLICATION', '2');
define('NT_STOCK_THRESHOLD', '3');
define('NT_DOCUMENT_CONFIRMATION', '4');
define('NT_CREDIT_LIMIT', '5');
define('NT_ELECTRONIC_IMPORT_EXCEPTION', '6');
define('NT_EDI_PRICE_VARIANCE', '7');
define('NT_EDIFILEDEF', '8');
define('NT_EDIFILEDEF_EXPORT', '9');
define('NT_DAILY_EXTRACT_CUSTOM', '10');
define('NT_DELIVERY_EXCEPTION', '11');
define('NT_DAILY_EXTRACT_ALTCUSTOM1', '12'); // alternate extract
define('NT_DAILY_EXTRACT_ALTCUSTOM2', '13');
define('NT_DAILY_EXTRACT_ALTCUSTOM3', '14');
define('NT_DAILY_EXTRACT_ALTCUSTOM4', '15');
define('NT_EMAIL', '16');
define('NT_SMS', '17');

// notification recipient types
define('NRT_USERS', 'U');
define('NRT_CONTACT', 'C');

// contact type descriptors
define('CTD_ADMIN_CLERK', '1');
define('CTD_PALLET_CONTROLLER', '2');
define('CTD_EDI', '3');
define('CTD_USER_MANAGER', '4');
define('CTD_SYSTEM_NOTIFICATION', '5');
define('CTD_TESTING', '6');
define('CTD_EDI_PRICE_VARIANCE', '9');
define('CTD_KOS_ACCOUNTS', '12');
define('CTD_SGX_ACCOUNTS', '15');
define('CTD_MAIL_INVOICES', '13');
define('CTD_ZERO_INVOICES', '14');

// principal types
define('PT_PRINCIPAL', 'P');
define('PT_DEPOT', 'D');
define('PT_SALES_AGENT', 'S');

// error types (as used in file import)
define('ET_CUSTOMER', 'C');
define('ET_SYSTEM', 'S');

// Unit Price Types
define('UPT_CASES', '1');
define('UPT_CHARGE', '2');

// Price Types
define('PRT_PRODUCT', '1');
define('PRT_PRODUCT_GROUP', '2');

// Document Pricing Levels
define('DPL_ITEM', 'I');
define('DPL_DOCUMENT', 'D');
define('DPL_DOCUMENT_ITEM', 'B');

// Document Pricing Cumulative Types
define('DPCT_NETT_PRICE', '1'); // only apply if deal type is nett price
define('DPCT_DISCOUNTS_ZERO', '2'); // can be anything, as long as discounts are zero
define('DPCT_DISCOUNTS_CUMULATIVE', '3'); // add to existing
define('DPCT_DISCOUNTS_OVERRIDE', '4'); // override list price discounts

// Price Conflict Action
define('PCA_USE_OWN', '1');   // principal's or RT's own
define('PCA_USE_VENDOR', '2');
define('PCA_STOP', '3');

// Document Number allocation types
define('DNAT_USE_CLIENT', '1');
define('DNAT_AUTOSEQ', '2');

// Data Sources
define('DS_CAPTURE', 'CAPTURE');
define('DS_WS', 'WS');
define('DS_EDI', 'EDI');
define('BW_CSV', 'BWH');
define('DS_DIRECTSQL', 'DIRECTSQL');
define('DS_SCAN', 'SCAN');

// vendors
define('V_HARDING_VENDOR', '3');
define('V_UNKNOWN_VENDOR', '4');
define('V_CHECKERS_VENDOR', '27');
define('V_ICTECNOLOGY_VENDOR', '29'); // supplies Elvin ordes

//date ranges
define('DR_DATE', 'DR_D');
define('DR_YESTERDAY', 'DR_YTD');
define('DR_CURRENT_WEEK_START', 'DR_CUR_WK_S');
define('DR_CURRENT_WEEK_END', 'DR_CUR_WK_E');
define('DR_LAST_WEEK_START', 'DR_LST_WK_S');
define('DR_LAST_WEEK_END', 'DR_LST_WK_E');
define('DR_CURRENT_MONTH_START', 'DR_CUR_MTH_S');
define('DR_CURRENT_MONTH_END', 'DR_CUR_MTH_E');
define('DR_NO_MONTH_START', 'DR_NO_MTH_S');
define('DR_NO_MONTH_END', 'DR_NO_MTH_E');

// document status types
define('DST_UNACCEPTED', '74');
define('DST_ACCEPTED', '75');
define('DST_CANCELLED', '47');
define('DST_INVOICED', '76');
define('DST_PROCESSED', '81');
define('DST_QUEUED', '86');
define('DST_DELIVERED_POD_OK', '77');
define('DST_DIRTY_POD', '78');
define('DST_UNKNOWN_POD_STATUS', '72');
define('DST_POD_SCANNED', '73');
define('DST_INPICK', '87');
define('DST_CANCELLED_NOT_OUR_AREA', '84');
define('DST_IN_PROGRESS', '91');
define('DST_JOB_COMPLETE', '92');
define('DST_PAYMENT', '95');
define('DST_WAITING_DISPATCH', '97');
define('DST_WAREHOUSE_RECEIPT', '71');
define('DST_RE_DELIVERY', '90');

//smart event types
define('SE_EXPORT', 'E');
define('SE_NOTIFICATION', 'N');
define('SE_EXTRACT', 'EXT');
define('SE_INVOICE_UPLOAD', 'WSINV');
define('SE_INVOICE_UPLOAD_REST', 'RTINV');
define('SE_BILLING_RUN', 'BR');
define('SE_DELIVERY_EXCEPTION', 'DE');
define('SE_ZERO_REPORT', 'ZREP');

//update types
define('UPDATE_DOCUMENT_TYPE_CONFIRM', 1);
define('UPDATE_DOCUMENT_TYPE_INVOICE', 2);
define('UPDATE_DOCUMENT_TYPE_CORRECTION', 3);
define('UPDATE_DOCUMENT_TYPE_INVOICE_2', 4); // allowed to update to invoice status from unaccepted
define('UPDATE_DOCUMENT_TYPE_POD_VIT', 5);
define('UPDATE_DOCUMENT_TYPE_POD_SCANNED', 10);
define('UPDATE_DOCUMENT_TYPE_POD_ULL', 7);
define('UPDATE_DOCUMENT_TYPE_GRV', 6); // arrival
define('UPDATE_DOCUMENT_TYPE_AUDIT_LOG', 8);
define('UPDATE_DOCUMENT_TYPE_BUYER_POD', 9);

define('SHORT_URL', 'r/');

// systems
define('SYS_KWELANGA', 1);

//measuring system
define('MEASURE_MILLIMETER', 1);
define('MEASURE_CENTIMETER', 2);
define('MEASURE_METER', 3);
define('MEASURE_INCH', 4);
define('MEASURE_FOOT', 5);

// document origin action types
define('DOAT_REQUIRES_APPROVAL_REQ', 'APPROVAL_REQ');

// Retailer Types
define('RETAILER_PNP', '1');
define('RETAILER_CHECKERS', '2');

// Billing Types
define('BILLING_PRINCIPAL_ID', 171);
define('BILLING_FLAT_RATE_DEBTORS', 5);
define('BILLING_WAREHOUSE_MANAGEMENT', 6);
define('BILLING_DOCUMENT_CHARGE', 1);
define('BILLING_TURNOVER_CHARGE', 7);
define('BILLING_WEBHOSTING_CHARGE', 4);

// CapturedBy Types
define('CB_PNP_WS', 'PNP');
define('CB_CHECKERS_WS', 'CHECKERS');

// Image Types
define('IMAGE_TYPE_SIGNATURE', 'SIGNATURE');

// Payments

define('UNMATCHED_INVOICE', 0);
define('MATCHED_INVOICE_FULL', 1);
define('MATCHED_INVOICE_PARTIAL', 2);
define('MATCHED_CREDIT', 3);
define('IGNORE_INVOICE', 9);

define('PAYMENT_BY_CUSTOMER', 1);
define('PAYMENT_BY_GROUP', 2);

define('CHAIN_FILTER_PRICE', 1);
define('CHAIN_FILTER_DEBTOR', 2);
define('CHAIN_FILTER_ALL', 3);

define('PT_CASH', 1);
define('PT_EFT', 2);

define('LT_INTER_AFRICA', 115);

//API Processes
define('GETSTOCKLEVEL', '1');
define('POSTORDER', '2');

// Dear Systems api
define('SALECREATED', '1');
define('SALEORDERCREATED', '2');
define('SALEINVOICED', '3');

// API Principals
define('RHODES', '389');
define('ROLLINGCHICKEN', '380');
define('RICHES', '354');
define('GOOSEBUMPS', '369');
define('BAKEITEASY', '407');
