<?php
require_once '../config.php';
if (!isLoggedIn()) redirect('../login.php');

$admin_id = $_SESSION['admin_id'];

$stmt = $pdo->prepare("SELECT * FROM membership_plans WHERE admin_id = ? ORDER BY price ASC");
$stmt->execute([$admin_id]);
$plans = $stmt->fetchAll();
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<div class="ml-64 p-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold">Membership Plans</h1>
        <a href="add.php" class="bg-orange-500 hover:bg-orange-600 px-6 py-3 rounded-xl font-semibold flex items-center gap-2">
            <i class="fas fa-plus"></i> Create New Plan
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach($plans as $plan): ?>
        <div class="bg-gray-900 rounded-3xl p-8 hover:border-orange-500 border border-transparent transition">
            <div class="flex justify-between items-start">
                <div>
                    <h3 class="text-2xl font-bold"><?= htmlspecialchars($plan['plan_name']) ?></h3>
                    <p class="text-orange-400 text-4xl font-semibold mt-4">
                        ₹<?= number_format($plan['price']) ?>
                    </p>
                </div>
                <span class="px-5 py-2 bg-gray-800 rounded-2xl text-sm">
                    <?= $plan['duration_months'] ?> Months
                </span>
            </div>

            <p class="mt-6 text-gray-400"><?= htmlspecialchars($plan['description'] ?? 'No description') ?></p>

            <div class="mt-8 flex gap-3">
                <a href="edit.php?id=<?= $plan['id'] ?>" 
                   class="flex-1 text-center py-4 bg-gray-800 hover:bg-gray-700 rounded-2xl">
                    Edit
                </a>
                <a href="delete.php?id=<?= $plan['id'] ?>" 
                   onclick="return confirm('Delete this plan? Members using it may be affected.')"
                   class="flex-1 text-center py-4 bg-red-600/20 hover:bg-red-600/30 text-red-400 rounded-2xl">
                    Delete
                </a>
            </div>
        </div>
        <?php endforeach; ?>

        <?php if(empty($plans)): ?>
        <div class="col-span-3 text-center py-20 text-gray-400">
            <p class="text-6xl mb-4">🏋️</p>
            <p>No plans created yet. Create your first membership plan!</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>