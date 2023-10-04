<?php

      $principalList = ((isset($_GET["PRINCIPALLIST"]))?$_GET["PRINCIPALLIST"]:"");
      $wareHouseList = ((isset($_GET["WAREHOUSLIST"]))?$_GET["WAREHOUSLIST"]:"");
      $dInterval     = ((isset($_GET["DINTERVAL"]))?$_GET["DINTERVAL"]:"");



echo "This Works";
echo "<br>";
echo $principalList;
echo "<br>";
echo $wareHouseList ;
echo "<br>";
echo $dInterval;

?>