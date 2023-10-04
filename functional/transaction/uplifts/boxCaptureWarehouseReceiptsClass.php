<?php
    include_once('ROOT.php'); 
    include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER.'DAO/AgedStockDAO.php');
    include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
    //($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
 
class captureWarehouseReceipt {
	
      function __construct() {

         global $dbConn;
         $this->dbConn = $dbConn;
      }	
      
// ********************************************************************************************************************************      
      
   public function firstform() {
	
      ?>
      <body  onload='setFocusToTextBoxF()'>
          <center>
              <FORM name='Capture Warehouse Receipts' method=post action=''>
                   <table width="720"; style="border:none">
                        <tr>
                          <td class=head1 Colspan="5"; style="text-align:center">Warehouse Reciepts</td>
                        </tr>
                        <tr>
                          <td>&nbsp</td>
                        </tr>	        	
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td width="38%"; style="border:none">&nbsp</td>
                          <td width="20%"; style="border:none">&nbsp</td>
                          <td width="20%"; style="border:none">&nbsp</td>
                          <td width="20%"; style="border:none">&nbsp</td>
                          <td width="2%" ; style="border:none">&nbsp</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                          <td style="text-align:center";>Enter Document Number</td>
                          <td colspan="4"; style="text-align:left"><input type="text" id="UPLIFTNO" name="UPLIFTNO"></td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="FIRSTFORM" value= "Get Document Details">
                                                                      <INPUT TYPE="submit" class="submit" name="CANFORM"   value= "Cancel"></td>
                        </tr>          
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                         </tr>  
                   </table>
              </form>
          </center>
      </body>          
    <?php 
   } 

// ********************************************************************************************************************************      

  public function numOfBoxes($custName, $prinUid, $date, $docNum, $docUid, $deliverName) {
	
			//$AgedStockDAO = new AgedStockDAO($this->dbConn); 
			//$boxDetails = $AgedStockDAO->getBoxDetailsToUpdate($principalUID, $docNum);
         
         $class = 'odd';
                                                            
             	?>
      <body  onload='setFocusToTextBoxB()'>
         <center>
             <form name='Capture No.Boxes' method=post action=''>
                <table width="750"; style="border:none">
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                       <td class=head1 Colspan="7"; style="text-align:center">Capture Number of Boxes</td>
                   </tr> 
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                       <td Colspan="7"; style="text-align:center"> &nbsp;</td>
                   </tr>       	
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
	                  	 <td width="5%"; style="border:none">&nbsp</td>
	                     <td width="20%"; style="border:none">&nbsp</td>
	                     <td width="20%"; style="border:none">&nbsp</td>
	                     <td width="5%"; style="border:none">&nbsp</td>
	                     <td width="15%"; style="border:none">&nbsp</td>
	                     <td width="30%"; style="border:none">&nbsp</td>
	                     <td width="5%"; style="border:none">&nbsp</td>
                   </tr>
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">                   
                       <td Colspan="7"; style="text-align:center"> &nbsp;</td>
                   </tr>  
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
	                     <td style="text-align:left";>&nbsp;</td>
	                     <td class="det1" style="text-align:right";>Principal:</td>
	                     <td class="det2" style="text-align:left";><?php echo trim($custName);?></td>
	                     <td style="text-align:left";><input type="hidden" name="PRIN" value='<?php echo $custName; ?>'>
	                     	                            <input type="hidden" name="PRINUID" value='<?php echo $prinUid; ?>'></td> 
	                     <td class="det1" style="text-align:left";>Date:</td>	
	                     <td class="det2" style="text-align:left";> <?php echo $date;?></td> 
	                     <td style="text-align:left";><input type="hidden" name="DOCDATE" value='<?php echo $date; ?>'></td> 
                   </tr>
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                       <td class=head1 Colspan="7"; style="text-align:center"> &nbsp;</td>
                   </tr>  
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
	                     <td style="text-align:left";>&nbsp;</td>
	                     <td class="det1" style="text-align:right";>Document No:</td>  
	                     <td class="det2" style="text-align:left";><?php echo ltrim($docNum,'0');?></td>	                        	
	                     <td style="text-align:left";><input type="hidden" name="DOCNUM" value='<?php echo ltrim($docNum,'0');?>'></td>
	                     <td class="det1" style="text-align:left";>Store Name:</td>	
	                     <td class="det2" style="text-align:left";><?php echo $deliverName;?></td>		
	                     <td class="det1" style="text-align:left";><input type="hidden" name="STORE" value='<?php echo $deliverName; ?>'></td>
                   </tr>
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                       <td Colspan="7"; style="text-align:center"><input type="hidden" name="DOCID"  value='<?php echo ltrim($docUid,'0');?>'></td>
                   </tr>                 
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                       <td Colspan="7"; style="text-align:center"><input type="hidden" name="DOCID"  value='<?php echo ltrim($docUid,'0');?>'></td>
                   </tr>   
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                       <td class=head1 Colspan="7"; style="text-align:center"> &nbsp;</td>
                   </tr>
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
	                     <td style="text-align:left";>&nbsp;</td>
	                     <td class="det1" colspan='2' style="text-align:right";>Enter No of Boxes: </td>
	                     <td class="det2" colspan='3' style="text-align:left";><input type="text" id="NOBOXES" name="NOBOXES" size="15"></td> 
                       <td style="text-align:left";>&nbsp;</td>
                   </tr>	            
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                       <td class=head1 Colspan="7"; style="text-align:center"> &nbsp;</td>
                   </tr>            	            
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                      <td Colspan="7" style="text-align:center";><INPUT TYPE="submit" class="submit" name="NUMOFBOXES" value= "Confirm No of Boxes">
                                                                 <INPUT TYPE="submit" class="submit" name="CAPTCANCEL"   value= "Cancel"></td>
                   </tr> 
                </table>
             </form>
         </center>
      </body>    
        <?php        
  }	
 // ********************************************************************************************************************************
 
