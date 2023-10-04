<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'elements/basicSelectElement.php');
include_once($ROOT.$PHPFOLDER.'elements/basicInputElement.php');
include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
include_once($ROOT.$PHPFOLDER.'DAO/AdministrationDAO.php');
include_once($ROOT.$PHPFOLDER.'TO/UserTO.php');

if (!isset($_SESSION)) session_start;
$userId=$_SESSION['user_id'];
$principalId = $_SESSION['principal_id'];
$userCategory = $_SESSION['category'];

//Create new database object
$dbConn = new dbConnect();
$dbConn->dbConnection();

// passed POST Fields
$postDMLTYPE=mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST['DMLTYPE']));
if (isset($_POST["PAGEDEST"])) $postPAGEDEST=$_POST["PAGEDEST"];
else $postPAGEDEST="";
if (isset($_POST["USERID"])) $postUSERID=$_POST["USERID"];
else $postUSERID="";

if (($postUSERID=="") && ($postDMLTYPE=="UPDATE")) {
	print("No User Chosen");
	return;
}

$userTO=new UserTO();
$administrationDAO=new AdministrationDAO($dbConn);
$adminUser = (isset($_SESSION['admin_user']) && ($_SESSION['admin_user']=="Y")) ? true : false;
$staffUser = (isset($_SESSION['staff_user']) && ($_SESSION['staff_user']=="Y")) ? true : false;

$class = 'odd';

if ($postDMLTYPE=="UPDATE") {
	$userDetails=$administrationDAO->getUserItem($postUSERID);
	$userTO->userID=$userDetails[0]['uid'];
	$userTO->username=$userDetails[0]['username'];
	//$userTO->password=$userDetails[0]['password'];
	$userTO->full_name=$userDetails[0]['full_name'];
	$userTO->user_email=$userDetails[0]['user_email'];
	$userTO->user_tel=$userDetails[0]['user_tel'];
	$userTO->user_cell=$userDetails[0]['user_cell'];
	if ($userDetails[0]['suspended']) $userTO->suspended="Y"; else $userTO->suspended="N";
	if ($userDetails[0]['selfregistered']) $userTO->selfregistered="Y"; else $userTO->selfregistered="N";
	if ($userDetails[0]['deleted']) $userTO->deleted="Y"; else $userTO->deleted="N";
	$userTO->lastLogin=$userDetails[0]['lastlogin'];
	$userTO->category=$userDetails[0]['category'];
	$userTO->organisationName=$userDetails[0]['organisation_name'];
  $userTO->adminUser = $userDetails[0]['admin_user'];
	$userTO->staffUser = $userDetails[0]['staff_user'];

	$hasSURole = $administrationDAO->hasRoleSuperUser($postUSERID, $principalId);

	// can modify details
	if ($hasSURole) $canModifyDetails = $administrationDAO->hasRoleInList($userId,$postUSERID,ROLE_MODIFY_SU,$userCategory,$principalId);
	else $canModifyDetails = $administrationDAO->hasRoleInList($userId,$postUSERID,ROLE_MODIFY_GU,$userCategory,$principalId);

	// can delete user
	if ($hasSURole) $canDeleteUser = $administrationDAO->hasRoleInList($userId,$postUSERID,ROLE_DELETE_SU,$userCategory,$principalId);
	else $canDeleteUser = $administrationDAO->hasRoleInList($userId,$postUSERID,ROLE_DELETE_GU,$userCategory,$principalId);
} else {
	//apply defaults
	$userTO->suspended='N';
	$userTO->selfregistered='N';
	$userTO->deleted='N';
	$userTO->category='P';
	$userTO->adminUser = 'N';
	$userTO->staffUser = 'N';

	$canDeleteUser = true;
	$canModifyDetails = true;
}

if ($canModifyDetails) $restrictDetails="N"; else $restrictDetails="Y";

if ($canDeleteUser) $restrictDelete="N"; else $restrictDelete="Y";

// field names for this form
$fldChosenUserRB='ChosenUser';
$fldUId='fldUID';
$fldUN='fldUN';
$fldPWD='flPWD';
$fldFN='fldFN';
$fldEmail='fldE';
$fldTel='fldTEL';
$fldCell='fldCELL';
$fldSuspended='fldS';
$fldSR='fldSR';
$fldDeleted='fldD';
$fldCategory='fldCat';
$fldBT='fldBT';
$fldORGNAME='fldORGNAME';
$fldAdminF = 'fldADMINUSER';
$fldStaffF = 'fldSTAFFUSER';





