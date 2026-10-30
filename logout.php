<?php
chdir(__DIR__ . '/..');
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');
?>

<h1><em>Logging out...</em></h1>

<script>
async function destroySession() {
    const sessionToken = sessionStorage.getItem('session_token');
    try {
        await fetch("sessionValidator.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ typeRequest: 'logout', session_token: sessionToken })
        });
    } catch (e) {}
    sessionStorage.removeItem('session_token');
    window.location.href = "login.php";
}
destroySession();
</script>
