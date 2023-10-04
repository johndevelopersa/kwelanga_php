<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'elements/basicSelectElement.php');
include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
CommonUtils::getSystemConventions();

if(!isset($_SESSION)) session_start;
$userId = $_SESSION['user_id'];
$principalId = $_SESSION['principal_id'];

//Create new database object
$dbConn = new dbConnect();
$dbConn->dbConnection();

$adminUser=(isset($_SESSION['admin_user']) && ($_SESSION['admin_user']=="Y"))?true:false;


$fldChosenRoleRB='ChosenRole';  // field names for this form
$fldChosenUserRB='ChosenUser';
$fldChosenPrincipal='ChosenPrincipal';
$divAjaxRoleList="ajaxRoleList";  // the ajax divs. refreshed independently
$divAjaxUserList="ajaxUserList";
$divAjaxUserPrincipalList="ajaxUserPrincipalList";
$divAjaxUserRoleList="ajaxUserRoleList";
print("&nbsp;"); // for some reason, the javascript does NOT work if this line is missing.


function userList() {
	global $ROOT; global $PHPFOLDER; global $divAjaxUserList; global $fldChosenUserRB; global $adminUser;
	echo("<span style=''>Please choose a user to apply roles to...</span>");
	echo("<div id='".$divAjaxUserList."'></div>");
	echo("<scr"."ipt type='text/javascript' defer>");
            echo("AjaxRefresh(\"RBNAME=".$fldChosenUserRB."&CALLBACK=refreshUserPrincipals(); nextStep(2); \",");
               echo("\"".$ROOT.$PHPFOLDER."functional/administration/adminUsersListTable.php\",
               \"".$divAjaxUserList."\",
               \"Please wait whilst page is refreshed...\",
               \"\");");
	echo("</scr"."ipt>");
}

function principalList() {
	global $divAjaxUserPrincipalList;
	echo("<span>Please choose a ".SNC::principal." for this user to apply roles to...</span>");
	echo("<div id='".$divAjaxUserPrincipalList."'><br><font color='red'>Select a user first!</font></div>");
	//echo("<scr"."ipt type='text/javascript' defer>");
	//echo("refreshUserPrincipals();");
	//echo("</scr"."ipt>");
}

function roleList() {
	global $divAjaxRoleList;

	echo("<div id='".$divAjaxRoleList."'><br><font color='red'>Select a user and ".SNC::principal." first!</font></div>");
	echo("<scr"."ipt type='text/javascript' defer>");
	// print("refreshRoles();"); // not necessary because refreshUserPrincipals() calls refreshRoles afterwards itself
	echo("</scr"."ipt>");
}


  // the step icons
  GUICommonUtils::getSteps(array("Step 1<BR>Select User","Step 2:<BR>Select ".SNC::principal,"Step 3:<BR>Select Role/Profile","Step 4:<BR>Submit Changes"));

  echo("<div id='step1'>");
  userList();
  echo("</div>");

  echo("<div id='step2'>");
  principalList();
  echo("</div>");

  echo("<div id='step3'>");
  roleList();
  echo("</div>");

  echo("<div id='step4'>");
  echo "<BR>";
  echo("<input class='submit' type='submit' value='Add Role to User' onclick='submitContentForm(\"INSERT\");'/>");
  echo("<input class='submit' type='submit' value='Remove Role from User' onclick='submitContentForm(\"DELETE\");'/>");
  echo("</div>");

  /*
  echo("<div id='step5'>");
  echo("<div id='".$divAjaxUserRoleList."'>No User Selected. Please choose User in Step 1</div>");
  echo("</div>");
  */


?>

