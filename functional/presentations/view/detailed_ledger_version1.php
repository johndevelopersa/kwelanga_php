<?php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER."elements/SignatureArea.php");
include_once($ROOT.$PHPFOLDER."DAO/AdministrationDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/ReportsDAO.php");

$userId = ((isset($_GET["USERID"]))?$_GET["USERID"]:"");
$userCategory = ((isset($_GET["USERCATEGORY"]))?$_GET["USERCATEGORY"]:"");
$principalId  = ((isset($_GET["PRINCIPALID"]))?$_GET["PRINCIPALID"]:"");
$docmastId    = ((isset($_GET["DOCMASTID"]))?$_GET["DOCMASTID"]:"");
$outputTyp    = ((isset($_GET["OUTPUTTYP"]))?$_GET["OUTPUTTYP"]:"");
$custName     = ((isset($_GET["CUSTNAME"]))?$_GET["CUSTNAME"]:"");
$psmUid       = ((isset($_GET["PSMUID"]))?$_GET["PSMUID"]:"");

$dbConn->dbConnection();

$adminDAO = new AdministrationDAO($dbConn);
$hasRoleSign = $adminDAO->hasRole(mysqli_real_escape_string($dbConn->connection, $userId), mysqli_real_escape_string($dbConn->connection, $principalId), ROLE_SIGNITURE);
$hasRoleVP   = $adminDAO->hasRole(mysqli_real_escape_string($dbConn->connection, $userId), mysqli_real_escape_string($dbConn->connection, $principalId),ROLE_VIEW_PRICE);

// Get Banking Details

$reportsDAO = new ReportsDAO($dbConn);
$mfT = $reportsDAO->getPrincipalBankingDetails(mysqli_real_escape_string($dbConn->connection, $principalId));

?>

<!DOCTYPE html>
<html>
   <title></title>
    <head>
      <style type="text/css">
         #wrapper{width:700px;text-align:left;}
   
         #toolbar {font-size:12px;background:#047;padding:8px 10px}
         #toolbar a img{margin:2px 5px 2px 0px;}
         #toolbar a:hover{background:aliceBlue}
         #toolbar a{margin-right:10px;float:left;background:#fff;text-align:center;display:block;border:1px solid #047;padding:0px 8px;line-height:36px;text-decoration:none;color:#666;font-weight:bold;}
         #block{background:#fff;padding:10px 5px;border:1px solid #ccc;}
         .dtitle{text-align:left;}
   
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
          <?php if (substr($outputTyp,0,3) == "pdf") { ?>
                     td.dc3 {background-color:white; 
                     color:black; 
                     font-weight:normal; 
                     font-size:45px;}   
                     
                     td.dc3a {background-color:white; 
                     color:black; 
                     font-weight:bold; 
                     font-size:45px;}

                     td.dc3h {background-color:white; 
                     color:black; 
                     font-weight:bold; 
                     font-size:70px;}

          <?php } else { ?>
          	         td.dc3 {background-color:white; 
                     color:black; 
                     font-weight:normal; 
                     font-size:15px;}         	
          	         
                     td.dc3a {background-color:white; 
                     color:black; 
                     font-weight:bold; 
                     font-size:15px;}

                     td.dc3h {background-color:white; 
                     color:black; 
                     font-weight:bold; 
                     font-size:25px;}

          <?php } ?>
      </style>
     <script type="text/javascript" language="javascript" src="<?php echo $ROOT.$PHPFOLDER ?>js/jquery.js"></script>
    </head>
    <body>
       <div align="center" id="noprint" class="disableprint" >
         <table id="wrapper" cellspacing="0" cellpadding="0">
            <tr>
              <td>
                <div id="noprint"><!-- HIDE THIS PRINT AREA : START /--->
                  <div id="toolbar">
                    <a href="javascript:window.print();"><img src="<?php echo $ROOT.$PHPFOLDER ?>images/print-icon.png" border="0" alt="" align="left" > PRINT</a>
                 <?php 
                    if ($userCategory == "P") { ?>
                        <a href="javascript:;" onclick="emailDoc1();"><img src="<?php echo $ROOT.$PHPFOLDER ?>images/email-icon.png" border="0" alt="" align="left" > Email Self</a>
                        <a href="javascript:;" onclick="emailDoc2();"><img src="<?php echo $ROOT.$PHPFOLDER ?>images/email-icon.png" border="0" alt="" align="left" > Email Customer</a>
                  <?php } ?>
                        <div style="clear:both;"></div>
                  </div>
                </div><!-- HIDE THIS PRINT AREA : END /--->
              </td>
            </tr>
         </table>
       </div>
       <?php
       $tsql = "select `Field1`,
                `Field2`,
                `Field3`,
                `Field4`,
                `Field5`,
                `Field6`,
                `Field7`,
                `Sort`
       from temp_ledger_" . $userId . " where 1
       order by sort, Field1;";
       $statement = $dbConn->dbGetAll($tsql);
       
