 <?php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
// COMMONUTILS should be included in calling script !!

/**
 * Description of AdaptorCustomDocumentConfirmation
 *
 * @author onyx_rtt
 */

class AdaptorCustomDocumentConfirmation {

  private $dbConn;
  private $bIDAO;
  private $postBIDAO;

  function __construct($dbConn, $bIDAO) {
       $this->dbConn = $dbConn;
       $this->bIDAO = $bIDAO;
    }


  public function RIESES($docArr){

    global $ROOT;
    $returnTO = new errorTO();
    $returnTO->identifier = OT_CSV;

    //FILENAME
    $sequenceDAO = new SequenceDAO(null);
    $fileSeq = $sequenceDAO->getFTPFileExportSequence();
    $filename = 'CNFU'.$fileSeq.'.edi'; //don't care about output type.

    //FILE CONTENTS
    $dataArr = array();
    foreach($docArr as $doc){
      $dataArr[] = str_replace('-','',$doc['merged_date']) .'|'. $doc['merged_time'] .'|'. abs(trim($doc['document_number'])); //ADD 2 HOURS TO MAKE GMT+2:00
    }
    $dataStr = join("\r\n",$dataArr);


    //CREATE FILE.
    $bkupFolder = CommonUtils::createBkupDirs($ROOT.FILE_ARCHIVE_EXPORTS_PATH.'rieses/');
    $fSize = file_put_contents($bkupFolder.$filename, $dataStr);
    if($fSize != strlen($dataStr)){
	BroadcastingUtils::sendAlertEmail("Error in AdaptorCustomDocumentConfirmation", "error running RIESES Adaptor, could not create Depot Confirmation file.", "Y", false);
    }

    $returnTO->type=FLAG_ERRORTO_SUCCESS;

    $returnTO->object = $bkupFolder.$filename;
    return $returnTO;

  }

