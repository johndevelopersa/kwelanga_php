<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');

include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');

class onlineErrorManagmentDAO {
	
	private $dbConn;

	function __construct($dbConn) {
       $this->dbConn = $dbConn;
       $this->errorTO = new ErrorTO;
    }

// ******************************************************************************************************************
	public function getKosNotificationRecipientsAdditionalParm($principalUId, $eList, $ntype) {
		
		if($ntype == CTD_KOS_ACCOUNTS) { $ctdDep = "";} else { $ctdDep = "AND pc.depot_uid = dm.depot_uid ";}
 
		$sql="SELECT distinct(dm.depot_uid),
                 d.name AS 'Warehouse', 
                 dm.principal_uid,
                 p.name AS 'Principal',
                 pc.email_addr, 
                 dm.document_number,
                 dh.invoice_date,
                 psm.deliver_name,
                 psm.uid as 'psm.uid',
                 se.general_reference_1,
                 se.type,
                 se.type_uid,
                 dm.uid as 'dataUid'
          FROM        document_master dm
          INNER JOIN  document_header dh ON dm.uid = dh.document_master_uid
          INNER JOIN  principal_store_master psm ON psm.uid = dh.principal_store_uid
          INNER JOIN  principal p ON p.uid = dm.principal_uid
          INNER JOIN  depot d ON d.uid = dm.depot_uid
          LEFT  JOIN  principal_contact pc ON pc.principal_uid = dm.principal_uid 
                                           " . $ctdDep . "
                                           AND pc.contact_type_uid = ".mysqli_real_escape_string($this->dbConn->connection, $ntype).",
                      smart_event se
          WHERE dm.uid = se.data_uid
          AND   dm.principal_uid = '".mysqli_real_escape_string($this->dbConn->connection, $principalUId)."'
          AND   se.uid IN (".mysqli_real_escape_string($this->dbConn->connection, $eList).")
          ORDER BY pc.email_addr, dm.document_number ";
          

//echo "<br>";
//echo $sql;
//echo "<br>";
		
		return $this->dbConn->dbGetAll($sql);
	}

// ******************************************************************************************************************
	public function getKosNotificationRecipientsNoErrors($principalUId, $ntype) {

      $sql = "SELECT distinct( p.name) AS 'Principal',
                     pc.email_addr
              FROM   principal_contact pc,
                     principal p
              WHERE p.uid = pc.principal_uid
              AND   pc.principal_uid = '". mysqli_real_escape_string($this->dbConn->connection, $principalUId) ."'
              AND   pc.contact_type_uid = ". mysqli_real_escape_string($this->dbConn->connection, $ntype) . "
              ORDER BY pc.email_addr ;";
		  
		  return $this->dbConn->dbGetAll($sql);
	
	}

// ******************************************************************************************************************

