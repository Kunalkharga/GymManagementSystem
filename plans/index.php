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

<div class="lg:ml-64 min-h-screen pt-16 lg:pt-0">
    <div class="p-4 lg:p-8">
        
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
            <h1 class="text-2xl lg:text-3xl font-bold">Membership Plans</h1>
            <a href="add.php" 
               class="bg-orange-500 hover:bg-orange-600 px-6 py-3 rounded-2xl font-semibold flex items-center gap-2 text-sm lg:text-base">
                <i class="fas fa-plus"></i> Create New Plan
            </a>
        </div>

        <?php if(empty($plans)): ?>
            <div class="bg-gray-900 rounded-3xl p-12 text-center">
                <p class="text-6xl mb-4">🏋️</p>
                <h3 class="text-xl font-semibold mb-2">No Plans Created Yet</h3>
                <p class="text-gray-400 mb-6">Create your first membership plan to get started.</p>
                <a href="add.php" class="bg-orange-500 hover:bg-orange-600 px-8 py-4 rounded-2xl inline-block">
                    Create First Plan
                </a>
            </div>
        <?php else: ?>

        <!-- Plans Grid - Responsive -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-6">
            <?php foreach($plans as $plan): ?>
            <div class="bg-gray-900 rounded-3xl p-6 lg:p-8 hover:border-orange-500 border border-transparent transition-all">
                
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="text-xl lg:text-2xl font-bold"><?= htmlspecialchars($plan['plan_name']) ?></h3>
                        <p class="text-orange-400 text-3xl lg:text-4xl font-semibold mt-4">
                            ₹<?= number_format($plan['price']) ?>
                        </p>
                    </div>
                    <span class="px-5 py-2 bg-gray-800 text-sm rounded-2xl">
                        <?= $plan['duration_months'] ?> Months
                    </span>
                </div>

                <p class="mt-6 text-gray-400 text-sm line-clamp-3">
                    <?= htmlspecialchars($plan['description'] ?? 'No description available.') ?>
                </p>

                <div class="mt-8 flex flex-col sm:flex-row gap-3">
                    <a href="edit.php?id=<?= $plan['id'] ?>" 
                       class="flex-1 text-center py-4 bg-gray-800 hover:bg-gray-700 rounded-2xl font-medium transition">
                        Edit Plan
                    </a>
                    <a href="delete.php?id=<?= $plan['id'] ?>" 
                       onclick="return confirm('Delete this plan? This may affect existing members.')"
                       class="flex-1 text-center py-4 bg-red-600/20 hover:bg-red-600/30 text-red-400 rounded-2xl font-medium transition">
                        Delete
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php endif; ?>

    </div>
</div>

<?php include '../includes/footer.php'; ?>