  public function finalForm($custName, $prinUid, $date, $docNum, $docUid, $deliverName, $nBoxes, $numList) {
 	
      if(count(explode(',',$numList)) <= $nBoxes) {
           $contEnd = 'Y';
      } else {
           $contEnd = 'N';
     }
    	$class = 'odd';
 	
     ?>
      <body  onload='setFocusToTextBoxN()'> 	      
           <center>
               <form name='Scan Boxes' method=post action=''>
                   <table width="750"; style="border:none">
                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                           <td class='head1' Colspan="7"; style="text-align:center">Enter Box Numbers</td>
                       </tr> 
                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                           <td Colspan="7"; style="text-align:center"> &nbsp;</td>
                       </tr>      	
                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
	                  	      <td width="3%"; style="border:none">&nbsp</td>
	                          <td width="17%"; style="border:none">&nbsp</td>
	                          <td width="25%"; style="border:none">&nbsp</td>
	                          <td width="1%"; style="border:none">&nbsp</td>
	                          <td width="21%"; style="border:none">&nbsp</td>
	                          <td width="30%"; style="border:none">&nbsp</td>
	                          <td width="3%"; style="border:none">&nbsp</td>
                       </tr>
                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                       	    <td style="border:none">&nbsp</td>
                            <td class="det1" style="text-align:left";>Principal:</td>
	                          <td class="det2" style="text-align:left";><?php echo $custName;?></td>
	                          <td style="text-align:left";><input type="hidden" name="PRIN" value='<?php echo $custName; ?>'>
	                          	                           <input type="hidden" name="PRINUID" value='<?php echo $prinUid; ?>'></td> 
	                          <td class="det1" style="text-align:left";>Date:</td>	
	                          <td class="det2" style="text-align:left";><?php echo $date; ?> </td>
	                          <td style="border:none"><input type="hidden" name="DOCDATE" value='<?php echo $date; ?>'></td> 
                       </tr>
                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                           <td Colspan="7"; style="text-align:center"> &nbsp;</td>
                       </tr> 
                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                           <td style="text-align:left";>&nbsp;</td>                                                                             
                           <td class="det1" style="text-align:right";>Document No:</td>                                                         
                           <td class="det2" style="text-align:left";><?php echo ltrim($docNum,'0');?></td>	                        	                                    	
                           <td style="text-align:left";><input type="hidden" name="DOCNUM" value='<?php echo ltrim($docNum,'0');?>'>
                           	                            <input type="hidden" name="DOCID" value='<?php echo ltrim($docUid,'0');?>'></td>                 
                           <td class="det1" style="text-align:left";>Store Name:</td>	                                                          
                           <td class="det2" style="text-align:left";><?php echo $deliverName;?></td>		                                        
                           <td class="det1" style="text-align:left";><input type="hidden" name="STORE" value='<?php echo $deliverName; ?>'></td>
                       </tr>
                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                           <td Colspan="7"; style="text-align:center"><input type="hidden" name="FNOBOXES" value='<?php echo $nBoxes; ?>'>
                          	                                          <input type="hidden" name="BOXLIST" value='<?php echo $numList; ?>'></td>
                       </tr>
                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                           <?php if($contEnd == 'Y') { $capBlock = ''; $cmessage = 'Scan Or Enter Box No';} else { $capBlock = 'disabled'; $cmessage = 'Box Number Capture Complete';} ; ?> 
                           <td Colspan="7"; style="text-align:center"> &nbsp;</td>
                       </tr> 
                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                           <td style="text-align:left";>&nbsp;</td>
                           <td class='det1' colspan='2' style="text-align:right";>Scan Box Number: </td>
                           <td colspan='3' style="text-align:left";><input type="text" id="BOXNUMBER" name="BOXNUMBER" size="25" <?php echo $capBlock; ?> placeholder='<?php echo $cmessage; ?>'></td> 
                           <td style="text-align:left";><?php echo count(explode(',',$numList))-1 . '/' . $nBoxes ; ?></td> 		
                       </tr>                        	            
                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                           <td Colspan="7">&nbsp</td>
                       </tr>
                       <?php if($contEnd == 'Y') { ?>
                            <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                <td Colspan="7">&nbsp</td>
                            </tr>                       
                       <?php
                       } else { 
                                   	$AgedStockDAO = new AgedStockDAO($this->dbConn);
                                     $replst = $AgedStockDAO->getStoreRep($prinUid, ''); 
                       	?>
                            <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                <td Colspan="7">&nbsp</td>
                            </tr>                                     	
                            <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                                <td colspan="1">&nbsp</td>
                         	      <td class='det1' colspan='2' style="text-align:right";>Value</td>
                                <td class='det2' colspan="3"; style="text-align:left"><input type="text" name="UVALUE" value="<?php echo $uvalue; ?>"></td>
                                <td colspan="1">&nbsp</td>
                            </tr>                      
                            <tr class="<?php echo GUICommonUtils::styleEO($class);?>">  
                               <td  colspan="7"; style="text-align:center" >&nbsp;</td>  
                            </tr>                      
                            <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                            	   <td colspan="1">&nbsp</td>
                                 <td class='det1' colspan='2' style="text-align:right";>Uplifted By</td>
                                 <td class='det1' colspan="3"; style="text-align:left">
                             	     <select name="DELBY" id="DELBY">
                                       <option value="Select Rep">Select Rep</option>
                                        <?php foreach($replst as $row) { ?>
                                                  <option value="<?php echo $row['uid']; ?>"><?php echo $row['first_name']; ?></option>
                                        <?php
                                               }  
                                        ?>
                                   </select>
                                 <td colspan="1">&nbsp</td>  
                           </tr>
                          <tr class="<?php echo GUICommonUtils::styleEO($class);?>">  
                            <td  colspan="7"; style="text-align:center" >&nbsp;</td>  
                          </tr>                       	
                             <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                                <td colspan="1">&nbsp</td>
                         	      <td class='det1' colspan='2' style="text-align:right";>Store Reference</td>
                                <td class='det2' colspan="3"; style="text-align:left"><input type="text" name="SREFERENCE" value="<?php echo $sReference; ?>"></td>
                                <td colspan="1">&nbsp</td>
                            </tr>                      
                            <tr class="<?php echo GUICommonUtils::styleEO($class);?>">  
                               <td  colspan="7"; style="text-align:center" >&nbsp;</td>  
                            </tr>
                            <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                <td Colspan="7" style="text-align:center";><INPUT TYPE="submit" class="submit" name="SAVEBXNOS" value= "Submit Box Numbers">
                                	                                         <INPUT TYPE="submit" class="submit" name="CAPTCANCEL"   value= "Cancel"></td>
                            </tr>
                       <?php                      	
                       } ?>
                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                           <td Colspan="7">&nbsp</td>
                       </tr>
                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                           <td Colspan="7">&nbsp</td>
                       </tr>
                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                           <td Colspan="7">&nbsp</td>
                       </tr>
                   </table>
               </form>
           </center>
      </body>

      <script type="text/javascript">           
          function setFocusToTextBoxN() {              
             document.getElementById("BOXNUMBER").focus();           
          }        
      </script>
      <script type="text/javascript">        
          function setFocusToTextBoxB(){            
             document.getElementById("NOBOXES").focus();        
          }        
      </script>        
      <script type="text/javascript">        
          function setFocusToTextBoxF(){            
             document.getElementById("UPLIFTNO").focus();        
          }        
      </script>

      <?php      
  }
  
// ********************************************************************************************************************************	

