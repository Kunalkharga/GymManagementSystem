<?php
require_once '../config.php';
if (!isLoggedIn()) redirect('../login.php');

$admin_id = $_SESSION['admin_id'];
$member_id = (int)$_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM members WHERE id = ? AND admin_id = ?");
$stmt->execute([$member_id, $admin_id]);
$member = $stmt->fetch();

if (!$member) redirect('index.php');

$plans = $pdo->query("SELECT * FROM membership_plans WHERE admin_id = $admin_id")->fetchAll();

if ($_POST) {
    $photo = $member['photo'];
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $target_dir = "../uploads/members/";
        $new_photo = time() . '_' . basename($_FILES['photo']['name']);
        move_uploaded_file($_FILES['photo']['tmp_name'], $target_dir . $new_photo);
        if ($photo) @unlink($target_dir . $photo);
        $photo = $new_photo;
    }

    $start_date = $_POST['start_date'];
    $plan_id = (int)$_POST['plan_id'];

    // Get duration
    $stmt = $pdo->prepare("SELECT duration_months FROM membership_plans WHERE id = ?");
    $stmt->execute([$plan_id]);
    $duration_months = $stmt->fetchColumn();

    $stmt = $pdo->prepare("UPDATE members SET 
        full_name = ?,
        phone = ?,
        address = ?,
        gender = ?,
        age = ?,
        photo = ?,
        membership_plan_id = ?,
        start_date = ?,
        expiry_date = DATE_ADD(?, INTERVAL ? MONTH),
        status = 'active'
        WHERE id = ? AND admin_id = ?");

    $stmt->execute([
        sanitize($_POST['full_name']),
        sanitize($_POST['phone']),
        sanitize($_POST['address']),
        $_POST['gender'],
        (int)$_POST['age'],
        $photo,
        $plan_id,
        $start_date,
        $start_date,
        $duration_months,
        $member_id,
        $admin_id
    ]);

    redirect("profile.php?id=$member_id&updated=1");
}
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<div class="lg:ml-64 min-h-screen pt-16 lg:pt-0">
    <div class="p-4 lg:p-8 max-w-2xl mx-auto">
        <h1 class="text-2xl lg:text-3xl font-bold mb-8">Edit Member</h1>

        <form method="POST" enctype="multipart/form-data" class="bg-gray-900 p-6 lg:p-8 rounded-3xl space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm mb-2">Full Name</label>
                    <input type="text" name="full_name" value="<?= htmlspecialchars($member['full_name']) ?>" required 
                           class="w-full bg-gray-800 border border-gray-700 rounded-2xl px-5 py-4">
                </div>
                <div>
                    <label class="block text-sm mb-2">Phone Number</label>
                    <input type="tel" name="phone" value="<?= htmlspecialchars($member['phone']) ?>" required 
                           class="w-full bg-gray-800 border border-gray-700 rounded-2xl px-5 py-4">
                </div>
            </div>

            <div>
                <label class="block text-sm mb-2">Address</label>
                <textarea name="address" rows="3" class="w-full bg-gray-800 border border-gray-700 rounded-2xl px-5 py-4"><?= htmlspecialchars($member['address'] ?? '') ?></textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm mb-2">Gender</label>
                    <select name="gender" required class="w-full bg-gray-800 border border-gray-700 rounded-2xl px-5 py-4">
                        <option value="Male" <?= $member['gender']=='Male'?'selected':'' ?>>Male</option>
                        <option value="Female" <?= $member['gender']=='Female'?'selected':'' ?>>Female</option>
                        <option value="Other" <?= $member['gender']=='Other'?'selected':'' ?>>Other</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm mb-2">Age</label>
                    <input type="number" name="age" value="<?= $member['age'] ?>" required 
                           class="w-full bg-gray-800 border border-gray-700 rounded-2xl px-5 py-4">
                </div>
                <div>
                    <label class="block text-sm mb-2">Membership Plan</label>
                    <select name="plan_id" required class="w-full bg-gray-800 border border-gray-700 rounded-2xl px-5 py-4">
                        <?php foreach($plans as $plan): ?>
                            <option value="<?= $plan['id'] ?>" <?= $member['membership_plan_id']==$plan['id']?'selected':'' ?>>
                                <?= $plan['plan_name'] ?> - ₹<?= $plan['price'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm mb-2">Start Date</label>
                <input type="date" name="start_date" value="<?= $member['start_date'] ?>" required 
                       class="w-full bg-gray-800 border border-gray-700 rounded-2xl px-5 py-4">
            </div>

            <div>
                <label class="block text-sm mb-2">Profile Photo</label>
                <?php if($member['photo']): ?>
                    <img src="../uploads/members/<?= $member['photo'] ?>" class="w-24 h-24 object-cover rounded-2xl mb-4">
                <?php endif; ?>
                <input type="file" name="photo" accept="image/*" 
                       class="w-full bg-gray-800 border border-gray-700 rounded-2xl px-5 py-4">
            </div>

            <button type="submit" class="w-full bg-orange-500 hover:bg-orange-600 py-4 rounded-2xl font-semibold text-lg">
                Update Member
            </button>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>