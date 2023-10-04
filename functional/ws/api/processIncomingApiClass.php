<?php

include_once('ROOT.php');
include_once($ROOT . 'PHPINI.php');
include_once($ROOT . $PHPFOLDER . "libs/GUICommonUtils.php");
include_once($ROOT . $PHPFOLDER . "DAO/db_Connection_Class.php");
include_once($ROOT . $PHPFOLDER . 'DAO/ApiDAO.php');
include_once($ROOT . $PHPFOLDER . 'DAO/PostApiTransactionDAO.php');
include_once($ROOT . $PHPFOLDER . 'DAO/CreateTransactionDAO.php');
include_once($ROOT . $PHPFOLDER . 'TO/PostingDocumentTO.php');
include_once($ROOT . $PHPFOLDER . 'TO/PostingDocumentDetailTO.php');
include_once($ROOT . $PHPFOLDER . 'libs/smartqueue/SmartQueue.php');
include_once($ROOT . $PHPFOLDER . 'libs/storage/Storage.php');
include_once($ROOT . $PHPFOLDER . 'libs/newrelic.php');

global $ROOT;
global $PHPFOLDER;

class processIncomingApiClass
{

    function __construct()
    {

        global $dbConn, $logFileDAO;
        $this->dbConn = $dbConn;
        $this->errorTO = new ErrorTO;
    }

    public function getProduct($reqData, $pvUser, $principalId, $usrEmail)
    {

        $newApiDAO = new APIDAO($this->dbConn);
        $result = $newApiDAO->validatePrinId($principalId, $pvUser, $reqData);

        if ($result['resultStatus'] == 'E') {
            $returnResult = json_encode($result);
            return $returnResult;
        }

        $newApiDAO = new APIDAO($this->dbConn);
        $result = $newApiDAO->validateEmailAdd(trim($usrEmail), $pvUser, $reqData);

        if ($result['resultStatus'] == 'E') {
            $returnResult = json_encode($result);
            return $returnResult;
        }

        $newApiDAO = new APIDAO($this->dbConn);
        $aresult = $newApiDAO->getRequiredDataProducts($principalId, $usrEmail);

        if ($aresult['resultStatus'] == 'E') {
            $returnResult = json_encode($aresult);
            return $returnResult;
        }

        $returnArr = [];
        foreach ($aresult as $r) {

            $returnArr[] = [
                "principalUid" => $r["principal_uid"],
                "ProdCode" => $r["product_code"],
                "Product" => $r["product_description"],
                "OuterCaseBarCode" => $r["outercasing_gtin"],
                "SkuBarCode" => $r["sku_gtin"]
            ];

        }
        // send JSON back to the client :
        $newApiDAO = new APIDAO($this->dbConn);
        $errorTO = $newApiDAO->addVendorUserlogEntry(trim($uCred[0]['pv_uid']),
            "s",
            trim($JSON['requireddata']),
            '000');

        $returnResult = json_encode(["resultStatus" => "S",
            "resultMessage" => "Successfully retrieved data",
            "data" => $returnArr
        ]);

        return $returnResult;
    }

    public function getAllPriceProducts($reqData, $pvUser, $principalId, $usrEmail)
    {

        $newApiDAO = new APIDAO($this->dbConn);
        $result = $newApiDAO->validatePrinId($principalId, $pvUser, $reqData);

        if ($result['resultStatus'] == 'E') {
            $returnResult = json_encode($result);
            return $returnResult;
        }

        $newApiDAO = new APIDAO($this->dbConn);
        $result = $newApiDAO->validateEmailAdd(trim($usrEmail), $pvUser, $reqData);

        if ($result['resultStatus'] == 'E') {
            $returnResult = json_encode($result);
            return $returnResult;
        }

        $newApiDAO = new APIDAO($this->dbConn);
        $sResult = $newApiDAO->getUserStoreList(trim($usrEmail), trim($principalId), "G", $pvUser, $reqData);

        // Finds No Stores
        if ($sResult['resultStatus'] == 'E') {
            $returnResult = json_encode($sResult);
            return $returnResult;
        }

        $returnPriceArr = [];
        foreach ($sResult as $sRow) {
            // get user product list
            $newApiDAO = new APIDAO($this->dbConn);
            $ppResult = $newApiDAO->getRequiredDataProducts($principalId, $usrEmail);

            if (count($ppResult) != 0) {
                foreach ($ppResult as $pRow) {

                    $newApiDAO = new APIDAO($this->dbConn);
                    $prodArr = $newApiDAO->getAllPriceProducts($principalId, $sRow['psmChain'],
                        $sRow['chainName'],
                        $pRow['product_code'],
                        $pRow['product_description']);

                    $returnPriceArr = array_merge($returnPriceArr, $prodArr);
                }


            } else {
                if ($aresult['resultStatus'] == 'E') {
                    $returnResult = json_encode($aresult);
                    return $returnResult;
                }

            }
        }
        $newApiDAO = new APIDAO($this->dbConn);
        $errorTO = $newApiDAO->addVendorUserlogEntry(trim($uCred[0]['pv_uid']),
            "S",
            trim($JSON['requireddata']),
            '000');

        $returnResult = json_encode(["resultStatus" => "S",
            "resultMessage" => "Successfully retrieved data",
            "data" => $returnPriceArr
        ]);

        return $returnResult;
    }

