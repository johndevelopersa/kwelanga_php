<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    include_once($ROOT.$PHPFOLDER."libs/GUICommonUtils.php");
    		    
class testApiForm {
	
      function __construct() {

         global $dbConn;
         $this->dbConn = $dbConn;
      }	 
// ********************************************************************************************************************************	
      public function firstform() {
      
          $class = 'odd';
          ?>
          <center>	
              <form name='Manage Transaction' method=post action=''>
                   <table width="80%" style="border:none">        	
                      <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                          <td rowspan="3"; ><img src=<?php echo "https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelangaweb/images/kos/Kwelanga PNG Transparent version 2.png" ?> style="width:120px; height:75px; float:left;" ></td>
                          <td colspan="3";>&nbsp</td>
                      </tr>
                      <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                          <td colspan="1";>&nbsp</td>
                          <td colspan="4"; style="text-align:left; font-size: 16px; font-weight:Bold;" >Kwelanga Online Line API Test Screen </td>
                      </tr>
                      <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                          <td colspan="1";>&nbsp</td>
                          <td colspan="4"; style="text-align:left; font-size: 18px; font-weight:Bold;" >&nbsp;</td>
                      </tr>
                      <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                          <td width="10%";>&nbsp</td>
                          <td width="30%";>&nbsp</td>
                          <td width="30%";>&nbsp</td>
                          <td width="20%";>&nbsp</td>
                          <td width="10%";>&nbsp</td> 	 	 	
                      </tr>
                      <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                          <td colspan="5";>&nbsp</td>
                      </tr>	        	
                      <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                          <td>&nbsp</td>
                          <td colspan="3"; style="text-align:center; font-weight:bold; font-size:14px">Transaction Details to Update</td>
                          <td>&nbsp</td>
                      </tr>        	
                      <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                          <td colspan="5";>&nbsp</td>
                      </tr>	  
                      <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                          <td>&nbsp</td>
                          <td colspan="3"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="select"    value= "Update Customer Account">
                                                                      <INPUT TYPE="submit" class="submit" name="canform"   value= "Cancel"></td>
                          <td>&nbsp</td>
                       </tr>   
                       <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                          <td colspan="5";>&nbsp</td>
                       </tr>	  

                   </table>
              </form>
          </center>          
      <?php
      } 
}
// ********************************************************************************************************************************	
