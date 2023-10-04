<?php
	include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
	require($ROOT.$PHPFOLDER."functional/main/access_control.php");
	ob_start(); //Turn on output buffering
    include_once($ROOT.$PHPFOLDER."libs/GUICommonUtils.php");
    include_once($ROOT.$PHPFOLDER."elements/basicSelectElement.php");
    include_once($ROOT.$PHPFOLDER."DAO/StoreDAO.php");
    include_once($ROOT.$PHPFOLDER."DAO/MiscellaneousDAO.php");
    include_once($ROOT.$PHPFOLDER."DAO/PrincipalDAO.php");
    include_once($ROOT.$PHPFOLDER."DAO/AdministrationDAO.php");
    include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
    include_once($ROOT.$PHPFOLDER."elements/datePickerElement.php");


 if (!isset($_SESSION)) session_start() ;
 $userId = $_SESSION['user_id'] ;
 $principalId = $_SESSION['principal_id'] ;
 $depotId = $_SESSION['depot_id'] ;
 
 $rvlarray = array(346,363,336);
 $pcArray  = array(305);


$fldChosenGSRB="ChosenGlobalStore";  // fields
$divAjaxMainContentArea="ajaxMainContentArea";  // the ajax divs. refreshed independently

$dbConn = new dbConnect();  //Create new database object
$dbConn->dbConnection();  //Database connection method

$storeDAO = new StoreDAO($dbConn);
$principalDAO = new PrincipalDAO($dbConn);
$miscDAO = new MiscellaneousDAO($dbConn);
$adminDAO = new AdministrationDAO($dbConn);
$hasSURole=$adminDAO->hasRoleSuperUser($userId,$principalId);

$fldPref = $adminDAO->getAllFieldPreferences($principalId, $systemId, 'STORE');


//PRINCIPAL IS EPOD ENABLED
$pPrefArr = $principalDAO->getPrincipalPreferences($principalId);
$epodFlag = (isset($pPrefArr[0]['epod_enabled']) && $pPrefArr[0]['epod_enabled'] == 'Y') ? true : false;
$epodHasRole = $adminDAO->hasRole($userId,$principalId,ROLE_MODIFY_STORE_EPOD);


if (isset($_POST['DMLTYPE'])) $postDMLTYPE=mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST['DMLTYPE'])); else $postDMLTYPE="INSERT";

// the lookup value when coming from modify filter in modifyStore.php
if (isset($_POST['PRINCIPALSTOREUID'])) $postPRINCIPALSTOREUID=mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST['PRINCIPALSTOREUID'])); else $postPRINCIPALSTOREUID="";

// the lookup value when coming from global stores in storeForm
if (isset($_POST['LOOKUPUID'])) $postLOOKUPUID=mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST['LOOKUPUID'])); else $postLOOKUPUID="";