    public function getUserStore($reqData, $pvUser, $principalId, $usrEmail)
    {

        $newApiDAO = new APIDAO($this->dbConn);
        $result = $newApiDAO->validatePrinId($principalId, $pvUser, $reqData);

        if ($result['resultStatus'] == 'E') {
            $returnResult = json_encode($result);
            return $returnResult;
        }

        $newApiDAO = new APIDAO($this->dbConn);
        $result = $newApiDAO->validateEmailAdd(trim($usrEmail), $pvUser, $reqData);

        if ($result['resultStatus'] == 'E') {
            $returnResult = json_encode($result);
            return $returnResult;
        }

        $newApiDAO = new APIDAO($this->dbConn);
        $aResult = $newApiDAO->getUserStoreList(trim($usrEmail), trim($principalId), "S", $pvUser, $reqData);

        if ($aResult['resultStatus'] == 'E') {
            $returnResult = json_encode($aResult);
            return $returnResult;
        }

        $returnArr = [];

        foreach ($aResult as $r) {

            $returnArr[] = [
                "principalUid" => $r["principal_uid"],
                "storeId" => $r["psmUid"],
                "storeName" => $r["psmStore"],
                "storeGroup" => $r["psmChain"],
                "GroupName" => $r["chainName"],
                "storeDepot" => $r["psmWh"],
                "DepotName" => $r["depotName"]
            ];
        }
        // send JSON back to the client :
        $newApiDAO = new APIDAO($this->dbConn);
        $errorTO = $newApiDAO->addVendorUserlogEntry(trim($pvUser),
            'S',
            trim($reqData),
            '000');
        $returnResult = json_encode(["resultStatus" => "S",
            "ResultCode" => '000',
            "resultMessage" => "Successfully retrieved data",
            "data" => $returnArr
        ]);
        return $returnResult;

    }

    public function getPriceProduct($reqData, $pvUser, $principalId, $usrEmail, $cGroup, $groupName, $prodCode, $prodDesc)
    {

        $newApiDAO = new APIDAO($this->dbConn);
        $prodArr = $newApiDAO->getAllPriceProducts($principalId,
            $cGroup,
            $groupName,
            $prodCode,
            $prodDesc);

        $returnPriceArr = $prodArr;

        $newApiDAO = new APIDAO($this->dbConn);
        $errorTO = $newApiDAO->addVendorUserlogEntry(trim($uCred[0]['pv_uid']),
            "S",
            trim($JSON['requireddata']),
            '000');

        $returnResult = json_encode(["resultStatus" => "S",
            "resultMessage" => "Successfully retrieved data",
            "data" => $returnPriceArr
        ]);

        return $returnResult;

    }

