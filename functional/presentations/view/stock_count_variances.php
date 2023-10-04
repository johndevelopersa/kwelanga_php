<?php

/*
 *
 *      OUTPUTS PDF latest
 *
 */
 
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER."DAO/StockByCatDAO.php");

include_once ($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER.'properties/dbSettings.inc');

require_once($ROOT . $PHPFOLDER . 'libs/pdf/tcpdf/config/lang/eng.php');
require_once($ROOT . $PHPFOLDER . 'libs/pdf/tcpdf/tcpdf.php');


$category = ((isset($_GET["CATEGORY"]))?$_GET["CATEGORY"]:"");
$prin     = ((isset($_GET["PRIN"]))?$_GET["PRIN"]:"");
$dep      = ((isset($_GET["WH"]))?$_GET["WH"]:"");
$varType  = ((isset($_GET["VARTYPE"]))?$_GET["VARTYPE"]:"");
$varList  = ((isset($_GET["VARLIST"]))?$_GET["VARLIST"]:"");
echo "<br>";
echo $category; 
echo "<br>";        
echo $prin  ; 
echo "<br>";         
echo $dep ;  
echo "<br>";          
echo $varType ;
echo "<br>";        
echo $varList ;
echo "<br>here";        


//Create new database object  
$dbConn = new dbConnect(); 
$dbConn->dbConnection();


// print_r($prodSheet); 

if (!isset($_SESSION)) session_start();


// Extend the TCPDF class to create custom Header and Footer







//============================================================+
// END OF FILE
//============================================================+

?>