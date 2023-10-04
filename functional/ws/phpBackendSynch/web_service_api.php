<?php
/* web_service_api.php */
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER."properties/dbSettings.inc");
include_once($ROOT.$PHPFOLDER."properties/Constants.php");
include_once($ROOT.$PHPFOLDER."TO/ErrorTO.php");
include_once($ROOT.$PHPFOLDER."TO/LogonTO.php");
include_once($ROOT.$PHPFOLDER."TO/PostingProductTO.php");
include_once($ROOT.$PHPFOLDER."DAO/ImportDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/SynchronisationDAO.php");
include_once($ROOT.$PHPFOLDER."libs/CommonUtils.php");
include_once($ROOT.$PHPFOLDER."libs/ArchiveStorage.php");

$dbConn = new dbConnect();
$dbConn->dbConnection();

$importDAO = new ImportDAO($dbConn);
$synchronisationDAO = new SynchronisationDAO($dbConn);

$phpRPC_methods = array();
$phpRPC_methods['synchProducts'] = "synchProducts";
$phpRPC_methods['synchStock'] = "synchStock";
$phpRPC_methods['synchStores'] = "synchStores";
$phpRPC_methods['synchDocuments'] = "synchDocuments";
$phpRPC_methods['synchDocumentsConfirm'] = "synchDocumentsConfirm";
$phpRPC_methods['synchTripsheets'] = "synchTripsheets";
$phpRPC_methods['synchDeliveryDays'] = "synchDeliveryDays";
$phpRPC_methods['getPrincipalsForSynching'] = "getPrincipalsForSynching";
$phpRPC_methods['confirmPrincipalsSynched'] = "confirmPrincipalsSynched";
$phpRPC_methods['getDownloadCounts'] = "getDownloadCounts";
$phpRPC_methods['getDownloadErrors'] = "getDownloadErrors";
$phpRPC_methods['method_not_found'] = "phpRPC_method_not_found";

// is called after php backend successfully processed list. This func then sets synched status.
function confirmProductsSynched ( $paramsArr ) {
	global $ROOT; global $PHPFOLDER; global $dbConn; global $importDAO; global $synchronisationDAO;
	include_once($ROOT.$PHPFOLDER."DAO/PostSynchronisationDAO.php");

	// check credentials
	$logonTO = $paramsArr[0]["value"];
	if ($logonTO->userName!=md5(WS_PHPBACKEND_USERNAME) || ($logonTO->passWord!=md5(WS_PHPBACKEND_PASSWORD))) {
		$errorTO = new ErrorTO();
		$errorTO->type = FLAG_ERRORTO_ERROR;
		$errorTO->description = "Access to WS denied. Invalid Logon Credentials.";
		echo base64_encode($aS->compressObject($errorTO));
   		return;
	}
	$runTime = $paramsArr[1]["value"];
	$resultTOArr = $paramsArr[2]["value"];

	$postSynchronisationDAO = new PostSynchronisationDAO($dbConn);

	$aS = new ArchiveStorage; // (un)serializes and (de)compressObjectes an array

	foreach($resultTOArr as $row) {
		$errorTO = $postSynchronisationDAO->setSynchedPrincipalProductResult($runTime, $row->identifier, $row->type, $row->description);

		if ($errorTO->type != FLAG_ERRORTO_SUCCESS) {
			$errorTO->identifier = $row->identifier;
			$dbConn->dbinsQuery("rollback");
			echo base64_encode($aS->compressObject($errorTO));
			return;
		}
	}

	$dbConn->dbinsQuery("commit");

	$errorTO = new ErrorTO();
	$errorTO->type = FLAG_ERRORTO_SUCCESS;
	$errorTO->description = "WS successfully invoked.";

	echo base64_encode($aS->compressObject($errorTO));

}

/* Stock */
function synchStock ( $paramsArr ) {
	global $ROOT; global $PHPFOLDER; global $dbConn; global $importDAO;
	include_once($ROOT.$PHPFOLDER."DAO/PostStockDAO.php");

	$errorTOArr = array();
	$postStockDAO = new PostStockDAO($dbConn);

	$aS = new ArchiveStorage; // (un)serializes and (de)compressObjects an array

	// check credentials
	$logonTO = $paramsArr[0]["value"];
	if ($logonTO->userName!=md5(WS_PHPBACKEND_USERNAME) || ($logonTO->passWord!=md5(WS_PHPBACKEND_PASSWORD))) {
		$errorTO = new ErrorTO();
		$errorTO->type = FLAG_ERRORTO_ERROR;
		$errorTO->description = "Access to WS denied. Invalid Logon Credentials.";
		echo base64_encode($aS->compressObject($errorTO));
   		return;
	}

	$TO = $paramsArr[1]["value"];

//print("<pre>");
//print_r($paramsArr);
//print("</pre>");

	// temporarily fudge the session for validation purposes
	if (!isset($_SESSION)) session_start();
	$_SESSION['user_id'] = "000";

	// because stock is simplified for the moment, and we want to reduce sql time, just do a batch insert, rather then individual in commented-out section below
	$errorTO2 = $postStockDAO->postStockBulk($TO);
	foreach ($TO as $postingStockTO) {
		$errorTO = new ErrorTO();
		$errorTO->type = $errorTO2->type;
		$errorTO->identifier = "";
		$errorTO->identifier2 = $postingStockTO->synchUId;
		$errorTO->description = $errorTO2->description;

		$errorTOArr[] = $errorTO;
	}

	/*
	foreach ($TO as $postingStockTO) {
		$postingStockTO->DMLType = "INSERT";
		$postingStockTO->stkUid = "";

		$errorTO2 = $postStockDAO->postStock($postingStockTO); // does not create new ErrorTO each time, so this is necessary
		$errorTO = new ErrorTO();
		$errorTO->type = $errorTO2->type;
		$errorTO->identifier = $errorTO2->identifier;
		$errorTO->identifier2 = $postingStockTO->synchUId;
		$errorTO->description = $errorTO2->description;

		$errorTOArr[] = $errorTO;
	}
	*/

	$dbConn->dbinsQuery("commit");

	$errorTO = new ErrorTO();
	$errorTO->type = FLAG_ERRORTO_SUCCESS;
	$errorTO->description = "WS successfully invoked.";
	$errorTO->object = $errorTOArr;

	echo base64_encode($aS->compressObject($errorTO));

   	return;

}


