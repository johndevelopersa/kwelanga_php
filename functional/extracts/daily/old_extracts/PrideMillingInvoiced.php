<?Php

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
class PrideMillingInvoiced {
  public static function generateOutput(){
    $className = basename(__FILE__,'.php').'Init';
    global $ROOT, $PHPFOLDER, $dbConn;
    $obj = new $className();
    return $obj->generateOutput();
  }
}
class PrideMillingInvoicedInit extends extractController {

  private $principalUid = 273; //uid of principal extract.
  private $filename = 'INV273[@FSEQ].csv';  //main controller will build full filename with seq. for us.
  private $crnFilename = 'CRN273[@FSEQ].csv';  //credit note filename


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

    // Create new log entries into SMART_EVENT - This doesn't rely on a notification trigger
    if (!$this->skipInsert) {
      $rTO = $this->postExtractDAO->queueAllInvoiced($this->principalUid, $recipientUId, $inclCancelled = false);  //use the loaded receipientUID and not the notification type... *** same as document confirmations***
      if ($rTO->type != FLAG_ERRORTO_SUCCESS) {
        BroadcastingUtils::sendAlertEmail("System Error", "Export failed to call postExtractDAO->queueAllInvoiced in ".get_class($this)." " . $rTO->description, "Y");
      } else {
        $this->dbConn->dbinsQuery("commit;");
      }
         //credits and debit notes
      $rTO = $this->postExtractDAO->queueAllCreditsAndDebits($this->principalUid, $recipientUId, array(DT_CREDITNOTE, DT_DEBITNOTE, DT_MCREDIT_DAMAGES, DT_MCREDIT_OTHER, DT_MCREDIT_PRICING, DT_MCREDIT_PROMOTIONS, DT_MDEBIT_NOTE, DT_MCREDIT_STORE));  //use the loaded receipientUID and not the notification type... *** same as document confirmations***
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
      $dmUIds[] = $r["dm_uid"];
    }
    
    if (sizeof($psms)>0) {
      $sfvals_PA = $this->miscDAO->getPrincipalSpecialFieldValuesMultEntities($this->principalUid, 320, implode(",",$psms), CT_STORE_SHORTCODE, $arrayIndex="entity_uid");
      $sfvals_RG = $this->miscDAO->getPrincipalSpecialFieldValuesMultEntities($this->principalUid, 321, implode(",",$psms), CT_STORE_SHORTCODE, $arrayIndex="entity_uid");

    }

