<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER."DAO/transactionDAO.php");
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

         $class = 'even';

    if (!isset($_SESSION)) session_start() ;
      $userUId     = $_SESSION['user_id'] ;
      $principalId = $_SESSION['principal_id'] ;
      $depotId     = $_SESSION['depot_id'] ;
      $systemId    = $_SESSION["system_id"];
      $systemName  = $_SESSION['system_name'];
      
      if (isset($_POST["INVOICE"])) $postINVOICE=test_input($_POST["INVOICE"]); else $postINVOICE = ''; 
      if (isset($_POST["WAYBILL"])) $postWAYBILL=test_input($_POST["WAYBILL"]); else $postWAYBILL = ''; 
      
      //Create new database object
     $dbConn = new dbConnect(); $dbConn->dbConnection();
     
       if (isset($_POST['finish'])) {
     	
        include_once($ROOT.$PHPFOLDER."DAO/PostTransactionDAO.php");
        $postTransactionDAO = new PostTransactionDAO($dbConn);
        
        $ordseq = test_input($_POST['orderSeq']);
        $waybill1 = $postWAYBILL;
        
        $rTO = $postTransactionDAO->updateTrackingNo($ordseq,$waybill1, 'PO');       	
       	
        if ($rTO->type==FLAG_ERRORTO_SUCCESS) {
             $dbConn->dbinsQuery("commit");
             ?>
             <script type='text/javascript'>parent.showMsgBoxInfo('Customer Ref Updated Succcessfully')</script> 
       	     <?php
       	} else {
       		   $dbConn->dbinsQuery("rollback");     
             ?>
             <script type='text/javascript'>parent.showMsgBoxInfo('Customer Ref Update Failed - Contact Support')</script> 
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
                  		<td class=head1 colspan="5"; style="text-align:center" >Document Details</td>  
             	       </tr>
                    <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                    	<td colspan="2";>&nbsp;</td>
                    	<td colspan="3"; style="text-align:center" ><input type="hidden" name="orderSeq" value=<?php echo $mfDDU[0]['uid'];?>></td>            	
                    </tr>	
                    <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                    	 <td  colspan="2";>Document&nbsp;No</td>
                       <td  colspan="3"; style="text-align:center"><?php echo substr($mfDDU[0]['document_number'],2,6);?></td>
                       
                    </tr>
                    <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                      <td  colspan="5"; style="text-align:center" >&nbsp;</td>            	
                    </tr>   
                    <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                    	 <td  colspan="2";>Customer</td>
                       <td  colspan="3"; style="text-align:center"><?php echo $mfDDU[0]['deliver_name'];?></td>
                     </tr> 
                    <tr class="<?php echo GUICommonUtils::styleEO($class);?>">  
                      <td  colspan="5"; style="text-align:center" >&nbsp;</td> 
                    </tr>   
                    <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                      <td  colspan="2";>Customer Ref&nbsp;No</td>
                    	<td  colspan="3";style="text-align:center"><input type="text" name="WAYBILL" autofocus value="<?php echo $mfDDU[0]['customer_order_number'];?>"><br></td>
                    </tr>   
                    <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                    	 <td  colspan="5" >&nbsp;</td>
                     </tr>
                     <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                      <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="finish" value= "Customer Ref Number"></td>
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
            
     }   ?>

<center>	
	 <FORM name='Select Invoice' method=post action=''>
        <table width:"720"; style="border:none">        	
           <tr>
              <td>&nbsp</td>
              <td>&nbsp</td>
           </tr>
           <tr>
              <td class=head1 colspan="2"; style="text-align:center;" >Add / Amend a Customer Ref Number</td>
           </tr>
           <tr>
              <td>&nbsp</td>
              <td>&nbsp</td>
           </tr>
           <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
              <td colspan="2";>&nbsp</td>
           </tr>	        	
           <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
              <td class=head1 style="font-weight:normal; font-size:1em">Choose the required parameters</td>
              <td>&nbsp</td>
           </tr>        	
           <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
              <td style="text-align:left";>Enter Deocument Number</td>
              <td style="text-align:left";><input type="text" name="INVOICE"></td>
           </tr>
           <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
              <td colspan="2";>&nbsp</td>
           </tr>
           <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
              <td colspan="2"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="select" value= "Get Document Details"></td>
           </tr>          
           <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
              <td colspan="2";>&nbsp</td>
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