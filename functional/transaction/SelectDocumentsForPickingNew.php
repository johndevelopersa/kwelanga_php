<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER."DAO/TripsheetDAO.php");
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
     <BODY> 
         <?php


if (isset($_POST['canform'])) {
      return;    
}

if (isset($_POST['backform'])) {

      unset($_POST['firstform']);	
      unset($_POST['finish']);	
}	


         if (isset($_POST['firstform'])) {
          	
          	     If(count($_POST['select']) == 0) { ?>
                    <script type='text/javascript' >parent.showMsgBoxError("No Documents Selected for Picking - Try again<BR><BR>")</script> 
          	        <?php
                      unset($_POST['firstform']);	
                      unset($_POST['finish']);	
          	     } else {
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
                             } else {
                                  echo "EA not set";
                             }
                       }  else { 
                             $dbConn->dbinsQuery("rollback"); ?>
                             <script type='text/javascript'>parent.showMsgBoxInfo('Picking List Not Created')</script> 
                       <?php 
                       }

                        unset($_POST['firstform']);
                 }
         }
         if(isset($_POST["CLEARFILTER"])) {
             unset($_POST["SUBMITFILTER"]);
             unset($storeList);
         }
         if(isset($_POST["SUBMITFILTER"])) {
	
              if (isset($_POST["PRUID"]))    $pRuid        = test_input($_POST["PRUID"]);    else $pRuid         = '0'; 
              if (isset($_POST['GRAREA']))   $grArea        = test_input($_POST['GRAREA']);  else $grArea        = '0';
              if (isset($_POST['WAREA']))    $wArea        = test_input($_POST['WAREA']);    else $wArea         = '0';
              if (isset($_POST["WDOCNO"]))   $postWDOCNO   = test_input($_POST["WDOCNO"]);   else $postWDOCNO    = '0';
              if (isset($_POST["WINVDATE"])) $postWINVDATE = test_input($_POST["WINVDATE"]); else $postWINVDATE  = '0';
              if (isset($_POST['WSTORE']))   $wStore       = test_input($_POST['WSTORE']);   else $wStore        = '0';
              if (isset($_POST["WNDD"]))     $postWNDD     = test_input($_POST["WNDD"]);     else $postWNDD      = '0';

              $TripsheetDAO = new TripsheetDAO($dbConn);
              $mfDD         = $TripsheetDAO->getDocumentsForPickingNew($principalId,       
                                                                       $depotId,
                                                                       $grArea,           
                                                                       $pRuid,         
                                                                       $wArea,         
                                                                       $postWDOCNO,        
                                                                       $postWINVDATE,      
                                                                       $wStore,        
                                                                       $postWNDD);
              $cl = "odd"; 
         }
         if(!isset($_POST['firstform'])) { ?>     
               <center>          
                  <form name='reprintts' method=post target=''>
                      <table width="980px"; style="border:none">
                          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                               <td class=head1 colspan="12"; style="text-align:center";>Select Documents for Picking</td>	   
                          </tr>
                          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
      	  	                   <td colspan="1">&nbsp</td>
                               <td class='det2' colspan="3" style="text-align:center";><INPUT TYPE="submit" class="submit" name="SUBMITFILTER" value= "Submit Filter">&nbsp;
                                                                                     <INPUT TYPE="submit" class="submit" name="CLEARFILTER"  value= "Clear Filter">
                               </td>
                               <td colspan="5">&nbsp</td>
                               <td colspan="3">&nbsp</td>
                          </tr>
                          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                               <td colspan="12"; style="text-align:center";>&nbsp;</td>	   
                          </tr>
                          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                               <td width="10%"; style="border:none">&nbsp;</td>
                               <td class="head1" width="9%"; style="text-align:left; padding:15px;">Principal</td>
                               <td class="head1" width="7%"; style="text-align:left; padding:15px;">Greater Area</td>
                               <td class="head1" width="7%"; style="text-align:left; padding:15px;">Del. Area</td>
                               <td class="head1" width="7%"; style="text-align:left; padding:15px;">Document&nbsp;No</td>
                               <td class="head1" width="20%"; style="text-align:left; padding:15px;">Store</td>
                               <td width="9%"; style="border:none">&nbsp;</td>	 
                               <td width="9%"; style="border:none">&nbsp;</td>	  
                               <td class="head1" width="9%"; style="text-align:left; padding:10px;">From&nbsp;Order&nbsp;Date</td>
                               <td class="head1" width="9%"; style="text-align:left; padding:15px;">NDD</td>                        
                               <td class="head1" width="5%"; style="text-align:left; padding:15px;">Quantity</td>
                               <td class="head1" width="9%"; style="text-align:left;"">Select<br><a href="javascript:;" onClick="selectAll('select[]', 1);">All</a>&nbsp;|&nbsp;<a href="javascript:;" onClick="selectAll('select[]', 0);">None</a></td>
                          </tr>
                          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                               <td colspan="1">&nbsp</td>
                               <td class="det1" colspan="1" style="text-align:left;"><INPUT TYPE="TEXT" size="8"  name="PRUID"    value= "" ></td>
                               <td class="det1" colspan="1" style="text-align:left;"><INPUT TYPE="TEXT" size="8"  name="GRAREA"    value= "" ></td>
                               <td class="det1" colspan="1" style="text-align:left;"><INPUT TYPE="TEXT" size="8"  name="WAREA"    value= "" ></td>
                               <td class="det1" colspan="1" style="text-align:left;"><INPUT TYPE="TEXT" size="5"  name="WDOCNO"   value= "" ></td>
                               <td class="det1" colspan="3" style="text-align:left;"><INPUT TYPE="TEXT" size="15" name="WSTORE"   value= "" ></td>
                               <td class="det1" colspan="1" style="text-align:left;"><INPUT TYPE="TEXT" size="10" name="WINVDATE" value= "" placeholder="YYYY-MM-DD"  ></td>
                               <td class="det1" colspan="1" style="text-align:left;"><INPUT TYPE="TEXT" size="2"  name="WNDD"     value= "" ></td>
                               <td colspan="2"; style="text-align:center";>&nbsp;</td>	
                          </tr>
                          <?php
                             if(count($mfDD) == 0) { ?>
                                  <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                                     <td  colspan="1">&nbsp</td>
                                     <td  class="det3" colspan= "9" style="text-align:left; color:Red;">No Orders selected - Use submit filter</td>
                                     <td  colspan="2">&nbsp</td>
                                 </tr> 
                                 <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                                     <td  colspan="12" style="text-align:center;">&nbsp;</td>
                                 </tr>
                             <?php        
                             } 
                             else {         
                                 foreach ($mfDD as $row) {
                                     $cl = GUICommonUtils::styleEO($cl); ?>
                                     <tr class="<?php echo $cl; ?>">
                                         <td style="border:none">&nbsp;</td>
                                         <td class="det2" style="text-align:left;" ><?php echo $row['Principal'];?></td>
                                         <td class="det2" style="text-align:left;" ><?php echo $row['wh_area'];?></td>
                                         <td class="det2" style="text-align:left;" ><?php echo $row['wa_name'];?></td>
                                         <td class="det2" style="text-align:left;" ><?php echo substr($row['document_number'],-6);?></td>
                                         <td class="det2" colspan="3" style="text-align:left;" ><?php echo trim($row['deliver_name']);?></td>
                                         <td class="det2" style="text-align:left;" ><?php echo $row['order_date'];?></td>
                                         <td class="det2" style="text-align:left;" ><?php echo $row['short_name'];?></td>
                                         <td class="det2" style="text-align:right;" ><?php echo $row['cases'];?></td>
                                         <td class="det2" style="text-align:center;" ><INPUT TYPE="checkbox" name="select[]" value= "<?php echo $row['docuid'];?>"></td>
                                     </tr>
                                     <?php $cl = GUICommonUtils::styleEO($cl); ?>
                                     <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                                         <td  colspan="12" style="text-align:center;">&nbsp;</td>
                                     </tr>
                                 <?php 	
                                 } 
                             }  ?> 
                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                 <td class=head1 colspan="12"; style="text-align:center";>&nbsp;</td>	   
                             </tr>
                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                 <td colspan="1" style="border:none">&nbsp;</td>
                                 <td class="det1" colspan="3" style="text-align:center";>Print Bulk Picking List&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE="checkbox" name="BULKLIST" value= "1" CHECKED></td>
                                 <td colspan="1" style="border:none">&nbsp;</td>
                                 <td class="det1" colspan="6" style="text-align:center";>Print Each Store Document&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE="checkbox" name="EACHSTORE" value= "1"></td>
                                 <td style="border:none">&nbsp;</td>
                             </tr>
                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                 <td colspan="12"; style="text-align:center";>&nbsp;</td>	   
                             </tr>
                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                 <td colspan="12"; style="text-align:center";>&nbsp;</td>	   
                             </tr>                       
                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                 <td colspan="11" style="text-align:center";><INPUT TYPE="submit" class="submit" name="firstform" value= "Print Picking List"></input>
                                                                             <INPUT TYPE="submit" class="submit" name="backform" value= "Back"></input>
                                                                             <INPUT TYPE="submit" class="submit" name="canform" value= "Cancel">
                                 </td> 
                             </tr>
                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                  <td colspan="12"; style="text-align:center";>&nbsp;</td>	   
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
<?php
 function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  if($data=='') { $data=0; }
  
  return $data ; 

 }  
?>

 <script type="text/javascript" defer>
     function selectAll(elementName, flag){
          $("input[name='"+elementName+"']").each( function(){$(this).attr("checked",((flag == 1)?true:false));})
     }
</script>


