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
       unset($firstform); 
}

if (isset($_POST['getReport'])) {
	
	    if (count($_POST['select']) <> 0 ) {
	    	
	       // Get User Principals
	       
         $TripsheetDAO = new TripsheetDAO($dbConn);     
         $upl = $TripsheetDAO->getUserPrincipals($userUId, $_POST['WHN']);
         
         $plist = Array();
         
         foreach($upl as $prow) {
             array_push($plist,$prow['prin_uid']);
         }
         
         $prinList  = implode(",",$plist);
         
         $transList = implode(",",$_POST['select']);
         
         $ReportsDAO = new ReportsDAO($dbConn);     
         $tRep = $ReportsDAO->getTransporterReport($prinList, $_POST['WHN'], $transList, $_POST['FROMDATE'], $_POST['TODATE'] );
         
         $fstrow = 'T';
         
         $fileName = "Transport Report.csv";
         ob_clean();
         $csv_export = '';
         
         foreach($tRep as $head=>$repRow) {
             if($fstrow == 'T') {
                $csv_export.= implode(",",array_keys($repRow)) . "\n";
                $fstrow = 'F';
             }
            $csv_export.= implode(",",$repRow) . "\n"; 
         }
         $csv_export.= "***  End of Report  ****" . "\n"; 
         
         header("Content-Description: File Transfer");
         header("Content-Disposition: attachment; filename=\"".$fileName."\"");
         header("Content-Type: application/force-download");
         echo $csv_export;
         ob_clean();
         return;
         
	    } else { ?>
         <script type='text/javascript'>parent.showMsgBoxError('No Transporter / Agent selected <br><br> Try Again')</script>
         <?php	
         unset($_POST['firstform']);
	    	
	    	
	    	
	    }
	

}



if(isset($_POST['firstform'])) { 
      if($_POST['WAREHOUSE'] <> 'Select a Warehouse') { 
	
          $postFROMDATE = (isset($_POST["FROMDATE"])) ? htmlspecialchars($postFROMDATE=$_POST["FROMDATE"]) : CommonUtils::getUserDate();     
           	
          $postTODATE   = (isset($_POST["TODATE"])) ? htmlspecialchars($postTODATE=$_POST["TODATE"]) : CommonUtils::getUserDate();	 
           
          $TripsheetDAO = new TripsheetDAO($dbConn);     
          $tsl = $TripsheetDAO->getTripSheetTransporter2($_POST['WAREHOUSE']) ;
    	 
	  	    ?>
	        <center>
             <form name='Transporter Report' method=post action='' onload='setFocusTPselect()'>
                <table width="720"; style="border:none">
                  <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td style="width:10%; border:none;">&nbsp</td>
                     <td style="width:30%; border:none;">&nbsp</td>
                     <td style="width:20%; border:none;">&nbsp</td>
                     <td style="width:30%; border:none;">&nbsp</td>
                     <td style="width:10%; border:none;">&nbsp</td>
                   </tr>
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td class=head1 colspan="5"; style="text-align:center";>Select Requied Transporter     -     <?php echo trim(substr($_POST['WAREHOUSE'],strpos($_POST['WAREHOUSE']+1,'-'),20)); ?></td>
                   </tr>
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td Colspan="5">&nbsp$_POST['FROMDATE']</td>
                   </tr>  
    
     
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                         <td >&nbsp</td>
                         <td class="head1" colspan="2"; style="text-align:center; padding-left:20px;">Trip&nbsp;Sheet&nbsp;Start&nbsp;Date</td>
                         <td colspan="1"; style="text-align:left";><?php DatePickerElement::getDatePickerLibs(); DatePickerElement::getDatePicker("FROMDATE",$postFROMDATE); ?> </td>
                         <td>&nbsp</td>
                   </tr>
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td Colspan="5">&nbsp</td>
                   </tr>  
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                         <td >&nbsp</td>
                         <td class="head1" colspan="2"; style="text-align:center; padding-left:20px;">Trip&nbsp;Sheet&nbsp;End&nbsp;Date</td>
                         <td colspan="1"; style="text-align:left";><?php DatePickerElement::getDatePickerLibs(); DatePickerElement::getDatePicker("TODATE",$postTODATE); ?></td>
                         <td>&nbsp</td>
                   </tr>
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td Colspan="5">&nbsp</td>
                   </tr> 
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td colspan="1">&nbsp</td>
                          <td class="detB12" colspan="2" style="text-align:left;">Transporter&nbsp;/&nbsp;Agent</td>
                          <td class="detB12" colspan="1" style="text-align:center;">Select<br><a href="javascript:;" onClick="selectAll('select[]', 1);">&nbsp;&nbsp;&nbsp;All</a>&nbsp;|&nbsp;<a href="javascript:;" onClick="selectAll('select[]', 0);">None</a></td>
                          <td colspan="1">&nbsp</td>
                   </tr>            
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                         <td Colspan="5">&nbsp</td>
                   </tr> 
                   <?php 
                   foreach($tsl as $drow) { ?>
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            <td >&nbsp</td>
                            <td class="det2" colspan="2" style="text-align:left;"><?php echo trim($drow['name']); ?></td>
                            <td class="det2" style="text-align:center;"><INPUT TYPE="checkbox" name="select[]" value= "<?php echo trim($drow['uid']) ; ?>"></td>
                            <td >&nbsp</td>
                         </tr>               
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            <td Colspan="5">&nbsp</td>
                         </tr>
                   <?php
                   } ?>
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="getReport" value= "Get Report">
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
      } else { ?>
         <script type='text/javascript'>parent.showMsgBoxError('Warehouse Not Selected <br><br> Try Again')</script>
         <?php	
         unset($_POST['firstform']);
      }
}
if(!isset($_POST['firstform'])) {
	
    $TripsheetDAO = new TripsheetDAO($dbConn);     
    $depl = $TripsheetDAO->getUserWarehouses($userUId) ;
    
    $class = 'odd';    
    
    ?>
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
                     <td  class=head2 >Warehouse </td>
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
                 <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="firstform" value= "Get Warehouse List">
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
</script>

<?php

?>



