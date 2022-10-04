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

//Query Credentials
$testUsername = "Devin Callahan";
$testPassword = "1230";
$query = "SELECT * FROM account WHERE username = \"".$testUsername."\" AND password = \"".$testPassword."\" LIMIT 1";

$result = $connection->query($query);

//Check Credentials
$numRows = mysqli_num_rows($result);

if($numRows != 0){
	echo "Now generate a session token!\n";
}else{
	echo "Tell the website that authentication has failed!\n";
}

?>
