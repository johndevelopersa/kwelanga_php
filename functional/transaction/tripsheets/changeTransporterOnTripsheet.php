<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER."properties/Constants.php");
    include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
    include_once($ROOT.$PHPFOLDER."DAO/tripsheetDAO.php");
    include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
?>
<!DOCTYPE html>
<HTML>
	<HEAD>

		<TITLE>Document Selection</TITLE>

    <link href='<?php echo $ROOT.$PHPFOLDER ?>css/1_kwelanga.css' rel='stylesheet' type='text/css'>
    <link href='<?php echo $ROOT.$PHPFOLDER ?>css/kos_standard_screen2.css' rel='stylesheet' type='text/css'>
    <script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/jquery.js"></script>
		<script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/dops_global_functions.js"></script>

    <style>
      
     </style>

		</HEAD>
<body>
<?php

    if (!isset($_SESSION)) session_start() ;
       $userUId     = $_SESSION['user_id'] ;
       $principalId = $_SESSION['principal_id'] ;
       $depotId     = $_SESSION['depot_id'] ;
      
       if (isset($_POST["TSNUMBER"])) $postTSNUMBER=test_input($_POST["TSNUMBER"]); else $postTSNUMBER = ''; 
       if (isset($_POST['reason'])) $postreason = $_POST['reason']; else $postreason="";
      
       //Create new database object
     $dbConn = new dbConnect(); 
     $dbConn->dbConnection();
     $errorTO = new ErrorTO;
     
     $TripsheetDAO = new TripsheetDAO($dbConn);
     $uTS = $TripsheetDAO->checkWarehouseUser($userUId);

     if($uTS[0]['category'] != 'D') {	?>
                <script type='text/javascript'>parent.showMsgBoxError('You are not a warehouse user <br><br> Cannot Continue!! ')</script>
               <?php
               return;
     }
// ***************************************************************************************************************************************************************
if(isset($_POST['FINSHFORM'])) {
	
	     if($_POST['TRANSPORTER'] <> 'Select Transporter') {

	     	      $tripNo = test_input($_POST['TRPNUMBER']);
	            $TripsheetDAO = new TripsheetDAO($dbConn);     
              $errorTO = $TripsheetDAO->updateCurrentTransporterNew($depotId, $tripNo, $_POST['TRANSPORTER'], $userUId);
              if($errorTO->type != 'S') { ?>
                      <script type='text/javascript'>parent.showMsgBoxError('Transporter update failed..<BR> Contact Support.')</script> 
               <?php 
              } else { ?>
                      <script type='text/javascript'>parent.showMsgBoxInfo('Transporter update Successful..<BR>')</script> 
              <?php 
              }
	     } else { ?>
                      <script type='text/javascript'>parent.showMsgBoxError('No New Transporter Selected ..<BR> Try Again.')</script> 
               <?php 
	     }
       unset($_POST['FIRSTFORM']);	
       unset($_POST['FINSHFORM']);
}

// ***************************************************************************************************************************************************************

