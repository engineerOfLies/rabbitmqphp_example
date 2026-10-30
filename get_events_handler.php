<?php
header("Content-Type: application/json");

require_once __DIR__ . "/path.inc";
require_once __DIR__ . "/get_host_info.inc";
require_once __DIR__ . "/rabbitMQLib.inc";

if ($_SERVER["REQUEST_METHOD"] !== "GET"){
	http_response_code(405);

	echo json_encode([
	"success" => false,
	"message" => "GET method required"
	]);

	exit;
}

try{
	$client = new rabbitMQClient(
		__DIR__ . "/testRabbitMQ.ini",
		"testServer"
	);

	$request = [
		"type" => "get_events"
	];

	$response = $client->send_request($request);

	if (!is_array($response)){
		http_response_code(502);

		echo json_encode([
			"success" => false,
			"message" => "Something wrong with backend"
		]);

		exit;
	}

	if (($response["success"] ?? false) !== true){
		http_response_code(502);
	}

	echo json_encode($response);
} catch (Throwable $e){
	error_log("Get events error: " . $e->getMessage());

	http_response_code(502);

	echo json_encode([
		"success" => false,
		"message" => "Unable to retrieve events"
	]);
}

?>
