<?php


include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');


class BasicSelectElement {


	public static function buildGenericDD($tagId,$lableArr,$valueArr,$chosenValue,$readOnly,$disabled,$onChange,$onClick,$onMouseOver,$returnAsString=false) {

		global $ROOT, $PHPFOLDER;
		$permission = '';
		$style = '';
		$content = "";

		if ($readOnly=='Y') $permission=' READONLY ';
		if ($disabled=='Y') {
			$permission.=' DISABLED ';
			$style.=' background-color:silver; border-style:solid; border-width:1px; border-color:#DDDDFF;';
		}

		if(count($valueArr) != count($lableArr)){
		  $content .= 'ERROR.';
		} else {
          $content .= '<select id="'.$tagId.'" name="'.$tagId.'" style="'.$style.'" '.$permission.' onChange="'.$onChange.'" >';
          $totValue = count($valueArr);
          for ($i=0; $i<$totValue; $i++){
          	$content .= '<option value="'.$valueArr[$i].'" '.(($valueArr[$i] == $chosenValue) ? ('SELECTED') : ('')) . '>' . $lableArr[$i] . '</option>';
          }
          $content .= '</select>';

		}

		if ($returnAsString===true) return $content;
		else echo $content;
	}


	public static function getGeneralDD($tagId,$lable,$value,$chosenValue,$readOnly,$disabled,$onChange,$onClick,$onMouseOver) {

		global $ROOT, $PHPFOLDER;
		$permission = '';
		$style = 'text-align:left; ';

		if ($readOnly=='Y') $permission=' READONLY ';
		if ($disabled=='Y') {
			$permission.=' DISABLED ';
			$style.=' background-color:silver; border-style:solid; border-width:1px; border-color:#DDDDFF;';
		}

		$valueArr = explode(",",$value);
		$lableArr = explode(",",$lable);

		if(count($valueArr) != count($lableArr)){
		  echo 'ERROR.';
		} else {
          echo "<select id=\"".$tagId."\" name=\"".$tagId."\" style='".$style."' ".$permission." onChange=\"".$onChange."\" >";

          $totValue = count($valueArr);
          for ($i=0; $i<$totValue; $i++){
          	echo '<option value="'.$valueArr[$i].'" ',($valueArr[$i] == $chosenValue) ? ('SELECTED') : ('') , '>' . $lableArr[$i] . '</option>';
          }
          echo '</select>';
		}
	}


	public static function getRolesDD($tagId,$value,$readOnly,$disabled,$onChange,$onClick,$onMouseOver,$dbConn) {

		global $ROOT, $PHPFOLDER;
		include_once($ROOT.$PHPFOLDER.'DAO/AdministrationDAO.php');
		$permission = '';
		$style = 'text-align:left; ';

		if ($readOnly=="Y") $permission=" READONLY ";
		if ($disabled=="Y") {
			$permission.=" DISABLED ";
			$style.=" background-color:silver; border-style:solid; border-width:1px; border-color:#DDDDFF;";
		}

		$administrationDAO=new AdministrationDAO($dbConn);
		$rolesArr=$administrationDAO->getRolesArray(null,null);

		print("<select id=\"".$tagId."\" name=\"".$tagId."\" style='".$style."' ".$permission." onChange=\"".$onChange."\" >");
		print("<option value=''>No Role Selected</option>");
		foreach ($rolesArr as $row) {
			print("<option value=\"".$row['uid']."\" ");
			if ($value==$row['uid']) echo "SELECTED"; else { echo ""; }
			echo '>'.
						$row['uid'].
						DELIMITER_OTHER_1.
						$row['description'].
						DELIMITER_OTHER_2.
						$row['long_description'].
						'</option>';
		}
		echo '</select>';
	}


	// note: this returns a DD using principal_id as the Value, and not UID of user permissions !!!
	public static function getUserPrincipalDD($tagId,$value,$readOnly,$disabled,$onChange,$onClick,$onMouseOver,$dbConn,$userId) {

		global $ROOT, $PHPFOLDER;
		include_once($ROOT.$PHPFOLDER.'DAO/PrincipalDAO.php');

		$principalDAO=new PrincipalDAO($dbConn);
		$principalArr=$principalDAO->getUserPrincipalArray($userId,"");

		$lableArr = array('select option...');
		$valueArr = array('');

		foreach ($principalArr as $row) {
			$lableArr[] = $row['principal_id'] . DELIMITER_OTHER_1 . $row['principal_name'];
			$valueArr[] = $row['principal_id'];
		}

		self::buildGenericDD($tagId,$lableArr,$valueArr,$value,$readOnly,$disabled,$onChange,$onClick,$onMouseOver);

	}


	// NB : returns DD as variable string, not echo'd
	public static function getLogonUserPrincipalDD($tagId,$value,$readOnly,$disabled,$onChange,$onClick,$onMouseOver,$dbConn,$userId) {

		global $ROOT, $PHPFOLDER;
		include_once($ROOT.$PHPFOLDER.'DAO/PrincipalDAO.php');
		$permission = '';
		$style = 'text-align:left; ';

		if ($readOnly=="Y") $permission=" READONLY ";
		if ($disabled=="Y") {
			$permission.=" DISABLED ";
			$style.=" background-color:silver; border-style:solid; border-width:1px; border-color:#DDDDFF;";
		}

		$principalDAO = new PrincipalDAO($dbConn);
		$principalArr=$principalDAO->getUserPrincipalArray($userId,"", true);


		$dd="<select id=\"{$tagId}\" name=\"{$tagId}\" style='{$style}' {$permission} onChange=\"{$onChange}\" >
		     <option value=''>select option...</option>";
		foreach ($principalArr as $row) {
			$dd.="<option value=\"{$row['principal_id']},{$row['principal_name']},{$row['principal_code']},{$row['principal_type']}\" ";
			if ($value==$row['principal_id']) $dd.=" SELECTED ";
			$dd.=">{$row['principal_name']}</option>";
		}
		$dd.="</select>";

		return array(sizeof($principalArr),$dd);
	}


