<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER."properties/Constants.php");
    include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
    include_once($ROOT.$PHPFOLDER."libs/CommonUtils.php");
    include_once($ROOT.$PHPFOLDER."DAO/TaskManDAO.php");	    
    include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
    include_once($ROOT.$PHPFOLDER.'TO/DmTaskTO.php');
    include_once($ROOT.$PHPFOLDER.'elements/basicSelectElement.php');
    include_once($ROOT.$PHPFOLDER."elements/datePickerElement.php");
    
    if (!isset($_SESSION)) session_start() ;
      $userUId     = $_SESSION['user_id'] ;
      $principalId = $_SESSION['principal_id'] ;

      //Create new database object
      $dbConn  = new dbConnect(); 
      $dbConn->dbConnection();
      $errorTO = new ErrorTO;
?>

<!DOCTYPE html>
<HTML>
	<HEAD>

		<TITLE>Principal - Warehouse Selection</TITLE>

		<link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/1_kwelanga.css' rel='stylesheet' type='text/css'>
		<script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/jquery.js"></script>
		<script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/dops_global_functions.js"></script>

    <style>
      td.head1 {font-weight:bold;
                font-size:20px;text-align:left; 
                font-family: Calibri, Verdana, Ariel, sans-serif; 
                padding: 0 150px 0 150px }
                
      td.det1  {border-style:none; 
                text-align: left; 
                font-weight: bold; 
                font-size: 13px;}

      td.det2  {border-style:none; 
                text-align: left; 
                font-weight: normal; 
                font-size: 13px;}
                
      td.det3  {border-style:none; 
                text-align: left;
                font-style: italic; 
                font-weight: normal; 
                font-size: 9px;}                
    	</style>
      <script type="text/javascript" language="javascript" src="<?php echo $ROOT.$PHPFOLDER ?>js/jquery.js"></script>
		</HEAD>
    <body>
<?php
//   saveform *********************************************************************************************************************************************      
if (isset($_POST['saveform']))  {

	    $postChain   = (isset($_POST["DEBTLIST"]))   ? htmlspecialchars($_POST["DEBTLIST"])      : '';
	    $postPrinID  = (isset($_POST["PRINID"]))     ? htmlspecialchars($_POST["PRINID"])        : '';
	    $postTmonth  = (isset($_POST["TMONTH"]))     ? htmlspecialchars($_POST["TMONTH"])        : '';
	    $postTuser   = (isset($_POST["TUSER"]))      ? htmlspecialchars($_POST["TUSER"])         : '';
	    $postUtasks  = (isset($_POST["UTASKS"]))     ? htmlspecialchars($_POST["UTASKS"])        : '';
	    $postRepType = (isset($_POST["REPTYPE"]))    ? htmlspecialchars($_POST["REPTYPE"])       : '';

      $TaskManDAO = new TaskManDAO($dbConn);
      $monthList = $TaskManDAO->getDatePeriod($postTmonth);
      
      foreach($_POST["TASKUID"] as $tKey=>$erow) {
      	
             if (in_array($erow, $_POST['TASKUID'])) {
                  $as = "A";
             } else {
                  $as = "D";
             }
             
             $taskSuccess = 'S';
        
             $DmTaskTO  = new DmTaskTO;
             $DmTaskTO->PrincipalUid           = $postPrinID ;
             $DmTaskTO->DebtorList             = $postChain;        
             $DmTaskTO->Year                   = substr($monthList[0]['month_start'],0,4);
             $DmTaskTO->StartDate              = $monthList[0]['month_start'];          
             $DmTaskTO->Month                  = $postTmonth; 
             $DmTaskTO->TaskTransactionUid     = $_POST['DMTTUID'][0];     
	           $DmTaskTO->TaskUid                = $erow;
	           $DmTaskTO->InputType              = $_POST['INPUTID'][0];
	           $DmTaskTO->dueDate                = $_POST['CALDATE'];          
	           $DmTaskTO->Comments               = $_POST['USERCOM'][0];      
             $DmTaskTO->OwnerID                = $postTuser ;         
             $DmTaskTO->CapturedBy             = $userUId;
             $DmTaskTO->Status                 = $as;
             $DmTaskTO->InputYn                = $_POST['INSTATUS'];
             $DmTaskTO->InputDate              = $_POST['TODATE'];
             $DmTaskTO->InputComment           = $_POST['INPCOMM'];
             
             $DmTaskTO->debtor_contact1        = $_POST['CONTACT1'];
             $DmTaskTO->debtor_contact2        = $_POST['CONTACT2'];
             $DmTaskTO->debtor_tel1            = $_POST['CNUMBER1'];
             $DmTaskTO->debtor_tel2            = $_POST['CNUMBER2'];
             $DmTaskTO->debtor_email1          = $_POST['CEMAIL1'];
             $DmTaskTO->debtor_email2          = $_POST['CEMAIL2']; 
             $DmTaskTO->portal                 = $_POST['RPORTAL'];
             $DmTaskTO->portal_user            = $_POST['PUSER'];
             $DmTaskTO->portal_password        = $_POST['PPASSWORD'];
             $DmTaskTO->debtor_update_date     = $_POST['DUPDATE'];
             $DmTaskTO->debtor_update_comments = $_POST['CONTACTCOMM'];

             $DmTaskTO->communication_date     = $_POST['COMDATE'];  
             $DmTaskTO->communication_comments = $_POST['COMCOMM']; 
             
             $TaskManDAO = new TaskManDAO($dbConn);
             $errorTO = $TaskManDAO->postTaskTransaction($DmTaskTO, 'UserCapture');
             
             $transUid = $_POST['DMTTUID'][0];
                          
             include_once($ROOT.$PHPFOLDER.'functional/uploads/fileUpload.php');
             
             if($errorTO->type <> 'S') {
                 $taskSuccess = 'F';
                 
                 if($errorTO->type == 'D') {
                 	    $taskSuccess = 'D';
                 }	
                 
             }
      }      
      if($taskSuccess == 'S') {
         ?>
         <script type='text/javascript'>parent.showMsgBoxInfo('Update Successful <br>')</script>
         <?php 
      } elseif($taskSuccess == 'D') {
         ?>
         <script type='text/javascript'>parent.showMsgBoxInfo('Update Successful <br><br> *** NOTE ***<br><br>File      <?php echo $fileName ?>        Not Uploaded -- Duplicate')</script>
         <?php 
      }	else {
         ?>
         <script type='text/javascript'>parent.showMsgBoxError('Update Failed <br> Contact Support')</script> 
          <?php  
      }
      unset($_POST['firstform']);	
      unset($_POST['selectDebtorForm']);	
      unset($_POST['selectTaskListForm']);
      unset($_POST['saveform']); 
}

