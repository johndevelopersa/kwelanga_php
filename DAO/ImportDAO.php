<?php

include_once('ROOT.php');
include_once($ROOT . 'PHPINI.php');
include_once($ROOT . $PHPFOLDER . 'properties/Constants.php');
include_once($ROOT . $PHPFOLDER . 'libs/CommonUtils.php');
include_once($ROOT . $PHPFOLDER . 'TO/ErrorTO.php');
include_once($ROOT . $PHPFOLDER . 'DAO/ExceptionThrower.php');

class ImportDAO_getOrdersHoldingForProcessingTO
{
    public $headerArr = array();
    public $detailArr = array();
    public $storeArr = array();
    public $specialFieldArr = array();
    public $onlineFileProcessingMapping = array();
}

class ImportDAO
{
    private $dbConn;
    public static $FILE_SORT_BY_PROCESSED_DATE_DESC = "FILE_SORT_BY_PROCESSED_DATE_DESC";
    public static $PDP_EXP_MAP_INDEX_1 = "PRINCIPAL,PRINCIPALCODE KEY"; // principalCode is the export mapping for the depot for that principal
    public static $PDP_EXP_MAP_INDEX_2 = "PRINCIPAL,DEPOTUID KEY";
    public static $PDP_EXP_MAP_INDEX_3 = "PRINCIPALCODE,DEPOTUID KEY";

    function __construct($dbConn)
    {
        $this->dbConn = $dbConn;
    }

    public function getEmailLogByStatus($status, $limit = 50): array
    {

        $sql = "SELECT 
                    l.uid, l.status, l.subject, l.body, l.from_email, l.to_email, 
                    ela.uid as attach_uid, ela.filename, 
                    ela.uri as attach_uri, l.uri as email_uri
                FROM (
                    SELECT * 
                    FROM email_log e 
                    WHERE e.status = '" . mysqli_real_escape_string($this->dbConn->connection, $status) . "' 
                    LIMIT " . (int)$limit . "
                    ) l
                LEFT JOIN email_log_attachments ela on l.email_uuid = ela.email_uuid";

        $this->dbConn->dbQuery($sql);

        $arr = [];
        if ($this->dbConn->dbQueryResultRows > 0) {
            while ($row = mysqli_fetch_array($this->dbConn->dbQueryResult, MYSQLI_ASSOC)) {
                if (!isset($arr[$row['uid']])) {
                    $arr[$row['uid']] = [
                        'uid' => $row['uid'],
                        'to' => $row['to_email'],
                        'uri' => $row['email_uri'],
                        'from' => $row['from_email'],
                        'subject' => $row['subject'],
                        'body' => $row['body'],
                        'attachments' => []
                    ];
                }

                //email might have no attachments
                if (!empty($row['attach_uid'])) {
                    $arr[$row['uid']]['attachments'][] = [
                        'uid' => $row['attach_uid'],
                        'filename' => $row['filename'],
                        'uri' => $row['attach_uri'],
                    ];
                }
            }
        }

        return $arr;
    }

    public function getDepotByCode($code, $arrayIndex)
    {
        $sql = "select *
			  from   depot
			  where  code='" . mysqli_real_escape_string($this->dbConn->connection, $code) . "'";

        $this->dbConn->dbQuery($sql);

        $arr = array();
        if ($this->dbConn->dbQueryResultRows > 0) {
            while ($row = mysqli_fetch_array($this->dbConn->dbQueryResult, MYSQLI_ASSOC)) {
                if ($arrayIndex == "") $arr[] = $row; else $arr[$row[$arrayIndex]] = $row;
            }
        }

        return $arr;
    }

    public function getAllDepotsArray($arrayIndex)
    {
        $sql = "SELECT a.uid, a.code, a.name depot_name
			  from depot a";

        $this->dbConn->dbQuery($sql);

        $arr = array();
        if ($this->dbConn->dbQueryResultRows > 0) {
            while ($row = mysqli_fetch_array($this->dbConn->dbQueryResult, MYSQLI_ASSOC)) {
                if ($arrayIndex == "") $arr[] = $row; else $arr[$row[$arrayIndex]] = $row;
            }
        }

        return $arr;
    }

    public function getPrincipalByCode($code, $arrayIndex)
    {
        // the lookup must strip off leading zeros. You cannot change the principal table value because it needs
        // to be the same as for historical maindb lookups.
        $pC = intval(trim($code)); // just in case it has leading zeros
        $sql = "select uid, principal_code
			  from   principal
			  where  principal_code='" . mysqli_real_escape_string($this->dbConn->connection, $pC) . "'";

        $this->dbConn->dbQuery($sql);

        $arr = array();
        if ($this->dbConn->dbQueryResultRows > 0) {
            while ($row = mysqli_fetch_array($this->dbConn->dbQueryResult, MYSQLI_ASSOC)) {
                if ($arrayIndex == "") $arr[] = $row; else $arr[$row[$arrayIndex]] = $row;
            }
        }

        return $arr;
    }

// *******************************************************************************************************************************


    public function getPrincipalByAltCode($code, $arrayIndex)
    {
        $sql = "select uid, principal_code, alt_principal_code
			  from   principal
			  where  alt_principal_code='" . mysqli_real_escape_string($this->dbConn->connection, $code) . "'";

        $this->dbConn->dbQuery($sql);

        $arr = array();
        if ($this->dbConn->dbQueryResultRows > 0) {
            while ($row = mysqli_fetch_array($this->dbConn->dbQueryResult, MYSQLI_ASSOC)) {
                if ($arrayIndex == "") $arr[] = $row; else $arr[$row[$arrayIndex]] = $row;
            }
        }

        return $arr;
    }

    public function getPrincipalByUpliftCode($code, $arrayIndex)
    {

        $sql = "select uid, principal_code, principal_uplift_code
                    from   principal
                    where  principal_uplift_code='" . mysqli_real_escape_string($this->dbConn->connection, $code) . "'";

        $this->dbConn->dbQuery($sql);

        $arr = array();
        if ($this->dbConn->dbQueryResultRows > 0) {
            while ($row = mysqli_fetch_array($this->dbConn->dbQueryResult, MYSQLI_ASSOC)) {
                if ($arrayIndex == "") $arr[] = $row; else $arr[$row[$arrayIndex]] = $row;
            }
        }

        return $arr;

    }

    // note: if alt_principal_code is supplied as index and there is no alt principal_code then the row is not added !
    public function getAllPrincipalsArray($arrayIndex)
    {
        $sql = "select uid, principal_code, alt_principal_code
			  from   principal";

        $this->dbConn->dbQuery($sql);

        $arr = array();
        if ($this->dbConn->dbQueryResultRows > 0) {
            while ($row = mysqli_fetch_array($this->dbConn->dbQueryResult, MYSQLI_ASSOC)) {
                if ($arrayIndex == "") $arr[] = $row;
                else {
                    if (($arrayIndex == "alt_principal_code") && ($row[$arrayIndex] != "")) $arr[$row[$arrayIndex]] = $row;
                    else if (($arrayIndex == "principal_code") && ($row[$arrayIndex] != "")) $arr[$row[$arrayIndex]] = $row;
                    else if ($arrayIndex != "alt_principal_code") $arr[$row[$arrayIndex]] = $row;
                }
            }
        }

        return $arr;
    }

