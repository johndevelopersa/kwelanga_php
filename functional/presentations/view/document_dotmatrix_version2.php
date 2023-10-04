<?php

include_once('ROOT.php');
include_once($ROOT . 'PHPINI.php');
include_once($ROOT . $PHPFOLDER . 'properties/Constants.php');
include_once($ROOT . $PHPFOLDER . "elements/SignatureArea.php");
include_once($ROOT . $PHPFOLDER . "DAO/AdministrationDAO.php");
include_once($ROOT . $PHPFOLDER . "DAO/NewTransactionDAO.php");

$userId = ((isset($_GET["USERID"])) ? $_GET["USERID"] : "");
$userCategory = ((isset($_GET["USERCATEGORY"])) ? $_GET["USERCATEGORY"] : "");
$principalId = ((isset($_GET["PRINCIPALID"])) ? $_GET["PRINCIPALID"] : "");
$docmastId = ((isset($_GET["DOCMASTID"])) ? $_GET["DOCMASTID"] : "");
$outputTyp = ((isset($_GET["OUTPUTTYP"])) ? $_GET["OUTPUTTYP"] : "");

$dbConn->dbConnection();

$adminDAO = new AdministrationDAO($dbConn);
$hasRoleSign = $adminDAO->hasRole(mysqli_real_escape_string($dbConn->connection, $userId), mysqli_real_escape_string($dbConn->connection, $principalId), ROLE_SIGNITURE);
$hasRoleVP = $adminDAO->hasRole(mysqli_real_escape_string($dbConn->connection, $userId), mysqli_real_escape_string($dbConn->connection, $principalId), ROLE_VIEW_PRICE);

$special_field_list = "S433,D429,S430,S434,S431,S432,S427,S428,";

$newtransactionDAO = new NewTransactionDAO($dbConn);
$mfT = $newtransactionDAO->getDocumentWithDetailsForPrinting((mysqli_real_escape_string($dbConn->connection, $docmastId)), $orderBy=false, $special_field_list);
// print_r($mfT);
?>

<!DOCTYPE html>
<html>
<title>&nbsp;</title>
<head>
    <style type="text/css">

        * {
            font-family: verdana, Calibri, Arial, sans-serif;
        }

        #wrapper {
            width: 700px;
            text-align: left;
        }

        #toolbar {
            font-size: 12px;
            background: #047;
            padding: 8px 10px
        }

        #toolbar a img {
            margin: 2px 5px 2px 0px;
        }

        #toolbar a:hover {
            background: aliceBlue
        }

        #toolbar a {
            margin-right: 10px;
            float: left;
            background: #fff;
            text-align: center;
            display: block;
            border: 1px solid #047;
            padding: 0px 8px;
            line-height: 36px;
            text-decoration: none;
            color: #666;
            font-weight: bold;
        }

        #block {
            background: #fff;
            padding: 10px 5px;
            border: 1px solid #ccc;
        }

        .dtitle {
            text-align: left;
        }

        body {
            max-width: 820px;
        }

        /* print styles */
        @media print {
            .no-print, .no-print * {
                visibility: hidden !important;
                display: none !important;
            }

            body {
                max-width: 740px;
                width: auto;
                margin-top:0;
            }

            #wrapper {
                border: 0px;
            }

            #block {
                padding: 10px 0px;
                border: 0px;
            }

            th, td {
                padding: 0 !important;;
            }

            * {
                font-size: 7pt !important;
                font-family: verdana, Arial, Courier New, Courier, Lucida Sans Typewriter, Lucida Typewriter, monospace;
                line-height: 1.6em;
                font-weight: normal !important;
            }
        }

        table {
            font-size: 12px;
        }

        table.grid {
            border-collapse: collapse;
        }

        table.grid td, table.grid th {
            border: 1px solid #aaa;
        }

        table.grid th {
            background: #efefef;
        }

        .bordUnderline {
            border-bottom: 1px solid #333;
            height: 30px;
        }

        td.dc {
            background-color: white;
            color: black;
            font-weight: bold;

        }

        td.dc2 {
            background-color: white;
            color: black;
            font-weight: bold;
            font-size: 18px;
        }

        td.dc3 {
            background-color: white;
            color: black;
            font-weight: normal;
            font-size: 15px;
        }

        td.dc3a {
            background-color: white;
            color: black;
            font-weight: bold;
            font-size: 15px;
        }

        td.dc4 {
            background-color: white;
            font-weight: normal;
            font-size: 12px;
            border-collapse: collapse;
            border-left-style: solid;
            border-left-color: black;
            border-left-width: 1px;
            border-right-style: solid;
            border-right-color: black;
            border-right-width: 1px;
        }

        td.dc5 {
            background-color: white;
            font-weight: normal;
            font-size: 12px;
            border-collapse: collapse;
            border-left-style: solid;
            border-left-color: black;
            border-left-width: 1px;
            border-right-style: solid;
            border-right-color: black;
            border-right-width: 1px;
        }

        td.dc6 {
            border-collapse: collapse;
            border-top-style: solid;
            border-top-color: black;
            border-top-width: 1px;
        }

        td.dc7 {
            border-collapse: collapse;
            border-bottom-style: solid;
            border-bottom-color: black;
            border-bottom-width: 1px;
        }

        td.dc8 {
            border-collapse: collapse;
            border-style: solid solid solid solid;
            border-color: black;
            border-width: 1px;
        }

        th.th1 {
            text-align: left;
            font-size: 15px;
            font-weight: bold;
            background-color: white;
            border-collapse: collapse;
            border-left-style: solid;
            border-left-width: 1px;
            border-left-color: black;
            border-right-style: solid;
            border-right-width: 1px;
            border-right-color: black;
            border-top-style: solid;
            border-top-width: 1px;
            border-top-color: black;
            border-bottom-style: solid;
            border-bottom-width: 1px;
            border-bottom-color: black;
        }
    </style>
    <script type="text/javascript" language="javascript" src="<?php echo $ROOT . $PHPFOLDER ?>js/jquery.js"></script>
