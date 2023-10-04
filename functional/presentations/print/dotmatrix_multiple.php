<script type="text/javascript">

    var DOCMASTID = $('#DOCMASTID').val();
    var STATUSID = $('#STATUSID').val();
    $('body').prepend('<div align="center"><iframe src="<?php echo $ROOT.$PHPFOLDER ?>functional/presentations/print/ajaxDotmatrixHandler.php?ALLOWMULTIPLE=1&DOCMASTID='+DOCMASTID+'&STATUSID='+STATUSID+'&STATIONARY=<?php echo $stationaryScript?>" height="100" width="700" border="0" style="border:1px solid #047" id="itoolbar" ><iframe></div>');

</script>