<?php

/*
 * 
 * THIS SCREEN is to be used as an AUDIT. Therefore show as many fields as possible, and avoid any coding that could erroneously "hide" rows 
 * from view such as inner joins on wrong values
 * 
 */
	include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
	require($ROOT.$PHPFOLDER."functional/main/access_control.php");
	include_once($ROOT.$PHPFOLDER."elements/datePickerElement.php");
	include_once($ROOT.$PHPFOLDER.'DAO/TransactionDAO.php');
	include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
	include_once($ROOT.$PHPFOLDER.'elements/basicInputElement.php');
	include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
	include_once($ROOT.$PHPFOLDER.'elements/basicSelectElement.php');
	include_once($ROOT.$PHPFOLDER."libs/CommonUtils.php");
	
	
	if (!isset($_SESSION)) session_start();
	$principalId  = $_SESSION['principal_id'];
	$userId       = $_SESSION['user_id'];

	$dbConn = new dbConnect();
	$dbConn->dbConnection();

	$adminUser=(isset($_SESSION['admin_user']) && ($_SESSION['admin_user']=="Y"))?true:false;
	if (!$adminUser) {
		echo "Sorry, you do not have permissions to VIEW ORDER AUDITS!";
		return;
	}
	/*
	 * NB ! Remember to limit view of pricing if ever we decide to extend access past just administrators
	 * 		$hasRoleVP = $adminDAO->hasRole($userId,$principalId,ROLE_VIEW_PRICE); 
	 */
	 
    if (!isset($_GET["PFORM_CBFILTER"])) $firstTime = true; else $firstTime = false;
	
	if (isset($_GET["FILTERLIST"])) { $postFilterList=$_GET["FILTERLIST"]; $postFilterList=explode(',',$postFilterList); } else $postFilterList="";
	if (isset($_GET["PFORM_CBFILTER"])) $postCBFILTER=mysql_real_escape_string(htmlspecialchars($_GET["PFORM_CBFILTER"])); else $postCBFILTER="";
	if (isset($_GET["PFORM_CFROMDATE"])) $postCFROMDATE=mysql_real_escape_string(htmlspecialchars($_GET["PFORM_CFROMDATE"])); else $postCFROMDATE="";
	if (isset($_GET["PFORM_CTODATE"])) mysql_real_escape_string(htmlspecialchars($postCTODATE=$_GET["PFORM_CTODATE"])); else $postCTODATE="";
	if (isset($_GET["PFORM_OFROMDATE"])) $postOFROMDATE=mysql_real_escape_string(htmlspecialchars($_GET["PFORM_OFROMDATE"])); else $postOFROMDATE="";
	if (isset($_GET["PFORM_OTODATE"])) mysql_real_escape_string(htmlspecialchars($postOTODATE=$_GET["PFORM_OTODATE"])); else $postOTODATE="";
	if (isset($_GET["PFORM_ORDSEQ"])) mysql_real_escape_string(htmlspecialchars($postORDSEQ=$_GET["PFORM_ORDSEQ"])); else $postORDSEQ="";
	if (isset($_GET["PFORM_USER"])) mysql_real_escape_string(htmlspecialchars($postUSER=$_GET["PFORM_USER"])); else $postUSER="";
	
	if ($postCFROMDATE=="") $postCFROMDATE = CommonUtils::getUserDate();
	if ($postCTODATE=="") $postCTODATE = CommonUtils::getUserDate();
	if ($postOFROMDATE=="") $postOFROMDATE = CommonUtils::getUserDate();
	if ($postOTODATE=="") $postOTODATE = CommonUtils::getUserDate();
	
	$cbFilter = explode(",",$postCBFILTER);

	echo "<HTML>";

	echo "<HEAD>";
	echo "<script type='text/javascript' language='javascript' src='".$DHTMLROOT.$PHPFOLDER."js/jquery.js'></script>";
	echo "<script type='text/javascript' language='javascript' src='".$DHTMLROOT.$PHPFOLDER."js/dops_global_functions.js'></script>";
	// autoscroll start
	echo "<script type='text/javascript' language='javascript' src='".$DHTMLROOT.$PHPFOLDER."js/jquery.autoscroll.js'></script>";
	echo "<scr"."ipt type=\"text/javascript\">";
	echo "\$(document).ready(function(){ document.body.focus(); \$.autoscroll.init({step: 200}); });";
	echo "</scr"."ipt>";
	// autoscroll end
	DatePickerElement::getDatePickerLibs();
	echo "<LINK href='".$ROOT.$PHPFOLDER."css/default.css' rel='stylesheet' type='text/css'>" ;
	echo "</HEAD>";

	echo "<BODY><CENTER>";
	echo "<SPAN style='color:".COLOR_UNOBTRUSIVE_INFO."; font-weight:bold'>***NEW FEATURE*** To automatically scroll across, press and hold down CTRL and move mouse to edge of page</SPAN>";
	echo "<FORM action='".$_SERVER['PHP_SELF']."'  style='margin:0; padding:0;'>";
	echo "<BR><SPAN style='font-family:Verdana,Arial,Helvetica,sans-serif; font-weight:bold;font-size:0.8em;'>Parameters</SPAN>";
	echo "<TABLE class='tblReset'>";
	echo "<TR>";
		if (($firstTime) || (in_array("CD",$cbFilter))) $CHECKED=" CHECKED "; else $CHECKED="";
		echo "<TD><input type='checkbox' name='PFORM_CBFILTER' value='CD' ".$CHECKED." ></TD>";
		echo "<TD>Capture Date :</TD>";
		echo "<TD>From: ";
			DatePickerElement::getDatePicker("PFORM_CFROMDATE",$postCFROMDATE);
			echo "&nbsp;&nbsp;To: ";
			DatePickerElement::getDatePicker("PFORM_CTODATE",$postCTODATE);
		echo "</TD>";
	echo "</TR>";
	echo "<TR>";
		if (in_array("OD",$cbFilter)) $CHECKED=" CHECKED "; else $CHECKED="";
		echo "<TD><input type='checkbox' name='PFORM_CBFILTER' value='OD' ".$CHECKED." ></TD>";
		echo "<TD>Order Date :</TD>";
		echo "<TD>From: ";
			DatePickerElement::getDatePicker("PFORM_OFROMDATE",$postOFROMDATE);
			echo "&nbsp;&nbsp;To: ";
			DatePickerElement::getDatePicker("PFORM_OTODATE",$postOTODATE);
		echo "</TD>";
	echo "</TR>";
	echo "<TR>";
		if (in_array("USER",$cbFilter)) $CHECKED=" CHECKED "; else $CHECKED="";
		echo "<TD><input type='checkbox' name='PFORM_CBFILTER' value='USER'".$CHECKED." ></TD>";
		echo "<TD>Captured By :</TD>";
		echo "<TD>";
			BasicSelectElement::getUsersWithinPriviledgesDD("PFORM_USER",$postUSER,"N","N",null,null,null,$dbConn,$userId, $principalId);
		echo "</TD>";
	echo "</TR>";
	echo "<TR>";
		if (in_array("ORDSEQ",$cbFilter)) $CHECKED=" CHECKED "; else $CHECKED="";
		echo "<TD><input type='checkbox' name='PFORM_CBFILTER' value='ORDSEQ'".$CHECKED." ></TD>";
		echo "<TD>Order Sequence :</TD>";
		echo "<TD>";
			BasicInputElement::getGeneralFieldString("PFORM_ORDSEQ",$postORDSEQ,"text",20,20,"N","N",null,null,null);
		echo "</TD>";
	echo "</TR>";
	echo "<TR style='text-align:center'>";
		echo "<TD colspan=3><input type='button' class='submit' value='Submit' onclick='submitMainParmsOnly();' /></TD>";
	echo "</TR>";
	echo "</TABLE>";
	echo "</FORM>";
	
	$transactionDAO = new TransactionDAO($dbConn);
	// build up where clause
	$p_postCFROMDATE=""; $p_postCTODATE=""; $p_postOFROMDATE=""; $p_postOTODATE=""; $p_postUSER=""; $p_postORDSEQ="";
	foreach ($cbFilter as $f) {
		switch ($f) {
			case "CD" :
				$p_postCFROMDATE=$postCFROMDATE;
				$p_postCTODATE=$postCTODATE;
				break;
			case "OD" :
				$p_postOFROMDATE=$postOFROMDATE;
				$p_postOTODATE=$postOTODATE;
				break;
			case "USER" :
				$p_postUSER=$postUSER;
				break;
			case "ORDSEQ" :
				$p_postORDSEQ=$postORDSEQ;
				break;
			default:
		} 
	}
	if (!$firstTime) {
		if ($adminUser) $mfDocs = $transactionDAO->getOrdersArray($p_postCFROMDATE.",".$p_postCTODATE, $p_postOFROMDATE.",".$p_postOTODATE, $p_postUSER, $p_postORDSEQ);
		else $mfDocs = $transactionDAO->getUserOrdersArray($userId, $principalId, $p_postCFROMDATE.",".$p_postCTODATE, $p_postOFROMDATE.",".$p_postOTODATE, $p_postUSER, $p_postORDSEQ);
	} else $mfDocs=array();
	
	// field names for this form
	$fldFilterListname="OrdListFilter"; // the names of the filter fields
	$fldFilterListUsageArr=array(1=>"Y","Y","Y","Y","Y","Y","Y","Y","Y","Y","Y","Y","Y");
	$fldFilterListSizeArr=array(1=>"5","5","5","5","5","5","5","5","5","5","5","5","5");
	
	$ordListArr=array(); // strip off the columns we want. 
	$tdExtraColArr=array(); $tdExtraRowArr=array();
	$tdExtraColArr[0]=" nowrap ";
	$tdExtraColArr[1]=" nowrap ";
	$tdExtraColArr[2]=" nowrap ";
	$tdExtraColArr[3]=" nowrap ";
	$tdExtraColArr[4]=" nowrap ";
	$tdExtraColArr[5]=" nowrap ";
	$tdExtraColArr[6]=" nowrap ";
	$tdExtraColArr[7]=" nowrap ";
	$tdExtraColArr[8]=" nowrap ";
	$tdExtraColArr[9]=" nowrap ";
	$tdExtraColArr[10]=" nowrap ";
	$tdExtraColArr[11]=" nowrap ";
	$tdExtraColArr[12]=" nowrap ";
	$headers=array("EDI FileName","Captured By","Principal uid","Store Name","Document Type Description","Capture Date","EDI Created",
					"Order Number","Order Sequence","Order Date","Deleted","Document Type","Delivery Date");
	class OrdTbl {
		public $ediFileName;
		public $capturedBy;
		public $principalUId;
		public $storeName;
		public $documentType;
		public $captureDate;
		public $ediCreated;
		public $orderNumber;
		public $orderSequence;
		public $orderDate;
		public $deleted;
		public $documentTypeDescription;
		public $deliveryDate;
	}

	for ($i=0; $i<sizeof($mfDocs); $i++) {
		$row=$mfDocs[$i];
		
		$class=new OrdTbl;
		$class->capturedBy = $row["user_uid"]." - ".$row["capture_name"];
		$class->storeName = $row["deliver_name"];
		$class->principalUId = $row["principal_uid"];
		$class->orderNumber = $row["order_number"];
		$class->orderSequence = "<A href='#' onclick='showOrderCard(".$row["order_sequence_no"].");' >".$row["order_sequence_no"]."</A>";
		$class->orderDate = $row["order_date"];
		$class->captureDate = $row["capturedate"];
		$class->deleted = $row["deleted"];
		$class->ediCreated = $row["edi_created"];
		$class->ediFileName = "<A href='#' alt='download file' onclick='javascript:window.open(\"".$ROOT.$PHPFOLDER."functional/general/downloadFile.php?TYPE=ORDER&UID=".$row['order_sequence_no']."\",\"downloadFile\",\"scrollbars=yes,width=400,height=550\");'>".basename($row["edi_filename"])."</A>";
		$class->documentType = $row["document_type"];
		$class->documentTypeDescription = $row["dt_description"];
		$class->deliveryDate = $row["deliverydate"];
		
		$ordListArr[]=$class;
	}
	
	$pArr=GUICommonUtils::applyFilter($ordListArr,$postFilterList);
	
	echo "<BR><BR>";
	print("<TABLE>");
	
			GUICommonUtils::getFilterFieldsNonAjax($fldFilterListname,
													$fldFilterListUsageArr,
													$fldFilterListSizeArr,
													$postFilterList,
													"+'&'+getFilters();",
													$ROOT.$PHPFOLDER."functional/transaction/viewOrders.php");
			// the data
			GUICommonUtils::outputTable ($headers,
										 $pArr,
										 $tdExtraColArr,
										 $tdExtraRowArr);
										   
	print("</TABLE>");
	
	echo "</CENTER></BODY></HTML>";
	$dbConn->dbClose();
	echo "<img name='endpositioner' src='".$DHTMLROOT.$PHPFOLDER."images/invis.gif' />"; // used by adjustMyFrameHeight() to get bottom position. offset calculation is otherwise unreliable
?>
<SCRIPT type="text/javascript" defer>
adjustMyFrameHeight();

function getFilters() {
	var params="PFORM_CBFILTER="+convertElementToArray(document.getElementsByName("PFORM_CBFILTER"));
	params+="&PFORM_CFROMDATE="+document.getElementById("PFORM_CFROMDATE").value;
	params+="&PFORM_CTODATE="+document.getElementById("PFORM_CTODATE").value;
	params+="&PFORM_OFROMDATE="+document.getElementById("PFORM_OFROMDATE").value;
	params+="&PFORM_OTODATE="+document.getElementById("PFORM_OTODATE").value;
	params+="&PFORM_USER="+document.getElementById("PFORM_USER").value;
	params+="&PFORM_ORDSEQ="+document.getElementById("PFORM_ORDSEQ").value;

	return params;
}

function submitMainParmsOnly() {
	window.location="<?php echo $_SERVER['PHP_SELF'] ?>?"+getFilters();
}

function showOrderCard(val) {
	window.open('<?php echo $ROOT.$PHPFOLDER; ?>functional/transaction/orderCard.php?DOCMASTID='+val,'myOrder','scrollbars=yes,width=700,height=500');
}
</SCRIPT>
