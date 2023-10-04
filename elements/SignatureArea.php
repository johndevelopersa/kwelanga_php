<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');

class SignatureArea {

  function __construct() {
  }

  static function getWatermark($mainText) {
    // Create the image
    $im = imagecreatetruecolor(600, 300);

    // Create some colors
    $white = imagecolorallocate($im, 255, 255, 255);
    $grey = imagecolorallocate($im, 228, 228, 228);
    $black = imagecolorallocate($im, 228, 245, 250);
    imagefilledrectangle($im, 0, 0, 599, 299, $white);

    // The text to draw
    $text = $mainText;
    // Replace path by your own font path
    $font = 'c:\www\live\rtsystem\fonts\arial.ttf';

    // Add some shadow to the text
    imagettftext($im, 80, -15, 1, 80, $grey, $font, $text);

    // Add the text
    imagettftext($im, 80, -15, 10, 90, $black, $font, $text);
    imagettftext($im, 20, 0, 10, 190, $black, $font, date("Y-m-d H:i:s"));
    imagettftext($im, 30, 0, 50, 220, $black, $font, $_SESSION["full_name"]);

    // Using imagepng() results in clearer text compared with imagejpeg()

    ob_start();
    imagepng($im);
    $watermark = ob_get_contents();
    ob_end_clean();
    // echo '<p><img src="data:image/png;base64,'.base64_encode($watermark).'" /></p>';
    // <img src="http://yoursite.com/canvas.php" />
    // imagepng($im);
    imagedestroy($im);

    return base64_encode($watermark);
  }

  static function importJSLink() {
    global $ROOT, $PHPFOLDER;
    echo "<script type='text/javascript' language='javascript' src='".$ROOT.$PHPFOLDER."js/SignatureArea.js'></script>";
  }

  static function outputCanvas($dmUId, $watermarkMainText, $transactionDAO) {
    global $ROOT, $PHPFOLDER;
    /* A canvas actually has two sizes: the size of the element itself and the size of the element’s drawing surface.
    Setting the element's width and height attributes sets both of these sizes; CSS attributes affect only the
    element’s size and not the drawing surface. */

    $mfT = $transactionDAO->getDocumentImage($dmUId, IMAGE_TYPE_SIGNATURE);

    if (count($mfT)==0) {
      $watermark = SignatureArea::getWatermark($watermarkMainText);
    } else {
      $watermark = $mfT[0]["image_data"];
    }

    echo "<style>
          #canvas {
            background: #ffffff;
            border: thin inset #aaaaaa;
            width: 600px;
            height: 300px;
            margin:20px;
          }
          button.submit {
          	margin-left: 10px;
          	height:22px;
          	font-size: 12px;
          	font-weight:bold;
          	color: #FFFFFF;
          	background-color: #012D58;
          	background-image: url({$ROOT}{$PHPFOLDER}images/bg4.gif);
          	border: 0px;
          	font-family: Arial, Helvetica, sans-serif;
          }
          </style>
          <canvas id='canvas' width='600' height='300'>Canvas not supported</canvas>
          <br>
          <button onclick='signatureArea.clearSignatureArea();' class='submit'>Clear Signature</button>
          <button id='btnSaveSignature' class='submit'>Save Signature</button>
          <img id='imgWatermark' src='data:image/png;base64,{$watermark}' style='display:none;'>

          <script type='text/javascript' defer>
          $(document).ready(function() {
            signatureArea.initialise('DMUID={$dmUId}','{$ROOT}{$PHPFOLDER}');
            signatureArea.applyWatermark();
          });
          </script>";
  }


}
?>