	public static function getUsersWithinPriviledgesDD($tagId,$value,$readOnly,$disabled,$onChange,$onClick,$onMouseOver,$dbConn,$userId, $principalId) {

		global $ROOT, $PHPFOLDER;
		include_once($ROOT.$PHPFOLDER.'DAO/AdministrationDAO.php');
		$permission = '';
		$style = 'text-align:left; ';

		if ($readOnly=="Y") $permission=" READONLY ";
		if ($disabled=="Y") {
			$permission.=" DISABLED ";
			$style.=" background-color:silver; border-style:solid; border-width:1px; border-color:#DDDDFF;";
		}

		$administrationDAO=new AdministrationDAO($dbConn);
		$adminUser=(isset($_SESSION['admin_user']) && ($_SESSION['admin_user']=="Y"))?true:false;
		if ($adminUser) $usersArr=$administrationDAO->getUsersArray();
		else $usersArr=$administrationDAO->getUsersByPrincipalDepotArray($userId);
		print("<select id=\"".$tagId."\" name=\"".$tagId."\" style='".$style."' ".$permission." onChange=\"".$onChange."\" >");
		print("<option value=''>No User Selected</option>");
		foreach ($usersArr as $row) {
			print("<option value=\"".$row['uid']."\" ");
			if ($value==$row['uid']) echo "SELECTED"; else { echo ""; }
			print(">".
						$row['uid'].
						DELIMITER_OTHER_1.
						$row['full_name'].
						DELIMITER_OTHER_2.
						$row['principal_name'].
						DELIMITER_OTHER_2.
						$row['depot_name'].
						"</option>");
		}
		echo '</select>';
	}


	public static function getPrincipalChainsDD($tagId,$value,$readOnly,$disabled,$onChange,$onClick,$onMouseOver,$dbConn,$principalId) {

		global $ROOT, $PHPFOLDER;
		include_once($ROOT.$PHPFOLDER.'DAO/ChainDAO.php');
		$permission = '';
		$style = 'text-align:left; ';

		if ($readOnly=="Y") $permission=" READONLY ";
		if ($disabled=="Y") {
			$permission.=" DISABLED ";
			$style.=" background-color:silver; border-style:solid; border-width:1px; border-color:#DDDDFF;";
		}

		$chainDAO=new ChainDAO($dbConn);
		$chainsArr=$chainDAO->getPrincipalChainsArray($principalId);
		print("<select id=\"".$tagId."\" name=\"".$tagId."\" style='".$style."' ".$permission." onChange=\"".$onChange."\" >");
		print("<option value=''>No Chain Selected</option>");
		foreach ($chainsArr as $row) {
			print("<option value=\"".$row['uid']."\" ");
			if ($value==$row['uid']) echo "SELECTED"; else { echo ""; }
			print(">".
						$row['uid'].
						DELIMITER_OTHER_1.
						$row['description'].
						"</option>");
		}
		echo '</select>';
	}


	public static function getUserPrincipalChainsDD($tagId,$value,$readOnly,$disabled,$onChange,$onClick,$onMouseOver,$dbConn,$userId,$principalId,$lfilter) {

		global $ROOT, $PHPFOLDER;
		include_once($ROOT.$PHPFOLDER.'DAO/StoreDAO.php');

		$storeDAO = new StoreDAO($dbConn);
		$chainsArr = $storeDAO->getAllPrincipalChainsForUser($userId,$principalId,$lfilter);

		$lableArr = array('No Chain Selected');
		$valueArr = array('');

		foreach ($chainsArr as $row) {
			$lableArr[] = $row['principal_chain_uid'] . DELIMITER_OTHER_1 . $row['chain_name'];
			$valueArr[] = $row['principal_chain_uid'];
		}

		self::buildGenericDD($tagId,$lableArr,$valueArr,$value,$readOnly,$disabled,$onChange,$onClick,$onMouseOver);

	}

	public static function getPrincipalAreas($tagId,$value,$readOnly,$disabled,$onChange,$onClick,$onMouseOver,$dbConn,$userId,$principalId) {

		global $ROOT, $PHPFOLDER;
		include_once($ROOT.$PHPFOLDER.'DAO/StoreDAO.php');

		$storeDAO = new StoreDAO($dbConn);
		$chainsArr = $storeDAO->getPrincipalAreas($principalId);

		$lableArr = array('No Area Selected');
		$valueArr = array('');

		foreach ($chainsArr as $row) {
			$lableArr[] = $row['uid'] . DELIMITER_OTHER_1 . $row['description'];
			$valueArr[] = $row['uid'];
		}

		self::buildGenericDD($tagId,$lableArr,$valueArr,$value,$readOnly,$disabled,$onChange,$onClick,$onMouseOver);

	}


	public static function getUserDepotsForPrincipalDD($tagId,$value,$readOnly,$disabled,$onChange,$onClick,$onMouseOver,$dbConn,$userId,$principalId,$returnAsString=false) {

		global $ROOT, $PHPFOLDER;
		include_once($ROOT.$PHPFOLDER.'DAO/DepotDAO.php');

		$depotDAO=new DepotDAO($dbConn);
		$depotsArr=$depotDAO->getAllDepotsForPrincipalArray($userId,$principalId);

		$lableArr = array('No Depot Selected');
		$valueArr = array('');

		foreach ($depotsArr as $row) {
			$lableArr[] = $row['uid'] . DELIMITER_OTHER_1 . $row['depot_name'];
			$valueArr[] = $row['uid'];
		}

		self::buildGenericDD($tagId,$lableArr,$valueArr,$value,$readOnly,$disabled,$onChange,$onClick,$onMouseOver,$returnAsString);

	}

	public static function getDepotUserDepotDD($tagId,$value,$readOnly,$disabled,$onChange,$onClick,$onMouseOver,$dbConn,$userId,$depotUId) {

		global $ROOT, $PHPFOLDER;
		include_once($ROOT.$PHPFOLDER.'DAO/DepotDAO.php');

		$depotDAO=new DepotDAO($dbConn);
		$depotsArr=$depotDAO->getDepotItemForDepotUser($userId, $depotUId);

                if(count($depotsArr)==0){
                  $lableArr = array('No Depot Selected');
                  $valueArr = array('');
                }

		foreach ($depotsArr as $row) {
			$lableArr[] = $row['uid'] . DELIMITER_OTHER_1 . $row['depot_name'];
			$valueArr[] = $row['uid'];
		}

		self::buildGenericDD($tagId,$lableArr,$valueArr,$value,$readOnly,$disabled,$onChange,$onClick,$onMouseOver);

	}

