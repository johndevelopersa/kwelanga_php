<?php
// https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/functional/presentations/view/employee_stickers.php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER.'DAO/EmployeeDAO.php');

$dbConn = new dbConnect();
$dbConn->dbConnection();

$depot_Id = $_SESSION['depot_id'];

if($depot_Id < 100) {?>
         <script type='text/javascript'>parent.showMsgBoxError('Error! Warehouse Not Set <br> Set ware with another Emploee menu Option')</script>
         <?php
         return;
}
?>

<!DOCTYPE html>
<html>
   <title>Bar Code Sheet</title>
          <link   href='<?php echo $ROOT.$PHPFOLDER ?>css/kos_standard_template.css' rel='stylesheet' type='text/css'>
      <head>
          <style type="text/css">

          </style>
          <script type="text/javascript" language="javascript" src="<?php echo $ROOT.$PHPFOLDER ?>js/jquery.js"></script>
      </head>
    <body>
       <div align="center" id="noprint" class="disableprint" >
         <table id="wrapper" cellspacing="0" cellpadding="0">
          <tr>
            <td>
             <div id="noprint"><!-- HIDE THIS PRINT AREA : START /--->
               <div id="toolbar">
                 <a href="javascript:window.print();"><img src="<?php echo $ROOT.$PHPFOLDER ?>images/print-icon.png" border="0" alt="" align="left" > PRINT</a>
                  <div style="clear:both;"></div>
               </div>
             </div><!-- HIDE THIS PRINT AREA : END /--->
            </td>
          </tr>
         </table>
       </div>
       <?php
       
       $EmployeeDAO = new EmployeeDAO($dbConn); 
       $ecArr = $EmployeeDAO->getEmployeeNumbers($depot_Id);

       ?>
       
       <table style="border-collapse:collapse; width:80%">
       	
       	  <?php
       	  $ecount = 1;
       	  foreach ($ecArr as $value) {
       	  	  if($ecount == 1) {
                    $emp1 = $value['code'] . ' - ' . $value['name'];
                    $ecount++;
       	  	  } elseif($ecount == 2) {
                    $emp2 = $value['code'] . ' - ' . $value['name']; ?>
                   <tr>
                          <td>&nbsp;</td>
                          <td style="border:none; text-align:center; font-weight:bold; font-size: 25px;"><?php echo $emp1; ?></td>
                          <td>&nbsp;</td>
                          <td style="border:none; text-align:center; font-weight:bold; font-size: 25px;"><?php echo $emp2; ?></td>
                          <td>&nbsp;</td>
                  </tr>                    
                   <tr>
                         <td>&nbsp;</td>
                         <td style="text-align:center;"><img alt="<?php echo $emp1; ?>" src=" ../../../../kwelanga_php/barcode/barcode.php?text=<?php echo $emp1; ?>"&print=true" </img></td>
                         <td>&nbsp;</td>
                         <td style="text-align:center;"><img alt="<?php echo $emp2; ?>" src="<?php echo $ROOT.$PHPFOLDER; ?>barcode/barcode.php?text=<?php echo $emp2; ?>"&print=true" </img></td>
                         <td>&nbsp;</td>               
                   </tr>
                   <tr>
                          <td>&nbsp;</td>
                          <td style="border:none; text-align:center; font-weight:bold; font-size: 25px;">&nbsp;</td>
                          <td>&nbsp;</td>
                          <td style="border:none; text-align:center; font-weight:bold; font-size: 25px;">&nbsp;</td>
                          <td>&nbsp;</td>
                   </tr>
                   <tr>
                          <td>&nbsp;</td>
                          <td style="border:none; text-align:center; font-weight:bold; font-size: 25px;">&nbsp;</td>
                          <td>&nbsp;</td>
                          <td style="border:none; text-align:center; font-weight:bold; font-size: 25px;">&nbsp;</td>
                          <td>&nbsp;</td>
                   </tr>
              <?php
                   $ecount = 1;
       	  	  } 
       	  	  
       	 } 	  ?>
       </table>
    </body>
 </html>

<?php
$dbConn->dbClose();
?>
