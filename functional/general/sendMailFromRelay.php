<?php
/*
 * This is just used to send myself an email from local machine (or Mobile MAP) where the local server
 * is not configured to send mail
 */
$postPWD = ((isset($_GET["USERPWD"]))?$_GET["USERPWD"]:false);
$postSUBJ = ((isset($_GET["SUBJECT"]))?$_GET["SUBJECT"]:false);
$postMSG = ((isset($_GET["MESSAGE"]))?$_GET["MESSAGE"]:false);


if (md5("X".gmdate("Ymd"))!=$postPWD) {
  echo "Failed Validation";
  exit;
}

$to = "brett.stoop@chep.com,grayson.govender@chep.com";
$from = "noreply@nodomain.com";
$headers = "From:" . $from;
$result = mail($to,$postSUBJ,$postMSG,$headers);

echo "Result: ".(($result)?"OK":"FAILED");
?>