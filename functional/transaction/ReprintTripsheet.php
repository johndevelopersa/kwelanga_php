<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER."DAO/transactionDAO.php");
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

$nelarray = array('392','393', '190');   
      
if (isset($_POST['transporter'])) $posttransporter = $_POST['transporter']; else $posttransporter="";
if (isset($_POST['Days']))        $days            = $_POST['Days']; else $days="";
      
//Create new database object
$dbConn = new dbConnect(); $dbConn->dbConnection();

?>
<!DOCTYPE html>
<html>
  <head>

		<TITLE>Document Selection</TITLE>

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

<?php

if (isset($_POST['finishform'])) {
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
             <?php } else { ?>
                     window.open('<?php echo $ROOT.$PHPFOLDER ?>functional/presentations/presentationManagement.php?TYPE=tripsheet&FINDNUMBER=<?PHP echo $list; ?>');
             <?php } 
                   if(isset($_POST['LOADSHEET']) && in_array($depotId, $nelarray)) { ?>
                            window.open('<?php echo $ROOT.$PHPFOLDER ?>functional/presentations/view/load_sheet_document_version_multi.php?TRIPNO=<?PHP echo $list; ?>');    
                   <?php                         	
                   } ?>
           </script>       
<?php               
}

if (isset($_POST['firstform'])) {	
      
      if (isset($_POST['transporter']) && $posttransporter <> 'Select transporter') {
          if($days == 'today') {
             $subdays = "date_format(dh.tripsheet_date,'%Y/%m/%d') = '" . date("Y/m/d") ."'" ;
          } elseif ($days == '1') {	 
             $subdays = "date_format(dh.tripsheet_date,'%Y/%m/%d') = DATE_SUB('" .date("Y/m/d"). "',INTERVAL 1 DAY)";   		
          } elseif ($days == '2') {	 
             $subdays = "date_format(dh.tripsheet_date,'%Y/%m/%d') = DATE_SUB('" .date("Y/m/d"). "',INTERVAL 2 DAY)";
          } elseif ($days == 'All') {	 
             $subdays = "date_format(dh.tripsheet_date,'%Y/%m/%d') <= '". date("Y/m/d") . "'";
          }    
          
          include_once($ROOT.$PHPFOLDER."DAO/transactionDAO.php");
          $transactionDAO = new transactionDAO($dbConn);
          $mfTS = $transactionDAO->gettripSheets($depotId,$posttransporter,$subdays);
          
          $nelarray = array('392','393', '190');     

          if (sizeof($mfTS)>0) { ?>
               <center>          
                  <form name='reprintts' method=post target=''>
                     <Table style="border:none; float: center;">
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            <td colspan="5" style="border:none; text-align: center; font-weight: normal; font-size:20px">Select Trip Sheet to Re Print</td>            	
                        </tr>	
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            <td colspan="5" style="border:none; text-align: center; font-weight: bold;">&nbsp</td>            	
                        </tr>	
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <th style="border:none; float: center;">Transporter</th>
                          <th style="border:none; float: center;">Trip Sheet Number</th>
                          <th style="border:none; float: center;">Date</th>
                          <th style="border:none; float: center;">No of Documents</th>
                          <th style="border:none; float: center;">Select</th>
                        </tr>
                        <?php
                        $cl = "even";
                        foreach ($mfTS as $row) { ?>
                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                <td style="border:1px solid black; border-collapse: collapse; float: center;"><?php echo $row['transporter'];?></td>
                                <td style="border:1px solid black; border-collapse: collapse; float: center;"><?php echo $row['tripsheet_number'];?></td>
                                <td style="border:1px solid black; border-collapse: collapse; float: center;"><?php echo $row['tripsheet_date'];?></td>
                                <td style="border:1px solid black; border-collapse: collapse; float: center;"><?php echo $row['Documents'];?></td>
                                <td style="border:1px solid black; border-collapse: collapse; float: center;"><INPUT TYPE="radio" name="select[]" value= "<?php echo $row['tripsheet_number'];?>"><br></td>
                             </tr> <?php 
                 	      } ?>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            <td colspan="5" style="border:none; text-align: center; font-weight: bold;">&nbsp</td>            	
                        </tr>	
                        <tr> 
                        <?php	                        	
                 	      if(in_array($depotId, $nelarray)) { ?>
                                    <td class="det1" colspan="5" style="text-align:center";>Print&nbsp;Load&nbsp;Sheet&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE="checkbox" name="LOADSHEET" value= "1" CHECKED></td>
                               <?php                              	
                               } else { ?>
                                    <td class="det1" colspan="5" style="text-align:center";>&nbsp</td>
                               <?php 	
                               } ?>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            <td colspan="5" style="border:none; text-align: center; font-weight: bold;">&nbsp</td>            	
                        </tr>	
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>"> 
                        	  <td colspan="5" style="text-align: center";><INPUT TYPE="submit" class="submit" name="finishform" value= "Print Selected Trip Sheets"></td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            <td colspan="5" style="border:none; text-align: center; font-weight: bold;">&nbsp</td>            	
                        </tr>	
                     </table>
                  </form>   
               </center> <?php	
          } else { ?>
              <script type='text/javascript'>parent.showMsgBoxError("No Tripsheets found");</script>	 <?php
              unset($_POST['firstform']);	
          }
      } else { ?>
              <script type='text/javascript'>parent.showMsgBoxError("No Transporter Selected");</script>	 <?php 
              unset($_POST['firstform']);	     	
      }
}             
if(!isset($_POST['firstform'])) {       
       
     include_once($ROOT.$PHPFOLDER."DAO/transactionDAO.php");
     $transactionDAO = new transactionDAO($dbConn);
     $mfTS = $transactionDAO->gettripSheettransporter($depotId);

     ?>
     <body>
        <center>
           <form name='Re Print' method=post action=''>
              <table width="720"; style="border:none">
                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                   <td Colspan="3">&nbsp</td>
                 </tr>
                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                       <td Colspan="3" class=head1 >Select transporter</td>
                 </tr>
                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                       <td Colspan="3">&nbsp</td>
                 </tr>
                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                      <td Colspan="1">&nbsp</td>
                      <td style="text-align:center";>
                         <select name="transporter" id="transporter">
                            <option value="Select transporter">Select transporter</option>
                            <?php foreach($mfTS as $row) { ?>
                               <option value="<?php echo $row['uid']; ?>"><?php echo $row['name']; ?></option>
                            <?php } ?>
              	         </select>
                      </td>
                      <td Colspan="1">&nbsp</td>
                 </tr>
                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                       <td Colspan="3">&nbsp</td>
                 </tr>
                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 	    <td Colspan="1">&nbsp</td>
                      <td style="text-align:center";>  <select name="Days">
                               <option value="today">Today</option>
                               <option value="1">1 Day Ago</option>
                               <option value="2">2 Days Ago</option>
                               <option value="All">All</option>
                            </select>
                      </td>
                      <td Colspan="1">&nbsp</td>
                 </tr>
                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                      <td colspan="3">&nbsp</td>	
                 </tr>
                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
              	     <td colspan="3" style="text-align:center";><INPUT TYPE="submit" class="submit" name="firstform" value= "Get Available Trips Sheet"></td>
                 </tr>
                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                      <td colspan="3">&nbsp</td>	
                 </tr>
              </table>
	  	     </form>
        </center>
        <?php 
} ?>    
</body>        
</html>
 
 <?php 

function tripsheetlist() {
	
	echo transporter;
	
}	

?>
