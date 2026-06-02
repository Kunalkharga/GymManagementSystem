<?php
require_once '../config.php';
if (!isLoggedIn()) redirect('../login.php');

$admin_id = $_SESSION['admin_id'];
$member_id = (int)$_GET['id'];

// Fetch member
$stmt = $pdo->prepare("SELECT * FROM members WHERE id = ? AND admin_id = ?");
$stmt->execute([$member_id, $admin_id]);
$member = $stmt->fetch();

if (!$member) redirect('index.php');

$plans = $pdo->query("SELECT * FROM membership_plans WHERE admin_id = $admin_id ORDER BY price ASC")->fetchAll();

if ($_POST) {
    $plan_id = (int)$_POST['plan_id'];
    $start_date = $_POST['start_date'];
    $payment_method = $_POST['payment_method'];
    $amount = (float)$_POST['amount'];

    // Get plan details
    $stmt = $pdo->prepare("SELECT plan_name, duration_months FROM membership_plans WHERE id = ?");
    $stmt->execute([$plan_id]);
    $plan = $stmt->fetch();

    // Update member
    $stmt = $pdo->prepare("UPDATE members SET 
        membership_plan_id = ?, 
        start_date = ?, 
        expiry_date = DATE_ADD(?, INTERVAL ? MONTH), 
        status = 'active' 
        WHERE id = ?");
    $stmt->execute([$plan_id, $start_date, $start_date, $plan['duration_months'], $member_id]);

    // Create NEW payment record
    $stmt = $pdo->prepare("INSERT INTO payments 
        (member_id, admin_id, plan_name, amount, payment_method, payment_date, start_date, expiry_date) 
        VALUES (?, ?, ?, ?, ?, ?, ?, DATE_ADD(?, INTERVAL ? MONTH))");
    $stmt->execute([
        $member_id,
        $admin_id,
        $plan['plan_name'],
        $amount,
        $payment_method,
        $start_date,
        $start_date,
        $start_date,
        $plan['duration_months']
    ]);

    redirect("profile.php?id=$member_id&renewed=1");
}
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<div class="lg:ml-64 min-h-screen pt-16 lg:pt-0">
    <div class="p-4 lg:p-8 max-w-2xl mx-auto">
        
        
        <a href="profile.php?id=<?= $member_id ?>" class="inline-flex items-center gap-2 text-gray-400 hover:text-white mb-6">
            ← Back to Profile
        </a>

        <h1 class="text-3xl font-bold mb-8">Renew Membership</h1>

        <div class="bg-gray-900 rounded-3xl p-8">
            <h2 class="text-2xl font-semibold mb-6"><?= htmlspecialchars($member['full_name']) ?></h2>

            <form method="POST" class="space-y-8">
                
                <div>
                    <label class="block text-sm mb-2">New Membership Plan</label>
                    <select name="plan_id" required class="w-full bg-gray-800 border border-gray-700 rounded-2xl px-5 py-4">
                        <?php foreach($plans as $plan): ?>
                            <option value="<?= $plan['id'] ?>" 
                                <?= $member['membership_plan_id'] == $plan['id'] ? 'selected' : '' ?>>
                                <?= $plan['plan_name'] ?> - ₹<?= number_format($plan['price']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm mb-2">Amount (₹)</label>
                    <input type="number" name="amount" step="0.01" required 
                           value="<?= $member['membership_plan_id'] ? 
                               (function() use ($pdo, $member) {
                                   $s = $pdo->prepare("SELECT price FROM membership_plans WHERE id = ?");
                                   $s->execute([$member['membership_plan_id']]);
                                   return $s->fetchColumn();
                               })() : '' ?>" 
                           class="w-full bg-gray-800 border border-gray-700 rounded-2xl px-5 py-4">
                </div>

                <div>
                    <label class="block text-sm mb-2">New Start Date</label>
                    <input type="date" name="start_date" value="<?= date('Y-m-d') ?>" required 
                           class="w-full bg-gray-800 border border-gray-700 rounded-2xl px-5 py-4">
                </div>

                <div>
                    <label class="block text-sm mb-2">Payment Method</label>
                    <select name="payment_method" required class="w-full bg-gray-800 border border-gray-700 rounded-2xl px-5 py-4">
                        <option value="cash">Cash</option>
                        <option value="esewa">eSewa</option>
                        <option value="khalti">Khalti</option>
                        <option value="bank">Bank Transfer</option>
                    </select>
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 py-5 rounded-2xl font-semibold text-lg">
                    Renew Membership & Record Payment
                </button>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>