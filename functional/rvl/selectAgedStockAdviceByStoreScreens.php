<?php
    include_once('ROOT.php'); 
    include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
    include_once($ROOT.$PHPFOLDER."elements/datePickerElement.php");
    include_once($ROOT.$PHPFOLDER."DAO/transactionDAO.php");
    include_once($ROOT.$PHPFOLDER."DAO/PresentationDAO.php");  
    include_once($ROOT.$PHPFOLDER.'TO/PostingDistributionTO.php');
    include_once($ROOT.$PHPFOLDER.'DAO/postDistributionDAO.php');
 
class selectAgedStockAdviceByStoreScreens {
	
      function __construct() {

         global $dbConn;
         $this->dbConn = $dbConn;
      }	
// ********************************************************************************************************************************      
      public function selectAgedStockStore($mfDD, $repl) {       	
      	        $postFROMDATE  = (isset($_POST["FROMDATE"])) ? htmlspecialchars($postFROMDATE=$_POST["FROMDATE"]) : CommonUtils::getUserDate();
      	        $postTODATE    = (isset($_POST["TOMDATE"]))  ? htmlspecialchars($postTODATE  =$_POST["TODATE"]) : CommonUtils::getUserDate();          
      	  ?>

         <center>
             <form name='Invoices' method=post target=''>	
                 <table width="90%"; style="border:none">
                     <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                          <td class="det1" colspan="12" style="text-align:center;">Select Aged Stock Advice Forms</td>
                     </tr>    
                     <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td width="2%";  style="border:none;">&nbsp;</td>
                          <td width="6%"; style="border:none;">&nbsp;</td>
                          <td width="13%"; style="border:none;">&nbsp;</td>
                          <td width="13%"; style="border:none;">&nbsp;</td>
                          <td width="13%"; style="border:none;">&nbsp;</td>
                          <td width="8%";  style="border:none;">&nbsp;</td>
                          <td width="13%";  style="border:none;">&nbsp;</td>
                          <td width="13%";  style="border:none;">&nbsp;</td>
                          <td width="13%";  style="border:none;">&nbsp;</td>
                          <td width="4%";  style="border:none;">&nbsp;</td>
                          <td width="4%";  style="border:none;">&nbsp;</td>
                          <td width="1%";  style="border:none;">&nbsp;</td>
                     </tr>
                     <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
      	                 <td colspan="1">&nbsp</td>
                         <td class=det2 colspan="3" style="text-align:center";><INPUT TYPE="submit" class="submit" name="SUBMITFILTER" value= "Submit Filter">&nbsp;
                                                                    <INPUT TYPE="submit" class="submit" name="CLEARFILTER"  value= "Clear Filter"></td>
                         <td colspan="5">&nbsp</td>
                         <td colspan="3">&nbsp</td>
                     </tr>
                     <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                         <td  colspan="12" style="text-align:center;">&nbsp;</td>
                     </tr>
                     <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                         <td colspan="1">&nbsp</td>                           
                         <td class="det1" style="text-align:left; border: none;">Start&nbsp;Doc&nbsp;Date</td>
                         <td class="det1" colspan="2" style="text-align:left; border: none;"><?php DatePickerElement::getDatePickerLibs(); DatePickerElement::getDatePicker("FROMDATE",$postFROMDATE); ?> </td>
                         <td colspan="8" style="border: none;">&nbsp</td>                        
                     </tr>
                     <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                         <td colspan="1">&nbsp</td>                           
                         <td  class="det1" style="text-align:left;">End&nbsp;Doc&nbsp;Date</td>
                         <td class="det1" colspan="2" style="text-align:left";><?php DatePickerElement::getDatePickerLibs(); DatePickerElement::getDatePicker("TODATE",$postTODATE); ?> </td>
                         <td colspan="8">&nbsp</td>                         
                     </tr>
                     <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                         <td  colspan="12" style="text-align:center;">&nbsp;</td>
                     </tr>
                     <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                         <td colspan="1">&nbsp</td>
                         <td  class="det1" style="text-align:left; padding-left: 15px;">Status</td>
                         <td  class="det1" style="text-align:left; padding-left: 15px;">Invoice Date</td>
                         <td  class="det1" style="text-align:left; padding-left: 15px;">Area</td>
                         <td  class="det1" style="text-align:left; padding-left: 15px;">Doc&nbsp;No</td>
                         <td  class="det1" colspan="3" style="text-align:left; padding-left: 15px;">Store</td>
                         <td  class="det1" colspan="2" style="text-align:left; padding-left: 15px;">Rep&nbsp;Name</td>
                         <td  class="det1" colspan="1" >Select<br><a href="javascript:;" onClick="selectAll('SELECT[]', 1);">All</a>&nbsp;
                                                                   <a href="javascript:;" onClick="selectAll('SELECT[]', 0);">None</a></td>
                         <td colspan="1">&nbsp</td>                                          	
                     </tr>
                     <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                         <td colspan="3">&nbsp</td>
                         <td class="det1" colspan="1" style="text-align:left";><INPUT TYPE="TEXT" size="8"  name="WAREA"    value= "" ></td>
                         <td class="det1" colspan="1" style="text-align:left";><INPUT TYPE="TEXT" size="5"  name="WDOCNO"   value= "" ></td>
                         <td class="det1" colspan="3" style="text-align:left";><INPUT TYPE="TEXT" size="15" name="WSTORE"   value= "" ></td>
                         <td class="det1" colspan="1" style="text-align:left";><INPUT TYPE="TEXT" size="10"  name="WREP"     value= "" ></td>
                         <td colspan="5">&nbsp</td>
                     </tr>
                     <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                         <td  colspan="12" style="text-align:center;">&nbsp;</td>
                     </tr>
                     <?php
                     if(count($mfDD) == 0) { ?>
                          <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                               <td  colspan="1">&nbsp</td>
                               <td  class="det3" colspan= "10" style="text-align:left; color:Red;">No Invoices selected - Use filters</td>
                               <td  colspan="1">&nbsp</td>
                          </tr> 
                          <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                               <td  colspan="12" style="text-align:center;">&nbsp;</td>
                          </tr>
                     <?php        
                     } else { 
                           foreach ($mfDD as $row) { ?>
                                <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                                     <td  colspan="1">&nbsp</td>
                                     <td class="detN12" style="text-align:left;"><?php echo $row['Status'];?></td>
                                     <td class="detN12" style="text-align:left;"><?php echo $row['invoice_date'];?></td>
                                     <td class="detN12" style="text-align:left;"><?php echo $row['Area'];?></td>
                                     <td class="detN12" style="text-align:left;"><?php echo $row['document_number'];?></td>
                                     <td class="detN12" colspan="3" style="text-align:left;"><?php echo $row['Store'];?></td>
                                     <td class="detN12" colspan="2" style="text-align:left;"><?php echo $row['first_name'];?></td>
                                     <td class="detN12" colspan="1" style="text-align:left;"><INPUT TYPE="checkbox" name="SELECT[]" value= "<?php echo str_pad($row['document_number'],10,"0",STR_PAD_LEFT) . str_pad($row['dm_uid'],10,"0",STR_PAD_LEFT) ;?>"><br></td>
                                     <td colspan="1">&nbsp</td> 
                                </tr>
                           <?php  
                           }
                     } ?>
                     <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                         <td colspan="12" style="text-align:center;">&nbsp;</td>
                     </tr>
                     <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                     	  <td  colspan="4">&nbsp</td>
                        <td class="det1" colspan="12" style="text-align:center;">Select Rep</td> 
                        <td  colspan="2">&nbsp</td>  
                     </tr>
                     <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                         <td colspan="12" style="text-align:center;">&nbsp;</td>
                     </tr>
                     <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                            <td  colspan="3">&nbsp</td>
                            <td  class="det1" colspan="1">Mail Selected</td>
                            <td  class="det1" colspan="1"><INPUT TYPE="checkbox" name="MAILSELECT" value= "" CHECKED></td>
                            <td   colspan="5" style="text-align:center;">
                            <select name="REP" id="REP">
                               <option value="Select Rep">Select Rep</option>
                               <?php foreach($repl as $row) { 
                               	?>
                                    <option value="<?php echo $row['uid']; ?>"><?php echo $row['first_name']; ?></option>
                               <?php } ?>
                            </select>
                         </td>
                         <td  colspan="1">&nbsp</td>
                         <td  colspan="1">&nbsp</td>
                     </tr>
                     <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                         <td colspan="12" style="text-align:center;">&nbsp;</td>
                     </tr>
                     <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                         <td colspan="12"style="text-align:center;"><INPUT TYPE="submit" class="submit" name="FINISH" value= "Print / Mail Aged Stock Lists"></td>
                     </tr>
                 </table>
             </form>
         </center>                                       
         <script type="text/javascript" defer>
                 function selectAll(elementName, flag) {
                       $("input[name='"+elementName+"']").each( function(){$(this).attr("checked",((flag == 1)?true:false));})
                 } 
          </script>

<?php
      }
// ********************************************************************************************************************************      

}