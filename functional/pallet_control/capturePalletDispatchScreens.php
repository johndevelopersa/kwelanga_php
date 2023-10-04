<?php
    include_once('ROOT.php'); 
    include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER.'DAO/EmployeeDAO.php');
    include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
    //($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
 
class capturePalletDispatchScreens {
	
      function __construct() {

         global $dbConn;
         $this->dbConn = $dbConn;
      }	

// ********************************************************************************************************************************      
      
   public function selectWarehouse($userId, $prin) { 
   	
       $EmployeeDAO = new EmployeeDAO($this->dbConn); 
       $whDetails  = $EmployeeDAO->selectUserWarehouse($userId, $prin);
       
       if(count($whDetails) == 0) { ?>
            <script> alert("You have no warehouses - Problem")</script>
            <?php
            return;    	
       }
   	   ?>
       <body>
          <center>
              <FORM name='Capture Pallet Dispatch' method=post action=''>
                   <table width="720"; style="border:none">
                        <tr>
                          <td class=head1 Colspan="5"; style="text-align:center">Set Active Warehouse</td>
                        </tr>
                        <tr>
                          <td>&nbsp</td>
                        </tr>	        	
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td width="5%"; style="border:none">&nbsp</td>
                          <td width="30%"; style="border:none">&nbsp</td>
                          <td width="30%"; style="border:none">&nbsp</td>
                          <td width="30%"; style="border:none">&nbsp</td>
                          <td width="3%" ; style="border:none">&nbsp</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                        	<td Colspan="1">&nbsp</td>
                        	<td colspan="1"; style="text-align:right">Select Warehouse</td>
                          <td colspan="2"; style="text-align:left";> 
                          	           <select name="WHID" id="WHID">
                                             <option value="Select Warehouse">Select Warehouse</option>
                                             <?php foreach($whDetails as $row) { ?>
                                                   <option value="<?php echo $row['WhUid'] . "-" .  $row['pallet_depot'] . "$" .  $row['pallet_principal']  ; ?>"><?php echo trim($row['WhUid']) . " - " . trim($row['Warehouse']); ?></option>
                                             <?php
                                             } ?>
                                        </select></td>
                          <td colspan="1"; style="text-align:left"></td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="SETWH" value= "Filter on Name">&nbsp;&nbsp<INPUT TYPE="submit" class="submit" name="CODEFILTER" value= "Filter on Emp. Code"></td>
                        </tr>          
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                         </tr>  
                   </table>
              </FORM>
          </center>
      </body>
      <?php  	
   }      
// ********************************************************************************************************************************      
      
   public function firstform() {
      ?>
      <body  onload='setFocusToTextBoxF()'>
          <center>
              <FORM name='Employee recording' method=post action=''>
                   <table width="720"; style="border:none">
                        <tr>
                          <td class=head1 Colspan="5"; style="text-align:center">Capture Pallet Dispatch</td>
                        </tr>
                        <tr>
                          <td>&nbsp</td>
                        </tr>	        	
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td width="5%"; style="border:none">&nbsp</td>
                          <td width="30%"; style="border:none">&nbsp</td>
                          <td width="30%"; style="border:none">&nbsp</td>
                          <td width="30%"; style="border:none">&nbsp</td>
                          <td width="3%" ; style="border:none">&nbsp</td>
                        </tr>

                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                        </tr>
                        
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            <td Colspan="2">&nbsp</td> 	
                            <td colspan="1"><span><input type="radio" id="TLIST" name="DDISPAT" value="TRANSPORTER" CHECKED></span>
                   	                                      <span	style="font-weight: bold; text-align: center; padding-left : 20px;">Transporter List</span></td>
                            <td colspan="1"><span><input type="radio" id="STLIST" name="DDISPAT" value="STORE"></span>
                   	               <span style="font-weight: bold; text-align: center; padding-left : 20px;">Customer List</span></td></td>
                            <td colspan="1"></td>
                        </tr>

                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                        </tr>

                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                        	<td Colspan="1">&nbsp</td>
                        	<td colspan="1"; style="text-align:right">Find Transporter/Customer By Name</td>
                          <td colspan="2"; style="text-align:left";><input type="text" name="UVALUE" id="UVALUE" size="50" value="" placeholder="Search by (Part) Transporter/Customer"></td>
                          <td colspan="1"; style="text-align:left"></td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="NAMEFILTER" value= "Filter on Name"></td>
                        </tr>          
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                         </tr>  
                   </table>
              </FORM>
          </center>
      </body>
      <script type="text/javascript">           
          function setFocusToTextBoxN() {              
             document.getElementById("UVALUE").focus();           
          }        
      </script>
    <?php 
   }
// ********************************************************************************************************************************      

