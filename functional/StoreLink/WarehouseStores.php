<?php 
   include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER."properties/Constants.php");
    include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
    include_once($ROOT.$PHPFOLDER."libs/CommonUtils.php");
    include_once($ROOT.$PHPFOLDER.'DAO/WarehouseStoresDAO.php');    
    include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");

    include_once("WarehouseStoresScreens.php");
   
   if (!isset($_SESSION)) session_start() ;
      $userUId      = $_SESSION['user_id'] ;
      $principalId  = $_SESSION['principal_id'] ;
      $wareHouseCde = $_SESSION['depot_id'] ;
      $systemName   = $_SESSION['system_name'] ;
      

      //Create new database object
      $dbConn = new dbConnect(); 
      $dbConn->dbConnection();
      $errorTO = new ErrorTO;
      
  //*************************************************************************************************************************************
  if(!isset($_POST['MODEMPSP'])&&!isset($_POST['ADDEMPSP'])&&!isset($_POST['SEARCH'])&&!isset($_POST['MODIFYSTORE'])){
  	 $WarehouseStoresScreens = new WarehouseStoresScreens();
       $a = $WarehouseStoresScreens->pickUpdateAction(); 	
  	
  	
  	}
  //*************************************************************************************************************************************
  if(isset($_POST['MODEMPSP'])){
  	$WarehouseStoresScreens = new WarehouseStoresScreens();
       $a = $WarehouseStoresScreens->SearchStores($wareHouseCde); 	
  	
  	}
  //*************************************************************************************************************************************
  if(isset($_POST['SEARCH'])){
  	
  	$filtersearch =$_POST['TRUID'];
  	$WarehouseStoresScreens = new WarehouseStoresScreens();
       $a = $WarehouseStoresScreens->ModifyWhAreaSelect($filtersearch,$wareHouseCde); 	
  	
  	}
  	//**********************************************************************************************************************************
  	if(isset($_POST['MODIFYSTORE'])){
  		
  		$UID = trim(substr($_POST["EMPID"],0,strpos($_POST["EMPID"],"-")));;
  		$WarehouseStoresScreens = new WarehouseStoresScreens();
       $a = $WarehouseStoresScreens->ModifyStoreScreen($UID,$userUId,$principalId,$wareHouseCde); 	
  		
  		
  		}
  
  //***************************************************************************************************************************************
  if(isset($_POST['SaveDet'])){
  	$DelArea = trim(substr($_POST["DEL"],0,strpos($_POST["DEL"],"-")));;
  	$Branch = $_POST['NBRANCH'];
  	$GLN = $_POST['NGLN'];
  	$Name = $_POST['NNAME'];
  	$Add1 = $_POST['NADD1'];
  	$Add2 = $_POST['NADD2'];
  	$Add3 = $_POST['NADD3'];
  	$Lat = $_POST['NLAT'];
  	$Long = $_POST['NLONG'];
  	$UID = $_POST['UID'];
  	
  	
  	
  	$Ndd = trim(substr($_POST['NDD'],0,strpos($_POST["NDD"],"-")));;
  	$Nod = trim(substr($_POST['NOD'],0,strpos($_POST["NOD"],"-")));;
 
  	
  	
  	$WarehouseStoreDao = new WarehouseStoreDao($dbConn);
       $errorTO = $WarehouseStoreDao->UpdateStore($DelArea,$Branch,$GLN,$Name,$Add1,$Add2,$Add3,$Lat,$Long,$UID,$Ndd,$Nod); 
   		  
   		if ($errorTO->type==FLAG_ERRORTO_SUCCESS) { ?>
                    <script type='text/javascript'>parent.showMsgBoxInfo('Store Updated')</script>  
                    <?php
                         		 	

                    } else { ?>
                   	<script type='text/javascript'>parent.showMsgBoxError('Failed To Update Store <br><br> Contact Kwelanga Support')</script>
                    <?php
                    
                    }
  }                  
    //*********************************************************************************************************8                
  	if(isset($_POST['ADDEMPSP'])){
  		$WarehouseStoresScreens = new WarehouseStoresScreens();
       $a = $WarehouseStoresScreens->ADDStoreScreen($wareHouseCde); 	
       
  		
  		}
  //***************************************************************************************************************
  if(isset($_POST['AddDet'])){
  	$DelArea = trim(substr($_POST["DEL"],0,strpos($_POST["DEL"],"-")));;
  	$Branch = $_POST['NBRANCH'];
  	$GLN = $_POST['NGLN'];
  	$Name = $_POST['NNAME'];
  	$Add1 = $_POST['NADD1'];
  	$Add2 = $_POST['NADD2'];
  	$Add3 = $_POST['NADD3'];
  	$Lat = $_POST['NLAT'];
  	$Long = $_POST['NLONG'];
 
    $Ndd = trim(substr($_POST['NNDD'],0,strpos($_POST["NNDD"],"-")));;
  	$Nod = trim(substr($_POST['NNOD'],0,strpos($_POST["NNOD"],"-")));;
  
  
  	
  	$WarehouseStoreDao = new WarehouseStoreDao($dbConn);
       $errorTO = $WarehouseStoreDao->AddStore($wareHouseCde,$DelArea,$Branch,$GLN,$Name,$Add1,$Add2,$Add3,$Lat,$Long,$Ndd,$Nod); 
   		  
   		if ($errorTO->type==FLAG_ERRORTO_SUCCESS) { ?>
                    <script type='text/javascript'>parent.showMsgBoxInfo('Store Added')</script>  
                    <?php
                         		 	

                    } else { ?>
                   	<script type='text/javascript'>parent.showMsgBoxError('Failed To Add Store <br><br> Contact Kwelanga Support')</script>
                    <?php
                    
                    }
  }                  
  
  
  //*************************************************************************************************************************************    
  
  
?>
     <!DOCTYPE html>
<HTML>
	<HEAD>

		<TITLE>Warehouse Stores Screen</TITLE>

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
      
      
      
      
      
      
      
      
      
            
      
      
      
      