<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER."properties/Constants.php");
    include_once($ROOT.$PHPFOLDER."elements/datePickerElement.php");
    include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
    include_once($ROOT.$PHPFOLDER."libs/CommonUtils.php");
    include_once($ROOT.$PHPFOLDER."DAO/TripSheetDAO.php");		
    include_once($ROOT.$PHPFOLDER."TO/TripDispatchTO.php");		
    include_once($ROOT.$PHPFOLDER."TO/TripDispatchDetailTO.php");		
	  
     if (!isset($_SESSION)) session_start() ;
     $userUId     = $_SESSION['user_id'] ;
     $principalId = $_SESSION['principal_id'] ;
     $depotId     = $_SESSION['depot_id'] ;
                
//Create new database object
$dbConn = new dbConnect(); 
$dbConn->dbConnection();
$errorTO = new ErrorTO;

?>
<!DOCTYPE html>
<html style="height:100%;width:100%;">
  <head>

		<TITLE>Document Selection</TITLE>
    <link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/1_kwelanga.css?v=1' rel='stylesheet' type='text/css'>
    <link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/kos_standard_screen.css' rel='stylesheet' type='text/css'>
    <?php DatePickerElement::getDatePickerLibs(); ?>
	 <LINK href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/1_default.css' rel='stylesheet' type='text/css'>
    
    <script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/jquery.js"></script>
    <script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/dops_global_functions.js"></script>
    <!-- done this way and not a LINK tag so that PHP within the .js file still works -->
    <script type='text/javascript' language='javascript' ><?php include_once($ROOT.$PHPFOLDER . "functional/transaction/debriefing/debriefDocument.js"); ?></script> 

    <style>

      .scan-input {
         height: 42px !important;
         border-radius: 15px !important;
         padding-left: 50px !important;
         width:100% !important;
      }
      .scan-input:focus {
         outline:none !important;
         border:3px solid black;
      }

    </style>

  </head>

<body style="background:url('<?php echo $DHTMLROOT.$PHPFOLDER ?>images/scan-background.jpg'); background-repeat:no-repeat; background-size:100% 100%; width:100%;height:100%;">

<div style="background:rgba(255,255,255,0.9); width:100%;height:100%;">

<table class="tableReset" style="width:100%;">
   </tr>
      <td>&#160;</td>
      <td style="width:30%;">
         <p>Please enter the Invoice Document Number, or use the scanner</p>
         <div style="position:relative;">
            <INPUT type="TEXT" size="15" name="DOCUMENTNO" id="DOCUMENTNO" class="scan-input" placeholder="scan or input" autofocus />
            <div class="icon-size-32 icon-scan" style="position:absolute;top:4px;left:10px;" ></div>
         </div>
      </td>
      <td>&#160;</td>
   </tr>
</table>

</div>


<script type="text/javascript" >

