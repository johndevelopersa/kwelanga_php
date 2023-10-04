<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once ($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER.'properties/dbSettings.inc');
include_once($ROOT.$PHPFOLDER."DAO/SequenceDAO.php");

$postUSER=((isset($_GET["user"]))?$_GET["user"]:((isset($_POST["user"]))?$_POST["user"]:false));
$postHASHEADER=((isset($_POST["p_HASHEADER"]))?$_POST["p_HASHEADER"]:"N");
$postPREVIEW=((isset($_POST["p_PREVIEW"]))?$_POST["p_PREVIEW"]:"P");
$postPRINCIPAL=((isset($_POST["p_PRINCIPAL"]))?$_POST["p_PRINCIPAL"]:false);
$postDELIVERNAME_NDX=((isset($_POST["p_DELIVERNAME_NDX"]))?$_POST["p_DELIVERNAME_NDX"]:false);
$postDELIVERADD1_NDX=((isset($_POST["p_DELIVERADD1_NDX"]))?$_POST["p_DELIVERADD1_NDX"]:false);
$postDELIVERADD2_NDX=((isset($_POST["p_DELIVERADD2_NDX"]))?$_POST["p_DELIVERADD2_NDX"]:false);
$postDELIVERADD3_NDX=((isset($_POST["p_DELIVERADD3_NDX"]))?$_POST["p_DELIVERADD3_NDX"]:false);
$postBILLNAME_NDX=((isset($_POST["p_BILLNAME_NDX"]))?$_POST["p_BILLNAME_NDX"]:false);
$postBILLADD1_NDX=((isset($_POST["p_BILLADD1_NDX"]))?$_POST["p_BILLADD1_NDX"]:false);
$postBILLADD2_NDX=((isset($_POST["p_BILLADD2_NDX"]))?$_POST["p_BILLADD2_NDX"]:false);
$postBILLADD3_NDX=((isset($_POST["p_BILLADD3_NDX"]))?$_POST["p_BILLADD3_NDX"]:false);
$postVATNUMBER_NDX=((isset($_POST["p_VATNUMBER_NDX"]))?$_POST["p_VATNUMBER_NDX"]:false);
$postEANCODE_NDX=((isset($_POST["p_EANCODE_NDX"]))?$_POST["p_EANCODE_NDX"]:false);
$postDEPOTUID_NDX=((isset($_POST["p_DEPOTUID_NDX"]))?$_POST["p_DEPOTUID_NDX"]:false);
$postDEPOTLOOKUP_NDX=((isset($_POST["p_DEPOTLOOKUP_NDX"]))?$_POST["p_DEPOTLOOKUP_NDX"]:false);
$postDELDAYUID_NDX=((isset($_POST["p_DELDAYUID_NDX"]))?$_POST["p_DELDAYUID_NDX"]:false);
$postDELDAYLOOKUP_NDX=((isset($_POST["p_DELDAYLOOKUP_NDX"]))?$_POST["p_DELDAYLOOKUP_NDX"]:false);
$postCHAINUID_NDX=((isset($_POST["p_CHAINUID_NDX"]))?$_POST["p_CHAINUID_NDX"]:false);
$postCHAINLOOKUP_NDX=((isset($_POST["p_CHAINLOOKUP_NDX"]))?$_POST["p_CHAINLOOKUP_NDX"]:false);
$postALTCHAINUID_NDX=((isset($_POST["p_ALTCHAINUID_NDX"]))?$_POST["p_ALTCHAINUID_NDX"]:false);
$postALTCHAINLOOKUP_NDX=((isset($_POST["p_ALTCHAINLOOKUP_NDX"]))?$_POST["p_ALTCHAINLOOKUP_NDX"]:false);
$postBRANCH_NDX=((isset($_POST["p_BRANCH_NDX"]))?$_POST["p_BRANCH_NDX"]:false);
$postOLDACCOUNT_NDX=((isset($_POST["p_OLDACCOUNT_NDX"]))?$_POST["p_OLDACCOUNT_NDX"]:false);
$postTELNO1_NDX=((isset($_POST["p_TELNO1_NDX"]))?$_POST["p_TELNO1_NDX"]:false);
$postTELNO2_NDX=((isset($_POST["p_TELNO2_NDX"]))?$_POST["p_TELNO2_NDX"]:false);
$postEMAIL_NDX=((isset($_POST["p_EMAIL_NDX"]))?$_POST["p_EMAIL_NDX"]:false);
$postAREAUID_NDX=((isset($_POST["p_AREAUID_NDX"]))?$_POST["p_AREAUID_NDX"]:false);
$postRETAILER_NDX=((isset($_POST["p_RETAILER_NDX"]))?$_POST["p_RETAILER_NDX"]:false);
$postNOVAT_NDX=((isset($_POST["p_NOVAT_NDX"]))?$_POST["p_NOVAT_NDX"]:false);

$dbConn = new dbConnect();
$dbConn->dbConnection(); // can connect to any db, as long as db is referenced as part of sql passed


if ($postUSER!="1976") {
	echo "Access Denied";
	return;
}

echo "<html>
      <head>
        <style>
        table {font-family:verdana; font-size:12px;}
        td { border-right:1px dotted gray; }
        </style>
      </head>
      <body style='font-family:verdana; font-size:12px;'>";

echo "<p>This program assumes you have copied your csv file to multlink/scripts/phpscripts/datadump/ as file name <b>import.csv</b>";

