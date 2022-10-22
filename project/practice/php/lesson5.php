<?php
 //initialize the database
 include 'lesson3.php';
function create_users_table($conn) {
    $sql = "CREATE TABLE users (
        id INT (11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(32) NOT NULL,
        password VARCHAR(32) NOT NULL,
        email VARCHAR(255) DEFAULT NULL
    )";
    if ($conn->query($sql) === TRUE) {
        echo "Table Users table created successfully";
    } else {
        echo "Error creating user table: " . $conn->error;
    }
}

function create_user($conn, $username, $password, $email) {
    $sql = "INSERT INTO users (username, password, email)
            VALUES ('$username', '$password', '$email')";

    print($sql);
    if ($conn->query($sql) === TRUE) {
        echo "User $username added successfully";
    } else {
        echo "Error creating user $username : " . $conn->error;
    }
}

function initialize_db() {
    $conn = connect_to_db();
    create_users_table($conn);
    create_user($conn, "user1", "user1pass", "user1@njit.edu");
    create_user($conn, "user2", "user2pass", "user2@njit.edu");
    create_user($conn, "user3", "user3pass", "user3@njit.edu");
    close_db($conn);
}

initialize_db();
?>