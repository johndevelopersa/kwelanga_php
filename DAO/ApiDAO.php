<?php

include_once('ROOT.php');
include_once($ROOT . 'PHPINI.php');
include_once($ROOT . $PHPFOLDER . 'DAO/ExceptionThrower.php');
include_once($ROOT . $PHPFOLDER . "properties/Constants.php");
include_once($ROOT . $PHPFOLDER . "DAO/MaintenanceDAO.php");


class APIDAO
{

    private $dbConn;

    function __construct($dbConn)
    {
        $this->dbConn = $dbConn;
        $this->errorTO = new ErrorTO;
    }

// ************************************************************************************************************************************
    public function getVendorUser($apiUser)
    {

        $sql = "SELECT pv.username, pv.password, pv.pv_uid
            FROM      vendor v
            LEFT JOIN principal_vendor pv ON pv.vendor_uid = v.uid 
                                          AND pv.username  = '" . mysqli_real_escape_string($this->dbConn->connection, $apiUser) . "' 
            WHERE v.name = 'WebApi'";

        return $this->dbConn->dbGetAll($sql);
    }

// ************************************************************************************************************************************
    public function addVendorUserlogEntry($apiUserUid, $logResult, $reqData, $resultCode = '')
    {

        $sql = "INSERT INTO `kwelanga_live`.`principal_vendor_log` (`vp_uid`, 
                                                                `accessdatetime`, 
                                                                `accessresult`, 
                                                                `requireddata`,
                                                                `result_code`) 
            VALUES ('" . mysqli_real_escape_string($this->dbConn->connection, $apiUserUid) . "', 
                     NOW(), 
                    '" . mysqli_real_escape_string($this->dbConn->connection, $logResult) . "',
                    '" . mysqli_real_escape_string($this->dbConn->connection, $reqData) . "',
                    '" . mysqli_real_escape_string($this->dbConn->connection, $resultCode) . "');";

        $this->errorTO = $this->dbConn->processPosting($sql, "");

        if ($this->errorTO->type == 'S') {
            $this->dbConn->dbQuery("commit");
            // echo "<br>" . $sql . "<br>";
            return $this->errorTO;
        } else {
            // echo $sql;
            return $this->errorTO;
        }
    }

// ************************************************************************************************************************************
    public function getVendorDataOrders($startD, $endD)
    {

        $sql = "select   p.name as 'Vendor Name',
                    dm.document_number as 'Document Number',
                    'Accepted' as 'Status',
                    dm.client_document_number as 'FormID',
                    psm.deliver_name as 'Store',
                    sfd.value AS 'Outer Account',
                    dh.customer_order_number as 'PO Number',
                    dm.processed_date as 'Processed Date',
                    dh.invoice_date AS 'Invoice Date',
                    dh.delivery_date AS 'Delivered Date',
                    dh.order_date as 'Order Date',
                    dd.line_no AS 'Line Number',
                    if(dm.principal_uid=337,concat('C', pp.product_code),pp.product_code) as 'Product Code', 
                    pp.product_description as 'Product',     
                    dd.ordered_qty   as 'Ordered Cases',
                    dd.document_qty  as 'Invoiced Cases',
                    dd.delivered_qty as 'Delivered Cases',
                    round(dd.ordered_qty *dd.net_price,2) as 'Excl. Value'
            from  document_master dm,   
                  document_header dh,   
                  document_detail dd, 
                  principal p,    
                  principal_store_master psm
                  LEFT JOIN .special_field_fields sff ON psm.principal_uid = sff.principal_uid AND sff.name LIKE '%outer%'
                  left JOIN .special_field_details sfd ON sff.uid = sfd.field_uid AND sfd.entity_uid = psm.uid,    
                  principal_product pp
            where dm.uid = dh.document_master_uid
            and   dm.uid = dd.document_master_uid
            and   dm.principal_uid = p.uid
            and   dh.principal_store_uid = psm.uid
            and   dd.product_uid = pp.uid
            and   dm.principal_uid in (216, 317, 326, 337, 338, 339, 315, 348, 352, 362, 267, 305)
            and   dh.document_status_uid in (74,75)
            and   dh.captured_by = 'iRam'
            and   dm.processed_date between '" . mysqli_real_escape_string($this->dbConn->connection, $startD) . "' 
                                    and     '" . mysqli_real_escape_string($this->dbConn->connection, $endD) . "' 
            order by p.name, dm.document_number;";

        return $this->dbConn->dbGetAll($sql);

    }

// ************************************************************************************************************************************
    public function getVendorDataInvoices($startD, $endD)
    {

        $sql = "select   p.name as 'Vendor Name',
                    dm.document_number as 'Document Number',
                    'Accepted' as 'Status',
                    dm.client_document_number as 'FormID',
                    psm.deliver_name as 'Store',
                    sfd.value AS 'Outer Account',
                    dh.customer_order_number as 'PO Number',
                    dm.processed_date as 'Processed Date',
                    dh.invoice_date AS 'Invoice Date',
                    dh.delivery_date AS 'Delivered Date',
                    dh.order_date as 'Order Date',
                    dd.line_no AS 'Line Number',
                    if(dm.principal_uid=337,concat('C', pp.product_code),pp.product_code) as 'Product Code', 
                    pp.product_description as 'Product',     
                    dd.ordered_qty   as 'Ordered Cases',
                    dd.document_qty  as 'Invoiced Cases',
                    dd.delivered_qty as 'Delivered Cases',
                    round(dd.ordered_qty *dd.net_price,2) as 'Excl. Value'
            from  document_master dm,   
                  document_header dh,   
                  document_detail dd, 
                  principal p,    
                  principal_store_master psm
                  LEFT JOIN .special_field_fields sff ON psm.principal_uid = sff.principal_uid AND sff.name LIKE '%outer%'
                  left JOIN .special_field_details sfd ON sff.uid = sfd.field_uid AND sfd.entity_uid = psm.uid,      
                  principal_product pp
            where dm.uid = dh.document_master_uid
            and   dm.uid = dd.document_master_uid
            and   dm.principal_uid = p.uid
            and   dh.principal_store_uid = psm.uid
            and   dd.product_uid = pp.uid
            and   dm.principal_uid in (216, 317, 326, 337, 338, 339, 315, 348, 352, 362)
            and   dh.document_status_uid in (76)
            and   dh.captured_by = 'iRam'
            and   dh.invoice_date between '" . mysqli_real_escape_string($this->dbConn->connection, $startD) . "' 
                                    and     '" . mysqli_real_escape_string($this->dbConn->connection, $endD) . "' 
            order by p.name, dm.document_number;";

        return $this->dbConn->dbGetAll($sql);


    }

// ************************************************************************************************************************************
    public function getVendorDataPod($startD, $endD)
    {


        $sql = "select   p.name as 'Vendor Name',
                    dm.document_number as 'Document Number',
                    'Accepted' as 'Status',
                    dm.client_document_number as 'FormID',
                    psm.deliver_name as 'Store',
                    sfd.value AS 'Outer Account',
                    dh.customer_order_number as 'PO Number',
                    dm.processed_date as 'Processed Date',
                    dh.invoice_date AS 'Invoice Date',
                    dh.delivery_date AS 'Delivered Date',
                    dh.order_date as 'Order Date',
                    dd.line_no AS 'Line Number',
                    if(dm.principal_uid=337,concat('C', pp.product_code),pp.product_code) as 'Product Code', 
                    pp.product_description as 'Product',     
                    dd.ordered_qty   as 'Ordered Cases',
                    dd.document_qty  as 'Invoiced Cases',
                    dd.delivered_qty as 'Delivered Cases',
                    round(dd.ordered_qty *dd.net_price,2) as 'Excl. Value'
            from  document_master dm,   
                  document_header dh,   
                  document_detail dd, 
                  principal p,    
                  principal_store_master psm
                  LEFT JOIN .special_field_fields sff ON psm.principal_uid = sff.principal_uid AND sff.name LIKE '%outer%'
                  left JOIN .special_field_details sfd ON sff.uid = sfd.field_uid AND sfd.entity_uid = psm.uid,      
                  principal_product pp
            where dm.uid = dh.document_master_uid
            and   dm.uid = dd.document_master_uid
            and   dm.principal_uid = p.uid
            and   dh.principal_store_uid = psm.uid
            and   dd.product_uid = pp.uid
            and   dm.principal_uid in (216, 317, 326, 337, 338, 339, 315, 348, 352, 362)
            and   dh.document_status_uid in (73,77,78)
            and   dh.captured_by = 'iRam'
            and   dh.delivery_date between '" . mysqli_real_escape_string($this->dbConn->connection, $startD) . "' 
                                   and     '" . mysqli_real_escape_string($this->dbConn->connection, $endD) . "' 
            order by p.name, dm.document_number;";


        return $this->dbConn->dbGetAll($sql);

    }

// ************************************************************************************************************************************
    public function getRequiredDataProducts($debtAccount, $email = FALSE)
    {

        $sql = "SELECT u.uid AS 'uID', 
                           ur.entity_uid , 
                           ur.role_id
                    FROM .users u
                    LEFT JOIN .user_role ur ON ur.user_id = u.uid AND ur.role_id = " . ROLE_BYPASS_USER_PRODUCT_RESTRICTION . "
                    WHERE u.user_email = '" . mysqli_real_escape_string($this->dbConn->connection, $email) . "'";

        $hasByPass = $this->dbConn->dbGetAll($sql);

        if (count($hasByPass) == 0) {
            $newApiDAO = new APIDAO($this->dbConn);
            $errorTO = $newApiDAO->addVendorUserlogEntry($pvUser,
                "E",
                trim($JSON['requireddata']),
                '704 - Email Lookup Failed');

            $returnResult = ["resultStatus" => "E",
                "ResultCode" => '718',
                "resultMessage" => "Email Lookup Failed - Cannot Continue"
            ];

            return $returnResult;
        }

        $repID = $hasByPass['0']['uID'];
        if ($hasByPass['0']['role_id'] == '') {
            $byPass = 0;
        } else {
            $byPass = $hasByPass['0']['role_id'];
        };

        $sql = "SELECT pp.principal_uid, 
                           pp.principal_uid,
                           pp.product_code, 
                           pp.product_description,
                           pg.outercasing_gtin,
                           pg.sku_gtin
                    FROM principal_product pp
                    LEFT JOIN user_principal_product upp ON upp.principal_product_uid = pp.uid AND upp.user_uid = " . mysqli_real_escape_string($this->dbConn->connection, $repID) . "
                    LEFT JOIN principal_product_depot_gtin pg ON pg.principal_product_uid = pp.uid 
                    WHERE pp.principal_uid = '" . mysqli_real_escape_string($this->dbConn->connection, $debtAccount) . "'
                    AND   pp.`status` = 'A'
                    AND   if(" . $byPass . "= " . ROLE_BYPASS_USER_PRODUCT_RESTRICTION . " ,1,upp.user_uid = " . mysqli_real_escape_string($this->dbConn->connection, $repID) . ");";

        $aResult = $this->dbConn->dbGetAll($sql);

        if (count($aResult) == 0) {
            $newApiDAO = new APIDAO($this->dbConn);
            $errorTO = $newApiDAO->addVendorUserlogEntry($pvUser,
                "E",
                trim($JSON['requireddata']),
                '708 - No Products set up for the rep');

            $returnResult = ["resultStatus" => "E",
                "ResultCode" => '708',
                "resultMessage" => "No Products set up for the rep - Cannot Continue"
            ];

            return $returnResult;
        }
        return $aResult;
    }

