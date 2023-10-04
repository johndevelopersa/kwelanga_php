<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'libs/CommonUtils.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');

include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');

class messagingDAO {
	
	private $dbConn;

	function __construct($dbConn) {
       $this->dbConn = $dbConn;
       $this->errorTO = new ErrorTO;
    }

// ******************************************************************************************************************
	public function getWarehouseNotificationRecipients($principalUId, $eList) {

		$sql="SELECT distinct(dm.depot_uid),
                 d.name AS 'Warehouse', 
                 dm.principal_uid,
                 pc.email_addr, 
                 dm.document_number,
                 dh.invoice_date,
                 psm.deliver_name,
                 psm.uid as 'psm.uid',
                 se.general_reference_2,
                 se.type,
                 dm.uid as 'dataUid'
          FROM        document_master dm
          INNER JOIN  document_header dh ON dm.uid = dh.document_master_uid
          INNER JOIN  principal_store_master psm ON psm.uid = dh.principal_store_uid
          INNER JOIN  depot d ON d.uid = dm.depot_uid
          LEFT JOIN   principal_contact pc ON pc.principal_uid = dm.principal_uid AND pc.depot_uid = dm.depot_uid,
                      smart_event se
          WHERE dm.uid = se.data_uid
          AND   dm.principal_uid = '".mysqli_real_escape_string($this->dbConn->connection, $principalUId)."'
          AND   se.uid IN (".mysqli_real_escape_string($this->dbConn->connection, $eList).")
          ORDER BY dm.depot_uid, pc.uid ";
          
//          echo "<br>";
//          echo $sql;
//          echo "<br>";
		
		return $this->dbConn->dbGetAll($sql);
	}
// ******************************************************************************************************************
	public function getWarehouseNotificationRecipientsAdditionalParm($principalUId, $eList, $ntype) {
		
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
                 se.general_reference_2,
                 se.type,
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
          ORDER BY dm.depot_uid, pc.uid ";
          

//echo "<br>";
//echo $sql;
//echo "<br>";
		
		return $this->dbConn->dbGetAll($sql);
	}

// ******************************************************************************************************************

  function getTemplateOmniImportErrorSubject($additional = ''){
      return "Omni Document Import Errors " . date("Y-m-d") . (($additional!='')?(' (' . $additional . ')'):(''));
  }
// ******************************************************************************************************************

  function getTemplateSgxImportErrorSubject($additional = ''){
      return "SGX Document Import Errors " . date("Y-m-d") . (($additional!='')?(' (' . $additional . ')'):(''));
  }

// ******************************************************************************************************************

  function getTemplateZeroLinesSubject($additional = '') {
      return "Zero Lines on Invoices " . date("Y-m-d") . (($additional!='')?(' (' . $additional . ')'):(''));
  }
// ******************************************************************************************************************

