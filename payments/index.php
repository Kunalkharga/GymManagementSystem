<?php
require_once '../config.php';
if (!isLoggedIn()) redirect('../login.php');

$admin_id = $_SESSION['admin_id'];

// Monthly Income
$monthlyIncome = $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM payments 
                              WHERE admin_id = $admin_id AND MONTH(payment_date) = MONTH(CURDATE()) 
                              AND YEAR(payment_date) = YEAR(CURDATE())")->fetchColumn();

// All Payments
$stmt = $pdo->prepare("SELECT p.*, m.full_name, m.phone 
                       FROM payments p 
                       JOIN members m ON p.member_id = m.id 
                       WHERE p.admin_id = ? 
                       ORDER BY p.payment_date DESC");
$stmt->execute([$admin_id]);
$payments = $stmt->fetchAll();
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<div class="ml-64 p-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold">Payment Management</h1>
        <a href="record.php" class="bg-orange-500 hover:bg-orange-600 px-6 py-3 rounded-xl font-semibold flex items-center gap-2">
            <i class="fas fa-plus"></i> Record Payment
        </a>
    </div>

    <!-- Income Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        <div class="bg-gray-900 p-6 rounded-3xl">
            <p class="text-gray-400">This Month Revenue</p>
            <p class="text-5xl font-bold text-orange-400 mt-3">₹<?= number_format($monthlyIncome) ?></p>
        </div>
        <div class="bg-gray-900 p-6 rounded-3xl">
            <p class="text-gray-400">Total Transactions</p>
            <p class="text-5xl font-bold mt-3"><?= count($payments) ?></p>
        </div>
        <div class="bg-gray-900 p-6 rounded-3xl">
            <p class="text-gray-400">Avg Transaction</p>
            <p class="text-5xl font-bold mt-3">
                ₹<?= count($payments) ? number_format($monthlyIncome / count($payments)) : '0' ?>
            </p>
        </div>
    </div>

    <div class="bg-gray-900 rounded-3xl overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-800">
                <tr>
                    <th class="text-left p-6">Date</th>
                    <th class="text-left p-6">Member</th>
                    <th class="text-left p-6">Amount</th>
                    <th class="text-left p-6">Method</th>
                    <th class="text-center p-6">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($payments as $payment): ?>
                <tr class="border-t border-gray-800 hover:bg-gray-800/50">
                    <td class="p-6"><?= date('d M Y', strtotime($payment['payment_date'])) ?></td>
                    <td class="p-6">
                        <div>
                            <p class="font-medium"><?= htmlspecialchars($payment['full_name']) ?></p>
                            <p class="text-sm text-gray-400"><?= $payment['phone'] ?></p>
                        </div>
                    </td>
                    <td class="p-6 font-semibold text-green-400">₹<?= number_format($payment['amount']) ?></td>
                    <td class="p-6">
                        <span class="px-4 py-1 bg-gray-700 rounded-full text-sm capitalize">
                            <?= $payment['payment_method'] ?>
                        </span>
                    </td>
                    <td class="p-6 text-center">
                        <a href="../members/profile.php?id=<?= $payment['member_id'] ?>" class="text-blue-400 hover:text-blue-500">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>