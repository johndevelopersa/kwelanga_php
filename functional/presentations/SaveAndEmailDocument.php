<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'libs/BroadcastingUtils.php');
include_once($ROOT.$PHPFOLDER."functional/extracts/daily/extractController.php");
include_once($ROOT.$PHPFOLDER.'TO/PostingDistributionTO.php');
include_once($ROOT.$PHPFOLDER."DAO/TransactionDAO.php");
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');

class SaveAndEmailDocument extends extractController  {

    public function SaveAndMail ($userId, $outputTyp, $psmUid, $seqFilename, $prinnam, $docmastid, $DocumentDiscription, $principalUid ) {

        if (!isset($dbConn)) {
            $dbConn = new dbConnect();
            $dbConn->dbConnection();
        }
        $recipientsCheckCount = 0;  //check if atleast one distribution item was posted, incase of invalid recipients

        // Get Customer Abbreviation

        $dResult ='';

        if($psmUid <> '') {
            $transactionDAO = new TransactionDAO($dbConn);
            $sn = $transactionDAO->principalShortName(mysqli_real_escape_string($dbConn->connection, $psmUid));
            $prinAbr = trim($sn[0]['short_name']);
        } else {
            $prinAbr = '';
        }
        // Get User Email Address
        $eAr=Array();

        if (substr($outputTyp,3,4) == "Cust") {
            $transactionDAO = new TransactionDAO($dbConn);
            $eAr = $transactionDAO->getCustomerEmail(mysqli_real_escape_string($dbConn->connection, $psmUid));       $firstMailSent = 'N';

            foreach($eAr as $mailRow) {
                if($firstMailSent == 'N') {
                    if($eAr[0]['EmailAddress'] == NULL) {
                        $transactionDAO = new TransactionDAO($dbConn);
                        $eAr = $transactionDAO->getUserEmail($userId);
                        if($eAr[0]['EmailAddress'] == NULL){
                            echo "No User Email Address <br>";
                            $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
                            $this->errorTO->description = "Email Failure";
                            return $this->errorTO;
                        }
                        echo "No Customer Email Address - Email sent to Self". "<br><br>" ;
                    }
                    $SendMail = $this->SetUpDistribution($prinnam, $DocumentDiscription, $docmastid, $seqFilename, $eAr[0]['EmailAddress'] , $prinAbr);
                    $firstMailSent = 'Y';
                    $dResult = $dResult . $eAr[0]['EmailAddress'] . "    ";
                }
                if($eAr[0]['NextEmailAdress'] <> NULL) {
                    $SendMail = $this->SetUpDistribution($prinnam, $DocumentDiscription, $docmastid, $seqFilename, $eAr[0]['NextEmailAdress'] , $prinAbr);
                    $dResult = $dResult . '   ' . $eAr[0]['NextEmailAdress'];
                }
            }
        }
        if (substr($outputTyp,3,4) == "Self") {
            $transactionDAO = new TransactionDAO($dbConn);
            $eAr = $transactionDAO->getUserEmail($userId);
            if(count($eAr)==0){
                echo "No User Email Address <br>";
                $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
                $this->errorTO->description = "Email Failure";
                return $this->errorTO;
            }
            $SendMail = $this->SetUpDistribution($prinnam, $DocumentDiscription, $docmastid, $seqFilename, $eAr[0]['EmailAddress'], $prinAbr );
            $dResult = $eAr[0]['EmailAddress'];

        }
        return $dResult;
    }
// ******************************************************************************************************************************************
    function SetUpDistribution($prinnam, $DocumentDiscription, $docmastid, $attachmentPath, $destEmail, $prinAbr )  {

        $dResult = '';
        $recipientsCheckCount = 0;

        // SETUP DISTRIBUTION
        $postingDistributionTO = new PostingDistributionTO;
        $postingDistributionTO->DMLType = "INSERT";
        $postingDistributionTO->deliveryType = BT_EMAIL;
        $postingDistributionTO->subject = trim($prinnam) . ' ' . $prinAbr .' ' . trim($DocumentDiscription)  . '  ' . trim($docmastid) ;
        $postingDistributionTO->body = 'Attached is your '.trim($DocumentDiscription). ' From '. trim($prinnam) ;
        $postingDistributionTO->setAttachmentFileAsS3Uri(S3_BUCKET_NAME, $attachmentPath);

        $postingDistributionTO->destinationAddr = $destEmail;
        $postDistributionDAO = new PostDistributionDAO($this->dbConn);
        $dResult = $postDistributionDAO->postQueueDistribution($postingDistributionTO);

        if ($dResult->type!=FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->type=FLAG_ERRORTO_ERROR;
            $this->errorTO->description = get_class($this)." Extract distribution queue failed for for nr.UID {$recipientUId} and Recipient/Contact: '{$re}'.";
            BroadcastingUtils::sendAlertEmail("System Error", $this->errorTO->description, "Y", true);
            return $this->errorTO;
        } else {
            $recipientsCheckCount++;  //successful
            $this->dbConn->dbinsQuery("commit");
        }
        return ($destEmail);
    }
// ******************************************************************************************************************************************

}
