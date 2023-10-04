<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER."DAO/transactionDAO.php");
    include_once($ROOT.$PHPFOLDER."DAO/PosttransactionDAO.php");
    include_once($ROOT.$PHPFOLDER."properties/Constants.php");
    include_once($ROOT.$PHPFOLDER."elements/basicSelectElement.php");
    include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
    include_once($ROOT.$PHPFOLDER.'DAO/TransactionDAO.php');
    include_once($ROOT.$PHPFOLDER.'TO/PostingOrderNewDetailLineTO.php');

    if (!isset($_SESSION)) session_start() ;
      $userUId     = $_SESSION['user_id'] ;
      $principalId = $_SESSION['principal_id'] ;
      $depotId     = $_SESSION['depot_id'] ;
      $systemId    = $_SESSION["system_id"];
      $systemName  = $_SESSION['system_name'];
    
      if (isset($_POST["User"]))        $postUser=$_POST["User"]; else $postUser = ''; 
      if (isset($_POST["Transaction"])) $postTransaction=$_POST["Transaction"]; else $postTransaction = ''; 
      
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

//************************************************************************************************************************
      if (isset($_POST['finish'])) {
         if (isset($_POST['select'])) {
            $list = implode(",",$_POST['select']);
            
            foreach($_POST['select'] as $row){
            	
            	if(trim(substr($row,11,3)) == DT_PAYMENT ) {
                   $PostTransactionDAO   = new PostTransactionDAO($dbConn);
                   $errorTO = $PostTransactionDAO->SaveAuthorisedPayments($userUId, ltrim(substr($row,0,10), '0'));            		
            	} else {
                   $PostTransactionDAO   = new PostTransactionDAO($dbConn);
                   $errorTO = $PostTransactionDAO->SaveAuthorisedTransctions($userUId, ltrim(substr($row,0,10), '0'));            		
            	}
            	if($errorTO->type != FLAG_ERRORTO_SUCCESS){
             ?>	
                 <script type='text/javascript' >parent.showMsgBoxError('Big Problem' )</script>
            <?php 
                  return;
              }                    
            }
        ?>	
             <script type='text/javascript'>parent.showMsgBoxInfo('Tranasctions successfully authorised for this transction type and user')</script> 
       <?php 
         } else {
       ?>	
             <script type='text/javascript' >parent.showMsgBoxError('No tranasctions authorised for this transction type and user' )</script>
       <?php 
             unset($_POST['firstform'] );
         }
      }
