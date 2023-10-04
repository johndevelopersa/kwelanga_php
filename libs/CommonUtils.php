<?php
include_once('ROOT.php');
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');

class CommonUtils {
	// this function ONLY acceps dates without time.
	// NOTE : there seems to be a problem when inserting into MYSQL -> the date is a day behind, so don't use this until tested
	public static function convertPHPtoUnixTime ($date) {
		if (preg_match(GUI_PHP_DATE_REGEX,$date,$parts)) {
			// checkdate is in format of m/d/Y
			if(!checkdate($parts[GUI_PHP_MONTH_POS],$parts[GUI_PHP_DAY_POS],$parts[GUI_PHP_YEAR_POS])) {
					return false;
				}
		} else {
			return false;
		  }

	   return mktime(0,0,0,$parts[GUI_PHP_MONTH_POS],$parts[GUI_PHP_DAY_POS],$parts[GUI_PHP_YEAR_POS]); //mktime(hour,minute,second,month,day,year,is_dst)
	}

	// MySQL stores dates in "YYYY-MM-DD" format
	// DONT USE : Constant not defined
	public static function convertPHPTextDateToMySQLFormat ($date) {
		if (preg_match(GUI_PHP_DATE_REGEX,$date,$parts)) {
			// checkdate is in format of m/d/Y
			if(!checkdate($parts[GUI_PHP_MONTH_POS],$parts[GUI_PHP_DAY_POS],$parts[GUI_PHP_YEAR_POS])) {
					return false;
				}
		} else {
			return false;
		  }

	   return $parts[GUI_PHP_YEAR_POS].GUI_PHP_DATE_FORMAT_SEPARATOR.$parts[GUI_PHP_MONTH_POS].GUI_PHP_DATE_FORMAT_SEPARATOR.$parts[GUI_PHP_DAY_POS];
	}

	public static function formatCompactDate ($date) {
		if (strlen($date)!=8) return false;

		return substr($date,0,4)."-".substr($date,4,2)."-".substr($date,6,2);
	}

	public static function formatFromDDsMMsYYYY ($date,$separator="/") {
		if (strlen($date)!=10) return false;

		$arr=explode($separator,$date);
		if (sizeof($arr)!=3) return false;

		return $arr[2]."-".$arr[1]."-".$arr[0];
	}

	public static function getMicroTime() {
		$mtime = microtime();
   		$mtime = explode(" ",$mtime);
   		$mtime = $mtime[1] + $mtime[0];
   		return $mtime;
	}

	// get user date and time according to their local timezone
	public static function getGMTime($daysOffset=0) {
		return gmdate(GUI_PHP_DATETIME_FORMAT, time());
	}

	// get user date and time according to their local timezone, compressed
	public static function getGMTimeCompressed($daysOffset=0) {
		return gmdate(GUI_PHP_DATETIME_FORMAT_COMPRESSED, time()+(86400*$daysOffset));
	}

	// get user date and time according to their local timezone
	public static function getUserTime($daysOffset=0) {
		return gmdate(GUI_PHP_DATETIME_FORMAT, time()+7200+(86400*$daysOffset)); // for the moment just use SA until uzer timezones implemented
	}

	// get user date and time according to their local timezone, compressed
	public static function getUserTimeCompressed($daysOffset=0) {
		return gmdate(GUI_PHP_DATETIME_FORMAT_COMPRESSED, time()+7200+(86400*$daysOffset)); // for the moment just use SA until uzer timezones implemented
	}

	// get user date according to their local timezone
	public static function getUserDate($daysOffset=0) {
		return gmdate(GUI_PHP_DATE_FORMAT, time()+7200+(86400*$daysOffset)); // for the moment just use SA until uzer timezones implemented
	}

	// convert GMT date to user's local timezone
	public static function convertUserDateTime($GMTdate) {
		$time = strtotime($GMTdate)+(60*60*2); // for the moment hardcode gmt+2
		return date("Y-m-d H:i:s",$time);
	}


	// convert ONLY GMT time to user's local timezone
	public static function convertUserTime($GMTtime) {
		$time = strtotime($GMTtime)+(7200); // for the moment hardcode gmt+2
		return date("H:i:s",$time);
	}


	public static function submitErrorTO($type, $description, $identifier = null, $identifier2 = null, $object = null) {
          $eTo = new ErrorTO();
          $eTo->type = $type;
          $eTo->description = str_replace("'",'',$description); //add more parsing?
          $eTo->identifier = $identifier;
          $eTo->identifier2 = $identifier2;
          $eTo->object = $object;
          
          echo self::getJavaScriptMsg($eTo);
          return;
	}


