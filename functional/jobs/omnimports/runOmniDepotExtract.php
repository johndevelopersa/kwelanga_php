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
      
      $postDepot   = (isset($_POST["Depot"])) ? htmlspecialchars($_POST["Depot"]) : '';
      $postChain   = (isset($_POST["Chain"])) ? htmlspecialchars($_POST["Chain"]) : ''; 
   
      //Create new database object
      $dbConn = new dbConnect(); 
      $dbConn->dbConnection();
      
      if (isset($_POST['canform'])) {?>
         <script type='text/javascript'>parent.showMsgBoxError("Cancelled");</script>	 <?php	
         return;    
      }

if (isset($_POST['firstform'])) {
		
      if($postDepot <> 'Select a Depot' ) {
      	   if($postChain <> 'Select a Chain' ) {
              include_once ($ROOT.$PHPFOLDER."functional/jobs/omnimports/manualProcessOmniImports.php");
      	   } else {
              ?>
              <script type='text/javascript'>parent.showMsgBoxError('No Chain Selected - Try Again')</script> 
              <?php
           }              
      } else {
              ?>
              <script type='text/javascript'>parent.showMsgBoxError('No Depot Selected - Try Again')</script> 
              <?php
      }
       return;	      

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
                font-weight: normal; 
                font-size: 13px;
               }
    	
    	</style>

		</HEAD>
    <body>
<?php
      
// ********************************************************************************************************************************************************      
     
if(!isset($_POST['firstform'])) {
	
    $MaintenanceDAO = new MaintenanceDAO($dbConn);
    $aWP = $MaintenanceDAO->omniExtractDepotList( $userUId, $principalId);

    $MaintenanceDAO = new MaintenanceDAO($dbConn);
    $aCH = $MaintenanceDAO->omniExtractChainList($principalId);
    
    $class = 'odd';    
    
    ?>
    <center>
       <FORM name='Reset Extract Time' method=post action=''>
            <table width="720"; style="border:none">
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td class=head1 colspan="5"; style="text-align:center";>Omni Depot Extract</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td width="10%"; style="border:none">&nbsp</td>
                 <td width="30%"; style="border:none">&nbsp</td>
                 <td width="30%"; style="border:none">&nbsp</td>
                 <td width="20%"; style="border:none">&nbsp</td>
                 <td width="15%"; style="border:none">&nbsp</td>
               </tr>               
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                  <td >&nbsp</td>
                  <td class=det1 >Omni&nbsp;Depot </td>
                  <td colspan="2"; style="text-align:left;">
                      <select name="Depot" id="Depot">
                           <option value="Select a Depot"><?php echo 'Select GDS Depot' ?></option>
                                 <?php foreach($aWP as $row) { ?>
                                       <option value="<?php echo trim($row['depot_uid']) ; ?>"><?php echo $row['depot_name']; ?></option>
                                 <?php } ?>
                      </select>
                  </td> 
                  <td colspan="1"; >&nbsp;</td>
               </tr>               
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                  <td Colspan="5">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                  <td >&nbsp</td>
                  <td  class=det1 style="text-align:left;">Principal&nbsp;Chain</td>
                  <td colspan="2"; style="text-align:left;">
                      <select name="Chain" id="Chain">
                           <option value="Select a Chain"><?php echo 'Select a Chain' ?></option>
                                 <?php foreach($aCH as $crow) { ?>
                                       <option value="<?php echo trim($crow['chain_uid']) ; ?>"><?php echo $crow['chain']; ?></option>
                                 <?php } ?>
                      </select>
                  </td>                  
                  <td >&nbsp</td>
                <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                  <td Colspan="5">&nbsp</td>
                </tr>
                <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                  <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="firstform" value= "Extract Now">
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
