<?php
include_once('ROOT.php');
include_once($ROOT . 'PHPINI.php');
include_once($ROOT . $PHPFOLDER . 'DAO/ExceptionThrower.php');
include_once($ROOT . $PHPFOLDER . "DAO/db_Connection_Class.php");

class BbqUpdateDAO
{
    /* @var $dbConn dbConnect */
    private $dbConn;

    function __construct($dbConn)
    {

        $this->dbConn = $dbConn;
    }

    // ****************************************************************************************************************************************************

    public function getBbqOrders($prinUid)
    {

        $sql = "select dm.uid
              from   document_master dm,
                     document_header dh 
              where   dm.uid = dh.document_master_uid
              and     dm.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $prinUid) . "
              and     dm.bbq_updated = 'N'
              and     dm.document_type_uid = 1
              and     dh.captured_by in ('PNP', 'CHECKERS')";

        $gBBQ = $this->dbConn->dbGetAll($sql);
        return $gBBQ;

    }

    public function getHomeTexOrders()
    {

        $sql = "select dm.uid
              from   document_master dm,
                     document_header dh 
              where   dm.uid = dh.document_master_uid
              and     dm.principal_uid = 328
              and     dm.bbq_updated = 'N'
              and     dm.document_type_uid = 1
              and     dh.data_source in ('BWH')";

        $gBBQ = $this->dbConn->dbGetAll($sql);
        return $gBBQ;

    }

    // ****************************************************************************************************************************************************
    public function updateHomeTexOrder($gBBQ)
    {

        $ordList = [];
        foreach ($gBBQ as $item) {
            $ordList[] = $item['uid'];
        }
        $docList = implode(',', $ordList);
        //echo "<br>";
        //echo $docList;
        //echo "<br>";
        $isql = "update document_master dm,
                      document_detail dd,
                      principal_product pp set dd.old_quantity = dd.ordered_qty,
                                               dd.old_price    = dd.net_price,
                                               dd.ordered_qty = floor(dd.ordered_qty / pp.items_per_case),
                                               dd.document_qty = floor(dd.document_qty / pp.items_per_case),
                                               dd.selling_price = round(dd.selling_price * pp.items_per_case,2),
                                               dd.net_price     = round(dd.net_price * pp.items_per_case,2),
                                               dm.bbq_updated = 'Y'
               where dm.uid = dd.document_master_uid
               and   dd.product_uid = pp.uid
               and   dm.uid in (" . $docList . ");";

        $itresult = $this->dbConn->dbQuery($isql);
        $this->dbConn->dbQuery("commit");

        return;
    }

    // ****************************************************************************************************************************************************
    public function getHastyPNPDCOrders()
    {

        $sql = "select dm.uid
              from   document_master dm,
                     document_header dh,
                     principal p 
              where   dm.uid = dh.document_master_uid
              and     dm.principal_uid = p.uid
              and     dm.principal_uid = 71
              and     dm.bbq_updated = 'N'
              and     dh.document_status_uid in (74,75)
              and     dm.document_type_uid = 1
              and     dm.processed_date > '2019-08-20'
              and     dm.depot_uid <> '202'
              and     dh.captured_by in ('PNP')";

        $gBBQ = $this->dbConn->dbGetAll($sql);
        return $gBBQ;

    }

    // ****************************************************************************************************************************************************
    public function updateHastyPNPDCOrder($gBBQ)
    {

        $ordList = [];
        foreach ($gBBQ as $item) {
            $ordList[] = $item['uid'];
        }
        $docList = implode(',', $ordList);
        //echo "<br>";
        //echo $docList;
        //echo "<br>";
        $isql = "update document_master dm,
                      document_detail dd,
                      principal p, 
                      principal_product pp set dd.old_quantity  = dd.ordered_qty,
                                               dd.old_price     = dd.net_price,
                                               dd.ordered_qty   = floor(dd.ordered_qty / 4),
                                               dd.document_qty  = floor(dd.document_qty / 4),
                                               dd.selling_price = round(dd.selling_price * 4,2),
                                               dd.net_price     = round(dd.net_price * 4,2),
                                               dd.conversion    = 4,
                                               dd.bbq_updated   = 'Y',
                                               dm.bbq_updated   = 'Y'
               where dm.uid = dd.document_master_uid
               and   p.uid  = dm.principal_uid
               and   dm.depot_uid <> '202'
               and   dd.product_uid = pp.uid
               and   dm.uid in (" . $docList . ");";

        $itresult = $this->dbConn->dbQuery($isql);
        $this->dbConn->dbQuery("commit");

        return;
    }

    // ****************************************************************************************************************************************************
    public function clearNotUniquePO()
    {

        $sql = "UPDATE orders_holding oh SET oh.`status` = 'D'
              WHERE oh.principal_uid in (411, 425, 434, 401, 417, 354, 347, 305, 35, 71, 290, 70, 360, 74, 64, 374, 193, 368, 122, 351, 370, 354, 390, 216, 412, 450, 443, 451)
              AND   oh.status_msg LIKE '%unique%'
              AND   oh.`status` NOT IN ('D','S');";

        $itresult = $this->dbConn->dbQuery($sql);
        $this->dbConn->dbQuery("commit");

        return;
    }

    // ****************************************************************************************************************************************************
    public function checkOrderStatus()
    {
        $sql = "UPDATE document_master dm, 
                      document_header dh, 
                      document_detail dd set dh.document_status_uid = " . DST_ACCEPTED . "
               WHERE  dm.uid = dh.document_master_uid
               AND    dm.uid = dd.document_master_uid
               AND    dm.principal_uid = 354
               AND    dm.incoming_file LIKE '%rich%'
               AND    dm.processed_date  = CURDATE()
               AND    dm.document_type_uid = " . DT_SALES_ORDER . "
               AND    dh.document_status_uid = " . DST_UNACCEPTED . " ;";

        $itresult = $this->dbConn->dbQuery($sql);
        $this->dbConn->dbQuery("commit");

        return;
    }

    // ****************************************************************************************************************************************************
    public function checkInvoiceStatus()
    {
        $sql = "UPDATE document_master dm, 
                      document_header dh, 
                      document_detail dd set dh.document_status_uid = " . DST_INVOICED . ",
                                             dd.document_qty = dd.pallets
               WHERE  dm.uid = dh.document_master_uid
               AND    dm.uid = dd.document_master_uid
               AND    dm.principal_uid = 354
               AND    dm.incoming_file LIKE '%RPC%'
               AND    dm.processed_date  = CURDATE()
               AND    dm.document_type_uid = " . DT_ORDINV . "
               AND    dh.document_status_uid = " . DST_UNACCEPTED . ";";

        $itresult = $this->dbConn->dbQuery($sql);
        $this->dbConn->dbQuery("commit");

        return;
    }


    public function cstoresAltChain()
    {
        $sql = "update principal_store_master psm SET psm.alt_principal_chain_uid = 2728
              WHERE psm.principal_uid = 354
              AND   psm.principal_chain_uid NOT IN (2726, 9999)
              AND   psm.alt_principal_chain_uid = 2726
              AND   psm.`status` = 'A';";

        $itresult = $this->dbConn->dbQuery($sql);
        $this->dbConn->dbQuery("commit");

        return;
    }


    public function emaxWarhouseStatus()
    {
        $sql = "UPDATE document_master dm, .document_header dh SET dm.depot_uid = 195, dh.document_status_uid = 75
              WHERE dm.uid = dh.document_master_uid
              AND   dm.principal_uid = 361
              AND   dm.depot_uid = 99
              AND   dh.document_status_uid IN (1);";

        $itresult = $this->dbConn->dbQuery($sql);
        $this->dbConn->dbQuery("commit");

        return;
    }


    public function createCredNoteUidLookup()
    {
        $sql = "SELECT m.principal_uid, m.uid, m.document_number, h.source_document_number
              FROM .document_master m
              INNER JOIN .document_header h ON h.document_master_uid = m.uid
              WHERE m.document_type_uid IN (" . DT_CREDITNOTE . ")
              AND   h.invoice_date > '2021-01-01'
              AND   m.principal_uid = 390
              AND   h.document_status_uid IN (" . DST_PROCESSED . ")
              AND   h.source_document_number IS NOT NULL
              AND   h.source_uid_update = 'N';";

        $itresult = $this->dbConn->dbGetAll($sql);

        if (!empty($itresult)) {

            foreach ($itresult as $chkrow) {

                $chsql = "SELECT *
                        FROM .document_credit_source dcs
                        WHERE dcs.credit_uid = " . $chkrow['uid'] . "
                        AND   dcs.source_document_number = " . $chkrow['source_document_number'] . ";";

                $chresult = $this->dbConn->dbGetAll($chsql);

                if (count($chresult) == 0) {
                    $isql = "INSERT INTO document_credit_source (document_credit_source.invoice_uid,
                                                                   document_credit_source.source_document_number,                                    
                                                                   document_credit_source.credit_uid)

                               (SELECT dm1.uid, dh1.source_document_number, (SELECT dm.uid
                                                                             FROM .document_master dm
                                                                             INNER JOIN .document_header dh ON dm.uid = dh.document_master_uid
                                                                             WHERE dm.principal_uid = " . $chkrow['principal_uid'] . "
                                                                             AND   dm.document_type_uid IN (" . DT_CREDITNOTE . ")
                                                                             AND   dm.document_number IN ('" . $chkrow['document_number'] . "')) 
                                FROM .document_master dm1
                                INNER JOIN .document_header dh1 on dh1.document_master_uid = dm1.uid
                                WHERE dm1.principal_uid = " . $chkrow['principal_uid'] . "
                                AND   dh1.source_document_number = (SELECT dh.source_document_number
                                                                    FROM .document_master dm
                                                                    INNER JOIN .document_header dh ON dm.uid = dh.document_master_uid
                                                                    WHERE dm.principal_uid = " . $chkrow['principal_uid'] . "
                                                                    AND   dm.document_type_uid IN (" . DT_CREDITNOTE . ")
                                                                    AND   dm.document_number IN ('" . $chkrow['document_number'] . "')) 
                                AND dm1.document_type_uid IN (" . DT_ORDINV . ")
                                AND dh1.source_uid_update = 'N');";

                    $itresult = $this->dbConn->dbQuery($isql);

                    $this->dbConn->dbQuery("commit");

                    $usql = "UPDATE document_header h SET h.source_uid_update = 'Y'
                                WHERE h.document_master_uid = " . $chkrow['uid'] . ";";

                    $updresult = $this->dbConn->dbQuery($usql);
                    $this->dbConn->dbQuery("commit");
                }

            }
        }
        return;
    }

    // ****************************************************************************************************************************************************
    public function updateSafariOrder($gBBQ)
    {

        $ordList = [];
        foreach ($gBBQ as $item) {
            $ordList[] = $item['uid'];
        }
        $docList = implode(',', $ordList);

        $isql = "update document_master dm,
                      document_detail dd,
                      principal_product pp set dd.old_quantity  = if('pp.alt_code = 'm,dd.ordered_qty,dd.ordered_qty),
                                               dd.old_price     = if(pp.alt_code = 'm',dd.net_price,dd.net_price),
                                               dd.ordered_qty   = if(pp.alt_code = 'm',dd.ordered_qty * pp.items_per_case,dd.ordered_qty),
                                               dd.document_qty  = if(pp.alt_code = 'm',dd.document_qty * pp.items_per_case,dd.document_qty),
                                               dd.selling_price = if(pp.alt_code = 'm',round(dd.selling_price / pp.items_per_case,2), dd.selling_price),
                                               dd.net_price     = if(pp.alt_code = 'm',round(dd.net_price / pp.items_per_case,2),dd.net_price),
                                               dd.conversion    = if(pp.alt_code = 'm',pp.items_per_case,pp.items_per_case),
                                               dd.bbq_updated = 'Y',
                                               dm.bbq_updated = 'Y'
               where dm.uid = dd.document_master_uid
               and   dd.product_uid = pp.uid
               and   dm.uid in (" . $docList . ");";

        $itresult = $this->dbConn->dbQuery($isql);

        $this->dbConn->dbQuery("commit");
    }


    public function UpdateAODNO()
    {

        $isql = "UPDATE orders_holding oh,                                              
                       document_master dm                                              
                INNER JOIN orders o ON o.order_sequence_no = dm.order_sequence_no ,    
                           document_header dh 
                    SET o.delivery_instructions = oh.document_type,
		                dh.customer_order_number = oh.reference    
                WHERE oh.order_sequence_number = dm.order_sequence_no                      	
                AND   dm.uid = dh.document_master_uid                                  
                AND   oh.document_type_uid = 23
                AND  dm.processed_date > NOW() - INTERVAL 3 DAY ";

        $brresult = $this->dbConn->dbQuery($isql);
        $this->dbConn->dbQuery("commit");

    }

    public function updateDeciTotals()
    {

        //get the list of decimal_allowed principals.
        $sql = "SELECT uid FROM principal p WHERE p.decimal_allowed = 'Y'";
        $principalArr = $this->dbConn->dbGetAll($sql);

        if (!is_array($principalArr) || !count($principalArr)) {
            return;
        }

        foreach ($principalArr as $pArr) {

            $principalId = $pArr['uid'];

            echo "Updating DeciTotals for Principal: {$principalId}<br>";

            $sql = "UPDATE document_master dm
               LEFT JOIN .document_header dh ON dh.document_master_uid = dm.uid
               LEFT JOIN .document_detail dd ON dd.document_master_uid = dm.uid             
               LEFT JOIN .principal_product pp ON pp.uid = dd.product_uid
                     SET dd.decimal_qty = if(dh.document_status_uid in (76,77,78), 
                                          if(pp.allow_decimal='Y' ,round(dd.document_qty/100,2) , dd.document_qty), 
                                          if(pp.allow_decimal='Y' ,round(dd.ordered_qty/100,2) , dd.ordered_qty)), 
                         dd.decimal_excl = if(pp.allow_decimal='Y',round(dd.extended_price/100,2), dd.extended_price)
               WHERE dm.principal_uid = " . (int)$principalId . "
               AND dh.invoice_date >= curdate() - interval 30 day 
               AND dh.decimal_updated = if(dh.document_status_uid IN (74,75,87),'P','Y')
               AND dm.processed_date > curdate() - interval 90 day
               AND   dm.document_type_uid IN (" . DT_ORDINV . ", " . DT_DELIVERYNOTE . " );";

            $brresult = $this->dbConn->dbQuery($sql);
            $this->dbConn->dbQuery("commit");

            $sql = "UPDATE document_master dm
              LEFT JOIN .document_header dh ON dh.document_master_uid = dm.uid
              
                  SET dh.decimal_cases   = (SELECT SUM(dd.decimal_qty)
                                            FROM .document_detail dd
                                            WHERE dd.document_master_uid = dm.uid),
                      dh.exclusive_total = (SELECT round(SUM(dd1.decimal_excl),2)
                                            FROM .document_detail dd1
                                            WHERE dd1.document_master_uid = dm.uid),
                      dh.vat_total       = (SELECT round(SUM(dd2.decimal_excl * dd2.vat_rate/100),2)
                                            FROM .document_detail dd2
                                            WHERE dd2.document_master_uid = dm.uid),
                      dh.invoice_total	  = (SELECT round(SUM(dd3.decimal_excl) + SUM(dd3.decimal_excl * dd3.vat_rate/100),2)
                                            FROM .document_detail dd3
                                            WHERE dd3.document_master_uid = dm.uid),
                      dh.decimal_updated = 'Y'
               WHERE dm.principal_uid = " . (int)$principalId . "
                   AND  dh.invoice_date > curdate() - interval 30 day
                   AND  dm.processed_date > curdate() - interval 90 day
                   AND  dh.decimal_updated = if(dh.document_status_uid IN (74,75,87),'P','Y')
                   AND  dm.document_type_uid IN (" . DT_ORDINV . ", " . DT_DELIVERYNOTE . " );";

            $brresult = $this->dbConn->dbQuery($sql);
            $this->dbConn->dbQuery("commit");
        }
    }


    public function zeroOrderLine()
    {

        $sql = "UPDATE document_master dm
               INNER JOIN .document_header dh ON dh.document_master_uid = dm.uid
               INNER JOIN .document_detail dd ON dd.document_master_uid = dm.uid SET dd.ordered_qty = 0,
                                                                          dd.extended_price = 0,
                                                                          dd.vat_amount = 0,
                                                                          dd.total = 0,
                                                                          dm.bbq_updated = 'Y'
               WHERE dm.principal_uid = 351
               AND   dm.document_type_uid IN (1)
               AND   dh.document_status_uid IN (74,75,87)
               AND   dh.captured_by = 'PNP'
               AND   dd.product_uid = 99845
               AND   dm.depot_uid IN (384, 190)
               AND   dm.bbq_updated <> 'Y';";

        $wwesult = $this->dbConn->dbQuery($sql);
        $this->dbConn->dbQuery("commit");
    }

    // ****************************************************************************************************************************************************
    public function idoSpecialFields()
    {

        $sql = "INSERT IGNORE INTO .special_field_details (special_field_details.field_uid,
                                                          special_field_details.value,
                                                          special_field_details.entity_uid)

               SELECT 253, 'ITDEL', psm.uid
               FROM document_master dm 
               LEFT JOIN .document_header dh ON dh.document_master_uid = dm.uid
               LEFT JOIN .principal_store_master psm ON psm.uid = dh.principal_store_uid
               LEFT JOIN .special_field_details spp ON spp.field_uid = 253 AND psm.uid = spp.entity_uid 
               WHERE dm.principal_uid = 216 
               AND   dh.captured_by = 'Shopify'
               AND   spp.field_uid IS NULL;";

        //echo "<br>";
        //echo $sql;
        //echo "<br>";

        $itresult = $this->dbConn->dbQuery($sql);
        $this->dbConn->dbQuery("commit");

        $sql = "INSERT IGNORE INTO .special_field_details (special_field_details.field_uid,
                                           special_field_details.value,
                                           special_field_details.entity_uid)

               SELECT 257, if(dm.depot_uid= 186,'CW',
                           if(dm.depot_uid= 187,'CWJHB',
                           if(dm.depot_uid= 276,'DPMS',''))) , 
                           psm.uid
               FROM document_master dm 
               LEFT JOIN .document_header dh ON dh.document_master_uid = dm.uid
               LEFT JOIN .principal_store_master psm ON psm.uid = dh.principal_store_uid
               LEFT JOIN .special_field_details spa ON spa.field_uid = 257 AND psm.uid = spa.entity_uid 
               WHERE dm.principal_uid = 216 
               AND   dh.captured_by = 'Shopify'
               AND   spa.field_uid IS NULL;";

        //echo "<br>";
        //echo $sql;
        //echo "<br>";

        $itresult = $this->dbConn->dbQuery($sql);
        $this->dbConn->dbQuery("commit");

        return;
    }
    // ****************************************************************************************************************************************************

}
