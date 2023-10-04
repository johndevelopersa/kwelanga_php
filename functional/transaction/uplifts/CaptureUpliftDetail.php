<?php
include_once('ROOT.php');
include_once($ROOT . 'PHPINI.php');
require_once($ROOT . $PHPFOLDER . "functional/main/access_control.php");
include_once($ROOT . $PHPFOLDER . 'libs/GUICommonUtils.php');
include_once($ROOT . $PHPFOLDER . 'functional/transaction/uplifts/CaptureUpliftDetailClass.php');
include_once($ROOT . $PHPFOLDER . 'DAO/AgedStockDAO.php');
include_once($ROOT . $PHPFOLDER . 'DAO/PostAgedStockDAO.php');
include_once($ROOT . $PHPFOLDER . 'TO/AgedStockTO.php');
include_once($ROOT . $PHPFOLDER . 'TO/AgedStockDetailTO.php');

if (!isset($_SESSION)) session_start();
$userUId = $_SESSION['user_id'];
$principalId = $_SESSION['principal_id'];

//Create new database object
$dbConn = new dbConnect();
$dbConn->dbConnection();
$errorTO = new ErrorTO;

?>
<!DOCTYPE html>
<HTML>
<HEAD>
    <link href='<?php echo $DHTMLROOT . $PHPFOLDER ?>css/1_kwelanga.css' rel='stylesheet' type='text/css'>
    <script type="text/javascript" language="javascript"
            src="<?php echo $DHTMLROOT . $PHPFOLDER ?>js/jquery.js"></script>
    <script type="text/javascript" language="javascript"
            src="<?php echo $DHTMLROOT . $PHPFOLDER ?>js/dops_global_functions.js"></script>
    <style>
        td.head1 {
            font-weight: normal;
            font-size: 20px;
            text-align: left;
            font-family: Calibri, Verdana, Ariel, sans-serif;
            padding: 0 150px 0 150px
        }

        td.det1 {
            border-style: none;
            text-align: left;
            font-weight: bold;
            font-size: 13px;
            padding: 0 150px 0 150px
        }

        table.box {
            border: collapse;
            border: 2px solid;
            border-color: #990000;
            background: #fcecec
        }

    </style>

</HEAD>
<body>
<?php

if (isset($_POST["INVOICE"])) $clnInvoice = ($_POST["INVOICE"]); else $clnInvoice = '';

if (strpos($clnInvoice, '-') == false) {
    $postINVOICE = str_pad(ltrim($clnInvoice, '0'), 8, '0', STR_PAD_LEFT);
} else {
    $postINVOICE = str_pad(ltrim(trim(substr($clnInvoice, strpos($clnInvoice, '-') + 1, 10)), '0'), 8, '0', STR_PAD_LEFT);
}

//jb 29/09/2023
$cBox ="";
//$_POST['cBOX'];
$docUid = $_POST['DOCUID'];
$docNum = $_POST['DOCNUM'];

