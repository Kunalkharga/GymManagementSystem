<?php
require_once 'config.php';

$success_msg = '';
$error = '';

// Show success message only from URL parameter
if (isset($_GET['logout']) && $_GET['logout'] === 'success') {
    $success_msg = "You have been logged out successfully!";
}

// Handle Login
if ($_POST) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ?");
    $stmt->execute([$email]);
    $admin = $stmt->fetch();

    if ($admin && $password === $admin['password']) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['gym_name'] = $admin['gym_name'];
        header("Location: dashboard/index.php");
        exit();
    } else {
        $error = "Invalid email or password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - GymSaas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-950 text-white min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md p-8 bg-gray-900 rounded-3xl">
        
        <div class="text-center mb-10">
            <h1 class="text-4xl font-bold text-orange-500">GYM<span class="text-white">SAAS</span></h1>
            <p class="text-gray-400 mt-2">Login to your Dashboard</p>
        </div>

        <!-- Success Message -->
        <?php if(!empty($success_msg)): ?>
            <div id="successMessage" class="bg-green-600/20 border border-green-500 text-green-400 p-4 rounded-2xl mb-6 text-center">
                <?= htmlspecialchars($success_msg) ?>
            </div>
        <?php endif; ?>

        <!-- Error Message -->
        <?php if(!empty($error)): ?>
            <div class="bg-red-600/20 border border-red-500 text-red-400 p-4 rounded-2xl mb-6 text-center">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6" autocomplete="off">
            <div>
                <label class="block mb-2 text-sm">Email</label>
                <input type="email" name="email" id="email" autocomplete="off"
                       class="w-full px-5 py-4 bg-gray-800 rounded-2xl border border-gray-700 focus:border-orange-500">
            </div>
            
            <div>
                <label class="block mb-2 text-sm">Password</label>
                <input type="password" name="password" id="password" autocomplete="new-password"
                       class="w-full px-5 py-4 bg-gray-800 rounded-2xl border border-gray-700 focus:border-orange-500">
            </div>

            <button type="submit" 
                    class="w-full py-4 bg-orange-500 hover:bg-orange-600 rounded-2xl font-bold text-lg transition">
                LOGIN
            </button>
        </form>

        <p class="text-center text-xs text-gray-500 mt-8">
            Default: admin@gymsaas.com / admin123
        </p>
    </div>

    <script>
        // Clear form fields
        window.onload = function() {
            document.getElementById('email').value = '';
            document.getElementById('password').value = '';
            
            // Remove success message from URL after showing (Clean URL)
            if (window.location.search.includes('logout=success')) {
                setTimeout(() => {
                    window.history.replaceState({}, document.title, window.location.pathname);
                }, 1500); // Remove after 1.5 seconds
            }
        };
    </script>
</body>
</html>