    public function getPrincipalStoreByStDelNameOldAccount($principalUId, $oldAccount, $strippedDeliverName, $arrayIndex)
    {
        $sql = "select uid, old_account, deliver_name, stripped_deliver_name, principal_chain_uid
			  from   principal_store_master
			  where  principal_uid='" . mysqli_real_escape_string($this->dbConn->connection, $principalUId) . "'
			  and    old_account='" . mysqli_real_escape_string($this->dbConn->connection, $oldAccount) . "'
			  and    stripped_deliver_name='" . mysqli_real_escape_string($this->dbConn->connection, $strippedDeliverName) . "'
			  order  by if(status='A',1,2), if(owned_by is null,1,2)";

        $this->dbConn->dbQuery($sql);

        $arr = array();
        if ($this->dbConn->dbQueryResultRows > 0) {
            while ($row = mysqli_fetch_array($this->dbConn->dbQueryResult, MYSQLI_ASSOC)) {
                if ($arrayIndex == "") $arr[] = $row; else $arr[$row[$arrayIndex]] = $row;
            }
        }

        return $arr;
    }

    public function getPrincipalStoreByOldAccount($principalUId, $oldAccount, $arrayIndex)
    {
        if (trim($oldAccount) == "") {
            return array();
        }

        $sql = "select uid, old_account, deliver_name, stripped_deliver_name, status, on_hold, depot_uid, no_vat
			  from   principal_store_master
			  where  principal_uid='" . mysqli_real_escape_string($this->dbConn->connection, $principalUId) . "'
			  and    old_account='" . mysqli_real_escape_string($this->dbConn->connection, trim($oldAccount)) . "'
			  order  by if(status='A',1,2), if(owned_by is null,1,2)";

        $this->dbConn->dbQuery($sql);

        $arr = array();
        if ($this->dbConn->dbQueryResultRows > 0) {
            while ($row = mysqli_fetch_array($this->dbConn->dbQueryResult, MYSQLI_ASSOC)) {
                if ($arrayIndex == "") $arr[] = $row; else $arr[$row[$arrayIndex]] = $row;
            }
        }

        return $arr;
    }

    // the order did not originate from SureServer, preference given to UId
    public function getPrincipalStoreByOfflineClient($principalUId, $psmUId, $oldAccount)
    {
        if (trim($psmUId) == "") $tPSM = "X"; else $tPSM = trim($psmUId);
        if (trim($oldAccount) == "") $tOA = "X"; else $tOA = trim($oldAccount);
        $sql = "select uid, old_account, deliver_name, stripped_deliver_name, owned_by, vendor_created_by_uid
			  from   principal_store_master
			  where  principal_uid='" . mysqli_real_escape_string($this->dbConn->connection, $principalUId) . "'
			  and    (old_account='" . mysqli_real_escape_string($this->dbConn->connection, $tOA) . "' or uid='" . mysqli_real_escape_string($this->dbConn->connection, $tPSM) . "')
			  order   by if(uid='{$psmUId}',1,2), if(status='A',1,2),if(uid='" . mysqli_real_escape_string($this->dbConn->connection, $tPSM) . "',1,2), if(owned_by is null,1,2)";

        $this->dbConn->dbQuery($sql);

        $arr = array();
        if ($this->dbConn->dbQueryResultRows > 0) {
            while ($row = mysqli_fetch_array($this->dbConn->dbQueryResult, MYSQLI_ASSOC)) {
                $arr[] = $row;
            }
        }

        return $arr;
    }


    public function getPrincipalStoreByStrippedDeliverName($principalUId, $deliverName, $arrayIndex)
    {
        $strippedDeliverName = CommonUtils::getStrippedValue($deliverName);
        $sql = "select uid, old_account, deliver_name, stripped_deliver_name, principal_chain_uid, depot_uid
			  from   principal_store_master
			  where  principal_uid='" . mysqli_real_escape_string($this->dbConn->connection, $principalUId) . "'
			  and    stripped_deliver_name='" . mysqli_real_escape_string($this->dbConn->connection, $strippedDeliverName) . "'
			  order  by if(status='A',1,2),if(owned_by is null,1,2)";

        $this->dbConn->dbQuery($sql);

        $arr = array();
        if ($this->dbConn->dbQueryResultRows > 0) {
            while ($row = mysqli_fetch_array($this->dbConn->dbQueryResult, MYSQLI_ASSOC)) {
                if ($arrayIndex == "") $arr[] = $row; else $arr[$row[$arrayIndex]] = $row;
            }
        }

        return $arr;
    }

    public function getDocumentTypeByCode($code, $arrayIndex)
    {
        $sql = "select *
			  from   document_type
			  where  code='" . mysqli_real_escape_string($this->dbConn->connection, $code) . "'";

        $this->dbConn->dbQuery($sql);

        $arr = array();
        if ($this->dbConn->dbQueryResultRows > 0) {
            while ($row = mysqli_fetch_array($this->dbConn->dbQueryResult, MYSQLI_ASSOC)) {
                if ($arrayIndex == "") $arr[] = $row; else $arr[$row[$arrayIndex]] = $row;
            }
        }

        return $arr;
    }

    public function getStatusByCode($code, $arrayIndex)
    {
        $sql = "select *
			  from   status
			  where  status_code='" . mysqli_real_escape_string($this->dbConn->connection, $code) . "'";

        $this->dbConn->dbQuery($sql);

        $arr = array();
        if ($this->dbConn->dbQueryResultRows > 0) {
            while ($row = mysqli_fetch_array($this->dbConn->dbQueryResult, MYSQLI_ASSOC)) {
                if ($arrayIndex == "") $arr[] = $row; else $arr[$row[$arrayIndex]] = $row;
            }
        }

        return $arr;
    }

    public function getStatusArray()
    {

        $sql = "select uid, status_code from status";
        return $this->dbConn->dbGetAll($sql);

    }


    public function getReasonCodeArray()
    {

        $sql = "select uid, code from reason_code";
        return $this->dbConn->dbGetAll($sql);

    }


    public function getPrincipalProductByCode($principalUId, $code, $arrayIndex)
    {


        $sql = "select uid, product_code, product_description
			  from   principal_product
			  where  principal_uid='" . mysqli_real_escape_string($this->dbConn->connection, $principalUId) . "'
			  and    product_code='" . mysqli_real_escape_string($this->dbConn->connection, $code) . "'";

        $this->dbConn->dbQuery($sql);

        $arr = array();
        if ($this->dbConn->dbQueryResultRows > 0) {
            while ($row = mysqli_fetch_array($this->dbConn->dbQueryResult, MYSQLI_ASSOC)) {
                if ($arrayIndex == "") $arr[] = $row; else $arr[$row[$arrayIndex]] = $row;
            }
        }

        return $arr;
    }

    public function getPrincipalProductByAltCode($principalUId, $code, $arrayIndex)
    {
        $sql = "select uid, product_code, product_description
			  from   principal_product
			  where  principal_uid='" . mysqli_real_escape_string($this->dbConn->connection, $principalUId) . "'
			  and    alt_code='" . mysqli_real_escape_string($this->dbConn->connection, $code) . "'";

        $this->dbConn->dbQuery($sql);

        $arr = array();
        if ($this->dbConn->dbQueryResultRows > 0) {
            while ($row = mysqli_fetch_array($this->dbConn->dbQueryResult, MYSQLI_ASSOC)) {
                if ($arrayIndex == "") $arr[] = $row; else $arr[$row[$arrayIndex]] = $row;
            }
        }

        return $arr;
    }


