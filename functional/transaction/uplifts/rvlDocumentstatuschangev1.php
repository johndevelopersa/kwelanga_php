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
include_once($ROOT . $PHPFOLDER . 'DAO/db_Connection_Class.php');
include_once($ROOT . $PHPFOLDER . 'properties/Constants.php');

$document_ref = '';
$description = '';
$status = '';
$reason = '';
$message = '';

$userUId = $_SESSION['user_id'];
$principalId = $_SESSION['principal_id'];

// Create new database object
$dbConn = new dbConnect();
$dbConn->dbConnection();

$errorTO = new ErrorTO;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['select'])) {

        // Validate Document Number
        $document_ref = $_POST['document_ref'];
        $sql = "SELECT invoice_number,document_status_uid FROM document_header WHERE invoice_number = '$document_ref' limit 1";
        $dbConn->dbQuery($sql);

        if ($dbConn->dbQueryResult === false)
        {
            $message = 'Error: query failed ' . $dbConn->error;
        }

        if ($dbConn->dbQueryResult->num_rows >  0)
        {
            $row = $dbConn->dbQueryResult->fetch_assoc();
            $description = $row['invoice_number'];
            $reason = '';
            $status = $row['document_status_uid'];
        }
        else
        {
            $message = 'Error: Invalid number';
        } else {
            $description = $docArr['invoice_number'];
            $reason = '';
            $status = $docArr['document_status_uid'];
        }
    }

    if (isset($_POST['update'])) {
        // Update Document Status and Insert into Log
        $document_ref = $_POST['document_ref'];
        $reason = $_POST['reason'];
        //TODO: move into a DAO
        $sql = 'update document_header set document_status_uid=' . DST_ACCEPTED . ' WHERE
                    princiapl_id = ' . (int)$principalId . " 
                    AND invoice_number = '$document_ref'";
        $result = $dbConn->processPosting($sql, '');
        if ($result->isError()) {
            $message = 'Error: query failed ' . $result->description;
        }

        $sql1 = "INSERT INTO document_log (document_masxter_uid, change_type, old_value, change_value,comments, change_by_user) VALUES (";
        $sql1 .= "'" . $document_ref . "', ";
        $sql1 .= "'RvlDocumentstatus', ";
        $sql1 .= "'" . "0" . "', ";
        $sql1 .= "'" . DST_ACCEPTED . "', ";
        $sql1 .= "'" . mysqli_real_escape_string($dbConn->connection, $reason) . "', ";
        $sql1 .= "'" . (int)$userUId . "'";
        $sql1 .= ")";
        $result = $dbConn->processPosting($sql1, '');

        if ($result->isSuccess()) {
            $dbConn->dbQuery("commit");
            $message = 'Updated ' . $document_ref;
        } else {
            $message = 'Error: query failed ' . $result->description;
        }
    }
}

?>

<!DOCTYPE html>
<html>

<head>
    <!--<h1 style="text-align: center;">Rvl Document Status</h1>-->
    <br>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .outer-box {
            border: 1px solid #ccc;
            padding: 20px;
            width: 75%;
            margin: auto;
        }

        .stripe:nth-child(even) {
            background-color: lightgray;
        }

        .stripe:nth-child(odd) {
            background-color: darkgray;
        }

        .custom-button {
            background-color: black;
            color: white;
        }

        .updated-message {
            background-color: lightgreen;
            width: 20%;
            margin: left;
            text-align: left;
        }
    </style>
</head>

<body>
<div class="container mt-4">
    <h1 style="text-align: center;">Rvl Document Status</h1>
    <br>
    <div class="outer-box">

        <form action="" method="POST">
            <div class="mb-3">
                <div class="stripe">
                    <div class="row">
                        <div class="col-md-3">
                            <label for="document_ref" class="form-label"> Document number</label>
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="form-control" name="document_ref"
                                   value="<?php echo htmlspecialchars($document_ref); ?>">
                        </div>
                    </div>
                </div>
                <div class="stripe">
                    <button type="submit" class="custom-button" name="select">Select</button>
                </div>

                <?php if (!empty($description) && !empty($status)) : ?>
                    <div class="mt-3">
                        <!--
        <p>Description: <?php echo htmlspecialchars($description); ?></p>
        <div class="stripe">
        <p>Status: <?php echo htmlspecialchars($status); ?></p>
        </div>
        -->
                        <div class="stripe">
                            <div class="row">
                                <div class="col-md-3">
                                    <label for="reason" class="form-label">Reason</label>
                                </div>
                                <div class="col-md-5">
                                    <input type="text" class="form-control" name="reason"
                                           value="<?php echo htmlspecialchars($reason); ?>">
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="document_ref_hidden"
                               value="<?php echo htmlspecialchars($document_ref); ?>">
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