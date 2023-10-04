<?php
// This MUST initially be as lightweight as possible until it requires content to be returned, hence no access_control

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT . $PHPFOLDER . "properties/Constants.php");
include_once($ROOT . $PHPFOLDER . "properties/ServerConstants.php");
include_once($ROOT . $PHPFOLDER . "DAO/db_Connection_Class.php");
include_once($ROOT . $PHPFOLDER . 'DAO/ApiDAO.php');
include_once($ROOT . $PHPFOLDER . 'functional/ws/api/ProcessTransactionsFromApi.php');
include_once($ROOT . $PHPFOLDER . 'functional/ws/api/getTripSheetDriverClass.php');

$dbConn = new dbConnect();
$dbConn->dbConnection();

$errorTO = new ErrorTO;

$data = file_get_contents('php://input');

file_put_contents( "C:/inetpub/wwwroot/systems/kwelanga_system/ftp/outerjoin/extracts/api.txt", $data . "\n\n"); 

$JSON = json_decode($data, true);

file_put_contents( "C:/inetpub/wwwroot/systems/kwelanga_system/ftp/outerjoin/extracts/api.txt", print_r($JSON, TRUE) . "\n\n"); 
$newApiDAO = new ApiDAO($dbConn);
$uCred = $newApiDAO->getVendorUser($JSON['username']);
$uCredStr = trim($uCred[0]['username']) . trim($uCred[0]['password']);
$upayLoadStr = trim($JSON['username']) . trim($JSON['password']);

