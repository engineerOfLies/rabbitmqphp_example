<?php 
session_start(); 
$user = $_SESSION["user"];
if(isset($user) && $user["logged"] == 1){
    header("Location: ../home.php");
} else {
    header("Location: auth/");
}

?>