	public static function getPrincipalProductsDD($tagId,$value,$readOnly,$disabled,$onChange,$onClick,$onMouseOver,$extra,$dbConn,$principalId, $userId) {

		global $ROOT, $PHPFOLDER;
		include_once($ROOT.$PHPFOLDER.'DAO/ProductDAO.php');
		$permission = '';
		$style = 'text-align:left; ';

		if ($readOnly=="Y") $permission=" READONLY ";
		if ($disabled=="Y") {
			$permission.=" DISABLED ";
			$style.=" background-color:silver; border-style:solid; border-width:1px; border-color:#DDDDFF;";
		}

		$productDAO=new ProductDAO($dbConn);
		$mfPP = $productDAO->getUserPrincipalProductsArray($principalId, $userId);

		echo "<select style='font-family: \"Courier New\", Courier, monospace; {$style}' id=\"".$tagId."\" name=\"".$tagId."\" onChange=\"".$onChange."\" ".$extra." ".$permission." >";
		echo "<option value=''>No Product Selected</option>";
		foreach ($mfPP as $row) {
			print("<option value=\"".$row['uid']."\" ");
			if ($value==$row['uid']) echo "SELECTED"; else { echo ""; }
			print(">".
						str_pad($row['product_code'],15,".",STR_PAD_RIGHT).
						DELIMITER_OTHER_1.
						$row['product_description'].
						"</option>");
		}
		echo "</select>";
	}


	 // note: this returns a DD using principal_id as the Value, and not UID of user permissions !!!
  public static function getPrincipalProductCategoryDD($tagId, $value, $readOnly, $disabled, $onChange, $onClick, $onMouseOver, $dbConn, $principalId, $status) {

	global $ROOT, $PHPFOLDER;
	include_once ($ROOT . $PHPFOLDER . 'DAO/ProductDAO.php');
	$permission = '';

    if ($readOnly == "Y") $permission = " READONLY ";
    if ($disabled == "Y") {
      $permission .= " DISABLED ";
      $style .= " background-color:silver; border-style:solid; border-width:1px; border-color:#DDDDFF;";
    }

    $productDAO = new ProductDAO($dbConn);
    $productCategoryArr = $productDAO->getPrincipalProductCategoryArray($principalId, $status);

    echo '<select id="' . $tagId . '" name="' . $tagId . '" style="' . $permission . '" onChange="' . $onChange . '" >';
    echo '<option value="" >No Category Selected</option>';

    foreach ( $productCategoryArr as $row ) {
        $selected = ($value == $row['uid']) ? ('SELECTED') : ('');
        echo '<option value="' . $row['uid'] . '" ' . $selected . ' >' . $row['description'] . '</option>';
    }

    echo '</select>';
  }


	public static function getDealTypesDD($tagId,$value,$readOnly,$disabled,$onChange,$onClick,$onMouseOver,$dbConn) {

		global $ROOT, $PHPFOLDER;
		include_once($ROOT.$PHPFOLDER.'DAO/ProductDAO.php');
		$permission = '';
		$style = 'text-align:left; ';

		if ($readOnly=="Y") $permission=" READONLY ";
		if ($disabled=="Y") {
			$permission.=" DISABLED ";
			$style.=""; //" background-color:silver; border-style:solid; border-width:1px; border-color:#DDDDFF;";
		}

		$productDAO=new ProductDAO($dbConn);
		$mfDT = $productDAO->getDealType("");
		print("<select id=\"".$tagId."\" name=\"".$tagId."\" style='".$style."' ".$permission." onChange=\"".$onChange."\" >");
		print("<option value=''>Not Selected</option>");
		foreach ($mfDT as $row) {
			print("<option value=\"".$row['uid']."\" ");
			if ($value==$row['uid']) echo "SELECTED"; else { echo ""; }
			print(">".
						$row['description']." [".$row['unit']."]".
						"</option>");
		}
		echo '</select>';
	}


	public static function getDocumentTypesAllowedDD($tagId,$value,$readOnly,$disabled,$onChange,$onClick,$onMouseOver,$dbConn,$userId,$principalId) {

		global $ROOT, $PHPFOLDER;
		include_once($ROOT.$PHPFOLDER.'DAO/CommonDAO.php');
		$permission = '';
		$style = 'text-align:left; ';

		if ($readOnly=="Y") $permission=" READONLY ";
		if ($disabled=="Y") {
			$permission.=" DISABLED ";
			$style.=" background-color:silver; border-style:solid; border-width:1px; border-color:#DDDDFF;";
		}

		$commonDAO=new CommonDAO($dbConn);
		$mfDT = $commonDAO->getDocumentTypesAllowedArray($userId,$principalId);

		// first choose the default
		$hasOI=false; $hasVal=false;
		foreach ($mfDT as $row) {
			if ($row['uid']==DT_ORDINV) $hasOI=true;
			if ($row['uid']==$value) $hasVal=true;
		}
		if ($hasVal) $default=$value;
		else if ($hasOI) $default=DT_ORDINV;
		else { if (isset($mfDT[0])) $default=$mfDT[0]['uid']; else $default=""; }

		print("<select id=\"".$tagId."\" name=\"".$tagId."\" style='".$style."' ".$permission." onChange=\"".$onChange."\" >");
		print("<option value=''>Not Selected</option>");
		foreach ($mfDT as $row) {
		  if($row['show_on_capture']=='Y'){
			print("<option value=\"".$row['uid']."\" ");
			if ($default==$row['uid']) echo "SELECTED"; else { echo ""; }
			print(">".
						$row['description'].
						"</option>");
		  }
		}
		echo '</select>';
	}


	public static function getReportDocumentTypesAllowedDD($tagId,$value,$readOnly,$disabled,$onChange,$onClick,$onMouseOver,$dbConn,$userId,$principalId) {

		global $ROOT, $PHPFOLDER;
		include_once($ROOT.$PHPFOLDER.'DAO/CommonDAO.php');
		$permission = '';
		$style = 'text-align:left; ';

		if ($readOnly=="Y") $permission=" READONLY ";
		if ($disabled=="Y") {
			$permission.=" DISABLED ";
			$style.=" background-color:silver; border-style:solid; border-width:1px; border-color:#DDDDFF;";
		}

		$commonDAO=new CommonDAO($dbConn);
		$mfDT = $commonDAO->getReportDocumentTypesAllowedArray($userId,$principalId);

		// first choose the default
		$hasOI=false; $hasVal=false;
		foreach ($mfDT as $row) {
			if ($row['uid']==DT_ORDINV) $hasOI=true;
			if ($row['uid']==$value) $hasVal=true;
		}
		if ($hasVal) $default=$value;
		else if ($hasOI) $default=DT_ORDINV;
		else { if (isset($mfDT[0])) $default=$mfDT[0]['uid']; else $default=""; }

		print("<select id=\"".$tagId."\" name=\"".$tagId."\" style='".$style."' ".$permission." onChange=\"".$onChange."\" >");
		print("<option value=''>Not Selected</option>");
		foreach ($mfDT as $row) {
			print("<option value=\"".$row['uid']."\" ");
			if ($default==$row['uid']) echo "SELECTED"; else { echo ""; }
			print(">".
						$row['description'].
						"</option>");
		}
		echo '</select>';
	}


