<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once ($ROOT.$PHPFOLDER.'functional/reports/phpreports/transporter/transporterOwnerClass.php');
    include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
    include_once($ROOT.$PHPFOLDER."DAO/ReportsDAO.php");    
		    
    if (!isset($_SESSION)) session_start() ;
      $userUId     = $_SESSION['user_id'] ;
      $principalId = $_SESSION['principal_id'] ;

      //Create new database object
      $dbConn  = new dbConnect(); 
      $dbConn->dbConnection();
      $errorTO = new ErrorTO;

?>
<!DOCTYPE html>
<HTML>
   <head>

		<TITLE>Transporter Report</TITLE>

		<link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/1_kwelanga.css' rel='stylesheet' type='text/css'>
    <link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/kos_standard_screen2.css' rel='stylesheet' type='text/css'>
		<script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/jquery.js"></script>
		<script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/dops_global_functions.js"></script>

    <style> 
    	
    	</style>

   </head>
   <body>
   <?php
   
   if(isset($_POST['ownerform'])) {
   	
   	
   	print_r($_POST['TRANSP']);
   	
   	echo "<br>";
   	   	 echo $_POST['WAREH'];
   	   	 echo "<br>";
   	   	 echo $_POST['TODAT'];
   	   	 echo "<br>";
   	   	 echo $_POST['FROMDAT'];
   	   	 echo "<br>";
   	
   	
   	
   	}
   
   
   
   
   
   

   if(isset($_POST['firstform'])) {
   	   if($_POST['WAREHOUSE'] <> 'Select a Warehouse' ) {
   	   	   
         $transportOwner = new transportOwner();
         $a = $transportOwner->ownerForm(substr($_POST['WAREHOUSE'],0,strpos($_POST['WAREHOUSE'],'-')),
                                         $_POST['TODATE'],
                                         $_POST['FROMDATE']);
   	   	 echo "<br>";
   	   	
   	   } else {?>
           <script type='text/javascript'>parent.showMsgBoxError('Warehouse Not Selected <br><br> Try Again')</script>
           <?php	
           unset($_POST['firstform']);
   	   }
   } 
   
   
   if(!isset($_POST['firstform'])) {
         $transportOwner = new transportOwner();
         $a = $transportOwner->firstform($userUId, $principalId);             	
   } ?>
   </body>       
</HTML>

<?php

 ?> 
