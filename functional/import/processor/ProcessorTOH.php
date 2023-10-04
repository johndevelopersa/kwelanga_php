<?php
/*
 * PROCESSES ORDERS INTO ORDERS_HOLDING
 *
  */

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'libs/BroadcastingUtils.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER.'TO/PostingOrdersHoldingTO.php');
include_once($ROOT.$PHPFOLDER.'DAO/PostImportDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/PrincipalDAO.php');
include_once($ROOT.$PHPFOLDER.'DAO/BIDAO.php');
include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');
include_once($ROOT.$PHPFOLDER.'DAO/ProductDAO.php');

// all files should only be committed if entire file is OK
class ProcessorTOH {
	private $dbConn;
	private $postImportDAO;
  private $productDAO;
	private $BIDAO;
	private $EDIFileDEFUIds; // array of online_file_processing UIDs by principal (unique across principals)
  private $principalDAO;
  private $pDT_ProformaPricing = array();

	function __construct($dbConn) {
	      $this->dbConn = $dbConn;
	      $this->postImportDAO = new PostImportDAO($dbConn);
        $this->productDAO = new ProductDAO($dbConn);
        $this->principalDAO = new PrincipalDAO($this->dbConn);

	      // preload principals who have the trigger loaded - at the moment there is only 1 parameter : the onlineFileProcessing UID which is unique across principals
	      $this->BIDAO = new BIDAO($dbConn);
	      $rs =  $this->BIDAO->getAllEDIFileDefNotificationOFD();
	      foreach ($rs as $r) {
	      	$uid=CommonUtils::getParamValuesFromString($r["additional_parameter_string"],"p1",$paramSeparator="&",$paramValueAsignment="=");
	      	if ($uid!="") {
	      		$this->EDIFileDEFUIds[$r["principal_uid"]][]=$uid;
	      	}
	      }

        $mfPDocType = $this->principalDAO->getAllPrincipalDocumentTypes_ProformaPricing(); // document types overrides if any
        foreach ($mfPDocType as $r) {
             $this->pDT_ProformaPricing[$r["principal_uid"]][]=$r["document_type_uid"];
        }
    }

