<?php
header ("Content-Type: application/json");
require_once ('path.inc');
require_once ('get_host_info.inc');
require_once('rabbitMQLib.inc');

// get the input from html
$data = json_decode(file_get_contents("php://input"), true);

// extracting token from the data array
$session_token = $data['session_token'] ?? "";

// making sure token is not empty
if ($session_token === ""){
    // dont let anyone get hangouts if the token is empty!!!
    echo json_encode([
        "success" => false,
        "message" => "Missing session token"
        ]);
    exit;
}

try {
    // establishing communication to rabbitmq
    $client = new rabbitMQClient("testRabbitMQ.ini", "testServer");

    // get hangouts request
    $request = array();
    $request['type'] = "get_hangouts";
    $request['session_token'] = $session_token;

    // send request to rabbitmq and wait for a reply from listener
    $response = $client->send_request($request);

    // if request failed send a message and end
    if(!is_array($response)) {
        echo json_encode ([
            "success" => false,
            "message" => "Unknown error occurred"
            ]);
        exit;
    }

    // send response from db listener
    echo json_encode($response);

} catch (\Throwable $th) {
    // tell me if anything goes wrong
    error_log("Getting hangouts was unsuccessful: " . $th->getMessage());

    // any other error occur tell the user that it was unsuccessful
    echo json_encode([
        "success" => false,
        "message" => "Internal server error"
        ]);
}

?>