<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once ($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER.'properties/dbSettings.inc');

if (isset($_GET["user"])) $user=$_GET["user"]; else if (isset($_POST["user"])) $user=$_POST["user"]; else $user="";
if (isset($_POST["tbl"])) $tbl=$_POST["tbl"]; else $tbl="";
if (isset($_POST["sql"])) $sql=$_POST["sql"]; else $sql="";

if ($user!="1976") {
	echo "Access Denied";
	return;
}

$htmlBody="<body style='font-family:courier; font-size:8px;'>";
ob_start(); //Turn on output buffering

echo "<form name='pform' action='".$_SERVER["PHP_SELF"]."' method='post'>";
echo "<input type='hidden' name='user' value='{$user}' />
	  Table to insert into:<br><input name='tbl' value='{$tbl}' /><br>
	  SQL:<br><textarea name='sql' cols=80 rows=20>{$sql}</textarea><br>
	  <input type='button' value='submit' onclick='document.pform.submit();' />
	 ";

echo "</form>";
$htmlBody=ob_get_clean();

if ($sql=="") { echo $htmlBody; return; }

if (preg_match("/(UPDATE |DELETE |DROP)/i",$sql)) {
	echo "Only SELECT allowed in sql query!";
	return;
}

$htmlBody="";

$dbConn = new dbConnect();
if (strpos($sql,"retailtr_maindb")!==false) {
  $dbConn->dbConnectionHistorical();
} else {
  $dbConn->dbConnection();  
}


// first get the nullables

$dbConn->dbQuery("select column_name, is_nullable, data_type
					from information_schema.columns
					where table_schema = '".DATABASE."'
					and table_name = '{$tbl}'");
if (!$dbConn->dbQueryResult) {
	echo "Could not get table schema: ".mysql_error($dbConn->connection);
	return;
}
$schema=array();
while($row = mysql_fetch_array($dbConn->dbQueryResult,MYSQL_ASSOC)){
	$schema[$row["column_name"]]["is_nullable"]=$row["is_nullable"];
	$schema[$row["column_name"]]["data_type"]=$row["data_type"];
}


$dbConn->dbQuery($sql);

if (!$dbConn->dbQueryResult) {
	echo "Error in SQL:".mysql_error($dbConn->connection);
	return;
}

$i=0;
while($row = mysql_fetch_array($dbConn->dbQueryResult,MYSQL_ASSOC)){
	if ($i==0) {
		$htmlBody.="insert into {$tbl} values ";
	} else if (fmod($i,1000)==0) {
		$htmlBody.=";\ninsert into {$tbl} values ";
	}
	$line="";
	foreach ($row as $key=>$field) {
		if ($field=="") {
			if (($schema[$key]["data_type"]=="varchar") || ($schema[$key]["is_nullable"]=="NO")) {
				$line.=($line=="")?'':",''";
			} else if ($schema[$key]["is_nullable"]=="YES") {
				$line.=($line=="")?"NULL":",NULL";
			} else {
				$line.=($line=="")?"NULL":",NULL";
			}
		} else {
			$line.=($line=="")?"'".addSlashes($field)."'":",'".addSlashes($field)."'";
		}
	}
	$htmlBody.=(($i==0) || (fmod($i,1000)==0))?"({$line})":",({$line})";
	$i++;
}

$htmlBody.=";"; // dont forget to add the semicolon for last row

$htmlBody = gzencode($htmlBody, 9, FORCE_GZIP);
header("Content-Type: application/force-download");
header("Content-Disposition: filename=\"data.sql\"");
header("Content-Encoding: gzip");
header("Content-Length: ".strlen($htmlBody));

echo $htmlBody;

?>