  public function TRADEMODEL($docArr){

    global $ROOT, $bIDAO, $miscellaneousDAO;
    $returnTO = new errorTO();
    $returnTO->identifier = OT_CSV;

    $principalTRADEMODEL = 228;

    //FILENAME
    $sequenceDAO = new SequenceDAO(null);
    $fileSeq = $sequenceDAO->getFTPFileExportSequence();
    $filename = 'TM'.$fileSeq.'.csv'; //don't care about output type.

    $grpDocs = $uniqueUIDs = $psms = array();
    foreach($docArr as &$r){
      $grpDocs[$r['uid']]["header"] = $r;
      $uniqueUIDs[$r['uid']] = $r['uid'];
      $psms[$r["psm_uid"]] = $r["psm_uid"];
    }
    unset($r);

    $dtl = $bIDAO->getBIDocumentsDetailsByUIdList(implode(",",$uniqueUIDs));
    foreach($grpDocs as &$r){
      $r["detail"] = $dtl[$r["header"]["uid"]];
    }
    unset($r);

    // get special field values for all stores in above docs
     if (sizeof($psms)>0) {
      $sfvals = $miscellaneousDAO->getPrincipalSpecialFieldValuesMultEntities($principalTRADEMODEL, 258, implode(",",$psms), CT_STORE_SHORTCODE, $arrayIndex="entity_uid");
      }

    if (sizeof($psms)>0) {
      $sfvalsACC1    = $miscellaneousDAO->getPrincipalSpecialFieldValuesMultEntities($principalTRADEMODEL, 258, implode(",",$psms), CT_STORE_SHORTCODE, $arrayIndex="entity_uid");
      $sfvalsACC2    = $miscellaneousDAO->getPrincipalSpecialFieldValuesMultEntities($principalTRADEMODEL, 261, implode(",",$psms), CT_STORE_SHORTCODE, $arrayIndex="entity_uid");
      $sfvalsSALCODE = $miscellaneousDAO->getPrincipalSpecialFieldValuesMultEntities($principalTRADEMODEL,  263, implode(",",$psms), CT_STORE_SHORTCODE, $arrayIndex="entity_uid");
      $sfvalsSTORE   = $miscellaneousDAO->getPrincipalSpecialFieldValuesMultEntities($principalTRADEMODEL,  264, implode(",",$psms), CT_STORE_SHORTCODE, $arrayIndex="entity_uid");
      $sfvalsPROJECT = $miscellaneousDAO->getPrincipalSpecialFieldValuesMultEntities($principalTRADEMODEL,  265, implode(",",$psms), CT_STORE_SHORTCODE, $arrayIndex="entity_uid");
     }

    //FILE CONTENTS
    $dataArr = array();

    foreach($grpDocs as $ord){

      //period
      $period = '00';
      switch (date("m", strtotime($ord["header"]["order_date"]))) {
        case '01':
          $period = 11;
          break;
        case '02':
          $period = 12;
          break;
        case '03':
          $period = 1;
          break;
        case '04':
          $period = 2;
          break;
        case '05':
          $period = 3;
          break;
        case '06':
          $period = 4;
          break;
        case '07':
          $period = 5;
          break;
        case '08':
          $period = 6;
          break;
        case '09':
          $period = 7;
          break;
        case '10':
          $period = 8;
          break;
        case '11':
          $period = 9;
          break;
        case '12':
          $period = 10;
          break;
      }

        // $pastelAccount = $sfvals[$ord["header"]["psm_uid"]]['value'];
			$pastelAccount = ((trim($ord["detail"][0]['major_category']) == '201') ? $sfvalsACC2[$ord["header"]["psm_uid"]]["value"] : $sfvalsACC1[$ord["header"]["psm_uid"]]["value"]);

      $salesCode     = $sfvalsSALCODE[$ord["header"]["psm_uid"]]['value'];
      $storeCode     = $sfvalsSTORE[$ord["header"]["psm_uid"]]['value'];
      $projectCode   = $sfvalsPROJECT[$ord["header"]["psm_uid"]]['value'];

      if(empty($pastelAccount)){  //has no special field and/or blank...
        // do nothing for time being, not expected to be blank because PA is a required field and can only be blank if someone fiddled
      }


      /* PASTEL HEADER */
      //array containing list of row values
      $rowArr = array();
      $rowArr[] = '"HEADER"';
      $rowArr[] = '"IA' . str_pad(substr($ord["header"]['document_number'],-6), 6, 0, STR_PAD_LEFT) . '"';
      $rowArr[] = '" "';
      $rowArr[] = '"Y"';  //printed
      $rowArr[] = '"'.$pastelAccount.'"';  //CUSTOMER CODE - Pastel Account.
      $rowArr[] = '"'.$period.'"';  //period number
      $rowArr[] = '"'.date("d/m/Y", strtotime($ord["header"]["order_date"])).'"';  //DATE (DD/MM/YYYY)
      $rowArr[] = '"'.trim(str_replace(array('"',"'"),array('',''),$ord["header"]["customer_order_number"])).'"';
      $rowArr[] = '"N"';  //IN / EX - CHAR
      $rowArr[] = '"0"';  //discount
      $rowArr[] = '" "';  //MESSAGE - CHAR
      $rowArr[] = '""';   //MESSAGE - CHAR
      $rowArr[] = '""';   //MESSAGE - CHAR
      $rowArr[] = '"'.trim($ord["header"]["deliver_name"]).'"';
      $rowArr[] = '"'.trim($ord["header"]["deliver_add1"]).'"';
      $rowArr[] = '"'.trim($ord["header"]["deliver_add2"]).'"';
      $rowArr[] = '"'.trim($ord["header"]["deliver_add3"]).'"';
      $rowArr[] = '""';
      $rowArr[] = '"'.$salesCode.'"'; //SALES ANALYSIS - CHAR
      $rowArr[] = '""';
      $rowArr[] = '"'.date("d/m/Y", strtotime($ord["header"]["order_date"])).'"';  //DATE (DD/MM/YYYY)
      $rowArr[] = '""';
      $rowArr[] = '""';
      $rowArr[] = '""';
      $rowArr[] = '0';
      $rowArr[] = '"N"';
      $rowArr[] = '" "';
      $rowArr[] = '" "';
      $dataArr[] = join(',',$rowArr);

      foreach($ord["detail"] as $d){ //detail rows.

        if(abs($d['ordered_qty'])>0){

          $detArr = array();
          $detArr[] = '"DETAIL"';
          $detArr[] = '0';
          $detArr[] = abs($d['ordered_qty']);
          $detArr[] = number_format(abs(round($d['net_price'], 2)), 2, '.', ''); //SELLING PRICE - NUM
          $detArr[] = number_format(abs(round(($d['net_price']*VAL_VAT_RATE_ADD), 2)), 2, '.', '');  //INCLUSIVE PRICE - NUM
          $detArr[] = '" "';  //UNIT - CHAR
          $detArr[] = (substr($d['vat_rate'],0,2)=='14')?1:2;
          $detArr[] = '3'; //DISCOUNT TYPE
          // $detArr[] = (abs($d['selling_price'])>0 && abs($d['discount_value'])>0) ?  number_format(round($d['discount_value'] / $d['selling_price'] * 100, 2), 2, '', '') : 0; //DISCOUNT %  //stored value : discount_value backwards calculation.
          $detArr[] = 0;
          $detArr[] = '"' . trim(str_replace(array('"'),array(''),$d['product_code'])) . '"';
          $detArr[] = '"' . trim(str_replace(array('"','\\',"\t","\n","\r"),array('','','','',''),$d['product_description'])) . '"';
          $detArr[] = '4';  //unknown.
          $detArr[] = '"'.$projectCode.'"'; //Project Code - CHAR
          $detArr[] = '"'.$storeCode.'"'; //Warehouse Store - CHAR
          
          
          

          $dataArr[] = join(',',$detArr);

        }
      } // detail

    }
    $dataStr = join("\r\n",$dataArr);


    //CREATE FILE.
    $bkupFolder = CommonUtils::createBkupDirs($ROOT.FILE_ARCHIVE_EXPORTS_PATH.'trademodel/');
    $fSize = file_put_contents($bkupFolder.$filename, $dataStr);
    if($fSize != strlen($dataStr)){
      BroadcastingUtils::sendAlertEmail("Error in AdaptorCustomDocumentConfirmation", "error running TRADEMODEL Adaptor, could not create Depot Confirmation file.", "Y", false);
    }

    $returnTO->type=FLAG_ERRORTO_SUCCESS;

    $returnTO->object = $bkupFolder.$filename;
    return $returnTO;

  }


