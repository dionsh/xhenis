<?php
// auth.php — include at the TOP of every page that requires login.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}