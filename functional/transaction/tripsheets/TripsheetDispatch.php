<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER."properties/Constants.php");
    include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
    include_once($ROOT.$PHPFOLDER."libs/CommonUtils.php");
    include_once($ROOT.$PHPFOLDER."DAO/TripSheetDAO.php");		
    include_once($ROOT.$PHPFOLDER."TO/TripDispatchTO.php");		
    include_once($ROOT.$PHPFOLDER."TO/TripDispatchDetailTO.php");		
	  include_once($ROOT.$PHPFOLDER.'TO/SequenceTO.php');
		include_once($ROOT.$PHPFOLDER.'DAO/SequenceDAO.php');
		
     if (!isset($_SESSION)) session_start() ;
     $userUId     = $_SESSION['user_id'] ;
     $principalId = $_SESSION['principal_id'] ;
     $depotId     = $_SESSION['depot_id'] ;
                
//Create new database object
$dbConn = new dbConnect(); 
$dbConn->dbConnection();
$errorTO = new ErrorTO;

?>
<!DOCTYPE html>
<html>
  <head>

		<TITLE>Document Selection</TITLE>

    <link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/1_kwelanga.css' rel='stylesheet' type='text/css'>
    <link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/kos_standard_screen.css' rel='stylesheet' type='text/css'>
    
    <script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/jquery.js"></script>
    <script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/dops_global_functions.js"></script>

  </head>
<?php

if(isset($_POST['CANFORM'])) { 

	    $dashpos = strpos($_POST['TTO'],"-");
      $depId   = trim(substr($_POST['TTO'],0, $dashpos));
      $tripNo  = trim(substr($_POST['TTO'],$dashpos + 1, 10));

      unset($_POST['INVOICENO']);
	    unset($displayCapture);
	    
	}


if(isset($_POST['SAVEDISPATCH'])) { 
	 if($_SESSION['ddocs'] == $_POST['NODOC']) {
	 	
      $seqVal="000000";
      $sequenceDAO = new SequenceDAO($dbConn);
      $sequenceTO  = new SequenceTO;
      $sequenceTO->sequenceKey=LITERAL_SEQ_TRIPSHEET_DISPATCH;
      $sequenceTO->depotUId = $depotId;	
      $result=$sequenceDAO->getSequence($sequenceTO,$seqVal);
      if ($result->type!=FLAG_ERRORTO_SUCCESS) { ?>
          <script type='text/javascript'>parent.showMsgBoxInfo('Trip Sheet Dispatch Sequence not set up')</script>  
          <?php
          echo $result;
          return $result;
          echo "<br>";
      }
      
      $tripSheetDespatch  = $seqVal;
	    $dashpos = strpos($_POST['TTO'],"-");
      $depId   = trim(substr($_POST['TTO'],0, $dashpos));
      $tripNo  = trim(substr($_POST['TTO'],$dashpos + 1, 10));

      $TripSheetDAO = new TripSheetDAO($dbConn);
      $errorTO = $TripSheetDAO->saveTripSheetDispatch($depId, $tripNo, 'Y', $tripSheetDespatch, $userUId);

      if($errorTO->type==FLAG_ERRORTO_SUCCESS) {?>
      	   <script type='text/javascript'>parent.showMsgBoxInfo('Tripsheet dispatch authorised<BR><BR>Dispatch Number <?php echo $tripSheetDespatch; ?>')</script> 
      <?php	
           unset($_POST['INVOICENO']);
	         unset($displayCapture);	
      } else {
      	    echo "<pre>";
      	    print_r($errorTO);
            ?>
      	   <script type='text/javascript'>parent.showMsgBoxError('Tripsheet dispatch Failed <br><br> Contact Kwelanga Support')</script>
      <?php	
           unset($_POST['INVOICENO']);
	         unset($displayCapture);	 	  	    	
      }
	    unset($_POST['INVOICENO']);
	    unset($displayCapture);	 	    
	 }	
	}

