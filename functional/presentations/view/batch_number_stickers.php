<?php
// https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/functional/presentations/view/batch_number_stickers.php?BOXNO=10000

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");

$dbConn = new dbConnect();
$dbConn->dbConnection();

if (!isset($_SESSION)) session_start() ;

$boxNo = ((isset($_GET["BOXNO"]))?$_GET["BOXNO"]:"");

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
       ?>
       
       <table style="border-collapse:collapse; width:80%">
          <tr>
               <td>&nbsp;</td>
               <td style="border:none; text-align:center; font-weight:bold; font-size: 25px;">Major Tech</td>
               <td>&nbsp;</td>
               <td style="border:none; text-align:center; font-weight:bold; font-size: 25px;">Major Tech</td>
               <td>&nbsp;</td>
          </tr>                    
          <tr>
               <td>&nbsp;</td>
               <td style="text-align:center;"><img alt="<?php echo $boxNo + 1; ?>" src="<?php echo $ROOT.$PHPFOLDER; ?>barcode/barcode.php?text=<?php echo $boxNo + 1; ?>"&print=true" </img></td>
               <td>&nbsp;</td>
               <td style="text-align:center;"><img alt="<?php echo $boxNo + 2; ?>" src="<?php echo $ROOT.$PHPFOLDER; ?>barcode/barcode.php?text=<?php echo $boxNo + 2; ?>"&print=true" </img></td>
               <td>&nbsp;</td>               
          </tr>                    
          <tr>
               <td>&nbsp;</td>
               <td style="text-align:center; font-weight:bold; font-size: 25px;"><?php echo $boxNo + 1 ; ?></td>
               <td>&nbsp;</td>
               <td style="text-align:center; font-weight:bold; font-size: 25px;"><?php echo $boxNo + 2 ; ?></td>
               <td>&nbsp;</td>
          </tr>
          <tr>
              <td width="10%";>&nbsp;</td>
              <td width="35%";>&nbsp;</td>          	
              <td width="15%";>&nbsp;</td>          	
              <td width="35%";>&nbsp;</td>          	
              <td width="5%";>&nbsp;</td>
          </tr>                   
          <tr>
              <td colspan='5'>&nbsp;</td>
          </tr>
          <tr>
              <td colspan='5'>&nbsp;</td>
          </tr>
          <tr>
               <td>&nbsp;</td>
               <td style="text-align:center; font-weight:bold; font-size: 25px;">Major Tech</td>
               <td>&nbsp;</td>
               <td style="text-align:center; font-weight:bold; font-size: 25px;">Major Tech</td>
               <td>&nbsp;</td>
          </tr>                    
          <tr>
               <td>&nbsp;</td>
               <td style="text-align:center;"><img alt="<?php echo $boxNo + 3; ?>" src="<?php echo $ROOT.$PHPFOLDER; ?>barcode/barcode.php?text=<?php echo $boxNo + 3; ?>"&print=true" </img></td>
               <td>&nbsp;</td>
               <td style="text-align:center;"><img alt="<?php echo $boxNo + 4; ?>" src="<?php echo $ROOT.$PHPFOLDER; ?>barcode/barcode.php?text=<?php echo $boxNo + 4; ?>"&print=true" </img></td>
               <td>&nbsp;</td>               
          </tr>                    
          <tr>
               <td>&nbsp;</td>
               <td style="text-align:center; font-weight:bold; font-size: 25px;"><?php echo $boxNo + 3 ; ?></td>
               <td>&nbsp;</td>
               <td style="text-align:center; font-weight:bold; font-size: 25px;"><?php echo $boxNo + 4 ; ?></td>
               <td>&nbsp;</td>
          </tr>
          <tr>
              <td colspan='5'>&nbsp;</td>
          </tr>
          <tr>
              <td colspan='5'>&nbsp;</td>
          </tr>
          <tr>
               <td>&nbsp;</td>
               <td style="text-align:center; font-weight:bold; font-size: 25px;">Major Tech</td>
               <td>&nbsp;</td>
               <td style="text-align:center; font-weight:bold; font-size: 25px;">Major Tech</td>
               <td>&nbsp;</td>
          </tr>                    
          <tr>
               <td>&nbsp;</td>
               <td style="text-align:center;"><img alt="<?php echo $boxNo + 5; ?>" src="<?php echo $ROOT.$PHPFOLDER; ?>barcode/barcode.php?text=<?php echo $boxNo + 5; ?>"&print=true" </img></td>
               <td>&nbsp;</td>
               <td style="text-align:center;"><img alt="<?php echo $boxNo + 6; ?>" src="<?php echo $ROOT.$PHPFOLDER; ?>barcode/barcode.php?text=<?php echo $boxNo + 6; ?>"&print=true" </img></td>
               <td>&nbsp;</td>               
          </tr>                    
          <tr>
               <td>&nbsp;</td>
               <td style="text-align:center; font-weight:bold; font-size: 25px;"><?php echo $boxNo + 5 ; ?></td>
               <td>&nbsp;</td>
               <td style="text-align:center; font-weight:bold; font-size: 25px;"><?php echo $boxNo + 6 ; ?></td>
               <td>&nbsp;</td>
          </tr>
          <tr>
              <td colspan='5'>&nbsp;</td>
          </tr>
          <tr>
              <td colspan='5'>&nbsp;</td>
          </tr>

          <tr>
               <td>&nbsp;</td>
               <td style="text-align:center; font-weight:bold; font-size: 25px;">Major Tech</td>
               <td>&nbsp;</td>
               <td style="text-align:center; font-weight:bold; font-size: 25px;">Major Tech</td>
               <td>&nbsp;</td>
          </tr>                    
          <tr>
               <td>&nbsp;</td>
               <td style="text-align:center;"><img alt="<?php echo $boxNo + 7; ?>" src="<?php echo $ROOT.$PHPFOLDER; ?>barcode/barcode.php?text=<?php echo $boxNo + 7; ?>"&print=true" </img></td>
               <td>&nbsp;</td>
               <td style="text-align:center;"><img alt="<?php echo $boxNo + 8; ?>" src="<?php echo $ROOT.$PHPFOLDER; ?>barcode/barcode.php?text=<?php echo $boxNo + 8; ?>"&print=true" </img></td>
               <td>&nbsp;</td>               
          </tr>                    
          <tr>
               <td>&nbsp;</td>
               <td style="text-align:center; font-weight:bold; font-size: 25px;"><?php echo $boxNo + 7 ; ?></td>
               <td>&nbsp;</td>
               <td style="text-align:center; font-weight:bold; font-size: 25px;"><?php echo $boxNo + 8 ; ?></td>
               <td>&nbsp;</td>
          </tr>
          <tr>
              <td colspan='5'>&nbsp;</td>
          </tr>
          <tr>
              <td colspan='5'>&nbsp;</td>
          </tr>

          <tr>
               <td>&nbsp;</td>
               <td style="text-align:center; font-weight:bold; font-size: 25px;">Major Tech</td>
               <td>&nbsp;</td>
               <td style="text-align:center; font-weight:bold; font-size: 25px;">Major Tech</td>
               <td>&nbsp;</td>
          </tr>                    
          <tr>
               <td>&nbsp;</td>
               <td style="text-align:center;"><img alt="<?php echo $boxNo + 9; ?>" src="<?php echo $ROOT.$PHPFOLDER; ?>barcode/barcode.php?text=<?php echo $boxNo + 9; ?>"&print=true" </img></td>
               <td>&nbsp;</td>
               <td style="text-align:center;"><img alt="<?php echo $boxNo + 10; ?>" src="<?php echo $ROOT.$PHPFOLDER; ?>barcode/barcode.php?text=<?php echo $boxNo + 10; ?>"&print=true" </img></td>
               <td>&nbsp;</td>               
          </tr>                    
          <tr>
               <td>&nbsp;</td>
               <td style="text-align:center; font-weight:bold; font-size: 25px;"><?php echo $boxNo + 9 ; ?></td>
               <td>&nbsp;</td>
               <td style="text-align:center; font-weight:bold; font-size: 25px;"><?php echo $boxNo + 10 ; ?></td>
               <td>&nbsp;</td>
          </tr>

          <tr>
              <td colspan='5'>&nbsp;</td>
          </tr>
          <tr>
              <td colspan='5'>&nbsp;</td>
          </tr>
          <tr>
               <td>&nbsp;</td>
               <td style="text-align:center; font-weight:bold; font-size: 25px;">Major Tech</td>
               <td>&nbsp;</td>
               <td style="text-align:center; font-weight:bold; font-size: 25px;">Major Tech</td>
               <td>&nbsp;</td>
          </tr>                    
          <tr>
               <td>&nbsp;</td>
               <td style="text-align:center;"><img alt="<?php echo $boxNo + 11; ?>" src="<?php echo $ROOT.$PHPFOLDER; ?>barcode/barcode.php?text=<?php echo $boxNo + 11; ?>"&print=true" </img></td>
               <td>&nbsp;</td>
               <td style="text-align:center;"><img alt="<?php echo $boxNo + 12; ?>" src="<?php echo $ROOT.$PHPFOLDER; ?>barcode/barcode.php?text=<?php echo $boxNo + 12; ?>"&print=true" </img></td>
               <td>&nbsp;</td>               
          </tr>                    
          <tr>
               <td>&nbsp;</td>
               <td style="text-align:center; font-weight:bold; font-size: 25px;"><?php echo $boxNo + 11 ; ?></td>
               <td>&nbsp;</td>
               <td style="text-align:center; font-weight:bold; font-size: 25px;"><?php echo $boxNo + 12 ; ?></td>
               <td>&nbsp;</td>
          </tr>
          <tr>
              <td colspan='5'>&nbsp;</td>
          </tr>
          <tr>
              <td colspan='5'>&nbsp;</td>
          </tr>

          <tr>
               <td>&nbsp;</td>
               <td style="text-align:center; font-weight:bold; font-size: 25px;">Major Tech</td>
               <td>&nbsp;</td>
               <td style="text-align:center; font-weight:bold; font-size: 25px;">Major Tech</td>
               <td>&nbsp;</td>
          </tr>                    
          <tr>
               <td>&nbsp;</td>
               <td style="text-align:center;"><img alt="<?php echo $boxNo + 13; ?>" src="<?php echo $ROOT.$PHPFOLDER; ?>barcode/barcode.php?text=<?php echo $boxNo + 13; ?>"&print=true" </img></td>
               <td>&nbsp;</td>
               <td style="text-align:center;"><img alt="<?php echo $boxNo + 14; ?>" src="<?php echo $ROOT.$PHPFOLDER; ?>barcode/barcode.php?text=<?php echo $boxNo + 14; ?>"&print=true" </img></td>
               <td>&nbsp;</td>               
          </tr>                    
          <tr>
               <td>&nbsp;</td>
               <td style="text-align:center; font-weight:bold; font-size: 25px;"><?php echo $boxNo + 13 ; ?></td>
               <td>&nbsp;</td>
               <td style="text-align:center; font-weight:bold; font-size: 25px;"><?php echo $boxNo + 14 ; ?></td>
               <td>&nbsp;</td>
          </tr>
          <tr>
              <td colspan='5'>&nbsp;</td>
          </tr>
          <tr>
              <td colspan='5'>&nbsp;</td>
          </tr>

          <tr>
               <td>&nbsp;</td>
               <td style="text-align:center; font-weight:bold; font-size: 25px;">Major Tech</td>
               <td>&nbsp;</td>
               <td style="text-align:center; font-weight:bold; font-size: 25px;">Major Tech</td>
               <td>&nbsp;</td>
          </tr>                    
          <tr>
               <td>&nbsp;</td>
               <td style="text-align:center;"><img alt="<?php echo $boxNo + 15; ?>" src="<?php echo $ROOT.$PHPFOLDER; ?>barcode/barcode.php?text=<?php echo $boxNo + 15; ?>"&print=true" </img></td>
               <td>&nbsp;</td>
               <td style="text-align:center;"><img alt="<?php echo $boxNo + 16; ?>" src="<?php echo $ROOT.$PHPFOLDER; ?>barcode/barcode.php?text=<?php echo $boxNo + 16; ?>"&print=true" </img></td>
               <td>&nbsp;</td>               
          </tr>                    
          <tr>
               <td>&nbsp;</td>
               <td style="text-align:center; font-weight:bold; font-size: 25px;"><?php echo $boxNo + 15 ; ?></td>
               <td>&nbsp;</td>
               <td style="text-align:center; font-weight:bold; font-size: 25px;"><?php echo $boxNo + 16 ; ?></td>
               <td>&nbsp;</td>
          </tr>

       </table>
    </body>
 </html>

<?php
$dbConn->dbClose();
?>
