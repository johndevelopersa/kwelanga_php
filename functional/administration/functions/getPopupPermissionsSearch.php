<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
ob_start(); //Turn on output buffering
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');

if (!isset($_SESSION)) session_start();
$userId=$_SESSION["user_id"];
$principalId=$_SESSION["principal_id"];

//Create new database object
$dbConn = new dbConnect();

//Database connection method
$dbConn->dbConnection();

$postSEARCHCRITERIA=mysql_real_escape_string(htmlspecialchars($_POST['SEARCHCRITERIA']));
$postSEARCHTYPE=mysql_real_escape_string(htmlspecialchars($_POST['SEARCHTYPE']));

$sCArr=explode(",",strtolower($postSEARCHCRITERIA));

switch ($postSEARCHTYPE) {
	case "summary":
		include_once($ROOT.$PHPFOLDER."DAO/AdministrationDAO.php");
		$adminDAO = new AdministrationDAO($dbConn);
		$mfP = $adminDAO->getPermissionCounts($userId, $principalId);
                if(count($mfP)==0){
                 die('Empty, permission counts! ('.$userId.':'. $principalId.')');
                }
		$pixelToPercRatio = 2;

		echo "You ".("<span style='color:".COLOR_UNOBTRUSIVE_INFO."'>".(($mfP[0]["has_chain_bypass"])?"have":"do not have")."</span>")." the Chain Permissions Bypass Role loaded.<BR>";
		echo "You ".("<span style='color:".COLOR_UNOBTRUSIVE_INFO."'>".(($mfP[0]["has_store_bypass"])?"have":"do not have")."</span>")." the Store Permissions Bypass Role loaded.<BR>";
		echo "You ".("<span style='color:".COLOR_UNOBTRUSIVE_INFO."'>".(($mfP[0]["has_product_bypass"])?"have":"do not have")."</span>")." the Product Permissions Bypass Role loaded.<BR><br>";
		echo "% ~ permissions allocated to you";
		echo "<TABLE style='text-align:left;'>";

		$perc=($mfP[0]["store_pcnt"]>0)?round(($mfP[0]["store_pcnt"]/$mfP[0]["store_totcnt"])*100):0;
		$width=round($pixelToPercRatio*($perc));
		echo "<TR><TD nowrap>Stores (within my<br>chains & depots):</TD><TD nowrap><div style='background-color:#00AA80; width:{$width}px;'>&nbsp;</div></td><td>{$perc}% ({$mfP[0]["store_pcnt"]} out of {$mfP[0]["store_totcnt"]})</TD></TR>";

		$perc=$perc=($mfP[0]["product_pcnt"]>0)?round(($mfP[0]["product_pcnt"]/$mfP[0]["product_totcnt"])*100):0;
		$width=round($pixelToPercRatio*($perc));
		echo "<TR><TD nowrap>Products:</TD><TD nowrap><div style='background-color:#00AAAA; width:{$width}px;'>&nbsp;</div></td><td>{$perc}% ({$mfP[0]["product_pcnt"]} out of {$mfP[0]["product_totcnt"]})</TD></TR>";

		$perc=$perc=($mfP[0]["chain_pcnt"]>0)?round(($mfP[0]["chain_pcnt"]/$mfP[0]["chain_totcnt"])*100):0;
		$width=round($pixelToPercRatio*($perc));
		echo "<TR><TD nowrap>Chains:</TD><TD nowrap><div style='background-color:#00AACC; width:{$width}px;'>&nbsp;</div></td><td>{$perc}% ({$mfP[0]["chain_pcnt"]} out of {$mfP[0]["chain_totcnt"]})</TD></TR>";

		$perc=$perc=($mfP[0]["prindepot_pcnt"]>0)?round(($mfP[0]["prindepot_pcnt"]/$mfP[0]["prindepot_totcnt"])*100):0;
		$width=round($pixelToPercRatio*($perc));
		echo "<TR><TD nowrap>Depots:</TD><TD nowrap><div style='background-color:#00AAFF; width:{$width}px;'>&nbsp;</div></td><td>{$perc}% ({$mfP[0]["prindepot_pcnt"]} out of {$mfP[0]["prindepot_totcnt"]})</TD></TR>";

		echo "</TABLE>";
		break;
	case "stores":
		include_once($ROOT.$PHPFOLDER."DAO/StoreDAO.php");
		if (trim($postSEARCHCRITERIA)=="") {
			echo "Search Criteria cannot be blank.";
			return;
		}
		$storeDAO = new StoreDAO($dbConn);
		$mfSAS = $storeDAO->getUserSearchPrincipalStoreArray($userId, $principalId, $sCArr);
		echo "Strikethrough ~ you do <span style='color:white;'>NOT</span> have permissions for this store";
		echo "<TABLE style='text-align:left;'>";
		echo "<TR>
				  <TH>Store Name</TH>
				  <TH>Delivery Addr 1</TH>
				  <TH>Depot Name</TH>
				  <TH>Chain Name</TH>
				  <TH>Principal</TH>
				  <TH>GLN</TH>
				  <TH>Owned By</TH>
				  <TH>Status</TH>
				  </TR>";
		foreach ($mfSAS as $s) {
			if (!$s["has_store_permission"]) $font="text-decoration:line-through;"; else $font="";
			echo "<TR>
				  <TD style='{$font}' nowrap>{$s["store_name"]}</TD>
				  <TD style='{$font}' nowrap>{$s["deliver_add1"]}</TD>
				  <TD style='{$font}' nowrap>{$s["depot_name"]}</TD>
				  <TD style='{$font}' nowrap>{$s["chain_name"]}</TD>
				  <TD style='{$font}' nowrap>{$s["principal"]}</TD>
				  <TD style='{$font}' nowrap>{$s["ean_code"]}</TD>
				  <TD style='{$font}' nowrap>{$s["vendor_created_by_uid"]}</TD>
				  <TD style='{$font}' nowrap>{$s["status"]}</TD>
				  </TR>";
		}
		echo "</TABLE>";
		break;
	case "products":
		include_once($ROOT.$PHPFOLDER."DAO/ProductDAO.php");
		if (trim($postSEARCHCRITERIA)=="") {
			echo "Search Criteria cannot be blank.";
			return;
		}
		$productDAO = new ProductDAO($dbConn);
		$mfP = $productDAO->getUserSearchPrincipalProductArray($userId, $principalId, $sCArr);
		echo "<TABLE style='text-align:left;'>";
		echo "<TR>
				  <TH>Product Code</TH>
				  <TH>Product Description</TH>
				  </TR>";
		foreach ($mfP as $p) {
			if (!$p["has_product_permission"]) $font="text-decoration:line-through;"; else $font="";
			echo "<TR>
				  <TD style='{$font}' nowrap>{$p["product_code"]}</TD>
				  <TD style='{$font}' nowrap>{$p["product_description"]}</TD>
				  </TR>";
		}
		echo "</TABLE>";
          break;

 	case "document":

       	include_once($ROOT.$PHPFOLDER.'DAO/TransactionDAO.php');

	$postUSERID = $userId;
	$postDOCUMENTNUMBER = str_pad(trim($postSEARCHCRITERIA), 8 ,0, STR_PAD_LEFT);

	echo "<BR>";

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

    break;

}

$dbConn->dbClose();

$htmlBody = ob_get_clean();
$htmlBody = gzencode($htmlBody, 9, FORCE_GZIP);
header ("Content-Encoding: gzip");
header ('Content-Length: '.strlen($htmlBody));
echo $htmlBody;
?>