<?php
    include_once('ROOT.php');
    include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER.'DAO/EmployeeDAO.php');
    include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');

class JobDescriptionMaintenanceScreens {

      function __construct() {

         global $dbConn;
         $this->dbConn = $dbConn;
      }
// ********************************************************************************************************************************

   public function firstform() {
      ?>
      
          <center>
              <form name='Add a Job to Job List' method=post action=''>
                   <table width="720"; style="border:none">
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td class=head1 Colspan="5"; style="text-align:center">Job Description Maintenance</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td style="width:5%; border:none">&nbsp</td>
                          <td width="30%"; style="border:none">&nbsp</td>
                          <td width="30%"; style="border:none">&nbsp</td>
                          <td width="30%"; style="border:none">&nbsp</td>
                          <td width="3%" ; style="border:none">&nbsp</td>
                        </tr>
                        
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                               <td Colspan="5">&nbsp</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                        	<td Colspan="1">&nbsp</td>
                        	<td colspan="1"; style="text-align:right; font-weight: bold;">Job Description</td></td>
                          <td colspan="2"; style="text-align:left;"><input type="text" name="EJOB" value=""></td>
                          <td colspan="1"; style="text-align:left">&nbsp;</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                        </tr>

                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                             <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="SUBEMPUPD" value= "Submit Details ">
                                                                         <INPUT TYPE="submit" class="submit" name="BACKFORM"   value= "Back"></td>
                        </tr>
                   </table>
                  
             </form>           
             
             
    <?php
    
   }
   
// ********************************************************************************************************************************      
   
  public function ModifyJobDetails($dbConn) { 
  	
  	      // Get Existing Jobs
  	     
          $EmployeeDAO = new EmployeeDAO($this->dbConn);
          $ejs = $EmployeeDAO->getEmployeeJobDes();
  
  	?>
  	
          	
    	
         <center>
              <form name='Functions' method=post target=''>	
                  <table width="720px"; style="border:none">
                      <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                           <td class="head1" colspan="5" style="text-align:center;font-weight: bold;">Job description to update</td>
                      </tr>    
                      <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                           <td width="10%"; style="border: none;">&nbsp;</td>
                           <td width="50%"; style="border: none;">&nbsp;</td>
                           <td width="18%"; style="border: none;">&nbsp;</td>
                           <td width="18%"; style="border: none;">&nbsp;</td>
                           <td width="4%";  style="border: none;">&nbsp;</td>
                      <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td colspan="1" style="border: none;">&nbsp</td>
                          <td class="head1" colspan="3" style="text-align:left; border: none; padding-left:5px;font-weight: bold;"><INPUT TYPE="submit" class="submit" name="SUBMITFILTER" value= "Submit Filter">&nbsp;
                                                                                <INPUT TYPE="submit" class="submit" name="CLEARFILTER"  value= "Clear Filter"></td>
                          
                          <td colspan="1" style="border: none;">&nbsp</td>
                      </tr>
                      <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                           <td colspan="5" style="text-align:center;">&nbsp;</td>
                      </tr>    

                      <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                          <td colspan="1" >&nbsp</td>
                          <td  class="det1" colspan="3" style="text-align:left; padding: 0px 5px 0px 5px;font-weight: bold;">Function</td>
                          
                         
                          <td colspan="1">&nbsp</td>
                      </tr>
                      <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                           <td colspan="5" >&nbsp;</td>
                      </tr>    
                      <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td colspan="1">&nbsp</td>
                          <td class="det1" colspan="1" style="text-align:left; padding: 0px 5px 0px 5px;" ;><INPUT TYPE="TEXT" size="20" name="TRUID"    value= "" ></td>
                          <td colspan="3">&nbsp</td>
                      </tr>
                      <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                           <td colspan="5" style="text-align:center;">&nbsp;</td>
                      </tr>  
                      <?php
                     ?>
                          <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                               <td  colspan="1">&nbsp</td>
                               <td  class="det3" colspan= "3" style="text-align:left; color:Red;font-weight: bold">No Function selected - Use filters</td>
                               <td  colspan="1">&nbsp</td>
                          </tr> 
                          <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                               <td  colspan="5" style="text-align:center;">&nbsp;</td>
                          </tr>
                      <?php        
                       
                          
                       ?>




                  </table>
		          </form>
		     </center>
       <?php
   	
   		
   		
   		   }
// ********************************************************************************************************************************      
  
