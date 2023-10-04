<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER."properties/Constants.php");
		include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
    include_once($ROOT.$PHPFOLDER."elements/datePickerElement.php");
    include_once($ROOT.$PHPFOLDER."DAO/AgedStockDAO.php");    
    include_once($ROOT.$PHPFOLDER."DAO/PostAgedStockDAO.php");  
    include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
    		
?>
<!DOCTYPE html>
<HTML>
	<HEAD>

		<TITLE>Document Management</TITLE>

    <link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/1_kwelanga.css' rel='stylesheet' type='text/css'>
    <script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/jquery.js"></script>
    <script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/dops_global_functions.js"></script>

    <style>
    	td.head1 {font-weight:normal;
    		        font-size:2em;text-align:left; 
    		        font-family: Calibri, Verdana, Ariel, sans-serif; 
    		        padding: 0 150px 0 150px }
      
      td.det1  {border-style:solid none solid none; 
      	        border-color:DarkGray; 
      	        border-width:1px;
      	        border-collapse:collapse; 
      	        text-align: center; 
      	        font-weight: bold; 
      	        font-size: 15px;
      	        padding: 0 150px 0 150px  }

     td.det2  {border-style:solid solid solid none; 
      	        border-color:DarkGray; 
      	        border-width:1px;
      	        border-collapse:collapse; 
      	        text-align: left; 
      	        font-weight: normal; 
      	        font-size: 12px;  }
    	
    	</style>

		</HEAD>
<body>

<?php

    $class = 'odd';

    if (!isset($_SESSION)) session_start() ;
      $userUId     = $_SESSION['user_id'] ;
      $principalId = $_SESSION['principal_id'] ;
      $depotId     = $_SESSION['depot_id'] ;
      $systemId    = $_SESSION["system_id"];
      $systemName  = $_SESSION['system_name'];
      
      echo "HH";
      
      if (isset($_POST["INVOICE"])) {
            $clnInvoice= test_input($_POST["INVOICE"]); 
            if(strpos($clnInvoice,'-') == FALSE) {
                 $postINVOICE = str_pad(ltrim($clnInvoice, '0'),8,'0',STR_PAD_LEFT);
            } else {
                 $postINVOICE = str_pad(ltrim(trim(substr($clnInvoice,strpos($clnInvoice,'-')+1,10)), '0'),8,'0',STR_PAD_LEFT) 	;
            }
      } else {
            $postINVOICE = '';
      }       
      echo "<br>";
      echo  $postINVOICE;
      //Create new database object
     $dbConn = new dbConnect(); $dbConn->dbConnection();
     $errorTO = new ErrorTO;
