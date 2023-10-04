<?php
    include_once('ROOT.php'); 
    include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
    include_once($ROOT.$PHPFOLDER."libs/GUICommonUtils.php");
    include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');    
    include_once('employeeRecordingScreens.php');    

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
   <HEAD>

		<TITLE>Nellwyn Employee recording</TITLE>

    <link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/kos_standard_screen.css' rel='stylesheet' type='text/css'>
		<link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/1_kwelanga.css' rel='stylesheet' type='text/css'>
		<script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/jquery.js"></script>
		<script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/dops_global_functions.js"></script>

    <style>
       table.box {border:collapse;
                  border: 2px solid; 
       	          border-color: #990000; 
      	          background:   #fcecec }     
    </style>

   </HEAD>
   <?php
    if(isset($_POST['CANFORM'])) { 
        return;	    
// ***********************************************************************************************************************************************    
    if (!isset($_POST['FIRSTFORM']) {
    	
    	
    	    include_once('employeeRecordingScreens.php');
    	// 1. If FIRST FORM and NO OF BOXES is not set, proceed to first form. 
        $employeeRecordingScreens = new cemployeeRecordingScreens();
         $a = $employeeRecordingScreens->firstform($postUPLIFTNO);              //  firstform = function contained in the class

    } ?> 
</HTML>

<?php
 function test_input($data) {

  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
    
  return $data;
 }
 ?> 

  