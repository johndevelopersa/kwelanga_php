<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'properties/dbSettings.inc');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER."libs/CommonUtils.php");
include_once($ROOT.$PHPFOLDER."libs/ValidationCommonUtils.php");
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');
include_once($ROOT.$PHPFOLDER."DAO/ProductDAO.php");
include_once($ROOT.$PHPFOLDER.'TO/SequenceTO.php');
include_once($ROOT.$PHPFOLDER."DAO/StockDAO.php");
/*
 * NOTE : The validation should return IMMEDIATELY if an error type occurred (required for the way screen is processed)
 */

class PostStockDAO {
	public $errorTO;
	private $dbConn;

	function __construct($dbConn) {
		$this->dbConn = $dbConn;
		$this->errorTO = new ErrorTO;
    }

	/*
	 *
	 * PRINCIPAL STORE UPDATE
	 *
	 */

	 public function postStockValidation($postingStockTO) {
    	global $ROOT; global $PHPFOLDER;

		return true;

    }


	 function postStock( $postingStockTO ) {

  		$resultOK = $this->postStockValidation($postingStockTO);
    	if ($resultOK) {
    		if ($postingStockTO->stockCountDate=="") $stockCountDate = "NULL"; else $stockCountDate = "'".$postingStockTO->stockCountDate."'";

			$this->dbConn->dbQuery("SET time_zone='+0:00'"); // important to make sure PHPBACKEND which sits in RSA synchs changes updated according to SA time (unsynched table is in RSA)
			$this->dbConn->dbinsQuery ("DELETE FROM stock
					          	        WHERE  principal_id = '".$postingStockTO->principalId."'
					          		    	AND    depot_id     = '".$postingStockTO->depotId."'
															AND    stock_item   = '".$postingStockTO->stockCode."'" );

			if (!$this->dbConn->dbQueryResult) {
				$this->errorTO->type = FLAG_ERRORTO_ERROR;
				$this->errorTO->description = "Unable to delete stock.";
				return $this->errorTO;
			}


	       	// there is only an insert for time being because the unsynched_stock table is cleared out at start of import
			$sql = "INSERT INTO  `stock` ( `principal_id`, 
                                     `depot_id`,
                                     `principal_product_uid`,
                                     `stock_item`,
                                     `stock_descrip`, 
                                     `goods_in_transit`, 
                                     `opening`,
                                     `arrivals`,
                                     `uplifts`,
                                     `returns_cancel`,
                                     `returns_nc`,
                                     `delivered`,
                                     `adjustment`,
                                     `closing`,
                                     `allocations`,
                                     `in_pick`,
                                     `available`,
                                     `blocked_stock`, 
                                     `lost_sales_cancel`,
                                     `lost_sales_oos`,
                                     `stock_count`,
                                     `stock_count_date`,
                                     `data_generated_date`,
                                     `last_updated`)
	       	         		VALUES ( " .
	  		 		 			"'" . $postingStockTO->principalId                         . "', " .
	  		 		 			"'" . $postingStockTO->depotId                             . "', " .
	  		 		 			"'" . $postingStockTO->stkUid                              . "', " .
	  		 		 			"'" . $postingStockTO->stockCode                           . "', ".
	           			"'" . addSlashes( $postingStockTO->stockDescription )      . "', ".
									"'" . $postingStockTO->goodsInTransit                      . "', ".
	           		 	"'" . $postingStockTO->opening                             . "', ".
           		 		"'" . $postingStockTO->arrivals                            . "', ".
           				"'" . $postingStockTO->uplifts                             . "', ".
           	 	 		"'" . $postingStockTO->returnsCancel                       . "', ".
           	 	 		"'" . $postingStockTO->returnsNC                           . "', ".
           		 		"'" . $postingStockTO->delivered                           . "', ".
           		 		"'" . $postingStockTO->adjustment                          . "', ".
           				"'" . $postingStockTO->closing                             . "', ".
           	 	 		"'" . $postingStockTO->allocations                         . "', ".
           		 		"'" . $postingStockTO->inPick                              . "', ".
           		 		"'" . $postingStockTO->available                           . "', ".
           		 		"'" . $postingStockTO->blockedStock                        . "', ".
           				"'" . $postingStockTO->lostSalesCancel                     . "', ".
           	 	 		"'" . $postingStockTO->lostSalesOOS                        . "', ".
           		 		"'" . $postingStockTO->stockCount                          . "', ".
	                      $stockCountDate.",
	                      now(),
                        now()) ";
		 	   $this->errorTO = $this->dbConn->processPosting($sql,$postingStockTO->stockDescription);
			   if ($this->errorTO->type==FLAG_ERRORTO_SUCCESS) {
                $this->errorTO->identifier=$this->dbConn->dbGetLastInsertId(); // get the UID just created
                $this->errorTO->description = "Stock Successfully Created.";
         } else  {
                $this->errorTO->type = FLAG_ERRORTO_ERROR;
                $this->errorTO->description = "Failed to create Stock.".mysqli_error($this->dbConn->connection) . $sql;
                return $this->errorTO;
         }
    	}

      	return $this->errorTO;

	}

	function postStockBulk( $postingStockTOarr ) {

  		$dSQL = ""; $iSQL = "";
  		foreach ($postingStockTOarr as $postingStockTO) {
    		if ($postingStockTO->stockCountDate=="") $stockCountDate = "NULL"; else $stockCountDate = "'".$postingStockTO->stockCountDate."'";

    		$dSQL .= ($dSQL=="")?"":",";
    		$dSQL .="({$postingStockTO->principalId},{$postingStockTO->depotId},'{$postingStockTO->stockCode}')";

    		$iSQL .= ($iSQL=="")?"":",";
    		$iSQL .="(
						'{$postingStockTO->principalId}',
		 		 		'{$postingStockTO->depotId}',
		 		 		'{$postingStockTO->stockCode}',
	       				'".addSlashes( $postingStockTO->stockDescription )."',
	       				'{$postingStockTO->goodsInTransit}',
	       		 		'{$postingStockTO->opening}',
	       		 		'{$postingStockTO->arrivals}',
	       				'{$postingStockTO->uplifts}',
	       	 	 		'{$postingStockTO->returnsCancel}',
	       	 	 		'{$postingStockTO->returnsNC}',
	       		 		'{$postingStockTO->delivered}',
	       		 		'{$postingStockTO->adjustment}',
	       				'{$postingStockTO->closing}',
	       	 	 		'{$postingStockTO->allocations}',
	       		 		'{$postingStockTO->inPick}',
	       		 		'{$postingStockTO->available}',
	       		 		'{$postingStockTO->blockedStock}',
	       				'{$postingStockTO->lostSalesCancel}',
	       	 	 		'{$postingStockTO->lostSalesOOS}',
	       		 		'{$postingStockTO->stockCount}',
	       	 	 		{$stockCountDate},
						now(),
						'{$postingStockTO->dataGeneratedDate}'
					)";
  		}
  		$dSQL = "delete from stock
				 where  (principal_id, depot_id, stock_item) in ({$dSQL})";

