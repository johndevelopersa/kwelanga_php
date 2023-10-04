<?php 
// https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/mobile_dev/dispatch_control/dispatch_control.php
   include ('ROOT.php'); include ($ROOT.'PHPINI.php');

echo "eeee"
;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Kwelanga Online Solutions</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
  <link rel="stylesheet" type="text/css" href='css/kos_standard_mobi_screen.css'>
  
  
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
</head>
<body class="body">
    <form action="action_page.php" method="post" target="_self">
       <div class="container">
           <div class="table-responsive">       
              <table >
                 <tr class="bg" >
                     <td class="head1" colspan="4" ><img src="https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/images/logos/kwelanga2021v8.jpg" style="width:100%; height:40%; float:left;" ></td>
                 </tr>
                 <tr class="bg">
                     <td class="head1" width="25%"; >&nbsp;</td>
                     <td class="head1" width="25%"; >&nbsp;</td>
                     <td class="head1" width="25%"; >&nbsp;</td>
                     <td class="head1" width="25%"; >&nbsp;</td>
                 </tr>
                 <tr class="bg" >
                     <td class="det1" colspan="4">Welcome to our Mobile World</td>
                 </tr>                
                 <tr class="bg" >
                     <td class="det2" colspan="2">Log in to access the transaction menu</td>
                     <td class="det1" colspan="2"><div id="wrapper">
                                                     <input type="text" name="tusername" id="tusername" placeholder="username" />
                                                     <input type="password" name="tpassword" id="tpassword" placeholder="password" onKeyPress='return submitenter(event);' />
                                                     <center><a href="javascript:submitenter();" class="loginButton">Login</a></center>
                                                      </div>
                     </td>
                 </tr>                
              </table>
           </div>
       </div>	  	
    </form>
</body>
</html>

<SCRIPT type="text/javascript" language="javascript" >
<?php

        $js="function submitenter(e)	{

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