
<?php
require_once('vendor/autoload.php');
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

if (empty($_POST["name"])) {
    die("Name is required");
}

if ( ! filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
    die("Valid email is required");
}

if (strlen($_POST["password"]) < 8) {
    die("Password must be at least 8 characters");
}

if ( ! preg_match("/[a-z]/i", $_POST["password"])) {
    die("Password must contain at least one letter");
}

if ( ! preg_match("/[0-9]/", $_POST["password"])) {
    die("Password must contain at least one number");
}

if ($_POST["password"] !== $_POST["password_confirmation"]) {
    die("Passwords must match");
}

//$password_hash = password_hash($_POST["password"], PASSWORD_DEFAULT);
$password = $_POST['password'];
$email = $_POST["email"];
$name = $_POST["name"];

$connection = new AMQPStreamConnection('10.241.109.75', 5672, 'test', 'test', 'testHost');
$channel = $connection->channel();


$channel->queue_declare('testQueue', true);
$channel->queue_declare('responseQueue', true);

$channel->exchange_declare('testExchange', 'topic', true, true, false);

$registerCreds = json_encode(['name'=> $name, 'email' => $email, 'password' => $password]);

$msg = new AMQPMessage($registerCreds);

$channel->basic_publish($msg, 'testExchange', 'user');
echo "sent message";
try {
    $channel->consume();
} catch (\Throwable $exception) {
    echo $exception->getMessage(), "is this the error", "\n";
}

$channel->close();
$connection->close();


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
	  $response = ["signup" => true, "message" => 'success']; header("Location: signup-success.html");
	 exit(); 
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


?>
