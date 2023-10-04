<?php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER."libs/CommonUtils.php");
include_once($ROOT.$PHPFOLDER."libs/ValidationCommonUtils.php");
include_once($ROOT.$PHPFOLDER."DAO/AdministrationDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/PrincipalDAO.php");
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');

/*
 * NOTE : The validation should return IMMEDIATELY if an error type occurred (required for the way screen is processed)
 */

class PostPrincipalDAO {

	public $errorTO;
	private $dbConn;

	function __construct($dbConn) {
		$this->dbConn = $dbConn;
		$this->errorTO = new ErrorTO;
    }

    public function postPrincipalValidation($postingPrincipalTO) {

      global $ROOT; global $PHPFOLDER;

      // get user. don't pass it because this is more secure.
      if (!isset($_SESSION)) session_start();
      $userId = $_SESSION['user_id'];
      $systemId = $_SESSION['system_id'];
      $principalId = $_SESSION['principal_id']; // used for hasRole validation

    	//Second level of Role Check
    	$adminDAO = new AdministrationDAO($this->dbConn);
        if($postingPrincipalTO->DMLType == 'INSERT' && (!$adminDAO->hasRole($userId, $principalId, ROLE_ADD_PRINCIPAL))){
    	  $this->errorTO->type=FLAG_ERRORTO_ERROR;
		  $this->errorTO->description="You do not have permissions to Add New Principals!";
		  return false;
        } elseif($postingPrincipalTO->DMLType == 'UPDATE' && (!$adminDAO->hasRole($userId, $principalId, ROLE_MODIFY_PRINCIPAL))){
    	  $this->errorTO->type=FLAG_ERRORTO_ERROR;
		  $this->errorTO->description="You do not have permissions to Change Principals!";
		  return false;
        }

    	//Start Validation : From Top > Bottom
    	if (!ValidationCommonUtils::checkPostingType($postingPrincipalTO->DMLType)) return false;

    	if(trim($postingPrincipalTO->name) == "") {
             $this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description='Please enter a principal Name!';
			return false;
        }

        if (($postingPrincipalTO->DMLType=="INSERT") && (strlen($postingPrincipalTO->principal_code)>3 || (empty($postingPrincipalTO->principal_code) || !is_numeric($postingPrincipalTO->principal_code)))) {
             $this->errorTO->type=FLAG_ERRORTO_ERROR;
             $this->errorTO->description='Principal Code must be a max of 3 numbers.';
             return false;
        }

		if (!empty ($postingPrincipalTO->altPrincipalCode)) {
    		if (strlen($postingPrincipalTO->altPrincipalCode)>3 || !is_numeric($postingPrincipalTO->altPrincipalCode)) {
    			$this->errorTO->type=FLAG_ERRORTO_ERROR;
    			$this->errorTO->description="Alternate Principal Code can be a max of 3 numbers.";
    			return false;
    		}
		}

        if (empty($postingPrincipalTO->physical_add1)) {
		    $this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Please enter a Physical Address.";
			return false;
        }

		if (empty($postingPrincipalTO->vat_num) || !is_numeric($postingPrincipalTO->vat_num)) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Please enter a valid VAT Number.";
			return false;
		}

