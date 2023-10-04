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
include_once($ROOT.$PHPFOLDER.'DAO/ImportDAO.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER.'TO/PostingDocumentUpdateTO.php');
include_once($ROOT.$PHPFOLDER.'TO/PostingDocumentUpdateDetailTO.php');
include_once($ROOT.$PHPFOLDER.'TO/PostingDocumentUpdateBatchTO.php');
include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');

class AdaptorTOU {

     private $dbConn;
     private $principalDAO;

     function __construct($dbConn){
         $this->dbConn = $dbConn;
         $this->principalDAO = new PrincipalDAO($this->dbConn);
         $this->importDAO = new ImportDAO($this->dbConn);
     }
     function adaptorGDS_INV($content, $onlineFileProcessItem){

     global $ROOT, $PHPFOLDER;

     $arrTO = array();
     $eTO = new ErrorTO;
     $fileArr = explode("\n", preg_replace('/[\xFF\xFE\xEF\xBB\xBF\x22]/', '', $content)); //linefeed newlines.
     $ullCnfArr = array();
    
     // validate first line of file
     $lineArr=str_getcsv($fileArr[0], ",", '"', "\\");
     
     if(trim(preg_replace('/[\xFF\xFE\xEF\xBB\xBF\x22\x00]/', '', $lineArr[0]))=='Preferred Supplier Name') {
           for ($x = 0; $x <= 17; $x++) {
                if(trim(preg_replace('/[\xFF\xFE\xEF\xBB\xBF\x22\x00]/', '', $lineArr[$x]))=='Preferred Supplier Name') {
                       $SupplierName = $x;
                       $validfile++;
                } elseif(trim(preg_replace('/[\xFF\xFE\xEF\xBB\xBF\x22\x00]/', '', $lineArr[$x]))=='Stock Category Code') {
                    $StockCategoryCode = $x;
                    $validfile++;    
                } elseif(trim(preg_replace('/[\xFF\xFE\xEF\xBB\xBF\x22\x00]/', '', $lineArr[$x]))=='Area Description') {
                    $AreaDescription = $x;
                    $validfile++;                       
                } elseif(trim(preg_replace('/[\xFF\xFE\xEF\xBB\xBF\x22\x00]/', '', $lineArr[$x]))=='Document Type') {
                    $DocumentType = $x;
                    $validfile++;                       
                } elseif(trim(preg_replace('/[\xFF\xFE\xEF\xBB\xBF\x22\x00]/', '', $lineArr[$x]))=='Document Date') {
                    $DocumentDate = $x;
                    $validfile++;                       
                } elseif(trim(preg_replace('/[\xFF\xFE\xEF\xBB\xBF\x22\x00]/', '', $lineArr[$x]))=='Reference') {
                    $Reference = $x;
                    $validfile++;    
                } elseif(trim(preg_replace('/[\xFF\xFE\xEF\xBB\xBF\x22\x00]/', '', $lineArr[$x]))=='Customer Account') {
                    $Channel = $x;
                    $validfile++;                       
                } elseif(trim(preg_replace('/[\xFF\xFE\xEF\xBB\xBF\x22\x00]/', '', $lineArr[$x]))=='Customer Account Name') {
                    $CustomerAccountName = $x;
                    $validfile++;                       
                } elseif(trim(preg_replace('/[\xFF\xFE\xEF\xBB\xBF\x22\x00]/', '', $lineArr[$x]))=='Customer Category') {
                    $CustomerCategory = $x;
                    $validfile++;                       
                } elseif(trim(preg_replace('/[\xFF\xFE\xEF\xBB\xBF\x22\x00]/', '', $lineArr[$x]))=='Customer Order No') {
                    $CustomerOrderNo = $x;
                    $validfile++;    
                } elseif(trim(preg_replace('/[\xFF\xFE\xEF\xBB\xBF\x22\x00]/', '', $lineArr[$x]))=='Principal inv #') {
                    $Principalinv = $x;
                    $validfile++;                       
                } elseif(trim(preg_replace('/[\xFF\xFE\xEF\xBB\xBF\x22\x00]/', '', $lineArr[$x]))=='Order Number') {
                    $OrderNumber = $x;
                    $validfile++;                       
                } elseif(trim(preg_replace('/[\xFF\xFE\xEF\xBB\xBF\x22\x00]/', '', $lineArr[$x]))=='Stock Code') {
                    $StockCode = $x;
                    $validfile++;                       
                } elseif(trim(preg_replace('/[\xFF\xFE\xEF\xBB\xBF\x22\x00]/', '', $lineArr[$x]))=='Stock Description') {
                    $StockDescription = $x;
                    $validfile++;    
               } elseif(trim(preg_replace('/[\xFF\xFE\xEF\xBB\xBF\x22\x00]/', '', $lineArr[$x]))=='Quantity') {
                   $Quantity = $x;
                   $validfile++;                       
               } elseif(trim(preg_replace('/[\xFF\xFE\xEF\xBB\xBF\x22\x00]/', '', $lineArr[$x]))=='Value (Excl) After Discount') {
                   $ValueExcl = $x;
                   $validfile++;                       
               } elseif(trim(preg_replace('/[\xFF\xFE\xEF\xBB\xBF\x22\x00]/', '', $lineArr[$x]))=='Value (Incl) After Discount') {
                   $ValueIncl = $x;
                   $validfile++;                       
               } elseif(trim(preg_replace('/[\xFF\xFE\xEF\xBB\xBF\x22\x00]/', '', $lineArr[$x]))=='System Date') {
                   $SystemDate = $x;
                   $validfile++;                       
               }                         
           }
           echo "<br>";
           echo "Valid File  - " . $validfile;
           echo "<br>";
           if($validfile <> 18) {
                   $eTO->type = FLAG_ERRORTO_ERROR;
                   $eTO->description = "Check file - Order fields missing!";
                   return $eTO;
           }          
           if(count($lineArr) < 18 ) {
                $eTO->type = FLAG_ERRORTO_ERROR;
                $eTO->description = "Invalid Column Count - expecting 40 columns Found " . count($lineArr);
                return $eTO;
           }          
           unset($fileArr[0]);
     } else {     	
              $eTO->type = FLAG_ERRORTO_ERROR;
              $eTO->description = "First line expected to be a header!";
              return $eTO;
     }
     foreach ($fileArr as $key=>$line) {
     	
//     	print_r($line);
     	
     	
         // convert line to CSV
         $lineArr=str_getcsv($line, ",", '"', "\\");
    
    
        	
        	echo "<pre>";
        	print_r($lineArr);
        	
        	echo "<br>";
        }
    
    


  }

}