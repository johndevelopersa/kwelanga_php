<?php
// https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/functional/payments/UpdateMonthendBalances1209.php

ini_set('max_execution_time', 3600); //300 seconds = 5 minutes

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once ($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER."libs/CommonUtils.php");
include_once($ROOT.$PHPFOLDER."DAO/CustomerBalancesDAO.php");
include_once($ROOT.$PHPFOLDER.'elements/basicSelectElement.php');	
	
$dbConn = new dbConnect();
$dbConn->dbConnection();

if (isset($_POST["UPDATDATE"]))    $postUPDATDATE=$_POST["UPDATDATE"];       else $postUPDATDATE = ''; 
if (isset($_POST["UPDATTYPE"]))    $postUPDATTYPE=$_POST["UPDATTYPE"];       else $postUPDATTYPE = ''; 
if (isset($_POST["UPDATBATCH"]))   $postUPDATBATCH=$_POST["UPDATBATCH"];     else $postUPDATBATCH = ''; 
if (isset($_POST["UPDATACCOUNT"])) $postUPDATACCOUNT=$_POST["UPDATACCOUNT"]; else $postUPDATACCOUNT = ''; 


  
if (isset($_POST['finish'])) {  
	
  include_once($ROOT.$PHPFOLDER."functional/payments/UpdateMonthendBalances.php");
	
	UpdateMonthend::UpdateMonthendBalances(305, $postUPDATDATE, $postUPDATTYPE, $postUPDATACCOUNT, $postUPDATBATCH );
	
	return;
	
}	
 
?>
<!doctype html>
<html>
     <head>
     <title>extract data</title>
     </head>

     <body>
        <center>
          <form name='extract' method=post action=''>
             <table style="border: none";>
                 <tr>
                    <td colspan="3">&nbsp&nbsp&nbsp&nbsp&nbsp</td>
                 </tr>
                 <tr>
                      <th colspan="3">Run Month End Balances</th>
                 </tr>
                 <tr>
                      <td colspan="3">&nbsp</td>	
                 </tr>
                 <tr>
                 	  <td colspan="1" style="text-align:left;">Select Period</td>
                 	  <td colspan="1">&nbsp</td>
                  	<td colspan="1"; style="text-align:left;"><?php $lableArr = array('201711','201712', '201801', '201802', '201803', '201804', '201805', '201806', '201807', '201808', '201809', '201810', '201811','201909');
          		                                                $valueArr = array('201711','201712', '201801', '201802', '201803', '201804', '201805', '201806', '201807', '201808', '201809', '201810', '201811', '201909');
          		                                                BasicSelectElement::buildGenericDD('UPDATDATE', $lableArr,$valueArr, $postUPDATDATE, "N", "N", null, null, null);?>
                    </td>
                 </tr>
                 <tr>
                      <td colspan="3">&nbsp</td>	
                 </tr>
                 <tr>
                     <td colspan="1" style="text-align:left;">Select Payment By</td>
                     <td colspan="1">&nbsp</td>
                     <td colspan="1"; style="text-align:left;"><?php $lableArr = array( 'PAYMENT BY CUSTOMER','PAYMENT BY GROUP');
          		                                                $valueArr = array('1','2');
          		                                                BasicSelectElement::buildGenericDD('UPDATTYPE', $lableArr,$valueArr, $postUPDATDATE, "N", "N", null, null, null);?>
                    </td>
                 </tr>
                 <tr>
                      <td colspan="3">&nbsp</td>	
                 </tr>
                 <tr>
                     <td colspan="1" style="text-align:left;">Select Update UID</td>
                     <td colspan="1">&nbsp</td>
                     <td colspan="1"; style="text-align:left;"><input type="text" name="UPDATACCOUNT"></td>
                 </tr>
                 <tr>
                      <td colspan="3">&nbsp</td>	
                 </tr>
                 <tr>
                     <td colspan="1" style="text-align:left;">Select Update Batch</td>
                     <td colspan="1">&nbsp</td>
                     <td colspan="1"; style="text-align:left;"><input type="text" name="UPDATBATCH"></td>
                 </tr>


                 <tr>
                     <td colspan="3">&nbsp</td>	
        	       </tr>
                 <tr>
        	          <td colspan="3"; style="text-align:center;"><input type="submit" class="submit" name="finish" value= "Run Update"></td>
                 </tr>
				     </table>
		      </form>
        </center>    
     </body>
</html>