    foreach($grpDocs as $type => $orders){  // Start of document type

       $dataArr = array();
       $errorSEUIdArr = array();
       $successSEUIdArr = array();
       $successCount = 0;

       foreach($orders as $ord) {         // Start of Documents
     	
          /*-------------------------------------------------*/
          /*            START BUILDING OUTPUT
          /*-------------------------------------------------*/
          $region = (isset($depotTrans[$ord[0]['depot_uid']])) ? $depotTrans[$ord[0]['depot_uid']] : false;

          if((empty($sfvals_PA[$ord[0]["principal_store_uid"]]['value'])) ) {  //has no special field and/or blank...
             $errorSEUIdArr[] = $ord[0]['se_uid']; //list of smart event errors
          } else {

            $successCount++;
            $successSEUIdArr[] = $ord[0]['se_uid']; //list of smart event success
            $storeAcc = trim($sfvals_PA[$ord[0]["principal_store_uid"]]['value']);
            $prideRegion = trim($sfvals_RG[$ord[0]["principal_store_uid"]]['value']);

            $reacde = '00';
            switch (($ord[0]['reason_uid'])) {          /*-----Begin Case-----*/
              case  '1':	
                    $reacde = '06';
                    break;	            	
              case  '3':	
                    $reacde = '15';
                    break;
              case  '4':	
                    $reacde = '05';
                    break;
              case  '6':	
                    $reacde = '10';
                    break;         
              case  '7':	
                    $reacde = '14';
                    break;          
              case  '10':	
                    $reacde = '11';
                    break;          
              case  '11':	
                    $reacde = '14';
                    break;          
              case  '16':	
                    $reacde = '14';
                    break;          
              case  '17':	
                    $reacde = '14';
                    break;          
              case  '22':	
                    $reacde = '05';
                    break;          
              case  '64':	
                    $reacde = '14';
                  break;         
              case  '65':	
                    $reacde = '14';
                    break;          
              case  '66':	
                    $reacde = '14';
                    break;          
              case  '67':	
                    $reacde = '06';
                    break;          
              case  '68':	
                    $reacde = '14';
                     break;         
              case  '69':	
                    $reacde = '14';
                    break;          
              case  '70':	
                    $reacde = '06';
                    break;          
              case  '71':	
                     $reacde = '14';
                    break;          
              case  '72':	
                     $reacde = '14';
                    break;         
              case  '73':	
                     $reacde = '14';
                    break;         
              case  '74':	
                     $reacde = '14';
                    break;          
              case  '75':	
                     $reacde = '07';
                     break;         
              case  '76':	
                     $reacde = '14';
                    break;          
              case  '77':	
                     $reacde = '16';
                    break;          
              case  '78':	
                     $reacde = '16';
                    break;          
              case  '79':	
                     $reacde = '14';
                    break;          
              case  '99':	
                     $reacde = '11';
                    break;          
              case  '100':	
                     $reacde = '14';
                    break;          
              case  '102':	
                     $reacde = '12';
                    break;         
              case  '103':	
                     $reacde = '14';
                    break;          
              case  '105':	
                     $reacde = '15';
                    break;                    
              case  '106':	
                     $reacde = '14';
                    break;         
              case  '107':	
                     $reacde = '04';
                    break;                    
              case  '108':	
                     $reacde = '14';
                    break;          
              case  '109':	
                     $reacde = '12';
                    break;          
              case  '110':	
                     $reacde = '06';
                    break;          
              case  '111':	
                     $reacde = '06';
                    break;          
              case  '112':	
                     $reacde = '14';
                    break;          
              case  '113':	
                     $reacde = '14';
                    break;          
              case  '114':	
                     $reacde = '06';
                    break;          
              case  '115':	
                     $reacde = '05';
                    break;          
              case  '116':	
                     $reacde = '11';
                    break;
              default:  
                     $reacde = '00';          
            }                                           /*-----End Case-------*/
            /* FILE HEADER */
            //array containing list of row values
            /*  Credit Note H,500001,ALF0004,150820,35-910,TEST ORDER,000001,06,ALF0004,900001,,500001-35-910
              Invoice     H,000001,ALF0004,150820,35-910,TEST ORDER,000001,  ,ALF0004,500001,,000001-35-910
              
              Header     
              ‘H’	1	Constant - Header
               Document / Sales Order No on Invoice  -  Source Document Number on Credit Note	8	Character 
               Pride M Customer Number	15	Character
               Document Date	6	Date (yymmdd)
               Pride M Region Code	6	Character
               Customer PO Number	20	Character
               Document / Sales Order No on Invoice
               Source Document Number on Credit Note	8	Character - Blank on Invoice
               Reason Description on Credit Note 	20 	Character
               Store Branch Code	10	Character
               Sequential Invoice Number (on Invoice) - Sequential Credit Note Number (on Credit Note)	8	Character
               Space	1	Character
               Region – Document Number	8	Character
             */

            $rowArr = array();
            $rowArr[] = '"H"';

            if($type == 'i'){
              $rowArr[] = str_pad(substr($ord[0]['document_number'],-6), 6, 0, STR_PAD_LEFT);
            } else {
              $rowArr[] = str_pad(substr($ord[0]['source_document_number'],-6), 6, 0, STR_PAD_LEFT);
            }            
            $rowArr[] = $storeAcc;  //CUSTOMER CODE - GP Account.
             $rowArr[] = '"'.date("Y/m/d", strtotime($ord[0]["invoice_date"])).'"';  //DATE (YYYY/MM/DD)
             $rowArr[] = $prideRegion;  // Pride M Region Code	6	Character
             $ponum = trim(str_replace(array('"',"'"),array('',''),$ord[0]["customer_order_number"]));
             $rowArr[] = $ponum ;   
             if($type == 'i'){
               $rowArr[] = "";
               $rowArr[] = "";
             } else {
               $rowArr[] = str_pad(substr($ord[0]['source_document_number'],-7), 7, 0, STR_PAD_LEFT);
               $rowArr[] = $reacde;   // Reason Description on Credit Note 	20
             }          
             $rowArr[] = $storeAcc; // Store Branch Code	10	Character
             if($type == 'i'){
               $rowArr[] = 'IU35-'. str_pad(substr($ord[0]['invoice_number'],-7), 7, 0, STR_PAD_LEFT); // Sequential Invoice Number (on Invoice)
             } else {
               $rowArr[] = 'CU35-'. str_pad(substr($ord[0]['alternate_document_number'],-7), 7, 0, STR_PAD_LEFT); //Sequential Credit Note Number (on Credit Note)
             }            
             $rowArr[] = "";
             $rowArr[] = $storeAcc;
             $dataArr[] = join(',',$rowArr);

             foreach($ord as $d){ //detail rows.

                if(abs($d['document_qty'])>0){

                 /* Record  - D,925102,10,200.12
                 Details     
                 ‘ D’	1	Constant – Detail
                   Product code 	15	Character
                   Quantity	8	Number (8)
                   Net Price 	6	Number (6,2)
                 */
                  $detArr = array();
                  $detArr[] = '"D"';
                  $detArr[] = trim(str_replace(array('"'),array(''),$d['product_code'])); // Product code
                  $detArr[] = abs($d['document_qty']); //Quantity
                  $detArr[] = number_format(abs(round($d['net_price'], 2)), 2, '.', ''); // Net Price 
                  $dataArr[] = join(',',$detArr);
                }
            } //eo detail
          }   //eo special field check
       } //eo documents
            
       $data = join("\r\n",$dataArr);
       //create file only if there are successful items.
       $filePath = false;
       if(count($successSEUIdArr)>0){

          //determine seq.
          $seqFilename = $this->setFilenameFSEQ((($type=='i')?$this->filename:$this->crnFilename), $this->principalUid, false, 3, self::setFilenameFSEQ_LenType_PAD);
          if($seqFilename==false){
            BroadcastingUtils::sendAlertEmail("System Error", "Sequence method setFilenameFSEQ failed in ".get_class($this)."!", "Y");
            return $this->errorTO;
          }

          //write physical file
          $filePath = $this->createFile($folder, $seqFilename, $data);  //places file in correct folder
          if($filePath == false){
            BroadcastingUtils::sendAlertEmail("System Error", "Failed to create extract file!", "Y");
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description = "Failed in ".get_class($this)." on file creation";
            return $this->errorTO;
          }
          //create actual file to local smollan folder.
          $fp = file_put_contents(DIR_DATA_SURESERVER_NON_FTP_FROM . 'pride/' . $seqFilename, $data);
          if($fp != strlen($data)){
            BroadcastingUtils::sendAlertEmail("System Error", "Failed to create extract file!", "Y");
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description = "Failed in ".get_class($this)." on actual file creation";
            return $this->errorTO;
          }
       }
       // SETUP DISTRIBUTION
        $postingDistributionTO = new PostingDistributionTO;
        $postingDistributionTO->DMLType = "INSERT";
        $postingDistributionTO->deliveryType = BT_EMAIL;
        $postingDistributionTO->subject = (($type=='i')?$this->getTemplateInvoiceSubject():$this->getTemplateCreditSubject()); //might have no filename if all errors therefore don't display on subject line...
        $postingDistributionTO->body = $this->getTemplateBody($principalName, $successCount, count($errorSEUIdArr), $this->getManagementURL($this->principalUid));
        if($filePath!=false){
          $postingDistributionTO->attachmentFile = $filePath;
        }

        $recipientList = explode(",", $reArr[0]["user_uid_list"]); //if blank, sizeof() will still be 1
        $recipientsCheckCount = 0;  //check if atleast one distribution item was posted, in          case of invalid recipients

        foreach($recipientList as $re){

          $mfC = $this->miscDAO->getContactItem($this->principalUid, "", $re);
          if (sizeof($mfC)==0) {
            BroadcastingUtils::sendAlertEmail("System Error",get_class($this)." Extract for nr.UID {$recipientUId} has an invalid Recipient/Contact: '{$re}'.","Y", true);
            continue;
          }

          $postingDistributionTO->destinationAddr = $mfC[0]["email_addr"];
          $dResult = $this->postDistributionDAO->postQueueDistribution($postingDistributionTO);
          if ($dResult->type!=FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->type=FLAG_ERRORTO_ERROR;
            $this->errorTO->description = get_class($this)." Extract distribution queue failed for for nr.UID {$recipientUId} and Recipient/Contact: '{$re}'.";
            BroadcastingUtils::sendAlertEmail("System Error", $this->errorTO->description, "Y", true);
            return $this->errorTO;
          } else {
            $recipientsCheckCount++;  //successful
          }
        }

        if ($recipientsCheckCount==0) {
          $this->errorTO->type = FLAG_ERRORTO_ERROR;
          $this->errorTO->description = "Failed in ".get_class($this)." extract no valid Recipient/Contact found, no outgoing mail generated!";
          BroadcastingUtils::sendAlertEmail("Error in ".get_class($this)." extract", $this->errorTO->description, "Y", false);
          return $this->errorTO;
        }
        
        /*
         *  UPDATE SMART EVENT in BULK
         */
        //SUCCESSFUL ITEMS
        if (sizeof($successSEUIdArr) > 0) {
           $bIResult = $this->postBIDAO->setSmartEventStatusBulk(implode(",", $successSEUIdArr), basename($filePath), "");
           if ($bIResult->type != FLAG_ERRORTO_SUCCESS) {
             $this->errorTO->type = FLAG_ERRORTO_ERROR;
             $this->errorTO->description = "Failed in ".get_class($this)." extract on setting success setSmartEventStatusBulk with {$bIResult->description}";
             BroadcastingUtils::sendAlertEmail("Error in ".get_class($this)." extract", $this->errorTO->description, "Y", false);
             return $this->errorTO;
           }
        }
        //ERROR EXTRACTS - MARK SE AS "E", for extract errors display screen.
        if (sizeof($errorSEUIdArr) > 0) {
          $bIResult = $this->postBIDAO->setSmartEventStatusBulk(implode(",", $errorSEUIdArr), "289", "", FLAG_ERRORTO_ERROR);
          if ($bIResult->type != FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description = "Failed in ".get_class($this)." on setting errror setSmartEventStatusBulk with {$bIResult->description}";
            BroadcastingUtils::sendAlertEmail("Error in ".get_class($this)." extract", $this->errorTO->description, "Y", false);
            return $this->errorTO;
          }
        }  
    }     // End of document type
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