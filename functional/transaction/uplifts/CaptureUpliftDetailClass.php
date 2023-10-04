<?php
include_once('ROOT.php');
include_once($ROOT . 'PHPINI.php');
require_once($ROOT . $PHPFOLDER . "functional/main/access_control.php");
include_once($ROOT . $PHPFOLDER . 'DAO/AgedStockDAO.php');
include_once($ROOT . $PHPFOLDER . "libs/GUICommonUtils.php");

class CaptureUpliftDetail
{

    function __construct()
    {
        global $dbConn;
        $this->dbConn = $dbConn;
    }

   //jb 20230906
    public function printsummary()
    {
        echo "Hello world!<br>";
    }

    public function firstform()
    {

        ?>
        <center>
            <FORM name='Select Invoice' method=post action='CaptureUpliftDetail.php'>
                <table width="720" ; style="border:none">
                    <tr>
                        <td class=head1 Colspan="5" ;>Capture Store Uplifts</td>
                    </tr>
                    <tr>
                        <td>&nbsp</td>
                    </tr>
                    <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        <td width="38%" ; style="border:none">&nbsp</td>
                        <td width="20%" ; style="border:none">&nbsp</td>
                        <td width="20%" ; style="border:none">&nbsp</td>
                        <td width="20%" ; style="border:none">&nbsp</td>
                        <td width="2%" ; style="border:none">&nbsp</td>
                    </tr>
                    <tr class="<?php echo GUICommonUtils::styleEO($class); ?>" ;>
                    <!--jb 20230906-->
                        <td style="text-align:left" ;>Enter Uplift Number (06/09/2023)</td>
                        <td colspan="4" ; style="text-align:left"><input type="text" name="INVOICE"></td>
                    </tr>
                    <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        <td Colspan="5">&nbsp</td>
                    </tr>
                    <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        <td colspan="5" ; style="text-align:center;">
                           <INPUT TYPE="submit" class="submit" name="firstform" value="Get Uplift Details">
                            <INPUT TYPE="submit" class="submit" name="canform" value="Cancel">
                            <!--jb 20230906-->
                            <INPUT TYPE="submit" class="submit" name="printsummary" value="Print Summary">
                        </td>
                    </tr>
                    <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        <td Colspan="5">&nbsp</td>
                    </tr>
                </table>
            </form>
        </center>
        <?php
    }

    public function receiptform($principalId, $postINVOICE)
    {

        // Clean up invoice number

        $AgedStockDAO = new AgedStockDAO($this->dbConn);
        $mfDDU = $AgedStockDAO->getDocumentDetailsToUpdate(mysqli_real_escape_string($this->dbConn->connection, $principalId),
            mysqli_real_escape_string($this->dbConn->connection, $postINVOICE));

        if (count($mfDDU) > 0) {
            $parray = [81];
            if (!in_array($mfDDU[0]['document_status_uid'], $parray) && $mfDDU[0]['uplift_number'] != $postINVOICE) {
                $AgedStockDAO = new AgedStockDAO($this->dbConn);
                $whSr = $AgedStockDAO->getWareHouseReceipt($mfDDU[0]['uid']);
                ?>
                <center>
                    <form name='Select Invoice' method=post action='CaptureUpliftDetail.php'>
                        <table width="720" ; style="border:none">
                            <tr>
                                <td class=head1 Colspan="5" ;>Verify The Warehouse Receipt</td>
                            </tr>
                            <tr>
                                <td>&nbsp</td>
                            </tr>
                            <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                <td width="20%" ; style="border:none">&nbsp</td>
                                <td width="20%" ; style="border:none">&nbsp</td>
                                <td width="20%" ; style="border:none">&nbsp</td>
                                <td width="20%" ; style="border:none">&nbsp</td>
                                <td width="20%" ; style="border:none">&nbsp</td>
                            </tr>
                            <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                <td style="text-align:left" ;>&nbsp;</td>
                                <td style="text-align:left" ;>Store</td>
                                <td colspan="1" ; style="text-align:left"><?php echo $mfDDU[0]['deliver_name']; ?></td>
                                <?php
                                if (count($whSr) > 0) {
                                    $whR = 'T';
                                    ?>
                                    <td style="text-align:left" ;>Warehouse Receipt Date</td>
                                    <td colspan="1" ; style="text-align:left"><?php echo $whSr[0]['date']; ?>
                                        <input type="hidden" name="WHR" value=<?php echo $whR; ?>></td>
                                    <?php
                                } else {
                                    $whR = 'F';
                                    ?>
                                    <td colspan="2" ; style="text-align:left; font-weight:bold; color:red;">No Warehouse
                                        Receipt Captured
                                        <input type="hidden" name="WHR" value=<?php echo $whR; ?>></td>
                                    <?php
                                } ?>
                            </tr>
                            <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                <td Colspan="5"><input type="hidden" name="DOCUID"
                                                       value=<?php echo $mfDDU[0]['docUid']; ?>></td>
                            </tr>
                            <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                <td Colspan="5"><input type="hidden" name="DOCNUM"
                                                       value=<?php echo mysqli_real_escape_string($this->dbConn->connection, $postINVOICE); ?>>
                                </td>
                            </tr>
                            <!--jb 29/09/2023
                            <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                <td style="text-align:left" ;>&nbsp;</td>
                                 <td colspan="2" ; style="text-align:left">Enter the number of Boxes</td>
                                <td style="text-align:left" ;><input type="text" name="cBOX" size="5"></td>
                                <td colspan="2" ; style="text-align:left">&nbsp;</td>
                            </tr>
                            -->
                            <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                <td Colspan="5">&nbsp</td>
                            </tr>
                            <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                <td Colspan="5" style="text-align:center" ;><INPUT TYPE="submit" class="submit"
                                                                                   name="receiptform"
                                                                                   value="Verify Warehouse Receipt">
                                    <INPUT TYPE="submit" class="submit" name="canform" value="Cancel"></td>
                            </tr>
                            <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                <td Colspan="5">&nbsp</td>
                            </tr>
                        </table>
                    </form>
                </center>
                <?php
            } else { ?>
                <script type='text/javascript'>parent.showMsgBoxError('Document Already Captured')</script>
                <?php
                unset($postINVOICE);
                unset($_POST['firstform']);
            }
        } else { ?>
            <script type='text/javascript'>parent.showMsgBoxError('Document Number not Found')</script>
            <?php
            unset($postINVOICE);
            unset($_POST['firstform']);
        }
    }

