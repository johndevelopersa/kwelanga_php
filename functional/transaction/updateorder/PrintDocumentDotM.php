<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER."DAO/ManageOrdersDAO.php");
    include_once($ROOT.$PHPFOLDER."properties/Constants.php");
		include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
    include_once($ROOT.$PHPFOLDER.'DAO/ManageOrdersDAO.php');
    include_once($ROOT.$PHPFOLDER."DAO/PostTransactionDAO.php");  
    include_once($ROOT.$PHPFOLDER."elements/datePickerElement.php");
    
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
      if (isset($_POST["chainName"])) $postChainName=test_input($_POST["chainName"]); else $postChainName = '';
      if (isset($_POST["Printed"])) $postPrinted=$_POST["Printed"]; else $postPrinted = '';
      if (isset($_POST["PrintType"])) $postPrintType=$_POST["PrintType"]; else $postPrintType = '';
      $postFROMDATE = (isset($_POST["FROMDATE"])) ? htmlspecialchars($_POST["FROMDATE"]) :  CommonUtils::getUserDate();
      
      if (isset($_POST['canform'])) {
            unset($_POST['finishform']);
            unset($_POST['firstform']);  	
      }
      if (isset($_POST['finishform'])) {
          $list = implode(",",$_POST['select']);
          $ptype = $_POST['pType'];
          foreach($_POST['select'] as $arow){
          	          	
             $docTyp = substr($arow,strpos($arow,'-')+1,strpos($arow,'&') - strpos($arow,'-')-1);
             $docNum = substr($arow,strpos($arow,'*')+1,8);
             $fNum   = substr($arow,0,strpos($arow,'-'));
             if($ptype == 1) { ?>
                 <script type='text/javascript'>
                    window.open('<?php echo $ROOT.$PHPFOLDER ?>functional/presentations/presentationManagement.php?TYPE=RICHDOC&FINDNUMBER=<?PHP echo $fNum; ?>','<?PHP echo $fNum; ?>','scrollbars=yes,width=750,height=600,resizable=yes', 'false');
                 </script>
             <?php
            } else { ?>
                 <script type='text/javascript'>
                    window.open('<?php echo $ROOT.$PHPFOLDER ?>functional/presentations/presentationManagement.php?TYPE={<?php echo $docTyp; ?>}&FINDNUMBER=<?PHP echo $fNum; ?>','<?PHP echo $fNum; ?>','scrollbars=yes,width=750,height=600,resizable=yes', 'false');
                 </script>           	
           	<?php
            }	
            $postTransactionDAO = new PostTransactionDAO($dbConn);
            $mCopy = $postTransactionDAO->UpdateDocumentCopies(mysqli_real_escape_string($dbConn->connection, $fNum));

          }
          ?>
          <script type='text/javascript'>
                 parent.showMsgBoxInfo('Printing Complete ')</script> 
          <?php 
            
          
            unset($_POST['finishform']);
            unset($_POST['firstform']);          
      }
     if (isset($_POST['firstform'])) {
                $ManageOrdersDAO = new ManageOrdersDAO($dbConn);
                $mfDDU = $ManageOrdersDAO->getListOfGDSDocumentsToPrint($principalId, $userUId, $postDocNum, $postChainName, $postPrinted, $postFROMDATE);
                
            //    print_r($mfDDU);
                if (sizeof($mfDDU)>0) { ?>
                       <center>	
                          <form name='Select Invoice' method=post action='' >
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
                                     <td width="28%;" style="font-weight:bold; text-align:left; ">Customer Name</td>
                                     <td width="10%;" style="font-weight:bold; text-align:left; ">Type</td>
                                     <td width="7%;" style="font-weight:bold; text-align:left; ">Status</td>
                                     <td width="7%;" style="font-weight:bold; text-align:left; ">Copies</td>
                                     <td width="10%;"  style="font-weight:bold; text-align:left; ">Date</td>
                                     <td width="7%;"  style="font-weight:bold; text-align:right; ">Cases</td>
                                     <td width="9%;"  style="font-weight:bold; text-align:right; ">Excl. Value</td>
                                     <td width="3%;"  style="font-weight:bold; text-align:center; ">Select</td>
                                     <td width="2%;" >&nbsp;</td>
                                 </tr>
                                 <?php 
                                 foreach ($mfDDU as $row) {
                                 	     if($row['captured_by'] == 'RICH') {
                                 	    	  $docNo = trim($row['client_document_number']);
                                 	     } else {
                                 	        $docNo = ltrim($row['document_number'],'0');
                                 	     } ?>                                 	     
                                       <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                                          <td ><input type="hidden" id="pType" name="pType" value=<?php echo $postPrintType; ?></td>
                                          <td ><?php echo $docNo;?></td>
                                          <td ><?php echo $row['deliver_name'];?></td>
                                          <td ><?php echo $row['Type'];?></td>
                                          <td ><?php echo $row['Status'];?></td>
                                          <td ><?php echo $row['copies'];?></td>
                                          <td ><?php echo $row['invoice_date'];?></td>
                                          <td style="text-align:right;"><?php echo $row['cases'];?></td>
                                          <td style="text-align:right;"><?php echo number_format($row['exclusive_total'],2, "."," "); ?></td>
                                          <td style="float: center;"><INPUT TYPE="checkbox" name="select[]" value= "<?php echo $row['uid'] .'-' . $row['Type'] .'&' . $row['StatusUid'] .'*'. $row['document_number'] ;?>"><br></td>
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
                                     <td >&nbsp;</td>
                                </tr>
                                <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                    <td colspan="11"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="finishform" value= "Print Selected">
                                                                                <INPUT TYPE="submit" class="submit" name="canform"   value= "Cancel"></td>
                                </tr>
                             </table>
                          </form>
                       </centre>
                     
                <?php 
                } else { ?>
                    <script type='text/javascript'>parent.showMsgBoxInfo('No Documents found')</script> 
                <?php
                    unset($_POST['firstform']);
                }
     } 
if(!isset($_POST['firstform']) && !isset($_POST['finishform'])) { 
	
	 $ManageOrdersDAO = new ManageOrdersDAO($dbConn);
   $mfCHL = $ManageOrdersDAO->getListOfChains($principalId);
	?>
  <center>	
     <form name='Select Invoice' method=post action=''>
        <table width="65%"; style="border:none">        	
           <tr>
               <td class=head1 colspan="5"; style="text-align:center;" >Search for a Document</td>
           </tr>
           <tr>
               <td class=head1 colspan="5"; style="text-align:center;" >&nbsp</td>
           </tr>
           <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
              <td width="1%;" style="border:none" >&nbsp</td>
              <td width="18%;" style="font-weight:bold; text-align:left; ">Invoice Date</td>
              <td width="20%;" style="font-weight:bold; text-align:left; ">Document Number</td>
              <td width="20%;" style="font-weight:bold; text-align:left; ">Chain</td>
              <td width="20%;" style="font-weight:bold; text-align:left; ">Printed</td>
              <td width="20%;" style="font-weight:bold; text-align:left; ">Output</td>
              <td width="1%;" >&nbsp</td>
           <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
              <td colspan="7" >&nbsp</td>
           </tr>	
           <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
              <td>&nbsp;</td>
              <td style="text-align:left"><?php DatePickerElement::getDatePickerLibs(); DatePickerElement::getDatePicker("FROMDATE",$postFROMDATE); ?> </td>
              <td><input type="text" style="width: 200px; height: 17px" name="docNum" placeholder="Document Number or Blank for All" ></td>
              <td>
                  <select name="chainName" id="chainName">
                         <?php foreach($mfCHL as $row) { ?>
                              <option value="<?php echo trim($row['uid']); ?>"><?php echo trim($row['description']); ?></option>
                         <?php } ?>
                   </select>
              </td>
              <td style="text-align:left";>  
              	  <select name="Printed">
                     <option value="2">No</option>
                     <option value="1">Yes</option>
                  </select>
              </td>
              <td style="text-align:left";>  
              	  <select name="PrintType">
                     <option value="2">PDF Format</option>
                     <option value="1">Dot Matrix</option>
                  </select>
              </td>
              <td colspan="1" >&nbsp</td>
           </tr> 
           <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
              <td colspan="7" >&nbsp</td>
           </tr> 
           <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
              <td colspan="7";>&nbsp</td>
           </tr> 
           <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
              <td colspan="7"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="firstform" value= "Get Document Details">
                                                          <INPUT TYPE="submit" class="submit" name="canform"   value= "Cancel"></td>
           </tr>          
           <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
              <td colspan="7";>&nbsp</td>
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