    // this can be an array or single TO passed eg. PnP WS, or File Import
    // the PnP does not pass the $onlineImportLocation
    function postTOH($postingOrdersHoldingTOArr, $onlineImportLocation=false) {
    	global $importDAO, $ROOT, $PHPFOLDER;

    	if (!isset($importDAO)) {
    		include_once($ROOT.$PHPFOLDER."DAO/ImportDAO.php");
    		$importDAO = new ImportDAO($this->dbConn);
    	}

    	$eTO = new ErrorTO;

		// make it an array if it isnt already
		if (!is_array($postingOrdersHoldingTOArr)) $arrTO=array($postingOrdersHoldingTOArr);
		else $arrTO=$postingOrdersHoldingTOArr;

		foreach ($arrTO as $postingOrdersHoldingTO) {

			// assign the edi file level preferences
			// for the time being, WS uses the high-level default
			if (($postingOrdersHoldingTO->dataSource!=DS_WS) && ($onlineImportLocation!==false)) {
				$onlineFileProcessingMapping=$importDAO->getMappingFromOIMByPrincipalUId($onlineImportLocation["onlineFileProcessingMapping"], $postingOrdersHoldingTO->principalUid);
    			if (empty($onlineFileProcessingMapping)) {
    				$eTO->type = FLAG_ERRORTO_ERROR;
			   		$eTO->description = "Unknown Principal Type passed to postTOH in ProcessorTOH";
			    	return $eTO;
    			}
				$postingOrdersHoldingTO->checkPriceVariance=$onlineFileProcessingMapping["check_price_variance"];
				$postingOrdersHoldingTO->pricingConflictAction=$onlineFileProcessingMapping["pricing_conflict_action"];
			}

			// set the fileLog universally
      if ($onlineImportLocation===false) {
          $postingOrdersHoldingTO->fileLogUId = 0;
      } else {
          $postingOrdersHoldingTO->fileLogUId = $onlineImportLocation["fileLogUId"];
      }

      /*
       * START : General non-conditional detail rows processing
       */

      foreach ($postingOrdersHoldingTO->detailArr as &$dtl) {
        // only do so if values have been supplied (even zero is a value that influences processing!)
        // this needs to be done so that Ullmanns pricing and our pricing agree (we can't have sep. pricing as the confirmation that comes back rounds it anyway in DM)
        if (strval($dtl->nettPrice)!="") {
          $dtl->nettPrice = round($dtl->nettPrice,2);
          $dtl->extPrice = round($dtl->quantity*$dtl->nettPrice,2);
          // some documents are inserted without vat (blank as opposed to zero) to force the sys to calc it using PSM and PP vat settings
          if ((floatval($dtl->vatAmount)>0) && (floatval($dtl->vatRate)==0)) {
            $eTO->type = FLAG_ERRORTO_ERROR;
            $eTO->description = "Vat settings not correct in ProcessorTOH for principal {$postingOrdersHoldingTO->principalUid} and document {$postingOrdersHoldingTO->clientDocumentNo}";
            return $eTO;
          }
          if (floatval($dtl->vatAmount)>0) $dtl->vatAmount = round($dtl->extPrice*$dtl->vatRate/100,2);
          $dtl->totalPrice = $dtl->extPrice + $dtl->vatAmount;
        }
      }
      unset($dtl);

      /*
       * END : General non-conditional detail rows processing
      */



    	// try lookup the principal uid if it hasn't been set
    	// CHECKERS already have the principalUId set directly, so will never enter here
			if ($postingOrdersHoldingTO->principalUid=="") {
				$mfP=array();
				if ($postingOrdersHoldingTO->principalGLN!="") {
				  $mfP = $this->principalDAO->getPrincipalVendorsByGln($postingOrdersHoldingTO->principalGLN, $postingOrdersHoldingTO->vendorUid); // can return multiple
				} else if ($postingOrdersHoldingTO->principalCode!="") {
					$mfP = $this->principalDAO->getPrincipalByCode($postingOrdersHoldingTO->principalCode);
				}

				if (sizeof($mfP)==0) {
					$eTO->type = FLAG_ERRORTO_ERROR;
					$eTO->description = "Principal UID could not be set. Invalid Principal / Seller GLN ({$postingOrdersHoldingTO->principalGLN})";
					return $eTO;
				}

				// Only GLN lookups cater for multiple rows
				if ((sizeof($mfP)>1) && (array_key_exists("vendor_account", $mfP[0]))) {

			    // choose the principal based upon vendor account number
			    $principalUId_arr=$vendorAccount=false;
			    foreach ($mfP as $pv) {

			      if ($pv["has_principal_vendor"]=="N") continue; // only consider those GLN's configured for transmission

			      // assign generic
			      if ($pv["vendor_account"]=="") {

			        // only record the generic if no specific has been assigned
			        if ($vendorAccount===false || strval($vendorAccount)=="") {
  			        $principalUId_arr[] = $pv["uid"]; // hold this as generic usage
  			        $vendorAccount = $pv["vendor_account"];
			        }

			      } else {

			        // assign specific to override any generic
			        if (in_array($postingOrdersHoldingTO->vendorReference,explode(",",$pv["vendor_account"]))) {
			          if (strval($vendorAccount)=="") {
			            $principalUId_arr = array(); // lose the generics
			          }
  			        $principalUId_arr[] = $pv["uid"];
  			        $vendorAccount = $pv["vendor_account"];
			        }

			      }
			    } // end for loop

			    if ($principalUId_arr===false) {
			      $eTO->type = FLAG_ERRORTO_ERROR;
			      $eTO->description = "Cannot allocate (specific) principal UID using GLN as no matches could be made on vendor account for ({$postingOrdersHoldingTO->documentNo}) and Principal / Seller GLN ({$postingOrdersHoldingTO->principalGLN}) and vendor account ({$postingOrdersHoldingTO->vendorReference})";
			      return $eTO;
			    }


			    // if multiple principals try to refine further by seeing which principal has the product
			    if (count($principalUId_arr)>1) {

			      // at the moment this only works if GTINs are used for the lookups
			      $gtinArr = array();
			      foreach ($postingOrdersHoldingTO->detailArr as $dtl) {
			        if (!empty($dtl->productGTIN)) $gtinArr[] = $dtl->productGTIN; // we assume the gtin is not blank, but will be caught in next step anyway !
			      }

			      // if some gtins are invalid, no problem, just use the ones we are given
			      $mfMP = $this->productDAO->getPrincipalProductByOCGTIN(implode(",",$principalUId_arr), $gtinArr);

			      $prArr = array();
			      foreach ($mfMP as $p) {
			        if ($p["status"]==FLAG_STATUS_ACTIVE) $prArr[] = $p["principal_uid"];
			      }
			      $prArr = array_unique($prArr);
			      if (count($prArr)>0) $principalUId_arr = $prArr; // if empty then make it default to first with processing error

			      // if more than one match, use the first principal, but force it to required approval-multiple principals
			      $postingOrdersHoldingTO->principalUid = $principalUId_arr[0];
			      if (count($principalUId_arr)>1) {
			        $postingOrdersHoldingTO->status = 'R.A.MP';
			      }

			    } else {
			      $postingOrdersHoldingTO->principalUid = $principalUId_arr[0];
			    }

				} else {
				  $postingOrdersHoldingTO->principalUid = $mfP[0]["uid"];
				}
			}

			// set the EDIFileDefNotified flag according to SMART trigger - files can contain many principals some of which may not be activated for confirmations
			if ((isset($this->EDIFileDEFUIds[$postingOrdersHoldingTO->principalUid])) &&
				(in_array($postingOrdersHoldingTO->onlineFileProcessingUId,$this->EDIFileDEFUIds[$postingOrdersHoldingTO->principalUid]))) {
				$postingOrdersHoldingTO->EDIFileDefNotified="Q";
			}

			// block file if missing detail lines

			if (($postingOrdersHoldingTO->skipDtlLineCountCheck!="Y") && (sizeof($postingOrdersHoldingTO->detailArr)==0)) {
				$eTO->type = FLAG_ERRORTO_ERROR;
		   		$eTO->description = "Orders without detail lines found in ProcessorTOH for principal {$postingOrdersHoldingTO->principalUid} document ({$postingOrdersHoldingTO->documentNo}) client doc ($postingOrdersHoldingTO->clientDocumentNo)";
		    	return $eTO;
			}


      // 1st level pricing (excl Bulk) is required at time of capture for some principals and doc types (uplifts) for approval information
      /// ... so we get just the active pricing (1st level only) here if the principal is configured to do so
      if ((isset($this->pDT_ProformaPricing[$postingOrdersHoldingTO->principalUid])) &&
          (in_array($postingOrdersHoldingTO->documentTypeUId, $this->pDT_ProformaPricing[$postingOrdersHoldingTO->principalUid]))) {

       foreach ($postingOrdersHoldingTO->detailArr as &$dtl) {

          // EDI files will probably send the product code thru and not ppUId, so in other words approval pricing will not be attached for EDI.
          // For EDI to work it will need 1. ProductUId, 2. PCA_USE_OWN, 3. empty listPrice ie. pricing not passed
          if ((!empty($dtl->principalProductUid)) && (empty($dtl->listPrice))) {

            // if pricing was overridden on manual capture screen then use those
            if (($dtl->overridePriceType==PCA_USE_VENDOR) && ($dtl->nettPrice>0) && (empty($dtl->listPrice)) && ($postingOrdersHoldingTO->dataSource==DS_CAPTURE)) {

              $dtl->listPrice=$dtl->nettPrice;
              $dtl->discountValue=0;
              $dtl->nettPrice=$dtl->listPrice;
              $dtl->extPrice=$dtl->nettPrice * $dtl->quantity;
              $dtl->vatRate=VAL_VAT_RATE_TBLSTD; // VAT RATE is always applied for non ORDERS as sometimes the principal does not maintain masterfiles eg. CT !!
              $dtl->vatAmount=round($dtl->extPrice * VAL_VAT_RATE,2);
              $dtl->totalPrice = $dtl->extPrice + $dtl->vatAmount;

            } else {

              // the pricingConflictAction will be blank if coming from eg. capture screen not from edi so we use "!=".
              // (this NEEDS the listPrice empty check above and the ppuid !!!)
              if (($postingOrdersHoldingTO->pricingConflictAction!=PCA_USE_VENDOR) && ($dtl->overridePriceType!=PCA_USE_VENDOR)) {
               $mfAP = $this->productDAO->getActivePricesForProduct($postingOrdersHoldingTO->principalUid,
                                                                    $postingOrdersHoldingTO->principalStoreUId,
                                                                    $dtl->principalProductUid,
                                                                    $returnVATSettings=true);

               if (isset($mfAP[0])) {
                 // active price found
                 $dtl->overridePriceType=PCA_USE_VENDOR;
                 $dtl->listPrice=$mfAP[0]["list_price"];
                 $dtl->discountValue=$mfAP[0]["discount_value"];
                 $dtl->nettPrice=$mfAP[0]["price"];
                 $dtl->extPrice=$dtl->nettPrice * $dtl->quantity;
                 $dtl->vatRate=$mfAP["calculatedVatRate"]; // this index is at root level asit applies for all rows, unlike the rest
                 $dtl->vatAmount=round($dtl->extPrice * ($dtl->vatRate/100),2);
                 $dtl->totalPrice = $dtl->extPrice + $dtl->vatAmount;
               } else {
                 $eTO->type = FLAG_ERRORTO_ERROR;
                 $eTO->description = "ProcessorTOH : No active pricing found for product!";
                 return $eTO;
               }
              }

            }

          }

        } // end loop
        unset($dtl);
      }


      // temporarily suspend documents
      /*
      if (($postingOrdersHoldingTO->principalUid=="104") && ($postingOrdersHoldingTO->dataSource==DS_WS) && ($postingOrdersHoldingTO->capturedBy==CB_PNP_WS)) {
        $postingOrdersHoldingTO->status = "SUSP";
      }
      */

			// everything passed using the list only
			$rTO=$this->postImportDAO->postOrdersHolding($postingOrdersHoldingTO);
			if ($rTO->type!=FLAG_ERRORTO_SUCCESS) {
				$eTO->type = FLAG_ERRORTO_ERROR;
				$eTO->description = "Failed to store Order in postTOH : ".$rTO->description;
        echo $eTO->description;
				return $eTO;
			}

		} // end array TO loop


   		$eTO->type = FLAG_ERRORTO_SUCCESS;
   		$eTO->description = "Successful";

    	return $eTO;
    }

}



?>