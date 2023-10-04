<?php

    // https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/functional/ws/api/test/testApiCall.php";

    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
    include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");  
    include_once('testApiCallClass.php');


    //Create new database object
    $dbConn  = new dbConnect(); 
    $dbConn->dbConnection();
    $errorTO = new ErrorTO;

?>
<!DOCTYPE html>
<HTML>
   <HEAD>

		<TITLE>Simple Form</TITLE>

		<link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/1_kwelanga.css' rel='stylesheet' type='text/css'>
		<script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/jquery.js"></script>
		<script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/dops_global_functions.js"></script>

    <style>
      td.head1 {font-weight:normal;
                font-size:20px;text-align:left; 
                font-family: Calibri, Verdana, Ariel, sans-serif; 
                padding: 0 150px 0 150px }
                
      td.det1  {border-style:none; 
                text-align: left; 
                font-weight: bold; 
                font-size: 13px;
                padding: 0 150px 0 150px  }
                
      table.box {border:collapse;
      	         border: 2px solid; 
      	         border-color: #990000; 
      	         background: #fcecec }          
    	
    	</style>

   </HEAD>
      <body>
<?php


      
     if (!isset($_POST['firstform'])) {
         $testApiForm = new testApiForm();
         $a = $testApiForm->firstform();
     }

?>

      </body>       
</HTML>

<?php
 function test_input($data) {

  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  if($data=='') { $data=0; } 
    
  return $data;
 }
 ?> 
