<?php
// https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/functional/presentations/view/rb_rvl_box_stickers.php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER.'TO/SequenceTO.php');
include_once($ROOT.$PHPFOLDER.'DAO/SequenceDAO.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');

$dbConn = new dbConnect();
$dbConn->dbConnection();

$errorTO = new ErrorTO;

if (!isset($_SESSION)) session_start() ;

      $seqVal="000000";
      $sequenceDAO = new SequenceDAO($dbConn);
      $sequenceTO = new SequenceTO;
      $errorTO = new ErrorTO;
      
      $sequenceTO->sequenceKey=LITERAL_SEQ_RVLBOX;
      
      $result=$sequenceDAO->getSequence($sequenceTO,$seqVal);
      
      if ($result->type!=FLAG_ERRORTO_SUCCESS) { ?>
          <script type='text/javascript'>parent.showMsgBoxInfo('RVL Box Sequence not set up')</script>  
          <?php
          return $result;
      }              
      $sticker1 = $seqVal;

      $seqVal="000000";
      $sequenceDAO = new SequenceDAO($dbConn);
      $sequenceTO = new SequenceTO;
      $errorTO = new ErrorTO;
      
      $sequenceTO->sequenceKey=LITERAL_SEQ_RVLBOX;
       
      $result=$sequenceDAO->getSequence($sequenceTO,$seqVal);
      
      if ($result->type!=FLAG_ERRORTO_SUCCESS) { ?>
          <script type='text/javascript'>parent.showMsgBoxInfo('RVL Box Sequence not set up')</script>  
          <?php
          return $result;
       }              
       $sticker2 = $seqVal;

      $seqVal="000000";
      $sequenceDAO = new SequenceDAO($dbConn);
      $sequenceTO = new SequenceTO;
      $errorTO = new ErrorTO;
      
      $sequenceTO->sequenceKey=LITERAL_SEQ_RVLBOX;
      
      $result=$sequenceDAO->getSequence($sequenceTO,$seqVal);
              
      if ($result->type!=FLAG_ERRORTO_SUCCESS) { ?>
          <script type='text/javascript'>parent.showMsgBoxInfo('RVL Box Sequence not set up')</script>  
          <?php
          return $result;
       }              
       $sticker3 = $seqVal;

      $seqVal="000000";
      $sequenceDAO = new SequenceDAO($dbConn);
      $sequenceTO = new SequenceTO;
      $errorTO = new ErrorTO;
      
      $sequenceTO->sequenceKey=LITERAL_SEQ_RVLBOX;
      
      $result=$sequenceDAO->getSequence($sequenceTO,$seqVal);
      
      if ($result->type!=FLAG_ERRORTO_SUCCESS) { ?>
          <script type='text/javascript'>parent.showMsgBoxInfo('RVL Box Sequence not set up')</script>  
          <?php
          return $result;
      }
      
      $sticker4 = $seqVal;

?>

