<?php
include 'eagaerBeavers-/auth2.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// Create a connection to RabbitMQ
$connection = new AMQPStreamConnection('172.28.222.209', 5672, 'admin', 'admin');
$channel = $connection->channel();

// Declare a queue for receiving messages
$channel->queue_declare('FE2BE', false, false, false, false);

echo "-={[Back-end] Waiting for Front-end messages. To exit press CTRL+C}=-\n";

// Define the callback function to process received messages
$callback = function ($message) use ($channel) {
    //echo "Received message from Front-end: " . $message->body . "\n";
	
    $data = json_decode($message->getBody(), true);

    // Get the data from the message body
    $username = $data['username'];
    $password = $data['password'];
    $confirm = $data['confirm'];
    $email = $data['email'];
    $firstname = $data['firstname'];
    $lastname = $data['lastname'];

    //Sanitize the username and password data
    $sanitizedUsername = filter_var($username, FILTER_SANITIZE_STRING);
    $sanitizedPassword = filter_var($password, FILTER_SANITIZE_STRING);
    $sanitizedConfirm = filter_var($confirm, FILTER_SANITIZE_STRING);
    $sanitizedEmail = filter_var($email, FILTER_SANITIZE_EMAIL);
    $sanitizedFirstname = filter_var($firstname, FILTER_SANITIZE_EMAIL);
    $sanitizedLastname = filter_var($lastname, FILTER_SANITIZE_EMAIL);

//Check for valid Email FORMAT (Not database entry)
    if (preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.(edu|[a-zA-Z]{2,})$/', $sanitizedEmail)) {
	   $isValidEmail = true; echo "[Valid Email ✓ ]\n";
    } else {
	   $isValidEmail = false; echo "[Invalid Email ✗ ]\n";
    }
//Check for valid Username FORMAT (Not database entry)
    if (preg_match('/^[a-zA-Z0-9_]+$/', $sanitizedUsername)) {
	   $isValidUsername = true; echo "[Valid Username ✓ ]\n";
    } else {
	   $isValidUsername = false; echo "[Invalid Username ✗ ]\n";
    }
//Check for valid Firstname
    if (preg_match('/^[a-zA-Z]+$/', $sanitizedFirstname)){
            $isValidFirstname = true; echo "[Valid Firstname ✓ ]\n";	
    } else {
	    $isValidFirstname = false; echo "[Invalid Firstname ✗ ]\n";
    }
//Check for a valid Lastname
    if (preg_match('/^[a-zA-Z]+$/', $sanitizedLastname)){
            $isValidLastname = true; echo "[Valid Lastname ✓ ]\n";
    } else {
            $isValidLastname = false; echo "[Invalid Lastname ✗ ]\n";
    }
//Check if password is using a valid format (At least 1 letter, at least 1 digit and at least 8 chars)
    if (preg_match('/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $sanitizedPassword)) {
	    $isValidPassword = true; echo "[Valid Password Format ✓ ]\n";
    } else {
    	    $isValidPassword = false; echo "[Invalid Password Format ✗ ]\n";
    }
//Check if the Two Passwords match:
    if ($sanitizedPassword == $sanitizedConfirm){
	    $isMatchingPassword = true; echo "[Passwords match ✓ ]\n";
    } else {
    	    $isMatchingPassword = false; echo "[Passwords do not match ✗ ]\n";
    }

//#################################[Registration Validation]##########################################	    
    if (!($isValidEmail && $isValidUsername && $isValidFirstname && $isValidLastname && $isValidPassword && $isMatchingPassword)) 
    {
        echo "\n[Register Unsuccessful ✗ ]\n";
          
	//Send a message in the JSON that will let the front end know there was an error. Include all validated variable true/false values
        $errorMessageBody = json_encode
        (
                [
			'isValidEmail' => $isValidEmail,
			'isValidUsername' => $isValidUsername,
			'isValidFirstname' => $isValidFirstname,
			'isValidLastname' => $isValidLastname,
			'isValidPassword' => $isValidPassword,
			'isMatchingPassword' => $isMatchingPassword
                ]
        );

        // Send a new error message to FrontEnd 
        $errorConnection = new AMQPStreamConnection('192.168.191.111', 5672, 'admin', 'admin');
        $errorChannel = $errorConnection->channel();
        $errorChannel->queue_declare('BE2FE', false, false, false, false);
        $errorMessage = new AMQPMessage($errorMessageBody);
        $errorChannel->basic_publish($errorMessage, '', 'BE2FE');
        $errorChannel->close();
	$errorConnection->close();

        echo "[Sent ERROR data to FrontEnd!: " . $errorMessage->body . "]\n\n";
    }
    else
      {
	echo "\n[Successfully Sent to Database ✓ ]\n";
	
	// Hash the password using bcrypt
    	$hashPassword = password_hash($sanitizedPassword, PASSWORD_BCRYPT); 

    	//Funnel everything back into the json. Also add the salt value.
    	$successMessageBody = json_encode
    	(	
        	[
	       		'username' => $sanitizedUsername,
               		'password' => $hashPassword,
              		'email' => $sanitizedEmail,
               		'firstname' => $sanitizedFirstname,
               		'lastname' => $sanitizedLastname
        	]
    	);

    	// Send a new message to the database
    	$successConnection = new AMQPStreamConnection('192.168.191.111', 5672, 'admin', 'admin');
    	$successChannel = $successConnection->channel();
    	$successChannel->queue_declare('BE2DB', false, false, false, false);
    	$successMessage = new AMQPMessage($successMessageBody);
    	$successChannel->basic_publish($successMessage, '', 'BE2DB');
    	$successChannel->close();
    	$successConnection->close();
    }
};

// Start consuming messages from the queue
$channel->basic_consume('FE2BE', '', false, true, false, false, $callback);

// Keep consuming messages until the channel is closed
while ($channel->is_open()) {
    $channel->wait();
}

// Close the channel and the connection
$channel->close();
$connection->close();
?>
