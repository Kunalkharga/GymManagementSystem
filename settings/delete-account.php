<?php
require_once '../config.php';
if (!isLoggedIn()) redirect('../login.php');

$admin_id = $_SESSION['admin_id'];
$error = '';
$success = false;

if ($_POST && isset($_POST['confirm_delete'])) {
    $confirm_text = strtoupper(trim($_POST['confirm_text']));

    if ($confirm_text === "DELETE MY GYM") {
        try {
            $pdo->beginTransaction();

            // Delete related data first
            $pdo->prepare("DELETE FROM payments WHERE admin_id = ?")->execute([$admin_id]);
            $pdo->prepare("DELETE FROM attendance WHERE member_id IN (SELECT id FROM members WHERE admin_id = ?)")->execute([$admin_id]);
            $pdo->prepare("DELETE FROM notifications WHERE admin_id = ?")->execute([$admin_id]);
            $pdo->prepare("DELETE FROM members WHERE admin_id = ?")->execute([$admin_id]);
            $pdo->prepare("DELETE FROM membership_plans WHERE admin_id = ?")->execute([$admin_id]);

            // Delete gym owner
            $pdo->prepare("DELETE FROM admins WHERE id = ?")->execute([$admin_id]);

            $pdo->commit();

            session_unset();
            session_destroy();
            $success = true;

        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Error deleting account. Please try again.";
        }
    } else {
        $error = "Please type exactly: DELETE MY GYM";
    }
}
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<div class="lg:ml-64 min-h-screen pt-16 lg:pt-0">
    <div class="p-4 lg:p-8 max-w-lg mx-auto">
        
        <a href="index.php" class="inline-flex items-center gap-2 text-gray-400 hover:text-white mb-6">
            ← Back to Settings
        </a>

        <div class="bg-red-900/20 border border-red-500/50 rounded-3xl p-8 lg:p-10">
            <div class="text-center mb-8">
                <div class="text-6xl mb-4">⚠️</div>
                <h1 class="text-3xl font-bold text-red-400">Delete Gym Account</h1>
                <p class="text-gray-400 mt-3">This action is permanent and cannot be undone.</p>
            </div>

            <?php if($success): ?>
                <div class="bg-green-600/20 border border-green-500 text-green-400 p-8 rounded-2xl text-center">
                    <h2 class="text-2xl font-bold mb-4">Account Deleted Successfully</h2>
                    <p class="mb-6">Thank you for using GymSaas.</p>
                    <a href="../login.php" class="inline-block bg-white text-black px-8 py-3 rounded-xl font-semibold">Go to Login</a>
                </div>
            <?php else: ?>

                <?php if($error): ?>
                <div class="bg-red-500/20 border border-red-500 text-red-400 p-4 rounded-2xl mb-6 text-center">
                    <?= htmlspecialchars($error) ?>
                </div>
                <?php endif; ?>

                <div class="bg-gray-900 rounded-2xl p-6 mb-8">
                    <h3 class="font-semibold text-red-400 mb-4">This will permanently delete:</h3>
                    <ul class="space-y-2 text-sm">
                        <li class="flex items-center gap-2">• All Members & Photos</li>
                        <li class="flex items-center gap-2">• All Membership Plans</li>
                        <li class="flex items-center gap-2">• All Payment Records</li>
                        <li class="flex items-center gap-2">• Attendance History</li>
                        <li class="flex items-center gap-2">• Your Complete Gym Profile</li>
                    </ul>
                </div>

                <form method="POST">
                    <div class="mb-6">
                        <label class="block text-red-400 text-sm mb-3">
                            Type <span class="font-mono bg-gray-800 px-3 py-1 rounded">DELETE MY GYM</span> to confirm
                        </label>
                        <input type="text" name="confirm_text" required 
                               class="w-full bg-gray-900 border border-red-500/50 rounded-2xl px-6 py-4 focus:outline-none focus:border-red-500 text-center text-lg"
                               placeholder="DELETE MY GYM">
                    </div>

                    <button type="submit" name="confirm_delete" 
                            onclick="return confirm('FINAL WARNING: This cannot be undone!')"
                            class="w-full bg-red-600 hover:bg-red-700 py-5 rounded-2xl font-bold text-lg">
                        YES, DELETE MY ENTIRE GYM ACCOUNT
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>