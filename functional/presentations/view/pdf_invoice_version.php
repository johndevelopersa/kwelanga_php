<?php

/*
 *
 *      OUTPUTS PDF latest
 *
 */
 
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER."DAO/TransactionDAO.php");

include_once ($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER.'properties/dbSettings.inc');

require_once($ROOT . $PHPFOLDER . 'libs/pdf/tcpdf/config/lang/eng.php');
require_once($ROOT . $PHPFOLDER . 'libs/pdf/tcpdf/tcpdf.php');

echo "PP";


$userId = ((isset($_GET["USERID"]))?$_GET["USERID"]:"");
$userCategory = ((isset($_GET["USERCATEGORY"]))?$_GET["USERCATEGORY"]:"");
$principalId  = ((isset($_GET["PRINCIPALID"]))?$_GET["PRINCIPALID"]:$prinUid);
$docmastId    = ((isset($_GET["DOCMASTID"]))?$_GET["DOCMASTID"]:$docUid);
$outputTyp    = ((isset($_GET["OUTPUTTYP"]))?$_GET["OUTPUTTYP"]:$oType);
$fullpath     = ((isset($_GET["FULLPATH"]))?$_GET["FULLPATH"]:$oType);

//Create new database object
$dbConn = new dbConnect(); 
$dbConn->dbConnection();

$transactionDAO = new TransactionDAO($dbConn);
$mfT = $transactionDAO->getDocumentWithDetailIgnorePermissionsItem(mysqli_real_escape_string($dbConn->connection, $docmastId));

// print_r($mfT); 

if (!isset($_SESSION)) session_start();
$_SESSION['header'] = array('principal'    => $mfT[0]['principal_name'],
                            'prin_add1'    => $mfT[0]['prin_ph_add1'],
                            'prin_add2'    => $mfT[0]['prin_ph_add2'],
                            'prin_add3'    => $mfT[0]['prin_ph_add3'],
                            'prin_vat'     => $mfT[0]['prin_vat'],
                            'office_tel'   => $mfT[0]['office_tel'],
                            'principalUid' => $mfT[0]['principal_uid']);

// Extend the TCPDF class to create custom Header and Footer
class MYPDF extends TCPDF {

