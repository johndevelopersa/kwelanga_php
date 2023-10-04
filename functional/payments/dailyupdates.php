<?php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once ($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER."DAO/PaymentsDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/PostPaymentsDAO.php");
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');

$camErrTO = new ErrorTO;

if (!isset($_SESSION)) session_start();
$_SESSION['user_id'] = SESSION_ADMIN_USERID;

//Create new database object
$dbConn = new dbConnect(); 
$dbConn->dbConnection();

//$PostPaymentsDAO   = new PostPaymentsDAO($dbConn);
//$camErrTO           = $PostPaymentsDAO->AutoMatchCredits($je['principal_uid']);

//echo '<br>';
//echo "End of Invoices and Credit Matching";

// calculcate update period

If(date("m")==1) {
       $previousPeriod = date("Y")-1 .'12';
} else {
       $previousPeriod = date("Y").date("m")-1;
}
$currentPeriod = date("Y").date("m");

echo $previousPeriod;
echo "<br>";
echo $currentPeriod;
echo "<br>";
// $currentPeriod = "201812";

include_once($ROOT.$PHPFOLDER."functional/payments/UpdateMonthendBalancesNew.php");

if(date("d") <= 5 ) {     // recalculte last months balance until 5th of new month
	
	    $UpdateMonthend = new UpdateMonthend($dbConn);
      $errorTO = $UpdateMonthend->UpdateMonthendBalancesNew($principalUid, $previousPeriod, $updateType, '', $updateBatch );
      // Update last month here
      echo '<br>';
      echo "Last Month Balances Updated " . $previousPeriod . " - Successful " . $errorTO->identifier . " Faailed - " . $errorTO->identifier2 ;

} 

	    $UpdateMonthend = new UpdateMonthend($dbConn);
      $errorTO = $UpdateMonthend->UpdateMonthendBalancesNew($principalUid, $currentPeriod, $updateType, '', $updateBatch );
      // Update last month here
      echo '<br>';
      echo "Current Month Balances Updated " . $currentPeriod . "- Successful " . $errorTO->identifier . " Faailed - " . $errorTO->identifier2 ;
      echo '<br>';
      return $errorTO;
?>