/* Tripsheet */
function synchTripsheets($paramsArr){


	global $ROOT, $PHPFOLDER, $dbConn, $importDAO;
	include_once($ROOT.$PHPFOLDER."DAO/PostImportDAO.php");
	include_once($ROOT.$PHPFOLDER."TO/PostingTripsheetTO.php");

	$errorTOArr = array();
	$postImportDAO = new PostImportDAO($dbConn);
	$aS = new ArchiveStorage; // (un)serializes and (de)compressObjects an array

	// check credentials
	$logonTO = $paramsArr[0]["value"];
	if ($logonTO->userName!=md5(WS_PHPBACKEND_USERNAME) || ($logonTO->passWord!=md5(WS_PHPBACKEND_PASSWORD))) {
          $errorTO = new ErrorTO();
          $errorTO->type = FLAG_ERRORTO_ERROR;
          $errorTO->description = "Access to WS denied. Invalid Logon Credentials.";
          echo base64_encode($aS->compressObject($errorTO));
          return;
	}

	$TO = $paramsArr[1]["value"];


    //SESSION
	//if (!isset($_SESSION)) session_start();
	//$_SESSION['user_id'] = "000";


	// because stock is simplified for the moment, and we want to reduce sql time, just do a batch insert, rather then individual in commented-out section below
	foreach ($TO as $postingTripsheetTO) {

          $postingTripsheetTO = unserialize (serialize ($postingTripsheetTO));
          $errorTO2 = $postImportDAO->postTripsheet($postingTripsheetTO); // does not create new ErrorTO each time, so this is necessary

          $errorTO = new ErrorTO();
          $errorTO->type = $errorTO2->type;
          $errorTO->identifier = $errorTO2->identifier;
          $errorTO->identifier2 = $postingTripsheetTO->synchUId;
          $errorTO->description = $errorTO2->description;

          $errorTOArr[] = $errorTO;
	}

	$dbConn->dbinsQuery("commit");

	$errorTO = new ErrorTO();
	$errorTO->type = FLAG_ERRORTO_SUCCESS;
	$errorTO->description = "WS successfully invoked.";
	$errorTO->object = $errorTOArr;

	echo base64_encode($aS->compressObject($errorTO));

   	return;

}


/* Delivery Days */
function synchDeliveryDays($paramsArr){

	global $ROOT, $PHPFOLDER, $dbConn, $importDAO;
	include_once($ROOT.$PHPFOLDER."DAO/PostImportDAO.php");
	include_once($ROOT.$PHPFOLDER."TO/PostingDeliveryDayTO.php");

	$errorTOArr = array();
	$postImportDAO = new PostImportDAO($dbConn);

	$aS = new ArchiveStorage; // (un)serializes and (de)compressObjects an array

	// check credentials
	$logonTO = $paramsArr[0]["value"];
	if ($logonTO->userName!=md5(WS_PHPBACKEND_USERNAME) || ($logonTO->passWord!=md5(WS_PHPBACKEND_PASSWORD))) {
		$errorTO = new ErrorTO();
		$errorTO->type = FLAG_ERRORTO_ERROR;
		$errorTO->description = "Access to WS denied. Invalid Logon Credentials.";
		echo base64_encode($aS->compressObject($errorTO));
   		return;
	}

	$TO = $paramsArr[1]["value"];

	$postingDDayTOArr = unserialize(serialize($TO));


	//delete all for depot that older than 2 hours old
	$dResult = $postImportDAO->removeDeliveryDaysforDepot($postingDDayTOArr[0]->depotUid);

	$errorTO2= $postImportDAO->postBulkDeliveryDays($postingDDayTOArr); // does not create new ErrorTO each time, so this is necessary

	//BUILD FULL RETURN.
	foreach ($postingDDayTOArr as $postingDDayTO) {
		$errorTO = new ErrorTO();
		$errorTO->type = $errorTO2->type;
		$errorTO->identifier = "";
		$errorTO->identifier2 = $postingDDayTO->synchUId;
		$errorTO->description = $errorTO2->description;

		$errorTOArr[] = $errorTO;
	}

	$dbConn->dbinsQuery("commit");

	$errorTO = new ErrorTO();
	$errorTO->type = FLAG_ERRORTO_SUCCESS;
	$errorTO->description = "WS successfully invoked.";
	$errorTO->object = $errorTOArr;

	echo base64_encode($aS->compressObject($errorTO));

   	return;

}


