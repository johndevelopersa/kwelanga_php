<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER."libs/CommonUtils.php");
include_once($ROOT.$PHPFOLDER."libs/ValidationCommonUtils.php");
include_once($ROOT.$PHPFOLDER."DAO/AdministrationDAO.php");
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');

/*
 * NOTE : The validation should return IMMEDIATELY if an error type occurred (required for the way screen is processed)
 */

class PostProductDAO {
	public $errorTO;
	private $dbConn;

	function __construct($dbConn) {
		$this->dbConn = $dbConn;
		$this->errorTO = new ErrorTO;
    }

    /*
     *  sss
     *
     */


     public function postProductValidation($postingProductTO) {
    	global $ROOT; global $PHPFOLDER;
    	include_once($ROOT.$PHPFOLDER."DAO/AdministrationDAO.php");
    	include_once($ROOT.$PHPFOLDER."DAO/ProductDAO.php");
    	$administrationDAO = new AdministrationDAO($this->dbConn);
    	$productDAO = new ProductDAO($this->dbConn);

    	// get user. don't pass it because this is more secure.
    	if (!isset($_SESSION)) session_start();
    	$userId = $_SESSION['user_id'];
    	$principalId = $_SESSION['principal_id']; // used for hasRole validation
      $systemId = ((isset($_SESSION['system_id']))?$_SESSION['system_id']:SYS_KWELANGA); // used for hasRole validation

    	if (!ValidationCommonUtils::checkPostingType($postingProductTO->DMLType)) return false;

      if (($postingProductTO->DMLType=="INSERT") && ($postingProductTO->UId!="")) {
        $this->errorTO->type=FLAG_ERRORTO_ERROR;
        $this->errorTO->description="UId must be blank for INSERT";
        return false;
      };

		if ($postingProductTO->DMLType=="UPDATE") {
			if($postingProductTO->UId=="") {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="Update requires a product UID";
				return false;
			};
			if ($userId==SESSION_ADMIN_USERID) {
				// NB: ignore the deleted flag for uploads from backend
				$mfP=$productDAO->getPrincipalProductItem($principalId,$postingProductTO->UId);
			} else {
				$mfP=$productDAO->getUserPrincipalProductItem($principalId,$postingProductTO->UId,$userId);
			}
			if(sizeof($mfP)==0) {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="Principal Product not found for Update, or user does not have permissions for this product";
				return false;
			};
      if (substr($mfP[0]["product_code"],0,3)=="SYS") {
        $this->errorTO->type=FLAG_ERRORTO_ERROR;
        $this->errorTO->description="You cannot edit a system product";
        return false;
      }
			// check roles
			if (($mfP[0]['status']!=$postingProductTO->status) && ($postingProductTO->status==FLAG_STATUS_DELETED)) {
				$hasRole = $administrationDAO->hasRole($userId,$principalId,ROLE_DELETE_PRODUCT);
				if(!$hasRole) {
					$this->errorTO->type=FLAG_ERRORTO_ERROR;
					$this->errorTO->description="You do not have permissions to Delete/Undelete Products";
					return false;
				};
			}
			$hasRole = $administrationDAO->hasRole($userId,$principalId,ROLE_MODIFY_PRODUCT);
			if(!$hasRole) {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="You do not have permissions to Modify Products";
				return false;
			};
			if(!in_array($postingProductTO->status,array("A","S","D"))) {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="Invalid Status Flag";
				return false;
			};

		} else {
				// check roles
				$hasRole = $administrationDAO->hasRole($userId,$principalId,ROLE_ADD_PRODUCT);
				if(!$hasRole) {
					$this->errorTO->type=FLAG_ERRORTO_ERROR;
					$this->errorTO->description="You do not have permissions to Add Products";
					return false;
				};
				// check for duplication of product
				$sql="select 1
						from principal_product
						where principal_uid = ".mysqli_real_escape_string($this->dbConn->connection, $postingProductTO->principal)."
						and   product_code = '".mysqli_real_escape_string($this->dbConn->connection, $postingProductTO->productCode)."'"; // product may exist (hidden) but is flagged as deleted

			    $this->dbConn->dbinsQuery($sql);
				if ($this->dbConn->dbQueryResultRows > 0) {
					$this->errorTO->type=FLAG_ERRORTO_ERROR;
					$this->errorTO->description="A product for this Principal and Product Code already exists (or has been marked as deleted). If it has been marked as deleted, you need to unflag it throught the \"maintain products\" screen to be able to use it.";
					return false;
				}
				// end duplicate check
			} // end INSERT

		if (($postingProductTO->weight=="") || (!preg_match(GUI_PHP_FLOAT_REGEX,$postingProductTO->weight))) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Invalid Product WEIGHT value. Must be Numeric Integer";
			return false;
		};

