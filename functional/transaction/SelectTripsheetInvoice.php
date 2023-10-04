<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER."DAO/TransactionDAO.php");
    include_once($ROOT.$PHPFOLDER."properties/Constants.php");
		include_once($ROOT.$PHPFOLDER.'TO/SequenceTO.php');
		include_once($ROOT.$PHPFOLDER.'DAO/SequenceDAO.php');
		include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');

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
		<center>
		<form name='Invoices' method=post target=''>
<?php

    if (!isset($_SESSION)) session_start() ;
      $userUId     = $_SESSION['user_id'] ;
      $principalId = $_SESSION['principal_id'] ;
      $depotId     = $_SESSION['depot_id'] ;
      $systemId    = $_SESSION["system_id"];
      $systemName  = $_SESSION['system_name'];

//Create new database object
     $dbConn = new dbConnect(); $dbConn->dbConnection();

     include_once($ROOT.$PHPFOLDER."DAO/TransactionDAO.php");
     $transactionDAO = new TransactionDAO($dbConn);

     $mfDD = $transactionDAO->getTripSheetInvoices($depotId, $principalId);
     
     include_once($ROOT.$PHPFOLDER."DAO/TransactionDAO.php");
     $transactionDAO = new TransactionDAO($dbConn);
     $mfTS = $transactionDAO->getTripSheetTransporter($depotId);
     
     $finalResult = new ErrorTO;

?>

			<Table style="border:1px solid black; border-collapse: collapse; float: center;">
			  <tr>
				<TH style="border:1px solid black; border-collapse: collapse; float: center;">Principal</TH>
				<TH style="border:1px solid black; border-collapse: collapse; float: center;">Delivery Area</TH>
				<TH style="border:1px solid black; border-collapse: collapse; float: center;">Document Number</TH>
			  <TH style="border:1px solid black; border-collapse: collapse; float: center;">Document Type</TH>
			  <TH style="border:1px solid black; border-collapse: collapse; float: center;">Document Status</TH>
			  <TH style="border:1px solid black; border-collapse: collapse; float: center;">Invoice Date</TH>
			  <TH style="border:1px solid black; border-collapse: collapse; float: center;">Delivery Point</TH>
			  <TH style="border:1px solid black; border-collapse: collapse; float: center;">Cases</TH>
			  <TH style="border:1px solid black; border-collapse: collapse; float: center;">Weight</TH>
			  <TH style="border:1px solid black; border-collapse: collapse; float: center;">Select</TH>
			  </tr>


<?php
    $cl = "even";
       foreach ($mfDD as $row) {
         $cl = GUICommonUtils::styleEO($cl);
 ?>
				<TR class="<?php echo $cl; ?>">
					<TD style="border:1px solid black; border-collapse: collapse; float: center;"><?php echo $row['Principal'];?></TD>
					<TD style="border:1px solid black; border-collapse: collapse; float: center;"><?php echo $row['Area'];?></TD>
					<TD style="border:1px solid black; border-collapse: collapse; float: center;"><?php echo $row['Docno'];?></TD>
					<TD style="border:1px solid black; border-collapse: collapse; float: center;"><?php echo $row['Dtype'];?></TD>
					<TD style="border:1px solid black; border-collapse: collapse; float: center;"><?php echo $row['Dstatus'];?></TD>
					<TD style="border:1px solid black; border-collapse: collapse; float: center;"><?php echo $row['Invoice Date'];?></TD>
					<TD style="border:1px solid black; border-collapse: collapse; float: center;"><?php echo $row['Store'];?></TD>
					<TD style="border:1px solid black; border-collapse: collapse; float: center;"><?php echo $row['Cases'];?></TD>
					<TD style="border:1px solid black; border-collapse: collapse; float: center;"><?php echo $row['Weight'];?></TD>
					<TD style="border:1px solid black; border-collapse: collapse; float: center;"><INPUT TYPE="checkbox" name="select[]" value= "<?php echo $row['dm_uid'];?>"></TD>
					</TR>
<?php 	} ?>
				</Table>
        <TABLE style="border: none";>
        <TR>
        	<TD colspan="3">&nbsp&nbsp&nbsp&nbsp&nbsp</TD>
       	</TR>

      	<table>
           <tr>
           <th>Select Transporter</th>
           </tr>
           <tr>
              <td>
              	 <select name="Transporter" id="transporter">
              			 <option value="Select Transporter">Select Transporter</option>
              			<?php foreach($mfTS as $row) { ?>
              					<option value="<?php echo $row['uid']; ?>"><?php echo $row['name']; ?></option>
              			<?php } ?>
              		</select>
              </td>
           </tr>
        </table>
        <TR>
        	<TD colspan="3"></TD>
        	<TD><INPUT TYPE="submit" class="submit" name="finish" value= "Add to Trip Sheet"></TD>
        </TR>
				</TABLE>
		</form>
