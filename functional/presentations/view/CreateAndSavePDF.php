
<?php 
include_once('ROOT.php'); 
include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER."functional/presentations/SaveAndEmailDocument.php");
include_once($ROOT.$PHPFOLDER."DAO/TransactionDAO.php");

$userId = ((isset($_GET["USERID"]))?$_GET["USERID"]:"");
$principalId = ((isset($_GET["PRINCIPALID"]))?$_GET["PRINCIPALID"]:"");
$docmastId   = ((isset($_GET["DOCMASTID"]))?$_GET["DOCMASTID"]:"");
$outputTyp   = ((isset($_GET["OUTPUTTYP"]))?$_GET["OUTPUTTYP"]:"");
$psmUid      = ((isset($_GET["PSMUID"]))?$_GET["PSMUID"]:"empty");
$prinnam     = ((isset($_GET["PRINNAM"]))?$_GET["PRINNAM"]:"empty");
$doctypeid   = ((isset($_GET["DOCTYPEID"]))?$_GET["DOCTYPEID"]:"empty");
$templat     = ((isset($_GET["TEMPLAT"]))?($_GET["TEMPLAT"]):"empty");
$custName    = ((isset($_GET["CUSTNAME"]))?($_GET["CUSTNAME"]):"empty");

$transactionDAO = new TransactionDAO($dbConn);
$dtAr = $transactionDAO->getDocumentTypeSubject(trim(substr(mysqli_real_escape_string($dbConn->connection, $outputTyp),7,2)));
if(count($dtAr)==0){
  echo "No Document Type Found <br>";
  $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
  $this->errorTO->description = "Successful";
  return $this->errorTO;
}

if (trim(substr(mysqli_real_escape_string($dbConn->connection, $outputTyp),7,2)) == DT_STATEMENT) {
       $seqFilename = trim($custName) . '_' . trim($dtAr[0]['DocumentDiscription']) . '_' . date("d-M-y") . '.pdf';
} else {
       $seqFilename = substr($dtAr[0]['DocumentDiscription'],0,1).mysqli_real_escape_string($dbConn->connection, $principalId).mysqli_real_escape_string($dbConn->connection, substr($outputTyp,9,8)).".pdf";
}

$ch = curl_init(HOST_SURESERVER_AS_NEWUSER.$PHPFOLDER."functional/administration/functions/templatePdfUserHTML.php?USERID={$userId}&PRINCIPALID={$principalId}&DOCMASTID={$docmastId}&OUTPUTTYP={$outputTyp}&TEMPLATP={$templat}");

curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch,CURLOPT_USERAGENT,"Interweb Explorer");
$response=curl_exec($ch); 
curl_close($ch);

// write to file
$fP = "C:/inetpub/wwwroot/systems/kwelanga_system/archives/emaildocs/";
        @mkdir($fP, 0777, true);
        $bkupFolder = CommonUtils::createBkupDirs($fP);
        $myFile = $fP . $seqFilename;
        $fh = fopen($myFile, 'w');
        fwrite($fh, $response);
        fclose($fh);

$dResult = '';

$SaveAndEmailDocument = new SaveAndEmailDocument;

$dResult = $SaveAndEmailDocument->SaveAndMail(mysqli_real_escape_string($dbConn->connection, $userId), mysqli_real_escape_string($dbConn->connection, $outputTyp), mysqli_real_escape_string($dbConn->connection, $psmUid), $seqFilename, mysqli_real_escape_string($dbConn->connection, $prinnam), mysqli_real_escape_string($dbConn->connection, substr($outputTyp,9,8)), $dtAr[0]['DocumentDiscription'] );

echo trim($dtAr[0]['DocumentDiscription'])." - ". substr($outputTyp,9,8) . "<br><br>Successfully sent to - " . $dResult;
?>