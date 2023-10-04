<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER.'libs/common.php');
    include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
    include_once($ROOT.$PHPFOLDER."elements/basicInputElement.php");
    include_once($ROOT.$PHPFOLDER.'DAO/AdministrationDAO.php');


?>
<!DOCTYPE html>
<HTML>
	<HEAD>

		<TITLE>Document Selection</TITLE>

		<link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/1_default.css' rel='stylesheet' type='text/css'>
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

    if (!isset($_SESSION)) session_start() ;
      $userUId     = $_SESSION['user_id'] ;
      $principalId = $_SESSION['principal_id'] ;
      $depotId     = $_SESSION['depot_id'] ;
      $systemId    = $_SESSION["system_id"];
      $systemName  = $_SESSION['system_name'];
      
      if (isset($_POST["TSNUMBER"])) $postTSNUMBER=test_input($_POST["TSNUMBER"]); else $postTSNUMBER = ''; 
      if (isset($_POST["DOCNUMBER"])) $postDOCNUMBER=test_input($_POST["DOCNUMBER"]); else $postDOCNUMBER = ''; 
      
      //Create new database object
      $dbConn = new dbConnect(); 
      $dbConn->dbConnection();

      $class = 'odd';
?>    
<center>
	 <FORM name='Select Invoice' method=post action=''>
        <table width:"100%"; style="border:none">
        	<tr>
        		<td class=head1 >Add Document to Tripsheet</td>
        		</tr>
        	<tr>
        		 <td>&nbsp</td>
        		</tr>	        	
        	<tr>
        		<td class=head1 style="font-weight:normal; font-size:1em">Choose the required parameters</td>
        		</tr>        	
         </table>
        <table width:"100%"; >
          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
           <td style="text-align:left";>Enter the Document Number&nbsp&nbsp</td>
           <td colspan="2"; style="text-align:left"><input type="text" id='DOCNUMBER' value= <?php echo($postDOCNUMBER); ?> >'<br></td>
           <td>&nbsp</td>
           <td>&nbsp</td>
          </tr>
          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
            <td>&nbsp</td>
            <td>&nbsp</td>
            <td>&nbsp</td>
            <td>&nbsp</td>
            <td>&nbsp</td>
          </tr>
        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
           <td style="text-align:left";>Enter Tripsheet Number&nbsp&nbsp&nbsp&nbsp</td>
           <td colspan="2"; style="text-align:left"><input type="text" id='TSNUMBER' value= <?php echo($postTSNUMBER); ?> ><br></td>
           <td>&nbsp</td>
           <td>&nbsp</td> 
          </tr>
        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
           <td Colspan="5">&nbsp</td>
          </tr>
          </tr>
       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
        	 <td colspan="5"; style="text-align:center;"><INPUT TYPE="button" class="submit" onClick='submitForm( <?php echo($principalId . ",".$userUId); ?>)' value= " Add to Tripsheet " </td>
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
 function test_input($data) {

  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  
  return $data;
 }
#--------------------------------------------------------------------------------------------------------------------------

?>

<script type='text/javascript' defer>

var alreadySubmitted=false;

function submitForm(principal_Id, user_Id) {

    if (alreadySubmitted) {
      return;
    }
    alreadySubmitted=true;

    var params ='PRINCIPALID='+principal_Id;
        params+='&USERID='+user_Id;
        params+='&DOCNUMBER='+document.getElementById("DOCNUMBER").value;
        params+='&TSNUMBER='+document.getElementById("TSNUMBER").value;

    params = params.replace(/'/g,'').replace(/"/g,''); // get rid of quotes which can upset the display element

    AjaxRefreshWithResult(params,
                          '<?php  echo $ROOT . $PHPFOLDER ?>functional/transaction/tripsheets/AddInvoiceToTripsheetSubmit.php',
                          'alreadySubmitted=false;  if (msgClass.type=="S") successCallback();',
                          'Request is processed...');

}

function successCallback() {}

</script> 