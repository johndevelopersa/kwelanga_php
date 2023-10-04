<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER."DAO/transactionDAO.php");
    include_once($ROOT.$PHPFOLDER."properties/Constants.php");
    include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
    include_once($ROOT.$PHPFOLDER.'DAO/StoreDAO.php');

    if (!isset($_SESSION)) session_start() ;
      $userUId     = $_SESSION['user_id'] ;
      $principalId = $_SESSION['principal_id'] ;
      $depotId     = $_SESSION['depot_id'] ;
      $systemId    = $_SESSION["system_id"];
      $systemName  = $_SESSION['system_name'];
      
      if (isset($_POST["INVOICE"])) $postINVOICE=test_input($_POST["INVOICE"]); else $postINVOICE = ''; 
      if (isset($_POST["DOCUMENTTYPE"])) $postDOCTYPE=test_input($_POST["DOCUMENTTYPE"]); else $postDOCTYPE = 0 ; 
      
      if($postDOCTYPE == 1 ){
           $DT = DT_CREDITNOTE;
           $DTDes =  "Credit Note" ;         
      } elseif ($postDOCTYPE == 2) {
           $DT = DT_MCREDIT_OTHER ;     	
           $DTDes =  "Manual Credit Note" ;
      } else {
           $DT = DT_ORDINV;
           $DTDes =  "Order/Invoice" ;
      }
            
      //Create new database object
      $dbConn = new dbConnect(); $dbConn->dbConnection();
?>
<!DOCTYPE html>
<HTML>
	<HEAD>

		<TITLE>Document Selection</TITLE>

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
      $class = 'even';
      
      if (isset($_POST['canform'])) {
          return;	
      }
      
      if (isset($_POST['finishform'])) {
      	    if($_POST['Rep'] <> 'Select OverRide Rep' && $_POST['Rep'] <> $_POST['firstrep']) {
      	          // Write new product to document detail and reclculate total
                  include_once($ROOT.$PHPFOLDER."DAO/PostTransactionDAO.php");
                  $postTransactionDAO = new PostTransactionDAO($dbConn);
                  $result = $postTransactionDAO->UpdateOverRideRep($_POST['documentUid'], $_POST['Rep'], $userUId); 
                  
                  if($result->type  == FLAG_ERRORTO_SUCCESS) { ?>
                      <script type='text/javascript'>parent.showMsgBoxInfo('OverRide Rep Updated')</script> 
                      <?php
                       unset($postINVOICE);
                       unset($_POST['select']);
                       unset($_POST['firstform'] );
                       unset($_POST['finishform']); 
                  } else {
                       echo 'Oh Shit';
                       
                  }               
      	    } else {
      	    ?>
                 <script type='text/javascript'>parent.showMsgBoxInfo('No Update Selected')</script> 
                 <?php
                 
                  unset($postINVOICE);
                  unset($_POST['select']);
                  unset($_POST['firstform'] ); 
                  unset($_POST['finishform']);
      	    	
      	    }
      }
     if (isset($_POST['firstform'])) {
          if ($postINVOICE !== '') {
              $transactionDAO = new transactionDAO($dbConn);
              $mfDDU = $transactionDAO->getDocumentHeaderToUpdate($principalId,$postINVOICE, $DT);
              
//             print_r($mfDDU);
              
              if (sizeof($mfDDU) > 0) {
                  if($mfDDU[0]['overide_rep_code_uid'] == 0 || $mfDDU[0]['overide_rep_code_uid'] == NULL) {
                      $fstRep   = "Select Overide Rep";
                      $fstRepId = "Do_Update";
                  } else {
                      $fstRep = trim($mfDDU[0]['first_name']) . ' ' . trim($mfDDU[0]['surname']);
                      $fstRepId = $mfDDU[0]['psruid'];
                  }
              
                  $storeDAO = new StoreDAO($dbConn);
                  $mfPR = $storeDAO->getPrincipalSalesRepAll($principalId); 
                  ?>
                        <center>
                            <FORM name='Select Invoice' method=post action=''>
                                 <table width="700"; style="border:none">
                                     <tr>
                                          <td class=head1 colspan="5"; style="text-align:center">Add Change OverRide Rep on Document</td>
                                      </tr>
                                      <tr>
                                         <td>&nbsp;</td>
                                      </tr>
                                      <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>                               
                                         <td colspan="5"; style="text-align:left">&nbsp</td>
                                      </tr>
                                      <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                         <td width="45%"; style="border:none">&nbsp</td>
                                         <td width="5%"; style="border:none">&nbsp</td>
                                         <td width="25%"; style="border:none">&nbsp</td>
                                         <td width="25%"; style="border:none">&nbsp</td>
                                      </tr>
                                      <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                         <td style="border:none; text-align:left;"><?php echo "Customer  :  " . trim($mfDDU[0]['deliver_name'] ." "); ?></td>
                                         <td style="border:none">&nbsp</td>
                                         <td style="border:none text-align:right;"><?php echo trim($DTDes) ." "; ?></td>
                                         <td style="border:none text-align:left ;"><?php echo substr($mfDDU[0]['document_number'],2,6) ." "; ?></td>
                                      </tr>
                                      <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>                               
                                         <td colspan="4"; style="text-align:left">&nbsp</td>
                                      </tr>
                                      <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                         <td colspan="4"; style="border:none; text-align:left;">Sales Rep</td>
                                      </tr> 
                                     <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                                         <td>
                                            <select name="Rep" id="Rep">
                                             <option value="Select OverRide Rep"><?php echo $fstRep ?></option>
                                             <?php foreach($mfPR as $row) { ?>
                                                  <option value="<?php echo trim($row['uid']); ?>"><?php echo $row['first_name'] ." - " . trim($row['surname']); ?></option>
                                             <?php } ?>
                                            </select>
                                         </td>  
                                         <td colspan="5"; style="border:none; text-align:right;">
                                             <input type="hidden" id="documentUid" name="documentUid" value=<?php echo $mfDDU[0]['uid'];?>> </td>
                                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>                               
                                          <td colspan="5"; style="text-align:left"><input type="hidden" id="firstrep" name="firstrep" value=<?php echo $fstRepId;?>> </td>
                                       </tr>
                                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>                               
                                          <td colspan="5"; style="text-align:left">&nbsp</td>
                                       </tr>
                                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>                               
                                          <td colspan="5"; style="text-align:left">&nbsp</td>
                                       </tr>
                      	               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";> 
                                          <td colspan="5"; style="text-align:center"><INPUT TYPE="submit" class="submit" name="finishform" value= "Update Override Rep"><INPUT TYPE="submit" class="submit" name="canform" value= "Cancel"></td>
                                       </tr>
                                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>                               
                                           <td colspan="5";>&nbsp</td>
                                       </tr>
                                  </table>
                            </form>
                        </center> 
              <?php          
             } else { ?>
                 <script type='text/javascript'>parent.showMsgBoxError('Document Number/Type no Found - Check Input')</script> 
                 <?php
                 unset($postINVOICE);
                 unset($_POST['select']);
                 unset($_POST['firstform'] ); 
            	
              }
          } else { ?>
                 <script type='text/javascript'>parent.showMsgBoxError('Document Number cannot be blank')</script> 
                 <?php
                 unset($postINVOICE);
                 unset($_POST['select']); 
                 unset($_POST['firstform'] ); 	
          }       	
     }         	
