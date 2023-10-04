<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER.'elements/basicSelectElement.php');	
    include_once($ROOT.$PHPFOLDER."properties/Constants.php");
    include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
    include_once($ROOT.$PHPFOLDER.'DAO/StoreDAO.php');
    include_once($ROOT.$PHPFOLDER.'DAO/ManageOrdersDAO.php');
        
    if (!isset($_SESSION)) session_start() ;
      $userUId     = $_SESSION['user_id'] ;
      $principalId = $_SESSION['principal_id'] ;
      $depotId     = $_SESSION['depot_id'] ;
      $systemId    = $_SESSION["system_id"];
      $systemName  = $_SESSION['system_name'];
      
      if (isset($_POST["INVOICE"])) $postINVOICE=test_input($_POST["INVOICE"]); else $postINVOICE = ''; 

      if (isset($_POST["STSTATUS"])) $postSTSTATUS=test_input($_POST["STSTATUS"]); else $postSTSTATUS = '';      
            
      //Create new database object
      $dbConn = new dbConnect(); $dbConn->dbConnection();
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

// *******************************************************************************************************************************************

      $class = 'even';
      
      if (isset($_POST['CANFORM'])) {
          return;	
      }

      if (isset($_POST['BACK'])) {
           unset($_POST['FIRSTFORM']);
      }
      
// *******************************************************************************************************************************************
      if(isset($_POST['FIRSTFORM'])) {
	
             echo "Iam here";
             
             if (isset($_POST["INVOICE"])) $postINVOICE=test_input($_POST["INVOICE"]); else $postINVOICE = ''; 
             echo "<br>";
            echo $postINVOICE;
             
             
             
             $resetBatchCodeDAO= new resetBatchCodeDAO($this->dbConn);
             $batchCodeInfo = $resetBatchCodeDAO->getBatchCodeInfo($postINVOICE, $principalID);

             if (count($batchCodeInfo)!==0) { 
 
		         } else { ?>
                   <script type='text/javascript'>parent.showMsgBoxError('Nothing To Update<br><br>Contact Support')</script>
                  <?php
              
                   unset($_POST['FIRSTFORM']);
		         }             
	    }
      
// *******************************************************************************************************************************************


if(!isset($_POST['FIRSTFORM']) && !isset($_POST['finishform'])) { ?>
    <center>
       <FORM name='Select Invoice' method=post action=''>
            <table width="900"; style="border:none">
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                   <td class="head1" colspan="4"; style="text-align:center; padding: 0 15px 0 20px ; "><strong>Manage Batch Codes on Order</td>
                   	<td colspan="4">&nbsp</td>
             </tr>
             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                   <td Colspan="5">&nbsp</td>
             </tr>      	
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td class="det3" style="text-align:center; color:Red;">*Enter Required Document Numbers Seperated by Comma (,)</td>
                 <td colspan="4">&nbsp</td>
               </tr>        	
            </table>
            <table width="900"; style="border:none" >        	
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td width="15%"; style="border:none">&nbsp</td>
                 <td width="25%"; style="border:none">&nbsp</td>
                 <td width="30%"; style="border:none">&nbsp</td>
                 <td width="20%"; style="border:none">&nbsp</td>
                 <td width="10%" ; style="border:none">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                 <td style="text-align:right";><strong>Document&nbsp;Numbers:</td>
                 <td colspan="4"; style="text-align:left"><input type="text" size="100" name="INVOICE" placeholder= "Invoice Number"></td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="5">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                 <td style="text-align:right";>&nbsp;</td>
                 <td style="text-align:right";><strong>Product Code:</td>	
                 <td colspan="1"; style="text-align:left"><input type="text" size="20" name="PRODC" placeholder= "Product code"></td>
                 <td colspan="2"; style="text-align:left; color:Red;">*Enter 1 product or leave blank</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="5">&nbsp</td>
               </tr>

               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="FIRSTFORM" value= "Order Batch Codes">
                 	                                           <INPUT TYPE="submit" class="submit" name="BACK"   value= "Back">
                                                             <INPUT TYPE="submit" class="submit" name="CANFORM"   value= "Cancel"></td>
               </tr>          
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="5">&nbsp</td>
               </tr>  
 			    	</table>
		   </form>
    </center> 
	</body>       
 </HTML>
<?php 
}

// *******************************************************************************************************************************************

 function test_input($data) {

  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  
  return $data;
 }
?> 