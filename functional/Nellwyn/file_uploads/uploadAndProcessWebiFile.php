<?php
// https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/functional/nellwyn/file_uploads/uploadAndProcessWebiFile.php

    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
    include_once($ROOT.$PHPFOLDER."DAO/ProcessCheckersWebiFileDAO.php");
    include_once($ROOT.$PHPFOLDER."elements/datePickerElement.php");
    include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php'); 
        
    if (!isset($_SESSION)) session_start;
    $userId = $_SESSION['user_id'];

    $dbConn = new dbConnect();
    $dbConn->dbConnection();
    $errorTO = new ErrorTO;
    
if(isset($_POST['ccancel'])) { 
     unset($_POST['firstform']);	
}
if(isset($_POST['SELECT'])) {
      $errors= array();
      $file_name = $_FILES['UFILE']['name'];
      $file_tmp = $_FILES['UFILE']['tmp_name'];
      $file_type = $_FILES['UFILE']['type'];
      $file_ext=strtolower(end(explode('.',$_FILES['UFILE']['name'])));
      
      echo "<br>";
      echo $_POST['FROMDATE'];
      echo "<br>";      
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
     
      $ProcessCheckersWebiFileDAO = new ProcessCheckersWebiFileDAO($dbConn);
      $ProcessCheckersWebiFileDAO->dropTempWebiTable($userId);
      
      $ProcessCheckersWebiFileDAO = new ProcessCheckersWebiFileDAO($dbConn);
      $ProcessCheckersWebiFileDAO->createTempWebiTable($userId) ; 
      
      $ProcessCheckersWebiFileDAO = new ProcessCheckersWebiFileDAO($dbConn);
      $ProcessCheckersWebiFileDAO->uploadDataToWebi($file_name, $userId);
      
      $ProcessCheckersWebiFileDAO = new ProcessCheckersWebiFileDAO($dbConn);
      $webiData = $ProcessCheckersWebiFileDAO->formatWebiData();
      
      $wbDate = $_POST['FROMDATE'];
      
      foreach($webiData as $wrow) {
      	
      	print_r($wrow);
      
           $webiArray = array((explode(',', $wrow['Record'])));  
           
           foreach($webiArray as $fRow) {
           	
           	  if(substr($fRow[0],0,10) == 'Department') {
                   $artNo = substr($fRow[0], strpos($fRow[0],'Article:')+8,9);
           	  } else {
           	  	   if(trim($fRow[1]) <> '' && trim($fRow[1]) <> 'xSite') {
           	  	   	
           	  	   	     $ProcessCheckersWebiFileDAO = new ProcessCheckersWebiFileDAO($dbConn);
                         $errorTO = $ProcessCheckersWebiFileDAO->insertWebiRecord($wbDate, trim($fRow[1]), trim($artNo), trim($fRow[8]), trim($fRow[11]), trim($fRow[12]));        

                         if ($errorTO->type!=FLAG_ERRORTO_SUCCESS) { ?>
                                      <script type='text/javascript'>parent.showMsgBoxError('Bomb Out I<br>Contact Kwelanga Support')</script> 
                                      <?php
                                      print_r($errorTO);
                                      echo "PPP";
                                      return;
                          }       
                   }
                   echo $wbDate;
                   echo " - ";          	  	
                   echo $artNo;
                   echo " - ";
                   echo($fRow[1]);
                   echo " - ";
                   echo($fRow[8]);
                   echo " - ";
                   echo($fRow[11]);
                   echo " - ";
                   echo($fRow[12]);
                   echo "<br>";
          	  }
          }

      } ?>

        <script type='text/javascript'>parent.showMsgBoxInfo('Success')</script> 
        <?php
      
//      $ProcessCheckersWebiFileDAO = new ProcessCheckersWebiFileDAO($dbConn);
//      $ProcessCheckersWebiFileDAO->dropTempWebiTable($userId);
      unset($_POST['firstform']);	
      unset($_POST['SELECT']);
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
      
      td.det1  {border-style:none; 
      	        border-color:DarkGray; 
      	        border-width:1px;
      	        border-collapse:collapse; 
      	        text-align: center; 
      	        font-weight: bold; 
      	        font-size: 15px;}

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

  $postFROMDATE  = (isset($_POST["FROMDATE"])) ? htmlspecialchars($postFROMDATE=$_POST["FROMDATE"]) : CommonUtils::getUserDate();

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
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                   <td colspan="1">&nbsp</td>                           
                   <td class="det1" style="text-align:left; border: none;">Webi&nbsp;Date</td>
                   <td class="det1" colspan="1" style="text-align:left; border: none;"><?php DatePickerElement::getDatePickerLibs(); DatePickerElement::getDatePicker("FROMDATE",$postFROMDATE); ?> </td>
                   <td colspan="1" >&nbsp;</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                  <td colspan="4" >&nbsp;</td>
               </tr>


               <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                  <td colspan="4" >&nbsp;</td>
               </tr>	        	
 	
               <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
               	  <td>&nbsp</td>
                  <td class="det1" style="text-align:left";>Select Webi CSV file</td>
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