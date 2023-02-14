<?php
session_start();

require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

$client = new rabbitMQClient("testRabbitMQ.ini","testServer");


$request["type"] = "login";
$request["email"] = $_POST["email"];
$request["password"] = $_POST["password"];;

$response = $client->send_request($request);

$res_obj = json_decode($response, true);

if(intval($res_obj["logged"]) == 1){
    $_SESSION["user"] = $res_obj;
    header("Location: home.php");
}


?>
