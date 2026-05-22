<?php
require_once '../config.php';
if (!isLoggedIn()) redirect('../login.php');

$admin_id = $_SESSION['admin_id'];
$type = $_GET['type'] ?? '';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $type . '_report_' . date('Y-m-d') . '.csv"');

$output = fopen('php://output', 'w');

if ($type == 'revenue') {
    fputcsv($output, ['Month', 'Revenue (₹)']);
    
    $stmt = $pdo->prepare("SELECT DATE_FORMAT(payment_date, '%Y-%m') as month, SUM(amount) as revenue 
                           FROM payments WHERE admin_id = ? GROUP BY month ORDER BY month DESC");
    $stmt->execute([$admin_id]);
    
    while ($row = $stmt->fetch()) {
        fputcsv($output, [date('M Y', strtotime($row['month'] . '-01')), $row['revenue']]);
    }
} 

elseif ($type == 'members') {
    fputcsv($output, ['Full Name', 'Phone', 'Gender', 'Age', 'Plan', 'Start Date', 'Expiry Date', 'Status']);
    
    $stmt = $pdo->prepare("SELECT m.*, p.plan_name FROM members m 
                           LEFT JOIN membership_plans p ON m.membership_plan_id = p.id 
                           WHERE m.admin_id = ? ORDER BY m.created_at DESC");
    $stmt->execute([$admin_id]);
    
    while ($row = $stmt->fetch()) {
        fputcsv($output, [
            $row['full_name'],
            $row['phone'],
            $row['gender'],
            $row['age'],
            $row['plan_name'],
            $row['start_date'],
            $row['expiry_date'],
            $row['status']
        ]);
    }
} 

elseif ($type == 'expired') {
    fputcsv($output, ['Full Name', 'Phone', 'Expiry Date', 'Days Since Expired']);
    
    $stmt = $pdo->prepare("SELECT full_name, phone, expiry_date,
                           DATEDIFF(CURDATE(), expiry_date) as days_expired 
                           FROM members 
                           WHERE admin_id = ? AND status = 'expired' 
                           ORDER BY expiry_date DESC");
    $stmt->execute([$admin_id]);
    
    while ($row = $stmt->fetch()) {
        fputcsv($output, [$row['full_name'], $row['phone'], $row['expiry_date'], $row['days_expired']]);
    }
}

fclose($output);
exit();
?>