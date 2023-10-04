<?php
      include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
      include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
      include_once($ROOT.$PHPFOLDER."DAO/MiscellaneousDAO.php");
      include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
      include_once($ROOT.$PHPFOLDER."TO/ErrorTO.php");
      include_once($ROOT.$PHPFOLDER."TO/PresentationManagementTO.php");

      $dbConn = new dbConnect();
      $dbConn->dbConnection();

      $presentationManagement = new presentationManagement();
      if(isset($_GET['TYPE'])){$presentationManagement->type = $_GET['TYPE'];}
      if(isset($_GET['FINDNUMBER'])){$presentationManagement->findnumber = $_GET['FINDNUMBER'];}
      if(isset($_GET['DOCTYPE'])){$presentationManagement->doctype = $_GET['DOCTYPE'];}

      if(isset($_GET['PSTORE'])){$presentationManagement->pstore = $_GET['PSTORE'];}
      if(isset($_GET['STARTDATE'])){$presentationManagement->startdate = $_GET['STARTDATE'];}
      if(isset($_GET['ENDDATE'])){$presentationManagement->enddate = $_GET['ENDDATE'];}
      if(isset($_GET['NOOFDOC'])){$presentationManagement->noofdoc = $_GET['NOOFDOC'];}

      $presentationManagement->mobile = (isset($_GET["MOBILE"]) && $_GET["MOBILE"] == 1) ? "MOBILE" : "WEB";

      $presentationManagement->documentSelect();

class presentationManagement{

  public  $mobile = 'WEB',
      $type = 'error',
      $findnumber = '';

