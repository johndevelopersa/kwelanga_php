<?php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER."properties/Constants.php");
include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER.'DAO/PostMiscellaneousDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/MiscellaneousDAO.php');
include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');
include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
include_once($ROOT.$PHPFOLDER.'libs/BroadcastingUtils.php');
include_once($ROOT.$PHPFOLDER.'libs/newrelic.php');
include_once($ROOT.$PHPFOLDER.'TO/PostingFTPFileLogTO.php');


set_time_limit(5*60); // 5 mins - don't allow overlapping exec.


echo "Job Started: ".CommonUtils::getGMTime(0)."<BR>";

  $ftpOrders = new importFTPOrders();

echo "<BR>Job Ended: ".CommonUtils::getGMTime(0)."<BR>";
echo "----------------------------------------------------------------<BR>";
echo "[***EOS***]";



//ftp class controller.
class importFTPOrders{


  public $errorTO;
  private $dbConn;
  private $miscDAO;
  private $postMiscDAO;
  private $ftpConn = false;
  private $tempPrefix = '_TEMP_';
  private $fileLogArr = [];
  private $fileCountArr = [];
  private $processID;
  private $currentIterationServerHost;

  public function __construct() {

    $this->errorTO = new ErrorTO();
    $this->dbConn = new dbConnect();
    $this->dbConn->dbConnection();
    $this->miscDAO = new MiscellaneousDAO($this->dbConn);
    $this->postMiscDAO = new PostMiscellaneousDAO($this->dbConn);

    $this->processID = (isset($_GET['process'])) ? ($_GET['process']) : (false);

    if($this->processID!==false){
         $this->runServers();

      //inserts log and updates locations
      //to disable comment out.
      $this->runFTPLog();  //ftp logging of incoming files
      $this->runFTPLocationsUpdate();  //location file counts and last downloaded timestamp.
    } else {
      echo 'PROCESS ID NOT PASSED!';
    }
  }


  //get Active FTP Servers.
  public function runServers(){

    $ftpServersArr = $this->miscDAO->getActiveFTPServers($this->processID);

    if(count($ftpServersArr)==0){
      echo 'NO ACTIVE SERVERS FOR PROCESS ID: ' . $this->processID . ' .<BR>';
    } else {
      foreach($ftpServersArr as $server){  //loop through each server

        //connect to server.
        $this->currentIterationServerHost = $server['host'];
        $cResult = $this->ftpConnect($server);

        //log failure/success against ftp server.
        $lResult = $this->postMiscDAO->postFTPServerResult($server['uid'], $cResult->type, $cResult->description);
        if($lResult->type==FLAG_ERRORTO_SUCCESS){
          $this->dbConn->dbinsQuery("commit;");
        } else {
          $this->dbConn->dbinsQuery("rollback;");
          echo 'Failed to Update FTP Server Result: ' . $lResult->description;
        }


        if($cResult->type==FLAG_ERRORTO_SUCCESS && $this->ftpConn!==false){  //connection is ok.

          //get locations allocated to server.
          $locationArr = $this->miscDAO->getActiveFTPLocations($server['uid']);

          echo "----------------------------------------------------------------<BR>";

          if(count($locationArr)==0){

            echo 'NO LOCATIONS<BR>';

          } else {

            if($server['direction'] == "PULL"){
              $this->runPullProcess($locationArr);
            } else if($server['direction'] == "PUSH"){
              $this->runPushProcess($locationArr);
            }

          }
        } else {

          /********************************/
          /*    ALLOWED RUNNING TIMES     */
          /********************************/

          //GMT 0 - times
          $now = strtotime(gmdate("H:i"));
          $notBefore = '04:00'; //2 hours before 8am.
          $notAfter = '17:00';  //2 hours after 5pm

          if(($now > strtotime($notAfter)) || ($now < strtotime($notBefore))){

            //silent error notifications
            //[HERE]

          } else {

            BroadcastingUtils::sendAlertEmail('ERROR: Import FTP Failed!', 'Error occured running the Import FTP Script' . "\n" .'Description: ' . $cResult->description, 'N', false);
          }

          /********************************/
        }

      }  //eo server loop.


    }

  }


