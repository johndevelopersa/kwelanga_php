<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER."DAO/ProductDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/CommonDAO.php");
include_once($ROOT . $PHPFOLDER . 'DAO/DepotDAO.php');

if (!isset($_SESSION)) session_start() ;
$userId = $_SESSION['user_id'] ;
$principalId = $_SESSION['principal_id'] ;
$principalName = $_SESSION['principal_name'] ;

//Create new database object
$dbConn = new dbConnect(); $dbConn->dbConnection();

if (isset($_GET['PRODUCTUID'])) $postPRODUCTUID=mysql_real_escape_string(htmlspecialchars($_GET['PRODUCTUID']));
else if (isset($_POST['PRODUCTUID'])) $postPRODUCTUID=mysql_real_escape_string(htmlspecialchars($_POST['PRODUCTUID']));
else $postPRODUCTUID="";

$productDAO = new ProductDAO($dbConn);
// this also doubles as the security check because this sql joins on user_principal_depot
$mfP = $productDAO->getUserPrincipalProductItem($principalId, $postPRODUCTUID,$userId);

if (sizeof($mfP)==0) {
	echo "You do not have access to this information, or product does not exist.";
	return;
}


//Get Product Category Name.
$productCategoryName = '';
if(!empty($mfP[0]['major_category'])){
  $mfPCat = $productDAO->getProductCategoryItem($mfP[0]['major_category']);
  if(isset($mfPCat[0]['description'])){
    $productCategoryName = $mfPCat[0]['description'];
  }
}

?>
<HTML>
<HEAD>
</HEAD>
<BODY style='width:300px; font-family:Verdana,Arial,Helvetica,sans-serif;'>

<TABLE style='border-style:double;' cellpadding="2">
<TR>
<TD colspan="2" style='text-align:center; background-color:gray; color:white; font-weight:bold; font-size:0.8em;'>Product Information</TD>
</TR>

<TR>
	<TD style='font-weight:bold; font-size:0.6em;'>Principal:</TD>
	<TD style='font-weight:normal; font-size:0.6em;'><?php echo $principalName; ?></TD>
</TR>
<TR>
	<TD style='font-weight:bold; font-size:0.6em;'>Product Code:</TD>
	<TD style='font-weight:normal; font-size:0.6em;'><?php echo $mfP[0]['product_code']; ?></TD>
</TR>
<TR>
	<TD style='font-weight:bold; font-size:0.6em;'>Alternate Product Code:</TD>
	<TD style='font-weight:normal; font-size:0.6em;'><?php echo $mfP[0]['alt_code']; ?></TD>
</TR>
<TR>
	<TD style='font-weight:bold; font-size:0.6em;'>Product Description:</TD>
	<TD style='font-weight:normal; font-size:0.6em;'><?php echo $mfP[0]['product_description']; ?></TD>
</TR>
<TR>
	<TD style='font-weight:bold; font-size:0.6em;'>Product Category:</TD>
	<TD style='font-weight:normal; font-size:0.6em;'><?php echo $productCategoryName; ?></TD>
</TR>
<TR>
	<TD style='font-weight:bold; font-size:0.6em;'>Weight:</TD>
	<TD style='font-weight:normal; font-size:0.6em;'><?php echo $mfP[0]['weight']; ?></TD>
</TR>
<TR>
	<TD style='font-weight:bold; font-size:0.6em;'>VAT Rate:</TD>
	<TD style='font-weight:normal; font-size:0.6em;'><?php echo $mfP[0]['vat_rate']; ?></TD>
</TR>


<TR>
	<TD style='font-weight:bold; font-size:0.6em;'>Depot linked GTIN:</TD>
	<TD style='font-weight:normal; font-size:0.6em;padding:0px;' rowSpan="3">

<?php

function buildCells($csvValues){

  if($csvValues == ''){
    echo '<TD>&nbsp;</TD>';
  } else {

    $valArr = explode(',',$csvValues);
    $i = 0;
    $css = '';
    foreach($valArr as $val){
      if($i != 0)$css = 'style="border-left:1px solid #999"';
      echo '<TD '.$css.'>',($val!='')?($val):('&nbsp;').'</TD>';
      $i++;
    }
  }
}

function buildDepotCells($csvValues){

  global $dbConn;

  //Build Depot
  $depotDAO = new DepotDAO($dbConn);
  $depotsArr = $depotDAO->getAllDepotsArray();  //a.uid, a.code, a.name depot_name

  if($csvValues == ''){
    echo '<TD>All Depots</TD>';
  } else {

    $valArr = explode(',',$csvValues);
    $i = 0;
    $css = '';
    foreach($valArr as $val){

    $depotName = '';
    foreach($depotsArr as $depotItem){
      if($depotItem['uid'] == $val){
        $depotName = $depotItem['depot_name'];
      }
    }
    if($depotName == '') $depotName = 'All Depots';

      if($i != 0)$css = 'style="border-left:1px solid #999"';
      echo '<TD '.$css.'>',$depotName.'</TD>';
      $i++;
    }
  }
}


?>
      	<TABLE style='font-weight:normal; font-size:1em;border:0px;' border="0" cellpadding="3" cellspacing="0">
    	  <TR><?php buildDepotCells($mfP[0]['gtin_depot_uid_list']); ?></TR>
    	  <TR><?php buildCells($mfP[0]['sku_gtin_list']); ?></TD></TR>
    	  <TR><?php buildCells($mfP[0]['outer_casing_gtin_list']); ?></TD></TR>
		</TABLE>

	</TD>
</TR>
<TR>
	<TD style='font-weight:bold; font-size:0.6em;'>SKU GTIN (EAN)</TD>
</TR>
<TR>
	<TD style='font-weight:bold; font-size:0.6em;'>Outercasing GTIN:</TD>
</TR>


<TR>
	<TD style='font-weight:bold; font-size:0.6em;'>Items per Case:</TD>
	<TD style='font-weight:normal; font-size:0.6em;'><?php echo $mfP[0]['items_per_case']; ?></TD>
</TR>
<TR>
	<TD style='font-weight:bold; font-size:0.6em;'>Enforce Pallet Consignment:</TD>
	<TD style='font-weight:normal; font-size:0.6em;'><?php echo $mfP[0]['enforce_pallet_consignment']; ?></TD>
</TR>
<TR>
	<TD style='font-weight:bold; font-size:0.6em;'>Units Per Pallet:</TD>
	<TD style='font-weight:normal; font-size:0.6em;'><?php echo $mfP[0]['units_per_pallet']; ?></TD>
</TR>


<TR>
	<TD style='font-weight:bold; font-size:0.6em;'>Status:</TD>
	<TD style='font-weight:normal; font-size:0.6em;'><?php echo $mfP[0]['status']; ?></TD>
</TR>

<!-- footer -->
<TR>
<TD colspan="2" style='text-align:center; color:grey; font-weight:normal; font-size:0.55em;'><script type="text/javascript">var d = new Date(); document.write("<b>" + d.getDate() + "/" + d.getMonth() + "/" + d.getFullYear() + "&nbsp;&nbsp;" + d.getHours() + ":" + d.getMinutes() + ":" + d.getSeconds() + "</b>");</script></TD>
</TR>
</TABLE>

</BODY>

</HTML>

<?php
$dbConn->dbClose();
?>