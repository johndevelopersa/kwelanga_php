<?php
	include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
	require($ROOT.$PHPFOLDER."functional/main/access_control.php");
	include_once($ROOT.$PHPFOLDER."elements/basicSelectElement.php");
	include_once($ROOT.$PHPFOLDER.'elements/basicInputElement.php');
	include_once($ROOT.$PHPFOLDER."libs/GUICommonUtils.php");
    include_once($ROOT.$PHPFOLDER."DAO/MiscellaneousDAO.php");  //Custom Fields

if (!isset($_SESSION)) session_start();
$userId = $_SESSION['user_id'];
$principalId = $_SESSION['principal_id'];
$principalName = $_SESSION['principal_name'];

// fields
$fldChosenGCRB = 'ChosenGlobalChain';

// the ajax divs. refreshed independently
$divAjaxMainContentArea="ajaxMainContentArea";

//Create new database object
$dbConn = new dbConnect();

//Database connection method
$dbConn->dbConnection();

$postDMLTYPE = (isset($_POST['DMLTYPE'])) ? (htmlspecialchars($_POST['DMLTYPE'])) : ("INSERT");
// this is the value when coming from modifyChain
$postPRINCIPALCHAINUID = (isset($_POST['PRINCIPALCHAINUID'])) ? (htmlspecialchars($_POST['PRINCIPALCHAINUID'])) : ("");
// GCUID is used to look up a value when coming from "lookup global chain"
$postLOOKUPUID = (isset($_POST['LOOKUPUID'])) ? (htmlspecialchars($_POST['LOOKUPUID'])) : ("");

if ($postLOOKUPUID!="") {
	include_once($ROOT.$PHPFOLDER."DAO/StoreDAO.php");
	$storeDAO = new StoreDAO($dbConn);
	$mfGC = $storeDAO->getGlobalChainItem($postLOOKUPUID);
	$postCHAINNAME = $mfGC[0]['description'];
	$postSTATUS = $mfGC[0]['status'];
	$postPRINCIPALID = $principalId;
	$postOLDCODE = "";
} else if ($postPRINCIPALCHAINUID!="") {
	include_once($ROOT.$PHPFOLDER."DAO/StoreDAO.php");
	$storeDAO = new StoreDAO($dbConn);
	$mfPC = $storeDAO->getPrincipalChainItem($postPRINCIPALCHAINUID);
	$postCHAINNAME = $mfPC[0]['chain_name'];
	$postSTATUS = $mfPC[0]['status'];
	$postPRINCIPALID = $principalId;
	$postOLDCODE = $mfPC[0]['old_code'];
  } else {
    if (isset($_POST['OLDCODE'])) $postOLDCODE = $_POST['OLDCODE']; else $postOLDCODE="";
  	if (isset($_POST['CHAINNAME'])) $postCHAINNAME = $_POST['CHAINNAME']; else $postCHAINNAME="";
  	if (isset($_POST['PRINCIPAL'])) $postCHAINNAME = $_POST['PRINCIPAL']; else $postCHAINNAME="";
  	if (isset($_POST['STATUS'])) $postSTATUS = $_POST['STATUS']; else $postSTATUS="A";
  }


