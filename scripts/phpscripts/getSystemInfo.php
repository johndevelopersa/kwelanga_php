<?php


//gets a list of export and incoming awaiting files.

include('ROOT.php');
include($ROOT.'PHPINI.php');
include($ROOT.$PHPFOLDER."DAO/db_Connection_Class.php");
include($ROOT.$PHPFOLDER.'properties/dbSettings.inc');

if(!isset($_GET['PWD'])){
	echo 'Restricted Access (1).';
	return;
}
if($_GET['PWD'] != 'aa8554'){
	echo 'Restricted Access (2).';
	return;
}


set_time_limit(60*5);

new qFiles();


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
    $this->renderFileCount('Pending Depot Exports : FILE', $depotArr);


    //import files
    $importArr = array();
    foreach($this->getImportLocations() as $loc){
      $importArr[] = array(
                          'path' => constant($loc['root_dir_constant']) . $loc['file_path'],
                          'name' => $loc['vendor']
                         );
    }
    $this->renderFileCount('EDI Imports : FILE', $importArr);


    //failed depot files
    $this->renderDepotFailed();

    //Computational errors
    $this->renderCompErrors();

    $this->renderDocumentCount();

    $this->renderDocumentUpdateErrors();
  }


  private function renderFileCount($title, $locations){

    echo '<div id="level" ><div id="wrap">';
      echo '<div id="title">',$title,'</div>';

	  $no = 0;
      foreach($locations as $loc){
        $dirArr = scandir($loc['path']);
        $cnt = 0;
        foreach($dirArr as $file){
          if(is_file($loc['path'].$file) && $file != '.' && $file != '..' && $file[0] != '.'){  //first char is not a dot, ie: .htaccess, .bash_profile etc
            $cnt++;
          }
        }

        if($cnt>0){
		  echo '<div id="color_blue">' . $loc['name'] . ' : <strong>' . $cnt . '</strong></div>';
		  $no++;
        }
      }
	  if($no==0){
	    echo '<div id="color_blue">nothing pending...</div>';
	  }

      echo '</div>';
    echo '</div>';
	echo '<div style="clear:both"></div><br>';
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


  private function renderDocumentCount(){




	  echo '<div id="level" ><div id="wrap">';
	  echo '<div id="title">Transactions : Orders Holding</div>';

		$ohArr = $this->getOrderHoldingCounts();

		if(count($ohArr)>0){
			if($ohArr[0]['error_count'] > 0){
				echo '<div id="color_red">Exceptions: <strong>' . $ohArr[0]['error_count'] . '</strong></div>';
			}
			if($ohArr[0]['pending_count'] > 0){
				echo '<div id="color_blue">Processing: <strong>' . $ohArr[0]['pending_count'] . '</strong></div>';
			}
		}
		echo '</div>';
	  echo '</div>';


	  echo '<div id="level" ><div id="wrap">';
	  echo '<div id="title">Transactions : Today Totals</div>';

		$docArr = $this->getDocumentCounts();
		foreach($docArr as $row){
			echo '<div id="color_blue">'.$row['source'].': <strong>' . $row['total'] . '</strong></div>';
		}


		//echo '<br>';


		$unDocArr = $this->getDocumentUnacceptedCount();
		$unacceptedCount = (isset($unDocArr[0]['total'])) ? $unDocArr[0]['total'] : 'na';
		echo '<div id="color_blue">Unaccepted: <strong>' . $unacceptedCount . '</strong></div>';

		$mergeDocArr = $this->getDocumentHighMergeTimeCount();
		$highMergeCount = (isset($mergeDocArr[0]['total'])) ? $mergeDocArr[0]['total'] : 'na';
		echo '<div id="color_blue">Long Merge Time: <strong>' . $highMergeCount . '</strong></div>';


		//echo '<br>';

		$userArr = $this->getUsersToday();
		$userArr = (isset($userArr[0]['total'])) ? $userArr[0]['total'] : 'na';
		echo '<div id="color_blue">User Access: <strong>' . $userArr . '</strong></div>';


		echo '</div>';
	  echo '</div>';


  }


  private function renderDocumentUpdateErrors(){

    $arr = $this->getDocumentUpdateCount();

    if(count($arr)>0){
	  echo '<div id="level" ><div id="wrap">';
      echo '<div id="title">Transactions : Updates</div>';
			if($arr[0]['pending_count'] > 0){
				echo '<div id="color_blue">Processing: <strong>' . $arr[0]['pending_count'] . '</strong></div>';
			}
			if($arr[0]['error_count'] > 0){
				echo '<div id="color_red">Errors: <strong>' . $arr[0]['error_count'] . '</strong></div>';
			}
        echo '</div>';
      echo '</div>';
    }

  }



  private function renderCompErrors(){

    $arr = $this->getCompErrors();

    if(count($arr)>0){
 	  echo '<div id="level" ><div id="wrap">';
		echo '<div id="title">Computational Errors:</div>';

        foreach($arr as $f){
          echo '<div style="background:#DF0101;color:#fff;;padding:3px 10px;margin-top:1px;">' . $f['name'] . ' : ' . $f['error_cnt'] . '</div>';
        }

        echo '</div>';
      echo '</div>';
    }

  }

  private function getDocumentHighMergeTimeCount($time = 20){

    return $this->dbConn->dbGetAll(" SELECT
										COUNT(m.uid) as 'total'
									 FROM document_master m
									WHERE m.processed_date = CURDATE()
									AND m.depot_uid NOT IN (105,7)
									AND IFNULL(TIMESTAMPDIFF(MINUTE,
																CONCAT(m.processed_date, ' ', m.processed_time),
																CONCAT(m.merged_date, ' ', m.merged_time)
														 ),0) > {$time}");

  }

  private function getDocumentUnacceptedCount(){

    return $this->dbConn->dbGetAll("SELECT COUNT(m.uid) as 'total'
                                    FROM document_master m
                  										    INNER JOIN document_header h ON m.uid = h.document_master_uid
                  									WHERE m.processed_date >= CURDATE() - INTERVAL 7 DAY
                  									AND h.document_status_uid = 74
                                    AND  m.principal_uid != 171");

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


  private function getDocumentUpdateCount(){

    return $this->dbConn->dbGetAll("SELECT
										COUNT(IF(u.processed_status = 'E',1,NULL)) as 'error_count',
										COUNT(IF(u.processed_status = 'Q',1,NULL)) as 'pending_count'
									FROM document_update u
									  WHERE u.processed_status IN ('E','Q')");

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
							AND o.document_type NOT IN (4,8)
                            and o.capturedate > (CURDATE() - INTERVAL 7 DAY)");
  }

  private function getDepotLocations(){

    return $this->dbConn->dbGetAll("select folder_name from online_export_mapping ORDER BY folder_name");

  }

  private function getOrderHoldingCounts(){

    return $this->dbConn->dbGetAll("SELECT
										COUNT(IF(u.`status` != '',1,NULL)) as 'error_count',
										COUNT(IF(u.`status` = '',1,NULL)) as 'pending_count'
									FROM orders_holding u
									  WHERE u.`status` NOT IN ('D','S')");

  }

   private function getDocumentCounts(){

    return $this->dbConn->dbGetAll("SELECT
										IF(h.data_source IS NOT NULL, h.data_source,'- TOTAL -') as 'source',
										count(m.uid) as 'total'
									FROM document_master m
									INNER JOIN document_header h on m.uid = h.document_master_uid
									  WHERE m.processed_date = CURDATE()
									  GROUP BY h.data_source
									  WITH ROLLUP");

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


  private function getUsersToday(){

    return $this->dbConn->dbGetAll("SELECT
						COUNT(DISTINCT u.user_uid) as 'total'
						FROM user_tracking u
							WHERE u.login_date_time BETWEEN CONCAT(CURDATE(),' 00:00:00')
														AND CONCAT(CURDATE(),' 23:59:59')");

  }



}

