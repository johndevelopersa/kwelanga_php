<?php 
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER."properties/Constants.php");
    include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
    include_once($ROOT.$PHPFOLDER."libs/CommonUtils.php");
    include_once($ROOT.$PHPFOLDER.'DAO/StockByCatDAO.php');    
    include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");

    include_once("StockByCatScreens.php");
   
   if (!isset($_SESSION)) session_start() ;
      $userUId      = $_SESSION['user_id'] ;
      $principalId  = $_SESSION['principal_id'] ;
      $wareHouseCde = $_SESSION['depot_id'] ;
      $systemName   = $_SESSION['system_name'] ;

      //Create new database object
      $dbConn = new dbConnect(); 
      $dbConn->dbConnection();
      $errorTO = new ErrorTO;

if(isset($_POST['BACKFORM'])) { 
     unset($_POST['SELECTCAT']);
     unset($_POST['SAVECOUNTS']);
}

//******************************************************************************************************************************************   	
if (isset($_POST['SAVECOUNTS'])){
	   
      $cat    = $_POST['CAT'];
      $count  = $_POST['myInput'];    //inputted counts         
      $prList = $_POST['PRUID'];      //product uid's   	       
      $loopc  = 0;  
       
      foreach ($prList as $row) {
	       
	         $StockByCatDAO = new StockByCatDAO($dbConn);
           $errorTO = $StockByCatDAO->Autosave($row, $cat, $count[$loopc], $userUId);
           
           if ($errorTO->type!=FLAG_ERRORTO_SUCCESS) { ?>
                   	<script type='text/javascript'>parent.showMsgBoxError('Failed To Update (SC001b)<br><br> Contact Kwelanga Support')</script>
                    <?php
                    return;
           }
           $loopc++;
        }


        $StockByCatScreens = new StockByCatScreens();
        $a = $StockByCatScreens->ProductList($cat, $userUId, $principalId, $wareHouseCde); 	
        
 //       unset($_POST['SELECTCAT']);                   
 //       unset($_POST['CAPTURECOUNT']);
 //       unset($_POST['PRINTLIST']);
 //       unset($_POST['SHOWNEG']);
 //       unset($_POST['SHOWPOS']);
 //       unset($_POST['SHOWALL']);
 //       unset($_POST['BACKCOUNT']);
                 
 }
//******************************************************************************************************************************************   	
if (isset($_POST['CLEARCOUNTS'])) {
	   
      $cat   = $_POST['CAT'];
      
      $StockByCatDAO = new StockByCatDAO($dbConn);
      $errorTO = $StockByCatDAO->AutoSaveClear($userUId,$cat);
      
      $StockByCatScreens = new StockByCatScreens();
      $a = $StockByCatScreens-> ProductList($cat, $userUId, $principalId, $wareHouseCde);; 	
                          
      unset($_POST['SAVECOUNTS']); 
}     
       
//**********************************************************START***************************************************************************
if (!isset($_POST['SELECTCAT']) && 
    !isset($_POST['CLEARCOUNTS']) && 
    !isset($_POST['CAPTURECOUNT']) &&
    !isset($_POST['SAVECOUNTS']) &&
    !isset($_POST['SHOWNEG']) &&
    !isset($_POST['SHOWPOS']) &&
    !isset($_POST['DISPLAYVARS']) && 
    !isset($_POST['BACKCOUNT'])){
       
     $StockByCatScreens = new StockByCatScreens(); 
     $a = $StockByCatScreens->CategorySelect($principalId); 	
      	
}  
//******************************************************************************************************************************************
if (isset($_POST['SELECTCAT'])){
    
    $cat = $_POST['CATEGORYDROP'];
    
    if($cat != 'Select Category') {
          $StockByCatScreens = new StockByCatScreens();
          $a = $StockByCatScreens->ProductList($cat, $userUId, $principalId, $wareHouseCde);	
    } else { ?>
          <script type='text/javascript'>parent.showMsgBoxError('No Category Selected')</script> 
          <?php
          
          unset($_POST['SELECTCAT']);
          unset($_POST['CATEGORYDROP']);
          
          $StockByCatScreens = new StockByCatScreens(); 
          $a = $StockByCatScreens->CategorySelect($principalId); 	 
    }         	      	
}
//******************************************************************************************************************************************
if (isset($_POST['DISPLAYVARS']) || isset($_POST['SHOWNEG']) || isset($_POST['SHOWPOS'])) {
    
       $prList = $_POST['PRUID'];
       $cat    = $_POST['CAT'];	
       $count  = $_POST['myInput'];    //inputted counts
       $loopc = 0; 
       
       if($_POST['DISPLAYVARS'] == 'Show Variances') {
            $selectVarType = 1;
       }
       if($_POST['SHOWNEG'] == 'Show Negative Variances') {
            $selectVarType = 2;
       }
       if($_POST['SHOWPOS'] == 'Show Positive Variances') {
            $selectVarType = 3;
       }
       
       $varianceList = array();
       
       $foundVariances = 'N';

       foreach ($prList as $row) {
    	
           $StockByCatDAO = new StockByCatDAO($dbConn);
           $errorTO = $StockByCatDAO->Autosave($row, $cat, $count[$loopc], $userUId);
           
           if ($errorTO->type!=FLAG_ERRORTO_SUCCESS) { ?>
                   	<script type='text/javascript'>parent.showMsgBoxError('Failed To Update (SC001) <br><br> Contact Kwelanga Support')</script>
                    <?php
           }	
	       
           $StockByCatDAO = new StockByCatDAO($dbConn);
           $varList = $StockByCatDAO->getStockVariances($principalId, 
                                                        $wareHouseCde, 
                                                        $row, 
                                                        $count[$loopc],
                                                        $selectVarType);
                                                        
           if($varList[0]['adjTyp'] > 0) {
                $foundVariances = 'Y';	
           }                                    
    
           $varianceList = array_merge($varianceList,$varList);
           $loopc++;
        } 
   
        $StockByCatScreens = new StockByCatScreens();
        $a = $StockByCatScreens-> DisplayVariances($varianceList, $foundVariances, $selectVarType);
   
}

