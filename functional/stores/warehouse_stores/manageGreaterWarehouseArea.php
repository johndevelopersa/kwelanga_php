<?php 
	include_once('ROOT.php'); 
	include_once($ROOT.'PHPINI.php');	
	include_once('manageGreaterWarehouseAreaClass.php');
	include_once($ROOT.$PHPFOLDER."libs/GUICommonUtils.php");
	require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
	include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
	include_once($ROOT.$PHPFOLDER.'DAO/manageGreaterWarehouseAreaDAO.php');
	include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
	
	if (!isset($_SESSION)) session_start();
    
     	$principalID = $_SESSION['principal_id'] ;
      $depotID     = $_SESSION['depot_id'] ;
      

	    $dbConn  = new dbConnect(); 
      $dbConn->dbConnection();
      $errorTO = new ErrorTO;
	
?>
<!DOCTYPE html>
		<HTML>
   		<HEAD>

				<TITLE>Simple Form</TITLE>

				<link href='<?php echo $ROOT.$PHPFOLDER ?>css/1_kwelanga.css' rel='stylesheet' type='text/css'>
				<link href='<?php echo $ROOT.$PHPFOLDER ?>css/kos_standard_screen2.css' rel='stylesheet' type='text/css'>
				<script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/jquery.js"></script>
				<script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/dops_global_functions.js"></script>

		    <style>
		      td.head1 {font-weight:normal;
		                font-size:20px;text-align:left; 
		                font-family: Calibri, Verdana, Ariel, sans-serif; 
		                padding: 0 150px 0 150px }
		                
		      td.det1  {border-style:none; 
		                text-align: left; 
		                font-weight: bold; 
		                font-size: 13px;
		                padding: 0 150px 0 150px  }
		                
		      table.box {border:collapse;
		      	         border: 2px solid; 
		      	         border-color: #990000; 
		      	         background: #fcecec }          
		    	
		    	</style>

		   </HEAD>
		      <body>

	<?php
	
// *******************************************************************************************************************************************

	  if (isset($_POST['CANFORM'])) {
          return;    
    }
    
// *******************************************************************************************************************************************    

 if (isset($_POST['BACKFORM'])) {
       unset($_POST['MODWHAREA']); 
       unset($_POST['ADDWHAREA']);
    } 
	
// *******************************************************************************************************************************************	
   
   //addAreaForm()
   
 	 //addAreaForm Button = formADDAREA
 	 
	if (isset($_POST["ADDWHAREA"])) {
		
		
    $manageGreaterWarehouseArea = new manageGreaterWarehouseArea();
    $a = $manageGreaterWarehouseArea->addGreaterArea($depotID);
		
  }
  
	if (isset($_POST["formADDAREA"])) { 
		       
       if (isset($_POST["txtDELIVERYAREANAME"])) $postDelAreaName  = (test_input($_POST["txtDELIVERYAREANAME"])); else $postDelAreaName = '';
       if (isset($_POST["txtGREATERAREA"]))      $postwhArea       = (test_input(substr($_POST['txtGREATERAREA'],0,strpos($_POST['txtGREATERAREA'],'-'))));      else $postwhArea = '';
       if (isset($_POST["txtNDD"]))              $postwhNdd        = (test_input(substr($_POST['txtNDD'],0,strpos($_POST['txtNDD'],'-'))));              else $postwhNdd = '';
       
       
       		if($postDelAreaName != '') {
                if(strlen($postDelAreaName) > 3) { 								  	 
								     if($postwhArea <> 'Select Warehouse Area' ) {
                          if($postwhNdd <> 'Select NDD' ) {
                          	
                          	   $manageGreaterWarehouseAreaDAO = new greaterWarehouseAreaDAO($dbConn);
					                     $aName = $manageGreaterWarehouseAreaDAO->checkDeliveryArea($postDelAreaName); 
					        
					    	 	             if(count($aName) ==  0){ 
					    	 	             	
					    	 	             	  $manageGreaterWarehouseAreaDAO = new greaterWarehouseAreaDAO($dbConn);
       														$errorTO = $manageGreaterWarehouseAreaDAO->insertDeliveryArea($postDelAreaName, $postwhArea, $postwhNdd);
       														
       														?>
        														<script type='text/javascript'>parent.showMsgBoxInfo('Success! New Area Added')</script>  
        													<?php
					    	 	             	
					    	 	             	
					    	 	             } else { ?>
					    	 	                    <script type='text/javascript'>parent.showMsgBoxError('Error! The area you have entered already exists<br><br> Try Again')</script>
							    	 		              <?php
							                   
							    	 		               unset($_POST['MODWHAREA']);
					                             unset($_POST['ADDWHAREA']);
					    	 	             }
     					  	        } else {?>
							                 <script type='text/javascript'>parent.showMsgBoxError('NDD Not Selected (x001)<br><br> Try Again')</script>
							                 <?php	
							           
							                 unset($_POST['MODWHAREA']);
							                 unset($_POST['ADDWHAREA']);
							   	        }
								     } else {
								       	  ?>
								          <script type='text/javascript'>parent.showMsgBoxError('Warehouse Delivery Area Not Selected (x001)<br><br> Try Again')</script>
								           <?php	
								           
							             unset($_POST['MODWHAREA']);
							             unset($_POST['ADDWHAREA']);
					    	     }
								} else {
									?>
			            <script type='text/javascript'>parent.showMsgBoxError('Area too Short <br><br> Try Again')</script>
			            <?php
			           
			            unset($_POST['MODWHAREA']);
			            unset($_POST['ADDWHAREA']);
  	 	
  	 	   	 			}  
  	 	   	} else { 
  	 	     ?>
			     <script type='text/javascript'>parent.showMsgBoxError('Warehouse Delivery Area Blank <br><br> Try Again')</script>
			     <?php
			           
		       unset($_POST['MODWHAREA']);
           unset($_POST['ADDWHAREA']);
          }  
  }      
// *******************************************************************************************************************************************

	if(isset($_POST['UPDATEWAREHOUSE'])) { 
		
		if (isset($_POST["DELIVERYAREA"]))   $postDelAreaName   = test_input($_POST["DELIVERYAREA"]); else $postDelAreaName     = '';
		if (isset($_POST["WHAREA"]))    		 $postWhArea    = test_input($_POST["WHAREA"]);    				else $postWhArea      = '';
    if (isset($_POST["NSTATUS"])) 			 $postNSTATUS = test_input($_POST["NSTATUS"]); 						else $postNSTATUS   = '';  
    if (isset($_POST["WhAreaUID"])) 		 $postWAUID = test_input($_POST["WhAreaUID"]); 						else $postWAUID   = ''; 
    
       if(strlen($postDelAreaName) > 3) {
		 		 			
		 		 			$manageGreaterWarehouseAreaDAO = new greaterWarehouseAreaDAO($dbConn);
		 					$errorTO = $manageGreaterWarehouseAreaDAO->updateWarehouseArea($postDelAreaName, $postWhArea, $postNSTATUS, $postWAUID); 

              if ($errorTO->type==FLAG_ERRORTO_SUCCESS) { ?>
                    <script type='text/javascript'>parent.showMsgBoxInfo('Warehouse Delivery Area Updated Successfully')</script>  
                    <?php
                    unset($_POST['MODWHAREA']); 
                    unset($_POST['ADDWHAREA']);
              } else { ?>
                   	<script type='text/javascript'>parent.showMsgBoxError('Warehouse Delivery Area Update Failed <br><br> Contact Kwelanga Support')</script>
                    <?php
                    unset($_POST['MODWHAREA']); 
                    unset($_POST['ADDWHAREA']);
              }      
        } else { ?>
             <script type='text/javascript'>parent.showMsgBoxError('Warehouse Delivery Area Name Blank or Too Short (Minimum 4) <br><br> Try Again')</script>
             <?php	
             unset($_POST['MODWHAREA']); 
             unset($_POST['ADDWHAREA']);
        }

}


// *******************************************************************************************************************************************

	if(isset($_POST['SELMOD'])) {
	
	//echo ($_POST['SELMOD']);
	
	 ?>
			<center>
				<form name='Maintain Warehouse Delivery Area' method=post action='' onload='setFocusTPselect()'>
					<table width="720"; style="border:none">
						 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td style="width:30%; border:none;">&nbsp</td>
                     <td style="width:20%; border:none;">&nbsp</td>
                     <td style="width:20%; border:none;">&nbsp</td>
                     <td style="width:30%; border:none;">&nbsp</td>
                 </tr>
                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td class="head1" colspan="4"; style="text-align:center; padding: 0 15px 0 20px ; "><strong>Update Warehouse Delivery Area</td>
                 </tr> 
                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td Colspan="4">&nbsp</td>
                 </tr>
                 <?php
                 if($_POST['CURSTAT'] == "D") {
                    $dCheck = 'CHECKED';
                    $aCheck = '';
                 } else {
                    $dCheck = '';
                    $aCheck = 'CHECKED';
                 } ?>
                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td Colspan="1" >&nbsp</td>
                     <td class="head2" colspan="1" style="text-align:left; padding: 0 0px 0 0px ; "><strong>Warehouse Delivery Area</td>
                     <td Colspan="2" ><INPUT TYPE="TEXT" size="40" name="DELIVERYAREA" id="DELIVERYAREA" value= "<?php echo trim(substr($_POST["SELMOD"], 0, strpos($_POST["SELMOD"],"-")));?>"></td>
                 </tr>
                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td Colspan="4">&nbsp<input type="hidden" id="WHAREA" name="WHAREA" value= <?php echo trim(substr($_POST["SELMOD"], strpos($_POST["SELMOD"],"*")-5, -6));?>></td>
                 </tr>
                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td Colspan="4">&nbsp<input type="hidden" id="WhAreaUID" name="WhAreaUID" value= <?php echo trim(substr($_POST["SELMOD"], strpos($_POST["SELMOD"], "*")-9, -10));?>></td>
                 </tr>
                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td Colspan="1">&nbsp</td>
                      <td class="det2" colspan="1" style="text-align:right; border: none; padding: 0px 25px 0px 25px;"><input type="radio" id="ACTIVE"  name="NSTATUS" value="A" <?php echo $aCheck; ?>><label class="label" for="ACTIVE">Active</td>
                      <td class="det2" colspan="1" style="text-align:right; border: none; padding: 0px 25px 0px 25px;"><input type="radio" id="DELETED" name="NSTATUS" value="D" <?php echo $dCheck; ?>><label class="label" for="DELETED">Deleted</label></td>
                     <td Colspan="1">&nbsp</td>
                 </tr>  
                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td Colspan="4">&nbsp</td>
                 </tr>
                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>"> 
                     <td colspan="4"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="UPDATEWAREHOUSE"   value= "Update Warehouse Delivery Area">
                     	                                           <INPUT TYPE="submit" class="submit" name="BACKFORM"   value= "Back">
                                                                 <INPUT TYPE="submit" class="submit" name="CANFORM"   value= "Cancel"></td>
                 </tr> 
                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td Colspan="4">&nbsp</td>
                 </tr>  
					</table>
				</form>
		 </center>
	 <?php
	}

// *******************************************************************************************************************************************

	if (isset($_POST['MODWHAREA']) || isset($_POST["SUBMITFILTER"])) {
		
		if (isset($_POST["STATUS"])) $postStat = test_input($_POST["STATUS"]);  else $postStat = 'A';
    if (isset($_POST["WHDELIVERYAREA"]))  $postWhDeliveryArea = test_input($_POST["WHDELIVERYAREA"]); else $postWhDeliveryArea = 'XXXXX';
		
		$manageGreaterWarehouseArea = new manageGreaterWarehouseArea();
    $a = $manageGreaterWarehouseArea->modifyWarehouseArea($postWhDeliveryArea, $postStat, $depotID);
			
		
	
	}


// *******************************************************************************************************************************************

		//Radio button clicked, call addGreaterArea form
		
		if (isset($_POST['QADDWHAREA'])) {
			
	 	
       $manageGreaterWarehouseArea = new manageGreaterWarehouseArea();
       $a = $manageGreaterWarehouseArea->addGreaterArea($depotID);
       
       $manageGreaterWarehouseArea = new manageGreaterWarehouseArea();
       $b = $manageGreaterWarehouseArea->addGreaterArea();

		}
	
// *******************************************************************************************************************************************	

	//Both buttons are not set. Therfore, if one is clicked, access the form required
	
	  if (!isset($_POST['ADDWHAREA'])  && !isset($_POST['MODWHAREA']) && !isset($_POST['SUBMITFILTER'])  && !isset($_POST['SELMOD']) ){
    	 
    	$manageGreaterWarehouseArea = new manageGreaterWarehouseArea();
  		$a = $manageGreaterWarehouseArea->firstForm();  
       
       }
       
// *******************************************************************************************************************************************	

	function test_input($data) {

		  $data = trim($data);
		  $data = stripslashes($data);
		  $data = htmlspecialchars($data);
		  if($data=='') { $data=0; } 
	    
	   return $data;
	 } ?> 