   public function SelectDispatch($empDetails, $ddispatchType) { 
   	
   	  if($ddispatchType = 'TRANSPORTER') {
   	  	    $stypeNme = 'Transporter';
   	  } else {
   	  	    $stypeNme = 'Customer';
   	  } ?>
   	
      <body  onload='setFocusToTextBoxF()'>
          <center>
              <FORM name='Pallet Dispatch Control' method=post action=''>
                   <table width="720"; style="border:none">
                        <tr>
                          <td class=head1 Colspan="5"; style="text-align:center"><?php echo 'Select ' . $stypeNme; ?></td>
                        </tr>
                        <tr>
                          <td>&nbsp</td>
                        </tr>	        	
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td width="5%"; style="border:none">&nbsp</td>
                          <td width="30%"; style="border:none">&nbsp</td>
                          <td width="30%"; style="border:none">&nbsp</td>
                          <td width="30%"; style="border:none">&nbsp</td>
                          <td width="3%" ; style="border:none">&nbsp</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                        	<td Colspan="1">&nbsp</td>
                        	<td colspan="1"; style="text-align:right"><?php echo 'Select ' . $stypeNme; ?></td>
                          <td colspan="2"; style="text-align:left";>
                          	      	     <select name="DISID" id="DISID">
                                             <option value="DISTYPE"><?php echo 'Select ' . $stypeNme; ?></option>
                                             <?php foreach($empDetails as $row) { ?>
                                                   <option value="<?php echo $row['uid'] . "-" . trim($row['name']); ?>"><?php echo trim($row['uid']) . " - " . trim($row['name']); ?></option>
                                             <?php
                                             } ?>
                                        </select>
                                        </td>
                          <td colspan="1"; style="text-align:left"></td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="GETDISPATCH" value= "<?php echo 'Get ' . $stypeNme; ?>"></td>
                        </tr>          
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5"><input type="hidden" name="DTYPE" value=<?php echo mysqli_real_escape_string($this->dbConn->connection, $ddispatchType); ?></td>
                         </tr>  
                   </table>
              </FORM>
          </center>
      </body>

    <?php 
   }
// ********************************************************************************************************************************      
   public function capturePalletDispatch($recName, $recUid, $recType) {
   	  if($recType == "TRANSPORTER") {
          $hedName = 'Transporter';
   	  } else {
          $hedName = 'Customer';
   	  }
      ?>
      <body>
          <center>
              <FORM name='Pallet Dispatch Capture' method=post action=''>
                   <table width="720"; style="border:none">
                        <tr>
                          <td class=head1 Colspan="5"; style="text-align:center"><?php echo 'Capture ' . $hedName . ' Dispatch' ?></td>
                        </tr>
                        <tr>
                          <td>&nbsp</td>
                        </tr>	        	
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td width="5%"; style="border:none">&nbsp</td>
                          <td width="30%"; style="border:none">&nbsp</td>
                          <td width="30%"; style="border:none">&nbsp</td>
                          <td width="30%"; style="border:none">&nbsp</td>
                          <td width="3%" ; style="border:none">&nbsp</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                        	<td Colspan="1">&nbsp</td>
                        	<td colspan="1"; style="text-align:right; font-weight: bold;"><?php echo trim($hedName); ?></td>
                          <td colspan="2"; style="text-align:left";><?php echo trim($recUid) .   "   -   "    . trim($recName);?></td>
                          <td colspan="1"; style="text-align:left"><input type="hidden" name="EMDID" value=<?php echo mysqli_real_escape_string($this->dbConn->connection, $recUid); ?>>
                          	                                       <input type="hidden" name="RECPTID" value=<?php echo mysqli_real_escape_string($this->dbConn->connection, $recType); ?>></td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                        	<td Colspan="1">&nbsp</td>
                        	<td colspan="1"; style="text-align:right; font-weight: bold;">Time of Day </td>
                          <td colspan="2"; style="text-align:left";><?php echo date("Y-m-d H:i");?></td>
                          <td colspan="1"; style="text-align:left"><input type="hidden" name="CTIME" value='<?php echo date("Y-m-d H:i"); ?>'</td>
                        </tr>
                        </tr>
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td colspan="5"; style="text-align:center;">&nbsp;</td>
                        </tr>   
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                        	<td Colspan="1">&nbsp</td>
                        	<td colspan="1"; style="text-align:right; font-weight: bold;">Enter Trip Sheet Numbers</td>
                          <td colspan="2"; style="text-align:left;"><input type="text" name="STRIPSHEET" value="<?php echo $sTripsheet; ?>"</td>
                          <td Colspan="1" class="tooltip" style="text-align:left;"><span><IMG src= <?php echo HOST_KOSSERVER_PHPFOLDER_AS_USER . "images/info-icon-small.png";?>></span><span class="tooltiptext">Tip<br>More than one Trip Sheet?<br>Seperated the Tripsheet Numbers<br>with a comma (,)</span></td>
                        </tr>                              
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td colspan="5"; style="text-align:center;">&nbsp;</td>
                        </tr>  
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                        	<td Colspan="1">&nbsp</td>
                        	<td colspan="1"; style="text-align:right; font-weight: bold;">Comments</td>
                          <td colspan="2"; style="text-align:left;"><input type="text" name="SCOMMENT" value="<?php echo $sReference; ?>"></td>
                          <td Colspan="1">&nbsp</td>
                        </tr> 
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td colspan="5"; style="text-align:center;">&nbsp;</td>
                        </tr>   
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                        	<td Colspan="1">&nbsp</td>
                        	<td colspan="1"; style="text-align:right; font-weight: bold;">Number Of Chep Pallets </td>
                          <td colspan="2"; style="text-align:left;"><input type="number" name="NOPALLETS" MIN=0 value="<?php echo $palletQty; ?>"></td>
                          <td Colspan="1">&nbsp</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td colspan="5"; style="text-align:center;">&nbsp;</td>
                        </tr>   
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                        	<td Colspan="1">&nbsp</td>
                        	<td colspan="1"; style="text-align:right; font-weight: bold;">Transporter Pallet Balance</td>
                          <td colspan="2"; style="text-align:left;><?php echo 'Pallet Balance'; ?>></td>
                          <td Colspan="1">&nbsp</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td colspan="5"; style="text-align:center;">&nbsp;</td>
                        </tr>   
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td colspan="5"; style="text-align:center;">&nbsp;</td>
                        </tr>   
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="SUBDET" value= "Submit Details "></td>
                        </tr>                         
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td colspan="5"; style="text-align:center;">&nbsp;</td>
                        </tr>                
         
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                         </tr>  
                   </table>
              </FORM>
          </center>
      </body>
      <script type="text/javascript">           
          function setFocusToTextBoxN() {              
             document.getElementById("UVALUE").focus();           
          }        
      </script>
    <?php 
   }
// ********************************************************************************************************************************      


}


