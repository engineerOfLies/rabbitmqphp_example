document.addEventListener('DOMContentLoaded', function () {
  
    // Login form submission
    const loginForm = document.getElementById('loginForm');
    loginForm.addEventListener('submit', function (event) {
      event.preventDefault(); // Prevent the default form submission
  
      const username = document.getElementById('username').value;
      const password = document.getElementById('password').value;
  
      // Call your backend API to authenticate the user
      authenticateUser(username, password);
    });
  
    // Function to authenticate user
    function authenticateUser(username, password) {
      // Here you would typically make an AJAX request to your server's login API
      // Using fetch() for demonstration purposes
      fetch('/api/login', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ username, password })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Display a success message or redirect to another page
          displayMessage('Login successful! Redirecting...', 'success');
          setTimeout(() => window.location.href = '/dashboard.html', 2000); // Redirect after 2 seconds
        } else {
          // Display an error message
          displayMessage('Login failed: ' + data.message, 'error');
        }
      })
      .catch(error => {
        console.error('Error during login:', error);
        displayMessage('An error occurred during login.', 'error');
      });
    }
  
    // Register form validation
    const registerForm = document.querySelector('.registration-container form');
    registerForm.addEventListener('submit', function (event) {
      event.preventDefault(); // Prevent the default form submission
  
      const firstName = document.getElementById('first_name').value;
      const lastName = document.getElementById('last_name').value;
      const username = document.getElementById('username').value;
      const password = document.getElementById('password').value;
      const confirmPassword = document.getElementById('confirm_password').value;
  
      // Validate the input
      if (password !== confirmPassword) {
        displayMessage('Passwords do not match.', 'error');
        return false;
      }
  
      // Call your backend API to register the user
      createUser(firstName, lastName, username, password);
    });
  
    // Function to create user
    function createUser(firstName, lastName, username, password) {
      // Here you would typically make an AJAX request to your server's registration API
      // Using fetch() for demonstration purposes
      fetch('/api/register', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ firstName, lastName, username, password })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Display a success message or redirect to another page
          displayMessage('Registration successful! Redirecting to login...', 'success');
          setTimeout(() => window.location.href = '/login.html', 2000); // Redirect after 2 seconds
        } else {
          // Display an error message
          displayMessage('Registration failed: ' + data.message, 'error');
        }
      })
      .catch(error => {
        console.error('Error during registration:', error);
        displayMessage('An error occurred during registration.', 'error');
      });
    };
  
    // Function to display messages to the user
    function displayMessage(message, type) {
      // type can be 'success' or 'error'
      const messageDiv = document.createElement('div');
      messageDiv.textContent = message;
      messageDiv.className = type === 'success' ? 'message-success' : 'message-error';
  
      // Append the message to the body or another element of your choice
      document.body.appendChild(messageDiv);
  
      // Remove the message after 5 seconds
      setTimeout(() => {
        messageDiv.remove();
      }, 5000);
    }
  
  });
  