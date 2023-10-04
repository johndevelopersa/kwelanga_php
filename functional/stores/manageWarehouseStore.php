<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
    include_once($ROOT.$PHPFOLDER.'DAO/storeDAO.php');
        
    //Create new database object 
    $dbConn = new dbConnect(); $dbConn->dbConnection();

    if (!isset($_SESSION)) session_start() ;
      $userUId     = $_SESSION['user_id'] ;
      $principalId = $_SESSION['principal_id'] ;
      $depotId     = $_SESSION['depot_id'] ;
      $systemId    = $_SESSION["system_id"];
      $systemName  = $_SESSION['system_name'];
      $class = 'even';
      
      
?>
<!DOCTYPE html>
<HTML>
	<HEAD>

		<TITLE>Document Selection</TITLE>

		<link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/1_kwelanga.css' rel='stylesheet' type='text/css'>
		<link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/kos_standard_screen.css' rel='stylesheet' type='text/css'>
		
		<script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/jquery.js"></script>
		<script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/dops_global_functions.js"></script>

   </HEAD>
     <BODY> <?php
     	
if (isset($_POST['canform'])) {
      return;    
}

if (isset($_POST['backform'])) {

      unset($_POST['firstform']);	
      unset($_POST['finish']);	
}

if (isset($_POST["WSUID"]))    $postWSUID    = test_input($_POST["WSUID"]);    else $postWSUID    = '0'; 
if (isset($_POST["WSNAME"]))   $postWSNAME   = test_input($_POST["WSNAME"]);   else $postWSNAME   = '0';
if (isset($_POST["GLN"]))      $postGLN      = test_input($_POST["GLN"]);      else $postGLN      = '0';
if (isset($_POST["WSBRANCH"])) $postWsBranch = test_input($_POST["WSBRANCH"]); else $postWsBranch = '0';
if (isset($_POST["WAREA"]))    $postWAREA    = test_input($_POST["WAREA"]);    else $postWAREA    = '0';
if (isset($_POST["WSTATUS"]))  $postWsStatus = 'A'; else $postWsStatus  = 'D';

if(isset($_POST["CLEARFILTER"])) {
     unset($_POST["SUBMITFILTER"]);
     unset($storeList);
}
if(isset($_POST["SUBMITFILTER"])) {
     $StoreDAO = new StoreDAO($dbConn);
     $storeList = $StoreDAO->getWarehouseStoreDetails($depotId, $postWSUID, $postWSNAME ,$postGLN, $postWsBranch, $postWAREA, $postWsStatus);  	
}

if(isset($_POST["WSUID"])) {
     echo "I am here0";  	
}


