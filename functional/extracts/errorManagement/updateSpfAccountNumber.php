<?php

    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    include_once($ROOT.$PHPFOLDER ."DAO/db_Connection_Class.php");
		include_once($ROOT.$PHPFOLDER .'libs/GUICommonUtils.php');
		include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');
    include_once($ROOT.$PHPFOLDER ."DAO/messagingDAO.php");
    include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
    include_once($ROOT.$PHPFOLDER.'DAO/SgxImportDAO.php');

    $errorTO = new ErrorTO;

    //Create new database object
    $dbConn = new dbConnect(); 
    $dbConn->dbConnection();

?>

<!DOCTYPE html>
<html>
	  <head>
        <title>Import Transaction Management</title>
        <link href='<?php echo $ROOT.$PHPFOLDER ?>css/1_kwelanga.css' rel='stylesheet' type='text/css'>
        <script type="text/javascript" language="javascript" src="<?php echo $ROOT.$PHPFOLDER ?>js/jquery.js"></script>
        <script type="text/javascript" language="javascript" src="<?php echo $ROOT.$PHPFOLDER ?>js/dops_global_functions.js"></script>
    </head>
    
<body>
<?php 

if($_POST["canform"] == 'Cancel') {
	 ?>
	    <script>alert("Account Number Update Cancelled");</script>		    
   <?php
      return;
   }
   
// print_r($_POST);

// Only need to Insert if blank - If rejected or wrong I do not know

             $sgxUpdate = new SgxImportDAO($dbConn);
             $errorTO   = $sgxUpdate->insertSfdAccount(test_input($_POST["SFFUID"]),
                                                       test_input($_POST["PSUID"]),
                                                       test_input($_POST["OMNIACC"]));
                                                       
             if ($errorTO->type!=FLAG_ERRORTO_SUCCESS) { ?>
                  <script>alert("Special Field Account Update Failed");</script>	
             <?php 
             } else { ?>
                  <script>alert("Special Field Account Update Successful");</script>	
             <?php 
             } ?>
</body>

</html>
<?php
 function test_input($data) {

  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  
  return $data;
 }
?> 