    // uploads from clipper have 1,13 combined for doc types
    // if this is called specifying array index value then only the LAST document found will be returned in this case 13!!!
    public function getDocumentMasterByOtherKey($principalUId, $docNumber, $docTypeUId, $depotUId, $arrayIndex)
    {
        $sql = "select dm.uid, document_number, principal_store_uid, data_source, captured_by
			  from   document_master dm,
                     document_header dh
			  where  principal_uid='" . mysqli_real_escape_string($this->dbConn->connection, $principalUId) . "'
			  and    dm.uid = dh.document_master_uid
			  and    document_number='" . mysqli_real_escape_string($this->dbConn->connection, $docNumber) . "'
			  and    dm.depot_uid='" . mysqli_real_escape_string($this->dbConn->connection, $depotUId) . "'
			  and    if(document_type_uid=" . DT_ORDINV_ZERO_PRICE . " OR document_type_uid=" . DT_DELIVERYNOTE . "," . DT_ORDINV . ",document_type_uid)='{$docTypeUId}'
			  order  by if(document_type_uid='{$docTypeUId}',0,document_type_uid)";

        $this->dbConn->dbQuery($sql);

        $arr = array();
        if ($this->dbConn->dbQueryResultRows > 0) {
            while ($row = mysqli_fetch_array($this->dbConn->dbQueryResult, MYSQLI_ASSOC)) {
                if ($arrayIndex == "") $arr[] = $row; else $arr[$row[$arrayIndex]] = $row;
            }
        }

        return $arr;
    }

    public function getDocumentHeaderByOtherKey($dmUId, $arrayIndex)
    {
        $sql = "select uid
			  from   document_header
			  where  document_master_uid='" . mysqli_real_escape_string($this->dbConn->connection, $dmUId) . "'";

        $this->dbConn->dbQuery($sql);

        $arr = array();
        if ($this->dbConn->dbQueryResultRows > 0) {
            while ($row = mysqli_fetch_array($this->dbConn->dbQueryResult, MYSQLI_ASSOC)) {
                if ($arrayIndex == "") $arr[] = $row; else $arr[$row[$arrayIndex]] = $row;
            }
        }

        return $arr;
    }

    public function getDocumentDetailByOtherKey($dmUId, $lineNo, $productUId, $arrayIndex)
    {
        $sql = "select uid
			  from   document_detail
			  where  document_master_uid='" . mysqli_real_escape_string($this->dbConn->connection, $dmUId) . "'
			  and    line_no='" . mysqli_real_escape_string($this->dbConn->connection, $lineNo) . "'
			  and    line_no='" . mysqli_real_escape_string($this->dbConn->connection, $productUId) . "'";

        $this->dbConn->dbQuery($sql);

        $arr = array();
        if ($this->dbConn->dbQueryResultRows > 0) {
            while ($row = mysqli_fetch_array($this->dbConn->dbQueryResult, MYSQLI_ASSOC)) {
                if ($arrayIndex == "") $arr[] = $row; else $arr[$row[$arrayIndex]] = $row;
            }
        }

        return $arr;
    }


    public function getUploadFiles()
    {
        global $ROOT;

        $waiting = array();

        // get errors
        $path = $ROOT . DIR_PHPBACKEND_DATA_IMPORT_PROCESSED_ERROR;
        $handle = opendir($path);
        while (false !== ($file = readdir($handle))) {
            if (is_dir($path . $file)) continue; // skip if directory
            $prefix = strtoupper(substr($file, 0, 3));
            if (strtoupper(substr($file, -4)) == ".ZIP") $prefix = "ZIP";
            elseif (substr($prefix, 0, 2) == "EX") $prefix = "EX";
            if (!isset($waiting[$prefix]["errors"])) $waiting[$prefix]["errors"] = 0;
            switch ($prefix) {
                case "WO_":
                    $waiting[$prefix]["errors"]++;
                    $waiting[$prefix]["description"] = "Processed Orders, awaiting upload as Document(s)";
                    break;
                case "EX":
                    $waiting[$prefix]["errors"]++;
                    $waiting[$prefix]["description"] = "Stock Listing";
                    break;
                case "SET":
                    $waiting[$prefix]["errors"]++;
                    $waiting[$prefix]["description"] = "Stock Listing";
                    break;
                case "ZIP":
                    $waiting[$prefix]["errors"]++;
                    $waiting[$prefix]["description"] = "Stock Listing";
                    break;
                default:
                    $waiting[$prefix]["errors"]++;
                    $waiting[$prefix]["description"] = "Unknown Document Type";
            }
        }

        // get waiting
        $path = $ROOT . DIR_PHPBACKEND_DATA_IMPORT;
        $handle = opendir($path);
        while (false !== ($file = readdir($handle))) {
            if (is_dir($path . $file)) continue; // skip if directory
            $prefix = strtoupper(substr($file, 0, 3));
            if (strtoupper(substr($file, -4)) == ".ZIP") $prefix = "ZIP";
            elseif (substr($prefix, 0, 2) == "EX") $prefix = "EX";
            if (!isset($waiting[$prefix]["cnt"])) $waiting[$prefix]["cnt"] = 0;
            switch ($prefix) {
                case "WO_":
                    $waiting[$prefix]["cnt"]++;
                    $waiting[$prefix]["description"] = "Processed Orders, awaiting upload as Document(s)";
                    break;
                case "EX":
                    $waiting[$prefix]["cnt"]++;
                    $waiting[$prefix]["description"] = "Stock Listing";
                    break;
                case "SET":
                    $waiting[$prefix]["cnt"]++;
                    $waiting[$prefix]["description"] = "Stock Listing";
                    break;
                case "ZIP":
                    $waiting[$prefix]["cnt"]++;
                    $waiting[$prefix]["description"] = "ZIP Archive, awaiting auto extraction";
                    break;
                default:
                    $waiting[$prefix]["cnt"]++;
                    $waiting[$prefix]["description"] = "Unknown Document Type";
            }
        }

        return $waiting;
    }

    public function getLatestJobs()
    {
        $this->dbConn->dbQuery("SET time_zone='+0:00'"); // important to make sure PHPBACKEND which sits in RSA synchs changes updated according to SA time (unsynched table is in RSA), so make sureserver and this same time

        $sql = "select a.type, a.run_date, a.job_ended, message, completed_successfully,
							hour(timediff(now(),run_date))*60*60 + minute(timediff(now(),run_date))*60 + second(timediff(now(),run_date)) seconds_elapsed,
							(select max(end_time_seconds-start_time_seconds) job_time from job c where a.type = c.type and c.start_time_seconds>0 and c.end_time_seconds>0) max_job_time
				from   job a
				where  run_date = (select max(run_date) mrd from job b where a.type = b.type)";

        $this->dbConn->dbQuery($sql);

        $arr = array();
        while ($row = mysqli_fetch_array($this->dbConn->dbQueryResult, MYSQLI_ASSOC)) {
            $arr[$row["type"]]["job_ended"] = $row["job_ended"];
            $arr[$row["type"]]["run_date"] = $row["run_date"];
            $arr[$row["type"]]["message"] = $row["message"];
            $arr[$row["type"]]["completed_successfully"] = $row["completed_successfully"];
            $arr[$row["type"]]["seconds_elapsed"] = $row["seconds_elapsed"];
            $arr[$row["type"]]["max_job_time"] = $row["max_job_time"];
        }

        return $arr;
    }

