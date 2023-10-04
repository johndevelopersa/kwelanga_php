<?php


include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');
include_once($ROOT.$PHPFOLDER.'DAO/StockDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/ProductDAO.php');
include_once($ROOT.$PHPFOLDER."DAO/AdministrationDAO.php");


if (!isset($_SESSION)) session_start();
$userId = $_SESSION['user_id'];
$userCategory = $_SESSION['user_category'];
$principalId = $_SESSION['principal_id'];
$principalName = $_SESSION['principal_name'];
$systemId = $_SESSION['system_id'];
$systemName = $_SESSION['system_name'];


//only available to depot category users
if(!CommonUtils::isDepotUser()){  //available to only depot users - see above check!
  echo "<h3>Restricted Access</h3>Only Depot users are allowed to do stock take!";
  return;
}

//these session vars are only avail for depot users - check above.
$depotName = $_SESSION['depot_name'];
$depotId = $_SESSION['depot_id'];


$dbConn = new dbConnect();
$dbConn->dbConnection();


/*-----------------------*
 *      PERMISSIONS
 *-----------------------*/

if(!CommonUtils::isAdminUser()){  //super level.

  //role
  $adminDAO = new AdministrationDAO($dbConn);
  $hasRole = $adminDAO->hasRole($userId, $principalId, ROLE_STOCK_TAKE);
  if (!$hasRole) {
    echo "You do not have permissions to preform Stock Take";
    return;
  }

}


//already in stock mode.
$stockDAO = new StockDAO($dbConn);
$stockMode = $stockDAO->checkStockMode($principalId, $depotId);


?>
<HTML>
<HEAD>
<script type='text/javascript' language='javascript' src="<?php ECHO $DHTMLROOT.$PHPFOLDER ?>js/jquery.js"></script>
<script type='text/javascript' language='javascript' src="<?php ECHO $DHTMLROOT.$PHPFOLDER ?>js/dops_global_functions.js"></script>

<LINK href="<?php ECHO $DHTMLROOT.$PHPFOLDER ?>css/1_default.css" rel='stylesheet' type='text/css'>

