<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    include_once($ROOT.$PHPFOLDER ."DAO/db_Connection_Class.php");
    include_once($ROOT.$PHPFOLDER ."DAO/SgxImportDAO.php");
    include_once($ROOT.$PHPFOLDER ."properties/Constants.php");
		include_once($ROOT.$PHPFOLDER .'libs/GUICommonUtils.php');

    //Create new database object
    $dbConn = new dbConnect(); 
    $dbConn->dbConnection();
?>  

<!DOCTYPE html>
<html>
	  <head>
	  	  <title>Import Transaction Management</title>
            <link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/1_kwelanga.css' rel='stylesheet' type='text/css'>
            <script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/jquery.js"></script>
            <script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/dops_global_functions.js"></script>
        <style>
    	     a.ac1 {text-align:left; 
    	     	      font-weight:normal; 
    	     	      color:red;  }
    	
    	     a.ac2 {text-align:left; 
    	     	      font-weight:normal; 
    	     	      color:green;  }  
        </style>
    </head>
	  <body>
	  	 <?php
	  	   if(isset($POST['canform'])) { ?>
	  	   	     <script>alert("Account Number Update Cancelled");</script>		    
               <?php
               return;
	  	   }
	  	   if($action == 'clear') { 
	  	 	     // Get Transaction to clear
	  	 	      $sgxUpdate = new SgxImportDAO($dbConn);
              $manageTx  = $sgxUpdate->getSFDTransactionToManage($docmastId);
    	      	
    	      	$class = 'odd';
         ?>
	  	 	       <center>	
	                  <FORM name='Manage Transaction' method=post action=<?php echo $ROOT.$PHPFOLDER; ?>functional\extracts\errorManagement\clearSmartEventTransaction.php>
                         <table width="80%" style="border:none">        	
                            <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                            	  <td colspan="1";></td>
                                <td rowspan="3"; colspan="1"; ><img src="<?php echo $ROOT; ?>kwelangaweb\images\kos\Kwelanga PNG Transparent version 2.png" style="width:120px; height:75px; float:left;" style="width:120px; height:75px; float:left;" ></td>
                                <td colspan="3";>&nbsp</td>
                            </tr>
                            <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                                <td colspan="1";>&nbsp</td>
                                <td colspan="3"; style="padding-left:50px; text-align:left; font-size:16px; font-weight:bold;" >Kwelanga Online Line Transaction Management </td>
                            </tr>
                            <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                                <td colspan="1";>&nbsp</td>
                                <td colspan="1";>&nbsp</td>
                                <td colspan="3"; style="text-align:left; font-size: 18px; font-weight:Bold;" >&nbsp;</td>
                            </tr>
                            <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                                 <td width="10%"; style="border:none";>&nbsp</td>
                                 <td width="15%"; style="border:none";>&nbsp</td>
                                 <td width="30%"; style="border:none";>&nbsp</td>
                                 <td width="30%"; style="border:none";>&nbsp</td>
                                 <td width="15%"; style="border:none";>&nbsp</td> 	 	 	
                            </tr>
                            <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                                <td colspan="5";>&nbsp</td>
                            </tr>	       	
                            <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                            	  <td>&nbsp</td>
                            	  <td colspan="3"; style="text-align:center; font-weight:bold; font-size:14px">Transaction Details to Clear</td>
                                <td>&nbsp</td>
                            </tr>        	
                            <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                                <td colspan="5";>&nbsp</td>
                            </tr>	  
                            <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                            	  <td>&nbsp</td>
                            	  <td colspan="1"; style="font-weight:normal; font-size:11px">Document Number</td>
                                <td colspan="2"; style="font-weight:normal; font-size:11px"><?php echo $manageTx[0]['document_number'];?></td>
                                <td><input type="hidden" id="txUid" name="txUid" value='<?php echo $manageTx[0]['seUid']; ?>'</td>
                            </tr>   
                            <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                                <td colspan="5";>&nbsp</td>
                            </tr>	  
                            <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                            	  <td>&nbsp</td>
                            	  <td colspan="1"; style="font-weight:normal; font-size:11px">Customer</td>
                                <td colspan="2"; style="font-weight:normal; font-size:11px"><?php echo $manageTx[0]['deliver_name'];?></td>
                                <td>&nbsp</td>
                            </tr>                               
                            <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                                <td colspan="5";>&nbsp</td>
                            </tr>	                              
                            <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                            	  <td>&nbsp</td>
                            	  <td colspan="1"; style="font-weight:normal; font-size:11px">Error / Reason</td>
                                <td colspan="2"; style="font-weight:normal; font-size:11px"><?php echo $manageTx[0]['general_reference_2'];?></td>
                                <td>&nbsp</td>
                            </tr>   
                            <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                                <td colspan="5";>&nbsp</td>
                            </tr>	  
                            <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                            	  <td>&nbsp</td>
                                <td colspan="3"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="select"    value= "Clear Transaction">
                                	                                          <INPUT TYPE="submit" class="submit" name="canform"   value= "Cancel"></td>
                                <td>&nbsp</td>
                            </tr>   
                            <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                                <td colspan="5";>&nbsp</td>
                            </tr>	  

                         </table>
		                </form>
               </center> 
         <?php
  	
         } else {
	  	 	     // Get Transaction to manage
    	        $sgxUpdate = new SgxImportDAO($dbConn);
              $manageTx  = $sgxUpdate->getSFDTransactionToManage($docmastId);
    	      	$class = 'odd';
              ?>
	  	 	      <center>	
	                  <FORM name='Manage Transaction' method=post action=<?php echo $ROOT.$PHPFOLDER; ?>functional\extracts\errorManagement\updateSpfAccountNumber.php>
                         <table width="80%" style="border:none">        	
                            <tr class="<?php echo GUICommonUtils::styleEO($class);?>"
                            	  <td colspan="1";></td>
                                <td rowspan="3"; colspan="1"; ><img src="<?php echo $ROOT; ?>kwelangaweb\images\kos\Kwelanga PNG Transparent version 2.png" style="width:120px; height:75px; float:left;" style="width:120px; height:75px; float:left;" ></td>
                                <td colspan="3";>&nbsp</td>
                            </tr>
                            <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                                <td colspan="1";>&nbsp</td>
                                <td colspan="3"; style="padding-left:50px; text-align:left; font-size:16px; font-weight:bold;" >Kwelanga Online Line Transaction Management </td>
                            </tr>
                            <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                                <td colspan="1";>&nbsp</td>
                                <td colspan="1";>&nbsp</td>
                                <td colspan="3"; style="text-align:left; font-size: 18px; font-weight:Bold;" >&nbsp;</td>
                            </tr>
                            <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                                 <td width="10%"; style="border:none";>&nbsp</td>
                                 <td width="15%"; style="border:none";>&nbsp</td>
                                 <td width="30%"; style="border:none";>&nbsp</td>
                                 <td width="30%"; style="border:none";>&nbsp</td>
                                 <td width="15%"; style="border:none";>&nbsp</td> 	 	 	
                            </tr>
                            <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                                <td colspan="5";>&nbsp</td>
                            </tr>	        	
                            <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                            	  <td>&nbsp</td>
                            	  <td colspan="3"; style="text-align:left; font-weight:bold; font-size:14px">Transaction Details to Update</td>
                                <td>&nbsp</td>
                            </tr>        	
                            <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                                <td colspan="5";>&nbsp</td>
                            </tr>	  
                            <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                            	  <td>&nbsp</td>
                            	  <td colspan="1"; style="font-weight:normal; font-size:11px">Document Number</td>
                                <td colspan="2"; style="font-weight:normal; font-size:11px"><?php echo $manageTx[0]['document_number'];?></td>
                                <td><input type="hidden" id="txUid" name="txUid" value='<?php echo $manageTx[0]['seUid']; ?>'</td>
                            </tr>   
                            <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                                <td colspan="5";>&nbsp</td>
                            </tr>	  
                            <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                            	  <td>&nbsp</td>
                            	  <td colspan="1"; style="font-weight:normal; font-size:11px">Customer</td>
                                <td colspan="2"; style="font-weight:normal; font-size:11px"><?php echo $manageTx[0]['deliver_name'];?></td>
                                <td>&nbsp</td>
                            </tr>                               
                            <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                                <td colspan="5";>&nbsp</td>
                            </tr>	                              
                            <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                            	  <td>&nbsp</td>
                            	  <td colspan="1"; style="font-weight:normal; font-size:11px">Error / Reason</td>
                                <td colspan="2"; style="font-weight:normal; font-size:11px"><?php echo $manageTx[0]['general_reference_2'];?></td>
                                <td><input type="hidden" id="PSUID" name="PSUID" value='<?php echo $manageTx[0]['psmUid']; ?>'></td>
                            </tr>   
                            <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                                <td colspan="5";>&nbsp;</td>
                            </tr>	
                            <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                            	  <td><input type="hidden" id="SFDENTITY" name="SFDENTITY" value='<?php echo $manageTx[0]['entity_uid']; ?>'></td>
                            	  <td colspan="1"; style="font-weight:normal; font-size:11px">Account number</td>
                                <td colspan="2"; style="font-weight:normal; font-size:11px"><input type="text" id="OMNIACC" name="OMNIACC" value='<?php echo $manageTx[0]['value']; ?>'></td>
                                <td><input type="hidden" id="SFFUID" name="SFFUID" value='<?php echo $manageTx[0]['sffUid']; ?>'>
                            </tr>
                            <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                                <td colspan="5";>&nbsp</td>
                            </tr>	  
                            <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                            	  <td>&nbsp</td>
                                <td colspan="3"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="select"    value= "Update Customer Account">
                                	                                          <INPUT TYPE="submit" class="submit" name="canform"   value= "Cancel"></td>
                                <td>&nbsp</td>
                            </tr>   
                            <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                                <td colspan="5";>&nbsp</td>
                            </tr>	  

                         </table>
		                </form>
              </center>          
         <?php
         } ?> 	
	  </body>
</html>