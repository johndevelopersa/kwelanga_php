<?php


include_once('ROOT.php');
include_once($ROOT . 'PHPINI.php');
require_once($ROOT . $PHPFOLDER . 'functional/ws/lib/nusoap.php');  //soap lib
include_once($ROOT . $PHPFOLDER . 'libs/BroadcastingUtils.php');
require_once $ROOT . $PHPFOLDER . 'libs/newrelic.php';


class PnPClient
{


    public $jobCount = 0; //counter for script output

    private $dbConn;
    private $transactionDAO;
    private $postBIDAO;


    public function __construct($dbConn)
    {

        $this->dbConn = $dbConn;

        $this->transactionDAO = new TransactionDAO($this->dbConn);
        $this->postBIDAO = new PostBIDAO($this->dbConn);

    }

    public function runProcess()
    {

        //START PROCESS
        $rs = $this->transactionDAO->getPnPWSInvoiced();

        $this->jobCount = count($rs);

        if (count($rs) == 0) {

            echo 'No Queued Items<BR>';

        } else {

            foreach ($rs["header"] as $doc) {

                $seTO = new SmartEventTO();

                $seTO->type = SE_INVOICE_UPLOAD;
                $seTO->typeUid = $doc["principal_uid"];
                $seTO->dataUid = array($doc["uid"]);

                // PNP must not be sent zero quantity lines or zero value lines / invoice !
                $hasLines = false;
                foreach ($rs["detail"][$doc["uid"]] as $key => $line) {
                    if ((floatval($line["total"]) > 0) && ($line["document_qty"] > 0)) {
                        $hasLines = true;
                    } else {
                        unset($rs["detail"][$doc["uid"]][$key]);
                    }
                }

                if ($hasLines) {
                    $callResult = $this->UploadInvoice(
                        $doc,
                        $rs["detail"][$doc["uid"]],
                        $options = array(
                            'uid' => $doc['uid']
                        )
                    );


                    $seTO->status = $callResult->type;
                    $seTO->statusMsg = $callResult->description;

                } else {

                    $seTO->status = FLAG_ERRORTO_INFO; // use this status so that reporting will show the appropriate message
                    $seTO->statusMsg = "Successful - Zero priced Invoice is not uploaded";

                }

                // var_dump($callResult);

                //
                // interpret callResult
                //


                $resultTO = $this->postBIDAO->postSmartEventBulk($seTO); // used instead of single insert method due to field usage

                if ($resultTO->type != FLAG_ERRORTO_SUCCESS) {
                    echo "<br><span style='color:red'>ERROR creating SMART EVENT in PnPClient.runProcess(): </span>" . $resultTO->description;
                    $this->dbConn->dbinsQuery("rollback");
                } else {
                    $this->dbConn->dbinsQuery("commit");
                }

                // email RT admin staff for urgent action if error in upload
                // - excl INFO status
                if ($seTO->status == FLAG_ERRORTO_ERROR) {
                    BroadcastingUtils::sendSupportEmail("Please Action! PnP Invoice Upload Error",
                        "The System atempted to upload an invoice to the PnP System but failed with the following reason :\n\n{$seTO->statusMsg}\n\nPlease correct the data ASAP as this is a time-critical service",
                        "Y",
                        false);
                }

            }

        }

    }


