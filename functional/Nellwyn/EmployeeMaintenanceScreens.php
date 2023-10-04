<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER."libs/GUICommonUtils.php");
    		    
class EmployeeMaintenanceScreens {
	
      function __construct() {

         global $dbConn;
         $this->dbConn = $dbConn;
         
       }
  
// ********************************************************************************************************************************      
      
      
   public function updateEmployeeDetails() { ?>

    <?php 
    }
// ********************************************************************************************************************************      
      
   public function insertNewEmployeeDetails() { ?>

    <?php 
    }    
// ********************************************************************************************************************************      
   public function findEmployeeDetails() { ?>

    <?php 
    }    
// ********************************************************************************************************************************      
            












/*
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
      $depotId     = $_SESSION['depot_id'] ;

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

    if(isset($_POST['UPDTRAN'])) { 
    	
        if (isset($_POST["TNAME"]))   $postTNAME   = test_input($_POST["TNAME"]);   else $postTNAME     = '';
        if (isset($_POST["TUID"]))    $postTUID    = test_input($_POST["TUID"]);    else $postTUID      = '';
    	  if (isset($_POST["NSTATUS"])) $postNSTATUS = test_input($_POST["NSTATUS"]); else $postNSTATUS   = '';  

        if(strlen($postTNAME) > 3) {
              $TripsheetDAO = new TripsheetDAO($dbConn);
              $errorTO = $TripsheetDAO->updateTransporter($postTUID, $postTNAME, $postNSTATUS);

              if ($errorTO->type==FLAG_ERRORTO_SUCCESS) { ?>
                    <script type='text/javascript'>parent.showMsgBoxInfo('Transporter Update Successfully')</script>  
                    <?php
                    unset($_POST['MODTRANSP']); 
                    unset($_POST['ADDTRANSP']);
              } else { ?>
                   	<script type='text/javascript'>parent.showMsgBoxError('Transporter UpdateFailed <br><br> Contact Kwelanga Support')</script>
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
    }
     
    if(isset($_POST['SELMOD'])) { ?>  	
       <center>
          <form name='Maintain Transporter' method=post action='' onload='setFocusTPselect()'>
             <table width="720"; style="border:none">
                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td style="width:30%; border:none;">&nbsp</td>
                     <td style="width:20%; border:none;">&nbsp</td>
                     <td style="width:20%; border:none;">&nbsp</td>
                     <td style="width:30%; border:none;">&nbsp</td>
                 </tr>
                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td class="head1" colspan="4"; style="text-align:center; padding: 0 15px 0 20px ; ">Update Transporter Details</td>
                 </tr> 
                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td Colspan="4">&nbsp</td>
                 </tr>
                 <?php
                 if($_POST['CURSTAT'] == "D") {
                    $dCheck = 'CHECKED';
                    $aCheck = '';
                 } else {
                    $dCheck = '';
                    $aCheck = 'CHECKED';
                 } ?>
                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td Colspan="1" >&nbsp</td>
                     <td class="head1" colspan="1" style="text-align:left; padding: 0 0px 0 0px ; ">Transporter Name </td>
                     <td Colspan="2" ><INPUT TYPE="TEXT" size="40" name="TNAME" id="TNAME" value= <?php echo trim(substr($_POST["SELMOD"],strpos($_POST["SELMOD"],"-") + 1,50)); ?>></td>
                 </tr>
                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td Colspan="4">&nbsp<input type="hidden" id="TUID" name="TUID" value= <?php echo trim(substr($_POST["SELMOD"],0,strpos($_POST["SELMOD"],"-"))); ?>></td>
                 </tr>
                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td Colspan="1">&nbsp</td>
                     
                      <td class="det2" colspan="1" style="text-align:right; border: none; padding: 0px 25px 0px 25px;"><input type="radio" id="ACTIVE"  name="NSTATUS" value="A" <?php echo $aCheck; ?>><label class="label" for="ACTIVE">Active</td>
                      <td class="det2" colspan="1" style="text-align:right; border: none; padding: 0px 25px 0px 25px;"><input type="radio" id="DELETED" name="NSTATUS" value="D" <?php echo $dCheck; ?>><label class="label" for="DELETED">Deleted</label></td>
                     <td Colspan="1">&nbsp</td>
                 </tr>  
                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td Colspan="4">&nbsp</td>
                 </tr>
                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>"> 
                     <td colspan="4"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="UPDTRAN"   value= "Update Transporter">
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
    if (isset($_POST['ADDTRAN'])) {
        if (isset($_POST["TNAME"]))   $postTNAME   = test_input($_POST["TNAME"]);   else $postTNAME  = '';
        if(strlen($postTNAME) > 3) {
               $TripsheetDAO = new TripsheetDAO($dbConn);
               $tNam = $TripsheetDAO->checkTransporter($postTNAME, $depotId);
               
               if(count($tNam) == 0) {
                   $TripsheetDAO = new TripsheetDAO($dbConn);
                   $errorTO = $TripsheetDAO->addTransporter($postTNAME, $depotId);
                   
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

    }

    if (isset($_POST['MODTRANSP']) || isset($_POST["SUBMITFILTER"])  ) { 

         if (isset($_POST["STATUS"])) $postStat = test_input($_POST["STATUS"]);  else $postStat = '';
         if (isset($_POST["TRUID"]))  $postTrUid = test_input($_POST["TRUID"]);   else $postTrUid = 'xxxxxxxxxxx';
         $TripsheetDAO = new TripsheetDAO($dbConn);
         $mfDD = $TripsheetDAO->getTripSheetTransporter2($depotId, $postStat, $postTrUid)
    	
    	?> 
    	
         <center>
              <form name='Invoices' method=post target=''>	
                  <table width="720px"; style="border:none">
                      <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                           <td class="det1" colspan="5" style="text-align:center;">Transporter to Edit</td>
                      </tr>    
                      <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                           <td width="10%"; style="border: none;">&nbsp;</td>
                           <td width="50%"; style="border: none;">&nbsp;</td>
                           <td width="18%"; style="border: none;">&nbsp;</td>
                           <td width="18%"; style="border: none;">&nbsp;</td>
                           <td width="4%";  style="border: none;">&nbsp;</td>
                      <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td colspan="1" style="border: none;">&nbsp</td>
                          <td class=det2 colspan="1" style="text-align:left; border: none; padding-left:5px;"><INPUT TYPE="submit" class="submit" name="SUBMITFILTER" value= "Submit Filter">&nbsp;
                                                                                <INPUT TYPE="submit" class="submit" name="CLEARFILTER"  value= "Clear Filter"></td>
                          <td class="head1" colspan="1" style="text-align:left; border: none; padding: 0px 5px 0px 5px;"><input type="radio" id="ACTIVE"  name="STATUS" value="A" CHECKED ><label class="label" for="ACTIVE">Active</label></td>
                          <td class="head1" colspan="1" style="text-align:left; border: none; padding: 0px 5px 0px 5px;"><input type="radio" id="DELETED" name="STATUS" value="D"><label class="label" for="DELETED">Deleted</label></td>
                          <td colspan="1" style="border: none;">&nbsp</td>
                      </tr>
                      <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                           <td colspan="5" style="text-align:center;">&nbsp;</td>
                      </tr>    

                      <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                          <td colspan="1" >&nbsp</td>
                          <td  class="det1" colspan="1" style="text-align:left; padding: 0px 5px 0px 5px;">Transporter</td>
                          <td  class="det1" colspan="1" style="text-align:center; padding: 0px 5px 0px 5px;">Select</td>
                          <td  class="det1" colspan="1" style="text-align:right; padding: 0px 5px 0px 5px;">Status</td>
                          <td colspan="1">&nbsp</td>
                      </tr>
                      <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                           <td colspan="5" >&nbsp;</td>
                      </tr>    
                      <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td colspan="1">&nbsp</td>
                          <td class="det1" colspan="1" style="text-align:left; padding: 0px 5px 0px 5px;" ;><INPUT TYPE="TEXT" size="20" name="TRUID"    value= "" ></td>
                          <td colspan="3">&nbsp</td>
                      </tr>
                      <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                           <td colspan="5" style="text-align:center;">&nbsp;</td>
                      </tr>  
                      <?php
                      if(count($mfDD) == 0) { ?>
                          <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                               <td  colspan="1">&nbsp</td>
                               <td  class="det3" colspan= "3" style="text-align:left; color:Red;">No Transporter selected - Use filters</td>
                               <td  colspan="1">&nbsp</td>
                          </tr> 
                          <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                               <td  colspan="5" style="text-align:center;">&nbsp;</td>
                          </tr>
                      <?php        
                      } else {         
                     
                          foreach ($mfDD as $row) { 
                              if($row['status'] == "D") {
                                 $ts = 'Deleted';
                              } else {
                                 $ts = 'Active';
                              } ?>
                              <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                                   <td  colspan="1">&nbsp</td>
                                   <td class="detN12" style="text-align:left;"><?php echo $row['name'];?></td>
                                   <td class="detN12" style="text-align:center;"><INPUT TYPE="radio" name="SELMOD" onclick="javascript: submit()" value= "<?php echo $row['uid'] . "-" . $row['name'];?>"></td>
                                   <td class="detN12" style="text-align:right;"><?php echo $ts; ?></td>
                                   <td  colspan="1">&nbsp<input type="hidden" id="CURSTAT" name="CURSTAT" value="<?php echo $row['status'];?>"></td>
                              </tr>
                              <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                                   <td  colspan="5" style="text-align:center;">&nbsp;</td>
                              </tr>
                          <?php  
                          }
                      } ?>




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
    
    if (!isset($_POST['MODTRANSP']) && !isset($_POST['ADDTRANSP']) && !isset($_POST["SUBMITFILTER"]) && !isset($_POST['SELMOD'])) { 
         // Check Warehouse User
         $TripsheetDAO = new TripsheetDAO($dbConn);
         $uTS = $TripsheetDAO->checkWarehouseUser($userUId); 
         
         if($uTS[0]['category'] == 'D') {	?>

         <?php  	
         } else { ?>
             <script type='text/javascript'>parent.showMsgBoxError('You are not a warehouse user <br><br> Cannot Continue!! ')</script>
             <?php	
             return;
         }    
   
   
   
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
*/

}  
?>