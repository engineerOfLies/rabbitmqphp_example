#!/usr/bin/php
<?php
require_once(__DIR__ . "/../lib/config/path.inc");
require_once(__DIR__ . "/../lib/rabbitMQLib.inc");
require_once(__DIR__ . "/../lib/config/rabbitMQ.ini");

function dbConnect($request)
{
    $db_info = parse_ini_file(__DIR__ . "/../backend/database.ini", true);
    $db_info = $db_info["database"];
    $db = mysqli_connect($db_info["HOST_NAME"], $db_info["USERNAME"], $db_info["PASSWORD"], $db_info["DATABASE"]);


    if (mysqli_connect_errno() !== 0) {
        die("MYSQL Connection error: " . mysqli_connect_error());
    }
    $data = $request["data"];
    switch ($request['type']) {
        case "login":
            $stmt = mysqli_prepare($db, "SELECT * FROM users");
            $stmt->execute();
            if($stmt->errno !== 0) {
                die("Failed to execute query" . $stmt->error);
            }

            $result = $stmt->get_result()->fetch_assoc();
            if($result["user_pass"] == $data["user_pass"] && $result["username"] == $data['username']) {
                return array("code"=> 0, "message"=> "User found!");
            } else {
                return 1;
            }      
    }
}

$server = new rabbitMQServer(__DIR__."/../lib/config/rabbitMQ.ini", "rabbit");
echo "Server started...";
$server->process_requests("dbConnect");

?>