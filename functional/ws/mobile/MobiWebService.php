<?php

/*
 * WEB SERVICE FOR MOBI APPS
 *
 * MAIN CLASS
 *
 */

include_once 'ROOT.php'; include_once $ROOT.'PHPINI.php';
include_once $ROOT.$PHPFOLDER."DAO/db_Connection_Class.php";
include_once $ROOT.$PHPFOLDER.'properties/dbSettings.inc';
include_once $ROOT.$PHPFOLDER.'libs/CommonUtils.php';
include_once $ROOT.$PHPFOLDER.'TO/ErrorTO.php';

class MobiWebService {

  private $errorTO;
  private $allowedCallMatrix = array('MobileHandler' => array(), 'ProductDAO' => array(), 'ProductDAO' => array());

  public function __construct(){

    global $ROOT, $PHPFOLDER;

    $this->errorTO = new ErrorTO();
    $this->errorTO->type = FLAG_ERRORTO_ERROR;


    //for large post data
    $requestJson = (isset($HTTP_RAW_POST_DATA)) ? $HTTP_RAW_POST_DATA : false;
    $requestArr = json_decode($requestJson);


    if(count($_POST)==0){
      die('<h1>mobile web service</h1></h3>no request made<h3>');
    }

    $dbConn = new dbConnect();	//create db connection
    $dbConn->dbConnection();


    $arr = array('test');

    $class = $_POST['class'];
    $method = $_POST['method'];
    $params = explode(',',$_POST['params']);

    if($class == 'MobileHandler'){

      include 'MobileHandler.php';
      $obj = new MobileHandler();

      $this->output($arr);

    } else {

      //direct DAO calls.
      if($class)
      echo $ROOT.$PHPFOLDER."DAO/".$class.'.php';
      include($ROOT.$PHPFOLDER."DAO/".$class.'.php');
      $obj = new $class($dbConn);

    }
          //call dao and method
      //$parsedRequest = method.
    $arr = call_user_func_array(array($obj, $method), $params);

    //call dao and method
    //$parsedRequest = method.

    $this->output($arr);

  }


  //send response.
  private function output($resultArr = array()){

    header('Content-type: application/json');
    echo json_encode(array('header' => get_object_vars($this->errorTO), 'result' => $resultArr));

  }


}
