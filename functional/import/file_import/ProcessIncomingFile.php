<?php 
include_once('ROOT.php'); 
include_once($ROOT.'PHPINI.php');	
include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");


class ProcessIncomingFile {
	    private $dbConn;	
      function __construct($dbConn) {

         global $ROOT, $PHPFOLDER, $dbConn;
         $this->dbConn = $dbConn;
         $this->errorTO = new ErrorTO;
      }	

// ********************************************************************************************************************************************************
      public function processSaucyInvoiceFile($prinId, $f1, $f2, $f3, $f4, $iDate) {
      	
          // Get the SS Invoices
          
          $ProcessFilesDAO = new ProcessFilesDAO($this->dbConn);
          $utresult = $ProcessFilesDAO->manageInvoiceTransactions(mysqli_real_escape_string($this->dbConn->connection, $prinId),
                                                                  mysqli_real_escape_string($this->dbConn->connection, $f1),
                                                                  mysqli_real_escape_string($this->dbConn->connection, $f2),
                                                                  mysqli_real_escape_string($this->dbConn->connection, $f3),
                                                                  mysqli_real_escape_string($this->dbConn->connection, $f4),
                                                                  mysqli_real_escape_string($this->dbConn->connection, $iDate));                
          
          if (count($utresult) == 0) { ?>
                <script type='text/javascript' >parent.showMsgBoxError("Invoices yo Process<BR><BR>")</script> 
                <?php
                 return;	
          }	else {
          	    $success = 'T';
          	
          	    foreach($utresult as $row) { 
                       $ProcessFilesDAO = new ProcessFilesDAO($this->dbConn);
                       $errorTO = $ProcessFilesDAO->updateInvoiceTransactions($row['docUid'], 
                                                                              $row['product_uid'], 
                                                                              $iDate,  
                                                                              $row['fld9'],
                                                                              $row['fld1']);
                                                                              
                      If($this->errorTO->Type <> "S") {
                          $success = 'F';
                      }                                                                 	    	
          	    }
          	    return $success;
          }
      }
// ********************************************************************************************************************************************************

// ********************************************************************************************************************************************************

}
?>