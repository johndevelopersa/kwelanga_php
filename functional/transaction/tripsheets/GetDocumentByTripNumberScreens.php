<?php 
include_once('ROOT.php'); 
include_once($ROOT.'PHPINI.php');	
include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER."DAO/MaintenanceDAO.php");
include_once($ROOT.$PHPFOLDER.'elements/basicSelectElement.php');	


class GetDocumentByTripNumberScreens {
	
	   function __construct() {

         global $dbConn, $ROOT, $PHPFOLDER ;
         $this->dbConn = $dbConn;
      }

// ********************************************************************************************************************************	
  
     public function showTripSheetNumber() { 
     	   $class = 'odd'; ?>
         <center>
             <FORM name='Select Invoice' method=post action=''>
                <table width='750px'; style="border:none">
                    <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        <td Colspan="5">&nbsp</td>
                    </tr>  
                	  <tr>
                        <td class='head1' Colspan="5" style="text-align:center"; >Mange a Tripsheet</td>
                    </tr>
                    <tr>
                        <td width='10%';>&nbsp</td>
                        <td width='30%';>&nbsp</td>
                        <td width='40%';>&nbsp</td>
                        <td width='10%';>&nbsp</td>
                        <td width='10%';>&nbsp</td>
                    </tr>	        	      	
                    <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        <td Colspan="5">&nbsp</td>
                    </tr>
                    <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        <td>&nbsp</td>
                        <td class='det1' style="text-align:left";>Enter Tripsheet Number</td>
                        <td colspan="2"; style="text-align:left"><input type="text" name="TSNUMBER"><br></td>
                        <td>&nbsp</td>
                    </tr>
                    <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        <td Colspan="5">&nbsp</td>
                    </tr>
                    <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        <td>&nbsp</td>
                        <td class='det1' style="text-align:left";>Show Removed Documents</td>
                        <td colspan="2"; style="text-align:left"><input type="checkbox" name="REMDOCS" value="A"></td>
                        <td>&nbsp</td>
                    </tr>
                    <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        <td Colspan="5">&nbsp</td>
                    </tr>
                    <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="FIRSTFORM" value= "Get Tripsheet Details">
                        	                                          <INPUT TYPE="submit" class="submit" name="CANFORM" value= "Cancel"></td>
                    </tr>          
                </table>
             </form>
         </center>
     <?php     
     } 
// ********************************************************************************************************************************	
     public function showTripSheetDocuments($depotId, $postTSNUMBER, $remStatus) {
     	
     	    global $ROOT, $PHPFOLDER ;
     	
          $tripsheetDAO = new tripsheetDAO($this->dbConn);
          $mfRTS = $tripsheetDAO->getDocumentsOnTripsheet($depotId, $postTSNUMBER, $remStatus);
          
          if (sizeof($mfRTS)!==0) {
         	    $class = 'odd'; ?>    	
              <center>          
                   <FORM name='removets' method=post target='' >
                       <table width='1000px'; style="border:none">
                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                  <td class='head1' colspan="10"  ' style="text-align:center;">Select Trip Sheet Documents to Manage - <?php echo $postTSNUMBER; ?>  </td>            	
                             </tr>	
                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                 <td class='head1' colspan="10"  ' style="text-align:center;"><input type="hidden" id="TSNUM" name="TSNUM"    value=<?php echo $postTSNUMBER; ?>></td>            	
                             </tr>
                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                 <td class='head1' colspan="10"  ' style="text-align:center;">&nbsp;</td>            	
                             </tr>                          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                       <td class='det1' style="text-align:left";>&nbsp;</td>
                                       <td class='det1' style="text-align:left";>Driver/Transporter</td>
                                       <td class='det2' style="text-align:left";><?php echo $mfRTS[0]['name'];?></td>
                                       <td class='det1' style="text-align:left";><input type=button onClick=window.open("<?php echo $ROOT . $PHPFOLDER?>functional/transaction/tripsheets/changeTripsheetTransporter.php?TSNUMBER=<?php echo $postTSNUMBER;?>","demo","width=800,height=300,left=150,top=200,toolbar=0,status=0"); value="Change Transporter"></td>
                                       <td class='det1' colspan="2" style="text-align:right";>Trip Sheet Date</td>
                                       <td class='det2' colspan="3" style="text-align:left";><?php echo $mfRTS[0]['tripsheet_date'];?></td>
                                       <td class='det1' style="text-align:left";>&nbsp;</td>
                             </tr>
                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                 <td class='head1' colspan="10"  ' style="text-align:center;">&nbsp;<input type="hidden" id="TSNUM" name="TSNUM"    value=<?php echo $postTSNUMBER; ?>></td>            	
                             </tr>	
                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                 <td class='det1' width="5%"; style="text-align:left";>&nbsp;</td>
                                 <td class='det1' width="8%"; style="text-align:left";>Principal</td>
                                 <td class='det1' width="13%"; style="text-align:left";>Document No</td>
                                 <td class='det1' width="40%"; style="text-align:left";>Store</td>
                                 <td class='det1' width="5%"; style="text-align:right";>Quantity</td>
                                 <td class='det1' width="5%"; style="text-align:right";>Total</td>
                                 <td class='det1' width="5%"; style="text-align:right";>Scanned</td>                              
                                 <td class='det1' width="5%"; style="text-align:left";>Re Deliver</td> 
                                 <td class='det1' width="11%"; style="border:none; text-align: center;">Select<br><a href="javascript:;" onClick="selectAll('DOCLIST[]', 1);">All</a>&nbsp;|&nbsp;<a href="javascript:;" onClick="selectAll('DOCLIST[]', 0);">None</a></td>
                                 <td class='det1' width="3%"; style="text-align:left";>&nbsp;</td>
                            </tr>
                            <?php
                            $tdisPatch = 'N';
                            foreach($mfRTS as $row) {  ?>
                                  <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                       <td class='det1' style="text-align:left";>&nbsp;</td>
                                       <td class='det2' style="text-align:left";><?php echo $row['shortname'];?></td>
                                       <td class='det2' style="text-align:left";><?php echo $row['Docno'];?></td>
                                       <td class='det2' style="text-align:left";><?php echo trim($row['Store']);?></td>
                                       <?php 
                                       if($row['decimal_updated'] == 'N') {?>
                                              <td class='det2' style="text-align:right";><?php echo $row['Cases'];?></td>
                                              <td class='det2' style="text-align:right";><?php echo round($row['total'],2);?></td>
                                       <?php                                        	
                                       } else {?>
                                              <td class='det2' style="text-align:right";><?php echo $row['Cases']/100;?></td>
                                              <td class='det2' style="text-align:right";><?php echo round($row['total']/100,2);?></td>
                                       <?php                                        	
                                       }
                                       if($row['removed_flag'] == 'Y') { ?>
                                            <td class='det2' colspan="3" style="text-align:right";>Document Removed</td>
                                            <td class='det1' style="text-align:left";>&nbsp;</td>
                                       <?php 	
                                       } else { 
                                            if($row['i_dispatched'] == 'Y') { $docDis    = 'Yes';} else { $docDis = 'No';} 
                                            if($row['t_dispatched'] == 'Y') { $tdisPatch = 'Y';} ?>
                                            <td class='det2' style="text-align:right";><?php echo $docDis;?></td>
                                            <?php
                                            if($row['Redeliver'] == '90') { $reDelivery = 'Yes';} else { $reDelivery = '';} ?>
                                            <td class='det2' style="text-align:right";><?php echo $reDelivery;?></td>
                                            <td class='det2' style="text-align:center";><INPUT TYPE="checkbox" name="DOCLIST[]" value= "<?php echo  $row['i_dispatched'] . $row['dm_uid'] ;?>"></td>
                                            <td class='det1' style="text-align:left";>&nbsp;</td>
                                       <?php
                                       } ?>
                                  </tr>
                                  <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                       <td class='det1' style="text-align:left";>&nbsp;</td>
                                       <td class='det1' style="text-align:left";>&nbsp;</td>
                                       <td class='det1' style="text-align:left";>&nbsp;</td>
                                       <td class='det1' style="text-align:left";>&nbsp;</td>
                                       <td class='det1' style="text-align:left";>&nbsp;</td>
                                       <td class='det1' style="text-align:left";>&nbsp;</td>
                                       <td class='det1' style="text-align:left";>&nbsp;</td>
                                       <td class='det1' style="text-align:left";>&nbsp;</td>
                                       <td class='det1' style="text-align:left";>&nbsp;</td>
                                       <td class='det1' style="text-align:left";>&nbsp;</td>
                                  </tr>
                            <?php 	
                            } 
                            if($mfRTS[0]['verified_for_dispatch'] <> 'Y') {
                            	    $rmStatus ='';
                            
                                  $tripsheetDAO = new tripsheetDAO($this->dbConn);
                                  $mfRC = $tripsheetDAO->gettripSheetReason($depotId); ?> 
                                  <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                      <td class='det1' colspan="10"  ' style="text-align:center;">&nbsp;</td>            	
                                  </tr>	 
                                  <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                      <td class='det1' colspan="10"  ' style="text-align:center;">Select Reason for Removal</td>
                                  </tr>
                                  <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                      <td class='det1' colspan="10"  ' style="text-align:center;">&nbsp;</td>            	
                                  </tr>	 
                                  <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                      <td class='det1' colspan="2" style="text-align:center;">&nbsp;</td>
                                      <td class='det1' colspan="4" style="text-align:center;">                              	
                                      <select name="REASON" id="REASON">
                                          <option value="Select Reason">Select Reason</option>
                                          <?php
                                          foreach($mfRC as $row) { ?>
                                              <option value="<?php echo $row['uid']; ?>"><?php echo $row['description']; ?></option>
                                          <?php 
                                          } ?>
                                      </select>
                                     <td class='det1' colspan="4" style="text-align:center;">&nbsp;</td>
                                 </tr>	 
                                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                     <td class='det1' colspan="10" style="text-align:center;">&nbsp;</td>            	
                                </tr>
                            <?php
                          } else { 
                          	     $rmStatus = 'disabled'; ?>
                                <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                	
                                	
                                    <td class='det1' colspan="10" style="text-align:center;">&nbsp;</td>            	
                                </tr>	 
                                <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                    <td class='det1' colspan="10" style="text-align:center; color:red;">Tripsheet Verified for Dispatch</td>
                                </tr>
                                <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                    <td class='det1' colspan="10" style="text-align:center;">&nbsp;</td>            	
                                </tr>	 

                                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                     <td class='det1' colspan="10"  ' style="text-align:center;">&nbsp;</td>            	
                                </tr>
                            <?php
                          }
                          if($tdisPatch == 'Y') {?>
                                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                     <td class='det1' colspan="10"  ' style="text-align:center;"><INPUT TYPE="submit" class="submit" name="FINISH" value= "Return For Re Delivery">
                                   	                                                         <INPUT TYPE="submit" class="submit" name="TSPRINT" value= "Print TripSheet">
                                   	                                                         <INPUT TYPE="submit" class="submit" name="REFRESH" value= "Refresh">
                                                                                             <INPUT TYPE="submit" class="submit" name="BACKBUTTON" value= "Back"></td>
                                 </tr>
                            <?php                             	
                            } else {
                            	?>
                                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                    <td class='det1' colspan="10"  ' style="text-align:center;"><INPUT TYPE="submit" class="submit" name="FINISH" value= "Remove document from Tripsheet" <?php echo $rmStatus ?>>
                                 	                                                          <INPUT TYPE="submit" class="submit" name="TSPRINT" value= "Print TripSheet">
                                 	                                                          <INPUT TYPE="submit" class="submit" name="REFRESH" value= "Refresh">
                                                                                            <INPUT TYPE="submit" class="submit" name="BACKBUTTON" value= "Back"></td>
                                 </tr>
                            <?php   
                            } ?>    
                            <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                 <td class='det1' colspan="10"  ' style="text-align:center;">&nbsp;</td>            	
                            </tr> 
                            <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                 <td class='det1' colspan="10"  ' style="text-align:center;">&nbsp;</td>            	
                            </tr> 
                       </table>
                   </form>
              </center>
              <?php
              } else { ?>
                  <script type='text/javascript'>parent.showMsgBoxError('No Documents found on Tripsheet - Include Show Removed')</script> 
              <?php
              }  
     }      
// ********************************************************************************************************************************	

}    // End of Class      	
      