		if (($postingProductTO->productVATRate=="") || (!preg_match(GUI_PHP_FLOAT_REGEX,$postingProductTO->productVATRate))) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Invalid Product VAT RATE value. Must be Numeric Integer".$postingProductTO->productVATRate;
			return false;
		};


                //ONLY ALLOW INT - as calculations might/will run off these in the future...
                if (!empty($postingProductTO->sizeWidth) && !is_numeric($postingProductTO->sizeWidth)){
                  $this->errorTO->type=FLAG_ERRORTO_ERROR;
                  $this->errorTO->description="Invalid 'Dimensions : Width' must be numeric!";
                  return false;
		}
                if (!empty($postingProductTO->sizeLength) && !is_numeric($postingProductTO->sizeLength)){
                  $this->errorTO->type=FLAG_ERRORTO_ERROR;
                  $this->errorTO->description="Invalid 'Dimensions : Length' must be numeric!";
                  return false;
		}
                if (!empty($postingProductTO->sizeHeight) && !is_numeric($postingProductTO->sizeHeight)){
                  $this->errorTO->type=FLAG_ERRORTO_ERROR;
                  $this->errorTO->description="Invalid 'Dimensions : Height' must be numeric!";
                  return false;
		}

                if (!empty($postingProductTO->unitValue) && !is_numeric($postingProductTO->unitValue)){
                  $this->errorTO->type=FLAG_ERRORTO_ERROR;
                  $this->errorTO->description="Invalid 'Unit Value' must be numeric!";
                  return false;
		}


		if (!preg_match(GUI_PHP_INTEGER_REGEX,$postingProductTO->unitsPerPallet)) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Units per Pallet must be a positive integer";
			return false;
		};
		if (!ValidationCommonUtils::checkFieldYesNoSimple($postingProductTO->enforcePalletConsignment)) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Invalid value for enforce-pallet-consignment";
			return false;
		};
		if ($postingProductTO->enforcePalletConsignment=="Y")

		if (trim($postingProductTO->productDescription)=="") {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Product description cannot be blank";
			return false;
		};
    
		if (($postingProductTO->itemsPerCase=="") || (!preg_match(GUI_PHP_INTEGER_REGEX,$postingProductTO->itemsPerCase))) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Invalid Product Items-per-Case value. Must be Numeric Integer, (use default of 1 if applicable)";
			return false;
		};

    // Principal Product Minor Group(s)
    foreach ($postingProductTO->principalProductMinorCategoryTOArr as $TO) {
      if ($TO->principalProductUId != $postingProductTO->UId) {
        $this->errorTO->type=FLAG_ERRORTO_ERROR;
        $this->errorTO->description="There is a discrepancy between the product uid and the uid passed for Product Minor Group creation!";
        return false;
      }
      // productMinorGroupUId is validated as part of insert / update
    }


    //required minor categories -
    if(count($postingProductTO->principalProductMinorCategoryTOArr)>0){

      $minArr = $productDAO->getProductMinorCategoryLables($principalId, $systemId);
      foreach($postingProductTO->principalProductMinorCategoryTOArr as $key => $TO){

        $row = false;
        foreach($minArr as $cat){
          if($cat['uid'] == $TO->minorCategoryTypeUId){
            $row = $cat;
            break;
          }
        }

        if($row === false){
          $this->errorTO->type=FLAG_ERRORTO_ERROR;
          $this->errorTO->description="Error - invalid Category Type : " . $TO->minorCategoryTypeUId . " ";
          return false;
        }

        if($row['required'] == 'Y' && trim($TO->productMinorCategoryUId)==""){
          $this->errorTO->type=FLAG_ERRORTO_ERROR;
          $this->errorTO->description = $row['lable'] . ' is a required minor category!';
          return false;
        } else {

          //is not required and empty pop off to
          if(trim($TO->productMinorCategoryUId)==""){
            unset($postingProductTO->principalProductMinorCategoryTOArr[$key]);
          }

        }

      }

    }

    // enforce unique gtins
    // NB : This relies on array being same size for SKU and OC barcode arrays ! This will be the case if the new product is coming from productSubmit.
    //      EDI will call a different procedure for UPDATES and will neer have barcodes for INSERTS (when this same func is called)
    if (is_array($postingProductTO->skuGTINList)) {
      $uid=(($postingProductTO->UId=="")?"0":$postingProductTO->UId);
      $skuList=((implode("','",array_filter($postingProductTO->skuGTINList))=="")?"'EMPTY'":"'".implode("','",array_filter($postingProductTO->skuGTINList))."'");
      $ocList=((implode("','",array_filter($postingProductTO->outerCasingGTINList))=="")?"'EMPTY'":"'".implode("','",array_filter($postingProductTO->outerCasingGTINList))."'");
      $sql="select a.product_code
            from  principal_product a,
                  principal_product_depot_gtin b
            where a.uid = b.principal_product_uid
            and   a.principal_uid = {$principalId}
            and   principal_product_uid!={$uid}
            and   (sku_gtin in ({$skuList}) or outercasing_gtin in ({$ocList}))";


      $dups=$this->dbConn->dbGetAll($sql);
      if (sizeof($dups) > 0) {
        $this->errorTO->type=FLAG_ERRORTO_ERROR;
        $this->errorTO->description = "Duplicated SKU / OUTERCASING GTINs found (against product code {$dups[0]["product_code"]}).";
        return false;
      }
    }


    return true;

    }


     public function postProduct($postingProductTO, $userId) {

        // defaults
     	$prodStr = md5($postingProductTO->principal . "|" . addSlashes( $postingProductTO->productCode )) ;
     	$postingProductTO->productCode=trim(strtoupper($postingProductTO->productCode));
     	if ($postingProductTO->majorCategory=='') $postingProductTO->majorCategory = '0';
     	if ($postingProductTO->minorCategory=='') $postingProductTO->minorCategory = '0';
     	if ($postingProductTO->enforcePalletConsignment=="") $postingProductTO->enforcePalletConsignment="N";
     	if ($postingProductTO->unitsPerPallet=="") $postingProductTO->unitsPerPallet="0";
     	if ($postingProductTO->itemsPerCase=="") $postingProductTO->itemsPerCase="1";
     	if ($postingProductTO->nonStockItem=="") $postingProductTO->nonStockItem="N";
     	if ($postingProductTO->status=="") $postingProductTO->status=FLAG_STATUS_ACTIVE;
     	if (trim($postingProductTO->productVATRate)=="") $postingProductTO->productVATRate="0";
      if(trim($postingProductTO->unitValue)=="") $postingProductTO->unitValue = 0;
      if(trim($postingProductTO->sizeType)=="") $postingProductTO->sizeType = 0;
      if(trim($postingProductTO->sizeWidth)=="") $postingProductTO->sizeWidth = 0;
      if(trim($postingProductTO->sizeLength)=="") $postingProductTO->sizeLength = 0;
      if(trim($postingProductTO->sizeHeight)=="") $postingProductTO->sizeHeight = 0;

     	$resultOK = $this->postProductValidation($postingProductTO);
    	if ($resultOK) {
    		$this->dbConn->dbQuery("SET time_zone='+0:00'"); // important to make sure PHPBACKEND which sits in RSA synchs changes updated according to SA time (unsynched table is in RSA)
    		if ($postingProductTO->DMLType=="UPDATE") {

          $vatExclAuthorisedBy=((($postingProductTO->productVATRate==0) && ($postingProductTO->vatExclAuthorisedByFlag=="Y"))?$userId:"NULL"); // only overwrite with new ID if not already set
	    		$sql="UPDATE principal_product pp
	    		               left join stock s on s.principal_product_uid = ".$postingProductTO->UId."
    				  SET pp.product_description='".mysqli_real_escape_string($this->dbConn->connection, substr($postingProductTO->productDescription,0,50))."',
                  -- ean_code='',
                  -- outer_casing_gtin='',
                  pp.packing='".mysqli_real_escape_string($this->dbConn->connection, $postingProductTO->packing)."',
                  pp.weight='".$postingProductTO->weight."',
                  pp.vat_rate='".mysqli_real_escape_string($this->dbConn->connection, trim($postingProductTO->productVATRate))."',
                  pp.enforce_pallet_consignment='".$postingProductTO->enforcePalletConsignment."',
                  pp.non_stock_item='".$postingProductTO->nonStockItem."',
                  pp.units_per_pallet='".$postingProductTO->unitsPerPallet."',
                  pp.major_category='".$postingProductTO->majorCategory."',
                  pp.minor_category='".$postingProductTO->minorCategory."',
                  pp.alt_code='{$postingProductTO->altCode}',
                  pp.status='{$postingProductTO->status}',
                  pp.last_updated = now(),
                  pp.last_change_by_userid = '{$userId}',
                  pp.items_per_case = '{$postingProductTO->itemsPerCase}',
                  pp.unit_value = '{$postingProductTO->unitValue}',
                  pp.size_type = '{$postingProductTO->sizeType}',
                  pp.size_width = '{$postingProductTO->sizeWidth}',
                  pp.size_length = '{$postingProductTO->sizeLength}',
                  pp.size_height = '{$postingProductTO->sizeHeight}',
                  pp.vat_excl_authorised_by = if(isnull({$vatExclAuthorisedBy}) || (!isnull({$vatExclAuthorisedBy}) && isnull(vat_excl_authorised_by)),{$vatExclAuthorisedBy},vat_excl_authorised_by),

                  s.stock_descrip = '".mysqli_real_escape_string($this->dbConn->connection, substr($postingProductTO->productDescription,0,50))."',
                  pp.web_capture   = '".mysqli_real_escape_string($this->dbConn->connection, $postingProductTO->webCapture)."',
                  pp.load_to_shopify   = '".mysqli_real_escape_string($this->dbConn->connection, $postingProductTO->loadToShopify)."',
                  pp.no_discount       = '".mysqli_real_escape_string($this->dbConn->connection, $postingProductTO->noDiscounts)."',
                  pp.allow_decimal     = '".mysqli_real_escape_string($this->dbConn->connection, $postingProductTO->allowDecimal)."'
              WHERE pp.uid=".$postingProductTO->UId;

	    		if ($postingProductTO->lastUpdated!="") $sql.= " AND   (last_updated <= '".$postingProductTO->lastUpdated."' OR last_updated is null) ";

    		} else if ($postingProductTO->DMLType=="INSERT") {
    			
                    $sql="INSERT INTO `principal_product`
                          (
                              `principal_uid`,
                              `product_code`,
                              `product_description`,
                              `packing`,
                              `ean_code`,
                              `weight`,
                              `vat_rate`,
                              `enforce_pallet_consignment`,
                              `non_stock_item`,
                              `units_per_pallet`,
                              `major_category`,
                              `minor_category`,
                              `status`,
                              `product_string`,
                              alt_code,
                              items_per_case,
                              unit_value,
                              size_type,
                              size_width,
                              size_length,
                              size_height,
                              last_updated,
                              last_change_by_userid,
                              vat_excl_authorised_by,
                              web_capture,
                              `load_to_shopify`,
                              `no_discount`,
                              `allow_decimal`
                          ) VALUES (".
                              $postingProductTO->principal.",".
                              "'" . mysqli_real_escape_string($this->dbConn->connection, $postingProductTO->productCode) ."',".
                              "'" . mysqli_real_escape_string($this->dbConn->connection, substr($postingProductTO->productDescription,0,50)) . "',".
                              "'" . mysqli_real_escape_string($this->dbConn->connection, $postingProductTO->packing) ."',".
                              "'',".
                              $postingProductTO->weight.",".
                              mysqli_real_escape_string($this->dbConn->connection, trim($postingProductTO->productVATRate)).",".
                              "'".$postingProductTO->enforcePalletConsignment."',".
                              "'".$postingProductTO->nonStockItem."',".
                              $postingProductTO->unitsPerPallet.",".
                              $postingProductTO->majorCategory.",".
                              $postingProductTO->minorCategory.",'".
                              $postingProductTO->status."',".
                              "'".$prodStr."',
                              '{$postingProductTO->altCode}',
                              '{$postingProductTO->itemsPerCase}'," .
                              mysqli_real_escape_string($this->dbConn->connection, trim($postingProductTO->unitValue)).",".
                              mysqli_real_escape_string($this->dbConn->connection, trim($postingProductTO->sizeType)).",".
                              mysqli_real_escape_string($this->dbConn->connection, trim($postingProductTO->sizeWidth)).",".
                              mysqli_real_escape_string($this->dbConn->connection, trim($postingProductTO->sizeLength)).",".
                              mysqli_real_escape_string($this->dbConn->connection, trim($postingProductTO->sizeHeight)).",
                              now(),
                              '{$userId}',".
                              ((($postingProductTO->productVATRate==0) && ($postingProductTO->vatExclAuthorisedByFlag=="Y"))?$userId:"NULL") . ",'" .
                              mysqli_real_escape_string($this->dbConn->connection, trim($postingProductTO->webCapture))."','".
                              mysqli_real_escape_string($this->dbConn->connection, trim($postingProductTO->loadToShopify))."','" .
                              mysqli_real_escape_string($this->dbConn->connection, trim($postingProductTO->noDiscounts))  ."','"  .
                              mysqli_real_escape_string($this->dbConn->connection, trim($postingProductTO->allowDecimal))."')";
	    	} else {
                  return $this->errorTO; // not a recognised DMLType
	    	}

	    	$this->errorTO = $this->dbConn->processPosting($sql,$postingProductTO->productDescription);

        if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {

          $this->errorTO->type = FLAG_ERRORTO_ERROR;
          $this->errorTO->description = "Failed to create/update Product!" . $this->errorTO->description ;
          return $this->errorTO;

        } else {

          if ($postingProductTO->DMLType=="INSERT") {
            $postingProductTO->UId = $this->dbConn->dbGetLastInsertId();
            if ($postingProductTO->UId=="") {
              $this->errorTO->type = FLAG_ERRORTO_ERROR;
              $this->errorTO->description = "Failed to retrieve UID after creating product!";
              return $this->errorTO;
            }
          }

        }


        // START : GTINs
      	//PRODUCT SQL OK -  MOVE TO DEPOT LINKED GTIN - NO DIFF BWT INSERT / UPDATE - AS EXISTING ROWS ARE DELETED FIRST.
      	$this->errorTO->identifier = $productId = $postingProductTO->UId;

        $errorTO_DepotGTIN = new ErrorTO;
      	$errorTO_DepotGTIN->type = FLAG_ERRORTO_SUCCESS;

      	//DELETE EXISTING ROWS. --> only barcodes from productSubmit should enter here !!
        if( is_array($postingProductTO->skuGTINList) && count($postingProductTO->skuGTINList) > 0){

        	$sqlDELETEDepotGTIN = 'DELETE FROM `principal_product_depot_gtin` WHERE principal_product_uid = '.mysqli_real_escape_string($this->dbConn->connection, $productId) ;
        	$errorTO_DepotGTIN = $this->dbConn->processPosting($sqlDELETEDepotGTIN,$postingProductTO->productDescription);
          if($errorTO_DepotGTIN->type != FLAG_ERRORTO_SUCCESS){
            return $errorTO_DepotGTIN; //catch failure of deleting
          }

          $sqlDepotGTIN = 'INSERT into `principal_product_depot_gtin`
                                        (
                                                principal_product_uid,
                                                depot_uid,
                                                sku_gtin,
                                                outercasing_gtin
                                        )
                                        VALUES ';

            $sqlDepotGTIN_Values = array();
            for($i = 0; $i<count($postingProductTO->skuGTINList); $i++){
              //don't insert empty values.
              if($postingProductTO->skuGTINList[$i] != '' || $postingProductTO->outerCasingGTINList[$i] != '') {
                $depotUID = ($postingProductTO->gtinDepotUidList[$i] != '') ? ($postingProductTO->gtinDepotUidList[$i]) : ('NULL');
                $sqlDepotGTIN_Values[] = '(' . $productId . ','.$depotUID . ',\'' . $postingProductTO->skuGTINList[$i] . '\',\'' . $postingProductTO->outerCasingGTINList[$i] . '\')';
              }
            }

      		  //Check array as it might be empty => no values were set, why save?
      		  if(count($sqlDepotGTIN_Values)>0){
      		    $sqlDepotGTIN .= join(',',$sqlDepotGTIN_Values);
      		    $errorTO_DepotGTIN = $this->dbConn->processPosting($sqlDepotGTIN,$postingProductTO->productDescription);

            if ($errorTO_DepotGTIN->type != FLAG_ERRORTO_SUCCESS) {
              $this->errorTO->type = FLAG_ERRORTO_ERROR;
              $this->errorTO->description = "Principal - Failure on Insert Depot GTIN Codes.";
              return $this->errorTO;
            }



    		  }
    		}

        // END : GTIN


        // START : Minor Groups

        if (sizeof($postingProductTO->principalProductMinorCategoryTOArr)>0) {
          //DELETE EXISTING ROWS.
          $sqlDelete = 'DELETE FROM `principal_product_minor_category` WHERE principal_product_uid = '.mysqli_real_escape_string($this->dbConn->connection, $postingProductTO->UId) ;
          $result = $this->dbConn->processPosting($sqlDelete,$postingProductTO->productDescription);

          if($result->type!=FLAG_ERRORTO_SUCCESS){
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description = "Unable to Delete existing Product Minor Groups!";
            return $this->errorTO;
          }

          $vals=array();
          foreach ($postingProductTO->principalProductMinorCategoryTOArr as &$TO) {
            $TO->principalProductUId = $postingProductTO->UId; // set for passback as double security
            $vals[] = $TO->productMinorCategoryUId;
          } // end loop

          $sql = "INSERT into`principal_product_minor_category` (principal_product_uid, product_minor_category_uid)
                  SELECT {$postingProductTO->UId}, uid
                  FROM   product_minor_category b
                  WHERE  b.principal_uid = {$postingProductTO->principal}
                  AND    b.uid in (".(implode(",",$vals)).")";

                //  echo $sql;
          $this->errorTO = $this->dbConn->processPosting($sql,$postingProductTO->productDescription);

          if($this->errorTO->type!=FLAG_ERRORTO_SUCCESS){
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description = "Error Occurred inserting / updating Principal Product Minor Groups!".$sql;
            return $this->errorTO;
          }

        }

      } else {
        // validation failed
        return $this->errorTO;  //catch failure of product : insert / update.
      }

      if ($postingProductTO->DMLType=="INSERT") {
        $this->errorTO->description="Principal - Product Successfully Inserted.";
      } else if ($postingProductTO->DMLType=="UPDATE"){
        $this->errorTO->description="Principal - Product Successfully Updated.";
      }

      $this->errorTO->identifier = $postingProductTO->UId;

      return $this->errorTO;

   }


   // onlineFileProcessing updates only
   public function postProductOFP($principalUId, $pUId, $changeDateTime, $vatRate=false, $description=false, $status=false) {
   		$set="";

      $set=array();

   		if($vatRate!==false) {
   			if (trim($vatRate)=="") $vatRate="0";
        $set[] = "pp.vat_excl_authorised_by = if ({$vatRate}!=pp.vat_rate,NULL,pp.vat_excl_authorised_by)"; // must come first
   			$set[] = "pp.vat_rate='".mysqli_real_escape_string($this->dbConn->connection, trim($vatRate))."' ";
   		}
   		if($description!==false) {
   		  $set[] =" pp.product_description='".mysqli_real_escape_string($this->dbConn->connection, substr($description,0,50))."' ";
   		  $set[] =" s.stock_descrip='".mysqli_real_escape_string($this->dbConn->connection, substr($description,0,50))."' ";
   		}
      if($status!==false) {
        if ($status==FLAG_STATUS_ACTIVE) {
          $set[] = "pp.status='{$status}' ";
        } else {
          $this->errorTO->type=FLAG_ERRORTO_ERROR;
          $this->errorTO->description="Invalid product status passed for update in postProductOFP.";
          return $this->errorTO;
        }
      }

   		$sql = "update principal_product pp
   		                  left join stock s on s.principal_product_uid = ".$pUId."
				set
				    ".implode(",",$set)."
				where pp.uid = '{$pUId}'
				and   pp.principal_uid = '{$principalUId}'
				and   (pp.last_updated <= '{$changeDateTime}' or pp.last_updated is null)";

		$this->errorTO = $this->dbConn->processPosting($sql,"");

		if ($this->errorTO->type==FLAG_ERRORTO_SUCCESS) {
			$this->errorTO->description="Principal-Product Successfully updated.";
		}

		return $this->errorTO;
   }

   /*
    *
    * PRICING :
    * NOTE - This TO CAN contain a list of products / product groups and chains / stores, UID fieldnames could be a list !
    *
    */

   public function postPricingValidation($postingPricingDealTO) {
    	global $ROOT; global $PHPFOLDER;
    	include_once($ROOT.$PHPFOLDER."DAO/AdministrationDAO.php");
    	include_once($ROOT.$PHPFOLDER."DAO/ProductDAO.php");
    	$administrationDAO = new AdministrationDAO($this->dbConn);
    	$productDAO = new ProductDAO($this->dbConn);

    	// get user. don't pass it because this is more secure.
    	if (!isset($_SESSION)) session_start();
    	$userId = $_SESSION['user_id'];
    	$principalId = $_SESSION['principal_id']; // used for hasRole validation

    	if (!ValidationCommonUtils::checkPostingType($postingPricingDealTO->DMLType)) return false;

    	$arrProductList=explode(",",trim($postingPricingDealTO->principalProdUid));
    	$arrLocationList=explode(",",trim($postingPricingDealTO->chainOrStoreUid));

		if (($postingPricingDealTO->DMLType!="INSERT") && ((sizeof($arrProductList)>1) || (sizeof($arrLocationList)>1))) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Bulk pricing load is limited to the Bulk Screen, and only Inserts are catered for.";
			return false;
		}
		if (trim($postingPricingDealTO->principalProdUid)=="") {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="A Product/Product Group must be specified";
			return false;
		}
		if (trim($postingPricingDealTO->chainOrStoreUid)=="") {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="A Chain/Store must be specified";
			return false;
		}

		if ($postingPricingDealTO->DMLType=="UPDATE") {
			$mfP=$productDAO->getUserPrincipalPricingItem($userId,$principalId,$postingPricingDealTO->pduid);
			if (sizeof($mfP)==0) {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="Pricing Item being Updated cannot be found.";
				return false;
			}
			if ($mfP[0]['deleted']==1) {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="Pricing Item being Updated is marked as deleted already. Only Active items can be Updated.";
				return false;
			}

			// check roles
    		$hasMPRole = $administrationDAO->hasRole($userId,$principalId,ROLE_MODIFY_PRICE);
    		if(!$hasMPRole) {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="You do not have permissions to Modify Pricing";
				return false;
			};
    		if ($mfP[0]['end_date']!=$postingPricingDealTO->endDate) {
				$hasEEDRole = $administrationDAO->hasRole($userId,$principalId,ROLE_EXTEND_ENDDATE_PRICE);
				if (!$hasEEDRole) {
					$this->errorTO->type=FLAG_ERRORTO_ERROR;
					$this->errorTO->description="You do not have permissions to Modify Pricing Item End Date.";
					return false;
				}
			}
			if ($mfP[0]['deleted']!=$postingPricingDealTO->deleted) {
				$hasDPRole = $administrationDAO->hasRole($userId,$principalId,ROLE_DELETE_PRICE);
				if (!$hasDPRole) {
					$this->errorTO->type=FLAG_ERRORTO_ERROR;
					$this->errorTO->description="You do not have permissions to Delete Pricing Items.";
					return false;
				}
			}
		} else {
			// check roles
			$hasRole = $administrationDAO->hasRole($userId,$principalId,ROLE_ADD_PRICE);
			if(!$hasRole) {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="You do not have permissions to Add Pricing";
				return false;
			};
		  }

		if (($postingPricingDealTO->DMLType=="UPDATE") && ($postingPricingDealTO->pduid=="")) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Update requires a UID.";
			return false;
		};

		if (($postingPricingDealTO->priceTypeUId!=PRT_PRODUCT) && ($postingPricingDealTO->priceTypeUId!=PRT_PRODUCT_GROUP)) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Invalid Product Level";
			return false;
		};

		// the pricing screen has been modified so that the inserts can happen in bulk if coming from the bulk insert screen. Passed as a set from submit processor
		// Otherwise Updates and Inserts happen singularly because they are passed one by one to this function from the submit processor
		if ($postingPricingDealTO->priceTypeUId==PRT_PRODUCT) {
			$mfP = $productDAO->getUserPrincipalProductsArray($principalId, $userId, $allProducts = false, $showOnlyProductsInTT = false, $arrayIndex="uid", $productUIDList=$postingPricingDealTO->principalProdUid);
		} else {
			$mfP = $productDAO->getPrincipalProductCategoryArray($principalId, FLAG_STATUS_ACTIVE, $arrayIndex="uid", $pcUIDList=$postingPricingDealTO->principalProdUid);
		}

		if ($postingPricingDealTO->DMLType=="INSERT") {
			// if product level check the product
			foreach ($arrProductList as $r) {
				if ($postingPricingDealTO->priceTypeUId==PRT_PRODUCT) {
					if (!isset($mfP[$r])) {
						$this->errorTO->type=FLAG_ERRORTO_ERROR;
						$this->errorTO->description="Invalid Product value {$r}, or user does not have permissions for this product.";
						return false;
					};
					if ($mfP[$r]['status']==FLAG_STATUS_DELETED) {
						$this->errorTO->type=FLAG_ERRORTO_ERROR;
						$this->errorTO->description="This product {$mfP[$r]['product_code']} has been deleted.";
						return false;
					};
				} else {
					if (!isset($mfP[$r])) {
						$this->errorTO->type=FLAG_ERRORTO_ERROR;
						$this->errorTO->description="Invalid Product Category {$r}.";
						return false;
					};
					if ($mfP[$r]['status']!=FLAG_STATUS_ACTIVE) {
						$this->errorTO->type=FLAG_ERRORTO_ERROR;
						$this->errorTO->description="This Product Category {$mfP[$r]['description']} is not active.";
						return false;
					};
				}
			}

			if (($postingPricingDealTO->discountValue>0) && (($postingPricingDealTO->dealTypeID!=VAL_DEALTYPE_AMOUNT_OFF) && ($postingPricingDealTO->dealTypeID!=VAL_DEALTYPE_PERCENTAGE))) {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="Discount Value must be > zero if Deal Type is 'Amount Off' or 'Percentage'.";
				return false;
			}
			// allow zero prices now as at 12Mar2012
			if ($postingPricingDealTO->listPrice<0) {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="List Price cannot be negative.";
				return false;
			}
			if ($postingPricingDealTO->exclInclFlag!=substr(LITERAL_DEAL_EXCLUSIVE,0,1)) {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="Only EXCLUSIVE of VAT is permitted for consistency.";
				return false;
			}
			// incorrect values section
			$mfDT=$productDAO->getDealType("uid");
			if(!isset($mfDT[$postingPricingDealTO->dealTypeID])) {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="Invalid Deal Type value.";
				return false;
			};
			if(!preg_match(GUI_PHP_FLOAT_REGEX,$postingPricingDealTO->listPrice)) {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="Invalid List Price value.".$postingPricingDealTO->listPrice."-".$postingPricingDealTO->principalProdUid;
				return false;
			};
			if(!preg_match(GUI_PHP_FLOAT_REGEX,$postingPricingDealTO->discountValue)) {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="Invalid Discount Value.";
				return false;
			};
			// check start date
			if (preg_match(GUI_PHP_DATE_VALIDATION,$postingPricingDealTO->startDate,$parts)) {
				if(!checkdate($parts[2],$parts[3],$parts[1])) {
					$this->errorTO->type=FLAG_ERRORTO_ERROR;
					$this->errorTO->description="Invalid Start Date format.";
					return false;
				}
			} else {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="Invalid Start Date format.";
				return false;
			  }

			// check customer type
			if(($postingPricingDealTO->customerTypeUid!=CT_CHAIN) && ($postingPricingDealTO->customerTypeUid!=CT_STORE)) {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="Customer Type Invalid";
				return false;
			}

			// check customer type
			if(strlen($postingPricingDealTO->reference)>50) {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="Reference cannot be longer than 50 chars";
				return false;
			}
		} // end INSERT
		// check end date
		if (preg_match(GUI_PHP_DATE_VALIDATION,$postingPricingDealTO->endDate,$parts)) {
			if(!checkdate($parts[2],$parts[3],$parts[1])) {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="Invalid End Date format.";
				return false;
			}
		} else {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Invalid End Date format.";
			return false;
		  }
		// check start date not after end date
		if(strtotime($postingPricingDealTO->startDate)>strtotime($postingPricingDealTO->endDate)) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Start Date cannot be after End Date.";
			return false;
		}
		/*
		// check dates not too far in future
		if(((abs(strtotime($arrSTARTDATE[$i])-time()))/(60*60*24))>365) {
			$returnMessages->type=FLAG_ERRORTO_ERROR;
			$returnMessages->description="Dates cannot be more than 1 year in future/past";
			print(CommonUtils::getJavaScriptMsg($returnMessages));
			return;
		}
		*/


		// check for duplication of date range for product, must join on principal so it uses proper indexes
		// only do the check if not bulk insert
		if ((sizeof($arrProductList)==1) && (sizeof($arrLocationList)==1)) {
			if (strval($postingPricingDealTO->deleted)!="1") {
				$sql="select 1
						from pricing
						where principal_uid = '{$postingPricingDealTO->principalUid}'
						and   principal_product_uid = '".mysqli_real_escape_string($this->dbConn->connection, $postingPricingDealTO->principalProdUid)."'
						and   chain_store = '".mysqli_real_escape_string($this->dbConn->connection, $postingPricingDealTO->chainOrStoreUid)."'
						and   deleted=0
						and   start_date = '".$postingPricingDealTO->startDate."'
						and   end_date = '".$postingPricingDealTO->endDate."'
						and   customer_type_uid = '".$postingPricingDealTO->customerTypeUid."'
						and   price_type_uid = '{$postingPricingDealTO->priceTypeUId}'".
						(($postingPricingDealTO->pduid=="")?"":"uid!={$postingPricingDealTO->pduid}");

				$sql.="and   uid!='".$postingPricingDealTO->pduid."'";

			    $this->dbConn->dbinsQuery($sql);
				if ($this->dbConn->dbQueryResultRows > 0) {
					$this->errorTO->type=FLAG_ERRORTO_ERROR;
					if (($postingPricingDealTO->priceTypeUId==PRT_PRODUCT) && (isset($mfP[$postingPricingDealTO->principalProdUid]))) $product=$mfP[$postingPricingDealTO->principalProdUid]["product_code"];
					else if (($postingPricingDealTO->priceTypeUId==PRT_PRODUCT_GROUP) && (isset($mfP[$postingPricingDealTO->principalProdUid]))) $product=$mfP[$postingPricingDealTO->principalProdUid]["description"];
					else $product=$postingPricingDealTO->principalProdUid;
					$this->errorTO->description="A product (".$product.") for this date range (".$postingPricingDealTO->startDate." to ".$postingPricingDealTO->endDate.") and customer type already exists.";
					return false;
				}
			}
		}
		// end duplicate check

		return true;

    }


    // NB !!!!
    // THERE IS AN AUDITOR . TRIGGER on this table.
    // If an SQL Error Occurs, the error may be in the trigger !!!!

   public function postPricing($postingPricingDealTO) {
   		if (!isset($_SESSION['user_id'])) session_start();
   		$userId = $_SESSION['user_id'];

    	$resultOK = $this->postPricingValidation($postingPricingDealTO);
    	if ($resultOK) {
    		 if ($postingPricingDealTO->DMLType=="INSERT") {
    			$sql="INSERT INTO `pricing` ( `customer_type_uid`, `chain_store`,`price_type_uid`, `principal_product_uid`, `principal_uid`, `list_price`, ".
	              	         		"`deal_type_uid`, `discount_value`, `start_date`,`end_date`, `status_uid`, `excl_incl`, `user_uid`, `capture_date`, `active` ,`guid`,
	              	         	  reference, last_change_by_userid)
	              	         	  VALUES ( " .
		       		 		 		"'" . $postingPricingDealTO->customerTypeUid        . "', " .
		       		 		 		"'" . $postingPricingDealTO->chainOrStoreUid        . "', ".
		       		 		 		"'" . $postingPricingDealTO->priceTypeUId          . "', " .
		            				"'" . $postingPricingDealTO->principalProdUid       . "', ".
		            				"'" . $postingPricingDealTO->principalUid           . "', ".
		            		 		"'" . $postingPricingDealTO->listPrice              . "', ".
		            		 		"'" . $postingPricingDealTO->dealTypeID             . "', ".
		            				"'" . $postingPricingDealTO->discountValue          . "', ".
		             	 	 		"'" . $postingPricingDealTO->startDate              . "', ".
		            		 		"'" . $postingPricingDealTO->endDate                . "', ".
		            				"1,".
		             	 	 		"'" . $postingPricingDealTO->exclInclFlag           . "', ".
		            		 		"'" . $postingPricingDealTO->user_uid               . "', ".
		            				"'" . gmdate(GUI_PHP_DATE_FORMAT) . "', ".
		             	 	 		"'" . $postingPricingDealTO->activated              . "', ".
		             	 	 		"'" . $postingPricingDealTO->guid                   . "',".
		             	 	 		"'" . $postingPricingDealTO->reference              . "'," .
		             	 	 		"'{$userId}')";
    		  } else if ($postingPricingDealTO->DMLType=="UPDATE") {
    			$sql="UPDATE pricing ";
    			if ($postingPricingDealTO->deleted==1) {
    				$sql .= "SET end_date='".$postingPricingDealTO->endDate."', " .
    						"deleted=".$postingPricingDealTO->deleted.", " .
    						"deleted_date=now(), " .
    						"last_change_by_userid=".$userId." ";
    			} else {
    				$sql .= "SET end_date='".$postingPricingDealTO->endDate."', " .
    						"    last_change_by_userid=".$userId." ";
    			  }
    			$sql.="WHERE uid=".$postingPricingDealTO->pduid;
    		  }

    		  $this->errorTO = $this->dbConn->processPosting($sql,$postingPricingDealTO->principalProdUid);

			  if ($this->errorTO->type==FLAG_ERRORTO_SUCCESS) {
			  	if ($postingPricingDealTO->DMLType=="INSERT")	{
			  		$this->errorTO->identifier=$this->dbConn->dbGetLastInsertId(); // get the UID just created
			  		$this->errorTO->description="Pricing Successfully Inserted.";
			  	} else $this->errorTO->description="Pricing Successfully Updated.";
			  	return $this->errorTO;
			  }

    	} else {
    		return $this->errorTO;
    	  }

    	return $this->errorTO;
   }


   public function postPricingBulk($postingPricingDealTO) {
   		if (!isset($_SESSION['user_id'])) session_start();
   		$userId = $_SESSION['user_id'];

		$postingPricingDealTO->discountValue=(empty($postingPricingDealTO->discountValue)?"0":$postingPricingDealTO->discountValue);

    	$resultOK = $this->postPricingValidation($postingPricingDealTO);
    	if ($resultOK) {
    			$join=($postingPricingDealTO->priceTypeUId==PRT_PRODUCT)?" principal_product a, ":" principal_product_category a, ";
    			$join.=($postingPricingDealTO->customerTypeUid==CT_CHAIN)?" principal_chain_master b ":" principal_store_master b ";

    			$sql="INSERT INTO `pricing` ( `customer_type_uid`, `chain_store`,`price_type_uid`, `principal_product_uid`, `principal_uid`, `list_price`, ".
	              	         		"`deal_type_uid`, `discount_value`, `start_date`,`end_date`, `status_uid`, `excl_incl`, `user_uid`, `capture_date`, `active` ,`guid`,
	              	         	  reference, last_change_by_userid)
	              	   SELECT   '{$postingPricingDealTO->customerTypeUid}',
							    b.uid,
								'{$postingPricingDealTO->priceTypeUId}',
								a.uid,
								a.principal_uid,
								'{$postingPricingDealTO->listPrice}',
								'{$postingPricingDealTO->dealTypeID}',
								'{$postingPricingDealTO->discountValue}',
								'{$postingPricingDealTO->startDate}',
								'{$postingPricingDealTO->endDate}',
								'1',
								'E',
								'{$postingPricingDealTO->user_uid}',
								'".gmdate(GUI_PHP_DATE_FORMAT)."}',
								'{$postingPricingDealTO->activated}',
								'',
								'{$postingPricingDealTO->reference}',
								'{$userId}'
					   FROM    {$join}
					   WHERE   a.principal_uid = {$postingPricingDealTO->principalUid}
					   AND     b.principal_uid = {$postingPricingDealTO->principalUid}
					   AND     a.uid in ({$postingPricingDealTO->principalProdUid})
					   AND     b.uid in ({$postingPricingDealTO->chainOrStoreUid})";

    		  $this->errorTO = $this->dbConn->processPosting($sql,$postingPricingDealTO->principalProdUid);

			  if ($this->errorTO->type==FLAG_ERRORTO_SUCCESS) {
			  	if ($postingPricingDealTO->DMLType=="INSERT")	{
			  		$this->errorTO->identifier=$this->dbConn->dbGetLastInsertId(); // get the UID just created
			  		$this->errorTO->description="Bulk Pricing Successfully Inserted.";
			  	} else $this->errorTO->description="Bulk Pricing Successfully Updated.";
			  	return $this->errorTO;
			  }

    	} else {
    		return $this->errorTO;
    	  }

    	return $this->errorTO;
   }


   public function postDocumentPricingValidation($postingDocumentPricingTO) {
    	global $ROOT; global $PHPFOLDER;
    	include_once($ROOT.$PHPFOLDER."DAO/AdministrationDAO.php");
    	include_once($ROOT.$PHPFOLDER."DAO/ProductDAO.php");
    	include_once($ROOT.$PHPFOLDER."DAO/StoreDAO.php");
    	$administrationDAO = new AdministrationDAO($this->dbConn);
    	$storeDAO = new StoreDAO($this->dbConn);
    	$productDAO = new ProductDAO($this->dbConn);

    	// get user. don't pass it because this is more secure.
    	if (!isset($_SESSION)) session_start();
    	$userId = $_SESSION['user_id'];
    	$principalId = $_SESSION['principal_id'];

    	if (!ValidationCommonUtils::checkPostingType($postingDocumentPricingTO->DMLType)) return false;

		if ($postingDocumentPricingTO->principalUId!=$principalId) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Invalid Principal";
			return false;
		}

		// there is no delete, to delete, update status to deleted
		if ($postingDocumentPricingTO->DMLType=="INSERT") {
			$hasRole=$administrationDAO->hasRole($userId,$principalId,ROLE_ADD_PRICE);
		} else {
			$hasRole=$administrationDAO->hasRole($userId,$principalId,ROLE_MODIFY_PRICE);
		}
		if ($hasRole!==true) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="You do not have permissions to {$postingDocumentPricingTO->type} Document Pricing.";
			return false;
		}

		if (($postingDocumentPricingTO->DMLType=="INSERT") && ($postingDocumentPricingTO->uid!="")) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="non-blank UID passed for INSERT";
			return false;
		} else if (($postingDocumentPricingTO->DMLType=="UPDATE") && ($postingDocumentPricingTO->uid=="")) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Blank UID passed for UPDATE";
			return false;
		}

		if (strlen(trim($postingDocumentPricingTO->description))==0) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Description is required.";
			return false;
		}

		if (intval($postingDocumentPricingTO->grouping)<1) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Grouping not entered.";
			return false;
		}

		// check applyLevel
		if (($postingDocumentPricingTO->applyLevel!=DPL_DOCUMENT) && ($postingDocumentPricingTO->applyLevel!=DPL_ITEM) && ($postingDocumentPricingTO->applyLevel!=DPL_DOCUMENT_ITEM)) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Invalid Apply-Level.";
			return false;
		}


		// START: CHECK DATES
		if (preg_match(GUI_PHP_DATE_VALIDATION,$postingDocumentPricingTO->startDate,$parts)) {
			if(!checkdate($parts[2],$parts[3],$parts[1])) {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="Invalid Start Date format.";
				return false;
			}
		} else {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Invalid Start Date format.";
			return false;
		  }
		if (preg_match(GUI_PHP_DATE_VALIDATION,$postingDocumentPricingTO->endDate,$parts)) {
			if(!checkdate($parts[2],$parts[3],$parts[1])) {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="Invalid End Date format.";
				return false;
			}
		} else {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Invalid End Date format.";
			return false;
		  }
		// check start date not after end date
		if(strtotime($postingDocumentPricingTO->startDate)>strtotime($postingDocumentPricingTO->endDate)) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Start Date cannot be after End Date.";
			return false;
		}
		// END: CHECK DATES


		if ($postingDocumentPricingTO->customerTypeUId==CT_CHAIN) {
			$mfC=$storeDAO->getUserPrincipalChainItem($userId, $postingDocumentPricingTO->storeChainUId);
			if (sizeof($mfC)==0) {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="User does not have permissions for this Chain, or Chain does not exist.";
				return false;
			}
		} else if ($postingDocumentPricingTO->customerTypeUId==CT_STORE) {
			$mfS=$storeDAO->getUserPrincipalStoreItem($userId, $postingDocumentPricingTO->storeChainUId);
			if (sizeof($mfS)==0) {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="User does not have permissions for this Store, or Store does not exist.";
				return false;
			}
		} else {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Invalid Customer Type (Chain or Store)";
			return false;
		}

		$mfUPT=$productDAO->getUnitPriceTypeItem($postingDocumentPricingTO->unitPriceTypeUId);
		if (sizeof($mfUPT)==0) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Invalid Unit Price Type.";
			return false;
		}

		if (!preg_match(GUI_PHP_FLOAT_REGEX,$postingDocumentPricingTO->quantity)) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Invalid Quantity. Positive Number expected.";
			return false;
		}

		$mfDT=$productDAO->getDealTypeItem($postingDocumentPricingTO->dealTypeUId);
		if (sizeof($mfDT)==0) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Invalid Deal Type.";
			return false;
		}

		// a leading + sign needs to be trimmed
		if (!preg_match(GUI_PHP_SIGNED_FLOAT_REGEX,trim($postingDocumentPricingTO->value))) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Invalid Value Adjustment entered. Expecting a number.";
			return false;
		}
		/*
		if (($postingDocumentPricingTO->applyLevel==DPL_ITEM) && (floatval($postingDocumentPricingTO->value)>0)) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Value Adjustment MUST be negative (item level only has discounts, not charges) if apply-level is ITEM level";
			return false;
		}
		*/
		if (!ValidationCommonUtils::checkFieldYesNoSimple($postingDocumentPricingTO->applyPerUnit)) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Invalid Apply-Per-Unit flag";
			return false;
		}
		if (($postingDocumentPricingTO->applyPerUnit=="Y") && ($postingDocumentPricingTO->unitPriceTypeUId!=UPT_CASES)) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Apply-per-Unit is only allowed if the unit type CASES is chosen.";
			return false;
		}
		if (($postingDocumentPricingTO->applyPerUnit=="Y") &&
			(($postingDocumentPricingTO->applyLevel!=DPL_ITEM) && ($postingDocumentPricingTO->applyLevel!=DPL_DOCUMENT_ITEM))
			) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Apply-per-Unit is only allowed at Item Level.";
			return false;
		}

		if (!in_array($postingDocumentPricingTO->productType,array('','P','PC'))) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Invalid Product Type.";
			return false;
		}

		// check products
		if (($postingDocumentPricingTO->productType=="") && (sizeof($postingDocumentPricingTO->productArr)>0)) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Product Type is set to ALL, but specific products/categories were selected";
			return false;
		}
		if ((in_array($postingDocumentPricingTO->productType,array('P','PC'))) && (sizeof($postingDocumentPricingTO->productArr)==0)) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Product Type is set to Products/Categories, but no specific products/categories were selected";
			return false;
		}
		// Check each product / category
		// 1. Get the List of allowed vals
		if ($postingDocumentPricingTO->productType=="P") {
			$mfP = $productDAO->getPrincipalProductsArray($principalId, "uid");
		} else if ($postingDocumentPricingTO->productType=="PC") {
			$mfP = $productDAO->getPrincipalProductCategoryArray($principalId, FLAG_STATUS_ACTIVE, $arrayIndex="uid", $pcUIDList=implode(",",$postingDocumentPricingTO->productArr));
		}
		// 2. Validate Against this list
		foreach ($postingDocumentPricingTO->productArr as $p) {
			if (!isset($mfP[$p])) {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="Invalid Product value was specified - {$p}";
				return false;
			};
		}
		
		if (!ValidationCommonUtils::checkStatus($postingDocumentPricingTO->status)) {
			$this->errorTO->type=FLAG_ERRORTO_ERROR;
			$this->errorTO->description="Invalid StatusZZZaaaaaaaaaaZZ.   " . $postingDocumentPricingTO->status;
			return false;
		}

    if ($postingDocumentPricingTO->DMLType=="UPDATE") {
       $mfDP=$productDAO->getUserDocumentPriceItem($postingDocumentPricingTO->uid, $principalId, $userId);
       if (sizeof($mfDP)==0) {
           $this->errorTO->type=FLAG_ERRORTO_ERROR;
           $this->errorTO->description="Document Pricing Item not found for Update.";
          return false;
       }
       if (($mfDP[0]["status"]!=$postingDocumentPricingTO->status) && ($postingDocumentPricingTO->status==FLAG_STATUS_DELETED)) {
          $hasRole=$administrationDAO->hasRole($userId,$principalId,ROLE_DELETE_PRICE);
          if ($hasRole!==true) {
              $this->errorTO->type=FLAG_ERRORTO_ERROR;
              $this->errorTO->description="You do not have permissions to DELETE Document Pricing.";
              return false;
          }
       }
		} else {
			/* deprecated due to product_type enhancement
			// check nothing too similar
			$sql="select uid
					from pricing_document
					where 	 principal_uid = {$principalId}
					and      customer_type_uid = {$postingDocumentPricingTO->customerTypeUId}
					and      store_chain_uid = {$postingDocumentPricingTO->storeChainUId}
					and      grouping = {$postingDocumentPricingTO->grouping}
					and      unit_price_type_uid = {$postingDocumentPricingTO->unitPriceTypeUId}
					and      quantity = '{$postingDocumentPricingTO->quantity}'
					and      product_type = '{$postingDocumentPricingTO->productType}'";

		    $this->dbConn->dbinsQuery($sql);
			if ($this->dbConn->dbQueryResultRows > 0) {
				$this->errorTO->type=FLAG_ERRORTO_ERROR;
				$this->errorTO->description="A Document Price already exists that is too similar to this deal.";
				return false;
			}
			*/
		}

		return true;

    }

	// WARNING : These use a sql user VAR @userId
   public function postDocumentPricing($postingDocumentPricingTO) {
   		if (!isset($_SESSION['user_id'])) session_start();
   		$userId = $_SESSION['user_id'];
   		$this->dbConn->dbQuery("SET @userId={$userId}");
    	$resultOK = $this->postDocumentPricingValidation($postingDocumentPricingTO);   	
    	
    	if ($resultOK) {

    		 // HEADER
    		 if ($postingDocumentPricingTO->DMLType=="INSERT") {
    			$sql="INSERT INTO pricing_document (principal_uid, grouping, description, customer_type_uid, store_chain_uid, unit_price_type_uid,
													quantity, deal_type_uid, value, status, last_change_by_userid, deleted_date, apply_level,
													start_date, end_date, apply_per_unit, cumulative_type, product_type)
					  VALUES (
							{$postingDocumentPricingTO->principalUId},
							{$postingDocumentPricingTO->grouping},
							'".addSlashes($postingDocumentPricingTO->description)."',
							{$postingDocumentPricingTO->customerTypeUId},
							{$postingDocumentPricingTO->storeChainUId},
							{$postingDocumentPricingTO->unitPriceTypeUId},
							{$postingDocumentPricingTO->quantity},
							{$postingDocumentPricingTO->dealTypeUId},
							{$postingDocumentPricingTO->value},
							'{$postingDocumentPricingTO->status}',
							{$userId},
							".(($postingDocumentPricingTO->status==FLAG_STATUS_DELETED)?"now()":"null").",
							'{$postingDocumentPricingTO->applyLevel}',
							'{$postingDocumentPricingTO->startDate}',
							'{$postingDocumentPricingTO->endDate}',
							'{$postingDocumentPricingTO->applyPerUnit}',
							'{$postingDocumentPricingTO->cumulativeType}',
							'{$postingDocumentPricingTO->productType}'
					  )";
    		  } else if ($postingDocumentPricingTO->DMLType=="UPDATE") {
    				$sql="UPDATE pricing_document
						  SET 	 description='{$postingDocumentPricingTO->description}',
								 quantity={$postingDocumentPricingTO->quantity},
								 deal_type_uid={$postingDocumentPricingTO->dealTypeUId},
								 value={$postingDocumentPricingTO->value},
								 status='{$postingDocumentPricingTO->status}',
								 last_change_by_userid='{$userId}',
								 deleted_date=".(($postingDocumentPricingTO->status==FLAG_STATUS_DELETED)?"now()":"null").",
								 apply_level='{$postingDocumentPricingTO->applyLevel}',
								 start_date='{$postingDocumentPricingTO->startDate}',
								 end_date='{$postingDocumentPricingTO->endDate}',
								 apply_per_unit='{$postingDocumentPricingTO->applyPerUnit}',
								 cumulative_type='{$postingDocumentPricingTO->cumulativeType}',
								 product_type='{$postingDocumentPricingTO->productType}'
						  WHERE  uid = '{$postingDocumentPricingTO->uid}'";
    		  }

          // echo $sql;
          // echo "<br>";
          // file_put_contents('C:/inetpub/wwwroot/systems/kwelanga_system/kwelanga_php/log/price.txt', $sql, FILE_APPEND);
    		  $this->errorTO = $this->dbConn->processPosting($sql,$postingDocumentPricingTO->uid);

			  if ($this->errorTO->type==FLAG_ERRORTO_SUCCESS) {
			  	if ($postingDocumentPricingTO->DMLType=="INSERT") {
			  		$this->errorTO->identifier=$this->dbConn->dbGetLastInsertId();
			  	}
			  	$this->errorTO->description="Document Pricing Successfully Saved.";
			  } else {
			  	$this->errorTO->description="Failed to save Document Pricing.";
			  	return $this->errorTO;
			  }

			  // PRODUCTS
			  if (sizeof($postingDocumentPricingTO->productArr)>0) {
	    		 if ($postingDocumentPricingTO->DMLType=="INSERT") {
	    			$sql="INSERT INTO pricing_document_product (pricing_document_uid, product_entity_uid) values ";
	    			$i=0;
	    			foreach ($postingDocumentPricingTO->productArr as $p) {
	    				$sql.=(($i>0)?",":"")."({$this->errorTO->identifier},$p)";
	    				$i++;
	    			}
	    		  } else if ($postingDocumentPricingTO->DMLType=="UPDATE") {
	    		  		// it is done this way because to cut down on the auditor table triggering,
	    		  		// and because we dont have additional field values to update as there is really only a product_entity_uid
	    		  		// No need to check against product_type if switching from P to PC and vice versa, as if it is the same uid, then no problem
	    				$sql="DELETE FROM pricing_document_product
							  WHERE  pricing_document_uid = '{$postingDocumentPricingTO->uid}'
							  AND    product_entity_uid not in (".(implode(",",$postingDocumentPricingTO->productArr)).")";

	    				$this->errorTO = $this->dbConn->processPosting($sql,$postingDocumentPricingTO->uid);

						if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {
						  	$this->errorTO->description="Failed to delete from pricing_document_product.";
						  	return $this->errorTO;
						}

	    				$sql="INSERT INTO pricing_document_product (pricing_document_uid, product_entity_uid)
							  SELECT {$postingDocumentPricingTO->uid}, uid ".
							  (($postingDocumentPricingTO->productType=="P")?" FROM principal_product a ":" FROM principal_product_category a ")."
							  WHERE  principal_uid = '{$postingDocumentPricingTO->principalUId}'
							  AND    uid in (".(implode(",",$postingDocumentPricingTO->productArr)).")
							  AND    not exists (select 1 from pricing_document_product b where b.product_entity_uid = a.uid and b.pricing_document_uid = '{$postingDocumentPricingTO->uid}')";
	    		  }
			  } else {
			  	$sql="DELETE FROM pricing_document_product
					  WHERE  pricing_document_uid = '{$postingDocumentPricingTO->uid}'";
			  }

    		  $this->errorTO = $this->dbConn->processPosting($sql,$postingDocumentPricingTO->uid);

			  if ($this->errorTO->type==FLAG_ERRORTO_SUCCESS) {
			  	if ($postingDocumentPricingTO->DMLType=="INSERT") {
			  		$this->errorTO->identifier=$this->dbConn->dbGetLastInsertId();
			  	}
			  	$this->errorTO->description="Document Pricing Product Successfully Saved.";
			  } else {
			  	$this->errorTO->description="Failed to save Document Pricing Product.";
			  	return $this->errorTO;
			  }

    	} else {
    		
             // file_put_contents('C:/inetpub/wwwroot/systems/kwelanga_system/kwelanga_php/log/price.txt', print_r($this->errorTO, TRUE), FILE_APPEND);    		

          		return $this->errorTO;
      }

    	return $this->errorTO;
   }


   public function purgePricing($period) {
		$sql="DELETE from pricing " .
			  "WHERE deleted=1 " .
			  "AND   end_date < DATE_SUB(CURDATE(),INTERVAL ".$period." DAY)";

  		$this->errorTO = $this->dbConn->processPosting($sql,$period);

		if ($this->errorTO->type==FLAG_ERRORTO_SUCCESS) {
			$this->errorTO->description="Purging Successful. Rows Purged:".mysql_affected_rows();
		 	return $this->errorTO;
		}

    	return $this->errorTO;
   }

    /*
    *
    * PRODUCT CATEGORIES
    *
    */
  public function postProductCategoryValidation($postingProductCategoryTO) {

    $userId = $_SESSION['user_id'];
    $principalId = $_SESSION['principal_id']; // used for hasRole validation

    if ($postingProductCategoryTO->principalUId != $principalId) {
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description = 'Principal Code differs from Session';
      return false;
    }

    //Second level of Role Check
    $adminDAO = new AdministrationDAO($this->dbConn);

    if ($postingProductCategoryTO->DMLType == 'INSERT' && (! $adminDAO->hasRole($userId, $principalId, ROLE_ADD_PRODUCT))) {
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description = "You do not have permissions to Add New Product Categories!";
      return false;
    } elseif ($postingProductCategoryTO->DMLType == 'UPDATE' && (! $adminDAO->hasRole($userId, $principalId, ROLE_MODIFY_PRODUCT))) {
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description = "You do not have permissions to Change Product Categories!";
      return false;
    }

    //Start Validation : From Top > Bottom
    if (! ValidationCommonUtils::checkPostingType($postingProductCategoryTO->DMLType)) return false;

    if (trim($postingProductCategoryTO->description) == '') {
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description = 'Please enter a Category Name!';
      return false;
    }

     if ($postingProductCategoryTO->status != FLAG_STATUS_ACTIVE && $postingProductCategoryTO->status != FLAG_STATUS_DELETED) {
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description = 'Please select a Product Category Status';
      return false;
    }

    if($postingProductCategoryTO->DMLType == 'UPDATE' && !is_numeric($postingProductCategoryTO->uid)){
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description = 'Error while updating Product Category UID:'.$postingProductCategoryTO->uid;
      return false;
    }

    return true;
  }

   public function postProductCategory($postingProductCategoryTO) {

    $userId = $_SESSION['user_id'];
    $principalId = $_SESSION['principal_id'];

     //Validation check
     if ($this->postProductCategoryValidation($postingProductCategoryTO)) {

        $this->dbConn->dbQuery('SET time_zone="+0:00"');

     	//Accept only INSERT | UPDATE.
    	if ($postingProductCategoryTO->DMLType=='INSERT') {

    	  $sql = 'INSERT INTO `principal_product_category` (
    	  	`principal_uid`,
    	    `description`,
    	  	`status`
    	  ) VALUES (
    	  	"'.mysqli_real_escape_string($this->dbConn->connection, trim($postingProductCategoryTO->principalUId)).'",
    	  	"'.mysqli_real_escape_string($this->dbConn->connection, trim($postingProductCategoryTO->description)).'",
    	  	"'.$postingProductCategoryTO->status.'"
    	  )';

          $this->errorTO = $this->dbConn->processPosting($sql,'');
          $this->errorTO->identifier = $this->dbConn->dbGetLastInsertId();

	    } elseif ($postingProductCategoryTO->DMLType == 'UPDATE') {

	        $sql= "UPDATE `principal_product_category`
	        	SET
	       			`description` = '".mysqli_real_escape_string($this->dbConn->connection, trim($postingProductCategoryTO->description))."',
    	  			`status` = '".$postingProductCategoryTO->status."'
    	  		WHERE
    	  			`uid` = '{$postingProductCategoryTO->uid}'
    	  		AND  principal_uid = '{$postingProductCategoryTO->principalUId}'";

	    	$this->errorTO = $this->dbConn->processPosting($sql,'');

        } else  {
	      return $this->errorTO;
	    }
     } else {
       return $this->errorTO;
     }

     return $this->errorTO;

   }


   public function postProductMinorCategoryValidation($TO) {

     global $ROOT, $PHPFOLDER;

    $userId = $_SESSION['user_id'];
    $principalId = $_SESSION['principal_id']; // used for hasRole validation
    $systemId = $_SESSION["system_id"];

    if(!in_array($TO->DMLType, array('INSERT', 'UPDATE', 'DELETE'))){
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description = 'Error: Invalid DML type!';
      return false;
    }

    if(($TO->DMLType == 'INSERT' || $TO->DMLType == 'UPDATE') && trim($TO->value) == '') {
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description = 'Please enter a Description!';
      return false;
    }

    if(($TO->DMLType == 'UPDATE' || $TO->DMLType == 'DELETE') && empty($principalId)){
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description = 'Error: Invalid Principal Uid Supplied!';
      return false;
    }

    if(($TO->DMLType == 'UPDATE' || $TO->DMLType == 'DELETE') && empty($TO->UId)){
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description = 'Error: Invalid Principal Uid Supplied!';
      return false;
    }

    if($TO->DMLType == 'INSERT' && empty($TO->minorCategoryTypeUid)){
      $this->errorTO->type = FLAG_ERRORTO_ERROR;
      $this->errorTO->description = 'Error: Invalid Type UId Supplied!';
      return false;
    }

    return true;
  }

   public function postProductMinorCategory($postingProductMinorCategoryTO) {

    $userId = $_SESSION['user_id'];
    $principalId = $_SESSION['principal_id'];

     //Validation check
     if ($this->postProductMinorCategoryValidation($postingProductMinorCategoryTO)) {

    	if ($postingProductMinorCategoryTO->DMLType=='INSERT') {

    	  $sql = "INSERT INTO `product_minor_category` (
                              `minor_category_type_uid`,
                              `principal_uid`,
                              `value`,
                              `status`
                        ) VALUES (
                              ".mysqli_real_escape_string($this->dbConn->connection, trim($postingProductMinorCategoryTO->minorCategoryTypeUid)).",
                              ".mysqli_real_escape_string($this->dbConn->connection, trim($postingProductMinorCategoryTO->principalUId)).",
                              '".mysqli_real_escape_string($this->dbConn->connection, trim($postingProductMinorCategoryTO->value))."',
                              '".FLAG_STATUS_ACTIVE."'
                        )";

	    } elseif ($postingProductMinorCategoryTO->DMLType == 'UPDATE') {

	        $sql= "UPDATE `product_minor_category`
                        SET
                               `value` = '".mysqli_real_escape_string($this->dbConn->connection, trim($postingProductMinorCategoryTO->value))."'
                        WHERE	`uid` = '{$postingProductMinorCategoryTO->UId}'
                          and principal_uid = '{$postingProductMinorCategoryTO->principalUId}'";

          } elseif ($postingProductMinorCategoryTO->DMLType == 'DELETE') {

                $sql= "UPDATE `product_minor_category`
                        SET
                               `status` = '{$postingProductMinorCategoryTO->status}'
                        WHERE	`uid` = '{$postingProductMinorCategoryTO->UId}'
                          and principal_uid = '{$postingProductMinorCategoryTO->principalUId}'";

      } else  {
	      return $this->errorTO;
      }

      $this->errorTO = $this->dbConn->processPosting($sql,'');
      if ($postingProductMinorCategoryTO->DMLType=='INSERT') {
        $postingProductMinorCategoryTO->UId = $this->errorTO->identifier = $this->dbConn->dbGetLastInsertId();
      }

      if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {
        $this->errorTO->description="Error occured inserting/updating product_minor_group";
        return $this->errorTO;
      } else {
        if($postingProductMinorCategoryTO->DMLType == 'INSERT'){
          $this->errorTO->description="Successfully created item!";
        } else {
          $this->errorTO->description="Successfully updated item!";
        }
      }

     } else {
       return $this->errorTO;
     }

     return $this->errorTO;

   }

   public function createPrincipalNonMFProduct($principalUId) {

    global $ROOT,$PHPFOLDER;
    include_once($ROOT.$PHPFOLDER."TO/PostingProductTO.php");
    $errorTO = new ErrorTO;

    $postingProductTO = new PostingProductTO;
    $postingProductTO->DMLType = "INSERT";
    $postingProductTO->principal = $principalUId;
    $postingProductTO->productCode = VAL_PRODUCTCODE_NOT_ON_MF;
    $postingProductTO->productDescription = "PRODUCT NOT ON MASTERFILES";
    $postingProductTO->weight = "0.0";
    $postingProductTO->majorCategory = "0";
    $postingProductTO->minorCategory = "0";
    $postingProductTO->productVATRate = VAL_VAT_RATE_TBLSTD; // non-zero so as to exclude authorisation message from showing
    $postingProductTO->status = FLAG_STATUS_ACTIVE;
    $postingProductTO->enforcePalletConsignment = "";
    $postingProductTO->unitsPerPallet = "";
    $postingProductTO->altCode = "";
    $postingProductTO->itemsPerCase = "1";
    $postingProductTO->unitValue = "0.00";


    $rTO = $this->postProduct($postingProductTO, $userId="0");
    if ($rTO->type!=FLAG_ERRORTO_SUCCESS) {
      $errorTO->type = FLAG_ERRORTO_ERROR;
      $errorTO->description = "Error creating Non MF Principal-Product: ".$rTO->description;
      return $errorTO;
    }

    $errorTO->type = FLAG_ERRORTO_SUCCESS;
    $errorTO->description = "Successfully Created Principal-Product";
    $errorTO->identifier = $rTO->identifier;

    return $errorTO;

  }

  public function createPrincipalConsolidatedProduct($principalUId) {

    global $ROOT,$PHPFOLDER;
    include_once($ROOT.$PHPFOLDER."TO/PostingProductTO.php");
    $errorTO = new ErrorTO;

    $postingProductTO = new PostingProductTO;
    $postingProductTO->DMLType = "INSERT";
    $postingProductTO->principal = $principalUId;
    $postingProductTO->productCode = VAL_PRODUCTCODE_CONSOLIDATED;
    $postingProductTO->productDescription = "CONSOLIDATED PRODUCT";
    $postingProductTO->weight = "0.0";
    $postingProductTO->majorCategory = "0";
    $postingProductTO->minorCategory = "0";
    $postingProductTO->productVATRate = VAL_VAT_RATE_TBLSTD; // non-zero so as to exclude authorisation message from showing
    $postingProductTO->status = FLAG_STATUS_ACTIVE;
    $postingProductTO->enforcePalletConsignment = "";
    $postingProductTO->unitsPerPallet = "";
    $postingProductTO->altCode = "";
    $postingProductTO->itemsPerCase = "1";
    $postingProductTO->unitValue = "0.00";


    $rTO = $this->postProduct($postingProductTO, $userId="0");
    if ($rTO->type!=FLAG_ERRORTO_SUCCESS) {
      $errorTO->type = FLAG_ERRORTO_ERROR;
      $errorTO->description = "Error creating Consolidated Principal-Product: ".$rTO->description;
      return $errorTO;
    }

    $errorTO->type = FLAG_ERRORTO_SUCCESS;
    $errorTO->description = "Successfully Created Principal-Product";
    $errorTO->identifier = $rTO->identifier;

    return $errorTO;

  }

}

?>