<?php
require_once '../config.php';
if (!isLoggedIn()) redirect('../login.php');

$admin_id = $_SESSION['admin_id'];

// Auto update expiry status on every dashboard load
updateExpiryStatus($pdo, $admin_id);

$expiringTomorrow = getExpiringTomorrow($pdo, $admin_id);

// Stats
$totalMembers = $pdo->query("SELECT COUNT(*) FROM members WHERE admin_id = $admin_id")->fetchColumn();
$activeMembers = $pdo->query("SELECT COUNT(*) FROM members WHERE admin_id = $admin_id AND status = 'active'")->fetchColumn();
$expiredMembers = $pdo->query("SELECT COUNT(*) FROM members WHERE admin_id = $admin_id AND status = 'expired'")->fetchColumn();
$monthlyIncome = $pdo->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE admin_id = $admin_id AND MONTH(payment_date) = MONTH(CURDATE())")->fetchColumn();
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<div class="ml-64 p-8">
    <h1 class="text-3xl font-bold mb-8">Welcome back, <?= htmlspecialchars($_SESSION['gym_name']) ?> 👋</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-gray-900 p-6 rounded-3xl">
            <p class="text-gray-400">Total Members</p>
            <p class="text-5xl font-bold mt-3"><?= $totalMembers ?></p>
        </div>
        <div class="bg-green-900/30 p-6 rounded-3xl">
            <p class="text-green-400">Active Members</p>
            <p class="text-5xl font-bold mt-3 text-green-400"><?= $activeMembers ?></p>
        </div>
        <div class="bg-red-900/30 p-6 rounded-3xl">
            <p class="text-red-400">Expired</p>
            <p class="text-5xl font-bold mt-3 text-red-400"><?= $expiredMembers ?></p>
        </div>
        <div class="bg-orange-900/30 p-6 rounded-3xl">
            <p class="text-orange-400">Monthly Income</p>
            <p class="text-5xl font-bold mt-3 text-orange-400">₹<?= number_format($monthlyIncome) ?></p>
        </div>
    </div>

    <!-- Expiry Alerts -->
    <?php if(count($expiringTomorrow) > 0): ?>
    <div class="mt-10 bg-amber-900/30 border border-amber-500/30 p-6 rounded-3xl">
        <h2 class="text-xl font-semibold text-amber-400 mb-4 flex items-center gap-2">
            <i class="fas fa-bell"></i> Expiring Tomorrow (<?= count($expiringTomorrow) ?>)
        </h2>
        <div class="space-y-4">
            <?php foreach($expiringTomorrow as $m): ?>
            <div class="flex justify-between items-center bg-gray-900 p-4 rounded-2xl">
                <div>
                    <p class="font-medium"><?= htmlspecialchars($m['full_name']) ?></p>
                    <p class="text-sm text-gray-400"><?= $m['phone'] ?></p>
                </div>
                <button onclick="sendWhatsAppReminder(<?= $m['id'] ?>, '<?= addslashes($m['full_name']) ?>', '<?= $m['phone'] ?>', '<?= date('d M Y', strtotime($m['expiry_date'])) ?>')" 
                        class="bg-green-600 hover:bg-green-700 px-6 py-2 rounded-xl text-sm">
                    <i class="fab fa-whatsapp"></i> Remind
                </button>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Recent Members (same as before) -->
</div>

<script>
function sendWhatsAppReminder(id, name, phone, expiry) {
    const message = encodeURIComponent(
        `Hello ${name},\n\nYour gym membership expires tomorrow (${expiry}).\nPlease renew soon! 💪`
    );
    window.open(`https://wa.me/91${phone}?text=${message}`, '_blank');
}
</script>

<?php include '../includes/footer.php'; ?>