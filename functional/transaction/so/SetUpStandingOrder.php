<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER."properties/Constants.php");
		include_once($ROOT.$PHPFOLDER.'TO/SequenceTO.php');
		include_once($ROOT.$PHPFOLDER.'DAO/SequenceDAO.php');
		include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
		include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');
    include_once($ROOT.$PHPFOLDER.'elements/basicSelectElement.php');	
    include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');	

    //Create new database object
     $dbConn = new dbConnect(); 
     $dbConn->dbConnection();
    
    if(isset($_GET['DMUID'])){$postDMUID = $_GET['DMUID'];}
    if (isset($_POST["SOLEAD"])) $postSOLEAD=$_POST["SOLEAD"]; else $postSOLEAD = '';
 
    $errorTO = new ErrorTO; 
    $result = new ErrorTO;


?>
<!DOCTYPE html>
<HTML>
	<HEAD>

		<TITLE>Document Selection</TITLE>

		<link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/1_default.css' rel='stylesheet' type='text/css'>
		<script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/jquery.js"></script>
		<script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/dops_global_functions.js"></script>

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
     $dbConn = new dbConnect(); $dbConn->dbConnection();
     
     include_once($ROOT.$PHPFOLDER."DAO/transactionDAO.php");
     include_once($ROOT.$PHPFOLDER."DAO/PostTransactionDAO.php");

     if (isset($_POST['finish']) && $postSOLEAD !== '') {
        if (isset($_POST['select'])) {     	
            $list = implode(",",$_POST['select']);
           
            // Check for existing SO for customer for a month
           
           $mtharray=$_POST['select'];
          
           foreach($mtharray as $row) {
                $transactionDAO = new TransactionDAO($dbConn);
                $mfCSD = $transactionDAO->checkForExistingSo(trim(substr($postDMUID,20,10)), $row);
                
                if(count($mfCSD) <> 0 ) {
               ?>
                <script type='text/javascript' >parent.showMsgBoxError("A Standing Order for <?php echo($mfCSD[0]['somonth']) ?> <br><br><?php echo($mfCSD[0]['deliver_name']) ?><br><br> Already Exists - Please Try again" )</script> 
                <?php 
                    return;                	
                }
                
                // subtract lead time from first day of month
                
                $yeararray = array();
                $yeararray = array('2017','2018');
                
                foreach($yeararray as $yrow) {
                
                    $datestr=($yrow .'-'. str_pad($row,2,"0",STR_PAD_LEFT) . '-01');
                    $date = date_create($datestr);
                    $newdate = date_sub($date,date_interval_create_from_date_string("'".$postSOLEAD ." days'"));
         
                    $postTransactionDAO = new PostTransactionDAO($dbConn);
                    $result = $postTransactionDAO->createStandingOrderRecord($newdate, 
                                                                       $postSOLEAD, 
                                                                       trim(substr($postDMUID,20,10)), 
                                                                       trim(str_replace("-"," ",substr($postDMUID,12,8))), 
                                                                       str_pad($row,2,"0",STR_PAD_LEFT));
 
 
                    if ($result->type!=FLAG_ERRORTO_SUCCESS) {
                       $dbConn->dbQuery("rollback");
                       $errorTO->type=$result->type;
                    ?>
                      <script type='text/javascript' >parent.showMsgBoxError("<Strong>Error updating document!</strong><BR><BR>")</script> 
                    <?php 
                       $dbConn->dbClose();
                       return;
                    } else {
                       $dbConn->dbQuery("commit");
                       $errorTO->type=$result->type;
                      $errorTO->description=$result->description;
                    }
                     
                }     
     	     }

           ?>
           <script type='text/javascript' >parent.showMsgBoxInfo("Standing Order/s successfully Created")</script> 
           <?php 
           $dbConn->dbClose();
           return; 

        }	
     }
     $class = 'odd';
?>
<center>
	 <FORM name='Set Up Standing Order' method=post action=''>
        <table width:"100%"; style="border:none">
        	<tr>
        		<td colspan"0"; style="font-weight:normal;font-size:2em;text-align:left"; font-family: Calibri, Verdana, Ariel, sans-serif; >Set Up Standing Order</td>
        		</tr>
        	<tr>
        		 <td>&nbsp</td>
        		</tr>	        	
        	<tr>
        		<td style="font-weight:normal; font-size:1em">Select the required Order Date</td>
        		</tr>        	
         </table>
        <table width:"30%"; >        	
          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
              <td style="text-align:right;"><INPUT TYPE="checkbox" name="select[]" value= "1">Jan</td>
              <td style="text-align:right;"><INPUT TYPE="checkbox" name="select[]" value= "2">Feb</td>
              <td style="text-align:right;"><INPUT TYPE="checkbox" name="select[]" value= "3">Mar</td>          
              <td style="text-align:right;"><INPUT TYPE="checkbox" name="select[]" value= "4">Apr</td>          
              <td style="text-align:right;"><INPUT TYPE="checkbox" name="select[]" value= "5">May</td>          
              <td style="text-align:right;"><INPUT TYPE="checkbox" name="select[]" value= "6">Jun</td> 
          </tr>
          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
              <td style="text-align:right;"><INPUT TYPE="checkbox" name="select[]" value= "7">Jul</td>
              <td style="text-align:right;"><INPUT TYPE="checkbox" name="select[]" value= "8">Aug</td>
              <td style="text-align:right;"><INPUT TYPE="checkbox" name="select[]" value= "9">Sep</td>          
              <td style="text-align:right;"><INPUT TYPE="checkbox" name="select[]" value= "10">Oct</td>          
              <td style="text-align:right;"><INPUT TYPE="checkbox" name="select[]" value= "11">Nov</td>          
              <td style="text-align:right;"><INPUT TYPE="checkbox" name="select[]" value= "12">Dec</td> 
          </tr>
          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
            <td>&nbsp</td>
            <td>&nbsp</td>
            <td>&nbsp</td>        
            <td>&nbsp</td>
            <td>&nbsp</td>
          </tr>
            <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
              <td colspan="4" style="text-align:left";>Select Required Preparation Time</td>
              <td colspan="2"; style="text-align:center;"><?php $lableArr = array('1 Day','2 Days','3 Days','4 Days','5 Days','6 Days','7 Days','10 Days','14 Days', '21 Days','28 Days');
                                                                $valueArr = array('1','2','3','4','5','6','7','10','14','21','28');
                                                                BasicSelectElement::buildGenericDD('SOLEAD', $lableArr,$valueArr, $postSOLEAD, "N", "N", null, null, null);?>
              </td>
          </tr>
          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
            <td>&nbsp</td>
            <td>&nbsp</td>
            <td>&nbsp</td>
            <td>&nbsp</td>
            <td>&nbsp</td>
          </tr>

        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
           <td Colspan="6">&nbsp</td>
          </tr> 
        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
           <td Colspan="6">&nbsp</td>
          </tr>
       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
        	 <td colspan="6"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="finish" value= "Create Standing Order"></td>
         </tr>          
        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
           <td Colspan="12">&nbsp</td>
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