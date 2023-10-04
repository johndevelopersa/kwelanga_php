<?php

//---------------------------------------------
//
//  SCRIPT TO SHOW IMAGE RESOURCES : PAPERTRAIL
//  includes logging of access if found.
//
//---------------------------------------------

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER."libs/GUICommonUtils.php");
include_once($ROOT.$PHPFOLDER.'DAO/AdministrationDAO.php');
include_once($ROOT.$PHPFOLDER."DAO/PostMiscellaneousDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/principalDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/transactionDAO.php");

if (!isset($_SESSION)) session_start();
if(!isset($_SESSION['user_id']) || !isset($_SESSION['principal_id']) || !isset($_SESSION['staff_user'])){ die(); }  //STOP ERRORS.

$userId = $_SESSION['user_id'];
$principalId = $_SESSION['principal_id'];
$staffUser = $_SESSION['staff_user'];
$bucketType = 'INVOICE PAPER TRAIL';
$bucketNo = 1;
$error = false;


//Create new database object
$dbConn = new dbConnect();
$dbConn->dbConnection();

if (isset($_GET['IMAGETYPE'])) $imagetype = $_GET['IMAGETYPE']; else $imagetype="";


if(empty($_GET['DOCNO'])){

  $error = true;  //NO DOC

} else {
	
	// Get Stored image location - By principal
	

  $principalDAO = new PrincipalDAO($dbConn);
  $storedImageLocationArr = $principalDAO->getPrincipalItem($principalId);

//	print_r($storedImageLocationArr);

  $storedImageLocation    = $storedImageLocationArr[0]['image_file_location'];
  
	// Get Image File Name from document header
	
	$tranasctionDAO = new transactionDAO($dbConn);
  $imageFileArr = $tranasctionDAO->getImageFileName($principalId,str_pad($_GET['DOCNO'],8,'0',STR_PAD_LEFT));
 echo $imagetype; 
  if ($imagetype == 'L') {
      $imageFile = $imageFileArr[0]['trip_image'];
  } else {
      $imageFile = $imageFileArr[0]['image_file'];
  }

  $imageResourceUrl = 'http://' . trim($storedImageLocation). trim($imageFile);
  
//  echo $imageResourceUrl;

  //CHECK HEADER : 200 OK
$vCheckFile1 = get_headers("http://dev.kwelangasolutions.co.za/systems/kwelanga_system/scans/305/SKZ_SKZ097_IN00302851.pdf");   //get header array - if failed false is return => document not found
// echo "<br>";
// print_r($vCheckFile1);
if(($vCheckFile1[0] != 'HTTP/1.1 200 OK')){
 $error = true;      }

  }

//DISPLAY
if(!$error){


  if(isset($_GET['FORCEDL'])){

    //FORCE DOWNLOAD - SEND ATTACHMENT HEADER.
    header("Cache-Control: public");
    header("Content-Description: File Transfer");
    header("Content-Disposition: attachment; filename=".trim($imageFile));
    header("Content-Type: application/pdf");
    header("Content-Transfer-Encoding: binary");
    readfile($imageResourceUrl);
    die();

  } else {

    //BUCKET LOG.


    //LOG ONLY IF IS NOT STAFF MEMBER
    if($staffUser == 'N'){  //CHECK IF STAFF.

      //Check Principals Bucket Flag - FOR NON STAFF MEMEBERS
      //LVL 2 CHECK  (LVL 1 IN TT)
      if(isset($principalArr[0]['activity_price_bucket_1']) && $principalArr[0]['activity_price_bucket_1'] == 'N'){

        echo '<h2>ACCESS DENIED!</h2><BR>To access this feature please contact Kwelanga Solutions.';
        die();

      }
    }

    //DISPLAY
     echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
          	"http://www.w3.org/TR/html4/loose.dtd">
          <HTML>
          <HEAD>
          <meta http-equiv="content-type" content="text/html;charset=utf-8">
			<title>Document Resource Imagery</title>
          <STYLE TYPE="TEXT/CSS">
           html,body{margin:0;padding:0;height:100%;border:none;}
          </STYLE>
          </HEAD>
          <BODY>';

    //DISPLAY FILE
    echo '<TABLE STYLE="width:100%;height:100%;margin:0;padding:0;" ><TR><TD align="right" style="padding:3px 15px;" height="10%;">';

    echo '<A href="'.$_SERVER['PHP_SELF'].'?DOCNO='.$_GET['DOCNO'].'&FORCEDL=1" onClick="killIframe()" style="text-decoration:none;color:#777;line-height:18px;font-size:12px;display:block;padding:8px;background:#dedede;border:1px solid #ccc;width:200px;font-family:tahoma,verdana,arial;text-align:center;">
    	 	<strong>Problems with viewing?</strong><br><span style="color:#047">Please click here</span>
    	 </a>';

    echo '</TD></TR><TR><TD height="85%" id="iframeTD">';
    echo '<iframe src="'.$imageResourceUrl.'" style="width:100%;height:100%;border:0px;margin:0;padding:0;"></iframe>';
    echo '</TD></TR></TABLE>';
    echo '<SCRIPT>function killIframe(){document.getElementById("iframeTD").innerHTML=""}</SCRIPT>';
    die();
  }

}


