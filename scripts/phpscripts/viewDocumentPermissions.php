<?php
	include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
	require($ROOT.$PHPFOLDER."functional/main/access_control.php");
	include_once($ROOT.$PHPFOLDER.'DAO/TransactionDAO.php');
	include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
	include_once($ROOT.$PHPFOLDER.'elements/basicSelectElement.php');
	
	if (!isset($_SESSION)) session_start();
	$principalId  = $_SESSION['principal_id'];
	$userId       = $_SESSION['user_id'];

	$dbConn = new dbConnect();
	$dbConn->dbConnection();
	
	if (isset($_GET["USERID"])) $postUSERID=$_GET["USERID"]; else $postUSERID=$userId;
	if (isset($_GET["DOCUMENTNUMBER"])) $postDOCUMENTNUMBER=$_GET["DOCUMENTNUMBER"]; else $postDOCUMENTNUMBER="";
	
	echo "<HTML>
		  <HEAD>
		    <LINK href='".$ROOT.$PHPFOLDER."css/default.css' rel='stylesheet' type='text/css'>
			<script type='text/javascript' language='javascript' src='".$DHTMLROOT.$PHPFOLDER."js/jquery.js'></script>
			<script type='text/javascript' language='javascript' src='".$DHTMLROOT.$PHPFOLDER."js/dops_global_functions.js'></script>
		  </HEAD>
		  <BODY><CENTER>";
	
	
	echo "<FORM id='pForm' name='pForm' action='".$_SERVER['PHP_SELF']."'  style='margin:0; padding:0;'>";
	echo "<TABLE class='tblReset'>";
	echo "<TR>";
	echo "<TD>Check Permissions for User : </TD>";
	echo "<TD>";
		BasicSelectElement::getUsersWithinPriviledgesDD("USERID",$postUSERID,"N","N","document.pForm.submit();",null,null,$dbConn,$userId, $principalId);
	echo "</TD>";
	echo "</TR>";
	echo "<TR>";
	echo "<TD>Document Number : </TD>";
	echo "<TD>";
		echo "<INPUT name=\"DOCUMENTNUMBER\" type=\"text\" value='".$postDOCUMENTNUMBER."' \>";
	echo "</TD>";
	echo "</TR>";
	echo "</TABLE>";
	echo "<BR><INPUT class='submit' type='submit' value='submit'>";
	echo "</FORM>";
	
	echo "<BR>";
	
	// results
	if (($postUSERID!="") && ($postDOCUMENTNUMBER!="")) {
		echo "<SPAN style='color:".COLOR_UNOBTRUSIVE_INFO."'>
			Store/Chain/Product details are not revealed so as to fulfill security completeness.<BR>
		  </SPAN>";
		  
		$tranDAO = new TransactionDAO($dbConn);
		
		$mfT = $tranDAO->getDocumentWithDetailByDNItem($postUSERID, $principalId, $postDOCUMENTNUMBER);
		echo sizeof($mfT)." document(s) found.<BR><BR>";
		foreach ($mfT as $doc) {
			echo "Processed : ".$doc["processed_date"].", Order Sequence No: ".$doc["order_sequence_no"].", Depot UId: ".$doc["depot_uid"]."<BR>";
			echo "<SPAN style='color:".COLOR_UNOBTRUSIVE_INFO."'>";
			if ($doc["upd_uid"]!="") echo "User has permissions for principal-depot<BR>"; else echo "User does <B><U>NOT</U></B> have permissions for principal-depot<BR>";
			if ($doc["ups_uid"]!="") echo "User has permissions for principal-store<BR>"; else echo "User does <B><U>NOT</U></B> have permissions for principal-store<BR>";
			if ($doc["upc_uid"]!="") echo "User has permissions for principal-chain<BR>"; else echo "User does <B><U>NOT</U></B> have permissions for principal-chain<BR>";
			$doesNotHaveAPermission=false;
			foreach ($doc["document_detail_array"] as $det) {
				if ($det["upp_uid"]=="") $doesNotHaveAPermission=true;
			}
			if ($doesNotHaveAPermission===false) echo "User has permissions for all principal-products<BR>"; else echo "User does <B><U>NOT</U></B> have permissions for atleast 1 principal-product";
			echo "</SPAN>";
		}
		
	}
	
	echo "<img name='endpositioner' src='".$DHTMLROOT.$PHPFOLDER."images/invis.gif' />"; // used by adjustMyFrameHeight() to get bottom position. offset calculation is otherwise unreliable
?>
<SCRIPT type="text/javascript">
parent.adjustMyFrameHeight();
</SCRIPT>
</CENTER>
</BODY>
</HTML>