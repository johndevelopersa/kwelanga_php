<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER."libs/GUICommonUtils.php");
    include_once($ROOT.$PHPFOLDER."libs/CommonUtils.php");
    include_once($ROOT.$PHPFOLDER."DAO/AdministrationDAO.php");
    include_once($ROOT.$PHPFOLDER."elements/datePickerElement.php");
    include_once($ROOT.$PHPFOLDER.'elements/basicSelectElement.php');	    		    
class processDebriefWithUomScreens {
	
      function __construct() {

         global $dbConn;
         $this->dbConn = $dbConn;
         
         $this->adminDAO = new AdministrationDAO($dbConn);
        
      }	
// ********************************************************************************************************************************	
  public function getDocumentNumber() {
  	
  	  // Check RD Roll
  	  
  	  $hasReDelRole = TRUE // $adminDAO->hasRole(mysqli_real_escape_string($this->dbConn->connection, $userUId), mysqli_real_escape_string($this->dbConn->connection, $principalId), ROLE_SIGNITURE);
  	
  	  ?>
       <center>
       <FORM name='Select Invoice' method=post action=''>
            <table width="720"; style="border:none">
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                   <td class=head1 Colspan="5" style="text-align:center;";>Debrief a Document</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                   <td Colspan="5">&nbsp</td>
               </tr>     	
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td width="38%"; style="border:none">&nbsp</td>
                 <td width="20%"; style="border:none">&nbsp</td>
                 <td width="20%"; style="border:none">&nbsp</td>
                 <td width="20%"; style="border:none">&nbsp</td>
                 <td width="2%" ; style="border:none">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                 <td class="det1" style="text-align:left";>Enter / Scan Document Number</td>
                 <td colspan="4"; style="text-align:left">
                 	     <div style="position:relative;">
                           <INPUT type="TEXT" size="15" name="DOCUMENTNO" id="DOCUMENTNO" class="scan-input" placeholder="scan or input" autofocus />
                           <div class="icon-size-32 icon-scan" style="position:absolute;top:4px;left:10px;" ></div>
                       </div>
                 </td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="5">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>"/>
                 <td colspan="5"; style="text-align:center;" ><input TYPE="submit" class="submit" name="DELFULL"    value= "Full Delivery"/>&nbsp;&nbsp;&nbsp;&nbsp;
                 	                                           <input TYPE="submit" class="submit"  name="DELPARTIAL" value= "Partial Delivery"/>&nbsp;&nbsp;&nbsp;&nbsp;
                 	                                           <?php If($hasReDelRole) { ?>
                 	                                           <input TYPE="submit" class="submit"  name="REDELIVERY" value= "Return For ReDelivery"/>&nbsp;&nbsp;&nbsp;&nbsp;
                 	                                           <?php
                 	                                           } ?>
                                                             <input TYPE="submit" class="submit"  name="BACK"       value= "Back"/></td>
               </tr>          
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>"/>
                 <td Colspan="5">&nbsp</td>
               </tr>  
 			    	</table>
		   </form>
    </center>  	
    <?php     
  }

// ********************************************************************************************************************************	
  public function showSelectedDocument($jsonDocumentDetail) {
  	
  	  $DocumentDetailArr = json_decode($jsonDocumentDetail, true);
  	  
  	  $postDELDATE = (isset($_POST["PAYDATE"])) ? htmlspecialchars($_POST["PAYDATE"]) :  CommonUtils::getUserDate();
  	  	  
  	  // echo "<pre>";
  	  // print_r($DocumentDetailArr);
  	  ?>
       <center>
       <FORM name='Process Full Delivery' method=post action=''>
            <table width="900"; style="border:none">
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                   <td class=head1 Colspan="6" style="text-align:center;";>Full Delivery</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td class="det1" style="border:none; width: 5%;">&nbsp</td>
                 <td class="det1" style="border:none; width: 20%;">&nbsp</td>
                 <td class="det1" style="border:none; width: 30%;">&nbsp</td>
                 <td class="det1" style="border:none; width: 20%;">&nbsp</td>
                 <td class="det1" style="border:none; width: 20%;">&nbsp</td>
                 <td class="det1" style="border:none; width: 5%;">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                    <td Colspan="1">&nbsp</td>
                    <td class="det1" style="text-align:left";  >Principal</td>
                    <td class="det2" style="text-align:left";  ><?php echo trim($DocumentDetailArr[0]['store']) ; ?></td>
                    <td class="det1" style="text-align:right"; >WareHouse</td>
                    <td class="det2" style="text-align:left";  ><?php echo $DocumentDetailArr[0]['depot'] ; ?></td>
                    <td Colspan="1">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="6">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                    <td Colspan="1">&nbsp</td>
                    <td class="det1" style="text-align:left"; >Document Number</td>
                    <td class="det2" style="text-align:left"; ><?php echo $DocumentDetailArr[0]['document_number'] ; ?></td>
                    <td class="det1" style="text-align:right"; >Invoice Date</td>
                    <td class="det2" style="text-align:left"; ><?php echo $DocumentDetailArr[0]['invoice_date'] ; ?></td>
                    <td Colspan="1" ><input type="hidden" name="DOCUID"  value=<?php echo $DocumentDetailArr[0]['dmUid'];?>>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>"/>
                 <td Colspan="6">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class);?>"/>
                      <td Colspan="1">&nbsp</td>
                      <td class="det1" style="text-align:left";>GRV&nbsp;No</td>
                      <td class="det2" style="text-align:left"; ><input type="text" name="GRVNO" autofocus value=""/><br></td>
                    	<td Colspan="3";style="text-align:center"></td>
               </tr>  
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="6">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class);?>"/>
                      <td Colspan="1">&nbsp</td>
                      <td Colspan="1" class="det1" style="text-align:left";>Actual&nbsp;Delivery&nbsp;Date</td>
                      <td Colspan="3" class="det2" style="text-align:left"; ><?php DatePickerElement::getDatePickerLibs(); DatePickerElement::getDatePicker("DELDATE",CommonUtils::getUserDate()); ?></td>
                      <td Colspan="1";style="text-align:center"></td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="6">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td colspan="6"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="DEBRIEFDOC"   value= "Debrief Document">
                                                             <INPUT TYPE="submit" class="submit" name="BACK"       value= "Back"></td>
               </tr>          
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="6">&nbsp</td>
               </tr>  
 			    	</table>
		   </form>
    </center>  	
    <?php     
  }