/* Stores */

function synchStores ( $paramsArr ) {
	global $ROOT; global $PHPFOLDER; global $dbConn; global $importDAO;
	include_once($ROOT.$PHPFOLDER."DAO/PostStoreDAO.php");
	include_once($ROOT.$PHPFOLDER."DAO/PostImportDAO.php");
	include_once($ROOT.$PHPFOLDER."DAO/ChainDAO.php");
	include_once($ROOT.$PHPFOLDER."TO/PostingStoreTO.php");

	$errorTOArr = array();
	$postStoreDAO = new PostStoreDAO($dbConn);
	$postImportDAO = new PostImportDAO($dbConn);
	$chainDAO = new ChainDAO($dbConn);

	$aS = new ArchiveStorage; // (un)serializes and (de)compressObjectes an array

	// check credentials
	$logonTO = $paramsArr[0]["value"];
	if ($logonTO->userName!=md5(WS_PHPBACKEND_USERNAME) || ($logonTO->passWord!=md5(WS_PHPBACKEND_PASSWORD))) {
		$errorTO = new ErrorTO();
		$errorTO->type = FLAG_ERRORTO_ERROR;
		$errorTO->description = "Access to WS denied. Invalid Logon Credentials.";
		echo base64_encode($aS->compressObject($errorTO));
   		return;
	}

	$TO = $paramsArr[1]["value"];

	// temporarily fudge the session for validation purposes
	if (!isset($_SESSION)) session_start();
	$_SESSION['user_id'] = "000";

	foreach ($TO as $postingStoreTO) {
		$errorTO = new ErrorTO();

		$storeArr=$importDAO->getPrincipalStoreByOfflineClient($postingStoreTO->principal,$postingStoreTO->principalStoreUId,$postingStoreTO->oldAccount);
		if (($postingStoreTO->principalStoreUId!="") && (isset($storeArr[0])) && ($storeArr[0]["uid"]!=$postingStoreTO->principalStoreUId)) {
			$errorTO->type=FLAG_ERRORTO_ERROR;
			$errorTO->description="The documents supplied psm UID does not match the lookup.";
			$errorTO->identifier2 = $postingStoreTO->synchUId;
			$errorTOArr[] = $errorTO;
			continue;
		}

		// backend stores set to unknown vendor
		$postingStoreTO->ownedBy = V_UNKNOWN_VENDOR;
		$postingStoreTO->vendorCreatedByUId = V_UNKNOWN_VENDOR;

		if (isset($storeArr[0])) {
			// ONLY UPDATE SPECIFIC FIELDS IF FROM THE BACKEND !
			$postingStoreTO->principalStoreUId = $storeArr[0]["uid"];

			if (($storeArr[0]["vendor_created_by_uid"]==$postingStoreTO->vendorCreatedByUId) && ($storeArr[0]["owned_by"]==$postingStoreTO->ownedBy)) {
				$errorTO = $postImportDAO->setStoreFieldsFromBackend($postingStoreTO);
			} else {
				$errorTO->type = FLAG_ERRORTO_SUCCESS;
				$errorTO->description = "Skipped Store specific fields update as vendor does not own store";
			}
			$errorTO->identifier = $storeArr[0]["uid"]; // the uid in main table, not unsynched one
			$errorTO->identifier2 = $postingStoreTO->synchUId;
			$errorTOArr[] = $errorTO;
		} else {
			// always enforce to generic if coming from backend and insert
			$mfPChainArr = $chainDAO->getPrincipalChainByOldCode($postingStoreTO->principal, CHAIN_GENERIC_OLD_CODE);
			if (!isset($mfPChainArr[0])) {
				$errorTO = new ErrorTO();
				$errorTO->type = FLAG_ERRORTO_ERROR;
				$errorTO->description = "Failed to Retrieve Generic Chain UID in ws call in synchStores.";
				$errorTO->identifier2 = $postingStoreTO->synchUId;
				$errorTOArr[] = $errorTO;
				continue;
			}
			$postingStoreTO->chain = $mfPChainArr[0]["uid"];

			$postingStoreTO->DMLType = "INSERT";
			$postingStoreTO->principalStoreUId = "";

			$_SESSION['principal_id'] = $postingStoreTO->principal;

			$errorTO2 = $postStoreDAO->postPrincipalStore($postingStoreTO); // does not create new ErrorTO each time, so this is necessary
			$errorTO->type = $errorTO2->type;
			$errorTO->identifier = $errorTO2->identifier;
			$errorTO->identifier2 = $postingStoreTO->synchUId;
			$errorTO->description = $errorTO2->description;

			$errorTOArr[] = $errorTO;
		}

	}

	$dbConn->dbinsQuery("commit");

	$errorTO = new ErrorTO();
	$errorTO->type = FLAG_ERRORTO_SUCCESS;
	$errorTO->description = "WS successfully invoked.";
	$errorTO->object = $errorTOArr;

	echo base64_encode($aS->compressObject($errorTO));

   	return;

}