if ($postLOOKUPUID!="") {
	$mfGS = $storeDAO->getGlobalStoreItem($postLOOKUPUID);
	$postDELNAME = $mfGS[0]['deliver_name'];
	$postBILLNAME = $mfGS[0]['bill_name'];
	$postDELADDR1 = $mfGS[0]['deliver_add1'];
	$postDELADDR2 = $mfGS[0]['deliver_add2'];
	$postDELADDR3 = $mfGS[0]['deliver_add3'];
	$postBILLADDR1 = $mfGS[0]['bill_add1'];
	$postBILLADDR2 = $mfGS[0]['bill_add2'];
	$postBILLADDR3 = $mfGS[0]['bill_add3'];
	$postTELNO1 = '';
	$postTELNO2 = '';
	$postEMAILADD = '';
	$postEAN = $mfGS[0]['ean_code'];
	$postVATNO = $mfGS[0]['vat_number'];
	$postVATNO2 = "";
  $postAUTHVAT="N";
	$postBRANCHCODE = $mfGS[0]['branch_code'];
	($mfGS[0]['no_vat'])?$postNOVAT = "Y":$postNOVAT = "N";
	$postCHAIN = ""; // we don't use the global chain
	$postALTCHAIN = '';
	$postDEPOT = "";
	$postDELDAY = "";
	$postORDDAY = "";
	($mfGS[0]['on_hold'])?$postONHOLD = "Y":$postONHOLD = "N";
	$postOLDACCOUNT = $mfGS[0]['old_account'];
	$postLB = "";
	$postLCL = "";
	$postSTATUS = FLAG_STATUS_ACTIVE;
	$chainStatus = FLAG_STATUS_ACTIVE; // shouldnt be used in global
	$postOWNEDBY = '';
	$postAREA = '';
	$postDMUID = '';
  $postSALESREPID = 0;
  $postDISVAL = 0 ;
  $postWLINK  = '';
  if($epodFlag){
    $postEPODFLAG = 'N';
    $postEPODRSAID = '';
    $postEPODCELLNO = '';
  }
  $postRETAILER="";
  $postBACCOUNT="1";
  $postQRCODE="";
  $postEXPORTNUMBERENABLED="N";
  $postNOPRICES = "N";	
  $postAUTOMAILINVOICE= "N";

} else if ($postPRINCIPALSTOREUID!="") {	
	
	$mfGS = $storeDAO->getPrincipalStoreItem($postPRINCIPALSTOREUID);	
	$postDELNAME = $mfGS[0]['store_name'];
	$postBILLNAME = $mfGS[0]['bill_name'];
	$postDELADDR1 = $mfGS[0]['deliver_add1'];
	$postDELADDR2 = $mfGS[0]['deliver_add2'];
	$postDELADDR3 = $mfGS[0]['deliver_add3'];
	$postBILLADDR1 = $mfGS[0]['bill_add1'];
	$postBILLADDR2 = $mfGS[0]['bill_add2'];
	$postBILLADDR3 = $mfGS[0]['bill_add3'];
	$postTELNO1 = $mfGS[0]['tel_no1'];
	$postTELNO2 = $mfGS[0]['tel_no2'];
	$postEMAILADD = $mfGS[0]['email_add'];
	$postEAN = $mfGS[0]['ean_code'];
	$postVATNO = $mfGS[0]['vat_number'];
	$postVATNO2 = $mfGS[0]['vat_number_2'];
  $postAUTHVAT = ((trim($mfGS[0]['vat_excl_authorised_by'])!="")?"Y":"N");
	$postBRANCHCODE = $mfGS[0]['branch_code'];
	($mfGS[0]['no_vat'])?$postNOVAT = "Y":$postNOVAT = "N";
	$postCHAIN = $mfGS[0]['principal_chain_uid'];
	$postALTCHAIN = $mfGS[0]['alt_principal_chain_uid'];
	$postDEPOT = $mfGS[0]['depot_uid'];
	$postDELDAY = $mfGS[0]['delivery_day_uid'];
	$postORDDAY = $mfGS[0]['order_day_uid'];
	($mfGS[0]['on_hold'])?$postONHOLD = "Y":$postONHOLD = "N";
	$postOLDACCOUNT = $mfGS[0]['old_account'];
	$postLB = $mfGS[0]['ledger_balance'];
	$postLCL = $mfGS[0]['ledger_credit_limit'];
	$postSTATUS = $mfGS[0]['status'];
	$chainStatus = $mfGS[0]['chain_status'];
	$postOWNEDBY = $mfGS[0]['owned_by'];
	$postAREA = $mfGS[0]['area_uid'];
	$postDMUID = '';
  $postSALESREPID = $mfGS[0]['principal_sales_representative_uid'];

  if($epodFlag){
    $postEPODFLAG = $mfGS[0]['epod_store_flag'];
    $postEPODRSAID = $mfGS[0]['epod_rsa_id'];
    $postEPODCELLNO = $mfGS[0]['epod_cellphone_number'];
  }
  $postRETAILER=$mfGS[0]['retailer'];
  $postBACCOUNT=$mfGS[0]['bank_details_to_print'];
  $postQRCODE=$mfGS[0]['q_r_code_to_print'];
  $postEXPORTNUMBERENABLED=$mfGS[0]['export_number_enabled'];
  $postNOPRICES = $mfGS[0]['no_prices_on_invoice'];	
  $postAUTOMAILINVOICE=$mfGS[0]['auto_mail_invoice'];
  $postDISVAL=$mfGS[0]['off_invoice_discount'];
  $postWLINK=$mfGS[0]['warehouse_link'];
  $postLC=$mfGS[0]['LC'];

} else {
    // $postDELNAME = (isset($_POST['DELNAME'])) ? ($_POST['DELNAME']) : ('');
    $postDELNAME = (isset($_GET['deliver_name'])) ? ($_GET['deliver_name']) : ('');
    $postBILLNAME = (isset($_GET['bill_name'])) ? ($_GET['bill_name']) : ('');
    $postDELADDR1 = (isset($_GET['deliver_add1'])) ? ($_GET['deliver_add1']) : ('');
    $postDELADDR2 = (isset($_GET['deliver_add2'])) ? ($_GET['deliver_add2']) : ('');
    $postDELADDR3 = (isset($_GET['deliver_add3'])) ? ($_GET['deliver_add3']) : ('');
    $postBILLADDR1 = (isset($_GET['bill_add1'])) ? ($_GET['bill_add1']) : ('');
    $postBILLADDR2 = (isset($_GET['bill_add2'])) ? ($_GET['bill_add2']) : ('');
    $postBILLADDR3 = (isset($_GET['bill_add3'])) ? ($_GET['bill_add3']) : ('');
    $postTELNO1 = (isset($_GET['tel_no1'])) ? ($_GET['tel_no1']) : ('');
    $postTELNO2 = (isset($_GET['tel_no2'])) ? ($_GET['tel_no2']) : ('');
    $postEMAILADD = (isset($_GET['email_add'])) ? ($_GET['email_add']) : ('');
    $postEAN = (isset($_GET['ean_code'])) ? ($_GET['ean_code']) : ('');
    $postVATNO = (isset($_GET['vat_number'])) ? ($_GET['vat_number']) : ('');
    $postVATNO2 = (isset($_GET['vat_number_2'])) ? ($_GET['vat_number_2']) : ('');
    $postAUTHVAT = "N";
    $postBRANCHCODE = (isset($_GET['branch_code'])) ? ($_GET['branch_code']) : ('');
    $postNOVAT = "N";
    $postCHAIN = "";
    $postALTCHAIN = '';
    $postDEPOT = (isset($_GET['depot_uid'])) ? ($_GET['depot_uid']) : ('');
    $postDELDAY = "";
    $postORDDAY = "";
    $postONHOLD = "N";
    $postOLDACCOUNT = "";
    $postUSERPERMISSIONS = ""; // only on insert - list of users (sales agents for now) to give permissions to for this store
    $postLB = "";
    $postLCL = "";
    $postSTATUS = FLAG_STATUS_ACTIVE;
    $chainStatus = FLAG_STATUS_ACTIVE;
    $postOWNEDBY = '';
    $postAREA = '';
    $postSALESREPID = 0;
    $postEXPORTNUMBERENABLED = "N";
    $postAUTOMAILINVOICE = "N";
    $postDISVAL = (isset($_GET['off_invoice_discount'])) ? ($_GET['off_invoice_discount']) : ('');
    $postWLINK  = (isset($_GET['warehouse_link']))       ? ($_GET['warehouse_link']) : ('');    
    $postLC="Y";
    

    // fields not part of form
    $postPSMCLIENTUID = (isset($_GET['PSMCLIENTUID'])) ? ($_GET['PSMCLIENTUID']) : ('');
    $postDMUID = (isset($_GET['DMUID'])) ? ($_GET['DMUID']) : ('');

    if($epodFlag){
      $postEPODFLAG = 'N';
      $postEPODRSAID = '';
      $postEPODCELLNO = '';
    }

    $postRETAILER="";
    $postBACCOUNT="1";
    $postQRCODE="";

  }


$postAGSM = (isset($_POST['AGSM'])) ? ($_POST['AGSM']) : ('');



#--------------------------------------------------------------------------------------------------------------------------

echo "<BR>";
include_once($ROOT.$PHPFOLDER.'elements/basicInputElement.php');


DatePickerElement::getDatePickerLibs();
	    ?>
<script type="text/javascript" >
	    function selectedGlobalStore(val) {
	    	getContent("<?php echo $ROOT.$PHPFOLDER; ?>functional/stores/storeForm.php","LOOKUPUID="+val); // func is in generalAjaxBase.php
	    }

		function refreshSelectGlobalStores() {
			AjaxRefresh("RBNAME=<?php echo $fldChosenGSRB; ?>&CALLBACK=selectedGlobalStore(this.value);",
						"<?php echo $ROOT.$PHPFOLDER; ?>functional/administration/adminGlobalStoresListTable.php",
					    "<?php echo $divAjaxMainContentArea; ?>",
					    "Please wait whilst page is refreshed...",
					    "");
		}
		function processSalesAgents() {
			var dpt=document.getElementById('DEPOT').value;
			var ch=document.getElementById('CHAIN').value;
			saFlds=document.getElementsByName('FORM_USERPERMISSIONS');
			for (var i=0; i<saFlds.length; i++) {
				var dptListArr=saArrD[saFlds[i].value].split(',');
				var chListArr=saArrC[saFlds[i].value].split(',');
				if ((dptListArr.findIndex(dpt).toString()!='') && (chListArr.findIndex(ch).toString()!='')) {
					saFlds[i].disabled=false;
					saFlds[i].style.visibility='visible';
				} else {
					saFlds[i].disabled=true;
					saFlds[i].checked=false;
					saFlds[i].style.visibility='hidden';
				}
			}
		}
		</script>
