<?php
$isLoggedIn = false;
$username = "";
if (isset($_SESSION["username"])) {
    $username = $_SESSION["username"];
    $isLoggedIn = true;
}
?>

<nav class="container">
    <div class="navbar-container">
        <a href="/frontend/recommendedTest.php">MovieSite</a>
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
        margin: 0 auto;
        color:white;
        padding: 1rem;
        
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