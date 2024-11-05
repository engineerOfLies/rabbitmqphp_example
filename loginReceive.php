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
  $email = $loginCreds['email'];
  $password = $loginCreds['password'];
  $password_hash = password_hash($password, PASSWORD_DEFAULT);
  $sessionID = $loginCreds['session_id'];
  $mysqli = require __DIR__ . "/database.php";

  //$sql = "INSERT INTO users (uname, email, password_hash)
  // VALUES (?, ?, ?)"; we can use an update
  $sql = sprintf("SELECT * FROM users
                    WHERE email = '%s'",
		    $mysqli->real_escape_string($email));
  $result = $mysqli->query($sql);
  $user = $result->fetch_assoc();

  if (password_verify($password_hash, $user["password_hash"])) {
      session_start();      
      session_regenerate_id();
            
      $_SESSION["user_id"] = $user["user_id"];
      // create a new AMQP message
      // send to the loginResponseExchange
     // header("Location: index.php");
     // exit;  
  }
};

$channel->basic_consume('loginSendQueue','', false, true, false, false, $callback);
try {
    $channel->consume();
} catch (\Throwable $exception) {
    echo $exception->getMessage(), "is this the error", "\n";
}


?>