if(isset($_POST['INVOICENO'])) { 
	
    $docUidStr =  trim($_POST['DOCUID']) . $_POST['INVOICENO'] . "," ;
    
    $dashpos = strpos($_POST['TTO'],"-");
    $depId   = trim(substr($_POST['TTO'],0, $dashpos));
    $tripNo  = trim(substr($_POST['TTO'],$dashpos + 1, 10));
	
    $TripDispatchTO  = new TripDispatchTO;
    $TripDispatchTO->scannedNo = $_POST['TTO'];
    $TripDispatchTO->whId      = $depId ;
    $TripDispatchTO->tsNumber  = $tripNo;
    
    $iDashpos  = strpos($_POST['INVOICENO'],"-");
    $iPrinId   = trim(substr($_POST['INVOICENO'],0, $iDashpos));
    $iDocNo    = str_pad(trim(substr($_POST['INVOICENO'],$iDashpos + 1, 10)),8,'0',STR_PAD_LEFT);
    
    $TripSheetDAO = new TripSheetDAO($dbConn);
    $chTS = $TripSheetDAO->checkTripSheetDispatch($iPrinId, $iDocNo, $tripNo );
    
    if(count($chTS) <> 0) {    	
    	      if($chTS[0]['document_verified_for_dispatch'] <> 'P') {
                    $TripSheetDAO = new TripSheetDAO($dbConn);
                    $errorTO = $TripSheetDAO->pendTripSheetDispatch($iPrinId, $iDocNo, $tripNo, $userUId );
            } else {?>
                 <script type='text/javascript' >parent.showMsgBoxError("Document Already Selected For Dispatch<BR><BR>")</script> 	
                 <?php 
           	     unset($_POST['INVOICENO']);
	               unset($displayCapture);	 	  
           	}
           	
    } else { ?>
          <script type='text/javascript' >parent.showMsgBoxError("Document Not Found on this Trip Sheet <BR><BR> <?php echo  $iDocNo; ?>") </script> 	
          <?php 
          unset($_POST['INVOICENO']); 
//	        unset($displayCapture);	 	    	
    }
    $displayCapture = TRUE;
	}
