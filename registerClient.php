#!/usr/bin/php
<?php

require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

$client = new rabbitMQClient("testRabbitMQ.ini","testServer");

$request = array();
$request['type'] = "Login";
$request['username'] = "user";
$request['password'] = "password";
$request['email'] = "email";
//$request['message'] = $msg;

echo "Registration request... ".PHP_EOL;
print_r($request);
echo "\n";

$response = $client->send_request($request);

echo "Registration response:".PHP_EOL;
print_r($response);
echo "\n";

//Check if email already exists
//check if username already exists
?>