    public function getAllFTPLocations($processId)
    {
        $this->dbConn->dbQuery("SET time_zone='+0:00'"); // important to make sure PHPBACKEND which sits in RSA synchs changes updated according to SA time (unsynched table is in RSA), so make sureserver and this same time

        $sql = "select from_location, file_wildcard, file_end_delimiter, root_dir_constant, destination_folder, ftp_type
			  from   ftp_location ";
        if ($processId != "") $sql .= " where  process_id = '" . $processId . "'";

        $this->dbConn->dbQuery($sql);

        $arr = array();
        while ($row = mysqli_fetch_array($this->dbConn->dbQueryResult, MYSQLI_ASSOC)) {
            if ($row["root_dir_constant"] != "") $row["root_dir_constant"] = constant($row["root_dir_constant"]);
            $arr[] = $row;
        }
        // add

        return $arr;
    }

    // process online files directly

    /**
     * @return uid, root_dir_constant, file_path, file_wildcard, file_end_delimiter, stop_on_error, file_end_delimiter_is_regex, order,vendor_uid, principal_uid, adaptor_name, process_name
     */
    public function getAllOnlineImportLocations()
    {
        $sql = "select uid, root_dir_constant, file_path, file_wildcard, file_end_delimiter, stop_on_error, file_end_delimiter_is_regex, `order`,
					 vendor_uid, principal_uid, adaptor_name, process_name, transaction_type, email_domain_list, email_add_file_seq
			  from   online_file_processing
			  order  by root_dir_constant, file_path, if(`order` is null,99999,`order`), file_wildcard";

        $this->dbConn->dbQuery($sql);

        $arr = array();
        while ($row = mysqli_fetch_array($this->dbConn->dbQueryResult, MYSQLI_ASSOC)) {
            if ($row["root_dir_constant"] != "") $row["root_dir_constant"] = constant($row["root_dir_constant"]);
            $arr[] = $row;
        }
        // add

        return $arr;
    }

    public function getAllEmailFileMappings()
    {
        $sql = "select *
          from   email_file_mapping
          order  by root_dir_constant, file_path, file_wildcard";

        $this->dbConn->dbQuery($sql);

        $arr = array();
        while ($row = mysqli_fetch_array($this->dbConn->dbQueryResult, MYSQLI_ASSOC)) {
            if ($row["root_dir_constant"] != "") $row["root_dir_constant"] = constant($row["root_dir_constant"]);
            $arr[] = $row;
        }
        // add

        return $arr;
    }

    public function getOnlineImportMappings($ofpUId)
    {
        $sql = "select *
			  from   online_file_processing_mapping
			  where  online_file_processing_uid = '{$ofpUId}'";

        $this->dbConn->dbQuery($sql);

        $arr = array();
        while ($row = mysqli_fetch_array($this->dbConn->dbQueryResult, MYSQLI_ASSOC)) {
            $arr[] = $row;
        }

        return $arr;
    }

    // $rsOIMArr is essentially the array structure from getOnlineImportMappings above
    public function getMappingFromOIMByPrincipalIdentifier($rsOIMArr, $principalIdentifier)
    {
        $mapping = array();
        foreach ($rsOIMArr as $row) {
            // get where null just in case doesnt find exact principal later
            if ($row["principal_uid"] == "") {
                $mapping = $row;
            } else if ($row["principal_identifier"] == $principalIdentifier) {
                return $row;
            }
        }

        return $mapping;
    }

    // $rsOIMArr is essentially the array structure from getOnlineImportMappings above
    public function getMappingFromOIMByPrincipalUId($rsOIMArr, $principalUId)
    {
        $mapping = array();
        foreach ($rsOIMArr as $row) {
            // get where null just in case doesnt find exact principal later
            if ($row["principal_uid"] == "") {
                $mapping = $row;
            } else if ($row["principal_uid"] == $principalUId) {
                return $row;
            }
        }

        return $mapping;
    }

    // @param $indexArray = static var PD*
    public function getAllDepotPrincipalExportMapping($indexArray = false, $principalUId = false)
    {
        $sql = "select *
          from   depot_principal_export_mapping
          " . (($principalUId !== false) ? "where  principal_uid = {$principalUId}" : "");

        $this->dbConn->dbQuery($sql);

        $arr = array();
        while ($row = mysqli_fetch_array($this->dbConn->dbQueryResult, MYSQLI_ASSOC)) {

            if ($indexArray === false) $arr[] = $row;
            // make it directly accessible, is unique key on tbl so won't have multiple rows
            else if ($indexArray == self::$PDP_EXP_MAP_INDEX_1) $arr[$row["principal_uid"]][$row["principal_code"]] = $row; // principal_code is the export mapping for that depot+principal
            else if ($indexArray == self::$PDP_EXP_MAP_INDEX_2) $arr[$row["principal_uid"]][$row["depot_uid"]] = $row; // principal_code is the export mapping for that depot+principal
            else if ($indexArray == self::$PDP_EXP_MAP_INDEX_3) $arr[$row["principal_code"]] = $row; // principal_code is the export mapping for that depot+principal

        }

        return $arr;
    }

    /**
     * @return []file_name, processed_date, status, vendor_uid, vendor_removal_date, vendor_removed, error_count, error_msg
     */
    public function getFileLogItem($fileName, $filterToPastMonths = false)
    {
        $this->dbConn->dbQuery("SET time_zone='+0:00'"); // important to make sure PHPBACKEND which sits in RSA synchs changes updated according to SA time (unsynched table is in RSA), so make sureserver and this same time

        // duplicate files get stored with status=N
        $sql = "select uid, file_name, processed_date, status, vendor_uid, vendor_removal_date, vendor_removed, error_count, error_msg, error_type,
  					principal_uid, hour(timediff(now(),processed_date))*60*60 + minute(timediff(now(),processed_date))*60 + second(timediff(now(),processed_date)) seconds_elapsed,
  					online_file_processing_uid
  			  from   file_log
  			  where  file_name = '{$fileName}'
                          and    vendor_removed != 'Y'
  			  and    status not in ('" . FLAG_STATUS_DELETED . "') " .
            (($filterToPastMonths !== false) ? (" AND processed_date > NOW() - INTERVAL {$filterToPastMonths} MONTH") : ("")) .
            " order  by if(status!='" . FLAG_ERRORTO_SUCCESS . "',1,2)";

        $this->dbConn->dbQuery($sql);

        $arr = array();
        while ($row = mysqli_fetch_array($this->dbConn->dbQueryResult, MYSQLI_ASSOC)) {
            $arr[] = $row;
        }

        return $arr;
    }

    /**
     * @return []file_name, processed_date, status, vendor_uid, vendor_removal_date, vendor_removed, error_count, error_msg
     */
    public function getFileLogItemByUId($fileLogUId)
    {
        $this->dbConn->dbQuery("SET time_zone='+0:00'"); // important to make sure PHPBACKEND which sits in RSA synchs changes updated according to SA time (unsynched table is in RSA), so make sureserver and this same time

        $sql = "select a.uid, file_name, processed_date, status, a.vendor_uid, vendor_removal_date, vendor_removed, error_count, error_msg, error_type,
					a.principal_uid, hour(timediff(now(),processed_date))*60*60 + minute(timediff(now(),processed_date))*60 + second(timediff(now(),processed_date)) seconds_elapsed,
					online_file_processing_uid, b.stop_on_error
			  from   file_log a,
                     online_file_processing b
			  where  a.uid = '{$fileLogUId}'
			  and    vendor_removed != 'Y'
			  and    a.online_file_processing_uid = b.uid";

        $this->dbConn->dbQuery($sql);

        while ($row = mysqli_fetch_array($this->dbConn->dbQueryResult, MYSQLI_ASSOC)) {
            return $row;
        }

        return array();
    }

