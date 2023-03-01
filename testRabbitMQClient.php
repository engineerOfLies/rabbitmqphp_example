#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

$name = "Raiyan";
$username = "bees";
$password = "123";

$request = array();
$request['type'] = "Registration";
$request['name'] = $name;
$request['username'] = $username;
$request['password'] = $password;

/*$request = array();
$request['type'] = "Login";
$request['username'] = $username;
$request['password'] = $password;*/

$client = new rabbitMQClient("testRabbitMQ.ini","testServer");
$response = $client->send_request($request);

echo "Received response: ".PHP_EOL;
print_r($response);
echo "\n\n";
