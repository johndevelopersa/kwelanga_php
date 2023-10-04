<?php 
include_once('ROOT.php'); 
include_once($ROOT.'PHPINI.php');	
include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER."DAO/MaintenanceDAO.php");
include_once($ROOT.$PHPFOLDER.'elements/basicSelectElement.php');
include_once($ROOT.$PHPFOLDER.'DAO/AdministrationDAO.php');	

class ManageExistingOrderScreens {
	
      function __construct() {

         global $ROOT, $PHPFOLDER, $dbConn;
         $this->dbConn = $dbConn;
      }	
      


// ********************************************************************************************************************************************************
      public function selectItemToManage($docUid, $userId, $principalId) {
      	
          $class = 'even';
          $manageOrders = new MaintenanceDAO($this->dbConn);
          $orderArr     = $manageOrders->getOrderDetailsToManage($docUid) ;
          
          $adminDAO = new AdministrationDAO($this->dbConn);
          
          $unCancelAllow = $chgWhAllow = $chgDebriefStatAllow = $chgReSetInvoiceAllow = $chgPoAllow = $amendAllow = 'disabled';
          
          $amendAccStat = $chgGrvAllow   = $chgClaimAllow = $chgDicountAllow = $CancelAllow = 'disabled';
          
          if(in_array($orderArr[0]['document_status_uid'], array(DST_UNACCEPTED,DST_ACCEPTED, DST_PROCESSED))) {
                $chgWhAllow       = '';
                $chgPoAllow       = '';
                $chgGrvAllow      = '';
                $chgClaimAllow    = '';
                $chgDicountAllow  = '';
                $CancelAllow      = ''; 
                $amendAllow       = '';         	
          }  
          if(in_array($orderArr[0]['document_status_uid'], array(DST_INPICK))) {
                $chgPoAllow       = '';
                $CancelAllow      = ''; 
          }
          if(in_array($orderArr[0]['document_status_uid'], array(DST_CANCELLED))) {
                $unCancelAllow = '';           	
          } 
          if(in_array($orderArr[0]['document_status_uid'], array(DST_DIRTY_POD))) {
                $chgPoAllow    = '';
                $chgGrvAllow   = '';
                $chgClaimAllow = '';            	
          } 
          if(in_array($orderArr[0]['document_status_uid'], array(DST_PROCESSED))) {
                $chgPoAllow       = '';
                $chgGrvAllow      = '';
                $chgClaimAllow    = '';           	
          }
          if(in_array($orderArr[0]['document_status_uid'], array(DST_DELIVERED_POD_OK))) {
                $chgDebriefStatAllow = '';
                $chgPoAllow  = '';
                $chgGrvAllow = '';
                $chgClaimAllow = '';           	
          } 
          if(in_array($orderArr[0]['document_status_uid'], array(DST_INVOICED))) {
                $chgReSetInvoiceAllow = '';
                $chgPoAllow           = '';           	
                $chgDicountAllow      = '';   
          } 
          if(in_array($orderArr[0]['document_status_uid'], array(DST_UNACCEPTED,DST_ACCEPTED))) {
                $amendAccStat         = '';   
          } 
          ?>
    <center>
       <FORM name='Select Action' method=post action=''>
            <table width="700"; style="border:none">
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                   <td Colspan = "8";>&nbsp;</td>
               </tr>	    
                   <td Colspan="8"; class="head1" style="text-align:center;" >Select Order Manage Action</td>
               </tr>	    
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                   <td Colspan = "8">&nbsp;</td>
               </tr>     	
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td style="width: 2%; border:none">&nbsp;</td>
                 <td style="width: 18%; border:none">&nbsp;</td>
                 <td style="width: 30%; border:none">&nbsp;</td>
                 <td style="width: 18%; border:none">&nbsp;</td>
                 <td style="width: 25%; border:none">&nbsp;</td>
                 <td style="width: 5%;  border:none">&nbsp;</td>
                 <td style="width: 5%;  border:none">&nbsp;</td>
                 <td style="width: 2%;  border:none">&nbsp;</td>
               </tr>

               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td style="border:none">&nbsp;</td>
                 <td class="det1" style="text-align:right;">Document</td>
                 <td class="det2" style="text-align:left;"><?php echo ltrim($orderArr[0]['document_number'],'0'); ?></td>
                 <td class="det1" style="border:none">Customer</td>
                 <td class="det2" colspan="2" style="border:none"><?php echo trim($orderArr[0]['store']); ?></td>
                 <td colspan="2" style="border:none">&nbsp;</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                   <td Colspan = "8">&nbsp;</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td style="border:none">&nbsp</td>
                 <td class="det1" style="text-align:right;">Status</td>
                 <td class="det2" style="text-align:left;"><?php echo ltrim($orderArr[0]['status'],'0'); ?></td>
                 <td class="det1" style="border:none">Warehouse</td>
                 <td class="det2" Colspan="2" style="border:none"><?php echo trim($orderArr[0]['wh']); ?></td>
                 <td colspan="2" style="border:none"><input type="hidden" id="DOCUID" name="DOCUID" value='<?php echo $orderArr[0]['uid']; ?>'></td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                   <td Colspan = "8">&nbsp;</td>
               </tr>
               <?php
               $hasRole = $adminDAO->hasRole($userId, $principalId, ROLE_CHANGE_PO_NUMBER);
               if($hasRole) { ?>
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                      <td colspan="2">&nbsp;</td> 
                      <td colspan="2" class="det1" style="text-align:left;">Change or Add PO / reference</td>
                      <td class="det2" style="text-align:center;"><INPUT TYPE="radio" name="ACTIONTYPE"  onclick="javascript: submit()" value= "AMENDREF" <?php echo $chgPoAllow; ?> ></td>
                      <td colspan="1" class="tooltip" style="text-align:left;"><span><IMG src= <?php echo HOST_KOSSERVER_PHPFOLDER_AS_USER . "images/info-icon-small.png";?>></span><span class="tooltiptext">Tip<br>Add/change PO Number</span></td>                  
                      <td colspan="2">&nbsp;</td> 
                   </tr>
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                      <td Colspan = "8">&nbsp;</td>
                   </tr>  
              <?php
              }
               $hasRole = $adminDAO->hasRole($userId, $principalId, ROLE_AMEND_GRV);
               if($hasRole) { ?>
                    <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                       <td colspan="2">&nbsp;</td> 
                       <td colspan="2" class="det1" style="text-align:left;">Change or Add GRV Number</td>
                       <td class="det2" style="text-align:center;"><INPUT TYPE="radio" name="ACTIONTYPE"  onclick="javascript: submit()" value= "AMENDGRV" <?php echo $chgGrvAllow; ?> ></td>
                       <td colspan="1" class="tooltip" style="text-align:left;"><span><IMG src= <?php echo HOST_KOSSERVER_PHPFOLDER_AS_USER . "images/info-icon-small.png";?>></span><span class="tooltiptext">Tip<br>Add/change GRV Number</span></td>                  
                       <td colspan="2">&nbsp;</td> 
                    </tr>
                    <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                       <td Colspan = "8">&nbsp;</td>
                    </tr>               
              <?php	
              } 
               $hasRole = $adminDAO->hasRole($userId, $principalId, ROLE_AMEND_GRV);
               if($hasRole) { ?>
                    <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                       <td colspan="2">&nbsp;</td> 
                       <td colspan="2" class="det1" style="text-align:left;">Change or Add Claim Number</td>
                       <td class="det2" style="text-align:center;"><INPUT TYPE="radio" name="ACTIONTYPE"  onclick="javascript: submit()" value= "AMENDCLM" <?php echo $chgClaimAllow; ?> ></td>
                       <td colspan="1" class="tooltip" style="text-align:left;"><span><IMG src= <?php echo HOST_KOSSERVER_PHPFOLDER_AS_USER . "images/info-icon-small.png";?>></span><span class="tooltiptext">Tip<br>Add/change PO Number</span></td>                  
                       <td colspan="2">&nbsp;</td> 
                   </tr>
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                       <td Colspan = "8">&nbsp;</td>
                   </tr>
               <?php	
               }
               $hasRole = $adminDAO->hasRole($userId, $principalId, ROLE_OFF_INVOICE_DISCOUNT);
               if($hasRole) { ?>
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                       <td colspan="2">&nbsp;</td> 
                       <td colspan="2" class="det1" style="text-align:left;">Off Invoice Discount</td>
                       <td class="det2" style="text-align:center;"><INPUT TYPE="radio" name="ACTIONTYPE"  onclick="javascript: submit()" value= "AMENDDIS" <?php echo $chgPoAllow; ?> ></td>
                       <td colspan="1" class="tooltip" style="text-align:left;"><span><IMG src= <?php echo HOST_KOSSERVER_PHPFOLDER_AS_USER . "images/info-icon-small.png";?>></span><span class="tooltiptext">Tip<br>Add/change PO Number</span></td>                  
                       <td colspan="2">&nbsp;</td> 
                  </tr>
                  <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                       <td Colspan = "8">&nbsp;</td>
                  </tr>
               <?php	
               } 
               $hasRole = $adminDAO->hasRole($userId, $principalId, ROLE_MANAGE_WAREHOUSE);
               if($hasRole) { ?>                 
                  <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                       <td colspan="2">&nbsp;</td> 
                       <td colspan="2" class="det1" style="text-align:left;">Manage Warehouse on Order</td>
                       <td class="det2" style="text-align:center;"><INPUT TYPE="radio" name="ACTIONTYPE"  onclick="javascript: submit()" value= "CHANGEWH" <?php echo $chgWhAllow; ?> ></td>
                       <td colspan="1" class="tooltip" style="text-align:left;"><span><IMG src= <?php echo HOST_KOSSERVER_PHPFOLDER_AS_USER . "images/info-icon-small.png";?>></span><span class="tooltiptext">Tip<br>Change Warehouse on Order<br>Update Store for Future Orders<br>Allowed Status Accepted</></span></td>                  
                       <td colspan="2">&nbsp;</td> 
                  </tr>
                  <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                       <td colspan = "8">&nbsp;</td>
                  </tr>
               <?php	
               }  
               $hasRole = $adminDAO->hasRole($userId, $principalId, ROLE_RESET_DELIVERY_POD_OK);
               if($hasRole) { ?>
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                       <td colspan="2">&nbsp;</td> 
                       <td colspan="2" class="det1" style="text-align:left;">Reset Delivery and POD OK</td>
                       <td class="det2" style="text-align:center;"><INPUT TYPE="radio" name="ACTIONTYPE"  onclick="javascript: submit()" value= "RESETPODOK" <?php echo $chgDebriefStatAllow; ?>></td>
                       <td colspan="1" class="tooltip" style="text-align:left;"><span><IMG src= <?php echo HOST_KOSSERVER_PHPFOLDER_AS_USER . "images/info-icon-small.png";?>></span><span class="tooltiptext">Tip<br>Add/change PO Number</span></td>                  
                       <td colspan="2">&nbsp;</td> 
                   </tr>
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                       <td Colspan = "8">&nbsp;</td>
                   </tr>
               <?php	
               }  
               $hasRole = $adminDAO->hasRole($userId, $principalId, ROLE_UN_CANCEL_ORDER);
               if($hasRole) { ?>
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                       <td colspan="2">&nbsp;</td> 
                       <td colspan="2" class="det1" style="text-align:left;">Un Cancel Order</td>
                       <td class="det2" style="text-align:center;"><INPUT TYPE="radio" name="ACTIONTYPE"  onclick="javascript: submit()" value= "UNCANCEL" <?php echo $unCancelAllow; ?>></td>
                       <td colspan="1" class="tooltip" style="text-align:left;"><span><IMG src= <?php echo HOST_KOSSERVER_PHPFOLDER_AS_USER . "images/info-icon-small.png";?>></span><span class="tooltiptext">Tip<br>Add/change PO Number</span></td>                  
                       <td colspan="2">&nbsp;</td> 
                   </tr>
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                       <td Colspan = "8">&nbsp;</td>
                   </tr>
               <?php	
               }  
               $hasRole = $adminDAO->hasRole($userId, $principalId, ROLE_UN_CANCEL_ORDER);
               if($hasRole) { ?>
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                       <td colspan="2">&nbsp;</td> 
                       <td colspan="2" class="det1" style="text-align:left;">Cancel this Order</td>
                       <td class="det2" style="text-align:center;"><INPUT TYPE="radio" name="ACTIONTYPE"  onclick="javascript: submit()" value= "CANCELORDER" <?php echo $CancelAllow; ?>></td>
                       <td colspan="1" class="tooltip" style="text-align:left;"><span><IMG src= <?php echo HOST_KOSSERVER_PHPFOLDER_AS_USER . "images/info-icon-small.png";?>></span><span class="tooltiptext">Tip<br>Add/change PO Number</span></td>                  
                       <td colspan="2">&nbsp;</td> 
                   </tr>
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                       <td Colspan = "8">&nbsp;</td>
                   </tr>
               <?php	
               }  
               $hasRole = $adminDAO->hasRole($userId, $principalId, ROLE_AMEND_ORDER);
               if($hasRole) { ?>
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                       <td colspan="2">&nbsp;</td> 
                       <td colspan="2" class="det1" style="text-align:left;">Amend Order</td>
                       <td class="det2" style="text-align:center;"><INPUT TYPE="radio" name="ACTIONTYPE"  onclick="javascript: submit()" value= "AMENDORD" <?php echo $amendAllow; ?>></td>
                       <td colspan="1" class="tooltip" style="text-align:left;"><span><IMG src= <?php echo HOST_KOSSERVER_PHPFOLDER_AS_USER . "images/info-icon-small.png";?>></span><span class="tooltiptext">Tip<br>Add/change PO Number</span></td>                  
                       <td colspan="2">&nbsp;</td> 
                       </tr>
                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                       <td Colspan = "8">&nbsp;</td>
                   </tr>
               <?php	
               }
               $hasRole = $adminDAO->hasRole($userId, $principalId, ROLE_ACCEPTED_STATUS);
               if($hasRole) { ?>
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                       <td colspan="2">&nbsp;</td> 
                       <td colspan="2" class="det1" style="text-align:left;">Change Accepted Status</td>
                       <td class="det2" style="text-align:center;"><INPUT TYPE="radio" name="ACTIONTYPE"  onclick="javascript: submit()" value= "ACCSTAT" <?php echo $amendAccStat; ?>></td>
                       <td colspan="1" class="tooltip" style="text-align:left;"><span><IMG src= <?php echo HOST_KOSSERVER_PHPFOLDER_AS_USER . "images/info-icon-small.png";?>></span><span class="tooltiptext">Tip<br>Manage Accepted / UnAccepted</span></td>                  
                       <td colspan="2">&nbsp;</td> 
                       </tr>
                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                       <td Colspan = "8">&nbsp;</td>
                   </tr>
               <?php	
               }
               $hasRole = $adminDAO->hasRole($userId, $principalId, ROLE_RESET_AN_INVOICE);
               if($hasRole) { ?>
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                       <td colspan="2">&nbsp;</td> 
                       <td colspan="2" class="det1" style="text-align:left;">Reset Invoiced Status</td>
                       <td class="det2" style="text-align:center;"><INPUT TYPE="radio" name="ACTIONTYPE"  onclick="javascript: submit()" value= "RESETINV" <?php echo $chgReSetInvoiceAllow; ?>></td>
                       <td colspan="1" class="tooltip" style="text-align:left;"><span><IMG src= <?php echo HOST_KOSSERVER_PHPFOLDER_AS_USER . "images/info-icon-small.png";?>></span><span class="tooltiptext">Tip<br>Reset Status to Invoiced<br>OTP Required</span></td>                  
                       <td colspan="2">&nbsp;</td> 
                   </tr>
               <?php	
               } ?>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                   <td Colspan = "8">&nbsp;</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan = "8">&nbsp;</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan = "8">&nbsp;</td>
               </tr>             
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td colspan = "8" style="border:none; text-align:center;"><INPUT TYPE="submit" class="submit" name="CANFORM"   value= "Close Window"></td>
               </tr>          
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan = "8">&nbsp;</td>
               </tr>  
 			    	</table>
		   </form>
    </center> 
    <?php 
    }
// ********************************************************************************************************************************************************
// Change Warehouse Screen
// ********************************************************************************************************************************************************

