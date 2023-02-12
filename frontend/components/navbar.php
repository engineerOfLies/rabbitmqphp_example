<?php
session_start();
$isLoggedIn = false;
$username = "";
if (isset($_SESSION["user"])) {
    $username = $_SESSION["user"];
}
?>

<nav class="container">
    <div class="navbar-container">
        <a>MovieSite</a>
        <ul>
            <li>Movies</li>
            <li>TV Shows</li>
            <li>Forums</li>
            <?php if ($isLoggedIn) { ?>
                <li>Dashboard</li>
            <?php } else { ?>
                <li>Log In</li>
                <li>Register </li>
            <?php } ?>
        </ul>
    </div>
</nav>


<style>
    .container {
        width: 100%;
        max-width: 1440px;
        margin: 0;
        color:white;
        
    }
    ul {
        list-style: none;
        display: flex;
        gap: 16px;
        width: fit;
    }
    li {
        font-size: 14px;
    }

    .navbar-container {
        display:flex;
        width: 100%;
        justify-content: space-between;
        align-items: center;
    }
</style>