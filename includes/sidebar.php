<?php
$active = basename(dirname($_SERVER['PHP_SELF']));
?>
<!-- Mobile Top Bar -->
<div class="lg:hidden fixed top-0 left-0 right-0 bg-gray-900 border-b border-gray-800 z-50 px-4 py-4 flex items-center justify-between">
    <h1 class="text-2xl font-bold text-orange-500">GYM<span class="text-white">SAAS</span></h1>
    <button onclick="toggleMobileMenu()" class="text-3xl">
        <i class="fas fa-bars"></i>
    </button>
</div>

<!-- Sidebar -->
<div id="sidebar" class="sidebar w-64 bg-gray-900 h-screen fixed left-0 top-0 border-r border-gray-800 lg:translate-x-0 -translate-x-full transition-transform z-50">
    <div class="p-6 border-b border-gray-800 flex items-center justify-between">
        <h1 class="text-3xl font-bold text-orange-500">GYM<span class="text-white">SAAS</span></h1>
        <button onclick="toggleMobileMenu()" class="lg:hidden text-2xl">
            <i class="fas fa-times"></i>
        </button>
    </div>
    
    <nav class="mt-8 px-3 space-y-1">
        <a href="../dashboard/index.php" class="flex items-center gap-3 px-4 py-3.5 rounded-2xl <?= $active=='dashboard'?'bg-orange-500 text-white':'hover:bg-gray-800' ?>">
            <i class="fas fa-tachometer-alt w-5"></i> Dashboard
        </a>
        <a href="../members/index.php" class="flex items-center gap-3 px-4 py-3.5 rounded-2xl <?= $active=='members'?'bg-orange-500 text-white':'hover:bg-gray-800' ?>">
            <i class="fas fa-users w-5"></i> Members
        </a>
        <a href="../plans/index.php" class="flex items-center gap-3 px-4 py-3.5 rounded-2xl hover:bg-gray-800">
            <i class="fas fa-dumbbell w-5"></i> Plans
        </a>
        <a href="../payments/index.php" class="flex items-center gap-3 px-4 py-3.5 rounded-2xl hover:bg-gray-800">
            <i class="fas fa-money-bill w-5"></i> Payments
        </a>
        <a href="../reports/index.php" class="flex items-center gap-3 px-4 py-3.5 rounded-2xl hover:bg-gray-800">
            <i class="fas fa-chart-bar w-5"></i> Reports
        </a>
        <a href="../settings/index.php" class="flex items-center gap-3 px-4 py-3.5 rounded-2xl hover:bg-gray-800">
            <i class="fas fa-cog w-5"></i> Settings
        </a>
    </nav>

    <div class="absolute bottom-8 w-full px-6">
       <a href="../logout.php" 
   onclick="return confirm('Are you sure you want to logout?')"
   class="flex items-center gap-3 text-red-400 hover:text-red-500 px-4 py-3">
    <i class="fas fa-sign-out-alt"></i> Logout
</a>
    </div>
</div>

<script>
function toggleMobileMenu() {
    const sidebar = document.getElementById('sidebar');
    sidebar.classList.toggle('-translate-x-full');
}
</script>