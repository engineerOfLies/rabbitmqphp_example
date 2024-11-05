<?php

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamConnection('10.241.109.75', 5672, 'test', 'test','testHost');
$channel = $connection->channel();

$channel->queue_declare('testQueue', true);
$channel->queue_declare('responseQueue', true);

$channel->exchange_declare('testExchange', 'topic', true, true, false);
$channel->exchange_declare('responseExchange', 'topic', true, true, false);

echo " [*] Waiting for messages. To exit press CTRL+C\n";

$callback = function ($msg) use ($channel) {

  echo ' [x] Received ', $msg->body, "\n";
  
  $registerCreds = json_decode($msg->body, true); 
  $name = $registerCreds['name'];
  $email = $registerCreds['email'];
  $password = $registerCreds['password'];
  $password_hash = password_hash($password, PASSWORD_DEFAULT);
  $mysqli = require __DIR__ . "/database.php";

  $sql = "INSERT INTO users (uname, email, password_hash)
        VALUES (?, ?, ?)";

  $stmt = $mysqli->stmt_init(); echo 'stmt init', "\n";

  if ( ! $stmt->prepare($sql)) {
    die("SQL error: " . $mysqli->error);
  }

  $stmt->bind_param("sss", $name, $email, $password_hash); echo 'stmt bindedparams',"\n";
  $response = ["signup" => false, "message" => 'no success'];

  if ($stmt->execute()) {
	  $response = ["signup" => true, "message" => 'success']; echo 'next line exit', "\n";
	 // exit 
  } else {
	  if ($mysqli->errno === 1062) {
	    $response = json_encode(["signup"=> false, "message" => 'email taken']);
       	   // die("email already taken");
	  } else {
	    $response = json_encode(["signup"=> false, "message" => $mysqli->errno]);
           // die($mysqli->errno);
           }
  }
  echo 'packing json msg';
 $respMsg = json_encode($response);
 $msg = new AMQPMessage($respMsg);
 $channel->basic_publish($msg, 'responseExchange', 'user');
 echo 'did the json send',"\n", $respMsg, "\n";
};

$channel->basic_consume('testQueue','', false, true, false, false, $callback);

try {
    $channel->consume();
} catch (\Throwable $exception) {
    echo $exception->getMessage(), "is this the error", "\n";
}

$channel->close();
$connection->close();
