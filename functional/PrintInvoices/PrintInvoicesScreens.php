<?php
    include_once('ROOT.php');
    include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER.'DAO/PrintInvoicesDAO.php');
    include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
    include_once($ROOT.$PHPFOLDER."elements/datePickerElement.php");

class PrintInvoicesScreens {

      function __construct() {

         global $dbConn;
         $this->dbConn = $dbConn;
      }
//************************************************************************************************************************************************************************************************************************************************************************************************   
      public function TripSheetNumber(){      
      ?>      
      <body  onload='setFocusToTextBoxF()'>
          <center>
              <FORM name='SELECT USER' method=post action=''>
                   <table width="720"; style="border:none">
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                             <td class=head1 Colspan="5"; style="text-align:center">Print Invoices On Tripsheet</td>
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
                        	<td colspan="1"; style="text-align:centre">Enter Trip Sheet Number :</td>
                          <td colspan="1"; style="text-align:centre";>&nbsp</td>
                         <td Colspan="5">&nbsp</td>
                          </tr>
                          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td colspan="2">&nbsp</td>
                          <td class="det1" colspan="2" style="text-align:left; padding: 0px 5px 0px 5px;" ;><INPUT TYPE="TEXT" size="20" name="SEARCHTP"    value= "" ></td>
                          <td colspan="1">&nbsp</td>            
                         
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="GETTRIPINV" value= "Get TripSheet Invoices">&nbsp;&nbsp
                          	                                          <INPUT TYPE="submit" class="submit" name="BACK" value= "Back">
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
//************************************************************************************************************************************************************************************************************************************************************************************************   
      public function displayInvoices($tsInvoices) { 
           //echo "<br>";
      	   //echo "<pre>";
           //	print_r($tsInvoices);
      	?>
           <body>
              <center>
                <FORM name='UserUpdateScreen' method=post action=''>
                   <table width="720"; style="border:none">
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            <td class=head1 Colspan="7"; style="text-align:center">Invoices To Print</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td width="5%";  style="border:none">&nbsp;</td>
                          <td width="10%"; style="border:none">&nbsp;</td>
                          <td width="20%"; style="border:none">&nbsp;</td>
                          <td width="45%"; style="border:none">&nbsp;</td>
                          <td width="5%";  style="border:none">&nbsp;</td>
                          <td width="10%";  style="border:none">&nbsp;</td>
                          <td width="5%";  style="border:none">&nbsp;</td>
                        </tr>              
                         </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">                          
                             <td Colspan="1">&nbsp</td>
                             <td class="det1" colspan="1"; style="text-align:left;">Tripsheet&nbsp;Number</td>                           	                         
                             <td class="det2" colspan="1"; style="text-align:left;"><?php echo $tsInvoices[0]['tripsheet_number'];?></td>            
                             <td class="det1" colspan="1"; style="text-align:right;"><?php echo 'Transporter';?></td>
                             <td class="det2" colspan="2"; style="text-align:right;"><?php echo $tsInvoices[0]['Transporter'];?></td>
                             <td Colspan="1">&nbsp</td>                           
                        </tr>                         
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="7">&nbsp</td>
                         </tr> 
                        <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">                                                                              
                          <td Colspan="1">&nbsp</td>
                          <td class="det1" colspan="1"; style="text-align:left;font-weight: bold;">Document Number</td>                           	                         
                          <td class="det1" colspan="1"; style="text-align:left;font-weight: bold;">Principal</td>            
                          <td class="det1" colspan="1"; style="text-align:left;font-weight: bold;">Store</td>
                          <td class="det1" colspan="1"; style="text-align:left;font-weight: bold;">Printed</td>
                          <td colspan="1"; style="text-align:left;font-weight: bold;">Select<br><a href="javascript:;" onClick="selectAll('INVSELECT[]', 1);">All</a>
                                                                                                <a href="javascript:;" onClick="selectAll('INVSELECT[]', 0);">None</a></td>
                          <td Colspan="1">&nbsp</td>                                                          
                        </tr>                  
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="7">&nbsp</td>
                         </tr>                                                      
                          <?php
                         foreach ($tsInvoices as $row) {?>
                             <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>"> 
                                  <td Colspan="1">&nbsp</td>                         
                                  <td class="det2"colspan="1"; style="text-align:left;"><?php echo $row['DocumentNumber'];?></td>
                                  <td class="det2"colspan="1"; style="text-align:left;"><?php echo $row['Principal'];?></td>
                                  <td class="det2"colspan="1"; style="text-align:left;"><?php echo $row['Store'];?></td>
                                  <td class="det2"colspan="1"; style="text-align:center;"><?php echo $row['invPrinted'];?></td>                                    
                                  <td class="det2" colspan="1";style="text-align:right;"><INPUT TYPE="checkbox" name="INVSELECT[]" value= "<?php echo $row['docUid'];?>"></td>                                 
                                  <td Colspan="1">&nbsp;</td>
                             </tr>
                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                   <td Colspan="7">&nbsp</td>
                             </tr> 
                          <?php 	
                       }   ?>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="7">&nbsp</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                              <td Colspan="7">&nbsp</td>
                         </tr>                    
                          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="7">&nbsp</td>
                         </tr>                      
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                             <td colspan="7"; style="text-align:center;">
                                                                         <INPUT TYPE="submit" class="submit" name="PRINTINVOICES" value= "Print Selected Invoices">
                                                                         <INPUT TYPE="submit" class="submit" name="BACKFORM"   value= "Back"></td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                             <td colspan="7"; style="text-align:center;">&nbsp;</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="7">&nbsp</td>
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
      <script type="text/javascript" defer>
     function selectAll(elementName, flag){
          $("input[name='"+elementName+"']").each( function(){$(this).attr("checked",((flag == 1)?true:false));})
     }
     function setFocusToTextBox(){
         document.getElementById("PRUID").focus();
     }    
</script>
    <?php                    

      } 

//************************************************************************************************************************************************************************************************************************************************************************************************   
  
//************************************************************************************************************************************************************************************************************************************************************************************************   








     public function Select(){
     	?>      
      <body  onload='setFocusToTextBoxF()'>
          <center>
              <FORM name='SELECT USER' method=post action=''>
                   <table width="720"; style="border:none">
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                      
                          <td class=head1 Colspan="5"; style="text-align:center">Select Function To Print By</td>
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
                          <td Colspan="5">&nbsp</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="SEARCHT" value= "Print By Tripsheet">&nbsp;&nbsp
                          	                                          <INPUT TYPE="submit" class="submit" name="SEARCHD" value= "Print By Date">&nbsp;&nbsp
                          	                                          
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
//************************************************************************************************************************************************************************************************************************************************************************************************   
      public function DateSelect($DateCaptured){ 
      ?>      
      <body  onload='setFocusToTextBoxF()'>
          <center>
              <FORM name='SELECT USER' method=post action=''>
                   <table width="720"; style="border:none">
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                      
                          <td class=head1 Colspan="5"; style="text-align:center">Print Invoices By Date</td>
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
                          <td colspan= "1"; style="text-align:centre;font-weight: bold;">Start Date :<?php DatePickerElement::getDatePickerLibs(); DatePickerElement::getDatePicker("DATECAPTURED",$DateCaptured); ?> </td>                                                                                                                                  
                          <td Colspan="2">&nbsp</td>
                        </tr>
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">                              
                         <td Colspan="2">&nbsp</td>
                         <td colspan= "1"; style="text-align:centre;font-weight: bold;"> End Date  &nbsp :<?php DatePickerElement::getDatePickerLibs(); DatePickerElement::getDatePicker("DATECAPTUREDEND",$DateCaptured); ?> </td>                 
                         <td Colspan="2">&nbsp</td>
                        </tr>
                        
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="SEARCHDD" value= "Search For Invoices">&nbsp;&nbsp
                          	                                        
                          	                                          
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