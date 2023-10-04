<?php 
include_once('ROOT.php'); 
include_once($ROOT.'PHPINI.php');	
include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
include_once($ROOT.$PHPFOLDER.'DAO/manageClaimsClass.php');
include_once($ROOT.$PHPFOLDER.'DAO/ManageClaimsDAO.php');
include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");

class manageClaims {
	
      function __construct() {

         global $dbConn;
         $this->dbConn = $dbConn;
      }	

// ********************************************************************************************************************************************************
      public function selectClaimsToManage($principalID) {
      	
          $class = 'even';
          
          $manageClaims = new ManageClaimsDAO($this->dbConn);
          $clmStart = $manageClaims->getClaimStartDate($principalID);
          
          $startDate = $clmStart[0]['checkers_ws_starting_claims_date'];
          
          $clmLst = $manageClaims->getClaimlist($principalID, $startDate);      	 
      	
          // Get Unmanaged claims - Call to manageClaimsDAO
          // Document number
          // Date
          // type
          // Store
          // Claim Reference number
          // Warehouse
          // qty
          // value
          
          ?>
          <center>
                <form name='Un Managed Claims' method=post action=''>
                    <table style="width: 1200px; border:none";>
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
                             <td>&nbsp;</td>
                             <td>&nbsp;</td>
                         </tr>
                         <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                             <td class="head2" colspan="11"; style="text-align:center;">Mange Buyer Generated Claims</td>
                         </tr>
                         <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                             <td colspan="11"; style="text-align:center;">&nbsp;</td>
                         </tr>
                         <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                             <td class="det1" style="width: 2%;" >&nbsp;</td>
                             <td class="det1" style="width: 7%;">Doc&nbsp;No</td>
                             <td class="det1" style="width: 10%;">Date</td>
                             <td class="det1" style="width: 15%;">Type</td>
                             <td class="det1" style="width: 30%;">Store</td>
                             <td class="det1" style="width: 5%;" >Status</td>
                             <td class="det1" style="width: 7%;">Depot</td>
                             <td class="det1" style="width: 8%;" >Claim&nbsp;No</td>
                             <td class="det1" style="width: 9%; text-align:right;" >Value</td>
                             <td class="det1" style="width: 5%;" >Manage</td>
                             <td class="det1" style="width: 2%;" >&nbsp</td>                             
                         </tr>
                         <?php
                         foreach ($clmLst as $row) { 
                         	    $docNo = "<a href=\"javascript:;\" onClick=\"window.open('https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/functional/presentations/presentationManagement.php?TYPE=BUYER ORIGINATED CLAIM&DSTATUS=".trim($row['StatUid'])."&CSOURCE=T&FINDNUMBER=".trim($row['dmUid']) ."','myDocumentProcessing','scrollbars=yes,width=750,height=600,resizable=yes');\">".trim($row['Docno'])."</a>";
                              ?>
                              <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                                   <td class="det2" >&nbsp;</td>
                                   <td class="det2" ><?php echo $docNo; ?></td>
                                   <td class="det2" ><?php echo $row['invoice_date']; ?></td>
                                   <td class="det2" ><?php echo $row['Add_Type']; ?></td>
                                   <td class="det2" ><?php echo trim($row['deliver_name']); ?></td>
                                   <td class="det2" ><?php echo $row['Status']; ?></td>
                                   <td class="det2" ><?php echo trim($row['Short_Wh']); ?></td>
                                   <td class="det2" ><?php echo $row['customer_order_number']; ?></td>
                                   <td class="det2" style="text-align:right;"><?php echo $row['Total']; ?></td>
                                   <td class="det2" style="text-align:center;"><INPUT TYPE="checkbox" name="SELLIST[]" value= "<?php echo $row['dmUid'];?>"></td>
                                   <td class="det2"  >&nbsp;</td>                             
                              </tr>
                         <?php
                         } 
                         ?>
                         <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                             <td colspan="11"; style="text-align:center;">&nbsp;</td>
                         </tr>
                         <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                             <td colspan="11"; style="text-align:center;">&nbsp;</td>
                         </tr>
                         <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                             <td colspan="11"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="CREDITSELECTED" value= "Credit Selected">
                 	                                                        <INPUT TYPE="submit" class="submit" name="REJECTSELECTED" value= "Reject Selected">
                 	                                                        <INPUT TYPE="submit" class="submit" name="BACK"   value= "Back">
                                                                          <INPUT TYPE="submit" class="submit" name="CANFORM"   value= "Cancel"></td>
                         </tr>          
                         <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                             <td colspan="11"; style="text-align:center;">&nbsp;</td>
                         </tr>
                    </table>
                
                </form> 
          </center>
          
          <?php 
             	
      	
      	
      }

}


?>