	// historical DB - remove when no longer needed
	public static function getHistoricalDocumentTypesAllowedDD($tagId,$value,$readOnly,$disabled,$onChange,$onClick,$onMouseOver,$dbConn,$userId,$principalId) {

		global $ROOT, $PHPFOLDER;
		$permission = '';
		$style = 'text-align:left; ';

		if ($readOnly=="Y") $permission=" READONLY ";
		if ($disabled=="Y") {
			$permission.=" DISABLED ";
			$style.=" background-color:silver; border-style:solid; border-width:1px; border-color:#DDDDFF;";
		}

		$mfDT[] = array("id"=>"V","description"=>"(VAT) Invoice");
		$mfDT[] = array("id"=>"P","description"=>"(Pick-up) Uplift Advice");

		print("<select id=\"".$tagId."\" name=\"".$tagId."\" style='".$style."' ".$permission." onChange=\"".$onChange."\" >");
		print("<option value=''>Not Selected</option>");
		foreach ($mfDT as $row) {
			print("<option value=\"".$row['id']."\" ");
			if ($value==$row['id']) echo "SELECTED"; else { echo ""; }
			print(">".
						$row['description'].
						"</option>");
		}
		echo '</select>';
	}


	public static function getUserPrincipalStoresDD($tagId,$value,$readOnly,$disabled,$onChange,$onClick,$onMouseOver,$extra,$dbConn,$userId,$principalId) {

		global $ROOT, $PHPFOLDER;
		$permission = '';
		$style = 'text-align:left; ';

		if ($readOnly=="Y") $permission=" READONLY ";
		if ($disabled=="Y") {
			$permission.=" DISABLED ";
			$style.=" background-color:silver; border-style:solid; border-width:1px; border-color:#DDDDFF;";
		}

		include_once($ROOT.$PHPFOLDER.'DAO/StoreDAO.php');
		$storeDAO=new StoreDAO($dbConn);
		$mfPS = $storeDAO->getUserPrincipalStoreArray($userId, $principalId, "");
		print("<select style='font-family: \"Courier New\", Courier, monospace' id=\"".$tagId."\" name=\"".$tagId."\" style='".$style."' ".$permission." onChange=\"".$onChange."\" ".$extra." >");
		print("<option value=''>Not Selected</option>");
		foreach ($mfPS as $row) {
			if (($row['dd_uid']=="0") || ($row['dd_uid']=="8") || ($row['dd_uid']=="")) $day="";
			else $day=$row['delivery_day'];
			print("<option value=\"".$row['psm_uid']."\" ");
			if ($value==$row['psm_uid']) echo "SELECTED"; else { echo ""; }
			print(">".
						$row['store_name'].
						DELIMITER_OTHER_1.
						$row['depot_name'].
						DELIMITER_OTHER_2.
						$day.
						"</option>");
		}
		echo '</select>';
	}
// ******************************************************************************************************************************************************************
	public static function getPaymentPrincipalStoresDD($tagId,$value,$readOnly,$disabled,$onChange,$onClick,$onMouseOver,$extra,$dbConn,$userId,$principalId) {

		global $ROOT, $PHPFOLDER;
		$permission = '';
		$style = 'text-align:left; ';

		if ($readOnly=="Y") $permission=" READONLY ";
		if ($disabled=="Y") {
			$permission.=" DISABLED ";
			$style.=" background-color:silver; border-style:solid; border-width:1px; border-color:#DDDDFF;";
		}

		include_once($ROOT.$PHPFOLDER.'DAO/PaymentsDAO.php');
		$PaymentsDAO=new PaymentsDAO($dbConn);
		$mfPSI = $PaymentsDAO->getPaymentCustomers($principalId);
		
		$PaymentsDAO=new PaymentsDAO($dbConn);
		$mfPSG = $PaymentsDAO->getPaymentGroups($principalId);		
		
		$mfPS  = array_merge($mfPSI,$mfPSG);
		
  	print("<select style='font-family: \"Courier New\", Courier, monospace' id=\"".$tagId."\" name=\"".$tagId."\" style='".$style."' ".$permission." onChange=\"".$onChange."\" ".$extra." >");
		print("<option value=''>Not Selected</option>");
		foreach ($mfPS as $row) {
			print("<option value=\"".$row['payment_by'] . $row['uid']."\" ");
			if ($value==$row['payment_by'] . $row['uid']) echo "SELECTED"; else { echo ""; }
			print(">".
						$row['Customer'] .
						"</option>");
		}
		echo '</select>';
	}
// ******************************************************************************************************************************************************************
	public static function getPaymentPGroupsDD($tagId,$value,$readOnly,$disabled,$onChange,$onClick,$onMouseOver,$extra,$dbConn,$userId,$principalId) {

		global $ROOT, $PHPFOLDER;
		$permission = '';
		$style = 'text-align:left; ';

		if ($readOnly=="Y") $permission=" READONLY ";
		if ($disabled=="Y") {
			$permission.=" DISABLED ";
			$style.=" background-color:silver; border-style:solid; border-width:1px; border-color:#DDDDFF;";
		}

		include_once($ROOT.$PHPFOLDER.'DAO/PaymentsDAO.php');
		
		$PaymentsDAO=new PaymentsDAO($dbConn);
		$mfPS = $PaymentsDAO->getPaymentGroupChains($principalId);		
		
  	print("<select style='font-family: \"Courier New\", Courier, monospace' id=\"".$tagId."\" name=\"".$tagId."\" style='".$style."' ".$permission." onChange=\"".$onChange."\" ".$extra." >");
		print("<option value=''>Not Selected</option>");
		foreach ($mfPS as $row) {
			print("<option value=\"".$row['payment_by'] . $row['uid']."\" ");
			if ($value==$row['payment_by'] . $row['uid']) echo "SELECTED"; else { echo ""; }
			print(">".
						$row['Customer'] .
						"</option>");
		}
		echo '</select>';
	}
// ******************************************************************************************************************************************************************
	public static function getDaysDD($tagId,$value,$readOnly,$disabled,$onChange,$onClick,$onMouseOver,$dbConn) {

	  global $ROOT, $PHPFOLDER;
	  include_once($ROOT.$PHPFOLDER."DAO/CommonDAO.php");

	  $commonDAO = new CommonDAO($dbConn);
	  $mfDays = $commonDAO->getDaysArray();

          $lableArr = array('Not Known');
          $valueArr = array('8');

          foreach ($mfDays as $row) {
            $lableArr[] = $row['name'];
            $valueArr[] = $row['uid'];
          }

          self::buildGenericDD($tagId,$lableArr,$valueArr,$value,$readOnly,$disabled,$onChange,$onClick,$onMouseOver);

	}

// &&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&