<script type='text/javascript' defer>


  function refreshUserPrincipals() {
    AjaxRefresh("RBNAME=<?php echo $fldChosenPrincipal?>&USERID="+convertElementToArray(document.getElementsByName("<?php echo $fldChosenUserRB ?>"))+"&TAGID=<?php echo $fldChosenPrincipal; ?>&PRINCIPALID=<?php echo $principalId; ?>&CALLBACK=refreshRoles();refreshUserRoles(); nextStep(3);",
                            "<?php echo $ROOT.$PHPFOLDER ?>functional/administration/adminPrincipalsListTable.php",
                            "<?php echo $divAjaxUserPrincipalList ?>",
                            "Please wait whilst user principals are refreshed...",
                            "");
  }

  // must be separated out and used in Callback due to dependency
  function refreshRoles() {

    AjaxRefresh("RBNAME=<?php echo $fldChosenRoleRB ?>&USERID="+convertElementToArray(document.getElementsByName("<?php echo $fldChosenUserRB ?>"))+"&PRINCIPALID="+convertElementToArray(document.getElementsByName("<?php echo $fldChosenPrincipal ?>")),
                            "<?php echo $ROOT.$PHPFOLDER ?>functional/administration/adminRolesListTable.php",
                            "<?php echo $divAjaxRoleList ?>",
                            "Please wait whilst roles are refreshed...",
                            "");
  }

  function refreshUserRoles(){return;}

  /*
  function refreshUserRoles() {
                  AjaxRefresh("USERID="+convertElementToArray(document.getElementsByName("<?php echo $fldChosenUserRB ?>"))+"&FILTERONPRINCIPAL=Y&FILTERPRINCIPALVALUE="+convertElementToArray(document.getElementsByName("<?php echo $fldChosenPrincipal ?>")),
                                          "<?php echo $ROOT.$PHPFOLDER ?>functional/administration/adminUserRolesListTable.php",
                                          "<?php echo $divAjaxUserRoleList ?>",
                                          "Please wait whilst user roles are refreshed...",
                                          "");
  }
  */

  function applyProfile(){
    var list = $('#roleProfile').val();

    if(list == '' || list == undefined){
      alert('Please select a profile!');
    } else {
      var params='DMLTYPE=INSERT';
      params+='&USERID='+convertElementToArray(document.getElementsByName("<?php echo $fldChosenUserRB ?>"));
      params+='&PRINCIPALID='+convertElementToArray(document.getElementsByName("<?php echo $fldChosenPrincipal ?>"));
      params+='&ROLEID='+list;

      submitContentFinal("INSERT", params);
    }
  }


  function submitContentForm(p_type){
    var params='DMLTYPE='+p_type;
    params+='&USERID='+convertElementToArray(document.getElementsByName("<?php echo $fldChosenUserRB ?>"));
    params+='&PRINCIPALID='+convertElementToArray(document.getElementsByName("<?php echo $fldChosenPrincipal ?>"));
    params+='&ROLEID='+convertElementToArray(document.getElementsByName("<?php echo $fldChosenRoleRB ?>"));
    params=params.replace(/'/g,'').replace(/"/g,''); // get rid of quotes which can upset the display element

    submitContentFinal(p_type, params);
  }

  $("div[id*='step']").css({display:'none'});
  $("#step1").css({display:'block'});
  toggleSteps(1,"<?php echo $ROOT.$PHPFOLDER ?>");


  var alreadySubmitted=false;

  function submitContentFinal(p_type,params) {
    if (alreadySubmitted) {
            return;
    }
    alreadySubmitted=true;

    AjaxRefreshWithResult(params,
                          '<?php echo $ROOT.$PHPFOLDER ?>functional/administration/userRolesSubmit.php',
                          'alreadySubmitted=false; refreshUserRoles(); refreshRoles();  if (msgClass.type=="S") successCallback("'+p_type+'"); else errorCallback("'+p_type+'");',
                          'Please wait while request is processed...');
  }

  function successCallback(p_type) {
          toggleSteps(1,"<?php echo $ROOT.$PHPFOLDER ?>");
  }

  function errorCallback(p_type) {
          toggleSteps(1,"<?php echo $ROOT.$PHPFOLDER ?>");
  }
  function nextStep(step) {
	toggleSteps(step,"<?php echo $ROOT.$PHPFOLDER ?>");
  }
</script>
