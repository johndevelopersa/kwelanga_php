<?php

    include_once('ROOT.php'); 
    include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
    include_once($ROOT.$PHPFOLDER."libs/GUICommonUtils.php");
    include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
    include_once($ROOT.$PHPFOLDER."DAO/TripSheetDAO.php"); 
    
    if (!isset($_SESSION)) session_start() ;
      $userUId     = $_SESSION['user_id'] ;
      $principalId = $_SESSION['principal_id'] ;
      $depotId = $_SESSION['depot_id'] ;      

      //Create new database object
      $dbConn  = new dbConnect(); 
      $dbConn->dbConnection();
      $errorTO = new ErrorTO;
      
?>
<!DOCTYPE html>
<html>
  <head>

		<TITLE>Export Trip Sheet</TITLE>

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

   </head>
   <body>
<?php
// ********************************************************************************************************************************************

if (isset($_POST['BACKFORM'])) {
     unset($_POST['FIRSTFORM']);		
     unset($_POST['finishform']);		
	
}
if (isset($_POST['CANFORM'])) {
     return;
}

// ********************************************************************************************************************************************

if (isset($_POST['FINISHFORM'])) {
          $tripNo = implode(",",$_POST['select']);          
          ?>             
          <script type='text/javascript'>parent.showMsgBoxInfo('Trip Sheet Successfully Exported') </script> 
          <?php 
          
          include_once($ROOT . $PHPFOLDER . "functional/reports/phpreports/TripsheetToCsv.php");

}
// ********************************************************************************************************************************************

if (isset($_POST['FIRSTFORM'])) {	

      if (isset($_POST['transporter'])) $posttransporter = $_POST['transporter']; else $posttransporter="";
      if (isset($_POST['Days']))        $days            = $_POST['Days']; else $days="";
      
      $class = 'odd';
      
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
                        	<td class='det1' style="border:none; text-align: center;">&nbsp;</td>
                          <td class='det1' style="border:none; text-align: center;">&nbsp;</td>
                          <td class='det1' style="border:none; text-align: center;">&nbsp;</td>
                          <td class='det1' style="border:none; text-align: center;">&nbsp;</td>
                          <td class='det1' style="border:none; text-align: center;">&nbsp;</td>
                          <td class='det1' style="border:none; text-align: center;">&nbsp;</td>
                          <td class='det1' style="border:none; text-align: center;">&nbsp;</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            <td colspan="7" style="border:none; text-align:center; font-weight: normal; font-size:20px;">Select Trip Sheet to Export</td>            	
                        </tr>	
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            <td colspan="7" style="border:none; text-align: center;">&nbsp</td>            	
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
                        	  <td colspan="7" style="text-align: center";><INPUT TYPE="submit" class="submit" name="FINISHFORM" value= "Download Selected Trip Sheets">
                        	  	                                          <INPUT TYPE="submit" class="submit" name="BACKFORM"   value= "Back">
                                                                        <INPUT TYPE="submit" class="submit" name="CANFORM"    value= "Cancel"></td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            <td colspan="7" style="border:none; text-align: center;">&nbsp</td>
                        </tr>	
                    </table>
                  </form>   
               </center> 
          <?php	
          } else { ?>
              <script type='text/javascript'>parent.showMsgBoxError("No Trip sheets found");</script>	 
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
</HTML>

<?php
 function test_input($data) {

  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  if($data=='') { $data=0; } 
    
  return $data;
 }
 ?> 