  //PULL : Get files
  private function runPullProcess($locationArr){

    //group locations by server location
    $grpLocationArr = array();
    foreach($locationArr as $l){
      $grpLocationArr[$l['server_file_path']][] = $l;
    }

    foreach($grpLocationArr as $serverFolder => $getFileArr){


      $fwArr = array(); foreach($getFileArr as $f) $fwArr[] = $f['file_wildcard'];
      echo '<strong>' . $serverFolder . '</strong><BR><i>Pulling:</i> ' . join(' | ', $fwArr) . '<BR>';

      $folderFileArr = $this->getFTPFolder($serverFolder);
      
//      print_r($folderFileArr);

      if($folderFileArr==false || count($folderFileArr)==0){
        echo "0 Files<BR>";
      } else {

        $createBkup = false;  //only create a backup once per loop.
        $rejectedCnt = 0;
        foreach($getFileArr as $getFile){

          //backup folder
          $bkFolder = false;
          if(($getFile['server_file_backup_flag']=="Y") && ($createBkup==false)){

            $bkupResult = $this->createFTPBackup($serverFolder);
            if ($bkupResult===false) {
              echo "Failed to create backup Folder: ".$serverFolder.'<BR>';
              break 1;  //leave this folder NO BACKUP FOLDER - NO DOWNLOADING FROM HERE.
            }

            $bkFolder = $bkupResult;  //returns folder if successful.
            $createBkup = true;
          }
          if($getFile['server_file_backup_flag']=="Y"){
            $createBkup = false;  //reset created folder flag.
          }


          //file ma
         
          foreach($folderFileArr as $file){
          	
          	echo "<br>Next<br>";
          	echo(($file));
          	
          	if(substr(basename($file),0,14) =='UniversalQuick') {
          		
          		    $filename = substr(basename($file), -strpos($filename,"UNIVERSAL",0));
                  if(fnmatch($getFile['file_wildcard'], $filename, FNM_CASEFOLD)){
                      $this->getFTPFile(trim(substr($file, -65)), $getFile, $bkFolder);  //handles download, moving to folder, trailers and backups.
                  } else {
                      $rejectedCnt++;
                  }  
          	} elseif(substr(basename($file),0,3) =='ITD') {
          		
          		    $filename = substr(basename($file), -strpos($filename,"UNIVERSAL",0));
                  if(fnmatch($getFile['file_wildcard'], $filename, FNM_CASEFOLD)){
                      $this->getFTPFile(trim(substr($file, -38)), $getFile, $bkFolder);  //handles download, moving to folder, trailers and backups.
                  } else {
                      $rejectedCnt++;
                  } 
          	} elseif(substr(basename($file),0,3) == 'SGX') {
          		
          		    $filename = substr(basename($file), -strpos($filename,"SGX",0));
                  if(fnmatch($getFile['file_wildcard'], $filename, FNM_CASEFOLD)){
                      $this->getFTPFile(trim(substr(trim($file), -36)), $getFile, $bkFolder);  //handles download, moving to folder, trailers and backups.
                  } else {
                      $rejectedCnt++;
                  }                   
          	} else {
                  $filename = substr(basename($file), -strpos($filename,"Exp",1));
                        
                  if(fnmatch($getFile['file_wildcard'], $filename, FNM_CASEFOLD)){
                      $this->getFTPFile(trim(substr($file, -25)), $getFile, $bkFolder);  //handles download, moving to folder, trailers and backups.
                  } else {
                      $rejectedCnt++;
                  }
          	}
          } //end of mask loop
        }  //end of file loop
        echo "{$rejectedCnt} Rejected File(s)<BR>";
      }

      echo "----------------------------------------------------------------<BR>";
    }  //end of location loop

  }