#--------------------------------------------------------------------------------------------------------------------------

    /*
     *
     * START OF SCREEN
     *
     */
    echo "<HTML><HEAD></HEAD><BODY>";

    ?>
    <script type="text/javascript" defer>
    function selectedGlobalChain(val) {
    	getContent("<?php echo $ROOT.$PHPFOLDER; ?>functional/chains/chainForm.php","LOOKUPUID="+val); // func is in generalAjaxBase.php
    }

  function refreshSelectGlobalChains() {
  	AjaxRefresh("RBNAME=<?php echo $fldChosenGCRB; ?>&CALLBACK=selectedGlobalChain(this.value);",
  				"<?php echo $ROOT.$PHPFOLDER; ?>functional/administration/adminGlobalChainsListTable.php",
  			    "<?php echo $divAjaxMainContentArea; ?>",
  			    "Please wait whilst page is refreshed...",
  			    "");
  }
  </script>
  <?php
    echo "<div id='".$divAjaxMainContentArea."'><BR>";

    include_once($ROOT.$PHPFOLDER."DAO/StoreDAO.php");
    if ($postDMLTYPE=="INSERT") {
  	echo "<A href='javascript:refreshSelectGlobalChains();'><img src='".$DHTMLROOT.$PHPFOLDER."images/load-from-chain-icon.png' style='border-style: none' /></A>";
    }

  // create the JS for inteli lookups
  $storeDAO = new StoreDAO($dbConn);
  $globalChainList=$storeDAO->getAllChainsForPrincipal($principalId);

  echo "<SCR"."IPT type=\"text/javascript\" defer>";
  echo "var gsArrUID=new Array();";
  echo "var gsArrCN=new Array();";
  if (sizeof($globalChainList)>5000) {
  	echo "alert('WARNING: Size of ChainList returned for JS Lookups has exceeded allowable entries. Lookups have been disabled. Please inform RetailTrading Management.')";
  } else {
  		$i=0;
  		foreach ($globalChainList as $row) {
  			echo "gsArrUID[".$i."]=\"".str_replace('"','',$row['principal_chain_uid'])."\";";
  			echo "gsArrCN[".$i."]=\"".str_replace('"','',$row['chain_name'])."\";";
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
  	if (val.length==0) { parent.hideMsgBoxSystemFeedbackAll(); return; }
  	var pattern = new RegExp(val.replace(/[^a-zA-Z0-9]+/g,'')); // leave only alpha chars and digits
  	for (i=0; ((i<gsArrCN.length) && (matchCnt<2000)); i++) {
  		switch (fldName) {
  			case "CHAINNAME": if (pattern.test(gsArrCN[i].replace(/[^a-zA-Z0-9]+/g,'').toLowerCase())) {
  							  list += "<tr><td class='tableReset standardFont' style='padding:5px;'>"+gsArrCN[i]+"</td></tr>";
  							  matchCnt++;
  							  if (matchCnt>(2000-1)) list += "<tr><td colspan=1 class='tableReset standardFont' style='padding:5px;'><I><B>list incomplete...list exceeds 2000.</B></I></td></tr>";
  							 }
  							 fullName = 'Chain Name';
  							 break;
  		}
  	}
  	if (list.length > 0) {
  		// user must use scroll wheel on mouse to scroll because otherwise onblur first to hide list
  		list = "<div style='height:250px;'><B>Principal-Chains with Similar "+fullName+":</B><BR><BR><table class='tableReset'>"+list+"</table></div>";
  		parent.showMsgBoxSystemFeedback(list);
  	}
  	else parent.hideMsgBoxSystemFeedbackAll();
  }

  <?php
  echo "</SCRIPT>";

      echo "<table width='720' border='1'>";
  echo "<tr>";
  echo "<td colspan=4>";
      echo "<INPUT type='hidden' name='action' value='add_store'>\n";
  echo "<tbody style=\"font-size: 12px;\">";
  echo "<tr class='even'>";
  if ($postDMLTYPE=="INSERT") {
  	echo '<td colspan="4"><b> Add a new chain to the master list</b></td>';
  } else {
   	echo '<td colspan="4"><b> Modify details of an existing Principal-Chain</b></td>';
    }
  echo '</tr>';

      echo "<TR class='".GUICommonUtils::styleEO($class)."'><TD width=\"160\">Chain Name"; GUICommonUtils::requiredField(); echo "</TD><TD><INPUT type='text' size='30' maxlength='40' id='CHAINNAME' value='".$postCHAINNAME."' onKeyUp='suggest(\"CHAINNAME\");' onblur='parent.hideMsgBoxSystemFeedbackAll();'>&nbsp;&nbsp;&nbsp;<img src='".$DHTMLROOT.$PHPFOLDER."images/archive-icon-1.png' style='border-style: none' /></TD></TR>";


  /*
   * CUSTOM CHAIN FIELDS : START
   */

?>
<SCRIPT type="text/javascript">
function addNewField(writeId, size, len){jQuery('#'+writeId).append(' <INPUT type="text" size="'+size+'" maxlength="'+len+'" value="" style="margin:1px 0px;">');}
</SCRIPT>
<?php


  $miscDAO = new MiscellaneousDAO($dbConn);

  if ($postPRINCIPALCHAINUID==""){
    $smpf = $miscDAO->getPrincipalSpecialFields($principalId,CT_CHAIN_SHORTCODE);
  } else {
    $smpf = $miscDAO->getPrincipalSpecialFieldValues($principalId,$postPRINCIPALCHAINUID,CT_CHAIN_SHORTCODE);
  }

  for($i = 0; $i < count($smpf); $i++) {

    $line = $smpf[$i];
    $value = str_replace(' ','',$line["name"]);
    $postVal = ($postPRINCIPALCHAINUID=="") ? ("") : (htmlentities(htmlspecialchars($line['value'])));
    $required = ($line['required']=='Y') ? true : false;
    $maxLength = ($line['value_max_length'] >= 1) ? ($line['value_max_length']) : (30);  //30 is the default max length.

    echo '<TR class="'.GUICommonUtils::styleEO($class).'">';
      echo '<TD>'. $line["name"] . ' ' , ($required)?(GUICommonUtils::requiredField()):('') , '</TD>';
        echo '<TD id="csf_' . $value . '"><INPUT type="text" size="20" maxlength="'.$maxLength.'" value="' .$postVal . '" ',($line["editable"]=='N' && $postDMLTYPE!="INSERT")?('DISABLED'):(''),'>';

        //loop through array and echo same names out.
        for($j = $i; $j < count($smpf); $j++) {
            if(isset($smpf[$i+1]) && $value == str_replace(' ','',$smpf[$i+1]["name"])){
              echo '<INPUT type="text" size="20" maxlength="'.$maxLength.'" value="' .$smpf[$i+1]["value"] . '" ',($line["editable"]=='N' && $postDMLTYPE!="INSERT")?('DISABLED'):(''),'>';
              $i++;
          }
        }

        echo '<span id="nfid',$i,'"></span>';
        if($postDMLTYPE=="INSERT" || ($postDMLTYPE=="UPDATE" && $line["editable"]=='Y')){
          echo'<A href="javascript:addNewField(\'nfid'.$i.'\',20,'.$maxLength.')" title="Add Another Field"><img src="../../images/add_icon.png" width="24" height="24" border="0" style="padding:0px;margin:0px;margin-bottom:-8px"></a>';
        }
      echo '</TD>';
    echo '</TR>',"\n";

   }

  /*
   * CUSTOM CHAIN FIELDS : END
   */

     //if ($class=="odd") $class="even"; else $class="odd";
    echo "<TR class='".GUICommonUtils::styleEO($class)."'><TD>Principal"; GUICommonUtils::requiredField(); echo "</TD><TD>".$principalName."</TD></TR>";

    echo "<TR class='".GUICommonUtils::styleEO($class)."'><TD>Old Code:</TD><TD>";
    BasicInputElement::getCSS3RadioHorizontal("OLDCODE", "Not Specified,Generic Chain", ",".CHAIN_GENERIC_OLD_CODE, $postOLDCODE);
    echo "</TD></TR>";

    echo "<TR class='".GUICommonUtils::styleEO($class)."'><TD>Status"; GUICommonUtils::requiredField(); echo "</TD><TD>"; BasicInputElement::getGeneralHorizontalRB("STATUS","Active,Deleted","A,D",$postSTATUS,"N","N",null,null,null); echo "</TD></TR>";
    if ($postDMLTYPE=="INSERT") {
    	echo "<TR class='".GUICommonUtils::styleEO($class)."'><TD colspan='2'> Add Chain to Global Master&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type= 'checkbox' name='ACGM' value='Y'></TD></TR>";
    }
    echo "</TABLE><BR>";

    echo "<INPUT type='submit' class='submit' name='submit' value='Submit Chain' onclick='submitContentForm(\"".$postDMLTYPE."\");'>\n";

    echo "</FORM></div>";  // main content area
    echo "</BODY></HTML>";
#--------------------------------------------------------------------------------------------------------------------------

$dbConn->dbClose();

?>

<script type='text/javascript' defer>
var alreadySubmitted=false;

function submitContentForm(p_type) {
	if (alreadySubmitted) {
		return;
	}
	alreadySubmitted=true;
	var params='DMLTYPE='+p_type;

	params+='&PRINCIPALCHAINUID=<?php echo $postPRINCIPALCHAINUID ?>';
	params+='&CHAINNAME='+document.getElementById("CHAINNAME").value;
	params+='&PRINCIPALID=<?php echo $principalId; ?>';
	params+='&OLDCODE='+convertElementToArray(document.getElementsByName("OLDCODE"));
	params+='&STATUS='+convertElementToArray(document.getElementsByName("STATUS"));
	params+='&ACGM='+convertElementToArray(document.getElementsByName("ACGM"));
	<?php

	  //BUILD SUBMIT FOR CUSTOM FIELDS
      foreach ($smpf as $line) {
        $value = str_replace(' ','',$line["name"]);
       ?>
       var obj = jQuery('#csf_<?php echo $value ?> input');
	   var <?php echo $value ?> = new Array();
	   for(var i=0;i<obj.length;i++){
         <?php echo $value?>[i] = obj.eq(i).val().replace(/'/g,'').replace(/"/g,'');
       }
		<?php
		 echo "params+='&".$value."='+".$value.".join('#,#');";
      }

     ?>

	params=params.replace(/'/g,'').replace(/"/g,''); // get rid of quotes which can upset the display element
	AjaxRefreshWithResult(params,
						  '<?php echo $ROOT.$PHPFOLDER ?>functional/chains/chainSubmit.php',
						  'alreadySubmitted=false; if (msgClass.type=="S") successCallback("'+p_type+'"); else errorCallback("'+p_type+'");',
						  'Please wait while request is processed...');
}

function successCallback(p_type) {
	if (p_type=="INSERT") {
		document.getElementById("CHAINNAME").value='';
		css3_reset_by_val_OLDCODE('');

		//empty custom fields
		<?php
	      foreach ($smpf as $line) {
	    ?>
	    	var obj = jQuery('#csf_<?php echo str_replace(' ','',$line["name"]); ?> input').val('');
	    <?php
	      }
	    ?>
	}
}

function errorCallback(p_type) {

}
</script>