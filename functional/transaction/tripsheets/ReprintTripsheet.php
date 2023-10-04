<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER."DAO/TripSheetDAO.php");
    include_once($ROOT.$PHPFOLDER."properties/Constants.php");
		include_once($ROOT.$PHPFOLDER.'TO/SequenceTO.php');
		include_once($ROOT.$PHPFOLDER.'DAO/SequenceDAO.php');
		include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
	  include_once($ROOT.$PHPFOLDER."libs/CommonUtils.php");		

if (!isset($_SESSION)) session_start() ;
$userUId = $_SESSION['user_id'] ;
$principalId = $_SESSION['principal_id'] ;
$depotId = $_SESSION['depot_id'] ;
$systemId = $_SESSION["system_id"];
$systemName = $_SESSION['system_name'];

$nelarray = array('392','393', '190', '396', '397', '400', '401', '417');     
      
if (isset($_POST['transporter'])) $posttransporter = $_POST['transporter']; else $posttransporter="";
if (isset($_POST['Days']))        $days            = $_POST['Days']; else $days="";
      
//Create new database object
$dbConn = new dbConnect(); $dbConn->dbConnection();

?>
<!DOCTYPE html>
<html>
  <head>

		<TITLE>Document Selection</TITLE>
     
    <link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/kos_standard_screen.css' rel='stylesheet' type='text/css'> 
    <link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/1_kwelanga.css' rel='stylesheet' type='text/css'>
    <script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/jquery.js"></script>
    <script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/dops_global_functions.js"></script>

    <style>
    	td.head1 {font-weight:normal;
                font-size:2em;text-align:center; 
                font-family: Calibri, Verdana, Ariel, sans-serif; 
                padding: 0 150px 0 150px }
    </style>
  </head>
  <body>

<?php

if (isset($_POST['BACKFORM'])) {
     unset($_POST['FIRSTFORM']);		
     unset($_POST['finishform']);		
	
}
if (isset($_POST['CANFORM'])) {
     return;
}
if (isset($_POST['FINISHFORM'])) {
          $list = implode(",",$_POST['select']);
?>             
          <script type='text/javascript'>parent.showMsgBoxInfo('Trip Sheet Successfully Re Printed')
             <?php 
               $ingarray = array('222');
               $honarray = array('230');
               $cleararray = array('163','186','236');
               
                if(in_array($depotId, $ingarray)) { ?>
                	  window.open('<?php echo $ROOT.$PHPFOLDER ?>functional/presentations/presentationManagement.php?TYPE=CUSTOMNTS294&FINDNUMBER=<?PHP echo $list; ?>');
             <?php } 
                elseif(in_array($depotId, $honarray)) { ?>
                     window.open('<?php echo $ROOT.$PHPFOLDER ?>functional/presentations/presentationManagement.php?TYPE=CUSTOMTS305&FINDNUMBER=<?PHP echo $list; ?>');
             <?php } 
                elseif(in_array($depotId, $cleararray)) { ?>
                     window.open('<?php echo $ROOT.$PHPFOLDER ?>functional/presentations/presentationManagement.php?TYPE=CUSTOMTS216&FINDNUMBER=<?PHP echo $list; ?>');
             <?php }
                elseif(in_array($depotId, $nelarray)) { ?>
                     window.open('<?php echo $ROOT.$PHPFOLDER ?>functional/presentations/presentationManagement.php?TYPE=CUSTOMTSNEL&FINDNUMBER=<?PHP echo $list; ?>');
             <?php } else { ?>
                     window.open('<?php echo $ROOT.$PHPFOLDER ?>functional/presentations/presentationManagement.php?TYPE=tripsheet&FINDNUMBER=<?PHP echo $list; ?>');
             <?php } 
                   if(isset($_POST['LOADSHEET']) && in_array($depotId, $nelarray)) { ?>
                     window.open('<?php echo $ROOT.$PHPFOLDER ?>functional/presentations/view/load_sheet_document_multi_version3.php?TRIPNO=<?PHP echo $list; ?>');    
                   <?php       	
                   } ?>
           </script>       
<?php               
}

