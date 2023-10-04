<?php 
include_once('ROOT.php'); 
include_once($ROOT.'PHPINI.php');	
include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER."DAO/TripSheetDAO.php");
include_once($ROOT.$PHPFOLDER.'elements/basicSelectElement.php');	

if (!isset($_SESSION)) session_start() ;
     $userUId     = $_SESSION['user_id'] ;
     $principalId = $_SESSION['principal_id'] ;
     $systemId    = $_SESSION["system_id"];
     $systemName  = $_SESSION['system_name'];
  
     //Create new database object
     $dbConn = new dbConnect(); 
     $dbConn->dbConnection();
     $errorTO = new ErrorTO;
?>
<!DOCTYPE html>
<HTML>
	<HEAD>

		<TITLE>Document Selection</TITLE>

    <link href='<?php echo $ROOT.$PHPFOLDER ?>css/1_kwelanga.css' rel='stylesheet' type='text/css'>
    <link href='<?php echo $ROOT.$PHPFOLDER ?>css/kos_standard_screen2.css' rel='stylesheet' type='text/css'>
    <script type="text/javascript" language="javascript" src="<?php echo $ROOT.$PHPFOLDER ?>js/jquery.js"></script>
		<script type="text/javascript" language="javascript" src="<?php echo $ROOT.$PHPFOLDER ?>js/dops_global_functions.js"></script>

    <style>
    input[type=button] {
            background-color: transparent;
            border: none;
            color: Orange;
            margin: 4px 2px;
            cursor: pointer;
        }
	}
      
   	</style>

		</HEAD>
<body>
<?php

if(isset($_POST['CANFORM'])) {
     return; 
}

if (isset($_POST['FIRSTFORM'])) {
       if (isset($_POST["TSNUMBER"])) $postTSNUMBER = test_input($_POST["TSNUMBER"]); else $postTSNUMBER = '';
       
       if($postTSNUMBER <> '') {
      
            // Check that user has access to thhis tripsheetDAO
      
            $TripsheetDAO = new TripsheetDAO($dbConn);
            $hasAccess = $TripsheetDAO->userAccessToTripsheet($postTSNUMBER, $userUId, 392);
      
            If(count($hasAccess) == 1) {
                      $loadSheet = TRUE;
                      $list = $postTSNUMBER; ?>             
                      <script type='text/javascript'>parent.showMsgBoxInfo('Trip Sheet Successfully Displayed')
                          <?php
                          $nelarray = array('392', '393', '190', '396', '397', '400', '401', '417');  ?>   
                          window.open('<?php echo $ROOT.$PHPFOLDER ?>functional/presentations/presentationManagement.php?TYPE=CUSTOMTSNEL&FINDNUMBER=<?PHP echo $list; ?>');
                          <?php
                          if($loadSheet && in_array(392, $nelarray)) { ?>
                                window.open('<?php echo $ROOT.$PHPFOLDER ?>functional/presentations/view/load_sheet_document_multi_version4.php?TRIPNO=<?PHP echo $list; ?>');    
                          <?php       	
                          } ?>
                      </script>       
                      <?php 
                      unset($_POST['FIRSTFORM']);    	
            } else { ?>
                   <script type='text/javascript'>parent.showMsgBoxError('User does not have access to this Tripsheet..<BR> Try again.')</script> 
                  <?php 
                  unset($_POST['FIRSTFORM']);
            }  
       } else { ?>
             <script type='text/javascript'>parent.showMsgBoxError('No Document Selected..<BR> Try again.')</script> 
             <?php 
             unset($_POST['FIRSTFORM']);
       }
}
// ********************************************************************************************************************************	
if (!isset($_POST['FIRSTFORM'])) { 
     
     $class = 'odd'; ?>
     <center>
         <FORM name='Select Invoice' method=post action=''>
            <table width='750px'; style="border:none">
                <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                    <td Colspan="5">&nbsp</td>
                </tr>  
            	  <tr>
                    <td class='head1' Colspan="5" style="text-align:center"; >View a Tripsheet</td>
                </tr>
                <tr>
                    <td width='10%';>&nbsp</td>
                    <td width='30%';>&nbsp</td>
                    <td width='40%';>&nbsp</td>
                    <td width='10%';>&nbsp</td>
                    <td width='10%';>&nbsp</td>
                </tr>	        	      	
                <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                    <td Colspan="5">&nbsp</td>
                </tr>
                <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                    <td>&nbsp</td>
                    <td class='det1' style="text-align:left";>Enter Tripsheet Number</td>
                    <td colspan="2"; style="text-align:left"><input type="text" name="TSNUMBER"><br></td>
                    <td>&nbsp</td>
                </tr>
                <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                    <td Colspan="5">&nbsp</td>
                </tr>
                <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                    <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="FIRSTFORM" value= "Get Tripsheet">
                    	                                          <INPUT TYPE="submit" class="submit" name="CANFORM" value= "Cancel"></td>
                </tr>          
            </table>
         </form>
     </center> 
<?php


}

// ********************************************************************************************************************************	
 function test_input($data) {

  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  
  return $data;
 } 
?>