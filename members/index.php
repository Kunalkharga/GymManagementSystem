<?php
require_once '../config.php';
if (!isLoggedIn()) redirect('../login.php');

$admin_id = $_SESSION['admin_id'];
updateExpiryStatus($pdo, $admin_id);

$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$status = isset($_GET['status']) ? sanitize($_GET['status']) : '';

$sql = "SELECT m.*, p.plan_name FROM members m 
        LEFT JOIN membership_plans p ON m.membership_plan_id = p.id 
        WHERE m.admin_id = ?";
$params = [$admin_id];

if ($search) {
    $sql .= " AND (m.full_name LIKE ? OR m.phone LIKE ?)";
    $params[] = "%$search%"; $params[] = "%$search%";
}
if ($status) {
    $sql .= " AND m.status = ?";
    $params[] = $status;
}
$sql .= " ORDER BY m.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$members = $stmt->fetchAll();
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<div class="lg:ml-64 min-h-screen pt-16 lg:pt-0">
    <div class="p-4 lg:p-8">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
            <h1 class="text-2xl lg:text-3xl font-bold">All Members</h1>
            <a href="add.php" class="bg-orange-500 hover:bg-orange-600 px-6 py-3 rounded-2xl font-semibold flex items-center gap-2 text-sm lg:text-base">
                <i class="fas fa-plus"></i> Add New Member
            </a>
        </div>

        <!-- Search & Filter -->
        <div class="bg-gray-900 p-4 lg:p-6 rounded-3xl mb-8">
            <form method="GET" class="flex flex-col lg:flex-row gap-4">
                <input type="text" name="search" value="<?= $search ?>" placeholder="Search by name or phone..." 
                       class="flex-1 bg-gray-800 border border-gray-700 rounded-2xl px-5 py-4 focus:outline-none focus:border-orange-500">
                
                <select name="status" class="bg-gray-800 border border-gray-700 rounded-2xl px-5 py-4">
                    <option value="">All Members</option>
                    <option value="active" <?= $status=='active'?'selected':'' ?>>Active</option>
                    <option value="expired" <?= $status=='expired'?'selected':'' ?>>Expired</option>
                </select>
                
                <button type="submit" class="bg-orange-500 hover:bg-orange-600 px-8 py-4 rounded-2xl font-medium">Filter</button>
            </form>
        </div>

        <!-- Members Table -->
        <div class="bg-gray-900 rounded-3xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[700px]">
                    <thead class="bg-gray-800">
                        <tr>
                            <th class="text-left p-4 lg:p-6">Member</th>
                            <th class="text-left p-4 lg:p-6 hidden md:table-cell">Phone</th>
                            <th class="text-left p-4 lg:p-6 hidden lg:table-cell">Plan</th>
                            <th class="text-left p-4 lg:p-6">Expiry</th>
                            <th class="text-left p-4 lg:p-6">Status</th>
                            <th class="text-center p-4 lg:p-6">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($members as $member): 
                            $isExpired = strtotime($member['expiry_date']) < time();
                        ?>
                        <tr class="border-t border-gray-800 hover:bg-gray-800/70">
                            <td class="p-4 lg:p-6">
                                <div class="flex items-center gap-3">
                                    <?php if($member['photo']): ?>
                                        <img src="../uploads/members/<?= $member['photo'] ?>" class="w-10 h-10 rounded-full object-cover">
                                    <?php else: ?>
                                        <div class="w-10 h-10 bg-gray-700 rounded-full flex items-center justify-center text-lg">👤</div>
                                    <?php endif; ?>
                                    <div>
                                        <p class="font-semibold"><?= htmlspecialchars($member['full_name']) ?></p>
                                        <p class="text-xs text-gray-400 md:hidden"><?= $member['phone'] ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4 lg:p-6 hidden md:table-cell"><?= htmlspecialchars($member['phone']) ?></td>
                            <td class="p-4 lg:p-6 hidden lg:table-cell"><?= htmlspecialchars($member['plan_name'] ?? 'N/A') ?></td>
                            <td class="p-4 lg:p-6 text-sm"><?= date('d M Y', strtotime($member['expiry_date'])) ?></td>
                            <td class="p-4 lg:p-6">
                                <span class="px-4 py-1 text-xs rounded-full <?= $isExpired ? 'bg-red-500/20 text-red-400' : 'bg-green-500/20 text-green-400' ?>">
                                    <?= $isExpired ? 'Expired' : 'Active' ?>
                                </span>
                            </td>
                            <td class="p-4 lg:p-6 text-center">
                                <a href="profile.php?id=<?= $member['id'] ?>" class="text-blue-400 hover:text-blue-500 mx-2">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="edit.php?id=<?= $member['id'] ?>" class="text-orange-400 hover:text-orange-500 mx-2">
                                    <i class="fas fa-edit"></i>
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

<?php include '../includes/footer.php'; ?>