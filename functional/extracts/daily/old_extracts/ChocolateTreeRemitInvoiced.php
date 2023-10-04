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
class ChocolateTreeRemitInvoiced {
  public static function generateOutput(){
    $className = basename(__FILE__,'.php').'Init';
    global $ROOT, $PHPFOLDER, $dbConn;
    $obj = new $className();
    return $obj->generateOutput();
  }
}

echo "I am here";

class ChocolateTreeRemitInvoicedInit extends extractController {

  private $principalUid = 33; //uid of principal extract.
  private $filename = 'RM33[@FSEQ].csv';  //main controller will build full filename with seq. for us.
  

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
    $reArr = $this->bIDAO->getNotificationRecipients($this->principalUid, NT_DAILY_EXTRACT_ALTCUSTOM3);
    if (count($reArr)==0) {
      BroadcastingUtils::sendAlertEmail("System Error", "Extract failed load recipients in ".get_class($this)."!", "Y");
      return $this->errorTO;
    }
    $recipientUId = $reArr[0]['uid'];
        
    $remitArr = $this->RemittanceDAO->getRemittanceArray($this->principalUid);
    if (count($remitArr)==0) {
      BroadcastingUtils::sendAlertEmail("System Error", "Extract failed load remittance in ".get_class($this)."!", "Y");
      return $this->errorTO;
    }
    
    foreach($remitArr as $rm=>$remit){                  
        //print_r($remit);

          /*-------------------------------------------------*/
          /*            START BUILDING OUTPUT
          /*-------------------------------------------------*/
          
          $period = mysql_real_escape_string($remit["period"]);
            //period
          if($period == 11){
          	  $remitdate = "31/01/2015";
          }
          elseif ($period == 12){
          	$period = 12;
            $remitdate = "28/02/2015";
          }	
          elseif ($period == 1){
          	$remitdate = "31/03/2015";
          }	
          elseif ($period ==2){
          	
            $remitdate = "30/04/2015";
          }	
          elseif ($period == 3){
          	
            $remitdate = "31/05/2014";
          }	
          elseif ($period == 4){
          	
            $remitdate = "30/06/2014";
          }	
          elseif ($period == 5){
          	
            $remitdate = "31/07/2014";
          }	
          elseif ($period == 6){
          	
            $remitdate = "31/08/2014";
          }	
          elseif ($period == 7){
          	
            $remitdate = "30/09/2014";
          }	
          elseif ($period == 8){
          	
            $remitdate = "31/10/2014";
          }	
          elseif ($period == 9){
          	
            $remitdate = "30/11/2014";
          }	
          elseif ($period == 10){
          	
            $remitdate = "31/12/2014";
          }	
          else {
          	$period = 00;
          }	

            /* PASTEL HEADER */
            //array containing list of row values
            $cnSequence = "CR".$this->getRemitFileSequence($this->principalUid);
            $rowArr = array();
            $rowArr[] = '"HEADER"';
            $rowArr[] = '"'. $cnSequence.'"';
            $rowArr[] = '" "';  //printed
            $rowArr[] = '" "';  //CUSTOMER CODE - Pastel Account.
            $rowArr[] = '"PNP001"'; 
            $rowArr[] = '"'.$period.'"';  //period number
            $rowArr[] = $remitdate;
            $rowArr[] = '"'. substr($remit["invoice_reference"],2,8).'"'; 
            $rowArr[] = '"Y"';  //MESSAGE - CHAR
            $rowArr[] = 0;   //MESSAGE - CHAR
            $rowArr[] = '" "';   
            $rowArr[] = '" "';  
            $rowArr[] = '" "';  
            $rowArr[] = '"Uplift"';
            $rowArr[] = '" "'; 
            $rowArr[] = '" "'; 
            $rowArr[] = '" "'; 
            $rowArr[] = '" "'; 
            $rowArr[] = '"RETUR"';
            $rowArr[] = 0;
            $rowArr[] = $remitdate;
            $rowArr[] = '"(021) 658-1068"';
            $rowArr[] = '"                "';
            $rowArr[] = '"Francheste Reyno"';
            $rowArr[] = 1;
            $rowArr[] = '" "';
            $rowArr[] = '" "';
            $dataArr[] = join(',',$rowArr);            

            if(abs($remit['original_amount'])>0){

              $detArr = array();
              $detArr[] = '"DETAIL"';
              $detArr[] = '0';               
              $detArr[] = 1;
              $detArr[] = round(($remit['original_amount']/VAL_VAT_RATE_ADD), 2); //Exc- NUM
              $detArr[] = round($remit['original_amount'], 2);  //INCLUSIVE PRICE - NUM
              $detArr[] = '" "';  //UNIT - CHAR
              $detArr[] = 1;
              $detArr[] = 0; //DISCOUNT TYPE
              $detArr[] = 0; //DISCOUNT %  //stored value : discount_value backwards calculation.             

              $detArr[] = '"1000000"';
              $detArr[] = '"' .'claim - '. $remit['invoice_reference'] . '"';
              $detArr[] = '6';  //unknown.
              $detArr[] = '""';
              $detArr[] = '" "';

              $dataArr[] = join(',',$detArr);

            }
          //eo detail
          
         //eo documents
        
        $data = join("\r\n",$dataArr);
      
        }
    
        //create file only if there are successful items.
        $filePath = false;
       

          //determine seq.
          $seqFilename = $this->setFilenameFSEQ(($this->filename), $this->principalUid, false, 3, self::setFilenameFSEQ_LenType_PAD);
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
      


        // SETUP DISTRIBUTION
        $postingDistributionTO = new PostingDistributionTO;
        $postingDistributionTO->DMLType = "INSERT";
        $postingDistributionTO->deliveryType = BT_EMAIL;
        $postingDistributionTO->subject = $this->getTemplateInvoiceSubject(); //might have no filename if all errors therefore don't display on subject line...
        if($filePath!=false){
          $postingDistributionTO->attachmentFile = $filePath;
        }

        $recipientList = explode(",", $reArr[0]["user_uid_list"]); //if blank, sizeof() will still be 1
        $recipientsCheckCount = 0;  //check if atleast one distribution item was posted, incase of invalid recipients

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