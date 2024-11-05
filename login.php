<?php

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/*
$is_invalid = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $mysqli = require __DIR__ . "/database.php";
    
    $sql = sprintf("SELECT * FROM users
                    WHERE email = '%s'",
                   $mysqli->real_escape_string($_POST["email"]));
    $result = $mysqli->query($sql);   
    $user = $result->fetch_assoc();
 
    if ($user) {
        
        if (password_verify($_POST["password"], $user["password_hash"])) {
            
            session_start();
            
            session_regenerate_id();
            
            $_SESSION["user_id"] = $user["user_id"];
            
            header("Location: index.php");
            exit;
        }
    }  
    $is_invalid = true;
}
*/

function sendToRabbit($email, $password, $sessionID) {

    $connection = new AMQPStreamConnection('10.241.109.75', 5672, 'test', 'test', 'testHost');
    $channel = $connection->channel();

    $channel->queue_declare('loginSendQueue', true);
    $channel->queue_declare('loginResponseQueue', true);

    $channel->exchange_declare('loginSendExchange', 'topic', true, true, false);
    $channel->exchange_declare('loginResponseExchange', 'topic', true, true, false);


    $data = ['email' => $email,'password' => $password, 'session_id' => $sessionID,];

    $msg = new AMQPMessage(json_encode($data));

    $channel->basic_publish($msg, 'loginSendExchange', 'user');

    $channel->close();
    $connection->close();
}

$email = $_POST['email'];
$password = $_POST['password'];
$sessionID = bin2hex(random_bytes(16));

sendToRabbit($email, $password, $sessionID);

$channel->close();
$connection->close();
?>



<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
</head>
<body>
    
    <h1>Login</h1>
    
    <?php if ($is_invalid): ?>
        <em>Invalid login</em>
    <?php endif; ?>
    
    <form method="post">
        <label for="email">email</label>
        <input type="email" name="email" id="email"
               value="<?= htmlspecialchars($_POST["email"] ?? "") ?>">
        
        <label for="password">Password</label>
        <input type="password" name="password" id="password">
        
        <button>Log in</button>
    </form>
    
</body>
</html>
