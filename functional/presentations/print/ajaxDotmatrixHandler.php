<?php

$getDOCMASTID = (isset($_GET['DOCMASTID']) ? $_GET['DOCMASTID'] : false);
$getSTATUSID = (isset($_GET['STATUSID']) ? $_GET['STATUSID'] : false);
$getSTATIONARY = (isset($_GET['STATIONARY']) ? $_GET['STATIONARY'] : false);

if($getDOCMASTID == false || empty($getDOCMASTID) || $getSTATIONARY == false || empty($getSTATIONARY)){
  echo 'Invalid Document Id or Stationary';
  return;
}


$path = '../stationary/'.$getSTATIONARY;
if(!is_file($path)){
  echo 'Stationary Could not be found!';
  return;
}
include($path);


$docArr = explode(',', $getDOCMASTID);

$STATIONARY = '';
foreach($docArr as $docmastId){
  $stat = new Stationary($docmastId);
  $STATIONARY .= $stat->render();
}

IF($STATIONARY==""){
  echo "Invalid/Empty Stationary!";
  return;
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html><body style="background:#047;color:#ccc;font-size:12px;font-family: verdana;">
<script type="text/javascript" src="../../../js/PluginDetect.js"></script>
<script type="text/javascript" src="../../../js/jquery.js"></script>
<script type="text/javascript">


// Applet constants.  These shouldn't ever change unless you recompile jzebra.jar
var JAVA_MIN_VER = '1.5';
var APPLET_NAME = 'jzebra';
var APPLET_URL = '../../../libs/jZebra/jzebra.jar';
var APPLET_CLASS = 'jzebra.PrintApplet.class';

// Time to wait before loading the applet, in seconds (necessary for IE)
var WAIT = 0.5;

// Display messages, feel free to change
var ERR_MIN_VER = 'Sorry, Java ' + JAVA_MIN_VER + ' or higher was not detected.';
var ERR_LOADING = 'Sorry, ' + APPLET_NAME + ' was unable to load properly.';
var ERR_PRINTER = 'Error: Could not find printer';
var MSG_LOADING = 'Loading printing applet, please wait...';
var MSG_LOADED =  'Finished loading printing applet, getting list of printers...';
var MSG_PRINTER = 'Found printer: ';
var MSG_SUCCESS = 'Print successful';

var applet = null;
var isReady = false;

// Wait 200ms before appending the applet
PluginDetect.onWindowLoaded(function() {setTimeout(function() {appendApplet()}, WAIT * 1000.0)});

// Automatically gets called when the applet is finished loading
function jzebraReady() {
   applet = document.applets[APPLET_NAME];
   setStatus(MSG_LOADED);
   //document.getElementById('heading').innerHTML = 'jZebra ' + applet.getVersion();
   isReady = true;

    if (isReady) {
        debugger;
      var multiple = applet.getAllowMultipleInstances();
      applet.allowMultipleInstances(!multiple);
      //alert('Allowing of multiple applet instances set to "' + !multiple + '"');
    }

   $('#printersID').html('<select id="printerName" style="padding:4px;"></select>');

    //applet.findPrinter();
   applet.findPrinter("\\{dummy printer name for listing\\}");
   monitorFindingList(); //handler for above

}




function monitorFindingList() {

  if (isReady) {
     if (!applet.isDoneFinding()) {
        window.setTimeout('monitorFindingList()', 100);
     } else {
        var printersCSV = applet.getPrinters();
        var printers = printersCSV.split(",");
        for (p in printers) {
          $('#printerName').append('<option value="'+printers[p]+'">'+printers[p]+'</option>');
        }
        $('#message').html("Select printer...");
        setStatus('Successfully loaded printers, finding default printer...');
        selectDefaultPrinter(); //select the default printer.
     }
  } else {
    alert("Applet not loaded!");
  }
}


function selectDefaultPrinter(){
  applet.findPrinter();
  monitorFinding()
}


function monitorFinding() {
  var applet = document.jzebra;
  if (applet != null) {
     if (!applet.isDoneFinding()) {
        window.setTimeout('monitorFinding()', 100);
     } else {
        var printer = applet.getPrinter();
        if(printer == null){
          //default printer not found....
          setStatus('Default printer could not be found!...');
        } else {
          setStatus('Successfully loaded printers...');
          $('#printerName option').each(function () {
            if($.trim($(this).text()) == $.trim(printer)){
              $(this).attr('selected','selected');
            }
          });
          $('#printersID').append('<input type="button" onClick="setPrinter();" value="PRINT" style="background:#fff;padding:4px 8px;">');

        }
     }
  } else {
      alert("Applet not loaded!");
  }
}

function setPrinter(){
  setStatus('Selecting printer to print... (600)');
  var printer = $('#printerName option:selected').val();
  applet.findPrinter(printer);
  monitorSetPrinter();
}

function monitorSetPrinter() {

  if (isReady) {
     if (!applet.isDoneFinding()) {
        window.setTimeout('monitorSetPrinter()', 100);
     } else {
        var printer = applet.getPrinter();
        setStatus('Set printer to '+printer+', sending document to print....');
        print();
     }
  } else {
    alert("Applet not loaded!");
  }
}


function print() {

    $.ajax({
        url: "<?php echo $ROOT.$PHPFOLDER ?>functional/presentations/print/logPrint.php",
        global: false,
        type: 'POST',
        data: "<?php echo (count($docArr)>1)?'BULKACTION=1&':'' ?>DOCMASTID=<?php echo implode(',',$docArr) ?>&STATUSID=<?php echo $getSTATUSID ?>",
        dataType: 'html',
        cache: false,
        timeout: 60000,
        success: function(data){
          if(data == "SUCCESS"){

              //returns if it can print and log.
              //print after successful log...
              if (isReady) {
                  document.jzebra.append("<?php echo $STATIONARY; ?>");
                  applet.print();
              }
              monitorSuccessfulPrint();

          } else {
            alert(data);
          }

        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
          alert('ERROR : Print Log Failure');
        }
    });


}

function monitorSuccessfulPrint(){

  var applet = document.jzebra;
  if (applet != null) {
     if (!applet.isDonePrinting()) {
        window.setTimeout('monitorSuccessfulPrint()', 100);
     } else {
       var e = applet.getException();
       if(e == null){
         closeDocument();
       } else {
         alert("Exception occured: " + e.getLocalizedMessage());
       }
     }
  } else {
      alert("Applet not loaded!");
  }

}

function closeDocument(){
  parent.$('body').html('<div align="center" style="margin-top:50px;font-size:12px;color:#444;">Printing complete, click the close button below<br>or close the tab by pressing CTRL+W<br><br><br><a href="javascript:window.close();" style="display:block;width:200px;line-height:35px;text-decoration:none;font-weight:bold;font-size:14px;background:#FA5858;color:#fff;">Close</a></div>');
}


// Automatically gets called when applet is done appending a file
function jzebraDoneAppending() {}
function jzebraDoneFinding() {}
function jzebraDonePrinting() {}

// Check for proper Java version, then append the applet
function appendApplet() {

   if (PluginDetect.isMinVersion('Java', JAVA_MIN_VER) == 1) {
      if (document.applets[APPLET_NAME] == null) {
         setStatus(MSG_LOADING);
         document.getElementById('applet').innerHTML = '<applet name="' + APPLET_NAME + '" code="' + APPLET_CLASS +
      	      '" archive="' + APPLET_URL + '" width="0" height="0" mayscript></applet>';
      }
   } else {
      setStatus(PluginDetect.getVersion('Java')+ERR_MIN_VER);
   }
}

// Display status
function setStatus(text) {
   document.getElementById('status').innerHTML = text;
}

</script>


<div align="center">
  <span style="position:absolute;bottom:0px;left:0px;border-top:1px solid #666;background:#efefef;color:#777;font-size:9px;width:100%;padding:4px 0px;text-align:left;">&nbsp;&nbsp;Status: <span id="status"></span></span>
  <div id="applet" style="position:absolute;top:0px;left:0px;width:1px;height:1px;" ></div>

  <div id="message">
    <h2>Loading...</h2>
    <small>This requires JAVA - <a href="http://java.com/en/download/" target="_blank" style="color:yellow;">download here</a>,
    please also accept any security notifications so we can connect to your print devices.
    </small>
  </div>

  <div id="printControl">
    <div id="printersID"></div>
</div>
</div>

</body></html>



