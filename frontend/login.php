<?php
  include(__DIR__ . "/../lib/helpers.php");

  $username = $_POST['username'];
  $password = $_POST['password'];
  $message = -99;
  if(isset($_POST['submit'])) {
    $data = array('type' => 'login', 'data' => array('username' => $username, 'user_pass' => $password));
    $response = send($data, "rabbit");
      if($response["code"] == 0) {
        session_start();
        $_SESSION["username"] = $username;
        // var_dump($response);
        header("Location: index.php");
      }
      else if($response["code"] == 1) {
        $message = $response["code"];
      }
      else {
        $message = $response["code"];
        // echo "Invalid credentials, create an account";
        // var_dump($response);
        $message = 2;
      }
    // var_dump($response);
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
      <?php if($message == 2) {
        echo "<p class='message'> <a class='message' href='create_account.php'> Create an account </a></p>";
        } 
        else if($message == 1) {
        echo "<p class='message'> Wrong password </p>";
        }
        ?>
        Username
        <input name="username" id="username" required/>
        Password
        <input name="password" id="password" type =password required/>
        <input hidden name="type" value="login" id="type" /> <br> <br>
        <button class="loginb" type="submit" name="submit" onclick=>Login</button>
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