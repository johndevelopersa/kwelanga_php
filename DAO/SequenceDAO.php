<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once ($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');

class SequenceDAO {
  	var $_dbConn;
  	public $errorTO;

// ***************************************************************************************************************************************************
		function __construct( $dbConn ) {
			$this->_dbConn = $dbConn;
			$this->errorTO = new ErrorTO;
	}
// ***************************************************************************************************************************************************
	function getSequence( $sequenceTO, &$val ) {
		global $ROOT; global $PHPFOLDER;
		// it is necessary to create a temporary connection to be able to commit only this section
		$dbConn = new dbConnect();
        $dbConn->dbConnection();

		$lockFile=$ROOT.$PHPFOLDER."lockfiles/".LOCK_FILENAME_SEQUENCE;

		$fp = fopen($lockFile, "r+");
		// try to acquire the lock
		if (flock($fp, LOCK_EX)) { // do an exclusive lock
			// the table must determine most specific seq to use. Calling function only passes what it knows, except that the calling function MUST KNOW if principal and depot is important !
			$dbConn->dbQuery("SELECT * FROM `sequence_control`
							  WHERE `sequence_key` = '" . $sequenceTO->sequenceKey . "'
							  AND   (principal_uid = '{$sequenceTO->principalUId}' or principal_uid is null)

							  AND   ((FIND_IN_SET('{$sequenceTO->depotUId}',depot_uid)>0) or depot_uid is null)
	

							  AND   ((FIND_IN_SET('{$sequenceTO->documentTypeUId}',document_type_uid)>0) or document_type_uid is null)
							  AND   ((FIND_IN_SET('{$sequenceTO->dataSource}',data_source)>0) or data_source is null)
							  order  by if(principal_uid is not null,1,2), if(depot_uid is not null,1,2), if(document_type_uid is not null,1,2), if(data_source is not null,1,2)");
			// only consider first row
	 		if ($seqline = mysqli_fetch_array($dbConn->dbQueryResult,MYSQLI_ASSOC))  {
	 			
	 			$nextseq = $seqline['sequence_value'];
	 			$uid = $seqline['uid'];
	            $nextseq++;
	            $val = str_pad($nextseq, $sequenceTO->sequenceLen, "0", STR_PAD_LEFT);
	            $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
	            $this->errorTO->description = "Successfully retrieved.";
	  		}
	 		else {
	 			$this->errorTO->type = FLAG_ERRORTO_ERROR;
	            $this->errorTO->description = "Failed to retrieve sequence.";
	 			/*
	 			$dbConn->dbinsQuery ("INSERT INTO `sequence_control` ( `sequence_key`, `sequence_value`) VALUES ( " .
			       		 		 		   "'" . $sequenceTO->sequenceKey                       . "', " .
			       		 		 		   "'" . $sequenceTO->sequenceStart                     . "' ) ") ;

			   	if (!$dbConn->dbQueryResult) {
			   		$this->errorTO->type = FLAG_ERRORTO_ERROR;
		            $this->errorTO->description = "Sequence Error. Could not Insert.";
			   	}
			   	else {
			   		$nextseq = $sequenceTO->sequenceStart;
	            	$nextseq++;
	            	$val = str_pad($nextseq, $sequenceTO->sequenceLen, "0", STR_PAD_LEFT);
	            	$this->errorTO->type = FLAG_ERRORTO_SUCCESS;
		            $this->errorTO->description = "Successfully Inserted.";
			   	}
			   	*/
	    	}

	    	// 2.update the seq
	    	if ($this->errorTO->type == FLAG_ERRORTO_SUCCESS) {
		    	$dbConn->dbinsQuery("UPDATE `sequence_control` set `sequence_value` = " . $nextseq . " " .
	                            "WHERE `uid` = '{$uid}' ") ;

		        if (!$dbConn->dbQueryResult) {
					$this->errorTO->type = FLAG_ERRORTO_ERROR;
		            $this->errorTO->description = "Update of Sequence Failed";
				}
	    	}

		    $dbConn->dbQuery("commit");

    	} else {
	            $this->errorTO->type = FLAG_ERRORTO_ERROR;
		        $this->errorTO->description = "TimeOut occurred, could not obtain lock during sequence request.";
		  }

		flock($fp, LOCK_UN); // unlock, no longer automatic when handle closed
		fclose($fp);
		return $this->errorTO;

   }

   public function getOrdersSequence() {
    	global $ROOT; global $PHPFOLDER;

        include_once($ROOT.$PHPFOLDER."TO/SequenceTO.php");
        $getSequenceResult="";
        $sequenceTO = new SequenceTO();
        $sequenceTO->sequenceKey    = LITERAL_SEQ_ORDER;
        $sequenceTO->sequenceStart  = 0;
        $sequenceTO->sequenceLen    = 6;
        $result = $this->getSequence($sequenceTO, $getSequenceResult);
        // check if valid sequence returned.
        if ($result->type!=FLAG_ERRORTO_SUCCESS) {
                return "";
        }

        return $getSequenceResult;
   }

   // optional params, pass "" if not needed, uses whats registered
   public function getDocumentNumberSequence($documentType, $principalUId, $depotUId, $dataSource) {
    	global $ROOT; global $PHPFOLDER;

    	if ($documentType=="") {
    		return "";
    	}

        include_once($ROOT.$PHPFOLDER."TO/SequenceTO.php");
        $getSequenceResult="";
        $sequenceTO = new SequenceTO();
        $sequenceTO->sequenceKey    = LITERAL_SEQ_DOCUMENT_NUMBER;
        $sequenceTO->sequenceStart  = 0;
        $sequenceTO->sequenceLen    = 7;
        $sequenceTO->documentTypeUId = $documentType;
        $sequenceTO->principalUId = $principalUId;
        $sequenceTO->depotUId = $depotUId;
        $sequenceTO->dataSource = $dataSource;
        $result = $this->getSequence($sequenceTO, $getSequenceResult);
        // check if valid sequence returned.
        if ($result->type!=FLAG_ERRORTO_SUCCESS) {
                return "";
        }

        return $getSequenceResult;
   }



   // optional params, pass "" if not needed, uses whats registered
   public function getAlternateDocumentNumberSequence($documentType, $principalUId, $depotUId, $dataSource) {
    	global $ROOT; global $PHPFOLDER;

    	if ($documentType=="") {
          return "";
    	}

        include_once($ROOT.$PHPFOLDER."TO/SequenceTO.php");
        $getSequenceResult="";
        $sequenceTO = new SequenceTO();
        $sequenceTO->sequenceKey    = LITERAL_SEQ_ALTERNATE_DOCUMENT_NUMBER;
        $sequenceTO->sequenceStart  = 0;
        $sequenceTO->sequenceLen    = 8;
        $sequenceTO->documentTypeUId = $documentType;
        $sequenceTO->principalUId = $principalUId;
        $sequenceTO->depotUId = $depotUId;
        $sequenceTO->dataSource = $dataSource;
        $result = $this->getSequence($sequenceTO, $getSequenceResult);
        // check if valid sequence returned.
        if ($result->type!=FLAG_ERRORTO_SUCCESS) {
                return "";
        }

        return $getSequenceResult;
   }


   public function getStoreOASequence() {
    	global $ROOT; global $PHPFOLDER;

        include_once($ROOT.$PHPFOLDER."TO/SequenceTO.php");
        $getSequenceResult="";
        $sequenceTO = new SequenceTO();
        $sequenceTO->sequenceKey    = LITERAL_SEQ_STORE;
        $sequenceTO->sequenceStart  = 0;
        $sequenceTO->sequenceLen    = 6;
        $result = $this->getSequence($sequenceTO, $getSequenceResult);
        // check if valid sequence returned.
        if ($result->type!=FLAG_ERRORTO_SUCCESS) {
                return "";
        }

        return $getSequenceResult;
   }

   public function getFTPFileExportSequence() {
    	global $ROOT; global $PHPFOLDER;

        include_once($ROOT.$PHPFOLDER."TO/SequenceTO.php");
        $getSequenceResult="";
        $sequenceTO = new SequenceTO();
        $sequenceTO->sequenceKey    = LITERAL_SEQ_EXPORT_FILE;
        $sequenceTO->sequenceStart  = 0;
        $sequenceTO->sequenceLen    = 6;
        $result = $this->getSequence($sequenceTO, $getSequenceResult);
        // check if valid sequence returned.
        if ($result->type!=FLAG_ERRORTO_SUCCESS) {
                return "";
        }

        return $getSequenceResult;
   }

   public function getFTPFileExportSequenceDepot($depotUId) {
    	global $ROOT; global $PHPFOLDER;

        include_once($ROOT.$PHPFOLDER."TO/SequenceTO.php");
        $getSequenceResult="";
        $sequenceTO = new SequenceTO();
        $sequenceTO->sequenceKey    = LITERAL_SEQ_EXPORT_FILE;
        $sequenceTO->sequenceStart  = 0;
        $sequenceTO->sequenceLen    = 6;
        $sequenceTO->depotUId       = $depotUId;
        $result = $this->getSequence($sequenceTO, $getSequenceResult);
        // check if valid sequence returned.
        if ($result->type!=FLAG_ERRORTO_SUCCESS) {
                return "";
        }

        return $getSequenceResult;
   }


// ***************************************************************************************************************************************************
}
?>