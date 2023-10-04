//POP UP MESSAGE LAYER
//TYPES: info | error | success | warn | general
//No layer on page required! - update
//Fix on dropdown and background click to close - update

//POP UP SETTINGS
var boxDefaultWidth = 500;
var boxLyrID = 'UInBoxLyr';
var boxID = 'UInBox';
var boxInnerID = 'UInBoxInner';

function popBoxClose(){
	jQuery('#'+boxLyrID).remove();
	jQuery('#UInBoxLyrTrans').remove();
}

function popBox(text,css,width,cClose){
	
	var css = (typeof(css) != 'undefined') ? css.toLowerCase() : 'info';
	var width = (typeof(width) != 'undefined') ? width : boxDefaultWidth;	
	var cClose = (typeof(cClose) != 'undefined') ? true : false;
	var InnerBox = '<div id="UInBoxLyr"><div id="'+boxInnerID+'" class="UIBoxType_'+css+'"><div id="UInBoxClose" style="width:'+((width)+43)+'px;" align="right"><a href="javascript:popBoxClose()"></a></div><div style="clear:both"></div><span class="UIBoxIcon"></span>'+text+'</div></div><div id="UInBoxLyrTrans" class="UIBoxType_'+css+'"></div>';
	jQuery(InnerBox).appendTo('body');
	jQuery('#'+boxLyrID).css('display','block');
	var innerObj = jQuery('#'+boxInnerID);
	var margintop = (innerObj.height()+45)/2;
	innerObj.css({'width':width+'px','margin-left':-(width/2),'margin-top':-margintop});
	if(!cClose){jQuery('#UInBoxLyrTrans').bind('click', function() {popBoxClose();});}
	
}

