<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');

class stockCountByCategoryDAO {
	private $dbConn;

	function __construct($dbConn) {
       $this->dbConn = $dbConn;
    }

    
    function getOTPUserInfo($principleCode) {
        $sql="SELECT u.full_name, u.user_cell , u.password, u.username
        FROM principal p
        LEFT JOIN users u ON u.uid = p.stock_authorisation_user
        WHERE p.principal_code = $principleCode";
                          
        return $this->dbConn->dbGetAll($sql);
    }
}