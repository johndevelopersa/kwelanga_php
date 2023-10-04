<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER."DAO/transactionDAO.php");
    include_once($ROOT.$PHPFOLDER."properties/Constants.php");
		include_once($ROOT.$PHPFOLDER.'TO/SequenceTO.php');
		include_once($ROOT.$PHPFOLDER.'DAO/SequenceDAO.php');
		include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
		include_once($ROOT.$PHPFOLDER."elements/datePickerElement.php");
    include_once($ROOT.$PHPFOLDER.'elements/basicSelectElement.php');		
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
      
      $postFROMDATE = (isset($_POST["FROMDATE"])) ? htmlspecialchars($_POST["FROMDATE"]) :  CommonUtils::getUserDate();
      $postTODATE   = (isset($_POST["TODATE"])) ? htmlspecialchars($postTODATE=$_POST["TODATE"]) : CommonUtils::getUserDate();     
      if (isset($_POST["WBSTATUS"])) $postWBSTATUS=$_POST["WBSTATUS"]; else $postWBSTATUS = '1'; 
      
      //Create new database object
     $dbConn = new dbConnect(); $dbConn->dbConnection();
     
     include_once($ROOT.$PHPFOLDER."DAO/transactionDAO.php");

     if (isset($_POST['select'])) {
       if (isset($_POST['finish'])) {
       	  $list =implode(",",$_POST['select']);
       	  $list2 = explode(",",$list);

       	  if ($list2[1] == null) {
       	  
              $sequenceDAO = new SequenceDAO($dbConn);
  				    $sequenceTO = new SequenceTO;
  				    $errorTO = new ErrorTO;
	  			    $sequenceTO->sequenceKey=LITERAL_SEQ_WAYBILL;
      	     	$sequenceTO->depotUId = $depotId;
      	    	$sequenceTO->principalUId = $principalId;
 	  			    $result=$sequenceDAO->getSequence($sequenceTO,$seqVal);
 	  			
 		  			  if ($result->type!=FLAG_ERRORTO_SUCCESS) {
		  		      return $result;
		        }
		      
		        $wayBillNumber  = $seqVal;
		                  
            include_once($ROOT.$PHPFOLDER."DAO/PostTransactionDAO.php");
            $postTransactionDAO = new PostTransactionDAO($dbConn);
            $rTO = $postTransactionDAO->setWaybillNumber($principalId, $list2[0], $wayBillNumber);
            
            if ($rTO->type==FLAG_ERRORTO_SUCCESS) {
               $dbConn->dbinsQuery("commit");
            }  else {
              $dbConn->dbinsQuery("rollback");
            ?>
                <script type='text/javascript'>parent.showMsgBoxInfo('Way Bill Not Created')</script>    
            <?php   
                return; 
                    }
                 }    
            ?>
             <script type='text/javascript'>parent.showMsgBoxInfo('Printing WayBill Completed')
                window.open('<?php echo $ROOT.$PHPFOLDER ?>functional/presentations/presentationManagement.php?TYPE=WAYBILL&FINDNUMBER=<?PHP echo $list2[0]; ?>');
             </script> 
            <?php 
            
            return;
        }
      }   

     if (isset($_POST['select'])) {
          
        $transactionDAO = new transactionDAO($dbConn);
        $mfTS = $transactionDAO->getWayBillsToPrint($principalId,$postFROMDATE,$postTODATE,$postWBSTATUS);
          if (sizeof($mfTS)==0) { ?>
 				    <center>
 				    <table>
 					     <tr>
                  <td colspan="5" style="border:1px solid black; border-collapse: collapse; text-align: center; font-weight: bold;">No Way Bills found To Print !!</td>            	
               </tr>	
    		    </table>
    		    </center>
         <?php 
           return;
         }
       	?>
		    <center>
          <FORM name='reprintts' method=post target=''>
            <Table style="border:1px solid black; border-collapse: collapse; float: center;">
             <tr>
               <td colspan="6" style="border:1px solid black; border-collapse: collapse; text-align: center; font-weight: bold;">Select WayBills to Print</td>            	
             </tr>	
             <tr>
               <th style="border:1px solid black; border-collapse: collapse; float: center;">Invoice Numberr</th>
               <th style="border:1px solid black; border-collapse: collapse; float: center;">Invoice Date</th>
               <th style="border:1px solid black; border-collapse: collapse; float: center;">Customer</th>
               <th style="border:1px solid black; border-collapse: collapse; float: center;">Tracking Number</th>
               <th style="border:1px solid black; border-collapse: collapse; float: center;">WayBill Number</th>
               <th style="border:1px solid black; border-collapse: collapse; float: center;">Select</th>
             </tr>
<?php
           $cl = "even";
           foreach ($mfTS as $row) {
              $cl = GUICommonUtils::styleEO($cl);
?>
              <tr class="<?php echo $cl; ?>">
                <td style="border:1px solid black; border-collapse: collapse; float: center;"><?php echo $row['document_number'];?></td>
                <td style="border:1px solid black; border-collapse: collapse; float: center;"><?php echo $row['invoice_date'];?></td>
                <td style="border:1px solid black; border-collapse: collapse; float: center;"><?php echo $row['bill_name'];?></td>
                <td style="border:1px solid black; border-collapse: collapse; float: center;"><?php echo $row['delivery_instructions'];?></td>
                <td style="border:1px solid black; border-collapse: collapse; float: center;"><?php echo $row['waybill_number'];?></td>
                <td style="border:1px solid black; border-collapse: collapse; float: center;"><INPUT TYPE="radio" name="select[]" value=" <?php echo($row['uid']. ',' .$row['waybill_number']);?>" ></td>
              </tr>

<?php 	} ?>              
             </table> 
              <br><br>
             <table> 
              <tr>
                <td colspan="5"></td>
        	      <td><INPUT TYPE="submit" class="submit" name="finish" value= "Print Selected Way Bills"></td>
              </tr>
             </table>
	     </center>
<?php
       return;
    }
