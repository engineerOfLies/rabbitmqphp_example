#!/usr/bin/php
<?php
include(__DIR__ . "/../lib/helpers.php");
include(__DIR__ . "/../rabbit/rabbitmq.php"); 

$username = $_POST['username'];
$password = $_POST['password'];

if(isset($_POST['submit'])) {
    $data = array('type' => 'login', 'data' => array('username' => $username, 'user_pass' => $password));
    $response = send($data, "rabbit");
    if($response["code"] == 0) {
      session_start();
      $_SESSION["username"] = $username;
      header("Location: index.php");
    }
    else {
      echo "Try again";
    }
  var_dump($response);
}
?>


<!DOCTYPE html>
<html>
    <head>
        <script src="index.js"></script>
    </head>
    <body>
        <h1> hello </h1>
    <form method="post" onsubmit="return sendLoginRequest(this)">
      Username
      <input name="username" id="username" required/>
      Password
      <input name="password" id="password" required/>
      <input hidden name="type" value="login" id="type" />
      <button type="submit" name="submit">Login</button>
    </form>
    </body>
</html>