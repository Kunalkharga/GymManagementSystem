<?php
require_once '../config.php';
if (!isLoggedIn()) redirect('../login.php');

$admin_id = $_SESSION['admin_id'];
$plans = $pdo->query("SELECT * FROM membership_plans WHERE admin_id = $admin_id ORDER BY price ASC")->fetchAll();

if ($_POST) {
    $photo = '';
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $target_dir = "../uploads/members/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);
        $photo = time() . '_' . basename($_FILES['photo']['name']);
        move_uploaded_file($_FILES['photo']['tmp_name'], $target_dir . $photo);
    }

    $stmt = $pdo->prepare("SELECT duration_months FROM membership_plans WHERE id = ?");
    $stmt->execute([$_POST['plan_id']]);
    $duration_months = $stmt->fetchColumn();

    $stmt = $pdo->prepare("INSERT INTO members 
        (admin_id, full_name, phone, address, gender, age, photo, membership_plan_id, 
         start_date, expiry_date, emergency_contact, emergency_phone, height, weight, 
         goal, medical_conditions, blood_group, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 
                DATE_ADD(?, INTERVAL ? MONTH), ?, ?, ?, ?, ?, ?, ?, 'active')");
    
    $stmt->execute([
        $admin_id,
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
        sanitize($_POST['blood_group'])
    ]);

    redirect('index.php?success=added');
}
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<!-- Additional custom styles for form enhancements - these ONLY affect form area -->
<style>
/* Smooth transitions for form elements */
.form-section {
    transition: all 0.2s ease;
}

.form-input-modern:focus {
    outline: none;
    border-color: #f97316;
    box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.1);
}

/* Custom file upload styling */
.file-upload-label:hover {
    background-color: rgba(249, 115, 22, 0.05);
    border-color: #f97316;
}

/* Scroll reveal animation for sections */
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

/* Responsive touch-friendly inputs */
@media (max-width: 768px) {
    input, select, textarea, button {
        font-size: 16px !important; /* Prevents zoom on mobile */
    }
}
</style>