      public function changeWareHouse($docUid, $userUId) {

              $ManageOrdersDAO = new ManageOrdersDAO($this->dbConn);
             	$mfDDU = $ManageOrdersDAO->getDocumentToUpdate($docUid);

             	$mfPR  = $ManageOrdersDAO->getuserWarehouses($mfDDU[0]['Principal'], $userUId, $mfDDU[0]['depot_uid']); ?>
                    <center>
                       <FORM name='Manage Warehouse' method=post action=''>
                          <table width="720"; style="border:none">
                          	<tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                 <td style="width:2%;  border:none;">&nbsp</td>
                                 <td style="width:15%; border:none;">&nbsp</td>
                                 <td style="width:35%; border:none;">&nbsp</td>
                                 <td style="width:15%; border:none;">&nbsp</td>
                                 <td style="width:45%; border:none;">&nbsp</td>
                                 <td style="width:2%;  border:none;">&nbsp</td>
                            </tr>
                            <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                 <td class="head1" colspan="6"; style="text-align:center;">Manage Warehouse Details</td>
                            </tr>
                            <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            	   <td colspan="6">&nbsp</td>
                            </tr>
                            <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                            	    <td Colspan="1">&nbsp;</td>                               
                                  <td class="det1" colspan="1"; style="text-align:left;">Customer</td>
                                  <td class="det2" colspan="1"; style="text-align:left;"><?php echo trim($mfDDU[0]['deliver_name'] ); ?></td>
                                  <td class="det1" colspan="1"; style="text-align:left;">Document&nbsp;No</td>
                                  <td class="det2" colspan="1"; style="text-align:right;"><?php echo ltrim($mfDDU[0]['document_number'],'0' ); ?></td>
                                  <td Colspan="1"><input type="hidden" id="DOCUID" name="DOCUID" value='<?php echo $docUid; ?>'></td>
                             </tr>
                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>                               
                                  <td colspan="6"; style="text-align:left"><input type="hidden" id="OLDWH"  name="OLDWH"  value='<?php echo $mfDDU[0]['depot_uid']; ?>'>
                                                                           <input type="hidden" id="USERID" name="USERID" value='<?php echo $userUId; ?>'></td>
                             </tr>
                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                             	    <td Colspan="1">&nbsp;</td>                                
                                  <td class="det1" colspan="1"; style="text-align:left;">Existing Warehouse:</td>
                                  <td class="det2" colspan="1"; style="text-align:left;"><?php echo trim($mfDDU[0]['Depot'] ); ?></td>
                                  <td class="det1" colspan="1"; style="text-align:left;">New Warehouse:</td>
                                  <td>
                                      <select name="Warehouse" id="Warehouse" size="1">
                                             <option value="Select New Warehouse">Select New Warehouse</option>
                                             <?php foreach($mfPR as $row) { ?>
                                                  <option value="<?php echo trim($row['uid']); ?>"><?php echo trim($row['name']); ?></option>
                                             <?php } ?>
                                            </select>
                                  </td>
                                  <td Colspan="1"><input type="hidden" id="psmUid" name="psmUid" value='<?php echo $mfDDU[0]['StoreUid']; ?>'></td>
                             </tr>
                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>                               
                                  <td colspan="6"; style="text-align:left">&nbsp</td>
                             </tr>                                  
                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";> 
                             	    <td colspan="1">&nbsp;</td>                               
                                  <td class="det1" colspan="1"; style="text-align:right">Update Store Master:</td>
                                  <td colspan="1"; style="text-align:left;"><?php $lableArr = array('No','Yes');
          		                                                $valueArr = array('2','1');
          		                                                BasicSelectElement::buildGenericDD('STSTATUS', $lableArr,$valueArr, $postSTSTATUS, "N", "N", null, null, null);?>
                                  </td>
                                  <td colspan="3"; style="text-align:left">&nbsp</td>
                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>                               
                                  <td colspan="6"; style="text-align:left">&nbsp</td>
                             </tr>
                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";> 
                                  <td colspan="6"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="UPDWH" value= "Update Warehouse Details">
                                                             <INPUT TYPE="submit" class="submit" name="CANFORM"   value= "Cancel"></td>
                             </tr>
                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>                               
                                  <td colspan="6"; style="text-align:left">&nbsp</td>
                             </tr>
                          </table>  
                       </FORM>           
                    	
                    </center>	
                  	
                  <?php 
      }    
// ********************************************************************************************************************************************************
// Change Uncancel Order
// ********************************************************************************************************************************************************

