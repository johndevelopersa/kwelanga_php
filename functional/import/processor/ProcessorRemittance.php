<?php
/*
 * PROCESSES ORDERS INTO ORDERS_HOLDING
 *
  */

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'libs/BroadcastingUtils.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER.'DAO/PostImportDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/PrincipalDAO.php');

// all files should only be committed if entire file is OK
class ProcessorRemittance {
	private $dbConn;
	private $postImportDAO;
  private $principalDAO;

	function __construct($dbConn) {
	      $this->dbConn = $dbConn;
	      $this->postImportDAO = new PostImportDAO($dbConn);
        $this->principalDAO = new PrincipalDAO($this->dbConn);
  }

  // this can be an array or single TO passed eg. PnP WS, or File Import
  // the PnP does not pass the $onlineImportLocation
  function postRemittance($postingRemittanceTOArr, $onlineImportLocation=false) {
  	global $importDAO, $ROOT, $PHPFOLDER;

  	if (!isset($importDAO)) {
  		include_once($ROOT.$PHPFOLDER."DAO/ImportDAO.php");
  		$importDAO = new ImportDAO($this->dbConn);
  	}

  	$eTO = new ErrorTO;

		// make it an array if it isnt already
		if (!is_array($postingRemittanceTOArr)) $arrTO=array($postingRemittanceTOArr);
		else $arrTO=$postingRemittanceTOArr;

		foreach ($arrTO as $postingRemittanceTO) {

    	// try lookup the principal uid if it hasn't been set
    	// CHECKERS already have the principalUId set directly, so will never enter here
			if ($postingRemittanceTO->principalUId=="") {
				$mfP=array();
				if ($postingRemittanceTO->principalGLN!="") {
				  $mfP = $this->principalDAO->getPrincipalVendorsByGln($postingRemittanceTO->principalGLN, $postingRemittanceTO->vendorUId); // can return multiple
				}

				if (sizeof($mfP)==0) {
					$eTO->type = FLAG_ERRORTO_ERROR;
					$eTO->description = "Principal UID could not be set. Invalid Principal / Seller GLN ({$postingRemittanceTO->principalGLN})";
					return $eTO;
				}

				// Only GLN lookups cater for multiple rows
			  $postingRemittanceTO->principalUId = $mfP[0]["uid"];

			  foreach ($postingRemittanceTO->detailArr as &$r) {
			    $r->principalUId = $postingRemittanceTO->principalUId;
			  }
			}


			// everything passed using the list only
			$rTO=$this->postImportDAO->postRemittance($postingRemittanceTO);
			if ($rTO->type!=FLAG_ERRORTO_SUCCESS) {
				$eTO->type = FLAG_ERRORTO_ERROR;
				$eTO->description = "Failed to store Remittance in postRemittance : ".$rTO->description;
        echo $eTO->description;
				return $eTO;
			}

		} // end array TO loop


   		$eTO->type = FLAG_ERRORTO_SUCCESS;
   		$eTO->description = "Successful";

    	return $eTO;
    }

}



?>