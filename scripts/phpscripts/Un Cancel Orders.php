<?php
// http://www.kwelangasolutions.co.za/kwelanga_system/scripts/phpScripts/Un Cancel Orders.php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once ($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER.'properties/dbSettings.inc');

if (isset($_GET["user"])) $user=$_GET["user"]; else if (isset($_POST["user"])) $user=$_POST["user"]; else $user="";
if (isset($_POST["prin"])) $prin=$_POST["prin"]; else $prin="";
if (isset($_POST["docno"])) $docno=$_POST["docno"]; else $docno="";
if (isset($_POST["newdep"])) $newdep=$_POST["newdep"]; else $newdep="";
if (isset($_POST["sstatus"])) $sstatus=$_POST["sstatus"]; else $sstatus="74";

echo '<br>';
echo '<br>';

echo "<form name='pform' action='".$_SERVER["PHP_SELF"]."' method='post'>";
echo "<input type='hidden' name='user' value='{$user}' />
	  Principal        :<br><input name='prin' value='{$prin}' /><br><br>
	  Document Number  :<br><input name='docno' value='{$docno}' /><br><br>
	  New Depot        :<br><input name='newdep' value='{$newdep}' /><br><br>
	  Status           :<br><input name='sstatus' value='{$sstatus}' /><br><br>
	  <input type='button' value='submit' onclick='document.pform.submit();' /> ";

echo "</form>";


if (isset($_POST["prin"])) {
   $dbConn = new dbConnect();
   $dbConn->dbConnection();

   $can_list=array();

   $fsql = ("select p.name,
                    dm.principal_uid,
                    dm.depot_uid,
                    d.name,
                    dm.document_number,
                    psm.deliver_name,
                    dh.invoice_date,
                    s.description,
                    dh.cases,
                    dh.invoice_total 
            from .document_master dm, 
                    document_header dh, 
                    principal_store_master psm, 
                    `status` s, 
                    depot d,
                    principal p
            where dm.uid = dh.document_master_uid
            and   dh.principal_store_uid = psm.uid
            and   s.uid = dh.document_status_uid
            and   p.uid = dm.principal_uid
            and   dm.depot_uid = d.uid 
            and   dm.principal_uid = ".$prin. "
            and   dh.document_status_uid = 47
            and   dm.document_number in (" .$docno . ")");
   $can_list = $dbConn->dbGetAll($fsql);


   if (sizeof($can_list)==0) {
        echo "No Orders to cancel: ".mysql_error($dbConn->connection);
        return;
   }

   ?>


   <!DOCTYPE html>
   <html>
   <head>
   <style>
   table, th, td {
       border: 1px solid black;
       border-collapse: collapse;
   }
   </style>
   </head>
   <body>
   <table style="width:100%">
     <tr>
       <th>Warehouse</th>
       <th>Document</th> 
       <th>Store</th>
       <th>Date</th>
       <th>Status</th>
       <th>Cases</th>
     </tr>
  
   <?php  
     foreach($can_list as $xtrow) { ?>
      <tr>
          <td style="width:15%"><?php echo($xtrow['name']);?></td>
          <td style="width:10%"><?php echo(substr($xtrow['document_number'],3,6));?></td>
          <td style="width:40%"><?php echo($xtrow['deliver_name']);?></td>
          <td style="width:10%"><?php echo($xtrow['invoice_date']);?></td>
          <td style="width:15%"><?php echo($xtrow['description']);?></td>
          <td style="width:10%; text-align:center;"><?php echo($xtrow['cases']);?></td>
       	</tr>
   <?php      } ?>
   </table>

   </body>
   </html>

   <?php



}


?>