if (isset($_POST['finishform'])) {
    $docNum = $_POST['DOCNO'];
    //jb 29/09/2023
    $cBox =$_POST['SCBOX'];
    $x = 0;
    $continueCapture = 'N';

for ($x = 0;
     $x <= count($_POST['detID']);
     $x++) {
    $AgedStockDAO = new AgedStockDAO($dbConn);
    $errorTO = $AgedStockDAO->validateUpliftDetail(test_input($_POST['ULQTY'][$x]),
        test_input($_POST['DISQTY'][$x]),
        test_input($_POST['RFQTY'][$x]),
        test_input($_POST['NOTFND'][$x]),
        test_input($_POST['DAMAGES'][$x]),
        test_input($_POST['agedStock'][$x]));
if ($errorTO->type != FLAG_ERRORTO_SUCCESS) { ?>
    <script type='text/javascript'>parent.showMsgBoxError('<?php echo $errorTO->description; ?>')</script>
<?php
$continueCapture = 'Y';
$_POST['finishform'];
break;
}
}

if ($continueCapture == 'N') {

$upliftUid = $_POST['DOCMID'];
$upliftNumber = $_POST['DOCNO'];
//jb 29/09/2023
$postBOXQTY =$_POST['SCBOX'];
$uwarehouse = $_POST['WAREHOUSE'];
$dstat = $_POST['DOCSTAT'];

$AgedStockTO = new AgedStockTO();
$AgedStockTO->documentUid = $upliftUid;
$AgedStockTO->uplNumber = $upliftNumber;
$AgedStockTO->boxes = $postBOXQTY;
$AgedStockTO->principal = $principalId;
$AgedStockTO->reference1 = test_input($_POST['UREF1']);;
$AgedStockTO->reference2 = test_input($_POST['UREF2']);;
$AgedStockTO->reference3 = test_input($_POST['UREF3']);;
$AgedStockTO->reference4 = test_input($_POST['UREF4']);;
$AgedStockTO->reference5 = test_input($_POST['UREF5']);;
$AgedStockTO->reference6 = test_input($_POST['UREF6']);;
$AgedStockTO->warehouseUid = $uwarehouse;

for ($x = 0; $x < count($_POST['detID']); $x++) {
    if (test_input($_POST['ULQTY'][$x]) +
        test_input($_POST['DISQTY'][$x]) +
        test_input($_POST['RFQTY'][$x]) +
        test_input($_POST['NOTFND'][$x]) +
        test_input($_POST['DAMAGES'][$x]) > 0) {

        $AgedStockDetailTO = new AgedStockDetailTO;

        $AgedStockDetailTO->ddUid = $_POST['detID'][$x];
        $AgedStockDetailTO->prodUid = $_POST['prodID'][$x];
        $AgedStockDetailTO->prodCode = $_POST['prodCode'][$x];
        $AgedStockDetailTO->agedStkQty = $_POST['agedStock'][$x];

        if (trim(test_input($_POST['ULQTY'][$x])) <> '') {
            $ulqty = trim(test_input($_POST['ULQTY'][$x]));
        }
        $AgedStockDetailTO->found = $ulqty;

        if (trim(test_input($_POST['DISQTY'][$x])) <> '') {
            $disqty = trim(test_input($_POST['DISQTY'][$x]));
        }
        $AgedStockDetailTO->display = $disqty;

        if (trim(test_input($_POST['RFQTY'][$x])) <> '') {
            $rfqty = trim(test_input($_POST['RFQTY'][$x]));
        }
        $AgedStockDetailTO->storerefused = $rfqty;

        if (trim(test_input($_POST['DAMAGES'][$x])) <> '') {
            $damages = trim(test_input($_POST['DAMAGES'][$x]));
        }
        $AgedStockDetailTO->damages = $damages;

        if (trim(test_input($_POST['NOTFND'][$x])) <> '') {
            $notFound = trim(test_input($_POST['NOTFND'][$x]));
        }

        $AgedStockDetailTO->notFound = $notFound;

    }
    $AgedStockTO->detailArr[] = $AgedStockDetailTO;
}

$PostAgedStockDAO = new PostAgedStockDAO($dbConn);
$errorTO = $PostAgedStockDAO->InsertUpliftRecord($AgedStockTO, $userUId);

if ($errorTO->type != FLAG_ERRORTO_SUCCESS) { ?>
    <script type='text/javascript'>parent.showMsgBoxError(' <?php echo $errorTO->description; ?><br> Contact Kwelanga Support - 001')</script>
<?php

}
/*                  $PostAgedStockDAO   = new PostAgedStockDAO($dbConn);
                  $errorTO   = $PostAgedStockDAO->UpdateUpliftDocDetail($AgedStockTO);
                  if($errorTO->type!=FLAG_ERRORTO_SUCCESS) {?>
                          <script type='text/javascript'>parent.showMsgBoxError('<?php echo $errorTO->description; ?><br>Contact Kwelanga Support')</script> 
                          <?php
                          return;
                  }  
*/
if ($dstat == 'Accepted' || $dstat == 'Unaccepted' || $dstat == 'No Warehouse Receipt') {
    $newstat = 70;
} elseif ($dstat == 'Warehouse Receipt') {
    $newstat = 81;
}
$PostAgedStockDAO = new PostAgedStockDAO($dbConn);
$errorTO = $PostAgedStockDAO->UpdateUpliftStatus($postBOXQTY, $upliftUid, $newstat);
if ($errorTO->type != FLAG_ERRORTO_SUCCESS) { ?>
    <script type='text/javascript'>parent.showMsgBoxError('<?php echo $errorTO->description; ?><br>Contact Kwelanga Support - 002')</script>
<?php
return;
} else { ?>
    <script type='text/javascript'>
        parent.showMsgBoxInfo('Uplift Succesfully Captured')
        const saveKey = 'uplift-autosave-<?= $principalId . '-' . $upliftNumber ?>';
        localStorage.removeItem(saveKey)
    </script>
    <?php
    unset($_POST['firstform']);
    unset($_POST['receiptform']);
    unset($_POST['finishform']);
    unset($continueCapture);
    unset($_POST['CaptCont']);
}
}

    $restarray = $restarraydq = $restarrayrf = $restarraynf = $restarraydamC = $restlnTot = [];

    $x = 0;
    $lnTot = [];
    foreach ($_POST['ULQTY'] as $ulrow) {
        $lineK = $_POST['detID'][$x];
        $restarray[$lineK] = $ulrow;
        $restlnTot[$lineK] = (int)$ulrow;
        $x++;
    }
    $x = 0;
    foreach ($_POST['DISQTY'] as $dqrow) {
        $lineK = $_POST['detID'][$x];
        $restarraydq[$lineK] = (int)$dqrow;
        $restlnTot[$lineK] = $restlnTot[$lineK] + (int)$dqrow;
        $x++;
    }
    $x = 0;
    foreach ($_POST['RFQTY'] as $srfrow) {
        $lineK = $_POST['detID'][$x];
        $restarrayrf[$lineK] = (int)$srfrow;
        $restlnTot[$lineK] = $restlnTot[$lineK] + (int)$srfrow;
        $x++;
    }
    $x = 0;
    foreach ($_POST['NOTFND'] as $dnfrow) {
        $lineK = $_POST['detID'][$x];
        $restarraynf[$lineK] = (int)$dnfrow;
        $restlnTot[$lineK] = $restlnTot[$lineK] + (int)$dnfrow;
        $x++;
    }
    $x = 0;
    foreach ($_POST['DAMAGES'] as $damrow) {
        $lineK = $_POST['detID'][$x];
        $restarraydam[$lineK] = (int)$damrow;
        $restlnTot[$lineK] = $restlnTot[$lineK] + (int)$damrow;
        $x++;
    }

}
// Verify warehouse receipt
if (isset($_POST['firstform']) && isset($_POST["INVOICE"])) {
    $CaptureUpliftDetail = new CaptureUpliftDetail();
    $whSr = $CaptureUpliftDetail->receiptform($principalId, $postINVOICE);
}

