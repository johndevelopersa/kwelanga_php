<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER."libs/GUICommonUtils.php");
    		    
class enterOtpClass {
	
      function __construct() {

         global $dbConn;
         $this->dbConn = $dbConn;
      }	
// ********************************************************************************************************************************	
  public function otpForm($hVar1, $hVar2, $hotP1, $otpVal, $otpCorrect, $oLabel, $oLabel1, $message=false) {
  	   global $ROOT; global $PHPFOLDER;
  	
  	   ?> 
    <center>
       <form name='OTP Request Form' method=post action=''>
        <table width="500"; style="border:none">
            <tr>
               <td width="20%";>&nbsp</td>
               <td width="20%";>&nbsp</td>
               <td width="20%";>&nbsp</td>
               <td width="20%";>&nbsp</td>
            </tr>
            <tr>
               <td Colspan="4">&nbsp</td> 	
            </tr>
            <tr>
               <td Colspan="1">&nbsp</td>
               <?php 
               if($otpCorrect <> 0) { ?>
                   <td Colspan="2" style="font-size:10px; color: red; font-weight: bold; font-style: italic; text-align:center;">OTP Incorrect - Try Again</td>
               <?php    
               } else { ?>
                   <td Colspan="2">&nbsp</td>
               <?php    
               } ?>
               <td Colspan="1">&nbsp</td>
                	
            </tr>
        </table>
        <table class="box" width="400";>
            <tr>
               <td width="5%";>&nbsp</td>
               <td width="30%";>&nbsp</td>
               <td width="30%";>&nbsp</td>
               <td width="30%";>&nbsp</td>                             
               <td width="5%"; style="border:collapse; border-none: 2px solid; border-color: #990000;">&nbsp</td> 
            <tr>
               <td Colspan="1" rowspan="3"><img src="<?php echo $ROOT.$PHPFOLDER.'images/error-icon-big.png'; ?>" style="width:60px; height:60px; float:left;" ></td> 	
               <td Colspan="3" style="font-size: 13px; font-weight: bold; text-align:center;"><?php echo $oLabel; ?> One Time Pin<br></td> 
               <td Colspan="1" style="border:collapse; border-none: 2px solid; border-color: #990000;">&nbsp</td> 
            </tr>
            <tr>
               <td Colspan="1" style="border:collapse; border-none: 2px solid; border-color: #990000;">&nbsp</td> 	
               <td Colspan="4" style="border:collapse; border-none: 2px solid; border-color: #990000;">&nbsp</td> 	
            </tr> 
           <tr>
               <td Colspan="1" style="border:collapse; border-none: 2px solid; border-color: #990000;">&nbsp</td> 	
               <td Colspan="3" style="font-size: 13px; font-weight: bold;  text-align:left;">Enter OTP<br><input type="text" name="CAPOTP" size="5" value= "<?php echo mysqli_real_escape_string($this->dbConn->connection, $otpVal); ?> "></td> 
               <td Colspan="1" style="border:collapse; border-none: 2px solid; border-color: #990000;">&nbsp</td> 
            </tr>       
            <tr>
               <td Colspan="1" style="border:collapse; border-none: 2px solid; border-color: #990000;">&nbsp</td> 	
               <td Colspan="4" style="border:collapse; border-none: 2px solid; border-color: #990000;">&nbsp</td> 	
            </tr>        
            <tr>
               <td Colspan="5"><input type="hidden" name="HVAR1" value=<?php echo mysqli_real_escape_string($this->dbConn->connection, $hVar1); ?>>
               	               <input type="hidden" name="HVAR2" value=<?php echo mysqli_real_escape_string($this->dbConn->connection, $hVar2); ?>>
               	               <input type="hidden" name="HOTP1" value=<?php echo mysqli_real_escape_string($this->dbConn->connection, $hotP1); ?>></td> 	
            </tr>       	
            <tr>
               <td Colspan="5"; style="text-align:center";><INPUT TYPE="submit" class="submit" name="RESETDOC" value= <?php echo $oLabel1; ?>>
               	                                           <INPUT TYPE="submit" class="submit" name="NEWOTP"  value= "Get New OTP">
                                                           <INPUT TYPE="submit" class="submit" name="CAPTCANCEL"  value= "Cancel"></td> 	
            </tr> 
            <tr>
               <td Colspan="5" style="border:collapse; border-none: 2px solid; border-color: #990000;">&nbsp</td> 	
            </tr> 
            <tr>
               <td Colspan="5" style="border:collapse; border-none: 2px solid; border-color: #990000;">&nbsp</td> 	
            </tr>
        </table>
        <?php
        if($message <> FALSE) {
        ?>	
        <table style="border:none; width: "400";>
             <tr>
                  <td width="10%";>&nbsp;</td>
                  <td width="80%";>&nbsp;</td>
                  <td width="10%";>&nbsp;</td>
             <tr>
             <tr>
                  <td>&nbsp;</td>
                  <td style="color: #FF0000; text-align:center";><?php echo $message; ?> </td>
                  <td>&nbsp;</td>
             <tr>
             <tr>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
             <tr>	
        <?php
        } ?>


       </form>
    </center>
<?php     
  }
}


// ********************************************************************************************************************************	
