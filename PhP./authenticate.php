<?php
session_start();
//use path;
//use get_host_info;
//use rabbitMQLib;
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = filter_input(INPUT_POST, 'username');
    $password = filter_input(INPUT_POST, 'password');

    // Hash the password for security
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Create a session ID
    $session_id = session_id();
    $_SESSION['session_id'] = $session_id;
    $_SESSION['username'] = $username;

    // Connect to RabbitMQ server
    $client = new rabbitMQClient("testRabbitMQ.ini", "testServer");

    // Prepare login request
    $request = array();
    $request['type'] = "login";
    $request['username'] = $username;
    $request['password'] = $hashed_password;
    $request['session_id'] = $session_id;

    // Send login request to RabbitMQ server
    $response = $client->send_request($request);

    // Process login response
    if ($response == 1) {
        // Login successful, redirect to homepage
        header("Location: home.php");
        exit();
    } else {
        // Invalid login, display error message
        $error_message = "Invalid login. Please try again.";
    }
}

// If not a POST request or login failed, include login form
//use login;
include('login.php');

// Optionally, display error message
if (isset($error_message)) {
    echo "<p>$error_message</p>";
}
?>
