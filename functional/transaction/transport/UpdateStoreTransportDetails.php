<?php
    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    require_once($ROOT.$PHPFOLDER."functional/main/access_control.php");
    include_once($ROOT.$PHPFOLDER.'DAO/TransportCostDAO.php');	
    include_once($ROOT.$PHPFOLDER."properties/Constants.php");
		include_once($ROOT.$PHPFOLDER.'libs/GUICommonUtils.php');
?>
<!DOCTYPE html>
<HTML>
	<HEAD>

		<TITLE>Document Management</TITLE>

    <link href='<?php echo $DHTMLROOT.$PHPFOLDER ?>css/1_kwelanga.css' rel='stylesheet' type='text/css'>
    <script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/jquery.js"></script>
    <script type="text/javascript" language="javascript" src="<?php echo $DHTMLROOT.$PHPFOLDER ?>js/dops_global_functions.js"></script>

    <style>
    	td.head1 {font-weight:normal;
    		        font-size:2em;text-align:left; 
    		        font-family: Calibri, Verdana, Ariel, sans-serif; 
    		        padding: 0 150px 0 150px }
      
      td.det1  {border-style:solid none solid none; 
      	        border-color:DarkGray; 
      	        border-width:1px;
      	        border-collapse:collapse; 
      	        text-align: center; 
      	        font-weight: bold; 
      	        font-size: 15px;
      	        padding: 0 150px 0 150px  }

     td.det2  {border-style:solid solid solid none; 
      	        border-color:DarkGray; 
      	        border-width:1px;
      	        border-collapse:collapse; 
      	        text-align: left; 
      	        font-weight: normal; 
      	        font-size: 12px;  }
    	
    	</style>

		</HEAD>
<body>

