<?php

// https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/functional/jobs/dearsystem/get_products.php 

/* * ********************************************************************************************
 * *
 * *  Example - Get Products from DEAR System API.
 * *
 * *****************ss*************************************************************************** */

require_once 'ROOT.php';
require_once $ROOT . 'PHPINI.php';
include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once ($ROOT.$PHPFOLDER."DAO/DearSystemDAO.php");
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');	 
    
require_once __DIR__ . "/../../../libs/api/dearsystems/DearRestAPI.php";

require_once __DIR__ . "/../../../properties/RoyalSaltConstants.php";

//Create new database object
$dbConn = new dbConnect(); 
$dbConn->dbConnection();
$errorTO = new ErrorTO;

$apiBaseUri     = RoyalSaltConstants::DearHostname;
$accountId      = RoyalSaltConstants::DearUsername;
$applicationKey = RoyalSaltConstants::DearPassword;
$PrincipalID    = RoyalSaltConstants::PrincipalID;

//construct api client
$api = new DEARRestAPI($apiBaseUri, $accountId, $applicationKey);

print_r("GetProducts:" . PHP_EOL);
echo "PP";
echo "<br>";

$t = 0;
$c = 0;
$n = 0;

for ($x = 1; $x <= 30; $x++) {
$productsListResponse = $api->GetProducts($x, 500);
     if ($productsListResponse->getResponse()->getSuccess()) {
     
         print_r("Total:" . $productsListResponse->getTotal() . PHP_EOL);
         echo "<br>";
         
         foreach ($productsListResponse->getProducts() as $product) {
      print_r("ID: "   . $product->getID()   . PHP_EOL);
         print_r("NAME: " . $product->getName() . PHP_EOL);
          print_r("SKU: "  . $product->getSKU()  . PHP_EOL);
         print_r("REC: "  . $product->getREVENUEACCOUNT()  . PHP_EOL);
         print_r("---------------------------"  . PHP_EOL);
             
             $dearSysDAO = new DearSystemDAO($dbConn);
             $rsUpdate = $dearSysDAO->getRoyalSaltProducts($PrincipalID, $product->getSKU());
             
             $t++;
     
             if(count($rsUpdate) <> 0) {
                 echo "Product Match<br>";
                 echo "<br>";
                 if(trim($product->getID()). trim($product->getREVENUEACCOUNT()) <> trim($rsUpdate[0]['product_guid']) . trim($rsUpdate[0]['revenue_account'])) {
                 	
                     $dearSysDAO = new DearSystemDAO($dbConn);
                     $errorTO = $dearSysDAO->updateRoyalSaltGuid(trim($rsUpdate[0]['pUid']), 
                                                                 trim($product->getID()),
                                                                 trim($product->getREVENUEACCOUNT()));
                     
                     if($errorTO->type == 'S') {
                     	   $c++;
                     	   echo "Update Successful<br>";
                     } else {
                          echo "Update Failed<br>";
                          print_r($errorTO);
                          return;
                     }
                 } else {
                 	$n++;
                 	echo "No Update Required <br>";
                 }
             } else {
             	   echo "No Product Match <br>";
             }
         }
     }
}
echo "<br>";
echo "Total - " . $t;
echo "<br>";
echo "Updated - " . $c;
echo "<br>";
echo "No Update Required - " . $n;
echo "<br>";
print_r("End of Script:" . PHP_EOL);