    public function postKosOrder($pvUser, $orderData)
    {

        global $ROOT;
        global $PHPFOLDER;

        $orderArray = json_decode($orderData, true);

        file_put_contents($ROOT . $PHPFOLDER . 'log/mobileOrder' . date("ymd") . '.txt', print_r($orderArray, TRUE), FILE_APPEND);

        // Validate incoming data

        $newApiDAO = new APIDAO($this->dbConn);
        $result = $newApiDAO->validatePrinId($orderArray['principalUid'],
            $pvUser,
            $orderArray['requireddata']);

        if ($result['resultStatus'] == 'E') {
            $returnResult = json_encode($result);
            return $returnResult;
        }
        // Validate Incoming TransactionDAO

        if (isset($orderArray['capturedBy'])) {
            $capturedBy = $orderArray['capturedBy'];
        } else {
            $capturedBy = $orderArray['userEmail'];
        }

        $newApiDAO = new APIDAO($this->dbConn);
        $result = $newApiDAO->validateEmailAdd(trim($capturedBy), $pvUser, $orderArray['requireddata']);

        if ($result['resultStatus'] == 'E') {
            $returnResult = json_encode($result);
            return $returnResult;
        }


        $newApiDAO = new ApiTransactionDAO($this->dbConn);
        $result = $newApiDAO->incomingKosOrderValidation($orderArray['principalUid'],
            $orderArray['OrderReference'],
            $capturedBy,
            $orderArray['storeId']);

        if ($result['resultStatus'] == 'E') {
            $returnResult = json_encode($result);
            return $returnResult;
        }

        $newApiDAO = new ApiTransactionDAO($this->dbConn);
        $result = $newApiDAO->incomingKosOrderDetailValidation($orderArray['principalUid'],
            $orderArray['detailLines']);

        if ($result['resultStatus'] == 'E') {
            $returnResult = json_encode($result);
            return $returnResult;
        }

        // Load order onto TT

        // Get Start Status

        $CreateTransactionDAO = new CreateTransactionDAO($this->dbConn);
        $result = $CreateTransactionDAO->getStartStatusUsingStoreUID($orderArray['storeId']);

        $startStatus = $result[0]['order_start_status'];
        $orderWarehouse = $result[0]['depotUid'];
        $docType = DT_ORDINV;

        // Get Transaction Sequence - Document Number - Order seqnumber

        $CreateTransactionDAO = new CreateTransactionDAO($this->dbConn);
        $result = $CreateTransactionDAO->getdocumentSequences(trim($orderArray['principalUid']),
            LITERAL_SEQ_DOCUMENT_NUMBER,
            DT_ORDINV,
            $orderWarehouse,
            'API');

        $docNumber = $result;

        $CreateTransactionDAO = new CreateTransactionDAO($this->dbConn);
        $result = $CreateTransactionDAO->getdocumentSequences(trim($orderArray['principalUid']),
            LITERAL_SEQ_ORDER,
            '',
            '',
            '');

        $docseq = $result;

        // Loop through Order Array and create TO
        //echo "<pre>";
        //print_r($orderArray);

        foreach ($orderArray as $key => $row) {
            if ($key == 'username') {
                $PostingDocumentTO = new PostingDocumentTO;
                $PostingDocumentTO->documentNumber = $docNumber;
                $PostingDocumentTO->documentTypeUId = $docType;
                $PostingDocumentTO->processedDate = gmdate(GUI_PHP_DATE_FORMAT);
                $PostingDocumentTO->processedTime = gmdate(GUI_PHP_TIME_FORMAT);
                $PostingDocumentTO->depotUId = $orderWarehouse;
                $PostingDocumentTO->orderSequenceNo = $docseq;
                $PostingDocumentTO->version = "1";
                $PostingDocumentTO->documentStatusUId = $startStatus;
            }
            if ($key == 'principalUid') {
                $PostingDocumentTO->principalUId = trim($row);
                $prinUid = trim($row);
            }
            if ($key == 'orderDate') {
                $PostingDocumentTO->orderDate = trim($row);
                $PostingDocumentTO->invoiceDate = trim($row);
            }
            if ($key == 'requiredDate') {
                $PostingDocumentTO->deliveryDate = trim($row);
                $PostingDocumentTO->requestedDeliveryDate = trim($row);
                $PostingDocumentTO->deliveryDueDate = trim($row);
            }
            if ($key == 'userEmail') {
                $PostingDocumentTO->additionalDetails = trim($row);
            }
            if ($key == 'capturedBy') {
                $PostingDocumentTO->capturedBy = substr(trim($row), 0, 20);
            }
            if ($key == 'captureByLocation') {
                $PostingDocumentTO->rwrFile = trim($row);
            }
            if ($key == 'OrderReference') {
                $PostingDocumentTO->apiReference = trim($row);
            }
            if ($key == 'storeId') {
                $PostingDocumentTO->principalStoreUId = trim($row);
            }

            if ($key == 'purchaseOrderNumber') {
                $PostingDocumentTO->customerOrderNumber = trim($row);
            }
            if ($key == 'captureDateTime') {
                $PostingDocumentTO->processed_date = trim(substr($row, 0, 10));
                $PostingDocumentTO->processed_time = trim(substr($row, 11, 8));
            }
            if ($key == 'requiredDate') {
                if (trim($row) == '') {
                    $PostingDocumentTO->deliveryDate = '0000-00-00';
                } else {
                    $PostingDocumentTO->deliveryDate = trim($row);
                }
            }
            if ($key == 'deliveryInstructions') {
                $PostingDocumentTO->debrief_comment = trim($row);
            }
            if ($key == 'type') {
                $PostingDocumentTO->documentTypeUId = DT_ORDINV;
            }
            if ($key == 'photoUrl') {
                $PostingDocumentTO->incomingFile = trim($row);
            }
            if ($key == 'signitureUrl') {
                $PostingDocumentTO->confirmationFile = trim($row);
            }
            if ($key == 'detailLines') {
                $lineNo = 1;

                foreach ($row as $detRow) {
                    foreach ($detRow as $dkey => $dRow) {
                        if ($dkey == 'prodCode') {
                            // get product ID
                            $newApiDAO = new ApiTransactionDAO($this->dbConn);
                            $prdUid = $newApiDAO->getProductUid($prinUid,
                                trim($dRow));

                            $PostingDocumentDetailTO = new PostingDocumentDetailTO;
                            $PostingDocumentDetailTO->lineNo = $lineNo;
                            $PostingDocumentDetailTO->productUId = $prdUid;
                        }
                        if ($dkey == 'orderQuantity') {
                            if (trim($dRow) == '') {
                                $qRow = '0';
                            } else {
                                $qRow = $dRow;
                            }

                            $PostingDocumentDetailTO->orderedQty = trim($qRow);
                            $PostingDocumentDetailTO->documentQty = 0;
                            $PostingDocumentDetailTO->deliveredQty = 0;

                        }
                        if ($dkey == 'sellingPrice') {
                            $PostingDocumentDetailTO->sellingPrice = trim($dRow);
                            $PostingDocumentDetailTO->discountValue = 0;
                            $PostingDocumentDetailTO->netPrice = trim($dRow);
                            $PostingDocumentDetailTO->extendedPrice = round(trim($dRow) * $qRow, 2);
                            $PostingDocumentDetailTO->vatAmount = round(trim($dRow) * $qRow * 0.15, 2);
                            $PostingDocumentDetailTO->vatRate = '15.00';
                            $PostingDocumentDetailTO->total = round(trim($dRow) * $qRow * 1.15, 2);

                            $PostingDocumentTO->detailArr[] = $PostingDocumentDetailTO;
                        }
                        $lineNo++;
                    }
                }
            }
        }

        $CreateTransactionDAO = new CreateTransactionDAO($this->dbConn);
        $result = $CreateTransactionDAO->createTransaction($PostingDocumentTO);

        if ($result == FLAG_ERRORTO_ERROR) {
            $returnResult = json_encode(["resultStatus" => "E",
                "resultMessage" => "Failed to Create Transaction",
                "data" => ''
            ]);
            return $returnResult;
        }

        $newApiDAO = new APIDAO($this->dbConn);
        $errorTO = $newApiDAO->addVendorUserlogEntry(trim($uCred[0]['pv_uid']),
            "S",
            trim($JSON['requireddata']),
            '000');


        $returnResult = json_encode(["resultStatus" => "S",
            "resultMessage" => "Successfully Loaded Order",
            "data" => trim($JSON['OrderReference'])
        ]);

        return $returnResult;
    }