//ERROR PAGE BELOW::

?>
<html>
<head>
<meta http-equiv="content-type" content="text/html;charset=utf-8">
<title>Document Not Available</title>
<style type="text/css">
body{
	margin:0px;
	padding:0px;
	padding-top:50px;
	font-family: Tahoma, Arial, Verdana, Arial, Tahoma;
	background: -webkit-gradient(linear, left top, left bottom, from(#cccccc), to(#fff));
	background: -moz-linear-gradient(top,  #cccccc,  #fff) no-repeat;
	*background:#cccccc;	/*ENABLE ONLY FOR IE*/
}
div.block{

	display:block;
	width:650px;
	border:8px solid #777;
	background:#777;
	-webkit-border-radius: 15px;
	-moz-border-radius: 15px;
	border-radius: 15px;
	-webkit-box-shadow: 2px 4px 6px rgba(0,0,0,.5);
	-moz-box-shadow: 2px 4px 6px rgba(0,0,0,.5);
	box-shadow: 2px 4px 6px rgba(0,0,0,.5);
}
.bordMid {
	border:1px solid #efefef;
	background:#efefef;
	-webkit-border-radius: 12px;
	-moz-border-radius: 12px;
	border-radius: 12px;
}
.bordInner {
	border:1px solid #ccc;
	background:#fff;
	-webkit-border-radius: 12px;
	-moz-border-radius: 12px;
	border-radius: 12px;
	padding:5px 20px 10px 20px;
	text-align:center;
}
h1,h2 {
	margin:0px;padding:0px;
	color:#092750;
	text-shadow: 1px 1px 1px #ccc;
}
h1 {
	margin:20px 0px 5px 0px;
	display:block;
	font-size:45px;
	color:#a50513
}
ul {font-size:8pt;color:#666;padding-left:20px;margin:0px;margin-top:15px;}
ul li{margin-top:6px;}
p#head {margin:0px;font-size:10pt;font-weight:bold;color:#555;line-height:18px;}
div.text{
	width:550px;
	text-align:left;
	line-height:16px;
}
.bordBot {
	padding:12px 0px;
	margin-bottom:15px;
	border-bottom:3px solid #efefef;
}
div.disclam{
	width:420px;
	font-size:11px;
	color:#a50513;
	font-weight:bold;
	margin:10px 0px 18px 0px;
	padding:8px;
	background:#ffff5b;
	border:1px solid #999;
    -webkit-border-radius: 5px;
    -moz-border-radius: 5px;
    border-radius: 5px;
}
</style>

</head>
<body>

<div align="center">
<div class="block"><div class="bordMid"><div class="bordInner">
	<h1>Document Not Available!</h1>

	<div align="center" class="bordBot"><div class="text">
		<p id="head">We are sorry to inform you but the document you are looking for was not found!<br>Possible Reasons:</p>

		<ul>
			<li>The document was never scanned in by the DEPOT.</li>
			<li>Or the document has been filed incorrectly.</li>
			<li>Or (less likely but certainly plausible) we might have coded the URL incorrectly.</li>
			<li>Or (far less plausible, but theoretically possible), depending on which ill-defined Grand Unifying Theory of physics one subscribes to), some random fluctuation in the space-time continuum might have produced a shatteringly brief but nonetheless real electromagnetic discombobulation which caused this error page to appear.</li>
		</ul>

	</div></div>

	<div align="center" >
	   <div class="disclam">PLEASE NOTE: Kwelanga Solutions has no control over these documents.</div>
	</div>

</div></div></div>
</div>

</body>
</html>