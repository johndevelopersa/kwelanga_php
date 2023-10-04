<?php 
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER."properties/Constants.php");
    include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
    include_once($ROOT.$PHPFOLDER."libs/CommonUtils.php");
    include_once($ROOT.$PHPFOLDER.'DAO/UserPermissionsDAO.php');    
    include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");

    include_once("UserPermissionsScreens.php");
   
   if (!isset($_SESSION)) session_start() ;
      $userUId      = $_SESSION['user_id'] ;
      $principalId  = $_SESSION['principal_id'] ;
      $wareHouseCde = $_SESSION['depot_id'] ;
      $systemName   = $_SESSION['system_name'] ;
      
     

      //Create new database object
      $dbConn = new dbConnect(); 
      $dbConn->dbConnection();
      $errorTO = new ErrorTO;
      
//********************************************************************************************************************************************************
  if (!isset($_POST['SEARCH'])&&!isset($_POST['SELECTUSER'])&&!isset($_POST['COPYD'])&&!isset($_POST['COPYR'])&&!isset($_POST['SEARCHC'])&&!isset($_POST['SELECTCUSER'])&&!isset($_POST['COPYA'])&&!isset($_POST['COPYS'])&&!isset($_POST['SELECTCUSERR'])&&!isset($_POST['SEARCHCR'])&&!isset($_POST['COPYSR'])&&!isset($_POST['COPYAR'])){
       $UserPermissionsScreens = new UserPermissionsScreens();
       $a = $UserPermissionsScreens->SearchNewUser(); 	
  }	
//********************************************************************************************************************************************************
  if (isset($_POST['SEARCH'])){
       
     $SEARCH = $_POST['SEARCHU'];
     
     $UserPermissionsScreens = new UserPermissionsScreens();
     $a = $UserPermissionsScreens->UserSelect($SEARCH); 	
      	
  }
//********************************************************************************************************************************************************
  if (isset($_POST['SELECTUSER'])){
  	 
  	 $userID = trim(substr($_POST["USERID"],0,strpos($_POST["USERID"],"-")));;
  	 
  	 
  	 $UserPermissionsScreens = new UserPermissionsScreens();
     $a = $UserPermissionsScreens->UserUpdateScreen($userID); 
  	 
  }
//********************************************************************************************************************************************************
  if (isset($_POST['COPYD'])){
  	 	
  	 $Cat = $_POST['CAT'];	
  	 $userID = $_POST['USERID'];	
  	  
  	 $UserPermissionsScreens = new UserPermissionsScreens();
     $a = $UserPermissionsScreens->SearchCurrentUser($userID,$Cat); 
  	  
  }
//********************************************************************************************************************************************************
  if (isset($_POST['SEARCHC'])){
  	 $Cat = $_POST['CAT']; 
  	 $userID = $_POST['USERID']; 
  	 $SearchC = $_POST['SEARCHCU'];  	 
     $UserPermissionsScreens = new UserPermissionsScreens();
     $a = $UserPermissionsScreens->CurrentUserSelect($SearchC,$Cat,$userID); 
   
  }
//********************************************************************************************************************************************************
  if (isset($_POST['SELECTCUSER'])){
  	 
  	 $NUserID = $_POST['NUSERID'];
  	  $CuserID = trim(substr($_POST["USERID"],0,strpos($_POST["USERID"],"-")));;
  	 
  	 $UserPermissionsScreens = new UserPermissionsScreens();
     $a = $UserPermissionsScreens->CopyDepots($CuserID,$NUserID); 
   
  }


//********************************************************************************************************************************************************
  if (isset($_POST['COPYS'])){
  	
    	$NUserID = $_POST['NUSERID'];
  	  $CuserID = $_POST['CUSERID'];
  	  $List = implode(",",$_POST['select']);
  	  
  	 
  	      $UserPermissionsDOA = new UserPermissionsDOA($dbConn);
          $errorTO = $UserPermissionsDOA->UpdateSDepots($NUserID,$CuserID,$List);
  	 
  	 if ($errorTO->type==FLAG_ERRORTO_SUCCESS) { ?>
                    <script type='text/javascript'>parent.showMsgBoxInfo('User Depots Updated')</script>  
                    <?php
                      $UserPermissionsScreens = new UserPermissionsScreens();
                      $a = $UserPermissionsScreens->CopyDepots($CuserID,$NUserID);   		 	

                    } else { ?>
                   	<script type='text/javascript'>parent.showMsgBoxError('Failed To Update <br><br> Contact Kwelanga Support')</script>
                    <?php
                    
                    }
	}
