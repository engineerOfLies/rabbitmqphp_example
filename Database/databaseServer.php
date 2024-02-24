#!/usr/bin/php
<?php

require_once __DIR__.'/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;



function doLogin($username,$password)
{
	include "mysqlconnect.php";
	$query = "SELECT * from accounts WHERE
		username = '$username' AND password = '$password'";
	$result = $mydb->query($query);
	if ($result->num_rows == 1){
		echo "\n\n";
		return array ("destination" => 'frontend', 'username' => $username, 'message' => "Account found");
	}else{
		echo "\n\n";
		return array ("destination" => 'frontend', 'username' => NULL, 'message' => "Account not found");
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
            case "register":
                $response = doRegister($request['username'], $request['password'], $request['email']);
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
