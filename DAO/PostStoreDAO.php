<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'properties/dbSettings.inc');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER."libs/CommonUtils.php");
include_once($ROOT.$PHPFOLDER."libs/GUICommonUtils.php");
include_once($ROOT.$PHPFOLDER."libs/ValidationCommonUtils.php");
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');
include_once($ROOT.$PHPFOLDER.'DAO/ChainDAO.php');
include_once($ROOT.$PHPFOLDER."DAO/SequenceDAO.php");

/*
 * NOTE : The validation should return IMMEDIATELY if an error type occurred (required for the way screen is processed)
 */

class PostStoreDAO {
	public $errorTO;
	private $dbConn;

	function __construct($dbConn) {
		$this->dbConn = $dbConn;
		$this->errorTO = new ErrorTO;
    }

    /*
     *
     * GLOBAL STORE UPDATE
     *
     */

    public function postGlobalStoreValidation($postingStoreTO) {
    	global $ROOT; global $PHPFOLDER;
    	include_once($ROOT.$PHPFOLDER."DAO/AdministrationDAO.php");
    	$administrationDAO = new AdministrationDAO($this->dbConn);

    	// get user. don't pass it because this is more secure.
    	if (!isset($_SESSION)) session_start();
    	$userId = $_SESSION['user_id'];
    	$principalId = $_SESSION['principal_id']; // used for hasRole validation

    	if ($postingStoreTO->DMLType!="INSERT") return false;

		if ($postingStoreTO->DMLType=="INSERT") {
			// check roles
			$hasRole = $administrationDAO->hasRole($userId,$principalId,ROLE_ADD_STORE);
			if(!$hasRole) {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="You do not have permissions to Add Global Stores";
				return false;
			};

			// check store name is unique.
			$after=CommonUtils::getStrippedValue($postingStoreTO->deliverName);
			$sql="
				select deliver_name
				from   global_store_master
				where  stripped_deliver_name = '".$after."'";

		    $this->dbConn->dbinsQuery($sql);
			if ($this->dbConn->dbQueryResultRows > 0) {
				$list="";
				while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
					$list.=$row['deliver_name']."<BR>";
				}
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="A Global Store Name already exists that is too similar to the new store.<BR><BR>".$list;
				return false;
			}


		}

