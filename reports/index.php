<?php
require_once '../config.php';
if (!isLoggedIn()) redirect('../login.php');

$admin_id = $_SESSION['admin_id'];
updateExpiryStatus($pdo, $admin_id); // Ensure latest status

// Monthly Revenue (Last 6 Months)
$stmt = $pdo->prepare("SELECT DATE_FORMAT(payment_date, '%Y-%m') as month, SUM(amount) as revenue 
                       FROM payments 
                       WHERE admin_id = ? 
                       GROUP BY month 
                       ORDER BY month DESC LIMIT 6");
$stmt->execute([$admin_id]);
$revenueData = $stmt->fetchAll();

// Active vs Expired
$activeCount = $pdo->query("SELECT COUNT(*) FROM members WHERE admin_id = $admin_id AND status = 'active'")->fetchColumn();
$expiredCount = $pdo->query("SELECT COUNT(*) FROM members WHERE admin_id = $admin_id AND status = 'expired'")->fetchColumn();

// Members by Gender
$genderStats = $pdo->query("SELECT gender, COUNT(*) as count FROM members WHERE admin_id = $admin_id GROUP BY gender")->fetchAll();
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<div class="ml-64 p-8">
    <h1 class="text-3xl font-bold mb-8">Reports & Analytics</h1>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Revenue Report -->
        <div class="bg-gray-900 rounded-3xl p-8">
            <div class="flex justify-between mb-6">
                <h2 class="text-xl font-semibold">Monthly Revenue (Last 6 Months)</h2>
                <a href="export.php?type=revenue" 
                   class="text-orange-400 hover:text-orange-500 flex items-center gap-2">
                    <i class="fas fa-download"></i> Export CSV
                </a>
            </div>
            
            <div class="space-y-4">
                <?php foreach($revenueData as $row): ?>
                <div class="flex justify-between items-center bg-gray-800 p-4 rounded-2xl">
                    <p class="font-medium"><?= date('M Y', strtotime($row['month'] . '-01')) ?></p>
                    <p class="text-2xl font-bold text-orange-400">₹<?= number_format($row['revenue']) ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Membership Overview -->
        <div class="bg-gray-900 rounded-3xl p-8">
            <h2 class="text-xl font-semibold mb-6">Membership Overview</h2>
            
            <div class="grid grid-cols-2 gap-6">
                <div class="bg-green-900/30 p-6 rounded-3xl text-center">
                    <p class="text-green-400 text-sm">Active Members</p>
                    <p class="text-6xl font-bold mt-3"><?= $activeCount ?></p>
                </div>
                <div class="bg-red-900/30 p-6 rounded-3xl text-center">
                    <p class="text-red-400 text-sm">Expired Members</p>
                    <p class="text-6xl font-bold mt-3"><?= $expiredCount ?></p>
                </div>
            </div>

            <div class="mt-8">
                <h3 class="font-medium mb-4">Members by Gender</h3>
                <?php foreach($genderStats as $g): ?>
                <div class="flex justify-between py-3 border-b border-gray-700 last:border-none">
                    <span><?= $g['gender'] ?></span>
                    <span class="font-semibold"><?= $g['count'] ?></span>
                </div>
                <?php endforeach; ?>
            </div>

            <a href="export.php?type=members" 
               class="mt-8 block text-center bg-orange-500 hover:bg-orange-600 py-4 rounded-2xl font-semibold">
                Download Full Member List (CSV)
            </a>
        </div>
    </div>

    <!-- Expired Members Report -->
    <div class="mt-10 bg-gray-900 rounded-3xl p-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold">Expired Memberships</h2>
            <a href="export.php?type=expired" class="text-orange-400 hover:text-orange-500">
                <i class="fas fa-download"></i> Export
            </a>
        </div>
        
        <?php
        $expiredMembers = $pdo->query("SELECT full_name, phone, expiry_date 
                                      FROM members 
                                      WHERE admin_id = $admin_id AND status = 'expired' 
                                      ORDER BY expiry_date DESC LIMIT 20")->fetchAll();
        ?>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-left text-gray-400 border-b border-gray-700">
                        <th class="pb-4">Member Name</th>
                        <th class="pb-4">Phone</th>
                        <th class="pb-4">Expired On</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($expiredMembers as $m): ?>
                    <tr class="border-t border-gray-800">
                        <td class="py-4"><?= htmlspecialchars($m['full_name']) ?></td>
                        <td class="py-4"><?= $m['phone'] ?></td>
                        <td class="py-4 text-red-400"><?= date('d M Y', strtotime($m['expiry_date'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>