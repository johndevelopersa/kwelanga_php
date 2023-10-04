<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER.'DAO/AgedStockDAO.php');
    include_once($ROOT.$PHPFOLDER."libs/GUICommonUtils.php");
    		    
class simpleForm {
	
      function __construct() {

         global $dbConn;
         $this->dbConn = $dbConn;
      }	
// ********************************************************************************************************************************	
  public function firstform() {
  	
  	  ?>
       <center>
       <FORM name='Select Invoice' method=post action='simpleForm.php'>
            <table width="720"; style="border:none">
               <tr>
                 <td class=head1 Colspan="5";>Capture Store Uplifts</td>
               </tr>
               <tr>
                 <td>&nbsp</td>
               </tr>	        	
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td width="38%"; style="border:none">&nbsp</td>
                 <td width="20%"; style="border:none">&nbsp</td>
                 <td width="20%"; style="border:none">&nbsp</td>
                 <td width="20%"; style="border:none">&nbsp</td>
                 <td width="2%" ; style="border:none">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                 <td style="text-align:left";>Enter Uplift Number</td>
                 <td colspan="4"; style="text-align:left"><input type="text" name="INVOICE"></td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="5">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="firstform" value= "Get Uplift Details">
                                                             <INPUT TYPE="submit" class="submit" name="canform"   value= "Cancel"></td>
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
  public function receiptform($principalId, $postINVOICE) {
  	
  	     $AgedStockDAO = new AgedStockDAO($this->dbConn);
         $mfDDU = $AgedStockDAO->getDocumentDetailsToUpdate(mysqli_real_escape_string($this->dbConn->connection, $principalId ), 
                                                            mysqli_real_escape_string($this->dbConn->connection, $postINVOICE));
         
         if (count($mfDDU) > 0) {
              $parray = array(81);	
              if(!in_array($mfDDU[0]['document_status_uid'], $parray)) {
                 $AgedStockDAO = new AgedStockDAO($this->dbConn);
                 $whSr = $AgedStockDAO->getWareHouseReceipt($mfDDU[0]['uid']) ;
                  ?>
                 <center>
                   <form name='Select Invoice' method=post action='simpleForm.php'>
                    <table width="720"; style="border:none">
                        <tr>
                            <td class=head1 Colspan="5";>Verify The Warehouse Receipt</td>
                        </tr>
                        <tr>
                           <td>&nbsp</td>
                        </tr>	        	
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                           <td width="20%"; style="border:none">&nbsp</td>
                           <td width="20%"; style="border:none">&nbsp</td>
                           <td width="20%"; style="border:none">&nbsp</td>
                           <td width="20%"; style="border:none">&nbsp</td>
                           <td width="20%"; style="border:none">&nbsp</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                           <td style="text-align:left";>&nbsp;</td>
                           <td style="text-align:left";>Store</td>
                           <td colspan="1"; style="text-align:left"><?php echo $mfDDU[0]['deliver_name'];?></td>
                           <?php
                           if (count($whSr) > 0) {
                                $whR = 'T';
                           ?>
                               <td style="text-align:left";>Warehouse Receipt Date</td>
                               <td colspan="1"; style="text-align:left"><?php echo $whSr[0]['date'];?>
                                                                        <input type="hidden" name="WHR" value=<?php echo $whR; ?>></td>
                           <?php
                           } else  {
                          	     $whR = 'F';
                           ?>	     
                               <td colspan="2"; style="text-align:left; font-weight:bold; color:red;">No Warehouse Receipt Captured
                               	                                       <input type="hidden" name="WHR" value=<?php echo $whR; ?>></td>
                           <?php
                          } ?>	
                       </tr>               	
                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5"><input type="hidden" name="DOCUID" value=<?php echo $mfDDU[0]['docUid']; ?>></td>
                       </tr>
                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5"><input type="hidden" name="DOCNUM" value=<?php echo mysqli_real_escape_string($this->dbConn->connection, $postINVOICE); ?>></td>
                       </tr>
                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                          <td style="text-align:left";>&nbsp;</td>
                          <td colspan="2"; style="text-align:left">Enter the number of Boxes </td>
                          <td style="text-align:left";><input type="text" name="cBOX" size="5"></td>
                          <td colspan="2"; style="text-align:left">&nbsp;</td>
                       </tr>               	               
                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                         <td Colspan="5">&nbsp</td>
                       </tr>
                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5" style="text-align:center";><INPUT TYPE="submit" class="submit" name="receiptform" value= "Verify Warehouse Receipt">
                                                                    <INPUT TYPE="submit" class="submit" name="canform"     value= "Cancel"></td>
                       </tr>               
                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                        </tr>
                    </table>
                   </form>
                 </center>
    <?php
             } else { ?>
              <script type='text/javascript'>parent.showMsgBoxError('Document Already Captured')</script> 
              <?php 
              unset($postINVOICE);
              unset($_POST['firstform']);
             }        
         } else { ?>
              <script type='text/javascript'>parent.showMsgBoxError('Document Number not Found')</script> 
              <?php 
              unset($postINVOICE);
              unset($_POST['firstform']);
         }        
  } 
// ********************************************************************************************************************************	
  public function receiptError($rbox, $rdocmun) {
  	
  	   ?> 
    <center>
       <form name='WareHouse Receipt Error' method=post action='simpleForm.php'>
        <table width="500"; style="border:none">
            <tr>
               <td width="20%";>&nbsp</td>
               <td width="20%";>&nbsp</td>
               <td width="20%";>&nbsp</td>
               <td width="20%";>&nbsp</td>
            </tr>
            <tr>
               <td Colspan="4">&nbsp</td> 	
            </tr>
            <tr>
               <td Colspan="4">&nbsp</td> 	
            </tr>
        </table>
        <table class="box" width="400";>
            <tr>
               <td width="5%";>&nbsp</td>
               <td width="30%";>&nbsp</td>
               <td width="30%";>&nbsp</td>
               <td width="30%";>&nbsp</td>                             
               <td width="5%"; style="border:collapse; border-right: 2px solid; border-color: #990000;">&nbsp</td>  
            <tr>
               <td Colspan="1" rowspan="3"><img src="<?php echo 'error-icon-big.png'; ?>" style="width:60px; height:60px; float:left;" ></td> 	
               <td Colspan="3" style="font-size: 13px; font-weight: bold;">Warehouse Box Receipt Quantity<br><br>Not Equal to Captured Box Quantity</td> 
               <td Colspan="1" style="border:collapse; border-right: 2px solid; border-color: #990000;">&nbsp</td> 
            </tr>
            <tr>
               <td Colspan="5"><input type="hidden" name="RDOCNUM" value=<?php echo mysqli_real_escape_string($this->dbConn->connection, $rdocmun); ?>>
               	               <input type="hidden" name="RBOX"    value=<?php echo mysqli_real_escape_string($this->dbConn->connection, $rbox); ?>></td> 	
            </tr>       	
            <tr>
               <td Colspan="5"; style="text-align:center";><INPUT TYPE="submit" class="submit" name="CaptCont" value= "Continue ">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                           <INPUT TYPE="submit" class="submit" name="CaptCancel"  value= "Cancel Capture"></td> 	
            </tr> 
            <tr>
               <td Colspan="5" style="border:collapse; border-right: 2px solid; border-color: #990000;">&nbsp</td> 	
            </tr> 
            <tr>
               <td Colspan="5" style="border:collapse; border-right: 2px solid; border-color: #990000;">&nbsp</td> 	
            </tr>
        </table>


       </form>
    </center>
<?php     
  }
// ********************************************************************************************************************************	
  public function upliftDetailCapture($principalId, 
                                      $INVOICE, 
                                      $capBoxes, 
                                      $restarray,
                                      $restarraydq,
                                      $restarrayrf,
                                      $restarraydam,
                                      $restarraydamB,
                                      $restarraydamC) {
  	
      $AgedStockDAO = new AgedStockDAO($this->dbConn);
      $mfDDU = $AgedStockDAO->getDocumentDetailsToUpdate($principalId, $INVOICE);
       
?>
       <center>
           <form name='Select Invoice' method=post action=''>
               <table width="1200"; style="border:none">
                  <tr>
                     <td class=head1 colspan="6"; style="text-align:center">Capture Uplifts from Store</td>
                  </tr>
                  <tr>
                     <td class=head1 colspan="6"; style="text-align:center">&nbsp;</td>
                  </tr>                      
                  <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        <td width="2%";  style="border:none">&nbsp</td>
                        <td width="24%"; style="border:none">&nbsp</td>
                        <td width="24%"; style="border:none">&nbsp</td>
                        <td width="24%"; style="border:none">&nbsp</td>
                        <td width="24%"; style="border:none">&nbsp</td>
                        <td width="2%";  style="border:none">&nbsp</td>
                  </tr>
                  <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                  	    <td style="border:none">&nbsp</td>
                        <td colspan="1"; style="border:none; text-align:left; font-weight:bold; font-size: 12px; "><?php echo "Customer:            " . trim($mfDDU[0]['deliver_name'] ." "); ?></td>
                        <td colspan="1";style="border:none">&nbsp</td>
                        <td colspan="2"; style="border:none; text-align:right; font-weight:bold; font-size: 12px; "><?php echo "Document No.        " . substr($mfDDU[0]['document_number'],2,6) ." "; ?>
                                                                                <input type="hidden" name="DOCNO"     value=<?php echo substr($mfDDU[0]['document_number'],2,6); ?>>
                                                                                <input type="hidden" name="DOCMID"    value=<?php echo $mfDDU[0]['uid']; ?>>
                                                                                <input type="hidden" name="SCBOX"     value=<?php echo mysqli_real_escape_string($this->dbConn->connection, $capBoxes); ?>>
                                                                                <input type="hidden" name="WAREHOUSE" value=<?php echo $mfDDU[0]['depot_uid']; ?>>
                                                                                <input type="hidden" name="DOCSTAT"  value="<?php echo $mfDDU[0]['Status'];?>"></td>
                        <td style="border:none">&nbsp</td>
                  </tr>
                  <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>                               
                        <td colspan="6"; style="text-align:left">&nbsp</td>
                  </tr>
                  <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                  	    <td style="border:none">&nbsp</td>                               
                        <td colspan="1"; style="text-align:left;  font-weight:bold;   font-size: 12px; ">Total Number of Boxes</td>
                        <td colspan="1"; style="text-align:left   font-weight:normal; font-size: 12px; "><?php echo mysqli_real_escape_string($this->dbConn->connection, $capBoxes); ?></td>
                        <td colspan="1"; style="text-align:right; font-weight:bold;   font-size: 12px; ">Reference Number: </td>
                        <td colspan="1"; style="text-align:right; font-weight:bold;   font-size: 12px;"><input type="text" name="UREF" size="30"></td>
                        <td colspan="1";>&nbsp</td> 
                  </tr>
                  <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>                               
                        <td colspan="6"; style="text-align:left">&nbsp</td>
                  </tr>
               </table>
               <table width="1200"; style="border:none">
                  <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                  	    <td style="width:  2%; text-align:center; border-left; border-none">&nbsp</td>
                        <td style="width: 10%; text-align:center; border-left: 1px dotted red; font-weight:bold;">Part<br>Number</td>
                        <td style="width: 10%; text-align:center; border-left: 1px dotted red; font-weight:bold">Bar Code</td>
                        <td style="width: 31%; text-align:center; border-left: 1px dotted red; font-weight:bold">Description</td>
                        <td style="width:  7%; text-align:center; border-left: 1px dotted red; font-weight:bold">Cost</td>
                        <td style="width:  7%; text-align:center; border-left: 1px dotted red; font-weight:bold">Aged<br>Stock</td>
                        <td style="width:  7%; text-align:center; border-left: 1px dotted red; font-weight:bold">Uplift<br>Quantity</td>
                        <td style="width:  7%; text-align:center; border-left: 1px dotted red; font-weight:bold">Display</td>
                        <td style="width:  7%; text-align:center; border-left: 1px dotted red; font-weight:bold">Store<br>Refuse</td>
                        <td style="width:  7%; text-align:center; border-left: 1px dotted red; font-weight:bold">Damages</td>
                        <td style="width:  2%; text-align:center; border-left: 1px dotted red; font-weight:bold">Damage<br>Reason B</td>
                        <td style="width:  2%; text-align:center; border-left: 1px dotted red; font-weight:bold">Damage<br>Reason C</td>
                        <td style="width:  1%;  border-left: 1px dotted red; font-weight:bold">&nbsp;</td>
                  </tr>
                  <?php
                  $srcrow = 0;
                  foreach ($mfDDU as $row) { ?>
                  	<tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                  	    <td style="border-none">&nbsp</td>
                        <td style="border-left: 1px dotted red"><?php echo ($row['product_code'] ." "); ?></td>
                        <td style="border-left: 1px dotted red;"><?php echo ($row['outercasing_gtin'] ." "); ?></td>
                        <td style="border-left: 1px dotted red"><?php echo ($row['product_description'] ." "); ?></td>
                        <td style="border-left: 1px dotted red"><?php echo ($row['unit_value'] ." "); ?></td>
                        <td style="border-left: 1px dotted red"><?php echo ($row['ordered_qty'] ." "); ?></td>
                        <td style="border-left: 1px dotted red"><input style="float:right" type="text" name="ULQTY[]"  size="5" value="<?php echo $restarray[$row['detailUid']]; ?>">
                                                                <input type="hidden" name="detID[]"    value=<?php echo $row['detailUid']; ?>></td>
                                                                <input type="hidden" name="prodID[]"   value=<?php echo $row['product_uid']; ?>></td>
                                                                <input type="hidden" name="prodCode[]" value=<?php echo $row['product_code']; ?>></td>
                        <td style="border-left: 1px dotted red"><input style="float:right" type="text" name="DISQTY[]"   size="5" value="<?php echo $restarraydq[$row['detailUid']];   ?>"></td>
                        <td style="border-left: 1px dotted red"><input style="float:right" type="text" name="RFQTY[]"    size="5" value="<?php echo $restarrayrf[$row['detailUid']];   ?>"></td>
                        <td style="border-left: 1px dotted red"><input style="float:right" type="text" name="DAMAGES[]"  size="5" value="<?php echo $restarraydam[$row['detailUid']];  ?>"></td>
                        <td style="border-left: 1px dotted red"><input style="float:right" type="text" name="DAMAGESB[]" size="5" value="<?php echo $restarraydamB[$row['detailUid']]; ?>"></td>   
                        <td style="border-left: 1px dotted red"><input style="float:right" type="text" name="DAMAGESC[]" size="5" value="<?php echo $restarraydamC[$row['detailUid']]; ?>"></td>   
                        <td style="border-left: 1px dotted red; font-weight:bold">&nbsp;</td>
                  </tr>
                  <?php 
                       $srcrow++;
                  } ?>	

                  <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>                               
                      <td colspan="13"; style="text-align:left">&nbsp</td>
                  </tr>
                  <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";> 
                  	  <td style="border-none">&nbsp</td>
                      <td colspan="2"; style="text-align:left">&nbsp;</td>                              
                      <td colspan="1"; style="text-align:right; font-weight:bold; font-size: 12px;">Enter #hash Total : </td>";
                      <td colspan="1"; style="text-align:left; font-weight:bold; font-size: 12px;"><input type="text" name="hashT" size="5"></td>
                      <td colspan="7"; style="border-none">&nbsp;</td>
                  </tr>
                  <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>                               
                      <td colspan="13"; style="text-align:left">&nbsp</td>
                  </tr>
                  <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";> 
                     <td colspan="13"; style="text-align:center"><INPUT TYPE="submit" class="submit" name="finishform" value= "Submit Line"><INPUT TYPE="submit" class="submit" name="canform" value= "Cancel"></td>
                  </tr>
                  <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>                               
                     <td colspan="13";>&nbsp</td>
                  </tr>
               </table>
           </form>                     
       </center>

<?php
  }
}
// ********************************************************************************************************************************	