// ****************************************************************************
if(!isset($_POST['firstform']) && !isset($_POST['finishform'])) { ?>
    <center>
       <FORM name='Select Invoice' method=post action=''>
            <table width="720"; style="border:none">
               <tr>
                 <td class=head1 >Add Change OverRide Rep on Document</td>
               </tr>
               <tr>
                 <td>&nbsp</td>
               </tr>	        	
               <tr>
                 <td class=head1 style="font-weight:normal; font-size:1em">Enter the required document Number</td>
               </tr>        	
            </table>
            <table width="720"; style="border:none" >        	
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td width="6%"; style="border:none">&nbsp</td>
                 <td width="22%"; style="border:none">&nbsp</td>
                 <td width="22%"; style="border:none">&nbsp</td>
                 <td width="15%"; style="border:none">&nbsp</td>
                 <td width="29%"; style="border:none">&nbsp</td>
                 <td width="6%" ; style="border:none">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
               	 <td Colspan="1">&nbsp</td>
                 <td style="text-align:right";>Enter Document Number</td>
                 <td style="text-align:left"><input type="text" name="INVOICE"></td>

                 <td style="text-align:right";>Document Type</td>
                 <td style="text-align:left";><select name="DOCUMENTTYPE" id="DOCUMENTTYPE">
                                                 <option value="0">Order/Invoice</option>
                                                 <option value="1">Credit Note</option>
                                                 <option value="2">Manual Credit</option>
                                              </select></td>
                 <td Colspan="1">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="6">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td>&nbsp</td>
                 <td colspan="4"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="firstform" value= "Get Document Details">
                                                             <INPUT TYPE="submit" class="submit" name="canform"   value= "Cancel"></td>
                 <td>&nbsp</td>
               </tr>          
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="6">&nbsp</td>
               </tr>  
 			    	</table>
		   </form>
    </center> 
	</body>       
 </HTML>
<?php 
}
 function test_input($data) {

  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  
  return $data;
 }
?> 