  public function pickUpdateAction() { ?>
             <center>
                <form name='Maintain Employee' method=post action=''>
                   <table width="720"; style="border:none">
                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            <td style="width:10%; border:none;">&nbsp</td>
                            <td style="width:40%; border:none;">&nbsp</td>
                            <td style="width:40%; border:none;">&nbsp</td>
                            <td style="width:10%; border:none;">&nbsp</td>
                       </tr>
                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            <td class="head1" colspan="4"; style="text-align:center; padding: 0 15px 0 20px ; ">Job description maintenence</td>
                       </tr> 
                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            <td Colspan="4">&nbsp</td>
                       </tr>
                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            <td >&nbsp</td>
                            <td class="head1" colspan="1"; style="text-align:right; padding: 0 15px 0 20px ; "><label class="label" for="MODEMPSP">Modify&nbsp;job&nbsp;detail&nbsp;</label><input type="radio" name="MODEMPSP" onclick="javascript: submit()" value="MODIFY"></td>
                            <td class="head1" colspan="1"; style="text-align:right; padding: 0 20px 0 30px ; "><label class="label" for="ADDEMPSP">Add&nbsp;job&nbsp;detail&nbsp;</label><input type="radio" name="ADDEMPSP" onclick="javascript: submit()" value="ADD"></td>	
                            <td >&nbsp</td>    
                       </tr>
                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            <td Colspan="4">&nbsp;</td>
                       </tr>  
                       
                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                           <td Colspan="4">&nbsp</td>
                       </tr>  
                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                           <td Colspan="4">&nbsp</td>
                       </tr>  
                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>"> 
                           <td colspan="4"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="BACKFORM"   value= "Back">
                                                                       <INPUT TYPE="submit" class="submit" name="CANFORM"    value= "Cancel"></td>
                       </tr>          
                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="4">&nbsp</td>
                       </tr>
                   </table>
                </form>
             </center>   	   	

    <?php 
    }
    
// ********************************************************************************************************************************      
public function Modifyscreen2() {

	?>  	
       <center>
          <form name='Maintain Job Description' method=post action='' onload='setFocusTPselect()'>
             <table width="720"; style="border:none">
                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td style="width:30%; border:none;">&nbsp</td>
                     <td style="width:20%; border:none;">&nbsp</td>
                     <td style="width:20%; border:none;">&nbsp</td>
                     <td style="width:30%; border:none;">&nbsp</td>
                 </tr>
                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td class="head1" colspan="4"; style="text-align:center; padding: 0 15px 0 20px ; ">Update Job Description</td>
                 </tr> 
                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td Colspan="4">&nbsp</td>
                 </tr>
                 <?php
                 if($_POST['CURSTAT'] == "D") {
                    $dCheck = 'CHECKED';
                    $aCheck = '';
                 } else {
                    $dCheck = '';
                    $aCheck = 'CHECKED';
                 } ?>
                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td Colspan="1" >&nbsp</td>
                     <td class="det1" colspan="1" style="text-align:left; padding: 0 0px 0 0px ; ">Function Description</td>
                     <td Colspan="2" ><INPUT TYPE="TEXT" size="40" name="FNAME" id="FNAME" value="<?php echo trim(substr($_POST["SELMOD"],strpos($_POST["SELMOD"],"-")+1,50)); ?>"></td>
                 </tr>
                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td Colspan="4">&nbsp<input type="hidden" id="FUID" name="FUID" value= <?php echo trim(substr($_POST["SELMOD"],0,strpos($_POST["SELMOD"],"-"))); ?>></td>
                 </tr>
                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td Colspan="1">&nbsp</td>
                     
                      <td class="det2" colspan="1" style="text-align:right; border: none; padding: 0px 25px 0px 25px;"><input type="radio" id="ACTIVE"  name="FSTATUS" value="Y" <?php echo $aCheck; ?>><label class="label" for="ACTIVE">Active</td>
                      <td class="det2" colspan="1" style="text-align:right; border: none; padding: 0px 25px 0px 25px;"><input type="radio" id="DELETED" name="FSTATUS" value="N" <?php echo $dCheck; ?>><label class="label" for="DELETED">Deleted</label></td>
                     <td Colspan="1">&nbsp</td>
                 </tr>  
                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td Colspan="4">&nbsp</td>
                 </tr>
                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>"> 
                     <td colspan="4"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="UPDFUNCTION"   value= "Update Job Description">
                     	                                          <INPUT TYPE="submit" class="submit" name="backform"   value= "Back">
                                                                 <INPUT TYPE="submit" class="submit" name="canform"   value= "Cancel"></td>
                 </tr>          
                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td Colspan="4">&nbsp</td>
                 </tr>
             </table>
          </form>
       </center>
       <?php
	
	
	
		
	
	}
public function ModifyJobDetailsFiltered($dbConn, $filtersearch) { 
  	
  	      // Get Existing Jobs
  	     
          $EmployeeDAO = new EmployeeDAO($this->dbConn);
          $ejs = $EmployeeDAO->getEmployeeJobsFiltered($filtersearch);
  
  	?>
         <center>
              <form name='Functions' method=post target=''>	
                  <table width="720px"; style="border:none">
                      <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                           <td class="head1" colspan="5" style="text-align:center;font-weight: bold;">Job description to update</td>
                      </tr>    
                      <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                           <td width="10%"; style="border: none;">&nbsp;</td>
                           <td width="50%"; style="border: none;">&nbsp;</td>
                           <td width="18%"; style="border: none;">&nbsp;</td>
                           <td width="18%"; style="border: none;">&nbsp;</td>
                           <td width="4%";  style="border: none;">&nbsp;</td>
                      <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td colspan="1" style="border: none;">&nbsp</td>
                          <td class="det2" colspan="3" style="text-align:left; border: none; padding-left:5px;"><INPUT TYPE="submit" class="submit" name="SUBMITFILTER" value= "Submit Filter">&nbsp;
                                                                                <INPUT TYPE="submit" class="submit" name="CLEARFILTER"  value= "Clear Filter"></td>
                         
                          <td colspan="1" style="border: none;">&nbsp</td>
                      </tr>
                      <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                           <td colspan="5" style="text-align:center;">&nbsp;</td>
                      </tr>    

                      <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                          <td colspan="1" >&nbsp</td>
                          <td  class="det1" colspan="1" style="text-align:left; padding: 0px 5px 0px 5px;font-weight: bold;">Function</td>
                          <td  class="det1" colspan="2" style="text-align:center; padding: 0px 5px 0px 5px;font-weight: bold;">Select</td>
                          
                          <td colspan="1">&nbsp</td>
                      </tr>
                      <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                           <td colspan="5" >&nbsp;</td>
                      </tr>    
                      <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td colspan="1">&nbsp</td>
                          <td class="det1" colspan="1" style="text-align:left; padding: 0px 5px 0px 5px;" ;><INPUT TYPE="TEXT" size="20" name="TRUID"    value= "" ></td>
                          <td colspan="3">&nbsp</td>
                      </tr>
                      <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                           <td colspan="5" style="text-align:center;">&nbsp;</td>
                      </tr>  
                      <?php
                      if(count($ejs) == 0) { ?>
                          <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                               <td  colspan="1">&nbsp</td>
                               <td  class="det3" colspan= "3" style="text-align:left; color:Red;font-weight: bold;">No Function selected - Use filters</td>
                               <td  colspan="1">&nbsp</td>
                          </tr> 
                          <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                               <td  colspan="5" style="text-align:center;">&nbsp;</td>
                          </tr>
                      <?php        
                      } else {         
                     
                          foreach ($ejs as $row) {
                              echo $row['job_description'];?> 
                              <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                                   <td  colspan="1">&nbsp</td>
                                   <td class="detN12" colspan="1" style="text-align:left;"><?php echo $row['job_description'];?></td>
                                   <td class="detN12" colspan="2" style="text-align:center;"><INPUT TYPE="radio" name="SELMOD" onclick="javascript: submit()" value= "<?php echo $row['uid'] . "-" . $row['job_description'];?>"></td>
                                 
                                   <td  colspan="3">&nbsp<input type="hidden" id="CURSTAT" name="CURSTAT" value="<?php echo $row['status'];?>"></td>
                              </tr>
                              <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                                   <td  colspan="5" style="text-align:center;">&nbsp;</td>
                              </tr>
                          <?php  
                          
                          } 
                          
                      } ?>




                  </table>
		          </form>
		     </center>
       <?php
   	
   		
   		
   		   }



}


