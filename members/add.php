<?php
require_once '../config.php';
if (!isLoggedIn()) redirect('../login.php');

$admin_id = $_SESSION['admin_id'];

// Get Plans
$plans = $pdo->query("SELECT * FROM membership_plans WHERE admin_id = $admin_id")->fetchAll();

if ($_POST) {
    $photo = '';
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $target_dir = "../uploads/members/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);
        
        $photo = time() . '_' . basename($_FILES['photo']['name']);
        move_uploaded_file($_FILES['photo']['tmp_name'], $target_dir . $photo);
    }

    $stmt = $pdo->prepare("INSERT INTO members (admin_id, full_name, phone, address, gender, age, photo, membership_plan_id, start_date, expiry_date) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, DATE_ADD(?, INTERVAL (SELECT duration_months FROM membership_plans WHERE id=?) MONTH))");
    
    $stmt->execute([
        $admin_id,
        sanitize($_POST['full_name']),
        sanitize($_POST['phone']),
        sanitize($_POST['address']),
        $_POST['gender'],
        (int)$_POST['age'],
        $photo,
        $_POST['plan_id'],
        $_POST['start_date'],
        $_POST['start_date'],
        $_POST['plan_id']
    ]);

    redirect('index.php?success=1');
}
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<div class="ml-64 p-8 max-w-2xl">
    <h1 class="text-3xl font-bold mb-8">Add New Member</h1>

    <form method="POST" enctype="multipart/form-data" class="bg-gray-900 p-8 rounded-3xl space-y-6">
        <div class="grid grid-cols-2 gap-6">
            <div>
                <label>Full Name</label>
                <input type="text" name="full_name" required class="w-full mt-2 bg-gray-800 border border-gray-700 rounded-2xl px-5 py-4">
            </div>
            <div>
                <label>Phone Number</label>
                <input type="tel" name="phone" required class="w-full mt-2 bg-gray-800 border border-gray-700 rounded-2xl px-5 py-4">
            </div>
        </div>

        <div>
            <label>Address</label>
            <textarea name="address" rows="3" class="w-full mt-2 bg-gray-800 border border-gray-700 rounded-2xl px-5 py-4"></textarea>
        </div>

        <div class="grid grid-cols-3 gap-6">
            <div>
                <label>Gender</label>
                <select name="gender" required class="w-full mt-2 bg-gray-800 border border-gray-700 rounded-2xl px-5 py-4">
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div>
                <label>Age</label>
                <input type="number" name="age" required class="w-full mt-2 bg-gray-800 border border-gray-700 rounded-2xl px-5 py-4">
            </div>
            <div>
                <label>Membership Plan</label>
                <select name="plan_id" required class="w-full mt-2 bg-gray-800 border border-gray-700 rounded-2xl px-5 py-4">
                    <?php foreach($plans as $plan): ?>
                        <option value="<?= $plan['id'] ?>"><?= $plan['plan_name'] ?> - ₹<?= $plan['price'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div>
            <label>Start Date</label>
            <input type="date" name="start_date" value="<?= date('Y-m-d') ?>" required 
                   class="w-full mt-2 bg-gray-800 border border-gray-700 rounded-2xl px-5 py-4">
        </div>

        <div>
            <label>Profile Photo</label>
            <input type="file" name="photo" accept="image/*" class="w-full mt-2 bg-gray-800 border border-gray-700 rounded-2xl px-5 py-4">
        </div>

        <button type="submit" class="w-full bg-orange-500 hover:bg-orange-600 py-5 rounded-2xl font-semibold text-lg">
            Add Member
        </button>
    </form>
</div>