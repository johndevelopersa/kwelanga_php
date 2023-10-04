<?php
    include_once('ROOT.php');
    include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER.'DAO/StockByCatDAO.php');
    include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");

    include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
    
    
  

class StockByCatScreens {
 function __construct() {

         global $dbConn;
         $this->dbConn = $dbConn;
      }
      
//***************************************************START**********************************************************************************
 public function CategorySelect($principalId){
       
          $StockByCatDAO = new StockByCatDAO($this->dbConn);
          $Categories = $StockByCatDAO->GetCat($principalId);
 	  ?>
 	  <body >
          <center>
              <FORM name='CatSelect' method=post action=''>
                   <table width="720"; style="border:none">
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td class=head1 Colspan="5"; style="text-align:center">Stock Count By Category</td>
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
                        	<td colspan="1"; style="text-align:right">Select Category</td> 
                          <td colspan="2"; style="text-align:left";>
                          	      	     <select name="CATEGORYDROP" id="CATEGORYDROP">
                                             <option value="Select Category">Select Category</option>
                                             <?php foreach($Categories as $row) { ?>
                                                   <option value="<?php echo trim($row['uid']); ?>"><?php echo trim($row['description']); ?></option>
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
                          <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="SELECTCAT" value= "Proceed to Count Capture">&nbsp;&nbsp 
                          	                                          <INPUT TYPE="submit" class="submit" name="PRINTLIST" value= "Print Count Sheet">        
                          	                                          <INPUT TYPE="submit" class="submit" name="BACKFORM"   value= "Back">  
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
//**********************************************************GET PRODUCT LIST AND DISPLAY COUNT CAPTURE********************************************************************************
public function ProductList($cat, $userUId, $principalId, $wareHouseCde) {
    
    $StockByCatDAO = new StockByCatDAO($this->dbConn);
    $Products = $StockByCatDAO->GetProducts($cat, $principalId, $wareHouseCde);
    
     $StockByCatDAO = new StockByCatDAO($this->dbConn);
     $Saved = $StockByCatDAO-> GetAutoSavedData($userUId,$cat);
     
?>
    	 <body>
          <center>
              <FORM name='CaptureCountScreen' method=post action=''>
                   <table width="720"; style="border:none">
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td class=head1 Colspan="5"; style="text-align:center">Capture Stock Count</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            <td width="5%"; style="border:none">&nbsp</td>
                            <td width="30%"; style="border:none">&nbsp</td>
                            <td width="50%"; style="border:none">&nbsp</td>
                            <td width="10%"; style="border:none">&nbsp</td>
                            <td width="5%" ; style="border:none">&nbsp</td>
                        </tr>  
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            <td Colspan="5">&nbsp</td>
                        </tr>
                        
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>                        
                          <td Colspan="1">&nbsp</td>
                          <td colspan="1"; style="text-align:centre;font-weight: bold;">Product Code</td>                          
                          <td colspan="1"; style="text-align:centre;font-weight: bold;">Description</td>
                          <td colspan="1"; style="text-align:centre;font-weight: bold;">Count</td>
                          <td Colspan="1">&nbsp</td>
                        </tr>
                        
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                        </tr>
                        
                        <?php
                          $count = 0;
                          foreach ($Products as $row) { ?>
                          	 <?php $count++;?>
                             <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                                 <td Colspan="1">&nbsp</td>
                                 <td class="detN12"colspan="1"; style="text-align:left;"><?php echo $row['product_code'];?></td>
                                 <td class="detN12"colspan="1"; style="text-align:left;"><?php echo $row['product_description'];?></td>    
                                 <?php
                                 $Oldc = 0; 
                                 if (count($Saved)!=0){
                                      foreach ($Saved as $srow) {
                          
                                             if ($srow['product_uid']==$row['uid']) {
                                                 $Oldc = $srow['count'];
                                                 break; 
                                             }
                                      }
                                } ?>
                                <td class="detN12" colspan="1";style="text-align:centre;"><input type="text" name="myInput[]" id="myInput[]" onchange="myFunction()" value= "<?php echo $Oldc?>" ></td>   
	                              <td Colspan="1"><input type="hidden" name="CAT" value=<?php echo $cat; ?>>
	                              	              <input type="hidden" name="PRUID[]" value=<?php echo $row['uid'];?>></td>
                            </tr>
                            <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                <td Colspan="5">&nbsp</td>
                            </tr>
                           <?php                       
                           } ?> 
    
                          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                              <td Colspan="5">&nbsp</td>
                          </tr>
                          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                              <td Colspan="5">&nbsp</td>
                          </tr>
                          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                              <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="SAVECOUNTS"  id="SAVECOUNTS"  value= "Save Stock Counts">
                              	                                          <INPUT TYPE="submit" class="submit" name="CLEARCOUNTS" id="CLEARCOUNTS" value= "Clear Stock Counts">                                    	                                           
                                                                          <INPUT TYPE="submit" class="submit" name="BACKFORM"   value= "Back"></td>
                          </tr>
                          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                              <td colspan="5"; style="text-align:center;">&nbsp;</td>
                          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                              <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="DISPLAYVARS" value= "Show All Variances">
                                                                          <INPUT TYPE="submit" class="submit" name="SHOWNEG"     value= "Show Negative Variances">
                                                                          <INPUT TYPE="submit" class="submit" name="SHOWPOS"     value= "Show Positive Variances"></td>
                          </tr>
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
//*************************************************DISPLAY VARIANCES *****************************************************************************************
public function DisplayVariances($varianceList, $hasVariances, $selectVarType) {  ?>
    	 <body>
          <center>
              <FORM name='CaptureCountScreen' method=post action=''>
                   <table width="90%"; style="border:none">
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td class=head1 Colspan="7"; style="text-align:center">Show Stock Count Variances</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td width="5%"; style="border:none">&nbsp</td>
                          <td width="20%"; style="border:none">&nbsp</td>
                          <td width="40%"; style="border:none">&nbsp</td>
                          <td width="10%"; style="border:none">&nbsp</td>
                          <td width="10%"; style="border:none">&nbsp</td>
                          <td width="10%"; style="border:none">&nbsp</td> 
                          <td width="5%" ; style="border:none">&nbsp</td>
                        </tr>                                        
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="7">&nbsp</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                        	<td Colspan="1">&nbsp</td>                         
                          <td colspan="1"; style="text-align:centre;font-weight: bold;">Product Code</td>
                          <td colspan="1"; style="text-align:centre;font-weight: bold;">Description</td>
                          <td colspan="1"; style="text-align:centre;font-weight: bold;">System</td>
                          <td colspan="1"; style="text-align:centre;font-weight: bold;">Count</td>
                          <td colspan="1"; style="text-align:centre;font-weight: bold;">Variance</td>
                          <td Colspan="1">&nbsp</td> 
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="7">&nbsp</td>
                        </tr>
                        <?php          
                        $vr = array()  ;                                                
                        foreach ($varianceList as $row) { ?>

                                <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                                	  <td Colspan="1">&nbsp</td>  
                                    <td class="detN12"colspan="1"; style="text-align:left;"><?php echo $row['product_code'];?></td>
                                    <td class="detN12"colspan="1"; style="text-align:left;"><?php echo $row['product_description'];?></td>                          
                                    <td class="detN12" colspan="1";style="text-align:centre;"><?php echo $row['closing'];?></td>
                                    <td class="detN12" colspan="1";style="text-align:centre;"><?php echo $row['count'];?></td>   
                                    <?php
                                    if($row['adjTyp'] == 0) { ?>
                                         <td class="detN12" colspan="1";style="text-align:centre;"style="color:green";><?php echo (($row['count']) - ($row['closing']));;?></td>                                                                                  
                                    <?php
                                    } else { ?>
                         	               <td class="detN12" colspan="1";style="text-align:centre;"style="color:red";><?php echo (($row['count']) - ($row['closing']));;?></td>  
                         	          <?php
                                    }
                                    $vr[] = $row['ppUid'] . '!' . $row['count'];                                    
                                    ?>
                                    <td Colspan="1">&nbsp</td>                        
                                 </tr>
                                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                     <td Colspan="7"><input type="hidden" name="CAT"   value=<?php echo $row['major_category']; ?>>
                                     	               <input type="hidden" name="PRNID" value=<?php echo $row['principal_uid']; ?>>
                                     	               <input type="hidden" name="DEPID" value=<?php echo $row['depot_id']; ?>></td>
                                 </tr>
                                  <?php 	 $countc++;

                        } ?>   
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            <td Colspan="7">&nbsp</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            <td Colspan="7">&nbsp</td>
                        </tr>                               
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                             <td colspan="7"; style="text-align:center;"><?php if($hasVariances == 'N')   { ?><INPUT TYPE="submit" class="submit" name="ROLLOVER" value= "Roll Over Stock"> <?php } ?>                    	                             	                                           
                                                                                                              
                                                                         <?php if($selectVarType == '3') { ?><INPUT TYPE="submit" class="submit" name="INCADJ" value= "Auto Increase Adjustment"> <?php } ?>
                                                                         <?php if($selectVarType == '2') { ?><INPUT TYPE="submit" class="submit" name="DECADJ" value= "Auto Decrease Adjustment"> <?php } ?>
                                                                                                             <INPUT TYPE="submit" class="submit" name="BACKCOUNT"   value= "Back To Counts">
                                                                                                             <INPUT TYPE="submit" class="submit" name="PRINTVARIANCES"   value= "Print Variances"></td>                                                          
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            <td Colspan="7"><input type="hidden" name="VARLIST" value=<?php echo json_encode($vr); ?>>
                            	              <input type="hidden" name="VARTYPE" value=<?php echo $selectVarType; ?>></td>
                        </tr>
                   </table>
              </FORM>
          </center>
      </body>
    
    <?php	
    ?>  	
    <script>
    	    function myFunction() {
                let text = document.getElementById("myInput[]").value;                              
                SAVECOUNTS.click();                                                             
          }  
    </script>
    <script>    
         function myFunction2() {
               var checkBox = document.getElementById("myCheckNEG");
               var text = document.getElementById("text");
               if (checkBox.checked == true){
                   SHOWNEG.click();       
               } else {
                   text.style.display = "none";
               }
        }
    </script>
    <script>   
        function myFunction3() {
            var checkBox = document.getElementById("myCheckPOS");
            var text = document.getElementById("text");
            if (checkBox.checked == true){
               SHOWPOS.click();  	
            } else {
               
            }
        }
    </script>
    <script>   
        function myFunction4() {
            var checkBox = document.getElementById("myCheckALL");
            var text = document.getElementById("text");
            if (checkBox.checked == true){
                 SHOWALL.click();  	
           } else {
               
           }
        }
    </script>    
<?php
}
     
      
//********************************************************END**********************************************************************************


}      