// ************************************************************************************************************************************
    public function getRequiredProductByBarcode($debtAccount, $scannedProduct)
    {

        $sql = "SELECT pp.principal_uid, 
                     pp.uid as 'productUid',
                     pp.product_code, 
                     pp.product_description,
                     pdg.sku_gtin, 
                     pdg.outercasing_gtin,
                     CONCAT('https:&&kwelangaonlinesolutions.co.za&systems&kwelanga_system&kwelanga_php&images&pics&',pp.uid,'.jpg') AS 'Image'
              FROM principal_product pp
              INNER JOIN .principal_product_depot_gtin pdg ON pdg.principal_product_uid = pp.uid 
              WHERE pp.principal_uid      = '" . mysqli_real_escape_string($this->dbConn->connection, $debtAccount) . "'
              AND   pdg.outercasing_gtin  = '" . mysqli_real_escape_string($this->dbConn->connection, $scannedProduct) . "'
              AND   pp.`status` = 'A';";
//              echo $sql;
        $scanedOuterProd = $this->dbConn->dbGetAll($sql);

        if (count($scanedOuterProd) == 1) {
            return $scanedOuterProd;
        } else {

            $sql = "SELECT pp.principal_uid, 
                           pp.uid as 'productUid',
                           pp.product_code, 
                           pp.product_description,
                           pdg.sku_gtin, 
                           pdg.outercasing_gtin
                    FROM principal_product pp
                    INNER JOIN .principal_product_depot_gtin pdg ON pdg.principal_product_uid = pp.uid 
                    WHERE pp.principal_uid      = '" . mysqli_real_escape_string($this->dbConn->connection, $debtAccount) . "'
                    AND   pdg.sku_gtin          = '" . mysqli_real_escape_string($this->dbConn->connection, $scannedProduct) . "'
                    AND   pp.`status` = 'A';";

            $scanedInnerProd = $this->dbConn->dbGetAll($sql);

            if (count($scanedInnerProd) == 1) {
                return $scanedInnerProd;
            } else {

                $sql = "SELECT pp.principal_uid, 
                                 pp.uid as 'productUid',
                                 pp.product_code, 
                                 pp.product_description,
                                 pdg.sku_gtin, 
                                 pdg.outercasing_gtin
                          FROM principal_product pp
                          LEFT JOIN .principal_product_depot_gtin pdg ON pdg.principal_product_uid = pp.uid 
                          WHERE pp.principal_uid      = '" . mysqli_real_escape_string($this->dbConn->connection, $debtAccount) . "'
                          AND   pp.product_code       = '" . mysqli_real_escape_string($this->dbConn->connection, $scannedProduct) . "'
                          AND   pp.`status` = 'A';";

                $scanedProdCode = $this->dbConn->dbGetAll($sql);

                return $scanedProdCode;
            }
        }

        return;
    }

// ************************************************************************************************************************************
    public function getUserData($uEmail)
    {

        $sql = "SELECT DISTINCT(upd.principal_id), 
                     u.user_email,
                     u.uid AS 'userUid',
                     p.uid AS 'principal_uid', 
                     p.name AS 'principal_name',
                     p.mobi_enable, 
                     u.mobi_password
              FROM .users u
              INNER JOIN .user_principal_depot upd ON upd.user_id = u.uid
              INNER JOIN .principal p ON p.uid = upd.principal_id AND   p.mobi_enable = 'Y' AND   p.`status` = 'A'
              WHERE u.user_email = '" . mysqli_real_escape_string($this->dbConn->connection, $uEmail) . "'
              AND   u.mobi_user  = 'Y';";

        //   file_put_contents( "C:/inetpub/wwwroot/systems/kwelanga_system/kwelanga_php/log/api.txt", $sql . FILE_APPEND);


        return $this->dbConn->dbGetAll($sql);

    }

// ************************************************************************************************************************************
    public function getUserWareHouses($uEmail)
    {

        $sql = "SELECT SELECT p.uid AS 'principal_uid', 
                            u.user_email,
                            p.name AS 'principal_name',
                            upd.depot_id
              FROM .users u
              INNER JOIN .user_principal_depot upd ON upd.user_id = u.uid
              INNER JOIN .principal p ON p.uid = upd.principal_id AND   p.mobi_enable = 'Y' AND   p.`status` = 'A'
              WHERE u.user_email = '" . mysqli_real_escape_string($this->dbConn->connection, $uEmail) . "'
              AND   u.mobi_user  = 'Y';";

        return $this->dbConn->dbGetAll($sql);

    }

// ************************************************************************************************************************************
    public function getArrivalUserData($uUserName, $uPassWd)
    {

        $sql = "SELECT DISTINCT(upd.principal_id), 
                     u.user_email,
                     u.password,
                     u.uid AS 'userUid',
                     p.uid AS 'principal_uid', 
                     p.name AS 'principal_name',
                     p.short_name AS 'short_name',
                     p.mobi_enable, 
                     u.mobi_password,
                     u.device_id
              FROM .principal_vendor pv
              INNER JOIN users u ON u.username = pv.username
              INNER JOIN .user_principal_depot upd ON upd.user_id = u.uid AND upd.depot_id = trim(pv.allowed_warehouses)
              INNER JOIN .principal p ON p.uid = upd.principal_id AND p.mobi_enable = 'Y'
              WHERE pv.username = '" . mysqli_real_escape_string($this->dbConn->connection, $uUserName) . "' 
              AND   pv.password = '" . mysqli_real_escape_string($this->dbConn->connection, $uPassWd) . "'   
              AND   u.mobi_user  = 'Y'
              AND   p.status <> 'S';";
//        echo "<br>";
//         echo "<pre>";
//         echo $sql;	
//        file_put_contents( "C:/inetpub/wwwroot/systems/kwelanga_system/kwelanga_php/log/api.txt", $sql . FILE_APPEND);

        return $this->dbConn->dbGetAll($sql);

    }

// ************************************************************************************************************************************
    public function getUserStoreList($uEmail, $principalUid, $reqList, $pvUser, $reqData)
    {

        // check for bypass stores 

        $sql = "SELECT *
              FROM .user_role ur
              INNER JOIN .users u ON u.uid = ur.user_id
              WHERE ur.entity_uid = " . mysqli_real_escape_string($this->dbConn->connection, $principalUid) . "
              AND   ur.role_id IN (" . ROLE_BYPASS_USER_STORE_RESTRICTION . ", " . ROLE_BYPASS_USER_CHAIN_RESTRICTION . ")
              AND   u.user_email = '" . mysqli_real_escape_string($this->dbConn->connection, $uEmail) . "'";

        $overRide = $this->dbConn->dbGetAll($sql);

        // print_r($overRide);

        if ($reqList == 'S') {
            $seList = "SELECT psm.principal_uid as 'principal_uid',
                            psm.uid AS 'psmUid',
                            psm.deliver_name AS 'psmStore',
                            psm.principal_chain_uid AS 'psmChain',
                            pcm.description AS 'chainName',
                            psm.depot_uid   AS 'psmWh',
                            d.name        AS 'depotName'";
        } else {
            $seList = "SELECT DISTINCT (psm.principal_chain_uid) AS 'psmChain',
                                       pcm.description AS 'chainName'";
        }
        if (count($overRide) == 0) {

            $sql = $seList . " 
                  FROM .principal_store_master psm
                  INNER JOIN .users u ON u.user_email = '" . mysqli_real_escape_string($this->dbConn->connection, $uEmail) . "' 
                                      AND u.mobi_user = 'Y'
                  INNER JOIN .user_principal_store ups ON ups.principal_store_uid = psm.uid AND ups.user_uid = u.uid
                  INNER JOIN principal_chain_master pcm ON pcm.uid = psm.principal_chain_uid
                  INNER JOIN depot d ON d.uid = psm.depot_uid
                  WHERE psm.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $principalUid) . "
                  AND   psm.`status` = 'A';";

            $aResult = $this->dbConn->dbGetAll($sql);
        } else {
            $sql = $seList . " 
                  FROM user_principal_depot upd
                  INNER JOIN users u ON upd.user_id AND u.uid and u.user_email = '" . mysqli_real_escape_string($this->dbConn->connection, $uEmail) . "' AND u.mobi_user = 'Y'
                  INNER JOIN principal_store_master psm ON psm.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $principalUid) . " AND psm.depot_uid = upd.depot_id AND psm.deliver_name NOT LIKE '%stock%'
                  INNER JOIN principal_chain_master pcm ON pcm.uid = psm.principal_chain_uid
                  INNER JOIN depot d ON d.uid = psm.depot_uid
                  WHERE upd.principal_id = " . mysqli_real_escape_string($this->dbConn->connection, $principalUid) . "
                  AND   psm.`status` = 'A'
                  AND   upd.user_id = u.uid;";

            $aResult = $this->dbConn->dbGetAll($sql);

        }
        if (count($aResult) == 0) {

            $newApiDAO = new APIDAO($this->dbConn);
            $errorTO = $newApiDAO->addVendorUserlogEntry($pvUser,
                "E",
                trim($reqData),
                '703 - No Stores available for Rep');

            $aResult = ["resultStatus" => "E",
                "ResultCode" => '703',
                "resultMessage" => "No Stores available for Rep - Contact Support"
            ];

            return $aResult;
        }

        return $aResult;
    }

