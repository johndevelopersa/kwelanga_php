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
      
      $postpassword      = (isset($_POST["password"]))    ? test_input($_POST["password"])    : '';
      $postquerySelect   = (isset($_POST["querySelect"])) ? test_input($_POST["querySelect"]) : '';
      
      $tblName           = (isset($_POST["tblName"]))   ? test_input($_POST["tblName"])   : '';
      $tControl1         = (isset($_POST["tControl1"])) ? test_input($_POST["tControl1"]) : '';     
      $tControl2         = (isset($_POST["tControl2"])) ? test_input($_POST["tControl2"]) : '';  
      $tControl3         = (isset($_POST["tControl3"])) ? test_input($_POST["tControl3"]) : '';  

      $ctlVar1           = (isset($_POST["ctlVar1"]))   ? test_input($_POST["ctlVar1"])   : '';     
      $ctlVar2           = (isset($_POST["ctlVar2"]))   ? test_input($_POST["ctlVar2"])   : '';   
      $ctlVar3           = (isset($_POST["ctlVar3"]))   ? test_input($_POST["ctlVar3"])   : '';   
            
      $ufld1             = (isset($_POST["ufld1"]))     ? test_input($_POST["ufld1"])     : '';
      $ufld2             = (isset($_POST["ufld2"]))     ? test_input($_POST["ufld2"])     : '';
      $ufld3             = (isset($_POST["ufld3"]))     ? test_input($_POST["ufld3"])     : '';
      $ufld4             = (isset($_POST["ufld4"]))     ? test_input($_POST["ufld4"])     : '';
      $ufld5             = (isset($_POST["ufld5"]))     ? test_input($_POST["ufld5"])     : '';
      $ufld6             = (isset($_POST["ufld6"]))     ? test_input($_POST["ufld6"])     : '';
      $ufld7             = (isset($_POST["ufld7"]))     ? test_input($_POST["ufld7"])     : '';
      $ufld8             = (isset($_POST["ufld8"]))     ? test_input($_POST["ufld8"])     : '';
      $ufld9             = (isset($_POST["ufld9"]))     ? test_input($_POST["ufld9"])     : '';
      $ufld10            = (isset($_POST["ufld10"]))    ? test_input($_POST["ufld10"])    : '';



      $uVar1             = (isset($_POST["Var1"]))     ? test_input($_POST["Var1"])     : '';
      $uVar2             = (isset($_POST["Var2"]))     ? test_input($_POST["Var2"])     : '';
      $uVar3             = (isset($_POST["Var3"]))     ? test_input($_POST["Var3"])     : '';
      $uVar4             = (isset($_POST["Var4"]))     ? test_input($_POST["Var4"])     : '';
      $uVar5             = (isset($_POST["Var5"]))     ? test_input($_POST["Var5"])     : '';
      $uVar6             = (isset($_POST["Var6"]))     ? test_input($_POST["Var6"])     : '';
      $uVar7             = (isset($_POST["Var7"]))     ? test_input($_POST["Var7"])     : '';
      $uVar8             = (isset($_POST["Var8"]))     ? test_input($_POST["Var8"])     : '';
      $uVar9             = (isset($_POST["Var9"]))     ? test_input($_POST["Var9"])     : '';
      $uVar10            = (isset($_POST["Var10"]))    ? test_input($_POST["Var10"])    : '';      

      //Create new database object
      $dbConn  = new dbConnect(); 
      $dbConn->dbConnection();
      $errorTO = new ErrorTO;
      
      if (isset($_POST['canform'])) {
         return;    
      }

