<?php
    include_once('ROOT.php');
    include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER.'DAO/WarehouseAreaDao.php');
    include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');

class WarehouseAreaScreens {

      function __construct() {

         global $dbConn;
         $this->dbConn = $dbConn;
      }
      
      //***********************************************************************************************************
  //************************************************
public function pickUpdateAction() { ?>
             <center>
                <form name='Maintain Employee' method=post action=''>
                   <table width="720"; style="border:none">
                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            <td style="width:10%; border:none;">&nbsp</td>
                            <td style="width:40%; border:none;">&nbsp</td>
                            <td style="width:40%; border:none;">&nbsp</td>
                            <td style="width:10%; border:none;">&nbsp</td>
                       </tr>
                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            <td class="head1" colspan="4"; style="text-align:center; padding: 0 15px 0 20px ; ">Warehouse Area Maintenance</td>
                       </tr> 
                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            <td Colspan="4">&nbsp</td>
                       </tr>
                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            <td >&nbsp</td>
                            <td class="head1" colspan="1"; style="text-align:right; padding: 0 15px 0 20px ; "><label class="label" for="MODEMPSP">Modify&nbsp;Warehouse&nbsp;Area&nbsp;</label><input type="radio" name="MODEMPSP" onclick="javascript: submit()" value="MODIFY"></td>
                            <td class="head1" colspan="1"; style="text-align:right; padding: 0 20px 0 30px ; "><label class="label" for="ADDEMPSP">Add&nbsp;Warehouse&nbsp;Area&nbsp;</label><input type="radio" name="ADDEMPSP" onclick="javascript: submit()" value="ADD"></td>	
                            <td >&nbsp</td>    
                       </tr>
                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            <td Colspan="4">&nbsp;</td>
                       </tr>  
                       
                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                           <td Colspan="4">&nbsp</td>
                       </tr>  
                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                           <td Colspan="4">&nbsp</td>
                       </tr>  
                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>"> 
                           <td colspan="4"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="BACKFORM"   value= "Back">
                                                                       <INPUT TYPE="submit" class="submit" name="CANFORM"    value= "Cancel"></td>
                       </tr>          
                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="4">&nbsp</td>
                       </tr>
                   </table>
                </form>
             </center>   	   	

    <?php 
    }
  
  
  
  
    //*******************************************************************************************
   public function SearchArea($wareHouseCde) {
   	
   		?>



      <body  onload='setFocusToTextBoxF()'>
          <center>
              <FORM name='SELECT USER' method=post action=''>
                   <table width="720"; style="border:none">
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                      
                          <td class=head1 Colspan="5"; style="text-align:center">Search For Area To Modify</td>
                        </tr>
                        <tr>
                         
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td width="5%"; style="border:none">&nbsp</td>
                          <td width="30%"; style="border:none">&nbsp</td>
                          <td width="30%"; style="border:none">&nbsp</td>
                          <td width="30%"; style="border:none">&nbsp</td>
                          <td width="5%" ; style="border:none">&nbsp</td>
                        </tr>
                        
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        	<td Colspan="1">&nbsp</td>
                        	<td colspan="1"; style="text-align:centre">Search Area</td>
                          <td colspan="2"; style="text-align:centre";>&nbsp</td>
                         <td Colspan="5">&nbsp</td>
                          </tr>
                          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td colspan="1">&nbsp</td>
                          <td class="det1" colspan="2" style="text-align:left; padding: 0px 5px 0px 5px;" ;><INPUT TYPE="TEXT" size="20" name="TRUID"    value= "" ></td>
                          <td colspan="2">&nbsp</td>            
                         
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="SEARCH" value= "Search Area">&nbsp;&nbsp
                          	                                        
                          	                                          <INPUT TYPE="submit" class="submit" name="BACKFORM"   value= "Back"></td>
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
       //*******************************************************************************************

    public function ModifyWhAreaSelect($filtersearch,$wareHouseCde) { 
    	
 	    $WarehouseAreaDao = new WarehouseAreaDao($this->dbConn);
          $Area = $WarehouseAreaDao->getAreaDetails($filtersearch,$wareHouseCde);
 	
 	
 	
 	  ?>
 	  <body  onload='setFocusToTextBoxF()'>
          <center>
              <FORM name='SELECT USER' method=post action=''>
                   <table width="720"; style="border:none">
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td class=head1 Colspan="5"; style="text-align:center">Select Area</td>
                        </tr>
                        <tr>
                         
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
                        	<td colspan="1"; style="text-align:right">Select Area</td>
                          <td colspan="2"; style="text-align:left";>
                          	      	     <select name="EMPID" id="EMPID">
                                             <option value="Select Employee">Select Area</option>
                                             <?php foreach($Area as $row) { ?>
                                                   <option value="<?php echo trim($row['uid']) . " - " . trim($row['wh_area']); ?>"><?php echo trim($row['uid']) . " - " . trim($row['wh_area']); ?></option>
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
                          <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="MODIFYAREA" value= "Modify Area Details">&nbsp;&nbsp
                          	                                          
                          	                                          <INPUT TYPE="submit" class="submit" name="DELAREADETAIL"   value= "Delete Area">
                          	                                          
                          	                                          <INPUT TYPE="submit" class="submit" name="BACKFORM"   value= "BACK">
                          	                                          
                          	                                          </td>
                          	                                          
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
    //*******************************************************************************************
 public function ADDAreaScreen($userUId,$principalId){
 	 
 	 $WarehouseAreaDao = new WarehouseAreaDao($this->dbConn);
          $whDetails = $WarehouseAreaDao->WarehouseDet($userUId,$principalId);
 	?>
    	 <body>
          <center>
              <FORM name='Warehouse Area Manage' method=post action=''>
                   <table width="720"; style="border:none">
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td class=head1 Colspan="5"; style="text-align:center">Manage Area Details</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td width="5%"; style="border:none">&nbsp</td>
                          <td width="30%"; style="border:none">&nbsp</td>
                          <td width="30%"; style="border:none">&nbsp</td>
                          <td width="30%"; style="border:none">&nbsp</td>
                          <td width="3%" ; style="border:none">&nbsp</td>
                        </tr>
                        
                        <?php 
                    ?>     
                     <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                                 <td Colspan="1">&nbsp</td>
                                 <td colspan="1"; style="text-align:right; font-weight: bold;">Warehouse</td>
                                 <td colspan="1"; style="text-align:left;"><?php echo trim("Select Warehouse");?></td>
                                  <td>
                                       <select name="WAREHOUSE" id="WAREHOUSE" size="1">
                                           <option value="Change Warehouse">Change Warehouse</option>
                                                <?php foreach($whDetails as $row) { ?>
                                                          <option value="<?php echo trim($row['depot_id']); ?>"><?php echo trim($row['name']); ?></option>
                                                 <?php } ?>
                                       </select>
                                  </td>
                                  <td Colspan="1">&nbsp</td>
                            </tr>                   
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                        	<td Colspan="5">&nbsp</td> 
                         
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                        </tr>
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                        	<td Colspan="1">&nbsp</td>
                        	<td colspan="1"; style="text-align:right; font-weight: bold;">Area Name</td>
                          <td colspan="2"; style="text-align:left;"><input type="text" name="ANAME" value="<?php echo ""; ?>"></td>
                          <td colspan="1"; style="text-align:left">&nbsp;</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                        	<td Colspan="3">&nbsp</td>
                        	
                          <td colspan="2"; style="text-align:left">&nbsp;</td>
                        </tr>
                        <?php 
                        ?>      
                 
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                             <td Colspan="5">&nbsp</td>
                        </tr>
                       
                     
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                             <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="ADD" value= "Add Area ">
                                                                         <INPUT TYPE="submit" class="submit" name="BACKFORM"   value= "Back"></td>
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
          function setFocusToTextBoxF() {
             document.getElementById("EMPID").focus();
          }
      </script>
    <?php
 	}



    //*******************************************************************************************
    public function ModifyAreaScreen($areaUID,$wareHouseCde,$userUId,$principalId){

$WarehouseAreaDao = new WarehouseAreaDao($this->dbConn);
          $AreaDET = $WarehouseAreaDao->ModifyAreaDetails($areaUID);

   $warehouse= $AreaDET[0]['depot_uid'];
   $warehousename= $AreaDET[0]['name'];
   $AreaName= $AreaDET[0]['wh_area'];
   $DPName= $AreaDET[0]['name'];
 
  $WarehouseAreaDao = new WarehouseAreaDao($this->dbConn);
          $whDetails = $WarehouseAreaDao->WarehouseDet($userUId,$principalId);

   
   
    	?>
    	 <body>
          <center>
              <FORM name='Warehouse Area Manage' method=post action=''>
                   <table width="720"; style="border:none">
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td class=head1 Colspan="5"; style="text-align:center">Manage Area Details</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td width="5%"; style="border:none">&nbsp</td>
                          <td width="30%"; style="border:none">&nbsp</td>
                          <td width="30%"; style="border:none">&nbsp</td>
                          <td width="30%"; style="border:none">&nbsp</td>
                          <td width="3%" ; style="border:none">&nbsp</td>
                        </tr>
                        
                        <?php 
                    ?>     
                     <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                                 <td Colspan="1">&nbsp</td>
                                 <td colspan="1"; style="text-align:right; font-weight: bold;">Warehouse</td>
                                 <td colspan="1"; style="text-align:left;"><?php echo trim($warehousename);?></td>
                                  <td>
                                       <select name="WAREHOUSE" id="WAREHOUSE" size="1">
                                           <option value="Change Warehouse">Change Warehouse</option>
                                                <?php foreach($whDetails as $row) { ?>
                                                          <option value="<?php echo trim($row['depot_id']); ?>"><?php echo trim($row['name']); ?></option>
                                                 <?php } ?>
                                       </select>
                                  </td>
                                  <td Colspan="1">&nbsp</td>
                            </tr>                   
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                        	<td Colspan="1">&nbsp</td>
                        	<td colspan="1"; style="text-align:right; font-weight: bold;">Area Code</td> 
                        	 <td colspan="2"; style="text-align:left;"><?php echo $areaUID; ?></td>
                        	 	<input type="hidden" name="AC" value=<?php echo mysqli_real_escape_string($this->dbConn->connection, $areaUID); ?>>
                        	<?php
                        	
                        	 ?>
                          <td colspan="1"; style="text-align:left">&nbsp;</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                        	<td Colspan="1">&nbsp</td>
                        	<td colspan="1"; style="text-align:right; font-weight: bold;">Area Name</td>
                          <td colspan="2"; style="text-align:left;"><input type="text" name="ANAME" value="<?php echo $AreaName; ?>"></td>
                          <td colspan="1"; style="text-align:left">&nbsp;</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                        </tr>
                        
                        <?php 
                        ?>      
                 
                        
                        
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                             <td colspan="5"; style="text-align:center;">&nbsp;</td>
                        </tr>
                       
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                             <td colspan="5"; style="text-align:center;">&nbsp;</td>
                        </tr>
                      
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                             <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="SUBEMPUPD" value= "Submit Details ">
                                                                         <INPUT TYPE="submit" class="submit" name="BACKFORM"   value= "Back"></td>
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
          function setFocusToTextBoxF() {
             document.getElementById("EMPID").focus();
          }
      </script>
    <?php
    	}
    	
//********************************************************************************************************************************************************************    	
    public function pickUpdateActionDel() { ?>
             <center>
                <form name='Maintain Employee' method=post action=''>
                   <table width="720"; style="border:none">
                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            <td style="width:10%; border:none;">&nbsp</td>
                            <td style="width:40%; border:none;">&nbsp</td>
                            <td style="width:40%; border:none;">&nbsp</td>
                            <td style="width:10%; border:none;">&nbsp</td>
                       </tr>
                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            <td class="head1" colspan="4"; style="text-align:center; padding: 0 15px 0 20px ; ">Delivery Area Maintenance</td>
                       </tr> 
                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            <td Colspan="4">&nbsp</td>
                       </tr>
                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            <td >&nbsp</td>
                            <td class="head1" colspan="1"; style="text-align:right; padding: 0 15px 0 20px ; "><label class="label" for="MODEMPSPD">Modify&nbsp;Delivery&nbsp;Area&nbsp;</label><input type="radio" name="MODEMPSPD" onclick="javascript: submit()" value="MODIFY"></td>
                            <td class="head1" colspan="1"; style="text-align:right; padding: 0 20px 0 30px ; "><label class="label" for="ADDEMPSPD">Add&nbsp;Delivery&nbsp;Area&nbsp;</label><input type="radio" name="ADDEMPSPD" onclick="javascript: submit()" value="ADD"></td>	
                            <td >&nbsp</td>    
                       </tr>
                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            <td Colspan="4">&nbsp;</td>
                       </tr>  
                       
                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                           <td Colspan="4">&nbsp</td>
                       </tr>  
                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                           <td Colspan="4">&nbsp</td>
                       </tr>  
                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>"> 
                           <td colspan="4"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="BACKFORM"   value= "Back">
                                                                       <INPUT TYPE="submit" class="submit" name="CANFORM"    value= "Cancel"></td>
                       </tr>          
                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="4">&nbsp</td>
                       </tr>
                   </table>
                </form>
             </center>   	   	

    <?php 
    }
  
  //*****************************************************************************************************************************************************************
  public function ADDAreaScreenDel($userUId,$principalId,$wareHouseCde){
 	 
 	 
 	  $WarehouseAreaDao = new WarehouseAreaDao($this->dbConn);
          $whDetails = $WarehouseAreaDao->WarehouseAreaDet($wareHouseCde);
 	?>
    	 <body>
          <center>
              <FORM name='Warehouse Area Manage' method=post action=''>
                   <table width="720"; style="border:none">
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td class=head1 Colspan="5"; style="text-align:center">Add Delivery Area</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td width="5%"; style="border:none">&nbsp</td>
                          <td width="30%"; style="border:none">&nbsp</td>
                          <td width="30%"; style="border:none">&nbsp</td>
                          <td width="30%"; style="border:none">&nbsp</td>
                          <td width="3%" ; style="border:none">&nbsp</td>
                        </tr>
                        
                        <?php 
                    ?>     
                     <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                                 <td Colspan="1">&nbsp</td>
                                 <td colspan="1"; style="text-align:right; font-weight: bold;">Warehouse Area</td>
                                 <td colspan="1"; style="text-align:left;"><?php echo trim("Select Warehouse Area");?></td>
                                  <td>
                                       <select name="WAREHOUSE" id="WAREHOUSE" size="1">
                                           <option value="Change Warehouse">Change Warehouse</option>
                                                <?php foreach($whDetails as $row) { ?>
                                                          <option value="<?php echo trim($row['uid']); ?>"><?php echo trim($row['wh_area']); ?></option>
                                                 <?php } ?>
                                       </select>
                                  </td>
                                  <td Colspan="1">&nbsp</td>
                            </tr>                   
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                        	<td Colspan="5">&nbsp</td> 
                         
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                        </tr>
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                        	<td Colspan="1">&nbsp</td>
                        	<td colspan="1"; style="text-align:right; font-weight: bold;">Area Name</td>
                          <td colspan="2"; style="text-align:left;"><input type="text" name="ANAME" value="<?php echo ""; ?>"></td>
                          <td colspan="1"; style="text-align:left">&nbsp;</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                        	<td Colspan="3">&nbsp</td>
                        	
                          <td colspan="2"; style="text-align:left">&nbsp;</td>
                        </tr>
                        <?php 
                        ?>      
                 
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                             <td Colspan="5">&nbsp</td>
                        </tr>
                       
                     
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                             <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="ADDD" value= "Add Area ">
                                                                         <INPUT TYPE="submit" class="submit" name="BACKFORM"   value= "Back"></td>
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
          function setFocusToTextBoxF() {
             document.getElementById("EMPID").focus();
          }
      </script>
    <?php
 	}
  //*****************************************************************************************************************************************************************
  
    public function ModifyDelAreaSelect($filtersearch,$wareHouseCde) { 
    	
 	    $WarehouseAreaDao = new WarehouseAreaDao($this->dbConn);
          $Area = $WarehouseAreaDao->getDelAreaDetails($filtersearch,$wareHouseCde);
 	
 	
 	
 	  ?>
 	  <body  onload='setFocusToTextBoxF()'>
          <center>
              <FORM name='SELECT USER' method=post action=''>
                   <table width="720"; style="border:none">
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td class=head1 Colspan="5"; style="text-align:center">Select Area</td>
                        </tr>
                        <tr>
                         
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
                        	<td colspan="1"; style="text-align:right">Select Area</td>
                          <td colspan="2"; style="text-align:left";>
                          	      	     <select name="EMPID" id="EMPID">
                                             <option value="Select Employee">Select Area</option>
                                             <?php foreach($Area as $row) { ?>
                                                   <option value="<?php echo trim($row['uid']) . " - " . trim($row['wh_description']); ?>"><?php echo trim($row['uid']) . " - " . trim($row['wh_description']); ?></option>
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
                          <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="MODIFYDELAREA" value= "Modify Area Details">&nbsp;&nbsp
                          	                                          
                          	                                          <INPUT TYPE="submit" class="submit" name="DELDAREADETAIL"   value= "Delete Area">
                          	                                          
                          	                                          <INPUT TYPE="submit" class="submit" name="BACKFORM"   value= "BACK">
                          	                                          
                          	                                          </td>
                          	                                          
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
    //*******************************
    public function SearchDelArea($wareHouseCde) {
   	
   		?>



      <body  onload='setFocusToTextBoxF()'>
          <center>
              <FORM name='SELECT USER' method=post action=''>
                   <table width="720"; style="border:none">
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                      
                          <td class=head1 Colspan="5"; style="text-align:center">Search For Del Area To Modify</td>
                        </tr>
                        <tr>
                         
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td width="5%"; style="border:none">&nbsp</td>
                          <td width="30%"; style="border:none">&nbsp</td>
                          <td width="30%"; style="border:none">&nbsp</td>
                          <td width="30%"; style="border:none">&nbsp</td>
                          <td width="5%" ; style="border:none">&nbsp</td>
                        </tr>
                        
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        	<td Colspan="1">&nbsp</td>
                        	<td colspan="1"; style="text-align:centre">Search Area</td>
                          <td colspan="2"; style="text-align:centre";>&nbsp</td>
                         <td Colspan="5">&nbsp</td>
                          </tr>
                          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td colspan="1">&nbsp</td>
                          <td class="det1" colspan="2" style="text-align:left; padding: 0px 5px 0px 5px;" ;><INPUT TYPE="TEXT" size="20" name="TRUID"    value= "" ></td>
                          <td colspan="2">&nbsp</td>            
                         
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="SEARCHD" value= "Search Area">&nbsp;&nbsp
                          	                                        
                          	                                          <INPUT TYPE="submit" class="submit" name="BACKFORM"   value= "Back"></td>
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
    //**********************
    public function ModifyDelAreaScreen($areaUID,$wareHouseCde,$userUId,$principalId){

$WarehouseAreaDao = new WarehouseAreaDao($this->dbConn);
          $AreaDET = $WarehouseAreaDao->ModifyDelAreaDetails($areaUID);

  
   $warehouseAreaname= $AreaDET[0]['wh_area'];
   $AreaName= $AreaDET[0]['wh_description'];
   
   
  $WarehouseAreaDao = new WarehouseAreaDao($this->dbConn);
          $whDetails = $WarehouseAreaDao->WarehouseAreaDet($wareHouseCde);

   
   
    	?>
    	 <body>
          <center>
              <FORM name='Warehouse Area Manage' method=post action=''>
                   <table width="720"; style="border:none">
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td class=head1 Colspan="5"; style="text-align:center">Manage Delivery Area Details</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td width="5%"; style="border:none">&nbsp</td>
                          <td width="30%"; style="border:none">&nbsp</td>
                          <td width="30%"; style="border:none">&nbsp</td>
                          <td width="30%"; style="border:none">&nbsp</td>
                          <td width="3%" ; style="border:none">&nbsp</td>
                        </tr>
                        
                        <?php 
                    ?>     
                     <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                                 <td Colspan="1">&nbsp</td>
                                 <td colspan="1"; style="text-align:right; font-weight: bold;">Warehouse Area</td>
                                 <td colspan="1"; style="text-align:left;"><?php echo trim($warehouseAreaname);?></td>
                                  <td>
                                       <select name="WAREHOUSE" id="WAREHOUSE" size="1">
                                           <option value="Change Warehouse">Change Warehouse Area</option>
                                                <?php foreach($whDetails as $row) { ?>
                                                          <option value="<?php echo trim($row['uid']); ?>"><?php echo trim($row['wh_area']); ?></option>
                                                 <?php } ?>
                                       </select>
                                  </td>
                                  <td Colspan="1">&nbsp</td>
                            </tr>                   
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                        	<td Colspan="1">&nbsp</td>
                        	<td colspan="1"; style="text-align:right; font-weight: bold;">Area Code</td> 
                        	 <td colspan="2"; style="text-align:left;"><?php echo $areaUID; ?></td>
                        	 	<input type="hidden" name="AC" value=<?php echo mysqli_real_escape_string($this->dbConn->connection, $areaUID); ?>>
                        	<?php
                        	
                        	 ?>
                          <td colspan="1"; style="text-align:left">&nbsp;</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                        	<td Colspan="1">&nbsp</td>
                        	<td colspan="1"; style="text-align:right; font-weight: bold;">Area Name</td>
                          <td colspan="2"; style="text-align:left;"><input type="text" name="ANAME" value="<?php echo $AreaName; ?>"></td>
                          <td colspan="1"; style="text-align:left">&nbsp;</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                        </tr>
                        
                        <?php 
                        ?>      
                 
                        
                        
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                             <td colspan="5"; style="text-align:center;">&nbsp;</td>
                        </tr>
                       
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                             <td colspan="5"; style="text-align:center;">&nbsp;</td>
                        </tr>
                      
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                             <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="SUBEMPUPDD" value= "Submit Details ">
                                                                         <INPUT TYPE="submit" class="submit" name="BACKFORM"   value= "Back"></td>
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
          function setFocusToTextBoxF() {
             document.getElementById("EMPID").focus();
          }
      </script>
    <?php
    	}
    
    
  }