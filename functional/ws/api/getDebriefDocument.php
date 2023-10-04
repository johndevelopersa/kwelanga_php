<?php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER."properties/Constants.php");
include_once($ROOT.$PHPFOLDER."properties/ServerConstants.php");
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT . $PHPFOLDER . 'DAO/AdministrationDAO.php');
include_once($ROOT . $PHPFOLDER . 'DAO/ApiDAO.php');

$dbConn = new dbConnect();
$dbConn->dbConnection();

if (!isset($_SESSION)) session_start();
$principalId   = $_SESSION['principal_id'];
$userId        = $_SESSION['user_id'];


$adminDAO = new AdministrationDAO($dbConn);
$hasRoleTT = $adminDAO->hasRole($userId,$principalId,ROLE_TRANSACTION_TRACKING);
//$hasRoleManageOrders = $adminDAO->hasRole($userId,$principalId,ROLE_MANAGE_ORDERS);

if(!$hasRoleTT /*|| !$hasRoleManageOrders*/){
    echo json_encode([
        "resultStatus" => "E",
        "resultMessage" => "Unauthorised to access debrief document API".$hasRoleTT.":".$hasRoleManageOrders,
    ]);

    exit;
  }

$data = file_get_contents('php://input');
$JSON = json_decode($data, true);

$documentNo = $JSON["documentNo"];
$principalId = $JSON["principalId"];

if (!preg_match("/^[a-zA-Z0-9]+$/", $documentNo)) {
    echo json_encode([
                        "resultStatus" => "E",
                        "resultMessage" => "Invalid Document No passed",
                    ]);
     
     exit;
}
if (!preg_match("/^[0-9]+$/", $principalId)) {
    echo json_encode([
                        "resultStatus" => "E",
                        "resultMessage" => "Invalid Principal Id passed",
                    ]);
     
     exit;
}

$allowedPrincipals = [ $_SESSION['principal_id'] ];
if (isset($_SESSION["allowed_principals"])) $allowedPrincipals = unserialize($_SESSION["allowed_principals"]);

    if (!in_array($principalId, $allowedPrincipals)) {
        echo json_encode([
            "resultStatus" => "E",
            "resultMessage" => "Invalid or unauthorised Principal Id passed",
        ]);

    exit;
}

$apiDAO = new APIDAO($dbConn);
$doc = $apiDAO->getDebriefDocument($principalId, $documentNo);

if (count($doc) != 1) {

    echo json_encode([
        "resultStatus" => "E",
        "resultMessage" => "Invalid Document No passed or Document not Invoiced ".$documentNo+":"+$principalId,
    ]);

    exit;

}

echo json_encode([
    "resultStatus" => "S",
    "resultMessage" => "Successful",
    "data" => ["dmUId" => $doc[0]["dm_uid"]]
]);

exit;


?>