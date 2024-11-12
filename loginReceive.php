<?php
require_once('vendor/autoload.php');
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamConnection('10.241.109.75', 5672, 'test', 'test','testHost');
$channel = $connection->channel();

$channel->queue_declare('loginSendQueue', true);
$channel->queue_declare('loginResponseQueue', true);

$channel->exchange_declare('loginSendExchange', 'topic', true, true, false);
$channel->exchange_declare('loginResponseExchange', 'topic', true, true, false);

$callback = function ($msg) use ($channel) {

  echo ' [x] Received ', $msg->body, "\n";

  $loginCreds = json_decode($msg->body, true);
  //echo $loginCreds['password'], " login password <--";
  $email = $loginCreds['email'];
  $loginPassword = $loginCreds['password'];
  $mysqli = require __DIR__ . "/database.php";

  //$sql = "INSERT INTO users (uname, email, password_hash)
  // VALUES (?, ?, ?)"; we can use an update
  $sql = sprintf("SELECT * FROM users
                    WHERE email = '%s'",
		    $mysqli->real_escape_string($email));
  $result = $mysqli->query($sql);
  $user = $result->fetch_assoc();

  if (password_verify($loginPassword, $user["password_hash"])) {       
      $response = ["login" => true, "message" => "login successful"];
  } else {
      $response = ["login" => false, "message" => "login failed"];
  }
  $respMsg = json_encode($response);
  $msg = new AMQPMessage($respMsg);
  $channel->basic_publish($msg, 'loginResponseExchange', 'user');
};

$channel->basic_consume('loginSendQueue','', false, true, false, false, $callback);
try {
    $channel->consume();
} catch (\Throwable $exception) {
    echo $exception->getMessage(), "is this the error", "\n";
}

$channel->close();
$connection->close();

?>
