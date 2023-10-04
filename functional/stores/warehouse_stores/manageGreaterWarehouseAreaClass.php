<?php 
	include_once('ROOT.php'); 
  include_once($ROOT.'PHPINI.php');
  include_once($ROOT.$PHPFOLDER.'DAO/manageGreaterWarehouseAreaDAO.php');
  include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
  include_once($ROOT.$PHPFOLDER."libs/GUICommonUtils.php");
  
 	class manageGreaterWarehouseArea {
		
		 function __construct() {

         global $dbConn;
         $this->dbConn = $dbConn;
      }	
      
// ********************************************************************************************************************************

	public function firstForm() {
		
		?>
		<center>
       <FORM name='Manage Greater Warehouse Area' method=post action=''>
            <table width="720"; style="border:none">
            	<tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="4">&nbsp</td>
              </tr>
              <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td class="head1" colspan="4"; style="text-align:center; padding: 0 15px 0 20px ; "><strong>Manage Warehouse Delivery Area</td>
              </tr> 
              <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td style="width:10%; border:none;">&nbsp</td>
                 <td style="width:40%; border:none;">&nbsp</td>
                 <td style="width:40%; border:none;">&nbsp</td>
                 <td style="width:10%; border:none;">&nbsp</td>
              </tr>
              <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="4">&nbsp</td>
              </tr>
              <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
               	 <td >&nbsp</td>
	               <td class="head1" colspan="1"; style="text-align:right; padding: 0 15px 0 20px ; "><label class="label">Modify&nbsp;Area</label><input type="radio" name="MODWHAREA" onclick="javascript: submit()" value="MODIFY"></td>
	               <td class="head1" colspan="1"; style="text-align:right; padding: 0 20px 0 30px ; "><label class="label">Add&nbsp;Area</label><input type="radio" name="ADDWHAREA" onclick="javascript: submit()" value="ADD"></td>	
	               <td >&nbsp</td>
              </tr>
              <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="4">&nbsp</td>
              </tr>
              <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">  
                 <td Colspan="4">&nbsp</td>
              </tr>    
              <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="4">&nbsp</td>
              </tr>  
              <tr class="<?php echo GUICommonUtils::styleEO($class); ?>"> 
                 <td colspan="4"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="CANFORM"   value= "Cancel"></td>
              </tr>
              <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="4">&nbsp</td>
              </tr>
 			    	</table>
		   </form>
    </center>
	 <?php
	}
	
// ********************************************************************************************************************************

	public function addGreaterArea($depotID) {
		
		$manageGreaterWarehouseAreaDAO = new greaterWarehouseAreaDAO($this->dbConn);
    $depl = $manageGreaterWarehouseAreaDAO->getGreaterDeliveryArea($depotID);
    
    $manageGreaterWarehouseAreaDAO = new greaterWarehouseAreaDAO($this->dbConn);
    $dep2 = $manageGreaterWarehouseAreaDAO->getNDD();

    $class = 'odd';
		
		?>
	  <center>
       <form name='Manage Greater Warehouse Area' method=post action='' onload=''>
          <table width="720"; style="border:none">
              <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td style="width:10%; border:none;">&nbsp</td>
                 <td style="width:30%; border:none;">&nbsp</td>
                 <td style="width:50%; border:none;">&nbsp</td>
                 <td style="width:10%; border:none;">&nbsp</td>
              </tr>
              <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td class="head1" colspan="4"; style="text-align:center; padding: 0 15px 0 20px ; "><strong>Manage Warehouse Delivery Area</td>
              </tr> 
              <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="4">&nbsp</td>
              </tr>                    
              <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="1" >&nbsp</td>
                 <td class="head2" colspan="1" style="text-align:right; padding: 0 15px 0 20px ; ">Warehouse Delivery Area: </td>
                 <td Colspan="1" ><INPUT TYPE="TEXT" size="50" name="txtDELIVERYAREANAME" id="txtDELIVERYAREANAME" placeholder='Warehouse Delivery Area'></td>
                 <td Colspan="1" >&nbsp</td>
              </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="4">&nbsp</td>
              </tr>
              <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="1" >&nbsp</td>
                 <td class="head2" colspan="1" style="text-align:right; padding: 0 15px 0 20px ; ">Warehouse Area: </td>
                 <td Colspan="1" ><Select style="width:330px;" name="txtGREATERAREA" id="txtGREATERAREA" size="1"> 
                 											<option value="Select Warehouse Area"><?php echo 'Select Warehouse Area' ?></option>
                 											<?php foreach($depl as $drow) { ?>
                                           <option value="<?php echo trim($drow['WhUid']) . ' - ' .trim($drow['wh_area']); ?>"><?php echo $drow['wh_area']; ?></option>
                                   		<?php } ?>
                 							    </Select>
                 </td>
                 <td Colspan="1" >&nbsp</td>
              </tr>
              <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="4">&nbsp</td>
              </tr> 
              <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="1" >&nbsp</td>
                 <td class="head2" colspan="1" style="text-align:right; padding: 0 15px 0 20px ; ">NDD: </td>
                 <td Colspan="1" ><Select style="width:330px;" name="txtNDD" id="txtNDD" size="1"> 
                 											<option value="Select NDD"><?php echo 'Select NDD' ?></option>
                 											<?php foreach($dep2 as $arow) { ?>
                                           <option value="<?php echo trim($arow['dUID']) . '-' . trim($arow['name']); ?>"><?php echo $arow['name']; ?></option>
                                   		<?php } ?>
                 							    </Select>
                 <td Colspan="1" >&nbsp</td>
              </tr> 
              <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="4">&nbsp</td>
              </tr>
              <tr class="<?php echo GUICommonUtils::styleEO($class); ?>"> 
                 <td colspan="4"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="formADDAREA"   value= "Add Area">
                 																						 <INPUT TYPE="submit" class="submit" name="BACKFORM"   value= "Back">		
                                                             <INPUT TYPE="submit" class="submit" name="CANFORM"   value= "Cancel"></td>
              </tr>          
              <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="4">&nbsp</td>
              </tr>
          </table>
       </form>
    </center> 	
	 <?php

	}
	
	
	
// ********************************************************************************************************************************

	public function modifyWarehouseArea($postWhDeliveryArea, $postStat, $depotID){
		
		
		$manageGreaterWarehouseAreaDAO = new greaterWarehouseAreaDAO($this->dbConn);
		$WhDelAreaName = $manageGreaterWarehouseAreaDAO->searchWarehouseDeliveryArea($postWhDeliveryArea, $postStat, $depotID); 
		
		?> 
		 <center>
		 	<form name='Modify Warehouse Delivery Area' method=post target=''>
		 		<table width="720px"; style="border:none">
		 			<tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
              <td class="det1" colspan="5" style="text-align:center;">Warehouse Delivery Areas to Edit</td>
          </tr> 
          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
              <td width="10%"; style="border: none;">&nbsp;</td>
              <td width="50%"; style="border: none;">&nbsp;</td>
              <td width="18%"; style="border: none;">&nbsp;</td>
              <td width="18%"; style="border: none;">&nbsp;</td>
              <td width="4%";  style="border: none;">&nbsp;</td>
          </tr>
          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
              <td colspan="1" style="border: none;">&nbsp</td>
              <td class="det2" colspan="1" style="text-align:left; border: none; padding-left:5px;"><INPUT TYPE="submit" class="submit" name="SUBMITFILTER" value= "Submit Filter">&nbsp;
                                                     <INPUT TYPE="submit" class="submit" name="CLEARFILTER"  value= "Clear Filter"></td>
              <td class="head1" colspan="1" style="text-align:left; border: none; padding: 0px 5px 0px 5px;"><input type="radio" id="ACTIVE"  name="STATUS" value="A" CHECKED ><label class="label" for="ACTIVE">Active</label></td>
              <td class="head1" colspan="1" style="text-align:left; border: none; padding: 0px 5px 0px 5px;"><input type="radio" id="DELETED" name="STATUS" value="D"><label class="label" for="DELETED">Deleted</label></td>
              <td colspan="1" style="border: none;">&nbsp</td>
          </tr>
          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
              <td colspan="5" style="text-align:center;">&nbsp;</td>
          </tr>
          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
              <td colspan="1" >&nbsp</td>
              <td  class="det1" colspan="1" style="text-align:left; padding: 0px 5px 0px 5px;">Warehouse Delivery Area</td>
              <td  class="det1" colspan="1" style="text-align:center; padding: 0px 5px 0px 5px;">Select</td>
              <td  class="det1" colspan="1" style="text-align:right; padding: 0px 5px 0px 5px;">Status</td>
              <td colspan="1">&nbsp</td>
          </tr>
          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
              <td colspan="5" >&nbsp;</td>
          </tr>
          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
             <td colspan="1">&nbsp</td>
             <td class="det1" colspan="1" style="text-align:left; padding: 0px 5px 0px 5px;" ;><INPUT TYPE="TEXT" size="20" name="WHDELIVERYAREA"    value= "" ></td>
             <td colspan="3">&nbsp</td>
          </tr>
          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
             <td colspan="5" style="text-align:center;">&nbsp;</td>
          </tr>
          
          <!--Validation to check if warehouse area entered-->
          
          <?php
           if(count($WhDelAreaName) == 0) { ?>
             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td  colspan="1">&nbsp</td>
                 <td  class="det3" colspan= "3" style="text-align:left; color:Red;">No Warehouse Delivery Area Selected - Use filters</td>
                 <td  colspan="1">&nbsp</td>
             </tr> 
             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td  colspan="5" style="text-align:center;">&nbsp;</td>
             </tr>
           <?php     
           } else {         
                     
               foreach ($WhDelAreaName as $row) { 
                   if($row['status'] == "D") {
                     $depl = 'Deleted';
                   } else {
                     $depl = 'Active';
                   } ?>
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                       <td  colspan="1">&nbsp</td>
                       <td class="detN12" style="text-align:left;"><?php echo $row["wh_description"];?></td>
                       <td class="detN12" style="text-align:center;"><INPUT TYPE="radio" name="SELMOD" onclick="javascript: submit()" value= "<?php echo $row['wh_description'] . "-" . $row['uid'] . "$" . $row['wh_area'] . "&" . $row['status'] . "*" . $row["depot_uid"];?>"></td>
                       <td class="detN12" style="text-align:right;"><?php echo $depl; ?></td>
                       <td  colspan="1">&nbsp<input type="hidden" id="CURSTAT" name="CURSTAT" value="<?php echo $row['status'];?>"></td> 
                   </tr>
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                       <td  colspan="5" style="text-align:center;">&nbsp;</td>
                   </tr>
               <?php  
               }
           }?>
		 	  </table>
		 	</form>
		 </center>
		<?php

		
	}





// ********************************************************************************************************************************

	}
