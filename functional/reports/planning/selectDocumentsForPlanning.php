<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER."DAO/transactionDAO.php");
    include_once($ROOT.$PHPFOLDER."DAO/storeDAO.php");
    include_once($ROOT.$PHPFOLDER."properties/Constants.php");
    include_once($ROOT.$PHPFOLDER.'TO/SequenceTO.php');
    include_once($ROOT.$PHPFOLDER.'DAO/SequenceDAO.php');
    include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');

    //Create new database object
    $dbConn = new dbConnect(); $dbConn->dbConnection();

    if (!isset($_SESSION)) session_start() ;
      $userUId     = $_SESSION['user_id'] ;
      $principalId = $_SESSION['principal_id'] ;
      $depotId     = $_SESSION['depot_id'] ;
      $systemId    = $_SESSION["system_id"];
      $systemName  = $_SESSION['system_name'];
      $class = 'even';
      
      
?>
<!DOCTYPE html>
<HTML>
	<HEAD>

		<TITLE>Document Selection</TITLE>

		<link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/1_kwelanga.css' rel='stylesheet' type='text/css'>
		<link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/kos_standard_screen.css' rel='stylesheet' type='text/css'>
		
		<script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/jquery.js"></script>
		<script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/dops_global_functions.js"></script>

   </HEAD>
     <BODY> <?php
     	
if (isset($_POST['canform'])) {
      return;    
}

if (isset($_POST['backform'])) {

      unset($_POST['firstform']);	
      unset($_POST['finish']);	
}	


          if (isset($_POST['finish'])) {
      	 		
                 $seqVal="000000";
                 $list = implode(",",$_POST['select']);
                 $sequenceDAO = new SequenceDAO($dbConn);
                 $sequenceTO = new SequenceTO;
                 $errorTO = new ErrorTO;
                 $sequenceTO->sequenceKey=LITERAL_SEQ_PICKLIST;
                 $sequenceTO->depotUId = $depotId;
                 $result=$sequenceDAO->getSequence($sequenceTO,$seqVal);
                 if ($result->type!=FLAG_ERRORTO_SUCCESS) { ?>
                         <script type='text/javascript'>parent.showMsgBoxInfo('Picking List Sequence not set up')</script>  
                         <?php
                         return $result;
                 }
		          
                 $picklistNumber   = $seqVal;
                 $picklistDate     = date("Y-m-d") ;
                 $list = implode(",",$_POST['select']);
                 $eaStore          = $_POST['EACHSTORE'];
             
                 include_once($ROOT.$PHPFOLDER."DAO/PostTransactionDAO.php");
                 $postTransactionDAO = new PostTransactionDAO($dbConn);
                 $rTO = $postTransactionDAO->updatePickingStatus($list, $picklistNumber, $picklistDate); 
             
                 if ($rTO->type==FLAG_ERRORTO_SUCCESS) {
                      $dbConn->dbinsQuery("commit"); ?>
        
                      <script type='text/javascript'>parent.showMsgBoxInfo('Picking List Successfully Created')
            	             window.open('<?php echo $ROOT.$PHPFOLDER ?>functional/presentations/presentationManagement.php?TYPE=BPICKLIST&FINDNUMBER=<?PHP echo $picklistNumber; ?>');
                      </script>                             
                      <?php
                      if($eaStore) { ?>
                           <script type='text/javascript'>window.open('<?php echo $ROOT.$PHPFOLDER ?>functional/presentations/view/inpick_document_version_multi.php?DOCMASTID=<?PHP echo $list; ?>');
                      </script>	
                      <?php 
                      } 
                 }  else { 
                       $dbConn->dbinsQuery("rollback"); ?>
                   
                      <script type='text/javascript'>parent.showMsgBoxInfo('Picking List Not Created')</script> 
                 <?php 
                 }

                 unset($_POST['firstform']);	
                 unset($_POST['finish']);	
          }

