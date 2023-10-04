<?php

include_once 'ROOT.php';
include_once $ROOT.'PHPINI.php';
include_once $ROOT.$PHPFOLDER.'DAO/db_Connection_Class.php';
include_once $ROOT.$PHPFOLDER.'properties/Constants.php';
include_once $ROOT.$PHPFOLDER.'DAO/MiscellaneousDAO.php';
include_once $ROOT.$PHPFOLDER.'DAO/PostDistributionDAO.php';
include_once $ROOT.$PHPFOLDER.'DAO/BIDAO.php';
include_once $ROOT.$PHPFOLDER.'DAO/PostBIDAO.php';
include_once $ROOT.$PHPFOLDER.'DAO/ExtractDAO.php';
include_once $ROOT.$PHPFOLDER.'libs/CommonUtils.php';
include_once $ROOT.$PHPFOLDER.'libs/GUICommonUtils.php';
include_once $ROOT.$PHPFOLDER.'elements/datePickerElement.php';
include_once $ROOT.$PHPFOLDER.'elements/basicSelectElement.php';

if (!isset($_SESSION)) session_start();
$userId = $_SESSION['user_id'];
$principalId = $_SESSION['principal_id'];
$principalName = $_SESSION['principal_name'];

if(!CommonUtils::isStaffUser()){
  echo 'Restricted Access!';
  return;
}

//init vars
$dayGap = 10;
$postDMLTYPE = 'VIEW';
$postFROMDATE = date('Y-m-d', mktime(0, 0, 0, date('m'), (date('d') - $dayGap), date('Y')));
$postTODATE = date('Y-m-d');
$postPRINCIPALID = 0;
CommonUtils::setPostVars();


if (isset($_POST["FILTERLIST"])) { $postFilterPPList = urldecode($_POST["FILTERLIST"]); $postFilterPPList=explode(',',$postFilterPPList); } else $postFilterPPList='';
if (isset($_POST["PAGEDEST"])) $postPAGEDEST = $_POST["PAGEDEST"]; else $postPAGEDEST="divArea";
if (isset($_POST["CALLBACK"])) $postCALLBACK = $_POST["CALLBACK"]; else $postCALLBACK="";
if (isset($_SESSION["USERID"])) $postUSERID=$_SESSION["USERID"]; else $postUSERID="0";
$_POST["RBNAME"] = '';

$dbConn = new dbConnect();
$dbConn->dbConnection();
$extractDAO = new ExtractDAO($dbConn);
$extractArr = $extractDAO->getExtractForPeriod($postFROMDATE, $postTODATE, ($postPRINCIPALID==0)?false:$postPRINCIPALID);

$fldFilterPPListname = "PPListFilter"; // the names of the filter fields
$fldFilterPPListUsageArr = array(1=>"Y","Y","Y","Y","Y","Y");
$fldFilterPPListSizeArr =  array(1=>"10","10","10","10","10","10");
$FCArr = array(); // strip off the columns we want. Remember that fetch_array doubles up everything.
$headers = array("Principal","Exported File", "Date", "Document Type", "Documents Exported", "Contacts");
$valArr = array();

echo '<HTML>';
echo DatePickerElement::getDatePickerLibs();
?>
<link href="<?php echo $DHTMLROOT.$PHPFOLDER ?>/css/1_default.css" rel="stylesheet" type="text/css">
<script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/jquery.js"></script>
<script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/dops_global_functions.js"></script>
<STYLE type="text/css">
body{margin:0px;padding:20px;}
</STYLE>
</HEAD>
<BODY>
  <DIV align="center" id="divArea">
  <h2>Document Extract Management</h2>
<?php

echo "<TABLE class='tblReset' width='100%'>";
  echo "<TR>";
  echo "<TD width='50%'></TD>";
    echo "<TD width='120' nowrap>";
      echo "Date From:<BR>";
        DatePickerElement::getDatePicker("FROMDATE",$postFROMDATE);
    echo "</TD>";
    echo "<TD width='120' nowrap>";
      echo "Date To:<BR>";
        DatePickerElement::getDatePicker("TODATE",$postTODATE);
    echo "</TD>";
    echo "<TD colspan='4'>";
      echo "Principal:<BR>";
      BasicSelectElement::getUserPrincipalDD("PRINCIPALID", $postPRINCIPALID, 'N', 'N', null, null, null, $dbConn, $userId);
    echo "</TD>";
    echo "<TD width='50%'></TD>";
  echo '</TR>';
