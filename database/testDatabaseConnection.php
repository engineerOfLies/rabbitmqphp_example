<?php
$servername = "localhost";
$username = "local";
$password = "ChangeLater";
$database = "test_db";

// Create Connection
$connection = new mysqli($servername, $username, $password, $database);

// Check Connection
if($connection->connect_error) {
	die("Connection failed: " . $connection->connect_error);
}

//Query Username
$result = $connection->query("SELECT * FROM account");

//Print Query Result
if($result->num_rows > 0){
	while ($row = $result->fetch_assoc()) {
	printf("Username: %s, Password: %s\n",
		$row["username"],
		$row["password"]);
	}
}

?>
