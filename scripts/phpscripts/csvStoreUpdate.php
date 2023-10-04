<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once ($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER.'properties/dbSettings.inc');

$postUSER=((isset($_GET["user"]))?$_GET["user"]:((isset($_POST["user"]))?$_POST["user"]:false));
$postHASHEADER=((isset($_POST["p_HASHEADER"]))?$_POST["p_HASHEADER"]:"N");
$postPREVIEW=((isset($_POST["p_PREVIEW"]))?$_POST["p_PREVIEW"]:"P");
$postPRINCIPAL=((isset($_POST["p_PRINCIPAL"]))?$_POST["p_PRINCIPAL"]:false);
$postPSMUID_NDX=((isset($_POST["p_PSMUID_NDX"]))?$_POST["p_PSMUID_NDX"]:false);
$postBILLNAME_NDX=((isset($_POST["p_BILLNAME_NDX"]))?$_POST["p_BILLNAME_NDX"]:false);
$postBILLADD1_NDX=((isset($_POST["p_BILLADD1_NDX"]))?$_POST["p_BILLADD1_NDX"]:false);
$postBILLADD2_NDX=((isset($_POST["p_BILLADD2_NDX"]))?$_POST["p_BILLADD2_NDX"]:false);
$postBILLADD3_NDX=((isset($_POST["p_BILLADD3_NDX"]))?$_POST["p_BILLADD3_NDX"]:false);
$postVATNUMBER_NDX=((isset($_POST["p_VATNUMBER_NDX"]))?$_POST["p_VATNUMBER_NDX"]:false);
$postEANCODE_NDX=((isset($_POST["p_EANCODE_NDX"]))?$_POST["p_EANCODE_NDX"]:false);
$postRETAILER_NDX=((isset($_POST["p_RETAILER_NDX"]))?$_POST["p_RETAILER_NDX"]:false);
$postDEPOTUID_NDX=((isset($_POST["p_DEPOTUID_NDX"]))?$_POST["p_DEPOTUID_NDX"]:false);
$postDEPOTLOOKUP_NDX=((isset($_POST["p_DEPOTLOOKUP_NDX"]))?$_POST["p_DEPOTLOOKUP_NDX"]:false);
$postCHAINUID_NDX=((isset($_POST["p_CHAINUID_NDX"]))?$_POST["p_CHAINUID_NDX"]:false);
$postCHAINLOOKUP_NDX=((isset($_POST["p_CHAINLOOKUP_NDX"]))?$_POST["p_CHAINLOOKUP_NDX"]:false);
$postALTCHAINUID_NDX=((isset($_POST["p_ALTCHAINUID_NDX"]))?$_POST["p_ALTCHAINUID_NDX"]:false);
$postALTCHAINLOOKUP_NDX=((isset($_POST["p_ALTCHAINLOOKUP_NDX"]))?$_POST["p_ALTCHAINLOOKUP_NDX"]:false);


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

