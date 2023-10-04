<?php

// https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/functional/maintenance/uploadAfile.php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
include_once($ROOT.$PHPFOLDER."properties/Constants.php");
include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
include_once($ROOT.$PHPFOLDER."libs/CommonUtils.php");
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER.'elements/basicSelectElement.php');
    

//Create new database object
$dbConn  = new dbConnect(); 
$dbConn->dbConnection();
$errorTO = new ErrorTO;

$postfile    = (isset($_POST["FILENAME"])) ? test_input($_POST["FILENAME"]) : ''; 

$posttrunc    = (isset($_POST["TRUNC"])) ? test_input($_POST["TRUNC"]) : 1;    

if (isset($_POST['firstform'])) {
	
	  if($postfile <> '') {
        $dirPath = "C:/inetpub/wwwroot/systems/kwelanga_system/ftp/uploads/" . trim(mysqli_real_escape_string($dbConn->connection, $postfile) ) ;	  	

       if(file_exists($dirPath)) {
        	     	   $content=file_get_contents($dirPath);
       	     	//     preg_replace('/[\xFF\xFE\xEF\xBB\xBF\xBD\x22\x00]/', '', $content);
       	     	     echo "<br>";
       	     	     echo(preg_replace('/[\xFF\xFE\xEF\xBB\xBF\xBD\x22\xA0\x96\x91\x92\x27\x00]/', '', $content));
                   file_put_contents($dirPath, preg_replace('/[\xFF\xFE\xEF\xBB\xBF\xBD\x22\xA0\x91\x94\x92\x96\x27\xE9\x00]/', '', $content));
       	
 /*      	
       	
       	echo "<br>";
        // Check file contents 
        
         $content=file_get_contents($filename);
    
    
}
Usage:

$filename="users/data/letter.txt";
$string_to_replace="US$";
$replace_with="Yuan";
replace_string_in_file($filename, $string_to_replace, $replace_with);
        
        
 */       
        
        
        
       	
       	echo "<br>";
       	
       	    if($posttrunc == 2) {
       	    	
       	    		echo "<br>";
       	    			echo "drop";
       	    			
       	    				echo "<br>";
       	    	
       	    	
       	    	
                 $bldsql = "DROP TABLE IF EXISTS file_upload_temp";
       
                 $result = $dbConn->dbQuery($bldsql);
       
                //******************************************************************************************************************************************************
       
                 $bldsql = "CREATE TABLE file_upload_temp (`FLD1`              VARCHAR(100)  NULL,
                                                           `FLD2`              VARCHAR(100)  NULL,
                                                           `FLD3`              VARCHAR(100)  NULL,
                                                           `FLD4`              VARCHAR(100)  NULL,
                                                           `FLD5`              VARCHAR(100)  NULL,
                                                           `FLD6`              VARCHAR(100)  NULL,
                                                           `FLD7`              VARCHAR(100)  NULL,
                                                           `FLD8`              VARCHAR(100)  NULL,
                                                           `FLD9`              VARCHAR(100)  NULL,
                                                           `FLD10`             VARCHAR(100)  NULL,
                                                           `FLD11`             VARCHAR(100)  NULL,
                                                           `FLD12`             VARCHAR(100)  NULL,
                                                           `FLD13`             VARCHAR(100)  NULL,
                                                           `FLD14`             VARCHAR(100)  NULL,
                                                           `FLD15`             VARCHAR(100)  NULL,
                                                           `FLD16`             VARCHAR(100)  NULL,
                                                           `FLD17`             VARCHAR(100)  NULL,
                                                           `FLD18`             VARCHAR(100)  NULL,
                                                           `FLD19`             VARCHAR(100)  NULL,
                                                           `FLD20`             VARCHAR(100)  NULL,
                                                           `FLD21`             VARCHAR(100)  NULL,
                                                           `FLD22`             VARCHAR(100)  NULL,
                                                           `FLD23`             VARCHAR(100)  NULL,
                                                           `FLD24`             VARCHAR(100)  NULL,
                                                           `FLD25`             VARCHAR(100)  NULL,
                                                           `FLD27`             VARCHAR(100)  NULL,
                                                           `FLD28`             VARCHAR(100)  NULL,
                                                           `FLD29`             VARCHAR(100)  NULL,
                                                           `FLD30`             VARCHAR(100)  NULL);";
                                   
                 $dtresult = $dbConn->dbQuery($bldsql);
       	    }
            //*************************************************************************************************************************************************
       
            $sql='LOAD DATA LOCAL INFILE "' . $dirPath . '" INTO TABLE file_upload_temp
                  FIELDS TERMINATED BY ","
                  OPTIONALLY ENCLOSED BY "\""
                  ESCAPED BY "\\\"
                  LINES TERMINATED BY "\\r\\n" 
                  IGNORE 1 LINES';	
                  
                  echo $sql;
       
            $errorTO = $dbConn->processPosting($sql,"");
            
            
            print_r($errorTO);
          
            if($errorTO->type == 'S') {
                  $dbConn->dbQuery("commit");   
                  
                  ?>
                    <script type='text/javascript'>parent.showMsgBoxInfo('File Successful Uploaded<br><br><?php echo $dirPath ?> <br>')</script> 
                    <?php 
                    unset($_POST['firstform']);
            } else {?>         
            	      <script type='text/javascript'>parent.showMsgBoxError('File has a problem <br><br>')</script> 
             <?php 
             echo "<pre>";
             print_r($errorTO);    
             unset($_POST['firstform']);
            }
            
       } else {?>
           <script type='text/javascript'>parent.showMsgBoxError('File does not exist <br><br><?php echo $dirPath ?> <br>')</script> 
             <?php 
             unset($_POST['firstform']);
       }	
    } else { ?>
           <script type='text/javascript'>parent.showMsgBoxError('File name cannot be blank <br>')</script> 
             <?php 
             unset($_POST['firstform']);	
	  }
} ?>
<!DOCTYPE html>
<HTML>
	<HEAD>

		<TITLE>Upload a file to Temp</TITLE>

		<link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/1_kwelanga.css' rel='stylesheet' type='text/css'>
		<script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/jquery.js"></script>
		<script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/dops_global_functions.js"></script>

    <style>
      td.head1 {font-weight:bold;
                font-size:17px;text-align:left; 
                font-family: Calibri, Verdana, Ariel, sans-serif; 
                padding: 0 150px 0 150px }
                
      td.det1  {border-style:none; 
                text-align: left; 
                font-weight: bold; 
                font-size: 13px;
                padding: 0 150px 0 150px  }
 
      td.det2  {border-style:none; 
                text-align: left;
                color: Red;
                font-weight: bold; 
                font-size: 13px;
                padding: 0 150px 0 150px  }
     	</style>

		</HEAD>
    <body>

    <?php
    if (isset($_POST['canform'])) {
         return;    
    } 
       
    if(!isset($_POST['firstform'])) {
        $class = 'odd';
        ?>
        <center>
             <FORM name='TestAndInsert' method=post action=''>
                <table width="720"; style="border:none">
                    <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        <td class=head1 colspan="6"; style="text-align:center";>Upload a file to Temp</td>
                    </tr>
                    <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        <td width="10%"; style="border:none">&nbsp</td>
                        <td width="10%"; style="border:none">&nbsp</td>
                        <td width="30%"; style="border:none">&nbsp</td>
                        <td width="15%"; style="border:none">&nbsp</td>
                        <td width="30%"; style="border:none">&nbsp</td>
                        <td width="5%"; style="border:none">&nbsp</td>
                    </tr>
                    <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        <td >&nbsp</td>
                        <td colspan="2"; class=det1;>Enter the file name to upload</td>
                        <td colspan="2"; style="text-align:left;"><input type="text" name="FILENAME" id="FILENAME" </td> 
                        <td colspan="1"; >&nbsp;</td>
                    </tr>
                    <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        <td Colspan="6">&nbsp</td>
                    </tr>
                    <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        <td Colspan="6" class=det2>Save file to FTP -> /ftp/uploads.. </td>
                    </tr>
                    <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        <td Colspan="6">&nbsp</td>
                    </tr>

                    <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        <td >&nbsp</td>
                        <td colspan="2"; class=det1;>Truncate Table First</td>
                        <td colspan="2"; style="text-align:left;"><?php $lableArr = array('NO','YES');
                                                                        $valueArr = array('1','2');
                                                                        BasicSelectElement::buildGenericDD('TRUNC', $lableArr,$valueArr, $postWBSTATUS, "N", "N", null, null, null);?>
                      </td>                        <td colspan="1"; >&nbsp;</td>
                    </tr>
                    <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        <td Colspan="6">&nbsp</td>
                    </tr>



                    <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                       <td colspan="6"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="firstform" value= "Process File">
                                                             <INPUT TYPE="submit" class="submit" name="canform"   value= "Cancel"></td>
                    </tr>          
                    <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                       <td Colspan="6">&nbsp</td>
                    </tr>
                </table>
             </FORM>   
        </center>
	  </body>       
</HTML>        
<?php
}
 function test_input($data) {

  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  
  return $data;
 }

?>  