<?php
    function connect_to_db() {
        $servername = "localhost";
        $username = "itadmin";
        $password = "***";
        $dbname = "IT490";

        $conn = new mysqli($servername, $username, $password, $dbname);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        return $conn;
    }
    function close_db($conn) {
        $conn->close();
    }
?>