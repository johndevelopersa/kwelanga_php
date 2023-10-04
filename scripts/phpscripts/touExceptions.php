<?php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER."libs/CommonUtils.php");
include_once($ROOT.$PHPFOLDER."libs/BroadcastingUtils.php");
include_once($ROOT.$PHPFOLDER.'DAO/BIDAO.php');
include_once($ROOT.$PHPFOLDER."DAO/ImportDAO.php");
include_once($ROOT.$PHPFOLDER.'DAO/PrincipalDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/PostTransactionDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/DocumentUpdateDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/PostDocumentUpdateDAO.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER.'TO/SmartEventTO.php');
include_once($ROOT.$PHPFOLDER.'TO/PostingDocumentUpdateConfirmationTO.php');
include_once($ROOT.$PHPFOLDER.'TO/PostingDocumentStatusTO.php');
include_once($ROOT.$PHPFOLDER."libs/GUICommonUtils.php");


if (!isset($_SESSION)) session_start();
$userId = $_SESSION['user_id'];
$principalId = $_SESSION['principal_id'];

if (isset($_POST["FILTERLIST"])) { $postFilterExcept = urldecode($_POST["FILTERLIST"]); $postFilterExcept=explode(',',$postFilterExcept); } else $postFilterExcept='';
if (isset($_POST["PAGEDEST"])) $postPAGEDEST = $_POST["PAGEDEST"]; else $postPAGEDEST="divArea";
if (isset($_POST["CALLBACK"])) $postCALLBACK = $_POST["CALLBACK"]; else $postCALLBACK="";
if (isset($_SESSION["USERID"])) $postUSERID=$_SESSION["USERID"]; else $postUSERID="0";
$_POST["RBNAME"] = '';

$dbConn = new dbConnect();
$dbConn->dbConnection();


$docUpdateDAO = new DocumentUpdateDAO($dbConn);
$eArr = $docUpdateDAO->getExceptions();


echo '<pre>';
//var_dump($eArr);


$fldFilterExceptname = "ExceptFilter"; // the names of the filter fields
$fldFilterExceptUsageArr = array(1=>"N","Y","Y","Y","Y","Y","Y","Y","Y","Y","Y");
$fldFilterExceptSizeArr = array(1=>"0","5","5","5","5","5","5","5","5","5","5");
$FCArr = array(); // strip off the columns we want. Remember that fetch_array doubles up everything.
$headers = array("Update Type", "Principal", "Depot", "Update File", "Document Ref", "Principal Lookup", "Depot Lookup", "Status Code" , "Created Date", "Status Message", );
$valArr = array();


class ExceptionTbl {

  public $null;
  public $updateType;
  public $principal;
  public $depot;
  public $incomingFilename;
  public $documentNumber;
  public $principalLookup;
  public $depotLookup;
  public $statusLookup;
  public $createdDT;
  public $processMsg;

}


foreach ($eArr as $k => $f) {

  //var_Dump($f);

  $class = new ExceptionTbl;
  $class->null = "";
  $class->updateType = $f['update_type_uid'];
  $class->principal = $f['principal'];
  $class->depot = $f['depot'];
  $class->incomingFilename = $f['incoming_filename'];
  $class->documentNumber = $f['document_number'];
  $class->principalLookup = $f['principal_lookup'];
  $class->depotLookup = $f['depot_lookup'];
  $class->statusLookup = $f['document_status_lookup'];
  $class->createdDT = $f['created_datetime'];
  $class->processMsg = "<font color='red'>".$f['processed_msg']."</font>";

  $valArr[] = $f['uid'];
  $FCArr[]=$class;
}

$pArr = GUICommonUtils::applyFilter($FCArr,$postFilterExcept);




/*--------------------------------------------------------------------------------------------------
 *
 *     SCREEN OUTPUT
 *
 *-------------------------------------------------------------------------------------------------*/


echo <<<EOF
<link href="{$DHTMLROOT}{$PHPFOLDER}/css/default.css" rel="stylesheet" type="text/css">
<DIV align="center" id="divArea">
<script type="text/javascript" language="javascript" src="{$DHTMLROOT}{$PHPFOLDER}js/jquery.js"></script>
<script type="text/javascript" language="javascript" src="{$DHTMLROOT}{$PHPFOLDER}js/dops_global_functions.js"></script>
<BR>
<H2 style="color:#047;">Update Exceptions</H1>
EOF;


echo '<TABLE>';

  // filter row
  GUICommonUtils::getFilterFields($fldFilterExceptname,
                                  $fldFilterExceptUsageArr,
                                  $fldFilterExceptSizeArr,
                                  $postFilterExcept,
                                  $postPAGEDEST,
                                  "+'&USERID=".$postUSERID."'",
                                  $ROOT.$PHPFOLDER.'/scripts/phpscripts/'.basename(__FILE__)
                                  );
  // the data
  GUICommonUtils::outputRBTable ($headers,
                                 $pArr,
                                 'duUid',
                                 $valArr,
                                 '',
                                 'check',
                                 "",
                                 "");
echo '</TABLE>';