// ************************************************************************************************************************************

    public function getDebriefDocument($principalId, $documentNo)
    {

        if (!isset($_SESSION)) session_start();
        // $principalId   = $_SESSION['principal_id'];
        $userId = $_SESSION['user_id'];
        $principalType = $_SESSION['principal_type'];
        $systemId = $_SESSION['system_id'];

        $sql = "select b.uid dm_uid
					from  user_principal_depot a,
							 	document_master b,
							 	document_header c,
								depot d
					where  a.user_id = '{$userId}'
          and    a.principal_id = '{$principalId}'
          and    b.document_number like '%{$documentNo}'
					and    a.principal_id = b.principal_uid
					and    a.depot_id = b.depot_uid
					and    b.document_type_uid in (" . DT_ORDINV . "," . DT_ORDINV_ZERO_PRICE . ")
					and    b.uid = c.document_master_uid
					and    c.document_status_uid in (" . DST_INVOICED . ")
					and    a.depot_id = d.uid
					and    d.wms = 'Y'
          " . (isset($_SESSION['depot_id']) ? (" and    b.depot_uid = '" . $_SESSION['depot_id'] . "'") : ('')) . "";

        return $this->dbConn->dbGetAll($sql);

    }

// ************************************************************************************************************************************
    public function jsonDataValidation($pvUid,
                                       $requireddata,
                                       $principalId,
                                       $custName,
                                       $delAdd1,
                                       $delAdd,
                                       $emailAdd,
                                       $reference)
    {

        // Data validation

        $sql = "SELECT pv.principal_uid, 
                     pv.allowed_warehouses, 
                     pv.allowed_to_add_stores,
                     upd.principal_id
      FROM principal_vendor pv
      LEFT JOIN users u ON u.pv_user = pv_uid
      LEFT JOIN user_principal_depot upd ON upd.user_id = u.uid
      WHERE pv.pv_uid = " . mysqli_real_escape_string($this->dbConn->connection, $pvUid) . "
      AND upd.principal_id = " . mysqli_real_escape_string($this->dbConn->connection, $principalId) . " ;";
        // echo $sql;
        $aVal = $this->dbConn->dbGetAll($sql);

        // print_r($aVal);

        // Access validation

//      echo strtoupper($requireddata);
//      echo "<br>";

        if (in_array(strtoupper($requireddata), array('GETSTOCKLEVEL', 'POSTORDER', 'POSTARRIVAL', 'POSTSTOCKADJINCREASE', 'POSTSTOCKADJDECREASE', 'POSTREDELARRIVAL'))) {
            if ($aVal[0]['principal_id'] <> mysqli_real_escape_string($this->dbConn->connection, $principalId)) {

                $this->errorTO->type = 'E';
                $this->errorTO->description = 'No access to this Principal ';
                $this->errorTO->identifier = '713';

                return $this->errorTO;

            }
            if (in_array($requireddata, array('POSTARRIVAL', 'POSTSTOCKADJINCREASE', 'POSTSTOCKADJDECREASE', 'POSTREDELARRIVAL'))) {
                if (strlen("'" . mysqli_real_escape_string($this->dbConn->connection, $reference) . "'") > 20) {

                    $this->errorTO->type = 'E';
                    $this->errorTO->description = 'Reference Cannot Exceed 20 Characters';
                    $this->errorTO->identifier = '718';

                    return $this->errorTO;
                }
            }

            // Validate BM Client Document Number

            if (mysqli_real_escape_string($this->dbConn->connection, $principalId) == 290) {

                $MaintenanceDAO = new MaintenanceDAO($this->dbConn);
                $seDup = $MaintenanceDAO->checkForDuplicates('290', mysqli_real_escape_string($this->dbConn->connection, $reference));
                if (count($seDup) > 0) {
                    $this->errorTO->type = 'E';
                    $this->errorTO->description = 'Duplicate BM Reference';
                    $this->errorTO->identifier = '718';

                    return $this->errorTO;
                } else {
                    $MaintenanceDAO = new MaintenanceDAO($this->dbConn);
                    $seDup = $MaintenanceDAO->addToDocControl('290', mysqli_real_escape_string($this->dbConn->connection, $reference), '');
                }
            }

            if (in_array($requireddata, array('POSTORDER'))) {
                if (strlen("'" . mysqli_real_escape_string($this->dbConn->connection, $custName) . "'") < 3) {

                    $this->errorTO->type = 'E';
                    $this->errorTO->description = 'Customer Name to Short or Invalid';
                    $this->errorTO->identifier = '715';

                    return $this->errorTO;
                }
                /*                  if(strlen("'" . mysqli_real_escape_string($this->dbConn->connection, $delAdd1) . "'") < 6 ) {
                
                                    $this->errorTO->type = 'E';
                                    $this->errorTO->description = 'Delivery Address to Short or Invalid' ;
                                    $this->errorTO->identifier  = '716';
                           
                                    return $this->errorTO;	
                                  } */
                if (!filter_var($emailAdd, FILTER_VALIDATE_EMAIL)) {
                    $this->errorTO->type = 'E';
                    $this->errorTO->description = 'Email address is Invalid';;
                    $this->errorTO->identifier = '707';

                    return $this->errorTO;
                }

                $this->errorTO->type = 'S';
                return $this->errorTO;

            }
            $this->errorTO->type = 'S';
            return $this->errorTO;
        }
    }

// ************************************************************************************************************************************
    public function jsonDetailLineValidation($pvUid,
                                             $requireddata,
                                             $principalId,
                                             $depotId,
                                             $prodCode,
                                             $quantity,
                                             $price)
    {

        if (in_array($requireddata, array(GETSTOCKLEVEL, POSTORDER))) {

            $sql = "SELECT *
                      FROM .principal_product pp
                      WHERE pp.principal_uid =  " . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "
                      AND   pp.product_code  = '" . mysqli_real_escape_string($this->dbConn->connection, $prodCode) . "';";

            $aProd = $this->dbConn->dbGetAll($sql);

            if (count($aProd) == 0) {
                $this->errorTO->type = 'E';
                $this->errorTO->description = 'Supplied Product Code does not exist';;
                $this->errorTO->identifier = '709';

                return $this->errorTO;
            }
            if (is_int($quantity)) {
                if ($quantity < 1 || $quantity > 5000) {
                    $this->errorTO->type = 'E';
                    $this->errorTO->description = 'Quantity out of range';
                    $this->errorTO->identifier = '710';
                } else {
                    $this->errorTO->type = 'E';
                    $this->errorTO->description = 'Quantity Invalid';
                    $this->errorTO->identifier = '710';
                }
            }
            if (is_float($price)) {
                if ($price < 0.00 || $price > 2000.00) {
                    $this->errorTO->type = 'E';
                    $this->errorTO->description = 'Price out of range';
                    $this->errorTO->identifier = '711';
                } else {
                    $this->errorTO->type = 'E';
                    $this->errorTO->description = 'Price Invalid';
                    $this->errorTO->identifier = '711';
                }
            }
        }

        $this->errorTO->type = 'S';
        return $this->errorTO;
    }

// ************************************************************************************************************************************

    function getUserForPortalAuthentication($username, $encPassword)
    {

        $systemId = "1";

        $sql = "SELECT uid, system_uid, full_name, user_email, user_cell, lastlogin, user_cell, app_metadata_json
                  FROM users
                  WHERE username = BINARY '" . mysqli_real_escape_string($this->dbConn->connection, $username) . "'
                  AND password = BINARY '" . mysqli_real_escape_string($this->dbConn->connection, $encPassword) . "'
                  -- AND system_uid = '" . mysqli_real_escape_string($this->dbConn->connection, $systemId) . "'
                  AND deleted=0";

        return $this->dbConn->dbGetAll($sql);

    }

// ************************************************************************************************************************************

    function getSalesDetailreport($startDate, $endDate, $principalUid)
    {
        $sql = "SELECT d.`name`                    AS 'Region',
                         dm.`document_number`        AS 'Document No',
                         dt.`description`            AS 'Document Type', 
                         dm.`processed_date`         AS 'Date Captured',
                         s.`description`             AS 'Status',
                         psm.`deliver_name`          AS 'Customer',
                         dh.`customer_order_number`  AS 'Customer Order No',
                         pp.product_code             AS 'Product Code',  
                         pp.product_description      AS 'Product',                               
                         dd.ordered_qty              AS 'Ordered Qty',     
                         dd.document_qty             AS 'Document Qty',
                         round(dd.extended_price,2)  AS 'Exclusive Total', 
                         round(dd.vat_amount,2)      AS 'Vat Total', 
                         round(dd.total,2)           AS 'Invoice Total' ,
                         dh.`grv_number`             AS 'GRV Number', 
                         if(dt.uid IN (4), dh2.source_document_number, '')       AS 'Source Document No',                             
                         dm.processed_date as 'Processed Date',
                         dh.`order_date` AS 'Order Date',
                         dh.invoice_date AS 'Invoice Date',
                         '" . mysqli_real_escape_string($this->dbConn->connection, $startDate) . "' AS 'Report: Start Date',
                         '" . mysqli_real_escape_string($this->dbConn->connection, $startEnd) . "' AS 'Report: End Date',           
                          rc.description as 'Reason Description'      
                  FROM `document_master` dm
                  INNER JOIN document_header dh ON dh.`document_master_uid` = dm.`uid`                              
                  INNER JOIN document_detail dd ON dd.document_master_uid   = dm.uid
                  INNER join depot d            ON dm.`depot_uid` = d.`uid`
                  INNER JOIN document_type dt   ON dm.`document_type_uid` = dt.`uid`
                  INNER JOIN .principal_store_master psm ON psm.uid = dh.principal_store_uid
                  INNER JOIN .principal_product pp ON pp.uid = dd.product_uid
                  INNER JOIN `status` s ON s.uid = dh.document_status_uid 
                  LEFT JOIN  document_tripsheet dts on dts.document_master_uid = dm.uid  AND dts.tripsheet_removed_by IS NULL
                  LEFT JOIN  transporter t on  dts.transporter_id = t.uid
                  LEFT JOIN  reason_code rc ON dh.pod_reason_uid = rc.uid
                  LEFT JOIN  document_master dm2 ON dm.principal_uid = dm2.principal_uid 
                                                 AND dh.source_document_number = dm2.document_number 
                                                 AND dm.document_type_uid = " . DT_CREDITNOTE . "
                                                 AND dm2.document_type_uid in (" . DT_ORDINV . "," . DT_ORDINV_ZERO_PRICE . ") 
                                                 AND TRIM(dh.source_document_number) !=''
                  LEFT join document_header dh2 ON dm2.uid = dh2.document_master_uid
                  WHERE dm.`principal_uid`     =  " . mysqli_real_escape_string($this->dbConn->connection, $principalUid) . "
                  AND  dm.`document_type_uid` IN (" . DT_CREDITNOTE . "," . DT_ORDINV . " )
                  AND  dh.invoice_date BETWEEN '" . mysqli_real_escape_string($this->dbConn->connection, $startDate) . "' 
                                       AND     '" . mysqli_real_escape_string($this->dbConn->connection, $endDate) . "'
                  AND  dh.document_status_uid IN  (" . DST_ACCEPTED . ", 
                                                   " . DST_CANCELLED . ", 
                                                   " . DST_INVOICED . ", 
                                                   " . DST_PROCESSED . ", 
                                                   " . DST_DELIVERED_POD_OK . ", 
                                                   " . DST_DIRTY_POD . ")
                  group by dm.uid
                  ORDER BY d.name, dt.uid, dm.document_number;";

        //     echo "<br>";
        //      echo $sql;
        //       echo "<br>";

        return $this->dbConn->dbGetAll($sql);
    }

