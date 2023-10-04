<?php

  include_once 'ROOT.php';
  include_once $ROOT.'PHPINI.php';
  require $ROOT.$PHPFOLDER."functional/main/access_control.php";
  include_once $ROOT.$PHPFOLDER.'DAO/StockDAO.php';
  include_once $ROOT.$PHPFOLDER.'DAO/AdministrationDAO.php';
  include_once $ROOT.$PHPFOLDER.'properties/Constants.php';
  include_once $ROOT.$PHPFOLDER.'libs/GUICommonUtils.php';
  include_once $ROOT.$PHPFOLDER.'libs/CommonUtils.php';
  include_once $ROOT.$PHPFOLDER.'elements/basicSelectElement.php';


  if (!isset($_SESSION)) session_start() ;
  $principalId = $_SESSION['principal_id'] ;
  $userId = $_SESSION['user_id'];
  $systemId = $_SESSION['system_id'];


  $dbConn = new dbConnect();
  $dbConn->dbConnection();


  $postFilterList = (isset($_GET["FILTERLIST"])) ? (explode(',',$_GET["FILTERLIST"])) : ("");
  $productMinorFilter = (isset($_GET['prod_minor_category'])) ? ($_GET['prod_minor_category']) : array();


  // check roles
  $adminDAO = new AdministrationDAO($dbConn);
  $hasRole = $adminDAO->hasRole($userId, $principalId, ROLE_VIEW_STOCK);
  if (!$hasRole) {
    echo "You do not have permissions to view Stock";
    return;
  }


  $stockDAO = new StockDAO($dbConn);
  if (CommonUtils::isDepotUser()) {
    $mfS = $stockDAO->getDepotPrincipalStock($userId, $principalId, $productMinorFilter);
  } else {
    $mfS = $stockDAO->getUserPrincipalStock($userId, $principalId, $productMinorFilter);
  }


?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<HTML>
<HEAD>
  <LINK href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/1_default.css' rel='stylesheet' type='text/css'>
  <script type='text/javascript' language='javascript' src='<?php echo $DHTMLROOT.$PHPFOLDER ?>js/jquery.js'></script>
  <script type='text/javascript' language='javascript' src='<?php echo $DHTMLROOT.$PHPFOLDER ?>js/dops_global_functions.js'></script>
  <script type='text/javascript' language='javascript' src='<?php echo $DHTMLROOT.$PHPFOLDER ?>js/jquery.autoscroll.js'></script>
</HEAD>
<BODY id='body'>
  <CENTER><BR>

