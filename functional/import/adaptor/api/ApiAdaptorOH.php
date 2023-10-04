<?php


include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');
include_once($ROOT.$PHPFOLDER.'DAO/StoreDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/PostMiscellaneousDAO.php');
include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');
include_once($ROOT.$PHPFOLDER.'libs/ValidationCommonUtils.php');
include_once($ROOT.$PHPFOLDER.'TO/PostingStoreTO.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER.'TO/PostingOrdersHoldingTO.php');
include_once($ROOT.$PHPFOLDER.'TO/PostingOrdersHoldingDetailTO.php');
include_once($ROOT.$PHPFOLDER.'TO/PostingBillingInstructionsTO.php');
include_once($ROOT.$PHPFOLDER.'DAO/PostImportDAO.php');

class ApiAdaptorOH  {

    private $dbConn;

    function __construct($dbConn) {
    	$this->dbConn = $dbConn;
    }
// --------------------------------------------------------------------------------------------------------------------------------
// --------------------------------------------------------------------------------------------------------------------------------
    function adaptorAPI_216($content) {
    	
       global $ROOT, $PHPFOLDER;
       
         if(trim($content['username'])=='APITest') {
       
              echo "<br>Into Adaptor<br>";
              echo "<pre>"; 
              print_r($content);

         }
       

             
       foreach($content as $key => $value) {
       	
               if(trim($content['username'])=='APITest') {
                    echo '<br>' . $key . '    ';
                    echo $value;
               }     
       
                 if($key == 'detail_lines') 	{ 
          	           /*******************
                        *   ORDER DETAILS
                        ******************/
          	
          	            foreach($value as $subskey => $subsvalue) {
                               if(trim($content['username'])=='APITest') {
                                    echo '<br>' . $subskey . '    ';
                                    echo $subsvalue;
                               }           	            	
          	            }	 
                 }
       

       }	
       
    }
    
}       
/* 	
       	    if($key == 'id') {
       	    	
                $postingOrdersHoldingTO = new PostingOrdersHoldingTO();
                $postingOrdersHoldingTO->onlineFileProcessingUId = '';
                $postingOrdersHoldingTO->principalUid = 216;
                $postingOrdersHoldingTO->updateProduct="N";
                $postingOrdersHoldingTO->insertProduct="N";
       
                $postingOrdersHoldingTO->vendorUid = "";
                $postingOrdersHoldingTO->dataSource = WP_API;
                $postingOrdersHoldingTO->incomingFile = "";
                $postingOrdersHoldingTO->captureDate = CommonUtils::getGMTime(0);
                $postingOrdersHoldingTO->requestedDeliveryDate = "";
                $postingOrdersHoldingTO->capturedBy = 'WEB';
                $postingOrdersHoldingTO->salesAgentStoreIdentifier = "";
                $postingOrdersHoldingTO->debtorsStoreIdentifier    = "";           
                $postingOrdersHoldingTO->reference = '';
                $postingOrdersHoldingTO->deliveryInstructions = '';
                $postingOrdersHoldingTO->documentTypeUId = DT_ORDINV;	

                $postingOrdersHoldingTO->clientDocumentNo = test_input($subvalue);
                $postingOrdersHoldingTO->documentNo = $postingOrdersHoldingTO->clientDocumentNo;       	    	

                $postingOrdersHoldingTO->storeLookupRef   = '';
                $postingOrdersHoldingTO->enforceSameDepot = "N";
                $postingOrdersHoldingTO->chainLookupRef   = 'GENERIC CHAIN';
                $postingOrdersHoldingTO->oldAccount       = '';
                $postingOrdersHoldingTO->reference        = '';
       	    	
       	    	
       	    	
       	    }

            if($key == 'date_created') 	{       
              	$postingOrdersHoldingTO->orderDate = substr($value,0,10);   
        
                if ($postingOrdersHoldingTO->orderDate === false) {
                    $eTO->type = FLAG_ERRORTO_ERROR;
                    $eTO->description = "Order date invalid format or empty";
                    $eTO->identifier = ET_CUSTOMER;
                    return $eTO;
                }
            }

          if($key == 'billing') 	{
          	  
          	  foreach($value as $subkey => $subvalue) {
          	         if($subkey == 'first_name') {
          	            $firstname =  test_input($subvalue);
          	         }   
          	         if($subkey == 'last_name') {
          	            $lastname = test_input($subvalue);

          	             /*******************
          	             *   CREATE STORE
          	             ******************
          	             $postingStoreTO = new PostingStoreTO;
          	             $postingStoreTO->DMLType = "INSERT";
          	             $postingStoreTO->principalStoreUId ="";
          	             $postingStoreTO->principal = $postingOrdersHoldingTO->principalUid;
                         
          	             $postingStoreTO->depot = ""; // this will be set by the processing script
          	             $postingStoreTO->deliveryDay = "8";
          	             $postingStoreTO->noVAT="0";
          	             $postingStoreTO->onHold = "0";
          	             $postingStoreTO->chain = ""; // this will be set by the processing script
          	             $postingStoreTO->altPrincipalChainUId = ""; // let the posting allocate the generic chain
          	             $postingStoreTO->status=FLAG_STATUS_ACTIVE;
          	             $postingStoreTO->vendorCreatedByUId='';
          	             $postingStoreTO->ownedBy='';
          	             $postingStoreTO->oldAccount = '';
          	             
          	             $postingOrdersHoldingTO->flagStrippedDeliverNameLookupRef = 'Y';
                         
          	             $postingStoreTO->vatNumber = "";
            
                         $postingStoreTO->billName = trim($firstname) . ' ' . trim($lastname) ;
          	         }   
          	         if($subkey == 'address_1') {
          	             $postingStoreTO->billAdd1 = test_input($subvalue)	;  	
          	         }
          	         if($subkey == 'address_2') {
          	             $postingStoreTO->billAdd2 = test_input($subvalue)	;
          	         }          	                   	         	
          	         if($subkey == 'city') {
           	             $city = test_input($subvalue)	;         	
          	         }                   	         	
          	         if($subkey == 'postcode'); {
          	            $postcode = test_input($subvalue);
          	            $postingStoreTO->billAdd3 = trim($city) . ' ' . trim($postcode) ;          	         	
          	         }        	            
              }             	  
          }
          if($key == 'shipping') 	{
          	  
          	  foreach($value as $subkey => $subvalue) {
          	         if($subkey == 'first_name') {
          	            $firstname =  test_input($subvalue);
          	         }   
          	         if($subkey == 'last_name') {
          	            $lastname = test_input($subvalue);
          	            $postingStoreTO->deliverName = trim($firstname) . ' ' . trim($lastname) ;
          	            
          	            $postingOrdersHoldingTO->shipToName  = trim($firstname) . ' ' . trim($lastname) ;
                        $postingOrdersHoldingTO->deliverName = trim($firstname) . ' ' . trim($lastname) ;
          	            $postingOrdersHoldingTO->depotLookupRef = trim($status);

          	         }   
          	         if($subkey == 'address_1') {
          	            $postingStoreTO->deliverAdd1 = test_input($subvalue)	;  	
          	         }
          	         if($subkey == 'address_2') {
          	            $postingStoreTO->deliverAdd2 = test_input($subvalue)	;
          	         }          	                   	         	
          	         if($subkey == 'city') {
           	            $city = test_input($subvalue)	;         	
          	         } 
          	         
          	         if($subkey == 'state') {
           	            $state = test_input($subvalue)	;         	
          	         } 
          	         
          	         
          	                           	         	
          	         if($subkey == 'postcode') {
          	            $postcode = test_input($subvalue);
          	            $postingStoreTO->deliverAdd3 = trim($city) . ' ' . trim($postcode) ; 
          	              
                        
                        $postingOrdersHoldingTO->postingStoreTO=$postingStoreTO;    	         	
          	         }        	            
              }             	  
          }
          
          if($key == 'line_items') 	{ 
          	    /*******************
                *   ORDER DETAILS
                ******************
                $postingOrdersHoldingDetailTO = new PostingOrdersHoldingDetailTO();
                $vrat = '15.00'; 
          	
          	    foreach($value as $subskey => $subsvalue) {         	    	
          	    	  foreach($subsvalue as $subpkey => $subpvalue) {
          	    		    if($subpkey == 'sku') {
          	    	      	    $postingOrdersHoldingDetailTO->clientLineNo  =  '';
          	    	        	  $postingOrdersHoldingDetailTO->productCode   =  test_input($subpvalue); 
          	    	      }
          	    	      if($subpkey == 'name') {
          	    	      	    $postingOrdersHoldingDetailTO->pallets       =   0;
          	    	        	  $postingOrdersHoldingDetailTO->productName   =   test_input($subpvalue); 
          	    	        	  $postingOrdersHoldingDetailTO->principalProductUid = "";
          	    	      }          
          	    	      if($subpkey == 'quantity') {
          	    	    	      $postingOrdersHoldingDetailTO->quantity      =  test_input($subpvalue); 
          	    	      }
          	    	      if($subpkey == 'price') {
          	    	    	      $postingOrdersHoldingDetailTO->listPrice     =  test_input($subpvalue); 
          	    	    	    
          	    	    	      $postingOrdersHoldingDetailTO->discountValue = '';
          	    	    	      $postingOrdersHoldingDetailTO->nettPrice     =  test_input($subpvalue); 
                              $postingOrdersHoldingDetailTO->extPrice      =  round($postingOrdersHoldingDetailTO->quantity * $postingOrdersHoldingDetailTO->listPrice,2);
                              $postingOrdersHoldingDetailTO->vatRate       =  $vrat;
                              $postingOrdersHoldingDetailTO->vatAmount     =  round($postingOrdersHoldingDetailTO->extPrice * $vrat/100,2) ;
                              $postingOrdersHoldingDetailTO->totalPrice    =  round($postingOrdersHoldingDetailTO->extPrice + $postingOrdersHoldingDetailTO->vatAmount,2);
                        }           
          	    		}
                }    
           	    echo '</br>';

                    $postingOrdersHoldingTO->detailArr[] = $postingOrdersHoldingDetailTO;  
 
          }
       }
       
       $postImportDAO = new PostImportDAO($this->dbConn);
       $rTO = $postImportDAO->postOrdersHolding($postingOrdersHoldingTO);
			if ($rTO->type!=FLAG_ERRORTO_SUCCESS) {
				$eTO->type = FLAG_ERRORTO_ERROR;
				$eTO->description = "Failed to store Order in postTOH : ".$rTO->description;
        echo $eTO->description;
				return $eTO;
			}
       	
    }
    
} */
// --------------------------------------------------------------------------------------------------------------------------------
// --------------------------------------------------------------------------------------------------------------------------------	
