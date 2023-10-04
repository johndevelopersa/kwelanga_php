<?php
/* This is an ADAPTOR. As little as possible processing and lookups should happen in here. Leave that to the processing script. 
 * Adaptors should be as lightweight as possible
 * 
 * STORE CREDIT LIMITS IMPORT
 * 
 * Updates the credit limit fields on principal_store_master by using store special fields
 * File Structure : XML
 * Sample :
 * see xsd
  */

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'libs/FileParser.php');
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');
include_once($ROOT.$PHPFOLDER.'TO/PostingTSCLTO.php');

class AdaptorTSCL {
	private $dbConn;
	
	function __construct($dbConn) {
	      $this->dbConn = $dbConn;
    }
    
    function adaptorTSCL_V1($content, $onlineFileProcessItem) {
    	global $importDAO; // NB !!! Assumes that calling script (onlineFileProcessing.php) has importDAO declared outside of any class !
    	$xsd = <<<EOD
<?xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema" elementFormDefault="qualified">
<xs:element name="ar_recon_file">
 <xs:complexType> 
  <xs:sequence>
   <xs:element name="vendor_no" type="xs:string"/>
   <xs:element name="processed_date" type="xs:string"/>
   <xs:element name="ar_recon_det" minOccurs ="1" maxOccurs="unbounded">
    <xs:complexType>
     <xs:sequence>
     <xs:element name="account_no" type="xs:string"/>
     <xs:element name="int_cover" type="xs:float"/>
     <xs:element name="ext_cover" type="xs:float"/>
     <xs:element name="ar_balance" type="xs:float"/>
     </xs:sequence>
    </xs:complexType>
   </xs:element>
  </xs:sequence>
 </xs:complexType>
</xs:element>
</xs:schema>       
EOD;
    	$eTO = new ErrorTO;
    	$xml = new DOMDocument(); 
    	
    	// Test the XML File 
		libxml_use_internal_errors(true); // needed to parse errors below
		$xml->loadXML($content);
		if (!$xml->schemaValidateSource($xsd)) { 
			$errorStr=FileParser::getDOMErrors();
			$eTO->type = FLAG_ERRORTO_ERROR;
    		$eTO->description = "Invalid XML format of file :<br>\n".$errorStr;
    		$eTO->identifier = ET_CUSTOMER;
    		return $eTO;
		} 
		
		// I prefer to process it using SimpleXML into an Array instead of DOM
		$fileArray = FileParser::xmlToArray($content);
	
    	// put into common TO, ignore all the other extra fields
    	$onlineFileProcessingMapping=$importDAO->getMappingFromOIMByPrincipalUId($onlineFileProcessItem["onlineFileProcessingMapping"], $onlineFileProcessItem["principal_uid"]);
    	// this adaptor file is only used for one principal, so treat null or exact principal as same, but must be found !
    	if (empty($onlineFileProcessingMapping)) {
  			$eTO->type = FLAG_ERRORTO_ERROR;
	   		$eTO->description = "Could not retrieve online file principal mappings";
	   		$eTO->identifier = ET_SYSTEM;
	    	return $eTO;
  		}
    			
    	$arrTO=array();
    	// if there is only 1 row, then the xml parser stores it as a non-array ie there is no [0]
    	if (!isset($fileArray["ar_recon_det"][0])) {
    		$temp=$fileArray["ar_recon_det"];
    		unset($fileArray["ar_recon_det"]);
    		$fileArray["ar_recon_det"][0]=$temp;
    	}
    	foreach ($fileArray["ar_recon_det"] as $fa) {
    		
    		$faTO=new PostingTSCLTO;
    		$faTO->principalId = $onlineFileProcessItem["principal_uid"];
    		$faTO->sourceAdaptorName = $onlineFileProcessItem["adaptor_name"];
    		$faTO->vendorId = $onlineFileProcessItem["vendor_uid"];
    		$faTO->specialStoreFieldIdForLookup = $onlineFileProcessingMapping["psm_special_field_uid"]; // special store field, SGX Bill To
    		$faTO->specialStoreFieldValue = $fa["account_no"];
    		$faTO->creditBalance = floatval($fa["ar_balance"]);
    		$faTO->creditLimit = floatval($fa["int_cover"])+floatval($fa["ext_cover"]);
    		$faTO->principalStoreUId = ""; // force lookup to happen inside processing on the special fields
  	
  			$arrTO[]=$faTO;
    	}
    	
   		$eTO->type = FLAG_ERRORTO_SUCCESS;
   		$eTO->description = "Successful";
   		$eTO->object = $arrTO;
   		
    	return $eTO;
    }
	
}
  


?>