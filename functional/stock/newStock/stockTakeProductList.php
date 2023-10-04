<?php


include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER."DAO/StockDAO.php");
include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');
include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');


if (!isset($_SESSION)) session_start;
$principalId = $_SESSION['principal_id'];
$depotId = $_SESSION['depot_id'];
$postPRINCIPALID = false;
$postDEPOTID = false;
$postCATEGORIES = false;
$postPROJSON = false;
CommonUtils::setPostVars(); //magic function

//basic validation
//compare session vars to supplied
if($postPRINCIPALID != $principalId){
  echo "ERROR - Principal Id differs from Supplied Id!";
  return;
}
if($postDEPOTID != $depotId){
  echo "ERROR - Depot Id differs from Supplied Id!";
  return;
}


//db
$dbConn = new dbConnect();
$dbConn->dbConnection();
$stockDAO = new StockDAO($dbConn);
$categoryUIds = [];

// build up the list of category uids
foreach (json_decode(urldecode($postCATEGORIES), true) as $key => $value) array_push($categoryUIds, $value['uid']);

// get stock count for either 'ALL' products or under categories
$categoryUIdsString = implode(",", array_map('intval', $categoryUIds));
if (count($categoryUIds) !== 0 && $categoryUIds[0] != "all_products") $productArr = $stockDAO->getStockCountProducts($depotId, $principalId, $categoryUIdsString);
else $productArr = $stockDAO->getStockCountProducts($depotId, $principalId);

//convert product list
if($postPROJSON!=false) $jsonArray = @json_decode(html_entity_decode(trim($postPROJSON)),true);

//build rows
$varRows = array();
$okRows = array();
foreach($productArr as $pro){

  $hasVariance = false;
  $val = 0;
  $var = 0;

  if(isset($jsonArray[$pro['product_uid']])){
    $val = $jsonArray[$pro['product_uid']]['p'];
    $hasVariance = ($jsonArray[$pro['product_uid']]['v']=="N") ? false : true;
    $var = (!$hasVariance)?'<img src="'.$ROOT.'images/tick_yes.gif" width="12" height="12" alt="no variances">':(($jsonArray[$pro['product_uid']]['v']>0)?('+'.$jsonArray[$pro['product_uid']]['v']):($jsonArray[$pro['product_uid']]['v']));
  }

  $class='odd';
  $htmlRow = '<tr class="' . GUICommonUtils::styleEO($class) . ' '. (($hasVariance)?"hasVariance":"") .'">';
  $htmlRow .= '<td height="30">'.(($hasVariance)?'<a href="javascript:displayProductAudit('.$pro['product_uid'].')">'.$pro['product_code'].'</a>':$pro['product_code']).'</td>';
  $htmlRow .= '<td>'.$pro['product_description'].'</td>';
  $htmlRow .= '<td nowrap><input type="text" onchange="submitStockCount(null, true)" maxlength="10" size="6" value="'.$val.'" name="PRODCOUNT['.$pro['product_uid'].']" class="large-input highlightMe"></td>';
  if(($postPROJSON!=false) && ($jsonArray!=null)){
    $htmlRow .= '<td nowrap>'.($jsonArray[$pro['product_uid']]['s']).'</td>';
    $htmlRow .= '<td nowrap><strong>'.$var.'</strong></td>';
  }
  $htmlRow .= '</tr>';

  if($hasVariance){
    $varRows[$pro['product_uid']] = $htmlRow; //use uids as keys for print section
  } else {
    $okRows[] = $htmlRow;
  }

}

if($postPROJSON==false){

  //table with blank counts
  echo '<table width="600">
          <thead>
            <tr>
              <th>Product Code</th>
              <th>Product Description</th>
              <th>Count</th>';
  echo '</tr>
          </thead>' . join('', $okRows)  .
  '</table>';


} else {


  //variance table
  echo '<h2>Variance(s) Found!</h2>

    <div align="left" style="width:560px;text-align:left;margin:0px;padding:0px;">
      The system cannot rollover your stock as variance(s) exist, you will need to:
      <ul>
        <li>EITHER re-count the below products and submit the new count below!<br>
          <a href="javascript:;" onClick="displayProductPrint(\'VARDISPLAYIMAGE\',\''.join(',',array_keys($varRows)).'\')" >Print variances only</a>
          <input type="checkbox" id="VARDISPLAYIMAGE"> Include Product photos<br><br></li>
        <li>OR account for variances by capturing stock adjustments, then click submit again to compare!
          <br><a href="'.$ROOT.$PHPFOLDER.'home.php" target="_blank" >Capture Stock Adjustment</a><br><a href="#submit">Jump to Submit</a>
        </li>
      </ul>
    </div>

  <br>

  <div style="text-align:right;width:600px;">
    <a href="javascript:;" onClick="displayPrintVariances()">[PRINT]</a>
  </div>
  <span id="product-variance-table">
  <table width="600">
     <thead>
       <tr>
         <th>Product Code</th>
         <th>Description</th>
         <th>Count</th>
         <th>System</th>
         <th>Variance</th>
       </tr>
     </thead>' .
    join('', $varRows) . '<tr><th colSpan="5"></th></tr>' .
    join('', $okRows)  .
  '</table>
  </span>';

}
