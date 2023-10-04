<?php
/* NB:
 * This should only be accessible by a depot user from a depot principal
 */
 
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER."DAO/TransactionDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/AdministrationDAO.php");
include_once($ROOT.$PHPFOLDER."properties/Constants.php");
include_once($ROOT.$PHPFOLDER."elements/basicInputElement.php");
include_once($ROOT.$PHPFOLDER."libs/GUICommonUtils.php");
include_once($ROOT.$PHPFOLDER."elements/datePickerElement.php");

// dont need to do this because it should already be in viewTracking.php ~ DatePickerElement::getDatePickerLibs(); 

if (!isset($_SESSION)) session_start() ;
$userId = $_SESSION['user_id'] ;
$principalId = $_SESSION['principal_id'] ;
$principalAliasId = (($_SESSION['principal_alias_id']=="")?$principalId:$_SESSION['principal_alias_id']);

//Create new database object
$dbConn = new dbConnect(); $dbConn->dbConnection();

if (isset($_GET['DOCMASTID'])) $postDOCMASTID=mysql_real_escape_string(htmlspecialchars($_GET['DOCMASTID']));
else if (isset($_POST['DOCMASTID'])) $postDOCMASTID=mysql_real_escape_string(htmlspecialchars($_POST['DOCMASTID']));
else $postDOCMASTID="";


// check roles
$administrationDAO = new AdministrationDAO($dbConn);
$hasRole = $administrationDAO->hasRole($userId,$principalAliasId,ROLE_ORDER_CAPTURE);
if (!$hasRole) {
	echo "Sorry, you do not have permissions to CAPTURE!";
	return;
}

// this fetch does validation
$transactionDAO = new TransactionDAO($dbConn);
$mfT = $transactionDAO->getDocumentWithDetailItem($userId, $principalAliasId, $postDOCMASTID);
if (sizeof($mfT)==0) {
	echo "You do not have access to this information, or document master does not exist.";
	return;
}

$mfSFArr = $transactionDAO->getDocumentSpecialFields($postDOCMASTID,$mfT[0]["document_type_uid"]);
if (sizeof($mfSFArr)==0) {
	echo "No Special Fields exist for editing against this trade document";
	return;
}


/***************************************************************************
 * NB !!
 * Deleted Status CANNOT be edited, they are just for display purposes !
 * This displays all special fields for a document type regardless of used
 ***************************************************************************/
$paramsJS=array();
echo "<table class='tableReset'>";
foreach ($mfSFArr as $f) {
	echo "<tr>
					<td style='border-bottom:1px; border-bottom-style:dotted; border-bottom-color:#505050;'>
							{$f["name"]} :
					</td>
					<td style='border-bottom:1px; border-bottom-style:dotted; border-bottom-color:#505050; font-size:12px;'>";
  $fldName="MDSF_{$f["uid"]}";
  if ($f["value_validation"]=="DATE") {
    
    if ($f["status"]!=FLAG_STATUS_DELETED) {
      $paramsJS[]="'&MDSF_{$f["uid"]}='+encodeURIComponent(document.getElementById('MDSF_{$f["uid"]}').value)";
    }
    echo DatePickerElement::getDatePicker($fldName,$f["value"],$disabled=(($f["status"]==FLAG_STATUS_DELETED)?true:false));
    
  } else if ($f["value_validation"]=="RADIO") {
    
    if ($f["status"]!=FLAG_STATUS_DELETED) {
      $paramsJS[]="'&MDSF_{$f["uid"]}='+convertElementToArray(document.getElementsByName('MDSF_{$f["uid"]}'))";
    }
    $parts=explode("?",$f["value_list"]);
    BasicInputElement::getCSS3RadioHorizontal($fldName, $f["label_list"], $parts[1], (($f["value"]=="")?$parts[0]:$f["value"]), false);
    
  } else {
    
    if ($f["status"]!=FLAG_STATUS_DELETED) {
      $paramsJS[]="'&MDSF_{$f["uid"]}='+encodeURIComponent(document.getElementById('MDSF_{$f["uid"]}').value)";
    }
    echo "<input id='{$fldName}' name = '{$fldName}' type='text'>";
    
  }
  echo (($f["status"]==FLAG_STATUS_DELETED)?" *Cannot Edit - deleted field":"");
	echo "	</td>
				</tr>";
}
echo "</table>";
//<input id='MDSF_{$f["uid"]}' type='text' size='10' value='{$f["value"]}' ".(($f["status"]==FLAG_STATUS_DELETED)?" DISABLED ":"")."> ".(($f["status"]==FLAG_STATUS_DELETED)?" *Cannot Edit - deleted field":"")."

// WARNING : the .load() jquery event causes scripts to be executed twice in IE. The page is not fetched twice. 
echo "<script type='text/javascript' defer>
				if (!debriefDocument.isLoadedManageSF) {
						debriefDocument.isLoadedManageSF=true; 
						$('#m_action').unbind('click'); 
						$('#m_action').click(
															function(){ 
																	magageDocumentSFAccept({$postDOCMASTID});
															}
														) 
				}";
				?>
				function magageDocumentSFAccept(dMUId) {
				  if (alreadySubmitted){
						alert('You have already clicked on submit... If you are sure the capture has NOT been stored then you may click submit again after 2 minutes.');
						return;
					}
					alreadySubmitted=true;
					
					var params='ACTIONTYPE=UPDATESF&DOCMASTID='+dMUId+<?php echo implode("+",$paramsJS)?>;
					var postJS='';
					AjaxRefreshWithResult(params,
										  '<?php echo $ROOT.$PHPFOLDER ?>functional/transaction/viewTrackingSubmit.php',
										  'alreadySubmitted=false; if(msgClass.type=="S") '+ postJS + ';',
										  'Please wait while request is processed...');
				}
				<?php
echo "</script>"; // assign the click event to action button

?>