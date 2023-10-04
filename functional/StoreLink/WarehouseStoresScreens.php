<?php
    include_once('ROOT.php');
    include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER.'DAO/WarehouseStoresDAO.php');
    include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');

class WarehouseStoresScreens {

      function __construct() {

         global $dbConn;
         $this->dbConn = $dbConn;
      }
      
      //***********************************************************************************************************
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
                            <td class="head1" colspan="4"; style="text-align:center; padding: 0 15px 0 20px ; ">Warehouse Store Maintenance</td>
                       </tr> 
                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            <td Colspan="4">&nbsp</td>
                       </tr>
                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            <td >&nbsp</td>
                            <td class="head1" colspan="1"; style="text-align:right; padding: 0 15px 0 20px ; "><label class="label" for="MODEMPSP">Modify&nbsp;Warehouse&nbsp;Store&nbsp;</label><input type="radio" name="MODEMPSP" onclick="javascript: submit()" value="MODIFY"></td>
                            <td class="head1" colspan="1"; style="text-align:right; padding: 0 20px 0 30px ; "><label class="label" for="ADDEMPSP">Add&nbsp;Warehouse&nbsp;Store&nbsp;</label><input type="radio" name="ADDEMPSP" onclick="javascript: submit()" value="ADD"></td>	
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
   //Seach for store***********************************************************************************************************
    public function SearchStores($wareHouseCde) {
   		?>



      <body  onload='setFocusToTextBoxF()'>
          <center>
              <FORM name='SELECT USER' method=post action=''>
                   <table width="720"; style="border:none">
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                      
                          <td class=head1 Colspan="5"; style="text-align:center">Search For Store To Modify</td>
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
                        	<td colspan="1"; style="text-align:centre">Search Stores :</td>
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
                          <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="SEARCH" value= "Search Stores">&nbsp;&nbsp
                          	                                        
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
   //Select Store*********************************************************************************************************** 
 public function ModifyWhAreaSelect($filtersearch,$wareHouseCde) { 
   
 	    $WarehouseStoreDao = new WarehouseStoreDao($this->dbConn);
          $Area = $WarehouseStoreDao->getStoreDetails($filtersearch,$wareHouseCde);
 	
 	
 	
 	  ?>
 	  <body  onload='setFocusToTextBoxF()'>
          <center>
              <FORM name='SELECT USER' method=post action=''>
                   <table width="720"; style="border:none">
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td class=head1 Colspan="5"; style="text-align:center">Select Store</td>
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
                        	<td colspan="1"; style="text-align:right">Select Store</td>
                          <td colspan="2"; style="text-align:left";>
                          	      	     <select name="EMPID" id="EMPID">
                                             <option value="Select Store">Select Store</option>
                                             <?php foreach($Area as $row) { ?>
                                                   <option value="<?php echo trim($row['uid']) . " - " . trim($row['del_point_name']); ?>"><?php echo trim($row['uid']) . " - " . trim($row['del_point_name']); ?></option>
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
                          <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="MODIFYSTORE" value= "Modify Store Details">&nbsp;&nbsp
                          	                                          
                          	                                          <INPUT TYPE="submit" class="submit" name="DELSTORE"   value= "Delete Store">
                          	                                          
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
    
  //*************************************************************************************************************************************************************  
        public function ModifyStoreScreen($UID,$userUId,$principalId,$wareHouseCde){

$WarehouseStoreDao = new WarehouseStoreDao($this->dbConn);
          $Store = $WarehouseStoreDao->getStore($UID);
   $Warehouse= $Store[0]['depot_uid'];
   $DepotName= $Store[0]['name'];
   $Branch= $Store[0]['branch'];
   $warehouseArea= $Store[0]['wh_delivery_area'];
   $DelArea= $Store[0]['delivery_area'];
   $DelAreaName= $Store[0]['wh_description'];
   $StoreName= $Store[0]['del_point_name'];
   $Address1= $Store[0]['add1'];
   $Address2= $Store[0]['add2'];
   $Address3= $Store[0]['add3'];
   $Lat= $Store[0]['latitude'];
   $Long= $Store[0]['longitude'];
   $Gps= $Store[0]['gps_co_ords'];
   $Status= $Store[0]['status'];
   $Gln= $Store[0]['gln'];
   $Ndd= $Store[0]['ndd'];
   $Nod= $Store[0]['nod'];
   
 
  $WarehouseStoreDao = new WarehouseStoreDao($this->dbConn);
          $whDetails = $WarehouseStoreDao->DelAreaDet($wareHouseCde);

   $NODDD=array(" ","1 - Monday","2 - Tuesday","3 - Wednesday","4 - Thursday","5 - Friday","6 - Saturday","7 - Sunday","8 - Unknown"); 
   $nood= $NODDD[$Nod];
   $nddd= $NODDD[$Ndd];
    	?>
    	 <body>
          <center>
              <FORM name='Warehouse Area Manage' method=post action=''>
                   <table width="720"; style="border:none">
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td class=head1 Colspan="5"; style="text-align:center">Manage Store Details</td>
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
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                        	<td Colspan="1">&nbsp</td>
                        	<td colspan="1"; style="text-align:right; font-weight: bold;">Store Name :</td>
                          <td colspan="2"; style="text-align:left;"><input type="text" name="NNAME" value="<?php echo $StoreName; ?>"></td>
                          <td colspan="1"; style="text-align:left">&nbsp;</td>
                        </tr>
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                        	<td Colspan="1">&nbsp</td>
                        	<td colspan="1"; style="text-align:right; font-weight: bold;">Store Address :</td>
                          <td colspan="2"; style="text-align:left;"><input type="text" name="NADD1" value="<?php echo $Address1; ?>"></td>
                          <td colspan="1"; style="text-align:left">&nbsp;</td>
                        </tr>
                        
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                        	<td Colspan="1">&nbsp</td>
                        	<td colspan="1"; style="text-align:right; font-weight: bold;">&nbsp</td>
                          <td colspan="2"; style="text-align:left;"><input type="text" name="NADD2" value="<?php echo $Address2; ?>"></td>
                          
                          <td colspan="1"; style="text-align:left">&nbsp;</td>
                        </tr>
                        
                         </tr>
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                        	<td Colspan="1">&nbsp</td>
                        	<td colspan="1"; style="text-align:right; font-weight: bold;">&nbsp</td>
                           	<td colspan="2"; style="text-align:left;"><input type="text" name="NADD3" value="<?php echo $Address3; ?>"></td>
                          
                          <td colspan="1"; style="text-align:left">&nbsp;</td>
                        </tr>
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                        </tr>
                     <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                                 <td Colspan="1">&nbsp</td>
                                 <td colspan="1"; style="text-align:right; font-weight: bold;">Delivery Area :</td>
                                 <td colspan="1"; style="text-align:left;"><?php echo trim($DelAreaName);?></td>
                                 
                                  <td>
                                       <select name="DEL" id="DEL" size="1">
                                            <option value="<?php echo $DelArea," - ",$DelAreaName; ?>"><?php echo $DelArea," - ",$DelAreaName; ?></option> 
                                                <?php foreach($whDetails as $row) { ?>
                                                          <option value="<?php echo trim($row['uid']) . " - " . trim($row['wh_description']); ?>"><?php echo trim($row['uid']) . " - " . trim($row['wh_description']); ?></option>
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
                        	<td colspan="1"; style="text-align:right; font-weight: bold;">Store Branch Code</td> 
                        	 
                        	 	<td colspan="2"; style="text-align:left;"><input type="text" name="NBRANCH" value="<?php echo $Branch; ?>"></td>
                        	
                          <td colspan="1"; style="text-align:left">&nbsp;</td>
                        </tr>
                     
                        
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                        	<td Colspan="4">&nbsp</td>
                        	
                        	 	<input type="hidden" name="UID" value=<?php echo mysqli_real_escape_string($this->dbConn->connection, $UID); ?>>
                        	
                          <td colspan="1"; style="text-align:left">&nbsp;</td>
                        </tr>
                        
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                        	<td Colspan="1">&nbsp</td>
                        	<td colspan="1"; style="text-align:right; font-weight: bold;">Store GLN :</td>
                          <td colspan="2"; style="text-align:left;"><input type="text" name="NGLN" value="<?php echo $Gln; ?>"></td>
                          <td colspan="1"; style="text-align:left">&nbsp;</td>
                        </tr>
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                        </tr>
                        
                       
                       
                        
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                        	<td Colspan="1">&nbsp</td>
                        	<td colspan="1"; style="text-align:right; font-weight: bold;">Latitude :</td>
                          <td colspan="2"; style="text-align:left;"><input type="text" name="NLAT" value="<?php echo $Lat; ?>"></td>
                          <td colspan="1"; style="text-align:left">&nbsp;</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                        	<td Colspan="1">&nbsp</td>
                        	<td colspan="1"; style="text-align:right; font-weight: bold;">Longitude :</td>
                          <td colspan="2"; style="text-align:left;"><input type="text" name="NLONG" value="<?php echo $Long; ?>"></td>
                          <td colspan="1"; style="text-align:left">&nbsp;</td>
                        </tr>
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                        	<td Colspan="1">&nbsp</td>
                        	<td colspan="1"; style="text-align:right; font-weight: bold;">NDD :</td>
                        	<td colspan="1"; style="text-align:left;"><?php echo trim($nddd);?></td>
                          <td <td>
                          	               <select name="NDD" id="NDD" size="1">
                                           <option value="<?php echo $nddd; ?>"><?php echo $nddd; ?></option> 
                                                <?php foreach($NODDD as $row) { ?>
                                                          <option value="<?php echo $row; ?>"><?php echo $row; ?></option>
                                                 <?php } ?>
                                           </select></td>
                          <td colspan="1"; style="text-align:left">&nbsp;</td>
                        </tr>
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                        	<td Colspan="1">&nbsp</td>
                        	<td colspan="1"; style="text-align:right; font-weight: bold;">NOD :</td>
                          <td colspan="1"; style="text-align:left;"><?php echo trim($nood);?></td>
                          <td <td>
                          	               <select name="NOD" id="NOD" size="1">
                                                <option value="<?php echo $nood; ?>"><?php echo $nood; ?></option> 
                                                <?php foreach($NODDD as $row) { ?>
                                                	        
                                                          <option value="<?php echo $row; ?>"><?php echo $row; ?></option>
                                                 <?php } ?>
                                           </select></td>
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
                             <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="SaveDet" value= "Submit Details ">
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
             document.getElementById("DEL").focus();
          }
      </script>
    <?php
    	}
    	
 //*******************************************************************************************************88
          public function ADDStoreScreen($wareHouseCde){


   $Warehouse= "";
   $DepotName= "";
   $Branch= "";
   $warehouseArea= "";
   $DelArea= "";
   $DelAreaName=  "";
   $StoreName=  "";
   $Address1=   "";
   $Address2=  "";
   $Address3=  "";
   $Lat= "" ;
   $Long=  "";
   $Gps=  "";
   $Status=  "";
   $Gln= "" ;
   $Ndd= "";
   $Nod= "";
 
  $WarehouseStoreDao = new WarehouseStoreDao($this->dbConn);
          $whDetails = $WarehouseStoreDao->DelAreaDet($wareHouseCde);

   $NODDD=array(" ","1 - Monday","2 - Tuesday","3 - Wednesday","4 - Thursday","5 - Friday","6 - Saturday","7 - Sunday","8 - Unknown"); 

   
   
    	?>
    	 <body>
          <center>
              <FORM name='Warehouse Area Manage' method=post action=''>
                   <table width="720"; style="border:none">
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td class=head1 Colspan="5"; style="text-align:center">Add New Warehouse Store</td>
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
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                        	<td Colspan="1">&nbsp</td>
                        	<td colspan="1"; style="text-align:right; font-weight: bold;">Store Name :</td>
                          <td colspan="2"; style="text-align:left;"><input type="text" name="NNAME" value="<?php echo $StoreName; ?>"></td>
                          <td colspan="1"; style="text-align:left">&nbsp;</td>
                        </tr>
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                        	<td Colspan="1">&nbsp</td>
                        	<td colspan="1"; style="text-align:right; font-weight: bold;">Store Address :</td>
                          <td colspan="2"; style="text-align:left;"><input type="text" name="NADD1" value="<?php echo $Address1; ?>"></td>
                          <td colspan="1"; style="text-align:left">&nbsp;</td>
                        </tr>
                        
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                        	<td Colspan="1">&nbsp</td>
                        	<td colspan="1"; style="text-align:right; font-weight: bold;">&nbsp</td>
                          <td colspan="2"; style="text-align:left;"><input type="text" name="NADD2" value="<?php echo $Address2; ?>"></td>
                          
                          <td colspan="1"; style="text-align:left">&nbsp;</td>
                        </tr>
                        
                         </tr>
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                        	<td Colspan="1">&nbsp</td>
                        	<td colspan="1"; style="text-align:right; font-weight: bold;">&nbsp</td>
                           	<td colspan="2"; style="text-align:left;"><input type="text" name="NADD3" value="<?php echo $Address3; ?>"></td>
                          
                          <td colspan="1"; style="text-align:left">&nbsp;</td>
                        </tr>
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                        </tr>
                     <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                                 <td Colspan="1">&nbsp</td>
                                 <td colspan="1"; style="text-align:right; font-weight: bold;">Select Delivery Area</td>
                                 
                                 
                                  <td>
                                       <select name="DEL" id="DEL" size="1">
                                           <option value="Change Delivery Area">Delivery Area</option>
                                                <?php foreach($whDetails as $row) { ?>
                                                          <option value="<?php echo trim($row['uid']) . " - " . trim($row['wh_description']); ?>"><?php echo trim($row['uid']) . " - " . trim($row['wh_description']); ?></option>
                                                 <?php } ?>
                                       </select>
                                  </td>
                                  <td Colspan="3">&nbsp</td>
                            </tr>                   
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                        	<td Colspan="1">&nbsp</td>
                        	<td colspan="1"; style="text-align:right; font-weight: bold;">Store Branch Code</td> 
                        	 
                        	 	<td colspan="2"; style="text-align:left;"><input type="text" name="NBRANCH" value="<?php echo $Branch; ?>"></td>
                        	
                          <td colspan="1"; style="text-align:left">&nbsp;</td>
                        </tr>
                     
                        
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                        	<td Colspan="4">&nbsp</td>
                        	
                        	 	
                        	
                          <td colspan="1"; style="text-align:left">&nbsp;</td>
                        </tr>
                        
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                        	<td Colspan="1">&nbsp</td>
                        	<td colspan="1"; style="text-align:right; font-weight: bold;">Store GLN :</td>
                          <td colspan="2"; style="text-align:left;"><input type="text" name="NGLN" value="<?php echo $Gln; ?>"></td>
                          <td colspan="1"; style="text-align:left">&nbsp;</td>
                        </tr>
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                        </tr>
                        
                       
                       
                        
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                        	<td Colspan="1">&nbsp</td>
                        	<td colspan="1"; style="text-align:right; font-weight: bold;">Latitude :</td>
                          <td colspan="2"; style="text-align:left;"><input type="text" name="NLAT" value="<?php echo $Lat; ?>"></td>
                          <td colspan="1"; style="text-align:left">&nbsp;</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                        	<td Colspan="1">&nbsp</td>
                        	<td colspan="1"; style="text-align:right; font-weight: bold;">Longitude :</td>
                          <td colspan="2"; style="text-align:left;"><input type="text" name="NLONG" value="<?php echo $Long; ?>"></td>
                          <td colspan="1"; style="text-align:left">&nbsp;</td>
                        </tr>
                        
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                        </tr>
                        
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                        	<td Colspan="1">&nbsp</td>
                        	<td colspan="1"; style="text-align:right; font-weight: bold;">NDD :</td>
                          <td <td>
                          	               <select name="NNDD" id="NNND" size="1">
                                           <option value="Select NDD">Select NDD :</option>
                                                <?php foreach($NODDD as $row) { ?>
                                                          <option value="<?php echo $row; ?>"><?php echo $row; ?></option>
                                                 <?php } ?>
                                           </select></td>
                          <td colspan="1"; style="text-align:left">&nbsp;</td>
                        </tr>
                        
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                        </tr>
                        
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                        	<td Colspan="1">&nbsp</td>
                        	<td colspan="1"; style="text-align:right; font-weight: bold;">NOD :</td>
                          
                          <td    <td>
                                       <select name="NNOD" id="NOD" size="1">
                                           <option value="Select NOD">Select NOD :</option>
                                                <?php foreach($NODDD as $row) { ?>
                                                          <option value="<?php echo $row; ?>"><?php echo $row; ?></option>
                                                 <?php } ?>
                                       </select>
                                  </td>
                          
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
                             <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="AddDet" value= "Add Store">
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
             document.getElementById("DEL").focus();
          }
      </script>
    <?php
    	}  	
    
  }