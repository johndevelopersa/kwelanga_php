<?php 
   include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER."properties/Constants.php");
    include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
    include_once($ROOT.$PHPFOLDER."libs/CommonUtils.php");
    include_once($ROOT.$PHPFOLDER.'DAO/WarehouseStoreLinkDAO.php');    
    include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");

    include_once("WarehouseStoreLinkScreens.php");
   
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
       	     	
      
   if(isset($_POST['SUBNAME'])){
   	
   		  $StoreUID =trim(substr($_POST["EMPID"],0,strpos($_POST["EMPID"],"-")));
   	    $StoreName =trim(substr($_POST["EMPID"], strpos($_POST["EMPID"],"-")+1),50);
   		
   			$WarehouseStoreLinkScreens = new WarehouseStoreLinkScreens();
        $a = $WarehouseStoreLinkScreens->LinkStoresScreenName($StoreName,$StoreUID,$userUId,$wareHouseCde); 	
   		
   }
   	
   	if (isset($_POST['SEARCHNAME'])){
   		
   		  
   		  $NameSearch = test_input( $_POST['NAME']);
   		           if (trim($NameSearch) == ""){
   		           	      ?>
         	              <script type='text/javascript'>parent.showMsgBoxError('Error! Check Name')</script>
                        <?php	
   		                	
   		                	
   		           }else {
   		           	     $WarehouseStoreLinkScreens = new WarehouseStoreLinkScreens();
                       $a = $WarehouseStoreLinkScreens->StoreLinkSelect($NameSearch,$userUId,$wareHouseCde); 	
                        
   		            }
   			
   		
   		
   	}
   	
     //*************************************************************************************************************************************
   	   	
   	    if (isset($_POST['SEARCHBRANCH']) || isset($_POST['SELMOD'])){
    	           
    	           if(isset($_POST['SELMOD'])){
    	               $search = test_input( $_POST['STOREUID']);
    	               $WarehouseStoreLinkScreens = new WarehouseStoreLinkScreens();
                     $a = $WarehouseStoreLinkScreens->LinkStoresScreen("U",$search,$userUId,$wareHouseCde); 	     
                    	          
    	          }else{
    	           	   
    	               $search = test_input( $_POST['BRANCH']);	
    	           	
    	           	 if (trim($search) == ""){
   		           	      ?>
         	              <script type='text/javascript'>parent.showMsgBoxError('Error! Check Branch Code')</script>
                        <?php	
   		                	
   		                	
   		           }else {
   		           	      $WarehouseStoreLinkScreens = new WarehouseStoreLinkScreens();
                        $a = $WarehouseStoreLinkScreens->LinkStoresScreen("B",$search,$userUId,$wareHouseCde); 	
                        
   		            }
   		          
    	           	}
    	           
    	           
   		           
   		          
   		          
   
   	   	}
   	
      	if (isset($_POST['SEARCHGLN']))
   	{
    	         	     $search = test_input( $_POST['GLN']);	
   		           	   $WarehouseStoreLinkScreens = new WarehouseStoreLinkScreens();
                     $a = $WarehouseStoreLinkScreens->LinkStoresScreen("G",$search,$userUId,$wareHouseCde); 	
                        
   		            }
  
   	  if (isset($_POST['SELMOD'])){
   		
   	       	$principalUID = $_POST['SELMOD'];
   	       	$storeUID = $_POST['STOREUID'];
   		      $link = "<script>window.open('".$ROOT.$PHPFOLDER."functional/maintenance/LinkStoreCard.php?PRINCIPALUID=".$principalUID ." & WSTOREUID=".$storeUID."', 'popup', 'width=850,height=600')</script>";
         	  echo $principalUID;
   		      echo $link;
   		      
   		      
   		}
   	 if (isset($_POST['REFRESH'])){
   	   	             
   	   	             $search = test_input( $_POST['STOREUID']);
   	   	             
   	   	             $WarehouseStoreLinkScreens = new WarehouseStoreLinkScreens();
                     $a = $WarehouseStoreLinkScreens->LinkStoresScreen("U",$search,$userUId,$wareHouseCde); 	     
   	 }
   		
   		
   		
      if (!isset($_POST['SEARCHBRANCH'])&&!isset($_POST['SEARCHGLN'])&&!isset($_POST['SEARCHNAME'])&&!isset($_POST['SELMOD'])&&!isset($_POST['SUBNAME'])&&!isset($_POST['REFRESH'])){
   	
   	     	 $WarehouseStoreLinkScreens = new WarehouseStoreLinkScreens();
           $a = $WarehouseStoreLinkScreens->SearchStore(); 	
   		
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
   
   //***********************************************
      
  function test_input($data) {

  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
    
  return $data;
 }
