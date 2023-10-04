<?php
    include_once('ROOT.php');
    include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER.'DAO/PrincipalProductCatDAO.php');
    include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
    include_once($ROOT.$PHPFOLDER."elements/datePickerElement.php");

class stockRollOverCatScreens {

      function __construct() {

         global $dbConn;
         $this->dbConn = $dbConn;
      }
//*************************************************************************************************************************************************************      
public function DisplayCat($principalId){
            
           $CatStockRollOverDAO = new CatStockRollOverDAO($this->dbConn);
           $PCat = $CatStockRollOverDAO->GetProductCat($principalId);
           
           $postFROMDATE  = CommonUtils::getUserDate();

           ?>
           	 <body>
               <center>
                  <FORM name='UserUpdateScreen' method=post action=''>
                     <table width="720"; style="border:none">
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td class=head1 Colspan="4"; style="text-align:center">Stock Roll Over By Category</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td width="10%"; style="border:none">&nbsp</td>
                          <td width="70%"; style="border:none">&nbsp</td>
                          <td width="10%"; style="border:none">&nbsp</td>
                          <td width="10%"; style="border:none">&nbsp</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                             <td colspan="1">&nbsp</td>                           
                             <td class="det1" colspan="2" style="text-align:left; border: none;">Roll Over Date&nbsp;&nbsp;<?php DatePickerElement::getDatePickerLibs(); DatePickerElement::getDatePicker("FROMDATE",$postFROMDATE); ?></td>
                             <td colspan="1" >&nbsp;</td>
                        </tr>                         
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                             <td Colspan="4">&nbsp</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                             <td class="det1" colspan="1">&nbsp</td>
                             <td class="det1" colspan="1"; style="text-align:centre;font-weight: bold;">Category</td>
                             <td class="det2" colspan="1"; style="text-align:centre;font-weight: bold;">Select   <br><a href="javascript:;" onClick="selectAll('CATLIST[]', 1);">All</a>
                                                                          <a href="javascript:;" onClick="selectAll('CATLIST[]', 0);">None</a></td>
                             <td Colspan="1">&nbsp</td>
                        </tr>
                                   <?php

                         foreach ($PCat as $row) {?>
                           <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                               <td Colspan="1">&nbsp</td>
                               <td class="det2" colspan="1"; style="text-align:left;"><?php echo $row['description'];?></td>
                               <td class="det2" colspan="1";style="text-align:centre;"><INPUT TYPE="checkbox" name="CATLIST[]" value= "<?php echo $row['uid'];?>"></td>     
                               <td Colspan="1">&nbsp</td>
                           </tr>
                         
                       <?php 	
                   } ?>   
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="4">&nbsp</td>
                         </tr>
                          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="4">&nbsp</td>
                         </tr>
                        
                        
                        <?php 
                        
                        ?>                              
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                             <td colspan="4"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="SUBMIT" value= "Roll Over">
                                                                         <INPUT TYPE="submit" class="submit" name="BACKFORM"   value= "Back"></td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                             <td colspan="4"; style="text-align:center;">&nbsp;</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="4">&nbsp</td>
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
	
	
	
	
	
	




//*************************************************************************************************************************************************************      
//*************************************************************************************************************************************************************      
//*************************************************************************************************************************************************************      
//*************************************************************************************************************************************************************      
//*************************************************************************************************************************************************************      
//*************************************************************************************************************************************************************      
//*************************************************************************************************************************************************************      
//*************************************************************************************************************************************************************      
//*************************************************************************************************************************************************************      
      
      
            
      
 }       