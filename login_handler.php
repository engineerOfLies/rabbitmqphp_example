<?php
require_once ('path.inc');
require_once ('get_host_info.inc');
require_once('rabbitMQLib.inc');

// read the input the login page sends
// file get contents reads raw data from body of  request
//&convert json into php array throug json decode
$data = json_decode(file_get_contents("php://input"), true);

//extracting login id and pwd from the data array
$username = $data['username'] ?? "";
$password = $data['password'] ?? "";

if ($username === "" || $password === "") {
    // dont let anyone log in if the fields are empty!!!
    echo json_encode(["success" => false]);
    exit;
}

// establishing communication to rabbitmq
$client = new rabbitMQClient("testRabbitMQ.ini", "testServer");

//login request
$request = array();
$request['type'] = "login";
$request['username'] = $username;
$request['password'] = $password;
//send request to rabbitmq and wait for a reply from listner
$response = $client->send_request($request);

// check if login was successful and includes a token
if (isset($response["success"]) && $response["success"] === true 
&& isset ($response['session_token']) )
    {
        //output login was successful and the token is sent to login page
        //turning php array back into json to send to login
        echo json_encode(
            [
            "success" => true, 
            "session_token" => $response["session_token"],
            "user_id" => $response["user_id"]
            ]
            );
        
    }
else
    {
    //output login was unsuccesful so failure reply sent with no token
    // turning php array back into json to send to login
    echo json_encode(["success" => false]);
}

?>