if (isset($_POST['FIRSTFORM'])) {	

      if (isset($_POST['transporter'])) $posttransporter = $_POST['transporter']; else $posttransporter="";
      if (isset($_POST['Days']))        $days            = $_POST['Days']; else $days="";

      
      if (isset($_POST['transporter']) && $posttransporter <> 'Select transporter') {
          if($days == 'today') {
             $subdays = "date_format(dh.tripsheet_date,'%Y/%m/%d') = '" . date("Y/m/d") ."'" ;
          } elseif ($days == '1') {	 
             $subdays = "date_format(dh.tripsheet_date,'%Y-%m-%d') = date_format(DATE_SUB('" . date("Y/m/d"). "',INTERVAL 1 DAY),'%Y-%m-%d')";   		
          } elseif ($days == '2') {	 
             $subdays = "date_format(dh.tripsheet_date,'%Y-%m-%d') = date_format(DATE_SUB('" . date("Y/m/d"). "',INTERVAL 2 DAY),'%Y-%m-%d')"; 
          } elseif ($days == 'All') {	 
             $subdays = "date_format(dh.tripsheet_date,'%Y/%m/%d') <= '". date("Y/m/d") . "'";
          }    
          
           $TripSheetDAO = new TripSheetDAO($dbConn);
           $mfTS = $TripSheetDAO->gettripSheets($depotId,$posttransporter,$subdays);
          
          if (sizeof($mfTS)>0) { ?>
               <center>          
                  <form name='reprintts' method=post target=''>
                     <table width="900"; style="border:none">
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            <td colspan="7" style="border:none; text-align: center; font-weight: normal; font-size:20px">Select Trip Sheet to Re Print</td>            	
                        </tr>	
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            <td colspan="7" style="border:none; text-align: center; font-weight: bold;">&nbsp</td>            	
                        </tr>	
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        	<td class='det1' style="border:none; text-align: center;">&nbsp;</td>
                          <td class='det1' style="border:none; text-align: center;">Transporter</td>
                          <td class='det1' style="border:none; text-align: center;">Trip Sheet Number</td>
                          <td class='det1' style="border:none; text-align: center;">Date</td>
                          <td class='det1' style="border:none; text-align: center;">No of Documents</td>
                          <td class='det1' style="border:none; text-align: center;">Select</td>
                          <td class='det1' style="border:none; text-align: center;">&nbsp;</td>
                        </tr>
                        <?php
                        $cl = "even";
                        foreach ($mfTS as $row) { ?>
                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                             	  <td class='det2' style="border:none; text-align: center;">&nbsp;</td>
                                <td class='det2' style="border:none; text-align: center;"><?php echo $row['transporter'];?></td>
                                <td class='det2' style="border:none; text-align: center;"><?php echo $row['tripsheet_number'];?></td>
                                <td class='det2' style="border:none; text-align: center;"><?php echo $row['tripsheet_date'];?></td>
                                <td class='det2' style="border:none; text-align: center;"><?php echo $row['Documents'];?></td>
                                <td class='det2' style="border:none; text-align: center;""><INPUT TYPE="radio" name="select[]" value= "<?php echo $row['tripsheet_number'];?>"><br></td>
                                <td class='det2' style="border:none; text-align: center;">&nbsp;</td>
                             </tr>                              
                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                <td colspan="7" style="border:none; text-align: center; font-weight: bold;">&nbsp</td>
                             </tr> <?php 
                 	      } ?>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            <td colspan="7" style="border:none; text-align: center; font-weight: bold;">&nbsp</td>            	
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">	
                        <?php	                        	
                 	      if(in_array($depotId, $nelarray)) { ?>
                                    <td class="det1" colspan="7" style="text-align:center";>Print&nbsp;Load&nbsp;Sheet&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE="checkbox" name="LOADSHEET" value= "1" CHECKED></td>
                               <?php                              	
                               } else { ?>
                                    <td class="det1" colspan="7" style="text-align:center";>&nbsp</td>
                               <?php 	
                               } ?>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            <td colspan="7" style="border:none; text-align: center; font-weight: bold;">&nbsp</td>            	
                        </tr>	
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>"> 
                        	  <td colspan="7" style="text-align: center";><INPUT TYPE="submit" class="submit" name="FINISHFORM" value= "Print Selected Trip Sheets">
                        	  	                                          <INPUT TYPE="submit" class="submit" name="BACKFORM"   value= "Back">
                                                                        <INPUT TYPE="submit" class="submit" name="CANFORM"    value= "Cancel"></td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            <td colspan="7" style="border:none; text-align: center; font-weight: bold;">&nbsp</td>            	
                        </tr>	
                     </table>
                  </form>   
               </center> <?php	
          } else { ?>
              <script type='text/javascript'>parent.showMsgBoxError("No Tripsheets found");</script>	 
              <?php
              unset($_POST['FIRSTFORM']);	
          }
      } else { ?>
              <script type='text/javascript'>parent.showMsgBoxError("No Transporter Selected");</script>	 
              <?php 
              unset($_POST['FIRSTFORM']);	     	
      }
} 
// ********************************************************************************************************************************************
    if (!isset($_POST['FIRSTFORM'])) { 

     include_once($ROOT.$PHPFOLDER."DAO/transactionDAO.php");
     $TripSheetDAO = new TripSheetDAO($dbConn);
     $mfTS = $TripSheetDAO->gettripSheettransporter($depotId);

     ?>
        <center>
           <form name='Re Print' method=post action=''>
              <table width="900"; style="border:none">
                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                   <td Colspan="5">&nbsp</td>
                 </tr>
                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                       <td Colspan="5" class=head1 style="text-align:center"; >Select transporter</td>
                 </tr>
                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                       <td Colspan="5">&nbsp</td>
                 </tr>
                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                      <td Colspan="2">&nbsp</td>
                      <td style="text-align:center";>
                         <select name="transporter" id="transporter">
                            <option value="Select transporter">Select transporter</option>
                            <?php foreach($mfTS as $row) { ?>
                               <option value="<?php echo $row['uid']; ?>"><?php echo $row['name']; ?></option>
                            <?php } ?>
              	         </select>
                      </td>
                      <td Colspan="2">&nbsp</td>
                 </tr>
                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                       <td Colspan="5">&nbsp</td>
                 </tr>
                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 	    <td Colspan="2">&nbsp</td>
                      <td style="text-align:center";>  <select name="Days">
                               <option value="today">Today</option>
                               <option value="1">1 Day Ago</option>
                               <option value="2">2 Days Ago</option>
                               <option value="All">All</option>
                            </select>
                      </td>
                      <td Colspan="2">&nbsp</td>
                 </tr>
                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                      <td colspan="5">&nbsp</td>	
                 </tr>
                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
              	     <td colspan="5" style="text-align:center";><INPUT TYPE="submit" class="submit" name="FIRSTFORM" value= "Get Available Trips Sheet"></td>
                 </tr>
                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                      <td colspan="5">&nbsp</td>	
                 </tr>
              </table>
	  	     </form>
        </center>
     <?php        
     }
// ********************************************************************************************************************************************
?>    
</body>        
</html>
 
 <?php 

function tripsheetlist() {
	
	echo transporter;
	
}	

?>
