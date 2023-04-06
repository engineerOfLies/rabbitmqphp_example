<?php
// Connect to the database
$servername = "localhost";
$username = "user490";
$password = "it490";
$dbname = "friends_list";
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Retrieve user data from the database
$sql = "SELECT * FROM friends";
$result = mysqli_query($conn, $sql);

// Output user data in a table
if (mysqli_num_rows($result) > 0) {
    echo "<table>";
    echo "<tr><th>Name</th></tr>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['name'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No user data found.";
}

// Close the database connection
mysqli_close($conn);
?>


