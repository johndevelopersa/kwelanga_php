<?php
/* NB:
 * This should only be accessible by a depot user from a depot principal
 */
 
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER."DAO/TransactionDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/StoreDAO.php");
include_once($ROOT.$PHPFOLDER."properties/Constants.php");
include_once($ROOT.$PHPFOLDER."elements/intelliDDElement.php");

if (!isset($_SESSION)) session_start() ;
$userId = $_SESSION['user_id'] ;
$principalId = $_SESSION['principal_id'] ;
$principalAliasId = (($_SESSION['principal_alias_id']=="")?$principalId:$_SESSION['principal_alias_id']);

//Create new database object
$dbConn = new dbConnect(); $dbConn->dbConnection();

if (isset($_GET['DOCMASTID'])) $postDOCMASTID=mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_GET['DOCMASTID']));
else if (isset($_POST['DOCMASTID'])) $postDOCMASTID=mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST['DOCMASTID']));
else $postDOCMASTID="";

// don't do security checks on principal because not document details are revealed, and on submit, the security is then validated
$transactionDAO = new TransactionDAO($dbConn);
$mfT = $transactionDAO->getDepotDocumentItem($userId, $postDOCMASTID); 

if (sizeof($mfT)==0) {
	echo "You do not have access to this information, or order does not exist.";
	return;
}

if (!in_array($mfT[0]["document_type_uid"],array(DT_ORDINV,DT_ORDINV_ZERO_PRICE,DT_DELIVERYNOTE))) {
  echo "Only Orders and Delivery Notes can be managed by depots";
	return;
}


$storeDAO = new StoreDAO($dbConn);
$mfS = $storeDAO->getPrincipalStoreItem($mfT[0]["principal_store_uid"]); 
if (sizeof($mfT)==0) {
	echo "Error ! Store not found !";
	return;
}

$mfSA = $storeDAO->getPrincipalStoreParentAssociations($principalId,$mfT[0]["principal_store_uid"]); // get stores the principal store is linked to of the depot
$mfSL = $storeDAO->getPrincipalStoreSuggestedLinks($principalId,$mfT[0]["principal_store_uid"]);

echo "<style>.miTD {white-space:nowrap;}</style>
			<table class='tableReset'>";