  //this handles session values for this presentation type for the which presentation to use.
  public function documentSelect(){

    global $ROOT, $PHPFOLDER;

  if (
      ((!isset($_GET["KEYFROMLINK"])) && (!isset($_POST["KEYFROMLINK"]))) ||
      ((isset($_GET["KEYFROMLINK"])) && ($_GET["KEYFROMLINK"]=="")) ||
      ((isset($_POST["KEYFROMLINK"])) && ($_POST["KEYFROMLINK"]==""))
     ) {
       require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
       } else {
       include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php"); // needed because of access_control commented out
       include_once($ROOT.$PHPFOLDER.'properties/dbSettings.inc');
       }
       include_once($ROOT.$PHPFOLDER."DAO/TransactionDAO.php");
       include_once($ROOT.$PHPFOLDER."DAO/PrincipalDAO.php");
       include_once($ROOT.$PHPFOLDER."DAO/AdministrationDAO.php");
       include_once($ROOT.$PHPFOLDER."DAO/CommonDAO.php");
       include_once($ROOT.$PHPFOLDER."libs/GUICommonUtils.php");
       include_once($ROOT.$PHPFOLDER."properties/Constants.php");
       include_once($ROOT.$PHPFOLDER."TO/PresentationManagementTO.php");

       $postFINDNUMBER="";  //preset.
       if (isset($_GET['FINDNUMBER'])) $postFINDNUMBER = ($_GET['FINDNUMBER']);
       else if (isset($_POST['FINDNUMBER'])) $postFINDNUMBER = ($_POST['FINDNUMBER']);
       
       $postSOURCE=""; 
       if (isset($_GET['CSOURCE'])) $postSOURCE = ($_GET['CSOURCE']);
       else if (isset($_POST['CSOURCE'])) $postSOURCE = ($_POST['CSOURCE']);       

       $postDstatus=""; 
       if (isset($_GET['DSTATUS'])) $postDstatus = ($_GET['DSTATUS']);
       else if (isset($_POST['DSTATUS'])) $postDstatus = ($_POST['DSTATUS']);       

       $postPstore=""; 
       if (isset($_GET['PSTORE'])) $postPstore = ($_GET['PSTORE']);
       else if (isset($_POST['PSTORE'])) $postPstore = ($_POST['PSTORE']);       
       
       $postStartdate=""; 
       if (isset($_GET['STARTDATESTARTDATE'])) $postStartdate = ($_GET['STARTDATE']);
       else if (isset($_POST['STARTDATE'])) $postStartdate = ($_POST['STARTDATE']);       
       
       $postEnddate=""; 
       if (isset($_GET['ENDDATE'])) $postEnd = ($_GET['ENDDATE']);
       else if (isset($_POST['ENDDATE'])) $postEnddate = ($_POST['ENDDATE']);       

       $postEnddate=""; 
       if (isset($_GET['NOOFDOC'])) $noofdoc = ($_GET['NOOFDOC']);
       else if (isset($_POST['NOOFDOC'])) $noofdoc = ($_POST['NOOFDOC']);       


       $postKEYFROMLINK = (isset($_GET["KEYFROMLINK"])) ? $_GET["KEYFROMLINK"] : false; // the user came to this page from an email link - no userlogin necessary
      
       $dbConn = new dbConnect();
       $dbConn->dbConnection();
       
       $adminDAO = new AdministrationDAO($dbConn);
       $principalDAO = new PrincipalDAO($dbConn);

       if (!$postKEYFROMLINK){
            if (!isset($_SESSION)) session_start();
                $userId        = $_SESSION['user_id'];
                $userCategory  = $_SESSION['user_category'];
                $principalId   = $_SESSION['principal_id'];
                $principalName = $_SESSION['principal_name'];
                $principalType = $_SESSION['principal_type'];
                $depotId       = $_SESSION['depot_id'];
       } else {
                $userId        = "";
                $principalId   = "";
                $principalName = "";
                $principalType = "";
                $userCategory  = "P";
                $depotId       = "";
       }
       
   /*  --------------------------------------------------------  *
    *   Select Template           *
    *  -------------------------------------------------------- */
       if (strtoupper($this->type) == "TRIPSHEET") {

            $prO = new PresentationManagementTO();

            $prO->type = (strtoupper($this->type));
            $prO->systemUId = 1;
            $prO->principalUid =$principalId;
            $prO->postFindnumber = mysqli_real_escape_string($dbConn->connection, $this->findnumber);
            $prO->userCategory = $userCategory;
            $prO->depotUId = $depotId;
            $prO->documentTypeUId = "";
            $prO->documentStatusUId = "";
            $prO->platform = "WEB";
            
            $gts = array();

           // Get the display template to use
            $miscDAO = new MiscellaneousDAO($dbConn);
            $gts = $miscDAO->getPresentation($prO);

           if(count($gts)==0){
             echo "ERRROR: No presentation layer found!";
           return;
           }
           // Get the Trip Sheet data
           
           $transactionDAO = new TransactionDAO($dbConn);
           $tsDT = $transactionDAO->getInvoicesbyTripsheetNumber($prO);
                      
           $pathTemplate = $ROOT. $PHPFOLDER . 'functional/presentations/view/'.$gts['display_template_script'];
           if(is_file($pathTemplate))
           include($pathTemplate);
      } elseif (strtoupper($this->type) == "CUSTOMTS294") {     

            $prO = new PresentationManagementTO();

            $prO->type = (strtoupper($this->type));
            $prO->systemUId = 1;
            $prO->principalUid =$principalId;
            $prO->postFindnumber = mysqli_real_escape_string($dbConn->connection, $this->findnumber);
            $prO->userCategory = $userCategory;
            $prO->depotUId = $depotId;
            $prO->documentTypeUId = "";
            $prO->documentStatusUId = "";
            $prO->platform = "WEB";
            
            $gts = array();

           // Get the display template to use
            $miscDAO = new MiscellaneousDAO($dbConn);
            $gts = $miscDAO->getPresentation($prO);

           if(count($gts)==0){
             echo "ERRROR: No presentation layer found!";
           return;
           }
           // Get the Trip Sheet data
           
           $transactionDAO = new TransactionDAO($dbConn);
           $tsDT = $transactionDAO->InvoicesDetailsbyTripsheetNumberAsco($prO);
                      
           $pathTemplate = $ROOT. $PHPFOLDER . 'functional/presentations/view/'.$gts['display_template_script'];
           if(is_file($pathTemplate))
           include($pathTemplate);
      } elseif (strtoupper($this->type) == "CUSTOMNTS294"  || strtoupper($this->type) == "CUSTOMTS216") {     

            $prO = new PresentationManagementTO();

            $prO->type = (strtoupper($this->type));
            $prO->systemUId = 1;
            $prO->principalUid =$principalId;
            $prO->postFindnumber = mysqli_real_escape_string($dbConn->connection, $this->findnumber);
            $prO->userCategory = $userCategory;
            $prO->depotUId = $depotId;
            $prO->documentTypeUId = "";
            $prO->documentStatusUId = "";
            $prO->platform = "WEB";
            
            $gts = array();

           // Get the display template to use
            $miscDAO = new MiscellaneousDAO($dbConn);
            $gts = $miscDAO->getPresentation($prO);

           if(count($gts)==0){
             echo "ERRROR: No presentation layer found!";
           return;
           }
           // Get the Trip Sheet data
           
           $transactionDAO = new TransactionDAO($dbConn);
           $tsDT = $transactionDAO->InvoicesDetailsbyTripsheetNumberProduct($prO);
                      
           $pathTemplate = $ROOT. $PHPFOLDER . 'functional/presentations/view/'.$gts['display_template_script'];
           if(is_file($pathTemplate))
           include($pathTemplate);


      } elseif (strtoupper($this->type) == "CUSTOMTS305") {     

            $prO = new PresentationManagementTO();

            $prO->type = (strtoupper($this->type));
            $prO->systemUId = 1;
            $prO->principalUid =$principalId;
            $prO->postFindnumber = mysqli_real_escape_string($dbConn->connection, $this->findnumber);
            $prO->userCategory = $userCategory;
            $prO->depotUId = $depotId;
            $prO->documentTypeUId = "";
            $prO->documentStatusUId = "";
            $prO->platform = "WEB";
            
            $gts = array();

           // Get the display template to use
            $miscDAO = new MiscellaneousDAO($dbConn);
            $gts = $miscDAO->getPresentation($prO);

           if(count($gts)==0){
             echo "ERRROR: No presentation layer found!";
           return;
           }
           // Get the Trip Sheet data
           
           $transactionDAO = new TransactionDAO($dbConn);
           $tsDT = $transactionDAO->getInvoicesbyTripsheetNumber($prO);
                      
           $pathTemplate = $ROOT. $PHPFOLDER . 'functional/presentations/view/'.$gts['display_template_script'];
           if(is_file($pathTemplate))
           include($pathTemplate);
      } elseif (strtoupper($this->type) == "CUSTOMTSNEL") {     

            $prO = new PresentationManagementTO();

            $prO->type = (strtoupper($this->type));
            $prO->systemUId = 1;
            $prO->principalUid =$principalId;
            $prO->postFindnumber = mysqli_real_escape_string($dbConn->connection, $this->findnumber);
            $prO->userCategory = $userCategory;
            $prO->depotUId = $depotId;
            $prO->documentTypeUId = "";
            $prO->documentStatusUId = "";
            $prO->platform = "WEB";
            
            $gts = array();

           // Get the display template to use
            $miscDAO = new MiscellaneousDAO($dbConn);
            $gts = $miscDAO->getPresentation($prO);

           if(count($gts)==0){
             echo "ERRROR: No presentation layer found!";
           return;
           }
           // Get the Trip Sheet data
           
           $transactionDAO = new TransactionDAO($dbConn);
           $tsDT = $transactionDAO->getInvoicesbyTripsheetNumber($prO);
                      
           $pathTemplate = $ROOT. $PHPFOLDER . 'functional/presentations/view/'.$gts['display_template_script'];
           if(is_file($pathTemplate))
           include($pathTemplate);

      } elseif (strtoupper($this->type) == "BPICKLIST") {   

            $prO = new PresentationManagementTO();

            $prO->type = (strtoupper($this->type));
            $prO->systemUId = 1;
            $prO->principalUid =$principalId;
            $prO->postFindnumber = mysqli_real_escape_string($dbConn->connection, $this->findnumber);
            $prO->userCategory = $userCategory;
            $prO->depotUId = $depotId;
            $prO->documentTypeUId = "";
            $prO->documentStatusUId = "";
            $prO->platform = "WEB";
            
            $gts = array();

           // Get the display template to use
            $miscDAO = new MiscellaneousDAO($dbConn);
            $gts = $miscDAO->getPresentation($prO);

           if(count($gts)==0){
             echo "ERRROR: No presentation layer found!";
           return;
           }
           // Get the Trip Sheet data
           
           $transactionDAO = new TransactionDAO($dbConn);
           $psDT = $transactionDAO->getDocumentPickingDetail($prO);
           
           $transactionDAOH = new TransactionDAO($dbConn);
           $psHD = $transactionDAOH->getDocumentPickingHeader($prO);           
                     
           $pathTemplate = $ROOT. $PHPFOLDER . 'functional/presentations/view/'.$gts['display_template_script'];
           if(is_file($pathTemplate))
           include($pathTemplate);

      } elseif (strtoupper($this->type) == "WAYBILL") {     

            $prO = new PresentationManagementTO();

            $prO->type = (strtoupper($this->type));
            $prO->systemUId = 1;
            $prO->principalUid =$principalId;
            $prO->postFindnumber = mysqli_real_escape_string($dbConn->connection, $this->findnumber);
            $prO->userCategory = $userCategory;
            $prO->depotUId = $depotId;
            $prO->documentTypeUId = "";
            $prO->documentStatusUId = "";
            $prO->platform = "WEB";
            
            $gts = array();

           // Get the display template to use
            $miscDAO = new MiscellaneousDAO($dbConn);
            $gts = $miscDAO->getPresentation($prO);

           if(count($gts)==0){
             echo "ERRROR: No presentation layer found!";
           return;
           }
           // Get the Trip Sheet data
           
           $transactionDAO = new TransactionDAO($dbConn);
           $mfWB = $transactionDAO->getWayBillsToPrintUseDocument($prO);

                     
           $pathTemplate = $ROOT. $PHPFOLDER . 'functional/presentations/view/'.$gts['display_template_script'];
           if(is_file($pathTemplate))
           include($pathTemplate); 
      } elseif (strtoupper($this->type) == "PAYMENT") {     

            $prO = new PresentationManagementTO();

            $prO->type = (strtoupper($this->type));
            $prO->systemUId = 1;
            $prO->principalUid =$principalId;
            $prO->postFindnumber = mysqli_real_escape_string($dbConn->connection, $this->findnumber);
            $prO->userCategory = $userCategory;
            $prO->depotUId = $depotId;
            $prO->documentTypeUId = "";
            $prO->documentStatusUId = "";
            $prO->platform = "WEB";
            
            $gts = array();

           // Get the display template to use
            $miscDAO = new MiscellaneousDAO($dbConn);
            $gts = $miscDAO->getPresentation($prO);

           if(count($gts)==0){
             echo "ERRROR: No presentatiddon layer found!";
           return;
           }
           
           $transactionDAO = new TransactionDAO($dbConn);
           $mfT = $transactionDAO->getDocumentPaymentDetails($prO);
                    
           $pathTemplate = $ROOT. $PHPFOLDER . 'functional/presentations/view/'.$gts['display_template_script'];
           if(is_file($pathTemplate))
           include($pathTemplate); 
      } elseif (strtoupper($this->type) == "PAYMENTMULT") {     

            $prO = new PresentationManagementTO();

            $prO->type = (strtoupper($this->type));
            $prO->systemUId = 1;
            $prO->principalUid =$principalId;
            $prO->postFindnumber = mysqli_real_escape_string($dbConn->connection, $this->findnumber);
            $prO->userCategory = $userCategory;
            $prO->depotUId = $depotId;
            $prO->documentTypeUId = "";
            $prO->documentStatusUId = "";
            $prO->platform = "WEB";
            
            $gts = array();

           // Get the display template to use
            $miscDAO = new MiscellaneousDAO($dbConn);
            $gts = $miscDAO->getPresentation($prO);

           if(count($gts)==0){
             echo "ERRROR: No presentation layer found!";
           return;
           }
           
           $pathTemplate = $ROOT. $PHPFOLDER . 'functional/presentations/view/'.$gts['display_template_script'];
           if(is_file($pathTemplate))
           include($pathTemplate); 

      } elseif (strtoupper($this->type) == "CRSUMMARY")   {     

            $prO = new PresentationManagementTO();

            $prO->type = (strtoupper($this->type));
            $prO->systemUId = 1;
            $prO->principalUid =$principalId;
            $prO->postFindnumber = mysqli_real_escape_string($dbConn->connection, $this->findnumber);
            $prO->userCategory = $userCategory;
            $prO->depotUId = $depotId;
            $prO->documentTypeUId = "";
            $prO->documentStatusUId = "";
            $prO->platform = "WEB";
            
            $gts = array();

           // Get the display template to use
            $miscDAO = new MiscellaneousDAO($dbConn);
            $gts = $miscDAO->getPresentation($prO);

           if(count($gts)==0){
             echo "ERRROR: No presentatiddon layer found!";
           return;
           }
           
           $transactionDAO = new TransactionDAO($dbConn);
           $mfCSD = $transactionDAO->getCustomerRecordsDetail($principalId,$postPstore,$this->enddate,$this->startdate);
           if (sizeof($mfCSD)==0) { 
           ?>
               <script type='text/javascript' >parent.showMsgBoxError("No Customer Invoices found for this period<BR><BR>")</script>
           <?php 
                 return;   		
           }
           $pathTemplate = $ROOT. $PHPFOLDER . 'functional/presentations/view/'.$gts['display_template_script'];
           if(is_file($pathTemplate))
           include($pathTemplate); 
      } elseif (strtoupper($this->type) == "PAYMEwNTTOMULT") {     

            $prO = new PresentationManagementTO();

            $prO->type = (strtoupper($this->type));
            $prO->systemUId = 1;
            $prO->principalUid =$principalId;
            $prO->postFindnumber = mysqli_real_escape_string($dbConn->connection, $this->findnumber);
            $prO->userCategory = $userCategory;
            $prO->depotUId = $depotId;
            $prO->documentTypeUId = "";
            $prO->documentStatusUId = "";
            $prO->platform = "WEB";
            
            $gts = array();

           // Get the display template to use
            $miscDAO = new MiscellaneousDAO($dbConn);
            $gts = $miscDAO->getPresentation($prO);

           if(count($gts)==0){
             echo "ERRROR: No presentation layer found!";
           return;
           }
           
           $pathTemplate = $ROOT. $PHPFOLDER . 'functional/presentations/view/'.$gts['display_template_script'];
           if(is_file($pathTemplate))
           include($pathTemplate); 
      } elseif (strtoupper($this->type) == "DETAILEDLEDGER") {
      	
            $prO = new PresentationManagementTO();

            $prO->type = (strtoupper($this->type));
            $prO->systemUId = 1;
            $prO->principalUid =$principalId;
            $prO->postFindnumber = '';
            $prO->userCategory = $userCategory;
            $prO->depotUId = $depotId;
            $prO->documentTypeUId = "";
            $prO->documentStatusUId = "";
            $prO->platform = "WEB";
            
            $gts = array();

           // Get the display template to use
            $miscDAO = new MiscellaneousDAO($dbConn);
            $gts = $miscDAO->getPresentation($prO);

           if(count($gts)==0){
             echo "ERRROR: No presentation layer found!";
           return;
           }
           
           $_GET['USERID'] = $userId;
           $_GET['USERCATEGORY'] = $userCategory; 
            
           $pathTemplate = $ROOT. $PHPFOLDER . 'functional/presentations/view/'.$gts['display_template_script'];
           if(is_file($pathTemplate))
           include($pathTemplate); 
      } elseif (strtoupper($this->type) == "RICHDOC") {
            
            $miscInfoDAO = new MiscellaneousDAO($dbConn);
            $its = $miscInfoDAO->getDocumentInfo(mysqli_real_escape_string($dbConn->connection, $this->findnumber),mysqli_real_escape_string($dbConn->connection, $postSOURCE));

            $prO = new PresentationManagementTO();
 
            $prO->type = (strtoupper($this->type));
            $prO->systemUId = 1;
            $prO->principalUid =$principalId;
            $prO->postFindnumber = ($its[0]['document_master_uid'] );
            $prO->userCategory = $userCategory;
            $prO->depotUId = $depotId;
            $prO->documentTypeUId = ($its[0]['document_type_uid'] );
            $prO->documentStatusUId = ($its[0]['document_status_uid'] );
            $prO->platform = "WEB";
            $source =  mysqli_real_escape_string($dbConn->connection, $postSOURCE);   
            
            $gts = array();
            // Get the display template to use
            $miscDAO = new MiscellaneousDAO($dbConn);
            $gts = $miscDAO->getPresentation($prO); 

           if(count($gts)==0){
             echo "ERRROR: No presentation layer found!";
           return;
           }
           
           $_GET['USERID'] = $userId;
           $_GET['PRINCIPALID'] = $principalId;
           $_GET['DOCMASTID'] = $prO->postFindnumber;
           $_GET['OUTPUTTYP'] = "print";
           $_GET['CSOURCE'] = $postSOURCE;
           $_GET['USERCATEGORY'] = $userCategory; 
            
           $pathTemplate = $ROOT. $PHPFOLDER . 'functional/presentations/view/'.$gts['display_template_script'];
           if(is_file($pathTemplate))
           include($pathTemplate); 
      
      } else {
      	

           $miscInfoDAO = new MiscellaneousDAO($dbConn);
           $its = $miscInfoDAO->getDocumentInfo(mysqli_real_escape_string($dbConn->connection, $this->findnumber),mysqli_real_escape_string($dbConn->connection, $postSOURCE));
         
            if ($its[0]['principal_uid'] == 380) {
                $principalId = 380;
            }
            
            if ($userCategory == 'P') {
                $depotId = $its[0]['depot_uid'];            	
            }
         
            $prO = new PresentationManagementTO();
 
            $prO->type = (strtoupper($its[0]['description']));
            $prO->systemUId = 1;
            $prO->principalUid =$principalId;
            $prO->postFindnumber = ($its[0]['document_master_uid'] );
            $prO->userCategory = $userCategory;
            $prO->depotUId = $depotId;
            $prO->documentTypeUId = ($its[0]['document_type_uid'] );
            $prO->documentStatusUId = ($its[0]['document_status_uid'] );
            $prO->platform = "WEB";
            $source =  mysqli_real_escape_string($dbConn->connection, $postSOURCE);     	
      	
            // Get the display template to use
//        echo "<pre>";
//        print_r($prO); 
//        echo "</pre>";          
             
           $miscDAO = new MiscellaneousDAO($dbConn);
           $gts = $miscDAO->getPresentation($prO);

           if(count($gts)==0){
             echo "---ERRROR: No presentation layer found!  " . $source;
             echo "<br>";
  print_r($prO);
     
     
             return;
           }
           
           $_GET['USERID'] = $userId;
           $_GET['PRINCIPALID'] = $principalId;
           $_GET['DOCMASTID'] = $prO->postFindnumber;
           $_GET['OUTPUTTYP'] = "print";
           $_GET['CSOURCE'] = $postSOURCE;
           $_GET['USERCATEGORY'] = $userCategory; 
           
        
           $pathTemplate = $ROOT. $PHPFOLDER . 'functional/presentations/view/'.$gts['display_template_script'];
          // echo $pathTemplate;
          //echo "<br><br><br><br>" ;             
          //   echo   $pathTemplate;
           

// file_put_contents("C:/inetpub/wwwroot/systems/kwelanga_system/kwelanga_php/log/tempp.txt", $gts['display_template_script']);

           if(is_file($pathTemplate))
           include($pathTemplate);
          /*  --------------------------------------------------------  */
      }
  }
}  

?>