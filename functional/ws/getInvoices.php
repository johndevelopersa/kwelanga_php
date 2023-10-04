<?php
// This MUST initially be as lightweight as possible until it requires content to be returned, hence no access_control

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER."properties/Constants.php");
include_once($ROOT.$PHPFOLDER."properties/ServerConstants.php");
include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT . $PHPFOLDER . 'DAO/ApiDAO.php');

$dbConn = new dbConnect();
$dbConn->dbConnection();

$data = file_get_contents('php://input');


file_put_contents( "C:/inetpub/wwwroot/systems/kwelanga_system/ftp/outerjoin/extracts/sql.txt", $data . "\n\n", FILE_APPEND); 

$JSON = json_decode($data, true);

file_put_contents( "C:/inetpub/wwwroot/systems/kwelanga_system/ftp/outerjoin/extracts/sql.txt", print_r($JSON, TRUE) . "\n\n", FILE_APPEND); 


$newApiDAO = new ApiDAO($dbConn);
$uCred = $newApiDAO->getVendorUser($JSON['user']['username']);

$uCredStr = trim($uCred[0]['username']) . trim($uCred[0]['password']);

//echo "<br>";
$upayLoadStr = trim($JSON['user']['username']) . trim($JSON['user']['password']);
echo "<br>";

if (strcmp($uCredStr, $upayLoadStr) !== 0) {  // make sure you setup a password specifically for each client individually
		echo json_encode( [
												"resultStatus"=>"E",
												"resultMessage"=>"Sorry, incorrect credentials supplied"
											] );
		exit; // !! NB !!
} else {
       $newApiDAO = new APIDAO($dbConn);
       $aresult   = $newApiDAO->getVendorDataOrders(trim($JSON['user']['startdate']), trim($JSON['user']['enddate']));
       
       $returnArr = [];
       foreach ($aresult as $r) {
	
	             $returnArr[] = [
		                          "vendorName"    => $r["Vendor Name"],
		                          "documentNo"    => $r["Document Number"],
		                          "status"        => $r["Status"],
		                          "formId"        => $r["FormID"],
		                          "store"         => $r["Store"],
		                          "outerAcc"      => $r["Outer Account"],
		                          "po"            => $r["PO Number"],
		                          "processedDate" => $r["Processed Date"],
		                          "InvoicedDate"  => $r["Invoiced Date"],
		                          "DeliveredDate" => $r["Delivered Date"],
		                          "orderDate"     => $r["Order Date"],
		                          "productCode"   => $r["Product Code"],
		                          "LineNo"        => $r["Line Number"],
		                          "product"       => $r["Product"],
		                          "cases"         => $r["Ordered Cases"],
		                          "invoiced"      => $r["Invoiced Cases"],
		                          "delivered"     => $r["Delivered Cases"],
		                          "value"         => $r["Excl. Value"]
		                          ];
       }

       $newApiDAO = new APIDAO($dbConn);
       $aresult = $newApiDAO->getVendorDataInvoices(trim($JSON['user']['startdate']), trim($JSON['user']['enddate']));
       
       foreach ($aresult as $r) {
	
	             $returnArr[] = [
		                          "vendorName"    => $r["Vendor Name"],
		                          "documentNo"    => $r["Document Number"],
		                          "status"        => $r["Status"],
		                          "formId"        => $r["FormID"],
		                          "store"         => $r["Store"],
		                          "outerAcc"      => $r["Outer Account"],
		                          "po"            => $r["PO Number"],
		                          "processedDate" => $r["Processed Date"],
		                          "InvoicedDate"  => $r["Invoiced Date"],
		                          "DeliveredDate" => $r["Delivered Date"],
		                          "orderDate"     => $r["Order Date"],
		                          "productCode"   => $r["Product Code"],
		                          "LineNo"        => $r["Line Number"],
		                          "product"       => $r["Product"],
		                          "cases"         => $r["Ordered Cases"],
		                          "invoiced"      => $r["Invoiced Cases"],
		                          "delivered"     => $r["Delivered Cases"],
		                          "value"         => $r["Excl. Value"]
		                          ];
	
       }

       $newApiDAO = new APIDAO($dbConn);
       $aresult = $newApiDAO->getVendorDataPod(trim($JSON['user']['startdate']), trim($JSON['user']['enddate']));
       
       foreach ($aresult as $r) {
	
	             $returnArr[] = [
		                          "vendorName"    => $r["Vendor Name"],
		                          "documentNo"    => $r["Document Number"],
		                          "status"        => $r["Status"],
		                          "formId"        => $r["FormID"],
		                          "store"         => $r["Store"],
		                          "outerAcc"      => $r["Outer Account"],
		                          "po"            => $r["PO Number"],
		                          "processedDate" => $r["Processed Date"],
		                          "InvoicedDate"  => $r["Invoiced Date"],
		                          "DeliveredDate" => $r["Delivered Date"],
		                          "orderDate"     => $r["Order Date"],
		                          "LineNo"        => $r["Line Number"],
		                          "productCode"   => $r["Product Code"],
		                          "product"       => $r["Product"],
		                          "cases"         => $r["Ordered Cases"],
		                          "invoiced"      => $r["Invoiced Cases"],
		                          "delivered"     => $r["Delivered Cases"],
		                          "value"         => $r["Excl. Value"]
		                          ];
	
       }
       // send JSON back to the client :
       
       echo json_encode([
	                       "resultStatus" => "S",
                         "resultMessage" => "Successfully retrieved data",
                         "data" => $returnArr
                        ]);

	
}
