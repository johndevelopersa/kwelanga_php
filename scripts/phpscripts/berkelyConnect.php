<?php
// https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/scripts/phpscripts/berkelyConnect.php

$serverName = "berkley.dyndns.org:1600";
    $connectionInfo = array( "Database"=>"CustomData", "UID"=>"sa", "PWD"=>"C0mpu5y5");
    $conn = sqlsrv_connect( $serverName, $connectionInfo);
    if( $conn === false )
    {
        echo "Could not connect.\n";
        print('<pre>');
        die( print_r( sqlsrv_errors(), true));
        print('</pre>');
    } else {
    	echo "Connected";
    }
?>    