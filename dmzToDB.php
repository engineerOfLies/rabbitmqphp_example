#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

$client = new rabbitMQClient("testRabbitMQ.ini","testServer");

$filename = $argv[1];
$filePath = ("ApiFiles/" . $argv[1]);

//echo $argv[1];
echo filesize($filename);
$jsonFile = fopen($filename, "r") or die("Unable to open file!");
$jsonText = fread($jsonFile, filesize($filename));
$jsonObj = json_decode($jsonText);

$request = array();
$request['type'] = "apiData";
$request['data'] = $jsonObj;
$response = $client->send_request($request);
//$response = $client->publish($request);

echo "client received response: ".PHP_EOL;
print_r($response);
echo "\n\n";

echo $argv[0]." END".PHP_EOL;


