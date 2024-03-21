<!DOCTYPE html>
<html>
<head>
  <title>Login</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- You can include your stylesheet link here -->
  <link rel="stylesheet" href="">
</head>
<body>
  <main>
    <h1>Login</h1>
    <form action="authenticate.php" method="post">
        <label>User Name:</label>
      <input type="text" name="username" required>
      <br>
        <label>Password:</label>
      <input type="password" name="password" required>
      <br>
      <input type="submit" value="Login">
    </form>
  </main>
</body>
</html>
