     document.addEventListener('DOMContentLoaded', function() {
        // Function to display the welcome message
        function displayWelcome(user) {
            var welcomeMessageDiv = document.getElementById('welcome-message');
            welcomeMessageDiv.innerHTML = 'Welcome ' + user + '!';
            welcomeMessageDiv.style.display = 'block';
        }

        // Simulate the login/authentication process
        // Replace this with your actual login/authentication code
        // This is where you would retrieve the username of the logged-in user
        // For demonstration purposes, we'll just set a dummy username
        var loggedInUsername = 'JohnDoe'; // You would get this from your server after login
        displayWelcome(logloggedInUsername);
    });
