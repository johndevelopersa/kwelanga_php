<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER."properties/Constants.php");
    include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
    include_once($ROOT.$PHPFOLDER.'elements/basicSelectElement.php');	
    include_once($ROOT.$PHPFOLDER."libs/CommonUtils.php");
    include_once ($ROOT.$PHPFOLDER."DAO/MaintenanceDAO.php");	    

    if (!isset($_SESSION)) session_start() ;
      $userUId     = $_SESSION['user_id'] ;
      $principalId = $_SESSION['principal_id'] ;
      $depotId     = $_SESSION['depot_id'] ;
      $systemId    = $_SESSION["system_id"];
      $systemName  = $_SESSION['system_name'];
      
      $postPrincipal   = (isset($_POST["Principal"])) ? htmlspecialchars($_POST["Principal"]) : ''; 
      $postWarehouse   = (isset($_POST["Warehouse"])) ? htmlspecialchars($_POST["Warehouse"]) : ''; 
   
      //Create new database object
      $dbConn = new dbConnect(); $dbConn->dbConnection();
      if (isset($_POST['canform'])) {?>
      		       	
	       <script type='text/javascript'>parent.showMsgBoxError("Cancelled");</script>	 <?php	
         return;    
      }

if (isset($_POST['firstform'])) {	
	
         $prinDash  = strpos($_POST["Principal"],'-');
         $warehDash = strpos($_POST["Warehouse"],'-');
         
         $intVar    = $_POST["dInterval"];
	
	       if($_POST["Principal"] == 'Select All Principals' && $_POST["Warehouse"] == 'Select All Warehouses') {
	       	  $confirmMsg = 'Run Update for all Pincipals & All Warehouses';
	       	  $prinVar    = $_POST["Principal"];
	       	  $warehVar   = $_POST["Warehouse"];
	       } elseif($_POST["Principal"] == 'Select All Principals') {
	       	  $confirmMsg = 'Run Update for All Pincipals & ' . trim(substr($_POST["Warehouse"],$warehDash+1,40)) ;
	       	  $prinVar    = $_POST["Principal"];
	       	  $warehVar   = substr($_POST["Warehouse"],0,$warehDash);	
	       } elseif($_POST["Principal"] == 'Select All Warehouses') {
	       	  $confirmMsg = 'Run Update for ' . trim(substr($_POST["Principal"],$prinDash+1,40)) . ' & All Warehouses';	
	       	  $prinVar    = substr($_POST["Principal"],0,$prinDash);
	       	  $warehVar   = $_POST["Warehouse"];	
	       } else {	
	       	  $confirmMsg = 'Run Update for ' . trim(substr($_POST["Principal"],$prinDash+1,40)) . ' and ' . trim(substr($_POST["Warehouse"],$warehDash+1,40));	
	       	  $prinVar    = substr($_POST["Principal"],0,$prinDash);
	       	  $warehVar   = substr($_POST["Warehouse"],0,$warehDash);	
         } ?>
         
	       	
	       <script type='text/javascript'>
               var r = confirm('<?php echo $confirmMsg ;?>');
               if (r == true) { 
                	   	window.location.href='Update stock AllocatedInpick.php?PRINCIPALLIST=<?php echo $prinVar; ?>&WAREHOUSLIST=<?php echo $warehVar;?>&DINTERVAL=<?php echo $intVar; ?>' ;
               } else {   
                	     parent.showMsgBoxError("Update Cancelled") ;
               }       
         </script>	 <?php	
	       
	       unset($_POST['firstform']);	       
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
      
// ********************************************************************************************************************************************************      
     
if(!isset($_POST['firstform'])) {
    $MaintenanceDAO = new MaintenanceDAO($dbConn);
    $aPW = $MaintenanceDAO->activePrincipalWarehouses();
    
    $MaintenanceDAO = new MaintenanceDAO($dbConn);
    $aWP = $MaintenanceDAO->activeWarehousesPrincipal()    
    
    ?>
    <center>
       <FORM name='Select Distribution Parameters' method=post action=''>
            <table width="720"; style="border:none">
               <tr>
                 <td class=head1 >Select Parameters</td>
               </tr>
               <tr>
                 <td>&nbsp</td>
               </tr>	        	
            <table width="720"; style="border:none" >        	
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td width="38%"; style="border:none">&nbsp</td>
                 <td width="20%"; style="border:none">&nbsp</td>
                 <td width="20%"; style="border:none">&nbsp</td>
                 <td width="20%"; style="border:none">&nbsp</td>
                 <td width="2%" ; style="border:none">&nbsp</td>
               </tr>
               
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                  <td style="text-align:left";>Principal : </td>
               	  <td colspan="4"; style="text-align:left;">
               	  	   <select name="Principal" id="Principal">
                           <option value="Select All Principals"><?php echo 'Select All Principals' ?></option>
                                 <?php foreach($aPW as $row) { ?>
                                       <option value="<?php echo trim($row['principal_uid']) . '-' . $row['name'] ; ?>"><?php echo $row['name']; ?></option>
                                 <?php } ?>
               	  	   </select>
               	  </td> 
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="5">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                  <td style="text-align:left";>Warehouse : </td>
               	  <td colspan="4"; style="text-align:left;">
               	  	   <select name="Warehouse" id="Warehouse">
                           <option value="Select All Warehouses"><?php echo 'Select All Warehouses' ?></option>
                                 <?php foreach($aWP as $row) { ?>
                                       <option value="<?php echo trim($row['depot_uid']) . '-' . $row['name'] ; ?>"><?php echo $row['name']; ?></option>
                                 <?php } ?>
               	  	   </select>
               	  </td> 
               </tr>          
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="5">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                  <td style="text-align:left";>Days Interval : </td>
               	  <td colspan="4"; style="text-align:left;"><input type="text" name="dInterval" value='45'>               	  	
               	  </td> 
               </tr>   
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="5">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="firstform" value= "Update Available Stock">
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




<?php 
 function test_input($data) {

  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  if($data=='') { $data=0; } 
    
  return $data;
 }
?> 