		$iSQL = "INSERT INTO  `stock` ( `principal_id`, `depot_id`, `stock_item`, `stock_descrip`, `goods_in_transit`,`opening`,`arrivals`,`uplifts`,`returns_cancel`,
																							`returns_nc`,`delivered`,`adjustment`,`closing`,`allocations`,`in_pick`,`available`,`blocked_stock`,`lost_sales_cancel`,
																							`lost_sales_oos`,`stock_count`,`stock_count_date`,`last_updated`, data_generated_date)
	       	     VALUES {$iSQL}";

		$this->dbConn->dbQuery("SET time_zone='+0:00'"); // important to make sure PHPBACKEND which sits in RSA synchs changes updated according to SA time (unsynched table is in RSA)

		$this->dbConn->dbinsQuery ($dSQL);
		if (!$this->dbConn->dbQueryResult) {
			$this->errorTO->type = FLAG_ERRORTO_ERROR;
			$this->errorTO->description = "Unable to delete stock.";
			return $this->errorTO;
		}

		$this->dbConn->dbinsQuery ($iSQL);
		if (!$this->dbConn->dbQueryResult) {
			$this->errorTO->type = FLAG_ERRORTO_ERROR;
			$this->errorTO->description = "Unable to insert stock.".$iSQL;
			return $this->errorTO;
		}

		$this->errorTO->type=FLAG_ERRORTO_SUCCESS;
		$this->errorTO->identifier="";
		$this->errorTO->description = "Stock Successfully Created.";

      	return $this->errorTO;

	}


  public function updateStockArrivalValidation($depotId, $principalId){

    global $ROOT, $PHPFOLDER;
    include_once($ROOT.$PHPFOLDER.'DAO/StockDAO.php');
    $stockDAO = new StockDAO($this->dbConn);

    $stockMode = $stockDAO->checkStockMode($principalId, $depotId);

    if($stockMode){
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description = "This depot - principal is in stock take mode, please try again later!";
      return false;
    }

    return true;
  }



	// No need to apply row locking here as it doesnt read the value first
	// Unfortunately if the row doesnt exist because you supplied the wrong product uid then it will create the row
	public function updateStockArrival($principalUId, $depotUId, $pProductUId, $processedQty) {
       global $ROOT, $PHPFOLDER;
    
       include_once($ROOT.$PHPFOLDER.'DAO/ProductDAO.php');
       $productDAO = new ProductDAO($this->dbConn);
       $nSI = $productDAO->getNonStockItemByProductUid($pProductUId);
        
      if($nSI['non_stock_item'] == "N") {

          if($this->updateStockArrivalValidation($depotUId, $principalUId)){  //for arrivals cannot be in stock take mode...

            // NB!! This uses the ORDER_QTY
            $this->dbConn->dbQuery("SET time_zone='+0:00'");

            if (trim($processedQty)=="") $processedQty = "0";

            // not a problem if the depot does not use the inpick stage as inpick should be zero then.
            $sql="UPDATE stock
                    SET arrivals=arrivals+abs({$processedQty}),
                        closing=closing+abs({$processedQty}),
                        available = if(available>=0,available,0) + abs({$processedQty}) - abs(allocations) - abs(in_pick)
                    WHERE principal_id = '{$principalUId}'
                            AND   depot_id = '{$depotUId}'
                            AND   principal_product_uid = '{$pProductUId}'";

              $this->errorTO = $this->dbConn->processPosting($sql,"");

              if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {
                              $this->errorTO->description="Failed to updateStockArrival : ".$this->errorTO->description;
                              return $this->errorTO;
                      }

              // create if not exists - Unfortunately if the row doesnt exist because you supplied the wrong product uid then it will create the row
              if (mysqli_affected_rows($this->dbConn->connection)==0) {
                $sql="INSERT INTO stock (principal_id,depot_id,principal_product_uid,stock_item,stock_descrip,opening,
                                                                                                                                      arrivals,uplifts,returns_cancel,returns_nc,delivered,adjustment,closing,allocations,
                                                                                                                                      in_pick,available,lost_sales_cancel,lost_sales_oos,stock_count,stock_count_date,guid,
                                                                                                                                      data_generated_date,last_updated)
                                              select {$principalUId},{$depotUId},{$pProductUId}, product_code, product_description, 0,
                                                                               abs({$processedQty}),0,0,0,0,0,abs({$processedQty}),0,
                                                                               0,abs({$processedQty}),0,0,0,null,null,
                                                                               now(), now()
                                                      from   principal_product
                                                      where  principal_uid = '{$principalUId}'
                                                      and    uid = '{$pProductUId}'";

                      $this->errorTO = $this->dbConn->processPosting($sql,"");
              }

                      if ($this->errorTO->type==FLAG_ERRORTO_SUCCESS) {
                              $this->errorTO->description="updateStockArrival successful";
                              return $this->errorTO;
                      }

          }
           return $this->errorTO;
      } else { 
    	   $this->errorTO->type=FLAG_ERRORTO_SUCCESS;
         return $this->errorTO;
     }    
  }

  public function updateStockAdjustmentValidation($depotId, $principalId, $pProductUId, $processedQty, $negative){

    global $ROOT, $PHPFOLDER;
    include_once($ROOT.$PHPFOLDER.'DAO/StockDAO.php');
    $stockDAO = new StockDAO($this->dbConn);
    $stockArr = $stockDAO->getPrincipalProductStock($depotId, $principalId, $pProductUId);

    //stock item must be loaded for adjustment to be captured - unlike arrivals.
    if(count($stockArr)==0){
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description = "Stock Adjustment failed, as a product is NOT loaded in the stock table! (product:$pProductUId)";
      return false;
    }

    //if negative movement, make sure the closing stock does not drop below zero...
    if($negative == true){

      if(!isset($stockArr[0]['closing'])){
        $this->errorTO->type = FLAG_ERRORTO_ERROR;
        $this->errorTO->description = "Closing Stock Array index failure!";
        return false;
      }

      if(!(abs($stockArr[0]['closing']) - abs((int)$processedQty) >= -100000)){ //make sure closing stock does not drop below zero...
         $this->errorTO->type = FLAG_ERRORTO_ERROR;
        $this->errorTO->description = "A captured amount will make the closing stock drop below zero, this is not allowed!";
        return false;
      }

    }

    return true;

  }

  // No need to apply row locking here as it doesnt read the value first
  // Unfortunately if the row doesnt exist because you supplied the wrong product uid then it will create the row
  public function updateStockAdjustment($principalUId, $depotUId, $pProductUId, $processedQty, $negative = false) {
      global $ROOT, $PHPFOLDER;
    
      include_once($ROOT.$PHPFOLDER.'DAO/ProductDAO.php');
      $productDAO = new ProductDAO($this->dbConn);
      $nSI = $productDAO->getNonStockItemByProductUid($pProductUId);

      if($nSI['non_stock_item'] == "N") {

         if($this->updateStockAdjustmentValidation($depotUId, $principalUId, $pProductUId, $processedQty, $negative)){
  
           $this->dbConn->dbQuery("SET time_zone='+0:00'");

           $processedQty = (int)$processedQty;
           $adjustSign = ($negative==true)?'-':'';

           $sql="UPDATE stock SET adjustment = adjustment + {$adjustSign} abs({$processedQty}),
                                  closing = closing + {$adjustSign} abs({$processedQty}),
                                  available =  available + {$adjustSign} abs({$processedQty})
                WHERE principal_id = '{$principalUId}'
                AND   depot_id = '{$depotUId}'
                AND   principal_product_uid = '{$pProductUId}'";

           $this->errorTO = $this->dbConn->processPosting($sql,"");

          if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {
             $this->errorTO->description="Failed to Update Stock : ".$this->errorTO->description;
             return $this->errorTO;
          }

         }
         return $this->errorTO;
      } else { 
    	   $this->errorTO->type=FLAG_ERRORTO_SUCCESS;
         return $this->errorTO;
      }    
  }

  // No need to apply row locking here as it doesnt read the value first
	// Unfortunately if the row doesnt exist because you supplied the wrong product uid then it will create the row
	public function updateStockUplift($principalUId, $depotUId, $pProductUId, $processedQty, $amendedQty) {

		// NB!! This uses the ORDER_QTY ---- PLS VERIFY IF IT IS STILL THE CASE !!!!!!!

    $this->dbConn->dbQuery("SET time_zone='+0:00'");

		if (trim($processedQty)=="") $processedQty = "0";

		$sql="UPDATE stock
			  	SET uplifts=uplifts+abs({$processedQty}),
							closing=closing+abs({$processedQty}),
							available=available+abs({$processedQty})
			  	WHERE principal_id = '{$principalUId}'
					AND   depot_id = '{$depotUId}'
					AND   principal_product_uid = '{$pProductUId}'";

  	$this->errorTO = $this->dbConn->processPosting($sql,"");

  	if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {
		 	$this->errorTO->description="Failed to updateStockArrival : ".$this->errorTO->description;
		 	return $this->errorTO;
		}

  	// create if not exists - Unfortunately if the row doesnt exist because you supplied the wrong product uid then it will create the row
  	if (mysqli_affected_rows($this->dbConn->connection)==0) {
  	  $sql="INSERT INTO stock (principal_id,depot_id,principal_product_uid,stock_item,stock_descrip,opening,
																arrivals,uplifts,returns_cancel,returns_nc,delivered,adjustment,closing,allocations,
																in_pick,available,lost_sales_cancel,lost_sales_oos,stock_count,stock_count_date,guid,
																data_generated_date,last_updated)
			  		select {$principalUId},{$depotUId},{$pProductUId}, product_code, product_description, 0,
									 0,abs({$processedQty}),0,0,0,0,abs({$processedQty}),0,
									 0,abs({$processedQty}),0,0,0,null,null,
									 now(), now()
						from   principal_product
						where  principal_uid = '{$principalUId}'
						and    uid = '{$pProductUId}'";

  		$this->errorTO = $this->dbConn->processPosting($sql,"");
  	}

		if ($this->errorTO->type==FLAG_ERRORTO_SUCCESS) {
		 	$this->errorTO->description="updateStockUplift successful";
		 	return $this->errorTO;
		}

    return $this->errorTO;
  }

  // No need to apply row locking here as it doesnt read the value first
	// Unfortunately if the row doesnt exist because you supplied the wrong product uid then it will create the row
	public function updateStockOrder($principalUId, $depotUId, $pProductUId, $processedQty) {
    
    global $ROOT, $PHPFOLDER;
    
    include_once($ROOT.$PHPFOLDER.'DAO/ProductDAO.php');
    $productDAO = new ProductDAO($this->dbConn);
    $nSI = $productDAO->getNonStockItemByProductUid($pProductUId);
        
    if($nSI['non_stock_item'] == "N") {
		   if (trim($processedQty)=="") $processedQty = "0";
		   
		      $reverseDirection=true;
		   
           $direction=(($reverseDirection)?-1:+1); // reverse the process of how it got here back into inpick
		 
		       if($nSI['direct_inv'] == 'Y') {
                  $sql="UPDATE stock SET delivered=delivered+(abs({$processedQty})*$direction), 
                                         closing=closing+(abs({$processedQty})*$direction)
                        WHERE principal_id = '{$principalUId}'
                        AND   depot_id = '{$depotUId}'
                        AND   principal_product_uid = '{$pProductUId}'";
		       } else {
		              $sql="UPDATE stock SET allocations=allocations-abs({$processedQty}), available=available-abs({$processedQty})
                        WHERE principal_id = '{$principalUId}'
                        AND   depot_id = '{$depotUId}'
                        AND   principal_product_uid = '{$pProductUId}'";
           }            

  	     $this->errorTO = $this->dbConn->processPosting($sql,"");

  	     if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {
		 	       $this->errorTO->description="Failed to Update Stock : ".$this->errorTO->description;
		 	       return $this->errorTO;
		     }

  	     // create if not exists - Unfortunately if the row doesnt exist because you supplied the wrong product uid then it will create the row
  	     if ($this->errorTO->object["rows_matched"]==0) {
  	         $sql="INSERT INTO stock (principal_id,
  	                                  depot_id,
  	                                  principal_product_uid,
  	                                  stock_item,
  	                                  stock_descrip,
  	                                  opening,
  	                                  arrivals,
  	                                  uplifts,
  	                                  returns_cancel,
  	                                  returns_nc,
  	                                  delivered,
  	                                  adjustment,
  	                                  closing,
  	                                  allocations,
																      in_pick,
																      available,
																      lost_sales_cancel,
																      lost_sales_oos,
																      stock_count,
																      stock_count_date,
																      guid,
																      data_generated_date,last_updated) 
																      select {$principalUId},
																             {$depotUId},
																             {$pProductUId}, 
																             product_code, 
																             product_description, 
																             0,
																             0,
																             0,
																             0,
																             0,
																             0,
																             0,
																             0,
																             abs({$processedQty})*-1,
																             0,
																             abs({$processedQty})*-1,
																             0,
																             0,
																             0,
																             null,
																             null,
																             now(), 
																             now()
						                          from   principal_product
					                          	where  principal_uid = '{$principalUId}'
						                          and    uid = '{$pProductUId}'";
						                          
  		       $this->errorTO = $this->dbConn->processPosting($sql,"");
  	     }

		     if ($this->errorTO->type==FLAG_ERRORTO_SUCCESS) {
		 	       $this->errorTO->description="Update Stock Successful";
		 	       return $this->errorTO;
	       }
         return $this->errorTO;
    } else { 
    	       
         $this->errorTO->type=FLAG_ERRORTO_SUCCESS;
         return $this->errorTO;
    }
}  

  // No need to apply row locking here as it doesnt read the value first
	// Unfortunately if the row doesnt exist because you supplied the wrong product uid then it will create the row
	public function updateStockInvoiced($principalUId, 
	                                    $depotUId, 
	                                    $pProductUId, 
	                                    $processedQty, 
	                                    $amendedQty, 
	                                    $reverseDirection=false, 
	                                    $skipInPickStage=false,
	                                    $waitingDispatch=false,
	                                    $docmastUid=false) {
      global $ROOT, $PHPFOLDER;
    
      include_once($ROOT.$PHPFOLDER.'DAO/ProductDAO.php');
      $productDAO = new ProductDAO($this->dbConn);
      $nSI = $productDAO->getNonStockItemByProductUid($pProductUId);

      if($nSI['non_stock_item'] == "N") {

          if ($reverseDirection!==false) {
	           $this->errorTO->FLAG_ERRORTO_ERROR;
	           $this->errorTO->description="Reverse Direction currently not implemented for updateStockInvoiced!";
	           // remember to also take into account depots that don't use the in-pick status if you decide to implement this!
	           return $this->errorTO;
	        }

          $this->dbConn->dbQuery("SET time_zone='+0:00'");

          if (trim($processedQty)=="") $processedQty = "0";
          if (trim($amendedQty)=="") $amendedQty = "0";
          
          $direction=(($reverseDirection)?-1:+1); // reverse the process of how it got here back into inpick
          
          if($waitingDispatch=='Y') {
        	
                 $sql="UPDATE stock SET pending_dispatch = pending_dispatch + abs({$amendedQty})*$direction
                       WHERE  principal_id = '{$principalUId}'
                       AND    depot_id = '{$depotUId}'
                       AND    principal_product_uid = '{$pProductUId}'";
                 
          } else {
          	
                 if ($skipInPickStage=="Y" ) {
                       $sql="UPDATE stock SET delivered=delivered-(abs({$amendedQty})*$direction), 
                                              closing=closing-(abs({$amendedQty})*$direction),
                                              allocations=allocations+(abs({$processedQty})*$direction),
                                              available=available+((abs($processedQty)-abs($amendedQty))*$direction) -- was originally adjusted already so change it by the difference
                             WHERE principal_id = '{$principalUId}'
                             AND   depot_id = '{$depotUId}'
                             AND   principal_product_uid = '{$pProductUId}'";
                 } else {
                       $sql="UPDATE stock SET delivered=delivered-(abs({$amendedQty})*$direction),
                                              closing=closing-(abs({$amendedQty})*$direction),
                                              in_pick=in_pick+(abs({$processedQty})*$direction),
                                              available=available+((abs($processedQty)-abs($amendedQty))*$direction) -- was originally adjusted already so change it by the difference
                             WHERE principal_id = '{$principalUId}'
                             AND   depot_id = '{$depotUId}'
                             AND   principal_product_uid = '{$pProductUId}'";
                 }
          }
          
          file_put_contents('VTS532.txt', $sql, FILE_APPEND);
          
  	     $this->errorTO = $this->dbConn->processPosting($sql,"");

  	    if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {
		 	      $this->errorTO->description="Failed to update Invoiced Stock : ".$this->errorTO->description;
		 	      return $this->errorTO;
	    	}

  	    // create if not exists - Unfortunately if the row doesnt exist because you supplied the wrong product uid then it will create the row
  	    if (mysqli_affected_rows($this->dbConn->connection)==0) {
  		     // skip, only arrivals load rows
  	    }
                   
		    if ($this->errorTO->type==FLAG_ERRORTO_SUCCESS) {
		 	      $this->errorTO->description="Update Stock Invoiced Successful";
            $this->dbConn->dbQuery("commit"); 
		 	      return $this->errorTO;
		    }

        return $this->errorTO;
      } else { 
    	       
         $this->errorTO->type=FLAG_ERRORTO_SUCCESS;
         return $this->errorTO;
      }        
  }

  // No need to apply row locking here as it doesnt read the value first
	// Unfortunately if the row doesnt exist because you supplied the wrong product uid then it will create the row
	public function updateStockInPick($principalUId, $depotUId, $pProductUId, $processedQty, $reverseDirection=false) {
      global $ROOT, $PHPFOLDER;
    
      include_once($ROOT.$PHPFOLDER.'DAO/ProductDAO.php');
      $productDAO = new ProductDAO($this->dbConn);
      $nSI = $productDAO->getNonStockItemByProductUid($pProductUId);

      if($nSI['non_stock_item'] == "N") {

          $this->dbConn->dbQuery("SET time_zone='+0:00'");

		      if (trim($processedQty)=="") $processedQty = "0";
		      $direction=(($reverseDirection)?-1:+1); // reverse the process back into accepted

		      // NB !
		      // This relies on this function never being called for those depots not using the in-pick status !

		      $sql="UPDATE stock SET allocations=allocations+(abs({$processedQty})*$direction), -- might need to only do this if < 0 to not more than 0
							                  in_pick=in_pick-(abs({$processedQty})*$direction) -- doesnt change available as stock was originally already take out of available and still is.
			  	     WHERE principal_id = '{$principalUId}'
					     AND   depot_id = '{$depotUId}'
					     AND   principal_product_uid = '{$pProductUId}'";

  	      $this->errorTO = $this->dbConn->processPosting($sql,"");

  	      if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {
		 	       $this->errorTO->description="Failed to updateStockArrival : ".$this->errorTO->description;
		 	       return $this->errorTO;
		      }

  	      // create if not exists - Unfortunately if the row doesnt exist because you supplied the wrong product uid then it will create the row
  	      if (mysqli_affected_rows($this->dbConn->connection)==0) {
  	        // skip, only arrivals load rows
  	      }

          if ($this->errorTO->type==FLAG_ERRORTO_SUCCESS) {
		 	        $this->errorTO->description="Update Stock Successful";
		 	        return $this->errorTO;
		      }

          return $this->errorTO;
      } else { 
    	       
         $this->errorTO->type=FLAG_ERRORTO_SUCCESS;
         return $this->errorTO;
      }

  }

  // No need to apply row locking here as it doesnt read the value first
	// Unfortunately if the row doesnt exist because you supplied the wrong product uid then it will create the row
	public function updateStockCancelled($principalUId, $depotUId, $pProductUId, $processedQty, $amendedQty, $currentStatus, $skipInPickStage=false) {
		 global $ROOT,$PHPFOLDER;

      include_once($ROOT.$PHPFOLDER.'DAO/ProductDAO.php');
      $productDAO = new ProductDAO($this->dbConn);
      $nSI = $productDAO->getNonStockItemByProductUid($pProductUId);

      if($nSI['non_stock_item'] == "N") {    

          include_once($ROOT.$PHPFOLDER."TO/PostingDepotAmendDocumentDetailTO.php");

          $this->dbConn->dbQuery("SET time_zone='+0:00'");

		      if (trim($processedQty)=="") $processedQty = "0";
		      if (trim($amendedQty)=="") $amendedQty = "0";

		      switch ($currentStatus) {
            case DST_UNACCEPTED:

      		  case DST_ACCEPTED: {
				        $sql="UPDATE stock SET allocations=allocations+abs({$processedQty}), -- might need to only do this if < 0 to not more than 0
									                     available=available+abs({$processedQty})
					  	        WHERE principal_id = '{$principalUId}'
							        AND   depot_id = '{$depotUId}'
							        AND   principal_product_uid = '{$pProductUId}'";
				    break;
			      }
			      case DST_INPICK: {

			         if ($skipInPickStage=="Y") {
			            $this->errorTO->type = FLAG_ERRORTO_ERROR;
			            $this->errorTO->description="This depot should not have any documents currently on the inpick status as this depot does not use the inpick stage";
			            return $this->errorTO;
			         }

				       $sql="UPDATE stock SET in_pick=in_pick+abs({$processedQty}), -- might need to only do this if < 0 to not more than 0
								                  	available=available+abs({$processedQty})
					  	    WHERE principal_id = '{$principalUId}'
							    AND   depot_id = '{$depotUId}'
							    AND   principal_product_uid = '{$pProductUId}'";
				    break;
			      }
			      case DST_INVOICED: {
			   	     $sql="UPDATE stock SET delivered=delivered+abs({$amendedQty}), -- might need to only do this if < 0 to not more than 0
									                    closing=closing+abs({$amendedQty}),
									                    available=available+abs($amendedQty)
					  	       WHERE principal_id = '{$principalUId}'
							       AND   depot_id = '{$depotUId}'
							       AND   principal_product_uid = '{$pProductUId}'";
				       break;
			    }
			   default: {
				   $this->errorTO->description="Unrecognised current status for Cancel in updateStockCancelled";
		 		   return $this->errorTO;
			   }
		      }

  	      $this->errorTO = $this->dbConn->processPosting($sql,"");

  	      if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {
		 	        $this->errorTO->description="Failed to updateStockCancelled : ".$this->errorTO->description;
		 	        return $this->errorTO;
		      }

        	// create if not exists
        	if (mysqli_affected_rows($this->dbConn->connection)==0) {
          		// perhaps in future we might want to create a row in stock for amounts, but I cant see why. You should not have gotten this far without the row having been created already.
        	}

		      if ($this->errorTO->type==FLAG_ERRORTO_SUCCESS) {
		        	$this->errorTO->description="updateStockCancelled successful";
		 	    return $this->errorTO;
		      }

          return $this->errorTO;
  
      } else { 
    	       
         $this->errorTO->type=FLAG_ERRORTO_SUCCESS;
         return $this->errorTO;
      } 
  }


  // No need to apply row locking here as it doesnt read the value first
	// Unfortunately if the row doesnt exist because you supplied the wrong product uid then it will create the row
    public function updateStockReturnCancel($principalUId, $depotUId, $pProductUId, $amendedQty) {

      global $ROOT, $PHPFOLDER;
    
      include_once($ROOT.$PHPFOLDER.'DAO/ProductDAO.php');
      $productDAO = new ProductDAO($this->dbConn);
      $nSI = $productDAO->getNonStockItemByProductUid($pProductUId);

      if($nSI['non_stock_item'] == "N") {    

          $this->dbConn->dbQuery("SET time_zone='+0:00'");

          if (trim($amendedQty)=="") $amendedQty = "0";

          $sql="UPDATE stock SET returns_cancel = returns_cancel+abs({$amendedQty}),
                                 closing = closing+abs({$amendedQty}),
                                 available = available+abs($amendedQty)
                WHERE principal_id = '{$principalUId}'
                AND   depot_id = '{$depotUId}'
                AND   principal_product_uid = '{$pProductUId}'";

          $this->errorTO = $this->dbConn->processPosting($sql,"");

          if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {
             $this->errorTO->description="Failed to updateStockArrival : ".$this->errorTO->description;
             return $this->errorTO;
          }

         // create if not exists - Unfortunately if the row doesnt exist because you supplied the wrong product uid then it will create the row
         if (mysqli_affected_rows($this->dbConn->connection)==0) {
            // skip, only arrivals load rows
         }

         if ($this->errorTO->type==FLAG_ERRORTO_SUCCESS) {
             $this->errorTO->description="updateStockInvoiced successful";
             return $this->errorTO;
         }

         return $this->errorTO;
      } else { 
    	       
         $this->errorTO->type=FLAG_ERRORTO_SUCCESS;
         return $this->errorTO;
      } 
   }


  public function postStockModeValidation($depotId, $principalId, $userId, $switch){

    global $ROOT, $PHPFOLDER;
    include_once($ROOT.$PHPFOLDER.'DAO/StockDAO.php');
    include_once($ROOT.$PHPFOLDER."DAO/DepotDAO.php");
    $stockDAO = new StockDAO($this->dbConn);
    $depotDAO = new DepotDAO($this->dbConn);

    //check if in stock mode already - this shouldn't happen, depot-principal values  are also unique in database!
    $stockMode = $stockDAO->checkStockMode($principalId, $depotId);

    if($stockMode && $switch == 1){
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description = "Error, this depot - principal is already in stock take mode!";
      return false;
    }
    if(!$stockMode && $switch != 1){
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description = "Error, this depot - principal is not in stock take mode!";
      return false;
    }


    if($switch == 1){ //turn ON?

      //check if there is any products in pick, display list of products...
      $stockArr = $stockDAO->getStockCountProducts($depotId, $principalId);
      $hasInpickRows = false;
      $inpickArr = array();
      foreach($stockArr as $s){
        if(abs($s['in_pick'])>0){
          $hasInpickRows = true;
          $inpickArr[] = $s['product_code'] . ' -- ' . $s['product_description'];
        }
      }
      if($hasInpickRows){
        $this->errorTO->type = FLAG_ERRORTO_ERROR;
        $this->errorTO->description = "Stock mode failed as there are ".count($inpickArr)." stock item(s) in-pick!<br><u>Products:</u><div style=\"height:80px;overflow:auto;\">" . join("<br>", $inpickArr) . '</div>';
        return false;
      }

    }


    //is a depot type user...
    if(!CommonUtils::isDepotUser()){
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description = "Restricted Access<br>Only Depot users are allowed to do stock take functions!";
      return false;
    }


    //depot exists and is wms?
    $depotArr = $depotDAO->getDepotItem($depotId);
    if(!count($depotArr)>0){
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description = "Invalid depot supplied!";
      return false;
    }
    if(!isset($depotArr[$depotId]['wms'])){
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description = "Invalid depot supplied - WMS array failure!";
      return false;
    }
    if($depotArr[$depotId]['wms'] != 'Y'){
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description = "Operation only allowed on Warehouse Management enabled depots!";
      return false;
    }


    return true;
  }



  public function postStockMode($depotId, $principalId, $userId, $switch = 1){  //switch - 1 = turn freeze on, 0 = turn freeze off!


    if($this->postStockModeValidation($depotId, $principalId, $userId, $switch)){

      if($switch == 1){

        $sql = "INSERT INTO `stock_take_mode`
                  (principal_uid, depot_uid, user_uid, datetime)
                VALUES
                (
                  " . mysqli_real_escape_string($this->dbConn->connection, $principalId) . ",
                  " . mysqli_real_escape_string($this->dbConn->connection, $depotId) . ",
                  " . mysqli_real_escape_string($this->dbConn->connection, $userId) . ",
                  NOW()
                )";

      } else {

          $sql = "DELETE FROM `stock_take_mode`
                  WHERE  principal_uid = '" . mysqli_real_escape_string($this->dbConn->connection, $principalId) ."'
                  AND    depot_uid = '" . mysqli_real_escape_string($this->dbConn->connection, $depotId) ."'";

      }

      $this->errorTO = $this->dbConn->processPosting($sql,"");

      if ($this->errorTO->type==FLAG_ERRORTO_SUCCESS) {
        $this->errorTO->description = "Successfully set stock mode!";
      }	else  {
        $this->errorTO->type = FLAG_ERRORTO_ERROR;
        $this->errorTO->description = "Error setting stock mode - ".mysql_error($this->dbConn->connection).$this->errorTO->description;
      }

    }

    return $this->errorTO;

  }


  public function rolloverStockValidation($depotId, $principalId, $userId){

    global $ROOT, $PHPFOLDER;
    include_once($ROOT.$PHPFOLDER.'DAO/StockDAO.php');
    include_once($ROOT.$PHPFOLDER."DAO/DepotDAO.php");
    include_once($ROOT.$PHPFOLDER."DAO/AdministrationDAO.php");
    $stockDAO = new StockDAO($this->dbConn);
    $depotDAO = new DepotDAO($this->dbConn);
    $adminDAO = new AdministrationDAO($this->dbConn);


    $stockMode = $stockDAO->checkStockMode($principalId, $depotId);

    if(!$stockMode){
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description = "You are not in stock take mode, please start over!";
      return false;
    }

    //role
    $hasRole = $adminDAO->hasRole($userId, $principalId, ROLE_STOCK_TAKE);
    if (!$hasRole && !CommonUtils::isAdminUser()) {
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description = "You do not have permissions to preform this function!";
      return false;
    }

    //check if there is any products in pick, display list of products...
    $stockArr = $stockDAO->getStockCountProducts($depotId, $principalId);
    $hasInpickRows = false;
    $inpickArr = array();
    foreach($stockArr as $s){
      if(abs($s['in_pick'])>0){
        $hasInpickRows = true;
        $inpickArr[] = $s['product_code'] . ' -- ' . $s['product_description'];
      }
    }
    if($hasInpickRows){
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description = "Stock rollver failed as there are ".count($inpickArr)." stock item(s) in-pick!<br><u>Products:</u><div style=\"height:80px;overflow:auto;\">" . join("<br>", $inpickArr) . '</div>';
      return false;
    }


    //is a depot type user...
    if(!CommonUtils::isDepotUser()){
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description = "Restricted Access<br>Only Depot users are allowed to do stock take functions!";
      return false;
    }


    //depot exists and is wms?
    $depotArr = $depotDAO->getDepotItem($depotId);
    if(!count($depotArr)>0){
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description = "Invalid depot supplied!";
      return false;
    }
    if(!isset($depotArr[$depotId]['wms'])){
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description = "Invalid depot supplied - WMS array failure!";
      return false;
    }
    if($depotArr[$depotId]['wms'] != 'Y'){
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description = "Operation only allowed on Warehouse Management enabled depots!";
      return false;
    }



    return true;
  }

  public function rolloverStock($depotId, $principalId, $userId){


    $this->dbConn->dbQuery("SET time_zone='+0:00'");

    if($this->rolloverStockValidation($depotId, $principalId, $userId)){

      //STORE STOCK SNAPSHOT
      $sql = "INSERT INTO `stock_audit`
                (
                  principal_uid, depot_uid, principal_product_uid, stock_item, stock_descrip, opening,
                  arrivals, uplifts, returns_cancel, returns_nc, delivered, adjustment, closing, allocations,
                  in_pick, available, lost_sales_cancel, lost_sales_oos, stock_count, stock_count_date,
                  snapshot_type, snapshot_by_uid, snapshot_date
                )
              (
                SELECT principal_id, depot_id, principal_product_uid, stock_item, stock_descrip, opening, arrivals,
                       uplifts, returns_cancel, returns_nc, delivered, adjustment, closing, allocations, in_pick,
                       available, lost_sales_cancel, lost_sales_oos, stock_count, stock_count_date,
                       1, " . mysqli_real_escape_string($this->dbConn->connection, $userId) . ", NOW()
                FROM stock
                WHERE  depot_id = " . mysqli_real_escape_string($this->dbConn->connection, $depotId) . "
                AND    principal_id = " . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "
              )";

      $result = $this->dbConn->processPosting($sql,"");

      if ($result->type != FLAG_ERRORTO_SUCCESS) {
        $this->errorTO->type = FLAG_ERRORTO_ERROR;
        $this->errorTO->description = "Error setting stock mode - ".mysql_error($this->dbConn->connection).$result->description;
      }	else  {


        //ROLL STOCK OVER
        // this sql update re-calculates the allocated stock so a
        // stock rollover now is a complete flush of the stock table,
        // besides using the pervious closing value
        $sql = "UPDATE `stock` s
                  LEFT JOIN (
                        SELECT
                          SUM(d.ordered_qty) as 'total', product_uid
                        FROM document_master m
                        INNER JOIN document_header h on m.uid = h.document_master_uid
                        INNER JOIN document_detail d on m.uid = d.document_master_uid
                        WHERE m.depot_uid = " . mysqli_real_escape_string($this->dbConn->connection, $depotId) . "
                        and m.principal_uid = " . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "
                        and m.document_type_uid IN (" . DT_ORDINV .",". DT_STOCKTRANSFER .",". DT_DELIVERYNOTE .",". DT_ORDINV_ZERO_PRICE .",". DT_WALKIN_INVOICE . ")
                        and h.document_status_uid IN (" . DST_UNACCEPTED .",". DST_ACCEPTED .",". DST_QUEUED . ")
                        GROUP BY d.product_uid
                        ) allocation ON s.principal_product_uid = allocation.product_uid
                  SET
                    opening = closing,
                    arrivals = 0,
                    uplifts = 0,
                    returns_cancel = 0,
                    returns_nc = 0,
                    delivered = 0,
                    adjustment = 0,
                    closing = closing,
                    allocations = IFNULL(allocation.total,0)*-1,
                    in_pick = 0,
                    available = closing - IFNULL(allocation.total,0),
                    lost_sales_cancel = 0,
                    lost_sales_oos = 0,
                    stock_count = closing,
                    stock_count_date = CURDATE(),
                    data_generated_date = NOW(),
                    last_updated = NOW()
                  WHERE  depot_id = " . mysqli_real_escape_string($this->dbConn->connection, $depotId) . "
                     AND principal_id = " . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "
                ";

        $result = $this->dbConn->processPosting($sql,"");


        if ($result->type != FLAG_ERRORTO_SUCCESS) {

          $this->errorTO->type = FLAG_ERRORTO_ERROR;
          $this->errorTO->description = "Error rolling over stock - ".mysql_error($this->dbConn->connection).$result->description;

        } else {

          //DISABLE STOCK MODE
          $result = $this->postStockMode($depotId, $principalId, $userId, $switch = 0);

          if ($result->type != FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description = "Error setting stock mode - ".mysql_error($this->dbConn->connection).$result->description;
          } else {
            $this->errorTO->description = "Successfully set stock mode!";
          }

        }
        return $this->errorTO;

      }
      return $this->errorTO;

    }
    return $this->errorTO;

  }