$content = trim(file_get_contents("{$ROOT}{$PHPFOLDER}scripts/phpscripts/datadump/import.csv"));

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
    $postPSMUID_VAL = (($postPSMUID_NDX!="")?$line[$postPSMUID_NDX]:"");
    $postBILLNAME_VAL = (($postBILLNAME_NDX!="")?$line[$postBILLNAME_NDX]:"");
    $postBILLADD1_VAL = (($postBILLADD1_NDX!="")?$line[$postBILLADD1_NDX]:"");
    $postBILLADD2_VAL = (($postBILLADD2_NDX!="")?$line[$postBILLADD2_NDX]:"");
    $postBILLADD3_VAL = (($postBILLADD3_NDX!="")?$line[$postBILLADD3_NDX]:"");
    $postVATNUMBER_VAL = (($postVATNUMBER_NDX!="")?$line[$postVATNUMBER_NDX]:"");
    $postEANCODE_VAL = (($postEANCODE_NDX!="")?$line[$postEANCODE_NDX]:"");
    $postRETAILER_VAL = (($postRETAILER_NDX!="")?$line[$postRETAILER_NDX]:"");
    $postDEPOTUID_VAL = (($postDEPOTUID_NDX!="")?$line[$postDEPOTUID_NDX]:"");
    $postDEPOTLOOKUP_VAL = (($postDEPOTLOOKUP_NDX!="")?$line[$postDEPOTLOOKUP_NDX]:"");
    $postCHAINUID_VAL = (($postCHAINUID_NDX!="")?$line[$postCHAINUID_NDX]:"");
    $postCHAINLOOKUP_VAL = (($postCHAINLOOKUP_NDX!="")?$line[$postCHAINLOOKUP_NDX]:"");
    $postALTCHAINUID_VAL = (($postALTCHAINUID_NDX!="")?$line[$postALTCHAINUID_NDX]:"");
    $postALTCHAINLOOKUP_VAL = (($postALTCHAINLOOKUP_NDX!="")?$line[$postALTCHAINLOOKUP_NDX]:"");
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
      <tr><td>Unique Store UId</td><td><input type='text' name='p_PSMUID_NDX' value='{$postPSMUID_NDX}'></td><td><input type='text' value='{$postPSMUID_VAL}' size='10'></td></tr>
      <tr><td>Bill Name</td><td><input type='text' name='p_BILLNAME_NDX' value='{$postBILLNAME_NDX}'></td><td><input type='text' value='{$postBILLNAME_VAL}' size='50'></td></tr>
      <tr><td>Bill Add1</td><td><input type='text' name='p_BILLADD1_NDX' value='{$postBILLADD1_NDX}'></td><td><input type='text' value='{$postBILLADD1_VAL}' size='50'></td></tr>
      <tr><td>Bill Add2</td><td><input type='text' name='p_BILLADD2_NDX' value='{$postBILLADD2_NDX}'></td><td><input type='text' value='{$postBILLADD2_VAL}' size='50'></td></tr>
      <tr><td>Bill Add3</td><td><input type='text' name='p_BILLADD3_NDX' value='{$postBILLADD3_NDX}'></td><td><input type='text' value='{$postBILLADD3_VAL}' size='50'></td></tr>
      <tr><td>VAT Number</td><td><input type='text' name='p_VATNUMBER_NDX' value='{$postVATNUMBER_NDX}'></td><td><input type='text' value='{$postVATNUMBER_VAL}' size='15'></td></tr>
      <tr><td>EAN Code</td><td><input type='text' name='p_EANCODE_NDX' value='{$postEANCODE_NDX}'></td><td><input type='text' value='{$postEANCODE_VAL}' size='15'></td></tr>
      <tr><td>Retailer</td><td><input type='text' name='p_RETAILER_NDX' value='{$postRETAILER_NDX}'></td><td><input type='text' value='{$postRETAILER_VAL}' size='15'></td></tr>
      <tr><td>Depot UID</td><td><input type='text' name='p_DEPOTUID_NDX' value='{$postDEPOTUID_NDX}'></td><td><input type='text' value='{$postDEPOTUID_VAL}' size='15'></td></tr>
      <tr><td>Depot Name</td><td><input type='text' name='p_DEPOTLOOKUP_NDX' value='{$postDEPOTLOOKUP_NDX}'></td><td><input type='text' value='{$postDEPOTLOOKUP_VAL}' size='15'></td></tr>
      <tr><td>Chain UID</td><td><input type='text' name='p_CHAINUID_NDX' value='{$postCHAINUID_NDX}'></td><td><input type='text' value='{$postCHAINUID_VAL}' size='15'></td></tr>
      <tr><td>Chain Name</td><td><input type='text' name='p_CHAINLOOKUP_NDX' value='{$postCHAINLOOKUP_NDX}'></td><td><input type='text' value='{$postCHAINLOOKUP_VAL}' size='15'></td></tr>
      <tr><td>Alt Chain UID</td><td><input type='text' name='p_ALTCHAINUID_NDX' value='{$postALTCHAINUID_NDX}'></td><td><input type='text' value='{$postALTCHAINUID_VAL}' size='15'></td></tr>
      <tr><td>Alt Name</td><td><input type='text' name='p_ALTCHAINLOOKUP_NDX' value='{$postALTCHAINLOOKUP_NDX}'></td><td><input type='text' value='{$postALTCHAINLOOKUP_VAL}' size='15'></td></tr>
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

