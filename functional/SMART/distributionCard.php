<?php


include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER."DAO/DistributionDAO.php");
include_once($ROOT.$PHPFOLDER.'libs/EncryptionClass.php');


$postDID = (isset($_GET['DID']))?$_GET['DID']:false;
if(empty($postDID)){
  die('ERROR: Empty/Invalid DID passed!');
}
$eC = new EncryptionClass();  //passed id is lightly encoded
$postDID = $eC->decryptUIDValue($postDID);
if($postDID==false){
  die('Restrictired Access!');
}

$dbConn = new dbConnect();
$dbConn->dbConnection();
$distributionDAO = new DistributionDAO($dbConn);
$dI = $distributionDAO->getDistributionItem($postDID);


?>
<html>
<head>
<title>Online E-mail Viewer</title>
<script type="text/javascript">

function render () {
  var iframe = document.getElementById('ibody');
  if (iframe) {
    var iframeDoc;
    if (iframe.contentDocument) {
            iframeDoc = iframe.contentDocument;
    }
    else if (iframe.contentWindow) {
            iframeDoc = iframe.contentWindow.document;
    }
    else if (window.frames[iframe.name]) {
            iframeDoc = window.frames[iframe.name].document;
    }
    if (iframeDoc) {
            iframeDoc.open();
            <?php
              echo 'iframeDoc.write(\'' . str_replace(array("\r","\n","'"),array('\r','\n',"`"), $dI[0]['body']) . '\');';
            ?>
            iframeDoc.close();
    }
  }
}

window.onload = function (evt){render();}

</script>

<style type="text/css">

body{
	font-size:10px;
	font-family:arial,verdana,tahoma;
	background:#efefef;
	margin:0px;
	padding:0px;
}
div.wrapper{
        margin-top:20px;
	width:700px;
	background:#fff;
	border:1px solid #ccc;
	padding:20px;
}
a.download{
  color:darkred;
  font-weight:bold;
  font-size:12px;
  margin-left:20px;
  text-decoration:none;
}
a.download:hover{
  color:red;
}
div.footer{
  color:#666;
  line-height:35px;
}
div.footer a{
  color:#047;
  text-decoration:none;
}
.iframe {
  border:1px solid #ccc;
  font-size:12px;
}
h1,h2,h3{color:#047;}
h1{margin:10px 0px 20px 0px;font-size:20px}
h2{margin:5px 0px;font-weight: normal;font-size:15px;}
h2 .title {color:#888;font-weight: normal;}
</style>


</head>
<body>
<div align="center">
<div class="wrapper" align="left">
  <h1><?php echo $dI[0]['subject']; ?></h1>
  <h2><span class="title">Sent:</span> <?php echo ($dI[0]['run_date']!="")?(date('d M Y h:i:s A', strtotime($dI[0]['run_date']))):""; ?></h2>
  <h2><span class="title">To:</span> <?php echo htmlentities($dI[0]['destination_addr']); ?></h2>
  <br>
  <?php if($dI[0]['attachment_file']!=""){ ?>
    <h2><span class="title">Attachment:</span><br><?php echo htmlentities(basename($dI[0]['attachment_file'])); ?> <a href="<?php echo $ROOT.$dI[0]['attachment_file'] ?>" target="_blank" class="download">DOWNLOAD</a></h2>
  <?php } ?>
  <br>

  <iframe src="about:blank" id="ibody" class="iframe" height="380" width="700" frameborder="0" border="1" scrolling="yes" ></iframe>
</div>
</div>
<div align="center" class="footer">Powered by <a href="https://kwelangaonlinesolutions.co.za/" target="_blank">Kwelanga Online Solutions</a></div>
</body>
</html>