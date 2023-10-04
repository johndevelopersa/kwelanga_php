<?php

class Audit {

	var $_dbConn;

// Pass a handle to the resource when creating the object - that way you can share a db conntection.
// Opening a new db connection is slow and costly - this way you can 're-use' them and speed it up.
// If there is a function with the same name as the class - it's a constructor and runs when you create the object.
	function Audit( $dbConn ) {
		$this->_dbConn = $dbConn;
	}

   function UpdateLogin ($userid) {
       $this->_dbConn->dbinsQuery("UPDATE  `users` SET  lastlogin=now() WHERE `UID`=".$userid);
   	}


/*    public $uid            	    ;
	public $userid           	;
	public $operationtype       ;
  	public $objectid          	;
  	public $previousvalue      	;
*/


  	function AuditAction( $AuditTO ) {

        echo "SELECT uid FROM `audittypes` WHERE `description` = '" . $AuditTO->operationtype . "' ";

	    $this->_dbConn->dbinsQuery ("SELECT uid FROM `audittypes` WHERE `description` = '" . $AuditTO->operationtype . "' " ) ;

 		if ($auditline = mysql_fetch_array($this->_dbConn->dbQueryResult, MYSQL_ASSOC))  {
 			$audittypeid = $auditline[uid];
  		}
 		else {
 			$this->_dbConn->dbinsQuery ("INSERT INTO  `audittypes` ( `description`) VALUES ( ".
		       		 		 		   "'" . $AuditTO->operationtype . "' ) ") ;

	        $this->_dbConn->dbinsQuery ("SELECT uid FROM `audittypes` WHERE `description` = '" . $AuditTO->operationtype . "' " ) ;

 		    if ($auditline = mysql_fetch_array($this->_dbConn->dbQueryResult, MYSQL_ASSOC))  {
 		   	$audittypeid = $auditline[uid];
      		}

    	}


       	$this->_dbConn->dbinsQuery ("INSERT INTO  `auditlog` ( `actioned`,`userid`, `audittypeid`, `objectid`, `previousvalue`".
	              	         ") VALUES ( NOW()," .
		       		 		 "'" . $AuditTO->userid         . "', ".
		            		 "'" . $audittypeid             . "', ".
		            		 "'" . $AuditTO->objectid       . "', ".
		            		 "'" . $AuditTO->previousvalue  . "' )" ) ;

            echo "";

      		return("audited");
      	}



      	}


?>