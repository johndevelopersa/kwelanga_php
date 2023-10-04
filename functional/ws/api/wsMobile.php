<?php

set_time_limit(60*1);

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER."properties/Constants.php");
include_once($ROOT.$PHPFOLDER."properties/ServerConstants.php");
include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT . $PHPFOLDER . 'DAO/ApiDAO.php');

$ws = new WS();

if (!isset($_SESSION)) session_start();

$ws->outputHeaders();
$ws->validate();
if ($ws->processService()!==true) return;

class WS {
  private $dbConn;
  private $postSN;
  private $data;
  private $rawData;
  private $userResult;
  private $appVersion = 1.7;

  function __construct() {
  	$this->dbConn = new dbConnect();
    $this->dbConn->dbConnection();

    $this->postSN = ((isset($_REQUEST["sn"]))?addslashes($_REQUEST["sn"]):false); // the MD5 string - because the line is not SSL and there is no server session maintained, we do this to promote some level of security
    // $this->rawData = $data = preg_replace('/[^A-Za-z0-9\.\-\_\=\:\{\}\[\],; \"\'\#\@\!\$\%\^\&\*\(\)\+\=\|\<\>\?\~\/]/', '', file_get_contents("php://input")); // needs to be done as contact list can contain extra symbols
    $this->rawData = $data = file_get_contents("php://input");

    $this->data = json_decode($data,true); // JSON only works if " is used and not '
    
  }

  function outputHeaders() {
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');
  }

  function validJSON($dataStr) {

    /*
     * NOTE !
     * For some of these, we still echo Success and rely on the false flag, in order to remove the document from the device
     */

    if ($dataStr!="") {
      if (mb_detect_encoding($dataStr, 'UTF-8', true)!=="UTF-8") {

        // json only works with utF-8
        $this->sendFailure("JSON must be UTF8 - could not parse request. ".$this->postSN);

      } else if ($this->data===null) {

        $err = "";
        switch (json_last_error()) {
          case JSON_ERROR_NONE:
            $err = ' - No errors';
            break;
          case JSON_ERROR_DEPTH:
            $err = ' - Maximum stack depth exceeded';
            break;
          case JSON_ERROR_STATE_MISMATCH:
            $err = ' - Underflow or the modes mismatch';
            break;
          case JSON_ERROR_CTRL_CHAR:
            $err = ' - Unexpected control character found';
            break;
          case JSON_ERROR_SYNTAX:
            $err = ' - Syntax error, malformed JSON';
            break;
          case JSON_ERROR_UTF8:
            $err = ' - Malformed UTF-8 characters, possibly incorrectly encoded';
            break;
          default:
            $err = ' - Unknown error';
            break;
        }

        $this->sendFailure("JSON error occurred - could not parse request. {$err}".$this->postSN);

      }

    }

    return true;

  }

  function validate() {
    global $ROOT, $PHPFOLDER;

    /*
     * NOTE !
     * For some of these, we still echo Success and rely on the false flag, in order to remove the document from the device
     */

    if (!$this->validJSON($this->rawData)) return false;

    // all JSON returned no longer has the d=>array() part because the Java GSON library cant handle it
    if (!isset($this->data["logonTO"])) {
      $this->sendFailure("Missing parameters to WS Call ".$this->postSN);
    }
    if (!isset($this->data["logonTO"]["userName"])) {
      $this->sendFailure("Invalid Key - FAILURE AT BP 1");
    }

    include_once($ROOT.$PHPFOLDER.'libs/EncryptionClass.php');
    $eC = new EncryptionClass();
    $apiDAO = new ApiDAO($this->dbConn);
    $encPassword = $eC->encrypt(ENCRYPT_DB_KEY, trim($this->data["logonTO"]['password']), ENCRYPT_PWD_LENGTH);
    $mfU = $this->userResult = $apiDAO->getUserForPortalAuthentication(trim($this->data["logonTO"]['userName']), $encPassword);

    if (count($mfU)!=1) {
      $this->sendFailure("Sorry, incorrect API credentials supplied");
    }

    /*
    if ($this->data["logonTO"]["appVersion"] != $this->appVersion) {
      $this->sendFailure("Your app version is outdated. Please upgrade to latest version to continue using the app.");
    }
    */

    return true;

  }

  function processService() {

    if ($this->postSN=="validateCredentials") $result = $this->validateCredentials();
    else if ($this->postSN=="getProducts") $result = $this->getProducts();
    else if ($this->postSN=="getStoresByFilter") $result = $this->getStoresByFilter();
    else if ($this->postSN=="getProductMetaData") $result = $this->getProductMetaData();
    else if ($this->postSN=="uploadDocument") $result = $this->uploadDocument();
    else if ($this->postSN=="getTripSheet") $result = $this->getTripSheet();
    else if ($this->postSN=="sendOTP") $result = $this->sendOTP();
    else if ($this->postSN=="confirmOTP") $result = $this->confirmOTP();
    else if ($this->postSN=="uploadTripSheet") $result = $this->uploadTripSheet();
    else $result = $this->invalidSN();

    return $result;
  }