</head>
<body>
<div align="center" class="no-print">
    <table id="wrapper" cellspacing="0" cellpadding="0">
        <tr>
            <td>
                <div id="toolbar">
                    <a href="javascript:window.print();"><img
                                src="<?php echo $ROOT . $PHPFOLDER ?>images/print-icon.png" border="0" alt=""
                                align="left"> PRINT</a>
                    <?php if ($userCategory == "P") { ?>
                        <a href="javascript:;" onclick="emailDoc1();"><img
                                    src="<?php echo $ROOT . $PHPFOLDER ?>images/email-icon.png" border="0" alt=""
                                    align="left"> Email Self</a>
                        <a href="javascript:;" onclick="emailDoc2();"><img
                                    src="<?php echo $ROOT . $PHPFOLDER ?>images/email-icon.png" border="0" alt=""
                                    align="left"> Email Customer</a>
                    <?php } ?>
                    <div style="clear:both;"></div>
                </div>
            </td>
        </tr>
    </table>
</div>

<div class="paging" data-page="1">

    <!-- document row 1 - date, document type, doc number /-->
    <table style="border-collapse:collapse;width:230px;margin-left:472px;height:36px;overflow:hidden;margin-top: 140px;page-break-before: always">
        <tr>
            <td class="dc3" style="text-align:left;" nowrap><span style="font-weight:bold ;"
                                                                  class="no-print">Date&nbsp;</span><span
                        style="font-weight:normal;">&nbsp;&nbsp;&nbsp;<?php echo str_replace('-', '.', $mfT[0]["invoice_date"]); ?></span>
            </td>
            <?php 
            if (in_array($mfT[0]['status_uid'], [DST_INVOICED, DST_DELIVERED_POD_OK, DST_DIRTY_POD])) {y?>
                    <td class="dc" style="text-align:left;">TAX INVOICE</td>
            <?php 
            }
            if (trim($mfT[0]['invoice_number']) == '') { ?>
                   <td class="dc3" style="text-align:right;" nowrap><span style="font-weight:bold;" class="no-print">Invoice Number&nbsp;</span><span
                                   style="font-weight:normal ;"><?php echo substr($mfT[0]['document_number'], 0, 8); ?></span>
                   </td>
            <?php 
            } else { ?>
                   <td class="dc3" style="text-align:right;" nowrap><span style="font-weight:bold;" class="no-print">Invoice Number</span>&nbsp;&nbsp;<span
                                   style="font-weight:normal ;"><?php echo substr(trim($mfT[0]['invoice_number']), 0, 15); ?></span>
            </td>
            <?php 
            } ?>
        </tr>
    </table>

    <!-- document row 2 - invoice to, delivery to /-->
    <table style="border-collapse:collapse;;width:610px;margin-left:18px;margin-top:17px;height:80px;overflow:hidden;">
        <tr class="no-print">
            <td class="dc3" width="50%;" style="text-align:left;" nowrap><span style="font-weight:bold;">Customer Delivered To</span>
            </td>
            <td class="dc3" width="50%;" style="text-align:right;" nowrap><span style="font-weight:bold;">Customer Invoice to</span>&nbsp;&nbsp;
            </td>
        </tr>
        <tr>
            <td class="dc3" width="50%;" style="text-align:left;" nowrap><span
                            style="font-weight:normal;"><?php echo $mfT[0]['store_name']; ?></span></td>
            <td class="dc3" width="50%;" style="text-align:left;padding-left" nowrap><span
                            style="font-weight:normal;"><?php echo $mfT[0]['bill_name']; ?></span></td>
        </tr>
        <tr>
            <td class="dc3" width="50%;" style="text-align:left;" nowrap><span
                        style="font-weight:normal;"><?php echo $mfT[0]['deliver_add1']; ?></span></td>
            <td class="dc3" width="50%;" style="text-align:left;" nowrap><span
                        style="font-weight:normal;"><?php echo $mfT[0]['bill_add1']; ?></span></td>
        </tr>
        <tr>
            <td class="dc3" width="50%;" style="text-align:left;" nowrap><span
                        style="font-weight:normal;"><?php echo $mfT[0]['deliver_add2']; ?></span></td>
            <td class="dc3" width="50%;" style="text-align:left;" nowrap><span
                        style="font-weight:normal;"><?php echo $mfT[0]['bill_add2']; ?></span></td>
        </tr>

        <tr>
            <td class="dc3" width="50%;" style="text-align:left;" nowrap><span
                        style="font-weight:normal;"><?php echo $mfT[0]['deliver_add3']; ?></span></td>
            <td class="dc3" width="50%;" style="text-align:left;" nowrap><span
                        style="font-weight:normal;"><?php echo $mfT[0]['bill_add3']; ?></span></td>
        </tr>
    </table>
    <!-- document row 3 - ACC NUMBER, DELIVERY ACC NUMBER /-->
    <table style="border-collapse:collapse;;width:450px;margin-left:175px;margin-top:29px;">
        <tr>
            <td width="75%"><?php echo $mfT[0]['sp7']; ?></td>
            <td width="25%"><span style="padding-left:5px;">&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $mfT[0]['old_account']; ?></span></td>
        </tr>
    </table>
    <!-- document row 4 - SPECIAL INSTRUCTIONS /-->
    <table style="border-collapse:collapse;width:400px;margin-left:334px;height:36px;margin-top:10px;">
        <tr>
            <td style="width: 130px;max-width: 130px;">INSTRUCTIONS</td>
            <td style="width: 30px;">&nbsp;</td>
            <td><?php echo $mfT[0]['delivery_instructions']; ?></td>
        </tr>
    </table>
    <!-- document row 5 - DOCUMENT HEADER /-->
    <table style="border-collapse:collapse;width:730px;margin-left:2px;height:36px;margin-top:20px;">
        <tr>
            <td style="width:8%; "><span style="font-weight:bold ;" class="no-print">Customer Reference </span><?php echo $mfT[0]["customer_order_number"] ?></td>
            <td style="width:28%; text-align: center;">GDS</td>
            <td style="width:8%;  text-align: right;">30<br>DAYS</td>
            <td style="width:6%;  text-align: right; font-size:6pt !important;">%</td>
            <td style="width:12%; text-align: center;"><span style="font-weight:bold ;" class="no-print">Customer VAT No. </span><?php echo $mfT[0]['vat_number']; ?></td>
            <td style="width:10%;  text-align: center;">Z04</td>
            <td style="width:10%;  text-align: right;"><?php echo $mfT[0]["sp2"] ?></td>
            <td style="width:9%;  text-align: right;"><?php echo $mfT[0]['document_number']; ?></td>
            <td style="width:9%; text-align: right;" class="page-no">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<!-- page no. /-->1</td>
        </tr>
    </table>
    <!-- order details /-->
    <div class="page-details" style="height:430px;overflow:hidden;margin-left:5px;">
        <table style="border-collapse:collapse;margin-top:39px;width:auto;">
            <tbody>
                  <?php
                      $totQ  = 0;
                      $totLP = 0;
                      $totDV = 0;
            $totCP = 0;
            $totNett = 0;
            $totVAT = 0;
            $totTot = 0;
            $weightTot = 0;
            $cls = "dc5";

            foreach ($mfT as $lineNo => $row) {
                $nettCP = 0;
                $nettcp = $row["extended_price"];
                $weightTot += ($row['document_qty'] * $row['weight']);
                $totVAT += $row["vat_amount"];
                $totTot += $row["total"];
                $totNett += $nettcp; ?>
                <tr>
                    <td style="width: 60px;font-size:6pt !important;vertical-align: top"><?php
                        //split product code up, we only have space for around 9 characters here..
                        if(strlen($row["product_code"]) > 9){
                            $codeParts = str_split($row["product_code"], 8);
                            echo implode("-<br>",$codeParts);
                        } else {
                            echo $row["product_code"];
                        }
                        ?></td>
                    <td style="width: 155px;vertical-align: top"><?php echo $row["product_description"] ?></td>
                    <td style="width: 52px;text-align: center;vertical-align: top"><?php echo $row["ordered_qty"] ?></td>
                    <td style="width: 55px;text-align: center;vertical-align: top"><?php echo $row["document_qty"] ?></td>
                    <td style="width: 53px;text-align: center;vertical-align: top"><!-- to follow qty /-->0</td>
                    <td style="width: 45px;;vertical-align: top"><!-- unit? -->&nbsp;</td>
                    <td style="width: 47px;text-align: right;vertical-align: top"><?php echo number_format($row["selling_price"], 2, ".", " ") ?>&nbsp;</td>
                    <td style="width: 45px;;vertical-align: top"><!-- discount /--> &nbsp;</td>
                    <td style="width: 62px;text-align: right;vertical-align: top"><?php echo number_format($nettcp, 2, ".", " ") ?>&nbsp;</td>
                    <td style="width: 65px;text-align: right;vertical-align: top"><?php echo number_format($row["vat_amount"], 2, ".", " ") ?>&nbsp</td>
                    <td style="width: 90px;text-align: right;vertical-align: top"><?php echo number_format($row["total"], 2, ".", " ") ?>&nbsp;</td>
                </tr>
                <tr>
                    <!-- spacer row /-->
                    <td colspan="11" style="font-size:2pt !important;">&nbsp;</td>
                </tr>
            <?php
            } // endforeach; 
            ?>
            </tbody>
        </table>
    </div>

