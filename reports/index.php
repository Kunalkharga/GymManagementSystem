<?php
require_once '../config.php';
if (!isLoggedIn()) redirect('../login.php');

$admin_id = $_SESSION['admin_id'];
updateExpiryStatus($pdo, $admin_id);

// Monthly Revenue (Last 6 Months)
$stmt = $pdo->prepare("SELECT DATE_FORMAT(payment_date, '%Y-%m') as month, SUM(amount) as revenue 
                       FROM payments 
                       WHERE admin_id = ? 
                       GROUP BY month 
                       ORDER BY month DESC LIMIT 6");
$stmt->execute([$admin_id]);
$revenueData = $stmt->fetchAll();

// Stats
$activeCount = $pdo->query("SELECT COUNT(*) FROM members WHERE admin_id = $admin_id AND status = 'active'")->fetchColumn();
$expiredCount = $pdo->query("SELECT COUNT(*) FROM members WHERE admin_id = $admin_id AND status = 'expired'")->fetchColumn();

// Gender Stats
$genderStats = $pdo->query("SELECT gender, COUNT(*) as count FROM members WHERE admin_id = $admin_id GROUP BY gender")->fetchAll();
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<div class="lg:ml-64 min-h-screen pt-16 lg:pt-0">
    <div class="p-4 lg:p-8">
        
        <h1 class="text-2xl lg:text-3xl font-bold mb-8">Reports & Analytics</h1>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- Revenue Report -->
            <div class="bg-gray-900 rounded-3xl p-6 lg:p-8">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                    <h2 class="text-xl font-semibold">Monthly Revenue</h2>
                    <a href="export.php?type=revenue" 
                       class="text-orange-400 hover:text-orange-500 flex items-center gap-2 text-sm">
                        <i class="fas fa-download"></i> Export CSV
                    </a>
                </div>
                
                <div class="space-y-4">
                    <?php foreach($revenueData as $row): ?>
                    <div class="flex justify-between items-center bg-gray-800 p-4 lg:p-5 rounded-2xl">
                        <p class="font-medium"><?= date('M Y', strtotime($row['month'] . '-01')) ?></p>
                        <p class="text-xl lg:text-2xl font-bold text-orange-400">₹<?= number_format($row['revenue']) ?></p>
                    </div>
                    <?php endforeach; ?>

                    <?php if(empty($revenueData)): ?>
                    <p class="text-gray-400 text-center py-10">No revenue data available yet.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Membership Overview -->
            <div class="bg-gray-900 rounded-3xl p-6 lg:p-8">
                <h2 class="text-xl font-semibold mb-6">Membership Overview</h2>
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-green-900/30 p-6 rounded-3xl text-center">
                        <p class="text-green-400 text-sm">Active Members</p>
                        <p class="text-4xl lg:text-5xl font-bold mt-3"><?= $activeCount ?></p>
                    </div>
                    <div class="bg-red-900/30 p-6 rounded-3xl text-center">
                        <p class="text-red-400 text-sm">Expired Members</p>
                        <p class="text-4xl lg:text-5xl font-bold mt-3"><?= $expiredCount ?></p>
                    </div>
                </div>

                <!-- Gender Stats -->
                <div class="mt-8">
                    <h3 class="font-medium mb-4">Members by Gender</h3>
                    <div class="space-y-3">
                        <?php foreach($genderStats as $g): ?>
                        <div class="flex justify-between items-center bg-gray-800 px-5 py-4 rounded-2xl">
                            <span class="font-medium"><?= $g['gender'] ?></span>
                            <span class="font-bold"><?= $g['count'] ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <a href="export.php?type=members" 
                   class="mt-8 block text-center bg-orange-500 hover:bg-orange-600 py-4 rounded-2xl font-semibold">
                    Download Full Member List (CSV)
                </a>
            </div>
        </div>

        <!-- Expired Members Section -->
        <div class="mt-8 bg-gray-900 rounded-3xl p-6 lg:p-8">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                <h2 class="text-xl font-semibold">Recently Expired Memberships</h2>
                <a href="export.php?type=expired" 
                   class="text-orange-400 hover:text-orange-500 flex items-center gap-2">
                    <i class="fas fa-download"></i> Export
                </a>
            </div>
            
            <?php
            $expiredMembers = $pdo->query("SELECT full_name, phone, expiry_date 
                                          FROM members 
                                          WHERE admin_id = $admin_id AND status = 'expired' 
                                          ORDER BY expiry_date DESC LIMIT 15")->fetchAll();
            ?>
            
            <div class="overflow-x-auto">
                <table class="w-full min-w-[600px]">
                    <thead class="bg-gray-800">
                        <tr>
                            <th class="text-left p-4 lg:p-6">Member Name</th>
                            <th class="text-left p-4 lg:p-6 hidden sm:table-cell">Phone</th>
                            <th class="text-left p-4 lg:p-6">Expired On</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($expiredMembers as $m): ?>
                        <tr class="border-t border-gray-800 hover:bg-gray-800/70">
                            <td class="p-4 lg:p-6"><?= htmlspecialchars($m['full_name']) ?></td>
                            <td class="p-4 lg:p-6 hidden sm:table-cell text-gray-400"><?= $m['phone'] ?></td>
                            <td class="p-4 lg:p-6 text-red-400"><?= date('d M Y', strtotime($m['expiry_date'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if(empty($expiredMembers)): ?>
            <div class="text-center py-12 text-gray-400">
                No expired memberships.
            </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<?php include '../includes/footer.php'; ?>