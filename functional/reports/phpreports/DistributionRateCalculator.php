<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER."properties/Constants.php");
    include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
    include_once($ROOT.$PHPFOLDER.'elements/basicSelectElement.php');	
    include_once($ROOT.$PHPFOLDER."libs/CommonUtils.php");
    include_once($ROOT.$PHPFOLDER."elements/datePickerElement.php");
    include_once($ROOT.$PHPFOLDER.'DAO/TransportCostDAO.php');	    

    if (!isset($_SESSION)) session_start() ;
      $userUId     = $_SESSION['user_id'] ;
      $principalId = $_SESSION['principal_id'] ;
      $depotId     = $_SESSION['depot_id'] ;
      $systemId    = $_SESSION["system_id"];
      $systemName  = $_SESSION['system_name'];
      
      $postTransporter = (isset($_POST["Transporter"])) ? htmlspecialchars($_POST["Transporter"]) : ''; 
      $postSTARTDATE   = (isset($_POST["STARTDATE"])) ? htmlspecialchars($_POST["STARTDATE"]) :  CommonUtils::getUserDate(); 
      $postENDDATE     = (isset($_POST["ENDDATE"]))   ? htmlspecialchars($_POST["ENDDATE"])   :  CommonUtils::getUserDate(); 
      $postIGNACC      = (isset($_POST["IGNORACCOUNTS"])) ? test_input($_POST["IGNORACCOUNTS"])     :  "";       
      $postDP          = (isset($_POST["DP"])) ? test_input($_POST["DP"])     :  "0";
      $postMARGE       = (isset($_POST["MARGE"])) ? test_input($_POST["MARGE"])     :  "0";
    
      //Create new database object
      $dbConn = new dbConnect(); $dbConn->dbConnection();
if (isset($_POST['canform'])) {
    return;	
}