if(isset($_POST['TRIPNO']) && !isset($displayCapture)) { 
	
	    $dashpos = strpos($_POST['TRIPNO'],"-");
      $depId   = trim(substr($_POST['TRIPNO'],0, $dashpos));
      $tripNo  = trim(substr($_POST['TRIPNO'],$dashpos + 1, 10));
	
      $TripDispatchTO  = new TripDispatchTO;
      $TripDispatchTO->scannedNo = $_POST['TRIPNO'];
      $TripDispatchTO->whId      = $depId ;
      $TripDispatchTO->tsNumber  = $tripNo;
      
      $_SESSION['ddocs'] = 0 ;

      $displayCapture = TRUE;
	
}

       if($displayCapture) { 
       	
       	    $TripSheetDAO = new TripSheetDAO($dbConn);
            $cnthTS = $TripSheetDAO->countTripSheetDispatched($TripDispatchTO->whId, $TripDispatchTO->tsNumber);
            $_SESSION['ddocs'] = $cnthTS[0]['docCnt'];
            $TripSheetDAO = new TripSheetDAO($dbConn);
            $mfTS = $TripSheetDAO->getTripSheetInvoices($TripDispatchTO->whId, $TripDispatchTO->tsNumber);
            
            if(count($mfTS) <> 0)  { 
                 if($mfTS[0]['verified_for_dispatch'] <> 'Y') {  ?>
                    <body onload='setFocusToTextBoxI()'>
                        <center>
                          <form name='Dispatch a Tripsheet2' method=post action=''>
                             <table width="720"; style="border:none">
                                <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                    <td class=head1 colspan="8"; style="text-align:center";><?php echo $mfTS[0]['Warehouse'];?>&nbsp;&nbsp;-&nbsp;&nbsp;TripSheet Handover</td>
                                </tr>
                                <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                	  <td width="5%"; style="border:none">&nbsp;</td>
                                    <td width="20%"; style="border:none">&nbsp;</td>
                                    <td width="20%"; style="border:none">&nbsp;</td>
                                    <td width="10%"; style="border:none">&nbsp;</td>
                                    <td width="20%"; style="border:none">&nbsp;</td>
                                    <td width="10%"; style="border:none">&nbsp;</td>
                                    <td width="10%"; style="border:none">&nbsp;</td>
                                    <td width="5%"; style="border:none">&nbsp;</td>
                                </tr> 	
                                <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                   <td class="det2" colspan="1" style="text-align:left;" >&nbsp;</td>
                                   <td class="det1" colspan="2" style="text-align:left;" >Transporter / Agent</td>
                                   <td Colspan="3"><?php echo $mfTS[0]['name'];?></td>
                                   <td Colspan="2">&nbsp</td>
                                </tr>              	
                                <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                  <td Colspan="8">&nbsp<input type="hidden" name="TTO" value='<?php echo $TripDispatchTO->scannedNo; ?>'></td>
                                <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                   <td class="det2" colspan="1" style="text-align:left;" >&nbsp;</td>
                                   <td class="det1" style="text-align:left;" >Trip&nbsp;Sheet&nbsp;No</td>
                                   <td Colspan="2"><?php echo $TripDispatchTO->scannedNo;?></td>
                                   <td class="det1" style="text-align:left;" >Tripsheet&nbsp;Date</td>
                                   <td Colspan="2"><?php echo $mfTS[0]['tripsheet_date'];?></td>
                                   <td Colspan="1">&nbsp</td>
                                </tr>              	
                                <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                  <td Colspan="8">&nbsp</td>
                     
                                </tr>
                                <?php if($_SESSION['ddocs'] <> count($mfTS)) { $capBlock = ''; $cmessage = 'Scan Or Enter';} else { $capBlock = 'disabled'; $cmessage = 'Trip Sheep Complete';} ; ?>
                                
                                <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                   <td class="det2" colspan="1" style="text-align:left;" >&nbsp;</td>
                                   <td class="det1" style="text-align:left;" >No Of Invoices</td>
                                   <td Colspan="4"><?php echo count($mfTS);?></td>
                                   <td Colspan="2"><input type="hidden" name="NODOC" value='<?php echo count($mfTS); ?>'></td>
                                </tr>              	
                                <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                    <td colspan="8">&nbsp</td>	
                                </tr>
                                <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                    <td colspan="8" style="text-align:center";><INPUT TYPE="submit" class="submit" name="SAVEDISPATCH" value= "Save and Dispatch">
                                       	                                       <INPUT TYPE="submit" class="submit" name="CANFORM" value= "Cancel"></td>
                                </tr>
                                <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                    <td colspan="8">&nbsp</td>	
                                </tr>
                                <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                   <td class="det2" colspan="1" style="text-align:left;" >&nbsp;</td>
                                   <td class="det1" colspan="2" style="text-align:left;" >Enter / Scan Invoice</td>
                                   <td Colspan="3"><INPUT TYPE="TEXT" size="20" name="INVOICENO" id="INVOICENO" <?php echo $capBlock; ?> placeholder='<?php echo $cmessage; ?>'></td>
                                   <td colspan="2">&nbsp</td>
                                </tr>  
                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                <td colspan="8">&nbsp</td>	
                             </tr>
                                <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                   <td class="det2" colspan="1" style="text-align:left;" >&nbsp;</td>
                                   <td class="det1" colspan="1" style="text-align:left;" >Principal</td>
                                   <td class="det1" colspan="1" style="text-align:left;" >Document No</td>
                                   <td class="det1" colspan="3" style="text-align:left;" >Delivery Point</td>
                                   <td class="det1" colspan="1" style="text-align:right;" >Cases</td>
                                   <td Colspan="1">&nbsp</td>
                                </tr>  
                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                <td colspan="8">&nbsp</td>	
                             </tr>
                             <?php
                             $allNd = 0;
                             
                             foreach($mfTS as $row) { 
                             	  if($row['document_verified_for_dispatch'] == 'P') {
                                    $allNd++ ; ?>
                                    <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                        <td class="det2" colspan="1" style="text-align:left;" >&nbsp;</td>
                                        <td class="det3" colspan="1" style="text-align:left; color:red;" ><?php echo $row['Principal'];?></td>
                                        <td class="det3" colspan="1" style="text-align:left; color:red;" ><?php echo $row['document_number'];?></td>
                                        <td class="det3" colspan="3" style="text-align:left; color:red;" ><?php echo $row['deliver_name'];?></td>
                                        <td class="det3" colspan="1" style="text-align:right; color:red;"><?php echo $row['cases'];?></td>
                                        <td Colspan="1">&nbsp</td>
                                    </tr> 
                                    <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                        <td colspan="8">&nbsp</td>	
                                    </tr>
                                <?php
                                }
                             }
                                if($allNd == 0) { ?>
                                    <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                        <td class="det2" colspan="1" style="text-align:left;" >&nbsp;</td>
                                        <td class="det3" colspan="3" style="text-align:left; color:red;" >No invoices Selected to Dispatch</td>
                                        <td class="det1" colspan="1" style="text-align:left;" >&nbsp;</td>
                                        <td Colspan="3">&nbsp</td>
                                    </tr> 
                                    <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                        <td colspan="8">&nbsp</td>	
                                    </tr>
                                    <?php	
                                } ?>
                                    <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                        <td colspan="8">&nbsp</td>	
                                    </tr>
                             </table>
                          </form>
                        </center> 
                    </body>
                <?php
                 } else { ?>
                    <script type='text/javascript' >parent.showMsgBoxError("This Trip Sheet is already Dispatched <BR><BR>")</script> 	
                    <?php 
                    unset($_POST['INVOICENO']);
	                  unset($displayCapture);
	                  unset($_POST['SAVEDISPATCH']);
	                  unset($_POST['TRIPNO']);
                 }	 	
            }      else { ?>
               <script type='text/javascript' >parent.showMsgBoxError("No Tripsheet Found - Please try Again!!<BR><BR>")</script> 	
               <?php 
               unset($_POST['INVOICENO']);
	             unset($displayCapture);
	             unset($_POST['SAVEDISPATCH']);
	             unset($_POST['TRIPNO']);	 	
            }
       }