  public function PLAINTEXT($docArr){

    global $ROOT;
    $returnTO = new errorTO();
    $returnTO->identifier = OT_CSV;

    //FILENAME
    $sequenceDAO = new SequenceDAO(null);
    $fileSeq = $sequenceDAO->getFTPFileExportSequence();
    $filename = 'CONFIRM'.$fileSeq.'.txt'; //don't care about output type.

    //FILE CONTENTS
    $dataArr = array();
    $dataArr[] = str_pad('Depot', 25, ' ', STR_PAD_RIGHT) .
                 str_pad('Store Name', 35, ' ', STR_PAD_RIGHT) .
                 str_pad('Document', 10, ' ', STR_PAD_RIGHT) .
                 str_pad('Incoming File', 15, ' ', STR_PAD_RIGHT) .
                 str_pad('Reference', 15, ' ', STR_PAD_RIGHT) .
                 str_pad('Processed Date/Time', 20, ' ', STR_PAD_RIGHT);
    $dataArr[] = str_pad('',119,'-');

    foreach($docArr as $doc){
      $dataArr[] = str_pad(substr($doc["depot_name"],0,23), 25, ' ', STR_PAD_RIGHT) .
                   str_pad(substr(trim($doc["deliver_name"]),0,33), 35, ' ', STR_PAD_RIGHT) .
                   str_pad(substr(abs(trim($doc["document_number"])),-8), 10, ' ', STR_PAD_RIGHT) .
                   str_pad(substr($doc["incoming_file"],0,14), 15, ' ', STR_PAD_RIGHT) .
                   str_pad(substr($doc["customer_order_number"],0,15), 15, ' ', STR_PAD_RIGHT) .
                   str_pad(substr($doc["processed_date"]." ".$doc["processed_time"],0,20), 20, ' ', STR_PAD_RIGHT);
    }
    $dataStr = join("\r\n",$dataArr);


    //CREATE FILE.
    $bkupFolder = CommonUtils::createBkupDirs($ROOT.FILE_ARCHIVE_EXPORTS_PATH.'plaintext/');
    $fSize = file_put_contents($bkupFolder.$filename, mb_convert_encoding($dataStr, 'ASCII'));
    if($fSize != strlen($dataStr)){
	BroadcastingUtils::sendAlertEmail("Error in AdaptorCustomDocumentConfirmation", "error running PLAINTEXT Adaptor, could not create Depot Confirmation file.", "Y", false);
    }

    $returnTO->type=FLAG_ERRORTO_SUCCESS;

    $returnTO->object = $bkupFolder.$filename;
    return $returnTO;

  }

}

?>
