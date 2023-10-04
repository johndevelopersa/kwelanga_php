<?php
    include_once('ROOT.php'); 
    include_once($ROOT.'PHPINI.php');
    include_once($ROOT.$PHPFOLDER."libs/GUICommonUtils.php");
    include_once($ROOT.$PHPFOLDER.'DAO/ManageOrdersDAO.php');
    
    class LoadStoreFromGlnClass{
	
        function __construct() {

           global $dbConn;
           $this->dbConn = $dbConn;
        }	
      
// ********************************************************************************************************************************	
        public function getGlnForm() {
        ?>
        <center>	
        <form name='Select Invoice' method=post action=''>
            <table width:"720"; style="border:none">        	
               <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                   <td style="width:5%;" >&nbsp;</td>
                   <td style="width:45%;" >&nbsp;</td>
                   <td style="width:45%;" >&nbsp;</td>
                   <td style="width:5%;" >&nbsp;</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                   <td class="head1" colspan="4"; style="text-align:center;" ><strong>Load Store From GLN</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                   <td colspan="4";>&nbsp</td>
               </tr>	        	
               <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
               	   <td>&nbsp;</td>
                   <td style="text-align:right";><strong>Enter Store GLN</td>
                   <td style="text-align:left";><input type="text" name="GLN" value="" ></td>
                   <td>&nbsp;</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                   <td colspan="4";>&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                   <td colspan="4"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="GETSTOREGLN" value= "Get Store Details"></td>
               </tr>          
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                   <td colspan="4";>&nbsp</td>
               </tr>  
            </table>
        </form>
        <?php 
	      }		
// ********************************************************************************************************************************	
        public function getUidForm() {
        ?>
        <center>	
        <form name='Select Invoice' method=post action=''>
            <table width:"720"; style="border:none">        	
               <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                   <td style="width:5%;" >&nbsp;</td>
                   <td style="width:45%;" >&nbsp;</td>
                   <td style="width:45%;" >&nbsp;</td>
                   <td style="width:5%;" >&nbsp;</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                   <td class="head1" colspan="4"; style="text-align:center;" ><strong>Load Store From UID</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                   <td colspan="4";>&nbsp</td>
               </tr>	        	
               <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
               	   <td>&nbsp;</td>
                   <td style="text-align:right";><strong>Enter Store UID</td>
                   <td style="text-align:left";><input type="text" name="STOREUID" value="" ></td>
                   <td>&nbsp;</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                   <td colspan="4";>&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                   <td colspan="4"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="GETSTOREUID" value= "Get Store Details"></td>
               </tr>          
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                   <td colspan="4";>&nbsp</td>
               </tr>  
            </table>
        </form>
        <?php 
	      }		


// ********************************************************************************************************************************	
        public function getBranchForm() {
        ?>
        <center>	
        <form name='Select Invoice' method=post action=''>
            <table width:"720"; style="border:none">        	
               <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                   <td style="width:5%;" >&nbsp;</td>
                   <td style="width:45%;" >&nbsp;</td>
                   <td style="width:45%;" >&nbsp;</td>
                   <td style="width:5%;" >&nbsp;</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                   <td class="head1" colspan="4"; style="text-align:center;" ><strong>Load Store From Branch</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                   <td colspan="4";>&nbsp</td>
               </tr>	        	
               <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                   <td colspan="4";>&nbsp</td>
               </tr>	
               <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                   <td colspan="1">&nbsp</td>
                   <td colspan="1"><span><input type="radio" id="CHECKERS" name="RETAILER" value="CHECKERS" CHECKED></span>
                   	                                      <span	style="font-weight: bold; text-align: center; padding-left : 20px;">Shoprite/Checkers</span></td>
                   <td colspan="1"><span><input type="radio" id="PNP" name="RETAILER" value="PNP"></span>
                   	               <span style="font-weight: bold; text-align: center; padding-left : 20px;">Pick n Pay</span></td></td>
                   <td colspan="1">&nbsp</td>
               </tr>	
               <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                   <td colspan="4";>&nbsp</td>
               </tr>	
               <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
               	   <td>&nbsp;</td>
                   <td style="text-align:right";><strong>Enter Store Branch No</td>
                   <td style="text-align:left";><input type="text" name="STOREBRANCH" value="" ></td>
                   <td>&nbsp;</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                   <td colspan="4";>&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                   <td colspan="4"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="GETSTOREBRANCH" value= "Get Store Details"></td>
               </tr>          
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                   <td colspan="4";>&nbsp</td>
               </tr>  
            </table>
        </form>
        <?php 
	      }		



// ********************************************************************************************************************************	
        public function getFindSelection() {
        ?>
        <center>	
        <form name='Select Invoice' method=post action=''>
            <table width:"720"; style="border:none">        	
               <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                   <td style="width:5%;" >&nbsp;</td>
                   <td style="width:45%;" >&nbsp;</td>
                   <td style="width:45%;" >&nbsp;</td>
                   <td style="width:5%;" >&nbsp;</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                   <td class="head1" colspan="4"; style="text-align:center;" ><strong>Select Store Find Type</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                   <td colspan="4";>&nbsp</td>
               </tr>	        	
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                   <td colspan="4"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="TYPESTOREGLN" value= "Get Store Details Using GLN"></td>
               </tr>          
               <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                   <td colspan="4";>&nbsp</td>
               </tr>	        	
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                   <td colspan="4"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="TYPESTOREBRANCH" value= "Get Store Details Using Branch"></td>
               </tr> 
               <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                   <td colspan="4";>&nbsp</td>
               </tr>	        	
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                   <td colspan="4"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="TYPESTOREUID" value= "Get Store Details Using Kwelanga UID"></td>
               </tr> 
               <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                   <td colspan="4";>&nbsp</td>
               </tr>	        	
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                   <td colspan="4"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="CANCEL" value= "Cancel"></td></td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                   <td colspan="4";>&nbsp</td>
               </tr>  
            </table>
        </form>
        <?php 
	      }		
// ********************************************************************************************************************************	

        public function showStoreDetails($LoadGlnStoreTO) { 
        	        	
          $ManageOrdersDAO = new ManageOrdersDAO($this->dbConn);
          $mfPR = $ManageOrdersDAO->getuserWarehouses( $LoadGlnStoreTO->Principal,  $LoadGlnStoreTO->UserId, "999");

          $MaintenanceDAO = new MaintenanceDAO($this->dbConn);
          $mChn = $MaintenanceDAO->getPrincipalChainList( $LoadGlnStoreTO->Principal);
        	?>
          <center>
              <FORM name='displayStore' method=post target=''>
                     <table style="border-none; width:60%; ">
                          <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                              <td style="width:10%;" >&nbsp;</td>
                              <td style="width:30%;" >&nbsp;</td> 
                              <td style="width:50%;" >&nbsp;</td>                                       
                              <td style="width:10%;" >&nbsp;</td>
                          </tr>
                          <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                              <td class="head1" colspan="5"; style="text-align:center" ><strong><?php echo "Load " .  $LoadGlnStoreTO->StoreType . " Store Details " ; ?></td>  
                          </tr>
                          <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                              <td  colspan="4"; style="text-align:center" ><input type="hidden" name="RTYPE" value="<?php echo $LoadGlnStoreTO->StoreType; ?>"></td>            	
                          </tr>                 
                          <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                              <td style="width:10%;" >&nbsp;</td>
                              <td class="det1" style="text-align:right; padding-right:30px;" >Store&nbsp;GLN</td> 
                              <?php
                              if($LoadGlnStoreTO->addType == 'GETSTOREGLN') {?>
                                   <td class="det2" style="text-align:left;" ><?php echo $LoadGlnStoreTO->gln;?></td> 
                                   <td style="width:10%;" ><input type="hidden" id="SGLN" name="SGLN"  value=<?php echo $LoadGlnStoreTO->gln; ?>></td>
                                    	
                              <?php
                              } else {?>
                                   <td class="det2" style="text-align:left;" ><input type="text" name="SGLN" size="50" value="<?php echo $LoadGlnStoreTO->gln;?>"</td> 
                                   <td style="width:10%;" >&nbsp;</td>
                              <?php
                              } ?>
                          </tr>
                          <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                              <td  colspan="4"; style="text-align:center" ><input type="hidden" name="ADDTYPE" value="<?php echo $LoadGlnStoreTO->addType; ?>"</td>            	
                          </tr>  
                          <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                              <td style="width:10%;" >&nbsp;</td>
                              <td class="det1" style="text-align:right; padding-right:30px;" >Suggested&nbsp;Store&nbsp;Name</td>
                              <?php 
                              if($LoadGlnStoreTO->StoreType == 'Pick N Pay') { ?>
                                     <td class="det2" style="text-align:left;" ><input type="text" name="SNAME" size="50" value="<?php echo "Pick N Pay " . $LoadGlnStoreTO->Name . "  " . $LoadGlnStoreTO->Branch ;?>"></td>                                       	
                              <?php
                              } else { ?>
                                     <td class="det2" style="text-align:left;" ><input type="text" name="SNAME" size="50" value="<?php echo $LoadGlnStoreTO->Name . " " . $LoadGlnStoreTO->Branch ;?>"></td>                                       	
                              <?php	
                              } ?>
                              <td style="width:10%;" >&nbsp;</td>
                          </tr>
                          <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                              <td  colspan="4"; style="text-align:center" >&nbsp;</td>            	
                          </tr>  
                          <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                              <td style="width:10%;" >&nbsp;</td>
                              <td class="det1" style="text-align:right; padding-right:30px;" >Address&nbsp;Line1</td> 
                              <td class="det2" style="text-align:left;" ><input type="text" name="SADD1" size="50" value="<?php echo $LoadGlnStoreTO->add1 ; ?>"></td>                                     
                              <td style="width:10%;" >&nbsp;</td>
                          </tr>
                          <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                              <td  colspan="4"; style="text-align:center" >&nbsp;</td>            	
                          </tr>  
                          <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                              <td style="width:10%;" >&nbsp;</td>
                              <td class="det1" style="text-align:right; padding-right:30px;" >Address&nbsp;Line2</td> 
                              <td class="det2" style="text-align:left;" ><input type="text" name="SADD2" size="50" value="<?php echo $LoadGlnStoreTO->add2 ;?>"></td>                                     
                              <td style="width:10%;" >&nbsp;</td>
                          </tr>                                 
                          <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                              <td  colspan="4"; style="text-align:center" >&nbsp;</td>            	
                          </tr>  
                          <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                              <td style="width:10%;" >&nbsp;</td>
                              <td class="det1" style="text-align:right; padding-right:30px;" >Address&nbsp;Line3</td> 
                              <td class="det2" style="text-align:left;" ><input type="text" name="SADD3" size="50" value="<?php echo $LoadGlnStoreTO->add3 ;?>"></td>                                     
                              <td style="width:10%;" >&nbsp;</td>
                          </tr>
                          <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                              <td  colspan="4"; style="text-align:center" >&nbsp;</td>            	
                          </tr>
                          <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                              <td style="width:10%;" >&nbsp;</td>
                              <td class="det1" style="text-align:right; padding-right:30px;" >Billing&nbsp;Store&nbsp;Name</td> 
                              <td class="det2" style="text-align:left;" ><input type="text" name="BNAME" size="50" value="<?php echo $LoadGlnStoreTO->BillName;?>"></td>                                     
                              <td style="width:10%;" >&nbsp;</td>
                          </tr>
                          <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                              <td  colspan="4"; style="text-align:center" >&nbsp;</td>            	
                          </tr>  
                          <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                              <td style="width:10%;" >&nbsp;</td>
                              <td class="det1" style="text-align:right; padding-right:30px;" >Billing&nbsp;Address&nbsp;Line1</td> 
                              <td class="det2" style="text-align:left;" ><input type="text" name="BADD1" size="50" value="<?php echo $LoadGlnStoreTO->Billadd1 ; ?>"></td>                                     
                              <td style="width:10%;" >&nbsp;</td>
                          </tr>
                          <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                              <td  colspan="4"; style="text-align:center" >&nbsp;</td>            	
                          </tr>  
                          <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                              <td style="width:10%;" >&nbsp;</td>
                              <td class="det1" style="text-align:right; padding-right:30px;" >Billing&nbsp;Address&nbsp;Line2</td> 
                              <td class="det2" style="text-align:left;" ><input type="text" name="BADD2" size="50" value="<?php echo $LoadGlnStoreTO->Billadd2 ;?>"></td>                                     
                              <td style="width:10%;" >&nbsp;</td>
                          </tr>                                 
                          <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                              <td  colspan="4"; style="text-align:center" >&nbsp;</td>            	
                          </tr>  
                          <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                              <td style="width:10%;" >&nbsp;</td>
                              <td class="det1" style="text-align:right; padding-right:30px;" >Address&nbsp;Line3</td> 
                              <td class="det2" style="text-align:left;" ><input type="text" name="BADD3" size="50" value="<?php echo $LoadGlnStoreTO->Billadd3 ;?>"></td>                                     
                              <td style="width:10%;" >&nbsp;</td>
                          </tr>
                          <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                              <td  colspan="4"; style="text-align:center" >&nbsp;</td>            	
                          </tr>
                              <td style="width:10%;" >&nbsp;</td>
                              <td class="det1" style="text-align:right; padding-right:30px;" >Use&nbsp;Deliver&nbsp;Address&nbsp;as<br>Billing&nbsp;Address</td>                            
                              <td class="det1" style="text-align:left";><INPUT TYPE="checkbox" name="BILLADD" value= "" ></td> 
                              <td style="width:10%;" >&nbsp;</td>
                          <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                          <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                              <td  colspan="4"; style="text-align:center" >&nbsp;</td>            	
                          </tr>  
                          <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                              <td style="width:10%;" >&nbsp;</td>
                              <td class="det1" style="text-align:right; padding-right:30px;" >Branch</td> 
                              <td class="det2" style="text-align:left;" ><input type="text" name="BRANCH" size="30" value="<?php echo $LoadGlnStoreTO->Branch ;?>"></td>                                     
                              <td style="width:10%;" >&nbsp;</td>
                          </tr>
                          <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                              <td  colspan="4"; style="text-align:center" >&nbsp;</td>            	
                          </tr>  
                          <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                              <td style="width:10%;" >&nbsp;</td>
                              <td class="det1" style="text-align:right; padding-right:30px;" >VAT No</td> 
                              <td class="det2" style="text-align:left;" ><input type="text" name="VAT" size="30" value="<?php echo $LoadGlnStoreTO->Vat ;?>"></td>                                     
                              <td style="width:10%;" >&nbsp;</td>
                          </tr>
                          <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                              <td  colspan="4"; style="text-align:center" >&nbsp;</td>            	
                          </tr>                 
                          </tr>  
                          <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                              <td>&nbsp;</td>
                              <td class="det1" style="text-align:right; padding-right:30px;" >Select Warehouse</td> 
                              <td>
                               <select name="WAREHOUSE" id="WAREHOUSE" size="1">
                                      <option value="Select Warehouse">Select Warehouse</option>
                                      <?php foreach($mfPR as $row) { ?>
                                           <option value="<?php echo trim($row['uid']); ?>"><?php echo trim($row['name']); ?></option>
                                      <?php } ?>
                                     </select>
                             </td>
                             <td>&nbsp;</td>
                          </tr>
                          <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                              <td  colspan="4"; style="text-align:center" >&nbsp;</td>
                          </tr>
                          <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                              <td>&nbsp;</td>
                              <td class="det1" style="text-align:right; padding-right:30px;" >Select Chain</td> 
                              <td>
                               <select name="CHAIN" id="CHAIN" size="1">
                                      <option value="Select Chain">Select Chain</option>
                                      <?php foreach($mChn as $row) { ?>
                                           <option value="<?php echo trim($row['uid']); ?>"><?php echo trim($row['description']); ?></option>
                                      <?php } ?>
                                     </select>
                             </td>
                             <td>&nbsp;</td>
                          </tr>
                          <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                              <td  colspan="4"; style="text-align:center" >&nbsp;</td>
                          </tr>
                          <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                              <td colspan="4"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="FINISH" value= "Create Store">
                                                                          <INPUT TYPE="submit" class="submit" name="BACK" value= "Back">
                              	                                          <INPUT TYPE="submit" class="submit" name="CANCEL" value= "Cancel"></td>
                          </tr>
                          <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                              <td  colspan="5"; style="text-align:center" >&nbsp;</td>
                          </tr>                          
                     </table>
	             </center>    	
               <?php 
            
        }
 
// ********************************************************************************************************************************	
    }

?>    