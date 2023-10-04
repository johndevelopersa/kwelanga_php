<?php

include_once('ROOT.php');
include_once($ROOT . 'PHPINI.php');
include_once($ROOT . $PHPFOLDER . 'DAO/ExceptionThrower.php');
include_once($ROOT . $PHPFOLDER . 'properties/Constants.php');
include_once($ROOT . $PHPFOLDER . 'TO/SequenceTO.php');
include_once($ROOT . $PHPFOLDER . 'DAO/SequenceDAO.php');
include_once($ROOT . $PHPFOLDER . 'TO/ErrorTO.php');

class PostAgedStockDAO
{
    private $dbConn;

    public function __construct($dbConn)
    {
        $this->dbConn = $dbConn;
        $this->errorTO = new ErrorTO();
    }

    public function InsertUpliftRecord(AgedStockTO $AgedStockTO, $userUId)
    {
        $boxes = $found = $display = $storerefused = $damages = $notFound = $extras = 0;

        foreach ($AgedStockTO as $akey => $aRow) {
            if ($akey == 'documentUid') {
                $docUid = mysqli_real_escape_string($this->dbConn->connection, $aRow);
            }
            if ($akey == 'uplNumber') {
                $uplNumber = mysqli_real_escape_string($this->dbConn->connection, $aRow);
            }

            if ($akey == 'boxes') {
                $boxes = "'" . mysqli_real_escape_string($this->dbConn->connection, $aRow) . "'";
            }
            if ($akey == 'comments') {
                $comments = "'" . mysqli_real_escape_string($this->dbConn->connection, $aRow) . "'";
            }
            if ($akey == 'principal') {
                $principal = mysqli_real_escape_string($this->dbConn->connection, $aRow);
            }
            if ($akey == 'warehouseUid') {
                $warehouseUid = mysqli_real_escape_string($this->dbConn->connection, $aRow);
            }
            if ($akey == 'detailArr') {

                for ($x = 0; $x < count($aRow); $x++) {

                    foreach ($aRow[$x] as $dKey => $detRow) {
                        if ($dKey == 'ddUid') {
                            $ddUid = mysqli_real_escape_string($this->dbConn->connection, $detRow);
                            $sql = 'SELECT *
                                           FROM  aged_stock_detail asd
                                           WHERE asd.document_master_uid = ' . $docUid . '
                                           AND   asd.document_detail_uid = ' . $ddUid . ';';

                            $aSR = $this->dbConn->dbGetAll($sql);

                        }
                        if ($dKey == 'found' && mysqli_real_escape_string($this->dbConn->connection, $detRow) <> '') {
                            $found = mysqli_real_escape_string($this->dbConn->connection, $detRow);
                        }
                        if ($dKey == 'display' && mysqli_real_escape_string($this->dbConn->connection, $detRow) <> '') {
                            $display = mysqli_real_escape_string($this->dbConn->connection, $detRow);
                        }
                        if ($dKey == 'storerefused' && mysqli_real_escape_string($this->dbConn->connection, $detRow) <> '') {
                            $storerefused = mysqli_real_escape_string($this->dbConn->connection, $detRow);
                        }
                        if ($dKey == 'damages' && mysqli_real_escape_string($this->dbConn->connection, $detRow) <> '') {
                            $damages = mysqli_real_escape_string($this->dbConn->connection, $detRow);
                        }
                        if ($dKey == 'notFound' && mysqli_real_escape_string($this->dbConn->connection, $detRow) <> '') {
                            $notFound = mysqli_real_escape_string($this->dbConn->connection, $detRow);
                        }
                        if ($dKey == 'extras' && mysqli_real_escape_string($this->dbConn->connection, $detRow) <> '') {
                            $extras = mysqli_real_escape_string($this->dbConn->connection, $detRow);

                            if (count($aSR) == 0) {

                                $sql = 'INSERT into aged_stock_detail (
                                            document_master_uid, document_detail_uid, uplift_number, `found`, display, storerefused, 
                                            damages, notfound, boxes, 
                                            reference1, reference2, reference3, reference4, reference5, reference6,  
                                            captured_by, datetime
                                        ) VALUES (
                                            ' . $docUid . ', 
                                            ' . $ddUid . ",
                                            '" . $uplNumber . "',
                                            " . $found . ',
                                            ' . $display . ',
                                            ' . $storerefused . ',
                                            ' . $damages . ',
                                            ' . $notFound . ',
                                            ' . $boxes . ",
                                            '" . mysqli_real_escape_string($this->dbConn->connection, $AgedStockTO->reference1) . "',
                                            '" . mysqli_real_escape_string($this->dbConn->connection, $AgedStockTO->reference2) . "',
                                            '" . mysqli_real_escape_string($this->dbConn->connection, $AgedStockTO->reference3) . "',                                                       
                                            '" . mysqli_real_escape_string($this->dbConn->connection, $AgedStockTO->reference4) . "',                                                       
                                            '" . mysqli_real_escape_string($this->dbConn->connection, $AgedStockTO->reference5) . "',                                                       
                                            '" . mysqli_real_escape_string($this->dbConn->connection, $AgedStockTO->reference6) . "',                                                       
                                            " . $userUId . ',
                                            NOW()
                                        )';

                                $this->errorTO = $this->dbConn->processPosting($sql, '');

                                if ($this->errorTO->type != FLAG_ERRORTO_SUCCESS) {
                                    $this->errorTO->description = 'Uplift Record Create Failed : ' . $this->errorTO->description;
                                    return $this->errorTO;
                                }

                                $this->dbConn->dbQuery('commit');

                                $PostAgedStockDAO = new PostAgedStockDAO($this->dbConn);
                                $this->errorTO = $PostAgedStockDAO->UpdateUpliftDocDetail($found, $ddUid);

                            } else {

                                $sql = 'UPDATE aged_stock_detail SET  
                                           found         = ' . $found . ',
                                           display       = ' . $display . ',
                                           storerefused  = ' . $storerefused . ',
                                           damages       = ' . $damages . ',
                                           notfound      = ' . $notFound . ',
                                           boxes         = ' . $boxes . '
                                        WHERE  document_master_uid = ' . $docUid . '
                                            AND    document_detail_uid = ' . $ddUid;

                                $this->errorTO = $this->dbConn->processPosting($sql, '');

                                if ($this->errorTO->type != FLAG_ERRORTO_SUCCESS) {
                                    $this->errorTO->description = 'Uplift Record Create Failed : ' . $this->errorTO->description;

                                    return $this->errorTO;
                                }
                                $this->dbConn->dbQuery('commit');

                                $PostAgedStockDAO = new PostAgedStockDAO($this->dbConn);
                                $this->errorTO = $PostAgedStockDAO->UpdateUpliftDocDetail($found, $ddUid);
                            }
                        }

                    }
                }
                return $this->errorTO;
            }
        }
    }

    public function UpdateUpliftDocDetail($found, $ddUid)
    {

        $sql = 'UPDATE `document_detail` SET `document_qty`=  ' . mysqli_real_escape_string($this->dbConn->connection, $found) . ',
  	                                     `delivered_qty`=  ' . mysqli_real_escape_string($this->dbConn->connection, $found) . '
  	        WHERE  `uid`= ' . mysqli_real_escape_string($this->dbConn->connection, $ddUid) . ';';

        $this->errorTO = $this->dbConn->processPosting($sql, '');

        if ($this->errorTO->type != FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->description = 'Record Update Failed : ' . $this->errorTO->description;
            echo '<br>';
            echo $sql;
            echo '<br>';
            return $this->errorTO;
        }
        $this->dbConn->dbQuery('commit');
        return $this->errorTO;

    }