<?php
	    echo "<div id='".$divAjaxMainContentArea."'>";

	    include_once($ROOT.$PHPFOLDER."DAO/StoreDAO.php");
	    if ($postDMLTYPE=="INSERT") {
	    	echo "<A href='javascript:refreshSelectGlobalStores();'><img src='".$DHTMLROOT.$PHPFOLDER."images/load-from-store-icon.png' style='border-style: none' /></A>";
	    }

		// create the JS for inteli lookups
		$storeDAO = new StoreDAO($dbConn);
		$storeList=$storeDAO->getAllPrincipalStoresUser($userId,$principalId);

		echo '<SCRIPT type="text/javascript" defer>';
		echo "var sArrDN=new Array();";
		if (sizeof($storeList)>10000) {
			echo "alert('WARNING: Size of StoreList returned for JS Lookups has exceeded allowable entries. Lookups have been disabled. Please inform Kwelanga Solutions Management.'); \n";
		} else {
				$i=0;$x="";
				foreach ($storeList as $row) {
					echo "sArrDN[".$i."]=\"".str_replace(array('"',"'",";","\\","\r\n","\n"),array('','','',''),$row['store_name'])."\";";
					$i++;
				}
		  }
		?>
		parent.hideMsgBoxSystemFeedbackAll();
		function suggest(fldName) {
			var list = new String();
			var matchCnt=0;
			var fullName;

			var fld=document.getElementsByName(fldName);
			var val=fld[0].value.toLowerCase();
			if (val.length==0) { hideSuggest(); return; }

			var pattern = new RegExp(val.replace(/[^a-zA-Z0-9]+/g,'')); // leave only alpha chars and digits
			for (i=0; ((i<sArrDN.length ) && (matchCnt<2000)); i++) {

    			switch (fldName) {
    			case "DELNAME": if(pattern.test(sArrDN[i].replace(/[^a-zA-Z0-9]+/g,'').toLowerCase())) {
    				list += "<tr><td class='tableReset standardFont' style='padding: 5px;'>"+sArrDN[i]+"</td></tr>";
    				matchCnt++;
    				if (matchCnt>(2000-1)) list += "<tr><td colspan=2 class='tableReset standardFont' style='padding: 5px;'><I><B>list incomplete...list exceeds 2000.</B></I></td></tr>";
    			}
    			fullName = 'Delivery Name';
    			break;
    			}
			}
			if (list.length > 0) {
				//user must use scroll wheel on mouse to scroll because otherwise onblur first to hide list
				list = "<div style='height: 250px;'><B>Principal-Stores with Similar "+fullName+":</B><BR><BR><table class='tableReset'>"+list+"</table></div>";

				var inputObj = jQuery('#'+fldName);
				var listBox = '<div id="suggestBoxID" style="position:absolute;top:'+(inputObj.offset().top + 22)+'px;left:'+inputObj.offset().left+'px;overflow:auto;padding:5px;overflow-x:hidden;border:1px solid black;background:lightSkyblue">'+list+'</div>';

				//check if id exists
				if(jQuery('#suggestBoxID').attr('id') == undefined){
					jQuery(listBox).appendTo('body');	//append div to body first time.
				} else {
					jQuery('#suggestBoxID').html(list);	//update list inside div.
				}
			}
			else hideSuggest();
		}

		function hideSuggest(){
			jQuery('#suggestBoxID').remove();	//remove store suggestion div.
		}
		function carryFields() {
			if ((document.getElementsByName('BILLNAME')[0].value=='') && (document.getElementsByName('BILLADDR1')[0].value=='')) {
				document.getElementsByName('BILLNAME')[0].value=document.getElementsByName('DELNAME')[0].value;
				document.getElementsByName('BILLADDR1')[0].value=document.getElementsByName('DELADDR1')[0].value;
				document.getElementsByName('BILLADDR2')[0].value=document.getElementsByName('DELADDR2')[0].value;
				document.getElementsByName('BILLADDR3')[0].value=document.getElementsByName('DELADDR3')[0].value;
			}
		}
		<?php
		echo "</SCRIPT>";
		if ($postDMLTYPE=="UPDATE") {
		    echo '<div align="center"><INPUT type="submit" class="submit" value="Back to Stores" onClick="backToStoreList()"></DIV><BR>';
			echo "<SPAN style='font-family:Verdana,Arial,Helvetica,sans-serif; font-weight:bold;font-size:0.8em;'>";
			echo "<A style='color:grey;' href=\"javascript:;\" onClick=\"window.open('".$ROOT.$PHPFOLDER."functional/stores/storeCard.php?PRINCIPALSTOREUID=".$postPRINCIPALSTOREUID."','myStore','scrollbars=yes,width=400,height=550');\">view store as popup</A>";
			echo " | <A style='color:grey;' href='javascript:;' onclick='emailStore();'>email store details to self</A>";
			echo "</SPAN>";
		}


