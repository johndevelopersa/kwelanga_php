<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');
include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER."properties/Constants.php");
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');


class PrincipalProductCatDAO {
	private $dbConn;

	function __construct($dbConn) {
       $this->dbConn = $dbConn;
       $this->errorTO = new ErrorTO;
  }
  
//******************************************************************************************************************************************
public function GetProductCat($PrincipalUID){

            $sql = "SELECT *
                    FROM principal_product_category ppc
                    WHERE ppc.principal_uid = ". mysqli_real_escape_string($this->dbConn->connection, $PrincipalUID) ."
                    ORDER BY ppc.comments";
            $pCat = $this->dbConn->dbGetAll($sql);
          
     return $pCat;	
  	 }
  	 


//******************************************************************************************************************************************
//******************************************************************************************************************************************
//******************************************************************************************************************************************
//******************************************************************************************************************************************
//******************************************************************************************************************************************





//************************************************END END END END END END END******************************************************************************************
}