    // this is done this way to save db time later, so that each row doesnt have to fetch the detail etc.
    public function getOrdersHoldingForProcessing($ohUId = false)
    {
        $TO = new ImportDAO_getOrdersHoldingForProcessingTO();

        $where = (($ohUId === false) ? "" : " and oh.uid = '{$ohUId}'");

        // Theoretically we should lock the tables right here, but it does not matter if you get orphan children (if inserts happening whilst this is running) because the loop will be controlled by header.

        // build the headerArr with key as uid
        $sql = "select oh.*
                from   orders_holding oh
		                      INNER JOIN principal p on oh.principal_uid = p.uid and p.status = '" . FLAG_STATUS_ACTIVE . "'
				where  if(oh.status is null,0,oh.status) not in ('" . FLAG_ERRORTO_SUCCESS . "','" . FLAG_STATUS_DELETED . "')
        and    (FIND_IN_SET('R.A',ifnull(oh.status,0))=0 or 1=" . (($ohUId === false) ? 0 : 1) . ")
				and    FIND_IN_SET('R.A.MP',ifnull(oh.status,0))=0
				and    FIND_IN_SET('SUSP',ifnull(oh.status,0))=0 "
            . $where;

        $this->dbConn->dbQuery($sql);

        while ($row = mysqli_fetch_array($this->dbConn->dbQueryResult, MYSQLI_ASSOC)) {
            foreach ($row as $key => $value) {
                $TO->headerArr[$row["uid"]][$key] = $value;
            }
        }

        // build the array with key as orders_holding_uid
        $sql = "select ohd.*
            from   orders_holding oh
		                  INNER JOIN principal p on oh.principal_uid = p.uid and p.status = '" . FLAG_STATUS_ACTIVE . "',
					         orders_holding_detail ohd
    				where  if(oh.status is null,0,oh.status) not in ('" . FLAG_ERRORTO_SUCCESS . "','" . FLAG_STATUS_DELETED . "')
            and    (FIND_IN_SET('R.A',ifnull(oh.status,0))=0 or 1=" . (($ohUId === false) ? "0" : "1") . ")
    				and    FIND_IN_SET('R.A.MP',ifnull(oh.status,0))=0
    				and    FIND_IN_SET('SUSP',ifnull(oh.status,0))=0
    				and    oh.uid = ohd.orders_holding_uid
    				and    if(ohd.status is null,0,ohd.status)!='" . FLAG_STATUS_DELETED . "' "
            . $where . "
            order  by ohd.uid"; // depot extracts (CandyTops) need the order to be correct

        $this->dbConn->dbQuery($sql);

        while ($row = mysqli_fetch_array($this->dbConn->dbQueryResult, MYSQLI_ASSOC)) {
            foreach ($row as $key => $value) {
                $TO->detailArr[$row["orders_holding_uid"]][$row["uid"]][$key] = $value;
            }
            /*echo '<pre>';
            print_r($TO);
            echo '</pre>';*/
        }

        // build the array with key as orders_holding_uid
        $sql = "select ohs.*
            from   orders_holding oh
		                      INNER JOIN principal p on oh.principal_uid = p.uid and p.status = '" . FLAG_STATUS_ACTIVE . "',
                    orders_holding_store ohs
    				where  if(oh.status is null,0,oh.status) not in ('" . FLAG_ERRORTO_SUCCESS . "','" . FLAG_STATUS_DELETED . "')
            and    (FIND_IN_SET('R.A',ifnull(oh.status,0))=0 or 1=" . (($ohUId === false) ? "0" : "1") . ")
    				and    FIND_IN_SET('R.A.MP',ifnull(oh.status,0))=0
    				and    FIND_IN_SET('SUSP',ifnull(oh.status,0))=0
    				and    oh.uid = ohs.orders_holding_uid "
            . $where;

        $this->dbConn->dbQuery($sql);

        while ($row = mysqli_fetch_array($this->dbConn->dbQueryResult, MYSQLI_ASSOC)) {
            foreach ($row as $key => $value) {
                $TO->storeArr[$row["orders_holding_uid"]][$key] = $value;
            }
        }

        // build the array with key as orders_holding_uid - can exist without a store level above
        $sql = "select ohsp.*
            from   orders_holding oh
                      INNER JOIN principal p on oh.principal_uid = p.uid and p.status = '" . FLAG_STATUS_ACTIVE . "',
                   orders_holding_special_field ohsp
				where  if(oh.status is null,0,oh.status) not in ('" . FLAG_ERRORTO_SUCCESS . "','" . FLAG_STATUS_DELETED . "')
        and    (FIND_IN_SET('R.A',ifnull(oh.status,0))=0 or 1=" . (($ohUId === false) ? "0" : "1") . ")
				and    FIND_IN_SET('R.A.MP',ifnull(oh.status,0))=0
				and    FIND_IN_SET('SUSP',ifnull(oh.status,0))=0
				and    oh.uid = ohsp.orders_holding_uid "
            . $where;

        $this->dbConn->dbQuery($sql);

        while ($row = mysqli_fetch_array($this->dbConn->dbQueryResult, MYSQLI_ASSOC)) {
            foreach ($row as $key => $value) {
                $TO->specialFieldArr[$row["orders_holding_uid"]][$row["uid"]][$key] = $value;
            }
        }
        // build the array with key as orders_holding_uid
        $sql = "select oh.uid orders_holding_uid, ofpm.*
            from   orders_holding oh
		                      INNER JOIN principal p on oh.principal_uid = p.uid and p.status = '" . FLAG_STATUS_ACTIVE . "',
                    online_file_processing_mapping ofpm
				where  if(oh.status is null,0,oh.status) not in ('" . FLAG_ERRORTO_SUCCESS . "','" . FLAG_STATUS_DELETED . "')
        and    (FIND_IN_SET('R.A',ifnull(oh.status,0))=0 or 1=" . (($ohUId === false) ? "0" : "1") . ")
				and    FIND_IN_SET('R.A.MP',ifnull(oh.status,0))=0
				and    FIND_IN_SET('SUSP',ifnull(oh.status,0))=0
				and    if(oh.user_action_status is null,0,oh.user_action_status)!='E'
				and    oh.online_file_processing_uid is not null
				and    oh.online_file_processing_uid = ofpm.online_file_processing_uid
				and    (oh.principal_uid = ofpm.principal_uid or ofpm.principal_uid is null) "
            . $where . "
				order  by orders_holding_uid, if(ofpm.principal_uid is not null,1,2)"; // with MySQL, the order is done first then limit applied, contrary to Oracle etc.

        $this->dbConn->dbQuery($sql);

        while ($row = mysqli_fetch_array($this->dbConn->dbQueryResult, MYSQLI_ASSOC)) {
            foreach ($row as $key => $value) {
                if (!isset($TO->onlineFileProcessingMapping[$row["orders_holding_uid"]][$key])) {
                    // only store the first most specific one by order clause
                    $TO->onlineFileProcessingMapping[$row["orders_holding_uid"]][$key] = $value;
                }
            }
        }

        return $TO;
    }

