<?php


include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER."libs/CommonUtils.php");
include_once($ROOT.$PHPFOLDER.'DAO/ProductDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/AdministrationDAO.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');


if (!isset($_SESSION)) session_start();
$userId = $_SESSION['user_id'];
$principalId = $_SESSION['principal_id'];


$postPRODUCTUID = (isset($_GET['PRODUCTUID'])) ? ($_GET['PRODUCTUID']) : false;
$postSTOREUID = (isset($_GET['STOREUID'])) ? ($_GET['STOREUID']) : false;


if($postPRODUCTUID === false || $postSTOREUID === false || empty($postPRODUCTUID) || empty($postSTOREUID)){


  //REQUIRES A PRODUCT + STORE UID ELSE DISPLAY FAILED MESSAGE
  ?>
  <br><br>
  <div align="center">
  	<h2>An Error has occurred!</h2>
  	<div>The store or product was not set or is invalid.</div>
  	<br><hr>
  </div>
  <?php
  return;


} else {


  $dbConn = new dbConnect();
  $dbConn->dbConnection();

  $adminDAO = new AdministrationDAO($dbConn);
  $returnMessages = new ErrorTO;

  $divStoreID = 'store_div';
  $divProductID = 'product_div';
  $ScheduleInnerHTML = '<span style="font-size:1em"><strong>PLEASE NOTE:</strong></span><br>
							<span style="font-size:10px">
								The store details above are for the store selected for processing according to the relevant criteria for lookup such as GLN, Pastel Account, etc.<br>
								There MAY be duplicated stores that satisfied this criteria for lookup, and these are not shown here.<br>
								If you are sure pricing exists for this store, it may be that you were finding that pricing under the alternative duplicated store which is not shown here.
							</span></div>';

  $hasRolePricing = $adminDAO->hasRole($userId,$principalId,ROLE_VIEW_PRICE);

  if (!($hasRolePricing===true)) {
  	$pricingHTML = "<h3>Error No Price was found or you do not have Permission to view pricing.</h3>";
  } else {

      //GET PRICING
      $productDAO = new ProductDAO($dbConn);
      $mfP = $productDAO->getAllEligibleActivePricesForProduct($principalId,$postSTOREUID,$postPRODUCTUID);
      if (sizeof($mfP)==0) {

        $pricingHTML = "<h3>Error No Price was found</h3>";

      } else {

       //Debug - return value.
       //echo '<pre>';
       //var_Dump($mfP);

        $pricingHTML = '<table style="border-style:double;font-size:12px;" cellspacing="0" cellpadding="6" width="850" >
        					<tr style="background-color:gray; color:white; font-weight:bold;">
        						<th style="border-right:1px solid #ccc;">Level Information</th>
        						<th style="border-right:1px solid #ccc;">List Price</th>
        						<th style="border-right:1px solid #ccc;">Discount Value</th>
        						<th style="border-right:1px solid #ccc;">Nett Price</th>
        						<th style="border-right:1px solid #ccc;">Deal Type</th>
        						<th style="border-right:1px solid #ccc;" width="80">Start Date</th>
        						<th style="border-right:1px solid #ccc;" width="80">End Date</th>
        						<th>Reference</th>
        					</tr>';
        $rows = '';
        $first = true;

        foreach($mfP as $k => $priceRow){

          $cssBrd = (($k+1) != count($mfP)) ? ('border-bottom:1px solid gray;') : ('');
          $rows .= '<tr style="font-size:10px;'.(($first===true)?('background-color:#F3F781;font-weight:bold;'):('')).'">
        						<td style="'.$cssBrd.'border-right:1px solid #ccc;white-space:nowrap;">'.(($first===true)?($priceRow['description_level'] . ' <font color="red">*Applied Price*</font><br>' . $priceRow['pricing_level']):($priceRow['description_level'] . '<br>' . $priceRow['pricing_level'] . '')).'</td>
        						<td style="'.$cssBrd.'border-right:1px solid #ccc;white-space:nowrap;">'.$priceRow['list_price'].'</td>
        						<td style="'.$cssBrd.'border-right:1px solid #ccc;white-space:nowrap;">'.$priceRow['discount_value'].'</td>
        						<td style="'.$cssBrd.'border-right:1px solid #ccc;white-space:nowrap;">'.$priceRow['price'].'</td>
        						<td style="'.$cssBrd.'border-right:1px solid #ccc;white-space:nowrap;">'.$priceRow['DealType'].'</td>
        						<td style="'.$cssBrd.'border-right:1px solid #ccc;white-space:nowrap;">'.$priceRow['start_date'].'</td>
        						<td style="'.$cssBrd.'border-right:1px solid #ccc;white-space:nowrap;">'.$priceRow['end_date'].'</td>
        						<td style="'.$cssBrd.'border-right:1px solid #ccc;white-space:nowrap;">'.$priceRow['reference'].'</td>
							</tr>';
          $first = false;
        }
        				$pricingHTML .= $rows . '</table>';

      }
  }

  $dbConn->dbClose();

}



  ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<HTML>
<HEAD>
<script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/jquery.js"></script>
<script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/dops_global_functions.js"></script>


<script type="text/javascript">

function displayStore(){
AjaxRefreshHTML("PRINCIPALSTOREUID=<?php echo $postSTOREUID; ?>",
		"<?php echo $ROOT.$PHPFOLDER; ?>functional/stores/storeCard.php",
	    "<?php echo $divStoreID; ?>",
	    "loading store...",
	    "");
}


function displayProduct(){
  AjaxRefreshHTML("PRODUCTUID=<?php echo $postPRODUCTUID; ?>",
  		"<?php echo $ROOT.$PHPFOLDER; ?>functional/products/productCard.php",
  	    "<?php echo $divProductID; ?>",
  	    "loading product...",
  	    "");
}

displayStore();
displayProduct();

</script>
<BODY style='font-family:Verdana,Arial,Helvetica,sans-serif;'>
<table align="center">
	<tr>
		<td colSpan="3"><h4>RT Pricing</H4><?php echo $pricingHTML; ?></td>
	</tr><tr>


	<td valign="top" width="520">

		<h4>Product Information</H4>
		<div id="<?php echo $divProductID; ?>"></div>
		<br>
<?php
  if(isset($_GET['DISCLAIMER'])){
    GUICommonUtils::outputBlkRed($ScheduleInnerHTML,500);
  }
?>
	</td>
	<td valign="top" width="330">

		<h4>Store Information</H4>
		<div id="<?php echo $divStoreID; ?>"></div>

		</td>
	</tr><tr>
		<td colSpan="3"><br>

		</td>
	</tr>
</table>
</BODY>

</HTML>