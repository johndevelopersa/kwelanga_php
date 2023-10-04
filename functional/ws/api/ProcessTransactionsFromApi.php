<?php 
include_once('ROOT.php'); 
include_once($ROOT.'PHPINI.php');	
include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT . $PHPFOLDER . 'DAO/CreateStockMovementDAO.php');
include_once($ROOT . $PHPFOLDER . 'DAO/CreateTransactionDAO.php');
include_once($ROOT . $PHPFOLDER . 'TO/PostingDocumentTO.php');  
include_once($ROOT . $PHPFOLDER . 'TO/PostingDocumentDetailTO.php');
include_once($ROOT . $PHPFOLDER . 'DAO/StockDAO.php');   

class ProcessTransactionsFromApi {
	
      function __construct() {

         global $ROOT, $PHPFOLDER, $dbConn;
         $this->dbConn = $dbConn;
      }	

// ********************************************************************************************************************************************************
      public function postStockMovementFromAPI($data, $allowedWareHouse, $userId, $docType, $dataSource) {
      
            $JSON = json_decode($data, true);
            
            if($docType == DT_ARRIVAL) {
                 $stockDAO = new StockDAO($this->dbConn);
          
                 $stockMode = $stockDAO->checkStockMode(trim($JSON['principalId']), $allowedWareHouse);
                 if($stockMode){
                      $this->errorTO->type = FLAG_ERRORTO_ERROR;
                      $this->errorTO->description = "This depot - principal is in stock take mode, please try again later!";
                      $this->errorTO->identifier = '830';
                      return $this->errorTO;
                 }            	
            }
            
            $CreateStockMovementDAO = new CreateStockMovementDAO($this->dbConn);
            $arrivalStore           = $CreateStockMovementDAO->getStockMovementStore(trim($JSON['principalId']), $allowedWareHouse, $docType );
            
            $psmUid = $arrivalStore[0]['psmUid'];
                      
            
            if(trim($psmUid) == '') {
                   $CreateStockMovementDAO = new CreateStockMovementDAO($this->dbConn);
                   $errorTO     = $CreateStockMovementDAO->insertStockMovementStore(trim($JSON['principalId']), $allowedWareHouse, $userId, $docType) ;            
                   $psmUid = $errorTO->identifier; 
            }
                 	   
            $CreateTransactionDAO = new CreateTransactionDAO($this->dbConn);
            $result               = $CreateTransactionDAO->getStartStatus($allowedWareHouse);
            $txStart = $result;
            $CreateTransactionDAO = new CreateTransactionDAO($this->dbConn);
            $result               = $CreateTransactionDAO->getdocumentSequences(trim($JSON['principalId']), 
                                                                                LITERAL_SEQ_DOCUMENT_NUMBER,
                                                                                $docType,
                                                                                $allowedWareHouse,
                                                                                $dataSource);
                                                                                                 
            $docNumber = $result; 
            
            $CreateTransactionDAO = new CreateTransactionDAO($this->dbConn);
            $result               = $CreateTransactionDAO->getdocumentSequences(trim($JSON['principalId']), 
                                                                                LITERAL_SEQ_ORDER,
                                                                                '',
                                                                                '',
                                                                                '');
                             
            $docseq = $result;

            foreach($JSON as $key => $row) {

                 if($key == 'username') {
                       $PostingDocumentTO = New PostingDocumentTO;
                       $PostingDocumentTO->documentNumber      = $docNumber;
                       $PostingDocumentTO->documentTypeUId     = $docType ;
                       $PostingDocumentTO->processedDate       = gmdate(GUI_PHP_DATE_FORMAT);
                       $PostingDocumentTO->processedTime       = gmdate(GUI_PHP_TIME_FORMAT);
                       $PostingDocumentTO->depotUId            = $allowedWareHouse;
                       $PostingDocumentTO->orderSequenceNo     = $docseq;
                       $PostingDocumentTO->version             = "1";
                       $PostingDocumentTO->documentStatusUId   = $txStart;
                       $PostingDocumentTO->principalStoreUId   = $psmUid ;
                       $PostingDocumentTO->capturedBy          = $row;
                 }
                 
                 if($key == 'principalId') {
                       $PostingDocumentTO->principalUId        = $row;
                 }
                 
                 if($key == 'orderDate') {
                 	     if(substr($row,0,3) == '202') {
                 	     	    $oDate = substr($row,0,4) . '-' . substr($row,5,2) . '-' . substr($row,8,2);
                 	     } else {
                 	     	    $oDate = substr($row,6,4) . '-' . substr($row,3,2) . '-' . substr($row,0,2) ;
                 	     }
                 	
                       $PostingDocumentTO->orderDate           = $oDate;
                       $PostingDocumentTO->invoiceDate         = $oDate;
                 
                       $PostingDocumentTO->deliveryDate           = trim($oDate);
                       $PostingDocumentTO->requestedDeliveryDate  = trim($oDate);
                       $PostingDocumentTO->deliveryDueDate        = trim($oDate);                 
                 
                 
                 }                             	     
                 if($key == 'orderReference') {
                       $PostingDocumentTO->customerOrderNumber =  $row;
                       $PostingDocumentTO->cases               = 0;
                       $PostingDocumentTO->sellingPrice        = 0;
                       $PostingDocumentTO->exclusiveTotal      = 0;
                       $PostingDocumentTO->vatTotal            = 0;
                       $PostingDocumentTO->invoiceTotal        = 0;
                       $PostingDocumentTO->dataSource          = $dataSource;
                 }
                 $lineNo = 1;
                        
                 if($key == 'detailLines') {

                       foreach($row as $dRow) {
                       	
                       	    if(trim($dRow['quantity']) == '') {
                       	         $qRow = '0';
                       	    } else {
                       	    	   $qRow = $dRow['quantity'];
                       	    }
                          
                            $PostingDocumentDetailTO = New PostingDocumentDetailTO;
                            $PostingDocumentDetailTO->lineNo        = $lineNo;
                            $PostingDocumentDetailTO->productUId    = $dRow['productUid'];
                            $PostingDocumentDetailTO->orderedQty    = $qRow;
                            $PostingDocumentDetailTO->documentQty   = $qRow;
                            $PostingDocumentDetailTO->deliveredQty  = $qRow;
                            $PostingDocumentDetailTO->sellingPrice  = 0;
                            $PostingDocumentDetailTO->discountValue = 0;
                            $PostingDocumentDetailTO->netPrice      = 0;
                            $PostingDocumentDetailTO->extendedPrice = 0;
                            $PostingDocumentDetailTO->vatAmount     = 0;
                            $PostingDocumentDetailTO->vatRate       = 0;
                            $PostingDocumentDetailTO->total         = 0;
                          
                            $PostingDocumentTO->detailArr[] = $PostingDocumentDetailTO;
                     
                            $lineNo++;
                        }
                            
                 }         
            }
            
            $CreateTransactionDAO = new CreateTransactionDAO($this->dbConn);
            $result               = $CreateTransactionDAO->createTransaction($PostingDocumentTO);
            
            if($result == FLAG_ERRORTO_ERROR) {
            	    return $result;            	    
            }
            $CreateStockMovementDAO = new CreateStockMovementDAO($this->dbConn);
            $result           = $CreateStockMovementDAO->stockMovementStockTransaction($PostingDocumentTO); 
            return $result;     
      
      }

// ********************************************************************************************************************************************************

      }
?>