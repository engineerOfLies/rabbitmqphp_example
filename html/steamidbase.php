<?php

require_once('../src/include/loginbase.inc'); 

$client = new rabbitMQClient("testRabbitMQ.ini","databaseServer"); 

$request = array();

// ENTER STEAM ID 
$steamId = $_POST['loginsteamid'];
$enter = $_POST['enterbutton'];
echo "line12";


if (!is_null($enter)) {
    session_start();
    $id = $_SESSION['id'];


    $request['type'] = "set_steam_link";
    $request['steamId'] = $steamId;
    $request['sessionId'] = $id;

    $response = $client->send_request($request);
    //$response = $client->publish($request);

    echo "client received username: {$user}".PHP_EOL;
    echo "client received password: {$pass}".PHP_EOL;

    session_commit();

    
    // Go to index page if successful
    if ($response == 1) {
        header('location:index.html');
        exit;
    }

    else {
      header('location:sendsteamid.html');
      exit;
  }

}


echo "client received response: ".PHP_EOL;

print_r($response);


echo "\n\n";

echo $argv[0]." END".PHP_EOL;

?>
