<?php    
// must be put here and not later otherwise you get the "headers not sent" error
if (!isset($_SESSION)) session_start();
	
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER."libs/ArchiveStorage.php");

// unfortunately, is necessary to import libraries first before unserializing, otherwise invalid object error occurs
include_once($ROOT.$PHPFOLDER."TO/PostingProductTO.php");
include_once($ROOT.$PHPFOLDER."TO/PostingStockTO.php");
include_once($ROOT.$PHPFOLDER."TO/PostingStoreTO.php");
include_once($ROOT.$PHPFOLDER."TO/PostingDocumentTO.php");
include_once($ROOT.$PHPFOLDER."TO/PostingDocumentDetailTO.php");

// this server handles only calls known to pass PHP objects, and therefore does not convert to and from XML
include_once($ROOT.$PHPFOLDER."libs/phpRPC.php");    
  
/* Include a file that defines all the xml-rpc "methods" */    
include("web_service_api.php");   

$aS = new ArchiveStorage; // (un)serializes and (de)compressObjectes an array

/* Now use the XMLRPC_parse function to take POST     
  data from what xml-rpc client connects and turn     
  it into normal PHP variables */
//$xmlrpc_request = XMLRPC_parse($GLOBALS['HTTP_RAW_POST_DATA']);   // this contains just the params part, not the data returned

// check if data is compressObjected as sent by phpBackendWSClient
$tempG = trim($GLOBALS['HTTP_RAW_POST_DATA']);

if (substr($tempG,0,11)=="COMPRESSED:") {
	$requestArr=$aS->decompressObject(base64_decode(substr($tempG,11)));
} elseif (substr($tempG,0,13)=="UNCOMPRESSED:") {
	$requestArr=unserialize(base64_decode(substr($tempG,13)));
} else { $requestArr=unserialize(base64_decode($tempG)); }

$methodName = PhpRPC::getMethodName($requestArr);    
  
/* Get the parameters associated with that method */  
$params = PhpRPC::getParams($requestArr);

//print("<pre>");
//print_r($requestArr);
//print("</pre>");    
   
/* Error check - if a method was used that doesn't exist, return the error response to the client */    
if(!isset($phpRPC_methods[$methodName])){    
   $phpRPC_methods['method_not_found']($methodName);    
   
/* Otherwise, let's run the PHP function corresponding to that method */     
}else{    
   
   /* Call the method */    
  call_user_func($phpRPC_methods[$methodName],$params);    
}    
?>

