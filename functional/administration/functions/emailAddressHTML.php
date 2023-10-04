<?php


include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'libs/BroadcastingUtils.php');
include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');
include_once($ROOT.$PHPFOLDER.'DAO/AdministrationDAO.php');
include_once($ROOT.$PHPFOLDER.'libs/FileParser.php');


//SESSION
if (!isset($_SESSION)) session_start;
$userId=$_SESSION['user_id'];
$principalId = $_SESSION['principal_id'];
$systemId = $_SESSION['system_id'];


$postHTMLSTR = (isset($_POST["HTMLSTR"])) ? $_POST["HTMLSTR"] : false;
$postEMAILADD = (isset($_POST["EMAILADD"])) ? $_POST["EMAILADD"] : false;
$postSUBJECT = (isset($_POST["SUBJECT"])) ? $_POST["SUBJECT"] : false;


$dbConn = new dbConnect();
$dbConn->dbConnection();
$adminDAO = new AdministrationDAO($dbConn);
$returnMessages=new ErrorTO;


if($postHTMLSTR == false || $postEMAILADD == false){

  $returnMessages->type=FLAG_ERRORTO_ERROR;
  $returnMessages->description="A required paramater is missing.";
  print(CommonUtils::getJavaScriptMsg($returnMessages));
  return;

}


if(!strlen($postHTMLSTR) > 50){

  $returnMessages->type=FLAG_ERRORTO_ERROR;
  $returnMessages->description="HTML is too small. (" . strlen($postHTMLSTR) . ")";
  print(CommonUtils::getJavaScriptMsg($returnMessages));
  return;

}

//email bigger than 2MB - TOO BIG........
if(strlen($postHTMLSTR) > 2097152){

  $returnMessages->type=FLAG_ERRORTO_ERROR;
  $returnMessages->description="HTML is too big. (" . strlen($postHTMLSTR) . ")";
  print(CommonUtils::getJavaScriptMsg($returnMessages));
  return;

}


if(!preg_match(GUI_PHP_EMAIL_REGEX,$postEMAILADD)) {

  $returnMessages->type=FLAG_ERRORTO_ERROR;
  $returnMessages->description="Email Address must be of recognisable structure.";
  print(CommonUtils::getJavaScriptMsg($returnMessages));
  return;

}


# and grab the settings from the class via tokenizer...
$sysArr = $adminDAO->getSystemByUid($systemId);
$fparse = new FileParser();
$conArr = $fparse->classConstTokenizer($ROOT . $PHPFOLDER . 'properties/conventions/'.$systemId .'_'. strtolower($sysArr[0]['name']) . '.php');
if(count($conArr)==0){
  Echo "System Error","Error in loading const from system SNC file!";
  return;
}


$systemTitle = $conArr['title'];
$systemFromEmail = $conArr['admin_email_addr'];
$systemLogo = $conArr['logo_path'];
$fromArr = ($systemId==SYS_KWELANGA) ? array() : array('alias' => $systemTitle, 'addr' => $systemFromEmail);

$body = '<div align="right"><img src="'.DIR_DATA_NON_FTP_FROM.$PHPFOLDER. $systemLogo.'" alt="" ></div><hr><br><br>';
$body .= 'Dear User,<br>
          <br>
          Please find below the page/information from Kwelanga Solutions that was sent to you by <strong>'.$_SESSION['full_name'].'</strong> for you to receive.<br>
          <br>
          Regards,<br>
          ' . $systemTitle . '<br>
          <hr>';
$body .= $postHTMLSTR;


$returnMessages = BroadcastingUtils::sendEmailHTMLEmbedded($postSUBJECT,  array($postEMAILADD), $body, '', $fromArr);
echo CommonUtils::getJavaScriptMsg($returnMessages);


?>