<?php
    include_once('ROOT.php');
    include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER.'DAO/WarehouseStoreLinkDAO.php');
    include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');

class WarehouseStoreLinkScreens {

      function __construct() {

         global $dbConn;
         $this->dbConn = $dbConn;
      }
    //******************************************************************  
      public function SearchStore() {
   		?>



      <body  onload='setFocusToTextBoxF()'>
          <center>
              <FORM name='SELECT USER' method=post action=''>
                   <table width="720"; style="border:none">
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                      
                          <td class=head1 Colspan="5"; style="text-align:center">Search For Store To Link</td>
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
                        	<td Colspan="2">&nbsp</td>
                      
                        	<td colspan="1"; style="text-align:centre">Find Store By Branch Code</td>
                          <td colspan="1"; style="text-align:centre";>&nbsp</td>
                         <td Colspan="5">&nbsp</td>
                          </tr>
                          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td colspan="2">&nbsp</td>
                          <td class="det1" colspan="2" style="text-align:centre; padding: 0px 5px 0px 5px;" ;><INPUT TYPE="TEXT" size="20" name="BRANCH"    value= "" ></td>
                          <td colspan="1">&nbsp</td>            
                         </tr>
                         
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                        </tr>
                        
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        	<td Colspan="2">&nbsp</td>
                        	<td colspan="1"; style="text-align:centre">Find Store By GLN Code</td>
                          <td colspan="1"; style="text-align:centre";>&nbsp</td>
                         <td Colspan="5">&nbsp</td>
                          </tr>
                          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td colspan="2">&nbsp</td>
                          <td class="det1" colspan="2" style="text-align:centre; padding: 0px 5px 0px 5px;" ;><INPUT TYPE="TEXT" size="20" name="GLN"    value= "" ></td>
                          <td colspan="1">&nbsp</td>            
                         
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                        </tr>
                         
                           <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        	<td Colspan="2">&nbsp</td>
                        	<td colspan="1"; style="text-align:centre">Find Store By Name</td>
                          <td colspan="1"; style="text-align:centre";>&nbsp</td>
                         <td Colspan="5">&nbsp</td>
                          </tr>
                          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td colspan="2">&nbsp</td>
                          <td class="det1" colspan="2" style="text-align:centre; padding: 0px 5px 0px 5px;" ;><INPUT TYPE="TEXT" size="20" name="NAME"    value= "" ></td>
                          <td colspan="1">&nbsp</td>            
                         
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="SEARCHBRANCH" value= "Search Store By Branch">&nbsp;&nbsp
                          	                                          <INPUT TYPE="submit" class="submit" name="SEARCHGLN" value= "Search Store By GLN">&nbsp;&nbsp
                                          	                          <INPUT TYPE="submit" class="submit" name="SEARCHNAME" value= "Search Store By Name">&nbsp;&nbsp
          	                                          
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
   //*************************************************************************************************************************************88
   public function LinkStoresScreen($Type,$search,$userUId,$wareHouseCde) {
   	
   	      $WarehouseStoreLinkDAO = new WarehouseStoreLinkDAO($this->dbConn);
          $Store = $WarehouseStoreLinkDAO->getStoreUid($Type,$search,$wareHouseCde);
    
          $StoreUID =$Store[0]['uid'];
          $StoreName =$Store[0]['del_point_name'];
   	
   	       $WarehouseStoreLinkDAO = new WarehouseStoreLinkDAO($this->dbConn);
          $Principals = $WarehouseStoreLinkDAO->getPrincipals($userUId,$wareHouseCde,$StoreUID);
 
   	   		?>



   	      <body  onload='setFocusToTextBoxF()'>
              <center>
                 <FORM name='SELECT USER' method=post action=''>
                   <table width="720"; style="border:none">
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                      
                          <td class=head1 Colspan="5"; style="text-align:center">Select Principals To Link to Warehouse Store</td>
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
                        <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>";>
                                 <td Colspan="1">&nbsp</td>
                                 <td class="detN12" colspan="1"; style="text-align:right; padding: 0px 5px 0px 5px;font-weight: bold;">Store :</td>
                                 <td class="detN12" colspan="1"; style="text-align:center; padding: 0px 5px 0px 5px;"><?php echo trim($StoreName);?></td>
                                 <td Colspan="1">&nbsp</td>
                                  <td Colspan="1">&nbsp</td>
                            </tr>                   
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        	<td Colspan="5">&nbsp</td>
                      
                        	<tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                          <td colspan="1" >&nbsp</td>
                          <td  class="head1" colspan="1" style="text-align:left; padding: 0px 5px 0px 5px;font-weight: bold;">Principal</td>
                          <td  class="head1" colspan="1" style="text-align:center; padding: 0px 5px 0px 5px;font-weight: bold;">Store</td>
                          <td  class="head1" colspan="1" style="text-align:right; padding: 0px 5px 0px 5px;font-weight: bold;">Manage</td>
                          <td  colspan="1">&nbsp<input type="hidden" id="STOREUID" name="STOREUID" value="<?php echo $StoreUID;?>"></td>
                          </tr>
                          <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                                   <td  colspan="5" style="text-align:center;">&nbsp;</td> 
                              </tr>
                     
                      
                             <?php
                        	   foreach ($Principals as $row) {?> 
                              
                              <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                                    <td colspan="1" >&nbsp</td>
                                   <td class="detN12" colspan="1" style="text-align:left;"><?php echo $row['principal_name'];?></td>
                                   	<td class="detN12" colspan="1" style="text-align:center;"><?php echo $row['store_name'];?></td>
                                   <td class="detN12" colspan="1" style="text-align:right;"><INPUT TYPE="radio" name="SELMOD" onclick="javascript: submit()" value= "<?php echo $row['principal_uid'];?>">
                                   	</td> 
                                   	                                
                                  <td colspan="1" >&nbsp</td>
                                   <input type="hidden" id="CURSTAT" name="CURSTAT" value="<?php echo $row['status'];?>">
                              </tr>  
                        	 
                             
                              
                          <?php 
                          
                          } ?>
                        	
                          
                      
                         
                             
                                             
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                        </tr>
                  
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td colspan="5"; style="text-align:center;">
                          	                                          <INPUT TYPE="submit" class="submit" name="BACKFORM"   value= "Back">
                          	                                          <INPUT TYPE="submit" class="submit" name="REFRESH"   value= "Refresh"></td>
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
    //**********************************************************************************************
    
   	//****************************************************************************************
   	public function LinkStoresScreenName($StoreName,$StoreUID,$userUId,$wareHouseCde)
   {
   	
   	       
   	       
   	       $WarehouseStoreLinkDAO = new WarehouseStoreLinkDAO($this->dbConn);
           $Principals = $WarehouseStoreLinkDAO->getPrincipals($userUId,$wareHouseCde,$StoreUID);
             	
   	      
   	
   	   		?>



   	 <body  onload='setFocusToTextBoxF()'>
          <center>
              <FORM name='SELECT USER' method=post action=''>
                   <table width="720"; style="border:none">
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                      
                          <td class=head1 Colspan="5"; style="text-align:center">Select Principals To Link to Warehouse Store</td>
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
                        <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>";>
                                 <td Colspan="1">&nbsp</td>
                                 <td class="detN12" colspan="1"; style="text-align:right; padding: 0px 5px 0px 5px;font-weight: bold;">Store :</td>
                                 <td class="detN12" colspan="1"; style="text-align:center; padding: 0px 5px 0px 5px;"><?php echo trim($StoreName);?></td>
                                 <td Colspan="1">&nbsp</td>
                                  <td Colspan="1">&nbsp</td>
                            </tr>                   
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        	<td Colspan="5">&nbsp</td>
                      
                        	<tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                          <td colspan="1" >&nbsp</td>
                          <td  class="head1" colspan="1" style="text-align:left; padding: 0px 5px 0px 5px;font-weight: bold;">Principal</td>
                          <td  class="head1" colspan="1" style="text-align:center; padding: 0px 5px 0px 5px;font-weight: bold;">Store</td>
                          <td  class="head1" colspan="1" style="text-align:right; padding: 0px 5px 0px 5px;font-weight: bold;">Manage</td>
                          <td  colspan="1">&nbsp<input type="hidden" id="STOREUID" name="STOREUID" value="<?php echo $StoreUID;?>"></td>
                          </tr>
                          <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                                   <td  colspan="5" style="text-align:center;">&nbsp;</td> 
                              </tr>
                     
                      
                             <?php
                        	   foreach ($Principals as $row) {?> 
                              
                              <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                                    <td colspan="1" >&nbsp</td>
                                   <td class="detN12" colspan="1" style="text-align:left;"><?php echo $row['principal_name'];?></td>
                                   	<td class="detN12" colspan="1" style="text-align:center;"><?php echo $row['store_name'];?></td>
                                   <td class="detN12" colspan="1" style="text-align:right;"><INPUT TYPE="radio" name="SELMOD" onclick="javascript: submit()" value= "<?php echo $row['principal_uid'];?>">
                                   	</td> 
                                   	                                
                                  <td colspan="1" >&nbsp</td>
                                   <input type="hidden" id="CURSTAT" name="CURSTAT" value="<?php echo $row['status'];?>">
                              </tr>  
                              
                              
                             
                              
                          <?php 
                          
                          } ?>
                        	
                          
                      
                        	                 
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                        </tr>
                  
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td colspan="5"; style="text-align:center;">
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
   	
   	//***************************************************************************8
   	public function StoreLinkSelect($NameSearch,$userUId,$wareHouseCde) { 
    	
 	   
 	$WarehouseStoreLinkDAO = new WarehouseStoreLinkDAO($this->dbConn);
          $Store = $WarehouseStoreLinkDAO->getStoreUidName($NameSearch,$wareHouseCde);
          
 	//
 	
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
                                             <option value="Select Employee">Select Store</option>
                                             <?php foreach($Store as $row) { ?>
                                                   <option value="<?php echo trim($row['uid']) . " - " . trim($row['del_point_name']); ?>"><?php echo trim($row['uid']. " - " . trim($row['del_point_name'])) ; ?></option>
                                            
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
                          <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="SUBNAME" value= "Submit">&nbsp;&nbsp
                          	                                          
                          	                                  
                          	                                          
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
//*****************************************************************************************************************************************************
 
    
    
    }