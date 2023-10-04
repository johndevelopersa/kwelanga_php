<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER."DAO/transactionDAO.php");
    include_once($ROOT.$PHPFOLDER."DAO/storeDAO.php");
    include_once($ROOT.$PHPFOLDER."properties/Constants.php");
		include_once($ROOT.$PHPFOLDER.'TO/SequenceTO.php');
		include_once($ROOT.$PHPFOLDER.'DAO/SequenceDAO.php');
		include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
		
?>
<!DOCTYPE html>
<HTML>
	<HEAD>

		<TITLE>Document Selection</TITLE>

		<link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/4_default.css' rel='stylesheet' type='text/css'>
		<script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/jquery.js"></script>
		<script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/dops_global_functions.js"></script>

		</HEAD>

<?php

    if (!isset($_SESSION)) session_start() ;
      $userUId = $_SESSION['user_id'] ;
      $principalId = $_SESSION['principal_id'] ;
      $depotId = $_SESSION['depot_id'] ;
      $systemId = $_SESSION["system_id"];
      $systemName = $_SESSION['system_name'];
      
      if (isset($_POST['area'])) $postarea = $_POST['area']; else $postarea="";
      if (isset($_POST['Days']))        $days            = $_POST['Days']; else $days="";
      
      //Create new database object
     $dbConn = new dbConnect(); $dbConn->dbConnection();

          if (isset($_POST['finish'])) {
       	 	if (isset($_POST['select'])) {
             $list = implode(",",$_POST['select']);

?>             
          <script type='text/javascript'>parent.showMsgBoxInfo('Trip Sheet Successfully Created')
             <?php 
               $ingarray = array('222');
               $honarray = array('230');
                if(in_array($depotId, $ingarray)) { ?>
                	  window.open('<?php echo $ROOT.$PHPFOLDER ?>functional/presentations/presentationManagement.php?TYPE=CUSTOMTS294&FINDNUMBER=<?PHP echo $list; ?>');
             <?php } 
                elseif(in_array($depotId, $honarray)) { ?>
                     window.open('<?php echo $ROOT.$PHPFOLDER ?>functional/presentations/presentationManagement.php?TYPE=CUSTOMTS305&FINDNUMBER=<?PHP echo $list; ?>');
             <?php } else { ?>
                     window.open('<?php echo $ROOT.$PHPFOLDER ?>functional/presentations/presentationManagement.php?TYPE=tripsheet&FINDNUMBER=<?PHP echo $list; ?>');
             <?php } ?>
           </script>       
<?php               
          }
         }
      
      if (isset($_POST['area']) && $postarea <> 'Select Area') {

          include_once($ROOT.$PHPFOLDER."DAO/transactionDAO.php");
          $transactionDAO = new transactionDAO($dbConn);
          $mfTS = $transactionDAO->getDocumentsForPicking($principalId, $depotId, $postarea);     
          
 if (sizeof($mfTS)==0) { ?>
 				<center>
 					<table>
 						  <tr>
               <td colspan="5" style="border:1px solid black; border-collapse: collapse; text-align: center; font-weight: bold;">No Trip Sheets Found !!</td>            	
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
               <td colspan="5" style="border:1px solid black; border-collapse: collapse; text-align: center; font-weight: bold;">Select trip Sheet to Re Print</td>            	
             </tr>	
             <tr>
               <th style="border:1px solid black; border-collapse: collapse; float: center;">Area</th>
               <th style="border:1px solid black; border-collapse: collapse; float: center;">Document Number</th>
               <th style="border:1px solid black; border-collapse: collapse; float: center;">Store</th>
               <th style="border:1px solid black; border-collapse: collapse; float: center;">Order Date</th>
               <th style="border:1px solid black; border-collapse: collapse; float: center;">Delivery date</th>
               <th style="border:1px solid black; border-collapse: collapse; float: center;">Quantity</th>
               <th style="border:1px solid black; border-collapse: collapse; float: center;">Select</th>
             </tr>
<?php
           $cl = "even";
           foreach ($mfTS as $row) {
              $cl = GUICommonUtils::styleEO($cl);
 ?>
              <tr class="<?php echo $cl; ?>">
                <td style="border:1px solid black; border-collapse: collapse; float: center;"><?php echo $row['description'];?></td>
                <td style="border:1px solid black; border-collapse: collapse; float: center;"><?php echo $row['document_number'];?></td>
                <td style="border:1px solid black; border-collapse: collapse; float: center;"><?php echo $row['deliver_name'];?></td>
                <td style="border:1px solid black; border-collapse: collapse; float: center;"><?php echo $row['order_date'];?></td>
                <td style="border:1px solid black; border-collapse: collapse; float: center;"><?php echo $row['due_delivery_date'];?></td>
                <td style="border:1px solid black; border-collapse: collapse; float: center;"><?php echo $row['cases'];?></td>
      					<td style="border:1px solid black; border-collapse: collapse; float: center;"><INPUT TYPE="checkbox" name="select[]" value= "<?php echo $row['docuid'];?>"></TD>

              </tr>
<?php 	} ?>              
             </table> 
              <br><br>
             <table> 
              <tr>
                <td colspan="5"></td>
        	      <td><INPUT TYPE="submit" class="submit" name="finish" value= "Print Selected Trip Sheets"></td>
              </tr>
             </table>
	     </center>
<?php

            return;
       }

     include_once($ROOT.$PHPFOLDER."DAO/storeDAO.php");
     $storeDAO = new StoreDAO($dbConn);
     $mfTS = $storeDAO->getPrincipalAreas($principalId);

?>
		<BODY>
		<center>
			<FORM name='Select Documents for Picking' method=post action=''>
        <TABLE style="border: none";>
        <tr>
        	<td Colspan="3">&nbsp&nbsp&nbsp&nbsp&nbsp</td>
       	</tr>

      	<table>
           <tr>
           <th>Select Area</th>
           </tr>
           <tr>
              <td>
              	 <select name="area" id="area">
              			 <option value="Select Area">Select Area</option>
              			<?php foreach($mfTS as $row) { ?>
              					<option value="<?php echo $row['uid']; ?>"><?php echo $row['description']; ?></option>
              			<?php } ?>
              		</select>
              </td>
           </tr>
        </table>
        <br>
        <tr>
          <td colspan="5">&nbsp</td>	
        	</tr>
       <tr>
        	<td colspan="3"></td>
        	<td><INPUT TYPE="submit" class="submit" name="finish" value= "Documents for Picking"></td>
        </tr>
				</TABLE>
		</form>
    </center>    
 </HTML>
 
 <?php

function tripsheetlist() {
	
	echo transporter;
	
}	
