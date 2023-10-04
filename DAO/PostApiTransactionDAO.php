<?php

include_once('ROOT.php');
include_once($ROOT . 'PHPINI.php');
include_once($ROOT . $PHPFOLDER . 'DAO/ExceptionThrower.php');
include_once($ROOT.$PHPFOLDER."properties/Constants.php");
include_once($ROOT.$PHPFOLDER."DAO/MaintenanceDAO.php");


class ApiTransactionDAO {

  private $dbConn;

	function __construct($dbConn) {
       $this->dbConn = $dbConn;
       $this->errorTO = new ErrorTO;
  }
// ************************************************************************************************************************************
       public function incomingKosOrderValidation($principalId,
                                                  $reference,
                                                  $capturedBy,
                                                  $storeID) {
                                      	
       // Check if reference is unique
       
              $sql = "SELECT dm.document_number, dm.principal_uid, dm.api_reference
                      FROM document_master dm 
                      LEFT JOIN .document_header dh ON dm.uid = dh.document_master_uid
                      WHERE dm.principal_uid = "  . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "
                      AND   dm.api_reference = '" . mysqli_real_escape_string($this->dbConn->connection, $reference) . "';";

             $validTx = $this->dbConn->dbGetAll($sql);             
             
             if(count($validTx) > 0) {
             	    $returnResult = ["resultStatus"  =>"E",
                       	           "ResultCode"    =>'850' ,
                       	     	     "resultMessage" =>"Transaction Reference Captured By User - Duplicate"];
                       	     	     	           
                  return $returnResult;
             }
             
             
             $sql = "SELECT *
                     FROM .principal_store_master psm
                     WHERE psm.uid = '" . mysqli_real_escape_string($this->dbConn->connection, $storeID) . "'";	

             $validTx = $this->dbConn->dbGetAll($sql);             
             
             if(count($validTx) != 1) {
             	    $returnResult = ["resultStatus"  =>"E",
                       	           "ResultCode"    =>'851' ,
                       	     	     "resultMessage" =>"Store Not Valid"];
                       	     	     	           
                  return $returnResult;
             }                    	
      
       }
//********************************************************************************************************************************************************	 
       public function incomingKosOrderDetailValidation($principalId, 
                                                        $detailLines) {
                                                                                   	
       if(count($detailLines) == 0) {
             	    $returnResult = ["resultStatus"  =>"E",
                       	           "ResultCode"    =>'852' ,
                       	     	     "resultMessage" =>"No Detail Lines Present"];
                       	     	     	           
                  return $returnResult;
       }
       $detError = 'N';
       $returnResult = ["resultStatus"  =>"S",
                        "ResultCode"    =>'000' ,
                        "resultMessage" =>"Prod Validation Success"
                       ];
          foreach($detailLines as $detRow) {
       	      foreach($detRow as $key=>$row) {
       	      	  if($key == 'prodCode') {
       	    	         $sql = "SELECT *
                               FROM .principal_product pp
                               WHERE pp.principal_uid = "  . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "
                               AND   pp.product_code = '"  . mysqli_real_escape_string($this->dbConn->connection, $row) . "'";
       	               $validProd = $this->dbConn->dbGetAll($sql);             
             
                       if(count($validProd) != 1) {	
                           $detError = 'Y';
                           break;
       	               }          	      	  	
       	      	  }                                         	
              }
             if($detError == 'Y') {	
                  $returnResult = ["resultStatus"  =>"E",
                                    "ResultCode"    =>'853' ,
                                    "resultMessage" =>"Product Code Not Found"];
                  $detError = 'Y';
                  break;
       	     }
          }
          return $returnResult;
       }
//********************************************************************************************************************************************************	 
       public function getProductUid($principalId, 
                                     $productCode) {
                                     	
             $sql  = "SELECT uid as 'prodUid'
                      from .principal_product pp
                      where pp.principal_uid = "  . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "
                      AND   pp.product_code = '"  . mysqli_real_escape_string($this->dbConn->connection, $productCode) . "';";                        	
       	
            $prdUid = $this->dbConn->dbGetAll($sql);
            
            return  $prdUid[0]['prodUid'];

       }

//********************************************************************************************************************************************************	 
}