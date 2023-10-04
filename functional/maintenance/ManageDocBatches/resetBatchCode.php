<?php 
include_once('ROOT.php'); 
include_once($ROOT.'PHPINI.php');	
include_once('resetBatchCodeClass.php');
include_once($ROOT.$PHPFOLDER."DAO/resetBatchCodeDAO.php");
include_once($ROOT.$PHPFOLDER."properties/Constants.php");
include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");


	 $class = 'odd';

    if (!isset($_SESSION)) session_start() ;
    
     $principalID = $_SESSION['principal_id'] ;
	   $depotID     = $_SESSION['depot_id'] ;
      
      $dbConn  = new dbConnect(); 
      $dbConn->dbConnection();
      $errorTO = new ErrorTO;

?>
<!DOCTYPE html>
		<HTML>
   		<HEAD>

				<TITLE>Simple Form</TITLE>

				<link href='<?php echo $ROOT.$PHPFOLDER ?>css/1_kwelanga.css' rel='stylesheet' type='text/css'>
				<link href='<?php echo $ROOT.$PHPFOLDER ?>css/kos_standard_screen2.css' rel='stylesheet' type='text/css'>
				<script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/jquery.js"></script>
				<script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/dops_global_functions.js"></script>

		    <style>
		      td.head1 {font-weight:normal;
		                font-size:20px;text-align:left; 
		                font-family: Calibri, Verdana, Ariel, sans-serif; 
		                padding: 0 150px 0 150px }
		                
		      td.det1  {border-style:none; 
		                text-align: left; 
		                font-weight: bold; 
		                font-size: 13px;
		                padding: 0 150px 0 150px  }
		                
		      table.box {border:collapse;
		      	         border: 2px solid; 
		      	         border-color: #990000; 
		      	         background: #fcecec }          
		    	
		    	</style>

		   </HEAD>
		      <body>

	<?php
	
// *******************************************************************************************************************************************
     
     if (isset($_POST['CANCEL'])) {
   
           return;
       	   

     }
     
 // *******************************************************************************************************************************************
     
     if (isset($_POST['BACK'])) {
   
           unset($_POST['SELECT']);
       	   

     }
	
// *******************************************************************************************************************************************
	
	 if (isset($_POST['SELECT'])) {
   
  	if (isset($_POST["INVOICENUM"])) $postINVOICE= test_input($_POST["INVOICENUM"]); else $postINVOICE = ''; 
  	if (isset($_POST["PRODCODE"])) $postPRODUCT=test_input($_POST["PRODCODE"]); else $postPRODUCT = ''; 
  	
    if(strlen($postINVOICE) > 3 && $postINVOICE !== '') {
    	
           $resetBatchCode = new resetBatchCode();
           $a = $resetBatchCode->secondform($postINVOICE, $principalID, $postPRODUCT);
           
       
           
   	}  else {?>
           <script type='text/javascript'>parent.showMsgBoxError('Document Number / Product Code Blank or Too Short <br><br> Try Again')</script>
           <?php	
           
         unset($_POST['SELECT']);
         unset($_POST['UPDATEBATCH']);
    }
    
 }
// *******************************************************************************************************************************************
	
	if (isset($_POST['UPDATEBATCH'])) {
		
		if (isset($_POST["BATCHNO[]"])) $postBATCHNO=test_input($_POST["BATCHNO[]"]); else $postBATCHNO = ''; 
  	if (isset($_POST["UID[]"])) $postUID=test_input($_POST["UID[]"]); else $postUID = '';

    $pickedUpError = 'N';
    
    if(count($_POST["BATCHNO"]) && count($_POST["UID"])  > 0) {
        
        for ( $x = 0; $x < count($_POST["UID"]); $x++ ) {

            $resetBatchCodeDAO= new resetBatchCodeDAO($dbConn);
            $errorTO = $resetBatchCodeDAO->updateBatchNumber(($_POST["BATCHNO"][$x]), ($_POST["UID"][$x]));
            
            if($errorTO->type != FLAG_ERRORTO_SUCCESS) { 
                   $pickedUpError = 'Y';
                   break;
            }
        }       

        if($pickedUpError == 'N') { ?>
                 <script type='text/javascript'>parent.showMsgBoxInfo('Batch Number Successfully Updated<br><br>')</script>
                 <?php	
                 unset($_POST['SELECT']);
                 unset($_POST['UPDATEBATCH']);
        } else { ?>
                <script type='text/javascript'>parent.showMsgBoxError('Batch Number Update Failed<br><br>Contact Support')</script>
	              <?php
                unset($_POST['SELECT']);
	              unset($_POST['UPDATEBATCH']);
        }
    } else { ?>
              <script type='text/javascript'>parent.showMsgBoxError('Nothing To Update<br><br>Contact Support')</script>
              <?php
              unset($_POST['SELECT']);
              unset($_POST['UPDATEBATCH']);
    }   
     
	}


// *******************************************************************************************************************************************

	 if (!isset($_POST['SELECT']) && !isset($_POST['UPDATEBATCH'])){
     	
     	$resetBatchCode = new resetBatchCode();
  		$a = $resetBatchCode->firstform();
     	
     }

// *******************************************************************************************************************************************

		function test_input($data) {

		  $data = trim($data);
		  $data = stripslashes($data);
		  $data = htmlspecialchars($data);
		  if($data=='e') { $data=0; } 
	    
	   return $data;
	 } ?> 