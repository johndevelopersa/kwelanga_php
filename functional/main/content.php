<?php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'functional/main/access_control.php');
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'DAO/AdministrationDAO.php');
include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
CommonUtils::getSystemConventions();

if (!isset($_SESSION))
session_start();
$userId = $_SESSION['user_id'];
$principalId = $_SESSION['principal_id'];
$systemId = $_SESSION['system_id'];
$systemName = $_SESSION['system_name'];

$dbConn = new dbConnect();  //Create new database object
$dbConn->dbConnection();  //Database connection method

$administrationDAO = new AdministrationDAO($dbConn);
$uPref = $administrationDAO->getUserPreferences($userId);
$displayLog = (count($uPref)==0 || (isset($uPref[0]['display_access_log']) && $uPref[0]['display_access_log'] == 'Y')) ? true : false;

if($displayLog){

  $uALogArr = $administrationDAO->getUsersAccessLogForDays($userId, $days = 30);

  $userLogOut = '<i>no activity for '.$days.' days</i>';  //present empty...
  if(count($uALogArr)>0){
    $userLogOut = '';
    $no = 0;
    foreach($uALogArr as $logItem){
       if($systemId == $logItem['system'])  {
         $no++;
         $userLogOut .= '<div id="accessItem" style=";'.((($no != 5) && ($no != count($uALogArr)))?('border-bottom:1px dashed #ccc'):('')).'">';
         $userLogOut .= $logItem['login_date_time'] .' <small>GMT + 0:00</small><br>';
         $userLogOut .= '<span>'. $logItem['principal_name'] . '</span></div>';
       }   
     if($no == 5)break;
    }
  }
}

// authorisations
$rsAuth=$administrationDAO->getVATAuthorisations($principalId);

$fldPref = $administrationDAO->getAllFieldPreferences($principalId, $systemId, 'DASHBOARD');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<html>
<head>
  <link href="<?php echo $DHTMLROOT.$PHPFOLDER.'css/css.php?SYSID='.$systemId.'&SYSNAME='.$systemName ?>" rel="stylesheet" type="text/css" />
  <script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/jquery.js"></script>    
  <meta name="SKYPE_TOOLBAR" content="SKYPE_TOOLBAR_PARSER_COMPATIBLE">
</head>
<body>

