<?php


include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require($ROOT.$PHPFOLDER."functional/main/access_control.php");

ob_start(); //Turn on output buffering

if (!isset($_SESSION)) session_start();
$userId = $_SESSION['user_id'];
$principalId = $_SESSION['principal_id'];


if (isset($_POST['action'])) $action=$_POST['action']; else $action="VIEW";
if (isset($_POST['PAGETYPE'])) $postPAGETYPE=$_POST['PAGETYPE']; else $postPAGETYPE="A";


// fields
$fldChosenPCRB = 'ChosenPrincipalProduct';
$divAjaxMainContentArea = "ajaxMainContentArea";
$divAjaxEditContent = "ajaxEditContent";

/*---------------------------------------------------------------------------------------------------------------------------
 *
 * 	START OF SCREEN
 *
 *--------------------------------------------------------------------------------------------------------------------------*/


?>

<script type="text/javascript" >

function selectedPrincipalpProduct(val) {

	$('#<?php echo $divAjaxMainContentArea ?>').hide();

	AjaxRefresh("LOADPRINPRODID="+val+"&DMLTYPE=UPDATE",
	    "<?php echo $ROOT.$PHPFOLDER; ?>functional/products/productForm.php",
			"<?php echo $divAjaxEditContent ?>",
			"Please wait whilst page is refreshed...",
			"");

}

function backToProductList(){
  $('#<?php echo $divAjaxEditContent ?>').html('');	//EMPTY OUT AREA
  $('#<?php echo $divAjaxMainContentArea ?>').show(); //DISPLAY OTHER AREA
  adjustMyFrameHeight();
}

function refreshSelectPrincipalProducts() {
	AjaxRefresh("USERID=<?php echo $userId; ?>&RBNAME=<?php echo $fldChosenPCRB; ?>&CALLBACK=selectedPrincipalpProduct(this.value);&PRINCIPALID=<?php echo $principalId; ?>&RBTYPE=radio",
			"<?php echo $ROOT.$PHPFOLDER; ?>functional/administration/adminPrincipalProductsListTable.php",
		    "<?php echo $divAjaxMainContentArea; ?>",
			    "Please wait whilst page is refreshed...",
			    "");
}


</script>

<?php

  echo "<DIV id='".$divAjaxEditContent."'></DIV>";
  echo "<BR><DIV id='".$divAjaxMainContentArea."'></DIV>";

?>
<script type="text/javascript" defer>
	refreshSelectPrincipalProducts();
</script>
<?php


#--------------------------------------------------------------------------------------------------------------------------


$htmlBody = ob_get_clean();
//$htmlBody = gzencode($htmlBody, 9, FORCE_GZIP);
//header ("Content-Encoding: gzip");
//header ('Content-Length: '.strlen($htmlBody));
echo $htmlBody;

?>