  function validateCredentials() {
    global $ROOT, $PHPFOLDER;

    // already validated in ->validate() so just return successful

    include_once($ROOT . $PHPFOLDER . 'DAO/AdministrationDAO.php');
    $adminDAO = new AdministrationDAO($this->dbConn);
    $rs = $adminDAO->getUsersPrincipals($this->userResult[0]["uid"], $this->userResult[0]["system_uid"]);
    $principalList = [];
    foreach ($rs as $r) {
      $principalList[] = ["principalId"=>$r["principal_id"], "principalName"=>$r["principal_name"], "principalCode"=>$r["principal_code"], "principalType"=>$r["principal_type"]];
    }

    $hasRoleDocumentDispatchControl = $adminDAO->hasRole($this->userResult[0]["uid"], "",ROLE_DOCUMENT_DISPATCH_CONTROL);

    $transporterList = [];
    if ($hasRoleDocumentDispatchControl) {
      $rs = $this->dbConn->dbGetAll("SELECT uid, name, address1, depot_uid FROM transporter WHERE status = '".FLAG_STATUS_ACTIVE."'");
      foreach ($rs as $r) {
        $transporterList[] = ["uid"=>$r["uid"],
                              "name"=>$r["name"],
                              "address1"=>empty($r["address1"]) ? "" : $r["address1"],
                              "depotUId"=>$r["depot_uid"]
                            ];
      }
    }
    if ($this->userResult[0]["app_metadata_json"] == "") $this->userResult[0]["app_metadata_json"] = "[]";

    // reason list
    $sql = "SELECT uid, code, description
            FROM   reason_code
            WHERE  reason_group = '002'";

    $reasonList = $this->dbConn->dbGetAll($sql);

    echo json_encode([
        "resultStatus" => "S",
        "resultMessage" => "User has access",

        "principalList" => $principalList,
        "transporterList" => $transporterList,
        "qtyChangeReasons" => $reasonList,
        "permissions" => [
                            "hasRoleDocumentDispatchControl" => $hasRoleDocumentDispatchControl
                         ],
        "appMetaDataJSON" => json_decode($this->userResult[0]["app_metadata_json"], true)

    ]);
    return;

  }

  // this method also has hitched onto it other preferences related to the principal as it is triggered on the principal selection within the app
  function getProducts() {
    global $ROOT, $PHPFOLDER;

    $principalId = $this->data["data"]["principalId"];

    include_once($ROOT . $PHPFOLDER . 'DAO/AdministrationDAO.php');
    $adminDAO = new AdministrationDAO($this->dbConn);
    $mfP = $adminDAO->getUsersPrincipals($this->userResult[0]["uid"], $this->userResult[0]["system_uid"], $principalId);
    if (count($mfP) != 1) {
      echo json_encode([
          "resultStatus" => "E",
          "resultMessage" => "Invalid Principal Id passed or you do not have access",
      ]);
      exit;
    }

    include_once($ROOT . $PHPFOLDER . 'DAO/ProductDAO.php');
    $productDAO = new ProductDAO($this->dbConn);

    $hasRoleCapture = $adminDAO->hasRole($this->userResult[0]["uid"], $principalId, ROLE_ORDER_CAPTURE);
    $hasRoleModifyDD = $adminDAO->hasRole($this->userResult[0]["uid"], $principalId,ROLE_OC_CAN_MODIFY_DELDATE);
    $hasRolePriceOverride = $adminDAO->hasRole($this->userResult[0]["uid"], $principalId,ROLE_ALLOW_PRICE_OVERRIDE);
    $hasRoleViewPrice = $adminDAO->hasRole($this->userResult[0]["uid"], $principalId,ROLE_VIEW_PRICE);

    $rs = $productDAO->getUserPrincipalProductsArray($principalId, $this->userResult[0]["uid"]);
    $productList = [];
    foreach ($rs as $r) {
      $productList[] = ["uid"=>$r["uid"], "productCode"=>$r["product_code"], "productDescription"=>$r["product_description"]];
    }

    echo json_encode([
        "resultStatus" => "S",
        "resultMessage" => "Successful",

        "productList" => $productList,
        "permissions" => [
                          "hasRoleCapture"=>$hasRoleCapture,
                          "hasRoleModifyDelDate"=>$hasRoleModifyDD,
                          "hasRolePriceOverride"=>$hasRolePriceOverride,
                          "hasRoleViewPrice"=>$hasRoleViewPrice,
                        ]

    ]);
    return;

  }