//   print_r($statement);
       
       
       ?>
       <table style="border-collapse:collapse; width:96%">
           <tr>
              <td width: 12%;>&nbsp;</td>
              <td width: 12%;>&nbsp;</td>
              <td width: 12%;>&nbsp;</td>
              <td width: 12%;>&nbsp;</td>
              <td width: 9%; >&nbsp;</td>
              <td width: 9%; >&nbsp;</td>
              <td width: 15%;>&nbsp;</td>
              <td width: 15%;>&nbsp;</td> 
           </tr>
           <?php
           foreach($statement as $row) {
           	   $h1array = array(1);
               if(in_array($row['Sort'], $h1array)) { ?>
                  <tr>
                     <td class="dc3h" colspan="4" style="text-align:left; "><?php echo $row['Field1']; ?></td>
                     <td class="dc3"  colspan="4" style="text-align:right;"><?php echo $row['Field6']; ?></td>
                  </tr>
           <?php 
             }           	
               $h1array = array(3);
               if(in_array($row['Sort'], $h1array)) { ?>
                  <tr>
                     <td class="dc3a" colspan="4" style="text-align:left; "><?php echo $row['Field1']; ?></td>
                     <td class="dc3"  colspan="4" style="text-align:right;"><?php echo $row['Field6']; ?></td>
                  </tr>
           <?php 
             }
               $h2array = array(4,6,9);
               if(in_array($row['Sort'], $h2array)) { ?>
                  <tr>
                     <td class="dc3"  colspan="4" style="text-align:left; "><?php echo trim($row['Field1'] . ' ' . trim($row['Field2'])); ?></td>
                     <td class="dc3"  colspan="2" style="text-align:right;"></td>
                     <td class="dc3"  colspan="1" style="text-align:right;"><?php echo $row['Field5']; ?></td>
                     <td class="dc3"  colspan="1" style="text-align:right;"><?php echo $row['Field6']; ?></td>
                  </tr>
             <?php 
               }
               $h3array = array(2,7,11,13);
               if(in_array($row['Sort'], $h3array)) { ?>
                  <tr>
                     <td class="dc3"  colspan="4" style="text-align:left; "></td>
                     <td class="dc3"  colspan="4" style="text-align:right;"></td>
                  </tr>
            <?php 	
             	 }
             	 $tarray = array(8,14,19,18);
               if(in_array($row['Sort'], $tarray)) { ?>
                  <tr>
                     <td class="dc3"  colspan="1" style="text-align:left;"><?php echo $row['Field1']; ?></td>
                     <td class="dc3"  colspan="3" style="text-align:left;"><?php echo $row['Field2']; ?></td>
                     <td class="dc3"  colspan="1" style="text-align:left;"><?php echo $row['Field3']; ?></td>
                     <td class="dc3"  colspan="1" style="text-align:left;"><?php echo $row['Field4']; ?></td>
                     <td class="dc3"  colspan="1" style="text-align:right;"><?php echo $row['Field5']; ?></td>
                     <td class="dc3"  colspan="1" style="text-align:right;"><?php echo $row['Field6']; ?></td>
                  </tr>
           <?php   	            	
             	 } 
             	 $tarray = array(10,12);
               if(in_array($row['Sort'], $tarray)) { ?>
                  <tr>
                     <td class="dc3"  colspan="1" style="text-align:left;"><?php echo $row['Field1']; ?></td>
                     <td class="dc3"  colspan="3" style="text-align:left;"><?php echo $row['Field2']; ?></td>
                     <td class="dc3"  colspan="1" style="text-align:left;"><?php echo $row['Field3']; ?></td>
                     <td class="dc3"  colspan="1" style="text-align:left;"><?php echo $row['Field4']; ?></td>
                     <td class="dc3"  colspan="1" style="text-align:right;"><?php if($row['Field5'] <> NULL) {echo number_format(floatval(trim($row['Field5'])),2,"."," "); } else {echo $row['Field5'] ; } ?></td>
                     <td class="dc3"  colspan="1" style="text-align:right;"><?php if($row['Field6'] <> NULL) {echo number_format(floatval(trim($row['Field6'])),2,"."," "); } else {echo $row['Field6'] ; } ?></td>
                  </tr>
           <?php   	            	
               } 
               $tarray = array(17);
               if(in_array($row['Sort'], $tarray)) { ?>
                  <tr>
                     <td class="dc3"  colspan="2" style="text-align:right;"><?php if($row['Field1'] <> NULL) {echo number_format(floatval(trim($row['Field1'])),2,"."," "); } else {echo $row['Field1'] ; } ?></td>
                     <td class="dc3"  colspan="1" style="text-align:right;"><?php if($row['Field2'] <> NULL) {echo number_format(floatval(trim($row['Field2'])),2,"."," "); } else {echo $row['Field2'] ; } ?></td>
                     <td class="dc3"  colspan="1" style="text-align:right;"><?php if($row['Field3'] <> NULL) {echo number_format(floatval(trim($row['Field3'])),2,"."," "); } else {echo $row['Field3'] ; } ?></td>
                     <td class="dc3"  colspan="2" style="text-align:right;"><?php if($row['Field4'] <> NULL) {echo number_format(floatval(trim($row['Field4'])),2,"."," "); } else {echo $row['Field4'] ; } ?></td>
                     <td class="dc3"  colspan="1" style="text-align:right;"><?php if($row['Field5'] <> NULL) {echo number_format(floatval(trim($row['Field5'])),2,"."," "); } else {echo $row['Field5'] ; } ?></td>
                     <td class="dc3"  colspan="1" style="text-align:right;"><?php if($row['Field6'] <> NULL) {echo number_format(floatval(trim($row['Field6'])),2,"."," "); } else {echo $row['Field6'] ; } ?></td>
                  </tr>
           <?php   	            	
               }  
 
             	 $tarray = array(16);
                if(in_array($row['Sort'], $tarray)) { ?>
                  <tr>
                     <td class="dc3"  colspan="2" style="text-align:right;"><?php echo $row['Field1']; ?></td>
                     <td class="dc3"  colspan="1" style="text-align:right;"><?php echo $row['Field2']; ?></td>
                     <td class="dc3"  colspan="1" style="text-align:right;"><?php echo $row['Field3']; ?></td>
                     <td class="dc3"  colspan="2" style="text-align:right;"><?php echo $row['Field4']; ?></td>
                     <td class="dc3"  colspan="1" style="text-align:right;"><?php echo $row['Field5']; ?></td>
                     <td class="dc3"  colspan="1" style="text-align:right;"><?php echo $row['Field6']; ?></td>
                  </tr>
           <?php   	            	
               }
           }
           ?>
           </table>
           <table style="border-collapse:collapse; width:96%">
           <?php  if (trim($mfT[0]['banking_details']) <> NULL)  { 
           	      if($principalId == 306) { ?>
                     <tr> <?php $plus1 = strpos($mfT[0]["banking_details"],'+') ;
         	              $plus2 = strpos($mfT[0]["banking_details"],'$') ;
         	              $plus3 = strpos($mfT[0]["banking_details"],'&') ; ?>     
         	              <td class="dc3" style="width:50%"; "text-align:left"; nowrap ><table>
         	    	                                                                <tr>
         	    	                                                                	  <td class="dc3" style="width:13%"; "text-align:left";>Banking Details</td>
         	    	                                                                	  <td class="dc3" style="width:20%"; "text-align:left";><?php echo substr($mfT[0]["banking_details"],0,$plus1); ?></td>
         	    	                                                                	  <td class="dc3" style="width:17%"; "text-align:left";>&nbsp;</td>
         	    	                                                                	  <td class="dc3" style="width:50%"; "text-align:left";>&nbsp;</td>
         	    	                                                                </tr>
                                                                                <tr>
         	    	                                                                    <td class="dc3" style="width:13%"; "text-align:left";>Bank</td>
         	    	                                                                    <td class="dc3" style="width:20%"; "text-align:left";><?php echo substr($mfT[0]["banking_details"],$plus1+1,$plus2-$plus1-1); ?></td>
         	    	                                                                    <td class="dc3" style="width:17%"; "text-align:left";>&nbsp;</td>
         	    	                                                                    <td class="dc3" style="width:50%"; "text-align:left";>&nbsp;</td>
         	    	                                                                </tr>
                                                                                <tr>
         	    	                                                                    <td class="dc3" style="width:13%"; "text-align:left";>Branch</td>
         	    	                                                                    <td class="dc3" style="width:20%"; "text-align:left";><?php echo substr($mfT[0]["banking_details"],$plus2+1,$plus3-$plus2-1); ?></td>
         	    	                                                                    <td class="dc3" style="width:17%"; "text-align:left";>&nbsp;</td>
         	    	                                                                    <td class="dc3" style="width:50%"; "text-align:left";>&nbsp;</td>
         	    	                                                                </tr>
                                                                                <tr>
         	    	                                                                    <td class="dc3" style="width:13%"; "text-align:left";>Account No</td>
         	    	                                                                    <td class="dc3" style="width:20%"; "text-align:left";><?php echo trim(substr($mfT[0]["banking_details"],$plus3+1,20)); ?></td>
         	    	                                                                    <td class="dc3" style="width:17%"; "text-align:left";>&nbsp;</td>
         	    	                                                                    <td class="dc3" style="width:50%"; "text-align:left";>&nbsp;</td>
         	    	                                                                </tr>
                                                                      	    </table>
         	              </td>
                        <td class="dc3" >&nbsp;</td> 
                     </tr>	           	      	
                   <?php 
                  } else { ?>
                     <tr>
                        <td class="dc3" style="width:50%"; "text-align:left"; >Banking Details<br><?php echo (str_replace(chr(43),"<br>", $mfT[0]["banking_details"]))?></td>
                        <td class="dc3" style="width:50%"; "text-align:left"; ></td>
                     </tr>
                  <?php  
                  } 
                } ?>
                 <tr>
                       <td class="dc3" colspan="2" > </td>
                 </tr>
           <?php  if (trim($mfT[0]['statementmessage']) <> NULL)  { ?>
                     <tr>
                        <td class="dc3a" style="width:100%"; ><?php echo (str_replace(chr(43),"<br>", $mfT[0]["statementmessage"]))?></td>
                     </tr>
           <?php  } ?>
                 <tr>
                       <td class="dc3" colspan="2" > </td>
                 </tr>
                 <tr>
                       <td class="dc3" colspan="2" > </td>
                 </tr>
                 <tr>
                       <td class="dc3" colspan="2" > </td>
                 </tr>
                 <tr>  
                       <td class="dc3" ></td>
                       <td style="text-align:right; color:grey; font-weight:normal; font-size:0.55em;">
                             <img src="<?php echo $ROOT.$PHPFOLDER; ?>images/Kwelanga_Solutions_Logo_smaller.jpg" style="width:75px; height:30px; float:right;" >
                       </td>
                 </tr>
                 <tr>
                       <td class="dc3" > </td>                 	
                       <td width="100%;" style="text-align:right; color:grey; font-weight:normal; font-size:0.55em;">
                       <?php echo date('Y-m-d H:i:s'); ?>
                       </td>
                 </tr> 
                 <tr>
                       <td class="dc3" > </td>
                       <td style="text-align:right; color:grey; font-weight:normal; font-size:0.55em;">
                           <?php echo 'detailed_ledger_version1a'; ?> 
                      	</td>
                 </tr>
           </table>
    </body>
 </html>

