<?php
	include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
	require($ROOT.$PHPFOLDER."functional/main/access_control.php");
	include_once($ROOT.$PHPFOLDER."elements/basicSelectElement.php");
	include_once($ROOT.$PHPFOLDER."libs/GUICommonUtils.php");

if (!isset($_SESSION)) session_start();
$userId = $_SESSION['user_id'];
$principalId = $_SESSION['principal_id'];

// fields
$fldChosenPCRB = 'ChosenPrincipalStore';

// the ajax divs. refreshed independently
$divAjaxMainContentArea = "ajaxMainContentArea";
$divAjaxEditContent = "ajaxEditContent";
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
	    function selectedPrincipalStore(val) {

			$('#<?php echo $divAjaxMainContentArea ?>').hide();

				AjaxRefresh("PRINCIPALSTOREUID="+val+"&DMLTYPE=UPDATE",
				    "<?php echo $ROOT.$PHPFOLDER; ?>functional/stores/storeForm.php",
						"<?php echo $divAjaxEditContent ?>",
						"Please wait whilst page is refreshed...",
						"");

	    }

        function backToStoreList(){
          $('#<?php echo $divAjaxEditContent ?>').html('');	//EMPTY OUT AREA
          $('#<?php echo $divAjaxMainContentArea ?>').show(); //DISPLAY OTHER AREA
          adjustMyFrameHeight();
        }

		function refreshSelectPrincipalStores() {
			AjaxRefresh("USERID=<?php echo $userId; ?>&RBNAME=<?php echo $fldChosenPCRB; ?>&CALLBACK=selectedPrincipalStore(this.value);&PRINCIPALID=<?php echo $principalId; ?>&RBTYPE=radio",
						"<?php echo $ROOT.$PHPFOLDER; ?>functional/administration/adminPrincipalStoresListTable.php",
					    "<?php echo $divAjaxMainContentArea; ?>",
					    "Please wait whilst page is refreshed...",
					    "");
		}
		</script>
		<?php

		echo "<DIV id='".$divAjaxEditContent."'></DIV>";
	    echo "<BR><DIV id='".$divAjaxMainContentArea."'></DIV>";

	    echo '<script type="text/javascript">';
        if(isset($_POST['PSMUID'])){
          echo "selectedPrincipalStore({$_POST['PSMUID']})";
        } else {
          echo "refreshSelectPrincipalStores();";
        }

        ?>
        </script>
        <?php
        echo "</BODY></HTML>";
#--------------------------------------------------------------------------------------------------------------------------

$dbConn->dbClose();

?>