// &&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  public function insertStockAuditRow($depotId, 
                                      $principalId, 
                                      $userId, 
                                      $captdate,
                                      $batchdate, 
                                      $product_uid, 
                                      $row_seq, 
                                      $qty){
                                      	
      if($this->insertStockAuditRowValidation($depotId, 
                                              $principalId, 
                                              $captdate,
                                              $batchdate, 
                                              $product_uid,
                                              $batchdate,
                                              $qty)){

      $sql = "INSERT INTO `principal_product_date_audit` (`principal_uid`, 
                                                          `depot_uid`, 
                                                          `capture_date`,
                                                          `batch_date`, 
                                                          `product_uid`, 
                                                          `row_seq`, 
                                                          `quantity`, 
                                                          `captured_by_uid`) 
      VALUES ('". mysqli_real_escape_string($this->dbConn->connection, $principalId) ."', 
              '". mysqli_real_escape_string($this->dbConn->connection, $depotId) ."',
              '". mysqli_real_escape_string($this->dbConn->connection, $captdate) ."', 
              '". mysqli_real_escape_string($this->dbConn->connection, $batchdate) ."',   
              '". mysqli_real_escape_string($this->dbConn->connection, $product_uid) ."',
              '". mysqli_real_escape_string($this->dbConn->connection, $row_seq) ."',
              '". mysqli_real_escape_string($this->dbConn->connection, $qty) ."',
              '". mysqli_real_escape_string($this->dbConn->connection, $userId) ."');";
                             
              $result = $this->dbConn->processPosting($sql,"");

        if ($result->type != FLAG_ERRORTO_SUCCESS) {
          $result->description = "Error inserting stock Audit Row - ".mysqli_error($this->dbConn->connection).$result->description;
        }	
        return $result;
     } else {
     	  return $this->errorTO;
     }   
  }
