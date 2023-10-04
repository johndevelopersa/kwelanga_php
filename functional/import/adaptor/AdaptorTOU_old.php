<?php

/* -----------------------------
 *    Document Update Adaptor
 * -----------------------------
 *
 * Should be as lightweight as possible.
 *
 * created date : 2012.07.30
 * owner : onyx
 *
 * ----------------------------- */

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');
include_once($ROOT.$PHPFOLDER.'DAO/PrincipalDAO.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER.'TO/PostingDocumentUpdateTO.php');
include_once($ROOT.$PHPFOLDER.'TO/PostingDocumentUpdateDetailTO.php');
include_once($ROOT.$PHPFOLDER.'TO/PostingDocumentUpdateBatchTO.php');
include_once($ROOT.$PHPFOLDER.'TO/PostingStockTO.php');
include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');



class AdaptorTOU {


  private $dbConn;
  private $principalDAO;


  function __construct($dbConn){

    $this->dbConn = $dbConn;
    $this->principalDAO = new PrincipalDAO($this->dbConn);

  }
   function adaptorVITAL_INV($content, $onlineFileProcessItem){

    global $ROOT, $PHPFOLDER, $importDAO;
    $arrTO = array();
    $eTO = new ErrorTO;
    $lineArr = explode("\n", trim(str_replace('"','',$content))); //linefeed newlines.
    $ullCnfArr = array();

    // disregard the first line - headers
    
    if (trim(substr($lineArr[0],3,7))=="Princip") {
      unset($lineArr[0]);
    } else {
      $eTO->type = FLAG_ERRORTO_ERROR;
      $eTO->description = "First line expected to be a header!";
      return $eTO;
    }

    $docs=array();
    foreach($lineArr as $lno => $line){
      if (trim($line)=="") continue;

      $fields=explode(",",$line);
      $docs[$fields[0]."-".$fields[2]][] = $fields;

      // LINE STRUCTURE CHECKS
      if (sizeof($fields)!=16) {
        $eTO->type = FLAG_ERRORTO_ERROR;
        $eTO->description = "invalid Column Count - expecting 16 columns!";
        return $eTO;
      }

    }

    $mfDPEM = $importDAO->getAllDepotPrincipalExportMapping(); // get all depot-principal-export-mapping
    $depotMap = $importDAO->getAllDepotPrincipalExportMapping($indexArray=ImportDAO::$PDP_EXP_MAP_INDEX_1);

    $lineCnt=1;
     foreach($docs as $key=>$doc){

      $postingDocumentUpdateTO = new PostingDocumentUpdateTO();

      // should really check this every line tho
        $postingDocumentUpdateTO->documentTypeUId = DT_ORDINV;
 
      // set the principal
      $onlineFileProcessingMapping=$importDAO->getMappingFromOIMByPrincipalIdentifier($onlineFileProcessItem["onlineFileProcessingMapping"], substr($doc[0][0],0,3));
      if (empty($onlineFileProcessingMapping)) {
        $eTO->type = FLAG_ERRORTO_ERROR;
        
        $eTO->description = "Unknown Principal Type (".trim(substr($line,0,3)).") @line:".($key+1);
        $eTO->identifier = ET_CUSTOMER;
        return $eTO;
      }
      // this adaptor file can have multiple principals within file, so you cannot have null principal_uid
      if ($onlineFileProcessingMapping["principal_uid"]=="") {
        $eTO->type = FLAG_ERRORTO_ERROR;
        $eTO->description = "Incorrect Configuration : onlineFileProcessingMapping encountered blank principal_uid or not found!";
        $eTO->identifier = ET_SYSTEM;
        return $eTO;
      }

      $postingDocumentUpdateTO->createdDatetime = CommonUtils::getGMTime(0);
      $postingDocumentUpdateTO->incomingFilename = substr(basename($onlineFileProcessItem["file_being_processed"]),52,18);

//    Vital send 2 different data formats
//        0123456789
//    ZB4,2016/10/31 12:00:00 AM,B4-0700813,4517897453,B4-0700813,15146,15146,PNP HAYFIELDS,550-10X1,2,20.8,162.1,0,162.1,324.2,369.59

//        0123456789
//    ZT1,03/10/2016 00:00:00,T1-000403,4516542114,T1-000403,53807,53807,PNP MUSGRAVE,T1412,1,9.5,488.82,0,488.82,488.82,557.25

      if (substr($doc[0][1],2,1) == '/' && substr($doc[0][1],5,1) == '/') {
          $postingDocumentUpdateTO->invoiceDate = substr($doc[0][1],6,4) . '-' . substr($doc[0][1],3,2) . '-' . substr($doc[0][1],0,2);
      } else {
          $postingDocumentUpdateTO->invoiceDate = substr($doc[0][1],0,4) . '-' . substr($doc[0][1],5,2) . '-' . substr($doc[0][1],8,2);
      }
      $postingDocumentUpdateTO->principalUId = $onlineFileProcessingMapping["principal_uid"];
      $postingDocumentUpdateTO->invoiceNumber = substr($doc[0][2],0,7);

      if ((!isset($depotMap[$postingDocumentUpdateTO->principalUId][substr($doc[0][0],0,3)])) || (trim($depotMap[$postingDocumentUpdateTO->principalUId][substr($doc[0][0],0,3)]["principal_code"])=="")) {
        $eTO->type = FLAG_ERRORTO_ERROR;
        $eTO->description = "Vital Depot mapping could not be determined for {$doc[0][0]}";
        $eTO->identifier = ET_SYSTEM;
        return $eTO;
      }

      $postingDocumentUpdateTO->depotUId=$depotMap[$postingDocumentUpdateTO->principalUId][substr($doc[0][0],0,3)]["depot_uid"];
      
      $stripdocument = str_replace(substr($doc[0][0],1,2).'-','',$doc[0][4]);
      $stripinvoice  = str_replace(substr($doc[0][0],1,2).'-','',$doc[0][4]);
 
      $postingDocumentUpdateTO->documentNumber    = str_pad(str_replace('"','',$stripdocument),8,"0",STR_PAD_LEFT);
      $postingDocumentUpdateTO->invoiceNumber     = str_pad(str_replace('"','',$stripinvoice),6,"0",STR_PAD_LEFT);
      $postingDocumentUpdateTO->updateTypeUid     = UPDATE_DOCUMENT_TYPE_INVOICE;
      $postingDocumentUpdateTO->documentStatusUId = DST_INVOICED;
      $postingDocumentUpdateTO->podReasonUId = 0;

      foreach ($doc as $dtl) {

        $postingDocumentUpdateDetailTO = new PostingDocumentUpdateDetailTO();

        // don't worry about doing a pricing calculation check here as that is done in onlineUpdateProcessing if this pricing ends up being used
        $postingDocumentUpdateDetailTO->pageNo = "";
        $postingDocumentUpdateDetailTO->lineNo = "";
        $postingDocumentUpdateDetailTO->productCode = trim(str_replace('"', '',$dtl[8]));
        $postingDocumentUpdateDetailTO->documentQty = trim(str_replace('"', '',$dtl[9]));
        $postingDocumentUpdateDetailTO->deliveredQty = trim(str_replace('"', '',$dtl[9]));
        // pricing
        $postingDocumentUpdateDetailTO->listPrice = round($dtl[10],2);
        $postingDocumentUpdateDetailTO->discountValue = round($postingDocumentUpdateDetailTO->listPrice*$dtl[11]/100,2);
        $postingDocumentUpdateDetailTO->nettPrice = round($dtl[12],2);
        $postingDocumentUpdateDetailTO->extendedPrice = round($dtl[13],2);
        $postingDocumentUpdateDetailTO->total = round($dtl[14],2);
        $postingDocumentUpdateDetailTO->vatAmount = ((($postingDocumentUpdateDetailTO->total - $postingDocumentUpdateDetailTO->extendedPrice)>0.05)?($postingDocumentUpdateDetailTO->total - $postingDocumentUpdateDetailTO->extendedPrice):0);
        $postingDocumentUpdateDetailTO->vatRate = (($postingDocumentUpdateDetailTO->vatAmount>0)?VAL_VAT_RATE_TBLSTD:0);

        $postingDocumentUpdateTO->detailArr[] = $postingDocumentUpdateDetailTO;
       }

      $arrTO[] = $postingDocumentUpdateTO;
      // do it a second time as this is actually a POD and an Invoice and we need to move it through both statuses for the triggered workflow
     // if ($postingDocumentUpdateTO->updateTypeUid != UPDATE_DOCUMENT_TYPE_CORRECTION) {
    //    $copyTO = clone $postingDocumentUpdateTO;
     //   $copyTO->updateTypeUid = UPDATE_DOCUMENT_TYPE_POD_VIT;
     //   $copyTO->documentStatusUId=DST_DELIVERED_POD_OK;
    //    $arrTO[] = $copyTO;
    //  }

      $lineCnt++;

    } // unique doc loop

    $eTO->type = FLAG_ERRORTO_SUCCESS;
    $eTO->description = "Successful";
    $eTO->object = $arrTO;
    return $eTO;

  }
  function adaptorVITAL_PD($content, $onlineFileProcessItem){

    global $ROOT, $PHPFOLDER, $importDAO;
    $arrTO = array();
    $eTO = new ErrorTO;
    $lineArr = explode("\n", trim(str_replace('"','',$content))); //linefeed newlines.
    $ullCnfArr = array();

    // disregard the first line - headers
    
    if (trim(substr($lineArr[0],3,7))=="Invoice") {
      unset($lineArr[0]);
    } else {
      $eTO->type = FLAG_ERRORTO_ERROR;
      $eTO->description = "First line expected to be a header!";
      return $eTO;
    }

    $docs=array();
    foreach($lineArr as $lno => $line){
      if (trim($line)=="") continue;

      $fields=explode(",",$line);
      $docs[$fields[0]][] = $fields;

      // LINE STRUCTURE CHECKS
      if (sizeof($fields)!=14) {
        $eTO->type = FLAG_ERRORTO_ERROR;
        $eTO->description = "invalid Column Count - expecting 14 columns!";
        return $eTO;
      }
    }
    $mfDPEM = $importDAO->getAllDepotPrincipalExportMapping(); // get all depot-principal-export-mapping
    $depotMap = $importDAO->getAllDepotPrincipalExportMapping($indexArray=ImportDAO::$PDP_EXP_MAP_INDEX_1);
    
    $lineCnt=1;

    foreach($docs as $key=>$doc){

      $postingDocumentUpdateTO = new PostingDocumentUpdateTO();

      // should really check this every line tho
        $postingDocumentUpdateTO->documentTypeUId = DT_ORDINV;
        
      // set the principal
      $onlineFileProcessingMapping=$importDAO->getMappingFromOIMByPrincipalIdentifier($onlineFileProcessItem["onlineFileProcessingMapping"], ('Z'.substr($doc[0][0],0,2)));
      if (empty($onlineFileProcessingMapping)) {
        $eTO->type = FLAG_ERRORTO_ERROR;

        $eTO->description = "Unknown Principal Type (".trim(substr($line,0,2)).") @line:".($key+1);
        $eTO->identifier = ET_CUSTOMER;
        return $eTO;
      }
      // this adaptor file can have multiple principals within file, so you cannot have null principal_uid
      if ($onlineFileProcessingMapping["principal_uid"]=="") {
        $eTO->type = FLAG_ERRORTO_ERROR;
        $eTO->description = "Incorrect Configuration : onlineFileProcessingMapping encountered blank principal_uid or not found!";
        $eTO->identifier = ET_SYSTEM;
        return $eTO;
      }

      $postingDocumentUpdateTO->createdDatetime = CommonUtils::getGMTime(0);
      $postingDocumentUpdateTO->incomingFilename = substr(basename($onlineFileProcessItem["file_being_processed"]),52,18);
      
//    Vital send 2 different data formats


      if (substr($doc[0][5],2,1) == '/' && substr($doc[0][5],5,1) == '/') {
          $postingDocumentUpdateTO->invoiceDate = substr($doc[0][5],6,4) . '-' . substr($doc[0][5],3,2) . '-' . substr($doc[0][5],0,2);
      } else {
          $postingDocumentUpdateTO->invoiceDate = substr($doc[0][5],0,4) . '-' . substr($doc[0][5],5,2) . '-' . substr($doc[0][5],8,2);
      }
//      print_r($postingDocumentUpdateTO);
      
      $postingDocumentUpdateTO->principalUId = $onlineFileProcessingMapping["principal_uid"];
      
      if ((!isset($depotMap[$postingDocumentUpdateTO->principalUId]['Z'.substr($doc[0][0],0,2)])) || (trim($depotMap[$postingDocumentUpdateTO->principalUId]['Z'.substr($doc[0][0],0,2)]["principal_code"])=="")) {
        $eTO->type = FLAG_ERRORTO_ERROR;
        $eTO->description = "Vital Depot mapping could not be determined for {$doc[0][0]}";
        $eTO->identifier = ET_SYSTEM;
        return $eTO;
      }
      $postingDocumentUpdateTO->depotUId=$depotMap[$postingDocumentUpdateTO->principalUId]['Z'.substr($doc[0][0],0,2)]["depot_uid"];      
      $stripdocument = str_replace(substr($doc[0][0],0,3),'',$doc[0][0]);
      $postingDocumentUpdateTO->documentNumber    = str_pad(str_replace('"','',$stripdocument),8,"0",STR_PAD_LEFT);
      $postingDocumentUpdateTO->updateTypeUid     = UPDATE_DOCUMENT_TYPE_POD_ULL;
      
      if(trim($doc[0][8]) ==  '')  {
         $postingDocumentUpdateTO->documentStatusUId = DST_DELIVERED_POD_OK;
      } else {
         $postingDocumentUpdateTO->documentStatusUId = DST_DIRTY_POD;
      }		
     $postingDocumentUpdateTO->podReasonUId = 0;

      $arrTO[] = $postingDocumentUpdateTO;
      // do it a second time as this is actually a POD and an Invoice and we need to move it through both statuses for the triggered workflow
     // if ($postingDocumentUpdateTO->updateTypeUid != UPDATE_DOCUMENT_TYPE_CORRECTION) {
    //    $copyTO = clone $postingDocumentUpdateTO;
     //   $copyTO->updateTypeUid = UPDATE_DOCUMENT_TYPE_POD_VIT;
     //   $copyTO->documentStatusUId=DST_DELIVERED_POD_OK;
    //    $arrTO[] = $copyTO;
    //  }

      $lineCnt++;

    } // unique doc loop

    $eTO->type = FLAG_ERRORTO_SUCCESS;
    $eTO->description = "Successful";
    $eTO->object = $arrTO;
    return $eTO;

  }

  /********************************************************************************************************/
  function adaptorVITAL_CREDITS($content, $onlineFileProcessItem){

    global $ROOT, $PHPFOLDER, $importDAO;
    $arrTO = array();
    $eTO = new ErrorTO;
    $lineArr = explode("\n", trim(str_replace('"','',$content))); //linefeed newlines.
    $ullCnfArr = array();

    // disregard the first line - headers
    
    if (trim(substr($lineArr[0],3,7))=="Custome") {
      unset($lineArr[0]);
    } else {
      $eTO->type = FLAG_ERRORTO_ERROR;
      $eTO->description = "First line expected to be a header!";
      return $eTO;
    }

    $docs=array();
    foreach($lineArr as $lno => $line){
      if (trim($line)=="") continue;

      $fields=explode(",",$line);
      $docs[$fields[0]][] = $fields;
      // LINE STRUCTURE CHECKS
      if (sizeof($fields)!=17) {
        $eTO->type = FLAG_ERRORTO_ERROR;
        $eTO->description = "invalid Column Count - expecting 17 columns!";
        return $eTO;
      }
    }
    $mfDPEM = $importDAO->getAllDepotPrincipalExportMapping(); // get all depot-principal-export-mapping
    $depotMap = $importDAO->getAllDepotPrincipalExportMapping($indexArray=ImportDAO::$PDP_EXP_MAP_INDEX_1);

    $lineCnt=1;

    foreach($docs as $key=>$doc){

      $postingDocumentUpdateTO = new PostingDocumentUpdateTO();

      // should really check this every line tho
        $postingDocumentUpdateTO->documentTypeUId = DT_CREDITNOTE;
 
      // set the principal
      $onlineFileProcessingMapping=$importDAO->getMappingFromOIMByPrincipalIdentifier($onlineFileProcessItem["onlineFileProcessingMapping"], ('Z'.substr($doc[0][0],0,2)));
      if (empty($onlineFileProcessingMapping)) {
        $eTO->type = FLAG_ERRORTO_ERROR;
        
        $eTO->description = "Unknown Principal Type (".trim(substr($line,0,2)).") @line:".($key+1);
        $eTO->identifier = ET_CUSTOMER;
        return $eTO;
      }
       // this adaptor file can have multiple principals within file, so you cannot have null principal_uid
      if ($onlineFileProcessingMapping["principal_uid"]=="") {
        $eTO->type = FLAG_ERRORTO_ERROR;
        $eTO->description = "Incorrect Configuration : onlineFileProcessingMapping encountered blank principal_uid or not found!";
        $eTO->identifier = ET_SYSTEM;
        return $eTO;
      }

      $postingDocumentUpdateTO->createdDatetime = CommonUtils::getGMTime(0);
      $postingDocumentUpdateTO->incomingFilename = trim(substr(basename($onlineFileProcessItem["file_being_processed"]),58,18));

//    Vital send 2 different data formats

      if (substr($doc[0][4],2,1) == '/' && substr($doc[0][4],5,1) == '/') {
          $postingDocumentUpdateTO->invoiceDate = substr($doc[0][4],6,4) . '-' . substr($doc[0][4],3,2) . '-' . substr($doc[0][4],0,2);
      } else {
          $postingDocumentUpdateTO->invoiceDate = substr($doc[0][4],0,4) . '-' . substr($doc[0][4],5,2) . '-' . substr($doc[0][4],8,2);
      } 
      
      $postingDocumentUpdateTO->principalUId = $onlineFileProcessingMapping["principal_uid"];
      $postingDocumentUpdateTO->invoiceNumber = '';

      if ((!isset($depotMap[$postingDocumentUpdateTO->principalUId]['Z'.substr($doc[0][0],0,2)])) || (trim($depotMap[$postingDocumentUpdateTO->principalUId]['Z'.substr($doc[0][0],0,2)]["principal_code"])=="")) {
        $eTO->type = FLAG_ERRORTO_ERROR;
        $eTO->description = "Vital Depot mapping could not be determined for {$doc[0][0]}";
        $eTO->identifier = ET_SYSTEM;
        return $eTO;
      }

      $postingDocumentUpdateTO->depotUId=$depotMap[$postingDocumentUpdateTO->principalUId]['Z'.substr($doc[0][0],0,2)]["depot_uid"];
      
      $stripdocument = str_replace(substr($doc[0][0],0,2).'-','',$doc[0][0]);
 
      $postingDocumentUpdateTO->documentNumber    = str_pad(str_replace('"','',$stripdocument),8,"0",STR_PAD_LEFT);
      $postingDocumentUpdateTO->invoiceNumber     = '';
      $postingDocumentUpdateTO->updateTypeUid     = UPDATE_DOCUMENT_TYPE_CORRECTION;
      $postingDocumentUpdateTO->documentStatusUId = DST_ACCEPTED;
      $postingDocumentUpdateTO->sourceDocumentNumber=str_pad(str_replace('"','',$stripdocument),8,"0",STR_PAD_LEFT);

			switch (trim(str_replace('"', '',$doc[0][16]))) {
        case  "Not Ordered":
           $podreason = "1";
           break;
       case  "Duplicate Order":
           $podreason = "3";
           break;
       case  "Customer Overstocked":
           $podreason = "4";
           break;
       case  "Damaged by Customer (Store)":
           $podreason = "6";
           break;
       case  "Damaged Stock":
           $podreason = "6";
           break;
       case  "Damaged in Warehouse":
           $podreason = "6";
           break;
       case  "Recieved as Damaged":
           $podreason = "6";
           break;
       case  "Damaged on Vehicle":
           $podreason = "6";
           break;
       case  "Short Loaded on Vehicle":
           $podreason = "10";
           break;
       case  "Short delivered by Driver":
           $podreason = "10";
           break;
       case  "Incorrect Bar Code (printed)":
           $podreason = "17";
           break;
       case  "Order Cancelled by Customer":
           $podreason = "23";
           break;
       case  "Delivery cancelled by Principal":
           $podreason = "23";
           break;
       case  "Customer Stock Take":
           $podreason = "72";
           break;
       case  "Accident":
           $podreason = "74";
           break;
       case  "CROSS PICKED":
           $podreason = "78";
           break;
       case  "Hijacked/Major theft":
           $podreason = "79";
           break;
       case  "No Expiry Date":
           $podreason = "102";
           break;
       case  "Sales Order Incorrectly Captured":
           $podreason = "104";
           break;
       case  "Late Delivery - due to Late Receipt":
           $podreason = "105";
           break;
       case  "Goods Wrongly Priced":
           $podreason = "107";
           break;
       case  "Customer (Store) Closed - Billable":
           $podreason = "112";
           break;
     default:
           $podreason = "";
 } 

      $postingDocumentUpdateTO->podReasonUId = $podreason;

      foreach ($doc as $dtl) {

        $postingDocumentUpdateDetailTO = new PostingDocumentUpdateDetailTO();

        // don't worry about doing a pricing calculation check here as that is done in onlineUpdateProcessing if this pricing ends up being used
        $postingDocumentUpdateDetailTO->pageNo = "";
        $postingDocumentUpdateDetailTO->lineNo = "";
        $postingDocumentUpdateDetailTO->productCode = trim(str_replace('"', '',trim(substr($dtl[9],4,10))));
        $postingDocumentUpdateDetailTO->orderedQty  = trim(str_replace('"', '',$dtl[13]));;
        $postingDocumentUpdateDetailTO->documentQty = trim(str_replace('"', '',$dtl[13]));
        $postingDocumentUpdateDetailTO->deliveredQty = (trim(str_replace('"', '',$dtl[11])));
        $postingDocumentUpdateDetailTO->podReasonLookup = $podreason;
        
        $postingDocumentUpdateTO->detailArr[] = $postingDocumentUpdateDetailTO;
        
//        print_r($postingDocumentUpdateTO);

      }

      $arrTO[] = $postingDocumentUpdateTO;
      // do it a second time as this is actually a POD and an Invoice and we need to move it through both statuses for the triggered workflow
     // if ($postingDocumentUpdateTO->updateTypeUid != UPDATE_DOCUMENT_TYPE_CORRECTION) {
    //    $copyTO = clone $postingDocumentUpdateTO;
     //   $copyTO->updateTypeUid = UPDATE_DOCUMENT_TYPE_POD_VIT;
     //   $copyTO->documentStatusUId=DST_DELIVERED_POD_OK;
    //    $arrTO[] = $copyTO;
    //  }

      $lineCnt++;

    } // unique doc loop

    $eTO->type = FLAG_ERRORTO_SUCCESS;
    $eTO->description = "Successful";
    $eTO->object = $arrTO;
    return $eTO;

  }
  /********************************************************************************************************/
  function adaptorIRL_CNF($content, $onlineFileProcessItem){

    global $ROOT, $PHPFOLDER, $importDAO;
    $arrTO = array();
    $eTO = new ErrorTO;
    $lineArr = explode("\n", trim(str_replace('"','',$content))); //linefeed newlines.

    // disregard the first line - headers
    
    if (trim(substr($lineArr[0],0,3))=="000") {
//      unset($lineArr[0]);
    } else {
      $eTO->type = FLAG_ERRORTO_ERROR;
      $eTO->description = "First line expected to be a header!";
      return $eTO;
    }

    $docs=array();
    foreach($lineArr as $lno => $line){
      if (trim($line)=="") continue;

      $fields=explode("ý",$line);
      $docs[$fields[0]][] = $fields;
    }

    $lineCnt=1;
     $postingDocumentUpdateTO = new PostingDocumentUpdateTO();

    foreach($docs as $key=>$doc){
 echo "<br>";    	
  print_r($doc);

      if ($doc[0][0] == '000') {
        $datetimeStr = substr($doc[0][4],0,4) . '-' . substr($doc[0][4],4,2) . '-' . substr($doc[0][4],6,2) . ' ' . substr($doc[0][5],0,2) . ':' . substr($doc[0][4],2,2) . ':00';
        $dateStamp = strtotime($datetimeStr);
      } elseif ($doc[0][0] == '030') {
        $mfDPEM    = $importDAO->getAllDepotPrincipalExportMapping(); // get all depot-principal-export-mapping
        $depotMap  = $importDAO->getAllDepotPrincipalExportMapping($indexArray=ImportDAO::$PDP_EXP_MAP_INDEX_3);
        $depotcode = $importDAO->getDepotByDocument($depotMap[trim($doc[0][12])]["principal_uid"],str_pad(trim($doc[0][4]),8,"0",STR_PAD_LEFT));

        $postingDocumentUpdateTO->principalUId=$depotMap[trim($doc[0][12])]["principal_uid"];
        $postingDocumentUpdateTO->updateTypeUid     = UPDATE_DOCUMENT_TYPE_CONFIRM;
        $postingDocumentUpdateTO->createdDatetime   = CommonUtils::getGMTime(0);
        $postingDocumentUpdateTO->incomingFilename  = basename($onlineFileProcessItem["file_being_processed"]);
        $postingDocumentUpdateTO->principalLookup   = trim($doc[0][12]);
        $postingDocumentUpdateTO->documentNumber    = str_pad(trim($doc[0][4]),8,"0",STR_PAD_LEFT);
        $postingDocumentUpdateTO->depotUId          = $depotcode[0]['depot_uid'];
        $postingDocumentUpdateTO->documentStatusUId = DST_ACCEPTED;
        $postingDocumentUpdateTO->mergeDate = date('Y-m-d', $dateStamp);
        $postingDocumentUpdateTO->mergeTime = date('H:i:s', $dateStamp);
        if (trim($doc[0][10]) <> '') {
           $postingDocumentUpdateTO->dueDeliveryDate = substr($doc[0][10],0,4) . '-' . substr($doc[0][10],4,2) . '-' . substr($doc[0][10],6,2);      	
        } else {
           $postingDocumentUpdateTO->dueDeliveryDate = '0000-00-00';
        }   
      } elseif ($doc[0][0] == '220') {

        $mfDPEM    = $importDAO->getAllDepotPrincipalExportMapping(); // get all depot-principal-export-mapping
        $depotMap  = $importDAO->getAllDepotPrincipalExportMapping($indexArray=ImportDAO::$PDP_EXP_MAP_INDEX_3);
        $depotcode = $importDAO->getDepotByDocument($depotMap[trim($doc[0][19])]["principal_uid"],str_pad(trim($doc[0][3]),8,"0",STR_PAD_LEFT));
        $postingDocumentUpdateTO->principalUId=$depotMap[trim($doc[0][19])]["principal_uid"];
        $postingDocumentUpdateTO->updateTypeUid     = UPDATE_DOCUMENT_TYPE_INVOICE;
        $postingDocumentUpdateTO->createdDatetime   = CommonUtils::getGMTime(0);
        $postingDocumentUpdateTO->incomingFilename  = basename($onlineFileProcessItem["file_being_processed"]);
        $postingDocumentUpdateTO->principalLookup   = trim($doc[0][27]);
        $postingDocumentUpdateTO->documentNumber    = str_pad(trim($doc[0][3]),8,"0",STR_PAD_LEFT);
        $postingDocumentUpdateTO->depotUId          = $depotcode[0]['depot_uid'];
        
         if (trim($doc[0][11]) == '50') {
           $postingDocumentUpdateTO->documentStatusUId = DST_INVOICED;
        } else {
           $postingDocumentUpdateTO->documentStatusUId = DST_CANCELLED;
        }
        $postingDocumentUpdateTO->podReasonUId = 0;
        
        $postingDocumentUpdateTO->invoiceDate = substr($doc[0][7],0,4) . '-' . substr($doc[0][7],4,2) . '-' . substr($doc[0][7],6,2);
      } elseif ($doc[0][0] == '221') {
          $postingDocumentUpdateDetailTO = new PostingDocumentUpdateDetailTO();
          foreach ($doc as $dtl) {

            $postingDocumentUpdateDetailTO = new PostingDocumentUpdateDetailTO();
            // don't worry about doing a pricing calculation check here as that is done in onlineUpdateProcessing if this pricing ends up being used
            $postingDocumentUpdateDetailTO->pageNo = "";
            $postingDocumentUpdateDetailTO->lineNo = "";
                                
            if(trim($dtl[12]) == 'DOYPC0000002') {
            	 $postingDocumentUpdateDetailTO->productCode = 'DOYPC000002';
            } elseif(trim($dtl[12]) == 'DOYPC0000001') {
            	 $postingDocumentUpdateDetailTO->productCode = 'DOYPC000001';
            } elseif(trim($dtl[12]) == 'DOYPC0000004') {
            	 $postingDocumentUpdateDetailTO->productCode = 'DOYPC000004';
            } else {
            	 $postingDocumentUpdateDetailTO->productCode  = trim($dtl[12]);
            }     
    
            if ($postingDocumentUpdateTO->documentStatusUId == DST_INVOICED) {
                  $postingDocumentUpdateDetailTO->documentQty  = trim($dtl[3]);
                  $postingDocumentUpdateDetailTO->deliveredQty = trim($dtl[3]);
            } else {
               $postingDocumentUpdateDetailTO->documentQty  = 0;
               $postingDocumentUpdateDetailTO->deliveredQty = 0;
            }
            $postingDocumentUpdateTO->detailArr[] = $postingDocumentUpdateDetailTO;
          }
      } elseif (substr($doc[0][0],2,1) == '9') {
            $arrTO[] = $postingDocumentUpdateTO;
      }
    } // unique doc loop
    
echo(substr($doc[0][0],2,1));
     $eTO->type = FLAG_ERRORTO_SUCCESS;
     $eTO->description = "Successful";
     $eTO->object = $arrTO;
     return $eTO;

  }
  /********************************************************************************************************/
  function adaptorVITAL_STOCK($content, $onlineFileProcessItem){

    global $ROOT, $PHPFOLDER, $importDAO;
    $arrTO = array();
    $eTO = new ErrorTO;
    $lineArr = explode("\n", trim(str_replace('"','',$content))); //linefeed newlines.
    $ullCnfArr = array();

    // disregard the first line - headers
    
    if (trim(substr($lineArr[0],3,7))=="Princip") {
      unset($lineArr[0]);
    } else {
      $eTO->type = FLAG_ERRORTO_ERROR;
      $eTO->description = "First line expected to be a header!";
      return $eTO;
    }

    $docs=array();
    foreach($lineArr as $lno => $line){
      if (trim($line)=="") continue;

      $fields=explode(",",$line);
      $docs[$fields[0]][] = $fields;

      // LINE STRUCTURE CHECKS
      if (sizeof($fields)!=10) {
        $eTO->type = FLAG_ERRORTO_ERROR;
        $eTO->description = "invalid Column Count - expecting 10 columns!";
        return $eTO;
      }
    }
    
    ?>
    <pre>
<?php
print_r($docs);
?>

    	
    	</pre>
<?php
    foreach($docs as $key=>$doc){
    	
        // set the principal
       $onlineFileProcessingMapping=$importDAO->getMappingFromOIMByPrincipalIdentifier($onlineFileProcessItem["onlineFileProcessingMapping"], (substr($doc[0][0],1,2)));
        if (empty($onlineFileProcessingMapping)) {
          $eTO->type = FLAG_ERRORTO_ERROR;
          $eTO->description = "Unknown Principal Type (".trim(substr($line,0,2)).") @line:".($key+1);
          $eTO->identifier = ET_CUSTOMER;
          return $eTO;
        }
        $PostingStockTO = new PostingStockTO();
    }

    $eTO->type = FLAG_ERRORTO_SUCCESS;
    $eTO->description = "Successful";
    $eTO->object = $arrTO;
    return $eTO;
  }
  /********************************************************************************************************/

  function adaptorVITAL_CANCEL($content, $onlineFileProcessItem){

    global $ROOT, $PHPFOLDER, $importDAO;
    $arrTO = array();
    $eTO = new ErrorTO;
    $lineArr = explode("\n", trim(str_replace('"','',$content))); //linefeed newlines.
    $ullCnfArr = array();

    // disregard the first line - headers
    

  echo (substr($lineArr[0],3,16))  ;
    
     if (trim(substr($lineArr[0],3,16))=="Load List Status" || (trim(substr($lineArr[0],3,8))=="Order No") || (trim(substr($lineArr[0],3,6))=="Status")){
        echo 'File OK';
     } else {
       $eTO->type = FLAG_ERRORTO_ERROR;
       $eTO->description = "First line expected to be a header!";
       return $eTO;
     }
     $fla = explode(',',$lineArr[0]);
     $ae = 0;
     $true = 'F';
     while ($ae<count($fla) && $true == 'F') {
      echo "<br>";
     	echo TRIM($fla[$ae]);
      echo "<br>";
 	     if (TRIM($fla[$ae]) == "Status" || TRIM(substr($fla[$ae],3,6)) == "Status" ) {
 	     	
 	     	echo("KK");
          	$true = 'T';
          	$SeqStatus = $ae;
       }
       $ae++;
     }
     if ($true == 'F') {         	            	
        $eTO->type = FLAG_ERRORTO_ERROR;
        $eTO->description = "First Line of file ".basename($onlineFileProcessItem["file_being_processed"])."  ---  Status Not Located";
        $eTO->identifier = ET_SYSTEM;
        return $eTO;
     }
     $ae = 0;
     $true = 'F';
     while ($ae<count($fla) && $true == 'F') {
 	     if (substr($fla[$ae],3,8) == "Order No" || $fla[$ae] == "Order No") {
          	$true = 'T';
          	$SeqOrderNo = $ae;
       }
       $ae++;
     }
     if ($true == 'F') {         	            	
        $eTO->type = FLAG_ERRORTO_ERROR;
        $eTO->description = "First Line of file ".basename($onlineFileProcessItem["file_being_processed"])."  ---  Order No Not Located";
        $eTO->identifier = ET_SYSTEM;
        return $eTO;
     }    
     $ae = 0;
     $true = 'F';
     while ($ae<count($fla) && $true == 'F') {
 	     if (TRIM($fla[$ae]) == "Principal No") {
          	$true = 'T';
          	$SeqPrincipalNo = $ae;
       }
       $ae++;
     }
     if ($true == 'F') {         	            	
        $eTO->type = FLAG_ERRORTO_ERROR;
        $eTO->description = "First Line of file ".basename($onlineFileProcessItem["file_being_processed"])."  ---  Principal No Not Located";
        $eTO->identifier = ET_SYSTEM;
        return $eTO;
     }    
     $ae = 0;
     $true = 'F';   
     while ($ae<count($fla) && $true == 'F') {
 	     if (TRIM($fla[$ae]) == "Planned Due Date") {
          	$true = 'T';
          	$PlannedDueDate = $ae;
       }
       $ae++;
     }
     if ($true == 'F') {         	            	
        $eTO->type = FLAG_ERRORTO_ERROR;
        $eTO->description = "First Line of file ".basename($onlineFileProcessItem["file_being_processed"])."  ---  Planned Due Date Not Located";
        $eTO->identifier = ET_SYSTEM;
        return $eTO;
     } 
      
    $docs=array();
    foreach($lineArr as $lno => $line){

      $fields=explode(",",$line);
      $docs[$fields[$SeqOrderNo]][] = $fields;

      // LINE STRUCTURE CHECKS
      if (sizeof($fields)<33) {
        $eTO->type = FLAG_ERRORTO_ERROR;
        $eTO->description = "invalid Column Count - expecting 33 columns!";
        return $eTO;
      }
    }
    $mfDPEM = $importDAO->getAllDepotPrincipalExportMapping(); // get all depot-principal-export-mapping
    $depotMap = $importDAO->getAllDepotPrincipalExportMapping($indexArray=ImportDAO::$PDP_EXP_MAP_INDEX_1);

    $lineCnt=1;
    
    foreach($docs as $key=>$doc){

    		$PrincipalNo = substr($doc[0][$SeqPrincipalNo],0,3);
    		if (substr($doc[0][$PlannedDueDate],2,1) == '/' && substr($doc[0][$PlannedDueDate],5,1) == '/') {
          $DueDate = substr($doc[0][$PlannedDueDate],6,4) . '-' . substr($doc[0][$PlannedDueDate],3,2) . '-' . substr($doc[0][$PlannedDueDate],0,2);
        } else {
          $DueDate = substr($doc[0][$PlannedDueDate],0,4) . '-' . substr($doc[0][$PlannedDueDate],5,2) . '-' . substr($doc[0][$PlannedDueDate],8,2);
        }
        $OrderNo = str_replace(substr($doc[0][$SeqOrderNo],0,3),'',$doc[0][$SeqOrderNo]);	

   			$Status  = trim($doc[0][$SeqStatus]);

    	  if($Status ==  'Cancelled'  )  {
 
           $postingDocumentUpdateTO = new PostingDocumentUpdateTO();

           // should really check this every line tho
              $postingDocumentUpdateTO->documentTypeUId = DT_ORDINV;
        
           // set the principal
              $onlineFileProcessingMapping=$importDAO->getMappingFromOIMByPrincipalIdentifier($onlineFileProcessItem["onlineFileProcessingMapping"], $PrincipalNo);
              if (empty($onlineFileProcessingMapping)) {
                $eTO->type = FLAG_ERRORTO_ERROR;

                $eTO->description = "Unknown Principal Type (".$PrincipalNo.") @line:".($key+1);
                $eTO->identifier = ET_CUSTOMER;
               return $eTO;
             }
         // this adaptor file can have multiple principals within file, so you cannot have null principal_uid
           if ($onlineFileProcessingMapping["principal_uid"]=="") {
                $eTO->type = FLAG_ERRORTO_ERROR;
                $eTO->description = "Incorrect Configuration : onlineFileProcessingMapping encountered blank principal_uid or not found!";
                $eTO->identifier = ET_SYSTEM;
                return $eTO;
           }

           $postingDocumentUpdateTO->createdDatetime = CommonUtils::getGMTime(0);
           $postingDocumentUpdateTO->incomingFilename = substr(basename($onlineFileProcessItem["file_being_processed"]),52,18);
      
           if (substr($doc[0][5],2,1) == '/' && substr($doc[0][5],5,1) == '/') {
              $postingDocumentUpdateTO->invoiceDate = $DueDate;
           } else {
              $postingDocumentUpdateTO->invoiceDate = $DueDate;
           }
      
           $postingDocumentUpdateTO->principalUId = $onlineFileProcessingMapping["principal_uid"];
      
           if ((!isset($depotMap[$postingDocumentUpdateTO->principalUId][$PrincipalNo])) || (trim($depotMap[$postingDocumentUpdateTO->principalUId][$PrincipalNo]["principal_code"])=="")) {
              $eTO->type = FLAG_ERRORTO_ERROR;
              $eTO->description = "Vital Depot mapping could not be determined for {$PrincipalNo}";
              $eTO->identifier = ET_SYSTEM;
              return $eTO;
           }
           $postingDocumentUpdateTO->depotUId=$depotMap[$postingDocumentUpdateTO->principalUId][$PrincipalNo]["depot_uid"];      
           $stripdocument = $OrderNo;
           $postingDocumentUpdateTO->documentNumber    = str_pad(str_replace('"','',$stripdocument),8,"0",STR_PAD_LEFT);
           $postingDocumentUpdateTO->updateTypeUid     = UPDATE_DOCUMENT_TYPE_POD_VIT;
           $postingDocumentUpdateTO->podReasonUId = 0;
           $postingDocumentUpdateTO->documentStatusUId = DST_CANCELLED;
//         print_r($postingDocumentUpdateTO);
           $arrTO[] = $postingDocumentUpdateTO;

           $lineCnt++;
        }    

    } // unique doc loop


    $eTO->type = FLAG_ERRORTO_SUCCESS;
    $eTO->description = "Successful";
    $eTO->object = $arrTO;
    return $eTO;

  }

  /********************************************************************************************************/
  function adaptorIMPERIAL_STOCK($content, $onlineFileProcessItem){

    global $ROOT, $PHPFOLDER, $importDAO;
    $arrTO = array();
    $eTO = new ErrorTO;
    $lineArr = explode("\n", trim(str_replace('"','',$content))); //linefeed newlines.
    
    // disregard the first line - headers
    
    if (trim(substr($lineArr[0],0,9))=="Warehouse") {
      unset($lineArr[0]);
    } else {
      $eTO->type = FLAG_ERRORTO_ERROR;
      $eTO->description = "First line expected to be a header!";
      return $eTO;
    }
    
    // Determine Principal UID from file name using mapping
    
    $PrincipalUid=$importDAO->getPrincipalUidFromMapping(substr($onlineFileProcessItem['file_being_processed'],strpos($onlineFileProcessItem['file_being_processed'], '.',1)-3,3), "IRL");
    if (empty($PrincipalUid)) {
          $eTO->type = FLAG_ERRORTO_ERROR;
          $eTO->description = "Unknown Principal (".substr($onlineFileProcessItem['file_being_processed'],strpos($onlineFileProcessItem['file_being_processed'], '.',1)-3,3).")";
          $eTO->identifier = ET_CUSTOMER;
          echo $eTO->description; 
          return $eTO;
    }
    
    $WarehouseUid = array();
    $ProductUid   = array();
    $ValidStckLine = 'N';   
    
    foreach($lineArr as $lno => $line){
        if (trim($line)=="") continue;
           $stockLine=explode(",",$line);
           // LINE STRUCTURE CHECKS
           if (sizeof($stockLine)!=19) {
                $eTO->type = FLAG_ERRORTO_ERROR;
                $eTO->description = "invalid Column Count - expecting 19 columns!";
                return $eTO;
           }           
       	   $ValidStckLine = 'Y';
       	   $openingBalance = $totalReceipts = $InvSales =$recdDamaged = $stkadjust = $customerReturns = 0;
           foreach($stockLine as $slineNo => $sline) {
               switch ($slineNo) {
                 case "0":
                       if($ValidStckLine == 'Y') {    
                          // Get Warehouse code for stock line
                          $WarehouseUid=$importDAO->getWarehouseUidFromMapping($PrincipalUid[0]['principal_uid'],$sline); 	
                          if (empty($WarehouseUid)) {
                               $eTO->type = FLAG_ERRORTO_ERROR;
                               $eTO->description = "Unknown Warehouse  (Principal -".substr($onlineFileProcessItem['file_being_processed'],strpos($onlineFileProcessItem['file_being_processed'], '.',1)-3,3). " Warehouse - ". $sline . ")";
                               $eTO->identifier = ET_CUSTOMER;
                               echo $eTO->description;
                               echo "<br>";
                               $ValidStckLine = 'N';
                          }     
                       }     
                       break;
                 case "2":
                       if($ValidStckLine == 'Y') {                          
                          // Get product detail for eack line
                          $ProductUid=$importDAO->getProductUidFromPrincipalProduct($PrincipalUid[0]['principal_uid'],$sline); 	
                          if (empty($ProductUid)) {
                               $eTO->type = FLAG_ERRORTO_ERROR;
                               $eTO->description = "Unknown Product -> Skipped (Principal ".substr($onlineFileProcessItem['file_being_processed'],strpos($onlineFileProcessItem['file_being_processed'], '.',1)-3,3). " Product - " .$sline .")";
                               $eTO->identifier = ET_CUSTOMER;
                               echo $eTO->description;
                               echo "<br>";
                               $ValidStckLine = 'N';
                          }
                       }   
                       break;
                 case "4":
                     // Opening Balance
                     if($ValidStckLine == 'Y') {  
                        $openingBalance = $sline;
                     }   
                     break;
                 case "5":
                     // Total Receipts
                     if($ValidStckLine == 'Y') {  
                        $totalReceipts = $sline;
                     }
                     break;
                 case "7":
                     // Issues Invoiced sales
                     $InvSales = $sline;
                     break;
                 case "6":
                     // Recd Damaged
                     $recdDamaged = $sline;
                     break;
                 case "8":
                     // Customer Returns
                     $customerReturns = $sline;
                     break;
                 case "9":
                     // Returns To Principal
                     $stkadjust = $stkadjust + $sline;
                     break;
                 case "10":
                     // Transfers In
                     $stkadjust = $stkadjust + $sline;
                     break;
                 case "11":
                     // Transfers Out
                     $stkadjust = $stkadjust + $sline;
                     break;
                 case "15":
                     // Stock Adj
                     $stkadjust = $stkadjust + $sline;
                     break;

                 default:
                      // Do Nothing
               }
           }
           if ($ValidStckLine == 'Y') { 
                	
                $PostingStockTO = new PostingStockTO();
                $PostingStockTO->stkUid                 = $ProductUid[0]['ProductUid'];
                $PostingStockTO->principalId            = $PrincipalUid[0]['principal_uid'];
                $PostingStockTO->depotId                = $WarehouseUid[0]['depot_uid'];
                $PostingStockTO->stockCode              = $ProductUid[0]['product_code'];
                $PostingStockTO->stockDescription       = $ProductUid[0]['product_description'];
                $PostingStockTO->opening                = $openingBalance;
                $PostingStockTO->arrivals               = $totalReceipts;
                $PostingStockTO->uplifts                = $recdDamaged;
                $PostingStockTO->returnsCancel          = $customerReturns;
                $PostingStockTO->delivered              = $InvSales;
                $PostingStockTO->adjustment             = $stkadjust;
                $PostingStockTO->closing                = $openingBalance  + 
                                                          $totalReceipts   + 
                                                          $customerReturns + 
                                                          $InvSales        + 
                                                          $recdDamaged     + 
                                                          $stkadjust;
                $dataGeneratedDate;                                          
                $arrTO[] = $PostingStockTO; 
                
           }
    } // End of Stock Line
    
    
    $eTO->type = FLAG_ERRORTO_SUCCESS;
    $eTO->description = "Successful";
    $eTO->object = $arrTO;
    return $eTO;
  }
  /********************************************************************************************************/
  function adaptorIMPERIAL_AVAIL_STOCK($content, $onlineFileProcessItem){

    global $ROOT, $PHPFOLDER, $importDAO;
    $arrTO = array();
    $eTO = new ErrorTO;
    $lineArr = explode("\n", trim(str_replace('"','',$content))); //linefeed newlines.
    
    // disregard the first line - headers
    
    echo "HH";
    
    if (trim(substr($lineArr[0],0,7))=="Company") {
      unset($lineArr[0]);
    } else {
      $eTO->type = FLAG_ERRORTO_ERROR;
      $eTO->description = "First line expected to be a header!";
      return $eTO;
    }
    $PrincipalUid = array();
    $WarehouseUid = array();
    $ProductUid   = array();
    $ValidStckLine = 'N';   
    foreach($lineArr as $lno => $line){
        if (trim($line)=="") continue;
           $stockLine=explode(",",$line);
           // LINE STRUCTURE CHECKS
           if (sizeof($stockLine)!=34) {
                $eTO->type = FLAG_ERRORTO_ERROR;
                $eTO->description = "invalid Column Count - expecting 34 columns!";
                return $eTO;
           }
           $ValidStckLine = 'Y';
           $osorders = 0;
           foreach($stockLine as $slineNo => $sline){

                   switch ($slineNo) {
                      case "0":
                         if($sline == "Code") {
                            $ValidStckLine = 'F'; 
                         }        	
                         	
                         if($ValidStckLine == 'Y') {
                            // Determine Principal UID from file name using mapping
                            $PrincipalUid=$importDAO->getPrincipalUidFromMapping(substr($onlineFileProcessItem['file_being_processed'],strpos($onlineFileProcessItem['file_being_processed'], '.',1)-3,3), "IRL");
                            if (empty($PrincipalUid)) {
                                 $eTO->type = FLAG_ERRORTO_ERROR;
                                 $eTO->description = "Unknown Principal (".substr($onlineFileProcessItem['file_being_processed'],strpos($onlineFileProcessItem['file_being_processed'], '.',1)-3,3).")";
                                 $eTO->identifier = ET_CUSTOMER;
                                 echo $eTO->description;
                                 echo "<br>"; 
                                 return $eTO;
                            }      
                         }
                         break;
                      case "3":                    
                         if($ValidStckLine == 'Y') {    
                            // Get Warehouse code for stock line
                            $WarehouseUid=$importDAO->getWarehouseUidFromMapping($PrincipalUid[0]['principal_uid'],$sline); 	
                            if (empty($WarehouseUid)) {
                                 $eTO->type = FLAG_ERRORTO_ERROR;
                                 $eTO->description = "Unknown Warehouse  (Principal -".substr($onlineFileProcessItem['file_being_processed'],strpos($onlineFileProcessItem['file_being_processed'], '.',1)-3,3). " Warehouse - ". $sline . ")";
                                 $eTO->identifier = ET_CUSTOMER;
//                                 echo $eTO->description;
//                                 echo "<br>";
                                 $ValidStckLine = 'N';
                            }     
                         }     
                         break;
                      case "4":
                         if($ValidStckLine == 'Y') {                          
                            // Get product detail for eack line
                            $ProductUid=$importDAO->getProductUidFromPrincipalProduct($PrincipalUid[0]['principal_uid'],$sline); 	
                            if (empty($ProductUid)) {
                                 $eTO->type = FLAG_ERRORTO_ERROR;
                                 $eTO->description = "Unknown Product -> Skipped (Principal ".substr($onlineFileProcessItem['file_being_processed'],strpos($onlineFileProcessItem['file_being_processed'], '.',1)-3,3). " Product - " .$sline .")";
                                 $eTO->identifier = ET_CUSTOMER;
//                                 echo $eTO->description;
//                                echo "<br>";
                                 $ValidStckLine = 'N';
                            }
                         }   
                         break;
                      case "18":
                         if($ValidStckLine == 'Y') {    
                            // O/S Orders
                            $osorders = $sline;
                         }   
                         break;
                      default:
                      // Do Nothing
                   }
           } // End of Stock Line
            if ($ValidStckLine == 'Y') { 
                // Check for stock record for Principal / WH / Product
                include_once($ROOT.$PHPFOLDER.'DAO/StockDAO.php');
                include_once($ROOT.$PHPFOLDER.'DAO/PostStockDAO.php');
           
                $StockDAO = new StockDAO($this->dbConn);
                $stockRecFound = $StockDAO->CheckForStockRecord($PrincipalUid[0]['principal_uid'], 
                                                                $WarehouseUid[0]['depot_uid'], 
                                                                $ProductUid[0]['ProductUid'] );
                                                                
//                print_r($stockRecFound);                                                
           
                If(count($stockRecFound) <> 0 ) {
                     $PostStockDAO = new PostStockDAO($this->dbConn);
                     $errorTO = $PostStockDAO->UpadteIRLallocations($osorders, $stockRecFound[0]['uid']);
                     if($errorTO->type==FLAG_ERRORTO_ERROR) {
                	       echo $errorTO->description;
                	       echo "<br>";  
                     }	    	
                } 
            } 
    }
           
    $eTO->type = FLAG_ERRORTO_SUCCESS;
    $eTO->description = "Successful";
    $eTO->object = $arrTO;
    return $eTO;
  }
  /********************************************************************************************************/
  function adaptorIRL_CREDITS($content, $onlineFileProcessItem){
// Credit Note file Array
// Array ( [0] => Array ( [0] => 000 [1] => 1 [2] => 6001553000008 [3] => HONEYFIELDS [4] => 20180815 [5] => 1141 [6] => 8748463 [7] => 423 [8] => [9] => [10] => ) ) 
// Array ( [0] => Array ( [0] => 050 [1] => 1 [2] => REJ [3] => 891230049 [4] => 295009 [5] => [6] => C02 [7] => 20180810 [8] => 308000 [9] => [10] => C02 [11] => 308000 [12] => MNTMSP [13] => 6001008604102 [14] => SPAECP [15] => [16] => 4280173396 [17] => [18] => [19] => 40253 [20] => [21] => [22] => 300075 [23] => 308000 [24] => 30844/441 ) ) 
// Array ( [0] => Array ( [0] => 051 [1] => 2005 [2] => -1 [3] => N [4] => -569.00 [5] => -654.35 [6] => 15 [7] => [8] => [9] => [10] => 28 [11] => 2005 [12] => 6005284000214 [13] => O [14] => [15] => 569.00000 [16] => 569.00000 [17] => 0 [18] => 0 [19] => 6005284000016 [20] => [21] => 569.00000 [22] => Honeyfields Sugar Cones 12's [23] => [24] => [25] => ) [1] => Array ( [0] => 051 [1] => 4075 [2] => -1 [3] => N [4] => -272.04 [5] => -312.85 [6] => 15 [7] => [8] => [9] => [10] => 12 [11] => 4075 [12] => 6005284005073 [13] => O [14] => [15] => 272.04000 [16] => 272.04000 [17] => 0 [18] => 0 [19] => 6005284005073 [20] => [21] => 272.04000 [22] => H/F Eezy Freezy Dips D/choc 250g [23] => [24] => [25] => ) [2] => Array ( [0] => 051 [1] => 4076 [2] => -1 [3] => N [4] => -272.04 [5] => -312.85 [6] => 15 [7] => [8] => [9] => [10] => 12 [11] => 4076 [12] => 6005284005264 [13] => O [14] => [15] => 272.04000 [16] => 272.04000 [17] => 0 [18] => 0 [19] => 6005284005097 [20] => [21] => 272.04000 [22] => H/F Eezy Freezy Caramel Crisp 250g [23] => [24] => [25] => ) [3] => Array ( [0] => 051 [1] => 9522 [2] => -1 [3] => N [4] => -479.00 [5] => -550.85 [6] => 15 [7] => [8] => [9] => [10] => 24 [11] => 9522 [12] => 6005284000238 [13] => O [14] => [15] => 479.00000 [16] => 479.00000 [17] => 0 [18] => 0 [19] => 6005284000023 [20] => [21] => 479.00000 [22] => Honeyfields Plain Baskets 12's [23] => [24] => [25] => ) ) Array ( [0] => Array ( [0] => 059 [1] => 6 ) ) Array ( [0] => Array ( [0] => 009 [1] => 8 ) ) 

    
    global $ROOT, $PHPFOLDER, $importDAO;
    $arrTO = array();
    $eTO = new ErrorTO;
    $lineArr = explode("\n", trim(str_replace('"','',$content))); //linefeed newlines.

    // disregard the first line - headers
    
    if (trim(substr($lineArr[0],0,3))=="000") {
//      unset($lineArr[0]);
    } else {
      $eTO->type = FLAG_ERRORTO_ERROR;
      $eTO->description = "First line expected to be a header!";
      return $eTO;
    }
    echo trim(basename($onlineFileProcessItem["file_being_processed"]));
    echo "<br>";
    $docs=array();
    foreach($lineArr as $lno => $line){
      if (trim($line)=="") continue;

      $fields=explode("ý",$line);
      $docs[$fields[0]][] = $fields;
    }

    $postingDocumentUpdateTO = new PostingDocumentUpdateTO();

    foreach($docs as $key=>$doc){
        // print_r($doc);    	
    	  if ($doc[0][0] == '050') {
           // Get the principal code from the file Name - trim(substr(basename($onlineFileProcessItem["file_being_processed"]),12,3))  
            $principalLookup = trim(substr(basename($onlineFileProcessItem["file_being_processed"]),12,3));
            $PrincipalArr=$importDAO->getPrincipalUidFromMapping($principalLookup, "IRL");
            if (empty($PrincipalArr)) {
                  $eTO->type = FLAG_ERRORTO_ERROR;
                  $eTO->description = "Unknown Principal (". $principalLookup .")";
                  $eTO->identifier = ET_CUSTOMER;
                  echo $eTO->description;
                  echo "<br>"; 
                  return $eTO;
             }
             
             $PrincipalUid = $PrincipalArr[0]['principal_uid'];

            // Get warehouse code fom original order for rejections
            
            if(trim($doc[0][2]) == 'REJ') {
                $warehouseArr=$importDAO->getDepotByDocument($PrincipalUid, str_pad($doc[0][8],8,"0",STR_PAD_LEFT));
                if (empty($warehouseArr)) {
                      $eTO->type = FLAG_ERRORTO_ERROR;
                      $eTO->description = "Unknown Warehouse (". str_pad($doc[0][8],8,"0",STR_PAD_LEFT) .")";
                      $eTO->identifier = ET_CUSTOMER;
                      echo $eTO->description;
                      echo "<br>"; 
                      return $eTO;
                }
                $postingDocumentUpdateTO->documentTypeUId = DT_CREDITNOTE;
            } else { 
                    $warehouseArr=$importDAO->getDepotByDocument($PrincipalUid,  str_pad($doc[0][8],8,"0",STR_PAD_LEFT));
                    if (empty($warehouseArr)) {
                         $eTO->type = FLAG_ERRORTO_ERROR;
                         $eTO->description = "Unknown Warehouse (".  str_pad($doc[0][8],8,"0",STR_PAD_LEFT) .")";
                         $eTO->identifier = ET_CUSTOMER;
                         echo $eTO->description;
                         echo "<br>"; 
                         return $eTO;
                    }
                    $postingDocumentUpdateTO->documentTypeUId = DT_MCREDIT_OTHER; 
            } 
            $warehouseUid = $warehouseArr[0]['depot_uid']; 
            $postingDocumentUpdateTO->createdDatetime = CommonUtils::getGMTime(0);
            $postingDocumentUpdateTO->incomingFilename = trim(basename($onlineFileProcessItem["file_being_processed"]));
            $postingDocumentUpdateTO->invoiceDate = substr($doc[0][7],0,4) . '-' . substr($doc[0][7],4,2) . '-' . substr($doc[0][7],6,2);
            $postingDocumentUpdateTO->principalUId = $PrincipalUid;
            $postingDocumentUpdateTO->invoiceNumber = '';

            $postingDocumentUpdateTO->depotUId=$warehouseUid;
      
            $postingDocumentUpdateTO->documentNumber = str_pad($doc[0][4],8,"0",STR_PAD_LEFT);
            $postingDocumentUpdateTO->invoiceNumber     = '';
            $postingDocumentUpdateTO->updateTypeUid     = UPDATE_DOCUMENT_TYPE_CORRECTION;
            $postingDocumentUpdateTO->documentStatusUId = DST_PROCESSED;
            $postingDocumentUpdateTO->sourceDocumentNumber=str_pad($doc[0][8],8,"0",STR_PAD_LEFT);

    	  } elseif ($doc[0][0] == '051') {
    	  	  foreach ($doc as $dtl) {
                  $postingDocumentUpdateDetailTO = new PostingDocumentUpdateDetailTO();
                  $ReasonArr=$importDAO->getIrlReturnReason(trim($dtl[3]));
                  if (empty($ReasonArr)) {
                      $podreason = '181';
                  } else {
                      $podreason = $ReasonArr[0]['uid'];
                  }
                  
                  $postingDocumentUpdateTO->podReasonUId = $podreason;
                  
                  $postingDocumentUpdateDetailTO = new PostingDocumentUpdateDetailTO();
                  $postingDocumentUpdateDetailTO->pageNo = "";
                  $postingDocumentUpdateDetailTO->lineNo = "";
                  $postingDocumentUpdateDetailTO->productCode  = trim($dtl[1]);
                  $postingDocumentUpdateDetailTO->orderedQty   = trim(trim($dtl[2]));;
                  $postingDocumentUpdateDetailTO->documentQty  = trim(trim($dtl[2]));
                  $postingDocumentUpdateDetailTO->deliveredQty = trim($dtl[2]);
                  $postingDocumentUpdateDetailTO->podReasonLookup = $podreason;
        
                  $postingDocumentUpdateTO->detailArr[] = $postingDocumentUpdateDetailTO;
            } 
// .....................................................................................................................................................................
        } elseif ($doc[0][0] == '340') {

                   // Array ( [0] => Array ( [0] => 000 [1] => 1 [2] => 6001553000008 [3] => CAPEHERBSPICE [4] => 20180824 [5] => 1451 [6] => 8808536 [7] => 36262 [8] => [9] => [10] => ) ) 
                   // Array ( [0] => Array ( [0] => 340 [1] => 1 [2] => 185358 [3] => 20180821 [4] => 307514 [5] => 332216 [6] => 148699 [7] => I [8] => 20180823 [9] => 14:51 [10] => [11] => C01 [12] => 5020 [13] => 0 [14] => 76381 [15] => 307514 [16] => 15286 [17] => 20180823 [18] => 132 [19] => 8.164 [20] => KG [21] => 25578.000000 [22] => [23] => 4 [24] => ) ) 
                   // Array ( [0] => Array ( [0] => 009 [1] => 3 ) )

        	
                  // Get the principal code from the file Name - trim(substr(basename($onlineFileProcessItem["file_being_processed"]),12,3))  
                  $principalLookup = trim(substr(basename($onlineFileProcessItem["file_being_processed"]),12,3));
                  $PrincipalArr=$importDAO->getPrincipalUidFromMapping($principalLookup, "IRL");
                  if (empty($PrincipalArr)) {
                       $eTO->type = FLAG_ERRORTO_ERROR;
                       $eTO->description = "Unknown Principal (". $principalLookup .")";
                       $eTO->identifier = ET_CUSTOMER;
                       echo $eTO->description;
                       echo "<br>"; 
                       return $eTO;
                  }
                  $PrincipalUid = $PrincipalArr[0]['principal_uid'];
                  // Get warehouse code fom original order
                  $warehouseArr=$importDAO->getDepotByDocument($PrincipalUid,  str_pad($doc[0][4],8,"0",STR_PAD_LEFT));
                  if (empty($warehouseArr)) {
                       $eTO->type = FLAG_ERRORTO_ERROR;
                       $eTO->description = "Unknown Warehouse (".  str_pad($doc[0][8],8,"0",STR_PAD_LEFT) .")";
                       $eTO->identifier = ET_CUSTOMER;
                       echo $eTO->description;
                       echo "<br>"; 
                       return $eTO;
                  } 
                  $warehouseUid = $warehouseArr[0]['depot_uid'];
        	
                  $postingDocumentUpdateTO = new PostingDocumentUpdateTO();
                  
                  $postingDocumentUpdateTO->documentTypeUId = DT_ORDINV;
                  $postingDocumentUpdateTO->createdDatetime = CommonUtils::getGMTime(0);
                  $postingDocumentUpdateTO->incomingFilename = trim(basename($onlineFileProcessItem["file_being_processed"]));
                  $postingDocumentUpdateTO->deliveryDate = substr($doc[0][8],0,4) . '-' . substr($doc[0][8],4,2) . '-' . substr($doc[0][8],6,2);
                  $postingDocumentUpdateTO->principalUId = $PrincipalUid;
                  $postingDocumentUpdateTO->depotUId=$warehouseUid;      
                  $postingDocumentUpdateTO->documentNumber    = str_pad($doc[0][4],8,"0",STR_PAD_LEFT);
                  $postingDocumentUpdateTO->updateTypeUid     = UPDATE_DOCUMENT_TYPE_POD_ULL;
                  $postingDocumentUpdateTO->documentStatusUId = DST_POD_SCANNED;

                  $arrTO[] = $postingDocumentUpdateTO;
// .....................................................................................................................................................................
        }    elseif (substr($doc[0][0],2,1) == '9') {
             $arrTO[] = $postingDocumentUpdateTO;
        }
    } // unique doc loop

    $eTO->type = FLAG_ERRORTO_SUCCESS;
    $eTO->description = "Successful";
    $eTO->object = $arrTO;
    return $eTO;

  }
  /********************************************************************************************************/
  function adaptorIMPERIAL_GRV($content, $onlineFileProcessItem){

    global $ROOT, $PHPFOLDER, $importDAO;
    $arrTO = array();
    $eTO = new ErrorTO;
    $lineArr = explode("\n", trim(str_replace('"','',$content))); //linefeed newlines.
    
    // disregard the first line - headers
    
    if (trim(substr($lineArr[0],0,7))=="Company") {
      unset($lineArr[0]);
    } else {
      $eTO->type = FLAG_ERRORTO_ERROR;
      $eTO->description = "First line expected to be a header!";
      return $eTO;
    }
    
    // Determine Principal UID from file name using mapping
    
    foreach($lineArr as $lno => $line){
    	
    	  $docnoStore="";
    	
        if (trim($line)=="") continue;
           $grvLine=explode(",",$line);
           // LINE STRUCTURE CHECKS
           if (sizeof($grvLine)!=24) {
                $eTO->type = FLAG_ERRORTO_ERROR;
                $eTO->description = "invalid Column Count - expecting 24 columns! " . sizeof($grvLine);
                return $eTO;
           } 
           
 //          print_r($grvLine);
           if($docnoStore <> $grvLine[18]) {
           	
           	      $postingDocumentUpdateTO = new PostingDocumentUpdateTO();

                  $postingDocumentUpdateTO->documentTypeUId = DT_ORDINV;
        
                  // set the principal
                  $PrincipalArr=$importDAO->getPrincipalUidFromMapping(trim($grvLine[0]), "IRL");

                  
                    if (empty($PrincipalArr)) {
                          $eTO->type = FLAG_ERRORTO_ERROR;

                          $eTO->description = "Unknown Principal Type (".trim($grvLine[0]) . " )";
                          $eTO->identifier = ET_CUSTOMER;
                          return $eTO;
                  }
                  // this adaptor file can have multiple principals within file, so you cannot have null principal_uid

                  $postingDocumentUpdateTO->createdDatetime = CommonUtils::getGMTime(0);
                  $postingDocumentUpdateTO->incomingFilename = substr(basename($onlineFileProcessItem["file_being_processed"]),52,18);

                  $postingDocumentUpdateTO->invoiceDate = '20' . substr($grvLine[20],6,2) . '-' . substr($grvLine[20][5],3,2) . '-' . substr($grvLine[20],0,2);
      
                  $postingDocumentUpdateTO->principalUId = $PrincipalArr[0]['principal_uid'];
      
                  $stripdocument = trim($grvLine[18]);
                  $postingDocumentUpdateTO->documentNumber    = str_pad(str_replace('"','',$stripdocument),8,"0",STR_PAD_LEFT);
                  $postingDocumentUpdateTO->updateTypeUid     = UPDATE_DOCUMENT_TYPE_POD_ULL;
      
                  if($dispatchedVariance == 0)  {
                         $postingDocumentUpdateTO->documentStatusUId = DST_DELIVERED_POD_OK;
                  } else {
                         $postingDocumentUpdateTO->documentStatusUId = DST_DIRTY_POD;
                  }		
                  $postingDocumentUpdateTO->podReasonUId = 0;

                  $arrTO[] = $postingDocumentUpdateTO;
                  
                  $dispatchedVariance = 0;
                  $docnoStore = $grvLine[18];
           }
           
           $dispatchedVariance = $dispatchedVariance + $grvLine[14];           	
    } 
    $eTO->type = FLAG_ERRORTO_SUCCESS;
    $eTO->description = "Successful";
    $eTO->object = $arrTO;
    return $eTO;
  }
  /********************************************************************************************************/
  function adaptorIMPERIAL_SCANS($content, $onlineFileProcessItem){

    global $ROOT, $PHPFOLDER, $importDAO;
    $arrTO = array();
    $eTO = new ErrorTO;
    $lineArr = explode("\n", trim(str_replace('"','',$content))); //linefeed newlines.
    
    // disregard the first line - headers
    
    echo trim(substr($lineArr[0],0,3));
    
    if (trim(substr($lineArr[0],0,3))=="W/H") {
      unset($lineArr[0]);
    } else {
      $eTO->type = FLAG_ERRORTO_ERROR;
      $eTO->description = "First line expected to be a header!";
      return $eTO;
    }
    
    // Determine Principal UID from file name using mapping
    
    foreach($lineArr as $lno => $line){
     	
        if (trim($line)=="") continue;
           $grvLine=explode(",",$line);
           // LINE STRUCTURE CHECKS
           if (sizeof($grvLine)!=7) {
                $eTO->type = FLAG_ERRORTO_ERROR;
                $eTO->description = "invalid Column Count - expecting 7 columns! " . sizeof($grvLine);
                return $eTO;
           } 
           
                      echo '<pre>';
           print_r($grvLine);
           echo '</pre>';
           
           
           

           // set the principal

                $PrincipalArr=$importDAO->getPrincipalUidFromMapping(substr(basename($onlineFileProcessItem['file_being_processed']),17,3), "IRL");
           if (empty($PrincipalArr)) {
                      $eTO->type = FLAG_ERRORTO_ERROR;
                      $eTO->description = "Unknown Principal Type (".trim($grvLine[0]) . " )";
                      $eTO->identifier = ET_CUSTOMER;
                      return $eTO;
           }
           $stripdocument = trim($grvLine[3]);
                
           if($stripdocument >= 300000 && $stripdocument <= 330000 ) {
                
           
                $postingDocumentUpdateTO = new PostingDocumentUpdateTO();
                $postingDocumentUpdateTO->documentTypeUId = DT_ORDINV;
                $postingDocumentUpdateTO->createdDatetime = CommonUtils::getGMTime(0);
                $postingDocumentUpdateTO->incomingFilename = basename($onlineFileProcessItem["file_being_processed"]);
                $postingDocumentUpdateTO->deliveryDate = '20' . substr($grvLine[4],6,2) . '-' . substr($grvLine[4],3,2) . '-' . substr($grvLine[4],0,2);
                $postingDocumentUpdateTO->principalUId = $PrincipalArr[0]['principal_uid'];           
                // Get warehouse code fom original order
                
           
                $warehouseArr=$importDAO->getDepotByDocument($PrincipalArr[0]['principal_uid'],  str_pad($stripdocument,8,"0",STR_PAD_LEFT));
                if (empty($warehouseArr)) {
                       $eTO->type = FLAG_ERRORTO_ERROR;
                       $eTO->description = "Unknown Warehouse (".  str_pad($stripdocument,8,"0",STR_PAD_LEFT) .")";
                       $eTO->identifier = ET_CUSTOMER;
                       echo $eTO->description;
                       echo "<br>"; 
                       return $eTO;
                } 
                $warehouseUid = $warehouseArr[0]['depot_uid'];
                $postingDocumentUpdateTO->depotUId=$warehouseUid; 
           
                $postingDocumentUpdateTO->documentNumber    = str_pad(str_replace('"','',$stripdocument),8,"0",STR_PAD_LEFT);
                $postingDocumentUpdateTO->updateTypeUid     = UPDATE_DOCUMENT_TYPE_POD_ULL;
                $postingDocumentUpdateTO->documentStatusUId = DST_POD_SCANNED;
           
                $postingDocumentUpdateTO->podReasonUId = 0;
                
           
                
                           echo '<pre>';
                print_r($postingDocumentUpdateTO);
                echo '</pre>';
                
                
                
                
           
                $arrTO[] = $postingDocumentUpdateTO;
           } 
    } 
    $eTO->type = FLAG_ERRORTO_SUCCESS;
    $eTO->description = "Successful";
    $eTO->object = $arrTO;
    return $eTO;
  }
  /********************************************************************************************************/
   function adaptorGDS_INV($content, $onlineFileProcessItem){

    global $ROOT, $PHPFOLDER, $importDAO;
    $arrTO = array();
    $eTO = new ErrorTO;
    $lineArr = explode("\n", trim(str_replace('"','',$content))); //linefeed newlines.
    $ullCnfArr = array();

    // disregard the first line - headers
    
    print_r($lineArr);
    echo "<br>";
    echo (substr($lineArr[0],3,20));
    echo "<br>";
    die;
    
    if (trim(substr($lineArr[0],3,7))=="Princip") {
      unset($lineArr[0]);
    } else {
      $eTO->type = FLAG_ERRORTO_ERROR;
      $eTO->description = "First line expected to be a header!";
      return $eTO;
    }

    $docs=array();
    foreach($lineArr as $lno => $line){
      if (trim($line)=="") continue;

      $fields=explode(",",$line);
      $docs[$fields[0]."-".$fields[2]][] = $fields;

      // LINE STRUCTURE CHECKS
      if (sizeof($fields)!=16) {
        $eTO->type = FLAG_ERRORTO_ERROR;
        $eTO->description = "invalid Column Count - expecting 16 columns!";
        return $eTO;
      }

    }

  }




}