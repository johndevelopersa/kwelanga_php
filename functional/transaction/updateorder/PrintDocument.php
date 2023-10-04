<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER."DAO/ManageOrdersDAO.php");
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

     td.det2  {border-style:solid solid solid solid; 
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

      //Create new database object
      $dbConn = new dbConnect(); $dbConn->dbConnection();
      
      if (isset($_POST["docNum"])) $postDocNum=test_input($_POST["docNum"]); else $postDocNum = ''; 
      if (isset($_POST["storeName"])) $postStoreName=test_input($_POST["storeName"]); else $postStoreName = '';
      if (isset($_POST["Days"])) $postinterval=$_POST["Days"]; else $postinterval = '';
      if (isset($_POST["DocType"])) $postdocType=$_POST["DocType"]; else $postdocType = '';
      
      if (isset($_POST['canform'])) {
         return;	
      }
      if (isset($_POST['finishform'])) {
          $list = implode(",",$_POST['select']);
          foreach($_POST['select'] as $arow){
             $docTyp = substr($arow,strpos($arow,'-')+1,strpos($arow,'&') - strpos($arow,'-')-1);
             $fNum   = substr($arow,0,strpos($arow,'-'));

             ?>
             <script type='text/javascript'>
                window.open('<?php echo $ROOT.$PHPFOLDER ?>functional/presentations/presentationManagement.php?TYPE={<?php echo $docTyp; ?>}&FINDNUMBER=<?PHP echo $fNum; ?>','<?PHP echo $fNum; ?>','scrollbars=yes,width=750,height=600,resizable=yes', 'false');
             </script>
             <?php	
          }
          ?>
          <script type='text/javascript'>
                 parent.showMsgBoxInfo('Printing Complete ')</script> 
          <?php 
            unset($_POST['finishform']);
            unset($_POST['firstform']);          
      }
     if (isset($_POST['firstform'])) {
          if (trim($postDocNum) . trim($postStoreName) !== '' ) {
                $ManageOrdersDAO = new ManageOrdersDAO($dbConn);
                $mfDDU = $ManageOrdersDAO->getListOfDocumentsToPrint($principalId, $postDocNum, $postStoreName, $postinterval, $postdocType);
               if (sizeof($mfDDU)>0) { ?>
                       <center>	
                          <form name='Select Invoice' method=post action=''>
                             <table width="95%"; style="border:none">        	
                                 <tr>
                                    <td class=head1 colspan="10"; style="text-align:center;" >Select Documents to Print</td>
                                 </tr>
                                 <tr>
                                    <td class=head1 colspan="10"; style="text-align:center;" >&nbsp</td>
                                 </tr>
                                 <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                                     <td width="2%;" style="border:none" >&nbsp</td>
                                     <td width="15%;" style="font-weight:bold; text-align:left; ">Document Number</td>
                                     <td width="30%;" style="font-weight:bold; text-align:left; ">Customer Name</td>
                                     <td width="10%;" style="font-weight:bold; text-align:left; ">Type</td>
                                     <td width="14%;" style="font-weight:bold; text-align:left; ">Status</td>
                                     <td width="7%;"  style="font-weight:bold; text-align:left; ">Date</td>
                                     <td width="7%;"  style="font-weight:bold; text-align:right; ">Cases</td>
                                     <td width="9%;"  style="font-weight:bold; text-align:right; ">Excl. Value</td>
                                     <td width="3%;"  style="font-weight:bold; text-align:center; ">Select</td>
                                     <td width="2%;" >&nbsp;</td>
                                 </tr>
                                 <?php 
                                 foreach ($mfDDU as $row) { ?> 
                                       <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                                          <td >&nbsp;</td>
                                          <td ><?php echo trim($row['document_number'],'0');?></td>
                                          <td ><?php echo $row['deliver_name'];?></td>
                                          <td ><?php echo $row['Type'];?></td>
                                          <td ><?php echo $row['Status'];?></td>
                                          <td ><?php echo $row['invoice_date'];?></td>
                                          <td style="text-align:right;"><?php echo $row['cases'];?></td>
                                          <td style="text-align:right;"><?php echo number_format($row['exclusive_total'],2, "."," "); ?></td>
                                          <td style="float: center;"><INPUT TYPE="checkbox" name="select[]" value= "<?php echo $row['uid'] .'-' . $row['Type'] .'&' . $row['StatusUid'];?>"><br></td>
                                          <td>&nbsp;</td>
                                       </tr>
                                <?php
                                }   ?>   
                                <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                                     <td >&nbsp;</td>
                                     <td >&nbsp;</td>
                                     <td >&nbsp;</td>
                                     <td >&nbsp;</td>
                                     <td >&nbsp;</td>
                                     <td >&nbsp;</td>
                                     <td >&nbsp;</td>
                                     <td >&nbsp;</td>
                                     <td >&nbsp;</td>
                                     <td >&nbsp;</td>
                                </tr>
                                <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                    <td colspan="10"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="finishform" value= "Print Selected">
                                                                                <INPUT TYPE="submit" class="submit" name="canform"   value= "Cancel"></td>
                                </tr>=
                             </table>
                          </form>
                       </centre>
                     
                <?php 
                } else { ?>
                    <script type='text/javascript'>parent.showMsgBoxInfo('No Documents found')</script> 
                <?php
                    unset($_POST['firstform']);
                }
          }  else { ?>
              <script type='text/javascript'>parent.showMsgBoxInfo('Select either Document Number or Part of a Store Name')</script> 
          <?php
              unset($_POST['firstform']);     	
          }
     } 
if(!isset($_POST['firstform']) && !isset($_POST['finishform'])) { ?>
  <center>	
     <form name='Select Invoice' method=post action=''>
        <table width="55%"; style="border:none">        	
           <tr>
               <td class=head1 colspan="5"; style="text-align:center;" >Search for a Document</td>
           </tr>
           <tr>
               <td class=head1 colspan="5"; style="text-align:center;" >&nbsp</td>
           </tr>
           <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
              <td width="1%;" style="border:none" >&nbsp</td>
              <td width="20%;" style="font-weight:bold; text-align:left; ">Document Number</td>
              <td width="38%;" style="font-weight:bold; text-align:left; ">Customer Name</td>
              <td width="20%;" style="font-weight:bold; text-align:left; ">Document Type</td>
              <td width="20%;" style="font-weight:bold; text-align:left; ">Interval</td>
              <td width="1%;" >&nbsp</td>
           <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
              <td colspan="6" >&nbsp</td>
           </tr>	
           <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
              <td>&nbsp;</td>
              <td><input type="text" name="docNum" placeholder="Enter Document Number" height="500" ></td>
              <td><input type="text" name="storeName" placeholder="Enter Customer Name"></td>
              <td style="text-align:left";>  <select name="DocType">
                               <option value="1,6,13,4">Orders/Invoice/Del.Note/Credits</option>
                               <option value="1,6,13">Orders/Invoices/Del.Note Only</option>
                               <option value="4">Credits Only</option>
                               <option value="5,21,22">Arrivals/Stock Movements</option>
                               <option value="23">AOD's</option>
                            </select></td>
              <td style="text-align:left";>  <select name="Days">
                               <option value="30">30 Days</option>
                               <option value="7">7 Days</option>
                               <option value="60">60 Days</option>
                               <option value="90">90 Days</option>
                               <option value="180">6 Months</option>
                               <option value="365">1 Year</option>
                            </select></td>
              <td>&nbsp;</td>
           </tr> 
           <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
              <td colspan="6" >&nbsp</td>
           </tr> 
  
           <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
              <td colspan="6";>&nbsp</td>
           </tr> 
           <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
              <td colspan="6"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="firstform" value= "Get Document Details">
                                                          <INPUT TYPE="submit" class="submit" name="canform"   value= "Cancel"></td>
           </tr>          
           <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
              <td colspan="6";>&nbsp</td>
           </tr>  
        </table>
     </form>
  </center> 
<?php 
}
?>  
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