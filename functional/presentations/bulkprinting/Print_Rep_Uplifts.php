<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER."DAO/transactionDAO.php");
    include_once($ROOT.$PHPFOLDER."DAO/PresentationDAO.php");    
    include_once($ROOT.$PHPFOLDER."properties/Constants.php");
		include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
    include_once($ROOT.$PHPFOLDER."elements/datePickerElement.php");
    include_once($ROOT.$PHPFOLDER.'TO/PostingDistributionTO.php');
    include_once($ROOT.$PHPFOLDER.'DAO/postDistributionDAO.php');
?>
<!DOCTYPE html>
<HTML>
	<HEAD>

		<TITLE>Document Management</TITLE>

    <link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/1_kwelanga.css' rel='stylesheet' type='text/css'>
    <script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/jquery.js"></script>
    <script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/dops_global_functions.js"></script>

    <style>
    	td.head1 {font-weight:normal;
    		        font-size:2em;text-align:left; 
    		        font-family: Calibri, Verdana, Ariel, sans-serif; 
    		        padding: 0 150px 0 150px }
      
      td.det1  {border-style:solid none solid none; 
      	        border-color:DarkGray; 
      	        border-width:1px;
      	        border-collapse:collapse; 
      	        text-align: center; 
      	        font-weight: bold; 
      	        font-size: 15px;
      	        padding: 0 150px 0 150px  }

      td.det2  {border-style:solid solid solid none; 
      	        border-color:DarkGray; 
      	        border-width:1px;
      	        border-collapse:collapse; 
      	        text-align: left; 
      	        font-weight: normal; 
      	        font-size: 12px;  }
    	
    	</style>

		</HEAD>
<body>

<?php

         $class = 'odd';

    if (!isset($_SESSION)) session_start() ;
      $userUId     = $_SESSION['user_id'] ;
      $principalId = $_SESSION['principal_id'] ;
      $depotId     = $_SESSION['depot_id'] ;
      $systemId    = $_SESSION["system_id"];
      $systemName  = $_SESSION['system_name'];
      
      $postFROMDATE      = (isset($_POST["FROMDATE"])) ? htmlspecialchars($postFROMDATE=$_POST["FROMDATE"]) : CommonUtils::getUserDate();     
      $postENDDATE       = (isset($_POST["FROMDATE"])) ? htmlspecialchars($postENDDATE=$_POST["ENDDATE"]) : CommonUtils::getUserDate(); 
      $postREPLIST       = (isset($_POST["REPLIST"]))  ? htmlspecialchars($postREPLIST = test_input($_POST["REPLIST"])) : $postREPLIST;
     
      //Create new database object
     $dbConn = new dbConnect(); $dbConn->dbConnection();
     
     if (isset($_POST['cancel'])) {
        return;
     }
     $recipientsCheckCount =0;
     if (isset($_POST['finishform'])) {
  	
     	   foreach($_POST['select'] as $prow) {
             $docUid = substr($prow,10,10);           
             $oType = 'F';
             $prinUid = $principalId;
             
             $lpf = loadPrintFile($docUid, $oType, $prinUid);
     	       // Mail file
             $PresentationDAO = new PresentationDAO($dbConn);
             $mDetails = $PresentationDAO->getMailDetails($principalId, substr($prow,0,10)) ; 

             // SETUP DISTRIBUTION
              $postingDistributionTO = new PostingDistributionTO;
              $postingDistributionTO->DMLType = "INSERT";
              $postingDistributionTO->deliveryType = BT_EMAIL;
              $postingDistributionTO->subject = trim($mDetails[0]['Principal']) . ' ' . trim($mDetails[0]['deliver_name']) .' ' .  trim($mDetails[0]['document_number']);
              $postingDistributionTO->body = 'Attached is Aged Stock form' ; 
              $postingDistributionTO->attachmentFile = 'archives/emaildocs/R'.trim($mDetails[0]['document_number']) . '.pdf';
              $postingDistributionTO->destinationAddr = trim($mDetails[0]['email_addr']);
              $postDistributionDAO = new PostDistributionDAO($dbConn);
              $dResult = $postDistributionDAO->postQueueDistribution($postingDistributionTO);
        
              if ($dResult->type=FLAG_ERRORTO_SUCCESS) {
              	
                  $PresentationDAO = new PresentationDAO($dbConn);
                  $mDetails = $PresentationDAO->setMailedStatus($principalId, substr($prow,0,10)) ; 
              	
                  $recipientsCheckCount++;  //successful
                  $dbConn->dbinsQuery("commit") ;                 
              }
         }
         
         if($recipientsCheckCount > 0) {  ?> 
              <script type='text/javascript'>parent.showMsgBoxInfo('<?php echo $recipientsCheckCount ;?>Uplift Instructions Mailed Successfully')</script> 
               <?php 
               unset($_POST['firstform']);         
         }  else { ?>
              <script type='text/javascript'>parent.showMsgBoxError('Uplist Advice mail Failed')</script> 
               <?php 
               unset($_POST['firstform']);  
         }
     }
     
     if (isset($_POST['firstform'])) {
            if($postREPLIST <> 'Select a Rep') {
                    $PresentationDAO = new PresentationDAO($dbConn);
        	          $mfDDU = $PresentationDAO->getStoresToBeMailed($principalId, $postREPLIST, $postFROMDATE, $postENDDATE);
                    if (sizeof($mfDDU)!==0) { ?>
                    	    <center>          
                          <form name='reque' method=post target=''>
                              <table width="1000"; style="border:none">
                                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                    <td colspan="8" style="border:none; text-align: center; font-weight: normal; font-size:20px">Select Documents to be Mailed</td>            	
                                 </tr>	
                                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                    <td colspan="8" style="border:none; text-align: center; font-weight: bold;">&nbsp</td>            	
                                 </tr>	
                                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                    <th width="10%";  style="border:none; float: center;">Document Number</th>
                                    <th width="20%"; style="border:none; float: center;">Date</th>
                                    <th width="50%"; style="border:none; float: center;">Store</th>
                                    <th width="10%"; style="border:none; float: center;">Uplift Quantity</th>
                                    <td width="10%"; style="border:none; float: center;">Select<br><a href="javascript:;" onClick="selectAll('select[]', 1);">All</a>&nbsp;|&nbsp;<a href="javascript:;" onClick="selectAll('select[]', 0);">None</a></td>

                             </tr>
                             <?php
                             $cl = "even";
                              foreach ($mfDDU as $seRow) { ?>
                                  <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                                                                                                 
                                     <td style="border:1px solid black; border-collapse: collapse; float: center;"><?php echo $seRow['document_number'];?></td>
                                     <td style="border:1px solid black; border-collapse: collapse; float: center;"><?php echo $seRow['invoice_date'];?></td>
                                     <td style="border:1px solid black; border-collapse: collapse; float: center;"><?php echo $seRow['deliver_name'];?></td>
                                     <td style="border:1px solid black; border-collapse: collapse; float: center;"><?php echo $seRow['cases'];?></td>
                                     <td style="border:1px solid black; border-collapse: collapse; float: center;"><INPUT TYPE="checkbox" name="select[]" value= "<?php echo str_pad($seRow['document_number'],10,"0",STR_PAD_LEFT) . str_pad($seRow['Document_Uid'],10,"0",STR_PAD_LEFT) ;?>"><br></td>
                                  </tr> <?php 
                      	      } ?>              
                              <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                 <td colspan="8" style="border:none; text-align: center; font-weight: bold;">&nbsp</td>            	
                              </tr>	
                              <tr class="<?php echo GUICommonUtils::styleEO($class); ?>"> 
                             	  <td colspan="8" style="text-align: center";><INPUT TYPE="submit" class="submit" name="finishform" value= "Mail Selected">
                             	  	                                          <INPUT TYPE="submit" class="submit" name="canform"    value= "Cancel"></td>
                              </tr>
                              <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                 <td colspan="8" style="border:none; text-align: center; font-weight: bold;">&nbsp</td>            	
                              </tr>	
                          </table>
                       </form>   
                      </center> 
                      <?php 
                    } else { ?>
                             <script type='text/javascript'>parent.showMsgBoxError('No Uplifts for this Rep<br>in this period')</script> 
                             <?php 
                             unset($_POST['firstform']);
                    }
            } else {  ?>
                   <script type='text/javascript'>parent.showMsgBoxError('No Rep Selected')</script> 
                   <?php 
                   unset($_POST['firstform']);	
            }
            	
            	
     }

