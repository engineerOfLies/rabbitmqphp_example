#!/usr/bin/php
<?php
require_once(__DIR__ . "/../lib/config/path.inc");
require_once(__DIR__ . "/../lib/rabbitMQLib.inc");
require_once(__DIR__ . "/../lib/config/rabbitMQ.ini");
    // NEED TO CHECK IF NO RESULTS ARE FOUND 
    // viewing movie page check if no results generated 
    function add($request) {
        $data = $request["data"];
        switch ($request['type']) {
            case "search":
                $path = "http://www.omdbapi.com/?s={$data['title']}&type=movie&apikey=f3d054e8";
                $json = file_get_contents($path); 
                $phparr = json_decode($json, TRUE);
                $allResults = array();
                // print_r ($phparr['Search'][0]['imdbID']);
                $totalResults = $phparr['totalResults'];
                $pages = ceil($totalResults / 10);
                if($pages == 1) {
                    return array("code" => 0, "message" => $phparr);
                }
                // change the amount of times it loops from $pages to some small number so u arent waiting 100 years for a response
                for($i = 1; $i <= 2; $i++) {
                    $newPath = "http://www.omdbapi.com/?s={$data['title']}&type=movie&apikey=f3d054e8&page=$i";
                    $jsonNew = file_get_contents($newPath);
                    $newArr = json_decode($jsonNew, TRUE);
                    array_push($allResults, $newArr);
                }
                return array("code" => 0, "message" => $allResults);
            case "fetch":
                $omdbID = $data['id'];
                $imdbData = "http://www.omdbapi.com/?i=$omdbID&apikey=f3d054e8&plot=full";
                $json = file_get_contents($imdbData);
                $imdbArr = json_decode($json, TRUE);
                echo $imdbArr['Plot'];
                // incase i forget
                // grab id -> fetch imdb data -> send shit back -> creates new page -> lets user view
                if($stmt->errno !== 0) {
                    send(array("data" => array("error" => $stmt->error)), "error");
                }
                return array("code" => 0, "message" => $imdbArr);
    }

}


$server = new rabbitMQServer(__DIR__ . "/../lib/config/rabbitMQ.ini", "dmz");
echo "DMZ started..." . PHP_EOL;
$server->process_requests("add");

?>