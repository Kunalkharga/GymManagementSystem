<?php
require_once '../config.php';
if (!isLoggedIn()) redirect('../login.php');

$admin_id = $_SESSION['admin_id'];
$error = '';
$success = false;

// Final Confirmation Step
if ($_POST && isset($_POST['confirm_delete'])) {
    $confirm_text = sanitize($_POST['confirm_text']);
    
    // Security Check: User must type "DELETE MY GYM"
    if ($confirm_text === "DELETE MY GYM") {
        
        try {
            $pdo->beginTransaction();

            // Delete in correct order (due to foreign keys)
            $pdo->prepare("DELETE FROM payments WHERE admin_id = ?")->execute([$admin_id]);
            $pdo->prepare("DELETE FROM attendance WHERE member_id IN (SELECT id FROM members WHERE admin_id = ?)")->execute([$admin_id]);
            $pdo->prepare("DELETE FROM notifications WHERE admin_id = ?")->execute([$admin_id]);
            $pdo->prepare("DELETE FROM members WHERE admin_id = ?")->execute([$admin_id]);
            $pdo->prepare("DELETE FROM membership_plans WHERE admin_id = ?")->execute([$admin_id]);
            
            // Finally delete the admin
            $pdo->prepare("DELETE FROM admins WHERE id = ?")->execute([$admin_id]);

            $pdo->commit();

            // Destroy session
            session_destroy();
            
            $success = true;

        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Failed to delete account. Please try again.";
        }
    } else {
        $error = "Please type exactly: DELETE MY GYM";
    }
}
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<div class="ml-64 p-8">
    <div class="max-w-lg mx-auto">
        <div class="bg-red-900/20 border border-red-500/50 rounded-3xl p-10">
            <div class="text-center mb-8">
                <div class="text-red-500 text-6xl mb-4">⚠️</div>
                <h1 class="text-3xl font-bold text-red-400">Delete Gym Account</h1>
                <p class="text-gray-400 mt-3">This action is permanent and cannot be undone.</p>
            </div>

            <?php if($success): ?>
                <div class="bg-green-500/10 border border-green-500 text-green-400 p-6 rounded-2xl text-center">
                    <h2 class="text-2xl font-bold mb-2">Account Deleted Successfully</h2>
                    <p class="mb-6">Thank you for using GymSaas. We hope to see you again!</p>
                    <a href="../login.php" class="inline-block bg-white text-black px-8 py-3 rounded-xl font-semibold">
                        Go to Login Page
                    </a>
                </div>
            <?php else: ?>

                <?php if($error): ?>
                <div class="bg-red-500/10 border border-red-500 text-red-400 p-4 rounded-2xl mb-6 text-center">
                    <?= htmlspecialchars($error) ?>
                </div>
                <?php endif; ?>

                <div class="bg-gray-900 rounded-2xl p-8 mb-8">
                    <h3 class="font-semibold text-red-400 mb-4">What will be deleted:</h3>
                    <ul class="space-y-2 text-sm text-gray-300">
                        <li class="flex items-center gap-2">✅ All Members & Photos</li>
                        <li class="flex items-center gap-2">✅ All Membership Plans</li>
                        <li class="flex items-center gap-2">✅ All Payment Records</li>
                        <li class="flex items-center gap-2">✅ Attendance History</li>
                        <li class="flex items-center gap-2">✅ Notifications</li>
                        <li class="flex items-center gap-2">✅ Your Gym Profile</li>
                    </ul>
                </div>

                <form method="POST" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium mb-3 text-red-400">
                            Type <span class="font-mono bg-gray-800 px-2 py-1 rounded">DELETE MY GYM</span> to confirm
                        </label>
                        <input type="text" name="confirm_text" required
                               class="w-full bg-gray-900 border border-red-500/50 rounded-2xl px-6 py-4 focus:outline-none focus:border-red-500"
                               placeholder="DELETE MY GYM">
                    </div>

                    <button type="submit" name="confirm_delete" 
                            onclick="return confirm('Are you 100% sure? This cannot be undone!')"
                            class="w-full bg-red-600 hover:bg-red-700 py-5 rounded-2xl font-bold text-lg transition">
                        YES, DELETE MY ENTIRE GYM ACCOUNT
                    </button>
                </form>

                <div class="text-center mt-8">
                    <a href="index.php" class="text-gray-400 hover:text-white">
                        ← Cancel and Go Back
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>