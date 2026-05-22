<?php
require_once '../config.php';
if (!isLoggedIn()) redirect('../login.php');

$admin_id = $_SESSION['admin_id'];

if ($_POST) {
    $stmt = $pdo->prepare("INSERT INTO membership_plans 
        (admin_id, plan_name, duration_months, price, description) 
        VALUES (?, ?, ?, ?, ?)");
    
    $stmt->execute([
        $admin_id,
        sanitize($_POST['plan_name']),
        (int)$_POST['duration_months'],
        (float)$_POST['price'],
        sanitize($_POST['description'])
    ]);

    redirect('index.php?success=1');
}
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<div class="ml-64 p-8 max-w-lg">
    <h1 class="text-3xl font-bold mb-8">Create New Membership Plan</h1>

    <form method="POST" class="bg-gray-900 p-8 rounded-3xl space-y-6">
        <div>
            <label class="block text-sm mb-2">Plan Name</label>
            <input type="text" name="plan_name" placeholder="e.g. Monthly Fitness" required
                   class="w-full bg-gray-800 border border-gray-700 rounded-2xl px-6 py-4 focus:outline-none focus:border-orange-500">
        </div>

        <div class="grid grid-cols-2 gap-6">
            <div>
                <label class="block text-sm mb-2">Duration (Months)</label>
                <select name="duration_months" required 
                        class="w-full bg-gray-800 border border-gray-700 rounded-2xl px-6 py-4">
                    <option value="1">1 Month</option>
                    <option value="3">3 Months</option>
                    <option value="6">6 Months</option>
                    <option value="12" selected>12 Months (Yearly)</option>
                </select>
            </div>
            <div>
                <label class="block text-sm mb-2">Price (₹)</label>
                <input type="number" name="price" step="0.01" required
                       class="w-full bg-gray-800 border border-gray-700 rounded-2xl px-6 py-4">
            </div>
        </div>

        <div>
            <label class="block text-sm mb-2">Description (Optional)</label>
            <textarea name="description" rows="4"
                      class="w-full bg-gray-800 border border-gray-700 rounded-2xl px-6 py-4"></textarea>
        </div>

        <button type="submit" 
                class="w-full bg-orange-500 hover:bg-orange-600 py-5 rounded-2xl font-semibold text-lg transition">
            Create Plan
        </button>
    </form>
</div>

<?php include '../includes/footer.php'; ?>