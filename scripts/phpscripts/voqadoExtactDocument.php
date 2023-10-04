<?php

/* * ********************************************************************************************
 * *
 * *  This job can run as many times per day as is necessary according to job scheduler.
 * *
 * *  It executes notifications that occur throughout the day by triggers
 * *
 * ******************************************************************************************** */

include_once('ROOT.php');
include_once($ROOT . 'PHPINI.php');
include_once($ROOT . $PHPFOLDER . "functional/extracts/daily/extractController.php");

include_once __DIR__ . "/../../../libs/api/voqado-api/VoqadoRestAPI.php";
include_once $ROOT . $PHPFOLDER . "functional/jobs/voqado/VoqadoConstants.php";

include_once $ROOT . $PHPFOLDER . "functional/jobs/voqado/VoqadoParameters.php";

// print_r($je);
// echo "<br>";

echo $je['principal_uid'];
$prinId = $je['principal_uid'];
echo "<br>";

//static method handler.
class voqadoExtactDocument
{
    public static function generateOutput()
    {
        $className = basename(__FILE__, '.php') . 'Init';
        global $ROOT, $PHPFOLDER, $dbConn, $prinId;
        $obj = new $className();
        return $obj->generateOutput();
    }
}

class voqadoExtactDocumentInit extends extractController
{

