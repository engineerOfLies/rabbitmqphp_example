#!/usr/bin/php
<?php
require_once(__DIR__ . "/../lib/config/path.inc");
require_once(__DIR__ . "/../lib/rabbitMQLib.inc");
require_once(__DIR__ . "/../lib/config/rabbitMQ.ini");

function errorHandler($request) {
    $error_message = $request['error_message'];

    $error_log_file = __DIR__. "/error.log";
    $timestamp = date("Y-n-d H:i:s");
    $log_entry = "$timestamp: $error_message\n";
    file_put_contents($error_log_file. $log_entry, FILE_APPEND);

    return true;
}

$server = new rabbitMQServer(__DIR__."/../lib/config/rabbitMQ.ini", "rabbit");
echo "Server started...";
$server->process_requests("errorHandler");
?>