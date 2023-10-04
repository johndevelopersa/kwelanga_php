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
class HoneyfieldsInvoicedIA {
  public static function generateOutput(){
    $className = basename(__FILE__,'.php').'Init';
    global $ROOT, $PHPFOLDER, $dbConn;
    $obj = new $className();
    return $obj->generateOutput();
  }
}



class HoneyfieldsInvoicedIAInit extends extractController {

    private $principalUid = 305; //uid of principal extract.

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
        $reArr = $this->bIDAO->getNotificationRecipients($this->principalUid, NT_DAILY_EXTRACT_ALTCUSTOM4);
        if (count($reArr)==0) {
            BroadcastingUtils::sendAlertEmail("System Error", "Extract failed load recipients in ".get_class($this)."!", "Y");
            return $this->errorTO;
        }
        $recipientUId = $reArr[0]['uid'];
        
        $documentTypeArr = [DT_DELIVERYNOTE,
                            DT_ORDINV,
                            DT_ORDINV_ZERO_PRICE];
        $documentStatusArr = [DST_INVOICED];
        
        $documentLogSheetArr = [LT_INTER_AFRICA];

        // Create new log entries into SMART_EVENT - This doesn't rely on a notification trigger
        $rTO = $this->postExtractDAO->queueAllInvoiced($this->principalUid, 
                                                       $recipientUId, 
                                                       $inclCancelled = false,
                                                       $documentTypeArr,
                                                       $documentStatusArr,
                                                       $fromInvDate=false,
                                                       $toInvDate=false,
                                                       $chainUIdIn=false,
                                                       $dataSource=false,
                                                       $capturedBy=false,
                                                       $depotUId = false,
                                                       $altChainUIdIn=false,
                                                       $documentLogSheetArr);  

