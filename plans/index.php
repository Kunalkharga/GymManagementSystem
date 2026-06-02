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
        
        <!-- Success Toast -->
        <?php if(isset($_GET['success'])): ?>
        <div id="successToast" class="fixed top-6 right-6 bg-green-600 text-white px-6 py-4 rounded-2xl shadow-2xl flex items-center gap-3 z-50">
            <i class="fas fa-check-circle text-xl"></i>
            <span class="flex-1">
                <?php 
                if($_GET['success'] == 'added') echo "✅ New Plan Created Successfully!";
                elseif($_GET['success'] == 'updated') echo "✅ Plan Updated Successfully!";
                elseif($_GET['success'] == 'deleted') echo "✅ Plan Deleted Successfully!";
                ?>
            </span>
            <button onclick="hideToast()" class="text-white/70 hover:text-white">✕</button>
            <div class="absolute bottom-0 left-0 h-1 bg-green-300 rounded-b-2xl" id="progressBar" style="width: 100%; transition: width 5s linear;"></div>
        </div>
        <?php endif; ?>

        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
            <h1 class="text-2xl lg:text-3xl font-bold">Membership Plans</h1>
            <a href="add.php" class="bg-orange-500 hover:bg-orange-600 px-6 py-3 rounded-2xl font-semibold flex items-center gap-2">
                <i class="fas fa-plus"></i> Create New Plan
            </a>
        </div>

        <?php if(empty($plans)): ?>
            <div class="bg-gray-900 rounded-3xl p-12 text-center">
                <p class="text-6xl mb-4">🏋️</p>
                <h3 class="text-xl font-semibold">No Plans Yet</h3>
                <p class="text-gray-400 mt-2">Create your first membership plan</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach($plans as $plan): ?>
                <div class="bg-gray-900 rounded-3xl p-6 lg:p-8 hover:border-orange-500 border border-transparent transition">
                    <div class="flex justify-between">
                        <h3 class="text-xl font-bold"><?= htmlspecialchars($plan['plan_name']) ?></h3>
                        <span class="px-4 py-1 bg-gray-800 rounded-xl text-sm"><?= $plan['duration_months'] ?> Months</span>
                    </div>
                    <p class="text-3xl font-semibold text-orange-400 mt-4">₹<?= number_format($plan['price']) ?></p>
                    
                    <p class="mt-6 text-gray-400 text-sm"><?= htmlspecialchars($plan['description'] ?? '') ?></p>

                    <div class="mt-8 flex gap-3">
                        <a href="edit.php?id=<?= $plan['id'] ?>" class="flex-1 text-center py-4 bg-gray-800 hover:bg-gray-700 rounded-2xl">Edit</a>
                        <a href="delete.php?id=<?= $plan['id'] ?>" 
                           onclick="return confirm('Delete this plan?')" 
                           class="flex-1 text-center py-4 bg-red-600/20 hover:bg-red-600/30 text-red-400 rounded-2xl">Delete</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
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
        }, 5000);
    }
};
function hideToast() {
    const toast = document.getElementById('successToast');
    if (toast) toast.style.display = 'none';
}
</script>

<?php include '../includes/footer.php'; ?>