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
                if($phparr["Response"] == "False") {
                    // echo "false";
                    return array("code" => 1, "message" => $phparr);
                }
                else {
                    $totalResults = $phparr['totalResults'];
                    $pages = ceil($totalResults / 10);
                    // echo "true";
                }
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
                if(!str_contains($omdbID, 'tt')) {
                    $fetchOMDBid = "https://api.themoviedb.org/3/movie/$omdbID?api_key=03c07d3da47475c86c83bcbcec8516d2&language=en-US";
                    $getContents = file_get_contents($fetchOMDBid);
                    $decode = json_decode($getContents, TRUE);
                    $omdbID = $decode['imdb_id'];
                }
                $imdbData = "http://www.omdbapi.com/?i=$omdbID&apikey=f3d054e8&plot=full";
                $json = file_get_contents($imdbData);
                $imdbArr = json_decode($json, TRUE);
                echo $imdbArr['Plot'];
                return array("code" => 0, "message" => $imdbArr);
            case "onload":
                $getPopular = "https://api.themoviedb.org/3/movie/popular?api_key=03c07d3da47475c86c83bcbcec8516d2&language=en-US&page=1";
                $json = file_get_contents($getPopular);
                $tmdbArr = json_decode($json, TRUE);
                return array("code" => 0, "message" => $tmdbArr);
    }

}


$server = new rabbitMQServer(__DIR__ . "/../lib/config/rabbitMQ.ini", "dmz");
echo "DMZ started..." . PHP_EOL;
$server->process_requests("add");

?>