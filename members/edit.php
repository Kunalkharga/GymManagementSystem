<?php
require_once '../config.php';
if (!isLoggedIn()) redirect('../login.php');

$admin_id = $_SESSION['admin_id'];
$member_id = (int)$_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM members WHERE id = ? AND admin_id = ?");
$stmt->execute([$member_id, $admin_id]);
$member = $stmt->fetch();

if (!$member) redirect('index.php');

$plans = $pdo->query("SELECT * FROM membership_plans WHERE admin_id = $admin_id")->fetchAll();

if ($_POST) {
    $photo = $member['photo'];
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $target_dir = "../uploads/members/";
        $new_photo = time() . '_' . basename($_FILES['photo']['name']);
        move_uploaded_file($_FILES['photo']['tmp_name'], $target_dir . $new_photo);
        if ($photo) @unlink($target_dir . $photo);
        $photo = $new_photo;
    }

    $stmt = $pdo->prepare("SELECT duration_months FROM membership_plans WHERE id = ?");
    $stmt->execute([$_POST['plan_id']]);
    $duration_months = $stmt->fetchColumn();

    $stmt = $pdo->prepare("UPDATE members SET 
        full_name = ?, 
        phone = ?, 
        address = ?, 
        gender = ?, 
        age = ?, 
        photo = ?, 
        membership_plan_id = ?, 
        start_date = ?, 
        expiry_date = DATE_ADD(?, INTERVAL ? MONTH),
        emergency_contact = ?, 
        emergency_phone = ?, 
        height = ?, 
        weight = ?, 
        goal = ?, 
        medical_conditions = ?, 
        blood_group = ?,
        shift = ?,
        status = ?
        WHERE id = ? AND admin_id = ?");

    $stmt->execute([
        sanitize($_POST['full_name']),
        sanitize($_POST['phone']),
        sanitize($_POST['address']),
        $_POST['gender'],
        (int)$_POST['age'],
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
        sanitize($_POST['medical_conditions']),
        sanitize($_POST['blood_group']),
        $_POST['shift'],
        $member['status'],           // ← Important: Keep original status
        $member_id,
        $admin_id
    ]);

    redirect("profile.php?id=$member_id&updated=1");
}
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<!-- Custom styles for form enhancements -->
<style>
.form-section {
    transition: all 0.2s ease;
}

.form-input-modern:focus {
    outline: none;
    border-color: #f97316;
    box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.1);
}

.file-upload-label:hover {
    background-color: rgba(249, 115, 22, 0.05);
    border-color: #f97316;
}

@keyframes fadeSlideUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.form-section {
    animation: fadeSlideUp 0.4s ease-out forwards;
}

.form-section:nth-child(1) { animation-delay: 0s; }
.form-section:nth-child(2) { animation-delay: 0.05s; }
.form-section:nth-child(3) { animation-delay: 0.1s; }
.form-section:nth-child(4) { animation-delay: 0.15s; }
.form-section:nth-child(5) { animation-delay: 0.2s; }

@media (max-width: 768px) {
    input, select, textarea, button {
        font-size: 16px !important;
    }
}


</style>

