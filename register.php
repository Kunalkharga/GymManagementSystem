<?php
require_once 'config.php';

$error = '';

if ($_POST) {
    $gym_name = sanitize($_POST['gym_name']);
    $full_name = sanitize($_POST['full_name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long!";
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM admins WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            $error = "Email address is already registered!";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("INSERT INTO admins (gym_name, full_name, email, password, phone) 
                                   VALUES (?, ?, ?, ?, ?)");
            
            if ($stmt->execute([$gym_name, $full_name, $email, $hashed_password, $phone])) {
                // Redirect to login with success message
                header("Location: login.php?register=success");
                exit();
            } else {
                $error = "Failed to create account. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - GymSaas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-950 text-white min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md p-8 bg-gray-900 rounded-3xl">
        
        <div class="text-center mb-10">
            <h1 class="text-4xl font-bold text-orange-500">GYM<span class="text-white">SAAS</span></h1>
            <p class="text-gray-400 mt-2">Create New Gym Account</p>
        </div>

        <?php if($error): ?>
            <div class="bg-red-600/20 border border-red-500 text-red-400 p-4 rounded-2xl mb-6 text-center">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <div>
                <label class="block text-sm mb-2">Gym Name</label>
                <input type="text" name="gym_name" required 
                       class="w-full px-5 py-4 bg-gray-800 border border-gray-700 rounded-2xl">
            </div>
            <div>
                <label class="block text-sm mb-2">Owner Full Name</label>
                <input type="text" name="full_name" required 
                       class="w-full px-5 py-4 bg-gray-800 border border-gray-700 rounded-2xl">
            </div>
            <div>
                <label class="block text-sm mb-2">Email Address</label>
                <input type="email" name="email" required 
                       class="w-full px-5 py-4 bg-gray-800 border border-gray-700 rounded-2xl">
            </div>
            <div>
                <label class="block text-sm mb-2">Phone Number</label>
                <input type="tel" name="phone" required 
                       class="w-full px-5 py-4 bg-gray-800 border border-gray-700 rounded-2xl">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm mb-2">Password</label>
                    <input type="password" name="password" required minlength="6"
                           class="w-full px-5 py-4 bg-gray-800 border border-gray-700 rounded-2xl">
                </div>
                <div>
                    <label class="block text-sm mb-2">Confirm Password</label>
                    <input type="password" name="confirm_password" required minlength="6"
                           class="w-full px-5 py-4 bg-gray-800 border border-gray-700 rounded-2xl">
                </div>
            </div>

            <button type="submit" class="w-full py-4 bg-orange-500 hover:bg-orange-600 rounded-2xl font-semibold text-lg">
                Create Gym Account
            </button>
        </form>

        <div class="text-center mt-8">
            <p class="text-gray-400">
                Already have an account? 
                <a href="login.php" class="text-orange-400 hover:text-orange-500 font-semibold">Login Here</a>
            </p>
        </div>
    </div>
</body>
</html>