<?php

    $class = 'odd';

    if (!isset($_SESSION)) session_start() ;
      $userUId     = $_SESSION['user_id'] ;
      $principalId = $_SESSION['principal_id'] ;
      $depotId     = $_SESSION['depot_id'] ;
      $systemId    = $_SESSION["system_id"];
      $systemName  = $_SESSION['system_name'];
      
      if (isset($_POST["INVOICE"])) $postINVOICE=test_input($_POST["INVOICE"]); else $postINVOICE = '';
      $postTransporter = (isset($_POST["Transporter"])) ? htmlspecialchars($_POST["Transporter"]) : '';  
      
      //Create new database object
     $dbConn = new dbConnect(); $dbConn->dbConnection();
     
     if (isset($_POST['cancel'])) {
        return;
     }
     if (isset($_POST['finish'])) {
          $dmltype = htmlspecialchars($_POST['PSTTYPE']);
                    
          if(htmlspecialchars($_POST["FromArea"]) == '' ) {
                $frmArea = htmlspecialchars($_POST['FIRSTFAREA']);
          } else {
                $frmArea = htmlspecialchars($_POST['FromArea']);  
          }

          if(htmlspecialchars($_POST["ToArea"]) == '' ) {
                $toArea = htmlspecialchars($_POST['FSTTAREA']);
          } else {
                $toArea = htmlspecialchars($_POST['ToArea']);  
          }
          $pstUid  = htmlspecialchars($_POST['PSTUID']);
               	
          if($frmArea <> 'Select' ) { 
            	 if($toArea <> 'Select' ) {
              	 	if(trim($dmltype) === 'INSERT') {
              	 	     $TransportCostDAO = new TransportCostDAO($dbConn);
                       $result = $TransportCostDAO->insertStoreArea(htmlspecialchars($_POST['TRNSPTR']), htmlspecialchars($_POST['PSTSTI']), $frmArea, $toArea ) ;
                       if($result <> 'S'){ ?>
    	                      <script type='text/javascript'>parent.showMsgBoxError("Store Area Load Failed") </script> 
                            <?php   unset($_POST['firstform']); 
                                    return; 
                       } else {?>
    	                      <script type='text/javascript'>parent.showMsgBoxInfo("Store Area loaded Successfully") </script> 
                            <?php     unset($postINVOICE);
                                      unset($_POST['select']);
                                      unset($_POST['finish']);
                                      unset($_POST['firstform'] );
                  	   }
                  } else {
                  	                  	
                  	   $TransportCostDAO = new TransportCostDAO($dbConn);
                       $result = $TransportCostDAO->updateStoreArea($pstUid,$frmArea,$toArea) ;
                       if($result <> 'S'){ ?>
    	                      <script type='text/javascript'>parent.showMsgBoxError("Store Area Update Failed") </script> 
                            <?php   unset($_POST['firstform']); 
                                    return; 
                       } else {?>
    	                      <script type='text/javascript'>parent.showMsgBoxInfo("Store Area Updated Successfully") </script> 
                            <?php     unset($postINVOICE);
                                      unset($_POST['select']);
                                      unset($_POST['finish']);
                                      unset($_POST['firstform'] ); 
                       }
                  }	   
               } else { ?>
                     <script type='text/javascript'>parent.showMsgBoxError('To Area Not Selected')</script> 
       	             <?php
       	             unset($postINVOICE);
                     unset($_POST['select']);
                     unset($_POST['finish']);
                     unset($_POST['firstform'] );
               }     
          } else {	?>
                   <script type='text/javascript'>parent.showMsgBoxError('From Area Not Selected')</script> 
       	      <?php
       	         unset($postINVOICE);
                 unset($_POST['select']);
                 unset($_POST['finish']);
                 unset($_POST['firstform'] );  
       	  }
     }
     if (isset($_POST['firstform']) && $postINVOICE !== '') {
     	     	
     	    $TransportCostDAO = new TransportCostDAO($dbConn);
        	$mfDDU = $TransportCostDAO->updateStoreAreas($principalId,$postINVOICE);

          $TransportCostDAO = new TransportCostDAO($dbConn);
        	$mfFTA = $TransportCostDAO->getAreaArray($postTransporter);

          $TransportCostDAO = new TransportCostDAO($dbConn);
        	$mfTTA = $TransportCostDAO->getAreaArray($postTransporter);
        	
        	if($mfDDU[0]['principal_store_uid'] == NULL) {
               $pstType = "INSERT   ";
        	} else {
               $pstType = 'UPDATE';
        	}
          if($mfDDU[0]['frmUid'] == 0 || $mfDDU[0]['frmUid'] == NULL) {
                $frmArea   = "Select From Area";
                $frmAreaId = "Select From Area";
          } else {
                $frmArea = trim($mfDDU[0]['From Area']);
                $frmAreaId = $mfDDU[0]['frmUid'];
                $pstUid    = $mfDDU[0]['pstUid'];
          }        	    	

          if($mfDDU[0]['toUid'] == 0 || $mfDDU[0]['toUid'] == NULL) {
                $toArea   = "Select To Area";
                $toAreaId = "Select From Area";
          } else {
                $toArea   = trim($mfDDU[0]['To Area']);
                $toAreaId = $mfDDU[0]['toUid'];
                $pstUid   = $mfDDU[0]['pstUid'];
          }  

        	
          if (sizeof($mfDDU)!==0) { ?>
     	       <center>
               <form name='displayinv' method=post target=''>
                  <table width:"80%"; style="border-none";>
                  	  <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                          <td  colspan="5"; style="text-align:center" >&nbsp;</td>            	
                      </tr> 
                  	  <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                          <td class=head1 colspan="5"; style="text-align:center" >Customer Details</td>  
                  	  </tr>
                  	  <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                          <td  colspan="5"; style="text-align:center" >&nbsp;</td>            	
                  	  </tr> 
                  	  <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                          <td colspan="1";>Customer</td>
                          <td colspan="2";><?php echo $mfDDU[0]['deliver_name'];?></td>
                          <td colspan="2"; style="text-align:center" ><input type="hidden" name="PSTUID" value=<?php echo $mfDDU[0]['pstUid'];?>></td>            	
                  	  </tr>	
                  	  <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                          <td>&nbsp;</td>
                          <td colspan="2";><?php echo $mfDDU[0]['deliver_add1'];?></td>
                          <td colspan="2"; style="text-align:center" >&nbsp;</td>            	
                  	  </tr>
                  	  <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                          <td>&nbsp;</td>
                          <td colspan="2";><?php echo $mfDDU[0]['deliver_add2'];?></td>
                          <td colspan="2"; style="text-align:center" >&nbsp;</td>            	
                  	  </tr>
                  	  <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                          <td>&nbsp;</td>
                          <td colspan="2";><?php echo $mfDDU[0]['deliver_add3'];?></td>
                          <td colspan="2"; style="text-align:center" >&nbsp;</td>            	
                  	  </tr>
                      <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                      <td  colspan="5"; style="text-align:center" >&nbsp;</td>            	
                    </tr>   
                    <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                    	 <td  colspan="1";>From Area</td>
                       <td  colspan="4";> 
                       	   <select name="FromArea" id="FromArea">
                                <option value=<?php echo $frmAreaId; ?>><?php echo $frmArea; ?></option>
                                      <?php foreach($mfFTA as $row) { ?>
                                           <option value="<?php echo trim($row['taUID']); ?>"><?php echo $row['area_name']; ?></option>
                                      <?php } ?>
               	  	       </select>
                       </td>
                    </tr> 
                    <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                          <td  colspan="5"; style="text-align:center" >&nbsp;</td>            	
                    </tr> 
                    <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                    	 <td  colspan="1";>To Area</td>
                       <td  colspan="4";> 
                       	   <select name="ToArea" id="ToArea">
                                <option value=<?php echo $toAreaId; ?>><?php echo $toArea; ?></option>
                                      <?php foreach($mfTTA as $row) { ?>
                                           <option value="<?php echo trim($row['taTUid']); ?>"><?php echo $row['area_name']; ?></option>
                                      <?php } ?>
               	  	       </select>
                       </td>
                    </tr>
                    <tr class="<?php echo GUICommonUtils::styleEO($class);?>">  
                      <td  colspan="1"; style="text-align:center" ><input type="hidden" id="FIRSTFAREA" name="FIRSTFAREA" value=<?php echo $frmAreaId;?>></td>
                      <td  colspan="1"; style="text-align:center" ><input type="hidden" id="TRNSPTR"    name="TRNSPTR"    value=<?php echo $postTransporter; ?>></td>  
                      <td  colspan="1"; style="text-align:center" ><input type="hidden" id="PSTSTI"     name="PSTSTI"     value=<?php echo $mfDDU[0]['psmUid']; ?>></td>  
                      <td  colspan="1"; style="text-align:center" ><input type="hidden" id="FSTTAREA"   name="FSTTAREA"   value=<?php echo $toAreaId; ?>></td>  
                      <td  colspan="1"; style="text-align:center" ><input type="hidden" id="PSTTYPE"    name="PSTTYPE"    value=<?php echo $pstType; ?></td>   
                    </tr>   
                    <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                    	 <td  colspan="5" >&nbsp;</td>
                     </tr>
                     <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                          <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="finish" value= "Update Store Areas">
                          	                                          <INPUT TYPE="submit" class="submit" name="cancel" value= "Cancel"></td>
                     </tr>
                    <tr class="<?php echo GUICommonUtils::styleEO($class);?>">  
                      <td  colspan="5"; style="text-align:center" >&nbsp;</td>  
                    </tr>   
                  </table>
               </form>   
	           </center>    	
          <?php
            return;
          } else {
          ?>	
            <center>
 				     <table>
 					     <tr>
                  <td colspan="5" style="border:1px solid black; border-collapse: collapse; text-align: center; font-weight: bold;">Invoice number not found !!</td>            	
               </tr>	
    		      </table>
    		     </center>
    		   <?php 
    		     return;
          }	  
            
     }   
    if(!isset($_POST['firstform'])) {
         $TransportCostDAO = new TransportCostDAO($dbConn);
         $mfPR = $TransportCostDAO->getActiveTransporters();  ?>

         <center>	
              <FORM name='Select Invoice' method=post action=''>
                  <table width:"720"; style="border:none">        	
                      <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                          <td colspan="2";>&nbsp</td>
                      </tr>	
                      <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                          <td class=head1 colspan="2"; style="text-align:center;" >Enter Store Transporter Details</td>
                      </tr>
                      <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                           <td colspan="2";>&nbsp</td>
                      </tr>
                      <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                           <td style="text-align:left";>Select Transport Provider : </td>
                           <td colspan="4"; style="text-align:left;">
                              <select name="Transporter" id="Transporter">
                                <option value="Transport Provider"><?php echo 'Select Service Provider' ?></option>
                                      <?php foreach($mfPR as $row) { ?>
                                           <option value="<?php echo trim($row['uid']); ?>"><?php echo $row['name'] ." - " . trim($row['address1']); ?></option>
                                      <?php } ?>
               	  	          </select>
               	           </td> 
                      </tr> 	
                      <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                           <td colspan="2";>&nbsp</td>
                      </tr>
                      <tr class="<?php echo GUICommonUtils::styleEO($class);?>">
                           <td style="text-align:left";>Enter Document Number</td>
                           <td style="text-align:left";><input type="text" name="INVOICE"></td>
                      </tr>
                      <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                           <td colspan="2";>&nbsp</td>
                      </tr>
                      <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                           <td colspan="5"; style="text-align:center;"><INPUT TYPE="submit" class="submit" name="firstform" value= "Get Store Details">
                                                                       <INPUT TYPE="submit" class="submit" name="canform"   value= "Cancel"></td>
                      </tr>          
                      <tr class="<?php echo GUICommonUtils::styleEO($class); ?>">
                          <td colspan="2";>&nbsp</td>
                      </tr>  
                  </table>
		          </form>
         </center> 
    <?php 
    } ?>


</body>       
</HTML>
 
<?php
 function test_input($data) {

  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  
  return $data;
 }
?> 