<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER."DAO/InvoiceDiscountDAO.php");
    include_once($ROOT.$PHPFOLDER."properties/Constants.php");
    include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
    include_once($ROOT.$PHPFOLDER."libs/CommonUtils.php");
    include_once($ROOT.$PHPFOLDER."elements/datePickerElement.php");
    include_once($ROOT.$PHPFOLDER.'elements/basicSelectElement.php');
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
      	        font-weight: bold; 
      	        font-size: 12px;  }

     td.det3  {font-weight: bold; 
      	       font-size: 12px;  }
    	
    	</style>

		</HEAD>
<body>

<?php

         $class = 'odd';

    if (!isset($_SESSION)) session_start() ;
      $userUId     = $_SESSION['user_id'] ;
      $principalId = $_SESSION['principal_id'] ;
      $systemId    = $_SESSION["system_id"];
      $systemName  = $_SESSION['system_name'];
      
      if (isset($_POST["CUSTOMER"])) $postCUSTOMER=test_input($_POST["CUSTOMER"]); else $postCUSTOMER = ''; 
      if (isset($_POST["custname"])) $postcustname=$_POST["custname"]; else $postcustname = ''; 
      
      $postFROMDATE  = (isset($_POST["FROMDATE"])) ? htmlspecialchars($postFROMDATE=$_POST["FROMDATE"]) : CommonUtils::getUserDate();     
      $postENDDATE  = (isset($_POST["ENDDATE"])) ? htmlspecialchars($postENDDATE=$_POST["ENDDATE"]) : CommonUtils::getUserDate();     
      
      if (isset($_POST["CUSTUID"])) $postCUSTUID=test_input($_POST["CUSTUID"]); else $postCUSTUID = ''; 
      if (isset($_POST["DISTYPE"])) $postDISTYPE=test_input($_POST["DISTYPE"]); else $postDISTYPE = '';  

      if (isset($_POST["DISVAL"])) $postDISVAL=test_input($_POST["DISVAL"]); else $postDISVAL = '';  
      if (isset($_POST["MINVAL"])) $postMINVAL=test_input($_POST["MINVAL"]); else $postMINVAL = '';  

            
      //Create new database object
     $dbConn = new dbConnect(); $dbConn->dbConnection();
     
     $errorTO = new ErrorTO;
     
     if (isset($_POST['canform'])) {
        return;
     }

     if (isset($_POST['insertform'])) {

          $InvoiceDiscountDAO = new InvoiceDiscountDAO($dbConn);
          $errorTO = $InvoiceDiscountDAO->validateInput($postFROMDATE, $postENDDATE, $postDISVAL, 'P');

          if($errorTO->type <> 'E') { 
          	
               $InvoiceDiscountDAO = new InvoiceDiscountDAO($dbConn);
               $errorTO = $InvoiceDiscountDAO->insertDiscountRecord($principalId, $postFROMDATE, $postENDDATE, $postCUSTUID, $postDISTYPE,$postDISVAL, $postMINVAL, $userUId );

               if($errorTO->type == 'S') { ?>
                  <script type='text/javascript'>parent.showMsgBoxInfo('Discount Insert<br><?php echo  $errorTO->description; ?> <br>')</script> 
                  <?php 
                  
                  unset($_POST['firstform']);	
                  unset($_POST['actionform']);	
                  unset($_POST['insertform']);	
               } else { ?>
                      <script type='text/javascript'>parent.showMsgBoxError('Discount Insert<br><?php echo  $errorTO->description; ?> <br>')</script> 
                      <?php 
              }         	 
          } else {	?>
              <script type='text/javascript'>parent.showMsgBoxError('Parameter Error<br><?php echo  $errorTO->description; ?> <br>')</script> 
              <?php 
              unset($_POST['firstform']);	
              unset($_POST['actionform']);	
              unset($_POST['insertform']);	
          	
          }
     }

     if (isset($_POST['actionform'])) { ?>
                <center>
                    <FORM name='Insert Discount' method=post target=''>
                       <table width="720"; style="border:none">
                           <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                              <td class=head1 colspan="5"; style="text-align:center";>Manage Invoice Discounts</td>
                           </tr>
                           <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                              <td width="10%"; style="border:none">&nbsp</td>
                              <td width="30%"; style="border:none">&nbsp</td>
                              <td width="30%"; style="border:none">&nbsp</td>
                              <td width="20%"; style="border:none">&nbsp</td>
                              <td width="10%"; style="border:none">&nbsp</td>
                           </tr>
                           <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                              <td >&nbsp</td>
                              <td  class=det3 >Customer</td>
                              <td colspan="2"; style="text-align:left;"><?php echo trim(substr($postcustname,10,50)); ?></td> 
                              <td colspan="1"; >&nbsp;</td>
                            </tr>
                           <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                              <td Colspan="5"><input type="hidden" name="CUSTUID" value=<?php echo substr($postcustname,0,9); ?>></td>
                           </tr>
                           <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                           	  <td >&nbsp</td>
                              <td class=det3; >Start Date </td>
                              <td colspan="2"><?php DatePickerElement::getDatePickerLibs(); DatePickerElement::getDatePicker("FROMDATE",$postFROMDATE); ?> </td>
                              <td colspan="1"; >&nbsp;</td>   
                           </tr>
                           <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                              <td Colspan="5">&nbsp</td>
                           </tr>
                            <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            	<td >&nbsp</td>
                              <td class=det3; >End Date </td>
                              <td colspan="2"><?php DatePickerElement::getDatePickerLibs(); DatePickerElement::getDatePicker("ENDDATE",$postENDDATE); ?> </td>
                              <td colspan="1"; >&nbsp;</td>   
                           </tr>                          
                           <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                              <td Colspan="5">&nbsp</td>
                           </tr>
                            <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            	<td >&nbsp</td>
                              <td class=det3; >Discount Type </td>
                              <td colspan="2"; style="text-align:left;"><?php $lableArr = array('Percentage','Amount');
                                                                        $valueArr = array('1','2');
                                                                        BasicSelectElement::buildGenericDD('DISTYPE', $lableArr,$valueArr, $postWBSTATUS, "N", "N", null, null, null);?></td>
                              <td colspan="1"; >&nbsp;</td>   
                           </tr>                       
                           <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                              <td Colspan="5">&nbsp</td>
                           </tr>
                           <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            	<td >&nbsp</td>
                              <td class=det3; >Discount Value</td>
                              <td colspan="2"; style="text-align:left;"><input type="number" min="0" value="0" step="0.01" name="DISVAL" id="DISVAL" value =""</td> 
                              <td colspan="1"; >&nbsp;</td>   
                           </tr>  
                           <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                              <td Colspan="5">&nbsp</td>
                           </tr>
                           <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            	<td >&nbsp</td>
                              <td class=det3; >Minimum Invoiuce Value</td>
                              <td colspan="2"; style="text-align:left;"><input type="number" min="0" value="0" step="1" name="MINVAL" id="MINVAL" value =""</td> 
                              <td colspan="1"; >&nbsp;</td>   
                           </tr>  
                           <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                              <td Colspan="5">&nbsp</td>
                           </tr>
                           <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                              <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="insertform" value= "Insert New Discount">
                                                                 <INPUT TYPE="submit" class="submit" name="canform"   value= "Cancel"></td>
                           </tr>          
                           <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                              <td Colspan="5">&nbsp</td>
                           </tr>
                       </table>
                    </FORM>
	              </center> 

     <?php
   }
     if (isset($_POST['firstform'])) {
     	      if($postCUSTOMER <> 'Select a Customer') { ?>
                <center>
                    <FORM name='Select Action' method=post target=''>
                       <table width="720"; style="border:none">
                           <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                              <td class=head1 colspan="5"; style="text-align:center";>Manage Invoice Discounts</td>
                           </tr>
                           <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                              <td width="10%"; style="border:none">&nbsp</td>
                              <td width="30%"; style="border:none">&nbsp</td>
                              <td width="30%"; style="border:none">&nbsp</td>
                              <td width="20%"; style="border:none">&nbsp</td>
                              <td width="10%"; style="border:none">&nbsp</td>
                           </tr>
                           <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                              <td >&nbsp</td>
                              <td  class=det3 >Customer</td>
                              <td colspan="2"; style="text-align:left;"><?php echo trim(substr($postCUSTOMER,10,50)); ?></td> 
                              <td colspan="2"; >&nbsp;</td>
                            </tr>
                           <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                              <td Colspan="5"><input type="hidden" name="custname" value='<?php echo $postCUSTOMER; ?>'></td>
                           </tr>
                           <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                              <td Colspan="5">&nbsp</td>
                           </tr>
                           <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                              <td Colspan="5">&nbsp</td>
                           </tr>
                           <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                              <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="actionform" value= "Insert New Discount">
                                                                 <INPUT TYPE="submit" class="submit" name="canform"   value= "Cancel"></td>
                           </tr>          
                           <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                              <td Colspan="5">&nbsp</td>
                           </tr>  
                       </table>
                    </FORM>
	              </center>       	
                <?php    	
            } else {
              ?>
              <script type='text/javascript'>parent.showMsgBoxError('No Customer Selected - Try Again')</script> 
              <?php
              unset($_POST['firstform']);	  
           }
     }           
 
