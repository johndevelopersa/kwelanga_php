<?php
	include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
	require($ROOT.$PHPFOLDER."functional/main/access_control.php");
	include_once($ROOT.$PHPFOLDER."elements/basicSelectElement.php");
	include_once($ROOT.$PHPFOLDER."libs/GUICommonUtils.php");

if (!isset($_SESSION)) session_start();
$userId = $_SESSION['user_id'];
$principalId = $_SESSION['principal_id'];
$principalName = $_SESSION['principal_name'];

// fields
$fldChosenPCRB = 'ChosenPrincipalChain';

// the ajax divs. refreshed independently	
$divAjaxMainContentArea="ajaxMainContentArea"; 

//Create new database object
$dbConn = new dbConnect();

//Database connection method
$dbConn->dbConnection();


#--------------------------------------------------------------------------------------------------------------------------
		
		
	    /*
	     * 
	     * START OF SCREEN
	     * 
	     */
	    echo "<HTML><HEAD></HEAD><BODY>";
	    include_once($ROOT.$PHPFOLDER.'elements/basicInputElement.php');
	    
	    ?>
	    <script type="text/javascript" defer>
	    function selectedPrincipalChain(val) {
	    	getContent("<?php echo $ROOT.$PHPFOLDER; ?>functional/chains/chainForm.php","PRINCIPALCHAINUID="+val+"&DMLTYPE=UPDATE"); // func is in generalAjaxBase.php
	    }
	    
		function refreshSelectPrincipalChains() {
			AjaxRefresh("USERID=<?php echo $userId; ?>&RBNAME=<?php echo $fldChosenPCRB; ?>&CALLBACK=selectedPrincipalChain(this.value);&PRINCIPALID=<?php echo $principalId; ?>",
						"<?php echo $ROOT.$PHPFOLDER; ?>functional/administration/adminPrincipalChainsListTable.php",
					    "<?php echo $divAjaxMainContentArea; ?>",
					    "Please wait whilst page is refreshed...",
					    "");
		}
		</script>
		<?php
	    echo "<div id='".$divAjaxMainContentArea."'>";
	     
        echo "</div>";  // main content area
        ?>
        <script type="text/javascript" defer>
        refreshSelectPrincipalChains();
        </script>
        <?php
        echo "<img name='endpositioner' src='".$DHTMLROOT.$PHPFOLDER."images/invis.gif' />"; // used by adjustMyFrameHeight() to get bottom position. offset calculation is otherwise unreliable
        echo "</BODY></HTML>";
#--------------------------------------------------------------------------------------------------------------------------

$dbConn->dbClose();

?>
