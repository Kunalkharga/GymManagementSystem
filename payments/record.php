<?php
require_once '../config.php';
if (!isLoggedIn()) redirect('../login.php');

$admin_id = $_SESSION['admin_id'];

// Get Active Members
$stmt = $pdo->prepare("SELECT id, full_name, phone FROM members WHERE admin_id = ? AND status = 'active'");
$stmt->execute([$admin_id]);
$members = $stmt->fetchAll();

if ($_POST) {
    $stmt = $pdo->prepare("INSERT INTO payments (member_id, admin_id, amount, payment_date, payment_method, notes) 
                           VALUES (?, ?, ?, ?, ?, ?)");
    
    $stmt->execute([
        (int)$_POST['member_id'],
        $admin_id,
        (float)$_POST['amount'],
        $_POST['payment_date'],
        $_POST['payment_method'],
        sanitize($_POST['notes'])
    ]);

    // Optional: Update member expiry if needed (for renewal logic)
    redirect('index.php?success=1');
}
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<div class="ml-64 p-8 max-w-xl">
    <h1 class="text-3xl font-bold mb-8">Record New Payment</h1>

    <form method="POST" class="bg-gray-900 p-8 rounded-3xl space-y-6">
        <div>
            <label class="block text-sm mb-2">Member</label>
            <select name="member_id" required class="w-full bg-gray-800 border border-gray-700 rounded-2xl px-6 py-4">
                <?php foreach($members as $m): ?>
                    <option value="<?= $m['id'] ?>">
                        <?= htmlspecialchars($m['full_name']) ?> (<?= $m['phone'] ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="grid grid-cols-2 gap-6">
            <div>
                <label class="block text-sm mb-2">Amount (₹)</label>
                <input type="number" name="amount" step="0.01" required
                       class="w-full bg-gray-800 border border-gray-700 rounded-2xl px-6 py-4">
            </div>
            <div>
                <label class="block text-sm mb-2">Payment Date</label>
                <input type="date" name="payment_date" value="<?= date('Y-m-d') ?>" required
                       class="w-full bg-gray-800 border border-gray-700 rounded-2xl px-6 py-4">
            </div>
        </div>

        <div>
            <label class="block text-sm mb-2">Payment Method</label>
            <select name="payment_method" class="w-full bg-gray-800 border border-gray-700 rounded-2xl px-6 py-4">
                <option value="cash">Cash</option>
                <option value="bank">Bank Transfer</option>
                <option value="online">Online (UPI/Card)</option>
            </select>
        </div>

        <div>
            <label class="block text-sm mb-2">Notes (Optional)</label>
            <textarea name="notes" rows="3" 
                      class="w-full bg-gray-800 border border-gray-700 rounded-2xl px-6 py-4"></textarea>
        </div>

        <button type="submit" 
                class="w-full bg-orange-500 hover:bg-orange-600 py-5 rounded-2xl font-semibold text-lg">
            Record Payment
        </button>
    </form>
</div>

<?php include '../includes/footer.php'; ?>