    public function UploadInvoice($header, $detail, $addOptions = array())
    {
        global $ROOT, $PHPFOLDER;

        $errorTO = new ErrorTO();

        //data template to send.
        include($ROOT . $PHPFOLDER . 'functional/ws/templates/PnPInvoiceSendingData.php');
        include($ROOT . $PHPFOLDER . 'functional/ws/templates/PnPInvoiceDetailSendingData.php');

        $uniqueCreatorId = $header["uid"];
        $invoiceNumber = str_pad(trim(((empty($header["invoice_number"])) ? $header["document_number"] : $header["invoice_number"])), 8, "0", STR_PAD_LEFT);
        $invoiceNumber = preg_replace("/^[0]+/", "", $invoiceNumber); // PnP must not have any leading zeros, even if the number is only 4 digits long
        $deliveryDate = ((empty($header["delivery_date"]) || $header["delivery_date"] == "0000-00-00") ? $header["order_date"] : $header["delivery_date"]);

        // basic validations for submitted data
        $errors = array();
        if (trim($header["principal_gln"]) == "") $errors[] = "Principal GLN not supplied";
        if (trim($header["ean_code"]) == "") $errors[] = "Store GLN not supplied";
        if (trim($header["invoice_date"]) == "" || trim($header["invoice_date"]) == "0000-00-00") $errors[] = "Invoice Date not supplied";
        if (trim($deliveryDate) == "" || trim($deliveryDate) == "0000-00-00") $errors[] = "Delivery Date not supplied";
        if (trim($invoiceNumber) == "") $errors[] = "Invoice Number not supplied";
        if (trim($header["principal_vat_number"]) == "") $errors[] = "Principal VAT Number not supplied";
        if (!preg_match("/^[0-9]{0,1}[0-9]{9}$/", $header["customer_order_number"])) $errors[] = "Invalid Order PO Number ({$header["customer_order_number"]}) for {$header["principal_name"]}, Doc {$header["document_number"]}";

        $now = date("c"); // ISO 8601 ~ 2013-01-30T12:42:31+02:00

        // replace values
        $sendXML = str_replace(array("&&var_username&&",
            "&&var_password&&",
            "&&var_rtGLN&&",
            "&&var_principalGLN&&",
            "&&var_storeGLN&&",
            "&&var_uniqueCreatorId&&",
            "&&var_invoiceCreationDateTime&&",
            "&&var_invoiceNumber&&",
            "&&var_sellerVATNumber&&",
            "&&var_invoiceTotalInclusive&&",
            "&&var_invoiceTotalExclusive&&",
            "&&var_invoiceTotalVatAmount&&",
            "&&var_purchaseOrderReference&&"),
            array(WS_URL_PNP_INVOICE_USERNAME,
                WS_URL_PNP_INVOICE_PASSWORD,
                RT_GLN,
                $header["principal_gln"],
                $header["ean_code"],
                $uniqueCreatorId,
                $now,
                $invoiceNumber,
                $header["principal_vat_number"],
                $header["invoice_total"],
                $header["exclusive_total"],
                $header["vat_total"],
                $header["customer_order_number"]),
            $sendXML);

        $dtlArr = array();
        foreach ($detail as $row) {
            // basic validations for submitted data cont...
            if (!preg_match(GUI_PHP_INTEGER_REGEX, $row["line_no"])) $errors[] = "Invalid Line Number";
            // commented out 15Dec2014 - PnP would prefer to receive invoice regardless
            // re-instated 20Feb2015 - PnP complaining they receiving blank GTINs ?????? We now email to RT staff for immediate action
            if (!preg_match("/^[0-9]{8,}$/", $row["outercasing_gtin"])) $errors[] = "Invalid Product ({$row["product_code"]}) GTIN for {$header["principal_name"]}, Doc {$header["document_number"]}, PO {$header["customer_order_number"]}";

            $weight = ""; // ($row["weight"]) PnP require that if we send a weight (ours are all "1" anyway), then they require a proper unitOfMeasure, so we don't send weight

            if ($header["principal_uid"] == 64) {
                $cDocumentQty = $row["document_qty"] / $row["items_per_case"];
                $cNetPrice = $row["net_price"] * $row["items_per_case"];
                $cExtPrice = $row["extended_price"];
                $cTotal = $row["total"];
                $cVatAmount = $row["vat_amount"];
            } else {
                $cDocumentQty = $row["document_qty"];
                $cNetPrice = $row["net_price"];
                $cExtPrice = $row["extended_price"];
                $cTotal = $row["total"];
                $cVatAmount = $row["vat_amount"];
            }
            // we must not supply KG as the var_productWeightUnit as then PnP expect the weight metrics to be filled in
            $dtlArr[] = str_replace(array("&&var_lineNumber&&",
                "&&var_productGTIN&&",
                "&&var_productCode&&",
                "&&var_quantity&&",
                "&&var_transferOfOwnershipDate&&",
                "&&var_extendedPriceExclusive&&",
                "&&var_productDescription&&",
                "&&var_sellingPriceExclusive&&",
                "&&var_extendedPriceInclusive&&",
                "&&var_vatAmount&&",
                "&&var_vatRate&&",
                "&&var_vatCategory&&",
                "&&var_productWeight&&",
                "&&var_productWeightUnit&&"),
                array($row["line_no"],
                    $row["outercasing_gtin"],
                    str_replace("&", "", $row["product_code"]),
                    $cDocumentQty,
                    $deliveryDate,
                    $row["extended_price"],
                    trim(preg_replace('/[\x00-\x1F\x80-\xFF\x22\x26\xEF\xBD\xBF]/', '', $row["product_description"])),
                    $cNetPrice,
                    $row["total"],
                    $row["vat_amount"],
                    $row["vat_rate"],
                    (($row["vat_rate"] > 0) ? "STANDARD_RATE" : "ZERO_RATE"),
                    $weight,
                    ""),
                $sendXMLDtl);
        }

        if (sizeof($errors) > 0) {
            echo "<br>Could not export PnP Invoice via WS (dm_uid:{$header["uid"]}): " . implode("<br>", $errors);
            $errorTO->type = FLAG_ERRORTO_ERROR;
            $errorTO->description = implode(",", $errors);
            return $errorTO;
        }

        $sendXML = str_replace("&&var_invoiceDetailLines&&", implode("", $dtlArr), $sendXML);
        return $this->WebServiceCall($sendXML, $addOptions, $logFolder = "pnp_invoice");

    }


