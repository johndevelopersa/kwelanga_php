<?php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER."DAO/StoreDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/MiscellaneousDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/CommonDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/StoreDAO.php");


if (!isset($_SESSION)) session_start() ;

$userId = $_SESSION['user_id'] ;
$principalId = $_SESSION['principal_id'] ;
$principalAliasId = (($_SESSION['principal_alias_id']=="")?$principalId:$_SESSION['principal_alias_id']);
$principalName = $_SESSION['principal_name'] ;


$dbConn = new dbConnect();
$dbConn->dbConnection();
$storeDAO = new StoreDAO($dbConn);
$miscDAO = new MiscellaneousDAO($dbConn);


if (isset($_GET['PRINCIPALSTOREUID'])) $postPRINCIPALSTOREUID=mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_GET['PRINCIPALSTOREUID']));
else if (isset($_POST['PRINCIPALSTOREUID'])) $postPRINCIPALSTOREUID=mysqli_real_escape_string($dbConn->connection, htmlspecialchars($_POST['PRINCIPALSTOREUID']));
else $postPRINCIPALSTOREUID="";

//USE GLN TO GET STORE UID.
$postSTOREGLN = (isset($_GET['STOREGLN'])) ? ($_GET['STOREGLN']) : (false);
if($postSTOREGLN !== false){
  $mfSUid = $storeDAO->getPrincipalStoreByGLN($principalAliasId,$postSTOREGLN);
  if (isset($mfSUid[0]['uid'])) {
    $postPRINCIPALSTOREUID = $mfSUid[0]['uid'];
  }
}

// this also doubles as the security check because this sql joins on user_principal_store
$mfPS = $storeDAO->getUserPrincipalStoreItem($userId,$postPRINCIPALSTOREUID);

if (sizeof($mfPS)==0) {
	echo "You do not have access to this store, or store does not exist, or chain master is registered to a different principal than store chain.";
	return;
}

?>
<HTML>
<HEAD>
</HEAD>
<BODY style='width:300px; font-family:Verdana,Arial,Helvetica,sans-serif;'>

<?php

if(isset($_GET['MODIFY']) && $_GET['MODIFY']==1){
  echo '<div><input class="submit" type="submit" value="Edit Store" onClick="location.href=\'../../home.php?m_id=15&tab_id=48&param=PSMUID:'.$postPRINCIPALSTOREUID.'\'"></div><br>';
}

?>

<TABLE style='border-style:double;'>
<TR>
<TD colspan="2" style='text-align:center; background-color:gray; color:white; font-weight:bold; font-size:0.8em;'><?php echo $principalName; ?></TD>
</TR>
<TR>
<TD colspan="2" style='text-align:center; background-color:gray; color:white; font-weight:bold; font-size:0.8em;'><?php echo $mfPS[0]['deliver_name']; ?></TD>
</TR>
<TR>
<TD colspan="2">&nbsp;</TD>
</TR>

<TR>
	<TD style='font-weight:bold; font-size:0.6em;'>Depot Name:</TD>
	<TD style='font-weight:normal; font-size:0.6em;'><?php echo $mfPS[0]['depot_name']; ?></TD>
</TR>
<TR>
	<TD style='font-weight:bold; font-size:0.6em;'>Chain Name:</TD>
	<TD style='font-weight:normal; font-size:0.6em;'><?php echo $mfPS[0]['chain_name']; ?></TD>
</TR>

<TR>
<TD colspan="2">
  <TABLE><TR><TD>
	<TABLE style='border-style:solid; border-width:1px;'>
	<TR>
		<TD colspan="2" style='font-weight:bold; font-size:0.6em;'>Delivery Address:</TD>
	</TR>
	<TR>
		<TD style='font-weight:bold; font-size:0.6em;'>&nbsp;</TD>
		<TD style='font-weight:normal; font-size:0.6em;'><?php echo $mfPS[0]['deliver_name']; ?></TD>
	</TR>
	<TR>
		<TD style='font-weight:bold; font-size:0.6em;'>&nbsp;</TD>
		<TD style='font-weight:normal; font-size:0.6em;'><?php echo $mfPS[0]['deliver_add1']; ?></TD>
	</TR>
	<TR>
		<TD style='font-weight:bold; font-size:0.6em;'>&nbsp;</TD>
		<TD style='font-weight:normal; font-size:0.6em;'><?php echo $mfPS[0]['deliver_add2']; ?></TD>
	</TR>
	<TR>
		<TD style='font-weight:bold; font-size:0.6em;'>&nbsp;</TD>
		<TD style='font-weight:normal; font-size:0.6em;'><?php echo $mfPS[0]['deliver_add3']; ?></TD>
	</TR>
	</TABLE>

	</TD>
	<TD>&nbsp;</TD>
	<TD>

	<TABLE style='border-style:solid; border-width:1px;'>
	<TR>
	<TD colspan="2" style='font-weight:bold; font-size:0.6em;'>Billing Address:</TD>
	</TR>
	<TR>
		<TD style='font-weight:bold; font-size:0.6em;'>&nbsp;</TD>
		<TD style='font-weight:normal; font-size:0.6em;'><?php echo $mfPS[0]['bill_name']; ?></TD>
	</TR>
	<TR>
		<TD style='font-weight:bold; font-size:0.6em;'>&nbsp;</TD>
		<TD style='font-weight:normal; font-size:0.6em;'><?php echo $mfPS[0]['bill_add1']; ?></TD>
	</TR>
	<TR>
		<TD style='font-weight:bold; font-size:0.6em;'>&nbsp;</TD>
		<TD style='font-weight:normal; font-size:0.6em;'><?php echo $mfPS[0]['bill_add2']; ?></TD>
	</TR>
	<TR>
		<TD style='font-weight:bold; font-size:0.6em;'>&nbsp;</TD>
		<TD style='font-weight:normal; font-size:0.6em;'><?php echo $mfPS[0]['bill_add3']; ?></TD>
	</TR>
	</TABLE>
  </TD></TR></TABLE>
