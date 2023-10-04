<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER."properties/Constants.php");
    include_once($ROOT.$PHPFOLDER."elements/datePickerElement.php");
    include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
    include_once($ROOT.$PHPFOLDER."libs/CommonUtils.php");
    include_once($ROOT.$PHPFOLDER."DAO/deBriefingDAO.php");			
    include_once("processDebriefWithUomScreens.php");
	  
     if (!isset($_SESSION)) session_start() ;
     $userUId     = $_SESSION['user_id'] ;
     $principalId = $_SESSION['principal_id'] ;
     $depotId     = $_SESSION['depot_id'] ;
                
//Create new database object
$dbConn = new dbConnect(); 
$dbConn->dbConnection();
$errorTO = new ErrorTO;

?>
<!DOCTYPE html>
<html style="height:100%;width:100%;">
  <head>

		<TITLE>Document Selection</TITLE>
    <link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/1_kwelanga.css?v=1' rel='stylesheet' type='text/css'>
    <link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/kos_standard_screen.css' rel='stylesheet' type='text/css'>
    <?php DatePickerElement::getDatePickerLibs(); ?>
	 <LINK href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/1_default.css' rel='stylesheet' type='text/css'>
    
    <script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/jquery.js"></script>
    <script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/dops_global_functions.js"></script>

    <style>

      .scan-input {
         height: 42px !important;
         border-radius: 15px !important;
         padding-left: 50px !important;
         width:100% !important;
      }
      .scan-input:focus {
         outline:none !important;
         border:3px solid black;
      }

    </style>

  </head>
  
  <body>
  	  <?php
  	  
  	if(isset($_POST['BACK'])) {
            unset($_POST['DELPARTIAL']); 
            unset($_POST['DELFULL']);  		
  		
  		
  	}  	  
  	if(isset($_POST['DEBRIEFDOC'])) {
  		
  		   // Set status 77
  		   // Set GRV
  		   // Set Log
  		   // Set Del D
  		   
  		   echo $_POST['DOCUID'];
  		   echo "<br>";
  		   echo $_POST['GRVNO'];
  		   echo "<br>";  		
  		   echo $_POST['DELDATE'];
  		   echo "<br>";
  		   
  		   unset($_POST['DELPARTIAL']); 
         unset($_POST['DELFULL']);  	
  		     		
  	}
  	if(isset($_POST['DEBRIEFPART'])) {
  		
  		   echo $_POST['DOCUID'];
  		   echo "<br>";
  		   echo $_POST['GRVNO'];
  		   echo "<br>";   		   
  		   echo $_POST['CLMNO'];
  		   echo "<br>"; 
  		   echo $_POST['DELDATE'];
  		   echo "<pre>";
  		   echo "<br>";
  		   print_r($_POST['RETQTY']);  		
         echo "<br>";
  		   print_r($_POST['STSTATUS']);
  		   echo "<br>";
  		   print_r($_POST['PRODUID']);  	
  		   echo "<br>";
  	}  	  
  	if(isset($_POST['PROCREDELIVER'])) {
  		
  		   echo $_POST['DOCUID'];
  		   echo "<br>";
         echo $_POST['REASON'];
         echo "<br>"; 
         echo $_POST['TRIPUID'];
         echo "<br>";
         echo $_POST['DEPUID'];
         echo "<br>";
         echo $_POST['TRIPNUM'];
  		   echo "<br>";
  	}  	
    if(isset($_POST['DELFULL'])) {
  	  	
             $docNumber = test_input($_POST['DOCUMENTNO']);
             $dashPos = strpos($docNumber,'-');
  	  	      
             if($dashPos > 0) {
  	  	          $prinUid = trim(substr($docNumber,0,$dashPos));
  	  	          $docNo   = trim(substr($docNumber,$dashPos + 1 ,10));
  	  	      	     
                  $getRdDoc = new deBriefingDAO($dbConn);
                  $rdDoc     = $getRdDoc->getFullDelDocument($prinUid, $docNo);

                  if(count($rdDoc) != 0) {
                       
                       $jsonDocumentheader = json_encode($rdDoc, TRUE);
                       $scrVar = new processDebriefWithUomScreens();
                       $a = $scrVar->showSelectedDocument($jsonDocumentheader);  
                       
                  } else { ?>
                       <script type='text/javascript'>parent.showMsgBoxError("Document Number Not Found<BR>")</script> 
                       <?php
                       unset($_POST['DELPARTIAL']); 
                       unset($_POST['DELFULL']);
                  }   	     

  	  	     } else { ?>
                    <script type='text/javascript'>parent.showMsgBoxError("Document Number Format Wrong<BR><BR> Must be 'Principal' - 'Document Number'<BR>")</script> 
                    <?php
                    unset($_POST['DELPARTIAL']); 
                    unset($_POST['DELFULL']);             	
             }
  	  
  	  
  	  
  	  
  	  
    }
    if(isset($_POST['DELPARTIAL'])) {
             $docNumber = test_input($_POST['DOCUMENTNO']);
             $dashPos = strpos($docNumber,'-');
  	  	      
             if($dashPos > 0) {
  	  	          $prinUid = trim(substr($docNumber,0,$dashPos));
  	  	          $docNo   = trim(substr($docNumber,$dashPos + 1 ,10));
  	  	      	     
                  $getRdDoc = new deBriefingDAO($dbConn);
                  $rdDoc     = $getRdDoc->getFullPartialDocumentDetail($prinUid, $docNo);

                  if(count($rdDoc) != 0) {
                       
                       $jsonDocumentheader = json_encode($rdDoc, TRUE);
                       $scrVar = new processDebriefWithUomScreens();
                       $a = $scrVar->showSelectedPartialDocument($jsonDocumentheader);  
                       
                  } else { ?>
                       <script type='text/javascript'>parent.showMsgBoxError("Document Number Not Found<BR>")</script> 
                       <?php
                       unset($_POST['DELPARTIAL']); 
                       unset($_POST['DELFULL']);
                  }   	     

  	  	     } else { ?>
                    <script type='text/javascript'>parent.showMsgBoxError("Document Number Format Wrong<BR><BR> Must be 'Principal' - 'Document Number'<BR>")</script> 
                    <?php
                    unset($_POST['DELPARTIAL']); 
                    unset($_POST['DELFULL']);             	
             }
    } 
  	   	  
    if(isset($_POST['REDELIVERY'])) {
  	  	
             $docNumber = test_input($_POST['DOCUMENTNO']);
             $dashPos = strpos($docNumber,'-');
  	  	      
             if($dashPos > 0) {
  	  	          $prinUid = trim(substr($docNumber,0,$dashPos));
  	  	          $docNo   = trim(substr($docNumber,$dashPos + 1 ,10));
  	  	      	     
                  $getRdDoc = new deBriefingDAO($dbConn);
                  $rdDoc     = $getRdDoc->getReturnUomDocument($prinUid, $docNo);

                  if(count($rdDoc) != 0) {
                       
                       $jsonDocumentheader = json_encode($rdDoc, TRUE);
                       $scrVar = new processDebriefWithUomScreens();
                       $a = $scrVar->showSelectedRddDocument($jsonDocumentheader);  
                       
                  } else { ?>
                       <script type='text/javascript'>parent.showMsgBoxError("Document Number Not Found<BR>")</script> 
                       <?php
                       unset($_POST['DELPARTIAL']); 
                       unset($_POST['DELFULL']);
                  }   	     

  	  	     } else { ?>
                    <script type='text/javascript'>parent.showMsgBoxError("Document Number Format Wrong<BR><BR> Must be 'Principal' - 'Document Number'<BR>")</script> 
                    <?php
                    unset($_POST['DELPARTIAL']); 
                    unset($_POST['DELFULL']);             	
             }
 	  }   	   
 	  if(!isset($_POST['DELFULL']) && !isset($_POST['DELPARTIAL']) && !isset($_POST['REDELIVERY'])) {
            $scrVar = new processDebriefWithUomScreens();
            $a = $scrVar->getDocumentNumber();   
    } 	
      ?>
      
  </body>

    <?php  
 function test_input($data) {

      $data = trim($data);
      $data = stripslashes($data);
      $data = htmlspecialchars($data);
      if($data=='') { $data = 0; } 
    
      return $data;  
}      
/*  

<body style="background:url('<?php echo $DHTMLROOT.$PHPFOLDER ?>images/scan-background.jpg'); background-repeat:no-repeat; background-size:100% 100%; width:100%;height:100%;">

<div style="background:rgba(255,255,255,0.9); width:100%;height:100%;">

<table class="tableReset" style="width:100%;">
   </tr>
      <td>&#160;</td>
      <td style="width:30%;">
         <p>Please enter the Invoice Document Number, or use the scanner</p>

      </td>
      <td>&#160;</td>
   </tr>
</table>

</div>
*/