<?php
// https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/scripts/phpscripts/CheckForDocumentScans.php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once ($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER."properties/ServerConstants.php");
include_once($ROOT.$PHPFOLDER."DAO/PrincipalDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/ChainDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/PostTransactionDAO.php");

$dbConn = new dbConnect();
$dbConn->dbConnection();

// Get allowed prefixes array
$c= 0;

// C:\inetpub\wwwroot\systems\kwelanga_system\scans\honeyfields";

$prefixArr = array();

// Get all scanning principals

$principalDAO = new PrincipalDAO($dbConn);
$principalArr = $principalDAO->GetAllDocumentScanningPrincipals();

foreach($principalArr as $prow){
	
    // Get scanned prefix for each chain

    $chainDAO = new chainDAO($dbConn);
    $chainArr = $chainDAO->getPrincipalChainsArray($prow['uid']);

    foreach($chainArr as $chrow) {
         if (!in_array($chrow['scanned_document_prefix'], $prefixArr)) {
              array_push($prefixArr, $chrow['scanned_document_prefix']); 
         }
    }
    array_push($prefixArr, 'LOG');

    $dir = "C:/inetpub/wwwroot/systems/kwelanga_system/ftp/". strtolower(trim(str_replace(' ','',$prow['uid']))) . "_scanned";
    $scandir = "C:/inetpub/wwwroot/systems/kwelanga_system/scans/" . strtolower(trim(str_replace(' ','',$prow['uid'])));

    
    if (is_dir($dir)){
       if ($dh = opendir($dir)){
         while (($file = readdir($dh)) !== false){
          	if (in_array(substr($file,0,3), $prefixArr)) {

               $inpos = strripos($file, "IN");
               $pdfpos = strripos($file, ".pdf");
               $cnpos = strripos($file, "CN");
   
               if(substr($file,0,3) == 'LOG') {
                   $logpos = strripos($file, " 20");
                   $logSheetNo = "L".substr($file, 3, $logpos-(3));
                   echo(trim(substr($logSheetNo,1,6)));
                   echo "<br>";
                   // Update document_tripsheet
                   
                   $postTransactionDAO = new PostTransactionDAO($dbConn);        
                   $rTO = $postTransactionDAO->updateTripSheetImage($prow['uid'], trim(substr($logSheetNo,1,6)), str_replace(' ','_',trim(trim($file))));
                   $dbConn->dbinsQuery("commit"); 
     
                   if (!copy($dir. "/" . trim($file),$scandir. "/" . str_replace(' ','_',trim(trim($file))))) {echo "File Copy Failed";}
                   
                   if (!unlink($dir. "/" . trim($file))) {echo "File Delete Failed";}                   
                     
               } else {
                   if (!$cnpos) {
                       $docnopos = "I". substr($file, $inpos+2, $pdfpos-($inpos+2));
                   } elseif (!$inpos) {
                       $docnopos = "C". substr($file, $cnpos+2, $pdfpos-($cnpos+2));
                   } elseif ($inpos < $cnpos ) {
                       $docnopos = "I". substr($file, $inpos+2, $cnpos-($inpos+2));
                   } elseif ($cnpos < $inpos ) {
                       $docnopos = substr($file, $cnpos+2, $cnpos-($inpos+2));
                   } else {
         	             $docnopos = "Unable to determine Document Number";
                       echo "<br>";      	
                   }
                   
                   if ($docnopos <> "Unable to determine Document Number")
                       if(trim(substr($docnopos,0,1))== "I") {
                           $doctype = "1,6,13";
                           $docnoField = 'document_number';
                           $docno = trim(substr($docnopos,1,8));
                           
                       } else {
                           $doctype = "4,31";
                           $docnoField = 'alternate_document_number';
                           $docno = trim(substr($docnopos,1,8));
                       }
                   
                   $postTransactionDAO = new PostTransactionDAO($dbConn);        
                   $rTO = $postTransactionDAO->updateDocumentHeaderImage($prow['uid'], $docno, str_replace(' ','_',trim($file)), $doctype, $docnoField);
                   $dbConn->dbinsQuery("commit"); 
                   
                   if (!copy($dir. "/" . trim($file),$scandir. "/" . str_replace(' ','_',trim($file)))) {echo "File Copy Failed";}
                   
                   if (!unlink($dir. "/" . trim($file))) {echo "File Delete Failed";}                          
                   
                   
               }       	

            } else {
            	if (substr(trim($file),0,1) <> ".") {
                echo ($dir. "/" . trim($file));
              	echo "<br>";
                if (!unlink($dir. "/" . trim($file))) {echo "File Delete Failed";} 
              }	
            }
         } 
         closedir($dh);
       } 
       // Get all debriefed documents not yet scanned
    } else {
         echo "Principal Folder Not Found";
         return;
    }
}
echo "Scan Files Check Completed ";
    
?>