    public function getInvoiceImports($reqData, $pvUser, $principalId, $usrEmail)
    {

        $newApiDAO = new APIDAO($this->dbConn);
        $result = $newApiDAO->validatePrinId($principalId, $pvUser, $reqData);

        if ($result['resultStatus'] == 'E') {
            $returnResult = json_encode($result);
            return $returnResult;
        }

        if ($reqData == 'getInvoiceImports') {
            $extractType = 'INVOICE';
        } elseif ($reqData == 'getCreditNoteImports') {
            $extractType = 'CREDIT';
        }

        // Get Qued transactions
        $newApiDAO = new APIDAO($this->dbConn);
        $docArr = $newApiDAO->getImportDocuments($principalId, $extractType);

        if ($extractType == 'INVOICE') {
            $docPreFix = $docArr[0]['preFix1'];
            $docPreFix2 = $docArr[0]['preFix3'];
            $jNumTag = "invoiceNumber";
            $jDateTag = "invoiceDate";
            $jOrdDateTag = "orderDate";
            $credReason  = "";
            $jClaimNoTag = "";
            $credReasonId = "";

        } elseif ($extractType == 'CREDIT') {
            $docPreFix = $docArr[0]['preFix2'];
            $docPreFix2 = $docArr[0]['preFix4'];
            $credReason = $docArr[0]['credReason'];
            $credReasonId = $docArr[0]['credReasonId'];
            $jNumTag = "creditNoteNumber";
            $jDateTag = "creditNoteDate";
            $jClaimNoTag = 'claimNumber';
            $jOrdDateTag = "creditNoteCreatedDate";
        }
        $storeOrd = '';

        // print_r($docArr);

        if (count($docArr) > 0) {
            foreach ($docArr as $row) {
                if ($storeOrd <> $row['dmUid']) {
                    if ($storeOrd <> '') {
                        $returnArr[] = [
                            "Type" => $extractType,
                            "customerNumber" => $csNum,
                            "CustomerName" => $csName,
                            "CustomerAdd1" => $csAdd1,
                            "CustomerAdd2" => $csAdd2,
                            "CustomerAdd3" => $csAdd3,
                            "purchaseOrderNumber" => $csPoNo,
                             $jOrdDateTag => $oDate,
                             $jDateTag => $iDate,
                             $jNumTag => $iNum,
                            "orderNumber" => $sNum,
                            "warehouseCode" => $whC,
                            "location" => $whL,
                            "sourceDocumentNumber" => $sDocNo,
                            "sourceInvoiceNumber"  => $srcNum,
                            "sourcePurchaseOrderNumber"  => $csSrcPoNo,
                             $jClaimNoTag => $claimNumber,
                            "creditNoteReason"     => $credReason,
                            "creditNoteReasonId"   => $credReasonId,
                            "detailLines" => $detailArr
                        ];
                        $detailArr = array();
                    }
                    $csNum = $row['customerNumber'];
                    $csName = $row['CustomerName'];
                    $csAdd1 = $row['CustomerAdd1'];
                    $csAdd2 = $row['CustomerAdd2'];
                    $csAdd3 = $row['CustomerAdd3'];
                    $csPoNo = $row['purchaseOrderNumber'];
                    
                    $oDate = $row['orderDate'];
                    $iDate = $row['invoiceDate'];
                    if ($extractType == 'INVOICE') {
                        $iNum = trim($docPreFix) . ltrim($row['invoiceNumber'], '0');
                        $sNum = trim($docPreFix2) . ltrim($row['salesOrderNumber'], '0');
                        $srcNum    = '';
                        $csSrcPoNo = '';
                        $claimNumber = '';
                    } else {
                        $iNum      = trim($docPreFix) . ltrim($row['alternateDocumentNumber'], '0');
                        $sNum      = '';
                        $srcNum    = trim($docPreFix) . ltrim($row['sourceInvoiceNumber'], '0');;
                        $csSrcPoNo = $row['sourcePurchaseOrderNumber'];
                        $claimNumber = $row['claimNumber'];
                    }
                    $whC = $row['warehouseCode'];
                    $whL = $row['whLocation'];
                    $sDocNo = $row['sourceDocumentNumber'];
                    $storeOrd = $row['dmUid'];
                }
                $detailArr[] = [
                    "productCode" => $row['productCode'],
                    "outerCaseBarcode" => $row['outerCaseBarcode'],
                    "productDescription" => $row['productDescription'],
                    "orderedQuantity" => $row['orderedQuantity'],
                    "invoicedQuantity" => $row['invoicedQuantity'],
                    "sellingPrice" => $row['sellingPrice'],
                    "Discount" => round($row['Discount'], 2),
                    "extendedPrice" => round($row['extendedPrice'], 2),
                    "vatAmount" => round($row['vatAmount'], 2),
                    "invoiceTotal" => round($row['invoiceTotal'], 2)
                ];

            }

            $returnArr[] = [
                "customerNumber" => $csNum,
                "CustomerName" => $csName,
                "CustomerAdd1" => $csAdd1,
                "CustomerAdd2" => $csAdd2,
                "CustomerAdd3" => $csAdd3,
                "purchaseOrderNumber" => $csPoNo,
                $jOrdDateTag => $oDate,
                $jDateTag => $iDate,
                $jNumTag => $iNum,
                "orderNumber" => $sNum,
                "warehouseCode" => $whC,
                "location" => $whL,
                "sourceDocumentNumber" => $sDocNo,
                "sourceInvoiceNumber"  => $srcNum,
                "sourcePurchaseOrderNumber"  => $csSrcPoNo,
                $jClaimNoTag => $claimNumber,
                "creditNoteReason"     => $credReason,
                "creditNoteReasonId"   => $credReasonId,
                "detailLines" => $detailArr
            ];
        } else {
            if ($extractType == 'CREDIT') {
                $returnResult = json_encode(["resultStatus" => "S",
                    "resultMessage" => "No Credit Notes to Return",
                    "data" => ''
                ]);
            } else {
                $returnResult = json_encode(["resultStatus" => "S",
                    "resultMessage" => "No Invoices to Return",
                    "data" => ''
                ]);
            }
            return $returnResult;
        }
        // send JSON back to the client :
        $newApiDAO = new APIDAO($this->dbConn);
        $errorTO = $newApiDAO->addVendorUserlogEntry(trim($pvUser),
            'S',
            trim($reqData),
            '000');
        $returnResult = json_encode(["resultStatus" => "S",
            "ResultCode" => '000',
            "resultMessage" => "Successfully retrieved data",
            "data" => $returnArr
        ]);

        return $returnResult;
    }