</div>

<div class="page-footer">

    <!-- total excl & vat row /-->
    <table style="border-collapse:collapse;;width:120px;margin-left:525px;height:18px;margin-top:50px;">
        <tr>
            <td style="width: 70px;font-size:6.5pt !important;"
                nowrap>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo number_format($totNett, 2, '.', ' '); ?></td>
            <td style="width: 60px;font-size:6.5pt !important;"
                nowrap>&nbsp;&nbsp;&nbsp;&nbsp;<?php echo number_format($totVAT, 2, '.', ' '); ?></td>
        </tr>
    </table>

    <!-- total invoice row /-->
    <table style="border-collapse:collapse;;width:90px;margin-left:655px;height:28px;margin-top:15px;">
        <tr>
            <td style="width: 100px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo number_format($totTot, 2, '.', ' '); ?></td>
        </tr>
    </table>

    <!-- total mass row /-->
    <table style="border-collapse:collapse;width:90px;margin-left:642px;height:28px;margin-top:5px;">
        <tr>
            <td style="width: 100px;">
                <?php if ($mfT[0]['status_uid'] >= 76 and $weightTot > 0) { ?>
                    <span class="no-print">Calculated Weight</span>
                    <?php echo number_format($weightTot, 2) ?> kg
                <?php } ?>
                &nbsp;
            </td>
        </tr>
    </table>
