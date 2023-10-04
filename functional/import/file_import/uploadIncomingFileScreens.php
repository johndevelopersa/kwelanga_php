<?php
    include_once('ROOT.php'); 
    include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER.'DAO/EmployeeDAO.php');
    include_once($ROOT.$PHPFOLDER.'DAO/ProcessFilesDAO.php');    
    include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
 
class uploadIncomingFileScreens {
	
      function __construct() {

         global $dbConn;
         $this->dbConn = $dbConn;
      }	

// ********************************************************************************************************************************      
      
   public function selectFileUploadType($uploadConfig) {
   	
        $configArr = json_decode($uploadConfig, true); ?>
   
       <center>	
         <FORM name='File Upload' method=post action='' enctype="multipart/form-data">
            <table width="50%"; style="border-style:none";>        	
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                  <td width="1%"; >&nbsp</td>
                  <td width="25%";>&nbsp</td>
                  <td width="60%";>&nbsp</td>
                  <td width="1%"; >&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                  <td class=head1 colspan="4"  style="text-align:center;" >Upload and Process Incoming File</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                  <td colspan="4" >&nbsp;</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                  <td colspan="1">&nbsp</td>      
                  <td colspan="1"; style="text-align:left; font-weight: bold"><?php echo 'Select Upload Type'; ?></td>
                  <td colspan="1"; style="text-align:left";>
                            <select name="UPLID" id="UPLID">
                                  <option value="Select Upload Type"><?php echo 'Select Upload Type'; ?></option>
                                          <?php foreach($configArr as $row) { ?>
                                                   <option value="<?php echo $row['update_file_type']; ?>"><?php echo trim($row['update_file_type']); ?></option>
                                             <?php
                                           } ?>
                            </select>
                  </td>
                  <td colspan="1">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                  <td colspan="4" >&nbsp;</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                  <td colspan="4" >&nbsp;</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
               	  <td>&nbsp</td>
                  <td colspan="2"  style="text-align:center;"><INPUT TYPE="submit" class="submit" name="FIRSTFORM" value= "Select Upload Type">
                  	                                          <INPUT TYPE="submit" class="submit" name="CCANCEL" value= "Start Again"></td>
                  <td>&nbsp</td>	                                          
               </tr>          
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                  <td colspan="4" >&nbsp;</td>
               </tr>  
            </table>
        </form>
    </center>
   
   
   <?php
        
   }
// ********************************************************************************************************************************      
      
