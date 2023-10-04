<?php

    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    include_once($ROOT.$PHPFOLDER ."DAO/db_Connection_Class.php");
		include_once($ROOT.$PHPFOLDER .'libs/GUICommonUtils.php');
		include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');
    include_once($ROOT.$PHPFOLDER ."DAO/messagingDAO.php");
    include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');

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
	    <script>alert("Transaction Delete Cancelled");</script>		    
   <?php
      return;
   } 

$messagingDAO = new messagingDAO($dbConn);
$errorTO = $messagingDAO->setTransactionToDelete($_POST["txUid"]);

if ($errorTO->type!=FLAG_ERRORTO_SUCCESS) { ?>
	    <script>alert("Delete Failed");</script>	
<?php } else { ?>
	    <script>alert("Transaction Delete Successful");</script>	
<?php } ?>

</body>

</html>