<div id="dashPage" >
  <?php
    if(in_array($systemId,array(SYS_KWELANGA)))   {
  ?>

  <BR>
  <div class="selectMenuCont rdCrn3">
    <div align="left">
      <h1>Welcome to...</h1>
      <h3><?php echo SNC::title; ?></h3>
      <strong><i>Development System</i></strong><br>
      <strong><i>Please select from menu to continue...</i></strong>
    </div>
  </div>

  <?php } ?>

  <div class="sectionWrapper">

  <table class='tableReset' ><tr>
  <td style='vertical-align:top;'>

        <?php if($displayLog){ ?>
  	<div id="accessWrapper" <?php echo GUICommonUtils::showHideField($fldPref,'ACCESSLOG',$class,false) ?>>
  		<div class="header"><strong>Access Log</strong></div>
  		<div class="content accessCon"><?php echo $userLogOut ?></div>
  	</div>
        <?php } ?>

   </td>

   <td style='vertical-align:top;'>

   <div id="centerWrapper" <?php echo GUICommonUtils::showHideField($fldPref,'VATAUTH',$class,false) ?> >
      <div class="header"><strong>VAT Authorisations</strong></div>
      <div class="content accessCon" style="width:300px;">
      <?php
          if ($rsAuth["storeCnt"]==0) {
            echo "<span style='color:".COLOR_UNOBTRUSIVE_INFO.";'>Stores : ".$rsAuth["storeCnt"]."</span><br><br>";
          } else {
            echo "<div style='font-weight:bold;font-size:20px;color:".COLOR_URGENT_TEXT.";margin-bottom:10px;'>Stores : ".$rsAuth["storeCnt"]."</div>
                  <a href='javascript:;' onClick='$(\"#sinfo1\").slideToggle();' style='color:#666'><b>What does this mean?</b></a>
                  <div id=\"sinfo1\" style='display:none;border-top:1px solid #ccc;'>
                  It means that one or more of your stores are set to exclude VAT whenever an order is created. For your protection, you will need
                  to authorise this setting before any orders can be created for that store. If you do not authorise the store's exlusive vat setting
                  then no orders can be processed to that store.</div>
                  <br><br>
                  <a href='javascript:;' onClick='$(\"#sinfo2\").slideToggle();' style='color:#666'><b>How do I authorise a store?</b></a>
                  <div id=\"sinfo2\" style='display:none;border-top:1px solid #ccc;'>
                  You can run a store report to see which of your stores have VAT excluded. You will see atleast the first 3 listed below to start you.
                  <br><br>";
            foreach ($rsAuth["storeList"] as $row) {
              echo $row["uid"]."-".$row["deliver_name"]."<br>";
            }
            echo "<br>Then simply query that store by clicking the modify store menu item, select your store and tick the authorisation checkbox under the VAT setting.</div>
                   <br><br><br>";
          }
          if ($rsAuth["prdCnt"]==0) {
            echo "<span style='color:".COLOR_UNOBTRUSIVE_INFO.";'>Products : ".$rsAuth["prdCnt"]."</span><br>";
          } else {
            echo "<div style='font-weight:bold;font-size:20px;margin-bottom:10px;color:".COLOR_URGENT_TEXT.";'>Products : ".$rsAuth["prdCnt"]."</div>
                  <a href='javascript:;' onClick='$(\"#pinfo1\").slideToggle();' style='color:#666'><b>What does this mean?</b></a>
                  <div id=\"pinfo1\" style='display:none;border-top:1px solid #ccc;'>
                  It means that one or more of your products are set to exclude VAT whenever an order is created. For your protection, you will need
                  to authorise this setting before any orders can be created for that product. If you do not authorise the product's zero vat rate setting
                  then no orders can be processed for that product.
                  </div>
                  <br><br>
                  <a href='javascript:;' onClick='$(\"#pinfo2\").slideToggle();' style='color:#666'><b>How do I authorise a product?</b></a>
                  <div id=\"pinfo2\" style='display:none;border-top:1px solid #ccc;'>
                  You can run a product report to see which of your products have zero VAT rate. You will see atleast the first 3 listed below to start you.
                  <br><br>";
            foreach ($rsAuth["prdList"] as $row) {
              echo $row["product_code"]." - ".$row["product_description"]."<br>";
            }
            echo "<br>Then simply query that product by clicking the modify product menu item, select your product and tick the authorisation checkbox under the VAT rate setting.</div>";
          }
      ?>
      </div>
   </div>

   </td>
   </tr>
   </table>

  	<!--
  	<div id="updatesWrapper" >
  		<div class="header"><strong>Updates &amp; News</strong></div>
  		<div class="content" align="center">
  			<div style="padding:18px 30px;display:block;font-size:24px;color:#ccc;"><strong>COMING SOON</strong></div>
  		</div>
  	</div>
	-->
  </div>
</div>

<div id="dashSidebar" >

  <div class="header"><strong>Help Desk</strong></div>
  <span style="font-size:12px;text-align:center;">
    <div style="text-align:left;padding-top:10px;line-height:14px;margin-bottom:10px;">
    	<?php echo SNC::title; ?> offers a <strong>help desk</strong> where you may request additional assistance.
    </div>
    <div align="left" class="rdCrn5" style="color:#b00d02;display:block;padding:15px 14px 18px 14px;background:#fff url('<?php echo $DHTMLROOT.$PHPFOLDER ?>images/support_rep90.png') right center no-repeat;border:1px solid darkgray;">
    	<div style="font-size:22px;"><strong>010 023 3036</strong></div>
    	<!--<div style="font-size:22px;margin-top:2px;"><strong>084 516 9808</strong></div>/-->
    	<div style="font-size:13px;margin-top:4px;"><a href="mailto:<?php echo SNC::support_email_addr; ?>" style="color:#b00d02;"><strong><?php echo SNC::support_email_addr; ?></strong></a></div>
	</div>
  </span>

  <div align="center"><div class="divider" ></div></div>

  <br>
  <div class="header"><strong>Hosting By SA Software</strong></div>
  <br>
  <span style="font-size:10px;text-align:center;">
     <div class="rdCrn5" style="float:left;display:block;background:#fff;border:1px solid darkgray;padding:3px 6px;">
      <img src="<?php echo $DHTMLROOT.$PHPFOLDER ?>images/sas_logo.png" width="120" height="80" alt="" >
      </div>
 	<div style="clear:both;"></div>
  </span>

</div>

</body>
</html>