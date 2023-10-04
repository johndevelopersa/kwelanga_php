<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER."properties/Constants.php");
    include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
    include_once($ROOT.$PHPFOLDER."libs/CommonUtils.php");
    include_once ($ROOT.$PHPFOLDER."DAO/MaintenanceDAO.php");	    
    include_once($ROOT.$PHPFOLDER."elements/datePickerElement.php"); 
    include_once($ROOT.$PHPFOLDER.'elements/basicSelectElement.php');
    
    if (!isset($_SESSION)) session_start() ;
      $userUId     = $_SESSION['user_id'] ;
      $principalId = $_SESSION['principal_id'] ;
      $systemId    = $_SESSION["system_id"];
      $systemName  = $_SESSION['system_name'];
      
      $postPrincipal   = (isset($_POST["Principal"])) ? htmlspecialchars($_POST["Principal"]) : ''; 
      $postFROMDATE = (isset($_POST["FROMDATE"])) ? htmlspecialchars($_POST["FROMDATE"]) :  CommonUtils::getUserDate();
      $postTODATE   = (isset($_POST["TODATE"])) ? htmlspecialchars($postTODATE=$_POST["TODATE"]) : CommonUtils::getUserDate();     

      $postDOCTYPE   = (isset($_POST["DOCTYPE"])) ? htmlspecialchars($postDOCTYPE=test_input($_POST["DOCTYPE"])) : '';     

      $postPSMNME    = (isset($_POST["PSMNME"]))    ? htmlspecialchars($postPSMNME    = test_input($_POST["PSMNME"])) : '';    
      $postDOCNO     = (isset($_POST["DOCNO"]))     ? htmlspecialchars($postDOCNO     = test_input($_POST["DOCNO"])) : ''; 
      $postINVNO     = (isset($_POST["INVNO"]))     ? htmlspecialchars($postINVNO     = test_input($_POST["INVNO"])) : ''; 
      $postCREDNO    = (isset($_POST["CREDNO"]))    ? htmlspecialchars($postCREDNO    = test_input($_POST["CREDNO"])) : ''; 
      $postEXTTYPE   = (isset($_POST["EXTTYPE"]))   ? htmlspecialchars($postEXTTYPE   = test_input($_POST["EXTTYPE"])) : ''; 
      $postCAPBY     = (isset($_POST["CAPBY"]))     ? htmlspecialchars($postCAPBY     = test_input($_POST["CAPBY"])) : ''; 
      $postWAREHOUSE = (isset($_POST["WAREHOUSE"])) ? htmlspecialchars($postWAREHOUSE = test_input($_POST["WAREHOUSE"])) : ''; 
      $postLIMITREC  = (isset($_POST["LIMITREC"]))  ? htmlspecialchars($postLIMITREC  = test_input($_POST["LIMITREC"])) : ''; 


      //Create new database object
      $dbConn = new dbConnect(); 
      $dbConn->dbConnection();
      
      if (isset($_POST['canform'])) {?>
         <script type='text/javascript'>parent.showMsgBoxError("Cancelled");</script>	 <?php	
         return;    
      }

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

