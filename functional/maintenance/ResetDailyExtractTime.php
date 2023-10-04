<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER."properties/Constants.php");
    include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
    include_once($ROOT.$PHPFOLDER."libs/CommonUtils.php");
    include_once ($ROOT.$PHPFOLDER."DAO/MaintenanceDAO.php");  	    

    if (!isset($_SESSION)) session_start() ;
      $userUId     = $_SESSION['user_id'] ;
      $principalId = $_SESSION['principal_id'] ;
      $systemId    = $_SESSION["system_id"];
      $systemName  = $_SESSION['system_name'];
   
      //Create new database object
      $dbConn = new dbConnect(); 
      $dbConn->dbConnection();
      
      if (isset($_POST['canform'])) {?>
         <script type='text/javascript'>parent.showMsgBoxError("Cancelled");</script>	 <?php	
         return;    
      }

if (isset($_POST['firstform'])) {
      if($postPrincipal <> 'Select a Principal' ) { 
      	      	
      	   $postfParm     = (isset($_POST["Principal"])) ? htmlspecialchars($_POST["Principal"]) : '';
      	   
      	   $postPrincipal = substr($postfParm,0,strpos($postfParm,"-")) ;
      	   
      	   $postType      = trim(substr($postfParm,strpos($postfParm,"-")+1,4)) ;
     
           //totals
           $statST = microtime(true);
           $statJOBcnt = 0;
           
           $MaintenanceDAO = new MaintenanceDAO($dbConn);
           $mfJE = $MaintenanceDAO->getDailyExecutionEntry($postPrincipal, $postType);
           
           if ((isset($mfJE[0])) && ($mfJE[0]["script_name"]!="")) {
               foreach ($mfJE as $je) {
               	
                  include_once($ROOT.$PHPFOLDER."functional/extracts/daily/{$je["script_name"]}.php");
                  // each processor must control its own rollbacks !
                  if($postPrincipal == 290) {
                       $dbConn->dbinsQuery("commit"); // commit any successfuly	
                  } else {
                      $clTO=call_user_func(array($je["script_name"], "generateOutput"));
                      if ($clTO->type!=FLAG_ERRORTO_SUCCESS) {
                           $dbConn->dbinsQuery("rollback");
                      } else {
                           $dbConn->dbinsQuery("commit"); // commit any successfuly
                      }
                  }
                   $statJOBcnt++;
               }
           } else {
               ?>
               <script type='text/javascript'>parent.showMsgBoxError('No Principal Function Found')</script> 
               <?php
               unset($_POST['firstform']);
           }
           
           $statET = microtime(true);
           $statTT = round($statET - $statST,4);      
           ?>
             <script type='text/javascript'>parent.showMsgBoxInfo("Successfully Completed Extract : <br>Job Started: @ <?php echo (CommonUtils::getGMTime(0)) ?><br>JOBS :   <?php echo $statJOBcnt ?><br>TT :   <?php echo $statTT ?>}<BR>Please refresh your screen<BR>***EOS***")</script> 
           <?php 
           	return;
      } else {
                ?>
                <script type='text/javascript'>parent.showMsgBoxError('No Principal Selected - Try Again')</script> 
                <?php
                unset($_POST['firstform']);
      }     
}
?>
<!DOCTYPE html>
<HTML>
	<HEAD>

		<TITLE>Principal - Warehouse Selection</TITLE>

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
	
    $MaintenanceDAO = new MaintenanceDAO($dbConn);
    $aWP = $MaintenanceDAO->dailyExtractPrincipalList( $userUId, $principalId);
    
//    print_r($aWP);
    
    $class = 'odd';    
    
    ?>
    <center>
       <FORM name='Reset Extract Time' method=post action=''>
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
               	  <td >&nbsp</td>
                  <td  class=head1 >Principal </td>
               	  <td colspan="2"; style="text-align:left;">
               	  	   <select name="Principal" id="Principal">
                           <option value="Select a Principal"><?php echo 'Select a Principal' ?></option>
                                 <?php foreach($aWP as $row) { 
                                 	     if($row['type'] <> NULL) {$tp = $row['type'];} else {$tp = NULL;} ?>
                                       <option value="<?php echo trim($row['principal_uid'] . '-' . $tp ) ; ?>"><?php echo $row['principal'] . ' ' . $tp ; ?></option>
                                 <?php } ?>
               	  	   </select>
               	  </td> 
               	  <td colspan="2"; >&nbsp;</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="5">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="5">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="firstform" value= "Run Daily Extract">
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
