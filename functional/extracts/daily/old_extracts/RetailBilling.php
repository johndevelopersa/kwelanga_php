<?php

/* * ********************************************************************************************
 * *
 * *  This job can run as many times per day as is necessary according to job scheduler.
 * *
 * *  It executes notifications that occur throughout the day by triggers
 * *
 * ******************************************************************************************** */

include_once('ROOT.php');
include_once($ROOT . 'PHPINI.php');
include_once($ROOT . $PHPFOLDER . "functional/extracts/daily/extractController.php");


//static method handler.
class RetailBilling {
  public static function generateOutput(){
    $className = basename(__FILE__,'.php').'Init';
    global $ROOT, $PHPFOLDER, $dbConn;
    $obj = new $className();
    return $obj->generateOutput();
  }
}

class RetailBillingInit extends extractController {

  private $principalUid = 171; //uid of principal extract.
  private $filename = 'RB[@FSEQ].csv';  //main controller will build full filename with seq. for us.
  

  public function generateOutput(){

    global $ROOT, $PHPFOLDER;

    //name in email and folder to place bkup files.
    $pArr = $this->principalDAO->getPrincipalItem($this->principalUid);
    if (count($pArr)==0) {
      BroadcastingUtils::sendAlertEmail("System Error", "Extract failed to load principal item in ".get_class($this)."!", "Y");
      return $this->errorTO;
    }
    $principalName = $pArr[0]['principal_name'];
    $folder = $this->principalUid . '_' . explode(' ',  strtolower($pArr[0]['principal_name']))[0]; //folder replaced with principal id + first WORD of principal.
     
    
        
    $billingArr = $this->BillingDAO->getBillingArray();
    if (count($billingArr)==0) {
      BroadcastingUtils::sendAlertEmail("System Error", "Extract failed load remittance in ".get_class($this)."!", "Y");
      return $this->errorTO;
    }
    
//    print_r($billingArr);
    foreach($billingArr as $b=>$billing){                  
      $storeAcc = trim($billing["Account No"]);

          /*-------------------------------------------------*/
          /*            START BUILDING OUTPUT
          /*-------------------------------------------------*/
          
           $period = '00';
        //switch (date("m", strtotime($ord[0]["invoice_date"]))) {  //invoice date with be the period end -> comes from last month.
        switch (date("m", strtotime('2018-01-27'))) { 
          case '01':
            $period = 11;
            $custOrderNo = "January - 2018";
            break;
          case '02':
            $period = 12;
            $custOrderNo = "BILL : February - 2015";
            break;
          case '03':
            $period = 13;
            $custOrderNo = "BILL : March - 2017";
            break;
          case '04':
            $period = 2;
            $custOrderNo = "BILL : April- 2017";
            break;
          case '05':
            $period = 3;
            $custOrderNo = "BILL : May - 2017";
            break;
          case '06':
            $period = 4;
            $custOrderNo = "BILL : June - 2017";
            break;
          case '07':
            $period = 5;
            $custOrderNo = "BILL : July - 2016";
            break;
          case '08':
            $period = 6;
            $custOrderNo = "BILL : August - 2015";
            break;
          case '09':
            $period = 7;
            $custOrderNo = "BILL : September - 2015";
            break;
          case '10':
            $period = 8;
            $custOrderNo = "BILL : October - 2015";
            break;
          case '11':
            $period = 9;
            $custOrderNo = "BILL : November - 2016";
            break;
          case '12':
            $period = 10;
            $custOrderNo = "BILL : December - 2016";
            break;
        }

            /* PASTEL HEADER */
            //array containing list of row values
        $inSequence = "IN".$this->getBillingFileSequence($this->principalUid, 1);
        $rowArr = array();
        $rowArr[] = '"HEADER"';
        $rowArr[] = '"'.$inSequence.'"';
        $rowArr[] = '" "';  //space(1);
        $rowArr[] = '" "';  //printed
        $rowArr[] = '"'.$storeAcc.'"';  //CUSTOMER CODE - Pastel Account.
        $rowArr[] = $period;  //period number
        //$rowArr[] = '"'.date("d/m/Y", strtotime($ord[0]["invoice_date"])).'"';  //DATE (DD/MM/YYYY)
        $rowArr[] = '"27/01/2018"';  //DATE (DD/MM/YYYY)
        $rowArr[] = '"'.$custOrderNo.'"';
        $rowArr[] = '"N"';  //IN / EX - CHAR
        $rowArr[] = '0';  //discount
        $rowArr[] = '""';  //MESSAGE - CHAR
        $rowArr[] = '""';   //MESSAGE - CHAR
        $rowArr[] = '""';   //MESSAGE - CHAR
        $rowArr[] = '"'.trim($billing["name"]).'"';
        $rowArr[] = '"'.trim($billing["physical_add1"]).'"';
        $rowArr[] = '"'.trim($billing["physical_add2"]).'"';        
        if (!empty($billing["physical_add4"])){
          $rowArr[] = '"'.trim($billing["physical_add3"]).' '.trim($billing["physical_add4"]).'"';
        } else {
            $rowArr[] = '"'.trim($billing["physical_add3"]).'"';
          }      
        $rowArr[] = '""';
        $rowArr[] = '""'; //SALES ANALYSIS - CHAR
        $rowArr[] = '0';
        //$rowArr[] = '"'.date("d/m/Y", strtotime($ord[0]["invoice_date"])).'"';  //CLOSING DATE (DD/MM/YYYY)
        $rowArr[] = '"27/01/2018"';  //DATE (DD/MM/YYYY)
        $rowArr[] = '""';
        $rowArr[] = '""';
        $rowArr[] = '""';
        $rowArr[] = '0';
        $rowArr[] = '""';
        $rowArr[] = '""';
        $rowArr[] = '"N"';
        $rowArr[] = '" "';
        $dataArr[] = join(',',$rowArr);          

        if(abs($billing['Docs'])>0){

          $detArr = array();
          $detArr2 = array();
          $detArr[] = '"DETAIL"';
          $detArr[] = '0';
          $detArr[] = abs($billing['Docs']);
          $detArr[] = number_format(round($billing['DocCost'], 2), 2, '.', ''); //SELLING PRICE - NUM
          $detArr[] = number_format(round(($billing['DocCost']*VAL_VAT_RATE_ADD), 2), 2, '.', '');  //INCLUSIVE PRICE - NUM
          $detArr[] = '" "';  //UNIT - CHAR
          $detArr[] = '1';
          $detArr[] = '0'; //DISCOUNT TYPE
          //$detArr[] = (abs($d['selling_price'])>0 && abs($d['discount_value'])>0) ?  number_format(round($d['discount_value'] / $d['selling_price'] * 100, 2), 2, '', '') : 0; //DISCOUNT %  //stored value : discount_value backwards calculation.
          $detArr[] = '0'; 
          //$detArr[] = '"' . trim(str_replace(array('"'),array(''),$d['product_code'])) . '"';
          //$detArr[] = '"' . trim(str_replace(array('"','\\',"\t","\n","\r"),array('','','','',''),$d['product_description'])) . '"';
          $detArr[] = '"DOCS"';
          $detArr[] = '"Document Charge"';
          $detArr[] = '4';  //unknown.
          $detArr[] = '""';
          $detArr[] = '"001"';

          $dataArr[] = join(',',$detArr);

        }
         if(abs($billing['ServerHosting'])>0){

          $detArr = array();
          $detArr2 = array();
          $detArr[] = '"DETAIL"';
          $detArr[] = '0';
          $detArr[] = '1';
          $detArr[] = number_format(round($billing['ServerHosting'], 2), 2, '.', ''); //SELLING PRICE - NUM
          $detArr[] = number_format(round(($billing['ServerHosting']*VAL_VAT_RATE_ADD), 2), 2, '.', '');  //INCLUSIVE PRICE - NUM
          $detArr[] = '" "';  //UNIT - CHAR
          $detArr[] = '1';
          $detArr[] = '0'; //DISCOUNT TYPE
          //$detArr[] = (abs($d['selling_price'])>0 && abs($d['discount_value'])>0) ?  number_format(round($d['discount_value'] / $d['selling_price'] * 100, 2), 2, '', '') : 0; //DISCOUNT %  //stored value : discount_value backwards calculation.
          $detArr[] = '0'; 
          //$detArr[] = '"' . trim(str_replace(array('"'),array(''),$d['product_code'])) . '"';
          //$detArr[] = '"' . trim(str_replace(array('"','\\',"\t","\n","\r"),array('','','','',''),$d['product_description'])) . '"';
          $detArr[] = '"SERV01"';
          $detArr[] = '"Server Hosting"';
          $detArr[] = '4';  //unknown.
          $detArr[] = '""';
          $detArr[] = '"001"';

          $dataArr[] = join(',',$detArr);

        }
        
        if(abs($billing['MinAdjust'])>0){

          $detArr = array();
          $detArr2 = array();
          $detArr[] = '"DETAIL"';
          $detArr[] = '0';
          $detArr[] = '1';
          $detArr[] = number_format(round($billing['MinAdjust'], 2), 2, '.', ''); //SELLING PRICE - NUM
          $detArr[] = number_format(round(($billing['MinAdjust']*VAL_VAT_RATE_ADD), 2), 2, '.', '');  //INCLUSIVE PRICE - NUM
          $detArr[] = '" "';  //UNIT - CHAR
          $detArr[] = '1';
          $detArr[] = '0'; //DISCOUNT TYPE
          //$detArr[] = (abs($d['selling_price'])>0 && abs($d['discount_value'])>0) ?  number_format(round($d['discount_value'] / $d['selling_price'] * 100, 2), 2, '', '') : 0; //DISCOUNT %  //stored value : discount_value backwards calculation.
          $detArr[] = '0'; 
          //$detArr[] = '"' . trim(str_replace(array('"'),array(''),$d['product_code'])) . '"';
          //$detArr[] = '"' . trim(str_replace(array('"','\\',"\t","\n","\r"),array('','','','',''),$d['product_description'])) . '"';
          $detArr[] = '"MIN"';
          $detArr[] = '"Minimum Charges"';
          $detArr[] = '4';  //unknown.
          $detArr[] = '""';
          $detArr[] = '"001"';

          $dataArr[] = join(',',$detArr);
        }
        if(abs($billing['DebtFee'])>0){

          $detArr = array();
          $detArr2 = array();
          $detArr[] = '"DETAIL"';
          $detArr[] = '0';
          $detArr[] = '1';
          $detArr[] = number_format(round($billing['DebtFee'], 2), 2, '.', ''); //SELLING PRICE - NUM
          $detArr[] = number_format(round(($billing['DebtFee']*VAL_VAT_RATE_ADD), 2), 2, '.', '');  //INCLUSIVE PRICE - NUM
          $detArr[] = '" "';  //UNIT - CHAR
          $detArr[] = '1';
          $detArr[] = '0'; //DISCOUNT TYPE
          $detArr[] = '0'; 
          $detArr[] = '"DEBT"';
          $detArr[] = '"Debtors Management"';
          $detArr[] = '4';  //unknown.
          $detArr[] = '""';
          $detArr[] = '"001"';

          $dataArr[] = join(',',$detArr);

          $detArr2 = array();
          $detArr2[] = '"DETAIL"';
          $detArr2[] = '0';
          $detArr2[] = '1';
          $detArr2[] = '0';
          $detArr2[] = '0';
          $detArr2[] = '" "';  //UNIT - CHAR
          $detArr2[] = '1';
          $detArr2[] = '0'; //DISCOUNT TYPE
          $detArr2[] = '0'; 
          $detArr2[] = '"."';
          $detArr2[] = '"Turn Over '. $billing['Turnover'] . ' Rate ' . 	$billing['DebtRate']. '%"' ;
          $detArr2[] = '7';  //unknown.
          $detArr2[] = '""';
          $detArr2[] = '"001"';

          $dataArr[] = join(',',$detArr2);
        }
 
         if(abs($billing['%ofTurnover'])>0){

          $detArr = array();
          $detArr2 = array();
          $detArr[] = '"DETAIL"';
          $detArr[] = '0';
          $detArr[] = '1';
          $detArr[] = number_format(round($billing['%ofTurnover'], 2), 2, '.', ''); //SELLING PRICE - NUM
          $detArr[] = number_format(round(($billing['%ofTurnover']*VAL_VAT_RATE_ADD), 2), 2, '.', '');  //INCLUSIVE PRICE - NUM
          $detArr[] = '" "';  //UNIT - CHAR
          $detArr[] = '1';
          $detArr[] = '0'; //DISCOUNT TYPE
          $detArr[] = '0'; 
          $detArr[] = '"DT001"';
          $detArr[] = '"Document Charge on Turnover"';
          $detArr[] = '4';  //unknown.
          $detArr[] = '""';
          $detArr[] = '"001"';

          $dataArr[] = join(',',$detArr);

          $detArr2 = array();
          $detArr2[] = '"DETAIL"';
          $detArr2[] = '0';
          $detArr2[] = '1';
          $detArr2[] = '0';
          $detArr2[] = '0';
          $detArr2[] = '" "';  //UNIT - CHAR
          $detArr2[] = '1';
          $detArr2[] = '0'; //DISCOUNT TYPE
          $detArr2[] = '0'; 
          $detArr2[] = '"."';
          $detArr2[] = '"Turn Over '. $billing['Turnover'] . ' Rate ' . 	$billing['DebtRate']. '%"' ;
          $detArr2[] = '7';  //unknown.
          $detArr2[] = '""';
          $detArr2[] = '"001"';

          $dataArr[] = join(',',$detArr2);
        }
 
         
        if(abs($billing['WebHosting'])>0){
          $detArr = array();
          $detArr[] = '"DETAIL"';
          $detArr[] = '0';
          $detArr[] = '1';
          $detArr[] = number_format(round($billing['WebHosting'], 2), 2, '.', ''); //SELLING PRICE - NUM
          $detArr[] = number_format(round(($billing['WebHosting']*VAL_VAT_RATE_ADD), 2), 2, '.', '');  //INCLUSIVE PRICE - NUM
          $detArr[] = '" "';  //UNIT - CHAR
          $detArr[] = '1';
          $detArr[] = '0'; //DISCOUNT TYPE
          $detArr[] = '0'; 
          $detArr[] = '"WEBH01"';
          $detArr[] = '"Web Site Hosting"';
          $detArr[] = '4';  //unknown.
          $detArr[] = '""';
          $detArr[] = '"001"';

          $dataArr[] = join(',',$detArr);

          $detArr2 = array();          
          $detArr2[] = '"DETAIL"';
          $detArr2[] = '0';
          $detArr2[] = '1';
          $detArr2[] = '0';
          $detArr2[] = '0';
          $detArr2[] = '" "';  //UNIT - CHAR
          $detArr2[] = '1';
          $detArr2[] = '0'; //DISCOUNT TYPE
          $detArr2[] = '0'; 
          $detArr2[] = '"."';
          $detArr2[] = $billing['Remarks'];
          $detArr2[] = '7';  //unknown.
          $detArr2[] = '""';
          $detArr2[] = '"001"';

          $dataArr[] = join(',',$detArr2);
        }        
        
        if(abs($billing['Domain'])>0){

          $detArr = array();
          $detArr[] = '"DETAIL"';
          $detArr[] = '0';
          $detArr[] = '1';
          $detArr[] = number_format(round($billing['Domain'], 2), 2, '.', ''); //SELLING PRICE - NUM
          $detArr[] = number_format(round(($billing['Domain']*VAL_VAT_RATE_ADD), 2), 2, '.', '');  //INCLUSIVE PRICE - NUM
          $detArr[] = '" "';  //UNIT - CHAR
          $detArr[] = '1';
          $detArr[] = '0'; //DISCOUNT TYPE
          $detArr[] = '0'; 
          $detArr[] = '"DOM001"';
          $detArr[] = '"Domain Registration"';
          $detArr[] = '4';  //unknown.
          $detArr[] = '""';
          $detArr[] = '"001"';

          $dataArr[] = join(',',$detArr);

          $detArr2 = array();
          $detArr2[] = '"DETAIL"';
          $detArr2[] = '0';
          $detArr2[] = '1';
          $detArr2[] = '0';
          $detArr2[] = '0';
          $detArr2[] = '" "';  //UNIT - CHAR
          $detArr2[] = '1';
          $detArr2[] = '0'; //DISCOUNT TYPE
          $detArr2[] = '0'; 
          $detArr2[] = '"."';
          $detArr2[] = $billing['Remarks'];
          $detArr2[] = '7';  //unknown.
          $detArr2[] = '""';
          $detArr2[] = '"001"';

          $dataArr[] = join(',',$detArr2);
        }
        if(abs($billing['WareHouseManage'])>0){

          $detArr = array();
          $detArr[] = '"DETAIL"';
          $detArr[] = '0';
          $detArr[] = '1';
          $detArr[] = number_format(round($billing['WareHouseManage'], 2), 2, '.', ''); //SELLING PRICE - NUM
          $detArr[] = number_format(round(($billing['WareHouseManage']*VAL_VAT_RATE_ADD), 2), 2, '.', '');  //INCLUSIVE PRICE - NUM
          $detArr[] = '" "';  //UNIT - CHAR
          $detArr[] = '1';
          $detArr[] = '0'; //DISCOUNT TYPE
          $detArr[] = '0'; 
          $detArr[] = '"WMS01"';
          $detArr[] = '"WareHouse Management"';
          $detArr[] = '4';  //unknown.
          $detArr[] = '""';
          $detArr[] = '"001"';

          $dataArr[] = join(',',$detArr);

          $detArr2 = array();
          $detArr2[] = '"DETAIL"';
          $detArr2[] = '0';
          $detArr2[] = '1';
          $detArr2[] = '0';
          $detArr2[] = '0';
          $detArr2[] = '" "';  //UNIT - CHAR
          $detArr2[] = '1';
          $detArr2[] = '0'; //DISCOUNT TYPE
          $detArr2[] = '0'; 
          $detArr2[] = '"."';
          $detArr2[] = $billing['Remarks'];
          $detArr2[] = '7';  //unknown.
          $detArr2[] = '""';
          $detArr2[] = '"001"';

          $dataArr[] = join(',',$detArr2);
        }                
        if(abs($billing['Consulting'])>0){

          $detArr = array();
          $detArr[] = '"DETAIL"';
          $detArr[] = '0';
          $detArr[] = '1';
          $detArr[] = number_format(round($billing['Consulting'], 2), 2, '.', ''); //SELLING PRICE - NUM
          $detArr[] = number_format(round(($billing['Consulting']*VAL_VAT_RATE_ADD), 2), 2, '.', '');  //INCLUSIVE PRICE - NUM
          $detArr[] = '" "';  //UNIT - CHAR
          $detArr[] = '1';
          $detArr[] = '0'; //DISCOUNT TYPE
          $detArr[] = '0'; 
          $detArr[] = '"CON001"';
          $detArr[] = '"Consulting"';
          $detArr[] = '4';  //unknown.
          $detArr[] = '""';
          $detArr[] = '"001"';

          $dataArr[] = join(',',$detArr);

          $detArr2 = array();
          $detArr2[] = '"DETAIL"';
          $detArr2[] = '0';
          $detArr2[] = '1';
          $detArr2[] = '0';
          $detArr2[] = '0';
          $detArr2[] = '" "';  //UNIT - CHAR
          $detArr2[] = '1';
          $detArr2[] = '0'; //DISCOUNT TYPE
          $detArr2[] = '0'; 
          $detArr2[] = '"."';
          $detArr2[] = $billing['Remarks'];
          $detArr2[] = '7';  //unknown.
          $detArr2[] = '""';
          $detArr2[] = '"001"';

          $dataArr[] = join(',',$detArr2);

        } 
         if(abs($billing['Development'])>0){

          $detArr = array();
          $detArr[] = '"DETAIL"';
          $detArr[] = '0';
          $detArr[] = '1';
          $detArr[] = number_format(round($billing['Development'], 2), 2, '.', ''); //SELLING PRICE - NUM
          $detArr[] = number_format(round(($billing['Development']*VAL_VAT_RATE_ADD), 2), 2, '.', '');  //INCLUSIVE PRICE - NUM
          $detArr[] = '" "';  //UNIT - CHAR
          $detArr[] = '1';
          $detArr[] = '0'; //DISCOUNT TYPE
          $detArr[] = '0'; 
          $detArr[] = '"DEV001"';
          $detArr[] = '"Development"';
          $detArr[] = '4';  //unknown.
          $detArr[] = '""';
          $detArr[] = '"001"';

          $dataArr[] = join(',',$detArr);

          $detArr2 = array();          
          $detArr2[] = '"DETAIL"';
          $detArr2[] = '0';
          $detArr2[] = '1';
          $detArr2[] = '0';
          $detArr2[] = '0';
          $detArr2[] = '" "';  //UNIT - CHAR
          $detArr2[] = '1';
          $detArr2[] = '0'; //DISCOUNT TYPE
          $detArr2[] = '0'; 
          $detArr2[] = '"."';
          $detArr2[] = $billing['Remarks'];
          $detArr2[] = '7';  //unknown.
          $detArr2[] = '""';
          $detArr2[] = '"001"';

          $dataArr[] = join(',',$detArr2);
        } 
        
         if(abs($billing['Setup'])>0){
          $detArr = array();
          $detArr[] = '"DETAIL"';
          $detArr[] = '0';
          $detArr[] = '1';
          $detArr[] = number_format(round($billing['Setup'], 2), 2, '.', ''); //SELLING PRICE - NUM
          $detArr[] = number_format(round(($billing['Setup']*VAL_VAT_RATE_ADD), 2), 2, '.', '');  //INCLUSIVE PRICE - NUM
          $detArr[] = '" "';  //UNIT - CHAR
          $detArr[] = '1';
          $detArr[] = '0'; //DISCOUNT TYPE
          $detArr[] = '0'; 
          $detArr[] = '"SETUP1"';
          $detArr[] = '"System Set UP"';
          $detArr[] = '4';  //unknown.
          $detArr[] = '""';
          $detArr[] = '"001"';

          $dataArr[] = join(',',$detArr);

          $detArr2 = array();          
          $detArr2[] = '"DETAIL"';
          $detArr2[] = '0';
          $detArr2[] = '1';
          $detArr2[] = '0';
          $detArr2[] = '0';
          $detArr2[] = '" "';  //UNIT - CHAR
          $detArr2[] = '1';
          $detArr2[] = '0'; //DISCOUNT TYPE
          $detArr2[] = '0'; 
          $detArr2[] = '"."';
          $detArr2[] = $billing['Remarks'];
          $detArr2[] = '7';  //unknown.
          $detArr2[] = '""';
          $detArr2[] = '"001"';

          $dataArr[] = join(',',$detArr2);
        } 

        $data = join("\r\n",$dataArr);

   }
        //create file only if there are successful items.
        $filePath = false;
       

          //determine seq.
          $seqFilename = $this->setFilenameFSEQ(($this->filename), $this->principalUid, false, 3, self::setFilenameFSEQ_LenType_PAD);
          if($seqFilename==false){
            BroadcastingUtils::sendAlertEmail("System Error", "Sequence method setFilenameFSEQ failed in ".get_class($this)."!", "Y");
            return $this->errorTO;
          }

          //write physical file
          $filePath = $this->createFile($folder, $seqFilename, $data);  //places file in correct folder
          if($filePath == false){
            BroadcastingUtils::sendAlertEmail("System Error", "Failed to create extract file!", "Y");
            $this->errorTO->type = FLAG_ERRORTO_ERROR;
            $this->errorTO->description = "Failed in ".get_class($this)." on file creation";
            return $this->errorTO;
          }
      /*-------------------------------------------------*/



      echo "Successfully Completed Extract : ".get_class($this)."<br>";

      /*  SUCCESS POINT - 2  */
      $this->errorTO->type = FLAG_ERRORTO_SUCCESS;
      $this->errorTO->description = "Successful";
      return $this->errorTO;

    }
  }

//direct run!
if ($runMe) {
  directRunExtract(__FILE__);
}

?>