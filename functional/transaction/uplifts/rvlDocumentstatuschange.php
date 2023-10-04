<?php

include_once('ROOT.php');
include_once($ROOT . 'PHPINI.php');
require_once($ROOT . $PHPFOLDER . "functional/main/access_control.php");
require_once($ROOT . $PHPFOLDER . 'DAO/UpliftsDAO.php');
require_once($ROOT . $PHPFOLDER . 'DAO/UpliftscmdDAO.php');

$document_ref = '';
$dhuid=1;
$description = '';
$status = '';
$reason = '';
$message = '';

$userUId = $_SESSION['user_id'];
$principalId = $_SESSION['principal_id'];

$dbConn = new dbConnect();
$dbConn->dbConnection();
$dbfunctions=new dbFunctions();
$dbfunctions1=new dbFunctions1();
         

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    if (isset($_POST['select']))
    {
        // Validate Document Number
        $document_ref = $_POST['document_ref'];
       
       $sql = "SELECT dh.uid".
       " FROM document_master dm".
       " INNER JOIN document_header dh ON dh.document_master_uid = dm.uid".
       " INNER JOIN principal p ON dm.principal_uid = p.uid".
       " WHERE dm.principal_uid = ?".
       " AND dm.document_number = ?";
       $ptypes="is";
//$ptypes = ["i", "s"];
$pvalues = [$principalId, $document_ref];
        //$types=[];
        //$types[0]="i";
        //$types[1]="s";
        //$values = [];
        //$values[0]=$principalId;
        //$values[1]=$document_ref;
        $result=$dbfunctions->getTablevalue($sql,$ptypes,$pvalues,"uid");
        //$result=$dbfunctions->getTablevalue("json","parameters","cs",
        //"sql","kwelanga_dev.document_header","invoice_number",$document_ref);
        //$sql = "SELECT invoice_number,document_status_uid FROM document_header WHERE invoice_number = '$document_ref' limit 1";
        //$dbConn->dbQuery($sql);
        //echo 'result '.$result;
        if (strpos($result, 'Error')!==false)
        //if ($dbConn->dbQueryResult === false)
        {
            $dhuid=2;
            //$result;
            $message=$result;
            //$message = 'Error: query failed ' . $dbConn->
            //error;
        }
        else
        //if (strpos($result, 'Error')==false)
        //if ($dbConn->dbQueryResult->num_rows >  0)
        {
            //$row = $dbConn->dbQueryResult->fetch_assoc();
            $dhuid=$result;
            $description =$result;
            // $row['invoice_number'];
            $reason = '';
            $status ='A';
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
        $sql=[];
        $sql[0] = "UPDATE document_header".
        " SET document_status_uid=" . DST_ACCEPTED .
        //" WHERE principal_uid = ? ".
        " WHERE uid = ?";
        //" AND document_number = ?";
        //WHERE document_number = '$document_ref'";
        $ptypes[0]="i";
        //$ptypes = ["i", "s"];
        $pvalues1 = [$dhuid];
        //468436
        $pvalues[0]=$pvalues1;

        $sql[1] ="INSERT INTO document_log ".
        "(document_master_uid,".
        "change_type,".
        "old_value,".
        "change_value,".
        "comments,".
        "change_by_user)".
        " VALUES (?,?,?,?,?,?)";
        $ptypes[1]="sssiss";
        $pvalues1 = [$dhuid,"RvlDocumentstatus","0",DST_ACCEPTED,$reason,$userid];
        $pvalues[1]=$pvalues1;
        $result=$dbfunctions1->cmdexecutetn($sql,$ptypes,$pvalues);
        //$sql = "update document_header set document_status_uid=" . DST_ACCEPTED . " WHERE document_number = '$document_ref'";
        //$dbConn->dbQuery($sql);

        if ($result=="success")
        //if ($dbConn->dbQueryResult === true)
        {
            //$dbConn->dbQuery("commit");
            //$dbConn->dbQuery($sql1);
            //$dbConn->dbQuery("commit");
            $message = 'Updated ' . $document_ref.' '.$dhuid;
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
    <title style="text-align: center;">Rvl Document Status</title>
    <h1><?php echo "Principal id " . htmlspecialchars($principalId); ?></h1>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="rvlDocumentstatuschange.css">
    <!--<link rel="stylesheet" type="text/css" href="functional/transaction/uplifts/rvlDocumentstatuschange.css">-->
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
                                <input type="text" class="form-control" name="document_ref" value="<?php echo htmlspecialchars($document_ref); ?>">
                            </div>
                        </div>
                    </div>
                    <div class="stripe">
                        <button type="submit" class="custom-button" name="select">Select</button>
                    </div>
                    <div class="stripe">
                    <button type="button" class="custom-button" name="summary" onclick="location.href='boxMaintainWhsrcts.php'">Box receipts</button>
                    <!--<button type="button" class="custom-button" name="summary" onclick="location.href='reportCasesummary.php'">Summary</button>-->
                    </div>

                    <?php if (!empty($description) && !empty($status)) : ?>
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