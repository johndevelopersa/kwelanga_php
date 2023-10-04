<?php


include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');


//------------------------------------------------
//
//  FTP CLASS
//
//------------------------------------------------
//
//  CONNECTION
//  LOGIN
//  FILE UPLOAD
//  FILE INTEGRITY CHECK (SHA1)
//  FILE READ FUNC
//
//------------------------------------------------


class FTP {



  public function sendFile($host, $user, $pass, $folder, $file, $to_file_name = false, $port = 21, $mode = 1, $integrity_check = false){

    global $ROOT;

    $errorTO = new ErrorTO();
    $errorTO->type = FLAG_ERRORTO_ERROR;
    $errorTO->description = 'FTP Generic Failure!';  //PRESET FAILURE - ONE POINT OF SUCCESS

    $ftpConn = @ftp_connect($host, $port);  //CONNECT

    $errorTO->identifier = $ftpConn;


    if(!$ftpConn){

      $errorTO->description = 'FTP Connection Failure : "'.$host.'"';
	  return $errorTO;

    } else {

      $login = @ftp_login($ftpConn, $user, $pass);  //LOGIN

      if(!$login){

        $errorTO->description = 'FTP Login Failure : "'.$user .' - '. $pass.'"';  //return at bottom level for ftp close to work.

      } else {

        if($mode == 1){  //PASSIVE
          @ftp_pasv($ftpConn, true);  //failure for passive|active occurs when trying to upload etc.
        }

        if(!empty($folder)){  //CHANGE DIR
          $cd = @ftp_chdir($ftpConn, $folder);
        } else {
          $cd = true;
        }

        if(!$cd){  //only check here for no folder change values
          $errorTO->description = 'FTP Folder Change Failure : "'.$folder.'"';
        } else {

          //CHECK IF LOCAL FILE EXISTS.
          $file = $ROOT.$file;
          if(!is_file($file)){
            $errorTO->description = 'FTP File Does not exist: "'.$file.'"';
          } else {

            if(!$to_file_name){$to_file_name = basename($file);}

            //UPLOAD FILE
            $upload = @ftp_put($ftpConn, $to_file_name, $file, FTP_ASCII);

            if(!$upload){
              $errorTO->description = 'FTP File Upload Failed, try again or select Passive Mode';
            } else {

              /**** IF "ON" INCREASES OVERHEAD ****/
              $integrityOk = true;  //preset
              if($integrity_check){
                $local_sha1 = sha1_file($file);  //local sha1 hash
                $ftp_file_contents = $this->ftp_get_contents($ftpConn, $to_file_name);  //ftp hash - stream from ftp to temp file basically download.
                $ftp_sha1 = sha1($ftp_file_contents);
                $integrityOk = ($ftp_sha1 == $local_sha1) ? (true) : (false);  //re-set if integrity used.
              }

              if(!$integrityOk){
                $errorTO->description = 'FTP File Integrity Check Failure, please try again or check your server settings.';
              } else {

                //ONLY - SUCCESS POINT!!!
                $errorTO->type = FLAG_ERRORTO_SUCCESS;
              }
            }
          }
        }
      }

      @ftp_close($ftpConn);  //CLOSE CATCHES ALL - AFTER CONN : OK
      return $errorTO;  //RETURN ALL
    }

    return $errorTO;
  }



  private function ftp_get_contents($ftp_conn_id, $ftp_file){

    $tempHandle = fopen('php://temp', 'r+');  //Create temp handler:
    if (@ftp_fget($ftp_conn_id, $tempHandle, $ftp_file, FTP_ASCII, 0)) {
        rewind($tempHandle);
        return stream_get_contents($tempHandle);
    } else {
        return false;
    }
  }

}

?>