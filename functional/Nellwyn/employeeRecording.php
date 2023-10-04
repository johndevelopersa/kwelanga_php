<?php
    include_once('ROOT.php'); 
    include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
    include_once($ROOT.$PHPFOLDER."libs/GUICommonUtils.php");
    include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
    include_once($ROOT.$PHPFOLDER.'elements/basicSelectElement.php');	    
    include_once('employeeRecordingScreens.php');    

  if (!isset($_SESSION)) session_start() ;
      $userUId      = $_SESSION['user_id'] ;
      $principalId  = $_SESSION['principal_id'] ;
      $wareHouseCde = $_SESSION['depot_id'] ;
      $systemName   = $_SESSION['system_name'] ;
      
      
            $DateCaptured = (isset($_POST["DATECAPTURED"])) ? htmlspecialchars($_POST["DATECAPTURED"]) :  CommonUtils::getUserDate();     

      
// if($userUId == 11) {
//      echo "<pre>";
//    print_r($_SESSION);
//  }

// echo $systemName ;
      
      //Create new database object
      $dbConn  = new dbConnect(); 
      $dbConn->dbConnection();
      $errorTO = new ErrorTO;
?>
<!DOCTYPE html>
<HTML>
   <HEAD>

		<TITLE>Nellwyn Employee recording</TITLE>

    <link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/kos_standard_screen2.css' rel='stylesheet' type='text/css'>
    <link href="<?php echo $DHTMLROOT.$PHPFOLDER.'css/css.php?SYSID='.$systemId.'&SYSNAME='.$systemName ?>" rel="stylesheet" type="text/css" />
		<script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/jquery.js"></script>
		<script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/dops_global_functions.js"></script>

    <style>
       table.box {border:collapse;
                  border: 2px solid; 
       	          border-color: #990000; 
      	          background:   #fcecec 
      	         }     
            .scan-input {
                  height: 30px !important;
                  border-radius: 15px !important;
                  padding-left: 50px !important;
                  width:100% !important;
            }
            .scan-input:focus {
                  outline:none !important;
                  border:2px solid black;
            }
    </style>

   </HEAD>
   <?php
    if(isset($_POST['CANFORM'])) { 
        return;
    }
    if(isset($_POST['BACKFORM'])) { 
        unset($_POST['NAMEFILTER']);
        unset($_POST['CODEFILTER']);
        unset($_POST['FIRSTFORM']);
        unset($_POST['GETEMP']);
        unset($_POST['EMPCODE']);    
    }
    if(isset($_POST['SETWH'])) { 
        $_SESSION['depot_id'] = $_POST['WHID'];
        if($_SESSION['depot_id'] == 1 || $_SESSION['depot_id'] == 0) {
              $_SESSION['depot_id'] = 0	; ?>
                <script type='text/javascript'>parent.showMsgBoxError('Error! Warehouse Not Set')</script>
                <?php	
        }
        unset($_POST['SETWH']);
    }
    
    if(isset($_POST['SUBDET'])) {
    	    	
           $eUid  = test_input($_POST['EMDID']);
           $etim  = test_input($_POST['DATECAPTURED']);
           $ejob  = test_input($_POST['NEWJOB']);
           $ojob  = test_input($_POST['OLDJOB']);
           $dcom  = test_input($_POST['SCOMMENT']);
           $depId = test_input($_POST['DEPID']);
           
           $EmployeeDAO = new EmployeeDAO($dbConn); 
           $dupStr     = $EmployeeDAO->checkForDuplicates($depId, $eUid,$etim);         
           
           if(count($dupStr) == 0) {
                  $EmployeeDAO = new EmployeeDAO($dbConn); 
                  $errorTO     = $EmployeeDAO->insertEmployeeRecords($eUid, $etim, $ojob, $dcom, $ejob, $userUId, $depId);
           	
                  if($errorTO->type!=FLAG_ERRORTO_SUCCESS)	{?>
                         <script type='text/javascript'>parent.showMsgBoxError('Error! Inserting Record')</script>
                         <?php	
                  } else {?>
                         <script type='text/javascript'>parent.showMsgBoxInfo('Employee Record Saved Successfully')</script>
                         <?php
                  }
           } else {?>
                  <script type='text/javascript'>parent.showMsgBoxError('Error! Employee Already Captured Today')</script>
                  <?php         	
           }       
           
           unset($_POST['NAMEFILTER']);
           unset($_POST['CODEFILTER']);
           unset($_POST['FIRSTFORM']);
           unset($_POST['GETEMP']);
    } 
    
    if(isset($_POST['EMPCODE']) && $_POST['EMPCODE'] <> '') {
    	
          if( $_POST['EMPCODE'] <> '') {
                 $EmployeeDAO = new EmployeeDAO($dbConn); 
                 
                 $empDetails = $EmployeeDAO->getEmployeeDetails($_POST['EMPCODE'], 3, $wareHouseCde, '');
                                  
                 if(count($empDetails) <> 0) {
                         $employeeRecordingScreens = new employeeRecordingScreens();
                         $a = $employeeRecordingScreens->captureEmpDetails($empDetails,$DateCaptured);
                         unset($_POST['NAMEFILTER']);
                         unset($_POST['CODEFILTER']);
                         unset($_POST['FIRSTFORM']);
                         unset($_POST['GETEMP']);                 	
                 } else { ?>
                      <script type='text/javascript'>parent.showMsgBoxError('Error! No Employee Found For Code')</script>
                      <?php
                
                      unset($_POST['NAMEFILTER']);
                      unset($_POST['CODEFILTER']);
                      unset($_POST['FIRSTFORM']);
                      unset($_POST['GETEMP']);
                      unset($_POST['EMPCODE']);                 	
                 }
            } else { ?>
         	      <script type='text/javascript'>parent.showMsgBoxError('Error! No Employee Selected')</script>
                <?php
                
                 unset($_POST['NAMEFILTER']);
                 unset($_POST['CODEFILTER']);
                 unset($_POST['FIRSTFORM']);
                 unset($_POST['GETEMP']);
                 unset($_POST['EMPCODE']);
            }
            
      unset($_POST['CODEFILTER']);
      unset($_POST['FIRSTFORM']); 
      unset($_POST['NAMEFILTER']);  
      unset($_POST['GETEMP']);
    } 
       
    if(isset($_POST['NAMEFILTER'])) {
    	
    	   $EmployeeDAO = new EmployeeDAO($dbConn); 
         $empDetails = $EmployeeDAO->getEmployeeDetails($_POST['UVALUE'], 1, $wareHouseCde, '');
         
         if(count($empDetails) <> 0) {
               $employeeRecordingScreens = new employeeRecordingScreens();
               $a = $employeeRecordingScreens->SelectEmp($empDetails, '');
               
                unset($_POST['CODEFILTER']);
                unset($_POST['FIRSTFORM']);    	
         } else { ?>
         	
         	      <script type='text/javascript'>parent.showMsgBoxError('Error! Check Filter No Rows Returned')</script>
                <?php	
                unset($_POST['NAMEFILTER']);
                unset($_POST['FIRSTFORM']);         	
         }      
    }
    if(isset($_POST['CODEFILTER'])) { 
    	   $EmployeeDAO = new EmployeeDAO($dbConn); 
         $empDetails = $EmployeeDAO->getEmployeeDetails($_POST['UVALUE'], 2, $wareHouseCde, '');
         
         if(count($empDetails) <> 0) {
               $employeeRecordingScreens = new employeeRecordingScreens();
               $a = $employeeRecordingScreens->SelectEmp($empDetails);
               
                unset($_POST['NAMEFILTER']);
                unset($_POST['FIRSTFORM']);     	
         } else {?>
         	
         	      <script type='text/javascript'>parent.showMsgBoxError('Error! Check Filter No Rows Returned')</script>
                <?php	
                unset($_POST['NAMEFILTER']);
                unset($_POST['CODEFILTER']);
                unset($_POST['FIRSTFORM']);
         	
         }      
    }
    if(isset($_POST['GETEMP'])) {  

            $EmployeeDAO = new EmployeeDAO($dbConn); 
            $empDetails = $EmployeeDAO->getEmployeeDetails($_POST['EMPID'], 3, $wareHouseCde,'');
    	 	      
            $eN = trim(substr($_POST['EMPID'],strpos($_POST['EMPID'],'-') + 1, 50));
            $eU = trim(substr($_POST['EMPID'],0,strpos($_POST['EMPID'],'&')));
            $eC = trim(substr($_POST['EMPID'],strpos($_POST['EMPID'],'&')+1,strpos($_POST['EMPID'],'-')-2));
            
            if( $_POST['EMPID'] <> 'Select Employee') {
                 $employeeRecordingScreens = new employeeRecordingScreens();
                 $a = $employeeRecordingScreens->captureEmpDetails($empDetails,$DateCaptured);
                 unset($_POST['NAMEFILTER']);
                 unset($_POST['CODEFILTER']);
                 unset($_POST['FIRSTFORM']);

            } else { ?>
         	      <script type='text/javascript'>parent.showMsgBoxError('Error! No Employee Selected')</script>
                <?php
                
                 unset($_POST['NAMEFILTER']);
                 unset($_POST['CODEFILTER']);
                 unset($_POST['FIRSTFORM']);
                 unset($_POST['GETEMP']);
            }

    }         
                
        	    
// ***********************************************************************************************************************************************    
    if (!isset($_POST['FIRSTFORM']) && !isset($_POST['NAMEFILTER']) && !isset($_POST['CODEFILTER']) && !isset($_POST['GETEMP']) && !isset($_POST['EMPCODE']) && $_SESSION['depot_id'] > 2) {
    	
        $employeeRecordingScreens = new employeeRecordingScreens();
        $a = $employeeRecordingScreens->firstform('');              //  firstform = function contained in the class

    } 
// ***********************************************************************************************************************************************    
    
    if($_SESSION['depot_id'] == 0 && !isset($_POST['SETWH']) ) {
    	 
    	  $_SESSION['depot_id'] = 1;
    	  
        $employeeRecordingScreens = new employeeRecordingScreens();
        $a = $employeeRecordingScreens->selectWarehouse($userUId, $principalId) ;
    } ?> 
        
</HTML>

<?php
 function test_input($data) {

  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
    
  return $data;
 }
