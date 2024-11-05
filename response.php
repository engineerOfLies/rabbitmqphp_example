<?php

require_once('vendor/autoload.php');
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamConnection('10.241.109.75', 5672, 'test', 'test','testHost');
$channel = $connection->channel();

$channel->queue_declare('testQueue', true);
$channel->queue_declare('responseQueue', true);

$channel->exchange_declare('responseExchange', 'topic', true, true, false);
$channel->exchange_declare('testExchange', 'topic', true, true, false);


//echo " [*] Waiting for messages. To exit press CTRL+C\n";

$redirect = false;

$callback = function ($msg) {
    if ($msg) {
        echo 'Received response on responseQueue: ', $msg->body, "\n";

        $respMsg = json_decode($msg->body, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo "JSON decode error: " . json_last_error_msg() . "\n";
            return;
        }

        $signup = $respMsg['signup'] ?? 'No signup data';
        $message = $respMsg['message'] ?? 'No message data';
        echo "Signup: $signup, Message: $message\n";
//	header("Location: signup-success.html");
	if ($signup == 1) {
		$redirect = true;
		echo "redirect = true.\n";
	}
    } else {
        echo "No message received.\n";
    }
};
$channel->basic_consume('responseQueue','', false, true, false, false, $callback);

//header("Location: signup-success.html");

while ($channel->is_consuming()) {
    try {
	$channel->wait();
	if ($redirect) {
	//	header("Location: signup-success.html");
	    }
    } catch (\Throwable $exception) {
        echo "Error: " . $exception->getMessage() . "\n";
    }
}

$channel->close();
$connection->close();
