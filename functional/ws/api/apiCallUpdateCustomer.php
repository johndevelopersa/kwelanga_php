<?php

    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    include_once($ROOT . $PHPFOLDER . "DAO/db_Connection_Class.php");

    $dbConn = new dbConnect();
    $dbConn->dbConnection();
    

// $callresult = "https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/functional/ws/api/apiCallUpdateCustomer.php";

    $sql = "SELECT trim(fld2) as 'acc'
            FROM .file_upload_temp f
            LEFT JOIN .special_field_details sfd ON sfd.field_uid IN (621) 
                                                 AND trim(sfd.value) = trim(fld2)
            WHERE trim(fld1) = 'customerAccount'
            AND   sfd.entity_uid IS null;";
            
            echo $sql;
            
//    $cusToAdd = $dbConn->dbGetAll($sql);
    
    $sql = "SELECT trim(fld2) as 'val',
                   trim(fld1) as 'fld',
                   trim(fld3) as 'IN',
                   fld30
            FROM file_upload_temp f
            WHERE 1
            and trim(fld1) not in ('(',')', 'Array')
            and fld30 > 1000000  
            limit 200000;";
            
    $cusDetail = $dbConn->dbGetAll($sql);
    $startOutput = 'N';
    foreach($cusDetail as $cRow) {
    	     if($cRow['fld'] == 'customerAccount' && $cRow['IN'] <> NULL) {
    	     	  $startOutput = 'Y';
    	     } elseif($cRow['fld'] == 'requireddata') {
    	     	  $startOutput = 'N';
    	     }
    	     if($startOutput == "Y") {
    	     	   echo $cRow['fld'] . '  ' . $cRow['val'] . '  ' . $cRow['fld30'];
    	         echo "<br>";
    	         
    	         $sql = "UPDATE file_upload_temp g SET g.fld5 = 'U'
                       WHERE g.fld30 = " . $cRow['fld30'];
                       
               $errorTO = $dbConn->processPosting($sql, "");        
    	         $dbConn->dbQuery("commit");
    	     }
    	     

    }
    
    
    echo "<br>*** FIN ****<br>";

/*
    'username'     => 'PrimaPasta',
    'password'     => 'yF!+KssJr-Ca8yM=NX',
    'requireddata' => 'uploadCustomerfile',
    'principalUid' => '450',
    'userEmail'    => 'PrimaAPI@kwelangaonlinesolutions.co.za',
    
    'customerAccount'  => 'ABC123',
    'DeliverName'      => 'ABC WholeSalers',
    'DeliverAddress1'  => '1 SBC Street',
    'DeliverAddress2'  => 'Gardens',
    'DeliverAddress3'  => 'Fine Town',
    'InvoiceName'      => 'ABC WholeSalers Accounts Dept',
    'InvoiceAddress1'  => 'P O Box 12',
    'InvoiceAddress2'  => 'Gardens',
    'InvoiceAddress3'  => 'Fine Town',
    'postCode'         => '1234',
    'vatNumber'        => '4213838383',
    'branch'           => '12321',
    'GLN'              => '6001007835124',
    'coOrdinates'      => '-26.2581462,28.2700144',
    'defaultWarehouse' => 'OBFN',
    'priceList1'       => 'EDLP',
    'priceList1'       => 'DEAL',
    'creditLimit'      => '100000',
    'onHold'           => 'N',
    'customerBalance'  => '50000');
     
     
     // zareenam@stepintime.co.za
     // adnaanm@stepintime.co.za
     // theunis.uys@stepintime.co.za
     // yaseen.karriem@stepintime.co.za
     

$payload = json_encode($data, true);
/*
echo "<h4>Data Array</h4>";
echo "<pre>";
print_r($data);
echo "<br>";
echo "</pre>";
echo "<hr>";

echo "<h4>Json Payload</h4>";
echo $payload;
echo "<br>";
echo "<br>";

echo "<br>";echo "<br>";echo "<br>";echo "<br>";echo "<br>";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/m/e/kKre3CSva3E/chariii.php");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt($ch, CURLOPT_VERBOSE, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$result = curl_exec($ch);

$curlDebug = curl_getinfo($ch);

// echo "<br><pre>";
// print_r(curl_getinfo($ch));

if (curl_errno($ch)) {
    $error_msg = curl_error($ch);
    print_r($error_msg);
}

// echo "<h4>Result</h4>";
print_r($result);

// close cURL resource, and free up system resources
curl_close($ch);
*/

?>