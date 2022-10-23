<?php
$my_password = "edm123";
$hidden_password = password_hash($my_password,PASSWORD_DEFAULT);
echo "clear text password is " . $my_password . "\n";
echo "hidden password is " . $hidden_password . "\n";

// encrypted hash cannot be reversed to get the password

if (password_verify("abcd", $hidden_password)) {
    echo "password matches" . "\n";
} else {
    echo ("password does not match" . "\n");
}



?>