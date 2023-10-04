<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER."properties/Constants.php");
    include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
    include_once($ROOT.$PHPFOLDER."libs/CommonUtils.php");
    include_once($ROOT.$PHPFOLDER."DAO/TaskManDAO.php");	    
    include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
		    
    if (!isset($_SESSION)) session_start() ;
      $userUId     = $_SESSION['user_id'] ;
      $principalId = $_SESSION['principal_id'] ;
      

      //Create new database object
      $dbConn  = new dbConnect(); 
      $dbConn->dbConnection();
      $errorTO = new ErrorTO;
      
      $postPrincipal   = (isset($_POST["Principal"])) ? htmlspecialchars($_POST["Principal"]) : '';
      $postChain       = (isset($_POST["Chain"]))     ? htmlspecialchars($_POST["Chain"])     : '';
      $postPrinID      = (isset($_POST["PRINID"]))    ? htmlspecialchars($_POST["PRINID"])    : '';

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
    	</style>

		</HEAD>
    <body>
<?php
      
// ********************************************************************************************************************************************************      
if (isset($_POST['canform'])) {
      return;    
}

if (isset($_POST['backform'])) {

      unset($_POST['firstform']);	
      unset($_POST['chainform']);	
}	
// saveform***********************************************************************************************************************************************      

if (isset($_POST['saveform'])) {
        
        $postPrincipal   = (isset($_POST["PRINUID"])) ? htmlspecialchars($_POST["PRINUID"]) : '';
        
        $postDebtor      = (isset($_POST["DEBTLIST"])) ? htmlspecialchars($_POST["DEBTLIST"]) : '';
        
        $poststatus      = (isset($_POST["stat"])) ? htmlspecialchars($_POST["stat"]) : '';
        
        // Delete existing task list
                
        $x = 0;
        foreach ($_POST['TASKUID'] as $key=>$urow) { 
 
             if(in_array($urow, $_POST['selectWh'])) {

                  if($poststatus == 'active') {$poststat = 'A';} else {$poststat = 'D';}
                  
                  if($_POST['ReqDay'][$key] == 'Select a Required Day') {
                         $postDay = 999;
                  } elseif($_POST['ReqDay'][$key] == 'Select') {
                         $postDay = 999;
                  } else {
                  	      $postDay = $_POST['ReqDay'][$key];
                  }
        	          
                  $TaskManDAO = new TaskManDAO($dbConn);
                  $errorTO = $TaskManDAO->insertNewPrinDebtorTasks($postPrincipal, $postDebtor, $_POST['TASKUID'][$key] , $postDay, $poststat, '');	
                                 
             } else {
                  $TaskManDAO = new TaskManDAO($dbConn);
                  $errorTO = $TaskManDAO->insertNewPrinDebtorTasks($postPrincipal, $postDebtor, $urow , NULL, 'D', 'SKIPINSERT' );
                  
                  	     	
             }
        }
        if($errorTO->type == 'S') {
           ?>
           <script type='text/javascript'>parent.showMsgBoxInfo('Update Successful <br>')</script>
           <?php 
        	
        }	else {
           ?>
           <script type='text/javascript'>parent.showMsgBoxError('Update Failed <br> Contact Support')</script> 
            <?php  
        }
        unset($_POST['firstform']);	
        unset($_POST['chainform']);	
        unset($_POST['saveform']);
        }
