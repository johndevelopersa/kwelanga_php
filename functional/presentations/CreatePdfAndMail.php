<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'libs/BroadcastingUtils.php');
include_once($ROOT.$PHPFOLDER.'TO/PostingDistributionTO.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER."DAO/MailedInvoicesDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/PostDistributionDAO.php");
include_once($ROOT.$PHPFOLDER.'libs/storage/Storage.php');

// Get Mailed invoices recipients from principal contacts

if (!isset($_SESSION)) session_start() ;
      $userUId     = $_SESSION['user_id'] ;
      $principalId = $_SESSION['principal_id'] ;

//setup S3 storage class.
new Storage(S3_ACCESS_ID, S3_SECRET_KEY, false, S3_ENDPOINT, S3_REGION);      

//Create new database object
$dbConn  = new dbConnect(); 
$dbConn->dbConnection();
$errorTO = new ErrorTO;


$MailedInvoicesDAO = new MailedInvoicesDAO($dbConn);
$recpList          = $MailedInvoicesDAO->getMailedInvoiceRecipients();

foreach($recpList as $row) {
	       
	       $prinId    = $row['principal_uid'];
         $depId     = $row['depot_uid'];
	       $email     = $row['email_addr'];
	       $DepNme    = $row['name'];
	       $prinList  = $row['mail_invoices_principal_uid'];
	       $invStart  = $row['mail_invoices_start'];
	       $contactID = $row['pcUid'];
	
         $MailedInvoicesDAO = new MailedInvoicesDAO($dbConn); 
         $invList           = $MailedInvoicesDAO->getDocumentsForMail($depId, 
                              $prinId, 
                              $invStart,
                              $contactID);	
         if(count($invList) > 0) {
	
               foreach($invList as $iRow) {
         	
                     $docUid    = $iRow['dmUid'];
                     $docNo     = $iRow['document_number'];
                     $invStart  = $iRow['invoice_date'];
	                   $prinName  = $iRow['prinName'];
                     $shortName = $iRow['pShortName'];

                     $seqFilename = 'INV-' . ltrim($iRow['document_number'],"0") .'.pdf';
	
	                   //upload to AWS S3
                     $storageFilename = "/archives/pdfinvoices/" . date("Y") . "/" . date("m") . "/" . $seqFilename;
       
                     $url = "https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/functional/presentations/view/pdf_invoice_version.php?DOCMASTID=" . $docUid;
       
                     $ch = curl_init();
       
                     curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
                     curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                     curl_setopt($ch, CURLOPT_URL, $url);
       
                     $response=curl_exec($ch);
                     curl_close($ch);
       
                     // var_dump(($response));
                     $storageUploadResult = Storage::putObject(S3_BUCKET_NAME, $storageFilename, $response);
                     if(!$storageUploadResult){
       	                  //echo "<br><br>";
       	                  //var_dump($response);
       	
                          echo "error uploading document!";
                          return;
                     } else { 
                     	
                     	    // Flag Document as Sent
       	     
                          // Mail Document
       	     
                          // Set up Distribution
                          if($shortName <> '') {
                	                $prinnam = trim($shortName);
                	        } else {
                	                $prinnam = trim($prinName);	
                	        }
                	          
                          $postingDistributionTO = new PostingDistributionTO;
                          $postingDistributionTO->DMLType = "INSERT";
                          $postingDistributionTO->deliveryType = BT_EMAIL;
                          $postingDistributionTO->subject = trim($prinnam) . ' - Invoice ' . ltrim($docNo,'0') ;
                          $postingDistributionTO->body = 'Invoice ' .   ltrim($docNo,'0'). ' From '. trim($prinnam) ;
                          //$postingDistributionTO->attachmentFile = $ROOT.'/archives/pdfinvoices/INV-' . ltrim($docNo,'0') .'.pdf';
                          $postingDistributionTO->setAttachmentFileAsS3Uri(S3_BUCKET_NAME, $storageFilename);
       
                          $postingDistributionTO->destinationAddr = ltrim($email);
                          $postDistributionDAO = new PostDistributionDAO($dbConn);
                          $dResult = $postDistributionDAO->postQueueDistribution($postingDistributionTO);
       
                          if ($dResult->type!=FLAG_ERRORTO_SUCCESS) {
                                   $errorTO->type=FLAG_ERRORTO_ERROR;
                                   $errorTO->description = " Extract distribution queue failed for for nr.UID {$recipientUId} and Recipient/Contact: '{$re}'.";
                                   BroadcastingUtils::sendAlertEmail("System Error", $errorTO->description, "Y", true);
                                   return $errorTO;
                          } else {
                                   $dbConn->dbinsQuery("commit");
                          }         	          
                	        // Flag as sent
                	          
                	        $MailedInvoicesDAO = new MailedInvoicesDAO($dbConn); 
                          $errorTO = $MailedInvoicesDAO->flagDocumentAsSent($docUid, $contactID );
                           
                          if ($errorTO->type!=FLAG_ERRORTO_SUCCESS) {
                                 $errorTO->type=FLAG_ERRORTO_ERROR;
                                 $errorTO->description = "'Invoice Not Flagged - ??";
                                 BroadcastingUtils::sendAlertEmail("System Error", $errorTO->description, "Y", true);
                                 return $errorTO;
                          }
     
                          echo "Successfully Mailed Invoice " .   ltrim($docNo,'0'). " From ". trim($prinnam) . "<br><br>" ;
                     }
               }
         } else {       
              echo 'No Invoices for Mailing now<br><br>' ;       
         } 
}

echo "End Of Script[***EOS***]";
// ******************************************************************************************************************************************

