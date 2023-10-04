<?php

include_once('ROOT.php');
include_once($ROOT . 'PHPINI.php');
include_once($ROOT . $PHPFOLDER . "DAO/db_Connection_Class.php");
include_once($ROOT . $PHPFOLDER . 'properties/dbSettings.inc');
include_once($ROOT . $PHPFOLDER . 'libs/EncryptionClass.php');
include_once($ROOT . $PHPFOLDER . 'DAO/AdministrationDAO.php');

if (!isset($_SESSION)) session_start();

// taskman email viewer link session revert
if (isset($_SESSION["revert"])) {
    include_once($ROOT . $PHPFOLDER . 'libs/CommonUtils.php');
    CommonUtils::revertSession();
}

$eC = new EncryptionClass();

$firstTime = false;
if (isset($_POST['username'])) {
    $firstTime = true;
    $uN = $eC->hexToString($_POST['username']);
    $pW = $eC->hexToString($_POST['password']);


    // it is javascript encrypted using DES so decrypt...
    $username = trim((string)$eC->des(ENCRYPT_JS_KEY, $uN, 0, 0, null, null)); // for some reason if i don't trim it, then var_dump((string) $username) says it is a string(n+1) instead of string(n) !
    $password = trim((string)$eC->des(ENCRYPT_JS_KEY, $pW, 0, 0, null, null));

    // now convert to session and db values
    $eR_sk = $eC->encrypt(ENCRYPT_SESSION_KEY, $password, ENCRYPT_PWD_LENGTH);
    $eR_dbk = $eC->encrypt(ENCRYPT_DB_KEY, $password, ENCRYPT_PWD_LENGTH);
    $_SESSION['username'] = $username;
    $_SESSION['password'] = $eR_sk;
    $systemId = $_SESSION['system_id'] = (isset($_POST['system'])) ? ($_POST['system']) : (0);
} else {
    $username = (isset($_SESSION['username'])) ? $_SESSION['username'] : "";
    $password = (isset($_SESSION['password'])) ? $_SESSION['password'] : "";
    $systemId = (isset($_SESSION['system_id'])) ? $_SESSION['system_id'] : "";
    // convert the password to db encryption from session encryption
    $pwd2 = $eC->decrypt(ENCRYPT_SESSION_KEY, $password);
    unset($password); // for some reason, if i assign above decrypt directly to password, then $_SESSION gets modified !!! maybe try the trim as in above ?
    $password = $pwd2;
    $eR_dbk = $eC->encrypt(ENCRYPT_DB_KEY, $password, ENCRYPT_PWD_LENGTH);
}

if (!isset($dbConn)) {
    $dbConn = new dbConnect();
    $dbConn->dbConnection();
}

$dbConn->dbQuery("SELECT *
                  FROM users
                  WHERE username = BINARY '" . mysqli_real_escape_string($dbConn->connection, $username) . "'
                  AND password = BINARY '" . mysqli_real_escape_string($dbConn->connection, $eR_dbk) . "'
                  AND system_uid = '" . mysqli_real_escape_string($dbConn->connection, $systemId) . "'
                  AND deleted=0");

if (mysqli_num_rows($dbConn->dbQueryResult) > 0) {
    $row = mysqli_fetch_array($dbConn->dbQueryResult, MYSQLI_ASSOC);
}

//If no records are found - remove session variables and redirect to login screen
if (
    (mysqli_num_rows($dbConn->dbQueryResult) == 0) ||
    (($firstTime === false) && (md5($row["uid"] . $password . ENCRYPT_SESSION_KEY . $row["full_name"]) != $_SESSION['user_key']))
) {
    session_unset();
    session_destroy();
    header('location:' . ($_SERVER['REQUEST_SCHEME']??'https') . '://'. $_SERVER['HTTP_HOST'] . '/systems/kwelanga_system/kwelangaweb/index.php?error=Y');
    exit;

} else {

    // must be set every request.
    if ((isset($_SESSION["principal_type"])) && ($_SESSION["principal_type"] == PT_DEPOT)) {
        // principalAlias is a depot user who is performing a function for one of their principals as that principal
        $principalAliasId = ((isset($_REQUEST["pAlias"])) ? trim(mysqli_real_escape_string($dbConn->connection, $_REQUEST["pAlias"])) : ""); // depot users only, can be a get or a post so use request
        $principalAliasName = ((isset($_REQUEST["pAliasName"])) ? trim(mysqli_real_escape_string($dbConn->connection, $_REQUEST["pAliasName"])) : ""); // depot users only, can be a get or a post so use request

        // validate against allowed principals
        if (isset($_SESSION["allowed_principals"])) $apArr = unserialize($_SESSION["allowed_principals"]); else $apArr = array();
        if ($principalAliasId == "") {
            $_SESSION["principal_alias_id"] = $principalAliasId;
            $_SESSION["principal_alias_name"] = $principalAliasName;
        } else if (in_array($principalAliasId, $apArr)) {
            $_SESSION["principal_alias_id"] = $principalAliasId;
            $_SESSION["principal_alias_name"] = $principalAliasName;
        } else {
            session_unset();
            session_destroy();
            trigger_error('Illegal passing of principal alias Id in access control !', E_USER_ERROR);
        }
    } else {
        $_SESSION["principal_alias_id"] = "";
    }

    // only set session if first time to save time.
    if ($firstTime === true) {
        $_SESSION['user_id'] = $row["uid"];
        $_SESSION['staff_user'] = $row["staff_user"];
        $_SESSION['user_category'] = $row["category"];  //DEPOT OR PRINCIPAL TYPE
        $_SESSION['admin_user'] = $row["admin_user"];
        $_SESSION['full_name'] = $row["full_name"];
        $_SESSION['category'] = $row["category"];
        $_SESSION['user_email'] = $row["user_email"];
        $_SESSION['user_key'] = md5($row["uid"] . $password . ENCRYPT_SESSION_KEY . $row["full_name"]); // special key guards against fudging the session while the user is still logged in. Useless if logs out and then back in.

        $adminDAO = new AdministrationDAO($dbConn);
        $sysArr = $adminDAO->getSystemByUid($systemId);
        $_SESSION['system_name'] = strtolower(trim($sysArr[0]['name']));
    }
    // change password if firsttime
    if ($password == NEW_USER_PWD) {
        echo '<meta http-equiv="refresh" content="0;url=' . $ROOT . $PHPFOLDER . 'functional/main/changePassword.php">';
        exit;
    }


    /* 45 DAYS PASSWORD RESET
     *
     */
    //Check if there is a date and if user has days > 0 | 0 = disabled.
    if (isset($row['last_password_change_date']) && isset($row['password_days']) && $row['password_days'] > 0) {

        $lastpwdSet = $row['last_password_change_date'];
        $expDate = date("Y-m-d", strtotime($lastpwdSet) + (86400 * $row['password_days'])); //add # days => 86400 seconds in a day.
        $todaysDate = date("Y-m-d");

        //echo $expDate;
        //echo $todaysDate;

        if ($expDate < $todaysDate) {
            echo '<meta http-equiv="refresh" content="0;url=' . $ROOT . $PHPFOLDER . 'functional/main/changePassword.php?expiredpwd=1">';
            exit;
        }
    }
}

$dbConn->dbClose();
