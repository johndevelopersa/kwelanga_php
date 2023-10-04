<?php

/*
 * https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/functional/presentations/view/pdf_tripsheet_loading_summary.php?TRIPNUMBER=5407&DEPID=417
 *      OUTPUTS PDF
 *
 */
 
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER."DAO/PrintInvoicesDAO.php");
include_once ($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER.'properties/dbSettings.inc');

require_once($ROOT . $PHPFOLDER . 'libs/pdf/tcpdf/config/lang/eng.php');
require_once($ROOT . $PHPFOLDER . 'libs/pdf/tcpdf/tcpdf.php');

$tripId    = ((isset($_GET["TRIPNUMBER"]))?$_GET["TRIPNUMBER"]:'');
$depotId   = ((isset($_GET["DEPID"]))?$_GET["DEPID"]:'');

global $tripId, $driver, $iDate ;

//Create new database object
$dbConn = new dbConnect(); 
$dbConn->dbConnection();

$invList = new PrintInvoicesDAO($dbConn);
$mfT = $invList->getDocumentWithDetailByTripSheet($tripId, 417, $orderBy='FALSE');

// print_r($mfT);

class MYPDF extends TCPDF {
	
     //Page header
     public function Header() {
     	
     	  global $tripId, $driver, $iDate ;
     	  
         $docType = 'Trip Sheet Loading List';
     	   
     	   $style = array(
                       'position' => 'R',
                       'align' => 'R',
                       'stretch' => false,
                       'fitwidth' => true,
                       'cellfitalign' => '',
                       'border' => false,
                       'hpadding' => '0',
                       'vpadding' => '0',
                       'fgcolor' => array(0,0,0),
                       'bgcolor' => false, //array(255,255,255),
                       'text' => true,
                       'font' => 'helvetica',
                       'fontsize' => 8,
                       'stretchtext' => 4
                        );
     	   	   
         // Title
         $this->SetY(+10);
         $this->SetFont('helvetica', 'B', 16);
         $this->Cell(0, 0, $docType . ((isset($_GET['VARIANCE'])&&$_GET['VARIANCE']==1)?' : Variance' : ''), 0, false, 'C', 0, '', 0, false, 'M', 'M');

         $this->SetY(+10);
         $this->Cell(0, 0, '', 0, 1);
         $this->write1DBarcode($tripId, 'C128', '', '', '', 18, 0.4, $style, 'N');

         $this->SetY(+25);
         $this->SetFont('helvetica', 'B', 12);
         $this->Cell(60, 0, 'Trip Sheet Number ', 0, false, 'L', 0, '', 0, false, 'M', 'M');
         $this->SetFont('helvetica', 'N', 12);
         $this->Cell(60, 0, $tripId, 0, false, 'L', 0, '', 0, false, 'M', 'M');
 
         
         $this->SetY(+33);
         $this->SetFont('helvetica', 'B', 12);
         $this->Cell(60, 0, 'Driver' , 0, false, 'L', 0, '', 0, false, 'M', 'M');
         $this->SetFont('helvetica', 'N', 12);
         $this->Cell(60, 0, $driver, 0, false, 'L', 0, '', 0, false, 'M', 'M');         
         
         $this->SetY(+41);
         $this->SetFont('helvetica', 'B', 12);
         $this->Cell(60, 0, 'Date' , 0, false, 'L', 0, '', 0, false, 'M', 'M');
         $this->SetFont('helvetica', 'N', 12);
         $this->Cell(60, 0, $iDate, 0, false, 'L', 0, '', 0, false, 'M', 'M'); 
         
         $this->SetY(+50);
         $this->SetFont('helvetica', 'B', 12);
         $this->Cell(189, 0,  ' ' , 'B', false, 'L', 0, '', 0, false, 'M', 'M');

         $this->SetY(+60);
         $this->SetFont('helvetica', 'B', 12);
         $this->Cell(189, 0,  ' ' , '0', false, 'L', 0, '', 0, false, 'M', 'M');
         
     }

     // Page footer
     public function Footer() {

          // Position at 15 mm from bottom
          // $this->SetY(-20);
          // Set font
          $this->SetFont('helvetica', 'I', 7);
          // Page number
          $this->Cell(0, 10, 'Powered by Kwelanga Online Solutions (Pty) Ltd', 0, false, 'C', 0, '', 0, false, 'T', 'M');
     }
}

// Extend the TCPDF class to create custom Header and Footer

// create new PDF document
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Kwelanga Solutions');
$pdf->SetTitle('Print Invoices');

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

$txt .= '<style type="text/css">
          td.phead {text-align:center; 
                    font-weight:bold; 
                    font-size:35px;  
                    border-style:solid solid solid solid;
                    border-width:1px;
                   }
          td.pdet  {"text-align:left; 
                     font-weight:normal; 
                     font-size:30px;
                     height: 30px;
                     vertical-align:middle;
                     border-style:solid solid solid solid;
                     border-left-style:solid;
                     border-width:1px;"
                    }          
          </style>';

$headerVar = '';
$txt = '';
$lineCount=10;
$firstRec = 'T';

