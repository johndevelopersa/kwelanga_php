<?php

/* * ********************************************************************************************
 * *
 * *  This job can run as many times per day as is necessary according to job scheduler.
 * *
 * *  It executes notifications that occur throughout the day by triggers
 * *
 * ******************************************************************************************** */

include_once('ROOT.php');
include_once($ROOT . 'PHPINI.php');
include_once($ROOT . $PHPFOLDER . "functional/extracts/daily/extractController.php");
include_once $ROOT.$PHPFOLDER.'properties/Constants.php';
include_once $ROOT.$PHPFOLDER.'libs/CommonUtils.php';
include_once $ROOT.$PHPFOLDER.'TO/ErrorTO.php';
include_once $ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php';

//static method handler.
class BillExtract {
  public static function generateOutput(){
    $className = basename(__FILE__,'.php').'Init';
    global $ROOT, $PHPFOLDER, $dbConn;
    $obj = new $className();
    return $obj->generateOutput();
  }
}

BillExtract();



function BillExtract() {
	
	
		 $bb = BillData();
		 
		 print_r($bb);
		 

    } 
		  
		  
		  
		  
		  
		  
			function BillData() {
				
					$sql = mysql_query("select * from `billing_temp`");
					
					$resultrows = mysql_num_rows($sql);  
					
					echo $resultrows;
					
					if ($resultrows > 0) {
					
					while($row = mysql_fetch_array($sql,MYSQL_ASSOC)){
						
						echo $row;
							$arr[] = $row;
							}
					}
			
//					mysql_free_result($resultrows);

					return $arr;
					}
					



