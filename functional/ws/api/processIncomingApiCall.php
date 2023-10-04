<?php
// "https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/functional/ws/api/apiCallBase.php";
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER."properties/Constants.php");
include_once($ROOT.$PHPFOLDER."properties/ServerConstants.php");
include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT . $PHPFOLDER . 'DAO/ApiDAO.php');

$dbConn = new dbConnect();
$dbConn->dbConnection();

$data = file_get_contents('php://input');

// Only Uncomment this live for Debugging
// file_put_contents( "C:/inetpub/wwwroot/systems/kwelanga_system/ftp/outerjoin/extracts/sql.txt", $data . "\n\n", FILE_APPEND); 

$JSON = json_decode($data, true);



$newApiDAO = new ApiDAO($dbConn);
$uCred = $newApiDAO->getVendorUser($JSON['username']);

$uCredStr = trim($uCred[0]['username']) . trim($uCred[0]['password']);

//echo "<br>";
$upayLoadStr = trim($JSON['username']) . trim($JSON['password']);

if (strcmp($uCredStr, $upayLoadStr) !== 0) {  // make sure you setup a password specifically for each client individually
		echo json_encode( [
												"resultStatus"=>"E",
												"ResultCode"    =>'701',
												"resultMessage"=>"Sorry, incorrect credentials supplied"
											] );
		exit; // !! NB !!
} else {
	      
	     $newApiDAO = new ApiDAO($dbConn);
       $reqData   = $newApiDAO->getRequiredDataProcessing($JSON['requireddata']);
       
       if(count($reqData) != 1) {
       	     echo json_encode( [
                                "resultStatus"  =>'E',
                                "ResultCode"    =>'700' ,
                                "ResultMessage"=>"Sorry, Request is not recognised"
                                ] );
               
                exit; // !! NB !!
       } elseif($reqData[0]['script_to_run']== NULL || trim($reqData[0]['script_to_run']) == '' || trim($reqData[0]['status']) == 'D') {
       	     echo json_encode( [
                                "resultStatus"  =>'E',
                                "ResultCode"    =>'716' ,
                                "ResultMessage"=>"No Query Script Found"
                                ] );
               
                exit; // !! NB !!
       }	else {
       	
       	      $p1 = $p2 = $p3 = $p4 = $p5 = $p6 = '';
       	      $pCnt = 1;
       	      
       	      foreach(explode(',',trim($reqData[0]['req_parameters']) ) AS $pRow) {
       	      	    
       	      	    if($pCnt == 1) {
       	          	   $p1 = $JSON[$pRow];
       	            }
        	      	    if($pCnt == 2) {
       	          	   $p2 = $JSON[$pRow];
       	            }
       	      	    if($pCnt == 3) {
       	          	   $p3 = $JSON[$pRow];
       	            }
        	      	    if($pCnt == 4) {
       	          	   $p4 = $JSON[$pRow];
       	            }      	      	
       	      	    if($pCnt == 5) {
       	          	   $p5 = $JSON[$pRow];
       	            }
        	      	  if($pCnt == 6) {
       	          	   $p6 = $JSON[$pRow];
       	            }
       	      	    $pCnt++;
       	      }
       	      
       	      $fQuery = trim($reqData[0]['script_to_run']);
       	      
       	      $newApiDAO = new ApiDAO($dbConn);
              $extData   = $newApiDAO->$fQuery($p1,
                                               $p2,
                                               $p5,
                                               $p4,
                                               $p6,
                                               $p6);
                                                                                
             if(count($extData)==0) {
                  echo json_encode( [
                                     "resultStatus"  =>'E',
                                     "ResultCode"    =>'716' ,
                                     "ResultMessage"=>"No Data Returned From Query"
                                    ] );
               
                  exit; // !! NB !!
             } else {
             	      $returnArr = [];
             	      foreach($extData AS $dRow) { 
             	      	      $detLineArr = [];
             	      	      foreach(explode(',',trim($reqData[0]['json_columns']) ) AS $pRow) {
                                 $detLineArr[$pRow] = $dRow[$pRow];
             	      	      }
             	      	      array_push($returnArr, $detLineArr);
             	      }
             	      
             	      echo json_encode( [
                                       "resultStatus" =>'S',
                                       "ResultCode"    =>'000' ,
                                       "resultMessage"=>"Successful",
                                       "data" => $returnArr
                                      ] );  	
             } 
       }
}
