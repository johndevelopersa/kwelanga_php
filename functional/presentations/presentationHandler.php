<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER."DAO/MiscellaneousDAO.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER."TO/ErrorTO.php");
include_once($ROOT.$PHPFOLDER."TO/PresentationTO.php");

$dbConn = new dbConnect();
$dbConn->dbConnection();

$presentationHandler = new PresentationHandler();
if(isset($_GET['TYPE'])){$presentationHandler->type = $_GET['TYPE'];}
if(isset($_GET['ADDITIONALTYPE'])){$presentationHandler->additionalType = $_GET['ADDITIONALTYPE'];}
if(isset($_GET['CATEGORIES'])){$presentationHandler->categories = urldecode($_GET['CATEGORIES']);}
$presentationHandler->mobile = (isset($_GET["MOBILE"]) && $_GET["MOBILE"] == 1) ? "MOBILE" : "WEB";
$presentationHandler->init();



// PRESENTATION / VIEW / PRINTING INIT LAYER
class PresentationHandler{

  public  $mobile = 'WEB',
          $type = 'error',
          $additionalType = '';

  public function init(){
    $method = strtolower($this->type).'Init';
    if(!method_exists($this, $method)){
      echo 'ERROR: method ' . $method .' does not exist!';
      return;
    } else {
      $this->{$method}();
    }
  }

  private function errorInit(){
    echo "ERROR: Invalid presentation type!";
    return;
  }


  //this handles session values for this presentation type for the which presentation to use.
  private function stockcountInit(){


    global $ROOT, $PHPFOLDER;

    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER.'DAO/StockDAO.php');

    if (!isset($_SESSION)) session_start();
    $userId = $_SESSION['user_id'];
    $userCategory = $_SESSION['user_category'];
    $principalId = $_SESSION['principal_id'];
    $principalName = $_SESSION['principal_name'];
    $systemId = $_SESSION['system_id'];
    $depotId = $_SESSION['depot_id'];

    $dbConn = new dbConnect();
    $dbConn->dbConnection();
    
    $catList = '';
    
    if(count(json_decode($this->categories, true)) > 0) {
        foreach(json_decode($this->categories, true) as $row) {
        	  if(trim($catList) <> '') {$catList = $catList . ",";}
        	  $catList = $catList . $row['uid'];
        }
    }
    $stockDAO = new StockDAO($dbConn);
    $stockArr = $stockDAO->getStockCountProducts($depotId, $principalId, $catList);

    // print_r($stockArr);



    echo $_GET['FILTERPID'];
    if(isset($_GET['FILTERPID']) && count($filterId = explode(',',$_GET['FILTERPID']))>0 && $_GET['FILTERPID'] <> 'false'){
    // echo 'test';
      $filterStockArr = array();
     // var_dump($filterId);
      foreach($stockArr as $s){
        if(in_array($s['product_uid'], $filterId)){
          $filterStockArr[] = $s;
        }
      }
      $stockArr = $filterStockArr;
    }

    //Which template do we use???
    $p = new PresentationTO();
    $p->type = strtoupper($this->type);
    $p->systemUId = $systemId; //document -> principal -> system
    $p->principalUid = $principalId;
    $p->depotUId = $depotId;
    $p->documentTypeUId = '';
    $p->documentStatusUId = '';
    $p->platform = $this->mobile;
    $p->userCategory = $userCategory;

    $miscDAO = new MiscellaneousDAO($dbConn);
    $prArr = $miscDAO->getPresentation($p);
    
    // print_r($prArr);
    


    if(count($prArr)==0){
      echo "ERRROR: No presentation layer found!";
      return;
    }

    $stationaryScript = $prArr['stationary_script'];
   
    //render
    $pathTemplate = $ROOT. $PHPFOLDER . 'functional/presentations/view/'.$prArr['display_template_script'];
    if(is_file($pathTemplate))
      include($pathTemplate);

