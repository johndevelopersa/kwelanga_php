<?php
    include_once('ROOT.php'); 
    include_once($ROOT.'PHPINI.php');
    include_once($ROOT.$PHPFOLDER."libs/GUICommonUtils.php");
    
    class resetDocumentStatus{
	
      function __construct() {

         global $dbConn;
         $this->dbConn = $dbConn;
      }	
      
// ********************************************************************************************************************************	

	public function firstform(){
		
	?>
	<center>	
	 <FORM name='Select Invoice' method=post action=''>
        <table width:"720"; style="border:none">        	
           <tr>
              <td>&nbsp</td>
              <td>&nbsp</td>
           </tr>
           <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
              <td class="head1" colspan="2"; style="text-align:center;" ><strong>Reset Document Status</td>
           </tr>
           <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
              <td colspan="2";>&nbsp</td>
           </tr>	        	
           <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
              <td style="text-align:right";><strong>Enter Document Number:</td>
              <td style="text-align:left";><input type="text" name="INVOICE"></td>
           </tr>
           <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
              <td colspan="2";>&nbsp</td>
           </tr>
           <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
              <td colspan="2"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="select" value= "Get Document Details"></td>
           </tr>          
           <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
              <td colspan="2";>&nbsp</td>
           </tr>  
        </table>
		</form>
    </center> 
	</body>       
 	</HTML>
	<?php    
			
	}		
// ********************************************************************************************************************************	
			

}