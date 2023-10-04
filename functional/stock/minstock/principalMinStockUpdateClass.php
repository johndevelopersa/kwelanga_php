<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
 		include_once($ROOT.$PHPFOLDER.'DAO/updateStockDAO.php'); 
    include_once($ROOT.$PHPFOLDER."libs/GUICommonUtils.php");


class principalDepot {
	
      function __construct() {

         global $dbConn;
         $this->dbConn = $dbConn;
      }	
      
  // ********************************************************************************************************************************	
  
  public function firstform($userID, $principalId) 
  {
  	//echo $userID;
  	
  	$updateStockDAO = new updateStockDAO($this->dbConn);     
    $depl = $updateStockDAO->getUserWarehouses($userID, $principalId) ;
    
    $class = 'odd';
    
    
    ?>
    <center>
       <FORM name='Principal Depot Report' method=post action='' onload='setFocusWhselect()'>
            <table width="720"; style="border:none">
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                   <td Colspan="5">&nbsp</td>
               </tr>  
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                   <td class=head1 colspan="5"; style="text-align:center";>Select Required Warehouse</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                   <td width="5%";  style="border:none">&nbsp</td>
                   <td width="40%"; style="border:none">&nbsp</td>
                   <td width="40%"; style="border:none">&nbsp</td>
                   <td width="10%"; style="border:none">&nbsp</td>
                   <td width="5%";  style="border:none">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td >&nbsp</td>
                     <td class="det1" >Warehouse </td>
                     <td colspan="2"; style="text-align:left;">
                         <select name="WAREHOUSE" id="WAREHOUSE">
                             <option value="Select a Warehouse"><?php echo 'Select a Warehouse' ?></option>
                                   <?php foreach($depl as $drow) { ?>
                                           <option value="<?php echo trim($drow['warehouse_uid']) ."-". $drow['warehouse']; ?>"><?php echo $drow['warehouse']; ?></option>
                                   <?php } ?>
                          </select>
                     </td>             
                     <td>&nbsp</td>
                     
                     <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td Colspan="5">&nbsp</td>
                     
                      <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="firstform" value= "Get Stock List">
                                                             <INPUT TYPE="submit" class="submit" name="backform"   value= "Back">
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
  public function prodListForm($principalId, $depotId)  {
  	
         $updateStockDAO = new updateStockDAO($this->dbConn);     
         $tRep = $updateStockDAO->getMinimumStockQuantity($principalId, $depotId);
         
         $class = 'odd';
         if (count($tRep) > 0) 
         { ?>
               <center>
                  <form name='Minimum Stock Quantity' method=post action=''>
                      <table width="750"; style="border:none">
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            <td class=head1 Colspan="5"; style="text-align:center;">Manage Minimum Stock Quantity  -  <?php echo trim($tRep[0]['Warehouse']);?></td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            <td Colspan="5">&nbsp</td>
                        </tr>    	
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                           <td width="5%"; style="border:none">&nbsp</td>
                           <td width="20%"; style="border:none">&nbsp</td>
                           <td width="50%"; style="border:none">&nbsp</td>
                           <td width="20%"; style="border:none">&nbsp</td>
                           <td width="5%"; style="border:none">&nbsp;</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                           <td style="border:none; float: center;">&nbsp;</td>
                           <td class="det1" style="text-align:left;">Product Code</td>
                           <td class="det1" style="text-align:left;">Product Description</td>
                           <td class="det1" input type="text" style="text-align:right;">Minimum Quantity</td>
                           <td style="border:none; float: center;">&nbsp;</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            <td Colspan="5">&nbsp</td>
                        </tr> 
                        <?php 
                        foreach($tRep as $rr) { 
                        	
                        	?>
                            <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                <td style="text-align:left";>&nbsp;</td>
                                <td style="text-align:left";><?php echo $rr['Product Code'];?></td>
                                <td style="text-align:left";><?php echo $rr['Product Description'];?></td>
                                <td style="text-align:right;"><INPUT TYPE="text"  name= PRODLISTFORM[] size="5" value="<?php echo  trim($rr['Minimum Stock Quantity']); ?>";></td>
                                <td style="text-align:left";><input type="hidden" name=PRODLISTUID[]  value=<?php echo $rr['Stock_UID']; ?>></td>
                                
                            </tr>
                            <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                <td>&nbsp</td>
                                <td>&nbsp</td>
                                <td>&nbsp</td>
                                <td>&nbsp</td>
                                <td>&nbsp</td>
                            </tr>
                        <?php	
                        } ?>      	               

                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            <td Colspan="5" style="text-align:center";><INPUT TYPE="submit" class="submit" name="submit" value= "Submit">
                            																					 <INPUT TYPE="submit" class="submit" name="backform" value= "Back">
                                                                       <INPUT TYPE="submit" class="submit" name="canform"  value= "Cancel"></td>
                        </tr>               
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                           <td Colspan="5">&nbsp</td>
                         </tr>
                      </table>
                  </form>
               </center>

<?php
				 }					 
	}
	
	}
