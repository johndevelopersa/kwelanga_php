
<?php
// https://kwelangaonlinesolutions.co.za/systems/kwelanga_system/kwelanga_php/functional/marquee/marquee.php

 include_once('ROOT.php'); 
 include_once($ROOT.'PHPINI.php');
 include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
 include_once($ROOT.$PHPFOLDER."DAO/BcScannerDAO.php");
 include_once($ROOT.$PHPFOLDER.'TO/ErrorTO.php');	 
 
//Create new database object
$dbConn = new dbConnect(); 
$dbConn->dbConnection();
$errorTO = new ErrorTO;

$BcScannerDAO = new BcScannerDAO($dbConn);
$scanResult = $BcScannerDAO->extractScannedLoadsheets();
      
 ?>  
 <html>  
      <head>  
           <meta name="viewport" content="initial-scale=1.0, user-scalable=no">  
           <meta charset="utf-8">  
           <title>Webslesson Tutorial</title>  
           <script src="jquery.js"></script>  
           <script src="js/bootstrap.js"></script>  
           <link href="css/bootstrap.css" rel="stylesheet" />
           
           <style type="text/css"> 
              .header { width: 100%;
                        height: 80px;
                        background-color: #E1E1E1;
                        box-sizing:border-box;
                        margin-bottom: 4px;
                        margin-top: 10px;
                        margin-right: 10;
                        margin-left: auto;
                        font-size: 26px;
                        font-weight: Bold;
                        padding-top:15px;
                     }
           </style>  
      </head>  
      <body>
      	   <table style="width: 90%; border-collapse: collapse;;">
      	   	<tr>
      	   		  <td style="width: 10%; font-size: 26px; font-weight: Bold; background-color: #d9d9d9;""></td>
      	   		  <td style="width: 40%; font-size: 32px; font-weight: Bold; background-color: #d9d9d9; text-align: center;">Kwelanga&nbsp;Online&nbsp;Solutions</td>
      	   		  <td style="width: 40%; font-size: 26px; font-weight: Bold; background-color: #d9d9d9;""><?php echo "<img src=".$ROOT.$PHPFOLDER."images/logos/Kwelanga-new-logo.png style='width:100px; height:80px; float:right'";?>  ></td>
      	   		  <td style="width: 10%; font-size: 26px; font-weight: Bold; background-color: #d9d9d9;""></td>
      	   	</tr>
      	   	<marquee width="80%" height="100%" behavior="scroll" direction="up" onmouseover="this.stop();" onmouseout="this.start();">  
                <?php  
                     if(count($scanResult) > 0)  
                     {  
                          foreach($scanResult as $row) {?>
                          	  <tr>
                                 <td></td>
                                 <td><?php echo $row['username'] ;?></td>
                                 <td></td>                                                          
                                 <td></td>                                 
                          	  </tr>
                          <?php 
                          }  
                     }  ?>        	   	
      	    </marquee> 
      	   </table>
 
      </body>  
 </html>  