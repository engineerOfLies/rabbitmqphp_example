#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

$name = $_POST['name'];
$username = $_POST['username'];
$password = $_POST['password'];

$request = array();
$request['type'] = "Registration";
$request['name'] = $name;
$request['username'] = $username;
$request['password'] = $password;

$client = new rabbitMQClient("testRabbitMQ.ini","testServer");
$response = $client->send_request($request);

if ($response)
{
	header("Location: loginn.html");
	//echo "Login response received";
}
    
    
echo "Received response: ".PHP_EOL;
print_r($response);
echo "\n\n";