	public static function getPeriodDD2($tagId,$value,$readOnly,$disabled,$onChange,$onClick,$onMouseOver,$dbConn,$principalId) {

	  global $ROOT, $PHPFOLDER;
	  include_once($ROOT.$PHPFOLDER."DAO/CommonDAO.php");

	  $commonDAO = new CommonDAO($dbConn);
	  $mfPeriod = $commonDAO->getPeriodArray($principalId, 'Dates');
	  
//	  	  print_r($mfPeriod);
    foreach ($mfPeriod as $row) {
        $lableArr[] = $row['start_date'];
        $valueArr[] = $row['year']. $row['period'].$row['start_date'].$row['end_date'];
    }

    self::buildGenericDD($tagId,$lableArr,$valueArr,$value,$readOnly,$disabled,$onChange,$onClick,$onMouseOver);

	}
// &&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&

	public static function getPeriodDD($tagId,$value,$readOnly,$disabled,$onChange,$onClick,$onMouseOver,$dbConn,$principalId) {

	  global $ROOT, $PHPFOLDER;
	  include_once($ROOT.$PHPFOLDER."DAO/CommonDAO.php");

	  $commonDAO = new CommonDAO($dbConn);
	  $mfPeriod = $commonDAO->getPeriodArray($principalId, '');
	  
//	  	  print_r($mfPeriod);
    foreach ($mfPeriod as $row1) {
         if($row1['sort'] == "Current") {
             $lableArr[] = $row1['year'] . " - " .$row1['period'] . " -      From " . $row1['start_date'] . " To " . $row1['end_date'] . "  (" . $row1['sort'] . ")" ;
             $valueArr[] = $row1['year']. $row1['period'].$row1['start_date'].$row1['end_date'];
         }
    }
    foreach ($mfPeriod as $row) {
        $lableArr[] = $row['year'] . " - " .$row['period'] . " -      From " . $row['start_date'] . " To " . $row['end_date'];
        $valueArr[] = $row['year']. $row['period'].$row['start_date'].$row['end_date'];
    }

    self::buildGenericDD($tagId,$lableArr,$valueArr,$value,$readOnly,$disabled,$onChange,$onClick,$onMouseOver);

	}
// &&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&

	public static function getFinYearDD($tagId,$value,$readOnly,$disabled,$onChange,$onClick,$onMouseOver,$dbConn,$principalId) {

	  global $ROOT, $PHPFOLDER;
	  include_once($ROOT.$PHPFOLDER."DAO/CommonDAO.php");

	  $commonDAO = new CommonDAO($dbConn);
	  $mfPeriod = $commonDAO->getFinYearArray($principalId);

    foreach ($mfPeriod as $row) {
        $lableArr[] = $row['year']  . " -      From " . $row['start_date'] . " To " . $row['end_date'];
        $valueArr[] = $row['year'] . $row['start_date'] . $row['end_date'];
    }

    self::buildGenericDD($tagId,$lableArr,$valueArr,$value,$readOnly,$disabled,$onChange,$onClick,$onMouseOver);

	}

// &&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&

	public static function getCPYearDD($tagId,$value,$readOnly,$disabled,$onChange,$onClick,$onMouseOver,$dbConn,$principalId) {

	  global $ROOT, $PHPFOLDER;
	  
	  $mfPeriod = array(date("Y"), date("Y",strtotime("-1 year")));
	  
    foreach ($mfPeriod as $row) {
        $lableArr[] = $row;
        $valueArr[] = $row;
    }

    self::buildGenericDD($tagId,$lableArr,$valueArr,$value,$readOnly,$disabled,$onChange,$onClick,$onMouseOver);

	}

// &&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&

	public static function getUserCategory($tagId,$value,$readOnly,$disabled,$onChange,$onClick,$onMouseOver,$dbConn) {

	  global $ROOT, $PHPFOLDER;
	  include_once($ROOT.$PHPFOLDER."DAO/AdministrationDAO.php");

	  $adminDAO = new AdministrationDAO($dbConn);
	  $uCat = $adminDAO->getUserCategoryAll();

          foreach ($uCat as $row) {
            $lableArr[] = $row['name'];
            $valueArr[] = $row['code'];
          }

          self::buildGenericDD($tagId,$lableArr,$valueArr,$value,$readOnly,$disabled,$onChange,$onClick,$onMouseOver);

	}


	public static function getDocumentReasonByAssociatedStatus($tagId,$value,$readOnly,$disabled,$onChange,$onClick,$onMouseOver,$dbConn, $statusUid) {

	  global $ROOT, $PHPFOLDER;
	  include_once($ROOT.$PHPFOLDER."DAO/TransactionDAO.php");

	  $transactionDAO = new TransactionDAO($dbConn);
	  $uReasonArr = $transactionDAO->getDocumentReasonByAssociatedStatus($statusUid);

          $lableArr = array('select...');
          $valueArr = array('');
          foreach ($uReasonArr as $row) {
            $lableArr[] = $row['description'];
            $valueArr[] = $row['uid'];
          }

          self::buildGenericDD($tagId,$lableArr,$valueArr,$value,$readOnly,$disabled,$onChange,$onClick,$onMouseOver);
	}


	public static function getDocumentServiceTypes($tagId,$value,$readOnly,$disabled,$onChange,$onClick,$onMouseOver,$dbConn) {

	  global $ROOT, $PHPFOLDER;
	  include_once($ROOT.$PHPFOLDER."DAO/TransactionDAO.php");

	  $transactionDAO = new TransactionDAO($dbConn);
	  $uReasonArr = $transactionDAO->getDocumentServiceTypesAll();

          $lableArr = array('Not Selected');
          $valueArr = array('0');
          foreach ($uReasonArr as $row) {
            $lableArr[] = $row['description'];
            $valueArr[] = $row['uid'];
          }

          self::buildGenericDD($tagId,$lableArr,$valueArr,$value,$readOnly,$disabled,$onChange,$onClick,$onMouseOver);
	}

