<?php
/*  
 * STORE CREDIT LIMITS IMPORT
 * 
 * Updates the credit limit fields on principal_store_master by using store special fields
 * File Structure : XML
 * Sample :
 * see xsd
  */

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER.'TO/PostingTSCLTO.php');
include_once($ROOT.$PHPFOLDER.'DAO/StoreDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/PostStoreDAO.php');

// all files should only be committed if entire file is OK
class ProcessorTSCL {
	private $dbConn;
	private $storeDAO;
	private $postStoreDAO;
	
	function __construct($dbConn) {
	      $this->dbConn = $dbConn;
	      $this->storeDAO = new storeDAO($dbConn);
	      $this->postStoreDAO = new PostStoreDAO($dbConn);
    }
    
    function postTSCL($arrPostingTSCLTO, $onlineFileProcessItem) {
    	$eTO = new ErrorTO;

		foreach ($arrPostingTSCLTO as &$TO) {
			if (($TO->principalStoreUId=="") && ($TO->principalStoreUIdList=="")) {
				// use store special fields
				if ($TO->specialStoreFieldIdForLookup!="") {
					$arr=$this->storeDAO->getPrincipalStoreBySF($onlineFileProcessItem["principal_uid"],$TO->specialStoreFieldIdForLookup, $TO->specialStoreFieldValue, $TO->vendorId);
					if (sizeof($arr)==0) continue;
					$tempArr=array();
					foreach ($arr as $row) {
						$tempArr[]=$row["uid"];
					}
					$TO->principalStoreUIdList = implode(",",$tempArr); // can be multiple
				}
			} else if ($TO->principalStoreUId!="") $TO->principalStoreUIdList=$TO->principalStoreUId; // NB: if list not blank, it will be overwritten. Adaptor must ONLY load one or the other 
			
			// everything passed using the list only
			$rTO=$this->postStoreDAO->updateCreditLimits($TO->principalId, $TO->principalStoreUIdList, $TO->creditLimit, $TO->creditBalance);
			if ($rTO->type!=FLAG_ERRORTO_SUCCESS) {
				$eTO->type = FLAG_ERRORTO_ERROR;
   				$eTO->description = "Failed to update Credit Limits for account {$TO->specialStoreFieldValue} : ".$rTO->description;
   				if ((preg_match("/ExceptionThrower/i",$eTO->description)) || (preg_match("/processPosting/i",$eTO->description))) $eTO->identifier = ET_SYSTEM;
   				else $eTO->identifier = ET_CUSTOMER; // for the time being, just assume all errors are on the customer side other than fallovers
   				return $eTO;
			}
		}
    	
   		$eTO->type = FLAG_ERRORTO_SUCCESS;
   		$eTO->description = "Successful";
   		
    	return $eTO;
    }
	
}
  


?>