$fn = "import.csv";
echo $ROOT;
echo "LL";
// echo $phpfolder;

$content = trim(file_get_contents("{$ROOT}{$PHPFOLDER}scripts/phpscripts/datadump/{$fn}"));

if ($content===false) {
  echo "<p>File Not Found or could not be read!!</p>";
  return;
}

$fileArr = explode("\n",$content);

echo "<form name='pForm' action='".$_SERVER["PHP_SELF"]."' method='post'>
      <input type='hidden' name='user' value='".$postUSER."'>

      <input type='radio' name='p_HASHEADER' value='Y' ".(($postHASHEADER=="Y")?"checked='checked'":"")." onclick='document.pForm.submit();'>File has Header in row 1
      <input type='radio' name='p_HASHEADER' value='N' ".(($postHASHEADER=="N")?"checked='checked'":"")." onclick='document.pForm.submit();'>File does NOT have Header";



// convert to array for each line from CSV
$maxCols=0;
foreach ($fileArr as $key => &$line){
  $line = str_getcsv(
      $line, # Input line
      ',',   # Delimiter
      '"',   # Enclosure
      '\\'   # Escape char
  );

  if ($key==1) {
    $postDELIVERNAME_VAL = ((strval($postDELIVERNAME_NDX)!="")?$line[$postDELIVERNAME_NDX]:"");
    $postDELIVERADD1_VAL = ((strval($postDELIVERADD1_NDX)!="")?$line[$postDELIVERADD1_NDX]:"");
    $postDELIVERADD2_VAL = ((strval($postDELIVERADD2_NDX)!="")?$line[$postDELIVERADD2_NDX]:"");
    $postDELIVERADD3_VAL = ((strval($postDELIVERADD3_NDX)!="")?$line[$postDELIVERADD3_NDX]:"");
    $postBILLNAME_VAL = ((strval($postBILLNAME_NDX)!="")?$line[$postBILLNAME_NDX]:"");
    $postBILLADD1_VAL = ((strval($postBILLADD1_NDX)!="")?$line[$postBILLADD1_NDX]:"");
    $postBILLADD2_VAL = ((strval($postBILLADD2_NDX)!="")?$line[$postBILLADD2_NDX]:"");
    $postBILLADD3_VAL = ((strval($postBILLADD3_NDX)!="")?$line[$postBILLADD3_NDX]:"");
    $postVATNUMBER_VAL = ((strval($postVATNUMBER_NDX)!="")?$line[$postVATNUMBER_NDX]:"");
    $postEANCODE_VAL = ((strval($postEANCODE_NDX)!="")?$line[$postEANCODE_NDX]:"");
    $postDEPOTUID_VAL = ((strval($postDEPOTUID_NDX)!="")?$line[$postDEPOTUID_NDX]:"");
    $postDEPOTLOOKUP_VAL = ((strval($postDEPOTLOOKUP_NDX)!="")?$line[$postDEPOTLOOKUP_NDX]:"");
    $postDELDAYUID_VAL = ((strval($postDELDAYUID_NDX)!="")?$line[$postDELDAYUID_NDX]:"");
    $postDELDAYLOOKUP_VAL = ((strval($postDELDAYLOOKUP_NDX)!="")?$line[$postDELDAYLOOKUP_NDX]:"");
    $postCHAINUID_VAL = ((strval($postCHAINUID_NDX)!="")?$line[$postCHAINUID_NDX]:"");
    $postCHAINLOOKUP_VAL = ((strval($postCHAINLOOKUP_NDX)!="")?$line[$postCHAINLOOKUP_NDX]:"");
    $postALTCHAINUID_VAL = ((strval($postALTCHAINUID_NDX)!="")?$line[$postALTCHAINUID_NDX]:"");
    $postALTCHAINLOOKUP_VAL = ((strval($postALTCHAINLOOKUP_NDX)!="")?$line[$postALTCHAINLOOKUP_NDX]:"");
    $postBRANCH_VAL = ((strval($postBRANCH_NDX)!="")?$line[$postBRANCH_NDX]:"");
    $postOLDACCOUNT_VAL = ((strval($postOLDACCOUNT_NDX)!="")?$line[$postOLDACCOUNT_NDX]:"");
    $postTELNO1_VAL = ((strval($postTELNO1_NDX)!="")?$line[$postTELNO1_NDX]:"");
    $postTELNO2_VAL = ((strval($postTELNO2_NDX)!="")?$line[$postTELNO2_NDX]:"");
    $postEMAIL_VAL = ((strval($postEMAIL_NDX)!="")?$line[$postEMAIL_NDX]:"");
    $postAREAUID_VAL = ((strval($postAREAUID_NDX)!="")?$line[$postAREAUID_NDX]:"");
    $postRETAILER_VAL = ((strval($postRETAILER_NDX)!="")?$line[$postRETAILER_NDX]:"");
    $postNOVAT_VAL = ((strval($postNOVAT_NDX)!="")?$line[$postNOVAT_NDX]:"");
  }


  if (count($line) > $maxCols) $maxCols = count($line);

}
unset($line);

echo "<p>".count($fileArr)." line(s) found, {$maxCols} columns.</p>";

