<?php

// Load the AMQP library
require_once __DIR__ . '/vendor/autoload.php';

// Set up connection parameters
$connection = new \PhpAmqpLib\Connection\AMQPStreamConnection(
	'localhost',  // RabbitMQ host
	5672,     	// RabbitMQ port
	'test',  	// RabbitMQ username
	'test'   	// RabbitMQ password
);

// Create a channel
$channel = $connection->channel();

// Declare the queue to send messages to
$channel->queue_declare('registration_queue', false, true, false, false);

// Define the message to send
$message = json_encode(array(
	'name' => $_POST['name'],
	'username' => $_POST['username'],
	'password' => $_POST['password']
));

// Send the message to the queue
$channel->basic_publish(new \PhpAmqpLib\Message\AMQPMessage($message), '', 'registration_queue');

// Close the channel and connection
$channel->close();
$connection->close();