  public function captError($prinId, $rdocmun, $rbox, $eLine1, $eLine2) { ?> 
      <center>
         <form name='WareHouse Receipt Error' method=post action=''>
          <table width="500"; style="border:none">
              <tr>
                 <td width="20%";>&nbsp</td>
                 <td width="20%";>&nbsp</td>
                 <td width="20%";>&nbsp</td>
                 <td width="20%";>&nbsp</td>
              </tr>
              <tr>
                 <td Colspan="4">&nbsp</td> 	
              </tr>
              <tr>
                 <td Colspan="4">&nbsp</td> 	
              </tr>
        </table>
        <table class="box" width="400";>
            <tr>
               <td width="5%";>&nbsp</td>
               <td width="30%";>&nbsp</td>
               <td width="30%";>&nbsp</td>
               <td width="30%";>&nbsp</td>                             
               <td width="5%"; style="border:collapse; border-right: 2px solid; border-color: #990000;">&nbsp</td>  
            <tr>
               <td Colspan="1" rowspan="3"><img src="<?php echo 'error-icon-big.png'; ?>" style="width:60px; height:60px; float:left;" ></td> 	
               <td Colspan="3" style="font-size: 13px; font-weight: bold;"><?php echo $eLine1; ?><br><br><?php echo $eLine2; ?></td> 
               <td Colspan="1" style="border:collapse; border-right: 2px solid; border-color: #990000;">&nbsp</td> 
            </tr>
            <tr>
               <td Colspan="5"><input type="hidden" name="RDOCNUM" value=<?php echo mysqli_real_escape_string($this->dbConn->connection, $rdocmun); ?>>
                               <input type="hidden" name="PRINID"  value=<?php echo mysqli_real_escape_string($this->dbConn->connection, $prinId); ?>>
                               <input type="hidden" name="RBOX"    value=<?php echo mysqli_real_escape_string($this->dbConn->connection, $rbox); ?>></td> 	
            </tr>       	
            <tr>
               <td Colspan="5"; style="text-align:center";><INPUT TYPE="submit" class="submit" name="CAPTCONT" value= "Continue ">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                           <INPUT TYPE="submit" class="submit" name="CAPTCANCEL"  value= "Cancel Capture"></td> 	
            </tr> 
            <tr>
               <td Colspan="5" style="border:collapse; border-right: 2px solid; border-color: #990000;">&nbsp</td> 	
            </tr> 
            <tr>
               <td Colspan="5" style="border:collapse; border-right: 2px solid; border-color: #990000;">&nbsp</td> 	
            </tr>
        </table>


       </form>
    </center>
<?php     
  }
// ********************************************************************************************************************************	



}

 ?>

