<?php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER."DAO/AdministrationDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/TripSheetDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER."TO/LoadSheetTO.php");
include_once($ROOT.$PHPFOLDER."TO/LoadSheetDetailTO.php");
include_once($ROOT.$PHPFOLDER."TO/LoadSheetDocsTO.php");

$dbConn = new dbConnect();
$dbConn->dbConnection();

if (!isset($_SESSION)) session_start() ;
$depotId = $_SESSION['depot_id'] ;
$userUId = $_SESSION['user_id'] ;

$tripNo = ((isset($_GET["TRIPNO"]))?$_GET["TRIPNO"]:"");

$adminDAO = new AdministrationDAO($dbConn);

?>

<!DOCTYPE html>
<html>
   <title>Print Loading Sheet</title>
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
       
       $TripSheetDAO = new TripSheetDAO($dbConn);
       $loadd = $TripSheetDAO->getLoadSheetDetailsVersion4($depotId, $tripNo, 'pp.principal_uid, pp.uid');

       $newDocument = '';
       $newProd     = '';
       $prdStrig    = '';
       $rowTot      = 0 ;
       $gTotal      = 0 ;  
       
       $LoadSheetTO = new LoadSheetTO();
       $LoadSheetTO->Principal    = $loadd[0]['Principal'];
       $LoadSheetTO->prinShotName = $loadd[0]['PSN'];  
       $LoadSheetTO->Wh           = $loadd[0]['Warehouse'];  
       $LoadSheetTO->TripNo       = $loadd[0]['tripsheet_number'];              
       $LoadSheetTO->Transporter  = $loadd[0]['Transporter'];         
       $prodArray = array();
       $lineArray = array();   
           
       foreach($loadd as $row) {
       	   if ($newDocument <> $row['document_number']) {
 
               $LoadSheetDocsTO = new LoadSheetDocsTO();
               $LoadSheetDocsTO->docno    = $row['document_number'];
               $LoadSheetDocsTO->prShortN = $row['PSN'];
               $LoadSheetDocsTO->store    = $row['deliver_name'];
               $LoadSheetDocsTO->cases    = $row['cases'];
               
               $LoadSheetTO->Documents[] = $LoadSheetDocsTO ; 
               
               $newDocument = $row['document_number']; 
           }   
           if ($newProd <> $row['prodId']) { 
           	    if($newProd <> '') {
                    $prodArray['row'] = $prdStrig;
                    $prodArray['Tot'] =  $rowTot;
                    $lineArray[] = $prodArray;
                    $prdStrig    = '';
                    $prodArray = array();
                    $rowTot = 0;
                }
                $prodArray['pc'] = $row['product_code'];
                $prodArray['pd'] = $row['product_description'];
           	    $newProd = $row['prodId'];

           }
           if($prdStrig == '') { $spacer = '';} else { $spacer = ' - ';}
           
           $prdStrig = $prdStrig . $spacer  . $row['document_qty'];
           
           $rowTot   =  $rowTot + $row['document_qty'];
       }
       $prodArray['row'] = $prdStrig;
       $prodArray['Tot'] =  $rowTot;
       $lineArray[] = $prodArray;
       
       $LoadSheetTO->Documents[] = $LoadSheetDocsTO ; 
       
       $LoadSheet = json_decode(json_encode($LoadSheetTO), true);?>
       
       <table style="border-collapse:collapse; width:100%">
                <tr>
                   <td colspan="1" style="text-align:left;">&nbsp;</td>
                   <td class="th1" colspan="9" style="text-align:center;"><?php echo trim($loadd[0]['depot_group']); ?>  - DAILY TRANSPORTER SCHEDULE</td>
                   <td colspan="1" style="text-align:left;">&nbsp;</td>
                </tr>
                <tr>
                   <td colspan="1" style="text-align:left;">&nbsp;</td>
                   <td class="lr" colspan="9">&nbsp;</td>
                   <td colspan="1" style="text-align:left;">&nbsp;</td>
                </tr>                
                <tr>
                   <td style="text-align:left;">&nbsp;</td>
                   <td class="dc15b" colspan="1" style="text-align:left; border-left-style:solid; border-left-width:1px; border-left-color:black; padding-left: 10px;">TRANSPORTER </td>
                   <td class="dc15"  colspan="3" style=" text-align:left;"><?php echo $LoadSheet['Transporter'];?></td>
                   <td class="dc15b" colspan="2" style="text-align:left; padding-left: 10px;">Trip Sheet No</td>
                   <td class="td13"  colspan="2" style="text-align:left; border-right-style:none; border-right-width:1px; border-right-color:black; padding-right: 10px;"><?php echo $LoadSheet['TripNo'];?></td>
                   <td class="dc3" style="text-align:left; border-right-style:solid; border-right-width:1px; border-right-color:black; "><img alt="<?php echo $depotId  . ' - ' .  ltrim($LoadSheet['TripNo'],'0'); ?>" src="<?php echo $ROOT.$PHPFOLDER; ?>barcode/barcode.php?text=<?php echo $depotId . ' - ' . ltrim($LoadSheet['TripNo'],'0'); ?>&print=true" /></td>
                </tr>
                <tr>
                   <td style="text-align:left;">&nbsp;</td>
                   <td class="dc15b" colspan="1" style="text-align:left; border-left-style:solid; border-left-width:1px; border-left-color:black; padding-left: 10px;">TRANSPORTER<br>2</td>
                   <td class="dc15"  colspan="3" style=" text-align:left;">&nbsp;</td>
                   <td class="dc15b" colspan="2" style="text-align:left; padding-left: 10px;">&nbsp;</td>
                   <td class="td13"  colspan="2" style="text-align:left; border-right-style:none; border-right-width:1px; border-right-color:black; padding-right: 10px;">&nbsp;</td>
                   <td class="dc3" style="text-align:left; border-right-style:solid; border-right-width:1px; border-right-color:black; ">&nbsp;</td>
                </tr>
                
                <tr>
                   <td style="text-align:left;">&nbsp;</td>
                   <td class="dc15b" colspan="1" style="text-align:left; border-left-style:solid; border-left-width:1px; border-left-color:black; padding-left: 10px;">TRANSPORTER<br>3</td>
                   <td class="dc15"  colspan="3" style=" text-align:left;">&nbsp;</td>
                   <td class="dc15b" colspan="2" style="text-align:left; padding-left: 10px;">&nbsp;</td>
                   <td class="dc15b"  colspan="2" style="text-align:left; border-right-style:none; border-right-width:1px; border-right-color:black; padding-right: 10px;">LOADED&nbsp;BY&nbsp;NAME</td>
                   <td class="dc3" style="text-align:left; border-right-style:solid; border-right-width:1px; border-right-color:black; ">_______________________________________</td>
                </tr>                
                <tr>
                   <td style="text-align:left;">&nbsp;</td>
                   <td class="dc15b" colspan="1" style="text-align:left; border-left-style:solid; border-left-width:1px; border-left-color:black; padding-left: 10px;">DATE</td>
                   <td class="dc7"   colspan="3" style="text-align:left;">&nbsp;</td>
                   <td class="dc15b" colspan="2" style="text-align:left; padding-left: 10px;">TIME LOADED</td>
                   <td class="dc7"   colspan="3" style="text-align:left; border-right-style:solid; border-right-width:1px; border-right-color:black; padding-left: 10px;">&nbsp;</td>
                   <td style="text-align:left;">&nbsp;</td>
                </tr>
                </tr>
                   <td colspan="1" style="text-align:left;">&nbsp;</td>
                   <td class="lr" colspan="9">&nbsp;</td>
                   <td colspan="1" style="text-align:left;">&nbsp;</td>
                </tr>      
                <tr>
                   <td style="text-align:left;">&nbsp;</td>
                   <td class="dc15b" colspan="1" style="text-align:left; border-left-style:solid; border-left-width:1px; border-left-color:black; padding-left: 10px;">VARIFIED&nbsp;BY</td>
                   <td class="dc7"   colspan="3" style="text-align:left;">&nbsp;</td>
                   <td class="dc15b" colspan="2" style="text-align:left; padding-left: 10px;">&nbsp;</td>
                   <td class="dc7"   colspan="3" style="text-align:left; border-bottom-style:none; border-right-style:solid; border-right-width:1px;">&nbsp;</td>
                   <td style="text-align:left;">&nbsp;</td>
                </tr>
                <tr>
                   <td colspan="1" style="text-align:left;">&nbsp;</td>
                   <td class="lr" colspan="9">&nbsp;</td>
                   <td colspan="1" style="text-align:left;">&nbsp;</td>
                </tr>           	
                <tr>
                   <td colspan="1" style="text-align:left;">&nbsp;</td>
                   <td class="th1" colspan="1" style="text-align:left; padding-left: 10px; border-right-style:none;">Document No</td>
                   <td class="th1" colspan="1" style="text-align:left; padding-left: 10px; border-left-style:none ;">&nbsp;</td>
                   <td class="th1" colspan="6" style="text-align:left; padding-left: 10px;">Store</td>
                   <td class="th1" colspan="1" style="text-align:right; padding-right: 10px;;">Total&nbsp;Qty</td>
                   <td colspan="1" style="text-align:left;">&nbsp;</td>         
                </tr>
                <?php
                $sstore = '';	
                sort($LoadSheet['Documents']); ?>
                <tr>
                      <td colspan="1" style="text-align:left;">&nbsp;</td>
                      <td class="dc13" colspan="1" style="text-align:left; border-left-style:solid; border-left-width:1px; border-left-color:black; padding-left: 10px;">&nbsp;</td>
                      <td class="dc13" colspan="1" style="text-align:left; border-left-style:none; border-left-width:1px; border-left-color:black; padding-left: 10px;">&nbsp;</td>
                      <td class="dc13" colspan="6" style="text-align:left; border-left-style:solid; border-left-width:1px; border-left-color:black; border-right-style:solid; border-right-width:1px; border-right-color:black; padding-left: 10px;">&nbsp;</td>
                      <td class="dc13" colspan="1" style="text-align:right; border-right-style:solid; border-right-width:1px; border-right-color:black; padding-right: 10px;">&nbsp;</td>
                      <td colspan="1" style="text-align:left;">&nbsp;</td>         
                </tr>
                <?php
                
 //             echo "<pre>";
                
 //             print_r($LoadSheet['Documents']);
                
                foreach($LoadSheet['Documents'] as $to) {                	
                     if($sstore <>  $to['docno'] . $to['store']) 	{ 	
                     	
                     	   if(trim($to['prShortN']) <> '') {$prinSn = trim($to['prShortN']);} else { $prinSn = trim($loadd[0]['Principal']); }
                     	?>
                         <tr>
                              <td colspan="1" style="text-align:left;">&nbsp;</td>
                              <td class="dc13" colspan="1" style="text-align:left; border-left-style:solid; border-left-width:1px; border-left-color:black; padding-left: 10px;"><?php echo $prinSn . ' - ' . ltrim($to['docno'],"0");?></td>
                              <td class="dc13" colspan="1" style="text-align:left; border-left-style:none; border-left-width:1px; border-left-color:black; padding-left: 10px;">&nbsp;</td>
                              <td class="dc13" colspan="6" style="text-align:left; border-left-style:solid; border-left-width:1px; border-left-color:black; border-right-style:solid; border-right-width:1px; border-right-color:black; padding-left: 10px;"><?php echo $to['store'];?></td>
                              <td class="dc13" colspan="1" style="text-align:right; border-left-style:solid; border-left-width:1px; border-left-color:black; border-right-style:solid; border-right-width:1px; border-right-color:black; padding-right: 10px;"><?php echo $to['cases'];?></td>
                              <td colspan="1" style="text-align:left;">&nbsp;</td>         
                         </tr>
                        <tr>
                              <td colspan="1" style="text-align:left;">&nbsp;</td>
                              <td class="dc13" colspan="1" style="text-align:left; border-left-style:solid; border-left-width:1px; border-left-color:black; padding-left: 10px;">&nbsp;</td>
                              <td class="dc13" colspan="1" style="text-align:left; border-left-style:none; border-left-width:1px; border-left-color:black; padding-left: 10px;">&nbsp;</td>
                              <td class="dc13" colspan="6" style="text-align:left; border-left-style:solid; border-left-width:1px; border-left-color:black; border-right-style:solid; border-right-width:1px; border-right-color:black; padding-left: 10px;">&nbsp;</td>
                              <td class="dc13" colspan="1" style="text-align:right; border-right-style:solid; border-right-width:1px; border-right-color:black; padding-right: 10px;">&nbsp;</td>
                              <td colspan="1" style="text-align:left;">&nbsp;</td>         
                         </tr>
                     	   <?php            	
                          $sstore =  $to['docno'] . $to['store'] ;
                     }
                } ?>
                <tr>
                   <td colspan="1" style="text-align:left;">&nbsp;</td>
                   <td class="dc13" colspan="1" style="text-align:right; border-style:none none solid solid; border-width:1px; border-color:black; padding-right: 10px;">&nbsp;</td>
                   <td class="dc13" colspan="1" style="text-align:right; border-style:none solid solid none;  border-width:1px; border-color:black; padding-right: 10px;">&nbsp;</td>
                   <td class="dc13" colspan="6" style="text-align:right; border-style:none solid solid solid; border-width:1px; border-color:black; padding-right: 10px;">&nbsp;</td>
                   <td class="dc13" colspan="1" style="text-align:right; border-style:none solid solid solid; border-width:1px; border-color:black; padding-right: 10px;">&nbsp;</td>
                   <td colspan="1" style="text-align:left;">&nbsp;</td>
                </tr>  
                <tr>
                   <td colspan="1" style="text-align:left;">&nbsp;</td>
                   <td class="th1" colspan="2" style="text-align:left; padding-left: 10px;">Product</td>
                   <td class="th1" colspan="6" style="text-align:left; padding-left: 10px;">Loading Quantities</td>
                   <td class="th1" colspan="1" style="text-align:right; padding-right: 10px;;">Total&nbsp;Qty</td>
                   <td colspan="1" style="text-align:left;">&nbsp;</td>         
                </tr>
                <tr>
                   <td style="width:  2%; text-align:left;">&nbsp;</td>
                   <td style="width: 20%; border-left-style:solid; border-left-width:1px; border-left-color:black;">&nbsp;</td>
                   <td style="width: 10%; border-right-style:solid; border-right-width:1px; border-right-color:black;">&nbsp;</td>
                   <td style="width: 10%; text-align:left;">&nbsp;</td>         	
                   <td style="width: 10%; text-align:left;">&nbsp;</td>
                   <td style="width: 10%; text-align:left;">&nbsp;</td>         	
                   <td style="width: 10%; text-align:left;">&nbsp;</td>
                   <td style="width: 10%; text-align:left;">&nbsp;</td>         	
                   <td style="width: 5%;  border-right-style:solid; border-right-width:1px; border-right-color:black;"">&nbsp;</td>
                   <td style="width: 11%; border-right-style:solid; border-right-width:1px; border-right-color:black;">&nbsp;</td>
                   <td style="width:  2%; text-align:left;">&nbsp;</td>
                </tr>
                <?php                
                $x= 0;
                $newTd     = 'N';
                $prodD     = '';
                $qtyString = '';
                
                foreach($lineArray as $row) { 
                	       $gTotal    = $gTotal + $row['Tot']; ?>
                         <tr>
                            <td colspan="1" style="text-align:left;">&nbsp;</td>
                            <td class="dc13" colspan="2" style="text-align:left; border-left-style:solid; border-left-width:1px; border-left-color:black; padding-left: 10px;"><?php echo $row['pd']; ?></td>
                            <td class="dc13" colspan="6" style="text-align:left; border-left-style:solid; border-left-width:1px; border-left-color:black; border-right-style:solid; border-right-width:1px; border-right-color:black; padding-left: 10px;"><?php echo $row['row']; ?></td>
                            <td class="dc13" colspan="1" style="text-align:right; border-left-style:solid; border-left-width:1px; border-left-color:black; border-right-style:solid; border-right-width:1px; border-right-color:black; padding-right: 10px;"><?php echo $row['Tot']; ?></td>
                            <td colspan="1" style="text-align:left;">&nbsp;</td>         
                         </tr>          
                         <tr>
                            <td colspan="1" style="text-align:left;">&nbsp;</td>
                            <td class="dc13" colspan="2" style="text-align:left; border-left-style:solid; border-left-width:1px; border-left-color:black; padding-left: 10px;">&nbsp;</td>
                            <td class="dc13" colspan="6" style="text-align:left; border-left-style:solid; border-left-width:1px; border-left-color:black; border-right-style:solid; border-right-width:1px; border-right-color:black; padding-left: 10px;">&nbsp;</td>
                            <td class="dc13" colspan="1" style="text-align:right; border-left-style:solid; border-left-width:1px; border-left-color:black; border-right-style:solid; border-right-width:1px; border-right-color:black; padding-right: 10px;">&nbsp;</td>
                            <td colspan="1" style="text-align:left;">&nbsp;</td>         
                         </tr>                     
                               		
                <?php
                } ?>
                <tr>
                   <td colspan="1" style="text-align:left;">&nbsp;</td>
                   <td class="dc13" colspan="2" style="text-align:right; border-style:none solid solid solid; border-width:1px; border-color:black; padding-right: 10px;">&nbsp;</td>
                   <td class="dc13" colspan="6" style="text-align:right; border-style:none solid solid solid; border-width:1px; border-color:black; padding-right: 10px; font-weight:bold;">Total</td>
                   <td class="dc13" colspan="1" style="text-align:right; border-style:none solid solid solid; border-width:1px; border-color:black; padding-right: 10px;"><?php echo $gTotal; ?></td>
                   <td colspan="1" style="text-align:left;">&nbsp;</td>
                </tr>  


               <tr>
                   <td colspan="1" style="text-align:left;">&nbsp;</td>
                   <td class="dc13b" colspan="2" style="font-size:15px; text-align:left; border-bottom-style:none; border-bottom-width:1px; border-left-style:solid; border-left-width:1px; border-left-color:black; padding-left: 10px;">Returned Stock</td>
                   <td class="dc13b" colspan="7" style="font-size:15px; text-align:left; border-bottom-style:none; border-bottom-width:1px; border-left-style:solid; border-left-width:1px; border-left-color:black; border-right-style:solid; border-right-width:1px; border-right-color:black; padding-left: 10px;">Reason</td>
                   <td colspan="1" style="text-align:left;">&nbsp;</td>         
               </tr>  
               <tr>
                   <td colspan="1" style="text-align:left;">&nbsp;</td>
                   <td class="dc13" colspan="2" style="text-align:left; border-left-style:solid; border-left-width:1px; border-left-color:black; padding-left: 10px;">&nbsp;</td>
                   <td class="dc13" colspan="7" style="text-align:left; border-left-style:solid; border-left-width:1px; border-left-color:black; border-right-style:solid; border-right-width:1px; border-right-color:black; padding-left: 10px;">&nbsp;</td>
                   <td colspan="1" style="text-align:left;">&nbsp;</td>         
               </tr>  
               <tr>
                   <td colspan="1" style="text-align:left;">&nbsp;</td>
                   <td class="dc13" colspan="2" style="text-align:left; border-left-style:solid; border-bottom-style:solid; border-bottom-width:1px; border-left-width:1px; border-left-color:black; padding-left: 10px;">&nbsp;</td>
                   <td class="dc13" colspan="7" style="text-align:left; border-left-style:solid; border-bottom-style:solid; border-bottom-width:1px; border-left-width:1px; border-left-color:black; border-right-style:solid; border-right-width:1px; border-right-color:black; padding-left: 10px;">&nbsp;</td>
                   <td colspan="1" style="text-align:left;">&nbsp;</td>         
               </tr>  


               <tr>
                   <td colspan="1" style="text-align:left;">&nbsp;</td>
                   <td class="dc13b" colspan="2" style="font-size:15px; text-align:left; border-bottom-style:none; border-bottom-width:1px; border-left-style:solid; border-left-width:1px; border-left-color:black; padding-left: 10px;">Damaged Stock</td>
                   <td class="dc13b" colspan="7" style="font-size:15px; text-align:left; border-bottom-style:none; border-bottom-width:1px; border-left-style:solid; border-left-width:1px; border-left-color:black; border-right-style:solid; border-right-width:1px; border-right-color:black; padding-left: 10px;">Reason</td>
                   <td colspan="1" style="text-align:left;">&nbsp;</td>         
               </tr>  
               <tr>
                   <td colspan="1" style="text-align:left;">&nbsp;</td>
                   <td class="dc13" colspan="2" style="text-align:left; border-left-style:solid; border-left-width:1px; border-left-color:black; padding-left: 10px;">&nbsp;</td>
                   <td class="dc13" colspan="7" style="text-align:left; border-left-style:solid; border-left-width:1px; border-left-color:black; border-right-style:solid; border-right-width:1px; border-right-color:black; padding-left: 10px;">&nbsp;</td>
                   <td colspan="1" style="text-align:left;">&nbsp;</td>         
               </tr>  
               <tr>
                   <td colspan="1" style="text-align:left;">&nbsp;</td>
                   <td class="dc13" colspan="2" style="text-align:left; border-left-style:solid; border-bottom-style:solid; border-bottom-width:1px; border-left-width:1px; border-left-color:black; padding-left: 10px;">&nbsp;</td>
                   <td class="dc13" colspan="7" style="text-align:left; border-left-style:solid; border-bottom-style:solid; border-bottom-width:1px; border-left-width:1px; border-left-color:black; border-right-style:solid; border-right-width:1px; border-right-color:black; padding-left: 10px;">&nbsp;</td>
                   <td colspan="1" style="text-align:left;">&nbsp;</td>         
               </tr>  
               
               <tr>
                   <td colspan="1" style="text-align:left;">&nbsp;</td>
                   <td class="dc13b" colspan="2" style="font-size:15px; text-align:left; border-bottom-style:none; border-bottom-width:1px; border-left-style:solid; border-left-width:1px; border-left-color:black; padding-left: 10px;">Number of pallets sent</td>
                   <td class="dc13b" colspan="5" style="font-size:15px; text-align:left; border-bottom-style:none; border-bottom-width:1px; border-left-style:solid; border-left-width:1px; border-left-color:black; border-right-style:solid; border-right-width:1px; border-right-color:black; padding-left: 10px;">Number of pallets Returned</td>
                   <td class="dc13b" colspan="2" style="font-size:15px; text-align:left; border-bottom-style:none; border-bottom-width:1px; border-left-style:solid; border-left-width:1px; border-right-style:solid; border-right-width:1px; border-left-color:black; padding-left: 10px;">Pallets left at Store </td>
                   <td colspan="1" style="text-align:left;">&nbsp;</td>         
               </tr>  
               <tr>
                   <td colspan="1" style="text-align:left;">&nbsp;</td>
                   <td class="dc13" colspan="2" style="text-align:left; border-left-style:solid; border-left-width:1px; border-left-color:black; padding-left: 10px;">&nbsp;</td>
                   <td class="dc13" colspan="5" style="text-align:left; border-left-style:solid; border-left-width:1px; border-left-color:black; border-right-style:solid; border-right-width:1px; border-right-color:black; padding-left: 10px;">&nbsp;</td>
                   <td class="dc13" colspan="2" style="text-align:left; border-left-style:solid; border-left-width:1px; border-left-color:black; border-right-style:solid; border-right-width:1px; border-right-color:black; padding-left: 10px;">&nbsp;</td>
                   <td colspan="1" style="text-align:left;">&nbsp;</td>         
               </tr>  
               <tr>
                   <td colspan="1" style="text-align:left;">&nbsp;</td>
                   <td class="dc13" colspan="2" style="text-align:left; border-left-style:solid; border-bottom-style:solid; border-bottom-width:1px; border-left-width:1px; border-left-color:black; padding-left: 10px;">&nbsp;</td>
                   <td class="dc13" colspan="5" style="text-align:left; border-left-style:solid; border-bottom-style:solid; border-bottom-width:1px; border-left-width:1px; border-left-color:black; border-right-style:solid; border-right-width:1px; border-right-color:black; padding-left: 10px;">&nbsp;</td>
                   <td class="dc13" colspan="2" style="text-align:left; border-left-style:solid; border-bottom-style:solid; border-bottom-width:1px; border-left-width:1px; border-left-color:black; border-right-style:solid; border-right-width:1px; border-right-color:black; padding-left: 10px;">&nbsp;</td>
                   <td colspan="1" style="text-align:left;">&nbsp;</td>         
               </tr>                 
               
               <tr>
                   <td colspan="1" style="text-align:left;">&nbsp;</td>
                   <td class="dc13" colspan="4" style="text-align:left; border-bottom-style:solid; border-bottom-width:1px; border-bottom-color:black; padding-bottom: 10px;">Disclaimer: The undersigned hereby acknowledges receipt and delivery of the goods described on the annexed list or invoice and further acknowledges that said goods have been inspected<br>and are without defect and the correct quantity<br><br>Signature:</td>
                   <td class="dc13" colspan="4" style="text-align:left; border-bottom-style:solid; border-bottom-width:1px; border-left-color:black; border-bottom-style:solid; border-right-width:1px; border-right-color:black; padding-left: 10px;">&nbsp;</td>
                   <td colspan="1"><img src="<?php echo $ROOT.$PHPFOLDER; ?>images/Kwelanga_Solutions_Logo_smaller.jpg" style="width:40px; height:30px; float:right;" ></td>
                   <td colspan="1" style="text-align:left;">&nbsp;</td>         
               </tr>
              <tr>
                   <td colspan="9" >&nbsp;</td>
                   <td colspan="1"; style="text-align:right; color:grey; font-weight:normal; font-size:0.55em;"><?php echo date('Y-m-d H:i:s'); ?></td>
                   <td colspan="1" >&nbsp;</td>
              </tr>
              <tr>
                   <td colspan="9" >&nbsp;</td>
                   <td colspan="1"; style="text-align:right; color:grey; font-weight:normal; font-size:0.55em;"><?php echo 'load_sheet_document_multi_version4'; ?></td>
                   <td colspan="1" >&nbsp;</td>
              </tr>
        </table> 
       <p id="page-break">--- End of Page---</p>


    </body>
 </html>

<?php
$dbConn->dbClose();
?>
