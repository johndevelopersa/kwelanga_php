<?php

	include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
	require($ROOT.$PHPFOLDER."functional/main/access_control.php");
	include_once($ROOT.$PHPFOLDER.'DAO/ImportDAO.php');
	include_once($ROOT.$PHPFOLDER.'DAO/TransactionDAO.php');
	include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
	include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
	include_once($ROOT.$PHPFOLDER."libs/CommonUtils.php");


	if (!isset($_SESSION)) session_start();
	$principalId  = $_SESSION['principal_id'];
	$userId       = $_SESSION['user_id'];
	$staffUser    = $_SESSION['staff_user'];

	if (isset($_GET["FLUID"])) $postFLUID=$_GET["FLUID"]; else $postFLUID="";

	$dbConn = new dbConnect();
	$dbConn->dbConnection();

	$importDAO = new ImportDAO($dbConn);
	$mfF = $importDAO->getPrincipalEDIFilesProcessed($principalId, $fLUId=false, $sortBy=(ImportDAO::$FILE_SORT_BY_PROCESSED_DATE_DESC));

  // DOCTYPE is needed for the select options filter to work in IE8+ !!
	echo "<!DOCTYPE html><html><head>";
	echo "<script type='text/javascript' language='javascript' src='".$DHTMLROOT.$PHPFOLDER."js/jquery.js'></script>";
	echo "<script type='text/javascript' language='javascript' src='".$DHTMLROOT.$PHPFOLDER."js/dops_global_functions.js'></script>
		  <meta name='SKYPE_TOOLBAR' content='SKYPE_TOOLBAR_PARSER_COMPATIBLE' />
	      <link href='".$ROOT.$PHPFOLDER."css/default.css' rel='stylesheet' type='text/css'>";

	// autoscroll start
	echo "<script type='text/javascript' language='javascript' src='".$DHTMLROOT.$PHPFOLDER."js/jquery.autoscroll.js'></script>";
	echo "<scr"."ipt type=\"text/javascript\">";
	echo "\$(document).ready(function(){ document.body.focus(); \$.autoscroll.init({step: 200}); });";
	echo "</scr"."ipt>";
	// autoscroll end

  // this needs to go here as there is a return before the end
  ?>
  <script type='text/javascript' defer>
  var arrFullFileList=new Array();
  function filterFileDD() {
    var criteria=document.getElementById('fldFileCriteria').value,
        dd=document.getElementById('fldFileUId');
    
    // if full list is not already stored, store it (from first download)
    if (arrFullFileList.length==0) {
      // start array from 1 so that "Not Selected" is excluded
      for (var i=1; i<dd.length; i++) {
        arrFullFileList.push({'t':dd[i].text,'v':dd[i].value});
      }
    }
    
    // remove all from DD in preparation for filter
    // +1 because first row was skipped
    for (var i=0; i<arrFullFileList.length+1; i++) {
      dd.remove(0);
    }
    
    // add back in the ones matching
    var option;
    for (var i=0; i<arrFullFileList.length; i++) {
      if ((criteria.trim()=='') || (arrFullFileList[i].t.toUpperCase().search(criteria.toUpperCase().trim())>-1)) {
        option=document.createElement("option");
        option.text=arrFullFileList[i].t;
        option.value=arrFullFileList[i].v;
        try {
          // for IE earlier than version 8
          dd.add(option, dd.options[null]);
        } catch (e) {
          dd.add(option, null);
        }
      }
    }
    
    // add first line
    option=document.createElement("option");
    if (criteria.trim()=='') {
      option.text="Not Selected";
      option.value="";
    } else {
      option.text="("+dd.length+") Filtered Rows Found";
      option.value="";
    }
    try {
      dd.add(option, dd.options[0]);
    } catch (e) {
      dd.add(option, 0);
    }
    dd.selectedIndex=0;
    
    
  }
  
  </script>
  <?php


	echo "</head>";
	echo "<body>";

	// search instead
	echo "<div align='center'>
      <table>
      <tr><td>Filter the file list :</td><td><input id='fldFileCriteria' size=50><input type='submit' class='submit' value='Filter' onclick='filterFileDD();'></td>
      <tr><td>select a Processed File from History :</td><td><select id='fldFileUId' ><option value=''>Not Selected</option>";
	$selectedFileRow=false;
	foreach ($mfF as $key=>$f) {
		if ($f["uid"]==$postFLUID) {
			$selectedFileRow=$mfF[$key];
			$attr=" SELECTED ";
		} else $attr="";
		echo "<option value='{$f["uid"]}' {$attr} >".basename($f["file_name"])." - ".$f["processed_date"]."</option>";
	}
	echo "</select></td></tr>
      </table>
		  <input type='submit' class='submit' onclick='parent.change_iframe_content(\"".$_SERVER["PHP_SELF"]."?FLUID=\"+document.getElementById(\"fldFileUId\").value);' value='Submit'/>
		  </div><br>";

  /*
   * WARNING : This screen is only accessible to RT Staff Users because an EDI (raw) file can contain orders for other principals too within
   * 
   * This is shown AFTER the DD of files so that atleast the principal can see what files have been imported, just not their contents 
   */
  if ($staffUser!="Y") {
    echo "Sorry, you do not have permissions to view EDI Files!";
    return;
  }

	echo "<center>";

	// detail
	
	if ($postFLUID=="") return;
	
	if ($f===false) {
		echo "Invalid File Selected, or you do not have access to this file";
		return;
	}
	
	/*
	 * Show the processed docs
	 */
	
	echo "Download this file as HTML : <a href='#' onclick='window.open(\"".$ROOT.$PHPFOLDER."functional/general/downloadFile.php?TYPE=EDI&UID={$postFLUID}&HTMLOUTPUT=Y\",\"EDIDownloadFile\",\"scrollbars=yes,resizable=yes,width=800,height=400\");' >".
		 basename($selectedFileRow['file_name']).
		 "</a><br>";
		 
	echo "Download this file RAW : <a href='#' onclick='window.open(\"".$ROOT.$PHPFOLDER."functional/general/downloadFile.php?TYPE=EDI&UID={$postFLUID}&HTMLOUTPUT=N\",\"EDIDownloadFile\",\"scrollbars=yes,resizable=yes,width=800,height=400\");' >".
		 basename($selectedFileRow['file_name']).
		 "</a>";
	
	$transactionDAO = new TransactionDAO($dbConn);
	$docs=$transactionDAO->getOrdersHoldingByFileName($principalId, basename($selectedFileRow["file_name"]));
	
	echo "<style>
		th.ohe,td.ohe { font-size:12px; white-space:nowrap; }
		th.ohe { border:1px; border-style:solid; border-color:#DDDDDD; }
		.oheExceptionHeader td {border-top: 5px double #87CEFA;}
		.oheExceptionDetail{ background-color:#F7F8E0; }
		.oheExceptionDetail th{ background-color:#F2F5A9; }
		.oheExceptionDetail td, .oheExceptionDetail th {border:1px; border-style:solid; border-color:#F5DA81}
	  </style>";
	
	echo "<br><h3 style='color:#047;'><i>Orders as Loaded and Validated...</i></h3>";
	
	echo "<table class='ohe' cellspacing=0>
			  <tr class='".GUICommonUtils::styleEO($rowStyle)."'>
				<th class='ohe'>UId</th>
				<th class='ohe'>Vendor Source</th>
				<th class='ohe'>Created Date</th>
				<th class='ohe'>Capture Date</th>
				<th class='ohe'>Order Date</th>
				<th class='ohe'>Data Source</th>
				<th class='ohe'>Incoming Ref</th>
				<th class='ohe'>Client Document</th>
				<th class='ohe'>Reference</th>
				<th class='ohe'>General Reference 1</th>
				<th class='ohe'>General Reference 2</th>
				<th class='ohe'>Delivery Instructions</th>
				<th class='ohe'>Document Type</th>
				<th class='ohe'>RT Store</th>
				<th class='ohe'>Ship To GLN</th>
				<th class='ohe'>Vendor Store Name</th>
				<th class='ohe'>Debtors Store Identifier</th>
				<th class='ohe'>Sales Agent Store Identifier</th>
				<th class='ohe'>Store Lookup Ref</th>
				<th class='ohe'>Chain Lookup Ref</th>
				<th class='ohe'>Depot Lookup Ref</th>
				<th class='ohe'>Status</th>
			 </tr>";
	
		$break="";
		$rowStyle="even";
		
	foreach ($docs as $n => $r) {
	
		// header change
		if ($r["oh_uid"]!=$break) {
			GUICommonUtils::styleEO($rowStyle);
	
			// get the error status description(s)
			if ($r["oh_status"]==FLAG_ERRORTO_SUCCESS) $statusMsg="Successfully Processed";
			else if ($r["oh_status"]==FLAG_STATUS_DELETED) $statusMsg="Deleted by User";
			else if ($r["oh_status"]=="") $statusMsg="Not yet validated (Unprocessed)";
			else $statusMsg="Failed Validation";
			
			echo "<tr " , ($r["oh_status"]==FLAG_STATUS_DELETED)?("style='background:#F5A9A9'"):('class="odd oheExceptionHeader"') , ">
					<td class='ohe'>{$r["oh_uid"]}</td>
					<td class='ohe'>{$r["vendor_name"]}</td>
					<td class='ohe'>{$r["created_date"]}</td>
					<td class='ohe'>{$r["capture_date"]}</td>
					<td class='ohe'>{$r["order_date"]}</td>
					<td class='ohe'>{$r["data_source"]}</td>
					<td class='ohe'>{$r["incoming_ref"]}</td>
					<td class='ohe'>{$r["client_document_number"]}</td>
					<td class='ohe'>{$r["reference"]}</td>
					<td class='ohe'>{$r["general_reference_1"]}</td>
					<td class='ohe'>{$r["general_reference_2"]}</td>
					<td class='ohe'>{$r["delivery_instructions"]}</td>
					<td class='ohe'>{$r["document_type"]}</td>
					<td class='ohe'>{$r["deliver_name"]}</td>
					<td class='ohe'>{$r["ship_to_gln"]}</td>
					<td class='ohe'>{$r["ship_to_name"]}</td>
					<td class='ohe'>{$r["debtors_store_identifier"]}</td>
					<td class='ohe'>{$r["sales_agent_store_identifier"]}</td>
					<td class='ohe'>{$r["store_lookup_ref"]}</td>
					<td class='ohe'>{$r["chain_lookup_ref"]}</td>
					<td class='ohe'>{$r["depot_lookup_ref"]}</td>
					<td class='ohe'>{$statusMsg}</td>
				 </tr>
				 <tr class='tableReset oheExceptionDetail'>
					<th class='ohe'>Client Line No</th>
					<th class='ohe'>Client Page No</th>
					<th class='ohe'>RT Product</th>
					<th class='ohe'>Quantity</th>
					<th class='ohe'>List Price</th>
					<th class='ohe'>Discount Value</th>
					<th class='ohe'>Nett Price</th>
					<th class='ohe'>Ext Price</th>
					<th class='ohe'>Vat Rate</th>
					<th class='ohe'>VAT Amount</th>
					<th class='ohe'>Total Price</th>
					<th class='ohe'>Price Variance</th>
					<th class='ohe'>Vendor Product Name</th>
					<th class='ohe'>GTIN</th>
					<th class='ohe'>Product Code</th>
					<th class='ohe' colspan=8>Status</th>
				 </tr>";
	
			$break=$r["oh_uid"];
		} // end : header change
		
		// detail
		echo "<tr class='tableReset oheExceptionDetail' " , ($r["ohd_status"]==FLAG_STATUS_DELETED)?("style='background:#F5A9A9'"):('') , ">
				<td class='ohe'>{$r["client_line_no"]}</td>
				<td class='ohe'>{$r["client_page_no"]}</td>
				<td class='ohe'>{$r["product_description"]}</td>
				<td class='ohe'>{$r["quantity"]}</td>
				<td class='ohe'>{$r["list_price"]}</td>
				<td class='ohe'>{$r["discount_value"]}</td>
				<td class='ohe'>{$r["nett_price"]}</td>
				<td class='ohe'>{$r["ext_price"]}</td>
				<td class='ohe'>{$r["vat_rate"]}</td>
				<td class='ohe'>{$r["vat_amount"]}</td>
				<td class='ohe'>{$r["total_price"]}</td>
				<td class='ohe'>{$r["price_diff_notified"]}</td>
				<td class='ohe'>{$r["product_name"]}</td>
				<td class='ohe'>{$r["product_gtin"]}</td>
				<td class='ohe'>{$r["product_code"]}</td>";
	
		echo 	"<td class='ohe' colspan=8>".(($r["ohd_status"]==FLAG_STATUS_DELETED)?"Deleted by User":"")."</td>
			 </tr>";
	
	}
	echo "</table>
        </center>";
	
echo "</body>
      </html>";