      public function unCancelAnOrder($docUid, $userUId) {

              $ManageOrdersDAO = new ManageOrdersDAO($this->dbConn);
             	$mfDDU = $ManageOrdersDAO->getDocumentToUpdate($docUid);

             	$mfPR  = $ManageOrdersDAO->getuserWarehouses($mfDDU[0]['Principal'], $userUId, $mfDDU[0]['depot_uid']); ?>
                    <center>
                       <FORM name='Manage Warehouse' method=post action=''>
                          <table width="720"; style="border:none">
                          	<tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                 <td style="width:2%;  border:none;">&nbsp</td>
                                 <td style="width:15%; border:none;">&nbsp</td>
                                 <td style="width:35%; border:none;">&nbsp</td>
                                 <td style="width:15%; border:none;">&nbsp</td>
                                 <td style="width:45%; border:none;">&nbsp</td>
                                 <td style="width:2%;  border:none;">&nbsp</td>
                            </tr>
                            <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                 <td class="head1" colspan="6"; style="text-align:center;">Uncancel a Cancelled Order</td>
                            </tr>
                            <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            	   <td colspan="6">&nbsp</td>
                            </tr>
                            <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                            	    <td Colspan="1">&nbsp;</td>                               
                                  <td class="det1" colspan="1"; style="text-align:left;">Customer</td>
                                  <td class="det2" colspan="1"; style="text-align:left;"><?php echo trim($mfDDU[0]['deliver_name'] ); ?></td>
                                  <td class="det1" colspan="1"; style="text-align:left;">Document&nbsp;No</td>
                                  <td class="det2" colspan="1"; style="text-align:right;"><?php echo ltrim($mfDDU[0]['document_number'],'0' ); ?></td>
                                  <td Colspan="1"><input type="hidden" id="DOCUID" name="DOCUID" value='<?php echo $docUid; ?>'></td>
                             </tr>
                            <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            	   <td colspan="6">&nbsp</td>
                            </tr>
                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>                               
                                  <td colspan="6"; style="text-align:left"><input type="hidden" id="OLDSTAT"  name="OLDSTAT"  value='<?php echo trim($mfDDU[0]['document_status_uid']); ?>'>
                                                                           <input type="hidden" id="USERID"   name="USERID" value='<?php echo $userUId; ?>'></td>
                             </tr>
                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                             	    <td Colspan="1">&nbsp;</td>                                
                                  <td class="det1" colspan="1"; style="text-align:left;">Warehouse:</td>
                                  <td class="det2" colspan="1"; style="text-align:left;"><?php echo trim($mfDDU[0]['Depot'] ); ?></td>
                                  <td class="det1" colspan="1"; style="text-align:left;">Status</td>
                                  <td class="det2" colspan="1"; style="text-align:left;"><?php echo trim($mfDDU[0]['Status'] ); ?></td>
                                  <td Colspan="1"><input type="hidden" id="psmUid" name="DOCNO" value='<?php echo $mfDDU[0]['document_number']; ?>'></td>
                             </tr>
                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>                               
                                  <td colspan="6"; style="text-align:left">&nbsp</td>
                             </tr>                                  
                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>                               
                                  <td colspan="6"; style="text-align:left">&nbsp</td>
                             </tr>
                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";> 
                                  <td colspan="6"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="UNCANCELORDER" value= "Uncancel Order">
                                                             <INPUT TYPE="submit" class="submit" name="CANFORM"   value= "Cancel"></td>
                             </tr>
                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>                               
                                  <td colspan="6"; style="text-align:left">&nbsp</td>
                             </tr>
                          </table>  
                       </FORM>           
                    	
                    </center>	
                  	
                  <?php 
      }    

// ********************************************************************************************************************************************************
// Change Cancel Order
// ********************************************************************************************************************************************************

