<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER.'elements/basicSelectElement.php');	
    include_once($ROOT.$PHPFOLDER."properties/Constants.php");
    include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
    include_once($ROOT.$PHPFOLDER.'DAO/CustomDAO.php');
    include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
        
    if (!isset($_SESSION)) session_start() ;
      $userUId     = $_SESSION['user_id'] ;
      $principalId = $_SESSION['principal_id'] ;
      $systemId    = $_SESSION["system_id"];
      $systemName  = $_SESSION['system_name'];
      
      //Create new database object
      $dbConn = new dbConnect(); $dbConn->dbConnection();
      
      $errorTO = new ErrorTO;
      
      $disCode = '';
      $distype = '';
      $disVal  = '';
      
      $disUid  = ((isset($_GET["DOCUID"]))?$_GET["DOCUID"]:"");
      $disTask = ((isset($_GET["TASK"]))?$_GET["TASK"]:"");
      
      if($disTask == 'UPDATE') {
            $ManageDiscounts = new CustomDAO($dbConn);
            $discountRec    = $ManageDiscounts->getOneDiscountRecord($disUid);
      	
    	      $disCode   = $discountRec[0]['code'];
    	      $distype   = $discountRec[0]['type'];
            $disVal    = $discountRec[0]['amount'];
            $disStatus = $discountRec[0]['status'];
      } else {
            $disCode   = '';
            $distype   = '';
            $disVal    = '';
            $disStatus = 'A' ;
      }
      if(isset($_POST['BACK'])) { ?>
            <script type='text/javascript'>window.close()</script> 
           <?php    
      }     
      if(isset($_POST['UPDATEDISCOUNT'])) {
      	
      	     if($_POST['DISACT'] == 'UPDATE') {
      	
      	         if($_POST['DISVALUE'] <= 0 || $_POST['DISVALUE'] > 100) { ?>
                      <script type='text/javascript' >alert('Discount Value Error - Try Again')</script>
                 <?php
       	         } else {
                      $ManageDiscounts = new CustomDAO($dbConn);
                      $errorTO         = $ManageDiscounts->updateDiscountRecord($_POST['DISUID'], 
                                                                                $_POST['DISTYPE'],
                                                                                $_POST['DISCDE'],
                                                                                $_POST['DISVALUE'],
                                                                                $_POST['DISSTATUS']);
                                                                                                                                                              
                     if($errorTO-type =='S') {?>
                             <script type='text/javascript' >alert('Discount Update Successful')</script>
                             <script type='text/javascript'>window.close()</script> 
                             <?php
                     } else {?>
                             <script type='text/javascript' >alert('Discount Update Failed - Contact Support F90001')</script>
                             <script type='text/javascript'>window.close()</script> 
                             <?php
                     }                                                         
       	         }
       	    } else {
       	          	
      	         if($_POST['DISVALUE'] <= 0 || $_POST['DISVALUE'] > 250) { ?>
                      <script type='text/javascript' >alert('Discount Value Error - Try Again')</script>
                 <?php      	
                 } elseif (trim($_POST['DISCDE']) == '') { ?>
                      <script type='text/javascript' >alert('Discount Code Blank - Try Again')</script>
                 <?php      	
                  } elseif (trim($_POST['DISSTATUS']) == 'Deleted') { ?>
                      <script type='text/javascript' >alert('Inserting a Deleted Record  ?? - Try Again')</script>
                 <?php      	                	
                  } else {
                        $ManageDiscounts = new CustomDAO($dbConn);
                        $errorTO         = $ManageDiscounts->insertDiscountRecord($_POST['DISTYPE'],
                                                                                  $_POST['DISCDE'],
                                                                                  $_POST['DISVALUE'],
                                                                                  $_POST['DISSTATUS']);
                                                                                                                                                              
                       if($errorTO-type =='S') {?>
                             <script type='text/javascript' >alert('Discount Insert Successful')</script>
                             <script type='text/javascript'>window.close()</script> 
                             <?php
                       } else { ?>
                             <script type='text/javascript' >alert('Discount Insert Failed - Contact Support F90001')</script>
                             <script type='text/javascript'>window.close()</script> 
                             <?php
                       } 
                  }	
           }       
      }

?>
<!DOCTYPE html>
<HTML>
	<HEAD>

		<TITLE>Document Selection</TITLE>

    <link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/kos_standard_screen2.css' rel='stylesheet' type='text/css'> 
		<link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/1_kwelanga.css' rel='stylesheet' type='text/css'>
		<script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/jquery.js"></script>
		<script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/dops_global_functions.js"></script>

    <style>
    	
    	</style>

		</HEAD>
