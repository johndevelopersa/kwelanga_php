<?php


include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');
include_once($ROOT.$PHPFOLDER."libs/GUICommonUtils.php");



if (!isset($_SESSION)) session_start() ;
$principalId = $_SESSION['principal_id'] ;
$userId = $_SESSION["user_id"];


$postTYPE = (isset($_POST['TYPE'])) ? $_POST['TYPE'] : false;



if($postTYPE==false){

  echo '<H2>Error - no type supplied!</H2>';

} else {

  if($postTYPE == 'PRODUCT'){

    //UPLOAD FILE
    if(isset($_POST['upload'])){

      echo '<script type="text/javascript" language="javascript" src="'. $DHTMLROOT.$PHPFOLDER .'js/jquery.js"></script>';
      $msg = '';
      $callBack = '';
      if (!isset($_FILES['file'])) {
        $msg = 'ERROR - No file was uploaded';
      } else {
          // no error
          if ($_FILES['file']['error'] == UPLOAD_ERR_OK){

            $fileType = $_FILES["file"]["type"];
            $fileName = $_FILES["file"]["name"];
            if ($fileType == 'image/x-png' || $fileType == 'image/png' || $fileType == 'image/gif' || $fileType == 'image/jpeg' || $fileType == 'image/pjpeg' || $fileType == 'image/jpg') {

              //move uploaded file to temp filename.
              $copyTo = $ROOT.$PHPFOLDER.'uploads/products/'.$principalId .'_-TEMP-' . uniqid().'_'.$fileName;
              $resizeTo = $ROOT.$PHPFOLDER.'uploads/products/'.$principalId .'_-TEMP-' . uniqid().'.jpg';
              move_uploaded_file($_FILES['file']['tmp_name'], $copyTo);

              //resize and convert png and gifs
              //COVNERT
              if ($fileType == "image/jpg" || $fileType == "image/jpeg" || $fileType == "image/pjpeg") {
                $vsrc = imagecreatefromjpeg($copyTo);
              } else if ($fileType == "image/png" || $fileType == "image/x-png") {
                $vsrc = imagecreatefrompng($copyTo);
              } else {
                $vsrc = imagecreatefromgif($copyTo);
              }

              list($vwidth, $vheight) = getimagesize($copyTo);

              //RESIZE
              $vnewwidth = 350;
              $vnewheight = ($vheight / $vwidth) * $vnewwidth;
              $vtmp = imagecreatetruecolor(350, 350);

              $backgroundColor = imagecolorallocate($vtmp, 255, 255, 255);
              imagefill($vtmp, 0, 0, $backgroundColor);

              #create image based on the scource file and adjust to new size
              imagecopyresampled($vtmp, $vsrc, 0, 0, 0, 0, $vnewwidth, $vnewheight, $vwidth, $vheight);

              #path to temp file
              $vImgTemp = $resizeTo;

              #create temp image file.
              $d = imagejpeg($vtmp, $vImgTemp, 70);

              $size = filesize($vImgTemp);

              #read temp saved file
              $vfp = fopen($vImgTemp, 'r');
              $vdata = fread($vfp, $size) or die("Error: cannot read file");
              fclose($vfp);

              imagedestroy($vsrc); #delete image resource
              imagedestroy($vtmp); #delete tmp
              unlink($copyTo); #delete temp image file

              // main action -- move uploaded file to $upload_dir
              $msg = "Successfully Uploaded File - " . $_FILES['file']['name']. ' (' . $size .' bytes)';
              $callBack = 'parent.$("#content").contents().find("#productPhoto").attr("src","'.$resizeTo.'");
                           parent.$("#content").contents().find("#productPhotoLink").attr("href","javascript:;");
                           parent.$("#content").contents().find("#photomsg").html("You need to save this product for the photo to take effect.");
                           parent.popBoxClose();';

            } else {
              $msg = 'ERROR - Only JPEG, JPG, PNG or GIF file types are allowed! (' . $fileType . ')';
            }

          } else {
            $msg = 'ERROR : '. GUICommonUtils::translateUploadError($_FILES['file']['error']);
          }
      }
      echo '<script>alert("'.$msg.'");'.$callBack.'</script>';

    } else {

      //display form
?>
      <H2>Select a image:</H2>
      Only JPEG, JPG, PNG or GIF file types are allowed.<br><br>
      <div style="border:1px solid #ccc;background:#fff;padding:15px 0px">
      <iframe name="upload_iframe"style="width: 40px; height: 10px; display: none;border:0px;"></iframe>
      <form target="upload_iframe" action="<?php echo $_SERVER['PHP_SELF'] ?>"  method="post" enctype="multipart/form-data">
      <input type="hidden" name="TYPE" value="<?php echo $postTYPE ?>">
      <input type="hidden" value="1" name="upload">
      <input type="file" name="file" id="file">
      <input type="submit" value="Upload" name="submit" class="submit">
      </form>
      <a href="upload_iframe"></a>
      </div>

<?php
    }

  } else if($postTYPE == 'RECONFILE'){

    //UPLOAD FILE
    if(isset($_POST['upload'])){

      echo '<script type="text/javascript" language="javascript" src="'. $DHTMLROOT.$PHPFOLDER .'js/jquery.js"></script>';
      $msg = '';
      $callBack = '';
      if (!isset($_FILES['file'])) {
        $msg = 'ERROR - No file was uploaded';
      } else {
          // no error
          if ($_FILES['file']['error'] == UPLOAD_ERR_OK){

            $fileType = $_FILES["file"]["type"];
            $fileName = $_FILES["file"]["name"];
            $fileNdx = $_POST["FILENDX"];
//            if ($fileType == 'application/vnd.ms-excel') {

              //move uploaded file to temp filename.
              // Recon expects two files to compare - index 1 and 2
              $copyTo = $ROOT.$PHPFOLDER."uploads/recon/p{$principalId}_u{$userId}_recon{$fileNdx}.csv";
              move_uploaded_file($_FILES['file']['tmp_name'], $copyTo);
              $size=filesize($copyTo);

              // main action -- move uploaded file to $upload_dir
              $msg = "Successfully Uploaded File - " . $_FILES['file']['name']. ' (' . $size .' bytes)';
              $callBack = 'parent.content.refreshMe();
                           parent.popBoxClose();';

//            } else {
//              $msg = 'ERROR - Only CSV file types are allowed! (' . $fileType . ')';
//            }

          } else {
            $msg = 'ERROR : '. GUICommonUtils::translateUploadError($_FILES['file']['error']);
          }
      }
      echo '<script>alert("'.$msg.'");'.$callBack.'</script>';

    } else {

      //display form
?>
      <H2>Select a CSV File:</H2>
      Only comma separated text file types are allowed.<br><br>
      <div style="border:1px solid #ccc;background:#fff;padding:15px 0px">
      <iframe name="upload_iframe"style="width: 40px; height: 10px; display: none;border:0px;"></iframe>
      <form target="upload_iframe" action="<?php echo $_SERVER['PHP_SELF'] ?>"  method="post" enctype="multipart/form-data">
      <input type="hidden" name="TYPE" value="<?php echo $postTYPE ?>">
      <input type="hidden" value="1" name="upload">
      <input type="hidden" value="<?php echo $_POST["FILENDX"]?>" name="FILENDX">
      <input type="file" name="file" id="file">
      <input type="submit" value="Upload" name="submit" class="submit">
      </form>
      <a href="upload_iframe"></a>
      </div>

<?php
    }

  } else {
    echo '<H2>Error - unknown type: '.$postTYPE.'!</H2>';
  }

}




?>