    $pathPrint = $ROOT. $PHPFOLDER . 'functional/presentations/print/'.$prArr['printing_control_script'];
    if(is_file($pathPrint))
      include($pathPrint);

  }
  
  //this handles session values for this presentation type for the which presentation to use.
  private function documentInit(){


    global $ROOT, $PHPFOLDER;

    if (
        ((!isset($_GET["KEYFROMLINK"])) && (!isset($_POST["KEYFROMLINK"]))) ||
        ((isset($_GET["KEYFROMLINK"])) && ($_GET["KEYFROMLINK"]=="")) ||
        ((isset($_POST["KEYFROMLINK"])) && ($_POST["KEYFROMLINK"]==""))
       ) {
       require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    } else {
      include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php"); // needed because of access_control commented out
      include_once($ROOT.$PHPFOLDER.'properties/dbSettings.inc');
    }
    include_once($ROOT.$PHPFOLDER."DAO/TransactionDAO.php");
    include_once($ROOT.$PHPFOLDER."DAO/PrincipalDAO.php");
    include_once($ROOT.$PHPFOLDER."DAO/AdministrationDAO.php");
    include_once($ROOT.$PHPFOLDER."DAO/CommonDAO.php");
    include_once($ROOT.$PHPFOLDER."libs/GUICommonUtils.php");
    include_once($ROOT.$PHPFOLDER."properties/Constants.php");


    $postDOCMASTID="";  //preset.
    if (isset($_GET['DOCMASTID'])) $postDOCMASTID = ($_GET['DOCMASTID']);
    else if (isset($_POST['DOCMASTID'])) $postDOCMASTID = ($_POST['DOCMASTID']);
    $postORDERSEQ = (isset($_GET['ORDERSEQ'])) ? $_GET['ORDERSEQ'] : false ;
    $postKEYFROMLINK = (isset($_GET["KEYFROMLINK"])) ? $_GET["KEYFROMLINK"] : false; // the user came to this page from an email link - no userlogin necessary
    $dbConn = new dbConnect();
    $dbConn->dbConnection();
    $adminDAO = new AdministrationDAO($dbConn);
    $transactionDAO = new TransactionDAO($dbConn);
    $principalDAO = new PrincipalDAO($dbConn);


    if (!$postKEYFROMLINK){

      if (!isset($_SESSION)) session_start();
      $userId = $_SESSION['user_id'];
      $userCategory = $_SESSION['user_category'];
      $principalId = $_SESSION['principal_id'];
      $principalName = $_SESSION['principal_name'];
      $principalType = $_SESSION['principal_type'];

      //capture screen : order seq -> document uid
      //requires principal id.
      if($postORDERSEQ!=false && $postDOCMASTID == ""){
        $dmUid = $transactionDAO->getDocumentUidByOrderSeq($postORDERSEQ, $principalId);
        if(isset($dmUid['uid']))
          $postDOCMASTID = $dmUid['uid'];
      }

    } else {

      $userId = "";
      $principalId = "";
      $principalName = "";
      $principalType = "";
      $userCategory = "";

    }


    /*  --------------------------------------------------------  *
     *        PERMISSION CHECKING AND DATA COLLECTION             *
     *   -------------------------------------------------------- */

    if (($postKEYFROMLINK!="") || ($userCategory=="D")) {

      //all security is bypassed
      $mfT = $transactionDAO->getDocumentWithDetailIgnorePermissionsItem($postDOCMASTID, $orderBy="principal_product.product_code");

      if (sizeof($mfT)==0) {
        echo "You do not have access to this information, or document master does not exist. (1)";
        return;
      }

      $hasRoleVP = true;
      if (
          (($userCategory=="D") && ($mfT[0]['depot_wms']!="Y")) ||
          ($userCategory!="D") && (
                (MD5($mfT[0]['dm_uid'].$mfT[0]['document_number'].NT_DOCUMENT_CONFIRMATION)!=$postKEYFROMLINK) &&
                ((MD5(gmdate('Y-m-d')).base64_encode($postDOCMASTID))!=$postKEYFROMLINK)
                )
          ){
        echo "You do not have access to this information, or document master does not exist. (2)";
        return;
      }
    } else {

      // this also doubles as the security check because this sql joins on user_principal_depot
      $mfT = $transactionDAO->getDocumentWithDetailItem($userId, $principalId, $postDOCMASTID, $orderBy="principal_product.product_code");
      if (sizeof($mfT)==0) {
        echo "You do not have access to this information, or document master does not exist. (3)";
        return;
      }

      $hasRoleVP = $adminDAO->hasRole($userId, $principalId, ROLE_VIEW_PRICE);

    }
 
    //Which template do we use???
    $docArr = $transactionDAO->getSimpleDocumentByDMUId($postDOCMASTID);

    $principalId = $docArr[0]['principal_uid'];

    $mfP = $principalDAO->getPrincipalItem($docArr[0]['principal_uid']);

    $p = new PresentationTO();
    $p->type = (($this->additionalType=="")?strtoupper($this->type):$this->additionalType);
    $p->systemUId = $docArr[0]['system_uid']; //document -> principal -> system
    $p->principalUid = $docArr[0]['principal_uid'];
    $p->depotUId = $docArr[0]['depot_uid'];
    $p->documentTypeUId = $docArr[0]['document_type_uid'];
    $p->documentStatusUId = $docArr[0]['document_status_uid'];
    $p->platform = $this->mobile;


    if((!$postKEYFROMLINK)){
      $uArr = $adminDAO->getUserItem($userId);
      if(count($uArr)==0){
        echo "ERROR: User could not be found!";
        return;
      }
      $p->userCategory = $uArr[0]['category'];  //principal default

    } else {
      $p->userCategory = 'P';  //principal default
    }


    //render($p);   //getPresentation($presentationType, $systemUId, $principalUid, $depotUId, $documentTypeUId, $documentStatusUId, $userCategory) {
    $miscDAO = new MiscellaneousDAO($dbConn);
    $prArr = $miscDAO->getPresentation($p);
    
    if(count($prArr)==0){
      echo "ERRROR: No presentation layer found!";
      return;
    }

    $stationaryScript = $prArr['stationary_script'];

    //render

    $pathTemplate = $ROOT. $PHPFOLDER . 'functional/presentations/view/'.$prArr['display_template_script'];
    if(is_file($pathTemplate))
      include($pathTemplate);

    // this note is to be combined with another note, then spawn that too
    if (!empty($prArr['additional_render_presentation_type'])) {
      echo "<script type='text/javascript' defer>
            window.open('".HOST_SURESERVER_AS_USER.$PHPFOLDER."functional/transaction/documentCard.php?DOCMASTID={$postDOCMASTID}&ADDITIONALTYPE={$prArr['additional_render_presentation_type']}','additionalNote','scrollbars=yes,width=750,height=600,resizable=yes');
            </script>";
    }


    $pathPrint = $ROOT. $PHPFOLDER . 'functional/presentations/print/'.$prArr['printing_control_script'];
    if(is_file($pathPrint))
      include($pathPrint);

  }



 }



?>