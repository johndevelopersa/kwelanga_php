<?php
    include_once('ROOT.php'); 
    include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
    //($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
 
class repMaintenanceScreens {
	
      function __construct() {

         global $dbConn;
         $this->dbConn = $dbConn;
      }	
// ********************************************************************************************************************************      
    public function selectRepAction() { ?>
    	   <body>   
             <center>
                <form name='Maintain Reps' method=post action=''>
                   <table width="720"; style="border:none">
                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                           <td style="width:10%; border:none;">&nbsp</td>
                           <td style="width:40%; border:none;">&nbsp</td>
                           <td style="width:40%; border:none;">&nbsp</td>
                           <td style="width:10%; border:none;">&nbsp</td>
                       </tr>
                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                           <td class="head1" colspan="4"; style="text-align:center; padding: 0 15px 0 20px ; "> Rep Maintenance</td>
                       </tr> 
                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                           <td Colspan="4">&nbsp</td>
                       </tr>
                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                           <td >&nbsp</td>
                           <td class="head1" colspan="1"; style="text-align:right; padding: 0 15px 0 20px ; "><label class="label" for="MODSREP">Modify&nbsp;Rep&nbsp;Details&nbsp;</label><input type="radio" name="MODSLREP" onclick="javascript: submit()" value="MODIFY"></td>
                           <td class="head1" colspan="1"; style="text-align:right; padding: 0 20px 0 30px ; "><label class="label" for="ADDSREP">Add&nbsp;Sales&nbsp;Rep</label><input type="radio" name="ADDSLREP" onclick="javascript: submit()" value="ADD"></td>	
                           <td >&nbsp</td>    
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
    public function addNewRep() { ?>
    	   <body>   
          <center>
             <form name='Maintain Sales Reps' method=post action='' onload='setFocusTPselect()'>
                <table width="720"; style="border:none">
                    <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        <td style="width:10%; border:none;">&nbsp</td>
                        <td style="width:30%; border:none;">&nbsp</td>
                        <td style="width:50%; border:none;">&nbsp</td>
                        <td style="width:10%; border:none;">&nbsp</td>
                    </tr>
                    <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        <td class="head1" colspan="4"; style="text-align:center; padding: 0 15px 0 20px ; ">Add Sales Rep</td>
                    </tr> 
                    <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        <td Colspan="4">&nbsp</td>
                    </tr>                    
                    <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        <td Colspan="1" >&nbsp</td>
                        <td class="head1" colspan="1" style="text-align:right; padding: 0 15px 0 20px ; ">Rep Name </td>
                        <td Colspan="1" ><INPUT TYPE="TEXT" size="50" name="TREP" id="TREP" placeholder='New Sales Rep'></td>
                        <td Colspan="1" >&nbsp</td>
                    </tr>
                    <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        <td Colspan="4">&nbsp</td>
                    </tr>
                    <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        <td Colspan="1" >&nbsp</td>
                        <td class="head1" colspan="1" style="text-align:right; padding: 0 15px 0 20px ; ">Rep Email </td>
                        <td Colspan="1" ><INPUT TYPE="TEXT" size="50" name="RMAIL" id="RMAIL" placeholder='Email Address'></td>
                        <td Colspan="1" >&nbsp</td>
                    </tr>
                    <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                       <td Colspan="4">&nbsp</td>
                    </tr>  
                    <tr class="<?php echo GUICommonUtils::styleEO($class); ?>"> 
                        <td colspan="4"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="ADDNEWREP"   value= "Add New Rep">
                        	                                          <INPUT TYPE="submit" class="submit" name="BACKFORM"   value= "Back">
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
    public function repFilter($mfDD) { ?>
    	
         <center>
              <form name='Rep Filter' method=post target=''>	
                  <table width="720px"; style="border:none">
                      <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                           <td class="det1" colspan="5" style="text-align:center;">Sales Rep to Edit</td>
                      </tr>    
                      <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                           <td width="10%"; style="border: none;">&nbsp;</td>
                           <td width="50%"; style="border: none;">&nbsp;</td>
                           <td width="18%"; style="border: none;">&nbsp;</td>
                           <td width="18%"; style="border: none;">&nbsp;</td>
                           <td width="4%";  style="border: none;">&nbsp;</td>
                      <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td colspan="1" style="border: none;">&nbsp</td>
                          <td class=det2 colspan="1" style="text-align:left; border: none; padding-left:5px;"><INPUT TYPE="submit" class="submit" name="SUBMITFILTER" value= "Submit Filter">&nbsp;
                                                                                <INPUT TYPE="submit" class="submit" name="CLEARFILTER"  value= "Clear Filter"></td>
                          <td class="head1" colspan="1" style="text-align:left; border: none; padding: 0px 5px 0px 5px;"><input type="radio" id="ACTIVE"  name="STATUS" value="A" CHECKED ><label class="label" for="ACTIVE">Active</label></td>
                          <td class="head1" colspan="1" style="text-align:left; border: none; padding: 0px 5px 0px 5px;"><input type="radio" id="DELETED" name="STATUS" value="D"><label class="label" for="DELETED">Deleted</label></td>
                          <td colspan="1" style="border: none;">&nbsp</td>
                      </tr>
                      <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                           <td colspan="5" style="text-align:center;">&nbsp;</td>
                      </tr>    

                      <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                          <td colspan="1" >&nbsp</td>
                          <td  class="det1" colspan="1" style="text-align:left; padding: 0px 5px 0px 5px;">Rep</td>
                          <td  class="det1" colspan="1" style="text-align:center; padding: 0px 5px 0px 5px;">&nbsp</td>
                          <td  class="det1" colspan="1" style="text-align:right; padding: 0px 5px 0px 5px;">&nbsp</td>
                          <td colspan="1">&nbsp</td>
                      </tr>
                      <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                           <td colspan="5" >&nbsp;</td>
                      </tr>    
                      <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td colspan="1">&nbsp</td>
                          <td class="det1" colspan="1" style="text-align:left; padding: 0px 5px 0px 5px;" ;><INPUT TYPE="TEXT" size="20" name="TRUID" value= "" ></td>
                          <td colspan="3">&nbsp</td>
                      </tr>
                      <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                           <td colspan="5" style="text-align:center;">&nbsp;</td>
                      </tr>  
                      <?php
                      if(count($mfDD) == 0) { ?>
                          <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                               <td  colspan="1">&nbsp</td>
                               <td  class="det3" colspan= "3" style="text-align:left; color:Red;">No Rep selected - Use filters</td>
                               <td  colspan="1">&nbsp</td>
                          </tr> 
                          <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                               <td  colspan="5" style="text-align:center;">&nbsp;</td>
                          </tr>
                      <?php        
                      } else {         
                     
                          foreach ($mfDD as $row) { 
                          	
                              if($row['status'] == "D") {
                                 $ts = 'Deleted';
                              } else {
                                 $ts = 'Active';
                              } ?>
                              <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                                   <td  colspan="1">&nbsp</td>
                                   <td class="detN12" style="text-align:left;"><?php echo $row['first_name'];?></td>
                                   <td class="detN12" style="text-align:center;"><INPUT TYPE="radio" name="SELMOD" onclick="javascript: submit()" value= "<?php echo $row['uid'] . "-" . $row['first_name'];?>"></td>
                                   <td class="detN12" style="text-align:right;"><?php echo $ts; ?></td>
                                   <td  colspan="1">&nbsp<input type="hidden" id="CURSTAT" name="CURSTAT" value="<?php echo $row['status'];?>">
                                   	                     <input type="hidden" id="EM" name="EM" value="<?php echo $row['email_addr'];?>"></td>
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
// ********************************************************************************************************************************      
    public function modifySelected($repName, $repUid, $repStat, $repMail ) { ?>

      <center>
          <form name='Maintain Sales Rep' method=post action='' onload='setFocusTPselect()'>
             <table width="720"; style="border:none">
                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td style="width:30%; border:none;">&nbsp</td>
                     <td style="width:20%; border:none;">&nbsp</td>
                     <td style="width:20%; border:none;">&nbsp</td>
                     <td style="width:30%; border:none;">&nbsp</td>
                 </tr>
                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td class="head1" colspan="4"; style="text-align:center; padding: 0 15px 0 20px ; ">Update Sales Rep Details</td>
                 </tr> 
                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td Colspan="4">&nbsp</td>
                 </tr>
                 <?php
                 if($repStat == "D") {
                    $dCheck = 'CHECKED';
                    $aCheck = '';
                 } else {
                    $dCheck = '';
                    $aCheck = 'CHECKED';
                 } ?>
                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td Colspan="1" >&nbsp</td>
                     <td class="head1" colspan="1" style="text-align:left; padding: 0 0px 0 0px ; ">Rep Name </td>
                     <td Colspan="2" ><INPUT TYPE="TEXT" size="40" name="TNAME" id="TNAME" value= <?php echo $repName ; ?>></td>
                 </tr>
                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td Colspan="4">&nbsp</td>
                 </tr>
                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td Colspan="1" >&nbsp</td>
                     <td class="head1" colspan="1" style="text-align:left; padding: 0 0px 0 0px ; ">Email Address </td>
                     <td Colspan="2" ><INPUT TYPE="TEXT" size="40" name="REMAIL" id="REMAIL" value= <?php echo $repMail ; ?>></td>
                 </tr>
                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td Colspan="4">&nbsp</td>
                 </tr>
                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td Colspan="4">&nbsp<input type="hidden" id="TUID" name="TUID" value= <?php echo $repUid ; ?>></td>
                 </tr>
                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td Colspan="1">&nbsp</td>
                     
                      <td class="det2" colspan="1" style="text-align:right; border: none; padding: 0px 25px 0px 25px;"><input type="radio" id="ACTIVE"  name="NSTATUS" value="A" <?php echo $aCheck; ?>><label class="label" for="ACTIVE">Active</td>
                      <td class="det2" colspan="1" style="text-align:right; border: none; padding: 0px 25px 0px 25px;"><input type="radio" id="DELETED" name="NSTATUS" value="D" <?php echo $dCheck; ?>><label class="label" for="DELETED">Deleted</label></td>
                     <td Colspan="1">&nbsp</td>
                 </tr>  
                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                     <td Colspan="4">&nbsp</td>
                 </tr>
                 <tr class="<?php echo GUICommonUtils::styleEO($class); ?>"> 
                     <td colspan="4"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="UPDTRAN"   value= "Update Sales Rep">
                     	                                           <INPUT TYPE="submit" class="submit" name="BACKFORM"  value= "Back">
                                                                 <INPUT TYPE="submit" class="submit" name="CANFORM"   value= "Cancel"></td>
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



} 
?>

