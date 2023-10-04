<?php
 include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
 include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
 include_once ($ROOT.$PHPFOLDER."DAO/MaintenanceDAO.php");	 
 
      //Create new database object
      $dbConn = new dbConnect(); 
      $dbConn->dbConnection(); 
      
      $MaintenanceDAO = new MaintenanceDAO($dbConn);
      $seInsert = $MaintenanceDAO->checkNotificationParams(); 
      
      foreach($seInsert as $row) {
      	
      	$p1 = $p2 = $p3 = $p4 = $p5 = $p6 = $p7 = null;
      	
      	parse_str($row['additional_parameter_string']);
      	
      	$MaintenanceDAO = new MaintenanceDAO($dbConn);
        $seupdate = $MaintenanceDAO->updateNotificationParams($row['uid'], $p1, $p2, $p3, $p4, $p5, $p6, $p7); 
        
      }

?>