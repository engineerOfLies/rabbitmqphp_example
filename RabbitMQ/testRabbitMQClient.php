#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

$client = new rabbitMQClient("testRabbitMQ.ini","dataServer");
//$username = $argv[1];
//$password = $argv[2];

$request = array();
$request['type'] = "Login";
$request['username'] = $username;
$request['password'] = $password;
$request['message'] = "to database";

$response = $client->send_request($request);

echo "client recieved response: ".PHP_EOL;
//print_r($response);
echo "\n\n";

//$response = $client->($request);




