// regular expressions
var regex_date = new RegExp(/^([0-9]{4})\-([0-9]{2})\-([0-9]{2})$/);

String.prototype.trim = function() {
	return this.replace(/^[ ]+/g,"").replace(/[ ]+$/g,"");
}

String.prototype.fulltrim = function() {
	return this.replace(/(?:(?:^|\n)\s+|\s+(?:$|\n))/g,"").replace(/\s+/g," ");
}

String.prototype.right = function(count) {
	return this.substr(this.length - count);
}

String.prototype.left = function(count) {
	return this.substr(0, count);
}

Array.prototype.findIndex = function(value){
	var ctr = "";
	for (var i=0; i < this.length; i++) {
		// use === to check for Matches. ie., identical (===), ;
		if (this[i] == value) {
			return i;
		}
	}
	return ctr;
};

Array.prototype.unique = function () {
	var r = new Array();
	o:for(var i = 0, n = this.length; i < n; i++)
	{
		for(var x = 0, y = r.length; x < y; x++)
		{
			if(r[x]==this[i])
			{
				continue o;
			}
		}
		r[r.length] = this[i];
	}
	return r;
}

// for non array prototypes
function findFormFldIndex(fld,value){
	var ctr = "";
	for (var i=0; i < fld.length; i++) {
		if (fld[i].value == value) {
			return i;
		}
	}
	return ctr;
};

// this doesn't preserve blanks in checkboxes. ie. an unchecked tickbox is not processed
function convertElementToArray(fld) {
	var arr = new Array();
	for (var i=0; i<fld.length; i++) {
		if ((fld[i].checked) || (fld[i].type=="text") || (fld[i].type=="number") || (fld[i].type=="hidden") || (fld[i].type=="select-one")) arr.push(fld[i].value);
	} 
	return arr;
}
//this doesn't preserve blanks. ie. an unchecked tickbox is not processed, but is able to handle DDs
// DEPRECATED. Shouldn't use because one above now handles select DDs
function convertElementToArrayOther(fld) {
	var arr = new Array();
	for (var i=0; i<fld.length; i++) {
		arr.push(fld[i].value);
	} 
	return arr;
}
// this DOES preserve blanks in checkboxes, and you can specify blank val to use
function convertElementToArrayEnforceBlankValue(fld,blankVal) {
	var arr = new Array();
	for (var i=0; i<fld.length; i++) {
		if ((fld[i].checked) || (fld[i].type=="text") || (fld[i].type=="number") || (fld[i].type=="hidden") || (fld[i].type=="select-one")) arr.push(fld[i].value);
		else arr.push(blankVal);
	} 
	return arr;
}

function getElement(aID){
        return (document.getElementById)?document.getElementById(aID) : document.all[aID];
    }

function adjustMyFrameHeight(){
  /*
   * DEAD FUNCTION - FIXED WITH WRAP LAYER FOR IFRAME.
   */
  /*
	// force garbage collection in IE so no old DOM is left
	if (typeof(CollectGarbage) == "function") CollectGarbage();
	try {
		// get largest
		positions=document.getElementsByName('endpositioner');
		var pos=0;
		for (i=0; i<positions.length; i++) {
			if (positions[i].offsetTop>pos) pos=positions[i].offsetTop;
		}
		if (pos==0) pos=800;
		
		o = parent.document.getElementsByTagName('iframe');
		for(i=0;i<o.length;i++){
			if (/\bautoHeight\b/.test(o[i].className)){
					o[i].height = pos+22;
			}
		}
		// if the msgbox modal div is displaying, then resize it too
		if (document.getElementById('mainmodaldiv')) {
			if (document.getElementById('mainmodaldiv').style.display=='block') document.getElementById('mainmodaldiv').style.height=pos+22;
		}
		if (parent.document.getElementById('mainmodaldiv')) {
			if (parent.document.getElementById('mainmodaldiv').style.display=='block') parent.document.getElementById('mainmodaldiv').style.height=pos+22;
		}
	}  catch (e) {
					//alert('error occurred while resizing adjustMyFrameHeight(). Positioner Image not Found');
	   }
	   */
}

//Execute JavaScript from returned AJAX calls, bug fix for Chrome/FireFox
function ExtExeAjaxJS(output_to_parse) {
  if (output_to_parse != '') {
    var script = "";
    output_to_parse = output_to_parse.replace(/<script[^>]*>([\s\S]*?)<\/script>/gi, function() {
          if (output_to_parse !== null)
            script += arguments[1] + '\n';
          return '';
        });
    if (script) {
      if (window.execScript) {
        window.execScript(script);
      } else {
        window.setTimeout(script, 0);
      }
    }
  }
}