function synchProducts ( $paramsArr ) {
	global $ROOT; global $PHPFOLDER; global $dbConn; global $importDAO;
	include_once($ROOT.$PHPFOLDER."DAO/PostProductDAO.php");

	$errorTOArr = array();
	$postProductDAO = new PostProductDAO($dbConn);
	$aS = new ArchiveStorage; // (un)serializes and (de)compressObjectes an array

	// check credentials
	$logonTO = $paramsArr[0]["value"];
	if ($logonTO->userName!=md5(WS_PHPBACKEND_USERNAME) || ($logonTO->passWord!=md5(WS_PHPBACKEND_PASSWORD))) {
		$errorTO = new ErrorTO();
		$errorTO->type = FLAG_ERRORTO_ERROR;
		$errorTO->description = "Access to WS denied. Invalid Logon Credentials.";
		echo base64_encode($aS->compressObject($errorTO));
   		return;
	}

	$TO = $paramsArr[1]["value"];

//print("<pre>");
//print_r($paramsArr);
//print("</pre>");

	// temporarily fudge the session for validation purposes
	if (!isset($_SESSION)) session_start();
	$_SESSION['user_id'] = "000";

	foreach ($TO as $postingProductTO) {
		$productArr = $importDAO->getPrincipalProductByCode($postingProductTO->principal,$postingProductTO->productCode,"product_code");
		if (isset($productArr[$postingProductTO->productCode])) {
			/* WE NO LONGER UPDATE PRODUCT DETAILS FROM THE BACKEND !
			$postingProductTO->DMLType = "UPDATE";
			// clipper chops the product description, therefore only change if it seems to be updated to be longer or same as a guess
			if (strlen(trim($postingProductTO->productDescription))<=strlen(trim($productArr[$postingProductTO->productCode]["product_description"]))) $postingProductTO->productDescription=$productArr[$postingProductTO->productCode]["product_description"];

			$postingProductTO->UId = $productArr[$postingProductTO->productCode]["uid"];

			$_SESSION['principal_id'] = $postingProductTO->principal;

			$errorTO2 = $postProductDAO->postProduct($postingProductTO,SESSION_ADMIN_USERID); // does not create new ErrorTO each time, so this is necessary
			$errorTO = new ErrorTO();
			$errorTO->type = $errorTO2->type;
			$errorTO->identifier = $postingProductTO->UId; // the uid in main table, not unsynched one
			$errorTO->identifier2 = $postingProductTO->synchUId;
			$errorTO->description = $errorTO2->description;
			*/

			$errorTO = new ErrorTO();
			$errorTO->type = FLAG_ERRORTO_SUCCESS; // set it to Success, so it will be removed on backend !
			$errorTO->identifier = $productArr[$postingProductTO->productCode]["uid"]; // the uid in main table, not unsynched one
			$errorTO->identifier2 = $postingProductTO->synchUId;
			$errorTO->description = "We no longer update products from the Backend! It appears that a user inserted a product on SureServer at the same time as the product got uploaded from Backend, causing this error.";


			$errorTOArr[] = $errorTO;
		} else {
			$postingProductTO->DMLType = "INSERT";
			$postingProductTO->UId = "";

			$_SESSION['principal_id'] = $postingProductTO->principal;

			$errorTO2 = $postProductDAO->postProduct($postingProductTO,SESSION_ADMIN_USERID); // does not create new ErrorTO each time, so this is necessary
			$errorTO = new ErrorTO();
			$errorTO->type = $errorTO2->type;
			$errorTO->identifier = $errorTO2->identifier;
			$errorTO->identifier2 = $postingProductTO->synchUId;
			$errorTO->description = $errorTO2->description;

			$errorTOArr[] = $errorTO;
		}

	}

	$dbConn->dbinsQuery("commit");

	$errorTO = new ErrorTO();
	$errorTO->type = FLAG_ERRORTO_SUCCESS;
	$errorTO->description = "WS successfully invoked.";
	$errorTO->object = $errorTOArr;

	echo base64_encode($aS->compressObject($errorTO));

   	return;

}


/* Documents */

