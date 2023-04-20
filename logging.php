#!/usr/bin/php
<?php

require_once(__DIR__ . "/path.inc");
require_once(__DIR__ . "/rabbitMQLib.inc");

function errorLog($request) {
    if (isset($request["data"]["errorqueue"])) {
        // open file to add stuff
        $file = fopen('errorLogger.txt', 'a+');
        // messages to log
        $logMsg = $request["type"] . ":" . $request["data"]["errorqueue"] . "\r\n";
        // writes in file
        fwrite($file, "---------------------------------------------------" . PHP_EOL);
        fwrite($file, $logMsg);
        fwrite($file, "---------------------------------------------------".PHP_EOL);
        fclose($file);
    }
}

$server = new rabbitMQServer(__DIR__. "/testRabbitMQ.ini", "errorqueue");
echo "Server started...";
$server->process_requests("errorLogger");