</TD>
</TR>

<TR>
	<TD style='font-weight:bold; font-size:0.6em;'>Contact No 1:</TD>
	<TD style='font-weight:normal; font-size:0.6em;'><?php echo $mfPS[0]['tel_no1']; ?></TD>
</TR>
<TR>
	<TD style='font-weight:bold; font-size:0.6em;'>Contact No 2:</TD>
	<TD style='font-weight:normal; font-size:0.6em;'><?php echo $mfPS[0]['tel_no2']; ?></TD>
</TR>
<TR>
	<TD style='font-weight:bold; font-size:0.6em;'>Email Address:</TD>
	<TD style='font-weight:normal; font-size:0.6em;'><?php echo $mfPS[0]['email_add']; ?></TD>
</TR>

<TR>
	<TD style='font-weight:bold; font-size:0.6em;'>EAN:</TD>
	<TD style='font-weight:normal; font-size:0.6em;'><?php echo $mfPS[0]['ean_code']; ?></TD>
</TR>
<TR>
	<TD style='font-weight:bold; font-size:0.6em;'>VAT Number:</TD>
	<TD style='font-weight:normal; font-size:0.6em;'><?php echo $mfPS[0]['vat_number']; ?></TD>
</TR>
<TR>
	<TD style='font-weight:bold; font-size:0.6em;'>Vat:</TD>
	<TD style='font-weight:normal; font-size:0.6em;'><?php if ($mfPS[0]['no_vat']==1) echo "VAT Excluded"; else echo "VAT Included"; ?></TD>
</TR>
<TR>
	<TD style='font-weight:bold; font-size:0.6em;'>On Hold:</TD>
	<TD style='font-weight:normal; font-size:0.6em;'><?php if ($mfPS[0]['on_hold']==1) echo "On Hold"; else echo "not On Hold"; ?></TD>
</TR>

<?php
	$commonDAO = new CommonDAO($dbConn);
	$mfDays = $commonDAO->getDaysArray();
	if (isset($mfDays[$mfPS[0]['delivery_day_uid']])) $dayDesc = $mfDays[$mfPS[0]['delivery_day_uid']]['name'];
	else $dayDesc = "Error Retrieving Day Description"
?>
<TR>
	<TD style='font-weight:bold; font-size:0.6em;'>Delivery Day:</TD>
	<TD style='font-weight:normal; font-size:0.6em;'><?php echo $dayDesc; ?></TD>
</TR>

<?php
	$smpf=$miscDAO->getPrincipalSpecialFieldValues($principalAliasId,$postPRINCIPALSTOREUID,"S");
	if(sizeof($smpf)>0) {
?>
<!-- store specialist fields -->
<TR>
	<TD colspan="2">&nbsp;</TD>
</TR>

<TR>
	<TD colspan="2" style='font-weight:bold; font-size:0.6em;'>Store Specialist Fields:</TD>
</TR>
<TR>
	<TD colspan="2">
		<TABLE style='border-style:solid; border-width:1px;'>
			<?php
			  foreach ($smpf as $row) {
			?>
			<TR>
				<TD style='font-weight:bold; font-size:0.6em;'><?php echo $row['name']; ?>:</TD>
				<TD style='font-weight:normal; font-size:0.6em;'><?php echo $row['value']; ?></TD>
			</TR>
			<?php } ?>
		</TABLE>
	</TD>
</TR>

<?php } ?>

<TR>
	<TD colspan="2">&nbsp;</TD>
</TR>

<TR>
<TD colspan="2" style='text-align:center; color:grey; font-weight:normal; font-size:0.55em;'><script type="text/javascript">var d = new Date(); document.write("<b>" + d.getDate() + "/" + d.getMonth() + "/" + d.getFullYear() + "&nbsp;&nbsp;" + d.getHours() + ":" + d.getMinutes() + ":" + d.getSeconds() + "</b>");</script></TD>
</TR>
</TABLE>

</BODY>

</HTML>

<?php
$dbConn->dbClose();
?>