function synchDocuments ( $paramsArr ) {
	global $ROOT; global $PHPFOLDER; global $dbConn; global $importDAO;
	include_once($ROOT.$PHPFOLDER."DAO/PostTransactionDAO.php");
	include_once($ROOT.$PHPFOLDER."TO/PostingDocumentTO.php");
	include_once($ROOT.$PHPFOLDER."TO/PostingDocumentDetailTO.php");

	$aS = new ArchiveStorage; // (un)serializes and (de)compressObjectes an array

	$errorTOArr = array();
	$postTransactionDAO = new PostTransactionDAO($dbConn);

	// check credentials
	$logonTO = $paramsArr[0]["value"];
	if ($logonTO->userName!=md5(WS_PHPBACKEND_USERNAME) || ($logonTO->passWord!=md5(WS_PHPBACKEND_PASSWORD))) {
		$errorTO = new ErrorTO();
		$errorTO->type = FLAG_ERRORTO_ERROR;
		$errorTO->description = "Access to WS denied. Invalid Logon Credentials.";
		echo base64_encode($aS->compressObject($errorTO));
   		return;
	}

	$TO = $paramsArr[1]["value"];

	// temporarily fudge the session for validation purposes
	if (!isset($_SESSION)) session_start();
	$_SESSION['user_id'] = "000";

	foreach ($TO as $postingDocumentTO) {
		$skip=false;
		$errorTO = new ErrorTO();

		// documents must be left padded with zeros for backwards compatibility
		$postingDocumentTO->documentNumber=str_pad(trim($postingDocumentTO->documentNumber),8,"0",STR_PAD_LEFT);

		// get the document if exists
		$dmArr = $importDAO->getDocumentMasterByOtherKey($postingDocumentTO->principalUId,$postingDocumentTO->documentNumber,$postingDocumentTO->documentTypeUId,$postingDocumentTO->depotUId,"document_number");

		$storeArr=$importDAO->getPrincipalStoreByOfflineClient($postingDocumentTO->principalUId,$postingDocumentTO->principalStoreUId,$postingDocumentTO->holdOldAccount);

     if(in_array($postingDocumentTO->documentTypeUId, array(1,2,3,6,13)) && in_array($postingDocumentTO->documentStatusUId, array(74,75,79)) && in_array($postingDocumentTO->depotUId, array(2,3,5,6,14,118,119,122))){
  		$errorTO->type = FLAG_ERRORTO_SUCCESS;
			$errorTO->identifier2 = $postingDocumentTO->synchUId;
			$errorTO->description = "Document not allowed to be updated from this source.";
			$errorTOArr[] = $errorTO;
			$skip=true;
    } else if (!isset($storeArr[0])) {
			$errorTO->type=FLAG_ERRORTO_ERROR;
			$errorTO->description="Store could not be found based on PSM UId or Old Account";
			$errorTO->identifier2 = $postingDocumentTO->synchUId;
			$errorTOArr[] = $errorTO;
			$skip=true;
		} else if (($postingDocumentTO->principalStoreUId!="") && ($storeArr[0]["uid"]!=$postingDocumentTO->principalStoreUId)) {
			$errorTO->type = FLAG_ERRORTO_ERROR;
			$errorTO->identifier2 = $postingDocumentTO->synchUId;
			$errorTO->description="The documents supplied psm UID does not match the lookup.";
			$errorTOArr[] = $errorTO;
			$skip=true;
		} else if ($postingDocumentTO->principalStoreUId=="") {
			// if multiple rows with same old account is returned, then choose the most appropriate. This is to get around duplicate old_accounts
			if (isset($dmArr[$postingDocumentTO->documentNumber])) {
				foreach ($storeArr as $s) {
					if ($s["uid"]==$dmArr[$postingDocumentTO->documentNumber]["principal_store_uid"]) {
						$postingDocumentTO->principalStoreUId=$s["uid"]; // use existing as it is eligible
						break;
					}
				}
			}
			// set if not matched above
			if ($postingDocumentTO->principalStoreUId=="") $postingDocumentTO->principalStoreUId=$storeArr[0]["uid"];
		}

		if (!$skip) {
			// assign Product UId if not already assigned
			foreach ($postingDocumentTO->detailArr as $row) {
				if ($row->productUId=="") {
					$prodArr=$importDAO->getPrincipalProductByCode($postingDocumentTO->principalUId,$row->holdProductCode,"");
					if (sizeof($prodArr)>0) $row->productUId = $prodArr[0]["uid"];
				}
			}

			if (isset($dmArr[$postingDocumentTO->documentNumber])) {
				$postingDocumentTO->DMLType = "UPDATE";
				$postingDocumentTO->dmUId = $dmArr[$postingDocumentTO->documentNumber]["uid"];

				// make sure some identifiers are the same
				$postingDocumentTO->dataSource = ($dmArr[$postingDocumentTO->documentNumber]["data_source"]=="")?DS_EDI:$dmArr[$postingDocumentTO->documentNumber]["data_source"];
				$postingDocumentTO->capturedBy = ($dmArr[$postingDocumentTO->documentNumber]["captured_by"]=="")?"CLIPPER":$dmArr[$postingDocumentTO->documentNumber]["captured_by"];

				$errorTO2 = $postTransactionDAO->postDocument($postingDocumentTO); // does not create new ErrorTO each time, so this is necessary
				$errorTO = new ErrorTO();
				$errorTO->type = $errorTO2->type;
				$errorTO->identifier = $postingDocumentTO->dmUId; // the uid in main table, not unsynched one
				$errorTO->identifier2 = $postingDocumentTO->synchUId;
				$errorTO->description = $errorTO2->description;

				$errorTOArr[] = $errorTO;
			} else {
				$postingDocumentTO->DMLType = "INSERT";
				$postingDocumentTO->dmUId = "";

				// lookup identifiers from orders table if web source to be used for processing in postDocument
				if ($postingDocumentTO->orderSequenceNo!="") {
					$oObj = $importDAO->getOrdersByOS($postingDocumentTO->principalUId, $postingDocumentTO->orderSequenceNo);
					if (!empty($oObj)) {
						$postingDocumentTO->dataSource = $oObj[0]["data_source"];
						$postingDocumentTO->captureUserUId = $oObj[0]["captureuser_uid"];
						$postingDocumentTO->capturedBy = $oObj[0]["captured_by"];
					} else {
						$postingDocumentTO->dataSource = DS_EDI;
						$postingDocumentTO->capturedBy = "CLIPPER";
					}
				} else {
					$postingDocumentTO->dataSource = DS_EDI;
					$postingDocumentTO->capturedBy = "CLIPPER";
				}

				$errorTO2 = $postTransactionDAO->postDocument($postingDocumentTO); // does not create new ErrorTO each time, so this is necessary
				$errorTO = new ErrorTO();
				$errorTO->type = $errorTO2->type;
				$errorTO->identifier = $errorTO2->identifier;
				$errorTO->identifier2 = $postingDocumentTO->synchUId;
				$errorTO->description = $errorTO2->description;
				// this one MUST have a rollback if fails as otherwise the DM or DH or DD may be missing
				if ($errorTO2->type != FLAG_ERRORTO_SUCCESS) {
					$dbConn->dbinsQuery("rollback");
			    }

				$errorTOArr[] = $errorTO;
			}
		} // end skip

		$dbConn->dbinsQuery("commit"); // must be inside loop because of rollback above, and because we return an array of errorTO's

	}

	$errorTO = new ErrorTO();
	$errorTO->type = FLAG_ERRORTO_SUCCESS;
	$errorTO->description = "WS successfully invoked.";
	$errorTO->object = $errorTOArr;

	echo base64_encode($aS->compressObject($errorTO));

   	return;

}


