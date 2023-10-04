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

set_time_limit(500);

// $basedir="C:/www/ftp/";
$basedir="C:/www/live/archives/";
$tree=array();
$highlightedTree=array();
$fCnt=0;

// params
$delete=true;
$echoDeleteNames=true;


// will delete folders AND files !!
$deleteCriteriaFolders=array("/2011/",
                             "/2012/",
                             "/2013/",
                             "/2014/");
// will only delete files
$deleteCriteriaFilesOnly=array("/sgx/fromsgx/LOADCON*",
                               "/sgx/fromsgx/RTT_ACT*");
$deleteCriteriaFilesOnly_converted = convertFilenameArrayToDirnameArray();


parseFolder($basedir, $tree);
parseForHighlighting($tree, $highlightedTree);

function parseFolder($basedir, &$treeNode) {
  global $fCnt, $delete, $echoDeleteNames, $deleteCriteriaFolders, $deleteCriteriaFilesOnly;

  $fCnt++;
  if ($fCnt>9999) {
    echo "Folder Limit Reached";
    return;
  }

  $handle = opendir($basedir);
  if ($handle===false) {
    echo "Invalid Directory";
    return;
  }

  $regexFolders="/(".str_replace("/","\/",implode("|",$deleteCriteriaFolders)).")/i";
  $regexFiles="/(".str_replace(array("/",".","*"),array("\/","[.]",".*"),implode("|",$deleteCriteriaFilesOnly)).")/i";

  while (false !== ($file = readdir($handle))) {
    if (in_array($file,array(".",".."))) continue;

    $bd=$basedir.$file."/";

    if (is_dir($bd)) {
      $treeNode[$bd]["files"]=0;
      parseFolder($bd, $treeNode[$bd]);
      // remove folder after all files have been removed
      if (preg_match($regexFolders,$basedir)) {
         if ($echoDeleteNames) echo "remove folder : ".$basedir.$file."<br>";
         if ($delete) rmdir(preg_replace("/\/$/","",$bd)); // remove the trailing slash
      }
    } else {
      $treeNode["files"]++;
      // remove files
      if (
          // file criteria incl folder name so compare to full name
          (preg_match($regexFiles,$bd)) ||
          // else
          (preg_match($regexFolders,$basedir))
         ) {
        if ($echoDeleteNames) echo "remove file : ".$basedir.$file."<br>";
        if ($delete) unlink(preg_replace("/\/$/","",$bd)); // remove the trailing slash
      }
    }

    // send results immediately
    ob_flush();
    ob_implicit_flush(false);
    flush();
    ob_end_flush();
  }

}

function parseForHighlighting($treeNode, &$highlightedTreeNode) {
  global $deleteCriteriaFolders, $deleteCriteriaFilesOnly_converted;

  $regexFolders="/(".str_replace("/","\/",implode("|",$deleteCriteriaFolders)).")/i";
  $regexFiles="/(".str_replace("/","\/",implode("|",$deleteCriteriaFilesOnly_converted)).")$/i";

  // cannot modify and parse same loop otherwise endless loop results

  foreach ($treeNode as $key=>$node) {
    if ($key=="files") {
      // highlight file count
      if ($node>1000) {
        $highlightedTreeNode[$key]="<span style='background-color:red; color:white;'>".$node."</span>";
      } else {
        $highlightedTreeNode[$key]=$node;
      }
      $hKey=$key;
    } else {
      // highlight folder first
      if (preg_match($regexFolders,$key)) {
        $hKey="<span style='background-color:green; color:white;'>".$key."</span>";
        $highlightedTreeNode[$hKey]="";
      }
      // highlight files next, altho not listed - not very reliable way of doing this below as it depends on path being supplied in filename
      else if (preg_match($regexFiles,$key)) {
        $hKey="<span style='background-color:DarkCyan; color:white;'>".$key."</span>";
        $highlightedTreeNode[$hKey]="";
      }
      else {
        $hKey=$key;
        $highlightedTreeNode[$hKey]="";
      }
      parseForHighlighting($node,$highlightedTreeNode[$hKey]);
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

echo "<pre>";
print_r($highlightedTree);

?>
<script type="text/javascript">
</script>