$sql = "select uid,name from principal order by name";
$mfP = $dbConn->dbGetAll($sql);
echo "<p>Please Choose your Principal:<br>
      <select name='p_PRINCIPAL' id='p_PRINCIPAL' autofocus='autofocus' style='font-size:13px;margin:18px 0px 10px 0px;' onchange='document.pForm.submit();' >
      <option value=''>No Principal Selected</option>";
      foreach ($mfP as $row) {
              echo "<option value='{$row['uid']}' ".(($postPRINCIPAL==$row["uid"])?" selected='selected'":"")." >{$row['name']} ({$row["uid"]})</option>";
      }
echo "</select></p>";


if ($postHASHEADER=="Y") {

 echo "<hr>
       <p>Header row:</p>
       <span style='font-weight:bold;'>".(implode(" | ",$fileArr[0]))."</span>";
 echo "<hr>
       <p>1st Detail Row:</p>
       <span style='font-weight:bold;'>".(implode(" | ",$fileArr[1]))."</span>";

 $firstDtlRow=1;

} else {
  echo "<hr>
        <p>Header row:</p>
       <span style='font-weight:bold;'>... no header row ...</span>";
  echo "<hr>
        <p>1st Detail Row:</p>
       <span style='font-weight:bold;'>".(implode(" | ",$fileArr[0]))."</span>";

  $firstDtlRow=0;
}


// available columns in Store master for updating
echo "<hr>
      <p style='color:green'>The following store fields are available for updating in PRINCIPAL STORE MASTER</p>
      <br>
      <table cellpadding=5 cellspacing=5><tr style='text-align:top;'><td style='text-align:top;' valign=top>
      <table cellpadding=5 cellspacing=5 style='text-align:top;'>
      <tr><td><u>Store Main Fields</u></td><td><u>Column Numbers to Use</u></td><td>Effective Values<br>(after preview)</td></tr>
      <tr><td>DELIVER Name</td><td><input type='text' name='p_DELIVERNAME_NDX' value='{$postDELIVERNAME_NDX}'></td><td><input type='text' value='{$postDELIVERNAME_VAL}' size='50'></td></tr>
      <tr><td>DELIVER Add1</td><td><input type='text' name='p_DELIVERADD1_NDX' value='{$postDELIVERADD1_NDX}'></td><td><input type='text' value='{$postDELIVERADD1_VAL}' size='50'></td></tr>
      <tr><td>DELIVER Add2</td><td><input type='text' name='p_DELIVERADD2_NDX' value='{$postDELIVERADD2_NDX}'></td><td><input type='text' value='{$postDELIVERADD2_VAL}' size='50'></td></tr>
      <tr><td>DELIVER Add3</td><td><input type='text' name='p_DELIVERADD3_NDX' value='{$postDELIVERADD3_NDX}'></td><td><input type='text' value='{$postDELIVERADD3_VAL}' size='50'></td></tr>
      <tr><td>Bill Name</td><td><input type='text' name='p_BILLNAME_NDX' value='{$postBILLNAME_NDX}'></td><td><input type='text' value='{$postBILLNAME_VAL}' size='50'></td></tr>
      <tr><td>Bill Add1</td><td><input type='text' name='p_BILLADD1_NDX' value='{$postBILLADD1_NDX}'></td><td><input type='text' value='{$postBILLADD1_VAL}' size='50'></td></tr>
      <tr><td>Bill Add2</td><td><input type='text' name='p_BILLADD2_NDX' value='{$postBILLADD2_NDX}'></td><td><input type='text' value='{$postBILLADD2_VAL}' size='50'></td></tr>
      <tr><td>Bill Add3</td><td><input type='text' name='p_BILLADD3_NDX' value='{$postBILLADD3_NDX}'></td><td><input type='text' value='{$postBILLADD3_VAL}' size='50'></td></tr>
      <tr><td>VAT Number</td><td><input type='text' name='p_VATNUMBER_NDX' value='{$postVATNUMBER_NDX}'></td><td><input type='text' value='{$postVATNUMBER_VAL}' size='15'></td></tr>
      <tr><td>EAN Code</td><td><input type='text' name='p_EANCODE_NDX' value='{$postEANCODE_NDX}'></td><td><input type='text' value='{$postEANCODE_VAL}' size='15'></td></tr>

      <tr><td>RT Depot UId</td><td><input type='text' name='p_DEPOTUID_NDX' value='{$postDEPOTUID_NDX}'></td><td><input type='text' value='{$postDEPOTUID_VAL}' size='15'></td></tr>
      <tr><td style='color:gray;'>Lookup Depot on Name</td><td><input type='text' name='p_DEPOTLOOKUP_NDX' value='{$postDEPOTLOOKUP_NDX}'></td><td><input type='text' value='{$postDEPOTLOOKUP_VAL}' size='15'></td></tr>
      <tr><td>RT DelDay UId</td><td><input type='text' name='p_DELDAYUID_NDX' value='{$postDELDAYUID_NDX}'></td><td><input type='text' value='{$postDELDAYUID_VAL}' size='15'></td></tr>
      <tr><td style='color:gray;'>Lookup DelDay on Name</td><td><input type='text' name='p_DELDAYLOOKUP_NDX' value='{$postDELDAYLOOKUP_NDX}'></td><td><input type='text' value='{$postDELDAYLOOKUP_VAL}' size='15'></td></tr>
      <tr><td>RT Chain UId</td><td><input type='text' name='p_CHAINUID_NDX' value='{$postCHAINUID_NDX}'></td><td><input type='text' value='{$postCHAINUID_VAL}' size='15'></td></tr>
      <tr><td style='color:gray;'>Lookup Chain on Name</td><td><input type='text' name='p_CHAINLOOKUP_NDX' value='{$postCHAINLOOKUP_NDX}'></td><td><input type='text' value='{$postCHAINLOOKUP_VAL}' size='15'></td></tr>
      <tr><td>RT Alt Chain UId</td><td><input type='text' name='p_ALTCHAINUID_NDX' value='{$postALTCHAINUID_NDX}'></td><td><input type='text' value='{$postALTCHAINUID_VAL}' size='15'></td></tr>
      <tr><td>Lookup Alt Chain on Name</td><td><input type='text' name='p_ALTCHAINLOOKUP_NDX' value='{$postALTCHAINLOOKUP_NDX}'></td><td><input type='text' value='{$postALTCHAINLOOKUP_VAL}' size='15'></td></tr>

      <tr><td>Branch</td><td><input type='text' name='p_BRANCH_NDX' value='{$postBRANCH_NDX}'></td><td><input type='text' value='{$postBRANCH_VAL}' size='15'></td></tr>
      <tr><td>Old Account (empty vals will get autoSeq)</td><td><input type='text' name='p_OLDACCOUNT_NDX' value='{$postOLDACCOUNT_NDX}'></td><td><input type='text' value='{$postOLDACCOUNT_VAL}' size='15'></td></tr>
      <tr><td>TELNO1</td><td><input type='text' name='p_TELNO1_NDX' value='{$postTELNO1_NDX}'></td><td><input type='text' value='{$postTELNO1_VAL}' size='15'></td></tr>
      <tr><td>TELNO2</td><td><input type='text' name='p_TELNO2_NDX' value='{$postTELNO2_NDX}'></td><td><input type='text' value='{$postTELNO2_VAL}' size='15'></td></tr>
      <tr><td>EMail</td><td><input type='text' name='p_EMAIL_NDX' value='{$postEMAIL_NDX}'></td><td><input type='text' value='{$postEMAIL_VAL}' size='15'></td></tr>
      <tr><td>Area UId</td><td><input type='text' name='p_AREAUID_NDX' value='{$postAREAUID_NDX}'></td><td><input type='text' value='{$postAREAUID_VAL}' size='15'></td></tr>
      <tr><td>Retailer (1=PnP;2=Checkers)</td><td><input type='text' name='p_RETAILER_NDX' value='{$postRETAILER_NDX}'></td><td><input type='text' value='{$postRETAILER_VAL}' size='15'></td></tr>
      <tr><td>NoVAT (values: 'NO VAT' or 'VAT')</td><td><input type='text' name='p_NOVAT_NDX' value='{$postNOVAT_NDX}'></td><td><input type='text' value='{$postNOVAT_VAL}' size='15'></td></tr>
      <tr><td colspan=3><u>Store Special Fields</u><td></tr>";

