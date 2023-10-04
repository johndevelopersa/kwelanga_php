<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');
include_once($ROOT.$PHPFOLDER."libs/CommonUtils.php");

class PrincipalDAO {
	private $dbConn;

	function __construct($dbConn) {
       $this->dbConn = $dbConn;
    }

	public function getUserPrincipalDepotArray($userId, $arrayIndex) {
		$sql="SELECT a.uid, a.depot_id, c.name depot_name, a.principal_id, b.name principal_name, principal_type
		  from user_principal_depot a,
		  	   principal b,
		  	   depot c
		  where b.uid = a.principal_id
		  and   a.depot_id = c.uid
		  and   a.user_id =".mysqli_real_escape_string($this->dbConn->connection, $userId);

		$this->dbConn->dbQuery($sql);

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				if ($arrayIndex=="principal_id") $arr[$row['principal_id']] = $row;
				else $arr[$row['uid']] = $row;
			}
		}

		return $arr;
	}

	public function getUserPrincipalArray($userId, $arrayIndex, $limitByDepot = false) {

          global $ROOT, $PHPFOLDER;
          include_once($ROOT.$PHPFOLDER."DAO/BillingDAO.php");

          $billingDAO = new BillingDAO($this->dbConn);
          $userList = join(',', $billingDAO->getAllowedBillingPrincipalUsers());

          if(CommonUtils::isAdminUser() || CommonUtils::isStaffUser()){

            $sql="SELECT b.uid as principal_id,
                         CONCAT(b.name, ' (',b.uid,')') AS principal_name,
                         b.principal_code,
                         principal_type
                  FROM  principal b
                  WHERE system_uid = ".mysqli_real_escape_string($this->dbConn->connection, $_SESSION['system_id'])."
                  AND   b.`status` <> 'D'
                  ORDER BY b.name";

          } else {
 
            $sql="SELECT b.uid as principal_id,
                         b.name AS principal_name,
                         b.principal_code,
                         principal_type
                  FROM principal b
                  INNER join user_principal_depot a on b.uid = a.principal_id AND a.user_id = ".mysqli_real_escape_string($this->dbConn->connection, $userId)."
                  WHERE system_uid = ".mysqli_real_escape_string($this->dbConn->connection, $_SESSION['system_id'])."
                  AND b.`status` <> 'D' " . 
                  (($limitByDepot === true && isset($_SESSION['depot_id']) && $_SESSION['depot_id'] != 0) ? (' AND a.depot_id = '.mysqli_real_escape_string($this->dbConn->connection, $_SESSION['depot_id']).' '):(' ')) . " 
                  GROUP BY b.uid  
                  ORDER BY b.name" ;
          }
          $this->dbConn->dbQuery($sql);

          $arr=array();
          if ($this->dbConn->dbQueryResultRows > 0) {
            while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
              if ($arrayIndex=="principal_id") $arr[$row['principal_id']] = $row;
              else $arr[$row['principal_id']] = $row;
            }
          }
          return $arr;
	}


	public function getUserPrincipalDepotItem($userId, $principalId, $depotId) {
		$sql="SELECT a.uid, a.depot_id, c.name depot_name, a.principal_id, b.name principal_name
			  from user_principal_depot a,
			  	   principal b,
			  	   depot c
			  where b.uid = a.principal_id
			  and   a.depot_id = c.uid
			  and   a.user_id =".mysqli_real_escape_string($this->dbConn->connection, $userId)."
			  and   a.principal_id =".mysqli_real_escape_string($this->dbConn->connection,$principalId)."
			  and   a.depot_id =".mysqli_real_escape_string($this->dbConn->connection, $depotId);

		$this->dbConn->dbQuery($sql);

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				$arr[] = $row;
			}
		}

		return $arr;
	}

	public function getPrincipalItem($principalUId) {
		$sql="SELECT a.uid, 
		             a.principal_code, 
		             a.name principal_name, 
		             a.physical_add1, 
		             a.physical_add2, 
		             a.physical_add3, 
		             a.physical_add4,
		             a.postal_add1, 
		             a.postal_add2, 
		             a.postal_add3, 
		             a.postal_add4, 
		             a.vat_num, 
		             a.rt_acc_num, 
		             a.office_tel, 
		             a.email_add, 
		             a.contactperson, 
		             a.suspended,
		             a.banking_details, 
		             a.alt_principal_code, 
		             a.principal_uplift_code, 
		             a.principal_type, 
		             a.principal_gln, 
		             a.activity_price_bucket_1,
		             a.status, 
		             a.export_number , 
		             a.charge,
		             a.doc_charge,
		             a.cancelled,
		             a.turnover,
		             a.image_file_location
  			  from   principal a
  			  where  a.uid =".mysqli_real_escape_string($this->dbConn->connection,$principalUId);

		$this->dbConn->dbQuery($sql);

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				$arr[] = $row;
			}
		}

		return $arr;
	}

	public function getUserPrincipalItem($principalUId, $userUId) {
		$sql="SELECT a.uid, a.principal_code, a.name principal_name, a.physical_add1, a.physical_add2, physical_add3, physical_add4,
				postal_add1, postal_add2, postal_add3, postal_add4, vat_num, rt_acc_num, office_tel, email_add, contactperson, suspended,
				banking_details, alt_principal_code, a.status
			  from   principal a
			  where  a.uid =".mysqli_real_escape_string($this->dbConn->connection, $principalUId)."
			  and    exists (select 1 from user_principal_depot b where a.uid = b.principal_id and b.user_id='{$userUId}')";

		$this->dbConn->dbQuery($sql);

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				$arr[] = $row;
			}
		}

		return $arr;
	}

	public function getAllPrincipalsArray() {
		$sql="SELECT a.uid, a.principal_code, a.name principal_name, a.physical_add1, a.physical_add2, a.suspended
			  from principal a";

		$this->dbConn->dbQuery($sql);

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				$arr[$row['uid']] = $row;
			}
		}

		return $arr;
	}

	public function getAllPrincipalCodesArray() {

          $sql="SELECT a.uid, a.principal_code, a.principal_uplift_code, a.alt_principal_code, a.status
                    from principal a
                    where system_uid = '".SYS_KWELANGA."'";

          $this->dbConn->dbQuery($sql);

          $arr=array();
          if ($this->dbConn->dbQueryResultRows > 0) {
            while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
              $arr[$row['uid']] = $row;
            }
          }

          return $arr;
	}

	 public function getPrincipalUidByGln($gln){

	   // can return multiple
	   	$sql="select uid
	   			from principal
				  where principal_gln = '".mysqli_real_escape_string($this->dbConn->connection, $gln)."'";

  		$this->dbConn->dbQuery($sql);

  		$arr=array();
  		if ($this->dbConn->dbQueryResultRows > 0) {
  			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
  				$arr[] = $row;
  			}
  		}

  		return $arr;
	  }

	  public function getPrincipalVendorsByGln($gln, $vendorUId){

	    // can return multiple
	    $sql="select a.uid, b.vendor_account, if(b.principal_uid is null,'N','Y') has_principal_vendor
            from principal a
            left join principal_vendor b on a.uid = b.principal_uid
            where a.principal_gln = '".mysqli_real_escape_string($this->dbConn->connection, $gln) ."' or a.principal_gln2 = '".mysqli_real_escape_string($this->dbConn->connection, $gln) ."'
            and   (b.vendor_uid is null or b.vendor_uid = '".mysqli_real_escape_string($this->dbConn->connection, $vendorUId)."')
            and    b.status = 'A'";

	    $this->dbConn->dbQuery($sql);

	    $arr=array();
	    if ($this->dbConn->dbQueryResultRows > 0) {
	      while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
	    				$arr[] = $row;
	      }
	    }

	    return $arr;
	  }

  	public function getPrincipalByCode($principalCode) {
  		// the lookup must strip off leading zeros. You cannot change the principal table value because it needs
  		// to be the same as for historical maindb lookups.
  		$pC = intval(trim($principalCode)); // just in case it has leading zeros
  		$sql="select uid, principal_code
  			  from   principal
  			  where  principal_code='".mysqli_real_escape_string($this->dbConn->connection, $pC)."'";

  		$this->dbConn->dbQuery($sql);

  		$arr=array();
  		if ($this->dbConn->dbQueryResultRows > 0) {
  			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
  				$arr[] = $row;
  			}
  		}

  		return $arr;
  	}

    public function getPrincipalByUpliftCode($principalCode) {
      // the lookup must strip off leading zeros. You cannot change the principal table value because it needs
      // to be the same as for historical maindb lookups.
      $pC = intval(trim($principalCode)); // just in case it has leading zeros
      $sql="select uid, principal_code
          from   principal
          where  principal_uplift_code='".mysqli_real_escape_string($this->dbConn->connection, $pC)."'";

      $this->dbConn->dbQuery($sql);

      $arr=array();
      if ($this->dbConn->dbQueryResultRows > 0) {
        while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
          $arr[] = $row;
        }
      }

      return $arr;
    }


	  public function getPrincipalVendor($vendorUid,$princiaplUid){

	    // can return multiple
	  	$sql="select *
				  from principal_vendor
				  where vendor_uid = '".mysqli_real_escape_string($this->dbConn->connection, $vendorUid)."'
				  and principal_uid = '".mysqli_real_escape_string($this->dbConn->connection, $princiaplUid)."'";

		$this->dbConn->dbQuery($sql);

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				$arr[] = $row;
			}
		}

		return $arr;

	  }

	  public function getPrincipalsForVendor($vendorUid){

	    // can be multiple rows per principal per vendor even
      $sql="select *
          from principal_vendor
          where vendor_uid = '".mysqli_real_escape_string($this->dbConn->connection, $vendorUid)."'
          and    status = 'A'";

    $this->dbConn->dbQuery($sql);

    $arr=array();
    if ($this->dbConn->dbQueryResultRows > 0) {
      while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
        $arr[] = $row;
      }
    }

    return $arr;

    }

	  // always returns atleast 1 row
	  public function getPrincipalPreferences($principalId) {
		$sql="select *
				from  principal_preference
				where principal_uid = '".mysqli_real_escape_string($this->dbConn->connection, $principalId)."'";
				
		$this->dbConn->dbQuery($sql);

		$arr=array();
		while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
			$arr[] = $row;
		}
		if (empty($arr)) {
			$arr[0]["uid"]="";
			$arr[0]["principal_uid"]=$principalId;
			$arr[0]["document_unique"]="Y";
			$arr[0]["order_number_unique"]="N"; // limit sql processing time
      $arr[0]["order_number_unique_ws"]="N";
			$arr[0]["direct_insert_tt"]="N";
			$arr[0]["use_rt_doc_num"]="N";
      $arr[0]["pnp_ws_invoice_enabled"]="N";
      $arr[0]["pnp_ws_starting_invoice_date"]="";
		}
		return $arr;
	}

	// only the most specific should be used
	public function getPrincipalCapturePreferences($principalId, $documentTypeUId="") {
		$sql="select *
				from  field_preference
				where principal_uid = '".mysqli_real_escape_string($this->dbConn->connection, $principalId)."'
				and   system = 'CAPTURE'
				and   (document_type_uid is null or document_type_uid = '{$documentTypeUId}')
				order by if(document_type_uid is not null,1,2)";

		$this->dbConn->dbQuery($sql);

		$arr=array();
		while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
			$arr[] = $row;
		}

		return $arr;
	}

	// only the most specific should be used, returned for all document types
	public function getAllPrincipalCapturePreferences($principalId) {

          if (!isset($_SESSION)) session_start();
          $systemId = (isset($_SESSION["system_id"])) ? $_SESSION["system_id"] : SYS_RETAIL;

          $sql="SELECT *
                      FROM  field_preference
                      WHERE system_uid = '" . mysqli_real_escape_string($this->dbConn->connection, $systemId) . "'
                      AND   (principal_uid IS NULL OR principal_uid = '" . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "')
                      AND   system = 'CAPTURE'
                      AND   (
                        		  FIND_IN_SET('1', hide_field) or
                         		  FIND_IN_SET('2', hide_field)
                         		 )
                      order by if(document_type_uid is not null,1,2)";

          $this->dbConn->dbQuery($sql);

          $arr=array();
          while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
                  $arr[] = $row;
          }

          return $arr;
	}

  // only the most specific should be used, returned for all document types
  public function getPrincipalDocumentTypes($principalId) {
    $sql="select *
          from  principal_document_type
          where principal_uid = '".mysqli_real_escape_string($this->dbConn->connection, $principalId)."'
          order by document_type_uid";

    $this->dbConn->dbQuery($sql);

    $arr=array();
    while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
      $arr[] = $row;
    }

    return $arr;
  }

  public function getAllPrincipalDocumentTypes() {
    $sql="select *
          from  principal_document_type
          order by principal_uid, document_type_uid";

    $this->dbConn->dbQuery($sql);

    $arr=array();
    while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
      $arr[] = $row;
    }

    return $arr;
  }

  public function getAllPrincipalDocumentTypes_ProformaPricing() {
   $sql="select *
          from  principal_document_type
          where  proforma_pricing = 'Y'
          order by principal_uid, document_type_uid";

   $this->dbConn->dbQuery($sql);

   $arr=array();
   while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
    $arr[] = $row;
   }

   return $arr;
  }

	public function usesDocumentNumberAutoSeq($principalId, $docTypeUId, $dataSource, $depotUId, $capturedBy) {
    // capturedBy is not part of this check deliberately
		if ((trim($principalId)=="") ||
				(trim($docTypeUId)=="") ||
				(trim($dataSource)=="") ||
				(trim($depotUId)=="")) {
					return ErrorTO::NewError("FUNCTION usesDocumentNumberAutoSeq FAILED ! - required parameter empty for document seq lookup (principal: $principalId, docTypeUId: $docTypeUId, dataSource: $dataSource, depotUId: $depotUId, capturedBy: $capturedBy)");
		}

		// default is to use autoSeq ; exclude takes priority
		$sql="select * from
					(
						select 'MATCH' row_source,a.*
						from  sequence_control_users a
						where principal_uid = '".mysqli_real_escape_string($this->dbConn->connection, $principalId)."'
					  and   ((FIND_IN_SET('".trim($depotUId)."',depot_uid)>0) or depot_uid is null)
						and   ((FIND_IN_SET('".trim($docTypeUId)."',document_type_uid)>0) or document_type_uid is null)
						and   ((FIND_IN_SET('".trim($dataSource)."',data_source)>0) or data_source is null)
            and   ((FIND_IN_SET('".trim($capturedBy)."',captured_by)>0) or captured_by is null)
						union all
						-- if this is the only row in RS then you know there are rows, but that none matched, saves having to do a second separate select
						select distinct 'NOT MATCHED' row_source, null, null, null, null, null, null, null
						from  sequence_control_users a
						where principal_uid = '".mysqli_real_escape_string($this->dbConn->connection, $principalId)."'
					) a
					order  by if(row_source='MATCH',1,2),
						      if(`type`='E',1,2)";

		$this->dbConn->dbQuery($sql);

		if (!$this->dbConn->dbQueryResult) {
			trigger_error('FUNCTION usesDocumentNumberAutoSeq FAILED !', E_USER_ERROR);
			return;
		} else if ($this->dbConn->dbQueryResultRows>0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				if ($row["type"]=="E") return false; // Exclude takes priority
				else if ($row["type"]=="I") return true;
				else return false; // if any rows are loaded but not matched, then the default is client doc num
			}
			return false; // if any rows are loaded but not matched, then the default is client doc num
		} else return true; // default to autoseq if zero rows loaded at all before match

	}

	// the parameters differ from above function in that a blank value means ignore whereas above means crash !
	public function getSequenceControlUsers($principalId, $docTypeUId, $dataSource, $depotUId, $capturedBy) {
		$sql="select * from
					(
						select 'MATCH' row_source,a.*
						from  sequence_control_users a
						where principal_uid = '".mysqli_real_escape_string($this->dbConn->connection, $principalId)."'
					  and   ('".trim($depotUId)."'='' or (FIND_IN_SET('".trim($depotUId)."',depot_uid)>0) or depot_uid is null)
						and   ('".trim($docTypeUId)."'='' or (FIND_IN_SET('".trim($docTypeUId)."',document_type_uid)>0) or document_type_uid is null)
						and   ('".trim($dataSource)."'='' or (FIND_IN_SET('".trim($dataSource)."',data_source)>0) or data_source is null)
            and   ('".trim($capturedBy)."'='' or (FIND_IN_SET('".trim($capturedBy)."',captured_by)>0) or captured_by is null)
						union all
						-- if this is the only row in RS then you know there are rows, but that none matched, saves having to do a second separate select
						select distinct 'NOT MATCHED' row_source, null, null, null, null, null, null, null
						from  sequence_control_users a
						where principal_uid = '".mysqli_real_escape_string($this->dbConn->connection, $principalId)."'
					) a
					order  by if(row_source='MATCH',1,2), if(`type`='E',1,2)";

		$this->dbConn->dbQuery($sql);

		$arr = array();
		if (!$this->dbConn->dbQueryResult) {
			trigger_error('FUNCTION getSequenceControlUsers FAILED !', E_USER_ERROR);
			return;
		} else {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				$arr[] = $row;
			}
			return $arr;
		}

	}

	// gets the actual principal who controls a depot for WMS users
	// there should never be more than one for WMS users
	public function getDepotPrincipal($depotUId){

	  	$sql="select distinct a.uid principal_uid, a.name principal_name
					  from principal a,
								 user_principal_depot b,
								 depot c
					  where a.principal_type = '".PT_DEPOT."'
						and   a.uid = b.principal_id
						and   b.depot_id = c.uid
						and   c.wms = 'Y'
						and   c.uid = '".mysqli_real_escape_string($this->dbConn->connection, $depotUId)."'";

		$this->dbConn->dbQuery($sql);

		$arr=array();
		if ($this->dbConn->dbQueryResultRows > 0) {
			while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
				$arr[] = $row;
			}
		}

		return $arr;

	  }

    /* @param actionType is a constant DOAT_*
     * */
    public function getPrincipalDocumentOriginAction($principalUId, $actionType) {
      $sql="select *
          from  document_origin_action
          where principal_uid = '{$principalUId}'
          and   action_type = '{$actionType}'";

      $this->dbConn->dbQuery($sql);

      $arr=array();
      while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
        $arr = $row;
        return $arr;
      }

      return $arr;
    }

    // ie. Logic must be processed as : Is there a matching row ? (there can only be one row due to constraints)
    public function resolvePrincipalDocumentOriginAction($PDOAArray, $documentTypeUId, $depotUId, $dataSource, $capturedBy) {
      if (empty($PDOAArray)) return false;

      // these checks disqualify the final return true value
      if ((trim($PDOAArray["document_type_uids"])!="") && (!in_array($documentTypeUId,explode(",",$PDOAArray["document_type_uids"])))) return false;
      if ((trim($PDOAArray["depot_uids"])!="") && (!in_array($depotUId,explode(",",$PDOAArray["depot_uids"])))) return false;
      if ((trim($PDOAArray["data_sources"])!="") && (!in_array($dataSource,explode(",",$PDOAArray["data_sources"])))) return false;
      if ((trim($PDOAArray["captured_bys"])!="") && (!in_array($capturedBy,explode(",",$PDOAArray["captured_bys"])))) return false;

      // you dont realy need this line as final return value is also true, but is incl here to ensure you understand the impact of blank depot uid
      if ($documentTypeUId=="") return true;
      if ($depotUId=="") return true;

      return true;
    }

   public function GetAllDocumentScanningPrincipals(){

      $sql="select  a.uid,
                    a.name,
                    a.scanned_pod_start
            from principal a
            where a.scanned_pod_start IS NOT NULL";

      $this->dbConn->dbQuery($sql);

      $arr=array();
      if ($this->dbConn->dbQueryResultRows > 0) {
        while($row = mysqli_fetch_array($this->dbConn->dbQueryResult,MYSQLI_ASSOC)){
           $arr[] = $row;
        }
      }

      return $arr;

   }




		            



}
?>
