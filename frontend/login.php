<?php
session_start();

require_once(__DIR__."/../lib/path.inc");
require_once(__DIR__."/../lib/get_host_info.inc");
require_once(__DIR__."/../lib/rabbitMQLib.inc");

$client = new rabbitMQClient("testRabbitMQ.ini", "testServer");

if(!isset($_POST)) {
    $msg = "INVALID REQUEST";
    echo json_encode($msg);
    exit(0);
}

$request = $_POST;
$response = "Unsupported request type";
$arr = array();
$arr['type'] = $_POST["type"];
$arr['username'] = $_POST["username"];
$arr['password'] = $_POST["password"];
switch($request["type"]) {
    case "login":
        $response = "Logged in! RESPONSE FROM LOGIN.PHP ";
        break;
}
// echo json_encode($response);

$send = $client->send_request($arr);
exit(0);
?>
