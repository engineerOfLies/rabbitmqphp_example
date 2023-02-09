#!/usr/bin/php
<?php
require_once(__DIR__ . "/../lib/config/path.inc");
require_once(__DIR__ . "/../lib/rabbitMQLib.inc");
require_once(__DIR__ . "/../lib/config/rabbitMQ.ini");

    function add($request) {
        $data = $request["data"];
        print_r ($data['title']); 
        $path = "http://www.omdbapi.com/?s={$data['title']}&apikey=f3d054e8";

        print_r ($path); 
        $json = file_get_contents($path); 
        $phparr = json_decode($json, TRUE);
        // $count = count($phparr["Search"]);
        // var_dump($count);
        // $titles = array(
        // );
        // for ($i = 0; $i < $count; $i++) {
        //     $titles[$i] = $phparr["Search"][$i];
        // }
        return array("code" => 0, "message" => $phparr);
   }


$server = new rabbitMQServer(__DIR__ . "/../lib/config/rabbitMQ.ini", "dmz");
echo "DMZ started..." . PHP_EOL;
$server->process_requests("add");

?>