<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER."libs/CommonUtils.php");
include_once($ROOT.$PHPFOLDER."libs/ValidationCommonUtils.php");
include_once($ROOT.$PHPFOLDER."DAO/AdministrationDAO.php");
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');

/*
 * NOTE : The validation should return IMMEDIATELY if an error type occurred (required for the way screen is processed)
 */

class PostDepotDAO {
	public $errorTO;
	private $dbConn;

	function __construct($dbConn) {
		$this->dbConn = $dbConn;
		$this->errorTO = new ErrorTO;
    }

    /*
     *
     *
     */

	 public function postDepotValidation($postingDepotTO) {
    	global $ROOT; global $PHPFOLDER;
    	include_once($ROOT.$PHPFOLDER."DAO/AdministrationDAO.php");
    	include_once($ROOT.$PHPFOLDER."DAO/ProductDAO.php");

    	// get user. don't pass it because this is more secure.
    	if (!isset($_SESSION)) session_start();
    	$userId = $_SESSION['user_id'];
    	$principalId = $_SESSION['principal_id']; // used for hasRole validation

	     //Second level of Role Check
    	$adminDAO = new AdministrationDAO($this->dbConn);
      if($postingDepotTO->DMLType == 'INSERT' && (!$adminDAO->hasRole($userId, $principalId, ROLE_ADD_DEPOT))){
    	  $this->errorTO->type=FLAG_ERRORTO_ERROR;
    	  $this->errorTO->description="You do not have permissions to Add New Depots!";
    	  return false;
      } elseif($postingDepotTO->DMLType == 'UPDATE' && (!$adminDAO->hasRole($userId, $principalId, ROLE_MODIFY_DEPOT))){
        $this->errorTO->type=FLAG_ERRORTO_ERROR;
  		  $this->errorTO->description="You do not have permissions to Change Depots!";
  		  return false;
      }

    	if (!ValidationCommonUtils::checkPostingType($postingDepotTO->DMLType)) return false;

  		if (($postingDepotTO->DMLType=="INSERT") && (strlen($postingDepotTO->depotCode)>2)) {
  			$this->errorTO->type=FLAG_ERRORTO_ERROR;
  			$this->errorTO->description="Depot Code can be a max of 2 chars.";
  			return false;
  		};

  		if (!in_array($postingDepotTO->WMS,array("Y","N"))) {
  		  $this->errorTO->type=FLAG_ERRORTO_ERROR;
  			$this->errorTO->description="WMS has an invalid value";
  			return false;
  		}

  		if (!in_array($postingDepotTO->deliveryNote,array("Y","N"))) {
  		  $this->errorTO->type=FLAG_ERRORTO_ERROR;
  		  $this->errorTO->description="Delivery Note has an invalid value";
  		  return false;
  		}

  		return true;

    }

