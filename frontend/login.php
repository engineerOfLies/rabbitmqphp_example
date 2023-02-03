<?php
  session_start();
  require_once 'db_conn.php';
  $username = $_POST['username'];
  $password = $_POST['password'];

  if(isset($_POST['submit'])) {
	$select = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username' AND password='$password'");
	var_dump($select);
	if(mysqli_num_rows($select) == 1) {
		echo "Logged in";
		$_SESSION["username"] = $username;
		header("Location: index.php");
	}
	else {
		echo "Doesn't work";
	}
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