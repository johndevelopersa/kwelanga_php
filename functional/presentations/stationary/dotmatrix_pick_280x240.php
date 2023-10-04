<?php


include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER."DAO/TransactionDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/AdministrationDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/PrincipalDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/StoreDAO.php");
include_once($ROOT.$PHPFOLDER."DAO/CommonDAO.php");
include_once($ROOT.$PHPFOLDER."libs/GUICommonUtils.php");
include_once($ROOT.$PHPFOLDER."properties/Constants.php");
include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php"); // needed because of access_control commented out
include_once($ROOT.$PHPFOLDER.'properties/dbSettings.inc');
include_once($ROOT.$PHPFOLDER.'DAO/MiscellaneousDAO.php');



class Stationary{

  private $out = '';


  function __construct($docID) {

    //data collection
    $dbConn = new dbConnect();
    $dbConn->dbConnection();
    $transactionDAO = new TransactionDAO($dbConn);
    $principalDAO = new PrincipalDAO($dbConn);
    $storeDAO = new StoreDAO($dbConn);
    $miscDAO = new MiscellaneousDAO($dbConn);
    $docArr = $transactionDAO->getDocumentWithDetailIgnorePermissionsItem($docID);
    $principalArr = $principalDAO->getPrincipalItem($docArr[0]['principal_uid']);
    $storeArr = $storeDAO->getPrincipalStoreItem($docArr[0]['principal_store_uid']);
    $mfSF = $miscDAO->getPrincipalSpecialFieldValues($docArr[0]['principal_uid'], $docArr[0]['principal_store_uid'], "S", false, "processing_order");

    $mfSFValue1 = (isset($mfSF[0]['value']) && (trim($mfSF[0]['value']) != "")) ? (trim($mfSF[0]['value'])) : (false);
    $mfSFValue2 = (isset($mfSF[1]['value']) && (trim($mfSF[1]['value']) != "")) ? (trim($mfSF[1]['value'])) : (false);
    $supplierCustNumber = str_pad(substr((($mfSFValue1!==false)?$mfSFValue1:$storeArr[0]['old_account']),0,12),12," ");
    $accNumber = str_pad(substr((($mfSFValue2!==false)?$mfSFValue2:$storeArr[0]['old_account']),0,12),12," ");
    $delInstr = ""; //delivery instructions are not stored in the doucment tables awaiting fix....



    //calculate pages...
    $a = count($docArr);
    $ln = 0;
    $totaPages = 1;
    for($j = 1; $j <= $a; $j++){
      if($ln==10){
        $ln = 1;
        $totaPages++;
      } else {
        $ln++;
      }
    }

    $pageBreak = str_pad("", 70, " ") . "\r\n" . str_pad("", 70, " ") . "\r\n" . "\r\n";
    $orderQty = 0;
    $extendedPrice = 0;


    //START OUT PUT!
    $this->out .= chr(18) .         "\r" .  //Cancel Condensed Print
                  chr(27) . chr(120) . chr(48) . "\r" .
                  chr(27) . "P"   . "\r" .  //Select 10 cpi
                  chr(27) . "P0"  . "\r" .  //Proportional Spacing Off
                  chr(27) . "p0"  . "\r" .  //Proportional Spacing Off
                  chr(27) . "0"   . "\r" .  //Line Spacing, Set 1/8"
                  chr(27) . 'CD'  . "\r\n"; //Form Length n Lines (n = 1 to 255) | ESC C n | 27 67 n


    $ln = 0;
    $currentPage = 1;
    for($i = 0; $i < $a; $i++){

      if($ln == 10 || $ln == 0){

        //header
        if($ln != 0){
          $this->out .= "\f" . $pageBreak;
        }
		
        $this->out .= "\r\n";
        $this->out .= "\r\n";
        $this->out .= "\r\n";
		$this->out .= "\r\n";
        $this->out .= "                  * * *  P I C K I N G   S L I P  * * *                  " . "\r\n";
        $this->out .= "\r\n";

        $this->out .= "   " . str_pad(substr($principalArr[0]['principal_name'],0,30), 35, " ") . str_pad($docArr[0]['depot_name'], 35, " ", STR_PAD_LEFT) . "\r\n";
        $this->out .= "\r\n";
        $this->out .= "   " . str_pad("Document No. : " . substr(abs($docArr[0]['document_number']),0,30), 35, " ", STR_PAD_RIGHT) . str_pad("Page :  " . $currentPage . ' of ' . $totaPages, 35, " ", STR_PAD_LEFT) . "\r\n";
        $this->out .= "   " . str_pad("Invoice No. : " . substr(($docArr[0]['invoice_number']>0?abs($docArr[0]['invoice_number']):''),0,30), 35, " ", STR_PAD_RIGHT) . str_pad("Date :  " . gmdate('Y/m/d'), 35, " ", STR_PAD_LEFT) . "\r\n";
        $this->out .= "   " . str_pad("Customer Order No : " . substr(($docArr[0]['customer_order_number']!=''?abs($docArr[0]['customer_order_number']):''),0,30), 35, " ", STR_PAD_RIGHT) . str_pad("Order Date :  " . str_replace('-','/',$docArr[0]['order_date']), 35, " ", STR_PAD_LEFT) . "\r\n";
        $this->out .= "   " . str_pad(substr($storeArr[0]['store_name'],0,35), 35, " ") . str_pad("Del Date :  " . ($docArr[0]['delivery_date'] != '0000-00-00' ? str_replace('-','/',$docArr[0]['delivery_date']) : '          '), 35, " ", STR_PAD_LEFT) . "\r\n";
        $this->out .= "   " . str_pad(substr($storeArr[0]['deliver_add1'],0,35), 35, " ") . "\r\n";
        $this->out .= "   " . str_pad(substr($storeArr[0]['deliver_add2'],0,35), 35, " ") . "\r\n";
        $this->out .= "   " . str_pad(substr($storeArr[0]['deliver_add3'],0,35), 35, " ") . "\r\n";
        $this->out .= "\r\n";
        $this->out .= "\r\n";
        $this->out .= "                                                      Qty.   Case    Total    " . "\r\n";
        $this->out .= "   Product Description                      Prod.Code Ord    Price   Exc Vat  " . "\r\n";
        $this->out .= "   " . str_pad("", 75, "-") . "\r\n";

        $ln = 0;
        $currentPage++;

      }

      $ln++;

      //lines
      $this->out .= "   " . str_pad(substr(strtoupper($docArr[$i]['product_description']),0,37), 40, " ") .
                            str_pad(" ".substr($docArr[$i]['product_code'],0,11), 12, " ", STR_PAD_RIGHT) .
                            str_pad($docArr[$i]['ordered_qty']." ",6," ", STR_PAD_LEFT) .
                            str_pad(number_format($docArr[$i]['net_price'], 2, '.', '')." ",8," ", STR_PAD_LEFT) .
                            str_pad(number_format($docArr[$i]['extended_price'], 2, '.', '')." ",10," ", STR_PAD_LEFT) .
                            "\r\n";

      $orderQty += $docArr[$i]['ordered_qty'];
      $extendedPrice += $docArr[$i]['extended_price'];

      if($ln == 10 && $totaPages != ($currentPage-1)){
        $this->out .= "   " . str_pad("", 75, "-") . "\r\n";
        $this->out .= "   " . "CONTINUED ON NEXT PAGE..." . "\r\n";
      }
    }

    //if product lines don't total ten...
//    for($i=0; $i < (10-$ln); $i++){
//      $this->out .= "\r\n";
//      $this->out .= "\r\n";
//    }

    $this->out .= "   " . str_pad("", 75, "-") . "\r\n";

    //lines to build up to total lines...
    $this->out .= "\r\n";

    //TOTAL LINE
    $this->out .= str_pad(" TOTAL : ", 50, " ", STR_PAD_LEFT) .
                  str_pad($orderQty,10," ", STR_PAD_LEFT) .
                  str_pad(number_format($extendedPrice, 2, '.', ''),18," ", STR_PAD_LEFT);


    $this->out .= "\f";  //FORM FEED.


  }


  function render(){

    //return $this->out;  //prepare for javascript output....

    return str_replace("\n", "\\n", str_replace("\r","\\r",$this->out));  //prepare for javascript output....
  }

}


?>