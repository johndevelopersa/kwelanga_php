<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'properties/dbSettings.inc');
include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');
include_once($ROOT.$PHPFOLDER.'DAO/db_Connection_Class.php');
include_once($ROOT.$PHPFOLDER."TO/ErrorTO.php");

$errorTO = new ErrorTO();

$dbConn = new dbConnect();
$dbConn->dbConnection();

$dbConn->dbQuery("select deliver_name, uid from principal_store_master where stripped_deliver_name is null or stripped_deliver_name = ''");
$outerRS = $dbConn->dbQueryResult;

while($row = mysql_fetch_array($outerRS,MYSQL_ASSOC)) {
	$after = CommonUtils::getStrippedValue($row["deliver_name"]);
	$dbConn->dbinsQuery("update principal_store_master set stripped_deliver_name = '".$after."' where uid = ".$row["uid"]);
}

if ($dbConn->dbQueryResult) {
	echo "Successful. ".mysql_info($dbConn->connection);
	$infoArr=CommonUtils::getMysqlInfo(mysql_info($dbConn->connection));
	echo "<pre>";
	print_r($infoArr);
	echo "</pre>";
} else {
	echo "Failed. ".mysql_error($dbConn->dbConnection);
}

$dbConn->dbinsQuery("commit");

?>