      public function cancelAnOrder($docUid, $userUId) {

              $ManageOrdersDAO = new ManageOrdersDAO($this->dbConn);
             	$mfDDU = $ManageOrdersDAO->getDocumentToUpdate($docUid);

             	$mfPR  = $ManageOrdersDAO->getuserWarehouses($mfDDU[0]['Principal'], $userUId, $mfDDU[0]['depot_uid']); ?>
                    <center>
                       <FORM name='Manage Warehouse' method=post action=''>
                          <table width="720"; style="border:none">
                          	<tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                 <td style="width:2%;  border:none;">&nbsp</td>
                                 <td style="width:15%; border:none;">&nbsp</td>
                                 <td style="width:35%; border:none;">&nbsp</td>
                                 <td style="width:15%; border:none;">&nbsp</td>
                                 <td style="width:45%; border:none;">&nbsp</td>
                                 <td style="width:2%;  border:none;">&nbsp</td>
                            </tr>
                            <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                 <td class="head1" colspan="6"; style="text-align:center;">Cancel An Accepted Order</td>
                            </tr>
                            <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            	   <td colspan="6">&nbsp</td>
                            </tr>
                            <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                            	    <td Colspan="1">&nbsp;</td>                               
                                  <td class="det1" colspan="1"; style="text-align:left;">Customer</td>
                                  <td class="det2" colspan="1"; style="text-align:left;"><?php echo trim($mfDDU[0]['deliver_name'] ); ?></td>
                                  <td class="det1" colspan="1"; style="text-align:left;">Document&nbsp;No</td>
                                  <td class="det2" colspan="1"; style="text-align:right;"><?php echo ltrim($mfDDU[0]['document_number'],'0' ); ?></td>
                                  <td Colspan="1"><input type="hidden" id="DOCUID" name="DOCUID" value='<?php echo $docUid; ?>'></td>
                             </tr>
                            <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            	   <td colspan="6">&nbsp</td>
                            </tr>
                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>                               
                                  <td colspan="6"; style="text-align:left"><input type="hidden" id="OLDSTAT"  name="OLDSTAT"  value='<?php echo trim($mfDDU[0]['document_status_uid']); ?>'>
                                                                           <input type="hidden" id="USERID"   name="USERID" value='<?php echo $userUId; ?>'></td>
                             </tr>
                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                             	    <td Colspan="1">&nbsp;</td>                                
                                  <td class="det1" colspan="1"; style="text-align:left;">Warehouse:</td>
                                  <td class="det2" colspan="1"; style="text-align:left;"><?php echo trim($mfDDU[0]['Depot'] ); ?></td>
                                  <td class="det1" colspan="1"; style="text-align:left;">Status</td>
                                  <td class="det2" colspan="1"; style="text-align:left;"><?php echo trim($mfDDU[0]['Status'] ); ?></td>
                                  <td Colspan="1"><input type="hidden" id="psmUid" name="DOCNO" value='<?php echo $mfDDU[0]['document_number']; ?>'></td>
                             </tr>
                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>                               
                                  <td colspan="6"; style="text-align:left">&nbsp</td>
                             </tr>                                  
                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>                               
                                  <td colspan="6"; style="text-align:left">&nbsp</td>
                             </tr>
                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";> 
                                  <td colspan="6"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="CANCELORDER" value= "Cancel This Order">
                                                             <INPUT TYPE="submit" class="submit" name="CANFORM"   value= "Cancel"></td>
                             </tr>
                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>                               
                                  <td colspan="6"; style="text-align:left">&nbsp</td>
                             </tr>
                          </table>  
                       </FORM>           
                    	
                    </center>	
                  	
                  <?php 
      }
// ********************************************************************************************************************************************************
// Reset POD Status
// ********************************************************************************************************************************************************

