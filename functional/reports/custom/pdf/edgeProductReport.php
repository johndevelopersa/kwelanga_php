<?php


/**
 * @author onyx_rtt
 * @created 2013-01-22
 */

require_once($ROOT . $PHPFOLDER . 'libs/pdf/tcpdf/config/lang/eng.php');
require_once($ROOT . $PHPFOLDER . 'libs/pdf/tcpdf/tcpdf.php');
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');


class PDFReport {


  private $dbConn;
  private $pdf;

  public $errorTO;

  function __construct($dbConn) {
    $this->dbConn = $dbConn;
    $this->errorTO = new ErrorTO;
  }

  public function render($dataArr, $reportName){

    global $ROOT, $PHPFOLDER, $principalId; //principal id should be supplied by downloadBase.php

    // create new PDF document
    $this->pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // set document information
    $this->pdf->SetCreator(PDF_CREATOR);
    $this->pdf->SetAuthor('RETAIL TRADING');
    $this->pdf->SetTitle($reportName);

    // set default header data
    $this->pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

    // set header and footer fonts
    $this->pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $this->pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

    // set default monospaced font
    $this->pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    //set margins
    $this->pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_HEADER, PDF_MARGIN_RIGHT);
    $this->pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $this->pdf->SetFooterMargin(20);

    //set auto page breaks
    $this->pdf->SetAutoPageBreak(TRUE, 20);

    //set image scale factor
    $this->pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    // ---------------------------------------------------------

    // set font
    $this->pdf->SetFont('helvetica', '', 8);

    // add a page
    $this->pdf->AddPage();

    // set some text to print

    $txt = '<h1>'.$reportName.'</h1>';
    $txt .= '<div>Date Generated: ' . date('Y/m/d H:i:s') . '</div><br>';

    $n = 0;
    foreach($dataArr as $row){

      if($n%8 == 0){
        if($n != 0){
         $txt .=  '</table><br pagebreak="true"/>';
        }
        $txt .= '<table border="1" align="left" cellpadding="4" width="100%">';
      }
      $n++;

      $templatePhoto = $ROOT . $PHPFOLDER . '/images/product_template.gif';
      $photoPath = $ROOT . $PHPFOLDER . 'uploads/products/' . $principalId .'_'. $row['Unique ID'] .'.jpg';

      if(is_file($photoPath)){
        $templatePhoto = $photoPath;
      }

      $txt .=  '<tr>';
        $txt .=   '<td height="100" width="100">';
          $txt .=   '<img src="'.$templatePhoto.'" height="100" width="100" border="0" alt="" />';
        $txt .= '</td>';
        $txt .=   '<td>';
          $txt .=  'Code: <strong>' . $row['Product Code']  . '</strong><br>';
          $txt .=  'Description: ' . $row['Description'] . '<br>';
          $txt .=  'Category: ' . $row['Category'] . '<br>';
          $txt .=  'Brand: ' . $row['Brand'] . '<br>';
          $txt .=  'Group: ' . $row['Group'] . '<br>';
          $txt .=  'Item Group: ' . $row['Item Group'] . '<br>';
          $txt .=  'Value: ' . $row['Value'];
          #$txt .=  '<small>ID: '. $row['Unique ID'] .'</small>';
        $txt .= '</td>';
        $txt .=   '<td>';
          $txt .=  '<strong>Available Stock</strong><br>';
          $txt .=  'Johannesburg: ' . $row['Johannesburg'] . '<br>';
          $txt .=  'Durban: ' . $row['Durban'] . '<br>';
          $txt .=  'Cape Town: ' . $row['Cape Town'] . '<br>';
          $txt .=  '<br>';
          $txt .=  'Last Arrival: '. $row['Last Arrival'] . '<br>';
          $txt .=  'Last Ordered: '. $row['Last Order'] ;
        $txt .= '</td>';
      $txt .=  '</tr>';


    }
    $txt .=  '</table>';


    $this->pdf->WriteHTML($txt, true, $fill=0, $align='L', $ln=true, $stretch=0, $firstline=false, $firstblock=false, $maxh=0);

  }

  public function save($filename){
    $this->pdf->Output($filename, 'D');
  }

}


// Extend the TCPDF class to create custom Header and Footer
class MYPDF extends TCPDF {

  //Page header
  public function Header() {

      // Title
      $this->SetY(+12);
      $this->SetFont('helvetica', 'B', 20);
      $this->Cell(100, 0, '', 0, false, 'L', 0, '', 0, false, 'M', 'M');

      //SubTitle
      $this->SetY(+19);
      $this->SetFont('helvetica', '', 11);

  }

  // Page footer
  public function Footer() {

    // Position at 15 mm from bottom
    //$this->SetY(-20);
    // Set font
    $this->SetFont('helvetica', 'I', 7);
    // Page number
    $this->Cell(0, 8, 'Powered by Retail Trading', 0, false, 'C', 0, '', 0, false, 'T', 'M');

    $this->SetFont('helvetica', '', 8);
    $this->Cell(0, 0, 'Page ' . $this->getAliasNumPage() . ' of ' . $this->getAliasNbPages() , 0, false, 'R', 0, '', 0, false, 'M', 'M');

  }
}

