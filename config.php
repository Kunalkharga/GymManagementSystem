<?php

// session_start();

// ob_start(); // prevent header issues

// // Base URL
// define('BASE_URL', 'https://anytimegym.onrender.com/');

// // Get environment variables safely
// $host = getenv('DB_HOST') ?: '';
// $db   = getenv('DB_NAME') ?: '';
// $user = getenv('DB_USER') ?: '';
// $pass = getenv('DB_PASS') ?: '';

// // Check missing DB config (VERY IMPORTANT for debugging)
// if (!$host || !$db || !$user) {
//     die("Database environment variables are missing in Render.");
// }

// try {
//     $pdo = new PDO(
//         "mysql:host=$host;dbname=$db;charset=utf8",
//         $user,
//         $pass
//     );

//     $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// } catch(PDOException $e) {
//     die("Database Connection Failed: " . $e->getMessage());
// }

// // Include Functions
// require_once __DIR__ . '/includes/functions.php';

// // Helper: Check Login
// function isLoggedIn() {
//     return isset($_SESSION['admin_id']);
// }

// // Safe redirect (prevents header errors)
// function redirect($url) {
//     if (!headers_sent()) {
//         header("Location: $url");
//         exit();
//     } else {
//         echo "<script>window.location.href='$url';</script>";
//         exit();
//     }
// }

session_start();
define('BASE_URL', 'http://localhost/gym-saas/');

$host = 'localhost';
$db   = 'gym_saas';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Include Functions
require_once __DIR__ . '/includes/functions.php';

// Helper: Check Login
function isLoggedIn() {
    return isset($_SESSION['admin_id']);
}

function redirect($url) {
    header("Location: $url");
    exit(); 
}
?>