    private function WebServiceCall($params, $addOptions, $logFolder)
    {
        global $ROOT;

        $errorTO = new ErrorTO();
        $client = new nusoap_client(WS_URL_PNP_INVOICE);
        // $client->soap_defencoding = 'utf-8';
        $client->xml_encoding = 'UTF-8';
        // $client->setHTTPEncoding('deflate, gzip');

        /*
          $client->setCredentials($username,$password);
        */

        $err = $client->getError();
        if ($err) {
            echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
            echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->getDebug(), ENT_QUOTES) . '</pre>';
            $errorTO->type = FLAG_ERRORTO_ERROR;
            $errorTO->description = htmlspecialchars($client->getDebug(), ENT_QUOTES);
            return $errorTO;
        }

        $result = $client->send($params, $soapAction = "http://pnpportal.co.za/InvoiceEanUcc/PutInvoiceXmlMessage");

        /*
        echo "<pre>var_dump(result) | START<br>";
        var_dump($result);
        echo "<br>var_dump(result) | END</pre>";
        */

        //DEBUG
        $debug = false;
        if ($debug) {
            if ($client->fault) {
                echo '<h2>Fault (Expect - The request contains an invalid SOAP body)</h2><pre>';
                print_r($result);
                echo '</pre>';
            } else {
                $err = $client->getError();
                if ($err) {
                    echo '<h2>Error</h2><pre>' . $err . '</pre>';
                } else {
                    echo '<h2>Result</h2><pre>';
                    print_r($result);
                    echo '</pre>';
                }
            }

            echo '<h2>Request</h2><pre>' . htmlspecialchars(str_replace("><", ">\n<", $client->request), ENT_QUOTES) . '</pre>';
            echo '<h2>Response</h2><pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
            // echo '<h2>Response</h2><pre>' , var_dump($client->return) , '</pre>';
            echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->getDebug(), ENT_QUOTES) . '</pre>';
        }


        //LOGGING
        $xmlLog = true;
        if ($xmlLog) {

            //log to newrelic and s3
            $logResult = NewRelic::logEvent(
                $logType = $logFolder,
                $script = basename(__FILE__),
                $msg = substr($client->request, strpos($client->request, '<?'), strlen($client->request)),
                $attr = [
                    'dmuid' => $addOptions['uid'],
                    'timestamp' => gmdate('YmdHis'),
                    'flow' => 'REQUEST',
                ]
            );
            if (!$logResult) {
                echo "Failed to create log event for REQUEST or options UID not passed";
            }

            $logResult = NewRelic::logEvent(
                $logType = $logFolder,
                $script = basename(__FILE__),
                $msg = $client->response . $client->responseData,
                $attr = [
                    'dmuid' => $addOptions['uid'],
                    'timestamp' => gmdate('YmdHis'),
                    'flow' => 'RESPONSE',
                ]
            );
            if (!$logResult) {
                echo "Failed to create log event for RESPONSE or options UID not passed";
            }
        }


        $err = $client->getError();
        if ($client->fault) {
            $errorTO->type = FLAG_ERRORTO_ERROR;
            $errorTO->description = '<h2>Fault (Expect - The request contains an invalid SOAP body)</h2><pre>' . $result . $err . '</pre>';
        } else {
            if ($err) {
                $errorTO->type = FLAG_ERRORTO_ERROR;
                $errorTO->description = '<h2>Fault (Expect - The request contains an invalid SOAP body)</h2><pre>' . $result . $err . '</pre>';
            }
            if (!preg_match("/^HTTP.+ 2[0-9][0-9] /msU", $client->response)) {
                $errorTO->type = FLAG_ERRORTO_ERROR;
                $errorTO->description = 'Server issued a non-200 response code.' . var_dump($result) . $err . '</pre>';
            } else {
                if (isset($result["message"]["transaction"]["command"]["documentCommand"]["documentCommandOperand"]["response"]["!responseStatus"]) &&
                    ($result["message"]["transaction"]["command"]["documentCommand"]["documentCommandOperand"]["response"]["!responseStatus"] == "ACCEPTED")) {

                    $errorTO->type = FLAG_ERRORTO_SUCCESS;
                    $errorTO->description = "Successfully Uploaded";
                    $errorTO->object = $result;
                } else {
                    $errorTO->type = FLAG_ERRORTO_ERROR;
                    $errorTO->description = "Rejected by PnP";
                    $errorTO->object = $result;
                }
            }
        }


        return $errorTO;
    }


}






