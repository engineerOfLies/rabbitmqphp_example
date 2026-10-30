<?php

// set the response type to JSON
header("Content-Type: application/json");

// include the required files
require_once ('path.inc');
require_once ('get_host_info.inc');
require_once('rabbitMQLib.inc');

// read the request body
$data = json_decode(file_get_contents('php://input'), true);

// get the session token
$session_token = $data['session_token'] ?? "";

// get the hangout id
$hangoutID = $data['hangout_id'] ?? "";

// check if the required information is missing
if ($session_token === "" || $hangoutID === ""){
    echo json_encode([
        "success" => false,
        "message" => "Missing session token or hangout"
    ]);
    exit;
}

try{

    // create the RabbitMQ client
    $client = new rabbitMQClient("testRabbitMQ.ini", "testServer");

    // create the request
    $request = [];
    $request['type'] = "get_suggestions";
    $request['session_token'] = $session_token;
    $request['hangout_id'] = $hangoutID;

    // send the request to RabbitMQ
    $response = $client->send_request($request);

    // check if the response is valid
    if (!is_array($response)){
        echo json_encode([
            "success" => false,
            "message" => "Unable to retrieve suggestions"
        ]);
        exit;
    }

    // return the response
    echo json_encode($response);

} catch (Throwable $e){

    // log the real error
    error_log("Getting suggestions was unsuccessful: " . $e->getMessage());

    // return a generic error message
    echo json_encode([
        "success" => false,
        "message" => "Internal server error"
    ]);
}
?>