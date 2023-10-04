<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER."properties/Constants.php");
    include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
    include_once($ROOT.$PHPFOLDER."libs/CommonUtils.php");
    include_once($ROOT.$PHPFOLDER.'DAO/EmployeeDAO.php');
    include_once("employeeRecordingScreens.php");

    if (!isset($_SESSION)) session_start() ;
  if (!isset($_SESSION)) session_start() ;
      $userUId      = $_SESSION['user_id'] ;
      $principalId  = $_SESSION['principal_id'] ;
      $wareHouseCde = $_SESSION['depot_id'] ;

      //Create new database object
      $dbConn = new dbConnect(); 
      $dbConn->dbConnection();
      $errorTO = new ErrorTO;

?>  
<!DOCTYPE html>
<HTML>
	<HEAD>

		<TITLE>Principal - Warehouse Selection</TITLE>

    <link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/kos_standard_screen2.css' rel='stylesheet' type='text/css'>
    <link href="<?php echo $DHTMLROOT.$PHPFOLDER.'css/css.php?SYSID='.$systemId.'&SYSNAME='.$systemName ?>" rel="stylesheet" type="text/css" />
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
    <body>
    <?php

    if (isset($_POST['CANFORM'])) {
          return;    
    }
    
    if(isset($_POST['SETWH'])) { 
        $_SESSION['depot_id'] = $_POST['WHID'];
        unset($_POST['SETWH']);
    }    

    if (isset($_POST['BACKFORM'])) {
           unset($_POST['MODEMPSP']); 
           unset($_POST['ADDEMPSP']);
    }
    if (isset($_POST['MODEMPSP'])) {
    	
    	     if(isset($_POST['REMEMP']) == 'A') {
    	     	    $showDeleted = 'A';
    	     } else {
    	          $showDeleted = ''; 	
    	     }
    	
           $employeeRecordingScreens = new employeeRecordingScreens();
           $a = $employeeRecordingScreens->firstform($showDeleted); 
           
    }

    if (isset($_POST['SUBEMPUPD'])) {
    	
    	  if($_POST['DMLTYPE'] == 'A') { 
    	  	    	  	
    	  	     $empWh      = test_input($_POST['WAREHOUSE']);
    	  	     $empCode    = test_input(trim($_POST['ECODE']));
    	  	     $empName    = test_input(trim($_POST['ENAME']));
    	  	     $empJob     = test_input(trim($_POST['EJS']));
               $empComment = test_input(trim($_POST['SCOMMENT'])); 
               $empId      = test_input(trim($_POST['EID']));
    	  	
               $EmployeeDAO = new EmployeeDAO($dbConn); 
               $errorTO = $EmployeeDAO->employeeDataValidation($empWh, 
                                                               $empCode,
                                                               $empName,
                                                               $empJob,
                                                               'A');
                                                               
               if($errorTO->type <> FLAG_ERRORTO_SUCCESS) { ?>
                      <script type='text/javascript'>parent.showMsgBoxError('<?php echo $errorTO->description; ?>')</script>  
                      <?php
                      unset($_POST['NAMEFILTER']);
                      unset($_POST['CODEFILTER']);
                      unset($_POST['FIRSTFORM']);
                      unset($_POST['GETEMP']);
                      unset($_POST['EMPCODE']);                                                   
    	  	     } else {
                      $EmployeeDAO = new EmployeeDAO($dbConn); 
                      $errorTO = $EmployeeDAO->insertNewEmployee($empWh, 
                                                                 $empCode,
                                                                 $empName,
                                                                 $empId,
                                                                 $empJob,
                                                                 $empComment,
                                                                 $userUId);
                      if($errorTO->type == FLAG_ERRORTO_SUCCESS) { ?>
                            <script type='text/javascript'>parent.showMsgBoxInfo('New Employee Successfully Added')</script>  
                            <?php
                            unset($_POST['NAMEFILTER']);
                            unset($_POST['CODEFILTER']);
                            unset($_POST['FIRSTFORM']);
                            unset($_POST['GETEMP']);
                            unset($_POST['EMPCODE']); 
                      } else {?>
                            <script type='text/javascript'>parent.showMsgBoxError('Error! New Employee Add Failed')</script>
                            <?php
                            unset($_POST['NAMEFILTER']);
                            unset($_POST['CODEFILTER']);
                            unset($_POST['FIRSTFORM']);
                            unset($_POST['GETEMP']);
                            unset($_POST['EMPCODE']);  
                      } 
                  }    
       } else { 
       	
               $empNewWh   = test_input($_POST['WAREHOUSE']);
               $whUid      = test_input($_POST['WHID']);
    	  	     $empName    = test_input(trim($_POST['ENAME']));
               $empJob     = test_input(trim($_POST['NEWJOB']));
               $empComment = test_input(trim($_POST['SCOMMENT'])); 
               $empId      = test_input(trim($_POST['EID']));
               $empStatus  = test_input(trim($_POST['STATUS']));
               $empUid     = test_input(trim($_POST['EMPUID']));
               $empCode    = test_input(trim($_POST['ECODE']));
               
               if($empNewWh == 'Change Warehouse') {
                    $empWh =  $whUid;
               } else {
                    $empWh =  $empNewWh;  	
               }
       	
       	       if(trim($empJob) == 'Change Employee Job') {
                    $updEmpJob = test_input(trim($_POST['OLDJOB']));
       	       } else {
                    $updEmpJob = test_input(trim($_POST['NEWJOB']));
       	       }
       	       
               if($empStatus == 'ACTIVE') {
                    $updStatus = 'A';
       	       } else {
                    $updStatus = 'D';
       	       }
       	       
       	       $EmployeeDAO = new EmployeeDAO($dbConn); 
               $errorTO = $EmployeeDAO->employeeDataValidation($empWh, 
                                                               $empCode,
                                                               $empName,
                                                               $empJob,
                                                               'U');	     
       	
               $EmployeeDAO = new EmployeeDAO($dbConn); 
               $errorTO = $EmployeeDAO->updateEmployeeDetails($empWh, 
                                                              $empCode,
                                                              $empName,
                                                              $empId,
                                                              $updEmpJob,
                                                              $empComment,
                                                              $userUId,
                                                              $updStatus,
                                                              $empUid);
                                                              
                                                              
               if($errorTO->type == FLAG_ERRORTO_SUCCESS) { ?>
                            <script type='text/javascript'>parent.showMsgBoxInfo('New Employee Successfully Updated')</script>  
                            <?php
                            unset($_POST['NAMEFILTER']);
                            unset($_POST['CODEFILTER']);
                            unset($_POST['FIRSTFORM']);
                            unset($_POST['GETEMP']);
                            unset($_POST['EMPCODE']); 
                      } else {?>
                            <script type='text/javascript'>parent.showMsgBoxError('Error! New Employee Update Failed')</script>
                            <?php
                            unset($_POST['NAMEFILTER']);
                            unset($_POST['CODEFILTER']);
                            unset($_POST['FIRSTFORM']);
                            unset($_POST['GETEMP']);
                            unset($_POST['EMPCODE']);  
                      }
       }    	  	

    }    
    
    if(isset($_POST['EMPCODE']) && $_POST['EMPCODE'] <> '') {
    	
                 $EmployeeDAO = new EmployeeDAO($dbConn); 
                 $empDetails = $EmployeeDAO->getEmployeeDetails($_POST['EMPCODE'], 3, $wareHouseCde, $_POST['SHOWDELETED']);   
           
                 if(count($empDetails) <> 0) {
                        $employeeRecordingScreens = new employeeRecordingScreens();
                        $a = $employeeRecordingScreens->empDetailCapture($wareHouseCde, "U", $userUId, $principalId, $empDetails);
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
           } else {
                 if(isset($_POST['NAMEFILTER'])) {
    	
                      $EmployeeDAO = new EmployeeDAO($dbConn); 
                      $empDetails = $EmployeeDAO->getEmployeeDetails($_POST['UVALUE'], 1, $wareHouseCde, $_POST['SHOWDELETED']);
        
                      if(count($empDetails) <> 0) {
                          $employeeRecordingScreens = new employeeRecordingScreens();
                          $a = $employeeRecordingScreens->SelectEmp($empDetails, $_POST['SHOWDELETED']);
               
                          unset($_POST['CODEFILTER']);
                          unset($_POST['FIRSTFORM']);    	
                      } else { ?>
                           <script type='text/javascript'>parent.showMsgBoxError('Error! Check Filter No Rows Returned')</script>
                           <?php	
                           unset($_POST['NAMEFILTER']);
                           unset($_POST['CODEFILTER']);
                           unset($_POST['FIRSTFORM']);
                           unset($_POST['GETEMP']);
                           unset($_POST['EMPCODE']);              	
                      }      
                 }
                 if(isset($_POST['CODEFILTER'])) { 
                      $EmployeeDAO = new EmployeeDAO($dbConn); 
                      $empDetails = $EmployeeDAO->getEmployeeDetails($_POST['UVALUE'], 2, $wareHouseCde, $_POST['SHOWDELETED']);
         
                      if(count($empDetails) <> 0) {
                           $employeeRecordingScreens = new employeeRecordingScreens();
                           $a = $employeeRecordingScreens->SelectEmp($empDetails, $_POST['SHOWDELETED']);
               
                           unset($_POST['NAMEFILTER']);
                           unset($_POST['FIRSTFORM']);     	
                      } else {?>
         	
                           <script type='text/javascript'>parent.showMsgBoxError('Error! Check Filter No Rows Returned')</script>
                           <?php	
                           unset($_POST['NAMEFILTER']);
                           unset($_POST['CODEFILTER']);
                           unset($_POST['FIRSTFORM']);
                           unset($_POST['GETEMP']);
                           unset($_POST['EMPCODE']);     
                      }      
                 }
                 if(isset($_POST['GETEMP'])) {    
                 		      
                     $eN = trim(substr($_POST['EMPID'],strpos($_POST['EMPID'],'-') + 1, 50));
                     $eU = trim(substr($_POST['EMPID'],0,strpos($_POST['EMPID'],'&')));
                     $eC = trim(substr($_POST['EMPID'],0,strpos($_POST['EMPID'],'-')-1));
            
                     if( $_POST['EMPID'] <> 'Select Employee') {

                           $EmployeeDAO = new EmployeeDAO($dbConn); 
                           $empDetails = $EmployeeDAO->getEmployeeDetails($eC, 2, $wareHouseCde, $_POST['SHOWDELETED']);
             
                           if(count($empDetails) <> 0) {
                               $employeeRecordingScreens = new employeeRecordingScreens();
                               $a = $employeeRecordingScreens->empDetailCapture($wareHouseCde, "U", $userUId, $principalId, $empDetails);
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
           }
                      	  
    if (isset($_POST['ADDEMPSP'])) {
    	  $empUid = '';
        $employeeRecordingScreens = new employeeRecordingScreens();
        $a = $employeeRecordingScreens->empDetailCapture($wareHouseCde, "A", $userUId, $principalId, $empUid);    	
        
        unset($_POST['MODEMPSP']); 
    }	       
    if (!isset($_POST['MODEMPSP']) && !isset($_POST["ADDEMPSP"]) && !isset($_POST['EMPID']) && !isset($_POST['EMPCODE']) && $wareHouseCde <> 0) {
             $employeeForm = new employeeRecordingScreens();
             $whSr       = $employeeForm->pickUpdateAction();             
    }     
    if($_SESSION['depot_id'] == 0 && !isset($_POST['SETWH']) ) {
    	 
    	  $_SESSION['depot_id'] = 1;
    	  
        $employeeRecordingScreens = new employeeRecordingScreens();
        $a = $employeeRecordingScreens->selectWarehouse($userUId, $principalId) ;
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
 <script type="text/javascript" defer>
     function setFocusTPselect() {
          document.getElementById("WAREHOUSE").focus();
     }
function setFocusToTextBoxI(){
    document.getElementById("TRANSPORTER").focus();
}     

function selectAll(elementName, flag){
    $("input[name='"+elementName+"']").each( function(){$(this).attr("checked",((flag == 1)?true:false));})
}

function dupConfirm() {
	
    var userPreference;

		if (confirm("Do you want to save changes?") == true) {
         userPreference = "Data saved successfully!";
		} else {
         userPreference = "Save Canceled!";
		}

		document.getElementById("msg").innerHTML = userPreference;

}

</script>

<?php

?>



