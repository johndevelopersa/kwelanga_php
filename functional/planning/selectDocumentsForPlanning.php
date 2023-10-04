<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER."DAO/LoadPlanningDAO.php");
    include_once($ROOT.$PHPFOLDER."DAO/TripSheetDAO.php");    
    include_once($ROOT.$PHPFOLDER."DAO/storeDAO.php");
    include_once($ROOT.$PHPFOLDER."properties/Constants.php");
    include_once($ROOT.$PHPFOLDER.'TO/SequenceTO.php');
    include_once($ROOT.$PHPFOLDER.'DAO/SequenceDAO.php');
    include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
    include_once('managePlanningClass.php');

    //Create new database object
    $dbConn = new dbConnect(); 
    $dbConn->dbConnection();
    
    $errorTO = new ErrorTO;

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


if (isset($_POST['CANFORM'])) {
      return;    
}

if (isset($_POST['BACKFORM'])) {

      unset($_POST['GETDOCUMENTS']);	
      unset($_POST['FINISH']);	
}	

         if (isset($_POST['MANSELECTED'])) {
         	
             if(count($_POST['AMLIST']) > 0){
                   $managePlanningClass = new managePlanningClass();
                   $a = $managePlanningClass->AmendLoadingDoc($_POST['AMLIST'], $_POST['MAINLIST'], $_POST['LOADNUMBER']);
         	   } else { ?> 
                    <script type='text/javascript'>parent.showMsgBoxError('No Documents Selected to Amend<br><br> Try Again')</script>
         	   <?php
                  $managePlanningClass = new managePlanningClass();
                  $a = $managePlanningClass->MangeSelected($_POST['MAINLIST'], $_POST['LOADNUMBER'], '' );
 	           }
         }
         if (isset($_POST['DREMOVE'])) {
                  $managePlanningClass = new managePlanningClass();
                  $a = $managePlanningClass->MangeSelected($_POST['MAINLIST'], $_POST['LOADNUMBER'], ''); 
         }
         if (isset($_POST['ACCLOAD'])) {
                   $managePlanningClass = new managePlanningClass();
                   $a = $managePlanningClass->SelectTranporter($depotId, $_POST['MAINLIST'], $_POST['LOADNUMBER'], '');         	
         }
         if (isset($_POST['FINISH'])) {
         	
         	           if ($_POST['TRANSPORTER'] <> 'Select Transporter' ) {
         	           	
                           $tlist             = implode(",",$_POST['TOMAINLIST']);
	                         $transporterID    = $_POST['TRANSPORTER'];		            
	                         $tripSheetNumber  = $_POST['LOADNUMBER'];
                           $tripSheetDate    = date("Y-m-d H:i:s") ;
	                         $tripSheetUser    = $userUId;

// *** Deprecated	                                 
                           include_once($ROOT.$PHPFOLDER."DAO/PostTransactionDAO.php");
                           $LoadPlanningDAO = new LoadPlanningDAO($dbConn);
                           $rTO = $LoadPlanningDAO->setLoadSheetDetails($_POST['TOMAINLIST'], $transporterID, $tripSheetNumber, $tripSheetDate, $tripSheetUser,explode(',',$_POST['TOMAINLIST']));
// *** Deprecated	

                           $TripsheetDAO = new TripsheetDAO($dbConn);
                           $errorTO = $TripsheetDAO->setTripsheetHeaderNew($depotId, $tripSheetNumber, $transporterID, $tripSheetDate, $tripSheetUser);
//                  echo "ww<br>";
 //                 print_r($_POST['TOMAINLIST']);
                  $dUpdateArr = explode(',',$_POST['TOMAINLIST']);             
                  
                           if($errorTO->type==FLAG_ERRORTO_SUCCESS) {
                                  $tsHdrUid = $errorTO->identifier; 	
                           } else {
                     	            echo "Bomb Out - Tripsheet Create Error Contact Support (TS001)";
                     	            return;
                           }
                           
                           foreach($dUpdateArr as $trow) {
                           	                           	
                                 $TripsheetDAO = new TripsheetDAO($dbConn);
                                 $errorTO = $TripsheetDAO->setTripsheetDetailNew($tsHdrUid, $trow);
                                 
                                 if($errorTO->type!=FLAG_ERRORTO_SUCCESS) {
                                 	      print_r($errorTO);
                     	                  echo "Bomb Out - Tripsheet Create Error Contact Support (TS001)";
                     	                  return;
                                  }
                           } 
                    
                            if ($errorTO->type==FLAG_ERRORTO_SUCCESS) {
                                 $dbConn->dbinsQuery("commit");
                                 $loadSheetArray = array('392','393','396','397'); 
                                 
                                 if(isset($_POST['LOADSHEET'])) { ?>
                                      <script type='text/javascript'>
                                           window.open('<?php echo $ROOT.$PHPFOLDER ?>functional/presentations/view/load_sheet_document_version_multi_version2.php?TRIPNO=<?PHP echo $tripSheetNumber; ?>');    
                    
                                      </script> 
                                      <?php
                                 }
                                 if(isset($_POST['EACHSTORE'])) { ?>
                                      <script type='text/javascript'>parent.showMsgBoxInfo('Load Sheet Successfully Created')
                                           window.open('<?php echo $ROOT.$PHPFOLDER ?>functional/presentations/view/inpick_document_version_multi.php?DOCMASTID=<?PHP echo $_POST['TOMAINLIST']; ?>');
                    
                                      </script> 
                                      <?php
                                 }                                      
                            }  else { 
                                 $dbConn->dbinsQuery("rollback"); ?>
                                 <script type='text/javascript'>parent.showMsgBoxError('Trip Sheet Not Created')</script>    
                            <?php 
                            }
                     } else { ?>
                            <script type='text/javascript'>parent.showMsgBoxError('No Transporter selected')</script>  
                            <?php
                                          	
                     }
                     
                     unset($_POST['GETDOCUMENTS']);
                     unset($_POST['MANSELECTED']);
                     unset($_POST['NEXTDOC']);
                     unset($_POST['ACCLOAD']);
                     unset($_POST['FINISH']);
                     unset($_POST['LOADSHEET']);
                     unset($_POST['EACHSTORE']);
                     unset($_POST['TOMAINLIST']);
         	
         }	
         if (isset($_POST['NEXTDOC'])) {
         	   
         	    // Validate the update
         	    
         	    $uValid = "Y";
         	    
         	    for ($x = 0; $x < count($_POST['DDROW']); $x++) {
                   if(!isset($_POST['UACC'])) {  ?>  
                          <script type='text/javascript'>parent.showMsgBoxError('You have not accepted the quantities<br><br> Please Try Again')</script>
         	                <?php
         	                $uValid = "N";
         	                break;
                   }
                   if($_POST['AQTY'][$x] > $_POST['DDORD'][$x]) {  ?>  
                          <script type='text/javascript'>parent.showMsgBoxError('You cannot change to greater than the Original Order<br><br> Please Try Again')</script>
         	                <?php
         	                $uValid = "N";
         	                break;
                   }
         	    }
         	    
         	    if($uValid == 'Y') {
                     for ($x = 0; $x < count($_POST['DDROW']); $x++) {
                          $LoadPlanningDAO = new LoadPlanningDAO($dbConn);
                          $errorTO = $LoadPlanningDAO->saveDocumentAmendments($_POST['DDROW'][$x], $_POST['AQTY'][$x], $userUId, $_POST['LOADNUMBER']);
                          if($errorTO->type <> 'S') { ?> 
                              <script type='text/javascript'>parent.showMsgBoxError('Bomb Out <br><br> Comtact Support ')</script>
         	                    <?php
         	                    print_r($errorTO);
         	                    return;
                          }
                    }  
              }
              
              $managePlanningClass = new managePlanningClass();
              $a = $managePlanningClass->MangeSelected($_POST['TOMAINLIST'], $_POST['LOADNUMBER'],'');
         } 
         	
         if (isset($_POST['GETDOCUMENTS'])) {
          	
          	     If(count($_POST['select']) == 0) { ?>
                    <script type='text/javascript' >parent.showMsgBoxError("No Documents Selected for Planning - Try again<BR><BR>")</script> 
          	        <?php
                      unset($_POST['GETDOCUMENTS']);	
          	     } else {
                             	   
                      $seqVal="000000";
                      $tlist = implode(",",$_POST['TOMAINLIST']);
                      $sequenceDAO = new SequenceDAO($dbConn);
                      $sequenceTO = new SequenceTO;
                      $errorTO = new ErrorTO;
                      $sequenceTO->sequenceKey=LITERAL_SEQ_TRIPSHEET;
                      $sequenceTO->depotUId = $depotId;	
                      $sequenceTO->depotUId = $depotId;
                      $result=$sequenceDAO->getSequence($sequenceTO,$seqVal);
                      if ($result->type!=FLAG_ERRORTO_SUCCESS) { ?>
                            <script type='text/javascript'>parent.showMsgBoxInfo('Trip Sheet Sequence not set up')</script>  
                          <?php
                          return $result;
                      }
                      $loadSheetNumber = $seqVal;
      	     	
                      $list = implode(",",$_POST['select']);
                      
                      $removalList = array();
          	     	   
                      $managePlanningClass = new managePlanningClass();
                      $a = $managePlanningClass->MangeSelected($list, $loadSheetNumber, '');
                 }
         }
         if(isset($_POST["CLEARFILTER"])) {
             unset($_POST["SUBMITFILTER"]);
             unset($storeList);
         }
         if(isset($_POST["SUBMITFILTER"])) {
         	
         	    print_r($_POST['select']);
         	    if(count($_POST['select']) == 0) {
         	    	  $list = 0;
         	    } else {
         	    	  $list = implode(",",$_POST['select']);
         	    }
	
              if (isset($_POST["PRUID"]))    $pRuid        = test_input($_POST["PRUID"]);    else $pRuid         = '0'; 
              if (isset($_POST['GRAREA']))   $grArea       = test_input($_POST['GRAREA']);   else $grArea        = '0';
              if (isset($_POST['WAREA']))    $wArea        = test_input($_POST['WAREA']);    else $wArea         = '0';
              if (isset($_POST["WDOCNO"]))   $postWDOCNO   = test_input($_POST["WDOCNO"]);   else $postWDOCNO    = '0';
              if (isset($_POST["WINVDATE"])) $postWINVDATE = test_input($_POST["WINVDATE"]); else $postWINVDATE  = '0';
              if (isset($_POST['WSTORE']))   $wStore       = test_input($_POST['WSTORE']);   else $wStore        = '0';
              if (isset($_POST["WNDD"]))     $postWNDD     = test_input($_POST["WNDD"]);     else $postWNDD      = '0';

              $LoadPlanningDAO = new LoadPlanningDAO($dbConn);
              $mfDD         = $LoadPlanningDAO->getDocumentsForLoading($principalId,       
                                                                       $depotId,
                                                                       $grArea,           
                                                                       $pRuid,         
                                                                       $wArea,         
                                                                       $postWDOCNO,        
                                                                       $postWINVDATE,      
                                                                       $wStore,        
                                                                       $postWNDD,
                                                                       $list);
              $cl = "odd"; 
         }
