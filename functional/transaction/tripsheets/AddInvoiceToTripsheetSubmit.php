<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER."DAO/transactionDAO.php");
    include_once($ROOT.$PHPFOLDER."DAO/PostTransactionDAO.php");
    include_once($ROOT.$PHPFOLDER."properties/Constants.php");
    include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
    include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
    include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');
    include_once($ROOT.$PHPFOLDER.'elements/basicSelectElement.php');	
    
    if (!isset($_SESSION)) session_start() ;
      $userUId = $_SESSION['user_id'] ;
      $principalId = $_SESSION['principal_id'] ;
      $depotId = $_SESSION['depot_id'] ;

    
    
    $dbConn = new dbConnect();
    $dbConn->dbConnection();
    $PostTransactionDAO = new PostTransactionDAO($dbConn);
    
    $errorTO = new ErrorTO; 
    
//preset post vars...
    $postDOCNUMBER    = '';
    $postTSNUMBER     = '';
    $postPRINCIPALID  = '';
    $postUSERID       = '';    
    $postSTATUS = FLAG_STATUS_ACTIVE;
    CommonUtils::setPostVars();

     if ($postDOCNUMBER  == '') {
              $returnMessages=new ErrorTO;
              $returnMessages->type=FLAG_ERRORTO_ERROR;
              $returnMessages->description="No Document number entered<br><br>&nbsp;&nbsp;&nbsp;&nbsp;Please Try again<br>";
              print(CommonUtils::getJavaScriptMsg($returnMessages));
              return;
     }
     
     if ($postTSNUMBER == '') {
              $returnMessages=new ErrorTO;
              $returnMessages->type=FLAG_ERRORTO_ERROR;
              $returnMessages->description="No Tripsheet Number Entered<br><br>&nbsp;&nbsp;&nbsp;&nbsp;Please Try again<br>";
              print(CommonUtils::getJavaScriptMsg($returnMessages));
              return;
     }

     $TransactionDAO = new TransactionDAO($dbConn);
     $tsTO = $TransactionDAO->getInvoicesNotOnTripsheet($postPRINCIPALID, $postDOCNUMBER );
     
     if (count($tsTO) == 0){
              $returnMessages=new ErrorTO;
              $returnMessages->type=FLAG_ERRORTO_ERROR;
              $returnMessages->description="Document Not found <br><br>Please Check the number and Try Again.";
              print(CommonUtils::getJavaScriptMsg($returnMessages));
              return;
     }
    
     if ($tsTO[0]['tripsheet_number'] <> 0){
              $returnMessages=new ErrorTO;
              $returnMessages->type=FLAG_ERRORTO_ERROR;
              $returnMessages->description="Document already on a tripsheet <br><br>Please Check tripsheet Number  " . ($tsTO[0]['tripsheet_number']) . "<br>";
              print(CommonUtils::getJavaScriptMsg($returnMessages));
              return;
     }
     
     if ($tsTO[0]['tripsheet_number'] <> 0){
              $returnMessages=new ErrorTO;
              $returnMessages->type=FLAG_ERRORTO_ERROR;
              $returnMessages->description="Document already on a tripsheet <br><br>Please Check tripsheet Number  " . ($tsTO[0]['tripsheet_number']) . "<br>";
              print(CommonUtils::getJavaScriptMsg($returnMessages));
              return;
     }
     
     if (!in_array($tsTO[0]['document_status_uid'],array('76','77','78'))) {
              $returnMessages=new ErrorTO;
              $returnMessages->type=FLAG_ERRORTO_ERROR;
              $returnMessages->description="Incorrect Document Status for a Tripsheet <br><br>Current Document Status  -  " . ($tsTO[0]['document_status']) . "<br>";
              print(CommonUtils::getJavaScriptMsg($returnMessages));
              return;
     }
     
     $TransactionDAO = new TransactionDAO($dbConn);
     $tnTS = $TransactionDAO->getTripsheetNumbers($postPRINCIPALID, $postTSNUMBER );
     
     if (count($tnTS) == 0){
              $returnMessages=new ErrorTO;
              $returnMessages->type=FLAG_ERRORTO_ERROR;
              $returnMessages->description="Trip Sheet Not found <br><br>Please Check the number and Try Again.";
              print(CommonUtils::getJavaScriptMsg($returnMessages));
              return;
     }
     
     $returnTO = $PostTransactionDAO->setTripsheetDetails($tsTO[0]['dmUid'], $tnTS[0]['trip_transporter_uid'], $tnTS[0]['tripsheet_number'], $tnTS[0]['tripsheet_date'], $postUSERID, array($tsTO[0]['dmUid']));
     
     if($returnTO->type == FLAG_ERRORTO_SUCCESS){
     	        
     	        $returnTO = mysqli_query($dbConn->connection, "commit");
     	
     	        $returnMessages=new ErrorTO;
              $returnMessages->type=FLAG_ERRORTO_SUCCESS;
              $returnMessages->description="Document " . $postDOCNUMBER . " Successfully added to Tripsheet " . $tnTS[0]['tripsheet_number'] . "<BR><BR>";
              $returnMessages->description .= "<BR><a href=javascript:; onclick=window.open('" . HOST_SURESERVER_AS_NEWUSER.$PHPFOLDER . "functional/presentations/presentationManagement.php?TYPE=CUSTOMTS305&FINDNUMBER=" . $tnTS[0]['tripsheet_number'] ."','myDocumentProcessing','scrollbars=yes,width=750,height=600,resizable=yes');>[VIEW/PRINT TRIPSHEET]</a>";

              print(CommonUtils::getJavaScriptMsg($returnMessages));
              return;
     } else {
              $returnMessages = mysqli_query($dbConn->connection, "rollback");
              echo CommonUtils::getJavaScriptMsg($returnTO);
     }

?>