<style type="text/css">

  .wrap {width:280px;}
  .start, .bigbutton{
    display:block;
    margin-top:5px;
    padding:14px 0px;
    border:2px solid #DF0101;
    background:#FA5858;
    color:#fff;
    text-decoration:none;
    font-size:22px;
    font-weight:bold;
  }
  .bigbutton {
    border:2px solid lightskyblue;
    background:aliceBlue;
    color:#047;
  }
  .start.enable{background:lightskyblue;border-color:#047}
  .start:hover{background:#F7BE81; border:2px solid #FE9A2E;text-decoration:none;}
  .bigbutton:hover{color:#fff;background:#F7BE81; border:2px solid #FE9A2E;text-decoration:none;}
  .large-input{line-height:20px;height:20px;font-size:12px;padding:0px 2px;}
  #RowHighlight, #RowHighlight td{background:#FCFFB4;}
  .hasVariance, .hasVariance td{color:#B40404;}
  .hasVariance td a {color:#B40404;text-decoration:underline;}
  .hasVariance td a:hover {text-decoration:none;}
</style>

</HEAD>

<BODY id='body'>

<div align="center">

  <?php
  	GUICommonUtils::getSteps(array("Start",
                                       "Print Stock Items",
                                       "Stock Count<br>&amp; Variances",
                                       "Rollover")
                                 );
  ?>

  <div id='step1'>

    <?php if($stockMode){ ?>

    <br><br><br><br><br>
    <a href="javascript:;" onClick="disableStep(5);disableStep(3);enableStep(2);toggleSteps(2,'<?php echo $ROOT.$PHPFOLDER ?>');" class="wrap bigbutton rdCrn5">Continue</a>
    <br>
    <strong style="color:#888;"><?php echo $principalName . "<br>at depot " . $depotName ?> is in stock take mode!</strong>

    <?php } else { ?>

    <br><br><br><br>
    Welcome to stock take
    <a href="javascript:;" onClick="startStockTake()" class="wrap start rdCrn5">Start Stock Take</a>

    <br><br>
    <div class="wrap" >
      <table class="tableReset"><tr>
      <td valign="top" style="padding:0px;width:30px"><input type="checkbox" id="stocktaketc"></td>
      <td style="padding:0px;color:#555" align="left">I understand that proceeding will freeze any transactions for <?php echo "<strong>" . $principalName . "</strong> at depot <strong>" . $depotName ?></strong></td>
      </tr></table>
    </div>

    <?php } ?>
  </div>


  <div id='step2'>

    <div id="proceed2" style="display:none;" class="wrap" >
      <br><br><br><br>
      click to print the product listing sheet
      <a href="javascript:;" class=" bigbutton rdCrn5" onClick="displayProductPrint('DISPLAYIMAGE')" >
      <img src="<?php echo $ROOT.$PHPFOLDER ?>/images/print-icon.png" width="32" height="32" border="0" alt="Print Stock Take Sheet" style="margin-left:20px;float:left">
       Print Product List
      <div style="clear:both"></div>
      </a>
      <br><div align="left"><input type="checkbox" id="DISPLAYIMAGE"> Include Product photos</div>

      <br><br><input type="submit" class="submit" onclick="displayProductList();enableStep(3);toggleSteps(3,'<?php echo $ROOT.$PHPFOLDER ?>');" value="Next Step">

   </div>
  </div>


    <div id='step3'>

      <div id="proceed3" style="display:none;">
      <Br>
      <div class="wrap" >
      Capture the counted stock amounts below:
      </div>
      <Br>
      <form id="stockCountForm"><!-- display product list here... /--></form>
      <Br>
      <a name="submit"></a>
      <input type="submit" class="submit" onclick="submitStockCount();" value="Submit Count">

      </div>

    </div>


  <div id='step5'>
    <div id="proceed5" style="display:none;" class="wrap" >

      <br><br><br>
      <form id='theForm' style="display:none;" method='post' action='<?php echo $ROOT.$PHPFOLDER ?>functional/reports/downloadBase.php' target='StockMovementReport'>
        <input type='hidden' name='p1' value='<?php echo $depotId ?>'>
        <input type='hidden' name='p4' value='<?php echo $principalId ?>'>
        <input type='hidden' name='REPORTID' value='57'>
      </form>
      You need to download the stock movement report before you can rollover completely!
      <br>
      <a href="javascript:;" onClick="downloadMovement()" class="bigbutton rdCrn5">Download</a>

      <div id="rolloversubmit" style="display:none;">
      <br><br><br>
      To rollover your stock data, click the button below!
      <br>
      <a href="javascript:;" onClick="submitRollover()" class="wrap start rdCrn5">Stock Rollover</a>
      </div>
    </div>
  </div>


<?php if($stockMode){

  echo '<div style="margin-top:30px;border-top:1px solid lightSkyblue;width:700px;padding-top:10px;" align="right">';
      echo '<a href="javascript:;" onclick="stopStockTake();" style="color:red;">cancel stock take</a>';
  echo '</div><br><br>';

}
?>

</div>


<script type="text/javascript">


$("div[id*='step']").css({display:'none'});
$("#step1").css({display:'block'});
toggleSteps(1,"<?php echo $ROOT.$PHPFOLDER ?>");


var alreadySubmitted = false;

function startStockTake(){

  var tc = $('#stocktaketc').attr('checked')==undefined ? false : true;
  if(!tc){
    parent.popBox('Please read and tick the terms and conditions below and try again!','error');
  } else {

    if (alreadySubmitted) {
      alert('You have already clicked on submit... you may click submit again after 2 minutes.');
      return;
    }

    alreadySubmitted=true;

    AjaxRefreshWithResult('ACTION=MODE&SWITCH=1&PRINCIPALID=<?php echo $principalId ?>&DEPOTID=<?php echo $depotId ?>',
                          '<?php echo $ROOT.$PHPFOLDER ?>functional/stock/stockTakeSubmit.php',  //is validated again on submit level.
                          'alreadySubmitted=false; if(msgClass.type=="S") {location.reload();}',
                          'Please wait while request is processed...');

  }


}


function downloadMovement(){
 $('#rolloversubmit').slideDown();

 window.open('about:blank', 'StockMovementReport','scrollbars=yes,width=300,height=200,resizable=yes');
 document.getElementById('theForm').submit();
}


function submitRollover(){

  if (alreadySubmitted) {
    alert('You have already clicked on submit... you may click submit again after 2 minutes.');
    return;
  }

  alreadySubmitted=true;

  AjaxRefreshWithResult('ACTION=ROLLOVER&PRINCIPALID=<?php echo $principalId ?>&DEPOTID=<?php echo $depotId ?>',
                        '<?php echo $ROOT.$PHPFOLDER ?>functional/stock/stockTakeSubmit.php',  //is validated again on submit level.
                        'alreadySubmitted=false; if(msgClass.type=="S") {window.location.reload();}',
                        'Please wait while request is processed...');

}


function stopStockTake(){

  if(confirm("Are sure you want to stop this stock take?")){
      AjaxRefreshWithResult('ACTION=MODE&SWITCH=0&PRINCIPALID=<?php echo $principalId ?>&DEPOTID=<?php echo $depotId ?>',
                          '<?php echo $ROOT.$PHPFOLDER ?>functional/stock/stockTakeSubmit.php',  //is validated again on submit level.
                          'alreadySubmitted=false; if(msgClass.type=="S") {location.reload();}',
                          'Please wait while request is processed...');
  }
}


function submitStockCount(){

  param = $('#stockCountForm').serialize();

  AjaxRefreshWithResult('ACTION=COUNT&PRINCIPALID=<?php echo $principalId ?>&DEPOTID=<?php echo $depotId ?>&' + param,
                    '<?php echo $ROOT.$PHPFOLDER ?>functional/stock/stockTakeSubmit.php',  //is validated again on submit level.
                    'alreadySubmitted=false; if(msgClass.type=="S"){successfulCount(msgClass);}else{displayVariances(msgClass);}',
                    'Please wait while request is processed...');

}

//enable rollover
function successfulCount(msgClass){
  disableStep(2);
  disableStep(3);
  disableStep(4);
  enableStep(5);
  toggleSteps(5,"<?php echo $ROOT.$PHPFOLDER ?>");
}

function displayVariances(msgClass){
  displayProductList(msgClass.identifier2);
}

function displayProductPrint(imagesCheckBoxId, list){

  var param = (list != undefined)?"&FILTERPID=" + list:"";

  param += ($('#'+imagesCheckBoxId).attr('checked')==undefined)?"":"&IMAGES=1";

  if(imagesCheckBoxId == 'VARDISPLAYIMAGE'){
    param += '&VARIANCE=1';
  }

  window.open('<?php echo $ROOT.$PHPFOLDER ?>functional/presentations/presentationHandler.php?TYPE=stockcount' + param,'ProductList','scrollbars=yes,width=750,height=600,resizable=yes');
}

function displayPrintVariances(){


  var html = "<html>"
      html += "<head>";
      html += "<title>Variance Count</title>";
      html += '<LINK href="<?php ECHO HOST_SURESERVER_AS_NEWUSER . $PHPFOLDER ?>css/default.css" rel="stylesheet" type="text/css">';
      html += '<script src="<?php ECHO HOST_SURESERVER_AS_NEWUSER . $PHPFOLDER ?>js/jquery.js"><\/script>';
      html += "</head>";
      html += "<body onload='jQuery(\"input\").attr(\"disabled\", true);'>";
      html += '<a href="javascript:window.print();" style="text-align:center;display:block;border:1px solid #ccc;padding:0px 8px;line-height:30px;width:100px;background:yellow;text-decoration:none;color:#666;font-weight:bold;"><img src="../../images/print-icon.png" border="0" alt="" align="left" style="margin:2px 0px;"> Print</a>';
      html += "<h2>Stock count : Variance </h2><h4 style='color:red'>*** Print ONLY for internal office use ***</h4>";
      html += $('#product-variance-table').html();
      html += "</body></html>";
  var blob = new Blob([html], {type: 'text/html'});
  window.open(window.URL.createObjectURL(blob),'','scrollbars=yes,width=750,height=600,resizable=yes');

  //window.open('<?php echo $ROOT.$PHPFOLDER ?>functional/presentations/presentationHandler.php?TYPE=stockcount' + param,'ProductList','scrollbars=yes,width=750,height=600,resizable=yes');
}

function displayProductList(json){

  var param = (json != undefined)?"&PROJSON=" + json:"";
  AjaxRefresh('PRINCIPALID=<?php echo $principalId ?>&DEPOTID=<?php echo $depotId ?>' + param,
              '<?php echo $ROOT.$PHPFOLDER ?>functional/stock/stockTakeProductList.php',
              "stockCountForm",
              "Please wait whilst page is refreshed...",
              "highlight()");

}

function displayProductAudit(pid){

  //display all transactions for product since last stock take...
  window.open('<?php echo $ROOT.$PHPFOLDER ?>functional/stock/stockTakeProductAudit.php?PRODUCTID=' + pid,'Product Audit','scrollbars=yes,width=600,height=500,resizable=yes');

}

function highlight(){

  //row highlisting for inputing values
  $(".highlightMe").focus(function() {
      $(this).closest("tr").attr('id','RowHighlight');
  })
  .blur(function() {
      $(this).closest("tr").attr('id','');
  });
}



function enableStep(id){
  $('#proceed' + id).show();
}
function disableStep(id){
  $('#proceed' + id).hide();
}

function successFreeze(msgClass){
  //nothing to do... do something?
}




</script>

</BODY>
</HTML>