    public function receiptError($rbox, $rdocmun)
    {

        ?>
        <center>
            <form name='WareHouse Receipt Error' method=post action='CaptureUpliftDetail.php'>
                <table width="500" ; style="border:none">
                    <tr>
                        <td width="20%" ;>&nbsp</td>
                        <td width="20%" ;>&nbsp</td>
                        <td width="20%" ;>&nbsp</td>
                        <td width="20%" ;>&nbsp</td>
                    </tr>
                    <tr>
                        <td Colspan="4">&nbsp</td>
                    </tr>
                    <tr>
                        <td Colspan="4">&nbsp</td>
                    </tr>
                </table>
                <table class="box" width="400" ;>
                    <tr>
                        <td width="5%" ;>&nbsp</td>
                        <td width="30%" ;>&nbsp</td>
                        <td width="30%" ;>&nbsp</td>
                        <td width="30%" ;>&nbsp</td>
                        <td width="5%" ; style="border:collapse; border-right: 2px solid; border-color: #990000;">
                            &nbsp
                        </td>
                    <tr>
                        <td Colspan="1" rowspan="3"><img src="<?php echo 'error-icon-big.png'; ?>"
                                                         style="width:60px; height:60px; float:left;"></td>
                        <td Colspan="3" style="font-size: 13px; font-weight: bold;">Warehouse Box Receipt
                            Quantity<br><br>Not Equal to Captured Box Quantity
                        </td>
                        <td Colspan="1" style="border:collapse; border-right: 2px solid; border-color: #990000;">&nbsp
                        </td>
                    </tr>
                    <tr>
                        <td Colspan="5"><input type="hidden" name="RDOCNUM"
                                               value=<?php echo mysqli_real_escape_string($this->dbConn->connection, $rdocmun); ?>>
                            <input type="hidden" name="RBOX"
                                   value=<?php echo mysqli_real_escape_string($this->dbConn->connection, $rbox); ?>>
                        </td>
                    </tr>
                    <tr>
                        <td Colspan="5" ; style="text-align:center" ;><INPUT TYPE="submit" class="submit"
                                                                             name="CaptCont" value="Continue ">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <INPUT TYPE="submit" class="submit" name="CaptCancel" value="Cancel Capture"></td>
                    </tr>
                    <tr>
                        <td Colspan="5" style="border:collapse; border-right: 2px solid; border-color: #990000;">&nbsp
                        </td>
                    </tr>
                    <tr>
                        <td Colspan="5" style="border:collapse; border-right: 2px solid; border-color: #990000;">&nbsp
                        </td>
                    </tr>
                </table>


            </form>
        </center>
        <?php
    }

