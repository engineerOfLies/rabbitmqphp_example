#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

$username = $_POST['username'];
$password = $_POST['password'];

$request = array();
$request['type'] = "Login";
$request['username'] = $username;
$request['password'] = $password;

$client = new rabbitMQClient("testRabbitMQ.ini","testServer");
$response = $client->send_request($request);

if ($response[0] == 1)
//if ($response)
{
	header("Location: movie.html");
	//echo "Login response received";
}else{
	header("Location: login.html");
}
    
    
echo "Received response: ".PHP_EOL;
print_r($response);
echo "\n\n";