function AjaxRefresh(extraparams,pageSrc,pageDest,msg, successCallback){
  var params='PAGEDEST='+pageDest;
  if (extraparams!='') params=params+'&'+extraparams;
  document.getElementById(pageDest).innerHTML='<BR><BR>'+msg;
  if(typeof parent.showMsgBoxSystemFeedback == 'function') { parent.showMsgBoxSystemFeedback(msg); }
  $.ajax({
    url: pageSrc,
	  global: false,
	  type: 'POST',
      data: params,
      dataType: 'html',
	  cache: false,
	  success: function(retVal){
	  	// try block is necessary because if you switch tabs too quickly, the page might come back when the div tag is no longer present and pageDest will crash.
	  	try {
	  	document.getElementById(pageDest).innerHTML=retVal;
	  	if(typeof parent.hideMsgBoxSystemFeedback == 'function') { parent.hideMsgBoxSystemFeedback(msg); }	  	
	  		adjustMyFrameHeight();
	  		ExtExeAjaxJS(retVal);	//execute any JavaScript
	  	if (successCallback!='') eval(successCallback);
	  	} catch (err) { };
	  },
      error: function(XMLHttpRequest, textStatus, errorThrown) {
        //alert(textStatus+' - '+errorThrown);
        ajaxErrorHandler(XMLHttpRequest, textStatus, errorThrown);
        if(typeof parent.hideMsgBoxSystemFeedback == 'function') { parent.hideMsgBoxSystemFeedback(msg); }       
	  }
  });	
}

//submit with return result
function AjaxRefreshWithResult(extraparams,pageSrc,procSuccessCallback,msg){
  var params='X=';
  if (extraparams!='') params=params+'&'+extraparams;
  if(typeof parent.showMsgBoxSystemFeedback == 'function') { parent.showMsgBoxSystemFeedback(msg); }
  $.ajax({
	  url: pageSrc,
	  global: false,
	  type: "POST",
      data: params,
      dataType: "html",
	  cache: false,
	  timeout: 120000,
	  success: function(retVal){
	    alreadySubmitted=false;
	    // storeForm popup and other screens sometimes gets called directly so Messages dont have a parent
	    if(typeof parent.hideMsgBoxSystemFeedback == 'function') mF=parent.hideMsgBoxSystemFeedback; else if(typeof hideMsgBoxSystemFeedback == 'function') mF=hideMsgBoxSystemFeedback; else mF=false;
	    if(typeof parent.showMsgBoxInfo == 'function') mI=parent.showMsgBoxInfo; else if(typeof showMsgBoxInfo == 'function') mI=showMsgBoxInfo; else mI=false;
	    if(typeof parent.showMsgBoxError == 'function') mE=parent.showMsgBoxError; else if(typeof showMsgBoxError == 'function') mE=showMsgBoxError; else mE=false;
	    
	    if(mF!==false) { mF(msg); }
	    
	  	try {
			  //alreadySubmitted=false;
			} catch (err) {};
	  	try {
	  		
	  		eval(retVal); // run the returned JS. It creates an ErrorTO called msgClass
	  		if ((msgClass.type=='S') || (msgClass.type=='I')) mI(msgClass.description);
	  		else if ((msgClass.type=='E') || (msgClass.type=='W')) mE(msgClass.description+' - '+msgClass.identifier);
	  	} catch (err) { alert('FAILED to process request!\n\nPlease contact KwelangaSolutions Management before continuing.\n\n'+err.description+"\n"+retVal.replace(/<.*?>/g,'')); return false;}
	  	try {
	  		if (procSuccessCallback!='') eval(procSuccessCallback); // execute the proc on ajax success, NOT result success
	  	} catch (err) { alert('FAILED to run post processing Processes!\n\nPlease contact KwelangaSolutions Management before continuing.\n\n'+err.description+"\n"+retVal.replace(/<.*?>/g,'')); return false;}
	  },
      error: function(XMLHttpRequest, textStatus, errorThrown) {
        ajaxErrorHandler(XMLHttpRequest, textStatus, errorThrown);
        if(typeof parent.hideMsgBoxSystemFeedback == 'function') { parent.hideMsgBoxSystemFeedback(msg); }
        try {
          alreadySubmitted=false;
        } catch (err) {};
          
	  }
	});		
}

