<script type="text/javascript">


function printHandler() {

  if(confirm("WARNING!\nYou can only print a document once-off!\nComplete any printing before closing the next window!\n\nClick OK to Proceed")){

   //get document id - DOCMASTID
   var DOCMASTID = $('#DOCMASTID').val();
   var STATUSID = $('#STATUSID').val();

   if(DOCMASTID == undefined || DOCMASTID == "" || STATUSID == undefined || STATUSID == ""){
     alert('ERROR : Invalid Document/Status ID');
   } else {

      $.ajax({
          url: "<?php echo $ROOT.$PHPFOLDER ?>functional/presentations/print/logPrint.php",
          global: false,
          type: 'POST',
          data: "DOCMASTID=" + DOCMASTID + "&STATUSID=" + STATUSID,
          dataType: 'html',
          cache: false,
          timeout: 2000,
          success: function(data){


            if(data == "SUCCESS"){
              //returns if it can print and log.
              $('.disableprint').attr('id',''); //remove no print class from div class - allows printing
              val = window.print();
              window.setTimeout("closeDocument();",1000);
            } else {
              alert(data);
            }

          },
          error: function(XMLHttpRequest, textStatus, errorThrown) {
            alert('ERROR : Log Update Failure');
          }
      });
    }
  }

}

function closeDocument(){
  $('body').html('<div align="center" style="margin-top:50px;font-size:12px;color:#444;">Printing complete, click the close button below<br>or close the tab by pressing CTRL+W<br><br><br><a href="javascript:closeWindow();" style="display:block;width:200px;line-height:35px;text-decoration:none;font-weight:bold;font-size:14px;background:#FA5858;color:#fff;">Close</a></div>');
}

//ie work around
function closeWindow(){
  window.close();
}


//Disable right mouse click Script
//By Maximus (maximus@nsimail.com) w/ mods by DynamicDrive
//For full source code, visit http://www.dynamicdrive.com

var message="Function Disabled!";

///////////////////////////////////
function clickIE4(){
  if (event.button==2){
  //alert(message);
  return false;
  }
}

function clickNS4(e){
  if (document.layers||document.getElementById&&!document.all){
    if (e.which==2||e.which==3){
    //alert(message);
    return false;
    }
  }
}

if (document.layers){
  document.captureEvents(Event.MOUSEDOWN);
  document.onmousedown=clickNS4;
}
else if (document.all&&!document.getElementById){
  document.onmousedown=clickIE4;
}

document.oncontextmenu=new Function("return false")


var DOCMASTID = $('#DOCMASTID').val();
var STATUSID = $('#STATUSID').val();
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
        $('#printedtxt').html('COPY');
      } else if(data !== "SUCCESS"){
        $('#printedtxt').html('*COPY*');
        $('#toolbar').hide();
      }

    },
    error: function(XMLHttpRequest, textStatus, errorThrown) {
      alert('ERROR : Log Fetch Fa