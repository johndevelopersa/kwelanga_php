<?php

// "https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/functional/jobs/vendhq_demo/get_users.php";


include_once('ROOT.php');
include_once($ROOT . 'PHPINI.php');
include_once($ROOT . $PHPFOLDER . "DAO/db_Connection_Class.php");
include_once($ROOT . $PHPFOLDER . 'TO/ErrorTO.php');
include_once($ROOT . $PHPFOLDER . "libs/CommonUtils.php");
include_once($ROOT . $PHPFOLDER . 'libs/BroadcastingUtils.php');
include_once($ROOT . $PHPFOLDER . 'libs/GUICommonUtils.php');
include_once($ROOT . $PHPFOLDER . 'libs/api/vendhq/client.php');
include_once($ROOT . $PHPFOLDER . "DAO/PrincipalDAO.php");


//TO DO: store in SSM
$apiKey = "lsxs_pt_dhCVfmsY4VodPazeQKPFmKHmI570ArgO";
$domainPrefix = "bonniebio";

$client = new VendHQClient($apiKey, $domainPrefix);

$userResponse = $client->getUsers();

if(!$userResponse->getSuccess()){
    echo "Error: " . $userResponse->getErrorMessage();
    die();
}

$userArr = $userResponse->getBody();
echo "<pre>";

var_dump($userArr);
