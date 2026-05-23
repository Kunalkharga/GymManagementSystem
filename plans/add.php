<?php
require_once '../config.php';
if (!isLoggedIn()) redirect('../login.php');

$admin_id = $_SESSION['admin_id'];

if ($_POST) {
    $stmt = $pdo->prepare("INSERT INTO membership_plans (admin_id, plan_name, duration_months, price, description) 
                           VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        $admin_id,
        sanitize($_POST['plan_name']),
        (int)$_POST['duration_months'],
        (float)$_POST['price'],
        sanitize($_POST['description'])
    ]);
    redirect('index.php?success=added');
}
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<div class="lg:ml-64 min-h-screen pt-16 lg:pt-0">
    <div class="p-4 lg:p-8 max-w-lg mx-auto">
        
        <a href="index.php" class="inline-flex items-center gap-2 text-gray-400 hover:text-white mb-6">
            ← Back to Plans
        </a>

        <h1 class="text-2xl lg:text-3xl font-bold mb-8">Create New Plan</h1>

        <form method="POST" class="bg-gray-900 p-6 lg:p-8 rounded-3xl space-y-6">
            <div>
                <label class="block text-sm mb-2">Plan Name</label>
                <input type="text" name="plan_name" required class="w-full bg-gray-800 border border-gray-700 rounded-2xl px-5 py-4">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm mb-2">Duration (Months)</label>
                    <select name="duration_months" required class="w-full bg-gray-800 border border-gray-700 rounded-2xl px-5 py-4">
                        <option value="1">1 Month</option>
                        <option value="3">3 Months</option>
                        <option value="6">6 Months</option>
                        <option value="12" selected>12 Months</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm mb-2">Price (₹)</label>
                    <input type="number" name="price" step="0.01" required class="w-full bg-gray-800 border border-gray-700 rounded-2xl px-5 py-4">
                </div>
            </div>

            <div>
                <label class="block text-sm mb-2">Description</label>
                <textarea name="description" rows="4" class="w-full bg-gray-800 border border-gray-700 rounded-2xl px-5 py-4"></textarea>
            </div>

            <button type="submit" class="w-full bg-orange-500 hover:bg-orange-600 py-4 rounded-2xl font-semibold text-lg">
                Create Plan
            </button>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>