// generate the SQL
if ($postPREVIEW=="R") {

  if (
    (empty($postPRINCIPAL))
  ) {
    echo "<p>Principal is not selected !</p>";

  } else {
  	
 // 	print_r($fileArr);

    foreach ($fileArr as $key=>$line){
      if (($postHASHEADER=="Y") && $key==0) continue;
      
//      print_r($line);


// echo "<br>";
// echo "LL";

      
//      echo mysqli_real_escape_string($dbConn->connection,$line['21']);
//      echo "<br>";

      $psmUID = mysqli_real_escape_string($dbConn->connection,$line[$postPSMUID_NDX]);
      if ((empty($psmUID)) || !preg_match("/^[0-9]*/",$psmUID)) {
        echo "<p style='color:red;'>Line {$key} could not be updated as the Unique Store Identifiers ".$psmUID." is not valid</p>";
      } else {
          $sql = "UPDATE principal_store_master
                  SET ";
          $set=array();
          if ($postBILLNAME_NDX!="" && preg_match("/^[0-9]*/",$postBILLNAME_NDX) && isset($line[$postBILLNAME_NDX])) $set[] = "bill_name='".mysqli_real_escape_string($dbConn->connection,$line[$postBILLNAME_NDX])."'";
          if ($postBILLADD1_NDX!="" && preg_match("/^[0-9]*/",$postBILLADD1_NDX) && isset($line[$postBILLADD1_NDX])) $set[] = "bill_add1='".mysqli_real_escape_string($dbConn->connection,$line[$postBILLADD1_NDX])."'";
          if ($postBILLADD2_NDX!="" && preg_match("/^[0-9]*/",$postBILLADD2_NDX) && isset($line[$postBILLADD2_NDX])) $set[] = "bill_add2='".mysqli_real_escape_string($dbConn->connection,$line[$postBILLADD2_NDX])."'";
          if ($postBILLADD3_NDX!="" && preg_match("/^[0-9]*/",$postBILLADD3_NDX) && isset($line[$postBILLADD3_NDX])) $set[] = "bill_add3='".mysqli_real_escape_string($dbConn->connection,$line[$postBILLADD3_NDX])."'";
          if ($postVATNUMBER_NDX!="" && preg_match("/^[0-9]*/",$postVATNUMBER_NDX) && isset($line[$postVATNUMBER_NDX])) $set[] = "vat_number='".mysqli_real_escape_string($dbConn->connection,$line[$postVATNUMBER_NDX])."'";
          if ($postEANCODE_NDX!="" && preg_match("/^[0-9]*/",$postEANCODE_NDX) && isset($line[$postEANCODE_NDX])) $set[] = "ean_code='".mysqli_real_escape_string($dbConn->connection,$line[$postEANCODE_NDX])."'";
          if ($postRETAILER_NDX!="" && preg_match("/^[0-9]*/",$postRETAILER_NDX) && isset($line[$postRETAILER_NDX]))
                $set[] = "retailer = (CASE '".mysqli_real_escape_string($dbConn->connection,$line[$postRETAILER_NDX])."'
                    										WHEN 'PNP' THEN 1
                    										WHEN 'CHECKERS' THEN 2
                    										ELSE NULL
                    									END)";
          // DEPOT
          if (strval($postDEPOTLOOKUP_NDX)!="" && preg_match("/^[0-9]+$/",$postDEPOTLOOKUP_NDX)) {
            $set[] = "depot_uid=(select uid from depot where trim('".mysqli_real_escape_string($dbConn->connection,$line[$postDEPOTLOOKUP_NDX])."') = name)";
          } else if ($postDEPOTUID_NDX!="" && preg_match("/^[0-9]+$/",$postDEPOTUID_NDX)) {
            $set[] = "depot_uid='".mysqli_real_escape_string($dbConn->connection,$line[$postDEPOTUID_NDX])."'";
          }

          // CHAIN
          if (strval($postCHAINLOOKUP_NDX)!="" && preg_match("/^[0-9]+$/",$postCHAINLOOKUP_NDX)) {
            $set[] = "principal_chain_uid=(select uid from principal_chain_master where principal_uid = '{$postPRINCIPAL}' and trim('".mysqli_real_escape_string($dbConn->connection,$line[$postCHAINLOOKUP_NDX])."') = description)";
          } else if ($postCHAINUID_NDX!="" && preg_match("/^[0-9]+$/",$postCHAINUID_NDX)) {
            $set[] = "principal_chain_uid='".mysqli_real_escape_string($dbConn->connection,$line[$postCHAINUID_NDX])."'";
          }

          // ALT CHAIN
          if (strval($postALTCHAINLOOKUP_NDX)!="" && preg_match("/^[0-9]+$/",$postALTCHAINLOOKUP_NDX)) {
            $set[] = "alt_principal_chain_uid=(select uid from principal_chain_master where principal_uid = '{$postPRINCIPAL}' and trim('".mysqli_real_escape_string($dbConn->connection,$line[$postALTCHAINLOOKUP_NDX])."') = description)";
          } else if ($postALTCHAINUID_NDX!="" && preg_match("/^[0-9]+$/",$postALTCHAINUID_NDX)) {
            $set[] = "alt_principal_chain_uid='".mysqli_real_escape_string($dbConn->connection,$line[$postALTCHAINUID_NDX])."'";
          }

          $sql.=implode(", ",$set).
                " WHERE principal_uid = '{$postPRINCIPAL}'
                  AND   uid = '{$psmUID}';";
          if (count($set)>0) {
            echo "<p>{$sql}</p>";
          }

        // special fields
        if (trim($sfArr[$row["uid"]])!="") {

          foreach ($mfSF as $row) {

            // first remove existing row
            $sql = "DELETE FROM special_field_details
                    WHERE  field_uid = '{$row["uid"]}'
                    AND    entity_uid = '{$psmUID}';";

            echo "<p>{$sql}</p>";

            // first remove existing row
            $sfFileColVal=((isset($line[$sfArr[$row["uid"]]]))?$line[$sfArr[$row["uid"]]]:"");
            $sql = "INSERT INTO special_field_details (field_uid,value,entity_uid)
                    VALUES ({$row["uid"]},'{$sfFileColVal}','{$psmUID}');";

            echo "<p>{$sql}</p>";

          }

        }


      }

    }

  }
}

echo "</body></html>";
?>