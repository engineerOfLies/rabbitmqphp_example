#!/usr/bin/php
<?php
require_once(__DIR__ . "/../lib/config/path.inc");
require_once(__DIR__ . "/../lib/rabbitMQLib.inc");

 function errorHandler($request) {
    if ($request["data"]["error"]) {


        $file = fopen('errorLog.txt', 'a+');
        $logMsg = $request["type"] . ":" . $request["data"]["error"] . "\r\n";
        var_dump($request);
        fwrite($file, "---------------------------------------------------" . PHP_EOL);
        fwrite($file, $logMsg);
        // fwrite($file, "---------------------------------------------------".PHP_EOL);
        fclose($file);
    }
 
    // echo 'works'.PHP_EOL;
}

$server = new rabbitMQServer(__DIR__."/../lib/config/rabbitMQ.ini", "error");
echo "Server started...";
$server->process_requests("errorHandler");
?>