// **************************************************************************************************************************

    public function UpdateUpliftStatus($boxes, $documentUid, $newStatus)
    {

        $sql = 'UPDATE `document_header` SET `document_status_uid`= ' . $newStatus . ', 
  	                                     `invoice_date` = curdate() , 
  	                                     `capture_boxes` = ' . mysqli_real_escape_string($this->dbConn->connection, $boxes) . '
  	        WHERE  `document_master_uid`= ' . mysqli_real_escape_string($this->dbConn->connection, $documentUid) . ';';

        $this->errorTO = $this->dbConn->processPosting($sql, '');

        if ($this->errorTO->type != FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->description = 'Record Update Failed : ' . $this->errorTO->description;
            echo '<br>';
            echo $sql;
            echo '<br>';
            return $this->errorTO;
        }
        $this->dbConn->dbQuery('commit');
        return $this->errorTO;

    }

// **************************************************************************************************************************

    public function UpdateStockQuantity($AgedStockTO)
    {

        $sql = 'select *
            from  stock s
            where s.principal_id          = ' . mysqli_real_escape_string($this->dbConn->connection, $AgedStockTO->principal) . '
            and   s.depot_id              = ' . mysqli_real_escape_string($this->dbConn->connection, $AgedStockTO->warehouseUid) . '
            and   s.principal_product_uid = ' . mysqli_real_escape_string($this->dbConn->connection, $AgedStockTO->prodUid) . ';';

        $mfPUL = $this->dbConn->dbGetAll($sql);

        if (sizeof($mfPUL) > 0) {
            $sql = 'update stock s, principal_product pp set s.arrivals = s.arrivals + ' . mysqli_real_escape_string($this->dbConn->connection, $AgedStockTO->found) . ',
                                                                 s.closing  = s.closing  + ' . mysqli_real_escape_string($this->dbConn->connection, $AgedStockTO->found) . ',
                                                                 s.opening  = if(s.opening is null, 0, s.opening),
                                                                 s.returns_cancel  = if(s.returns_cancel is null, 0, s.returns_cancel),
                                                                 s.returns_nc      = if(s.returns_nc is null, 0, s.returns_nc),
                                                                 s.delivered       = if(s.delivered is null, 0, s.delivered),
                                                                 s.adjustment      = if(s.adjustment is null, 0, s.adjustment)
                        where  pp.uid = s.principal_product_uid 
                        and    s.principal_id =  pp.principal_uid  
                        and    s.depot_id     = ' . mysqli_real_escape_string($this->dbConn->connection, $AgedStockTO->warehouseUid) . ' 
                        and    s.principal_product_uid = ' . mysqli_real_escape_string($this->dbConn->connection, $AgedStockTO->prodUid) . ';';

            $this->errorTO = $this->dbConn->processPosting($sql, '');

            if ($this->errorTO->type != FLAG_ERRORTO_SUCCESS) {
                $this->errorTO->description = 'Record Update Failed : ' . $this->errorTO->description;
                echo '<br>';
                echo $sql;
                echo '<br>';
                return $this->errorTO;
            }
            return $this->errorTO;

        } else {

            $sql = 'INSERT INTO `stock` (`principal_id`, 
                                                   `depot_id`, 
                                                   `principal_product_uid`, 
                                                   `stock_item`,
                                                   `opening`,
                                                   `returns_cancel`,  
                                                   `returns_nc`,
                                                   `delivered`,
                                                   `adjustment`,                                                      
                                                   `arrivals`, 
                                                   `closing`) VALUES (' . mysqli_real_escape_string($this->dbConn->connection, $AgedStockTO->principal) . ', 
                                                                      ' . mysqli_real_escape_string($this->dbConn->connection, $AgedStockTO->warehouseUid) . ', 
                                                                      ' . mysqli_real_escape_string($this->dbConn->connection, $AgedStockTO->prodUid) . ", 
                                                                      '" . mysqli_real_escape_string($this->dbConn->connection, $AgedStockTO->prodCode) . "', 
                                                                       0,0,0,0,0,
                                                                       " . mysqli_real_escape_string($this->dbConn->connection, $AgedStockTO->found) . ', 
                                                                       ' . mysqli_real_escape_string($this->dbConn->connection, $AgedStockTO->found) . ');';

            $this->errorTO = $this->dbConn->processPosting($sql, '');

            if ($this->errorTO->type != FLAG_ERRORTO_SUCCESS) {
                $this->errorTO->description = 'Record Insert Failed : ' . $this->errorTO->description;
                return $this->errorTO;
            }

            return $this->errorTO;

        }
    }

