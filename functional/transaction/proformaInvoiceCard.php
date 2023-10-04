<!DOCTYPE html>
<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER."DAO/TransactionDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/ProductDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/AdministrationDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/CommonDAO.php");
include_once($ROOT.$PHPFOLDER."libs/GUICommonUtils.php");
include_once($ROOT.$PHPFOLDER."properties/Constants.php");

if (!isset($_SESSION)) session_start() ;
$userId = $_SESSION['user_id'] ;
$principalId = $_SESSION['principal_id'] ;
$principalAliasId = (($_SESSION['principal_alias_id']=="")?$principalId:$_SESSION['principal_alias_id']);
$principalName = $_SESSION['principal_name'] ;
$principalAliasName = (($_SESSION['principal_alias_name']=="")?$principalName:$_SESSION['principal_alias_name']);

//Create new database object
$dbConn = new dbConnect(); $dbConn->dbConnection();

if (isset($_GET['DOCMASTID'])) $postORDERSEQ=mysql_real_escape_string(htmlspecialchars($_GET['DOCMASTID']));
else if (isset($_POST['DOCMASTID'])) $postORDERSEQ=mysql_real_escape_string(htmlspecialchars($_POST['DOCMASTID']));
else $postORDERSEQ="";

if (isset($_GET['ORDESEQID'])) $postORDERSEQ=$_GET['ORDESEQID'];

include_once($ROOT.$PHPFOLDER."DAO/TransactionDAO.php");
$transactionDAO = new TransactionDAO($dbConn);

$adminDAO = new AdministrationDAO($dbConn);
$hasRoleVP = $adminDAO->hasRole($userId, $principalAliasId,ROLE_VIEW_PRICE);

$dMUId = "";
$mfD = $transactionDAO->getDocumentUidByOrderSeq($postORDERSEQ, $principalId);

if(isset($mfD['uid'])) $dMUId = $mfD['uid'];
else {
  echo "You do not have access to this information, or quotation does not exist.";
  return;
}

// this also doubles as the security check because this sql joins on user_principal_depot
$mfT = $transactionDAO->getDocumentWithDetailItem($userId, $principalId, $dMUId);

if (sizeof($mfT)==0) {
  echo "You do not have access to this information, or order does not exist.";
  return;
} 

// echo "<pre>"; print_r($mfT[0]); echo "</pre>";

?>
<HTML>
  <TITLE>Document - View</TITLE>