// ****************************************************************************************************************************************
if(!isset($_POST['TRIPNO']) && !isset($displayCapture)) { 
     ?>
         <body onload='setFocusToTextBox()'>    	  
        <center>
           <form name='Dispatch a Tripsheet' method=post action=''  onload='setFocusToTextBox()'>
              <table width="720"; style="border:none">
                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                    <td class=head1 colspan="6"; style="text-align:center";>Verify a Tripsheet for Dispatch</td>
                 </tr>
                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                    <td width="10%"; style="border:none">&nbsp;</td>
                    <td width="30%"; style="border:none">&nbsp;</td>
                    <td width="30%"; style="border:none">&nbsp;</td>
                    <td width="10%"; style="border:none">&nbsp;</td>
                    <td width="10%"; style="border:none">&nbsp;</td>
                    <td width="10%"; style="border:none">&nbsp;</td>
                 </tr>              	
                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                   <td Colspan="6">&nbsp</td>
                 </tr>              	
                    <td Colspan="1">&nbsp</td>
                    <td class="det1" Colspan="1">Enter&nbsp;or&nbsp;Scan&nbsp;Tripsheet&nbsp;No</td>
              	    <td class="det1" colspan="1" style="text-align:left";><INPUT TYPE="TEXT" size="15" name="TRIPNO" id="TRIPNO" ></td>
                    <td Colspan="3">&nbsp</td>
                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                   <td Colspan="6">&nbsp</td>
                 </tr>
                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                      <td colspan="6">&nbsp</td>	
                 </tr>
              </table>
	  	     </form>
        </center>
        <?php 
} ?>

<script type="text/javascript">
function setFocusToTextBox(){
    document.getElementById("TRIPNO").focus();
}
function setFocusToTextBoxI(){
    document.getElementById("INVOICENO").focus();
}


</script>
    
</body>        
</html>
 
 <?php 

function tripsheetlist() {
	
	echo transporter;
	
}	

?>
