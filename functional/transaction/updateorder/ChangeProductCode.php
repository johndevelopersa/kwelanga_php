<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER."DAO/transactionDAO.php");
    include_once($ROOT.$PHPFOLDER."properties/Constants.php");
    include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
    include_once($ROOT.$PHPFOLDER."DAO/MaintenanceDAO.php");	    
    include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');    
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

         $class = 'even';

    if (!isset($_SESSION)) session_start() ;
      $userUId     = $_SESSION['user_id'] ;
      $principalId = $_SESSION['principal_id'] ;
      $depotId     = $_SESSION['depot_id'] ;
      $systemId    = $_SESSION["system_id"];
      $systemName  = $_SESSION['system_name'];
      
     
      //Create new database object
     $dbConn = new dbConnect(); 
     $dbConn->dbConnection();
     $errorTO = new ErrorTO;
// ******************************************************************************************
if (isset($_POST["PRODC"])) $postProdC=$_POST["PRODC"]; else $postProdC = ''; 

if(isset($_POST['canform'])) {
    return;
}	

if(isset($_POST['finish'])) { 
	
	    if (isset($_POST["PRODUID"])) $prodUid=$_POST["PRODUID"];   else $prodUid = ''; 
	    if (isset($_POST["NPC"]))     $postNewProd=test_input($_POST["NPC"]);       else $postNewProd = '';
	    if (isset($_POST["OLDPROD"])) $postOldProd=test_input($_POST["OLDPROD"]);   else $postOldProd = '';
	    
	    if(trim($postNewProd) <> '' ) {
	           $MaintenanceDAO = new MaintenanceDAO($dbConn);
             $nprDet = $MaintenanceDAO->getProductDetails($principalId, $postNewProd);
             
             if(count($nprDet) == 0) 	{ 
             	
                  $MaintenanceDAO = new MaintenanceDAO($dbConn);
                  $errorTO = $MaintenanceDAO->updateProductDetails($prodUid, $postNewProd, $postOldProd, $userUId);
                  
                  if($errorTO->type == FLAG_ERRORTO_SUCCESS) {
                      ?>
                       <script type='text/javascript'>parent.showMsgBoxInfo('New Product Code - Update Successful')</script> 
                      <?php
                      unset($_POST['firstform']);
                      unset($_POST['finish']);
                  } else {
                       ?>
                       <script type='text/javascript'>parent.showMsgBoxError('New Product Code - Update Failed - Contact Support')</script> 
                       <?php
                       print_r($errorTO) ;
                  	   return ;         	
                  }             	
             } else {
                 ?>
                  <script type='text/javascript'>parent.showMsgBoxError('New Product Code - <?php echo  $postNewProd; ?>  - Already Exists Try again')</script> 
                  <?php
                   unset($_POST['firstform']);
                   unset($_POST['finish']);
             }                       
             
             

      } else {
              ?>
              <script type='text/javascript'>parent.showMsgBoxError('No New Product Code Entered - Try again')</script> 
              <?php
              unset($_POST['firstform']);
              unset($_POST['finish']);
      }
}	
if(isset($_POST['firstform'])) {
   
      if(trim($postProdC) <> '' ) {
              $MaintenanceDAO = new MaintenanceDAO($dbConn);
              $prDet = $MaintenanceDAO->getProductDetails($principalId, $postProdC);
              if(count($prDet) == 1) 	{ ?>
              	    <center>
                       <form name='changeprod' method=post target=''>
                           <table width:"80%"; style="border-none";>
                               <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                                   <td class=head1 colspan="2"; style="text-align:center" >Product Details</td>  
                               </tr>
                               <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                                   <td  colspan="2"; style="text-align:center" >&nbsp;</td>            	
                               </tr>
                               <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                                   <td colspan="1";>Existing Product</td>
                                   <td colspan="1";><?php echo $prDet[0]['product_code']; ?></td> 
                               </tr>	                                   
                               <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                                   <td colspan="1";>&nbsp;<input type="hidden" name="OLDPROD" value=<?php echo $prDet[0]['product_code'];?>></td>
                                   <td colspan="1";><?php echo $prDet[0]['product_description']; ?></td> 
                               </tr>	                                   
                               <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                                   <td colspan="2";>&nbsp;<input type="hidden" name="PRODUID" value=<?php echo $prDet[0]['uid'];?>></td>                                    
                               </tr>	                                   
                               <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                                   <td  colspan="1";>Enter New Product Code</td>
                  	               <td  colspan="1";style="text-align:center"><input type="text" name="NPC" autofocus value=""></td>
                              </tr>                                     
                               <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                                   <td  colspan="2"; style="text-align:center" >&nbsp;</td>            	
                              </tr>
                              <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                                   <td  colspan="2"; style="text-align:center" ><INPUT TYPE="submit" class="submit" name="finish" value= "Update Product">
                                   	                                            <INPUT TYPE="submit" class="submit" name="canform" value= "Cancel"></td>            	
                              </tr>
                              <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                                   <td  colspan="2"; style="text-align:center" >&nbsp;</td>            	
                              </tr>
                           </table>
                       </form>
	                  </center>    	
                <?php
              } else {
                  ?>
                  <script type='text/javascript'>parent.showMsgBoxError('Error Getting Product Details - Try again')</script> 
                  <?php
                  unset($_POST['firstform']);
              } 
      } else {
              ?>
              <script type='text/javascript'>parent.showMsgBoxError('No Product Code Entered - Try again')</script> 
              <?php
              unset($_POST['firstform']);
      }              
	
}
// ******************************************************************************************     
  
if(!isset($_POST['firstform'])) {    ?>
    <center>	
        <FORM name='Select Product' method=post action=''>
            <table width:"720"; style="border:none">        	
               <tr>
                  <td>&nbsp</td>
                  <td>&nbsp</td>
               </tr>
               <tr>
                  <td class=head1 colspan="2"; style="text-align:center;" >Change a Product Code</td>
               </tr>
               <tr>
                  <td>&nbsp</td>
                  <td>&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                  <td colspan="2";>&nbsp</td>
               </tr>	        	
               <tr>
                  <td>&nbsp</td>
                  <td>&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                  <td style="text-align:left";>Enter Product Code</td>
                  <td style="text-align:left";><input type="text" name="PRODC"></td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                  <td colspan="2";>&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                  <td colspan="2"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="firstform" value= "Get Product Details">
                  	                                          &nbsp;&nbsp;<INPUT TYPE="submit" class="submit" name="canform" value= "Cancel"></td>
               </tr>          
               <tr>
                  <td>&nbsp</td>
                  <td>&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                  <td colspan="2";>&nbsp</td>
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
} ?>