<?php

global $postREPORTID;

$reportNo = ((isset($_GET["REPORTID"]))?$_GET["REPORTID"]:"hjghjfgh");

echo "Rep No  ";

echo  $reportNo;

print_r($paramsArr);

echo $postREPORTID;

if (isset($paramsArr['p1']) && isset($paramsArr['p2'])) {
      $EndDate       = $paramsArr['p2'];
      $StartDate     = substr($paramsArr['p1'],6,10); 
}	else {
      echo "No Date set"; // Should never get here
}

$CustomerUid   = trim(substr($paramsArr['p3'],1,10));
$PaymentBy     = substr($paramsArr['p3'],0,1);
$custList      = explode(",",$paramsArr['p3']);
$emailself     = $paramsArr['p4'];
$emailCustomer = $paramsArr['p4'];

foreach($custList as $row1) {
     $CustomerUid   = trim(substr($row1,1,10));
     $PaymentBy     = substr($row1,0,1);
     
     echo $CustomerUid;
     echo "<br>";
     
     include_once $ROOT.$PHPFOLDER."functional/reports/phpreports/DetailedLedger_Extract.php";
	
	}



?>