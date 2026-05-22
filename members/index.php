<?php
require_once '../config.php';
if (!isLoggedIn()) redirect('../login.php');

$admin_id = $_SESSION['admin_id'];

// Handle Search & Filter
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$status = isset($_GET['status']) ? sanitize($_GET['status']) : '';

$sql = "SELECT m.*, p.plan_name FROM members m 
        LEFT JOIN membership_plans p ON m.membership_plan_id = p.id 
        WHERE m.admin_id = ?";

$params = [$admin_id];

if ($search) {
    $sql .= " AND (m.full_name LIKE ? OR m.phone LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($status) {
    $sql .= " AND m.status = ?";
    $params[] = $status;
}

$sql .= " ORDER BY m.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$members = $stmt->fetchAll();

// Auto update expiry
updateExpiryStatus($pdo, $admin_id);

?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<div class="ml-64 p-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold">All Members</h1>
        <a href="add.php" class="bg-orange-500 hover:bg-orange-600 px-6 py-3 rounded-xl font-semibold flex items-center gap-2">
            <i class="fas fa-plus"></i> Add New Member
        </a>
    </div>

    <!-- Search & Filter -->
    <div class="bg-gray-900 p-5 rounded-2xl mb-8 flex flex-wrap gap-4">
        <form method="GET" class="flex-1 flex gap-4">
            <input type="text" name="search" value="<?= $search ?>" placeholder="Search by name or phone..." 
                   class="flex-1 bg-gray-800 border border-gray-700 rounded-xl px-5 py-3 focus:outline-none focus:border-orange-500">
            
            <select name="status" class="bg-gray-800 border border-gray-700 rounded-xl px-5 py-3">
                <option value="">All Status</option>
                <option value="active" <?= $status=='active'?'selected':'' ?>>Active</option>
                <option value="expired" <?= $status=='expired'?'selected':'' ?>>Expired</option>
            </select>
            
            <button type="submit" class="bg-gray-700 hover:bg-gray-600 px-8 rounded-xl">Filter</button>
        </form>
    </div>

    <div class="bg-gray-900 rounded-2xl overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-800">
                <tr>
                    <th class="text-left p-5">Member</th>
                    <th class="text-left p-5">Phone</th>
                    <th class="text-left p-5">Plan</th>
                    <th class="text-left p-5">Expiry</th>
                    <th class="text-left p-5">Status</th>
                    <th class="text-center p-5">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($members as $member): 
                    $isExpired = strtotime($member['expiry_date']) < time();
                ?>
                <tr class="border-t border-gray-800 hover:bg-gray-800/50">
                    <td class="p-5">
                        <div class="flex items-center gap-3">
                            <?php if($member['photo']): ?>
                                <img src="../uploads/members/<?= $member['photo'] ?>" class="w-10 h-10 rounded-full object-cover">
                            <?php else: ?>
                                <div class="w-10 h-10 bg-gray-700 rounded-full flex items-center justify-center">
                                    <i class="fas fa-user"></i>
                                </div>
                            <?php endif; ?>
                            <div>
                                <p class="font-semibold"><?= htmlspecialchars($member['full_name']) ?></p>
                                <p class="text-sm text-gray-400"><?= $member['age'] ?> years • <?= $member['gender'] ?></p>
                            </div>
                        </div>
                    </td>
                    <td class="p-5"><?= htmlspecialchars($member['phone']) ?></td>
                    <td class="p-5"><?= htmlspecialchars($member['plan_name'] ?? 'N/A') ?></td>
                    <td class="p-5"><?= date('d M Y', strtotime($member['expiry_date'])) ?></td>
                    <td class="p-5">
                        <span class="px-4 py-1 rounded-full text-sm <?= $isExpired ? 'bg-red-500/20 text-red-400' : 'bg-green-500/20 text-green-400' ?>">
                            <?= $isExpired ? 'Expired' : 'Active' ?>
                        </span>
                    </td>
                    <td class="p-5 text-center">
                        <a href="profile.php?id=<?= $member['id'] ?>" class="text-blue-400 hover:text-blue-500 mr-4">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="edit.php?id=<?= $member['id'] ?>" class="text-orange-400 hover:text-orange-500 mr-4">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="delete.php?id=<?= $member['id'] ?>" onclick="return confirm('Delete this member?')" class="text-red-400 hover:text-red-500">
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>