echo "<table border='0' style='line-height:25px;'><thead><TR>";


      if ($postDMLTYPE=="INSERT") {
        echo '<th colspan="3"><b> Add a new store to the master list</b></th>';
      } else {
        echo '<th colspan="3"><b> Modify details of an existing Principal-Store : '.$postPRINCIPALSTOREUID.'</b></th>';
      }
      echo '</tr></thead><tr><td  valign="top" STYLE="PADDING:0PX;">';

      $class = 'odd';
        echo "<table border='0' class='tableReset' style='font-size:12px;'>";
		echo "<tr>";

        echo "<TR class='".GUICommonUtils::styleEO($class)."'>";
           // this line was put in because of wrong UIDs being passed back which 1 client had, but I could not duplicate.
          echo "<INPUT type='hidden' id='PSMUID' value='{$postPRINCIPALSTOREUID}' >";
          echo "<TD width='150'>Deliver to Name"; GUICommonUtils::requiredField(); echo "</TD><TD  width='310'><INPUT type='text' size='50' maxlength='60' id='DELNAME' name='DELNAME' value='"      . $postDELNAME  . "' onKeyUp='suggest(\"DELNAME\");' onblur='hideSuggest();'>&nbsp;&nbsp;<img src='".$DHTMLROOT.$PHPFOLDER."images/archive-icon-1.png' style='border-style: none;margin:0px;padding:0px;' /></TD>";
        echo "</TR>\n";

        echo "<TR class='".GUICommonUtils::styleEO($class)."'' >
          <TD>Deliver to Address 1</TD><TD><INPUT type='text' size='30' maxlength='30' id='DELADDR1' value='" . $postDELADDR1 . "' ></TD>";
        echo "</TR>\n";

        echo "<TR class='".GUICommonUtils::styleEO($class)."'>
          <TD>Deliver to Address 2</TD><TD><INPUT type='text' size='30' maxlength='30' id='DELADDR2' value='" . $postDELADDR2 . "'></TD>";
        echo "</TR>\n";

        echo "<TR class='".GUICommonUtils::styleEO($class)."'>
          <TD>Deliver to Address 3</TD><TD><INPUT type='text' size='30' maxlength='30' id='DELADDR3' value='" . $postDELADDR3 . "'></TD>";
        echo "</TR>\n";


        echo '<TR class="'.GUICommonUtils::styleEO($class).'">';
	      echo "<TD>Branch Code</TD><TD><INPUT type='text' size='6' maxlength='6' id='BRANCHCODE' value='" . $postBRANCHCODE. "'></TD>";
        echo '</TR>';

        echo '<TR class="'.GUICommonUtils::styleEO($class).'" '.GUICommonUtils::showHideField($fldPref,'EAN',$class,false).'>';
        echo "<TD>EAN</TD><TD><INPUT type='text' size='13' maxlength='13' id='EAN' value='" . $postEAN. "'></TD>";
        echo '</TR>';

        echo '<TR class="'.GUICommonUtils::styleEO($class).'" '.GUICommonUtils::showHideField($fldPref,'VATNO',$class,false).'>';
        echo "<TD>VAT No.</TD><TD><INPUT type='text' size='15' maxlength='15' id='VATNO' value='" . $postVATNO. "' ></TD>";
        echo '</TR>';

        echo '<TR class="'.GUICommonUtils::styleEO($class).'" '.GUICommonUtils::showHideField($fldPref,'VATNO2',$class,false).'>';
        echo "<TD>Co Registration</TD><TD><INPUT type='text' size='15' maxlength='15' id='VATNO2' value='" . $postVATNO2. "' ></TD>";
        echo '</TR>';

	    echo '<TR class="'.GUICommonUtils::styleEO($class).'" '.GUICommonUtils::showHideField($fldPref,'NOVAT',$class,false).'>';
	    // the label is actually NO VAT as this is the column name on db table and it is CRITICAL that developers modifying this screen are aware
	    // that the field is a negative setting and not affirmative !!! So instead of yes/no value labels, they are done as below to avoid confusion to customers
	    // It is too entrenched at moment to change the db column so we leave it as such.
          echo "<TD height='55'>Does this account<br>need to apply VAT ?</TD><TD>";
              BasicInputElement::getGeneralHorizontalRB('NOVAT',"VAT Exempt,VAT must be applied","Y,N",$postNOVAT,"N","N",null,"changeNOVAT();",null);
          $visible=(($postNOVAT!="N")?"block":"none");
          echo "<br><div id='divAuthVAT' style='display:{$visible}; line-height:15px;color:".COLOR_URGENT_TEXT."; font-size:10px'>I understand that VAT will be EXCLUDED
                whenever an order for this store is created :";
          echo "<input type='checkbox' id='AUTHVAT' name='AUTHVAT' value='Y' ".(($postAUTHVAT=="Y")?" CHECKED ":"").">
                </div>";
          echo "</TD>";

        echo '</TR>';

	echo '<TR class="'.GUICommonUtils::styleEO($class).'">';
          echo '<TD>'; 
          if(in_array($principalId, $rvlarray)) {echo 'Channel';} 
          elseif(in_array($principalId, $pcArray)) {echo 'Price Chain';} 
          else {echo 'Chain';} GUICommonUtils::requiredField(); echo '</TD>';
          echo '<TD>';
            BasicSelectElement::getUserPrincipalChainsDD("CHAIN",$postCHAIN,"N","N","processSalesAgents();",null,null,$dbConn,$userId,$principalId,CHAIN_FILTER_PRICE);
            if ($chainStatus!=FLAG_STATUS_ACTIVE) echo "<br><span style='color:".COLOR_UNOBTRUSIVE_INFO."; font-size:10px;'>Store is linked to a deleted chain which cannot<br>be displayed here, or is not supplied</span>";
          echo '</TD>';
        echo '</TR>';

        echo "<TR class='".GUICommonUtils::styleEO($class)."'>";
          echo "<TD>Depot"; GUICommonUtils::requiredField(); echo "</TD>";
          echo "<TD>";
          if(CommonUtils::isDepotUser()){
            BasicSelectElement::getDepotUserDepotDD("DEPOT",$postDEPOT,"N","N","processSalesAgents();",null,null,$dbConn,$userId,$depotId);
          } else {
            BasicSelectElement::getUserDepotsForPrincipalDD("DEPOT",$postDEPOT,"N","N","processSalesAgents();",null,null,$dbConn,$userId,$principalId);
          }
          echo "</TD>";
        echo "</TR>";

        $READONLY=(($hasSURole) || ($postDMLTYPE=="INSERT"))?"":" DISABLED READONLY ";

        echo "<TR class='".GUICommonUtils::styleEO($class)."' ".GUICommonUtils::showHideField($fldPref,'LB',$class,false).">";
          echo "<TD>Current Credit Balance</TD><TD><INPUT type='text' size='12' maxlength='12' id='LB' value='" . $postLB . "' {$READONLY}></TD>";
        echo "</TR>";

	    echo '<TR class="'.GUICommonUtils::styleEO($class).'">';
          echo "<TD>Status</TD><TD>"; BasicInputElement::getGeneralHorizontalRB('STATUS',"Active,Deleted",FLAG_STATUS_ACTIVE.",".FLAG_STATUS_DELETED,$postSTATUS,"N","N",null,null,null); echo "</TD>";

      echo "</TR>";


         // sales agents to give permissions to automatically
         $js="var saArrD=new Object();var saArrC=new Object();";
         if (($postDMLTYPE=="INSERT") && ($_SESSION["category"]!=FLAG_SALESAGENT_USER)) {
         	$mfSA = $adminDAO->getSalesAgentsForPrincipal($principalId);
         	if (sizeof($mfSA)>0) {
         		//if ($class=="odd") $class="even"; else $class="odd";
	         	echo "<TR class='".GUICommonUtils::styleEO($class)."'>";
		        echo "<TD>Allow these Sales Agents<BR>to use this Store:</TD>";
		        echo "<TD colspan=3>
							<FIELDSET style='padding:5px;'>";
								echo "<SPAN style='color:".COLOR_UNOBTRUSIVE_INFO."; font-size:9px'>Only agents who have permissions for chosen depot and chain can be selected</SPAN><BR>";
								foreach ($mfSA as $sa) {
								 $js.="saArrD[{$sa["user_uid"]}]='{$sa["depot_list"]}';saArrC[{$sa["user_uid"]}]='{$sa["chain_list"]}';";
								 echo "<INPUT type='checkbox' name='FORM_USERPERMISSIONS' value='{$sa["user_uid"]}' /> {$sa["full_name"]}, {$sa["organisation_name"]}<BR>";
								}
				echo "      </FIELDSET>
					  </TD>";
		        echo "</TR>";
		        $class="odd";
         	}
         }
        echo "<TR class='".GUICommonUtils::styleEO($class)."' ".GUICommonUtils::showHideField($fldPref,'OLDACCOUNT',$class,false).">";
          echo "<TD>Generic Lookup</TD><TD><INPUT type='text' size='20' maxlength='10' id='OLDACCOUNT' value='" . $postOLDACCOUNT . "' ", ($postDMLTYPE=="INSERT")?(''):('READONLY DISABLED') , "></TD>";
        echo "</TR>";

        echo '<TR class="'.GUICommonUtils::styleEO($class).'">';
	      echo "<TD>Export Number Enabled</TD><TD>"; BasicInputElement::getGeneralHorizontalRB('EXPORTNUMBERENABLED',"Yes,No","Y,N",$postEXPORTNUMBERENABLED,"N","N",null,null,null); echo "</TD></TR>\n";
        echo '</TR>';
        echo '<TR class="'.GUICommonUtils::styleEO($class).'">';
        echo "<TD>Off Invoice Discount %</TD><TD><INPUT type='text' size='6' maxlength='6'  id='DISVAL' value=" . $postDISVAL . "></TD>";
        echo '</TR>';
        echo '<TR class="'.GUICommonUtils::styleEO($class).'">';
        echo "<TD>Warehouse Link Code</TD><TD><INPUT type='text' size='15' maxlength='15'  id='WLINK' value=" . $postWLINK . "></TD>";
        echo '</TR>';
        echo '<TR class="'.GUICommonUtils::styleEO($class).'">';
	      echo "<TD>Auto Mail Invoices</TD><TD>"; BasicInputElement::getGeneralHorizontalRB('AUTOMAILINVOICE',"Yes,No","Y,N",$postAUTOMAILINVOICE,"N","N",null,null,null); echo "</TD></TR>\n";
        echo '</TR>';
