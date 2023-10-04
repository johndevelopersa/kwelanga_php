<?php 
include_once('ROOT.php'); 
include_once($ROOT.'PHPINI.php');	
include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
include_once($ROOT.$PHPFOLDER.'DAO/TripsheetDAO.php');


    class managePlanningClass{
	
      function __construct() {

         global $dbConn;
         $this->dbConn = $dbConn;
      }	
      
// ********************************************************************************************************************************	      

    public function MangeSelected($docList, $loadSheetNumber, $removelList ) {
    	
    	    global $ROOT; global $PHPFOLDER;
    	
          $class = 'even';
          
          $LoadPlanningDAO = new LoadPlanningDAO($this->dbConn);
          $loadLst = $LoadPlanningDAO->getDocumentsForManaging($docList, $removelList);
          
//          print_r($loadLst);
          
          ?>
          <center>
                <form name='Manage Documents' method=post action=''>
                    <table style="width: 900px; border:none";>
                         <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                             <td>&nbsp;</td>
                             <td>&nbsp;</td>
                             <td>&nbsp;</td>
                             <td>&nbsp;</td>
                             <td>&nbsp;</td>
                             <td>&nbsp;</td>
                             <td>&nbsp;</td>
                             <td>&nbsp;</td>
                             <td>&nbsp;</td>
                         </tr>
                         <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                             <td class="head1" colspan="9"; style="text-align:center;">Mange Documents for Loading</td>
                         </tr>
                         <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                             <td colspan="9"; style="text-align:center;"><INPUT TYPE="hidden" name="MAINLIST"  value= "<?php echo $docList;?>">
                             	                                           <INPUT TYPE="hidden" name="REMOVELIST"  value= "<?php echo $removelList;?>"></td>
                         </tr>
                         <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                             <td colspan="9"; style="text-align:center;">&nbsp;</td>
                         </tr>
                         <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                             <td class="det1" style="width: 2%;" >&nbsp;</td>
                             <td class="det1" style="width: 7%;">Doc&nbsp;No</td>
                             <td class="det1" style="width: 15%;">Date</td>
                             <td class="det1" style="width: 40%;">Store</td>
                             <td class="det1" style="width: 15%;" >P&nbsp;O</td>
                             <td class="det1" style="width: 5%;" >Cases</td>
                             <td class="det1" style="width: 9%; text-align:right;" >Value</td>
                             <td class="det1" style="width: 5%;" >Manage</td>
                             <td class="det1" style="width: 2%;" >&nbsp</td>                             
                         </tr>
                         <?php
                         $c=0;
                         foreach ($loadLst as $row) { 
                         	    $docNo = "<a href=\"javascript:;\" onClick=\"window.open('https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/functional/presentations/presentationManagement.php?TYPE=BUYER ORIGINATED CLAIM&DSTATUS=".trim($row['StatUid'])."&CSOURCE=T&FINDNUMBER=".trim($row['docuid']) ."','myDocumentProcessing','scrollbars=yes,width=750,height=600,resizable=yes');\">".ltrim($row['document_number'],'0')."</a>";
                              $c++
                              ?>
                              <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                                   <td class="det2" >&nbsp;</td>
                                   <td class="det2" ><?php echo $docNo; ?></td>
                                   <td class="det2" ><?php echo $row['order_date']; ?></td>
                                   <td class="det2" ><?php echo trim($row['deliver_name']); ?></td>
                                   <td class="det2" ><?php echo $row['customer_order_number']; ?></td>
                                   <td class="det2" style="text-align:right;"><?php echo $row['cases']; ?></td>
                                   <td class="det2" style="text-align:right;"><?php echo round($row['invoice_total'],2); ?></td>
                                   <?php 
                                   if($row['amended'] <> $loadSheetNumber) { ?>
                                        <td class="det2" style="text-align:center;"><INPUT TYPE="radio" name="AMLIST" value= "<?php echo $row['docuid'];?>"></td>
                                   <?php	
                                   } elseif($row['amended'] == 999999) { ?>
                                        <td class="det2" style="text-align:center;"><img src="<?php echo $ROOT.$PHPFOLDER.'images/removed.png';?>" width=15px; height=15px; ></td>
                                   <?php
                                   } else { ?>
                                        <td class="det2" style="text-align:center;"><img src="<?php echo $ROOT.$PHPFOLDER.'images/ok.png';?>" width=15px; height=15px; ></td>
                                   <?php	
                                   } ?>
                                   <td class="det2"  >&nbsp;</td>
                              </tr>
                              <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                                   <td colspan="9"; style="text-align:center;">&nbsp;</td>
                              </tr>
                         <?php
                         } 
                         ?>
                         <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                             <td colspan="9"; style="text-align:center;"><INPUT TYPE="hidden" name="LOADNUMBER"  value= "<?php echo $loadSheetNumber;?>"></td>
                         </tr>
                         <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                             <td colspan="9"; style="text-align:center;">&nbsp;</td>
                         </tr>
                         <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                             <td colspan="9"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="MANSELECTED" value= "Change Selected">
                             	                                           <INPUT TYPE="submit" class="submit" name="ACCLOAD" value= "Get Load Sheet">
                 	                                                       <INPUT TYPE="submit" class="submit" name="BACK"   value= "Back"></td>
                         </tr>          
                         <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                             <td colspan="9"; style="text-align:center;">&nbsp;</td>
                         </tr>
                    </table>
                
                </form> 
    <?php
    }	

// ********************************************************************************************************************************	      

    public function AmendLoadingDoc($docUid, $main, $loadListNumber) {
    	
          $class = 'even';
      	 
          $LoadPlanningDAO = new LoadPlanningDAO($this->dbConn);
          $amendLst = $LoadPlanningDAO->getDocumentsToAmend($docUid, $loadListNumber);
          
//         print_r($amendLst);
          
          ?>
          <center>
                <form name='Amend Document' method=post action=''>
                    <table style="width: 900px; border:none";>
                         <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                             <td>&nbsp;</td>
                             <td>&nbsp;</td>
                             <td>&nbsp;</td>
                             <td>&nbsp;</td>
                             <td>&nbsp;</td>
                             <td>&nbsp;</td>
                             <td>&nbsp;</td>
                         </tr>
                         <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                             <td class="head1" colspan="7"; style="text-align:center;">Amend Document</td>
                         </tr>
                         <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                             <td colspan="7"; style="text-align:center;"><INPUT TYPE="hidden" name="TOMAINLIST"  value= "<?php echo $main;?>">
                             	                                           <INPUT TYPE="hidden" name="LOADNUMBER"  value= "<?php echo $loadListNumber;?>"></td>
                         </tr>
                         <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                             <td class="det1">&nbsp;</td>
                             <td class="det1" colspan="2" style="text-align:left;"><span style="font-weight:bold;">Store&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="font-weight:normal;"><?php echo trim($amendLst[0]['deliver_name']);?></span></td>
                             <td class="det1" colspan="3" style="text-align:right;"><span style="font-weight:bold;">Date&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="font-weight:normal;"><?php echo trim($amendLst[0]['order_date']);?></span></td>
                             <td class="det1" >&nbsp</td>                             
                         </tr>
                         <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                             <td colspan="7"; style="text-align:center;">&nbsp;</td>
                         </tr>
                         <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                             <td class="det1">&nbsp;</td>
                             <td class="det1" colspan="2" style="text-align:left;"><span style="font-weight:bold;">P.O. Number&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="font-weight:normal;"><?php echo trim($amendLst[0]['customer_order_number']);?></span></td>
                             <td class="det1" colspan="3" style="text-align:right;"><span style="font-weight:bold;">Quantity&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="font-weight:normal;"><?php echo trim($amendLst[0]['cases']);?></span></td>
                             <td class="det1" >&nbsp</td>                             
                         </tr>
                         <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                             <td colspan="7"; style="text-align:center;">&nbsp;</td>
                         </tr>                         
                         <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                             <td class="det1" style="width: 2%;" >&nbsp;</td>
                             <td class="det1" style="width: 10%;">Product&nbsp;Code</td>
                             <td class="det1" style="width: 48%;">Product</td>
                             <td class="det1" style="width: 10%; text-align:right;">SOH</td>
                             <td class="det1" style="width: 10%; text-align:right;">Ordered</td>                             
                             <td class="det1" style="width: 15%*;" >Amended</td>
                             <td class="det1" style="width: 5%;" >&nbsp</td>                             
                         </tr>
                         <?php
                         foreach ($amendLst as $row) { ?>
                         <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                             <td class="det2" style="width: 2%;" >&nbsp;</td>
                             <td class="det2" style="width: 10%;"><?php echo $row['product_code']; ?></td>
                             <td class="det2" style="width: 48%;"><?php echo $row['product_description']; ?></td>
                             <td class="det2" style="width: 10%; text-align:right;"><?php echo $row['available']; ?></td>
                             <td class="det2" style="width: 10%; text-align:right;"><?php echo $row['ordered_qty']; ?></td>                             
                             <td class="det2" style="width: 15%;" ><input type="text" size="5" name="AQTY[]" value="<?php echo $row['ordered_qty']; ?>"></td>
                             <td class="det1" style="width: 5%;" ><INPUT TYPE="hidden" name="DDROW[]"  value= "<?php echo $row['dduid'];?>">
                             	                                    <INPUT TYPE="hidden" name="DDORD[]"  value= "<?php echo $row['cases'];?>"></td>                             
                         </tr>
                         <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                             <td colspan="7"; style="text-align:center;">&nbsp;</td>
                         </tr>   

                         <?php
                         } 
                         ?>
                         <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                             <td colspan="7"; style="text-align:center;">&nbsp;</td>
                         </tr>
                         <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                             <td colspan="2"; style="text-align:center;">&nbsp;</td>
                             <td colspan="3"; class="det1" style="text-align:right;">I&nbsp;accept&nbsp;these&nbsp;quantities&nbsp;:&nbsp;</td>
                             <td colspan="2"; style="text-align:left;"><INPUT TYPE="checkbox" name="UACC" value= ""></td>
                         </tr>   
                         <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                             <td colspan="7"; style="text-align:center;">&nbsp;</td>
                         </tr>
                         <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                             <td colspan="7"; style="text-align:center;"><INPUT TYPE="submit" class="submit"  name="NEXTDOC" value= "Accept Changes">
                 	                                                        <INPUT TYPE="submit" class="submit" name="CANDOC"   value= "Back">
                                                                          <INPUT TYPE="submit" class="submit" name="CANFORM"   value= "Cancel"></td>
                         </tr>          
                         <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                             <td colspan="7"; style="text-align:center;">&nbsp;</td>
                         </tr>
                    </table>                
                </form>
          </center>
    <?php
    }	
// ********************************************************************************************************************************	      

    public function SelectTranporter($depotId, $main, $loadSheetnumber) {
    	
          $TripsheetDAO = new TripsheetDAO($this->dbConn);     
          $mfTS = $TripsheetDAO->getTripSheetTransporter2($depotId, '', '');
 
          $class = 'even';
          
//         print_r($amendLst);
          
          ?>
          <center>
                <form name='Amend Document' method=post action=''>
                    <table style="width: 900px; border:none";>
                         <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                             <td style="width: 20%;">&nbsp;</td>
                             <td style="width: 20%;">&nbsp;</td>
                             <td style="width: 20%;">&nbsp;</td>
                             <td style="width: 20%;">&nbsp;</td>
                             <td style="width: 20%;">&nbsp;</td>
                         </tr>
                         <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                             <td class="head1" colspan="5"; style="text-align:center;">Create Planning Documents</td>
                         </tr>
                         <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                             <td colspan="5"; style="text-align:center;">&nbsp;</td>
                         </tr>

                         <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                             <td colspan="1"; style="text-align:center;">&nbsp;</td>
                             <td class="det1" colspan="1"; style="text-align:right;">Load Sheet Number</td>
                             <td class="det2" colspan="1"; style="text-align:left;"><?php echo $loadSheetnumber;?></td>
                         </tr>
                         <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                             <td colspan="5"; style="text-align:center;">&nbsp;</td>
                         </tr>


                         <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                             <td colspan="5"; style="text-align:center;"><INPUT TYPE="hidden" name="TOMAINLIST"  value= "<?php echo $main;?>">
                             	                                           <INPUT TYPE="hidden" name="LOADNUMBER"  value= "<?php echo $loadSheetnumber;?>"></td>
                         </tr>
                         <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                             <td class="det2">&nbsp;</td>
                             <td class="det1">Select Transporter</td>    
                             <td colspan="2">
                                <select name="TRANSPORTER" id="TRANSPORTER">
                                     <option value="Select Transporter">Select Transporter</option>
                                     <?php foreach($mfTS as $row) { ?>
                                         <option value="<?php echo $row['uid']; ?>"><?php echo $row['name']; ?></option>
                                     <?php } ?>
                                 </select>
                             </td>
                             <td class="det2">&nbsp;</td>
                         </tr>
                         <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                             <td colspan="5"; style="text-align:center;">&nbsp;</td>
                         </tr>   
                         <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                             <td colspan="5"; style="text-align:center;">&nbsp;</td>
                         </tr> 
                         <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                             <td colspan="1"; style="text-align:center;">&nbsp;</td>
                             <td class="det1" colspan="2" style="text-align:left";>Print&nbsp;Load&nbsp;Sheet&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE="checkbox" name="LOADSHEET" value= "1" CHECKED></td>
                             <td class="det1" colspan="2" style="text-align:left";>Print Each Store Document&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE="checkbox" name="EACHSTORE" value= "1" CHECKED></td>
                             
                         </tr>    
                         <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                             <td colspan="7"; style="text-align:center;">&nbsp;</td>
                         </tr>
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                              <td colspan="1" style="text-align:center;">&nbsp;</td>
                              <td colspan="3"style="text-align:center";><INPUT TYPE="submit" class="submit" name="FINISH" value= "Create Load Sheets"></td>
                              <td colspan="1" style="text-align:center;">&nbsp;</td>
                          </tr>
                         <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                             <td colspan="5"; style="text-align:center;">&nbsp;</td>
                         </tr>
                    </table>                
                </form>
          </center>
          <?php
    }
// ********************************************************************************************************************************	      

}