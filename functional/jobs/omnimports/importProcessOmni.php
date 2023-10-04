<?php
echo "Import Array";
print_r($omniOrder->getJSON());
echo "<br>";

$omniApi = new OMNIRestAPI($constantsClass::OmniHostname,
                           $constantsClass::OmniUsername,
                           $constantsClass::OmniPassword,
                           $constantsClass::OmniLiveCompany);

           $response = $omniApi->CreateSalesOrder($omniOrder, $documentNumber);
           $responseOrderNo = $response->getBody();  //only the order number comes back and not in JSON format!

           if (!$response->getSuccess() || $response->getSuccess() && $responseOrderNo != $documentNumber) {

               $errMessage = $response->getErrorMessage();
               if(strpos(strtolower($errMessage), "already exists") !== false) {
                    $general1 = "[OMNI] Duplicate Order, Ignoring error: " . $errMessage ;
                    $updateSmartE = new OmniExtractDAO($dbConn);
                    $uSE = $updateSmartE->updateSmartEventDirectly($seOrderLineStore, $general1, $general2 = "", $statusFlag = FLAG_STATUS_CLOSED);

                    $updateImportStat = new OmniExtractDAO($dbConn);
                    $uSE = $updateSmartE->setOmniImportStatus($seOrderLineStore, $constantsClass::extractType, DST_ACCEPTED);

               } else {
               	echo trim($errMessage);
               	
               	
                    if(trim($errMessage) == '' || trim($errMessage) == NULL ) {
                         $general1 = "Communication or Technical Error";                            
                    } else {
                         $general1 = "[OMNI API]: " . $errMessage;
                    }
                    $updateSmartE = new OmniExtractDAO($dbConn);
                    $uSE = $updateSmartE->updateSmartEventDirectly($seOrderLineStore, $general1, $general2 = "", $statusFlag = FLAG_ERRORTO_ERROR);



                    // For Debugging dump full response here...
                    // var_dump($response);	
               }
               echo "<br>";
               echo "[OMNI] Unsuccessful - Order Not Created - " . $general1;
               echo "<br>";
               echo "End of Process";
               
           } else { 
           	   
           	   $updateSmartE = new OmniExtractDAO($dbConn);
           	   $general1 = "Success"; 
               $uSE = $updateSmartE->updateSmartEventDirectly($seOrderLineStore, $general1, $general2 = "", $statusFlag = FLAG_STATUS_CLOSED);

               $updateImportStat = new OmniExtractDAO($dbConn);
               $uSE = $updateSmartE->setOmniImportStatus($seOrderLineStore, $constantsClass::extractType, DST_ACCEPTED);
           	
               echo "<h1>Loaded Order: {$documentNumber}</h1>";
               echo "<br>";
               echo "[OMNI] Successfully Created Order:" . $responseOrderNo;
               echo "<br>";
               echo "End of Process";
           }

?>           