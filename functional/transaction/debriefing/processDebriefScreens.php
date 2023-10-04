<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER."libs/GUICommonUtils.php");
    		    
class processDebriefScreens {
	
      function __construct() {

         global $dbConn;
         $this->dbConn = $dbConn;
      }	
// ********************************************************************************************************************************	
  public function getDocumentNumber() {
  	
  	  ?>
       <center>
       <FORM name='Select Invoice' method=post action=''>
            <table width="720"; style="border:none">
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                   <td class=head1 Colspan="5" style="text-align:center;";>Process Document Return</td>
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
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="REDELIVER"   value= "Return For Re Delivery">
                 	                                           <INPUT TYPE="submit" class="submit" name="DOCCOMPLETE" value= "Delivery Complete">
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
  public function showSelectedDocument($jsonDocumentDetail, $rClist) {
  	
  	  $DocumentDetailArr = json_decode($jsonDocumentDetail, true);
  	  
  	  $reasonlist = json_decode($rClist, true); 	  
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
                 	                                           <INPUT TYPE="submit" class="submit" name="DOCCOMPLETE" value= "Delivery Complete">
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
// ********************************************************************************************************************************	