if (empty($postPRINCIPAL)) {
  echo "<tr><td colspan=3><u>No Principal Selected</u><td></tr>";
}

$sql = "select uid,name from special_field_fields where principal_uid='{$postPRINCIPAL}' and `type`='S';";
$mfSF = $dbConn->dbGetAll($sql);
$sfArr = array();
foreach ($mfSF as $row) {
  $sfArr[$row["uid"]] = ((isset($_POST["p_SF{$row["uid"]}_NDX"]))?$_POST["p_SF{$row["uid"]}_NDX"]:"");
  $sfFileColVal=((isset($fileArr[0][$sfArr[$row["uid"]]]))?$fileArr[$firstDtlRow][$sfArr[$row["uid"]]]:"");
  echo"<tr><td>{$row["uid"]} - {$row["name"]}</td><td><input type='text' name='p_SF{$row["uid"]}_NDX' value='{$sfArr[$row["uid"]]}'></td><td><input type='text' value='{$sfFileColVal}'></td></tr>";
}

echo "</table></td><td>";

// list the chosen values
echo "<p><u>Column Numbering</u></p>";
// always show the header if present
$i=0;
foreach($fileArr[0] as $col) {
  echo "<p style='width:250px;'nowrap;><div style='margin-right:10px;width:50px;padding:left;5px;padding-right:5px;float:left;background-color:#006000; color:white;'>COL {$i}</div>{$col} ({$fileArr[1][$i]})</p>";
  $i++;
}

echo "</td></tr></table>";


echo "<br><br>
      <input type='radio' name='p_PREVIEW' value='P' ".(($postPREVIEW=="P")?"checked='checked'":"").">Preview Parameter results
      <input type='radio' name='p_PREVIEW' value='R' ".(($postPREVIEW=="R")?"checked='checked'":"")."><span style='color:red;'>RUN UPDATE !</span>

      <br><br>
      <input type='button' value='submit' onclick='document.pForm.submit();' >";

echo "</form>";

