<?php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');

// ----------------------------------------- //
//                CSS HANDLER                //
// ----------------------------------------- //
//                                           //
//      builds styles and common styles      //
//                                           //
// ----------------------------------------- //



//headers
header ("content-type: text/css; charset: UTF-8");


ob_start("compress");
function compress($buffer) {
  $buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer); /* remove comments */
  $buffer = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $buffer);  /* remove tabs, spaces, newlines, etc. */
  return $buffer;
}



// ----------------------------------------- //
//        COMMON CSS - OVERRIDE PREVIOUS     //
// ----------------------------------------- //
/* your css files */
include('uipopup_min.css');
// ----------------------------------------- //



// ----------------------------------------- //
//        SYSTEM CSS - OVERRIDE PREVIOUS     //
// ----------------------------------------- //
if(isset($_GET['SYSID']) && isset($_GET['SYSNAME']) && is_file($_GET['SYSID'].'_'.$_GET['SYSNAME'].'.css')){

  /* system */
  include($_GET['SYSID'].'_'.$_GET['SYSNAME'].'.css');
  include($_GET['SYSID'].'_'.'menu.css');  

} else {  //revert...

  /* default */
  include('1_kwelanga.css');
 

}
//  ----------------------------------------- //

ob_end_flush();
