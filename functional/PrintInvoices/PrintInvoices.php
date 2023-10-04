<?php 
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER."properties/Constants.php");
    include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
    include_once($ROOT.$PHPFOLDER."libs/CommonUtils.php");
    include_once($ROOT.$PHPFOLDER.'DAO/PrintInvoicesDAO.php');    
    include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");

    include_once("PrintInvoicesScreens.php");
   
   if (!isset($_SESSION)) session_start() ;
      $userUId      = $_SESSION['user_id'] ;
      $principalId  = $_SESSION['principal_id'] ;
      $wareHouseCde = $_SESSION['depot_id'] ;
      $systemName   = $_SESSION['system_name'] ;
      
     function __construct() {

         global $dbConn;
         $this->dbConn = $dbConn;
         
         $this->errorTO = new ErrorTO;
      }

      //Create new database object
      $dbConn = new dbConnect(); 
      $dbConn->dbConnection();
      
      ?>
      <!DOCTYPE html>
      <HTML>
	        <HEAD>

             <TITLE>Print Invoices</TITLE>
             <link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/kos_standard_screen2.css' rel='stylesheet' type='text/css'>
             <link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/1_kwelanga.css' rel='stylesheet' type='text/css'>
             <script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/jquery.js"></script>
             <script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/dops_global_functions.js"></script>

             <style>
                  td.head1 {font-weight:bold;
                            font-size:17px;text-align:left; 
                            font-family: Calibri, Verdana, Ariel, sans-serif; 
                            padding: 0 150px 0 150px }

                  td.head2 {font-weight:normal;
                            font-size:15px;text-align:left; 
                            font-family: Calibri, Verdana, Ariel, sans-serif; 
                            padding: 0 150px 0 150px }
                
             </style>
          </HEAD>
          
          <?php  
          
//*********************************************************************************************************************************
          if(!isset($_POST['GETTRIPINV'])) {
                $PrintInvoicesScreens = new PrintInvoicesScreens();
                $a = $PrintInvoicesScreens->TripSheetNumber();
           }          
//*********************************************************************************************************************************

      if (isset($_POST['GETTRIPINV'])) {
      	
             $Search = test_input($_POST['SEARCHTP']);
             
             if(trim($Search) != '') {
       	         $PrintInvoicesDAO = new PrintInvoicesDAO($dbConn);
                 $tsInvoices = $PrintInvoicesDAO->GetTripSheetInvoices($Search,$wareHouseCde);
                 if(count($tsInvoices) > 0) {
                       $PrintInvoicesScreens = new PrintInvoicesScreens();
                       $a = $PrintInvoicesScreens->displayInvoices($tsInvoices); 
                 } else { ?>
                       <script type='text/javascript'>parent.showMsgBoxError('No Tripsheet Found')</script>  
                       <?php
                       unset($_POST['GETTRIPINV']);  
                 }
             } else {?>
                   <script type='text/javascript'>parent.showMsgBoxError('Tripsheet Number Cannot be Blank')</script>  
                  <?php
                  unset($_POST['GETTRIPINV']);
             }       
      }
//********************************************************************************************************************************************************************************************************                        
          
      if (isset($_POST['BACK'])) {
            unset($_POST['GETTRIPINV']); 	
      }     
//********************************************************************************************************************************************************************************************************                        
      if (isset($_POST['PRINTINVOICES'])) {
      	
      	  $invList = implode(",",$_POST['INVSELECT']);
      	  
      	  if(count($invList) > 0) { 
      	  	    ?>
      	  	    <script type='text/javascript'>      	  	    
      		            window.open('<?php echo $ROOT.$PHPFOLDER ?>functional/presentations/view/pdf_invoice_print_version.php?DOCIDLIST= <?php echo $invList; ?>');
               
                      parent.showMsgBoxInfo('Invoice Printing Successful')
                </script>
                
                <?php
                
                
                // Insert into Printed Log                
      	  	
                $invoicesPrinted = new PrintInvoicesDAO($dbConn);
                $this->errorTO = $invoicesPrinted->insertIntoDocLog($invList, $userUId);
                
                // Insert into Printed Log                
      	  	
                unset($_POST['GETTRIPINV']);
      	  	
      	  } else {?>
                   <script type='text/javascript'>parent.showMsgBoxError('No Invoices Selected for Printing')</script>  
                  <?php
                  unset($_POST['GETTRIPINV']);
      	  } 	
      }     

//********************************************************************************************************************************************************************************************************                        
 function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  if($data=='') { $data=0; }
  
  return $data ; 

}  
?>    