echo "</TABLE>";

echo '<BR>';
echo '<div style="width:600px;">If you are searching for a single document, you can use the "Document Exported" field to search for the document number (In the case of GRV\'s it will be the grv number), you can do the same with the contact email address field.</div>';
echo '<BR>';


foreach ($extractArr as $row) {

  $data = array();
  $data['principal']= $row['principal_name'];

  //file
  $displayDocuments = true;
  if(substr($row['filename'],0,7) != 'CLEARED'){
    $data['file'] = '<a href="javascript:;" style="color:#DF0101" onclick="window.open(\''.$ROOT.$PHPFOLDER.'functional/general/downloadFile.php?TYPE=EXTRACTFILE&UID=' . $row['se_id'] . '\', \'DEPOTExportFile\', \'scrollbars=yes,width=500,height=400\');" >' . $row['filename'] . '</a>';
  } else {
    $displayDocuments = false;
    $data['file'] = '<span style="color:#888;font-style:italic;">' . $row['filename'] . '</span>';
  }

  $data['date']= $row['date'];
  $data['document_type']= $row['document_type'];

  //document list
  if(!$displayDocuments){
    $data['document_list'] = '';
  } else {

    //include a hidden tag of document numbers for filtering to work nicely!!
    $docExportedArr = explode(';', $row['document_number_list']);

    //popup link to view a document.
    $docHTML = '<div align="center"><h1 style="margin:0px;">'.$row['filename'].'</h1>'.
                'Number of Documents: ' . count($docExportedArr) . '</div><hr>';
    $docHTML .= '<div style="overflow:auto;max-height:250px;background:#fff;font-size:18px;font-weight:bold;" align="center">';
    $docArr = array();
    foreach($docExportedArr as $doc){
      $docRow = explode(':', $doc);
      $docArr[$docRow[0]] = $docRow[1];
    }
    asort($docArr);

    $no = 1;
    foreach($docArr as $id => $document){
      $docHTML .= '<div '. (($no%2)?(''):('style="background:#efefef;"')) .'><a href="' . $ROOT . $PHPFOLDER . '/functional/presentations/presentationHandler.php?DOCMASTID='.$id.'&TYPE=DOCUMENT&KEYFROMLINK=' . MD5($id.$document.NT_DOCUMENT_CONFIRMATION) . '" target="_blank">' . abs($document) . '</a></div>';
      $no++;
    }
    $docHTML .= '</div>';

    $data['document_list'] = '<span style="display:none;">' . implode(', ', array_values($docArr)) . '</span>'; //make this field searchable.
    $data['document_list'] .= '<a href="javascript:parent.popBox(\'' . htmlentities($docHTML) . '\',\'general\',500);">View Documents (' . count($docExportedArr) . ')</a>';

  }

  //user list
  $userArr = explode(';', $row['user_list']);
  $data['contacts'] = '<span style="display:none;">' . implode(', ',  array_values($userArr)) . '</span>';  //make this field searchable.
  $data['contacts'] .= '<a href="javascript:parent.popBox(\'<strong>Email Address List:</strong><br>' . htmlentities(implode('<BR>', $userArr)) . '<HR><strong>UID User List:</strong> ' . $row['user_uid_list'] . '\',\'general\');">View Contacts (' . count($userArr) . ')</a>';

  $FCArr[]=$data;
}

$pArr = GUICommonUtils::applyFilter($FCArr,$postFilterPPList);


echo '<TABLE width="100%">';

// filter row
  GUICommonUtils::getFilterFields($fldFilterPPListname,
                                  $fldFilterPPListUsageArr,
                                  $fldFilterPPListSizeArr,
                                  $postFilterPPList,
                                  $postPAGEDEST,
                                  "+'&USERID=".$postUSERID."+&FROMDATE='+document.getElementById(\"FROMDATE\").value+'&TODATE='+document.getElementById(\"TODATE\").value+'&PRINCIPALID='+convertElementToArray(document.getElementsByName(\"PRINCIPALID\"))",
                                  $ROOT.$PHPFOLDER.'/scripts/phpscripts/'.basename(__FILE__)
                                  );
  // the data
  GUICommonUtils::outputTable ($headers,
                                 $pArr,
                                 '',
                                 array(),
                                 '',
                                 '',
                                 "",
                                 "");
echo '</TABLE></DIV>'

?>