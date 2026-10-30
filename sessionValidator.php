<?php
require_once ('path.inc');
require_once ('get_host_info.inc');
require_once('rabbitMQLib.inc');
//read sent session token 
//file get contents reads raw data from body of request
//convert json into php array throug json decode
$data = json_decode(file_get_contents("php://input"), true);

//extracting session token from the data
$type = $data['typeRequest'] ?? "";
$sessionToken = $data['session_token'] ?? "";

//makes sure sessionToken field is not empty
if ($sessionToken === "") {
    echo json_encode(["success" => false]);
    exit;
}

// establishing communication to rabbitmq
$client = new rabbitMQClient("testRabbitMQ.ini", "testServer");

//token validation request
$request = array();
if (!in_array($type, ["validate_session", "logout"], true)) {
    echo json_encode(["success" => false]);
    exit;
}
$request['type'] = $type;
$request['sessionToken'] = $sessionToken;
//send request to rabbitmq and wait for a reply from listner
$response = $client->send_request($request);

// check if token was valid
if (isset($response["success"]) && $response["success"] === true)
    {
        //session was validated successfully
        //turning php array back into json to send to back
        echo json_encode(
            [
            "success" => true, 
            ]
            );
        
    }
else
    {
    //session was not validated
    echo json_encode(["success" => false]);
}


?>
