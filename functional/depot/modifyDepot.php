<?php

include_once ('ROOT.php');
include_once ($ROOT . 'PHPINI.php');
include_once ($ROOT . $PHPFOLDER . 'functional/main/access_control.php');
include_once ($ROOT . $PHPFOLDER . 'libs/common.php');
include_once ($ROOT . $PHPFOLDER . 'libs/GUICommonUtils.php');
include_once ($ROOT . $PHPFOLDER . 'DAO/DepotDAO.php');


if (!isset($_SESSION)) session_start;
$principalId = $_SESSION['principal_id'];
$userId = $_SESSION["user_id"];


//Database Connection
$dbConn = new dbConnect();
$dbConn->dbConnection();


$postDMLType = (isset($_POST['DMLTYPE']) && in_array($_POST['DMLTYPE'], array('INSERT', 'UPDATE', 'VIEW'))) ? $_POST['DMLTYPE'] : 'VIEW';
$divAjaxDepotDetails="ajaxDepotDetails";


#--------------------------------------------------------------------------------------------------------------------------
/*
 *	START DEPOT MODIFY DROPDOWN LIST.
 */
#--------------------------------------------------------------------------------------------------------------------------


    echo '<BR>';
    echo '<FORM action="" method="post">';
    echo '<TABLE width="450" border="0">';
    echo '<thead><tr>';
   // echo '<th >';

    echo '<th bgcolor="#87CEFA">Select Depot to ', mb_convert_case($postDMLType, MB_CASE_TITLE), '</Th><Th>';


    //Build Depot -> DD
    $depotDAO = new DepotDAO($dbConn);
    $depotsArr = $depotDAO->getAllDepotsArray();  //a.uid, a.code, a.name depot_name

    echo '<SELECT name="" onChange="refreshDepot(this.options[this.selectedIndex].value)">';
      echo '<option value="" style="color:#999">No Depot Selected...</option>';
    foreach($depotsArr as $depotItem){
      echo '<option value="',$depotItem['uid'],'">',$depotItem['code'],' - ',$depotItem['depot_name'],'</option>';
    }
    echo '</SELECT>';

    echo '</th>';
    echo '</tr></thead>';
    echo '</TABLE>';
    echo '</FORM>';

    //Div AJAX writes too.
    echo '<div id="',$divAjaxDepotDetails,'"></div>';

#--------------------------------------------------------------------------------------------------------------------------


$dbConn->dbClose();


?>

<script type="text/javascript" >
	function refreshDepot(DEPOTID) {
	DEPOTID = (DEPOTID===false) ? ("") : ("&DEPOTID="+DEPOTID);
	AjaxRefresh("USERID=<?php echo $userId ?>&DMLTYPE=<?php echo $postDMLType ?>"+DEPOTID+"",
			"<?php echo $ROOT.$PHPFOLDER ?>functional/depot/depotForm.php",
		    "<?php echo $divAjaxDepotDetails ?>",
		    "Please wait whilst page is refreshed...",
		    "");
	}
</script>
