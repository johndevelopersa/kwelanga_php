<?php

/*
 *
 *      OUTPUTS PDF
 *
 */
 
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER."DAO/TransactionDAO.php");

include_once ($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER.'properties/dbSettings.inc');

require_once($ROOT . $PHPFOLDER . 'libs/pdf/tcpdf/config/lang/eng.php');
require_once($ROOT . $PHPFOLDER . 'libs/pdf/tcpdf/tcpdf.php');


$userId = ((isset($_GET["USERID"]))?$_GET["USERID"]:"");
$userCategory = ((isset($_GET["USERCATEGORY"]))?$_GET["USERCATEGORY"]:"");
$principalId  = ((isset($_GET["PRINCIPALID"]))?$_GET["PRINCIPALID"]:$prinUid);
$docmastId    = ((isset($_GET["DOCMASTID"]))?$_GET["DOCMASTID"]:$docUid);
$outputTyp    = ((isset($_GET["OUTPUTTYP"]))?$_GET["OUTPUTTYP"]:$oType);

//Create new database object
$dbConn = new dbConnect(); 
$dbConn->dbConnection();

$transactionDAO = new TransactionDAO($dbConn);
$docNo = $transactionDAO->getUpliftDocumentNumber(mysqli_real_escape_string($dbConn->connection, $docmastId));

$transactionDAO = new TransactionDAO($dbConn);
$mfT = $transactionDAO->getUpliftDetailsToUpdate($principalId,$docNo[0]['document_number']);

// print_r($mfT);

if (!isset($_SESSION)) session_start();
$_SESSION['header'] = array('principal'=>$mfT[0]['Principal'],
                            'principalUid'=>$principalId,
                            'depot'=>$mfT[0]['warehouse'], 
                            'store'=>$mfT[0]['deliver_name'], 
                            'docno'=>ltrim($mfT[0]['document_number'],'0'),
                            'store'=>$mfT[0]['deliver_name']);

// Extend the TCPDF class to create custom Header and Footer
class MYPDF extends TCPDF {

	//Page header
	public function Header() {
                // Title
                $this->SetY(+10);
                $this->SetFont('helvetica', 'B', 16);
                $this->Cell(0, 0, '	Iram Uplift Instructions Form' . ((isset($_GET['VARIANCE'])&&$_GET['VARIANCE']==1)?' : Variance' : ''), 0, false, 'C', 0, '', 0, false, 'M', 'M');

                $this->SetFont('helvetica', '', 9);
                $this->Cell(20, 0, 'Page ' . $this->getAliasNumPage() . ' of ' . $this->getAliasNbPages() , 0, false, 'R', 0, '', 0, false, 'M', 'M');

                //SubTitle
                $this->SetY(+20);
                $this->SetFont('helvetica', 'B', 12);

                $this->Cell(0, 0, $_SESSION['header']['principal'], 0, false, 'L', 0, '', 0, false, 'M', 'M');

                $this->SetFont('helvetica', '', 10);
                $this->Cell(0, 0,$_SESSION['header']['depot'], 0, false, 'R', 0, '', 0, false, 'M', 'M');

                //SubTitle
                $this->SetY(+27);
                $this->SetFont('helvetica', 'B', 12);

                $this->Cell(0, 0,  $_SESSION['header']['store'], 0, false, 'L', 0, '', 0, false, 'M', 'M');

                $this->SetFont('helvetica', '', 10);
                $this->Cell(0, 0, date('Y-m-d'), 0, false, 'R', 0, '', 0, false, 'M', 'M');

                //SubTitle
                $this->SetY(+32);
                $this->SetFont('helvetica', 'B', 12);

                $this->Cell(0, 0, $_SESSION['header']['principalUid'] . "-" . $_SESSION['header']['docno'], 0, false, 'L', 0, '', 0, false, 'M', 'M');

                $this->SetFont('helvetica', '', 10);
                $this->Cell(0, 0, 'Reference ________________________________', 0, false, 'R', 0, '', 0, false, 'M', 'M');

	}

	// Page footer
	public function Footer() {

		// Position at 15 mm from bottom
		//$this->SetY(-20);
		// Set font
		$this->SetFont('helvetica', 'I', 7);
		// Page number
		$this->Cell(0, 10, 'Powered by Kwelanga Online Solutions (Pty) Ltd', 0, false, 'C', 0, '', 0, false, 'T', 'M');
	}
}
// create new PDF document
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);


// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Kwelanga Solutions');
$pdf->SetTitle('STOCK PRINTOUT');
//$pdf->SetSubject('TCPDF Tutorial');
//$pdf->SetKeywords('TCPDF, PDF, example, test, guide');

// set default header data
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

// set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

//set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_HEADER+30, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER+20);
$pdf->SetFooterMargin(15);

//set auto page breaks
$pdf->SetAutoPageBreak(TRUE, 55);

//set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

//set some language-dependent strings
$pdf->setLanguageArray($l);

// ---------------------------------------------------------

// set font
$pdf->SetFont('helvetica', '', 8);

// add a page
$pdf->AddPage();


// set some text to print



$txt .=  '<table border="0" align="left" cellpadding="4" width="100%" >';
$txt .=  '<tr>';
$txt .=  '<td colspan=11;>&nbsp;</td>';
$txt .=  '</tr>';

