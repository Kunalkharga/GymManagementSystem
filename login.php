<?php
require_once 'config.php';

$success_msg = '';
$error = '';

// Show logout success ONLY if coming from logout (and no form submission)
if (isset($_GET['logout']) && $_GET['logout'] === 'success' && !$_POST) {
    $success_msg = "You have been logged out successfully!";
}

// Registration Success Message
if (isset($_GET['register']) && $_GET['register'] === 'success') {
    $success_msg = "Account created successfully! Please login.";
}

// Handle Login
if ($_POST) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ?");
    $stmt->execute([$email]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
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

        <!-- Success Message (Logout) -->
        <?php if(!empty($success_msg)): ?>
            <div id="successToast" class="bg-green-600/20 border border-green-500 text-green-400 p-4 rounded-2xl mb-6 text-center flex items-center gap-3 relative overflow-hidden">
                <i class="fas fa-check-circle"></i>
                <span><?= htmlspecialchars($success_msg) ?></span>
                <button onclick="hideToast()" class="ml-auto text-green-300 hover:text-white">✕</button>
                
                <div id="progressBar" class="absolute bottom-0 left-0 h-1 bg-green-400 rounded-b-2xl transition-all duration-[3000ms]" style="width: 100%;"></div>
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
                <label class="block mb-2 text-sm">Email Address</label>
                <input type="email" name="email" required 
                       class="w-full px-5 py-4 bg-gray-800 rounded-2xl border border-gray-700 focus:border-orange-500">
            </div>
            
            <div>
                <label class="block mb-2 text-sm">Password</label>
                <input type="password" name="password" required 
                       class="w-full px-5 py-4 bg-gray-800 rounded-2xl border border-gray-700 focus:border-orange-500">
            </div>

            <button type="submit" 
                    class="w-full py-4 bg-orange-500 hover:bg-orange-600 rounded-2xl font-bold text-lg transition">
                LOGIN
            </button>
        </form>

        <div class="text-center mt-8">
            <p class="text-gray-400">
                Don't have an account? 
                <a href="register.php" class="text-orange-400 hover:text-orange-500 font-semibold">
                    Create New Gym Account
                </a>
            </p>
        </div>
    </div>

    <script>
        window.onload = function() {
            // Clear autofill
            document.querySelector('input[name="email"]').value = '';
            document.querySelector('input[name="password"]').value = '';

            // Auto hide success message after 3 seconds
            const toast = document.getElementById('successToast');
            if (toast) {
                const progressBar = document.getElementById('progressBar');
                
                setTimeout(() => {
                    progressBar.style.width = '0%';
                }, 100);

                setTimeout(() => {
                    toast.style.transition = 'opacity 0.5s ease';
                    toast.style.opacity = '0';
                    setTimeout(() => {
                        toast.style.display = 'none';
                    }, 500);
                }, 3000);
            }
        };

        function hideToast() {
            const toast = document.getElementById('successToast');
            if (toast) {
                toast.style.opacity = '0';
                setTimeout(() => toast.style.display = 'none', 500);
            }
        }
    </script>
</body>
</html>