/*    if($epodFlag && $epodHasRole){
      echo '<TR class="'.GUICommonUtils::styleEO($class).'">';
        echo '<TD style="color:#DF3A01"><strong>Enable EPOD</strong></TD><TD>';
        BasicInputElement::getGeneralHorizontalRB('EPODFLAG',"Yes,No","Y,N",$postEPODFLAG,"N","N",'epodDisplay(this.value)',null,null);
        echo '</TD>';
      echo "</TR>\n";
      echo '<TR class="'.GUICommonUtils::styleEO($class).' epodtr" '.(($postEPODFLAG=='N')?('style="display: none;"'):('')).'>
              <TD style="color:#DF3A01">EPOD RSA ID No.</TD><TD><input type="TEXT" size="13" maxlength="13" id="EPODRSAID" value="'.$postEPODRSAID.'" ></TD>';
      echo "</TR>\n";
      echo '<TR class="'.GUICommonUtils::styleEO($class).' epodtr" '.(($postEPODFLAG=='N')?('style="display: none;"'):('')).'>';
        echo '<TD style="color:#DF3A01">EPOD Cellphone No.</TD><TD><input type="text" size="10" maxlength="10" id="EPODCELLNO" value="'.$postEPODCELLNO.'" ></TD>';
      echo "</TR>\n";
    }
*/
    echo "</TABLE>\n";

  echo '</td><td valign="top" STYLE="PADDING:0PX;">';

  $class = 'odd';
   echo "<table border='0' class='tableReset' style='font-size:12px;'>";
    echo "<TR class='".GUICommonUtils::styleEO($class)."' ".GUICommonUtils::showHideField($fldPref,'INVOICE',$class,false)."> ";
      echo "<TD width='150'>Invoice to Name</TD><TD width='310'><INPUT type='text' size='50' maxlength='60' id='BILLNAME' value='"       . $postBILLNAME . "' onblur='carryFields();'></TD>";
    echo "</TR>\n";
    echo "<TR class='".GUICommonUtils::styleEO($class)."' ".GUICommonUtils::showHideField($fldPref,'INVOICE',$class,false)."> ";
      echo "<TD>Invoice to Address 1</TD><TD><INPUT type='text' size='30' maxlength='30' id='BILLADDR1' value='" . $postBILLADDR1. "'></TD></TR>\n";
    echo "</TR>\n";
    echo "<TR class='".GUICommonUtils::styleEO($class)."' ".GUICommonUtils::showHideField($fldPref,'INVOICE',$class,false)."> ";
      echo "<TD>Invoice to Address 2</TD><TD><INPUT type='text' size='30' maxlength='30' id='BILLADDR2' value='" . $postBILLADDR2. "'></TD></TR>\n";
    echo "</TR>\n";
    echo "<TR class='".GUICommonUtils::styleEO($class)."' ".GUICommonUtils::showHideField($fldPref,'INVOICE',$class,false)."> ";
      echo "<TD>Invoice to Address 3</TD><TD><INPUT type='text' size='30' maxlength='30' id='BILLADDR3' value='" . $postBILLADDR3. "'></TD>";
    echo "</TR>\n";
    echo '<TR class="'.GUICommonUtils::styleEO($class).'">';
      echo "<TD>Contact No 1</TD><TD><INPUT type='text' size='15' maxlength='20' id='TELNO1' value='" . $postTELNO1 . "'></TD>";
    echo "</TR>\n";
    echo '<TR class="'.GUICommonUtils::styleEO($class).'">';
      echo "<TD>Contact No 2</TD><TD><INPUT type='text' size='15' maxlength='20' id='TELNO2' value='" . $postTELNO2 . "'></TD>";
    echo "</TR>\n";
    echo '<TR class="'.GUICommonUtils::styleEO($class).'">';
      echo "<TD>Email Address</TD><TD><INPUT type='text' size='20' maxlength='50' id='EMAILADD' value='" . $postEMAILADD . "'></TD>";
    echo "</TR>\n";

    // STORE OWNED BY: START
    $venPrin = array('uid'=>NULL,'name'=>'PRINCIPAL','vendor_gln'=>NULL);  //Build DD Array
    $venArr = $miscDAO->getVendersArray();
    array_unshift($venArr, $venPrin);

    //Display DD
    echo '<TR class="'.GUICommonUtils::styleEO($class).'" '.GUICommonUtils::showHideField($fldPref,'OWNEDBY',$class,false).' >';
      echo '<TD>Owned By:</TD>';
      echo '<TD>';
      echo '<select id="OWNEDBY" name="OWNEDBY" >';
      foreach($venArr as $ownopt){
        echo '<option value="',$ownopt['uid'],'" ',($postOWNEDBY==$ownopt['uid'])?('SELECTED'):(''),'>',$ownopt['name'],'</option>';
      }
      echo '</select>';
      echo '</TD>';
    echo "</TR>\n";
    // STORE OWNED BY: END

    echo '<TR class="'.GUICommonUtils::styleEO($class).'" '.GUICommonUtils::showHideField($fldPref,'ALTCHAIN',$class,false).' >';
      echo '<TD>'; if(in_array($principalId, $rvlarray)) {echo 'Retailer';} 
                  elseif(in_array($principalId, $pcArray)) {echo 'Debtor Chain';} 
                  else {echo 'Alternate Chain';} echo '</TD>';
      echo '<TD>';
        BasicSelectElement::getUserPrincipalChainsDD("ALTCHAIN",$postALTCHAIN,"N","N","processSalesAgents();",null,null,$dbConn,$userId,$principalId,CHAIN_FILTER_DEBTOR);
      echo '</TD>';
    echo "</TR>\n";
    echo '<TR class="'.GUICommonUtils::styleEO($class).'">';
      echo "<TD width='150'>Delivery Day</TD><TD width='310'>"; BasicSelectElement::getDaysDD("DELDAY",$postDELDAY,"N","N",null,null,null,$dbConn); echo "</TD>";
    echo "</TR>\n";
    echo '<TR class="'.GUICommonUtils::styleEO($class).'">';
      echo "<TD width='150'>Order Day</TD><TD width='310'>"; BasicSelectElement::getDaysDD("ORDDAY",$postORDDAY,"N","N",null,null,null,$dbConn); echo "</TD>";
    echo "</TR>\n";
    echo '<TR class="'.GUICommonUtils::styleEO($class).'" '.GUICommonUtils::showHideField($fldPref,'LCL',$class,false).'>';
      echo "<TD>Credit Limit</TD><TD><INPUT type='text' size='12' maxlength='12' id='LCL' value='" . $postLCL. "' {$READONLY}></TD>";
    echo "</TR>\n";
    echo '<TR class="'.GUICommonUtils::styleEO($class).'" '.GUICommonUtils::showHideField($fldPref,'ONHOLD',$class,false).'>';
      echo "<TD>On Hold</TD><TD>"; BasicInputElement::getGeneralHorizontalRB('ONHOLD',"Yes,No","Y,N",$postONHOLD,"N","N",null,null,null); echo "</TD></TR>\n";
    echo "</TR>\n";
    echo '<TR class="'.GUICommonUtils::styleEO($class).'" '.GUICommonUtils::showHideField($fldPref,'LC',$class,false).'>';
      echo "<TD>Local/Country</TD><TD>"; BasicInputElement::getGeneralHorizontalRB('LC',"Local,Country","Y,N",$postLC,"N","N",null,null,null); echo "</TD></TR>\n";
    echo "</TR>\n";
    echo '<TR class="'.GUICommonUtils::styleEO($class).'" '.GUICommonUtils::showHideField($fldPref,'NOPRICES',$class,false).'>';
      echo "<TD>No Prices On Invoice</TD><TD>"; BasicInputElement::getGeneralHorizontalRB('NOPRICES',"Yes,No","Y,N",$postNOPRICES,"N","N",null,null,null); echo "</TD></TR>\n";
    echo "</TR>\n";
    echo '<TR class="'.GUICommonUtils::styleEO($class).'">';
      echo "<TD>Area:</TD><TD>";
      BasicSelectElement::getPrincipalAreas("AREA",$postAREA,"N","N","",null,null,$dbConn,$userId,$principalId);
      echo "</TD>";
    echo "</TR>\n";
    echo '<TR class="'.GUICommonUtils::styleEO($class).'">';
      echo '<TD>Sales Rep</TD>';
      echo '<TD>';
        BasicSelectElement::getPrincipalSalesRepDD("SALESREPID",$postSALESREPID,"N","N","",null,null,$dbConn,$userId,$principalId);
      echo '</TD>';
    echo '</TR>';
     echo '<TR class="'.GUICommonUtils::styleEO($class).'">';
      echo "<TD>Retailer/Group:</TD><TD style='white-space:nowrap;' nowrap><div style='white-space:nowrap;width:200px;' nowrap>";
      BasicInputElement::getCSS3RadioHorizontal('RETAILER',"Not Specified,PnP,Checkers",",".RETAILER_PNP.",".RETAILER_CHECKERS,$postRETAILER,false);
      echo "</div></TD>";
     echo '<TR class="'.GUICommonUtils::styleEO($class).'">';
      echo "<TD>Print Banking Details:</TD><TD style='white-space:nowrap;' nowrap><div style='white-space:nowrap;width:200px;' nowrap>";
      BasicInputElement::getCSS3RadioHorizontal('BACCOUNT',"Main,Alternate","1,2",$postBACCOUNT,false);
      echo "</div></TD>";
     echo '<TR class="'.GUICommonUtils::styleEO($class).'">';
      echo "<TD>Print QR Code</TD><TD><INPUT type='text' size='30' maxlength='50' id='QRCODE' value='" . $postQRCODE . "'></TD>";
     echo "</TR>\n";
      
    echo "</TR>\n";

   echo '</tr></table>';

  echo '</TD></TR></TABLE>';



    if ($postPRINCIPALSTOREUID==""){
      $smpf=$miscDAO->getPrincipalSpecialFields($principalId,CT_STORE_SHORTCODE);
    } else {
      $smpf=$miscDAO->getPrincipalSpecialFieldValues($principalId,$postPRINCIPALSTOREUID,CT_STORE_SHORTCODE);
    }

    if(count($smpf)>0){
      echo "<BR><table style='line-height:25px;font-size:12px;' border='0'>";
    }
         $i = 0;
         $l = 0;

         foreach ($smpf as $line) {
                $i++;
                $l++;

                if ($i == 1) {
                    $field1   = $line["name"];
                    $value1   = str_replace(' ','',$line["name"]) ;
                    $editable1 = $line["editable"];
                    $required1 = ($line['required']=='Y') ? true : false;
                    $maxLength1 = ($line['value_max_length'] >= 1) ? ($line['value_max_length']) : (100);  //30 is the default max length.
                    if ($postPRINCIPALSTOREUID=="") $postVal1=""; else $postVal1=$line['value'];
                    $inputDate1 = ($line['value_validation'] == 'DATE') ? true : false;
file_put_contents('C:/inetpub/wwwroot/systems/kwelanga_system/kwelanga_php/log/sql.txt', print_r($line, TRUE), FILE_APPEND);

                }

                if ($i == 2) {
                      $field2   = $line["name"];
                      $value2   = str_replace(' ','',$line["name"]);
                      $editable2 = $line["editable"];
                      $required2 = ($line['required']=='Y') ? true : false;
                      $maxLength2 = ($line['value_max_length'] >= 1) ? ($line['value_max_length']) : (100);  //30 is the default max length.
                      if ($postPRINCIPALSTOREUID=="") $postVal2=""; else $postVal2=$line['value'];
                      $inputDate2 = ($line['value_validation'] == 'DATE') ? true : false;

                  	  echo "<TR class='".GUICommonUtils::styleEO($class)."'><TD width='150'>". $field1 . " " , ($required1)?(GUICommonUtils::requiredField()):('') , "</TD><TD width='310' style='border-right:1px solid lightSkyBlue;'>";
                  	  if($inputDate1){
                  	    DatePickerElement::getDatePicker($value1,$postVal1,(($editable1=='N' && $postDMLTYPE!="INSERT")?true:false));
                  	  } else {
                  	    echo "<INPUT type='text' size='20' maxlength='".$maxLength1."' id='" . $value1 . "' value='" .$postVal1. "' ".(($editable1=='N' && $postDMLTYPE!="INSERT")?('DISABLED'):('')).">";
                  	  }
                  	  echo "</TD>";
                      echo "<TD width='150'>". $field2 . " " , ($required2)?(GUICommonUtils::requiredField()):('') , "</TD><TD width='310' style='border-right:1px solid lightSkyBlue;'>";
                      if($inputDate2){
                        DatePickerElement::getDatePicker($value2,$postVal2,(($editable2=='N' && $postDMLTYPE!="INSERT")?true:false));
                      } else {
                        echo "<INPUT type='text' size='20' maxlength='".$maxLength2."' id='" . $value2 . "' value='" .$postVal2. "' ".(($editable2=='N' && $postDMLTYPE!="INSERT")?('DISABLED'):('')).">";
                      }
                      echo "</TD></TR>\n";

                      $i = 0;
                }
         }

         if ($l == 1 || $l == 3 || $l == 5 || $l == 7 || $l == 9 ) {
              echo "<TR class='".GUICommonUtils::styleEO($class)."'><TD width='150'>" . $field1 . " " , ($required1)?(GUICommonUtils::requiredField()):('') , "</TD><TD width='310' style='border-right:1px solid lightSkyBlue;'>";
              if($inputDate1){
                DatePickerElement::getDatePicker($value1,$postVal1,(($editable1=='N' && $postDMLTYPE!="INSERT")?true:false));
              } else {
                echo "<INPUT type='text' size='20' maxlength='".$maxLength1."' id='" . $value1 . "' value='" .$postVal1. "' ",($editable1=='N' && $postDMLTYPE!="INSERT")?('DISABLED'):(''),">";
              }
              echo "</TD><TD width='150'>&nbsp;</TD><TD width='310'>&nbsp;</TD></TR>\n";
	          $i = 0;
         }

  echo '</TD></TR></TABLE>';


   if ($postDMLTYPE=="INSERT") {
    echo "<BR><div> <input type= 'checkbox' name= 'AGSM' value='Y' > Add Store to Global Master</div>";
   }
   echo "<BR><INPUT type='submit' class='submit' value='Submit Store' onclick='submitContentForm(\"".$postDMLTYPE."\");' >";

         echo "</div><BR><BR>";  // main content area

         echo "</BODY></HTML>";