?>
<SCRIPT type='text/javascript' defer>
function getUserName() {
	document.getElementById('<?php echo $fldUN; ?>').value='';
	params='FULLNAME='+document.getElementById('<?php echo $fldFN; ?>').value;
	parent.showMsgBoxSystemFeedback('Retrieving UserName...');
	$.ajax({
	  url: "<?php echo $ROOT.$PHPFOLDER ?>functional/administration/functions/getUserName.php",
	  global: false,
	  type: 'POST',
          data: params,
          dataType: 'html',
	  cache: false,
	  success: function(msg){
	  	try {
	  		eval(msg);
	  		if (msgClass.type=='S') document.getElementById('<?php echo $fldUN; ?>').value=msgClass.identifier;
	  		else document.getElementById('uncalc').innerHTML=msgClass.description;
	  	} catch (e) { alert('an unexpected error occurred:'+e.description); }
	  	parent.hideMsgBoxSystemFeedback('Retrieving UserName...');
	  },
	  error: function(XMLHttpRequest, textStatus, errorThrown) {
		  alert('Could not retrieve UserName. '+textStatus+' - '+errorThrown);
		  document.getElementById('uncalc').value='Error retrieving UserName!';
		  parent.hideMsgBoxSystemFeedback('Retrieving UserName...');
	  }
  });
}

function resetPassword() {
	params='USERID='+document.getElementById('<?php echo $fldUId; ?>').value;
	parent.showMsgBoxSystemFeedback('Resetting Password...');
	$.ajax({
	  url: "<?php echo $ROOT.$PHPFOLDER ?>functional/administration/functions/resetPassword.php",
	  global: false,
	  type: 'POST',
          data: params,
          dataType: 'html',
	  cache: false,
	  success: function(msg){
	  	try {
	  		eval(msg);
	  		document.getElementById('uncalc').innerHTML=msgClass.description;
	  	} catch (e) { alert('an unexpected error occurred:'+e.description); }
	  	parent.hideMsgBoxSystemFeedback('Resetting Password...');
	  },
	  error: function(XMLHttpRequest, textStatus, errorThrown) {
		  alert('Could not reset Password. '+textStatus+' - '+errorThrown);
		  document.getElementById('uncalc').value='Error resetting Password!';
		  parent.hideMsgBoxSystemFeedback('Resetting Password...');
	  }
  });
}
</SCRIPT>

<?php if ($postDMLTYPE!="INSERT") { ?>
<a href="javascript:;" onClick="userAccessDetails('USERID=<?php echo $userTO->userID ?>','PRINCIPAL')" style="color:orange">[view allocation]</a>
<?php } ?>
<TABLE>
<THEAD>
  <TR>
    <TH colSpan="2">
      User Details
      <span style="float:right;font-size:10px;"><?php echo $userTO->userID ?></span>
      <input type="hidden" value="<?php echo $userTO->userID ?>" name="<?php echo $fldUId ?>" id="<?php echo $fldUId ?>" >
    </TH>
  </TR>
</THEAD>

<TR class='<?php echo GUICommonUtils::styleEO($class)?>'>
	<TD class='label'>Username<?php echo GUICommonUtils::requiredField(); ?>:</TD>
	<TD class='field'><?php BasicInputElement::getGeneralFieldString($fldUN,$userTO->username,"text","10","10","Y","Y",null,null,null); ?></TD>
</TR>
<!--
<TR class='even'>
	<TD class='label'>PassWord<?php echo GUICommonUtils::requiredField(); ?>:</TD>
	<TD class='field'><?php BasicInputElement::getGeneralFieldString($fldPWD,$userTO->password,"text","20","20",$restrictDetails,$restrictDetails,null,null,null); ?></TD>
</TR>
-->
<TR class='<?php echo GUICommonUtils::styleEO($class)?>'>
	<TD class='label'>Fullname<?php echo GUICommonUtils::requiredField(); ?>:</TD>
	<TD class='field'><?php BasicInputElement::getGeneralFieldString($fldFN,$userTO->full_name,"text","50","50",$restrictDetails,$restrictDetails,null,null,null); ?></TD>