		if (($postingPrincipalTO->DMLType=="INSERT") && (!empty($postingPrincipalTO->rt_acc_num)) && ((strlen($postingPrincipalTO->rt_acc_num)>5) || !is_numeric($postingPrincipalTO->rt_acc_num))) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description = 'RT Acc Number can be a max of 5 numbers.';
			return false;
		}

		if (filter_var(trim($postingPrincipalTO->email_add), FILTER_VALIDATE_EMAIL) === False){
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Please enter a valid Email Address.";
			return false;
		}

		//if the contact number is entered make sure it is numeric
     	if (!empty ($postingPrincipalTO->office_tel)) {
			$office_tel = preg_replace ( '/\s/', '', trim ($postingPrincipalTO->office_tel) );
			if (! is_numeric ( $office_tel ) || strlen ( $office_tel ) < 10) {
			  	$this->errorTO->type=FLAG_ERRORTO_ERROR;
			    $this->errorTO->description="Please enter a valid Office Contact Number!";
				return false;
			}
		}

		if (strlen($postingPrincipalTO->bankingDetails)>255) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Banking Details can be a max of 255 chars.";
			return false;
		};

		if (($postingPrincipalTO->principalType!=PT_PRINCIPAL) && ($postingPrincipalTO->principalType!=PT_SALES_AGENT) && ($postingPrincipalTO->principalType!=PT_DEPOT)) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Please select a Principal Type.";
			return false;
		}

		$PrincipalDAO = new PrincipalDAO($this->dbConn);
		if(($postingPrincipalTO->DMLType == 'UPDATE') && (!count($PrincipalDAO->getPrincipalItem($postingPrincipalTO->puid))>0)){
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Principal was not found!<br>Please inform RetailTrading Management.";
			return false;
		}

		if(!in_array($postingPrincipalTO->status,array(FLAG_STATUS_ACTIVE,FLAG_STATUS_SUSPENDED))){
		  $this->errorTO->type=FLAG_ERRORTO_ERROR;
		  $this->errorTO->description="Invalid Status.";
		  return false;
		}

		return true;
    }

     public function postPrincipal($postingPrincipalTO) {

       if (!isset($_SESSION)) session_start();
        $systemId = $_SESSION['system_id'];


       //Validation check
       if ($this->postPrincipalValidation($postingPrincipalTO)) {

          $this->dbConn->dbQuery("SET time_zone='+0:00'");

          //CHANGE EMPTY VALUES TO NULLS : ONLY POSSIBLY EMPTY ENTRIES TO REDUCE TABLE SIZE, NULL HAS BETTER STORAGE
          $principalGLN = ($postingPrincipalTO->principalGLN != '') ? ("'" . mysqli_real_escape_string($this->dbConn->connection, trim($postingPrincipalTO->principalGLN)) . "'") : ('NULL');
          $contactperson = ($postingPrincipalTO->contactperson != '') ? ("'" . mysqli_real_escape_string($this->dbConn->connection, trim($postingPrincipalTO->contactperson)) . "'") : ('NULL');
          $bankingDetails = ($postingPrincipalTO->bankingDetails != '') ? ("'" . mysqli_real_escape_string($this->dbConn->connection, trim($postingPrincipalTO->bankingDetails)) . "'") : ('NULL');
          $office_tel = ($postingPrincipalTO->office_tel != '') ? ("'" . mysqli_real_escape_string($this->dbConn->connection, preg_replace ( '/\s/', '', $postingPrincipalTO->office_tel)) . "'") : ('NULL');
          $rt_acc_num = ($postingPrincipalTO->rt_acc_num != '') ? ("'" . mysqli_real_escape_string($this->dbConn->connection, trim($postingPrincipalTO->rt_acc_num)) . "'") : ('NULL');
          $altPrincipalCode = ($postingPrincipalTO->altPrincipalCode != '') ? ("'" . mysqli_real_escape_string($this->dbConn->connection, trim($postingPrincipalTO->altPrincipalCode)) . "'") : ('NULL');
          $principalUpliftCode = (trim($postingPrincipalTO->principalUpliftCode) != '') ? ("'" . mysqli_real_escape_string($this->dbConn->connection, trim($postingPrincipalTO->principalUpliftCode)) . "'") : ('NULL');
          $exportNumber = ($postingPrincipalTO->exportNumber != '') ? ("'" . mysqli_real_escape_string($this->dbConn->connection, trim($postingPrincipalTO->exportNumber)) . "'") : ('NULL');

          //Accept only INSERT | UPDATE.
          if ($postingPrincipalTO->DMLType=='INSERT') {

            $sql="INSERT INTO `principal` (
                                          `system_uid`,
                                          `principal_code`,
                                          `name`,
                                          `physical_add1`,
                                          `physical_add2`,
                                          `physical_add3`,
                                          `physical_add4`,
                                          `postal_add1`,
                                          `postal_add2`,
                                          `postal_add3`,
                                          `postal_add4`,
                                          `vat_num`,
                                          `principal_gln`,
                                          `rt_acc_num`,
                                          `office_tel`,
                                          `email_add`,
                                          `contactperson`,
                                          `alt_principal_code`,
                                          `principal_uplift_code`,
                                          `banking_details`,
                                          `principal_type`,
                                          `status`,
                                          `export_number`,
                                          `charge`,
                                          `doc_charge`,
                                          `cancelled`,
                                          `turnover`,
                                          `last_updated`,
                                          `last_synch_status`
                                  ) VALUES (".
                                  "'".$systemId."',".
                                  "'".mysqli_real_escape_string($this->dbConn->connection, trim($postingPrincipalTO->principal_code))."',".
                                  "'".mysqli_real_escape_string($this->dbConn->connection, trim($postingPrincipalTO->name))."',".
                                  "'".mysqli_real_escape_string($this->dbConn->connection, trim($postingPrincipalTO->physical_add1))."',".
                                  "'".mysqli_real_escape_string($this->dbConn->connection, trim($postingPrincipalTO->physical_add2))."',".
                                  "'".mysqli_real_escape_string($this->dbConn->connection, trim($postingPrincipalTO->physical_add3))."',".
                                  "'".mysqli_real_escape_string($this->dbConn->connection, trim($postingPrincipalTO->physical_add4))."',".
                                  "'".mysqli_real_escape_string($this->dbConn->connection, trim($postingPrincipalTO->postal_add1))."',".
                                  "'".mysqli_real_escape_string($this->dbConn->connection, trim($postingPrincipalTO->postal_add2))."',".
                                  "'".mysqli_real_escape_string($this->dbConn->connection, trim($postingPrincipalTO->postal_add3))."',".
                                  "'".mysqli_real_escape_string($this->dbConn->connection, trim($postingPrincipalTO->postal_add4))."',".
                                  "'".mysqli_real_escape_string($this->dbConn->connection, trim($postingPrincipalTO->vat_num))."',".
                                  "".$principalGLN.",".
                                  "".$rt_acc_num.",".
                                  "".$office_tel. ",".
                                  "'".mysqli_real_escape_string($this->dbConn->connection, $postingPrincipalTO->email_add)."',".
                                  "".$contactperson.",".
                                  "".$altPrincipalCode.",".
                                  "".$principalUpliftCode.",".
                                  "".$bankingDetails.",".
                                  "'".mysqli_real_escape_string($this->dbConn->connection, $postingPrincipalTO->principalType)."',".
                                  "'".mysqli_real_escape_string($this->dbConn->connection, $postingPrincipalTO->status)."',".
                                  "".$exportNumber.",".
                                  "'".mysqli_real_escape_string($this->dbConn->connection, $postingPrincipalTO->charge)."',".
                                  "'".mysqli_real_escape_string($this->dbConn->connection, $postingPrincipalTO->doc_charge)."',".
                                  "'".mysqli_real_escape_string($this->dbConn->connection, $postingPrincipalTO->cancel_charge)."',".
                                  "'".mysqli_real_escape_string($this->dbConn->connection, $postingPrincipalTO->debtor_charge)."',".
                                  "now(),".
                                  "'U'".
                            ")";

	    } elseif ($postingPrincipalTO->DMLType == 'UPDATE') {

	      //DO NOT TAKE INTO ACCOUNT DISABLED FIELDS
	        $sql="UPDATE `principal`
	    			SET
      					`name`='".mysqli_real_escape_string($this->dbConn->connection, trim($postingPrincipalTO->name))."',
      					`physical_add1`='".mysqli_real_escape_string($this->dbConn->connection, trim($postingPrincipalTO->physical_add1))."',
                `physical_add2`='".mysqli_real_escape_string($this->dbConn->connection, trim($postingPrincipalTO->physical_add2))."',
                `physical_add3`='".mysqli_real_escape_string($this->dbConn->connection, trim($postingPrincipalTO->physical_add3))."',
                `physical_add4`='".mysqli_real_escape_string($this->dbConn->connection, trim($postingPrincipalTO->physical_add4))."',
                `postal_add1`='".mysqli_real_escape_string($this->dbConn->connection, trim($postingPrincipalTO->postal_add1))."',
                `postal_add2`='".mysqli_real_escape_string($this->dbConn->connection, trim($postingPrincipalTO->postal_add2))."',
                `postal_add3`='".mysqli_real_escape_string($this->dbConn->connection, trim($postingPrincipalTO->postal_add3))."',
                `postal_add4`='".mysqli_real_escape_string($this->dbConn->connection, trim($postingPrincipalTO->postal_add4))."',
                `vat_num`='".mysqli_real_escape_string($this->dbConn->connection, trim($postingPrincipalTO->vat_num))."',
                `principal_gln`=".$principalGLN.",
                `rt_acc_num`=".$rt_acc_num.",
                `office_tel`=".$office_tel.",
                `email_add`= '".mysqli_real_escape_string($this->dbConn->connection, trim($postingPrincipalTO->email_add))."',
                `contactperson`=".$contactperson.",
                `principal_uplift_code` = ". $principalUpliftCode . ",
      					`banking_details`=".$bankingDetails.",
      					`status` = '".mysqli_real_escape_string($this->dbConn->connection, trim($postingPrincipalTO->status)). "',
      					`export_number` = ".$exportNumber.",
                `charge` = '".mysqli_real_escape_string($this->dbConn->connection, $postingPrincipalTO->charge)."',
                `doc_charge` = '".mysqli_real_escape_string($this->dbConn->connection, $postingPrincipalTO->doc_charge)."',
                `cancelled` = '".mysqli_real_escape_string($this->dbConn->connection, $postingPrincipalTO->cancel_charge)."',
                `turnover` = '".mysqli_real_escape_string($this->dbConn->connection, $postingPrincipalTO->debtor_charge)."',
      					`last_updated`= now(),
      					`last_synch_status`='U'
      				WHERE uid = ".trim($postingPrincipalTO->puid);

      } else  {
        return $this->errorTO;
      }

      $this->errorTO = $this->dbConn->processPosting($sql,'');
      if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {
        return $this->errorTO;
      }


      if ($postingPrincipalTO->DMLType=='INSERT') {

        $postingPrincipalTO->puid = $this->errorTO->identifier = $this->dbConn->dbGetLastInsertId();

        $sql="INSERT INTO principal_preference (principal_uid, document_unique, order_number_unique, order_number_unique_ws, direct_insert_tt, use_rt_doc_num)
              VALUES ({$postingPrincipalTO->puid},'Y','N','N','Y','Y')";

        $this->errorTO = $this->dbConn->processPosting($sql,'');
        if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {
          $this->errorTO->description = "Failed to load principal_preference defaults";
          return $this->errorTO;
        }

        if(CommonUtils::isDepotUser()){
          $this->errorTO = $this->autoAllocatePrincipal($postingPrincipalTO->puid, $_SESSION['user_id']);
          if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->description = "Failed to auto allocate Principal to User!";
            return $this->errorTO;
          }
        }

      }

        // failed validation
      } else {
        return $this->errorTO;
      }
      return $this->errorTO;
   }


   public function autoAllocatePrincipal($toPrincipalId, $userId){


     if(isset($_SESSION['principal_id']) && is_numeric($_SESSION['principal_id']) && $_SESSION['principal_id'] > 0){

      //use the current logged in principal as the copy from
      $fromPrincipalId = $_SESSION['principal_id'];


      //ALLOCATE DEPOTS
      $sql="INSERT INTO user_principal_depot(`user_id`, `depot_id`, `principal_id`)
            (
                    SELECT `user_id`, `depot_id`, {$toPrincipalId}
                    FROM user_principal_depot d
                    where d.principal_id = {$fromPrincipalId} and d.user_id = {$userId}
            )
            ";

      $this->errorTO = $this->dbConn->processPosting($sql,'');
      if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {
        $this->errorTO->description = "Failed to copy user depots!";
        return $this->errorTO;
      }


      //ALLOCATE ROLES
      $sql="INSERT INTO user_role(`user_id`, `role_id`, `entity_uid`)
            (
                    SELECT `user_id`, `role_id`, {$toPrincipalId}
                    FROM user_role r
                    where r.entity_uid = {$fromPrincipalId} and r.user_id =  {$userId}
            )
            ";

      $this->errorTO = $this->dbConn->processPosting($sql,'');
      if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {
        $this->errorTO->description = "Failed to copy user depots!";
        return $this->errorTO;
      }

     } else {
       $this->errorTO->type = FLAG_ERRORTO_ERROR;
       $this->errorTO->description = "Failed to auto allocate Principal to User - Invalid Current Principal";
       return $this->errorTO;
     }

     return $this->errorTO;


   }




}

?>