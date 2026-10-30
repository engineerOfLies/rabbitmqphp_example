<?php

header("Content-Type: application/json");

//For joseph to load rabbitmq dependencies

require_once __DIR__ . "/path.inc";
require_once __DIR__ . "/get_host_info.inc";
require_once __DIR__ . "/rabbitMQLib.inc";

//only looking for POST requests

if ($_SERVER["REQUEST_METHOD"] !== "POST"){
	http_response_code(405);

	echo json_encode([
		"success" => false,
		"message" => "POST method required"
	]);

	exit;
}

$data = json_decode(file_get_contents("php://input"), true);

//read JSON sent
if (!is_array($data)){
	http_response_code(400);

	echo json_encode([
		"success" => false,
		"message" => "Invalid JSON request"
	]);

	exit;
}

//retrieve the sesson token and event id
$sessionToken = $data["session_token"] ?? "";
$eventID = $data["event_id"] ?? "";

if (!is_string($sessionToken) || $sessionToken === "" || !is_string($eventID) || trim($eventID) === ""){
	http_response_code(400);

	echo json_encode([
		"success" => false,
		"message" => "Session token and event ID requred"
	]);

	exit;
}

try{
	$client = new rabbitMQClient(
		__DIR__ . "/testRabbitMQ.ini",
		"testServer"
	);

	$request = [
		"type" => "save_pick",
		"session_token" => $sessionToken,
		"event_id" => trim($eventID)
	];

	$response = $client->send_request($request);

	if (!is_array($response)){
		http_response_code(502);

		echo json_encode([
			"success" => false,
			"message" => "Something wrong with the backend..."
		]);

		exit;
	}

	if (($response["success"] ?? false) !== true){
		http_response_code(400);
	}

	echo json_encode($response);
} catch (Throwable $e){
	error_log("Save pick handler error: " . $e->getMessage());

	http_response_code(502);

	echo json_encode([
		"success" => false,
		"message" => "Unable to communicate with backend"
	]);
}
?>
