<?php
require_once 'config.php';

$admin_id = isset($_GET['gym']) ? (int) $_GET['gym'] : 0;
$error = '';
$success = false;

if ($admin_id == 0) {
    die("<div style='padding:50px;text-align:center;color:red;font-size:20px;'>❌ Invalid QR Code. Please scan again.</div>");
}

// Get gym name for better UX
$stmt = $pdo->prepare("SELECT gym_name FROM admins WHERE id = ?");
$stmt->execute([$admin_id]);
$gym = $stmt->fetch();
$gym_name = $gym ? $gym['gym_name'] : 'AnyTimeFitness';

$plans = $pdo->query("SELECT * FROM membership_plans WHERE admin_id = $admin_id ORDER BY price ASC")->fetchAll();

if ($_POST) {
    $photo = '';
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $target_dir = "uploads/members/";
        if (!is_dir($target_dir))
            mkdir($target_dir, 0755, true);
        $photo = time() . '_' . basename($_FILES['photo']['name']);
        move_uploaded_file($_FILES['photo']['tmp_name'], $target_dir . $photo);
    }

    $stmt = $pdo->prepare("SELECT duration_months FROM membership_plans WHERE id = ?");
    $stmt->execute([$_POST['plan_id']]);
    $duration_months = $stmt->fetchColumn();

    $stmt = $pdo->prepare("INSERT INTO members 
        (admin_id, full_name, phone, address, gender, age, photo, membership_plan_id, start_date, expiry_date,
         emergency_contact, emergency_phone, height, weight, goal, medical_conditions, blood_group, status, registration_type) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, DATE_ADD(?, INTERVAL ? MONTH), ?, ?, ?, ?, ?, ?, ?, 'pending', 'self')");

    $stmt->execute([
        $admin_id,
        sanitize($_POST['full_name']),
        sanitize($_POST['phone']),
        sanitize($_POST['address'] ?? ''),
        $_POST['gender'],
        (int) $_POST['age'],
        $photo,
        $_POST['plan_id'],
        $_POST['start_date'],
        $_POST['start_date'],
        $duration_months,
        sanitize($_POST['emergency_contact']),
        sanitize($_POST['emergency_phone']),
        !empty($_POST['height']) ? $_POST['height'] : null,
        !empty($_POST['weight']) ? $_POST['weight'] : null,
        $_POST['goal'],
        sanitize($_POST['medical_conditions'] ?? ''),
        sanitize($_POST['blood_group'] ?? '')
    ]);

    $success = true;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration - <?= htmlspecialchars($gym_name) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-gray-950 text-white min-h-screen">

    <div class="max-w-lg mx-auto px-4 py-8">

        <?php if ($success): ?>
            <div class="text-center py-20">
                <div class="text-7xl mb-6">🎉</div>
                <h1 class="text-3xl font-bold mb-4">Registration Successful!</h1>
                <p class="text-gray-400 text-lg">Thank you for registering with
                    <strong><?= htmlspecialchars($gym_name) ?></strong>.</p>
                <p class="text-green-400 mt-4">Your details have been submitted successfully.</p>
                <p class="text-sm text-gray-500 mt-8">Please wait for admin approval.</p>

                <button onclick="window.close()"
                    class="mt-10 bg-orange-500 hover:bg-orange-600 px-10 py-4 rounded-2xl font-semibold text-lg">
                    Close Window
                </button>
            </div>
        <?php else: ?>

            <div class="text-center mb-10">
                <h1 class="text-3xl font-bold text-orange-500"><?= htmlspecialchars($gym_name) ?></h1>
                <p class="text-gray-400 mt-2">New Member Registration</p>
            </div>

            <form method="POST" enctype="multipart/form-data" class="space-y-8">
                <!-- Personal Information -->
                <div class="space-y-5">
                    <h2 class="text-lg font-semibold">Personal Information</h2>
                    <input type="text" name="full_name" required placeholder="Full Name *"
                        class="w-full bg-gray-800 border border-gray-700 rounded-2xl px-5 py-4">
                    <input type="tel" name="phone" required placeholder="Phone Number *"
                        class="w-full bg-gray-800 border border-gray-700 rounded-2xl px-5 py-4">
                </div>

                <!-- Emergency -->
                <div class="space-y-5">
                    <h2 class="text-lg font-semibold">Emergency Contact</h2>
                    <input type="text" name="emergency_contact" required placeholder="Emergency Contact Name *"
                        class="w-full bg-gray-800 border border-gray-700 rounded-2xl px-5 py-4">
                    <input type="tel" name="emergency_phone" required placeholder="Emergency Phone Number *"
                        class="w-full bg-gray-800 border border-gray-700 rounded-2xl px-5 py-4">
                </div>

                <!-- Basic -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm mb-2">Gender</label>
                        <select name="gender" required
                            class="w-full bg-gray-800 border border-gray-700 rounded-2xl px-5 py-4">
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm mb-2">Age</label>
                        <input type="number" name="age" required
                            class="w-full bg-gray-800 border border-gray-700 rounded-2xl px-5 py-4">
                    </div>
                </div>

                <!-- Health -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm mb-2">Height (cm)</label>
                        <input type="number" step="0.01" name="height"
                            class="w-full bg-gray-800 border border-gray-700 rounded-2xl px-5 py-4">
                    </div>
                    <div>
                        <label class="block text-sm mb-2">Weight (kg)</label>
                        <input type="number" step="0.01" name="weight"
                            class="w-full bg-gray-800 border border-gray-700 rounded-2xl px-5 py-4">
                    </div>
                </div>

                <div>
                    <label class="block text-sm mb-2">Goal</label>
                    <select name="goal" required class="w-full bg-gray-800 border border-gray-700 rounded-2xl px-5 py-4">
                        <option value="Weight Gain">Weight Gain</option>
                        <option value="Weight Loss">Weight Loss</option>
                        <option value="Fitness">General Fitness</option>
                        <option value="Muscle Building">Muscle Building</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm mb-2">Blood Group</label>
                    <select name="blood_group" class="w-full bg-gray-800 border border-gray-700 rounded-2xl px-5 py-4">
                        <option value="">Select Blood Group</option>
                        <option value="A+">A+</option>
                        <option value="A-">A-</option>
                        <option value="B+">B+</option>
                        <option value="B-">B-</option>
                        <option value="O+">O+</option>
                        <option value="O-">O-</option>
                        <option value="AB+">AB+</option>
                        <option value="AB-">AB-</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm mb-2">Medical Conditions (Optional)</label>
                    <textarea name="medical_conditions" rows="3"
                        class="w-full bg-gray-800 border border-gray-700 rounded-2xl px-5 py-4"></textarea>
                </div>

                <div>
                    <label class="block text-sm mb-2">Membership Plan</label>
                    <select name="plan_id" required class="w-full bg-gray-800 border border-gray-700 rounded-2xl px-5 py-4">
                        <?php foreach ($plans as $plan): ?>
                            <option value="<?= $plan['id'] ?>"><?= $plan['plan_name'] ?> - ₹<?= $plan['price'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm mb-2">Start Date</label>
                    <input type="date" name="start_date" value="<?= date('Y-m-d') ?>" required
                        class="w-full bg-gray-800 border border-gray-700 rounded-2xl px-5 py-4">
                </div>

                <div>
                    <label class="block text-sm mb-2">Profile Photo (Optional)</label>
                    <input type="file" name="photo" accept="image/*"
                        class="w-full bg-gray-800 border border-gray-700 rounded-2xl px-5 py-4">
                </div>

                <button type="submit"
                    class="w-full bg-orange-500 hover:bg-orange-600 py-5 rounded-2xl font-semibold text-lg mt-6">
                    Submit Registration
                </button>
            </form>

        <?php endif; ?>
    </div>
</body>

</html>