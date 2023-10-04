<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER."properties/Constants.php");
    include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
    include_once($ROOT.$PHPFOLDER."libs/CommonUtils.php");
    include_once($ROOT.$PHPFOLDER.'DAO/EmployeeDAO.php');    
    include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");

    include_once("JobDescriptionMaintenanceScreens.php");

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
	
	        
	          
	         
                   $EmployeeDAO = new EmployeeDAO($dbConn);
                   $errorTO = $EmployeeDAO->insertJobFunction(test_input($_POST['EJOB']));   
          
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
    
if(!isset($_POST['MODEMPSP']) && !isset($_POST['ADDEMPSP']) && !isset($_POST['SELMOD']) && !isset($_POST['SUBMITFILTER']))
 {
       $JobDescriptionMaintenanceScreens = new JobDescriptionMaintenanceScreens();
       $a = $JobDescriptionMaintenanceScreens->pickUpdateAction(""); 	
}    
    
 //ADD Radio Button Press     
if(isset($_POST['ADDEMPSP'])) {

	  $JobDescriptionMaintenanceScreens = new JobDescriptionMaintenanceScreens();
    $a = $JobDescriptionMaintenanceScreens->firstform(""); 
         
}      
elseif(isset($_POST['MODEMPSP'])) {
	
	  $JobDescriptionMaintenanceScreens = new JobDescriptionMaintenanceScreens();
    $a = $JobDescriptionMaintenanceScreens->ModifyJobDetails(""); 
			

          
		
		
		
	}
  


//modify function 

 if(isset($_POST['SELMOD'])) { 
 	
 	 $JobDescriptionMaintenanceScreens = new JobDescriptionMaintenanceScreens();
    $a = $JobDescriptionMaintenanceScreens->Modifyscreen2(""); 
    
     unset($_POST['MODEMPSP']); 
           unset($_POST['ADDEMPSP']);
           unset($_POST['SUBEMPUPD']);
    }

    if(isset($_POST['UPDFUNCTION'])) { 
    	
        if (isset($_POST["FNAME"]))   $postTNAME   = test_input($_POST["FNAME"]);   else $postTNAME     = '';
        if (isset($_POST["FUID"]))    $postTUID    = test_input($_POST["FUID"]);    else $postTUID      = '';
    	  if (isset($_POST["FSTATUS"])) $postNSTATUS = test_input($_POST["FSTATUS"]); else $postNSTATUS   = '';  

        if(strlen($postTNAME) > 3) {
            
                   $EmployeeDAO = new EmployeeDAO($dbConn);
                   $errorTO = $EmployeeDAO->updatefunction($postTUID, $postTNAME, $postNSTATUS);   


              if ($errorTO->type==FLAG_ERRORTO_SUCCESS) { ?>
                    <script type='text/javascript'>parent.showMsgBoxInfo('Job Description Update Successfully')</script>  
                    <?php
                    unset($_POST['MODTRANSP']); 
                    unset($_POST['ADDTRANSP']);
              } else { ?>
                   	<script type='text/javascript'>parent.showMsgBoxError('Job Description UpdateFailed <br><br> Contact Kwelanga Support')</script>
                    <?php
                    unset($_POST['MODTRANSP']); 
                    unset($_POST['ADDTRANSP']);
              }      
        } else { ?>
             <script type='text/javascript'>parent.showMsgBoxError('Job Description Blank or too Short (Minimum 4) <br><br> Try Again')</script>
             <?php	
             unset($_POST['MODTRANSP']); 
             unset($_POST['ADDTRANSP']);
        }
        
        
    }
    if (isset($_POST['SUBMITFILTER']))
        {
        	
        	$filtersearch =$_POST['TRUID'];
        	 echo $filtersearch;
        	 
        	  $JobDescriptionMaintenanceScreens = new JobDescriptionMaintenanceScreens();
        	  
    $a = $JobDescriptionMaintenanceScreens->ModifyJobDetailsFiltered("",$filtersearch); 
        	
        	}
     

 function test_input($data) {

  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  
  return $data;
 } 
