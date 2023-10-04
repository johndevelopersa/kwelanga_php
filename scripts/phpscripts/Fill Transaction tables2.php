<?php
// http://www.kwelangasolutions.co.za/systems/kwelanga_system/kwelanga_php/scripts/phpScripts/Fill Transaction tables2.php
ini_set('max_execution_time', 3600); //300 seconds = 5 minutes

    include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
    include_once ($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
?>

<!DOCTYPE html>
<HTML>
	<HEAD>
		<TITLE>Extract Data</TITLE>
	</HEAD>

<?php
      
      if (isset($_POST["tabletype"])) $tabletype=$_POST["tabletype"]; else $tabletype="";
      if (isset($_POST["ddb"])) $ddb=$_POST["ddb"]; else $ddb="test_kwelanga1";
      if (isset($_POST["mpty"])) $mpty=$_POST["mpty"]; else $mpty="N";
      if (isset($_POST["pl"])) $pl=$_POST["pl"]; else $pl="";
      
      $alreadySubmitted = FALSE;
      
      //Create new database object
      $dbConn = new dbConnect(); 
      $dbConn->dbConnection();
      
      $filelistarr = array();

      if (isset($_POST['finish']) && $tabletype !== 'Select Table Type' && $alreadySubmitted == FALSE) {
          
          $alreadySubmitted = TRUE;
          $tranasction_tables=array();
          $file =  'C:/inetpub/wwwroot/systems/kwelanga_system/output/temp_data.sql' ;   
          if (file_exists($file)) {
                 echo ('Temp File ' . $file . ' Exists - Delete it before continuing');
                 return;
          }

          // Get tables to be extracted
          $sql = ("select de.table_name, 
                          de.extract_sql, 
                          de.query1,
                          de.query2,
                          de.extract_date, 
                          de.table_type, 
                          de.extract_date3,
                          de.sequence
                  from  data_extract de
                  where de.extract_sql is not null
                  and   de.table_type = '".$tabletype ."'
                  and   de.status = 'A' 
                  order by de.run_order, de.table_name ");
          $tranasction_tables= $dbConn->dbGetAll($sql);
     
          $sql = ("select p.uid
                   from  principal p
                   where p.`status` = 'A'
                   and   p.data_extract in (" .$pl . ")");
                   
          $prinarr= $dbConn->dbGetAll($sql);
          foreach($prinarr as $key => $pr) {
          	$plist[] = $pr[uid];
          }

          $recordcount = 0;
          file_put_contents($file , "-- Start of file --\n");
          $recordcount++;
          foreach($tranasction_tables as $trow) {
             $i=0;
             if(trim($trow['query2']) !='' && $i==0){ 
                  	
                     // Truncate temporary table
                     if(trim($trow['query1']) !=''){
                        $temp1  = $trow['query1'];
                        $dbConn->dbQuery($temp1);
                        if (!$dbConn->dbQueryResult) {
                           echo "Error in SQL:".mysql_error($dbConn->connection). $temp1;
                           return;
                        }
                           $dbConn->dbinsQuery("commit"); 
                     }

                	   $temp2  = str_replace('p_list',implode(",",$plist),$trow['query2']); 
                     $temp2  = str_replace('e_date',$trow['extract_date'],$temp2); 
                     $temp2  = str_replace('3_date',$trow['extract_date3'],$temp2); 
                     
//                   echo ("-- " . trim($temp2));

                    
                     file_put_contents($file, "-- " . trim($temp2) . "\n", FILE_APPEND);
                	   $recordcount++;
                     // One Principal 
                     if(!strpos(trim($temp2),implode(",",$plist))) {
                       $dbConn->dbQuery($temp2);
                       if (!$dbConn->dbQueryResult) {
                           echo "Error in SQL:".mysql_error($dbConn->connection). $temp2;
                           return;
                       }
                       $dbConn->dbinsQuery("commit");    
                     } else { 
                         // Principal List	
                          
            	           foreach($prinarr as $key => $pr) {
                               $dbConn->dbQuery($temp2);
                               if (!$dbConn->dbQueryResult) {
                                   echo "Error in SQL:".mysql_error($dbConn->connection). $temp2;
                                  return;
                               }
                               $dbConn->dbinsQuery("commit");  
                         } // End of Principal List	
                     } // E of Principal List selection
                  }  // end of query 2
       	
                  // Create SQL file
                  // Run Once for all tables
       	          if ($i==0) {
                     if (strtoupper($mpty) == "Y") {
                         file_put_contents($file, "truncate table " . $ddb . "." . $trow['table_name'] . ";\n", FILE_APPEND);  
                         $recordcount++;
                     }               
                     // ***********************************************************************************
         	           $dbConn->dbQuery("select column_name, is_nullable, data_type
                                       from information_schema.columns
                                       where table_schema = '".kwelanga_live."'
                                       and table_name = '" . $trow[table_name]. "'");   
                                                                
                     if (!$dbConn->dbQueryResult) {
                           echo "Could not get table schema: ".mysql_error($dbConn->connection);
                           return;
                     }
                     $schema=array();
                     while($row = mysqli_fetch_array($dbConn->dbQueryResult,MYSQL_ASSOC)){
                         $schema[$row["column_name"]]["is_nullable"]=$row["is_nullable"];
                         $schema[$row["column_name"]]["data_type"]=$row["data_type"];
                     }
                     $temp  = $trow[extract_sql];
                                          
                     file_put_contents($file, "-- " . trim($temp) . "\n", FILE_APPEND);
                  }
                      $dbConn->dbQuery($temp);
                      if (!$dbConn->dbQueryResult) {
                           echo "Error in SQL:".mysql_error($dbConn->connection). $temp;
                          return;
                      }
                      // *************************************************************************************            
                      while($drow = mysqli_fetch_array($dbConn->dbQueryResult,MYSQL_ASSOC)){
                      	 $line="";
                         if ($recordcount==250000){
                         	   file_put_contents($file,";\n",FILE_APPEND);
                             CreateFile($file, "LR", $tabletype, $filelistarr);
                             $recordcount=0;
                             $i=0;
                         }
                      	 if ($i==0) {
                             file_put_contents($file, "insert into " . $ddb . "." . $trow['table_name'] . " values ", FILE_APPEND);                 	
                             $recordcount++;
                         } elseif ($i == 500) {
                             file_put_contents($file, ";\n insert into " . $ddb . "." . $trow['table_name'] . " values ", FILE_APPEND);                 	  	      	
                             $i=1;
                             $recordcount++;
                         }
                         foreach ($drow as $key=>$field) {
                           if ($field=="") {
                                if ($schema[$key]["is_nullable"]=="YES") {
                                    $line.=($line=="")?"NULL":",NULL";
                                } else {
                                    $line.=($line=="")?"":",''";
                                }
                           } else {
                                $line.=($line=="")?"'".addSlashes($field)."'":",'".addSlashes($field)."'";
                           }
                         }

                         if ($i==0 || $i==1) {
                              file_put_contents($file,"(" . trim($line) .")\n", FILE_APPEND);
                              $i++;                 	
                         } elseif ($i > 1 && $i <= 500) {
                              file_put_contents($file, ",(" . trim($line) .")\n", FILE_APPEND); 
                         }
                         $i++;
                         $recordcount++;
                                          // *************************************************************************************            
                      }   // End of Updates
          file_put_contents($file,";\n",FILE_APPEND);
          // ***********************************************************************************
          }  // End of table loop

         file_put_contents($file,"\n",FILE_APPEND);
         CreateFile($file, ' ', $tabletype, $filelistarr);

           ?>
          <script language="javascript">
          
          var r = confirm("Extract Files Created")
          
          </script>
          
          <?php       
          $tabletype = 'Select Table Type';
      } 
// ***********************************************************************************
   
      $table_types=array();

      $fsql = ("select distinct(de.table_type) as 'tabletype'
                from  data_extract de
                where de.extract_sql is not null
                and   de.table_type <> 'N'
                and   de.status = 'A' 
                order by de.table_type");
      $table_types = $dbConn->dbGetAll($fsql);

?>
		<BODY>
		<center>
			<FORM name='Extract' method=post action=''>
        <TABLE style="border: none";>
        <tr>
        	<td Colspan="3">&nbsp&nbsp&nbsp&nbsp&nbsp</td>
       	</tr>

      	<table>
           <tr>
           <th colspan="2">Extract Data</th>
           </tr>
           <tr>
              <td colspan="2">&nbsp</td>	
        	 </tr>
           <tr>
           	  <td style="text-align:left";>Select Table Type</td>
              <td>
              	 <select name="tabletype" id="tabletype">
              			 <option value="Select Table Type">Select Table Type</option>
              			<?php foreach($table_types as $row) { ?>
              					<option value="<?php echo $row['tabletype']; ?>"><?php echo $row['tabletype']; ?></option>
              			<?php } ?>
              		</select>
              </td>
           </tr>
            <tr>
              <td colspan="2">&nbsp</td>	
        	 </tr> 
           <tr>
              <td style="text-align:left";>Select Database</td>
              <td style="text-align:left"><input type="text" value="<?php echo $ddb; ?>" name="ddb"><br></td>           
           </tr>
           <tr>
              <td colspan="2">&nbsp</td>	
        	 </tr>
            <tr>
              <td style="text-align:left";>Truncate Table</td>
              <td style="text-align:left text-transform:uppercase";><input type="text" value="<?php echo $mpty; ?>" name="mpty"><br></td>           
           </tr>          
           <tr>
              <td colspan="2">&nbsp</td>	
        	 </tr>           
            <tr>
              <td style="text-align:left";>Principal List </td>
              <td style="text-align:left"; ><input type="text" value="<?php echo $pl; ?>" name="pl"><br></td>           
           </tr>          
           <tr>
              <td colspan="2">&nbsp</td>	
        	 </tr>           
        </table>
        <br>
           <tr>
              <td colspan="2">&nbsp</td>	
        	 </tr>
       <tr>
        	<td colspan="2"></td>
        	<td><INPUT TYPE="submit" class="submit" name="finish" value= "Extract Data"></td>
        </tr>
				</TABLE>
		</form>
    </center>    
 </HTML>
 
 <?php
 
 function CreateFile($file, $lr, $tt, $filelistarr) {
 	
 	    $dbConn = new dbConnect(); 
      $dbConn->dbConnection(); 	
 	
      $sql = ("select a.sequence_value
              from  sequence_control a
              where a.sequence_key = 'DATAEXTRACT'");
 
              $file_sequence = $dbConn->dbGetAll($sql);
              
              if (!$dbConn->dbQueryResult) {
                    echo "Error in SQL:".mysql_error($dbConn->connection) . $sql;
                    return;
               }
               $nextseq = $file_sequence[0][sequence_value]+1;
               
               $ofile =  'C:/inetpub/wwwroot/systems/kwelanga_system/output/'. $tt . '-' . str_pad($nextseq,5,"0",STR_PAD_LEFT) . '_data.sql' ;   
 
      $sql = ("Update sequence_control a set a.sequence_value = ". $nextseq .
              " where a.sequence_key = 'DATAEXTRACT'");
 
              $file_sequence = $dbConn->dbQuery($sql);
              
              if (!$dbConn->dbQueryResult) {
                    echo "Error in SQL:".mysql_error($dbConn->connection) . $sql;
                    return;
               }
               
               $dbConn->dbinsQuery("commit"); 
               
               file_put_contents($file , "-- End of File --\n", FILE_APPEND);
               array_push($filelistarr, $ofile);
               rename($file,$ofile);
               $recordcount = 0;
               if ($lr == "LR") {
               	  file_put_contents($file , "-- Start of next file --\n");  
               }
                             
 }
