<?php
require_once '../config.php';
if (!isLoggedIn()) redirect('../login.php');

$admin_id = $_SESSION['admin_id'];
$success_msg = '';
$error = '';

// Handle Profile Update FIRST
if ($_POST && isset($_POST['update_profile'])) {
    $gym_name = sanitize($_POST['gym_name']);
    $full_name = sanitize($_POST['full_name']);
    $phone = sanitize($_POST['phone']);

    $stmt = $pdo->prepare("UPDATE admins SET gym_name = ?, full_name = ?, phone = ? WHERE id = ?");
    $result = $stmt->execute([$gym_name, $full_name, $phone, $admin_id]);

    if ($result) {
        $_SESSION['gym_name'] = $gym_name;
        $success_msg = "✅ Profile Updated Successfully!";
    } else {
        $error = "Failed to update profile.";
    }
}

// Handle Password Change
if ($_POST && isset($_POST['change_password'])) {
    $current_pass = $_POST['current_password'];
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    $stmt = $pdo->prepare("SELECT password FROM admins WHERE id = ?");
    $stmt->execute([$admin_id]);
    $admin = $stmt->fetch();

    if (password_verify($current_pass, $admin['password'])) {
        if ($new_pass === $confirm_pass && strlen($new_pass) >= 6) {
            $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE admins SET password = ? WHERE id = ?");
            $stmt->execute([$hashed, $admin_id]);
            $success_msg = "✅ Password Changed Successfully!";
        } else {
            $error = "New passwords do not match or too short.";
        }
    } else {
        $error = "Current password is incorrect.";
    }
}

// Fetch latest data AFTER update
$stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
$stmt->execute([$admin_id]);
$admin = $stmt->fetch();
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<div class="lg:ml-64 min-h-screen pt-16 lg:pt-0">
    <div class="p-4 lg:p-8 max-w-2xl mx-auto">
        
        <!-- Success Toast -->
        <?php if(!empty($success_msg)): ?>
        <div id="successToast" class="fixed top-6 right-6 bg-green-600 text-white px-6 py-4 rounded-2xl shadow-2xl flex items-center gap-3 z-50">
            <i class="fas fa-check-circle text-xl"></i>
            <span class="flex-1"><?= htmlspecialchars($success_msg) ?></span>
            <button onclick="hideToast()" class="text-white/70 hover:text-white">✕</button>
            <div class="absolute bottom-0 left-0 h-1 bg-green-300 rounded-b-2xl" id="progressBar" style="width: 100%; transition: width 3s linear;"></div>
        </div>
        <?php endif; ?>

        <h1 class="text-2xl lg:text-3xl font-bold mb-8">Gym Settings</h1>

        <?php if(!empty($error)): ?>
        <div class="bg-red-600/20 border border-red-500 text-red-400 p-4 rounded-2xl mb-6">
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <!-- Profile Form -->
        <div class="bg-gray-900 rounded-3xl p-6 lg:p-8 mb-8">
            <h2 class="text-xl font-semibold mb-6">Gym & Owner Profile</h2>

            <form method="POST" class="space-y-6">
                <input type="hidden" name="update_profile" value="1">

                <div>
                    <label class="block text-sm mb-2">Gym Name</label>
                    <input type="text" name="gym_name" value="<?= htmlspecialchars($admin['gym_name']) ?>" required
                           class="w-full bg-gray-800 border border-gray-700 rounded-2xl px-5 py-4">
                </div>

                <div>
                    <label class="block text-sm mb-2">Owner Full Name</label>
                    <input type="text" name="full_name" value="<?= htmlspecialchars($admin['full_name']) ?>" required
                           class="w-full bg-gray-800 border border-gray-700 rounded-2xl px-5 py-4">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm mb-2">Email</label>
                        <input type="email" value="<?= htmlspecialchars($admin['email']) ?>" disabled
                               class="w-full bg-gray-800 border border-gray-700 rounded-2xl px-5 py-4 text-gray-400">
                    </div>
                    <div>
                        <label class="block text-sm mb-2">Phone Number</label>
                        <input type="tel" name="phone" value="<?= htmlspecialchars($admin['phone'] ?? '') ?>"
                               class="w-full bg-gray-800 border border-gray-700 rounded-2xl px-5 py-4">
                    </div>
                </div>

                <button type="submit" class="w-full bg-orange-500 hover:bg-orange-600 py-4 rounded-2xl font-semibold text-lg">
                    Update Profile
                </button>
            </form>
        </div>

        <!-- Change Password -->
        <div class="bg-gray-900 rounded-3xl p-6 lg:p-8">
            <h2 class="text-xl font-semibold mb-6">Change Password</h2>

            <form method="POST" class="space-y-6">
                <input type="hidden" name="change_password" value="1">

                <div>
                    <label class="block text-sm mb-2">Current Password</label>
                    <input type="password" name="current_password" required class="w-full bg-gray-800 border border-gray-700 rounded-2xl px-5 py-4">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm mb-2">New Password</label>
                        <input type="password" name="new_password" required minlength="6" class="w-full bg-gray-800 border border-gray-700 rounded-2xl px-5 py-4">
                    </div>
                    <div>
                        <label class="block text-sm mb-2">Confirm New Password</label>
                        <input type="password" name="confirm_password" required minlength="6" class="w-full bg-gray-800 border border-gray-700 rounded-2xl px-5 py-4">
                    </div>
                </div>

                <button type="submit" class="w-full bg-gray-700 hover:bg-gray-600 py-4 rounded-2xl font-semibold text-lg">
                    Change Password
                </button>
            </form>
        </div>
    </div>
</div>

<script>
window.onload = function() {
    const toast = document.getElementById('successToast');
    if (toast) {
        const progressBar = document.getElementById('progressBar');
        setTimeout(() => { progressBar.style.width = '0%'; }, 100);
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.style.display = 'none', 500);
        }, 3000);
    }
};

function hideToast() {
    const toast = document.getElementById('successToast');
    if (toast) toast.style.display = 'none';
}
</script>

<?php include '../includes/footer.php'; ?>