// &&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  public function insertStockAuditRowValidation($depotId, 
                                                $principalId, 
                                                $captdate,
                                                $batchdate, 
                                                $product_uid,
                                                $qty){

    global $ROOT, $PHPFOLDER;

    if($qty == 0){
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description = "Zero Quantity Captured";
      return false;
    }
    
    $sql = "select *
            from .principal_product_date_audit pda
            where pda.principal_uid = ".  mysqli_real_escape_string($this->dbConn->connection, $principalId) ."
            and   pda.depot_uid     = ".  mysqli_real_escape_string($this->dbConn->connection, $depotId) ."
            and   pda.capture_date  = '". mysqli_real_escape_string($this->dbConn->connection, $captdate) ."'
            and   pda.batch_date    = '". mysqli_real_escape_string($this->dbConn->connection, $batchdate) ."'
            and   pda.product_uid   = '". mysqli_real_escape_string($this->dbConn->connection, $product_uid) ."';";   
     
           $arr = $this->dbConn->dbGetAll($sql);
           
           if (count($arr) > 0) {
               $this->errorTO->type = FLAG_ERRORTO_ERROR;
               $this->errorTO->description = "Audit record already exists for this product today";
               return false;
           	
           }

    return true;
  }
// ********************************************************************************************************************************************
   public function UpadteIRLallocations($orderQty, $stockUid) {
  
      $sql = "UPDATE `stock` SET `allocations`= 0 - " . mysqli_real_escape_string($this->dbConn->connection, $orderQty)  .",
                                 `available`  = `closing` - " . mysqli_real_escape_string($this->dbConn->connection, $orderQty)  ."
              WHERE  `uid`= '" . mysqli_real_escape_string($this->dbConn->connection, $stockUid)  ."';";
              
      $result = $this->dbConn->processPosting($sql,"");
      
      if ($result->type != FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description = "Error Update OS Orders - ".mysqli_error($this->dbConn->connection).$result->description;
      } else {
            $this->errorTO->description = "OS Orders Updated";
      }
      return $this->errorTO;
   }