//********************************************************************************************************************************************************
  if (isset($_POST['COPYA'])){
  	 
  	 $NUserID = $_POST['NUSERID'];
  	 $CuserID = $_POST['CUSERID'];
  	 
  	
          $UserPermissionsDOA = new UserPermissionsDOA($dbConn);
          $errorTO = $UserPermissionsDOA->UpdateADepots($NUserID,$CuserID); 
   		  
   		if ($errorTO->type==FLAG_ERRORTO_SUCCESS) { ?>
                    <script type='text/javascript'>parent.showMsgBoxInfo('User Depots Updated')</script>  
                    <?php
                      $UserPermissionsScreens = new UserPermissionsScreens();
                      $a = $UserPermissionsScreens->CopyDepots($CuserID,$NUserID);   		 	  		 	

                    } else { ?>
                   	<script type='text/javascript'>parent.showMsgBoxError('Failed To Update <br><br> Contact Kwelanga Support')</script>
                    <?php
                    
                    }
  }


//********************************************************************************************************************************************************
  if (isset($_POST['SELECTCUSERR'])){
  	 
  	 $NUserID = $_POST['NUSERID'];
  	  $CuserID = trim(substr($_POST["USERID"],0,strpos($_POST["USERID"],"-")));;
  	 
  	 $UserPermissionsScreens = new UserPermissionsScreens();
     $a = $UserPermissionsScreens->CopyRoles($CuserID,$NUserID); 
   
  }
//********************************************************************************************************************************************************
   if (isset($_POST['COPYR'])){
  	 	
  	 $Cat = $_POST['CAT'];	
  	 $userID = $_POST['USERID'];	
  	  
  	 $UserPermissionsScreens = new UserPermissionsScreens();
     $a = $UserPermissionsScreens->SearchCurrentUserR($userID,$Cat); 
  	  
   }
//********************************************************************************************************************************************************
   if (isset($_POST['SEARCHCR'])){
  	 $Cat = $_POST['CAT']; 
  	 $userID = $_POST['USERID']; 
  	 $SearchC = $_POST['SEARCHCU'];  	 
     $UserPermissionsScreens = new UserPermissionsScreens();
     $a = $UserPermissionsScreens->CurrentUserSelectR($SearchC,$Cat,$userID); 
   
   }
  
//********************************************************************************************************************************************************
 if (isset($_POST['COPYSR'])){
  	
    	$NUserID = $_POST['NUSERID'];
  	  $CuserID = $_POST['CUSERID'];
  	  $List = implode(",",$_POST['select']);
  	  
  	 
  	      $UserPermissionsDOA = new UserPermissionsDOA($dbConn);
          $errorTO = $UserPermissionsDOA->UpdateSRoles($NUserID,$CuserID,$List);
  	 
  	 if ($errorTO->type==FLAG_ERRORTO_SUCCESS) { ?>
                    <script type='text/javascript'>parent.showMsgBoxInfo('User Depots Updated')</script>  
                    <?php
                      $UserPermissionsScreens = new UserPermissionsScreens();
                      $a = $UserPermissionsScreens->CopyRoles($CuserID,$NUserID);   		 	

                    } else { ?>
                   	<script type='text/javascript'>parent.showMsgBoxError('Failed To Update <br><br> Contact Kwelanga Support')</script>
                    <?php
                    
                    }
	}

//********************************************************************************************************************************************************
if (isset($_POST['COPYAR'])){
  	 
  	 $NUserID = $_POST['NUSERID'];
  	 $CuserID = $_POST['CUSERID'];
  	 
  	
          $UserPermissionsDOA = new UserPermissionsDOA($dbConn);
          $errorTO = $UserPermissionsDOA->UpdateARoles($NUserID,$CuserID); 
   		  
   		if ($errorTO->type==FLAG_ERRORTO_SUCCESS) { ?>
                    <script type='text/javascript'>parent.showMsgBoxInfo('User Roles Updated')</script>  
                    <?php
                        		 	  		 	

                    } else { ?>
                   	<script type='text/javascript'>parent.showMsgBoxError('Failed To Update <br><br> Contact Kwelanga Support')</script>
                    <?php
                    
                    }
  }



//********************************************************************************************************************************************************

//********************************************************************************************************************************************************
 
?>
     <!DOCTYPE html>
<HTML>
	<HEAD>

		<TITLE>User Permissions</TITLE>

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
    <BODY>
    <?php   