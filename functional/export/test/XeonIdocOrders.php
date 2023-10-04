<?php

// https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/functional/export/test/XeonIdocOrders.php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');

echo "<br>";
echo "START";


    include($ROOT.$PHPFOLDER.'/functional/export/test/XeonIdocOrdersTemplate.php');
echo "<br>";    
    echo $orderHeaderXMl;
echo "<br>";    
    
    
        $orderHeaderXMla = str_replace(array("&&file_seq_num&&"),
                                      array('100000012345'),
                                      $orderHeaderXMl);
        
        $dataArray = Array();
        
        $dataArray = join("\r\n",$orderHeaderXMl);
        
        PRINT_R($dataArray);
        
        file_put_contents('test.txt', $orderHeaderXMla, FILE_APPEND);
                                      
echo "<br>";
echo "End";

?>