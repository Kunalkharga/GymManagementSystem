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
        if ($photo) unlink($target_dir . $photo);
        $photo = $new_photo;
    }

    $stmt = $pdo->prepare("UPDATE members SET 
        full_name=?, phone=?, address=?, gender=?, age=?, 
        membership_plan_id=?, photo=?, start_date=?, 
        expiry_date = DATE_ADD(?, INTERVAL (SELECT duration_months FROM membership_plans WHERE id=?) MONTH)
        WHERE id=?");
    
    $stmt->execute([
        sanitize($_POST['full_name']),
        sanitize($_POST['phone']),
        sanitize($_POST['address']),
        $_POST['gender'],
        (int)$_POST['age'],
        $_POST['plan_id'],
        $photo,
        $_POST['start_date'],
        $_POST['start_date'],
        $_POST['plan_id'],
        $member_id
    ]);

    redirect("profile.php?id=$member_id&updated=1");
}
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<div class="ml-64 p-8 max-w-2xl">
    <h1 class="text-3xl font-bold mb-8">Edit Member</h1>

    <form method="POST" enctype="multipart/form-data" class="bg-gray-900 p-8 rounded-3xl space-y-6">
        <!-- Same form fields as add.php but with value="" populated -->
        <div class="grid grid-cols-2 gap-6">
            <div>
                <label>Full Name</label>
                <input type="text" name="full_name" value="<?= htmlspecialchars($member['full_name']) ?>" required 
                       class="w-full mt-2 bg-gray-800 border border-gray-700 rounded-2xl px-5 py-4">
            </div>
            <div>
                <label>Phone Number</label>
                <input type="tel" name="phone" value="<?= htmlspecialchars($member['phone']) ?>" required 
                       class="w-full mt-2 bg-gray-800 border border-gray-700 rounded-2xl px-5 py-4">
            </div>
        </div>

        <!-- ... (rest of the form similar to add.php with current values) ... -->

        <button type="submit" class="w-full bg-orange-500 hover:bg-orange-600 py-5 rounded-2xl font-semibold text-lg">
            Update Member
        </button>
    </form>
</div>