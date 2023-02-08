<?php
require_once(__DIR__ . "/../lib/config/path.inc");
require_once(__DIR__ . "/../lib/config/get_host_info.inc");
require_once(__DIR__ . "/../lib/rabbitMQLib.inc");
function send(array $req, string $configName)
{
    $rabbitMQClient = new rabbitMQClient(__DIR__ . "/config/rabbitMQ.ini", "rabbit");
    $res = $rabbitMQClient->send_request($req);
    return $res;


}
function sendEvent(array $req, string $configName)
{
    $rabbitMQClient = new rabbitMQClient(__DIR__ . "/config/rabbitMQ.ini", "rabbit");
    $res = $rabbitMQClient->send_request($req);
    return $res;


}
?>