<?php


  /************************************************************
   * START : Additional Filters
   ************************************************************/
  echo '<form id="extraFilter">';
  echo '<table width="650"><tr class="odd" style="height:30px;" ><th>Additional Filters</th></tr><tr><td>';
    basicSelectElement::getProductMinorCategoryFilter('prod_minor_category',$productMinorFilter,"N","N",$onChange=null,$onClick=null,$onMouseOver=null,$dbConn,$principalId, $systemId);
  echo '</td></tr></table>';
  echo '</form>';
  /************************************************************/


  // field names for this form
  $fldFilterListname="TranListFilter"; // the names of the filter fields
  $fldFilterListUsageArr=array(1=>"Y","Y","Y","Y","Y","Y","Y","Y","Y","Y","Y","Y","Y","Y","Y","Y","Y","Y","Y","N","N");
  $fldFilterListSizeArr=array(1=>"5","5","5","5","5","5","5","5","5","5","5","5","5","5","5","5","5","5","5","5","5");
  $headers = array(
                  'depotName' => 'Depot',
                  'stockItem' => 'Product Code',
                  'stockDescrip' => 'Product Description',
                  'goodsInTransit' => 'Goods in Transit',
                  'opening' => 'Opening Stock',
                  'arrivals' => 'Goods Received',
                  'uplifts' => 'Upliftments',
                  'returnsCancel' => 'Returns ex Cancel',
                  'returnsNC' => 'Returns @ N/C',
                  'delivered' => 'Delivered',
                  'adjustment' => 'Stock Adjust',
                  'closing' => 'Closing Stock',
                  'allocations' => 'Allocated to Orders',
                  'inPick' => 'IN - Pick Orders',
                  'available' => 'Available',
                  'blockedStock' => 'Blocked Stock',
                  'lostSalesCancel' => 'Lost Sales Cancel',
                  'lostSalesOOS' => 'Lost Sales OOS',
                  'stockCount' => 'Stock Count',
                  'stockCountDate' => 'Stock Count Date',
                  'dataGeneratedDate' => 'Data Generated Date',
                  );

  $stkListArr = $tdExtraColArr = $tdExtraRowArr = array();

  foreach ($mfS as $row) {
  	
  	if($row['allow_decimal'] == 'Y') {$opening    = $row['opening'] / 100;        } else {$opening    = $row['opening']; }
  	if($row['allow_decimal'] == 'Y') {$arrivals   = $row['arrivals'] / 100;       } else {$arrivals   = $row['arrivals']; }
  	if($row['allow_decimal'] == 'Y') {$retc       = $row['returns_cancel'] / 100; } else {$retc       = $row['returns_cancel']; }
  	if($row['allow_decimal'] == 'Y') {$delivered  = $row['delivered']/100;        } else {$delivered  = $row['delivered']; }
  	if($row['allow_decimal'] == 'Y') {$adjustment = $row['adjustment']/100;       } else {$adjustment = $row['adjustment']; }
  	if($row['allow_decimal'] == 'Y') {$closing    = $row['closing']/100;          } else {$closing    = $row['closing']; }
  	if($row['allow_decimal'] == 'Y') {$alloc      = $row['allocations']/100;      } else {$alloc      = $row['allocations']; }
  	if($row['allow_decimal'] == 'Y') {$in_pick    = $row['in_pick']/100;          } else {$in_pick    = $row['in_pick']; }
  	if($row['allow_decimal'] == 'Y') {$available  = $row['available']/100;        } else {$available  = $row['available']; }

    $data = array(
            'depotName' => $row['depot_name'],
            'stockItem' => $row['stock_item'],
            'stockDescrip' => $row['stock_descrip'],
            'goodsInTransit' => $row['goods_in_transit'],
            'opening' => $opening,
            'arrivals' => $arrivals,
            'uplifts' => $row['uplifts'],
            'returnsCancel' => $retc,
            'returnsNC' => $row['returns_nc'],
            'delivered' => $delivered,
            'adjustment' => $adjustment,
            'closing' => $closing,
            'allocations' => $alloc,
            'inPick' => $in_pick,
            'available' => $available,
            'blockedStock' => $row['blocked_stock'],
            'lostSalesCancel' => $row['lost_sales_cancel'],
            'lostSalesOOS' => $row['lost_sales_oos'],
            'stockCount' => $row['stock_count'],
            'stockCountDate' => $row['stock_count_date'],
            'dataGeneratedDate' => $row['data_generated_date'],
            );

    $stkListArr[] = $data;
    $tdExtraRowArr[]=" nowrap ";

  }

  // SYSTEM Preferences
  GUICommonUtils::systemFieldPreferenceFilter("STOCK",
                                              $systemId,
                                              $principalId,
                                              $fldFilterListUsageArr,
                                              $fldFilterListSizeArr,
                                              $headers,
                                              $stkListArr);

  //filter data.
  $pArr=GUICommonUtils::applyFilter($stkListArr,$postFilterList);


  //calculate totals
  //index names must be the same as the header array indexes.
  $totals = array(
              'goodsInTransit' => 0,
              'opening' => 0,
              'arrivals' => 0,
              'uplifts' => 0,
              'returnsCancel' => 0,
              'returnsNC' => 0,
              'delivered' => 0,
              'adjustment' => 0,
              'closing' => 0,
              'allocations' => 0,
              'inPick' => 0,
              'available' => 0,
              'blockedStock' => 0,
              'lostSalesCancel' => 0,
              'lostSalesOOS' => 0,
              'stockCount' => 0,
              );

  foreach ($pArr as $row) {
    $totals['goodsInTransit'] += (isset($row['goodsInTransit'])) ? $row['goodsInTransit'] : 0;
    $totals['opening'] += (isset($row['opening'])) ? $row['opening'] : 0;
    $totals['arrivals'] += (isset($row['arrivals'])) ? $row['arrivals'] : 0;
    $totals['uplifts']+=(isset($row['uplifts'])) ? $row['uplifts'] : 0;
    $totals['returnsCancel']+=(isset($row['returnsCancel'])) ? $row['returnsCancel'] : 0;
    $totals['returnsNC']+=(isset($row['returnsNC'])) ? $row['returnsNC'] : 0;
    $totals['delivered']+=(isset($row['delivered'])) ? $row['delivered'] : 0;
    $totals['adjustment']+=(isset($row['adjustment'])) ? $row['adjustment'] : 0;
    $totals['closing']+=(isset($row['closing'])) ? $row['closing'] : 0;
    $totals['allocations']+=(isset($row['allocations'])) ? $row['allocations'] : 0;
    $totals['inPick']+=(isset($row['inPick'])) ? $row['inPick'] : 0;
    $totals['available']+=(isset($row['available'])) ? $row['available'] : 0;
    $totals['blockedStock'] += (isset($row['blockedStock'])) ? $row['blockedStock'] : 0;
    $totals['lostSalesCancel']+=(isset($row['lostSalesCancel'])) ? $row['lostSalesCancel'] : 0;
    $totals['lostSalesOOS']+=(isset($row['lostSalesOOS'])) ? $row['lostSalesOOS'] : 0;
    $totals['stockCount']+=(isset($row['stockCount'])) ? $row['stockCount'] : 0;
  }


  // SYSTEM Preferences
  GUICommonUtils::systemFieldPreferenceFilter("STOCK", $systemId, $principalId, $x, $x, $totals, $x);


  // button row - must be own table due to button sizes being wider than output columns
  echo "<BR>";
  echo "<TABLE id='xxx'>";

        GUICommonUtils::getFilterFieldsNonAjax($fldFilterListname,
                                                $fldFilterListUsageArr,
                                                $fldFilterListSizeArr,
                                                $postFilterList,
                                                "+'&'+ $('#extraFilter').serialize()",
                                                $ROOT.$PHPFOLDER."functional/stock/viewStock.php");

        // the data
        GUICommonUtils::outputTable ($headers,
                                      $pArr,
                                      $tdExtraColArr,
                                      $tdExtraRowArr);

    //total columns
    echo "<tr class='odd' style='font-weight:bold;'>
            <th colspan='3'>TOTALS (".sizeof($pArr)." rows)</th>";
    foreach($totals as $row){
      echo "<th><b>{$row}</b></th>";
    }
    echo "  <th colspan=2></th>
          </tr>";

   echo "</TABLE>";

?>

<div id='floatHdr' style='position:absolute; z-index:100;'></div>
<BR>

<SCRIPT type="text/javascript">

  //autoscroll
  $(document).ready(function(){
    document.body.focus();
    $.autoscroll.init({step: 200});
  });

  function floatHeader() {
          var f=document.getElementById('OTHdr');
          var pos = document.getElementById('body').scrollTop;
          if (pos<220) {
                  $(f).hide();
          } else {
                  $(f).css({'position':'absolute', 'top': pos, 'display':'inline'});
          }
  }

  intervalTimer=setInterval("floatHeader();",500);

  //jquery set the widths of the REAL header to the FAKE header so that the widths are equal.
  var tW = 0;
  $('#OTHdr').prev().children('th').each(function(i, obj){
          var colW = $(obj).width();
          $('#OTHdr').children('th').eq(i).width(colW);
          tW += colW+20;
  });
  $('#OTHdr').css('width',tW);

</SCRIPT>

</CENTER>
</BODY>
</HTML>