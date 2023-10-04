<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER."DAO/transactionDAO.php");
    include_once($ROOT.$PHPFOLDER."properties/Constants.php");
    include_once($ROOT.$PHPFOLDER.'TO/SequenceTO.php');
    include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
    include_once($ROOT.$PHPFOLDER.'DAO/SequenceDAO.php');
    include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
    include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');
    include_once($ROOT.$PHPFOLDER."elements/datePickerElement.php");
    include_once($ROOT.$PHPFOLDER.'elements/basicSelectElement.php');	
    
    $errorTO = new ErrorTO;
    	
?>
<!DOCTYPE html>
<HTML>
	<HEAD>

		<TITLE>Document Selection</TITLE>

		<link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/4_default.css' rel='stylesheet' type='text/css'>
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

    if (!isset($_SESSION)) session_start() ;
      $userUId     = $_SESSION['user_id'] ;
      $principalId = $_SESSION['principal_id'] ;
      $depotId     = $_SESSION['depot_id'] ;
      $systemId    = $_SESSION["system_id"];
      $systemName  = $_SESSION['system_name'];
      
      $returnMessage;
            
      if (isset($_POST["TSNUMBER"])) $postTSNUMBER=test_input($_POST["TSNUMBER"]); else $postTSNUMBER = ''; 
      if (isset($_POST["DOCNUMBER"])) $postDOCNUMBER=test_input($_POST["DOCNUMBER"]); else $postDOCNUMBER = ''; 
      if (isset($_POST['reason'])) $postreason = $_POST['reason']; else $postreason="";
      
      //Create new database object
     $dbConn = new dbConnect(); $dbConn->dbConnection();
     
       if (isset($_POST['select'])) {
       	
         if ($postDOCNUMBER  == '') {
              $returnMessages=new ErrorTO;
              $returnMessages->type=FLAG_ERRORTO_ERROR;
              $returnMessages->description="No Document number entered.\\n Please Try again.";
              print(CommonUtils::getJavaScriptMsg($returnMessages));
              return;
       	  }         	

          if ($postTSNUMBER == '') {
              $message = "No Tripsheet Number Entered\\n Try again.";
              echo "<center>";
              echo "<script type='text/javascript'>alert('$message');</script>";
              echo "</center>";
              return;
       	  }         	

          include_once($ROOT.$PHPFOLDER."DAO/TransactionDAO.php");
          $TransactionDAO = new TransactionDAO($dbConn);
          
          $tsTO = $TransactionDAO->getInvoicesNotOnTripsheet($principalId, $postDOCNUMBER );
          
          if (count($tsTO) == 0){
              $returnMessages=new ErrorTO;
              $returnMessages->type=FLAG_ERRORTO_ERROR;
              $returnMessages->description="No Document found.";
              print(CommonUtils::getJavaScriptMsg($returnMessages));
             return;
          };
 
          
          
          
          print_r($tsTO);
          
          include_once($ROOT.$PHPFOLDER."DAO/TransactionDAO.php");
          $TransactionDAO = new TransactionDAO($dbConn);
          
          $tnTO = $TransactionDAO->getTripsheetNumbers($principalId, $postTSNUMBER );
          
          print_r($tnTO);          
          
          
          
          
      	
       	  include_once($ROOT.$PHPFOLDER."DAO/PostTransactionDAO.php");
          $postTransactionDAO = new PostTransactionDAO($dbConn);
          
          $rTO = $postTransactionDAO->removeInvoiceFromTripSheet($list,$postreason,$userUId);       	
       	
        if ($rTO->type==FLAG_ERRORTO_SUCCESS) {
             $dbConn->dbinsQuery("commit");
             ?>
             <script type='text/javascript'>parent.showMsgBoxInfo('Tracking Number Updated Succcessfully')</script> 
       	     <?php
       	} else {
       		   $dbConn->dbinsQuery("rollback");     
             ?>
             <script type='text/javascript'>parent.showMsgBoxInfo('Tracking Number Update Failed - Contact Support')</script> 
       	     <?php
       	}       	
       	return;
     }
     $class = 'odd';
?>    
<center>
	 <FORM name='Select Invoice' method=post action=''>
        <table width:"100%"; style="border:none">
        	<tr>
        		<td class=head1 >Add Document to Tripsheet</td>
        		</tr>
        	<tr>
        		 <td>&nbsp</td>
        		</tr>	        	
        	<tr>
        		<td class=head1 style="font-weight:normal; font-size:1em">Choose the required parameters</td>
        		</tr>        	
         </table>
        <table width:"100%"; >
          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
           <td style="text-align:left";>Enter the Document Number&nbsp&nbsp</td>
           <td colspan="2"; style="text-align:left"><input type="text" name="DOCNUMBER"><br></td>
           <td>&nbsp</td>
           <td>&nbsp</td>
          </tr>
          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
            <td>&nbsp</td>
            <td>&nbsp</td>
            <td>&nbsp</td>
            <td>&nbsp</td>
            <td>&nbsp</td>
          </tr>
        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
           <td style="text-align:left";>Enter Tripsheet Number&nbsp&nbsp&nbsp&nbsp</td>
           <td colspan="2"; style="text-align:left"><input type="text" name="TSNUMBER"><br></td>
           <td>&nbsp</td>
           <td>&nbsp</td>
          </tr>
        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
           <td Colspan="5">&nbsp</td>
          </tr>
          </tr>
       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
        	 <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="select" value= "Add document to Tripsheet"></td>
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
 function test_input($data) {

  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  
  return $data;
 }
?> 