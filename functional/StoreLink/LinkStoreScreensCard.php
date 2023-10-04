<?php
    include_once('ROOT.php');
    include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER.'DAO/WarehouseStoreLinkDAO.php');
    include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');

class LinkStoreScreensCard {

      function __construct() {

         global $dbConn;
         $this->dbConn = $dbConn;
      }
//*************************************************************************************************************************************************
Public function LinkStoreCardSearch(){
  	?>



      <body  onload='setFocusToTextBoxF()'>
          <center>
              <FORM name='SELECT USER' method=post action=''>
                   <table width="720"; style="border:none">
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                      
                          <td class=head1 Colspan="5"; style="text-align:center">Search For Principal Store To Link</td>
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
                          <td class="det1" colspan="2" style="text-align:centre; padding: 0px 5px 0px 5px;" ;><INPUT TYPE="TEXT" size="20" name="BRANCHP"    value= "" ></td>
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
                          <td class="det1" colspan="2" style="text-align:centre; padding: 0px 5px 0px 5px;" ;><INPUT TYPE="TEXT" size="20" name="GLNP"    value= "" ></td>
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
                          <td class="det1" colspan="2" style="text-align:centre; padding: 0px 5px 0px 5px;" ;><INPUT TYPE="TEXT" size="20" name="NAMEP"    value= "" ></td>                     
                           <td Colspan="1">&nbsp</td>
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="SEARCHBRANCHP" value= "Search">&nbsp;&nbsp
                          	                                          
                                          	                          
          	                                          
                          	                                          <INPUT TYPE="submit" class="submit" name="BACKCLOSEFORM"   value= "Back"></td>
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
//*************************************************************************************************************************************************
public function LinkStoresCard($Type,$search,$userUId,$wareHouseCde,$principalUID,$wstoreID) {
   	
   	      $WarehouseStoreLinkDAO = new WarehouseStoreLinkDAO($this->dbConn);
          $Store = $WarehouseStoreLinkDAO->getPrincipalStoreUID($Type,$search,$principalUID,$wareHouseCde);
          
           if(count ($Store)<1) { 
           	          ?>  <script>alert("No Stores Found Check Filters");</script>;<?php 
           	      	 $LinkStoreScreensCard= new LinkStoreScreensCard();
                     $a = $LinkStoreScreensCard->LinkStoreCardSearch(); 	
                      return;
                              } 
          $StoreUID =$Store[0]['uid'];
          $StoreName =$Store[0]['deliver_name'];
   	      
   	      $WarehouseStoreLinkDAO = new WarehouseStoreLinkDAO($this->dbConn);
          $WStore = $WarehouseStoreLinkDAO->getWarehouseStoreName($wstoreID);
          
          $wStoreUID =$WStore[0]['uid'];
          $wStoreName =$WStore[0]['del_point_name'];
   	      
   	      
 
   	   		?>



   	      <body  onload='setFocusToTextBoxF()'>
              <center>
                 <FORM name='SELECT USER' method=post action=''>
                   <table width="720"; style="border:none">
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                      
                          <td class=head1 Colspan="5"; style="text-align:center">Select Store to link</td>
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
                                 <td class="detN12" colspan="1"; style="text-align:right; padding: 0px 5px 0px 5px;font-weight: bold;">Warehouse Store :</td>
                                 <td class="detN12" colspan="1"; style="text-align:center; padding: 0px 5px 0px 5px;"><?php echo trim($wStoreName);?></td>
                                 <td Colspan="1">&nbsp</td>
                                  <td Colspan="1">&nbsp</td>
                            </tr>                   
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        	<td Colspan="5">&nbsp</td>
                         
                        	
                          <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                                   <td  colspan="5" style="text-align:center;">&nbsp;</td> 
                              </tr>
                            
                          <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>";>
                                 <td Colspan="1">&nbsp</td>
                                 <td class="detN12" colspan="1"; style="text-align:right; padding: 0px 5px 0px 5px;font-weight: bold;">Principal Store :</td>
                                 <td class="detN12" colspan="1"; style="text-align:center; padding: 0px 5px 0px 5px;"><?php echo trim($StoreName);?></td>
                                 	<input type="hidden" name="PSTOREUID" value=<?php echo mysqli_real_escape_string($this->dbConn->connection, $StoreUID); ?>>
                                 <td Colspan="1">&nbsp</td>
                                  <td Colspan="1">&nbsp</td>
                            </tr>                   
                                               
                            <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                 <td Colspan="5">&nbsp</td>
                            </tr>
                  
                            <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                 <td colspan="5"; style="text-align:center;">
                          	                                          <INPUT TYPE="submit" class="submit" name="LINK"   value= "Link Principal Store To Warehouse Store">
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
//************************************************************************************************************************************************
Public function LinkStoreCardName($NameSearch,$principalUID,$wstoreID){
  	
  	
  	      $WarehouseStoreLinkDAO = new WarehouseStoreLinkDAO($this->dbConn);
          $Store = $WarehouseStoreLinkDAO->getPStoreUidName($NameSearch,$principalUID);
  	
  	
  	      $WarehouseStoreLinkDAO = new WarehouseStoreLinkDAO($this->dbConn);
          $WStore = $WarehouseStoreLinkDAO->getWarehouseStoreName($wstoreID);
          
          $wStoreUID =$WStore[0]['uid'];
          $wStoreName =$WStore[0]['del_point_name'];
          
   	   		?>



   	      <body  onload='setFocusToTextBoxF()'>
              <center>
                 <FORM name='SELECT USER' method=post action=''>
                   <table width="720"; style="border:none">
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                      
                          <td class=head1 Colspan="5"; style="text-align:center">Select Store to link</td>
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
                                 <td class="detN12" colspan="1"; style="text-align:right; padding: 0px 5px 0px 5px;font-weight: bold;">Warehouse Store :</td>
                                 <td class="detN12" colspan="1"; style="text-align:center; padding: 0px 5px 0px 5px;"><?php echo trim($wStoreName);?></td>
                                 <td Colspan="1">&nbsp</td>
                                  <td Colspan="1">&nbsp</td>
                            </tr>                   
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        	<td Colspan="5">&nbsp</td>
                         
                        	
                          <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                                   <td  colspan="5" style="text-align:center;">&nbsp;</td> 
                              </tr>
                            
                          <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>";>
                                 <td Colspan="1">&nbsp</td>
                                 <td colspan="2"; style="text-align:left";>
                          	      	     <select name="EMPID" id="EMPID">
                                             <option value="Select Employee">Select Principal Store :</option>
                                             <?php foreach($Store as $row) { ?>
                                                   <option value="<?php echo trim($row['uid']) . " - " . trim($row['deliver_name']); ?>"><?php echo trim($row['uid']. " - " . trim($row['deliver_name'])) ; ?></option>
                                             <?php
                                             } ?>
                                        </select>
                                        </td>
                                 
                                 <td Colspan="1">&nbsp</td>
                                  <td Colspan="1">&nbsp</td>
                            </tr>                   
                                               
                            <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                 <td Colspan="5">&nbsp</td>
                            </tr>
                  
                            <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                 <td colspan="5"; style="text-align:center;">
                          	                                          <INPUT TYPE="submit" class="submit" name="LINKN"   value= "Link Principal Store To Warehouse Store">
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
	   
	    }  	
	    