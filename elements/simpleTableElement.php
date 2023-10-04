<?php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');


class SimpleTableElement {


	public static function makeCollapsible($tagNameList) {
		echo "<scr"."ipt type=text/javascript defer >
			  \$().ready(function(){
				\$('{$tagNameList}').css({'height':'20px'});
				\$('{$tagNameList}').hover(
					  function () {
						\$(this).css({'height':'400px'});
						adjustMyFrameHeight();
					  },
					  function () {
						\$(this).css({'height':'20px'});
					  }
					)
			  });
			 </script>";
	}


	public static function getToolbarCell($tagName, $label) {
		return "<scr"."ipt type=text/javascript defer >
					function removeTags(html)
					{
						var tmp = document.createElement(\"DIV\");
						tmp.innerHTML = html;
						return tmp.textContent || tmp.innerText;
					}
			  		function {$tagName}Find(pattern, tagName) {
			  			var pattern = new RegExp(pattern.replace(/[^a-zA-Z0-9]+/g,'').toLowerCase()); // leave only alpha chars and digits
						var list=document.getElementById('{$tagName}TBL').getElementsByTagName('TR');
						for (i=0; i<list.length; i++) {
							if (list[i].id=='{$tagName}HDR') continue;
							if (pattern.test(removeTags(list[i].innerHTML).replace(/[^a-zA-Z0-9]+/g,'').toLowerCase())) {
							  list[i].style.display='inline';
							} else {
								list[i].style.display='none';
							}
						}
			  		}
			    </script>
			    <td colspan=2><b>{$label}</b>
							  <a href='#' onclick='javascript:fld=document.getElementsByName(\"".$tagName."\"); for(i=0; i<fld.length; i++) fld[i].checked=true;' />&lt;Select All&gt;</a>&nbsp;
							  <a href='#' onclick='javascript:fld=document.getElementsByName(\"".$tagName."\"); for(i=0; i<fld.length; i++) fld[i].checked=false;' />&lt;unSelect All&gt;</a>&nbsp;
							  <input type='text' value='' onkeyup='{$tagName}Find(this.value, \"{$tagName}\");' />
				</td>";
	}


	public static function getUserChainList($tagName, $defaults, $type, $height, $dbConn, $principalId, $userId, $lfilter) {
		global $ROOT; global $PHPFOLDER;
		include_once($ROOT.$PHPFOLDER."DAO/StoreDAO.php");

		$storeDAO = new StoreDAO($dbConn);
		$mfC = $storeDAO->getAllPrincipalChainsForUser($userId, $principalId, $lfilter);

		echo "<div id='{$tagName}DIV' style='text-align:left; height:{$height}px; max-width:600px; overflow:auto; border:1px; border-style:solid; border-color:#DDDDDD;'>
			  <table class='tableReset' id='{$tagName}TBL' >
			  <tr style='background-color:#EEEEEE;' id='{$tagName}HDR'>".(self::getToolbarCell($tagName,"Chains"))."</tr>";
		foreach ($mfC as $row) {
			$value=$row['principal_chain_uid'];
			if (in_array($value,$defaults)) $CHECKED=" CHECKED "; else $CHECKED="";
			echo "<tr>";
			echo "<td><input name='{$tagName}' type='{$type}' value='".$value."' ".$CHECKED." /></td><td>{$row['chain_name']}</td>";
			echo "</tr>";
		}
		echo "</table>
			  </div>";

		return;
	}


	public static function getUserProductList($tagName, $defaults, $type, $height, $dbConn, $principalId, $userId) {
		global $ROOT; global $PHPFOLDER;
		include_once($ROOT.$PHPFOLDER.'DAO/ProductDAO.php');

		$productDAO = new ProductDAO($dbConn);
		$mfP = $productDAO->getUserPrincipalProductsArray($principalId, $userId);

		echo "<div id='{$tagName}DIV' style='text-align:left; height:{$height}px; max-width:600px; overflow:auto; border:1px; border-style:solid; border-color:#DDDDDD;'>
			  <table class='tableReset' id='{$tagName}TBL' >
			  <tr style='background-color:#EEEEEE;' id='{$tagName}HDR'>".(self::getToolbarCell($tagName,"Products"))."</tr>";
		foreach ($mfP as $row) {
			$value=$row['uid'];
			if (in_array($value,$defaults)) $CHECKED=" CHECKED "; else $CHECKED="";
			echo "<tr>";
			echo "<td><input name='{$tagName}' type='{$type}' value='".$value."' ".$CHECKED." /></td><td>{$row['product_code']} - {$row['product_description']}</td>";
			echo "</tr>";
		}
		echo "</table>
			  </div>";

		return;
	}
	
	public static function getProductList($tagName, $defaults, $type, $height, $dbConn, $principalId, $userId) {
		global $ROOT; global $PHPFOLDER;
		include_once($ROOT.$PHPFOLDER.'DAO/ProductDAO.php');

		$productDAO = new ProductDAO($dbConn);
		$mfP = $productDAO->getPrincipalProductsArray($principalId, "");

		echo "<div id='{$tagName}DIV' style='text-align:left; height:{$height}px; max-width:600px; overflow:auto; border:1px; border-style:solid; border-color:#DDDDDD;'>
			  <table class='tableReset' id='{$tagName}TBL' >
			  <tr style='background-color:#EEEEEE;' id='{$tagName}HDR'>".(self::getToolbarCell($tagName,"Products"))."</tr>";
		foreach ($mfP as $row) {
			$value=$row['uid'];
			if (in_array($value,$defaults)) $CHECKED=" CHECKED "; else $CHECKED="";
			echo "<tr>";
			echo "<td><input name='{$tagName}' type='{$type}' value='".$value."' ".$CHECKED." /></td><td>{$row['product_code']} - {$row['product_description']}</td>";
			echo "</tr>";
		}
		echo "</table>
			  </div>";

		return;
	}


	public static function getProductGroupList($tagName, $defaults, $type, $height, $dbConn, $principalId, $userId) {

	  global $ROOT, $PHPFOLDER;
	  include_once($ROOT.$PHPFOLDER.'DAO/ProductDAO.php');

	  $productDAO = new ProductDAO($dbConn);
	  $mfPG = $productDAO->getPrincipalProductCategoryArray($principalId, 'A');

		echo "<div id='{$tagName}DIV' style='text-align:left; height:{$height}px; max-width:600px; overflow:auto; border:1px; border-style:solid; border-color:#DDDDDD;'>
			  <table class='tableReset' id='{$tagName}TBL' >
			  <tr style='background-color:#EEEEEE;' id='{$tagName}HDR'>".(self::getToolbarCell($tagName,"Product Groups"))."</tr>";

		foreach ($mfPG as $row) {
			echo '<tr>';
			echo "<td width='25'><input name='{$tagName}' type='{$type}' value='".$row['uid']."' ",(in_array($row['uid'],$defaults))?(" CHECKED "):("")," /></td>";
			echo '<td>',$row['description'],'</td>';
			echo '</tr>';
		}

		echo '</table></div>';

		return;
	}


	public static function getUserStoreList($tagName, $defaults, $type, $height, $dbConn, $principalId, $userId) {
		global $ROOT; global $PHPFOLDER;
		include_once($ROOT.$PHPFOLDER.'DAO/StoreDAO.php');

		$storeDAO = new StoreDAO($dbConn);
		$mfS = $storeDAO->getUserPrincipalStoreArray($userId, $principalId, "");

		echo "<div id='{$tagName}DIV' style='text-align:left; height:{$height}px; max-width:600px; overflow:auto; border:1px; border-style:solid; border-color:#DDDDDD;'>
			  <table class='tableReset' id='{$tagName}TBL' >
			  <tr style='background-color:#EEEEEE;' id='{$tagName}HDR'>".(self::getToolbarCell($tagName,"Stores"))."</tr>";
		foreach ($mfS as $row) {
			$value=$row['psm_uid'];
			if (in_array($value,$defaults)) $CHECKED=" CHECKED "; else $CHECKED="";
			echo "<tr>";
			echo "<td><input name='{$tagName}' type='{$type}' value='".$value."' ".$CHECKED." /></td><td>{$row['store_name']}</td>";
			echo "</tr>";
		}
		echo "</table>
			  </div>";

		return;
	}

}
?>
