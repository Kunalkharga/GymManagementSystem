<?php
require_once '../config.php';
if (!isLoggedIn()) redirect('../login.php');

$admin_id = $_SESSION['admin_id'];
$member_id = (int)$_GET['id'];

$stmt = $pdo->prepare("SELECT m.*, p.plan_name, p.price, p.duration_months 
                       FROM members m 
                       LEFT JOIN membership_plans p ON m.membership_plan_id = p.id 
                       WHERE m.id = ? AND m.admin_id = ?");
$stmt->execute([$member_id, $admin_id]);
$member = $stmt->fetch();

if (!$member) redirect('index.php');

$isExpired = strtotime($member['expiry_date']) < time();
$daysLeft = $isExpired ? 0 : floor((strtotime($member['expiry_date']) - time()) / (60*60*24));

// BMI Calculation
$bmi = null;
$bmi_status = '';
$bmi_color = '';
$bmi_bg = '';
if (!empty($member['height']) && !empty($member['weight'])) {
    $height_m = $member['height'] / 100;
    $bmi = round($member['weight'] / ($height_m * $height_m), 1);
    
    if ($bmi < 18.5) {
        $bmi_status = 'Underweight';
        $bmi_color = 'text-blue-400';
        $bmi_bg = 'bg-blue-500/10 border-blue-500/30';
    } elseif ($bmi < 25) {
        $bmi_status = 'Normal';
        $bmi_color = 'text-green-400';
        $bmi_bg = 'bg-green-500/10 border-green-500/30';
    } elseif ($bmi < 30) {
        $bmi_status = 'Overweight';
        $bmi_color = 'text-yellow-400';
        $bmi_bg = 'bg-yellow-500/10 border-yellow-500/30';
    } else {
        $bmi_status = 'Obese';
        $bmi_color = 'text-red-400';
        $bmi_bg = 'bg-red-500/10 border-red-500/30';
    }
}
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<style>
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes fadeInUp {
        from {
            transform: translateY(20px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }
    
    .toast-slide {
        animation: slideIn 0.3s ease-out;
    }
    
    .fade-in-up {
        animation: fadeInUp 0.5s ease-out;
    }
    
    .stat-card {
        transition: all 0.3s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 20px 25px -12px rgba(0, 0, 0, 0.5);
    }
    
    .info-row {
        transition: background-color 0.2s ease;
    }
    
    .info-row:hover {
        background-color: rgba(255, 255, 255, 0.05);
    }
    
    .progress-bar-animated {
        transition: width 3s linear;
    }
    
    .btn-hover-effect {
        position: relative;
        overflow: hidden;
    }
    
    .btn-hover-effect::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.1);
        transform: translate(-50%, -50%);
        transition: width 0.6s, height 0.6s;
    }
    
    .btn-hover-effect:hover::before {
        width: 300px;
        height: 300px;
    }
    
    .gradient-border {
        position: relative;
        background: linear-gradient(135deg, rgba(249, 115, 22, 0.1), rgba(249, 115, 22, 0.05));
        border: 1px solid rgba(249, 115, 22, 0.3);
    }
    
    .gradient-border::before {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: inherit;
        padding: 1px;
        background: linear-gradient(135deg, #f97316, #fed7aa);
        mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
        -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
        -webkit-mask-composite: xor;
        mask-composite: exclude;
        pointer-events: none;
    }
</style>

<div class="lg:ml-64 min-h-screen pt-16 lg:pt-0 bg-gradient-to-br from-gray-900 via-gray-900 to-black">
    <div class="p-4 lg:p-8 max-w-7xl mx-auto">
        
        <!-- Success Toast -->
        <?php if(isset($_GET['updated'])): ?>
        <div id="successToast" class="fixed top-6 right-6 bg-gradient-to-r from-green-600 to-green-700 text-white px-6 py-4 rounded-2xl shadow-2xl flex items-center gap-3 z-50 toast-slide">
            <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                <i class="fas fa-check-circle text-xl"></i>
            </div>
            <div class="flex-1">
                <p class="font-semibold">Success!</p>
                <p class="text-sm text-green-100">Member updated successfully</p>
            </div>
            <button onclick="hideToast()" class="text-white/70 hover:text-white transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
            <div class="absolute bottom-0 left-0 h-1 bg-green-400 rounded-b-2xl progress-bar-animated" id="progressBar" style="width: 100%;"></div>
        </div>
        <?php endif; ?>

        <!-- Breadcrumb Navigation -->
        <nav class="flex items-center gap-2 text-sm text-gray-400 mb-6 fade-in-up">
            <a href="index.php" class="hover:text-orange-500 transition-colors">
                <i class="fas fa-users mr-2"></i>Members
            </a>
            <i class="fas fa-chevron-right text-xs"></i>
            <span class="text-white font-medium">Member Details</span>
        </nav>

        <!-- Header Section -->
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-8 fade-in-up">
            <div>
                <h1 class="text-3xl lg:text-4xl font-bold bg-gradient-to-r from-white to-gray-400 bg-clip-text text-transparent">
                    Member Profile
                </h1>
            </div>
            <div class="flex gap-3">
                <a href="edit.php?id=<?= $member['id'] ?>" 
                   class="px-6 py-3 bg-orange-500 hover:bg-orange-600 rounded-2xl font-semibold transition-all transform hover:scale-105 btn-hover-effect relative overflow-hidden flex items-center gap-2">
                    <i class="fas fa-edit"></i>
                    <span>Edit Profile</span>
                </a>
                <button onclick="sendWhatsAppReminder()" 
                        class="px-6 py-3 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 rounded-2xl font-semibold transition-all transform hover:scale-105 flex items-center gap-2">
                    <i class="fab fa-whatsapp"></i>
                    <span>Send Reminder</span>
                </button>
            </div>
        </div>

        <!-- Main Profile Card -->
        <div class="bg-gray-900/50 backdrop-blur-sm rounded-3xl p-6 lg:p-8 border border-gray-800 fade-in-up" style="animation-delay: 0.1s;">
            
            <!-- Profile Header -->
            <div class="flex flex-col md:flex-row gap-8 items-center md:items-start mb-12 pb-8 border-b border-gray-800">
                <div class="relative flex-shrink-0 group">
                    <?php if($member['photo']): ?>
                        <img src="../uploads/members/<?= htmlspecialchars($member['photo']) ?>" 
                             class="w-40 h-40 rounded-3xl object-cover border-4 border-orange-500 shadow-xl transition-transform group-hover:scale-105">
                    <?php else: ?>
                        <div class="w-40 h-40 bg-gradient-to-br from-gray-700 to-gray-800 rounded-3xl flex items-center justify-center text-7xl border-4 border-orange-500">
                            <?= strtoupper(substr($member['full_name'], 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                    <div class="absolute -bottom-2 -right-2 w-8 h-8 bg-green-500 rounded-full border-4 border-gray-900"></div>
                </div>

                <div class="flex-1 text-center md:text-left">
                    <div class="flex flex-wrap items-center gap-3 justify-center md:justify-start">
                        <h1 class="text-4xl font-bold"><?= htmlspecialchars($member['full_name']) ?></h1>
                        
                    </div>
                    <p class="text-gray-400 mt-2 text-lg flex items-center justify-center md:justify-start gap-2">
                        <i class="fas fa-phone-alt"></i>
                        <?= htmlspecialchars($member['phone']) ?>
                    </p>
                    
                    <div class="mt-6 flex flex-wrap justify-center md:justify-start gap-3">
                        <div class="px-5 py-2.5 bg-gray-800 rounded-xl text-sm flex items-center gap-2">
                            <i class="fas fa-calendar-alt text-orange-500"></i>
                            <span><?= $member['age'] ?> years</span>
                        </div>
                        <div class="px-5 py-2.5 bg-gray-800 rounded-xl text-sm flex items-center gap-2">
                            <i class="fas fa-venus-mars text-orange-500"></i>
                            <span><?= $member['gender'] ?></span>
                        </div>
                        <div class="px-5 py-2.5 rounded-xl text-sm flex items-center gap-2 <?= $isExpired ? 'bg-red-500/20 text-red-400' : 'bg-green-500/20 text-green-400' ?>">
                            <i class="fas fa-circle text-xs"></i>
                            <span class="font-medium">
                                <?= $isExpired ? 'Expired' : 'Active' ?>
                            </span>
                            <?php if(!$isExpired): ?>
                                <span class="text-gray-400">•</span>
                                <span><?= $daysLeft ?> days left</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <div class="stat-card bg-gradient-to-br from-gray-800 to-gray-800/50 rounded-2xl p-5 border border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-400 text-sm mb-1">Plan Type</p>
                            <p class="text-xl font-bold"><?= htmlspecialchars($member['plan_name'] ?? 'Not Assigned') ?></p>
                        </div>
                        <div class="w-12 h-12 bg-orange-500/20 rounded-xl flex items-center justify-center">
                            <i class="fas fa-dumbbell text-orange-500 text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="stat-card bg-gradient-to-br from-gray-800 to-gray-800/50 rounded-2xl p-5 border border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-400 text-sm mb-1">Membership Fee</p>
                            <p class="text-xl font-bold text-orange-400">₹<?= number_format($member['price'] ?? 0) ?></p>
                        </div>
                        <div class="w-12 h-12 bg-green-500/20 rounded-xl flex items-center justify-center">
                            <i class="fas fa-rupee-sign text-green-500 text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="stat-card bg-gradient-to-br from-gray-800 to-gray-800/50 rounded-2xl p-5 border border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-400 text-sm mb-1">Start Date</p>
                            <p class="text-lg font-semibold"><?= date('d M Y', strtotime($member['start_date'])) ?></p>
                        </div>
                        <div class="w-12 h-12 bg-blue-500/20 rounded-xl flex items-center justify-center">
                            <i class="fas fa-play text-blue-500 text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="stat-card bg-gradient-to-br from-gray-800 to-gray-800/50 rounded-2xl p-5 border border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-400 text-sm mb-1">Expiry Date</p>
                            <p class="text-lg font-semibold <?= $isExpired ? 'text-red-400' : 'text-green-400' ?>">
                                <?= date('d M Y', strtotime($member['expiry_date'])) ?>
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-yellow-500/20 rounded-xl flex items-center justify-center">
                            <i class="fas fa-hourglass-half text-yellow-500 text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                
                <!-- Health & Fitness Section -->
                <div class="bg-gray-800/50 rounded-2xl p-6 border border-gray-700 hover:border-gray-600 transition-all">
                    <div class="flex items-center gap-3 mb-6 pb-3 border-b border-gray-700">
                        <div class="w-10 h-10 bg-orange-500/20 rounded-xl flex items-center justify-center">
                            <i class="fas fa-heartbeat text-orange-500 text-lg"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-semibold">Health & Fitness</h2>
                            <p class="text-xs text-gray-500">Physical measurements and goals</p>
                        </div>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="flex justify-between items-center py-3 border-b border-gray-700/50">
                            <span class="text-gray-400">Height</span>
                            <span class="text-lg font-semibold"><?= $member['height'] ? $member['height'] . ' cm' : '—' ?></span>
                        </div>
                        <div class="flex justify-between items-center py-3 border-b border-gray-700/50">
                            <span class="text-gray-400">Weight</span>
                            <span class="text-lg font-semibold"><?= $member['weight'] ? $member['weight'] . ' kg' : '—' ?></span>
                        </div>
                        <div class="flex justify-between items-center py-3 border-b border-gray-700/50">
                            <span class="text-gray-400">Fitness Goal</span>
                            <span class="px-3 py-1 bg-orange-500/20 text-orange-400 rounded-full text-sm font-medium">
                                <?= htmlspecialchars($member['goal'] ?? 'Not Specified') ?>
                            </span>
                        </div>
                        <?php if(!empty($member['blood_group'])): ?>
                        <div class="flex justify-between items-center py-3 border-b border-gray-700/50">
                            <span class="text-gray-400">Blood Group</span>
                            <span class="px-3 py-1 bg-red-500/20 text-red-400 rounded-full text-sm font-bold">
                                <?= htmlspecialchars($member['blood_group']) ?>
                            </span>
                        </div>
                        <?php endif; ?>
                    </div>

                    <?php if($bmi !== null): ?>
                    <div class="mt-6 p-5 rounded-xl <?= $bmi_bg ?> border">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-400 mb-1">Body Mass Index (BMI)</p>
                                <p class="text-3xl font-bold <?= $bmi_color ?>"><?= $bmi ?></p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-semibold <?= $bmi_color ?>"><?= $bmi_status ?></p>
                                <div class="w-24 h-2 bg-gray-700 rounded-full mt-2 overflow-hidden">
                                    <div class="h-full <?= str_replace('text', 'bg', $bmi_color) ?> rounded-full" style="width: <?= min(($bmi / 40) * 100, 100) ?>%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if(!empty($member['medical_conditions'])): ?>
                    <div class="mt-6">
                        <div class="flex items-center gap-2 mb-3">
                            <i class="fas fa-notes-medical text-orange-500"></i>
                            <p class="text-sm font-medium">Medical Conditions</p>
                        </div>
                        <div class="bg-gray-900/50 p-4 rounded-xl text-sm text-gray-300 leading-relaxed border border-gray-700">
                            <?= nl2br(htmlspecialchars($member['medical_conditions'])) ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Contact Information Section -->
                <div class="bg-gray-800/50 rounded-2xl p-6 border border-gray-700 hover:border-gray-600 transition-all">
                    <div class="flex items-center gap-3 mb-6 pb-3 border-b border-gray-700">
                        <div class="w-10 h-10 bg-orange-500/20 rounded-xl flex items-center justify-center">
                            <i class="fas fa-address-book text-orange-500 text-lg"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-semibold">Contact Information</h2>
                            <p class="text-xs text-gray-500">Emergency & address details</p>
                        </div>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="flex items-start gap-3 py-3 border-b border-gray-700/50">
                            <div class="w-8 h-8 bg-blue-500/20 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-phone text-blue-400 text-sm"></i>
                            </div>
                            <div class="flex-1">
                                <p class="text-xs text-gray-500 mb-1">Primary Phone</p>
                                <p class="text-lg font-semibold"><?= htmlspecialchars($member['phone']) ?></p>
                            </div>
                        </div>
                        
                        <div class="flex items-start gap-3 py-3 border-b border-gray-700/50">
                            <div class="w-8 h-8 bg-red-500/20 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-ambulance text-red-400 text-sm"></i>
                            </div>
                            <div class="flex-1">
                                <p class="text-xs text-gray-500 mb-1">Emergency Contact</p>
                                <p class="font-semibold"><?= htmlspecialchars($member['emergency_contact'] ?? 'Not Provided') ?></p>
                                <p class="text-sm text-gray-400 mt-1"><?= htmlspecialchars($member['emergency_phone'] ?? 'No phone provided') ?></p>
                            </div>
                        </div>
                        
                        <div class="flex items-start gap-3 py-3">
                            <div class="w-8 h-8 bg-purple-500/20 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-map-marker-alt text-purple-400 text-sm"></i>
                            </div>
                            <div class="flex-1">
                                <p class="text-xs text-gray-500 mb-1">Address</p>
                                <p class="text-gray-300 leading-relaxed"><?= nl2br(htmlspecialchars($member['address'] ?? 'No address provided')) ?></p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Quick Actions Footer -->
            
        </div>
    </div>
</div>

<script>
function sendWhatsAppReminder() {
    const name = "<?= addslashes($member['full_name']) ?>";
    const phone = "<?= $member['phone'] ?>";
    const expiry = "<?= date('d M Y', strtotime($member['expiry_date'])) ?>";
    
    const message = encodeURIComponent(`🏋️‍♂️ Hello ${name},\n\nYour gym membership is expiring on ${expiry}. Please renew your membership to continue your fitness journey! 💪\n\nThank you for being with us!`);
    window.open(`https://wa.me/977${phone}?text=${message}`, '_blank');
}

function copyProfileLink() {
    const url = window.location.href;
    navigator.clipboard.writeText(url).then(() => {
        const toast = document.createElement('div');
        toast.className = 'fixed bottom-6 right-6 bg-gray-800 text-white px-5 py-3 rounded-xl shadow-xl z-50 flex items-center gap-2';
        toast.innerHTML = '<i class="fas fa-check-circle text-green-400"></i> Link copied to clipboard!';
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 2000);
    });
}

// Auto hide toast
window.onload = function() {
    const toast = document.getElementById('successToast');
    if (toast) {
        const progressBar = document.getElementById('progressBar');
        setTimeout(() => { progressBar.style.width = '0%'; }, 100);
        setTimeout(() => {
            toast.style.transition = 'opacity 0.5s';
            toast.style.opacity = '0';
            setTimeout(() => toast.style.display = 'none', 500);
        }, 3000);
    }
};

function hideToast() {
    const toast = document.getElementById('successToast');
    if (toast) toast.style.display = 'none';
}
</script>

<?php include '../includes/footer.php'; ?>