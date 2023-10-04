<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    include_once($ROOT.$PHPFOLDER ."DAO/db_Connection_Class.php");
    include_once($ROOT.$PHPFOLDER ."DAO/onlineManageDAO.php");
    include_once($ROOT.$PHPFOLDER ."properties/Constants.php");
		include_once($ROOT.$PHPFOLDER .'libs/GUICommonUtils.php');
		include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
    
    //Create new database object
    $dbConn  = new dbConnect(); $dbConn->dbConnection();
    
    $errorTO = new ErrorTO;
?>  

<!DOCTYPE html>
<html>
	  <head>
	  	  <title>Import Transaction Management</title>
            <link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/1_kwelanga.css' rel='stylesheet' type='text/css'>
            <link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/kos_standard_screen2.css' rel='stylesheet' type='text/css'>

            <script type="text/javascript" language="javascript" src="<?php echo $ROOT.$PHPFOLDER ?>js/jquery.js"></script>
            <script type="text/javascript" language="javascript" src="<?php echo $ROOT.$PHPFOLDER ?>js/dops_global_functions.js"></script>
        <style>
    	     a.ac1 {text-align:left; 
    	     	      font-weight:normal; 
    	     	      color:red;  }
    	
    	     a.ac2 {text-align:left; 
    	     	      font-weight:normal; 
    	     	      color:green;  }
    	     	      
           table.box {border:collapse;
           border: 2px solid; 
           border-color: #990000; 
           background: #fcecec }     	      
    	     	        
        </style>
    </head>
	  <body>
	  	 <?php 
       if (isset($_POST['CANFORM'])) {?>
	  	 	     <script>alert('Manage Accounts Cancelled \n \n Please close this Window')</script>
	  	 	     <?php
	  	 	     return;
	  	 }
	  	 
       if (isset($_POST['ACCUPD'])) {
       	   $allOk = 'T';
           for ($x = 0; $x < count($_POST['ACCLIST']); $x++) {
           	   
           	   $onlineManageDAO = new onlineErrorManagmentDAO($dbConn);
               $errorTO = $onlineManageDAO->setStoreSpecialFieldNew($_POST['FLDID'], 
                                                                    $_POST['UIDLIST'][$x], 
                                                                    test_input($_POST['ACCLIST'][$x]), 
                                                                    $_POST['EMADDR'],
                                                                    $_POST['SEUID'][$x]);
               
               if($errorTO->type!=FLAG_ERRORTO_SUCCESS) {
                   $allOk = 'F';  
               }
           }
           
           if($allOk <> 'T') { ?>
                 <script>alert('ErrorSaving Spec. Fields (X003) \n \n Please close this Window')</script>
                 <?php	
           } else {?>
                 <script>alert('Update Successfull \n \n Please close this Window')</script>
                 <?php	
           }
           
           return;
       }
       if (isset($_POST['CLEARACCS'])) {
       	
            $clrList = explode(',',$_POST['ERRLIST']);
       	
       	    foreach($clrList as $row) {
                 $onlineManageDAO = new onlineErrorManagmentDAO($dbConn);
                 $eManageTx = $onlineManageDAO->setTransactionToDelete(substr($row,0, strpos($row,'~')), $_POST['EMADDR']);
       	    }
            ?>
            <script>alert('Errors Cleared \n \n Please close this Window')</script>
            <?php  
            return;
       }
	  	 
       if (isset($_POST['TXCLEAR'])) {
       	     if($_POST['ERRLIST'] > 0 ) {
       	     	    clearConfirm($_POST['NOTIFY'], implode(',', $_POST['ERRLIST']), $_POST['EMADD']);
       	     } else { ?>
       	     	    <script>alert("Nothing Selected to Clear \n \n Please close this Window")</script>
                  <?php
       	     }
       	
       	}
	  	 
// Fix Screen ************************************************************************************************************************************
	  	 
	  	 if (isset($_POST['TSFIX'])) {
	  	 	     if(count($_POST['ERRLIST']) > 0) {
                 // echo "<pre>";
                 // print_r($_POST['ERRLIST']);
                 // echo "<br>";
                 
                 $el = Array();

                 
                 foreach($_POST['ERRLIST'] as $row) {
                      $el[] = substr($row,strpos($row,'~') + 1,15);
                 } 
                 $docList = implode(",",$el);
                 $noteId  = $_POST['NOTIFY'];
                 $emAddr  = $_POST['EMADD'];
                 $onlineManageDAO = new onlineErrorManagmentDAO($dbConn);
                 $eManageTx = $onlineManageDAO->getSelectedErrorList($prinUid, $docList, $noteId, $emAddr);
                 if(count($eManageTx) > 0) { ?>
                      <center>	
	                         <FORM name='Manage Transaction' method=post action=''>
                               <table width="80%" style="border:none">        	
                                  <tr>
                                      <td rowspan="3"; ><img src="<?php echo $ROOT.$PHPFOLDER; ?>images/logos/Kwelanga Solutions Logo smaller.jpg" style="width:120px; height:75px; float:left;" ></td>
                                      <td colspan="3";>&nbsp</td>
                                  </tr>
                                  <tr>
                                      <td colspan="1";>&nbsp</td>
                                      <td colspan="4"; style="text-align:left; font-size: 16px; font-weight:Bold;" >Kwelanga Online Line Transaction Management </td>
                                  </tr>
                                  <tr>
                                      <td colspan="1";>&nbsp</td>
                                      <td colspan="4"; style="text-align:left; font-size: 18px; font-weight:Bold;" >&nbsp;</td>
                                  </tr>
                                  <tr>
                                      <td width="10%";>&nbsp</td>
                                      <td width="43%";>&nbsp</td>
                                      <td width="28%";>&nbsp</td>
                                      <td width="15%";>&nbsp</td>
                                      <td width="4%";>&nbsp</td> 	 	 	
                                  </tr>
                                  <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                                      <td colspan="5";>&nbsp</td>
                                  </tr>	        	
                                  <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                                      <td>&nbsp</td>
                                      <td colspan="3"; style="text-align:center; font-weight:bold; font-size:14px">Enter Missing Accounts</td>
                                      <td>&nbsp</td>
                                  </tr>        	
                                  <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                                      <td colspan="5";>&nbsp</td>
                                  </tr>
                                  <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                      <td class="det1" style="text-align:left;">Document No</td>
                                      <td class="det1" style="border:none; float: center;">Store</td>
                                      <td class="det1" style="border:none; float: center;">Error</td>
                                      <td class="det1" style="text-align:left;">Account No</td>
                                      <td style="border:none; float: center;">&nbsp;</td>
                                  </tr>
                                  <?php
                                  foreach($eManageTx as $row) {                                  	
                                    if(trim($row['value']) == NULL) {
                                        $placeH = "placeholder= 'Enter Account'";
                                        $val    = '';
                                    } else {
                                        $placeH = "";
                                        $val    = trim($row['value']);
                                    }   
                                  	?>
                                       <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                                            <td colspan="1"; style="font-weight:normal; font-size:11px"><?php echo ltrim($row['document_number'], '0');?></td>
                                            <td colspan="1"; style="font-weight:normal; font-size:11px"><?php echo  trim($row['deliver_name']);?></td>
                                            <td colspan="1"; style="font-weight:normal; font-size:11px"><?php echo  trim($row['general_reference_1']);?></td>
                                            <td style="text-align:left";><INPUT TYPE="text"   name="ACCLIST[]" <?php echo $placeH; ?> value=<?php echo trim($val);?>>
                                            	                           <INPUT TYPE="HIDDEN" name="UIDLIST[]" value= "<?php echo  trim($row['psmUid']);?>">
                                            	                           <INPUT TYPE="HIDDEN" name="SEUID[]"   value= "<?php echo  trim($row['seUid']);?>"></td>
                                            <td><INPUT TYPE="HIDDEN" name="EMADDR" value= "<?php echo $emAddr ;?>">
                                            	  <INPUT TYPE="HIDDEN" name="FLDID"  value= "<?php echo trim($row['fieldId']) ;?>"></td>
                                       </tr>                                  	
                                       <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                                            <td colspan="5";>&nbsp</td>
                                       </tr>
                                       <?php         	                                  	
                                  }  ?>
                                  <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                                       <td colspan="5";>&nbsp</td>
                                  </tr>	
                                  <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                                       <td>&nbsp</td>
                                       <td colspan="3"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="ACCUPD"  value= "Update Accounts">&nbsp;&nbsp;&nbsp;&nbsp;
                                	                                                 <INPUT TYPE="submit" class="submit" name="CANFORM" value= "Cancel"></td>
                                       <td>&nbsp</td>
                                  </tr>   
                                  <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                                      <td colspan="5";>&nbsp</td>
                                  </tr>	  
                               </table>
		                       </form>
                      </center> 	  	 	
                      <?php  
                 } else {?>
                      <script>alert('Something went wrong (x001) Call Support - Please close this Window')</script>
                      <?php
                      return;
                 }
	  	 	
             } else {?>
                      <script>alert('Nothing Select to Fix - Please close this Window')</script>
                      <?php
                      return;
             }
	     }
	  //First Screen ************************************************************************************************************************************

       if (!isset($_POST['TSFIX']) && 
           !isset($_POST['TXCLEAR']) && 
           !isset($_POST['ACCUPD'])  &&
           !isset($_POST['CLEARACCS'])) {
	  	 	     // Get Transaction to Manage
	  	 	     $onlineManageDAO = new onlineErrorManagmentDAO($dbConn);
    	       $manageTx = $onlineManageDAO->getErrorListToManage($prinUid, $sfFid, $ntf, $eadd);
             $class = 'odd';
             ?>
             <center>	
	                  <FORM name='Manage Transaction' method=post action=''>
                         <table width="70%" style="border:none">        	
                            <tr>
                                <td colspan="2"; rowspan="3"; ><img src="<?php echo $ROOT.$PHPFOLDER; ?>images/logos/Kwelanga Solutions Logo smaller.jpg" style="width:120px; height:75px; float:left;" ></td>
                                <td colspan="4";>&nbsp</td>
                            </tr>
                            <tr>
                                <td colspan="4"; style="text-align:left; font-size: 16px; font-weight:Bold;" >Kwelanga Online Line Transaction Management </td>
                            </tr>
                            <tr>
                                <td colspan="4"; style="text-align:left; font-size: 18px; font-weight:Bold;" >&nbsp;</td>
                            </tr>
                            <tr>
                                 <td width="2%";>&nbsp;</td>
                                 <td width="20%";>&nbsp;</td>
                                 <td width="30%";>&nbsp;</td>
                                 <td width="30%";>&nbsp;</td>	
                                 <td width="15%";>&nbsp;</td>
                                 <td width="3%";>&nbsp;</td> 	 	 	
                            </tr>
                            <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                                <td colspan="6";>&nbsp</td>
                            </tr>	        	
                            <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                            	  <td>&nbsp</td>
                            	  <td colspan="4"; style="text-align:center; font-weight:bold; font-size:14px">Transaction Details to Clear</td>
                                <td>&nbsp</td>
                            </tr>        	
                            <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                                <td colspan="6";>&nbsp</td>
                            </tr>
                            
                            <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                 <td style="text-align:left;">&nbsp;</td>
                                 <td class="det1" style="text-align:left;">Document Number</td>
                                 <td class="det1" style="text-align:left;">Store</td>
                                 <td class="det1" style="text-align:left;">Error</td>
                                 <td class="det1" style="text-align:right;">Select<br><a href="javascript:;" onClick="selectAll('ERRLIST[]', 1);">All</a>&nbsp;|&nbsp;<a href="javascript:;" onClick="selectAll('ERRLIST[]', 0);">None</a></td>
                                 <td style="border:none; float: center;">&nbsp;</td>
                        </tr>
                            <?php
                            if(count($manageTx <> 0)) {
                                 foreach($manageTx as $row) { ?>
                                      <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                                          <td>&nbsp</td>
                                          <td colspan="1"; style="font-weight:normal; font-size:11px"><?php echo ltrim($row['document_number'], '0');?></td>
                                          <td colspan="1"; style="font-weight:normal; font-size:11px"><?php echo trim($row['deliver_name']);?></td>
                                          <td colspan="1"; style="font-weight:normal; font-size:11px"><?php echo trim($row['general_reference_1']);?></td>
                                          <td style="text-align:right; padding-right: 30px";><INPUT TYPE="checkbox" name="ERRLIST[]" value= '<?php echo $row['seUid'] . '~' . $row['DocUid']; ?>'></td>
                                          <td><INPUT TYPE="HIDDEN" name="NOTIFY" value= "<?php echo $ntf ;?>">
                                              <INPUT TYPE="HIDDEN" name="EMADD" value= "<?php echo $eadd ;?>"</td>
                                      </tr>                                  	
                                      <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                                          <td colspan="6";>&nbsp</td>
                                      </tr>
                             <?php
                                 }	
                            }?>
                            <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                                <td colspan="6";>&nbsp</td>
                            </tr>	
                            <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                            	  <td>&nbsp</td>
                                <td colspan="4"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="TXCLEAR"    value= "Clear Selected">&nbsp;&nbsp;&nbsp;&nbsp;
                                	                                          <INPUT TYPE="submit" class="submit" name="TSFIX"      value= "Fix Selected">&nbsp;&nbsp;&nbsp;&nbsp;
                                	                                          <INPUT TYPE="submit" class="submit" name="CANFORM"   value= "Cancel"></td>
                                <td>&nbsp</td>
                            </tr>   
                            <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                                <td colspan="6";>&nbsp</td>
                            </tr>	  

                         </table>
		                </form>
             </center> 
       <?php                
       } ?>      
 	  </body>
</html>

   <script type="text/javascript" defer>
       function selectAll(elementName, flag) {
           $("input[name='"+elementName+"']").each( function(){$(this).attr("checked",((flag == 1)?true:false));})
       }
  </script>
  
  
<?php
 function test_input($data) {

  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  if($data=='') { $data="Enter&nbsp;Account"; } 
    
  return $data;
}  
 function clearConfirm($notify, $errList, $emAdd) {	
     include_once('ROOT.php'); include_once($ROOT.'PHPINI.php'); 
     
     
     ?> 
  	 
  	 <center>
       <form name='Confirm Clear Transactions' method=post action=''>
        <table width="500"; style="border:none">
            <tr>
               <td width="20%";>&nbsp</td>
               <td width="20%";>&nbsp</td>
               <td width="20%";>&nbsp</td>
               <td width="20%";>&nbsp</td>
            </tr>
            <tr>
              <td rowspan="3"; ><img src="<?php echo 'https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/images/logos/Kwelanga Solutions Logo smaller.jpg';?>" style="width:120px; height:75px; float:left;" ></td>
              <td colspan="3";>&nbsp</td>
            </tr>
            <tr>
              <td colspan="3"; style="text-align:left; font-size: 16px; font-weight:Bold;" >Kwelanga Online Line Transaction Management </td>
            </tr>
            <tr>
               <td Colspan="3">&nbsp</td> 	
            </tr>
            <tr>
               <td Colspan="4">&nbsp</td> 	
            </tr>
        </table>
        <table class="box" width="400";>
            <tr>
               <td width="20%";>&nbsp</td>
               <td width="30%";>&nbsp</td>
               <td width="15%";>&nbsp</td>
               <td width="30%";>&nbsp</td>                             
               <td width="5%"; style="border:collapse; border-right: 2px solid; border-color: #990000;">&nbsp</td>  
            <tr>
               <td Colspan="1" rowspan="3"><img src="<?php echo 'https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/images/error-icon-big.png'; ?>" style="width:60px; height:60px; float:left;" ></td> 	
               <td Colspan="3" style="font-size: 13px; font-weight: bold;">Confirm&nbsp;Clearing&nbsp;of&nbsp;Import&nbsp;Transactions</td> 
               <td Colspan="1" style="border:collapse; border-right: 2px solid; border-color: #990000;">&nbsp</td> 
            </tr>
            <tr>
               <td Colspan="4"><INPUT TYPE="HIDDEN" name="NOTEID"  value= "<?php echo $notify ;?>">
                               <INPUT TYPE="HIDDEN" name="ERRLIST" value= "<?php echo $errList;?>">
                               <INPUT TYPE="HIDDEN" name="EMADDR" value= "<?php echo $emAdd;?>"></td> 	
            </tr>       	
            <tr>
               <td Colspan="1"; style="text-align:left";><INPUT TYPE="submit" class="submit" name="CLEARACCS" value= "Continue "></td>
               <td Colspan="1">&nbsp;</td> 	
               <td Colspan="1"; style="text-align:left";><INPUT TYPE="submit" class="submit" name="CANFORM"  value= "Cancel Clear"></td> 	
               <td Colspan="1">&nbsp;</td>
            </tr> 
            <tr>
               <td Colspan="5" style="border:collapse; border-right: 2px solid; border-color: #990000;"></td> 	
            </tr> 
            <tr>
               <td Colspan="5" style="border:collapse; border-right: 2px solid; border-color: #990000;">&nbsp</td> 	
            </tr>
        </table>


       </form>
    </center>
<?php
 }?>  