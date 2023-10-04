<?php 
include_once('ROOT.php'); 
include_once($ROOT.'PHPINI.php');	
include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT . $PHPFOLDER . 'DAO/ExceptionThrower.php');
include_once($ROOT.$PHPFOLDER.'DAO/AdministrationDAO.php');	
include_once($ROOT.$PHPFOLDER.'DAO/ApiDAO.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER.'TO/ApiReturnTO.php');
include_once($ROOT.$PHPFOLDER.'TO/DriverQuestionTO.php');

class getTripSheetDriverClass {
	
      function __construct() {

         global $ROOT, $PHPFOLDER, $dbConn;
         $this->dbConn = $dbConn;
         $this->errorTO = new ErrorTO;
      }	
// ********************************************************************************************************************************************************
      public function getTripSheetDriverDetails($pv_uid, $tripSheetNumber, $requiredData) {
      	
      	   // Validate Tripsheet number structure
      	   
      	   if(strpos($tripSheetNumber, '-') == 0) {
                  $newApiDAO = new APIDAO($this->dbConn);
                  $this->errorTO = $newApiDAO->addVendorUserlogEntry(trim($pv_uid), 
                                                               "E", 
                                                               trim($requiredData),
                                                               '707');
                       	     echo json_encode( ["resultStatus"  =>"E",
                       	                        "ResultCode"    =>'707' ,
                                                "resultMessage" =>"Trip Sheet number not Valid    - Cannot Continue"
                                               ] );
                             exit; // !! NB !!
      	   	
      	   }
      	   
           $depotId  = trim(substr($tripSheetNumber,0, strpos($tripSheetNumber,"-") -1));
           $tsNumber = trim(substr($tripSheetNumber,strpos($tripSheetNumber,"-") + 1,10));
      	   
           $newApiDAO = new APIDAO($this->dbConn);
           $aVal      = $newApiDAO->validateUserTripSheetDepot($pv_uid);
           
           if($aVal[0]['allowed_warehouses'] <> $depotId) {
                  $newApiDAO = new APIDAO($this->dbConn);
                  $this->errorTO = $newApiDAO->addVendorUserlogEntry(trim($pv_uid), 
                                                               "E", 
                                                               trim($requiredData),
                                                               '708');
                       	     echo json_encode( ["resultStatus"  =>"E",
                       	                        "ResultCode"    =>'708' ,
                                                "resultMessage" =>"Invalid User Depot    - Cannot Continue"
                                               ] );
                             exit; // !! NB !!
           }     
           $newApiDAO = new APIDAO($this->dbConn);
           $aVal      = $newApiDAO->getDriverTsDetail($depotId, $tsNumber, $pv_uid);
                                                       
           if(count($aVal) == 0)	{
                       	
                  $newApiDAO = new APIDAO($this->dbConn);
                  $this->errorTO = $newApiDAO->addVendorUserlogEntry(trim($pv_uid), 
                                                               "E", 
                                                               trim($requiredData),
                                                               '710');
                       	     echo json_encode( ["resultStatus"  =>"E",
                       	                        "ResultCode"    =>'710' ,
                       	     	     	            "resultMessage" =>"No tripsheet found for this user    - Cannot Continue"
                       	     	     	           ] );
                             exit; // !! NB !!
           } else {
           	
                  $returnArr = []; 
                  // Count Documents on Tripsheet
                  $newApiDAO = new APIDAO($this->dbConn);
                  $dQuest    = $newApiDAO->getTripSheetQuestions($depotId);
                  
                  $returnArr = [
                                  'depot'           => $aVal[0]['Depot'],
                                  'tripsheetnumber' => $aVal[0]['TripSheetNumber'],
                                  'driveruid'       => $aVal[0]['DriverUid'],
                                  'drivername'      => $aVal[0]['DriverName'],
                                  'NumofInvoices'   => $aVal[0]['no_documents']
                                 ];
                                
                  foreach($dQuest as $r) {
                        $questionArray[] =[ 
                                           'questionUid'     => $r['ddq_uid'],
                                           'question'        => $r['question'],
                                           'answer'          => $r['answer'],
                                           'questionNumber'  => $r['question_number'],
                                          ];                                          
                  }
                  $returnArr[]->questionArr = $questionArray;
                  
           // send JSON back to the client :
           }           
           $newApiDAO = new APIDAO($this->dbConn);
           $this->errorTO = $newApiDAO->addVendorUserlogEntry(trim($pv_uid), 
                                                       "E", 
                                                       trim($requiredData),
                                                        '000');
                                                                  
                                           
           echo json_encode(["resultStatus" => "S",
                             "ResultCode"    =>'000' ,
                             "resultMessage" => "Successfully retrieved data",
                             "data" => $returnArr
                            ]);	                
      }       
// ********************************************************************************************************************************************************
      public function getChangeDriverDetails($tripSheetNumber) {
      	
           $depotId  = trim(substr($tripSheetNumber,0, strpos($tripSheetNumber,"-") -1));
           $tsNumber = trim(substr($tripSheetNumber,strpos($tripSheetNumber,"-") + 1,10));
           
           $newApiDAO = new APIDAO($this->dbConn);
           $aVal      = $newApiDAO->getChangeDriver($depotId);
                                                       
           if(count($aVal) == 0)	{
                       	
                  $newApiDAO = new APIDAO($this->dbConn);
                  $this->errorTO = $newApiDAO->addVendorUserlogEntry(trim($pv_uid), 
                                                               "E", 
                                                               trim($requiredData),
                                                               '710');
                       	     echo json_encode( ["resultStatus"  =>"E",
                       	                        "ResultCode"    =>"711",
                       	     	     	            "resultMessage" =>"No Change Drivers Available    - Cannot Change Driver"
                       	     	     	           ] );
                             exit; // !! NB !!
           } else {
                  $returnArr = [];
                  $vaildate  = 'Y';
                   foreach ($aVal as $r) {
                         $returnArr[] = [
                                        'drivername'      => $r['name'],
                                        'driveruid'       => $r['uid'], 
                                        'vehicle_reg'     => $r['vehicle_reg']
                                         ];
                                                                 
                       	     // send JSON back to the client :
                   }
                       
           	       $newApiDAO = new APIDAO($this->dbConn);
                  $this->errorTO = $newApiDAO->addVendorUserlogEntry(trim($pv_uid), 
                                                                     "S", 
                                                                      trim($requiredData),
                                                                      '000');
                                                                          
                                                   
                   echo json_encode(["resultStatus" => "S",
                                     "ResultCode"    =>'000' ,
                                     "resultMessage" => "Successfully retrieved data",
                                     "data" => $returnArr
                                    ]);	
           } 
      }               
// ********************************************************************************************************************************************************
      public function updateTripsheetDriver($tripSheetNumber, $driverId, $pv_uid) {
      	
           $depotId  = trim(substr($tripSheetNumber,0, strpos($tripSheetNumber,"-") -1));
           $tsNumber = trim(substr($tripSheetNumber,strpos($tripSheetNumber,"-") + 1,10));
           
           $newApiDAO = new APIDAO($this->dbConn);
           $this->errorTO  = $newApiDAO->updateChangeDriver($depotId, $tsNumber, $driverId);
           
           if($this->errorTO->type<>FLAG_ERRORTO_SUCCESS) {
                  
                  $newApiDAO = new APIDAO($this->dbConn);
                  $this->errorTO = $newApiDAO->addVendorUserlogEntry(trim($pv_uid), 
                                                                    "E", 
                                                                     trim($requiredData),
                                                                     "711");
                       	     echo json_encode( ["resultStatus"  =>"E",
                       	                        "ResultCode"    =>"711",
                       	     	     	            "resultMessage" =>"Update Driver Failed  - Cannot Change Driver"
                       	     	     	           ] );
                             exit; // !! NB !!
           } 
           
           $returnArr = [];
   	       $newApiDAO = new APIDAO($this->dbConn);
           $this->errorTO = $newApiDAO->addVendorUserlogEntry(trim($pv_uid), 
                                                              "S", 
                                                              trim($requiredData),
                                                              '000');
                                                                          
                                                   
                   echo json_encode(["resultStatus" => "S",
                                     "ResultCode"    =>'000' ,
                                     "resultMessage" => "Successfully Updated Driver",
                                     "data" => $returnArr
                                    ]);	
      }


// ********************************************************************************************************************************************************
      public function validateDriverQuestions($detailLines, $pv_uid, $requireddata, $errorcode) {
      	
                if($detailLines == 0) {

                     $newApiDAO = new APIDAO($this->dbConn);
                      $errorTO = $newApiDAO->addVendorUserlogEntry($pv_uid, 
                                                                   'E', 
                                                                    trim($requireddata),
                                                                   $errorcode);
                      $hasQuestions = 'N';
                } else {
                      $hasQuestions = 'Y';	
                }                                                                                                   
                return $hasQuestions;
      }


// ********************************************************************************************************************************************************
      public function saveDriverQuestions($pv_uid, $requireddata, $errorcode, $detailLines) {     	
      	   
//      	   echo "<br>";
//      	   echo "<pre>";
//      	   print_r($detailLines);
//      	   
//      	   echo "<br>";       	   
      	
           $returnArr = [];
   	       $newApiDAO = new APIDAO($this->dbConn);
           $this->errorTO = $newApiDAO->addVendorUserlogEntry(trim($pv_uid), 
                                                              "S", 
                                                              trim($requiredData),
                                                              '000');
                                                                          
                                                   
                   echo json_encode(["resultStatus" => "S",
                                     "ResultCode"    =>'000' ,
                                     "resultMessage" => "Successfully Loaded Driver Questions",
                                     "data" => $returnArr
                                    ]);	
      }
      
// ********************************************************************************************************************************************************
      public function getTripSheetInvoice($pv_uid, $requireddata, $tripSheetNumber, $invoiceNumber) {
      	
           $depotId  = trim(substr($tripSheetNumber,0, strpos($tripSheetNumber,"-") -1));
           $tsNumber = trim(substr($tripSheetNumber,strpos($tripSheetNumber,"-") + 1,10));
      	
           $principalId  = trim(substr($invoiceNumber,0, strpos($invoiceNumber,"-") -1));
           $invNumber = trim(substr($invoiceNumber,strpos($invoiceNumber,"-") + 1,10));      	
      	
           $newApiDAO = new APIDAO($this->dbConn);
           $result = $newApiDAO->getTripSheetInvoice($depotId,
                                                     $tsNumber,
                                                     $principalId,
                                                     $invNumber);
           
           $returnArr = [];

           if($result[0]['documentNumber'] == NULL || $result[0]['noLinesOnInvoice'] == 0 ) {
                  $newApiDAO = new APIDAO($this->dbConn);
                  $this->errorTO = $newApiDAO->addVendorUserlogEntry(trim($pv_uid), 
                                                                    "E", 
                                                                     trim($requiredData),
                                                                     "713");
                       	     echo json_encode( ["resultStatus"  =>"E",
                       	                        "ResultCode"    =>"713",
                       	     	     	            "resultMessage" =>"Invoice Not found on Trip Sheet or No Details"
                       	     	     	           ] );
                             exit; // !! NB !!           	

           }
           $returnArr[] = [
                           "invoiceUid"       => $result[0]['invoiceUid'],
                           "documentNumber"   => $result[0]['documentNumber'],
                           "invoiceDate"      => $result[0]['invoiceDate'],
                           "poNumber"         => $result[0]['poNumber'],
                           "storeName"        => $result[0]['storeName'],
                           "noLinesOnInvoice" => $result[0]['noLinesOnInvoice']
                          ];

                   echo json_encode(["resultStatus" => "S",
                                     "ResultCode"    =>'000' ,
                                     "resultMessage" => "Successfully Sent Trip Sheet Invoice Details",
                                     "data" => $returnArr
                                    ]);	      	
      }
// ********************************************************************************************************************************************************
      public function getInvoiceProduct($pv_uid, $requireddata, $docUid, $invoiceNumber, $prodCode) {
      	
           $principalId  = trim(substr($invoiceNumber,0, strpos($invoiceNumber,"-") -1));
           $invNumber    = trim(substr($invoiceNumber,strpos($invoiceNumber,"-") + 1,10));       	

           $newApiDAO = new APIDAO($this->dbConn);
           $result = $newApiDAO->getTripSheetInvoiceProduct($docUid, 
                                                            $principalId, 
                                                            $invNumber, 
                                                            $prodCode);
           
           $returnArr = [];

           if(count($result) == 0 ) {
                  $newApiDAO = new APIDAO($this->dbConn);
                  $this->errorTO = $newApiDAO->addVendorUserlogEntry(trim($pv_uid), 
                                                                    "E", 
                                                                     trim($requiredData),
                                                                     "713");
                       	     echo json_encode( ["resultStatus"  =>"E",
                       	                        "ResultCode"    =>"714",
                       	     	     	            "resultMessage" =>"Product could not be Identified"
                       	     	     	           ] );
                             exit; // !! NB !!           	

           }           
           $returnArr[] = [
                           "detailUid"           => $result[0]['detailUid'],
                           "productUid"          => $result[0]['prodUid'],
                           "productCode"         => $result[0]['prodCode'],
                           "productDescription"  => $result[0]['prodDesc'],
                           "qrderedQty"          => $result[0]['orderedQty'],
                           "availableStock"      => $result[0]['availStock']
                          ];

                   echo json_encode(["resultStatus" => "S",
                                     "ResultCode"    =>'000' ,
                                     "resultMessage" => "Successfully Returned Product Details",
                                     "data" => $returnArr
                                    ]);	     	
      }
// ********************************************************************************************************************************************************
      public function submitInvoiceProduct($pv_uid, 
                                           $requireddata,
                                           $invoiceNumber, 
                                           $docUid,
                                           $detailUid,
                                           $prodCode,
                                           $confirmQty) {
                                           	
      // Validate Line already invoiced
      
             $newApiDAO = new APIDAO($this->dbConn);
             $retFirst = $newApiDAO->validateInvoiceProductFirst($detailUid);
             
             if(count($retFirst) > 0) {
                  $newApiDAO = new APIDAO($this->dbConn);
                  $this->errorTO = $newApiDAO->addVendorUserlogEntry(trim($pv_uid), 
                                                                    "E", 
                                                                     trim($requiredData),
                                                                     "715");
                       	     echo json_encode( ["resultStatus"  =>"E",
                       	                        "ResultCode"    =>"715",
                       	     	     	            "resultMessage" =>"Line Already Invoiced"
                       	     	     	           ] );
                             exit; // !! NB !!
             }
      // Validate confirmed Qty      
                                           	
             $newApiDAO = new APIDAO($this->dbConn);
             $retSecond = $newApiDAO->validateInvoiceProductSecond($detailUid) ;
             
             if($retSecond[0]['ordered_qty'] < mysqli_real_escape_string($this->dbConn->connection, $confirmQty)) {
                    $newApiDAO = new APIDAO($this->dbConn);
                    $this->errorTO = $newApiDAO->addVendorUserlogEntry(trim($pv_uid), 
                                                                       "E", 
                                                                       trim($requiredData),
                                                                       "715");
                       	       echo json_encode( ["resultStatus"  =>"E",
                       	                          "ResultCode"    =>"715",
                       	     	     	              "resultMessage" =>"Confirmed Qty Exceeds Ordered Qty"
                       	     	     	             ] );
                               exit; // !! NB !!
             }   	
      // Validate Available stock Qty      
                                           	
             $newApiDAO = new APIDAO($this->dbConn);
             $retSecond = $newApiDAO->validateInvoiceProductThird($detailUid) ;
             
             if($retSecond[0]['closing'] - mysqli_real_escape_string($this->dbConn->connection, $confirmQty) < 0) {
                    $newApiDAO = new APIDAO($this->dbConn);
                    $this->errorTO = $newApiDAO->addVendorUserlogEntry(trim($pv_uid), 
                                                                       "E", 
                                                                       trim($requiredData),
                                                                       "715");
                       	       echo json_encode( ["resultStatus"  =>"E",
                       	                          "ResultCode"    =>"715",
                       	     	     	              "resultMessage" =>"Confirmed Qty Exceeds Ordered Qty"
                       	     	     	             ] );
                               exit; // !! NB !!
             }   	      	
      // Insert into detail temp
             
             $newApiDAO = new APIDAO($this->dbConn);
             $this->errorTO = $newApiDAO->insertIntoDocumentPend($detailUid,
                                                                 $docUid,
                                                                 $confirmQty) ;     
             
             if($this->errorTO->type<>FLAG_ERRORTO_SUCCESS) {
                  
                  $newApiDAO = new APIDAO($this->dbConn);
                  $this->errorTO = $newApiDAO->addVendorUserlogEntry(trim($pv_uid), 
                                                                    "E", 
                                                                     trim($requiredData),
                                                                     "717");
                       	     echo json_encode( ["resultStatus"  =>"E",
                       	                        "ResultCode"    =>"717",
                       	     	     	            "resultMessage" =>"Insert Invoice Product Failed"
                       	     	     	           ] );
                             exit; // !! NB !!
             } 
           
             $returnArr = [];
   	         $newApiDAO = new APIDAO($this->dbConn);
           
             $this->errorTO = $newApiDAO->addVendorUserlogEntry(trim($pv_uid), 
                                                                "S", 
                                                                trim($requiredData),
                                                                '000');
                                                                          
                                                   
                     echo json_encode(["resultStatus" => "S",
                                       "ResultCode"    =>'000' ,
                                       "resultMessage" => "Invoice Product Successful",
                                       "data" => $returnArr
                                      ]);	
      }      
// ********************************************************************************************************************************************************
      public function confirmTripSheetDispatch($pv_uid, $requiredData, $tripSheetNumber) {
      	
                     echo json_encode(["resultStatus" => "S",
                                       "ResultCode"    =>'000' ,
                                       "resultMessage" => "Trip Sheet Successfully Dispatched",
                                       "data" => $returnArr
                                      ]);	
      }
 
// ********************************************************************************************************************************************************
 
 
 }