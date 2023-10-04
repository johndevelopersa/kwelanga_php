<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER."DAO/transactionDAO.php");
    include_once($ROOT.$PHPFOLDER."properties/Constants.php");
    include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
    include_once($ROOT.$PHPFOLDER.'DAO/StoreDAO.php');
    include_once($ROOT.$PHPFOLDER.'DAO/ManageOrdersDAO.php');
    include_once($ROOT.$PHPFOLDER.'elements/basicSelectElement.php');	
        
    if (!isset($_SESSION)) session_start() ;
      $userUId     = $_SESSION['user_id'] ;
      $principalId = $_SESSION['principal_id'] ;
      $depotId     = $_SESSION['depot_id'] ;
      $systemId    = $_SESSION["system_id"];
      $systemName  = $_SESSION['system_name'];
      
      if (isset($_POST["INVOICE"])) $postINVOICE=test_input($_POST["INVOICE"]); else $postINVOICE = ''; 

      if (isset($_POST["STSTATUS"])) $postSTSTATUS=test_input($_POST["STSTATUS"]); else $postSTSTATUS = '';      
            
      //Create new database object
      $dbConn = new dbConnect(); $dbConn->dbConnection();
?>
<!DOCTYPE html>
<HTML>
	<HEAD>

		<TITLE>Document Selection</TITLE>

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

// *******************************************************************************************************************************************

      $class = 'even';
      
      if (isset($_POST['canform'])) {
          return;	
      }
      
// *******************************************************************************************************************************************

      if (isset($_POST['finishform'])) {
            if($_POST['Warehouse'] <> 'Select New Warehouse') {

                if($postSTSTATUS== 1) {
                     $ManageOrdersDAO = new ManageOrdersDAO($dbConn);
                     $result = $ManageOrdersDAO->updateStoreWarehouse(trim($_POST['psmUid']), trim($_POST['Warehouse']));
                     if($result->type <> 'S') { ?>
                         <script type='text/javascript' >parent.showMsgBoxError('Store Warehouse Update Failed - Contact Kwelanga Support') </script> 
                      <?php 
                     return;
                     } 
                }
                $ManageOrdersDAO = new ManageOrdersDAO($dbConn);
                $result = $ManageOrdersDAO->updateOrderWarehouse($_POST['dUid'], $_POST['Warehouse']);
                if($result->type <> 'S') { ?>
                     <script type='text/javascript' >parent.showMsgBoxError('Order Warehouse Update Failed - Contact Kwelanga Support') </script> 
                      <?php 
                     return;
                } 
                ?>
                <script type='text/javascript'>parent.showMsgBoxInfo('Warehouse Updated Successfully')</script> 
                <?php
                unset($postINVOICE);
                unset($_POST['select']);
                unset($_POST['firstform'] );
                unset($_POST['finishform']); 
      	    } else {  ?>
                   <script type='text/javascript' >parent.showMsgBoxError('No New Warehouse Selected') </script> 
                   <?php 
      	
                   unset($postINVOICE);
                   unset($_POST['select']);
                   unset($_POST['firstform'] );
                   unset($_POST['finishform']); 
            }       
      }
      
