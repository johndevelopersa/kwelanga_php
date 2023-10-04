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

    $pageBreak = str_pad("", 132, " ") . "\r\n" . str_pad("", 132, " ") . "\r\n" . "\r\n";

    $documentQty = 0;
    $extendedPrice = 0;
    $vatAmount = 0;
    $total = 0;


    //START OUT PUT!
    $this->out .= chr(18) .         "\r" .  //Cancel Condensed Print
                  chr(27) . chr(120) . chr(48) . "\r" .
                  chr(27) . "P"   . "\r" .  //Select 10 cpi
                  chr(27) . "P0"  . "\r" .  //Proportional Spacing Off
                  chr(27) . "p0"  . "\r" .  //Proportional Spacing Off
                  chr(27) . "0"   . "\r" .  //Line Spacing, Set 1/8"
                  chr(27) . 'CB'  . "\r\n"; //Form Length n Lines (n = 1 to 255) | ESC C n | 27 67 n


    $ln = 0;
    $currentPage = 1;
    for($i = 0; $i < $a; $i++){

      if($ln == 10 || $ln == 0){

        //header
        if($ln != 0){
          $this->out .= "\f" . $pageBreak;
        }

        $documentNo = str_pad(substr($docArr[0]['document_number'],0,12), 12, " ");
        $invoiceNo = str_pad(substr((($docArr[0]['invoice_number']>0 || $docArr[0]['invoice_number']!='') ? $docArr[0]['invoice_number'] : $docArr[0]['document_number']) ,0,14), 14, " ");

        //swop values.
        if($docArr[0]['principal_uid'] == 4){  //brennco brands

          $holder1 = $documentNo;
          $holder2 = $invoiceNo;

          $documentNo = $holder2;
          $invoiceNo = $holder1;
        }

        $this->out .= "          **** SUPPLIER ****                              " . str_pad(substr($principalArr[0]['principal_name'],0,30), 30, " ") . "\r\n";
        $this->out .= "     " . str_pad(substr($principalArr[0]['principal_name'],0,30), 30, " ") . str_pad("", 23, " ") . str_pad(substr($principalArr[0]['physical_add1'],0,30), 30, " ") . "\r\n";
        $this->out .= "     " . str_pad(substr($principalArr[0]['postal_add1'],0,30), 30, " ") . str_pad("", 23, " ") . str_pad(substr($principalArr[0]['physical_add2'],0,30), 30, " ") . "                " . (isset($_GET['ISCOPY'])?("COPY "):("     ")) . (($docArr[0]['document_type_uid']==DT_UPLIFTS)?("UPLIFTMENT"):("TAX INVOICE")) . "\r\n";
        $this->out .= "     " . str_pad(substr($principalArr[0]['postal_add2'],0,30), 30, " ") . str_pad("", 23, " ") . str_pad(substr($principalArr[0]['physical_add3'],0,20) . ' ' . substr($principalArr[0]['physical_add4'],0,10), 30, " ") . "\r\n";
        $this->out .= "     " . str_pad(substr($principalArr[0]['postal_add3'],0,30), 30, " ") . str_pad("", 23, " ") . str_pad("OFFICE: ".substr($principalArr[0]['office_tel'],0,15), 30, " ") . "\r\n";
        $this->out .= "     " . str_pad(substr($principalArr[0]['postal_add4'],0,30), 30, " ") . str_pad("", 69, " ") . $invoiceNo . "       " . $currentPage . ' of ' . $totaPages . "\r\n";
        $this->out .= "     " . "VAT REG.NO: " . str_pad($principalArr[0]['vat_num'], 102, " ") . "\r\n";
        $this->out .= "     " . str_pad("", 114, " ") . $supplierCustNumber . "\r\n";
        $this->out .= "\r\n";
        $this->out .= "     " . str_pad("", 114, " ") . $accNumber . "\r\n";
        $this->out .= "\r\n";
        $this->out .= "     " . str_pad(substr($storeArr[0]['bill_name'],0,30), 30, " ") . str_pad("", 10, " ") . str_pad(substr($storeArr[0]['store_name'],0,30), 30, " ") . str_pad("", 44, " ") . $documentNo . "\r\n";
        $this->out .= "     " . str_pad(substr($storeArr[0]['bill_add1'],0,30), 30, " ") . str_pad("", 10, " ") . str_pad(substr($storeArr[0]['deliver_add1'],0,30), 30, " ") . "\r\n";
        $this->out .= "     " . str_pad(substr($storeArr[0]['bill_add2'],0,30), 30, " ") . str_pad("", 10, " ") . str_pad(substr($storeArr[0]['deliver_add2'],0,30), 30, " ") . str_pad("", 44, " ") . str_pad(substr($docArr[0]['customer_order_number'],0,12), 12, " ") . "\r\n";
        $this->out .= "     " . str_pad(substr($storeArr[0]['bill_add3'],0,30), 30, " ") . str_pad("", 10, " ") . str_pad(substr($storeArr[0]['deliver_add3'],0,30), 30, " ") . "\r\n";
        $this->out .= "         \r\n";
        $this->out .= "\r\n";
        $this->out .= "     " . str_pad("", 114, " ") . str_replace('-','/',$docArr[0]['order_date']) . "\r\n";
        $this->out .= "\r\n";
        $this->out .= "     " . str_pad("", 114, " ") . str_replace('-','/',$docArr[0]['invoice_date']) . "\r\n";
        $this->out .= "\r\n";
        $this->out .= "     CUSTOMER VAT NO  " . str_pad($storeArr[0]['vat_number'], 107, " ") . "\r\n";
        $this->out .= "\r\n";
        $this->out .= "                    " . str_pad($delInstr, 81, " ") . "Total Mass         0.000 KG" . "\r\n";
        $this->out .= "\r\n";
        $this->out .= "\r\n";
        $this->out .= "                                                                         E X C L U D E S  V.A.T.                     INCLUDES V.A.T\r\n";
        $this->out .= "\r\n";
        $this->out .= "                                            PRODUCT  ORDER  CASE      DISCOUNT   DISCOUNT   NETT    TOTAL    VAT    TOTAL      TOTAL\r\n";
        $this->out .= " P R O D U C T    D E S C R I P T I O N      CODE      QTY   PRICE     VALUE    REFERENCE   PRICE   TAXABLE  RATE   TAX        DUE\r\n";
        $this->out .= "\r\n";


        $ln = 0;
        $currentPage++;

      }

      $ln++;

      //lines
      $this->out .= "  " . str_pad(substr(strtoupper($docArr[$i]['product_description']),0,37), 39, " ") .
                           str_pad(substr($docArr[$i]['product_code'],0,12), 12, " ") .
                           str_pad($docArr[$i]['document_qty'],6," ", STR_PAD_LEFT);

      if(abs($docArr[$i]['document_qty']) == 0){

        $this->out .= "    OUT OF STOCK \r\n";

      } else {

        $this->out .= str_pad(number_format($docArr[$i]['selling_price'], 2, '.', ''),12," ", STR_PAD_LEFT) .
        str_pad(number_format($docArr[$i]['discount_value'], 2, '.', ''),9," ", STR_PAD_LEFT) .
        str_pad(substr($docArr[$i]['discount_reference'], 0, 10),10," ", STR_PAD_BOTH) .
        str_pad(number_format($docArr[$i]['net_price'], 2, '.', ''),8," ", STR_PAD_LEFT) .
        str_pad(number_format($docArr[$i]['extended_price'], 2, '.', ''),11," ", STR_PAD_LEFT) .
        str_pad(number_format($docArr[$i]['vat_rate'], 2, '.', ''),6," ", STR_PAD_LEFT) .
        str_pad(number_format($docArr[$i]['vat_amount'], 2, '.', ''),9," ", STR_PAD_LEFT) .
        str_pad(number_format($docArr[$i]['total'], 2, '.', ''),10," ", STR_PAD_LEFT) .
        "\r\n";

      }


      $this->out .= "\r\n";

      $documentQty += $docArr[$i]['document_qty'];
      $extendedPrice += $docArr[$i]['extended_price'];
      $vatAmount += $docArr[$i]['vat_amount'];
      $total += $docArr[$i]['total'];

    }

    //if product lines don't total ten...
    for($i=0; $i < (10-$ln); $i++){
      $this->out .= "\r\n";
      $this->out .= "\r\n";
    }

    //lines to build up to total lines...
    $this->out .= "\r\n";
    $this->out .= "\r\n";
    $this->out .= "\r\n";
    $this->out .= "\r\n";
    $this->out .= "\r\n";
    $this->out .= "\r\n";
    $this->out .= "\r\n";


    //TOTAL LINE
    $this->out .= str_pad("", 49, " ") .
                  str_pad($documentQty,10," ", STR_PAD_LEFT) .
                  str_pad(number_format($extendedPrice, 2, '.', ''),50," ", STR_PAD_LEFT) .
                  str_pad(number_format($vatAmount, 2, '.', ''),15," ", STR_PAD_LEFT) .
                  str_pad(number_format($total, 2, '.', ''),10," ", STR_PAD_LEFT);

    $this->out .= "\f";  //FORM FEED.


  }


  function render(){

    //debug
    //file_put_contents('c:\text.txt', $this->out);

    return str_replace("\n", "\\n", str_replace("\r","\\r",$this->out));  //prepare for javascript output....
  }

}


?>