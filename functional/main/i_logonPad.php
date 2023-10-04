<?php
 include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
 include_once($ROOT.$PHPFOLDER.'properties/dbSettings.inc');
 include_once($ROOT.$PHPFOLDER.'libs/JavaScriptPacker.php');
 include_once($ROOT.$PHPFOLDER.'properties/Constants.php');

$getSTYLE = (isset($_GET["style"]))  ? strtolower($_GET["style"]) : "kwelanga";
$getSYSTEM = (isset($_GET["system"]))  ? $_GET["system"] : SYS_KWELANGA ;
$getELOGIN = (isset($_GET["elogin"]) && $_GET["elogin"] == 1)  ? true : false;
$clean = (isset($cleanLogin))?true:false;

if (!isset($_SESSION)) session_start();

if (isset($_GET["ingen"])) {
	$_SESSION['ingen_g'] = $_GET["ingen"];
} else {
	$_SESSION['ingen_g'] = '';
}

$css = ("<link href=\"{$DHTMLROOT}{$PHPFOLDER}css/{$getSTYLE}_logon_style.css\" rel=\"stylesheet\" type=\"text/css\" />");
$refArr = explode('/' ,(isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:''));
$frame = (isset($_GET['frame']) && isset($refArr[2]) && $refArr[2] != $_SERVER['SERVER_NAME'])?true:false;

 if(!$clean){
   echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
        <html xmlns="http://www.w3.org/1999/xhtml">
        <HEAD>';
   echo "{$css}";
   echo '<script type="text/javascript" language="javascript" src="'. $DHTMLROOT.$PHPFOLDER .'js/client-side-encryption-packed.js"></script>';
 } else if($frame){

      echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
        <html xmlns="http://www.w3.org/1999/xhtml">
        <HEAD>';
   echo "{$css}";
   echo '<script type="text/javascript" language="javascript" src="'. $DHTMLROOT.$PHPFOLDER .'js/client-side-encryption-packed.js"></script>';
 }

?>

<SCRIPT type="text/javascript" language="javascript" >
<?php

        $js="
                function submitenter(e)	{

                        var keycode=0;
                        if (window.event) keycode = window.event.keyCode;
                        else if (e) keycode = e.which;
                        if ((keycode==13) || (keycode==0)) {
                          document.logon.username.value=stringToHex(des(\"".ENCRYPT_JS_KEY."\", document.dummy.tusername.value, 1, 0));
                          document.logon.password.value=stringToHex(des(\"".ENCRYPT_JS_KEY."\", document.dummy.tpassword.value, 1, 0));
                          document.logon.submit();
                        } else return true;
                }


                ";
        $jsPacker = new JavaScriptPacker($js, 'Normal', true, false);
        $packed = $jsPacker->pack();
    echo $packed;
?>
</SCRIPT>

<?php
  if(!$clean){
    echo '</HEAD>
    <BODY>';
  }
?>
<?php

if (isset($_GET["wp"])) {?> 
<form id="logon" name="logon" action="<?php echo $ROOT.$PHPFOLDER ?>functional/principals/principal_select.php?wp=Y style=<?php echo $getSTYLE; ?>" method='post' target='_parent'> 
<?php }
else { ?> 
<form id="logon" name="logon" action="<?php echo $ROOT.$PHPFOLDER ?>functional/principals/principal_select.php?style=<?php echo $getSTYLE; ?>" method='post' target='_parent'>
<?php } ?>	
<input type='hidden' name='username' id='username' />
<input type='hidden' name='password' id='password' />
<input type='hidden' name='system' id='system' value='<?php echo $getSYSTEM ?>'/>
</form>

        <?php if(!$frame){ ?><div align="center" class="logoOuter"><div class="logo"></div></div> <?php } ?>
	<div class='logonPad'>
	<form id="dummy" name="dummy" action='' method='' target=''>

          <?php

          if($getELOGIN){
            echo '<div id="error">
                    <strong>LOGIN ERROR!</strong><br>
                    Login details are incorect or user could not be found on this system. Please try again!
                  </div><br>';
          }


          ?>


          <div id="wrapper">
          <input type="text" name="tusername" id="tusername" placeholder="username" />
          <input type="password" name="tpassword" id="tpassword" placeholder="password" onKeyPress='return submitenter(event);' />
          <br><br>
          <center><a href="javascript:submitenter();" class="loginButton">Login</a></center>
          </div>
    </form>
    </div>
    <?php if(!$frame){ ?>
      <a href="http://live.kwelangaonlinesolutions.co.za" target="_blank" id="poweredby"></a>
    <?php } ?>
<?php
  if(!$clean){
    echo '</BODY>
		</HTML>';
  }
?>
