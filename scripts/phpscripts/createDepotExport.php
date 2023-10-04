<?php


include_once('ROOT.php');
include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER.'properties/dbSettings.inc');
include_once($ROOT.$PHPFOLDER.'DAO/ExportDAO.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER.'DAO/PostTransactionDAO.php');
include_once($ROOT.$PHPFOLDER.'functional/export/adaptor/AdaptorDocumentExport.php');  //depot export adaptors.
include_once($ROOT.$PHPFOLDER."DAO/PostTransactionDAO.php");
include_once($ROOT.$PHPFOLDER.'TO/PostingOrderTO.php');
include_once($ROOT.$PHPFOLDER.'TO/PostingOrderDetailTO.php');




?>

<br><br><br><br>
<div align="center">
<div style="background:#efefef;border:1px solid #ccc;padding:20px;width:250px;">
<form method="POST">
  Order Sequence Number:<br>
  <input type="text" value="" name="orderSeq"><br>
  <input type="submit" value="Submit" name="submit">
</form>
</div>
</div>
<?php

if(isset($_POST['orderSeq'])){

  $orderSeqArr = explode(',', $_POST['orderSeq']);

  foreach ($orderSeqArr as $orderSeq){
    $orderSeq = trim($orderSeq);
  if(!is_numeric($orderSeq)){
    echo 'Failed: Seq must be numeric!';
  } else {

    $dbConn = new dbConnect();
    $dbConn->dbConnection();

    $hRowArr = getAll("SELECT m.uid as muid, m.*, h.* from document_master m, document_header h where m.uid = h.document_master_uid and m.order_sequence_no = '".$orderSeq."'");

    if(!count($hRowArr)>0){
      echo 'Failed: No Document Found!';
    } else {
      if(count($hRowArr)!=1){
        echo 'Failed: Multiple Documents Matched!';
      } else {

        echo '<pre>';
        $dRowsArr = getAll("SELECT d.*, p.product_code, p.product_description from document_detail d, principal_product p where d.document_master_uid = '".$hRowArr[0]['muid']."' and d.product_uid = p.uid order by d.uid"); // order by important, eg. for CandyTops
        $ohRowArr = getAll("SELECT o.*, oh.ws_unique_creator_id
														from orders o
																		 left join orders_holding oh on o.principal_uid = oh.principal_uid and
																																		o.order_sequence_no=oh.order_sequence_number
														where o.order_sequence_no = '".$orderSeq."'");
        $odRowArr = getAll("SELECT * from orders_detail where orders_uid = '".$ohRowArr[0]['uid']."'");
        $ohdRowArr = getAll("SELECT ohd.* from orders_holding oh, orders_holding_detail ohd where oh.uid = ohd.orders_holding_uid and oh.order_sequence_number = '".$orderSeq."'");
        $postingOrderTO = new PostingOrderTO;
        $postingOrderTO->storeChainUId = $hRowArr[0]['principal_store_uid'];
        $postingOrderTO->principalUId = $hRowArr[0]['principal_uid'];
        $postingOrderTO->orderNumber = $ohRowArr[0]['order_number'];
        $postingOrderTO->documentNumber = $hRowArr[0]['document_number'];
        $postingOrderTO->orderSequenceNo = $hRowArr[0]['order_sequence_no'];
        $postingOrderTO->processedDepotUId = $hRowArr[0]['depot_uid'];
        $postingOrderTO->deliveryInstructions = $ohRowArr[0]['delivery_instructions'];
        $postingOrderTO->documentDate = $hRowArr[0]['order_date'];
        $postingOrderTO->deliveryDate = $hRowArr[0]['delivery_date'];
        $postingOrderTO->batchGUID = '';
        $postingOrderTO->captureUserUId = 0;
        $postingOrderTO->deleted = 0;
        $postingOrderTO->ediCreated = "N";
        $postingOrderTO->ediFileName = $hRowArr[0]['incoming_file'];
        $postingOrderTO->incomingFileName = $hRowArr[0]['incoming_file'];
        $postingOrderTO->documentType = $hRowArr[0]['document_type_uid'];
        $postingOrderTO->confirmOption = '';
        $postingOrderTO->dataSource = $hRowArr[0]['data_source'];
        $postingOrderTO->clientDocumentNumber = $ohRowArr[0]['client_document_number'];
        $postingOrderTO->uniqueCreatorId = $ohRowArr[0]['ws_unique_creator_id'];
        
        //var_Dump($dRowsArr);
          echo '<hr>';
          //var_Dump($odRowArr);

        foreach($dRowsArr as $key => $dRow) {

          /*
          if($dRow['product_uid'] != $odRowArr[$key]['product_uid']){
            echo 'ERROR in detail lines....';
            break;
          }*/

          //echo $key;
          //var_Dump($dRow);
          //var_dump($odRowArr[$key]);
          //die();



          $postingOrderDetailTO = new PostingOrderDetailTO;

          $postingOrderDetailTO->productUId = $dRow['product_uid'];
          $postingOrderDetailTO->lineNo = $odRowArr[$key]['line_no'];
          $postingOrderDetailTO->pageNo = $odRowArr[$key]['page_no'];
          $postingOrderDetailTO->clientLineNo = $odRowArr[$key]['client_line_no'];
          $postingOrderDetailTO->quantity = $dRow['ordered_qty'];

          $postingOrderDetailTO->listPrice = $dRow['selling_price'];
          $postingOrderDetailTO->discountValue = $dRow['discount_value'];
          $postingOrderDetailTO->discountReference = $dRow['Discount_reference'];
          $postingOrderDetailTO->nettPrice = $dRow['net_price'];
          $postingOrderDetailTO->extPrice = $dRow['extended_price'];
          $postingOrderDetailTO->vatAmount = $dRow['vat_amount'];
          $postingOrderDetailTO->productVatRate = $dRow['vat_rate'];
          $postingOrderDetailTO->totPrice = $dRow['total'];

          $postingOrderDetailTO->productCode = $dRow["product_code"];
          $postingOrderDetailTO->productDescription = $dRow["product_description"];

          $ohd = findOHD($postingOrderDetailTO->productUId, $postingOrderDetailTO->quantity);
          if (count($ohd)>1) {
            echo "Could not lookup OHD as more than one row returned";
            return;
          } else if (count($ohd)==1) {
            $postingOrderDetailTO->mass = $ohd[0]["mass"];
            $postingOrderDetailTO->volume = $ohd[0]["volume"];
          }

          //$postingOrderDetailTO->priceOverrideValue = $arrOVERRIDEPRICE[$i];

          $postingOrderTO->detailArr[] = $postingOrderDetailTO;
        }

        $adaptorDocEx = new AdaptorDocumentExport($dbConn);
        $exportResult = $adaptorDocEx->generateExport($postingOrderTO);  //self contained error notifications

        if($exportResult==true){
          echo '<h2>EXTRACTED ORDER!</h2>';
        } else {
          echo 'failed to create extract!';
        }
      }
    }
  }


  }


}



function getAll($query){

  global $dbConn;
  $rArr = array();
  $dbConn->dbQuery($query);
  while($row = mysqli_fetch_array($dbConn->dbQueryResult,MYSQLI_ASSOC)){
    $rArr[] = $row;
  }
  return $rArr;

}

function findOHD($ppUId, $qty) {
  global $ohdRowArr;

  $arr = array();
  // return every row that matches so errors can be checked - for time being if prods are duplicated then qty helps iron that out somewhat
  foreach ($ohdRowArr as $r) {
    if (($r["principal_product_uid"]==$ppUId) && ($r["quantity"]==$qty)) {
      $arr[]=$r;
    }
  }
  return $arr;
}