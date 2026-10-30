<?php

require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST"){

	$username = trim($_POST["username"] ?? "");
	$email = trim($_POST["email"] ?? "");
	$password = $_POST["password"] ?? "";
	$confirmPassword = $_POST["confirm_password"] ?? "";

	//valid email check
	if (!filter_var($email, FILTER_VALIDATE_EMAIL)){
		$message = "Invalid email";
	}

	//valid password check
	elseif (
		strlen($password) < 8 ||
		!preg_match('/[A-Z]/', $password) ||
		!preg_match('/[a-z]/', $password) ||
		!preg_match('/[0-9]/', $password)
	){
		$message = "Invalid password, min 8 characters, requires Uppercase and a Number";
	}

	//This will make sure the user doesn't leave anything blank and password matches
	elseif ($username === "" || $email === "" || $password === "") {
		$message = "All fields required.";
	} elseif ($password !== $confirmPassword) {
		$message = "Passwords do not match";

	} else {

		//Connect to rabbitmq 
		$client = new rabbitMQClient(
			"testRabbitMQ.ini",
			"testServer"
		);

		//Registration request
		$request = [
			"type" => "register",
			"username" => $username,
			"email" => $email,
			"password" => $password
		];

		//Send info to rabbitmq :)
		$response = $client->send_request($request);

		//Database listener says registration succeeded
		if (isset($response["success"]) && $response["success"] === true) {
			//literally checks if we get a success reponse and if that response is true
			//will prevent the program from running if both things aren't true
			//database itself needs to return if the user already exists :P therefore success => false
			header("Location: login.php");
			exit;
		} else {
			$message = $response["message"] ?? "Registration failed.";
		}
	}
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<title>Register</title>
</head>
<?php
//used to show any errors, like if passwords don't match it displays it. gotta use this for html :/
if ($message !== ""){
	echo "<p>" . htmlspecialchars($message) . "</p>";
}
?>

<!-- okay actual webpage time -->

<form method="POST" action="register.php">

	<label>Username:</label>
	<input type="text" name="username" required>
	
	<br><br>

	<label>Email:</label>
	<input type="email" name="email" required>

	<br><br>

	<label>Password:</label>
	<input type="password" name="password" required>

	<br><br>

	<label>Confirm Password:</label>
	<input type="password" name="confirm_password" required>
	
	<br><br>

	<button type="submit">Register</button>

</form>

<br>

<!-- back to the lobby -->

<a href="login.php">Back to Login</a>

</body>
</html>
