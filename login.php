<?php
require_once 'config.php';

if ($_POST) {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ?");
    $stmt->execute([$email]);
    $admin = $stmt->fetch();

   if ($admin && $password === $admin['password']) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['gym_name'] = $admin['gym_name'];
        redirect('dashboard/index.php');
    } else {
        $error = "Invalid credentials";
    }
}
?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - GymSaas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-950 text-white min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md p-8 bg-gray-900 rounded-2xl shadow-2xl">
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-orange-500">GYM<span class="text-white">SAAS</span></h1>
            <p class="text-gray-400 mt-2">Modern Gym Management</p>
        </div>

        <?php if(isset($error)): ?>
            <div class="bg-red-500/10 text-red-400 p-3 rounded-lg mb-4 text-center"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <div>
                <label class="block text-sm font-medium mb-2">Email</label>
                <input type="email" name="email" required
                       class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl focus:outline-none focus:border-orange-500">
            </div>
            <div>
                <label class="block text-sm font-medium mb-2">Password</label>
                <input type="password" name="password" required
                       class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl focus:outline-none focus:border-orange-500">
            </div>
            <button type="submit"
                    class="w-full py-4 bg-orange-500 hover:bg-orange-600 rounded-xl font-semibold transition">
                Login to Dashboard
            </button>
        </form>
    </div>
</body>
</html>