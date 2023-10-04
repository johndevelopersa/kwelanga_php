<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');

class IntelliDDElement {

	private static function getJSShow ($tagId) {
		return "function {$tagId}Show() {
			  	// display the box in correct position
				var div=document.getElementById('{$tagId}SEARCHDIV');
				var fld=document.getElementById('{$tagId}SEARCH');
				var imgUp=document.getElementById('{$tagId}IMGUP');
				var imgDown=document.getElementById('{$tagId}IMGDOWN');
				imgDown.style.display='none';
				imgUp.style.display='inline';
				fld.style.display='inline';
				fld.focus();
			    var x = findPosX(fld), y = findPosY(fld);
			    div.style.left = String(parseInt(x) + 'px');
			    div.style.top = String(parseInt(y + 20) + 'px');
				div.style.display='block';
				if (div.innerHTML=='') div.innerHTML='<table><tr><td>No Rows(s) found</td></tr></table>';
				adjustMyFrameHeight();
			  }";
	}
	private static function getJSHide ($tagId) {
		return "function {$tagId}Hide() {
			  	var div=document.getElementById('{$tagId}SEARCHDIV');
				var fld=document.getElementById('{$tagId}SEARCH');
				var imgUp=document.getElementById('{$tagId}IMGUP');
				var imgDown=document.getElementById('{$tagId}IMGDOWN');
				imgDown.style.display='inline';
				imgUp.style.display='none';
				fld.style.display='none';
			    div.style.display='none';
			  }";
	}
	private static function getJSBind ($tagId) {
		return "function {$tagId}Bind() {
				\$(\"tr.{$tagId}TR\").hover(
				  function () {
					\$(this).css({'backgroundColor':'#FFFFAA','fontWeight':'bold'});
				  },
				  function () {
					\$(this).css({'backgroundColor':'#FFFFFF','fontWeight':'normal'});
				  }
				);
			  }";
	}
	private static function getJSFind ($tagId, $JSTestString, $JSDisplayString, $selectedValueString, $callbackJS) {
		$testString="";
		foreach ($JSTestString as $s) {
			$testString.=($testString=="")?"":"+";
			$testString.=$s.".replace(/[^a-zA-Z0-9]+/g,'').toLowerCase()";
		}
		$displayString="";
		foreach ($JSDisplayString as $s) {
			$displayString.="<td>'+{$s}+'</td>";
		}
		$displayString="'<tr class=\"{$tagId}TR\" onclick=\"document.getElementById(\'{$tagId}\').value=\''+{$selectedValueString}+'\'; {$tagId}Hide(); {$callbackJS}\">".$displayString."</tr>'";
		return "function {$tagId}Find(event) {
				var div=document.getElementById('{$tagId}SEARCHDIV');
				var fld=document.getElementById('{$tagId}SEARCH');
				var valFld=document.getElementById('{$tagId}');
			  	switch (event.keyCode) {
					case 27: {
								div.style.display='none';
								fld.focus();
								break;
					         }
					default: {
								{$tagId}Show();
								var pattern = new RegExp(fld.value.replace(/[^a-zA-Z0-9]+/g,'').toLowerCase()); // leave only alpha chars and digits
								var arr={$tagId}Arr; // points to original, not a copy!
								var list='', matchCnt=0;
								for (i=0; i<arr.length; i++) {
									if (pattern.test({$testString})) {
									  list += {$displayString};
									  matchCnt++;
									}
								}
								if (matchCnt==0) list='<tr><td>No Product(s) found</td></tr>';
								list='<table class=\'tableReset\'>'+list+'</table>';
							}
				}
				div.innerHTML=list;
				{$tagId}Bind();
				adjustMyFrameHeight();
			 	return;
	         }";
	}

	private static function getILayout($tagId,$callbackJS) {
		global $DHTMLROOT; global $PHPFOLDER;
		return "<style>
			    .iT { border-style:solid; border-width:1px; border-color:#DDDDDD; }
			    .iDD { height:8px; padding-top:0px; padding-bottom:0px; font-size:11px; }
		        </style>
		        Search Filter: <img id='{$tagId}IMGUP' title='Hide Search Box' src='{$DHTMLROOT}{$PHPFOLDER}images/up.jpg' onclick='{$tagId}Hide();' style='display:none'/>
							   <img id='{$tagId}IMGDOWN' title='Show Search Box' src='{$DHTMLROOT}{$PHPFOLDER}images/down.jpg' onclick='{$tagId}Show();' />
							   <input type='text' id='{$tagId}SEARCH' size='50' maxlength='100' onkeyup='{$tagId}Find(event);' style='display:none; background:#fdfae7; border-color:#BBBBBB;' />
							   <select id='{$tagId}' onchange='{$callbackJS}' /><option value=''>Not Selected</option></select></td></tr>";
	}

	public static function displayProductIDD($tagId,$value,$readOnly,$disabled,$onChange,$onClick,$onMouseOver,$dbConn,$principalId,$userId,$callbackJS) {
		global $ROOT; global $PHPFOLDER; global $DHTMLROOT;
		$permission=""; $style="text-align:right; ";
		if ($readOnly=="Y") $permission=" READONLY ";
		if ($disabled=="Y") {
			$permission.=" DISABLED ";
			$style.=" background-color:silver; border-style:solid; border-width:1px; border-color:#DDDDFF;";
		}

		include_once($ROOT.$PHPFOLDER.'DAO/ProductDAO.php');
		$productDAO=new ProductDAO($dbConn);
		$productArr=$productDAO->getUserPrincipalProductsArray($principalId,$userId);

		$js="";
		foreach ($productArr as $r) {

			if ($js!="") $js.=",";
			// sanitise
			$uid=str_replace("'","",$r["uid"]);
			$pc=str_replace("'","",$r["product_code"]);
			$ac=str_replace("'","",$r["alt_code"]);
			$desc=str_replace("'","",$r["product_description"]);
			$js.="{uid:{$uid},desc:'{$desc}',pc:'{$pc}',ac:'{$ac}'}";
		}
		$js="var {$tagId}Arr=new Array(".$js.");";

		// the area where the listed rows appear
		echo "<div id='{$tagId}SEARCHDIV' style='display:none; position:absolute; z-index:2000; background-color:white; border-style:solid; border-width:1px; border-color:#000000; max-width:500px; max-height:400px; overflow:auto;' >
			  </div>";

		// the elements
		echo "<div id='{$tagId}DIV' >".
		     (self::getILayout($tagId,$callbackJS)).
			 "<script type=\"text/javascript\" >
			  {$js}
			  \$(document).ready(function() {
					var dd=document.getElementById('{$tagId}');
					for (var i=0; i<{$tagId}Arr.length; i++) {
						dd.options[dd.options.length] = new Option({$tagId}Arr[i].pc+' - '+{$tagId}Arr[i].ac+' - '+{$tagId}Arr[i].desc,{$tagId}Arr[i].uid, false, false);
					}
					dd.value='{$value}';
			  });".(self::getJSBind($tagId)).
			  	   (self::getJSShow($tagId)).
			  	   (self::getJSHide($tagId)).
			  	   (self::getJSFind($tagId,array("arr[i].pc","arr[i].ac","arr[i].desc"),array("arr[i].pc","arr[i].ac","arr[i].desc"),"arr[i].uid",$callbackJS)).
			  "
			 </script>";
		echo "</div>";
	}


	public static function displayStoreIDD($tagId,$value,$readOnly,$disabled,$onChange,$onClick,$onMouseOver,$dbConn,$principalId,$userId,$callbackJS) {
		global $ROOT; global $PHPFOLDER; global $DHTMLROOT;
		$permission=""; $style="text-align:right; ";
		if ($readOnly=="Y") $permission=" READONLY ";
		if ($disabled=="Y") {
			$permission.=" DISABLED ";
			$style.=" background-color:silver; border-style:solid; border-width:1px; border-color:#DDDDFF;";
		}

		include_once($ROOT.$PHPFOLDER.'DAO/StoreDAO.php');
		$storeDAO=new StoreDAO($dbConn);
		$storeArr=$storeDAO->getUserPrincipalStoreArrayNew($userId, $principalId, "");
		$js="";
		foreach ($storeArr as $r) {
			if ($js!="") $js.=",";
			// sanitise
			$uid=str_replace("'","",$r["psm_uid"]);
			$sn=str_replace("'","",$r["store_name"]);
			$dn=str_replace("'","",$r["depot_name"]);
			$cn=str_replace("'","",$r["chain_name"]);
			$js.="{uid:{$uid},sn:'{$sn}',dn:'{$dn}',cn:'{$cn}'}";
		}
		$js="var {$tagId}Arr=new Array(".$js.");";

		// the area where the listed rows appear
		echo "<div id='{$tagId}SEARCHDIV' style='display:none; position:absolute; z-index:2000; background-color:white; border-style:solid; border-width:1px; border-color:#000000; max-width:500px; max-height:400px; overflow:auto;' >
			  </div>";

		// the elements
		echo "<div id='{$tagId}DIV' >".
		     (self::getILayout($tagId,$callbackJS)).
			 "<script type=\"text/javascript\" defer>
			  {$js}
			  \$(document).ready(function() {
					var dd=document.getElementById('{$tagId}');
					for (var i=0; i<{$tagId}Arr.length; i++) {
						dd.options[dd.options.length] = new Option({$tagId}Arr[i].sn+' - '+{$tagId}Arr[i].dn+' - '+{$tagId}Arr[i].cn,{$tagId}Arr[i].uid, false, false);
					}
					".(($value=="")?"":"dd.value='{$value}'")."
			  });".(self::getJSBind($tagId)).
			  	   (self::getJSShow($tagId)).
			  	   (self::getJSHide($tagId)).
			  	   (self::getJSFind($tagId,array("arr[i].sn","arr[i].dn","arr[i].cn"),array("arr[i].sn","arr[i].dn","arr[i].cn"),"arr[i].uid",$callbackJS)).
			  "
			 </script>";
		echo "</div>";
	}


	public static function selectStoreSearch($formFieldId, $columnsArr, $showUid, $onSubmitExtraJs, $secondReturnValue = "''", $showVendorStores = false, $urlString = "", $desktop=true){

    CommonUtils::getSystemConventions();

	  global $DHTMLROOT, $PHPFOLDER;

	  $vendorStores = ($showVendorStores===true) ? (1) : (0);

	  //make sure uid is included / else add it.
	  $u = array_search('psm_uid',$columnsArr);
	  if($u === false) $columnsArr[] = 'psm_uid';
	  $h = array_search('on_hold',$columnsArr);
	  if($h === false) $columnsArr[] = 'on_hold';

	  //Build header names
	  $headerColsArr = $columnsArr;  //preserve original array

	  //remove unwanted SEARCH on columns.
	  $r1 = array_search('psm_uid',$headerColsArr);
	  if($r1 !== false) unset($headerColsArr[$r1]);
	  $r2 = array_search('special_field_or',$headerColsArr);
	  if($r2 !== false) unset($headerColsArr[$r2]);
	  $r3 = array_search('ean_code_or',$headerColsArr);
	  if($r3 !== false) unset($headerColsArr[$r3]);
          $r4 = array_search('on_hold',$headerColsArr);
	  if($r4 !== false) unset($headerColsArr[$r4]);

          $hC = ($showUid) ? array('Uid') : array();
	  foreach($headerColsArr as $h){
	   if($h == 'special_fields'){
	     $hC[] = "'+splFld+'";  //special use js value of combined special fields.
	   } else {
	     $hC[] = ucwords(str_replace(array('-','_','store'),array(' ', ' ',SNC::store),trim($h)));
	   }
	  }

          //col index for json - remove search columns.
          $colIndex = $columnsArr;
	  $ri1 = array_search('special_field_or',$colIndex);
	  if($ri1 !== false) unset($colIndex[$ri1]);
	  $ri2 = array_search('ean_code_or',$colIndex);
	  if($ri2 !== false) unset($colIndex[$ri2]);



	  /*----------------------------------------
	   *
	   * 	OUTPUT BEGINS...
	   *
	   *----------------------------------------*/

	  $assistTxt = '<font color="#999"><i> click here to search...</i></font>';
	  if ($desktop) {
	    $linkHTML = "<div class='div-input' style='text-align:left;width:400px;font-size:12px;background-color:white;color:#047;border:1px solid gray;padding:1px 4px;float:left;' id='STORENAME'>".$assistTxt."</div><IMG src='{$DHTMLROOT}{$PHPFOLDER}images/search_button.gif' alt='Search Stores' border='0' width='24' height='24' style='margin-left:4px;margin-bottom:-7px;'>";
	  } else {
	    $linkHTML = "<IMG src='{$DHTMLROOT}{$PHPFOLDER}images/search_button.gif' alt='Search Stores' border='0' width='24' height='24' style='margin-left:4px;margin-bottom:-7px;'>
	                 <br><br>
	                 <div class='div-input' style='text-align:left;width:100%;border:1px solid gray;white-space:nowrap;overflow:hidden;' id='STORENAME'>".$assistTxt."</div>";
	  }
	  echo "<a href='javascript:storeSuggest()' title='Click here to select ".SNC::store.".'>".$linkHTML."</a>";


	  ?>
	  <script type='text/javascript'>

  var loading = '<div align="center" style="color:#999;font-size:20px;padding-top:110px;font-weight:bold;">loading <?php echo strtolower(SNC::store) ?>s...</div>';
		var noStores = '<div align="center" style="color:#B40404;font-size:20px;padding-top:110px;font-weight:bold;">No <?php echo strtolower(SNC::store) ?> found!</div>';
		var error = '<div align="center" style="color:#B40404;font-size:20px;padding-top:110px;font-weight:bold;">Error : Please Try again!</div>';
		var specialFldNames = false;

		//cookie is only valid of screen.
		createCookie("intelStoreStr","",1);

		function storeSuggest(){

		  var cookieStr = readCookie("intelStoreStr");
		  if(cookieStr == undefined){
		   cookieStr = '';
		  }

		  //clear selected form values on click.
		  $('#<?php echo $formFieldId?>').val('');
		  $('#STORENAME').html('<?php echo $assistTxt ?>');

			var box =  '<div style="display:block;">';
			box += '<div align="center"><span style="color:#000;">Search for <?php echo SNC::store ?>: </span><INPUT type=text value="'+cookieStr+'" size="15" id="storeSuggestValue" onkeydown="content.pressEnter(event);"/><input type="submit" class=submit value="search" onClick="content.ajaxStores()"/><div id="errorString" style="color:red;">&nbsp;</div></div>';
			box += '<div style="border-top:1px solid #888;border-bottom:1px solid #888;margin:8px 0px;height:250px;overflow:auto;color:#000;background:#fff;" id="storeSuggestList"></div>';
			box += '</div>';
			var w = parseInt($(window).width(),10);
			w=((w<680)?w-50:680); // mobile screens
			parent.popBox(box,'general',w);
			parent.document.getElementById('storeSuggestValue').focus();
		}

		function selectStoreSuggest(uid,name,onhold){
			 $('#<?php echo $formFieldId?>').val(uid);
			 $('#STORENAME').html(name);

			 //extra function upon submit.
			 <?php if($onSubmitExtraJs!='') echo "{$onSubmitExtraJs}(uid,name,onhold);"; ?>

		 }

	  function pressEnter(e){
	    if(e.charCode == 13){
			ajaxStores();
		} else if(e.keyCode == 13) {
		  ajaxStores();
		}
	  }

	  function ajaxStores(){

	    var root = '<?php echo $DHTMLROOT . $PHPFOLDER ?>';
		var valObj = parent.$('#storeSuggestValue');
		val = valObj.attr('value');
		createCookie("intelStoreStr",val,1);
		var errStr = parent.$('#errorString');
		var wrtObj = parent.$('#storeSuggestList');

		//empty?
		if(val == undefined || trim(val,'') == ""){
		  errStr.html('Empty Search, try again!');
		  valObj.focus();
		} else {

		  val = trim(val,'');
		  errStr.html('&nbsp;');
		  wrtObj.html(loading);

    	    $.ajax({
    	      timeout: (1000*60*2),
    	      url:  root+'functional/administration/functions/getStores.php?<?php echo $urlString; ?>',
    	      global: false,
    	      type: 'POST',
    	      data: 'FIELDS=<?php echo implode(',',$columnsArr); ?>&SEARCHSTRING='+val+'&VENDOR=<?php echo $vendorStores; ?>',
    	      dataType: 'text',
    	      cache: false,
    	      success: function(data){
    	       //debug - ajax
    	       //wrtObj.html(data);
    	       //return;

    	        if(data.search('</div>')== -1){	//check if not html - ie: user gets kicked out and ajax returns "html" from homepage.

    	          if(data == ''){
    	            wrtObj.html(noStores);
    	            parent.document.getElementById('storeSuggestValue').focus();
    	          } else {
    	          	eval(data);	//parse object into dom
    	          	wrtObj.html(formatJSONTable(data));
    	          	parent.document.getElementById('storeSuggestValue').focus();
    	          }
    	        } else {
    	          alert('Debugging - please send snapshot to RT : '+data.substring(0,100)+' : '+data.substring(data.length-100));
    	          wrtObj.html('Method true : '+error+data.substring(0,100)+' : '+data.substring(data.length-100));
    	          parent.document.getElementById('storeSuggestValue').focus();
    	        }
    	      },
    	      error: function(XMLHttpRequest, textStatus, errorThrown) {
    	        wrtObj.html('<br>Status: ' + textStatus);
              	wrtObj.html('<hr>' + XMLHttpRequest.responseText + '<hr>');
    	        wrtObj.html('Method false: '+XMLHttpRequest.status);
    	        wrtObj.html('Method false: '+errorThrown);
    	      }
    	    });
		}
	  }

	  function formatJSONTable(filterStr){

	    var no = 0;

	    var keyMap = new Array();
	    <?php
	    foreach($colIndex as $cI){
	     echo  'keyMap["'.$cI.'"] = false;' . "\n";
	    }
	    ?>
		var out = '';
		var splFld = '';
		var splFldArr = new Array();

	    if(specialFldNames !== false){
	    	for(var i in specialFldNames){
	    	  specialFldNames = specialFldNames[i];
	    	}
	    	if(specialFldNames != ""){
	    	  splFldArr = specialFldNames.split(',');
	    	  splFld = splFldArr.join('</th><th>');
	    	}
	    }

	    var out = '<table cellpadding="6" width="100%" cellspacing="0" id="storeSuggestTable"><thead bgcolor="#999999" style="color:#fff;"><tr>';
	    out += '<th style="padding-left:12px;"><?php echo implode('</th><th>',$hC) ?></th></tr></thead>';

	    for(var key in storeArray){

	      if(key == 0){

	        //First Obj contains Header Column - setup the key Mapping, columns are displayed in order of passed variable
	        reCol = storeArray[key].split(',');
	        for(i = 0; i < reCol.length; i++){
	          if(keyMap[reCol[i]] !== undefined){
	            keyMap[reCol[i]] = i;
	          }
	        }

	      } else {

	        var storeArr = storeArray[key].split(',');	//split the data.
	        <?php

	        foreach($colIndex as $i){
	          if($i == 'special_fields'){
	            echo 'var special_fields = trim(storeArr[keyMap["special_fields"]]);' . "\n";
	            echo 'specialFldArr = special_fields.split(\';\');' . "\n";
	            echo 'special_fields = specialFldArr.join("</td><td>");';
	          } else {
	            echo 'var '.$i.' = trim(storeArr[keyMap["'.$i.'"]]);' . "\n";
	          }
	        }

	        ?>

	        out += '<tr onClick="content.selectStoreSuggest(' + psm_uid + ',\''+<?php echo $secondReturnValue ?>+'\',' + on_hold + '); parent.popBoxClose();" onmouseover="$(this).css(\'background-color\',\'#FCFFB4\');$(this).css(\'cursor\',\'pointer\');" onmouseout="$(this).css(\'background-color\',\'\');$(this).css(\'cursor\',\'auto\');">';
	        <?php if($showUid){ echo 'out += "<td><small>"+psm_uid+"</small></td>";'; }?>
            <?php

            echo 'var color = (on_hold==1)?("#FF8000"):("#047");' . "\n";
            echo 'out += "';
	        foreach($colIndex as $c){

	          if($c != 'psm_uid' && $c != 'on_hold'){

	            if($c == 'store_name'){
	              echo '<td><strong style=\"color:"+color+";\">" + ' . $c . ' + "</strong></td>';
	            } else {
	              echo '<td>" + ' . $c . ' + "</td>';
	            }

	          }
                }
                echo '";';
                ?>

	        no++;
	      }

	    }
	    out += '</table>';

	    var errStr = parent.$('#errorString');
	    errStr.html('<span style="color:#000;"><strong>'+no+' <?php echo SNC::store ?>(s) Found!</strong></span>');

	    if(out == '') {
	      return error;
	    } else {
	      return out;
	    }

	  }

	  function trim(str, chars) {return ltrim(rtrim(str, chars), chars);}
	  function ltrim(str, chars) {chars = chars || "\\s"; return str.replace(new RegExp("^[" + chars + "]+", "g"), "");}
	  function rtrim(str, chars) {chars = chars || "\\s"; return str.replace(new RegExp("[" + chars + "]+$", "g"), "");}

	</script>

	  <?php

	}


}
?>
