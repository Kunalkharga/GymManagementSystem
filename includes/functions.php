<?php
// includes/functions.php

// Auto Update Member Expiry Status
function updateExpiryStatus($pdo, $admin_id) {
    $stmt = $pdo->prepare("UPDATE members SET status = 'expired' 
                          WHERE admin_id = ? 
                          AND expiry_date < CURDATE() 
                          AND status != 'expired'");
    $stmt->execute([$admin_id]);
}

// Get Members Expiring Tomorrow
function getExpiringTomorrow($pdo, $admin_id) {
    $stmt = $pdo->prepare("SELECT id, full_name, phone, expiry_date 
                          FROM members 
                          WHERE admin_id = ? 
                          AND expiry_date = DATE_ADD(CURDATE(), INTERVAL 1 DAY)");
    $stmt->execute([$admin_id]);
    return $stmt->fetchAll();
}

// Sanitize Input
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}
?>