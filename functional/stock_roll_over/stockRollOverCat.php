<?php 
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER."properties/Constants.php");
    include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
    include_once($ROOT.$PHPFOLDER."libs/CommonUtils.php");
    include_once($ROOT.$PHPFOLDER.'DAO/StockRollOverDAO.php');    
    include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");

    include_once("stockRollOverCatScreens.php");
   
   if (!isset($_SESSION)) session_start() ;
      $userUId      = $_SESSION['user_id'] ;
      $principalId  = $_SESSION['principal_id'] ;
      $wareHouseCde = $_SESSION['depot_id'] ;
      $systemName   = $_SESSION['system_name'] ;

      //Create new database object
      $dbConn = new dbConnect(); 
      $dbConn->dbConnection();
      $errorTO = new ErrorTO;
      
?>

<!DOCTYPE html>
<HTML>
	<HEAD>

		<TITLE>Roll Over Stock By Category</TITLE>

    <link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/kos_standard_screen2.css' rel='stylesheet' type='text/css'>
		<link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/1_kwelanga.css' rel='stylesheet' type='text/css'>
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
//****************************************************************************************************************************************************************

      if (isset($_POST['SUBMIT'])) {
        
           foreach($_POST['CATLIST'] as $row) {
        	
                $CatStockRollOverDAO = new CatStockRollOverDAO($dbConn);        	
                $contRoll = $CatStockRollOverDAO->validateRollOver($principalId, $wareHouseCde, $row, $_POST['FROMDATE']);
             
                if($contRoll == 'T') {
                      $CatStockRollOverDAO = new CatStockRollOverDAO($dbConn);        	
                      $errorTO = $CatStockRollOverDAO->catStockRollOver($principalId, $wareHouseCde, $row, $_POST['FROMDATE']);
                      if($errorTO->type!=FLAG_ERRORTO_SUCCESS) { ?>
      	                          <script type='text/javascript'>
                                  parent.showMsgBoxError('Roll Over Failed -Contact Support')</script> 
                                  <?php 
                                  return;	
                      }
                } else { ?>
      	             <script type='text/javascript'>
                     parent.showMsgBoxError('No Balanced Count Detected')</script> 
                 <?php 
                } 
            }
            if($errorTO->type==FLAG_ERRORTO_SUCCESS) { ?>
      	                          <script type='text/javascript'>
                                  parent.showMsgBoxInfo('Roll Over Successful')</script> 
                                  <?php 
                                  unset($_POST['SUBMIT']);
            }
            
            
        } 
       if (!isset($_POST['SUBMIT'])) {     
       	
          $stockRollOverCatScreens = new stockRollOverCatScreens();
          $stockRollOverCatScreens->DisplayCat($principalId);	
        }             