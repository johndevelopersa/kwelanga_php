<?php

/*---------------------------------------------------------------
 *
 * VIEW IMPORTED EMAILS
 *
 *---------------------------------------------------------------*/

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT . $PHPFOLDER . 'libs/pop3.php');
require($ROOT . $PHPFOLDER . 'libs/emailParser.php');
include_once($ROOT.$PHPFOLDER."libs/GUICommonUtils.php");
include_once($ROOT.$PHPFOLDER."libs/CommonUtils.php");
include_once($ROOT.$PHPFOLDER."elements/datePickerElement.php");


// passed POST Fields
if (!isset($_SESSION)) session_start();
if (isset($_POST["FILTERLIST"])) { $postFilterPPList = urldecode($_POST["FILTERLIST"]); $postFilterPPList=explode(',',$postFilterPPList); } else $postFilterPPList='';
if (isset($_POST["PAGEDEST"])) $postPAGEDEST = $_POST["PAGEDEST"]; else $postPAGEDEST="divArea";
if (isset($_POST["CALLBACK"])) $postCALLBACK = $_POST["CALLBACK"]; else $postCALLBACK="";
if (isset($_SESSION["USERID"])) $postUSERID=$_SESSION["USERID"]; else $postUSERID="0";

$postFROMDATE = (isset($_POST["FROMDATE"])) ? htmlspecialchars($_POST["FROMDATE"]) :  CommonUtils::getUserDate();
$postTODATE = (isset($_POST["TODATE"])) ? htmlspecialchars($postTODATE=$_POST["TODATE"]) : CommonUtils::getUserDate();
$postDATETYPE = (isset($_POST["DATETYPE"])) ?  htmlspecialchars($postDATETYPE=$_POST["DATETYPE"]) : "SUCCESS";

$_POST["RBNAME"] = '';

$rootFolder = $ROOT . 'archives/email/orders/';
$fileArr = array();
$filesTotalSize = 0;
$fldFilterPPListname = "PPListFilter"; // the names of the filter fields
$fldFilterPPListUsageArr = array(1=>"N","Y","Y","Y","Y","Y","Y","Y");
$fldFilterPPListSizeArr = array(1=>"0","5","5","5","5","5","5","5");
$FCArr = array(); // strip off the columns we want. Remember that fetch_array doubles up everything.
$headers = array("Date","From Address", "Subject", "Attachments", "Folder", "File", "Size");
$valArr = array();

function formatFromBytes($size) {
  $units = array(' B', ' KB', ' MB', ' GB', ' TB');
  for($i = 0; $size >= 1024 && $i < 4; $i ++){
    $size /= 1024;
  }
  return round($size, 2) . $units [$i];
}




chdir($rootFolder);

foreach ( new RecursiveIteratorIterator(new RecursiveDirectoryIterator('.')) as $file ) {

  $isEMLFile = fnmatch('*.eml', $file, FNM_CASEFOLD);

  if($isEMLFile){


    $path = str_replace('\\', '/', str_replace(basename($file), '', $file));
    if($pathPos = strpos($path,'/')!==FALSE){
      $path = substr($path,($pathPos+1));
    }
    $fileDateRaw = filectime($path . basename($file));
    $fileDate = strtotime(date('Y-m-d', $fileDateRaw));
    $fromDate = strtotime($postFROMDATE);
    $toDate = strtotime($postTODATE);

    $folderType = (strpos(strtoupper(str_replace(basename($file), '', $file)), $postDATETYPE)!==FALSE)?true:false;
    $fileDateValid = (($fileDate >= $fromDate) && ($fileDate <= $toDate)) ? true : false;


    if( $folderType && $fileDateValid){

      $fileSize = filesize($file);
      $filesTotalSize += $fileSize;


      $data = file_get_contents($path . basename($file));
      $parseProcess = new emailParser();
      $parResult = $parseProcess->parseRawEmailString($data);  //returns TO and array of parsed email.

      $emlArr = array();
      if($parResult->type == FLAG_ERRORTO_SUCCESS){

        $eDArr = $parResult->object;
        $emlArr['subject'] = $eDArr['Subject'];
        $emlArr['from'] = $eDArr['From'][0]['address'];
        $emlArr['to'] = $eDArr['To'][0]['address'];  //not in use
        $emlArr['date'] = $eDArr['Date'];  //not in use

        $attachmentArr = array();
        if(isset($eDArr['Attachments'])){
          foreach($eDArr['Attachments'] as $att){
            $attachmentArr[] = $att['FileName'];
          }
        }
        $emlArr['attachment_list'] = join(';', $attachmentArr);

      } else {

        $emlArr['attachment_list'] = 'ERROR';
        $emlArr['date'] = 'ERROR';
        $emlArr['from'] = 'ERROR';
        $emlArr['to'] = 'ERROR';
        $emlArr['subject'] = 'ERROR';

      }

      $fileArr[$fileDateRaw.'_'.uniqid()] = array(
                          'file' => basename($file),
                          'path' => $path,
                          'size' => $fileSize,
                          'eml' => $emlArr
                        );
    }
  }

}

