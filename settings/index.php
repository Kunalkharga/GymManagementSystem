<?php
require_once '../config.php';
if (!isLoggedIn()) redirect('../login.php');

$admin_id = $_SESSION['admin_id'];

// Fetch current admin data
$stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
$stmt->execute([$admin_id]);
$admin = $stmt->fetch();

$success = isset($_GET['success']) ? true : false;
$error = '';

// Handle Profile Update
if ($_POST && isset($_POST['update_profile'])) {
    $full_name = sanitize($_POST['full_name']);
    $phone = sanitize($_POST['phone']);
    $gym_name = sanitize($_POST['gym_name']);

    $stmt = $pdo->prepare("UPDATE admins SET full_name = ?, phone = ?, gym_name = ? WHERE id = ?");
    $stmt->execute([$full_name, $phone, $gym_name, $admin_id]);

    $_SESSION['gym_name'] = $gym_name; // Update session
    redirect('index.php?success=1');
}

// Handle Password Change
if ($_POST && isset($_POST['change_password'])) {
    $current_pass = $_POST['current_password'];
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    if (password_verify($current_pass, $admin['password'])) {
        if ($new_pass === $confirm_pass && strlen($new_pass) >= 6) {
            $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("UPDATE admins SET password = ? WHERE id = ?");
            $stmt->execute([$hashed, $admin_id]);
            
            redirect('index.php?success=2');
        } else {
            $error = "New passwords do not match or too short";
        }
    } else {
        $error = "Current password is incorrect";
    }
}
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<div class="ml-64 p-8 max-w-2xl">
    <h1 class="text-3xl font-bold mb-8">Gym Settings</h1>

    <?php if($success): ?>
    <div class="bg-green-500/10 border border-green-500 text-green-400 p-4 rounded-2xl mb-6">
        ✅ Settings updated successfully!
    </div>
    <?php endif; ?>

    <?php if($error): ?>
    <div class="bg-red-500/10 border border-red-500 text-red-400 p-4 rounded-2xl mb-6">
        <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <!-- Profile Information -->
    <div class="bg-gray-900 rounded-3xl p-8 mb-8">
        <h2 class="text-xl font-semibold mb-6 flex items-center gap-2">
            <i class="fas fa-user-circle"></i> Gym & Owner Profile
        </h2>

        <form method="POST" class="space-y-6">
            <input type="hidden" name="update_profile" value="1">

            <div>
                <label class="block text-sm mb-2">Gym Name</label>
                <input type="text" name="gym_name" value="<?= htmlspecialchars($admin['gym_name']) ?>" required
                       class="w-full bg-gray-800 border border-gray-700 rounded-2xl px-6 py-4 focus:outline-none focus:border-orange-500">
            </div>

            <div>
                <label class="block text-sm mb-2">Owner Full Name</label>
                <input type="text" name="full_name" value="<?= htmlspecialchars($admin['full_name']) ?>" required
                       class="w-full bg-gray-800 border border-gray-700 rounded-2xl px-6 py-4">
            </div>

            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm mb-2">Email (Cannot Change)</label>
                    <input type="email" value="<?= htmlspecialchars($admin['email']) ?>" disabled
                           class="w-full bg-gray-800 border border-gray-700 rounded-2xl px-6 py-4 text-gray-400">
                </div>
                <div>
                    <label class="block text-sm mb-2">Phone Number</label>
                    <input type="tel" name="phone" value="<?= htmlspecialchars($admin['phone'] ?? '') ?>"
                           class="w-full bg-gray-800 border border-gray-700 rounded-2xl px-6 py-4">
                </div>
            </div>

            <button type="submit" 
                    class="w-full bg-orange-500 hover:bg-orange-600 py-5 rounded-2xl font-semibold text-lg transition">
                Update Profile
            </button>
        </form>
    </div>

    <!-- Change Password -->
    <div class="bg-gray-900 rounded-3xl p-8">
        <h2 class="text-xl font-semibold mb-6 flex items-center gap-2">
            <i class="fas fa-lock"></i> Change Password
        </h2>

        <form method="POST" class="space-y-6">
            <input type="hidden" name="change_password" value="1">

            <div>
                <label class="block text-sm mb-2">Current Password</label>
                <input type="password" name="current_password" required
                       class="w-full bg-gray-800 border border-gray-700 rounded-2xl px-6 py-4">
            </div>

            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm mb-2">New Password</label>
                    <input type="password" name="new_password" required minlength="6"
                           class="w-full bg-gray-800 border border-gray-700 rounded-2xl px-6 py-4">
                </div>
                <div>
                    <label class="block text-sm mb-2">Confirm New Password</label>
                    <input type="password" name="confirm_password" required minlength="6"
                           class="w-full bg-gray-800 border border-gray-700 rounded-2xl px-6 py-4">
                </div>
            </div>

            <button type="submit" 
                    class="w-full bg-gray-700 hover:bg-gray-600 py-5 rounded-2xl font-semibold text-lg transition">
                Change Password
            </button>
        </form>
    </div>

    <!-- Danger Zone -->
    <div class="mt-12 bg-red-900/20 border border-red-500/30 rounded-3xl p-8">
        <h3 class="text-red-400 font-semibold mb-2">Danger Zone</h3>
        <p class="text-gray-400 text-sm">These actions cannot be undone.</p>
        
        <div class="mt-6">
            <a href="#" onclick="if(confirm('This will delete your entire gym data. Are you sure?')) window.location='delete-account.php';" 
               class="text-red-400 hover:text-red-500 flex items-center gap-2">
                <i class="fas fa-trash"></i> Delete Gym Account
            </a>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>