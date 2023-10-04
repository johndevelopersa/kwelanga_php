<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER.'DAO/MaintenanceDAO.php');
    include_once($ROOT.$PHPFOLDER."libs/GUICommonUtils.php");
   
class userRole {
	
      function __construct() {

         global $dbConn;
         $this->dbConn = $dbConn;
      }	
// ********************************************************************************************************************************	
  public function firstform($userUId, $principalId) {
  	
         $MaintenanceDAO = new MaintenanceDAO($this->dbConn);
         $uOrg = $MaintenanceDAO->getUseOrganisation(mysqli_real_escape_string($this->dbConn->connection, $userUId ));
  	
         $MaintenanceDAO = new MaintenanceDAO($this->dbConn);
         $mfUser = $MaintenanceDAO->getUseOrganisationUsers(mysqli_real_escape_string($this->dbConn->connection, $principalId),
                                                            mysqli_real_escape_string($this->dbConn->connection, $uOrg[0]['organisation_name']));  	
  	  ?>
       <center>
       <FORM name='Select User' method=post action='userRole.php'>
            <table width="720"; style="border:none">
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                  <td class=head1 Colspan="5"; style="text-align:center";>Select User Role to Amend</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                  <td Colspan="5">&nbsp</td>
               </tr>        	
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td width="38%"; style="border:none">&nbsp</td>
                 <td width="20%"; style="border:none">&nbsp</td>
                 <td width="20%"; style="border:none">&nbsp</td>
                 <td width="20%"; style="border:none">&nbsp</td>
                 <td width="2%" ; style="border:none">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                 <td class="det1" style="text-align:center";>Select User</td>
                 <td colspan="4"; style="text-align:left;">
                     <select name="USERID" id="USERID">
                           <option value="Select a User "><?php echo 'Select a User' ?></option>
                                 <?php foreach($mfUser as $row) { ?>
                                       <option value="<?php echo trim($row['user_id']) ; ?>"><?php echo $row['username']; ?></option>
                                 <?php } ?>
                     </select>
                 </td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="5">&nbsp</td>
               </tr>
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="firstform" value= "Get User Roles">
                                                             <INPUT TYPE="submit" class="submit" name="canform"   value= "Cancel"></td>
               </tr>          
               <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                 <td Colspan="5">&nbsp</td>
               </tr>  
 			    	</table>
		   </form>
    </center>
    <?php     
  } 	
	
// ********************************************************************************************************************************	
  public function roleform($principalId, $postUserId) {
  	
         $MaintenanceDAO = new MaintenanceDAO($this->dbConn);
         $uOrg = $MaintenanceDAO->getUserRoles(mysqli_real_escape_string($this->dbConn->connection, $principalId),
                                               mysqli_real_escape_string($this->dbConn->connection, $postUserId));
                                               
         
         if (count($uOrg) > 0) { ?>
               <center>
                  <form name='Manage Roles' method=post action='userRole.php'>
                      <table width="720"; style="border:none">
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            <td class=head1 Colspan="5"; style="text-align:center;">Manage Allowed Roles</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            <td Colspan="5">&nbsp</td>
                        </tr>    	
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                           <td style="border:none">&nbsp</td>
                           <td width="20%"; style="border:none">&nbsp</td>
                           <td width="25%"; style="border:none">&nbsp</td>
                           <td width="15%"; style="border:none">&nbsp</td>
                           <td width="20%"; style="border:none">&nbsp;</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                           <td style="border:none; float: center;">&nbsp;</td>
                           <td class="det1" style="text-align:left;">Role</td>
                           <td style="border:none; float: center;">&nbsp;</td>
                           <td class="det1" style="text-align:left;">Select<br><a href="javascript:;" onClick="selectAll('HASROLE[]', 1);">All</a>&nbsp;|&nbsp;<a href="javascript:;" onClick="selectAll('HASROLE[]', 0);">None</a></td>
                           <td style="border:none; float: center;">&nbsp;</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            <td Colspan="5">&nbsp</td>
                        </tr> 
                        <?php 
                        foreach($uOrg as $rr) { 
                            if($rr['user_id'] == NULL) {
                               $rcheck = '';
                            } else {
                               $rcheck = 'CHECKED';
                        	  }?>
                            <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                                <td style="text-align:left";>&nbsp;</td>
                                <td style="text-align:left";><?php echo $rr['RoleDesc'];?></td>
                                <td style="text-align:left";><input type="hidden" name="USERID" value=<?php echo mysqli_real_escape_string($this->dbConn->connection, $postUserId); ?>></td>
                                <td style="text-align:left";><INPUT TYPE="checkbox" name="HASROLE[]" value= "<?php echo $rr['roleId'];?>" <?php echo $rcheck;?>></td>
                                <td style="text-align:left";><input type="hidden" name="ROLELIST[]" value=<?php echo $rr['roleId']; ?>></td>
                            </tr>
                            <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                <td>&nbsp</td>
                                <td>&nbsp</td>
                                <td>&nbsp</td>
                                <td>&nbsp</td>
                                <td>&nbsp</td>
                            </tr>
                        <?php	
                        } ?>      	               
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                           <td Colspan="5">&nbsp</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            <td Colspan="5" style="text-align:center";><INPUT TYPE="submit" class="submit" name="roleform" value= "Modify Roles">
                                                                     <INPUT TYPE="submit" class="submit" name="canform"     value= "Cancel"></td>
                        </tr>               
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                           <td Colspan="5">&nbsp</td>
                         </tr>
                      </table>
                  </form>
               </center>
    <?php
         } else { ?>
              <script type='text/javascript'>parent.showMsgBoxError('No Roles Available')</script> 
              <?php 
              unset($postINVOICE);
              unset($_POST['firstform']);
         }
?>
   <script type="text/javascript" defer>
       function selectAll(elementName, flag) {
           $("input[name='"+elementName+"']").each( function(){$(this).attr("checked",((flag == 1)?true:false));})
       }
  </script>

<?php
  }
}
 
// ********************************************************************************************************************************	