    public function getStockLevels($reqData, $pvUser, $principalId, $usrEmail, $warehouseId = '')
    {

        $newApiDAO = new APIDAO($this->dbConn);
        $result = $newApiDAO->validatePrinId($principalId, $pvUser, $reqData);

        if ($result['resultStatus'] == 'E') {
            $returnResult = json_encode($result);
            return $returnResult;
        }

        $newApiDAO = new APIDAO($this->dbConn);
        $result = $newApiDAO->validateEmailAdd(trim($usrEmail), $pvUser, $reqData);

        if ($result['resultStatus'] == 'E') {
            $returnResult = json_encode($result);
            return $returnResult;
        }

        if (trim($warehouseId) == '') {
            $newApiDAO = new APIDAO($this->dbConn);
            $whList = $newApiDAO->getUserStockWareHouses($principalId, trim($usrEmail));

            if (count($whList) > 0) {
                $whList = $str = implode(',', array_map('implode', $whList));
            } else {
                $returnResult = json_encode(["resultStatus" => "E",
                    "ResultCode" => '826',
                    "resultMessage" => "No Warehouses found for User ",
                    "data" => ''
                ]);
                return $returnResult;
            }
        } else {
            $whList = $warehouseId;
        }

        $newApiDAO = new APIDAO($this->dbConn);
        $stkLst = $newApiDAO->getWareHouseStockLevels($principalId, $whList);

        if (count($stkLst) > 0) {

            $returnArr = [];

            $header = '';
            foreach ($stkLst as $r) {

                if ($header <> $r["warehouseUid"]) {
                    if ($header <> '') {

                        $returnArr[] = [
                            "principalUid" => $prinId,
                            "principal" => $prinName,
                            "warehouseUid" => $warehouseUid,
                            "warehouse" => $warehouse,
                            "DateTime" => $r["DateTime"],
                            "productLines" => $prodArr
                        ];
                        $prodArr = array();

                    }

                    $prinId = $r["principalUid"];
                    $prinName = $r["principal"];
                    $warehouseUid = $r["warehouseUid"];
                    $warehouse = $r["Warehouse"];
                }

                $prodArr[] = [
                    "ProdCode" => $r["ProdCode"],
                    "Product" => $r["Product"],
                    "ClosingLevel" => $r["ClosingLevel"],
                    "AvailableLevel" => $r["AvailableLevel"],
                ];

                $header = $r["warehouseUid"];
            }

            $returnArr[] = [
                "principalUid" => $prinId,
                "principal" => $prinName,
                "warehouseUid" => $warehouseUid,
                "warehouse" => $warehouse,
                "DateTime" => $r["DateTime"],
                "productLines" => $prodArr
            ];

            // send JSON back to the client :
            $newApiDAO = new APIDAO($this->dbConn);
            $errorTO = $newApiDAO->addVendorUserlogEntry(trim($pvUser),
                'S',
                trim($reqData),
                '000');
            $returnResult = json_encode(["resultStatus" => "S",
                "ResultCode" => '000',
                "resultMessage" => "Successfully retrieved data",
                "data" => $returnArr
            ]);
            return $returnResult;

        } else {
            $newApiDAO = new APIDAO($this->dbConn);
            $errorTO = $newApiDAO->addVendorUserlogEntry(trim($pvUser),
                "E",
                trim($reqData),
                '827 - No Stock Records found');
            $returnResult = [
                "resultStatus" => "E",
                "ResultCode" => '827',
                "resultMessage" => "No Stock Records found"
            ];

            return $returnResult;
        }
    }


