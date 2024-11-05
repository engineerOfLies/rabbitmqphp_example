
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

if (headers_sent()) {
    echo "Headers have already been sent!";
} else {
    header("Location: signup-success.html");
    exit;
}
