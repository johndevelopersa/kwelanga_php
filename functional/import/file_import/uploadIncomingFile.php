<?php
// https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/functional/import/file_import/uploadIncoming File.php

    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
    include_once($ROOT.$PHPFOLDER."DAO/FileUploadDAO.php");
    include_once($ROOT.$PHPFOLDER."elements/datePickerElement.php");
    include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php'); 
    include_once($ROOT.$PHPFOLDER.'DAO/ManageOrdersDAO.php');
    include_once($ROOT.$PHPFOLDER.'DAO/ProcessFilesDAO.php');
    include_once("ProcessIncomingFile.php"); 
    include_once("uploadIncomingFileScreens.php");   
       
    if (!isset($_SESSION)) session_start;
    $userId      = $_SESSION['user_id'];
    $principalId = $_SESSION['principal_id'] ;
    
    $dbConn = new dbConnect();
    $dbConn->dbConnection();
    $errorTO = new ErrorTO;
    
if(isset($_POST['CCANCEL'])) { 
     unset($_POST['FIRSTFORM']);
     	
}
if(isset($_POST['SELECT'])) {
      $errors= array();
      $file_name = $_FILES['UFILE']['name'];
      $file_tmp = $_FILES['UFILE']['tmp_name'];
      $file_type = $_FILES['UFILE']['type'];
      $file_ext=strtolower(end(explode('.',$_FILES['UFILE']['name'])));
      
      $f1 = test_input($_POST['REFIELD']);
      
      $f2 = test_input($_POST['ORDNO']); 
      
      $f3 = test_input($_POST['STOCKCODE']);
      
      $f4 = test_input($_POST['QTYFIELD']); 
      
      $iDate = test_input($_POST['FROMDATE']);

      $extensions= array("csv");
      
      if(in_array($file_ext,$extensions)=== false){
         $errors[]="Extension not allowed, Please choose a csv file.";
      }
      if(empty($errors)==true) {
         move_uploaded_file($file_tmp, $ROOT."ftp/file_upload/".$file_name);
      }else{ ?>
      	<script type='text/javascript'>
                 parent.showMsgBoxError('<?php echo $errors[0];?>')</script> 
      <?php 
      }      
      if($principalId == 393) {
             $ProcessIncomingFile = new ProcessIncomingFile($dbConn);       
             $a = $ProcessIncomingFile->processSaucyInvoiceFile($principalId, $f1, $f2, $f3, $f4, $iDate);   
      }                

      ?>
      <script type='text/javascript'>parent.showMsgBoxInfo('Success')</script> 
        <?php
      unset($_POST['firstform']);	
      unset($_POST['SELECT']);
}


?>
<!DOCTYPE html>
<HTML>
	<HEAD>

		<TITLE>Document Management</TITLE>

    <link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/1_kwelanga.css' rel='stylesheet' type='text/css'>
    <script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/jquery.js"></script>
    <script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/dops_global_functions.js"></script>

    <style>
    	td.head1 {font-weight:normal;
    		        font-size:2em;text-align:left; 
    		        font-family: Calibri, Verdana, Ariel, sans-serif; 
    		        padding: 0 150px 0 150px }
      
      td.det1  {border-style:none; 
      	        border-color:DarkGray; 
      	        border-width:1px;
      	        border-collapse:collapse; 
      	        text-align: center; 
      	        font-weight: bold; 
      	        font-size: 12px;}

      td.det2  {border-style:none; 
      	        border-color:DarkGray; 
      	        border-width:1px;
      	        border-collapse:collapse; 
      	        text-align: left; 
      	        font-weight: normal; 
      	        font-size: 12px;  }
    	
    	</style>

		</HEAD>
<body>
	
<?php

$tempFolder = $ROOT . 'temp';

