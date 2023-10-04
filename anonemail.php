<?php

include_once('ROOT.php');
include_once($ROOT.'PHPINI.php');
include_once($ROOT . $PHPFOLDER . 'libs/BroadcastingUtils.php');

BroadcastingUtils::sendTestEmail();