  function getTemplateVoqadoImportErrorSubject($additional = ''){
    return "KOS Document Import Errors " . date("Y-m-d") . (($additional!='')?(' (' . $additional . ')'):(''));
  }  
  // ******************************************************************************************************************
  function getTemplateKosBodyErrorHeader($prin,$notify, $success, $errors, $dt) {

    return '<!DOCTYPE html>
               <HTML>
                 <head>
                    <TITLE>Transporter Report</TITLE>
                 </head>
                 <body>
                     <table width="70%">
                        <tr>
                           <td style="text-align:left;" nowrap >&nbsp;</td>
                           <td colspan="5" style="text-align:left; font-weight:Bold;" >' . mysqli_real_escape_string($this->dbConn->connection, $prin) . '       Document Import Errors     -     ' . mysqli_real_escape_string($this->dbConn->connection, $notify) . ' </td>
                        </tr>
                        <tr>
                            <td colspan="6" style="text-align:left; font-weight:Bold;" >&nbsp;</td>
                       </tr>
                         <tr>
                            <td style="text-align:left;" nowrap >&nbsp;</td>
                            <td colspan="5" style="text-align:left; font-weight:Bold;" >' . mysqli_real_escape_string($this->dbConn->connection, $dt) . '</td>
                       </tr>
                        <tr>
                            <td colspan="6" style="text-align:left; font-weight:Bold;" >&nbsp;</td>
                       </tr> 
                       
                       <tr>
                            <td colspan="1" style="text-align:left;" >&nbsp;</td>
                            <td colspan="2" style="text-align:left; color: Green;" >Succesfully Imported</td>
                            <td colspan="3" style="text-align:left; color: Green;" >' . mysqli_real_escape_string($this->dbConn->connection, $success) . '</td>
                       </tr>
                        <tr>
                            <td colspan="6" style="text-align:left; font-weight:Bold;" >&nbsp;</td>
                       </tr>
                       <tr>
                            <td colspan="1" style="text-align:left;" >&nbsp;</td>
                            <td colspan="2" style="text-align:left; color: red;" >Total Errors</td>
                            <td colspan="3" style="text-align:left; color: red;" >' . mysqli_real_escape_string($this->dbConn->connection, $errors) . '</td>
                       </tr>
                       <tr>
                            <td width="7%;"  style="text-align:left; font-weight:Bold;"  nowrap >&nbsp;</td>
                            <td width="7%;"  style="text-align:left; font-weight:Bold;"  nowrap >Document No</td>
                            <td width="7%;"  style="text-align:left; font-weight:Bold;"  nowrap >Date</td>
                            <td width="40%;" style="text-align:left; font-weight:Bold;"  nowrap >Store</td>
                            <td width="90%;" style="text-align:left; font-weight:Bold;"         >Error</td>
                            <td width="9%;"  style="text-align:left; font-weight:Bold;"  nowrap >&nbsp</td>
                       </tr>
                       <tr>
                            <td colspan="6" style="text-align:left; font-weight:Bold;" >&nbsp;</td>
                       </tr>';
  }
// ******************************************************************************************************************
  function getTemplateKosBodyErrorend($prin, $sfId, $nt, $eAdd){

    return '<tr>
                 <td style="text-align:left;" nowrap >&nbsp;</td>
                 <td colspan="6" style="text-align:left; font-weight:normal;" >&nbsp;</td>
            </tr>
            <tr>
                 <td style="text-align:left;" nowrap >&nbsp;</td>
                 <td colspan="6" style="text-align:left; font-weight:normal;" ></td>
            </tr>
            <tr>
                 <td colspan="3"style="text-align:left;" nowrap >&nbsp;</td>
                 <td colspan="2" style="text-align:left;"><a style="text-align:left; font-weight:normal;color:green;"href=https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/m/k/kg.php?ACTION=MANAGEACCOUNTS&PRIN=' . mysqli_real_escape_string($this->dbConn->connection, $prin) . '&SFID=' . mysqli_real_escape_string($this->dbConn->connection, $sfId) . '&NT=' . mysqli_real_escape_string($this->dbConn->connection, $nt)  . '&EADD=' . mysqli_real_escape_string($this->dbConn->connection, $eAdd). '>Manage These Accounts</a></td>
                 <td colspan="1"style="text-align:left;" nowrap >&nbsp;</td>  
            </tr>
            <tr>
                 <td style="text-align:left;" nowrap >&nbsp;</td>
                 <td colspan="6" style="text-align:left; font-weight:normal;" >&nbsp;</td>
            </tr>


            <tr>
                 <td style="text-align:left;" nowrap >&nbsp;</td>
                 <td colspan="6" style="text-align:left; font-weight:normal;" >Fixed account numbers will be re extracted in the next import run</td>
            </tr>
            <tr>
                 <td style="text-align:left;" nowrap >&nbsp;</td>
                 <td colspan="6" style="text-align:left; font-weight:normal;" >&nbsp;</td>
            </tr> 
            <tr>
                 <td style="text-align:left;" nowrap >&nbsp;</td>
                 <td colspan="6" style="text-align:left; font-weight:normal;" >Regards,<br>The Kwelanga Solutions Team</td>
            </tr> 
            <tr>
                 <td style="text-align:left;" nowrap >&nbsp;</td>
                 <td colspan="6" style="text-align:left; font-weight:normal;" >*** This is an automated email. Please do NOT reply to this email as the sending address mailbox is not monitored</td>
                  </tr> 
             </table>
         </body>
       </HTML>'; 
  }

// ******************************************************************************************************************
  function getTemplateBodyKosErrorBody($docNO, $idate, $deliver_name, $general_reference_1, $dataId, $psmId, $type) {

    return '<tr>
                 <td style="text-align:left; font-weight:normal;" nowrap >&nbsp;</td>
                 <td style="text-align:left; font-weight:normal;" nowrap >' . mysqli_real_escape_string($this->dbConn->connection, $docNO) . '</td>
                 <td style="text-align:left; font-weight:normal;" nowrap >' . mysqli_real_escape_string($this->dbConn->connection, $idate) . '</td>
                 <td style="text-align:left; font-weight:normal;" nowrap >' . mysqli_real_escape_string($this->dbConn->connection, $deliver_name) . '</td>
                 <td style="text-align:left; font-weight:normal;"        >' . mysqli_real_escape_string($this->dbConn->connection, $general_reference_1) . '</td>
                 <td style="text-align:left; font-weight:normal;" nowrap >&nbsp;</td>
              </tr>';
  }
// ******************************************************************************************************************
  function getTemplateBodyKosNoErrorBody( ) {

    return '<tr>
                 <td style="text-align:left; font-weight:normal;" nowrap >&nbsp;</td>
                 <td colspan="5" style="text-align:left; font-weight:normal;" nowrap >No Errors to Display</td>
            </tr>';
  }
// ******************************************************************************************************************