function AjaxRefreshHTML(extraparams,pageSrc,pageDest,msg, successCallback){
  var params='PAGEDEST='+pageDest;
  if (extraparams!='') params=params+'&'+extraparams;
  if (msg!='') {
	  var outputDiv=getBestDestinationDiv(pageDest);
	  if (isDefined(outputDiv)) {
		  outputDiv.innerHTML='<BR>'+msg;
	  }
  }
  if(typeof parent.showMsgBoxSystemFeedback == 'function') { parent.showMsgBoxSystemFeedback(msg); }
  $.ajax({
	  url: pageSrc,
	  global: false,
	  type: 'POST',
      data: params,
      dataType: 'html',
	  cache: false,
	  success: function(retVal){
	    // have to put this check in here because synch dashboard refreshes the page and div may not be present temporarily
	    var outputDiv=getBestDestinationDiv(pageDest);
	    if (isDefined(outputDiv)) {
	  		outputDiv.innerHTML=retVal;
	  	} 
	  	if(typeof parent.hideMsgBoxSystemFeedback == 'function') parent.hideMsgBoxSystemFeedback(msg);
	    try {
	    	eval(successCallback);
		 } catch (err) {};
	  	adjustMyFrameHeight();
	  },
  	  error: function(XMLHttpRequest, textStatus, errorThrown) {
		  // hide the box for now so that processing doesn;t stop ...  alert(textStatus+' - '+errorThrown+' - pageDest:'+pageDest);
		  var outputDiv=getBestDestinationDiv(pageDest);
	      if (isDefined(outputDiv)) {
			  outputDiv.innerHTML=textStatus+' - '+errorThrown;
		  }
		  try {
			  alreadySubmitted=false;
		  } catch (err) {};
		  if(typeof parent.hideMsgBoxSystemFeedback == 'function') parent.hideMsgBoxSystemFeedback(msg);
	  }
  });	
}


function ajaxErrorHandler(Request, textStatus, errorThrown){
  
  //textStatus = timeout | error | abort
  if(textStatus == 'timeout'){
    alert("ERROR: Submit Timeout, Try again \n or check your internet connection.");
  } else {
  
    var resTxt = Request.responseText; //html response, strip title for error code, 404, 500 etc.
    //alert(XMLHttpRequest.responseText); //debug html error returned.

    //html error code: ie 404 Not Found.
    var cstart = resTxt.search("<title>") + 7;
    var cend = resTxt.search("</title>");

    //Full error description
    var dstart = resTxt.search("<p>") + 3;
    var dend = resTxt.search("</p>");          
    
    if (cstart > 0 && cend > 0){
      alert('Msg from ajaxErrorHandler:'+textStatus.toUpperCase() + " - " + resTxt.substring(cstart, cend) + "\n" + resTxt.substring(dstart, dend));
    } else {
      alert('Msg from ajaxErrorHandler:'+textStatus.toUpperCase());  //handle abort or other generic error.
    }
  }

}

// for multiple steps within page. Step divs must be named "step1" step2 etc...
function toggleSteps(activateStep, root) {
	// the content divs
	$("div[id*='step']").css({display:'none'});
	$('#step'+activateStep).css({display:'block'});
	
	// the icons
	$(".tabDivStep").css({backgroundImage:"url("+root+"images/step-unsel.png)",fontWeight:'normal',textDecoration:'none',color:'#2288ff'});
    $("#tds"+activateStep).css({backgroundImage:"url("+root+"images/step-sel.png)",fontWeight: 'bold',textDecoration:'underline',color:'#ff9933'});
    
    adjustMyFrameHeight();
}

// get position of passed object
function findPosX(obj)
{
   var curleft = 0;
   if(obj.offsetParent)
   while(1) {
      curleft += obj.offsetLeft;
      if(!obj.offsetParent)
         break;
      obj = obj.offsetParent;
   }
   else if(obj.x)
      curleft += obj.x;
   return curleft;
}
function findPosY(obj)
{
   var curtop = 0;
   if(obj.offsetParent)
   while(1) {
      curtop += obj.offsetTop;
      if(!obj.offsetParent)
         break;
      obj = obj.offsetParent;
   }
   else if(obj.y)
      curtop += obj.y;
   return curtop;
}
// end get position of passed object

// get scroll bar position and others

