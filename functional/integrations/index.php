<?php

  include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
  require($ROOT.$PHPFOLDER."functional/main/access_control.php");
  include_once($ROOT.$PHPFOLDER.'libs/common.php');
  include_once($ROOT.$PHPFOLDER."libs/GUICommonUtils.php");
  include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
  require __DIR__ . '/IntegrationDAO.php';

  error_reporting(-1);
  ini_set('display_errors', 1);
	
  if (!isset($_SESSION)) session_start() ;
  $principalId = $_SESSION['principal_id'] ;
  $principalName = $_SESSION['principal_name'] ;
  $userId = $_SESSION["user_id"];
  $systemId = $_SESSION["system_id"];


  //Create new database object
  $dbConn = new dbConnect();
  $dbConn->dbConnection();
  
?>
<style>

.btn-connect {
	width: 150px;
    line-height: 2em;
    background: #559325;
	border-radius:0.25em;
    border: 0;
    color: white;
    box-sizing: border-box;
    padding: 0.5em 1em;
}
.btn-disconnect {
	background: #e62727;
}

.btn-connect:hover {
	background:#666;
	cursor:hand;
}

</style>
<script>

const bc = new BroadcastChannel("integration_channel");
bc.onmessage = function (e) {
	console.log("received channel broadcast", e);
	if(e.data){ 		
		if(e.data === "reload"){
			window.location.reload();
			//tell the connect window to close!
			const cc = new BroadcastChannel("integration_callback");
			cc.postMessage("close");			
		}
	}
}

</script>

<div style="font-size:14px;font-family:arial;margin:auto;max-width: 720px;">
    <h2 style="margin-top:1em;">
		<div style="color:#aaa;font-size:42px;padding-bottom:10px;font-weight:normal;">INTEGRATIONS</div>
		<?= $principalName ?></h2>
    <hr>
	
	<?php

		$prinIntegrationsArr = (new IntegrationDAO($dbConn))->getAllByPrincipal($principalId);

		foreach($prinIntegrationsArr as $appName => $integrationArr){
		  
			echo '<div style="padding:1.5em">';

			if(is_array($integrationArr) && isset($integrationArr['title'])){
				echo '<button class="btn-connect btn-disconnect" onclick="confirm(\'Are you sure you want to disconnect this app?\') ? (window.open(\'api.php?type='.strtolower($appName).'&action=disconnect\',\'popup\',\'width=560,height=700\')) : void(0);" style="float:right;margin-top:5px;">DISCONNECT</button>
					<h3>'.$appName.' App connected: <span style="color:#047;">' . $integrationArr['title'] . '</span></h3>
					<div style="color:#777;">connected user: ' . $integrationArr['connected_user'] .'</div>
					<div style="color:#777;">linked: ' . $integrationArr['created'] . ' by ' . $integrationArr['user_name'] . '</div>';
			} else {					  
				echo '<button onclick="window.open(\'api.php?type='.strtolower($appName).'&action=connect\',\'popup\',\'width=560,height=700\'); return false;" rel="noopener noreferrer" target="popup"  class="btn-connect" style="float:right;margin-top:5px;">CONNECT</button>
					<h3>Authorize '.$appName.' App</h3>';						  
			}
																				
			echo '</div><div style="clear:both"></div><hr>';											  				
		}
			  	  				
	?>      
</div>
