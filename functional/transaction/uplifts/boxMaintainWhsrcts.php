<?php

include_once('ROOT.php');
include_once($ROOT . 'PHPINI.php');
require_once($ROOT . $PHPFOLDER . "functional/main/access_control.php");
require_once($ROOT . $PHPFOLDER . 'DAO/UpliftsDAO.php');
require_once($ROOT . $PHPFOLDER . 'DAO/UpliftscmdDAO.php');

if (!isset($_SESSION)) session_start();

// First, try to get the mode from POST data
if (isset($_POST['mode'])) {
    $mode = $_POST['mode'];
    $_SESSION['mode'] = $mode;  // Save to session
} else {
    // If there's no POST data, get the mode from the session (or default to 'N')
    $mode = isset($_SESSION['mode']) ? $_SESSION['mode'] : 'N';
}

$document_ref = '';
$dhuid = 1;
$description = '';
$status = '';
$reason = '';
$message = '';

$userUId = $_SESSION['user_id'];
$principalId = $_SESSION['principal_id'];

$dbConn = new dbConnect();
$dbConn->dbConnection();
$dbfunctions = new dbFunctions();
$dbfunctions1 = new dbFunctions1();

$custName="Test";
$date="01/10/2023";
$docNum="";
$docUid="";
$deliverName="Test";

//principal first three characters of document no xxxx-xxxx
$prinId="";
//remaining characters after dash
$upliftNo="";


if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
  if (isset($_POST['select']))
  {
    // Validate Document Number
    $document_ref = $_POST['document_ref'];

    $prinId   = substr($document_ref,0,strpos($document_ref,'-'));
    $docNum = str_pad(trim(substr($document_ref,strpos($document_ref,'-') +1 ,8)), 8, '0', STR_PAD_LEFT);
    
    $sql = "SELECT dh.uid" .
      " FROM document_master dm" .
      " INNER JOIN document_header dh ON dh.document_master_uid = dm.uid" .
      " INNER JOIN principal p ON dm.principal_uid = p.uid" .
      " WHERE dm.principal_uid = ?" .
      " AND dm.document_number = ?";
    $ptypes = "is";
    //$ptypes = ["i", "s"];
    $pvalues = [$prinId, $docNum];
    //$types=[];
    //$types[0]="i";
    //$types[1]="s";
    //$values = [];
    //$values[0]=$principalId;
    //$values[1]=$document_ref;
    $result = $dbfunctions->getTablevalue($sql, $ptypes, $pvalues, "uid");
    //$result=$dbfunctions->getTablevalue("json","parameters","cs",
    //"sql","kwelanga_dev.document_header","invoice_number",$document_ref);
    //$sql = "SELECT invoice_number,document_status_uid FROM document_header WHERE invoice_number = '$document_ref' limit 1";
    //$dbConn->dbQuery($sql);
    //echo 'result '.$result;
    if (strpos($result, 'Error') !== false)
    //if ($dbConn->dbQueryResult === false)
    {
      $dhuid = 2;
      //$result;
      ?>
      <script type='text/javascript'>
      var errorMessage = "<?php echo $result."|".$prinId."|".$docNum."|".$mode."."+$_SESSION['mode']; ?>"; 
      parent.showMsgBoxError(errorMessage)</script>
      <?php	 
      //$message = 'Error: query failed ' . $dbConn->
      //error;
    }
    else
    //if (strpos($result, 'Error')==false)
    //if ($dbConn->dbQueryResult->num_rows >  0)
    {
      //$row = $dbConn->dbQueryResult->fetch_assoc();
      $dhuid = $result;
      $description = $result;
      // $row['invoice_number'];
      $reason = '';
      $status = 'A';
      $message = $result;
      $mode="U";
      $_SESSION['mode'] = $mode;
      echo "Mode: $mode, Session Mode: {$_SESSION['mode']}";
      //$row['document_status_uid'];
    }
    //else
    //{
    //$message = $result;
    //}
  }

  if (isset($_POST['update']))
  {
    // Update Document Status and Insert into Log
    $dhuid = isset($_POST['dhuid_hidden']) ? intval($_POST['dhuid_hidden']) : 1;
    $document_ref = $_POST['document_ref'];
    $reason = $_POST['reason'];
    $sql = [];
    $sql[0] = "UPDATE document_header" .
      " SET document_status_uid=" . DST_ACCEPTED .
      //" WHERE principal_uid = ? ".
      " WHERE uid = ?";
    //" AND document_number = ?";
    //WHERE document_number = '$document_ref'";
    $ptypes[0] = "i";
    //$ptypes = ["i", "s"];
    $pvalues1 = [$dhuid];
    //468436
    $pvalues[0] = $pvalues1;

    $sql[1] = "INSERT INTO document_log " .
      "(document_master_uid," .
      "change_type," .
      "old_value," .
      "change_value," .
      "comments," .
      "change_by_user)" .
      " VALUES (?,?,?,?,?,?)";
    $ptypes[1] = "sssiss";
    $pvalues1 = [$dhuid, "RvlDocumentstatus", "0", DST_ACCEPTED, $reason, $userid];
    $pvalues[1] = $pvalues1;
    $result = $dbfunctions1->cmdexecutetn($sql, $ptypes, $pvalues);
    //$sql = "update document_header set document_status_uid=" . DST_ACCEPTED . " WHERE document_number = '$document_ref'";
    //$dbConn->dbQuery($sql);

    if ($result == "success")
    //if ($dbConn->dbQueryResult === true)
    {
      //$dbConn->dbQuery("commit");
      //$dbConn->dbQuery($sql1);
      //$dbConn->dbQuery("commit");
      $message = 'Updated ' . $document_ref . ' ' . $dhuid;
    }
    else
    {
      $message = 'Error: query failed ';
    }
  }
}
?>

