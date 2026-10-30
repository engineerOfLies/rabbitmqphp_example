<?php

header("Content-Type: application/json");

require_once __DIR__ . "/path.inc";
require_once __DIR__ . "/get_host_info.inc";
require_once __DIR__ . "/rabbitMQLib.inc";

//only allow POST request

if ($_SERVER["REQUEST_METHOD"] !== "POST"){
	http_response_code(405);

	echo json_encode([
		"success" => false,
		"message" => "POST method required"
	]);
	exit;

}

$data = json_decode(file_get_contents("php://input"), true);

if (!is_array($data)){
	http_response_code(400);

	echo json_encode([
		"success" => false,
		"message" => "Invalid JSON request"
	]);
	exit;
}

$type = $data["type"] ?? "";
$sessionToken = $data["session_token"] ?? "";

//im only going to allow these 4 functions i made on db_listener
$allowedTypes = [
	"send_friend_request",
	"get_friend_requests",
	"respond_friend_request",
	"get_friends"
];

if (!is_string($type) || !in_array($type, $allowedTypes, true)){
	http_response_code(400);

	echo json_encode([
		"success" => false,
		"message" => "Invalid request type"
	]);
	exit;
}

//validate session token
if (!is_string($sessionToken) || trim($sessionToken) === ""){
	http_response_code(400);

	echo json_encode([
		"success" => false,
		"message" => "Session token required"
	]);
	exit;
}

$request = [
	"type" => $type,
	"session_token" => $sessionToken
];

switch ($type){
	case "send_friend_request":
		$username = $data["friend_username"] ?? "";

		if (!is_string($username) || trim($username) === ""){
			http_response_code(400);

			echo json_encode([
				"success" => false,
				"message" => "Friend username is required"
			]);
			exit;
		}

		$request["friend_username"] = trim($username);
		break;

	case "respond_friend_request":
		$friendID = filter_var($data["friend_id"] ?? null, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);

		$action = $data["action"] ?? "";

		if ($friendID === false || $friendID === null || !is_string($action) || !in_array($action, ["accept", "decline"], true)){
			http_response_code(400);

			echo json_encode([
				"success" => false,
				"message" => "Valid friendID and action required"
			]);
			exit;
		}

		$request["friend_id"] = $friendID;
		$request["action"] = $action;
		break;
}

try{
	$client = new rabbitMQClient(
		__DIR__ . "/testRabbitMQ.ini",
		"testServer"
	);

	$response = $client->send_request($request);

	if (!is_array($response)){
		http_response_code(502);

		echo json_encode([
			"success" => false,
			"message" => "Invalid response from backend"
		]);

		exit;
	}

	if (($response["success"] ?? false) !== true){
		http_response_code(400);
	}

	echo json_encode($response);
} catch (Throwable $e){
	error_log("Friends handler error: " . $e->getMessage());

	http_response_code(502);

	echo json_encode([
		"success" => false,
		"message" => "Unable to communicate with backend"
	]);
}

?>
