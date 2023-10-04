<?php
    include_once('ROOT.php'); 
    include_once($ROOT.'PHPINI.php');
    include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
    include_once('boxCaptureWarehouseReceiptsClass.php');
 		include_once($ROOT.$PHPFOLDER.'DAO/AgedStockDAO.php');
    include_once($ROOT.$PHPFOLDER."libs/GUICommonUtils.php");
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');    
    
    if (!isset($_SESSION)) session_start() ;
      $userUId     = $_SESSION['user_id'] ;
      $principalId = $_SESSION['principal_id'] ;
      

      //Create new database object
      $dbConn  = new dbConnect(); 
      $dbConn->dbConnection();
      $errorTO = new ErrorTO;
      
?>
<!DOCTYPE html>
<HTML>
   <HEAD>

		<TITLE>Simple Form</TITLE>

    <link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/kos_standard_screen.css' rel='stylesheet' type='text/css'>
		<link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/1_kwelanga.css' rel='stylesheet' type='text/css'>
		<script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/jquery.js"></script>
		<script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/dops_global_functions.js"></script>

    <style>
       table.box {border:collapse;
                  border: 2px solid; 
       	          border-color: #990000; 
      	          background:   #fcecec }     
    </style>

   </HEAD>
   <?php
    if(isset($_POST['CANFORM'])) { 
        return;	    
	  }
    if(isset($_POST['BACKF'])) { 
        unset($_POST['FIRSTFORM']);
        unset($_POST['NUMOFBOXES']);
        unset($_POST['NUMOFBOXES']);
        unset($_POST['BOXNUMBER']);
    }
    
    if(isset($_POST['SAVEBXNOS'])) {
    	
    	    if($_POST['DELBY'] <> 'Select Rep') {
    	    	
                 $AgedStockDAO = New AgedStockDAO($dbConn);
                 $errorTO = $AgedStockDAO->insertBoxReceiptRecord($_POST['DOCID'],
                                                                  $_POST['UVALUE'],
                                                                  $_POST['DELBY'],
                                                                  $_POST['FNOBOXES'],
                                                                  $_POST['SREFERENCE'],
                                                                  '',
                                                                  $userUId ,
                                                                  explode(",",$_POST['BOXLIST']));
                 unset($_POST['FIRSTFORM']);
                 unset($_POST['NUMOFBOXES']);
                 unset($_POST['BOXNUMBER']);
    	  
                 if($errorTO->type==FLAG_ERRORTO_SUCCESS)  { ?>
                     <script type='text/javascript'>parent.showMsgBoxInfo('Warehouse Reciept Saved Successfully')</script>
                    <?php	    	             
                 } else {?>
                     <script type='text/javascript'>parent.showMsgBoxError('Warehouse Reciept Capture Failed')</script>
                    <?php	
                    echo "<br>";
                    print_r($errorTO);    	                              	
                 	
                 }
                 unset($_POST['FIRSTFORM']);
                 unset($_POST['NUMOFBOXES']);
                 unset($_POST['NUMOFBOXES']);
                 unset($_POST['BOXNUMBER']); 
                 
    	    } else {        ?>
                <script type='text/javascript'>parent.showMsgBoxError('No Delivered By Selected - You must Start Again')</script>
                <?php	    	  
    	  
                unset($_POST['FIRSTFORM']);
                unset($_POST['NUMOFBOXES']);
                unset($_POST['NUMOFBOXES']);
                unset($_POST['BOXNUMBER']);    	
    	    }
   } 
    
if(isset($_POST['CAPTCONT'])) {	
      $prinId   = $_POST['PRINID'];
      $upliftNo = str_pad(trim($_POST['RDOCNUM']), 10, '0', STR_PAD_LEFT);     

      $AgedStockDAO = New AgedStockDAO($dbConn);
      $custdet = $AgedStockDAO->getBoxDetailsToUpdate($prinId, $upliftNo);	
      $captureWarehouseReceipt = new captureWarehouseReceipt();
      $a = $captureWarehouseReceipt->numOfBoxes($custdet[0]['name'],
                                                $custdet[0]['prinUid'],
                                                $custdet[0]['invoice_date'], 
                                                $custdet[0]['document_number'],
                                                $custdet[0]['docUid'], 
                                                $custdet[0]['deliver_name']);	

      unset($_POST['NUMOFBOXES']);
      unset($_POST['BOXNUMBER']);    	

} 
if(isset($_POST['CAPTCANCEL'])) {	
      unset($_POST['FIRSTFORM']);
      unset($_POST['NUMOFBOXES']);
      unset($_POST['BOXNUMBER']);
      unset($_POST['CAPTCONT']);     	
}


// ***********************************************************************************************************************************************    

    if (isset($_POST['FIRSTFORM']) && !isset($_POST['NUMOFBOXES'])) {
    	      if (isset($_POST["UPLIFTNO"])) $postUPLIFTNO  = (test_input($_POST["UPLIFTNO"])); else $postUPLIFTNO = '';
   	        if (strpos($postUPLIFTNO,'-') != FALSE) {	
                   $prinId   = substr($postUPLIFTNO,0,strpos($postUPLIFTNO,'-'));
                   $upliftNo = str_pad(trim(substr($postUPLIFTNO,strpos($postUPLIFTNO,'-') +1 ,10)), 10, '0', STR_PAD_LEFT);
   	        	
                   $AgedStockDAO = New AgedStockDAO($dbConn);
                   $custdet = $AgedStockDAO->getBoxDetailsToUpdate($prinId,$upliftNo);
                   if(count($custdet) <> 0) {
                   	
                         if($custdet[0]['document_status_uid'] == DST_UNACCEPTED) {
                              $captureWarehouseReceipt = new captureWarehouseReceipt();
                              $a = $captureWarehouseReceipt->captError($prinId,
                                                                       $upliftNo,
                                                                       0,
                                                                       "Aged Stock List has not been printed", 
                                                                       "Continue with Warehouse Receipt");
                          }  else {
                              $AgedStockDAO = New AgedStockDAO($dbConn);
                              $custdet = $AgedStockDAO->getBoxDetailsToUpdate($prinId, $upliftNo);	
                              $captureWarehouseReceipt = new captureWarehouseReceipt();
                              $a = $captureWarehouseReceipt->numOfBoxes($custdet[0]['name'],
                                                                        $custdet[0]['prinUid'],
                                                                        $custdet[0]['invoice_date'], 
                                                                        $custdet[0]['document_number'],
                                                                        $custdet[0]['docUid'], 
                                                                        $custdet[0]['deliver_name']);	
                              unset($_POST['NUMOFBOXES']);
                              unset($_POST['BOXNUMBER']);    	
                          }
                    } else {
                                    $conCapt = "N";	?>
                                    <script type='text/javascript'>parent.showMsgBoxError('Error! Document not Found or Already Receipted')</script>
                                    <?php	
                                    unset($_POST['FIRSTFORM']);
                                    unset($_POST['NUMOFBOXES']);
                                    unset($_POST['BOXNUMBER']);  
                    } 
            }  else 	{ ?>
                    <script type='text/javascript'>parent.showMsgBoxError('Error! Please Enter Correct Uplift Number Number')</script>
                    <?php	
                    unset($_POST['FIRSTFORM']);
                    unset($_POST['NUMOFBOXES']);
                    unset($_POST['BOXNUMBER']);  
   	       }
    }
// ***********************************************************************************************************************************************    
    if (isset($_POST['NUMOFBOXES']) || isset($_POST['BOXNUMBER']) ) {
    	
          if(isset($_POST['NOBOXES'])) {
                  $boxTot = $_POST['NOBOXES'];
                  $boxArray = Array();
          } elseif(!isset($_POST['NOBOXES'])) {
                  $boxTot = $_POST['FNOBOXES'];
                  $prUid  = $_POST['PRINUID'];
                  $boxArray = explode(',', $_POST['BOXLIST']);
          } else {
                  $boxTot = 0;
                  $prUid  = $_POST['PRINUID'];
          }
          
          if(!is_numeric($boxTot) && ($boxTot) <= 0) { ?>
    	    	      <script type='text/javascript'>parent.showMsgBoxError('No of Boxes Blank or Invalid  - Start Again')</script>
                  <?php	
                  unset($_POST['NUMOFBOXES']);
                  unset($_POST['FINALFORM']);
          } else {
                  if(isset($_POST['BOXNUMBER']) || count($boxArray) > 0) {
                	
                     if (isset($_POST["BOXNUMBER"])) $postBOXNUMBER = (test_input($_POST["BOXNUMBER"])); else $postBOXNUMBER = '';
                          if($postBOXNUMBER <> '') {
        	    	                 $AgedStockDAO = New AgedStockDAO($dbConn);
                                 $dupBox = $AgedStockDAO->checkDupBoxNo($principalId, $postBOXNUMBER);                          	  
           	    	                if(count($dupBox) == 0) { 
           	    	                	   if(!in_array($postBOXNUMBER, $boxArray)) {
           	    	                           $boxArray[] = $postBOXNUMBER; 
                                       } else { ?>
                                             <script type='text/javascript'>parent.showMsgBoxError('Box Number already Captured<BR><BR>Close before Continuing')</script>
                                            <?php	
           	    	                     } 
           	    	                } else {?>
                                             <script type='text/javascript'>parent.showMsgBoxError('Box Number has been used<BR><BR>Close before Continuing')</script>
           	    	                           <?php           	    	                	
           	    	                }
           	              } else { ?>
                              <script type='text/javascript'>parent.showMsgBoxError('Box Number Blank<BR><BR>Close before Continuing')</script>
                              <?php	
           	              }	      
                 }
            	   $captureWarehouseReceipt = new captureWarehouseReceipt();
                 $a = $captureWarehouseReceipt->finalForm($_POST['PRIN'],
                                                          $_POST['PRINUID'], 
                                                          $_POST['DOCDATE'], 
                                                          $_POST['DOCNUM'],
                                                          $_POST['DOCID'], 
                                                          $_POST['STORE'],
                                                          $boxTot,
                                                          implode(',', $boxArray));
          }
    }	 
// ***********************************************************************************************************************************************    

    if (!isset($_POST['FIRSTFORM']) && !isset($_POST['NUMOFBOXES']) && !isset($_POST['BOXNUMBER']) && !isset($_POST['CAPTCONT'])) {
    	
    	// 1. If FIRST FORM and NO OF BOXES is not set, proceed to first form. 
        $captureWarehouseReceipt = new captureWarehouseReceipt();
         $a = $captureWarehouseReceipt->firstform($postUPLIFTNO);              //  firstform = function contained in the class

    } ?> 
</HTML>

<?php
 function test_input($data) {

  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
    
  return $data;
 }
 ?> 

  