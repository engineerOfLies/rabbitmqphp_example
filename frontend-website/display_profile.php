<?php
// Connect to the database
$servername = "localhost";
$username = "user490";
$password = "it490";
$dbname = "movie_website";
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Retrieve user data from the database
$sql = "SELECT * FROM users";
$result = mysqli_query($conn, $sql);

// Output user data in a table
if (mysqli_num_rows($result) > 0) {
    echo "<table>";
    echo "<tr><th>Name</th><th>Age</th><th>Bio</th><th>Image</th></tr>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['name'] . "</td>";
        echo "<td>" . $row['age'] . "</td>";
        echo "<td>" . $row['bio'] . "</td>";
        echo "<td><img src='" . $row['image'] . "'></td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No user data found.";
}

// Close the database connection
mysqli_close($conn);
?>