    public function generateOutput()
    {

        global $ROOT, $PHPFOLDER, $prinId;

        $voqadoParms = new VoquadoParms($dbConn);
        $vPa = $voqadoParms->getPrincipalParams($prinId);

        $principalUid = $vPa[0]['principal_uid']; //uid of principal extract.

        $voqadocode = $vPa[0]['voqado_code'];

        $specialFieldUid = $vPa[0]['voqado_account_field'];

        if ($vPa[0]['daily_extract_custom'] == 'NT_DAILY_EXTRACT_CUSTOM') {
            $dailyExtractCustom = '10';
        } elseif ($vPa[0]['daily_extract_custom'] == 'NT_DAILY_EXTRACT_ALTCUSTOM1') {
            $dailyExtractCustom = '12';
        } elseif ($vPa[0]['daily_extract_custom'] == 'NT_DAILY_EXTRACT_ALTCUSTOM2') {
            $dailyExtractCustom = '13';
        } elseif ($vPa[0]['daily_extract_custom'] == 'NT_DAILY_EXTRACT_ALTCUSTOM3') {
            $dailyExtractCustom = '14';
        } elseif ($vPa[0]['daily_extract_custom'] == 'NT_DAILY_EXTRACT_ALTCUSTOM4') {
            $dailyExtractCustom = '15';
        } else {
            $dailyExtractCustom = '';
        }

        $period_month_1 = $vPa[0]['period_month_1'];
        $period_month_2 = $vPa[0]['period_month_2'];
        $period_month_3 = $vPa[0]['period_month_3'];
        $period_month_4 = $vPa[0]['period_month_4'];
        $period_month_5 = $vPa[0]['period_month_5'];
        $period_month_6 = $vPa[0]['period_month_6'];
        $period_month_7 = $vPa[0]['period_month_7'];
        $period_month_8 = $vPa[0]['period_month_8'];
        $period_month_9 = $vPa[0]['period_month_9'];
        $period_month_10 = $vPa[0]['period_month_10'];
        $period_month_11 = $vPa[0]['period_month_11'];
        $period_month_12 = $vPa[0]['period_month_12'];

        echo "<pre>";
        print_r($vPa);
        echo "<pre>";
        echo $dailyExtractCustom;
        echo "PP";
        echo "<br>";

        //name in email and folder to place bkup files.
        $pArr = $this->principalDAO->getPrincipalItem($principalUid);
        if (count($pArr) == 0) {
            BroadcastingUtils::sendAlertEmail("System Error", "Extract failed to load principal item in " . get_class($this) . "!", "Y");
            return $this->errorTO;
        }
        $principalName = $pArr[0]['principal_name'];
        $folder = $principalUid . '_' . explode(' ', strtolower($pArr[0]['principal_name']))[0]; //folder replaced with principal id + first WORD of principal.

        //use the receipients listed in the notification table instead of hard coding them!!!
        //expecting only one row loaded per principal extract
        $reArr = $this->bIDAO->getNotificationRecipients($principalUid, $dailyExtractCustom);
        if (count($reArr) == 0) {
            BroadcastingUtils::sendAlertEmail("System Error", "Extract failed load recipients in " . get_class($this) . "!", "Y");
            return $this->errorTO;
        }
        $recipientUId = $reArr[0]['uid'];

        if (!$this->skipInsert) {
            // Create new log entries into SMART_EVENT - This doesn't rely on a notification trigger
            $rTO = $this->postExtractDAO->queueAllInvoiced($principalUid, $recipientUId, $inclCancelled = false);  //use the loaded receipientUID and not the notification type... *** same as document confirmations***
            if ($rTO->type != FLAG_ERRORTO_SUCCESS) {
                BroadcastingUtils::sendAlertEmail("System Error", "Export failed to call postExtractDAO->queueAllInvoiced in " . get_class($this) . " " . $rTO->description, "Y");
            } else {
                $this->dbConn->dbinsQuery("commit;");
            }
            //credits and debit notes
            $rTO = $this->postExtractDAO->queueAllCreditsAndDebits($principalUid, $recipientUId, [DT_CREDITNOTE, DT_MCREDIT_OTHER, DT_MCREDIT_PRICING]);  //use the loaded receipientUID and not the notification type... *** same as document confirmations***
            if ($rTO->type != FLAG_ERRORTO_SUCCESS) {
                BroadcastingUtils::sendAlertEmail("System Error", "Export failed to call postExtractDAO->queueAllCreditsAndDebits in " . get_class($this) . " " . $rTO->description, "Y");
            } else {
                $this->dbConn->dbinsQuery("commit;");
            }
        }
        $seDocs = $this->extractDAO->getDailyExtractInvoicedOrders($principalUid, $recipientUId);

        /*  SUCCESS POINT - 1  */
        //nothing to do...
        if (count($seDocs) == 0) {
            echo "Successfully Completed Extract : " . get_class($this) . " - No entries!<br>";
            $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
            $this->errorTO->description = "Successful";
            return $this->errorTO;
        }

        //group array
        $grpDocs = [];
        $psms = [];
        foreach ($seDocs as $k => $r) {

            $type = 'i';
            if ($r['document_type_uid'] == DT_CREDITNOTE) {
                $type = 'c';
            } elseif ($r['document_type_uid'] == DT_MCREDIT_OTHER) {
                $type = 'm';
            } elseif ($r['document_type_uid'] == DT_MCREDIT_PRICING) {
                $type = 'm';
            }

            $grpDocs[$type][$r['dm_uid']][] = $r;
            $psms[$r["principal_store_uid"]] = $r["principal_store_uid"];
        }

        // get special field values for all stores in above docs
        if (sizeof($psms) > 0) {
            $sfvals_PA = $this->miscDAO->getPrincipalSpecialFieldValuesMultEntities($principalUid, $specialFieldUid, implode(",", $psms), CT_STORE_SHORTCODE, $arrayIndex = "entity_uid");
        }
        //setup api class
        $voqadoApi = new VoqadoRestAPI(VoqadoConstants::ApiUri, VoqadoConstants::ApiUsername, VoqadoConstants::ApiPassword);

        foreach ($grpDocs as $type => $orders) {

            //   print_r($orders);

            $errorSEUIdArr = [];
            $successSEUIdArr = [];
            $successCount = 0;

            foreach ($orders as $ord) {
                if (empty($sfvals_PA[$ord[0]["principal_store_uid"]]['value'])) {  //has no special field and/or blank...
                    $errorSEUIdArr[] = $ord[0]['se_uid']; //list of smart event errors
                } else {
                    //setup header

                    echo "Voqado REST API - Post Invoices\n";

                    // php settings
                    set_time_limit(15 * 60); // 15 mins
                    error_reporting(-1);
                    ini_set('display_errors', 1);
                    $successCount++;
                    $successSEUIdArr[] = $ord[0]['se_uid']; //list of smart event success

                    $storeAcc = trim($sfvals_PA[$ord[0]["principal_store_uid"]]['value']);

                    //period
                    $period = '00';
                    $yy = '';
                    switch (date("m", strtotime($ord[0]["invoice_date"]))) {
                        case '01':
                            $period = $period_month_1;
                            $yy = date("Y", strtotime($ord[0]["invoice_date"]));
                            break;
                        case '02':
                            $period = $period_month_2;
                            $yy = date("Y", strtotime($ord[0]["invoice_date"]));
                            break;
                        case '03':
                            $period = $period_month_3;
                            $yy = date("Y", strtotime($ord[0]["invoice_date"])) + 1;
                            break;
                        case '04':
                            $period = $period_month_4;
                            $yy = date("Y", strtotime($ord[0]["invoice_date"])) + 1;
                            break;
                        case '05':
                            $period = $period_month_5;
                            $yy = date("Y", strtotime($ord[0]["invoice_date"])) + 1;
                            break;
                        case '06':
                            $period = $period_month_6;
                            $yy = date("Y", strtotime($ord[0]["invoice_date"])) + 1;
                            break;
                        case '07':
                            $period = $period_month_7;
                            $yy = date("Y", strtotime($ord[0]["invoice_date"])) + 1;
                            break;
                        case '08':
                            $period = $period_month_8;
                            $yy = date("Y", strtotime($ord[0]["invoice_date"])) + 1;
                            break;
                        case '09':
                            $period = $period_month_8;
                            $yy = date("Y", strtotime($ord[0]["invoice_date"])) + 1;
                            break;
                        case '10':
                            $period = $period_month_10;
                            $yy = date("Y", strtotime($ord[0]["invoice_date"])) + 1;
                            break;
                        case '11':
                            $period = $period_month_11;
                            $yy = date("Y", strtotime($ord[0]["invoice_date"])) + 1;
                            break;
                        case '12':
                            $period = $period_month_12;
                            $yy = date("Y", strtotime($ord[0]["invoice_date"])) + 1;
                            break;
                    }
                    if ($type == 'i') {
                        $docType = 'IN';
                        $docName = 'Invoice';
                        if ($principalUid == 71) {
                            $docNo = ltrim($ord[0]['invoice_number'], '0');
                        } else {
                            $docNo = ltrim($ord[0]['document_number'], '0');
                        }

                        $PoSNo = trim(str_replace(['"', "'"], ['', ''], $ord[0]["customer_order_number"]));
                        $allocationref = '';
                        $allocationAmount = 0;
                        $allocationrefto = '';
                    } elseif ($type == 'm') {
                        $docType = 'CR';
                        $docName = 'Credit Note';
                        if (trim($ord[0]['alternate_document_number']) == '') {
                            $docNo = ltrim(($ord[0]['document_number'],'0');
                        } else {
                            $docNo = ltrim($ord[0]['alternate_document_number'], '0');
                        }
                        $PoSNo = trim(str_replace(['"', "'"], ['', ''], $ord[0]["customer_order_number"]));
                        $allocationref = '';
                        $allocationAmount = 0;
                        $allocationrefto = '';
                    } else {
                        $docType = 'CR';
                        $docName = 'Credit Note';
                        if (trim($ord[0]['alternate_document_number']) == '') {
                            $docNo = ltrim($ord[0]['document_number'], '0');
                        } else {
                            $docNo = ltrim($ord[0]['alternate_document_number'], '0');
                        }
                        $PoSNo = trim(str_replace(['"', "'"], ['', ''], $ord[0]["source_document_number"]));
                        $allocationref = ltrim($docNo, '0');
                        $allocationAmount = round($ord[0]['invoice_total'] + 0, 2);
                        $allocationrefto = (ltrim($ord[0]["source_document_number"], '0');
                    }
                    $iDate = date("d/m/Y", strtotime($ord[0]["invoice_date"]));
                    $yDate = $yy;
                    $deliverName = trim($ord[0]["deliver_name"]);

                    $invoiceData = [
                        's_coyid' => $voqadocode,
                        's_code' => $storeAcc,
                        's_sname' => $deliverName,
                        's_ref' => $docNo,
                        's_type' => $docType,
                        's_desc' => $docName,
                        's_tyear' => $yDate,
                        's_period' => $period,
                        's_tdate' => $iDate,
                        's_custono' => $PoSNo,
                        's_exclamount' => round($ord[0]['exclusive_total'], 2),
                        's_vamt' => round($ord[0]['vat_total'], 2),
                        's_amount' => round($ord[0]['invoice_total'] + 0, 2),
                        's_userid' => 'TS',
                        's_transactionstatus' => 'A',
                        'smsadata' => [
                            ['s_code' => ''],
                            //add lines here
                        ],
                        'oiallocations' => [
                            ['s_allocationref' => $allocationref,
                                's_allocationrefto' => $allocationrefto,
                                's_allocationamount' => 0 - $allocationAmount],

                            ['s_allocationref' => $allocationrefto,
                                's_allocationrefto' => $allocationref,
                                's_allocationamount' => $allocationAmount],
                        ],
                    ];
                    //display the sent data!

                    $response = $voqadoApi->Request("POST", "vqdebtortransactions/upddrtn", $invoiceData);

                    if ($response->getSuccess()) {
                        // everything went OK!
                        echo "SUCCESS!\n<br>";
                        print_r($response->getBody());
                    } else {
                        echo "ERROR!\n<br>";
                        echo $response->getErrorMessage();
                    }
                } //eo special field check
            } //eo documents

            // SETUP DISTRIBUTION
            $postingDistributionTO = new PostingDistributionTO;
            $postingDistributionTO->DMLType = "INSERT";
            $postingDistributionTO->deliveryType = BT_EMAIL;
            $postingDistributionTO->subject = (($type == 'i') ? $this->getTemplateInvoiceSubject() : $this->getTemplateCreditSubject()); //might have no filename if all errors therefore don't display on subject line...
            $postingDistributionTO->body = $this->getTemplateBody($principalName, $successCount, count($errorSEUIdArr), $this->getManagementURL($principalUid));

            $recipientList = explode(",", $reArr[0]["user_uid_list"]); //if blank, sizeof() will still be 1
            $recipientsCheckCount = 0;  //check if atleast one distribution item was posted, incase of invalid recipients

            foreach ($recipientList as $re) {

                $mfC = $this->miscDAO->getContactItem($principalUid, "", $re);
                if (sizeof($mfC) == 0) {
                    BroadcastingUtils::sendAlertEmail("System Error", get_class($this) . " Extract for nr.UID {$recipientUId} has an invalid Recipient/Contact: '{$re}'.", "Y", true);
                    continue;
                }

                $postingDistributionTO->destinationAddr = $mfC[0]["email_addr"];
                $dResult = $this->postDistributionDAO->postQueueDistribution($postingDistributionTO);

                if ($dResult->type != FLAG_ERRORTO_SUCCESS) {
                    $this->errorTO->type = FLAG_ERRORTO_ERROR;
                    $this->errorTO->description = get_class($this) . " Extract distribution queue failed for for nr.UID {$recipientUId} and Recipient/Contact: '{$re}'.";
                    BroadcastingUtils::sendAlertEmail("System Error", $this->errorTO->description, "Y", true);
                    return $this->errorTO;
                } else {
                    $recipientsCheckCount++;  //successful
                }
            }
            if ($recipientsCheckCount == 0) {
                $this->errorTO->type = FLAG_ERRORTO_ERROR;
                $this->errorTO->description = "Failed in " . get_class($this) . " extract no valid Recipient/Contact found, no outgoing mail generated!";
                BroadcastingUtils::sendAlertEmail("Error in " . get_class($this) . " extract", $this->errorTO->description, "Y", false);
                return $this->errorTO;
            }
            /*
             *  UPDATE SMART EVENT in BULK
             */
            //SUCCESSFUL ITEMS
            if (sizeof($successSEUIdArr) > 0) {
                $bIResult = $this->postBIDAO->setSmartEventStatusBulk(implode(",", $successSEUIdArr), $response->getErrorMessage(), "");
                if ($bIResult->type != FLAG_ERRORTO_SUCCESS) {
                    $this->errorTO->type = FLAG_ERRORTO_ERROR;
                    $this->errorTO->description = "Failed in " . get_class($this) . " extract on setting success setSmartEventStatusBulk with {$bIResult->description}";
                    BroadcastingUtils::sendAlertEmail("Error in " . get_class($this) . " extract", $this->errorTO->description, "Y", false);
                    return $this->errorTO;
                }
            }
            // ERROR EXTRACTS - MARK SE AS "E", for extract errors display screen.
            if (sizeof($errorSEUIdArr) > 0) {
                $bIResult = $this->postBIDAO->setSmartEventStatusBulk(implode(",", $errorSEUIdArr), $specialFieldUid, "", FLAG_ERRORTO_ERROR);
                if ($bIResult->type != FLAG_ERRORTO_SUCCESS) {
                    $this->errorTO->type = FLAG_ERRORTO_ERROR;
                    $this->errorTO->description = "Failed in " . get_class($this) . " on setting errror setSmartEventStatusBulk with {$bIResult->description}";
                    BroadcastingUtils::sendAlertEmail("Error in " . get_class($this) . " extract", $this->errorTO->description, "Y", false);
                    return $this->errorTO;
                }
            }
        }

        /*-------------------------------------------------*/

        echo "Successfully Completed Extract : " . get_class($this) . "<br>";

        /*  SUCCESS POINT - 2  */
        $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
        $this->errorTO->description = "Successful";
        return $this->errorTO;
    }
}


//direct run!
if ($runMe) {
    directRunExtract(__FILE__);
}