//compact document update for less mysql usage - only does updates to existing documents.
//*** NO VALIDATION ***

function synchDocumentsConfirm ($paramsArr) {

	global $ROOT; global $PHPFOLDER; global $dbConn; global $importDAO;
	include_once($ROOT.$PHPFOLDER."DAO/PostTransactionDAO.php");
	include_once($ROOT.$PHPFOLDER."TO/PostingDocumentConfirmationTO.php");
	include_once($ROOT.$PHPFOLDER."TO/PostingDocumentConfirmationDetailTO.php");

	$aS = new ArchiveStorage; // (un)serializes and (de)compressObjectes an array

	$errorTOArr = array();
	$postTransactionDAO = new PostTransactionDAO($dbConn);

	// check credentials
	$logonTO = $paramsArr[0]["value"];
	if ($logonTO->userName!=md5(WS_PHPBACKEND_USERNAME) || ($logonTO->passWord!=md5(WS_PHPBACKEND_PASSWORD))) {
		$errorTO = new ErrorTO();
		$errorTO->type = FLAG_ERRORTO_ERROR;
		$errorTO->description = "Access to WS denied. Invalid Logon Credentials.";
		echo base64_encode($aS->compressObject($errorTO));
   		return;
	}

	$TO = $paramsArr[1]["value"];


	foreach ($TO as $postingDocumentConfirmTO) {

	    $postingDocumentConfirmTO = unserialize(serialize($postingDocumentConfirmTO));

		$errorTO = new ErrorTO();

		// documents must be left padded with zeros for backwards compatibility
		$postingDocumentConfirmTO->documentNumber=str_pad(trim($postingDocumentConfirmTO->documentNumber),8,"0",STR_PAD_LEFT);

		// get the document - MUST EXIST!
		$dmArr = $importDAO->getDocumentMasterByOtherKey($postingDocumentConfirmTO->principalUId,$postingDocumentConfirmTO->documentNumber,$postingDocumentConfirmTO->documentTypeUId,$postingDocumentConfirmTO->depotUId,"document_number");

		if (isset($dmArr[$postingDocumentConfirmTO->documentNumber])) {

                  $postingDocumentConfirmTO->dmUId = $dmArr[$postingDocumentConfirmTO->documentNumber]["uid"];

		  //assign Product UId's - MUST EXIST or product row update will "quietly fail"
		  $skip = false;

                  //TURN OFF CLIPPER WO_ FILES UPDATES HERE...
                  if(
                      (
                        in_array($postingDocumentConfirmTO->documentTypeUId, array(1,2,3,6,13)) &&
                        in_array($postingDocumentConfirmTO->documentStatusUId, array(74,75,76,77,78)) &&  //turned off ullmmans confirmation, invoice and pod updates from clipper WO files.
                        in_array($postingDocumentConfirmTO->depotUId, array(2,3,5,6,14,118,119,122))
                      )
                        ||
                      (
                        in_array($postingDocumentConfirmTO->depotUId, array(120, 116, 7))  //disable all updates for WMS depots loaded in clipper, turner bros no updates...
                      )
                        ||
                      ( //turn off updates for candy tops at turner bros...
                        in_array($postingDocumentConfirmTO->documentTypeUId, array(1,2,3,6,13)) &&
                        in_array($postingDocumentConfirmTO->depotUId, array(7)) &&
                        in_array($postingDocumentConfirmTO->principalUId, array(66))
                      )
                    ){

                    $skip = true;  //skip posting and return error.

                    $errorTO = new ErrorTO();
                    $errorTO->type = FLAG_ERRORTO_SUCCESS;
                    $errorTO->identifier = $postingDocumentConfirmTO->dmUId; // the uid in main table, not unsynched one
                    $errorTO->identifier2 = $postingDocumentConfirmTO->synchUId;
                    $errorTO->description = "No Update required for this status and depot";
                    $errorTOArr[] = $errorTO;

                  } else {

                    foreach ($postingDocumentConfirmTO->detailArr as $row){

                    $prodArr = $importDAO->getPrincipalProductByCode($postingDocumentConfirmTO->principalUId,$row->holdProductCode,"");

                    if (sizeof($prodArr)>0) {
                      $row->productUId = $prodArr[0]["uid"];
                    } else {
                      $skip = true;  //skip posting and return error.

                      $errorTO = new ErrorTO();
                      $errorTO->type = FLAG_ERRORTO_ERROR;
                      $errorTO->identifier = $postingDocumentConfirmTO->dmUId; // the uid in main table, not unsynched one
                      $errorTO->identifier2 = $postingDocumentConfirmTO->synchUId;
                      $errorTO->description = "The product could not be located for updating (code: '.$row->holdProductCode.')";
                      $errorTOArr[] = $errorTO;
                    }

                    }
                  }


		  if(!$skip){

            //*** NO VALIDATION ***
            $errorTO2 = $postTransactionDAO->postDocumentConfirmUpdate($postingDocumentConfirmTO); // does not create new ErrorTO each time, so this is necessary

            $errorTO = new ErrorTO();
            $errorTO->type = $errorTO2->type;
            $errorTO->identifier = $postingDocumentConfirmTO->dmUId; // the uid in main table, not unsynched one
            $errorTO->identifier2 = $postingDocumentConfirmTO->synchUId;
            $errorTO->description = $errorTO2->description;
            $errorTOArr[] = $errorTO;

            $dbConn->dbinsQuery("commit"); // must be inside loop because of rollback above, and because we return an array of errorTO's

		  }

		} else {

		  $errorTO = new ErrorTO();
          $errorTO->type = FLAG_ERRORTO_ERROR;
          $errorTO->identifier2 = $postingDocumentConfirmTO->synchUId;
          $errorTO->description="The document could not be found for updating!";
          $errorTOArr[] = $errorTO;

		}

	}

	$errorTO = new ErrorTO();
	$errorTO->type = FLAG_ERRORTO_SUCCESS;
	$errorTO->description = "WS successfully invoked.";
	$errorTO->object = $errorTOArr;

	echo base64_encode($aS->compressObject($errorTO));

   	return;

}


 function getPrincipalsForSynching ( $paramsArr ) {
	global $ROOT; global $PHPFOLDER; global $dbConn; global $importDAO; global $synchronisationDAO;

	$aS = new ArchiveStorage; // (un)serializes and (de)compressObjectes an array

	include_once($ROOT.$PHPFOLDER."TO/PostingPrincipalTO.php");

	$errorTO = new ErrorTO();
	// check credentials
	$logonTO = $paramsArr[0]["value"];
	if ($logonTO->userName!=md5(WS_PHPBACKEND_USERNAME) || ($logonTO->passWord!=md5(WS_PHPBACKEND_PASSWORD))) {
		$errorTO = new ErrorTO();
		$errorTO->type = FLAG_ERRORTO_ERROR;
		$errorTO->description = "Access to WS denied. Invalid Logon Credentials.";
		echo base64_encode($aS->compressObject($errorTO));
   		return;
	}
	$lastUpdated = $paramsArr[1]["value"]; // the date to synch up until. necessary to specify as otherwise process could run indefinitely as rows get updated.
	$rowsToReturn = $paramsArr[2]["value"];

	$principalArr = $synchronisationDAO->getPrincipalsForSynching($lastUpdated,$rowsToReturn);

	$returnArr = array();
	foreach($principalArr as $row) {
		$postingPrincipalTO = new PostingPrincipalTO();

		$postingPrincipalTO->puid = $row["uid"];
		$postingPrincipalTO->principal_code = $row["principal_code"];
		$postingPrincipalTO->name = $row["name"];
		$postingPrincipalTO->physical_add1 = $row["physical_add1"];
		$postingPrincipalTO->physical_add2 = $row["physical_add2"];
		$postingPrincipalTO->physical_add3 = $row["physical_add3"];
		$postingPrincipalTO->physical_add4 = $row["physical_add4"];
		$postingPrincipalTO->postal_add1 = $row["postal_add1"];
		$postingPrincipalTO->postal_add2 = $row["postal_add2"];
		$postingPrincipalTO->postal_add3 = $row["postal_add3"];
		$postingPrincipalTO->postal_add4 = $row["postal_add4"];
		$postingPrincipalTO->vat_num = $row["vat_num"];
		$postingPrincipalTO->rt_acc_num = $row["rt_acc_num"];
		$postingPrincipalTO->office_tel = $row["office_tel"];
		$postingPrincipalTO->email_add = $row["email_add"];
		$postingPrincipalTO->contactperson = $row["contactperson"];
		$postingPrincipalTO->suspended = $row["suspended"];
		$postingPrincipalTO->bankingDetails = $row["banking_details"];
		$postingPrincipalTO->altPrincipalCode = $row["alt_principal_code"];
		$postingPrincipalTO->lastUpdated = $row["last_updated"];
		$postingPrincipalTO->principalType = $row["principal_type"];
		$postingPrincipalTO->principalGLN = $row["principal_gln"];
		$postingPrincipalTO->status = $row["status"];

		$returnArr[] = $postingPrincipalTO;
	}

	$errorTO->type = FLAG_ERRORTO_SUCCESS;
	$errorTO->description = "WS successfully invoked.";
	$errorTO->object = $returnArr;

	echo base64_encode($aS->compressObject($errorTO));

}