// ********************************************************************************************************************************************************      
if (isset($_POST['canform'])) {
      return;    
}
if (isset($_POST['backform'])) {

      unset($_POST['firstform']);	
      unset($_POST['selectDebtorForm']);	
}	

// Capture Task Detail *********************************************************************************************************************************************      

if (isset($_POST['captureTaskDetail'])) {
	
     $postPrincipal   = (isset($_POST["PRINID"]))    ? htmlspecialchars($_POST["PRINID"])     : '';
     $postUser        = (isset($_POST["TUSER"]))     ? htmlspecialchars($_POST["TUSER"])      : '';
     $postMonth       = (isset($_POST["TMONTH"]))    ? htmlspecialchars($_POST["TMONTH"])     : '';
     $postTODATE      = (isset($_POST["TODATE"]))    ? htmlspecialchars($postTODATE=$_POST["TODATE"])   : CommonUtils::getUserDate(); 
     $postCOMDATE     = (isset($_POST["COMDATE"]))   ? htmlspecialchars($postCOMDATE=$_POST["COMDATE"]) : CommonUtils::getUserDate();
     $postDUPDATE     = (isset($_POST["DUPDATE"]))   ? htmlspecialchars($postDUPDATE=$_POST["DUPDATE"]) : CommonUtils::getUserDate();           
     
     
     if(empty($_POST['SELECTTASK'])) {
          ?>
           <script type='text/javascript'>parent.showMsgBoxError('No Task Selected <br>')</script> 
          <?php 
          unset($_POST['firstform']);
          unset($_POST['selectDebtorForm']);
     } elseif( $postMonth == 'Select a Month') {
          ?>
           <script type='text/javascript'>parent.showMsgBoxError('No Month Selected <br>')</script> 
          <?php 
          unset($_POST['firstform']);
          unset($_POST['selectDebtorForm']);
     } elseif($postPrincipal == 'Select a Principal') {
          ?>
           <script type='text/javascript'>parent.showMsgBoxError('No Principal Selected <br>')</script> 
          <?php 
          unset($_POST['firstform']);
          unset($_POST['selectDebtorForm']);
     } else {
        $TaskManDAO = new TaskManDAO($dbConn);
        $taskToMan = $TaskManDAO->getUserTaskToManage($_POST['SELECTTASK'][0]);
        
//      print_r($taskToMan);
        
        ?>
        <center>
           <FORM name='TaskManagement' method=post action='' enctype="multipart/form-data">
                <table width="720"; style="border:none">
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                      <td class=head1 colspan="12"; style="text-align:center";>Task Management Feedback - <?php echo $taskToMan[0]['task']; ?></td>
                                                                              <input type="hidden" name="TASKUID[]" value=<?php echo $taskToMan[0]['task_uid']; ?>></td>
                                  	                                          <input type="hidden" name="INPUTID[]" value=<?php echo $taskToMan[0]['input_type'];    ?>>          
                                                                              <input type="hidden" name="DMTTUID[]" value=<?php echo $taskToMan[0]['dtt_uid'];    ?>>
                                                                              <input type="hidden" name="USERCOM[]" value=<?php echo $taskToMan[0]['comment'];    ?>>
                   </tr>
                  <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                    <td width="5%";  style="border:none">&nbsp;</td>
                    <td width="9%";  style="border:none">&nbsp;</td>
                    <td width="9%";  style="border:none">&nbsp;</td>
                    <td width="9%";  style="border:none">&nbsp;</td>
                    <td width="9%";  style="border:none">&nbsp;</td>
                    <td width="9%";  style="border:none">&nbsp;</td>
                    <td width="9%";  style="border:none">&nbsp;</td>
                    <td width="9%";  style="border:none">&nbsp;</td>
                    <td width="5%";  style="border:none">&nbsp;</td>
                    <td width="9%";  style="border:none">&nbsp;</td>
                    <td width="13%"; style="border:none">&nbsp;</td>
                    <td width="5%";  style="border:none">&nbsp;</td>
                  </tr>
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                      <td >&nbsp;</td>
                      <td class=det1 colspan="1";>Principal </td>
                      <td class=det2 colspan="4";><?php echo $taskToMan[0]['principal_name']; ?></td>
                                                  <input type="hidden" name="PRINID"   value=<?php echo $taskToMan[0]['principal_uid']; ?>>
                                                  <input type="hidden" name="DEBTLIST" value=<?php echo $taskToMan[0]['debtor_uid']; ?>>
                                                  <input type="hidden" name="TMONTH"   value=<?php echo $postMonth; ?>>
                                                  <input type="hidden" name="TUSER"    value=<?php echo $postUser; ?>>
                                                  <input type="hidden" name="UTASKS"   value=<?php echo $taskToMan[0]['dm_tasks']; ?>> 
                      <td Colspan="6">&nbsp;</td>
                   </tr>
                  <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td Colspan="12">&nbsp;</td>
                   </tr>
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                   	  <td >&nbsp;</td>
                      <td class=det1 colspan="1";>Debtor </td>
                      <td class=det2 colspan="4";><?php echo trim($taskToMan[0]['debtor_name']); ?></td>
                      <td Colspan="1">&nbsp;</td>
                      <td class=det1 colspan="2";>Start Date</td>
                      <td class=det2 colspan="3";><?php echo $taskToMan[0]['start_date']; ?></td>
                   </tr>
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                      <td Colspan="12">&nbsp;</td>
                   </tr>
                   <?php if($taskToMan[0]['input_type'] == 1) {
                   	        if($taskToMan[0]['input_yn'] == 'Y') {
                   	             $lA = array('Yes','No');
                   	             $vA = array('1','2');
                   	             $postTODATE  = $taskToMan[0]['input_date'];
                   	             $postDUPDATE = $taskToMan[0]['input_date'];
                   	             
                   	        } else {
                   	        	   $lA = array('No','Yes');
                   	        	   $vA = array('2','1');
                   	        }     
                   	?>
                            <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                               <td >&nbsp;</td>
                               <td class=det1 colspan="3";>Task Status Completed</td>
                               <td colspan="1"; style="text-align:center;"><?php $lableArr = $lA;
          		                                                                   $valueArr = $vA;
          		                                                                   BasicSelectElement::buildGenericDD('INSTATUS', $lableArr,$valueArr, $postTSTATUS, "N", "N", null, null, null);?>
                               </td>
                               <td Colspan="1">&nbsp;</td>
                               <td class=det1 Colspan="1">Date Complete</td>
                               <td colspan="3"><?php DatePickerElement::getDatePickerLibs(); DatePickerElement::getDatePicker("TODATE",$postTODATE); ?> </td>
                               <td Colspan="2">&nbsp;</td>
                            </tr>                  
                            <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                 <td Colspan="12">&nbsp;</td>
                            </tr>
                            <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">  
                            	   <?php
                            	   if($taskToMan[0]['input_yn'] == 'Y') {
                                        $postDUPDATE = $taskToMan[0]['input_date'];
                                 } 	?>
                                 <td >&nbsp;</td>
                                 <td class=det1 colspan="3";>Contact Comments</td>
                                 <td class=det2 colspan="8";><textarea id="INPCOMM" name="INPCOMM" rows="4" cols="80"><?php echo $taskToMan[0]['input_comment'];?></textarea></td>
                            <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                 <td Colspan="12">&nbsp;</td>
                            </tr>     
                            <?php 
                            if($taskToMan[0]['allow_file_upload'] == 'Y'){
                            	    // Get uploaded file //
                            	    
                                  $TaskManDAO = new TaskManDAO($dbConn);
                                  $upLoadedFiles = $TaskManDAO->getUploadedFileList($taskToMan[0]['dtt_uid']);
                                  $fileList = ''; 
                                  
                                  if(count($upLoadedFiles) == 0 ) {
                                       $uploadlight = 'pp_bullet_red';
                                  } else {
                                       $uploadlight = 'pp_bullet_green';
                                       foreach($upLoadedFiles as $filerow) {
                                          $fileList = $fileList . $filerow['file_name'] .';<br>';
                                       } 	
                                  	
                                  	
                                  }?>
                                  <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                      <td >&nbsp;</td>
                                      <td class=det1 colspan="3">Document Type To Upload</td>
                                      <td Colspan="2" style="text-align:center;"><?php $lT = array('Statement','Remittance','Schedule','Recon','Note', 'Claim', 'Month End Reports');
                   	                                                              $vT = array('1','2','3','4','5','6','5');                        	
                                                                                  $lableArr = $lT;
          		                                                                    $valueArr = $vT;
          		                                                                    BasicSelectElement::buildGenericDD('REPTYPE', $lableArr,$valueArr, $postRepType, "N", "N", null, null, null);?>
          		                        </td>
                                      <td class=det1 colspan="2";>Get File to Upload</td> 
                                     	<td class=det1 colspan="1";><img src="<?php echo $ROOT.$PHPFOLDER ?>images/<?php echo $uploadlight ?>.png" style="width:10px; height:10px; float:left;"></td>
                                      <td colspan="2"; style="text-align:center;"><input type="file" id="myfile" name="myfile"></td>
                                      <td Colspan="1">&nbsp;</td>
                                  </tr>                             	
                            <?php 	
                            } else {?>
                                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                     <td Colspan="12">&nbsp;</td>
                                 </tr>     
                            <?php 	
                            }      
                            
                         } elseif($taskToMan[0]['input_type'] == 2) {?>
                              <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                   <td >&nbsp;</td>
                                   <td class=det1 colspan="3";>Update Date</td>                    
                                   <td colspan="4"><?php DatePickerElement::getDatePickerLibs(); DatePickerElement::getDatePicker("DUPDATE",$postDUPDATE); ?> </td>
                                   <td Colspan="4">&nbsp;</td>
                              </tr> 
                              <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                   <td Colspan="12">&nbsp;</td>
                              </tr> 
                              <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                   <td Colspan="1">&nbsp;</td>
                                   <td class=det1 colspan="3";>First Contact</td>
                                   <td colspan="3"; style="text-align:left;"><input type="text" size="30" name="CONTACT1" id="CONTACT1" value ="<?php echo $taskToMan[0]['debtor_contact1'] ?>"></td> 
                                   <td class=det1 colspan="2";>Contact No</td>
                                   <td colspan="4"; style="text-align:left;"><input type="text" size="30" name="CNUMBER1" id="CNUMBER1" value ="<?php echo $taskToMan[0]['debtor_tel1'] ?>"></td> 
                              </tr>                         
                              <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                   <td Colspan="12">&nbsp;</td>
                              </tr>    
                              <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                   <td Colspan="1">&nbsp;</td>
                                   <td class=det1 colspan="3";>Email Address</td>
                                   <td colspan="9"; style="text-align:left;"><input type="text" size="90" name="CEMAIL1" id="CEMAIL1" value ="<?php echo $taskToMan[0]['debtor_email1'] ?>"></td> 
                              </tr>
                              <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                   <td Colspan="12">&nbsp;</td>
                              </tr>            	
                              <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                   <td Colspan="1">&nbsp;</td>
                                   <td class=det1 colspan="3";>Second Contact</td>
                                   <td colspan="3"; style="text-align:left;"><input type="text" size="30" name="CONTACT2" id="CONTACT2" value ="<?php echo $taskToMan[0]['debtor_contact2'] ?>"></td> 
                                   <td class=det1 colspan="2";>Contact No</td>
                                   <td colspan="4"; style="text-align:left;"><input type="text" size="30" name="CNUMBER2" id="CNUMBER2" value ="<?php echo $taskToMan[0]['debtor_tel2'] ?>"></td> 
                              </tr>                         
                              <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                   <td Colspan="12">&nbsp;</td>
                              </tr>    
                              <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                   <td Colspan="1">&nbsp;</td>
                                   <td class=det1 colspan="3";>Email Address</td>
                                   <td colspan="9"; style="text-align:left;"><input type="text" size="90" name="CEMAIL2" id="CEMAIL2" value ="<?php echo $taskToMan[0]['debtor_email1'] ?>"></td> 
                              </tr>
                              <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                  <td Colspan="12">&nbsp;</td>
                              </tr>       
                              <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                   <td Colspan="1">&nbsp;</td>
                                   <td class=det1 colspan="3";>Retailer Portal</td>
                                   <td colspan="9"; style="text-align:left;"><input type="text" size="90" name="RPORTAL" id="RPORTAL" value ="<?php echo $taskToMan[0]['portal'] ?>"></td> 
                              </tr>
                              <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                   <td Colspan="1">&nbsp;</td>
                                   <td class=det1 colspan="3";>Portal Username</td>
                                   <td colspan="3"; style="text-align:left;"><input type="text" size="30" name="PUSER" id="PUSER" value ="<?php echo $taskToMan[0]['portal_username'] ?>"></td> 
                                   <td class=det1 colspan="2";>Password</td>
                                   <td colspan="4"; style="text-align:left;"><input type="text" size="30" name="PPASSWORD" id="PPASSWORD" value ="<?php echo $taskToMan[0]['portal_password'] ?>"></td> 
                              </tr>           
                              <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                   <td Colspan="12">&nbsp;</td>
                              </tr>    
                              <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                   <td >&nbsp;</td>
                                   <td class=det1 colspan="3";>Contact Comments</td>
                                   <td class=det2 colspan="8";><textarea id="CONTACTCOMM" name="CONTACTCOMM" rows="4" cols="80"><?php echo $taskToMan[0]['comments'];?></textarea></td>
                              <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                   <td Colspan="12">&nbsp;</td>
                              </tr>
                              <?php
                              if($taskToMan[0]['allow_file_upload'] == 'Y'){
                                  // Get uploaded file //
                                 
                                   $TaskManDAO = new TaskManDAO($dbConn);
                                   $upLoadedFiles = $TaskManDAO->getUploadedFileList($taskToMan[0]['dtt_uid']);
                                   $fileList = ''; 
                                  
                                   if(count($upLoadedFiles) == 0 ) {
                                        $uploadlight = 'pp_bullet_red';
                                   } else {
                                        $uploadlight = 'pp_bullet_green';
                                        foreach($upLoadedFiles as $filerow) {
                                            $fileList = $fileList . $filerow['file_name'] .';<br>';
                                        } 
                                   }?>
                                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                       <td >&nbsp;</td>
                                       <td class=det1 colspan="3">Document Type To Upload</td>
                                       <td Colspan="2" style="text-align:center;"><?php $lT = array('Statement','Remittance','Schedule','Recon','Note', 'Claim', 'Month End Reports');
                   	                                                                    $vT = array('1','2','3','4','5','6','5');                        	
                                                                                        $lableArr = $lT;
                                                                                        $valueArr = $vT;
                                                                                        BasicSelectElement::buildGenericDD('REPTYPE', $lableArr,$valueArr, $postRepType, "N", "N", null, null, null);?>
                                       </td>
                                       <td class=det1 colspan="2";>Get File to Upload</td> 
                                       <td class=det1 colspan="1";><img src="<?php echo $ROOT.$PHPFOLDER ?>images/<?php echo $uploadlight ?>.png" style="width:10px; height:10px; float:left;"></td>
                                       <td colspan="2"; style="text-align:center;"><input type="file" id="myfile" name="myfile"></td>
                                       <td Colspan="1">&nbsp;</td>
                                   </tr>                             	
                              <?php 	
                              } else { ?>
                                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                       <td Colspan="12">&nbsp;</td>
                                   </tr>     
                              <?php 
                              } ?> 
                              <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                    <td Colspan="12">&nbsp;</td>
                              </tr> 
                         <?php 
                         } elseif($taskToMan[0]['input_type'] == 3) {?>
                              <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                   <td >&nbsp;</td>
                                   <td class=det1 colspan="3";>Communication Date</td>                    
                                   <td colspan="4"><?php DatePickerElement::getDatePickerLibs(); DatePickerElement::getDatePicker("COMDATE",$postCOMDATE); ?> </td>
                                   <td Colspan="4">&nbsp;</td>
                              </tr> 
                              <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                   <td Colspan="12">&nbsp;</td>
                              </tr>   
                              <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                   <td >&nbsp;</td>
                                   <td class=det1 colspan="3";>Debtor Communication </td>
                                   <td class=det2 colspan="8";><textarea id="COMCOMM" name="COMCOMM" rows="4" cols="80"></textarea></td>
                              </tr> 
                              <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                   <td Colspan="12">&nbsp;</td>
                              </tr>
                              
                              <?php
                              if($taskToMan[0]['allow_file_upload'] == 'Y'){
                                   // Get uploaded file //
                                 
                                   $TaskManDAO = new TaskManDAO($dbConn);
                                   $upLoadedFiles = $TaskManDAO->getUploadedFileList($taskToMan[0]['dtt_uid']);
                                   $fileList = ''; 
                                  
                                   if(count($upLoadedFiles) == 0 ) {
                                        $uploadlight = 'pp_bullet_red';
                                   } else {
                                        $uploadlight = 'pp_bullet_green';
                                        foreach($upLoadedFiles as $filerow) {
                                            $fileList = $fileList . $filerow['file_name'] .';<br>';
                                        } 
                                   }?>
                                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                       <td >&nbsp;</td>
                                       <td class=det1 colspan="3">Document Type To Upload</td>
                                       <td Colspan="2" style="text-align:center;"><?php $lT = array('Statement','Remittance','Schedule','Recon','Note', 'Claim', 'Month End Reports');
                   	                                                                    $vT = array('1','2','3','4','5','6','5');                        	
                                                                                        $lableArr = $lT;
                                                                                        $valueArr = $vT;
                                                                                        BasicSelectElement::buildGenericDD('REPTYPE', $lableArr,$valueArr, $postRepType, "N", "N", null, null, null);?>
                                       </td>
                                       <td class=det1 colspan="2";>Get File to Upload</td> 
                                       <td class=det1 colspan="1";><img src="<?php echo $ROOT.$PHPFOLDER ?>images/<?php echo $uploadlight ?>.png" style="width:10px; height:10px; float:left;"></td>
                                       <td colspan="2"; style="text-align:center;"><input type="file" id="myfile" name="myfile"></td>
                                       <td Colspan="1">&nbsp;</td>
                                   </tr>                             	
                              <?php 	
                              } else { ?>
                                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                       <td Colspan="12">&nbsp;</td>
                                   </tr>     
                              <?php 
                              } ?> 
                              <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                    <td Colspan="12">&nbsp;</td>
                              </tr> 
                         <?php 
                         } else {?>
                                      
                         <?php 	
                         }    ?>          
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        <td Colspan="12">&nbsp;</td>
                   </tr>            
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                       <td Colspan="12">&nbsp;</td>
                   </tr>
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                       <td Colspan="12">&nbsp;</td>
                   </tr>
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                       <td colspan="12"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="saveform"= "Save Task Details">
                       	                                            <INPUT TYPE="submit" class="submit" name="backform"   value= "Back"> 
                                                                    <INPUT TYPE="submit" class="submit" name="canform"    value= "Cancel"></td>
                   </tr>
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        <td Colspan="12">&nbsp;</td>
                   </tr>     
                   </tr>
                   <?php
                   if($taskToMan[0]['allow_file_upload'] == 'Y'){ ?>
                        <tr >
                            <td Colspan="1">&nbsp;</td>
                            <td Colspan="1"><img src="<?php echo $ROOT.$PHPFOLDER ?>images/<?php echo $uploadlight ?>.png" style="width:10px; height:10px; float:left;"></td>
                            <td class=det3 Colspan="10"><?php if($fileList == '' ) { echo 'No Files uploaded for this task';} 
                            	                                                    else {echo 'Files uploaded for this task ' . $fileList;}?> </td>
                   </tr>
                   <?php } ?>

                </table>
           </form>
        </center> 
      <?php
	   }	
}