		return true;

    }

    function postGlobalStore( $postingStoreTO ) {

    	if (strval($postingStoreTO->noVAT) == 'Y') {
  			    $novat = '1';
  		} elseif (strval($postingStoreTO->noVAT) == 'N') {
  		        $novat = '0';
  		  } else $novat = $postingStoreTO->noVAT;

  		if ( strval($postingStoreTO->onHold) == 'Y') {
  			    $onhold = '1';
  		} elseif ( strval($postingStoreTO->onHold) == 'N') {
  		        $onhold = '0';
  		  } else $onhold = $postingStoreTO->onHold;


    	$resultOK = $this->postGlobalStoreValidation($postingStoreTO);
    	if ($resultOK) {

			if ($postingStoreTO->DMLType=="INSERT") {
				$strippedDeliverName = CommonUtils::getStrippedValue($postingStoreTO->deliverName);
		       	$sql = "INSERT INTO  `global_store_master` ( `deliver_name`, `deliver_add1`, `deliver_add2`, `deliver_add3`,      ".
			              	         "`bill_name`, `bill_add1`, `bill_add2`, `bill_add3`, `ean_code`, `vat_number`, `no_vat`, `on_hold`, `chain_uid`,  ".
			              	         "`branch_code`, `old_account`, stripped_deliver_name) VALUES ( " .
				       		 		 "'" . addSlashes( $postingStoreTO->deliverName )   . "', ".
				            		 "'" . addSlashes( $postingStoreTO->deliverAdd1 )   . "', ".
				            		 "'" . addSlashes( $postingStoreTO->deliverAdd2 )   . "', ".
				            		 "'" . addSlashes( $postingStoreTO->deliverAdd3 )   . "', ".
				            		 "'" . addSlashes( $postingStoreTO->billName )      . "', ".
				             	 	 "'" . addSlashes( $postingStoreTO->billAdd1 )      . "', ".
				             		 "'" . addSlashes( $postingStoreTO->billAdd2 )      . "', ".
				             		 "'" . addSlashes( $postingStoreTO->billAdd2 )      . "', ".
				             		 "'" . addSlashes( $postingStoreTO->eanCode )       . "', ".
				            		 "'" . addSlashes( $postingStoreTO->vatNumber )     . "', ".
				               		 "'" . $novat                       				. "', ".
		  							 "'" . $onhold					                    . "', ".
									 "'" . $postingStoreTO->chain                       . "', ".
									 "'" . $postingStoreTO->branchCode                  . "', ".
									 "'" . $postingStoreTO->oldAccount                  . "', ".
									 "'".$strippedDeliverName."' )";
			}

			$this->errorTO = $this->dbConn->processPosting($sql,$postingStoreTO->deliverName);

			if ($this->errorTO->type==FLAG_ERRORTO_SUCCESS) {
				$this->errorTO->description = "Global Store Successfully Created.";
	      	}
	      	else  {
	      		$this->errorTO->type = FLAG_ERRORTO_ERROR;
				$this->errorTO->description = "Failed to create Global Store.".mysqli_error($this->dbConn->connection);
	      	}

    	} else {
    		return $this->errorTO;
    	  }

      	return $this->errorTO;
	}

	/*
	 *
	 * PRINCIPAL STORE UPDATE
	 *
	 */

	 public function postPrincipalStoreValidation($postingStoreTO) {
    	global $ROOT; global $PHPFOLDER;
    	include_once($ROOT.$PHPFOLDER."DAO/AdministrationDAO.php");
    	include_once($ROOT.$PHPFOLDER."DAO/StoreDAO.php");
    	include_once($ROOT.$PHPFOLDER."DAO/PrincipalDAO.php");
    	$administrationDAO = new AdministrationDAO($this->dbConn);
    	$principalDAO = new PrincipalDAO($this->dbConn);
    	$storeDAO = new StoreDAO($this->dbConn);

    	// get user. don't pass it because this is more secure.
    	if (!isset($_SESSION)) session_start();
    	$userId = $_SESSION['user_id'];
    	$principalId = $_SESSION['principal_id']; // used for hasRole validation

    	$hasSURole = $administrationDAO->hasRoleSuperUser($userId,$principalId);

    	if (!ValidationCommonUtils::checkPostingType($postingStoreTO->DMLType)) return false;

		// check if noVAT is valid
		// First Convert to boolean if Y/N
		$postingStoreTO->noVAT=ValidationCommonUtils::translateYNtoBoolean($postingStoreTO->noVAT);
		if ($postingStoreTO->noVAT=="N") $postingStoreTO->noVAT=0;
		if(!ValidationCommonUtils::checkFieldBooleanSimple($postingStoreTO->noVAT)) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Invalid \"No VAT\" value.";
			return false;
		};
		// check if onHold is valid
		// First Convert to boolean if Y/N
		$postingStoreTO->onHold=ValidationCommonUtils::translateYNtoBoolean($postingStoreTO->onHold);
		if(!ValidationCommonUtils::checkFieldBooleanSimple($postingStoreTO->onHold)) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Invalid 'On Hold' value.";
			return false;
		};

		if(!ValidationCommonUtils::checkFieldYesNoSimple($postingStoreTO->exportNumberEnabled)) {
		  $this->errorTO->type=FLAG_ERRORTO_ERROR;
		  $this->errorTO->description="Invalid Export Number Enabled value.";
		  return false;
		};

		// check if valid delivery day. skip check if Unknown is selected
		if (($postingStoreTO->deliveryDay != "8") && ($postingStoreTO->deliveryDay != "")) {
			if(!preg_match("/^[1-7]$/",$postingStoreTO->deliveryDay)) {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="Invalid Delivery Day value.";
				return false;
			}
			$postingStoreTO->deliveryDayDescription = GUICommonUtils::translateDeliveryDay($postingStoreTO->deliveryDay);
		}

		// check status
		if(!ValidationCommonUtils::checkStatus($postingStoreTO->status)) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Invalid 'Status' value.";
			return false;
		};

		if ($userId!=SESSION_ADMIN_USERID) {
			// check if principal-depot is valid
			if (intval($postingStoreTO->depot)==0) {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="Depot must be specified.";
				return false;
			}

			$mfPD = $principalDAO->getUserPrincipalDepotItem($userId,$postingStoreTO->principal,$postingStoreTO->depot);
			if(sizeof($mfPD)==0) {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="User does not have access to this principal-depot, or principal-depot not found.";
				return false;
			};
		} // we could theoretically check if depot is valid regardless, if supplied (not system)

		// check if chain is valid
		if ($userId!=SESSION_ADMIN_USERID) {
			$mfC = $storeDAO->getUserPrincipalChainItem($userId,$postingStoreTO->chain);
			if(sizeof($mfC)==0) {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="User does not have access to this principal-chain, or principal-chain not found.";
				return false;
			};
		} else {
			// allow blank chain only if system created, otherwise if supplied must be validated
			if (($userId!=SESSION_ADMIN_USERID) || ($postingStoreTO->chain!="")) {
				$mfC = $storeDAO->getPrincipalChainItem($postingStoreTO->chain);
				if(sizeof($mfC)==0) {
					$this->errorTO->type=FLAG_ERRORTO_ERROR;
					$this->errorTO->description="Principal-chain not found - ".$postingStoreTO->chain." ".$postingStoreTO->DMLType;
					return false;
				};
			}
		}

		$mfAltC = $storeDAO->getUserPrincipalChainItem($userId,$postingStoreTO->altPrincipalChainUId );
		if (sizeof ( $mfAltC ) == 0) {

			$chainDAO = new ChainDAO ( $this->dbConn );
			$oldGenericChain = $chainDAO->getPrincipalChainByOldCode ( $postingStoreTO->principal, CHAIN_GENERIC_OLD_CODE );

			if (isset ( $oldGenericChain [0] ['uid'] )) {
				$postingStoreTO->altPrincipalChainUId = $oldGenericChain [0] ['uid'];
			}
		}

		//If email field NOT empty = VALIDATE EMAIL
		if(($postingStoreTO->emailAdd != '') && (filter_var($postingStoreTO->emailAdd, FILTER_VALIDATE_EMAIL) === False)){
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Store Email Address is invalid.";
			return false;
		}

		// check the credit fields
		if(($postingStoreTO->ledgerBalance!="") && (!preg_match(GUI_PHP_FLOAT_REGEX,$postingStoreTO->ledgerBalance))) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Invalid Credit Balance. Must be numeric.";
			return false;
		};
		if(($postingStoreTO->ledgerCreditLimit!="") && (!preg_match(GUI_PHP_FLOAT_REGEX,$postingStoreTO->ledgerCreditLimit))) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Invalid Credit Limit. Must be numeric.";
			return false;
		};
		
		if(($postingStoreTO->disval!="") && (!preg_match(GUI_PHP_FLOAT_REGEX,$postingStoreTO->disval))) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Invoice Discount. Must be numeric.";
			return false;
		};		
		
		if($postingStoreTO->disval > 100 ) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Discount Percentage can not be greater than 100.";
			return false;
		};				
	


		if ($postingStoreTO->DMLType=="INSERT") {
			// check roles
			$hasRole = $administrationDAO->hasRole($userId,$principalId,ROLE_ADD_STORE);
			if(!$hasRole) {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="You do not have permissions to Add Stores";
				return false;
			};

			// check sales agents passed for user permissions
			// NOTE: This is only validated here, and is NOT inserted, it is inserted by submit form rather after passing validation
			if ($postingStoreTO->allocatePermissionsUserList!="") {
				$mfSA = $administrationDAO->getSalesAgentsForPrincipal($principalId);
				$saArr=explode(",",$postingStoreTO->allocatePermissionsUserList);
				foreach ($saArr as $sa) {
					$found=false;
					foreach ($mfSA as $v) {
						$dptList=explode(",",$v["depot_list"]);
						$chList=explode(",",$v["chain_list"]);
						if (($sa==$v["user_uid"]) && (in_array($postingStoreTO->depot,$dptList)) && (in_array($postingStoreTO->chain,$chList))) {
							$found=true;
							break;
						}
					}
					if (!$found) {
						$this->errorTO->type=FLAG_ERRORTO_ERROR;
						$this->errorTO->description="A Sales Agent ({$sa}) was selected that is either not a sales agent, or is not a sales agent for the chosen principal depot / chain";
						return false;
					}
				}
			}

		} else if ($postingStoreTO->DMLType=="UPDATE") {
			// check roles
			$hasRole = $administrationDAO->hasRole($userId,$principalId,ROLE_MODIFY_STORE_DETAILS);
			if(!$hasRole) {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="You do not have permissions to Modify Store Details";
				return false;
			};

			if ($userId==SESSION_ADMIN_USERID) {
				$mfPS = $storeDAO->getPrincipalStoreExclChainItem($postingStoreTO->principalStoreUId);
				/*
				if($mfPS[0]["status"]!=FLAG_STATUS_ACTIVE) {
					$this->errorTO->type=FLAG_ERRORTO_ERROR;
					$this->errorTO->description="Store is not active.";
					return false;
				};
				*/
			} else {
				$mfPS = $storeDAO->getPrincipalStoreItem($postingStoreTO->principalStoreUId);
				// updates of credit fields from backend are discarded, so only validate here
				// check the credit fields for changes permissions
				if (($mfPS[0]['ledger_balance']!=$postingStoreTO->ledgerBalance) || ($mfPS[0]['ledger_credit_limit']!=$postingStoreTO->ledgerCreditLimit)) {
					if (!$hasSURole) {
						$this->errorTO->type=FLAG_ERRORTO_ERROR;
						$this->errorTO->description="Only Super Users are permitted to change the credit fields.";
						return false;
					}
				};
			}

			// check if store exists
			if(sizeof($mfPS)==0) {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="The Principal Store could not be found for editing.".$postingStoreTO->principalStoreUId;
				return false;
			};
			// check principal is same
			if($mfPS[0]['principal_uid']!=$postingStoreTO->principal) {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="The Principal Store principal differs from the principal passed.";
				return false;
			};

		  }

		  // check store name is unique. old account is taken out due to automatic next seq generation
		// stores coming from backend can create duplicate store names
		// if update, only do check if theres been a change to deliver name
		if (
			($userId!=SESSION_ADMIN_USERID) &&
			(
				($postingStoreTO->DMLType=="INSERT") ||
				(
					($postingStoreTO->DMLType=="UPDATE") &&
					($mfPS[0]['store_name']!=$postingStoreTO->deliverName)
				)
			)
		) {
			$after=CommonUtils::getStrippedValue($postingStoreTO->deliverName);
			$sql="
				select deliver_name, status
				from principal_store_master
				where 	 principal_uid = ".$postingStoreTO->principal."
				and      stripped_deliver_name = '".$after."'
				and      uid != '{$postingStoreTO->principalStoreUId}'
				and      vendor_created_by_uid is null";

		    $this->dbConn->dbinsQuery($sql);
			if ($this->dbConn->dbQueryResultRows > 0) {
				$list="";
				while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
					$list.=$row['deliver_name'].", ".(GUICommonUtils::translateStatus($row["status"]))."<BR>";
				}
				$this->errorTO->type = FLAG_ERRORTO_ERROR;
				$this->errorTO->description = "A Principal Store Name ({$postingStoreTO->deliverName},{$postingStoreTO->principalStoreUId},{$after}) already exists that is too similar to the new store. If the existing store is marked as Deleted, it must be unflagged.<BR><BR>".$list;
				return false;
			}
		}

    // do not allow changing of system stores - $mfPS is only set if UPDATE
    if (($postingStoreTO->DMLType=="UPDATE") && (($postingStoreTO->oldAccount==VAL_UNKNOWN_STORE_OLD_ACCOUNT) || ($postingStoreTO->oldAccount==VAL_PSM_OLD_ACCOUNT_PREFIX.$mfPS[0]['depot_uid']))) {
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description = "Updating of a system store is not allowed.";
      return false;
    }

		// allocate old_account if empty
		if ($postingStoreTO->oldAccount=="") {
			$sequenceDAO = new SequenceDAO(null);

			$postingStoreTO->oldAccount=$sequenceDAO->getStoreOASequence();
			if ($postingStoreTO->oldAccount=="") {
				$this->errorTO->type = FLAG_ERRORTO_ERROR;
				$this->errorTO->description = "Failed to allocate Store Old Account Sequence";
				return false;
			}
		}

		if ($postingStoreTO->areaUId!="") {
			$mfA=$storeDAO->getPrincipalAreaItem($postingStoreTO->areaUId);
			if (sizeof($mfA)==0) {
			  $this->errorTO->type = FLAG_ERRORTO_ERROR;
				$this->errorTO->description = "Invalid Area UId.";
				return false;
			}
			$postingStoreTO->areaDescription=$mfA[0]["description"];
		}

    if ($postingStoreTO->retailer!="") {
      if (!preg_match(GUI_PHP_INTEGER_REGEX,$postingStoreTO->retailer)) {
        $this->errorTO->type = FLAG_ERRORTO_ERROR;
        $this->errorTO->description = "Invalid Retailer / Group";
        return false;
      }
    }

                //EPOD
                if(!in_array($postingStoreTO->epodStoreFlag, array('N','Y'))){
                  $this->errorTO->type = FLAG_ERRORTO_ERROR;
                  $this->errorTO->description = "Invalid EPOD setting!";
                  return false;
                }

                if($postingStoreTO->epodStoreFlag == 'Y'){

                  //RSA ID
                  if(!is_numeric($postingStoreTO->epodRsaId)){
                    $this->errorTO->type = FLAG_ERRORTO_ERROR;
                    $this->errorTO->description = "EPOD RSA Id number is invalid - must be numeric!";
                    return false;
                  }
                  if(strlen($postingStoreTO->epodRsaId)!=13){
                    $this->errorTO->type = FLAG_ERRORTO_ERROR;
                    $this->errorTO->description = "EPOD RSA Id number is invalid - must be 13 characters!";
                    return false;
                  }

                  //CELLPHONE
                  if(!is_numeric($postingStoreTO->epodCellphoneNumber)){
                    $this->errorTO->type = FLAG_ERRORTO_ERROR;
                    $this->errorTO->description = "EPOD Cellphine number is invalid -  must be numeric!";
                    return false;
                  }
                  if(strlen($postingStoreTO->epodCellphoneNumber)!=10){
                    $this->errorTO->type = FLAG_ERRORTO_ERROR;
                    $this->errorTO->description = "EPOD Cellphine number is invalid - must be 10 characters!";
                    return false;
                  }
                }

		return true;
    }


	 function postPrincipalStore( $postingStoreTO ) {

  		$storeStr = md5($postingStoreTO->principal . "|" . addSlashes( $postingStoreTO->deliverName )) ;
  		if (!isset($_SESSION)) session_start();
			$userId = $_SESSION['user_id'];


   		if (strval($postingStoreTO->noVAT) == 'Y') {
  			    $novat = '1';
  		} elseif (strval($postingStoreTO->noVAT) == 'N') {
  		        $novat = '0';
  		  } else $novat =  mysqli_real_escape_string($this->dbConn->connection, $postingStoreTO->noVAT);

  		if ( strval($postingStoreTO->onHold) == 'Y') {
  			    $onhold = '1';
  		} elseif ( strval($postingStoreTO->onHold) == 'N') {
  		        $onhold = '0';
  		} else  $onhold =  mysqli_real_escape_string($this->dbConn->connection, $postingStoreTO->onHold);

  		if ( $postingStoreTO->localCountry == 'Y') {
  			    $locC = "Local";
  		} elseif ( $postingStoreTO->localCountry == 'N') {
  		        $locC = "Country" ;
  		}

  		$lB = ($postingStoreTO->ledgerBalance=="") ? "NULL" : mysqli_real_escape_string($this->dbConn->connection, $postingStoreTO->ledgerBalance);
  		$lCL  =($postingStoreTO->ledgerCreditLimit=="") ? "NULL" : mysqli_real_escape_string($this->dbConn->connection, $postingStoreTO->ledgerCreditLimit);
  		$lOB = ($postingStoreTO->ownedBy=="") ? "NULL" : mysqli_real_escape_string($this->dbConn->connection, $postingStoreTO->ownedBy);
  		$lVCB = ($postingStoreTO->vendorCreatedByUId=="") ? "NULL" : mysqli_real_escape_string($this->dbConn->connection, $postingStoreTO->vendorCreatedByUId);
  		$TEL1 = ($postingStoreTO->telNo1 == "") ? ("NULL") : ("'" . mysqli_real_escape_string($this->dbConn->connection, $postingStoreTO->telNo1) . "'");
  		$TEL2 = ($postingStoreTO->telNo2 == "") ? ("NULL") : ("'" . mysqli_real_escape_string($this->dbConn->connection, $postingStoreTO->telNo2) . "'");
  		$EMAIL = ($postingStoreTO->emailAdd == "") ? ("NULL") : ("'" . mysqli_real_escape_string($this->dbConn->connection, $postingStoreTO->emailAdd) . "'");


  		$resultOK = $this->postPrincipalStoreValidation($postingStoreTO);

    	if ($resultOK) {
			$this->dbConn->dbQuery("SET time_zone='+0:00'"); // important to make sure PHPBACKEND which sits in RSA synchs changes updated according to SA time (unsynched table is in RSA)
			$strippedDeliverName = CommonUtils::getStrippedValue($postingStoreTO->deliverName);

			//Check if not empty
			$altPrinChain = (empty($postingStoreTO->altPrincipalChainUId))? ('NULL') : (mysqli_real_escape_string($this->dbConn->connection, $postingStoreTO->altPrincipalChainUId));
   // file_put_contents('C:/inetpub/wwwroot/systems/kwelanga_system/kwelanga_php/log/store.txt', print_r($postingStoreTO), TRUE, FILE_APPEND);

			if ($postingStoreTO->DMLType=="INSERT") {
				
		       	$sql = "INSERT INTO  `principal_store_master` ( `principal_uid`, `deliver_name`, `deliver_add1`, `deliver_add2`, `deliver_add3`,
			              	         `bill_name`, `bill_add1`, `bill_add2`, `bill_add3`, `tel_no1`, `tel_no2`, `email_add`, `ean_code`, `vat_number`,
                                                 `depot_uid`, `delivery_day_uid`, `no_vat`, `on_hold`, `principal_chain_uid`, `alt_principal_chain_uid`,
                                                 `branch_code`, `old_account`, `store_string`, `captured_by`, stripped_deliver_name, ledger_balance,
                                                  ledger_credit_limit, status, last_updated, last_change_by_userid,  owned_by, vendor_created_by_uid,
                                                  area_uid, retailer,`bank_details_to_print` , `q_r_code_to_print`,`epod_store_flag`,  `epod_rsa_id`, `epod_cellphone_number`, vat_excl_authorised_by,
                                                  `principal_sales_representative_uid`, export_number_enabled, vat_number_2, off_invoice_discount, warehouse_link, local_country, auto_mail_invoice, no_prices_on_invoice
                                                  )
                                                  VALUES
                                                  ( '" .
				       		 		 mysqli_real_escape_string($this->dbConn->connection, $postingStoreTO->principal)  . "', " .
				       		 		 "'" . mysqli_real_escape_string($this->dbConn->connection, $postingStoreTO->deliverName). "', ".
				            	 "'" . mysqli_real_escape_string($this->dbConn->connection, substr($postingStoreTO->deliverAdd1,0,60)). "', ".
				            	 "'" . mysqli_real_escape_string($this->dbConn->connection, substr($postingStoreTO->deliverAdd2,0,60)). "', ".
				            	 "'" . mysqli_real_escape_string($this->dbConn->connection, substr($postingStoreTO->deliverAdd3,0,60)). "', ".
				            	 "'" . mysqli_real_escape_string($this->dbConn->connection, $postingStoreTO->billName). "', ".
				             	 "'" . mysqli_real_escape_string($this->dbConn->connection, $postingStoreTO->billAdd1). "', ".
				             	 "'" . mysqli_real_escape_string($this->dbConn->connection, $postingStoreTO->billAdd2). "', ".
				             	 "'" . mysqli_real_escape_string($this->dbConn->connection, $postingStoreTO->billAdd3). "', ".
				       		 		 $TEL1 . ", ".
				       		 		 $TEL2 . ", ".
				       		 		 $EMAIL . ", ".
				             	 "'" . mysqli_real_escape_string($this->dbConn->connection, $postingStoreTO->eanCode). "', ".
				            	 "'" . mysqli_real_escape_string($this->dbConn->connection, $postingStoreTO->vatNumber). "', ".
				             	 ((trim($postingStoreTO->depot)=="")?"NULL":$postingStoreTO->depot).", ".
				             	 "'" . mysqli_real_escape_string($this->dbConn->connection, $postingStoreTO->deliveryDay). "', ".
				             	 "'" . $novat. "', ".
		  							   "'" . $onhold. "', ".
		  							   (($postingStoreTO->chain=="")?"NULL":mysqli_real_escape_string($this->dbConn->connection, $postingStoreTO->chain)).", ".
				       		 		 ""  . $altPrinChain. ", ".
				       		 		 "'" . mysqli_real_escape_string($this->dbConn->connection, $postingStoreTO->branchCode). "', ".
    									 "'" . mysqli_real_escape_string($this->dbConn->connection, $postingStoreTO->oldAccount). "', ".
    									 "'" . mysqli_real_escape_string($this->dbConn->connection, $storeStr). "', ".
    									 "'" . mysqli_real_escape_string($this->dbConn->connection, $userId). "', ".
    									 "'" . mysqli_real_escape_string($this->dbConn->connection, $strippedDeliverName)."',".
									     $lB.",
    									 " . $lCL.",
    									 '" . mysqli_real_escape_string($this->dbConn->connection, $postingStoreTO->status)."',
    									 now(),
    									 '" . $userId . "',
    									 " . $lOB . ",
    									 " . $lVCB . ",".
    									 (($postingStoreTO->areaUId=="")?"NULL":mysqli_real_escape_string($this->dbConn->connection, $postingStoreTO->areaUId)). "," .
                       (($postingStoreTO->retailer=="")?"NULL":mysqli_real_escape_string($this->dbConn->connection, $postingStoreTO->retailer)). "," .                       
                       (($postingStoreTO->baccount=="")?"1":mysqli_real_escape_string($this->dbConn->connection, $postingStoreTO->baccount)). ",'" .
                       (($postingStoreTO->qrcode =="")?"NULL":mysqli_real_escape_string($this->dbConn->connection, $postingStoreTO->qrcode)). "'," .                       
                       "'" . mysqli_real_escape_string($this->dbConn->connection, $postingStoreTO->epodStoreFlag) . "'," .
                       "'" . mysqli_real_escape_string($this->dbConn->connection, $postingStoreTO->epodRsaId) . "'," .
                       "'" . mysqli_real_escape_string($this->dbConn->connection, $postingStoreTO->epodCellphoneNumber) . "'," .
                       ((($novat=="1") && ($postingStoreTO->vatExclAuthorisedByFlag=="Y"))?$userId:"NULL")."," .
                       "" . mysqli_real_escape_string($this->dbConn->connection, $postingStoreTO->principalSalesRepresentativeUId) . "," .
                       "'{$postingStoreTO->exportNumberEnabled}'," .
                       "'" . mysqli_real_escape_string($this->dbConn->connection, $postingStoreTO->vatNumber2). "',
                       "   . (($postingStoreTO->disval=="")?"NULL":mysqli_real_escape_string($this->dbConn->connection, $postingStoreTO->disval))    . ",
                       '"  . mysqli_real_escape_string($this->dbConn->connection, $postingStoreTO->wlink) . "',                      
                       '" . mysqli_real_escape_string($this->dbConn->connection, $locC). "',
                       '" . mysqli_real_escape_string($this->dbConn->connection, $postingStoreTO->autoMailInvoice). "',
                       'N')";
                       
                         

	  		} else if ($postingStoreTO->DMLType=="UPDATE") {

 

	  			// when updating the store, be careful of the data coming from backend. It must NOT overwrite critical fields. At the moment The Update is not even called from backend.
	  			// - onhold
	  			// - novat
	  			// - stripped deliver name
	  			// - deliver name
	  			// - ledger credit limit / balance ! front end only
  				$limitedFields=($userId!=SESSION_ADMIN_USERID)?" ledger_balance=" . mysqli_real_escape_string($this->dbConn->connection, $lB) . ",ledger_credit_limit=" . mysqli_real_escape_string($this->dbConn->connection, $lCL).", ":"";
          $vatExclAuthorisedBy=((($novat=="1") && ($postingStoreTO->vatExclAuthorisedByFlag=="Y"))?$userId:"NULL"); // only overwrite with new ID if not already set
	  			$sql = "UPDATE  `principal_store_master` SET
								deliver_name		   = '" . mysqli_real_escape_string($this->dbConn->connection,  $postingStoreTO->deliverName ) . "',
								stripped_deliver_name  = '" . $strippedDeliverName . "',
 							    `deliver_add1`         = '" . mysqli_real_escape_string($this->dbConn->connection,  $postingStoreTO->deliverAdd1 )   . "', ".
 							    "`deliver_add2`        = '" . mysqli_real_escape_string($this->dbConn->connection,  $postingStoreTO->deliverAdd2 )   . "', ".
 							    "`deliver_add3`        = '" . mysqli_real_escape_string($this->dbConn->connection,  $postingStoreTO->deliverAdd3 )   . "', ".
 							    "`bill_name`           = '" . mysqli_real_escape_string($this->dbConn->connection,  $postingStoreTO->billName )      . "', ".
 							    "`bill_add1`           = '" . mysqli_real_escape_string($this->dbConn->connection,  $postingStoreTO->billAdd1 )      . "', ".
 							    "`bill_add2`           = '" . mysqli_real_escape_string($this->dbConn->connection,  $postingStoreTO->billAdd2 )      . "', ".
 							    "`bill_add3`           = '" . mysqli_real_escape_string($this->dbConn->connection,  $postingStoreTO->billAdd3 )      . "', ".
 							    "`tel_no1`             = " . $TEL1 . ", ".
	  							"`tel_no2`             = " . $TEL2 . ", ".
	  							"`email_add`           = " . $EMAIL . ", ".
 							    "`ean_code`            = '" . mysqli_real_escape_string($this->dbConn->connection,  $postingStoreTO->eanCode ) . "', ".
 							    "`vat_number`          = '" . mysqli_real_escape_string($this->dbConn->connection,  $postingStoreTO->vatNumber). "', ".
 							    "`vat_number_2`        = '" . mysqli_real_escape_string($this->dbConn->connection,  $postingStoreTO->vatNumber2). "', ".
 							    "`depot_uid`           = '" . mysqli_real_escape_string($this->dbConn->connection,  $postingStoreTO->depot ) . "', ".
 							    "`delivery_day_uid`    = '" . mysqli_real_escape_string($this->dbConn->connection,  $postingStoreTO->deliveryDay ) . "', ".
 							    "`no_vat`              = '" . $novat ."', ".
 							    "`on_hold`             = '" . $onhold  . "', ".
 							    "`principal_chain_uid` = '" . mysqli_real_escape_string($this->dbConn->connection,  $postingStoreTO->chain ) . "', ".
                  "`alt_principal_chain_uid` = " . $altPrinChain . ", ".
                  "`principal_sales_representative_uid` = '" . mysqli_real_escape_string($this->dbConn->connection,  $postingStoreTO->principalSalesRepresentativeUId) . "', ".
 							    "`branch_code`         = '" . mysqli_real_escape_string($this->dbConn->connection,  $postingStoreTO->branchCode) . "',".
									"`area_uid`            = " . (($postingStoreTO->areaUId=="")?"NULL":mysqli_real_escape_string($this->dbConn->connection, $postingStoreTO->areaUId)) . ",".
                  "`retailer`            = " . (($postingStoreTO->retailer=="")?"NULL":mysqli_real_escape_string($this->dbConn->connection, $postingStoreTO->retailer)) . ",".
                  "`bank_details_to_print` = " . (($postingStoreTO->baccount=="")?"1":mysqli_real_escape_string($this->dbConn->connection, $postingStoreTO->baccount)) . ",".
                  "`q_r_code_to_print` = '" . (($postingStoreTO->qrcode=="")?"NULL":mysqli_real_escape_string($this->dbConn->connection, $postingStoreTO->qrcode)) . "',".
                  "`vat_excl_authorised_by` = if(isnull({$vatExclAuthorisedBy}) || (!isnull({$vatExclAuthorisedBy}) && isnull(vat_excl_authorised_by)),{$vatExclAuthorisedBy},vat_excl_authorised_by),
                  {$limitedFields}
                  status = '" . mysqli_real_escape_string($this->dbConn->connection,  $postingStoreTO->status) . "',
                  last_updated = now(),
                  last_change_by_userid = '" . $userId . "',
								 owned_by = " . $lOB . ",
								 vendor_created_by_uid = " . $lVCB . ",
                 `epod_store_flag` = '" . mysqli_real_escape_string($this->dbConn->connection, $postingStoreTO->epodStoreFlag) . "',
                 `epod_rsa_id` = '" . mysqli_real_escape_string($this->dbConn->connection, $postingStoreTO->epodRsaId) . "',
                 `epod_cellphone_number` = '" . mysqli_real_escape_string($this->dbConn->connection, $postingStoreTO->epodCellphoneNumber) . "',
                 `off_invoice_discount`  = " . (($postingStoreTO->disval=="")?"NULL":mysqli_real_escape_string($this->dbConn->connection, $postingStoreTO->disval)) . ",
                 `local_Country` = '" . mysqli_real_escape_string($this->dbConn->connection, $locC) . "',
                 `warehouse_link` = '" . mysqli_real_escape_string($this->dbConn->connection, $postingStoreTO->wlink) . "',
                 `export_number_enabled` = '{$postingStoreTO->exportNumberEnabled}',
                 `auto_mail_invoice` = '{$postingStoreTO->autoMailInvoice}',
                 `no_prices_on_invoice` = '{$postingStoreTO->noPricesOnInvoice}'
          WHERE `uid`  = '" . mysqli_real_escape_string($this->dbConn->connection,  $postingStoreTO->principalStoreUId ) . "'  ";
  
   
   
	  		  }

	  		$this->errorTO = $this->dbConn->processPosting($sql,$postingStoreTO->deliverName);

			if ($this->errorTO->type==FLAG_ERRORTO_SUCCESS) {
				if ($postingStoreTO->DMLType=="INSERT")	{
					$this->errorTO->identifier=$this->dbConn->dbGetLastInsertId(); // get the UID just created
					$this->errorTO->description = "Principal Store Successfully Created.";
				}
				else if ($postingStoreTO->DMLType=="UPDATE")	$this->errorTO->description = "Principal Store Successfully Updated.";
	      	}
	      	else  {
	      		$this->errorTO->type = FLAG_ERRORTO_ERROR;
				if ($postingStoreTO->DMLType=="INSERT") $this->errorTO->description = "Failed to create Principal Store.".mysqli_error($this->dbConn->connection).$this->errorTO->description;
				if ($postingStoreTO->DMLType=="UPDATE") $this->errorTO->description = "Failed to update Principal Store.".mysqli_error($this->dbConn->connection).$this->errorTO->description;
	      	}
    	} else {
    		return $this->errorTO;
    	  }

      	return $this->errorTO;

	}


    // comma separated list of uids
    function updateCreditLimits($principalId, $principalStoreUIdList, $creditLimit, $creditBalance) {
    	// floatval also converts to 0 if blank
    	$sql = "UPDATE  principal_store_master
			    		set     ledger_balance = ".(floatval($creditBalance)).",
											ledger_credit_limit = ".(floatval($creditLimit))."
							where   uid in ({$principalStoreUIdList})
							and     principal_uid = {$principalId}";

	    	$this->errorTO = $this->dbConn->processPosting($sql,"");

				if ($this->errorTO->type==FLAG_ERRORTO_SUCCESS) {
					$this->errorTO->description = "Credit Limits successfully updated";
      	} else  {
      		$this->errorTO->type = FLAG_ERRORTO_ERROR;
					$this->errorTO->description .= "Failed to update Store Credit Limits";
      	}

      	return $this->errorTO;
    }

    function updateStoreDepot($principalId, $psmUId, $depotUId) {
    	// skip depotuid validation for the time being - this should only be called from a proper lookup of depotUID anyway
    	if (trim($depotUId)=="") {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="updateStoreDepot requires a depotUID value";
				return $this->errorTO;
    	}

    	// floatval also converts to 0 if blank
    	$sql = "UPDATE  principal_store_master
			    		set     depot_uid = ".($depotUId)."
							where   uid = {$psmUId}
							and     principal_uid = {$principalId}";

	    $this->errorTO = $this->dbConn->processPosting($sql,"");

			if ($this->errorTO->type==FLAG_ERRORTO_SUCCESS) {
				$this->errorTO->description = "updateStoreDepot successfully updated";
      } else  {
      		$this->errorTO->type = FLAG_ERRORTO_ERROR;
					$this->errorTO->description .= "Failed to set depotUID in updateStoreDepot";
      }

      return $this->errorTO;
    }

		public function setStoreFieldsFromEDI($postingStoreEDIUpdateTO) {
			global $errorTO; global $dbConn;

			if (!isset($_SESSION)) session_start();
			$userId = $_SESSION['user_id'];

			$dbConn->dbQuery("SET time_zone='+0:00'"); // important to make sure PHPBACKEND which sits in RSA synchs changes updated according to SA time (unsynched table is in RSA), so make sureserver and this same time

			$strippedDeliverName=CommonUtils::getStrippedValue($postingStoreEDIUpdateTO->deliverName);

			// determine which fields need updating from TO
			$fields = array();
			if ((trim($postingStoreEDIUpdateTO->deliverName)!="") && ($postingStoreEDIUpdateTO->deliverName!==false)) {
				$fields[]="deliver_name='".mysqli_real_escape_string($this->dbConn->connection, trim($postingStoreEDIUpdateTO->deliverName))."'";
				$fields[]="stripped_deliver_name='{$strippedDeliverName}'";
			}
			if ((trim($postingStoreEDIUpdateTO->deliverAdd1)!="") && ($postingStoreEDIUpdateTO->deliverAdd1!==false)) {
				$fields[]="deliver_add1='".mysqli_real_escape_string($this->dbConn->connection, trim($postingStoreEDIUpdateTO->deliverAdd1))."'";
			}
			if ((trim($postingStoreEDIUpdateTO->deliverAdd2)!="") && ($postingStoreEDIUpdateTO->deliverAdd2!==false)) {
				$fields[]="deliver_add2='".mysqli_real_escape_string($this->dbConn->connection, substr(trim($postingStoreEDIUpdateTO->deliverAdd2),0,60))."'";
			}
			if ((trim($postingStoreEDIUpdateTO->deliverAdd3)!="") && ($postingStoreEDIUpdateTO->deliverAdd3!==false)) {
				$fields[]="deliver_add3='".mysqli_real_escape_string($this->dbConn->connection, trim($postingStoreEDIUpdateTO->deliverAdd3))."'";
			}
			if ((trim($postingStoreEDIUpdateTO->billName)!="") && ($postingStoreEDIUpdateTO->billName!==false)) {
				$fields[]="bill_name='".mysqli_real_escape_string($this->dbConn->connection, trim($postingStoreEDIUpdateTO->billName))."'";
			}
			if ((trim($postingStoreEDIUpdateTO->billAdd1)!="") && ($postingStoreEDIUpdateTO->billAdd1!==false)) {
				$fields[]="bill_add1='".mysqli_real_escape_string($this->dbConn->connection, trim($postingStoreEDIUpdateTO->billAdd1))."'";
			}
			if ((trim($postingStoreEDIUpdateTO->billAdd2)!="") && ($postingStoreEDIUpdateTO->billAdd2!==false)) {
				$fields[]="bill_add2='".mysqli_real_escape_string($this->dbConn->connection, trim($postingStoreEDIUpdateTO->billAdd2))."'";
			}
			if ((trim($postingStoreEDIUpdateTO->billAdd3)!="") && ($postingStoreEDIUpdateTO->billAdd3!==false)) {
				$fields[]="bill_add3='".mysqli_real_escape_string($this->dbConn->connection, trim($postingStoreEDIUpdateTO->billAdd3))."'";
			}
			if ((trim($postingStoreEDIUpdateTO->eanCode)!="") && ($postingStoreEDIUpdateTO->eanCode!==false)) {
				$fields[]="ean_code='".mysqli_real_escape_string($this->dbConn->connection, trim($postingStoreEDIUpdateTO->eanCode))."'";
			}
			if ((trim($postingStoreEDIUpdateTO->vatNumber)!="") && ($postingStoreEDIUpdateTO->vatNumber!==false)) {
				$fields[]="vat_number='".mysqli_real_escape_string($this->dbConn->connection, trim($postingStoreEDIUpdateTO->vatNumber))."'";
			}
			if ((trim($postingStoreEDIUpdateTO->vatNumber2)!="") && ($postingStoreEDIUpdateTO->vatNumber2!==false)) {
			  $fields[]="vat_number_2='".mysqli_real_escape_string($this->dbConn->connection, trim($postingStoreEDIUpdateTO->vatNumber2))."'";
			}
			if ((trim($postingStoreEDIUpdateTO->deliveryDay)!="") && ($postingStoreEDIUpdateTO->deliveryDay!==false)) {
				if (!in_array(strval($postingStoreEDIUpdateTO->deliveryDay),array("1","2","3","4","5","6","7","8"))) {
				  $this->errorTO->type=FLAG_ERRORTO_ERROR;
				  $this->errorTO->description="Invalid deliveryDay value passed in setStoreFieldsFromEDI.";
				  return $this->errorTO;
				}
				$fields[]="delivery_day_uid='".mysqli_real_escape_string($this->dbConn->connection, trim($postingStoreEDIUpdateTO->deliveryDay))."'";
			}
      if ((trim($postingStoreEDIUpdateTO->status)!="") && ($postingStoreEDIUpdateTO->status!==false)) {
        // at the moment on marking a store as active is catered for.
        if ($postingStoreEDIUpdateTO->status==FLAG_STATUS_ACTIVE) {
          $fields[]="status='".mysqli_real_escape_string($this->dbConn->connection, trim($postingStoreEDIUpdateTO->status))."'";
          $fields[]="on_hold='0'";
        } else {
          $this->errorTO->type=FLAG_ERRORTO_ERROR;
          $this->errorTO->description="Store status update type not catered for in setStoreFieldsFromEDI.";
          return $this->errorTO;
        }
      }
      if ((trim($postingStoreEDIUpdateTO->vatNumber)!="") && ($postingStoreEDIUpdateTO->vatNumber!==false)) {
        $fields[]="vat_number='".mysqli_real_escape_string($this->dbConn->connection, trim($postingStoreEDIUpdateTO->vatNumber))."'";
      }
			if ((trim($postingStoreEDIUpdateTO->noVAT)!="") && ($postingStoreEDIUpdateTO->noVAT!==false)) {
				if (!in_array(strval($postingStoreEDIUpdateTO->noVAT),array("0","1"))) {
				  $this->errorTO->type=FLAG_ERRORTO_ERROR;
				  $this->errorTO->description="Invalid noVAT value passed in setStoreFieldsFromEDI.";
				  return $this->errorTO;
				}
        $fields[] = "vat_excl_authorised_by = if ({$postingStoreEDIUpdateTO->noVAT}!=no_vat,NULL,vat_excl_authorised_by)"; // must come first. any source other than an explicit user update through the storeForm must reset this !
				$fields[] = "no_vat='".mysqli_real_escape_string($this->dbConn->connection, trim($postingStoreEDIUpdateTO->noVAT))."'";
			}

			if (sizeof($fields)==0) {
				  $this->errorTO->type=FLAG_ERRORTO_ERROR;
				  $this->errorTO->description="No fields found for updating in setStoreFieldsFromEDI.";
				  return $this->errorTO;
			} else {
			  $fields[] = "last_updated=now()";
			  $fields[] = "last_change_by_userid={$userId}";
			}


  		$sql="update principal_store_master
			      set ".(implode(",",$fields)).
			  	 " where uid = '{$postingStoreEDIUpdateTO->principalStoreUId}'";


			$errorTO = $dbConn->processPosting($sql,"");

			if ($errorTO->type==FLAG_ERRORTO_SUCCESS) {
				$errorTO->description = "Store Update in setStoreFieldsFromEDI Successful.";
    	} else  {
    		$errorTO->type = FLAG_ERRORTO_ERROR;
				$errorTO->description .= "Error Updating Store in setStoreFieldsFromEDI.";
				return $errorTO;
    	}

    	return $errorTO;

		}

		function associateStore($principalId, $psmParentUId, $psmChildUId) {
			// no need to do validation as the select (instead of values clause) covers that
			// parentUID is validated by the fact that this is only called after store creation and user would not have been able to create store if there was a problem
			// childUID is not permission validated because there is no risk as associations only used on depot management screen and is only used
			// when a principal creates an order using that uid which they would have access to as a result

    	$sql = "insert into principal_store_association (principal_uid, psm_parent_uid, psm_child_uid)
							select a.principal_uid,{$psmParentUId},{$psmChildUId}
							from   principal_store_master a, -- the parent (depot) store
										 principal_store_master b
							where  a.principal_uid = '{$principalId}'
							and    a.uid = '".mysqli_real_escape_string($this->dbConn->connection, $psmParentUId)."'
							and    b.uid = '".mysqli_real_escape_string($this->dbConn->connection, $psmChildUId)."'
							and    not exists (select 1 from principal_store_association c
																 where  c.principal_uid = '{$principalId}'
																 and    c.psm_parent_uid = '".mysqli_real_escape_string($this->dbConn->connection, $psmParentUId)."'
																 and    c.psm_child_uid = '".mysqli_real_escape_string($this->dbConn->connection, $psmChildUId)."' )";

	    $this->errorTO = $this->dbConn->processPosting($sql,"");

			if ($this->errorTO->type==FLAG_ERRORTO_SUCCESS) {
				$this->errorTO->description = "Store successfully Associated";
      } else  {
      		$this->errorTO->type = FLAG_ERRORTO_ERROR;
					$this->errorTO->description .= "Failed to associate Store!";
      }

      return $this->errorTO;
    }

    // no permissions or validations done
    function deassociateStore($principalId, $assocUId) {
    	$sql = "delete from principal_store_association
							where  principal_uid = '{$principalId}'
							and    uid = '".mysqli_real_escape_string($this->dbConn->connection, $assocUId)."'";

	    $this->errorTO = $this->dbConn->processPosting($sql,"");

			if ($this->errorTO->type==FLAG_ERRORTO_SUCCESS) {
				$this->errorTO->description = "Store Association successfully Removed";
      } else  {
      		$this->errorTO->type = FLAG_ERRORTO_ERROR;
					$this->errorTO->description .= "Failed to deassociate Store!";
      }

      return $this->errorTO;
    }


    public function postAreaValidation($postingAreaTO) {
    	global $ROOT; global $PHPFOLDER;

    	if (!in_array($postingAreaTO->DMLType,array("INSERT","UPDATE"))) {
    		$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="Invalid DMLType passed";
    		return false;
    	}

			if ($postingAreaTO->DMLType=="UPDATE") {
				if (trim($postingAreaTO->uId)=="") {
					$this->errorTO->type=FLAG_ERRORTO_ERROR;
					$this->errorTO->description="Update reqires a UID";
					return false;
				}

			}

			if (strlen(trim($postingAreaTO->description))>80) {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="Description can only be a maximum of 80 chars";
				return false;
			}

			if (trim($postingAreaTO->description)=="") {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="Description is a required field";
				return false;
			}

			return true;

    }

    function postArea( $postingAreaTO ) {

  		if (!isset($_SESSION)) session_start();
			$userId = $_SESSION['user_id'];

  		$resultOK = $this->postAreaValidation($postingAreaTO);

    	if ($resultOK===true) {

				if ($postingAreaTO->DMLType=="INSERT") {

			       	$sql = "INSERT INTO  `area` (principal_uid,description) VALUES ( " .
			       					"'" . mysqli_real_escape_string($this->dbConn->connection, trim($postingAreaTO->principalUId))  . "', " .
					       		 	"'" . mysqli_real_escape_string($this->dbConn->connection, trim($postingAreaTO->description))  . "' " .
											")";

		  		} else if ($postingAreaTO->DMLType=="UPDATE") {
		  			$sql = "UPDATE  `area`
										set		description		   = '" . mysqli_real_escape_string($this->dbConn->connection,  trim( $postingAreaTO->description ) ) . "'
			 							WHERE uid  = '" . mysqli_real_escape_string($this->dbConn->connection,  $postingAreaTO->uId ) . "'
										and   principal_uid = '{$postingAreaTO->principalUId}' ";

		  		  }

		  		$this->errorTO = $this->dbConn->processPosting($sql,$postingAreaTO->description);

				if ($this->errorTO->type==FLAG_ERRORTO_SUCCESS) {
					if ($postingAreaTO->DMLType=="INSERT")	{
						$this->errorTO->identifier=$this->dbConn->dbGetLastInsertId(); // get the UID just created
						$this->errorTO->description = "Area Successfully Created.";
					}	else if ($postingAreaTO->DMLType=="UPDATE")	$this->errorTO->description = "Area Successfully Updated.";
		    }	else  {
		      		$this->errorTO->type = FLAG_ERRORTO_ERROR;
							$this->errorTO->description = "Failed to update/create Area<br>\n.".mysql_error($this->dbConn->connection).$this->errorTO->description;
		    }

    	} else {
    			return $this->errorTO;
    	}

      return $this->errorTO;

	}


  public function createPrincipalDepotStore($principalUId, $depotUId) {

    global $ROOT,$PHPFOLDER;
    include_once($ROOT.$PHPFOLDER."DAO/PrincipalDAO.php");
    include_once($ROOT.$PHPFOLDER."DAO/DepotDAO.php");
    include_once($ROOT.$PHPFOLDER."DAO/ChainDAO.php");
    include_once($ROOT.$PHPFOLDER."TO/PostingStoreTO.php");
    $errorTO = new ErrorTO;
    $principalDAO = new PrincipalDAO($this->dbConn);
    $depotDAO = new DepotDAO($this->dbConn);
    $chainDAO = new ChainDAO($this->dbConn);

    // lookup the Principal Details
    $mfP = $principalDAO->getPrincipalItem($principalUId);
    if (sizeof($mfP)==0){
      $errorTO->type = FLAG_ERRORTO_ERROR;
      $errorTO->description = "Principal Record could not be located in order to auto-create Principal-Depot Store!";
      return $errorTO;
    }


    // lookup the Depot Details
    $mfD = $depotDAO->getDepotItem($depotUId);
    if (sizeof($mfD)==0){
      $errorTO->type = FLAG_ERRORTO_ERROR;
      $errorTO->description = "Depot Record could not be located in order to auto-create Principal-Depot Store!";
      return $errorTO;
    }

    // lookup the generic chain
    $mfC = $chainDAO->getPrincipalChainByOldCode($principalUId, CHAIN_GENERIC_OLD_CODE);
    if (sizeof($mfC)==0){
      $errorTO->type = FLAG_ERRORTO_ERROR;
      $errorTO->description = "Generic Chain could not be located in order to auto-create Principal-Depot Store!";
      return $errorTO;
    }

    $postingStoreTO = new PostingStoreTO;
    $postingStoreTO->DMLType = "INSERT";
    $postingStoreTO->principalStoreUId ="";
    $postingStoreTO->principal = $principalUId;
    $postingStoreTO->deliverName = substr($mfD[$depotUId]["depot_name"].' (WAREHOUSE)',0,60);
    $postingStoreTO->deliverAdd1 = $mfP[0]["physical_add1"];
    $postingStoreTO->deliverAdd2 = $mfP[0]["physical_add2"];
    $postingStoreTO->deliverAdd3 = $mfP[0]["physical_add3"];
    $postingStoreTO->billName = $mfP[0]["physical_add1"];
    $postingStoreTO->billAdd1 = $mfP[0]["postal_add1"];
    $postingStoreTO->billAdd2 = $mfP[0]["postal_add2"];
    $postingStoreTO->billAdd3 = $mfP[0]["postal_add3"];
    $postingStoreTO->depot = $depotUId;
    $postingStoreTO->deliveryDay = 8;
    $postingStoreTO->noVAT=0;
    $postingStoreTO->onHold = 0;
    $postingStoreTO->chain = $mfC[0]["uid"];
    $postingStoreTO->altPrincipalChainUId = $mfC[0]["uid"];
    $postingStoreTO->branchCode = "";
    $postingStoreTO->oldAccount = VAL_PSM_OLD_ACCOUNT_PREFIX.$depotUId;
    $postingStoreTO->allocatePermissionsUserList="";
    $postingStoreTO->ledgerBalance="";
    $postingStoreTO->ledgerCreditLimit="";
    $postingStoreTO->status=FLAG_STATUS_ACTIVE;
    $postingStoreTO->vendorCreatedByUId="";
    $postingStoreTO->ownedBy="";

    $rTO = $this->postPrincipalStore($postingStoreTO);
    if ($rTO->type!=FLAG_ERRORTO_SUCCESS) {
      $errorTO->type = FLAG_ERRORTO_ERROR;
      $errorTO->description = "Error creating Principal-Depot Store: ".$rTO->description;
      return $errorTO;
    }

    $errorTO->type = FLAG_ERRORTO_SUCCESS;
    $errorTO->description = "Successfully Created Principal-Depot Store";
    $errorTO->identifier = $rTO->identifier;
    $errorTO->object = json_decode(json_encode($postingStoreTO),true);  //return the TO in an array!

    return $errorTO;

  }


  public function postPrincipalSalesRepValidation($postingSalesRepTO, $userId) {

    global $ROOT,$PHPFOLDER;
    include_once($ROOT.$PHPFOLDER."DAO/StoreDAO.php");
    include_once($ROOT.$PHPFOLDER."DAO/AdministrationDAO.php");
    $storeDAO = new StoreDAO($this->dbConn);
    $adminDAO = new AdministrationDAO($this->dbConn);

    if(!in_array($postingSalesRepTO->DMLType, array("INSERT","UPDATE"))) {
      $this->errorTO->type=FLAG_ERRORTO_ERROR;
      $this->errorTO->description="Invalid DMLType passed";
      return false;
    }


    if(!CommonUtils::isAdminUser() && !CommonUtils::isStaffUser()){
      if($postingSalesRepTO->DMLType == 'INSERT'){
        if(!$adminDAO->hasRole($userId, $postingSalesRepTO->principalUId, ROLE_ADD_PRINCIPAL_SALES_REP)){
          $this->errorTO->type=FLAG_ERRORTO_ERROR;
          $this->errorTO->description="You do not have permissions to Add a Sales Rep";
          return false;
        }
      }
      if($postingSalesRepTO->DMLType == 'UPDATE'){
        if(!$adminDAO->hasRole($userId, $postingSalesRepTO->principalUId, ROLE_MODIFY_PRINCIPAL_SALES_REP)){
          $this->errorTO->type=FLAG_ERRORTO_ERROR;
          $this->errorTO->description="You do not have permissions to Modify a Sales Rep";
          return false;
        }
      }
    }

    if($postingSalesRepTO->DMLType=="UPDATE" && empty($postingSalesRepTO->UId)) {
      $this->errorTO->type=FLAG_ERRORTO_ERROR;
      $this->errorTO->description="Update reqires a UID";
      return false;
    }

 
    //RSA ID
    if(!empty($postingSalesRepTO->identityNumber)){
      if(!is_numeric($postingSalesRepTO->identityNumber)){
        $this->errorTO->type = FLAG_ERRORTO_ERROR;
        $this->errorTO->description = "Identity Number is invalid - must be numeric!";
        return false;
      }
      if(strlen($postingSalesRepTO->identityNumber)!=13){
        $this->errorTO->type = FLAG_ERRORTO_ERROR;
        $this->errorTO->description = "Identity Number is invalid - must be 13 characters!";
        return false;
      }
    }

    //first and surname require
    if(empty($postingSalesRepTO->firstName)){
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description = "First name is required!";
      return false;
    }
    if(empty($postingSalesRepTO->surname)){
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description = "Surname is required!";
      return false;
    }


    //email.
    if(filter_var($postingSalesRepTO->emailAddr, FILTER_VALIDATE_EMAIL) === False){
      $this->errorTO->type=FLAG_ERRORTO_ERROR;
      $this->errorTO->description="Email Address is invalid.";
      return false;
    }

    //CELLPHONE
    if(!is_numeric($postingSalesRepTO->mobileNumber)){
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description = "Mobile Number is invalid -  must be numeric!";
      return false;
    }
    if(strlen($postingSalesRepTO->mobileNumber)!=10){
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description = "Mobile Number is invalid - must be 10 characters!";
      return false;
    }

    if(!empty($postingSalesRepTO->salesTarget) && !preg_match(GUI_PHP_FLOAT_REGEX,$postingSalesRepTO->salesTarget)){
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description = "Sales Target must be a valid decimal number";
      return false;
    }

    //alt contact number
    if(!empty($postingSalesRepTO->alternateContactNumber)){
      if(!is_numeric($postingSalesRepTO->alternateContactNumber)){
        $this->errorTO->type = FLAG_ERRORTO_ERROR;
        $this->errorTO->description = "Alternate Contact Number is invalid -  must be numeric!";
        return false;
      }
      if(strlen($postingSalesRepTO->alternateContactNumber)!=10){
        $this->errorTO->type = FLAG_ERRORTO_ERROR;
        $this->errorTO->description = "Alternate Contact Number is invalid - must be 10 characters!";
        return false;
      }
    }

    //atleast one line of ship to required.
    if (empty($postingSalesRepTO->shiptoAddress1)) {
      $this->errorTO->type=FLAG_ERRORTO_ERROR;
      $this->errorTO->description="Please provide a ship to address!";
      return false;
    }


    return true;

  }


  public function postPrincipalSalesRep($postingSalesRepTO){


    if (!isset($_SESSION)) session_start();
      $userId = $_SESSION['user_id'];

    $this->dbConn->dbQuery("SET time_zone='+0:00'");

    if($this->postPrincipalSalesRepValidation($postingSalesRepTO, $userId)){

      if($postingSalesRepTO->DMLType == 'INSERT'){

        $sql = "INSERT INTO `principal_sales_representative`
                  (
                    principal_uid, rep_code, first_name, surname, identity_number,
                    email_addr, mobile_number, alternate_contact_number, shipto_address1,
                    shipto_address2, shipto_address3, `status`, created_datetime,
                    created_by_user_uid, last_update_datetime, last_update_user_uid,
                    sales_target
                  )
                 VALUES
                 (
                    " . mysqli_real_escape_string($this->dbConn->connection, $postingSalesRepTO->principalUId) . ",
                    '" . mysqli_real_escape_string($this->dbConn->connection, $postingSalesRepTO->repCode) . "',
                    '" . mysqli_real_escape_string($this->dbConn->connection, $postingSalesRepTO->firstName) . "',
                    '" . mysqli_real_escape_string($this->dbConn->connection, $postingSalesRepTO->surname) . "',
                    '" . mysqli_real_escape_string($this->dbConn->connection, $postingSalesRepTO->identityNumber) . "',
                    '" . mysqli_real_escape_string($this->dbConn->connection, $postingSalesRepTO->emailAddr) . "',
                    '" . mysqli_real_escape_string($this->dbConn->connection, $postingSalesRepTO->mobileNumber) . "',
                    '" . mysqli_real_escape_string($this->dbConn->connection, $postingSalesRepTO->alternateContactNumber) . "',
                    '" . mysqli_real_escape_string($this->dbConn->connection, $postingSalesRepTO->shiptoAddress1) . "',
                    '" . mysqli_real_escape_string($this->dbConn->connection, $postingSalesRepTO->shiptoAddress2) . "',
                    '" . mysqli_real_escape_string($this->dbConn->connection, $postingSalesRepTO->shiptoAddress3) . "',
                    '" . mysqli_real_escape_string($this->dbConn->connection, $postingSalesRepTO->status) . "',
                    NOW(),
                    " . mysqli_real_escape_string($this->dbConn->connection, $userId) . ",
                    NOW(),
                    " . mysqli_real_escape_string($this->dbConn->connection, $userId) . ",
                    ".((empty($postingSalesRepTO->salesTarget))?"NULL":mysqli_real_escape_string($this->dbConn->connection, $postingSalesRepTO->salesTarget))."
                 )";

      } else if($postingSalesRepTO->DMLType == 'UPDATE'){

        $sql = "UPDATE `principal_sales_representative`
                SET
                    rep_code = '" . mysqli_real_escape_string($this->dbConn->connection, $postingSalesRepTO->repCode) . "',
                    first_name = '" . mysqli_real_escape_string($this->dbConn->connection, $postingSalesRepTO->firstName) . "',
                    surname = '" . mysqli_real_escape_string($this->dbConn->connection, $postingSalesRepTO->surname) . "',
                    identity_number = '" . mysqli_real_escape_string($this->dbConn->connection, $postingSalesRepTO->identityNumber) . "',
                    email_addr = '" . mysqli_real_escape_string($this->dbConn->connection, $postingSalesRepTO->emailAddr) . "',
                    mobile_number = '" . mysqli_real_escape_string($this->dbConn->connection, $postingSalesRepTO->mobileNumber) . "',
                    alternate_contact_number = '" . mysqli_real_escape_string($this->dbConn->connection, $postingSalesRepTO->alternateContactNumber) . "',
                    shipto_address1 = '" . mysqli_real_escape_string($this->dbConn->connection, $postingSalesRepTO->shiptoAddress1) . "',
                    shipto_address2 = '" . mysqli_real_escape_string($this->dbConn->connection, $postingSalesRepTO->shiptoAddress2) . "',
                    shipto_address3 = '" . mysqli_real_escape_string($this->dbConn->connection, $postingSalesRepTO->shiptoAddress3) . "',
                    sales_target = ".((empty($postingSalesRepTO->salesTarget))?"NULL":mysqli_real_escape_string($this->dbConn->connection, $postingSalesRepTO->salesTarget)).",
                    `status` = '" . mysqli_real_escape_string($this->dbConn->connection, $postingSalesRepTO->status) . "',
                    last_update_datetime = NOW(),
                    last_update_user_uid = " . mysqli_real_escape_string($this->dbConn->connection, $userId) . "
                 WHERE uid = " . mysqli_real_escape_string($this->dbConn->connection, $postingSalesRepTO->UId) . "
                  AND principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $postingSalesRepTO->principalUId) . "";

      } else {
        $this->errorTO->type = FLAG_ERRORTO_ERROR;
        $this->errorTO->description = "Unknown DML Type provided!";
      }

      $result = $this->dbConn->processPosting($sql,"");

      if ($result->type != FLAG_ERRORTO_SUCCESS) {
        $this->errorTO->type = FLAG_ERRORTO_ERROR;
        $this->errorTO->description = "ERROR OCCURED: ".mysqli_error($this->dbConn->connection).$result->description;
      }	else  {
        $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
        $this->errorTO->description = "Successfully updated sales rep!";
        if($postingSalesRepTO->DMLType == 'INSERT'){
          $this->errorTO->identifier = $this->dbConn->dbGetLastInsertId();
          $this->errorTO->description = "Successfully created sales rep!";
        }
      }
      return $this->errorTO;

    }
    return $this->errorTO;

  }


}
?>
