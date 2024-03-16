document.addEventListener('DOMContentLoaded', function() {
    // Correctly target the login form using its ID
    const loginForm = document.getElementById('loginForm');

    // Ensure the validate function receives the event parameter
    loginForm.addEventListener('submit', function(event) {
        // Use the event parameter here to prevent the default form submission
        event.preventDefault();

        const username = document.getElementById('username').value;
        const password = document.getElementById('password').value;

        if (username === '' || password === '') {
            alert('Both username and password must be filled out');
            return false;
        }

        // Note: Update the URL to your actual authentication endpoint
        fetch('http://localhost:3000/api/fetchData', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                username: username,
                password: password,
            }),
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.authenticated) {
                // On successful authentication, redirect the user
                window.location.href = '/dashboard'; // Update the path as necessary
            } else {
                alert('Login failed: ' + data.message);
            }
        })
        .catch(error => {
            console.error('There has been a problem with your fetch operation:', error);
        });
    });
});