        if ($rTO->type != FLAG_ERRORTO_SUCCESS) {
            BroadcastingUtils::sendAlertEmail("System Error", "Export failed to call postExtractDAO->queueAllInvoiced in ".get_class($this)." " . $rTO->description, "Y");
        } else {
            $this->dbConn->dbinsQuery("commit;");
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

        $dataArr = array();
        $rowArr = array();
        
        $CustdataArr = array();
        $CustArr = array();
        
        $errorSEUIdArr = array();
        $successSEUIdArr = array();
        $successCount = 0;
        // Filer Header
        $rowArr[] = 'SourceDatabase';
        $rowArr[] = 'Invoice';
        $rowArr[] = 'SalesOrderLine';
        $rowArr[] = 'EdiSenderCode';
        $rowArr[] = 'Customer';
        $rowArr[] = 'Name';
        $rowArr[] = 'ShipToAddr3';
        $rowArr[] = 'DocumentType';
        $rowArr[] = 'InvoiceDate';
        $rowArr[] = 'InvoiceValue';
        $rowArr[] = '|PricePerOrderUom';
        $rowArr[] = 'StockCode';
        $rowArr[] = 'StockDescription';
        $rowArr[] = 'Warehouse';
        $rowArr[] = 'SalesOrder';
        $rowArr[] = 'ShipQty';
        $rowArr[] = 'Branch';
        $rowArr[] = 'Salesperson';
        $rowArr[] = 'CustomerPoNumber';
        $rowArr[] = 'EntrySystemDate';
        $rowArr[] = 'CSE_QTY';
        $rowArr[] = 'CSE_Partial_Fill';
        $rowArr[] = 'CSE_QTY_FullOnly';
        $rowArr[] = 'CSE_QTY_Remainder';
        $rowArr[] = 'OrderUom';
        $rowArr[] = 'StockUom';
        $rowArr[] = 'AlternateUom';
        $rowArr[] = 'OtherUom';
        $rowArr[] = 'ConvFactAltUom';
        $rowArr[] = 'ConvMulDiv';
        $rowArr[] = 'ConvFactOthUom';
        $rowArr[] = 'MulDiv';
        $rowArr[] = 'Mass';
        $rowArr[] = 'ChargeableWeight';
        $rowArr[] = 'DocumentType_ArTrnSummary';
        $rowArr[] = 'CreditedInvoice_ArTrnSummary';
        $rowArr[] = 'RouteCode';
        $rowArr[] = 'NOD';
        $rowArr[] = 'NOD_Date';
        $rowArr[] = 'NDD';
        $rowArr[] = 'NDD_Date';
        $rowArr[] = 'SctReference';
        $rowArr[] = 'PodScanDate';
        $rowArr[] = 'PodScanID';

        $dataArr[] = join('|',$rowArr);
        
        $CustArr[] = 'SourceDatabase';
        $CustArr[] = 'Customer';
        $CustArr[] = 'First_3PL_Order';
        $CustArr[] = 'Last_3PL_Order';
        $CustArr[] = 'Name';
        $CustArr[] = 'CustomerClass';
        $CustArr[] = 'CustomerGroup';
        $CustArr[] = 'StoreNumber';
        $CustArr[] = 'EdiSenderCode';
        $CustArr[] = 'MasterAccount';
        $CustArr[] = 'Contact';
        $CustArr[] = 'Email';
        $CustArr[] = 'Telephone';
        $CustArr[] = 'AddTelephone';
        $CustArr[] = 'RouteCode';
        $CustArr[] = 'SalesArea';
        $CustArr[] = 'SalesLocation';
        $CustArr[] = 'Area';
        $CustArr[] = 'ShipToAddr1';
        $CustArr[] = 'ShipToAddr2';
        $CustArr[] = 'ShipToAddr3';
        $CustArr[] = 'ShipToAddr4';
        $CustArr[] = 'ShipToAddr5';
        $CustArr[] = 'ShipPostalCode';
        $CustArr[] = 'ShortName';
        $CustArr[] = 'ShipToAddress';

        $CustdataArr[] = join('|',$CustArr);       
        
        $rowArr = array();
    
        $rowArr[] = 'KwelangaOnlineSoultions';                                                         // SourceDatabase|     

        $CustStore = '';

        foreach($seDocs as $ord){
        	
        	  $rowArr = array();
            /*-------------------------------------------------*/
            /*            START BUILDING OUTPUT
            /*-------------------------------------------------*/
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     
            $rowArr[] = str_pad(substr($ord['document_number'],-6), 8, 0, STR_PAD_LEFT) ;               // Invoice|                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      
            $rowArr[] = str_pad($ord['line_no'], 2, 0, STR_PAD_LEFT) ;                     // SalesOrderLine|                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               
            $rowArr[] = '';                                                                             // EdiSenderCode|                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                
            $rowArr[] = str_pad($ord['principal_store_uid'], 10, 0, STR_PAD_LEFT) ;                     // Customer| 
            $rowArr[] = trim($ord['deliver_name']);                                                     // Name|                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              
            $rowArr[] = trim($ord['deliver_add1']);                                                     // ShipToAddr3|                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  
            $rowArr[] = 'O';                                                                            // DocumentType|                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 
            $rowArr[] = date('Y-m-d', strtotime($ord['invoice_date']));  //DATE (YYYY-MM-dd)            // InvoiceDate |
            $rowArr[] = number_format($ord['invoice_total'] , 2, '.', '')  ;                            // InvoiceValue|
            $rowArr[] = number_format($ord['selling_price'] , 2, '.', '')  ;                            //  PricePerOrderUom|           
            $rowArr[] = trim($ord['product_code']) ;                                                    // StockCode|       
            $rowArr[] = trim($ord['product_description']) . '"';                                        // StockDescription|
            $rowArr[] = '';                                                                             // Warehouse|
            $rowArr[] = '';                                                                             // SalesOrder|
            $rowArr[] = abs($ord['document_qty']);                                                      // ShipQty|
            $rowArr[] = '';                                                                             // Branch|
            $rowArr[] = '';                                                                             // Salesperson|
            $rowArr[] = $ord["customer_order_number"]   ;                                               //CustomerPoNumber|
            $rowArr[] = '';                                                                             // EntrySystemDate|
            $rowArr[] = abs($ord['document_qty']);                                                      // CSE_QTY|
            $rowArr[] = '';                                                                             // CSE_Partial_Fill|
            $rowArr[] = '';                                                                             // CSE_QTY_FullOnly|
            $rowArr[] = '';                                                                             // CSE_QTY_Remainder|
            $rowArr[] = '';                                                                             // OrderUom|
            $rowArr[] = '';                                                                             // StockUom|
            $rowArr[] = '';                                                                             // AlternateUom|
            $rowArr[] = '';                                                                             // OtherUom|
            $rowArr[] = '';                                                                             // ConvFactAltUom|
            $rowArr[] = '';                                                                             // ConvMulDiv|
            $rowArr[] = '';                                                                             // ConvFactOthUom|
            $rowArr[] = '';                                                                             // MulDiv|
            $rowArr[] = abs($ord['document_qty'] * $ord['weight'] );                                    // Mass|
            $rowArr[] = '';                                                                             // ChargeableWeight
            $rowArr[] = '';                                                                             // |DocumentType_ArTrnSummary|
            $rowArr[] = '';                                                                             // CreditedInvoice_ArTrnSummary|
            $rowArr[] = '';                                                                             // RouteCode|
            $rowArr[] = '';                                                                             // NOD|
            $rowArr[] = '';                                                                             // NOD_Date|
            $rowArr[] = '';                                                                             // NDD|
            $rowArr[] = '';                                                                             // NDD_Date|
            $rowArr[] = '';                                                                             // SctReference|
            $rowArr[] = '';                                                                             // PodScanDate|
            $rowArr[] = '';                                                                             // PodScanID       
            
            $successCount++;
            $successSEUIdArr[] = $ord['se_uid']; //list of smart event success                                                                                                                  
        
            $dataArr[] = join('|',$rowArr);
            
            if($CustStore <> $ord['deliver_name']) {
            	
            	    $CustArr = array();

                  $CustArr[] = 'KwelangaOnlineSoultions';                                      // SourceDatabase|
                  $CustArr[] = str_pad($ord['principal_store_uid'], 10, 0, STR_PAD_LEFT) ;     // Customer|
                  $CustArr[] = '';                                                             // First_3PL_Order|
                  $CustArr[] = '';                                                             // Last_3PL_Order|
                  $CustArr[] = trim($ord['deliver_name']) ;                                    // Name|
                  $CustArr[] = '';                                                             // CustomerClass|
                  $CustArr[] = '';                                                             // CustomerGroup|
                  $CustArr[] = '';                                                             // StoreNumber|
                  $CustArr[] = '';                                                             // EdiSenderCode|
                  $CustArr[] = '';                                                             // MasterAccount|
                  $CustArr[] = '';                                                             // Contact|
                  $CustArr[] = '';                                                             // Email|
                  $CustArr[] = '';                                                             // Telephone|
                  $CustArr[] = '';                                                             // AddTelephone|
                  $CustArr[] = '';                                                             // RouteCode|
                  $CustArr[] = '';                                                             // SalesArea|
                  $CustArr[] = '';                                                             // SalesLocation|
                  $CustArr[] = '';                                                             // Area|
                  $CustArr[] = trim($ord['deliver_add1']) ;                                    // ShipToAddr1|
                  $CustArr[] = trim($ord['deliver_add2']) ;                                    // ShipToAddr2|
                  $CustArr[] = trim($ord['deliver_add3']) ;                                    // ShipToAddr3|
                  $CustArr[] = '';                                                             // ShipToAddr4|
                  $CustArr[] = '';                                                             // ShipToAddr5|
                  $CustArr[] = '';                                                             // ShipPostalCode|
                  $CustArr[] = '';                                                             // ShortName|
                  $CustArr[] = trim($ord['deliver_add1']) ;                                    // ShipToAddress  
                 
                  $CustdataArr[] = join('|',$CustArr);  
                  
                  $CustStore = $ord['deliver_name'];  
            }

        } //eo documents

        $data = join("\r\n",$dataArr);
        
        $custData = join("\r\n",$CustdataArr);

        //create file only if there are successful items.
        $filePath = false;
        if(count($successSEUIdArr)>0){

               $filename = 'SO' . date('YmdHis') . '.txt'; 
               
               $custFilename = 'CU' . date('YmdHis') . '.txt'; 

               //write physical file
               $filePath  = $this->createFile($folder, $filename, $data);  //places file in correct folder
               $cfilePath = $this->createFile($folder, $custFilename, $custData);  //places file in correct folder              
               
               if($filePath == false){
                    BroadcastingUtils::sendAlertEmail("System Error", "Failed to create extract file!", "Y");
                    $this->errorTO->type = FLAG_ERRORTO_ERROR;
                    $this->errorTO->description = "Failed in ".get_class($this)." on file creation";
                    return $this->errorTO;
               }
               //create actual file to local Honeyfields folder.
               $fp = file_put_contents(DIR_DATA_NON_FTP_FROM. 'ftp/honeyfields/interafrica/in/' . $filename, $data);
               $cfp = file_put_contents(DIR_DATA_NON_FTP_FROM. 'ftp/honeyfields/interafrica/in/' . $custFilename, $custData);
               if($fp != strlen($data)){
                   BroadcastingUtils::sendAlertEmail("System Error", "Failed to create extract file!", "Y");
                   $this->errorTO->type = FLAG_ERRORTO_ERROR;
                   $this->errorTO->description = "Failed in ".get_class($this)." on actual file creation";
                   return $this->errorTO;
               }

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
            $bIResult = $this->postBIDAO->setSmartEventStatusBulk(implode(",", $errorSEUIdArr), "343", "", FLAG_ERRORTO_ERROR);
                if ($bIResult->type != FLAG_ERRORTO_SUCCESS) {
                    $this->errorTO->type = FLAG_ERRORTO_ERROR;
                    $this->errorTO->description = "Failed in ".get_class($this)." on setting errror setSmartEventStatusBulk with {$bIResult->description}";
                    BroadcastingUtils::sendAlertEmail("Error in ".get_class($this)." extract", $this->errorTO->description, "Y", false);
                    return $this->errorTO;
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