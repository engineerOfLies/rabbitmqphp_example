<?php
    include(__DIR__ . "/../lib/helpers.php");
    include(__DIR__ . "/../rabbit/rabbitmq.php"); 

    $username = $_POST['username'];
    $password = $_POST['password'];
    $type = $_POST['type'];
    $message = -99;

    if (isset($_POST['submit'])) {
        $data = array('type' => $type, 'data' => array('username' => $username, 'user_pass' => $password));
        $response = send($data, "rabbit");
        if($response["code"] == 0) {
            $message = 0;
        }
        else {
            $message = 1;
        }
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <script src="index.js"></script>
    </head>
    <body>
    <div class="form">
        <form method="post">
        <?php if($message == 0) {
        echo "<p class='message'> Account created. <a class='message' href='login.php'>Login here</a></p>";
        } 
        else if($message == 1) {
        echo "<p class='message'> Username already exists </p>";
        }
        ?>
        Desired Username
        <input name="username" id="username" required/>
        Desired Password
        <input name="password" id="password" required/>
        <input hidden name="type" value="create" id="type" /> <br> <br>
        <button class="loginb" name="submit">Create Account</button>
        </form>
    </div>
    </body>
</html>

<style>

.message {
  text-align: center;
  color: white !important;
}
.loginb {
  margin: 0 auto;
  display: block;
  background: transparent;
  width: 180px;
  height: 60px;
  color: white;
  font-size: 20px;
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
background-color: #FF3CAC;
background-image: linear-gradient(225deg, #FF3CAC 0%, #784BA0 50%, #2B86C5 100%);

}

* {
  font-family: 'Trebuchet MS', sans-serif;
}
</style>