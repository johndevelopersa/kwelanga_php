<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER."properties/dbSettings.inc");

/*
 * USEFUL Cmds in SQLExpress :
 *
 * dbcc useroptions
 *   ~ list useroption variables eg date format
 *
 * Setting a date format in SQLExpress is complicated :-
 * 1. To set default server date, right click the server and change the language (changed date format too)
 *     But this only applies to new users created under that default
 * 2. set dateformat dmy only then works per session
 * 3. to change the user logins language, use :
 *     sp_defaultlanguage 'login_name', 'language_name'
 *     sp_defaultlanguage 'MW-DESKTOP\mark', 'British English'
 *
 *     or,
 *     ALTER LOGIN Fathima WITH DEFAULT_LANGUAGE = Arabic;
       GO
 *
 * To Find Servers :
 *   At a command line:
      SQLCMD -L
      or
      OSQL -L
    or SELECT @@SERVERNAME, @@SERVICENAME
    gives you : MW-DESKTOP, MSSQLSERVER


    select SYSTEM_USER
    ~ shows current user

 */

class DBConnect_3rdParty_SQLServer  {

	public $dbQueryResult;
	public $dbQueryResultRowCount;
	public $connection;
	public $dbQueryHasRows=false;

	function dbConnection_ICTechnology()
	{
	  $this->connection = sqlsrv_connect( $serverName=SERVERNAME_ICTECHNOLOGY, $connectionInfo=array("Database"=>DATABASE_ICTECHNOLOGY,"UID"=>USERNAME_ICTECHNOLOGY,"PWD"=>PASSWORD_ICTECHNOLOGY)) or  die( print_r( sqlsrv_errors(), true));
		$this->dbQuery("SET IMPLICIT_TRANSACTIONS ON"); // requires COMMIT TRANSACTION to commit
		// $this->dbQuery("SET DATEFORMAT ymd"); // requires COMMIT TRANSACTION to commit
	}

	function dbQuery($query)
	{
		$result = sqlsrv_query($this->connection,$query) or die ("Error in query  " . $query . var_dump(sqlsrv_errors()));
		$this->dbQueryResult = $result;

		$this->dbQueryResultRowCount = sqlsrv_num_rows($result); // this doesnt work as it works only with scrollable resultset (scrollable option)
		$this->dbQueryHasRows = sqlsrv_has_rows($result);
	}

	function dbClose()
	{
		$closeConn = sqlsrv_close($this->connection);
	}

	function dbGetLastInsertId() {
		$result=sqlsrv_query($this->connection, "select SCOPE_IDENTITY() as uid"); // @@IDENTITY fetches from current session, not last operation
		$row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC);
		$uid = $row['uid'];
		return $uid;
	}

  function dbGetAll($sql) {
		$result = sqlsrv_query($this->connection,$sql) or die ("Error in query  " . $sql . var_dump(sqlsrv_errors()));
		$this->dbQueryResult = $result;

		$this->dbQueryResultRowCount = sqlsrv_num_rows($result); // this doesnt work as it works only with scrollable resultset (scrollable option)
		$this->dbQueryHasRows = sqlsrv_has_rows($result);

		$arr=array();
    if ($this->dbQueryHasRows) {
      while($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)){
        $arr[] = $row;
      }
    }

    sqlsrv_free_stmt($this->dbQueryResult);

    return $arr;
  }

  public function processPosting($sql) {
    global $ROOTPHPFOLDER;
    include_once($ROOTPHPFOLDER.'TO/ResultTO.php');
    $resultTO = new resultTO;

    $this->dbQuery("SET IMPLICIT_TRANSACTIONS ON"); // requires COMMIT TRANSACTION to commit
    $result = sqlsrv_query($this->connection,$query) or die ("Error in query  " . $query . var_dump(sqlsrv_errors()));

    if ((!$result) || (sqlsrv_errors()!=NULL)) {
      $resultTO->type=FLAG_STATUS_ERROR;
      $resultTO->description="Error executing sqlsrv_query in processPosting: <br>".$sql."<br>".var_dump(sqlsrv_errors());
      return $resultTO;
    }

    $this->dbQueryResultRowCount = sqlsrv_rows_affected ($this->connection);

    $resultTO->type=FLAG_STATUS_SUCCESS;
    $resultTO->description="Request Successful";

    return $resultTO;
  }

  public function beginTransaction() {
    return sqlsrv_begin_transaction($this->connection);
  }

  public function commitTransaction() {
    return sqlsrv_commit($this->connection);
  }

  public function rollbackTransaction() {
    return sqlsrv_rollback($this->connection);
  }

}

?>