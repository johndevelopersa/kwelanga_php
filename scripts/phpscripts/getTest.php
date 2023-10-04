<?php


//gets a list of export and incoming awaiting files.

include('ROOT.php');
include($ROOT.'PHPINI.php');
include($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include($ROOT.$PHPFOLDER.'properties/dbSettings.inc');

set_time_limit(60);


$dbConn = new dbConnect();
$dbConn->dbConnection();
	
$dbConn->dbQuery("SET time_zone='+0:00'");
	
$r = $dbConn->dbGetAll("select count(*) as cnt from orders o 
					where o.capturedate > (NOW() - INTERVAL 5 MINUTE)");
					
echo $r[0]['cnt'];
					

return;

					
//SET time_zone='+0:00';



class qFiles {


  public $dbConn;


  public function __construct(){

    global $ROOT, $PHPFOLDER;
    $this->dbConn = new dbConnect();
    $this->dbConn->dbConnection();


    //depot
    $depotArr = array();
    foreach($this->getDepotLocations() as $loc){
      $depotArr[] = array(
                          'path' => $_SERVER['DOCUMENT_ROOT'] . '/archives/exports/' . $loc['folder_name'],
                          'name' => $loc['folder_name']
                         );
    }
    $this->renderFileCount('Depot Exports', $depotArr);


    //import files
    $importArr = array();
    foreach($this->getImportLocations() as $loc){
      $importArr[] = array(
                          'path' => constant($loc['root_dir_constant']) . $loc['file_path'],
                          'name' => $loc['vendor']
                         );
    }
    $this->renderFileCount('EDI Imports', $importArr);


    //failed depot files
    $this->renderDepotFailed();

    //Computational errors
    $this->renderCompErrors();

    $this->renderPendingOrders();

    $this->renderDocumentUpdateErrors();
  }


  private function renderFileCount($title, $locations){

    echo '<div class="listWrap" >';
      echo '<div class="title">',$title,'</div>';
      echo '<div id="level">';

      foreach($locations as $loc){
        $dirArr = scandir($loc['path']);
        $cnt = 0;
        foreach($dirArr as $file){
          if(is_file($loc['path'].$file) && $file != '.' && $file != '..' && $file[0] != '.'){  //first char is not a dot, ie: .htaccess, .bash_profile etc
            $cnt++;
          }
        }

        if($cnt>0){
          echo '<div style="' , (($cnt > 0)?('background:#F7D358'):('')) , ';padding:3px 10px;margin-top:1px;">' . $loc['name'] . ' : <strong>' . $cnt . '</strong></div>';
        }
      }

      echo '</div>';
    echo '</div>';

  }


  private function renderDepotFailed(){

    $arr = $this->getFailedDepotExports();

    if(count($arr)>0){
      echo '<div class="listWrap" >';
        echo '<div class="title">Fail Depot Exports:</div>';
        echo '<div id="level">';

        foreach($arr as $f){
          echo '<div style="background:#DF0101;color:#fff;;padding:3px 10px;margin-top:1px;">' . $f['name'] . ' : <strong>' . $f['order_sequence_no'] . '</strong></div>';
        }

        echo '</div>';
      echo '</div>';
    }

  }

  private function renderPendingOrders(){

    $arr = $this->getPendingOrders();

    if(count($arr)>0){
      echo '<div class="listWrap" >';
        echo '<div class="title">Pending Orders:</div>';
        echo '<div id="level">';
          echo '<div style="background:#DF0101;color:#fff;;padding:3px 30px;font-size:30px;line-height:40px;"><strong>' . $arr[0]['pending_orders'] . '</strong></div>';
        echo '</div>';
      echo '</div>';
    }

  }


  private function renderDocumentUpdateErrors(){

    $arr = $this->getDocumentUpdateErrorCount();

    if(count($arr)>0){
      echo '<div class="listWrap" >';
        echo '<div class="title">Document Update Errors:</div>';
        echo '<div id="level">';
          echo '<div style="background:#DF0101;color:#fff;;padding:3px 30px;font-size:30px;line-height:40px;"><strong>' . $arr[0]['error_cnt'] . '</strong></div>';
        echo '</div>';
      echo '</div>';
    }

  }



  private function renderCompErrors(){

    $arr = $this->getCompErrors();

    if(count($arr)>0){
      echo '<div class="listWrap" >';
        echo '<div class="title">Computational Errors:</div>';
        echo '<div id="level">';

        foreach($arr as $f){
          echo '<div style="background:#DF0101;color:#fff;;padding:3px 10px;margin-top:1px;">' . $f['name'] . ' : ' . $f['error_cnt'] . '</div>';
        }

        echo '</div>';
      echo '</div>';
    }

  }

  private function getImportLocations(){

    return $this->dbConn->dbGetAll("SELECT
                                  f.root_dir_constant,
                                  f.file_path,
                                  v.name AS `vendor`
                          FROM online_file_processing f
                          INNER JOIN vendor v ON f.vendor_uid = v.uid
                          GROUP BY f.file_path
                          ORDER BY v.name");

  }


  private function getDocumentUpdateErrorCount(){

    return $this->dbConn->dbGetAll("select count(*) as 'error_cnt' from document_update u where u.processed_status = 'E'");

  }

  private function getFailedDepotExports(){
    return $this->dbConn->dbGetAll("SELECT
                            p.name,
                            o.order_sequence_no
                          from orders o
                            inner join online_export_mapping m on o.processed_depot_uid = m.type_uid and type = 'D'
                            inner join principal p on o.principal_uid = p.uid
                          where  o.edi_depot_created = 'N'
                            and NOT FIND_IN_SET(o.principal_uid, m.principal_exclude_list)
                            and o.capturedate > '2012-08-21'");
  }

  private function getDepotLocations(){

    return $this->dbConn->dbGetAll("select folder_name from online_export_mapping ORDER BY folder_name");

  }

  private function getPendingOrders(){
    return $this->dbConn->dbGetAll("select count(*) as 'pending_orders' from orders_holding oh where if(oh.status is null,0,oh.status) not in ('S','D')");
  }

  private function getCompErrors(){


    return $this->dbConn->dbGetAll("select
                            p.name, m.order_sequence_no
                          from document_master m
                          inner join document_header h on m.uid = h.document_master_uid
                          inner join principal p on m.principal_uid = p.uid
                          where
                          m.processed_date > '2012-08-01'
                          and h.document_status_uid = 15");

  }


}