$class = 'odd';
?>
<center>
	 <FORM name='Select Waybill' method=post action=''>
        <table width:"100%"; style="border:none">
        	<tr>
        		<td colspan"0"; style="font-weight:normal;font-size:2em;text-align:left"; font-family: Calibri, Verdana, Ariel, sans-serif; >Find WayBills to Print</td>
        		</tr>
        	<tr>
        		 <td>&nbsp</td>
        		</tr>	        	
        	<tr>
        		<td style="font-weight:normal; font-size:1em">Choose the required parameters</td>
        		</tr>        	
         </table>
        <table width:"30%"; >        	
          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
            <td>&nbsp</td>
            <td>&nbsp</td>
            <td>&nbsp</td>
            <td>&nbsp</td>
            <td>&nbsp</td>
          </tr>
        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
           <td style="text-align:left";>Start Processed Date : </td>
           <td colspan="2"; style="text-align:left"><?php DatePickerElement::getDatePickerLibs(); DatePickerElement::getDatePicker("FROMDATE",$postFROMDATE); ?> </td>
           <td>&nbsp</td>
           <td>&nbsp</td>
          </tr>
        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
           <td Colspan="5">&nbsp</td>
          </tr>
        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
           <td style="text-align:left";>End Processed Date : </td>
           <td colspan="2"><?php DatePickerElement::getDatePickerLibs(); DatePickerElement::getDatePicker("TODATE",$postTODATE); ?> </td>
           <td>&nbsp</td>
           <td>&nbsp</td>    
          </tr>         
        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
           <td Colspan="5">&nbsp</td>
          </tr> 
        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
          	<td colspan="5"; style="text-align:center;"><?php $lableArr = array('Un-Printed','Printed');
          		                                                $valueArr = array('1','2');
          		                                                BasicSelectElement::buildGenericDD('WBSTATUS', $lableArr,$valueArr, $postWBSTATUS, "N", "N", null, null, null);?>
           </td>
          </tr>
        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
           <td Colspan="5">&nbsp</td>
          </tr>
       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
        	 <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="select" value= "Get WayBills"></td>
         </tr>          
        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
           <td Colspan="5">&nbsp</td>
          </tr>  
 				</table>
		</form>
    </center> 
	</body>       
 </HTML>