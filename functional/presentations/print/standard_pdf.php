<script type="text/javascript">


function printHandler() {
	var params="?USERID=<?php echo $userId; ?>&OBJECTID=<?php echo EO_DOC_CARD_TI; ?>&DOCMASTID=<?php echo $postDOCMASTID; ?>&KEYFROMLINK=<?php echo $postKEYFROMLINK; ?>";
	window.location='<?php echo $ROOT.$PHPFOLDER ?>functional/administration/functions/pdfUserHTML.php'+params;
}

</script>