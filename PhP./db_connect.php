<?php
$db_name = "test_db";
$sname = "localhost";
$uname = "root";
$password = "";

$conn = mysqli_connect($sname, $uname, $password, $db_name);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
} else {
    echo "Connected successfully";
}
?>