      public function resetDebriefStatus($docUid, $userUId) {

              $ManageOrdersDAO = new ManageOrdersDAO($this->dbConn);
             	$mfDDU = $ManageOrdersDAO->getDocumentToUpdate($docUid);

             	$mfPR  = $ManageOrdersDAO->getuserWarehouses($mfDDU[0]['Principal'], $userUId, $mfDDU[0]['depot_uid']); ?>
                    <center>
                       <FORM name='Manage Warehouse' method=post action=''>
                          <table width="720"; style="border:none">
                          	<tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                 <td style="width:2%;  border:none;">&nbsp</td>
                                 <td style="width:15%; border:none;">&nbsp</td>
                                 <td style="width:35%; border:none;">&nbsp</td>
                                 <td style="width:15%; border:none;">&nbsp</td>
                                 <td style="width:45%; border:none;">&nbsp</td>
                                 <td style="width:2%;  border:none;">&nbsp</td>
                            </tr>
                            <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                 <td class="head1" colspan="6"; style="text-align:center;">Reset POD OK Status</td>
                            </tr>
                            <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            	   <td colspan="6">&nbsp</td>
                            </tr>
                            <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                            	    <td Colspan="1">&nbsp;</td>                               
                                  <td class="det1" colspan="1"; style="text-align:left;">Customer</td>
                                  <td class="det2" colspan="1"; style="text-align:left;"><?php echo trim($mfDDU[0]['deliver_name'] ); ?></td>
                                  <td class="det1" colspan="1"; style="text-align:left;">Document&nbsp;No</td>
                                  <td class="det2" colspan="1"; style="text-align:right;"><?php echo ltrim($mfDDU[0]['document_number'],'0' ); ?></td>
                                  <td Colspan="1"><input type="hidden" id="DOCUID" name="DOCUID" value='<?php echo $docUid; ?>'></td>
                             </tr>
                            <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            	   <td colspan="6">&nbsp</td>
                            </tr>     
                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                             	    <td Colspan="1">&nbsp;</td>                                
                                  <td class="det1" colspan="1"; style="text-align:left;">Warehouse:</td>
                                  <td class="det2" colspan="1"; style="text-align:left;"><?php echo trim($mfDDU[0]['Depot'] ); ?></td>
                                  <td class="det1" colspan="1"; style="text-align:left;">Current Status</td>
                                  <td class="det2" colspan="1"; style="text-align:left;"><?php echo trim($mfDDU[0]['Status'] ); ?></td>
                                  <td Colspan="1">&nbsp;</td>
                             </tr>     
                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>                               
                                  <td colspan="6"; style="text-align:left">&nbsp</td>
                             </tr>                                  
                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>                               
                                  <td colspan="6"; style="text-align:left">&nbsp</td>
                             </tr>
                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";> 
                                  <td colspan="6"; style="text-align:center;"><INPUT TYPE="submit" class="submit " name="RESETPODOK" value= "Reset Status to 'Invoiced'">
                                                             <INPUT TYPE="submit" class="submit" name="CANFORM"   value= "Cancel"></td>
                             </tr>
                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>                               
                                  <td colspan="6"; style="text-align:left">&nbsp</td>
                             </tr>
                          </table>  
                       </FORM>           
                    	
                    </center>	
                  	
                  <?php 
      }
// ********************************************************************************************************************************************************
// Reset Invoiced Document
// ********************************************************************************************************************************************************

