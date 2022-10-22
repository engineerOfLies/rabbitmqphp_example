<?php
include 'db_util.php';

function redirect($url) {
    header('Location: ' .$url);
    die();
}

function check_password($request_user, $request_pass) {
    $success = False;

    $conn = connect_to_db();
    $sql = "SELECT password FROM users where username='$request_user'";
    $result = $conn->query($sql);

    if($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            if ($row["password"] == $request_pass) {
                $success = True;
            } else {
                echo "Invalid try again";
            }
        }
    }
    close_db($conn);
    return $success;
}

function do_login() {
    # $_REQUEST is superglobal and is available to all functions
    if (!isset($_REQUEST['username']) || !isset($_REQUEST['password']) || !isset($_REQUEST['type'])) {
        redirect("login.html");
    }

    $current_user = $_REQUEST['username'];
    $current_password = $_REQUEST['password'];
    if (check_password($current_user, $current_password)) {
        redirect("../success.html");
    } else {
        redirect("../login.html");
    }
}
do_login();
?>