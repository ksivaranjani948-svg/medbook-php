<?php
// Include this at the top of any page that requires a logged-in user.
// config.php must already be included (it starts the session).
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}
?>
