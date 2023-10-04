<?php

/* * ********************************************************************************************
 * *
 * *  This job can run as many times per day as is necessary according to job scheduler.
 * *
 * *  It executes notifications that occur throughout the day by triggers
 * *
 * ******************************************************************************************** */

include_once('ROOT.php');
include_once($ROOT . 'PHPINI.php');
include_once($ROOT . $PHPFOLDER . "functional/extracts/daily/extractController.php");


//static method handler.
class JumboBrandsInvoiced {
  public static function generateOutput(){
    $className = basename(__FILE__,'.php').'Init';
    global $ROOT, $PHPFOLDER, $dbConn;
    $obj = new $className();
    return $obj->generateOutput();
  }
}

class JumboBrandsInvoicedInit extends extractController {
	
  private $principalUid = 347; //uid of principal extract.
  private $filename = "H_[@DTE]_[@FSEQ].csv"; //main controller will build full filename with seq. for us.
  
 

  public function generateOutput(){

    global $ROOT, $PHPFOLDER;

    //name in email and folder to place bkup files.
    $pArr = $this->principalDAO->getPrincipalItem($this->principalUid);
    if (count($pArr)==0) {
      BroadcastingUtils::sendAlertEmail("System Error", "Extract failed to load principal item in ".get_class($this)."!", "Y");
      return $this->errorTO;
    }
    $principalName = $pArr[0]['principal_name'];
    $folder = $this->principalUid . '_' . explode(' ',  strtolower($pArr[0]['principal_name']))[0]; //folder replaced with principal id + first WORD of principal.


    //use the receipients listed in the notification table instead of hard coding them!!!
    //expecting only one row loaded per principal extract
    $reArr = $this->bIDAO->getNotificationRecipients($this->principalUid, NT_DAILY_EXTRACT_CUSTOM);
    if (count($reArr)==0) {
      BroadcastingUtils::sendAlertEmail("System Error", "Extract failed load recipients in ".get_class($this)."!", "Y");
      return $this->errorTO;
    }
    $recipientUId = $reArr[0]['uid'];

    if (!$this->skipInsert) {
      // Create new log entries into SMART_EVENT - This doesn't rely on a notification trigger
      $rTO = $this->postExtractDAO->queueAllInvoiced($this->principalUid, $recipientUId, $inclCancelled = false);  //use the loaded receipientUID and not the notification type... *** same as document confirmations***
      if ($rTO->type != FLAG_ERRORTO_SUCCESS) {
        BroadcastingUtils::sendAlertEmail("System Error", "Export failed to call postExtractDAO->queueAllInvoiced in ".get_class($this)." " . $rTO->description, "Y");
      } else {
        $this->dbConn->dbinsQuery("commit;");
      }
      //credits and debit notes
      $rTO = $this->postExtractDAO->queueAllCreditsAndDebits($this->principalUid, $recipientUId, array(DT_CREDITNOTE));  //use the loaded receipientUID and not the notification type... *** same as document confirmations***
      if ($rTO->type != FLAG_ERRORTO_SUCCESS) {
        BroadcastingUtils::sendAlertEmail("System Error", "Export failed to call postExtractDAO->queueAllCreditsAndDebits in ".get_class($this)." " . $rTO->description, "Y");
      } else {
        $this->dbConn->dbinsQuery("commit;");
      }
    }

    $seDocs = $this->extractDAO->getDailyExtractInvoicedOrders($this->principalUid, $recipientUId);

    /*  SUCCESS POINT - 1  */
    //nothing to do...
    if(count($seDocs)==0){
      echo "Successfully Completed Extract : ".get_class($this)." - No entries!<br>";
      $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
      $this->errorTO->description = "Successful";
      return $this->errorTO;
    }

    //group array
    $grpDocs = array();
    $psms=array();
    foreach($seDocs as $k=>$r){

      $type = 'i';
      if ($r['document_type_uid'] == DT_CREDITNOTE){
        $type = 'c';
      }
      $grpDocs[$type][$r['dm_uid']][] = $r;
      $psms[$r["principal_store_uid"]] = $r["principal_store_uid"];
    }
    
    foreach($grpDocs as $type => $orders){
    	
      $errorSEUIdArr = array();
      $successSEUIdArr = array();
      $successCount = 0;
 
      foreach($orders as $ord){
 ;

          /*-------------------------------------------------*/
          /*            START BUILDING OUTPUT
          /*-------------------------------------------------*/

          $dataArr = array();
          $successCount++;
          $successSEUIdArr[] = $ord[0]['se_uid']; //list of smart event success

          foreach($ord as $d){ //detail rows.
              $detArr = array();
              $detArr[] = $d['invoice_date'];              
              if($type == 'i') {
                 $detArr[] = $d['client_document_number'];
              } elseif ($type == 'c') {
                 $detArr[] = ltrim($d['alternate_document_number'],'0');
              }
              
              if(trim($d['outer_casing_gtin'])== '') {
                  $detArr[] = trim(str_replace(array('"','\\',"\t","\n","\r"),array('','','','',''),$d['product_code'])); 
              } else {
                  $detArr[] = trim(str_replace(array('"','\\',"\t","\n","\r"),array('','','','',''),$d['outer_casing_gtin']));  	
              }
                      //  Product code
              
              if($type == 'i' && ltrim($d['document_number'],'0') != substr($d['client_document_number'],2,7)) {
                    $detArr[] = $d['document_qty'];
                    $detArr[] = $d['document_number'];	
              } elseif ($type == 'c') {
              	    $detArr[] = $d['document_qty'];
                    $detArr[] = $d['source_document_number']; 	
              } else {
              	$detArr[] = $d['document_qty'];
              	$detArr[] = '';
              }              
              $dataArr[] = join(',',$detArr);
          } 
              
          $data = join("\r\n",$dataArr);
          
          //create file only if there are successful items.
          $filePath = false;
          if(count($successSEUIdArr)>0){
             //determine seq.
             $seqFilename = $this->setFilenameFSEQ($this->filename, $this->principalUid, false, 3, self::setFilenameFSEQ_LenType_PAD);
             if($seqFilename==false){
                 BroadcastingUtils::sendAlertEmail("System Error", "Sequence method setFilenameFSEQ failed in ".get_class($this)."!", "Y");
                 return $this->errorTO;
             }
             //write physical file
             if($type == 'i') {
                  $filePath = $this->createFile($folder, $d['client_document_number'] . '.csv', $data);  //places file in correct folder	
             } elseif ($type == 'c') {
                 $filePath = $this->createFile($folder, 'CRN' . ltrim($d['alternate_document_number'],'0') . '.csv', $data);  //places file in correct folder
             }              
             if($filePath == false){
                 BroadcastingUtils::sendAlertEmail("System Error", "Failed to create extract file!", "Y");
                 $this->errorTO->type = FLAG_ERRORTO_ERROR;
                 $this->errorTO->description = "Failed in ".get_class($this)." on file creation";
                return $this->errorTO;
             }
             //create actual file to local HOB folder.
             
             if($type == 'i') {
                 $fp = file_put_contents(DIR_DATA_NON_FTP_FROM . 'ftp/jumbo/in/' . $d['client_document_number'] . '.csv', $data);
             } elseif ($type == 'c') {
             	   $fp = file_put_contents(DIR_DATA_NON_FTP_FROM . 'ftp/jumbo/in/' . 'CRN' . ltrim($d['alternate_document_number'],'0') . '.csv', $data);
             }
             if($fp != strlen($data)){
                  BroadcastingUtils::sendAlertEmail("System Error", "Failed to create extract file!", "Y");
                  $this->errorTO->type = FLAG_ERRORTO_ERROR;
                 $this->errorTO->description = "Failed in ".get_class($this)." on actual file creation";
                 return $this->errorTO;
             }
          }
          
      } //eo documents

      /*
       *  UPDATE SMART EVENT in BULK
       */
      //SUCCESSFUL ITEMS
      if (sizeof($successSEUIdArr) > 0) {
        $bIResult = $this->postBIDAO->setSmartEventStatusBulk(implode(",", $successSEUIdArr), 'File per Invoice', "");
        if ($bIResult->type != FLAG_ERRORTO_SUCCESS) {
          $this->errorTO->type = FLAG_ERRORTO_ERROR;
          $this->errorTO->description = "Failed in ".get_class($this)." extract on setting success setSmartEventStatusBulk with {$bIResult->description}";
          BroadcastingUtils::sendAlertEmail("Error in ".get_class($this)." extract", $this->errorTO->description, "Y", false);
          return $this->errorTO;
        }
      }
    }
    /*-------------------------------------------------*/
    echo "Successfully Completed Extract : ".get_class($this)."<br>";

    /*  SUCCESS POINT - 2  */
    $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
    $this->errorTO->description = "Successful";
    return $this->errorTO;

  }
}

//direct run!
if ($runMe) {
  directRunExtract(__FILE__);
}

?>


