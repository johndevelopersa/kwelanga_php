<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once ($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER.'properties/dbSettings.inc');
include_once($ROOT.$PHPFOLDER."TO/PostingDocumentTO.php");
include_once($ROOT.$PHPFOLDER."TO/PostingDocumentDetailTO.php");
include_once($ROOT.$PHPFOLDER."DAO/ImportDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/ProductDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/StoreDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/PostTransactionDAO.php");

set_time_limit(60*5);

// if (isset($_GET["user"])) $user=$_GET["user"]; else if (isset($_POST["user"])) $user=$_POST["user"]; else $user="";


// echo $_GET["user"];
//  echo "kkk";

// if ($user!="1976") {
//	echo "Access Denied";
//	return;
// }

$content = trim(file_get_contents("temp/sor20141229.csv")); // this automatically leaves off trailing blank lines
if ((strlen($content)==0) || ($content===false)) {
	echo "file empty or not found!";
	return;
}

$dbConn = new dbConnect();
$dbConn->dbConnection();

$importDAO = new ImportDAO($dbConn);
$postTransactionDAO = new PostTransactionDAO($dbConn);
$productDAO = new ProductDAO($dbConn);
$storeDAO = new StoreDAO($dbConn);

$fileArr = explode("\n",$content);
$sCnt=$eCnt=0;

$ignoreInvalidProductLines = true;
$ignoreInvalidStores = true; // Rows with the last column (Skip Import) set to "Y" will be ignored regardless
if ($ignoreInvalidProductLines) echo "<p style='color:green;'>Invalid Products will be IGNORED. Rest of same document will be imported.</p>";
if ($ignoreInvalidStores) echo "<p style='color:green;'>Documents with Invalid Stores will be IGNORED.</p>";

// remove first row - header
echo "<span style='color:red'>First ROW Removed - assumed to be header !";
echo $fileArr[0]."</span><br>";
unset($fileArr[0]);

session_start();
$_SESSION["user_id"]=SESSION_ADMIN_USERID;

// put into a header-detail array
$SORs = array();
$bypassed=array();
foreach ($fileArr as $key=>$line) {
  $fArr = explode(",",$line);
  // I've inserted col K and to bypass SORs if they give errors, last columns for some reason need to be trimmed !
  if ((!isset($fArr[10])) || (trim($fArr[10])!="Y")) {
    $SORs[$fArr[0]][]=$fArr;
  } else {
    $bypassed[$fArr[0]]="Bypassed Waybill {$fArr[0]}";
  }
}
echo implode("<br>",$bypassed)."<br>";

$hasErrors=false; // only commit if no errors

foreach ($SORs as $doc) {

  $wayBill = $doc[0][0];
	$oldAccount = trim($doc[0][9]); // is this correct ?
	$mfS=array();
	if (trim($oldAccount)!="") {
	 $mfS = $importDAO->getPrincipalStoreByOldAccount(3,$oldAccount,"");
	}

	if (sizeof($mfS)==0) {

    // try the syspro account special field as an alternative
    $mfS=$storeDAO->getPrincipalStoreBySF(3, 10, $oldAccount, "");

    if (sizeof($mfS)==0) {

      if ($ignoreInvalidStores) {

        echo "Could not import WayBill {$wayBill} as Store could not be found from old_account = {$oldAccount}. Document Ignored.<br>";
        continue;

      } else {

    		$eCnt++;
    		echo "Could not import WayBill {$wayBill} as Store could not be found from old_account = {$oldAccount}.<br>";
        $hasErrors=true;
        continue;

      }

    }
	}

	$dTO = new PostingDocumentTO();
	$dTO->DMLType = "INSERT";
	$dTO->principalUId = 3;
	$dTO->depotUId = $mfS[0]["depot_uid"];
	$dTO->documentNumber = $wayBill;
	$dTO->documentTypeUId = 17; // Customer defined Document Type = SOR
	$dTO->processedDate = gmdate(GUI_PHP_DATE_FORMAT);
	$dTO->processedTime = gmdate(GUI_PHP_TIME_FORMAT);
	$dTO->mergedDate = $dTO->processedDate;
	$dTO->mergedTime = $dTO->processedTime;
	$dTO->validationDate = $dTO->processedDate;
	$dTO->validationTime = $dTO->processedTime;
	$dTO->validationStatus = 2; // unknown
	// $dTO->incomingFile = $postingOrderTO->incomingFileName;
	$dTO->TransmissionFlag1 = $dTO->TransmissionFlag2 = $dTO->TransmissionFlag3 = $dTO->TransmissionFlag4 = "0";
	// $dTO->orderSequenceNo = $postingOrderTO->orderSequenceNo;
	$dTO->orderDate = $dTO->processedDate;
	$dTO->invoiceDate = $dTO->processedDate;
	$dTO->deliveryDate = $dTO->processedDate;
	$dTO->documentStatusUId = 81; // Processed
	$dTO->principalStoreUId = $mfS[0]["uid"];

	$dTO->customerOrderNumber = "";
	$dTO->cases = $dTO->sellingPrice = $dTO->exclusiveTotal = $dTO->vatTotal = $dTO->invoiceTotal =0;
	$dTO->dataSource = DS_EDI;
	$dTO->capturedBy = "RT DEV";
	//$dTO->buyerAccountReference = $postingOrderTO->buyerAccountReference;

	// NB : Do NOT use $$postingOrderDetailTO as the "as $postingOrderDetailTO" because it overwrites all the original array values !
  foreach ($doc as $key=>$row) {
  	$ddTO = new PostingDocumentDetailTO();
  	$ddTO->lineNo = $key;
  	$ddTO->clientLineNo = $key;

    $mfP = $productDAO->getPrincipalProductByCode(3, trim($row[3]));
    if (sizeof($mfP)==0) {

      if ($ignoreInvalidProductLines) {

        echo "<br>WayBill {$wayBill} has invalid product ({$row[3]})! Product Line ignored.<br>";
        continue;

      } else {

        echo "<br>Could not import WayBill {$wayBill} product ({$row[3]}) as this product could not be found! Entire import stopped and rolled back.<br>";
        $dbConn->dbinsQuery("rollback;");
        //return;
        $eCnt++;
        $hasErrors=true;
        continue;
      }

    }

  	$ddTO->productUId = $mfP[0]["uid"];
    $ddTO->orderedQty = $ddTO->documentQty = $ddTO->deliveredQty = $row[6];
  	$ddTO->sellingPrice = $ddTO->discountValue = $ddTO->netPrice = $ddTO->extendedPrice = $ddTO->vatAmount = $ddTO->vatRate = $ddTO->total = $ddTO->pallets = 0;
  	// $ddTO->discountReference = $TO->discountReference;

  	$dTO->detailArr[] = $ddTO;
  }

	// do the posting into TT
	$result = $postTransactionDAO->postDocument($dTO, $pWebSourceChecksAlreadyDone=false);
	if ($result->type!=FLAG_ERRORTO_SUCCESS) {
		$eCnt++;
		echo "Could not import WayBill {$wayBill}. {$result->description}.<br>";
	  $dbConn->dbinsQuery("rollback;");
    continue;
	}

	$sCnt++;

}

if ($hasErrors) {
  echo "<br>WARNING !! The Entire Import was NOT SAVED as there were errors !!<br>";
  $dbConn->dbinsQuery("rollback;");
} else {
  $dbConn->dbinsQuery("commit;");
}
echo "<br><br>Imported {$eCnt} (Errors) : {$sCnt} (Successful).";

?>