#!/usr/bin/php
<?php

require_once __DIR__ . '/../get_host_info.inc';
require_once __DIR__ . '/../rabbitMQLib.inc';

$API = "b97a6678535cddda9458dcee24dddac2";
$URL = "http://ws.audioscrobbler.com/2.0/";


function lastfm($url, $api_key, $method, $extras=[]) { //call api
 $query = array_merge([
	 "method" => $method,
       	 "api_key" => $api_key,
       	 "format" => "json", ], $extras);

 $endpoint = $url . "?" . http_build_query($query); //creates url

 $data = file_get_contents($endpoint);

  return json_decode($data, true); //returns data
}

function many_artists($url, $api) { //gets info for 300 artists
 echo "Artists \n";

 $top_artists = lastfm($url, $api, "chart.getTopArtists", ["limit" => 1000]); //limit # of artists

 if (!$top_artists || !isset($top_artists["artists"]["artist"])) { //error checking
	echo "No Artists Data \n";
        return null;
    }

 $results = [];
 foreach ($top_artists["artists"]["artist"] as $artist) { //loop through each artist
	$name = $artist["name"];

	$artist_info = lastfm($url, $api, "artist.getInfo", ["artist" => $name]); //gets info

	if (!$artist_info || !isset($artist_info["artist"])) { //skips if no data
		echo "No data for: $name \n";
		continue;
	}

	$results[] = [
		"name" => $artist_info["artist"]["name"],
		"listeners" => (int)($artist_info["artist"]["stats"]["listeners"]),
		"play_count" => (int)($artist_info["artist"]["stats"]["playcount"]),
		"bio" => trim(strip_tags($artist_info["artist"]["bio"]["summary"])),
		"url" => $artist_info["artist"]["url"],
		"mbid" => $artist_info["artist"]["mbid"]
	];
 }
 echo "DONE \n";
 return [
	"type"       => "many_artists",
        "fetched_at" => date("Y-m-d"),
        "payload"    => $results
    ];
}


function many_tracks($url, $api) { //many tracks
 echo "Tracks \n";

 $top_tracks = lastfm($url, $api, "chart.getTopTracks",["limit" => 1000]) ; //chart.getTopTracks

 if (!$top_tracks || !isset($top_tracks["tracks"]["track"])) { //checks for error
	echo "No Track Data";
        return null;
    }

 $tracks = [];
 foreach ($top_tracks["tracks"]["track"] as $track) { //puts them into a list
	$tracks[] = [
		"track_name" => $track["name"],
		"artist" => $track["artist"]["name"],
		"play_count" => (int)($track["playcount"] ),
		"url" => $track["url"],
		"mbid" => $track["mbid"]
		];
    }

 echo "DONE \n";
 return [ //returns formatted list
	 "type" => "many_tracks",
         "fetched_at" => date("Y-m-d"),
         "payload" => $tracks
	];
}

function single_artist($url, $api, $name) { //gets the information on a certain artist
 echo "Artist Info \n";

 $artist = lastfm($url, $api, "artist.getInfo", ["artist" => $name]); //artst.getInfo

 if (!$artist || !isset($artist["artist"])) { //error checking
       	echo "Artist not found: $name\n";
        return null;
	}

	$artist[] = [
                "name" => $artist["artist"]["name"],
                "listeners" => (int)($artist["artist"]["stats"]["listeners"]),
                "play_count" => (int)($artist["artist"]["stats"]["playcount"]),
                "bio" => trim(strip_tags($artist["artist"]["bio"]["summary"])),
                "url" => $artist["artist"]["url"],
                "mbid" => $artist["artist"]["mbid"]
        ];

 echo "DONE \n";
 return [ //returns formatted info
	"type" => "single_artist",
        "fetched_at" => date("Y-m-d"),
        "payload" => $artist
	];
}

function requests($request) { //handles the requests
 global $API, $URL;

 if (!isset($request['type'])) { //error checking
        return ["status" => "error", "message" => "unsupported type"];

} elseif ($request["type"] == "many_artists") {
    	$data = many_artists($URL, $API);
    	return ["status" => "success", "data" => $data];

} elseif ($request["type"] == "many_tracks") {
	$data = many_tracks($URL,$API);
	return ["status" => "success", "data" => $data];

} elseif ($request["type"] == "single_artist") {
	$data = single_artist($URL,$API);
	return ["status" => "success", "data" => $data];

} else {
        echo "Unknown type: " . $request['type'] . "\n";
	}
}

$server = new rabbitMQServer(__DIR__ . '/../testRabbitMQ.ini', 'testServer3'); //connection to the broker
echo "API ready \n";
$server->process_requests('requests');
exit();
?>