  function getTripSheet() {
    global $ROOT, $PHPFOLDER;

    $tripSheetNoTemp = $this->data["data"]["tripSheetNo"];
    $parts = explode("-", $tripSheetNoTemp);
    $depotUId = trim($parts[0]);
    $tripSheetNo = (isset($parts[1])) ? trim($parts[1]) : "";

    if (!preg_match(Constants::GUI_PHP_INTEGER_REGEX, $depotUId) || !preg_match(Constants::GUI_PHP_INTEGER_REGEX, $tripSheetNo)) {
      echo json_encode([
        "resultStatus" => "E",
        "resultMessage" => "Invalid or missing parameters passed in getTripSheet() WS Call. Please make sure the TripSheet No is in format of XXX-XXXXX all digits",
      ]);
      exit;
    }

    include_once($ROOT . $PHPFOLDER . 'DAO/AdministrationDAO.php');
    $adminDAO = new AdministrationDAO($this->dbConn);

    $hasRoleDocumentDispatchControl = $adminDAO->hasRole($this->userResult[0]["uid"], "",ROLE_DOCUMENT_DISPATCH_CONTROL);
    if (!$hasRoleDocumentDispatchControl) {
      echo json_encode([
        "resultStatus" => "E",
        "resultMessage" => "Sorry, you do not have access to query tripsheets",
      ]);
      exit;
    }

    $mfP = $adminDAO->getUsersPrincipals($this->userResult[0]["uid"], $this->userResult[0]["system_uid"], false);
    $validPrincipalUIds = array_column($mfP, "principal_id");

    $sql = "SELECT th.uid, th.depot_uid, th.tripsheet_date, dh.trip_transporter_uid transporter_uid, t.name transporter_name, t.address1 transporter_address1,
                 td.document_master_uid, dm.document_number, dm.principal_uid,
                 dh.document_status_uid, t.vehicle_reg,
                 dd.product_uid, dd.ordered_qty, dd.document_qty, pp.product_code, pp.product_description, 
                 -- use the barcodes from this table, and NOT the main product table
					       ppdg.outercasing_gtin, ppdg.sku_gtin
       
            FROM   tripsheet_header th
                  INNER JOIN tripsheet_detail td ON td.tripsheet_master_uid = th.uid AND IFNULL(td.removed_flag,'N') != 'Y'
                    LEFT JOIN document_master dm ON dm.uid = td.document_master_uid
                      LEFT JOIN document_header dh ON dh.document_master_uid = dm.uid
                        LEFT JOIN transporter t ON t.uid = dh.trip_transporter_uid
                        LEFT JOIN document_detail dd ON dd.document_master_uid = dm.uid AND dd.document_qty > 0
                          LEFT JOIN principal_product pp ON pp.uid = dd.product_uid
                          LEFT JOIN principal_product_depot_gtin ppdg ON ppdg.principal_product_uid = dd.product_uid
                      INNER JOIN document_tripsheet dt ON dt.document_master_uid = dm.uid AND dt.tripsheet_number = th.tripsheet_number AND dt.i_dispatched = 'N' AND dt.t_dispatched = 'N'

            WHERE  th.tripsheet_number = '{$tripSheetNo}'	
            AND    th.depot_uid = {$depotUId}";

    $tRS = $this->dbConn->dbGetAll($sql);
    $documents = ["header"=>[], "eligible"=>[], "ineligible"=>[]];
    foreach ($tRS as $r) {

      if (empty($r["outercasing_gtin"]) && empty($r["sku_gtin"])) {
        echo json_encode([
          "resultStatus" => "E",
          "resultMessage" => "Some products ({$r["product_uid"]}) on this trip sheet are unreachable / unscannable due to missing sku/outercasing barcodes. Cannot continue.",
        ]);
        exit;
      }

      if (!in_array($r["principal_uid"], $validPrincipalUIds)) {
        echo json_encode([
          "resultStatus" => "E",
          "resultMessage" => "Sorry, you do not have access to atleast one of the principals involved in this tripsheet",
        ]);
        exit;
      }

      $documents["header"] = [
                              "tripSheetDate" => $r["tripsheet_date"],
                              "transporterUId" => $r["transporter_uid"],
                              "transporterName" => $r["transporter_name"],
                              "transporterAddress1" => empty($r["transporter_address1"]) ? "" : $r["transporter_address1"],
                              "vehicleReg" => $r["vehicle_reg"],
                            ];
      // $categ = ($r["document_status_uid"] == DST_INVOICED) ? "eligible" : "ineligible"; // we no longer do this as SQL changed to filter out ineligible
      $categ = "eligible";
      $documents[$categ][$r["document_master_uid"]][] = [
                                                          "documentMasterUId" => $r["document_master_uid"],
                                                          "documentNumber" => $r["document_number"],
                                                          "principalUId" => $r["principal_uid"],
                                                          "productUId" => $r["product_uid"],
                                                          "orderedQty" => $r["document_qty"],
                                                          "productCode" => $r["product_code"],
                                                          "productDescription" => $r["product_description"],
                                                          "outercasingGTIN" => $r["outercasing_gtin"],
                                                          "skuGTIN" => $r["sku_gtin"],
                                                        ];

    }
    // make sure JSON returned is not a {} class but rather a [] because the umuids are big numbers not in seq
    // ~ 05.01.2022 - we now rather just change SQL to exclude
    $documents["eligible"] = array_values($documents["eligible"]);
    $documents["ineligible"] = array_values($documents["ineligible"]);

    echo json_encode([
      "resultStatus" => (count($tRS) > 0) ? "S" : "E",
      "resultMessage" => (count($tRS) > 0) ? "Successful" : "No Trip Sheet data found",

      "documents" => $documents,
    ]);
    return;

  }

