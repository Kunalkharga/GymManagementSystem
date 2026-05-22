<?php
require_once '../config.php';
if (!isLoggedIn()) redirect('../login.php');

$admin_id = $_SESSION['admin_id'];

// Mark all as read when viewing
$pdo->prepare("UPDATE notifications SET is_read = 1 WHERE admin_id = ?")->execute([$admin_id]);

$stmt = $pdo->prepare("SELECT n.*, m.full_name 
                       FROM notifications n 
                       LEFT JOIN members m ON n.member_id = m.id 
                       WHERE n.admin_id = ? 
                       ORDER BY n.created_at DESC");
$stmt->execute([$admin_id]);
$notifications = $stmt->fetchAll();
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<div class="ml-64 p-8">
    <h1 class="text-3xl font-bold mb-8">Notifications</h1>

    <div class="bg-gray-900 rounded-3xl">
        <?php if(empty($notifications)): ?>
            <div class="p-20 text-center text-gray-400">
                <p class="text-6xl mb-4">🔔</p>
                <p>No notifications yet</p>
            </div>
        <?php else: ?>
            <?php foreach($notifications as $notif): ?>
            <div class="p-6 border-b border-gray-800 last:border-none hover:bg-gray-800/50">
                <div class="flex justify-between">
                    <div>
                        <p class="font-medium"><?= htmlspecialchars($notif['message']) ?></p>
                        <?php if($notif['full_name']): ?>
                            <p class="text-sm text-gray-400 mt-1">Member: <?= htmlspecialchars($notif['full_name']) ?></p>
                        <?php endif; ?>
                    </div>
                    <p class="text-xs text-gray-500"><?= date('d M, H:i', strtotime($notif['created_at'])) ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>