// is called after php backend successfully processed list. This func then sets synched status.
function confirmPrincipalsSynched ( $paramsArr ) {
	global $ROOT; global $PHPFOLDER; global $dbConn; global $importDAO; global $synchronisationDAO;
	include_once($ROOT.$PHPFOLDER."DAO/PostSynchronisationDAO.php");

	$aS = new ArchiveStorage; // (un)serializes and (de)compressObjectes an array

	// check credentials
	$logonTO = $paramsArr[0]["value"];
	if ($logonTO->userName!=md5(WS_PHPBACKEND_USERNAME) || ($logonTO->passWord!=md5(WS_PHPBACKEND_PASSWORD))) {
		$errorTO = new ErrorTO();
		$errorTO->type = FLAG_ERRORTO_ERROR;
		$errorTO->description = "Access to WS denied. Invalid Logon Credentials.";
		echo base64_encode($aS->compressObject($errorTO));
   		return;
	}
	$runTime = $paramsArr[1]["value"];
	$resultTOArr = $paramsArr[2]["value"];

	$postSynchronisationDAO = new PostSynchronisationDAO($dbConn);

	foreach($resultTOArr as $row) {
		$errorTO = $postSynchronisationDAO->setSynchedPrincipalResult($runTime, $row->identifier, $row->type, $row->description);

		if ($errorTO->type != FLAG_ERRORTO_SUCCESS) {
			$errorTO->identifier = $row->identifier;
			$dbConn->dbinsQuery("rollback");
			echo base64_encode($aS->compressObject($errorTO));
			return;
		}
	}

	$dbConn->dbinsQuery("commit");

	$errorTO = new ErrorTO();
	$errorTO->type = FLAG_ERRORTO_SUCCESS;
	$errorTO->description = "WS successfully invoked.";

	echo base64_encode($aS->compressObject($errorTO));

}

