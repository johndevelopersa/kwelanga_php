<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER."properties/Constants.php");
		include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
    include_once($ROOT.$PHPFOLDER."elements/datePickerElement.php");
    include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
    include_once "captureWarehouseDispatchNewScreens.php"
    		
?>
<!DOCTYPE html>
<HTML>
	<HEAD>

		<TITLE>Document Management</TITLE>

    <link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/1_kwelanga.css' rel='stylesheet' type='text/css'>
    <script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/jquery.js"></script>
    <script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/dops_global_functions.js"></script>

    <style>
    	td.head1 {font-weight:normal;
    		        font-size:2em;text-align:left; 
    		        font-family: Calibri, Verdana, Ariel, sans-serif; 
    		        padding: 0 150px 0 150px }
      
      td.det1  {border-style:none none none none; 
      	        border-color:DarkGray; 
      	        border-width:1px;
      	        border-collapse:collapse; 
      	        text-align: center; 
      	        font-weight: bold; 
      	        font-size: 15px;
      	        padding: 0 0px 0 0px  }

     td.det2  {border-style:none none none none; 
      	        border-color:DarkGray; 
      	        border-width:1px;
      	        border-collapse:collapse; 
      	        text-align: left; 
      	        font-weight: normal; 
      	        font-size: 15px;  }
    	
    	</style>

		</HEAD>
<body>

<?php

    $class = 'odd';

    if (!isset($_SESSION)) session_start() ;
      $userUId     = $_SESSION['user_id'] ;
      $principalId = $_SESSION['principal_id'] ;
      $depotId     = $_SESSION['depot_id'] ;
      $systemId    = $_SESSION["system_id"];
      $systemName  = $_SESSION['system_name'];
          
      if (isset($_POST["INVOICE"])) {
            $clnInvoice= test_input($_POST["INVOICE"]); 
            if(strpos($clnInvoice,'-') == FALSE) {
                 $postINVOICE = str_pad(ltrim($clnInvoice, '0'),8,'0',STR_PAD_LEFT);
            } else {
                 $postINVOICE = str_pad(ltrim(trim(substr($clnInvoice,strpos($clnInvoice,'-')+1,10)), '0'),8,'0',STR_PAD_LEFT) 	;
            }
      } else {
            $postINVOICE = '';
      }       
      //Create new database object
     $dbConn = new dbConnect(); $dbConn->dbConnection();
     $errorTO = new ErrorTO;
// *************************************************************************************************************************************
     if (isset($_POST['CAPTCANCEL'])) {
        return;
     }
// *************************************************************************************************************************************
     if(isset($_POST['SETWH'])) { 
           $_SESSION['depot_id'] = $_POST['WHID'];
           unset($_POST['SETWH']);
     }    
// *************************************************************************************************************************************
     if (isset($_POST['CTRDISPACH'])) {
     	
//        echo "PP";

          $AgedStockDAO = New AgedStockDAO($dbConn);
          $errorTO = $AgedStockDAO->SaveDispatchToTracking($principalId, $depotId, $userUId, $_POST['FNOBOXES'], $_POST['BOXLIST'], $_POST['DELBY'], $_POST['SREFERENCE'], $_POST['COMMENT']) ;
          
          //print_r($errorTO);
          
          
          if ($errorTO->type!=FLAG_ERRORTO_SUCCESS) {?>
                   <script type='text/javascript'>parent.showMsgBoxError('<?php echo $errorTO->description; ?><br>Contact Kwelanga Support')</script> 
                   <?php
                   return;
          } else {
          	       $returnMessages->description="Dispatch Successfully Saved<BR><BR><a href=functional/presentations/presentationManagement.php?TYPE=Delivery%20Note&DSTATUS=Invoiced&CSOURCE=T&FINDNUMBER={$errorTO->identifier} target='_blank' 'scrollbars=yes,width=350,height=200,resizable=yes'>[VIEW/PRINT Dispatch Note]";?>

                   <script 
                         type='text/javascript' >parent.showMsgBoxInfo("<?php echo $returnMessages->description;?>")
                   </script>
                   <?php
                   unset($_POST['NUMOFBOXES']);
                   unset($_POST['BOXNUMBER']);
                   unset($_POST["CAPTCONT"]);  
          }
     }     