  //PUSH : Send 'local' files
  private function runPushProcess($locationArr){


    foreach($locationArr as $l){


      echo '<strong>' . $l['local_file_path']  . '</strong><BR><i>Pushing:</i> ' . $l['file_wildcard']  . '<BR>';

      if(trim($l['root_dir_constant'])!=""){
        $rootConstant = constant($l['root_dir_constant']);
        echo $rootConstant;
        echo "<BR>";
      } else {
        $rootConstant = '';
      }

      $localFolder = $rootConstant . $l['local_file_path'];
      $globParam =  $localFolder . $l['file_wildcard'];
      $fArr = glob($globParam);

      if($fArr==false || count($fArr)==0){

        echo "0 Files<BR>";

      } else {

        $bkFolder = false;  //preset
        if($l['server_file_backup_flag']=="Y"){

          $bkupResult = $this->createLocalBackup($localFolder);
          if ($bkupResult===false) {
            echo "Failed to create backup Folder: ".$localFolder.'<BR>';
            break 1;  //leave this folder NO BACKUP FOLDER - NO DOWNLOADING FROM HERE.
          }
          $bkFolder = $bkupResult;
        }

        //check if zip enabled.
        if($l['create_zip_flag']=='Y'){


          //CREATE ZIP.
          $msSeq = number_format(microtime(TRUE),4,'','');
          if($l['zip_filename']==""){
            $zipFilename = 'FILENAME'.$msSeq.'.ZIP';  //default if zip filename is blank.
          } else {
            $zipFilename = str_replace('[@MSEQ]', $msSeq,$l['zip_filename']);
          }

          $zip = new ZipArchive();
          if ($zip->open($localFolder . $zipFilename, ZIPARCHIVE::CREATE)!==TRUE) {
            echo "Failed to create ZIP Archive: ".$zipFilename.'<BR>';
            break 1;  //leave this folder NO BACKUP FOLDER - NO DOWNLOADING FROM HERE.
          } else {

            foreach($fArr as $file){
              if(is_file($file)){
                $zip->addFile($file, basename($file));    //add files
              }
            }
            $zip->close();


            echo "ZIP CREATED => " . $zipFilename . '<BR>';

            $pushResult = @ftp_put($this->ftpConn,  $l['server_file_path'] . '_TEMP_'.$zipFilename.'_TEMP_', $localFolder . $zipFilename, $this->translateMode($locationArr['ftp_type']));
            $pushRename = @ftp_rename($this->ftpConn, $l['server_file_path'] . '_TEMP_'.$zipFilename.'_TEMP_', $l['server_file_path'] . $zipFilename);
            @ftp_chmod($this->ftpConn, 0777, $l['server_file_path'] . $zipFilename);

            if($pushResult === true && $pushRename === true){

              $this->fileCountArr[$l['uid']] = (isset($this->fileCountArr[$l['uid']])) ? ($this->fileCountArr[$l['uid']]+1) : ($l['file_counter']+1);
              $this->fileLogArr[] = array(
                    'server_filepath' => $l['server_file_path'] . basename($zipFilename),
                    'filesize_bytes' => filesize($localFolder . $zipFilename),
                    'local_filepath' => $localFolder . $zipFilename,  //remember we might prepend a suffix on the file here compared to the real name.
                    'ftp_fetch_location_uid' => $l['uid']
               );

              //backup.
              if($l['server_file_backup_flag']=="Y"){
                rename($localFolder . $zipFilename, $bkFolder . $zipFilename);
                foreach($fArr as $file){
                  rename($file, $bkFolder . basename($file));
                }
              } else {
                unlink($localFolder . $zipFilename);
                foreach($fArr as $file){
                  unlink($file);
                }
              }

            }
          }

        } else {

          foreach($fArr as $file){

            $size = filesize($file);
            $pushResult = @ftp_put($this->ftpConn,  $l['server_file_path'] . basename($file), $file, $this->translateMode($locationArr['ftp_type']));
            if($pushResult === true){
              //backup.
              if($l['server_file_backup_flag']=="Y"){
                rename($file, $bkFolder . basename($file) . '_' . CommonUtils::getGMTimeCompressed(0));
              } else {
                unlink($localFolder . $zipFilename);
              }

              $this->fileCountArr[$l['uid']] = (isset($this->fileCountArr[$l['uid']])) ? ($this->fileCountArr[$l['uid']]+1) : ($l['file_counter']+1);
              $this->fileLogArr[] = array(
                  'server_filepath' => $l['server_file_path'] . basename($file),
                  'filesize_bytes' => $size,
                  'local_filepath' => $file,  //remember we might prepend a suffix on the file here compared to the real name.
                  'ftp_fetch_location_uid' => $l['uid']
               );

            } else {
              echo "<br>Could not upload (push) file : {$file}";
            }

          }

        }  //end of file loop

      }

      echo "----------------------------------------------------------------<BR>";

    } //end of location loop


  }