function f_clientWidth() {
	return f_filterResults (
		window.innerWidth ? window.innerWidth : 0,
		document.documentElement ? document.documentElement.clientWidth : 0,
		document.body ? document.body.clientWidth : 0
	);
}
function f_clientHeight() {
	var pos=0;
	if (document.getElementById('endpositioner')) pos=document.getElementById('endpositioner').offsetTop;
	pos2=f_filterResults (
		window.innerHeight ? window.innerHeight : 0,
		document.documentElement ? document.documentElement.clientHeight : 0,
		document.body ? document.body.clientHeight : 0
	);
	if (pos>pos2) return pos; else return pos2;
}
function f_scrollLeft() {
	return f_filterResults (
		window.pageXOffset ? window.pageXOffset : 0,
		document.documentElement ? document.documentElement.scrollLeft : 0,
		document.body ? document.body.scrollLeft : 0
	);
}
//p=true, use parent notation
function f_scrollTop(p) {
	if(p)
		return f_filterResults (
			window.pageYOffset ? window.pageYOffset : 0,
			parent.document.documentElement ? parent.document.documentElement.scrollTop : 0,
			parent.document.body ? parent.document.body.scrollTop : 0
		);
	else
		return f_filterResults (
				window.pageYOffset ? window.pageYOffset : 0,
				document.documentElement ? parent.document.documentElement.scrollTop : 0,
				document.body ? document.body.scrollTop : 0
			);
}
function f_filterResults(n_win, n_docel, n_body) {
	var n_result = n_win ? n_win : 0;
	if (n_docel && (!n_result || (n_docel > n_result)))
		n_result = n_docel;
	return n_body && (!n_result || (n_body) > n_result) ? n_body : n_result;
}
// end get scroll bar position and others

// set scroll position
//p=true, use parent notation
function s_scrollPosition(offset, p) {
	if(p) {
			if (window.pageYOffset) window.pageYOffset = offset;
			else if (parent.document.documentElement) parent.document.documentElement.scrollTop = offset;
			else parent.document.body.scrollTop = offset;
	} else {
		if (window.pageYOffset) window.pageYOffset = offset;
		else if (document.documentElement) document.documentElement.scrollTop = offset;
		else document.body.scrollTop = offset;
	}
}
// end set scroll position

// to output HTML tags etc. It just leaves off the first <
function HTMLEncode(str){
     var aStr = str.split(''),
         i = aStr.length,
         aRet = [];

     while (--i) {
      var iC = aStr[i].charCodeAt();
       if (iC < 65 || iC > 127 || (iC>90 && iC<97)) {
        aRet.push('&#'+iC+';');
       } else {
        aRet.push(aStr[i]);
       }
    }
    return aRet.reverse().join('');
   }

// to perform an action when enter is pressed on a field eg.. auto submit when logging in
// For each field which should submit the form when they hit enter add an onKeyPress attribute like this:
// password: <INPUT NAME=password TYPE=PASSWORD SIZE=10 onKeyPress='return submitenter(this,event,"alert(\"hello\");")'>
function submitenter(myfield,e,jsAction)
{
	var keycode;
	if (window.event) keycode = window.event.keyCode;
	else if (e) keycode = e.which;
	else return true;
	
	if (keycode == 13){
	   exec(jsAction);
	   return false;
	} else return true;
}

function createCookie(name,value,days) {
	if (days) {
		var date = new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));
		var expires = "; expires="+date.toGMTString();
	}
	else var expires = "";
	var val=name+"="+value+expires+"; path=/";
	if ((document.cookie.length+val.length)>4000) alert('Total Cookie size exceeds 4Kb. The Saved session may not be reliable when restoring.'); // IE and other browsers limit
	document.cookie = val;
}

function readCookie(name) {
	var nameEQ = name + "=";
	var ca = document.cookie.split(';');
	if (document.cookie.length>4000) alert('Total Cookie size exceeds 4Kb. The Saved session may not be reliable when restoring.'); // IE and other browsers limit
	for(var i=0;i < ca.length;i++) {
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1,c.length);
		if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
	}
	return null;
}
		
function eraseCookie(name) {
	createCookie(name,"",-1);
}

function isDefined(fld) {
	if ((fld=="undefined") || (fld=="null") || (fld==null)) return false;
	else return true;
}
function getBestDestinationDiv(fldId) {
	var outputDiv=document.getElementById(fldId);
	if (!isDefined(outputDiv)) {
		try { 
			outputDiv=content.document.getElementById(fldId);
		} catch (e) {
			outputDiv=null;
		}
	}
	if (!isDefined(outputDiv)) {
		try { 
			outputDiv=parent.document.getElementById(fldId);
		} catch (e) {
			outputDiv=null;
		}
	}
	return outputDiv;
}
function isNumeric(val) {
	return !isNaN(parseFloat(val)) && isFinite(val);
}