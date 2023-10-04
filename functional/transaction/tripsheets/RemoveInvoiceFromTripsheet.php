<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER."properties/Constants.php");
		include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
    include_once($ROOT.$PHPFOLDER."DAO/tripsheetDAO.php");
    include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
?>
<!DOCTYPE html>
<HTML>
	<HEAD>

		<TITLE>Document Selection</TITLE>

    <link href='<?php echo $ROOT.$PHPFOLDER ?>css/1_kwelanga.css' rel='stylesheet' type='text/css'>
    <link href='<?php echo $ROOT.$PHPFOLDER ?>css/kos_standard_screen2.css' rel='stylesheet' type='text/css'>
    <script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/jquery.js"></script>
		<script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/dops_global_functions.js"></script>

    <style>
      
   	</style>

		</HEAD>
<body>
<?php

    if (!isset($_SESSION)) session_start() ;
       $userUId     = $_SESSION['user_id'] ;
       $principalId = $_SESSION['principal_id'] ;
       $depotId     = $_SESSION['depot_id'] ;
       $systemId    = $_SESSION["system_id"];
       $systemName  = $_SESSION['system_name'];
      
       if (isset($_POST["TSNUMBER"])) $postTSNUMBER=test_input($_POST["TSNUMBER"]); else $postTSNUMBER = ''; 
       if (isset($_POST['reason'])) $postreason = $_POST['reason']; else $postreason="";
      
       //Create new database object
     $dbConn = new dbConnect(); 
     $dbConn->dbConnection();
     $errorTO = new ErrorTO;
     
     $TripsheetDAO = new TripsheetDAO($dbConn);
     $uTS = $TripsheetDAO->checkWarehouseUser($userUId);

     if($uTS[0]['category'] != 'D') {	?>
                <script type='text/javascript'>parent.showMsgBoxError('You are not a warehouse user <br><br> Cannot Continue!! ')</script>
               <?php
               return;
     }
     if (isset($_POST['FINISH'])) {
       	
            if ($postreason !="Select Reason") { 
       	        if (isset($_POST['DOCLIST'])) {
                     $list = implode(",",$_POST['DOCLIST']);
                     $cont = 'Y';
                     foreach ($_POST['DOCLIST'] as $list) {
                     	   $tripsheetDAO = new tripsheetDAO($dbConn);
                         $errorTO = $tripsheetDAO->removeInvoiceFromTripSheet($list,$postreason,$userUId, $_POST['TSNUM']);
                         if ($errorTO->type!=FLAG_ERRORTO_SUCCESS) {
                         	   $cont = 'N';
                         	   break;
                         } 
                     	   $tripsheetDAO = new tripsheetDAO($dbConn);
                         $errorTO = $tripsheetDAO->updateRemoveTripsheetControl($list,$_POST['TSNUM']);
                     }
                     if ($cont == 'Y') {
                           $dbConn->dbinsQuery("commit");
                           ?>
                           <script type='text/javascript'>parent.showMsgBoxInfo('Documents Successfully Removed from Tripsheet')</script> 
       	                  <?php
       	             } else { ?>
                          <script type='text/javascript'>parent.showMsgBoxInfo('Could not Remove Document- Contact Support')</script> 
       	                  <?php
       	                  return;
       	             }
                } else { ?>
                 <script type='text/javascript'>parent.showMsgBoxError('No Document Selected..<BR> Try again.')</script> 
                 <?php 
                 unset($_POST['FIRSTFORM']);
                } 
            } else { ?>
                 <script type='text/javascript'>parent.showMsgBoxError('Removal Reason Not Selected..<BR> Try again.')</script> 
                 <?php 
                 unset($_POST['FIRSTFORM']);
            }
     }
     $class = 'odd';
     
     if (isset($_POST['FIRSTFORM']) && $postTSNUMBER !== '') {
     	
     	    $tripsheetDAO = new tripsheetDAO($dbConn);
        	$mfRTS = $tripsheetDAO->getDocumentsOnTripsheet($depotId,$postTSNUMBER, 'N');
          if (sizeof($mfRTS)!==0) { ?>
             <center>          
                 <FORM name='removets' method=post target='' >
                     <table width='900px'; style="border:none">
                          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                              <td class='head1' colspan="8" style="text-align:center;">Select Documents to Remove from Trip Sheet - <?php echo $postTSNUMBER; ?>  </td>            	
                          </tr>	
                          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                              <td class='head1' colspan="8" style="text-align:center;"><input type="hidden" id="TSNUM" name="TSNUM"    value=<?php echo $postTSNUMBER; ?>></td>            	
                          </tr>	
                          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                              <td class='det1' width="5%"; style="text-align:left";>&nbsp;</td>
                              <td class='det1' width="10%"; style="text-align:left";>Principal</td>
                              <td class='det1' width="15%"; style="text-align:left";>Document No</td>
                              <td class='det1' width="45%"; style="text-align:left";>Store</td>
                              <td class='det1' width="5%"; style="text-align:right";>Quantity</td>
                              <td class='det1' width="5%"; style="text-align:right";>Total</td>
                              <td class='det1' width="12%"; style="border:none; text-align: center;">Select<br><a href="javascript:;" onClick="selectAll('DOCLIST[]', 1);">All</a>&nbsp;|&nbsp;<a href="javascript:;" onClick="selectAll('DOCLIST[]', 0);">None</a></td>
                              <td class='det1' width="3%"; style="text-align:left";>&nbsp;</td>
                          </tr>
                          <?php
                          foreach($mfRTS as $row) {  ?>
                                <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                     <td class='det1' style="text-align:left";>&nbsp;</td>
                                     <td class='det2' style="text-align:left";><?php echo $row['shortname'];?></td>
                                     <td class='det2' style="text-align:left";><?php echo $row['Docno'];?></td>
                                     <td class='det2' style="text-align:left";><?php echo trim($row['Store']);?></td>
                                     <td class='det2' style="text-align:right";><?php echo $row['Cases'];?></td>
                                     <td class='det2' style="text-align:right";><?php echo round($row['total'],2);?></td>
                                     <td class='det2' style="text-align:center";><INPUT TYPE="checkbox" name="DOCLIST[]" value= "<?php echo $row['dm_uid'];?>"></td>
                                     <td class='det1' style="text-align:left";>&nbsp;</td>
                                </tr>
                                <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                     <td class='det1' style="text-align:left";>&nbsp;</td>
                                     <td class='det1' style="text-align:left";>&nbsp;</td>
                                     <td class='det1' style="text-align:left";>&nbsp;</td>
                                     <td class='det1' style="text-align:left";>&nbsp;</td>
                                     <td class='det1' style="text-align:left";>&nbsp;</td>
                                     <td class='det1' style="text-align:left";>&nbsp;</td>
                                     <td class='det1' style="text-align:left";>&nbsp;</td>
                                     <td class='det1' style="text-align:left";>&nbsp;</td>
                                </tr>
                          <?php 	
                          } 
                          $tripsheetDAO = new tripsheetDAO($dbConn);
                          $mfRC = $tripsheetDAO->gettripSheetReason($depotId); ?> 
                          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                <td class='det1' colspan="8" style="text-align:center;">&nbsp;</td>            	
                          </tr>	 
                          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                               <td class='det1' colspan="8" style="text-align:center;">Select Reason for Removal</td>
                          </tr>
                          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                <td class='det1' colspan="8" style="text-align:center;">&nbsp;</td>            	
                          </tr>	 
                          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                <td class='det1' colspan="3" style="text-align:center;">&nbsp;</td>
                                <td class='det1' colspan="3" style="text-align:center;">                              	
                                     <select name="reason" id="reason">
                                        <option value="Select Reason">Select Reason</option>
                                        <?php
                                        foreach($mfRC as $row) { ?>
                                            <option value="<?php echo $row['uid']; ?>"><?php echo $row['description']; ?></option>
                                        <?php 
                                        } ?>
                                     </select>
                                <td class='det1' colspan="2" style="text-align:center;">&nbsp;</td>
                          </tr>	 
                          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                <td class='det1' colspan="8" style="text-align:center;">&nbsp;</td>            	
                          </tr>    
                          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                <td class='det1' colspan="8" style="text-align:center;"><INPUT TYPE="submit" class="submit" name="FINISH" value= "Remove document from Tripsheet">
                                	                                                      <INPUT TYPE="submit" class="submit" name="BACKBUTTON" value= "Back"></td>
                          </tr>
                          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                <td class='det1' colspan="8" style="text-align:center;">&nbsp;</td>            	
                          </tr> 
                          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                <td class='det1' colspan="8" style="text-align:center;">&nbsp;</td>            	
                          </tr> 
                     </table>
                 </form>
	           </center>
             <?php
          } else { ?>
                 <script type='text/javascript'>parent.showMsgBoxError('No Documents seleced to Remove..<BR> or Tripsheet already Dispatched..<BR>')</script> 
                 <?php 
                 unset($_POST['FIRSTFORM']);
          }       
     } else { ?>
            <script type='text/javascript'>parent.showMsgBoxError('Tripsheet not found..<BR Try again..'</script> 
            <?php 
            unset($_POST['FIRSTFORM']);
     }	              
     if (!isset($_POST['FIRSTFORM'])) { ?>
         <center>
             <FORM name='Select Invoice' method=post action=''>
                <table width='750px'; style="border:none">
                    <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        <td Colspan="5">&nbsp</td>
                    </tr>  
                	  <tr>
                        <td class='head1' Colspan="5" style="text-align:center"; >Remove document from Tripsheet</td>
                    </tr>
                    <tr>
                        <td width='10%';>&nbsp</td>
                        <td width='30%';>&nbsp</td>
                        <td width='40%';>&nbsp</td>
                        <td width='10%';>&nbsp</td>
                        <td width='10%';>&nbsp</td>
                    </tr>	        	      	
                    <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        <td Colspan="5">&nbsp</td>
                    </tr>
                    <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        <td>&nbsp</td>
                        <td class='det1' style="text-align:left";>Enter Tripsheet Number</td>
                        <td colspan="2"; style="text-align:left"><input type="text" name="TSNUMBER"><br></td>
                        <td>&nbsp</td>
                    </tr>
                    <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        <td Colspan="5">&nbsp</td>
                    </tr>
                    <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="FIRSTFORM" value= "Get Tripsheet Details">
                        	                                          <INPUT TYPE="submit" class="submit" name="CANFORM" value= "Cancel"></td>
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
 }
?>

 <script type="text/javascript" defer>
     function selectAll(elementName, flag){
          $("input[name='"+elementName+"']").each( function(){$(this).attr("checked",((flag == 1)?true:false));})
     }
</script>  