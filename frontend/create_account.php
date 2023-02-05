<?php
    include(__DIR__ . "/../lib/helpers.php");
    include(__DIR__ . "/../rabbit/rabbitmq.php"); 

    $username = $_POST['username'];
    $password = $_POST['password'];

    if (isset($_POST['submit'])) {
        $data = array('type' => 'create', 'data' => array('username' => $username, 'user_pass' => $password));
        $response = send($data, "rabbit");
        var_dump($response);
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <script src="index.js"></script>
    </head>
    <body>
    <form method="post">
      Desired Username
      <input name="username" id="username" required/>
      Desired Password
      <input name="password" id="password" required/>
      <input hidden name="type" value="login" id="type" />
      <button type="submit" name="create">Create Account</button>
    </form>
    </body>
</html>