<?php

require_once('../src/include/loginbase.inc');

$client = new rabbitMQClient("testRabbitMQ.ini","databaseServer");



$request = array();

// LOG IN USER ------  
$user = $_POST['loginusername'];
$pass = $_POST['loginpassword'];
$login = $_POST['loginbutton'];
echo "{$login}";

if (!is_null($login)) {
    session_start();
    $id = session_create_id();
    session_id($id);
    print("id: ".$id);

    $_SESSION['type'] = "login";
    $_SESSION['username'] = $user;
    $_SESSION['id'] = $id;

    $request['type'] = "login";
    $request['username'] = $user;
    $request['password'] = $pass;
    $request['sessionId'] = $id;

    $response = $client->send_request($request);
    //$response = $client->publish($request);

    echo "client received username: {$user}".PHP_EOL;
    echo "client received password: {$pass}".PHP_EOL;

    session_commit();

    
    // Go to successful login page if login is successful
    if (($response['result']) == '1') {
        header('location:validlogin.html');
        exit;
    }

    // If login is not successful, pop up message then return to login page
    else {
     // echo '<script>alert("Incorrect Username or Password")</script>';
      echo '<script>if(confirm("Incorrect Username or Password")){document.location.href="home.html"};</script>';
      //header('location:home.html');
      //exit;
  }

}

echo "line 49 reached:";

// LOGOUT USER ---------
$logout = $_POST['logoutbutton'];
$request = array();

if (!is_null($logout)) {
    echo "line56:";
    session_start();
    $id = $_SESSION['id'];


    $request['type'] = 'logout';
    $request['sessionId'] = $id;
  
    $response = $client->send_request($request);
    //$response = $client->publish($request);
  
    
  if ($response == 1) {
    header('location:home.html');
    exit;
  }  
}



// REGISTER NEW USER ------ SIGN UP  
$registeruser = $_POST['registerusername'];
$registerpass = $_POST['registerpassword'];
$registerpassconf = $_POST['registerpasswordconfirm'];
$registeremail = $_POST['registeremail'];

$signup = $_POST['signupbutton'];

if (!is_null($signup)) {
    $request['type'] = "register";
    $request['username'] = $registeruser;
    $request['password'] = $registerpass;
    $request['email'] = $registeremail;
    $response = $client->send_request($request);

    echo "client received username: {$registeruser}".PHP_EOL;
    echo "client received password: {$registerpass}".PHP_EOL;

    if ($response == 1) {
      header('location:home.html');
      exit;
    }
}



echo "client received response: ".PHP_EOL;

print_r($response);


echo "\n\n";

echo $argv[0]." END".PHP_EOL;

?>
