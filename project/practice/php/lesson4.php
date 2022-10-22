<?php
include 'lesson3.php';

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
$result = check_password("user1", "user1pass");
echo $result;
echo "\n";

$result = check_password("user1", "dummy");
echo $result;
echo "\n";

?>