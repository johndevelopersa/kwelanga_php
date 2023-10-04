<?php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');


$ADDITIONALTYPE = ((isset($_GET["ADDITIONALTYPE"]))?$_GET["ADDITIONALTYPE"]:"");

$DOCMASTID = ((isset($_GET["DOCMASTID"]))?$_GET["DOCMASTID"]:"");

$_GET['TYPE'] = 'DOCUMENT';
$_GET['ADDITIONALTYPE'] = $ADDITIONALTYPE;
$_GET['FINDNUMBER'] = $DOCMASTID;
$_GET['CSOURCE'] = 'T';

header('Location: ' . $ROOT.$PHPFOLDER.'functional/presentations/presentationManagement.php?'.http_build_query($_GET));

// header('Location: ' . $ROOT.$PHPFOLDER.'functional/presentations/presentationHandler.php?'.http_build_query($_GET));

return;