// ********************************************************************************************************************************************
   public function recalculateStockBalances() {
  
      $sql = "update stock a set  a.closing = (a.opening + a.arrivals + a.returns_cancel + a.returns_nc + a.delivered + a.adjustment),
                           a.available      = (a.opening + a.arrivals + a.returns_cancel + a.returns_nc + a.delivered + a.adjustment + a.allocations + a.in_pick)
               where 1;";
  
      $result = $this->dbConn->processPosting($sql,"");
      
      if ($result->type != FLAG_ERRORTO_SUCCESS) {
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description = "Error Recalculating Stock - ".mysql_error($this->dbConn->connection).$result->description;
      } else {
            $this->errorTO->type = FLAG_ERRORTO_SUCCESS; 
            $this->errorTO->description = "Successfully Recalculated Stock";
            $this->dbConn->dbQuery("commit"); 
      }
      return $this->errorTO;
   }
// ********************************************************************************************************************************************


// ********************************************************************************************************************************************
   public function createStockAdjustmentTransaction($principalId, 
                                                    $depUid, 
                                                    $transType, 
                                                    $storeUid,
                                                    $Quantity,
                                                    $captured_by,
                                                    $prodUid) {

    	// Get Order sequence No
         $sequenceDAO = new SequenceDAO($this->dbConn);
         $sequenceTO = new SequenceTO;
         $errorTO = new ErrorTO;
         $sequenceTO->sequenceKey=LITERAL_SEQ_ORDER;
         $sequenceTO->principalUId = $principalId;
         $result=$sequenceDAO->getSequence($sequenceTO,$orderSeqVal);
         
    	// Get Order sequence No
         $sequenceDAO = new SequenceDAO($this->dbConn);
         $sequenceTO = new SequenceTO;
         $errorTO = new ErrorTO;
         $sequenceTO->sequenceKey=LITERAL_SEQ_DOCUMENT_NUMBER;
         $sequenceTO->documentTypeUId = $transType;
         $sequenceTO->principalUId = $principalId;
         $result=$sequenceDAO->getSequence($sequenceTO,$docNoVal);         
         
         $dmsql="INSERT INTO document_master (`depot_uid`, 
                                  `principal_uid`, 
                                  `document_number`,
                                  `document_type_uid`, 
                                  `processed_date`, 
                                  `processed_time`,
                                  `order_sequence_no`, 
                                  `version` ) 
                VALUES ("  .	mysqli_real_escape_string($this->dbConn->connection, $depUid)           . ",
                        "  .	mysqli_real_escape_string($this->dbConn->connection, $principalId)      . ",                
                        '" .	mysqli_real_escape_string($this->dbConn->connection, $docNoVal)         . "',  
                        "  .  mysqli_real_escape_string($this->dbConn->connection, $transType)        . ",   --   document_type_uid
                        '" .  gmdate(GUI_PHP_DATE_FORMAT)    .  "',   --   processed_date
                        '" .  gmdate(GUI_PHP_TIME_FORMAT)    .  "',   --   processed_time           
                        "  .  $orderSeqVal                   .  ",    --   order_sequence_no,             
                        1)  ;                                         --   version " ;
                        
         $this->errorTO = $this->dbConn->processPosting($dmsql,"");
         
         if ($this->errorTO->type != FLAG_ERRORTO_ERROR) {
                 $this->dbConn->dbQuery("commit"); 
                 $dmUId = $this->dbConn->dbGetLastInsertId();                                     
                                            
                 $dhsql="INSERT INTO document_header (document_master_uid, 
                                                   order_date, 
                                                   invoice_date,
                                                   document_status_uid, 
                                                   principal_store_uid, 
                                                   customer_order_number,
                                                   invoice_number, 
                                                   exclusive_total,
                                                   vat_total,
                                                   discount_reference,
                                                   grv_number,
                                                   claim_number,
                                                   cases,
                                                   invoice_total, 
                                                   source_document_number, 
                                                   captured_by)
                                                  
                                                   VALUES (" . $dmUId . ",  
                                                           '" . gmdate(GUI_PHP_DATE_FORMAT)        . "',                           
                                                           '" . gmdate(GUI_PHP_DATE_FORMAT)        . "',   
                                                           "  . DST_PROCESSED                   . " ,
                                                           " . mysqli_real_escape_string($this->dbConn->connection, $storeUid) . " ,
                                                           ''                                       ,
                                                           ''                                       ,
                                                           0                                        ,
                                                           0                                        , 
                                                           ''                                       ,
                                                           ''                                       ,
                                                           ''                                       ,
                                                           " . mysqli_real_escape_string($this->dbConn->connection, $Quantity) . "                        , 
                                                           0                   ,
                                                           ''                                       , 
                                                           " . mysqli_real_escape_string($this->dbConn->connection, $captured_by) .                       ");";       
                 $this->errorTO = $this->dbConn->processPosting($dhsql,"");
                 
                 if ($this->errorTO->type != FLAG_ERRORTO_ERROR) {
                        $this->dbConn->dbQuery("commit"); 
                                  $ddsql="INSERT INTO document_detail (document_master_uid, 
                                                                      line_no, 
                                                                      product_uid, 
                                                                      ordered_qty,
                                                                      document_qty,
                                                                      delivered_qty,
                                                                      selling_price, 
                                                                      discount_value,
                                                                      net_price,
                                                                      extended_price,
                                                                      vat_amount,
                                                                      vat_rate,
                                                                      Discount_reference,
                                                                      total)
                                  VALUES (" .  $dmUId    . ", 
                                               1,
                                               " . mysqli_real_escape_string($this->dbConn->connection, $prodUid).     ",
                                               " . mysqli_real_escape_string($this->dbConn->connection, $Quantity)      . ",
                                               " . mysqli_real_escape_string($this->dbConn->connection, $Quantity)       . ",
                                               " . mysqli_real_escape_string($this->dbConn->connection, $Quantity)       . ",
                                               0,0,0,0,0, " .VAL_VAT_RATE_TBLSTD . ",'', 
                                               0)";  
                                      
                                  $this->errorTO = $this->dbConn->processPosting($ddsql,"");
                                  
                        if ($this->errorTO->type != FLAG_ERRORTO_ERROR) {
                                  $this->dbConn->dbQuery("commit"); 
                                  $this->errorTO->type = FLAG_ERRORTO_SUCCESS;                                 
                                  $this->errorTO->description = "Transaction loaded successfully";
         	              } else {
         	              	        $this->errorTO->type = FLAG_ERRORTO_ERROR;
                	                $this->errorTO->description = "Insert into document detail failed";
         	              }
                 } else { 
                 	     $this->errorTO->type = FLAG_ERRORTO_ERROR; 
                 	     $this->errorTO->description = "Insert into document header failed";
                 }          
         }  else {
         	    $this->errorTO->type = FLAG_ERRORTO_ERROR;
         	    $this->errorTO->description = "Insert into document master failed";
         }     
         return $this->errorTO;
   
   }

