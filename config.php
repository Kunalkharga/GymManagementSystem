<?php
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