// Debtor Task *********************************************************************************************************************************************      
if (isset($_POST['selectTaskListForm']))  {
	
	    $postChain   = (isset($_POST["DEBTLIST"]))  ? htmlspecialchars($_POST["DEBTLIST"]) : '';
	    $postPrinID  = (isset($_POST["PRINID"]))    ? htmlspecialchars($_POST["PRINID"])    : '';
	    $postTmonth  = (isset($_POST["TMONTH"]))    ? htmlspecialchars($_POST["TMONTH"])    : '';
	    $postTuser   = (isset($_POST["TUSER"]))     ? htmlspecialchars($_POST["TUSER"])    : '';

	    if($postChain == 'Select a Debtor') {
          ?>
           <script type='text/javascript'>parent.showMsgBoxError('No Principal Debtor Selected <br>')</script> 
          <?php 
          unset($_POST['firstform']);	
          unset($_POST['selectDebtorForm']);	
          unset($_POST['selectTaskListForm']);
      } else {
           $TaskManDAO = new TaskManDAO($dbConn);
           $aWList = $TaskManDAO->getUserTasks($postPrinID, $postChain, $postTuser, $postTmonth);
           
        //   echo "<pre>";
        //   print_r($aWList);
           
           if (count($aWList)==0) {
               ?>
                <script type='text/javascript'>parent.showMsgBoxError('No Tasks found Allocate <br> Contact Kwelanga Support')</script> 
                <?php 
                unset($_POST['firstform']);	
                unset($_POST['selectDebtorForm']);	
                unset($_POST['selectTaskListForm']);
           } else {
           	
                ?>		
                <center>
                   <FORM name='AllocateUserTasks' method=post action=''>
                      <table width="950"; style="border:none">
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                           <td class=head1 colspan="12"; style="text-align:center";>Allocate Principal / Debtor Tasks to User </td>
                         </tr>
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                           <td width="5%"; style="border:none">&nbsp;</td>
                           <td width="9%"; style="border:none">&nbsp;</td>
                           <td width="9%"; style="border:none">&nbsp;</td>
                           <td width="9%"; style="border:none">&nbsp;</td>
                           <td width="9%"; style="border:none">&nbsp;</td>
                           <td width="9%"; style="border:none">&nbsp;</td>
                           <td width="9%"; style="border:none">&nbsp;</td>
                           <td width="9%"; style="border:none">&nbsp;</td>
                           <td width="5%"; style="border:none">&nbsp;</td>
                           <td width="9%"; style="border:none">&nbsp;</td>
                           <td width="13%"; style="border:none">&nbsp;</td>
                           <td width="5%";  style="border:none">&nbsp;</td>
                         </tr>
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            <td >&nbsp;</td>
                            <td class=det1 colspan="1";>Principal </td>
                            <td class=det2 colspan="5";><?php echo trim($aWList[0]['principal_name']); ?></td>
                                                        <input type="hidden" name="PRINUID"    value=<?php echo $aWList[0]['principal_uid']; ?>>
                         	                              <input type="hidden" name="DEBTLIST"   value=<?php echo $aWList[0]['debtor_uid'];    ?>>
                         	                              <input type="hidden" name="TMONTH"     value=<?php echo $postTmonth; ?>>
                         	                              <input type="hidden" name="TUSER"      value=<?php echo $postTuser;  ?>>
                            <td class=det1 colspan="2";>Month </td>
                            <td class=det2 colspan="2";><?php echo $postTmonth; ?></td>
                         	  <td >&nbsp;</td>
                         </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                           <td Colspan="12">&nbsp;</td>
                         </tr>
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                         	  <td >&nbsp;</td>
                         	  <td class=det1 colspan="1";>Debtor </td>
                            <td class=det2 colspan="5";><?php echo trim($aWList[0]['debtor_name']); ?></td>
                            <td class=det1 colspan="2";>Start Date</td>
                            <td class=det2 colspan="2";><?php echo $aWList[0]['start_date']; ?></td>
                         	  <td >&nbsp;</td>
                         </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                           <td Colspan="12">&nbsp;</td>
                         </tr>
                          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                         	  <td >&nbsp;</td>
                            <td class=det1 colspan="2";>Task</td>
                            <td class=det1 colspan="4";>Comments</td>
                            <td class=det1 colspan="2";>Due by</td>
                            <td class=det1 colspan="1";>Manage</td>
                            <td class=det1 colspan="1";>Status</td>
                            <td >&nbsp;</td>
                         </tr>
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            <td Colspan="12">&nbsp;</td>
                         </tr>
                         <?php
                         foreach ($aWList as $row) { ?>
                                <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                   <td >&nbsp;</td>
                                   <td class=det2 colspan="2";><?php echo $row['task']; ?></td>
                                                               <input type="hidden" name="TASKUID[]" value=<?php echo $row['task_uid']; ?>></td>
                                  	                           <input type="hidden" name="INPUTID[]"   value=<?php echo $row['inputId'];    ?>>          
                                   <td class=det2 colspan="4";><?php echo $row['comment'];?></td>
                                   <td class=det2 colspan="2";><?php echo $row['cal_date']; ?>
                                   	                           <input type="hidden" name="CALDATE[]" value=<?php echo $row['cal_date']; ?>></td>  
                                   	<?php
                                   if($row['feedback_status'] == 'C') {
                                          $light1 = 'pp_bullet_green';
                                          $light2 = 'pp_bullet_green';
                                          $light3 = 'pp_bullet_green';
                                   } else {
 	     	                                  if (date("Y-m-d") < trim($row['cal_date'])) {
                                              $light1 = 'pp_bullet_orange';
                                              $light2 = 'pp_bullet_orange';
                                              $light3 = 'pp_bullet_orange';
                                          } elseif (date("Y-m-d") == trim($row['cal_date'])) {
                                              $light1 = 'pp_bullet_orange';
                                              $light2 = 'pp_bullet_orange';
                                              $light3 = 'pp_bullet_red';
                                          } else {
                                              $light1 = 'pp_bullet_red';
                                              $light2 = 'pp_bullet_red';
                                              $light3 = 'pp_bullet_red';
                                          }
                                   }         ?>     	     	
                                   <td class=det2 colspan="1";><INPUT TYPE="radio" name="SELECTTASK[]" value= "<?php echo $row['task_transaction_uid'];?>" <?php echo $check ;?>></td>
                                   <td ><img src="<?php echo $ROOT.$PHPFOLDER ?>images/<?php echo $light1 ?>.png" style="width:10px; height:10px; float:left;">
                                   	    <img src="<?php echo $ROOT.$PHPFOLDER ?>images/pp_bullet_space.png" style="width:10px; height:10px; float:left;">
                                   	    <img src="<?php echo $ROOT.$PHPFOLDER ?>images/<?php echo $light2 ?>.png" style="width:10px; height:10px; float:left;">
                                   	    <img src="<?php echo $ROOT.$PHPFOLDER ?>images/pp_bullet_space.png" style="width:10px; height:10px; float:left;">
                                   	    <img src="<?php echo $ROOT.$PHPFOLDER ?>images/<?php echo $light3 ?>.png" style="width:10px; height:10px; float:left;"></td>
                                   <td >&nbsp;</td>
                                </tr>              
                                <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                   <td Colspan="12">&nbsp;</td>
                                </tr>
                         <?php 
                         } ?>
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                             <td Colspan="12">&nbsp;</td>
                          </tr>
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                             <td colspan="12"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="captureTaskDetail" value= "Capture Task Detail">
                                                                          <INPUT TYPE="submit" class="submit" name="backform"   value= "Back">
                                                                          <INPUT TYPE="submit" class="submit" name="canform"   value= "Cancel"></td>
                         </tr>          
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                           <td Colspan="12">&nbsp;</td>
                         </tr>  
                      </table>
                   </form>
                </center>
<?php	
           }
      }
}
// Debtor*********************************************************************************************************************************************      
if (isset($_POST['selectDebtorForm'])) {
	
     $postPrincipal   = (isset($_POST["Principal"])) ? htmlspecialchars($_POST["Principal"]) : '';
     $postUser        = (isset($_POST["TUSER"]))     ? htmlspecialchars($_POST["TUSER"])     : '';
     $postMonth       = (isset($_POST["tMonth"]))    ? htmlspecialchars($_POST["tMonth"])     : '';
	
     if($postUser  == 'Select a User') {
          ?>
           <script type='text/javascript'>parent.showMsgBoxError('No User Selected <br>')</script> 
          <?php 
          unset($_POST['firstform']);
          unset($_POST['selectDebtorForm']);
     } elseif( $postMonth == 'Select a Month') {
          ?>
           <script type='text/javascript'>parent.showMsgBoxError('No Month Selected <br>')</script> 
          <?php 
          unset($_POST['firstform']);
          unset($_POST['selectDebtorForm']);
     } elseif($postPrincipal == 'Select a Principal') {
          ?>
           <script type='text/javascript'>parent.showMsgBoxError('No Principal Selected <br>')</script> 
          <?php 
          unset($_POST['firstform']);
          unset($_POST['selectDebtorForm']);
     } else {
        $TaskManDAO = new TaskManDAO($dbConn);
        $uCList = $TaskManDAO->getPrincipalDebtorsList($postPrincipal);	
        ?>
        <center>
           <FORM name='GetPrincipalChain' method=post action=''>
                <table width="720"; style="border:none">
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td class=head1 colspan="6"; style="text-align:center";>Select Principal Debtor</td>
                   </tr>
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td width="10%"; style="border:none">&nbsp;</td>
                     <td width="10%"; style="border:none">&nbsp;</td>
                     <td width="30%"; style="border:none">&nbsp;</td>
                     <td width="15%"; style="border:none">&nbsp;</td>
                     <td width="30%"; style="border:none">&nbsp;</td>
                     <td width="5%"; style="border:none">&nbsp;</td>
                   </tr>
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                   	  <td >&nbsp;</td>
                      <td class=det1 colspan="2";>Principal </td>
                   	  <td class=det2 colspan="2";><?php echo $uCList[0]['principal_name']; ?></td>
                   	                              <input type="hidden" name="PRINID"  value=<?php echo $uCList[0]['principal_uid']; ?>>
                   	                              <input type="hidden" name="TMONTH"  value=<?php echo $postMonth; ?>>
                   	                              <input type="hidden" name="TUSER"   value=<?php echo $postUser; ?>> 
                   	  <td >&nbsp;</td>
                   </tr>
                  <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td Colspan="6">&nbsp;</td>
                   </tr>
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                      <td >&nbsp;</td>
                      <td class=det1 colspan="2";>Select Principal Debtor </td>
                      <td colspan="2"; style="text-align:left;">
                           <select name="DEBTLIST" id="DEBTLIST">
                               <option value="Select a Debtor"><?php echo 'Select a Debtor' ?></option>
                                     <?php foreach($uCList as $row) {?>
                                           <option value="<?php echo trim($row['debtor_uid']) ; ?>"><?php echo $row['debtor_code'] . ' - ' . $row['debtor_name']; ?></option>
                                     <?php } ?>
                           </select>
                      </td> 
                      <td >&nbsp;</td>
                   </tr>  
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td Colspan="6">&nbsp;</td>
                   </tr>
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td colspan="6"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="selectTaskListForm"= "Set Up User Activities">
                                                                 <INPUT TYPE="submit" class="submit" name="backform"   value= "Back">
                                                                 <INPUT TYPE="submit" class="submit" name="canform"    value= "Cancel"></td>
                   </tr>          
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td Colspan="6">&nbsp;</td>
                   </tr>  
                </table>
				   </form>
        </center> 
      <?php
	   }	
}
// First Form  ********************************************************************************************************************************************* 
if(!isset($_POST['firstform']) && !isset($_POST['selectDebtorForm']) 
                               && !isset($_POST['selectTaskListForm']) 
                               && !isset($_POST['captureTaskDetail'])) {
	
	  $TaskManDAO = new TaskManDAO($dbConn);
    $userList = $TaskManDAO->getTaskUserCapture($userUId);
    
    if(count($userList) == 0) {
          ?>
           <script type='text/javascript'>parent.showMsgBoxError('User list empty <br> Contact Support')</script> 
          <?php 
          unset($_POST['firstform']);
    }
    
	  $TaskManDAO = new TaskManDAO($dbConn);
    $monthList = $TaskManDAO->getTaskMonth();

    if(count($monthList) == 0) {
          ?>
           <script type='text/javascript'>parent.showMsgBoxError('Month list empty <br> Contact Support')</script> 
          <?php 
          unset($_POST['firstform']);
    }
    
    $TaskManDAO = new TaskManDAO($dbConn);
    $uPList = $TaskManDAO->getDebtorAdminPricipalList();
    
    $class = 'even';
    ?>
    <center>
       <FORM name='AddWarehouseToExtract' method=post action=''>
            <table width="720"; style="border:none">
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td class=head1 colspan="6"; style="text-align:center";>User Task Mamagement</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td width="10%"; style="border:none">&nbsp;</td>
                 <td width="10%"; style="border:none">&nbsp;</td>
                 <td width="30%"; style="border:none">&nbsp;</td>
                 <td width="15%"; style="border:none">&nbsp;</td>
                 <td width="30%"; style="border:none">&nbsp;</td>
                 <td width="5%";  style="border:none">&nbsp;</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
               	  <td >&nbsp;</td>
               	  <td class=det1 colspan="2";>Task User</td>
                  <td class=det2 colspan="2";><?php echo $userList[0]['full_name']; ?></td>
                                              <input type="hidden" name="TUSER"  value=<?php echo $userList[0]['uid']; ?>>                  
                  <td >&nbsp;</td>
               </tr> 	  
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="6">&nbsp;</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
               	  <td >&nbsp;</td>
                  <td class=det1 colspan="2">Select Month</td>
               	  <td colspan="2"; style="text-align:left;">
               	  	   <select name="tMonth" id="tMonth">
                           <option value="Select a Month"><?php echo 'Select a Month' ?></option>
                                 <?php foreach($monthList as $row) { ?>
                                       <option value="<?php echo trim($row['uid']) ; ?>"><?php echo $row['month_no'] . " - " . $row['month_start'] . " to " . $row['month_end']; ?></option>
                                 <?php } ?>
                       </select>
                  </td> 
                  <td >&nbsp;</td>
               </tr> 	  
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="6">&nbsp;</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
               	  <td >&nbsp;</td>
                  <td class=det1 colspan="2">Select Debtor Admin Principal </td>
               	  <td colspan="2"; style="text-align:left;">
               	  	   <select name="Principal" id="Principal">
                           <option value="Select a Principal"><?php echo 'Select a Principal' ?></option>
                                 <?php foreach($uPList as $row) { ?>
                                       <option value="<?php echo trim($row['principal_uid']) ; ?>"><?php echo $row['principal']; ?></option>
                                 <?php } ?>
                       </select>
                  </td> 
                  <td >&nbsp;</td>
               </tr> 	  
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="6">&nbsp;</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="6">&nbsp;</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td colspan="6"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="selectDebtorForm" value= "Debtor List">
                                                             <INPUT TYPE="submit" class="submit" name="canform"   value= "Cancel"></td>
               </tr>          
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="6">&nbsp;</td>
               </tr>  
 			    	</table>
		   </form>
    </center> 
<?php 
}
?>
    </body>       
</HTML>

<?php

 ?>
 

