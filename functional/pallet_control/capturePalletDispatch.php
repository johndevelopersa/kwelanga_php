<?php
    include_once('ROOT.php'); 
    include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
    include_once($ROOT.$PHPFOLDER."libs/GUICommonUtils.php");
    include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
    include_once($ROOT.$PHPFOLDER.'elements/basicSelectElement.php');	
    include_once($ROOT.$PHPFOLDER."DAO/PalletControlDAO.php");        
    include_once($ROOT.$PHPFOLDER."DAO/TripsheetDAO.php");        
    include_once($ROOT.$PHPFOLDER."DAO/PostNewTransactionRecordDAO.php");    
    include_once('capturePalletDispatchScreens.php');    

  if (!isset($_SESSION)) session_start() ;
      $userUId          = $_SESSION['user_id'] ;
      $principalId      = $_SESSION['principal_id'];
      $wareHouseCde     = $_SESSION['depot_id'];

      //Create new database object
      $dbConn  = new dbConnect(); 
      $dbConn->dbConnection();
      $errorTO = new ErrorTO;
      
//    echo $_SESSION['depot_id'];
//    echo "<br>";

      $TripsheetDAO = new TripsheetDAO($dbConn);
      $uTS = $TripsheetDAO->checkWarehouseUser($userUId);
      
      if($uTS[0]['category'] == 'D') {
             $PalletControlDAO = new PalletControlDAO($dbConn);
             $palDep = $PalletControlDAO->getPalletWarehouse($wareHouseCde);
             
//           print_r($palDep);
             
             $_SESSION['pallet_depot']      = $palDep[0]['pallet_depot'];
             $_SESSION['pallet_principal']  = $palDep[0]['pallet_principal'];

             $palletDepot      = $_SESSION['pallet_depot'];
             $palletPrincipal  = $_SESSION['pallet_principal'] ;  


      } else {
             $palletDepot      = $_SESSION['pallet_depot'];
             $palletPrincipal  = $_SESSION['pallet_principal'] ;            	
      }
/* if($userUId == 1932) {
       echo "<pre>";
        print_r($_SESSION);
      }  */
      