	// receives ErrorTO
	public static function getJavaScriptMsg($p_err) {
		$p_err->description = str_replace(array("\r\n", "\r", "\n"),'<BR>',$p_err->description);
		$JS="function MsgClass () {this.type; this.description; this.identifier; this.identifier2; } ";
		$JS.="var msgClass=new MsgClass(); ";
		$JS.="msgClass.type='".$p_err->type."'; ";
		$JS.="msgClass.identifier='".$p_err->identifier."'; ";
		$JS.="msgClass.identifier2='".$p_err->identifier2."'; ";
		$JS.="msgClass.description='".str_replace("'",'"',$p_err->description)."'; ";
		return $JS;
	}

	//splits up the mysql_info function, commonly used because Update does not show in affected_rows() if all fields same.
	public static function getMysqlInfo($info){
		$return = array();
	    preg_match("/Records: ([0-9]+)/", $info, $records);
	  	preg_match("/Duplicates: ([0-9]+)/", $info, $dupes);
	  	preg_match("/Warnings: ([0-9]+)/", $info, $warnings);
	  	preg_match("/Deleted: ([0-9]+)/", $info, $deleted);
	  	preg_match("/Skipped: ([0-9]+)/", $info, $skipped);
	  	preg_match("/Rows matched: ([0-9]+)/", $info, $rows_matched);
	  	preg_match("/Changed: ([0-9]+)/", $info, $changed);

	    if (isset($records[1])) $return['records'] = $records[1]; else $return['records'] = "0";
	    if (isset($dupes[1])) $return['duplicates'] = $dupes[1]; else $return['duplicates'] = "0";
	    if (isset($warnings[1])) $return['warnings'] = $warnings[1]; else $return['warnings'] = "0";
	    if (isset($deleted[1])) $return['deleted'] = $deleted[1]; else $return['deleted'] = "0";
	    if (isset($skipped[1])) $return['skipped'] = $skipped[1]; else $return['skipped'] = "0";
	    if (isset($rows_matched[1])) $return['rows_matched'] = $rows_matched[1]; else $return['rows_matched'] = "0";
	    if (isset($changed[1])) $return['changed'] = $changed[1]; else $return['changed'] = "0";

    	return $return;
	}

	public static function getGUID(){
	    if (function_exists('com_create_guid')){
	       return com_create_guid();
	    }else{
			mt_srand((int)microtime(true)*10000);//optional for php 4.2.0 and up.//optional for php 4.2.0 and up.
	        $charid = strtoupper(md5(uniqid(rand(), true)));
	        $hyphen = chr(45);// "-"
	        $uuid = chr(123)// "{"
	                .substr($charid, 0, 8).$hyphen
	                .substr($charid, 8, 4).$hyphen
	                .substr($charid,12, 4).$hyphen
	                .substr($charid,16, 4).$hyphen
	                .substr($charid,20,12)
	                .chr(125);// "}"
	        return $uuid;
	    }
	}

	public static function getRandomInteger(){
       mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
       $charid = rand();
       return $charid;
	}

	// return a stripped down version of a name (used to test for uniqueness eg. store deliver name), leaving only alphanumeric
	public static function getStrippedValue($val) {
		$name=trim($val);
		$after=preg_replace('/\s+/','',$name);
		$after=preg_replace('/[^a-zA-Z0-9]/', '', $after);
		$after=strtolower($after);

		return $after;
	}

	// for the moment just cater for all files
	public static function unzipFiles($zipPathFile, $destFolder, $specificFiles, $ignoreInternalPath=false) {
		$all_files = true;

		if (($z = zip_open($zipPathFile))=="11") {
			echo "could not open file !";
			return false;
		}

		while ($entry = zip_read($z)) {
		    $entry_name = zip_entry_name($entry);

		    // check if all files should be unzipped, or the name of
		    // this file is on the list of specific files to unzip
		    if ($all_files || ((isset($specificFiles[$entry_name])) && ($specificFiles[$entry_name]))) {

		        // only proceed if the file is not 0 bytes long
		        if (zip_entry_filesize($entry)) {
		            $dir = dirname($entry_name);

		            // make all necessary directories in the file's path, set recursion = true so nested sub dirs also created at same time otherwise error results
		            if ($ignoreInternalPath===false) {
		            	if (! is_dir($destFolder.$dir)) { mkdir($destFolder.$dir,0777,true); }
		        	}

		            $file = basename($entry_name);

		            if (zip_entry_open($z,$entry)) {
		            	if ($ignoreInternalPath===true) $toPath=$destFolder;
		            	else $toPath=$destFolder.$dir.'/';

		                if ($fh = fopen($toPath.$file,'w')) {
		                    // write the entire file
		                    if (fwrite($fh,
		                           zip_entry_read($entry,zip_entry_filesize($entry)))===false) {
		                           		echo "could not execute zip_entry_read";
		                        		return false;
		                           }
		                    if (fclose($fh)===false) return false;
		                } else {
		                	echo "could not create destination file";
		                    return false;
		                }
		                zip_entry_close($entry);
		            } else {
		                echo "can't open entry $entry_name";
		                return false;
		            }
		        }
		    }
		}

		return true;

	}

