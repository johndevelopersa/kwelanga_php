<?php
/* * ********************************************************************************************
 * *
 * *  Error Rport for Omni Transactions
 * *
 * *********************************************************************************************** */
 
require_once 'ROOT.php';
require_once $ROOT . 'PHPINI.php';
include_once($ROOT . $PHPFOLDER . 'DAO/db_Connection_Class.php');
include_once($ROOT . $PHPFOLDER . 'libs/BroadcastingUtils.php');
include_once($ROOT . $PHPFOLDER . 'libs/GUICommonUtils.php');
include_once($ROOT . $PHPFOLDER . "DAO/PrincipalDAO.php");
include_once($ROOT . $PHPFOLDER . "DAO/MiscellaneousDAO.php");
include_once($ROOT . $PHPFOLDER . "DAO/messagingDAO.php");
include_once($ROOT . $PHPFOLDER . "DAO/OmniExtractDAO.php");
include_once($ROOT . $PHPFOLDER . "DAO/postDistributionDAO.php");
include_once($ROOT . $PHPFOLDER . 'TO/postingDistributionTO.php');


require_once($ROOT . $PHPFOLDER . "properties/" . "Omni_Constants_" . $principal_uid . ".php");

if (!isset($dbConn)) {
    $dbConn = new dbConnect();
    $dbConn->dbConnection();
    
}

$constantsClass = "Omni_Constants_" . $principal_uid ;

$allowedErrors = 10;

$eList = (new OmniExtractDAO($dbConn))->getListofOmniErrors($constantsClass::PrincipalID, 
                                                            $constantsClass::extractType,
                                                            $allowedErrors);
// echo "<pre>";                                                        
// print_r($eList);

    if (count($eList) > 0) {
    	
        echo "<br>";
        echo  count($eList) . "-  Problems found for " . $constantsClass::PrincipalID;
        echo "<br>";   	
    	
        $storeString = '';
        $bodyString = '';
    
        $c= 0;

        foreach($eList as $elRow) {
        	
             if($storeString <> trim($elRow['email_addr'])  ) {
                 if($storeString <> '' ) {
    	  	      	   // finalise Distribution TO   
    	  	      	   
                     $messagingDAO = new messagingDAO($dbConn);
                     $bodyString = $bodyString . $messagingDAO->getTemplateBodyErrorend('');

                     $postingDistributionTO->body = $bodyString;

                     $postDistributionDAO = new postDistributionDAO($dbConn);
                     $dResult = $postDistributionDAO->postQueueDistribution($postingDistributionTO);   	  	
                     $bodyString = '';
                 }
    	  	   
    	  	       // Set up new distribution TO
                 $postingDistributionTO = new PostingDistributionTO;
                 $postingDistributionTO->DMLType = "INSERT";
                 $postingDistributionTO->deliveryType = BT_EMAIL;
                 $messagingDAO = new messagingDAO($dbConn);
                 $postingDistributionTO->subject = $messagingDAO->getTemplateOmniImportErrorSubject(trim($elRow['Principal'])); 
                 $postingDistributionTO->destinationAddr =  trim($elRow["email_addr"]); 
                 $storeString = trim($elRow['email_addr']);

                 $messagingDAO = new messagingDAO($dbConn);
                 $bodyString = $bodyString . $messagingDAO->getTemplateBodyErrorHeader('');
             }
             $messagingDAO = new messagingDAO($dbConn);
             $postingDistributionTO->destinationAddr = $elRow['email_addr'];
             if(strpos($elRow['general_reference_1'],'Debug') == 0) {$wordDebug = 30;} else { $wordDebug = strpos($elRow['general_reference_1'],'Debug');}    	  	 
             $bodyString = $bodyString . $messagingDAO->getTemplateBodyErrorBody($elRow['document_number'], 
    	                                                                           $elRow['order_date'], 
    	                                                                           $elRow['deliver_name'], 
    	                                                                           trim(preg_replace("/\r|\n/", "", substr($elRow['general_reference_1'],0,$wordDebug))) , 
    	                                                                           $elRow['data_uid'], 
    	                                                                           $elRow['psm_uid'], 
    	                                                                           $elRow['type']);
    	                                                                           
    	       // Update Error count
    	       $c++;
    	       
    	       $eList = (new OmniExtractDAO($dbConn))->updateOmniErrorCount($elRow['seUid']);
    	                                                                               	  
        }
        
        if($c > 0) {
            $messagingDAO = new messagingDAO($dbConn);
            $bodyString = $bodyString . $messagingDAO->getTemplateBodyErrorend($elRow['Warehouse']);
    
            $postingDistributionTO->body = $bodyString;

            $postDistributionDAO = new postDistributionDAO($dbConn);
            $dResult = $postDistributionDAO->postQueueDistribution($postingDistributionTO);  
            //   print_r($postingDistributionTO);
            
            $dbConn->dbinsQuery("commit;");           	
        	
        }
    } else {
        echo "<br>";
        echo "No Problems found for " . $constantsClass::PrincipalID;
        echo "<br>";   	
    }
    


    echo "<br>";
    echo "Report Completed";
    echo "<br>";
    echo "[***EOS***]"; 
?>    