if (strcmp($uCredStr, $upayLoadStr) !== 0) {  // make sure you setup a password specifically for each client individually
     echo json_encode( [
    "resultStatus"=>"E",
    "ResultCode"    =>'701' ,
    "resultMessage"=>"Sorry, incorrect API credentials supplied"
    ] );
    exit; // !! NB !!
} else {
//           echo trim($JSON['requireddata']);
           if (trim($JSON['requireddata'])     == 'getArrivalData') {
           	
           	file_put_contents($ROOT.'ftp/api2/bapiorderBD'  . date("YmdHis") . '.txt', print_r($JSON, TRUE));

// ********************** 1 * Arrival Capture Data  ********************** //                  	
           	
           $aresult   = $newApiDAO->getArrivalUserData(trim($JSON['username']), trim($JSON['password']));
                       
                       if(count($aresult) == 0)	{
                       	
           	                 $newApiDAO = new APIDAO($dbConn);
                             $errorTO = $newApiDAO->addVendorUserlogEntry(trim($uCred[0]['pv_uid']), 
                                                                          "E", 
                                                                          trim($JSON['requireddata']),
                                                                          '702');
                       	     echo json_encode( ["resultStatus"=>"E",
                       	                        "ResultCode"    =>'820' ,
                       	     	     	            "resultMessage"=>"Arrival Capture user Cannot Log in    - Cannot Continue"
                       	     	     	           ] );
                             exit; // !! NB !!
                       }
                       $appIdArray = array('DISPATCH');
                       
                       if(in_array(trim($JSON['appId']),$appIdArray)) {
                       	     // Verify Device ID
                       	     
                       	     $dIdresult   = $newApiDAO->verifyDeviceId($aresult[0]['userUid'], trim($JSON['deviceIdfilename']));
                       	     
                             if(count($dIdresult) == 999)	{
                       	
           	                      $newApiDAO = new APIDAO($dbConn);
                                  $errorTO = $newApiDAO->addVendorUserlogEntry(trim($uCred[0]['pv_uid']), 
                                                                               "E", 
                                                                                trim($JSON['requireddata']),
                                                                               '706');
                                  echo json_encode( ["resultStatus"=>"E",
                       	                             "ResultCode"    =>'706' ,
                       	     	     	                 "resultMessage"=>"Device ID cannot be Verified - Cannot Continue"
                       	     	     	                ] );
                                   exit; // !! NB !!
                             }
                       }
                       
                       $returnArr = [];
                       $vaildate  = 'Y';
                       foreach ($aresult as $r) {
                       	     if($vaildate  == 'Y') {
                       	     	     
                       	     	     $userCredStr = trim($r['user_email']) . trim($r['password']);
                       	     	     
                       	     	     $userpayLoadStr = trim($JSON['userEmail']) . trim($JSON['userPassword']);
                       	     	     
                       	     	     if (strcmp($userCredStr, $userpayLoadStr) !== 0) { 
                       	     	          // Check here that rep has Kwelanga account and the role to capture orders 	    
                                   } 
                                   $vaildate  = 'N'; 
                             }      
                             $returnArr[] = [
                                             "userEmail"       => $r["user_email"],
		                                         "principalUid"    => $r["principal_uid"],
		                                         "principalName"   => $r["principal_name"],
		                                         "shortName"       => $r["short_name"],
		                                         "status"          => "A",
		                                         "deviceId"        => "OK"
		                                        ];
                                                                 
                       	     // send JSON back to the client :
                       }
                       
           	                 $newApiDAO = new APIDAO($dbConn);
                             $errorTO = $newApiDAO->addVendorUserlogEntry(trim($uCred[0]['pv_uid']), 
                                                                          "S", 
                                                                          trim($JSON['requireddata']),
                                                                          '000');
                                                                          
                                                   
                       echo json_encode(["resultStatus" => "S",
                                         "ResultCode"    =>'000' ,
                                         "resultMessage" => "Successfully retrieved data",
                                         "data" => $returnArr
                                        ]);
               
           } elseif(trim($JSON['requireddata']) == 'getRepInfo') {
           	
// ********************** 2 * getRepInfo  ********************** //                  	
           	
           	           if(!filter_var(trim($JSON['userEmail']), FILTER_VALIDATE_EMAIL)) {
           	           	
           	                 $newApiDAO = new APIDAO($dbConn);
                             $errorTO = $newApiDAO->addVendorUserlogEntry(trim($uCred[0]['pv_uid']), 
                                                                          "E", 
                                                                          trim($JSON['requireddata']),
                                                                          '717');
                             echo json_encode( [
                                                 "resultStatus"=>"E",
                                                 "ResultCode"    =>'717' ,
                                                 "resultMessage"=>"Invalid Email Address"
                                               ] );
                                               
           	                 exit; // !! NB !!
           	           }
                       $newApiDAO = new APIDAO($dbConn);
                       $aresult   = $newApiDAO->getUserData(trim($JSON['userEmail']));
                       
                       if(count($aresult) == 0)	{
                       	
           	                 $newApiDAO = new APIDAO($dbConn);
                             $errorTO = $newApiDAO->addVendorUserlogEntry(trim($uCred[0]['pv_uid']), 
                                                                          "E", 
                                                                          trim($JSON['requireddata']),
                                                                          '702');
                       	     echo json_encode( ["resultStatus"=>"E",
                       	                        "ResultCode"    =>'702' ,
                       	     	     	            "resultMessage"=>"No Rep found for this email  - Cannot Continue"
                       	     	     	           ] );
                             exit; // !! NB !!
                       }     	
                       
                       $returnArr = [];
                       $vaildate  = 'Y';
                       foreach ($aresult as $r) {
                       	     if($vaildate  == 'Y') {
                       	     	     
                       	     	     $userCredStr = trim($r['user_email']) . trim($r['mobi_password']);
                       	     	     
                       	     	     $userpayLoadStr = trim($JSON['userEmail']) . trim($JSON['userPassword']);
                       	     	     
                       	     	     if (strcmp($userCredStr, $userpayLoadStr) !== 0) { 
                       	     	          // Check here that rep has Kwelanga account and the role to capture orders 	    
                                   } 
                                   $vaildate  = 'N'; 
                             }      
                             $returnArr[] = [
                                             "userEmail"       => $r["user_email"],
		                                         "principalUid"    => $r["principal_uid"],
		                                         "principalName"   => $r["principal_name"],
		                                         "status"          => "A"
		                                        ];
                                                                 
                       	     // send JSON back to the client :
                       }
                       
           	                 $newApiDAO = new APIDAO($dbConn);
                             $errorTO = $newApiDAO->addVendorUserlogEntry(trim($uCred[0]['pv_uid']), 
                                                                          "S", 
                                                                          trim($JSON['requireddata']),
                                                                          '000');
                       
                       echo json_encode(["resultStatus" => "S",
                                         "ResultCode"    =>'000' ,
                                         "resultMessage" => "Successfully retrieved data",
                                         "data" => $returnArr
                                        ]);
               
           } elseif(trim($JSON['requireddata']) == 'getUserStore') {
                               
// ********************** 3 * getUserStore  **********************                                                 
                                                         	   
           	           if(trim($JSON['principalId']) == '' || trim($JSON['principalId']) == NULL) {

           	                 $newApiDAO = new APIDAO($dbConn);
                             $errorTO = $newApiDAO->addVendorUserlogEntry(trim($uCred[0]['pv_uid']), 
                                                                          "E", 
                                                                          trim($JSON['requireddata']),
                                                                          '706');

           	                  echo json_encode( ["resultStatus"=>"E",
                       	                        "ResultCode"    =>'706' ,
                       	     	     	            "resultMessage"=>"Principal Code Not Supplied  - Cannot Continue"
                       	     	     	           ] );
                             exit; // !! NB !!
           	           }
           	           
          	           if(!filter_var(trim($JSON['userEmail']), FILTER_VALIDATE_EMAIL)) {

             $newApiDAO = new APIDAO($dbConn);
                             $errorTO = $newApiDAO->addVendorUserlogEntry(trim($uCred[0]['pv_uid']), 
                                                                          "E", 
                                                                          trim($JSON['requireddata']),
                                                                          '717');
                             echo json_encode( [
                                                 "resultStatus" =>"E",
                                                 "ResultCode"   =>'717' ,
                                                 "resultMessage"=>"Invalid Email Address"
                                               ] );
                                               
           	                 exit; // !! NB !!
           	           }
           	           
                       $newApiDAO = new APIDAO($dbConn);
                       $aresult   = $newApiDAO->getUserStoreList(trim($JSON['userEmail']), trim($JSON['principalId']));	     	
                       
                       $returnArr = [];
                   
                       	     if(count($aresult)  == 0) {
                       	     	
                                    $newApiDAO = new APIDAO($dbConn);
                                    $errorTO = $newApiDAO->addVendorUserlogEntry(trim($uCred[0]['pv_uid']), 
                                                                          "E", 
                                                                          trim($JSON['requireddata']),
                                                                          '703');
                                                                          
                       	            echo json_encode( ["resultStatus" =>"E",
                       	                               "ResultCode"   =>'703' ,
                                                       "resultMessage"=>"No Stores available for Rep - Contact Support"
                       	     	     	                  ] );
                                         exit; // !! NB !!
                             }
                             
                       foreach ($aresult as $r) {

                             $returnArr[] = [
                                             "principalUid"  => $r["principal_uid"],
		                                         "storeId"       => $r["psmUid"],
		                                         "storeName"     => $r["psmStore"],
		                                         "storeGroup"    => $r["psmChain"],
		                                         "GroupName"     => $r["chainName"],
		                                         "storeDepot"    => $r["psmWh"],
		                                         "DepotName"     => $r["depotName"]
		                                        ];
                       }                                          
                       // send JSON back to the client :
                            $newApiDAO = new APIDAO($dbConn);
                            $errorTO = $newApiDAO->addVendorUserlogEntry(trim($uCred[0]['pv_uid']), 
                                                                          "S", 
                                                                          trim($JSON['requireddata']),
                                                                          '000');
                            echo json_encode(["resultStatus" => "S",
                                              "ResultCode"   =>'000' ,
                                             "resultMessage" => "Successfully retrieved data",
                                         "data" => $returnArr
                                        ]); 


           } elseif(trim($JSON['requireddata']) == 'getProductByBarCode') {
// ********************** 4 * getProductByBarCodet  **********************   

                      $newApiDAO = new APIDAO($dbConn);
                       $aresult   = $newApiDAO->getRequiredProductByBarcode(trim($JSON['principalId']), trim($JSON['scannedProduct']));
                       if(count($aresult) == 0)	{
                                    $newApiDAO = new APIDAO($dbConn);
                                    $errorTO = $newApiDAO->addVendorUserlogEntry(trim($uCred[0]['pv_uid']), 
                                                                          "E", 
                                                                          trim($JSON['requireddata']),
                                                                          '704');
                             echo json_encode( ["resultStatus" =>"E",
                                                "ResultCode"  =>'704' ,
                                   	            "resultMessage"=>"No Products set up for the for this Code - Cannot Continue"
                                               ] );
                             exit; // !! NB !!
                       } elseif(count($aresult) > 1)	{
                                    $newApiDAO = new APIDAO($dbConn);
                                    $errorTO = $newApiDAO->addVendorUserlogEntry(trim($uCred[0]['pv_uid']), 
                                                                          "E", 
                                                                          trim($JSON['requireddata']),
                                                                          '704');
                             echo json_encode( ["resultStatus" =>"E",
                                                "ResultCode"  =>'704' ,
                                   	            "resultMessage"=>"More than one product returned - Big Problem - Cannot Continue"
                                               ] );
                             exit; // !! NB !!
                       }
                       
                       $returnArr = [];
                       foreach ($aresult as $r) {
	     
	                              $returnArr[] = [
		                                 "principalUid"          => $r["principal_uid"],
		                                 "productUid"            => $r["productUid"],
		                                 "Product"               => $r["product_description"],
		                                 "imageUrl"              => $r["Image"]
		                            ];
                       
                       }
	                     // send JSON back to the client :
                                    $newApiDAO = new APIDAO($dbConn);
                                    $errorTO = $newApiDAO->addVendorUserlogEntry(trim($uCred[0]['pv_uid']), 
                                                                          "s", 
                                                                          trim($JSON['requireddata']),
                                                                          '000');
           
                       echo json_encode(["resultStatus" => "S",
                                         "resultMessage" => "Successfully retrieved data",
                                         "data" => $returnArr
                                        ]); 

	         } elseif(trim($JSON['requireddata']) == 'getProduct') {

// ********************** 5 * getProduct  **********************   

                      $newApiDAO = new APIDAO($dbConn);
                       $aresult   = $newApiDAO->getRequiredDataProducts(trim($JSON['principalId']));
                       if(count($aresult) == 0)	{
                                    $newApiDAO = new APIDAO($dbConn);
                                    $errorTO = $newApiDAO->addVendorUserlogEntry(trim($uCred[0]['pv_uid']), 
                                                                          "E", 
                                                                          trim($JSON['requireddata']),
                                                                          '704');
                             echo json_encode( ["resultStatus" =>"E",
                                                 "ResultCode"  =>'704' ,
                                   	            "resultMessage"=>"No Products set up for the rep - Cannot Continue"
                                               ] );
                             exit; // !! NB !!
                       }
                       $returnArr = [];
                       foreach ($aresult as $r) {
	     
	                              $returnArr[] = [
		                                 "principalUid"          => $r["principal_uid"],
		                                 "ProdCode"              => $r["product_code"],
		                                 "Product"               => $r["product_description"],
		                              ];
                       
                       }
	                     // send JSON back to the client :
                                    $newApiDAO = new APIDAO($dbConn);
                                    $errorTO = $newApiDAO->addVendorUserlogEntry(trim($uCred[0]['pv_uid']), 
                                                                          "s", 
                                                                          trim($JSON['requireddata']),
                                                                          '000');
           
                       echo json_encode(["resultStatus" => "S",
                                         "resultMessage" => "Successfully retrieved data",
                                         "data" => $returnArr
                                        ]); 

	         } elseif(trim($JSON['requireddata']) == 'getScanData') {


// ********************** 6* getScanData  **********************   
/*           
                       $newApiDAO = new APIDAO($dbConn);
                       $aresult   = $newApiDAO->getUserStoreList(trim($JSON['userEmail']), trim($JSON['principalId']));	     	
                       
                       $returnArr = [];
                       foreach ($aresult as $r) {
                       	     if(count($aresult)  == 0) {                     	     	     	
                       	            echo json_encode( ["resultStatus"=>"E",
                                                       "resultMessage"=>"No User Stores Returned - Contact Support"
                       	     	     	                  ] );
                                         exit; // !! NB !!
                             } 
                             $returnArr[] = [
                                             "principalUid"  => $r["principal_uid"],
		                                         "storeId"       => $r["psmUid"],
		                                         "storeName"     => $r["psmStore"]
		                                        ];
                       }                                          
                       // send JSON back to the client :
           
                       echo json_encode(["resultStatus" => "S",
                                         "resultMessage" => "Successfully retrieved data",
                                         "data" => $returnArr
                                        ]); 
*/	         
// ********************** 7 * getPriceByCustomer	**********************               

           } elseif(trim($JSON['requireddata']) == 'getStockLevel') {

// ********************** 8 * getStockLevel     	********************** 	  
           	
           	    // Get wareHouse uid on Short Name
           	    
                $newApiDAO = new APIDAO($dbConn);
                $whUid     = $newApiDAO->getwhouseUidonShortName(test_input(trim($JSON['warehouse_short_name'])));
                
                if(count($whUid) == 1) {
                	
                } else {
                	
                      $newApiDAO = new APIDAO($dbConn);
                       $errorTO = $newApiDAO->addVendorUserlogEntry(trim($uCred[0]['pv_uid']), 
                                                                    'E', 
                                                                     trim($JSON['requireddata']),
                                                                     '816');

                      echo json_encode( ["resultStatus" >"E",
                                         "ResultCode"    =>'816' ,
                                         "resultMessage"=>"No Warehouse ID found"
                                        ] );
                             exit; // !! NB !!
                }
                // Validate json data before calling the script
	         	           
	               $newApiDAO = new APIDAO($dbConn);
                 $errorTO   = $newApiDAO->jsonDataValidation(trim($uCred[0]['pv_uid']),
                                                             GETSTOCKLEVEL,
                                                             trim($JSON['principalId']), 
                                                             $whUid[0]['wh_uid'],
                                                             '',
                                                             '',
                                                             '',
                                                             '');
                 if($errorTO->type == 'E') {

                        $newApiDAO = new APIDAO($dbConn);
                        $errorTO = $newApiDAO->addVendorUserlogEntry(trim($uCred[0]['pv_uid']), 
                                                                     $errorTO->type, 
                                                                     trim($JSON['requireddata']),
                                                                     $errorTO->identifier);

                        echo json_encode( ["resultStatus"  =>$errorTO->type,
                                           "ResultCode"    =>$errorTO->identifier , 
                                           "resultMessage" =>$errorTO->description
                                               ] );
                             exit; // !! NB !!
                 }                
                 $newApiDAO = new APIDAO($dbConn);
                 $stkBal = $newApiDAO->getRequiredDataStockBalances(trim($JSON['principalId']), 
                                                                    $whUid[0]['wh_uid'], 
                                                                    $JSON['product_code']);
                 if(count($stkBal) == 0)	{
                 	
                        $newApiDAO = new APIDAO($dbConn);
                        $errorTO = $newApiDAO->addVendorUserlogEntry(trim($uCred[0]['pv_uid']), 
                                                                     'E', 
                                                                     trim($JSON['requireddata']),
                                                                     '709');
                         echo json_encode( ["resultStatus" =>"E",
                                            "ResultCode"   =>"709" , 
                                   	        "resultMessage"=>"No Product selected or found"
                                               ] );
                        exit; // !! NB !!
                 } 	else {

                        $newApiDAO = new APIDAO($dbConn);
                        $errorTO = $newApiDAO->addVendorUserlogEntry(trim($uCred[0]['pv_uid']), 
                                                                     'S', 
                                                                     trim($JSON['requireddata']),
                                                                     '000');

                       echo json_encode(["resultStatus"  => "S",
                                         "ResultCode"    => "000",
                                         "resultMessage" => "Successful",
                                         "data" => $stkBal]); 
                 }	         

// ********************** 7 * getSalesDetailData	**********************

           } elseif(trim($JSON['requireddata']) == 'getSalesDetailData') {
	         	
                      $newApiDAO = new APIDAO($dbConn);
                       $aresult   = $newApiDAO->getSalesDetailreport(trim($JSON['startDate']), trim($JSON['endDate']), trim($JSON['principalId']));
                       
                       if(count($aresult) == 0)	{

                             $newApiDAO = new APIDAO($dbConn);
                             $errorTO = $newApiDAO->addVendorUserlogEntry(trim($uCred[0]['pv_uid']), "E", trim($JSON['requireddata']));  
 
                             echo json_encode( ["resultStatus"=>"E",
                                                 "ResultCode"    =>'704' ,
                                   	            "resultMessage"=>"No Sales Data Extracted - Cannot Continue"
                                               ] );
                             exit; // !! NB !!
                       }
                       $returnArr = [];
                       foreach ($aresult as $r) {
	     
	                              $returnArr[] = [
                                    "principalUid"          => $r["principal_uid"],
                                    "region"                => $r["Region"],
                                    "documentNumber"        => $r["Document No"],
                                    "description"           => $r["Document Type"],
                                    "processedDate"         => $r["Date Captured"],
                                    "description"           => $r["Status"],
                                    "deliverName"           => $r["Customer"],
                                    "customerOrderNumber"   => $r["Customer Order No"],
                                    "productCode"           => $r["Product Code"], 
                                    "productDescription"    => $r["Product"],                              
                                    "orderedQty"            => $r["Ordered Qty"],    
                                    "documentQty"           => $r["Document Qty"],
                                    "extendedPrice"         => $r["Exclusive Total"],
                                    "vatAmount"             => $r["Vat Total"],
                                    "total"                 => $r["Invoice Total"],
                                    "grvNumber"             => $r["GRV Number"],
                                    "sourceDocumentNumber"  => $r["Source Document No"],
                                    "processedDate"         => $r["Processed Date"],
                                    "orderDate"             => $r["Order Date"],
                                    "invoiceDate"           => $r["Invoice Date"] 		                                 
		                              ];                       
                       }
                       // send JSON back to the client :
                       $newApiDAO = new APIDAO($dbConn);
                       $errorTO = $newApiDAO->addVendorUserlogEntry(trim($uCred[0]['pv_uid']), "S", trim($JSON['requireddata']));  
            
                       echo json_encode(["resultStatus" => "S",
                                         "resultMessage" => "Successfully retrieved data",
                                         "data" => $returnArr
                                        ]);


           } elseif(trim($JSON['requireddata']) == 'getSalesDataHeaders') {
	         	
                      $newApiDAO = new APIDAO($dbConn);
                       $aresult   = $newApiDAO->getOrderDataHeaders(trim($JSON['startDate']), 
                                                                    trim($JSON['endDate']), 
                                                                    trim($JSON['principalList']),
                                                                    trim($JSON['depotList']),
                                                                    trim($JSON['statusList']));
                       if(count($aresult) == 0)	{

                             $newApiDAO = new APIDAO($dbConn);
                             $errorTO = $newApiDAO->addVendorUserlogEntry(trim($uCred[0]['pv_uid']), "E", trim($JSON['requireddata']));  
 
                             echo json_encode( ["resultStatus"=>"E",
                                                 "ResultCode"    =>'704' ,
                                   	            "resultMessage"=>"No Sales Data Extracted - Cannot Continue"
                                               ] );
                             exit; // !! NB !!
                       }
                       $returnArr = [];
                       foreach ($aresult as $r) {
	     
	                              $returnArr[] = [
                                    "principal"             => $r["Principal_Name"],
                                    "depot"                 => $r["Warehouse"],
                                    "documentNumber"        => $r["Document No"],
                                    "invoiceNumber"         => $r["Invoice Number"],
                                    "processedDate"         => $r["Processed Date"],
                                    "processedTime"         => $r["Processed Time"],
                                    "status"                => $r["Status"],
                                    "deliverName"           => $r["Customer"],
                                    "customerOrderNumber"   => $r["Customer Order No"],
                                    "quantity"              => $r["Quantity"],
                                    "exclusiveTotal"        => $r["Excl. Value"],
                                    "orderDate"             => $r["Order Date"],
                                    "invoiceDate"           => $r["Invoice Date"],
                                    "captured_by"           => $r["Captured By"] 		                                 
		                              ];
		                              
		                              
                       }
                       // send JSON back to the client :
                       $newApiDAO = new APIDAO($dbConn);
                       $errorTO = $newApiDAO->addVendorUserlogEntry(trim($uCred[0]['pv_uid']), "S", trim($JSON['requireddata']));  
            
                       echo json_encode(["resultStatus" => "S",
                                         "resultMessage" => "Successfully retrieved data",
                                         "data" => $returnArr
                                        ]);
	         } elseif(trim($JSON['requireddata']) == 'postOrder') {

// ********************** 10 * postOrder	**********************	         	
// if(trim($uCred[0]['pv_uid'])==254) {
//  echo "<pre>";
// print_r($JSON);	
// }

                 file_put_contents($ROOT.$PHPFOLDER.'log/apiorder' . date("ymd") . '.txt', print_r($JSON, TRUE), FILE_APPEND);  

	               $newApiDAO = new APIDAO($dbConn);
                 $errorTO   = $newApiDAO->jsonDataValidation(trim($uCred[0]['pv_uid']),
                                                             POSTORDER,
                                                             trim($JSON['principalId']), 
                                                             test_input(trim($JSON['customer_name'])),
                                                             test_input(trim($JSON['physical_address_1'])),
                                                             test_input(trim($JSON['physical_address_2'])),
                                                             test_input(trim($JSON['email_address'])),
                                                             test_input(trim($JSON['reference_number'])));
                 if($errorTO->type == 'E') {
                 	
                 	          $validationErrType        = $errorTO->type;
                 	          $validationErrDescription = $errorTO->description;
                 	          $validationErrIdentifier  = $errorTO->identifier;
                 	          

                            $newApiDAO = new APIDAO($dbConn);
                            $errorTO = $newApiDAO->addVendorUserlogEntry(trim($uCred[0]['pv_uid']), 
                                                                     $errorTO->type, 
                                                                     trim($JSON['requireddata']),
                                                                     $errorTO->identifier);

                             echo json_encode( ["resultStatus"  =>$validationErrType,
                                                "ResultCode"    =>$validationErrIdentifier , 
                                   	            "resultMessage" =>$validationErrDescription
                                               ] );
                             exit; // !! NB !!
                 }
                 if(trim($JSON['order_lines'])) {
                 	
                 	      if(count($JSON['order_lines']) == 0) {

                             $newApiDAO = new APIDAO($dbConn);
                             $errorTO = $newApiDAO->addVendorUserlogEntry(trim($uCred[0]['pv_uid']), 
                                                                     'E', 
                                                                     trim($JSON['requireddata']),
                                                                     '716');

                 	           echo json_encode( ["resultStatus"  =>'E',
                                                "ResultCode"    =>'716' , 
                                   	            "resultMessage" =>'Order has no Detail Lines'
                                               ] );
                             exit; // !! NB !!                  
                        } else {
                        	     foreach($JSON['order_lines'] as $drow) {
                                    $newApiDAO = new APIDAO($dbConn);
                                    $errorTO   = $newApiDAO->jsonDetailLineValidation(trim($uCred[0]['pv_uid']),
                                                                                      POSTORDER,
                                                                                      trim($JSON['principalUid']), 
                                                                                      195,
                                                                                      test_input(trim($JSON['ProdCode'])),
                                                                                      test_input(trim($JSON['orderQuantity'])),
                                                                                      test_input(trim($JSON['netPrice'])));
                 	             }
                        }                           
                        if($errorTO->type == 'E') {

                                $newApiDAO = new APIDAO($dbConn);
                                $errorTO = $newApiDAO->addVendorUserlogEntry(trim($uCred[0]['pv_uid']), 
                                                                     $errorTO->type, 
                                                                     trim($JSON['requireddata']),
                                                                     $errorTO->identifier);  

                                echo json_encode( ["resultStatus"  =>"F",
                                                   "ResultCode"    =>$errorTO->identifier , 
                                                   "resultMessage" =>$errorTO->description
                                                  ] );
                                exit; // !! NB !!
                        }
                 }
                       $newApiDAO = new APIDAO($dbConn);
                       $errorTO = $newApiDAO->addVendorUserlogEntry(trim($uCred[0]['pv_uid']), 
                                                                    'S', 
                                                                    trim($JSON['requireddata']),
                                                                    '000');  
                       echo json_encode(["resultStatus" => "S",
                                         "ResultCode"    =>'000',
                                         "resultMessage" => "Successfully processeed. Your Order will be loaded",
                                         "data" => $returnArr]);
                                         

                 $ignoreArray = array('IV0','IN0', 'SI2', 'SC2');
                 
                 // file_put_contents($ROOT.$PHPFOLDER.'log/API.txt', substr(trim($JSON['reference_number']),0,3), FILE_APPEND);  
                 
                 if(in_array(substr(trim($JSON['reference_number']),0,3),$ignoreArray)) {
                 	        
                 	        file_put_contents($ROOT.$PHPFOLDER.'log/API.txt', substr(trim($JSON['reference_number']),0,3).'HERE', FILE_APPEND);  
                          file_put_contents($ROOT.'ftp/api2/OldFiles/Xapiorder' . test_input(trim($JSON['reference_number'])) . date("YmdHis") . '.txt', $data);  
                 } else {
                          file_put_contents($ROOT.'ftp/api2/apiorder' . test_input(trim($JSON['reference_number'])) . date("YmdHis") . '.txt', $data);  
                 }         
 //                    if(trim($uCred[0]['pv_uid'])==257) {
 //                      	echo "<br>";
 //                      	   echo $ROOT.'ftp/api2/apiorder' . test_input(trim($JSON['reference_number'])) . date("YmdHis");
 //                           echo "<br>Back from adptor>"; 
 //                         echo "<br>";
 //                    }
                                         


           } elseif(trim($JSON['requireddata']) == 'XXXArrival') {

                 // ********************** 11 * postArrival	**********************	         	
                 if(trim($uCred[0]['pv_uid'])==257) {
                 //           echo "<pre>";
                 //           print_r($JSON);	
                 }
                 
                 $ignoreArray = array('IN0', 'SI2', 'SC2');
                 
                 if(in_array(substr(trim($JSON['orderReference']),0,3),$ignoreArray)) {
                         file_put_contents($ROOT.$PHPFOLDER.'log/apiorder/OldFiles' . date("ymd") . '.txt', print_r($JSON, TRUE), FILE_APPEND);  
                 } else {
                         file_put_contents($ROOT.$PHPFOLDER.'log/apiorder' . date("ymd") . '.txt', print_r($JSON, TRUE), FILE_APPEND);  
                 }
	               $newApiDAO = new APIDAO($dbConn);
                 $errorTO   = $newApiDAO->jsonDataValidation(trim($uCred[0]['pv_uid']),
                                                             trim(strtoupper($JSON['requireddata'])),
                                                             trim($JSON['principalId']),
                                                             '',
                                                             '',
                                                             '',
                                                             '',
                                                             trim($JSON['orderReference']));
                 if($errorTO->type == 'E') {
                 	
                 	          $validationErrType        = $errorTO->type;
                 	          $validationErrDescription = $errorTO->description;
                 	          $validationErrIdentifier  = $errorTO->identifier;
                 	          

                            $newApiDAO = new APIDAO($dbConn);
                            $errorTO = $newApiDAO->addVendorUserlogEntry(trim($uCred[0]['pv_uid']), 
                                                                     $errorTO->type, 
                                                                     trim($JSON['requireddata']),
                                                                     $errorTO->identifier);

                             echo json_encode( ["resultStatus"  =>$validationErrType,
                                                "ResultCode"    =>$validationErrIdentifier , 
                                   	            "resultMessage" =>$validationErrDescription
                                               ] );
                             exit; // !! NB !!
                 }
                 

                 
                 if(trim($JSON['detailLines'])) {
                 	
                 	      if(count($JSON['detailLines']) == 0) {

                             $newApiDAO = new APIDAO($dbConn);
                             $errorTO = $newApiDAO->addVendorUserlogEntry(trim($uCred[0]['pv_uid']), 
                                                                     'E', 
                                                                     trim($JSON['requireddata']),
                                                                     '716');

                 	           echo json_encode( ["resultStatus"  =>'E',
                                                "ResultCode"    =>'716' , 
                                   	            "resultMessage" =>'Arrival has no Detail Lines'
                                               ] );
                             exit; // !! NB !!                  
                        } 
                 } else {
                        	
                          // Get all the 'Parts' to create arrival
                          // Get allowed warehouse
                          $CreateStockMovementDAO = new CreateStockMovementDAO($dbConn);
                          $allowedWh = $CreateStockMovementDAO->getUserStockMovementDepot($uCred[0]['pv_uid']);  
                             
                          if(count($allowedWh) == 0) {
                                   $newApiDAO = new APIDAO($dbConn);
                                   $errorTO = $newApiDAO->addVendorUserlogEntry(trim($uCred[0]['pv_uid']), 
                                                                                'E', 
                                                                                 trim($JSON['requireddata']),
                                                                                 '716');

                 	                 echo json_encode( ["resultStatus"  =>'E',
                                                      "ResultCode"    =>'825' , 
                                                      "resultMessage" =>'Failed to Create Arrival - Warehouse'
                                               ] );
                                   exit; // !! NB !!                  
                        	   	
                        	   }                    
                        	   $allowedWareHouse = $allowedWh[0]['allowed_warehouses'];
                        	   $userId = $allowedWh[0]['userId'] ;
                    
                             $ProcessTransactionsFromApi = New ProcessTransactionsFromApi;
                             $result = $ProcessTransactionsFromApi->postStockMovementFromAPI($data, $allowedWareHouse, $userId, DT_ARRIVAL, 'SCAN');

                    }                    
                    if($result->type == 'E') {
                    	   $resultLog  = 'E';
                    	   $resultCode = $result->type;
                    	   $resultMessage = $result->description;
                    	   $returnArr = $result->description; 
                    }  else {
                    	   $resultLog  = 'S';
                    	   $resultCode = '000';
                    	   $resultMessage = 'Successfully processeed. Your Arrival has been loaded'; 
                    	   $returnArr = '';
                    } 
                    $newApiDAO = new APIDAO($dbConn);
                    $errorTO = $newApiDAO->addVendorUserlogEntry(trim($uCred[0]['pv_uid']), 
                                                                    $resultLog, 
                                                                    trim($JSON['requireddata']),
                                                                    $resultCode);  
                    echo json_encode(["resultStatus" => $resultLog,
                                      "ResultCode"    =>$resultCode,
                                      "resultMessage" => $resultMessage,
                                      "data" => $returnArr]);
                                      
                    file_put_contents($ROOT.'ftp/api2/tapiorder' . test_input(trim($JSON['reference_number'])) . date("YmdHis") . '.txt', $data);  
                    // file_put_contents($ROOT.'ftp/api2/tapiorderBD' . test_input(trim($JSON['reference_number'])) . date("YmdHis") . '.txt', $resultMessage);

           } elseif(trim($JSON['requireddata']) == 'postManualCredit') {
           	
// ********************** 12 * postManualCredit	********************** 

           } elseif(trim($JSON['requireddata']) == 'getTripSheetDriverData') {
// ********************** 13 * getTripSheetDriverData	********************** 
 
           // Validate Driver warehouse

           $getDriverTsData = new getTripSheetDriverClass($dbConn);
           $result = $getDriverTsData->getTripSheetDriverDetails(trim($uCred[0]['pv_uid']),
                                                                 trim($JSON['tripsheetnumber']),
                                                                 trim($JSON['requireddata']));
           
           } elseif(trim($JSON['requireddata']) == 'getChangeDriverDetails') {
// ********************** 14 * changeDriverDetails********************** 
 
           // Validate Driver warehouse

           $getDriverTsData = new getTripSheetDriverClass($dbConn);
           $result = $getDriverTsData->getChangeDriverDetails(trim($JSON['tripsheetnumber']));
           

           } elseif(trim($JSON['requireddata']) == 'submitNewDriver') {
// ********************** 15 * submitNewDriverDetails********************** 
 
           // Validate Driver warehouse

           $getDriverTsData = new getTripSheetDriverClass($dbConn);
           $result = $getDriverTsData->updateTripsheetDriver(trim($JSON['tripsheetnumber']),
                                                             trim($JSON['driveruid']),
                                                             trim($uCred[0]['pv_uid']));
                                                             

           } elseif(trim($JSON['requireddata']) == 'submitDriverVerification') {
// ********************** 15 * submitNewDriverDetails********************** 

           file_put_contents($ROOT.'ftp/api2/adjdata' . date("YmdHis") . '.txt', $data);  

           $arraySize = count($JSON['questionArr'],1);
           $getDriverTsData = new getTripSheetDriverClass($dbConn);
           $result = $getDriverTsData->validateDriverQuestions($arraySize, 
                                                               trim($uCred[0]['pv_uid']),
                                                               trim(strtoupper($JSON['requireddata'])),
                                                               '712');
           
           if($result == 'N') {
           	
                 echo json_encode( ["resultStatus"  =>'E',
                                    "ResultCode"    =>'712' , 
                                    "resultMessage" =>'No Question Answers'
                                   ] );
                 exit; // !! NB !!                  
           }
           
           $getDriverTsData = new getTripSheetDriverClass($dbConn);
           $result = $getDriverTsData->saveDriverQuestions(trim($uCred[0]['pv_uid']), 
                                                           trim(strtoupper($JSON['requireddata'])), 
                                                           $errorcode, 
                                                           $JSON['questionArr']);

           } elseif(trim($JSON['requireddata']) == 'postStockAdjIncrease' || trim($JSON['requireddata']) == 'postStockAdjDecrease' || trim($JSON['requireddata']) == 'postArrival' || trim($JSON['requireddata']) == 'postReDelArrival') {

                 // ********************** 16 * postAdjustment	**********************
                 
                 file_put_contents($ROOT.$PHPFOLDER.'log/apiAdjustment' . date("ymd") . '.txt', print_r($JSON, TRUE), FILE_APPEND);  

	               $newApiDAO = new APIDAO($dbConn);
                 $errorTO   = $newApiDAO->jsonDataValidation(trim($uCred[0]['pv_uid']),
                                                             trim(strtoupper($JSON['requireddata'])),
                                                             trim($JSON['principalId']),
                                                             '',
                                                             '',
                                                             '',
                                                             '',
                                                             trim($JSON['orderReference']));
                  if($errorTO->type == 'E') {
                 	
                 	          $validationErrType        = $errorTO->type;
                 	          $validationErrDescription = $errorTO->description;
                 	          $validationErrIdentifier  = $errorTO->identifier;
                 	          

                            $newApiDAO = new APIDAO($dbConn);
                            $errorTO = $newApiDAO->addVendorUserlogEntry(trim($uCred[0]['pv_uid']), 
                                                                     $errorTO->type, 
                                                                     trim($JSON['requireddata']),
                                                                     $errorTO->identifier);

                             echo json_encode( ["resultStatus"  =>$validationErrType,
                                                "ResultCode"    =>$validationErrIdentifier , 
                                   	            "resultMessage" =>$validationErrDescription
                                               ] );
                             exit; // !! NB !!
                  }
                	
                  if(count($JSON['detailLines']) == 0) {

                       $newApiDAO = new APIDAO($dbConn);
                       $errorTO = $newApiDAO->addVendorUserlogEntry(trim($uCred[0]['pv_uid']), 
                                                               'E', 
                                                               trim($JSON['requireddata']),
                                                               '716');

                 	     echo json_encode( ["resultStatus"  =>'E',
                                          "ResultCode"    =>'716' , 
                             	            "resultMessage" =>'Stock Movement has no Detail Lines'
                                         ] );
                       exit; // !! NB !!                  
                
                  } else {
                        	
                          // Get all the 'Parts' to create Stock Movement
                          // Get allowed warehouse
                          
                          if(trim($JSON['requireddata']) == 'postReDelArrival') {
                                  $allowedWh = trim($JSON['reDelWarehouse']);  	
                                  $allowedWareHouse = $allowedWh;
                        	        $userId = trim($JSON['userUid']);                       	
                          } else {
                                  $CreateStockMovementDAO = new CreateStockMovementDAO($dbConn);
                                  $allowedWh = $CreateStockMovementDAO->getUserStockMovementDepot($uCred[0]['pv_uid']);
                                  $allowedWareHouse = $allowedWh[0]['allowed_warehouses'];
                        	        $userId = $allowedWh[0]['userId'] ;
                          }
                         
                          $CreateStockMovementDAO = new CreateStockMovementDAO($dbConn);
                          $allowedWh = $CreateStockMovementDAO->getUserStockMovementDepot($uCred[0]['pv_uid']);  
                             
                          if(count($allowedWh) == 0 || $allowedWh = NULL ) {
                                   $newApiDAO = new APIDAO($dbConn);
                                   $errorTO = $newApiDAO->addVendorUserlogEntry(trim($uCred[0]['pv_uid']), 
                                                                                'E', 
                                                                                 trim($JSON['requireddata']),
                                                                                 '716');

                 	                 echo json_encode( ["resultStatus"  =>'E',
                                                      "ResultCode"    =>'825' , 
                                                      "resultMessage" =>'Failed to Create Stock Movement - Warehouse'
                                               ] );
                                   exit; // !! NB !!                  
                           }
                   
                        	   
                           if(strtoupper(trim($JSON['requireddata'])) == 'POSTSTOCKADJINCREASE') {
                                  $doctype = DT_STOCKADJUST_POS;
                                  $movementDesc = 'Adjustment Increase';
                           } elseif(strtoupper(trim($JSON['requireddata'])) == 'POSTSTOCKADJDECREASE') {
                                  $doctype = DT_STOCKADJUST_NEG;
                                  $movementDesc = 'Adjustment Decrease';
                           } elseif(strtoupper(trim($JSON['requireddata'])) == 'POSTARRIVAL') {
                                  $doctype = DT_ARRIVAL;
                                  $movementDesc = 'Arrival';
                           } elseif(strtoupper(trim($JSON['requireddata'])) == 'POSTREDELARRIVAL') {
                                  $doctype = DT_ARRIVAL;
                                  $movementDesc = 'Arrival';
                           }
                             
                           $ProcessTransactionsFromApi = New ProcessTransactionsFromApi;
                           $result = $ProcessTransactionsFromApi->postStockMovementFromAPI($data, $allowedWareHouse, $userId, $doctype , 'SCAN');

                  }                    
                  if($result->type == 'E') {
                    	   $resultLog  = 'E';
                    	   $resultCode = $result->type;
                    	   $resultMessage = $result->description;
                    	   $returnArr = $result->description; 
                  }  else {
                    	   $resultLog  = 'S';
                    	   $resultCode = '000';
                    	   $resultMessage = 'Successfully processeed. Your ' . $movementDesc . ' has been loaded'; 
                    	   $returnArr = '';
                  } 
                  $newApiDAO = new APIDAO($dbConn);
                  $errorTO = $newApiDAO->addVendorUserlogEntry(trim($uCred[0]['pv_uid']), 
                                                                    $resultLog, 
                                                                    trim($JSON['requireddata']),
                                                                    $resultCode);  
                  echo json_encode(["resultStatus"  => $resultLog,
                                    "ResultCode"    => $resultCode,
                                    "resultMessage" => $resultMessage,
                                    "data"          => $returnArr]);
                                      
                  file_put_contents($ROOT.'ftp/api2/tapiorder' . test_input(trim($JSON['reference_number'])) . date("YmdHis") . '.txt', $data);  
                  // file_put_contents($ROOT.'ftp/api2/tapiorderBD' . test_input(trim($JSON['reference_number'])) . date("YmdHis") . '.txt', $resultMessage);

           } elseif(trim($JSON['requireddata']) == 'getTripSheetInvoice') {
// ********************** 15 * 'getTripSheetInvoice'********************** 
 
                // Validate Driver warehouse

                $getDriverTsData = new getTripSheetDriverClass($dbConn);
                $result = $getDriverTsData->getTripSheetInvoice(trim($uCred[0]['pv_uid']),
                                                                trim(strtoupper($JSON['requireddata'])), 
                                                                trim($JSON['tripsheetnumber']),
                                                                trim($JSON['invoicenumber']));
                                                                


           } elseif(trim($JSON['requireddata']) == 'getInvoiceProduct') {
// ********************** 16 * getInvoiceProduct********************** 

                $getDriverTsData = new getTripSheetDriverClass($dbConn);
                $result = $getDriverTsData->getInvoiceProduct(trim($uCred[0]['pv_uid']),
                                                              trim(strtoupper($JSON['requireddata'])), 
                                                              trim($JSON['invoiceUid']),
                                                              trim($JSON['invoicenumber']),
                                                              trim(test_input($JSON['scanproduct'])));
           } elseif(trim($JSON['requireddata']) == 'submitInvoiceProduct') {
// ********************** 16 * submitInvoiceProduct********************** 

                file_put_contents($ROOT.'ftp/api2/invProdData' . date("YmdHis") . '.txt', $data);  
                
                $getDriverTsData = new getTripSheetDriverClass($dbConn);
                $result = $getDriverTsData->submitInvoiceProduct(trim($uCred[0]['pv_uid']),
                                                                 trim(strtoupper($JSON['requireddata'])), 
                                                                 trim($JSON['invoicenumber']),
                                                                 trim($JSON['invoiceUid']),
                                                                 trim($JSON['detailUid']),
                                                                 trim(test_input($JSON['scanproduct'])),
                                                                 trim(test_input($JSON['confirmedqty'])));


           } elseif(trim($JSON['requireddata']) == 'confirmTripSheetDispatch') {
// ********************** 16 * submitInvoiceProduct********************** 

                file_put_contents($ROOT.'ftp/api2/confirmData' . date("YmdHis") . '.txt', $data);  
                
                $getDriverTsData = new getTripSheetDriverClass($dbConn);
                $result = $getDriverTsData->confirmTripSheetDispatch(trim($uCred[0]['pv_uid']),
                                                                     trim(strtoupper($JSON['requireddata'])), 
                                                                     trim($JSON['tripsheetnumber']));

           } elseif(trim($JSON['requireddata']) == 'getTripSheetStores') {
           	
           	$newApiDAO = new APIDAO($dbConn);
            $retreg = $newApiDAO->checkVehicleReg($JSON['vehiclereg']);
            
            if(count($retreg) <> 1) {
            	        echo json_encode( ["resultStatus"  =>'E',
                                         "ResultCode"    =>'761' , 
                                         "resultMessage" =>'Vehicle Registration Not Valid'
                                   ] );
                 exit; // !! NB !!           	
            }
            
           	$newApiDAO = new APIDAO($dbConn);
            $retTs = $newApiDAO->getDriverTripSheetNumber($JSON['vehiclereg']); 
            
            $tsUID = $retTs[0]['tsUid'] ;
            
            if(count($retTs) > 1) {
            	        echo json_encode( ["resultStatus"  =>'E',
                                         "ResultCode"    =>'762' , 
                                         "resultMessage" =>'More Than One TripSheet Found'
                                   ] );
                 exit; // !! NB !!           	
            } elseif(count($retTs) < 1) {
            	        echo json_encode( ["resultStatus"  =>'E',
                                         "ResultCode"    =>'762' , 
                                         "resultMessage" =>'No TripSheet Found'
                                   ] );
                 exit; // !! NB !!           	
            }           	           	

            $newApiDAO = new APIDAO($dbConn);
            $retTsArr  = $newApiDAO->getDriverTripSheetDetails($tsUID);
            
            $driverArray = array();
            $newDriverArray = array();
            
            $stStore = '';
            $docList = '';
            $noDocs  = 0;
            
            foreach($retTsArr as $row) {
                 if($stStore <> $row['WsmUid']) {
                 	    if($stStore <> '') {
                 	    	 $driverArray['DocList'] = substr_replace($docList ,"",-1);
                 	       array_push($newDriverArray,$driverArray);
                 	       $docList = '';
                      }
                 	    if($row['WsmUid']) {
                          $cstore = $row['WsmUid'];
                 	    }
                      $driverArray['WsmUid']      = $row['WsmUid'];
                      $driverArray['Driver']      = $row['Driver'];
                      $driverArray['VehicleReg']  = $row['VehicleReg'];
                      $driverArray['Depot']       = $row['Depot'];
                      $driverArray['Store']       = $row['Store']; 
                      $driverArray['TripSheetNo'] = $row['TripSheetNo']; 
                      $driverArray['Coordinates'] = $row['Coordinates'];
                      if($row['Coordinates']) {
                          $stStore = $cstore	;
                      }
                  }
                  $docList = $docList . $row['DocumentNumber'] . "|";
             }
             $driverArray['DocList'] = substr_replace($docList ,"",-1);
             array_push($newDriverArray,$driverArray);
 
             echo json_encode( [
                               "resultStatus" =>'S',
                               "ResultCode"    =>'000' ,
                               "data" => $newDriverArray,
                               "resultMessage"=>"Successful"
                               ] );  
           } elseif(trim($JSON['requireddata']) == 'confirmDeliveryInfo') {
// ********************** 16 * Confirm Delivery Info********************** 
             file_put_contents($ROOT.'ftp/api2/deliveryinfo' . date("YmdHis") . '.txt', $data);   

             // Write data to data table
                         
             $newApiDAO = new APIDAO($dbConn);
             $errorTO  = $newApiDAO->insertDriverData($data);             
             if($errorTO->type == 'S') {
             	         echo json_encode( [
                                          "resultStatus" =>'S',
                                          "ResultCode"    =>'000' ,
                                          "data" => $errorTO,
                                          "resultMessage"=>"Successful-"
                                          ] );
             } elseif($errorTO->type == 'E') {
             	         echo json_encode( [
                                          "resultStatus" =>'E',
                                          "ResultCode"    =>'000' ,
                                          "data" => $errorTO,
                                          "resultMessage"=>"Bomb OUT-"
                                          ] );             	
             	
             }  else {
             	     echo json_encode( [
                                          "resultStatus" =>'E',
                                          "ResultCode"    =>'000' ,
                                          "data" => $errorTO,
                                          "resultMessage"=>"Oh Shit-"
                                          ] );  
             }
           }  
//*********************************************************************************************************************************8
              elseif(trim($JSON['requireddata']) == 'confirmVehicleInspection') {
// ********************** 16 * Confirm Delivery Info********************** 
             file_put_contents($ROOT.'ftp/api2/VehicleInspection' . date("YmdHis") . '.txt', $data);    

                       
             	         echo json_encode( [
                                          "resultStatus" =>'S',
                                          "ResultCode"    =>'000' ,
                                          "data" => $errorTO,
                                          "resultMessage"=>"Successful-"
                                          ] );
             
             } 
             
             
                                         
            else {      	
             $newApiDAO = new APIDAO($dbConn);
                            $errorTO = $newApiDAO->addVendorUserlogEntry(trim($uCred[0]['pv_uid']), 
                                                                     'E', 
                                                                     trim($JSON['requireddata']),
                                                                     '700');
 
                echo json_encode( [
                                   "resultStatus" =>'E',
                                   "ResultCode"    =>'700' ,
                                   "resultMessage"=>"Sorry, Request is not recognised"
                                  ] );
               
                exit; // !! NB !!  
           }     
}

 function test_input($data) {

  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  
  return $data;
 }