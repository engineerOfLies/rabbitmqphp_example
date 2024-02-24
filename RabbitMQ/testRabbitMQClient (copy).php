#!/usr/bin/php
<?php

require_once __DIR__.'/vendor/autoload.php';
require_once './vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');


    $connection = new AMQPStreamConnection('localhost', 5672, 'test', 'test', 'testHost');
    $channel = $connection->channel();

    // Declare a queue for the reply.
    list($callbackQueue, ,) = $channel->queue_declare("", false, false, true, false);

    // Generate a unique correlation ID for this request
    $corrId = uniqid();
    
    $prevmsg = array();
    $prevmsg['type'] = "Login";
    $prevmsg['username'] = $username;
    $prevmsg['password'] = $password;
   // $prevmsg['username'] = 'username test';
    //$prevmsg['password'] = 'password Test';
    $prevmsg['message'] = "to database";

    $msg = new AMQPMessage(
        json_encode($prevmsg),
        array(
            'correlation_id' => $corrId,
            'reply_to' => $callbackQueue,
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
        )
    );

    $channel->basic_publish($msg, '', 'dataQueue');

    echo "---------------------------------------\n";
    echo "Sending Message".PHP_EOL;


    // Wait for the response
    $response = null;
    $channel->basic_consume(
        $callbackQueue,
        '',
        false,
        true,
        false,
        false,
        function ($rep) use (&$response, $corrId) {
            if ($rep->get('correlation_id') == $corrId) {
                $response = json_decode($rep->body, true);
                echo "Response recieved\n";
                echo "---------------------------------------\n";
                var_dump($response);
            }
        }
    );
    // Wait for a response with the correct correlation ID
    while (!$response) {
        $channel->wait();
    }

    $channel->close();
    $connection->close();


?>
