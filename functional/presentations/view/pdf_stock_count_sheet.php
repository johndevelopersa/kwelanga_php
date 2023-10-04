<?php

/*
 *
 *      OUTPUTS PDF latest
 *
 */
 
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER."DAO/StockByCatDAO.php");

include_once ($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER.'properties/dbSettings.inc');

require_once($ROOT . $PHPFOLDER . 'libs/pdf/tcpdf/config/lang/eng.php');
require_once($ROOT . $PHPFOLDER . 'libs/pdf/tcpdf/tcpdf.php');


if (!isset($_SESSION)) session_start();
$prin  = $_SESSION['principal_id'] ;
$dep   = $_SESSION['depot_id'] ;

$category = ((isset($_GET["CATEGORY"]))?$_GET["CATEGORY"]:"");
$prinx     = ((isset($_GET["PRIN"]))?$_GET["PRIN"]:"");
$depx      = ((isset($_GET["WH"]))?$_GET["WH"]:"");

//Create new database object  
$dbConn = new dbConnect(); 
$dbConn->dbConnection();

$StockByCatDAO = new StockByCatDAO($dbConn);
$prodSheet = $StockByCatDAO->getProductCountSheet($category, $prin, $dep);

//print_r($prodSheet); 



// Extend the TCPDF class to create custom Header and Footer
class MYPDF extends TCPDF {

	//Page header
	public function Header() {
                // Title
                $this->SetY(+10);
                $this->SetFont('helvetica', 'B', 16);
                $this->Cell(0, 0, '	Stock Count Sheet' . ((isset($_GET['VARIANCE'])&&$_GET['VARIANCE']==1)?' : Variance' : ''), 0, false, 'C', 0, '', 0, false, 'M', 'M');

                //Principal Details
                $this->SetY(+1);
                $this->SetFont('helvetica', 'B', 12);

                $this->SetY(+18);
                $this->SetFont('helvetica', 'B', 11);
                $this->Cell(0, 0, '    Category                                 Product Code          Product                                             Count' , 0, false, 'L', 0, '', 0, false, 'M', 'M');

                $this->SetY(+22);
                $this->SetFont('helvetica', 'B', 11);
                $this->Cell(0, 0, '------------------------------------------------------------------------------------------------------------------------------------------' , 0, false, 'L', 0, '', 0, false, 'M', 'M');

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
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_HEADER+18, PDF_MARGIN_RIGHT);
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
                    height: 40px;
                    vertical-align:middle;
                    border-style:solid solid solid solid;
                    border-left-style:solid;
                    border-width:1px;"
                   }          
</style>';


$txt .=  '<table border="0" align="left" cellpadding="0" width="100%">';
$txt .=  '<table align="left" width="100%" >';
$txt .=  '<tr>';
$txt .=  '<td colspan=4;>&nbsp;</td>';
$txt .=  '</tr>';
$txt .=  '<tr>';
$txt .=  '<td colspan=4;>&nbsp;</td>';
$txt .=  '</tr>';
$txt .=  '<tr>';
$txt .=  '<td style="width:30%;">&nbsp;&nbsp; </td>';
$txt .=  '<td style="width:20%;">&nbsp;&nbsp; </td>';
$txt .=  '<td style="width:40%;">&nbsp;&nbsp; </td>';
$txt .=  '<td style="width:10%;">&nbsp;&nbsp; </td>';
$txt .=  '</tr>'; 

foreach($prodSheet as $row) {
      $txt .=  '<tr>'; 
      $txt .=  '<td class="pdet"; style = "text-align:left;">&nbsp;&nbsp;' . trim($row["Category"]) . '</td>';
      $txt .=  '<td class="pdet"; style = "text-align:left;">&nbsp;&nbsp;' . trim($row["ProductCode"]) . '</td>';
      $txt .=  '<td class="pdet"; style = "text-align:left;">&nbsp;&nbsp;' . trim($row["Product"]) . '</td>';	
      $txt .=  '<td class="pdet"; style = "text-align:left;">&nbsp;&nbsp;</td>'; 
      $txt .=  '</tr>';

}
$txt .=  '<tr>';
$txt .=  '<td colspan=4;>&nbsp;</td>';
$txt .=  '</tr>';
$txt .=  '<tr>';
$txt .=  '<td colspan=4;>&nbsp;</td>';
$txt .=  '</tr>';
$txt .=  '<tr>';
$txt .=  '<td colspan=4;>***EOR***</td>';
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