?>
    <center>
        <FORM name='Manage Warehouse Stores' method=post action=''>
             <table width="800"; style="border:none">
                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                    <td class=head1 colspan="11"; style="text-align:center";>Manage Warehouse Stores</td>
                 </tr>
                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                    <td colspan="11">&nbsp;</td>
                 </tr>
                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                    <td class=det2 colspan="3" style="text-align:center";><INPUT TYPE="submit" class="submit" name="SUBMITFILTER" value= "Submit Filter">&nbsp;
                                                                          <INPUT TYPE="submit" class="submit" name="CLEARFILTER"   value= "Clear Filter"></td>
                    <td colspan="7">&nbsp</td>
                    <td>&nbsp</td>
                 </tr>
                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                    <td width="2%";  style="border:none;">&nbsp;</td>
                    <td width="7%";  style="border:none;">&nbsp;</td>
                    <td width="15%"; style="border:none;">&nbsp;</td>
                    <td width="15%"; style="border:none;">&nbsp;</td>
                    <td width="15%"; style="border:none;">&nbsp;</td>
                    <td width="10%"; style="border:none;">&nbsp;</td>
                    <td width="10%"; style="border:none;">&nbsp;</td>
                    <td width="10%"; style="border:none;">&nbsp;</td>
                    <td width="10%"; style="border:none;">&nbsp;</td>
                    <td width="4%";  style="border:none;">&nbsp;</td>
                    <td width="2%";  style="border:none;">&nbsp;</td>
                 </tr>
                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                    <td colspan="1">&nbsp</td>
                    <td class="det1" colspan="1" style="text-align:left";><INPUT TYPE="TEXT" size="3" name="WSUID" ></td>
                    <td class="det1" colspan="3" style="text-align:left";><INPUT TYPE="TEXT" size="30" name="WSNAME" nowrap ></td>
                    <td class="det1" colspan="1" style="text-align:left";><INPUT TYPE="TEXT" size="5" name="GLN" value= "" ></td>
                    <td class="det1" colspan="1" style="text-align:left";><INPUT TYPE="TEXT" size="5" name="WSBRANCH" value= "" ></td>
                    <td class="det1" colspan="1" style="text-align:left";><INPUT TYPE="TEXT" size="5" name="WAREA" value= "" ></td>
                    <td class="det1" colspan="1" style="text-align:right";><INPUT TYPE="CHECKBOX" id="WSTATUS" name="WSTATUS" value="A" checked ></td>
                    <td colspan="1">&nbsp</td>
                    <td colspan="1">&nbsp</td>
                 </tr>                    
                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                    <td class="det1" colspan="1" style="text-align:left;">&nbsp</td>
                    <td class="detB10" colspan="1" style="text-align:left;">Uid</td>
                    <td class="detB10" colspan="3" style="text-align:left;" nowrap >Store</td>
                    <td class="detB10" colspan="1" style="text-align:left;">GLN</td>
                    <td class="detB10" colspan="1" style="text-align:left;">Branch</td>
                    <td class="detB10" colspan="1" style="text-align:left;">Area</td>
                    <td class="detB10" colspan="1" style="text-align:left;">Active/Deleted</td>
                    <td colspan="1">&nbsp</td>
                    <td colspan="1">&nbsp</td>
                 </tr>                       
                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                      <td class="det1" colspan="1" style="text-align:left";>&nbsp</td>
                      <td class="detN10" colspan="9" style="text-align:left; color:Red; border-top-style:solid; border-top-width:2px; border-top-color:black;">Please use filters to define your list</td>          
                      <td class="det1" colspan="1" style="text-align:left";>&nbsp</td>
                 </tr>   
               <?php 
               if(count($storeList) > 0) { 
               	   foreach($storeList as $row) { ?>
                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td class="det1" colspan="1" style="text-align:left";><INPUT TYPE="radio" name="WSUID" value= "<?php echo $row['wsmUid']; ?>"</td>
                          <td class="detN10" colspan="1" style="text-align:left;"><?php echo $row['wsmUid']; ?></td>
                          <td class="detN10" colspan="3" style="text-align:left;"><?php echo $row['del_point_name']; ?></td>
                          <td class="detN10" colspan="1" style="text-align:left;"><?php echo $row['gln']; ?></td>
                          <td class="detN10" colspan="1" style="text-align:left;"><?php echo $row['branch']; ?></td>
                          <td class="detN10" colspan="1" style="text-align:left;"><?php echo $row['delivery_area']; ?></td>
                          <td class="detN10" colspan="1" style="text-align:left;"><?php if($row['AD'] == 'A') {echo 'Active';} else {echo 'Deleted';}  ?></td>                  	
                          <td colspan="1">&nbsp</td>
                          <td colspan="1">&nbsp</td>

               <?php } ?>
               </tr>
               	   <?php 
               	   } ?>

                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                      <td class="det1" colspan="1" style="text-align:left";>&nbsp</td>                      
                      <td class="det3" colspan="9" style="text-align:left; border-top-style:double; border-bottom-width:2px; border-top-color:black;""><?php echo count($storeList) ?>&nbsp;Row(s) Found</php></td>          
                      <td class="det1" colspan="1" style="text-align:left";>&nbsp</td>
                   </tr>
        </table>
      </form>
    </center>

<script>

 $(document).ready(function() { 
   $('input[name=WSUID]').change(function(){
        $('form').submit();
   });
  });
</script>
     </BODY>
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