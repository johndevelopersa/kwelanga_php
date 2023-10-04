<?php

include_once('ROOT.php');
include_once($ROOT . 'PHPINI.php');
include_once($ROOT . $PHPFOLDER . "functional/extracts/daily/extractController.php");

error_reporting(-1);
ini_set('display_errors', 1);

//static method handler.
class newWilmarSoNellwyn {
  public static function generateOutput(){
    $className = basename(__FILE__,'.php').'Init';
    global $ROOT, $PHPFOLDER, $dbConn;
    $obj = new $className();
    return $obj->generateOutput();
  }
}

class newWilmarSoNellwynInit extends extractController {
  private $principalUid = 351; //uid of principal extract.
  private $filename     = 'SO_XX.txt';  //main controller will build full filename with seq. for us.
  private $DoFilename   = 'DO_XX.txt';  //credit note filename
  private $billFilename = 'BILL_XX.txt';  //Stock Movement filename
  private $credFilename = 'CR_XX.txt'; 
  
  public function generateOutput(){

        global $ROOT, $PHPFOLDER, $headerArr, $headerArr2, $headerArrCr, $headerArrCr2;

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
    
    $depotList = [393, 400];
    
    $reArr = $this->bIDAO->getNotificationRecipients($this->principalUid, NT_DAILY_EXTRACT_CUSTOM);
    if (count($reArr)==0) {
      BroadcastingUtils::sendAlertEmail("System Error", "Export failed load recipients in ".get_class($this)."!", "Y");
      return $this->errorTO;
    }
    $recipientUId = $reArr[0]['uid'];
    // Create new log entries into SMART_EVENT - This doesn't rely on a notification trigger
    if (!$this->skipInsert) {
      $rTO = $this->postExtractDAO->queueAllInvoiced($this->principalUid, 
                                                     $recipientUId, 
                                                     $inclCancelled = false,
                                                     $p_dtArr = false,
                                                     $p_wDSArr = false,
                                                     $fromInvDate='2021-01-01',
                                                     $toInvDate=false,
                                                     $chainUIdIn=false,
                                                     $dataSource=false,
                                                     $capturedBy=false,
                                                     $depotUId = $depotList);
       //use the loaded receipientUID and not the notification type... *** same as document confirmations***
      if ($rTO->type != FLAG_ERRORTO_SUCCESS) {
        BroadcastingUtils::sendAlertEmail("System Error", "Export failed to call postExtractDAO->queueAllInvoiced in ".get_class($this)." " . $rTO->description, "Y");
      } else {
        $this->dbConn->dbinsQuery("commit;");
      }
      //credits and debit notes
      $rTO = $this->postExtractDAO->queueAllCreditsAndDebits($this->principalUid, 
                                                             $recipientUId, 
                                                             array(DT_CREDITNOTE),
                                                             $depotUId = $depotList,
                                                             $fromInvDate=false,
                                                             $toInvDate=false,
                                                             $dataSource=false,
                                                             $capturedBy=false,
                                                             $altChainUIdIn = false);  //use the loaded receipientUID and not the notification type... *** same as document confirmations***

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
    
//echo "<pre>";               
//print_r($seDocs);           
//echo "<br>";                
    // Count line on the order and update header
    $ssorder = "";
    $totArray = [];
    $numberOfDocs = 0;
    
    foreach($seDocs as $crow) {
    	    if($ssorder <> $crow['document_number']){
              if($ssorder <> "") {
              	   $totArray[] = [$ssorder => $lT];
              	   $numberOfDocs++;
              }              
              $lT = 0;
              $ssorder = $crow['document_number'] ;
    	    }
    	    $lT++; 
    }
    $totArray[] = [$crow['document_number'] => $lT];
    $numberOfDocs++;
    
    //group array
    $grpDocs = array();
    $psms=array();
    foreach($seDocs as $k=>$r){
        //place into groups
        $type = 'i'; //invoices
        if ($r['document_type_uid'] == DT_CREDITNOTE){
           $type = 'c';
        }
        $grpDocs[$type][$r['dm_uid']][] = $r;  //group by index.
        $psms[$r["principal_store_uid"]]=$r["principal_store_uid"];
    }
    // get special field values for all stores in above docs
    if (sizeof($psms)>0) {
         $sfvals  = $this->miscDAO->getPrincipalSpecialFieldValuesMultEntities($this->principalUid, 513, implode(",",$psms), CT_STORE_SHORTCODE, $arrayIndex="entity_uid");  //Account - S/Sure
         $sfvalsc = $this->miscDAO->getPrincipalSpecialFieldValuesMultEntities($this->principalUid, 999, implode(",",$psms), CT_STORE_SHORTCODE, $arrayIndex="entity_uid");  //Account - S/Sure
    }
    
    include_once($ROOT . $PHPFOLDER . "functional/extracts/daily/newWilmarArr.php");
       
    $dataArr       = array();
    $dataArrDO     = array();
    $dataArrBILL   = array();
    $credArr       = array();
    
    $numOrd           =   0 ;
    
    $dataArr[] = join('|', $headerArr);  
    $dataArr[] = join('|', $headerArr2);
    
    $credArr[] = join('	', $headerArrCr);
    $credArr[] = join('	', $headerArrCr2);    
    
    $cInvoices = $cCredits = 0;    

    foreach($grpDocs as $type => $hr){
         /*-------------------------------------------------*/
         /* START BUILDING OUTPUT
         /*-------------------------------------------------*/
    
         $errorSEUIdArr    = array();
         $successSEUIdArr  = array();
         $filecount        =   0 ; 
         $successCount     =   0 ;
         $itemInd          =   0 ;
         $lineNo           =   0 ;
         $ssorder          =  "" ;
         
         $fileSequence     = $this->getDocumentFileSequence($this->principalUid);
         
         foreach($hr as $ord) { //document loop
         	    $itemInd++;
         	    $lineNo   =   0 ;
         	    foreach($ord as $d) { //detail rows.          	    	   
         	    	   if(key($totArray[$numOrd]) <> $ord[0]['document_number']) {
         	    	   	   $numOrd++;
         	    	   }
//         	    	   echo "<br>";    
//         	    	   echo $ord[0]['document_number'] . ' - ' . $totArray[$numOrd][$ord[0]['document_number']];
                   $lineNo++;

                   if((empty($sfvals[$ord[0]["principal_store_uid"]]['value']))) {  //has no special field and/or blank...
                       $errorSEUIdArr[] = $ord[0]['se_uid']; //list of smart event errors
                   } else {
                       $successSEUIdArr[] = $ord[0]['se_uid']; //list of smart event success
                       $storeAcc          = $sfvals[$ord[0]["principal_store_uid"]]['value'];
//                     $Storecode         = $sfvalsc[$ord[0]["principal_store_uid"]]['value'];
                       //array containing list of row values
                       $ordCount = 0;
                      
                       $successCount++;
                       $dataH    = array();  
                       $dataDO   = array();  
                       $dataBill = array(); 
                       $dataCR   = array(); 
                       
                       if($type == 'i') { 
                       	   $dataH[] = $itemInd;                                                                             // Line type 1
                           $dataH[] = '';                                                                                   // 2
                           $dataH[] = substr($ord[0]["invoice_date"],8,2) . '.' . substr($ord[0]["invoice_date"],5,2) . '.' . substr($ord[0]["invoice_date"],0,4)  ;                                    // InvoiceDate 3
                           $dataH[] = 'WXO3';   //4
                           $dataH[] = 'WXSO';   // 5
                           $dataH[] = '99';     // 6  
                           $dataH[] =  '60'; // 7
                           if($ord[0]['depot_uid'] == 393) {
                           	      $SalesOffice  = 'ZA08';
                           } elseif($ord[0]['depot_uid'] == 400) {
                           	      $SalesOffice  = 'ZA10';
                           } else {  
                           	      $SalesOffice  = ' '; 
                           }
                           $dataH[] = $SalesOffice;  // 8
                           $dataH[] = trim(str_replace(array('"',"'"),array('',''),$ord[0]["customer_order_number"]));                                        // Customer Order Number   9
                           $dataH[] = substr($ord[0]["order_date"],8,2) . '.' . substr($ord[0]["order_date"],5,2) . '.' . substr($ord[0]["order_date"],0,4);  // 7 PO Date  10
                           $dataH[] = $storeAcc;                                                                                                              // PrincipalsStoreCode
                           $dataH[] = '';                                                                                                                     // Payment Method 12
                           $dataH[] = '';                                                                                                                     // vbelInd  13
                           $dataH[] = $totArray[$numOrd][$ord[0]['document_number']]*10 ;                                                                     // vbelInd  14
                           $dataH[] =  '';                                                                                                                    // line NO  15
                           $dataH[] = trim(str_replace(array('"'),array(''),$d['alt_code']));                                                                 // PrincipalStockCode	x	   16
                           $dataH[] = abs($d['document_qty']);  // 17
                           $dataH[] = '';         //18
                           $dataH[] = '';         //19
                           $dataH[] = 'WX02'; //20
                           $dataH[] = '';        //21
                           $dataH[] = '';        // 22
                           if(trim($d['vat_rate']) == 0) { $tInd = 0; } else { $tInd = 1;}
                           $dataH[] = $tInd;      // 23
                           $dataH[] = 'ZP04';     // 24
                           $dataH[] = number_format(trim($d['net_price']),2,'.','') ;        //25
                           $dataH[] = 'ZAR';   //26
                           $dataH[] = '';        //27
                           $dataH[] = '';        //28
                           $dataH[] = '';        //29
                           $dataH[] = '';        //30
                           $dataH[] = '';        //31
                           $dataH[] = '';        //32
                           $dataH[] = '';        //33
                           $dataH[] = '';        //34
                           $dataH[] = '';        //35
                           $dataH[] = '';        //36        
                           $dataH[] = 'ZAR';     //37
                           $dataH[] = '10';      //38         
                           $dataH[] = '';        //39
                           $dataH[] = '';        
                           $dataH[] = '';        
                           $dataH[] = '';        
                           $dataH[] = '';        
                           $dataH[] = '';        
                           $dataH[] = '';        
                           $dataH[] = '';        
                           $dataH[] = '';  
                           $dataH[] = '';        
                           $dataH[] = '';        
                           $dataH[] = '';        
                           $dataH[] = 'Y';
                           $dataH[] = '';        
                           $dataH[] = '';        
                           $dataH[] = '';        
                           $dataH[] = '';        
                           $dataH[] = '';        
                           $dataH[] = '';        
                           $dataH[] = '';                
                           $dataH[] = '';        
                           $dataH[] = '';        
                           $dataH[] = '';        
                           $dataH[] = '';        
                           $dataH[] = '';        
                           $dataH[] = '';        
                           $dataH[] = '';        
                           $dataH[] = '';        
                           $dataH[] = '';        
                           $dataH[] = '';        
                           $dataH[] = '';        
                           $dataH[] = '';        
                           $dataH[] = '';        
                           $dataH[] = ''; 
                           $dataH[] = '';       // 2 
                           $dataH[] = '';       // 3 
                           $dataH[] = '';       // 4 
                           $dataH[] = '';       // 5 
                           $dataH[] = '';       // 6 
                           $dataH[] = '';       // 7 
                           $dataH[] = '';       // 8 
                           $dataH[] = '';       // 9
                           $dataH[] = '';       // 0 
                           $dataH[] = '';       // 1 
                           $dataH[] = str_pad(substr($ord[0]['document_number'],-6), 6, 0, STR_PAD_LEFT);       // 2 



                           $dataArr[] = join('	',$dataH); 
                           $data = join("\r\n",$dataArr);
                           
                           $dataDO[] =  str_pad(substr($ord[0]['document_number'],-6), 6, 0, STR_PAD_LEFT);   // SO Number
                           $dataDO[] =  $lineNo*10 ;                                                          // So Item
                           $dataDO[] =  substr($ord[0]["invoice_date"],8,2) . '-' . substr($ord[0]["invoice_date"],5,2) . '-' . substr($ord[0]["invoice_date"],0,4)  ;     // DO Date
                           $dataDO[] =  '' ;                                                                 // Planned GI Date
                           $dataDO[] =  '' ;                                                                 // Actual GI Date
                           $dataDO[] =  abs($d['document_qty']);                                             // DO Qty	
                           $dataDO[] =  abs($d['document_qty']);                                             // PGI QTY	
                           $dataDO[] =  '' ;                                                                 // UOM	
                           $dataDO[] =  'WX01';                                                              // Shipping Point	
                           if($ord[0]['depot_uid'] == 393) { 
                           	      $storageLoc = 'ST35';
                           } if($ord[0]['depot_uid'] == 400) {
                           	      $storageLoc = 'ST37';     
                           } else {  
                           	      $storageLoc = ' '; 
                           }
                           $dataDO[] =  $storageLoc;                                                         // Storage Location	
                           $dataDO[] =  'wx';                                                                // Batch	Indicator 
                           $dataDO[] =  $itemInd;                                                            // Parameter
                            
                           $dataArrDO[] = join('	',$dataDO); 
                           $dataDO      = join("\r\n",$dataArrDO); 
                       
                           $dataBill[] =  str_pad(substr($ord[0]['document_number'],-6), 6, 0, STR_PAD_LEFT);   // SO Number
                           $dataBill[] =  'Y' ;
               
                           $dataArrBILL[] = join('	',$dataBill); 
                           $dataBill   = join("\r\n",$dataArrBILL); 
                           
                           $cInvoices++;
                       }
                       if($type == 'c') { 
                       	
                             $dataCR[] = $itemInd;                                                                                                                       // "Item Indicator";                           // 1
                       	     $dataCR[] = '';                                                                                                                             // "";                                         // 2
                       	     $dataCR[] = substr($ord[0]["invoice_date"],8,2) . '.' . substr($ord[0]["invoice_date"],5,2) . '.' . substr($ord[0]["invoice_date"],0,4)  ;  // "Document date";                            // 3
                             $dataCR[] = 'WXR3';                                                                                                                         // "Document type";                            // 4
                         	   $dataCR[] = 'WXSO';                                                                                                                         // "Sales Organization";                       // 5
                             $dataCR[] = '99';                                                                                                                           // "Distribution channel";                     // 6
                        	   $dataCR[] = '60';                                                                                                                  // "Division";                                 // 7 
                              if($ord[0]['depot_uid'] == 393) {                                                                                                                         
                                 $SalesOffice  = 'ZA08';                                                                                                                 // "Sales Office";                                  
                              } else {                                                                                                                                   
                                 $SalesOffice  = '';                                                                                                                 
                              }
                             $dataCR[] = $SalesOffice;                                                                                                                   // "Sales Office"; 
                             $dataCR[] = substr($ord[0]["alternate_document_number"],2,6);                                                                               // "PO number";                                                                                                                                 
                             $dataCR[] = substr($ord[0]["invoice_date"],8,2) . '.' . substr($ord[0]["invoice_date"],5,2) . '.' . substr($ord[0]["invoice_date"],0,4)  ;  // "PO date";                                                                
                             $dataCR[] = $storeAcc;                                                                                                                      // "Customer code";                                        
                             $dataCR[] = '';                                                                                                                             // "Payment method";                                       
                             $dataCR[] = '';                                                                                                                             // "";                                                     
                             $dataCR[] = '10';                                                                                                                           // "SO item";                                              
                             $dataCR[] = '';                                                                                                                             // "";                                                     
                             $dataCR[] = trim(str_replace(array('"'),array(''),$d['alt_code']));                                                                         // "Material Code";                                        
                                                  $dataCR[] = abs($d['document_qty']);                                                                                                        //  "Order Qty";                                // 17   
                             $dataCR[] = 'CAR';                                                                                                                          //  "Order UoM";                                // 18   
                             $dataCR[] = '';                                                                                                                             //  "Reason for rejection";                     // 19   
                             $dataCR[] = 'WX01';                                                                                                                         //  "Plant";                                    // 20   
                             $dataCR[] = '';                                                                                                                             //  "Over tolerance";                           // 21   
                             $dataCR[] = '';                                                                                                                             //  "Under tolerance";                          // 22   
                             $dataCR[] = '';                                                                                                                             //  "Tax class 1";                              // 23   
                             $dataCR[] = 'ZP04';                                                                                                                         //  "Condition Type";                           // 24   
                             $dataCR[] = number_format(trim($d['net_price']),2,'.','')   ;                                                                               //  "Amount";                                   // 25   
                             $dataCR[] = '';                                                                                                                             //  "Currency";                                 // 26   
                             $dataCR[] = '';                                                                                                                             //  "Per";                                      // 27   
                             $dataCR[] = '';                                                                                                                             //  "UoM";                                      // 28   
                             $dataCR[] = '';                                                                                                                             //  "";                                         // 29   
                             $dataCR[] = '';                                                                                                                             //  "";                                         // 30   
                             $dataCR[] = '';                                                                                                                             //  "";                                         // 31   
                             $dataCR[] = '';                                                                                                                             //  "";                                         // 32   
                             $dataCR[] = '';                                                                                                                             //  "";                                         // 33   
                             $dataCR[] = 'DAP';                                                                                                                          //  "Payment terms";                            // 34   
                             $dataCR[] = 'DAP';                                                                                                                          //  "Inco1";                                    // 35   
                             $dataCR[] = 'Delivered at Place';                                                                                                           //  "Inco2";                                    // 36   
                             $dataCR[] = 'ZAR';                                                                                                                          //  "Currency";                                 // 37   
                             $dataCR[] = '10';                                                                                                                           //  "Shipping condition";                       // 38   
                             $dataCR[] = '';                                                                                                                             //  "Sales group";                              // 39   
                             $dataCR[] = '';                                                                                                                             //  "";                                         // 41   
                             $dataCR[] = '';                                                                                                                             //  "";                                         // 42   
                             $dataCR[] = '';                                                                                                                             //  "";                                         // 43   
                             $dataCR[] = '';                                                                                                                             //  "";                                         // 44   
                             $dataCR[] = '';                                                                                                                             //  "";                                         // 45   
                             $dataCR[] = '';                                                                                                                             //  "";                                         // 46   
                             $dataCR[] = '';                                                                                                                             //  "";                                         // 47   
                             $dataCR[] = '';                                                                                                                             //  "";                                         // 48   
                             $dataCR[] = '';                                                                                                                             //  "";                                         // 49   
                             $dataCR[] = '';                                                                                                                             //  "";                                         // 50   
                             $dataCR[] = '';                                                                                                                             //  "Y";                                         // 51   
                             $dataCR[] = '';                                                                                                                             //      "";                                     // 52   
                             $dataCR[] = '';                                                                                                                             //      "";                                     // 53   
                             $dataCR[] = '';                                                                                                                             //      "";                                     // 53   
                             $dataCR[] = '';                                                                                                                             //      "";                                     // 54   
                             $dataCR[] = '';                                                                                                                             //      "";                                     // 55   
                             $dataCR[] = '';                                                                                                                             //      "";                                     // 56   
                             $dataCR[] = '';                                                                                                                             //      "";                                     // 57   
                             $dataCR[] = '';                                                                                                                             //      "";                                     // 58   
                             $dataCR[] = '';                                                                                                                             //      "SO number";                            // 59   
                             $dataCR[] = '';                                                                                                                             //      "";                                     // 60   
                             $dataCR[] = '';                                                                                                                             //      "";                                     // 61   
                             $dataCR[] = '';                                                                                                                             //      "";                                     // 63   
                             $dataCR[] = '';                                                                                                                             //      "";                                     // 63   
                             $dataCR[] = '';                                                                                                                             //      "";                                     // 64   
                             $dataCR[] = '';                                                                                                                             //      "";                                     // 65   
                             $dataCR[] = '';                                                                                                                             //      "AFCE No";                              // 66   
                             $dataCR[] = '';                                                                                                                             //      "";                                     // 67   
                             $dataCR[] = '';                                                                                                                             //      "";                                     // 68   
                             $dataCR[] = '';                                                                                                                             //      "";                                     // 69   
                             $dataCR[] = '';                                                                                                                             //      "";                                     // 70   
                             $dataCR[] = '';                                                                                                                             //      "";                                     // 71
 
                             $credArr[] = join('	',$dataCR); 
                             $dataCred  = join("\r\n",$credArr);
                             
                             $cCredits++; 
                       }	                                                 
                   } //eo special field check
              } 
         } //eo document
         
           if(count($successSEUIdArr)>0){
         	   $data = join("\r\n",$dataArr);
             //write physical file
                          
             $fileType = '351' . str_pad($fileSequence,8,"0",STR_PAD_LEFT);
             
              $filePath = $CREDfilePath = FALSE;
             
             if($cInvoices > 0) { 
                   $filePath     = $this->createFile($folder, str_replace('XX',$fileType,$this->filename), $data);  //places file in correct folder
                   $DOfilePath   = $this->createFile($folder, str_replace('XX',$fileType,$this->DoFilename), $dataDO);  //places file in correct folder
                   $BILLfilePath = $this->createFile($folder, str_replace('XX',$fileType,$this->billFilename), $dataBill);  //places file in correct folder

             } else {
             	     $filePath = $DOfilePath = $BILLfilePath = FALSE;
             }

             if($cCredits > 0) { 
                    $CREDfilePath = $this->createFile($folder, str_replace('XX',$fileType,$this->credFilename), $dataCred);  //places file in correct folder
             } else {
             	     $CREDfilePath = FALSE;
             }
             if($filePath == false && $CREDfilePath ==  false){
                 BroadcastingUtils::sendAlertEmail("System Error", "Failed to create extract file!", "Y");
                 $this->errorTO->type = FLAG_ERRORTO_ERROR;
                 $this->errorTO->description = "Failed in ".get_class($this)." on file creation";
                 return $this->errorTO;
             }
              //create actual file to local FTP folder.
              // echo DIR_DATA_NON_FTP_FROM . 'ftp/willmar/invoices/';
              // $fp = file_put_contents(DIR_DATA_NON_FTP_FROM . 'ftp/willmar/invoices/' . str_replace('XX',$fileType,$seqFilename), $data);
         }
         if($cInvoices > 0) {
         
             // SETUP DISTRIBUTION SO
             $postingDistributionTO = new PostingDistributionTO;
             $postingDistributionTO->DMLType = "INSERT";
             $postingDistributionTO->deliveryType = BT_EMAIL;
             $postingDistributionTO->subject = $this->getTemplateInvoiceSubject();
             $postingDistributionTO->body = $this->getTemplateBody($principalName, $successCount, count($errorSEUIdArr), $this->getManagementURL($this->principalUid));
             if($filePath!=false){                 
				//$postingDistributionTO->attachmentFile = $filePath;
				$postingDistributionTO->setAttachmentFileAsS3Uri(S3_BUCKET_NAME, str_replace('/bkup/','/',$filePath));	
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
           
             // SETUP DISTRIBUTION DO
             $postingDistributionTO = new PostingDistributionTO;
             $postingDistributionTO->DMLType = "INSERT";
             $postingDistributionTO->deliveryType = BT_EMAIL;
             $postingDistributionTO->subject = $this->getTemplateInvoiceSubject();
             $postingDistributionTO->body = $this->getTemplateBody($principalName, $successCount, count($errorSEUIdArr), $this->getManagementURL($this->principalUid));
             if($filePath!=false){                
				//$postingDistributionTO->attachmentFile = $DOfilePath;
				$postingDistributionTO->setAttachmentFileAsS3Uri(S3_BUCKET_NAME, str_replace('/bkup/','/',$DOfilePath));	
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
           
             // SETUP DISTRIBUTION - BILL
             $postingDistributionTO = new PostingDistributionTO;
             $postingDistributionTO->DMLType = "INSERT";
             $postingDistributionTO->deliveryType = BT_EMAIL;
             $postingDistributionTO->subject = $this->getTemplateInvoiceSubject();
             $postingDistributionTO->body = $this->getTemplateBody($principalName, $successCount, count($errorSEUIdArr), $this->getManagementURL($this->principalUid));
             if($filePath!=false){
				//$postingDistributionTO->attachmentFile = $BILLfilePath;
				$postingDistributionTO->setAttachmentFileAsS3Uri(S3_BUCKET_NAME, str_replace('/bkup/','/',$BILLfilePath));	
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
             $cInvoices = 0;
         }
         if($cCredits > 0) {  
             // SETUP DISTRIBUTION - CREDIT
             $postingDistributionTO = new PostingDistributionTO;
             $postingDistributionTO->DMLType = "INSERT";
             $postingDistributionTO->deliveryType = BT_EMAIL;
             $postingDistributionTO->subject = $this->getTemplateCreditSubject(); 
             $postingDistributionTO->body = $this->getTemplateBody($principalName, $successCount, count($errorSEUIdArr), $this->getManagementURL($this->principalUid));
             if($CREDfilePath!=false){
				//$postingDistributionTO->attachmentFile = $CREDfilePath;
				$postingDistributionTO->setAttachmentFileAsS3Uri(S3_BUCKET_NAME, str_replace('/bkup/','/',$CREDfilePath));	
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
             
             $cCredits > 0;
         }    
        /*
         *  UPDATE SMART EVENT in BULK
         */
        //SUCCESSFUL ITEMS
        if (sizeof($successSEUIdArr) > 0) {
        	
        	if($cInvoices > 0) {$ofilename = basename($filePath);} else {$ofilename = basename($CREDfilePath);}
        	
          $bIResult = $this->postBIDAO->setSmartEventStatusBulk(implode(",", $successSEUIdArr), $ofilename, "");
          if ($bIResult->type != FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description = "Failed in ".get_class($this)." extract on setting success setSmartEventStatusBulk with {$bIResult->description}";
            BroadcastingUtils::sendAlertEmail("Error in ".get_class($this)." extract", $this->errorTO->description, "Y", false);
            return $this->errorTO;
          }
        }
        //ERROR EXTRACTS - MARK SE AS "E", for extract errors display screen.
        if (sizeof($errorSEUIdArr) > 0) {
          $bIResult = $this->postBIDAO->setSmartEventStatusBulk(implode(",", $errorSEUIdArr), "513", "", FLAG_ERRORTO_ERROR);
          if ($bIResult->type != FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description = "Failed in ".get_class($this)." on setting errror setSmartEventStatusBulk with {$bIResult->description}";
            BroadcastingUtils::sendAlertEmail("Error in ".get_class($this)." extract", $this->errorTO->description, "Y", false);
            return $this->errorTO;
          }
        }
    }
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