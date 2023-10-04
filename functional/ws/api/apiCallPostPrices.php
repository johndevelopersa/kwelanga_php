<?php
// $callresult = "https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/functional/ws/api/apiCallPostPrices.php";

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once $ROOT . $PHPFOLDER . 'properties/Constants.php';
include_once $ROOT . $PHPFOLDER . 'libs/storage/Storage.php';

//rather store large files outside of git!

new Storage(S3_ACCESS_ID, S3_SECRET_KEY, false, S3_ENDPOINT, S3_REGION);

$priceRequestS3 = "s3://kos.storage.cpt/archives/api/bulk_async_prices/450/2023/07/18/request_230718075642_000974_f64e8886ecca899fe12fe28523a8f04d.json";

$s3FilePartsArr = parse_url($priceRequestS3);
if (!isset($s3FilePartsArr['scheme']) || !$s3FilePartsArr['scheme'] === "s3") {
    echo "URI invalid";
    var_dump($priceRequestS3);
    var_dump($s3FilePartsArr);
}

//fetch the price update file
$priceUpdateFile = Storage::getObject($s3FilePartsArr['host'], $s3FilePartsArr['path']);
if(!$priceUpdateFile){
    echo "missing s3 file: $priceUpdateFile";
    exit;
}

//s3://kos.storage.cpt/archives/api/bulk_async_prices/450/2023/07/18/request_230718075642_000974_f64e8886ecca899fe12fe28523a8f04d.json
$payload = $priceUpdateFile->body;

echo "<h4>Data Array</h4>";
echo "<pre>";
#print_r($payload);
echo "<br>";
echo "</pre>";
echo "<hr>";

echo "<h4>Json Payload Size:</h4>";
echo strlen($payload) . " bytes";
echo "<br>";
echo "<br>";
echo "<hr>";

#die();

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/m/e/kKre3CSva3E/chariii.php");

#curl_setopt($ch, CURLOPT_URL, "http://localhost:8080/systems/kwelanga_system/m/e/kKre3CSva3E/chariii.php");

curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
curl_setopt($ch, CURLOPT_VERBOSE, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$result = curl_exec($ch);

$curlDebug = curl_getinfo($ch);

//echo "<br><pre>";
print_r(curl_getinfo($ch));

if (curl_errno($ch)) {
    $error_msg = curl_error($ch);
    print_r($error_msg);
}

echo "<br>";
echo "<h4>Result</h4>";
print_r($result);
echo "<br><br><hr><br><br>";

//print_r(json_decode($result, true ));

// close cURL resource, and free up system resources
curl_close($ch);


?>