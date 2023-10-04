<?php


  set_time_limit(60);

  include_once('ROOT.php');
  include_once($ROOT.'PHPINI.php');
  //include_once($ROOT.$PHPFOLDER.'functional/main/access_control.php');
  include_once($ROOT.$PHPFOLDER."DAO/AdministrationDAO.php");
  include($ROOT.$PHPFOLDER.'libs/fushion/Code/PHP/Includes/FusionCharts.php');  //fushion lib


  class systemStatus {


      public static function userTodayGraphTotal($dbConn){

        global $ROOT, $PHPFOLDER;

        //QUERY
        $dbConn->dbQuery("SELECT
        					sum(if(u.deleted!=1,1,0)) as active,
        					sum(if(DATE(u.lastlogin)=CURDATE(),1,0)) as login_today
        				  from
        					users u");

        $runSQL = mysql_num_rows($dbConn->dbQueryResult);

        if($runSQL = 1){

          $dataArr = array();
          while($row = mysql_fetch_array($dbConn->dbQueryResult,MYSQL_ASSOC)){
            $dataArr[] = $row;
          }

          //XML FOR GRAPH
          $strXML = "<graph caption='USERS TOTAL - ".date('Y-m-d')."' subCAption='".$dataArr[0]['active']." Active Users' showPercentValues='1' showValues='1'  pieSliceDepth='20' showNames='1' decimalPrecision='0' >";
          $strXML .= "<set name='Today Active' value='" . $dataArr[0]['login_today'] . "' />";
          $strXML .= "<set name='In-Active' value='" . ($dataArr[0]['active']-$dataArr[0]['login_today']) . "'/>";
          $strXML .= "</graph>";

	      //DISPLAY GRAPH
	      echo renderChart($ROOT.$PHPFOLDER."libs/fushion/Charts/FCF_Pie2D.swf", "", $strXML, "UsersToday", 380, 300);
        }
	}


        public static function ordersTypesTotalToday($dbConn){

        global $ROOT, $PHPFOLDER;

        //QUERY
        $dbConn->dbQuery("select date(o.capturedate), o.data_source, count(o.uid) AS day_total from orders o
                          where date(o.capturedate) = curdate()
                          group by o.data_source");

        $runSQL = mysql_num_rows($dbConn->dbQueryResult);

        if($runSQL = 1){

          $dataArr = array();
          while($row = mysql_fetch_array($dbConn->dbQueryResult,MYSQL_ASSOC)){
            $dataArr[] = $row;
          }

          //XML FOR GRAPH
          $strXML = "<graph caption='Order Types Today - ".date('Y-m-d')."' showPercentValues='1' showValues='1'  pieSliceDepth='20' showNames='1' decimalPrecision='0' >";

          foreach($dataArr as $k => $v){
            $strXML .= "<set name='".$v['data_source']."' value='" . $v['day_total'] . "' />";
          }
          $strXML .= "</graph>";

	      //DISPLAY GRAPH
	      echo renderChart($ROOT.$PHPFOLDER."libs/fushion/Charts/FCF_Pie2D.swf", "", $strXML, "OrderTypes", 420, 320);
        }
	}




    public static function userTodayGraph($dbConn){

        global $ROOT, $PHPFOLDER;

        //QUERY
        $dbConn->dbQuery("SELECT
        					HOUR(CONVERT_TZ(u.lastlogin,'+00:00','+02:00')) AS hour,
        					count(u.uid) AS 'users'
        				from `users` u
        					where DATE(u.lastlogin) = CURDATE()
        					GROUP BY HOUR(u.lastlogin)");

        $runSQL = mysql_num_rows($dbConn->dbQueryResult);

        if($runSQL = 1){

          $dataArr = array();
          while($row = mysql_fetch_array($dbConn->dbQueryResult,MYSQL_ASSOC)){
            $dataArr[] = $row;
          }

          //TIME PLACEHOLDER
          $timeArr = range(1,23);

          //XML FOR GRAPH
          $strXML = "<graph caption='USERS PER HOUR - ".date('Y-m-d')."' numberPrefix='' formatNumberScale='0' decimalPrecision='0'>";
          foreach ($timeArr as $hour){
            $t = '';
            foreach($dataArr as $usersPerHour){
              if($usersPerHour['hour'] == $hour){
                $t = $usersPerHour['users'];
              }
            }
            $strXML .= "<set name='" . $hour . ":00' value='" . $t . "' color='cc0000' />";
          }
          $strXML .= "</graph>";

	      //DISPLAY GRAPH
	      echo renderChart($ROOT.$PHPFOLDER."libs/fushion/Charts/FCF_Column2D.swf", "", $strXML, "UsersHours", 950, 300);
        }
	}


  public static function ordersTodayGraphDay($dbConn){

    global $ROOT, $PHPFOLDER;

    $sql = "select count(*) AS 'orders',
  						HOUR(CONVERT_TZ(o.capturedate,'+00:00','+02:00')) AS 'hour'
  					 from
  						`orders` o
  					 where
  						DATE(o.capturedate) = CURDATE()
  						GROUP BY HOUR(o.capturedate)";

     $ordersToday = $dbConn->dbGetAll($sql);

      //TIME PLACEHOLDER
      $timeArr = range(1,23);

      //XML FOR GRAPH
      $strXML = "<graph caption='ORDERS TODAY - ".date('Y-m-d')."' numberPrefix='' formatNumberScale='0' decimalPrecision='0'>";
      foreach ($timeArr as $hour){
        $t = '';
        foreach($ordersToday as $ordersPerHour){
          if($ordersPerHour['hour'] == $hour){
            $t = $ordersPerHour['orders'];
          }
        }
        $strXML .= "<set name='" . $hour . ":00' value='" . $t . "' color='3672b5' />";
      }
      $strXML .= "</graph>";

    //DISPLAY GRAPH
    echo renderChart($ROOT.$PHPFOLDER."libs/fushion/Charts/FCF_Line.swf", "", $strXML, "ordersDay", 950, 300);

    }



  public static function ordersTodayGraphMonth($dbConn){

    global $ROOT, $PHPFOLDER;

  	$dbConn->dbQuery("select
  						DATE(o.capturedate) AS 'date',
  						count(*) AS 'orders'
  					 from
  						`orders` o
  					 where
  						DATE(o.capturedate) BETWEEN CURDATE() - INTERVAL 30 DAY AND CURDATE()
  						GROUP BY DATE(o.capturedate)");

    $runSQL = mysql_num_rows($dbConn->dbQueryResult);

    if($runSQL = 1){

      $ordersWeek = array();
      while($row = mysql_fetch_array($dbConn->dbQueryResult,MYSQL_ASSOC)){
        $ordersWeek[] = $row;
      }

      //XML FOR GRAPH
      $strXML = "<graph caption='ORDERS PAST 30 DAYS' numberPrefix='' formatNumberScale='0' decimalPrecision='0'>";
      foreach ($ordersWeek as $order){
        $strXML .= "<set name='" . date('m.d',strtotime($order['date'])) . "' value='" . $order['orders'] . "' color='0f57a7' />";
      }
      $strXML .= "</graph>";

    //DISPLAY GRAPH
    echo renderChart($ROOT.$PHPFOLDER."libs/fushion/Charts/FCF_Line.swf", "", $strXML, "ordersWeek", 950, 300);

    }
  }


  public static function ordersTodayGraphYear($dbConn){

    global $ROOT, $PHPFOLDER;

  	$dbConn->dbQuery("SELECT
                      	o.capturedate AS 'date',
                        COUNT(*) AS 'orders'
                      from
                      	`orders` o
                      where
                      	DATE(o.capturedate) BETWEEN
                      		CONCAT(YEAR(curdate() - interval 12 month),'-',month(curdate() - interval 12 month),'-1')
                      	AND
                      		CONCAT(YEAR(curdate()),'-',month(curdate()),'-',DAY(LAST_DAY(curdate())))
                      GROUP BY YEAR(o.capturedate), month(o.capturedate)");

    $runSQL = mysql_num_rows($dbConn->dbQueryResult);

    if($runSQL = 1){

      $orders6Months = array();
      while($row = mysql_fetch_array($dbConn->dbQueryResult,MYSQL_ASSOC)){
        $orders6Months[] = $row;
      }

      //XML FOR GRAPH
      $strXML = "<graph caption='ORDERS PAST 12 MONTHS' numberPrefix='' formatNumberScale='0' decimalPrecision='0'>";
      foreach ($orders6Months as $order){
        $strXML .= "<set name='" . date('m-Y',strtotime($order['date'])) . "' value='" . $order['orders'] . "' color='FE642E' />";
      }
      $strXML .= "</graph>";

    //DISPLAY GRAPH
    echo renderChart($ROOT.$PHPFOLDER."libs/fushion/Charts/FCF_Line.swf", "", $strXML, "ordersMonth", 850, 300);

    }
  }


  public static function ordersCasesValueGraphYear($dbConn){

    global $ROOT, $PHPFOLDER;

  	$sql = "SELECT
                                      o.processed_date AS 'date',
                                SUM(h.cases) AS 'cases',
                                SUM(h.invoice_total) as 'value'
                              from
                                      `document_master` o
                                      inner join document_header h on o.uid = h.document_master_uid
                              where
                                      DATE(o.processed_date) BETWEEN
                                              CONCAT(YEAR(curdate() - interval 12 month),'-',month(curdate() - interval 12 month),'-1')
                                      AND
                                              CONCAT(YEAR(curdate()),'-',month(curdate()),'-',DAY(LAST_DAY(curdate())))
                              GROUP BY YEAR(o.processed_date), month(o.processed_date)";

     $orders6Months = $dbConn->dbGetAll($sql);


      //XML FOR GRAPH
      $strXML = "<graph caption='ORDERS CASES 12 months' numberPrefix='' formatNumberScale='0' rotateValues='1' decimalPrecision='0'>";
      foreach ($orders6Months as $order){
        $strXML .= "<set name='" .date('m-Y',strtotime($order['date'])) . "' value='" . $order['cases'] . "' color='FE642E' />";
      }
      $strXML .= "</graph>";

      //DISPLAY GRAPH
      echo renderChart($ROOT.$PHPFOLDER."libs/fushion/Charts/FCF_Line.swf", "", $strXML, "ordersValue1", 850, 300);


      /*---------------------------------*/

      //XML FOR GRAPH
      $strXML = "<graph caption='ORDERS VALUE 12 months' numberPrefix='' formatNumberScale='0' rotateValues='1' decimalPrecision='0'>";
      foreach ($orders6Months as $order){
        $strXML .= "<set name='" .date('m-Y',strtotime($order['date'])) . "' value='" . $order['value'] . "' color='FE642E' />";
      }
      $strXML .= "</graph>";

      //DISPLAY GRAPH
      echo renderChart($ROOT.$PHPFOLDER."libs/fushion/Charts/FCF_Line.swf", "", $strXML, "ordersValue2", 850, 300);

    }


  }


?>