// ************************************************************************************************************************************
    public function getwhouseUidonShortName($depotShortName)
    {
        if (trim($depotShortName) <> '') {

            // Get Depot from short name

            $sql = "SELECT d.uid AS 'wh_uid',
                           d.name AS 'wh',
                           d.short_name AS 'short_name'
                    FROM .depot d
                    WHERE d.short_name = '" . mysqli_real_escape_string($this->dbConn->connection, $depotShortName) . "';";

            $whId = $this->dbConn->dbGetAll($sql);

            return $whId;
        }
    }

// ************************************************************************************************************************************

    public function getRequiredDataStockBalances($principalId, $depotId, $stockItem)
    {

        if (trim($stockItem) <> '') {
            $selOne = "AND   pp.product_code =  '" . mysqli_real_escape_string($this->dbConn->connection, $stockItem) . "'";
        } else {
            $selOne = "";
        }
        $sql = "SELECT d.name AS 'Warehouse', 
                      pp.product_code AS 'ProdCode', 
                      pp.product_description AS 'Product', 
                      if(s.available <=0,0,if(s.available IS NULL,0,s.available)) AS 'quantity' 
               FROM principal_product pp
               LEFT JOIN stock s ON pp.uid = s.principal_product_uid AND s.depot_id = " . mysqli_real_escape_string($this->dbConn->connection, $depotId) . ",
                    depot d 
               WHERE pp.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "
               AND pp.load_to_shopify = 'Y'
               AND d.uid = " . mysqli_real_escape_string($this->dbConn->connection, $depotId) . " 
               " . $selOne . ";";

        // echo "<br>";
        // echo $sql;     
        // echo "<br>";
        return $this->dbConn->dbGetAll($sql);
    }

// ************************************************************************************************************************************
    public function getOrderDataHeaders($startD, $endD, $prinList, $whList, $statusList)
    {

        if ($whList == "") {
            $whQry = '';
        } else {
            $whQry = "AND dm.depot_uid IN (" . mysqli_real_escape_string($this->dbConn->connection, $whList) . " )";
        }

        if ($statusList == "") {
            $stQry = '';
        } else {
            $stQry = "AND dh.document_status_uid IN (" . mysqli_real_escape_string($this->dbConn->connection, $statusList) . " )";
        }

        $sql = "SELECT p.name as 'Principal_Name',
                  d.name AS 'Warehouse',
                  dm.document_number AS 'Document No',
                  dh.invoice_number AS 'Invoice Number',
                  s.description AS 'Status',
                  psm.deliver_name AS 'Customer',
                  dh.customer_order_number AS 'Customer Order No',
                  dm.processed_date AS 'Processed Date',
                  dm.processed_time AS 'Processed Time',
                  dh.order_date AS 'Order Date',
                  dh.invoice_date AS 'Invoice Date',
                  dh.cases AS 'Quantity',
                  round(dh.exclusive_total,2) AS 'Excl. Value',
                  if(u.full_name IS NULL, dh.captured_by, u.full_name) AS 'Captured By'
           FROM        document_master dm
           INNER JOIN  document_header dh ON dm.uid = dh.document_master_uid
           INNER JOIN  principal p ON dm.principal_uid = p.uid
           INNER JOIN  principal_store_master psm ON dh.principal_store_uid = psm.uid
           INNER JOIN  depot d ON dm.depot_uid = d.uid
           INNER JOIN  `status` s ON dh.document_status_uid = s.uid
           LEFT JOIN .users u ON u.uid = dh.captured_by
           WHERE dm.principal_uid IN (" . mysqli_real_escape_string($this->dbConn->connection, $prinList) . ")
           AND dm.processed_date BETWEEN '" . mysqli_real_escape_string($this->dbConn->connection, $startD) . "' 
                                 AND     '" . mysqli_real_escape_string($this->dbConn->connection, $endD) . "'
           " . $whQry . "
           " . $stQry . "
           ORDER BY p.name, dm.document_number;";

        return $this->dbConn->dbGetAll($sql);

    }

// ************************************************************************************************************************************
    public function verifyDeviceId($userId, $deviceId)
    {
        $sql = "SELECT *
           FROM users a
           WHERE a.uid = '" . mysqli_real_escape_string($this->dbConn->connection, $userId) . "' 
           AND  a.device_id = '" . mysqli_real_escape_string($this->dbConn->connection, $deviceId) . "'	";

        return $this->dbConn->dbGetAll($sql);

    }

// ************************************************************************************************************************************
    public function getDriverTsDetail($depot_id, $tripSheetNumber, $pv_uid)
    {

        $sql = "SELECT th.uid AS 'th_uid' ,
                         th.depot_uid AS 'Depot', 
                         th.tripsheet_number AS 'TripSheetNumber', 
                         th.transporter_id AS 'DriverUid', 
                         t.name AS 'DriverName', 
                         t.vehicle_reg,
                         COUNT(td.document_master_uid) as 'no_documents'
                  FROM tripsheet_header th
                  LEFT JOIN .tripsheet_detail td ON th.uid = td.tripsheet_master_uid 
                  LEFT JOIN transporter t ON t.uid = th.transporter_id
                  WHERE th.depot_uid  = '" . mysqli_real_escape_string($this->dbConn->connection, $depot_id) . "'
                  AND   th.tripsheet_number = '" . mysqli_real_escape_string($this->dbConn->connection, $tripSheetNumber) . "'
                  AND   th.tripsheet_allocated_to_loader = '" . mysqli_real_escape_string($this->dbConn->connection, $pv_uid) . "';";

        return $this->dbConn->dbGetAll($sql);

    }

// ************************************************************************************************************************************
    public function getTripSheetQuestions($depUid)
    {

        $sql = "SELECT ddq.uid AS 'ddq_uid',
                       ddq.question,
                       ddq.question_number,
                       ddq.answer
                FROM .depot_driver_questions ddq
                WHERE ddq.depot_uid = " . mysqli_real_escape_string($this->dbConn->connection, $depUid) . "
                AND   ddq.`status` = 'A';";

        return $this->dbConn->dbGetAll($sql);
    }

// ************************************************************************************************************************************
    public function getChangeDriver($depotUid)
    {

        $sql = "SELECT *
                  FROM .transporter t
                  WHERE t.depot_uid = '" . mysqli_real_escape_string($this->dbConn->connection, $depotUid) . "'
                  AND   t.`status` = 'A';";

        return $this->dbConn->dbGetAll($sql);
    }

// ************************************************************************************************************************************
    public function validateUserTripSheetDepot($pv_uid)
    {

        $sql = "SELECT pv.allowed_warehouses 
                  FROM   principal_vendor pv 
                  WHERE  pv.pv_uid = '" . mysqli_real_escape_string($this->dbConn->connection, $pv_uid) . "';";

        return $this->dbConn->dbGetAll($sql);
    }

// ************************************************************************************************************************************
    public function updateChangeDriver($depotId, $tsNumber, $driverId)
    {

        $sql = "UPDATE tripsheet_header th SET old_transporter_uid = th.transporter_id,
                                                 th.transporter_id   = '" . mysqli_real_escape_string($this->dbConn->connection, $driverId) . "'
                 WHERE th.depot_uid = '" . mysqli_real_escape_string($this->dbConn->connection, $depotId) . "'
                 AND th.tripsheet_number = '" . mysqli_real_escape_string($this->dbConn->connection, $tsNumber) . "';";

        $this->errorTO = $this->dbConn->processPosting($sql, "");

        if ($this->errorTO->type == 'S') {
            $this->dbConn->dbQuery("commit");
            return $this->errorTO;
        } else {
            echo $sql;
            return $this->errorTO;
        }
    }

//********************************************************************************************************************************************************
    public function getTripSheetInvoice($depUid, $tsNumber, $principalId, $docNo)
    {

        $sql = "SELECT dm.uid AS 'invoiceUid',
                        dm.document_number AS 'documentNumber',
                        dh.invoice_date AS 'invoiceDate',
                        dh.customer_order_number AS 'poNumber',
                        psm.deliver_name AS 'storeName',
                        COUNT(dd.uid) AS 'noLinesOnInvoice'
                 FROM tripsheet_header th
                 INNER JOIN tripsheet_detail td ON th.uid = td.tripsheet_master_uid AND td.removed_flag <> 'Y'
                 INNER JOIN document_master dm  ON dm.uid = td.document_master_uid
                 INNER JOIN document_header dh  ON dh.document_master_uid = dm.uid
                 INNER JOIN document_detail dd  ON dd.document_master_uid = dm.uid
                 INNER JOIN principal_store_master psm ON psm.uid = dh.principal_store_uid
                 WHERE th.depot_uid        = '" . mysqli_real_escape_string($this->dbConn->connection, $depUid) . "' 
                 AND   th.tripsheet_number = '" . mysqli_real_escape_string($this->dbConn->connection, $tsNumber) . "'
                 AND   dm.principal_uid    = '" . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "'
                 AND   dm.document_number  like '%" . mysqli_real_escape_string($this->dbConn->connection, $docNo) . "%';";

        // echo "<pre>";
        // echo $sql;


        return $this->dbConn->dbGetAll($sql);

    }

