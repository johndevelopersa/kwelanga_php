<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');

// this message class should only be added once to an application (otherwise conflict of names will occur), and the top level main page
class Messages {

	// the modal layer for user popup operations below the msgbox modal layer, not default, called and implemented manually for each screen
	// displayed within the iframe so does not need to be called with parent. notation, and top padded
	public static function msgboxSubModalLayer() {
		global $PHPFOLDER; global $DHTMLROOT;
		echo "<DIV id='mainsubmodaldiv'
				   style='display:none;
							padding:0px;
						  position:absolute;
						  top:1px;
						  left:1px;
						  width:1px;
						  height:1px;
						  z-index:200;
						  background-image:url(\"".$DHTMLROOT.$PHPFOLDER."images/modal-layer.png\"); '>
			  </DIV>";
		print("<scr"."ipt type='text/javascript' defer>");
		print("function showSubMsgBoxModal(){
				$('#mainsubmodaldiv').width(f_clientWidth());
				$('#mainsubmodaldiv').height(f_clientHeight()+20); // excludes masthead
				document.getElementById('mainsubmodaldiv').style.display='block';
			   }
			   function hideSubMsgBoxModal() {
				document.getElementById('mainsubmodaldiv').style.display='none';
			   }");
		print("</scr"."ipt>");
	}

	// the modal layer just below the msgbox z-index level
	public static function msgboxModalLayer() {
		global $PHPFOLDER; global $DHTMLROOT;
		echo "<DIV id='mainmodaldiv'
				   style='display:none;
						  position:absolute;
						  top:110px;
						  left:1px;
						  width:1px;
						  height:1px;
						  z-index:300;
						  background-image:url(\"".$DHTMLROOT.$PHPFOLDER."images/modal-layer.png\"); '>
			  </DIV>";
		print("<scr"."ipt type='text/javascript' defer>");
		print("function showMsgBoxModal(){
				document.getElementById('mainmodaldiv').style.width=f_clientWidth();
				document.getElementById('mainmodaldiv').style.height=f_clientHeight()+20; // excludes masthead
				document.getElementById('mainmodaldiv').style.display='block';
			   }
			   function hideMsgBoxModal() {
				document.getElementById('mainmodaldiv').style.display='none';
			   }");
		print("</scr"."ipt>");
	}

	public static function msgBoxInfo() {


	  //REPLACEMENT info popup: success popup css3, all browsers centered.
	  echo <<<EOF
<script type="text/javascript" defer>
function showMsgBoxInfo(text){
	popBox('<span style="color:#000">'+text+'</span>','success');
}
function hideMsgBoxInfo() {
	popBoxClose();
}
</script>
EOF;

	}

	public static function msgBoxError() {


	  //REPLACEMENT error popup: css3, all browsers centered.
	  echo <<<EOF
<script type="text/javascript" defer>
function showMsgBoxError(text){
	popBox('<span style="color:#000">'+text+'</span>','error');
}
function hideMsgBoxError() {
	popBoxClose();
}
</script>
EOF;
	}

	public static function msgBoxInput() {
		global $ROOT; global $PHPFOLDER; global $DHTMLROOT;
		$containerName=OBJ_NAME_GLOBAL_MSGBOX_INPUT;
		$textName=OBJ_NAME_GLOBAL_MSGBOX_INPUT."Text";
		print("<div id='".$containerName."outer'
					style='display:none; position:absolute; z-index: 301;' >
				<DIV id='".$containerName."'
					 class='msgBox'
					 style='overflow:auto;
						   position:absolute;
						   display:none;
						   width:420px;
						   margin-left:auto;
						   margin-right:auto;
						   background-color:#DDDDFF;
						   border-style:solid;
						   border-width:2px;
						   border-color:#AABBFF;
						   z-index: 201;
						   font-size: 11px;
					   	   font-family: arial;
					       color: #111111;
					       text-align: center;' >
				   <div style='float:right;'>
					 <!--<A href='javascript:hideMsgBoxInput();'>
					   <IMG src=\"".$DHTMLROOT.$PHPFOLDER."images/close-button.jpg\" width=20; align=\"top\"/>
					 </A>/-->
				   </div>
				   <div id='".$textName."'></div>
			   </DIV><br><br></div>");
		print("<scr"."ipt type='text/javascript' defer>");
		print("function showMsgBoxInput(text, type, callback){
				document.getElementById('".$containerName."outer').style.top='150px';
				document.getElementById('".$containerName."outer').style.left=f_clientWidth()/3;
				document.getElementById('".$textName."').innerHTML=text+'<BR><BR><INPUT id=\"{$containerName}Input\" type='+type+' value=\"\" /><input type=\"button\" class=\"submit\" value=\"Ok\" onclick=\"hideMsgBoxInput(); '+callback+'\" />';
				document.getElementById('".$containerName."').style.display='block';
				document.getElementById('".$containerName."outer').style.display='block';
				adjustMyFrameHeight();
				if(typeof showMsgBoxModal == 'function') showMsgBoxModal();
			   }
			   function hideMsgBoxInput() {
				document.getElementById('".$containerName."').style.display='none';
				document.getElementById('".$containerName."outer').style.display='none';
				if(typeof hideMsgBoxModal == 'function') hideMsgBoxModal();
			   }
			   function getMsgBoxInputValue() {
				if(document.getElementById('{$containerName}Input')) {
					return document.getElementById('{$containerName}Input').value;
				} else {
					return '';
				}
			   }
			   function clearMsgBoxInputValue() {
				if(document.getElementById('{$containerName}Input')) {
					document.getElementById('{$containerName}Input').value='';
				}
			   }");
		print("</scr"."ipt>");
	}

	public static function msgBoxContent() {


		echo <<<EOF
		<script type='text/javascript' >
//POP UP MESSAGE LAYER
//TYPES: info | error | success | warn | general
//No layer on page required! - update
//Fix on dropdown and background click to close - update

//POP UP SETTINGS
var boxDefaultWidth = 540;
var boxLyrID = 'UInBoxLyr';
var boxID = 'UInBox';
var boxInnerID = 'UInBoxInner';

function popBoxClose(){
	jQuery('#'+boxLyrID).remove();
	jQuery('#UInBoxLyrTrans').remove();
}

function popBox(text,css,width){
	var css = (typeof(css) != 'undefined') ? css.toLowerCase() : 'info';
	var width = (typeof(width) != 'undefined') ? width : boxDefaultWidth;
	var InnerBox = '<div id="UInBoxLyr"><div id="'+boxInnerID+'" class="UIBoxType_'+css+'"><div id="UInBoxClose" style="width:'+(parseInt(width)+8)+'px;" align="right"><a href="javascript:popBoxClose()"></a></div><div style="clear:both"></div><span class="UIBoxIcon"></span>'+text+'</div></div><div id="UInBoxLyrTrans" class="UIBoxType_'+css+'"></div>';
	jQuery(InnerBox).appendTo('body');
	jQuery('#'+boxLyrID).css('display','block');
	var innerObj = jQuery('#'+boxInnerID);
	var margintop = (innerObj.height()+45)/2;
	innerObj.css({'width':width+'px','margin-left':-((width/2)+20),'margin-top':-margintop});
	jQuery('#UInBoxLyrTrans').bind('click', function() {popBoxClose();});

}

function showMsgBoxContent(text){
	popBox(text,'general');
}

</script>
EOF;

	}


	public static function msgBoxSystemFeedback() {
	  global $PHPFOLDER;
		$containerName=OBJ_NAME_GLOBAL_MSGBOX_AJAX;
		$textName=OBJ_NAME_GLOBAL_MSGBOX_AJAX."Text";
		print("<DIV id='".$containerName."'
					class='msgBoxAjax'
					style=\"display:none;
						   overflow:auto;
position:absolute;top:0px;left:50%;width:300px;margin-left:-125px;text-align:left;padding:7px 15px 7px 10px;
color:#444;background:#ffe402;background-image:url('".HOST_SURESERVER_AS_NEWUSER.$PHPFOLDER."images/uiload.gif');background-repeat:no-repeat;
background-position:300px center;border:1px solid #968a28;border-top:0px;
font-size:11px;font-family:Verdana, Arial, Helvetica, sans-serif;
-webkit-border-bottom-right-radius: 6px;
-moz-border-bottom-right-radius: 6px;
border-bottom-right-radius: 6px;
-webkit-border-bottom-left-radius: 6px;
-moz-border-bottom-left-radius: 6px;
border-bottom-left-radius: 6px;
-webkit-box-shadow: 0px 1px 3px rgba(0,0,0,.4);
-moz-box-shadow: 0px 1px 3px rgba(0,0,0,.4);
box-shadow: 0px 1px 3px rgba(0,0,0,.4);z-index:600\" >
				   <div id='".$textName."'></div>
			   </DIV>");
		echo '<script type="text/javascript" defer>';
		print("var msgArr = new Array();");
		print("function showMsgBoxSystemFeedback(text){
				msgArr.push(text);
				document.getElementById('".$textName."').innerHTML=text;
				document.getElementById('".$containerName."').style.display='block';
			   }
			   function hideMsgBoxSystemFeedback(text) {
			   	var dummyArr=[];
			    if(msgArr.length<=1) {
			    	msgArr=[];
					document.getElementById('".$containerName."').style.display='none';
	  			} else {
					var foundCnt=0;
	  				for (i=0; i<msgArr.length; i++) {
					  // prevent duplicate removals of same message. Often generic messages get passed.
	  				  if ((msgArr[i]!=text) || (foundCnt>0)) {
							 dummyArr.push(msgArr[i]);
					  } else {
					  	foundCnt++;
					  }
	  				}
	  				msgArr=dummyArr;
	  				document.getElementById('".$textName."').innerHTML=msgArr[0];
				  }
			   }
			   function hideMsgBoxSystemFeedbackAll() {
			   		document.getElementById('".$textName."').innerHTML='';
			   		msgArr=[];
					document.getElementById('".$containerName."').style.display='none';
			   }");
		echo '</script>';
	}

	public static function tipBox() {
	  	echo "<div
			   id=\"TipBox\"
			   style=\"
			      display:none;
			      position:absolute;
			      font-size:12px;
			      font-weight:bold;
			      font-family:verdana;
			      border:#72B0E6 solid 1px;
			      padding:15px;
			      color:#1A80DB;
			      background-color:#FFFFFF;\">
			</div>";
		print("<scr"."ipt type='text/javascript' defer>");
		print("
				var tipBoxId;
				function displayTip(me,offX,offY,content) {
				   var tipO = me;
				   tipBoxId = document.getElementById('TipBox');
				   var offset = $(me).offset();
				   var x = offset.left;
				   var y = offset.top;
				   oi = window.frames['content'].document;
				   scrollTop = oi.documentElement.scrollTop || oi.body.scrollTop;
				   tipBoxId.style.left = String(parseInt(x + offX) + 'px');
				   tipBoxId.style.top = String(parseInt((y-scrollTop) + offY) + 'px');
				   tipBoxId.innerHTML = content;
				   tipBoxId.style.display = \"block\";
				   tipO.onmouseout = hideTip;
				}
				function hideTip() { tipBoxId.style.display = \"none\"; }");
		print("</scr"."ipt>");
	}

}
?>