  function getErrorListToManage($prin, $sfFid, $noteId){

       $sql = "SELECT dm.uid as 'DocUid',
                      dm.document_number, 
                      psm.deliver_name, 
                      psm.uid,
                      sfd.value,
                      se.uid AS 'seUid',
                      se.general_reference_1
               FROM   document_master dm
               INNER JOIN .document_header dh ON dm.uid = dh.document_master_uid
               INNER JOIN .principal_store_master psm ON dh.principal_store_uid = psm.uid
               LEFT  JOIN .special_field_details sfd ON sfd.field_uid = " . mysqli_real_escape_string($this->dbConn->connection, $sfFid) . "
                                                     AND sfd.entity_uid = psm.uid
               LEFT  JOIN smart_event se ON  se.type_uid = " . mysqli_real_escape_string($this->dbConn->connection, $noteId) . "
                                         AND se.data_uid = dm.uid
                                                                               
               WHERE dm.principal_uid = " .mysqli_real_escape_string($this->dbConn->connection, $prin). "
               AND   se.status = 'E';";

		return $this->dbConn->dbGetAll($sql);
  }
// ******************************************************************************************************************
  function setTransactionToDelete($seUid, $em='') {
  	 $dsql = "update smart_event se SET se.`status` = 'C', 
  	                                    se.general_reference_1 = 'Error Cleared',
  	                                    se.general_reference_2 = '" .mysqli_real_escape_string($this->dbConn->connection, $em) . "'
              WHERE se.uid = " . mysqli_real_escape_string($this->dbConn->connection, $seUid) ;
  	
              $this->errorTO = $this->dbConn->processPosting($dsql,"");

              if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
                     $this->errorTO->description="Transaction Delete Failed : ". $dsql .$this->errorTO->description;
                     return $this->errorTO;         	                  
              } else {
                     $this->dbConn->dbQuery("commit");
                     $this->errorTO->description="Transaction Delete Successful";
                     return $this->errorTO;                
              }  	
  	
  	
  }
// ******************************************************************************************************************  
  function setStoreSpecialFieldNew($fldUid, $entUID, $accNo, $eAdd, $seUid) {
  	 
  	 $dsql = "SELECT *
              FROM .special_field_details sfd
              WHERE sfd.field_uid  = " . mysqli_real_escape_string($this->dbConn->connection, $fldUid) . "
              AND   sfd.entity_uid = " . mysqli_real_escape_string($this->dbConn->connection, $entUID) . ";";
              
     $sdfExist = $this->dbConn->dbGetAll($dsql);
     
     if(count($sdfExist) > 0) {
            $dsql = "UPDATE special_field_details sfd SET sfd.value   = '" . mysqli_real_escape_string($this->dbConn->connection, $accNo) . "',
                                                          sfd.managed = 'Y'
                     WHERE sfd.field_uid  = " . mysqli_real_escape_string($this->dbConn->connection, $fldUid) . "
                     AND   sfd.entity_uid = " . mysqli_real_escape_string($this->dbConn->connection, $entUID) . ";";

            $this->errorTO = $this->dbConn->processPosting($dsql,"");

            if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
                 $this->errorTO->description="Special Field Update Failed : ". $dsql .$this->errorTO->description;
                 return $this->errorTO;         	                  
            } else {
                 $this->dbConn->dbQuery("commit");
                 $this->errorTO->description="Special Field Update Successful";
            }
              	            
            if($this->errorTO->type == FLAG_ERRORTO_SUCCESS) {
                       $dsql = "update smart_event se SET se.general_reference_1 = 'Spec. Field Updated',
                                                          se.general_reference_1 = '" . mysqli_real_escape_string($this->dbConn->connection, $eAdd) . "'
                                WHERE se.uid = " . mysqli_real_escape_string($this->dbConn->connection, $seUid) ;

                       $this->errorTO = $this->dbConn->processPosting($dsql,"");
            
                       if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
                           $this->errorTO->description="Special Field Update Failed : ". $dsql .$this->errorTO->description;
                           return $this->errorTO;         	                  
                       } else {
                           $this->dbConn->dbQuery("commit");
                           $this->errorTO->description="Special Field Update Successful";
                           return $this->errorTO;                
                       }
            }           
     } else {
     	
            $iSql = "INSERT INTO `special_field_details` (`field_uid`, 
                                                          `value`, 
                                                          `entity_uid`, 
                                                          `managed`) 
                     VALUES ("  . mysqli_real_escape_string($this->dbConn->connection, $fldUid) . " , 
                             '" . mysqli_real_escape_string($this->dbConn->connection, $accNo)  . "', 
                             "  . mysqli_real_escape_string($this->dbConn->connection, $entUID)  . " , 
                             'Y');";     	
            $this->errorTO = $this->dbConn->processPosting($iSql,"");

            if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
                 $this->errorTO->description="Special Field Update Failed : ". $iSql .$this->errorTO->description;
                  return $this->errorTO;         	                  
            } else {
                 $this->dbConn->dbQuery("commit");
                 $this->errorTO->description="Special Field Update Successful";
            }
              	            
            if($this->errorTO->type == FLAG_ERRORTO_SUCCESS) {
                       $dsql = "update smart_event se SET se.general_reference_1 = 'Spec. Field Updated',
                                                          se.general_reference_2 = '" . mysqli_real_escape_string($this->dbConn->connection, $eAdd) . "'
                                WHERE se.uid = " . mysqli_real_escape_string($this->dbConn->connection, $seUid) ;

                       $this->errorTO = $this->dbConn->processPosting($dsql,"");
            
                       if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
                           $this->errorTO->description="Special Field Update Failed : ". $dsql .$this->errorTO->description;
                           return $this->errorTO;         	                  
                       } else {
                           $this->dbConn->dbQuery("commit");
                           $this->errorTO->description="Special Field Update Successful";
                           return $this->errorTO;                
                       }
            }           
     }
  }
