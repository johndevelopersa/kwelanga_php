<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER."DAO/transactionDAO.php");
    include_once($ROOT.$PHPFOLDER."properties/Constants.php");
		include_once($ROOT.$PHPFOLDER.'TO/SequenceTO.php');
		include_once($ROOT.$PHPFOLDER.'DAO/SequenceDAO.php');
		include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
	  include_once($ROOT.$PHPFOLDER."libs/CommonUtils.php");
	  include_once($ROOT.$PHPFOLDER."DAO/TripSheetDAO.php");

if (!isset($_SESSION)) session_start() ;
$userUId = $_SESSION['user_id'] ;
$principalId = $_SESSION['principal_id'] ;
$depotId = $_SESSION['depot_id'] ;
$systemId = $_SESSION["system_id"];
$systemName = $_SESSION['system_name'];

$nelarray = array('392','393', '190' ,'396' ,'397');



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
		<link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/kos_standard_screen.css' rel='stylesheet' type='text/css'>
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

          $nelarray = array('392','393', '190', '396', '397');

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

// Selection Form ***************************************************************************************************************
     if(isset($_POST['SELMOD'])) {
           if(isset($_POST['TRIPACT']) == 'SPLITLOAD') { 
           	
           	
           	  echo "<br>". $_POST['TSNUM'];
              echo "<br>". $_POST['TSNAME'];
              echo "<br>". $_POST['TSCASES'];
              echo "<br>". $_POST['TSDATE'];
              echo "<br>". $_POST['TRIPACT'];

           	unset($_POST['TRIPACT']);
           	unset($_POST['SUBMITFILTER']);
           	unset($_POST['SPLITLOAD']);
           	?>
           	
           	
           	
           	
           	
           	

           <?php               	
           }

     }

// Selection Form ***************************************************************************************************************
     if(isset($_POST['TRIPACT']) || isset($_POST["SUBMITFILTER"])  ) {

         if (isset($_POST["TSNUM"]))  $postTSNUM  = test_input($_POST["TSNUM"]);     else $postTSNUM = '0000000';
         if (isset($_POST["TRNAME"])) $postTRNAME = test_input($_POST["TRNAME"]);    else $postTRNAME = 'xxxxxxxxxxx';
         if (isset($_POST["TRDATE"])) $postTRDATE = test_input($_POST["TRDATE"]);    else $postTRDATE = 'xxxxxxxxxxx';
         if (isset($_POST["TRDATE"])) $postTRDATE = test_input($_POST["TRDATE"]);    else $postTRDATE = 'xxxxxxxxxxx';
         echo "<br>";
         echo $_POST['TRIPACT'];
         echo "<br>";

         $TripsheetDAO = new TripsheetDAO($dbConn);
         $mfDD = $TripsheetDAO->getTripSheetDetails($depotId, $postTRNAME, $postTSNUM , $postTRDATE)
    	?>
          <center>
              <form name='Invoices' method=post target=''>
                  <table width="720px"; style="border:none">
                      <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                           <td class="det1" colspan="7" style="text-align:center;">Tripsheet to Manage</td>
                      </tr>
                      <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                           <td width="5%"; style="border: none;">&nbsp;</td>
                           <td width="20%"; style="border: none;">&nbsp;</td>
                           <td width="30%"; style="border: none;">&nbsp;</td>
                           <td width="15%"; style="border: none;">&nbsp;</td>
                           <td width="15%"; style="border: none;">&nbsp;</td>
                           <td width="10%";  style="border: none;">&nbsp;</td>
                           <td width="5%";  style="border: none;">&nbsp;</td>
                      </tr>     
                      <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td colspan="1" style="border: none;">&nbsp</td>
                          <td class="det2" colspan="2" style="text-align:left; border: none; padding-left:5px;"><INPUT TYPE="submit" class="submit" name="SUBMITFILTER" value= "Submit Filter">&nbsp;
                                                                                <INPUT TYPE="submit" class="submit" name="CLEARFILTER"  value= "Clear Filter"></td>
                          <td colspan="4" style="border: none;">&nbsp</td>
                      </tr>
                      <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                           <td colspan="7" style="text-align:center;">&nbsp;</td>
                      </tr>
                      <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                          <td colspan="1" >&nbsp</td>
                          <td  class="det1" colspan="1" style="text-align:left; padding: 0px 5px 0px 5px;">TripSheet No</td>
                          <td  class="det1" colspan="1" style="text-align:left; padding: 0px 5px 0px 5px;">Transporter</td>
                          <td  class="det1" colspan="1" style="text-align:right; padding: 0px 5px 0px 5px;">Qty</td>
                          <td  class="det1" colspan="1" style="text-align:right; padding: 0px 5px 0px 5px;" value= "" >From Date</td>
                          <td  class="det1" colspan="1" style="text-align:right; padding: 0px 5px 0px 5px;" value= "" >Select</td>
                          <td colspan="2">&nbsp</td>
                      </tr>
                      <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                           <td colspan="7" >&nbsp;</td>
                      </tr>
                      <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td colspan="1">&nbsp</td>
                          <td class="det1" colspan="1" style="text-align:left; padding: 0px 5px 0px 5px;" ;><INPUT TYPE="TEXT" size="5"  name="TSNUM"    value= "" ></td>
                          <td class="det1" colspan="1" style="text-align:left; padding: 0px 5px 0px 5px;" ;><INPUT TYPE="TEXT" size="25" name="TRNAME"   value= "" ></td>
                          <td class="det1" colspan="1" style="text-align:left; padding: 0px 5px 0px 5px;" ;>&nbsp;</td>
                          <td class="det1" colspan="1" style="text-align:right; padding: 0px 5px 0px 5px;" ;><INPUT TYPE="TEXT" size="10" name="TRDATE"     value= "" placeholder="YYYY-MM-DD"></td>

                          <td colspan="2">&nbsp</td>
                      </tr>
                      <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                           <td colspan="7" style="text-align:center;">&nbsp;</td>
                      </tr>
                      <?php
                      if(count($mfDD) == 0) { ?>
                          <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                               <td  colspan="1">&nbsp</td>
                               <td  class="det3" colspan= "4" style="text-align:left; color:Red;">No Tripsheet selected - Use filters</td>
                               <td  colspan="7">&nbsp</td>
                          </tr>
                          <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                               <td  colspan="3" style="text-align:center;">&nbsp;</td>
                          </tr>
                      <?php
                      } else {

                          foreach ($mfDD as $row) { ?>
                              <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                                   <td  colspan="1">&nbsp</td>
                                   <td class="detN12" style="text-align:left;"><?php echo $row['tripsheet_number'];?></td>
                                   <td class="detN12" style="text-align:left;"><?php echo $row['name'];?></td>
                                   <td class="detN12" style="text-align:right;"><?php echo $row['Cases'];?></td>
                                   <td class="detN12" style="text-align:right;"><?php echo $row['tripsheet_date']; ?></td>
                                   <td class="detN12" style="text-align:right;"><INPUT TYPE="radio" name="SELMOD" onclick="javascript: submit()" value= "<?php echo $row['tripsheet_number'] . "-" . $row['name'];?>"></td>

                                   <td  colspan="1">&nbsp<input type="hidden" id="TSNUM" name="TSNUM" value="<?php echo $row['tripsheet_number']; ?>
                                   	                     <input type="hidden" id="TSNAME" name="TSNAME" value="<?php echo $row['name']; ?>
                                   	                     <input type="hidden" id="TSCASES" name="TSCASES" value="<?php echo $row['Cases']; ?>>
                                   	                     <input type="hidden" id="TSCASES" name="TSDATE" value="<?php echo $row['tripsheet_date']; ?></td>
                              </tr>
                              <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                                   <td  colspan="7" style="text-align:center;">&nbsp;<input type="hidden" id="TRIPACT" name="TRIPACT" value="<?php echo $_POST['TRIPACT']; ?>"></td>
                              </tr>
                          <?php
                          }
                      } ?>
                  </table>
		          </form>
          </center>
     <?php
     }