</div>
<!-- footer -->
</body>
</html>

<script type="text/javascript">
    //re-build page to fit cross multiple pages, use js to figure out how many products fit!
    var pageOne = $('.paging[data-page="1"]');

    if(pageOne.length){

        var tableHeight = 0;
        var pageRowData = {};
        var pageCount = 0;
        pageOne.find('.page-details table>tbody>tr').each(function(k, row){

            var rowHeight = $(row).height();
            if((tableHeight + rowHeight + 20) > 430){
                console.log("break;");
                tableHeight = 0;
                pageCount++;
            }

            //remove from page one.
            if(pageCount > 0){
                if(typeof pageRowData[pageCount] === "undefined"){
                    pageRowData[pageCount] = {};
                }
                pageRowData[pageCount][k] = row;
                $(row).remove()
            }

            tableHeight += rowHeight;

        });

        $.each(pageRowData, function(pageNo, rowElements){

            var pageNo = parseFloat(pageNo);
            console.log("APPENDING PAGE:");
            console.log(pageNo);
            console.log(rowElements);
            var newPage = $('.paging:first').clone();
            newPage.attr('data-page', pageNo + 1);
            newPage.find('.page-no').text(pageNo+1);
            newPage.find('.page-details table>tbody>tr').remove();

            $.each(rowElements, function(k, rowEle) {
                newPage.find('.page-details table>tbody').append(rowEle);
            });

            console.log(newPage);

            newPage.insertBefore('.page-footer');

        });

        console.log(pageRowData);
        console.log(pageCount);

    }

</script> 
