<?php

include_once('ROOT.php');
include_once($ROOT . 'PHPINI.php');
include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");    
include_once($ROOT . $PHPFOLDER . 'DAO/ExceptionThrower.php');


class VoquadoParms {

  function __construct($dbConn) {
  	
  	  $this->dbConn = new dbConnect();
      $this->dbConn->dbConnection();
  }
// ************************************************************************************************************************************
  public function getPrincipalParams($principalId) {

    $sql = "SELECT *
            FROM  voqado_extract_parameters v
            WHERE v.principal_uid = '"  . mysqli_real_escape_string($this->dbConn->connection, $principalId) ."' ";

            return $this->dbConn->dbGetAll($sql);
  }
// ************************************************************************************************************************************

}