if (isset($_POST['finishform'])) {
	        $updateCount = $insertCount = 0;
	
          foreach($_POST['select'] as $urow) {
          	
          	print_r($urow);
          	
          	echo "<br>";
          	
          	
          	   if(substr($urow,10,10) == '000000000') {
          	   	    $postPrinID = (isset($_POST["PRINID"])) ? htmlspecialchars($postPrinID  = test_input($_POST["PRINID"])) : ''; 
                    $MaintenanceDAO = new MaintenanceDAO($dbConn);
                    $seInsert = $MaintenanceDAO->smartEventInsert($postPrinID, ltrim(substr($urow,0,10), '0'));
                    
                    if($seInsert == 'S') {
                        $insertCount++	;
                    }
                    
               } else {               	
           	    	    $MaintenanceDAO = new MaintenanceDAO($dbConn);
                      $seupdate = $MaintenanceDAO->smartEventUpdate(ltrim(substr($urow,10,10), '0'));
                      if($seupdate == 'S') {
                           $updateCount++	;
                      }
          	   	    
          	   }          	
          } ?>
                <script type='text/javascript'>parent.showMsgBoxInfo('Re Que Successfull <br><br>Inserted - <?php echo $insertCount ?><br>Updated - <?php echo $updateCount ?>')</script> 
                <?php 
                return;
          
}
if(isset($_POST['firstform'])) {
	    // Run Smart Event Query
      if($postPrincipal <> 'Select a Principal') {
             $MaintenanceDAO = new MaintenanceDAO($dbConn);
             $sEventRecs = $MaintenanceDAO->smartEventQuery($postPrincipal, 
                                                            $postFROMDATE, 
                                                            $postTODATE, 
                                                            $postDOCTYPE, 
                                                            $postPSMNME, 
                                                            $postDOCNO, 
                                                            $postINVNO,
                                                            $postCREDNO, 
                                                            $postEXTTYPE,
                                                            $postWAREHOUSE,
                                                            $postCAPBY,
                                                            $postLIMITREC);
                                                            
            echo "<br>";
            echo substr($sEventRecs,0,5);                                              
             echo "<br>";                                                
            if (substr($sEventRecs,0,5) <> "Error" ) {
                 if (sizeof($sEventRecs) > 0) {?>
                      <center>          
                         <form name='reque' method=post target=''>
                         	 <table width="1000"; style="border:none">
                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                 <td colspan="8" style="border:none; text-align: center; font-weight: normal; font-size:20px">Select Documents to Re - Que</td>            	
                             </tr>	
                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                 <td colspan="8" style="border:none; text-align: center; font-weight: bold;">&nbsp</td>            	
                             </tr>	
                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                 <th width="18%"; style="border:none; float: center;">Principal</th>
                                 <th width="7%";  style="border:none; float: center;">Document Number</th>
                                 <th width="10%"; style="border:none; float: center;">Date</th>
                                 <th width="21%"; style="border:none; float: center;">Store</th>
                                 <th width="5%";  style="border:none; float: center;">Extract Status</th>
                                 <th width="18%"; style="border:none; float: center;">Status Message</th>
                                 <th width="14%"; style="border:none; float: center;">General Message</th>
                                 <th width="7%";  style="border:none; float: center;">Select<br>
                                 	                                   <a href="javascript:;" onClick="selectAll('select[]', 1);">All</a> |
                                                                     <a href="javascript:;" onClick="selectAll('select[]', 0);">None</a></th>
                             </tr>
                             <?php
                             $cl = "even";
                             foreach ($sEventRecs as $seRow) { ?>
                                  <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                     <td style="border:1px solid black; border-collapse: collapse; float: center;"><?php echo trim($seRow['Principal']);?></td>
                                                                                                                   <input type="hidden" name="PRINID"  value=<?php echo $seRow['PrinId']; ?>>
                                     <td style="border:1px solid black; border-collapse: collapse; float: center;"><?php echo $seRow['Document Number'];?></td>
                                     <td style="border:1px solid black; border-collapse: collapse; float: center;"><?php echo $seRow['Invoice Date'];?></td>
                                     <td style="border:1px solid black; border-collapse: collapse; float: center;"><?php echo $seRow['Store'];?></td>
                                     <td style="border:1px solid black; border-collapse: collapse; float: center;"><?php echo $seRow['Status'];?></td>
                                     <td style="border:1px solid black; border-collapse: collapse; float: center;"><?php echo $seRow['status_msg'];?></td>
                                     <td style="border:1px solid black; border-collapse: collapse; float: center;"><?php echo $seRow['New File'];?></td>
                                     <td style="border:1px solid black; border-collapse: collapse; float: center;"><INPUT TYPE="checkbox" name="select[]" value= "<?php echo str_pad($seRow['Document_Uid'],10,"0",STR_PAD_LEFT) . str_pad($seRow['DataId'],10,"0",STR_PAD_LEFT);?>"><br></td>
                                  </tr> <?php 
                      	      } ?>              
                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                 <td colspan="8" style="border:none; text-align: center; font-weight: bold;">&nbsp</td>            	
                             </tr>	
                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>"> 
                             	  <td colspan="8" style="text-align: center";><INPUT TYPE="submit" class="submit" name="finishform" value= "Re Que Selected">
                             	  	                                          <INPUT TYPE="submit" class="submit" name="Exportform" value= "Export List To csv Selected">
                             	  	                                          <INPUT TYPE="submit" class="submit" name="canform"    value= "Cancel"></td>
                             </tr>
                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                 <td colspan="8" style="border:none; text-align: center; font-weight: bold;">&nbsp</td>            	
                             </tr>	
                          </table>
                       </form>   
                      </center> 
                      <?php
                 } else {  ?>
                     <script type='text/javascript'>parent.showMsgBoxError('No Documents found Check Criteria')</script> 
                     <?php 
                     unset($_POST['firstform']);	
                 }
            } else { ?>
                <script type='text/javascript'>parent.showMsgBoxError('Validation Error - <BR><BR><?php echo $sEventRecs ?> <br>')</script> 
                <?php 
                unset($_POST['firstform']);	            	
            }     
       } else {   ?>
                <script type='text/javascript'>parent.showMsgBoxError('No Principal Selected - Try again')</script> 
                <?php 
                unset($_POST['firstform']);	
       }
}

      
// ********************************************************************************************************************************************************      
     
