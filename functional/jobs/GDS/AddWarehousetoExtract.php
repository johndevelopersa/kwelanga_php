<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER."properties/Constants.php");
    include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
    include_once($ROOT.$PHPFOLDER."libs/CommonUtils.php");
    include_once($ROOT.$PHPFOLDER."DAO/GlacialDAO.php");	    
    include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
		    
    if (!isset($_SESSION)) session_start() ;
      $userUId     = $_SESSION['user_id'] ;
      $principalId = $_SESSION['principal_id'] ;
      

      //Create new database object
      $dbConn  = new dbConnect(); 
      $dbConn->dbConnection();
      $errorTO = new ErrorTO;
      
      $postPrincipal   = (isset($_POST["Principal"])) ? htmlspecialchars($_POST["Principal"]) : '';
      $postChain       = (isset($_POST["Chain"]))     ? htmlspecialchars($_POST["Chain"])     : '';
      $postPrinID      = (isset($_POST["PRINID"]))    ? htmlspecialchars($_POST["PRINID"])    : '';

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
                font-size:20px;text-align:left; 
                font-family: Calibri, Verdana, Ariel, sans-serif; 
                padding: 0 150px 0 150px }
                
      td.det1  {border-style:none; 
                text-align: left; 
                font-weight: bold; 
                font-size: 13px;}

      td.det2  {border-style:none; 
                text-align: left; 
                font-weight: normal; 
                font-size: 13px;}

    	
    	</style>

		</HEAD>
    <body>
<?php
      
// ********************************************************************************************************************************************************      
if (isset($_POST['canform'])) {
      return;    
}

if (isset($_POST['saveform'])) {
        $list = implode(",",$_POST['selectWh']);
        $updateParm = $list . "#" . $_POST['CHLIST'] . "+" . $_POST['STATLST'];        
        $GlacialDAO = new GlacialDAO($dbConn);
        $errorTO = $GlacialDAO->postUpdateParams($_POST['JEUID'], $updateParm) ; 
       
        if($errorTO->type == 'S') {
           ?>
           <script type='text/javascript'>parent.showMsgBoxInfo('Update Successful <br>')</script>
           <?php 
        	
        }	else {
           ?>
           <script type='text/javascript'>parent.showMsgBoxError('Update Failed <br> Contact Support')</script> 
            <?php  
        }
        unset($_POST['firstform']);	
        unset($_POST['chainform']);	
        unset($_POST['saveform']);
        }
// ********************************************************************************************************************************************************      
if (isset($_POST['firstform'])) {
	
    if($postPrincipal == 'Select a Principal') {
          ?>
           <script type='text/javascript'>parent.showMsgBoxError('No Principal Selected <br>')</script> 
          <?php 
          unset($_POST['firstform']);
    
    } else {
        $GlacialDAO = new GlacialDAO($dbConn);
        $uCList = $GlacialDAO->getPrincipalChainList($postPrincipal);	
        ?>
        <center>
           <FORM name='GetPrincipalChain' method=post action=''>
                <table width="720"; style="border:none">
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td class=head1 colspan="6"; style="text-align:center";>Select Principal Chain</td>
                   </tr>
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td width="10%"; style="border:none">&nbsp</td>
                     <td width="10%"; style="border:none">&nbsp</td>
                     <td width="30%"; style="border:none">&nbsp</td>
                     <td width="15%"; style="border:none">&nbsp</td>
                     <td width="30%"; style="border:none">&nbsp</td>
                     <td width="5%"; style="border:none">&nbsp</td>
                   </tr>
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                   	  <td >&nbsp</td>
                      <td class=det1 colspan="2";>DGS Principal </td>
                   	  <td class=det2 colspan="2";><?php echo $uCList[0]['principal_name']; ?></td>
                   	                              <input type="hidden" name="PRINID"  value=<?php echo $uCList[0]['principal_uid']; ?>> 
                   	  <td >&nbsp</td>
                   </tr>
                  <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td Colspan="6">&nbsp</td>
                   </tr>
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                      <td >&nbsp</td>
                      <td class=det1 colspan="2";>Select Principal Chain </td>
                      <td colspan="2"; style="text-align:left;">
                           <select name="Chain" id="Chain">
                               <option value="Select a Chain"><?php echo 'Select a Chain' ?></option>
                                     <?php foreach($uCList as $row) {?>
                                           <option value="<?php echo trim($row['chain_uid']) ; ?>"><?php echo $row['chain']; ?></option>
                                     <?php } ?>
                           </select>
                      </td> 
                      <td >&nbsp</td>
                   </tr>  
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td Colspan="6">&nbsp</td>
                   </tr>
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td colspan="6"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="chainform" value= "Get Warehouses">
                                                                 <INPUT TYPE="submit" class="submit" name="canform"   value= "Cancel"></td>
                   </tr>          
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td Colspan="6">&nbsp</td>
                   </tr>  
                </table>
				   </form>
        </center> 
      <?php
	  }	
}

