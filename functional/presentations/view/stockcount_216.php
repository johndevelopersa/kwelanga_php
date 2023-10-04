<?php

/*
 *
 *      OUTPUTS PDF
 *
 */


require_once($ROOT . $PHPFOLDER . 'libs/pdf/tcpdf/config/lang/eng.php');
require_once($ROOT . $PHPFOLDER . 'libs/pdf/tcpdf/tcpdf.php');

//echo '<pre>';
//var_dump($stockArr[0]['principal_name']);
if (!isset($_SESSION)) session_start();
$_SESSION['header'] = array('principal'=>$stockArr[0]['principal_name'],'depot'=>$stockArr[0]['depot_name']);



// Extend the TCPDF class to create custom Header and Footer
class MYPDF extends TCPDF {

	//Page header
	public function Header() {

            global $principalName;


                // Title
                $this->SetY(+12);
		$this->SetFont('helvetica', 'B', 20);
                $this->Cell(100, 0, 'Stock count sheet' . ((isset($_GET['VARIANCE'])&&$_GET['VARIANCE']==1)?' : Variance' : ''), 0, false, 'L', 0, '', 0, false, 'M', 'M');

                $this->SetFont('helvetica', '', 9);
                $this->Cell(0, 0, 'Page ' . $this->getAliasNumPage() . ' of ' . $this->getAliasNbPages() , 0, false, 'R', 0, '', 0, false, 'M', 'M');

                //SubTitle
                $this->SetY(+19);
                $this->SetFont('helvetica', '', 11);

                $this->Cell(0, 0, $_SESSION['header']['principal'] . ' - ' .  $_SESSION['header']['depot'] . ' - ' . date('Y-m-d'), 0, false, 'L', 0, '', 0, false, 'M', 'M');

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
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_HEADER+20, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER+20);
$pdf->SetFooterMargin(15);

//set auto page breaks
$pdf->SetAutoPageBreak(TRUE, 15);

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


$txt = '<table border="1" align="left" cellpadding="4" width="100%" >';
$txt .=  '<thead><tr bgcolor="#efefef" >';

if(isset($_GET['IMAGES'])){
  $txt .=  '<th width="100"></th>';
}
$txt .=  '<th width="150" >Product Catagory</th><th width="100" >Product Code</th><th width="300">Product Description</th><th width="50">Count</th></tr></thead>';

foreach($stockArr as $p){
  $txt .= '<tr nobr="true">';

  if(isset($_GET['IMAGES'])){

    $isImageURL = $ROOT . $PHPFOLDER . 'uploads/products/' . $p['principal_uid'] .'_'. $p['product_uid'] .'.jpg';   //45_16052.jpg
    $imageURL = (is_file($isImageURL)) ? ($isImageURL) : ($ROOT . $PHPFOLDER . 'images/product_template.gif');

    $txt .= '<td width="100"><img src="'.$imageURL.'" height="100" width="100" border="0" alt="" /></td>';
    $txt .= '<td><strong>' . $p['depot_name'] .'</strong></td>' .
    $txt .= '<td><strong>' . $p['product_code'] .'</strong></td>' .
                '<td width="200">' . $p['product_description'] . '</td>'.
            '<td width="100"></td>';
  } else {
  	$txt .= '<td width="150"><strong>' . trim($p['Catagory']) .'</strong></td>' .
            '<td width="100" height="25"><strong>' . trim($p['product_code']) .'</strong></td>
             <td width="300">'. trim($p['product_description']) .'</td>
             <td width="50">&nbsp;</td>';

  }
  

  $txt .= '</tr>';

}

$txt .=  '</table>';


// print a block of text using Write()
$pdf->WriteHTML( $txt, true, $fill=0, $align='L', $ln=true, $stretch=0, $firstline=false, $firstblock=false, $maxh=0);

// ---------------------------------------------------------

$pdf->lastPage();

ob_clean();

//Close and output PDF document
$pdf->Output('rt_stocktakesheet_'.date('Ymd').'.pdf', 'I');



//============================================================+
// END OF FILE
//============================================================+

?>