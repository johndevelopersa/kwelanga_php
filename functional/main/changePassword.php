<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once ($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER.'libs/EncryptionClass.php');
include_once($ROOT.$PHPFOLDER.'libs/JavaScriptPacker.php');
include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');
CommonUtils::getSystemConventions();

$eC = new EncryptionClass();

if (isset($_POST['action'])) $postACTION=$_POST['action']; else $postACTION="";
if (isset($_POST['pwd1'])) $postPWD1=trim((string)$eC->des(ENCRYPT_JS_KEY,$eC->hexToString($_POST['pwd1']),0, 0, null, null)); // for some reason if i don't trim it, then var_dump((string) $username) says it is a string(n+1) instead of string(n) !
if (isset($_POST['pwd2'])) $postPWD2=trim((string)$eC->des(ENCRYPT_JS_KEY,$eC->hexToString($_POST['pwd2']),0, 0, null, null));

if (!isset($_SESSION)) session_start();
$userId = $_SESSION["user_id"];
$dbConn = new dbConnect();
$dbConn->dbConnection();
$systemId = $_SESSION["system_id"];
$systemName = $_SESSION['system_name'];
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo SNC::title ?></title>
    <link href="<?php echo $DHTMLROOT.$PHPFOLDER.'css/css.php?SYSID='.$systemId.'&SYSNAME='.$systemName ?>" rel="stylesheet" type="text/css" />
    <script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/client-side-encryption-packed.js"></script>
<SCRIPT type="text/javascript" language="javascript" >
<?php
	$js="
		function submitForm()	{
		  document.logon.pwd1.value=stringToHex(des(\"".ENCRYPT_JS_KEY."\", document.dummy.tpwd1.value, 1, 0));
		  document.logon.pwd2.value=stringToHex(des(\"".ENCRYPT_JS_KEY."\", document.dummy.tpwd2.value, 1, 0));
		  document.logon.submit();
		  return false;
		}";
		$jsPacker = new JavaScriptPacker($js, 'Normal', true, false);
 		$packed = $jsPacker->pack();
		echo $packed;
?>
</SCRIPT>
</head>

<body>
  <div align="left">
    <div id="headerMain">
      <div align="left" id="headerLogo"></div>
    </div>
  </div>

  <div align="center" style="margin-top:160px;">
<div style='width: 400px; margin-left: auto; margin-right: auto; margin-top: auto; margin-bottom: auto; text-align:center;'>

<?php
 if($postACTION=="process") {
 	include_once($ROOT.$PHPFOLDER.'properties/dbSettings.inc');
 	$error="";

 	$after1=trim($postPWD1);
	$after1=preg_replace('/\s+/','',$after1); // strip whitespace;
	$after1=preg_replace('/[^a-zA-Z0-9]/', '', $after1); // strip accepted chars out
	$after2=trim($postPWD2);
	$after2=preg_replace('/\s+/','',$after2); // strip whitespace;
	$after2=preg_replace('/[^a-zA-Z0-9]/', '', $after2); // strip accepted chars out

	if (($after1!=$postPWD1) || ($after2!=$postPWD2)) {
 	 	$error.="Passwords cannot contain whitespace, spaces, special chars. Only alpha-numeric characters are permitted.<BR>";
 	}

 	if (($postPWD1=="") || ($postPWD2=="")) {
 	 	$error.="Both Password input fields must be entered!<BR>";
 	}

 	if (($postPWD1!=$postPWD2)) {
 	 	$error.="Both Password input fields must be the same value!<BR>";
 	}

 	if (strlen($postPWD1)<6) {
 	 	$error.="Password must be a minimum of 6 characters.<BR>";
 	}

 	if ($postPWD1==NEW_USER_PWD) {
 	 	$error.="Password cannot remain as the default.<BR>";
 	}

 	if ($error!="") {
	 	echo "<SPAN style='color:red; font-family: Verdana, Arial, Helvetica, sans-serif; font-weight:normal; font-size:0.7em;'>".$error."</SPAN>";
 	} else {
 		include_once ($ROOT.$PHPFOLDER."libs/CommonUtils.php");
 		include_once($ROOT.$PHPFOLDER.'DAO/AdministrationDAO.php');

 		$administrationDAO=new AdministrationDAO($dbConn);
 		$eTO = $administrationDAO->changePassword($userId,$postPWD1,"N");
 		if ($eTO->type==FLAG_ERRORTO_SUCCESS) {
 			$dbConn->dbinsQuery("commit");
 			$dbConn->dbClose();
			$eR_sk = $eC->encrypt(ENCRYPT_SESSION_KEY, $postPWD1, ENCRYPT_PWD_LENGTH);
 			$_SESSION['password'] = $eR_sk;
 			$_SESSION['user_key'] = md5($userId.$postPWD1.ENCRYPT_SESSION_KEY.$_SESSION['full_name']);
 			echo '<meta http-equiv="refresh" content="0;url='.$ROOT.$PHPFOLDER.'functional/principals/principal_select.php">';
 			exit;
 		} else echo "<SPAN style='color:red; font-family: Verdana, Arial, Helvetica, sans-serif; font-weight:normal; font-size:0.7em;'>".$eTO->description."</SPAN>";
 		$dbConn->dbClose();
 	  }
 }
?>

<form id="logon" name="logon" action='<?php echo $ROOT.$PHPFOLDER; ?>functional/main/changePassword.php' method='post'>
	<input type='hidden' value='process' name='action' />
	<input type="hidden" name="pwd1" id="pwd1" />
	<input type="hidden" name="pwd2" id="pwd2" />
	<?php
	  if(isset($_GET['expiredpwd']) || isset($_POST['expiredpwd'])){
	    echo '<input type="hidden" name="expiredpwd" value="1">';
	  }
	?>
</form>
<form id="dummy" name="dummy" action='' method="" target=''>
<Table style="border-color:gray; border-style:solid; color:black; font-family: Verdana, Arial, Helvetica, sans-serif; font-weight:normal; font-size:0.8em;">
<TR>
	<TD colspan=2>

	<?php
	  //change wording depending on expired or general password setup.
	  if(isset($_GET['expiredpwd']) || isset($_POST['expiredpwd'])){?>
		Your Password has expired please set a new one.
	<?php } else {?>
		Your Password has been Reset or you are a New User.
	<?php } ?>

	</TD>
</TR>
<TR style='border-width:1px; border-color:black; border-style:solid;'>
	<TD colspan=2 style='font-weight:bold; border-bottom-width:1px; border-color:gray; border-bottom-style:solid;'>
		Please change your password.
	</TD>
</TR>
<TR>
	<TD style="text-align:right;">
		<BR>New Password:
	</TD>
	<TD style=''>
		<BR><INPUT type="password" size="24" maxlength="20" name="tpwd1">
	</TD>
</TR>
<TR>
	<TD style="text-align:right;">
		<BR>Retype New Password:
	</TD>
	<TD style=''>
		<BR><INPUT type="password" size="24" maxlength="20" name="tpwd2">
	</TD>
</TR>
<TR>
	<TD colspan=2'>
		<BR>
	</TD>
</TR>
</TABLE>
<SPAN style='color:black; font-family: Verdana, Arial, Helvetica, sans-serif; font-weight:normal; font-size:0.55em;'>
* Your logon details will be emailed to you for your safe-keeping.
</SPAN>
<BR><BR>
<input type='hidden' value='process' name='action' />
<input class='submit' type='button' value='Submit' onclick='submitForm();'/>
</form>

</div>
</div>

</body>
</html>

