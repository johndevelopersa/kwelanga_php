<?php

class WSlogger {

  public static function wsServerLog($type, $description, $docno = 0, $principaluid = 0, $vendoruid = 0, $requestXML) {

    if (self::wsLogFolder()) {
      //log
      $fh = @fopen('wslog/'.date('Y.m.d').'.log', 'a'); //create file if doesn't exist
      if ($fh) {
        fwrite($fh, date('h:i:s').' '.$type.' : '.$description.' ('.$vendoruid.':'.$principaluid.':'.$docno.")\r\n");
        fclose($fh);
      }
      //lastRequest
      $fh = @fopen('wslog/lastRequest.xml', 'w'); //create file if doesn't exist
      if ($fh) {
        fwrite($fh, $requestXML);
        fclose($fh);
      }
    }
  }
  
  public static function wsDebugReceivedLog($requestXML) {

    if (self::wsLogFolder()) {
      //lastRequest
      $fh = @fopen('wslog/lastRequestDebug.xml', 'a'); // append, but create file if doesn't exist
      if ($fh) {
        fwrite($fh, $requestXML);
        fclose($fh);
      }
    }
  }

  private static function wsLogFolder() {

    if (is_dir('wslog')) {
      return true;
    } else {
      return mkdir('wslog');
    }
  }

}




class viewLogs {

  public function __construct() {

    if(isset($_GET['clearlogs'])){
      $this->deleteLogs();
      return;
    }

    //'wslog'
    echo '<table width="100%" border="1"><tr><td width="250" valign="top"><div><strong>LOG FILES:</strong> </div><hr>';
    chdir('wslog');
    $vDirSize = 0;
    foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator('.')) as $file) {
      $vFileSize = filesize($file);
      echo '<a href="?logfile='.substr($file,strpos($file,'\\')+1).'">'.substr($file,strpos($file,'\\')+1), '</a> (', $vFileSize, " bytes)\n<br>";
      $vDirSize += $vFileSize;
    }
    echo '<hr><strong>Total Directory: ', $vDirSize, ' bytes</strong><hr>';
    echo '<a href="'.$SERVER['PHP_SELF'].'?clearlogs=1" >Delete Logs</a>';
    echo '</td><td valign="top">';

    if(isset($_GET['logfile'])){
      echo '<h2>'.$_GET['logfile'].'</h2>';
      echo '<hr>';


      $fileArr = file($_GET['logfile']);

      if(strpos($_GET['logfile'],'.xml')!==false){
        echo '<div style="height:500;overflow:scroll;width:100%;max-width:700px"><pre>'.htmlentities(join('',$fileArr)).'</pre></div>';
      } else {
        foreach($fileArr as $fl){

          $style = '#efefef;color:#000';
          if(strpos(strtoupper($fl),'SUCCESS')!==false){
            $style = 'lime;color:#000';
          } elseif(strpos(strtoupper(substr($fl,0,15)),'ERROR')!==false) {
            $style = 'yellow;color:#000';
          } elseif(strpos(strtoupper($fl),'FAULT')!==false) {
            $style = 'red;color:#fff';
          }

          echo '<div style="background:'.$style.';">'.$fl.'</div>';
        }
      }
    }
    echo'</td></tr></table>';

    echo '<br><br><a href="'.$SERVER['PHP_SELF'].'?testws">Test Server</a>';
    if(isset($_GET['testws'])){
      new testWS();
    }

  }

    private function deleteLogs(){
      $dir = 'wslog/';
      foreach(glob($dir.'*.*') as $v){
        unlink($v);
      }
      echo '<div align="center"><H3 style="color:red;">deleted logs</H3><a href="'.$SERVER['PHP_SELF'].'?">BACK TO LOGS</a></div>';
    }

}




class testWS{

  public function __construct() {

    echo '<br><hr>Entering test...<hr>';
    require_once('lib/nusoap.php');	//include nusoap lib
    //connection param
    $wsURL = 'http://127.0.0.1/rtsystem/new/active%20eclipseworkspace/RetailTradingTest/ws/rttsoap.php';
    echo 'WS URL: '.$wsURL.'<hr>';
    $client = new nusoap_client($wsURL, false);
    $err = $client->getError();
    $client->xml_encoding = 'UTF-8';
    echo 'Saying Hello World...<hr>';
    //test using RPC call
    $test = $client->call('echoString',array('string'=>'hello world'),'http://soapinterop.org/');

    echo '<h3>Result </h3><pre>', var_dump($test) , '</pre><hr>';

    echo '<h3>Error ',var_dump($err),'</h3><pre>',var_dump($client->error_str),'</pre></hr>';

    echo '<h3>Debug </h3><pre>',$client->debug_str,'</pre><hr>';


  }

}

?>