	public static function getDocumentRepCodes($tagId,$value,$readOnly,$disabled,$onChange,$onClick,$onMouseOver,$dbConn, $principalAliasId) {

	  global $ROOT, $PHPFOLDER;
	  include_once($ROOT.$PHPFOLDER."DAO/TransactionDAO.php");

	  $transactionDAO = new TransactionDAO($dbConn);
	  $uReasonArr = $transactionDAO->getDocumentRepCodes($principalAliasId);
          $lableArr = array('Use Default');
          $valueArr = array('0');
          foreach ($uReasonArr as $row) {
          	 
            $lableArr[] = $row['description'];
            $valueArr[] = $row['uid'];
          }

          self::buildGenericDD($tagId,$lableArr,$valueArr,$value,$readOnly,$disabled,$onChange,$onClick,$onMouseOver);
	}


	public static function getPrincipalSalesRepDD($tagId,$value,$readOnly,$disabled,$onChange,$onClick,$onMouseOver,$dbConn,$userId,$principalId) {

	  global $ROOT, $PHPFOLDER;
	  include_once($ROOT.$PHPFOLDER."DAO/StoreDAO.php");

	  $storeDAO = new StoreDAO($dbConn);
	  $repArr = $storeDAO->getPrincipalSalesRepAll($principalId);

          $lableArr = array('No Sales Rep Selected');
          $valueArr = array('0');
          foreach ($repArr as $row) {
            $lableArr[] = $row['rep_code'] . ' - ' . $row['first_name'] . ' ' . $row['surname'];
            $valueArr[] = $row['uid'];
          }

          self::buildGenericDD($tagId,$lableArr,$valueArr,$value,$readOnly,$disabled,$onChange,$onClick,$onMouseOver);
	}

	public static function getMeasurementTypes($tagId,$value,$readOnly,$disabled,$onChange,$onClick,$onMouseOver) {

          $lableArr[] = 'type...'; $valueArr[] = 0;
          $lableArr[] = 'Millimeter'; $valueArr[] = MEASURE_MILLIMETER;
          $lableArr[] = 'Centimeter'; $valueArr[] = MEASURE_CENTIMETER;
          $lableArr[] = 'Meter'; $valueArr[] = MEASURE_METER;
          $lableArr[] = 'Inch'; $valueArr[] = MEASURE_INCH;
          $lableArr[] = 'Foot'; $valueArr[] = MEASURE_FOOT;

          self::buildGenericDD($tagId,$lableArr,$valueArr,$value,$readOnly,$disabled,$onChange,$onClick,$onMouseOver);

	}


	public static function getDocumentStatusLimitedDD($tagId,$value,$readOnly,$disabled,$onChange,$onClick,$onMouseOver,$dbConn) {

		global $ROOT, $PHPFOLDER;
		include_once($ROOT.$PHPFOLDER.'DAO/TransactionDAO.php');
		$permission = '';
		$style = 'text-align:left; ';

		if ($readOnly=="Y") $permission=" READONLY ";
		if ($disabled=="Y") {
			$permission.=" DISABLED ";
			$style.=" background-color:silver; border-style:solid; border-width:1px; border-color:#DDDDFF;";
		}

		$transactionDAO=new TransactionDAO($dbConn);
		$docStatusArr=$transactionDAO->getDocumentStatusArray(true);
		print("<select id=\"".$tagId."\" name=\"".$tagId."\" style='".$style."' ".$permission." onChange=\"".$onChange."\" >");
		print("<option value=''>No Document Status Selected</option>");
		foreach ($docStatusArr as $row) {
			print("<option value=\"".$row['uid']."\" ");
			if ($value==$row['uid']) echo "SELECTED"; else { echo ""; }
			print(">".
						$row['uid'].
						DELIMITER_OTHER_1.
						$row['description'].
						"</option>");
		}
		echo '</select>';
	}


	public static function getUserReportsDD($tagId,$value,$readOnly,$disabled,$onChange,$onClick,$onMouseOver,$dbConn,$userId, $principalId) {

		global $ROOT, $PHPFOLDER;
		include_once($ROOT.$PHPFOLDER.'DAO/ReportDAO.php');
		$permission = '';
		$style = 'text-align:left; ';

		if ($readOnly=="Y") $permission=" READONLY ";
		if ($disabled=="Y") {
			$permission.=" DISABLED ";
			$style.=" background-color:silver; border-style:solid; border-width:1px; border-color:#DDDDFF;";
		}

		$reportDAO=new ReportDAO($dbConn);
		$mfRep = $reportDAO->getAllReportsForUser($userId, $principalId);
		print("<select id=\"".$tagId."\" name=\"".$tagId."\" style='".$style."' ".$permission." onChange=\"".$onChange."\" >");
		print("<option value=''>Not Selected</option>");
		foreach ($mfRep as $row) {
			print("<option value=\"".$row['uid']."\" ");
			if ($value==$row['uid']) echo "SELECTED"; else { echo ""; }
			print(">".
						$row['report_name'].
						"</option>");
		}
		echo '</select>';
	}


	public static function getBroadcastOutputTypesDD($tagId,$value,$readOnly,$disabled,$onChange,$onClick,$onMouseOver,$valueList) {

		global $ROOT, $PHPFOLDER;
		$permission = '';
		$style = 'text-align:left; ';

		if ($readOnly=="Y") $permission=" READONLY ";
		if ($disabled=="Y") {
			$permission.=" DISABLED ";
			$style.=" background-color:silver; border-style:solid; border-width:1px; border-color:#DDDDFF;";
		}

		$listArr=explode(",",$valueList);
		foreach ($listArr as $v) {
			switch (strval($v)) {
				case OT_CSV: $desc="CSV Attachment"; break;
				case OT_HTML: $desc="Embedded HTML"; break;
				case OT_XML: $desc="XML"; break;
        case OT_ADAPTOR_SCRIPT_DECIDES: $desc="Adaptor Script Decides"; break;
        case OT_EXPORT_FILE: $desc="Export File Only"; break;
        case OT_SMS_STANDARD_TEXT: $desc="Standard SMS Text"; break;
				default: $desc="Unknown Output Type";
			}
			$mfOT[] = array("id"=>$v,"description"=>$desc);
		}

		print("<select id=\"".$tagId."\" name=\"".$tagId."\" style='".$style."' ".$permission." onChange=\"".$onChange."\" >");
		print("<option value=''>Not Selected</option>");
		foreach ($mfOT as $row) {
			print("<option value=\"".$row['id']."\" ");
			if ($value==$row['id']) echo "SELECTED"; else { echo ""; }
			print(">".
						$row['description'].
						"</option>");
		}
		echo '</select>';
	}


