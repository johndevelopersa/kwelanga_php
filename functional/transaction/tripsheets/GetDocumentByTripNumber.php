<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER."properties/Constants.php");
		include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
    include_once($ROOT.$PHPFOLDER."DAO/tripsheetDAO.php");
    include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
    include_once($ROOT.$PHPFOLDER.'functional/transaction/tripsheets/GetDocumentByTripNumberScreens.php');
?>
<!DOCTYPE html>
<HTML>
	<HEAD>

		<TITLE>Document Selection</TITLE>

    <link href='<?php echo $ROOT.$PHPFOLDER ?>css/1_kwelanga.css' rel='stylesheet' type='text/css'>
    <link href='<?php echo $ROOT.$PHPFOLDER ?>css/kos_standard_screen2.css' rel='stylesheet' type='text/css'>
    <script type="text/javascript" language="javascript" src="<?php echo $ROOT.$PHPFOLDER ?>js/jquery.js"></script>
		<script type="text/javascript" language="javascript" src="<?php echo $ROOT.$PHPFOLDER ?>js/dops_global_functions.js"></script>

    <style>
    input[type=button] {
            background-color: transparent;
            border: none;
            color: Orange;
            margin: 4px 2px;
            cursor: pointer;
        }
	}
      
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
      
       //Create new database object
       $dbConn = new dbConnect(); 
       $dbConn->dbConnection();
       $errorTO = new ErrorTO;
       
       if(isset($_POST['CANFORM'])) {
            return; 
       }
       $TripsheetDAO = new TripsheetDAO($dbConn);
       $uTS = $TripsheetDAO->checkWarehouseUser($userUId);

       if($uTS[0]['category'] != 'D') {	?>
                <script type='text/javascript'>parent.showMsgBoxError('You are not a warehouse user <br><br> Cannot Continue!! ')</script>
               <?php
               return;
       }
       if (isset($_POST['TSPRINT'])) {
       	
       	     $loadSheet = TRUE;
//           $list = trim(substr($_POST['TSNUM'],strpos($_POST['TSNUM'],"-")+1,10)); 
             $list = $_POST['TSNUM'];

             ?>             
             <script type='text/javascript'>parent.showMsgBoxInfo('Trip Sheet Successfully Re Printed')
                  <?php
                  $honarray = array('230');
                  $cleararray = array('163','186','236','432');
                  
                  $nelarray = array('392', '393', '190', '396', '397', '400', '401', '417'); 
                  $waitDispatchArray = array('417');    
               
                  if(in_array($depotId, $honarray)) { ?>
                       window.open('<?php echo $ROOT.$PHPFOLDER ?>functional/presentations/presentationManagement.php?TYPE=CUSTOMTS305&FINDNUMBER=<?PHP echo $list; ?>');
                  <?php } 
                       elseif(in_array($depotId, $cleararray)) { ?>
                            window.open('<?php echo $ROOT.$PHPFOLDER ?>functional/presentations/presentationManagement.php?TYPE=CUSTOMTS216&FINDNUMBER=<?PHP echo $list; ?>');
                  <?php }
                       elseif(in_array($depotId, $nelarray)) { ?>
                            window.open('<?php echo $ROOT.$PHPFOLDER ?>functional/presentations/presentationManagement.php?TYPE=CUSTOMTSNEL&FINDNUMBER=<?PHP echo $list; ?>');
                  <?php } else { ?>
                          window.open('<?php echo $ROOT.$PHPFOLDER ?>functional/presentations/presentationManagement.php?TYPE=tripsheet&FINDNUMBER=<?PHP echo $list; ?>');
                  <?php } 
                   if($loadSheet && in_array($depotId, $nelarray)) { 
                   	       if($userUId == 612) {?>
                                 window.open('<?php echo $ROOT.$PHPFOLDER ?>functional/presentations/view/load_sheet_document_multi_version5.php?TRIPNO=<?PHP echo $list; ?>');    
                            <?php 
                            } else { ?>
                                 window.open('<?php echo $ROOT.$PHPFOLDER ?>functional/presentations/view/load_sheet_document_multi_version4.php?TRIPNO=<?PHP echo $list; ?>');    
                            <?php 
                          }      	
                   } 
                   if(in_array($depotId, $waitDispatchArray)) { ?>
                            window.open('<?php echo $ROOT.$PHPFOLDER ?>functional/presentations/view/pdf_tripsheet_loading_summary.php?TRIPNUMBER=<?PHP echo $list; ?>&DEPID=<?PHP echo $depotId;?>');    
                   <?php
                   }  ?>
           </script>       
<?php               
       	    
       }     
       if (isset($_POST['FINISH'])) {
       	    // Check if Tripsheet is alreay dispatched
       	    $tripsheetDAO = new tripsheetDAO($dbConn);
            $chkts = $tripsheetDAO->getTripSheetHeaderStatus($_POST['TSNUM'], $depotId );
            
            if($chkts[0]['t_dispatched'] <> 'Y') {
                  if (isset($_POST['REASON']))  $postreason  = $_POST['REASON']; else $postreason="N";
                  if ($postreason !="Select Reason") { 
                     if (isset($_POST['DOCLIST'])) {
                           $list = implode(",",$_POST['DOCLIST']);
                           $cont = 'Y';
//                           print_r($list);
                           foreach ($_POST['DOCLIST'] as $list) {
                               $tripsheetDAO = new tripsheetDAO($dbConn);
                               $errorTO = $tripsheetDAO->removeInvoiceFromTripSheet(trim(substr($list,1,10)), $postreason, $userUId, $_POST['TSNUM'], substr($list,0,1), $depotId );
                               if ($errorTO->type!=FLAG_ERRORTO_SUCCESS) {
                               	   $cont = 'N';
                               	   break;
                               } 
                           }
                           if ($cont == 'Y') {
                                 $dbConn->dbinsQuery("commit");
                                 ?>
                                 <script type='text/javascript'>parent.showMsgBoxInfo('Documents Successfully Removed from Tripsheet')</script> 
       		                      <?php
       		                 } else { ?>
                                <script type='text/javascript'>parent.showMsgBoxError('Could not Remove Document- Contact Support (TS055)'</script> 
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
            } else { ?>
                       <script type='text/javascript'>parent.showMsgBoxError('Tripsheet already Dispatched..<BR> Use Return for Re Delivery to remove Documents.')</script> 
                       <?php 
                       unset($_POST['FIRSTFORM']);
            }
       	    
       	    
       	
       	

       }
     
     if(isset($_POST['REFRESH']) && $_POST['TSNUM'] !== '') {      	
          if (isset($_POST["TSNUM"])) $postTSNUMBER = test_input($_POST["TSNUM"]); else $postTSNUMBER = ''; 
          if (isset($_POST['REMDOCS']))  $postREMDOCS  = $_POST['REMDOCS']; else $postREMDOCS="N";
          
          $getTripSheetScreens = new GetDocumentByTripNumberScreens($dbConn);
          $a = $getTripSheetScreens->showTripSheetDocuments($depotId, $postTSNUMBER, $postREMDOCS);
         
     } 
     
     if (isset($_POST['FIRSTFORM']) && $postTSNUMBER !== '') {
            if (isset($_POST["TSNUMBER"])) $postTSNUMBER = test_input($_POST["TSNUMBER"]); else $postTSNUMBER = ''; 
            if (isset($_POST['REMDOCS']))  $postREMDOCS  = $_POST['REMDOCS']; else $postREMDOCS="N";
          
            $getTripSheetScreens = new GetDocumentByTripNumberScreens($dbConn);
            $a = $getTripSheetScreens->showTripSheetDocuments($depotId, $postTSNUMBER, $postREMDOCS);
     }
// ****************************************************************************************************************************************      
     if (!isset($_POST['FIRSTFORM']) && !isset($_POST['REFRESH'])) { 
          $getTripSheetScreens = new GetDocumentByTripNumberScreens();
          $a = $getTripSheetScreens->showTripSheetNumber();
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