if (isset($_POST['firstform'])) {
	
	  $success = 'Y';
	
	  if($postTransporter == 'Transport Provider') {?>
    	    	<script type='text/javascript'>parent.showMsgBoxError("No Transport Provider Selected") </script> 
            <?php   unset($_POST['firstform']); 
            $success = 'N';   	
	  }
	  if($postDP == 0 && $success == 'Y') {?>
    	    	<script type='text/javascript'>parent.showMsgBoxError("No Current Diesel Price Entered") </script> 
            <?php   unset($_POST['firstform']); 
            $success = 'N';   	
	  }
	  if($postMARGE == 0 && $success == 'Y') {?>
    	    	<script type='text/javascript'>parent.showMsgBoxError("No Warehouse Rate Entered") </script> 
            <?php   unset($_POST['firstform']); 
            $success = 'N';   	
	  }	  	
		
    $TransportCostDAO = new TransportCostDAO($dbConn);
    $result = $TransportCostDAO->dropTempTable($userUId);
    
    $TransportCostDAO = new TransportCostDAO($dbConn);
    $result = $TransportCostDAO->createTempTable($userUId);
    
    if($success == 'Y') {
    	  $TransportCostDAO = new TransportCostDAO($dbConn);
    	  $result = $TransportCostDAO->insertPeriodTransactions($principalId, $postSTARTDATE, $postENDDATE, $userUId, $postTransporter);
    	  if($result <> 'S'){ ?>
    	    	<script type='text/javascript'>parent.showMsgBoxError("No Transactions loaded - Check Dates") </script> 
            <?php   unset($_POST['firstform']); 
            $success = 'N'; 
    	  }    
    }
    
    if($success == 'Y') {
    	  $TransportCostDAO = new TransportCostDAO($dbConn);
    	  $result = $TransportCostDAO->minimumChargeCalc($postTransporter, $userUId);
    	  if($result <> 'S') { ?>
    	    	<script type='text/javascript'>parent.showMsgBoxError("Min Charge Load Failed") </script> 
            <?php   unset($_POST['firstform']); 
            $success = 'N'; 
        }        
    }

    if($success == 'Y') {
        $TransportCostDAO = new TransportCostDAO($dbConn);
        $result = $TransportCostDAO->additionalKGCharge($postTransporter, $userUId);
    	  if($result <> 'S') { ?>
    	    	<script type='text/javascript'>parent.showMsgBoxError("Per KG Charge Load Failed") </script> 
            <?php   unset($_POST['firstform']); 
            $success = 'N'; 
        }        
    }

    if($success == 'Y') {
        $TransportCostDAO = new TransportCostDAO($dbConn);
        $result = $TransportCostDAO->documentCharge($postTransporter, $userUId);
    	  if($result <> 'S') { ?>
    	    	<script type='text/javascript'>parent.showMsgBoxError("Document Charge Load Failed") </script> 
            <?php   unset($_POST['firstform']); 
            $success = 'N'; 
        }        
    }

    if($success == 'Y') {
        $TransportCostDAO = new TransportCostDAO($dbConn);
        $result = $TransportCostDAO->backDoorCharge($postTransporter, $userUId);
    	  if($result <> 'S') { ?>
    	    	<script type='text/javascript'>parent.showMsgBoxError("Back Door Charge Load Failed") </script> 
            <?php   unset($_POST['firstform']); 
            $success = 'N'; 
        }        
    }

    if($success == 'Y') {
        $TransportCostDAO = new TransportCostDAO($dbConn);
        $result = $TransportCostDAO->dieselSurcharge($postTransporter, $userUId, $postDP);
        if($result <> 'S'){ ?>
    	      <script type='text/javascript'>parent.showMsgBoxError("Diesel Surcharge Load Failed - Check Current Price and Surcharge range") </script> 
            <?php   unset($_POST['firstform']); 
             $success = 'N'; 
        }     
    }
    
    if($success == 'Y') {
          $TransportCostDAO = new TransportCostDAO($dbConn);
          $result = $TransportCostDAO->warehouseCharge($postMARGE, $userUId);
          if($result <> 'S'){ ?>
    	        <script type='text/javascript'>parent.showMsgBoxError("Warehouse Charge Load Failed") </script> 
              <?php   unset($_POST['firstform']); 
               $success = 'N'; 
          }
     }    

    if($success == 'Y') {
          $TransportCostDAO = new TransportCostDAO($dbConn);
          $result = $TransportCostDAO->getdocumentsWithPallets($principalId, $postSTARTDATE, $postENDDATE, $userUId);
          if($result <> 'S'){ ?>
    	        <script type='text/javascript'>parent.showMsgBoxError("Pallet Load Failed") </script> 
              <?php   unset($_POST['firstform']); 
               $success = 'N'; 
          }
     }    
       
    if($success == 'Y') {
          $TransportCostDAO = new TransportCostDAO($dbConn);
          $result = $TransportCostDAO->calculateTotals($userUId);
          if($result <> 'S'){ ?>
    	        <script type='text/javascript'>parent.showMsgBoxError("Total Calculation 1 Failed") </script> 
              <?php   unset($_POST['firstform']); 
               $success = 'N'; 
          }
     }    
       
    if($success == 'Y') {
          $TransportCostDAO = new TransportCostDAO($dbConn);
          $result = $TransportCostDAO->calculatePercentage($userUId);
          if($result <> 'S'){ ?>
    	        <script type='text/javascript'>parent.showMsgBoxError("Total Calculation 2 Failed") </script> 
              <?php   unset($_POST['firstform']); 
               $success = 'N'; 
          }
     }           
       
     if($success == 'Y') {
          $TransportCostDAO = new TransportCostDAO($dbConn);
          $result = $TransportCostDAO->calculatePercentage($userUId);
          if($result <> 'S'){ ?>
    	        <script type='text/javascript'>parent.showMsgBoxError("Total Calculation 2 Failed") </script> 
              <?php   unset($_POST['firstform']); 
               $success = 'N'; 
          }
     }            
       
     if($success == 'Y') {
          $TransportCostDAO = new TransportCostDAO($dbConn);
          $result = $TransportCostDAO->calculateGrandTotals($userUId);
          if($result <> 'S'){ ?>
    	        <script type='text/javascript'>parent.showMsgBoxError("Total Calculation 3 Failed") </script> 
              <?php   unset($_POST['firstform']); 
               $success = 'N'; 
          }
     }            
         

    if($success == 'Y') {
        $sql = "select `Field1` as 'Document Number',
                       `Field2` as 'Store',
                       `Field3` as 'Cases',
                       `Field4` as 'Mass (KG)',
                       `Field15`as 'Pallets',
                       `Field5` as 'Excl. Value',
                        ta1.short_name as 'From Area',
                        ta2.short_name as 'To Area',
                       `Field8` as 'Minimum Charge',
                       `Field9` as 'Rate per KG',
                       `Field10` as 'Additional KGs',
                       `Field11` as 'Document Charge',
                       `Field12` as 'Back Door Charge',
                       `Field13` as 'Diesel Surcharge',
                       `Field14` as 'Warehouse Charge',
                       `Field16` as 'Total Charge',
                       `Field17` as 'Rate %'
                       
               from .temp_rate_11 t
               left join .transport_areas ta1 on t.Field6 = ta1.uid
               left join .transport_areas ta2 on t.Field7 = ta2.uid
               where 1
               order by Sort, ta2.uid, Field2 ;";

        $utresult = $dbConn->dbGetAll($sql);
        
        if (count($utresult) == 0) {
             echo "<br>";
             echo "No Lines found";
             return;
        }
        
            
        $csv_export = '';
        $csv_export.= "Document Number,,Cases,Mass (KG), Pallets,Excl. Value,From Area,To Area,Minimum Charge,Rate per KG,Additional KGs,Document Charge,Back Door Charge,Diesel Surcharge, Warehouse Charge, Total, Rate% \n";
           
        foreach ($utresult as $brow) {
            $csv_export.= implode(',',$brow) . " \n";
        }
        $fileName = "Cost Report.csv";

        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=\"".$fileName."\"");
        header("Content-Type: application/force-download");
        echo $csv_export;
        
        return;
    }
}