	public static function getBroadcastDeliveryTypesDD($tagId,$value,$readOnly,$disabled,$onChange,$onClick,$onMouseOver,$valueList){

		global $ROOT, $PHPFOLDER;
		$permission = '';
		$style = 'text-align:left; ';

		if ($readOnly=="Y") $permission=" READONLY ";
		if ($disabled=="Y") {
			$permission.=" DISABLED ";
			$style.=" background-color:silver; border-style:solid; border-width:1px; border-color:#DDDDFF;";
		}

		$typesArr = [
			BT_EMAIL => "Email",
			BT_SCREEN => "Screen",
		];

		$listArr=explode(",",$valueList);
		foreach ($listArr as $v) {
			if(isset($typesArr[strval($v)])){
				$mfDT[] = [
					"id"=>$v,
					"description"=>$typesArr[strval($v)],
				];
			}
		}

		print("<select id=\"".$tagId."\" name=\"".$tagId."\" style='".$style."' ".$permission." onChange=\"".$onChange."\" >");
		print("<option value=''>Not Selected</option>");
		foreach ($mfDT as $row) {
			print("<option value=\"".$row['id']."\" ");
			if ($value==$row['id']) echo "SELECTED"; else { echo ""; }
			print(">".
						$row['description'].
						"</option>");
		}
		echo '</select>';
	}


	// depot uid not used at present so pass empty string
	public static function getContactTypesDD($tagId,$value,$readOnly,$disabled,$onChange,$onClick,$onMouseOver,$dbConn,$principalId,$depotId) {

		global $ROOT, $PHPFOLDER;
		include_once($ROOT.$PHPFOLDER.'DAO/MiscellaneousDAO.php');
		$permission = '';
		$style = 'text-align:left; ';

		if ($readOnly=="Y") $permission=" READONLY ";
		if ($disabled=="Y") {
			$permission.=" DISABLED ";
			$style.=" background-color:silver; border-style:solid; border-width:1px; border-color:#DDDDFF;";
		}

		$miscellaneousDAO=new MiscellaneousDAO($dbConn);
		$mfCT = $miscellaneousDAO->getContactTypes($principalId, $depotId);

		print("<select id=\"".$tagId."\" name=\"".$tagId."\" style='".$style."' ".$permission." onChange=\"".$onChange."\" >");
		print("<option value=''>Not Selected</option>");

		foreach ($mfCT as $row) {
			print("<option value=\"".$row['uid']."\" ");
			if ($value==$row['uid']) echo "SELECTED"; else { echo ""; }
			print(">".
					$row['name'].
					"</option>");
		}

		echo '</select>';
	}


	public static function getCustomerTypesDD($tagId,$value,$readOnly,$disabled,$onChange,$onClick,$onMouseOver,$dbConn) {

		global $ROOT, $PHPFOLDER;
		include_once($ROOT.$PHPFOLDER.'DAO/MiscellaneousDAO.php');
		$permission = '';
		$style = 'text-align:left; ';

		if ($readOnly=="Y") $permission=" READONLY ";
		if ($disabled=="Y") {
			$permission.=" DISABLED ";
			$style.=" background-color:silver; border-style:solid; border-width:1px; border-color:#DDDDFF;";
		}

		$miscellaneousDAO=new MiscellaneousDAO($dbConn);
		$mfCT = $miscellaneousDAO->getCustomerTypes();
		print("<select id=\"".$tagId."\" name=\"".$tagId."\" style='".$style."' ".$permission." onChange=\"".$onChange."\" >");
		print("<option value=''>Not Selected</option>");
		foreach ($mfCT as $row) {
			print("<option value=\"".$row['uid']."\" ");
			if ($value==$row['uid']) echo "SELECTED"; else { echo ""; }
			print(">".
						$row['description'].
						"</option>");
		}
		echo '</select>';
	}


	public static function getUnitPriceTypeDD($tagId,$value,$readOnly,$disabled,$onChange,$onClick,$onMouseOver,$dbConn) {

		global $ROOT, $PHPFOLDER;
		include_once($ROOT.$PHPFOLDER.'DAO/ProductDAO.php');
		$permission = '';
		$style = 'text-align:left; ';

		if ($readOnly=="Y") $permission=" READONLY ";
		if ($disabled=="Y") {
			$permission.=" DISABLED ";
			$style.=" background-color:silver; border-style:solid; border-width:1px; border-color:#DDDDFF;";
		}

		$productDAO=new ProductDAO($dbConn);
		$docStatusArr=$productDAO->getUnitPriceTypes();
		print("<select id=\"".$tagId."\" name=\"".$tagId."\" style='".$style."' ".$permission." onChange=\"".$onChange."\" >");
		print("<option value=''>Not Selected</option>");
		foreach ($docStatusArr as $row) {
			print("<option value=\"".$row['uid']."\" ");
			if ($value==$row['uid']) echo "SELECTED"; else { echo ""; }
			print(">".
						$row['description'].
						"</option>");
		}
		echo '</select>';
	}


	public static function getReportOutputTypesDD($tagId,$chosenValue,$readOnly,$disabled,$onChange,$onClick,$onMouseOver) {

		$valueArr = array(SCD_OT_CSV,
                      SCD_OT_HTML,
                      SCD_OT_PDF,
                      //SCD_OT_XML
                      );
		$lableArr = array('CSV / EXCEL',
                      'Embedded HTML',
                      "Adobe Reader / PDF"
                       //'XML'
                       );

		self::buildGenericDD($tagId,$lableArr,$valueArr,$chosenValue,$readOnly,$disabled,$onChange,$onClick,$onMouseOver);

	}