	//connect to server
    private function ftpConnect($serverArr){
    	
    	echo "<br>";

        $this->errorTO->type = FLAG_ERRORTO_ERROR;  //present failure - saves on setting later.
        $this->ftpConn = false;
        if(trim($serverArr['encryption']) == 'TLS/SSL Explicit encryption') {
            $this->ftpConn = ftp_ssl_connect(trim($serverArr['host']), trim($serverArr['port']));
        } else {
            $this->ftpConn = ftp_connect(trim($serverArr['host']), trim($serverArr['port']));
        }
        
        if($this->ftpConn==false){
            echo "Connecting to '".$serverArr['host']."'...\t@ERROR<BR>";
            $this->errorTO->description = 'FTP Connection Failure! Host:' . $serverArr['host'];
            return $this->errorTO;
        } else {
            echo "Connecting to '".$serverArr['host']."'...\tOK<BR>";
            
            $login = @ftp_login($this->ftpConn, trim($serverArr['username']), trim($serverArr['password']));
      if($login==false){

          echo "User '".$serverArr['username']."' logging in...\t@ERROR<BR>";
          $this->errorTO->description = 'FTP Login Failure! Host: ' . $serverArr['host'];
          return $this->errorTO;
      } else {

          echo "User '".$serverArr['username']."' logging in...\tOK<BR>";

          //change mode - we can't test this as error will occur when handling files.
          if($serverArr['passive_mode'] == 1){  //PASSIVE
            echo "Changed to Passive Mode...\tOK<BR>";            
            @ftp_set_option($this->ftpConn, FTP_USEPASVADDRESS, false);
            @ftp_pasv($this->ftpConn, true);
            
          }
          $this->errorTO->type = FLAG_ERRORTO_SUCCESS;  //sps.
          $this->errorTO->description = 'SUCCESSFUL';  //stored against ftp server

      }

    }
      return $this->errorTO;

    }

    //retrieves a list of ONLY files (no folders) from a set ftp path.
    private function getFTPFolder($folder){
        global $ROOT;

        $rawFileList = ftp_rawlist($this->ftpConn, $folder, false);  //get raw list.

        // if the ftp connection fails to connect at all
        if (!is_array($rawFileList)) {
          print_r($rawFileList);
          echo "<p>ERROR connecting to server : {$this->currentIterationServerHost} {$folder}</p>";
          $rawFileList = array();
        }

        // store a log for PULL operations as it is our only way to audit whether a client indeed put a file on the
        // server for us to fetch in case of blame
        NewRelic::logEvent(
            $logType = "ftp-log",
            $script = basename(__FILE__),
            $message = implode("\n",$rawFileList),
            $attr = [
                'host' => $this->currentIterationServerHost,
                'folder' => $folder,
            ]
        );

      	$fileList=array();
      	foreach($rawFileList as $rl) {
      		preg_match_all("/^([\-]d|^d)/",$rl,$arr);
      		if (sizeof($arr[1])>0) {
      			// is a dir
      		} else {
      			// is a file
      			preg_match_all("/[\:][0-9]{1,2}[ ]*(.*)\$/",$rl,$arr); // allow for any chars and spaces in filename
      			//could just do a dir listing again but trying to save time ftp=slow
      			if (!isset($arr[1][0])) {
      				$arr=preg_split("/[ ]/",$rl);
      				if ($arr[sizeof($arr)-1]!="") $fileList[]=$folder.$arr[sizeof($arr)-1];
      			} else {
      				if ($arr[1][0]!="") $fileList[] = $folder.$arr[1][0];
      			}
      		}
      	}
      	
      	
//      	echo "<pre>";
//      	print_r($fileList);

      	if (is_array($fileList))
      	  sort($fileList);

      	return $fileList;

    }


