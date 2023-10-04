<?php
include_once('ROOT.php');
include_once($ROOT . 'PHPINI.php');
require_once($ROOT . $PHPFOLDER . "functional/main/access_control.php");
//include_once($ROOT . $PHPFOLDER . 'libs/GUICommonUtils.php');
//include_once($ROOT . $PHPFOLDER . 'functional/transaction/uplifts/CaptureUpliftDetailClass.php');
//include_once($ROOT . $PHPFOLDER . 'DAO/AgedStockDAO.php');
//include_once($ROOT . $PHPFOLDER . 'DAO/PostAgedStockDAO.php');
//include_once($ROOT . $PHPFOLDER . 'TO/AgedStockTO.php');
//include_once($ROOT . $PHPFOLDER . 'TO/AgedStockDetailTO.php');
//include_once($ROOT . $PHPFOLDER . 'DAO/db_Connection_Class.php');
require_once($ROOT . $PHPFOLDER . 'DAO/UpliftsDAO.php');
require_once($ROOT . $PHPFOLDER . 'DAO/UpliftscmdDAO.php');


if (!isset($_SESSION)) session_start();
$userUId = $_SESSION['user_id'];
$principalId = $_SESSION['principal_id'];

//Create new database object
//$dbConn = new dbConnect();
//$dbConn->dbConnection();
//$errorTO = new ErrorTO;
$dbfunctions = new dbFunctions();

?>
<!DOCTYPE html>
<HTML>

<head>
    <h1><?php echo "Principal id " . htmlspecialchars($principalId); ?></h1>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="rvlDocumentstatuschange.css">
    <!--<link rel="stylesheet" type="text/css" href="functional/transaction/uplifts/rvlDocumentstatuschange.css">-->
    <link href='<?php echo $DHTMLROOT . $PHPFOLDER ?>css/1_kwelanga.css' rel='stylesheet' type='text/css'>
    <script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT . $PHPFOLDER ?>js/jquery.js"></script>
    <script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT . $PHPFOLDER ?>js/dops_global_functions.js"></script>
    <style>
        .centered-table {
            margin-left: auto;
            margin-right: auto;
            border: 2px solid blue;
            width: 50%;
            table-layout: fixed;
        }

        .hidden {
            display: none;
        }

        .col-document {
            width: 15%;
        }

        .col-depot {
            width: 30%;
        }

        .col-store {
            width: 40%;
        }

        .col-date {
            width: 15%;
        }

        .col-boxes {
            width: 10%;
        }

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

</head>

<body>
<?php
$limit = 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Use 1 as the default
$offset = ($page - 1) * $limit;
 //total cases per principal
 $sql = "SELECT SUM((SELECT COUNT(*)" .
 " FROM document_rvl_box_detail drbd" .
 " WHERE drbd.dispatched = ?" .
 " AND drbd.document_rvl_box_header_uid = drbh.uid" .
 " )) AS grandTotalBoxes" .
 " FROM document_rvl_box_header drbh" .
 " INNER JOIN document_master dm ON drbh.document_master_uid = dm.uid" .
 " INNER JOIN document_header dh ON drbh.document_master_uid = dh.uid" .
 " LEFT JOIN principal p ON dm.principal_uid = p.uid" .
 " LEFT JOIN depot d ON dm.depot_uid = d.uid" .
 " LEFT JOIN principal_store_master psm on dh.principal_store_uid = psm.uid" .
 " WHERE dm.principal_uid = ?";
$ptypes = "si";
$pvalues = ["N", 415];
$result1v = $dbfunctions->getTablevalue($sql, $ptypes, $pvalues, "grandTotalBoxes");
//echo $result1;

