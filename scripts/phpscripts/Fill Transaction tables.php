<?php
// http://www.kwelangasolutions.co.za/kwelanga_system/scripts/phpScripts/Fill Transaction tables.php

include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once ($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER.'properties/dbSettings.inc');

if (isset($_GET["user"])) $user=$_GET["user"]; else if (isset($_POST["user"])) $user=$_POST["user"]; else $user="";
if (isset($_POST["tbl"])) $tbl=$_POST["tbl"]; else $tbl="";
if (isset($_POST["ddb"])) $ddb=$_POST["ddb"]; else $ddb="";
if (isset($_POST["mpty"])) $mpty=$_POST["mpty"]; else $mpty="N";

ob_start(); //Turn on output buffering
echo '<br>';
echo '<br>';

echo "<form name='pform' action='".$_SERVER["PHP_SELF"]."' method='post'>";
echo "<input type='hidden' name='user' value='{$user}' />
	  Table Type to Extract :<br><input name='tbl' value='$tbl' /><br><br>
	  Destination Database  :<br><input name='ddb' value='$ddb' /><br><br>
	  Truncate Tables       :<br><input name='mpty' value='$mpty' /><br><br>
	  <input type='button' value='submit' onclick='document.pform.submit();' />
	 ";
echo "</form>";

$dbConn = new dbConnect();
$dbConn->dbConnection();

$table_types=array();