    public function getImportPreferences($principalId)
    {
        $sql = "select *
				from  import_preference
				where principal_uid = '" . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "'";

        $this->dbConn->dbQuery($sql);

        $arr = array();
        while ($row = mysqli_fetch_array($this->dbConn->dbQueryResult, MYSQLI_ASSOC)) {
            $arr[] = $row;
        }
        if (empty($arr)) {
            $arr[0]["uid"] = "";
            $arr[0]["principal_uid"] = $principalId;
            $arr[0]["escalate_error_cnt"] = "30";
            $arr[0]["pricing_conflict_action_ws_pnp"] = "3";
            $arr[0]["pricing_conflict_action_ws_checkers"] = "3";
            $arr[0]["pricing_conflict_action_directsql"] = "3";
            $arr[0]["price_variance_notification"] = "N";
            $arr[0]["pnp_conversion_items_per_case"] = "N";
            $arr[0]["checkers_conversion_items_per_case"] = "N";
        }

        return $arr;
    }

    public function getOrdersByDN($principalId, $documentNumber, $depotUId, $documentTypeUId)
    {
        $sql = "SELECT uid, storechain_uid, order_sequence_no, `date` order_date, capturedate
				FROM orders o
				WHERE o.principal_uid = '" . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "'
				AND   o.document_number = '" . mysqli_real_escape_string($this->dbConn->connection, $documentNumber) . "'
				AND   o.processed_depot_uid = '$depotUId'
				AND   o.document_type = '" . mysqli_real_escape_string($this->dbConn->connection, $documentTypeUId) . "'";

        $this->dbConn->dbQuery($sql);

        $arr = array();
        if ($this->dbConn->dbQueryResultRows > 0) {
            while ($row = mysqli_fetch_array($this->dbConn->dbQueryResult, MYSQLI_ASSOC)) {
                $arr[] = $row;
            }
        }

        return $arr;
    }

    public function getOrdersByON($principalId, $orderNumber, $depotUId, $documentTypeUId, $principalStoreUId = false)
    {
        $sql = "SELECT uid, storechain_uid, order_sequence_no, `date` order_date, capturedate
				FROM orders o
				WHERE o.principal_uid = '" . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "'
				AND   o.order_number = '" . mysqli_real_escape_string($this->dbConn->connection, $orderNumber) . "'
				AND   o.processed_depot_uid = '$depotUId'
				AND   o.document_type = '" . mysqli_real_escape_string($this->dbConn->connection, $documentTypeUId) . "'";

        if (!($principalStoreUId === false)) {
            $sql .= " AND o.storechain_uid = '{$principalStoreUId}' ";
        }

        $this->dbConn->dbQuery($sql);

        $arr = array();
        if ($this->dbConn->dbQueryResultRows > 0) {
            while ($row = mysqli_fetch_array($this->dbConn->dbQueryResult, MYSQLI_ASSOC)) {
                $arr[] = $row;
            }
        }

        return $arr;
    }

    public function getOrdersByOS($principalId, $orderSequenceNo)
    {
        $sql = "SELECT uid, data_source, captured_by, captureuser_uid
				FROM orders o
				WHERE o.principal_uid = '" . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "'
				AND   o.order_sequence_no = '" . mysqli_real_escape_string($this->dbConn->connection, $orderSequenceNo) . "'";

        $this->dbConn->dbQuery($sql);

        $arr = array();
        if ($this->dbConn->dbQueryResultRows > 0) {
            while ($row = mysqli_fetch_array($this->dbConn->dbQueryResult, MYSQLI_ASSOC)) {
                $arr[] = $row;
            }
        }

        return $arr;
    }

    public function getDocumentsByDN($principalId, $documentNumber, $depotUId, $documentTypeUId)
    {
        $sql = "SELECT a.uid, principal_store_uid, order_sequence_no, order_date
				FROM document_master a,
                     document_header b
				WHERE principal_uid = '" . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "'
				AND   a.uid = b.document_master_uid
				AND   document_type_uid = '" . mysqli_real_escape_string($this->dbConn->connection, $documentTypeUId) . "'
				AND   (document_number = '" . mysqli_real_escape_string($this->dbConn->connection, $documentNumber) . "' or
							 document_number = '" . mysqli_real_escape_string($this->dbConn->connection, str_pad(trim($documentNumber), 8, "0", STR_PAD_LEFT)) . "')
				AND   a.depot_uid = '" . mysqli_real_escape_string($this->dbConn->connection, $depotUId) . "'";

        $this->dbConn->dbQuery($sql);

        $arr = array();
        if ($this->dbConn->dbQueryResultRows > 0) {
            while ($row = mysqli_fetch_array($this->dbConn->dbQueryResult, MYSQLI_ASSOC)) {
                $arr[] = $row;
            }
        }

        return $arr;
    }

    public function getDocumentsByON($principalId, $orderNumber, $depotUId = false, $documentTypeUId, $principalStoreUId = false)
    {
        $sql = "SELECT a.uid, principal_store_uid, order_sequence_no, order_date, document_status_uid, pod_document_master_uid, buyer_document_status_uid
				FROM document_master a,
             document_header b
				WHERE principal_uid = '" . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "'
				AND   a.uid = b.document_master_uid
				AND   document_type_uid = '" . mysqli_real_escape_string($this->dbConn->connection, $documentTypeUId) . "'
				AND   customer_order_number = '" . mysqli_real_escape_string($this->dbConn->connection, $orderNumber) . "'";

        if ($depotUId !== false) $sql .= " AND   a.depot_uid = '" . mysqli_real_escape_string($this->dbConn->connection, $depotUId) . "'";
        if (!($principalStoreUId === false)) $sql .= " AND b.principal_store_uid = '{$principalStoreUId}' ";

        $this->dbConn->dbQuery($sql);

        $arr = array();
        if ($this->dbConn->dbQueryResultRows > 0) {
            while ($row = mysqli_fetch_array($this->dbConn->dbQueryResult, MYSQLI_ASSOC)) {
                $arr[] = $row;
            }
        }

        return $arr;
    }


    // this is all EDI files, not just orders
    public function getPrincipalEDIFileDefinitions($principalId)
    {
        $sql = "select ofp.uid, ofp.file_path, ofp.file_wildcard
				from   online_file_processing ofp
							 left join online_file_processing_mapping ofpm on ofp.uid = ofpm.online_file_processing_uid
				where  '{$principalId}' in (ofp.principal_uid,ofpm.principal_uid)";

        $this->dbConn->dbQuery($sql);

        $arr = array();
        if ($this->dbConn->dbQueryResultRows > 0) {
            while ($row = mysqli_fetch_array($this->dbConn->dbQueryResult, MYSQLI_ASSOC)) {
                $arr[] = $row;
            }
        }

        return $arr;
    }

