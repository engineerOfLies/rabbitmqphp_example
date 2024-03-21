#!/usr/bin/php
<?php
$mydb = new mysqli('127.0.0.1', 'Rukhsar', 'test', 'IT490');

if ($mydb->connect_errno)
{
    echo "Failed to connect to MySQL: " . $mydb->connect_error . PHP_EOL;
    exit(0);
}

echo "Successfully connected to MySQL database" . PHP_EOL;

// Query for Fitness_Pals table
$query_fitness_pals = "SELECT * FROM Fitness_Pals;";
$response_fitness_pals = $mydb->query($query_fitness_pals);

if ($mydb->errno != 0)
{
    echo "Failed to execute query for Fitness_Pals table:" . PHP_EOL;
    echo __FILE__ . ':' . __LINE__ . ": error: " . $mydb->error . PHP_EOL;
    exit(0);
}

// Query for Login_Credentials table
$query_login_credentials = "SELECT * FROM Login_Credentials;";
$response_login_credentials = $mydb->query($query_login_credentials);

if ($mydb->errno != 0)
{
    echo "Failed to execute query for Login_Credentials table:" . PHP_EOL;
    echo __FILE__ . ':' . __LINE__ . ": error: " . $mydb->error . PHP_EOL;
    exit(0);
}

?>

