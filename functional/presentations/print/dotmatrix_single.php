<script type="text/javascript">


var DOCMASTID = $('#DOCMASTID').val();
var STATUSID = $('#STATUSID').val();



//VALIDATE BEFORE DISPLAYING IFRAME....

$.ajax({
    url: "<?php echo $ROOT.$PHPFOLDER ?>functional/presentations/print/logPrint.php",
    global: false,
    type: 'POST',
    data: "VALIDATE=1&DOCMASTID=" + DOCMASTID + "&STATUSID=" + STATUSID,
    dataType: 'html',
    cache: false,
    timeout: 2000,
    success: function(data){


      if(data == "REPRINT"){
        $('body').prepend('<div align="center"><iframe src="<?php echo $ROOT.$PHPFOLDER ?>functional/presentations/print/ajaxDotmatrixHandler.php?DOCMASTID='+DOCMASTID+'&STATUSID='+STATUSID+'&STATIONARY=<?php echo $stationaryScript?>&ISCOPY=1" height="100" width="700" border="0" style="border:1px solid #047" id="itoolbar" ><iframe></div>');
      } else if(data !== "SUCCESS"){
        $('body').prepend('<div align="center" style="font-size:10px;padding:10px;">'+data+'</div>');
      } else {
        $('body').prepend('<div align="center"><iframe src="<?php echo $ROOT.$PHPFOLDER ?>functional/presentations/print/ajaxDotmatrixHandler.php?DOCMASTID='+DOCMASTID+'&STATUSID='+STATUSID+'&STATIONARY=<?php echo $stationaryScript?>" height="100" width="700" border="0" style="border:1px solid #047" id="itoolbar" ><iframe></div>');
      }

    },
    error: function(XMLHttpRequest, textStatus, errorThrown) {
      alert('ERROR : Log Fetch Failure');
    }
});




</script>