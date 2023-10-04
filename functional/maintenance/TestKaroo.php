<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER."properties/Constants.php");
    include_once($ROOT.$PHPFOLDER."DAO/PostCreateTrackingDAO.php");
		    
    if (!isset($_SESSION)) session_start() ;
    $userUId     = $_SESSION['user_id'] ;
    
    //Create new database object
    $dbConn  = new dbConnect(); 
    $dbConn->dbConnection();
    $errorTO = new ErrorTO;
    
    // Get Warehouses and Chains
    
    $PostCreateTrackingDAO = new PostCreateTrackingDAO($dbConn);
    $wCH = $PostCreateTrackingDAO->getConsolidationWarehousesChains(365);
    
    echo "HH";
    
    if(count($wCH) > 0) {
    	   if(trim($wCH[0]['consolidate_by_warehouses']) <> '' && trim($wCH[0]['consolidate_by_chains']) <> '' ) {
    	   	
             $whArray = explode(',', $wCH[0]['consolidate_by_warehouses']);
             $chArray = explode(',', $wCH[0]['consolidate_by_chains']);
             
             foreach($whArray as $whrow) {
             	   foreach($chArray as $chrow) {
             	      $PostCreateTrackingDAO = new PostCreateTrackingDAO($dbConn);
                    $rPW = $PostCreateTrackingDAO->CreateConsolidatedTransaction(365, $whrow, $chrow, $wCH[0]['consolidated_chain'], $wCH[0]['consolidated_warehouse']) ;	
             	   	
             	   	
             	   	  echo "<br>";
             	      echo($whrow);
             	      echo "<br>"; 	
             	   	  echo($chrow);
             	   	  echo "<br>";
             	   	  echo $wCH[0]['consolidated_warehouse'] ;
             	   	  echo "<br>";
             	   	  
             	   	  echo $wCH[0]['consolidated_chain'] ;	
             	   	  
             	   	   echo "<br>";	
             	   }
             	}
    	   	
    	   }
    }


 ?> 