if(!isset($_POST['firstform'])) {
	
    $PresentationDAO = new PresentationDAO($dbConn);
    $repl = $PresentationDAO->getRepList($principalId);
    
    $class = 'odd';    
    
    ?> 
    <center>
       <FORM name='Print Rep RVL lists' method=post action=''>
            <table width="720"; style="border:none">
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td class=head1 colspan="5"; style="text-align:center";>Email Rep RVL Lists</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="5">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td width="10%"; style="border:none">&nbsp</td>
                 <td width="30%"; style="border:none">&nbsp</td>
                 <td width="30%"; style="border:none">&nbsp</td>
                 <td width="20%"; style="border:none">&nbsp</td>
                 <td width="10%"; style="border:none">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                    <td>&nbsp</td>
                    <td class=det1; >Start Date </td>
                    <td colspan="2"><?php DatePickerElement::getDatePickerLibs(); DatePickerElement::getDatePicker("FROMDATE",$postFROMDATE); ?> </td>
                    <td colspan="2"; >&nbsp;</td>   
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="5">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                    <td>&nbsp</td>
                    <td class=det1; >End Date </td>
                    <td colspan="2"><?php DatePickerElement::getDatePickerLibs(); DatePickerElement::getDatePicker("ENDDATE",$postENDDATE); ?> </td>
                    <td colspan="2"; >&nbsp;</td>   
               </tr>               
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="5">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
               	  <td >&nbsp</td>
                  <td  class=det1;>Rep </td>
               	  <td colspan="2"; style="text-align:left;">
               	  	   <select name="REPLIST" id="REPLIST">
                           <option value="Select a Rep"><?php echo 'Select a Rep' ?></option>
                                 <?php foreach($repl as $row) { ?>
                                       <option value="<?php echo trim($row['uid']) ; ?>"><?php echo $row['first_name']; ?></option>
                                 <?php } ?>
               	  	   </select>
               	  </td> 
               	  <td colspan="2"; >&nbsp;</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="5">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="5">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="firstform" value= "Email Rep RVL Lists">
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
 }
 
  function loadPrintFile($docUid, $oType, $prinUid) {
  	
  	global $ROOT; global $PHPFOLDER;?>
  	<script>
  		  	printWindow = window.open('<?php echo $ROOT.$PHPFOLDER ?>functional/presentations/view/rvl_uplift_instructions.php?PRINCIPALID=<?PHP echo $prinUid; ?>&OUTPUTTYP=<?PHP echo $oType; ?>&DOCMASTID=<?PHP echo $docUid; ?>');
   	      printWindow = window.close;
   	
   	</script>
   	<?php
  
    return;
  }
?>
 <script type="text/javascript" defer>
     function selectAll(elementName, flag){
          $("input[name='"+elementName+"']").each( function(){$(this).attr("checked",((flag == 1)?true:false));})
     }
</script> 