<!DOCTYPE html>
<html>

<head>
  <!--<title style="text-align: center;">Warehouse Box Receipts</title>-->
  <!--<h1><?php echo "Principal id " . htmlspecialchars($principalId); ?></h1>-->

  <link href='<?php echo $DHTMLROOT . $PHPFOLDER ?>css/kos_standard_screen.css' rel='stylesheet' type='text/css'>

  <!--
 	<link href='<?php echo $DHTMLROOT . $PHPFOLDER ?>css/1_kwelanga.css' rel='stylesheet' type='text/css'>

    <script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT . $PHPFOLDER ?>js/jquery.js"></script>
		<script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT . $PHPFOLDER ?>js/dops_global_functions.js"></script>
    -->

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="boxMaintainWhsrcts.css">
  <!--<link rel="stylesheet" type="text/css" href="functional/transaction/uplifts/rvlDocumentstatuschange.css">-->


  <style>
    td.head1 {
      font-weight: bold;
      font-size: 16px;
      text-align: center;
      font-family: Calibri, Verdana, Ariel, sans-serif;
      padding: 0 30px 0 30px;
    }

    table.full-width {
      width: 100%;
    }

    .stripe.spacer {
      height: 35px;
    }

    .custom-input-height {
    height: 50%;
    }

    .consistent-height {
    height: 35px;  
    /*
    display: flex;
    align-items: center; 
    justify-content: center;  
    */
}


  </style>
</head>

<body>
  <div class="container mt-4">

    <!--<h1 style="text-align: center;">Warehouse Box Receipts</h1>-->
    <br>
    <div class="outer-box">

      <table class="full-width">
        <tr>
          <td class="head1" colspan="5">Warehouse Box Receipts</td>
        </tr>
      </table>
      <br>
      <form action="" method="POST">
        <div class="mb-3">
        <div class="stripe spacer"></div>
          <div class="stripe consistent-height">
            <div class="row justify-content-center">
              <div class="col-md-3 text-center">
                <label for="document_ref" class="form-label">Document number</label>
              </div>
              <div class="col-md-3">
                <input type="text" class="form-control custom-input-height" name="document_ref" value="<?php echo htmlspecialchars($document_ref); ?>">
              </div>
            </div>
          </div>
          <div class="stripe spacer"></div>
          <div class="stripe consistent-height">
            <div class="row justify-content-center">
              <div class="col-md-2 text-center">
                <button type="submit" class="custom-button" name="select">Select</button>
              </div>
              <div class="col-md-2 text-center">
                <button type="button" class="custom-button" name="summary" onclick="location.href='boxMaintainWhsrcts.php'">Cancel</button>
              </div>
            </div>
          </div>

          <?php echo "Mode value: " . $mode; ?>

          <?php if ($mode=="U") : ?>
            <div class="mt-3">
              <div class="stripe">
                <div class="row">
                  <div class="col-md-3">
                    <label for="reason" class="form-label">Reason <?php echo $dhuid; ?></label>
                  </div>
                  <div class="col-md-5">
                    <input type="text" class="form-control" name="reason" value="<?php echo htmlspecialchars($reason); ?>">
                  </div>
                </div>
              </div>

              <input type="hidden" name="document_ref_hidden" value="<?php echo htmlspecialchars($document_ref); ?>">
              <input type="hidden" name="dhuid_hidden" value="<?php echo htmlspecialchars($dhuid); ?>">
              <div class="stripe">
                <button type="submit" class="custom-button" name="update">Update</button>
              </div>
            </div>
          <?php endif; ?>

          <?php if (!empty($message)) : ?>
            <div class="mt-3 updated-message">
              <!--<div class="mt-3 alert alert-danger">-->
              <p><?php echo htmlspecialchars($message); ?></p>
            </div>
          <?php endif; ?>
      </form>
      <div>
      </div>
      <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>