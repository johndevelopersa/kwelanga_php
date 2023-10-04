<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER."properties/Constants.php");
    include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
    include_once($ROOT.$PHPFOLDER."libs/CommonUtils.php");
    include_once($ROOT.$PHPFOLDER.'DAO/SimpleFormDAO.php');    
    include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");

    include_once("simpleFormScreens.php");

    if (!isset($_SESSION)) session_start() ;
    if (!isset($_SESSION)) session_start() ;
      $userUId      = $_SESSION['user_id'] ;

      //Create new database object
      $dbConn = new dbConnect(); 
      $dbConn->dbConnection();
      $errorTO = new ErrorTO;
      
if (isset($_POST['BACKFORM'])) {
           unset($_POST['MODEMPSP']); 
           unset($_POST['ADDEMPSP']);
           unset($_POST['SUBEMPUPD']);
    }
if (isset($_POST['CANFORM'])) {
          return;    
    }

if(isset($_POST['SUBEMPUPD'])) {
	     
	        $SimpleFormDAO = new SimpleFormDAO($dbConn);
          $errorTO = $SimpleFormDAO->insertJobFunction($_POST['EJOB']);    
          
          if($errorTO->type!=FLAG_ERRORTO_SUCCESS) {?>
                         <script type='text/javascript'>parent.showMsgBoxError('Error! Inserting Record')</script>
                         <?php	
          } else {?>
                         <script type='text/javascript'>parent.showMsgBoxInfo('Job Record Saved Successfully')</script>
                         <?php
          }
}     
        
      
      
?>
<!DOCTYPE html>
<HTML>
	<HEAD>

		<TITLE>Job Update Screen</TITLE>

    <link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/kos_standard_screen2.css' rel='stylesheet' type='text/css'>
		<link href="<?php echo $DHTMLROOT.$PHPFOLDER.'css/css.php?SYSID='.$systemId.'&SYSNAME='.$systemName ?>" rel="stylesheet" type="text/css" />
		<script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/jquery.js"></script>
		<script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/dops_global_functions.js"></script>

    <style>
      td.head1 {font-weight:bold;
                font-size:17px;text-align:left; 
                font-family: Calibri, Verdana, Ariel, sans-serif; 
                padding: 0 150px 0 150px }

      td.head2 {font-weight:normal;
                font-size:15px;text-align:left; 
                font-family: Calibri, Verdana, Ariel, sans-serif; 
                padding: 0 150px 0 150px }
                
    </style>
		</HEAD>
    <BODY>
    <?php
    
if(!isset($_POST['MODEMPSP']) && !isset($_POST['ADDEMPSP'])) {
       $simpleFormScreens = new simpleFormScreens();
       $a = $simpleFormScreens->pickUpdateAction(""); 	
}    
    
 //ADD Radio Button Press     
if(isset($_POST['ADDEMPSP'])) {

	  $simpleFormScreens = new simpleFormScreens();
    $a = $simpleFormScreens->firstform(""); 
         
}      
elseif(isset($_POST['MODEMPSP'])) {
	


	  $simpleFormScreens = new simpleFormScreens();
    $a = $simpleFormScreens->ModifyJobDetails(""); 
			

          
		echo 'modify pressed';
		
		
	}
else {

	}    
    

    
     
?>
 </BODY>
</HTML>