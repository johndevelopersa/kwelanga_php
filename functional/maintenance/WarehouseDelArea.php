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
   	if (!isset($_POST['MODEMPSPD'])&&!isset($_POST['ADDEMPSPD'])&&!isset($_POST['SEARCHD'])&&!isset($_POST['MODIFYDELAREA'])&&!isset($_POST['DELDAREADETAIL'])){
   		 $WarehouseAreaScreens = new WarehouseAreaScreens();
       $a = $WarehouseAreaScreens->pickUpdateActionDel(); 	
   		
   	}
   //DD WHAREHOUSE AREA
     	//****************************************************************************************************************************************
   if (isset($_POST['SEARCHD']))
   {
   	 $filtersearch =$_POST['TRUID'];
   	 
   	 $WarehouseAreaScreens = new WarehouseAreaScreens();
       $a = $WarehouseAreaScreens->ModifyDelAreaSelect($filtersearch,$wareHouseCde); 	
   	}
   	   	//****************************************************************************************************************************************
if (isset ($_POST['MODEMPSPD'])){
   	
   		
   		$WarehouseAreaScreens = new WarehouseAreaScreens();
       $area = $WarehouseAreaScreens->SearchDelArea($wareHouseCde); 	
   		
   		}
   	//****************************************************************************************************************************************
   	if (isset ($_POST['ADDEMPSPD'])){
   	   
   		
   		$WarehouseAreaScreens = new WarehouseAreaScreens();
       $area = $WarehouseAreaScreens->ADDAreaScreenDel($userUId,$principalId,$wareHouseCde); 	
   		
   		}
   		   	//****************************************************************************************************************************************

   	if (isset ($_POST['MODIFYDELAREA'])){
   		$areaUID = trim(substr($_POST["EMPID"],0,strpos($_POST["EMPID"],"-")));;
   		
   		$WarehouseAreaScreens = new WarehouseAreaScreens();
       $area = $WarehouseAreaScreens->ModifyDelAreaScreen($areaUID,$wareHouseCde,$userUId,$principalId); 	
   		
   		}
   		
   //****************************************************************************************************************************************
   	if (isset ($_POST['DELDAREADETAIL'])){
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
   			if (isset ($_POST['SUBEMPUPDD'])){
   			$AName = $_POST['ANAME'];
   			$areaUID = $_POST['AC'];
   			$depottid = $_POST['WAREHOUSE'];
   				echo $AName;
   				 $WarehouseAreaDao = new WarehouseAreaDao($dbConn);
       $errorTO = $WarehouseAreaDao->UpdateDArea($areaUID,$AName,$depottid); 
   		  
   		if ($errorTO->type==FLAG_ERRORTO_SUCCESS) { ?>
                    <script type='text/javascript'>parent.showMsgBoxInfo('Area Updated')</script>  
                    <?php
                         		 	

                    } else { ?>
                   	<script type='text/javascript'>parent.showMsgBoxError('Failed To Update Area <br><br> Contact Kwelanga Support')</script>
                    <?php
                    
                    }
 	     	
   				
   				}
   		
  if (isset ($_POST['ADDD'])){
   		
   		$AName = $_POST['ANAME'];
   		$depottid = $_POST['WAREHOUSE'];
   		
   		 $WarehouseAreaDao = new WarehouseAreaDao($dbConn);
       $errorTO = $WarehouseAreaDao->ADDDArea($AName,$depottid); 
   		  
   		if ($errorTO->type==FLAG_ERRORTO_SUCCESS) { ?>
                    <script type='text/javascript'>parent.showMsgBoxInfo('Delivery Area Added')</script>  
                    <?php
                         		 	

                    } else { ?>
                   	<script type='text/javascript'>parent.showMsgBoxError('Failed To Delete Area <br><br> Contact Kwelanga Support')</script>
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
      
      
      
      
      
      
      