    // this is all EDI files, not just orders
    public function getPrincipalEDIFilesProcessed($principalId, $fLUId = false, $sortBy = false)
    {
        $sql = "select fl.*
				from   online_file_processing ofp
							 left join online_file_processing_mapping ofpm on ofp.uid = ofpm.online_file_processing_uid,
						 file_log fl
				where  '{$principalId}' in (ofp.principal_uid,ofpm.principal_uid)
				and    ofp.uid = fl.online_file_processing_uid " .
            (($fLUId !== false) ? " and fl.uid='{$fLUId}' " : "");

        if ($sortBy === false) $sql .= " order by file_name";
        else if ($sortBy == self::$FILE_SORT_BY_PROCESSED_DATE_DESC) $sql .= " order by fl.processed_date desc";

        $this->dbConn->dbQuery($sql);

        $arr = array();
        if ($this->dbConn->dbQueryResultRows > 0) {
            while ($row = mysqli_fetch_array($this->dbConn->dbQueryResult, MYSQLI_ASSOC)) {
                $arr[] = $row;
            }
        }

        return $arr;
    }

    public function getOrdersUsingFileLog($fileLogUId, $principalUId, $documentNumber, $documentTypeUId)
    {
        $sql = "select a.depot_uid, c.client_document_number
            from   document_master a,
            		document_header b,
            		orders c
            where a.uid = b.document_master_uid
            and   a.principal_uid = {$principalUId}
            and   a.file_log_uid = 	{$fileLogUId}
            and   a.document_number = '{$documentNumber}'
            and   a.document_type_uid = {$documentTypeUId}
            and   a.order_sequence_no = c.order_sequence_no";

        return $this->dbConn->dbGetAll($sql);

    }

//  ********************************************************************************************************************************************
    public function getPrincipalUidFromMapping($WhPrincipal, $altCode)
    {
        $sql = "select distinct(dpem.principal_uid)
            from depot_principal_export_mapping dpem
            where dpem.principal_code = '" . $WhPrincipal . "'
            and   dpem.alt_depot_code in ('" . mysqli_real_escape_string($this->dbConn->connection, $altCode) . "')";

        echo $sql;

        return $this->dbConn->dbGetAll($sql);

    }

//  ********************************************************************************************************************************************
    public function getWarehouseUidFromMapping($WhPrincipal, $WhCode)
    {
        $sql = "select distinct(dpem.depot_uid)
            from depot_principal_export_mapping dpem
            where dpem.principal_uid = " . $WhPrincipal . "
            and   dpem.depot_code = '" . $WhCode . "'";

        return $this->dbConn->dbGetAll($sql);
    }

//  ********************************************************************************************************************************************
    public function getProductUidFromPrincipalProduct($WhPrincipal, $ProdCode)
    {
        $sql = "select pp.uid as 'ProductUid',
                   pp.product_code,
                   pp.product_description
            from .principal_product pp
            where pp.principal_uid = " . $WhPrincipal . "
            and   pp.product_code  = '" . $ProdCode . "'";
        return $this->dbConn->dbGetAll($sql);
    }

//  ********************************************************************************************************************************************
    public function getDepotByDocument($principal_id, $document)
    {
        $sql = "select dm.depot_uid
            from   document_master dm
            where  dm.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $principal_id) . "
            and    dm.document_number = '" . mysqli_real_escape_string($this->dbConn->connection, $document) . "' ";
        $this->dbConn->dbQuery($sql);
        $arr = array();
        if ($this->dbConn->dbQueryResultRows > 0) {
            while ($row = mysqli_fetch_array($this->dbConn->dbQueryResult, MYSQLI_ASSOC)) {
                $arr[] = $row;
            }
            return $arr;
        }
    }

//  ********************************************************************************************************************************************
    public function getIrlReturnReason($Ireason)
    {
        $sql = "select rc.uid
              from reason_code rc
              where rc.irl_reason_code = '" . mysqli_real_escape_string($this->dbConn->connection, $Ireason) . "'";

        return $this->dbConn->dbGetAll($sql);
    }

//  ********************************************************************************************************************************************
    public function uploadXMLfile($f)
    {

        $bldsql = "DROP TABLE IF EXISTS xml_upload_temp";

        $result = $this->dbConn->dbQuery($bldsql);

        //******************************************************************************************************************************************************

        $bldsql = "CREATE TABLE xml_upload_temp   (`FLD1` VARCHAR(200)  NULL,
                                                       `FLD2` VARCHAR(200)  NULL,
                                                       `UID`  INT(10) NULL AUTO_INCREMENT, UNIQUE INDEX `UID` (`UID`));";
        $dtresult = $this->dbConn->dbQuery($bldsql);

        //*************************************************************************************************************************************************

        $sql = 'LOAD DATA LOCAL INFILE "' . mysqli_real_escape_string($this->dbConn->connection, $f) . '" INTO TABLE xml_upload_temp
                  FIELDS TERMINATED BY ">"';

        $this->dbConn->processPosting($sql, "");

        $this->dbConn->dbQuery("commit");

    }

//  ********************************************************************************************************************************************
    public function getXMLorderDataXML()
    {

        $sql = "SELECT trim(REPLACE(fld1,'<','')) as 'F1', 
   	                  fld2 as 'F2',
   	                  UID
               FROM xml_upload_temp
               WHERE 1;";

//           echo $sql;   

        return $this->dbConn->dbGetAll($sql);


    }