	//Page header
	public function Header() {
                // Title
                $this->SetY(+10);
                $this->SetFont('helvetica', 'B', 16);
                $this->Cell(0, 0, '	Tax Invoice' . ((isset($_GET['VARIANCE'])&&$_GET['VARIANCE']==1)?' : Variance' : ''), 0, false, 'C', 0, '', 0, false, 'M', 'M');

                //Principal Details
                $this->SetY(+20);
                $this->SetFont('helvetica', 'B', 12);

                $this->Cell(0, 0, $_SESSION['header']['principal'], 0, false, 'L', 0, '', 0, false, 'M', 'M');
                $this->Image('https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/images/logos/' .$_SESSION['header']['principalUid'] .'.jpg', 145,10,31,27);   //  X Y W H

                //Principal Details
                $this->SetY(+25);
                $this->SetFont('helvetica', 'B', 12);
                $this->Cell(0, 0,  $_SESSION['header']['prin_add1'], 0, false, 'L', 0, '', 0, false, 'M', 'M');
                //Principal Details
                $this->SetY(+30);
                $this->SetFont('helvetica', 'B', 12);
                $this->Cell(0, 0,  $_SESSION['header']['prin_add2'], 0, false, 'L', 0, '', 0, false, 'M', 'M');
                //Principal Details
                $this->SetY(+35);
                $this->SetFont('helvetica', 'B', 12);
                $this->Cell(0, 0,  $_SESSION['header']['prin_add3'], 0, false, 'L', 0, '', 0, false, 'M', 'M');

                //Principal Details
                $this->SetY(+42);
                $this->SetFont('helvetica', 'B', 12);
                $this->Cell(0, 0, 'VAT No  ' . $_SESSION['header']['prin_vat'], 0, false, 'L', 0, '', 0, false, 'M', 'M');

                $this->SetFont('helvetica', 'B', 12);
                $this->Cell(0, 0, 'Tel No  ' . substr($_SESSION['header']['office_tel'],0,3) . ' ' . substr($_SESSION['header']['office_tel'],3,3) . ' ' . substr($_SESSION['header']['office_tel'],6,4) , 0, false, 'R', 0, '', 0, false, 'M', 'M');
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
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_HEADER+45, PDF_MARGIN_RIGHT);
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

// Create Invoice Details

if($mfT[0]['invoice_number'] != '') { 
       $docNo = "BR" . ltrim($mfT[0]['invoice_number'],'0');
} else {
       $docNo = "BR" . ltrim($mfT[0]['document_number'],'0');
}

$txt .= '<style type="text/css">
          td.phead {text-align:center; 
                     font-weight:bold; 
                     font-size:35px;  
                     border-style:solid solid solid solid;
                     border-width:1px;
                    }
          td.pdet {"text-align:left; 
                    font-weight:normal; 
                    font-size:30px;
                    height: 30px;
                    vertical-align:middle;
                    border-style:solid solid solid solid;
                    border-left-style:solid;
                    border-width:1px;"
                   }          
</style>';


$txt .=  '<table border="0" align="left" cellpadding="0" width="100%">';
$txt .=  '<tr>';
$txt .=  '<td style="width: 50%; 
                     text-align:left; 
                     font-weight:bold; 
                     font-size:45px; 
                     text-align:left; ">Invoice To
          </td>';
$txt .=  '<td style="width: 50%; 
                     text-align:Right;
                     font-size:43px;"><span style="font-weight:bold">Invoice Date&nbsp;&nbsp;</span>
                                      <span style="font-weight:normal;">' . $mfT[0]['invoice_date']  . '</span>
          </td>';
$txt .=  '</tr>';

$txt .=  '<tr>';
$txt .=  '<td colspan="2"; style="width: 50%; text-align:left;">&nbsp;</td>';
$txt .=  '</tr>';
$txt .=  '<tr>';
$txt .=  '<td style="width: 50%; 
                     text-align:left; 
                     font-weight:normal; 
                     font-size:43px; 
                     text-align:left; ">' . trim($mfT[0]['store_name'])  . '
          </td>';
$txt .=  '<td style="width: 50%; 
                     text-align:Right;
                     font-size:43px;"><span style="font-weight:bold">Invoice Number&nbsp;&nbsp;</span>
                                      <span style="font-weight:normal;">' . $docNo  . '</span>
          </td>';
$txt .=  '</tr>';

$txt .=  '<tr>';
$txt .=  '<td style="width: 50%; 
                     text-align:left; 
                     font-weight:normal; 
                     font-size:43px; 
                     text-align:left; ">' . trim($mfT[0]['deliver_add1'])  . '
          </td>';
$txt .=  '<td style="width: 50%; 
                     text-align:Right;
                     font-size:43px;">&nbsp;
          </td>';
$txt .=  '</tr>';

$txt .=  '<tr>';
$txt .=  '<td style="width: 50%; 
                     text-align:left; 
                     font-weight:normal; 
                     font-size:43px; 
                     text-align:left; ">' . trim($mfT[0]['deliver_add2'])  . '
          </td>';
$txt .=  '<td style="width: 50%; 
                     text-align:Right;
                     font-size:43px;"><span style="font-weight:bold">Order Number&nbsp;&nbsp;</span>
                                      <span style="font-weight:normal;">' . $mfT[0]['customer_order_number']  . '</span>
          </td>';
$txt .=  '</tr>';

$txt .=  '<tr>';
$txt .=  '<td style="width: 50%; 
                     text-align:left; 
                     font-weight:normal; 
                     font-size:43px; 
                     text-align:left; ">' . trim($mfT[0]['deliver_add3'])  . '
          </td>';
$txt .=  '<td style="width: 50%; 
                     text-align:Right;
                     font-size:43px;">&nbsp;
          </td>';
$txt .=  '</tr>';

$txt .=  '<tr>';
$txt .=  '<td style="width: 50%; 
                     text-align:left; 
                     font-weight:normal; 
                     font-size:43px; 
                     text-align:left; "><span style="font-weight:bold">VAT No&nbsp;&nbsp;</span>' . trim($mfT[0]['vat_number'])  . '
          </td>';
$txt .=  '<td style="width: 50%; 
                     text-align:Right;
                     font-size:43px;">&nbsp;
          </td>';
$txt .=  '</tr>';

$txt .=  '</table>';





$txt .=  '<table align="left" width="100%" >';
$txt .=  '<tr>';
$txt .=  '<td colspan=8;>&nbsp;</td>';
$txt .=  '</tr>';
$txt .=  '<tr>';
$txt .=  '<td colspan=8;>&nbsp;</td>';
$txt .=  '</tr>';
$txt .=  '<tr>';
$txt .=  '<td class="Phead"; style="width:15%;">&nbsp;&nbsp;Product<br>Code</td>';
$txt .=  '<td class="Phead"; style="width:40%;">&nbsp;&nbsp;Product</td>';
$txt .=  '<td class="Phead"; style="width:6%;" >&nbsp;&nbsp;Qty</td>';
$txt .=  '<td class="Phead"; style="width:7%;" >&nbsp;&nbsp;Price</td>';
$txt .=  '<td class="Phead"; style="width:10%;">&nbsp;&nbsp;Excl Price</td>';
$txt .=  '<td class="Phead"; style="width:7%;" >&nbsp;&nbsp;Vat Rate</td>';
$txt .=  '<td class="Phead"; style="width:8%;" >&nbsp;&nbsp;Vat</td>';
$txt .=  '<td class="Phead"; style="width:8%;" >&nbsp;&nbsp;Total</td>';
$txt .=  '</tr>';

$casesTotal = $extTotal = $vatTotal = $totTotal  = 0;

foreach($mfT as $row) {
      $txt .=  '<tr>';	
      $txt .=  '<td class="pdet"; style = "text-align:left;">&nbsp;&nbsp;' . trim($row["product_code"]) . '</td>';
                          	
      $txt .=  '<td class="pdet"; style = "text-align:left;">&nbsp;&nbsp;' . trim($row["product_description"]) . '</td>';	
      $txt .=  '<td class="pdet"; style = "text-align:center;">&nbsp;&nbsp;' . $row["document_qty"] . '</td>';		
      $txt .=  '<td class="pdet"; style = "text-align:right;">' . number_format($row["net_price"],2, "."," ") . '&nbsp;&nbsp;</td>';		
      $txt .=  '<td class="pdet"; style = "text-align:right;">' . number_format($row["extended_price"],2, "."," ") . '&nbsp;&nbsp;</td>';		
      $txt .=  '<td class="pdet"; style = "text-align:right;">' . number_format($row["vat_rate"],2, "."," ") . '&nbsp;&nbsp;</td>';		
      $txt .=  '<td class="pdet"; style = "text-align:right;">' . number_format($row["vat_amount"],2, "."," ") . '&nbsp;&nbsp;</td>';				     
      $txt .=  '<td class="pdet"; style = "text-align:right;">' . number_format($row["total"],2, "."," ") . '&nbsp;&nbsp;</td>';      
      $txt .=  '</tr>';
			
      $casesTotal = $casesTotal + $row["document_qty"];
      $extTotal   = $extTotal   + $row["extended_price"];
      $vatTotal   = $vatTotal   + $row["vat_amount"];
      $totTotal   = $totTotal   + $row["total"];
}

$txt .=  '<tr>';
$txt .=  '<td class = "pdet"; style="text-align:right;" >&nbsp;</td>';
$txt .=  '<td class = "pdet"; style="text-align:right;" >Total&nbsp;&nbsp;&nbsp;</td>';
$txt .=  '<td class = "pdet"; style="text-align:center;">&nbsp;&nbsp;' . $casesTotal . '</td>';
$txt .=  '<td class = "pdet"; style="text-align:right;" >&nbsp;</td>';                    
$txt .=  '<td class = "pdet"; style="text-align:right;">' . number_format($extTotal,2, "."," ") . '&nbsp;&nbsp;</td>';
$txt .=  '<td class = "pdet"; style="text-align:right;">&nbsp;</td>';
$txt .=  '<td class = "pdet"; style="text-align:right;">' . number_format($vatTotal,2, "."," ") . '&nbsp;&nbsp;</td>';
$txt .=  '<td class = "pdet"; style="text-align:right;">' . number_format($totTotal,2, "."," ") . '&nbsp;&nbsp;</td>';
$txt .=  '</tr>';
$txt .=  '</table>';

$txt .=  '<table align="left" width="100%" >';
$txt .=  '<tr>';
$txt .=  '<tr>';
$txt .=  '<td colspan=2;>&nbsp;</td>';
$txt .=  '</tr>';
$txt .=  '<tr>';
$txt .=  '<td colspan=2;>&nbsp;</td>';
$txt .=  '</tr>';
$txt .=  '<tr>';
$txt .=  '<td colspan=2;>&nbsp;</td>';
$txt .=  '</tr>';
if (trim($mfT[0]["banking_details"]) <> NULL && !in_array($mfT[0]["document_type_uid"],array('4','2')) && ($mfT[0]["bank_details_to_print"] == 2) )  {
        $txt .=  '<td style="width:50%"; "text-align:left"; nowrap ><span style = "font-weight:bold; font-size:40px;">Banking Details<br></span>' . (str_replace(chr(43),"<br>", $mfT[0]["banking_details"])) . '</td>';
} else { 
        $txt .=  '<td >&nbsp;</td>';
}
$txt .=  '</tr>';
$txt .=  '<tr>';
$txt .=  '<td colspan=2;>&nbsp;</td>';
$txt .=  '</tr>';
$txt .=  '<tr>';
$txt .=  '<td colspan=2;>&nbsp;</td>';
$txt .=  '</tr>';
$txt .=  '<tr>';
$txt .=  '<td><img alt=' . $mfT[0]["principal_uid"]  . ' - ' .  ltrim(substr($mfT[0]["document_number"],0,8),'0') .' src="https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/barcode/barcode.php?text=' . $mfT[0]["principal_uid"] . ' - ' . ltrim(substr($mfT[0]["document_number"],0,8),'0') .'&print=true" /></td>';
$txt .=  '</tr>';




$txt .=  '</table>';

// print a block of text using Write()
$pdf->WriteHTML( $txt, true, $fill=0, $align='L', $ln=true, $stretch=0, $firstline=false, $firstblock=false, $maxh=0);

// ---------------------------------------------------------

$pdf->lastPage();

ob_clean();

if($outputTyp == "F") {
	
     //Close and output PDF document
     return $pdf->Output($fullpath, 'F');	
	
} else {
    //Close and output PDF document
      $pdf->Output('rt_stocktakesheet_'.date('Ymd').'.pdf', 'I');	
	
}

// return 'TRUE';







//============================================================+
// END OF FILE
//============================================================+

?>