?>
    <h1>Reverse Logistics Case Summary</h1>
    <div class="text-center">
        <h2>Total Boxes: <?php echo $result1v; ?></h2>
    </div>
    <div class="text-center mt-3">
        <button class="btn btn-primary" id="showDepotTotals">Depot Totals</button>
        <button class="btn btn-primary" id="showStoreTotals">Store Totals</button>
        <button class="btn btn-primary" id="showDocumentTotals">Document Totals</button>
    </div>
    <br>
    <?php
   
    //total cases by depot
    $sql = "SELECT d.name AS depot_name," .
        " SUM((SELECT COUNT(*) " .
        " FROM document_rvl_box_detail drbd" .
        " WHERE drbd.dispatched = ?" .
        " AND drbd.document_rvl_box_header_uid = drbh.uid" .
        " )) AS boxes" .
        " FROM document_rvl_box_header drbh" .
        " INNER JOIN document_master dm ON drbh.document_master_uid = dm.uid" .
        " INNER JOIN document_header dh ON drbh.document_master_uid = dh.uid" .
        " LEFT JOIN principal p ON dm.principal_uid = p.uid" .
        " LEFT JOIN depot d ON dm.depot_uid = d.uid" .
        " LEFT JOIN principal_store_master psm on dh.principal_store_uid = psm.uid" .
        " WHERE dm.principal_uid = ?" .
        " GROUP BY d.name";
    $ptypes = "si";
    $pvalues = ["N", 415];
    $resultdepot = $dbfunctions->getTablevalue($sql, $ptypes, $pvalues, "");

    //total cases by store
    $sql = "SELECT psm.deliver_name AS store_name," .
        " SUM((SELECT COUNT(*) " .
        " FROM document_rvl_box_detail drbd" .
        " WHERE drbd.dispatched = ?" .
        " AND drbd.document_rvl_box_header_uid = drbh.uid" .
        " )) AS boxes" .
        " FROM document_rvl_box_header drbh" .
        " INNER JOIN document_master dm ON drbh.document_master_uid = dm.uid" .
        " INNER JOIN document_header dh ON drbh.document_master_uid = dh.uid" .
        " LEFT JOIN principal p ON dm.principal_uid = p.uid" .
        " LEFT JOIN depot d ON dm.depot_uid = d.uid" .
        " LEFT JOIN principal_store_master psm on dh.principal_store_uid = psm.uid" .
        " WHERE dm.principal_uid = ?" .
        " GROUP BY psm.deliver_name".
        " HAVING boxes<>0";
    $ptypes = "si";
    $pvalues = ["N", 415];
    $resultstore = $dbfunctions->getTablevalue($sql, $ptypes, $pvalues, "");

    //$sql = "SELECT document_master_uid, principal_master_store_uid, boxes, comment FROM aged_stock_warehouse_receipt LIMIT 10";
    //$sql = "SELECT aswr.document_master_uid, aswr.principal_master_store_uid, aswr.boxes, aswr.comment" .
    //" FROM document_master dm" .
    //" INNER JOIN aged_stock_warehouse_receipt aswr ON aswr.document_master_uid = dm.uid" .
    //" LEFT JOIN principal p ON dm.principal_uid = p.uid" .
    //" WHERE dm.principal_uid = ?" .
    //" LIMIT 10";
    //detail by document
    $sql = "SELECT drbh.document_master_uid, SUBSTR(drbh.receipt_date,1,10) receipt_date,noOfBoxes boxes1,dm.principal_uid,dm.depot_uid,dm.document_number,dh.principal_store_uid,substr(d.name,1,25) as depot_name,substr(psm.deliver_name,1,30) as deliver_name," .
        " (select count(*) FROM document_rvl_box_detail drbd".
        " WHERE drbd.dispatched=?".
        " AND drbd.document_rvl_box_header_uid=drbh.uid) as boxes".
        " FROM document_rvl_box_header drbh" .
        " INNER JOIN document_master dm ON drbh.document_master_uid=dm.uid" .
        " INNER JOIN document_header dh ON drbh.document_master_uid=dh.uid" .
        " LEFT JOIN principal p ON dm.principal_uid = p.uid" .
        " LEFT JOIN depot d ON dm.depot_uid = d.uid" .
        " LEFT JOIN principal_store_master psm on dh.principal_store_uid=psm.uid".
        " WHERE dm.principal_uid = ?" .
        " AND drbh.document_master_uid>4000000" .
        " HAVING boxes<>0".
        " LIMIT " . $limit . " OFFSET " . $offset;
        //" LIMIT 20";
    //" AND dm.document_number = ?";
    $ptypes = "si";
    //$ptypes = ["i", "s"];
    $pvalues = ["N",415];
    //$pvalues = [9346];
    //$types=[];
    //$types[0]="i";
    //$types[1]="s";
    //$values = [];
    //$values[0]=$principalId;
    //$values[1]=$document_ref;
    $resultdocument = $dbfunctions->getTablevalue($sql, $ptypes, $pvalues, "");
    //$result=$dbfunctions->getTablevalue("json","parameters","cs",
    //"sql","kwelanga_dev.document_header","invoice_number",$document_ref);
    //$sql = "SELECT invoice_number,document_status_uid FROM document_header WHERE invoice_number = '$document_ref' limit 1";
    //$dbConn->dbQuery($sql);
    //echo 'result '.$result;
    //if (strpos($result, 'Error')!==false)
    //if ($dbConn->dbQueryResult === false)
    //{
    //$dhuid=2;
    //$result;
    //$message=$result;
    //$message = 'Error: query failed ' . $dbConn->
    //error;
    //}
    //else
    //if (strpos($result, 'Error')==false)
    //if ($dbConn->dbQueryResult->num_rows >  0)
    //{
    //$row = $dbConn->dbQueryResult->fetch_assoc();
    //$dhuid=$result;
    //$description =$result;
    // $row['invoice_number'];
    //$reason = '';
    //$status ='A';
    //$row['document_status_uid'];
    //}
    ?>
