#!/usr/bin/php
<?php
require_once(__DIR__."/../lib/config/path.inc");
require_once(__DIR__."/../lib/rabbitMQLib.inc");
include_once(__DIR__."/database.ini");
$db_info = parse_ini_file("database.ini", true);
$db_info = $db_info["database"];
$db = mysqli_connect($db_info["HOST_NAME"], $db_info["USERNAME"], $db_info["PASSWORD"], $db_info["DATABASE"]);


if(mysqli_connect_errno() !== 0) {
    die("MYSQL Connection error: " . mysqli_connect_error());
}


$stmt = mysqli_prepare($db, "SELECT * FROM users");
$stmt->execute();
$result = $stmt->get_result();
var_dump($result->fetch_assoc());

?>