//******************************************************************************************************************************************
if (isset($_POST['BACKCOUNT'])){
	
   $cat = $_POST['CAT'];   
    
    $StockByCatScreens = new StockByCatScreens();
    $a = $StockByCatScreens->ProductList($cat, $userUId, $principalId, $wareHouseCde); 	 
                 	      	
}

//******************************************************************************************************************************************
if (isset($_POST['ROLLOVER'])){
	
        $cat = $_POST['CAT'];
        $prnUid = $_POST['PRNID'];
        $depUid = $_POST['DEPID'];
         
        foreach(json_decode($_POST['VARLIST'] ,TRUE) as $row) {

        	      $cnt = trim(substr($row,strpos($row, '!') +1, 5));
        	      $prdUid = substr($row,0,strpos($row, '!'));
        	      
        	      $stockByCatDAO = new StockByCatDAO($dbConn);
        	      
        	      $errorTO = $stockByCatDAO->stockRolloverByProduct($cnt, $prnUid, $depUid,$prdUid);
        	      
        	      if ($errorTO->type!=FLAG_ERRORTO_SUCCESS) { ?>
                   	<script type='text/javascript'>parent.showMsgBoxError('RollOver Bomb Out (RO001) <br><br> Contact Kwelanga Support')</script>
                    <?php
                    break;
                }	 
        }        
        
        if ($errorTO->type==FLAG_ERRORTO_SUCCESS) { ?>
                   	<script type='text/javascript'>parent.showMsgBoxInfo('Category Rollover Successfull ') </script>
        <?php
        }	
        
        unset($_POST['SELECTCAT']);                   
        unset($_POST['CAPTURECOUNT']);
        unset($_POST['PRINTLIST']);
        unset($_POST['SHOWNEG']);
        unset($_POST['SHOWPOS']);
        unset($_POST['SHOWALL']);
        unset($_POST['BACKCOUNT']); 
                 	      	
}

//******************************************************************************************************************************************
if (isset($_POST['PRINTLIST'])) {
	
         $cat = $_POST['CATEGORYDROP'];
         
         if($cat != 'Select Category') { ?>
               <script type='text/javascript'>      	  	    
                     window.open('<?php echo $ROOT.$PHPFOLDER ?>functional/presentations/view/pdf_stock_count_sheet.php?CATEGORY=<?php echo $cat; ?>');

                     parent.showMsgBoxInfo('Stock Count Sheet Printing Successful')
               </script>
         <?php      
         } else { ?>
                <script type='text/javascript'>parent.showMsgBoxError('No Category Selected')</script> 
         <?php 
       }        	      	
}
//******************************************************************************************************************************************
 
if (isset($_POST['INCADJ']) || isset($_POST['DECADJ'])) {
	
        $cat = $_POST['CAT'];
        $prnUid = $_POST['PRNID'];
        $depUid = $_POST['DEPID'];
        
        echo $prnUid;
        echo "<br>";
        echo $depUid;
        echo "<br>";
        echo $cat;
        echo "<br>";
        echo $_POST['INCADJ'] . ' I' ;
        echo "<br>";
        echo $_POST['DECADJ'] . ' D';
        echo "<br>";
        
                           
        foreach(json_decode($_POST['VARLIST'] ,TRUE) as $row) {	
        
                $cnt = trim(substr($row,strpos($row, '!') +1, 5));
        	      $prdUid = substr($row,0,strpos($row, '!'));
        	      
        	      echo $prdUid . '   '. $cnt;
                echo "<br>";
        
        }	
        
        ?>
                <script type='text/javascript'>parent.showMsgBoxError('Auto Adjustments not yet available <br><br>Do adjustments manually')</script> 
         <?php 
        
}
//******************************************************************************************************************************************
 
if (isset($_POST['PRINTVARIANCES'])) {
	
print_r($_POST);

echo "<br><br><br>";
	
echo $_POST['VARLIST'];	

$cat          = $_POST['CAT'];
$principalId  = $_POST['PRNID'];
$wareHouseCde = $_POST['DEPID'];
$varType      = $_POST['VARTYPE'];
$varList      = $_POST['VARLIST'];

?>
<script type='text/javascript'>      	  	    
             window.open('<?php echo $ROOT.$PHPFOLDER ?>functional/presentations/view/stock_count_variances.php?CATEGORY=<?php echo $cat; ?>&PRIN=<?php echo $principalId; ?>&WH=<?php echo $wareHouseCde; ?>&VARTYPE=<?php echo $varType; ?>&VARLIST=<?php echo $varList; ?>);

             parent.showMsgBoxInfo('Variance List Printing Successful')
</script>
         <?php      

}
 
//******************************************************************************************************************************************
 
?>
     <!DOCTYPE html>
<HTML>
	<HEAD>

		<TITLE>Stock By Category</TITLE>

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