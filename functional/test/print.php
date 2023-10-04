<?php
    
    // https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/functional/test/print.php

    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');

    $out = '';
    ob_start();
    echo '<h1>Picking Slip</h1>';
    $out .= ob_get_contents();
    ob_flush();
    echo '<br>';
    echo '<p> This will be the store name<br></p>';
    $out .= ob_get_contents();
    ob_end_flush();
    // check that something was actually written to the buffer
    if (strlen($out) > 0) {
        $file = $ROOT . 'archives/prints/' . time() . '.html';
        touch($file); 
        $fh = fopen($file, 'w');
        fwrite($fh, $out);
        fclose($fh);
    }
?>