if(!isset($_POST['firstform'])) {
	
    $MaintenanceDAO = new MaintenanceDAO($dbConn);
    $aWP = $MaintenanceDAO->getReQuePrincipalList( $userUId, $principalId);

    $MaintenanceDAO = new MaintenanceDAO($dbConn);
    $depl = $MaintenanceDAO->getReQueWarehouseList( $userUId, $principalId);
    
    $class = 'odd';    
    
    ?>
    <center>
       <FORM name='Reset Extract Time' method=post action=''>
            <table width="720"; style="border:none">
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td class=head1 colspan="5"; style="text-align:center";>Select Parameters</td>
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
                     <td  class=head2 >Principal </td>
                     <td colspan="2"; style="text-align:left;">
                         <select name="Principal" id="Principal">
                             <option value="Select a Principal"><?php echo 'Select a Principal' ?></option>
                                   <?php foreach($aWP as $row) { ?>
                                           <option value="<?php echo trim($row['principal_id']) ; ?>"><?php echo $row['principal']; ?></option>
                                   <?php } ?>
                          </select>
                     </td> 
                    <td colspan="1"; >&nbsp;</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="5">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td >&nbsp</td>
                     <td  class=head2 >Select&nbsp;Document&nbsp;Type</td>
                     <td colspan="2"; style="text-align:left;"><?php $lableArr = array('Invoice','Credit');
                                                                        $valueArr = array('1','2');
                                                                        BasicSelectElement::buildGenericDD('DOCTYPE', $lableArr,$valueArr, $postWBSTATUS, "N", "N", null, null, null);?>
                      </td>
                     <td >&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td Colspan="5">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td >&nbsp</td>
                     <td class=head2 >Start Processed Date : </td>
                     <td colspan="2"; style="text-align:left"><?php DatePickerElement::getDatePickerLibs(); DatePickerElement::getDatePicker("FROMDATE",$postFROMDATE); ?> </td>
                     <td>&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td Colspan="5">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td >&nbsp</td>
                     <td class=head2 >End Processed Date : </td>
                     <td colspan="2"><?php DatePickerElement::getDatePickerLibs(); DatePickerElement::getDatePicker("TODATE",$postTODATE); ?> </td>
                     <td>&nbsp</td>   
               </tr>         
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td >&nbsp</td>
                     <td class=head1 >Search Criteria</td>
                     <td Colspan="3">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td >&nbsp</td>
                     <td class=head2 >Document number</td>            
                     <td colspan="2"; style="text-align:left;"><input type="text" name="DOCNO" id="DOCNO" value ="<?php echo $postDOCNO;?>"</td>                
                     <td>&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td Colspan="5">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td >&nbsp</td>
                     <td class=head2 >Invoice Number</td>            
                     <td colspan="2"; style="text-align:left;"><input type="text" name="INVNO" id="INVNO" value =""></td>                
                     <td>&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td Colspan="5">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td >&nbsp</td>
                     <td class=head2 >Credit Note Number</td>            
                     <td colspan="2"; style="text-align:left;"><input type="text" name="CREDNO" id="CREDNO" value =""></td>                
                     <td>&nbsp</td>
               </tr>
                <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td Colspan="5">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td >&nbsp</td>
                     <td class=head2 >Customer Name</td>            
                     <td colspan="2"; style="text-align:left;"><input type="text" name="PSMNME" id="PSMNME" value =""></td>                
                     <td>&nbsp</td>
               </tr>
                <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td Colspan="5">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td >&nbsp</td>
                     <td  class=head2 >Warehouse </td>
                     <td colspan="2"; style="text-align:left;">
                         <select name="WAREHOUSE" id="WAREHOUSE">
                             <option value="Select a Warehouse"><?php echo 'Select a Warehouse' ?></option>
                                   <?php foreach($depl as $drow) { ?>
                                           <option value="<?php echo trim($drow['warehouse_uid']) ; ?>"><?php echo $drow['warehouse']; ?></option>
                                   <?php } ?>
                          </select>
                     </td>             
                     <td>&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td Colspan="5">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td >&nbsp</td>
                     <td class=head2 >Captured By</td>            
                     <td colspan="2"; style="text-align:left;"><input type="text" name="CAPBY" id="CAPBY" value =""></td>                
                     <td>&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td Colspan="5">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td >&nbsp</td>
                     <td  class=head2 >Extract&nbsp;Type</td>
                     <td colspan="2"; style="text-align:left;"><?php $lableArr = array('Voqado', 'System', 'Retailer Invoice', 'GDS Notification');
                                                                        $valueArr = array('1','2','3','4');
                                                                        BasicSelectElement::buildGenericDD('EXTTYPE', $lableArr,$valueArr, $postWBSTATUS, "N", "N", null, null, null);?>
                      </td>
                     <td >&nbsp</td>
               </tr> 
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td Colspan="5">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td >&nbsp</td>
                     <td  class=head2 >Rows&nbsp;To&nbsp;View</td>
                     <td colspan="2"; style="text-align:left;"><?php $lableArr = array('20', '100', 'Unlimited');
                                                                        $valueArr = array('1','2','3');
                                                                        BasicSelectElement::buildGenericDD('LIMITREC', $lableArr,$valueArr, $postWBSTATUS, "N", "N", null, null, null);?>
                      </td>
                     <td >&nbsp</td>
               </tr> 
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td Colspan="5">&nbsp</td>
               </tr>





               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="firstform" value= "Get Documents to Extract">
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
     function selectAll(elementName, flag){
          $("input[name='"+elementName+"']").each( function(){$(this).attr("checked",((flag == 1)?true:false));})
     }
</script>