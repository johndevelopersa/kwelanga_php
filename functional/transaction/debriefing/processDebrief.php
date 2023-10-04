<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER."properties/Constants.php");
    include_once($ROOT.$PHPFOLDER."elements/datePickerElement.php");
    include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
    include_once($ROOT.$PHPFOLDER."libs/CommonUtils.php");
    include_once($ROOT.$PHPFOLDER."DAO/deBriefingDAO.php");			
    include_once("processDebriefScreens.php");
	  
     if (!isset($_SESSION)) session_start() ;
     $userUId     = $_SESSION['user_id'] ;
     $principalId = $_SESSION['principal_id'] ;
     $depotId     = $_SESSION['depot_id'] ;
                
//Create new database object
$dbConn = new dbConnect(); 
$dbConn->dbConnection();
$errorTO = new ErrorTO;

?>
<!DOCTYPE html>
<html style="height:100%;width:100%;">
  <head>

		<TITLE>Document Selection</TITLE>
    <link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/1_kwelanga.css?v=1' rel='stylesheet' type='text/css'>
    <link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/kos_standard_screen.css' rel='stylesheet' type='text/css'>
    <?php DatePickerElement::getDatePickerLibs(); ?>
	 <LINK href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/1_default.css' rel='stylesheet' type='text/css'>
    
    <script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/jquery.js"></script>
    <script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/dops_global_functions.js"></script>

    <style>

      .scan-input {
         height: 42px !important;
         border-radius: 15px !important;
         padding-left: 50px !important;
         width:100% !important;
      }
      .scan-input:focus {
         outline:none !important;
         border:3px solid black;
      }

    </style>

  </head>
  
  <body>
  	  <?php
  	  
  	  if(isset($_POST['PROCREDELIVER'])) {
  	  	
  	  	    // Remove from TripSheet
            $rmTripDoc = new deBriefingDAO($dbConn);
            $errorTO   = $rmTripDoc->removeReDeliveryFromTripSheet($_POST['DOCUID'], 
                                                                   $_POST['REASON'], 
                                                                   $userUId, 
                                                                   $_POST['TRIPUID'],
                                                                   $_POST['DEPUID'],
                                                                   $_POST['TRIPNUM']);  	  	    
            if ($errorTO->type!=FLAG_ERRORTO_SUCCESS) {?>
                          <script type='text/javascript'>parent.showMsgBoxError("Bomb Out - Contact Kwelanga = Error PD002<br><BR>")</script> 
                          <?php
                          return;	
  	  	    }  	  	    
  	  	    // Set RD Status
  	  	    
  	  	    $rmTripDoc = new deBriefingDAO($dbConn);
            $errorTO   = $rmTripDoc->setReDeliverStatus($_POST['DOCUID'],
                                                        $userUId,
                                                        $_POST['DAMUID'],
                                                        $_POST['REASON'],
                                                        $_POST['TRIPNUM']);
            if ($errorTO->type!=FLAG_ERRORTO_SUCCESS) {?>
                          <script type='text/javascript'>parent.showMsgBoxError("Bomb Out - Contact Kwelanga = Error PD001<br><BR>")</script> 
                          <?php
                          return;	
  	  	    } 	  	    
  	  	    // Put Stock to RD warehouse
  	  	    
            $rDelProd = new deBriefingDAO($dbConn);
  	  	    $rDprod   = $rDelProd->getReDeliverDetail($_POST['DOCUID'], $userUId);
  	  	    
  	  	    $prdList   = '';
  	  	    $storeDet = '';
  	  	    
            $arrStr = '';
            $detArr = array();
            
            $detArr['username'] = "APITest";
            $detArr['password'] = "yF!+KssJr-Ca8yM=NX";
            
            $detArr['requireddata']   = "postReDelArrival" ; 
            $detArr['principalId']    = $rDprod[0]['prinUid'];
            $detArr['orderReference'] = $rDprod[0]['document_number'] ;
            $detArr['orderDate']      = Date('Y-m-d');            
            $detArr['captureDate']    = Date('Y-m-d'); 
            $detArr['reDelWarehouse'] = $rDprod[0]['redelivery_warehouse'] ;
            $detArr['userUid']        = $rDprod[0]['userUid'] ;
            
            $prodArr = array(); 
            $detailArr  = array();
            $storeDet = '';             
            
  	  	    foreach($rDprod AS $prow) {
                  if($storeDet != $prow['ddUid'] && $storeDet != '') {
  	  	    	    	   	   $detailArr[] = $prodArr;
  	  	    	    	   	   $prodArr = array();
  	  	    	    }
  	  	    	    $storeDet = $prow['ddUid'];
  	  	          $prodArr['productUid'] =  $prow['product_uid'];
  	  	          $prodArr['quantity'] =  $prow['document_qty'];
  	  	    }
  	  	    $detailArr[] = $prodArr;
  	  	    
  	  	    $detArr['detailLines'] = $detailArr;	  ;
  	  	    
  	  	    $data = $detArr;

            $payload = json_encode($data);
            // echo "<br>";
            // echo "Start";
            // echo "<pre>";
            // print_r(json_decode($payload, TRUE));

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/m/v.php");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
            curl_setopt($ch, CURLOPT_VERBOSE, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $result = curl_exec($ch);

            $curlDebug = curl_getinfo($ch);

            // echo "<br><pre>";
            // print_r(curl_getinfo($ch));

            if (curl_errno($ch)) {
                    $error_msg = curl_error($ch);
                    print_r($error_msg);
            }

            $resArr = json_decode($result, TRUE);

            // close cURL resource, and free up system resources
            curl_close($ch);
            
            if($resArr['resultStatus'] != FLAG_ERRORTO_SUCCESS) {?>
                   <script type='text/javascript'>parent.showMsgBoxError("Bomb Out - Contact Kwelanga = Error PD004<br><br>")</script> 
                   <?php
                   return;
            } else {?>
                   <script type='text/javascript'>parent.showMsgBoxInfo("Successfully Created Re Delivery Document<br><br>")</script> 
                   <?php
            }                                                   
  	  }
 
  	  if(isset($_POST['REDELIVER'])) {
  	  	
  	  	      $docNumber = test_input($_POST['DOCUMENTNO']);
  	  	
  	  	      $dashPos = strpos($docNumber,'-');
  	  	      
  	  	      if($dashPos > 0) {
  	  	      	     $prinUid = trim(substr($docNumber,0,$dashPos));
  	  	      	     $docNo   = trim(substr($docNumber,$dashPos + 1 ,10));
  	  	      	     
  	  	             $getDoc = new deBriefingDAO($dbConn);
  	  	             $rDoc   = $getDoc->getReturnDocument($prinUid, str_pad(ltrim($docNo,'0'),8,"0",STR_PAD_LEFT));
  	  	             
  	  	             if(count($rDoc) != 0) {
  	  	             	
  	  	             	      if($rDoc[0]['buyer_document_status_uid'] !=   DT_REDELIVERY_INVOICE) {
  	  	             	
  	  	                         $getRcList = new deBriefingDAO($dbConn);
  	  	                         $rClist   = $getDoc->getReasonList();  	  	             	 
  	  	             	 
  	  	                         $scrVar = new processDebriefScreens();
                                 $a = $scrVar->showSelectedDocument(json_encode($rDoc), json_encode($rClist));   
                            } else {?>
                                 <script type='text/javascript'>parent.showMsgBoxError("Document Already on Re Delivery <BR><BR> Check that Document is on a TripSheet<BR>")</script> 
                                 <?php
                                 unset($_POST['REDELIVER']);
  	  	                    }  	  	             	
  	  	             	
  	  	             } else {?>
                          <script type='text/javascript'>parent.showMsgBoxError("Document Number Not Found <BR><BR> Check that Document is on a TripSheet<BR>")</script> 
                          <?php
                          unset($_POST['REDELIVER']);
  	  	             }
  	  	      	     
  	  	      	     
  	  	      	     
  	  	      } else { ?>
                    <script type='text/javascript'>parent.showMsgBoxError("Document Number Format Wrong<BR><BR> Must be 'Principal' - 'Document Number'<BR>")</script> 
                    <?php
                    unset($_POST['REDELIVER']);
  	  	      }	  	
  	  }
  	  
  	   
  	  if(!isset($_POST['REDELIVER'])) {
            $scrVar = new processDebriefScreens();
            $a = $scrVar->getDocumentNumber();   
  	  } 	
      ?>
      
      
      
  </body>

    <?php  
 function test_input($data) {

      $data = trim($data);
      $data = stripslashes($data);
      $data = htmlspecialchars($data);
      if($data=='') { $data = 0; } 
    
      return $data;  
}      
/*  

<body style="background:url('<?php echo $DHTMLROOT.$PHPFOLDER ?>images/scan-background.jpg'); background-repeat:no-repeat; background-size:100% 100%; width:100%;height:100%;">

<div style="background:rgba(255,255,255,0.9); width:100%;height:100%;">

<table class="tableReset" style="width:100%;">
   </tr>
      <td>&#160;</td>
      <td style="width:30%;">
         <p>Please enter the Invoice Document Number, or use the scanner</p>

      </td>
      <td>&#160;</td>
   </tr>
</table>

</div>
*/