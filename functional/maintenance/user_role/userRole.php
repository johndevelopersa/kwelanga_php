<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once ($ROOT.$PHPFOLDER.'functional/maintenance/user_role/userRoleClass.php');
    include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');  
    include_once($ROOT.$PHPFOLDER.'DAO/MaintenanceDAO.php');
		    
    if (!isset($_SESSION)) session_start() ;
      $userUId     = $_SESSION['user_id'] ;
      $principalId = $_SESSION['principal_id'] ;

      //Create new database object
      $dbConn  = new dbConnect(); 
      $dbConn->dbConnection();
      $errorTO = new ErrorTO;

?>
<!DOCTYPE html>
<HTML>
   <head>

		<TITLE>User Role Form</TITLE>

		<link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/1_kwelanga.css' rel='stylesheet' type='text/css'>
    <link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/kos_standard_screen2.css' rel='stylesheet' type='text/css'>
		<script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/jquery.js"></script>
		<script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/dops_global_functions.js"></script>

    <style> 
    	
    	</style>

   </head>
   <body>
   <?php
   
   
   if(isset($_POST['roleform'])) {

         $MaintenanceDAO = new MaintenanceDAO($dbConn);
         $errorTO = $MaintenanceDAO->clearUserRoles($principalId,
                                                    $_POST['USERID'],
                                                    implode(",",$_POST['ROLELIST']));
                                   
         if($errorTO->type <> FLAG_ERRORTO_SUCCESS) { ?>
              <script type='text/javascript'>parent.showMsgBoxError('Clearing Roles Failed - Contact Support')</script> 
              <?php
              unset($_POST['roleform']);
              unset($_POST['firstform']);
         } else {
         	  $successMsg = 'T';
         	  
         	  
//         	  echo "<br>";
//         	  print_r($_POST['HASROLE']);
            foreach ($_POST['HASROLE'] as $roleR) {
                  
                  $MaintenanceDAO = new MaintenanceDAO($dbConn);
                  $errorTO = $MaintenanceDAO->addUserRoles($principalId,
                                                           $_POST['USERID'],
                                                           $roleR);
                                                           
                  if($errorTO->type <> FLAG_ERRORTO_SUCCESS) { 
                  	    $successMsg = 'F'; ?>
                        <script type='text/javascript'>parent.showMsgBoxError('Adding Roles Failed - Contact Support')</script> 
                        <?php 
                        unset($_POST['roleform']);
                        unset($_POST['firstform']);
                        break;
                  }	                                      
            }
            
            if($successMsg == 'T') { ?>
                   <script type='text/javascript'>parent.showMsgBoxInfo('Role Changes Successful')</script> 
                   <?php 
                   unset($_POST['roleform']);
                   unset($_POST['firstform']);
            }
         }
   }
   
   if(isset($_POST['firstform'])) {
         $userRole = new userRole();
         $a = $userRole->roleform($principalId, $_POST['USERID']);             	
     }    
     if(!isset($_POST['firstform'])) {
         $userRole = new userRole();
         $a = $userRole->firstform($userUId, $principalId);             	
     } ?>
   </body>       
</HTML>

<?php
 function test_input($data) {

  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  if($data=='') { $data=0; } 
    
  return $data;
 }
 ?> 