function getDownloadCounts ( $paramsArr ) {
	global $ROOT; global $PHPFOLDER; global $dbConn; global $importDAO; global $synchronisationDAO;

	// check credentials
	$logonTO = $paramsArr[0]["value"];
	if ($logonTO->userName!=md5(WS_PHPBACKEND_USERNAME) || ($logonTO->passWord!=md5(WS_PHPBACKEND_PASSWORD))) {
		$errorTO = new ErrorTO();
		$errorTO->type = FLAG_ERRORTO_ERROR;
		$errorTO->description = "Access to WS denied. Invalid Logon Credentials.";
		echo base64_encode(serialize($errorTO));
   		return;
	}

	$downloadCntsArr = $synchronisationDAO->getDownloadCounts();

	if (sizeof($downloadCntsArr)==0) {
		$errorTO = new ErrorTO();
		$errorTO->type = FLAG_ERRORTO_ERROR;
		$errorTO->description = "ERROR! No downloads to retrieve.";
		echo base64_encode(serialize($errorTO));
		return;
	}

	$errorTO = new ErrorTO();
	$errorTO->type = FLAG_ERRORTO_SUCCESS;
	$errorTO->description = "WS successfully invoked.";
	$errorTO->object = $downloadCntsArr;

	echo base64_encode(serialize($errorTO));

}

function getDownloadErrors ( $paramsArr ) {
	global $ROOT; global $PHPFOLDER; global $dbConn; global $importDAO; global $synchronisationDAO;

	// check credentials
	$logonTO = $paramsArr[0]["value"];
	if ($logonTO->userName!=md5(WS_PHPBACKEND_USERNAME) || ($logonTO->passWord!=md5(WS_PHPBACKEND_PASSWORD))) {
		$errorTO = new ErrorTO();
		$errorTO->type = FLAG_ERRORTO_ERROR;
		$errorTO->description = "Access to WS denied. Invalid Logon Credentials.";
		echo base64_encode(serialize($errorTO));
   		return;
	}

	$source = $paramsArr[1]["value"];

	switch ($source) {
		case "PRIN":
			$downloadCntsArr = $synchronisationDAO->getDownloadPrincipalErrors();
			break;
		case "PPRD":
			$downloadCntsArr = $synchronisationDAO->getDownloadPrincipalProductErrors();
			break;
		case "PST":
			$downloadCntsArr = $synchronisationDAO->getDownloadPrincipalStoreErrors();
			break;
		case "DM":
			$downloadCntsArr = $synchronisationDAO->getDownloadDocumentErrors();
			break;
		default:
			echo "INVALID source type passed to WS getDownloadErrors";
			return;
	}

	if (sizeof($downloadCntsArr)==0) {
		$errorTO = new ErrorTO();
		$errorTO->type = FLAG_ERRORTO_ERROR;
		$errorTO->description = "ERROR! No downloads to retrieve.";
		echo base64_encode(serialize($errorTO));
		return;
	}

	$errorTO = new ErrorTO();
	$errorTO->type = FLAG_ERRORTO_SUCCESS;
	$errorTO->description = "WS successfully invoked.";
	$errorTO->object = $downloadCntsArr;

	echo base64_encode(serialize($errorTO));

}


/* Function for when the request method name
doesn't exist */
function phpRPC_method_not_found($methodName){

}
?>

