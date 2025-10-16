<?php
session_start();
require_once '../config/config.php';
require_once '../includes/functions.php';

// Log activity before destroying session
if (isLoggedIn()) {
    try {
        $db = (new Database())->getConnection();
        logActivity($db, $_SESSION['user_id'], 'User logged out', 'user', $_SESSION['user_id']);
    } catch (Exception $e) {
        error_log("Logout error: " . $e->getMessage());
    }
}

// Destroy session
session_destroy();

// Clear session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Redirect to homepage
header("Location: ../index.php");
exit();
?>