if ($_POST['receiptform']) {
    // Validate Boxes
    $AgedStockDAO = new AgedStockDAO($dbConn);
    //29/09/2023
    $errorTO->type =FLAG_ERRORTO_SUCCESS;
    // $AgedStockDAO->validateWarehseBoxes($cBox, $docUid);

    if ($errorTO->type != FLAG_ERRORTO_SUCCESS && $errorTO->description == 'Boxes not Numeric') { ?>
        <script type='text/javascript'>parent.showMsgBoxError('<?php echo $errorTO->description; ?>')</script>
        <?php
        unset($_POST['firstform']);
        unset($_POST['receiptform']);
        $cantcont = 'Y';
    }

    if (!isset($cantcont)) {
        if ($errorTO->type != FLAG_ERRORTO_SUCCESS && $errorTO->description != 'Boxes not Numeric') {                // Test for == Boxes
            $CaptureUpliftDetail = new CaptureUpliftDetail();
            $a = $CaptureUpliftDetail->receiptError($cBox, $docNum);
        } else {
            $continueCapture = 'Y';
        }
    }
}

if (isset($_POST['CaptCont'])) {
    $docNum = $_POST['RDOCNUM'];
    $cBox = $_POST['RBOX'];
    $continueCapture = 'Y';
}

if (isset($_POST['CaptCancel'])) {
    unset($_POST['firstform']);
    unset($_POST['receiptform']);
    unset($_POST['finishform']);
    unset($continueCapture);
    unset($_POST['CaptCont']);
}

if ($continueCapture == 'Y') {
    $CaptureUpliftDetail = new CaptureUpliftDetail();
    $whSr = $CaptureUpliftDetail->upliftDetailCapture($principalId,
        $docNum,
        $cBox,
        $restarray,
        $restarraydq,
        $restarrayrf,
        $restarraynf,
        $restarraydam,
        $restarraydamB,
        $restarraydamC,
        $loopNo);
}
// Begin capture firstform
if (!isset($_POST['firstform']) && !isset($_POST['receiptform']) && !isset($_POST['CaptCont']) && !isset($continueCapture)) {
    $CaptureUpliftDetail = new CaptureUpliftDetail();
    $a = $CaptureUpliftDetail->firstform();
}

?>

</body>
</HTML>

<?php
function test_input($data)
{

    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    if ($data == '') {
        $data = 0;
    }

    return $data;
}

?>
