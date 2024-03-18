// public/app.js
document.getElementById('loginForm').addEventListener('submit', function(event) {
    event.preventDefault();
    const loginData = {
        username : document.getElementById('username').value,
        password : document.getElementById('password').value
    };
    

    fetch('/api/login', {
        method: 'POST',
        headers: { 
            'Content-Type': 'application/json' 
        },
        body: JSON.stringify({loginData})
    })
      .then(response => response.json())
      .then(data => console.log(data))
      .catch(error => console.error('Error:', error));
      
});
document.getElementById('registerForm').addEventListener('submit', function(event) {
    event.preventDefault();

    const registerData = {
        first_name: document.getElementById('first_name').value,
        last_name: document.getElementById('last_name').value,
        username: document.getElementById('username').value,
        password: document.getElementById('password').value,
        confirm_password: document.getElementById('confirm_password').value
    };

    // Check if passwords match
    if (registerData.password !== registerData.confirm_password) {
        alert("Passwords do not match.");
        return;
    }

    fetch('/api/register', {
        method: 'POST',
        headers: { 
            'Content-Type': 'application/json' 
        },
        body: JSON.stringify(registerData)
    })
    .then(response => response.json())
    .then(data => alert(data.message))
    .catch(error => console.error('Error:', error));
});
