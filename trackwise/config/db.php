<?php
// ============================================
// config/db.php — Database Connection
// ============================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // Default XAMPP username
define('DB_PASS', '');           // Default XAMPP password (empty)
define('DB_NAME', 'trackwise');

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    die("❌ Connection failed: " . mysqli_connect_error());
}

// Start session for login system
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Helper: redirect if not logged in
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
}

// Helper: get logged-in user id
function userId() {
    return $_SESSION['user_id'] ?? null;
}
?>