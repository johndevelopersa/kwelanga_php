<?php
// https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/functional/ws/api/uploadJson.php

$jString =  file_get_contents("https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/log/pricefileb230706a.txt.txt");

$priceData = json_decode($jString, true); 

//echo $jString;

//print_r($priceData);

foreach($priceData as $prcRow) {
	
	echo "<pre>";
	print_r($prcRow);
	echo "<br>";
	
	
}

echo "PP";


?>    