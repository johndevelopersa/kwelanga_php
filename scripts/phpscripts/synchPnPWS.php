<?php
include_once('ROOT.php'); include_once($ROOT.'PHPINI.php');
include_once($ROOT.$PHPFOLDER.'properties/Constants.php');
include_once($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include_once($ROOT.$PHPFOLDER."properties/dbSettings.inc");
include_once($ROOT.$PHPFOLDER.'DAO/ExceptionThrower.php');

$dbConn = new dbConnect(); 
$dbConn->dbConnection();

$OdbConn = new dbConnect(); // online db
$OdbConn->dbConnectionOnline();



// first get the nullables
$dbConn->dbQuery("select column_name, is_nullable, data_type, table_name
                  from information_schema.columns
                  where table_schema = '".DATABASE."'
                  and table_name in ('orders_holding','orders_holding_detail','orders_holding_store','orders_holding_special_field')");
if (!$dbConn->dbQueryResult) {
  echo "Could not get table schema: ".mysql_error($dbConn->connection);
  return;
}
$schema=array();
while($row = mysql_fetch_array($dbConn->dbQueryResult,MYSQL_ASSOC)){
  $schema[$row["table_name"]][$row["column_name"]]["is_nullable"]=$row["is_nullable"];
  $schema[$row["table_name"]][$row["column_name"]]["data_type"]=$row["data_type"];
  $schema[$row["table_name"]]["columns"][]=$row["column_name"];
}



$OdbConn->dbQuery("select uid
                    from orders_holding
                    where data_source='WS'
                    and   ifnull(status,'') = ''");

$uids=array();
while($row = mysql_fetch_array($OdbConn->dbQueryResult,MYSQL_ASSOC)){
  $uids[] = $row["uid"];
}

if (sizeof($uids)>0) {
  
  $sqlOHDRows=array();
  $sqlOHSRows=array();
  $sqlOHSFRows=array();
  
  // START : get all the rows from all tables
  $OdbConn->dbQuery("select *
                    from orders_holding
                    where data_source='WS'
                    and   uid in (".implode(",",$uids).")");
                    
  $ohRows=$OdbConn->dbQueryResult;
  $ohRowsCnt=$OdbConn->dbQueryResultRows;
  
  $OdbConn->dbQuery("select *
                    from orders_holding_detail
                    where orders_holding_uid in (".implode(",",$uids).")");
  $ohdRows=$OdbConn->dbQueryResult;
  $ohdRowsCnt=$OdbConn->dbQueryResultRows;
  
  $OdbConn->dbQuery("select *
                    from orders_holding_store
                    where orders_holding_uid in (".implode(",",$uids).")");
                    
  $ohsRows=$OdbConn->dbQueryResult;
  $ohsRowsCnt=$OdbConn->dbQueryResultRows;
  
  $OdbConn->dbQuery("select *
                    from orders_holding_special_field
                    where orders_holding_uid in (".implode(",",$uids).")");
                    
  $ohsfRows=$OdbConn->dbQueryResult;
  $ohsfRowsCnt=$OdbConn->dbQueryResultRows;
  
  // END : get all the rows from all tables
   
   // for each ORDERS_HOLDING
   while($ohRow = mysql_fetch_array($ohRows,MYSQL_ASSOC)){
      
      // INSERT ORDERS_HOLDING
      
      $line=""; $sql=""; $cols=array();
      foreach ($ohRow as $key=>$field) {
        
        if ($key=="uid") continue; // key will be different
        
        if ($field=="") {
          if (($schema["orders_holding"][$key]["data_type"]=="varchar") || ($schema["orders_holding"][$key]["is_nullable"]=="NO")) {
            $line.=($line=="")?'':",''";
          } else if ($schema["orders_holding"][$key]["is_nullable"]=="YES") {
            $line.=($line=="")?"NULL":",NULL";
          } else {
            $line.=($line=="")?"NULL":",NULL";
          }
        } else {
          $line.=($line=="")?"'".addSlashes($field)."'":",'".addSlashes($field)."'";
        }
      }
      $cols=preg_replace("/(^uid,|,uid$)/","",implode(",",$schema["orders_holding"]["columns"]));
      $cols=str_replace(",uid,","",$cols);
      $sql="insert into orders_holding ({$cols}) 
            values ({$line})";
            
      $dbConn->dbinsQuery($sql);
      
      if (!$dbConn->dbQueryResult) {
        echo "Failed to insert ".$sql;
        $dbConn->dbInsQuery("rollback");
        return;
      } else if (!mysql_affected_rows($dbConn->connection)>0) {
        echo "Affected Rows Failed ";
        $dbConn->dbInsQuery("rollback");
        return;
      }
      $ohUId=$dbConn->dbGetLastInsertId();
      if ($ohUId=="") {
        echo "Failed to get UID";
        $dbConn->dbInsQuery("rollback");
        return;
      }
      
      // INSERT ORDERS_HOLDING_DETAIL
      mysql_data_seek($ohdRows,0);
      while($ohdRow = mysql_fetch_array($ohdRows,MYSQL_ASSOC)) {
        
        if ($ohdRow["orders_holding_uid"]!=$ohRow["uid"]) continue; // only process header detail
        $line="";
        foreach ($ohdRow as $key=>$field) {
          
          if ($key=="uid") continue; // key will be different
          
          if ($key=="orders_holding_uid") $line.=($line=="")?"'{$ohUId}'":",'{$ohUId}'"; 
          else {
            if ($field=="") {
              if (($schema["orders_holding_detail"][$key]["data_type"]=="varchar") || ($schema["orders_holding_detail"][$key]["is_nullable"]=="NO")) {
                $line.=($line=="")?'':",''";
              } else if ($schema["orders_holding_detail"][$key]["is_nullable"]=="YES") {
                $line.=($line=="")?"NULL":",NULL";
              } else {
                $line.=($line=="")?"NULL":",NULL";
              }
            } else {
              $line.=($line=="")?"'".addSlashes($field)."'":",'".addSlashes($field)."'";
            }
          }
        }
        $sqlOHDRows[]="({$line})";
        
      }
      
      
      // INSERT ORDERS_HOLDING_STORE
      if ($ohsfRowsCnt>0) mysql_data_seek($ohsRows,0);
      while($ohsRow = mysql_fetch_array($ohsRows,MYSQL_ASSOC)) {
        
        if ($ohsRow["orders_holding_uid"]!=$ohRow["uid"]) continue; // only process header detail
        
        $line="";
        foreach ($ohsRow as $key=>$field) {
          
          if ($key=="uid") continue; // key will be different
          
          if ($key=="orders_holding_uid") $line.=($line=="")?"'{$ohUId}'":",'{$ohUId}'"; 
          else {
            if ($field=="") {
              if (($schema["orders_holding_store"][$key]["data_type"]=="varchar") || ($schema["orders_holding_store"][$key]["is_nullable"]=="NO")) {
                $line.=($line=="")?'':",''";
              } else if ($schema["orders_holding_store"][$key]["is_nullable"]=="YES") {
                $line.=($line=="")?"NULL":",NULL";
              } else {
                $line.=($line=="")?"NULL":",NULL";
              }
            } else {
              $line.=($line=="")?"'".addSlashes($field)."'":",'".addSlashes($field)."'";
            }
          }
        }
        $sqlOHSRows[]="({$line})";
        
      }
      
      
       // INSERT ORDERS_HOLDING_SPECIAL_FIELD
       if ($ohsfRowsCnt>0) mysql_data_seek($ohsfRows,0);
      while($ohsfRow = mysql_fetch_array($ohsfRows,MYSQL_ASSOC)) {
        
        if ($ohsfRow["orders_holding_uid"]!=$ohRow["uid"]) continue; // only process header detail
        
        $line="";
        foreach ($ohsfRow as $key=>$field) {
          
          if ($key=="uid") continue; // key will be different
          
          if ($key=="orders_holding_uid") $line.=($line=="")?"'{$ohUId}'":",'{$ohUId}'"; 
          else {
            if ($field=="") {
              if (($schema["orders_holding_special_field"][$key]["data_type"]=="varchar") || ($schema["orders_holding_special_field"][$key]["is_nullable"]=="NO")) {
                $line.=($line=="")?'':",''";
              } else if ($schema["orders_holding_special_field"][$key]["is_nullable"]=="YES") {
                $line.=($line=="")?"NULL":",NULL";
              } else {
                $line.=($line=="")?"NULL":",NULL";
              }
            } else {
              $line.=($line=="")?"'".addSlashes($field)."'":",'".addSlashes($field)."'";
            }
          }
        }
        $sqlOHSFRows[]="({$line})";
        
      }
      
   }       
   
   $cols=preg_replace("/(^uid,|,uid$)/","",implode(",",$schema["orders_holding_detail"]["columns"]));
   $cols=str_replace(",uid,","",$cols);
   $sql="insert into orders_holding_detail ({$cols}) 
              values ".implode(",",$sqlOHDRows);
             
    $dbConn->dbinsQuery($sql);
    
    if (!$dbConn->dbQueryResult) {
      echo "Failed to insert ".mysql_error($dbConn->connection).$sql;
      $dbConn->dbInsQuery("rollback");
      return;
    } else if (!(mysql_affected_rows($dbConn->connection)>0)) {
      echo "Affected Rows Failed ";
      $dbConn->dbInsQuery("rollback");
      return;
    }
    
    if (sizeof($sqlOHSRows)>0) {
      $cols=preg_replace("/(^uid,|,uid$)/","",implode(",",$schema["orders_holding_store"]["columns"]));
      $cols=str_replace(",uid,","",$cols);
      $sql="insert into orders_holding_store ({$cols}) 
                values ".implode(",",$sqlOHSRows);
                
      $dbConn->dbinsQuery($sql);
      
      if (!$dbConn->dbQueryResult) {
        echo "Failed to insert ".mysql_error($dbConn->connection).$sql;
        $dbConn->dbInsQuery("rollback");
        return;
      } else if ((!(mysql_affected_rows($dbConn->connection)>0)) && (sizeof($sqlOHSRows)>0)) {
        echo "Affected Rows Failed ";
        $dbConn->dbInsQuery("rollback");
        return;
      }
    }
    
    if (sizeof($sqlOHSFRows)>0) {
      $cols=preg_replace("/(^uid,|,uid$)/","",implode(",",$schema["orders_holding_special_field"]["columns"]));
      $cols=str_replace(",uid,","",$cols);
      $sql="insert into orders_holding_special_field ({$cols}) 
                values ".implode(",",$sqlOHSFRows);
                
      $dbConn->dbinsQuery($sql);
      
      if (!$dbConn->dbQueryResult) {
        echo "Failed to insert ".mysql_error($dbConn->connection).$sql;
        $dbConn->dbInsQuery("rollback");
        return;
      } else if ((!(mysql_affected_rows($dbConn->connection)>0)) && (sizeof($sqlOHSFRows)>0)) {
        echo "Affected Rows Failed ";
        $dbConn->dbInsQuery("rollback");
        return;
      }
    }
  
    
} else {
  echo "no rows found to synch";
  return;
}


$OdbConn->dbQuery("update orders_holding set status = 'X' where data_source='WS' and uid in (".implode(",",$uids).")");

// make sure the lock is released
$dbConn->dbInsQuery("commit");
$OdbConn->dbInsQuery("commit");

echo "Header : ".sizeof($uids)." rows imported<br>";
echo "Detail : ".sizeof($sqlOHDRows)." rows imported<br>";
echo "There may be a limit in place of # rows";

?>