// ********************************************************************************************************************************************************      
if (isset($_POST['chainform'])) {
   
    if($postChain == 'Select a Chain') {
          ?>
           <script type='text/javascript'>parent.showMsgBoxError('No Principal Chain Selected <br>')</script> 
          <?php 
          unset($_POST['firstform']);
          unset($_POST['chainform']);
    } else {
           $GlacialDAO = new GlacialDAO($dbConn);
           $aWList = $GlacialDAO->getActiveWhList($postPrinID, $postChain);
           
           if (count($aWList)==0) {
               ?>
                <script type='text/javascript'>parent.showMsgBoxError('No Omni Extract Set up for this Principal Chain <br> Contact Kwelanga Support')</script> 
                <?php 
                unset($_POST['firstform']);
                unset($_POST['chainform']);
           } else {
                $dList = explode(",",$aWList[0]['Wh_List']);
	   	    	
                $GlacialDAO = new GlacialDAO($dbConn);
                $whList = $GlacialDAO->getWarehouseList($postPrinID, $userUId, $postChain);	
                ?>		
                <center>
                   <FORM name='GetWarehousList' method=post action=''>
                      <table width="720"; style="border:none">
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                           <td class=head1 colspan="6"; style="text-align:center";>Select Warhouses</td>
                         </tr>
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                           <td width="10%"; style="border:none">&nbsp</td>
                           <td width="10%"; style="border:none">&nbsp</td>
                           <td width="30%"; style="border:none">&nbsp</td>
                           <td width="15%"; style="border:none">&nbsp</td>
                           <td width="30%"; style="border:none">&nbsp</td>
                           <td width="5%"; style="border:none">&nbsp</td>
                         </tr>
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                         	  <td >&nbsp</td>
                            <td class=det1 colspan="2";>DGS Principal </td>
                            <td class=det2 colspan="2";><?php echo $whList[0]['principal_name']; ?></td>
                         	                              <input type="hidden" name="JEUID"    value=<?php echo $aWList[0]['JE_UID']; ?>>
                         	                              <input type="hidden" name="CHLIST"   value=<?php echo $aWList[0]['ch_List']; ?>>
                         	                              <input type="hidden" name="STATLST"  value=<?php echo $aWList[0]['status_List']; ?>> 
                         	  <td >&nbsp</td>
                         </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                           <td Colspan="6">&nbsp</td>
                         </tr>
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                         	  <td >&nbsp</td>
                            <td class=det1 colspan="2";>Principal Chain </td>
                            <td class=det2 colspan="2";><?php echo $whList[0]['chain_name']; ?></td>
                         	  <td >&nbsp</td>
                         </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                           <td Colspan="6">&nbsp</td>
                         </tr>
                          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                         	  <td >&nbsp</td>
                            <td class=det1 colspan="2";>Warehouse Name</td>
                            <td class=det1 colspan="2";>Selected</td>
                         	  <td >&nbsp</td>
                         </tr>
           
                         <?php
                         foreach ($whList as $row) { 
                         	      if(in_array($row['Warehouse_uid'], $dList)) {
                                      $check = 'CHECKED';
                                } else { $check= '';}
                         ?>
                                <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                   <td >&nbsp</td>
                                   <td class=det2 colspan="2";><?php echo $row['Warehouse']; ?></td>
                                   <td class=det2 colspan="2";><INPUT TYPE="checkbox" name="selectWh[]" value= "<?php echo $row['Warehouse_uid'];?>" <?php echo $check ;?>></td>
                                   <td >&nbsp</td>
                                </tr>              
                                <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                   <td Colspan="6">&nbsp</td>
                                </tr>
                         <?php 
                         } ?>
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                             <td colspan="6"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="saveform" value= "Save Selection">
                                                                         <INPUT TYPE="submit" class="submit" name="canform"   value= "Cancel"></td>
                         </tr>          
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                           <td Colspan="6">&nbsp</td>
                         </tr>  
                      </table>
                   </form>
                </center>
<?php	
           }
    }
}
// ********************************************************************************************************************************************************      

if(!isset($_POST['firstform']) && !isset($_POST['chainform'])) {
	
    $GlacialDAO = new GlacialDAO($dbConn);
    $uPList = $GlacialDAO->getUserPricipalList($userUId);
    
    $class = 'even';
    ?>
    <center>
       <FORM name='AddWarehouseToExtract' method=post action=''>
            <table width="720"; style="border:none">
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td class=head1 colspan="6"; style="text-align:center";>Add Warehouse To Onmi Extract</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td width="10%"; style="border:none">&nbsp</td>
                 <td width="10%"; style="border:none">&nbsp</td>
                 <td width="30%"; style="border:none">&nbsp</td>
                 <td width="15%"; style="border:none">&nbsp</td>
                 <td width="30%"; style="border:none">&nbsp</td>
                 <td width="5%";  style="border:none">&nbsp</td>
               </tr>

               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
               	  <td >&nbsp</td>
                  <td class=det1 colspan="2">Select GDS Principal </td>
               	  <td colspan="2"; style="text-align:left;">
               	  	   <select name="Principal" id="Principal">
                           <option value="Select a Principal"><?php echo 'Select a Principal' ?></option>
                                 <?php foreach($uPList as $row) { ?>
                                       <option value="<?php echo trim($row['principal_uid']) ; ?>"><?php echo $row['principal']; ?></option>
                                 <?php } ?>
                       </select>
                  </td> 
                  <td >&nbsp</td>
               </tr> 	  
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="6">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td colspan="6"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="firstform" value= "Continue">
                                                             <INPUT TYPE="submit" class="submit" name="canform"   value= "Cancel"></td>
               </tr>          
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="6">&nbsp</td>
               </tr>  
 			    	</table>
		   </form>
    </center> 
<?php 
} ?>

	  </body>       
</HTML>

<?php
// ********************************************************************************************************************************************************      

 /*
 
343 DBN,
346 JS,
347 JN,
349 lady,
351 PE,
353 PRET,
357 rb
2901
74
627 
*/
 ?>
 