if (isset($_POST['firstform'])) {
	
      if(trim($postpassword) <> '' && strlen(trim($postpassword) <> 9)) {
              $MaintenanceDAO = new MaintenanceDAO($dbConn);
              $rPW = $MaintenanceDAO->getScriptRunPassword($userUId);
              
              if (md5($postpassword) == trim($rPW[0]['taskman_account'])) {
                      $MaintenanceDAO = new MaintenanceDAO($dbConn);
                      $errorTO = $MaintenanceDAO->runScreenValidationScript($postquerySelect, $tblName , $tControl1, $ctlVar1, $ufld1, $uVar1);
                      if ($errorTO->type==FLAG_ERRORTO_SUCCESS) {
                                 
                            $MaintenanceDAO = new MaintenanceDAO($dbConn);
                            $errorTO = $MaintenanceDAO->runSelectScript($postquerySelect);
                            
                            print_r($errorTO);
                            
                            if ($errorTO->type==FLAG_ERRORTO_SUCCESS) {
                                 $rPW = $errorTO->object;                            	   
                            	   $insertCount = $updateCount = 0;
                                 if(count($rPW) > 0 ) {
                                     foreach ($rPW as $qrow) { 
                                     	    $MaintenanceDAO = new MaintenanceDAO($dbConn);                                     	    
                                          $rEX = $MaintenanceDAO->checkRecordExists($tblName, trim($tControl1), trim($tControl2), trim($tControl3),
                                                                                              trim($qrow[$ctlVar1]), trim($qrow[$ctlVar2]), trim($qrow[$ctlVar3]));
                                          $octlVar1 = $qrow[$ctlVar1];
                                          $octlVar2 = $qrow[$ctlVar2];
                                          $octlVar3 = $qrow[$ctlVar3];
                                          $oVar1  = $qrow[$uVar1];
                                          $oVar2  = $qrow[$uVar2];
                                          $oVar3  = $qrow[$uVar3];
                                          $oVar4  = $qrow[$uVar4];
                                          $oVar5  = $qrow[$uVar5];         
                                          $oVar6  = $qrow[$uVar6];         
                                          $oVar7  = $qrow[$uVar7];         
                                          $oVar8  = $qrow[$uVar8];         
                                          $oVar9  = $qrow[$uVar9];    
                                          $oVar10 = $qrow[$uVar10];
                                          
                                          if(count($rEX) == 0 ) {
                                         
                                               $MaintenanceDAO = new MaintenanceDAO($dbConn);
                                               $errorTO = $MaintenanceDAO->runInsertScript($tblName, $ufld1, $ufld2, $ufld3, $ufld4, $ufld5, $ufld6, $ufld7, $ufld8, $ufld9, $ufld10,
                                                                                           $tControl1, $tControl2, $tControl3, $octlVar1, $octlVar2, $octlVar3,
                                                                                           $oVar1, $oVar2, $oVar3, $oVar4, $oVar5, $oVar6, $oVar7, $oVar8, $oVar9, $oVar10);
                                          
                                               if ($errorTO->type==FLAG_ERRORTO_SUCCESS) {
                                               	    $insertCount++;
                                               } else {
                                                    ?>
                                                    <script type='text/javascript'>parent.showMsgBoxError('Something Failed on Insert - Shit <br>')</script> 
                                                    <?php 
                                                    return;
                                               }
                                           } else {
                                               $MaintenanceDAO = new MaintenanceDAO($dbConn);
                                               $errorTO = $MaintenanceDAO->runUpdateScript($tblName, $ufld1, $ufld2, $ufld3, $ufld4, $ufld5, $ufld6, $ufld7, $ufld8, $ufld9, $ufld10,
                                                                                           $tControl1, $tControl2, $tControl3, $octlVar1, $octlVar2, $octlVar3,
                                                                                           $oVar1, $oVar2, $oVar3, $oVar4, $oVar5, $oVar6, $oVar7, $oVar8, $oVar9, $oVar10);
                                          
                                               if ($errorTO->type==FLAG_ERRORTO_SUCCESS) {
                                               	    $updateCount++;
                                               } else {
                                                    ?>
                                                    <script type='text/javascript'>parent.showMsgBoxError('Something Failed on Update - Shit <br>')</script> 
                                                    <?php 
                                                    return;
                                               }
                                           }
                                     } 	
                                     $tblName = $ufld1 = $ufld2 = $ufld3 = $ufld4 = $ufld5 = $ufld6 = $ufld7 = $ufld8 = $ufld9 = $ufld10  = '';
                                     $tControl1 = $tControl2 = $tControl3 = $ctlVar1 = $ctlVar2 = $ctlVar3 = '';
                                     $oVar1 = $oVar2 = $oVar3 = $oVar4 = $oVar5 = $oVar6 = $oVar7 = $oVar8 = $oVar9 = $oVar10  = $postquerySelect = '';
                                     ?>
                                         <script type='text/javascript'>parent.showMsgBoxInfo('Insert / Update Successful <br><?php echo  $insertCount; ?> Records Inserted<br><?php echo  $updateCount; ?> Records Updated ')</script> 
                                     <?php 
                                 } else {
                                     ?>
                                         <script type='text/javascript'>parent.showMsgBoxError('No Records selected to Process<br>')</script> 
                                     <?php	
                                 }
                            } else {
                                  ?>
                                  <script type='text/javascript'>parent.showMsgBoxError('Error in query<BR><BR>')</script> 
                                 <?php echo $errorTO->description;
                                 echo 'HERE';
                                  unset($_POST['firstform']);	 
                            }     
                      } else {
                            ?>
                            <script type='text/javascript'>parent.showMsgBoxError('Parameters Incorrect<BR><BR><?php echo $errorTO->description ?> <br>')</script> 
                            <?php
                      }                       
              } else {
                  ?>
                  <script type='text/javascript'>parent.showMsgBoxError('Not Authorised - Quitting')</script> 
                  <?php              	
                   unset($_POST['firstform']);	 	
              }
      } else {
              ?>
              <script type='text/javascript'>parent.showMsgBoxError('Password Incorrect - Quitting')</script> 
              <?php
               unset($_POST['firstform']);	            
      }
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
       <FORM name='TestAndInsert' method=post action=''>
            <table width="720"; style="border:none">
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td class=head1 colspan="5"; style="text-align:center";>Test And Insert / Update Query</td>
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
                  <td  class=det1;>Password</td>
               	  <td colspan="2"; style="text-align:left;"><input type="text" name="password" id="password" placeholder="Enter Password"></td> 
               	  <td colspan="2"; >&nbsp;</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="6">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                    <td>&nbsp</td>
                    <td class=det1; >Select Query</td>
                    <td colspan="3"><textarea name="querySelect" id="querySelect"  rows="8" cols="50" ><?php echo $postquerySelect ; ?></textarea></td>
                    <td colspan="1"; >&nbsp;</td>   
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="6">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
               	  <td >&nbsp</td>
                  <td  class=det1;>Table Name</td>
               	  <td colspan="1"; style="text-align:left;"><input type="text" name="tblName" id="tblName" value ="<?php echo $tblName  ; ?>"></td> 
                  <td colspan="2"; style="text-align:left;">&nbsp;</td>
                  <td colspan="1"; >&nbsp;</td> 
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
               	  <td >&nbsp</td>
                  <td  class=det1;>Table - Control1</td>
               	  <td colspan="1"; style="text-align:left;"><input type="text" name="tControl1" id="tControl1" value ="<?php echo $tControl1 ?>"></td> 
                  <td  class=det1;>Mapped to </td>
                  <td colspan="1"; style="text-align:left;"><input type="text" name="ctlVar1" id="ctlVar1" value ="<?php echo $ctlVar1 ?>"></td>
                  <td colspan="1"; >&nbsp;</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
               	  <td >&nbsp</td>
                  <td  class=det1;>Table - Control2</td>
               	  <td colspan="1"; style="text-align:left;"><input type="text" name="tControl2" id="tControl2" value ="<?php echo $tControl2 ?>"></td> 
                  <td  class=det1;>Mapped to </td>
                  <td colspan="1"; style="text-align:left;"><input type="text" name="ctlVar2" id="ctlVar2" value ="<?php echo $ctlVar2 ?>"></td>  
               	  <td colspan="1"; >&nbsp;</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
               	  <td >&nbsp</td>
                  <td  class=det1;>Table - Control3</td>
               	  <td colspan="1"; style="text-align:left;"><input type="text" name="tControl3" id="tControl3" value ="<?php echo $tControl3 ?>"></td> 
                  <td  class=det1;>Mapped to </td>
                  <td colspan="1"; style="text-align:left;"><input type="text" name="ctlVar3" id="ctlVar3" value ="<?php echo $ctlVar3 ?>"></td> 
               	  <td colspan="1"; >&nbsp;</td>
               </tr>

               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
               	  <td >&nbsp</td>
                  <td  class=det1;>Field1</td>
               	  <td colspan="1"; style="text-align:left;"><input type="text" name="ufld1" id="ufld1" value ="<?php echo $ufld1 ?>"></td> 
                  <td  class=det1;>Mapped to </td>
                  <td colspan="1"; style="text-align:left;"><input type="text" name="Var1" id="Var1" value ="<?php echo $oVar1 ?>"></td>
               	  <td colspan="1"; >&nbsp;</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                  <td >&nbsp</td>
                  <td  class=det1;>Field1</td>
                  <td colspan="1"; style="text-align:left;"><input type="text" name="ufld2" id="ufld2" value ="<?php echo $ufld2 ?>"></td> 
                  <td  class=det1;>Mapped to </td>
                  <td colspan="1"; style="text-align:left;"><input type="text" name="Var2" id="Var2" value ="<?php echo $oVar2 ?>"></td>
                  <td colspan="1"; >&nbsp;</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                  <td >&nbsp</td>
                  <td  class=det1;>Field1</td>
                  <td colspan="1"; style="text-align:left;"><input type="text" name="ufld3" id="ufld3" value ="<?php echo $ufld3 ?>"></td> 
                  <td  class=det1;>Mapped to </td>
                  <td colspan="1"; style="text-align:left;"><input type="text" name="Var3" id="Var3" value ="<?php echo $oVar3 ?>"></td>
                  <td colspan="1"; >&nbsp;</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                  <td >&nbsp</td>
                  <td  class=det1;>Field1</td>
                  <td colspan="1"; style="text-align:left;"><input type="text" name="ufld4" id="ufld4" value ="<?php echo $ufld4 ?>"></td> 
                  <td  class=det1;>Mapped to </td>
                  <td colspan="1"; style="text-align:left;"><input type="text" name="Var4" id="Var4" value ="<?php echo $oVar4 ?>"></td>
                  <td colspan="1"; >&nbsp;</td>
               </tr>

               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                  <td >&nbsp</td>
                  <td  class=det1;>Field1</td>
                  <td colspan="1"; style="text-align:left;"><input type="text" name="ufld5" id="ufld5" value ="<?php echo $ufld5 ?>"></td> 
                  <td  class=det1;>Mapped to </td>
                  <td colspan="1"; style="text-align:left;"><input type="text" name="Var5" id="Var5" value ="<?php echo $oVar5 ?>"></td>
                  <td colspan="1"; >&nbsp;</td>
               </tr>

               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                  <td >&nbsp</td>
                  <td  class=det1;>Field1</td>
                  <td colspan="1"; style="text-align:left;"><input type="text" name="ufld4" id="ufld6" value ="<?php echo $ufld6 ?>"></td> 
                  <td  class=det1;>Mapped to </td>
                  <td colspan="1"; style="text-align:left;"><input type="text" name="Var4" id="Var6" value ="<?php echo $oVar6 ?>"></td>
                  <td colspan="1"; >&nbsp;</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                  <td >&nbsp</td>
                  <td  class=det1;>Field1</td>
                  <td colspan="1"; style="text-align:left;"><input type="text" name="ufld7" id="ufld7" value ="<?php echo $ufld7 ?>"></td> 
                  <td  class=det1;>Mapped to </td>
                  <td colspan="1"; style="text-align:left;"><input type="text" name="Var7" id="Var7" value ="<?php echo $oVar7 ?>"></td>
                  <td colspan="1"; >&nbsp;</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                  <td >&nbsp</td>
                  <td  class=det1;>Field1</td>
                  <td colspan="1"; style="text-align:left;"><input type="text" name="ufld8" id="ufld8" value ="<?php echo $ufld8 ?>"></td> 
                  <td  class=det1;>Mapped to </td>
                  <td colspan="1"; style="text-align:left;"><input type="text" name="Var8" id="Var8" value ="<?php echo $oVar8 ?>"></td>
                  <td colspan="1"; >&nbsp;</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                  <td >&nbsp</td>
                  <td  class=det1;>Field1</td>
                  <td colspan="1"; style="text-align:left;"><input type="text" name="ufld9" id="ufld9" value ="<?php echo $ufld9 ?>"></td> 
                  <td  class=det1;>Mapped to </td>
                  <td colspan="1"; style="text-align:left;"><input type="text" name="Var8" id="Var9" value ="<?php echo $oVar9 ?>"></td>
                  <td colspan="1"; >&nbsp;</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                  <td >&nbsp</td>
                  <td  class=det1;>Field1</td>
                  <td colspan="1"; style="text-align:left;"><input type="text" name="ufld10" id="ufld10" value ="<?php echo $ufld10 ?>"></td> 
                  <td  class=det1;>Mapped to </td>
                  <td colspan="1"; style="text-align:left;"><input type="text" name="Var10" id="Var10" value ="<?php echo $oVar10 ?>"></td>
                  <td colspan="1"; >&nbsp;</td>
               </tr>
              
              <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="6">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td colspan="6"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="firstform" value= "Run Query">
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
 function test_input($data) {

  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  
  return $data;
 }
 ?> 