//print_r($mfT);
//echo "<br>";
foreach($mfT as $iRow) {
	
	      if($headerVar != $iRow['dm_uid']) {
              if($headerVar <> '') {
              	    if($firstRec == 'T' || $lineCount >= 55) {
              	        $pdf->AddPage();
              	    	  $firstRec = 'N';
              	    	
              	    }
              	    
              	    $pdf->WriteHTML( $txt, true, $fill=0, $align='L', $ln=true, $stretch=0, $firstline=false, $firstblock=false, $maxh=0);	    
	            }
	            
	            $tripId =  $iRow['depot_uid'] . ' - ' . $iRow['tripsheet_number']; 
	            $driver =  trim($iRow['driver']);
	            $iDate  =  trim($iRow['tripsheet_date']);	
	                  	
              $txt = '';
	            //echo "<pre>";
              //print_r($iRow);
              //echo "<br>";
              //echo trim($iRow['Principal']);
	      	    //echo "<br>";

	      	
              if($iRow['invoice_number'] != '') { 
                    $docNo = ltrim($iRow['principal_uid']) . ' - ' . ltrim($iRow['invoice_number'],'0');
              } else {
                    $docNo = ltrim($iRow['principal_uid']) . ' - ' . ltrim($iRow['document_number'],'0');
              }	
              
              $principal     = $iRow['Principal'];
              $prinUid       = $iRow['principal_uid'];
              $store         = $iRow['store_name']; 
              
              $txt .=  '<table align="left" cellpadding="0" width="100%">';
              $txt .=  '<tr>';
              $txt .=  '<td colspan="5">&nbsp;</td>';
              $txt .=  '</tr>';
              $lineCount++;
              $txt .=  '<tr>';
              $txt .=  '<td colspan="5">&nbsp;</td>';
              $txt .=  '</tr>';
              $txt .=  '<tr>';
              $lineCount++;
              $txt .=  '<td style="width: 18%; 
                                              text-align:left; 
                                              font-weight:bold; 
                                              font-size:40px; 
                                              text-align:left;">' . trim($principal) . '</td>';      	
              $txt .=  '<td style="width: 18%; 
                                              text-align:left; 
                                              font-weight:bold; 
                                              font-size:40px; 
                                              text-align:left; ">' . trim($docNo) . '</td>';    	      	

              $txt .=  '<td style="width: 34%; 
                                              text-align:left; 
                                              font-weight:bold; 
                                              font-size:40px; 
                                              text-align:left; ">' . trim($store) . '</td>';  
              $txt .=  '<td style="width: 7%; 
                                              text-align:left; 
                                              font-weight:bold; 
                                              font-size:40px; 
                                              text-align:left; ">&nbsp;&nbsp;</td>'; 
              $txt .=  '<td rowspan="2" style="width: 23%; 
                                              text-align:left;
                                              font-weight:bold; 
                                              font-size:45px; 
                                              text-align:left; "><img alt=' . trim($docNo) .' src="https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/barcode/barcode.php?text=' . trim($docNo) .'&size=50&print=true" /></td>';    	      	
              $txt .=  '</tr>';
              $lineCount++;
              $txt .=  '<tr>';
              $txt .=  '<td colspan="5">&nbsp;</td>';
              $txt .=  '</tr>';
              $lineCount++;
        }
        $headerVar = $iRow['dm_uid'];
        
        $productCode = trim($iRow['product_code']);
        $product     = trim($iRow['product_description']);
        $qty         = trim($iRow['ordered_qty']);
        

        $txt .=  '<tr>';    
        $txt .=  '<td style="align:left; 
                            font-weight:normal; 
                            font-size:40px; 
                            text-align:left;">' . trim($productCode) . '</td>'; 
        $txt .=  '<td colspan="2" style="align:left; 
                            font-weight:normal; 
                            font-size:40px; 
                            text-align:left;">' . trim($product) . '</td>';      
        $txt .=  '<td style="align:left; 
                            font-weight:normal; 
                            font-size:40px; 
                            text-align:left;">' . trim($qty) . '</td>';      
        $txt .=  '</tr>';
        $lineCount++; 
        $txt .=  '<tr>';
        $txt .=  '<td colspan="5">&nbsp;</td>';
        $txt .=  '</tr>';
        $lineCount++;
        
        
}
// ---------------------------------------------------------
// $pdf->AddPage();
                   
// print a block of text using Write()
$txt .=  '<tr>';
$txt .=  '<td colspan="5">&nbsp;</td>';
$txt .=  '</tr>';
$txt .=  '<tr>';
$txt .=  '<td colspan="5">&nbsp;</td>';
$txt .=  '</tr>';
$txt .=  '<tr>';
$txt .=  '<td colspan="5">[*** EOR ***]</td>';
$txt .=  '</tr>';
 if($firstRec == 'T' || $lineCount >= 55) {
       $pdf->AddPage();
        $firstRec = 'N';
}

$pdf->WriteHTML( $txt, true, $fill=0, $align='L', $ln=true, $stretch=0, $firstline=false, $firstblock=false, $maxh=0);	    

$pdf->lastPage();

ob_clean();

if($outputTyp == "F") {
	
     //Close and output PDF document
     return $pdf->Output($fullpath, 'F');	
	
} else {
    //Close and output PDF document
      $pdf->Output('kos_pdf_'.date('Ymd').'.pdf', 'I');	
	
}

// return 'TRUE';
//============================================================+
// END OF FILE
//============================================================+

?>