      public function resetInvoicedDocument($postINVOICE) {

             $ManageOrdersDAO = new ManageOrdersDAO($this->dbConn);
             $aCuDet = $ManageOrdersDAO->getInvoiceDetailsToAmendNew($postINVOICE);
             
             if($aCuDet[0]['verified_for_dispatch'] == 'Y') {
                  $a = 'FAIL';
                  return $a;             	
             } ?>
                    
             <center>
                  <FORM name='Select Invoice' method=post action=''>
                     <table width="820"; style="border:none">
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            <td class=head1 Colspan="6"; style="text-align:center";>Document to Reset</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            <td Colspan="6">&nbsp</td>
                        </tr>	        	
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                             <td width="16%"; style="border:none">&nbsp</td>
                             <td width="16%"; style="border:none">&nbsp</td>
                             <td width="20%"; style="border:none">&nbsp</td>
                             <td width="16%"; style="border:none">&nbsp</td>
                             <td width="16%"; style="border:none">&nbsp</td>
                             <td width="16%"; style="border:none">&nbsp</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                             <td class='det1' Colspan="1" style="padding-left: 20px;" NOWRAP>Customer Name </td>
                             <td class='det2' Colspan="3" style="text-align:left ";><?php echo $aCuDet[0]['deliver_name']; ?> </td>
                             <td class='det1' colspan="1"; style="text-align:right">&nbsp;Document&nbsp;No</td>
                             <td class='det2' Colspan="1" style="text-align:right; padding-right: 20px;"><?php echo ltrim($aCuDet[0]['document_number'],'0'); ?> </td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                             <td Colspan="6" style="border:none;"><input type="hidden" name="DOCUID" value=<?php echo $aCuDet[0]['dUid']; ?>> 
                             	                                    <input type="hidden" name="DOCNUM" value=<?php echo ltrim($aCuDet[0]['document_number'],'0'); ?>></td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                             <td class='det1' Colspan="1" style="padding-left: 20px;" NOWRAP>PO Number </td>
                             <td class='det2' Colspan="3" style="text-align:left ";><?php echo $aCuDet[0]['customer_order_number']; ?> </td>
                             <td class='det1' colspan="1"; style="text-align:right">&nbsp;Invoice Date</td>
                             <td class='det2' Colspan="1" style="text-align:right; padding-right: 20px;"><?php echo $aCuDet[0]['invoice_date']; ?> </td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                             <td Colspan="6" style="border:none;">&nbsp</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                              <td class='det1' Colspan="1" style="padding-left: 20px;" NOWRAP>Product Code</td>
                              <td class='det1' Colspan="3" style="text-align:left ";>Product Description</td>
                              <td class='det1' colspan="1" style="text-align:center";>Ordered</td>
                              <td class='det1' colspan="1" style="text-align:center";>Invoiced</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                              <td Colspan="6" style="border:none;">&nbsp</td>
                        </tr>
                        <?php
                        foreach ($aCuDet as $row) { ?>
                            <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                 <td class='det2' Colspan="1" style="padding-left: 20px;" NOWRAP><?php echo $row['product_code']; ?></td>
                                 <td class='det2' Colspan="3" style="text-align:left "; NOWRAP><?php echo $row['product_description']; ?></td>
                                 <td class='det2' colspan="1" style="text-align:center";><?php echo $row['ordered_qty']; ?></td>
                                 <td class='det2' colspan="1" style="text-align:center";><?php echo $row['document_qty']; ?></td>    
                            </tr> 
                            <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                 <td Colspan="6" style="border:none;">&nbsp</td>
                                </tr>
                        <?php
                        } ?>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                              <td Colspan="6" style="border:none;">&nbsp</td>
                        </tr>                       
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                              <td Colspan="6" style="border:none;">&nbsp</td>
                        </tr>                      
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                              <td colspan="6"; style="border:none; text-align:center;"><INPUT TYPE="submit" class="submit" name="PROCESSCHANGE" value= "Reset Invoiced Status">
                                                                                       <INPUT TYPE="submit" class="submit" name="BACKF"   value= "Back">
                       	                                                               <INPUT TYPE="submit" class="submit" name="CANFORN"   value= "Cancel"></td>
                        </tr>          
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                             <td Colspan="6">&nbsp</td>
                        </tr>  
                     </table>
                  </FORM>
                </center> 
      <?php          
      }

// ********************************************************************************************************************************************************
// Change PO / GRV
// ********************************************************************************************************************************************************