// ******************************************************************************************************************
  function insertOmniAccount($sffUid, $psmUID, $sfdValue) {
  	 $dsql = "INSERT INTO special_field_details (special_field_details.field_uid,
                                   special_field_details.entity_uid,
                                   special_field_details.value)
              VALUES ("  . mysqli_real_escape_string($this->dbConn->connection, $sffUid) . ",
                      "  . mysqli_real_escape_string($this->dbConn->connection, $psmUID) . ",
                      '" . mysqli_real_escape_string($this->dbConn->connection, $sfdValue) . "');"  ;
  	
              $this->errorTO = $this->dbConn->processPosting($dsql,"");

              if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) {            	    
                     $this->errorTO->description="Omni Account Insert Failed : ". $dsql .$this->errorTO->description;
                    echo "<br>"; 
                    echo $dsql;
                   echo "<br>";

                     return $this->errorTO;         	                  
              } else {
                     $this->dbConn->dbQuery("commit");
                     $this->errorTO->description="Omni Account Insert Successful";
                     return $this->errorTO;                
              }  	
  	
  	
  }
// ******************************************************************************************************************
  function updateOmniAccount($sffUid, $psmUID, $sfdValue) {
  	 $dsql = "update special_field_details sfd SET sfd.`value` = '" . mysqli_real_escape_string($this->dbConn->connection, $sfdValue) . "'
              WHERE sfd.`field_uid`  =  " . mysqli_real_escape_string($this->dbConn->connection, $sffUid) . "
              AND   sfd.`entity_uid` = '" . mysqli_real_escape_string($this->dbConn->connection, $psmUID) . "';";
  	
              $this->errorTO = $this->dbConn->processPosting($dsql,"");

              if ($this->errorTO->type!=FLAG_ERRORTO_SUCCESS) { 
                  echo "<br>"; 
                   echo $dsql;
                  echo "<br>";
                     $this->errorTO->description="Transaction Delete Failed : ". $dsql .$this->errorTO->description;
                     return $this->errorTO;         	                  
              } else {
                     $this->dbConn->dbQuery("commit");
                     $this->errorTO->description="Transaction Delete Successful";
                     return $this->errorTO;                
              }  	
  	
  	
  }
// ******************************************************************************************************************
  function getSelectedErrorList($prin, $docList, $noteId) {

       $sql = "SELECT dm.uid as 'DocUid',
                      dm.document_number, 
                      psm.deliver_name, 
                      psm.uid as 'psmUid',
                      sff.uid as 'fieldId',
                      sfd.value,
                      se.uid as 'seUid',
                      se.general_reference_1,
                      " . mysqli_real_escape_string($this->dbConn->connection, $noteId) . " as 'notid'
               FROM   document_master dm
               INNER JOIN .document_header dh ON dm.uid = dh.document_master_uid
               INNER JOIN .principal_store_master psm ON dh.principal_store_uid = psm.uid
               LEFT  JOIN .special_field_fields sff  ON sff.principal_uid = dm.principal_uid 
                                                     AND sff.notify_type  = " . mysqli_real_escape_string($this->dbConn->connection, $noteId) . "
               LEFT  JOIN .special_field_details sfd ON sfd.field_uid     = sff.uid
                                                     AND sfd.entity_uid   = psm.uid
               LEFT  JOIN smart_event se ON  se.type_uid = " . mysqli_real_escape_string($this->dbConn->connection, $noteId) . "
                                         AND se.data_uid = dm.uid
               WHERE dm.uid IN (" . mysqli_real_escape_string($this->dbConn->connection, $docList) . ")";
 
		return $this->dbConn->dbGetAll($sql);
  }
}
?>


