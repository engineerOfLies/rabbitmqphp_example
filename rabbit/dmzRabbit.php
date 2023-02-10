#!/usr/bin/php
<?php
require_once(__DIR__ . "/../lib/config/path.inc");
require_once(__DIR__ . "/../lib/rabbitMQLib.inc");
require_once(__DIR__ . "/../lib/config/rabbitMQ.ini");

    function add($request) {
        $data = $request["data"];
        // print_r ($data['title']); 
        $path = "http://www.omdbapi.com/?s={$data['title']}&type=movie&apikey=f3d054e8";
        $json = file_get_contents($path); 
        $phparr = json_decode($json, TRUE);
        $allResults = array();
        $totalResults = $phparr['totalResults'];
        $pages = ceil($totalResults / 10);
        if($pages == 1) {
            return array("code" => 0, "message" => $phparr);
        }
        // this should be $pages but i dont feel like crashing my vm
        for($i = 1; $i <= 10; $i++) {
            $newPath = "http://www.omdbapi.com/?s={$data['title']}&type=movie&apikey=f3d054e8&page=$i";
            $jsonNew = file_get_contents($newPath);
            $newArr = json_decode($jsonNew, TRUE);
            array_push($allResults, $newArr);
        }
        return array("code" => 0, "message" => $allResults);
    }



        // for($i = 0; $i < $count; $i++) {

        // }
        // print_r ($phparr);




        // $count = count($phparr["Search"]);
        // var_dump($count);
        // $titles = array(
        // );
        // for ($i = 0; $i < $count; $i++) {
        //     $titles[$i] = $phparr["Search"][$i];
        // }

        // return array("code" => 0, "message" => $phparr);



$server = new rabbitMQServer(__DIR__ . "/../lib/config/rabbitMQ.ini", "dmz");
echo "DMZ started..." . PHP_EOL;
$server->process_requests("add");

?>