<?php
// set up basic SSL connection
$ftp_server = "41.203.166.122";
$ftp_conn = ftp_ssl_connect($ftp_server, '22') or die("Could not connect to $ftp_server");

$ftp_username = 'HoneyFields-t';
$ftp_userpass = 'T3st2018';

// login
$login = ftp_login($ftp_conn, $ftp_username, $ftp_userpass);

// then do something...

// close SSL connection
ftp_close($ftp_conn);


//
// Server: 41.203.166.122, Port 22 as per SFTP default
 
// Rsa2 key fingerprint
// ssh-rsa 2048 28:84:60:bd:8a:4c:f1:0f:f6:91:ab:fb:08:cf:9e:8b

// Login Details:

// Test environment
// HoneyFields-t
// T3st2018
?>