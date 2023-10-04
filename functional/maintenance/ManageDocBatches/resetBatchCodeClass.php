<?php 
include_once('ROOT.php'); 
include_once($ROOT.'PHPINI.php');	
include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
include_once($ROOT.$PHPFOLDER."DAO/resetBatchCodeDAO.php");


    class resetBatchCode{
	
      function __construct() {

         global $dbConn;
         $this->dbConn = $dbConn;
      }	
      
// ********************************************************************************************************************************	      
	public function firstform(){
		
	$col = '#FF0000' 
	
	?>
	<center>	
	 <FORM name='Select Invoice' method=post action=''>
        <table width:"720"; style="border:none">        	
           <tr>
              <td>&nbsp</td>
              <td>&nbsp</td>
           </tr>
           <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
              <td class="head1" colspan="2"; style="text-align:center;" ><strong>Reset Batch Code</td>
           </tr>
           <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
              <td colspan="2";>&nbsp</td>
           </tr>	   
            <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
              <td colspan="2" style="color: <?php echo $col;?>; text-align:center";><strong>***Enter Document Number(s) Separated By A Comma (,)</td>
           </tr>	 
           <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
              <td colspan="2";>&nbsp</td>
           </tr>    	
           <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
              <td style="text-align:right";><strong>Enter Document Number(s):</td>
              <td style="text-align:left";><input type="text" size="50" name="INVOICENUM"></td>
           </tr>
           <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
              <td colspan="2";>&nbsp</td>
           </tr>
           <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
              <td colspan="2" style="color: <?php echo $col;?>; text-align:center";><strong>***Enter One Product Code Or Leave Blank</td>
           </tr>	 
           <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
              <td colspan="2";>&nbsp</td>
           </tr>
           <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
              <td style="text-align:left";><strong>Enter Product Code:</td>
              <td style="text-align:left";><input type="text" size="20" name="PRODCODE"></td>
           </tr>
           <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
              <td colspan="2";>&nbsp</td>
           </tr>
           <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
              <td colspan="2"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="SELECT" value= "Get Document Details">
                                                          <INPUT TYPE="submit" class="submit" name="BACK" value= "Back">
                                                          <INPUT TYPE="submit" class="submit" name="CANCEL" value= "Cancel"></td>
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
	
		public function secondform($postINVOICE, $principalID, $postPRODUCT){
			
			$resetBatchCodeDAO= new resetBatchCodeDAO($this->dbConn);
			$batchCodeInfo = $resetBatchCodeDAO->getBatchCodeInfo($postINVOICE, $principalID, $postPRODUCT);
   	 	
   	 	if (count($batchCodeInfo)!==0) { 
   	 	?>
			<center>
               <FORM name='displayinv' method=post target=''>
                  <table width="720"; style="border-none";>
                  	<tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                      <td  colspan="9"; style="text-align:center" >&nbsp;</td>            	
                    </tr>
                  	<tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                  		<td class="head1" colspan="9"; style="text-align:center" ><strong>Reset Batch Code</td>  
             	      </tr>
                    <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                    	<td colspan="9";>&nbsp;</td>
                    </tr>	
                    <tr class="<?php echo GUICommonUtils::styleEO($class);?>"> 
                       <td  colspan="1"; style="text-align:left"><strong>Principal:</td>
                       <td  colspan="5"; style="text-align:left"><?php echo trim($batchCodeInfo[0]['Principal Name']);?></td>
                       <td  colspan="3"; style="text-align:left" >&nbsp;</td>    
                    </tr>          	
                    <tr class="<?php echo GUICommonUtils::styleEO($class);?>">  
                      <td  colspan="9"; style="text-align:center" >&nbsp;</td>  
                    </tr>   
                    <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                    	<td  colspan="2"; style="text-align:center"><strong>Document No</td>
                      <td  colspan="2"; style="text-align:left"><strong>Product Code</td>
                      <td  colspan="2"; style="text-align:left"><strong>Product Description</td>
                      <td  colspan="2"; style="text-align:left"><strong>Document Quantity</td>
                      <td  colspan="1"; style="text-align:left"><strong>Batch Code</td>
                    </tr>   
                    <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                    	 <td  colspan="9" >&nbsp;</td>
                    	 <?php foreach($batchCodeInfo as $drow) { 
                    	 ?>
                     	<tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     			<td colspan="1"; style="text-align:left"><INPUT TYPE="hidden" name="UID[]"  value= "<?php echo $drow['UID'];?>"></td>
                     			<td colspan="1"; style="text-align:left"><?php echo $drow['Document Number'];?></td>
                     			<td colspan="1"; style="text-align:left"><?php echo "<br>" . "<br>" ?></td> 
                       		<td colspan="1"; style="text-align:left"><?php echo $drow['Product Code'];?></td> 
                       		<td colspan="1"; style="text-align:left"><?php echo "<br>" ?></td> 
                       		<td colspan="1"; style="text-align:left"><?php echo $drow['Product Description'];?></td>
                       		<td colspan="1"; style="text-align:left"><?php echo "<br>" ?></td>  
                       		<td colspan="1"; style="text-align:left"><?php echo $drow['Document Quantity'];?></td> 
                       		<td colspan="1"; style="text-align:left"><INPUT TYPE="TEXT" size="20" name="BATCHNO[]" value= "<?php echo $drow['Batch Code'];?>"></td>
                       </tr>
                       <?php } ?>
                     </tr>
                      <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
					                 <td Colspan="9">&nbsp</td>
					           </tr>
                     <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                          <td colspan="9"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="UPDATEBATCH" value= "Update">
                          																						<INPUT TYPE="submit" class="submit" name="BACK" value= "Back">
                          																						<INPUT TYPE="submit" class="submit" name="CANCEL" value= "Cancel"></td>
                          																				
                     </tr>	
           				 </table>
							</form>
			</center> 
			<?php 
		}              
	}
		

// ********************************************************************************************************************************	
	
}