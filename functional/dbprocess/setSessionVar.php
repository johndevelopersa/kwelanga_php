<?php
$postVAL = ((isset($_GET["val"]))?$_GET["val"]:"ON");

if (!isset($_SESSION)) session_start();

$_SESSION["SMSALERT"] = $postVAL;
print_r($_SESSION);
?>