//********************************************************************************************************************************************************
    public function getTripSheetInvoiceProduct($docUid, $principalId, $invNumber, $prodCode)
    {
        if ($prodCode <> '' && $prodCode <> NULL) {
            for ($x = 1; $x <= 3; $x++) {
                $lookUpOn = '';
                if ($x == 1) {
                    $lookUpOn = "AND pdg.outercasing_gtin = '" . mysqli_real_escape_string($this->dbConn->connection, $prodCode) . "'";
                } elseif ($x == 2) {
                    $lookUpOn = "AND pdg.sku_gtin = '" . mysqli_real_escape_string($this->dbConn->connection, $prodCode) . "'";
                } elseif ($x == 3) {
                    $lookUpOn = "AND   pp.principal_uid = '" . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "'
   	                                      AND   pp.product_code  = '" . mysqli_real_escape_string($this->dbConn->connection, $prodCode) . "'";
                }
                if ($lookUpOn <> '') {
                    $sql = "SELECT dd.uid AS 'detailUid',
                                           pp.uid AS 'prodUid',
                                           pdg.outercasing_gtin,
                                           pp.product_code AS 'prodCode',
                                           pp.product_description AS 'prodDesc',
                                           dd.ordered_qty AS 'orderedQty',
                                           if(s.closing IS NULL,0, s.closing) AS 'availStock'
                                    FROM principal_product pp
                                    INNER JOIN .document_detail dd ON pp.uid = dd.product_uid
                                    INNER JOIN .document_master dm ON dd.document_master_uid = dm.uid
                                    LEFT JOIN .principal_product_depot_gtin pdg ON pdg.principal_product_uid = pp.uid
                                    LEFT JOIN .stock s ON s.principal_id = dm.principal_uid 
                                                       AND s.depot_id = dm.depot_uid 
                                                       AND s.principal_product_uid = dd.product_uid
                                    WHERE dm.principal_uid = '" . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "' 
                                    AND   TRIM(LEADING '0' FROM dm.document_number) = TRIM(LEADING '0' FROM '" . trim(mysqli_real_escape_string($this->dbConn->connection, $invNumber)) . "')" .
                        $lookUpOn . ";";

                    // echo "<pre>";
                    // echo $sql;
                    // echo "<br>";

                    $retProd = $this->dbConn->dbGetAll($sql);
                    if (count($retProd) > 0) {
                        break;
                    }
                }
            }
        }
        return $retProd;
    }

//********************************************************************************************************************************************************
    public function validateInvoiceProductFirst($detailUid)
    {
        // AND   dp.`status` <> 'A'
        $sql = "SELECT * 
                   FROM .document_detail_pend dp
                   WHERE dp.document_detail_uid = " . mysqli_real_escape_string($this->dbConn->connection, $detailUid) . "
                   AND   dp.`status` <> 'P'
                   AND   dp.`status` <> 'A'";


        $retFirst = $this->dbConn->dbGetAll($sql);
        return $retFirst;

    }

//********************************************************************************************************************************************************
    public function validateInvoiceProductSecond($detailUid)
    {

        $sql = "SELECT dd.ordered_qty
                   FROM .document_detail dd
                   WHERE dd.uid = " . mysqli_real_escape_string($this->dbConn->connection, $detailUid);

        $retSecond = $this->dbConn->dbGetAll($sql);
        return $retSecond;
    }

//********************************************************************************************************************************************************
    public function validateInvoiceProductThird($detailUid)
    {

        $sql = "SELECT s.closing
                   FROM .document_detail dd
                   INNER JOIN .document_master dm ON dm.uid = dd.document_master_uid
                   LEFT JOIN .stock s ON s.principal_id = dm.principal_uid AND s.depot_id = dm.depot_uid AND dd.product_uid = s.principal_product_uid
                   WHERE dd.uid = " . mysqli_real_escape_string($this->dbConn->connection, $detailUid);

        $retThird = $this->dbConn->dbGetAll($sql);

        return $retThird;
    }

//********************************************************************************************************************************************************
    public function insertIntoDocumentPend($detailUid,
                                           $docUid,
                                           $confirmQty)
    {

        $sql = "INSERT INTO document_detail_pend (document_detail_pend.document_detail_uid,
                                  document_detail_pend.document_master_uid,
                                  document_detail_pend.confirmed_qty,
                                  document_detail_pend.`status`)
                   VALUES (" . mysqli_real_escape_string($this->dbConn->connection, $detailUid) . ", 
                           " . mysqli_real_escape_string($this->dbConn->connection, $docUid) . ",
                           " . mysqli_real_escape_string($this->dbConn->connection, $confirmQty) . ",
                           'P')";

        $this->errorTO = $this->dbConn->processPosting($sql, "");

        if ($this->errorTO->type == 'S') {
            $this->dbConn->dbQuery("commit");
            return $this->errorTO;
        } else {
            echo $sql;
            return $this->errorTO;
        }

    }

// ************************************************************************************************************************************
    public function getDriverTripSheetNumber($vehicleReg)
    {

        $sql = "SELECT max(th.tripsheet_number) AS 'tsNumber', 
                      max(th.uid) AS 'tsUid'
                    FROM transporter t 
                    INNER JOIN tripsheet_header th ON th.transporter_id = t.uid AND th.tripsheet_date >= curdate() - interval 3 day
                    WHERE replace(replace(t.vehicle_reg, ' ',''),'-','') = '" . mysqli_real_escape_string($this->dbConn->connection, $vehicleReg) . "'
                    GROUP BY t.uid";

        // echo "<br>" . $sql . "<br>" ;

        $retTs = $this->dbConn->dbGetAll($sql);

        return $retTs;
    }

// ************************************************************************************************************************************
    public function getDriverTripSheetDetails($tsUID)
    {

        $sql = "SELECT wsm.uid as 'WsmUid',
                           t.name AS 'Driver',
                           replace(replace(t.vehicle_reg, ' ',''),'-','') AS 'VehicleReg',
                           t.depot_uid AS 'Depot',
                           wsm.del_point_name AS 'Store',
                           th.tripsheet_number AS 'TripSheetNo',
                           concat(wsm.latitude, ',', wsm.longitude) AS 'Coordinates',
                           concat(dm.principal_uid,' - ',trim(LEADING '0' FROM dm.document_number)) AS 'DocumentNumber'
                    FROM tripsheet_header th 
                    INNER JOIN tripsheet_detail td ON td.tripsheet_master_uid = th.uid AND td.removed_flag = 'N' 
                    INNER JOIN document_header dh ON dh.document_master_uid = td.document_master_uid 
                    INNER JOIN document_master dm ON dm.uid = dh.document_master_uid 
                    LEFT JOIN principal_warehouse_store_link pl ON pl.principal_store_master_uid = dh.principal_store_uid AND pl.depot_uid = dm.depot_uid
                    LEFT JOIN .principal_store_master psm ON psm.uid = dh.principal_store_uid 
                    LEFT JOIN .warehouse_store_master wsm ON wsm.uid = pl.warehouse_store_master_uid 
                    LEFT JOIN transporter t ON th.transporter_id = t.uid
                    
                    WHERE th.uid = '" . mysqli_real_escape_string($this->dbConn->connection, $tsUID) . "'
                    ORDER BY wsm.del_point_name ";

        // echo $sql;

        $retTs = $this->dbConn->dbGetAll($sql);

        return $retTs;

    }

// ************************************************************************************************************************************
    public function checkVehicleReg($vehicleReg)
    {

        $sql = "SELECT *
               FROM .transporter t
               WHERE replace(replace(t.vehicle_reg, ' ',''),'-','') = '" . mysqli_real_escape_string($this->dbConn->connection, $vehicleReg) . "';";

        $retreg = $this->dbConn->dbGetAll($sql);

        return $retreg;
    }

