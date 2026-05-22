<?php
require_once '../config.php';
if (!isLoggedIn()) redirect('../login.php');

$admin_id = $_SESSION['admin_id'];
$member_id = (int)$_GET['id'];

$stmt = $pdo->prepare("SELECT m.*, p.plan_name, p.price 
                       FROM members m 
                       LEFT JOIN membership_plans p ON m.membership_plan_id = p.id 
                       WHERE m.id = ? AND m.admin_id = ?");
$stmt->execute([$member_id, $admin_id]);
$member = $stmt->fetch();

if (!$member) {
    redirect('index.php');
}

// Check expiry
$isExpired = strtotime($member['expiry_date']) < time();
$daysLeft = floor((strtotime($member['expiry_date']) - time()) / (60*60*24));
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<div class="ml-64 p-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-start mb-8">
            <div class="flex items-center gap-6">
                <?php if($member['photo']): ?>
                    <img src="../uploads/members/<?= htmlspecialchars($member['photo']) ?>" 
                         class="w-28 h-28 rounded-2xl object-cover border-4 border-orange-500">
                <?php else: ?>
                    <div class="w-28 h-28 bg-gray-700 rounded-2xl flex items-center justify-center text-5xl">
                        👤
                    </div>
                <?php endif; ?>
                <div>
                    <h1 class="text-4xl font-bold"><?= htmlspecialchars($member['full_name']) ?></h1>
                    <p class="text-gray-400 mt-1"><?= $member['phone'] ?> • <?= $member['age'] ?> years • <?= $member['gender'] ?></p>
                </div>
            </div>
            
            <div class="flex gap-3">
                <a href="edit.php?id=<?= $member['id'] ?>" 
                   class="bg-orange-500 hover:bg-orange-600 px-6 py-3 rounded-xl flex items-center gap-2">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <a href="delete.php?id=<?= $member['id'] ?>" 
                   onclick="return confirm('Delete this member permanently?')"
                   class="bg-red-600 hover:bg-red-700 px-6 py-3 rounded-xl flex items-center gap-2">
                    <i class="fas fa-trash"></i> Delete
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Basic Info -->
            <div class="lg:col-span-2 bg-gray-900 rounded-3xl p-8">
                <h2 class="text-xl font-semibold mb-6 flex items-center gap-2">
                    <i class="fas fa-info-circle"></i> Membership Details
                </h2>
                
                <div class="grid grid-cols-2 gap-8">
                    <div>
                        <p class="text-gray-400 text-sm">Membership Plan</p>
                        <p class="text-2xl font-semibold mt-1"><?= htmlspecialchars($member['plan_name']) ?></p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-sm">Price</p>
                        <p class="text-2xl font-semibold mt-1 text-orange-400">₹<?= number_format($member['price']) ?></p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-sm">Start Date</p>
                        <p class="text-xl font-medium"><?= date('d M Y', strtotime($member['start_date'])) ?></p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-sm">Expiry Date</p>
                        <p class="text-xl font-medium <?= $isExpired ? 'text-red-400' : 'text-green-400' ?>">
                            <?= date('d M Y', strtotime($member['expiry_date'])) ?>
                            <?php if(!$isExpired): ?>
                                <span class="text-sm text-gray-500">(<?= $daysLeft ?> days left)</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>

                <div class="mt-10">
                    <button onclick="sendWhatsAppReminder()" 
                            class="w-full bg-green-600 hover:bg-green-700 py-5 rounded-2xl font-semibold flex items-center justify-center gap-3 text-lg">
                        <i class="fab fa-whatsapp text-2xl"></i>
                        Send WhatsApp Renewal Reminder
                    </button>
                </div>
            </div>

            <!-- Status Card -->
            <div class="bg-gray-900 rounded-3xl p-8 text-center">
                <div class="mb-6">
                    <span class="inline-block px-8 py-3 rounded-2xl text-lg font-semibold
                        <?= $isExpired ? 'bg-red-500/20 text-red-400' : 'bg-green-500/20 text-green-400' ?>">
                        <?= $isExpired ? 'Membership Expired' : 'Active Member' ?>
                    </span>
                </div>
                
                <p class="text-gray-400">Address</p>
                <p class="mt-2"><?= nl2br(htmlspecialchars($member['address'])) ?></p>
            </div>
        </div>
    </div>
</div>

<script>
function sendWhatsAppReminder() {
    const name = "<?= addslashes($member['full_name']) ?>";
    const phone = "<?= $member['phone'] ?>";
    const expiry = "<?= date('d M Y', strtotime($member['expiry_date'])) ?>";
    
    const message = encodeURIComponent(
        `Hello ${name},\n\n` +
        `Your gym membership expires on ${expiry}.\n` +
        `Please renew soon to continue your fitness journey! 💪\n\n` +
        `Thank you!`
    );
    
    window.open(`https://wa.me/91${phone}?text=${message}`, '_blank');
}
</script>

<?php include '../includes/footer.php'; ?>