// **************************************************************************************************************************
    public function CheckStockTotals($AgedStockTO)
    {

        $sql = 'update stock a set  a.closing = (a.opening + 
                                 a.arrivals + 
                                 a.returns_cancel + 
                                 a.returns_nc + 
                                 a.delivered + 
                                 a.adjustment),
                    a.available = (a.opening  + 
                                   a.arrivals + 
                                   a.returns_cancel + 
                                   a.returns_nc + 
                                   a.delivered + 
                                   a.adjustment +
                                   a.allocations +
                                   a.in_pick)
                                   
           where a.principal_id = ' . mysqli_real_escape_string($this->dbConn->connection, $AgedStockTO->principal) . '
           and   a.depot_id = ' . mysqli_real_escape_string($this->dbConn->connection, $AgedStockTO->warehouseUid) . ';';

        $this->errorTO = $this->dbConn->processPosting($sql, '');

        if ($this->errorTO->type != FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->description = 'Stock Check failed .. ' . $this->errorTO->description;
            return $this->errorTO;
        }
        $this->dbConn->dbQuery('commit');
        return $this->errorTO;
    }

// **************************************************************************************************************************
    public function InsertToAgeStockReceipt($depotUid, $docUid, $psmUID, $boxes, $value, $ddate, $dtime, $comment)
    {

        $isql = "INSERT INTO `aged_stock_warehouse_receipt` (`depot_uid`,                    
                                                              `document_master_uid`,          
                                                              `principal_master_store_uid`,   
                                                              `boxes`,                        
                                                              `value`,                        
                                                              `date`,                         
                                                              `time`,
                                                              `comment`,
                                                              `processed_date`)               
                  VALUES ('" . mysqli_real_escape_string($this->dbConn->connection, $depotUid) . "',
                          '" . mysqli_real_escape_string($this->dbConn->connection, $docUid) . "',
                          '" . mysqli_real_escape_string($this->dbConn->connection, $psmUID) . "',
                          '" . mysqli_real_escape_string($this->dbConn->connection, $boxes) . "',
                          '" . mysqli_real_escape_string($this->dbConn->connection, $value) . "',
                          substr('" . mysqli_real_escape_string($this->dbConn->connection, $ddate) . "',1,10),        
                          substr('" . mysqli_real_escape_string($this->dbConn->connection, $ddate) . "',12,8),
                          '" . mysqli_real_escape_string($this->dbConn->connection, $comment) . "',
                          now())";

        $this->errorTO = $this->dbConn->processPosting($isql, '');

        if ($this->errorTO->type != FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->description = 'Insert into aged stock failed .. ' . $this->errorTO->description;
            echo '<br>' . $isql . '<br>';
            return $this->errorTO;
        }
        $this->dbConn->dbQuery('commit');
        return $this->errorTO;
    }

