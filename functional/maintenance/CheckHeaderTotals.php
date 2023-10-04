<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER."properties/Constants.php");
    include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
    include_once($ROOT.$PHPFOLDER."libs/CommonUtils.php");
    include_once($ROOT.$PHPFOLDER."DAO/MaintenanceDAO.php");	    
    include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
    include_once($ROOT.$PHPFOLDER."elements/datePickerElement.php");
		    
    if (!isset($_SESSION)) session_start() ;
      $userUId     = $_SESSION['user_id'] ;
      $principalId = $_SESSION['principal_id'] ;
      
      $postPrincipal   = (isset($_POST["Principal"])) ? htmlspecialchars($_POST["Principal"]) : '';
      $postFROMDATE      = (isset($_POST["FROMDATE"])) ? htmlspecialchars($postFROMDATE=$_POST["FROMDATE"]) : CommonUtils::getUserDate();     
 
      //Create new database object
      $dbConn  = new dbConnect(); 
      $dbConn->dbConnection();
      $errorTO = new ErrorTO;
      
      if (isset($_POST['canform'])) {
         return;    
      }

if (isset($_POST['firstform'])) {
	
        $MaintenanceDAO = new MaintenanceDAO($dbConn);
        $errorTO = $MaintenanceDAO->checkHeaderTotals($principalId,$postFROMDATE, '');
              
        if ($errorTO->type==FLAG_ERRORTO_SUCCESS) {
        ?>
            <script type='text/javascript'>parent.showMsgBoxInfo('Update Successful<BR><BR><?php echo $errorTO->description ?> <br>')</script> 
        <?php
        } else {
        ?>
            <script type='text/javascript'>parent.showMsgBoxError('Update Failed<BR><BR><?php echo $errorTO->description ?> <br>')</script> 
            <?php
        }        
        unset($_POST['firstform']);	      

}
?>
<!DOCTYPE html>
<HTML>
	<HEAD>

		<TITLE>Start Date Selection</TITLE>

		<link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/1_kwelanga.css' rel='stylesheet' type='text/css'>
		<script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/jquery.js"></script>
		<script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/dops_global_functions.js"></script>

    <style>
      td.head1 {font-weight:bold;
                font-size:17px;text-align:left; 
                font-family: Calibri, Verdana, Ariel, sans-serif; 
                padding: 0 150px 0 150px }
                
      td.det1  {border-style:none; 
                text-align: left; 
                font-weight: bold; 
                font-size: 13px;
                padding: 0 150px 0 150px  }
    	
    	</style>

		</HEAD>
    <body>
<?php
      
// ********************************************************************************************************************************************************      
     
if(!isset($_POST['firstform'])) {
	
   $class = 'odd';    
    
    ?>
    <center>
       <FORM name='Check Header Totals' method=post action=''>
            <table width="720"; style="border:none">
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td class=head1 colspan="5"; style="text-align:center";>Select Parameters</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td width="10%"; style="border:none">&nbsp</td>
                 <td width="30%"; style="border:none">&nbsp</td>
                 <td width="30%"; style="border:none">&nbsp</td>
                 <td width="20%"; style="border:none">&nbsp</td>
                 <td width="10%"; style="border:none">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="5">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                    <td>&nbsp</td>
                    <td class=det1; >Start Date </td>
                    <td colspan="2"><?php DatePickerElement::getDatePickerLibs(); DatePickerElement::getDatePicker("FROMDATE",$postFROMDATE); ?> </td>
                    <td colspan="2"; >&nbsp;</td>   
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="5">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="firstform" value= "Check Totals">
                                                             <INPUT TYPE="submit" class="submit" name="canform"   value= "Cancel"></td>
               </tr>          
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="5">&nbsp</td>
               </tr>  
 			    	</table>
		   </form>
    </center> 
<?php 
} ?>

	  </body>       
</HTML>
