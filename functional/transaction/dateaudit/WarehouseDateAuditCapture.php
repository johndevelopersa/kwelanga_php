<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER."DAO/StockDAO.php");
    include_once($ROOT.$PHPFOLDER."properties/Constants.php");
    include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
		include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');
    include_once($ROOT.$PHPFOLDER."elements/datePickerElement.php");
    include_once($ROOT.$PHPFOLDER.'elements/basicSelectElement.php');

     //Create new database object
     $dbConn = new dbConnect(); 
     $dbConn->dbConnection();

?>
<!DOCTYPE html>
<HTML>
	<HEAD>

    <TITLE>Document Selection</TITLE>

    <link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/1_default.css' rel='stylesheet' type='text/css'>
    <script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/jquery.js"></script>
    <script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/dops_global_functions.js"></script>
    </HEAD>
<body>

<?php

    if (!isset($_SESSION)) session_start() ;
      $userUId     = $_SESSION['user_id'] ;
      $principalId = $_SESSION['principal_id'] ;
      $depotId     = $_SESSION['depot_id'] ;
      $systemId    = $_SESSION["system_id"];
      $systemName  = $_SESSION['system_name'];
      
 
     if (isset($_POST['product'])) $postProduct = $_POST['product']; else $postProduct="";
     $class = 'odd';
     $stockDAO = new StockDAO($dbConn);
     $mfCS = $stockDAO->getDateAuditProducts($principalId, $depotId, ''); 
     
 	 
     if (isset($_POST['select']) || isset($_POST['nextexpire']) || isset($_POST['nextproduct']) ) {
     	
        if (isset($_POST['select'])) {
            $lstprod = $postProduct;
            $rowcount = 0;
         }  elseif (isset($_POST['nextexpire']) || isset($_POST['nextproduct']) ) {

             if (isset($_POST['storeprod'])) {
                $lstprod = $_POST['storeprod'];
             }  
             if (isset($_POST['rowcnt'])) {
                $rowcount = $_POST['rowcnt'];
                $rowcount = $rowcount +1 ;  
             }
             if (isset($_POST['FROMDATE'])) {
                $batchdate = $_POST['FROMDATE'];
             }  
             include_once($ROOT.$PHPFOLDER."DAO/PostStockDAO.php");
             $postStockDAO = new PostStockDAO($dbConn);
             $rTO = $postStockDAO->insertStockAuditRow($depotId, 
                                                       $principalId, 
                                                       $userUId, 
                                                       date("Y-m-d"),
                                                       $batchdate, 
                                                       $lstprod, 
                                                       $rowcount, 
                                                       $_POST['SCOUNT']);
             if ($rTO->type!=FLAG_ERRORTO_SUCCESS) {
                $dbConn->dbinsQuery("rollback");
             ?>
                 <script type='text/javascript'>parent.showMsgBoxError('<?php echo $rTO->description;?>')</script> 
       	     <?php
            } else {
                 $dbConn->dbinsQuery("commit");
            }       	
         }
         if($lstprod == 'Select Product') {?>
          <script type='text/javascript' >parent.showMsgBoxError('No Product Selected - Please try again')</script>    
     <?php
           return;
         }
         
         if (!isset($_POST['nextproduct'])){

            $stockDAO = new StockDAO($dbConn);
            $mfCS = $stockDAO->getDateAuditProducts($principalId, $depotId, $lstprod);      
     	      $postFROMDATE = CommonUtils::getUserDate();
?>
            <center>
	            <form name='Enter Counts' method=post action=''>

                <table style="width:550px; border:none" >  
                  <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td style="width:3%;"><input type="hidden" name="rowcnt" value=   " <?php echo $rowcount ?> "></td>
                     <td style="width:30%;"><input type="hidden" name="storeprod" value=  " <?php echo $lstprod ?>  "></td>
                     <td style="width:50%;">&nbsp;</td>
                     <td style="width:15%;">&nbsp;</td>
                     <td style="width:2%;">&nbsp;</td>
                  </tr>
                  <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td>&nbsp;</td>
                     <td style="font-size: 1.1em; font-weight: bold;">Product Code</td>
                     <td style="font-size: 1.1em; font-weight: bold;">Product</td>
                     <td style="font-size: 1.1em; font-weight: bold;">Closing</td>
                     <td>&nbsp;</td>
                  </tr>
                  <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td>&nbsp;</td>
                     <td style="font-size: 1.0em; font-weight: bold;"><?php echo $mfCS[0]['stock_item'] ?></td>
                     <td style="font-size: 1.0em; font-weight: bold;"><?php echo $mfCS[0]['stock_descrip'] ?></td>
                     <td style="font-size: 1.0em; font-weight: bold;"><?php echo $mfCS[0]['closing'] ?></td>
                     <td>&nbsp;</td>
                  </tr>
                  <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td>&nbsp;</td>
                     <td style="font-size: 1.0em; font-weight: normal;">Expiry Date</td>
                     <td style="font-size: 1.0em; font-weight: normal;">Count</td>
                     <td>&nbsp;</td>
                     <td>&nbsp;</td>

                  </tr>
                  <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td>&nbsp;</td>
                     <td style="border:none" ><?php DatePickerElement::getDatePickerLibs(); DatePickerElement::getDatePicker("FROMDATE",$postFROMDATE); ?> </td>
                     <td style="border:none" ><input type="text" name="SCOUNT" autofocus value="0"></td>
                     <td>&nbsp;</td>
                     <td>&nbsp;</td>
                 </tr>
                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td>&nbsp;</td>
                     <td>&nbsp;</td>
                     <td>&nbsp;</td>
                     <td>&nbsp;</td>
                     <td>&nbsp;</td>
                 </tr>
                </table>
                <table style="width:550px; border:none" >  
                 <tr>
                     <td style="width:20%;">&nbsp;</td>
                     <td style="width:20%;">&nbsp;</td>
                     <td style="width:20%;">&nbsp;</td>
                     <td style="width:20%;">&nbsp;</td>
                     <td style="width:20%;">&nbsp;</td>
                </tr>
                <tr>
                     <td>&nbsp;</td>
                     <td><INPUT TYPE="submit" class="submit" name="nextexpire" value= "Next Expiry Date"></td>
                     <td>&nbsp;</td>
                     <td><INPUT TYPE="submit" class="submit" name="nextproduct" value= "Next Product"></td>
                     <td>&nbsp;</td>
                </tr> 
                </table>
              </form>
            </center>
<?php
}
         }  
     if (!isset($_POST['nextexpire']) && ($postProduct == '')) {
?> 
      <center>
	      <form name='Select Product' method=post action=''>
          <table style="width:550px; border:none" >  
           <tr>
             <td colspan"0"; style="font-weight:normal;font-size:2em;text-align:center"; font-family: Calibri, Verdana, Ariel, sans-serif; >Select Product</td>
           </tr>
           <tr>
             <td>&nbsp</td>
           </tr>	        	
         </table>
          <table style="width:550px; border:none" >        	
           <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
             <td>&nbsp</td>
             <td>&nbsp</td>
             <td>&nbsp</td>
             <td>&nbsp</td>
             <td>&nbsp</td>
           </tr>
           <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
             <td Colspan="5">&nbsp</td>
           </tr>
             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
               <td>Select Product</td>
               <td><select name="product" id="product">
                      <option value="Select Product">Select Product</option>
                      <?php foreach($mfCS as $row) { ?>
                         	<option value="<?php echo $row['principal_product_uid']; ?>"><?php echo $row['stock_item'] ." - " . $row['stock_descrip']; ?></option>
                      <?php } ?>
               </select>
               </td>
               <td>&nbsp</td>
               <td>&nbsp</td>
               <td>&nbsp</td>
             </tr>  
             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
        	       <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="select" value= "Capture Expiry Dates"></td>
             </tr>          
             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="5">&nbsp</td>
             </tr>  
         </table>
        </form>
     </center> 

<?php
   }
?>
	</body>       
 </HTML>
 
