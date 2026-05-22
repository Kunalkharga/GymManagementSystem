<?php
require_once '../config.php';
if (!isLoggedIn()) redirect('../login.php');

$admin_id = $_SESSION['admin_id'];
$plan_id = (int)$_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM membership_plans WHERE id = ? AND admin_id = ?");
$stmt->execute([$plan_id, $admin_id]);
$plan = $stmt->fetch();

if (!$plan) redirect('index.php');

if ($_POST) {
    $stmt = $pdo->prepare("UPDATE membership_plans SET 
        plan_name=?, duration_months=?, price=?, description=? 
        WHERE id=? AND admin_id=?");
    
    $stmt->execute([
        sanitize($_POST['plan_name']),
        (int)$_POST['duration_months'],
        (float)$_POST['price'],
        sanitize($_POST['description']),
        $plan_id,
        $admin_id
    ]);

    redirect('index.php?updated=1');
}
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<div class="ml-64 p-8 max-w-lg">
    <h1 class="text-3xl font-bold mb-8">Edit Plan</h1>

    <form method="POST" class="bg-gray-900 p-8 rounded-3xl space-y-6">
        <!-- Same fields as add.php with value populated -->
        <div>
            <label>Plan Name</label>
            <input type="text" name="plan_name" value="<?= htmlspecialchars($plan['plan_name']) ?>" required
                   class="w-full mt-2 bg-gray-800 border border-gray-700 rounded-2xl px-6 py-4">
        </div>

        <div class="grid grid-cols-2 gap-6">
            <div>
                <label>Duration (Months)</label>
                <select name="duration_months" required class="w-full mt-2 bg-gray-800 border border-gray-700 rounded-2xl px-6 py-4">
                    <?php for($i=1; $i<=12; $i++): ?>
                        <option value="<?= $i ?>" <?= $plan['duration_months']==$i ? 'selected' : '' ?>><?= $i ?> Month<?= $i>1?'s':'' ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div>
                <label>Price (₹)</label>
                <input type="number" name="price" value="<?= $plan['price'] ?>" step="0.01" required
                       class="w-full mt-2 bg-gray-800 border border-gray-700 rounded-2xl px-6 py-4">
            </div>
        </div>

        <div>
            <label>Description</label>
            <textarea name="description" rows="4" class="w-full mt-2 bg-gray-800 border border-gray-700 rounded-2xl px-6 py-4"><?= htmlspecialchars($plan['description'] ?? '') ?></textarea>
        </div>

        <button type="submit" class="w-full bg-orange-500 hover:bg-orange-600 py-5 rounded-2xl font-semibold text-lg">
            Update Plan
        </button>
    </form>
</div>