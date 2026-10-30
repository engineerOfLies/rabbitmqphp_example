<?php
	//literal simple db connection code
	// i used a 13 year old stackoverflow post for referencew, YOLO.
	$host = getenv("DB_HOST") ?: "localhost";
	$username = getenv("DB_USER") ?: "webapp";
	$password = getenv("DB_PASSWORD");
	$database = getenv("DB_NAME") ?: "webdb";
	$port = (int) (getenv("DB_PORT") ?: 3306);

	if ($password === false){
		throw new RuntimeException("DB_PASSWORD env var is not set.");
	}

	try{
		// allow mysqli to throw exxceptions on erroe
		mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
		// create connection to database
		$connection = new mysqli($host, $username, $password, $database, $port);
		$connection->set_charset("utf8mb4");
	} catch (mysqli_sql_exception $e){
		// log errors if connection fails 
		error_log("MySQL connection failed: " . $e->getMessage());

		throw new RuntimeException("Unable to connect to database", 0, $e);
	}

?>