// ************************************************************************************************************************************
    public function insertDriverData($driverData)
    {

        $datatArr = json_decode($driverData, TRUE);

        foreach ($datatArr as $key => $iRow) {

            if ($key == 'documentdetail') {

                for ($x = 0; $x <= count($iRow); $x++) {
                    foreach ($iRow[$x] as $dkey => $dRow) {
                        // get doc uid

                        if ($dkey == 'tripsheet_number') {
                            $tripsheetNumber = $dRow;
                        } elseif ($dkey == 'document_number') {
                            $documentNumber = $dRow;

                            $subPrin = trim(substr($documentNumber, 0, strpos($documentNumber, '-')));
                            $subDocNo = str_pad(trim(substr($documentNumber, strpos($documentNumber, '-') + 1, 10)), 8, '0', STR_PAD_LEFT);

                            $dmsql = "SELECT dm.uid AS 'dmUid'
                             	   	                          FROM document_master dm
                             	   	                          WHERE dm.principal_uid   = '" . $subPrin . "'
                             	   	                          AND   dm.document_number = '" . $subDocNo . "'";

                            $dmUidArr = $this->dbConn->dbGetAll($dmsql);
                            $dmUid = $dmUidArr[0]['dmUid'];

                        } elseif ($dkey == 'grv_number') {
                            $grvNum = $dRow;
                        } elseif ($dkey == 'photo1') {
                            $photo1 = $dRow;
                        } elseif ($dkey == 'photo2') {
                            $photo2 = $dRow;
                        } elseif ($dkey == 'photo3') {
                            $photo3 = $dRow;
                        } elseif ($dkey == 'photo4') {
                            $photo4 = $dRow;
                        } elseif ($dkey == 'Full_partial') {
                            $fullPart = $dRow;
                        } elseif ($dkey == 'claim_number') {
                            $claimNo = $dRow;
                        } elseif ($dkey == 'pallets_delivered') {
                            $palDel = $dRow;
                        } elseif ($dkey == 'pallets_returned') {
                            $palRet = $dRow;
                        } elseif ($dkey == 'cases_delivered') {
                            $cases = $dRow;
                        } elseif ($dkey == 'cases_returned') {
                            $casesRet = $dRow;
                        } elseif ($dkey == 'signatures') {
                            $sig = $dRow;
                        } elseif ($dkey == 'coordinates') {
                            $gps = $dRow;
                        } elseif ($dkey == 'uid') {
                            $rMeUid = $dRow;
                            $sql = "INSERT INTO app_data (app_data.tripsheet_number,
                                                                      app_data.document_number,
                                                                      app_data.document_master_uid,
                                                                      app_data.grv_number,
                                                                      app_data.photo1,
                                                                      app_data.photo2,
                                                                      app_data.photo3,
                                                                      app_data.photo4,
                                                                      app_data.Full_partial,
                                                                      app_data.claim_number,
                                                                      app_data.pallets_delivered,
                                                                      app_data.pallets_returned,
                                                                      app_data.cases_delivered,
                                                                      app_data.cases_returned,
                                                                      app_data.signature,
                                                                      app_data.gps,
                                                                      app_data.rme_uid)
                                                          VALUES ('" . mysqli_real_escape_string($this->dbConn->connection, $tripsheetNumber) . "',
                                                                  '" . mysqli_real_escape_string($this->dbConn->connection, $documentNumber) . "',
                                                                  '" . mysqli_real_escape_string($this->dbConn->connection, $dmUid) . "',
                                                                  '" . mysqli_real_escape_string($this->dbConn->connection, $grvNum) . "',
                                                                  '" . $photo1 . "',
                                                                  '" . $photo2 . "',
                                                                  '" . $photo3 . "',
                                                                  '" . $photo4 . "',
                                                                  '" . mysqli_real_escape_string($this->dbConn->connection, $fullPart) . "',
                                                                  '" . mysqli_real_escape_string($this->dbConn->connection, $claimNo) . "',
                                                                  '" . mysqli_real_escape_string($this->dbConn->connection, $palDel) . "',
                                                                  '" . mysqli_real_escape_string($this->dbConn->connection, $palRet) . "',
                                                                  '" . mysqli_real_escape_string($this->dbConn->connection, $cases) . "',
                                                                  '" . mysqli_real_escape_string($this->dbConn->connection, $casesRet) . "',
                                                                  '" . $sig . "',
                                                                  '" . mysqli_real_escape_string($this->dbConn->connection, $gps) . "',
                                                                  '" . mysqli_real_escape_string($this->dbConn->connection, $rMeUiD) . "');";

                            $this->errorTO = $this->dbConn->processPosting($sql, "");

                            if ($this->errorTO->type == 'S') {
                                $this->dbConn->dbQuery("commit");
                            } else {
                                return $this->errorTO;
                            }
                        }
                    }
                }  // End of for
            }
        }
        return $this->errorTO;
    }

//********************************************************************************************************************************************************
    public function getRequiredDataProcessing($requiredData)
    {

        $sql = "SELECT *
               FROM .api_required_data ar
               WHERE ar.required_data = '" . mysqli_real_escape_string($this->dbConn->connection, $requiredData) . "';";

        $reqData = $this->dbConn->dbGetAll($sql);

        return $reqData;
    }

//********************************************************************************************************************************************************
    public function getDepotRpOrders($depotId, $days)
    {

        $sql = "SELECT d.name AS 'Warhouse',
                    if(p.short_name IS NULL,p.name,p.short_name) AS 'Principal',
                    count(dm.document_number) AS 'OrderCount',
                    sum(dh.cases) AS 'Qty',
                    round(sum(dh.exclusive_total),2) AS 'Excl_Value',
                    round(avg(datediff(curdate(),dh.order_date)),1) AS 'Days_From_Order',
                    NOW()
             FROM " . iDATABASE . ".document_master dm
             INNER JOIN " . iDATABASE . ".document_header dh ON dh.document_master_uid = dm.uid
             INNER JOIN " . iDATABASE . ".principal_store_master psm ON psm.uid = dh.principal_store_uid
             INNER JOIN " . iDATABASE . ".principal p ON p.uid = dm.principal_uid
             INNER JOIN " . iDATABASE . ".depot d ON d.uid = dm.depot_uid
             INNER JOIN " . iDATABASE . ".`status` s ON s.uid = dh.document_status_uid
             WHERE dm.depot_uid IN  (" . mysqli_real_escape_string($this->dbConn->connection, $depotId) . ")
             AND   dh.document_status_uid IN (74,75, 87)
             AND   p.uid NOT IN (406,74, 393, 364, 30)
             AND   dm.document_type_uid = 1
             AND   dh.invoice_date between '2021-01-01' AND (curdate() - INTERVAL " . mysqli_real_escape_string($this->dbConn->connection, $days) . " day)
             group BY d.name, p.name";

        $reqData = $this->dbConn->dbGetAll($sql);

        return $reqData;

    }

//********************************************************************************************************************************************************
    public function validatePrinId($principalId, $pvUser, $reqData)
    {

        if (trim($principalId) == '' || trim($principalId) == NULL) {

            $newApiDAO = new APIDAO($this->dbConn);
            $errorTO = $newApiDAO->addVendorUserlogEntry(trim($pvUser),
                "E",
                trim($reqData),
                '706 - Principal Code Not Supplied');

            $returnResult = ["resultStatus" => "E",
                "ResultCode" => '706',
                "resultMessage" => "Principal Code Not Supplied  - Cannot Continue"];

            return $returnResult;

        }

        // Check API User Principal - pv_uid

        $sql = "SELECT pv.username, pv.password, pv.pv_uid, pv.principal_uid
                     FROM       vendor v
                     INNER JOIN principal_vendor pv ON pv.vendor_uid = v.uid 
                                                    AND pv.pv_uid  = '" . mysqli_real_escape_string($this->dbConn->connection, $pvUser) . "'  
                     WHERE v.name = 'WebApi'
                     AND   pv.principal_uid = '" . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "'";

        $valPs = $this->dbConn->dbGetAll($sql);

        if (count($valPs) == 0) {

            // Check API User Principal - pv_name
            $sql = "SELECT pv.username, pv.password, pv.pv_uid, pv.principal_uid
                           FROM       vendor v
                           INNER JOIN principal_vendor pv ON pv.vendor_uid = v.uid 
                                                       AND pv.username     = '" . mysqli_real_escape_string($this->dbConn->connection, $pvUser) . "'  
                     WHERE v.name = 'WebApi'
                     AND   pv.principal_uid = '" . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "'";

            $uservalPs = $this->dbConn->dbGetAll($sql);

            if (count($uservalPs) == 0) {
                $newApiDAO = new APIDAO($this->dbConn);
                $errorTO = $newApiDAO->addVendorUserlogEntry(trim($pvUser),
                    "E",
                    trim($reqData),
                    '705 - API User does not have access to Principal');

                $returnResult = ["resultStatus" => "E",
                    "ResultCode" => '705',
                    "resultMessage" => "API User does not have access to Principal  - Cannot Continue"];

                return $returnResult;
            } else {

                $returnResult = ["resultStatus" => "S",
                    "ResultCode" => '000',
                    "resultMessage" => "Successfull"];

                return $returnResult;
            }
        }
    }

//********************************************************************************************************************************************************
    public function validateEmailAdd($email, $pvUser, $reqData)
    {

        if (!filter_var(trim($email), FILTER_VALIDATE_EMAIL)) {

            $newApiDAO = new APIDAO($this->dbConn);
            $errorTO = $newApiDAO->addVendorUserlogEntry(trim($pvUser),
                "E",
                trim($reqData),
                '702 - Invalid Email Address');
            $returnResult = [
                "resultStatus" => "E",
                "ResultCode" => '702',
                "resultMessage" => "Invalid Email Address"
            ];

            return $returnResult;
        }
    }

//********************************************************************************************************************************************************
    public function getAllPriceProducts($prinId, $cGroup, $groupName, $prodCode, $productDesc)
    {

        $sql = "SELECT pp.uid AS 'ProdId',
                    '" . mysqli_real_escape_string($this->dbConn->connection, $cGroup) . "' AS 'PriceGroupId',
                    '" . mysqli_real_escape_string($this->dbConn->connection, $groupName) . "' AS 'PriceGroup',
                    pp.product_code AS 'ProdCode',
                    pp.product_description AS 'Product',
                    if(p.deal_type_uid=4,'Percent',if(p.deal_type_uid=2,'Amount','Net Price')) AS 'DiscountType',
                    p.discount_value AS 'DiscountValue',
                    p.list_price AS 'Price',
                    p.start_date AS 'StartDate',
                    p.end_date   AS 'EndDate',
                    ''           AS 'Comment'
             FROM  principal_product_category ppc
             LEFT JOIN  principal_product pp ON pp.major_category  = ppc.uid
             LEFT JOIN  pricing p ON p.price_type_uid =  " . PRT_PRODUCT_GROUP . " AND p.principal_product_uid = pp.major_category
             WHERE p.principal_uid                    =  " . mysqli_real_escape_string($this->dbConn->connection, $prinId) . "
             AND   pp.product_code                    = '" . mysqli_real_escape_string($this->dbConn->connection, $prodCode) . "'
             AND   p.chain_store                      =  " . mysqli_real_escape_string($this->dbConn->connection, $cGroup) . "
             AND   NOW() BETWEEN p.start_date AND p.end_date;";

        $priceData = $this->dbConn->dbGetAll($sql);

        if (count($priceData) == 0) {
            $sql = "SELECT pp.uid AS 'ProdId',
                                   '" . mysqli_real_escape_string($this->dbConn->connection, $cGroup) . "' AS 'PriceGroupId',
                                   '" . mysqli_real_escape_string($this->dbConn->connection, $groupName) . "' AS 'PriceGroup',
                                   pp.product_code AS 'ProdCode',
                                   pp.product_description AS 'Product',
                                   if(p.deal_type_uid=4,'Percent',if(p.deal_type_uid=2,'Amount','Net Price')) AS 'DiscountType',
                                   p.discount_value AS 'DiscountValue',
                                   p.list_price AS 'Price',
                                   p.start_date AS 'StartDate',
                                   p.end_date   AS 'EndDate',
                                   ''  AS 'Comment'
                            FROM  principal_product pp
                            LEFT JOIN  pricing p ON p.price_type_uid =  " . PRT_PRODUCT . " AND p.principal_product_uid = pp.uid
                            WHERE p.principal_uid                    =  " . mysqli_real_escape_string($this->dbConn->connection, $prinId) . "
                            AND   pp.product_code                    = '" . mysqli_real_escape_string($this->dbConn->connection, $prodCode) . "'
                            AND   p.chain_store                      =  " . mysqli_real_escape_string($this->dbConn->connection, $cGroup) . "
                            AND   NOW() BETWEEN p.start_date AND p.end_date;";

            $priceData = $this->dbConn->dbGetAll($sql);

        }
        if (count($priceData) != 0) {
            $returnPriceArr[] = [
                "GroupId" => mysqli_real_escape_string($this->dbConn->connection, $cGroup),
                "Group" => mysqli_real_escape_string($this->dbConn->connection, $groupName),
                "ProdCode" => $priceData[0]["ProdCode"],
                "Product" => $priceData[0]["Product"],
                "DealType" => $priceData[0]["DiscountType"],
                "DiscountValue" => $priceData[0]["DiscountValue"],
                "Price" => $priceData[0]["Price"],
                "ExclIncl" => 'EX',
                "StartDate" => $priceData[0]["StartDate"],
                "EndDate" => $priceData[0]["EndDate"],
                "Comment" => $priceData[0]["Comment"]
            ];

        } else {
            $returnPriceArr[] = [
                "GroupId" => mysqli_real_escape_string($this->dbConn->connection, $cGroup),
                "Group" => mysqli_real_escape_string($this->dbConn->connection, $groupName),
                "ProdCode" => mysqli_real_escape_string($this->dbConn->connection, $prodCode),
                "Product" => mysqli_real_escape_string($this->dbConn->connection, $cGroup),
                "DealType" => '',
                "DiscountValue" => 0,
                "Price" => 0,
                "ExclIncl" => '',
                "StartDate" => '',
                "EndDate" => '',
                "Comment" => 'No price found for this product'
            ];
        }

        return $returnPriceArr;

    }