if(isset($_POST['FIRSTFORM'])) {
	
	  if (isset($_POST["TSNUMBER"])) $postTSNUMBER=test_input($_POST["TSNUMBER"]); else $postTSNUMBER = '';   
	
	  // Get Tripsheet details
    $tripsheetDAO = new tripsheetDAO($dbConn);
    $mfRTS = $tripsheetDAO->getCurrentTransporter($depotId,$postTSNUMBER);

    $TripsheetDAO = new TripsheetDAO($dbConn);     
    $mfTS = $TripsheetDAO->getTripSheetTransporter2($depotId, '', '');
    
    if(count($mfRTS) > 0 ) { ?>
           <center>
              <form name='Select TripSheet' method=post action=''>
                  <table width="900"; style="border:none">
                      <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="6">&nbsp</td>
                      </tr>
                      <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td class="head1" colspan="6"; style="text-align:center; padding: 0 15px 0 20px ; ">Change Transporter on Tripsheet</td>
                      </tr>
                      <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td width="5%"; style="border:none">&nbsp</td>
                          <td width="20%"; style="border:none">&nbsp</td>
                          <td width="40%"; style="border:none">&nbsp</td>
                          <td width="10%"; style="border:none">&nbsp</td>
                          <td width="20%"; style="border:none">&nbsp</td>
                          <td width="5%" ; style="border:none">&nbsp</td>
                      </tr>
                      <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="1">&nbsp</td>
                          <td class="det1" style="text-align:left;"">Trip Sheet Number</td>
                          <td class="det2" style="text-align:left;""><?php echo $mfRTS[0]['tripsheet_number'];?></td>
                          <td class="det1" style="text-align:right;">Date  </td>
                          <td class="det2" style="text-align:right;"><?php echo $mfRTS[0]['tripsheet_date'];?></td>                  	
                          <td Colspan="1">&nbsp</td>
                      </tr>	
                      <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="6">&nbsp</td>
                      </tr>
                      <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="1">&nbsp</td>
                          <td class="det1" style="text-align:left;"">Transporter</td>
                          <td class="det2" style="text-align:left;""><?php echo $mfRTS[0]['Transporter'];?></td>
                          <td class="det1" style="text-align:right;">Documents</td>
                          <td class="det2" style="text-align:right;"><?php echo $mfRTS[0]['NoDocs'];?></td>                  	
                          <td Colspan="1"><input type="hidden" name="TRPNUMBER" value=<?php echo $postTSNUMBER; ?>></td>
                      </tr>	
                      <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="6">&nbsp</td>
                      </tr>
                      <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                      	  <td Colspan="1">&nbsp</td>
                      	  <td class="det1" style="text-align:left;"">New Transporter</td>
                          <td colspan="3">
                              <select name="TRANSPORTER" id="TRANSPORTER">
                                   <option value="Select Transporter">Select Transporter</option>
                                        <?php foreach($mfTS as $row) { ?>
                                                   <option value="<?php echo $row['uid']; ?>"><?php echo $row['name']; ?></option>
                                        <?php } ?>
                              </select>
                          </td> 
                          <td Colspan="1">&nbsp</td>
                      </tr>
                      <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="6">&nbsp</td>
                      </tr>
                      <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td colspan="6" style="text-align:center;"><INPUT TYPE="submit" class="submit" name="FINSHFORM" value= "Update Transporter">&nbsp;&nbsp;&nbsp;<INPUT TYPE="submit" class="submit" name="BACKFORM"   value= "Back">&nbsp;&nbsp;&nbsp;<INPUT TYPE="submit" class="submit" name="CANFORM"   value= "Cancel"></td>
                      </tr>          
                      <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            <td Colspan="6">&nbsp</td>
                      </tr>  
                  </table>
              </form>
           </center> 	 
<?php
    } else { ?>
           <script type='text/javascript'>parent.showMsgBoxError('No Trip Sheet found with this number..<BR> Try again.')</script> 
           <?php 
           unset($_POST['FIRSTFORM']);
    }
}	   
     
// ***************************************************************************************************************************************************************
if(!isset($_POST['FIRSTFORM']) && !isset($_POST['FINSHFORM'])) { ?>
    <center>
       <FORM name='Select TripSheet' method=post action=''>
            <table width="720"; style="border:none">
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                   <td class="head1" colspan="4"; style="text-align:center; padding: 0 15px 0 20px ; ">Change Transporter on Tripsheet</td>
                   <td colspan="4">&nbsp</td>
             </tr>
             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                   <td Colspan="5">&nbsp</td>
             </tr>      	
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td class="det3" style="text-align:center; color:Red;">*Enter Trip Sheet Number</td>
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
                 <td style="text-align:right";><strong>Trip Sheet Number:</td>
                 <td colspan="4"; style="text-align:left"><input type="text" name="TSNUMBER" placeholder= "Trip Sheet Number"></td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="5">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="FIRSTFORM" value= "Get Trip Sheet Details">
                 	                                           <INPUT TYPE="submit" class="submit" name="BACKFORM"   value= "Back">
                                                             <INPUT TYPE="submit" class="submit" name="CANFORM"   value= "Cancel"></td>
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

 function test_input($data) {

  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  
  return $data;
 }  