// ********************************************************************************************************************************************

   public function updatestockTransferAdjustment($principalId, $depUid, $prodUid, $qty ) {
  
      $sql = "update stock s set s.adjustment = s.adjustment + " . mysqli_real_escape_string($this->dbConn->connection, $qty) . "
              where s.principal_id = " . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "
              and   s.depot_id     = " . mysqli_real_escape_string($this->dbConn->connection, $depUid) . "
              and   s.principal_product_uid = " .mysqli_real_escape_string($this->dbConn->connection, $prodUid). ";";

      $this->errorTO = $this->dbConn->processPosting($sql,"");

      if ($this->errorTO->type != FLAG_ERRORTO_SUCCESS) {
      	     $this->errorTO->type = FLAG_ERRORTO_ERROR;
             $this->errorTO->description = "Error Adjusting Stock - ".mysqli_error($this->dbConn->connection).$result->description;
      } else {
            $this->dbConn->dbQuery("commit");
            $this->errorTO->type = FLAG_ERRORTO_SUCCESS;  
            $this->errorTO->description = "Successfully Adjusted Stock";
      }
      return $this->errorTO;
   }

// ********************************************************************************************************************************************

   public function updatestockTransferArrival($principalId, $depUid, $prodUid, $qty ) {
   	
   	  // Check for stock record
   	  
  	  $StockDAO = new StockDAO($this->dbConn);                                                    
      $stkrec    = $StockDAO->CheckForStockRecord($principalId, $depUid, $prodUid);
         	
      if (sizeof($stkrec)>0) { 
      
           $sql = "update stock s SET s.arrivals = s.arrivals + " . mysqli_real_escape_string($this->dbConn->connection, $qty) . ",
                                      s.closing  = s.closing  + " . mysqli_real_escape_string($this->dbConn->connection, $qty) . "
                   where s.principal_id = " . mysqli_real_escape_string($this->dbConn->connection, $principalId) . "  
                   and   s.depot_id     = " . mysqli_real_escape_string($this->dbConn->connection, $depUid) . "  
                   and   s.principal_product_uid = " .mysqli_real_escape_string($this->dbConn->connection, $prodUid). ";"; 
                   
 
           $result = $this->dbConn->processPosting($sql,"");
           
           if ($result->type != FLAG_ERRORTO_SUCCESS) {
              $this->errorTO->type = FLAG_ERRORTO_ERROR;
              $this->errorTO->description = "Error Recalculating Stock - ". mysqli_error($this->dbConn->connection) .$result->description;
           } else {
           	  $this->errorTO->type = FLAG_ERRORTO_SUCCESS; 
              $this->errorTO->description = "Successfully Recalculated Stock";
              $this->dbConn->dbQuery("commit"); 
           }   
      } else {
      	   // Get product details
      	   $psql = "select pp.product_code, 
                           pp.product_description
                    from .principal_product pp
                    where pp.uid = " . mysqli_real_escape_string($this->dbConn->connection, $prodUid). ";";
      	   
           $stkrec = $this->dbConn->dbGetAll($psql);     
           
           $sssql = "insert into .stock  (principal_id, 
                                  depot_id, 
                                  principal_product_uid, 
                                  stock_item,
                                  stock_descrip,
                                  opening,
                                  arrivals,
                                  returns_cancel,
                                  returns_nc,
                                  delivered,
                                  adjustment,
                                  closing,
                                  allocations,
                                  in_pick,
                                  available) 
                                  VALUES (" . mysqli_real_escape_string($this->dbConn->connection, $principalId) . ",
                                          " . mysqli_real_escape_string($this->dbConn->connection, $depUid)      . ",
                                          " . mysqli_real_escape_string($this->dbConn->connection, $prodUid)     . ",
                                         '" . $stkrec[0]['product_code']        . "' , 
                                         '" . $stkrec[0]['product_description'] . "' , 
                                         0,
                                         " . mysqli_real_escape_string($this->dbConn->connection, $qty) . ",
                                         0,0,0,0,0,0,0,0);";
                                         
                                                                                
           $result = $this->dbConn->processPosting($sssql,"");
          
           if ($result->type != FLAG_ERRORTO_ERROR) {
           	  $this->errorTO->type = FLAG_ERRORTO_SUCCESS; 
              $this->errorTO->description = "Created Stock Records";
              $this->dbConn->dbQuery("commit");            	
           } else {
              $this->errorTO->type = FLAG_ERRORTO_ERROR;
              $this->errorTO->description = "Error Creating Stock Record - ". mysqli_error($this->dbConn->connection) .$result->description;
      	   }     	
      }
      return $this->errorTO;
   }
