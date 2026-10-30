<?php
header ("Content-Type: application/json");
require_once ('path.inc');
require_once ('get_host_info.inc');
require_once('rabbitMQLib.inc');

// get the input from html
$data = json_decode(file_get_contents("php://input"), true);

//extracting token, event id and invitees from the data array
$session_token = $data['session_token'] ?? "";
$eventID = $data['event_id'] ?? "";
$usernames = $data['invitee_usernames'] ?? [];

// making sure token, event id and usernames are not empty
if ($session_token === "" || $eventID === "" || empty($usernames) || !is_array($usernames)){
    // dont let anyone create a hangout if the fields are empty!!!
    echo json_encode([
        "success" => false,
        "message" => "Missing event or username"
        ]);
    exit;
}


try {
// establishing communication to rabbitmq
    $client = new rabbitMQClient("testRabbitMQ.ini", "testServer");

//create hangout request
    $request = array();
    $request['type'] = "create_hangout";
    $request['session_token'] = $session_token;
    $request['event_id'] = $eventID;
    $request['invitee_usernames'] = $usernames;

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
   
    error_log("Creation of hangout was unsuccessful: " . $th->getMessage());
// any other error occur tell the user that it was unsuccessful
    echo json_encode([
        "success" => false,
        "message" => "Internal server error"
        ]);
}



?>
