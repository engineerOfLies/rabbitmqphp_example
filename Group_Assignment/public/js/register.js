
function login() {
    // Get the username and password from the input fields
    var username = document.getElementById("username").value;
    var password = document.getElementById("password").value;

    // Send a POST request to the server to authenticate the user
    fetch('/login', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ username: username, password: password })
    })
    .then(response => {
        // Check if the response indicates successful login
        if (response.ok) {
            alert("Login successful!");
            document.getElementById("loginContainer").classList.add("hidden");
            document.getElementById("logoutContainer").classList.remove("hidden");
            return true; // Allow form submission
        } else if (response.status === 404) {
            // User not found, display message to create an account
            alert("User not found. Please create an account.");
            return false; // Prevent form submission
        } else {
            // Other error occurred, display generic message
            alert("An error occurred. Please try again later.");
            return false; // Prevent form submission
        }
    })
    .catch(error => {
        console.error('Error logging in:', error);
        alert("An error occurred. Please try again later.");
        return false; // Prevent form submission
    });
}


function logout() {
    // You can perform any logout actions here, like clearing session data
    alert("Logout successful!");
    document.getElementById("loginContainer").classList.remove("hidden");
    document.getElementById("logoutContainer").classList.add("hidden");
}

// Function to validate the registration form
function validateRegistrationForm() {
    var firstName = document.getElementById("first_name").value.trim();
    var lastName = document.getElementById("last_name").value.trim();
    var username = document.getElementById("username").value.trim();
    var password = document.getElementById("password").value.trim();
    var confirmPassword = document.getElementById("confirm_password").value.trim();

    // Check if any field is empty
    if (firstName === "" || lastName === "" || username === "" || password === "" || confirmPassword === "") {
        alert("All fields are required");
        return false;
    }

    // Check if password and confirm password match
    if (password !== confirmPassword) {
        alert("Passwords do not match");
        return false;
    }

    // If all checks pass, form is valid
    return true;
}

// Function to handle form submission (registration)
document.getElementById("newUser").addEventListener("submit", function(event) {
    event.preventDefault(); // Prevent default form submission

    // Perform form validation
    if (validateRegistrationForm()) {
        // Form is valid, perform registration process here
        alert("Registration successful!"); // Example: Show alert for successful registration
        // You can add further logic here, such as submitting the form data to a server via AJAX
    }
});
function logout() {
    // You can perform any logout actions here, like clearing session data
    alert("Logout successful!");
    document.getElementById("loginContainer").classList.remove("hidden");
    document.getElementById("logoutContainer").classList.add("hidden");
}
