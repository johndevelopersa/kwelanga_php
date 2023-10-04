<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
include_once($ROOT.$PHPFOLDER.'libs/JavaScriptPacker.php');

if (!isset($_SESSION)) session_start;
$userId=$_SESSION['user_id'];
$principalId=$_SESSION['principal_id'];

//Create new database object
$dbConn = new dbConnect();

//Database connection method
$dbConn->dbConnection();


$adminUser=(isset($_SESSION['admin_user']) && ($_SESSION['admin_user']=="Y"))?true:false;

if (!($adminUser===true)) {
	echo "Incorrect Priviledges";
	return;
}

$eC = new EncryptionClass();

echo "<HTML>
	  <HEAD>
		<SCRIPT type=\"text/javascript\" language=\"javascript\" src=\"".$DHTMLROOT.$PHPFOLDER."js/dops_global_functions.js\"></script>
		<SCRIPT type=\"text/javascript\" language=\"javascript\" src=\"".$DHTMLROOT.$PHPFOLDER."js/client-side-encryption-packed.js\"></script>
		<SCRIPT type=\"text/javascript\" language=\"javascript\">";
		$js="var pwd='".($eC->stringToHex($eC->des(ENCRYPT_JS_KEY,ENCRYPT_JS_PWD,1,0,null,null)))."';
			var encrypted=true;
			function secure() {
				if (encrypted) return;
				var fld=document.getElementsByTagName('div');
				for (var i=0; i<fld.length; i++) {
					if (fld[i].name=='pwd') {
						fld[i].innerHTML = stringToHex(des('".ENCRYPT_JS_KEY."',fld[i].innerHTML,1,0));
					}
				};
				encrypted=true;
			}
			function unSecure(){

				if (!encrypted) return;
				var ans=parent.getMsgBoxInputValue();
				if (ans=='') {
					parent.showMsgBoxInput('Please enter Password:', 'password', 'content.unSecure();');
					return false;
				} else parent.clearMsgBoxInputValue();
				var tpwd=des('".ENCRYPT_JS_KEY."',hexToString(pwd),0,0,null,2).fulltrim().replace(/\\x00/g,''); // i think the server is appending nulls
				if (ans!=tpwd) {
					alert('incorrect password!');
					return;
				}

				var fld=document.getElementsByTagName('div');
				for (var i=0; i<fld.length; i++) {
					if (fld[i].getAttribute('name')=='pwd') {
						fld[i].innerHTML = des('".ENCRYPT_JS_KEY."',hexToString(fld[i].innerHTML),0,0);
					}
				};
				encrypted=false;
			}";
		$jsPacker = new JavaScriptPacker($js, 'Normal', true, false);
 		$packed = $jsPacker->pack();
		echo $packed;

echo 'function nul(){}';
echo '</SCRIPT>';

echo <<<EOF
<STYLE type="text/css">
	table { border:1; border-style:solid; }
	td { border-left:1; border-style:solid; border-right:1; }
</STYLE>
</HEAD>
<BODY style="font-size:12px;">

<a href="javascript:nul()"><img src="{$DHTMLROOT}{$PHPFOLDER}images/locked-icon.png" onclick='secure();' border="0" /></a>
<a href="javascript:nul()"><img src="{$DHTMLROOT}{$PHPFOLDER}images/unlocked-icon.png" onclick='unSecure();' border="0"/></a>
EOF;

$dbConn->dbQuery("select uid, username, password, full_name, user_email, deleted, suspended, system_uid, category, password_days, staff_user, admin_user from users order by full_name");

$arr=array();
$i=0;
while ($row=mysqli_fetch_assoc($dbConn->dbQueryResult)) {
	$password = $eC->decrypt(ENCRYPT_DB_KEY, $row["password"]);
	$passwordJS = $eC->stringToHex($eC->des(ENCRYPT_JS_KEY,$password,1,0,null,null));
	$row["password"]="<div><div name='pwd'>".$passwordJS."</div></div>";
	$arr[]=$row;
	$i++;
}
GUICommonUtils::outputRS($arr);

echo "<img name='endpositioner' src='".$DHTMLROOT.$PHPFOLDER."images/invis.gif' />"; // used by adjustMyFrameHeight() to get bottom position. offset calculation is otherwise unreliable
?>
<SCRIPT type="text/javascript">
adjustMyFrameHeight();
</SCRIPT>

</BODY></HTML>