// **************************************************************************************
      if (isset($_POST['firstform'])) {
      	    if (trim($postUser) !='Select User') { 
      	    	
      	    	  $mfTTX = $mfTTXP = array();
                $TransactionDAO = new TransactionDAO($dbConn);
                $mfTTXP = $TransactionDAO->getUnauthorisedPayments($principalId, $postUser, DT_PAYMENT) ; 

                $postTransaction = implode(",",array(DT_CREDITNOTE,DT_MCREDIT_OTHER ));
                $TransactionDAO = new TransactionDAO($dbConn);
                $mfTTXT = $TransactionDAO->getUnauthorisedTransactions($principalId, $postUser, $postTransaction) ; 
 
      	    	  $mfTTX = array_merge($mfTTXP, $mfTTXT);
      	    	  
                if (sizeof($mfTTX)>0) { 
                    $class = 'odd';
                ?>   
                    <center>
                        <form name='Invoices' method=post target=''>
                           <br>
                           <table width="900"; style="border:none">
                                <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                   <td colspan=8; style="font-weight:bold;font-size:2em;text-align:center; font-family: Calibri, Verdana, Ariel, sans-serif;" >Authorise Transactions</td>
                                </tr>
                                <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                     <td width="6%";>&nbsp</td>  
                                     <td width="35%";>&nbsp</td>
                                     <td width="18%";>&nbsp</td>
                                     <td width="12%";>&nbsp</td>
                                     <td width="8%";>&nbsp</td>
                                     <td width="13%";>&nbsp</td>
                                     <td width="3%" ;>&nbsp</td>
                                </tr>
                                <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                     <td style="border:none; font-weight: bold;   font-size: 14px;">User</td>
                                     <td style="border:none; font-weight: Normal; font-size: 13px;"><?php echo trim($mfTTX[0]['full_name']) ?></td>
                                     <td style="border:none; font-weight: bold;   font-size: 14px;">&nbsp;</td>
                                     <td colspan=3; style="border:none; font-weight: Normal; font-size: 13px;">&nbsp;</td>
                                     <td style="border:none">&nbsp</td>
                                </tr>
                                <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                	   <td colspan=7;">&nbsp;</td>
                                </tr>
                                <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                     <td style="border:1px solid black; border-collapse: collapse; font-weight: bold;" >Number</td>
                                     <td style="border:1px solid black; border-collapse: collapse; font-weight: bold;" >Customer</td>
                                     <td style="border:1px solid black; border-collapse: collapse; font-weight: bold;" >Status</td>
                                     <td style="border:1px solid black; border-collapse: collapse; font-weight: bold;" >Date</td>
                                     <td style="border:1px solid black; border-collapse: collapse; font-weight: bold;" >Qty</td>                          
                                     <td style="border:1px solid black; border-collapse: collapse; font-weight: bold;" >Total</td>                          
                                     <td style="border:1px solid black; border-collapse: collapse; font-weight: bold;" >Authorise</td>                                                
                                </tr>
                                <?php
                                  foreach($mfTTX as $row) { 
                                  	   if(trim($mfTTX[0]['document_type_uid']) == DT_PAYMENT ){
                	   	                     $printLink = "<A href=\"javascript:;\" onClick=\"window.open('".$ROOT.$PHPFOLDER."functional/presentations/presentationManagement.php?TYPE=PAYMENTMULT&DSTATUS=Processed&CSOURCE=T&FINDNUMBER=".$row['document_number']."','myDocumentProcessing','scrollbars=yes,width=750,height=600,resizable=yes');\">". ltrim($row['document_number'],'0') ."</A>";
                                       } else  {
                                       	   $printLink = "<A href=\"javascript:;\" onClick=\"window.open('".$ROOT.$PHPFOLDER."functional/presentations/presentationManagement.php?TYPE={$row['DocType']}&DSTATUS={$row['document_status_uid']}&CSOURCE=T&FINDNUMBER=".$row['uid']."','myDocumentProcessing','scrollbars=yes,width=750,height=600,resizable=yes');\">". ltrim($row['document_number'],'0') ."</A>";
                                       }
                                   	?>
                                     <tr class="<?php echo GUICommonUtils::styleEO($class); ?>" >
                                       <td style="border:1px solid black; border-collapse: collapse; font-weight: normal;" ><?php echo $printLink; ?></td>
                                       <td style="border:1px solid black; border-collapse: collapse; font-weight: normal;" ><?php echo trim($row['deliver_name']) ?></td>
                                       <td style="border:1px solid black; border-collapse: collapse; font-weight: normal;" ><?php echo trim($row['DocType']) ?></td>
                                       <td style="border:1px solid black; border-collapse: collapse; font-weight: normal;" ><?php echo trim($row['Date']) ?></td>
                                       <td style="border:1px solid black; border-collapse: collapse; font-weight: normal; text-align:right;" ><?php echo number_format(trim($row['cases']),2,'.',' ') ?></td>
                                       <td style="border:1px solid black; border-collapse: collapse; font-weight: normal; text-align:right;" ><?php echo number_format(trim($row['Total']),2,'.',' ') ?></td>
                                       <td style="border:1px solid black; border-collapse: collapse; font-weight: normal; text-align:right;" ><INPUT TYPE="checkbox" name="select[]" value= "<?php echo str_pad(trim($row['uid']),10,'0',STR_PAD_LEFT) .'$' .  trim($mfTTX[0]['document_type_uid']) ?>"></td>
                                   </tr>
                                 <?php
                                  }   ?>  
                                     <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                	     <td colspan=7;">&nbsp;</td>
                                     </tr>
                                     <tr class="<?php echo GUICommonUtils::styleEO($class); ?>" >
                                        <td colspan="7"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="finish" value= "Authorise">
                                                                                     <INPUT TYPE="submit" class="submit" name="canfinish" value= "Cancel"></td>
                                     </tr>
                                     <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                	     <td colspan=7;">&nbsp;</td>
                                     </tr>
                           </table>
                        </form>    
                    </center>
          <?php 
                } else {
                ?>	
                    <script type='text/javascript' >parent.showMsgBoxError('No Un authorised tranasctions for this transction type and user' )</script>
                <?php 
                   unset($_POST['firstform'] );
                }   
            } else {                                                                                                                                                          	
                ?>	
                    <script type='text/javascript' >parent.showMsgBoxError('User and / or trannsaction type cannot be blank' )</script>
                <?php 
                   unset($_POST['firstform'] );
            } 
      }
                                                                                                                                  
     $TransactionDAO = new TransactionDAO($dbConn);
     $mfPUL = $TransactionDAO->getPrincipalUsersHoney($principalId);   

     $TransactionDAO = new TransactionDAO($dbConn);
     $mfTTP = $TransactionDAO->getUsertransactionType($principalId); 

if(!isset($_POST['firstform']) && !isset($_POST['finishform'])) { ?>
    <center>
       <FORM name='Select Invoice' method=post action=''>
            <table width="720"; style="border:none">
               <tr>
                 <td class=head1 >Authorise and Update Processed Documents</td>
               </tr>
               <tr>
                 <td>&nbsp</td>
               </tr>	        	
            </table>
            <table width="600"; style="border:none" >        	
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td width="20%"; style="border:none">&nbsp</td>
                 <td width="50%"; style="border:none">User</td>
                 <td width="10%"; style="border:none">&nbsp</td>
                 <td width="10%"; style="border:none">&nbsp</td>
                 <td width="10%" ; style="border:none">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                  <td colspan="1";>&nbsp;</td>
                  <td>
                    <select name="User" id="User">
                      <option value="Select User">Select User</option>
                      <?php foreach($mfPUL as $row) {                     ?>
                        <option value="<?php echo $row['uid']; ?>"><?php echo $row['full_name']; ?></option>
                      <?php } ?>
                    </select>
                   </td>
                   <td colspan="3";>&nbsp;</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                      <td Colspan="5">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="firstform" value= "Get Transaction Details">
                                                             <INPUT TYPE="submit" class="submit" name="canform"   value= "Cancel"></td>
               </tr>          
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="5">&nbsp</td>
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