	public static function getProductMinorCategoryTree($tagId, $productUId = false, $readOnly,$disabled,$onChange,$onClick,$onMouseOver,$dbConn,$principalId, $systemId) {

          global $ROOT, $PHPFOLDER;
          include_once($ROOT.$PHPFOLDER.'DAO/ProductDAO.php');

          $productDAO = new ProductDAO($dbConn);
          $mfPMGL = $productDAO->getProductMinorCategoryLables($principalId, $systemId);

          if (sizeof($mfPMGL)==0) {
            echo "&nbsp;&nbsp;&nbsp;<i style='color:#666'>not configured</i>";
            return;
          }

          $mfPMG = $productDAO->getAllProductMinorCategory($principalId, $systemId);
          $mfP = array();
          if(!empty($productUId)){
            $mfP = $productDAO->getProductMinorCategoryByProductUid($productUId);
          }

          echo '<table class="tableReset">';
          foreach($mfPMGL as $row){
            echo '<tr><td>' . $row['lable'];
            if($row['required']=="Y")
              GUICommonUtils::requiredField();
            echo  '</td><td>';
            $lableArr = array(0=>'select... ' . str_repeat('&nbsp;',30));
            $valueArr = array(0=>'');
            if(isset($mfPMG[$row['uid']])){
              foreach($mfPMG[$row['uid']] as $i){
                $lableArr[] = $i['value'];
                $valueArr[] = $i['uid'];
              }
            }
            $chosenVal = "";
            if(count($mfP)>0){
              foreach($mfP as $p){
                if($p['minor_category_type_uid'] == $row['uid']){
                  $chosenVal = $p['product_minor_category_uid'];
                }
              }
            }
            self::buildGenericDD($tagId.'['.$row['uid'].']',$lableArr,$valueArr,$chosenVal,$readOnly,$disabled,$onChange,$onClick,$onMouseOver);
            echo '</td></tr>';
          }
          echo '</table>';

	}

	public static function getProductMinorCategoryFilter($tagId, $productSelArr, $readOnly,$disabled,$onChange,$onClick,$onMouseOver,$dbConn,$principalId, $systemId, $orientation = 'H') {

          global $ROOT, $PHPFOLDER;
          include_once($ROOT.$PHPFOLDER.'DAO/ProductDAO.php');

          $productDAO = new ProductDAO($dbConn);
          $mfPMGL = $productDAO->getProductMinorCategoryLables($principalId, $systemId);

          if (sizeof($mfPMGL)==0) {
            echo '<i>not active</i>';
            return;
          }

          $orientation = strtoupper($orientation);
          if($orientation == 'V'){
            $orientation = 'V';
          } else {
            $orientation = 'H';  //horizontal
          }

          $mfPMG = $productDAO->getAllProductMinorCategory($principalId, $systemId);

          echo '<table class="tableReset">';

          if($orientation == 'H'){
            echo '<tr>';
          }


          foreach($mfPMGL as $row){

            if($orientation == 'H'){
              echo '<td>';
            } else {
              echo '<tr><td>';
            }

            echo $row['lable'] . '<br>';
            $lableArr = array(0=>'select... ' . str_repeat('&nbsp;',30));
            $valueArr = array(0=>'');
            if(isset($mfPMG[$row['uid']])){
              foreach($mfPMG[$row['uid']] as $i){
                $lableArr[] = $i['value'];
                $valueArr[] = $i['uid'];
              }
            }
            $chosenVal = "";
            if(is_array($productSelArr)){
              if(isset($productSelArr[$row['uid']])){
                $chosenVal = $productSelArr[$row['uid']];
              }
            }
            self::buildGenericDD($tagId.'['.$row['uid'].']',$lableArr,$valueArr,$chosenVal,$readOnly,$disabled,$onChange,$onClick,$onMouseOver);

            if($orientation == 'H'){
              echo '</td>';
            } else {
              echo '</td></tr>';
            }

          }
          if($orientation == 'H'){
            echo '</tr>';
          }
          echo '</table>';

	}

	public static function getTaskmanAccounts($tagId,$value,$readOnly,$disabled,$onChange,$onClick,$onMouseOver,$dbConn) {

	  global $ROOT, $PHPFOLDER;
	  include_once($ROOT.$PHPFOLDER.'DAO/AdministrationDAO.php');

	  $adminDAO = new AdministrationDAO($dbConn);
	  $mfA = $adminDAO->getTaskmanAccounts();

	  $lableArr = array('No Taskman Account Selected');
	  $valueArr = array('');

	  foreach ($mfA as $row) {
	    $lableArr[] = $row['uid'] . DELIMITER_OTHER_1 . $row['client_name'];
	    $valueArr[] = $row['uid'];
	  }

	  self::buildGenericDD($tagId,$lableArr,$valueArr,$value,$readOnly,$disabled,$onChange,$onClick,$onMouseOver);

	}

	public static function getKwelangaDebtorsAccount($tagId,$value,$readOnly,$disabled,$onChange,$onClick,$onMouseOver,$dbConn, $principalId) {

	  global $ROOT, $PHPFOLDER;
	  include_once($ROOT.$PHPFOLDER.'DAO/reportsDAO.php');

	  $reportsDAO = new reportsDAO($dbConn);
	  $mfA = $reportsDAO->getKwelangaDebtorsAccount($principalId);

	  $lableArr = array('No Debtors Account Selected');
	  $valueArr = array('');

	  foreach ($mfA as $row) {
	    $lableArr[] = $row['sfd_value'] . DELIMITER_OTHER_1 . $row['name'];
	    $valueArr[] = $row['sfd_value'];
	  }

	  self::buildGenericDD($tagId,$lableArr,$valueArr,$value,$readOnly,$disabled,$onChange,$onClick,$onMouseOver);

	}
// ******************************************************************************************************************************************************************
	public static function getKwelangaDebtorsReportType($tagId,$value,$readOnly,$disabled,$onChange,$onClick,$onMouseOver,$dbConn, $principalId) {

	  global $ROOT, $PHPFOLDER;
	  include_once($ROOT.$PHPFOLDER.'DAO/reportsDAO.php');

	  $reportsDAO = new reportsDAO($dbConn);
	  $mfA = $reportsDAO->getKwelangaDebtorsReportType($principalId);

	  $lableArr = array('No Report Type Selected');
	  $valueArr = array('');

	  foreach ($mfA as $row) {
	    $lableArr[] = $row['report_name'];
	    $valueArr[] = $row['param_name'];
	  }

	  self::buildGenericDD($tagId,$lableArr,$valueArr,$value,$readOnly,$disabled,$onChange,$onClick,$onMouseOver);

	}
// ******************************************************************************************************************************************************************

}
?>
