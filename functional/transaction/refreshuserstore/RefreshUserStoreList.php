<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
    include_once($ROOT.$PHPFOLDER."DAO/transactionDAO.php");
    include_once($ROOT.$PHPFOLDER."properties/Constants.php");
    include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
    include_once($ROOT.$PHPFOLDER.'DAO/ManageOrdersDAO.php');
    include_once($ROOT.$PHPFOLDER.'TO/PostingOrderNewDetailLineTO.php');

    if (!isset($_SESSION)) session_start() ;
      $userUId     = $_SESSION['user_id'] ;
      $principalId = $_SESSION['principal_id'] ;
      $depotId     = $_SESSION['depot_id'] ;
      $systemId    = $_SESSION["system_id"];
      $systemName  = $_SESSION['system_name'];

      // Create new database object
      $dbConn = new dbConnect(); 
      $dbConn->dbConnection();
            
      // Get Rep UID from User Uid
      
      $ManageOrdersDAO = new ManageOrdersDAO($dbConn);
      $mfRUid = $ManageOrdersDAO->getUseRepUid($userUId);
      
      if(sizeof($mfRUid)<=0) { ?>
                 <script type='text/javascript'>parent.showMsgBoxError('User has No allocated Rep code - Contact Kwelanga Support')</script> 
                 
          <?php 
          return;
      }
      // Delete Existing User Store
      
      $ManageOrdersDAO = new ManageOrdersDAO($dbConn);
      $result = $ManageOrdersDAO->deleteExistingUserStores($userUId);
      
      if($result <> 'S') { ?>
             <script type='text/javascript'>parent.showMsgBoxError('Store Delete Failed - Contact Kwelanga Support')</script> 
          <?php 
          return;
      }    
      
      $ManageOrdersDAO = new ManageOrdersDAO($dbConn);
      $result = $ManageOrdersDAO->loadNewUserStores($principalId, $userUId, $mfRUid[0]['uid']);
      if($result->type <> 'S') { ?>
             <script type='text/javascript' >parent.showMsgBoxError('Loading New Stores Failed - Contact Kwelanga Support') </script> 
          <?php 
          return;
      };
      
      $addedStores = $result->object['records'];
      
      $ManageOrdersDAO = new ManageOrdersDAO($dbConn);
      $result = $ManageOrdersDAO->deleteExistingUserChains($userUId);
      
      if($result <> 'S') { ?>
             <script type='text/javascript'>parent.showMsgBoxError('Chain Delete Failed - Contact Kwelanga Support')</script> 
          <?php 
          return;
      }    
      
      $ManageOrdersDAO = new ManageOrdersDAO($dbConn);
      $result = $ManageOrdersDAO->loadNewUserChains($principalId, $userUId, $mfRUid[0]['uid']);
      if($result->type <> 'S') { ?>
             <script type='text/javascript' >parent.showMsgBoxError('Loading New Chains Failed - Contact Kwelanga Support') </script> 
          <?php 
          return;
      };      
      
      $addedChains = $result->object['records'];      
           
      ?>
      <script type='text/javascript'>parent.showMsgBoxInfo('Store List Refresh Complete <br>Stores added - <?php echo $addedStores ;?> <br>Chains added - <?php echo $addedChains ;?>')</script> 
          <?php 
          return;
          
?> 