<?php 

error_reporting(-1);
ini_set('display_errors', 1);

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER.'libs/common.php');
include_once($ROOT.$PHPFOLDER."libs/GUICommonUtils.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
require __DIR__ . '/IntegrationDAO.php';


if (!isset($_SESSION)) session_start() ;
$principalId = $_SESSION['principal_id'] ;
$principalName = $_SESSION['principal_name'] ;
$userId = $_SESSION["user_id"];
$userFullName = $_SESSION["full_name"];
$systemId = $_SESSION["system_id"];

//Create new database object
$dbConn = new dbConnect();
$dbConn->dbConnection();

$integrationDAO = new IntegrationDAO($dbConn);
  
$type = $_GET['type']??false;
$action = $_GET['action']??false;
$integrationKey = $integrationDAO->getIntegrationKey($type);


if(!$type || !$action){
	echo "error blank integration type";
	return;
}

$callbackFile = __DIR__ . "/" . $type . "/IntegrationClass.php";
if(!is_file($callbackFile)){
	echo "error invalid or missing callback file: $callbackFile";
	return;
}

//include the libraryClass
require $callbackFile;


//does the required class now exist?
if(!class_exists("IntegrationClass")){
	echo "error invalid or missing class IntegrationClass in file: $callbackFile";
	return;
}



/*------------------------------------------
 *	what todo???
 *----------------------------------------*/
 
if($action == "connect"){
	
	//not sure if other guys might want some system data!?	
	(new IntegrationClass)->connect();
	
} elseif($action == "callback"){
	
	$c = (new IntegrationClass);
	$c->callback($_GET);	//do method;
	
	//update database and redirect.
	if(!$c->getSuccess()){
		echo "<h3 style='color:darkred;'>Error</h3>" . $c->getError();
		return;
	}
		
	//store tokens etc against principal
	$arr = $c->getIntegrationArr();	
	$arr['user_id'] = $userId;
	$arr['user_name'] = $userFullName;
		
	$errorTO = $integrationDAO->save($principalId, $type, $arr);
	if ($errorTO->type != FLAG_ERRORTO_SUCCESS) {            	    		
		echo "error update of database failed: {$errorTO->description}";
		return ;       	                  
	} 
	$dbConn->dbQuery("commit");
	
	
	echo "<h1 style='color:darkgreen;'>SUCCESSFUL: closing in 5 secs</h1>";
	echo '<script>
			//setTimeout(function(){ window.close() }, 5000);
			
			//listen for close event
			const ic = new BroadcastChannel("integration_callback");
			ic.onmessage = function (e) {
				console.log("received callback broadcast", e);
				if(e.data){
					if(e.data === "close"){
						window.close()
					}
				} 
			}

			//this tells the integration page to reload
			const bc = new BroadcastChannel("integration_channel");
			bc.postMessage("reload");
		</script>';


} elseif($action == "disconnect"){
	
	//probably should add more checks here!?
	$integrationArr = $integrationDAO->getForPrincipalByType($principalId, $type);
	if(!count($integrationArr)){
		echo "<h3 style='color:darkred;'>Error</h3> no integration data available";
		return;
	}
	
	$c = (new IntegrationClass);
	$c->disconnect($integrationArr);	
	
	if(!$c->getSuccess()){
		echo "<h3 style='color:darkred;'>Error</h3>" . $c->getError();
		return;
	}
	
	$errorTO = $integrationDAO->remove($principalId, $type);
	if ($errorTO->type != FLAG_ERRORTO_SUCCESS) {            	    		
		echo "error update of database failed: {$errorTO->description}";
		return ;       	                  
	}
	$dbConn->dbQuery("commit");

	echo "<h1 style='color:darkgreen;'>SUCCESSFULLY DISCONNECTED: closing in 5 secs</h1>";
	echo '<script>
			//setTimeout(function(){ window.close() }, 5000);
			
			//listen for close event
			const ic = new BroadcastChannel("integration_callback");
			ic.onmessage = function (e) {
				console.log("received callback broadcast", e);
				if(e.data){
					if(e.data === "close"){
						window.close()
					}
				} 
			}

			//this tells the integration page to reload
			const bc = new BroadcastChannel("integration_channel");
			bc.postMessage("reload");
		</script>';	
	
} else {
	echo "<h3 style='color:darkred;'>Error</h3> Unknown action: {$action}";
}
