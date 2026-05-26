<?php
require_once '../config.php';
if (!isLoggedIn()) {
    redirect('../login.php');
}

$admin_id = $_SESSION['admin_id'];

// Auto update expiry status
updateExpiryStatus($pdo, $admin_id);

// Get expiring tomorrow
$expiringTomorrow = getExpiringTomorrow($pdo, $admin_id);

// Statistics
$totalMembers = $pdo->query("SELECT COUNT(*) FROM members WHERE admin_id = $admin_id")->fetchColumn();
$activeMembers = $pdo->query("SELECT COUNT(*) FROM members WHERE admin_id = $admin_id AND status = 'active'")->fetchColumn();
$expiredMembers = $pdo->query("SELECT COUNT(*) FROM members WHERE admin_id = $admin_id AND status = 'expired'")->fetchColumn();

// Monthly Income
$monthlyIncome = $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM payments 
                              WHERE admin_id = $admin_id 
                              AND MONTH(payment_date) = MONTH(CURDATE()) 
                              AND YEAR(payment_date) = YEAR(CURDATE())")->fetchColumn();

// Recent Members
$stmt = $pdo->prepare("SELECT m.*, p.plan_name 
                       FROM members m 
                       LEFT JOIN membership_plans p ON m.membership_plan_id = p.id 
                       WHERE m.admin_id = ? AND m.status != 'pending' 
                       ORDER BY m.created_at DESC LIMIT 5");
$stmt->execute([$admin_id]);
$recentMembers = $stmt->fetchAll();

// Set timezone to Nepal Standard Time (NST)
date_default_timezone_set('Asia/Kathmandu'); // For Nepal timezone

// Today's date for display
$today = new DateTime();
$greeting = "";
$hour = (int)date('G');
if ($hour < 12) {
    $greeting = "Good Morning";
} elseif ($hour < 17) {
    $greeting = "Good Afternoon";
} else {
    $greeting = "Good Evening";
}

?>



<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<div class="lg:ml-64 min-h-screen pt-16 lg:pt-0">
    <div class="p-4 lg:p-8">
        
        <h1 class="text-3xl lg:text-4xl font-bold">
                    <?= $greeting ?>
                </h1>
                <p class="text-gray-400 mt-2">
                    <i class="far fa-calendar-alt mr-2"></i>
                    <?= $today->format('l, F j, Y') ?>
                </p><br>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6">
            <div class="bg-gray-900 p-6 rounded-3xl">
                <p class="text-gray-400 text-sm">Total Members</p>
                <p class="text-4xl lg:text-5xl font-bold mt-3"><?= $totalMembers ?></p>
            </div>
            
            <div class="bg-green-900/30 p-6 rounded-3xl">
                <p class="text-green-400 text-sm">Active Members</p>
                <p class="text-4xl lg:text-5xl font-bold mt-3 text-green-400"><?= $activeMembers ?></p>
            </div>
            
            <div class="bg-red-900/30 p-6 rounded-3xl">
                <p class="text-red-400 text-sm">Expired</p>
                <p class="text-4xl lg:text-5xl font-bold mt-3 text-red-400"><?= $expiredMembers ?></p>
            </div>
            
            <div class="bg-orange-900/30 p-6 rounded-3xl">
                <p class="text-orange-400 text-sm">Monthly Income</p>
                <p class="text-4xl lg:text-5xl font-bold mt-3 text-orange-400">₹<?= number_format($monthlyIncome) ?></p>
            </div>
        </div>

        <!-- Expiring Tomorrow Alert -->
        <?php if (count($expiringTomorrow) > 0): ?>
        <div class="mt-8 bg-amber-900/30 border border-amber-500/30 p-6 rounded-3xl">
            <h2 class="text-xl font-semibold text-amber-400 mb-5 flex items-center gap-3">
                <i class="fas fa-bell"></i> 
                Expiring Tomorrow (<?= count($expiringTomorrow) ?> members)
            </h2>
            
            <div class="space-y-4">
                <?php foreach($expiringTomorrow as $member): ?>
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center bg-gray-900 p-5 rounded-2xl gap-4">
                    <div>
                        <p class="font-semibold"><?= htmlspecialchars($member['full_name']) ?></p>
                        <p class="text-sm text-gray-400"><?= $member['phone'] ?></p>
                    </div>
                    <button onclick="sendWhatsAppReminder(<?= $member['id'] ?>, '<?= addslashes($member['full_name']) ?>', '<?= $member['phone'] ?>')" 
                            class="bg-green-600 hover:bg-green-700 px-6 py-3 rounded-2xl text-sm flex items-center gap-2 whitespace-nowrap">
                        <i class="fab fa-whatsapp"></i> Send Reminder
                    </button>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Recent Registrations -->
        <div class="mt-10 bg-gray-900 rounded-3xl p-6 lg:p-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold">Recent Registrations</h2>
                <a href="../members/index.php" class="text-orange-400 hover:text-orange-500 text-sm font-medium">
                    View All →
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-full">
                    <thead>
                        <tr class="text-left text-gray-400 border-b border-gray-700">
                            <th class="pb-4">Member</th>
                            <th class="pb-4">Plan</th>
                            <th class="pb-4">Expiry</th>
                            <th class="pb-4">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($recentMembers as $member): 
                            $isExpired = strtotime($member['expiry_date']) < time();
                        ?>
                        <tr class="border-t border-gray-800 hover:bg-gray-800/50">
                            <td class="py-5">
                                <p class="font-medium"><?= htmlspecialchars($member['full_name']) ?></p>
                            </td>
                            <td class="py-5"><?= htmlspecialchars($member['plan_name'] ?? 'N/A') ?></td>
                            <td class="py-5"><?= date('d M Y', strtotime($member['expiry_date'])) ?></td>
                            <td class="py-5">
                                <span class="px-4 py-1 text-xs rounded-full <?= $isExpired ? 'bg-red-500/20 text-red-400' : 'bg-green-500/20 text-green-400' ?>">
                                    <?= $isExpired ? 'Expired' : 'Active' ?>
                                </span>
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
// WhatsApp Reminder Function
function sendWhatsAppReminder(id, name, phone) {
    const expiry = "tomorrow";
    const message = encodeURIComponent(
        `Hello ${name},\n\n` +
        `Your gym membership expires ${expiry}.\n` +
        `Please renew soon to continue your fitness journey! 💪\n\n` +
        `Thank you!`
    );
    window.open(`https://wa.me/91${phone}?text=${message}`, '_blank');
}
</script>

<?php include '../includes/footer.php'; ?>