   public function postNewDepot($postingDepotTO, $userId) {

		$resultOK = $this->postDepotValidation($postingDepotTO);
		$depotEmailList = (!empty($postingDepotTO->depotEmailList)) ? ("'" . mysqli_real_escape_string($this->dbConn->connection, $postingDepotTO->depotEmailList) . "'") : ('NULL');
    	if ($resultOK) {

    		if ($postingDepotTO->DMLType=="DELETE") {
	    		$sql="update `depot`".
	    					" set deleted=1 ".
	    					"where" ;
    		} else if ($postingDepotTO->DMLType=="INSERT") {
    			$sql = "INSERT INTO `depot`(`code`,`name`,`depot_email_list`,`wms`,`system_uid`,`skip_inpick_stage`,`charge`,`paper_charge`, delivery_note)
    				  VALUES ('" .
    				  	mysqli_real_escape_string($this->dbConn->connection, $postingDepotTO->depotCode) . "','".
    				  	mysqli_real_escape_string($this->dbConn->connection, $postingDepotTO->depotName) . "', " .
    				  	$depotEmailList . ", " .
    				  	"'{$postingDepotTO->WMS}', " .
    				  	$_SESSION['system_id'] . ", ".
    				  	"'{$postingDepotTO->skipInPickStage}'". ", ".
                "'$postingDepotTO->depotCharge" . "', " .
                "'$postingDepotTO->depotPaperCharge" . "', " .
                "'$postingDepotTO->depotCharge" . "' " .
    				  ")";
    		} else if ($postingDepotTO->DMLType=="UPDATE") {
     			$sql="UPDATE `depot`
     				 SET
     				 	`name` = '" . mysqli_real_escape_string($this->dbConn->connection, $postingDepotTO->depotName) . "',
     				 	`depot_email_list` = " . $depotEmailList . ",
							`wms` = '{$postingDepotTO->WMS}',
							`skip_inpick_stage` = '{$postingDepotTO->skipInPickStage}',
              `charge` = '" .mysqli_real_escape_string($this->dbConn->connection, $postingDepotTO->depotCharge) . "',
              `paper_charge` = '" .mysqli_real_escape_string($this->dbConn->connection, $postingDepotTO->depotPaperCharge) . "',
              delivery_note = '" .mysqli_real_escape_string($this->dbConn->connection, $postingDepotTO->deliveryNote) . "'
     				 where
     				 	uid = " . mysqli_real_escape_string($this->dbConn->connection, $postingDepotTO->depotUid) . "";
    		}

			$this->errorTO=$this->dbConn->processPosting($sql,$postingDepotTO->depotCode);

			if ($this->errorTO->type==FLAG_ERRORTO_SUCCESS) {
				if ($postingDepotTO->DMLType=="INSERT") {
					$this->errorTO->description = "Depot Successfully Added - ".$postingDepotTO->depotName;
					$this->errorTO->identifier=$this->dbConn->dbGetLastInsertId();
					return $this->errorTO;
				} else if ($postingDepotTO->DMLType=="DELETE") {
					$this->errorTO->description = "Depot Successfully Deleted - ".$postingDepotTO->depotName;
					return $this->errorTO;
				} else if ($postingDepotTO->DMLType=="UPDATE") {
					$this->errorTO->description = "Depot Successfully Updated - ".$postingDepotTO->depotName;
					return $this->errorTO;
				}
			}

   		} else return $this->errorTO;
		return $this->errorTO;
   }


  public function postDepotDeliveryCalendar($postingDepotDeliveryCalendarTO) {

    if (!in_array($postingDepotDeliveryCalendarTO->DMLType, array('INSERT','UPDATE','DELETE'))){
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description = "Invalid DML type supplied!";
      return $this->errorTO;
    }

    if($postingDepotDeliveryCalendarTO->DMLType == 'INSERT'){

      $sql="INSERT INTO depot_delivery_calendar
            (
              depot_uid,
              timestamp,
              type,
              comment,
              created_datetime,
              created_by_user_uid,
              calendar_date
            ) VALUES (" .
                    mysqli_real_escape_string($this->dbConn->connection, $postingDepotDeliveryCalendarTO->depotUId) . ",".
              "'" . mysqli_real_escape_string($this->dbConn->connection, $postingDepotDeliveryCalendarTO->timestamp) . "',".
              "'" . mysqli_real_escape_string($this->dbConn->connection, $postingDepotDeliveryCalendarTO->type) . "',".
              "'" . mysqli_real_escape_string($this->dbConn->connection, substr($postingDepotDeliveryCalendarTO->comment,0,100)) . "',
               NOW(),
              '" . mysqli_real_escape_string($this->dbConn->connection, $postingDepotDeliveryCalendarTO->createdByUserUId) . "',".
              "'" . mysqli_real_escape_string($this->dbConn->connection, $postingDepotDeliveryCalendarTO->calendarDate) . "'".
            ")";

    } else if($postingDepotDeliveryCalendarTO->DMLType == 'UPDATE'){

      $sql="UPDATE depot_delivery_calendar
            SET
              comment = '" . mysqli_real_escape_string($this->dbConn->connection, substr($postingDepotDeliveryCalendarTO->comment,0,100)) . "',
              created_datetime = NOW(),
              created_by_user_uid = '" . mysqli_real_escape_string($this->dbConn->connection, $postingDepotDeliveryCalendarTO->createdByUserUId) . "'
            WHERE uid = '" . mysqli_real_escape_string($this->dbConn->connection, $postingDepotDeliveryCalendarTO->UId) . "'
              AND depot_uid = '" . mysqli_real_escape_string($this->dbConn->connection, $postingDepotDeliveryCalendarTO->depotUId) . "'
              AND timestamp = '" . mysqli_real_escape_string($this->dbConn->connection, $postingDepotDeliveryCalendarTO->timestamp) . "'
              AND type = '" . mysqli_real_escape_string($this->dbConn->connection, $postingDepotDeliveryCalendarTO->type) . "'";

    } else if($postingDepotDeliveryCalendarTO->DMLType == 'DELETE'){

      $sql="DELETE FROM depot_delivery_calendar
            WHERE uid = '" . mysqli_real_escape_string($this->dbConn->connection, $postingDepotDeliveryCalendarTO->UId) . "'
              AND depot_uid = '" . mysqli_real_escape_string($this->dbConn->connection, $postingDepotDeliveryCalendarTO->depotUId) . "'
              AND timestamp = '" . mysqli_real_escape_string($this->dbConn->connection, $postingDepotDeliveryCalendarTO->timestamp) . "'
              AND type = '" . mysqli_real_escape_string($this->dbConn->connection, $postingDepotDeliveryCalendarTO->type) . "'
            LIMIT 1";
    }

    $this->errorTO = $this->dbConn->processPosting($sql,"");

    if ($this->errorTO->type==FLAG_ERRORTO_SUCCESS) {
      $this->errorTO->description="Successfully updated Delivery Day.";
      if($postingDepotDeliveryCalendarTO->DMLType == 'INSERT'){
        $this->errorTO->identifier=$this->dbConn->dbGetLastInsertId(); // get the UID just created
      }
      return $this->errorTO;
    }

    return $this->errorTO;

  }


}

