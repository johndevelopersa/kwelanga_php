<?php
// https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/functional/payments/test/liveform.php

$paymentAmount = 1000;

$invNo         = 100000;
$invamt        = 200;

$totalPayment  = 0;

$aa = array(0,1);

foreach($aa as $row) {

echo $row;
echo "<br>";

}




?>
<!doctype html>
<html>
     <head>
     <title>Update Balances</title>
     </head>
     <body>
        <center>
           <form  method="POST"  action="" id="cakeform">
              <table>
               	 <tr>
               	 	<td>Payment Balance  </td>
              	 	<td id='totalPrice'><?php echo $paymentAmount ?></td>
              	 </tr>
               	 <tr>
              	 	<td>&nbsp</td>
              	 </tr>
              	 <?php 
              	 
              	 foreach($aa as $row) {   
              	 ?>
              	 <tr>
              	 	  <td><?php echo $row; ?></td>
              	 	  <td><input name='PaidAmount[]' id='PaidAmount[]' type='text' value=<?php echo number_format($invamt ,2,'.',' '); ?> </td>
              	 	  <td><input type="checkbox" id= 'includecandles[]' name="includecandles" onclick=calculateTotal(<?php echo($row) ?>) </td>
                    <input type="hidden" name="balance" value=<?php echo $totalPayment; ?> >
              	 </tr>
              	 
              	<?php } ?>
              	 
               	 <tr>
              	 	<td>&nbsp</td>
              	 </tr>
        
              	 <tr>
              	 	<td><input type='submit' id='submit' value='Submit' " ></td>
              	 </tr>
            	 
              	 
              	               	
              </table>
           </form>
         </center>
     </body>
     
     <script type="text/javascript">
     	
     	       	
 	      function candlesPrice(row) {          
             //Get a reference to the form id="cakeform"
             var theForm = document.forms["cakeform"];
          
             //Get a reference to the checkbox id="includecandles"   
             
             var includeCandles = theForm.elements["includecandles"][row]; 
          
             //If they checked the box set candlePrice to 5
             if(includeCandles.checked==true) {
                linePayment = document.getElementsByName('PaidAmount[]')[row].value ; ;
             }
             //finally we return the candlePrice
             return linePayment;
        }
//   https://www.xul.fr/javascript/php.php        
        function calculateTotal(row) {
        	
        	    var lineIndex=row;

        	
        	    document.forms["cakeform"].balance.value = <?php echo($totalPayment) ?> - candlesPrice(lineIndex) ;
    
             //display the result
            var divobj = document.getElementById('totalPrice');
            divobj.style.display='block';
            divobj.innerHTML = "Total Price For the Cake $"+<?php echo($totalPayment) ?>;

        }       
        function hideTotal() {
           var divobj = document.getElementById('totalPrice');
           divobj.style.display='none';
        }
     </script>           


</html>






<?php

?>