      public function addChangePOnumber($docUid, $userUId, $type) {
      	
      	      if($type == 'G') {
      	      	  $fHeading  = "Add Change GRV Number";
      	      	  $selAction = 'MANAGEGRV'; 
      	      	  $upAction  = 'GRV Number';   	      	
      	      } elseif($type == 'C') {
      	      	  $fHeading  = "Add Change Claim Number";
      	      	  $selAction = 'MANAGECLM';
      	      	  $upAction  = 'Claim Number';      
      	      } elseif($type == 'D') {
      	      	  $fHeading  = "Off Invoice Discount";
      	      	  $selAction = 'MANAGEOID';
      	      	  $upAction  = 'Invoice Discount';
      	      } else {
      	      	  $fHeading = "Add Change PO Number";
      	      	  $selAction = 'MANAGEPO';
      	      	  $upAction  = 'PO Number'; 
      	      }
              $ManageOrdersDAO = new ManageOrdersDAO($this->dbConn);
             	$mfDDU = $ManageOrdersDAO->getDocumentToUpdate($docUid);

             	$mfPR  = $ManageOrdersDAO->getuserWarehouses($mfDDU[0]['Principal'], $userUId, $mfDDU[0]['depot_uid']); ?>
                    <center>
                       <FORM name='Add Change PO Number' method=post action=''>
                          <table width="720"; style="border:none">
                          	<tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                 <td style="width:2%;  border:none;">&nbsp</td>
                                 <td style="width:15%; border:none;">&nbsp</td>
                                 <td style="width:35%; border:none;">&nbsp</td>
                                 <td style="width:15%; border:none;">&nbsp</td>
                                 <td style="width:43%; border:none;">&nbsp</td>
                                 <td style="width:2%;  border:none;">&nbsp</td>
                                 <td style="width:2%;  border:none;">&nbsp</td>
                            </tr>
                            <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                 <td class="head1" colspan="7"; style="text-align:center;"><?php echo $fHeading ?></td>
                            </tr>
                            <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            	   <td colspan="7">&nbsp</td>
                            </tr>
                            <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                            	    <td Colspan="1">&nbsp;</td>                               
                                  <td class="det1" colspan="1"; style="text-align:left;">Customer</td>
                                  <td class="det2" colspan="1"; style="text-align:left;"><?php echo trim($mfDDU[0]['deliver_name'] ); ?></td>
                                  <td class="det1" colspan="1"; style="text-align:left;">Document&nbsp;No</td>
                                  <td class="det2" colspan="1"; style="text-align:right;"><?php echo ltrim($mfDDU[0]['document_number'],'0' ); ?></td>
                                  <td Colspan="2"><input type="hidden" id="DOCUID" name="DOCUID" value='<?php echo $docUid; ?>'></td>
                             </tr>
                            <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            	   <td colspan="7">&nbsp</td>
                            </tr>     
                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                             	    <td Colspan="1">&nbsp;</td>                                
                                  <td class="det1" colspan="1"; style="text-align:left;">Warehouse:</td>
                                  <td class="det2" colspan="1"; style="text-align:left;"><?php echo trim($mfDDU[0]['Depot'] ); ?></td>
                                  <td class="det1" colspan="1"; style="text-align:left;">Current Status</td>
                                  <td class="det2" colspan="1"; style="text-align:left;"><?php echo trim($mfDDU[0]['Status'] ); ?></td>
                                  <td Colspan="2"><input type="hidden" id="CSTATUS" name="CSTATUS" value='<?php echo trim($mfDDU[0]['document_status_uid'] ); ?>'>
                                  	              <input type="hidden" id="CSTATUS" name="OLDPON" value='<?php echo trim($mfDDU[0]['customer_order_number'] ); ?>'></td>
                             </tr>     
                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                                  <td colspan="7"; style="text-align:left">&nbsp</td>
                             </tr>
                             <?php 
                             if($type == 'G') { ?>
                                  <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                                     <td colspan="1"><input type="hidden" id="ATYPE" name="ATYPE" value='<?php echo $type; ?>'></td>
                                     <td class="det1" colspan="2";>Store GRV&nbsp;No</td>
                                     <td colspan="3";style="text-align:center"><input type="text" name="WAYBILL" autofocus value="<?php echo $mfDDU[0]['grv_number'];?>"><br></td>
                                     <td Colspan="2">&nbsp;</td>
                                  </tr>
                             <?php
                             } elseif($type == 'C') { ?>
                                  <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                                     <td colspan="1"><input type="hidden" id="ATYPE" name="ATYPE" value='<?php echo $type; ?>'></td>
                                     <td class="det1" colspan="2";>Store Claim&nbsp;No</td>
                                     <td colspan="3";style="text-align:center"><input type="text" name="WAYBILL" autofocus value="<?php echo $mfDDU[0]['grv_number'];?>"><br></td>
                                     <td Colspan="2">&nbsp;</td>
                                  </tr>                             
                             <?php
                             } elseif($type == 'D') { ?>
                                  <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                                     <td colspan="1"><input type="hidden" id="ATYPE" name="ATYPE" value='<?php echo $type; ?>'></td>
                                     <td class="det1" colspan="2";>Discount&nbsp;Type</td>
                                     <td olspan="3"; style="text-align:left";><select name="DISCOUNTTYPE" id="DISCOUNTTYPE">
                                                                                      <option value="P">Percentage</option>
                                                                                      <option value="A">Amount Off</option>
                                                                              </select></td>
                                     <td Colspan="2">&nbsp;</td>
                                  </tr>   
                                  <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>                               
                                     <td colspan="7"; style="text-align:left">&nbsp</td>
                                  </tr>
                                  <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                                     <td colspan="1">&nbsp;</td>
                                     <td class="det1" colspan="2";>Discount&nbsp;Value</td>
                                     <td colspan="3";style="text-align:center"><input type="text" name="DISVAL" autofocus value="<?php echo $mfDDU[0]['dh.off_invoice_discount, '];?>"><br></td>
                                     <td Colspan="2">&nbsp;</td>
                                  </tr>   
                                  <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>                               
                                     <td colspan="7"; style="text-align:left">&nbsp</td>
                                  </tr>
                             <?php
                             } else { ?>
                                 <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                                     <td colspan="1"><input type="hidden" id="ATYPE" name="ATYPE" value='<?php echo $type; ?>'></td>
                                     <td class="det1" colspan="2";>Customer Ref&nbsp;No</td>
                                     <td colspan="3";style="text-align:center"><input type="text" name="WAYBILL" autofocus value="<?php echo $mfDDU[0]['customer_order_number'];?>"><br></td>
                                     <td Colspan="2">&nbsp;</td>
                                 </tr>
                             <?php                             	
                             } ?>
                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>                               
                                  <td colspan="7"; style="text-align:left">&nbsp</td>
                             </tr>
                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";> 
                                  <td colspan="7"; style="text-align:center;"><INPUT TYPE="submit" class="submit " name="<?php echo $selAction ;?>" value= "Update <?php echo $upAction ;?>">
                                                             <INPUT TYPE="submit" class="submit" name="CANFORM"   value= "Cancel"></td>
                             </tr>
                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>                               
                                  <td colspan="7"; style="text-align:left">&nbsp</td>
                             </tr>
                          </table>  
                       </FORM>           
                    	
                    </center>	
                  	
                  <?php 
      }
// ********************************************************************************************************************************************************
// Reset Accepted Status
// ********************************************************************************************************************************************************

