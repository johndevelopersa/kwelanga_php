<?php
/*
 * This script will either list or delete folder structures on the server.
 * Simply set the delete flag to true and the deleteCriteria.
 *
 * ALWAYS !! Test first with $deleted=false to see which rows will be deleted
 *          green = entire folder and subfolders
 *          darkcyan = only files within that subfolder
 *          red = file counts exceeding 1000
 */
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');

set_time_limit(1000);

// $basedir=array(DIR_DATA_SURESERVER_NON_FTP_FROM,
$basedir=array(
              $ROOT."archives/"
              );
$fCnt=0;

echo "<style>
      td {border-bottom:1; border-bottom-style:dotted; border-color:gray;}
      </style>
      <table>";
foreach ($basedir as $bd) {
  $tree=array();
  echo "<tr><th colspan=2>
        **************************************************************************************************************<br>
        <span style='background-color:black; color:white;'>Now parsing Location: ".$bd."</span><br>
        **************************************************************************************************************
        </th></tr>";

  // send results immediately
  ob_flush();
  ob_implicit_flush(false);
  flush();
  ob_end_flush();

  parseFolder($bd, $tree);
  parseForHighlighting($tree, $bd);
}
echo "</tr></table>";

function parseFolder($basedir, &$treeNode) {
  global $fCnt;

  $fCnt++;
  /*
  if ($fCnt>2000) {
    echo "Folder Limit Reached";
    throw new Exception();
  }
  */

  $handle = opendir($basedir);
  if ($handle===false) {
    echo "Invalid Directory";
    return;
  }

  while (false !== ($file = readdir($handle))) {
    if (in_array($file,array(".",".."))) continue;

    $bd=$basedir.$file."/";

    if (is_dir($bd)) {
      $treeNode[$bd]["files"]=0;
      parseFolder($bd, $treeNode[$bd]);
    } else {
      $treeNode["files"]++;
    }
  }

}

function parseForHighlighting($treeNode, $basedir) {
  foreach ($treeNode as $key=>$node) {
    if ($key=="files") {
      // highlight file count
      if ($node>1000) {
        $bgC="red";
        $tC="white";
      } else if ($node>500) {
        $bgC="yellow";
        $tC="red";
      } else if ($node>100) {
        $bgC="blue";
        $tC="white";
      } else {
        $bgC="white";
        $tC="green";
      }
      echo "<td><span style='background-color:{$bgC}; color:{$tC};'>files: ".$node."</span></td></tr>";
    } else {
      echo "<tr><td><span style='background-color:white; color:#CCCCCC;'>".substr($key,strlen($basedir))."</span></td>";
      parseForHighlighting($node,$basedir);
    }

    // send results immediately
    ob_flush();
    ob_implicit_flush(false);
    flush();
    ob_end_flush();
  }
}

function convertFilenameArrayToDirnameArray() {
  global $deleteCriteriaFilesOnly;
  $arr = array();
  foreach ($deleteCriteriaFilesOnly as $file) {
    $arr[] = dirname($file)."/";
  }
  return $arr;
}

// echo "<pre>";
// print_r($highlightedTree);

?>
<script type="text/javascript">
</script>