$fsql = ("select distinct(de.table_type)
         from  data_extract de
         where de.extract_sql is not null
         and   de.table_type <> 'N'
         and   de.status = 'A' 
         order by de.table_type");
$table_types = $dbConn->dbGetAll($fsql);

echo '<br>';
echo '<br>';
echo ' Available Table Types <br>';
foreach($table_types as $xtrow) { 
	 echo '  -  ';
   echo ($xtrow[table_type]);
}
echo '  -  ';
echo '<br>';
echo '<br>';

if (isset($_POST['select'])) {

$tranasction_tables=array();

$sql = ("select de.table_name, 
                de.extract_sql, 
                de.query1,
                de.query2,
                de.principal_list,
                de.warehouse_list, 
                de.extract_date, 
                de.table_type, 
                de.principal_list_required,
                de.extract_date3
         from  data_extract de
         where de.extract_sql is not null
         and   de.table_type = '".$tbl ."'
         and   de.status = 'A' ");
$tranasction_tables= $dbConn->dbGetAll($sql);

echo $sql;

print_r($tranasction_tables);

echo 'oooooooo';



if ($tbl=="") { echo $htmlBody; return; }

$htmlBody="<body style='font-family:courier; font-size:12px;'>";

$htmlBody=ob_get_clean();
$htmlBody="";


$tranasction_tables=array();

$sql = ("select de.table_name, 
                de.extract_sql, 
                de.query1,
                de.query2,
                de.principal_list,
                de.warehouse_list, 
                de.extract_date, 
                de.table_type, 
                de.principal_list_required,
                de.extract_date3
         from  data_extract de
         where de.extract_sql is not null
         and   de.table_type = '{$tbl}'
         and   de.status = 'A' ");
$tranasction_tables= $dbConn->dbGetAll($sql);

if (sizeof($tranasction_tables)==0) {
     echo "No Tables to Extract: ".mysql_error($dbConn->connection);
     return;
} else {	
       $i=0;
       foreach($tranasction_tables as $trow) {
          	$i=0;    // Run Once for all principals 
            if ($ddb=="") { echo "No Destination Database Selected"; return; } 
            if ($i<>0) {
             	
             // Start each database table
                $htmlBody.="; \n";
            } 
             
             // Empty table if required
             
            if ($mpty == "Y") {
                $htmlBody.="truncate table " . $ddb . '.' . $trow[table_name] . "; \n"; 
            }        
         
            // Start each database table
         
         	 $dbConn->dbQuery("select column_name, is_nullable, data_type
                               from information_schema.columns
                               where table_schema = '".DATABASE."'
                               and table_name = '" . $trow[table_name]. "'");                       
                               
           if (!$dbConn->dbQueryResult) {
                 echo "Could not get table schema: ".mysql_error($dbConn->connection);
                 return;
           }
           $schema=array();
           while($row = mysql_fetch_array($dbConn->dbQueryResult,MYSQL_ASSOC)){
               $schema[$row["column_name"]]["is_nullable"]=$row["is_nullable"];
               $schema[$row["column_name"]]["data_type"]=$row["data_type"];
           }
             
           // Truncate temporary table
       
           if(trim($trow['de.query1']) !=''){
              $temp1  = $trow['de.query1'];
              $dbConn->dbQuery($temp1);
              if (!$dbConn->dbQueryResult) {
                  echo "Error in SQL:".mysql_error($dbConn->connection). $temp1;
                  return;
              } 
           }
           
           // If required fill Temp table for one principal 
           
          
           $htmlBody.="kKKKkkkkkkddddd";
           $htmlBody.= trim($trow['de.query1']);
           $htmlBody.= trim($trow['de.query2']);
           $htmlBody.= trim($trow['extract_sql']);
           
           if(trim($trow['de.query2']) !=''){ 
       
               if($trow[principal_list_required] == "N") {
       
                    $temp2  = str_replace('p_list',$trow['principal_list'],$trow['de.query2']); 
                    $temp2  = str_replace('d_list',$trow['warehouse_list'],$temp2);
                    $temp2  = str_replace('e_date',$trow['extract_date'],$temp2); 
                    $temp2  = str_replace('3_date',$trow['extract_date3'],$temp2); 
                    $dbConn->dbQuery($temp2);
                    if (!$dbConn->dbQueryResult) {
                       echo "Error in SQL:".mysql_error($dbConn->connection). $temp2;
                       return;
                    } 
               } else {           
                	
                  foreach($prinarr as $key => $pr) {
                 	   if(trim($trow['de.query2']) !=''){
                       $temp2  = str_replace('p_list',$trow['principal_list'],$trow['de.query2']); 
                       $temp2  = str_replace('d_list',$trow['warehouse_list'],$temp2);
                       $temp2  = str_replace('e_date',$trow['extract_date'],$temp2); 
                       $temp2  = str_replace('3_date',$trow['extract_date3'],$temp2); 
                       $dbConn->dbQuery($temp2);
                       if (!$dbConn->dbQueryResult) {
                          echo "Error in SQL:".mysql_error($dbConn->connection). $temp2;
                          return;
                      } 
                    }
                  }
               }     
           }
           // Created SQL file     
           if($trow[principal_list_required] == "N" || trim($trow['de.query2']) !='') {
           	  $temp  = str_replace('p_list',$fl,$trow[extract_sql]); 
              $temp  = str_replace('d_list',$whlist,$temp);
              $temp  = str_replace('e_date',$trow[extract_date],$temp); 
              $temp  = str_replace('3_date',$trow[extract_date3],$temp); 
                            
              $htmlBody.= "-- " . $temp . "\n";
              $dbConn->dbQuery($temp);
              if (!$dbConn->dbQueryResult) {
                  echo "Error in SQL:".mysql_error($dbConn->connection). $temp;
                  return;
              } 
              while($row = mysql_fetch_array($dbConn->dbQueryResult,MYSQL_ASSOC)){
                  $line="";
                  if ($i==0) {
                  	  $i=501;
                      $htmlBody.="insert into " . $ddb . '.' . $trow[table_name] . " values ";
                  } else if (fmod($i,1000)==0) {
                      $htmlBody.=";\n insert into " . $ddb . '.' . $trow[table_name] . " values ";
                  } 
                  foreach ($row as $key=>$field) {
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
        	        $htmlBody.=(($i==501) || (fmod($i,1000)==0))?"({$line})":",({$line})  \n";
                  $i++;
              } 
           } else {           
              $prinarr = array();
              $prinarr = explode(",", $principal_list);
           
              foreach($prinarr as $key => $pr) {
                   $temp  = str_replace('p_list',$fl,$trow[extract_sql]); 
                   $temp  = str_replace('d_list',$whlist,$temp);
                   $temp  = str_replace('e_date',$trow[extract_date],$temp); 
                   $temp  = str_replace('3_date',$trow[extract_date3],$temp);         	
                    
                   $htmlBody.= "-- " . $temp . "\n";
                   $dbConn->dbQuery($temp);
                   if (!$dbConn->dbQueryResult) {
                       echo "Error in SQL:".mysql_error($dbConn->connection). $temp;
                       return;
                   } 
                    while($row = mysql_fetch_array($dbConn->dbQueryResult,MYSQL_ASSOC)){
                       $line="";
                       if ($i==0) {
                  	       $i=501;                 	
                          $htmlBody.="insert into " . $ddb . '.' . $trow[table_name] . " values ";
                       } elseif (fmod($i,1000)==0) {
                     $htmlBody.=";\n insert into " . $ddb . '.' . $trow[table_name] . " values ";
                       } 
                       foreach ($row as $key=>$field) {
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
                       $htmlBody.=(($i==0) || (fmod($i,1000)==0))?"({$line})":",({$line})  \n";
                       $i++;
                    }
              }	
           }
       }
}         
$htmlBody.=";"; // dont forget to add the semicolon for last row
$htmlBody = gzencode($htmlBody, 9, FORCE_GZIP);
header("Content-Type: application/force-download");
header("Content-Disposition: filename=" . $trow[table_type]."_data.sql");
header("Content-Encoding: gzip");
header("Content-Length: ".strlen($htmlBody));
        
echo $htmlBody;

}
?>