      public function manageAcceptedStatus($docUid, $userUId) {

              $ManageOrdersDAO = new ManageOrdersDAO($this->dbConn);
             	$mfDDU = $ManageOrdersDAO->getDocumentToUpdate($docUid) ?>
             	
                    <center>
                       <FORM name='Manage Warehouse' method=post action=''>
                          <table width="720"; style="border:none">
                          	<tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                 <td style="width:2%;  border:none;">&nbsp</td>
                                 <td style="width:15%; border:none;">&nbsp</td>
                                 <td style="width:35%; border:none;">&nbsp</td>
                                 <td style="width:15%; border:none;">&nbsp</td>
                                 <td style="width:45%; border:none;">&nbsp</td>
                                 <td style="width:2%;  border:none;">&nbsp</td>
                            </tr>
                            <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                 <td class="head1" colspan="6"; style="text-align:center;">Manage Accepted / UnAccepted</td>
                            </tr>
                            <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            	   <td colspan="6">&nbsp</td>
                            </tr>
                            <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                            	    <td Colspan="1">&nbsp;</td>                               
                                  <td class="det1" colspan="1"; style="text-align:left;">Customer</td>
                                  <td class="det2" colspan="1"; style="text-align:left;"><?php echo trim($mfDDU[0]['deliver_name'] ); ?></td>
                                  <td class="det1" colspan="1"; style="text-align:left;">Document&nbsp;No</td>
                                  <td class="det2" colspan="1"; style="text-align:right;"><?php echo ltrim($mfDDU[0]['document_number'],'0' ); ?></td>
                                  <td Colspan="1"><input type="hidden" id="DOCUID" name="DOCUID" value='<?php echo $docUid; ?>'></td>
                             </tr>
                            <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            	   <td colspan="6">&nbsp</td>
                            </tr>     
                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                             	    <td Colspan="1">&nbsp;</td>                                
                                  <td class="det1" colspan="1"; style="text-align:left;">Warehouse:</td>
                                  <td class="det2" colspan="1"; style="text-align:left;"><?php echo trim($mfDDU[0]['Depot'] ); ?></td>
                                  <td class="det1" colspan="1"; style="text-align:left;">Current Status</td>
                                  <td class="det2" colspan="1"; style="text-align:left;"><?php echo trim($mfDDU[0]['Status'] ); ?></td>
                                  <td Colspan="1"><input type="hidden" id="CURSTAT" name="CURSTAT" value='<?php echo trim($mfDDU[0]['Status']); ?>'></td>
                             </tr>     
                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>                               
                                  <td colspan="6"; style="text-align:left">&nbsp</td>
                             </tr>                                  
                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>                               
                                  <td colspan="6"; style="text-align:left">&nbsp</td>
                             </tr>
                             <?php 
                             
                             echo trim($mfDDU[0]['Status']) . "<br>";
                             if(trim($mfDDU[0]['Status']) == 'Unaccepted') { ?>
                                  <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";> 
                                       <td colspan="6"; style="text-align:center;"><INPUT TYPE="submit" class="submit " name="RESETACCST" value= "Change to 'Accepted' Status">
                                                             <INPUT TYPE="submit" class="submit" name="CANFORM"   value= "Cancel"></td>
                                  </tr>
                             <?php                             	
                             } else { ?>
                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";> 
                                  <td colspan="6"; style="text-align:center;"><INPUT TYPE="submit" class="submit " name="RESETACCST" value= "Change to 'UnAccepted' Status">
                                                             <INPUT TYPE="submit" class="submit" name="CANFORM"   value= "Cancel"></td>
                             </tr>
                             <?php                             	
                             } ?>
                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>                               
                                  <td colspan="6"; style="text-align:left">&nbsp</td>
                             </tr>
                          </table>  
                       </FORM>           
                    	
                    </center>	
                  	
                  <?php 
      }
// ********************************************************************************************************************************************************

}
?>