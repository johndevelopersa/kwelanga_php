<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
include_once ($ROOT . $PHPFOLDER . 'DAO/AdministrationDAO.php');
include_once ($ROOT . $PHPFOLDER . 'DAO/AdministrationDAO.php');
?>

<?php


if (!isset($_SESSION)) session_start() ;
$principalId = $_SESSION['principal_id'];
$userId = $_SESSION["user_id"];

$lhsColCnt = $rhsColCnt = 0;

$postJSONJOINS=((isset($_POST["p_JSONJOINS"]))?$_POST["p_JSONJOINS"]:false);

$dbConn = new dbConnect();
$dbConn->dbConnection();

$administrationDAO = new AdministrationDAO($dbConn);
$hasRole = $administrationDAO->hasRole($userId, $principalId, ROLE_ELECTRONIC_RECONCILIATION);
if (!$hasRole) {
  echo 'You do not have permissions for Electronic Reconciliation';
  return;
}

echo "<html>
      <head>
				<script type='text/javascript' language='javascript' src='".$DHTMLROOT.$PHPFOLDER."js/underscore.js'></script>
				<script type='text/javascript' language='javascript' src='".$DHTMLROOT.$PHPFOLDER."js/moment.min.js'></script>
        <script type='text/javascript' language='javascript' src='".$DHTMLROOT.$PHPFOLDER."js/jquery.js'></script>
	      <script type='text/javascript' language='javascript' src='".$DHTMLROOT.$PHPFOLDER."js/dops_global_functions.js'></script>
	      <LINK href='".$ROOT.$PHPFOLDER."css/1_default.css' rel='stylesheet' type='text/css'>

        <style>
	          table {}
	          td {white-space:nowrap;border-right:1px;border-right-style:dotted;}
	          td.summary-lvl1 {font-size:14px;color:#004470;border-top:1px; border-top-style:solid;}
	          td.summary-lvl2 {font-size:11px;color:#909090}
	          .center-objects {
    	        /* Internet Explorer 10 */
              display:-ms-flexbox;
              -ms-flex-pack:center;
              -ms-flex-align:center;

              /* Firefox */
              display:-moz-box;
              -moz-box-pack:center;
              -moz-box-align:center;

              /* Safari, Opera, and Chrome */
              display:-webkit-box;
              -webkit-box-pack:center;
              -webkit-box-align:center;

              /* W3C */
              display:box;
              box-pack:center;
              box-align:center;
    	      }
        </style>
      </head>
      <body style='font-family:verdana; font-size:12px; padding:10px;'>";

echo "<img src='{$DHTMLROOT}{$PHPFOLDER}images/kwelanga_colour_logo_70.png'>";

echo "<p class='rounded-corners' style='text-align:center;width:100%;background-color:#dbe4ef; padding:10px;'>
      <span style='font-size:14px;color:#004470;font-weight:bold;'>Online Electronic Reconciliation</span><br>
      </p>";

$file1Exists=false;
$file1=$ROOT.$PHPFOLDER."uploads/recon/p{$principalId}_u{$userId}_recon1.csv";
if (file_exists($file1)) {
  $file1Exists=true;
  $size = filesize($file1);
  $fileMDate = date("Y-m-d H:i:s",filemtime($file1));
  $fileExistsText1 = "File is present on server with size:{$size} bytes ; last modified : {$fileMDate}";
} else $fileExistsText1="";

$file2Exists=false;
$file2=$ROOT.$PHPFOLDER."uploads/recon/p{$principalId}_u{$userId}_recon2.csv";
if (file_exists($file2)) {
  $file2Exists=true;
  $size = filesize($file2);
  $fileMDate = date("Y-m-d H:i:s",filemtime($file2));
  $fileExistsText2 = "File is present on server with size:{$size} bytes ; last modified : {$fileMDate}";
} else $fileExistsText2="";

$configs = json_decode($postJSONJOINS)->struc->config;
$joins = json_decode($postJSONJOINS)->struc->joins;
/*
 * stdClass Object
(
    [config] => stdClass Object
        (
            [hasHeader1] => Array
                (
                    [0] => Y
                )

            [hasHeader2] => Array
                (
                    [0] => Y
                )
            [colConfigs] => stdClass Object
                (
                    [lhs] => Array
                        (
                            [0] => stdClass Object
                                (
                                    [displayOnResult] => Y
                                )

                            [1] => stdClass Object
                                (
                                    [displayOnResult] => N
                                )

                                ...

        )

    [joins] => Array
        (
            [0] => stdClass Object
                (
                    [fromCol] => 0
                    [toCol] => 0
                    [joinType] => REQUIRED
                    [priority] => 1
                    [joinFormat] => stdClass Object
                        (
                            [lhs] => stdClass Object
                                (
                                    [ignoreLeadingZeros] => N
                                    [fieldType] => TEXT
                                    [consolidate] => N
                                    [dateFormat] => YYYY-MM-DD
                                )

                            [rhs] => stdClass Object
                                (
                                    [ignoreLeadingZeros] => N
                                    [fieldType] => TEXT
                                    [consolidate] => N
                                    [dateFormat] => YYYY-MM-DD
                                )

                        )

                )

        )

)
 */

/*
echo "<pre>";
print_r($joins);
print_r($configs);
echo "</pre>";
*/

if ($file1Exists===false || $file2Exists===false) {
  echo "Please upload both files in step 1 and step 2 before a reconciliation can be performed.";
  return;
}

if (count($joins)==0) {
  echo "Please define atleast one join before submitting.";
  return;
}

$hasReq=false;
foreach($joins as $j) {
  if ($j->joinType=="REQUIRED") {
    $hasReq = true;
    break;
  }
}
if (!$hasReq) {
  echo "Please define atleast one REQUIRED join before submitting";
  return;
}

$file1Array = explode("\n",trim(file_get_contents($file1)));
$file2Array = explode("\n",trim(file_get_contents($file2)));

// handle headers
if ($configs->hasHeader1[0]=="Y") {
  $hdr1=convertLineToArray($file1Array[0]);
  unset($file1Array[0]);
} else {
  $hdr1=false;
}
if ($configs->hasHeader2[0]=="Y") {
  $hdr2=convertLineToArray($file2Array[0]);
  unset($file2Array[0]);
} else {
  $hdr2=false;
}

// first convert files to arrays
$file1LineArr = convertLinesToArray($file1Array, $lhsColCnt);
$file2LineArr = convertLinesToArray($file2Array, $rhsColCnt);

standardiseCols();

// consolidate if required
$consolidated = array("LHSNdx"=>array(),
                      "RHSNdx"=>array());
$lhsConsolidatedCols = $rhsConsolidatedCols = array();
foreach ($joins as $j) {
  if ($j->joinFormat->lhs->consolidate=="Y") $lhsConsolidatedCols[] = $j->fromCol;
  if ($j->joinFormat->rhs->consolidate=="Y") $rhsConsolidatedCols[] = $j->toCol;
}
if (count($lhsConsolidatedCols)>0) $file1LineArr = consolidate($file1LineArr, "LHS");
if (count($rhsConsolidatedCols)>0) $file2LineArr = consolidate($file2LineArr, "RHS");

define("RECON_PERFECTLY_MATCHED", "PERFECTLY MATCHED");
define("RECON_DISCREPANCY", "DISCREPANCY");
define("RECON_NOT_MATCHED_PARTIAL", "NOT MATCHED, PARTIAL MATCHED SET");
define("RECON_NOT_MATCHED", "NOT MATCHED");

// must be aded on each side under index ["recon"]["rTO"]
class ReconTO {
  public $matchStatus = RECON_NOT_MATCHED;
  public $matchStatusDescription = "";
  public $matchIndex = "-1";
  public $matchRating = "";
}

// pre add the class to each line
foreach($file1LineArr as &$l) {
  $rTO = new ReconTO();
  $l["recon"]["rTO"] = $rTO;
}

unset($l);
foreach($file2LineArr as &$l) {
  $rTO = new ReconTO();
  $l["recon"]["rTO"] = $rTO;
}
unset($l);

// index the RHS to speed up matching
$RHSIndex = index($file2LineArr);

// 1. find the exact matches
foreach ($file1LineArr as $key1=>&$f1) {
  $reqKey = getReqKey($f1);
  if (isset($RHSIndex[$reqKey])) {
    foreach ($RHSIndex[$reqKey] as $key2) {
      if ($file2LineArr[$key2]["recon"]["rTO"]->matchStatus==RECON_PERFECTLY_MATCHED) continue;

      if (allRequiredJoinsMatch($f1,$file2LineArr[$key2])) {
        if (allSecondaryJoinsMatch($f1,$file2LineArr[$key2])) {
          $f1["recon"]["rTO"]->matchStatus = RECON_PERFECTLY_MATCHED;
          $f1["recon"]["rTO"]->matchIndex = $key2;
          $file2LineArr[$key2]["recon"]["rTO"]->matchStatus = RECON_PERFECTLY_MATCHED;
          $file2LineArr[$key2]["recon"]["rTO"]->matchIndex = $key1;
          break;
        }
      }

    }
    unset($f2);

  }
}
unset($f1);

// 2. find the discrepancies
$requiredIndexGroups = array();
foreach ($file1LineArr as $key=>$l1) {
  $joinFld = array();
  foreach($joins as $j) {
    if ($j->joinType=="REQUIRED") {
      $joinFld[] = trim($l1[$j->fromCol]);
    }
  }
  $requiredIndexGroups[implode("",$joinFld)][]=$key;
}

$secondaryJoins=getSecondaryJoins();
// done this way so that outerloop can be repeated just for 1 set
foreach ($requiredIndexGroups as $lhsReqValueKey=>$indexSet) {
// we loop like this because we need to do a reset and reset doesnt work for foreach
while (list($key, $index) = each($indexSet)) {

  //********** Only redo the ones here that are not matched at all
  if ($file1LineArr[$index]["recon"]["rTO"]->matchStatus!=RECON_NOT_MATCHED) continue;

  $provisional = false;
  if (isset($RHSIndex[$lhsReqValueKey])) {
    foreach ($RHSIndex[$lhsReqValueKey] as $rhsIndex) {
      // never touch perfectly matched
      if ($file2LineArr[$rhsIndex]["recon"]["rTO"]->matchStatus==RECON_PERFECTLY_MATCHED) continue;

      // ... but allow discrepancies to be better matched ...

      // remember that zero is a rating too !
      list($rating,$discrepancy1,$discrepancy2) = getSecondaryMatchRating($secondaryJoins,$file1LineArr[$index],$file2LineArr[$rhsIndex]);

      if ($file2LineArr[$rhsIndex]["recon"]["rTO"]->matchIndex=="-1") {
        $provisional = array("matchAgainstIndex1"=>$index,
                             "matchAgainstIndex2"=>$rhsIndex,
                             "undoIndex1"=>false,
                             "rating"=>$rating,
                             "discrepancyHeaders1"=>$discrepancy1,
                             "discrepancyHeaders2"=>$discrepancy2
                            );
      } else if ($rating > floatval($file2LineArr[$rhsIndex]["recon"]["rTO"]->matchRating)) {
        // wait until end of this set to make sure you have the highest rating
        $provisional = array("matchAgainstIndex1"=>$index,
                             "matchAgainstIndex2"=>$rhsIndex,
                             "undoIndex1"=>$file2LineArr[$rhsIndex]["recon"]["rTO"]->matchIndex,
                             "rating"=>$rating,
                             "discrepancyHeaders1"=>$discrepancy1,
                             "discrepancyHeaders2"=>$discrepancy2
                            );
      }

    } // requiredSet
  }

  if ($provisional!==false) {
    // undo existing
    if ($provisional["undoIndex1"]!==false){
      $rTO = new ReconTO();
      $file1LineArr[$provisional["undoIndex1"]]["recon"]["rTO"] = $rTO;
    }
    // set this
    $file1LineArr[$provisional["matchAgainstIndex1"]]["recon"]["rTO"]->matchStatus = RECON_DISCREPANCY;
    $file1LineArr[$provisional["matchAgainstIndex1"]]["recon"]["rTO"]->matchStatusDescription = "Differs on :".implode(",",$provisional["discrepancyHeaders1"]);
    $file1LineArr[$provisional["matchAgainstIndex1"]]["recon"]["rTO"]->matchRating = $provisional["rating"];
    $file1LineArr[$provisional["matchAgainstIndex1"]]["recon"]["rTO"]->matchIndex = $provisional["matchAgainstIndex2"];
    $file2LineArr[$provisional["matchAgainstIndex2"]]["recon"]["rTO"]->matchStatus = RECON_DISCREPANCY;
    $file2LineArr[$provisional["matchAgainstIndex2"]]["recon"]["rTO"]->matchStatusDescription = "Differs on :".implode(",",$provisional["discrepancyHeaders2"]);
    $file2LineArr[$provisional["matchAgainstIndex2"]]["recon"]["rTO"]->matchRating = $provisional["rating"];
    $file2LineArr[$provisional["matchAgainstIndex2"]]["recon"]["rTO"]->matchIndex = $provisional["matchAgainstIndex1"];
  }

  // rewind this set if any undo was performed
  if (($provisional!==false) && $provisional["undoIndex1"]!==false) {
    reset($indexSet);
  }

}
}

// now try better match the perfectly matched so as to cause whole group to match off if possible
// UPDATE :
// Will not be implemented - This script does not do better "set" matching.

// 3.1 from LHS side set the unmatched if is part of matched set
foreach ($requiredIndexGroups as $indexSet) {
  $hasOthersMatched=false;
  foreach ($indexSet as $index) {
    if ($file1LineArr[$index]["recon"]["rTO"]->matchStatus!=RECON_NOT_MATCHED) {
      $hasOthersMatched=true;
      break;
    }
  }
  foreach ($indexSet as $index) {
    if ($file1LineArr[$index]["recon"]["rTO"]->matchStatus==RECON_NOT_MATCHED) {
      $file1LineArr[$index]["recon"]["rTO"]->matchStatus=(($hasOthersMatched)?RECON_NOT_MATCHED_PARTIAL:RECON_NOT_MATCHED);
    }
  }
}
// 3.2 from RHS side set the unmatched if is part of matched set
$requiredIndex2Groups = array();
foreach ($file2LineArr as $key=>$l2) {
  $joinFld = array();
  foreach($joins as $j) {
    if ($j->joinType=="REQUIRED") {
      $joinFld[] = trim($l2[$j->toCol]);
    }
  }
  $requiredIndex2Groups[implode("",$joinFld)][]=$key;
}
foreach ($requiredIndex2Groups as $indexSet) {
  $hasOthersMatched=false;
  foreach ($indexSet as $index) {
    if ($file2LineArr[$index]["recon"]["rTO"]->matchStatus!=RECON_NOT_MATCHED) {
      $hasOthersMatched=true;
      break;
    }
  }
  foreach ($indexSet as $index) {
    if ($file2LineArr[$index]["recon"]["rTO"]->matchStatus==RECON_NOT_MATCHED) {
      $file2LineArr[$index]["recon"]["rTO"]->matchStatus=(($hasOthersMatched)?RECON_NOT_MATCHED_PARTIAL:RECON_NOT_MATCHED);
    }
  }
}

/***************************************************************
 * Start: FUNCTIONS
 ***************************************************************/
function consolidate($sourceArr, $side) {
  global $joins, $consolidated;

  $arr = $retArr = array();
  foreach ($sourceArr as $key=>$l) {
    $joinFld_NotConsolidatedValue = $joinFld_ConsolidatedIndex = array();
    // for both join types
    foreach($joins as $j) {
      // consolidate can also be used to remove dups
      if ($side=="LHS") {
        if ($j->joinFormat->lhs->consolidate=="N") $joinFld_NotConsolidatedValue[] = trim($l[$j->fromCol]); // store the value
        else $joinFld_ConsolidatedIndex[] = $j->fromCol; // store the index
      } else {
        if ($j->joinFormat->rhs->consolidate=="N") $joinFld_NotConsolidatedValue[] = trim($l[$j->toCol]); // store the value
        else $joinFld_ConsolidatedIndex[] = $j->toCol; // store the index
      }
    }
    if (!isset($arr[implode("",$joinFld_NotConsolidatedValue)])) $arr[implode("",$joinFld_NotConsolidatedValue)]=$l;
    else {
      $arr[implode("",$joinFld_NotConsolidatedValue)]["tempConsolidated"]="Y"; // only set if more than one iteration of it
      foreach ($joinFld_ConsolidatedIndex as $j) {
        if (is_numeric($l[$j])) $arr[implode("",$joinFld_NotConsolidatedValue)][$j]+=intval($l[$j]);
      }
    }
  }

  $i=0; // $a is not numeric
  foreach ($arr as &$a) {
    $isConsolidated = ((isset($a["tempConsolidated"]))?"Y":"N");
    if ($isConsolidated=="Y") {
      $consolidated["{$side}Ndx"][] = $i;
      unset($a["tempConsolidated"]); // make sure it does not get treated as a column
    }
    $retArr[] = $a;
    $i++;
  }

  return $retArr;
}
// only works with toCol (RHS) for time being !
function index($sourceArr) {
  global $joins;

  $retArr = array();
  foreach ($sourceArr as $key=>$l) {
    $joinFldValue = array();
    foreach($joins as $j) {
      if ($j->joinType=="REQUIRED") {

        // you must convert the index with the join conditions otherwise perfect matches wont work when looking up on index from LHS
        // with the exception of converting to numeric with removed spaces
        $joinFldValue[] = applyFormattingRHS($j, $l); // store the value
      }
    }
   $retArr[implode("",$joinFldValue)][]=$key;
  }

  return $retArr;
}
// only works on LHS fromCol for time being !
function getReqKey($line) {
  global $joins;

  $joinFldValue = array();
  foreach($joins as $j) {
    if ($j->joinType=="REQUIRED") {
      // remember :
      // you might need to also change index() function as this is also they key for indexing

      $joinFldValue[] = applyFormattingLHS($j, $line); // store the value
    }
  }
  return implode("",$joinFldValue);
}
function convertMONtoMM($mon) {
  switch (strtoupper($mon)) {
    case "JAN":
      return "01";
    case "FEB":
      return "02";
    case "MAR":
      return "03";
    case "APR":
      return "04";
    case "MAY":
      return "05";
    case "JUN":
      return "06";
    case "JUL":
      return "07";
    case "AUG":
      return "08";
    case "SEP":
      return "09";
    case "OCT":
      return "10";
    case "NOV":
      return "11";
    case "DEC":
      return "12";
    default:
      return $mon;
  }
}
function standardiseDateIfDate($value,$fieldType,$currentFormat) {
  $v = $value;
  if ($fieldType=="DATE") {
    // allow any separator
    if (preg_match("/^([0-9]{2,4}).([0-9]{2,4}).([0-9]{2,4})$/",$v,$parts)) {
      // put into default format of YYYY-MM-DD
      switch ($currentFormat) {
        case "YYYY-MM-DD":
          if (strlen($parts[1])==2) $parts[1] = "20".$parts[1];
          $parts[2] = str_pad($parts[2],2,"0",STR_PAD_LEFT);
          $parts[3] = str_pad($parts[3],2,"0",STR_PAD_LEFT);
          $v = $parts[1]."-".$parts[2]."-".$parts[3];
          break;
        case "YYYY-DD-MM":
          if (strlen($parts[1])==2) $parts[1] = "20".$parts[1];
          $parts[2] = str_pad($parts[2],2,"0",STR_PAD_LEFT);
          $parts[3] = str_pad($parts[3],2,"0",STR_PAD_LEFT);
          $v = $parts[1]."-".$parts[3]."-".$parts[2];
          break;
        case "YYYY-MON-DD":
          if (strlen($parts[1])==2) $parts[1] = "20".$parts[1];
          $parts[2] = convertMONtoMM($parts[2]);
          $parts[3] = str_pad($parts[3],2,"0",STR_PAD_LEFT);
          $v = $parts[1]."-".$parts[2]."-".$parts[3];
          break;
        case "DD-MM-YYYY":
          if (strlen($parts[3])==2) $parts[3] = "20".$parts[3];
          $parts[1] = str_pad($parts[1],2,"0",STR_PAD_LEFT);
          $parts[2] = str_pad($parts[2],2,"0",STR_PAD_LEFT);
          $v = $parts[3]."-".$parts[2]."-".$parts[1];
          break;
        case "DD-MOM-YYYY":
          if (strlen($parts[3])==2) $parts[3] = "20".$parts[3];
          $parts[1] = str_pad($parts[1],2,"0",STR_PAD_LEFT);
          $parts[2] = convertMONtoMM($parts[2]);
          $v = $parts[3]."-".$parts[2]."-".$parts[1];
          break;
        case "MM-DD-YYYY":
          if (strlen($parts[3])==2) $parts[3] = "20".$parts[3];
          $parts[1] = str_pad($parts[1],2,"0",STR_PAD_LEFT);
          $parts[2] = str_pad($parts[2],2,"0",STR_PAD_LEFT);
          $v = $parts[3]."-".$parts[1]."-".$parts[2];
          break;
        default: {
          // leave as-is as it is invalid
        }
      }

    } else {
      // just do a normal string comparison, but we could display an error msg here
    }
  }

  return $v;
}
/* too slow to be used
function getAllRequiredMatched($lineArr1) {
  global $joins, $file2LineArr;

  $subset = array();
  foreach ($file2LineArr as $key=>&$l2) {

    $matchedFailCnt=$matchedSuccessCnt=0;
    foreach($joins as $j) {
      if ($j->joinType=="REQUIRED") {
        $lhsVal = strtoupper(trim(strval($lineArr1[$j->fromCol])));
        $rhsVal = strtoupper(trim(strval($l2[$j->toCol])));

        $lhsVal=standardiseDateIfDate($lhsVal,$j->joinFormat->lhs->fieldType,$j->joinFormat->lhs->dateFormat);
        $rhsVal=standardiseDateIfDate($rhsVal,$j->joinFormat->rhs->fieldType,$j->joinFormat->rhs->dateFormat);

        $lhsVal = (($j->joinFormat->lhs->ignoreLeadingZeros=="Y")?preg_replace("/^0asterisk/","",$lhsVal):$lhsVal);
        $rhsVal = (($j->joinFormat->rhs->ignoreLeadingZeros=="Y")?preg_replace("/^0asterisk/","",$rhsVal):$rhsVal);
        if (
        (strval($lhsVal) !== strval($rhsVal))
        ) {
          $matchedFailCnt++;
          break;
        } else $matchedSuccessCnt++;
      } // end req.
    } // end joins

    if ($matchedFailCnt==0 && $matchedSuccessCnt>0) {
      $l2["recon"]["sourceIndex"] = $key;
      $subset[] = $l2;
    }

  }

  return $subset;
}
*/

// remember :
// you might need to also change index() function as this is also they key for indexing if you change this function below
function applyFormattingLHS($joinConditionIteration, $lineLHS) {
  $lhsVal = strtoupper(trim(strval($lineLHS[$joinConditionIteration->fromCol])));
  $lhsVal=standardiseDateIfDate($lhsVal,$joinConditionIteration->joinFormat->lhs->fieldType,$joinConditionIteration->joinFormat->lhs->dateFormat);
  $lhsVal = (($joinConditionIteration->joinFormat->lhs->ignoreLeadingZeros=="Y")?preg_replace("/^0*/","",$lhsVal):$lhsVal);
  $lhsVal = (($joinConditionIteration->joinFormat->lhs->ignoreSign=="Y" && is_numeric(str_replace(" ","",$lhsVal)))?str_replace(array("-","+"),array("",""),$lhsVal):$lhsVal);
  return $lhsVal;
}
function applyFormattingRHS($joinConditionIteration, $lineRHS) {
  $rhsVal = strtoupper(trim(strval($lineRHS[$joinConditionIteration->toCol])));
  $rhsVal=standardiseDateIfDate($rhsVal,$joinConditionIteration->joinFormat->rhs->fieldType,$joinConditionIteration->joinFormat->rhs->dateFormat);
  $rhsVal = (($joinConditionIteration->joinFormat->rhs->ignoreLeadingZeros=="Y")?preg_replace("/^0*/","",$rhsVal):$rhsVal);
  $rhsVal = (($joinConditionIteration->joinFormat->rhs->ignoreSign=="Y" && is_numeric(str_replace(" ","",$rhsVal)))?str_replace(array("-","+"),array("",""),$rhsVal):$rhsVal);
  return $rhsVal;
}
function applyFormatting($joinConditionIteration, $lineLHS, $lineRHS, &$lhsVal, &$rhsVal) {
  $lhsVal = applyFormattingLHS($joinConditionIteration, $lineLHS);
  $rhsVal = applyFormattingRHS($joinConditionIteration, $lineRHS);
}
function allRequiredJoinsMatch($lineArr1,$lineArr2) {
  global $joins;

  foreach($joins as $j) {
    if ($j->joinType=="REQUIRED") {
      $lhsVal = $rhsVal = "";

      applyFormatting($j, $lineArr1, $lineArr2, $lhsVal, $rhsVal);

      if ((is_numeric(str_replace(" ","",$lhsVal))) && (is_numeric(str_replace(" ","",$rhsVal)))) {
        $lhsVal=floatval(str_replace(" ","",$lhsVal));
        $rhsVal=floatval(str_replace(" ","",$rhsVal));
      }

      if (
      (strval($lhsVal) !== strval($rhsVal))
      ) {
        return false;
      }
    } // end req.
  } // end joins loop

  return true;
}
function allSecondaryJoinsMatch($lineArr1,$lineArr2) {
  global $joins;

  foreach($joins as $j) {
    if ($j->joinType=="SECONDARY") {

      applyFormatting($j, $lineArr1, $lineArr2, $lhsVal, $rhsVal);

      if ((is_numeric(str_replace(" ","",$lhsVal))) && (is_numeric(str_replace(" ","",$rhsVal)))) {
        $lhsVal=floatval(str_replace(" ","",$lhsVal));
        $rhsVal=floatval(str_replace(" ","",$rhsVal));
      }

      if (
      (strval($lhsVal) !== strval($rhsVal))
      ) {
        return false;
      }
    } // end req.
  } // end joins loop

  return true;
}
function getSecondaryMatchRating($secondaryJoins,$lineArr1,$lineArr2) {
  global $hdr1, $hdr2;
  $rating = 0;
  $discrepancies1 = $discrepancies2 = array();
  foreach($secondaryJoins as $j) {

      applyFormatting($j, $lineArr1, $lineArr2, $lhsVal, $rhsVal);

      if ((is_numeric(str_replace(" ","",$lhsVal))) && (is_numeric(str_replace(" ","",$rhsVal)))) {
        $lhsVal=floatval(str_replace(" ","",$lhsVal));
        $rhsVal=floatval(str_replace(" ","",$rhsVal));
      }

      if (
      (strval($lhsVal) === strval($rhsVal))
      ) {
        $rating += 1 / $j->priority;
      } else {
        $discrepancies1[] = $discrepancies2[] = ((!$hdr1)?$j->fromCol:$hdr1[$j->fromCol]);
        // $discrepancies2[] = ((!$hdr2)?$j->toCol:$hdr2[$j->toCol]); // cant use this as the summary will create a 2nd grouping if col headers different
      }
  } // end joins loop

  return array($rating,$discrepancies1,$discrepancies2);
}
function convertLinesToArray($fileLineArray, &$side) {

  $arr = array();
  foreach ($fileLineArray as $l) {

    if (trim(str_replace(",", "", $l))=="") continue; // ignore blank lines

    $lineArr = str_getcsv(
                          $l, # Input line
                          ',',   # Delimiter
                          '"',   # Enclosure
                          '\\'   # Escape char
                      );

    $side = max(count($lineArr),$side);

    $arr[] = $lineArr;

  }
  return $arr;
}
function convertLineToArray($fileline) {
  $arr = array();

  $arr = str_getcsv(
      $fileline, # Input line
      ',',   # Delimiter
      '"',   # Enclosure
      '\\'   # Escape char
      );
  return $arr;
}
// make sure each line has same number of cols
function standardiseCols() {
  global $file1LineArr, $file2LineArr, $lhsColCnt, $rhsColCnt;
  foreach ($file1LineArr as &$row) {
    for ($i=count($row)+1; $i<=$lhsColCnt; $i++) {
      $row[]="Line Incomplete";
    }
  }
  unset ($row);
  foreach ($file2LineArr as &$row) {
    for ($i=count($row)+1; $i<=$rhsColCnt; $i++) {
      $row[]="Line Incomplete";
    }
  }
  unset ($row);
}
function getSecondaryJoins() {
  global $joins;

  $arr = array();
  foreach($joins as $j) {
    if ($j->joinType=="SECONDARY") {
      $arr[] = $j;
    }
  }

  return $arr;
}
function sanitiseAfterStripTags($s) {
  return trim(
            preg_replace("/\n[ ]*/","\n",
                preg_replace("/[\x0A]+/","\n",
                    preg_replace("/\x0D/","\n",
                        preg_replace("/[ ]{2,}/"," ",
                            strip_tags($s)
                        )
                    )
                )
            )
        );
}

/***************************************************************
 * End: FUNCTIONS
***************************************************************/

echo "<a id='saveData' href='#' download='Recon_Report.csv' onclick='javascript:exportToCSV();'>Export to CSV</a>";
/****************************************************************************
 * Start of Output to Browser
 ****************************************************************************/

$summary = array(RECON_PERFECTLY_MATCHED => array("count"=>0),
                 RECON_DISCREPANCY => array("count"=>0, "sub"=>array()),
                 RECON_NOT_MATCHED => array("countLHS"=>0,"countRHS"=>0),
                 RECON_NOT_MATCHED_PARTIAL => array("countLHS"=>0,"countRHS"=>0)
                );
foreach ($file1LineArr as $l) {
  if (in_array($l["recon"]["rTO"]->matchStatus,array(RECON_NOT_MATCHED,RECON_NOT_MATCHED_PARTIAL)))
    $summary[$l["recon"]["rTO"]->matchStatus]["countLHS"] += 1;
  else $summary[$l["recon"]["rTO"]->matchStatus]["count"] += 1;

  if ($l["recon"]["rTO"]->matchStatus==RECON_DISCREPANCY) {
    if (!isset($summary[$l["recon"]["rTO"]->matchStatus]["sub"][$l["recon"]["rTO"]->matchStatusDescription])) {
      $summary[$l["recon"]["rTO"]->matchStatus]["sub"][$l["recon"]["rTO"]->matchStatusDescription]["count"] = 1;
    }
    else {
      $summary[$l["recon"]["rTO"]->matchStatus]["sub"][$l["recon"]["rTO"]->matchStatusDescription]["count"] += 1;
    }
  }
}
foreach ($file2LineArr as $l) {
  if (!in_array($l["recon"]["rTO"]->matchStatus,array(RECON_NOT_MATCHED,RECON_NOT_MATCHED_PARTIAL))) continue;
  $summary[$l["recon"]["rTO"]->matchStatus]["countRHS"] += 1;
}

echo "<div class='rounded-corners' style='text-align:center;width:100%;background-color:#dbe4ef; padding:10px;' >
        <span style='font-size:14px;color:#004470;font-weight:bold;'>Summary</span><br><br>
        <div class='rounded-corners' style='width:500px;text-align:center;background-color:white;padding:10px;' >
        <table style='border:0;'>";

foreach ($summary as $key=>$s) {
   if (in_array($key,array(RECON_NOT_MATCHED,RECON_NOT_MATCHED_PARTIAL))) {
     echo "<tr><td class='summary-lvl1'>{$key}</td><td class='summary-lvl1'>".($s["countLHS"]+$s["countRHS"])."</td></tr>";
     echo "<tr><td class='summary-lvl2'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;LHS</td><td class='summary-lvl2'>{$s["countLHS"]}</td></tr>";
     echo "<tr><td class='summary-lvl2'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;RHS</td><td class='summary-lvl2'>{$s["countRHS"]}</td></tr>";
   } else echo "<tr><td class='summary-lvl1'>{$key}</td><td class='summary-lvl1'>{$s["count"]}</td></tr>";

   if ($key==RECON_DISCREPANCY) {
     foreach ($s["sub"] as $key2=>$s2) {
       echo "<tr><td class='summary-lvl2'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$key2}</td>
             <td class='summary-lvl2'>{$s2["count"]}</td></tr>";
     }
   }
}

echo "  </table>
      </div>
      </div><br><br>";

// Detailed offset against each other
echo "<table>";
$colCnt1=$colCnt2=0;
foreach ($file1LineArr[0] as $key=>$f) {
  if (strval($key)=="recon") continue;
  // sometimes the header row has fewer cols that the data row so ignore
  if (isset($configs->colConfigs->lhs[$key])) {
    if ($configs->colConfigs->lhs[$key]->displayOnResult!="Y") continue;
  }
  $colCnt1++;
}

foreach ($file2LineArr[0] as $key=>$f) {
  if (strval($key)=="recon") continue;
  // sometimes the header row has fewer cols that the data row so ignore
  if (isset($configs->colConfigs->rhs[$key])) {
    if ($configs->colConfigs->rhs[$key]->displayOnResult!="Y") continue;
  }
  $colCnt2++;
}
// output headers
$csv = array();
$temp = array();
echo "<tr>";
if ($hdr1!==false) {
  foreach ($hdr1 as $key=>$h ) {

    if ($configs->colConfigs->lhs[$key]->displayOnResult!="Y") continue;
    echo "<th>{$h}</th>";
    $temp[] = $h;
  }
} else {
  echo "<th colspan='{$colCnt1}'>&nbsp;</th>";
  for ($i=1; $i<=$colCnt1; $i++) {
    $temp[] = "";
  }
}


echo "<th>&nbsp;</th>";
$temp[]="";

if ($hdr2!==false) {
  foreach ($hdr2 as $key=>$h ) {

    if ($configs->colConfigs->rhs[$key]->displayOnResult!="Y") continue;
    echo "<th>{$h}</th>";
    $temp[] = $h;
  }
} else {
  echo "<th colspan='{$colCnt2}'>&nbsp;</th>";
  for ($i=1; $i<=$colCnt2; $i++) {
    $temp[] = "";
  }
}

echo "</tr>";

$csv[] = $temp;

// output body
$class='odd';

foreach ($file1LineArr as $f1Key=>$f1) {
  $temp = array();

  echo "<tr class='".GUICommonUtils::styleEO($class)."'>";

  foreach ($f1 as $key=>$f) {

    if (strval($key)=="recon") continue;
    // sometimes the header row has fewer cols that the data row so ignore
    if (isset($configs->colConfigs->lhs[$key])) {
      if ($configs->colConfigs->lhs[$key]->displayOnResult!="Y") continue;
      echo "<td>{$f}</td>";
      $temp[] = $f;
    }
  }

  $sides=array();
  if (in_array($f1Key,$consolidated["LHSNdx"])) $sides[]="LHS";
  if (in_array($f1["recon"]["rTO"]->matchIndex,$consolidated["RHSNdx"])) $sides[]="RHS";
  $consolidatedStr = ((count($sides)>0)?"<span style='color:orange;'>* ".(implode(",",$sides))." has been consolidated</span><br>":"");
  $dividerText = "<td style='background-color:#E5E5E5;'>
                    {$consolidatedStr}
                    <i>{$f1["recon"]["rTO"]->matchStatus}<br>
                    <span style='color:#70b440;font-size:9px;'>{$f1["recon"]["rTO"]->matchStatusDescription}</span>
                    </i>
                  </td>";
  echo $dividerText;
  $temp[] = sanitiseAfterStripTags($dividerText);

  // show matching counterpart from RHS
  if ($f1["recon"]["rTO"]->matchIndex!="-1") {
    foreach ($file2LineArr[$f1["recon"]["rTO"]->matchIndex] as $key=>$f) {

      if (strval($key)=="recon") continue;
      // sometimes the header row has fewer cols that the data row so ignore
      if (isset($configs->colConfigs->rhs[$key])) {
        if ($configs->colConfigs->rhs[$key]->displayOnResult!="Y") continue;
        echo "<td>{$f}</td>";
        $temp[] = $f;
      }
    }
  } else {
    echo "<td colspan='{$colCnt2}'></td>";
    for ($i=1; $i<=$colCnt2; $i++) {
      $temp[] = "";
    }
  }

  echo "</tr>";

  $csv[] = $temp;
}

// Show unmatched RHS

foreach ($file2LineArr as $key=>$f2) {
  $temp = array();

  if (strval($f2["recon"]["rTO"]->matchStatus!=RECON_NOT_MATCHED) &&
      strval($f2["recon"]["rTO"]->matchStatus!=RECON_NOT_MATCHED_PARTIAL)) continue;

  echo "<tr class='".GUICommonUtils::styleEO($class)."'>";
  echo "<td colspan='{$colCnt1}'>&nbsp;</td>";
  for ($i=1; $i<=$colCnt1; $i++) {
    $temp[] = "";
  }

  $consolidatedStr = ((in_array($key,$consolidated["RHSNdx"]))?"<span style='color:orange;'>* RHS has been consolidated</span><br>":"");
  $dividerText = "<td style='background-color:#E5E5E5;'>
                    {$consolidatedStr}
                    <i>{$f2["recon"]["rTO"]->matchStatus}<br>
                    <span style='color:#70b440;font-size:9px;'>{$f2["recon"]["rTO"]->matchStatusDescription}</span>
                    </i>
                  </td>";
  echo $dividerText;
  $temp[] = sanitiseAfterStripTags($dividerText);

  foreach ($f2 as $key=>$f) {

    if (strval($key)=="recon") continue;
    if ($configs->colConfigs->rhs[$key]->displayOnResult!="Y") continue;
    echo "<td>{$f}</td>";
    $temp[] = $f;
  }

  echo "</tr>";
  $csv[] = $temp;
}

echo "</table>";

echo "</body>
      </html>";

?>

<script type='text/javascript' language='javascript' src='".$DHTMLROOT.$PHPFOLDER."js/underscore.js'></script>
<script type='text/javascript' language='javascript' src='".$DHTMLROOT.$PHPFOLDER."js/moment.min.js'></script>


<script type='text/javascript' language='javascript' defer>

/**
 * Converts a datasource's view to CSV and saves it using data URI.
   * Uses underscore for collection manipulation http://underscorejs.org/
 * Uses moment.js for date parsing (you can change this if you would like)
 * TODO save it using Downloadify to save the file name https://github.com/dcneiner/Downloadify
 * @param {Array.<Object>} data The data to convert.
 * @param {boolean} humanize If true, it will humanize the column header names.
 * It will replace _ with a space and split CamelCase naming to have a space in between names -> Camel Case
 * @param {Array.<String>} ignore Columns to ignore.
 * @returns {string} The csv string.
 */
var toCSV = function (data, fileName, humanize, ignore) {

  var csv = '';
  if (!ignore) {
      ignore = [];
  }

  //ignore added datasource properties
  ignore = _.union(ignore, ["_events", "idField", "_defaultId", "constructor", "init", "get",
            "_set", "wrap", "bind", "one", "first", "trigger", "unbind", "uid", "dirty", "id", "parent" ]);

  //add the header row - commented out as we add the header manually as 1st row - this here uses the index as the header
  /*
  if (data.length > 0) {
      for (var col in data[0]) {
          //do not include inherited properties
          if (!data[0].hasOwnProperty(col) || _.include(ignore, col)) {
            continue;
          }

          // humanize changes content into more readable format such as camelCaps has a space inserted in the middle with first letter capital
          if (humanize) {
            col = col.split('_').join(' ').replace(/([A-Z])/g, ' $1');
          }

          col = col.replace(/"/g, '""');
          csv += '"' + col + '"';
          if (col != data[0].length - 1) {
            csv += ",";
          }
      }
      csv += "\n";
  }
*/
  //add each row of data
  for (var row in data) {
      for (var col in data[row]) {
          //do not include inherited properties
          if (!data[row].hasOwnProperty(col) || _.include(ignore, col)) {
              continue;
          }

          var regEx_num = /^\d+$/;
          var value = data[row][col];
          if (value === null) {
              value = "";
          } else if (value instanceof Date) {
              value = moment(value).format("MM/D/YYYY");
          } else if (regEx_num.test(value)) {
            // stop Excel opening up csv file and converting to Scientific Notation
              value = "=\""+value.toString()+"\""; // could also use =TEXT(int,0)
          } else {
              value = value.toString();
          }

          value = value.replace(/"/g, '""');
          csv += '"' + value + '"';
          if (col != data[row].length - 1) {
              csv += ",";
          }
      }
      csv += "\n";
  }

    //TODO replace with downloadify so we can get proper file naming
    //window.open("data:text/csv;charset=utf-8," + escape(csv));
    // - u could use the above window.open command to open a window and stream data to it using URI, but there is no
    //   way to choose the filename, so recently browsers started supporting the <a> download functionality which does support filenames

    var content = escape(csv);
    if (content.length>200000) alert('The data exceeds 200000 characters. Some browsers limit the data uri length.');

    $("#saveData").attr('href',"data:text/csv;charset=utf-8," + escape(csv));
    $("#saveData").find('span').trigger('click');

    // cant use dynamic below as firefox doesnt support this for a download link
/*
    var link = document.createElement('a');
    link.download = 'Recon_Report.csv';
    link.href = 'data:text/csv;charset=utf-8,' + escape(csv);
    link.click();
    */

}

var exportToCSV = function() {
  var dataTbl=<?php echo json_encode($csv); ?>;
  toCSV(dataTbl, "RFID_Report.csv", true, []);
}

</script>