$txt .=  '<tr>';
$txt .=  '<td width="13%" height="4px" border="1">Product&nbsp;Code</td>';
$txt .=  '<td width="9%"  height="4px" border="1" >Article<br>Number</td>';
$txt .=  '<td width="30%" height="4px" border="1" >Product</td>';
$txt .=  '<td width="7%"  height="4px" border="1" >Value</td>';
$txt .=  '<td width="6%"  height="4px" border="1" >Uplift<br>Qty</td>';
$txt .=  '<td width="7%"  height="4px" border="1" >Uplifted</td>';
$txt .=  '<td width="7%"  height="4px" border="1" >Display</td>';
$txt .=  '<td width="7%"  height="4px" border="1" >Store<br>Refuse</td>';
$txt .=  '<td width="6%"  height="4px" border="1" >Not<br>Found</td>';
$txt .=  '<td width="8%"  height="4px" border="1" >Damage</td>';
$txt .=  '</tr>';

$uplTotal = 0;

foreach($mfT as $row) {
      $txt .=  '<tr>';	
      $txt .=  '<td height="4px" border="1">' . trim($row["alt_code"]) . '</td>';	
      $txt .=  '<td height="4px" border="1">' . trim($row["ArticleNo"]) . '</td>';	
      $txt .=  '<td height="4px" border="1">' . trim($row["product_description"]) . ' - ' . trim($row["product_code"]) . '</td>';	
      $txt .=  '<td height="4px" border="1">' . $row["additional_type"] . '</td>';	
      $txt .=  '<td height="4px" border="1">' . $row["ordered_qty"] . '</td>';	
      $txt .=  '<td height="4px" border="1">&nbsp;</td>';	
      $txt .=  '<td height="4px" border="1">&nbsp;</td>';	      
      $txt .=  '<td height="4px" border="1">&nbsp;</td>';	      
      $txt .=  '<td height="4px" border="1">&nbsp;</td>';	      
      $txt .=  '<td height="4px" border="1">&nbsp;</td>';	      
      
      $txt .=  '</tr>';
			
      $uplTotal = $uplTotal + $row["ordered_qty"];
}

$txt .=  '<tr>';
$txt .=  '<td height="4px" border="0" >&nbsp;</td>';
$txt .=  '<td height="4px" border="0" >&nbsp;</td>';
$txt .=  '<td height="4px" border="1" >Total</td>';
$txt .=  '<td height="4px" border="0" >&nbsp;</td>';
$txt .=  '<td height="4px" border="1" >' . $uplTotal . '</td>';
$txt .=  '<td height="4px" border="0" >&nbsp;</td>';
$txt .=  '<td height="4px" border="0" >&nbsp;</td>';
$txt .=  '<td height="4px" border="0" >&nbsp;</td>';
$txt .=  '<td height="4px" border="0" >&nbsp;</td>';
$txt .=  '<td height="4px" border="0" >&nbsp;</td>';
$txt .=  '</tr>';

$txt .=  '<tr>';
$txt .=  '<td height="4px" border="0" >&nbsp;</td>';
$txt .=  '<td height="4px" border="0" >&nbsp;</td>';
$txt .=  '<td height="4px" border="0" >&nbsp;</td>';
$txt .=  '<td height="4px" border="1" >No. Boxes</td>';
$txt .=  '<td height="4px" border="1" >&nbsp;</td>';
$txt .=  '<td height="4px" border="0" >&nbsp;</td>';
$txt .=  '<td height="4px" border="0" >&nbsp;</td>';
$txt .=  '<td height="4px" border="0" >&nbsp;</td>';
$txt .=  '<td height="4px" border="0" >&nbsp;</td>';
$txt .=  '<td height="4px" border="0" >&nbsp;</td>';
$txt .=  '</tr>';

$txt .=  '<tr>';
$txt .=  '<td colspan="9" >&nbsp;</td>';
$txt .=  '</tr>';

$txt .=  '<tr>';
$txt .=  '<td colspan="2"  height="6px" border="1"  >Store Employee Name & Sign</td>';
$txt .=  '<td colspan="3"  height="6px" border="1" >&nbsp;</td>';
$txt .=  '<td colspan="2"  height="6px" border="1"  >Date</td>';
$txt .=  '<td colspan="2"  height="6px" border="1" >&nbsp;</td>';
$txt .=  '</tr>';
$txt .=  '</table>';

// print a block of text using Write()
$pdf->WriteHTML( $txt, true, $fill=0, $align='L', $ln=true, $stretch=0, $firstline=false, $firstblock=false, $maxh=0);

// ---------------------------------------------------------

$pdf->lastPage();

ob_clean();

if($outputTyp == "F") {
	
	echo $mfT[0]['document_number'];
	echo "<br>";
	
	   $fullpath = 'C:/inetpub/wwwroot/systems/kwelanga_system/ftp/rvl/UpliftDocuments/R-'.trim($mfT[0]['deliver_name']) .' - ' . ltrim($mfT[0]['document_number'],'0') .'.pdf';

//  unlink($fullpath);
	   	   
     //Close and output PDF document
     $pdf->Output($fullpath, 'F');	
	
} else {
    //Close and output PDF document
      $pdf->Output('rt_stocktakesheet_'.date('Ymd').'.pdf', 'I');	
	
}

return 'TRUE';







//============================================================+
// END OF FILE
//============================================================+

?>