// Set firstform******************************************************************************************************************************************************
if (isset($_POST['firstform'])) { 
	
    if (isset($_POST['area'])) $postarea = $_POST['area']; else $postarea="";
    
     if($postarea <> "" && $postarea <> 'Select Area') {
    	
    	   include_once($ROOT.$PHPFOLDER."DAO/transactionDAO.php");
         $transactionDAO = new transactionDAO($dbConn);
         $mfTS = $transactionDAO->getDocumentsForPicking($principalId, $depotId, $postarea);     
      
         if (sizeof($mfTS)<> 0) { ?>
         	
              <center>          
                  <FORM name='reprintts' method=post target=''>
                     <table width="950px"; style="border:none">
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            <td class=head1 colspan="9"; style="text-align:center";>Select Documents for Picking</td>	   
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            <td colspan="9"; style="text-align:center";>&nbsp;</td>	   
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                             <td width="5%"; style="border:none">&nbsp;</td>
                             <td class="head1" width="10%";>Area</td>
                             <td class="head1" width="10%";>Document&nbsp;No</td>
                             <td class="head1" width="35%";>Store</td>
                             <td class="head1" width="10%";>Order&nbsp;Date</td>
                             <td class="head1" width="10%";>Delivery&nbsp;Date</td>
                             <td class="head1" width="10%";>Quantity</td>
                             <td class="head1" width="5%";>Select<br><a href="javascript:;" onClick="selectAll('select[]', 1);">All</a>&nbsp;|&nbsp;<a href="javascript:;" onClick="selectAll('select[]', 0);">None</a></td>
                             <td width="5%"; style="border:none">&nbsp;</td>	
                        </tr>
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                              <td class=head1 colspan="9"; style="text-align:center";>&nbsp;</td>	   
                         </tr>
                        <?php
       
                        foreach ($mfTS as $row) {
                                $cl = GUICommonUtils::styleEO($cl);
                        ?>
                                <tr class="<?php echo $cl; ?>">
                                	 <td style="border:none">&nbsp;</td>
                                   <td class="det2" style="text-align:left;" ><?php echo $row['wa_name'];?></td>
                                   <td class="det2" style="text-align:left;" ><?php echo $row['document_number'];?></td>
                                   <td class="det2" style="text-align:left;" ><?php echo $row['deliver_name'];?></td>
                                   <td class="det2" style="text-align:left;" ><?php echo $row['order_date'];?></td>
                                   <td class="det2" style="text-align:left;" ><?php echo $row['due_delivery_date'];?></td>
                                   <td class="det2" style="text-align:right;" ><?php echo $row['cases'];?></td>
                                   <td class="det2" style="text-align:right;" ><INPUT TYPE="checkbox" name="select[]" value= "<?php echo $row['docuid'];?>"></td>
                                   <td style="border:none">&nbsp;</td>
                                </tr>
                                <?php $cl = GUICommonUtils::styleEO($cl); ?>
                                <tr class="<?php echo $cl; ?>">
                                	 <td style="border:none">&nbsp;</td>
                                   <td class="det2" style="text-align:left;">&nbsp;</td>
                                   <td class="det2" style="text-align:left;">&nbsp;</td>
                                   <td class="det2" style="text-align:left;">&nbsp;</td>
                                   <td class="det2" style="text-align:left;">&nbsp;</td>
                                   <td class="det2" style="text-align:left;">&nbsp;</td>
                                   <td class="det2" style="text-align:right;">&nbsp;</td>
                                   <td class="det2" style="text-align:right;">&nbsp;</TD>
                                   <td style="border:none">&nbsp;</td>
                                </tr>
 
                        <?php 	} ?> 
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                              <td class=head1 colspan="9"; style="text-align:center";>&nbsp;</td>	   
                         </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        	  <td colspan="1" style="border:none">&nbsp;</td>
                        	  <td class="det1" colspan="3" style="text-align:center";>Print Bulk Picking List&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE="checkbox" name="BULKLIST" value= "1" CHECKED></td>
                        	  <td colspan="1" style="border:none">&nbsp;</td>
                            <td class="det1" colspan="3" style="text-align:center";>Print Each Store Document&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE="checkbox" name="EACHSTORE" value= "1"></td>
                            <td style="border:none">&nbsp;</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            <td colspan="9"; style="text-align:center";>&nbsp;</td>	   
                        </tr>
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            <td colspan="9"; style="text-align:center";>&nbsp;</td>	   
                        </tr>                       
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                               <td colspan="9" style="text-align:center";><INPUT TYPE="submit" class="submit" name="finish" value= "Print Picking List">
                               	                                          <INPUT TYPE="submit" class="submit" name="backform" value= "Back">
                               	                                          <INPUT TYPE="submit" class="submit" name="canform" value= "Cancel"></td> 
                               	                                          
                         </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            <td colspan="9"; style="text-align:center";>&nbsp;</td>	   
                        </tr>
                     </table>
	            </center>
         <?php } else { ?>
              <script type='text/javascript' >parent.showMsgBoxError("No Documents for this Region<BR><BR>")</script> 	
         <?php 
               unset ($_POST['firstform']);
         
         }   	
     } else { ?>	
              <script type='text/javascript' >parent.showMsgBoxError("No Area selected<BR><BR>")</script> 	
         <?php 
               unset ($_POST['firstform']);
     } 
}
// EO Set firstform******************************************************************************************************************************************************
     	
// firstform******************************************************************************************************************************************************
if (!isset($_POST['firstform'])) {      
    include_once($ROOT.$PHPFOLDER."DAO/DepotDAO.php");
    $DepotDAO = new DepotDAO($dbConn);
    $mfTS = $DepotDAO->getWarehouseAreas($userUId, $depotId); ?>

    <center>
        <FORM name='Select Documents for Picking' method=post action=''>
             <table width="720"; style="border:none">
                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                    <td class=head1 colspan="6"; style="text-align:center";>Select Documents for Picking - Pick by Product</td>
                 </tr>
                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                    <td width="10%"; style="border:none">&nbsp;</td>
                    <td width="20%"; style="border:none">&nbsp;</td>
                    <td width="30%"; style="border:none">&nbsp;</td>
                    <td width="10%"; style="border:none">&nbsp;</td>
                    <td width="20%"; style="border:none">&nbsp;</td>
                    <td width="10%"; style="border:none">&nbsp;</td>
                 </tr>
                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td>&nbsp</td>
                     <td class=det1 colspan="4"; style="text-align:center";>Select Area
                          <select name="area" id="area">
                              <option value="Select Area">Select Area</option>
                                   <?php foreach($mfTS as $row) { ?>
                                         <option value="<?php echo $row['waUid']; ?>"><?php echo $row['wh_area']; ?></option>
                                   <?php } ?>
                          </select>
                    </td>
                    <td class=det1 colspan="1";></td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                    <td colspan="6">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                   <td class=det2 colspan="1"></td>
                   <td class=det2 colspan="4" style="text-align:center";><INPUT TYPE="submit" class="submit" name="firstform" value= "Documents for Picking">
                                                                         <INPUT TYPE="submit" class="submit" name="canform"   value= "Cancel"></td>
                   <td>&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                    <td colspan="6">&nbsp</td>
               </tr>
        </table>
      </form>
    </center>
    <?php
}    
// EO firstform******************************************************************************************************************************************************

    ?> 
     	
     </BODY>
 </HTML>

 <script type="text/javascript" defer>
     function selectAll(elementName, flag){
          $("input[name='"+elementName+"']").each( function(){$(this).attr("checked",((flag == 1)?true:false));})
     }
</script>


