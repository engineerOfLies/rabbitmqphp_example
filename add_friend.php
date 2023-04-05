<?php
// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the friend name from the form
    $friend_name = $_POST["friend_name"];

    // Connect to the database
    $servername = "localhost";
    $username = "user490";
    $password = "it490";
    $dbname = "friends_list";

    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Insert the friend name into the database
    $sql = "INSERT INTO friends (name) VALUES ('$friend_name')";

    if ($conn->query($sql) === TRUE) {
        echo "New friend added successfully";
        header("Location: profile.html");
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $conn->close();
}
?>


