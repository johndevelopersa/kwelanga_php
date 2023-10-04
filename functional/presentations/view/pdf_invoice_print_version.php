<?php

/*
 *
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

$docIdList    = ((isset($_GET["DOCIDLIST"]))?$_GET["DOCIDLIST"]:'');

//Create new database object
$dbConn = new dbConnect(); 
$dbConn->dbConnection();

$invList = new PrintInvoicesDAO($dbConn);
$mfT = $invList->getDocumentWithDetailList($docIdList);

// print_r($mfT);

$headerVar = '';
class MYPDF extends TCPDF {
	
     //Page header
     public function Header() {
     	
     	  global $principal, $prin_add1, $prin_add2, $prin_add3, $prin_vat, $office_tel, $principalUid, $iCopies;
     	  
     	   if($iCopies > 0 ){
     	   	   $docType = 'Copy Tax Invoice';
     	   } else {
     	       $docType = 'Tax Invoice'; 	
     	   }
         // Title
         $this->SetY(+10);
         $this->SetFont('helvetica', 'B', 16);
         $this->Cell(0, 0, $docType . ((isset($_GET['VARIANCE'])&&$_GET['VARIANCE']==1)?' : Variance' : ''), 0, false, 'C', 0, '', 0, false, 'M', 'M');

         //Principal Details
         $this->SetY(+20);
         $this->SetFont('helvetica', 'B', 12);

         $this->Cell(0, 0,  $principal, 0, false, 'L', 0, '', 0, false, 'M', 'M');
         $this->Image('https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/images/logos/' . $principalUid .'.jpg', 145,10,20,16);   //  X Y W H

         //Principal Details
         $this->SetY(+25);
         $this->SetFont('helvetica', 'B', 12);
         $this->Cell(0, 0,   $prin_add1, 0, false, 'L', 0, '', 0, false, 'M', 'M');
         //Principal Details
         $this->SetY(+30);
         $this->SetFont('helvetica', 'B', 12);
         $this->Cell(0, 0,   $prin_add2, 0, false, 'L', 0, '', 0, false, 'M', 'M');
         //Principal Details
         $this->SetY(+35);
         $this->SetFont('helvetica', 'B', 12);
         $this->Cell(0, 0,   $prin_add3, 0, false, 'L', 0, '', 0, false, 'M', 'M');
    
         //Principal Details
         $this->SetY(+42);
         $this->SetFont('helvetica', 'B', 12);
         $this->Cell(0, 0, 'VAT No  ' .  $prin_vat, 0, false, 'L', 0, '', 0, false, 'M', 'M');

         $this->SetFont('helvetica', 'B', 12);
         $this->Cell(0, 0, 'Tel No  ' . substr( $office_tel,0,3) . ' ' . substr( $office_tel,3,3) . ' ' . substr( $office_tel,6,4) , 0, false, 'R', 0, '', 0, false, 'M', 'M');
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

foreach($mfT as $iRow) {
	
	      if($headerVar != $iRow['dm_uid']) {
	      	
	            if($headerVar <> '') {
	      	         $pdf->AddPage();
                   $pdf->WriteHTML( $txt, true, $fill=0, $align='L', $ln=true, $stretch=0, $firstline=false, $firstblock=false, $maxh=0);
                   $txt ='';

	            }
	            
              $principal     = $iRow['principal_name'];
              $prin_add1     = $iRow['prin_ph_add1'];
              $prin_add2     = $iRow['prin_ph_add2'];
              $prin_add3     = $iRow['prin_ph_add3'];
              $prin_vat      = $iRow['prin_vat'];
              $office_tel    = $iRow['office_tel'];
              $principalUid  = $iRow['principal_uid'];
              $iCopies       = $iRow['invoice_printed'];
     
              // Create Invoice Details
     
              if($iRow['invoice_number'] != '') { 
                    $docNo = ltrim($iRow['invoice_number'],'0');
              } else {
                    $docNo = ltrim($iRow['document_number'],'0');
              }
             
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
                                                               <span style="font-weight:normal;">' . $iRow['invoice_date']  . '</span>
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
                                              text-align:left; ">' . trim($iRow['store_name'])  . '
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
                                              text-align:left; ">' . trim($iRow['deliver_add1'])  . '
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
                                              text-align:left; ">' . trim($iRow['deliver_add2'])  . '
                                   </td>';
                         $txt .=  '<td style="width: 50%; 
                                              text-align:Right;
                                              font-size:43px;"><span style="font-weight:bold">Order Number&nbsp;&nbsp;</span>
                                                              <span style="font-weight:normal;">' . $iRow['customer_order_number']  . '</span>
                                   </td>';
                         $txt .=  '</tr>';
     
                         $txt .=  '<tr>';
                         $txt .=  '<td style="width: 50%; 
                                              text-align:left; 
                                              font-weight:normal; 
                                              font-size:43px; 
                                              text-align:left; ">' . trim($iRow['deliver_add3'])  . '
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
                                              text-align:left; "><span style="font-weight:bold">VAT No&nbsp;&nbsp;</span>' . trim($iRow['vat_number'])  . '
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
                         $txt .=  '<td class="Phead"; style="width:14%;">&nbsp;&nbsp;Product<br>Code</td>';
                         $txt .=  '<td class="Phead"; style="width:35%;">&nbsp;&nbsp;Product</td>';
                         $txt .=  '<td class="Phead"; style="width:6%;" >&nbsp;&nbsp;Qty</td>';
                         $txt .=  '<td class="Phead"; style="width:7%;" >&nbsp;&nbsp;Price</td>';
                         $txt .=  '<td class="Phead"; style="width:10%;">&nbsp;&nbsp;Excl Price</td>';
                         $txt .=  '<td class="Phead"; style="width:8%;" >&nbsp;&nbsp;Vat Rate</td>';
                         $txt .=  '<td class="Phead"; style="width:10%;" >&nbsp;&nbsp;Vat</td>';
                         $txt .=  '<td class="Phead"; style="width:10%;" >&nbsp;&nbsp;Total</td>';
                         $txt .=  '</tr>';
       
                         $casesTotal = $extTotal = $vatTotal = $totTotal  = 0;

        }
        $headerVar = $iRow['dm_uid'];
        
        // Update Invoices to printed status
                
        $invoicesPrinted = new PrintInvoicesDAO($dbConn);
        $tsInvoices = $invoicesPrinted->updateInvoicesPrinted($iRow['dm_uid']); 

        $txt .=  '<tr>';	
        $txt .=  '<td class="pdet"; style = "text-align:left;">&nbsp;&nbsp;' . trim($iRow["product_code"]) . '</td>';
        
        $txt .=  '<td class="pdet"; style = "text-align:left;">&nbsp;&nbsp;' . trim($iRow["product_description"]) . '</td>';	
        $txt .=  '<td class="pdet"; style = "text-align:center;">&nbsp;&nbsp;' . $iRow["document_qty"] . '</td>';		
        $txt .=  '<td class="pdet"; style = "text-align:right;">' . number_format($iRow["net_price"],2, "."," ") . '&nbsp;&nbsp;</td>';		
        $txt .=  '<td class="pdet"; style = "text-align:right;">' . number_format($iRow["extended_price"],2, "."," ") . '&nbsp;&nbsp;</td>';		
        $txt .=  '<td class="pdet"; style = "text-align:right;">' . number_format($iRow["vat_rate"],2, "."," ") . '&nbsp;&nbsp;</td>';		
        $txt .=  '<td class="pdet"; style = "text-align:right;">' . number_format($iRow["vat_amount"],2, "."," ") . '&nbsp;&nbsp;</td>';				     
        $txt .=  '<td class="pdet"; style = "text-align:right;">' . number_format($iRow["total"],2, "."," ") . '&nbsp;&nbsp;</td>';      
        $txt .=  '</tr>';
			
        $casesTotal = $casesTotal + $iRow["document_qty"];
        $extTotal   = $extTotal   + $iRow["extended_price"];
        $vatTotal   = $vatTotal   + $iRow["vat_amount"];
        $totTotal   = $totTotal   + $iRow["total"];


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
        if (trim($iRow["banking_details"]) <> NULL && !in_array($iRow["document_type_uid"],array('4','2')) && ($iRow["bank_details_to_print"] == 2) )  {
                                         $txt .=  '<td style="width:50%"; "text-align:left"; nowrap ><span style = "font-weight:bold; font-size:40px;">Banking Details<br></span>' . (str_replace(chr(43),"<br>", $iRow["banking_details"])) . '</td>';
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
        $txt .=  '<td><img alt=' . $iRow["principal_uid"]  . ' - ' .  ltrim(substr($iRow["document_number"],0,8),'0') .' src="https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/barcode/barcode.php?text=' . $iRow["principal_uid"] . ' - ' . ltrim(substr($iRow["document_number"],0,8),'0') .'&print=true" /></td>';
        $txt .=  '</tr>';
        
        $txt .=  '</table>';


}
// ---------------------------------------------------------
$pdf->AddPage();
                   
// print a block of text using Write()
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