<body>
      <center>
         <form name='Manage Discounts' method=post action=''>
            <table width="500"; style="border-none";>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                   <td class="head1" colspan="5"; style="text-align:center;">Manage Shopify Discounts</td>
               </tr>
              <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td class="det1" style="width: 10% border:none;">&nbsp</td>
                 <td class="det1" style="width: 25% border:none; text-align:left">&nbsp;</td>
                 <td class="det1" style="width: 25% border:none; text-align:left">&nbsp;</td>
                 <td class="det1" style="width: 25% border:none; text-align:left">&nbsp;</td>
                 <td class="det1" style="width: 15% border:none; text-align:left">&nbsp;</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                   <td Colspan="5" style="border:none;">&nbsp</td>
               </tr>  
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                   <td class="det1" style="border:none;">&nbsp</td>
                   <td class="det1" style="border:none;">Discount&nbsp;Code</td>
                   <td class="det1" style="border:none;">Type</td>
                   <td class="det1" style="border:none;">Value</td>
                   <td class="det1" style="border:none;">&nbsp</td>
               </tr>  
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                   <td Colspan="5" style="border:none;"><input type="hidden" id="DISACT" name="DISACT" value='<?php echo $disTask; ?>'</td>
               </tr>  

               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                   <?php
                   if($disTask == 'UPDATE') { ?>
                        <td class="det1" style="border:none;"><input type="hidden" id="DISCDE" name="DISCDE" value='<?php echo $disCode; ?>'>
                        	                                    <input type="hidden" id="DISUID" name="DISUID" value='<?php echo $disUid; ?>'>
                       	</td>
                        <td class="det1" style="text-align:left; border:none;"><?php echo $disCode;?></td>
                   <?php                   	
                   } else { ?>
                      	<td class="det1" style="border:none;">&nbsp</td>
                       <td class="det1" style="border:none;"><input type="text" name="DISCDE" value="<?php echo $disCode;?>"></td>                   	
                   <?php
                   } 
                   if($distype = 'Percentage') { ?>
                       <td colspan="1"; style="text-align:left;"><?php $lableArr = array('Percentage', 'Fixed Amount');
                                                                       $valueArr = array('1','2');
                                                                       BasicSelectElement::buildGenericDD('DISTYPE', $lableArr,$valueArr, $DISTYPE, "N", "N", null, null, null);?>
                   </td>                   	
                   <?php
                   } else { ?>
                      <td colspan="1"; style="text-align:left;"><?php $lableArr = array('Fixed Amount', 'Percentage');
          		                                                         $valueArr = array('2','1');
          		                                                         BasicSelectElement::buildGenericDD('STSTATUS', $lableArr,$valueArr, $DISTYPE, "N", "N", null, null, null);?>                   	                   	
                   </td>                   	
                   <?php
                   }  ?>               
                   <td class="det1" style="border:none; text-align:left;"><input type="text" name="DISVALUE" value="<?php echo $disVal;?>"></td>
                   <td class="det1" style="border:none;">&nbsp</td>

               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                   <td Colspan="5" style="border:none;">&nbsp</td>
               </tr>
               <?php 
               if($disStatus == 'A') { $aChecked = 'checked'; $dChecked = ''; } else { $aChecked = ''; $dChecked = 'checked';}
               ?>  
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                   <td class="det1" style="border:none;">&nbsp</td>
                   <td class="det1" style="border:none;">
                        <label for="Active">Active</label>
                        <INPUT TYPE="radio" name="DISSTATUS" id="Active" value= 'Active' <?php echo $aChecked; ?> ></td>
                   <td class="det1" style="border:none;">&nbsp</td>
               	   <td class="det1" style="border:none;">
                        <label for="Deleted">Deleted</label>
                        <INPUT TYPE="radio" name="DISSTATUS" id="Deleted" value= 'Deleted' <?php echo $dChecked; ?> ></td>
                   <td class="det1" style="border:none;">&nbsp</td>
               </tr>  
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                   <td Colspan="5" style="border:none;">&nbsp</td>
               </tr> 
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";> 
                   <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="UPDATEDISCOUNT" value= "Update Discount">
                                                               <INPUT TYPE="submit" class="submit" name="BACK"   value= "Close Window"></td>

                   </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                   <td Colspan="5" style="border:none;">&nbsp</td>
               </tr> 
            </table>
         </form>
    </center>
	</body>       
 </HTML>
<?php


?>     