  function getTemplateVoqadoImportErrorSubject($additional = '') {
    return "KOS Document Import Errors " . date("Y-m-d") . (($additional!='')?(' (' . $additional . ')'):(''));
  }  
// ******************************************************************************************************************
  function getTemplateBodyErrorHeader($whName){

    return '<table width="80%">
              <tr>
                 <td style="text-align:left;" nowrap >&nbsp;</td>
                 <td colspan="6" style="text-align:left; font-weight:Bold;" >Document Import Errors </td>
               </tr>
              <tr>
                 <td colspan="7" style="text-align:left; font-weight:Bold;" >&nbsp;</td>
              </tr>
              <tr>
                 <td width="5%;"  style="text-align:left; font-weight:Bold;"  nowrap >&nbsp;</td>
                 <td width="5%;"  style="text-align:left; font-weight:Bold;"  nowrap >Document No</td>
                 <td width="5%;"  style="text-align:left; font-weight:Bold;"  nowrap >Date</td>
                 <td width="20%;" style="text-align:left; font-weight:Bold;"  nowrap >Store</td>
                 <td width="35%;" style="text-align:left; font-weight:Bold;"         >Error</td>
                 <td width="10%;" style="text-align:left; font-weight:Bold;"  nowrap >Clear</td>
                 <td width="10%;" style="text-align:right; font-weight:Bold;" nowrap >Fix</td>
                 <td width="10%;"  style="text-align:left; font-weight:Bold;" nowrap >&nbsp;</td>
              </tr>
              <tr>
                 <td colspan="7" style="text-align:left; font-weight:Bold;" >&nbsp;</td>
              </tr>';
  }
// ******************************************************************************************************************
  function getTemplateBodyZeroLinesSpace() {

    return '<tr>
                  <td colspan="10" style="text-align:left; font-weight:Bold;" >&nbsp;</td>
            </tr>';
            
    }
  
// ******************************************************************************************************************
  function getTemplateBodyZeroLinesHeader() {

    return '<table width="80%">
              <tr>
                 <td colspan="10" style="text-align:center; font-weight:Bold;" >Invoices with Lines Not Invoiced</td>
               </tr>
              <tr>
                 <td colspan="10" style="text-align:left; font-weight:Bold;" >&nbsp;</td>
              </tr>
              <tr>
                 <td width="2%;"  style="text-align:left; font-weight:Bold;"  nowrap >&nbsp;</td>
                 <td width="5%;"  style="text-align:left; font-weight:Bold;"  nowrap >WareHouse</td>
                 <td width="5%;"  style="text-align:left; font-weight:Bold;"  nowrap >Doc No.</td>
                 <td width="5%;"  style="text-align:left; font-weight:Bold;"  nowrap >Date</td>
                 <td width="30%;" style="text-align:left; font-weight:Bold;"  nowrap >Customer</td>
                 <td width="30%;" style="text-align:left; font-weight:Bold;"         >Product</td>
                 <td width="7%;"  style="text-align:left; font-weight:Bold;"  nowrap >Order Qty</td>
                 <td width="7%;" style="text-align:right; font-weight:Bold;" nowrap  >Invoice Qty</td>
                 <td width="7%;"  style="text-align:left; font-weight:Bold;" nowrap  >Short</td>
                 <td width="2%;"  style="text-align:left; font-weight:Bold;"  nowrap >&nbsp;</td>
              </tr>
              <tr>
                 <td colspan="7" style="text-align:left; font-weight:Bold;" >&nbsp;</td>
              </tr>';
  }
// ******************************************************************************************************************
  function getTemplateBodyZeroLinesEnd() {

    return '  <tr>
                 <td style="text-align:left;" nowrap >&nbsp;</td>
                 <td colspan="6" style="text-align:left; font-weight:normal;" >&nbsp;</td>
               </tr>
               <tr>
                 <td style="text-align:left;" nowrap >&nbsp;</td>
                 <td colspan="6" style="text-align:left; font-weight:normal;" >&nbsp;</td>
               </tr>
               <tr>
                 <td style="text-align:left;" nowrap >&nbsp;</td>
                 <td colspan="6" style="text-align:left; font-weight:normal;" >End of Report</td>
               </tr>
               <tr>
                 <td style="text-align:left;" nowrap >&nbsp;</td>
                 <td colspan="6" style="text-align:left; font-weight:normal;" >&nbsp;</td>
               </tr> 
               <tr>
                 <td style="text-align:left;" nowrap >&nbsp;</td>
                 <td colspan="6" style="text-align:left; font-weight:normal;" >*** This is an automated email. Please do NOT reply to this email as the sending address mailbox is not monitored</td>
               </tr> 
             </table>'; 
  }
  
// ******************************************************************************************************************
  function getTemplateBodyErrorend($whName) {

    return '  <tr>
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
             </table>';   

  }
// ******************************************************************************************************************
  function getTemplateBodyErrorBody($docNO, $idate, $deliver_name, $general_reference_2, $dataId, $psmId, $type) {

    return '<tr>
                 <td style="text-align:left; font-weight:normal;" nowrap >&nbsp;</td>
                 <td style="text-align:left; font-weight:normal;" nowrap >' . mysqli_real_escape_string($this->dbConn->connection, $docNO) . '</td>
                 <td style="text-align:left; font-weight:normal;" nowrap >' . mysqli_real_escape_string($this->dbConn->connection, $idate) . '</td>
                 <td style="text-align:left; font-weight:normal;" nowrap >' . mysqli_real_escape_string($this->dbConn->connection, $deliver_name) . '</td>
                 <td style="text-align:left; font-weight:normal;"        >' . mysqli_real_escape_string($this->dbConn->connection, $general_reference_2) . '</td>
                 <td><a style="text-align:left; font-weight:normal;color:red;" href=https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/m/gd/g.php?ACTION=clear&DOCID=' . mysqli_real_escape_string($this->dbConn->connection, $dataId) . '&PSMID=' . mysqli_real_escape_string($this->dbConn->connection, $psmId) . '>Clear</a></td>
                 <td><a style="text-align:right; font-weight:normal;color:green;" href=https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/m/gd/g.php?ACTION=fix&DOCID='   . mysqli_real_escape_string($this->dbConn->connection, $dataId) . '&PSMID=' . mysqli_real_escape_string($this->dbConn->connection, $psmId) . '>Fix</a></td>
                 <td style="text-align:left; font-weight:normal;" nowrap >&nbsp;</td>
              </tr>';
              
              
              
  }
// ******************************************************************************************************************
  function getTemplateBodyGeneralError($docNO, $idate, $deliver_name, $general_reference_2, $dataId, $psmId, $type, $prin) {

    return '<tr>
                 <td style="text-align:left; font-weight:normal;" nowrap >&nbsp;</td>
                 <td style="text-align:left; font-weight:normal;" nowrap >' . mysqli_real_escape_string($this->dbConn->connection, $docNO) . '</td>
                 <td style="text-align:left; font-weight:normal;" nowrap >' . mysqli_real_escape_string($this->dbConn->connection, $idate) . '</td>
                 <td style="text-align:left; font-weight:normal;" nowrap >' . mysqli_real_escape_string($this->dbConn->connection, $deliver_name) . '</td>
                 <td style="text-align:left; font-weight:normal;"        >' . mysqli_real_escape_string($this->dbConn->connection, $general_reference_2) . '</td>
                 <td><a style="text-align:left; font-weight:normal;color:red;" href=https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/m/gd/s.php?ACTION=clear&DOCID=' . mysqli_real_escape_string($this->dbConn->connection, $dataId) . '&PSMID=' . mysqli_real_escape_string($this->dbConn->connection, $psmId) . '>Clear</a></td>
                 <td><a style="text-align:right; font-weight:normal;color:green;" href=https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/m/gd/s.php?ACTION=fix&DOCID='   . mysqli_real_escape_string($this->dbConn->connection, $dataId) . '&PSMID=' . mysqli_real_escape_string($this->dbConn->connection, $psmId) . '&PRIN=' . mysqli_real_escape_string($this->dbConn->connection, $prin) . '>Fix</a></td>
                 <td style="text-align:left; font-weight:normal;" nowrap >&nbsp;</td>
              </tr>';
  }

// ******************************************************************************************************************
  function getTemplateZeroLinesBody($wh, $docNO, $idate, $deliver_name, $product, $ordQty, $invQty, $short) {

    return '<tr>
                 <td style="text-align:left; font-weight:normal;" nowrap >&nbsp;</td>
                 <td style="text-align:left; font-weight:normal;" nowrap >' . mysqli_real_escape_string($this->dbConn->connection, $wh)           . '</td>
                 <td style="text-align:left; font-weight:normal;" nowrap >' . mysqli_real_escape_string($this->dbConn->connection, $docNO)        . '</td>
                 <td style="text-align:left; font-weight:normal;" nowrap >' . mysqli_real_escape_string($this->dbConn->connection, $idate)        . '</td>
                 <td style="text-align:left; font-weight:normal;" nowrap >' . mysqli_real_escape_string($this->dbConn->connection, $deliver_name) . '</td>
                 <td style="text-align:left; font-weight:normal;" nowrap>'  . mysqli_real_escape_string($this->dbConn->connection, $product)      . '</td>
                 <td style="text-align:right; font-weight:normal;"        >' . mysqli_real_escape_string($this->dbConn->connection, $ordQty)       . '</td>
                 <td style="text-align:right; font-weight:normal;"        >' . mysqli_real_escape_string($this->dbConn->connection, $invQty)       . '</td>
                 <td style="text-align:right; font-weight:normal;"        >' . mysqli_real_escape_string($this->dbConn->connection, $short)       . '</td>
                 <td style="text-align:right; font-weight:normal;"        >&nbsp;</td>
              </tr>';
  }

// ******************************************************************************************************************