if (count($fileArr)>1) {
  // preparatory DML
  $rTO = $dbConn->processPosting("drop table if exists x_store", "");
  if ($rTO->type!=FLAG_ERRORTO_SUCCESS ){
    echo "<p style='color:red'>Could not drop temp table !</p>";
    return;
  }

  $rTO = $dbConn->processPosting("CREATE TABLE `x_store` (
                                	`uid` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                                  `f0` VARCHAR(100) NULL DEFAULT NULL,
                                	`f1` VARCHAR(100) NULL DEFAULT NULL,
                                	`f2` VARCHAR(100) NULL DEFAULT NULL,
                                	`f3` VARCHAR(100) NULL DEFAULT NULL,
                                	`f4` VARCHAR(100) NULL DEFAULT NULL,
                                	`f5` VARCHAR(100) NULL DEFAULT NULL,
                                	`f6` VARCHAR(100) NULL DEFAULT NULL,
                                	`f7` VARCHAR(100) NULL DEFAULT NULL,
                                	`f8` VARCHAR(100) NULL DEFAULT NULL,
                                	`f9` VARCHAR(100) NULL DEFAULT NULL,
                                	`f10` VARCHAR(100) NULL DEFAULT NULL,
                                	`f11` VARCHAR(100) NULL DEFAULT NULL,
                                	`f12` VARCHAR(100) NULL DEFAULT NULL,
                                	`f13` VARCHAR(100) NULL DEFAULT NULL,
                                	`f14` VARCHAR(100) NULL DEFAULT NULL,
                                	`f15` VARCHAR(100) NULL DEFAULT NULL,
                                	`f16` VARCHAR(100) NULL DEFAULT NULL,
                                	`f17` VARCHAR(100) NULL DEFAULT NULL,
                                	`f18` VARCHAR(100) NULL DEFAULT NULL,
                                	`f19` VARCHAR(100) NULL DEFAULT NULL,
                                	`f20` VARCHAR(100) NULL DEFAULT NULL,
                                	`f21` VARCHAR(100) NULL DEFAULT NULL,
                                	`f22` VARCHAR(100) NULL DEFAULT NULL,
                                	`f23` VARCHAR(100) NULL DEFAULT NULL,
                                	`f24` VARCHAR(100) NULL DEFAULT NULL,
                                	`f25` VARCHAR(100) NULL DEFAULT NULL,
                                	`f26` VARCHAR(100) NULL DEFAULT NULL,
                                	`f27` VARCHAR(100) NULL DEFAULT NULL,
                                	`f28` VARCHAR(100) NULL DEFAULT NULL,
                                	`f29` VARCHAR(100) NULL DEFAULT NULL,
                                	`f30` VARCHAR(100) NULL DEFAULT NULL,
                                	`f31` VARCHAR(100) NULL DEFAULT NULL,
                                	`f32` VARCHAR(100) NULL DEFAULT NULL,
                                  `depot_uid` VARCHAR(100) NULL DEFAULT NULL,
                                  `delivery_day_uid` VARCHAR(100) NULL DEFAULT NULL,
                                  `chain_uid` VARCHAR(100) NULL DEFAULT NULL,
                                  `alt_chain_uid` VARCHAR(100) NULL DEFAULT NULL,
                                  `no_vat` VARCHAR(1) NULL DEFAULT NULL,
                                	PRIMARY KEY (`uid`)
                                )
                                ENGINE=InnoDB", "");
  if ($rTO->type!=FLAG_ERRORTO_SUCCESS ){
    echo "<p style='color:red'>Could not create temp table !</p>";
    return;
  }

  if ($maxCols>33) {
    echo "<p style='color:red'>Cannot continue with import : the file has more than allowed 33 fields !</p>";
    return;
  }

  $sql="LOAD DATA LOCAL INFILE '".DIR_DATA_SCRIPTS_IMPORT."{$fn}' IGNORE INTO TABLE ".iDATABASE.".x_store
        FIELDS TERMINATED BY ','
        OPTIONALLY ENCLOSED BY '\"'
        ESCAPED BY '\\\'
        LINES TERMINATED BY '\\r\\n' ".
        (($postHASHEADER=="Y")?" IGNORE 1 LINES ":"")."
        (`f0`, `f1`, `f2`, `f3`, `f4`, `f5`, `f6`, `f7`, `f8`, `f9`, `f10`, `f11`, `f12`, `f13`, `f14`, `f15`, `f16`, `f17`, `f18`, `f19`, `f20`, `f21`, `f22`, `f23`, `f24`, `f25`, `f26`, `f27`, `f28`, `f29`, `f30`, `f31`, `f32`)
        SET depot_uid=99,
            delivery_day_uid=8,
            chain_uid=null,
            alt_chain_uid=null,
            no_vat = 0";

  $rTO = $dbConn->processPosting($sql,"");
  if ($rTO->type!=FLAG_ERRORTO_SUCCESS ){
    echo "<p style='color:red'>Failed to import into temp table!</p>";
    echo "<p style='color:red'>".mysqli_error($dbConn->connection)."</p>";
    return;
  }

  $tArr = $dbConn->dbGetAll("select count(*) cnt from x_store");

  if ($tArr[0]["cnt"]!=(($postHASHEADER=="Y")?count($fileArr)-1:count($fileArr))){
    echo "<p style='color:red'>Failed to import into temp table : line count incorrect!</p>".mysqli_errno($dbConn->connection);
    return;
  }

  // DEPOT UID
  if (strval($postDEPOTLOOKUP_NDX)!="" && preg_match("/^[0-9]+$/",$postDEPOTLOOKUP_NDX)) {
    $rTO = $dbConn->processPosting("UPDATE x_store a, depot b set depot_uid = b.uid where trim(a.f{$postDEPOTLOOKUP_NDX}) = b.name","");
    if ($rTO->type!=FLAG_ERRORTO_SUCCESS ){
      echo "<p style='color:red'>Failed to update depot  into temp table!</p>";
      echo "<p style='color:red'>".mysqli_error($dbConn->connection)."</p>";
      return;
    }
  } else if ($postDEPOTUID_NDX!="" && preg_match("/^[0-9]+$/",$postDEPOTUID_NDX)) {
      $rTO = $dbConn->processPosting("UPDATE x_store a set depot_uid = a.f{$postDEPOTUID_NDX} WHERE a.f{$postDEPOTUID_NDX} REGEXP '^[0-9]+$'","");
      if ($rTO->type!=FLAG_ERRORTO_SUCCESS ){
        echo "<p style='color:red'>Failed to update depot  into temp table!</p>";
        echo "<p style='color:red'>".mysqli_error($dbConn->connection)."</p>";
        return;
      }
  }

  // DELIVERY DAY LOOKUP
  if (strval($postDELDAYLOOKUP_NDX)!="" && preg_match("/^[0-9]+$/",$postDELDAYLOOKUP_NDX)) {
    $rTO = $dbConn->processPosting("update x_store
                                    set    delivery_day_uid = (CASE lower(f{$postDELDAYLOOKUP_NDX})
                                    										WHEN 'monday' THEN 1
                                    										WHEN 'tuesday' THEN 2
                                    										WHEN 'wednesday' THEN 3
                                    										WHEN 'thursday' THEN 4
                                    										WHEN 'friday' THEN 5
                                    										WHEN 'saturday' THEN 6
                                    										WHEN 'sunday' THEN 7
                                    										ELSE 8
                                    									END)","");
    if ($rTO->type!=FLAG_ERRORTO_SUCCESS ){
      echo "<p style='color:red'>Failed to import into temp table!</p>";
      echo "<p style='color:red'>".mysqli_error($dbConn->connection)."</p>";
      return;
    }
  } else if ($postDELDAYUID_NDX!="" && preg_match("/^[0-9]+$/",$postDELDAYUID_NDX)) {
      $rTO = $dbConn->processPosting("UPDATE x_store a set depot_uid = a.f{$postDELDAYUID_NDX} WHERE a.f{$postDELDAYUID_NDX} REGEXP '^[0-9]+$'","");
      if ($rTO->type!=FLAG_ERRORTO_SUCCESS ){
        echo "<p style='color:red'>Failed to update delday into temp table!</p>";
        echo "<p style='color:red'>".mysqli_error($dbConn->connection)."</p>";
        return;
      }
  }

  // NO VAT conversion
  if (strval($postNOVAT_NDX)!="" && preg_match("/^[0-9]+$/",$postNOVAT_NDX)) {
    $rTO = $dbConn->processPosting("update x_store
                                    set    no_vat = 1
                                    where  upper(f{$postNOVAT_NDX}) = 'NO VAT'","");
    if ($rTO->type!=FLAG_ERRORTO_SUCCESS ){
      echo "<p style='color:red'>Failed to import into temp table!</p>";
      echo "<p style='color:red'>".mysqli_error($dbConn->connection)."</p>";
      return;
    }
  }

  // CHAIN UID
  if (strval($postCHAINLOOKUP_NDX)!="" && preg_match("/^[0-9]+$/",$postCHAINLOOKUP_NDX)) {
    $rTO = $dbConn->processPosting("UPDATE x_store a, principal_chain_master b set chain_uid = b.uid where a.f{$postCHAINLOOKUP_NDX} = b.description","");
    if ($rTO->type!=FLAG_ERRORTO_SUCCESS ){
      echo "<p style='color:red'>Failed to update chain into temp table!</p>";
      echo "<p style='color:red'>".mysqli_error($dbConn->connection)."</p>";
      return;
    }
  } else if ($postCHAINUID_NDX!="" && preg_match("/^[0-9]+$/",$postCHAINUID_NDX)) {
    $rTO = $dbConn->processPosting("UPDATE x_store a set chain_uid = a.f{$postCHAINUID_NDX} WHERE a.f{$postCHAINUID_NDX} REGEXP '^[0-9]+$'","");
    if ($rTO->type!=FLAG_ERRORTO_SUCCESS ){
      echo "<p style='color:red'>Failed to update chain into temp table!</p>";
      echo "<p style='color:red'>".mysqli_error($dbConn->connection)."</p>";
      return;
    }
  }

  // ALT CHAIN UID
  if (strval($postCHAINLOOKUP_NDX)!="" && preg_match("/^[0-9]+$/",$postCHAINLOOKUP_NDX)) {
    $rTO = $dbConn->processPosting("UPDATE x_store a, principal_chain_master b set alt_chain_uid = b.uid where a.f{$postCHAINLOOKUP_NDX} = b.description","");
    if ($rTO->type!=FLAG_ERRORTO_SUCCESS ){
      echo "<p style='color:red'>Failed to update alt chain into temp table!</p>";
      echo "<p style='color:red'>".mysqli_error($dbConn->connection)."</p>";
      return;
    }
  } else if ($postALTCHAINUID_NDX!="" && preg_match("/^[0-9]+$/",$postALTCHAINUID_NDX)) {
    $rTO = $dbConn->processPosting("UPDATE x_store a set alt_chain_uid = a.f{$postALTCHAINUID_NDX} WHERE a.f{$postALTCHAINUID_NDX} REGEXP '^[0-9]+$'","");
    if ($rTO->type!=FLAG_ERRORTO_SUCCESS ){
      echo "<p style='color:red'>Failed to update alt chain into temp table!</p>";
      echo "<p style='color:red'>".mysqli_error($dbConn->connection)."</p>";
      return;
    }
  }

  // OLD ACCOUNT
  $oACnt=0; $i=0;
  if (strval($postOLDACCOUNT_NDX)!="" && preg_match("/^[0-9]+$/",$postOLDACCOUNT_NDX)) {
    $sequenceDAO = new SequenceDAO(null);
    $rows = $dbConn->dbGetAll("select uid, f{$postOLDACCOUNT_NDX} from x_store");
    foreach ($rows as $r) {
      if (strval($r["f{$postOLDACCOUNT_NDX}"])=="") {
        $oACnt++;
        if ($postPREVIEW=="R") {
          $oASeq = $sequenceDAO->getStoreOASequence();
        } else {
          $oASeq = "TEMPVAL{$i}"; // save on used sequences
        }
        $rTO = $dbConn->processPosting("Update x_store SET f{$postOLDACCOUNT_NDX}='{$oASeq}' WHERE uid = {$r["uid"]}", "");
        if ($rTO->type!=FLAG_ERRORTO_SUCCESS) {
          echo "<p style='color:red;'>Updating OLD ACCOUNT (UID {$r["uid"]}) FAILED</p>";
        }
        $i++;
      }
    }
  } else {
    // MUST TRAP THIS LATER DURING GENERATE !!
  }

  $dbConn->dbinsQuery("commit");


  // check basic fields
  $tArr = $dbConn->dbGetAll("select sum(if(depot_uid=99,1,0)) dpt_cnt,
                                    sum(if(delivery_day_uid=8,1,0)) dd_cnt,
                                    sum(if(chain_uid is null,1,0)) chain_cnt,
                                    sum(if(alt_chain_uid is null,1,0)) alt_chain_cnt,
                                    sum(".((preg_match("/^[0-9]+$/",$postDELIVERNAME_NDX))?"if(trim(f{$postDELIVERNAME_NDX})='',1,0)":1).") deliver_name_cnt,
                                    sum(".((preg_match("/^[0-9]+$/",$postBILLNAME_NDX))?"if(trim(f{$postBILLNAME_NDX})='',1,0)":1).") bill_name_cnt,
                                    sum(".((preg_match("/^[0-9]+$/",$postOLDACCOUNT_NDX))?"if(trim(f{$postOLDACCOUNT_NDX})='',1,0)":1).") old_account_cnt,
                                    sum(".((preg_match("/^[0-9]+$/",$postRETAILER_NDX))?"if(trim(upper(f{$postRETAILER_NDX})) in (1,2,'','PNP','CHECKERS','NOT SPECIFIED'),0,1)":0).") retailer_cnt
                                    from x_store");

  if ($oACnt>0){
    echo "<h3 style='color:blue;'>";
    echo "<p>INFO : {$oACnt} Old Accounts did not specify an key value, so these were set to autoSeq</p>";
    echo "</h3>";
  }
  if ($tArr[0]["dpt_cnt"]>0){
    echo "<h3 style='color:orange;'>";
    echo "<p>WARNING ! {$tArr[0]["dpt_cnt"]} Depot UIDs found to be invalid. If you continue, the depots will be set to UNKNOWN</p>";
    echo "</h3>";
  }
  if ($tArr[0]["dd_cnt"]>0){
    echo "<h3 style='color:orange;'>";
    echo "<p>WARNING ! {$tArr[0]["dd_cnt"]} Delivery Day UIDs found to be unknown. If you continue, the Del Days will be set to UNKNOWN</p>";
    echo "</h3>";
  }
  if ($tArr[0]["chain_cnt"]>0){
    echo "<h3 style='color:orange;'>";
    echo "<p>WARNING ! {$tArr[0]["chain_cnt"]} Chain UIDs found to be invalid. If you continue, the chains will be set to UNKNOWN</p>";
    echo "</h3>";
  }
  if ($tArr[0]["alt_chain_cnt"]>0){
    echo "<h3 style='color:orange;'>";
    echo "<p>WARNING ! {$tArr[0]["alt_chain_cnt"]} Alt Chain UIDs found to be invalid. If you continue, the alt chains will be set to UNKNOWN</p>";
    echo "</h3>";
  }
  if ($tArr[0]["deliver_name_cnt"]>0){
    echo "<h3 style='color:red;'>";
    echo "<p>WARNING ! {$tArr[0]["deliver_name_cnt"]} Delivery Names found to be empty.  <u>YOU CANNOT CONTINUE</u></p>";
    echo "</h3>";
  }
  if ($tArr[0]["bill_name_cnt"]>0){
    echo "<h3 style='color:red;'>";
    echo "<p>WARNING ! {$tArr[0]["bill_name_cnt"]} Bill Names found to be empty.  <u>YOU CANNOT CONTINUE</u></p>";
    echo "</h3>";
  }
  if ($tArr[0]["old_account_cnt"]>0){
    echo "<h3 style='color:red;'>";
    echo "<p>WARNING ! {$tArr[0]["old_account_cnt"]} Old Accounts found to be empty.  <u>YOU CANNOT CONTINUE</u></p>";
    echo "</h3>";
  }
  if ($tArr[0]["retailer_cnt"]>0){
    echo "<h3 style='color:red;'>";
    echo "<p>WARNING ! {$tArr[0]["retailer_cnt"]} Invalid Retailer options found.  <u>YOU CANNOT CONTINUE</u></p>";
    echo "</h3>";
  }


}



// generate the SQL
if ($postPREVIEW=="R") {
  if (
    (empty($postPRINCIPAL))
  ) {
    echo "<p>Principal is not selected !</p>";

  } else {

    $sql = "insert into principal_store_master (
                      principal_uid,
                      deliver_name,
                      deliver_add1,
                      deliver_add2,
                      deliver_add3,
                      bill_name,
                      bill_add1,
                      bill_add2,
                      bill_add3,
                      ean_code,
                      vat_number,
                      depot_uid,
                      delivery_day_uid,
                      no_vat,
                      on_hold,
                      principal_chain_uid,
                      alt_principal_chain_uid,
                      branch_code,
                      old_account,
                      captured_by,
                      stripped_deliver_name,
                      ledger_balance,
                      ledger_credit_limit,
                      status,
                      owned_by,
                      vendor_created_by_uid,
                      tel_no1,
                      tel_no2,
                      email_add,
                      area_uid,
                      epod_store_flag,
                      epod_rsa_id,
                      epod_cellphone_number,
                      vat_excl_authorised_by,
                      retailer,
                      principal_sales_representative_uid,
                      last_updated
          )
                      select
                      {$postPRINCIPAL}, -- principal_uid
                      f{$postDELIVERNAME_NDX},  -- deliver_name
                      ".((preg_match("/^[0-9]+$/",$postDELIVERADD1_NDX))?"f{$postDELIVERADD1_NDX}":"NULL").", -- deliver_add1
                      ".((preg_match("/^[0-9]+$/",$postDELIVERADD2_NDX))?"f{$postDELIVERADD2_NDX}":"NULL").", -- deliver_add2
                      ".((preg_match("/^[0-9]+$/",$postDELIVERADD3_NDX))?"f{$postDELIVERADD3_NDX}":"NULL").", -- deliver_add3
                      f{$postBILLNAME_NDX},  -- bill name
                      ".((preg_match("/^[0-9]+$/",$postBILLADD1_NDX))?"f{$postBILLADD1_NDX}":"NULL").", -- bill_add1
                      ".((preg_match("/^[0-9]+$/",$postBILLADD2_NDX))?"f{$postBILLADD2_NDX}":"NULL").", -- bill_add2
                      ".((preg_match("/^[0-9]+$/",$postBILLADD3_NDX))?"f{$postBILLADD3_NDX}":"NULL").", -- bill_add3
                      ".((preg_match("/^[0-9]+$/",$postEANCODE_NDX))?"f{$postEANCODE_NDX}":"NULL").", -- ean
                      ".((preg_match("/^[0-9]+$/",$postVATNUMBER_NDX))?"f{$postVATNUMBER_NDX}":"NULL").", -- vat number
                      depot_uid, -- depot_uid
                      delivery_day_uid, -- delivery_day_uid
                      no_vat, -- no_vat
                      0, -- on_hold
                      chain_uid, -- principal_chain_uid
                      alt_chain_uid, -- alt_principal_chain_uid
                      ".((preg_match("/^[0-9]+$/",$postBRANCH_NDX))?"f{$postBRANCH_NDX}":"NULL").", -- branch
                      f{$postOLDACCOUNT_NDX}, -- old_account
                      0,      -- captured_by
                      alphaNumericValue(f{$postDELIVERNAME_NDX}), -- stripped_deliver_name
                      0,   -- ledger_balance
                      0,   -- ledger_credit_limit
                      'A', -- status
                      null,-- owned_by
                      null,-- vendor_created_by_uid
                      ".((preg_match("/^[0-9]+$/",$postTELNO1_NDX))?"f{$postTELNO1_NDX}":"NULL").", -- tel_no1
                      ".((preg_match("/^[0-9]+$/",$postTELNO2_NDX))?"f{$postTELNO2_NDX}":"NULL").", -- tel_no2
                      ".((preg_match("/^[0-9]+$/",$postEMAIL_NDX))?"f{$postEMAIL_NDX}":"NULL").", -- email_add
                      ".((preg_match("/^[0-9]+$/",$postAREAUID_NDX))?"f{$postAREAUID_NDX}":"NULL").", -- area_uid
                      null,-- epod_store_flag
                      null,-- epod_rsa_id
                      null,-- epod_cellphone_number
                      null,-- vat_excl_authorised_by
                      ".((preg_match("/^[0-9]+$/",$postRETAILER_NDX))?"if(upper(f{$postRETAILER_NDX}) not in ('PNP','CHECKERS'),null,(CASE upper(f{$postRETAILER_NDX}) WHEN 'PNP' THEN 1 WHEN 'CHECKERS' THEN 2 ELSE f{$postRETAILER_NDX} END))":"NULL").",   -- retailer, use f[ndx] at end so it fails if u forget to add new value
                      0,   -- principal_sales_representative_uid
                      now() -- last_updated
                      from x_store
                  ";


      echo "<p>".nl2br($sql)."</p>";

      // special fields
        foreach ($mfSF as $row) {

          if (preg_match("/^[0-9]+$/",$sfArr[$row["uid"]]) ) {
            $sql="INSERT INTO special_field_details (field_uid, value, entity_uid)
                  SELECT  {$row["uid"]}, f{$sfArr[$row["uid"]]}, b.uid
                  from    x_store a,
                          principal_store_master b
                  where   a.f{$postOLDACCOUNT_NDX} = b.old_account
                  and     b.principal_uid = {$postPRINCIPAL}
                  and     f{$sfArr[$row["uid"]]}!='';";

            echo "<p>{$sql}</p>";
          }

        }


  }
}

echo "</body></html>";
?>