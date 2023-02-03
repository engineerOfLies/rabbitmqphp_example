<?php
    $servername = "localhost";
    $username = "it490";
    $password = "password";
    $db_name = "testDB";

    $conn = mysqli_connect($servername, $username, $password, $db_name);

    if (mysqli_connect_errno()) {
        echo "Failed to connect";
    }
    else {
        echo "DB is working";
    }
?>