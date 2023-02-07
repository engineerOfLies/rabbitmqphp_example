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
        var_dump($response);
        header("Location: index.php");
      }
      else if($response["code"] == 1) {
        echo "Invalid password, account exists";
      }
      else {
        echo "Invalid credentials, create an account";
        var_dump($response);
        header("Location: create_account.php");
        // make button show up to create acc + put error msg
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
    <div class="form">
      <form method="post" onsubmit="return sendLoginRequest(this)">
        Username
        <input name="username" id="username" required/>
        Password
        <input name="password" id="password" type =password required/>
        <input hidden name="type" value="login" id="type" /> <br> <br>
        <button class="loginb" type="submit" name="submit">Login</button>
      </form>
    </div>
    </body>
</html>

<style>

.loginb {
  margin: 0 auto;
  display: block;
}
.form {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  color: white;
  font-size: 20px;
}
input {
  margin: 0;
  padding: 0;
  width: 200px;
}
body {
  background-image: linear-gradient(to right, #fc5c7d, #6a82fb);
}

* {
  font-family: 'Trebuchet MS', sans-serif;
}
</style>