    public function postStockLevels($orderData)
    {

        global $ROOT;
        global $PHPFOLDER;

        $JSON = json_decode($orderData, true);

        file_put_contents($ROOT . $PHPFOLDER . 'log/stockLevel' . date("ymd") . '.txt', print_r($JSON, TRUE), FILE_APPEND);

        $newApiDAO = new APIDAO($this->dbConn);
        $result = $newApiDAO->validatePrinId($JSON['principalUid'], $JSON['username'], $JSON['requireddata']);

        if ($result['resultStatus'] == 'E') {
            $returnResult = json_encode($result);
            return $returnResult;
        }

        $newApiDAO = new APIDAO($this->dbConn);
        $result = $newApiDAO->validateEmailAdd(trim($JSON['userEmail']), $JSON['username'], $JSON['requireddata']);

        if ($result['resultStatus'] == 'E') {
            $returnResult = json_encode($result);
            return $returnResult;
        }

        // Add Update stock table Here


        $newApiDAO = new APIDAO($this->dbConn);
        $errorTO = $newApiDAO->addVendorUserlogEntry(trim($uCred[0]['pv_uid']),
            "S",
            trim($JSON['requireddata']),
            '000');

        $returnResult = json_encode(["resultStatus" => "S",
            "resultMessage" => "Successfully Loaded Stock Levels",
            "data" => ''
        ]);

        return $returnResult;

    }

    public function postNewOrder($orderData)
    {

        global $ROOT;
        global $PHPFOLDER;

        $JSON = json_decode($orderData, true);

        print_r($JSON);

        file_put_contents($ROOT . $PHPFOLDER . 'log/mobileOrder' . date("ymd") . '.txt', print_r($JSON, TRUE), FILE_APPEND);

        $newApiDAO = new APIDAO($this->dbConn);
        $result = $newApiDAO->validatePrinId($JSON['principalUid'], $JSON['username'], $JSON['requireddata']);

        if ($result['resultStatus'] == 'E') {
            $returnResult = json_encode($result);
            return $returnResult;
        }

        $newApiDAO = new APIDAO($this->dbConn);
        $result = $newApiDAO->validateEmailAdd(trim($JSON['userEmail']), $JSON['username'], $JSON['requireddata']);

        if ($result['resultStatus'] == 'E') {
            $returnResult = json_encode($result);
            return $returnResult;
        }

        // Add Update stock table Here


        $newApiDAO = new APIDAO($this->dbConn);
        $errorTO = $newApiDAO->addVendorUserlogEntry(trim($uCred[0]['pv_uid']),
            "S",
            trim($JSON['requireddata']),
            '000');

        $returnResult = json_encode(["resultStatus" => "S",
            "resultMessage" => "Successfully Loaded Order",
            "data" => $JSON['OrderReference']
        ]);

        return $returnResult;
    }

    public function postDocumentConfirm($orderData)
    {

        global $ROOT;
        global $PHPFOLDER;

        $JSON = json_decode($orderData, true);

        file_put_contents($ROOT . $PHPFOLDER . 'log/documentConfirmations' . date("ymd") . '.txt', print_r($JSON, TRUE), FILE_APPEND);

        $newApiDAO = new APIDAO($this->dbConn);
        $result = $newApiDAO->validatePrinId($JSON['principalUid'], $JSON['username'], $JSON['requireddata']);

        if ($result['resultStatus'] == 'E') {
            $returnResult = json_encode($result);
            return $returnResult;
        }

        $newApiDAO = new APIDAO($this->dbConn);
        $result = $newApiDAO->validateEmailAdd(trim($JSON['userEmail']), $JSON['username'], $JSON['requireddata']);

        if ($result['resultStatus'] == 'E') {
            $returnResult = json_encode($result);
            return $returnResult;
        }
        foreach ($JSON as $key => $row) {
            if ($key == 'documentList') {
                foreach ($row as $line) {

                    $newApiDAO = new APIDAO($this->dbConn);
                    $errorTO = $newApiDAO->updateConfirmationStatus($JSON['principalUid'],
                        test_input($line['type']),
                        test_input($line['Status']),
                        test_input($line['documentNumber']));

                    if ($errorTO->type != FLAG_ERRORTO_SUCCESS) {
                        $newApiDAO = new APIDAO($this->dbConn);
                        $errorTO = $newApiDAO->addVendorUserlogEntry(trim($pvUser),
                            "E",
                            trim($reqData),
                            '999 - Query Failure');

                        $returnResult = ["resultStatus" => "E",
                            "ResultCode" => '999',
                            "resultMessage" => "Query Failure  - Cannot Continue"];

                        break;
                    } else {
                        $returnResult = json_encode(["resultStatus" => "S",
                            "resultMessage" => "Documents Successfully Confirmed ",
                            "data" => ''
                        ]);
                    }
                }
            }
        }

        return $returnResult;
    }