</TR>
<?php
 if ($postDMLTYPE=="INSERT") {
?>
<TR class='<?php echo $class ?>'>
	<TD class='label'></TD>
	<TD class='field'><input class='submit' type='submit' value='Get UserName' onclick='getUserName();' style="margin:0px;"/><div id='uncalc' style="display:block;width:300px;color:red;"></div></TD>
</TR>
<?php
 } else if (($postDMLTYPE=="UPDATE") && ($canModifyDetails)) {
?>
<TR class='<?php echo $class ?>'>
	<TD class='label'></TD>
	<TD class='field'><input class='submit' type='submit' value='Reset Password' onclick='resetPassword();' style="margin:0px;" /><div id='uncalc'></div></TD>
</TR>
<?php
 }
?>
<TR class='<?php echo GUICommonUtils::styleEO($class)?>'>
	<TD class='label'>Email<?php echo GUICommonUtils::requiredField(); ?>:</TD>
	<TD class='field'><?php BasicInputElement::getGeneralFieldString($fldEmail,$userTO->user_email,"text","50","50",$restrictDetails,$restrictDetails,null,null,null); ?></TD>
</TR>
<TR class='<?php echo GUICommonUtils::styleEO($class)?>'>
	<TD class='label'>Telephone:</TD>
	<TD class='field'><?php BasicInputElement::getGeneralFieldString($fldTel,$userTO->user_tel,"text","20","20",$restrictDetails,$restrictDetails,null,null,null); ?></TD>
</TR>
<TR class='<?php echo GUICommonUtils::styleEO($class)?>'>
	<TD class='label'>Mobile:</TD>
	<TD class='field'><?php BasicInputElement::getGeneralFieldString($fldCell,$userTO->user_cell,"text","20","20",$restrictDetails,$restrictDetails,null,null,null); ?></TD>
</TR>
<TR class='<?php echo GUICommonUtils::styleEO($class)?>'>
	<TD class='label'>Category of User:</TD>
	<TD class='field'><?php BasicSelectElement::getUserCategory($fldCategory,$userTO->category,$restrictDetails,$restrictDetails,null,null,null, $dbConn); ?></TD>
</TR>
<TR class='<?php echo GUICommonUtils::styleEO($class)?>'>
	<TD class='label'>Organisation Name:</TD>
	<TD class='field'><?php BasicInputElement::getGeneralFieldString($fldORGNAME,$userTO->organisationName,"text","35","50",$restrictDetails,$restrictDetails,null,null,null); ?></TD>
</TR>

<?php
  //HIDE LAST LOGIN FOR INSERT.
  if ($postDMLTYPE!="INSERT") {
?>
<TR class='<?php echo GUICommonUtils::styleEO($class)?>'>
	<TD class='label'>Last Login:</TD>
	<TD class='field'><font color="#666"><?php echo $userTO->lastLogin ?></font></TD>
</TR>
<?php }


//only admin's can change staff and admin flags... rest only can view.
$canModifyAdminStaffFlags = ($adminUser===true) ? ('N') : ('Y');
?>
<TR class='<?php echo GUICommonUtils::styleEO($class)?>'>
	<TD class='label'>Admin User:</TD>
	<TD class='field'><?php BasicInputElement::getGeneralHorizontalRB($fldAdminF,"Yes,No","Y,N",$userTO->adminUser,$canModifyAdminStaffFlags,$canModifyAdminStaffFlags,null,null,null); ?></TD>
</TR>
<TR class='<?php echo GUICommonUtils::styleEO($class)?>'>
	<TD class='label'>Staff User:</TD>
	<TD class='field'><?php BasicInputElement::getGeneralHorizontalRB($fldStaffF,"Yes,No","Y,N",$userTO->staffUser,$canModifyAdminStaffFlags,$canModifyAdminStaffFlags,null,null,null); ?></TD>
</TR>



<TR class='<?php echo GUICommonUtils::styleEO($class)?>'>
	<TD class='label'>Self Registered:</TD>
	<TD class='field'><?php BasicInputElement::getGeneralHorizontalRB($fldSR,"Yes,No","Y,N",$userTO->selfregistered,"Y","Y",null,null,null); ?></TD>
</TR>
<TR class='<?php echo GUICommonUtils::styleEO($class)?>'>
	<TD class='label'>Suspended:</TD>
	<TD class='field'><?php BasicInputElement::getGeneralHorizontalRB($fldSuspended,"Yes,No","Y,N",$userTO->suspended,"Y","Y",null,null,null); ?></TD>
</TR>
<TR class='<?php echo GUICommonUtils::styleEO($class)?>'>
	<TD class='label'>Deleted:</TD>
	<TD class='field'><?php BasicInputElement::getGeneralHorizontalRB($fldDeleted,"Yes,No","Y,N",$userTO->deleted,$restrictDelete,$restrictDelete,null,null,null); ?></TD>