// **************************************************************************************************************************

    public function SaveDispatchRecordsToTracking($principalId, $docNo, $boxes, $userID)
    {

        global $ROOT;
        global $PHPFOLDER;

        include_once($ROOT . $PHPFOLDER . 'DAO/PostAgedStockDAO.php');

        $AgedStockDAO = new AgedStockDAO($this->dbConn);
        $dispatchDetails = $AgedStockDAO->getDocumentDetailsToDispatch($principalId, $docNo);

        $lineNo = 1;

        // Get Order sequence No
        $sequenceDAO = new SequenceDAO($this->dbConn);
        $sequenceTO = new SequenceTO();
        $errorTO = new ErrorTO();
        $sequenceTO->sequenceKey = LITERAL_SEQ_ORDER;
        $sequenceTO->principalUId = $principalId;
        $result = $sequenceDAO->getSequence($sequenceTO, $orderSeqVal);

        if ($result->type != FLAG_ERRORTO_SUCCESS) {
            return $result;
        }

        // Get Document sequence No
        $sequenceDAO = new SequenceDAO($this->dbConn);
        $sequenceTO = new SequenceTO();
        $errorTO = new ErrorTO();
        $sequenceTO->sequenceKey = LITERAL_SEQ_DOCUMENT_NUMBER;
        $sequenceTO->principalUId = $principalId;
        $result = $sequenceDAO->getSequence($sequenceTO, $documentSeqVal);

        if ($result->type != FLAG_ERRORTO_SUCCESS) {
            return $result;
        }

        $dmsql = 'INSERT INTO document_master (`depot_uid`, 
                                               `principal_uid`, 
                                               `document_number`,
                                               `document_type_uid`, 
                                               `processed_date`, 
                                               `processed_time`, 
                                               `last_updated`,
                                               `order_sequence_no`, 
                                               `version` ) 
                   VALUES (' . $dispatchDetails[0]['depot_uid'] . ',
                          ' . $dispatchDetails[0]['principal_uid'] . ",                
                          '" . str_pad($documentSeqVal, 8, '0', STR_PAD_LEFT) . "',
                          " . DT_DELIVERYNOTE . "  ,   --   document_type_uid
                          '" . gmdate(GUI_PHP_DATE_FORMAT) . "',   --   processed_date
                          '" . gmdate(GUI_PHP_TIME_FORMAT) . "',   --   processed_time
                          now()                                                  ,   --   last_updated               
                          " . $orderSeqVal . ',   --   order_sequence_no,             
                          1)  ;                                                      --   version ';

        $this->errorTO = $this->dbConn->processPosting($dmsql, '');

        if ($this->errorTO->type != FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description = 'Erroe Inserting into Document Master';
            echo '<br>';
            echo $dmsql;
            echo '<br>';
            return $this->errorTO;
        } else {
            $this->dbConn->dbQuery('commit');
            $dmUId = $this->dbConn->dbGetLastInsertId();
        }

        $dhsql = 'INSERT INTO document_header (document_master_uid, 
                                                          order_date, 
                                                          invoice_date,
                                                          document_status_uid, 
                                                          principal_store_uid, 
                                                          customer_order_number,
                                                          invoice_number, 
                                                          exclusive_total,
                                                          vat_total,
                                                          discount_reference,
                                                          grv_number,
                                                          claim_number,
                                                          cases,
                                                          invoice_total, 
                                                          source_document_number, 
                                                          captured_by)
                                                         
                                                          VALUES (' . $dmUId . ',  
                                                                   CURDATE(),                           
                                                                   CURDATE(),   
                                                                  ' . DST_INVOICED . ' ,
                                                                  ' . $dispatchDetails[0]['psdID'] . " ,
                                                                  'Uplift Number " . mysqli_real_escape_string($this->dbConn->connection, $docNo) . "',
                                                                  ''                                       ,
                                                                  0                                        ,
                                                                  0                                        , 
                                                                  ''                                       ,
                                                                  ''                                       ,
                                                                  ''                                       ,
                                                                  " . mysqli_real_escape_string($this->dbConn->connection, $boxes) . ",
                                                                  0                                        ,
                                                                  ''                                       , 
                                                                  " . mysqli_real_escape_string($this->dbConn->connection, $userID) . ');';

        $this->errorTO = $this->dbConn->processPosting($dhsql, '');

        if ($this->errorTO->type != FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description = 'Error Inserting into Document Header';
            echo '<br>';
            echo $dhsql;
            echo '<br>';
            return $this->errorTO;
        } else {
            $this->dbConn->dbQuery('commit');
        }

        $ddsql = 'INSERT INTO document_detail (document_master_uid, 
                                                          line_no, 
                                                          product_uid, 
                                                          ordered_qty,
                                                          document_qty,
                                                          delivered_qty,
                                                          selling_price, 
                                                          discount_value,
                                                          net_price,
                                                          extended_price,
                                                          vat_amount,
                                                          vat_rate,
                                                          Discount_reference,
                                                          total)
                             VALUES (' . $dmUId . ', 
                                     ' . $lineNo . ',                                     
                                     ' . $dispatchDetails[0]['Dispatch_prodID'] . ',
                                     ' . mysqli_real_escape_string($this->dbConn->connection, $boxes) . ',
                                     ' . mysqli_real_escape_string($this->dbConn->connection, $boxes) . ',
                                     ' . mysqli_real_escape_string($this->dbConn->connection, $boxes) . ",
                                     0,0,0,0,0,0,'',0)";
        $lineNo++;

        echo '<br>';
        echo $ddsql;
        echo '<br>';

        $this->errorTO = $this->dbConn->processPosting($ddsql, '');

        if ($this->errorTO->type != FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description = 'Error Inserting into Document Detail';
            echo '<br>';
            echo $ddsql;
            echo '<br>';
            return $this->errorTO;
        } else {
            $this->dbConn->dbQuery('commit');
        }

        $osql = "INSERT INTO `kwelanga_live`.`orders` (`storechain_uid`, 
                                                                    `principal_uid`, 
                                                                    `order_number`, 
                                                                    `order_sequence_no`, 
                                                                    `delivery_instructions`,
                                                                    `document_type`,  
                                                                    `general_reference_1`, 
                                                                    `general_reference_2`,
                                                                    `processed_depot_uid`) 
                              VALUES ('" . $dispatchDetails[0]['psdID'] . "', 
                                      '" . $dispatchDetails[0]['principal_uid'] . "', 
                                      '', 
                                      '" . $orderSeqVal . "', 
                                      '" . $dispatchDetails[0]['comment'] . "',
                                      " . DT_DELIVERYNOTE . " ,
                                      '1', 
                                      '1',
                                      " . $dispatchDetails[0]['depot_uid'] . ');';

        $this->errorTO = $this->dbConn->processPosting($osql, '');

        if ($this->errorTO->type != FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description = 'Error Inserting into Orders';
            echo '<br>';
            echo $osql;
            echo '<br>';
            return $this->errorTO;
        } else {
            $this->dbConn->dbQuery('commit');
        }
        $this->errorTO->identifier = $dmUId;
        return $this->errorTO;

    }
}