//********************************************************************************************************************************************************
    public function getImportDocuments($prinId, $Type)
    {

        if ($Type == 'INVOICE') {
            $typeList = DT_ORDINV;
            $statusList = DST_INVOICED . "," . DST_DELIVERED_POD_OK . "," . DST_DIRTY_POD;
        } elseif ($Type == 'CREDIT') {
            $typeList = DT_CREDITNOTE . "," . DT_MCREDIT_OTHER . "," . DT_MCREDIT_DAMAGES . "," . DT_MCREDIT_PRICING;
            $statusList = DST_PROCESSED;
        }

        $principalId = mysqli_real_escape_string($this->dbConn->connection, $prinId);

        $sql = "SELECT dm.uid as 'dmUid',
                         sfd.value AS 'customerNumber',
                         sfd1.value AS 'whLocation',
                         psm.deliver_name 'CustomerName',
                         psm.deliver_add1 AS 'CustomerAdd1',
                         psm.deliver_add2 AS 'CustomerAdd2',
                         psm.deliver_add3 AS 'CustomerAdd3',
                         dh.customer_order_number AS 'purchaseOrderNumber',	
                         dh.invoice_date AS 'invoiceDate',
                         dh.order_date AS 'orderDate',
                         dh.claim_number AS 'claimNumber',
                         dh.invoice_number AS 'invoiceNumber',
                         dh.source_document_number AS 'sourceDocumentNumber',
                         dh2.invoice_number AS 'sourceInvoiceNumber',
                         dh2.customer_order_number AS 'sourcePurchaseOrderNumber',
                         dm.document_number AS 'salesOrderNumber',
                         dm.depot_uid AS 'warehouseCode',
                         dm.alternate_document_number AS 'alternateDocumentNumber',
                         p.document_prefix1 as 'preFix1',
                         p.document_prefix2 as 'preFix2',
                         p.document_prefix3 as 'preFix3',
                         p.document_prefix4 as 'preFix4',
                         pp.product_code AS 'productCode',
                         pp.product_description AS 'productDescription',
                         pg.outercasing_gtin AS 'outerCaseBarcode',
                         dd.ordered_qty  AS 'orderedQuantity',
                         dd.document_qty AS 'invoicedQuantity',
                         dd.selling_price AS  'sellingPrice',
                         dd.discount_value AS 'Discount',
                         dd.net_price     AS  'nettPrice',
                         dd.extended_price AS  'extendedPrice',
                         dd.vat_amount AS  'vatAmount',
                         dd.total AS 'invoiceTotal',
                         rc.description AS 'credReason',
                         rc.uid AS 'credReasonId'
                  FROM .document_master dm
                  INNER JOIN document_header dh ON dm.uid = dh.document_master_uid
                  INNER JOIN document_detail dd ON dm.uid = dd.document_master_uid
                  INNER JOIN principal_store_master psm ON psm.uid = dh.principal_store_uid
                  INNER JOIN principal_product pp ON pp.uid = dd.product_uid
                  INNER JOIN principal p ON p.uid = dm.principal_uid
                  LEFT JOIN special_field_fields sff ON sff.uid AND sff.principal_uid = " . $principalId . " AND sff.processing_order = 1
                  LEFT JOIN special_field_details sfd ON sfd.field_uid = sff.uid AND sfd.entity_uid = dh.principal_store_uid
                  LEFT JOIN special_field_fields  sff1 ON sff1.uid AND sff1.principal_uid = " . $principalId . " AND sff1.processing_order = 2
                  LEFT JOIN special_field_details sfd1 ON sfd1.field_uid = sff1.uid AND sfd1.entity_uid = dm.depot_uid
                  LEFT JOIN principal_product_depot_gtin pg ON pg.principal_product_uid = pp.uid
                  LEFT JOIN document_master dm2 ON dm2.principal_uid = " . $principalId . "
                                                AND dm2.document_number = dh.source_document_number
                  LEFT JOIN document_header dh2 ON dm2.uid = dh2.document_master_uid
                  LEFT JOIN reason_code rc ON rc.uid = dh.pod_reason_uid
                  WHERE dm.principal_uid = " . $principalId . "
                  AND   dm.document_type_uid IN ( " . $typeList . " )
                  AND   dh.document_status_uid IN ( " . $statusList . " )
                  AND   dm.api_extract in ('R', 'N');";

//echo $sql;
//die();
        $docArr = $this->dbConn->dbGetAll($sql);
        return $docArr;
    }

//********************************************************************************************************************************************************
    public function getUserStockWareHouses($principalId, $usrEmail)
    {

        $sql = "SELECT upd.depot_id
               FROM  users u
               INNER JOIN  user_principal_depot upd ON upd.user_id = u.uid
               INNER JOIN  principal p ON p.uid = upd.principal_id AND   p.`status` = 'A'
               WHERE u.user_email = '" . $usrEmail . "'
               AND   p.uid = " . $principalId . ";";

        $usrLst = $this->dbConn->dbGetAll($sql);
        return $usrLst;

    }

//********************************************************************************************************************************************************
    public function getWareHouseStockLevels($principalId, $whList)
    {

        $sql = "SELECT s.principal_id AS  'principalUid',
                      p.name AS 'principal',
                      s.depot_id AS 'warehouseUid',
                      d.name AS 'Warehouse',
                      s.stock_item AS 'ProdCode',
                      s.stock_descrip AS 'Product',
                      s.closing AS 'ClosingLevel',
                      s.available AS 'AvailableLevel',
                      NOW() AS 'DateTime'	 
               FROM stock s
               LEFT JOIN depot d on d.uid = s.depot_id
               LEFT JOIN principal p ON p.uid = s.principal_id
               WHERE s.principal_id = " . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "
               AND   s.depot_id in (" . $whList . ")
               ORDER BY p.name, d.name, s.stock_item ;";

        $stkLst = $this->dbConn->dbGetAll($sql);
        return $stkLst;
    }

//********************************************************************************************************************************************************
    public function getPrincipalRepList($principalId, $usrEmail)
    {

        $sql = "SELECT psr.uid AS 'repId',
                       psr.principal_uid AS 'Principal',
                       psr.first_name AS 'firstName',
                       psr.surname AS 'surname',
                       psr.email_addr AS 'emailAddress'
                FROM .principal_sales_representative psr
                WHERE psr.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "
                AND   psr.`status` = 'A'
                AND   psr.allow_mobi_capture = 'Y';";

        $repLst = $this->dbConn->dbGetAll($sql);
        return $repLst;
    }

//********************************************************************************************************************************************************
    public function checkUserRole($principalId, $usrEmail, $hasRole)
    {

        $sql = "SELECT u.uid, u.username, u.user_email, ur.role_id
               FROM users u 
               INNER JOIN user_role ur ON ur.user_id = u.uid
               WHERE u.user_email = '" . mysqli_real_escape_string($this->dbConn->connection, $usrEmail) . "'
               AND   ur.entity_uid = " . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "
               AND   ur.role_id = " . mysqli_real_escape_string($this->dbConn->connection, $hasRole) . ";";

        $userRole = $this->dbConn->dbGetAll($sql);
        return $userRole;

    }

//********************************************************************************************************************************************************
    public function updateConfirmationStatus($principalId, $type, $status, $docno)
    {

        if (mysqli_real_escape_string($this->dbConn->connection, $type) == 'INVOICE') {
            $typeId = DT_ORDINV;
            $cleanDoc = "TRIM(LEADING '0' FROM dh.invoice_number) = TRIM(LEADING '0' FROM (REPLACE('" . mysqli_real_escape_string($this->dbConn->connection, $docno) . "',p.document_prefix1, ''))";

        } elseif (mysqli_real_escape_string($this->dbConn->connection, $type) == 'CREDIT') {
            $typeId = DT_CREDITNOTE;
            $cleanDoc = "TRIM(LEADING '0' FROM dm.alternate_document_number) = TRIM(LEADING '0' FROM (REPLACE('" . mysqli_real_escape_string($this->dbConn->connection, $docno) . "',p.document_prefix2, ''))";
        } else {
            $returnResult = [
                "resultStatus" => "E",
                "ResultCode" => '830',
                "resultMessage" => "Invalid Confirmation Type"
            ];

            return $returnResult;
        }

        if (mysqli_real_escape_string($this->dbConn->connection, $status) == 'S') {
            $statusAction = 'Y';
        } elseif (mysqli_real_escape_string($this->dbConn->connection, $status) == 'R') {
            $statusAction = 'R';
        } else {
            $returnResult = [
                "resultStatus" => "E",
                "ResultCode" => '831',
                "resultMessage" => "Invalid Confirmation Status"
            ];

            return $returnResult;
        }
        $sql = "UPDATE  document_master dm
                INNER JOIN principal p ON p.uid = dm.principal_uid
                INNER JOIN document_header dh ON dh.document_master_uid = dm.uid SET dm.api_extract = '" . $statusAction . "',
                                                                          dm.api_extract_datetime = NOW()
                WHERE dm.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "
                AND   dm.document_type_uid IN (" . $typeId . ")
                AND   " . $cleanDoc . ");";

        $this->dbConn->dbQuery($sql);

        if (!$this->dbConn->dbQueryResult) {
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description = "Failed to Insert Batch Row";
        } else {
            $this->dbConn->dbQuery("commit");
            $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
            $this->errorTO->description = "Successful";
        }

        return $this->errorTO;
    }
