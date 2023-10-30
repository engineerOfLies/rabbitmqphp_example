<?php

require_once('../src/include/loginbase.inc'); 

$client = new rabbitMQClient("testRabbitMQ.ini","databaseServer"); 

$request = array();

// ENTER STEAM ID 
$steamId = $_POST['loginsteamid'];
$enter = $_POST['enterbutton'];
$profile = $_GET['profilebutton'];
echo "line12";


if (!is_null($enter)) {
    session_start();
    $id = $_SESSION['id'];


    $request['type'] = "set_steam_link";
    $request['steamId'] = $steamId;
    $request['sessionId'] = $id;

    $response = $client->send_request($request);
    //$response = $client->publish($request);

    session_commit();

    
    // Go to index page if successful
    if ($response == 1) {
        header('location:index.html');
        exit;
    }

    // If not successful, pop up message then return to steamid page
    else {
      echo '<script>if(confirm("Incorrect Steam ID")){document.location.href="steamid.html"};</script>';
    }
  
  //  else {
  //   header('location:sendsteamid.html');
  //  exit;
  //}

}


if (!is_null($profile)) {
  session_start();
  $uid = $_SESSION['uid'];

  $request['type'] = "get_steam_profile";
  $request['userId'] = $uid;

  $response = $client->send_request($request);
  //$response = $client->publish($request);

  // session_commit();
  echo $response['steamName'];
  
  // Go to profile page if successful
  if ($response != 0 ) {
      header('location:profile.php');
      
      exit;
  }

  // If not successful, pop up message then return to steamid page
  else {
    echo '<script>if(confirm("User Steam ID not found")){document.location.href="steamid.html"};</script>';
  }

}

echo "client received response: ".PHP_EOL;

print_r($response);


echo "\n\n";

echo $argv[0]." END".PHP_EOL;

?>
