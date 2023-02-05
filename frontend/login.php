#!/usr/bin/php
<?php

include(__DIR__ . "/../lib/helpers.php");


$data = array('type' => 'login', 'data' => array('username' => "it490", 'user_pass' => "root"));
$response = send($data, "rabbit");
var_dump($response);

?>


<!-- <head>
  <script src="index.js"></script>
</head>

<body>
  <h1> hello </h1>
  <form method="post" action="login.php">
    Username
    <input name="username" id="username" required />
    Password
    <input name="password" id="password" required />
    <input hidden name="type" value="login" id="type" />
    <button type="submit" name="submit">Login</button>
  </form>
</body>

</html> -->