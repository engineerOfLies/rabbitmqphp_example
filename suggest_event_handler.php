<?php
header ("Content-Type: application/json");
require_once ('path.inc');
require_once ('get_host_info.inc');
require_once('rabbitMQLib.inc');

// get the input from html
$data = json_decode(file_get_contents("php://input"), true);

//extracting token, event id and invitees from the data array
$session_token = $data['session_token'] ?? "";
$hangoutID = $data['hangout_id'] ?? "";
$eventID = $data['event_id'] ?? "";


// making sure token, event id and usernames are not empty
if ($session_token === "" || $hangoutID === "" || $eventID === ""){
    // dont let anyone create a hangout if the fields are empty!!!
    echo json_encode([
        "success" => false,
        "message" => "Missing event or hangout"
        ]);
    exit;
}


try {
// establishing communication to rabbitmq
    $client = new rabbitMQClient("testRabbitMQ.ini", "testServer");

//create hangout request
    $request = array();
    $request['type'] = "suggest_event";
    $request['session_token'] = $session_token;
    $request['hangout_id'] = $hangoutID;
    $request['event_id'] = $eventID;


//send request to rabbitmq and wait for a reply from listner
    $response = $client->send_request($request);
    // if request failed send a message  and set success to false and end loop
    if(!is_array($response)) {
        echo json_encode ([
            "success" => false,
            "message" => "Unknown error occurred"
             ]);
        exit;
             }
            
    // send response from db listner
    echo json_encode($response );
    
} catch (\Throwable $th) {
    // tell me if anything goes wrong
   
    error_log("Event suggestion was unsuccessful: " . $th->getMessage());
// any other error occur tell the user that it was unsuccessful
    echo json_encode([
        "success" => false,
        "message" => "Internal server error"
        ]);
}



?>
