<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER."properties/Constants.php");
		include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
    include_once($ROOT.$PHPFOLDER."elements/datePickerElement.php");
    include_once($ROOT.$PHPFOLDER."DAO/AgedStockDAO.php");    
    include_once($ROOT.$PHPFOLDER."DAO/SequenceDAO.php");    
    include_once($ROOT.$PHPFOLDER."DAO/PostAgedStockDAO.php");  
    include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
    		
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
      
      if (isset($_POST["INVOICE"])) $postINVOICE= test_input($_POST["INVOICE"]); else $postINVOICE = ''; 
      if (isset($_POST["DTIME"]))   $postDTIME  = test_input($_POST["DTIME"]);   else $postDTIME = CommonUtils::getUserTime(); 
      
      //Create new database object
     $dbConn = new dbConnect(); $dbConn->dbConnection();
     $errorTO = new ErrorTO;
     
     if (isset($_POST['cancel'])) {
        return;
     }
     if (isset($_POST['finish'])) {
     	
        if (isset($_POST["BOXES"]))   $boxes      = test_input($_POST["BOXES"]);   else $boxes   = '';
        if (isset($_POST["UVALUE"]))  $uvalue     = test_input($_POST["UVALUE"]);  else $uvalue  = '';
        if (isset($_POST["COMMENT"])) $comment    = test_input($_POST["COMMENT"]); else $comment = '';
        if (isset($_POST["DELBY"]))   $delby      = test_input($_POST["DELBY"]);   else $delby   = ''; 
     	
    	  $ordseq    = $_POST['orderSeq'];
     	  $docno     = $_POST['DOCNO'];
     	  $depotUid  = $_POST['DEPOT'];
     	  $psmuid    = $_POST['PSMUID'];
     	  $dstat     = $_POST['DOCSTAT'];
 
        $AgedStockDAO = new AgedStockDAO($dbConn);
        $errorTO = $AgedStockDAO->whReceiptValidation($boxes, $uvalue, $delby);
               	
        if ($errorTO->type==FLAG_ERRORTO_SUCCESS) { 
        	
             $PostAgedStockDAO = new PostAgedStockDAO($dbConn);
             $errorTO = $PostAgedStockDAO->InsertToAgeStockReceipt($depotUid, $ordseq, $psmuid, $boxes, $uvalue, $postDTIME, $postDTIME, $comment );
             if($dstat == 'Accepted') {$newstat = 71; } elseif($dstat == 'No Warehouse Receipt') {$newstat = 81; }

             $PostAgedStockDAO   = new PostAgedStockDAO($dbConn);
             $errorTO   = $PostAgedStockDAO->UpdateUpliftStatus($boxes, $ordseq , $newstat);       
             
             if ($errorTO->type!=FLAG_ERRORTO_SUCCESS) {?>
                   <script type='text/javascript'>parent.showMsgBoxError('<?php echo $errorTO->description; ?><br>Contact Kwelanga Support')</script> 
                   <?php
                   return;
             }     
             
             if ($errorTO->type==FLAG_ERRORTO_SUCCESS) {
        	         ?>
                   <script type='text/javascript'>parent.showMsgBoxInfo('Stock Receipt Capture Successful')</script> 
                   <?php
                   unset($_POST['finish']);
                   unset($_POST['firstform']);
                   unset($_POST["BOXES"]);  
                   unset($_POST["UVALUE"]); 
                   unset($_POST["COMMENT"]);
                   unset($_POST["DELBY"]);  
                   unset($_POST["DTIME"]);
                   $redisplay = 'N';
             } else {
             	
             	
             }      
       	} else {
               ?>
               <script type='text/javascript'>parent.showMsgBoxError('<?php echo $errorTO->description; ?>')</script> 
               <?php
               $postINVOICE = $docno;
               $redisplay = 'Y';
               unset($_POST['finish']);
        }
     }
     
     if ((isset($_POST['firstform']) && $postINVOICE !== '') || ($redisplay == 'Y' && $postINVOICE !== '')) { 

          $AgedStockDAO = new AgedStockDAO($dbConn);
          $mfDDU = $AgedStockDAO->getDocumentDetailsToUpdate($principalId,$postINVOICE);
          
          $AgedStockDAO = new AgedStockDAO($dbConn);
          $replst = $AgedStockDAO->getStoreRep($principalId, $mfDDU[0]['StoreUid']); 
          if (sizeof($mfDDU)!==0) { 
              $parray = Array(81, 71)	;
              if(!in_array($mfDDU[0]['document_status_uid'],$parray)) {?>
                   <center>
                     <FORM name='displayinv' method=post target=''>
                        <table width:"80%"; style="border-none";>
                          <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                            <td  colspan="5"; style="text-align:center" >&nbsp;</td>
                          </tr>
                          <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                            <td class=head1 colspan="5"; style="text-align:center" >Capture Warehouse Receipt</td>  
                          </tr>
                          <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                          	<td colspan="2";>&nbsp;</td>
                          	<td colspan="3"; style="text-align:center" ><input type="hidden" name="orderSeq" value="<?php echo $mfDDU[0]['uid'];?>">
                                                                        <input type="hidden" name="DOCNO"    value="<?php echo $postINVOICE;?>"></td>              	
                          </tr>	
                          <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                             <td  colspan="2";>Document&nbsp;No</td>
                             <td  colspan="3"; style="text-align:left"><?php echo substr($mfDDU[0]['document_number'],2,6);?></td>
                          </tr>
                          <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                            <td  colspan="5"; style="text-align:center" ><input type="hidden" name="DEPOT" value="<?php echo $mfDDU[0]['depot_uid'];?>"</td>            	
                          </tr>   
                          <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                          	 <td  colspan="2";>Customer</td>
                             <td  colspan="3"; style="text-align:left"><?php echo $mfDDU[0]['deliver_name'];?>
                             	                                         <input type="hidden" name="PSMUID" value="<?php echo $mfDDU[0]['StoreUid'];?>"</td></td>
                           </tr> 
                          <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                          	 <td colspan="5"><input type="hidden" name="DOCSTAT"  value="<?php echo $mfDDU[0]['Status'];?>"></td>
                          </tr>
                         <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                             <td colspan="2";>Date and Time Receipted</td>
                             <td colspan="2"; style="text-align:left;" ><input type="text" name="DTIME" value="<?php echo $postDTIME; ?>"></td>
                             <td colspan="1"; style="text-align:left">&nbsp;</td>
                           </tr> 
                          <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                          	 <td  colspan="5" >&nbsp;</td>
                          </tr>
                          <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                         	   <td  colspan="2";>No of Boxes</td>
                             <td  colspan="3"; style="text-align:left"><input type="text" name="BOXES" value="<?php echo $boxes; ?>"></td>
                          </tr> 
                          <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                          	 <td  colspan="5" >&nbsp;</td>
                          </tr>
                          <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                         	   <td  colspan="2";>Value</td>
                             <td  colspan="3"; style="text-align:left"><input type="text" name="UVALUE" value="<?php echo $uvalue; ?>"></td>
                          </tr>                      
                          <tr class="<?php echo GUICommonUtils::styleEO($class);?>">  
                            <td  colspan="5"; style="text-align:center" >&nbsp;</td>  
                          </tr>                      
                          <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                         	   <td  colspan="2";>Delivered By</td>
                             <td  colspan="3"; style="text-align:left">
                             	   <select name="DELBY" id="DELBY">
                                     <option value="Select Rep">Select Rep</option>
                                     <?php foreach($replst as $row) { ?>
                                               <option value="<?php echo $row['uid']; ?>"><?php echo $row['first_name']; ?></option>
                                     <?php
                                           }  
                                     ?>
                                 </select>
                           </tr>
                          <tr class="<?php echo GUICommonUtils::styleEO($class);?>">  
                            <td  colspan="5"; style="text-align:center" >&nbsp;</td>  
                          </tr>                      
                          <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                         	   <td  colspan="2";>Comments</td>
                             <td  colspan="3"><textarea name="COMMENT" id="COMMENT"  rows="2" cols="15" ><?php echo $comment; ?></textarea></td>
                          </tr>       
                           
                          <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                                <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="finish" value= "Save Receipt">
                                	                                          <INPUT TYPE="submit" class="submit" name="cancel" value= "Cancel"></td>
                           </tr>
                          <tr class="<?php echo GUICommonUtils::styleEO($class);?>">  
                            <td  colspan="5"; style="text-align:center" >&nbsp;</td>  
                          </tr>   
                   </table>
                     </form>
	                 </center>    	
          <?php
              } else { ?>
              <script type='text/javascript'>parent.showMsgBoxError('Document Already Captured')</script> 
                  <?php 
                  unset ($_POST['firstform']);
                  unset ($postINVOICE);
              }
          } else {
               ?>
               <script type='text/javascript'>parent.showMsgBoxError('No Document Found')</script> 
               <?php
               unset ($_POST['firstform']);
               unset ($postINVOICE);
          }
     }  
if(!isset($_POST['firstform']) && $postINVOICE == '') { ?>
   <center>	
       <FORM name='Capture Warehouse Receipt' method=post action=''>
            <table width:"720"; style="border:none">        	
               <tr>
                  <td>&nbsp</td>
                  <td>&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                  <td class=head1 colspan="2"; style="text-align:center;" >Capture Warehouse Receipt</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                  <td colspan="2";>&nbsp</td>
               </tr>	        	
               <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                  <td style="text-align:left";>Enter Deocument Number</td>
                  <td style="text-align:left";><input type="text" name="INVOICE"></td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                  <td colspan="2";>&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                  <td colspan="2"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="firstform" value= "Capture Receipt"></td>
               </tr>          
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                  <td colspan="2";>&nbsp</td>
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