<?php 
   include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER."properties/Constants.php");
    include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
    include_once($ROOT.$PHPFOLDER."libs/CommonUtils.php");
    include_once($ROOT.$PHPFOLDER.'DAO/warehouseAreaDAO.php');    
    include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");

    include_once("WarehouseAreaScreens.php");
   
   if (!isset($_SESSION)) session_start() ;
      $userUId      = $_SESSION['user_id'] ;
      $principalId  = $_SESSION['principal_id'] ;
      $wareHouseCde = $_SESSION['depot_id'] ;
      $systemName   = $_SESSION['system_name'] ;
      

      //Create new database object
      $dbConn = new dbConnect(); 
      $dbConn->dbConnection();
      $errorTO = new ErrorTO;
   
   //SearchArea*******************************************************************************************************************************
   	if (!isset($_POST['SEARCH'])&&!isset($_POST['MODIFYAREA'])&&!isset($_POST['MODEMPSP'])&&!isset($_POST['ADDEMPSP'])){
   		 $WarehouseAreaScreens = new WarehouseAreaScreens();
       $a = $WarehouseAreaScreens->pickUpdateAction(); 	
   		
   	}
   //DD WHAREHOUSE AREA
 if (isset ($_POST['ADD'])){
   		
   		$AName = $_POST['ANAME'];
   		$depottid = $_POST['WAREHOUSE'];
   		
   		 $WarehouseAreaDao = new WarehouseAreaDao($dbConn);
       $errorTO = $WarehouseAreaDao->ADDArea($AName,$depottid); 
   		  
   		if ($errorTO->type==FLAG_ERRORTO_SUCCESS) { ?>
                    <script type='text/javascript'>parent.showMsgBoxInfo('Area Added')</script>  
                    <?php
                         		 	

                    } else { ?>
                   	<script type='text/javascript'>parent.showMsgBoxError('Failed To Add Area <br><br> Contact Kwelanga Support')</script>
                    <?php
                    
                    }
 	     	
 	     	  
 	     	
 	}     
     	//****************************************************************************************************************************************
   if (isset($_POST['SEARCH']))
   {
   	 $filtersearch =$_POST['TRUID'];
   	 
   	 $WarehouseAreaScreens = new WarehouseAreaScreens();
       $a = $WarehouseAreaScreens->ModifyWhAreaSelect($filtersearch,$wareHouseCde); 	
   	}
   	   	//****************************************************************************************************************************************
if (isset ($_POST['MODEMPSP'])){
   	
   		
   		$WarehouseAreaScreens = new WarehouseAreaScreens();
      $area = $WarehouseAreaScreens->SearchArea($wareHouseCde); 	
   		
   		}
   	//****************************************************************************************************************************************
   	if (isset ($_POST['ADDEMPSP'])){
   	   
   		
   		$WarehouseAreaScreens = new WarehouseAreaScreens();
       $area = $WarehouseAreaScreens->ADDAreaScreen($userUId,$principalId); 	
   		
   		}
   		   	//****************************************************************************************************************************************

   	if (isset ($_POST['MODIFYAREA'])){
   		$areaUID = trim(substr($_POST["EMPID"],0,strpos($_POST["EMPID"],"-")));;
   	          	if($areaUID == ""){
   			                            ?>
                                 	<script type='text/javascript'>parent.showMsgBoxError('Error! No Area Selected')</script>
                                  <?php
                                  $WarehouseAreaScreens = new WarehouseAreaScreens();
                                  $area = $WarehouseAreaScreens->SearchArea($wareHouseCde); 	 	
   			            }else {
   	                     	$WarehouseAreaScreens = new WarehouseAreaScreens();
                           $area = $WarehouseAreaScreens->ModifyAreaScreen($areaUID,$wareHouseCde,$userUId,$principalId); 	
   		                    }
   		}
   		
   //****************************************************************************************************************************************
   	if (isset ($_POST['DELAREADETAIL'])){
   		$areaUID = trim(substr($_POST["EMPID"],0,strpos($_POST["EMPID"],"-")));;
   		
   		 $WarehouseAreaDao = new WarehouseAreaDao($dbConn);
       $errorTO = $WarehouseAreaDao->delArea($areaUID); 
   		  
   		if ($errorTO->type==FLAG_ERRORTO_SUCCESS) { ?>
                    <script type='text/javascript'>parent.showMsgBoxInfo('Area Deleted')</script>  
                    <?php
                         		 	

                    } else { ?>
                   	<script type='text/javascript'>parent.showMsgBoxError('Failed To Delete Area <br><br> Contact Kwelanga Support')</script>
                    <?php
                    
                    }
 	     	
 	     	  
 	     	
 	}     
 	//modify************************************
   			if (isset ($_POST['SUBEMPUPD'])){
   			$AName = $_POST['ANAME'];
   			$areaUID = $_POST['AC'];
   			$depottid = $_POST['WAREHOUSE'];
   			
   				 $WarehouseAreaDao = new WarehouseAreaDao($dbConn);
       $errorTO = $WarehouseAreaDao->UpdateWArea($areaUID,$AName,$depottid); 
   		  
   		if ($errorTO->type==FLAG_ERRORTO_SUCCESS) { ?>
                    <script type='text/javascript'>parent.showMsgBoxInfo('Area Updated')</script>  
                    <?php
                         		 	

                    } else { ?>
                   	<script type='text/javascript'>parent.showMsgBoxInfo('Area Updated Successfully')</script>
                    <?php
                    
                    }
 	     	
   				
   				}
   		
   	
      
   //****************************************************************************************************************************************
    ?>
     <!DOCTYPE html>
<HTML>
	<HEAD>

		<TITLE>Job Update Screen</TITLE>

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
      
      
      
      
      
      
      
      
      
      