<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');

class ValidationCommonUtils {
	
	public static function translateYNtoBoolean($field) {
		if (strval($field)=="Y") return 1;
		if (strval($field)=="N") return 0;
		return $field;
	}
	
	public static function checkFieldYesNoSimple($field) {
		if((strval($field)!="Y") && (strval($field)!="N")) {
			return false; 
		};
		return true;
	}
	
	public static function checkFieldBooleanSimple($field) {
		if((strval($field)!="1") && (strval($field)!="0")) {
			return false; 
		};
		return true;
	}
	
	public static function checkFieldYesNo($field, $fieldName) {
		if((strval($field)!="Y") && (strval($field)!="N")) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Please choose either YES or NO for field ".$fieldName;
			return false; 
		};
		return true;
	}
	
	public static function checkFieldNumeric($field, $fieldName, $blankAllowed) {
		if ($blankAllowed) {
	   		if(($field!="") && (!preg_match(GUI_PHP_INTEGER_REGEX,$field))) {
	   			$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description=$fieldName.", if entered, must be numeric integer";
				return false; 
			}
		} else {
			if(!preg_match(GUI_PHP_INTEGER_REGEX,$field)) {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description=$fieldName." must be numeric integer and is required";
				return false; 
			};
		  }
		return true;
	}
	
	public static function checkPostingType($field) {
   		if(($field!=="UPDATE") && ($field!=="INSERT") && ($field!=="DELETE")) {
   			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Posting Type Invalid";
			return false; 
		};
		return true;
	}
	
	public static function checkStatus($status) {
   		if(
   			(strval($status)!=FLAG_STATUS_ACTIVE) && 
   			(strval($status)!=FLAG_STATUS_DELETED)
   		  ) {
			return false; 
		};
		return true;
	}
	
	public static function checkIsDate($field,$mask,$withSeparator=false) {
		switch ($mask) {
			case "yyyymmdd":
				if ($withSeparator) {
					if(!checkdate((int)substr($field,5,2),(int)substr($field,8,2),(int)substr($field,0,4))) return false; else return true;
				} else {
					if(!checkdate((int)substr($field,4,2),(int)substr($field,6,2),(int)substr($field,0,4))) return false; else return true;
				}
				break;
			default:
				return false;
		}
	}
	
	public static function checkIsTime($field,$mask) {
		switch ($mask) {
			case "HH:MI:SS":
				$timeArr=explode(":",$field);
				if (sizeof($timeArr)!=3) return false;
				if (
					(($timeArr[0]<=24) && ($timeArr[0]>=0)) &&
					(($timeArr[1]<=59) && ($timeArr[1]>=0)) &&
					(($timeArr[2]<=59) && ($timeArr[2]>=0))
				   ) return true; else return false;
				break;
			default:
				return false;
		}
	}
	
}
?>
