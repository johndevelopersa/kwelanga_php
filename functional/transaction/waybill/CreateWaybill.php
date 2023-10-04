<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    include_once($ROOT.$PHPFOLDER."properties/Constants.php");
    include_once($ROOT.$PHPFOLDER.'TO/SequenceTO.php');
    include_once($ROOT.$PHPFOLDER.'DAO/SequenceDAO.php');

    if (!isset($_SESSION)) session_start() ;
		file_put_contents($ROOT.$PHPFOLDER.'log/cwb.txt', "here", FILE_APPEND);

    $userUId     = $_SESSION['user_id'] ;
    $principalId = $_SESSION['principal_id'] ;
    $depotId     = $_SESSION['depot_id'] ;
      
    if (isset($_POST["DOCUMENTUID"])) $postDOCUMENTUID=$_POST["DOCUMENTUID"]; else $postDOCUMENTUID = '1'; 
      
    //Create new database object
    $dbConn = new dbConnect(); $dbConn->dbConnection();
     
    $sequenceDAO = new SequenceDAO($dbConn);
    $sequenceTO = new SequenceTO;
    $errorTO = new ErrorTO;
    $sequenceTO->sequenceKey=LITERAL_SEQ_WAYBILL;
    $sequenceTO->depotUId = $depotId;
    $sequenceTO->principalUId = $principalId;
    file_put_contents($ROOT.$PHPFOLDER.'log/cwb.txt', print_r($sequenceTO, TRUE), FILE_APPEND);   
    $result=$sequenceDAO->getSequence($sequenceTO,$seqVal);

    if ($result->type!=FLAG_ERRORTO_SUCCESS) {
       return $result;
    }
    $wayBillNumber  = $seqVal;
		                  
    include_once($ROOT.$PHPFOLDER."DAO/PostTransactionDAO.php");
    $postTransactionDAO = new PostTransactionDAO($dbConn);
    $rTO = $postTransactionDAO->setWaybillNumber($principalId, $postDOCUMENTUID, $wayBillNumber);
            
     if ($rTO->type==FLAG_ERRORTO_SUCCESS) {
            $dbConn->dbinsQuery("commit");
            return; 
     }  else {
            $dbConn->dbinsQuery("rollback");
 ?>
            <script type='text/javascript'>parent.showMsgBoxInfo('Way Bill Not Created')</script>    
 <?php   
           return; 
    }