// *************************************************************************************************************************************
     if (isset($_POST['NUMOFBOXES']) || isset($_POST['BOXNUMBER']) ) {
    	
          if(isset($_POST['NOBOXES'])) {
                  $boxTot = $_POST['NOBOXES'];
                  $boxArray = Array();
          } elseif(!isset($_POST['NOBOXES'])) {
                  $boxTot = $_POST['FNOBOXES'];
                  $prUid  = $_POST['PRINUID'];
                  $boxArray = explode(',', $_POST['BOXLIST']);
          } else {
                  $boxTot = 0;
                  $prUid  = $_POST['PRINUID'];
          }

/*        if(!is_numeric($boxTot) && ($boxTot) <= 0) { ?>
    	    	      <script type='text/javascript'>parent.showMsgBoxError('No of Boxes Blank or Invalid  - Start Again')</script>
                  <?php	
                  unset($_POST['NUMOFBOXES']);
                  unset($_POST['FINALFORM']);
          } else { */
                  if(isset($_POST['BOXNUMBER']) || count($boxArray) > 0) {
                	
                     if (isset($_POST["BOXNUMBER"])) $postBOXNUMBER = (test_input($_POST["BOXNUMBER"])); else $postBOXNUMBER = '';
                          if($postBOXNUMBER <> '') {
                          	     $AgedStockDAO = New AgedStockDAO($dbConn);
                                 $dupBox = $AgedStockDAO->checkDispatchedBoxNo($principalId, $postBOXNUMBER, "N");                                 
                                 if(count($dupBox) > 0) {
                                       $AgedStockDAO = New AgedStockDAO($dbConn);
                                       $dupBox = $AgedStockDAO->checkDispatchedBoxNo($principalId, $postBOXNUMBER, "Y");                          	  
                                       if(count($dupBox) == 0) { 
                                            if(!in_array($postBOXNUMBER, $boxArray)) {
           	    	                              $boxArray[] = $postBOXNUMBER; 
                                            } else { ?>
                                                <script type='text/javascript'>parent.showMsgBoxError('Box Number already Captured<BR><BR>Close before Continuing')</script>
                                                <?php	
           	    	                          } 
           	    	                     } else {?>
                                             <script type='text/javascript'>parent.showMsgBoxError('Box Number already Dispatched<BR><BR>Close before Continuing')</script>
           	    	                           <?php           	    	                	
           	    	                     }                      	
                                 } else {?>
                                       <script type='text/javascript'>parent.showMsgBoxError('Box Number Not Receipted into Warehouse<BR><BR>Close before Continuing')</script>
           	    	                     <?php  
                                 }
           	              } else { ?>
                              <script type='text/javascript'>parent.showMsgBoxError('Box Number Blank<BR><BR>Close before Continuing')</script>
                              <?php	
           	              }	      
                 }
                 $captureWarehouseDispatchNewScreens = new captureWarehouseDispatchNewScreens();
                 $a = $captureWarehouseDispatchNewScreens->boxNumberCapture($_POST['PRIN'],
                                                                     $_POST['PRINUID'], 
                                                                     $boxTot,
                                                                     implode(',', $boxArray));
/*          }       */                                                   
     }     
     
// *************************************************************************************************************************************
if(!isset($_POST['NUMOFBOXES']) && !isset($_POST['BOXNUMBER']) && !isset($_POST['CAPTCONT']) && $depotId <> 0) { 
        $captureWarehouseDispatchNewScreens = new captureWarehouseDispatchNewScreens();
        $a = $captureWarehouseDispatchNewScreens->firstform($principalId);              //  firstform = function contained in the class
}      
// *************************************************************************************************************************************
    if($_SESSION['depot_id'] == 0  && !isset($_POST['SETWH']) ) {
    	 
    	  $_SESSION['depot_id'] = 1;
    	  
        $captureWarehouseDispatchNewScreens = new captureWarehouseDispatchNewScreens();
        $a = $captureWarehouseDispatchNewScreens->selectWarehouse($userUId, $principalId) ;
    } ?>
// *************************************************************************************************************************************


	</body>       
 </HTML>
 
<?php
 function test_input($data) {

  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  
  return $data;
 }
?> 