var debriefDocumentView = {

   getDocumentNoFromInput : function(val) {

      var parts = val.split('-');

      if (typeof parts[1] === 'undefined' ) {
         return parts[0].fulltrim();         
      }
      else {
         return parts[1].fulltrim();
      }

   },

   getScannedValues : function() {

      var parts = $('#DOCUMENTNO').val().split('-');

      if (typeof parts[1] === 'undefined' ) {
         return { principalId : '', documentNo : parts[0].fulltrim() };         
      }
      else {
         return { principalId : parts[0].fulltrim(), documentNo : parts[1].fulltrim() }; 
      }

   },

   showMessage : function(msgType, message) {

      if(typeof parent.hideMsgBoxSystemFeedback == 'function') mF=parent.hideMsgBoxSystemFeedback; else if(typeof hideMsgBoxSystemFeedback == 'function') mF=hideMsgBoxSystemFeedback; else mF=false;
      if(typeof parent.showMsgBoxInfo == 'function') mI=parent.showMsgBoxInfo; else if(typeof showMsgBoxInfo == 'function') mI=showMsgBoxInfo; else mI=false;
      if(typeof parent.showMsgBoxError == 'function') mE=parent.showMsgBoxError; else if(typeof showMsgBoxError == 'function') mE=showMsgBoxError; else mE=false;

      if (msgType=='error') mE(message);
      else if ((msgType=='info') || (msgType=='success')) mI(message);
      else if (msgType=='feedback') mF(message);

   },

   processDocumentNo : function() {

      var scannedValuesTO = debriefDocumentView.getScannedValues();
      var principalId = scannedValuesTO.principalId,
          documentNo = scannedValuesTO.documentNo;

      if ((principalId == '') || (documentNo == '')) {
         debriefDocumentView.showMessage('error', 'Incomplete input or format of entry - principal Id and Document No is required with a dash separator');
         return;
      }

      var TO = { 
                  "principalId" : principalId,
                  "documentNo" : documentNo 
               };
      serviceName = 'getDebriefDocument';
      $.ajax({
            type: "POST",
            data: JSON.stringify(TO),
            url: parent.app.serverPath+"m/api.php?sn="+serviceName,
            processData: false, // stop it adding params to GET url or trying to transform it into a query sring, which can cause 404 errors
            jsonp: false, // Override the callback function name in a JSONP request. This value will be used instead of 'callback' in the 'callback=?' part of the query string in the url
            dataType: "json", // note that this forces the server to return ONLY json, no HTML
            crossDomain: true,
            success: function (result) {

               if(typeof parent.hideMsgBoxSystemFeedback == 'function') mF=parent.hideMsgBoxSystemFeedback; else if(typeof hideMsgBoxSystemFeedback == 'function') mF=hideMsgBoxSystemFeedback; else mF=false;
               if(typeof parent.showMsgBoxInfo == 'function') mI=parent.showMsgBoxInfo; else if(typeof showMsgBoxInfo == 'function') mI=showMsgBoxInfo; else mI=false;
               if(typeof parent.showMsgBoxError == 'function') mE=parent.showMsgBoxError; else if(typeof showMsgBoxError == 'function') mE=showMsgBoxError; else mE=false;
               
               if (result) {
                  var myData = result;
                  if (myData.resultStatus=="S") {
                     debriefDocument.showManage(result.data.dmUId , false, false, debriefDocumentView.getDocumentNoFromInput($('#DOCUMENTNO').val()) );
                  } else {
                     if (myData.resultStatus=='E') mE(myData.resultMessage);
                  }
                  $('#DOCUMENTNO').val(''); // blank it out regardless
                  console.log('RESPONSE FROM makeWSCall.'+serviceName+':'+JSON.stringify(result));
               } else {
                  alert(serviceName+" returned an error on WS Call : "+result, "Error");
                  console.log('RESPONSE FROM makeWSCall.'+serviceName+':'+JSON.stringify(result));
               }

            },

            // deprecated in jQuery 1.5 ?
            error: function (xhr, textStatus, error) {
               alert(JSON.stringify(xhr)+" | "+JSON.stringify(textStatus)+" | "+JSON.stringify(error));
               console.log("Error occured while getting request "+serviceName+", Error Code: " + xhr.status + ". Error desc: " + xhr.statusText + " | " + textStatus + " | " + xhr.responseText);
            },

            fail: function (xhr, textStatus, error) {
               alert(JSON.stringify(xhr)+" | "+JSON.stringify(textStatus)+" | "+JSON.stringify(error));
               console.log("Error occured while getting request "+serviceName+", Error Code: " + xhr.status + ". Error desc: " + xhr.statusText + " | " + textStatus + " | " + xhr.responseText);
            },
            // complete: function() { alert("complete"); },

            /*
            // same as .complete() but supported JQuery 1.5+
            always: function (xhr, textStatus, error) {
            alert(JSON.stringify(xhr)+" | "+JSON.stringify(textStatus)+" | "+JSON.stringify(error));
            console.log("Error occured while getting request "+serviceName+", Error Code: " + xhr.status + ". Error desc: " + xhr.statusText + " | " + textStatus + " | " + xhr.responseText);
            if (callback!=false) { if (additionalCallbackTO==undefined) callback(false); else callback(false,additionalCallbackTO); }
            },
            */

      });

   },

   assignEnterKey : function() {
      
      $("#DOCUMENTNO").keyup(function(event){
         if(event.keyCode == 13){
            debriefDocumentView.processDocumentNo();
         }
      });

   },

}

debriefDocumentView.assignEnterKey();
$('#DOCUMENTNO').focus();


</script>



</body>        
</html>
 