<div class="lg:ml-64 min-h-screen pt-16 lg:pt-0">
    <div class="p-4 lg:p-8 max-w-4xl mx-auto">
        
        <!-- Back Navigation -->
        <div class="mb-8">
            <a href="profile.php?id=<?= $member_id ?>" class="inline-flex items-center gap-2 text-gray-400 hover:text-orange-500 transition-colors duration-200 group">
                <svg class="w-4 h-4 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span>Back to Profile</span>
            </a>
        </div>

        <!-- Page Header -->
        <div class="mb-10">
            <div class="flex items-center gap-3 mb-2">
                <h1 class="text-3xl lg:text-4xl font-bold tracking-tight text-white">Edit Member</h1>
                <span class="px-3 py-1 bg-orange-500/10 text-orange-400 rounded-full text-xs font-medium">Update Information</span>
            </div>
            <p class="text-gray-400 text-sm lg:text-base">Update <?= htmlspecialchars($member['full_name']) ?>'s information below</p>
        </div>

        <!-- Main Form -->
        <form method="POST" enctype="multipart/form-data" class="space-y-6 lg:space-y-8">
            
            <!-- Section 1: Personal Information -->
            <div class="form-section bg-gray-900/50 backdrop-blur-sm rounded-2xl lg:rounded-3xl border border-gray-800 overflow-hidden">
                <div class="px-5 py-4 lg:px-8 lg:py-5 border-b border-gray-800 bg-gray-900/80">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-orange-500/10 flex items-center justify-center">
                            <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <h2 class="text-base lg:text-lg font-semibold text-gray-200">Personal Information</h2>
                        <span class="text-xs text-gray-500 ml-auto">Required fields *</span>
                    </div>
                </div>
                <div class="p-5 lg:p-8 space-y-5">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                Full Name <span class="text-orange-500">*</span>
                            </label>
                            <input type="text" name="full_name" value="<?= htmlspecialchars($member['full_name']) ?>" required 
                                   class="form-input-modern w-full bg-gray-800 border border-gray-700 focus:border-orange-500 rounded-xl px-4 py-3 text-white placeholder-gray-500 transition-colors">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                Phone Number <span class="text-orange-500">*</span>
                            </label>
                            <input type="tel" name="phone" value="<?= htmlspecialchars($member['phone']) ?>" required 
                                   class="form-input-modern w-full bg-gray-800 border border-gray-700 focus:border-orange-500 rounded-xl px-4 py-3 text-white placeholder-gray-500 transition-colors">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Address</label>
                        <textarea name="address" rows="2" 
                                  class="form-input-modern w-full bg-gray-800 border border-gray-700 focus:border-orange-500 rounded-xl px-4 py-3 text-white placeholder-gray-500 transition-colors resize-none"
                                  placeholder="Street, City, State, Postal Code"><?= htmlspecialchars($member['address'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Section 2: Emergency Contact -->
            <div class="form-section bg-gray-900/50 backdrop-blur-sm rounded-2xl lg:rounded-3xl border border-gray-800 overflow-hidden">
                <div class="px-5 py-4 lg:px-8 lg:py-5 border-b border-gray-800 bg-gray-900/80">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-red-500/10 flex items-center justify-center">
                            <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </div>
                        <h2 class="text-base lg:text-lg font-semibold text-gray-200">Emergency Contact</h2>
                    </div>
                </div>
                <div class="p-5 lg:p-8 space-y-5">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                Contact Name <span class="text-orange-500">*</span>
                            </label>
                            <input type="text" name="emergency_contact" value="<?= htmlspecialchars($member['emergency_contact'] ?? '') ?>" required 
                                   class="form-input-modern w-full bg-gray-800 border border-gray-700 focus:border-orange-500 rounded-xl px-4 py-3 text-white placeholder-gray-500 transition-colors">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                Emergency Phone <span class="text-orange-500">*</span>
                            </label>
                            <input type="tel" name="emergency_phone" value="<?= htmlspecialchars($member['emergency_phone'] ?? '') ?>" required 
                                   class="form-input-modern w-full bg-gray-800 border border-gray-700 focus:border-orange-500 rounded-xl px-4 py-3 text-white placeholder-gray-500 transition-colors">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 3: Basic Details & Membership -->
            <div class="form-section bg-gray-900/50 backdrop-blur-sm rounded-2xl lg:rounded-3xl border border-gray-800 overflow-hidden">
                <div class="px-5 py-4 lg:px-8 lg:py-5 border-b border-gray-800 bg-gray-900/80">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-500/10 flex items-center justify-center">
                            <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path>
                            </svg>
                        </div>
                        <h2 class="text-base lg:text-lg font-semibold text-gray-200">Membership & Basic Details</h2>
                    </div>
                </div>
                <div class="p-5 lg:p-8">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                Gender <span class="text-orange-500">*</span>
                            </label>
                            <select name="gender" required 
                                    class="form-input-modern w-full bg-gray-800 border border-gray-700 focus:border-orange-500 rounded-xl px-4 py-3 text-white transition-colors cursor-pointer">
                                <option value="Male" <?= $member['gender']=='Male'?'selected':'' ?>>Male</option>
                                <option value="Female" <?= $member['gender']=='Female'?'selected':'' ?>>Female</option>
                                <option value="Other" <?= $member['gender']=='Other'?'selected':'' ?>>Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                Age <span class="text-orange-500">*</span>
                            </label>
                            <input type="number" name="age" value="<?= $member['age'] ?>" required min="12" max="120"
                                   class="form-input-modern w-full bg-gray-800 border border-gray-700 focus:border-orange-500 rounded-xl px-4 py-3 text-white transition-colors">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                Membership Plan <span class="text-orange-500">*</span>
                            </label>
                            <select name="plan_id" required 
                                    class="form-input-modern w-full bg-gray-800 border border-gray-700 focus:border-orange-500 rounded-xl px-4 py-3 text-white transition-colors cursor-pointer">
                                <?php foreach($plans as $plan): ?>
                                    <option value="<?= $plan['id'] ?>" <?= $member['membership_plan_id']==$plan['id']?'selected':'' ?>>
                                        <?= htmlspecialchars($plan['plan_name']) ?> - ₹<?= number_format($plan['price']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 4: Health Information -->
            <div class="form-section bg-gray-900/50 backdrop-blur-sm rounded-2xl lg:rounded-3xl border border-gray-800 overflow-hidden">
                <div class="px-5 py-4 lg:px-8 lg:py-5 border-b border-gray-800 bg-gray-900/80">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-green-500/10 flex items-center justify-center">
                            <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                            </svg>
                        </div>
                        <h2 class="text-base lg:text-lg font-semibold text-gray-200">Health Information</h2>
                        <span class="text-xs text-gray-500 ml-auto">Optional</span>
                    </div>
                </div>
                <div class="p-5 lg:p-8 space-y-5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Height (cm)</label>
                            <input type="number" step="0.01" name="height" value="<?= $member['height'] ?>"
                                   class="form-input-modern w-full bg-gray-800 border border-gray-700 focus:border-orange-500 rounded-xl px-4 py-3 text-white transition-colors"
                                   placeholder="e.g., 175.5">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Weight (kg)</label>
                            <input type="number" step="0.01" name="weight" value="<?= $member['weight'] ?>"
                                   class="form-input-modern w-full bg-gray-800 border border-gray-700 focus:border-orange-500 rounded-xl px-4 py-3 text-white transition-colors"
                                   placeholder="e.g., 72.5">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                Goal <span class="text-orange-500">*</span>
                            </label>
                            <select name="goal" required 
                                    class="form-input-modern w-full bg-gray-800 border border-gray-700 focus:border-orange-500 rounded-xl px-4 py-3 text-white transition-colors cursor-pointer">
                                <option value="Weight Gain" <?= $member['goal']=='Weight Gain'?'selected':'' ?>>💪 Weight Gain</option>
                                <option value="Weight Loss" <?= $member['goal']=='Weight Loss'?'selected':'' ?>>🔥 Weight Loss</option>
                                <option value="Fitness" <?= $member['goal']=='Fitness'?'selected':'' ?>>🧘 General Fitness</option>
                                <option value="Muscle Building" <?= $member['goal']=='Muscle Building'?'selected':'' ?>>🏋️ Muscle Building</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Blood Group</label>
                            <select name="blood_group"
                                    class="form-input-modern w-full bg-gray-800 border border-gray-700 focus:border-orange-500 rounded-xl px-4 py-3 text-white transition-colors cursor-pointer">
                                <option value="">Select Blood Group</option>
                                <option value="A+" <?= $member['blood_group']=='A+'?'selected':'' ?>>A+</option>
                                <option value="A-" <?= $member['blood_group']=='A-'?'selected':'' ?>>A-</option>
                                <option value="B+" <?= $member['blood_group']=='B+'?'selected':'' ?>>B+</option>
                                <option value="B-" <?= $member['blood_group']=='B-'?'selected':'' ?>>B-</option>
                                <option value="O+" <?= $member['blood_group']=='O+'?'selected':'' ?>>O+</option>
                                <option value="O-" <?= $member['blood_group']=='O-'?'selected':'' ?>>O-</option>
                                <option value="AB+" <?= $member['blood_group']=='AB+'?'selected':'' ?>>AB+</option>
                                <option value="AB-" <?= $member['blood_group']=='AB-'?'selected':'' ?>>AB-</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Medical Conditions (Optional)</label>
                        <textarea name="medical_conditions" rows="2"
                                  class="form-input-modern w-full bg-gray-800 border border-gray-700 focus:border-orange-500 rounded-xl px-4 py-3 text-white placeholder-gray-500 transition-colors resize-none"
                                  placeholder="Diabetes, Asthma, Allergy, etc."><?= htmlspecialchars($member['medical_conditions'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Section: Start Date & Shift -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <!-- Start Date -->
                <div
                    class="bg-gray-900/50 backdrop-blur-sm rounded-2xl lg:rounded-3xl border border-gray-800 overflow-hidden">
                    <div class="px-5 py-4 lg:px-8 lg:py-5 border-b border-gray-800 bg-gray-900/80">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-purple-500/10 flex items-center justify-center">
                                <i class="fas fa-calendar-alt text-purple-400"></i>
                            </div>
                            <h2 class="text-base lg:text-lg font-semibold text-gray-200">Start Date</h2>
                        </div>
                    </div>
                    <div class="p-5 lg:p-8">
                        <input type="date" name="start_date" value="<?= date('Y-m-d') ?>" required
                            class="w-full bg-gray-800 border border-gray-700 focus:border-orange-500 rounded-2xl px-5 py-4 text-lg">
                        <p class="text-xs text-gray-500 mt-3">Expiry date will be calculated automatically based on plan
                            duration.</p>
                    </div>
                </div>

                <!-- Training Shift Selection -->
                <div
                    class="bg-gray-900/50 backdrop-blur-sm rounded-2xl lg:rounded-3xl border border-gray-800 overflow-hidden">
                    <div class="px-5 py-4 lg:px-8 lg:py-5 border-b border-gray-800 bg-gray-900/80">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-blue-500/10 flex items-center justify-center">
                                <i class="fas fa-clock text-blue-500 text-lg"></i>
                            </div>
                            <h2 class="text-base lg:text-lg font-semibold text-gray-200">Training Shift <span
                                    class="text-red-500">*</span></h2>
                        </div>
                    </div>
                    <div class="p-5 lg:p-8">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                            <label class="cursor-pointer">
                                <input type="radio" name="shift" value="Morning" class="peer hidden">
                                <div
                                    class="peer-checked:bg-blue-600 peer-checked:text-white border-2 border-gray-700 hover:border-blue-500 transition-all rounded-2xl p-6 text-center">
                                    <i class="fas fa-sun text-3xl mb-3 text-yellow-400"></i>
                                    <p class="font-semibold text-lg">Morning Shift</p>
                                    <p class="text-sm text-gray-400">6:00 AM - 10:00 AM</p>
                                </div>
                            </label>

                            <label class="cursor-pointer">
                                <input type="radio" name="shift" value="Evening" class="peer hidden">
                                <div
                                    class="peer-checked:bg-blue-600 peer-checked:text-white border-2 border-gray-700 hover:border-blue-500 transition-all rounded-2xl p-6 text-center">
                                    <i class="fas fa-moon text-3xl mb-3 text-indigo-400"></i>
                                    <p class="font-semibold text-lg">Evening Shift</p>
                                    <p class="text-sm text-gray-400">4:00 PM - 8:00 PM</p>
                                </div>
                            </label>
                        </div>

                        <!-- Error Message -->
                        <p class="text-red-400 text-sm mt-3 hidden" id="shiftError">
                            ⚠️ Please select a training shift (Morning or Evening)
                        </p>
                    </div>
                </div>

            </div>

            <!-- Submit Button -->
            <div class="form-section pt-4">
                <div class="flex gap-4">
                    <a href="profile.php?id=<?= $member_id ?>" 
                       class="flex-1 bg-gray-800 hover:bg-gray-700 py-4 lg:py-5 rounded-xl lg:rounded-2xl font-semibold text-gray-300 text-center transition-all duration-200">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="flex-1 group bg-orange-500 hover:bg-orange-600 py-4 lg:py-5 rounded-xl lg:rounded-2xl font-semibold text-white text-lg lg:text-xl tracking-wide transition-all duration-200 transform hover:scale-[1.02] active:scale-[0.98] shadow-lg shadow-orange-500/20 hover:shadow-orange-500/40 flex items-center justify-center gap-3">
                        <svg class="w-5 h-5 lg:w-6 lg:h-6 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        <span>Update Member</span>
                        <svg class="w-5 h-5 lg:w-6 lg:h-6 opacity-0 group-hover:opacity-100 transition-all -translate-x-2 group-hover:translate-x-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                        </svg>
                    </button>
                </div>
            </div>

        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        
        if (form) {
            form.addEventListener('submit', function(e) {
                const shiftSelected = document.querySelector('input[name="shift"]:checked');
                
                if (!shiftSelected) {
                    e.preventDefault();   // Stop form submission
                    
                    const errorMsg = document.getElementById('shiftError');
                    if (errorMsg) {
                        errorMsg.classList.remove('hidden');
                        errorMsg.scrollIntoView({ behavior: "smooth", block: "center" });
                    }
                }
            });
        }
    });
</script>

<?php include '../includes/footer.php'; ?>