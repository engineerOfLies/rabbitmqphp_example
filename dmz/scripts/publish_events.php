<?php

require_once __DIR__ . '/../../path.inc';
require_once __DIR__ . '/../../get_host_info.inc';
require_once __DIR__ . '/../../rabbitMQLib.inc';

//This is in my(nick) local dir, dw
$file = "/opt/eventplanner/events.json";

if (!file_exists($file)){
	die("Error: events.json not fount\n");
}

//----------------------------------------------------

$json = file_get_contents($file);
$data = json_decode($json, true);

if (!is_array($data) || !isset($data["_embedded"]["events"])){
	die("Error: Invalid Ticketmaster data\n");
}

$events = [];

foreach ($data["_embedded"]["events"] as $event){
	if (empty($event["id"]) || empty($event["name"])){
		continue;
	}

	$events[] = [
		"event_id" => $event["id"],
		"name" => $event["name"],
		"date" => $event["dates"]["start"]["localDate"] ?? null,
		"venue" => $event["_embedded"]["venues"][0]["name"] ?? "",
		"city" => $event["_embedded"]["venues"][0]["city"]["name"] ?? "",
		"category" => $event["classifications"][0]["segment"]["name"] ?? "",
		"url" => $event["url"] ?? ""
		];
}

echo "Events prepared: " . count($events) . PHP_EOL;

//Now gonna stop this if there are no valid events

if (count($events) === 0){
	exit("No events to publish\n");
}

//Time to send it.
$client = new rabbitMQClient(
	__DIR__ . '/../../testRabbitMQ.ini',
	"testServer"
);

//Sending events in small batches
foreach (array_chunk($events, 5) as $batch){
	$request = [
		"type" => "sync_events",
		"events" => $batch
	];

	$reponse = $client->send_request($request);

	if (!is_array($response) || ($response["success"] ?? false) !== true){
		fwrite(STDERR, "Event batch synchronization failed\n");
		exit(1);
	}

	echo "Sent out batch of " . count($batch) . " events \n";
}

echo "Event synchronization completed\n";

//WHAT THIS SCRIPT DOES
//Opens my local events file
//Reads like 20 ticketmaster events
//extracts info (IDs, names, dates, venues, categories, and urls)
//Splits the events into batches of 5
//sends each to rabbitmq using type = sync_events
//it waits until a successful response before sending out the next batch (like a boss)

?>
