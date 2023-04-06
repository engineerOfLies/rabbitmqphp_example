<?php
// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  
  // Get the form data and sanitize it
  $name = filter_var($_POST["name"], FILTER_SANITIZE_STRING);
  $age = filter_var($_POST["age"], FILTER_SANITIZE_NUMBER_INT);
  $bio = filter_var($_POST["bio"], FILTER_SANITIZE_STRING);
  
  // Upload the profile picture
  $target_dir = "uploads/";
  $target_file = $target_dir . basename($_FILES["picture"]["name"]);
  $uploadOk = 1;
  $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
  // Check if image file is a actual image or fake image
  if(isset($_POST["submit"])) {
    $check = getimagesize($_FILES["picture"]["tmp_name"]);
    if($check !== false) {
      $uploadOk = 1;
    } else {
      echo "File is not an image.";
      $uploadOk = 0;
    }
  }
  // Check if file already exists
  if (file_exists($target_file)) {
    echo "Sorry, file already exists.";
    $uploadOk = 0;
  }
  // Check file size
  if ($_FILES["picture"]["size"] > 500000) {
    echo "Sorry, your file is too large.";
    $uploadOk = 0;
  }
  // Allow certain file formats
  if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
  && $imageFileType != "gif" ) {
    echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
    $uploadOk = 0;
  }
  // Check if $uploadOk is set to 0 by an error
  if ($uploadOk == 0) {
    echo "Sorry, your file was not uploaded.";
  // if everything is ok, try to upload file
  } else {
    if (move_uploaded_file($_FILES["picture"]["tmp_name"], $target_file)) {
      echo "The file ". basename( $_FILES["picture"]["name"]). " has been uploaded.";
    } else {
      echo "Sorry, there was an error uploading your file.";
    }
  }
  
  // Save the profile data into the database
  $servername = "localhost";
  $username = "user490";
  $password = "it490";
  $dbname = "movie_website";

  // Create connection
  $conn = mysqli_connect($servername, $username, $password, $dbname);
  // Check connection
  if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
  }

  $sql = "INSERT INTO users (name, age, bio, image)
  VALUES ('$name', '$age', '$bio', '$target_file')";

  if (mysqli_query($conn, $sql)) {
    echo "Profile created successfully";
    header("Location: profile.html");
  } else {
    echo "Error: " . $sql . "<br>" . mysqli_error($conn);
  }

  mysqli_close($conn);
}
?>


