<?php
require_once '../config.php';
if (!isLoggedIn()) redirect('../login.php');

$admin_id = $_SESSION['admin_id'];

// Monthly Income
$monthlyIncome = $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM payments 
                              WHERE admin_id = $admin_id 
                              AND MONTH(payment_date) = MONTH(CURDATE()) 
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

<div class="lg:ml-64 min-h-screen pt-16 lg:pt-0">
    <div class="p-4 lg:p-8">
        
        <!-- Success Toast -->
        <?php if(isset($_GET['success'])): ?>
        <div id="successToast" class="fixed top-6 right-6 bg-green-600 text-white px-6 py-4 rounded-2xl shadow-2xl flex items-center gap-3 z-50">
            <i class="fas fa-check-circle text-xl"></i>
            <span class="flex-1">✅ Payment Recorded Successfully!</span>
            <button onclick="hideToast()" class="text-white/70 hover:text-white">✕</button>
            <div class="absolute bottom-0 left-0 h-1 bg-green-300 rounded-b-2xl" id="progressBar" style="width: 100%; transition: width 3s linear;"></div>
        </div>
        <?php endif; ?>

        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
            <h1 class="text-2xl lg:text-3xl font-bold">Payment Management</h1>
            <a href="record.php" class="bg-orange-500 hover:bg-orange-600 px-6 py-3 rounded-2xl font-semibold flex items-center gap-2">
                <i class="fas fa-plus"></i> Record New Payment
            </a>
        </div>

        <!-- Income Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 lg:gap-6 mb-10">
            <div class="bg-gray-900 p-6 rounded-3xl">
                <p class="text-gray-400 text-sm">This Month Revenue</p>
                <p class="text-3xl lg:text-4xl font-bold text-orange-400 mt-3">₹<?= number_format($monthlyIncome) ?></p>
            </div>
            <div class="bg-gray-900 p-6 rounded-3xl">
                <p class="text-gray-400 text-sm">Total Transactions</p>
                <p class="text-3xl lg:text-4xl font-bold mt-3"><?= count($payments) ?></p>
            </div>
            <div class="bg-gray-900 p-6 rounded-3xl">
                <p class="text-gray-400 text-sm">Average Transaction</p>
                <p class="text-3xl lg:text-4xl font-bold mt-3">
                    ₹<?= count($payments) ? number_format($monthlyIncome / count($payments)) : '0' ?>
                </p>
            </div>
        </div>

        <!-- Payments Table -->
        <div class="bg-gray-900 rounded-3xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[650px]">
                    <thead class="bg-gray-800">
                        <tr>
                            <th class="text-left p-4 lg:p-6">Date</th>
                            <th class="text-left p-4 lg:p-6">Member</th>
                            <th class="text-left p-4 lg:p-6 hidden sm:table-cell">Phone</th>
                            <th class="text-left p-4 lg:p-6">Amount</th>
                            <th class="text-left p-4 lg:p-6 hidden md:table-cell">Method</th>
                            <th class="text-center p-4 lg:p-6">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($payments as $payment): ?>
                        <tr class="border-t border-gray-800 hover:bg-gray-800/70">
                            <td class="p-4 lg:p-6"><?= date('d M Y', strtotime($payment['payment_date'])) ?></td>
                            <td class="p-4 lg:p-6 font-medium"><?= htmlspecialchars($payment['full_name']) ?></td>
                            <td class="p-4 lg:p-6 hidden sm:table-cell text-gray-400"><?= htmlspecialchars($payment['phone']) ?></td>
                            <td class="p-4 lg:p-6 font-semibold text-green-400">₹<?= number_format($payment['amount']) ?></td>
                            <td class="p-4 lg:p-6 hidden md:table-cell">
                                <span class="px-4 py-1 bg-gray-700 text-xs rounded-full capitalize"><?= $payment['payment_method'] ?></span>
                            </td>
                            <td class="p-4 lg:p-6 text-center">
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
    </div>
</div>

<script>
window.onload = function() {
    const toast = document.getElementById('successToast');
    if (toast) {
        const progressBar = document.getElementById('progressBar');
        setTimeout(() => progressBar.style.width = '0%', 100);
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.style.display = 'none', 500);
        }, 3000);
    }
};
function hideToast() {
    const toast = document.getElementById('successToast');
    if (toast) toast.style.display = 'none';
}
</script>

<?php include '../includes/footer.php'; ?>