	// create the bkup dirs on local / mapped drive
	// pass type as blank to just create the bkup/ onwards
	public static function createBkupDirs($path,$preType="") {

		$bkupFolder=$path;
		// prefolders
		if ($preType=="1") {
			$bkupFolder.=DIR_SUCCESS_FOLDER;
			$handle = @opendir($bkupFolder);
			if ($handle===false) {
				$makeDir=@mkdir($bkupFolder);
				if ($makeDir===false) return false;
			}
		} else if ($preType=="2") {
			$bkupFolder.=DIR_ERROR_FOLDER;
			$handle = @opendir($bkupFolder);
			if ($handle===false) {
				$makeDir=@mkdir($bkupFolder);
				if ($makeDir===false) return false;
			}
		}
		// end: prefolders

		$bkupFolder.="bkup/";
		$handle = @opendir($bkupFolder);
		if ($handle===false) {
			$makeDir=@mkdir($bkupFolder);
			if ($makeDir===false) return false;
		}
		$bkupFolder.=date("Y")."/";
		$handle = @opendir($bkupFolder);
		if ($handle===false) {
			$makeDir=@mkdir($bkupFolder);
			if ($makeDir===false) return false;
		}
		$bkupFolder.=date("m")."/";
		$handle = @opendir($bkupFolder);
		if ($handle===false) {
			$makeDir=@mkdir($bkupFolder);
			if ($makeDir===false) return false;
		}

		return $bkupFolder;
	}

  // has dd level
  public static function createLocalBackup($folder){
    $bkupFolder = $folder."bkup/" . date("Y") . "/" . date("m") . "/" . date("d") . "/";
    if(!is_dir($bkupFolder)){
      mkdir($bkupFolder, 0777, TRUE);  //create recursive directory based on path.
    }
    return $bkupFolder;
  }

  // has daily level
  public static function createDailyBackup($folder){
    $bkupFolder = $folder. date("d") ;
    if(!is_dir($bkupFolder)){
      mkdir($bkupFolder, 0777, TRUE);  //create recursive directory based on path.
    }
    return $bkupFolder;
  }

	// paramStr = p1=xxx&p2=yyy&p3.... ; separator = & ; paramName = p1 ; paramValueAsignment = "="
	public static function getParamValuesFromString($paramStr,$paramName,$paramSeparator="&",$paramValueAsignment="=") {
		$params=explode($paramSeparator,$paramStr);
		foreach ($params as $p) {
			$arr=explode($paramValueAsignment,$p);
			if (isset($arr[0])) {
				if ($arr[0]==$paramName) {
					return $arr[1];
				}
			}
		}

		return "";
	}


  public static function isDepotUser(){
    return (isset($_SESSION['user_category']) && $_SESSION['user_category'] == PT_DEPOT) ? true : false;
  }

  public static function isAdminUser(){
    return (isset($_SESSION['admin_user']) && ($_SESSION['admin_user']=="Y")) ? true : false;
  }

  public static function isStaffUser(){
    return (isset($_SESSION['staff_user']) && ($_SESSION['staff_user']=="Y")) ? true : false;
  }

  public static function getSystemConventions(){

    global $ROOT, $PHPFOLDER;
    if (!isset($_SESSION)) session_start();
    $systemId = $_SESSION['system_id'];
    $systemName = $_SESSION['system_name'];
    
    include_once($ROOT . $PHPFOLDER . 'properties/conventions/'.$systemId .'_'. $systemName.'.php');
   }

  //POST VAR BUILDER - PUTS THE POST VARS INTO THE VARIABLES AS post[POSTNAME].
  //to preset a post var set the value before calling this function.
  public static function setPostVars(){
    if(count($_POST)>0){
      foreach($_POST as $name => $val){
        if(!is_array($val)){
          $GLOBALS['post'.$name] = htmlentities($val);
        } else {
          $GLOBALS['post'.$name] = $val;
        }
      }
    }
  }

  // taskman control
  public static function revertSession() {
    if (!isset($_SESSION)) session_start();
    if (!isset($_SESSION["revert"])) return;

    // user was logged in
    $_SESSION["taskman_account"] = ((isset($_SESSION["revert"]["taskman_account"]))?$_SESSION["revert"]["taskman_account"]:"");
    if (isset($_SESSION["revert"]["user_id"])) {
      $_SESSION["user_id"] = $_SESSION["revert"]["user_id"];
      $_SESSION["username"] = $_SESSION["revert"]["username"];
      $_SESSION["password"] = $_SESSION["revert"]["password"];
      $_SESSION["user_email"] = $_SESSION["revert"]["user_email"];
      $_SESSION["full_name"] = $_SESSION["revert"]["full_name"];
      $_SESSION["user_key"] = $_SESSION["revert"]["user_key"];
    }

    unset($_SESSION["revert"]);
  }

}
?>