    public function postNewPrices($body)
    {


        //decode
        $priceData = json_decode($body, true);
        $md5Sum = md5($body);

        NewRelic::logEvent("api-log", basename(__FILE__),
            $message = "incoming request",
            $attr = [
                'requestMethod' => 'postNewPrices',
                'requestMD5' => $md5Sum,
                'requestSize' => strlen($body),
                'requestBody' => (strlen($body) > 1000 ? substr($body, 0, 1000) . '...' : $body),
            ]
        );

        //authenticate
        $apiDAO = new APIDAO($this->dbConn);
        $result = $apiDAO->validatePrinId($priceData['principalUid'], $priceData['username'], $priceData['requireddata']);
        if ($result['resultStatus'] == 'E') {
            return json_encode($result);
        }

        $result = $apiDAO->validateEmailAdd(trim($priceData['userEmail']), $priceData['username'], $priceData['requireddata']);
        if ($result['resultStatus'] == 'E') {
            return json_encode($result);
        }

        //request sequence number.
        $createDAO = new CreateTransactionDAO($this->dbConn);
        $result  = $createDAO->getdocumentSequences((int)$priceData['principalUid'], LITERAL_SEQ_API_FILE_NUMBER, '', '', '');
        $fileSeq = str_pad($result, 6, "0", STR_PAD_LEFT);

        //upload to S3
        $fileName = 'archives/api/bulk_async_prices/' . $priceData['principalUid'] . '/' . date('Y') . '/' . date('m') . '/' . date('d') . '/request_' . date("ymdHis") . "_{$fileSeq}_{$md5Sum}.json";
        new Storage(S3_ACCESS_ID, S3_SECRET_KEY, false, S3_ENDPOINT, S3_REGION);
        $s3Result = Storage::putObject(S3_BUCKET_NAME, $fileName, json_encode($priceData, JSON_PRETTY_PRINT));
        if (!$s3Result) {
            return json_encode(['resultStatus' => 'E', 'resultMessage' => 'Storage Error: ' . $s3Result]);
        }

        $msg = (new SmartEventTO("API_ASYNC_PRICE_REQUEST"))
            ->setTypeUid((int)$fileSeq)
            ->setMetaArr([
                'md5sum' => $md5Sum,
                'principalUid' => $priceData['principalUid'],
                'uri' =>  "s3://" . S3_BUCKET_NAME . '/' . $fileName,
            ]);

        $result = SmartQueue::Publish("API-RequestAsyncPrices.fifo", $msg);
        if ($result->isError()) {
            http_response_code(500);
            return json_encode(['resultStatus' => 'E', 'resultMessage' => 'Queuing failure: ' . $result->getDescription()]);
        }

        NewRelic::logEvent(
            $logType = "api-log",
            $script = basename(__FILE__),
            $message = "async request successfully queued",
            $attr = [
                'RequestMethod' => 'postNewPrices',
                'sqsMessageSeq' => $result->identifier,
                'sqsMessageID' => $result->identifier2, //SQS Message ID
                's3Uri' => $fileName,
            ]
        );

        return json_encode([
            "resultStatus" => "S",
            "resultMessage" => "Prices Successfully uploaded for processing",
            'messageSequence' => $fileSeq,
            "reference" => $result->identifier2,    //SQS Message ID
            "data" => ''
        ]);
    }

    public function getPrincipalReps($reqData, $pvUser, $principalId, $usrEmail)
    {
        $newApiDAO = new APIDAO($this->dbConn);
        $result = $newApiDAO->validatePrinId($principalId, $pvUser, $reqData);

        if ($result['resultStatus'] == 'E') {
            $returnResult = json_encode($result);
            return $returnResult;
        }


        $newApiDAO = new APIDAO($this->dbConn);
        $result = $newApiDAO->validateEmailAdd(trim($usrEmail), $pvUser, $reqData);

        if ($result['resultStatus'] == 'E') {
            $returnResult = json_encode($result);
            return $returnResult;
        }

        // Check if user is Admin User

        $newApiDAO = new APIDAO($this->dbConn);
        $userRole = $newApiDAO->checkUserRole($principalId, trim($usrEmail), ROLE_API_ADMIN);

        if (count($userRole) == 0) {

            $returnResult = json_encode(["resultStatus" => "E",
                "ResultCode" => '703',
                "resultMessage" => "User Does not have API Admin Role",
                "data" => $returnArr
            ]);
            return $returnResult;
        }

        $newApiDAO = new APIDAO($this->dbConn);
        $aResult = $newApiDAO->getPrincipalRepList($principalId, $usrEmail);

        if ($aResult['resultStatus'] == 'E') {
            $returnResult = json_encode($aResult);
            return $returnResult;
        }

        $returnArr = [];

        foreach ($aResult as $r) {

            $returnArr[] = [
                "repId" => $r["repId"],
                "principal" => $r["Principal"],
                "firstName" => $r["firstName"],
                "surname" => $r["surname"],
                "emailAddress" => $r["emailAddress"]
            ];
        }
        // send JSON back to the client :
        $newApiDAO = new APIDAO($this->dbConn);
        $errorTO = $newApiDAO->addVendorUserlogEntry(trim($pvUser),
            'S',
            trim($reqData),
            '000');

        $returnResult = json_encode(["resultStatus" => "S",
            "ResultCode" => '000',
            "resultMessage" => "Successfully retrieved data",
            "data" => $returnArr
        ]);

        return $returnResult;

    }

