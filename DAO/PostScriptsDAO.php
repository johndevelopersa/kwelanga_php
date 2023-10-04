<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');

class PostScriptsDAO {
	private $dbConn;

	function __construct($dbConn) {
       $this->dbConn = $dbConn;
    }

	 function postDuplicateStores( $fPrincipalId, $fChainId, $fDepotId, $tPrincipalId, $tChainId, $tDepotId ) {
    	
    		// unfortunately you cannot have a subquery (not exists) in same sql, so i've opted for the IGNORE keyword to ignore duplicates, relying on the unique index!
    		// EISH ! contrary to my comment above, it tried it with a not exists and it worked regardless against specification ?!?!?!
	       	$sql = "insert into principal_store_master
						(`principal_uid`, `deliver_name`, `deliver_add1`, `deliver_add2`, `deliver_add3`, 
						 `bill_name`, `bill_add1`, `bill_add2`, `bill_add3`, `ean_code`, `vat_number`, `depot_uid`, 
						 `delivery_day_uid`, `no_vat`, `on_hold`, `principal_chain_uid`, `branch_code`, `old_account`, 
						 `store_string`, `captured_by`, `stripped_deliver_name`)
					select {$tPrincipalId}, `deliver_name`, `deliver_add1`, `deliver_add2`, `deliver_add3`, 
						 `bill_name`, `bill_add1`, `bill_add2`, `bill_add3`, `ean_code`, `vat_number`, if ('{$fDepotId}' = '',`depot_uid`,'{$tDepotId}'), 
						 `delivery_day_uid`, `no_vat`, `on_hold`, {$tChainId}, `branch_code`, `old_account`, 
						 `store_string`, `captured_by`, `stripped_deliver_name`
					from   principal_store_master a
					where  principal_uid = '{$fPrincipalId}'
					and    principal_chain_uid = '{$fChainId}'
					and    ((depot_uid = '{$fDepotId}') or ('{$fDepotId}' = ''))
					and    not exists 
							(
								select 1
								from   principal_store_master b
								where  a.stripped_deliver_name = b.stripped_deliver_name
								and    b.principal_uid = '{$tPrincipalId}'
								and    ((depot_uid = '{$fDepotId}') or ('{$fDepotId}' = ''))
							)";
	
			$this->errorTO = $this->dbConn->processPosting($sql,"");
			
			$infoArr=CommonUtils::getMysqlInfo(mysqli_info($this->dbConn->connection));
			$records=$infoArr["records"];
			
			// (do not create special store fields)
			
			if ($this->errorTO->type==FLAG_ERRORTO_SUCCESS) {
				$this->errorTO->description = "{$records} Stores Successfully Created.";
	      	}
	      	else  {
	      		$this->errorTO->type = FLAG_ERRORTO_ERROR;
				$this->errorTO->description = "Failed to create Stores!".$this->errorTO->description.$sql;
	      	}
	      	
      	return $this->errorTO;
	}
	
}
?>