/* if(!is_writable($tempFolder)){
    echo "PPP";
} else{
    echo 'The directory "' . $tempFolder . '" is writable. All is good.<br>';
}

echo file_put_contents($ROOT."temp/This Is The Root.txt","This Is The Root Today s File.txt");
echo "<br>";
*/
if(isset($_POST['PROCESSFILE'])) {
	
	  if(trim($_FILES['UFILE']['name']) <> '') {
	
            $errors= array();
            $file_name = $_FILES['UFILE']['name'];
            $file_tmp  = $_FILES['UFILE']['tmp_name'];
            $file_type = $_FILES['UFILE']['type'];
            $tmp       = $_FILES['UFILE']['name'];
            $tmp2      = explode('.',$tmp);
            
            $file_ext = strtolower(end($tmp2));
 
            $extensions= array($_POST['FEXT']);
            
            if(in_array($file_ext,$extensions) === false) { ?>
                    <script type='text/javascript'>parent.showMsgBoxError('Error! Extension not allowed, Please choose a valid file.')</script>
                    <?php	
                    unset($_POST['FIRSTFORM']);
                    unset($_POST['PROCESSFILE']);
                      
            } else {
                   $s = move_uploaded_file($file_tmp, $ROOT . 'temp/'.$file_name);
                   // if($s){echo "T";} else {echo "F";}
                   
                   $ManageOrdersDAO = new ManageOrdersDAO($dbConn);
                   $result = $ManageOrdersDAO->insertInDocumentlog('0000000', $userId ,0 ,0, 'File Upload', $file_name, 0);      
      
                   $FileUploadDAO = new FileUploadDAO($dbConn);
                   $FileUploadDAO->dropTempFilesTable($userId);
                   
                   $tmpArr    = array();
                   $fldArry2  = array();
                   for ($x = 1; $x <= $_POST['FARRAY']; $x++) {
                           $fldName = "FIELDNAME" . $x;
                           $fld = "FIELD" . $x;
                               
                           $fldArry[] = $_POST[$fldName]  ."-" . test_input($_POST[$fld]);
                           $tmpArr[$_POST[$fld]] = trim($_POST[$fldName]);
                   }
            
                   $FileUploadDAO = new FileUploadDAO($dbConn);
                   $FileUploadDAO->createTempFilesTable($userId, $_POST['FFTOT'], $tmpArr) ; 
            
                   $FileUploadDAO = new FileUploadDAO($dbConn);
                   $errorTO = $FileUploadDAO->uploadFileDataTemp($file_name, $userId, $_POST['FHEAD']);
                   if ($errorTO->type!=FLAG_ERRORTO_SUCCESS) { ?>
                          <script type='text/javascript'>parent.showMsgBoxError('Error! File Upload Failed - Contact Support..')</script>
                          <?php	
                          print_r($errorTO);
                          return ;       	                  
                   }
                   
                   if($_POST['FQUERY'] == "addWhToCmgj") {
      
                       $FileUploadDAO = new FileUploadDAO($dbConn);
                       $FileUploadDAO->addWhToCmgj(file_upl_temp_ . mysqli_real_escape_string($dbConn->connection, $userId), implode(',',$fldArry)) ;     

                       unset($_POST['FIRSTFORM']);
                       unset($_POST['PROCESSFILE']);
                       die();
                   }
                   if($_POST['FQUERY'] == "coegaPoCheck") {
                   	
                       $FileUploadDAO = new FileUploadDAO($dbConn);
                       $FileUploadDAO->coegaPoCheck(file_upl_temp_ . mysqli_real_escape_string($dbConn->connection, $userId), $fldArry[0], $fldArry[1]) ;     

                       unset($_POST['FIRSTFORM']);
                       unset($_POST['PROCESSFILE']);
                       die();

                   }
                   if($_POST['FQUERY'] == "stockCountFile") {
                   	
                   	  if($_POST['SDEPOT'] == "Select Warhouse") {?>
                               <script type='text/javascript'>parent.showMsgBoxError('Error! No Warehouse Selected')</script>
                               <?php	
                               unset($_POST['FIRSTFORM']);
                               unset($_POST['PROCESSFILE']);
                   	  	
                   	  } else {
                   	  	  $depId = $_POST['SDEPOT'];

                   	                   	
                       $FileUploadDAO = new FileUploadDAO($dbConn);
                       $countMat = $FileUploadDAO->stockCountFile(file_upl_temp_ . mysqli_real_escape_string($dbConn->connection, $userId), $principalId, $depId) ;     

                       print_r($countMat);
                       unset($_POST['FIRSTFORM']);
                       unset($_POST['PROCESSFILE']);
                       die();
                   	  }
                   }                   
                   if($_POST['FQUERY'] == "loadUppStores") {
                   	
                   	   echo "Processing Store Updates..<br><br>";
                   	
                       $FileUploadDAO = new FileUploadDAO($dbConn);
                       $errorTO = $FileUploadDAO->loadUppStores('file_upl_temp_' . mysqli_real_escape_string($dbConn->connection, $userId), $principalId, $userId) ;     

                       if($errorTO->type!=FLAG_ERRORTO_SUCCESS) { ?>
                          <script type='text/javascript'>parent.showMsgBoxError('Error! File Upload Failed - Contact Support..')</script>
                          <?php	
                          print_r($errorTO);
                          return ;       	                  
                       } else { ?>
                          <script type='text/javascript'>parent.showMsgBoxInfo('UPP Customers Loaded..')</script>
                          <?php	
                       	
                       	
                       }
                       unset($_POST['FIRSTFORM']);
                       unset($_POST['PROCESSFILE']);
                       die();                   	
                   	
                   }
                   
                   if($_POST['FQUERY'] == "maintainBerkleyStores") {
                   	
                   	   // Look up uniques on Special Fields  
                   	   // Get Special field ID
                   	   
                   	   $FileUploadDAO = new FileUploadDAO($dbConn);
                       $sfd = $FileUploadDAO->getSpecialFieldId($principalId);
                       
                       $FileUploadDAO = new FileUploadDAO($dbConn);
                       $tmpFile = $FileUploadDAO->getTempFileRecords(file_upl_temp_ . mysqli_real_escape_string($dbConn->connection, $userId));
                       
                       // Check if Store Exists on Spec Field
                       
                       foreach($tmpFile as $storeRow) {
                             $FileUploadDAO = new FileUploadDAO($dbConn);
                             $sfdExists = $FileUploadDAO->checkIfStoreExists($principalId, $sfd[0]['fldId'],$storeRow['C'] );
                             
                             if(count($sfdExists) == 0)  {
                             	         echo "<pre>"; 
                                       print_r($storeRow);
                                       $FileUploadDAO = new FileUploadDAO($dbConn);
                                       $FileUploadDAO->maintainPrincipalStores(file_upl_temp_ . mysqli_real_escape_string($dbConn->connection, $userId), 
                                                                               $principalId,
                                                                               $userId ,
                                                                               $sfd[0]['fldId'],
                                                                               $storeRow['D'], 
                                                                               $storeRow['J'], 
                                                                               $storeRow['K'], 
                                                                               $storeRow['L'], 
                                                                               $storeRow['P'], 
                                                                               $storeRow['Q'], 
                                                                               $storeRow['R'], 
                                                                               $storeRow['S'], 
                                                                               $storeRow['G'], 
                                                                               $storeRow['X'], 
                                                                               $storeRow['Y'], 
                                                                               $storeRow['B'], 
                                                                               $storeRow['C']);
                             }
                       }
       
                       echo "<br>"; 

                       unset($_POST['FIRSTFORM']);
                       unset($_POST['PROCESSFILE']);
                       die();

                   }

            } 
      } else { ?>
         	      <script type='text/javascript'>parent.showMsgBoxError('No File Upload Type Chosen - Cannot Continue')</script>
                <?php	
                unset($_POST['FIRSTFORM']);
                unset($_POST['PROCESSFILE']);
      }         
}