<!--depot totals-->
<table class="table table-bordered table-striped centered-table hidden" id="depotTotalsTable">
       <!--<table class="table table-bordered table-striped centered-table">-->
        <colgroup>
              <col class="col-depot">
              <col class="col-boxes">
        </colgroup>
        <thead>
            <tr class="text-center">
                <th>Depot</th>
                <th>Boxes</th>
            </tr>
        </thead>
        <tbody>
            <?php

            if ($resultdepot->num_rows > 0)
            {
                // Output each row
                while ($row = $resultdepot->fetch_assoc())
                {
                    echo "<tr class='text-center'>";
                    echo "<td>" . $row['depot_name'] . "</td>";
                     echo "<td>" . $row['boxes'] . "</td>";
                    echo "</tr>";
                }
            }
            else
            {
                echo "<tr><td colspan='4' class='text-center'>No records found</td></tr>";
            }
            ?>
        </tbody>
    </table>
    <!--store totals-->
<table class="table table-bordered table-striped centered-table hidden" id="storeTotalsTable">
       <!--<table class="table table-bordered table-striped centered-table">-->
        <colgroup>
              <col class="col-depot">
              <col class="col-boxes">
        </colgroup>
        <thead>
            <tr class="text-center">
                <th>Store</th>
                <th>Boxes</th>
            </tr>
        </thead>
        <tbody>
            <?php

            if ($resultstore->num_rows > 0)
            {
                // Output each row
                while ($row = $resultstore->fetch_assoc())
                {
                    echo "<tr class='text-center'>";
                    echo "<td>" . $row['store_name'] . "</td>";
                     echo "<td>" . $row['boxes'] . "</td>";
                    echo "</tr>";
                }
            }
            else
            {
                echo "<tr><td colspan='4' class='text-center'>No records found</td></tr>";
            }
            ?>
        </tbody>
    </table>
       <!--document totals-->
       <table class="table table-bordered table-striped centered-table hidden" id="documentTotalsTable">
       <!--<table class="table table-bordered table-striped centered-table">-->
        <colgroup>
            <col class="col-document">
             <col class="col-depot">
             <col class="col-store">
            <col class="col-date"> 
            <col class="col-boxes">
        </colgroup>
        <thead>
            <tr class="text-center">
                <th>Document number</th>
                <th>Depot</th>
                <th>Store</th>
                <th>Date received</th>
                <th>Boxes</th>
            </tr>
        </thead>
        <tbody>
            <?php

            if ($resultdocument->num_rows > 0)
            {
                // Output each row
                while ($row = $resultdocument->fetch_assoc())
                {
                    echo "<tr class='text-center'>";
                    echo "<td>" . $row['document_number'] . "</td>";
                    echo "<td>" . $row['depot_name'] . "</td>";
                    echo "<td>" . $row['deliver_name'] . "</td>";
                    echo "<td>" . $row['receipt_date'] . "</td>";
                    echo "<td>" . $row['boxes'] . "</td>";
                    echo "</tr>";
                }
            }
            else
            {
                echo "<tr><td colspan='4' class='text-center'>No records found</td></tr>";
            }
            ?>
        </tbody>
    </table>
    $previousPage = $page - 1;
$nextPage = $page + 1;

echo "<a href='?page=1'>First</a> ";
echo "<a href='?page={$previousPage}'>Previous</a> ";
echo "<a href='?page={$nextPage}'>Next</a> ";
    <script>
        $(document).ready(function() {
            $('#showDepotTotals').click(function() {
                $('#depotTotalsTable').show();
                $('#storeTotalsTable').hide();
                $('#documentTotalsTable').hide();
            });

            $('#showStoreTotals').click(function() {
                $('#depotTotalsTable').hide();
                $('#storeTotalsTable').show();
                $('#documentTotalsTable').hide();
            });

            $('#showDocumentTotals').click(function() {
                $('#documentTotalsTable').show();
                $('#storeTotalsTable').hide();
                $('#depotTotalsTable').hide();
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>