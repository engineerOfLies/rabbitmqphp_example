<!DOCTYPE html>
<html>
<body>
<div>
    <h1>Login</h1>
    <form method="POST" action="">
        <div>
            <label>Username</label>
            <input type="text" placeholder="Enter Username" name="username"  required>
        </div>
        <div>
            <label>Password</label>
            <input type="text" placeholder="Enter Password" name="password"  required>
        </div>
        <input type="submit" value="Login" name="submit" />
        <?php
            if(isset($_POST['submit']))//starts php when user clicks submit button
            {
                
                 $inputedusername= $_POST['username'];//getting username and password from the form 
				 $inputedpassword= $_POST['password'];
				 require_once('/home/ubuntu/Null/lib/rabbitMQLib.inc');//calls required files to connect to server

				 $client = new rabbitMQClient("/home/ubuntu/Null/lib/rabbitMQ.ini","Authentication");
				 if (isset($argv[1]))
				 {
				 $msg = $argv[1];
				 }
				 else
				 {
				 $msg = "login info";
				 }

				 $request = array();
				 $request['type'] = "Login";
				 $request['username'] = $inputedusername;//sending username to server
				 $request['password'] = $inputedpassword;//sending password to server
				 $request['message'] = $msg;
				//  $response = $client->send_request($request);
				 $response = $client->publish($request);

				 echo "client received response: ".PHP_EOL;
				 print_r($response);
				 echo "\n\n";
				 
				 
                
                
            } 
        ?>
    </form>
    
</div>

</body>
</html>