//  ********************************************************************************************************************************************
    public function uploadSmollanXMLfile($f)
    {

        $bldsql = "DROP TABLE IF EXISTS xml_upload_temp";
        $result = $this->dbConn->dbQuery($bldsql);

        //******************************************************************************************************************************************************

        $bldsql = "CREATE TABLE xml_upload_temp    (`FLD1` VARCHAR(200)  NULL,
                                                        `FLD2` VARCHAR(200)  NULL,
                                                       `UID`  INT(10) NULL AUTO_INCREMENT, UNIQUE INDEX `UID` (`UID`));";
        $dtresult = $this->dbConn->dbQuery($bldsql);

        //******************************************************************************************************************************************************
        $bldsql = "DROP TABLE IF EXISTS xml_smollan_temp";

        $result = $this->dbConn->dbQuery($bldsql);

        //******************************************************************************************************************************************************

        $bldsql = "CREATE TABLE xml_smollan_temp   (`0` VARCHAR(100)  NULL,
                                                        `1` VARCHAR(100)  NULL,
                                                        `2` VARCHAR(100)  NULL,
                                                        `3` VARCHAR(100)  NULL,
                                                        `4` VARCHAR(100)  NULL,
                                                        `5` VARCHAR(100)  NULL,
                                                        `6` VARCHAR(100)  NULL,
                                                        `7` VARCHAR(100)  NULL,
                                                        `8` VARCHAR(100)  NULL,
                                                        `9` VARCHAR(100)  NULL,
                                                        `10` VARCHAR(100)  NULL,
                                                        `11` VARCHAR(100)  NULL,
                                                        `12` VARCHAR(100)  NULL,
                                                        `13` VARCHAR(100)  NULL,
                                                        `14` VARCHAR(100)  NULL,
                                                        `15` VARCHAR(100)  NULL,
                                                        `16` VARCHAR(100)  NULL,
                                                        `17` VARCHAR(100)  NULL,
                                                        `18` VARCHAR(100)  NULL,
                                                        `19` VARCHAR(100)  NULL,
                                                        `20` VARCHAR(100)  NULL,
                                                        `21` VARCHAR(100)  NULL,
                                                        `22` VARCHAR(100)  NULL,
                                                        `23` VARCHAR(100)  NULL,
                                                        `24` VARCHAR(100)  NULL,
                                                        `25` VARCHAR(100)  NULL,
                                                        `26` VARCHAR(100)  NULL,
                                                        `27` VARCHAR(100)  NULL,
                                                        `28` VARCHAR(100)  NULL,
                                                        `29` VARCHAR(100)  NULL,
                                                        `30` VARCHAR(100)  NULL,
                                                        `31` VARCHAR(100)  NULL,
                                                        `32` VARCHAR(100)  NULL,
                                                        `33` VARCHAR(100)  NULL,
                                                        `34` VARCHAR(100)  NULL,
                                                        `35` VARCHAR(100)  NULL,
                                                        `36` VARCHAR(100)  NULL,
                                                        `37` VARCHAR(100)  NULL,
                                                        `38` VARCHAR(100)  NULL,
                                                        `39` VARCHAR(100)  NULL,
                                                        `40` VARCHAR(100)  NULL,
                                                        `41` VARCHAR(100)  NULL,
                                                        `42` VARCHAR(100)  NULL,
                                                        `43` VARCHAR(100)  NULL,
                                                        `44` VARCHAR(100)  NULL,
                                                        `45` VARCHAR(100)  NULL,
                                                        `46` VARCHAR(100)  NULL,
                                                        `47` VARCHAR(100)  NULL,
                                                        `48` VARCHAR(100)  NULL,
                                                        `49` VARCHAR(100)  NULL,
                                                        `50` VARCHAR(100)  NULL,
                                                        `51` VARCHAR(100)  NULL,
                                                        `52` VARCHAR(100)  NULL,
                                                        `53` VARCHAR(100)  NULL,
                                                        `54` VARCHAR(100)  NULL,
                                                        `55` VARCHAR(100)  NULL,
                                                        `56` VARCHAR(100)  NULL,
                                                        `57` VARCHAR(100)  NULL,
                                                        `58` VARCHAR(100)  NULL,
                                                        `59` VARCHAR(100)  NULL,
                                                        `60` VARCHAR(100)  NULL,
                                                        `61` VARCHAR(100)  NULL,
                                                        `62` VARCHAR(100)  NULL,
                                                        `63` VARCHAR(100)  NULL,
                                                        `64` VARCHAR(100)  NULL,
                                                        `65` VARCHAR(100)  NULL,
                                                        `66` VARCHAR(100)  NULL,
                                                        `67` VARCHAR(100)  NULL,
                                                        `68` VARCHAR(100)  NULL,
                                                        `69` VARCHAR(100)  NULL,
                                                        `70` VARCHAR(100)  NULL,
                                                        `71` VARCHAR(100)  NULL,
                                                        `72` VARCHAR(100)  NULL,
                                                        `73` VARCHAR(100)  NULL,
                                                        `74` VARCHAR(100)  NULL,
                                                        `75` VARCHAR(100)  NULL,
                                                        `76` VARCHAR(100)  NULL,
                                                        `77` VARCHAR(100)  NULL,
                                                        `78` VARCHAR(100)  NULL,
                                                        `79` VARCHAR(100)  NULL,
                                                        `80` VARCHAR(100)  NULL,
                                                        `81` VARCHAR(100)  NULL,
                                                        `82` VARCHAR(100)  NULL,
                                                        `83` VARCHAR(100)  NULL,
                                                        `84` VARCHAR(100)  NULL,
                                                        `85` VARCHAR(100)  NULL,
                                                        `86` VARCHAR(100)  NULL,
                                                        `87` VARCHAR(100)  NULL,
                                                        `88` VARCHAR(100)  NULL,
                                                        `89` VARCHAR(100)  NULL,
                                                        `90` VARCHAR(100)  NULL,
                                                        `91` VARCHAR(100)  NULL,
                                                        `92` VARCHAR(100)  NULL,
                                                        `93` VARCHAR(100)  NULL,
                                                        `94` VARCHAR(100)  NULL,
                                                        `95` VARCHAR(100)  NULL,
                                                        `96` VARCHAR(100)  NULL,
                                                        `97` VARCHAR(100)  NULL,
                                                        `98` VARCHAR(100)  NULL,
                                                        `99` VARCHAR(100)  NULL,
                                                        `100` VARCHAR(100)  NULL,
                                                        `101` VARCHAR(100)  NULL,
                                                        `102` VARCHAR(100)  NULL,
                                                        `103` VARCHAR(100)  NULL,
                                                        `104` VARCHAR(100)  NULL,
                                                        `105` VARCHAR(100)  NULL,
                                                        `106` VARCHAR(100)  NULL,
                                                        `107` VARCHAR(100)  NULL,
                                                        `108` VARCHAR(100)  NULL,
                                                        `109` VARCHAR(100)  NULL,
                                                        `UID`  INT(10) NULL AUTO_INCREMENT, UNIQUE INDEX `UID` (`UID`));";
        $dtresult = $this->dbConn->dbQuery($bldsql);

        //******************************************************************************************************************************************************

        $sql = 'LOAD DATA LOCAL INFILE "' . mysqli_real_escape_string($this->dbConn->connection, $f) . '" INTO TABLE xml_smollan_temp
                  FIELDS TERMINATED BY "><"';

        $this->dbConn->processPosting($sql, "");

        $this->dbConn->dbQuery("commit");

        //******************************************************************************************************************************************************
        $fld = 0;


        do {
            $sql = "SELECT if(s." . trim($fld) . " = '/order_file>'," . trim($fld) . ",'X') as 'EOF'
                        FROM xml_smollan_temp s
                        WHERE 1;";

            $eoo = $this->dbConn->dbGetAll($sql);
            $fld++;

        } while ($eoo[0]['EOF'] == 'X');

        $stpLoop = $fld;

        $fld = 0;

        do {
            $dsql = "INSERT INTO xml_upload_temp (xml_upload_temp.FLD1, 
                                                      xml_upload_temp.FLD2)
                         SELECT SUBSTR(xml_smollan_temp." . trim($fld) . ",1,if(POSITION('>' IN xml_smollan_temp." . trim($fld) . ")=0,
                                                                                 20,POSITION('>' IN xml_smollan_temp." . trim($fld) . ")-1)), 
                                SUBSTR(xml_smollan_temp." . trim($fld) . ", POSITION('>'  IN xml_smollan_temp." . trim($fld) . ")+1, 
		                                                       POSITION('<' IN xml_smollan_temp." . trim($fld) . ")-1-
                                                           POSITION('>' IN xml_smollan_temp." . trim($fld) . "))
                         FROM .xml_smollan_temp WHERE 1;";

            $this->dbConn->processPosting($dsql, "");

            $this->dbConn->dbQuery("commit");


            $fld++;

        } while ($fld < $stpLoop);

    }

//******************************************************************************************************************************************************
    public function getWhInvPrefix($fUid, $code)
    {
        $sql = "SELECT sfd.value
              FROM .special_field_details sfd
              WHERE sfd.field_uid = '" . mysqli_real_escape_string($this->dbConn->connection, $fUid) . "'
              AND   sfd.spare = '" . mysqli_real_escape_string($this->dbConn->connection, $code) . "'";

        return $this->dbConn->dbGetAll($sql);
    }

//******************************************************************************************************************************************************
    public function getUppFilesToDelete($fileNme) {
    	
    	  $sql = "SELECT *
                FROM .file_log fl
                WHERE fl.file_name = '" . mysqli_real_escape_string($this->dbConn->connection, $fileNme) . "'
                AND   fl.`status` = 'N';";

        return $this->dbConn->dbGetAll($sql);
    	
    }
    
//  ********************************************************************************************************************************************





}