// ********************************************************************************************************************************	
  public function showSelectedPartialDocument($jsonDocumentDetail) {
  	
  	  $DocumentDetailArr = json_decode($jsonDocumentDetail, true);
  	  
  	  $postDELDATE = (isset($_POST["PAYDATE"])) ? htmlspecialchars($_POST["PAYDATE"]) :  CommonUtils::getUserDate();
  	  
  	  $getRcList = new deBriefingDAO($this->dbConn);
  	  $reasonlist   = $getRcList->getReasonList();  
  	  	  
  	  // echo "<pre>";
  	  // print_r($DocumentDetailArr);
  	  ?>
       <center>
       <FORM name='Process Full Delivery' method=post action=''>
            <table width="900"; style="border:none">
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                   <td class=head1 Colspan="6" style="text-align:center;";>Full Delivery</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                   <td Colspan="6">&nbsp</td>
               </tr>     	
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td class="det1" style="border:none; width: 5%;">&nbsp</td>
                 <td class="det1" style="border:none; width: 20%;">Principal</td>
                 <td class="det1" style="border:none; width: 30%;"><?php echo trim($DocumentDetailArr[0]['store']) ; ?></td>
                 <td class="det1" style="border:none; width: 20%;">WareHouse</td>
                 <td class="det1" style="border:none; width: 20%;"><?php echo $DocumentDetailArr[0]['depot'] ; ?></td>
                 <td class="det1" style="border:none; width: 5%;">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="6">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                    <td Colspan="1">&nbsp</td>
                    <td class="det1" style="text-align:left";>Document Number</td>
                    <td class="det2" style="text-align:left";><?php echo $DocumentDetailArr[0]['document_number'] ; ?></td>
                    <td class="det1" style="text-align:right";>Actual&nbsp;Delivery&nbsp;Date</td>
                    <td class="det2" style="text-align:left";><?php DatePickerElement::getDatePickerLibs(); DatePickerElement::getDatePicker("DELDATE",CommonUtils::getUserDate()); ?></td>
                    <td Colspan="1"><input type="hidden" name="DOCUID"  value=<?php echo $DocumentDetailArr[0]['dmUid'];?>>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>"/>
                 <td Colspan="6">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class);?>"/>
                      <td Colspan="1">&nbsp</td>
                      <td class="det1" style="text-align:left";>GRV&nbsp;No</td>
                      <td class="det1" style="text-align:left"; ><input type="text" name="GRVNO" autofocus value=""/><br></td>
                      <td class="det1" style="text-align:left";>Claim&nbsp;No</td>
                      <td class="det1" style="text-align:left"; ><input type="text" name="CLMNO" value=""/><br></td>


                    	<td Colspan="1";style="text-align:center"></td>
               </tr>  
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="6">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class);?>"/>
                      <td Colspan="1">&nbsp</td>
                      <td Colspan="1" class="det1" style="text-align:left";>Actual&nbsp;Delivery&nbsp;Date</td>
                      <td Colspan="1" class="det2" style="text-align:left"; ><?php DatePickerElement::getDatePickerLibs(); DatePickerElement::getDatePicker("DELDATE",CommonUtils::getUserDate()); ?></td>
                      
                      <td Colspan="1" class="det1" style="text-align:left";>Reason For Return</td>
                      <td Colspan="1">
                            <select name="REASON" id="REASON">
                               <option value="Select Transporter">Select Reason</option>
                               <?php foreach($reasonlist as $row) { ?>
                                    <option value="<?php echo $row['rcUid']; ?>"><?php echo $row['reason']; ?></option>
                               <?php } ?>
                            </select>
                    </td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="6">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="6">
                 	   <table style="width: 100%; border:none">
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                           <td class="det1" style="width: 1%; border:none;">&nbsp</td>
                           <td class="det1" style="width: 15%; text-align:left; border:none;">Product Code</td>
                           <td class="det1" style="width: 40%; border:none;">Product</td>
                           <td class="det1" style="width: 15%; border:none;">Invoiced Qty</td>
                           <td class="det1" style="width: 15%; border:none;">Returned Qty</td>
                           <td class="det1" style="width: 15%; border:none;">Status</td>
                           <td class="det1" style="width: 5%; border:none;">&nbsp</td> 
                        </tr>
                        <?php foreach($DocumentDetailArr AS $row) { ?>
                              <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                 <td class="det2" style="border:none;">&nbsp</td>
                                 <td class="det2" style="text-align:left; border:none;"><?php echo $row['prodCode'];?></td>
                                 <td class="det2" style="border:none;"><?php echo $row['product'];?></td>
                                 <td class="det2" style="border:none; text-align:center;"><?php echo $row['document_qty'];?></td>
                                 <td class="det2" style="border:none;"><input type="text" size="5" name="RETQTY[]" value=""> </td>
                                 <td class="det2" style="border:none;"><?php $lableArr = array('Good Stock','Damages');
          		                                                $valueArr = array('2','1');
          		                                                BasicSelectElement::buildGenericDD('STSTATUS[]', $lableArr,$valueArr, $postSTSTATUS, "N", "N", null, null, null);?>
                                 </td>
                                 <td class="det2" style="border:none;"><input type="hidden" name="PRODUID[]" value=<?php echo $row['prodUid'];?>></td> 
                        </tr>	   
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                             <td Colspan="7">&nbsp</td>
                        </tr>                        	
                        	
                        <?php } ?>
                        
                        
                    </table>
                 </td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td colspan="6"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="DEBRIEFPART"   value= "Debrief Document">
                                                             <INPUT TYPE="submit" class="submit" name="BACK"       value= "Back"></td>
               </tr>          
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="6">&nbsp</td>
               </tr>  
 			    	</table>
		   </form>
    </center>  	
    <?php     
  }