// First Form ***************************************************************************************************************



     if(!isset($_POST['TRIPACT']) && !isset($_POST['SPLITLOAD']) && !isset($_POST["SUBMITFILTER"])) {
           // Check Warehouse User
           $TripsheetDAO = new TripsheetDAO($dbConn);
           $uTS = $TripsheetDAO->checkWarehouseUser($userUId);

           if($uTS[0]['category'] == 'D') {	?>
               <center>
                  <form name='Maintain Transporter' method=post action=''>
                     <table width="720"; style="border:none">
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                             <td style="width:10%; border:none;">&nbsp</td>
                             <td style="width:40%; border:none;">&nbsp</td>
                             <td style="width:40%; border:none;">&nbsp</td>
                             <td style="width:10%; border:none;">&nbsp</td>
                         </tr>
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                             <td class="head1" colspan="4"; style="text-align:center; padding: 0 15px 0 20px ;">TripSheet Transporter Maintenance</td>
                         </tr>
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                             <td Colspan="4">&nbsp</td>
                         </tr>
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                             <td Colspan="4">&nbsp</td>
                         </tr>
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                             <td >&nbsp</td>
                             <td class="det1" colspan="1"; style="text-align:right; padding: 0 15px 0 20px ; "><label class="label" for="MODTRANSP">Change&nbsp;Transporter&nbsp;Details&nbsp;</label><input type="radio" name="TRIPACT" onclick="javascript: submit()" value="MODIFY"></td>
                             <td class="det1" colspan="1"; style="text-align:right; padding: 0 20px 0 30px ; "><label class="label" for="SPLITLOAD">Split&nbsp;Tripsheet&nbsp;Load</label><input type="radio" name="TRIPACT" onclick="javascript: submit()" value="SPLITLOAD"></td>
                             <td >&nbsp</td>
                          </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                             <td Colspan="4">&nbsp</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                             <td Colspan="4">&nbsp</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                             <td colspan="4"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="backform"   value= "Back">
                                                                         <INPUT TYPE="submit" class="submit" name="canform"   value= "Cancel"></td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            <td Colspan="4">&nbsp</td>
                         </tr>
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                             <td Colspan="4">&nbsp</td>
                         </tr>
                   </table>
                </form>
             </center>
         <?php
           } else { ?>
               <script type='text/javascript'>parent.showMsgBoxError('You are not a warehouse user <br><br> Cannot Continue!! ')</script>
               <?php
               return;
           }
     } ?>

</body>
</html>

 <?php

 function test_input($data) {

  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);

  return $data;
 } ?>