// *************************************************************************************************************************************
     if (isset($_POST['cancel'])) {
        return;
     }
     
     if (isset($_POST['finish'])) {
     	
           if (isset($_POST["BOXES"]))   $boxes      = test_input($_POST["BOXES"]);   else $boxes   = '';
           $docno     = $_POST['DOCNO'];
           
           $PostAgedStockDAO = new PostAgedStockDAO($dbConn);
           $errorTO = $PostAgedStockDAO->SaveDispatchRecordsToTracking($principalId, $docno, $boxes, $userUId, $comment );

             if ($errorTO->type!=FLAG_ERRORTO_SUCCESS) {?>
                   <script type='text/javascript'>parent.showMsgBoxError('<?php echo $errorTO->description; ?><br>Contact Kwelanga Support')</script> 
                   <?php
                   return;
             }   
             
             echo "HHH";  
             
             if ($errorTO->type==FLAG_ERRORTO_SUCCESS) {        	   
                    $returnMessages->description="Dispatch Successfully Saved<BR>";
//                    $returnMessages->description .= "<BR>https://www.w3schools.com", "_blank", "toolbar=yes,scrollbars=yes,resizable=yes,top=500,left=500,width=400,height=400");>[VIEW/PRINT DISPATCH DOCUMENT]</a>;                    

echo $returnMessages->description;

                    ?>
                   <script 
                         type='text/javascript' >parent.showMsgBoxInfo("<?php echo $returnMessages->description;?>")
                   </script>
                   <?php
                   unset($_POST['finish']);
                   unset($_POST['firstform']);
                   unset($_POST["BOXES"]);  
             } else {
                   ?>
                     <script type='text/javascript'>parent.showMsgBoxError('<?php echo $errorTO->description; ?>')</script> 
                   <?php
                   unset($_POST['finish']);
                   unset($_POST['firstform']);
                   unset($_POST["BOXES"]);  
             }
     }
     if (isset($_POST['firstform']) && $postINVOICE !== '') {
     
          $AgedStockDAO = new AgedStockDAO($dbConn);
          $mfDDU = $AgedStockDAO->getDocumentDetailsToDispatch($principalId,$postINVOICE);
 
          if (sizeof($mfDDU)!==0) {
              $parray = Array(76)	;
              if(!in_array($mfDDU[0]['document_status_uid'],$parray)) {?>
              	
                   <center>
                      <FORM name='displayinv' method=post target=''>
                        <table width:"80%"; style="border-none";>
                           <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                              <td  colspan="5"; style="text-align:center" >&nbsp;</td>
                            </tr>
                            <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                               <td class=head1 colspan="5"; style="text-align:center" >Confirm Warehouse Dispatch </td>  
                            </tr>
                            <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                               <td  colspan="5" >&nbsp;</td>
                            </tr>
                            <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                               <td  colspan="2";>Dispatch To</td>
                               <td  colspan="3"; style="text-align:left"><?php echo $mfDDU[0]['Dispath_to'];?>
                                                                        <input type="hidden" name="PSDUID" value="<?php echo $mfDDU[0]['psdID'];?>"</td></td>
                               </tr>
                            <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                              <td colspan="2";>&nbsp;</td>
                              <td colspan="3"; style="text-align:center" ><input type="hidden" name="orderSeq" value="<?php echo $mfDDU[0]['uid'];?>">
                                                                        <input type="hidden" name="DOCNO"    value="<?php echo $postINVOICE;?>"></td>              	
                            </tr>	
                          <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                             <td  colspan="2";>Document&nbsp;No</td>
                             <td  colspan="3"; style="text-align:left"><?php echo substr($mfDDU[0]['document_number'],2,6);?></td>
                          </tr>
                            <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                               <td  colspan="5" >&nbsp;</td>
                            </tr>
                          <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                          	 <td  colspan="2";>Customer</td>
                             <td  colspan="3"; style="text-align:left"><?php echo $mfDDU[0]['deliver_name'];?>
                             	                                         <input type="hidden" name="PSMUID" value="<?php echo $mfDDU[0]['StoreUid'];?>"</td></td>
                           </tr> 
                          <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                          	 <td  colspan="5" >&nbsp;</td>
                          </tr>
                          <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                         	   <td  colspan="2";>No of Boxes</td>
                             <td  colspan="3"; style="text-align:left"><input type="text" name="BOXES" value="<?php echo $mfDDU[0]['boxes'];?>"></td>
                          </tr> 
                          <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                          	 <td  colspan="5" >&nbsp;</td>
                          </tr>          
                          <tr class="<?php echo GUICommonUtils::styleEO($class);?>">  
                            <td  colspan="5"; style="text-align:center" >&nbsp;</td>  
                          </tr>                      
                          <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                         	   <td  colspan="2";>Comments</td>
                             <td  colspan="3"><textarea name="COMMENT" id="COMMENT"  rows="2" cols="15" ><?php echo $comment; ?></textarea></td>
                          </tr>       
                           
                          <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                                <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="finish" value= "Create Dispatch Document">
                                	                                          <INPUT TYPE="submit" class="submit" name="cancel" value= "Cancel"></td>
                           </tr>
                          <tr class="<?php echo GUICommonUtils::styleEO($class);?>">  
                            <td  colspan="5"; style="text-align:center" >&nbsp;</td>  
                          </tr>   
                        </table>
                      </FORM>
	                 </center>      
              <?php 
              } else { ?>
                  <script type='text/javascript'>parent.showMsgBoxError('Document Already Dispatched')</script> 
                  <?php 
                  unset ($_POST['firstform']);
                  unset ($postINVOICE);
              }
          } else {
               ?>
               <script type='text/javascript'>parent.showMsgBoxError('No Document Found')</script> 
               <?php
               unset ($_POST['firstform']);
               unset ($postINVOICE);
          }

}

// *************************************************************************************************************************************
     
 
if(!isset($_POST['firstform']) && $postINVOICE == '') { ?>
   <center>	
       <FORM name='Capture Warehouse Dispatch' method=post action=''>
            <table width:"720"; style="border:none">        	
               <tr>
                  <td>&nbsp</td>
                  <td>&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                  <td class=head1 colspan="2"; style="text-align:center;" >Capture Warehouse Dispatch</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                  <td colspan="2";>&nbsp</td>
               </tr>	        	
               <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                  <td style="text-align:left";>Enter Deocument Number</td>
                  <td style="text-align:left";><input type="text" name="INVOICE"></td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                  <td colspan="2";>&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                  <td colspan="2"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="firstform" value= "Capture Dispatch"></td>
               </tr>          
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                  <td colspan="2";>&nbsp</td>
               </tr>  
            </table>
       </form>
    </center>
<?php
} ?>     
	</body>       
 </HTML>
 
<?php
 function test_input($data) {

  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  
  return $data;
 }
?> 