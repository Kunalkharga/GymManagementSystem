<?php
require_once '../config.php';
if (!isLoggedIn()) redirect('../login.php');

$admin_id = $_SESSION['admin_id'];

// Fetch current admin data
$stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
$stmt->execute([$admin_id]);
$admin = $stmt->fetch();

$success = isset($_GET['success']) ? (int)$_GET['success'] : 0;
$error = '';
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<div class="lg:ml-64 min-h-screen pt-16 lg:pt-0">
    <div class="p-4 lg:p-8 max-w-2xl mx-auto">
        
        <h1 class="text-2xl lg:text-3xl font-bold mb-8">Gym Settings</h1>

        <?php if($success === 1): ?>
            <div class="bg-green-600/20 border border-green-500 text-green-400 p-4 rounded-2xl mb-6">
                ✅ Profile updated successfully!
            </div>
        <?php elseif($success === 2): ?>
            <div class="bg-green-600/20 border border-green-500 text-green-400 p-4 rounded-2xl mb-6">
                ✅ Password changed successfully!
            </div>
        <?php endif; ?>

        <?php if(!empty($error)): ?>
            <div class="bg-red-600/20 border border-red-500 text-red-400 p-4 rounded-2xl mb-6">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- Profile Information -->
        <div class="bg-gray-900 rounded-3xl p-6 lg:p-8 mb-8">
            <h2 class="text-xl font-semibold mb-6 flex items-center gap-3">
                <i class="fas fa-user-circle"></i> Gym & Owner Profile
            </h2>

            <form method="POST" class="space-y-6">
                <input type="hidden" name="update_profile" value="1">

                <div>
                    <label class="block text-sm mb-2">Gym Name</label>
                    <input type="text" name="gym_name" value="<?= htmlspecialchars($admin['gym_name']) ?>" required
                           class="w-full bg-gray-800 border border-gray-700 rounded-2xl px-5 py-4 focus:outline-none focus:border-orange-500">
                </div>

                <div>
                    <label class="block text-sm mb-2">Owner Full Name</label>
                    <input type="text" name="full_name" value="<?= htmlspecialchars($admin['full_name']) ?>" required
                           class="w-full bg-gray-800 border border-gray-700 rounded-2xl px-5 py-4">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm mb-2">Email (Cannot Change)</label>
                        <input type="email" value="<?= htmlspecialchars($admin['email']) ?>" disabled
                               class="w-full bg-gray-800 border border-gray-700 rounded-2xl px-5 py-4 text-gray-400">
                    </div>
                    <div>
                        <label class="block text-sm mb-2">Phone Number</label>
                        <input type="tel" name="phone" value="<?= htmlspecialchars($admin['phone'] ?? '') ?>"
                               class="w-full bg-gray-800 border border-gray-700 rounded-2xl px-5 py-4">
                    </div>
                </div>

                <button type="submit" 
                        class="w-full bg-orange-500 hover:bg-orange-600 py-4 rounded-2xl font-semibold text-lg transition">
                    Update Profile
                </button>
            </form>
        </div>

        <!-- Change Password -->
        <div class="bg-gray-900 rounded-3xl p-6 lg:p-8">
            <h2 class="text-xl font-semibold mb-6 flex items-center gap-3">
                <i class="fas fa-lock"></i> Change Password
            </h2>

            <form method="POST" class="space-y-6">
                <input type="hidden" name="change_password" value="1">

                <div>
                    <label class="block text-sm mb-2">Current Password</label>
                    <input type="password" name="current_password" required
                           class="w-full bg-gray-800 border border-gray-700 rounded-2xl px-5 py-4">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm mb-2">New Password</label>
                        <input type="password" name="new_password" required minlength="6"
                               class="w-full bg-gray-800 border border-gray-700 rounded-2xl px-5 py-4">
                    </div>
                    <div>
                        <label class="block text-sm mb-2">Confirm New Password</label>
                        <input type="password" name="confirm_password" required minlength="6"
                               class="w-full bg-gray-800 border border-gray-700 rounded-2xl px-5 py-4">
                    </div>
                </div>

                <button type="submit" 
                        class="w-full bg-gray-700 hover:bg-gray-600 py-4 rounded-2xl font-semibold text-lg transition">
                    Change Password
                </button>
            </form>
        </div>

        <!-- Danger Zone -->
        <div class="mt-10 bg-red-900/20 border border-red-500/30 rounded-3xl p-6 lg:p-8">
            <h3 class="text-red-400 font-semibold mb-3">Danger Zone</h3>
            <p class="text-gray-400 text-sm mb-6">These actions are permanent and cannot be undone.</p>
            
            <a href="delete-account.php" 
               onclick="return confirm('This will permanently delete your entire gym account and all data. Are you sure?')"
               class="inline-flex items-center gap-3 text-red-400 hover:text-red-500 font-medium">
                <i class="fas fa-trash"></i> Delete My Gym Account
            </a>
        </div>

    </div>
</div>

<?php include '../includes/footer.php'; ?>