  function sendOTP() {
/*
    echo json_encode([
      "resultStatus" => "S",
      "resultMessage" => "XXX",
    ]);
    exit;
*/
    global $ROOT, $PHPFOLDER;
    include_once ($ROOT.$PHPFOLDER . "functional/maintenance/BulkSMS_v2.php");

    $mobileNo = $this->userResult[0]["user_cell"];
    $otp = rand(1000,9999);

    $rTO = BulkSMS_v2::sendSMS($mobileNo, "Your OTP for the Kwelanga administration of Dispatch Documents is : " . $otp, $this->userResult[0]["uid"]);

    if ($rTO->type == FLAG_ERRORTO_SUCCESS) {
      $sql = "INSERT INTO user_otp (user_uid, otp_value, sent_datetime, method)
              VALUES ({$this->userResult[0]["uid"]}, '{$otp}', NOW(), 'SMS')";
      $pTO = $this->dbConn->processPosting($sql, "");
      $this->dbConn->dbQuery("commit");

      echo json_encode([
        "resultStatus" => $pTO->type,
        "resultMessage" => ($pTO->type == FLAG_ERRORTO_SUCCESS) ? "Successful" : "Failed to generate OTP with log entry : " .$pTO->description,

        "otp" => $otp
      ]);
      exit;

    }

    echo json_encode([
      "resultStatus" => $rTO->type,
      "resultMessage" => $rTO->description,
    ]);
    exit;

  }

  function confirmOTP() {
    global $ROOT, $PHPFOLDER;
/*
    echo json_encode([
      "resultStatus" => "S",
      "resultMessage" => "XXX",
    ]);
    exit;
*/
    $otp = $this->data["data"]["otp"];

    $valid = false;
    $elapsedTime = date(Constants::GUI_PHP_DATETIME_FORMAT, time() - 60);

    $sql = "SELECT otp_value
            FROM   user_otp 
            WHERE  user_uid = {$this->userResult[0]["uid"]}
            AND    sent_datetime >= '{$elapsedTime}'
            AND    `method` = 'SMS'
            ORDER  BY sent_datetime DESC";

    $rs = $this->dbConn->dbGetAll($sql);

    if (($rs[0]["otp_value"]??"X") == $otp) $valid = true;

    echo json_encode([
      "resultStatus" => ($valid) ? "S" : "E",
      "resultMessage" => ($valid) ? "Passed" : "Failed",
    ]);
    exit;

  }

  function getStoresByFilter() {
    global $ROOT, $PHPFOLDER;

    $principalId = $this->data["data"]["principalId"];
    $filter = $this->data["data"]["filter"];

    // validate principal
    include_once($ROOT . $PHPFOLDER . 'DAO/AdministrationDAO.php');
    $adminDAO = new AdministrationDAO($this->dbConn);
    $mfP = $adminDAO->getUsersPrincipals($this->userResult[0]["uid"], $this->userResult[0]["system_uid"], $principalId);
    if (count($mfP) != 1) {
      echo json_encode([
          "resultStatus" => "E",
          "resultMessage" => "Invalid Principal Id passed or you do not have access",
      ]);
      exit;
    }

    include_once($ROOT . $PHPFOLDER . 'DAO/StoreDAO.php');
    $storeDAO = new StoreDAO($this->dbConn);
    $rs = $storeDAO->getUserSearchPrincipalStoreArray($this->userResult[0]["uid"], $principalId, [strtolower($filter)], $showVendorStores=true);
    $storeList = [];
    foreach ($rs as $r) {

      if (($r["has_store_permission"]!='1') || ($r["status"]!=FLAG_STATUS_ACTIVE)) continue;

      $storeList[] = ["uid"=>$r["psm_uid"],
                      "storeName"=>$r["store_name"],
                      "deliverAdd1"=>$r["deliver_add1"],
                      "depotUId"=>$r["depot_uid"],
                      "depotName"=>$r["depot_name"],
                      "deliveryDay"=>$r["delivery_day"]];
    }

    echo json_encode([
        "resultStatus" => "S",
        "resultMessage" => "Successful",

        "storeList" => $storeList,

    ]);
    return;

  }

  /*
   * Price
   * Stock
   */
  function getProductMetaData() {
    global $ROOT, $PHPFOLDER;

    $principalId = $this->data["data"]["principalId"];
    $storeUId = $this->data["data"]["storeUId"];
    $depotUId = $this->data["data"]["depotUId"];
    $productUId = $this->data["data"]["productUId"];

    $priceErrorMessage = $price = $stockErrorMessage = $stock = "";

    include_once($ROOT . $PHPFOLDER . 'DAO/AdministrationDAO.php');
    $adminDAO = new AdministrationDAO($this->dbConn);
    $mfP = $adminDAO->getUsersPrincipals($this->userResult[0]["uid"], $this->userResult[0]["system_uid"], $principalId);
    if (count($mfP) != 1) {
      echo json_encode([
          "resultStatus" => "E",
          "resultMessage" => "Invalid Principal Id passed or you do not have access",
      ]);
      exit;
    }

    /* ********** allow to continue past this point now, but check the error messages ********** */

    $hasRoleViewPrice = $adminDAO->hasRole($this->userResult[0]["uid"], $principalId,ROLE_VIEW_PRICE);
    if (!$hasRoleViewPrice) {
      $priceErrorMessage = "Access Denied for Viewing Pricing";
    }

    if (empty($priceErrorMessage)) {

      include_once($ROOT . $PHPFOLDER . 'DAO/ProductDAO.php');
      $productDAO = new ProductDAO($this->dbConn);

      $mfP = $productDAO->getActivePricesForProduct($principalId, $storeUId, $productUId);
      if (count($mfP) == 0) {
        $priceErrorMessage = "No Pricing";
      }
      else {

        /*if ($postDOCTYPE === DT_MCREDIT_OTHER){
          $returnMessages->identifier=round(($mfP[0]['price'] / $mfP[0]['items_per_case']),2);
        } else {*/
        $price = ($mfP[0]['price']);
        //}

      }

    }


    // STOCK

    if (!$adminDAO->hasRole($this->userResult[0]["uid"], $principalId, ROLE_BYPASS_USER_PRODUCT_RESTRICTION)){ //bypass role
      if(!$adminDAO->hasProduct($this->userResult[0]["uid"], $productUId, $principalId)){ //allocated to user
        $stockErrorMessage = "No Product Permissions";
      }
    }

    if (empty($stockErrorMessage)) {

      if (!$adminDAO->hasDepot($this->userResult[0]["uid"], $depotUId, $principalId)) {
        $stockErrorMessage = "No Depot Permissions";
      }

      if (empty($stockErrorMessage)) {

        include_once($ROOT . $PHPFOLDER . 'DAO/StockDAO.php');
        $stockDAO = new StockDAO($this->dbConn);
        $mfS = $stockDAO->getUserPrincipalProductStock($this->userResult[0]["uid"], $principalId, $productUId, $depotUId);
        $stock = ( (isset($mfS[0])) ? $mfS[0]['available'] : 0 );

      }

    }

    echo json_encode([
        "resultStatus" => "S",
        "resultMessage" => "Successful",

        "price" => ( (empty($priceErrorMessage)) ? $price : $priceErrorMessage ),
        "stock" => ( (empty($stockErrorMessage)) ? $stock : $stockErrorMessage ),

    ]);
    return;

  }