    private function getFTPFile($filepath, $locationArr, $backupPath){
    	echo "e<br>";
    	
    	echo basename($filepath);
    	echo "<br>";
    	echo 'ftp/temp/' .  uniqid($this->tempPrefix) . '_' . basename($filepath);
    	echo "<br>";

      //get file into temp folder - make filename very unique.
      $tempFile = DIR_DATA_NON_FTP_FROM . 'ftp/temp/' .  uniqid($this->tempPrefix) . '_' . basename($filepath);
      
      $serverFile = $locationArr['server_file_path'] . basename($filepath);
      $getResult = ftp_get($this->ftpConn, $tempFile, $serverFile, $this->translateMode($locationArr['ftp_type']), 0);

      if($getResult===true){


        //size used for ftp log.
        $fileSize = filesize($tempFile);

        $fh = fopen($tempFile, "r"); // open local temp file

        $completeFile = true;

        //complete file?
        if(trim($locationArr['file_end_delimiter'])!=""){  //skip EOF if blank trailer

        	$pos = -1;
        	$line = '';
        	$c = '';
        	do {
        	    fseek($fh, $pos--, SEEK_END);
        	    $c = fgetc($fh);
        	    if ($c!="\n") $line = $c . $line;
        	} while ((($c != "\n") || ($line=="")) && (ftell($fh)>1));

          if (substr($line,0,strlen($locationArr['file_end_delimiter']))!=$locationArr['file_end_delimiter']) {
        	  $completeFile = false;  //SPS
        	}

        }


        // start :  temporarily do this only for ITD (check start of file) as they are palookas
        if (preg_match("/ITD.ACT.*ORDERS.xml/i",$filepath)) {
          rewind($fh); // probably not needed
          $leadingStr = fgets($fh,6); // (5chars=6-1)

          if ($leadingStr!="<?xml") {
            BroadcastingUtils::sendAlertEmail('FTP FETCH notification for MARK', 'Error occured running the Import FTP Script for special ITD Check - follow up !' . "\n" .'Description: ' . $cResult->description, 'N', false);
            unlink($tempFile);  //remove temp file.
            $completeFile = false;
          }
        }
        // end : ITD temp check


        fclose($fh);
        
                  	echo "<br>";
          	echo "<pre>";
          	print_r($locationArr);
          	echo "HHH";


        if($completeFile==true){

          //backup file first before moving file - if backup fails and local file is moved possible multiple files received.
          $bkupResult = false;
          if($locationArr['server_file_backup_flag']=="Y"){
            $bkupResult = @ftp_rename($this->ftpConn,  $serverFile, $backupPath . basename($filepath).'_'.CommonUtils::getGMTimeCompressed(0));  //move ftp file to backup folder.
          } else {
            $bkupResult = ftp_delete($this->ftpConn,  $serverFile);  //if no backup we delete ftp file.
          }

          if($bkupResult==true){

            //move within local area.
            $toPath = constant($locationArr['root_dir_constant']) . $locationArr['local_file_path'] . $locationArr['prepend_local_filename'] . basename($filepath);    //use previous filename not temp.
            
            echo $tempFile;
            echo "<br>";
            echo $toPath;
            echo "<br>";
            
            $rename = rename($tempFile, $toPath);
            echo '<i>Received:</i> ' .  $serverFile . ' <i>---></i> ' . $toPath . ' (' . $fileSize . ' bytes)<BR>';

            //add to log arrays.
            $this->fileCountArr[$locationArr['uid']] = (isset($this->fileCountArr[$locationArr['uid']])) ? ($this->fileCountArr[$locationArr['uid']]+1) : ($locationArr['file_counter']+1);
            $this->fileLogArr[] = array(
                                        'server_filepath' =>  $serverFile,
                                        'filesize_bytes' => $fileSize,
                                        'local_filepath' => $toPath,  //remember we might prepend a suffix on the file here compared to the real name.
                                        'ftp_fetch_location_uid' => $locationArr['uid'] );

          } else {
            //moving or deleting of ftp file failed... remove local file and fail.
            echo 'Failed to Move/Delete FTP File: ' .  $serverFile . ' to ' . $backupPath . '<BR>';
            unlink($tempFile);  //remove temp file.
          }
        } else {
          echo 'Rejected file for: ' .  $serverFile . ' - End of File Delimiter not found "' . $locationArr['file_end_delimiter'] . '"<BR>';
          unlink($tempFile);  //remove temp file.
        }
      } else {
        echo "FTP Download Failed for: ".  $serverFile . '<BR>';
      }

      //no real return - nothing to handle.

    }


