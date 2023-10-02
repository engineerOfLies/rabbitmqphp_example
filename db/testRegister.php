#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

$client = new rabbitMQClient("testRabbitMQ.ini","testServer");
if (isset($argv[2]))
{
  $un = $argv[1];
  $pw = $argv[2];
}
else
{
	echo "You must provide at least a username and password. If you'd like, provide an email after the password.".PHP_EOL;
	exit(0);
}
if (isset($argv[3]))
{
  $em = $argv[3];
}
else{
	$em = null;
}

$request = array();
$request['type'] = "register";
$request['username'] = $un;
$request['password'] = $pw;
$request['email'] = $em;
$response = $client->send_request($request);
//$response = $client->publish($request);

echo "client received response: ".PHP_EOL;
print_r($response);
echo "\n\n";

echo $argv[0]." END".PHP_EOL;

