    <?php 
   include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER."properties/Constants.php");
    include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
    include_once($ROOT.$PHPFOLDER."libs/CommonUtils.php");
    include_once($ROOT.$PHPFOLDER.'DAO/WarehouseStoreLinkDAO.php');    
    include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
     
    include_once("LinkStoreScreensCard.php");
   
   
   
  
   
      
   if (!isset($_SESSION)) session_start() ;
      $userUId      = $_SESSION['user_id'] ;
      $principalId  = $_SESSION['principal_id'] ;
      $wareHouseCde = $_SESSION['depot_id'] ;
      $systemName   = $_SESSION['system_name'] ;
  
   
   if(isset($_GET['PRINCIPALUID'])){$principalUID = $_GET['PRINCIPALUID'];}   
   if(isset($_GET['WSTOREUID'])){$wstoreID = $_GET['WSTOREUID'];}   
    
        

      //Create new database object
      $dbConn = new dbConnect(); 
      $dbConn->dbConnection();
      $errorTO = new ErrorTO;
      
//*****************************************************************************************************************************
     if (!isset($_POST['SEARCHBRANCHP'])&&!isset($_POST['SEARCHGLNP'])&&!isset($_POST['SEARCHNAMEP'])){
           	
   	     	 $LinkStoreScreensCard= new LinkStoreScreensCard();
           $a = $LinkStoreScreensCard->LinkStoreCardSearch(); 	
   		
     }
//****************************************************************************************************************************
     if (isset($_POST['SEARCHBRANCHP'])){
	        
	         $search = test_input($_POST['BRANCHP']);	
	          if (trim($search) == ""){
   		           	      echo "Error! Check Branch Code";
   		           	      ?>
         	              <script type='text/javascript'>parent.showMsgBoxError('Error! Check Branch Code')</script>
                        <?php	
   		                	
   		                	
   		           }else {
   		           	    $LinkStoreScreensCard= new LinkStoreScreensCard();
                      $a = $LinkStoreScreensCard->LinkStoresCard("B",$search,$userUId,$wareHouseCde,$principalUID,$wstoreID); 	
                        
   		            }
	         
	         
	         
	
     }     
//*****************************************************************************************************************************
     if(isset($_POST['SEARCHGLNP'])){
	 
	       $search = test_input($_POST['GLNP']);	
	          if (trim($search) == ""){
	          	          echo "Error! Check Gln Code";
   		           	      ?>
         	              <script type='text/javascript'>parent.showMsgBoxError('Error! Check Gln Code')</script>
                        <?php	
   		                	
   		                	
   		           }else {
   		           	    $LinkStoreScreensCard= new LinkStoreScreensCard();
                      $a = $LinkStoreScreensCard->LinkStoresCard("G",$search,$userUId,$wareHouseCde,$principalUID,$wstoreID); 	
                        
   		            }
     }      
//****************************************************************************************************************************
      if(isset($_POST['SEARCHNAMEP'])){
      
      $NameSearch = test_input( $_POST['NAMEP']);
   		           if (trim($NameSearch) == ""){
   		           	      ?>
         	              <script type='text/javascript'>parent.showMsgBoxError('Error! Check Name')</script>
                        <?php	
   		                	
   		                	
   		           }else {
   		           	     $LinkStoreScreensCard = new LinkStoreScreensCard();
                       $a = $LinkStoreScreensCard->LinkStoreCardName($NameSearch,$principalUID,$wstoreID); 	
                        
      
      	
      	
      } 
      }


//****************************************************************************************************************************
      if (isset ($_POST['LINK'])){
   		
   		    $pStoreUID= $_POST['PSTOREUID'];
   		   
   		
   		    
   		    $WarehouseStoreLinkDAO = new WarehouseStoreLinkDAO($dbConn);
          $errorTO = $WarehouseStoreLinkDAO->LinkStores($wareHouseCde,$principalUID,$pStoreUID,$wstoreID);   
   		  
   		  
   		  
   		  
   		  
   		if ($errorTO->type==FLAG_ERRORTO_SUCCESS) { ?>
                    <script type='text/javascript'>parent.showMsgBoxInfo('Link Created')</script>  
                    <?php
                     echo "Linked";    		 	
                     ?>
                   <script type='text/javascript'>
                   self.close();
                    </script>
                    <?php
                    } else {
                    	
                    	  echo "Error Not Linked"; 
                    	   ?>
                   	<script type='text/javascript'>parent.showMsgBoxError('Failed To Link Store <br><br> Contact Kwelanga Support')</script>
                    <?php
                   
                    }
 	     	
 	     	  
 	     	
 	    }      
//****************************************************************************************************************************   
if (isset ($_POST['LINKN'])){
   		
   		    $pStoreUID =trim(substr($_POST["EMPID"],0,strpos($_POST["EMPID"],"-")));
   		   
   		
   		    
   		    $WarehouseStoreLinkDAO = new WarehouseStoreLinkDAO($dbConn);
          $errorTO = $WarehouseStoreLinkDAO->LinkStores($wareHouseCde,$principalUID,$pStoreUID,$wstoreID);   
   		  
   		  
   		  
   		  
   		  
   		if ($errorTO->type==FLAG_ERRORTO_SUCCESS) { ?>
                    <script type='text/javascript'>parent.showMsgBoxInfo('Link Created')</script>  
                    <?php
                     echo "Linked";    		 	
                     ?>
                    <script type='text/javascript'>
                    self.close();
                    </script>
                    <?php
                    } else {
                    	
                    	  echo "Error Not Linked"; 
                    	   ?>
                   	<script type='text/javascript'>parent.showMsgBoxError('Failed To Link Store <br><br> Contact Kwelanga Support')</script>
                    <?php
                   
                    }
 	     	
 	     	  
 	     	
 	    }        
//*******************************************************************************************************************************

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