// **************************************************************************************************************************************************************************         
         if(!isset($_POST['GETDOCUMENTS']) && !isset($_POST['MANSELECTED']) && !isset($_POST['NEXTDOC']) && !isset($_POST['ACCLOAD']) && !isset($_POST['FINISH']) && !isset($_POST['DREMOVE'])) { ?>
               <center>          
                  <form name='reprintts' method=post target=''>
                      <table width="980px"; style="border:none">
                          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                               <td class=head1 colspan="12"; style="text-align:center";>Select Documents for Planning</td>	   
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
                                     $cl = GUICommonUtils::styleEO($cl);
                                     $docNo = "<a href=\"javascript:;\" onClick=\"window.open('https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/functional/presentations/presentationManagement.php?TYPE=BUYER ORIGINATED CLAIM&DSTATUS=".trim($row['StatUid'])."&CSOURCE=T&FINDNUMBER=".trim($row['docuid']) ."','myDocumentProcessing','scrollbars=yes,width=750,height=600,resizable=yes');\">".ltrim($row['document_number'],'0')."</a>";                                      
                                     if($row['check'] == '1'){
                                         $check = 'CHECKED';
                                     } else {
                                         $check = '';
                                     }
                                     ?>
                                     <tr class="<?php echo $cl; ?>">
                                         <td style="border:none">&nbsp;</td>
                                         <td class="det2" style="text-align:left;" ><?php echo $row['Principal'];?></td>
                                         <td class="det2" style="text-align:left;" ><?php echo $row['wh_area'];?></td>
                                         <td class="det2" style="text-align:left;" ><?php echo $row['wa_name'];?></td>
                                         <td class="det2" style="text-align:left;" ><?php echo $docNo;?></td>
                                         <td class="det2" colspan="3" style="text-align:left;" ><?php echo trim($row['deliver_name']);?></td>
                                         <td class="det2" style="text-align:left;" ><?php echo $row['order_date'];?></td>
                                         <td class="det2" style="text-align:left;" ><?php echo $row['short_name'];?></td>
                                         <td class="det2" style="text-align:right;" ><?php echo $row['cases'];?></td>
                                         <td class="det2" style="text-align:center;" ><INPUT TYPE="checkbox" name="select[]" value= "<?php echo $row['docuid'];?>"  <?php echo $check; ?>></td>
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
                                 <td colspan="12"; style="text-align:center";>&nbsp;</td>	   
                             </tr>
                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                 <td colspan="12"; style="text-align:center";>&nbsp;</td>	   
                             </tr>                       
                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                 <td colspan="12" style="text-align:center";><INPUT TYPE="submit" class="submit" name="GETDOCUMENTS" value= "Manage Select Documents"></input>
                                                                             <INPUT TYPE="submit" class="submit" name="BACKFORM" value= "Back"></input>
                                                                             <INPUT TYPE="submit" class="submit" name="CANFORM" value= "Cancel">
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


