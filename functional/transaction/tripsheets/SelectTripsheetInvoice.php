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
		<link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/kos_standard_screen.css' rel='stylesheet' type='text/css'>
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
     
     $cl = "odd";

?>

      <table style="border:1px solid black; border-collapse: collapse; float: center;">
           <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                <td class="det1" colspan="10" style="text-align:center;">Select Invoices for Tripsheet</td>
           </tr>    
           <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                <td colspan="10" style="text-align:center;">&nbsp;</td>
           </tr>
           <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
               <td class="head1" style="text-align:center;">Principal</td>
               <td class="head1" style="text-align:center;">Area</td>
               <td class="head1" style="text-align:center;">Doc No</td>
               <td class="head1" style="text-align:center;">Inv Date</th>
               <td class="head1" colspan="2" style="text-align:center;">Store</th>
               <td class="head1" style="text-align:center;">Cases</th>
               <td class="head1" style="text-align:center;">Weight</th>
               <td class="head1" style="text-align:center;">Select</th>
           </tr>
           <?php
           foreach ($mfDD as $row) {?>
              <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                   <td class="detN12" style="text-align:center;"><?php echo $row['Principal'];?></td>
                   <td class="detN12" style="text-align:center;"><?php echo $row['W_Area'];?></td>
                   <td class="detN12" style="text-align:center;"><?php echo $row['Docno'];?></td>
                   <td class="detN12" style="text-align:center;"><?php echo $row['Invoice Date'];?></td>
                   <td class="detN12" colspan="2" style="text-align:center;"><?php echo $row['Store'];?></td>
                   <td class="detN12" style="text-align:center;"><?php echo $row['Cases'];?></td>
                   <td class="detN12" style="text-align:center;"><?php echo $row['Weight'];?></td>
                   <td class="detN12" style="text-align:center;"><INPUT TYPE="checkbox" name="select[]" value= "<?php echo $row['dm_uid'];?>"></td>

              </TR>
           <?php 	
           } ?>
           <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                <td colspan="10" style="text-align:center;">&nbsp;</td>
           </tr>
           <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                <td colspan="3" style="text-align:center;">&nbsp;</td>
                <td class="det1" colspan="3" style="text-align:center;">Select Transporter</td>                
                <td colspan="4" style="text-align:center;">&nbsp;</td>
           </tr>
           <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                <td colspan="10" style="text-align:center;">&nbsp;</td>
           </tr>
          <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                <td colspan="3" style="text-align:center;">&nbsp;</td>
                <td colspan="4">
                   <select name="Transporter" id="transporter">
                       <option value="Select Transporter">Select Transporter</option>
                       <?php foreach($mfTS as $row) { ?>
                           <option value="<?php echo $row['uid']; ?>"><?php echo $row['name']; ?></option>
                       <?php } ?>
                   </select>
                <td colspan="3" style="text-align:center;">&nbsp;</td>
           </tr>
           <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                <td colspan="10" style="text-align:center;">&nbsp;</td>
           </tr>
           <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                <td colspan="3" style="text-align:center;">&nbsp;</td>
                <td colspan="4"><INPUT TYPE="submit" class="submit" name="finish" value= "Add to Trip Sheet"></td>
                <td colspan="3" style="text-align:center;">&nbsp;</td>
           </tr>
       </table>
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
              $tripSheetDate    = date("Y-m-d H:i:s") ;
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

