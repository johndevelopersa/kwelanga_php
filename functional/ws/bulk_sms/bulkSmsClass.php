<?php

class sendSmsClass {
	
      function __construct() {

         global $dbConn;
         $this->dbConn = $dbConn;
//         $this->sndSms = new sendSmsClass($this->dbConn) ;         
      }	
// ********************************************************************************************************************************	
    public function sendSMS($smsNumber, $smsMessage) {
  	      global $ROOT; global $PHPFOLDER;
  	

          $basicAuth = 'Njc4RDFGNTVBNzJGNEM5OEFGMkQ2MkE2ODM2MUY4RUEtMDEtMTpIZzhIS3RwbmxZTm1ZT0I0Tmg4MG9NSVhnc0E2cA==';
          $messages = array(
                array('to'=>$smsNumber, 'body'=>$smsMessage)
          );

          // echo json_encode($messages);  

           $result = send_message( json_encode($messages), 'https://api.bulksms.com/v1/messages?auto-unicode=true&longMessageMaxParts=30', $basicAuth );

           if ($result['http_status'] != 201) {
                  print "Error sending: " . ($result['error'] ? $result['error'] : "HTTP status ".$result['http_status']."; Response was " .$result['server_response']);
           } else {
                // print "Response " . $result['server_response'];
                // Use json_decode($result['server_response']) to work with the response further
           }
     }      
// ********************************************************************************************************************************	
} 	 
   function send_message ( $post_body, $url, $basicAuth) {
          $ch = curl_init( );
          $headers = array(
          'Content-Type:application/json',
          'Authorization:Basic '. $basicAuth
          );
          curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
          curl_setopt ( $ch, CURLOPT_URL, $url );
          curl_setopt ( $ch, CURLOPT_POST, 1 );
          curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
          curl_setopt ( $ch, CURLOPT_POSTFIELDS, $post_body );
          // Allow cUrl functions 20 seconds to execute
          curl_setopt ( $ch, CURLOPT_TIMEOUT, 20 );
          // Wait 10 seconds while trying to connect
          curl_setopt ( $ch, CURLOPT_CONNECTTIMEOUT, 10 );
          curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, false );
          curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, false );
          $output = array();
          $output['server_response'] = curl_exec( $ch );
          $curl_info = curl_getinfo( $ch );
          $output['http_status'] = $curl_info[ 'http_code' ];
          $output['error'] = curl_error($ch);
          curl_close( $ch );
          return $output;
    } 
// ********************************************************************************************************************************	

?>       



  
