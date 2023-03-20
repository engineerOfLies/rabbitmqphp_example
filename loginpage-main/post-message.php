<?php
// get the form data
$name = $_POST['name'];
$message = $_POST['message'];
$timestamp = time();

// create an array of the message data
$messageData = [
  'name' => $name,
  'message' => $message,
  'timestamp' => $timestamp,
];

// load the existing messages from the file
$file = 'messages.json';
$messages = [];
if (file_exists($file)) {
  $messages = json_decode(file_get_contents($file), true);
}

// add the new message to the list
$messages[] = $messageData;

// save the updated messages to the file
file_put_contents($file, json_encode($messages));

// send the message data back to the client as JSON
header('Content-Type: application/json');
echo json_encode($messageData);


