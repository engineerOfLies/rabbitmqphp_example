#!/usr/bin/php
<?php

require_once __DIR__.'/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;



function doLogin($username,$password)
{

	include "mysqlconnect.php";
	$query = "SELECT * from accounts WHERE
		username = '$username'";
	$result = $mydb->query($query);
	if ($result->num_rows == 1){
		$results = $result->fetch_assoc();
		if (password_verify($password, $results['password'])){
			echo "\n\n";
			$token = bin2hex(openssl_random_pseudo_bytes(25));
			$date = date('Y/m/d h:i:s', time()+1800);
			$query = "UPDATE accounts
			 	SET sessionToken = '$token', expire = '$date'
			 	WHERE username = '$username'";
			 
			$result = $mydb->query($query);
			return array ("destination" => 'frontend', 'username' => $username, 'message' => "Account found", "token" => $token);
		}else{
			return array ("destination" => 'frontend', 'username' => NULL, 'message' => "Account not found");
		}
	}else{
		echo "\n\n";
		return array ("destination" => 'frontend', 'username' => NULL, 'message' => "Account not found");
	}	
}
function doRegister($username, $password, $email)
{
	include "mysqlconnect.php";
	$query = "SELECT * from accounts WHERE
		username = '$username'";
		
	$result = $mydb->query($query);
	if ($result->num_rows == 1){
		echo "\n\n";
		return array ("destination" => 'frontend', 'message' => "Failed");
	}else{
		//$token = bin2hex(openssl_random_pseudo_bytes(25));
		//$expire = null;
		$hashedP = password_hash($password, PASSWORD_DEFAULT);
		$query = "INSERT INTO accounts (username, password, email, sessionToken, expire)
			 VALUES ('$username', '$hashedP', '$email', NULL, NULL)";
		$result = $mydb->query($query);
		return array ("destination" => 'frontend', 'message' => "success");
	}	
	
}

function doLogout($username)
{
	include "mysqlconnect.php";
	$null = null;	
	$query = "UPDATE accounts
		 SET sessionToken = NULL, expire = NULL
		 WHERE username = '$username'";
	$result = $mydb->query($query);
	return array ("destination" => 'frontend', 'message' => "success");
	
}

function doAuth($username, $token){
	include "mysqlconnect.php";
	$currentTime = time();
	$query = "SELECT username ,sessionToken, expire from accounts
	WHERE username = '$username' AND sessionToken = '$token' AND expire < NOW() ";
	$result = $mydb->query($query);
	if ($result->num_rows >= 1){
		return array ("destination" => 'frontend', 'username' => $username, 'message' => "authed");
	}else{
		return array ("destination" => 'frontend', 'username' => $username, 'message' => "not authed");
	}
	
}



$connection = new AMQPStreamConnection('172.23.62.86', 5672, 'test', 'test', 'testHost');
$channel = $connection->channel();

$channel->queue_declare('dataQueue', false, true, false, false);

echo "\ntestRabbitMQServer BEGIN".PHP_EOL;

$callback = function ($msg) use ($channel) {
    echo "======================================\n";
    echo "recieved request\n";
    echo "--------------------------------------\n\n";
    $request = json_decode($msg->body, true);
    var_dump($request);
    $response = '';

    try {
        switch ($request['type']) {
            case "Login":
                $response = doLogin($request['username'], $request['password']);
                break;
            case "Register":
                $response = doRegister($request['username'], $request['password'], $request['email']);
                break;
            case "Logout":
            	$response = doLogout($request['username']);
            	break;
            case "Auth":
            	$response = doAuth($request['username'], $request['token']);
            	break;
            default:
                $response = ['success' => false, 'message' => "Request type not handled"];
                break;
        }
    } catch (Exception $e) {
        $response = ['success' => false, 'message' => $e->getMessage()];
    }
    
    
    $responseMsg = new AMQPMessage(
        json_encode($response),
        array('correlation_id' => $msg->get('correlation_id'))
    );

    $channel->basic_publish($responseMsg, '', $msg->get('reply_to'));
    echo "Sending Response\n";
};

$channel->basic_qos(null, 1, null);
$channel->basic_consume('dataQueue', '', false, true, false, false, $callback);

try {
    while (true) {
        $channel->wait();
    }
} catch (Exception $e) {
    echo 'An error occurred: ', $e->getMessage(), "\n";
    $channel->close();
    $connection->close();
}

$channel->close();
$connection->close();
