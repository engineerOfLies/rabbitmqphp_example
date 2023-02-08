#!/usr/bin/php
<?php
require_once(__DIR__ . "/../lib/config/path.inc");
require_once(__DIR__ . "/../lib/rabbitMQLib.inc");
require_once(__DIR__ . "/../lib/config/errorLogger.ini");

//function errorHandler($request) {
    $file = fopen('errorLog.txt', 'a+');
    fwrite($file, "hell"."<br>");
    fclose($file);
 
    echo 'works'.PHP_EOL;
//}

$server = new rabbitMQServer(__DIR__."/../lib/config/rabbitMQ.ini", "rabbit");
echo "Server started...";
$server->process_requests("errorHandler");
?>