</TR>
</TABLE>

<BR>
<CENTER><input class='submit' type='submit' value='Submit User' onclick='submitForm("<?php ECHO $postDMLTYPE ?>");'/></CENTER>



<script type='text/javascript' defer>
var alreadySubmitted=false;
function submitContentForm(p_type) {
	if (alreadySubmitted) {
		return;
	}
	alreadySubmitted=true;
	var params='DMLTYPE='+p_type;

	var userId=convertElementToArray(document.getElementsByName("<?php echo $fldChosenUserRB ?>"));
	if ((p_type=="UPDATE") && (userId=="")) {
		alert('Please select a user before Submitting...');
		return;
	}

	if (p_type!='INSERT') params+='&USERID='+convertElementToArray(document.getElementsByName("<?php echo $fldChosenUserRB ?>"));
	else params+='&USERID=';
	params+='&UN='+document.getElementById("<?php echo $fldUN ?>").value;
	//params+='&PWD='+document.getElementById("<?php echo $fldPWD ?>").value;
	params+='&FN='+document.getElementById("<?php echo $fldFN ?>").value;
	params+='&EMAIL='+document.getElementById("<?php echo $fldEmail ?>").value;
	params+='&TEL='+document.getElementById("<?php echo $fldTel ?>").value;
	params+='&CELL='+document.getElementById("<?php echo $fldCell ?>").value;
	params+='&SUSPENDED='+convertElementToArray(document.getElementsByName("<?php echo $fldSuspended ?>"));
	params+='&SELFREGISTERED='+convertElementToArray(document.getElementsByName("<?php echo $fldSR ?>"));
	params+='&DELETED='+convertElementToArray(document.getElementsByName("<?php echo $fldDeleted ?>"));
	params+='&CATEGORY='+convertElementToArray(document.getElementsByName("<?php echo $fldCategory ?>"));

	<?php
	//ONLY CARRY OVER FORM VALUES IF ADMIN USER ELSE USE DB SETTINGS.
	if ($adminUser === true) { ?>
		params+='&BT='+convertElementToArray(document.getElementsByName("<?php echo $fldBT ?>"));
		params+='&ADMINUSER='+convertElementToArray(document.getElementsByName("<?php echo $fldAdminF ?>"));
		params+='&STAFFUSER='+convertElementToArray(document.getElementsByName("<?php echo $fldStaffF ?>"));
	<?php } else { ?>
		params+='&ADMINUSER=<?php echo $userTO->adminUser; ?>';
		params+='&STAFFUSER=<?php echo $userTO->staffUser; ?>';
	<?php } ?>

	params+='&ORGNAME='+document.getElementById("<?php echo $fldORGNAME ?>").value;

	params=params.replace(/'/g,'').replace(/"/g,''); // get rid of quotes which can upset the display element
	AjaxRefreshWithResult(params,
						  '<?php echo $ROOT.$PHPFOLDER ?>functional/administration/adminUserSubmit.php',
						  'alreadySubmitted=false; if (msgClass.type=="S") successCallback("'+p_type+'"); else errorCallback("'+p_type+'");',
						  'Please wait while request is processed...');
}


function successCallback(p_type) {
        if (p_type!="INSERT"){
          toggleSteps(1,"<?php echo $ROOT.$PHPFOLDER ?>");
        }
	if (p_type=="INSERT") {
		document.getElementById("<?php echo $fldUN ?>").value='';
		document.getElementById("<?php echo $fldFN ?>").value='';
		document.getElementById("<?php echo $fldEmail ?>").value='';
		document.getElementById("<?php echo $fldTel ?>").value='';
		document.getElementById("<?php echo $fldCell ?>").value='';
	}

}

function errorCallback(p_type) {
	if (p_type=="INSERT") {
		toggleSteps(1,"<?php echo $ROOT.$PHPFOLDER ?>");
	} else {
		toggleSteps(2,"<?php echo $ROOT.$PHPFOLDER ?>");
	}
}


function userAccessDetails(param,type){

parent.popBoxClose();

  AjaxRefreshHTML("TYPE="+type+"&"+param,
    "<?php echo $ROOT.$PHPFOLDER ?>functional/administration/viewUserRoles.php",
    "userAccessDetails",
    "Retrieving Data...",
    "");

 var html = '<div id="userAccessDetails" style="font-size:16px;overflow:auto;height:320px;background:#fff" align="center"><br><h2 style="color:#999">loading...</h2></div>';
 parent.popBox(html,'general',750);


}


</script>