<?php
       if (isset($_POST['finish'])) {
       	 	if (isset($_POST['select'])) {
       				$seqVal="000000";
							$list = implode(",",$_POST['select']);
 							$sequenceDAO = new SequenceDAO($dbConn);
							$sequenceTO = new SequenceTO;
							$errorTO = new ErrorTO;
						 	$sequenceTO->sequenceKey=LITERAL_SEQ_TRIPSHEET;
						 	
						 	echo $depotId;
						 	if($depotId == 244) {
						 		  $sequenceTO->depotUId = 230;
						 	} else {
						 	    $sequenceTO->depotUId = $depotId;	
						 	}
    					$sequenceTO->depotUId = $depotId;
 							$result=$sequenceDAO->getSequence($sequenceTO,$seqVal);
							if ($result->type!=FLAG_ERRORTO_SUCCESS) { ?>
								<script type='text/javascript'>parent.showMsgBoxInfo('Trip Sheet Sequence not set up')</script>  
							<?php
							
							     return $result;
		          }
		          
	            $list             = implode(",",$_POST['select']);
	            $transporterID    = $_POST['Transporter'];		            
	            $tripSheetNumber  = $seqVal;
              $tripSheetDate    = date("Y-m-d") ;
	            $tripSheetUser    = $userUId;
	                          
              include_once($ROOT.$PHPFOLDER."DAO/PostTransactionDAO.php");
              $postTransactionDAO = new PostTransactionDAO($dbConn);
              $rTO = $postTransactionDAO->setTripsheetDetails($list, $transporterID, $tripSheetNumber, $tripSheetDate, $tripSheetUser,$_POST['select'] );
              
              if ($rTO->type==FLAG_ERRORTO_SUCCESS) {
                 $dbConn->dbinsQuery("commit");
              ?>
                 <script type='text/javascript'>parent.showMsgBoxInfo('Trip Sheet Successfully Created')
                    <?php 
                    $ingarray = array('222','179');
                    $honarray = array('230', '244');
                                   $cleararray = array('163','186','236');
  
                    if(in_array($depotId, $ingarray)) { ?>
                        window.open('<?php echo $ROOT.$PHPFOLDER ?>functional/presentations/presentationManagement.php?TYPE=CUSTOMNTS294&FINDNUMBER=<?PHP echo $tripSheetNumber; ?>');
                    <?php } 
                    elseif(in_array($depotId, $honarray)) { ?>
                        window.open('<?php echo $ROOT.$PHPFOLDER ?>functional/presentations/presentationManagement.php?TYPE=CUSTOMTS305&FINDNUMBER=<?PHP echo $tripSheetNumber; ?>');
                    <?php } 
                    elseif(in_array($depotId, $cleararray)) { ?>
                        window.open('<?php echo $ROOT.$PHPFOLDER ?>functional/presentations/presentationManagement.php?TYPE=CUSTOMTS216&FINDNUMBER=<?PHP echo $tripSheetNumber; ?>');
                    <?php }                    
                    else { ?>
                        window.open('<?php echo $ROOT.$PHPFOLDER ?>functional/presentations/presentationManagement.php?TYPE=tripsheet&FINDNUMBER=<?PHP echo $seqVal; ?>');
                    <?php } ?>
                 </script> 
              <?php
                 unset($mfDD);
                 unset($mfTS) ;              
                 return;  
              }  else { 
                     $dbConn->dbinsQuery("rollback");
             ?>
                 <script type='text/javascript'>parent.showMsgBoxInfo('Trip Sheet Not Created')</script>    
              <?php }
            
            }
         }   
     ?>
	 </body>
 </HTML>

