<?php
chdir(__DIR__ . '/..');
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');


?>
<head>
<title>Home</title>
<script>
async function validateSession() {	
	const sessionToken = sessionStorage.getItem('session_token');
	const response = await fetch("sessionValidator.php", {
            method: "POST",
            headers:{
            //header tells the server the format of the request's body
            "Content-Type": "application/json"},
            // converts the login id and password into json to use as request body
            body: JSON.stringify({typeRequest:'validate_session', session_token: sessionToken})
        });
        // waits for the server reply 
        const data = await response.json();

        //checks whether the session key is valid
        if (!data.success)
        {
            //else returns user back to login page
            window.location.href = "login.php";
            
	}
}
validateSession();
</script>
</head>

<body>
<h1>Welcome!<h1>
<a href="logout.php">Log Out</a>
</body>
