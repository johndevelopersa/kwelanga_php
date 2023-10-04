<?php 

// "https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/m/r/w.php";

    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
		include_once($ROOT.$PHPFOLDER."elements/datePickerElement.php");
    
?>
<!DOCTYPE html>
<html>
  <head>
      <style type="text/css">	
  	       body.b1  {background:white }
  	
           td.t1    {color:black; 
                     font-weight:bold; 
                     font-size:35px;
                     font-family: "Footlight MT Light", "Century Gothic", Georgia, Serif;
                      border-bottom: 0.1px solid black;} 	
           td.t2    {color:black; 
                     font-weight:normal; 
                     font-size:25px;
                     font-family: "Footlight MT Light", "Century Gothic", Georgia, Serif;}

      </style>	

      <title>Kwelanga Online Solutions</title>

		  <script type="text/javascript" language="javascript" src="../../../../kwelanga_php/js/jquery.js"></script>
		  <script type="text/javascript" language="javascript" src="../../../../kwelanga_php/js/dops_global_functions.js"></script>
    
      <meta name="author" content="Alan Argall" />
      <meta name="description" content="" />
  
  </head>
<body class="b1" >
  <table width = 90%; align="center"; >
       <tr>
	       <td colspan="4"; style='font-size:20px; font-weight:bold;'>&nbsp;</td>
       </tr>	
       <tr>
          <td width = 5%;>&nbsp;</td>
          <td class="t1"  width = 55%;>Kwelanga Online Solutions</td>
          <td class="t1"  width = 35%;><img src="../../../../kwelanga_php/images/logos/Kwelanga Solutions Logo smaller.jpg" style="width:180px; height:100px; float:right;" ></td>
          <td width = 5%;>&nbsp;</td>
       </tr>	
       <tr>
	       <td colspan="4"; style='font-size:20px; font-weight:bold;'>&nbsp;</td>
       </tr>	
       <tr>
          <td class="t2"  width = 5%;>&nbsp;</td>
          <td class="t2"  width = 55%;>Enter Data Collection Parameters</td>
          <td class="t2"  width = 35%;>&nbsp;</td>
          <td class="t2"  width = 5%;>&nbsp;</td>
       </tr>	
       <tr>
	       <td colspan="4" style='font-size:20px; font-weight:bold;'>&nbsp;</td>
       </tr>	
        <tr >
        	 <td class="t2"  width = 5%;>&nbsp</td>
           <td class="t2"  colspan="2"; <span style="text-align:left";>Start Date :&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
           	                            <span style="text-align:left"><?php DatePickerElement::getDatePickerLibs(); 
           	                            	                                  DatePickerElement::getDatePicker("FROMDATE",$postFROMDATE); ?></span> </td>
            <td class="t2"  width = 5%;>&nbsp;</td>
          </tr>
  </table>     
</body>
</html>