?>
<!DOCTYPE html>
<HTML>
	<HEAD>

		<TITLE>Document Selection</TITLE>

		<link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/1_kwelanga.css' rel='stylesheet' type='text/css'>
		<script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/jquery.js"></script>
		<script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/dops_global_functions.js"></script>

    <style>
    	td.head1 {font-weight:normal;
    		        font-size:2em;text-align:left; 
    		        font-family: Calibri, Verdana, Ariel, sans-serif; 
    		        padding: 0 150px 0 150px }
      
      td.det1  {border-style:solid none solid none; 
      	        border-color:DarkGray; 
      	        border-width:1px;
      	        border-collapse:collapse; 
      	        text-align: center; 
      	        font-weight: bold; 
      	        font-size: 15px;
      	        padding: 0 150px 0 150px  }

     td.det2  {border-style:solid solid solid none; 
      	        border-color:DarkGray; 
      	        border-width:1px;
      	        border-collapse:collapse; 
      	        text-align: left; 
      	        font-weight: normal; 
      	        font-size: 12px;  }
    	
    	</style>

		</HEAD>
    <body>
<?php
      
// ********************************************************************************************************************************************************      
     
if(!isset($_POST['firstform'])) {
    $TransportCostDAO = new TransportCostDAO($dbConn);
    $mfPR = $TransportCostDAO->getActiveTransporters();
    ?>
    <center>
       <FORM name='Select Distribution Parameters' method=post action=''>
            <table width="720"; style="border:none">
               <tr>
                 <td class=head1 >Select Distribution Parameters</td>
               </tr>
               <tr>
                 <td>&nbsp</td>
               </tr>	        	
            <table width="720"; style="border:none" >        	
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td width="38%"; style="border:none">&nbsp</td>
                 <td width="20%"; style="border:none">&nbsp</td>
                 <td width="20%"; style="border:none">&nbsp</td>
                 <td width="20%"; style="border:none">&nbsp</td>
                 <td width="2%" ; style="border:none">&nbsp</td>
               </tr>
               
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                  <td style="text-align:left";>Select Transport Provider : </td>
               	  <td colspan="4"; style="text-align:left;">
               	  	   <select name="Transporter" id="Transporter">
                           <option value="Transport Provider"><?php echo 'Select Service Provider' ?></option>
                                 <?php foreach($mfPR as $row) { ?>
                                       <option value="<?php echo trim($row['uid']); ?>"><?php echo $row['name'] ." - " . trim($row['address1']); ?></option>
                                 <?php } ?>
               	  	   </select>
               	  </td> 
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="5">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                  <td style="text-align:left";>Transaction Start Date : </td>
                  <td colspan=2; style="text-align:left"><?php DatePickerElement::getDatePickerLibs(); DatePickerElement::getDatePicker("STARTDATE",$postSTARTDATE); ?> </td>
                  <td colspan=2;>&nbsp</td>
              </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="5">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                  <td style="text-align:left";>Transaction End Date : </td>
                  <td colspan=2; style="text-align:left"><?php DatePickerElement::getDatePickerLibs(); DatePickerElement::getDatePicker("ENDDATE",$postENDDATE); ?> </td>
                 <td colspan=2;>&nbsp</td>
               </tr>               
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="5">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
               	 <td style="text-align:left";>Accounts to Exclude : </td>
                 <td Colspan="4"><textarea cols="20" name="IGNORACCOUNTS" rows="4"></textarea> </td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="5">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                 <td style="text-align:left";>Enter Currect Deisel Price</td>
                 <td colspan="4"; style="text-align:left"><input type="text" name="DP"></td>
               </tr>               
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="5">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                 <td style="text-align:left";>Enter Warehousing and management %</td>
                 <td colspan="4"; style="text-align:left"><input type="text" name="MARGE"></td>
               </tr>          
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="5">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="firstform" value= "Run Cost Report">
                                                             <INPUT TYPE="submit" class="submit" name="canform"   value= "Cancel"></td>
               </tr>          
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="5">&nbsp</td>
               </tr>  
 			    	</table>
		   </form>
    </center> 
<?php 
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