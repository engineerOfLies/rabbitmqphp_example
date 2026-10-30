<?php
?>
<!DOCTYPE html>

<h3>Login</h3>
<form onsubmit="return validate(this)" method="POST">
<div>
    <!-- enter the email or username -->
    <label for="username">Email or Username</label><br>
    <input type="text" id="username" name="username" required><br>
    
    <!-- enter the password -->    
    <label for="pwd">Password</label><br>
    <input type="password" id="pwd" name="password" required><br>

    <!-- adding button for login -->
    <button type="submit">Login</button>
</div>
</form>
<!-- adding link to register page -->
<p>Don't have an account? <a href="register.php">Register here</a></p>

<script> 
//validating the form values
    function validate(form) {
        //getting values via valiate(this)
        let username = form.username.value.trim();
        let password = form.password.value;

        //checking if the username is empty
        if (username === "") 
        {
            alert("Email or username must not be empty.");
            return false;
        }

        //checking if the password is empty
         if (password === "") 
        {
            alert("Password must not be empty.");
            return false;
        }

        sendLogin(username, password);

        return false;
    }

    // This function sends the input to the login handler and receives a response
    async function sendLogin(text, password) {
        const response = await fetch("login_handler.php", {
            method: "POST",
            headers:{
            //header tells the server the format of the request's body
            "Content-Type": "application/json"},
            // converts the login id and password into json to use as request body
            body: JSON.stringify({username: text, password: password})
        });
        // waits for the server reply 
        const data = await response.json();

        // checks whether the login details are correct
        if (data.success)
        {
            // if the login details are correct, it stores token into a session storage
            sessionStorage.setItem("session_token", data.session_token);
            //redirect to home page
            window.location.href = "home.php";
            
        }
        else{
            // else tells the user that the login details were incorrect, and doesn't store them
            alert("Your login was unsuccessful. Please try again.")
        }
        
    }
</script>