#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

$name = $_POST['name'];
$username = $_POST['username'];
$password = $_POST['password'];

//Hashed Username & Password
$salted = "98235iuhf34uhf34ohu23f".$password."30qt83th98ch98ru238r23";
$peppered = "b87i86v7dc69y8ppn8".$salted."n9887v65c45x3wxertdtfybnuimoipuic65434x";
$hashed = hash('sha512', $peppered);

$request = array();
$request['type'] = "Registration";
$request['name'] = $name;
$request['username'] = $username;
$request['password'] = $hashed;

$client = new rabbitMQClient("testRabbitMQ.ini","testServer");
$response = $client->send_request($request);

if ($response)
{
    header("Location: loginn.html");
    //echo "Login response received";
}
    
    
echo "Client Registration Complete".PHP_EOL;
print_r($response);
echo "\n\n";
