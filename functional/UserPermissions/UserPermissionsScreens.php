<?php
    include_once('ROOT.php');
    include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER.'DAO/WarehouseStoresDAO.php');
    include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');

class UserPermissionsScreens {

      function __construct() {

         global $dbConn;
         $this->dbConn = $dbConn;
      }
      
//******************************************************************************************************************************************
      public function SearchNewUser (){      
      ?>      
      <body  onload='setFocusToTextBoxF()'>
          <center>
              <FORM name='SELECT USER' method=post action=''>
                   <table width="720"; style="border:none">
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                      
                          <td class=head1 Colspan="5"; style="text-align:center">Search For User To Modify Permissions</td>
                        </tr>
                        <tr>
                         
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td width="5%"; style="border:none">&nbsp</td>
                          <td width="30%"; style="border:none">&nbsp</td>
                          <td width="30%"; style="border:none">&nbsp</td>
                          <td width="30%"; style="border:none">&nbsp</td>
                          <td width="5%" ; style="border:none">&nbsp</td>
                        </tr>
                        
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        	<td Colspan="2">&nbsp</td>
                        	<td colspan="1"; style="text-align:centre">Search Users :</td>
                          <td colspan="1"; style="text-align:centre";>&nbsp</td>
                         <td Colspan="5">&nbsp</td>
                          </tr>
                          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td colspan="2">&nbsp</td>
                          <td class="det1" colspan="2" style="text-align:left; padding: 0px 5px 0px 5px;" ;><INPUT TYPE="TEXT" size="20" name="SEARCHU"    value= "" ></td>
                          <td colspan="1">&nbsp</td>            
                         
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="SEARCH" value= "Search Users">&nbsp;&nbsp
                          	                                        
                          	                                          <INPUT TYPE="submit" class="submit" name="BACKFORM"   value= "Cancel"></td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                         </tr>
                   </table>
              </FORM>
          </center>
          </body>
      <?php   	
      }  	  	     	
//******************************************************************************************************************************************
      public function UserSelect($Search){
       
          $UserPermissionsDOA = new UserPermissionsDOA($this->dbConn);
          $Users = $UserPermissionsDOA->GetUsers($Search);
 	
 	
 	
 	  ?>
 	  <body  onload='setFocusToTextBoxF()'>
          <center>
              <FORM name='SELECT USER' method=post action=''>
                   <table width="720"; style="border:none">
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td class=head1 Colspan="5"; style="text-align:center">Select User</td>
                        </tr>
                        <tr>
                         
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td width="5%"; style="border:none">&nbsp</td>
                          <td width="30%"; style="border:none">&nbsp</td>
                          <td width="30%"; style="border:none">&nbsp</td>
                          <td width="30%"; style="border:none">&nbsp</td>
                          <td width="3%" ; style="border:none">&nbsp</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                        	<td Colspan="1">&nbsp</td>
                        	<td colspan="1"; style="text-align:right">Select User</td>
                          <td colspan="2"; style="text-align:left";>
                          	      	     <select name="USERID" id="USERID">
                                             <option value="Select User">Select User</option>
                                             <?php foreach($Users as $row) { ?>
                                                   <option value="<?php echo trim($row['uid']) . " - " . trim($row['username']); ?>"><?php echo trim($row['uid']) . " - " . trim($row['username']); ?></option>
                                             <?php
                                             } ?>
                                        </select>
                                        </td>
                          <td colspan="1"; style="text-align:left"></td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="SELECTUSER" value= "Modify User Permissions">&nbsp;&nbsp         
                          	                                          <INPUT TYPE="submit" class="submit" name="BACKFORM"   value= "BACK">  
                          	                                          </td>
                          	                                          
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                         </tr>
                   </table>
              </FORM>
          </center>
      </body>
 	  <?php      	
      }
//******************************************************************************************************************************************
     public function UserUpdateScreen($userID){
    

           $UserPermissionsDOA = new UserPermissionsDOA($this->dbConn);
           $User = $UserPermissionsDOA->UserDetails($userID);
           
           
           $Username = $User[0]['username'];
           $Email = $User[0]['user_email'];
           $Fullname = $User[0]['full_name'];
           $Cat = $User[0]['category'];
     
    	?>
    	 <body>
          <center>
              <FORM name='UserUpdateScreen' method=post action=''>
                   <table width="720"; style="border:none">
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td class=head1 Colspan="5"; style="text-align:center">Modify User Permissions</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td width="5%"; style="border:none">&nbsp</td>
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
                                 <td colspan="1"; style="text-align:right; font-weight: bold;">User Name :</td>
                                 <td colspan="1"; style="text-align:left;"><?php echo trim($Username);?></td>
                                 <td Colspan="1">&nbsp</td>
                                 <td Colspan="1">&nbsp</td> 
                         </tr>       
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                         </tr>
                        
                        	
                        	
                        	 	<input type="hidden" name="CAT" value=<?php echo mysqli_real_escape_string($this->dbConn->connection, $Cat); ?>>
                        	  <input type="hidden" name="USERID" value=<?php echo mysqli_real_escape_string($this->dbConn->connection, $userID); ?>>
                        	  
                          
                     
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                                 <td Colspan="1">&nbsp</td>
                                 <td colspan="1"; style="text-align:right; font-weight: bold;">Full Name :</td>
                                 <td colspan="1"; style="text-align:left;"><?php echo trim($Fullname);?></td>
                                 <td Colspan="1">&nbsp</td>
                                 <td Colspan="1">&nbsp</td> 
                         </tr>
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                        </tr>
                        
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                                 <td Colspan="1">&nbsp</td>
                                 <td colspan="1"; style="text-align:right; font-weight: bold;">Email Address :</td>
                                 <td colspan="1"; style="text-align:left;"><?php echo trim($Email);?></td>
                                 <td Colspan="1">&nbsp</td>
                                 <td Colspan="1">&nbsp</td> 
                         </tr>
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                        </tr>
                        <?php 
                        ?>                              
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                             <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="COPYD" value= "Copy Depots">
                             	                                           <INPUT TYPE="submit" class="submit" name="COPYR" value= "Copy Roles">
                                                                         <INPUT TYPE="submit" class="submit" name="BACKFORM"   value= "Back"></td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                             <td colspan="5"; style="text-align:center;">&nbsp;</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                         </tr>
                   </table>
              </FORM>
          </center>
      </body>
      <script type="text/javascript">
          function setFocusToTextBoxF() {
             document.getElementById("DEL").focus();
          }
      </script>
    <?php
    	}
//******************************************************************************************************************************************
      public function SearchCurrentUser ($userID,$Cat){      
      ?>      
      <body  onload='setFocusToTextBoxF()'>
          <center>
              <FORM name='SELECT USER' method=post action=''>
                   <table width="720"; style="border:none">
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                      
                          <td class=head1 Colspan="5"; style="text-align:center">Search For User To Copy From</td>
                        </tr>
                        <tr>
                         
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td width="5%"; style="border:none">&nbsp</td>
                          <td width="30%"; style="border:none">&nbsp</td>
                          <td width="30%"; style="border:none">&nbsp</td>
                          <td width="30%"; style="border:none">&nbsp</td>
                          <td width="5%" ; style="border:none">&nbsp</td>
                        </tr>
                        
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        	<td Colspan="2">&nbsp</td>
                        	<td colspan="1"; style="text-align:centre">Search Users :</td>
                          <td colspan="1"; style="text-align:centre";>&nbsp</td>
                         <td Colspan="5">&nbsp</td>
                          </tr>
                          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td colspan="2">&nbsp</td>
                          <td class="det1" colspan="2" style="text-align:left; padding: 0px 5px 0px 5px;" ;><INPUT TYPE="TEXT" size="20" name="SEARCHCU"    value= "" ></td>
                          <td colspan="1">&nbsp</td>            
                          <input type="hidden" name="CAT" value=<?php echo mysqli_real_escape_string($this->dbConn->connection, $Cat); ?>>
                          <input type="hidden" name="USERID" value=<?php echo mysqli_real_escape_string($this->dbConn->connection, $userID); ?>>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="SEARCHC" value= "Search Users">&nbsp;&nbsp
                          	                                        
                          	                                          <INPUT TYPE="submit" class="submit" name="BACKFORM"   value= "Cancel"></td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                         </tr>
                   </table>
              </FORM>
          </center>
          </body>
      <?php   	
      }  	  	     	     	     	
//******************************************************************************************************************************************
    public function CurrentUserSelect($SearchC,$Cat,$userID){
          
          
          $UserPermissionsDOA = new UserPermissionsDOA($this->dbConn);
          $Users = $UserPermissionsDOA->GetCUsers($SearchC,$Cat);
 	
 	
 	
 	  ?>
 	  <body  onload='setFocusToTextBoxF()'>
          <center>
              <FORM name='SELECT USER' method=post action=''>
                   <table width="720"; style="border:none">
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td class=head1 Colspan="5"; style="text-align:center">Select User</td>
                        </tr>
                        <tr>
                         
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td width="5%"; style="border:none">&nbsp</td>
                          <td width="30%"; style="border:none">&nbsp</td>
                          <td width="30%"; style="border:none">&nbsp</td>
                          <td width="30%"; style="border:none">&nbsp</td>
                          <td width="3%" ; style="border:none">&nbsp</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                        	<td Colspan="1">&nbsp</td>
                        	<td colspan="1"; style="text-align:right">Select User</td>
                          <td colspan="2"; style="text-align:left";>
                          	      	     <select name="USERID" id="USERID">
                                             <option value="Select User">Select User</option>
                                             <?php foreach($Users as $row) { ?>
                                                   <option value="<?php echo trim($row['uid']) . " - " . trim($row['username']); ?>"><?php echo trim($row['uid']) . " - " . trim($row['username']); ?></option>
                                             <?php
                                             } ?>
                                        </select>
                                        </td>
                          <td colspan="1"; style="text-align:left"></td>
                        </tr>
                        
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                        </tr>
                        <input type="hidden" name="NUSERID" value=<?php echo mysqli_real_escape_string($this->dbConn->connection, $userID); ?>>
                        
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="SELECTCUSER" value= "Select User">&nbsp;&nbsp         
                          	                                          <INPUT TYPE="submit" class="submit" name="BACKFORM"   value= "Back">  
                          	                                          </td>
                          	                                          
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                         </tr>
                   </table>
              </FORM>
          </center>
      </body>
 	  <?php      	
      }
//******************************************************************************************************************************************
      public function CopyDepots($CuserID,$NUserID){
    

           $UserPermissionsDOA = new UserPermissionsDOA($this->dbConn);
           $User = $UserPermissionsDOA->UserDetails($CuserID);
           
           $UserPermissionsDOA = new UserPermissionsDOA($this->dbConn);
           $NUser = $UserPermissionsDOA->UserDetails($NUserID);
           
           $UserPermissionsDOA = new UserPermissionsDOA($this->dbConn);
           $Cprincipal = $UserPermissionsDOA->Principals($CuserID);
           
           
           
           $NUsername = $NUser[0]['username'];
           
           
           
           $Username = $User[0]['username'];
           $Email = $User[0]['user_email'];
           $Fullname = $User[0]['full_name'];
           $Cat = $User[0]['category'];
           
      //. " - " . trim($row['del_point_name'])
    	?>
    	 <body>
          <center>
              <FORM name='UserUpdateScreen' method=post action=''>
                   <table width="720"; style="border:none">
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td class=head1 Colspan="5"; style="text-align:center">Copy Depots From User</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td width="5%"; style="border:none">&nbsp</td>
                          <td width="30%"; style="border:none">&nbsp</td>
                          <td width="30%"; style="border:none">&nbsp</td>
                          <td width="30%"; style="border:none">&nbsp</td>
                          <td width="3%" ; style="border:none">&nbsp</td>
                        </tr>
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                                 <td Colspan="1">&nbsp</td>
                                 <td colspan="1"; style="text-align:right; font-weight: bold;">User To Copy To :</td>
                                 <td colspan="1"; style="text-align:left;"><?php echo trim($NUsername);?></td>
                                 <td Colspan="1">&nbsp</td>
                                 <td Colspan="1">&nbsp</td> 
                         </tr>       
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                         </tr>
                         
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                                 <td Colspan="1">&nbsp</td>
                                 <td colspan="1"; style="text-align:right; font-weight: bold;">User Name :</td>
                                 <td colspan="1"; style="text-align:left;"><?php echo trim($Username);?></td>
                                 <td Colspan="1">&nbsp</td>
                                 <td Colspan="1">&nbsp</td> 
                         </tr>       
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                         </tr>
                        
                        	
                        	
                        	  <input type="hidden" name="NUSERID" value=<?php echo mysqli_real_escape_string($this->dbConn->connection, $NUserID); ?>>
                        	  <input type="hidden" name="CUSERID" value=<?php echo mysqli_real_escape_string($this->dbConn->connection, $CuserID); ?>>
                        	  
                          
                     
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                                 <td Colspan="1">&nbsp</td>
                                 <td colspan="1"; style="text-align:right; font-weight: bold;">Full Name :</td>
                                 <td colspan="1"; style="text-align:left;"><?php echo trim($Fullname);?></td>
                                 <td Colspan="1">&nbsp</td>
                                 <td Colspan="1">&nbsp</td> 
                         </tr>
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                        </tr>
                        
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                                 <td Colspan="1">&nbsp</td>
                                 <td colspan="1"; style="text-align:right; font-weight: bold;">Email Address :</td>
                                 <td colspan="1"; style="text-align:left;"><?php echo trim($Email);?></td>
                                 <td Colspan="1">&nbsp</td>
                                 <td Colspan="1">&nbsp</td> 
                         </tr>
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                        </tr>
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                         
                          <td colspan="5"; style="text-align:right;font-weight: bold;">Select Principal/Depot</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                        	
                        	
                          <td colspan="2"; style="text-align:left;font-weight: bold;">Depot</td>
                          
                          <td colspan="1"; style="text-align:centre;font-weight: bold;">Principal</td>
                          <td colspan="2"; style="text-align:right;font-weight: bold;">Select<br><a href="javascript:;" onClick="selectAll('select[]', 1);">All</a>
                                                                                                <a href="javascript:;" onClick="selectAll('select[]', 0);">None</a></td>
                        </tr>
                                   <?php

                         foreach ($Cprincipal as $row) {?>
                          <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                          <td Colspan="1">&nbsp</td>
                          <td class="detN12"colspan="1"; style="text-align:left;"><?php echo $row['depot_name'];?></td>
                          <td class="detN12"colspan="2"; style="text-align:left;"><?php echo $row['principal_name'];?></td>
                          
                          <td class="detN12" colspan="1";style="text-align:right;"><INPUT TYPE="checkbox" name="select[]" value= "<?php echo $row['uid'];?>"></td>     
                            
                          </TR>
                       <?php 	
                   } ?>   
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                         </tr>
                          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                         </tr>
                        
                        
                        <?php 
                        
                        ?>                              
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                             <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="COPYS" value= "Copy Selected Depot/Principal">
                             	                                           <INPUT TYPE="submit" class="submit" name="COPYA" value= "Copy All Depot's/Principals">
                                                                         <INPUT TYPE="submit" class="submit" name="BACKFORM"   value= "Back"></td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                             <td colspan="5"; style="text-align:center;">&nbsp;</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                         </tr>
                   </table>
              </FORM>
          </center>
      </body>
      <script type="text/javascript">
          function setFocusToTextBoxF() {
             document.getElementById("DEL").focus();
          }
      </script>
      <script type="text/javascript" defer>
     function selectAll(elementName, flag){
          $("input[name='"+elementName+"']").each( function(){$(this).attr("checked",((flag == 1)?true:false));})
     }
     function setFocusToTextBox(){
         document.getElementById("PRUID").focus();
     }    
</script>
    <?php
    	}


//******************************************************************************************************************************************
public function SearchCurrentUserR($userID,$Cat){      
      ?>      
      <body  onload='setFocusToTextBoxF()'>
          <center>
              <FORM name='SELECT USER' method=post action=''>
                   <table width="720"; style="border:none">
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                      
                          <td class=head1 Colspan="5"; style="text-align:center">Search For User To Copy From</td>
                        </tr>
                        <tr>
                         
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td width="5%"; style="border:none">&nbsp</td>
                          <td width="30%"; style="border:none">&nbsp</td>
                          <td width="30%"; style="border:none">&nbsp</td>
                          <td width="30%"; style="border:none">&nbsp</td>
                          <td width="5%" ; style="border:none">&nbsp</td>
                        </tr>
                        
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                        	<td Colspan="2">&nbsp</td>
                        	<td colspan="1"; style="text-align:centre">Search Users :</td>
                          <td colspan="1"; style="text-align:centre";>&nbsp</td>
                         <td Colspan="5">&nbsp</td>
                          </tr>
                          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td colspan="2">&nbsp</td>
                          <td class="det1" colspan="2" style="text-align:left; padding: 0px 5px 0px 5px;" ;><INPUT TYPE="TEXT" size="20" name="SEARCHCU"    value= "" ></td>
                          <td colspan="1">&nbsp</td>            
                          <input type="hidden" name="CAT" value=<?php echo mysqli_real_escape_string($this->dbConn->connection, $Cat); ?>>
                          <input type="hidden" name="USERID" value=<?php echo mysqli_real_escape_string($this->dbConn->connection, $userID); ?>>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="SEARCHCR" value= "Search Users">&nbsp;&nbsp
                          	                                        
                          	                                          <INPUT TYPE="submit" class="submit" name="BACKFORM"   value= "Cancel"></td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                         </tr>
                   </table>
              </FORM>
          </center>
          </body>
      <?php   	
      }  	  	     	     	     	

//******************************************************************************************************************************************
 public function CurrentUserSelectR($SearchC,$Cat,$userID){
          
          
          $UserPermissionsDOA = new UserPermissionsDOA($this->dbConn);
          $Users = $UserPermissionsDOA->GetCUsers($SearchC,$Cat);
 	
 	
 	
 	  ?>
 	  <body  onload='setFocusToTextBoxF()'>
          <center>
              <FORM name='SELECT USER' method=post action=''>
                   <table width="720"; style="border:none">
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td class=head1 Colspan="5"; style="text-align:center">Select User</td>
                        </tr>
                        <tr>
                         
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td width="5%"; style="border:none">&nbsp</td>
                          <td width="30%"; style="border:none">&nbsp</td>
                          <td width="30%"; style="border:none">&nbsp</td>
                          <td width="30%"; style="border:none">&nbsp</td>
                          <td width="3%" ; style="border:none">&nbsp</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                        	<td Colspan="1">&nbsp</td>
                        	<td colspan="1"; style="text-align:right">Select User</td>
                          <td colspan="2"; style="text-align:left";>
                          	      	     <select name="USERID" id="USERID">
                                             <option value="Select User">Select User</option>
                                             <?php foreach($Users as $row) { ?>
                                                   <option value="<?php echo trim($row['uid']) . " - " . trim($row['username']); ?>"><?php echo trim($row['uid']) . " - " . trim($row['username']); ?></option>
                                             <?php
                                             } ?>
                                        </select>
                                        </td>
                          <td colspan="1"; style="text-align:left"></td>
                        </tr>
                        
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                        </tr>
                        <input type="hidden" name="NUSERID" value=<?php echo mysqli_real_escape_string($this->dbConn->connection, $userID); ?>>
                        
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="SELECTCUSERR" value= "Select User">&nbsp;&nbsp         
                          	                                          <INPUT TYPE="submit" class="submit" name="BACKFORM"   value= "Back">  
                          	                                          </td>
                          	                                          
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                         </tr>
                   </table>
              </FORM>
          </center>
      </body>
 	  <?php      	
      }


//******************************************************************************************************************************************
 public function CopyRoles($CuserID,$NUserID){
    

           $UserPermissionsDOA = new UserPermissionsDOA($this->dbConn);
           $User = $UserPermissionsDOA->UserDetails($CuserID);
           
           $UserPermissionsDOA = new UserPermissionsDOA($this->dbConn);
           $NUser = $UserPermissionsDOA->UserDetails($NUserID);
           
           $UserPermissionsDOA = new UserPermissionsDOA($this->dbConn);
           $Cprincipal = $UserPermissionsDOA->Getprincipal($CuserID);
           
           
           
           $NUsername = $NUser[0]['username'];
           
           
           
           $Username = $User[0]['username'];
           $Email = $User[0]['user_email'];
           $Fullname = $User[0]['full_name'];
           $Cat = $User[0]['category'];
           
      //. " - " . trim($row['del_point_name'])
    	?>
    	 <body>
          <center>
              <FORM name='UserUpdateScreen' method=post action=''>
                   <table width="720"; style="border:none">
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td class=head1 Colspan="5"; style="text-align:center">Copy Roles From User</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td width="5%"; style="border:none">&nbsp</td>
                          <td width="30%"; style="border:none">&nbsp</td>
                          <td width="30%"; style="border:none">&nbsp</td>
                          <td width="30%"; style="border:none">&nbsp</td>
                          <td width="3%" ; style="border:none">&nbsp</td>
                        </tr>
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                                 <td Colspan="1">&nbsp</td>
                                 <td colspan="1"; style="text-align:right; font-weight: bold;">User To Copy To :</td>
                                 <td colspan="1"; style="text-align:left;"><?php echo trim($NUsername);?></td>
                                 <td Colspan="1">&nbsp</td>
                                 <td Colspan="1">&nbsp</td> 
                         </tr>       
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                         </tr>
                         
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                                 <td Colspan="1">&nbsp</td>
                                 <td colspan="1"; style="text-align:right; font-weight: bold;">User Name :</td>
                                 <td colspan="1"; style="text-align:left;"><?php echo trim($Username);?></td>
                                 <td Colspan="1">&nbsp</td>
                                 <td Colspan="1">&nbsp</td> 
                         </tr>       
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                         </tr>
                        
                        	
                        	
                        	  <input type="hidden" name="NUSERID" value=<?php echo mysqli_real_escape_string($this->dbConn->connection, $NUserID); ?>>
                        	  <input type="hidden" name="CUSERID" value=<?php echo mysqli_real_escape_string($this->dbConn->connection, $CuserID); ?>>
                        	  
                          
                     
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                                 <td Colspan="1">&nbsp</td>
                                 <td colspan="1"; style="text-align:right; font-weight: bold;">Full Name :</td>
                                 <td colspan="1"; style="text-align:left;"><?php echo trim($Fullname);?></td>
                                 <td Colspan="1">&nbsp</td>
                                 <td Colspan="1">&nbsp</td> 
                         </tr>
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                        </tr>
                        
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                                 <td Colspan="1">&nbsp</td>
                                 <td colspan="1"; style="text-align:right; font-weight: bold;">Email Address :</td>
                                 <td colspan="1"; style="text-align:left;"><?php echo trim($Email);?></td>
                                 <td Colspan="1">&nbsp</td>
                                 <td Colspan="1">&nbsp</td> 
                         </tr>
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                        </tr>
                        
                        
                        
                       
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                        	
                        	<td Colspan="1">&nbsp</td>
                          <td colspan="1"; style="text-align:left;font-weight: bold;">Entity ID</td>
                          
                          <td colspan="1"; style="text-align:centre;font-weight: bold;">Principal</td>
                          <td colspan="2"; style="text-align:right;font-weight: bold;">Select<br><a href="javascript:;" onClick="selectAll('select[]', 1);">All</a>
                                                                                                <a href="javascript:;" onClick="selectAll('select[]', 0);">None</a></td>
                        </tr>
                          <?php

                         foreach ($Cprincipal as $row) {?>
                          <tr class="<?php echo GUICommonUtils::styleEO($cl); ?>">
                          <td Colspan="1">&nbsp</td>
                          <td class="detN12"colspan="1"; style="text-align:left;"><?php echo $row['uid'];?></td>
                          <td class="detN12"colspan="2"; style="text-align:left;"><?php echo $row['name'];?></td>
                          
                          <td class="detN12" colspan="1";style="text-align:right;"><INPUT TYPE="checkbox" name="select[]" value= "<?php echo $row['uid'];?>"></td>     
                            
                          </TR>
                       <?php 	
                   } ?>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                        </tr>
                        
                       <?php 	
                   ?>   
                         <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                         </tr>
                          <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                         </tr>
                        
                        
                        <?php 
                        
                        ?>                              
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                             <td colspan="5"; style="text-align:center;">
                                                                         <INPUT TYPE="submit" class="submit" name="COPYSR" value= "Copy Selected Principal Roles">
                             	                                           <INPUT TYPE="submit" class="submit" name="COPYAR" value= "Copy All Roles">
                                                                         <INPUT TYPE="submit" class="submit" name="BACKFORM"   value= "Back"></td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                             <td colspan="5"; style="text-align:center;">&nbsp;</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                         </tr>
                   </table>
              </FORM>
          </center>
      </body>
      <script type="text/javascript">
          function setFocusToTextBoxF() {
             document.getElementById("DEL").focus();
          }
      </script>
      <script type="text/javascript" defer>
     function selectAll(elementName, flag){
          $("input[name='"+elementName+"']").each( function(){$(this).attr("checked",((flag == 1)?true:false));})
     }
     function setFocusToTextBox(){
         document.getElementById("PRUID").focus();
     }    
</script>
    <?php
    	}


//******************************************************************************************************************************************
    


}      