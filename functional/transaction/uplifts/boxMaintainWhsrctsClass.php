<?php
    include_once('ROOT.php'); 
    include_once($ROOT.'PHPINI.php');
    include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
    //include_once('boxCaptureWarehouseReceiptsClass.php');
    include_once($ROOT.$PHPFOLDER.'DAO/AgedStockDAO.php');
    include_once($ROOT.$PHPFOLDER."libs/GUICommonUtils.php");
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');    
    
    if (!isset($_SESSION)) session_start() ;
      $userUId     = $_SESSION['user_id'] ;
      $principalId = $_SESSION['principal_id'] ;
      

      //Create new database object
      $dbConn  = new dbConnect(); 
      $dbConn->dbConnection();
      $errorTO = new ErrorTO;
      $class = 'odd';
?>

<!DOCTYPE html>
<HTML>
   <HEAD>

		<TITLE>Simple Form</TITLE>

    <link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/kos_standard_screen.css' rel='stylesheet' type='text/css'>
		<link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/1_kwelanga.css' rel='stylesheet' type='text/css'>
		<script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/jquery.js"></script>
		<script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/dops_global_functions.js"></script>
    
   </HEAD>
   <?php
    if(isset($_POST['CANFORM'])) 
    { 
        return;	    
	  }

    if(isset($_POST['BACKF']))
     { 
        unset($_POST['FIRSTFORM']);
        unset($_POST['NUMOFBOXES']);
        unset($_POST['NUMOFBOXES']);
        unset($_POST['BOXNUMBER']);
    }
    
    if(isset($_POST['SAVEBXNOS']))
     {
    
    	    
   } 
    
if(isset($_POST['CAPTCONT'])) 
{	
      } 

if(isset($_POST['CAPTCANCEL'])) 
{	
        	
}


    if (isset($_POST['FIRSTFORM']) && !isset($_POST['NUMOFBOXES'])) {
    	  
    }

    if (isset($_POST['NUMOFBOXES']) || isset($_POST['BOXNUMBER']) )
     {
    	
                  
    }	 
// ***********************************************************************************************************************************************    

    if (!isset($_POST['FIRSTFORM']) && !isset($_POST['NUMOFBOXES']) && !isset($_POST['BOXNUMBER']) && !isset($_POST['CAPTCONT'])) {
    	
    	// 1. If FIRST FORM and NO OF BOXES is not set, proceed to first form. 
        //$captureWarehouseReceipt = new captureWarehouseReceipt();
         firstform($postUPLIFTNO);              //  firstform = function contained in the class

    } ?> 
</HTML>


<?php 
function firstform($upliftno) 
{
   
    ?>
    <body  onload='setFocusToTextBoxF()'>
        <center>
            <FORM name='Capture Warehouse Receipts' method=post action=''>
                 <table width="720"; style="border:none">
                      <tr>
                        <td class=head1 Colspan="5"; style="text-align:center">Warehouse Box Receipts</td>
                      </tr>
                      <tr>
                        <td>&nbsp</td>
                      </tr>	        	
                      <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        <td width="38%"; style="border:none">&nbsp</td>
                        <td width="20%"; style="border:none">&nbsp</td>
                        <td width="20%"; style="border:none">&nbsp</td>
                        <td width="20%"; style="border:none">&nbsp</td>
                        <td width="2%" ; style="border:none">&nbsp</td>
                      </tr>
                      <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                        <td style="text-align:center";>Enter Document Number</td>
                        <td colspan="4"; style="text-align:left"><input type="text" id="UPLIFTNO" name="UPLIFTNO"></td>
                      </tr>
                      <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        <td Colspan="5">&nbsp</td>
                      </tr>
                      <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="FIRSTFORM" value= "Get Document Details">
                                                                    <INPUT TYPE="submit" class="submit" name="CANFORM"   value= "Cancel"></td>
                      </tr>          
                      <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        <td Colspan="5">&nbsp</td>
                       </tr>  
                 </table>
            </form>
        </center>
    </body>          
    <?php 
 } 
 
 ?> 