if (in_array($mfT[0]["document_status_uid"],explode(",",$transactionDAO->getUnacceptedOrderStatuses().",".DST_ACCEPTED.",".DST_INPICK))) {
	
	if ((sizeof($mfSA)>1) && ($mfT[0]["depot_principal_store_uid"]=="")) {
	   echo "<tr><td colspan=2 style='color:#d2591e; font-weight:bold;'>This Store was not linked automatically as you have more than one link in place !</td></tr>";
	}
	
  echo "<tr><td>Depot linked Store :</td><td>";

	//----------------------------------------------------
	//STORE SEARCH
	//----------------------------------------------------
  
  echo "<INPUT TYPE='hidden' id=\"MS_STORE\" name=\"MS_STORE\" >";

	//special_field_or & ean_code_or means search on these fields - store name is always enabled for search.
	//ean_code = display column	ean code.
	//Header Name ie: store_name = Store Name  : ucword and remove dashes.
	//columns are displayed in order of below - if uid is set to true will be col 1 by default.
	$columnsArr = array('store_name','depot_name','delivery_day','special_fields','special_field_or');
	
	// this searches store under the DEPOT principal, not the principal and hence does not use pAlias
	IntelliDDElement::selectStoreSearch("MS_STORE", $columnsArr, false, '',"store_name + ' - ' + depot_name+', '+((delivery_day!='Not Known')?delivery_day:'')",$showVendorStores=false,$urlString="");

	echo "</td></tr>
				<tr>
				<td colspan=2>
					<input type='submit' class='submit' value=' Use Once ' onclick='manageUseLink({$postDOCMASTID},document.getElementById(\"MS_STORE\").value);'>
					<input type='submit' class='submit' value=' Link ' onclick='manageSetLink({$postDOCMASTID},{$mfT[0]["principal_store_uid"]},false);'>
				</td>
				</tr>";

  echo "</td></tr>
				<tr>
					<td colspan=2>&nbsp;</td>
				</tr>
				<tr>
					<td colspan=2 style='border-top:1px; border-top-style:solid; font-weight:bold;'>
						Principal Store Details";
						
						$urlParams="?DMLTYPE=INSERT".
												"&SCRSOURCE=DEPOTTT".
												"&PSMCLIENTUID={$mfT[0]["principal_store_uid"]}".
												"&deliver_name=".urlencode($mfS[0]["store_name"]).
												"&deliver_add1=".urlencode($mfS[0]["deliver_add1"]).
												"&deliver_add2=".urlencode($mfS[0]["deliver_add2"]).
												"&deliver_add3=".urlencode($mfS[0]["deliver_add3"]).
												"&bill_name=".urlencode($mfS[0]["bill_name"]).
												"&bill_add1=".urlencode($mfS[0]["bill_add1"]).
												"&bill_add2=".urlencode($mfS[0]["bill_add2"]).
												"&bill_add3=".urlencode($mfS[0]["bill_add3"]).
												"&branch_code=".urlencode($mfS[0]["branch_code"]).
												"&ean_code=".urlencode($mfS[0]["ean_code"]).
												"&vat_number=".urlencode($mfS[0]["vat_number"]).
												"&tel_no1=".urlencode($mfS[0]["tel_no1"]).
												"&tel_no2=".urlencode($mfS[0]["tel_no2"]).
												"&email_add=".urlencode($mfS[0]["email_add"]).
												"&depot_uid=".urlencode($mfT[0]["depot_uid"]).// note the diff obj
												"&DMUID={$postDOCMASTID}"; // note the diff obj
	echo	"</td>
				</tr>
				<tr>
					<td>Deliver Name:</td><td>{$mfS[0]["store_name"]}</td>
				</tr>
				<tr>
					<td>Deliver Addr 1:</td><td>{$mfS[0]["deliver_add1"]}</td>
				</tr>
				<tr>
					<td>Deliver Addr 2:</td><td>{$mfS[0]["deliver_add2"]}</td>
				</tr>
				<tr>
					<td>Deliver Addr 3:</td><td>{$mfS[0]["deliver_add3"]}</td>
				</tr>
				<tr>
					<td>Generic Principal Lookup:</td><td>{$mfS[0]["old_account"]}</td>
				</tr>
				<tr>
					<td colspan=2>
						<input type='submit' class='submit' value='Create Store & Link' onclick='window.open(\"{$ROOT}{$PHPFOLDER}functional/stores/storeForm.php{$urlParams}\",
																																												\"myNewLinkStore\",
																																												\"scrollbars=yes,width=1024,height=600,resizable=yes\");'>
					</td>
				</tr>
				<tr>
					<td colspan=2>&nbsp;</td>
				</tr>
				<tr>
					<td colspan=2 style='border-top:1px; border-top-style:solid; font-weight:bold;'>This principal store is currently linked to these delivery points :</td>
				</tr>
				<tr>
					<td colspan=2>
					<div>
						<table class='tableReset' style='font-size:10px;'>
						<tr><th>Deliver Name</th><th>Deliver Addr 1</th><th>Deliver Addr 2</th><th>Deliver Addr 3</th><th>Area</th><th>Depot</th></tr>";
		
							foreach($mfSA as $r) {
							  echo "<tr id='tr{$r["uid"]}'>
											<td class='miTD'>
												<input type='submit' class='submit' value='Use Once' onclick='manageUseLink({$postDOCMASTID},{$r["psm_parent_uid"]});'>
												<input type='submit' class='submit' value='Remove Link' onclick='manageRemoveLink({$postDOCMASTID},{$r["uid"]});'>
											</td>
											<td class='miTD'>{$r["deliver_name"]}</td>
											<td class='miTD'>{$r["deliver_add1"]}</td>
											<td class='miTD'>{$r["deliver_add2"]}</td>
											<td class='miTD'>{$r["deliver_add3"]}</td>
											<td class='miTD'>{$r["area_description"]}</td>
											<td class='miTD'>{$r["depot_name"]}".(($r["depot_uid"]!=$mfT[0]["depot_uid"])?"<span style='background-color:red;color:yellow;'>!Linked Depot differs from Order!</span>":"")."</td>
											</tr>";
							}
					
	echo   "	</table>
					</div>
					</td>
				</tr>
				<tr>
					<td colspan=2 style='border-top:1px; border-top-style:solid; font-weight:bold;'>Suggested Links :</td>
				</tr>
				<tr>
					<td colspan=2>
					<div>
						<table class='tableReset' style='font-size:10px;'>
						<tr><th>&nbsp;</th><th>Deliver Name</th><th>Deliver Addr 1</th><th>Deliver Addr 2</th><th>Deliver Addr 3</th><th>Area</th><th>Depot</th></tr>";
		
							foreach($mfSL as $r) {
							  echo "<tr>
											<td class='miTD'>
												<input type='submit' class='submit' value='Use Once' onclick='manageUseLink({$postDOCMASTID},{$r["principal_store_uid"]});'>
												<input type='submit' class='submit' value=' Link ' onclick='manageSetLink({$postDOCMASTID},{$mfT[0]["principal_store_uid"]},{$r["principal_store_uid"]});'>
											</td>
											<td class='miTD'>{$r["deliver_name"]}</td>
											<td class='miTD'>{$r["deliver_add1"]}</td>
											<td class='miTD'>{$r["deliver_add2"]}</td>
											<td class='miTD'>{$r["deliver_add3"]}</td>
											<td class='miTD'>{$r["area_description"]}</td>
											<td class='miTD'>{$r["depot_name"]}".(($r["depot_uid"]!=$mfT[0]["depot_uid"])?"<span style='background-color:blue;color:cyan;'>!Linked Depot differs from Order!</span>":"")."</td>
											</tr>";
							}
					
	echo   "	</table>
					</div>
					</td>
				</tr>";
} else {
  echo "Document Status not eligible for Linking by Depot";
}
echo "</table>";

// WARNING : the .load() jquery event causes scripts to be executed twice in IE. The page is not fetched twice. 
echo "<script type='text/javascript' defer>
				if (!debriefDocument.isLoadedLink) {
						debriefDocument.isLoadedLink=true; 
						$('#m_action').unbind('click'); 
						$('#m_action').click(
															function(){ 
																	manageAccept({$postDOCMASTID});
															}
														) 
				}
			</script>"; // assign the click event to action button

?>