$fileTotalCnt = count($fileArr);
$filesTotalSize = formatFromBytes($filesTotalSize);

krsort($fileArr);

class PPTbl {

  public $null;
  public $date;
  //public $toAddress;
  public $fromAddress;
  public $subject;
  public $attachmentList;
  public $folder;
  public $emlDownload;
  public $emlSize;

}

foreach ($fileArr as $f) {

  $class = new PPTbl;
  $class->null = '';
  $class->date = date('Y-m-d', strtotime($f['eml']['date']));
  //$class->toAddress = $f['eml']['to'];
  $class->fromAddress = $f['eml']['from'];
  $class->subject = $f['eml']['subject'];
  $class->attachmentList = '<STRONG style="color:#047;">' . join('<BR>', explode(';', $f['eml']['attachment_list'])) . '</STRONG>';
  $class->folder = $f['path'];
  $class->emlDownload = '<A href="' . $rootFolder . $f['path'] . $f['file'] . '" >Download EML File</a>';
  $class->emlSize =  formatFromBytes($f['size']);
  $valArr[] = 0;
  $FCArr[]=$class;
}

$pArr = GUICommonUtils::applyFilter($FCArr,$postFilterPPList);




/*--------------------------------------------------------------------------------------------------
 *
 *     SCREEN OUTPUT
 *
 *-------------------------------------------------------------------------------------------------*/

DatePickerElement::getDatePickerLibs();
echo <<<EOF
<link href="{$DHTMLROOT}{$PHPFOLDER}/css/default.css" rel="stylesheet" type="text/css">
<DIV align="center" id="divArea">
<script type="text/javascript" language="javascript" src="{$DHTMLROOT}{$PHPFOLDER}js/jquery.js"></script>
<script type="text/javascript" language="javascript" src="{$DHTMLROOT}{$PHPFOLDER}js/dops_global_functions.js"></script>
<BR>
<H1 style="color:#047;">EDI Email Orders</H1>
orderslive@retailtrading.net | EML Files: {$fileTotalCnt}| Total EML Size: {$filesTotalSize}<BR>
<BR><BR>
EOF;


echo "<TABLE class='tblReset' height='80'>";
echo "<TR>";
  echo "<TD width='20'>From:</TD>";
  echo "<TD width='100'>";
          DatePickerElement::getDatePicker("FROMDATE",$postFROMDATE);
  echo "</TD>";
  echo "<TD width='20'>To:</TD>";
  echo "<TD width='100'>";
          DatePickerElement::getDatePicker("TODATE",$postTODATE);
  echo "</TD>";
echo "</TR><TR>";
  echo "<TD colspan='4'>E-mail Filter: ";

  echo '<SELECT id="DATETYPE" name="DATETYPE">';
  echo '<OPTION value="SUCCESS" ',($postDATETYPE=='SUCCESS')?('SELECTED'):(''),'>Successful E-mails';
  echo '<OPTION value="JUNK" ',($postDATETYPE=='JUNK')?('SELECTED'):(''),'>Rejected E-mails';
  echo '</SELECT>';
  echo "</TD>";
echo "</TABLE>";


echo '<BR><BR>';


echo '<TABLE>';

  // filter row
  GUICommonUtils::getFilterFields($fldFilterPPListname,
                                  $fldFilterPPListUsageArr,
                                  $fldFilterPPListSizeArr,
                                  $postFilterPPList,
                                  $postPAGEDEST,
                                  "+'&USERID=".$postUSERID."+&FROMDATE='+document.getElementById(\"FROMDATE\").value+'&TODATE='+document.getElementById(\"TODATE\").value+'&DATETYPE='+convertElementToArray(document.getElementsByName(\"DATETYPE\"))",
                                  $ROOT.$PHPFOLDER.'/scripts/phpscripts/'.basename(__FILE__)
                                  );
  // the data
  GUICommonUtils::outputRBTable ($headers,
                                 $pArr,
                                 '',
                                 $valArr,
                                 '',
                                 '',
                                 "",
                                 "");
echo '</TABLE></DIV>';


