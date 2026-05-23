<?php
require_once '../config.php';
if (!isLoggedIn()) redirect('../login.php');

$id = (int)$_GET['id'];
$admin_id = $_SESSION['admin_id'];

$stmt = $pdo->prepare("DELETE FROM membership_plans WHERE id = ? AND admin_id = ?");
$stmt->execute([$id, $admin_id]);

redirect('index.php?success=deleted');
?>