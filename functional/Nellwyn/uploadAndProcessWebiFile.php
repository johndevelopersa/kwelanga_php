<?php
// https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/functional/nellwyn/file_uploads/uploadAndProcessWebiFile.php

    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
    include_once($ROOT.$PHPFOLDER."DAO/ProcessSalesFileDAO.php");
    
    if (!isset($_SESSION)) session_start;
    $userId = $_SESSION['user_id'];

    $dbConn = new dbConnect();
    $dbConn->dbConnection();
if(isset($_POST['ccancel'])) { 
     unset($_POST['firstform']);	
}
if(isset($_POST['SELECT'])) {
      $errors= array();
      $file_name = $_FILES['UFILE']['name'];
      $file_tmp = $_FILES['UFILE']['tmp_name'];
      $file_type = $_FILES['UFILE']['type'];
      $file_ext=strtolower(end(explode('.',$_FILES['UFILE']['name'])));
      
      $extensions= array("csv");
      
      if(in_array($file_ext,$extensions)=== false){
         $errors[]="Extension not allowed, Please choose a csv file.";
      }
      if(empty($errors)==true) {
         move_uploaded_file($file_tmp, $ROOT."ftp/pnpsales/".$file_name);
      }else{ ?>
      	<script type='text/javascript'>
                 parent.showMsgBoxError('<?php echo $errors[0];?>')</script> 
      <?php 
      }
/*      
      $ProcessSalesFileDAO = new ProcessSalesFileDAO($dbConn);
      $ProcessSalesFileDAO->dropTempSalesTable($userId);
      
      $ProcessSalesFileDAO = new ProcessSalesFileDAO($dbConn);
      $ProcessSalesFileDAO->createTempSalesTable($userId) ; 
      
      $ProcessSalesFileDAO = new ProcessSalesFileDAO($dbConn);
      $ProcessSalesFileDAO->uploadDataToSales($file_name, $userId);
      
      $ProcessSalesFileDAO = new ProcessSalesFileDAO($dbConn);
      $ProcessSalesFileDAO->updateTempFilePrice($userId);

      $ProcessSalesFileDAO = new ProcessSalesFileDAO($dbConn);
      $ProcessSalesFileDAO->extractRawData($userId);

      $ProcessSalesFileDAO = new ProcessSalesFileDAO($dbConn);
      $ProcessSalesFileDAO->extractComReport($userId);
*/      
      return;
      unset($_POST['firstform']);	
}



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

if(!isset($_POST['firstform'])) { 
?>
    <center>	
        <FORM name='Select Invoice' method=post action='' enctype="multipart/form-data">
            <table width="50%"; style="border-style:none";>        	
               <tr>
                  <td width="1%;>&nbsp</td>
                  <td width="28%;>&nbsp</td>
                  <td width="70%;>&nbsp</td>
                  <td width="1%;>&nbsp</td>
               </tr>
               <tr>
                  <td class=head1 colspan="4"  style="text-align:center;" >Upload and Process Webi file</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                  <td colspan="4" >&nbsp;</td>
               </tr>	        
               <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                  <td colspan="4" >&nbsp;</td>
               </tr>	        	
 	
               <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
               	  <td>&nbsp</td>
                  <td style="text-align:left";>Select Webi CSV file</td>
                  <td style="text-align:left";><input type="file" name="UFILE"></td>
                  <td>&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                  <td colspan="4" >&nbsp;</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                  <td colspan="4" >&nbsp;</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
               	  <td>&nbsp</td>
                  <td colspan="2"  style="text-align:center;"><INPUT TYPE="submit" class="submit" name="SELECT" value= "Upload and Process File">
                  	                                          <INPUT TYPE="submit" class="submit" name="CCANCEL" value= "Start Again"></td>
                  <td>&nbsp</td>	                                          
               </tr>          
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                  <td colspan="4" >&nbsp;</td>
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