    private function createFTPBackup($folder){

    	$bkupFolder = $folder."bkup/";
    	$orgFolder = ftp_pwd($this->ftpConn); //current folder - return to later.

    	@ftp_chdir($this->ftpConn, '/');
    	@ftp_chmod($this->ftpConn, 0777, $bkupFolder);
    	$bkfolderExists = @ftp_chdir($this->ftpConn, $bkupFolder);
    	if (!$bkfolderExists){
    		$makeDir=@ftp_mkdir($this->ftpConn, $bkupFolder);
    		if ($makeDir===false) return false;
    	}

    	$bkupFolder.=date("Y")."/";
    	@ftp_chmod($this->ftpConn, 0777, $bkupFolder);
    	$bkfolderExists = @ftp_chdir($this->ftpConn, $bkupFolder);
    	if (!$bkfolderExists){
    		$makeDir=@ftp_mkdir($this->ftpConn,$bkupFolder);
    		if ($makeDir===false) return false;
    	}

    	$bkupFolder.=date("m")."/";
    	@ftp_chmod($this->ftpConn, 0777, $bkupFolder);
    	$bkfolderExists = @ftp_chdir($this->ftpConn, $bkupFolder);
    	if (!$bkfolderExists){
    		$makeDir=@ftp_mkdir($this->ftpConn,$bkupFolder);
    		if ($makeDir===false) return false;
    	}

    	@ftp_chdir($this->ftpConn, $orgFolder);

    	return $bkupFolder;
    }


    //local files are stored BY DAY.
    private function createLocalBackup($folder){

      $bkupFolder = $folder."bkup/" . date("Y") . "/" . date("m") . "/" . date("d") . "/";
      if(!is_dir($bkupFolder)){
        mkdir($bkupFolder, 0777, TRUE);  //create recursive directory based on path.
      }
      return $bkupFolder;
    }


    private function translateMode($modeStr){
      return ($modeStr=="FTP_ASCII") ? FTP_ASCII : FTP_BINARY;
    }


    private function runFTPLog(){

      if(count($this->fileLogArr)>0){

        echo "----------------------------------------------------------------<BR>";

        $postFTPLog = new PostingFTPFileLogTO();
        $postFTPLog->DMLType = "INSERT";


        foreach($this->fileLogArr as $file){

          $postFTPLog->serverPath = str_replace(basename($file['server_filepath']), '', $file['server_filepath']);  //remove filename from path.
          $postFTPLog->serverFilename = basename($file['server_filepath']);
          $postFTPLog->localPath = str_replace(basename($file['local_filepath']), '', $file['local_filepath']);  //remove filename from path.
          $postFTPLog->localFilename = basename($file['local_filepath']);
          $postFTPLog->filesizeBytes = $file['filesize_bytes'];
          $postFTPLog->ftpFetchLocationUid = $file['ftp_fetch_location_uid'];

          $logResult = $this->postMiscDAO->postFTPFileLog($postFTPLog);
          if($logResult->type!=FLAG_ERRORTO_SUCCESS){
            break;
          }

        }

        if($logResult->type==FLAG_ERRORTO_SUCCESS){
          $this->dbConn->dbinsQuery("commit;");
          echo '*** FTP File Log Updated!<BR>';
        } else {
          $this->dbConn->dbinsQuery("rollback;");
          echo 'Failed to Update FTP File Log: ' . $logResult->description;
        }
      }

    }


    //update location counters
    private function runFTPLocationsUpdate(){

      if(count($this->fileCountArr)>0){

        echo "----------------------------------------------------------------<BR>";

        foreach($this->fileCountArr as $uid => $count){
          $cResult = $this->postMiscDAO->postFTPLocationsCounters($uid, $count);
          if($cResult->type!=FLAG_ERRORTO_SUCCESS){
            break;
          }
        }

        if($cResult->type==FLAG_ERRORTO_SUCCESS){
          $this->dbConn->dbinsQuery("commit;");
          echo '*** Updated FTP Location Counters<BR>';
        } else {
          $this->dbConn->dbinsQuery("rollback;");
          echo 'Failed to Update FTP Location Counters: ' . $cResult->description;
        }
      }

    }



}

