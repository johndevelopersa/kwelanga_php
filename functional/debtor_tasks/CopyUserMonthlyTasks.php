<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER."properties/Constants.php");
    include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
    include_once($ROOT.$PHPFOLDER."libs/CommonUtils.php");
    include_once($ROOT.$PHPFOLDER."DAO/TaskManDAO.php");	    
    include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
    include_once($ROOT.$PHPFOLDER.'TO/DmTaskTO.php');
        
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

		<TITLE>Copy User Tasks</TITLE>

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
    	</style>

		</HEAD>
    <body>
<?php
//   saveform *********************************************************************************************************************************************      
if (isset($_POST['saveform']))  {

	    $postChain   = (isset($_POST["DEBTLIST"]))   ? htmlspecialchars($_POST["DEBTLIST"])      : '';
	    $postPrinID  = (isset($_POST["PRINUID"]))    ? htmlspecialchars($_POST["PRINUID"])       : '';
	    $postTmonth  = (isset($_POST["TMONTH"]))     ? htmlspecialchars($_POST["TMONTH"])        : '';
	    $postTuser   = (isset($_POST["TUSER"]))      ? htmlspecialchars($_POST["TUSER"])         : '';

      $TaskManDAO = new TaskManDAO($dbConn);
      $monthList = $TaskManDAO->getDatePeriod($postTmonth);
      
      $taskSuccess = 'S';

      foreach($_POST["TASKUID"] as $tKey=>$erow) {

             if (in_array($erow, $_POST['SELECTWH'])) {
                  $as = "A";
             } else {
                  $as = "D";
             }
        
             $DmTaskTO  = new DmTaskTO;
             $DmTaskTO->PrincipalUid = $postPrinID ;
             $DmTaskTO->DebtorList   = $postChain;        
             $DmTaskTO->Year         = substr($monthList[0]['month_start'],0,4);
             $DmTaskTO->StartDate    = $monthList[0]['month_start'];          
             $DmTaskTO->Month        = $postTmonth;             
	           $DmTaskTO->TaskUid      = $erow;
	           $DmTaskTO->InputType    = $_POST['INPUTID'][$tKey];
	           $DmTaskTO->dueDate      = $_POST['CALDATE'][$tKey];          
	           $DmTaskTO->Comments     = $_POST['COMM'][$tKey];      
             $DmTaskTO->OwnerID      = $postTuser ;         
             $DmTaskTO->CapturedBy   = $userUId;
             $DmTaskTO->Status       = $as;  
             
             $TaskManDAO = new TaskManDAO($dbConn);
             $errorTO = $TaskManDAO->postTaskTransaction($DmTaskTO, 'alloCapture');

             if($errorTO->type <> 'S') {
                 $taskSuccess = 'F';
             }
      }
      if($taskSuccess == 'S') {
         ?>
         <script type='text/javascript'>parent.showMsgBoxInfo('Update Successful <br>')</script>
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
           $aWList = $TaskManDAO->Getusertasks($postPrinID, $postChain, $postTuser, $postTmonth);
           
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
                      <table width="900"; style="border:none">
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                           <td class=head1 colspan="11"; style="text-align:center";>Allocate Principal / Debtor Tasks to User </td>
                         </tr>
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                           <td width="5%"; style="border:none">&nbsp</td>
                           <td width="10%"; style="border:none">&nbsp</td>
                           <td width="10%"; style="border:none">&nbsp</td>
                           <td width="10%"; style="border:none">&nbsp</td>
                           <td width="10%"; style="border:none">&nbsp</td>
                           <td width="10%"; style="border:none">&nbsp</td>
                           <td width="10%"; style="border:none">&nbsp</td>
                           <td width="10%"; style="border:none">&nbsp</td>
                           <td width="10%"; style="border:none">&nbsp</td>
                           <td width="10%"; style="border:none">&nbsp</td>
                           <td width="5%";  style="border:none">&nbsp;</td>

                         </tr>
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            <td >&nbsp</td>
                            <td class=det1 colspan="1";>Principal </td>
                            <td class=det2 colspan="4";><?php echo trim($aWList[0]['principal_name']); ?></td>
                                                        <input type="hidden" name="PRINUID"    value=<?php echo $aWList[0]['principal_uid']; ?>>
                         	                              <input type="hidden" name="DEBTLIST"   value=<?php echo $aWList[0]['debtor_uid'];    ?>>
                         	                              <input type="hidden" name="TMONTH"     value=<?php echo $postTmonth; ?>>
                         	                              <input type="hidden" name="TUSER"      value=<?php echo $postTuser;  ?>>
                            <td class=det1 colspan="2";>Month </td>
                            <td class=det2 colspan="2";><?php echo $postTmonth; ?></td>
                         	  <td >&nbsp</td>
                         </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                           <td Colspan="11">&nbsp</td>
                         </tr>
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                         	  <td >&nbsp</td>
                         	  <td class=det1 colspan="1";>Debtor </td>
                            <td class=det2 colspan="4";><?php echo trim($aWList[0]['debtor_name']); ?></td>
                            <td class=det1 colspan="2";>Start Date</td>
                            <td class=det2 colspan="2";><?php echo $aWList[0]['start_date']; ?></td>
                         	  <td >&nbsp</td>
                         </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                           <td Colspan="11">&nbsp</td>
                         </tr>
                          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                         	  <td >&nbsp</td>
                            <td class=det1 colspan="2";>Task</td>
                            <td class=det1 colspan="2";>Due by</td>
                            <td class=det1 colspan="4";>Comments</td>
                            <td class=det1 colspan="1";>Allocated</td>
                            <td >&nbsp</td>
                         </tr>
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            <td Colspan="11">&nbsp</td>
                         </tr>
                         <?php
                         foreach ($aWList as $row) { 
                          	      if($row['Task_Allocated'] == 'A' && $row['dtt_status'] == 'A') {
                                      $check = 'CHECKED';
                                  } else { $check= '';}
                         ?>
                                <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                   <td >&nbsp</td>
                                   <td class=det2 colspan="2";><?php echo $row['task']; ?></td>
                                                               <input type="hidden" name="TASKUID[]" value=<?php echo $row['task_uid']; ?>></td>
                                  	                           <input type="hidden" name="INPUTID[]"   value=<?php echo $row['inputId'];    ?>>          
                                  <td class=det2 colspan="2";><?php echo $row['cal_date']; ?>
                                   	                           <input type="hidden" name="CALDATE[]" value=<?php echo $row['cal_date']; ?>></td>
                                   <td class=det2 colspan="4";><textarea id="comm" name="COMM[]" rows="1" cols="40"><?php echo $row['comment'];?></textarea></td>
                                   <td class=det2 colspan="1";><INPUT TYPE="checkbox" name="SELECTWH[]" value= "<?php echo $row['task_uid'];?>" <?php echo $check ;?>></td>
                                   <td >&nbsp</td>
                                </tr>              
                                <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                   <td Colspan="11">&nbsp</td>
                                </tr>
                         <?php 
                         } ?>
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                             <td Colspan="11">&nbsp</td>
                          </tr>
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                             <td colspan="11"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="saveform" value= "Save Selection">
                                                                         <INPUT TYPE="submit" class="submit" name="canform"   value= "Cancel"></td>
                         </tr>          
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                           <td Colspan="11">&nbsp</td>
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
     $postUser        = (isset($_POST["tUser"]))     ? htmlspecialchars($_POST["tUser"])     : '';
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
                     <td width="10%"; style="border:none">&nbsp</td>
                     <td width="10%"; style="border:none">&nbsp</td>
                     <td width="30%"; style="border:none">&nbsp</td>
                     <td width="15%"; style="border:none">&nbsp</td>
                     <td width="30%"; style="border:none">&nbsp</td>
                     <td width="5%"; style="border:none">&nbsp</td>
                   </tr>
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                   	  <td >&nbsp</td>
                      <td class=det1 colspan="2";>Principal </td>
                   	  <td class=det2 colspan="2";><?php echo $uCList[0]['principal_name']; ?></td>
                   	                              <input type="hidden" name="PRINID"  value=<?php echo $uCList[0]['principal_uid']; ?>>
                   	                              <input type="hidden" name="TMONTH"  value=<?php echo $postMonth; ?>>
                   	                              <input type="hidden" name="TUSER"   value=<?php echo $postUser; ?>> 
                   	  <td >&nbsp</td>
                   </tr>
                  <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td Colspan="6">&nbsp</td>
                   </tr>
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                      <td >&nbsp</td>
                      <td class=det1 colspan="2";>Select Principal Debtor </td>
                      <td colspan="2"; style="text-align:left;">
                           <select name="DEBTLIST" id="DEBTLIST">
                               <option value="Select a Debtor"><?php echo 'Select a Debtor' ?></option>
                                     <?php foreach($uCList as $row) {?>
                                           <option value="<?php echo trim($row['debtor_uid']) ; ?>"><?php echo $row['debtor_code'] . ' - ' . $row['debtor_name']; ?></option>
                                     <?php } ?>
                           </select>
                      </td> 
                      <td >&nbsp</td>
                   </tr>  
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td Colspan="6">&nbsp</td>
                   </tr>
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td colspan="6"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="selectTaskListForm"= "Set Up User Activities">
                                                                 <INPUT TYPE="submit" class="submit" name="canform"    value= "Cancel"></td>
                   </tr>          
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td Colspan="6">&nbsp</td>
                   </tr>  
                </table>
				   </form>
        </center> 
      <?php
	   }	
}
// First Form  ********************************************************************************************************************************************* 
if(!isset($_POST['firstform']) && !isset($_POST['selectDebtorForm']) && !isset($_POST['selectTaskListForm']) && !isset($_POST['saveform'])) {
	
	  $TaskManDAO = new TaskManDAO($dbConn);
    $userList = $TaskManDAO->getTaskUsers();
    
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
                 <td class=head1 colspan="6"; style="text-align:center";>Copy User Tasks to another Period / Debtor</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td width="10%"; style="border:none">&nbsp</td>
                 <td width="10%"; style="border:none">&nbsp</td>
                 <td width="30%"; style="border:none">&nbsp</td>
                 <td width="15%"; style="border:none">&nbsp</td>
                 <td width="30%"; style="border:none">&nbsp</td>
                 <td width="5%";  style="border:none">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
               	  <td >&nbsp</td>
                  <td class=det1 colspan="2">Select Task User</td>
               	  <td colspan="2"; style="text-align:left;">
               	  	   <select name="tUser" id="tUser">
                           <option value="Select a User"><?php echo 'Select a User' ?></option>
                                 <?php foreach($userList as $row) { ?>
                                       <option value="<?php echo trim($row['uid']) ; ?>"><?php echo $row['full_name']; ?></option>
                                 <?php } ?>
                       </select>
                  </td> 
                  <td >&nbsp</td>
               </tr> 	  
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="6">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
               	  <td >&nbsp</td>
                  <td class=det1 colspan="2">Select Month to copy Tasks From</td>
               	  <td colspan="2"; style="text-align:left;">
               	  	   <select name="tMonth" id="tMonth">
                           <option value="Select a Month"><?php echo 'Select a Month' ?></option>
                                 <?php foreach($monthList as $row) { ?>
                                       <option value="<?php echo trim($row['uid']) ; ?>"><?php echo $row['month_no'] . " - " . $row['month_start'] . " to " . $row['month_end']; ?></option>
                                 <?php } ?>
                       </select>
                  </td> 
                  <td >&nbsp</td>
               </tr> 	  
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="6">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
               	  <td >&nbsp</td>
                  <td class=det1 colspan="2">Select Debtor Admin Principal </td>
               	  <td colspan="2"; style="text-align:left;">
               	  	   <select name="Principal" id="Principal">
                           <option value="Select a Principal"><?php echo 'Select a Principal' ?></option>
                                 <?php foreach($uPList as $row) { ?>
                                       <option value="<?php echo trim($row['principal_uid']) ; ?>"><?php echo $row['principal']; ?></option>
                                 <?php } ?>
                       </select>
                  </td> 
                  <td >&nbsp</td>
               </tr> 	  
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="6">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="6">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td colspan="6"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="selectDebtorForm" value= "Debtor List">
                                                             <INPUT TYPE="submit" class="submit" name="canform"   value= "Cancel"></td>
               </tr>          
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="6">&nbsp</td>
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
// ********************************************************************************************************************************************************      

 ?>
 

