<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER."properties/Constants.php");
    include_once($ROOT.$PHPFOLDER.'DAO/OmniExtractDAO.php');
    include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');    

    if (!isset($_SESSION)) session_start() ;
        $userUId     = $_SESSION['user_id'] ;
        $principalId = $_SESSION['principal_id'] ;

        //Create new database object
        $dbConn  = new dbConnect(); 
        $dbConn->dbConnection();
        $errorTO = new ErrorTO;
        ?>

<!DOCTYPE html>
<HTML>
	  <HEAD>

		   <TITLE>Document Management</TITLE>

         <link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/1_kwelanga.css' rel='stylesheet' type='text/css'>
         <link href='<?php echo $ROOT.$PHPFOLDER ?>css/kos_standard_screen2.css' rel='stylesheet' type='text/css'>
         <script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/jquery.js"></script>
         <script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/dops_global_functions.js"></script>

     </HEAD>
<body>

    <?php
    
    if (isset($_POST['FIRSTFORM'])) {
    	
    	        if(count($_POST['SRESET']) > 0) {               
    	               $OmniExtractDAO = new OmniExtractDAO($dbConn);
                     $errorTO = $OmniExtractDAO->updateActiveStatus(implode(",",$_POST['SRESET']), TRUE);
                     
                     if($errorTO->type == FLAG_ERRORTO_SUCCESS) {?>
                              <script type='text/javascript'>parent.showMsgBoxInfo('Active Status Successfully Reset<br><br>)</script>
                     <?php	
                     } else { ?>
                     	        <script type='text/javascript'>parent.showMsgBoxError('Active Status Reset Failed<br><br>Contact Support')</script>
                              <?php
                              print_r($errorTO);
                     }
                     
    	        } else { ?>
                        <script type='text/javascript'>parent.showMsgBoxError('Nothing to reset<br><br>Contact Support')</script>
                              <?php
    	        }
    }
    
    if (isset($_POST['CANFORN'])) {
           return;
     }    
    
    
        $OmniExtractDAO = new OmniExtractDAO($dbConn);
        $uTS = $OmniExtractDAO->getActiveStatus();  ?>
        <center>
               <FORM name='Select Invoice' method=post  action=''>
                   <table width="720"; style="border:none">
                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td class="det1" Colspan="5"; style="text-align:center";>Reset Omni Run Status</td>
                       </tr>
                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                       </tr>	        	
                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td class="det1" width="10%"; style="border:none";>&nbsp</td>
                          <td class="det1" width="40%"; style="border:none; text-align:left";>Principal</td>
                          <td class="det1" width="20%"; style="border:none; text-align:right";>Seconds since Last Run</td>
                          <td class="det1" width="20%"; style="border:none; text-align:right";>Status</td>
                          <td class="det1" width="10%"; style="border:none";>Reset</td>
                       </tr> 
                       <?php 
                       foreach ($uTS as $row) { 
                       	    if($row['Secs'] > 90 && $row['active_status'] == 'Y') {
                                $col = RED;
                                $cbStatus = 'CHECKED';
                                $allowReset ='';
                       	    } else {
                                $cbStatus = '';
                                $col = BLACK;
                       	    }
                       	
                       	?>
                            <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                                <td Colspan="1">&nbsp</td>
                                <td Colspan="1" style="color: <?php echo $col;?>; text-align:left";><?php echo $row['name'];?></td>
                                <td colspan="1"; style="color: <?php echo $col;?>; text-align:right";><?php echo $row['Secs'];?></td>
                                <td colspan="1"; style="color: <?php echo $col;?>; text-align:right"; ><?php echo $row['active_status'];?></td>
                                <td Colspan="1"><input type="checkbox" name="SRESET[]" value=<?php echo $row['uid'];?> <?php echo $cbStatus;?> ></td>
                            </tr>              
                            <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                               <td Colspan="5">&nbsp</td>
                            </tr>
                       <?php     
                       } ?>
                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                       </tr>
                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                           <td Colspan="1">&nbsp</td>
                           <td colspan="3"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="FIRSTFORM" value= "Reset Selected">
                 	                                                     <INPUT TYPE="submit" class="submit" name="BACKF"   value= "Refresh">
                                                                       <INPUT TYPE="submit" class="submit" name="CANFORN"   value= "Cancel"></td>
                           <td Colspan="1">&nbsp</td>                                            
                       </tr>          
                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            <td Colspan="5">&nbsp</td>
                       </tr>  
                   </table>
               </FORM>
        </center>
	</body>       
 </HTML>        
        