    public function upliftDetailCapture($principalId,
                                        $INVOICE,
                                        $capBoxes,
                                        $restarray,
                                        $restarraydq,
                                        $restarrayrf,
                                        $restarrayntF,
                                        $restarraydam,
                                        $restarraydamB)
    {

        $AgedStockDAO = new AgedStockDAO($this->dbConn);
        $mfDDU = $AgedStockDAO->getDocumentDetailsToUpdate($principalId, $INVOICE);

        ?>
        <center>
            <form name='Select Invoice' method=post action='' id="myForm">
                <table width="1200" ; style="border:none">
                    <tr>
                        <td class=head1 colspan="6" ; style="text-align:center">Capture Uplifts from Store</td>
                    </tr>
                    <tr>
                        <td colspan="6" ; style="text-align:center;padding: 15px 0;">
                            <div style="font-size:14px;">Last Save: <span id="lastSave">...</span></div>
                        </td>
                    </tr>
                    <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        <td width="2%" ; style="border:none">&nbsp</td>
                        <td width="24%" ; style="border:none">&nbsp</td>
                        <td width="24%" ; style="border:none">&nbsp</td>
                        <td width="24%" ; style="border:none">&nbsp</td>
                        <td width="24%" ; style="border:none">&nbsp</td>
                        <td width="2%" ; style="border:none">&nbsp</td>
                    </tr>
                    <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        <td style="border:none">&nbsp</td>
                        <td colspan="1" ;
                            style="border:none; text-align:left; font-weight:bold; font-size: 12px; "><?php echo "Customer:            " . trim($mfDDU[0]['deliver_name'] . " "); ?></td>
                        <td colspan="1" ;style="border:none">&nbsp</td>
                        <td colspan="2" ;
                            style="border:none; text-align:right; font-weight:bold; font-size: 12px; "><?php echo "Document No.        " . substr($mfDDU[0]['document_number'], 2, 6) . " "; ?>
                            <input type="hidden" name="DOCNO"
                                   value=<?php echo substr($mfDDU[0]['document_number'], 2, 6); ?>>
                            <input type="hidden" name="DOCMID" value=<?php echo $mfDDU[0]['uid']; ?>>
                            <input type="hidden" name="SCBOX"
                                   value=<?php echo mysqli_real_escape_string($this->dbConn->connection, $capBoxes); ?>>
                            <input type="hidden" name="WAREHOUSE" value=<?php echo $mfDDU[0]['depot_uid']; ?>>
                            <input type="hidden" name="DOCSTAT" value="<?php echo $mfDDU[0]['Status']; ?>"></td>
                        <td style="border:none">&nbsp</td>
                    </tr>
                    <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        <td colspan="6" ; style="text-align:left">&nbsp</td>
                    </tr>
                    <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        <td style="border:none">&nbsp</td>
                        <td colspan="1" ; style="text-align:left;  font-weight:bold;   font-size: 12px; ">Total Number
                            of Boxes
                        </td>
                        <td colspan="1" ;
                            style="text-align:left   font-weight:normal; font-size: 12px; "><?php echo mysqli_real_escape_string($this->dbConn->connection, $capBoxes); ?></td>
                        <td colspan="1" ; style="text-align:right; font-weight:bold;   font-size: 12px; ">Reference  Number 1:
                        </td>
                        <td colspan="1" ; style="text-align:right; font-weight:bold;   font-size: 12px;"><input
                                    type="text" name="UREF1" size="30"></td>
                        <td colspan="1" ;>&nbsp</td>
                    </tr>
                    <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        <td style="border:none" colspan="3">&nbsp</td>
                        <td colspan="1" ; style="text-align:right; font-weight:bold;   font-size: 12px; ">Reference Number 2:
                        </td>
                        <td colspan="1" ; style="text-align:right; font-weight:bold;   font-size: 12px;"><input type="text" name="UREF2" size="30"></td>
                        <td colspan="1" ;>&nbsp</td>
                    </tr>
                    <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        <td style="border:none" colspan="3">&nbsp</td>
                        <td colspan="1" ; style="text-align:right; font-weight:bold;   font-size: 12px; ">Reference Number 3:
                        </td>
                        <td colspan="1" ; style="text-align:right; font-weight:bold;   font-size: 12px;"><input type="text" name="UREF3" size="30"></td>
                        <td colspan="1" ;>&nbsp</td>
                    </tr>
                    <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        <td style="border:none" colspan="3">&nbsp</td>
                        <td colspan="1" ; style="text-align:right; font-weight:bold;   font-size: 12px; ">Reference Number 4:
                        </td>
                        <td colspan="1" ; style="text-align:right; font-weight:bold;   font-size: 12px;"><input type="text" name="UREF4" size="30"></td>
                        <td colspan="1" ;>&nbsp</td>
                    </tr>
                    <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        <td style="border:none" colspan="3">&nbsp</td>
                        <td colspan="1" ; style="text-align:right; font-weight:bold;   font-size: 12px; ">Reference Number 5:
                        </td>
                        <td colspan="1" ; style="text-align:right; font-weight:bold;   font-size: 12px;"><input type="text" name="UREF5" size="30"></td>
                        <td colspan="1" ;>&nbsp</td>
                    </tr>
                    <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        <td style="border:none" colspan="3">&nbsp</td>
                        <td colspan="1" ; style="text-align:right; font-weight:bold;   font-size: 12px; ">Reference Number 6:
                        </td>
                        <td colspan="1" ; style="text-align:right; font-weight:bold;   font-size: 12px;"><input type="text" name="UREF6" size="30"></td>
                        <td colspan="1" ;>&nbsp</td>
                    </tr>

                    <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        <td colspan="6" ; style="text-align:left">&nbsp;</td>
                    </tr>
                    <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        <td colspan="6" ; style="text-align:left">

                        </td>
                    </tr>
                </table>
                <table width="1200" ; style="border:none" ;>
                    <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        <td style="width:  2%; text-align:center; border-left; border-none;">&nbsp</td>
                        <td style="width: 10%; text-align:center; border-left: 1px dotted red; font-weight:bold;">
                            Part<br>Number
                        </td>
                        <td style="width: 10%; text-align:center; border-left: 1px dotted red; font-weight:bold;">Bar
                            Code
                        </td>
                        <td style="width: 31%; text-align:center; border-left: 1px dotted red; font-weight:bold;">
                            Description
                        </td>
                        <td style="width:  7%; text-align:center; border-left: 1px dotted red; font-weight:bold;">
                            Aged<br>Stock
                        </td>
                        <td style="width:  7%; text-align:center; border-left: 1px dotted red; font-weight:bold;">
                            Uplifted
                        </td>
                        <td style="width:  7%; text-align:center; border-left: 1px dotted red; font-weight:bold;">
                            Display
                        </
                        ></td>
                        <td style="width:  7%; text-align:center; border-left: 1px dotted red; font-weight:bold;">
                            Store<br>Refuse
                        </td>
                        <td style="width:  7%; text-align:center; border-left: 1px dotted red; font-weight:bold;">
                            Not<br>found
                        </td>
                        <td style="width:  2%; text-align:center; border-left: 1px dotted red; font-weight:bold;">
                            Damages<br></td>
                        <td style="width:  2%; text-align:center; border-left: 1px dotted red; font-weight:bold;">Check
                            Total
                        </td>
                        <td style="width:  1%;  border-left: 1px dotted red; font-weight:bold;">&nbsp;</td>
                    </tr>
                    <?php
                    $srcrow = 0;
                    foreach ($mfDDU as $row) {

                        $rowTot = ((int)$restarray[$row['detailUid']] +
                            (int)$restarraydq[$row['detailUid']] +
                            (int)$restarrayrf[$row['detailUid']] +
                            (int)$restarrayntF[$row['detailUid']] +
                            (int)$restarraydam[$row['detailUid']]);
                        if ($rowTot == 0) {
                            $Linetot = 0;
                            $blkColor = BLACK;
                        } else {
                            if ($rowTot == $row['ordered_qty']) {
                                $Linetot = $rowTot;
                                $blkColor = GREEN;
                            } else {
                                $Linetot = $rowTot;
                                $blkColor = RED;
                            }
                        }
                        ?>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            <td style="border-none">&nbsp</td>
                            <td style="border-left: 1px dotted red"><?php echo($row['product_code'] . " "); ?></td>
                            <td style="border-left: 1px dotted red;"><?php echo($row['outercasing_gtin'] . " "); ?></td>
                            <td style="border-left: 1px dotted red"><?php echo($row['product_description'] . " "); ?></td>
                            <td style="border-left: 1px dotted red; text-align:center;"><?php echo($row['ordered_qty'] . " "); ?></td>
                            <td style="border-left: 1px dotted red"><input style="float:right" type="text"
                                                                           name="ULQTY[]" size="5"
                                                                           value="<?php echo $restarray[$row['detailUid']]; ?>">
                                <input type="hidden" name="detID[]" value=<?php echo $row['detailUid']; ?>></td>
                            <input type="hidden" name="prodID[]" value=<?php echo $row['product_uid']; ?>></td>
                            <input type="hidden" name="prodCode[]" value=<?php echo $row['product_code']; ?>>
                            <input type="hidden" name="agedStock[]" value=<?php echo $row['ordered_qty']; ?>></td>
                            <td style="border-left: 1px dotted red"><input style="float:right" type="text"
                                                                           name="DISQTY[]" size="5"
                                                                           value="<?php echo $restarraydq[$row['detailUid']]; ?>">
                            </td>
                            <td style="border-left: 1px dotted red"><input style="float:right" type="text"
                                                                           name="RFQTY[]" size="5"
                                                                           value="<?php echo $restarrayrf[$row['detailUid']]; ?>">
                            </td>
                            <td style="border-left: 1px dotted red"><input style="float:right" type="text"
                                                                           name="NOTFND[]" size="5"
                                                                           value="<?php echo $restarrayntF[$row['detailUid']]; ?>">
                            </td>
                            <td style="border-left: 1px dotted red"><input style="float:right" type="text"
                                                                           name="DAMAGES[]" size="5"
                                                                           value="<?php echo $restarraydam[$row['detailUid']]; ?>">
                            </td>
                            <td style="border-left: 1px dotted red; font-weight:bold; color:<?php echo $blkColor; ?>  "><?php echo $Linetot; ?></td>
                            <td style="border-left: 1px dotted red; font-weight:bold">&nbsp;</td>
                        </tr>
                        <?php
                        $srcrow++;
                    } ?>

                    <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        <td colspan="13" ; style="text-align:left">&nbsp</td>
                    </tr>
                    <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        <td colspan="13" ; style="text-align:left">&nbsp</td>
                    </tr>
                    <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        <td colspan="13" ; style="text-align:center"><INPUT TYPE="submit" class="submit"
                                                                            name="finishform" value="Submit Line"><INPUT
                                    TYPE="submit" class="submit" name="canform" value="Cancel"></td>
                    </tr>
                    <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        <td colspan="13" ;>&nbsp</td>
                    </tr>
                </table>
            </form>
        </center>
        <script>

            if (typeof Storage !== "undefined") {

                const saveKey = 'uplift-autosave-<?= $principalId . '-' . substr($mfDDU[0]['document_number'], 2, 6) ?>';
                const form = document.getElementById('myForm');
                const lastSaveSpan = document.getElementById('lastSave');

                saveForm = function () {
                    const now = new Date();

                    let formValues = {};
                    const formArr = Array.from(new FormData(form));

                    formArr.forEach(([name, value]) => {
                        if (formValues[name]) {
                            formValues[name].push(value)
                        } else {
                            formValues[name] = [value]
                        }
                    });

                    const saveData = JSON.stringify({
                        entries: formValues,
                        date: now.toLocaleString()
                    })

                    localStorage.setItem(saveKey, saveData);
                    lastSaveSpan.textContent = now.toLocaleString();
                }

                // Load form data from localStorage
                const savedData = localStorage.getItem(saveKey);
                if (savedData) {

                    try {
                        const parsedData = JSON.parse(savedData);

                        if (parsedData.date && parsedData.entries && confirm("Do you want to restore the saved state of this document, saved: " + parsedData.date + "?")) {

                            // file the normal form fields
                            for (const name in parsedData.entries) {
                                let value = parsedData.entries[name];
                                if (value.length > 1) {
                                    const inputFields = form.querySelectorAll(`[name="${name}"]`);
                                    if (inputFields.length > 1) {
                                        inputFields.forEach((inputField, index) => {
                                            inputField.value = value[index] || '';
                                        });
                                    }
                                } else {
                                    const inputField = form.querySelector(`[name="${name}"]`);
                                    if (inputField) {
                                        inputField.value = value;
                                    }
                                }
                            }
                            saveForm();
                        }
                    } catch (e) {
                        console.error("Error parsing saved data", e);
                    }
                }

                form.addEventListener('input', () => {
                    saveForm();
                });

            } else {
                console.warn("No localStorage support");
            }

        </script>

        <?php
    }
}