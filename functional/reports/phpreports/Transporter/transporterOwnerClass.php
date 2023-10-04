<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER.'DAO/TripsheetDAO.php');
    include_once($ROOT.$PHPFOLDER."libs/GUICommonUtils.php");
    include_once($ROOT.$PHPFOLDER."elements/datePickerElement.php"); 

   
class transportOwner {
	
      function __construct() {

         global $dbConn;
         $this->dbConn = $dbConn;
      }	
// ********************************************************************************************************************************	
  public function firstform($userUId, $principalId) {
  	
    $TripsheetDAO = new TripsheetDAO($this->dbConn);     
    $depl = $TripsheetDAO->getUserWarehouses($userUId) ;
    
    $class = 'odd';   
    
    $postFROMDATE = (isset($_POST["FROMDATE"])) ? htmlspecialchars($postFROMDATE=$_POST["FROMDATE"]) : CommonUtils::getUserDate();     
       	
    $postTODATE   = (isset($_POST["TODATE"])) ? htmlspecialchars($postTODATE=$_POST["TODATE"]) : CommonUtils::getUserDate(); 
    
    ?>
    <center>
       <FORM name='Transporter Report' method=post action='transporterOwnerReport.php' onload='setFocusWhselect()'>
            <table width="720"; style="border:none">
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                   <td Colspan="5">&nbsp</td>
               </tr>  
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                   <td class=head1 colspan="5"; style="text-align:center";>Select Required Warehouse</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                   <td width="5%";  style="border:none">&nbsp</td>
                   <td width="40%"; style="border:none">&nbsp</td>
                   <td width="40%"; style="border:none">&nbsp</td>
                   <td width="10%"; style="border:none">&nbsp</td>
                   <td width="5%";  style="border:none">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td >&nbsp</td>
                     <td class="det1" >Warehouse </td>
                     <td colspan="2"; style="text-align:left;">
                         <select name="WAREHOUSE" id="WAREHOUSE">
                             <option value="Select a Warehouse"><?php echo 'Select a Warehouse' ?></option>
                                   <?php foreach($depl as $drow) { ?>
                                           <option value="<?php echo trim($drow['warehouse_uid']) ."-". $drow['warehouse']; ?>"><?php echo $drow['warehouse']; ?></option>
                                   <?php } ?>
                          </select>
                     </td>             
                     <td>&nbsp</td>
                     
                     <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td Colspan="5">&nbsp</td>
                     
               </tr>
               </tr>  
                <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td >&nbsp</td>
                     <td class="det1" style="text-align:left;">Start&nbsp;Date </td>
                     <td colspan="2"; style="text-align:left";><?php DatePickerElement::getDatePickerLibs(); DatePickerElement::getDatePicker("FROMDATE",$postFROMDATE); ?> </td>
                     <td>&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td Colspan="5">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td >&nbsp</td>
                     <td class="det1" style="text-align:left;">End&nbsp;Date</td>
                     <td colspan="2"><?php DatePickerElement::getDatePickerLibs(); DatePickerElement::getDatePicker("TODATE",$postTODATE); ?> </td>
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

    <script type="text/javascript" defer>
        function selectAll(elementName, flag) {
            $("input[name='"+elementName+"']").each( function(){$(this).attr("checked",((flag == 1)?true:false));})
    }
    </script>

<?php
  }
// ********************************************************************************************************************************	
  public function ownerForm($whId, $fromDate, $toDate) {
  	
         $ReportsDAO = new ReportsDAO($this->dbConn);     
         $tRep = $ReportsDAO->getTransporterOwnerList($whId);
         if (count($tRep) > 0) { ?>
               <center>
                  <form name='Manage Roles' method=post action='transporterOwnerReport.php'>
                      <table width="500"; style="border:none">
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            <td class=head1 Colspan="5"; style="text-align:center;">Manage Allowed Roles</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            <td Colspan="5">&nbsp</td>
                        </tr>    	
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                           <td style="border:none">&nbsp</td>
                           <td width="25%"; style="border:none">&nbsp</td>
                           <td width="25%"; style="border:none">&nbsp</td>
                           <td width="25%"; style="border:none">&nbsp</td>
                           <td width="25%"; style="border:none">&nbsp;</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                           <td style="border:none; float: center;">&nbsp;</td>
                           <td class="det1" style="text-align:left;">Role</td>
                           <td style="border:none; float: center;">&nbsp;</td>
                           <td class="det1" style="text-align:center;">Select<br><a href="javascript:;" onClick="selectAll('TRANSP[]', 1);">All</a>&nbsp;|&nbsp;<a href="javascript:;" onClick="selectAll('TRANSP[]', 0);">None</a></td>
                           <td style="border:none; float: center;">&nbsp;</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            <td Colspan="5">&nbsp</td>
                        </tr> 
                        <?php 
                        foreach($tRep as $rr) { ?>
                            <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                                <td style="text-align:left";>&nbsp;</td>
                                <td style="text-align:left";><?php echo $rr['owner'];?></td>
                                <td style="text-align:center";>&nbsp;</td>
                                <td style="text-align:center";><INPUT TYPE="checkbox" name="TRANSP[]" value= "<?php echo $rr['owner'];?>"></td>
                                <td style="text-align:left";>&nbsp;</td>
                            </tr>
                            <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                <td>&nbsp</td>
                                <td>&nbsp</td>
                                <td>&nbsp</td>
                                <td>&nbsp</td>
                                <td>&nbsp</td>
                            </tr>
                        <?php	
                        } ?>      	               
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                                <td style="text-align:left";>&nbsp;</td>
                                <td style="text-align:left";><?php echo "Unknown Transport Owner";?></td>
                                <td style="text-align:center";>&nbsp;</td>
                                <td style="text-align:center";><INPUT TYPE="checkbox" name="TRANSP[]" value= "<?php echo 'Unknown Transport Owner';?>" <?php echo $rcheck;?>></td>
                                <td style="text-align:left";>&nbsp;</td>
                       </tr>
                            <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                <td><input type="hidden" name="FROMDAT"  value=<?php echo $fromDate; ?>>
                                	  <input type="hidden" name="TODAT"    value=<?php echo $toDate; ?>>
                                	  <input type="hidden" name="WAREH"    value=<?php echo $whId; ?>></td>
                                <td>&nbsp</td>
                                <td>&nbsp</td>
                                <td>&nbsp</td>
                                <td>&nbsp</td>
                            </tr>

                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            <td Colspan="5" style="text-align:center";><INPUT TYPE="submit" class="submit" name="ownerform" value= "Modify Roles">
                                                                       <INPUT TYPE="submit" class="submit" name="canform"     value= "Cancel"></td>
                        </tr>               
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                           <td Colspan="5">&nbsp</td>
                         </tr>
                      </table>
                  </form>
               </center>
    <?php
         } else { ?>
              <script type='text/javascript'>parent.showMsgBoxError('No Roles Available')</script> 
              <?php 
              unset($postINVOICE);
              unset($_POST['firstform']);
         }
?>
   <script type="text/javascript" defer>
       function selectAll(elementName, flag) {
           $("input[name='"+elementName+"']").each( function(){$(this).attr("checked",((flag == 1)?true:false));})
       }
  </script>

<?php
  }	 
  
}
 
// ********************************************************************************************************************************	
