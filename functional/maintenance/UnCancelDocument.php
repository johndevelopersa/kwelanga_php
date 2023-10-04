<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER."DAO/transactionDAO.php");
    include_once($ROOT.$PHPFOLDER."DAO/MaintenanceDAO.php");    
    include_once($ROOT.$PHPFOLDER."properties/Constants.php");
		include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
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
      
      if (isset($_POST["INVOICE"])) $postINVOICE=test_input($_POST["INVOICE"]); else $postINVOICE = ''; 
      
      //Create new database object
     $dbConn = new dbConnect(); $dbConn->dbConnection();
     
     if (isset($_POST['cancel'])) {
        return;
     }
     if (isset($_POST['finish'])) {
     	
        include_once($ROOT.$PHPFOLDER."DAO/PostTransactionDAO.php");
        $postTransactionDAO = new PostTransactionDAO($dbConn);
        
        $ordseq = $_POST['orderSeq'];
        
        $rTO = $postTransactionDAO->resetDocumentstatus($ordseq);       	
       	
        if ($rTO->type==FLAG_ERRORTO_SUCCESS) {
             $dbConn->dbinsQuery("commit");
             ?>
             <script type='text/javascript'>parent.showMsgBoxInfo('Document Status Updated Succcessfully')</script> 
       	     <?php
       	} else {
       		   $dbConn->dbinsQuery("rollback");     
             ?>
             <script type='text/javascript'>parent.showMsgBoxInfo('Document Status Update Failed - Contact Support')</script> 
       	     <?php
       	}       	
       	return;
     }
     if (isset($_POST['select']) && $postINVOICE !== '') {
     	
     	    $transactionDAO = new transactionDAO($dbConn);
        	$mfDDU = $transactionDAO->getDocumentDetailsToUpdate($principalId,$postINVOICE);
          if (sizeof($mfDDU)!==0) { ?>
     	    <center>
               <FORM name='displayinv' method=post target=''>
                  <table width:"80%"; style="border-none";>
                  	<tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                      <td  colspan="5"; style="text-align:center" >&nbsp;</td>            	
                    </tr>
                  	<tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                  		<td class=head1 colspan="5"; style="text-align:center" >Document Details</td>  
             	       </tr>
                    <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                    	<td colspan="2";>&nbsp;</td>
                    	<td colspan="3"; style="text-align:center" ><input type="hidden" name="orderSeq" value=<?php echo $mfDDU[0]['uid'];?>></td>            	
                    </tr>	
                    <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                    	 <td  colspan="2";>Document&nbsp;No</td>
                       <td  colspan="3"; style="text-align:left"><?php echo substr($mfDDU[0]['document_number'],2,6);?></td>
                       
                    </tr>
                    <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                      <td  colspan="5"; style="text-align:center" >&nbsp;</td>            	
                    </tr>   
                    <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                    	 <td  colspan="2";>Customer</td>
                       <td  colspan="3"; style="text-align:left"><?php echo $mfDDU[0]['deliver_name'];?></td>
                     </tr> 
                    <tr class="<?php echo GUICommonUtils::styleEO($class);?>">  
                      <td  colspan="5"; style="text-align:center" >&nbsp;</td>  
                    </tr>   
                    <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                      <td  colspan="2";>Current Document Status&nbsp;No</td>
                    	<td  colspan="3";style="text-align:left"> <?php echo $mfDDU[0]['Status'];?><br></td>
                    </tr>   
                    <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                    	 <td  colspan="5" >&nbsp;</td>
                     </tr>
                    <?php if($mfDDU[0]['document_status_uid'] <> DST_DELIVERED_POD_OK) { ?>
                        <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                          <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="cancel" value= "Status Cannot be Reset"></td>
                        </tr>
                    <?php } else{ ?>
                        <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                          <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="finish" value= "Reset Status To 'Invoiced'">
                          	                                          <INPUT TYPE="submit" class="submit" name="cancel" value= "Cancel"></td>
                        </tr>
                    <?php } ?>
                    <tr class="<?php echo GUICommonUtils::styleEO($class);?>">  
                      <td  colspan="5"; style="text-align:center" >&nbsp;</td>  
                    </tr>   
             </table>
	        </center>    	
          <?php
            return;
          } else {
          ?>	
            <center>
 				     <table>
 					     <tr>
                  <td colspan="5" style="border:1px solid black; border-collapse: collapse; text-align: center; font-weight: bold;">Invoice number not found !!</td>            	
               </tr>	
    		      </table>
    		     </center>
    		   <?php 
    		     return;
          }	  
            
     }  

if(!isset($_POST['firstform'])) {
	
	echo $userUId;
	echo "<br>";
	echo $principalId;
	echo "<br>";
	
    $MaintenanceDAO = new MaintenanceDAO($dbConn);
    $aWP = $MaintenanceDAO->getReQuePrincipalList($userUId, $principalId);
    
    $class = 'odd';    
    
    ?> 
    <center>
       <FORM name='Uncancel a document' method=post action=''>
            <table width="720"; style="border:none">
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td class=head1 colspan="5"; style="text-align:center";>Uncancel a document</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td width="10%"; style="border:none">&nbsp</td>
                 <td width="30%"; style="border:none">&nbsp</td>
                 <td width="30%"; style="border:none">&nbsp</td>
                 <td width="20%"; style="border:none">&nbsp</td>
                 <td width="10%"; style="border:none">&nbsp</td>
               </tr>
               
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
               	  <td >&nbsp</td>
                  <td  class=det1;>Principal </td>
               	  <td colspan="2"; style="text-align:left;">
               	  	   <select name="Principal" id="Principal">
                           <option value="Select a Principal"><?php echo 'Select a Principal' ?></option>
                                 <?php foreach($aWP as $row) { ?>
                                       <option value="<?php echo trim($row['principal_uid']) ; ?>"><?php echo $row['principal']; ?></option>
                                 <?php } ?>
               	  	   </select>
               	  </td> 
               	  <td colspan="2"; >&nbsp;</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="5">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
               	    <td >&nbsp</td>
                    <td style="text-align:left";>Enter Deocument Number</td>
                    <td colspan="2"; style="text-align:left";><input type="text" name="INVOICE"></td>
                    <td >&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="5">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="firstform" value= "Un">
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
?> 