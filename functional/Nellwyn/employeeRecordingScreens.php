<?php
    include_once('ROOT.php');
    include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER.'DAO/EmployeeDAO.php');
    include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
        include_once($ROOT.$PHPFOLDER."libs/CommonUtils.php");

        include_once($ROOT.$PHPFOLDER."elements/datePickerElement.php");

    //($ROOT.$PHPFOLDER.'TO/ErrorTO.php');

class employeeRecordingScreens {

      function __construct() {

         global $dbConn;
         $this->dbConn = $dbConn;
      }
// ********************************************************************************************************************************

   public function selectWarehouse($userId, $prin) {

       $EmployeeDAO = new EmployeeDAO($this->dbConn);
       $whDetails  = $EmployeeDAO->selectUserWarehouse($userId, $prin, '');

       $EmployeeDAO = new EmployeeDAO($this->dbConn);
       $whDetails  = $EmployeeDAO->selectUserWarehouse($userId, $prin, '');

       if(count($whDetails) == 0) { ?>
            <script> alert("You have no warehouses - Problem")</script>
            <?php
            return;
       }
   	   ?>
       <body>
          <center>
              <FORM name='Employee recording' method=post action=''>
                   <table width="720"; style="border:none">
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td class=head1 Colspan="5"; style="text-align:center">Set Active Warehouse</td>
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
                        	<td colspan="1"; style="text-align:right">Select Warehouse</td>
                          <td colspan="2"; style="text-align:left";>
                          	           <select name="WHID" id="WHID">
                                             <option value="Select Warehouse">Select Warehouse</option>
                                             <?php foreach($whDetails as $row) { ?>
                                                   <option value="<?php echo $row['WhUid']; ?>"><?php echo trim($row['WhUid']) . " - " . trim($row['Warehouse']); ?></option>
                                             <?php
                                             } ?>
                                        </select></td>
                          <td colspan="1"; style="text-align:left"></td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="SETWH" value= "Set Warehouse"></td>
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
// ********************************************************************************************************************************

   public function firstform($showDeleted) {
      ?>
      <body  onload='setFocusToTextBoxS()'>
          <center>
              <FORM name='Employee recording' method=post action=''>
                   <table width="720"; style="border:none">
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td class=head1 Colspan="5"; style="text-align:center">Employee Recording</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td style="width:5%; border:none">&nbsp</td>
                          <td width="30%"; style="border:none">&nbsp</td>
                          <td width="30%"; style="border:none">&nbsp</td>
                          <td width="30%"; style="border:none">&nbsp</td>
                          <td width="3%" ; style="border:none">&nbsp</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                           <td>&nbsp;</td>
                           <td class=det1 colspan="3"; style="text-align:center">Scan or Enter Employee Code</td>
                           <td><input type="hidden" name="SHOWDELETED" value=<?php echo mysqli_real_escape_string($this->dbConn->connection, $showDeleted); ?>></td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td colspan="5";>&nbsp;</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                           <td colspan="1">&nbsp;</td>
                           <td colspan="1" style="text-align:right;">&nbsp;</td>
                           <td class=det1 colspan="1" style="text-align:center; ">
                                    <div style="position:relative;">
                                           <INPUT type="TEXT" size="13" name="EMPCODE" id="EMPCODE" class="scan-input" placeholder="scan or input" autofocus />
                                           <div class="icon-size-32 icon-scan" style="position:absolute;top:1px;left:10px;" ></div>
                                           </div></td>
                           <td colspan="2">&nbsp;</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td colspan="5";>&nbsp</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td colspan="5";>&nbsp</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td class=det1 colspan="5"; style="text-align:center;">Find Employee By Name</td>
                        </tr>

                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td colspan="5";>&nbsp</td>
                        </tr>

                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                        	<td Colspan="1">&nbsp</td>
                        	<td colspan="1"; style="text-align:right"></td>
                          <td colspan="2"; style="text-align:left";><input type="text" name="UVALUE" id="UVALUE" size="50" value="" placeholder="Search by (Part) Employee Name or Code"></td>
                          <td colspan="1"; style="text-align:left"></td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td colspan="5";>&nbsp</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="NAMEFILTER" value= "Filter on Name">&nbsp;&nbsp
                          	                                          <INPUT TYPE="submit" class="submit" name="CODEFILTER" value= "Filter on Emp. Code">&nbsp;&nbsp
                          	                                          <INPUT TYPE="submit" class="submit" name="CANFORM"   value= "Cancel"></td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                         </tr>
                   </table>
              </FORM>
          </center>
      </body>


      <script type="text/javascript">
          function setFocusToTextBoxS() {
             document.getElementById("EMPCODE").focus();
          }
      </script>
    <?php
   }
// ********************************************************************************************************************************

   public function SelectEmp($empDetails, $showDeleted) { ?>

      <body  onload='setFocusToTextBoxF()'>
          <center>
              <FORM name='Employee recording' method=post action=''>
                   <table width="720"; style="border:none">
                        <tr>
                          <td class=head1 Colspan="5"; style="text-align:center">Select Employee</td>
                        </tr>
                        <tr>
                          <td><input type="hidden" name="SHOWDELETED" value=<?php echo mysqli_real_escape_string($this->dbConn->connection, $showDeleted); ?>></td>
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
                        	<td colspan="1"; style="text-align:right">Select Employee</td>
                          <td colspan="2"; style="text-align:left";>
                          	      	     <select name="EMPID" id="EMPID">
                                             <option value="Select Employee">Select Employee</option>
                                             <?php foreach($empDetails as $row) { ?>
                                                   <option value="<?php echo trim($row['code']) . " - " . trim($row['name']); ?>"><?php echo trim($row['code']) . " - " . trim($row['name']); ?></option>
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
                          <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="GETEMP" value= "Get Employee Details">&nbsp;&nbsp
                          	                                          <INPUT TYPE="submit" class="submit" name="CODEFILTER" value= "Filter on Emp. Code">&nbsp;&nbsp
                          	                                          <INPUT TYPE="submit" class="submit" name="BACKFORM"   value= "Back"></td>
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
// ********************************************************************************************************************************
   public function captureEmpDetails($empDetails,$DateCaptured) {
   	
     ?>
      <body>
          <center>
              <FORM name='Employee recording' method=post action=''>
                   <table width="720"; style="border:none">
                        <tr>
                          <td class=head1 Colspan="5"; style="text-align:center">Capture Employee Details</td>
                        </tr>
                        <tr>
                          <td>&nbsp</td>
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
                        	<td colspan="1"; style="text-align:right; font-weight: bold;">Employee</td>
                          <td colspan="2"; style="text-align:left";><?php echo trim($empDetails[0]['name']) .   "   -   "    . trim($empDetails[0]['code']);?></td>
                          <td colspan="1"; style="text-align:left"><input type="hidden" name="EMDID" value=<?php echo mysqli_real_escape_string($this->dbConn->connection, $empDetails[0]['empUid']); ?>>
                          	                                       <input type="hidden" name="DEPID" value=<?php echo mysqli_real_escape_string($this->dbConn->connection, $empDetails[0]['depot_uid']); ?>></td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                        	<td Colspan="1">&nbsp</td>
                        	<td colspan="1"; style="text-align:right; font-weight: bold;">Select Date </td>
                      <td colspan= "2"; style="text-align:left"><?php DatePickerElement::getDatePickerLibs(); DatePickerElement::getDatePicker("DATECAPTURED",$DateCaptured); ?> </td>

                         
                        </tr>
                        </tr>
                         <tr class="<?php 	echo GUICommonUtils::styleEO($class); ?>">
                          <td colspan="5"; style="text-align:center;">&nbsp;</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                        	<td Colspan="1">&nbsp</td>
                        	<td colspan="1"; style="text-align:right; font-weight: bold;">Job Function</td>
                          <td colspan="1"; style="text-align:left; font-weight: normal;"><?php echo trim($empDetails[0]['job_description']);?></td>
                          <?php 
                          
                          $EmployeeDAO = new EmployeeDAO($this->dbConn);
                          $jobArr = $EmployeeDAO->getEmployeeJobs(); ?>
                          <td colspan="1";>
                                 <select name="NEWJOB" id="NEWJOB" size="1">
                                     <option value="Change Employee Job">Change Employee Job</option>
                                         <?php foreach($jobArr as $row) { ?>
                                              <option value="<?php echo trim($row['jobUid']); ?>"><?php echo trim($row['job_description']); ?></option>
                                          <?php } ?>
                                 </select>
                          </td>
                          <td Colspan="1"><input type="hidden" name="OLDJOB" value=<?php echo mysqli_real_escape_string($this->dbConn->connection, $empDetails[0]['jobUid']); ?>></td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td colspan="5"; style="text-align:center;">&nbsp;</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td colspan="5"; style="text-align:center;">&nbsp;</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                        	<td Colspan="1">&nbsp</td>
                        	<td colspan="1"; style="text-align:right; font-weight: bold;">Comments</td>
                          <td colspan="2"; style="text-align:left;"><input type="text" name="SCOMMENT" value=""></td>
                          <td Colspan="1">&nbsp</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td colspan="5"; style="text-align:center;">&nbsp;</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="SUBDET" value= "Submit Details ">
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
             document.getElementById("EMPID").focus();
          }
      </script>
    <?php
   }
// ********************************************************************************************************************************
   public function empDetailCapture($whId, $addUpd, $userId, $prin, $empDet) {
   	
      $EmployeeDAO = new EmployeeDAO($this->dbConn);
      $whDetails  = $EmployeeDAO->selectUserWarehouse($userId, $prin, '');

      $EmployeeDAO = new EmployeeDAO($this->dbConn);
      $userWh      = $EmployeeDAO->selectUserWarehouse($userId, $prin, $whId);
      
      $EmployeeDAO = new EmployeeDAO($this->dbConn);
      $ejs  = $EmployeeDAO->getEmployeeJobs();    
      
      if($addUpd == 'A') {
      	     $eCode    = ''; 
             $empName  = "";
             $eId      = "";
             $eComment = "";
             $empUid   = "";
               	
      } else {
      	
      	     $warehouse       = $userWh[0]['Warehouse'];
      	     $wareHouseId     = $empDet[0]['depot_uid'];
      	     $eCode           = $empDet[0]['code']; 
             $empName         = $empDet[0]['name']; 
             $eId             = $empDet[0]['id_number'];
             $jobId           = $empDet[0]['jobUid']; 
             $jobDescription  = $empDet[0]['job_description']; 
             $empUid          = $empDet[0]['empUid'];
             $empStatus       = $empDet[0]['status'];
             $eComment        = $empDet[0]['comments'];
      }
      ?>
      <body>
          <center>
              <FORM name='Employee Maintenance' method=post action=''>
                   <table width="720"; style="border:none">
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td class=head1 Colspan="5"; style="text-align:center">Capture Employee Details</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            <td colspan="5"; style="text-align:center;"><input type="hidden" name="DMLTYPE" value=<?php echo mysqli_real_escape_string($this->dbConn->connection, $addUpd); ?>></td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td width="5%"; style="border:none">&nbsp</td>
                          <td width="30%"; style="border:none">&nbsp</td>
                          <td width="30%"; style="border:none">&nbsp</td>
                          <td width="30%"; style="border:none">&nbsp</td>
                          <td width="3%" ; style="border:none">&nbsp</td>
                        </tr>
                        <?php 
                        if($addUpd == 'U') { ?>
                            <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                                 <td Colspan="1">&nbsp<input type="hidden" name="EMPUID" value=<?php echo mysqli_real_escape_string($this->dbConn->connection, $empUid); ?>>
                                 	                    <input type="hidden" name="WHID" value=<?php echo mysqli_real_escape_string($this->dbConn->connection, $wareHouseId); ?>></td>
                                 <td colspan="1"; style="text-align:right; font-weight: bold;">Warehouse</td>
                                 <td colspan="1"; style="text-align:left;"><?php echo trim($warehouse);?></td>
                                  <td>
                                       <select name="WAREHOUSE" id="WAREHOUSE" size="1">
                                           <option value="Change Warehouse">Change Warehouse</option>
                                                <?php foreach($whDetails as $row) { ?>
                                                          <option value="<?php echo trim($row['WhUid']); ?>"><?php echo trim($row['Warehouse']); ?></option>
                                                 <?php } ?>
                                       </select>
                                  </td>
                                  <td Colspan="1">&nbsp</td>
                            </tr>
                        <?php
                        } else {?>
                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>                               
                                  <td colspan="1"><input type="hidden" name="EMPUID" value=<?php echo mysqli_real_escape_string($this->dbConn->connection, $empUid); ?>></td>
                                  <td colspan="1" style="text-align:right; font-weight: bold;">Warehouse</td>
                                  <td colspan="2" style="text-align:left; ">
                                       <select name="WAREHOUSE" id="WAREHOUSE" size="1">
                                           <option value="Select New Warehouse">Select Warehouse</option>
                                                <?php foreach($whDetails as $row) { ?>
                                                          <option value="<?php echo trim($row['WhUid']); ?>"><?php echo trim($row['Warehouse']); ?></option>
                                                 <?php } ?>
                                       </select>
                                  </td>
                                  <td colspan="1"; style="text-align:left">&nbsp;</td>
                             </tr>
                         <?php
                        } ?>                        
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                        	<td Colspan="1">&nbsp</td>
                        	<td colspan="1"; style="text-align:right; font-weight: bold;">Employee Code</td> 
                        	<?php
                        	if($addUpd == 'U') { ?>
                               <td colspan="2"; style="text-align:left;"><?php echo trim($eCode); ?>
                               	                                         <input type="hidden" name="ECODE" value=<?php echo mysqli_real_escape_string($this->dbConn->connection, $eCode); ?>></td>
                        	<?php	
                        	} else {?>
                        		    <td colspan="2"; style="text-align:left;"><input type="text" name="ECODE" value="<?php echo $eCode; ?>"></td>
                        	<?php	
                        	} ?>
                          <td colspan="1"; style="text-align:left">&nbsp;</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                        	<td Colspan="1">&nbsp</td>
                        	<td colspan="1"; style="text-align:right; font-weight: bold;">Employee Name</td>
                          <td colspan="2"; style="text-align:left;"><input type="text" name="ENAME" value="<?php echo $empName; ?>"></td>
                          <td colspan="1"; style="text-align:left">&nbsp;</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                        	<td Colspan="1">&nbsp</td>
                        	<td colspan="1"; style="text-align:right; font-weight: bold;">ID Number</td>
                          <td colspan="2"; style="text-align:left;"><input type="text" name="EID" value="<?php echo $eId; ?>"></td>
                          <td colspan="1"; style="text-align:left">&nbsp;</td>
                        </tr>
                        <?php 
                        if($addUpd == 'A') { ?>

                            <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                 <td colspan="5"; style="text-align:center;">&nbsp;</td>
                            </tr>
                            <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>                               
                                  <td Colspan="1">&nbsp</td>
                                  <td colspan="1"; style="text-align:right; font-weight: bold;">Job Function</td>
                                  <td colspan="2">
                                       <select name="EJS" id="EJS" size="1">
                                           <option value="Select New Function">Select New Function</option>
                                                <?php foreach($ejs as $row) { ?>
                                                          <option value="<?php echo trim($row['jobUid']); ?>"><?php echo trim($row['job_description']); ?></option>
                                                 <?php } ?>
                                       </select>
                                  </td>
                                  <td colspan="1"; style="text-align:left">&nbsp;</td>
                            </tr>
                        <?php
                        } else { ?>
                        	   <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                 <td colspan="5"; style="text-align:center;">&nbsp;</td>
                             </tr>
                            
                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                                 <td Colspan="1">&nbsp</td>
                                 <td colspan="1"; style="text-align:right; font-weight: bold;">Job Function</td>
                                 <td colspan="1"; style="text-align:left; font-weight: normal;"><?php echo trim($jobDescription);?></td>
                                 <?php 
                          
                                 $EmployeeDAO = new EmployeeDAO($this->dbConn);
                                 $jobArr = $EmployeeDAO->getEmployeeJobs(); ?>
                                 <td colspan="1";>
                                    <select name="NEWJOB" id="NEWJOB" size="1">
                                         <option value="Change Employee Job">Change Employee Job</option>
                                         <?php foreach($jobArr as $row) { ?>
                                                 <option value="<?php echo trim($row['jobUid']); ?>"><?php echo trim($row['job_description']); ?></option>
                                          <?php } ?>
                                    </select>
                                 </td>
                                 <td Colspan="1"><input type="hidden" name="OLDJOB" value=<?php echo mysqli_real_escape_string($this->dbConn->connection, $jobId); ?>></td>
                             </tr>
                        <?php } ?>      
                 
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                             <td Colspan="5">&nbsp</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                             <td Colspan="1">&nbsp</td>
                             <td colspan="1"; style="text-align:right; font-weight: bold;">Comments</td>
                             <td colspan="2"; style="text-align:left;"><input type="text" name="SCOMMENT" value="<?php echo $eComment; ?>"></td>
                             <td Colspan="1">&nbsp</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                             <td colspan="5"; style="text-align:center;">&nbsp;</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                             <td colspan="5"; style="text-align:center;">&nbsp;</td>
                        </tr>
                        <?php
                        if($addUpd == 'U') {                        	
                        	   if($empStatus == "A") {$aChkStat = 'CHECKED'; $dChkStat = ''; } else { $aChkStat = ''; $dChkStat = 'CHECKED';} 
                        	   ?>
                             <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                                 <td Colspan="2">&nbsp</td>
                                 <td colspan="1" style="text-align:center; border:none; font-weight: bold; "><label class="label" for="STATUS">Active</label>&nbsp;<input type="radio" name="STATUS" value="ACTIVE" <?php echo $aChkStat;?>></td>
                                 <td colspan="1" style="text-align:center; border:none; font-weight: bold;"><label class="label" for="STATUS">Deleted</label><input type="radio" name="STATUS" value="DELETED" <?php echo $dChkStat;?>></td>
                                 <td colspan="1"; style="border:none;">&nbsp</td>    
                             </tr>                        	
                        <?php	
                        } ?>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                             <td colspan="5"; style="text-align:center;">&nbsp;</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                             <td colspan="5"; style="text-align:center;">&nbsp;</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                             <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="SUBEMPUPD" value= "Submit Details ">
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
             document.getElementById("EMPID").focus();
          }
      </script>
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
                            <td class="head1" colspan="4"; style="text-align:center; padding: 0 15px 0 20px ; ">Employee Data Maintenance</td>
                       </tr> 
                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            <td Colspan="4">&nbsp</td>
                       </tr>
                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            <td >&nbsp</td>
                            <td class="head1" colspan="1"; style="text-align:right; padding: 0 15px 0 20px ; "><label class="label" for="MODEMPSP">Modify&nbsp;Employee&nbsp;Details&nbsp;</label><input type="radio" name="MODEMPSP" onclick="javascript: submit()" value="MODIFY"></td>
                            <td class="head1" colspan="1"; style="text-align:right; padding: 0 20px 0 30px ; "><label class="label" for="ADDEMPSP">Add&nbsp;Employee</label><input type="radio" name="ADDEMPSP" onclick="javascript: submit()" value="ADD"></td>	
                            <td >&nbsp</td>    
                       </tr>
                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                            <td Colspan="4">&nbsp;</td>
                       </tr>  
                       <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                           <td Colspan="1">&nbsp</td>
                           <td colspan="1"; style="font-size:9px; text-align:left; font-weight: normal;">&nbsp;&nbsp;Show Deleted Employees&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name="REMEMP" value="A"></td>
                           <td colspan="2"; style="text-align:left"></td>
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

   public function secondform($showDeleted) {
      ?>
      <body  onload='setFocusToTextBoxS()'>
          <center>
              <FORM name='Employee recording' method=post action=''>
                   <table width="720"; style="border:none">
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td class=head1 Colspan="5"; style="text-align:center">Employee Recording</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td style="width:5%; border:none">&nbsp</td>
                          <td width="30%"; style="border:none">&nbsp</td>
                          <td width="30%"; style="border:none">&nbsp</td>
                          <td width="30%"; style="border:none">&nbsp</td>
                          <td width="3%" ; style="border:none">&nbsp</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td colspan="5";>&nbsp</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td class=det1 colspan="5"; style="text-align:center;">Find Employee By Name</td>
                        </tr>

                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td colspan="5";>&nbsp</td>
                        </tr>

                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>";>
                        	<td Colspan="1">&nbsp</td>
                        	<td colspan="1"; style="text-align:right"></td>
                          <td colspan="2"; style="text-align:left";><input type="text" name="UVALUE" id="UVALUE" size="50" value="" placeholder="Search by (Part) Employee Name or Code"></td>
                          <td colspan="1"; style="text-align:left"></td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td colspan="5";>&nbsp</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="NAMEFILTER" value= "Filter on Name">&nbsp;&nbsp
                          	                                          <INPUT TYPE="submit" class="submit" name="CODEFILTER" value= "Filter on Emp. Code">&nbsp;&nbsp
                          	                                          <INPUT TYPE="submit" class="submit" name="BACKFORM"   value= "Back"></td>
                        </tr>
                        <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td Colspan="5">&nbsp</td>
                         </tr>
                   </table>
              </FORM>
          </center>
      </body>


      <script type="text/javascript">
          function setFocusToTextBoxS() {
             document.getElementById("EMPCODE").focus();
          }
      </script>
    <?php
   }    
// ********************************************************************************************************************************      


}


