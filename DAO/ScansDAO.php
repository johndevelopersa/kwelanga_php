<?php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');

class ScansDAO {
	
	private $dbConn;

	function __construct($dbConn) {
       $this->dbConn = $dbConn;
    }


	public function getDocumentScanByDocumentMasterID($documentMasterId) {
		$sql="select 
				s.uid, s.document_type_uid, s.file_size_bytes, s.file_md5_checksum, s.storage_path, 
				s.principal_uid, s.document_no, s.created_datetime
			  from   document_scans s
			  where  s.document_master_uid = ".(int)$documentMasterId;

        return $this->dbConn->dbGetAll($sql);
	}
	
	public function insertDocumentScanEntry($documentTypeUid, $storagePath, $principalUid, $documentNo, $fileSizeBytes, $fileMD5Sum) {
		
		$parsedDocumentNo = str_pad(mysqli_real_escape_string($this->dbConn->connection,$documentNo), 8, "0", STR_PAD_LEFT);
		
		$sql="INSERT IGNORE INTO document_scans (
			document_type_uid, 
			file_size_bytes, 
			file_md5_checksum,
			storage_path, 
			principal_uid, 
			document_no,
			document_master_uid,
			created_datetime
		) VALUES (	
			".(int)$documentTypeUid.",
			".(int)$fileSizeBytes.",			
			'".mysqli_real_escape_string($this->dbConn->connection,$fileMD5Sum)."',
			'".mysqli_real_escape_string($this->dbConn->connection,$storagePath)."',
			".(int)$principalUid.",
			'".$parsedDocumentNo."',
			(SELECT uid FROM document_master WHERE principal_uid=$principalUid AND document_number = '".$parsedDocumentNo."'),
			NOW()
		)";

       $this->errorTO = $this->dbConn->processPosting($sql, '');
       if ($this->errorTO->type==FLAG_ERRORTO_SUCCESS){
             $this->errorTO->description="document scan entry inserted.";
             $this->errorTO->identifier=$this->dbConn->dbGetLastInsertId(); // get the UID just created             
             return $this->errorTO;
       }

      return $this->errorTO;
	}

}
