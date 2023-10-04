<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER."properties/Constants.php");
    include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
    include_once($ROOT.$PHPFOLDER."libs/CommonUtils.php");
    include_once($ROOT.$PHPFOLDER.'DAO/RvlManageDAO.php'); 
    include_once('RepMaintenanceScreens.php');     
    
    if (!isset($_SESSION)) session_start() ;
      $userUId     = $_SESSION['user_id'] ;
      $principalId = $_SESSION['principal_id'] ;
      $systemId    = $_SESSION["system_id"];
      $systemName  = $_SESSION['system_name'];
      $depotId     = $_SESSION['depot_id'] ;

      //Create new database object
      $dbConn = new dbConnect(); 
      $dbConn->dbConnection();
      $errorTO = new ErrorTO;

?>  
<!DOCTYPE html>
<HTML>
	<HEAD>

		<TITLE>Principal - Warehouse Selection</TITLE>

		<link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/1_kwelanga.css' rel='stylesheet' type='text/css'>
		<link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/kos_standard_screen.css' rel='stylesheet' type='text/css'>
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
                
      td.det1  {border-style:none; 
                text-align: left; 
                font-weight: bold; 
                font-size: 13px;
                padding: 0 150px 0 150px  }
    	
    	</style>

		</HEAD>
    <?php

    if (isset($_POST['CANFORM'])) {
          return;    
    }
    
     if(isset($_POST['UPDTRAN'])) { 
    	
        if (isset($_POST["TNAME"]))   $postTNAME   = ($_POST["TNAME"]);   else $postTNAME     = '';
        if (isset($_POST["TUID"]))    $postTUID    = test_input($_POST["TUID"]);    else $postTUID      = '';
    	  if (isset($_POST["NSTATUS"])) $postNSTATUS = test_input($_POST["NSTATUS"]); else $postNSTATUS   = '';
    	  if (isset($_POST["REMAIL"]))  $postRMAIL   =  test_input($_POST["REMAIL"]); else $postRMAIL     = '';  

        if(strlen($postTNAME) > 3) {
        	
        	    if (filter_var($postRMAIL, FILTER_VALIDATE_EMAIL)) {
                    $rvlManagmentDAO = New RvlManagmentDAO($dbConn);
                    $errorTO = $rvlManagmentDAO->updatePsr($postTUID, $postTNAME, $postNSTATUS, $postRMAIL);

                    if ($errorTO->type==FLAG_ERRORTO_SUCCESS) { ?>
                         <script type='text/javascript'>parent.showMsgBoxInfo('Rep Update Successfully')</script>  
                         <?php
                          unset($_POST['MODSREP']);  
                          unset($_POST['ADDSREP']);  
                          unset($_POST['ADDSLREP']); 
                          unset($_POST['MODSLREP']); 
                    } else { ?>
                         <script type='text/javascript'>parent.showMsgBoxError('Rep UpdateFailed <br><br> Contact Kwelanga Support')</script>
                         <?php
                          unset($_POST['MODSREP']);  
                          unset($_POST['ADDSREP']);  
                          unset($_POST['ADDSLREP']); 
                          unset($_POST['MODSLREP']); 
                    }
              } else {?>
                          <script type='text/javascript'>parent.showMsgBoxError('Email Address not valid <br><br> Try Again')</script>
                          <?php	
                          unset($_POST['MODSREP']);  
                          unset($_POST['ADDSREP']);  
                          unset($_POST['ADDSLREP']); 
                          unset($_POST['MODSLREP']); 
               }              
        } else { ?>
             <script type='text/javascript'>parent.showMsgBoxError('Rep Name Blank or too Short (Minimum 4) <br><br> Try Again')</script>
             <?php	
             unset($_POST['MODSREP']);  
             unset($_POST['ADDSREP']);  
             unset($_POST['ADDSLREP']); 
             unset($_POST['MODSLREP']); 
        }
    }
    
    if(isset($_POST['SELMOD'])) {
    	
            $repName = trim(substr($_POST["SELMOD"],strpos($_POST["SELMOD"],"-") + 1,50));
          	$repUid = trim(substr($_POST["SELMOD"],0,strpos($_POST["SELMOD"],"-")));
          	
            $rvlManagmentDAO = New RvlManagmentDAO($dbConn);
            $mfDD = $rvlManagmentDAO->getRvlSingleRep($repUid);
            
            $repMaintenanceScreens = new repMaintenanceScreens();
            $aa = $repMaintenanceScreens->modifySelected($mfDD[0]['first_name'], $mfDD[0]['uid'], $mfDD[0]['status'], $mfDD[0]['email_addr']) ;
     }

    if (isset($_POST['MODSLREP']) || isset($_POST["SUBMITFILTER"])  ) { 

         if (isset($_POST["STATUS"])) $postStat  = test_input($_POST["STATUS"]);  else $postStat = '';
         if (isset($_POST["TRUID"]))  $postTrUid = test_input($_POST["TRUID"]);  else $postTrUid = '';

         $rvlManagmentDAO = New RvlManagmentDAO($dbConn);
         $mfDD = $rvlManagmentDAO->getRvlPrincipalReps($principalId, $postStat, $postTrUid);

         $repMaintenanceScreens = new repMaintenanceScreens();
         $aa = $repMaintenanceScreens->repFilter($mfDD)  ;

    }
    if (isset($_POST['ADDNEWREP'])) {
    	
        if (isset($_POST["TREP"]))   $postTNAME = test_input($_POST["TREP"]); else $postTNAME  = '';
        if (isset($_POST["RMAIL"]))  $postRMAIL =  test_input($_POST["RMAIL"]);   else $postRMAIL   = ''; 
        
        if(strlen($postTNAME) > 3) {
        	
        	     if (filter_var($postRMAIL, FILTER_VALIDATE_EMAIL)) {
                     $rvlManagmentDAO = New RvlManagmentDAO($dbConn);
                     $result = $rvlManagmentDAO->checkRepname($principalId, $postTNAME);
                     if(count($result) == 0) {
               	          $rvlManagmentDAO = New RvlManagmentDAO($dbConn);
                          $errorTO = $rvlManagmentDAO->addSalesRep($principalId, $postTNAME, $postRMAIL);
                          
                          if ($errorTO->type==FLAG_ERRORTO_SUCCESS) { ?>
                               <script type='text/javascript'>parent.showMsgBoxInfo('New Rep added')</script>  
                               <?php
                               unset($_POST['MODSREP']);  
                               unset($_POST['ADDSREP']);  
                               unset($_POST['ADDSLREP']); 
                               unset($_POST['MODSLREP']); 
                          } else {?>
                               <script type='text/javascript'>parent.showMsgBoxError('Add Rep Failed <br><br> Contact Kwelanga Support')</script>
                               <?php
                               print_r($errorTO);	
                               unset($_POST['MODSREP']);  
                               unset($_POST['ADDSREP']);  
                               unset($_POST['ADDSLREP']); 
                               unset($_POST['MODSLREP']); 

                          }
                     } else { ?>
                          <script type='text/javascript'>parent.showMsgBoxError('Rep with same name already Exists <br><br> Try Again')</script>
                          <?php	
                          unset($_POST['MODSREP']);  
                          unset($_POST['ADDSREP']);  
                          unset($_POST['ADDSLREP']); 
                          unset($_POST['MODSLREP']); 
                     }
               } else {?>
                          <script type='text/javascript'>parent.showMsgBoxError('Email Address not valid <br><br> Try Again')</script>
                          <?php	
                          unset($_POST['MODSREP']);  
                          unset($_POST['ADDSREP']);  
                          unset($_POST['ADDSLREP']); 
                          unset($_POST['MODSLREP']); 
               }  
        } else { ?>
             <script type='text/javascript'>parent.showMsgBoxError('Rep Name Blank or too Short (Minimum 4) <br><br> Try Again')</script>
             <?php	
             unset($_POST['MODSREP']);  
             unset($_POST['ADDSREP']);  
             unset($_POST['ADDSLREP']); 
             unset($_POST['MODSLREP']); 
        }

    }
    if (isset($_POST['BACKFORM'])) {
          unset($_POST['MODSREP']);  	
    	    unset($_POST['ADDSREP']); 
    	    unset($_POST['ADDSLREP']);
          unset($_POST['MODSLREP']);    
    }

    if (isset($_POST['ADDSLREP'])) {
          $repMaintenanceScreens = new repMaintenanceScreens();
          $aa = $repMaintenanceScreens->addNewRep()  ;    	
    }     

    if (!isset($_POST['MODSREP']) && 
        !isset($_POST['ADDSREP']) && 
        !isset($_POST['ADDSLREP']) &&
        !isset($_POST['MODSLREP']) &&
        !isset($_POST["SUBMITFILTER"]) &&
        !isset($_POST["SELMOD"])) {
          $repMaintenanceScreens = new repMaintenanceScreens();
          $aa = $repMaintenanceScreens->selectRepAction()  ;
    } ?>
    </body>
</HTML>

<?php
 function test_input($data) {

  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  
  return $data;
 } ?>


<?php

?>



