/*
    NOTE:
    This namespaces the debriefDocument.php JS functionality and allows it to be called externally.
    Method calls from within here to methods that do not exist in this namespaced JS class means that the method is still in the viewTracking.php file

*/

var debriefDocument = {

    active_dmUId : false,

    // deliveryNote is depot.delivery_note needs to be printed as a companion ~ show additional fields as well
    showManage : function(dmUId, rowIndex, thisField, deliveryNote) {
        debriefDocument.active_dmUId = dmUId;
        debriefDocument.displayManage(deliveryNote);
        // WARNING : The .load() jquery event causes scripts to be executed twice in IE. The page is not fetched twice.
        // 					 The content is passed to .html() prior to scripts being removed. This executes the script blocks before they are discarded and again after.
        //					 This is because JQuery uses the html() to parse the innerHTML when it is received which executes the script and again when it is inserted into DOM
        $('#div_MANAGECONTENT').load('<?php echo $ROOT.$PHPFOLDER."functional/transaction/manageDocument.php?pAlias=&DOCMASTID=" ?>'+dmUId, debriefDocument.isLoadedManage=false);
    },

    //deliveryNote is depot.delivery_note needs to be printed as a companion ~ show additional fields as well
    displayManage : function(deliveryNote){

        if ((typeof deliveryNote == "undefined") || (deliveryNote===false)) deliveryNote='N';

        debriefDocument.renderDivManage();
    
        $('#div_MANAGECONTENT').html("<center style='background-color:white;'><img src='<?php echo $DHTMLROOT.$PHPFOLDER ?>images/loading.gif' /></center>");
        // unfortunately the modal layer in parent doesnt work with z-index here
        $('body').closest().append("<div id='vt_modalLayer' "+
                                " style='position:absolute; top:0; left:0; width:100%; height:100%; z-index:99; background-image:url(\"<?php echo $DHTMLROOT.$PHPFOLDER; ?>images/modal-layer.png\");'>"+
                                "</div>");
    
        //$('#div_MANAGE,#div_MANAGECONTENT').css({'height':'','max-height':''}); // reset the height so max-height doesnt use the last size
        $('#div_MANAGE').css({'marginTop':f_scrollTop()+30,'marginLeft':(f_clientWidth()-750)/2,'max-height':f_clientHeight()-100})
                        .show(500,
                            function(){
                                            // $('#div_MANAGECONTENT').css({'height':$('#div_MANAGE').css('max-height').replace('px','')-80});
                            }
                        );
        $('#m_action').prop("onclick", null).attr("onclick", null); // remove the action event, returning val will set the action, also acts as a safety
        $('#m_action').unbind('click');
    
        // delivery note info handling
        $('#deliveryNoteInfoWrapper')
            .css({'display':'none'})
            .html(''); // the wrapper
        if (deliveryNote=='Y') {
        $('#btnDeliveryNoteInfo').css({'display':'table-cell'}); // the btn
        } else {
        $('#btnDeliveryNoteInfo').css({'display':'none'});
        }
    
    },

    renderDivManage : function() {

        if ($('#div_MANAGE').length > 0) return;

        // unfortunately couldnt use the generic popup box and the .load jquery method as the css doesnt work afterwards and DOM elements are not visible when dynamically created
	    var c = `<div id='div_MANAGE' class='rdCrn10' style='z-index:100; padding:10px; top:0px; left:0px; width:750px; position:absolute; display:none; background-color:#EFEFEF;border:3px solid #666;white-space:nowrap;'>

                    <div style='color:#1e4272;line-height:35px;' align='center'><strong>Available options for Document(s) :</strong></div>
                    <div id='div_MANAGECONTENT' class='rdCrn3' style='display:block;padding:15px 10px;border:1px solid #999; background-color:#FEFEFE; overflow:auto;' ></div>
                    <br>
                    <div align='center'>
                        <table class='tableReset' cellspacing=0 cellpadding=0><tr>
                                <td><input id='m_action' type='submit' class='submit' value='Accept Changes' /></td>
                                <td><input type='submit' class='submit' value='Cancel' onclick='debriefDocument.hideManageScreen();' /></td>
                            <td id='btnDeliveryNoteInfo' style='display:none;'>
                                <input type='button' class='submit' value='Delivery Note Info' onclick='debriefDocument.showDeliveryNoteInfo();' />
                            </td>
                        </tr></table>

                        <!-- only shows if delivery note info btn clicked -->
                        <div id='deliveryNoteInfoWrapper' style='display:none;border:1px solid rgb(102,102,102);border-radius:6px;-webkit-border-radius:6px;padding:10px;margin:10px;' ></div>

                    </div>
                    <br>
                </div>`;

        if ($('body').closest().length > 0) 
            $('body').closest().append(c);
        else
            $('body').append(c);

    },

    hideManageScreen : function() {

        debriefDocument.active_dmUId = false;
        $("#deliveryNoteInfoWrapper").html('');
        $("#vt_modalLayer").remove();
        $("#div_MANAGE").hide(500);
        $('#div_MANAGE,#div_MANAGECONTENT').css({'height':'','max-height':''}); // reset the height so max-height doesnt use the last size

    },

    showDeliveryNoteInfo : function() {

        $("#deliveryNoteInfoWrapper")
            .show()
            .load('<?php echo $ROOT.$PHPFOLDER; ?>functional/transaction/deliveryNoteInfo.php?DMUID='+debriefDocument.active_dmUId);

    },

    isLoadedLink : false, // see WARNING comments against .load below

    showLink : function(dmUId,rowIndex, thisField) {
        // unfortunately the modal layer in parent doesnt work with z-index here
        $('body').closest().append("<div id='vt_modalLayer' "+
                                    "	style='position:absolute; top:0; left:0; width:100%; height:100%; z-index:99; background-image:url(\"<?php echo $DHTMLROOT.$PHPFOLDER; ?>images/modal-layer.png\");'>"+
                                    "</div>");

        $('#div_MANAGE,#div_MANAGECONTENT').css({'height':'','max-height':''}); // reset the height so max-height doesnt use the last size
        $('#div_MANAGE').css({'marginTop':f_scrollTop()+20,'marginLeft':f_scrollLeft()+f_clientWidth()/2-350,'max-height':f_clientHeight()-100+'px'}).show(700,
            function(){
                            $('#div_MANAGECONTENT').css({'height':$('#div_MANAGE').css('max-height').replace('px','')-80});
            }
            );

        $('#m_action').prop("onclick", null).attr("onclick", null); // remove the action event, returning val will set the action, also acts as a safety
        $('#m_action').unbind('click');

        // WARNING : The .load() jquery event causes scripts to be executed twice in IE. The page is not fetched twice.
        // 					 The content is passed to .html() prior to scripts being removed. This executes the script blocks before they are discarded and again after.
        //					 This is because JQuery uses the html() to parse the innerHTML when it is received which executes the script and again when it is inserted into DOM
        $('#div_MANAGECONTENT').load('<?php echo $ROOT.$PHPFOLDER."functional/transaction/manageLink.php?pAlias=&DOCMASTID=" ?>'+dmUId,
                                                                debriefDocument.isLoadedLink=false);
    },

    // called when "Accept Changes" clicked
    manageAccept : function(dmUIdlist) {

        var pageGroup = '<?php echo $postSCRUSAGE; ?>',
            skipInPickStage = '<?php echo $skipInPickStage; ?>',
            params='';

        var status = convertElementToArray(document.getElementsByName('MD_STATUS'));


        // get additional page fields for params
        if (((pageGroup==3) || (pageGroup==2 && skipInPickStage=='Y')) && ((status=="W")) ) { // inpick changing to Invoiced
            params='&DDUID='+convertElementToArray(document.getElementsByName("MD_DDUID[]"));
            params+='&DOCMASTLIST='+dmUIdlist;
            params+='&AMENDEDQTY='+convertElementToArray(document.getElementsByName("MD_AMENDEDQTY[]"));
            params+='&ACCEPTQTY='+convertElementToArray(document.getElementsByName("MD_ACCEPTQTY"));

            // batch may contain CSV so that needs to be manually escaped so cant call convertElementToArray()
            var arr = new Array(),
                fld = document.getElementsByName("MD_BATCH[]");
                for (var i=0; i<fld.length; i++) {
                    arr.push(fld[i].value.replace(',','|'));
                }

            params+='&BATCH='+arr;
        } else if ((status=="I") && ('<?php echo ((!CommonUtils::isDepotUser())?1:2); ?>'=='1')) {
            // quotations
            params='&DDUID='+convertElementToArray(document.getElementsByName("MD_DDUID[]"));
            params+='&DOCMASTLIST='+dmUIdlist;
            params+='&AMENDEDQTY='+convertElementToArray(document.getElementsByName("MD_AMENDEDQTY[]"));
            params+='&ACCEPTQTY='+convertElementToArray(document.getElementsByName("MD_ACCEPTQTY"));
        }

        if (status=="A") {

            submitAction('ACCEPT',dmUIdlist,params,'refreshStatus("'+dmUIdlist+'",msgClass);');

        } else if (status=="UNACCEPTED") {

            submitAction('UNACCEPTED',dmUIdlist,params,'refreshStatus("'+dmUIdlist+'",msgClass);');

        } else if (status=="AMEND-USE-CAPTURE") {
            var parentBody = window.parent.document.body;
            $("#content", parentBody).attr('src','<?php echo HOST_SURESERVER_AS_NEWUSER.$PHPFOLDER; ?>functional/transaction/quotationCapture.php?DOCMASTID='+dmUIdlist);

        } else if (status=="C") {

            // skip if not depot user ie. is quotation mgmnt
            var comment = '';
            if ($('#MD_COMMENT').length>0) {
                comment = encodeURIComponent(document.getElementById('MD_COMMENT').value);
                params+='&REASONCODE='+document.getElementById('MD_REASONCODE').value;
            }
            submitAction('CANCEL',dmUIdlist,'COMMENT='+comment+params,'refreshStatus("'+dmUIdlist+'",msgClass);');

        } else if (status=="W") {

            params+='&REPCODE='+document.getElementById("REPCODE").value;
            params+='&TRACKINGNUMBER='+document.getElementById("TRACKINGNUMBER").value;
            submitAction('WAITDISPATCH',dmUIdlist,params,'debriefDocument.successCallBack("'+dmUIdlist+'",<?php echo ((CommonUtils::isDepotUser())?"true":"true") ?>); refreshStatus("'+dmUIdlist+'",msgClass);');

        } else if (status=="I") {

            params+='&REPCODE='+document.getElementById("REPCODE").value;
            params+='&TRACKINGNUMBER='+document.getElementById("TRACKINGNUMBER").value;
            submitAction('INVOICE',dmUIdlist,params,'debriefDocument.successCallBack("'+dmUIdlist+'",<?php echo ((CommonUtils::isDepotUser())?"true":"true") ?>); refreshStatus("'+dmUIdlist+'",msgClass);');

        } else if (status=="IN-PROGRESS") {

            submitAction('IN-PROGRESS',dmUIdlist,params,'debriefDocument.successCallBack("'+dmUIdlist+'", false); refreshStatus("'+dmUIdlist+'",msgClass);');

        } else if (status=="JOB-COMPLETE") {

            submitAction('JOB-COMPLETE',dmUIdlist,params,'debriefDocument.successCallBack("'+dmUIdlist+'", false); refreshStatus("'+dmUIdlist+'",msgClass);');

        } else if (status=="IP") {

            submitAction('INPICK',dmUIdlist,params,'debriefDocument.successCallBack("'+dmUIdlist+'"); refreshStatus("'+dmUIdlist+'",msgClass);');

        } else if (status=="DF") {

            params='&DELDATE='+document.getElementById("MD_DELDATE").value;
            params+='&DOCMASTLIST='+dmUIdlist;
            params+='&GRVNO='+document.getElementById("MD_GRVNO").value;
            params+='&WAYBILLNO='+document.getElementById("MD_WAYBILLNO").value;
            //      params+='&PAYMENTTYPE='+document.getElementById("MD_PAYMENTTYPE").value; 
            //      params+='&PAYMENTAMOUNT='+document.getElementById("MD_PAYMENTAMOUNT").value; 
            params+='&COMMENT='+encodeURIComponent(document.getElementById("MD_COMMENT").value.replace(/'/g,'').replace(/"/g,''));
            submitAction('DELFULL',dmUIdlist,params,'refreshStatus("'+dmUIdlist+'",msgClass);');
        
        } else if (status=="DP") {

            params='&DELDATE='+document.getElementById("MD_DELDATE").value;
            params+='&DOCMASTLIST='+dmUIdlist;
            params+='&GRVNO='+document.getElementById("MD_GRVNO").value;
            params+='&WAYBILLNO='+document.getElementById("MD_WAYBILLNO").value;
            //      params+='&PAYMENTTYPE='+document.getElementById("MD_PAYMENTTYPE").value; 
            //      params+='&PAYMENTAMOUNT='+document.getElementById("MD_PAYMENTAMOUNT").value; 
            params+='&COMMENT='+encodeURIComponent(document.getElementById("MD_COMMENT").value.replace(/'/g,'').replace(/"/g,''));

            //credit info.
            params+='&CLAIMNO='+document.getElementById("MD_CLAIMNO").value;
            params+='&DDUID='+convertElementToArray(document.getElementsByName("MD_DDUID[]"));
            params+='&AMENDEDQTY='+convertElementToArray(document.getElementsByName("MD_AMENDEDQTY[]"));
            params+='&ACCEPTQTY='+convertElementToArray(document.getElementsByName("MD_ACCEPTQTY"));
            params+='&REASONCODE='+document.getElementById('MD_REASONCODE').value;
            submitAction('DELPART',dmUIdlist,params,'refreshStatus("'+dmUIdlist+'",msgClass);');

        } else {
         // no change, orig value
        }
    },

    manageSetLink : function(dMUId,psmChildUId,psmParentUId) {
        var s=((psmParentUId===false)?document.getElementById('MS_STORE').value:psmParentUId);
        if (s=="") {
          alert('Please choose a Store in the search box first.');
          return false;
        } else {
            if (alreadySubmitted){
                alert('You have already clicked on Link Submit... If you are sure the link is not stored then you may click submit again after 2 minutes.');
                return;
            }
            alreadySubmitted=true;
    
            var postJS='refreshAssociation('+dMUId+',msgClass); debriefDocument.hideManageScreen();';
          AjaxRefreshWithResult('PSMPARENTUID='+s+'&PSMCHILDUID='+psmChildUId+'&DOCMASTID='+dMUId+'&ACTIONTYPE=SETLINK',
                              '<?php echo $ROOT.$PHPFOLDER ?>functional/transaction/manageLinkSubmit.php',
                              'alreadySubmitted=false; if(msgClass.type=="S") '+ postJS + ';',
                              'Please wait while request is processed...');
        }
    },

    isLoadedManage : false, // see WARNING comments against .load below

    showManageBulk : function() {

        var selectedArr = new Array();
        var no = 0;
        $('input[name="MANAGE_ITEM[]"]').each(function() {
            if($(this).attr('checked')){
                selectedArr[no] = $(this).val();
                no++;
            }
        });

        if(selectedArr.length==0){
            alert('No documents have been selected!');
        } else {
            debriefDocument.displayManage();
            var param = '?pAlias=&BULKACTION=1&DOCMASTARR[]=' + selectedArr.join('&DOCMASTARR[]=');
            $('#div_MANAGECONTENT').load('<?php echo $ROOT.$PHPFOLDER."functional/transaction/manageDocument.php" ?>'+param, debriefDocument.isLoadedManage=false);
        }

    },

    // does not create link, only uses a store
    manageUseLink : function(dMUId,psmParentUId) {

        if (alreadySubmitted){
                alert('You have already clicked on Link Submit... If you are sure the link is not stored then you may click submit again after 2 minutes.');
                return;
        }
        alreadySubmitted=true;
    
        var postJS='refreshAssociation('+dMUId+',msgClass); debriefDocument.hideManageScreen();';
        AjaxRefreshWithResult('PSMPARENTUID='+psmParentUId+'&DOCMASTID='+dMUId+'&ACTIONTYPE=USELINK',
                                        '<?php echo $ROOT.$PHPFOLDER ?>functional/transaction/manageLinkSubmit.php',
                                        'alreadySubmitted=false; if(msgClass.type=="S") '+ postJS + ';',
                                        'Please wait while request is processed...');
    },

    manageRemoveLink : function(dMUId,assocUId) {

        if (alreadySubmitted){
                alert('You have already clicked on Link Submit... If you are sure the link is not stored then you may click submit again after 2 minutes.');
                return;
        }
        alreadySubmitted=true;
    
        var postJS='$("#tr"+'+assocUId+').remove();';
        AjaxRefreshWithResult('ASSOCUID='+assocUId+'&DOCMASTID='+dMUId+'&ACTIONTYPE=REMOVELINK',
                            '<?php echo $ROOT.$PHPFOLDER ?>functional/transaction/manageLinkSubmit.php',
                            'alreadySubmitted=false; if(msgClass.type=="S") '+ postJS + ';',
                            'Please wait while request is processed...');
    },

    refreshStatus : function(dmUIdlist, msgClass) {

        var dmUIdArr = dmUIdlist.split(',');
        for(i =0 ; i < dmUIdArr.length; i++ ){
        dmUId = dmUIdArr[i];
        if (document.getElementById('status'+dmUId)) {
            document.getElementById('status'+dmUId).innerHTML=msgClass.identifier;
            $('#DMUID_'+dmUId).closest('tr').css({'background':'#CCCCCC'});
            $('#DMUID_'+dmUId).closest('tr').children('td').css({'background':'#CCCCCC'});
            $('#APANEL_'+dmUId).remove();   //remove single options
            $('#CHECKITEM_'+dmUId).remove();  //remove checkboxes
    
        }
        }
        debriefDocument.hideManageScreen();
    
    },

    refreshAssociation : function(dmUId, msgClass) {

        try {
            eval(msgClass.identifier);
        } catch (e) {
            alert('identifier could not be eval() in refreshAssociation! Please contact RT.');
            return false;
        }
    
        if (document.getElementById('delloc'+dmUId)) {
            document.getElementById('delloc'+dmUId).innerHTML=msgClassIdentifier.delloc;
        }
        if (document.getElementById('delarea'+dmUId)) {
            document.getElementById('delarea'+dmUId).innerHTML=msgClassIdentifier.delarea;
        }
        if (document.getElementById('delday'+dmUId)) {
            document.getElementById('delday'+dmUId).innerHTML=msgClassIdentifier.delday;
        }

    },

    printQuotationProformaInvoice : function(dmUId) {
        window.open('<?php echo $ROOT.$PHPFOLDER; ?>functional/presentations/presentationManagement.php?TYPE=quotation&FINDNUMBER='+dmUId+'','myQuotationProcessing','scrollbars=yes,width=750,height=600,resizable=yes');
    },
    
    printQuotationJobCard : function(dmUId) {
        window.open('<?php echo $ROOT.$PHPFOLDER; ?>functional/transaction/jobCard.php?DOCMASTID='+dmUId+'','myQuotationProcessing','scrollbars=yes,width=750,height=600,resizable=yes');
    },
    
    printQuotationCompletionCertificate : function(dmUId) {
        window.open('<?php echo $ROOT.$PHPFOLDER; ?>functional/transaction/quotationCompletionCertificate.php?DOCMASTID='+dmUId+'','myQuotationProcessing','scrollbars=yes,width=750,height=600,resizable=yes');
    },

    copyQuotationDocument : function(dmUId) {
        var parentBody = window.parent.document.body;
        $("#content", parentBody).attr('src','<?php echo HOST_SURESERVER_AS_NEWUSER.$PHPFOLDER; ?>functional/transaction/quotationCapture.php?COPYDOCUMENT=Y&DOCTYPE=27&DOCMASTID='+dmUId);
    },

    isLoadedManageSF : false,

    showManageSF : function(dmUId) {

        $('#div_MANAGECONTENT').html("<center style='background-color:white;'><img src='<?php echo $DHTMLROOT.$PHPFOLDER ?>images/loading.gif' /></center>");
      
          // unfortunately the modal layer in parent doesnt work with z-index here
          $('#viewTracking').append("<div id='vt_modalLayer' "+
                                                              "			style='position:absolute; top:0; left:0; width:100%; height:100%; z-index:99; background-image:url(\"<?php echo $DHTMLROOT.$PHPFOLDER; ?>images/modal-layer.png\");'>"+
                                                              "</div>");
      
          $('#div_MANAGE,#div_MANAGECONTENT').css({'height':'','max-height':''}); // reset the height so max-height doesnt use the last size
          $('#div_MANAGE').css({'width':'550px','marginTop':f_scrollTop()+20,'marginLeft':f_clientWidth()/2-200,'max-height':f_clientHeight()-100}).show(700,
              function(){
                              // $('#div_MANAGECONTENT').css({'height':$('#div_MANAGE').css('max-height').replace('px','')-80});
              }
              );
          $('#m_action').prop("onclick", null).attr("onclick", null); // remove the action event, returning val will set the action, also acts as a safety
          $('#m_action').unbind('click');
      
        // WARNING : The .load() jquery event causes scripts to be executed twice in IE. The page is not fetched twice.
        // 					 The content is passed to .html() prior to scripts being removed. This executes the script blocks before they are discarded and again after.
        //					 This is because JQuery uses the html() to parse the innerHTML when it is received which executes the script and again when it is inserted into DOM
          $('#div_MANAGECONTENT').load('<?php echo $ROOT.$PHPFOLDER."functional/transaction/manageDocumentSF.php?pAlias=&DOCMASTID=" ?>'+dmUId,
                                                                   debriefDocument.isLoadedManageSF=false);
      
    },

    successCallBack : function(dmUIdlist, openPrint){

        if (typeof openPrint == 'undefined') openPrint=true;
    
        var dmUIdArr = dmUIdlist.split(',');
        if(dmUIdArr.length == 0){
            alert('Success Call back failure!');
        } else if(dmUIdArr.length == 1){
        
            if (openPrint) window.open('<?php echo $ROOT.$PHPFOLDER; ?>functional/presentations/presentationManagement.php?TYPE=""&FINDNUMBER='+dmUIdlist+'','myQuotationProcessing','scrollbars=yes,width=750,height=600,resizable=yes');
        
        } else {
            if (openPrint) window.open('<?php echo $ROOT.$PHPFOLDER; ?>functional/transaction/documentCard.php?BULKACTION=1&DOCMASTID='+dmUIdlist+'','myOrderProcessing','scrollbars=yes,width=750,height=600,resizable=yes');
        }
    
    },

    submitAction : function(actionType,dmUIdlist,extraParams,postJS) {

        var params = '';
        var dmUIdArr = dmUIdlist.split(',');
      
        if (alreadySubmitted){
                alert('You have already clicked on submit... If you are sure the capture has NOT been stored then you may click submit again after 2 minutes.');
                return;
        }
      
        if(dmUIdArr.length == 0){
          alert('Document selection failure on screen!');
        } else if(dmUIdArr.length == 1){
          params='ACTIONTYPE='+actionType+'&DOCMASTID='+dmUIdArr[0]+'&'+extraParams;
        } else {
          //enable bulk action
          params='ACTIONTYPE='+actionType+'&BULKACTION=1&DOCMASTARR[]='+dmUIdArr.join('&DOCMASTARR[]=')+'&'+extraParams;
        }
      
        alreadySubmitted=true;
        AjaxRefreshWithResult(params,
                              '<?php echo $ROOT.$PHPFOLDER ?>functional/transaction/viewTrackingSubmit.php',
                              'alreadySubmitted=false; if(msgClass.type=="S"){'+ postJS + '};',
                              'Please wait while request is processed...');

    },
  
}

// these reassign legacy embedded methods to point to this namespaced object, as it was the safest way to maintain compatibility as there are many JS scripts that depend on these
// and its probably not not the right thing to safely start changing all those dependent scripts as yet, but rather just enforce that new scripts should call only namespaced object
// directly
var alreadySubmitted=false; // also put this in here as we may not necessarily be on the viewTracking.php view
showManageBulk = debriefDocument.showManageBulk;
showLink = debriefDocument.showLink;
manageAccept = debriefDocument.manageAccept;
manageSetLink = debriefDocument.manageSetLink;
manageUseLink = debriefDocument.manageUseLink;
manageRemoveLink = debriefDocument.manageRemoveLink;
refreshStatus = debriefDocument.refreshStatus;
refreshAssociation = debriefDocument.refreshAssociation;
printQuotationProformaInvoice = debriefDocument.printQuotationProformaInvoice;
printQuotationJobCard = debriefDocument.printQuotationJobCard;
printQuotationCompletionCertificate = debriefDocument.printQuotationCompletionCertificate;
copyQuotationDocument = debriefDocument.copyQuotationDocument;
showManageSF = debriefDocument.showManageSF;
successCallBack = debriefDocument.successCallBack;
submitAction = debriefDocument.submitAction;