if(!isset($_POST['firstform']) && !isset($_POST['actionform']) && !isset($_POST['insertform'])) {
	
    $InvoiceDiscountDAO = new InvoiceDiscountDAO($dbConn);
    $cList = $InvoiceDiscountDAO->fetchCustomerList($principalId);
    
    $class = 'odd';    
    
    ?> 
    <center>
       <FORM name='Invoice Discounts' method=post action=''>
            <table width="720"; style="border:none">
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td class=head1 colspan="5"; style="text-align:center";>Manage Invoice Discounts</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td width="10%"; style="border:none">&nbsp</td>
                 <td width="30%"; style="border:none">&nbsp</td>
                 <td width="30%"; style="border:none">&nbsp</td>
                 <td width="20%"; style="border:none">&nbsp</td>
                 <td width="10%"; style="border:none">&nbsp</td>
               </tr>
               
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
               	  <td >&nbsp</td>
                  <td  class=det3 >Customer</td>
               	  <td colspan="2"; style="text-align:left;">
               	  	   <select name="CUSTOMER" id="CUSTOMER">
                           <option value="Select a Customer"><?php echo 'Select a Customer' ?></option>
                                 <?php foreach($cList as $row) { ?>
                                       <option value="<?php echo trim($row['CustID']) . '-' . trim($row['Customer'])  ; ?>"><?php echo $row['Customer']; ?></option>
                                 <?php } ?>
               	  	   </select>
               	  </td> 
               	  <td colspan="2"; >&nbsp;</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="5">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="5">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="5">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="firstform" value= "Get Customer List">
                                                             <INPUT TYPE="submit" class="submit" name="canform"   value= "Cancel"></td>
               </tr>          
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="5">&nbsp</td>
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