// ********************************************************************************************************************************	
  public function showSelectedRddDocument($jsonDocumentDetail) {
  	
  	  $DocumentDetailArr = json_decode($jsonDocumentDetail, true);

  	  $getRcList = new deBriefingDAO($this->dbConn);
  	  $reasonlist   = $getRcList->getReasonList();  
  	  	  
  	  // echo "<pre>";
  	  // print_r($DocumentDetailArr);
  	  ?>
       <center>
       <FORM name='Show Document' method=post action=''>
            <table width="900"; style="border:none">
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                   <td class=head1 Colspan="6" style="text-align:center;";>Process Document Return</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                   <td Colspan="6">&nbsp</td>
               </tr>     	
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td width="5%"; style="border:none">&nbsp</td>
                 <td width="20%"; style="border:none">&nbsp</td>
                 <td width="30%"; style="border:none">&nbsp</td>
                 <td width="20%"; style="border:none">&nbsp</td>
                 <td width="20%"; style="border:none">&nbsp</td>
                 <td width="5%" ; style="border:none">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                    <td Colspan="1">&nbsp</td>
                    <td class="det1" style="text-align:left";>Principal</td>
                    <td class="det2" style="text-align:left";><?php echo trim($DocumentDetailArr[0]['store']) ; ?></td>
                    <td class="det1" style="text-align:right";>WareHouse</td>
                    <td class="det2" style="text-align:left";><?php echo $DocumentDetailArr[0]['depot'] ; ?></td>
                    <td Colspan="1">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="6">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                    <td Colspan="1">&nbsp</td>
                    <td class="det1" style="text-align:left";>Document Number</td>
                    <td class="det2" style="text-align:left";><?php echo $DocumentDetailArr[0]['document_number'] ; ?></td>
                    <td class="det1" style="text-align:right";>Date</td>
                    <td class="det2" style="text-align:left";><?php echo $DocumentDetailArr[0]['invoice_date'] ; ?></td>
                    <td Colspan="1"><input type="hidden" name="DOCUID"  value=<?php echo $DocumentDetailArr[0]['dmUid'];?>>
                    	              <input type="hidden" name="TRIPUID" value=<?php echo $DocumentDetailArr[0]['thUid'];?>>
                    	              <input type="hidden" name="DEPUID"  value=<?php echo $DocumentDetailArr[0]['depotUid'];?>>
                    	              <input type="hidden" name="DAMUID"  value=<?php echo $DocumentDetailArr[0]['redelivery_warehouse'];?>>
                    	              <input type="hidden" name="TRIPNUM" value=<?php echo $DocumentDetailArr[0]['tripsheet_number'];?>></td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="6">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                    <td Colspan="1">&nbsp</td>
                    <td class="det1" style="text-align:left";>Trip Sheet Number</td>
                    <td class="det2" style="text-align:left";><?php echo $DocumentDetailArr[0]['tripsheet_number'] ; ?></td>
                    <td class="det1" style="text-align:right";>Driver</td>
                    <td class="det2" style="text-align:left";><?php echo $DocumentDetailArr[0]['transporter'] ; ?></td>
                    <td Colspan="1">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="6">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                    <td Colspan="1">&nbsp</td>
                    <td class="det1" style="text-align:left";>Reason For Re Delivery</td>
                    <td colspan="2">
                            <select name="REASON" id="REASON">
                               <option value="Select Transporter">Select Reason</option>
                               <?php foreach($reasonlist as $row) { ?>
                                    <option value="<?php echo $row['rcUid']; ?>"><?php echo $row['reason']; ?></option>
                               <?php } ?>
                            </select>
                    </td> 
                    <td Colspan="2">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="6">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td colspan="6"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="PROCREDELIVER"   value= "Return For Re Delivery">
                 	                                           <INPUT TYPE="submit" class="submit" name="BACK"       value= "Back"></td>
               </tr>          
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="5">&nbsp</td>
               </tr>  
 			    	</table>
		   </form>
    </center>  	
    <?php     
  }
// ********************************************************************************************************************************	

}