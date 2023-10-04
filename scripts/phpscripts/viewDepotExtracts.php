<?php

/*---------------------------------------------------------------
 *
 * VIEW DEPOT EXTRACTS
 *
 *---------------------------------------------------------------*/


include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT . $PHPFOLDER . 'libs/pop3.php');
require($ROOT . $PHPFOLDER . 'libs/emailParser.php');
include_once($ROOT.$PHPFOLDER."libs/GUICommonUtils.php");
include_once($ROOT.$PHPFOLDER."libs/CommonUtils.php");
include_once($ROOT.$PHPFOLDER."elements/datePickerElement.php");

$rootFolder = $ROOT . 'archives/exports/';
$bkupFolder = 'bkup';



//RESEND


if(isset($_GET['RESEND'])){

  echo '<br><br><br><pre align="center">';

  if(isset($_GET['FILE']) && count($_GET['FILE'])>0){

    echo '<H1>Resending Depot files...</H1><hr>';

    foreach($_GET['FILE'] as $file){

      if(strpos($file, $bkupFolder)!==false){

        $toFolder = $rootFolder . substr($file,0,strpos($file, $bkupFolder)) ;
        $formFolder = $rootFolder . $file;
        $ok = @rename($formFolder, $toFolder . basename($file));

        echo '<strong>' . basename($file) .  "\t => " . $toFolder . "\t ..." , ($ok===true)?("OK"):("FAILED") , "</strong><br>";
      } else {
        echo '<strong style="color:red">ERROR: ' . $rootFolder .  $file . '</strong><br>';
      }

    }
    echo '<hr>';

  } else {
    echo '<H1>No files selected!</H1>';
  }
  echo '<a href="javascript:history.go(-1)"><H2>BACK</H2></A>';

  die();
}



// passed POST Fields
if (!isset($_SESSION)) session_start();
if (isset($_POST["FILTERLIST"])) { $postFilterPPList = urldecode($_POST["FILTERLIST"]); $postFilterPPList=explode(',',$postFilterPPList); } else $postFilterPPList='';
if (isset($_POST["PAGEDEST"])) $postPAGEDEST = $_POST["PAGEDEST"]; else $postPAGEDEST="divArea";
if (isset($_POST["CALLBACK"])) $postCALLBACK = $_POST["CALLBACK"]; else $postCALLBACK="";
if (isset($_SESSION["USERID"])) $postUSERID=$_SESSION["USERID"]; else $postUSERID="0";
$postDATE = (isset($_POST["DATE"])) ? htmlspecialchars($_POST["DATE"]) :  CommonUtils::getUserDate();
$_POST["RBNAME"] = '';


$fileArr = array();
$filesTotalSize = 0;
$fldFilterPPListname = "PPListFilter"; // the names of the filter fields
$fldFilterPPListUsageArr = array(1=>"N","Y","Y","Y","Y");
$fldFilterPPListSizeArr = array(1=>"0","5","5","5","5");
$FCArr = array(); // strip off the columns we want. Remember that fetch_array doubles up everything.
$headers = array("File", "Folder", "Size", "Download");
$valArr = array();

function formatFromBytes($size) {
  $units = array(' B', ' KB', ' MB', ' GB', ' TB');
  for($i = 0; $size >= 1024 && $i < 4; $i ++){
    $size /= 1024;
  }
  return round($size, 2) . $units [$i];
}


$fileArr = array();
if(isset($_POST['DATE'])){

   $rootArr = scandir($rootFolder);
   foreach($rootArr as $rf){

     if(is_dir($rootFolder.$rf) && $rf != '.' && $rf != '..'){

       $folderDATE = str_replace('-','/',$postDATE);
       $dateFolder = $rootFolder.$rf.'/'.$bkupFolder.'/'.$folderDATE;

       if(is_dir($dateFolder)){

         $folderArr = scandir($dateFolder);
         foreach($folderArr as $file){
          if(fnmatch("RW*", $file, FNM_CASEFOLD)){

            $folderPath = str_replace($rootFolder,'',$dateFolder.'/'.$file);
            $fileDetailArr = array('file' => $file, 'size' => filesize($dateFolder.'/'.$file), 'folder' => str_replace($file,'',$folderPath));
            $fileArr[$folderPath] = $fileDetailArr;

          }
         }

       }
     }
   }
}

krsort($fileArr);


class PPTbl {
  public $path;
  public $file;
  public $folder;
  public $size;
  public $download;
}

foreach ($fileArr as $k=>$f) {

  $class = new PPTbl;
  $class->path = $k;
  $class->file = $f['file'];
  $class->folder = $f['folder'];
  $class->download = '<A href="' . $rootFolder . $k . '" target="_blank" >Download</a>';
  $class->size =  formatFromBytes($f['size']);
  $FCArr[]=$class;
}



$pArr = GUICommonUtils::applyFilter($FCArr,$postFilterPPList);
$valArr=array(); // determine the RB values
foreach ($pArr as $row) {
  $valArr[]=$row->path;
}



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
<H2 style="color:#047;">Depot Extracts</H1>

EOF;


echo "<TABLE class='tblReset' height='40' width='400'>";
echo "<TR>";
  echo "<TD width='20'>Order Date:</TD>";
  echo "<TD width='100'>";
          DatePickerElement::getDatePicker("DATE",$postDATE);
  echo "</TD>";
echo "</TR>";
echo "</TABLE>";

echo '<BR>';


echo '<TABLE>';

  // filter row
  GUICommonUtils::getFilterFields($fldFilterPPListname,
                                  $fldFilterPPListUsageArr,
                                  $fldFilterPPListSizeArr,
                                  $postFilterPPList,
                                  $postPAGEDEST,
                                  "+'&USERID=".$postUSERID."+&DATE='+document.getElementById(\"DATE\").value+''",
                                  $ROOT.$PHPFOLDER.'/scripts/phpscripts/'.basename(__FILE__)
                                  );
  // the data
  GUICommonUtils::outputRBTable ($headers,
                                 $pArr,
                                 'FILE',
                                 $valArr,
                                 '',
                                 'tick',
                                 "",
                                 "");
echo '</TABLE>';

?>


<INPUT type='submit' class='submit' value='Resend Extracts' onclick='submitForm();' >

<script type='text/javascript' >

  function submitForm(){

  totF = $("input[name=FILE]:checked").length;
  if(totF==0 || totF == undefined){
    alert('Nothing selected!');
  } else {
    if(confirm("Are you sure you want to resend these " + totF + " Depot extracts?")){

      var getString = 'RESEND=1';
      $("input[name=FILE]:checked").each(function(index) {
        getString += '&FILE['+index + ']=' + $(this).val();
      });
      location.href = "<?php echo $_SERVER['PHP_SELF']?>?" + getString;



    }
  }

  }

</script>
</DIV>