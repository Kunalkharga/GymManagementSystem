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
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<div class="lg:ml-64 min-h-screen pt-16 lg:pt-0">
    <div class="p-4 lg:p-8 max-w-4xl mx-auto">
        
        <!-- Success Toast -->
        <?php if(isset($_GET['updated'])): ?>
        <div id="successToast" class="fixed top-6 right-6 bg-green-600 text-white px-6 py-4 rounded-2xl shadow-2xl flex items-center gap-3 z-50">
            <i class="fas fa-check-circle text-xl"></i>
            <span class="flex-1">✅ Member updated successfully!</span>
            <button onclick="hideToast()" class="text-white/70 hover:text-white">✕</button>
            <div class="absolute bottom-0 left-0 h-1 bg-green-300 rounded-b-2xl" id="progressBar" style="width: 100%; transition: width 3s linear;"></div>
        </div>
        <?php endif; ?>

        <a href="index.php" class="inline-flex items-center gap-2 text-gray-400 hover:text-white mb-6">
            ← Back to Members
        </a>

        <div class="bg-gray-900 rounded-3xl p-6 lg:p-10">
            <!-- Profile Header -->
            <div class="flex flex-col md:flex-row gap-6 lg:gap-10 items-center md:items-start">
                <div class="flex-shrink-0">
                    <?php if($member['photo']): ?>
                        <img src="../uploads/members/<?= htmlspecialchars($member['photo']) ?>" 
                             class="w-32 h-32 lg:w-40 lg:h-40 rounded-3xl object-cover border-4 border-orange-500">
                    <?php else: ?>
                        <div class="w-32 h-32 lg:w-40 lg:h-40 bg-gray-700 rounded-3xl flex items-center justify-center text-6xl">👤</div>
                    <?php endif; ?>
                </div>

                <div class="flex-1 text-center md:text-left">
                    <h1 class="text-3xl lg:text-4xl font-bold"><?= htmlspecialchars($member['full_name']) ?></h1>
                    <p class="text-gray-400 mt-2 text-lg"><?= htmlspecialchars($member['phone']) ?></p>
                    
                    <div class="mt-4 flex flex-wrap justify-center md:justify-start gap-4">
                        <span class="px-5 py-2 bg-gray-800 rounded-2xl text-sm">
                            <?= $member['age'] ?> years • <?= $member['gender'] ?>
                        </span>
                        <span class="px-5 py-2 <?= $isExpired ? 'bg-red-500/20 text-red-400' : 'bg-green-500/20 text-green-400' ?> rounded-2xl text-sm font-medium">
                            <?= $isExpired ? 'Expired' : 'Active' ?>
                            <?php if(!$isExpired): ?> (<?= $daysLeft ?> days left)<?php endif; ?>
                        </span>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
                    <a href="edit.php?id=<?= $member['id'] ?>" class="flex-1 md:flex-none text-center px-8 py-4 bg-orange-500 hover:bg-orange-600 rounded-2xl font-semibold">
                        Edit Profile
                    </a>
                    <button onclick="sendWhatsAppReminder()" class="flex-1 md:flex-none text-center px-8 py-4 bg-green-600 hover:bg-green-700 rounded-2xl font-semibold flex items-center justify-center gap-2">
                        <i class="fab fa-whatsapp"></i> WhatsApp
                    </button>
                </div>
            </div>

            <hr class="my-10 border-gray-700">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 lg:gap-12">
                <div>
                    <h2 class="text-xl font-semibold mb-5">Membership Details</h2>
                    <div class="space-y-6">
                        <div>
                            <p class="text-gray-400 text-sm">Plan</p>
                            <p class="text-2xl font-semibold"><?= htmlspecialchars($member['plan_name'] ?? 'Not Assigned') ?></p>
                        </div>
                        <div>
                            <p class="text-gray-400 text-sm">Price</p>
                            <p class="text-2xl font-semibold text-orange-400">₹<?= number_format($member['price'] ?? 0) ?></p>
                        </div>
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <p class="text-gray-400 text-sm">Start Date</p>
                                <p class="text-lg"><?= date('d M Y', strtotime($member['start_date'])) ?></p>
                            </div>
                            <div>
                                <p class="text-gray-400 text-sm">Expiry Date</p>
                                <p class="text-lg <?= $isExpired ? 'text-red-400' : 'text-green-400' ?>">
                                    <?= date('d M Y', strtotime($member['expiry_date'])) ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <h2 class="text-xl font-semibold mb-5">Contact Information</h2>
                    <div class="bg-gray-800 rounded-2xl p-6">
                        <div class="space-y-5">
                            <div>
                                <p class="text-gray-400 text-sm">Phone</p>
                                <p class="text-lg"><?= htmlspecialchars($member['phone']) ?></p>
                            </div>
                            <div>
                                <p class="text-gray-400 text-sm">Address</p>
                                <p class="text-lg leading-relaxed"><?= nl2br(htmlspecialchars($member['address'] ?? 'No address')) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function sendWhatsAppReminder() {
    const name = "<?= addslashes($member['full_name']) ?>";
    const phone = "<?= $member['phone'] ?>";
    const expiry = "<?= date('d M Y', strtotime($member['expiry_date'])) ?>";
    
    const message = encodeURIComponent(`Hello ${name}, your gym membership expires on ${expiry}. Please renew soon! 💪`);
    window.open(`https://wa.me/91${phone}?text=${message}`, '_blank');
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