// Principal Debtor*********************************************************************************************************************************************      
if (isset($_POST['firstform'])) {
	
    if($postPrincipal == 'Select a Principal') {
          ?>
           <script type='text/javascript'>parent.showMsgBoxError('No Principal Selected <br>')</script> 
          <?php 
          unset($_POST['firstform']);
    
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
                   	  <td >&nbsp</td>
                   </tr>
                  <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td Colspan="6">&nbsp</td>
                   </tr>
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                      <td >&nbsp</td>
                      <td class=det1 colspan="2";>Select Principal Debtor </td>
                      <td colspan="2"; style="text-align:left;">
                           <select name="Chain" id="Chain">
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
                     <td colspan="6"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="addTask" value= "Set Up Debtor Activities">
                                                                 <INPUT TYPE="submit" class="submit" name="backform"   value= "Back">
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
}

// addTask*********************************************************************************************************************************************      
if (isset($_POST['addTask']))  {
	
	
	    if($postChain == 'Select a Debtor') {
          ?>
           <script type='text/javascript'>parent.showMsgBoxError('No Principal Debtor Selected <br>')</script> 
          <?php 
          unset($_POST['firstform']);
          unset($_POST['chainform']);
          unset($_POST['addTask']);
      } else {
           $TaskManDAO = new TaskManDAO($dbConn);
           $aWList = $TaskManDAO->getDebtorTasks($postPrinID, $postChain);
           if (count($aWList)==0) {
               ?>
                <script type='text/javascript'>parent.showMsgBoxError('No Tasks found for Management <br> Contact Kwelanga Support')</script> 
                <?php 
                unset($_POST['firstform']);
                unset($_POST['chainform']);
                unset($_POST['addTask']);
           } else {
           	
                $TaskManDAO = new TaskManDAO($dbConn);
                $reqList = $TaskManDAO->getRequiredDay()
                ?>		
                <center>
                   <FORM name='GetWarehousList' method=post action=''>
                      <table width="720"; style="border:none">
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                           <td class=head1 colspan="6"; style="text-align:center";>Manage Tasks</td>
                         </tr>
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                           <td width="10%"; style="border:none">&nbsp</td>
                           <td width="20%"; style="border:none">&nbsp</td>
                           <td width="30%"; style="border:none">&nbsp</td>
                           <td width="20%"; style="border:none">&nbsp</td>
                           <td width="15%"; style="border:none">&nbsp</td>
                           <td width="5%";  style="border:none">&nbsp</td>
                         </tr>
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                         	  <td >&nbsp</td>
                            <td class=det1 colspan="2";>Principal </td>
                            <td class=det2 colspan="2";><?php echo $aWList[0]['principal_name']; ?></td>
                         	                              <input type="hidden" name="PRINUID"    value=<?php echo $aWList[0]['principal_uid']; ?>>
                         	                              <input type="hidden" name="DEBTLIST"   value=<?php echo $aWList[0]['debtor_uid']; ?>>
                         	  <td >&nbsp</td>
                         </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                           <td Colspan="6">&nbsp</td>
                         </tr>
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                         	  <td >&nbsp</td>
                            <td class=det1 colspan="2";>Principal Chain </td>
                            <td class=det2 colspan="2";><?php echo $aWList[0]['debtor_name']; ?></td>
                         	  <td >&nbsp</td>
                         </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                           <td Colspan="6">&nbsp</td>
                         </tr>
                          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                         	  <td >&nbsp</td>
                            <td class=det1 colspan="1";>Debtor Name</td>
                            <td class=det1 colspan="1";>Selected</td>
                            <td class=det1 colspan="1";>Input Required</td>
                            <td class=det1 colspan="1";>Day Required</td>
                            <td >&nbsp</td>
                         </tr>
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            <td Colspan="6">&nbsp</td>
                         </tr>
                         <?php
                         foreach ($aWList as $row) { 
                         	      if($row['Task_Active'] == 'A') {
                                      $check = 'CHECKED';
                                } else { $check= '';}
                         ?>
                                <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                   <td >&nbsp</td>
                                                  <input type="hidden" name="TASKUID[]" value=<?php echo $row['TaskId']; ?>></td>
                                   <td class=det2 colspan="1";><?php echo $row['task']; ?></td>
                                   <td class=det2 colspan="1";><INPUT TYPE="checkbox" name="selectWh[]" value= "<?php echo $row['TaskId'];?>" <?php echo $check ;?>></td>
                                   <td class=det2 colspan="1";><?php echo $row['inputType']; ?></td>
                                   <?php if($row['inputId']==1 || $row['inputId']== 3 || $row['inputId']== 2) {
                                         if(trim($row['requiedDayUid']) == 999 || trim($row['requiedDayUid']) == NULL) {
                                         	    $opt = $optvalue = 'Select a Required Day';
                                         } else {
                                              $opt      = $row['requireDay'] . ' - ' . $row['requiredDayCatagory'] ;
                                              $optvalue = $row['requiedDayUid']  ;
                                         }
                                   ?>
                                         <td class=det2 colspan="1";>
                                   	           <select name="ReqDay[]" id="ReqDay">
                                                    <option value=<?php echo $optvalue ; ?> selected='selected'><?php echo $opt ?></option>                                                    
                                                    <?php foreach($reqList as $lrow) { ?>
                                                                <option value="<?php echo trim($lrow['requiedDayUid']) ; ?>"><?php echo $lrow['requireDay'] . ' - ' . $lrow['requiredDayCatagory']; ?></option>
                                                    <?php } ?>
                                               </select>
                                         </td>
                                   <?php } else { ?>
                                          <td class=det2 colspan="1";>&nbsp</td>
                                   <?php } ?>
                                   <td >&nbsp</td>
                                </tr>              
                                <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                   <td Colspan="6">&nbsp</td>
                                </tr>
                         <?php 
                         } ?>
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                         	  <td Colspan="2">&nbsp</td>
                            <td Colspan="1">
                                   <input type="radio" id="active" name="stat" value="active" CHECKED>
                                   <label for="stat">Active</label></td>
                            <td Colspan="1">
                                   <input type="radio" id="deleted" name="stat" value="deleted">
                                   <label for="deleted">Deleted</label></td>
                            </td>
                             <td Colspan="2">&nbsp</td>
                         </tr>

                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                             <td Colspan="6">&nbsp</td>
                          </tr>
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                             <td colspan="6"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="saveform" value= "Save Selection">
                             	                                           <INPUT TYPE="submit" class="submit" name="backform"   value= "Back">
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
      }
}

// ********************************************************************************************************************************************************      

if(!isset($_POST['firstform']) && !isset($_POST['chainform']) && !isset($_POST['addTask'])) {
	
    $TaskManDAO = new TaskManDAO($dbConn);
    $uPList = $TaskManDAO->getDebtorAdminPricipalList();
    
    $class = 'even';
    ?>
    <center>
       <FORM name='AddWarehouseToExtract' method=post action=''>
            <table width="720"; style="border:none">
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td class=head1 colspan="6"; style="text-align:center";>Set Up Kwelanga Task Management</td>
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
                 <td colspan="6"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="firstform" value= "Continue">
                                                             <INPUT TYPE="submit" class="submit" name="canform"   value= "Cancel"></td>
               </tr>          
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="6">&nbsp</td>
               </tr>  
 			    	</table>
		   </form>
    </center> 
<?php 
} ?>

	  </body>       
</HTML>

<?php
// ********************************************************************************************************************************************************      

 ?>
 

