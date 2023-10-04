 <?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER."properties/Constants.php");
    include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
    include_once($ROOT.$PHPFOLDER."libs/CommonUtils.php");
    include_once($ROOT.$PHPFOLDER.'DAO/RvlManageDAO.php'); 
    include_once('selectAgedStockAdviceByStoreScreens.php');     
    
    if (!isset($_SESSION)) session_start() ;
      $userUId     = $_SESSION['user_id'] ;
      $principalId = $_SESSION['principal_id'] ;
      $systemId    = $_SESSION["system_id"];
      $systemName  = $_SESSION['system_name'];
      $depotId     = $_SESSION['depot_id'] ;

      //Create new database object
      $dbConn = new dbConnect(); 
      $dbConn->dbConnection();
      $errorTO = new ErrorTO;

?>  
<!DOCTYPE html>
<HTML>
	<HEAD>

		<TITLE>Principal - Warehouse Selection</TITLE>

		<link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/1_kwelanga.css' rel='stylesheet' type='text/css'>
		<link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/kos_standard_screen.css' rel='stylesheet' type='text/css'>
		<script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/jquery.js"></script>
		<script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/dops_global_functions.js"></script>

    <style>
      td.head1 {font-weight:bold;
                font-size:17px;text-align:left; 
                font-family: Calibri, Verdana, Ariel, sans-serif; }

      td.head2 {font-weight:normal;
                font-size:15px;text-align:left; 
                font-family: Calibri, Verdana, Ariel, sans-serif; }
                
      td.det1  {border-style:none; 
                text-align: left; 
                font-weight: bold; 
                font-size: 13px; }

      td.det2  {border-style:none; 
                text-align: left; 
                font-weight: normal; 
                font-size: 12px; }    	
    	</style>

		</HEAD>
		    <body>   
		    <?php

             if (isset($_POST['CANFORM'])) {
                 return;    
             }

         if (isset($_POST['FINISH'])) {
             	
             if(count($_POST['SELECT']) <> 0) {
             	
             	    if(isset($_POST['MAILSELECT']) && $_POST['REP'] <> 'Select Rep') {
     	                 foreach($_POST['SELECT'] as $prow) {
                             $docUid = substr($prow,10,10);           
                             $oType = 'F';
                             $prinUid = $principalId;
                        
                             $lpf = loadPrintFile($docUid, $oType, $prinUid);
     	                       // Mail file
     	                       
     	                       
     	                       if(isset($_POST['MAILSELECT'])) {
     	                              $PresentationDAO = new PresentationDAO($dbConn);
                                    $mDetails = $PresentationDAO->getMailDetails($principalId, substr($prow,0,10), $_POST['REP']) ; 
                        
                                    // SETUP DISTRIBUTION
                                    $postingDistributionTO = new PostingDistributionTO;
                                    $postingDistributionTO->DMLType = "INSERT";
                                    $postingDistributionTO->deliveryType = BT_EMAIL;
                                    $postingDistributionTO->subject = trim($mDetails[0]['Principal']) . '-'.trim($mDetails[0]['deliver_name']) .' -' . trim($mDetails[0]['deliver_name']) .' ' .  ltrim($mDetails[0]['document_number'],'0');
                                    $postingDistributionTO->body = 'Attached is Aged Stock form' ; 
                                    $postingDistributionTO->attachmentFile = '/ftp/rvl/UpliftDocuments/R-'.trim($mDetails[0]['deliver_name']) .' - ' . ltrim($mDetails[0]['document_number'],'0') .'.pdf';
                                    $postingDistributionTO->destinationAddr = trim($mDetails[0]['email_addr']);
                                    $postDistributionDAO = new PostDistributionDAO($dbConn);
                                    $dResult = $postDistributionDAO->postQueueDistribution($postingDistributionTO);
                        
                                    if ($dResult->type=FLAG_ERRORTO_SUCCESS) {
              	        
                                              $PresentationDAO = new PresentationDAO($dbConn);
                                              $mDetails = $PresentationDAO->setMailedStatus($principalId, substr($prow,0,10)) ; 
              	        
                                              $recipientsCheckCount++;  //successful
                                              $dbConn->dbinsQuery("commit") ;                 
                                    }
     	                       	
     	                       }

                        }             	
             	    } else { ?>
                      <script type='text/javascript'>parent.showMsgBoxError('Mail Selected but No Rep selected')</script> 
                      <?php 
                      unset($_POST['FINISH']);         
                      unset($_POST['SUBMITFILTER']);         
                  }
             }  else { ?>
                      <script type='text/javascript'>parent.showMsgBoxError('No Documents Selected for Printing')</script> 
                      <?php 
                      unset($_POST['FINISH']);         
                      unset($_POST['SUBMITFILTER']);         
             }

         
         if($recipientsCheckCount > 0) {  ?> 
              <script type='text/javascript'>parent.showMsgBoxInfo('<?php echo $recipientsCheckCount ;?> - Uplift Instructions Mailed Successfully')</script> 
               <?php 
               unset($_POST['FINISH']);         
               unset($_POST['SUBMITFILTER']);         
         }  
         }
             
             if(isset($_POST["SUBMITFILTER"])) {
             	
                    $startDate   = $_POST['FROMDATE'];
                    $endDate     = $_POST['TODATE'];
                    $repName     = trim($_POST['WREP']); 
                    $stor        = trim($_POST['WSTORE']); 
                    $docNo       = trim($_POST['WDOCNO']);
                    $area        = trim($_POST['WAREA']);
                    
                   $rvlManagmentDAO = New RvlManagmentDAO($dbConn);
                   $mfDD = $rvlManagmentDAO->getAgedStockStoreList($startDate, $endDate, $principalId, $repName, $stor, $docNo,$area );
                   
                   $rvlManagmentDAO = New RvlManagmentDAO($dbConn);
                   $repl = $rvlManagmentDAO->getRepListNew($principalId) ;                  
                   

             }
             $selectAgedStockAdviceByStoreScreens = new selectAgedStockAdviceByStoreScreens();
             $selectAgedStockAdviceByStoreScreens->selectAgedStockStore($mfDD, $repl)  ; ?>
		    </body>
 
    </body>
</HTML>

<?php
 function test_input($data) {

  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  
  return $data;
 } 
  function loadPrintFile($docUid, $oType, $prinUid) {
  	
  	global $ROOT; global $PHPFOLDER;?>
  	<script>
          printWindow = window.open('<?php echo $ROOT.$PHPFOLDER ?>functional/presentations/view/rvl_uplift_instructions.php?PRINCIPALID=<?PHP echo $prinUid; ?>&OUTPUTTYP=<?PHP echo $oType; ?>&DOCMASTID=<?PHP echo $docUid; ?>');
          printWindow = window.close;
   	</script>
   	<?php
  
    return;
  }

?>