<?php
$dbConn->dbClose();
?>
<script type="text/javascript">
  function emailDoc1() {  
      window.location="<?php echo $ROOT.$PHPFOLDER ?>functional/presentations/CreateAndSavePDF.php?DOCMASTID=<?php echo '';?>&USERID=<?php echo $userId;?>&PRINCIPALID=<?php echo $principalId;?>&OUTPUTTYP=<?php echo 'pdfSelf50';?>&PSMUID=<?php echo $psmUid ;?>&PRINNAM=<?php echo ($mfT[0]['principal_name']);?>&CUSTNAME=<?php echo $custName ;?>&TEMPLAT=<?php echo 'detailed_ledger_version1.php';?>";
  	}
  function emailDoc2() {
      window.location="<?php echo $ROOT.$PHPFOLDER ?>functional/presentations/CreateAndSavePDF.php?DOCMASTID=<?php echo '';?>&USERID=<?php echo $userId;?>&PRINCIPALID=<?php echo $principalId;?>&OUTPUTTYP=<?php echo 'pdfCust50';?>&PSMUID=<?php echo $psmUid ;?>&PRINNAM=<?php echo ($mfT[0]['principal_name']);?>&CUSTNAME=<?php echo $custName ;?>&TEMPLAT=<?php echo 'detailed_ledger_version1.php';?>";
   }  
</script> 