// *******************************************************************************************************************************************
      
      if (isset($_POST['firstform'])) {
          if ($postINVOICE !== '') {
              $transactionDAO = new transactionDAO($dbConn);
             	$mfDDU = $transactionDAO->getDocumentDetailsToUpdate($principalId,$postINVOICE);
              if (sizeof($mfDDU)>0) {
                  $ManageOrdersDAO = new ManageOrdersDAO($dbConn);
                  $mfPR = $ManageOrdersDAO->getuserWarehouses($principalId, $userUId, $mfDDU[0]['depot_uid']);
                  if (in_array($mfDDU[0]['document_status_uid'], array(74,75,76))) { ?>
                    <center>
                       <FORM name='Manage Warehouse' method=post action=''>
                          <table width="720"; style="border:none">
                          	<tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
							                   <td width="38%"; style="border:none">&nbsp</td>
								                 <td width="20%"; style="border:none">&nbsp</td>
								                 <td width="20%"; style="border:none">&nbsp</td>
								                 <td width="20%"; style="border:none">&nbsp</td>
								                 <td width="2%" ; style="border:none">&nbsp</td>
							             </tr>
							              <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
							                   <td class="head1" colspan="5"; style="text-align:center; padding: 0 15px 0 20px ; "><strong>Manage Warehouse Details</td>
							             </tr>
								             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
								                   <td Colspan="5">&nbsp</td>
								             </tr>
                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>                               
                                  <td colspan="1"; style="text-align:right"><strong>Customer:</td>
                                  <td colspan="1"; style="text-align:left"><?php echo trim($mfDDU[0]['deliver_name'] ); ?></td>
                                  <td colspan="1"; style="text-align:left">
                                      <input type="hidden" id="dUid" name="dUid"    value=<?php echo ltrim($mfDDU[0]['uid']); ?>
                                  </td>
                                  <td colspan="1"; style="text-align:left"><strong>Document No:</td>                                  
                                  <td colspan="1"; style="text-align:left"><?php echo ltrim($mfDDU[0]['document_number'],'0' ); ?></td>
                             </tr>
                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>                               
                                  <td colspan="5"; style="text-align:left">&nbsp</td>
                             </tr>
                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>                               
                                  <td colspan="1"; style="text-align:right"><strong>Existing Warehouse:</td>
                                  <td colspan="1"; style="text-align:left"><?php echo trim($mfDDU[0]['Depot'] ); ?></td>
                                  <td colspan="1"; style="text-align:left"><input type="hidden" id="psmUid" name="psmUid" value='<?php echo $mfDDU[0]['StoreUid']; ?>'</td>
                                  <td colspan="1"; style="text-align:left"><strong>New Warehouse:</td>
                                  <td>
                                      <select name="Warehouse" id="Warehouse" size="1">
                                             <option value="Select New Warehouse">Select New Warehouse</option>
                                             <?php foreach($mfPR as $row) { ?>
                                                  <option value="<?php echo trim($row['uid']); ?>"><?php echo trim($row['name']); ?></option>
                                             <?php } ?>
                                            </select>
                                  </td>
                             </td>       
                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>                               
                                  <td colspan="5"; style="text-align:left">&nbsp</td>
                             </tr>                                  
                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>                               
                                  <td colspan="1"; style="text-align:right"><strong>Update Warehouse Store Master:</td>
                                  <td colspan="1"; style="text-align:left;"><?php $lableArr = array('No','Yes');
          		                                                $valueArr = array('2','1');
          		                                                BasicSelectElement::buildGenericDD('STSTATUS', $lableArr,$valueArr, $postSTSTATUS, "N", "N", null, null, null);?>
                                  </td>
                                  <td colspan="3"; style="text-align:left">&nbsp</td>
                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>                               
                                  <td colspan="5"; style="text-align:left">&nbsp</td>
                             </tr>
                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";> 
                                  <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="finishform" value= "Update Warehouse Details">
                                                             <INPUT TYPE="submit" class="submit" name="canform"   value= "Cancel"></td>
                             </tr>
                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>                               
                                  <td colspan="5"; style="text-align:left">&nbsp</td>
                             </tr>
                          </table>  
                       </FORM>           
                    	
                    </center>	
                  	
                  <?php 
                  } else { 	?>
                     <script type='text/javascript'>parent.showMsgBoxError('Document Aready Invoiced Or In Pick. Warehouse Cannot Be Changed')</script> 
                  <?php 
                     unset($_POST['firstform']);
              			 unset($_POST['finishform']);
                     
                  }
              } else { ?>
                 <script type='text/javascript'>parent.showMsgBoxError('Document Number Not Found')</script> 
                 <?php 
                 unset($_POST['firstform']);
                 unset($_POST['select']);
              }              
          }  else { ?>
              <script type='text/javascript'>parent.showMsgBoxError('Document Number Cannot Be Blank')</script> 
          <?php
              unset($_POST['firstform']);
              unset($_POST['finishform']);      	
          }  
      }    
// *******************************************************************************************************************************************

if(!isset($_POST['firstform']) && !isset($_POST['finishform'])) { ?>
    <center>
       <FORM name='Select Invoice' method=post action=''>
            <table width="720"; style="border:none">
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                   <td class="head1" colspan="4"; style="text-align:center; padding: 0 15px 0 20px ; "><strong>Manage Warehouse On Order</td>
                   	<td colspan="4">&nbsp</td>
             </tr>
             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                   <td Colspan="5">&nbsp</td>
             </tr>      	
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td class="det3" style="text-align:center; color:Red;">*Enter Required Document Number</td>
                 <td colspan="4">&nbsp</td>
               </tr>        	
            </table>
            <table width="720"; style="border:none" >        	
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td width="38%"; style="border:none">&nbsp</td>
                 <td width="20%"; style="border:none">&nbsp</td>
                 <td width="20%"; style="border:none">&nbsp</td>
                 <td width="20%"; style="border:none">&nbsp</td>
                 <td width="2%" ; style="border:none">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                 <td style="text-align:right";><strong>Enter Invoice Number:</td>
                 <td colspan="4"; style="text-align:left"><input type="text" name="INVOICE" placeholder= "Invoice Number"></td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="5">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="firstform" value= "Get Invoice Details">
                                                             <INPUT TYPE="submit" class="submit" name="canform"   value= "Cancel"></td>
               </tr>          
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="5">&nbsp</td>
               </tr>  
 			    	</table>
		   </form>
    </center> 
	</body>       
 </HTML>
<?php 
}

// *******************************************************************************************************************************************

 function test_input($data) {

  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  
  return $data;
 }
?> 