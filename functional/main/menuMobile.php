<?php

// get the overhead top level services
// Roles can be loaded under multiple principals, so is necessary to do distinct select this way otherwise rows are duplicated on menu
//the below is a fix to allow for multiple row checks for a given menu item. This is in preference to redesigning the table ERD, but the tradeoff
// is that roles must be hardcoded if alternative 2ndary exists to check for.
// the roles are concatenated with a comma separator.


echo '<div id="thirdpane" class="menu_list">';
echo '<div id="lvl2popup" ></div>';
echo '<a href="javascript:parent.change_iframe_content(\''.$ROOT.'m/home.php\');" class="icon-home fleft" title="Home"></a>';
echo '<a href="javascript:window.location.replace(\''.HOST_SURESERVER_AS_NEWUSER.'m/desktop.php\');" class="icon-desktop fleft" style="margin-left:30px;" title="Full Desktop Version"></a>';
echo '<a href="javascript:window.location.replace(\''.HOST_SURESERVER_AS_NEWUSER.$PHPFOLDER.'logout.php\');" class="icon-logout fleft" style="margin-left:30px;" title="Logout"></a>';

echo '<div class="fclear"></div>';
echo '</div>';

?>
