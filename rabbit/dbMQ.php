#!/usr/bin/php
<?php
require_once(__DIR__ . "/../lib/config/path.inc");
require_once(__DIR__ . "/../lib/rabbitMQLib.inc");
require_once(__DIR__ . "/../lib/config/rabbitMQ.ini");
require_once(__DIR__ . "/../lib/helpers.php");
function dbConnect($request)
{
    $db_info = parse_ini_file(__DIR__ . "/../backend/database.ini", true);
    $db_info = $db_info["database"];
    try {
        $db = mysqli_connect($db_info["HOST_NAME"], $db_info["USERNAME"], $db_info["PASSWORD"], $db_info["DATABASE"]);
    }catch (Exception $e) {
        $date = date('m/d/Y h:i:s a', time());
        $errorMsg = $date . " : " . $e;
        $payload = array("data" => array("error" => $errorMsg), "type"=>"Database");
        send($payload, "error");

    }
    if (mysqli_connect_errno() !== 0) {
        $errorMsg = mysqli_connect_error();
       send(array("data" => array("error" => $errorMsg)), "error");
    }
    $data = $request["data"];
    switch ($request['type']) {
        case "login":
            $stmt = mysqli_prepare($db, "SELECT * FROM users WHERE username = '{$data['username']}'");
            $stmt->execute();
            if($stmt->errno !== 0) {
                die("Failed to execute query" . $stmt->error);
            }

            $result = $stmt->get_result()->fetch_assoc();

            // Checks if user is found
            if (array($result)[0] !== NULL) {
                if ($result["user_pass"] == $data["user_pass"] && $result["username"] == $data['username']) {
                    // echo and var_dump on terminal
                    echo "USER FOUND!".PHP_EOL;
                    var_dump($request);
                    return array("code" => 0, "message" => $result);
                } else {
                    // If first (if statement) fails, automatically fail since we don't want to show which one was wrong.
                    echo "INVALID Credentials!".PHP_EOL;
                    var_dump($request);
                    return array("code" => 1, "message" => "Invalid Credentials");
                }
            } else {
                echo "USER NOT FOUND".PHP_EOL;
                var_dump($request);
                return array("code" => 2, "message" => "Account not found.");

            }

        case "create":
            $stmt = mysqli_query($db, "SELECT username FROM users WHERE username = '{$data['username']}'");
            if (mysqli_num_rows($stmt) != 0) {
                echo "Username or email already exists";
                return array("code" => 1, "message" => "doesnt work");
            }
            else {
                $stmt = mysqli_prepare($db, "INSERT INTO users(username, user_pass)VALUES
                ('{$data['username']}', '{$data['user_pass']}', '{$data['email']}')");
                $stmt->execute();
                return array("code" => 0, "message" => "works");
            }
        case "checkBookmark": 

            $stmt = mysqli_query($db, "SELECT * FROM bookmarks WHERE username = '{$data['username']}' AND movie_id = '{$data['movie_id']}'");
            if(mysqli_num_rows($stmt) != 0) {
                echo "Already bookmarked";
                return array("code" => 0, "message" => true);
            } else {
                return array("code" => 0, "message" => false);
            }
      

    }

}

// function eventError($request) {
//     sendEvent("$request", "rabbit");
// }

$server = new rabbitMQServer(__DIR__ . "/../lib/config/rabbitMQ.ini", "rabbit");
echo "Server started..." . PHP_EOL;
$server->process_requests("dbConnect");

?>