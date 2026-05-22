<?php

session_start();

// Base URL
define('BASE_URL', 'https://gymmanagementsystem-5ae0.onrender.com');

// Database Credentials from Render Environment Variables
$host = getenv('DB_HOST');
$db   = getenv('DB_NAME');
$user = getenv('DB_USER');
$pass = getenv('DB_PASS');

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$db;charset=utf8",
        $user,
        $pass
    );

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
// session_start();
// define('BASE_URL', 'http://localhost/gym-saas/');

// $host = 'localhost';
// $db   = 'gym_saas';
// $user = 'root';
// $pass = '';

// try {
//     $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
//     $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
// } catch(PDOException $e) {
//     die("Connection failed: " . $e->getMessage());
// }

// // Include Functions
// require_once __DIR__ . '/includes/functions.php';

// // Helper: Check Login
// function isLoggedIn() {
//     return isset($_SESSION['admin_id']);
// }

// function redirect($url) {
//     header("Location: $url");
//     exit();
// }