  function uploadDocument() {
    global $ROOT, $PHPFOLDER;

    $principalId = $this->data["data"]["principalId"];

    file_put_contents(DIR_DATA_FTP_FROM."kwelangaPWA/orders-kwelangaPWA.".(date("YmdHis")).".json", "\r\n".json_encode($this->data));

    echo json_encode([
        "resultStatus" => "S",
        "resultMessage" => "Successful",
    ]);
    return;

  }

  function uploadTripSheet() {
    global $ROOT, $PHPFOLDER;

    $this->data["uploadedDateTime"] = date("Y-m-d H:i:s");
    $TO = $this->data["data"];

    file_put_contents(DIR_DATA_FTP_FROM."kwelangaPWA/documentDispatchControl-kwelangaPWA.".(date("YmdHis")).".json", "\r\n".json_encode($this->data));

    /*
     TO = {

      createdDateTime : common.getCurrentDateTime(),
      deviceTransactionSeq : (new Date()).getTime(),
      tripSheetNo : $('#f_TRIPSHEETNO').val(),
      transporterUId : $('#TRANSPORTERUID').attr('transporterUId'), // only updated if not empty
      vehicleReg : $('#f_VEHICLEREG').val(), // only updated if not empty
      invoices : documentDispatchControlView.newCapturedInvoices,
      otp : false,
      metaData : JSON.stringify(documentDispatchControlView.metaDataRS), // sent as text string JSON, not JSON

      coords : app.getLocation()

    }
     */

    // 1. First validate OTP if necessary
    // *************************************************************************

    $requiresOTP = false;
    foreach ($TO["invoices"] as $r) {
      if (count($r["otpConditions"]) > 0) {
        $requiresOTP = true;
        break;
      }
    }

    if ($requiresOTP) {

      $valid = false;
      $elapsedTime = date(Constants::GUI_PHP_DATETIME_FORMAT, time() - 60);
      $sql = "SELECT otp_value
              FROM   user_otp 
              WHERE  user_uid = {$this->userResult[0]["uid"]}
              AND    sent_datetime >= '{$elapsedTime}'
              AND    `method` = 'SMS'
              ORDER  BY sent_datetime DESC";

      $rs = $this->dbConn->dbGetAll($sql);

      if (($rs[0]["otp_value"] ?? "X") == $TO["otp"]) $valid = true;
      $valid = true;
      if (!$valid) {
        echo json_encode([
          "resultStatus" => "E",
          "resultMessage" => "Document could not be saved as the OTP is incorrect",
        ]);
        return;
      }

    }


    // 2. Validate access
    // *************************************************************************

    $uniqueDMUIds = $uniqueProductUIds = [];
    $updateRows = [];
    $countOfDetailLines = 0;
    foreach ($TO["invoices"] as $r) {
      $uniqueDMUIds[$r["documentMasterUId"]] = $r["documentMasterUId"];
      $countOfDetailLines += count($r["products"]);

      if (!preg_match(Constants::GUI_PHP_INTEGER_REGEX, $r["documentMasterUId"])) {
        echo json_encode([
          "resultStatus" => "E",
          "resultMessage" => "Invalid or missing document master UIds",
        ]);
        exit;
      }

      foreach ($r["products"] as $detail) {

        $uniqueProductUIds[$detail["productUId"]] = $detail["productUId"];

        if (!preg_match(Constants::GUI_PHP_INTEGER_REGEX, $detail["productUId"])) {
          echo json_encode([
            "resultStatus" => "E",
            "resultMessage" => "Invalid or missing Product UIds",
          ]);
          exit;
        }

        if (!preg_match(Constants::GUI_PHP_INTEGER_REGEX, $detail["qty"])) {
          echo json_encode([
            "resultStatus" => "E",
            "resultMessage" => "Invalid or missing Document Quantity",
          ]);
          exit;
        }

        if (
            (intval($detail["qty"]) < 0) ||
            (intval($detail["qty"]) > $detail["orderedQty"])
        ) {
          echo json_encode([
            "resultStatus" => "E",
            "resultMessage" => "Quantity out of bounds - ".$detail["qty"],
          ]);
          exit;
        }

        // other fields unused are : selectedValue, description
        $qtyReasonChangeUId = ($detail["qtyChangeReason"]["uid"]) ?? "";
        $qtyReasonChangeCode = ($detail["qtyChangeReason"]["code"]) ?? "";

        if (!empty($qtyReasonChangeUId)) {
          if (!preg_match(Constants::GUI_PHP_INTEGER_REGEX, $qtyReasonChangeUId)) {
            echo json_encode([
                "resultStatus" => "E",
                "resultMessage" => "Invalid or missing Qty Reason Change UId(s) for {$detail["productUId"]} - {$detail["productDescription"]}",
            ]);
            exit;
          }
        }

        $updateRows[] = " SELECT {$r["documentMasterUId"]} document_master_uid, 
                                 {$detail["productUId"]} product_uid,
                                 {$detail["orderedQty"]} ordered_qty,
                                 {$detail["qty"]} amended_qty,
                                 " . ((empty($qtyReasonChangeUId)) ? "NULL" : $qtyReasonChangeUId ) . " qty_reason_change_uid";

      }

    }

    $tripSheetNoTemp = $TO["tripSheetNo"];
    $parts = explode("-", $tripSheetNoTemp);
    $depotUId = trim($parts[0]);
    $tripSheetNo = (isset($parts[1])) ? trim($parts[1]) : "";

    if (!preg_match(Constants::GUI_PHP_INTEGER_REGEX, $depotUId) || !preg_match(Constants::GUI_PHP_INTEGER_REGEX, $tripSheetNo)) {
      echo json_encode([
        "resultStatus" => "E",
        "resultMessage" => "Invalid or missing parameters passed in getTripSheet() WS Call. Please make sure the TripSheet No is in format of XXX-XXXXX all digits",
      ]);
      exit;
    }

    include_once($ROOT . $PHPFOLDER . 'DAO/AdministrationDAO.php');
    $adminDAO = new AdministrationDAO($this->dbConn);

    $hasRoleDocumentDispatchControl = $adminDAO->hasRole($this->userResult[0]["uid"], "",ROLE_DOCUMENT_DISPATCH_CONTROL);
    if (!$hasRoleDocumentDispatchControl) {
      echo json_encode([
        "resultStatus" => "E",
        "resultMessage" => "Sorry, you do not have access to query tripsheets",
      ]);
      exit;
    }

    $mfP = $adminDAO->getUsersPrincipals($this->userResult[0]["uid"], $this->userResult[0]["system_uid"], false);
    $validPrincipalUIds = array_column($mfP, "principal_id");

    $sql = "SELECT th.uid, th.depot_uid, th.tripsheet_date, dh.trip_transporter_uid transporter_uid, t.name transporter_name, t.address1 transporter_address1,
                 td.document_master_uid, dm.document_number, dm.principal_uid,
                 dh.document_status_uid, t.vehicle_reg,
                 dd.product_uid, dd.ordered_qty, dd.document_qty, pp.product_code, pp.product_description, 
                 -- use the barcodes from this table, and NOT the main product table
					       ppdg.outercasing_gtin, ppdg.sku_gtin,
                 mods.ordered_qty mods_ordered_qty, mods.amended_qty mods_amended_qty
            FROM   tripsheet_header th
                      INNER JOIN tripsheet_detail td ON td.tripsheet_master_uid = th.uid AND IFNULL(td.removed_flag,'N') != 'Y'
                        LEFT JOIN document_master dm ON dm.uid = td.document_master_uid
                          LEFT JOIN document_header dh ON dh.document_master_uid = dm.uid
                            LEFT JOIN transporter t ON t.uid = dh.trip_transporter_uid
                            LEFT JOIN document_detail dd ON dd.document_master_uid = dm.uid AND dd.document_qty > 0
                              LEFT JOIN principal_product pp ON pp.uid = dd.product_uid
                              LEFT JOIN principal_product_depot_gtin ppdg ON ppdg.principal_product_uid = dd.product_uid
                              LEFT JOIN (
                                  " . implode(" UNION ALL ", $updateRows) . "
                              ) mods ON mods.product_uid = dd.product_uid AND mods.document_master_uid = dd.document_master_uid
                          INNER JOIN document_tripsheet dt ON dt.document_master_uid = dm.uid AND dt.tripsheet_number = th.tripsheet_number AND dt.i_dispatched = 'N' AND dt.t_dispatched = 'N'
            
            WHERE  th.tripsheet_number = '{$tripSheetNo}'	
            AND    th.depot_uid = {$depotUId}
            AND    dh.document_status_uid in (".DST_INVOICED.",". DST_WAITING_DISPATCH .",". DST_RE_DELIVERY ." )";

    $tRS = $this->dbConn->dbGetAll($sql);
    foreach ($tRS as $r) {

      if (!in_array($r["principal_uid"], $validPrincipalUIds)) {
        echo json_encode([
          "resultStatus" => "E",
          "resultMessage" => "Sorry, you do not have access to atleast one of the principals involved in this tripsheet",
        ]);
        exit;
      }

      if (!in_array($r["document_master_uid"], $uniqueDMUIds)) {
        echo json_encode([
          "resultStatus" => "E",
          "resultMessage" => "Sorry, you do not have access to at least one of the Documents ({$r["document_master_uid"]}) involved in this tripsheet",
        ]);
        exit;
      }

      if (!in_array($r["product_uid"], $uniqueProductUIds)) {
        echo json_encode([
          "resultStatus" => "E",
          "resultMessage" => "Sorry, you do not have access to at least one of the products ({$r["product_uid"]}) involved in this tripsheet",
        ]);
        exit;
      }

      if (
        (intval($r["document_qty"]) != intval($r["mods_amended_qty"])) &&
        ($requiresOTP === false)
      ) {
        echo json_encode([
          "resultStatus" => "E",
          "resultMessage" => "OTP requirements not resolved securely ! Cannot continue.",
        ]);
        exit;
      }

      // dont use empty() as it will include zero
      if (!preg_match(Constants::GUI_PHP_INTEGER_REGEX, $r["mods_amended_qty"])) {
        echo json_encode([
          "resultStatus" => "E",
          "resultMessage" => "RS submitted from app doesnt match up with server, missing amended qty.",
        ]);
        exit;
      }

    }
    if ($countOfDetailLines != count($tRS)) {
      echo json_encode([
        "resultStatus" => "E",
        "resultMessage" => "Sorry, the underlying recordset submitted from the app does not match the server",
      ]);
      exit;
    }

    // app ensures transporter should be inputted when amend process taken place
    if (!preg_match(Constants::GUI_PHP_INTEGER_REGEX, $TO["transporterUId"])) {
      echo json_encode([
          "resultStatus" => "E",
          "resultMessage" => "Transporter UId is invalid.",
      ]);
      exit;
    }

    $sql = "UPDATE document_header dh
            INNER JOIN document_master dm on dm.uid = dh.document_master_uid
            INNER JOIN depot d on d.uid = dm.depot_uid                                                 
            INNER JOIN transporter t ON t.uid = {$TO["transporterUId"]}
            INNER JOIN document_tripsheet dt ON dt.document_master_uid = dh.document_master_uid -- AND (dt.tripsheet_number = dh.tripsheet_number OR dh.tripsheet_number IS NULL)
            LEFT JOIN tripsheet_detail td ON td.document_master_uid = dh.document_master_uid
            LEFT JOIN tripsheet_header th ON th.uid = td.tripsheet_master_uid
            SET    dh.invoice_date  = '".(date("Y-m-d"))."',
                   dh.trip_transporter_uid = t.uid,
                   dh.truck_registration = '".mysqli_real_escape_string($this->dbConn->connection, $TO["vehicleReg"])."',
                   dh.transporter_name = t.name,
                   dt.i_dispatched = 'Y',
                   dt.t_dispatched = 'Y',
                   td.i_dispatched = 'Y',
                   td.t_dispatched = 'Y',
                   th.t_dispatched = 'Y',
                   dh.buyer_document_status_uid = document_status_uid,
                   dh.document_status_uid = " . DST_INVOICED . ",
                   dh.buyer_document_status_uid = " . DST_WAITING_DISPATCH ."
            WHERE  dh.document_master_uid IN (".implode(',', $uniqueDMUIds).") 
            ";
           
            
            
    $rTO = $this->dbConn->processPosting($sql, "");
    if ($rTO->type != FLAG_ERRORTO_SUCCESS) {
      echo json_encode([
        "resultStatus" => "E",
        "resultMessage" => "Failed to update header details : " . $rTO->description,
      ]);
      exit;
    }

    // update quantities and status
    $sql = "UPDATE document_header dh
            INNER JOIN document_master dm ON dm.uid = dh.document_master_uid
            INNER JOIN depot d on d.uid = dm.depot_uid
            INNER JOIN document_detail dd ON dd.document_master_uid = dh.document_master_uid
            INNER JOIN ( " . implode(" UNION ALL ", $updateRows) . ") mods ON mods.product_uid = dd.product_uid AND mods.document_master_uid = dd.document_master_uid
            LEFT  JOIN stock s  ON s.principal_id = dm.principal_uid
                                AND s.depot_id    = dm.depot_uid
                                AND s.principal_product_uid = dd.product_uid
            LEFT  JOIN stock sr ON sr.principal_id = dm.principal_uid
                                AND sr.depot_id    = d.redelivery_warehouse
                                AND sr.principal_product_uid = dd.product_uid
            SET    dd.waiting_delivery_qty = dd.document_qty,
                   dd.document_qty = if(dh.buyer_document_status_uid  = " . DST_WAITING_DISPATCH . ",mods.amended_qty, dd.document_qty),
                   dd.delivered_qty = if(dh.buyer_document_status_uid = " . DST_WAITING_DISPATCH . ",mods.amended_qty, dd.document_qty), 
                   dd.scanned_qty  = if(dh.buyer_document_status_uid  = " . DST_WAITING_DISPATCH . ",mods.amended_qty, 0),
                   dm.app_json_response = '".mysqli_real_escape_string($this->dbConn->connection, json_encode($this->data))."',
                   dm.app_metadata_json_response = '".mysqli_real_escape_string($this->dbConn->connection, $TO["metaData"])."',
                   dd.reason_uid = mods.qty_reason_change_uid,
                   s.delivered = s.delivered - if(dh.buyer_document_status_uid = " . DST_WAITING_DISPATCH . ",mods.amended_qty,0),
                   s.closing   = s.closing   - if(dh.buyer_document_status_uid = " . DST_WAITING_DISPATCH . ",mods.amended_qty,0),
                   s.available = s.available - if(dh.buyer_document_status_uid = " . DST_WAITING_DISPATCH . ",mods.amended_qty,0),

                   sr.delivered = sr.delivered - if(dh.buyer_document_status_uid = " . DST_RE_DELIVERY . ", mods.amended_qty,0),
                   sr.closing   = sr.closing   - if(dh.buyer_document_status_uid = " . DST_RE_DELIVERY . ", mods.amended_qty,0),
                   sr.available = sr.available - if(dh.buyer_document_status_uid = " . DST_RE_DELIVERY . ", mods.amended_qty,0)                  
            WHERE  dh.document_master_uid IN (".implode(',', $uniqueDMUIds).")";

// file_put_contents($ROOT . $PHPFOLDER . 'log/alan16.txt', date('Y-m-d H:i:s') . '/n'.  $sql, FILE_APPEND);         

    $rTO = $this->dbConn->processPosting($sql, "");
    if ($rTO->type != FLAG_ERRORTO_SUCCESS) {

      file_put_contents("mark_qty.txt", date("Y-m-d H:i:s") . " : {$sql}\r\nFailed to update quantities on server : " . $rTO->description, FILE_APPEND);

      echo json_encode([
        "resultStatus" => "E",
        "resultMessage" => "Failed to update quantities on server : " . $rTO->description,
      ]);
      exit;
    }
    
    // Re Calculate Server Quantities
    
    foreach($uniqueDMUIds as $dmRow) {
    	
    	    $sql = "SELECT dd.uid as 'ddUid', dd.document_qty, dd.waiting_delivery_qty
    	            FROM document_detail dd
    	            WHERE dd.document_master_uid = " . $dmRow;
    	            
    	    $ddRow = $this->dbConn->dbGetAll($sql);
    	    
    	    foreach($ddRow as $dddRow) {
    	    	      if($dddRow['dd.document_qty'] != $dddRow['waiting_delivery_qty']) {
    	    	      	
    	    	      	      $sql = "UPDATE document_detail dd SET dd.extended_price = dd.document_qty * dd.net_price,
                                         dd.vat_amount     = dd.document_qty * dd.net_price  * (dd.vat_rate /100),
                                         dd.total          = (dd.document_qty * dd.net_price) + (dd.document_qty * dd.net_price * (dd.vat_rate /100))
                                  WHERE dd.uid = " .$dddRow['ddUid'];   
    
                          $rTO = $this->dbConn->processPosting($sql, "");
                          
                          $sql = "UPDATE document_header dh SET dh.cases =  (SELECT sum(dd.document_qty)
                                                                             FROM  document_detail dd
                                                                             WHERE dd.uid = " .$dddRow['ddUid'] . "),
                                                                dh.exclusive_total =  (SELECT sum(dd.extended_price)
                                                                                       FROM document_detail dd
                                                                                       WHERE dd.uid = " . $dddRow['ddUid'] . "),
                                                                dh.vat_total       =  (SELECT sum(dd.vat_amount)
                                                                                       FROM document_detail dd
                                                                                       WHERE dd.uid = " . $dddRow['ddUid'] . "),
                                                                dh.invoice_total   =  (SELECT sum(dd.total)
                                                                                       FROM document_detail dd
                                                                                       WHERE dd.uid = " . $dddRow['ddUid'] . "),
                                  WHERE dh.document_master_uid = " . $dddRow['ddUid'] . " ;" ;
                            
                          $rTO = $this->dbConn->processPosting($sql, "");         
   	    	      }
    	    } 
    }
    $auditRows = [];
    foreach ($uniqueDMUIds as $r) {
      $auditRows[] = " ( {$r}, ".DST_INVOICED.", NOW(), {$this->userResult[0]["uid"]}, 'PWA submission via Document Dispatch Control capture facility', 'APP' ) ";
    }
    $sql = "INSERT INTO document_depot_audit_log (document_master_uid, document_status_uid, activity_date, changed_by, comment, `type`) 
            VALUES " . implode(",", $auditRows);
    $rTO = $this->dbConn->processPosting($sql, "");
    if ($rTO->type != FLAG_ERRORTO_SUCCESS) {
      echo json_encode([
        "resultStatus" => "E",
        "resultMessage" => "Failed to update quantities on server : " . $rTO->description,
      ]);
      exit;
    }

    $this->dbConn->dbQuery("commit");

    echo json_encode([
      "resultStatus" => "S",
      "resultMessage" => "Successful",
    ]);
    return;

  }

  function invalidSN() {

    $this->sendFailure("Unknown WS call sn");

  }

  function sendFailure($returnMessage) {

    echo json_encode([
          "resultStatus" => "E",
          "resultMessage" => $returnMessage,
          "identifier" => ""
    ]);

    exit;

  }

}
  
?>
