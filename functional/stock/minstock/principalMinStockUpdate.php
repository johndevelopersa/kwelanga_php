<?php
    include_once('ROOT.php'); 
    include_once($ROOT.'PHPINI.php');
    include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
    include_once('principalMinStockUpdateClass.php');
    include_once($ROOT.$PHPFOLDER.'DAO/updateStockDAO.php');
    include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
    include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
 
    if (!isset($_SESSION)) session_start();
    
     	$principalId = $_SESSION['principal_id'] ;
      $depotId     = $_SESSION['depot_id'] ;
      $userID 		 = $_SESSION['user_id'] ;
      
      //Create new database object
      $dbConn  = new dbConnect(); 
      $dbConn->dbConnection();
      $errorTO = new ErrorTO;
      
  ?>
	<!DOCTYPE html>
	<HTML>
   <head>

		<TITLE>Principal Depot Report</TITLE>

		<link href='<?php echo $ROOT.$PHPFOLDER ?>css/1_kwelanga.css' rel='stylesheet' type='text/css'>
    <link href='<?php echo $ROOT.$PHPFOLDER ?>css/kos_standard_screen2.css' rel='stylesheet' type='text/css'>
		<script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/jquery.js"></script>
		<script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/dops_global_functions.js"></script>

    <style> 
    
    	
    	</style>

   </head>
   <body>
   <?php
   
   if(isset($_POST['backform'])) {	
       unset($_POST['firstform']);
       unset($_POST['prodListForm']);
       unset($_POST['PRODLISTFORM']);
   }

   if(isset($_POST['canform'])) { 
         return;
   }
   
   
if(isset($_POST['PRODLISTFORM'])) {	
	         $doUpdate = 'Y';

	         for ($y = 0; $y <= count($_POST['PRODLISTUID']); $y++) {	
	         	 
	     	       $updateStockDAO = new updateStockDAO($dbConn);     
               $errorTO = $updateStockDAO->validateMinimumStockQuantity(test_input($_POST['PRODLISTFORM'][$y]));
               if($errorTO->type <> FLAG_ERRORTO_SUCCESS) { ?>
                   <script type='text/javascript'>parent.showMsgBoxError('<?php echo $errorTO->description; ?>')</script>
                    <?php	
                    $doUpdate = 'N';
                    unset($_POST['firstform']);
                    unset($_POST['prodListForm']);
                 }
           } 
           
           if ($doUpdate == 'Y'	) {
                 $uSuccess = 'Y';
                 for ($x = 0; $x < count($_POST['PRODLISTUID']); $x++) {
           	
  	                $updateStockDAO = new updateStockDAO($dbConn);     
                    $errorTO = $updateStockDAO->updateMinimumStockQuantity(test_input($_POST['PRODLISTFORM'][$x]), $_POST['PRODLISTUID'][$x]); 
                    
                    if($errorTO->type <> FLAG_ERRORTO_SUCCESS) {
                        $uSuccess = 'N';
                    }	
                 }
                 if($uSuccess=='Y') { ?>
                         <script type='text/javascript'>parent.showMsgBoxInfo('Success - Minimum Quantities Updated')</script>
                         <?php	
                         unset($_POST['firstform']);
                         unset($_POST['prodListForm']);
                 } else {?>
                         <script type='text/javascript'>parent.showMsgBoxInfo('Minimum Quantities Update Failed (X007)<br>Contact Support')</script>
                         <?php	
                         unset($_POST['firstform']);
                         unset($_POST['prodListForm']);
                 }
           }	
}
   
      if(isset($_POST['firstform'])) {
   	   if($_POST['WAREHOUSE'] <> 'Select a Warehouse' ) {
      /*   echo "<br>"; 
         echo $_POST['WAREHOUSE'];
   	   	 echo "<br>";*/  
         $principalDepot = new principalDepot();
         $a = $principalDepot->prodListForm($principalId, substr($_POST['WAREHOUSE'],0,strpos($_POST['WAREHOUSE'],'-')));       
         
	   	// substr($string, Start Length)
   	   	
   	   } else {?>
           <script type='text/javascript'>parent.showMsgBoxError('Warehouse Not Selected (x001)<br><br> Try Again')</script>
           <?php	
           unset($_POST['firstform']);
   	   }
   } 
   
   
   if(!isset($_POST['firstform']) && !isset($_POST['prodListForm'])) {
   	
         $principalDepot = new principalDepot();
         $a = $principalDepot->firstform($userID, $principalId);          	
   } ?>
   </body>       
</HTML>
<?php
 function test_input($data) {

  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  if($data=='') { $data=0; } 
  if($data==NULL) { $data=0; } 
    
  return $data;
}
 ?> 