?>
<!DOCTYPE html>
<HTML>
   <HEAD>

		<TITLE>Pallet Dispatch Capture</TITLE>

    <link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/kos_standard_screen.css' rel='stylesheet' type='text/css'>
		<link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/1_kwelanga.css' rel='stylesheet' type='text/css'>
		<script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/jquery.js"></script>
		<script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/dops_global_functions.js"></script>

    <style>
       table.box {border:collapse;
                  border: 2px solid; 
       	          border-color: #990000; 
      	          background:   #fcecec }     
       .tooltip {
            position: relative;
            left: -50px;
            display: block;
            padding: 0px  0px 0px  0px'
        }

       .tooltip .tooltiptext {
            visibility: hidden;
              width: 200px;
            background-color: black;
            color: #fff;
            text-align: center;
            border-radius: 6px;
            padding: 5px 0;
            position: absolute;
            z-index: 1;
            bottom: 150%;
            left: 50%;
            margin-left: -60px;
       }

       .tooltip .tooltiptext::after {
            content: "";
            position: absolute;
            top: 100%;
            left: 50%;
            margin-left: -5px;
            border-width: 5px;
            border-style: solid;
            border-color: black transparent transparent transparent;
        }

        .tooltip:hover .tooltiptext {
            visibility: visible;
        }

    </style>

   </HEAD>
   <?php
    if(isset($_POST['CANFORM'])) { 
        return;
    }
    if(isset($_POST['SETWH'])) { 
    	
        $dash = strpos($_POST['WHID'],"-");         
        $doll = strpos($_POST['WHID'],"$"); 
        
        $_SESSION['depot_id']         = substr($_POST['WHID'],0,$dash);
        $_SESSION['pallet_depot']     = substr($_POST['WHID'],$dash+1,($doll-$dash-1));
        $_SESSION['pallet_principal'] = trim(substr($_POST['WHID'],$doll+1,5));

        unset($_POST['SETWH']);
    }

    if(isset($_POST['NAMEFILTER'])) {
    	   
    	   if($_POST['DDISPAT'] == 'TRANSPORTER') {
    	        $PalletControlDAO = new PalletControlDAO($dbConn); 
              $dispatchDetails = $PalletControlDAO->getTransporterDetails($_POST['UVALUE'], $wareHouseCde);
    	   } else {
    	        // Add Customer Selection Here
    	   }
    	            
         if(count($dispatchDetails) <> 0) {
               $capturePalletDispatchScreens = new capturePalletDispatchScreens();
               $a = $capturePalletDispatchScreens->SelectDispatch($dispatchDetails, $_POST['DDISPAT']);
               
                unset($_POST['CODEFILTER']);
                unset($_POST['FIRSTFORM']);    	
         } else { ?>
         	
         	      <script type='text/javascript'>parent.showMsgBoxError('Error! Check Filter No Rows Returned')</script>
                <?php	
                unset($_POST['NAMEFILTER']);
                unset($_POST['FIRSTFORM']);         	
         }      
    }
       
    if(isset($_POST['GETDISPATCH'])) {
    	
    	$tUid  = substr($_POST['DISID'],0, strpos($_POST['DISID'],'-'));
    	$tName = trim(substr($_POST['DISID'], strpos($_POST['DISID'],'-')+1,30)) ;
    	
      $capturePalletDispatchScreens = new capturePalletDispatchScreens();
      $a = $capturePalletDispatchScreens->capturePalletDispatch($tName, $tUid, $_POST['DTYPE']);
    }
    
    if(isset($_POST['SUBDET'])) {
    	
    	   // insert transctions into TT
    	   
    	   // Chep Pallet uid
    	   $chepPalUid = '154718';
    	   $chepPal    = '01';
    	   
         $PostNewTransactionRecordDAO = new PostNewTransactionRecordDAO($dbConn); 
         $errorTO = $PostNewTransactionRecordDAO->SaveTransactionToTracking($palletPrincipal,
                                                                            $palletDepot ,
                                                                            substr($_POST['RECPTID'],0,1) . '-' . $palletDepot , 
                                                                            $userUId,
                                                                            $chepPalUid,
                                                                            $_POST['NOPALLETS'],
                                                                            test_input($_POST['STRIPSHEET']),
                                                                            test_input($_POST['SCOMMENT']),
                                                                            substr($_POST['RECPTID'],0,1) . '-' . $_POST['EMDID']);
                                                                            
         if($errorTO->type!=FLAG_ERRORTO_SUCCESS)	{?>
                <script type='text/javascript'>parent.showMsgBoxError('Error! Inserting Record - Bomb Out')</script>
                <?php	
                return;
         } else {
         	    $recUid = $errorTO->identifier;
         }

//echo "<pre>";
//print_r( $errorTO);

echo "<br>";
        // Update pallet balances
        
        $PalletControlDAO = new PalletControlDAO($dbConn); 
        $errorTO = $PalletControlDAO->updatePalletBalances($palletPrincipal ,
                                                           $palletDepot,
                                                           $chepPalUid,
                                                           $chepPal, 
                                                           $_POST['RECPTID'], 
                                                           $_POST['EMDID'],
                                                           $_POST['NOPALLETS'],
                                                           0);
                                                                                       
         if($errorTO->type!=FLAG_ERRORTO_SUCCESS)	{?>
                <script type='text/javascript'>parent.showMsgBoxError('Error! Updadting Balance - Bomb Out')</script>
                <?php	
                print_r($errorTO);
                die();
         } else {?>
                <script type='text/javascript'>parent.showMsgBoxInfo('Pallet Transaction Successful<BR><a href="functional/presentations/view/pallet_dispatch_note_version1.php?DOCMASTID=<?php echo $recUid; ?>" target="_blank" "scrollbars=yes,width=350,height=200,resizable=yes">[VIEW/PRINT PALLET DISPATCH NOTE]')</script>

                <?php
         }

    }
// ***********************************************************************************************************************************************    
    if (!isset($_POST['FIRSTFORM']) && !isset($_POST['NAMEFILTER']) && !isset($_POST['GETDISPATCH']) && $wareHouseCde <> 0) {
    	
        $capturePalletDispatchScreens = new capturePalletDispatchScreens();
        $a = $capturePalletDispatchScreens->firstform();              //  firstform = function contained in the class

    } 
// ***********************************************************************************************************************************************    
    
    if($_SESSION['pallet_depot'] == 0 && !isset($_POST['SETWH']) && $_SESSION['user_category'] <> 'D' ) {
    	 
    	  $_SESSION['depot_id'] = 1;
    	  
        $capturePalletDispatchScreens = new capturePalletDispatchScreens();
        $a = $capturePalletDispatchScreens->selectWarehouse($userUId, $principalId) ;
    } ?> 
        
</HTML>

<?php
 function test_input($data) {

  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
    
  return $data;
 }