<HEAD>
  <STYLE type="text/css">

    #wrapper{width:700px;text-align:left;}

    #toolbar {font-size:12px;background:#047;padding:8px 10px}
    #toolbar a img{margin:2px 5px 2px 0px;}
    #toolbar a:hover{background:aliceBlue}
    #toolbar a{margin-right:10px;float:left;background:#fff;text-align:center;display:block;border:1px solid #047;padding:0px 8px;line-height:36px;text-decoration:none;color:#666;font-weight:bold;}
    #block{background:#fff;padding:10px 5px;border:1px solid #ccc;}
    .dtitle{text-align:left;}
    /*h2{color:#000;font-size:15px;line-height:25px;letter-spacing:0.2em;margin:20px 0px 5px 0px;}*/

    /* print styles */
    @media print {
      #noprint {
          visibility:hidden;
          display:none;
      }
      #wrapper{
        border:0px;
      }
      #block{padding:10px 0px;border:0px;}
    }

    table {font-size:12px;}
    table.grid
    {
      border-collapse:collapse;
    }
    table.grid td, table.grid th
    {
    border:1px solid #aaa;
    }
    table.grid th {background:#efefef;}
    .bordUnderline{border-bottom:1px solid #333;height:30px;}

</STYLE>
<script type='text/javascript' language='javascript' src='<?php echo $ROOT.$PHPFOLDER ?>js/jquery.js'></script>


</HEAD>

<BODY style="font-family:Verdana,Arial,Helvetica,sans-serif;margin:0px;padding:0px;">
	
<!-- email -->
<div align="center" id="noprint" class="disableprint" >

<table id="wrapper" cellspacing="0" cellpadding="0">
  <tr>
     <td>
      <div id="noprint"><!-- HIDE THIS PRINT AREA : START /--->
        <div id="toolbar">
          <a href="javascript:window.print();"><img src="<?php echo $ROOT.$PHPFOLDER ?>images/print-icon.png" border="0" alt="" align="left" > PRINT</a>
          <a href="javascript: onclick='emailDoc();' "><img src="<?php echo $ROOT.$PHPFOLDER ?>images/email-icon.png" border="0" alt="" align="left" > Email</a>
          <div style="clear:both;"></div>
        </div>
      </div><!-- HIDE THIS PRINT AREA : END /--->
    </td>
  </tr>
</table>
</div>
<TABLE style='border-style:none; width:90%'>
<TR>
<TD colspan="3" style='text-align:left; background-color:white; color:Black; font-weight:bold; font-size:1.6em;'>Proforma Invoice</TD>
</TR><BR>
<TR>
</TR>
<TR>
<TD colspan="3" style='text-align:left; background-color:white; color:Black; font-weight:normal; font-size:1.0em;'><?php echo $mfT[0]['principal_name']; ?></TD>
</TR>
<TR>
<TD colspan="3" style='text-align:left; background-color:white; color:Black; font-weight:normal; font-size:1.0em;'><?php echo $mfT[0]['prin_add1'] ?></TD>
<TD colspan="3" style='text-align:right; background-color:white; color:Black;'><SPAN style='font-weight:bold; font-size:0.8em;'>Email Address:</SPAN>&nbsp;&nbsp;<SPAN style='font-weight:normal; font-size:1.0em;'><?php echo $mfT[0]['p_email'] ?></SPAN></TD>
<TR>
<TD colspan="3" style='text-align:left; background-color:white; color:Black; font-weight:normal; font-size:1.0em;'><?php echo $mfT[0]['prin_add2'] ?></TD>
<TD colspan="3" style='text-align:right; background-color:white; color:Black;'><SPAN style='font-weight:bold; font-size:0.8em;'>Office Tel:</SPAN>&nbsp;&nbsp;<SPAN style='font-weight:normal; font-size:1.0em;'><?php echo substr($mfT[0]['office_tel'],0,3).' '. substr($mfT[0]['office_tel'],3,3) .' '. substr($mfT[0]['office_tel'],6,4)?></SPAN></TD>
</TR>
<TR><BR>
</TR>


<!-- doc dates and ref -->
<TR>
  <TD colspan="3" style='text-align:left; background-color:white; color:Black;'><SPAN style='font-weight:bold; font-size:0.8em;'>Date :</SPAN><BR><SPAN style='font-weight:bold; font-size:1.0em;'><?php echo $mfT[0]['order_date']; ?></SPAN></TD>
  <TD colspan="3" style='text-align:right; background-color:white; color:Black;'><SPAN style='font-weight:bold; font-size:0.8em;'>Quote Number:&nbsp;</SPAN><SPAN style='font-weight:normal; font-size:1.0em;'><?php echo substr($mfT[0]['document_number'],3,6); ?></SPAN></TD>
</TR>
<TR>
  <TD colspan=3>&nbsp;</TD>
</TR>
<TR>
  <TD colspan="3" style='text-align:left; background-color:white; color:Black;'><SPAN style='font-weight:bold; font-size:0.8em;'>Customer</SPAN><BR><SPAN style='font-weight:normal; font-size:1.0em;'><?php echo $mfT[0]['store_name']."<br>".$mfT[0]['deliver_add1']."<br>".$mfT[0]['deliver_add2']."<br>".$mfT[0]['deliver_add3']; ?></SPAN></TD>
</TR>
<TR>
  <TD colspan=3>&nbsp;</TD>
</TR>
<TR>
  <TD colspan="3" style='text-align:left; background-color:white; color:Black;'><SPAN style='font-weight:bold; font-size:0.8em;'>Customer Reference:</SPAN><BR><SPAN style='font-weight:normal; font-size:1.0em;'><?php echo $mfT[0]['customer_order_number']; ?></SPAN></TD>
</TR>
<TR>
  <TD colspan=3>&nbsp;</TD>
</TR>
<!-- detail -->
<tr><td style='font-weight:bold; font-size:0.8em;' colspan=4>Invoice Details</td></tr>
</TABLE>
<STYLE>
th {
  padding-left:0.1cm; text-align:left; width:100%; border-collapse:collapse; border-left:solid; border-left-width:1px; border-left-color:black; border-top:solid; border-top-width:1px; border-top-color:black; border-bottom:solid; border-bottom-width:1px; border-bottom-color:black;
}
td.dC {
  padding-left:0.1cm; border-left:solid; border-left-width:1px; border-left-color:black; font-weight:normal; font-size:0.8em
}
</STYLE>
<TABLE style= 'border-collapse:collapse'>
<TR>
  <TH style='width:95px;'nowrap colspan=1 text-align:left;>Code&nbsp;</TH>
  <TH style='width:200px';nowrap colspan=1 >&nbsp;Description&nbsp;</TH>
  <TH style='width:60px';nowrap colspan=1 >&nbsp;Quantity&nbsp;</TH>
  <TH style='width:75px';nowrap colspan=1>&nbsp;&nbsp;Price&nbsp;</TH>
  <TH style='width:90px';border-right:solid; border-right-width:1px; border-right-color:black;' nowrap colspan=1>&nbsp;Exclusive Total&nbsp;</TH>
  <TH style='width:90px';border-right:solid; border-right-width:1px; border-right-color:black;' nowrap colspan=1>&nbsp;VAT&nbsp;</TH>
  <TH style='width:90px; border-right:solid; border-right-width:1px; border-right-color:black;' nowrap colspan=1 >&nbsp;Inclusive Total&nbsp;</TH>
</TR>
<?php
$totQ=0; $totLP=0; $totDV=0; $totCP=0; $totNett=0; $totVAT=0; $totTot = 0;
foreach($mfT as $row) {
  $nettCP=0;
?>
      <TR>
      <TD class='dC' nowrap><?php echo $row['product_code']?></TD>
      <TD class='dC' style= text-align:left; nowrap><?php echo $row['product_description']?></TD>
      <TD class='dC' style='text-align:right'; nowrap><?php echo $row['ordered_qty']?></TD>
<?php
  $totQ+= $row['ordered_qty'];

  if (!$hasRoleVP) {
?>  	
      <TD nowrap colspan=\"8\">not authorised to view pricing</td>
<?php
  } else {
?>  	
      <TD class='dC' style='text-align:right'; nowrap><?php echo number_format($row['net_price'],2, '.',' ')?></TD>
<?php
    $totCP+= $row['net_price'];
    $nettCP=$row['ordered_qty']*$row['net_price'];
    
?>
      <TD class='dC' style='text-align:right'; nowrap><?php echo number_format($nettCP,2,'.',' ')?></TD>
      <TD class='dC' style='text-align:right'; nowrap><?php echo number_format($row['vat_amount'],2, '.',' ')?></TD>
      <TD class='dC' style='text-align:right;  border-right:solid; border-right-width:1px; border-right-color:black;' nowrap><?php echo number_format($row['total'],2, '.',' ')?></TD>
<?php
      $totVAT+= $row['vat_amount'];
      $totTot+= $row['total'] ;
      $totNett+=$nettCP;
  }
?>
      </TR>
      <TR>
       <TD class='dC' style='widTD:95px;'nowrap colspan=1 text-align:left;>&nbsp;</TD>
       <TD class='dC' style='widTD:200px';nowrap colspan=1 >&nbsp;</TD>
       <TD class='dC' style='widTD:60px';nowrap colspan=1 >&nbsp;</TD>
       <TD class='dC' style='widTD:75px';nowrap colspan=1>&nbsp;</TD>
       <TD class='dC' style='widTD:90px';nowrap colspan=1>&nbsp;</TD>
       <TD class='dC' style='widTD:90px';nowrap colspan=1>&nbsp;</TD>
       <TD class='dC' style='widTD:90px; border-right:solid; border-right-width:1px; border-right-color:black;' nowrap colspan=1>&nbsp;</TD>
       </TR>
<?php
}
?>
<!-- total line -->
     <TR>
        <th style='border-bottom:none; border-left:none;' colspan="2"></th>
        <th style='text-align:right'; nowrap><?php echo $totQ; ?></th>
<?php 
     if ($hasRoleVP) { 
?>

  <th style='border-bottom:none;'>&nbsp;</th>
  <th style='text-align:right;' nowrap><?php echo number_format($totNett,2,'.',' '); ?></th>
  <th style='text-align:right;' nowrap><?php echo number_format($totVAT,2,'.',' '); ?></th>
  <th style='text-align:right; border-right:solid; border-right-width:1px; border-right-color:black;' nowrap><?php echo number_format($totTot,2,'.',' '); ?></th>
<?php 
     } else { 
?>
  <th>&nbsp;</th>
  <th>&nbsp;</th>
  <th>&nbsp;</th>
  <th>&nbsp;</th>
  <th>&nbsp;</th>
  <th>&nbsp;</th>
  <th>&nbsp;</th>
  <th>&nbsp;</th>
  <th>&nbsp;</th>
  <th>&nbsp;</th>
<?php } ?>
</TR>

</TABLE>
<!-- footer -->
<TABLE style='width:100%;'>
<TR>
  <TD &nbsp;</TD>
  <TD &nbsp;</TD>
  <TD &nbsp;</TD>    
</TR>	
<TR>
  <TD colspan="3" style='text-align:left; background-color:white; color:Black;'><SPAN style='font-weight:bold; font-size:0.8em;'>Terms And Conditions:</SPAN><BR><SPAN style='font-weight:normal; font-size:1.0em;'><?php echo (str_replace(chr(10),"<BR>", $mfT[0]['tcs'])); ?></SPAN></TD>
</TR>

<TR>
  <TD &nbsp;</TD>
  <TD &nbsp;</TD>
  <TD &nbsp;</TD> 
  <TD &nbsp;</TD>
  <TD &nbsp;</TD>
  <TD &nbsp;</TD>      
</TR>		
	
<TD colspan="2" style='text-align:center; color:grey; font-weight:normal; font-size:0.55em;'>
<img src='<?php echo HOST_SURESERVER_AS_USER.$PHPFOLDER; ?>images/rt_powerby.gif' style="border:1px solid #ccc;float:right; width:75px;height:30px" >
  <script type="text/javascript">var d = new Date(); document.write("<b>" + d.getDate() + "/" + d.getMonth() + "/" + d.getFullYear() + "&nbsp;&nbsp;" + d.getHours() + ":" + d.getMinutes() + ":" + d.getSeconds() + "</b>");</script>
  </TD>
</TR>
</TABLE>

<?php
$dbConn->dbClose();
?>
<script type='text/javascript'>
function emailDoc() {
	var params="?USERID=<?php echo $userId; ?>&OBJECTID=<?php echo EO_PROFORMAINV_CARD; ?>&SUBJECT=Proforma Invoice as per Request: <?php echo $postDOCMASTID; ?>&ORDESEQID=<?php echo $mfT[0]['order_sequence_no'] ?>";
	window.location='<?php echo $ROOT.$PHPFOLDER ?>functional/administration/functions/emailUserHTML.php'+params;
}
</script>

</BODY>

</HTML>