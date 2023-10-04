<?php


include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER."libs/GUICommonUtils.php");
include_once($ROOT.$PHPFOLDER."libs/CommonUtils.php");
include_once ($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER.'properties/dbSettings.inc');
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');





/*------------- CLEAN TWO OCEANS MARKETING FILES ----------------*/
$path = DIR_DATA_SURESERVER_NON_FTP_FROM . 'tom/';
if(is_dir($path)){
  $dirArr = scandir($path);
  foreach($dirArr  as $f){
    if(substr($f,0,6)=='INVPOL'){
        $bkPath = CommonUtils::createBkupDirs($path . 'processedError/');
        $r = @rename($path . $f, $bkPath . $f);
        if($r === true){
          echo 'backedup : ' . $f . ' --> ' . $bkPath . $f . '<br>';
        }
    }
  }
}
/*----------------------------------------------------------------*/


echo '[***EOS***]'; //this must be the very last line of this script.

?>