  function getTransactionToManage($docUid){

       $sql = 'SELECT  p.name AS "Principal",
                   d.name AS "Warehouse",
                   dm.document_number,
                   dh.invoice_date,
                   psm.deliver_name,
                   psm.uid as "psmUid",
                   se.general_reference_2,
                   dm.uid,
                   se.uid as "seUid",
                   se.`type`,
                   se.type_uid,
                   sff.order as "sffOrder",
                   sff.uid as "sffUid",
                   sfd.entity_uid,
                   sfd.value,
                   
                   sfb.order as "sfbOrder",                   
                   sfb.uid as "sfbUid",
                   sfdb.entity_uid as "bEntity",
                   sfdb.value as "bValue",

                   sfp.order as "sfpOrder",
                   sfp.uid as "sfpUid",
                   sfdp.entity_uid as "pEntity",
                   sfdp.value as "pValue"
                   
           FROM        document_master dm
           INNER JOIN  document_header dh ON dm.uid = dh.document_master_uid
           INNER JOIN  principal_store_master psm ON psm.uid = dh.principal_store_uid
           LEFT JOIN   special_field_fields sff ON sff.principal_uid = dm.principal_uid AND sff.name = "Omni Account"
           LEFT JOIN   .special_field_details sfd ON sff.uid = sfd.field_uid AND sfd.entity_uid = psm.uid

           LEFT JOIN   special_field_fields sfb ON sfb.principal_uid = dm.principal_uid AND sfb.name = "Omni Branch"
           LEFT JOIN   .special_field_details sfdb ON sfb.uid = sfdb.field_uid AND sfdb.entity_uid = psm.uid

           LEFT JOIN   special_field_fields sfp ON sfp.principal_uid = dm.principal_uid AND sfp.name = "Omni Private Label"
           LEFT JOIN   .special_field_details sfdp ON sfp.uid = sfdp.field_uid AND sfdp.entity_uid = psm.uid


           INNER JOIN  depot d ON d.uid = dm.depot_uid
           INNER JOIN  principal p ON p.uid = dm.principal_uid
           INNER JOIN  smart_event se ON dm.uid = se.data_uid
           WHERE dm.uid = ' . mysqli_real_escape_string($this->dbConn->connection, $docUid) . '
           AND   se.`type` = "EXT";';

		return $this->dbConn->dbGetAll($sql);
  }
// ******************************************************************************************************************
  function setTransactionToDelete($seUid) {
  	 $dsql = "update smart_event se SET se.`status` = 'C', se.general_reference_1 = 'Error Cleared'
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
	public function getOmniErrorNotificationRecipients($principalUId, $seType, $nType) {

        $kosErrorsOnly = "";
        $seErrorCnt    = "";
		
        if($principalUId == 389 ) {
              
              if($nType == CTD_ADMIN_CLERK ) {              	     	
              	     $kosErrorsOnly    =   "AND if(substr(se.status_msg,1,5) = '[KOS]', substr(se.status_msg,1,5) = '[KOS]' 
              	                                                                        AND se.error_count BETWEEN 0 AND 2 ,
              	                                                                        substr(se.status_msg,1,5) = 'NOTHING TO EXTRACT')";
              } else {
                    $kosErrorsOnly     =   "AND if(substr(se.status_msg,1,5) = '[KOS]', substr(se.status_msg,1,5) = '[KOS]' ,
                                                                                        substr(se.status_msg,1,10) = '[OMNI API]')";
              }      
        }
        $sql = "SELECT dm.depot_uid,
                       d.name AS 'Warehouse',
                       sfd.value AS 'WhAbr', 
                       dm.principal_uid,
                       pc.email_addr, 
                       dm.document_number,
                       dh.invoice_date,
                       psm.deliver_name,
                       psm.uid as 'psm.uid',
                       se.uid as 'se_uid',
                       se.general_reference_2,
                       se.general_reference_1,
                       se.status_msg,
                       se.type,
                       se.type_uid,
                       dm.uid as 'dataUid',
                       p.name AS 'Principal',
                       se.error_count
                FROM .smart_event se
                INNER JOIN .document_master dm ON dm.uid = se.data_uid
                INNER JOIN  document_header dh ON dm.uid = dh.document_master_uid
                INNER JOIN  principal_store_master psm ON psm.uid = dh.principal_store_uid
                INNER JOIN  depot d ON d.uid = dm.depot_uid
                INNER JOIN  principal p ON p.uid = dm.principal_uid
                LEFT  JOIN  special_field_fields sff ON sff.principal_uid = dm.principal_uid  AND sff.name = 'Omni Warehouse Code'
                LEFT  JOIN  special_field_details sfd on sff.uid = sfd.field_uid and sfd.entity_uid = psm.depot_uid                
                LEFT  JOIN  principal_contact pc ON  pc.principal_uid    = dm.principal_uid 
                                                 AND pc.contact_type_uid =  " . mysqli_real_escape_string($this->dbConn->connection, $nType) . "
                WHERE dm.principal_uid =  " . mysqli_real_escape_string($this->dbConn->connection, $principalUId) . "
                AND   se.type_uid      =  " . mysqli_real_escape_string($this->dbConn->connection, $seType) . "
                AND   se.`status` <> '" . FLAG_STATUS_CLOSED . "' && se.`status` <> '" . FLAG_STATUS_QUEUED . "' "
                . $kosErrorsOnly   . "
                AND se.error_count BETWEEN 0 AND 3
                ORDER BY pc.email_addr ; ";
                
//              echo $sql;
//              echo "<br>";
                return $this->dbConn->dbGetAll($sql);
}
// ******************************************************************************************************************



}
?>
