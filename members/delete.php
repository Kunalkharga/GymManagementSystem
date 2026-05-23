<?php
require_once '../config.php';
if (!isLoggedIn()) redirect('../login.php');

$admin_id = $_SESSION['admin_id'];
$member_id = (int)$_GET['id'];

// Security: Only delete if it belongs to this admin
$stmt = $pdo->prepare("SELECT photo FROM members WHERE id = ? AND admin_id = ?");
$stmt->execute([$member_id, $admin_id]);
$member = $stmt->fetch();

if ($member) {
    // Delete photo if exists
    if (!empty($member['photo'])) {
        $photo_path = "../uploads/members/" . $member['photo'];
        if (file_exists($photo_path)) {
            unlink($photo_path);
        }
    }

    // Delete the member
    $stmt = $pdo->prepare("DELETE FROM members WHERE id = ? AND admin_id = ?");
    $stmt->execute([$member_id, $admin_id]);

    // Optional: Also delete related payments and attendance
    $pdo->prepare("DELETE FROM payments WHERE member_id = ?")->execute([$member_id]);
    $pdo->prepare("DELETE FROM attendance WHERE member_id = ?")->execute([$member_id]);

    redirect('index.php?deleted=1');
} else {
    redirect('index.php');
}
?>