// ********************************************************************************************************************************************

   public function getGoodsInTransitDepot($psmStoreUId) {
   	  global $ROOT, $PHPFOLDER;
       $sql = "SELECT psm.uid, psm.branch_code, d.allow_git_in
               FROM principal_store_master psm
               INNER JOIN depot d ON d.uid = psm.branch_code
               WHERE psm.uid = " .mysqli_real_escape_string($this->dbConn->connection, $psmStoreUId). ";";    	
       $gitDepot = $this->dbConn->dbGetAll($sql);

       return $gitDepot;  	
   	
   }

// ********************************************************************************************************************************************

   public function checkRecDepProdLine($principalUId, $gitDepot, $productUId) {
   	   global $ROOT, $PHPFOLDER;
       $sql = "SELECT *
               FROM stock s
               WHERE s.principal_id          = " .mysqli_real_escape_string($this->dbConn->connection, $principalUId). "
               AND   s.depot_id              = " .mysqli_real_escape_string($this->dbConn->connection, $gitDepot). "
               AND   s.principal_product_uid = " .mysqli_real_escape_string($this->dbConn->connection, $productUId). ";";   	
   	
      $gitprodLine = $this->dbConn->dbGetAll($sql);

      return $gitprodLine;  	
   	
   }
// ********************************************************************************************************************************************
	public function updateGoodsInTransitDelivered($principalUId, $depotUId, $pProductUId, $processedQty) {
       global $ROOT, $PHPFOLDER;

       $this->dbConn->dbQuery("SET time_zone='+0:00'");

            include_once($ROOT.$PHPFOLDER.'DAO/ProductDAO.php');
            $productDAO = new ProductDAO($this->dbConn);
            $nSI = $productDAO->getNonStockItemByProductUid($pProductUId);

            if($nSI['non_stock_item'] == "N") {
          
               if (trim($processedQty)=="") $processedQty = "0";
                     $direction = -1;
          
                     $sql="UPDATE stock s SET delivered    = delivered   + (abs({$processedQty})*$direction), 
                                            closing        = closing     + (abs({$processedQty})*$direction),
                                            allocations    = allocations + (abs({$processedQty})*$direction),
                                            available      = available   + ((abs($processedQty)-abs($processedQty))*$direction) -- was originally adjusted already so change it by the difference
                           WHERE s.principal_id            = " .mysqli_real_escape_string($this->dbConn->connection, $principalUId). "
                           AND   s.depot_id                = " .mysqli_real_escape_string($this->dbConn->connection, $depotUId). "
                           AND   s.principal_product_uid   = " .mysqli_real_escape_string($this->dbConn->connection, $pProductUId). ";" ;
               
                $this->errorTO = $this->dbConn->processPosting($sql,"");
                
//             file_put_contents($ROOT.$PHPFOLDER.'log/poster.txt',  print_r($this->errorTO, TRUE) , FILE_APPEND);   

                if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {
                     $this->errorTO->description="Failed to GIT Delivered : ".$this->errorTO->description;
                     return $this->errorTO;
                }
                if ($this->errorTO->type==FLAG_ERRORTO_SUCCESS) {
                    $this->errorTO->description="Update Stock GIT Delivered Successful";
                    return $this->errorTO;
		            } 
            } else {
                     return $this->errorTO;      	
            }	
      
  }
