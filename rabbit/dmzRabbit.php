#!/usr/bin/php
<?php
require_once(__DIR__ . "/../lib/config/path.inc");
require_once(__DIR__ . "/../lib/rabbitMQLib.inc");
require_once(__DIR__ . "/../lib/config/rabbitMQ.ini");

    function add($request) {
        $data = $request["data"];
        print_r ($data['id']);
        $path = "http://www.omdbapi.com/?i={$data['id']}&apikey=f3d054e8";
        print_r ($path);
        $json = file_get_contents($path);
        return array("code" => 0, "message" => json_decode($json, TRUE));
    }


$server = new rabbitMQServer(__DIR__ . "/../lib/config/rabbitMQ.ini", "dmz");
echo "DMZ started..." . PHP_EOL;
$server->process_requests("add");

?>