    public function updateCustomerfile($orderData)
    {

        global $ROOT;
        global $PHPFOLDER;

        $custData = json_decode($orderData, true);
        
        //request sequence number.
        $createDAO = new CreateTransactionDAO($this->dbConn);
        $result  = $createDAO->getdocumentSequences((int)$custData['principalUid'], LITERAL_SEQ_API_FILE_NUMBER, '', '', '');
        $fileSeq = str_pad($result, 6, "0", STR_PAD_LEFT);
 
        file_put_contents($ROOT . 'ftp/primaCustomers/customerUpdate' . date("ymdHis") . $fileSeq . '.txt', $orderData, FILE_APPEND);

        $newApiDAO = new APIDAO($this->dbConn);
        $result = $newApiDAO->validatePrinId($custData['principalUid'], $custData['username'], $custData['requireddata']);

        if ($result['resultStatus'] == 'E') {
            $returnResult = json_encode($result);
            return $returnResult;
        }

        $newApiDAO = new APIDAO($this->dbConn);
        $result = $newApiDAO->validateEmailAdd(trim($custData['userEmail']), $custData['username'], $custData['requireddata']);

        if ($result['resultStatus'] == 'E') {
            $returnResult = json_encode($result);
            return $returnResult;
        }

        // Add Update stock table Here


        $newApiDAO = new APIDAO($this->dbConn);
        $errorTO = $newApiDAO->addVendorUserlogEntry(trim($uCred[0]['pv_uid']),
            "S",
            trim($custData['requireddata']),
            '000');

        $returnResult = json_encode(["resultStatus" => "S",
            "resultMessage" => "Successfully Loaded Customer Details",
            "data" => ''
        ]);

        return $returnResult;

    }

    public function getCompleteStoreList($reqData, $pvUser, $principalId, $usrEmail)
    {

        $newApiDAO = new APIDAO($this->dbConn);
        $result = $newApiDAO->validatePrinId($principalId, $pvUser, $reqData);

        if ($result['resultStatus'] == 'E') {
            $returnResult = json_encode($result);
            return $returnResult;
        }

        $newApiDAO = new APIDAO($this->dbConn);
        $result = $newApiDAO->validateEmailAdd(trim($usrEmail), $pvUser, $reqData);

        if ($result['resultStatus'] == 'E') {
            $returnResult = json_encode($result);
            return $returnResult;
        }

        $newApiDAO = new APIDAO($this->dbConn);
        $aResult = $newApiDAO->getPrincipalStoreList(trim($principalId));

        if ($aResult['resultStatus'] == 'E') {
            $returnResult = json_encode($aResult);
            return $returnResult;
        }

        $returnArr = [];

        foreach ($aResult as $r) {

            $returnArr[] = [
                "principalUid" => $r["principal_uid"],
                "storeId" => $r["storeId"],
                "customerAccount" => $r["customerAccount"],
                "DeliverName" => $r["DeliverName"],
                "DeliverAddress1" => $r["DeliverAddress1"],
                "DeliverAddress2" => $r["DeliverAddress2"],
                "DeliverAddress3" => $r["DeliverAddress3"],
                "InvoiceName" => $r["InvoiceName"],
                "InvoiceAddress1" => $r["InvoiceAddress1"],
                "InvoiceAddress2" => $r["InvoiceAddress2"],
                "InvoiceAddress3" => $r["InvoiceAddress3"],
                "vatNumber" => $r["vatNumber"],
                "branch" => $r["branch"],
                "GLN" => $r["GLN"],
                "defaultWarehouse" => $r["defaultWarehouse"],
                "priceList1" => $r["priceList1"],
                "creditLimit" => $r["creditLimit"],
                "onHold" => $r["onHold"],
                "customerBalance" => $r["customerBalance"],
                "chainName" => $r["chainName"],
                "DepotName" => $r["depotName"]
            ];
        }
        // send JSON back to the client :
        $newApiDAO = new APIDAO($this->dbConn);
        $errorTO = $newApiDAO->addVendorUserlogEntry(trim($pvUser),
            'S',
            trim($reqData),
            '000');
        $returnResult = json_encode(["resultStatus" => "S",
            "ResultCode" => '000',
            "resultMessage" => "Successfully retrieved data",
            "data" => $returnArr
        ]);
        return $returnResult;

    }
}