// ********************************************************************************************************************************************
	   public function updateGoodsInTransit($principalUId, $depotUId, $productUId, $processedQty) {
          global $ROOT, $PHPFOLDER;
          $sql = "UPDATE stock s SET s.goods_in_transit = if(s.goods_in_transit IS NULL,0,s.goods_in_transit) + " .mysqli_real_escape_string($this->dbConn->connection, $processedQty). "
                  WHERE s.principal_id          = " .mysqli_real_escape_string($this->dbConn->connection, $principalUId). "
                  AND   s.depot_id              = " .mysqli_real_escape_string($this->dbConn->connection, $depotUId). "
                  AND   s.principal_product_uid = " .mysqli_real_escape_string($this->dbConn->connection, $productUId). ";";

          $this->errorTO = $this->dbConn->processPosting($sql,"");

          if ($this->errorTO->type==FLAG_ERRORTO_SUCCESS) {
                 $this->errorTO->description="Update Stock Invoiced Successful";
                 return $this->errorTO;
		      }  else {
                     return $this->errorTO;      	
          }		   	
	   	
	   }                                     

// ********************************************************************************************************************************************
	   public function insertGoodsInTransit($principalUId, $depotUId, $pProductUId, $processedQty, $stock_item, $stock_description) {
       global $ROOT, $PHPFOLDER;

          $sql = "INSERT INTO stock (stock.principal_id,
                                     stock.depot_id,
                                     stock.principal_product_uid,
                                     stock.stock_item,
                                     stock.stock_descrip,
                                     stock.goods_in_transit,
                                     stock.opening,
                                     stock.arrivals,
                                     stock.returns_cancel,
                                     stock.delivered,
                                     stock.adjustment,
                                     stock.closing,
                                     stock.allocations,
                                     stock.in_pick,
                                     stock.available)
                  VALUES ("  .mysqli_real_escape_string($this->dbConn->connection, $principalUId)         . ",
                          "  .mysqli_real_escape_string($this->dbConn->connection, $depotUId)             . ",
                          "  .mysqli_real_escape_string($this->dbConn->connection, $pProductUId)          . ",
                          '" .mysqli_real_escape_string($this->dbConn->connection, $stock_item)           . "',
                          '" .mysqli_real_escape_string($this->dbConn->connection, $stock_description)    . "',
                          "  .mysqli_real_escape_string($this->dbConn->connection, $processedQty)         . ",
                          0,
                          0,
                          0,
                          0,
                          0,
                          0,
                          0,
                          0,
                          0);"; 

          $this->errorTO = $this->dbConn->processPosting($sql,"");

          if ($this->errorTO->type==FLAG_ERRORTO_SUCCESS) {
                 $this->errorTO->description="Update Stock Invoiced Successful";
                 $this->dbConn->dbQuery("commit"); 
                 return $this->errorTO;
		      }  else {
                     return $this->errorTO;      	
          }		

	   }          
// ********************************************************************************************************************************************
	   public function reduceGitFromArrival($principalUId, $depotUId, $pProductUId, $processedQty) {
	   	
           $sql = "UPDATE stock s SET s.goods_in_transit = if(IF(ISNULL(s.goods_in_transit),0,s.goods_in_transit) - "  .mysqli_real_escape_string($this->dbConn->connection, $processedQty) . " <= 0,
                                                              0,
                                                              s.goods_in_transit - "  .mysqli_real_escape_string($this->dbConn->connection, $processedQty) . ")
                   WHERE s.principal_id          = "  .mysqli_real_escape_string($this->dbConn->connection, $principalUId) . "
                   AND   s.depot_id              = "  .mysqli_real_escape_string($this->dbConn->connection, $depotUId)     . "
                   AND   s.principal_product_uid = '" .mysqli_real_escape_string($this->dbConn->connection, $pProductUId)  . "';";	   	
	   	
           $this->errorTO = $this->dbConn->processPosting($sql,"");

           if ($this->errorTO->type==FLAG_ERRORTO_SUCCESS) {
                 $this->errorTO->description="Update Stock Invoiced Successful";
                 $this->dbConn->dbQuery("commit"); 
                 return $this->errorTO;
		       }  else {
                     return $this->errorTO;      	
           }	
	   }

// ********************************************************************************************************************************************
     public function getStockDepotPrin($depotUId) {
	   	
           $sql = "SELECT d.pallet_principal, d.pallet_depot
                   FROM .depot d
                   WHERE d.uid = "  .mysqli_real_escape_string($this->dbConn->connection, $depotUId)     . ";"	;   	
	   	
           $palDep = $this->dbConn->dbGetAll($sql);

           return $palDep;  		   	
	   }

// ********************************************************************************************************************************************
     public function removeStockQtyFromPending($prinID, $depID, $pProductUId, $verifiedQty, $docUid) {
        	
               $sql="UPDATE stock SET pending_dispatch = pending_dispatch - ". mysqli_real_escape_string($this->dbConn->connection, $verifiedQty) . "
                     WHERE principal_id          = '"  . mysqli_real_escape_string($this->dbConn->connection, $prinID) . "'
                     AND   depot_id              = '"  . mysqli_real_escape_string($this->dbConn->connection, $depID)  . "'
                     AND   principal_product_uid = '"  . mysqli_real_escape_string($this->dbConn->connection, $pProductUId) . "'";

  	         $this->errorTO = $this->dbConn->processPosting($sql,"");
  	         
  	         if($this->errorTO->type <> 'S') {
  	         	      echo $sql;
                    return $this->errorTO;
             }
  	         return $this->errorTO;
     }

}
// ********************************************************************************************************************************************
?>