   public function fileUploadDetails($uploadConfig, $userId='') {
   	
        $configArr = json_decode($uploadConfig, true); 

        $postFROMDATE  = CommonUtils::getUserDate();
?>
   
       <center>	
         <FORM name='File Upload' method=post action='' enctype="multipart/form-data">
            <table width="50%"; style="border-style:none";>        	
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                  <td width="2%"; >&nbsp</td>
                  <td width="40%";>&nbsp</td>
                  <td width="56%";>&nbsp</td>
                  <td width="2%"; >&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                  <td class=head1 colspan="4"  style="text-align:center;" >Upload and Process Incoming File</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                  <td colspan="4" >&nbsp;</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                  <td colspan="1">&nbsp</td>      
                  <td class="det1" colspan="1"; style="text-align:left; font-weight: bold"><?php echo 'Selected Upload Type'; ?></td>
                  <td colspan="1"; style="text-align:left";><?php echo $configArr[0]['update_file_type']; ?></td>
                  <td colspan="1">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                  <td colspan="4" >&nbsp;</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                  <td colspan="1">&nbsp</td>      
                  <td class="det1" colspan="1"; style="text-align:left; font-weight: bold"><?php echo 'File Type'; ?></td>
                  <td colspan="1"; style="text-align:left";><?php echo $configArr[0]['allowed_types']; ?></td>
                  <td colspan="1"><input type="hidden" name="FEXT"  value="<?php echo mysqli_real_escape_string($this->dbConn->connection, $configArr[0]['allowed_types']); ?>" </input>
                  	              <input type="hidden" name="FDLIM"  value="<?php echo mysqli_real_escape_string($this->dbConn->connection, $configArr[0]['delimiter']); ?>" </input>
                  	              <input type="hidden" name="FQUERY" value="<?php echo mysqli_real_escape_string($this->dbConn->connection, $configArr[0]['query_to_run']); ?>" </input>
                  	              <input type="hidden" name="FARRAY" value="<?php echo count($configArr); ?>" </input> 
                  	              <input type="hidden" name="FFTOT" value="<?php echo mysqli_real_escape_string($this->dbConn->connection, $configArr[0]['temp_table']); ?>" </input>
                  	              <input type="hidden" name="FHEAD" value="<?php echo mysqli_real_escape_string($this->dbConn->connection, $configArr[0]['header_row']); ?>" </input></td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                  <td colspan="4" >&nbsp;</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                  <td colspan="1">&nbsp</td>      
                  <td class="det1" colspan="1"; style="text-align:left; font-weight: bold"><?php echo 'File Delimiter'; ?></td>
                  <td colspan="1"; style="text-align:left";><?php echo $configArr[0]['delimiter']; ?></td>
                  <td colspan="1">&nbsp</td>
               </tr>

               <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                  <td colspan="4" >&nbsp;</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                   <td colspan="1">&nbsp</td>                           
                   <td class="det1" style="text-align:left; border: none;">Date</td>
                   <td class="det1" colspan="1" style="text-align:left; border: none;"><?php DatePickerElement::getDatePickerLibs(); DatePickerElement::getDatePicker("FROMDATE",$postFROMDATE); ?> </td>
                   <td colspan="1" >&nbsp;</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                  <td colspan="4" >&nbsp;</td>
               </tr> 
               <?php
               if($configArr[0]['update_file_type'] == 'Stock Count') {
               	      $FileUploadDAO = new FileUploadDAO($this->dbConn);
                      $depotList = $FileUploadDAO->getUserDepots($configArr[0]['principal_uid'], $userId); ?>
                      <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td colspan="1">&nbsp</td>                           
                          <td class="det1" style="text-align:left; border: none;">Warehouse</td>
                          <td colspan="1"; style="text-align:left";>
                            <select name="SDEPOT" id="SDEPOT">
                                  <option value="Select Warhouse"><?php echo 'Select Warhouse'; ?></option>
                                          <?php foreach($depotList as $drow) { ?>
                                                   <option value="<?php echo $drow['depUid']; ?>"><?php echo trim($drow['Warehouse']); ?></option>
                                             <?php
                                           } ?>
                            </select>
                           </td>
                           <td colspan="1" >&nbsp;</td>
                      </tr>
                      <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            <td colspan="4" >&nbsp;</td>
                      </tr> 
               <?php       
               }
               ?>
               
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                      <td class="det1" colspan="4" style="text-align:center";>Confirm File Fields</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                  <td colspan="4" >&nbsp;</td>
               </tr> 
               <?php
               $fcnt = 0;
               foreach($configArr AS $row) {
               	   $fcnt++; ?>
                   <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                   	  <td>&nbsp</td>
                      <td class="det2" style="text-align:right";><?php echo $row['column_name']; ?></td>
                      <td style="text-align:left";><input type="text" name="<?php echo 'FIELD' . trim($fcnt); ?>" Value = "<?php echo $row['csv_column']; ?>"></td>
                      <td><input type="hidden" name="<?php echo 'FIELDNAME' . trim($fcnt); ?>" value="<?php echo $row['column_name']; ?>" </input></td>
                   </tr>
                   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                      <td colspan="4" >&nbsp;</td>
                   </tr>
               	
               	
               <?php	
               } ?>
               <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                  <td colspan="4" >&nbsp;</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
               	  <td>&nbsp</td>
                  <td class="det1" style="text-align:left";>Select CSV File to Upload</td>
                  <td style="text-align:left";><input type="file" name="UFILE"></td>
                  <td>&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                  <td colspan="4" >&nbsp;</td>
               </tr> 


               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
               	  <td>&nbsp</td>
                  <td colspan="2"  style="text-align:center;"><INPUT TYPE="submit" class="submit" name="PROCESSFILE" value= "Upload and Process File">
                  	                                          <INPUT TYPE="submit" class="submit" name="CCANCEL" value= "Start Again"></td>
                  <td>&nbsp</td>	                                          
               </tr>          
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                  <td colspan="4" >&nbsp;</td>
               </tr>  
            </table>
        </form>
    </center>
   
   
   <?php
        
   }   

}

?> 