if(isset($_POST['FIRSTFORM']) && !isset($_POST['PROCESSFILE']) ) {
	
	  if($_POST['UPLID'] <> 'Select Upload Type') {
	
        $availableUploads = new FileUploadDAO($dbConn); 
       	$result = $availableUploads->getAvailableUploads($principalId, '', $_POST['UPLID']);
   	
       	if(count($result) == 0) {?>
         	      <script type='text/javascript'>parent.showMsgBoxError('No File Uploads configured for this Principal - Cannot Continue')</script>
                <?php	
   		          return;
         	  }
       	// Pass Parameters as json
       	$uploadConfig = json_encode($result);
    
       	$uploadIncomingFileScreens = new uploadIncomingFileScreens();
       	$a = $uploadIncomingFileScreens->fileUploadDetails($uploadConfig, $userId);
    } else { ?>
         	      <script type='text/javascript'>parent.showMsgBoxError('No File Upload Type Chosen - Cannot Continue')</script>
                <?php	
                unset($_POST['FIRSTFORM']);
                unset($_POST['PROCESSFILE']);
    }   	
    
}

if(!isset($_POST['FIRSTFORM'])) {

   	$availableUploads = new FileUploadDAO($dbConn); 
   	$result = $availableUploads->getAvailableUploads($principalId, 'ONE', '');
   	
    if(count($result) == 0) {?>
         	      <script type='text/javascript'>parent.showMsgBoxError('No File Uploads configured for this Principal - Cannot Continue')</script>
                <?php	
   		          return;
    }
    // Pass Parameters as json
    $uploadConfig = json_encode($result);   
    
    $uploadIncomingFileScreens = new uploadIncomingFileScreens();
    $a = $uploadIncomingFileScreens->selectFileUploadType($uploadConfig);

}
?>
     
	</body>       
 </HTML>
 
<?php
 function test_input($data) {

  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  
  return $data;
 }
?> 