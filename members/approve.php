<?php
require_once '../config.php';
if (!isLoggedIn()) redirect('../login.php');

$admin_id = $_SESSION['admin_id'];
$member_id = (int)$_GET['id'];

// Verify member belongs to this admin and is pending
$stmt = $pdo->prepare("SELECT * FROM members WHERE id = ? AND admin_id = ? AND status = 'pending'");
$stmt->execute([$member_id, $admin_id]);
$member = $stmt->fetch();

if (!$member) {
    redirect('index.php');
}

// Approve the member
$stmt = $pdo->prepare("UPDATE members SET status = 'active' WHERE id = ? AND admin_id = ?");
$stmt->execute([$member_id, $admin_id]);

redirect('index.php?success=approved');
?>