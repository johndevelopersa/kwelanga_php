<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER.'elements/basicSelectElement.php');	
    include_once($ROOT.$PHPFOLDER."properties/Constants.php");
    include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
    include_once($ROOT.$PHPFOLDER.'DAO/CustomDAO.php');
        
    if (!isset($_SESSION)) session_start() ;
      $userUId     = $_SESSION['user_id'] ;
      $principalId = $_SESSION['principal_id'] ;
      $systemId    = $_SESSION["system_id"];
      $systemName  = $_SESSION['system_name'];
      
      //Create new database object
      $dbConn = new dbConnect(); $dbConn->dbConnection();
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
<?php

	
      $ManageDiscounts = new CustomDAO($dbConn);
      $discountlist    = $ManageDiscounts->getAllDiscountRecords();
      
      if($discountlist == 0) { ?>
            <script type='text/javascript'>parent.showMsgBoxError('Nothing To Update<br><br>Contact Support')</script>
            <?php 	
      	
      	    return; 
      }
      if(isset($_POST['BACK'])) {
      	return;
      }
      if(isset($_POST['MANAGEDISCOUNT'])) {
            if(isset($_POST['CHANGEDIS'])) {  
            	       $uidToUpd = $_POST['CHANGEDIS'];
            	       ?>
                    <script type='text/javascript'>window.open('updateAddIdoDiscounts.php?DOCUID=<?php echo $uidToUpd;?>&TASK=UPDATE','myOH','scrollbars=yes,width=800,height=400,resizable=yes')</script>
            	
            	   <?php            	   
            	   unset($_POST['MANAGEDISCOUNT']);
            	   unset($_POST['CHANGEDIS']);
            } else { ?>
                  <script type='text/javascript'>parent.showMsgBoxError('No Discount Selected to Manage<br><br>Try Again')</script>
            <?php
                  unset($_POST['MANAGEDISCOUNT']);
                  unset($_POST['CHANGEDIS']);
            } 
      }
      if(isset($_POST['ADDDISCOUNT'])) { ?>
                    <script type='text/javascript'>window.open('updateAddIdoDiscounts.php?DOCUID=<?php echo $uidToUpd;?>&TASK=NEW','myOH','scrollbars=yes,width=800,height=400,resizable=yes')</script>
            	
            	   <?php            	   
            	   unset($_POST['MANAGEDISCOUNT']);
            	   unset($_POST['CHANGEDIS']);
      }      
      if(isset($_POST['REFRESH'])) { 
            	   unset($_POST['MANAGEDISCOUNT']);
            	   unset($_POST['CHANGEDIS']);
      }      	   
	    ?>
      <center>
         <form name='Manage Discounts' method=post action=''>
            <table width="720"; style="border-none";>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                   <td class="head1" colspan="7"; style="text-align:center;">Manage Shopify Discounts</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                   <td Colspan="7" style="border:none;">&nbsp</td>
               </tr>      	
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td class="det1" style="width: 10% border:none;">&nbsp</td>
                 <td class="det1" style="width: 20% border:none; text-align:left">Discount&nbsp;Code</td>
                 <td class="det1" style="width: 20% border:none; text-align:left">Type</td>
                 <td class="det1" style="width: 20% border:none; text-align:left">Value</td>
                 <td class="det1" style="width: 10% border:none; text-align:left">Manage</td>
                 <td class="det1" style="width: 10% border:none; text-align:left">Status</td>
                 <td class="det1" style="width: 10% border:none; text-align:left">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                   <td Colspan="7" style="border:none;">&nbsp</td>
               </tr>  
               <?php 
                   foreach ($discountlist as $drow ) { 
                   	   if($drow['status'] == 'A') {$disStat = 'Active'; } else { $disStat = 'Deleted';}
                   	
                   	
                   	?>
                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            <td class="det2" style="border:none;">&nbsp</td>
                            <td class="det2" style="border:none; text-align:left"><?php echo trim($drow['code']); ?></td>
                            <td class="det2" style="border:none; text-align:left"><?php echo trim($drow['type']); ?></td>
                            <td class="det2" style="border:none; text-align:left"><?php echo trim($drow['amount']); ?></td>
                            <td class="det2" style="border:none; text-align:left"><INPUT TYPE="radio" name="CHANGEDIS" value= <?php echo $drow['uid']; ?> ></td>
                            <td class="det2" style="border:none; text-align:left"><?php echo trim($disStat); ?></td>
                            <td class="det2" style="border:none; text-align:left">&nbsp</td>
                       </tr>
                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            <td Colspan="7" style="border:none;">&nbsp</td>
                       </tr>                     	
                   	
               <?php 
               } ?>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                   <td Colspan="7" style="border:none;">&nbsp</td>
               </tr>  
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";> 
                   <td colspan="7"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="MANAGEDISCOUNT" value= "Manage Discount">
                                                               <INPUT TYPE="submit" class="submit" name="ADDDISCOUNT"    value= "Add Discount">
                                                               <INPUT TYPE="submit" class="submit" name="REFRESH"        value= "Refresh">
                                                               <INPUT TYPE="submit" class="submit" name="BACK"           value= "Back"></td>
                   </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                   <td Colspan="7" style="border:none;">&nbsp</td>
               </tr>  
            </table>
         </form>
    </center> 
	</body>       
 </HTML>
<?php


?> 