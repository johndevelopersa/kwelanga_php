<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER."properties/Constants.php");
    include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
    include_once($ROOT.$PHPFOLDER."libs/CommonUtils.php");    
    include_once($ROOT.$PHPFOLDER."elements/datePickerElement.php"); 
    include_once($ROOT.$PHPFOLDER.'elements/basicSelectElement.php');
    include_once($ROOT.$PHPFOLDER."DAO/TripSheetDAO.php");
    include_once($ROOT.$PHPFOLDER."DAO/ReportsDAO.php");    

    if (!isset($_SESSION)) session_start() ;
      $userUId     = $_SESSION['user_id'] ;
      $principalId = $_SESSION['principal_id'] ;
      $systemId    = $_SESSION["system_id"];
      $systemName  = $_SESSION['system_name'];

      //Create new database object
      $dbConn = new dbConnect(); 
      $dbConn->dbConnection();
      $errorTO = new ErrorTO;

?>  
<!DOCTYPE html>
<HTML>
	<HEAD>

		<TITLE>Principal - Warehouse Selection</TITLE>

		<link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/1_kwelanga.css' rel='stylesheet' type='text/css'>
		<link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/kos_standard_screen.css' rel='stylesheet' type='text/css'>
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
                
      td.det1  {border-style:none; 
                text-align: left; 
                font-weight: bold; 
                font-size: 13px;
                padding: 0 150px 0 150px  }
    	
    	</style>

		</HEAD>
    <body>
    <?php

    if (isset($_POST['canform'])) {
          return;    
    }

    if (isset($_POST['backform'])) {
       unset($_POST['MODTRANSP']); 
       unset($_POST['ADDTRANSP']);
    } 

    if (isset($_POST['ADDTRAN'])) {
        if($_POST['WAREHOUSE'] <> 'Select a Warehouse') { 
        	   if (isset($_POST["TNAME"]))   $postTNAME   = test_input($_POST["TNAME"]);   else $postTNAME  = '';
        	   if (isset($_POST["WAREHOUSE"])) $postWAREHOUSE = test_input($_POST["WAREHOUSE"]); else $postWAREHOUSE  = '';
             if(strlen($postTNAME) > 3) {
                    $TripsheetDAO = new TripsheetDAO($dbConn);
                    $tNam = $TripsheetDAO->checkTransporter($postTNAME, $postWAREHOUSE);
                    
                    if(count($tNam) == 0) {
                        $TripsheetDAO = new TripsheetDAO($dbConn);
                        $errorTO = $TripsheetDAO->addTransporter($postTNAME, $postWAREHOUSE);
                        
                        if ($errorTO->type=FLAG_ERRORTO_SUCCESS) { ?>
                              <script type='text/javascript'>parent.showMsgBoxInfo('New transporter added')</script>  
                              <?php
                              unset($_POST['MODTRANSP']); 
                              unset($_POST['ADDTRANSP']);
                        } else {?>
                        	    <script type='text/javascript'>parent.showMsgBoxError('Add Transporter Failed <br><br> Contact Kwelanga Support')</script>
                             <?php
                             print_r($errorTO);	
                             unset($_POST['MODTRANSP']); 
                             unset($_POST['ADDTRANSP']);
                        }
                    } else { ?>
                            <script type='text/javascript'>parent.showMsgBoxError('Transporter with same name already Exists <br><br> Try Again')</script>
                            <?php	
                            unset($_POST['MODTRANSP']); 
                            unset($_POST['ADDTRANSP']);
                    }
             } else { ?>
                  <script type='text/javascript'>parent.showMsgBoxError('Transport Name Blank or too Short (Minimum 4) <br><br> Try Again')</script>
                  <?php	
                  unset($_POST['MODTRANSP']); 
                  unset($_POST['ADDTRANSP']);
             }
        } else { ?>
            <script type='text/javascript'>parent.showMsgBoxError('Warehouse Not Selected <br><br> Try Again')</script>
            <?php	
             unset($_POST['MODTRANSP']); 
             unset($_POST['ADDTRANSP']);
        }
    }

    if (isset($_POST['MODTRANSP'])) {
        $TripsheetDAO = new TripsheetDAO($dbConn);     
        $depl = $TripsheetDAO->getUserWarehouses($userUId) ;
    
        $class = 'odd'; ?>   
        <center>
           <FORM name='Transporter Report' method=post action='' onload='setFocusWhselect()'>
                <table width="720"; style="border:none">
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td Colspan="5">&nbsp</td>
                   </tr>  
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td class=head1 colspan="5"; style="text-align:center";>Select Requied Warehouse</td>
                   </tr>
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td width="5%"; style="border:none">&nbsp</td>
                     <td width="40%"; style="border:none">&nbsp</td>
                     <td width="40%"; style="border:none">&nbsp</td>
                     <td width="10%"; style="border:none">&nbsp</td>
                     <td width="5%"; style="border:none">&nbsp</td>
                   </tr>
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                         <td >&nbsp</td>
                         <td class=head1 style="text-align:left;">Warehouse</td>
                         <td colspan="2"; style="text-align:left;">
                             <select name="WAREHOUSE" id="WAREHOUSE">
                                 <option value="Select a Warehouse"><?php echo 'Select a Warehouse' ?></option>
                                       <?php foreach($depl as $drow) { ?>
                                               <option value="<?php echo trim($drow['warehouse_uid']) ."-". $drow['warehouse']; ?>"><?php echo $drow['warehouse']; ?></option>
                                       <?php } ?>
                              </select>
                         </td>             
                         <td>&nbsp</td>
                   </tr>               
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td Colspan="5">&nbsp</td>
                   </tr>
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        <td style="border:none;">&nbsp</td>
                        <td class="head1" colspan="1" style="text-align:center; border:none; "><label class="label" for="STATUS">Active</label>&nbsp;<input type="radio" name="STATUS" value="ACTIVE" CHECKED ></td>
                        <td class="head1" colspan="1" style="text-align:center; border:none; "><label class="label" for="STATUS">Deleted</label><input type="radio" name="STATUS" value="DELETED"></td>
                        <td colspan="2"; style="border:none;">&nbsp</td>    
                    </tr>
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        <td Colspan="5">&nbsp</td>
                   </tr>
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        <td Colspan="5">&nbsp</td>
                   </tr>
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                       <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="firstform" value= "Get Warehouse Transporters">
                                                                 <INPUT TYPE="submit" class="submit" name="backform"   value= "Back">
                                                                 <INPUT TYPE="submit" class="submit" name="canform"   value= "Cancel"></td>
                   </tr>          
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                       <td Colspan="5">&nbsp</td>
                   </tr>
                </table>
           </form>
        </center> 
       <?php
    } 

    if (isset($_POST['ADDTRANSP'])) {
    	
          $TripsheetDAO = new TripsheetDAO($dbConn);
          $uTS = $TripsheetDAO->getUserWarehouses($userUId); ?>
       
          <center>
             <form name='Maintain Transporter' method=post action='' onload='setFocusTPselect()'>
                <table width="720"; style="border:none">
                    <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        <td style="width:10%; border:none;">&nbsp</td>
                        <td style="width:30%; border:none;">&nbsp</td>
                        <td style="width:50%; border:none;">&nbsp</td>
                        <td style="width:10%; border:none;">&nbsp</td>
                    </tr>
                    <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        <td class="head1" colspan="4"; style="text-align:center; padding: 0 15px 0 20px ; ">Add Transporter</td>
                    </tr> 
                    <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        <td Colspan="4">&nbsp</td>
                    </tr>                    
                    <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        <td Colspan="1" >&nbsp</td>
                        <td class="head1" colspan="1" style="text-align:right; padding: 0 15px 0 20px ; ">Transporter Name </td>
                        <td Colspan="1" ><INPUT TYPE="TEXT" size="50" name="TNAME" id="TNAME" placeholder='New Transporter'></td>
                        <td Colspan="1" >&nbsp</td>
                    </tr>
                    <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        <td Colspan="4">&nbsp</td>
                    </tr>
                    
                    <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        <td >&nbsp</td>
                        <td class="head1" colspan="1" style="text-align:right; padding: 0 15px 0 20px ; ">Warehouse </td>
                       <td colspan="1"; style="text-align:left;">
                           <select name="WAREHOUSE" id="WAREHOUSE">
                               <option value="Select a Warehouse"><?php echo 'Select a Warehouse' ?></option>
                                     <?php foreach($uTS as $drow) { ?>
                                             <option value="<?php echo trim($drow['warehouse_uid']); ?>"><?php echo $drow['warehouse']; ?></option>
                                     <?php } ?>
                           </select>
                       </td>             
                       <td>&nbsp</td>
                    </tr>
                    <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                       <td Colspan="4">&nbsp</td>
                    </tr>  
                    <tr class="<?php echo GUICommonUtils::styleEO($class); ?>"> 
                        <td colspan="4"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="ADDTRAN"   value= "Add Transporter">
                        	                                          <INPUT TYPE="submit" class="submit" name="backform"   value= "Back">
                                                                    <INPUT TYPE="submit" class="submit" name="canform"   value= "Cancel"></td>
                    </tr>          
                    <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        <td Colspan="4">&nbsp</td>
                    </tr>
                </table>
             </form>
          </center> 
       
    <?php
    } 
    
    if (!isset($_POST['MODTRANSP']) && !isset($_POST['ADDTRANSP'])) { ?>
          <center>
             <form name='Maintain Transporte' method=post action='' onload='setFocusTPselect()'>
                <table width="720"; style="border:none">
                    <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        <td style="width:10%; border:none;">&nbsp</td>
                        <td style="width:40%; border:none;">&nbsp</td>
                        <td style="width:40%; border:none;">&nbsp</td>
                        <td style="width:10%; border:none;">&nbsp</td>
                    </tr>
                    <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        <td class="head1" colspan="4"; style="text-align:center; padding: 0 15px 0 20px ; ">Transporter Maintenance</td>
                    </tr> 
                    <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        <td Colspan="4">&nbsp</td>
                    </tr>
                    <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        <td >&nbsp</td>
                        <td class="head1" colspan="1"; style="text-align:right; padding: 0 15px 0 20px ; "><label class="label" for="MODTRANSP">Modify&nbsp;Transporter&nbsp;Details&nbsp;</label><input type="radio" name="MODTRANSP" onclick="javascript: submit()" value="MODIFY"></td>
                        <td class="head1" colspan="1"; style="text-align:right; padding: 0 20px 0 30px ; "><label class="label" for="ADDTRANSP">Add&nbsp;Transporter</label><input type="radio" name="ADDTRANSP" onclick="javascript: submit()" value="ADD"></td>	
                        <td >&nbsp</td>    
                     </tr>
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        <td Colspan="4">&nbsp</td>
                   </tr>  
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        <td Colspan="4">&nbsp</td>
                   </tr>  
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>"> 
                        <td colspan="4"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="backform"   value= "Back">
                                                                    <INPUT TYPE="submit" class="submit" name="canform"   value= "Cancel"></td>
                   </tr>          
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                       <td Colspan="4">&nbsp</td>
                    </tr>
                </table>
             </form>
          </center> 
    <?php
    } ?>
	  </body>       
</HTML>

<?php
 function test_input($data) {

  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  
  return $data;
 } ?>
 <script type="text/javascript" defer>
     function setFocusTPselect() {
          document.getElementById("WAREHOUSE").focus();
     }
function setFocusToTextBoxI(){
    document.getElementById("TRANSPORTER").focus();
}     

function selectAll(elementName, flag){
    $("input[name='"+elementName+"']").each( function(){$(this).attr("checked",((flag == 1)?true:false));})
}

function dupConfirm() {
	
    var userPreference;

		if (confirm("Do you want to save changes?") == true) {
         userPreference = "Data saved successfully!";
		} else {
         userPreference = "Save Canceled!";
		}

		document.getElementById("msg").innerHTML = userPreference;

}

</script>

<?php

?>



