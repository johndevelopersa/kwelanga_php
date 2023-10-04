<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');  
    include_once ($ROOT.$PHPFOLDER.'functional/maintenance/simpleFormClass.php');
    include_once($ROOT.$PHPFOLDER. 'DAO/AgedStockDAO.php');
    include_once($ROOT.$PHPFOLDER.'DAO/PostAgedStockDAO.php');
    include_once($ROOT.$PHPFOLDER.'TO/AgedStockTO.php');
		    
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

		<link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/1_kwelanga.css' rel='stylesheet' type='text/css'>
		<script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/jquery.js"></script>
		<script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/dops_global_functions.js"></script>

    <style>
      td.head1 {font-weight:normal;
                font-size:20px;text-align:left; 
                font-family: Calibri, Verdana, Ariel, sans-serif; 
                padding: 0 150px 0 150px }
                
      td.det1  {border-style:none; 
                text-align: left; 
                font-weight: bold; 
                font-size: 13px;
                padding: 0 150px 0 150px  }
                
      table.box {border:collapse;
      	         border: 2px solid; 
      	         border-color: #990000; 
      	         background: #fcecec }          
    	
    	</style>

   </HEAD>
      <body>
<?php

     if (isset($_POST["INVOICE"])) $postINVOICE  = ($_POST["INVOICE"]); else $postINVOICE = ''; 
     
     $cBox   = $_POST['cBOX'];
     $docUid = $_POST['DOCUID'];
     $docNum = $_POST['DOCNUM'];
     
     if (isset($_POST['finishform'])) {
     	      $docNum = $_POST['DOCNO'];
            $cBox   = $_POST['SCBOX'];
            $x=0;
             $continueCapture= 'N';
            for ($x = 0; $x <= count($_POST['detID']); $x++) {
                  $AgedStockDAO = new AgedStockDAO($dbConn);
                  $errorTO = $AgedStockDAO->validateUpliftDetail(test_input($_POST['ULQTY'][$x]), test_input($_POST['DISQTY'][0]), test_input($_POST['RFQTY'][$x]), test_input($_POST['DAMAGES'][$x]), test_input($_POST['DAMAGESB'][$x]), test_input($_POST['DAMAGESC'][$x])) ;  
                  
                if ($errorTO->type!=FLAG_ERRORTO_SUCCESS) { ?>
                     <script type='text/javascript'>parent.showMsgBoxError('<?php echo $errorTO->description; ?>')</script> 
                     <?php
                      $continueCapture= 'Y';
                      $_POST['finishform'];
                      break;
                }
            }
            
            // Check Hash Total
            $capturedTotal = 0;
            for ($x = 0; $x <= count($_POST['detID']); $x++) {
            	  $capturedTotal = $capturedTotal +  test_input($_POST['ULQTY'][$x]);
            }
            if($capturedTotal <> test_input($_POST['hashT'])) { ?>
                     <script type='text/javascript'>parent.showMsgBoxError('Uplift Total Not Equal to Hash Total')</script> 
                     <?php
                     $continueCapture= 'Y';
                     $_POST['finishform'];
            }	
            if($continueCapture == 'N') {
                  
                  $upliftUid    = $_POST['DOCMID'];
                  $upliftNumber = $_POST['DOCNO'];
                  $postBOXQTY   = $_POST['SCBOX'];
                  $uReference   = test_input($_POST['UREF']);
                  $uwarehouse   = $_POST['WAREHOUSE'];
                  $dstat        = $_POST['DOCSTAT'];
                  
                  $AgedStockTO = new AgedStockTO ; 
                  $AgedStockTO->documentUid  = $upliftUid;  
                  $AgedStockTO->uplNumber    = $upliftNumber;
                  $AgedStockTO->boxes        = $postBOXQTY;
                  $AgedStockTO->principal    = $principalId;
                  $AgedStockTO->reference    = $uReference;
                  $AgedStockTO->warehouseUid = $uwarehouse;
                  
                  for ($x = 0; $x <= count($_POST['detID']); $x++) {
                       if(test_input($_POST['ULQTY'][$x])     + 
                          test_input($_POST['DISQTY'][$x])    + 
                          test_input($_POST['RFQTY'][$x])     + 
                          test_input($_POST['DAMAGES'][$x])   + 
                          test_input($_POST['DAMAGESB'][$x])  + 
                          test_input($_POST['DAMAGESC'][$x])  > 0) {   
   
                             $AgedStockTO->ddUid        = $_POST['detID'][$x];
                             $AgedStockTO->prodUid      = $_POST['prodID'][$x];
                             $AgedStockTO->prodCode     = $_POST['prodCode'][$x];
                             
                             if(trim(test_input($_POST['ULQTY'][$x])) <> '')  {$ulqty = trim(test_input($_POST['ULQTY'][$x])); } 
                             $AgedStockTO->found = $ulqty;
                             
                             if(trim(test_input($_POST['DISQTY'][$x])) <> '')  {$disqty = trim(test_input($_POST['DISQTY'][$x])); } 
                             $AgedStockTO->display = $disqty;                            
                             
                             if(trim(test_input($_POST['RFQTY'][$x])) <> '')  {$rfqty = trim(test_input($_POST['RFQTY'][$x])); } 
                             $AgedStockTO->extras = $rfqty;                            
                             
                             if(trim(test_input($_POST['DAMAGES'][$x])) <> '')  {$damages = trim(test_input($_POST['DAMAGES'][$x])); } 
                             $AgedStockTO->damages = $damages;                           

                             if(trim(test_input($_POST['DAMAGESB'][$x])) <> '')  {$damqtyb = trim(test_input($_POST['DAMAGESB'][$x])); } 
                             $AgedStockTO->damagesB = $damqtyb;
                             
                            if(trim(test_input($_POST['DAMAGESC'][$x])) <> '')  {$damqtyc = trim(test_input($_POST['DAMAGESC'][$x])); } 
                             $AgedStockTO->damagesC = $damqtyc;

                             $PostAgedStockDAO   = new PostAgedStockDAO($dbConn);
                             $errorTO   = $PostAgedStockDAO->InsertUpliftRecord($AgedStockTO);  
                             if ($errorTO->type!=FLAG_ERRORTO_SUCCESS) {?>
                                   <script type='text/javascript'>parent.showMsgBoxError('<?php echo $errorTO->description; ?><br>Contact Kwelanga Support')</script> 
                                   <?php
                                   return;
                             }
        
                             $PostAgedStockDAO   = new PostAgedStockDAO($dbConn);
                             $errorTO   = $PostAgedStockDAO->UpdateUpliftDocDetail($AgedStockTO);
                             if ($errorTO->type!=FLAG_ERRORTO_SUCCESS) {?>
                                   <script type='text/javascript'>parent.showMsgBoxError('<?php echo $errorTO->description; ?><br>Contact Kwelanga Support')</script> 
                                   <?php
                                   return;
                             }  
                             $PostAgedStockDAO   = new PostAgedStockDAO($dbConn);
                             $errorTO   = $PostAgedStockDAO->UpdateStockQuantity($AgedStockTO); 
                             if ($errorTO->type!=FLAG_ERRORTO_SUCCESS) {?>
                                   <script type='text/javascript'>parent.showMsgBoxError('<?php echo $errorTO->description; ?><br>Contact Kwelanga Support')</script> 
                                   <?php
                                   return;
                             } 
                        }
                        if($dstat == 'Accepted') {
                        	 $newstat = 70; 
                        } elseif ($dstat == 'Warehouse Receipt') { 
                        	 $newstat = 81; 
                        } 
                        $PostAgedStockDAO   = new PostAgedStockDAO($dbConn);
                        $errorTO   = $PostAgedStockDAO->UpdateUpliftStatus($postBOXQTY, $upliftUid , $newstat);
                        if ($errorTO->type!=FLAG_ERRORTO_SUCCESS) {?>
                               <script type='text/javascript'>parent.showMsgBoxError('<?php echo $errorTO->description; ?><br>Contact Kwelanga Support')</script> 
                                <?php
                                return;
                        }      
                        
                        $PostAgedStockDAO   = new PostAgedStockDAO($dbConn); 
                        $errorTO            = $PostAgedStockDAO->CheckStockTotals($AgedStockTO);
 
                         if ($errorTO->type!=FLAG_ERRORTO_SUCCESS) {?>
                               <script type='text/javascript'>parent.showMsgBoxError('<?php echo $errorTO->description; ?><br>Contact Kwelanga Support')</script> 
                                <?php
                                return;
                        }   
 
                        
                  } ?>
                      <script type='text/javascript'>parent.showMsgBoxInfo('Uplift Succesfully Captured')</script> 
                 <?php
                 unset($_POST['firstform']);
                 unset($_POST['receiptform']); 
                 unset($_POST['finishform']);
                 unset($continueCapture);
                 unset($_POST['CaptCont']); 	
            }
            
            $restarray = $restarraydq = $restarrayrf = $restarraydam = $restarraydamB = $restarraydamC  = array();

            $x=0;
            foreach($_POST['ULQTY'] as $ulrow) {
            	 $lineK = $_POST['detID'][$x];
               $restarray[$lineK]	 = $ulrow;
               $x++;
            }
            $x=0;
            foreach($_POST['DISQTY'] as $dqrow) {
            	 $lineK = $_POST['detID'][$x];
               $restarraydq[$lineK]	 = $dqrow;
               $x++;
            } 
            $x=0;           
            foreach($_POST['RFQTY'] as $srfrow) {
            	 $lineK = $_POST['detID'][$x];
               $restarrayrf[$lineK]	 = $srfrow;
               $x++;
            }
            $x=0;
            foreach($_POST['DAMAGES'] as $damrow) {
            	 $lineK = $_POST['detID'][$x];
               $restarraydam[$lineK]	 = $damrow;
               $x++;
            }
            $x=0;            
            foreach($_POST['DAMAGESB'] as $damBrow) {
            	 $lineK = $_POST['detID'][$x];
               $restarraydamB[$lineK]	 = $damBrow;
               $x++;
            }
            $x=0;            
            foreach($_POST['DAMAGESC'] as $damCrow) {
            	 $lineK = $_POST['detID'][$x];
               $restarraydamC[$lineK]	 = $damCrow;
               $x++;
            } 
     }

     if (isset($_POST['firstform']) && isset($_POST["INVOICE"])) {
             $simpleForm = new simpleForm();
             $whSr       = $simpleForm->receiptform($principalId, $postINVOICE);             
     } 
     
     if($_POST['receiptform']) {
         // Validate Boxes
         $AgedStockDAO = new AgedStockDAO($dbConn);
         $errorTO = $AgedStockDAO->validateWarehseBoxes($cBox, $docUid);
         
          if ($errorTO->type!=FLAG_ERRORTO_SUCCESS && $errorTO->description == 'Boxes not Numeric') { ?>
                 <script type='text/javascript'>parent.showMsgBoxError('<?php echo $errorTO->description; ?>')</script> 
                 <?php
                 unset($_POST['firstform']);
                 unset($_POST['receiptform']);
                 $cantcont = 'Y';
          }
          
          
          if(!isset($cantcont)) {
               if ($errorTO->type!=FLAG_ERRORTO_SUCCESS && $errorTO->description != 'Boxes not Numeric') {                // Test for == Boxes
                    $simpleForm = new simpleForm();
                    $a = $simpleForm->receiptError($cBox, $docNum);
               } else {
                    $continueCapture= 'Y';
               }
          }
     }

     if(isset($_POST['CaptCont'])) {
         $docNum = $_POST['RDOCNUM'];
         $cBox   = $_POST['RBOX'];
         $continueCapture = 'Y';
     }
     
     if(isset($_POST['CaptCancel'])) {
          unset($_POST['firstform']);
          unset($_POST['receiptform']); 
          unset($_POST['finishform']);
          unset($continueCapture);
          unset($_POST['CaptCont']);
     }
     
     if($continueCapture == 'Y'){
          $simpleForm = new simpleForm();
          $whSr = $simpleForm->upliftDetailCapture($principalId, 
                                                   $docNum, 
                                                   $cBox, 
                                                   $restarray,
                                                   $restarraydq,
                                                   $restarrayrf,
                                                   $restarraydam,
                                                   $restarraydamB,
                                                   $restarraydamC );
     }
      
     if (!isset($_POST['firstform']) && !isset($_POST['receiptform']) && !isset($_POST['CaptCont']) && !isset($continueCapture)) {
         $simpleForm = new simpleForm();
         $a = $simpleForm->firstform();             	
     }

?>

      </body>       
</HTML>

<?php
 function test_input($data) {

  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  if($data=='') { $data=0; } 
    
  return $data;
 }
 ?> 