#--------------------------------------------------------------------------------------------------------------------------

$dbConn->dbClose();

?>

<script type='text/javascript' defer>
<?php echo $js; ?>
var alreadySubmitted=false;
function successfulSubmit(p_type) {
	if(p_type=="INSERT") {

		if(typeof sArrDN !== 'undefined') sArrDN.push(document.getElementById("DELNAME").value); // add the just created store to the lookup list
		document.getElementById("DELNAME").value="";
		document.getElementById("BILLNAME").value="";
		document.getElementById("DELADDR1").value="";
		document.getElementById("DELADDR2").value="";
		document.getElementById("DELADDR3").value="";
		document.getElementById("BILLADDR1").value="";
		document.getElementById("BILLADDR2").value="";
		document.getElementById("BILLADDR3").value="";
		document.getElementById("TELNO1").value="";
		document.getElementById("TELNO2").value="";
		document.getElementById("EMAILADD").value="";
		document.getElementById("EAN").value="";
		document.getElementById("VATNO").value="";
		document.getElementById("VATNO2").value="";
		document.getElementById("BRANCHCODE").value="";
		document.getElementsByName("NOVAT")[0].checked=false; document.getElementsByName("NOVAT")[1].checked=true;
                changeNOVAT(); // reset the Auth Fields
		document.getElementById("CHAIN").value="";
		document.getElementById("ALTCHAIN").value="";
		document.getElementById("DEPOT").value="";
		document.getElementById("DELDAY").value=8;
		document.getElementById("ORDDAY").value=8;
		document.getElementById("LB").value="";
		document.getElementById("LCL").value="";
		document.getElementById("OLDACCOUNT").value="";
		document.getElementsByName("STATUS")[0].checked=true;
                document.getElementById("SALESREPID").value=0;
		document.getElementsByName("ONHOLD")[1].checked=true;
		document.getElementsByName("NOPRICES")[1].checked=true;		
		document.getElementById("DISVAL").value="";
		document.getElementById("WLINK").value="";
		document.getElementsByName("LC")[1].checked=true;

    css3_reset_by_val_RETAILER("");

		document.getElementById("AREA").value="";

                <?php if($epodFlag && $epodHasRole){ ?>
                  document.getElementsByName("EPODFLAG")[1].checked=true;
                  document.getElementById("EPODCELLNO").value="";
                  document.getElementById("EPODRSAID").value="";
                  epodDisplay('N'); //hide epod fields.
                <?php } ?>

	}
}
function submitContentForm(p_type) {
	if (alreadySubmitted) {
		return;
	}
	alreadySubmitted=true;
	var params='DMLTYPE='+p_type;

	params+='&DELNAME='+encodeURIComponent(document.getElementById("DELNAME").value);
	params+='&BILLNAME='+encodeURIComponent(document.getElementById("BILLNAME").value);
	params+='&DELADDR1='+encodeURIComponent(document.getElementById("DELADDR1").value);
	params+='&DELADDR2='+encodeURIComponent(document.getElementById("DELADDR2").value);
	params+='&DELADDR3='+encodeURIComponent(document.getElementById("DELADDR3").value);
	params+='&BILLADDR1='+encodeURIComponent(document.getElementById("BILLADDR1").value);
	params+='&BILLADDR2='+encodeURIComponent(document.getElementById("BILLADDR2").value);
	params+='&BILLADDR3='+encodeURIComponent(document.getElementById("BILLADDR3").value);
	params+='&EAN='+document.getElementById("EAN").value;
	params+='&VATNO='+document.getElementById("VATNO").value;
	params+='&VATNO2='+document.getElementById("VATNO2").value;
	params+='&TELNO1='+document.getElementById("TELNO1").value;
	params+='&TELNO2='+document.getElementById("TELNO2").value;
	params+='&EMAILADD='+document.getElementById("EMAILADD").value;
	params+='&BRANCHCODE='+document.getElementById("BRANCHCODE").value;
	params+='&NOVAT='+convertElementToArray(document.getElementsByName("NOVAT"));
  params+='&AUTHVAT='+convertElementToArray(document.getElementsByName("AUTHVAT"));
  params+='&SALESREPID='+convertElementToArray(document.getElementsByName("SALESREPID"));
	params+='&CHAIN='+document.getElementById("CHAIN").value;
	params+='&ALTCHAIN='+document.getElementById("ALTCHAIN").value;
	params+='&DEPOT='+document.getElementById("DEPOT").value;
	params+='&DELDAY='+document.getElementById("DELDAY").value;
	params+='&ORDDAY='+document.getElementById("ORDDAY").value;
	params+='&ONHOLD='+convertElementToArray(document.getElementsByName("ONHOLD"));
	params+='&OWNEDBY='+document.getElementById("OWNEDBY").value;
	<?php if($postDMLTYPE=="INSERT"){ ?>
		params+='&OLDACCOUNT=' + document.getElementById("OLDACCOUNT").value;
	<?php } else { ?>
		params+='&OLDACCOUNT=<?php echo $postOLDACCOUNT; ?>';
	<?php } ?>
	params+='&AGSM='+convertElementToArray(document.getElementsByName("AGSM"));
	params+='&LB='+document.getElementById("LB").value;
	params+='&LCL='+document.getElementById("LCL").value;
	params+='&STATUS='+convertElementToArray(document.getElementsByName("STATUS"));
	params+='&AREA='+document.getElementById("AREA").value;
  params+='&RETAILER='+convertElementToArray(document.getElementsByName("RETAILER"));
  params+='&BACCOUNT='+convertElementToArray(document.getElementsByName("BACCOUNT"));
  params+='&QRCODE='+document.getElementById("QRCODE").value;
  params+='&EXPORTNUMBERENABLED='+convertElementToArray(document.getElementsByName("EXPORTNUMBERENABLED"));
  params+='&NOPRICES='+convertElementToArray(document.getElementsByName("NOPRICES"));	
  params+='&DISVAL='+document.getElementById("DISVAL").value;
  params+='&WLINK='+document.getElementById("WLINK").value;
  params+='&LC='+convertElementToArray(document.getElementsByName("LC"));
  params+='&AUTOMAILINVOICE='+convertElementToArray(document.getElementsByName("AUTOMAILINVOICE"));  
	<?php if ($_SESSION["category"]!=FLAG_SALESAGENT_USER) { ?>
	if (p_type=='INSERT') {
		params+='&USERPERMISSIONS='+convertElementToArray(document.getElementsByName("FORM_USERPERMISSIONS"));
	}
	<?php } ?>


        <?php if($epodFlag && $epodHasRole){ ?>
          params+='&EPODFLAG='+convertElementToArray(document.getElementsByName("EPODFLAG"));
          params+='&EPODCELLNO='+document.getElementById("EPODCELLNO").value;
          params+='&EPODRSAID='+document.getElementById("EPODRSAID").value;
        <?php } else if($epodFlag){ ?>
          params+='&EPODFLAG=<?php echo $postEPODFLAG ?>';
          params+='&EPODCELLNO=<?php echo $postEPODCELLNO ?>';
          params+='&EPODRSAID=<?php echo $postEPODRSAID ?>';
        <?php } ?>

	<?php
			// user has come from viewTracking.php as a depot user and is linking a store.
			if (isset($_GET["SCRSOURCE"]) && ($_GET["SCRSOURCE"]=="DEPOTTT")) {
				echo "params+='&PSMCLIENTUID={$postPSMCLIENTUID}';";
			}
	?>

	if ((p_type=="UPDATE") && (document.getElementById("PSMUID").value!='<?php echo $postPRINCIPALSTOREUID; ?>')) {
		alert('CRITICAL ERROR: PSM UID passed does not correspond to the displayed store!');
		return;
	}
	params+='&PRINCIPALSTOREUID=<?php echo $postPRINCIPALSTOREUID; ?>';

	// special fields
	<?php
	         	
	
         foreach ($smpf as $line) {
         	
                      $value1   = str_replace(' ','',$line["name"]) ;
                      echo "params+='&".$value1."='+encodeURIComponent(document.getElementById('".$value1."').value);";
         }

	?>
	params=params.replace(/'/g,'').replace(/"/g,''); // get rid of quotes which can upset the display element
	AjaxRefreshWithResult(params,
						  '<?php echo $ROOT.$PHPFOLDER ?>functional/stores/storeSubmit.php',
						  'alreadySubmitted=false; if (msgClass.type=="S") { ttPassback("<?php echo $postDMUID; ?>",msgClass); successfulSubmit("'+p_type+'"); }',
						  'Please wait while request is processed...');
}
// if coming from the trackingTransaction screen as a Depot user
function ttPassback(dmUId,msgClass) {
	<?php
			// post JS to be executed if this store was added from the viewTracking.php depot user when linking a store.
			// Dont pass it, it proves to complex
			if (isset($_GET["SCRSOURCE"]) && ($_GET["SCRSOURCE"]=="DEPOTTT")) {
	?>
  try {
	  eval(msgClass.identifier);
	} catch (e) {
	  alert('identifier could not be eval() in ttPassback! Please contact RT.');
	  return false;
	}

  if (window.opener.document.getElementById('delloc'+dmUId)) {
    window.opener.document.getElementById('delloc'+dmUId).innerHTML=msgClassIdentifier.delloc;
  }
  if (window.opener.document.getElementById('delarea'+dmUId)) {
    window.opener.document.getElementById('delarea'+dmUId).innerHTML=msgClassIdentifier.delarea;
  }
  if (window.opener.document.getElementById('delday'+dmUId)) {
    window.opener.document.getElementById('delday'+dmUId).innerHTML=msgClassIdentifier.delday;
  }
  if (window.opener.document.getElementById('ordday'+dmUId)) {
    window.opener.document.getElementById('ordday'+dmUId).innerHTML=msgClassIdentifier.ordday;
  }
  <?php
		// post JS to be executed if this store was added from the viewTracking.php depot user when linking a store.
		// Dont pass it, it proves to complex
		echo "window.opener.hideDepotManageScreen();"; // close the manage Screen in parent screen
		echo "$('#UInBoxLyrTrans,#UInBoxClose').bind('click', function() {self.close();});"; // attach the close window event whe success popup is closed

		}
  ?>

}

function emailStore() {
	var params="USERID=<?php echo $userId; ?>&OBJECTID=<?php echo EO_STORE_CARD; ?>&SUBJECT=Store Details as per Request: "+escape(document.getElementById('DELNAME').value)+"&PRINCIPALSTOREUID=<?php echo $postPRINCIPALSTOREUID ?>";
	AjaxRefreshWithResult(params,
						  '<?php echo $ROOT.$PHPFOLDER ?>functional/administration/functions/emailUserHTML.php',
						  'alreadySubmitted=false;',
						  'Please wait while request is processed...');
}

function epodDisplay(val){
  if(val == 'N'){
    $('.epodtr').hide();
  } else {
    $('.epodtr').show();
  }
}

function changeNOVAT() {
  if (convertElementToArray(document.getElementsByName("NOVAT"))=='Y') {
    $('#divAuthVAT').css('display','block');
    $('#AUTHVAT')[0].checked=false;
  } else {
    $('#divAuthVAT').css('display','none');
  }
}
</script>
<?php

// direct navs to this screen as a popup need the objects from home.php
if (isset($_GET["SCRSOURCE"]) && ($_GET["SCRSOURCE"]=="DEPOTTT")) {
	include_once($ROOT.$PHPFOLDER.'elements/Messages.php');
	Messages::msgboxModalLayer();
	Messages::msgboxSubModalLayer();
	Messages::msgBoxSystemFeedback();
	Messages::msgBoxError();
	Messages::msgBoxInfo();
	Messages::msgBoxInput();
	Messages::msgBoxContent();
	Messages::tipBox();
}

// content was getting beyond 0.5MB so this is necessary
$htmlBody = ob_get_clean();

// if adding as popup from Depot TT then add the Style sheet and JS explicitly
if (isset($_GET["SCRSOURCE"]) && ($_GET["SCRSOURCE"]=="DEPOTTT")) {
  $htmlBody="
						<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Frameset//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd\">
						<html xmlns=\"http://www.w3.org/1999/xhtml\"  xmlns:sdk=\"\">
						<head>
								<script type=\"text/javascript\" language=\"javascript\" src=\"{$DHTMLROOT}{$PHPFOLDER}js/jquery.js\"></script>
								<script type=\"text/javascript\" language=\"javascript\" src=\"{$DHTMLROOT}{$PHPFOLDER}js/dops_global_functions.js\"></script>
								<link href=\"{$DHTMLROOT}{$PHPFOLDER}css/default.css\" rel=\"stylesheet\" type=\"text/css\">
								<link href=\"{$DHTMLROOT}{$PHPFOLDER}css/uipopup_min.css\" rel=\"stylesheet\" type=\"text/css\">

						 </head>
						 <body>".
						 $htmlBody."
						 </body>
						 </html>";
}
/*
$htmlBody = gzencode($htmlBody, 9, FORCE_GZIP);
header ("Content-Encoding: gzip");
header ('Content-Length: '.strlen($htmlBody));
*/
echo $htmlBody;

?>