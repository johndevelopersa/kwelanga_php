
<?php
include_once('ROOT.php');
include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER."functional/presentations/SaveAndEmailDocument.php");
include_once($ROOT.$PHPFOLDER."DAO/TransactionDAO.php");
include_once $ROOT . $PHPFOLDER . 'libs/storage/Storage.php';

//setup S3 storage class.
new Storage(S3_ACCESS_ID, S3_SECRET_KEY, false, S3_ENDPOINT, S3_REGION);

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

$stripedCustName = str_replace("'"," ",str_replace("/"," ",stripslashes($custName)));

if($psmUid <> '') {
    $transactionDAO = new TransactionDAO($dbConn);
    $sn = $transactionDAO->principalShortName(mysqli_real_escape_string($dbConn->connection, $psmUid));
    $prinAbr = trim($sn[0]['short_name']);
} else {
    $prinAbr = '';
}

if (trim(substr(mysqli_real_escape_string($dbConn->connection, $outputTyp),7,2)) == DT_STATEMENT) {
    $seqFilename = trim($stripedCustName) . '_' . trim($dtAr[0]['DocumentDiscription']) . '_' . date("d-M-y") . '.pdf';
} elseif(trim(substr(mysqli_real_escape_string($dbConn->connection, $outputTyp),7,2)) == 53) {
    $seqFilename = trim($sn) . '_' . trim($dtAr[0]['DocumentDiscription']) . '_' . date("d-M-y") . '.pdf';
} else {
    if($prinAbr == '') {
        $seqFilename = substr($dtAr[0]['DocumentDiscription'],0,1).mysqli_real_escape_string($dbConn->connection, $principalId).mysqli_real_escape_string($dbConn->connection, substr($outputTyp,9,8)).".pdf";
    } else {
        $seqFilename = substr($dtAr[0]['DocumentDiscription'],0,1).'_'.mysqli_real_escape_string($dbConn->connection, $prinAbr).'_'.mysqli_real_escape_string($dbConn->connection, ltrim(substr($outputTyp,9,8),'0')).".pdf";
    }
}

$ch = curl_init(HOST_SURESERVER_AS_NEWUSER.$PHPFOLDER."functional/administration/functions/templatePdfUserHTML.php?USERID={$userId}&PRINCIPALID={$principalId}&DOCMASTID={$docmastId}&OUTPUTTYP={$outputTyp}&TEMPLATP={$templat}");

curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch,CURLOPT_USERAGENT,"Interweb Explorer");
$response=curl_exec($ch);
curl_close($ch);


//upload to AWS S3
$storageFilename = "archives/emaildocs/" . date("Y") . "/" . date("m") . "/" . $seqFilename;
$storageUploadResult = Storage::putObject(S3_BUCKET_NAME, $storageFilename, $response);
if(!$storageUploadResult){
    echo "error uploading document!";
    return;
}

$dResult = '';

$SaveAndEmailDocument = new SaveAndEmailDocument;

$dResult = $SaveAndEmailDocument->SaveAndMail(mysqli_real_escape_string($dbConn->connection, $userId), mysqli_real_escape_string($dbConn->connection, $outputTyp), mysqli_real_escape_string($dbConn->connection, $psmUid), $storageFilename, mysqli_real_escape_string($dbConn->connection, $prinnam), mysqli_real_escape_string($dbConn->connection, substr($outputTyp,9,8)), $dtAr[0]['DocumentDiscription'], mysqli_real_escape_string($dbConn->connection, $principalId) );

echo trim($dtAr[0]['DocumentDiscription'])." - ". substr($outputTyp,9,8) . "<br><br>Successfully sent to - " . $dResult;