<div class="lg:ml-64 min-h-screen pt-16 lg:pt-0">
    <div class="p-4 lg:p-8 max-w-4xl mx-auto">
        
        <!-- Back Navigation -->
        <div class="mb-8">
            <a href="index.php" class="inline-flex items-center gap-2 text-gray-400 hover:text-orange-500 transition-colors duration-200 group">
                <svg class="w-4 h-4 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span>Back to Members</span>
            </a>
        </div>

        <!-- Page Header -->
        <div class="mb-10">
            <h1 class="text-3xl lg:text-4xl font-bold tracking-tight text-white mb-2">Add New Member</h1>
            <p class="text-gray-400 text-sm lg:text-base">Fill in the member's details below to register them into the system</p>
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
                            <input type="text" name="full_name" required 
                                   class="form-input-modern w-full bg-gray-800 border border-gray-700 focus:border-orange-500 rounded-xl px-4 py-3 text-white placeholder-gray-500 transition-colors"
                                   placeholder="Enter full name">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                Phone Number <span class="text-orange-500">*</span>
                            </label>
                            <input type="tel" name="phone" required 
                                   class="form-input-modern w-full bg-gray-800 border border-gray-700 focus:border-orange-500 rounded-xl px-4 py-3 text-white placeholder-gray-500 transition-colors"
                                   placeholder="+91 XXXXXXXXXX">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Address</label>
                        <textarea name="address" rows="2" 
                                  class="form-input-modern w-full bg-gray-800 border border-gray-700 focus:border-orange-500 rounded-xl px-4 py-3 text-white placeholder-gray-500 transition-colors resize-none"
                                  placeholder="Street, City, State, Postal Code"></textarea>
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
                            <input type="text" name="emergency_contact" required 
                                   class="form-input-modern w-full bg-gray-800 border border-gray-700 focus:border-orange-500 rounded-xl px-4 py-3 text-white placeholder-gray-500 transition-colors"
                                   placeholder="Full name of emergency contact">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                Emergency Phone <span class="text-orange-500">*</span>
                            </label>
                            <input type="tel" name="emergency_phone" required 
                                   class="form-input-modern w-full bg-gray-800 border border-gray-700 focus:border-orange-500 rounded-xl px-4 py-3 text-white placeholder-gray-500 transition-colors"
                                   placeholder="Emergency contact number">
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
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                Age <span class="text-orange-500">*</span>
                            </label>
                            <input type="number" name="age" required min="12" max="120"
                                   class="form-input-modern w-full bg-gray-800 border border-gray-700 focus:border-orange-500 rounded-xl px-4 py-3 text-white transition-colors"
                                   placeholder="Years">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                Membership Plan <span class="text-orange-500">*</span>
                            </label>
                            <select name="plan_id" required 
                                    class="form-input-modern w-full bg-gray-800 border border-gray-700 focus:border-orange-500 rounded-xl px-4 py-3 text-white transition-colors cursor-pointer">
                                <option value="">Select a plan</option>
                                <?php foreach($plans as $plan): ?>
                                    <option value="<?= $plan['id'] ?>"><?= htmlspecialchars($plan['plan_name']) ?> - ₹<?= number_format($plan['price']) ?></option>
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
                            <input type="number" step="0.01" name="height"
                                   class="form-input-modern w-full bg-gray-800 border border-gray-700 focus:border-orange-500 rounded-xl px-4 py-3 text-white transition-colors"
                                   placeholder="e.g., 175.5">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Weight (kg)</label>
                            <input type="number" step="0.01" name="weight"
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
                                <option value="Weight Gain">💪 Weight Gain</option>
                                <option value="Weight Loss">🔥 Weight Loss</option>
                                <option value="Fitness">🧘 General Fitness</option>
                                <option value="Muscle Building">🏋️ Muscle Building</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Blood Group</label>
                            <select name="blood_group"
                                    class="form-input-modern w-full bg-gray-800 border border-gray-700 focus:border-orange-500 rounded-xl px-4 py-3 text-white transition-colors cursor-pointer">
                                <option value="">Select Blood Group</option>
                                <option value="A+">A+</option>
                                <option value="A-">A-</option>
                                <option value="B+">B+</option>
                                <option value="B-">B-</option>
                                <option value="O+">O+</option>
                                <option value="O-">O-</option>
                                <option value="AB+">AB+</option>
                                <option value="AB-">AB-</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Medical Conditions (Optional)</label>
                        <textarea name="medical_conditions" rows="2"
                                  class="form-input-modern w-full bg-gray-800 border border-gray-700 focus:border-orange-500 rounded-xl px-4 py-3 text-white placeholder-gray-500 transition-colors resize-none"
                                  placeholder="Diabetes, Asthma, Allergy, etc."></textarea>
                    </div>
                </div>
            </div>

            <!-- Section 5: Start Date & Photo -->
            <div class="form-section bg-gray-900/50 backdrop-blur-sm rounded-2xl lg:rounded-3xl border border-gray-800 overflow-hidden">
                <div class="bg-gray-900/50 backdrop-blur-sm rounded-2xl lg:rounded-3xl border border-gray-800 overflow-hidden">
                    <div class="px-5 py-4 lg:px-8 lg:py-5 border-b border-gray-800 bg-gray-900/80">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-purple-500/10 flex items-center justify-center">
                                <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <h2 class="text-base lg:text-lg font-semibold text-gray-200">Start Date</h2>
                        </div>
                    </div>
                    <div class="p-5 lg:p-8">
                        <input type="date" name="start_date" value="<?= date('Y-m-d') ?>" required 
                               class="form-input-modern w-full bg-gray-800 border border-gray-700 focus:border-orange-500 rounded-xl px-4 py-3 text-white transition-colors">
                        <p class="text-xs text-gray-500 mt-2">Expiry date will be calculated automatically based on plan duration</p>
                    </div>
                </div>

                <!-- <div class="bg-gray-900/50 backdrop-blur-sm rounded-2xl lg:rounded-3xl border border-gray-800 overflow-hidden">
                    <div class="px-5 py-4 lg:px-8 lg:py-5 border-b border-gray-800 bg-gray-900/80">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-orange-500/10 flex items-center justify-center">
                                <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                            <h2 class="text-base lg:text-lg font-semibold text-gray-200">Profile Photo</h2>
                        </div>
                    </div>
                    <div class="p-5 lg:p-8">
                        <label class="file-upload-label flex flex-col items-center justify-center w-full border-2 border-gray-700 border-dashed rounded-xl cursor-pointer bg-gray-800/30 hover:bg-gray-800/50 transition-all py-6">
                            <div class="flex flex-col items-center justify-center text-center">
                                <svg class="w-10 h-10 text-gray-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <p class="text-sm text-gray-400"><span class="text-orange-500 font-medium">Click to upload</span> or drag and drop</p>
                                <p class="text-xs text-gray-500 mt-1">JPG, PNG (Max 5MB)</p>
                            </div>
                            <input type="file" name="photo" accept="image/*" class="hidden">
                        </label>
                    </div>
                </div> -->
            </div>

            <!-- Submit Button -->
            <div class="form-section pt-4">
                <button type="submit" 
                        class="group w-full bg-orange-500 hover:bg-orange-600 py-4 lg:py-5 rounded-xl lg:rounded-2xl font-semibold text-white text-lg lg:text-xl tracking-wide transition-all duration-200 transform hover:scale-[1.02] active:scale-[0.98] shadow-lg shadow-orange-500/20 hover:shadow-orange-500/40 flex items-center justify-center gap-3">
                    <svg class="w-5 h-5 lg:w-6 lg:h-6 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                    </svg>
                    <span>Add New Member</span>
                    <svg class="w-5 h-5 lg:w-6 lg:h-6 opacity-0 group-hover:opacity-100 transition-all -translate-x-2 group-hover:translate-x-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                    </svg>
                </button>
            </div>

        </form>
    </div>
</div>

<!-- Simple file name display script (UX enhancement only) -->
<script>
(function() {
    const fileInput = document.querySelector('input[type="file"][name="photo"]');
    if (fileInput) {
        const label = fileInput.closest('label');
        fileInput.addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                const fileName = e.target.files[0].name;
                const textContainer = label.querySelector('.text-center');
                if (textContainer && !label.querySelector('.selected-file')) {
                    const fileNameSpan = document.createElement('p');
                    fileNameSpan.className = 'selected-file text-xs text-orange-400 mt-2';
                    fileNameSpan.innerHTML = `📷 ${fileName.substring(0, 30)}${fileName.length > 30 ? '...' : ''}`;
                    textContainer.appendChild(fileNameSpan);
                } else if (label.querySelector('.selected-file')) {
                    label.querySelector('.selected-file').innerHTML = `📷 ${fileName.substring(0, 30)}${fileName.length > 30 ? '...' : ''}`;
                }
            }
        });
    }
})();
</script>

<?php include '../includes/footer.php'; ?>