<!DOCTYPE html>
<html>
   <title>RVL Box Stickers</title>
          <link   href='<?php echo $ROOT.$PHPFOLDER ?>css/kos_standard_template.css' rel='stylesheet' type='text/css'>
      <head>
          <style type="text/css">
              td.gl1 {font-weight:bold; 
                      font-size:18px; 
                      text-align:left; 
                      background-color:#A9D08E;
                     }
              td.wl1 {font-weight:bold; 
                      font-size:18px; 
                      text-align:left; 
                      background-color:white;
                     }
                     
              td.ltw {font-weight:normal; 
              	      font-size:18px; 
              	      text-align:left; 
              	      background-color:white;
              	      border-bottom:solid;
              	      border-bottom-width: 0.3px; 

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
       
       <table style="border-collapse:collapse; width:98%">
          <tr> 
              <td colspan="5" >&nbsp;</td>	
          </tr>
          <tr> 
              <td colspan="5" >&nbsp;</td>	
          </tr>
          <tr> 
              <td colspan="5" >&nbsp;</td>	
          </tr>
          <tr>
          	 <td style="width: 3%:">&nbsp;</td>
             <td style="width: 46%:">
                <table style="border:1px none black; border-collapse:collapse; width: 96%">  <! –– Top Left ––>
                   <tr>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                   </tr>
                   <tr>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                   </tr>
                   <tr>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                   </tr>
                   <tr>
                       <td style="width: 3%;" >&nbsp;</td>
                       <td style="width: 60%;">&nbsp;</td>
                       <td rowspan ='5' style="width: 36%;"><img src="<?php echo $ROOT.$PHPFOLDER ; ?>images/logos/rb-iRam.jpg" style="width:160px; height:80px; float:right;"></td>
                       <td style="width: 3%;" >&nbsp;</td>
                   </tr>
                   <tr>
                       <td style="">&nbsp;</td>
                       <td style="font-weight:bold; font-size:18px;">&nbsp;&nbsp;Date:&nbsp;..............................</td>
                       <td style="">&nbsp;</td>
                   </tr>
                   <tr>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                   </tr>                   
                   <tr>
                       <td style="">&nbsp;</td>
                       <td style=""><span style="font-weight:bold; font-size:18px;" NOWRAP>&nbsp;&nbsp;Box ..........&nbsp;of&nbsp; ...........&nbsp;</td>
                       <td style="">&nbsp;</td>
                   </tr>
                   <tr>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                   </tr>
                   <tr>
                       <td style="">&nbsp;</td>
                       <td style="font-weight:bold; font-size:18px;" NOWRAP>&nbsp;&nbsp;Kwelanga&nbsp;Document&nbsp;No:&nbsp;................................&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                   </tr>
                   <tr>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                   </tr>
                   <tr>
                       <td style="">&nbsp;</td>
                       <td style="font-weight:bold; font-size:18px;" NOWRAP>&nbsp;&nbsp;Store&nbsp;Document&nbsp;No:&nbsp;........................................</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                   </tr>
                   <tr>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                   </tr>
                   <tr>
                       <td style="">&nbsp;</td>
                       <td style="font-weight:bold; font-size:18px;" NOWRAP>&nbsp;&nbsp;RMA&nbsp;No:&nbsp;........................................</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                   </tr>

                   <tr>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                   </tr>
                 </table>
                 <table>  
                   <tr>
                       <td style="width:6%;">&nbsp;</td>
                       <td style="width:44%;">
                               <table style="border: solid; border-collapse:collapse; border-width: 0.5px; width:95%">
                                      <tr>
                                           <td class="gl1" style="width:100%;" >&nbsp;Store Code</td>
                                      </tr>
                                      <tr>
                                           <td class="wl1" style="width:100%;" >&nbsp;</td>
                                      </tr>                
                                      <tr>
                                           <td class="gl1" style="width:100%;" >&nbsp;Vendor No</td>
                                      </tr>
                                      <tr>
                                           <td <td class="wl1" style="width:100%;" >&nbsp;</td>
                                      </tr>

                                      <tr>
                                           <td <td class="gl1" style="width:100%;" >&nbsp;Vendor Name</td>
                                      </tr>
                                      <tr>
                                           <td <td class="wl1" style="width:100%;" >&nbsp;</td>
                                      </tr> 
                               </table>
                       </td>
                       <td style="width:44%;">
                       	       <table style="border: solid; border-collapse:collapse; border-width: 0.5px; width:95%">
                                      <tr>
                                           <td class="gl1" style="width:100%;" >&nbsp;Store Name</td>
                                      </tr>
                                      <tr>
                                           <td class="ltw" style="width:100%;" >&nbsp;SS</td>
                                      </tr>                
                                      <tr>
                                           <td class="ltw" style="width:100%;" >&nbsp;BEX</td>
                                      </tr>
                                      <tr>
                                           <td class="ltw" style="width:100%;" >&nbsp;BWH</td>
                                      </tr>

                                      <tr>
                                           <td class="ltw" style="width:100%;" >&nbsp;Makro</td>
                                      </tr>
                                      <tr>
                                           <td class="ltw" style="width:100%;" >&nbsp;</td>
                                      </tr> 
                               </table>
                       </td>
                       <td style="width:6%;">&nbsp;</td>
                   </tr>
                   <tr>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                   </tr>
                   <tr>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                   </tr>
                   <tr>
                       <td style="">&nbsp;</td>
                       <td style="font-weight:bold; font-size:18px;" NOWRAP>&nbsp;&nbsp;Uplifted&nbsp;By:&nbsp;........................................</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                   </tr>
                   <tr>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                   </tr>
                   <tr>
                       <td style="">&nbsp;</td>
                       <td style="font-weight:bold; font-size:18px; text-align:left" NOWRAP>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Box&nbsp;No</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                   </tr>
                   <tr>
                       <td style="">&nbsp;</td>
                       <td style=""><img alt="<?php echo $sticker1 ?>" src="<?php echo $ROOT.$PHPFOLDER; ?>barcode/barcode.php?text=<?php echo $sticker1 ?>&print=true" /></td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                   </tr>
                </table>	
             </td>
             <td style="width: 3%:">&nbsp;</td>
             <td style="width: 46%:">
                <table style="border:1px none black; border-collapse:collapse; width: 96%">  <! –– Top Right ––>
                   <tr>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                   </tr>
                   <tr>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                   </tr>
                   <tr>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                   </tr>
                   <tr>
                       <td style="width: 3%;" >&nbsp;</td>
                       <td style="width: 60%;">&nbsp;</td>
                       <td rowspan ='5' style="width: 36%;"><img src="<?php echo $ROOT.$PHPFOLDER ; ?>images/logos/rb-iRam.jpg" style="width:160px; height:80px; float:right;"></td>
                       <td style="width: 3%;" >&nbsp;</td>
                   </tr>
                   <tr>
                       <td style="">&nbsp;</td>
                       <td style="font-weight:bold; font-size:18px;">&nbsp;&nbsp;Date:&nbsp;..............................</td>
                       <td style="">&nbsp;</td>
                   </tr>
                   <tr>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                   </tr>                   
                   <tr>
                       <td style="">&nbsp;</td>
                       <td style=""><span style="font-weight:bold; font-size:18px;" NOWRAP>&nbsp;&nbsp;Box ..........&nbsp;of&nbsp; ...........&nbsp;</td>
                       <td style="">&nbsp;</td>
                   </tr>
                   <tr>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                   </tr>
                   <tr>
                       <td style="">&nbsp;</td>
                       <td style="font-weight:bold; font-size:18px;" NOWRAP>&nbsp;&nbsp;Kwelanga&nbsp;Document&nbsp;No:&nbsp;................................&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                   </tr>
                   <tr>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                   </tr>
                   <tr>
                       <td style="">&nbsp;</td>
                       <td style="font-weight:bold; font-size:18px;" NOWRAP>&nbsp;&nbsp;Store&nbsp;Document&nbsp;No:&nbsp;........................................</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                   </tr>
                   <tr>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                   </tr>
                   <tr>
                       <td style="">&nbsp;</td>
                       <td style="font-weight:bold; font-size:18px;" NOWRAP>&nbsp;&nbsp;RMA&nbsp;No:&nbsp;........................................</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                   </tr>
                   <tr>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                   </tr>
                 </table>
                 <table>  
                   <tr>
                       <td style="width:6%;">&nbsp;</td>
                       <td style="width:44%;">
                               <table style="border: solid; border-collapse:collapse; border-width: 0.5px; width:95%">
                                      <tr>
                                           <td class="gl1" style="width:100%;" >&nbsp;Store Code</td>
                                      </tr>
                                      <tr>
                                           <td class="wl1" style="width:100%;" >&nbsp;</td>
                                      </tr>                
                                      <tr>
                                           <td class="gl1" style="width:100%;" >&nbsp;Vendor No</td>
                                      </tr>
                                      <tr>
                                           <td <td class="wl1" style="width:100%;" >&nbsp;</td>
                                      </tr>

                                      <tr>
                                           <td <td class="gl1" style="width:100%;" >&nbsp;Vendor Name</td>
                                      </tr>
                                      <tr>
                                           <td <td class="wl1" style="width:100%;" >&nbsp;</td>
                                      </tr> 
                               </table>
                       </td>
                       <td style="width:44%;">
                       	       <table style="border: solid; border-collapse:collapse; border-width: 0.5px; width:95%">
                                      <tr>
                                           <td class="gl1" style="width:100%;" >&nbsp;Store Name</td>
                                      </tr>
                                      <tr>
                                           <td class="ltw" style="width:100%;" >&nbsp;SS</td>
                                      </tr>                
                                      <tr>
                                           <td class="ltw" style="width:100%;" >&nbsp;BEX</td>
                                      </tr>
                                      <tr>
                                           <td class="ltw" style="width:100%;" >&nbsp;BWH</td>
                                      </tr>

                                      <tr>
                                           <td class="ltw" style="width:100%;" >&nbsp;Makro</td>
                                      </tr>
                                      <tr>
                                           <td class="ltw" style="width:100%;" >&nbsp;</td>
                                      </tr> 
                               </table>
                       </td>
                       <td style="width:6%;">&nbsp;</td>
                   </tr>
                   <tr>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                   </tr>
                   <tr>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                   </tr>
                   <tr>
                       <td style="">&nbsp;</td>
                       <td style="font-weight:bold; font-size:18px;" NOWRAP>&nbsp;&nbsp;Uplifted&nbsp;By:&nbsp;........................................</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                   </tr>
                   <tr>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                   </tr>
                   <tr>
                       <td style="">&nbsp;</td>
                       <td style="font-weight:bold; font-size:18px; text-align:left" NOWRAP>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Box&nbsp;No</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                   </tr>
                   <tr>
                       <td style="">&nbsp;</td>
                       <td style=""><img alt="<?php echo $sticker2 ?>" src="<?php echo $ROOT.$PHPFOLDER; ?>barcode/barcode.php?text=<?php echo $sticker2 ?>&print=true" /></td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                   </tr>
                </table>	
             </td>
             <td style="width: 2%:">&nbsp;</td>
           </tr>
           <tr> 
              <td colspan="5" >&nbsp;</td>	
           </tr>
          <tr> 
              <td colspan="5" >&nbsp;</td>	
          </tr>
          <tr> 
              <td colspan="5" >&nbsp;</td>	
          </tr>
          <tr>
          	 <td style="width: 3%:">&nbsp;</td>
             <td style="width: 46%:">
                <table style="border:1px none black; border-collapse:collapse; width: 96%">  <! –– Bottom Left ––>
                   <tr>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                   </tr>
                   <tr>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                   </tr>
                   <tr>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                   </tr>
                   <tr>
                       <td style="width: 3%;" >&nbsp;</td>
                       <td style="width: 60%;">&nbsp;</td>
                       <td rowspan ='5' style="width: 36%;"><img src="<?php echo $ROOT.$PHPFOLDER ; ?>images/logos/rb-iRam.jpg" style="width:160px; height:80px; float:right;"></td>
                       <td style="width: 3%;" >&nbsp;</td>
                   </tr>
                   <tr>
                       <td style="">&nbsp;</td>
                       <td style="font-weight:bold; font-size:18px;">&nbsp;&nbsp;Date:&nbsp;..............................</td>
                       <td style="">&nbsp;</td>
                   </tr>
                   <tr>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                   </tr>                   
                   <tr>
                       <td style="">&nbsp;</td>
                       <td style=""><span style="font-weight:bold; font-size:18px;" NOWRAP>&nbsp;&nbsp;Box ..........&nbsp;of&nbsp; ...........&nbsp;</td>
                       <td style="">&nbsp;</td>
                   </tr>
                   <tr>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                   </tr>
                   <tr>
                       <td style="">&nbsp;</td>
                       <td style="font-weight:bold; font-size:18px;" NOWRAP>&nbsp;&nbsp;Kwelanga&nbsp;Document&nbsp;No:&nbsp;................................&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                   </tr>
                   <tr>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                   </tr>
                   <tr>
                       <td style="">&nbsp;</td>
                       <td style="font-weight:bold; font-size:18px;" NOWRAP>&nbsp;&nbsp;Store&nbsp;Document&nbsp;No:&nbsp;........................................</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                   </tr>
                   <tr>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                   </tr>
                   <tr>
                       <td style="">&nbsp;</td>
                       <td style="font-weight:bold; font-size:18px;" NOWRAP>&nbsp;&nbsp;RMA&nbsp;No:&nbsp;........................................</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                   </tr>
                   <tr>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                   </tr>
                 </table>
                 <table>  
                   <tr>
                       <td style="width:6%;">&nbsp;</td>
                       <td style="width:44%;">
                               <table style="border: solid; border-collapse:collapse; border-width: 0.5px; width:95%">
                                      <tr>
                                           <td class="gl1" style="width:100%;" >&nbsp;Store Code</td>
                                      </tr>
                                      <tr>
                                           <td class="wl1" style="width:100%;" >&nbsp;</td>
                                      </tr>                
                                      <tr>
                                           <td class="gl1" style="width:100%;" >&nbsp;Vendor No</td>
                                      </tr>
                                      <tr>
                                           <td <td class="wl1" style="width:100%;" >&nbsp;</td>
                                      </tr>

                                      <tr>
                                           <td <td class="gl1" style="width:100%;" >&nbsp;Vendor Name</td>
                                      </tr>
                                      <tr>
                                           <td <td class="wl1" style="width:100%;" >&nbsp;</td>
                                      </tr> 
                               </table>
                       </td>
                       <td style="width:44%;">
                       	       <table style="border: solid; border-collapse:collapse; border-width: 0.5px; width:95%">
                                      <tr>
                                           <td class="gl1" style="width:100%;" >&nbsp;Store Name</td>
                                      </tr>
                                      <tr>
                                           <td class="ltw" style="width:100%;" >&nbsp;SS</td>
                                      </tr>                
                                      <tr>
                                           <td class="ltw" style="width:100%;" >&nbsp;BEX</td>
                                      </tr>
                                      <tr>
                                           <td class="ltw" style="width:100%;" >&nbsp;BWH</td>
                                      </tr>

                                      <tr>
                                           <td class="ltw" style="width:100%;" >&nbsp;Makro</td>
                                      </tr>
                                      <tr>
                                           <td class="ltw" style="width:100%;" >&nbsp;</td>
                                      </tr> 
                               </table>
                       </td>
                       <td style="width:6%;">&nbsp;</td>
                   </tr>
                   <tr>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                   </tr>
                   <tr>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                   </tr>
                   <tr>
                       <td style="">&nbsp;</td>
                       <td style="font-weight:bold; font-size:18px;" NOWRAP>&nbsp;&nbsp;Uplifted&nbsp;By:&nbsp;........................................</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                   </tr>
                   <tr>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                   </tr>
                   <tr>
                       <td style="">&nbsp;</td>
                       <td style="font-weight:bold; font-size:18px; text-align:left" NOWRAP>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Box&nbsp;No</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                   </tr>
                   <tr>
                       <td style="">&nbsp;</td>
                       <td style=""><img alt="<?php echo $sticker3 ?>" src="<?php echo $ROOT.$PHPFOLDER; ?>barcode/barcode.php?text=<?php echo $sticker3 ?>&print=true" /></td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                   </tr>
                </table>		
             </td>
             <td style="width: 2%:">&nbsp;</td>
             <td style="width: 46%:">
                <table style="border:1px none black; border-collapse:collapse; width: 96%">  <! –– Buttom right ––>
                   <tr>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                   </tr>
                   <tr>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                   </tr>
                   <tr>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                   </tr>
                   <tr>
                       <td style="width: 3%;" >&nbsp;</td>
                       <td style="width: 60%;">&nbsp;</td>
                       <td rowspan ='5' style="width: 36%;"><img src="<?php echo $ROOT.$PHPFOLDER ; ?>images/logos/rb-iRam.jpg" style="width:160px; height:80px; float:right;"></td>
                       <td style="width: 3%;" >&nbsp;</td>
                   </tr>
                   <tr>
                       <td style="">&nbsp;</td>
                       <td style="font-weight:bold; font-size:18px;">&nbsp;&nbsp;Date:&nbsp;..............................</td>
                       <td style="">&nbsp;</td>
                   </tr>
                   <tr>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                   </tr>                   
                   <tr>
                       <td style="">&nbsp;</td>
                       <td style=""><span style="font-weight:bold; font-size:18px;" NOWRAP>&nbsp;&nbsp;Box ..........&nbsp;of&nbsp; ...........&nbsp;</td>
                       <td style="">&nbsp;</td>
                   </tr>
                   <tr>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                   </tr>
                   <tr>
                       <td style="">&nbsp;</td>
                       <td style="font-weight:bold; font-size:18px;" NOWRAP>&nbsp;&nbsp;Kwelanga&nbsp;Document&nbsp;No:&nbsp;................................&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                   </tr>
                   <tr>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                   </tr>
                   <tr>
                       <td style="">&nbsp;</td>
                       <td style="font-weight:bold; font-size:18px;" NOWRAP>&nbsp;&nbsp;Store&nbsp;Document&nbsp;No:&nbsp;........................................</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                   </tr>
                   <tr>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                   </tr>
                   <tr>
                       <td style="">&nbsp;</td>
                       <td style="font-weight:bold; font-size:18px;" NOWRAP>&nbsp;&nbsp;RMA&nbsp;No:&nbsp;........................................</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                   </tr>
                   <tr>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                   </tr>
                 </table>
                 <table>  
                   <tr>
                       <td style="width:6%;">&nbsp;</td>
                       <td style="width:44%;">
                               <table style="border: solid; border-collapse:collapse; border-width: 0.5px; width:95%">
                                      <tr>
                                           <td class="gl1" style="width:100%;" >&nbsp;Store Code</td>
                                      </tr>
                                      <tr>
                                           <td class="wl1" style="width:100%;" >&nbsp;</td>
                                      </tr>                
                                      <tr>
                                           <td class="gl1" style="width:100%;" >&nbsp;Vendor No</td>
                                      </tr>
                                      <tr>
                                           <td <td class="wl1" style="width:100%;" >&nbsp;</td>
                                      </tr>

                                      <tr>
                                           <td <td class="gl1" style="width:100%;" >&nbsp;Vendor Name</td>
                                      </tr>
                                      <tr>
                                           <td <td class="wl1" style="width:100%;" >&nbsp;</td>
                                      </tr> 
                               </table>
                       </td>
                       <td style="width:44%;">
                       	       <table style="border: solid; border-collapse:collapse; border-width: 0.5px; width:95%">
                                      <tr>
                                           <td class="gl1" style="width:100%;" >&nbsp;Store Name</td>
                                      </tr>
                                      <tr>
                                           <td class="ltw" style="width:100%;" >&nbsp;SS</td>
                                      </tr>                
                                      <tr>
                                           <td class="ltw" style="width:100%;" >&nbsp;BEX</td>
                                      </tr>
                                      <tr>
                                           <td class="ltw" style="width:100%;" >&nbsp;BWH</td>
                                      </tr>

                                      <tr>
                                           <td class="ltw" style="width:100%;" >&nbsp;Makro</td>
                                      </tr>
                                      <tr>
                                           <td class="ltw" style="width:100%;" >&nbsp;</td>
                                      </tr> 
                               </table>
                       </td>
                       <td style="width:6%;">&nbsp;</td>
                   </tr>
                   <tr>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                   </tr>
                   <tr>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                   </tr>
                   <tr>
                       <td style="">&nbsp;</td>
                       <td style="font-weight:bold; font-size:18px;" NOWRAP>&nbsp;&nbsp;Uplifted&nbsp;By:&nbsp;........................................</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                   </tr>
                   <tr>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                   </tr>
                   <tr>
                       <td style="">&nbsp;</td>
                       <td style="font-weight:bold; font-size:18px; text-align:left" NOWRAP>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Box&nbsp;No</td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                   </tr>
                   <tr>
                       <td style="">&nbsp;</td>
                       <td style=""><img alt="<?php echo $sticker4 ?>" src="<?php echo $ROOT.$PHPFOLDER; ?>barcode/barcode.php?text=<?php echo $sticker4 ?>&print=true" /></td>
                       <td style="">&nbsp;</td>
                       <td style="">&nbsp;</td>
                   </tr>
                </table>	
             </td>
             <td style="width: 1%:">&nbsp;</td>
       	   </tr>
       </table>
    </body>
 </html>

<?php
$dbConn->dbClose();
?>