//********************************************************************************************************************************************************

    private $cacheLookupArray = [];

    private function getChainForPricesCache($principalId, $pChain, $prcType)
    {
        //build a unique key for each lookup
        $cacheKey = sprintf("chainLookUp_%s_%s_%s)", $principalId, $pChain, $prcType);

        if (isset($this->cacheLookupArray[$cacheKey])) {
            //cache hit
            return $this->cacheLookupArray[$cacheKey];
        }

        // Get principal chain ID from Spec fieldField
        if ($prcType == 'S') {
            $sql = "SELECT sfd.entity_uid
                         FROM .special_field_details sfd
                         WHERE sfd.value = '" . mysqli_real_escape_string($this->dbConn->connection, $pChain) . "'
                         AND  sfd.field_uid = (SELECT sff.uid
                                               FROM .special_field_fields sff 
                                               WHERE sff.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "
                                               AND   sff.`type` = 'S')";
            $prnChain = $this->dbConn->dbGetAll($sql);

            $chainId = $prnChain[0]['entity_uid'] ?? false;

            //put in the in-memory cache
            $this->cacheLookupArray[$cacheKey] = $chainId;

            return $chainId;
        }


        $sql = "SELECT pcm.uid AS 'chnUid'
                        FROM .principal_chain_master pcm
                        LEFT JOIN .special_field_details sfd ON pcm.uid = sfd.entity_uid 
                                                             AND  sfd.field_uid IN (SELECT sff.uid
                                                                                    FROM .special_field_fields sff
                                                                                    WHERE sff.principal_uid = pcm.principal_uid
                                                                                    AND   sff.`type` = 'C')
                        WHERE pcm.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "
                        AND   sfd.value = '" . mysqli_real_escape_string($this->dbConn->connection, $pChain) . "'";

        $prnChain = $this->dbConn->dbGetAll($sql);

        $chainId = $prnChain[0]['chnUid'] ?? false;

        //put in the in-memory cache
        $this->cacheLookupArray[$cacheKey] = $chainId;

        return $chainId;
    }
//********************************************************************************************************************************************************

    private function getProductByGTINCache($principalId, $productGtin)
    {
        //build a unique key for each lookup
        $cacheKey = sprintf("productLookUp_%s_%s)", $principalId, $productGtin);

        if (isset($this->cacheLookupArray[$cacheKey])) {
            //cache hit
            return $this->cacheLookupArray[$cacheKey];
        }

        // Get principal product Code From GTIN
        $sql = "SELECT pp.uid AS 'prdUID'
               FROM principal_product pp
               LEFT JOIN principal_product_depot_gtin pg ON pg.principal_product_uid = pp.uid
               WHERE pp.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "
               AND   pg.outercasing_gtin = '" . mysqli_real_escape_string($this->dbConn->connection, $productGtin) . "'";

        $prnPrdUid = $this->dbConn->dbGetAll($sql);

        $prodUid = $prnPrdUid[0]['prdUID'] ?? false;

        //put in the in-memory cache
        $this->cacheLookupArray[$cacheKey] = $prodUid;

        return $prodUid;
    }
//********************************************************************************************************************************************************

    public function loadPrices($principalId, $pChain, $prnGtin, $lPrice, $dtype, $sDate, $eDate, $prcType)
    {
        $chainUid = $this->getChainForPricesCache($principalId, $pChain, $prcType);

        //check the chain before executing more queries
        if (!$chainUid) {
            $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
            $this->errorTO->description = "ignoring update, unknown chain code: $pChain";
            return $this->errorTO;
        }

        // Get principal product ID from Spec fieldField
        $prodUid = $this->getProductByGTINCache($principalId, $prnGtin);
        if (!$prodUid) {
            $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
            $this->errorTO->description = "ignoring update, unknown product:$prnGtin";
            return $this->errorTO;
        }

        // Clear all existing prices for this Chain
        $sql = "DELETE FROM pricing 
                      WHERE pricing.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "
                      AND   pricing.chain_store   = " . mysqli_real_escape_string($this->dbConn->connection, $chainUid) . "
                      AND   pricing.principal_product_uid = " . mysqli_real_escape_string($this->dbConn->connection, $prodUid);

        $this->dbConn->dbQuery($sql);

        if (!$this->dbConn->dbQueryResult) {
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            return $this->errorTO;
        }

        if (mysqli_real_escape_string($this->dbConn->connection, $dtype) == 'N') {
            $dealType = VAL_DEALTYPE_NETT_PRICE;
        } elseif (mysqli_real_escape_string($this->dbConn->connection, $dtype) == 'A') {
            $dealType = VAL_DEALTYPE_AMOUNT_OFF;
        } elseif (mysqli_real_escape_string($this->dbConn->connection, $dtype) == 'P') {
            $dealType = VAL_DEALTYPE_PERCENTAGE;
        }

        if ($prcType == "S") {
            $prcLvl = CT_STORE;
        } else {
            $prcLvl = CT_CHAIN;
        }

        $sql = "INSERT INTO pricing (pricing.customer_type_uid,
                                           pricing.price_type_uid,
                                           pricing.chain_store,
                                           pricing.principal_product_uid,
                                           pricing.principal_uid,
                                           pricing.list_price,
                                           pricing.deal_type_uid,
                                           pricing.start_date,
                                           pricing.end_date,
                                           pricing.user_uid,
                                           pricing.capture_date)
                      VALUES (" . $prcLvl . ",
                              " . PRT_PRODUCT . ",
                              " . mysqli_real_escape_string($this->dbConn->connection, $chainUid) . ",                     
                              " . mysqli_real_escape_string($this->dbConn->connection, $prodUid) . ",
                              " . mysqli_real_escape_string($this->dbConn->connection, $principalId) . ",     
                              " . mysqli_real_escape_string($this->dbConn->connection, $lPrice) . ",     
                              '" . mysqli_real_escape_string($this->dbConn->connection, $dealType) . "',     
                              '" . mysqli_real_escape_string($this->dbConn->connection, $sDate) . "',     
                              '" . mysqli_real_escape_string($this->dbConn->connection, $eDate) . "',     
                              2197,     
                              NOW())";

        $this->dbConn->dbQuery($sql);
        if (!$this->dbConn->dbQueryResult) {
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            return $this->errorTO;
        }

        $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
        $this->errorTO->description = "successfully updated prices ($pChain.$prnGtin)";
        return $this->errorTO;
    }


//********************************************************************************************************************************************************
    public function getPrincipalStoreList($principalId)
    {

        $sql = "SELECT psm.principal_uid AS 'principalUid',
                       psm.uid AS 'storeId',
                       sfd.value AS 'customerAccount',
                       psm.deliver_name AS 'DeliverName',
                       psm.deliver_add1 AS 'DeliverAddress1',
                       psm.deliver_add3 AS 'DeliverAddress2',
                       psm.deliver_add3 AS 'DeliverAddress3' ,
                       psm.bill_name AS 'InvoiceName',
                       psm.bill_add1 AS 'InvoiceAddress1',
                       psm.bill_add2 AS 'InvoiceAddress2',
                       psm.bill_add3 AS 'InvoiceAddress3',
                       psm.vat_number AS 'vatNumber',
                       psm.branch_code as 'branch',
                       psm.ean_code AS 'GLN',
                       d.name       AS 'depotName',
                       sfd1.value AS  'defaultWarehouse',
                       sfd2.value AS  'priceList1',
                       psm.ledger_credit_limit  AS  'creditLimit',
                       psm.on_hold as 'onHold',
                       psm.ledger_balance AS 'customerBalance'
                FROM principal_store_master psm
                LEFT JOIN special_field_fields sff ON sff.principal_uid = 450 AND sff.`type` = 'S'
                LEFT JOIN special_field_fields sff1 ON sff1.principal_uid = 450 AND sff1.`type` = 'D'
                LEFT JOIN special_field_fields sff2 ON sff2.principal_uid = 450 AND sff2.`type` = 'C'
                LEFT JOIN special_field_details sfd ON sfd.entity_uid = psm.uid AND sff.uid = sfd.field_uid
                LEFT JOIN special_field_details sfd1 ON sfd1.entity_uid = psm.depot_uid AND sff1.uid = sfd1.field_uid
                LEFT JOIN special_field_details sfd2 ON sfd2.entity_uid = psm.principal_chain_uid AND sff2.uid = sfd2.field_uid
                LEFT JOIN depot d ON d.uid = psm.depot_uid
                WHERE psm.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $principalId) . ";";

        $storeList = $this->dbConn->dbGetAll($sql);
        return $storeList;
    }

//********************************************************************************************************************************************************

    /*

    SELECT upd.depot_id
    FROM  users u
    INNER JOIN  user_principal_depot upd ON upd.user_id = u.uid
    INNER JOIN  principal p ON p.uid = upd.principal_id AND   p.`status` = 'A'
    WHERE u.user_email = 'jlewis@primapasta.co.za'
    AND   p.principal_code = 450


    SELECT a.principal_id AS  "principalUid",
           a.depot_id AS "warehouseUid",
             a.stock_item AS "ProdCode",
             a.stock_descrip AS "Product",
             a.closing AS "ClosingLevel",
             a.available AS "AvailableLevel"
    FROM .stock a
    WHERE a.principal_id = 450
    AND   a.depot_id = 393
    */
//********************************************************************************************************************************************************


}