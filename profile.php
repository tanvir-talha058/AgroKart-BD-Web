<?php
// FILE: profile.php

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

// Include header after checking for redirect
include 'includes/header.php';
// Fetch current user data for the form
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT name, email, phone, division, district, city, profile_image_path FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>

<!-- Enhanced Profile Section -->
<section class="profile-page">
    <div class="profile-background">
        <div class="profile-pattern"></div>
    </div>

    <div class="profile-container">
        <div class="profile-card">
            <div class="profile-header">
                <div class="profile-header-content">
                    <div class="profile-icon">
                        <i class="fas fa-user-edit"></i>
                    </div>
                    <div class="profile-title-section">
                        <h1 class="profile-title">Update Profile</h1>
                        <p class="profile-subtitle">Keep your information up to date</p>
                    </div>
                </div>
                <div class="current-profile-photo">
                    <?php if (!empty($user['profile_image_path']) && file_exists($user['profile_image_path'])): ?>
                        <img src="<?php echo htmlspecialchars($user['profile_image_path']); ?>" alt="Current Profile" class="current-photo">
                    <?php else: ?>
                        <div class="default-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php
            if (isset($_SESSION['error'])) {
                echo '<div class="message-wrapper error-wrapper">';
                echo '<div class="message-content error-message">';
                echo '<i class="fas fa-exclamation-circle"></i>';
                echo '<span>' . $_SESSION['error'] . '</span>';
                echo '</div>';
                echo '</div>';
                unset($_SESSION['error']);
            }
            if (isset($_SESSION['message'])) {
                echo '<div class="message-wrapper success-wrapper">';
                echo '<div class="message-content success-message">';
                echo '<i class="fas fa-check-circle"></i>';
                echo '<span>' . $_SESSION['message'] . '</span>';
                echo '</div>';
                echo '</div>';
                unset($_SESSION['message']);
            }
            ?>

            <form action="php/update_profile_process.php" method="post" enctype="multipart/form-data" class="profile-form">
                <!-- Profile Photo Section -->
                <div class="form-section photo-section">
                    <h3 class="section-title">
                        <i class="fas fa-camera"></i>
                        Profile Photo
                    </h3>
                    <div class="photo-upload-wrapper">
                        <div class="photo-preview" id="photoPreview">
                            <?php if (!empty($user['profile_image_path']) && file_exists($user['profile_image_path'])): ?>
                                <img src="<?php echo htmlspecialchars($user['profile_image_path']); ?>" alt="Profile Preview" class="preview-image">
                            <?php else: ?>
                                <div class="preview-placeholder">
                                    <i class="fas fa-user"></i>
                                    <span>No photo selected</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="photo-upload-controls">
                            <label for="profile_photo" class="photo-upload-btn">
                                <i class="fas fa-upload"></i>
                                <span>Choose Photo</span>
                            </label>
                            <input type="file" id="profile_photo" name="profile_photo" accept="image/*" style="display: none;">
                            <p class="photo-help-text">JPG, PNG or GIF (Max 5MB)</p>
                        </div>
                    </div>
                </div>

                <!-- Personal Information Section -->
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-user"></i>
                        Personal Information
                    </h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">
                                <i class="fas fa-user"></i>
                                Full Name
                            </label>
                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">
                                <i class="fas fa-envelope"></i>
                                Email Address
                            </label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                            <small class="field-note">Email cannot be changed for security reasons</small>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="phone">
                                <i class="fas fa-phone"></i>
                                Phone Number
                            </label>
                            <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                        </div>
                    </div>
                </div>

                <!-- Address Information Section -->
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-map-marker-alt"></i>
                        Address Information
                    </h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="division">
                                <i class="fas fa-map"></i>
                                Division
                            </label>
                            <select id="division" name="division">
                                <option value="">Select Division</option>
                                <option value="Dhaka" <?php echo ($user['division'] == 'Dhaka') ? 'selected' : ''; ?>>Dhaka</option>
                                <option value="Chittagong" <?php echo ($user['division'] == 'Chittagong') ? 'selected' : ''; ?>>Chittagong</option>
                                <option value="Rajshahi" <?php echo ($user['division'] == 'Rajshahi') ? 'selected' : ''; ?>>Rajshahi</option>
                                <option value="Khulna" <?php echo ($user['division'] == 'Khulna') ? 'selected' : ''; ?>>Khulna</option>
                                <option value="Barisal" <?php echo ($user['division'] == 'Barisal') ? 'selected' : ''; ?>>Barisal</option>
                                <option value="Sylhet" <?php echo ($user['division'] == 'Sylhet') ? 'selected' : ''; ?>>Sylhet</option>
                                <option value="Rangpur" <?php echo ($user['division'] == 'Rangpur') ? 'selected' : ''; ?>>Rangpur</option>
                                <option value="Mymensingh" <?php echo ($user['division'] == 'Mymensingh') ? 'selected' : ''; ?>>Mymensingh</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="district">
                                <i class="fas fa-building"></i>
                                District
                            </label>
                            <input type="text" id="district" name="district" value="<?php echo htmlspecialchars($user['district']); ?>" placeholder="Enter your district">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="city">
                                <i class="fas fa-city"></i>
                                City/Area
                            </label>
                            <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($user['city']); ?>" placeholder="Enter your city or area">
                        </div>
                    </div>
                </div>

                <!-- Password Update Section -->
                <div class="form-section password-section">
                    <h3 class="section-title">
                        <i class="fas fa-lock"></i>
                        Update Password
                    </h3>
                    <p class="section-note">Leave these fields blank if you don't want to change your password</p>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="current_password">
                                <i class="fas fa-key"></i>
                                Current Password
                            </label>
                            <div class="password-input-wrapper">
                                <input type="password" id="current_password" name="current_password" placeholder="Enter current password">
                                <button type="button" class="password-toggle" onclick="togglePassword('current_password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="new_password">
                                <i class="fas fa-lock"></i>
                                New Password
                            </label>
                            <div class="password-input-wrapper">
                                <input type="password" id="new_password" name="new_password" placeholder="Enter new password">
                                <button type="button" class="password-toggle" onclick="togglePassword('new_password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="confirm_new_password">
                                <i class="fas fa-check-circle"></i>
                                Confirm New Password
                            </label>
                            <div class="password-input-wrapper">
                                <input type="password" id="confirm_new_password" name="confirm_new_password" placeholder="Confirm new password">
                                <button type="button" class="password-toggle" onclick="togglePassword('confirm_new_password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="form-actions">
                    <button type="submit" class="update-btn">
                        <i class="fas fa-save"></i>
                        <span>Update Profile</span>
                    </button>
                    <a href="dashboard.php" class="cancel-btn">
                        <i class="fas fa-times"></i>
                        <span>Cancel</span>
                    </a>
                </div>
            </form>
        </div>
    </div>
</section>

<style>
    /* Enhanced Profile Page Styles */
    .profile-page {
        position: relative;
        min-height: 100vh;
        background: linear-gradient(135deg, #f8fff9 0%, #e8f5e8 50%, #d4edda 100%);
        overflow: hidden;
        padding: 40px 0;
    }

    .profile-background {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1;
    }

    .profile-pattern {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-image:
            radial-gradient(circle at 20% 80%, rgba(76, 175, 80, 0.1) 0%, transparent 50%),
            radial-gradient(circle at 80% 20%, rgba(139, 195, 74, 0.1) 0%, transparent 50%),
            radial-gradient(circle at 40% 40%, rgba(76, 175, 80, 0.05) 0%, transparent 50%);
    }

    .profile-container {
        position: relative;
        z-index: 2;
        max-width: 900px;
        margin: 0 auto;
        padding: 0 20px;
    }

    .profile-card {
        background: white;
        border-radius: 25px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    /* Profile Header */
    .profile-header {
        background: linear-gradient(135deg, #4CAF50, #8BC34A);
        padding: 40px;
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: relative;
        overflow: hidden;
    }

    .profile-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
        animation: float 6s ease-in-out infinite;
    }

    @keyframes float {
        0%, 100% { transform: translateY(0px) rotate(0deg); }
        50% { transform: translateY(-20px) rotate(180deg); }
    }

    .profile-header-content {
        display: flex;
        align-items: center;
        gap: 20px;
        position: relative;
        z-index: 2;
    }

    .profile-icon {
        width: 70px;
        height: 70px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        backdrop-filter: blur(10px);
        border: 2px solid rgba(255, 255, 255, 0.3);
    }

    .profile-title {
        font-size: 2.5rem;
        font-weight: 800;
        margin: 0;
        text-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .profile-subtitle {
        font-size: 1.1rem;
        opacity: 0.9;
        margin: 5px 0 0 0;
    }

    .current-profile-photo {
        position: relative;
        z-index: 2;
    }

    .current-photo {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid rgba(255, 255, 255, 0.3);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }

    .default-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        border: 4px solid rgba(255, 255, 255, 0.3);
        backdrop-filter: blur(10px);
    }

    /* Messages */
    .message-wrapper {
        margin: 30px 40px 0;
    }

    .message-content {
        padding: 15px 20px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        gap: 12px;
        font-weight: 600;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .error-wrapper .message-content {
        background: linear-gradient(135deg, #fff3cd, #ffeaa7);
        color: #856404;
        border-left: 4px solid #ffc107;
    }

    .success-wrapper .message-content {
        background: linear-gradient(135deg, #d4edda, #c3e6cb);
        color: #155724;
        border-left: 4px solid #28a745;
    }

    /* Form Styles */
    .profile-form {
        padding: 40px;
    }

    .form-section {
        margin-bottom: 40px;
        padding-bottom: 30px;
        border-bottom: 2px solid #f0f0f0;
    }

    .form-section:last-of-type {
        border-bottom: none;
        margin-bottom: 0;
    }

    .section-title {
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 1.4rem;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #e8f5e8;
    }

    .section-title i {
        color: #4CAF50;
        font-size: 1.2rem;
    }

    .section-note {
        color: #666;
        font-size: 0.95rem;
        margin-bottom: 20px;
        font-style: italic;
    }

    /* Photo Upload Section */
    .photo-section {
        text-align: center;
    }

    .photo-upload-wrapper {
        display: flex;
        align-items: center;
        gap: 30px;
        justify-content: center;
    }

    .photo-preview {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        overflow: hidden;
        border: 4px solid #e8f5e8;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }

    .preview-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .preview-placeholder {
        width: 100%;
        height: 100%;
        background: #f8fff9;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #666;
        font-size: 0.9rem;
    }

    .preview-placeholder i {
        font-size: 2rem;
        margin-bottom: 8px;
        color: #4CAF50;
    }

    .photo-upload-controls {
        text-align: left;
    }

    .photo-upload-btn {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        background: linear-gradient(135deg, #4CAF50, #8BC34A);
        color: white;
        padding: 12px 24px;
        border-radius: 15px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
        margin-bottom: 10px;
    }

    .photo-upload-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(76, 175, 80, 0.4);
    }

    .photo-help-text {
        color: #666;
        font-size: 0.85rem;
        margin: 0;
    }

    /* Form Layout */
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 25px;
        margin-bottom: 25px;
    }

    .form-row:last-child {
        margin-bottom: 0;
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    .form-row .form-group:only-child {
        grid-column: 1 / -1;
    }

    .form-group label {
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 8px;
        font-size: 0.95rem;
    }

    .form-group label i {
        color: #4CAF50;
        width: 16px;
        text-align: center;
    }

    .form-group input,
    .form-group select {
        padding: 15px 20px;
        border: 2px solid #e8f5e8;
        border-radius: 15px;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: white;
        color: #2c3e50;
    }

    .form-group input:focus,
    .form-group select:focus {
        outline: none;
        border-color: #4CAF50;
        box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
        transform: translateY(-2px);
    }

    .form-group input:disabled {
        background: #f8f9fa;
        color: #6c757d;
        cursor: not-allowed;
    }

    .field-note {
        color: #666;
        font-size: 0.85rem;
        margin-top: 5px;
        font-style: italic;
    }

    /* Password Input Wrapper */
    .password-input-wrapper {
        position: relative;
    }

    .password-toggle {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #666;
        cursor: pointer;
        padding: 5px;
        border-radius: 5px;
        transition: all 0.3s ease;
    }

    .password-toggle:hover {
        color: #4CAF50;
        background: rgba(76, 175, 80, 0.1);
    }

    /* Form Actions */
    .form-actions {
        display: flex;
        gap: 20px;
        justify-content: center;
        margin-top: 40px;
        padding-top: 30px;
        border-top: 2px solid #f0f0f0;
    }

    .update-btn {
        display: flex;
        align-items: center;
        gap: 10px;
        background: linear-gradient(135deg, #4CAF50, #8BC34A);
        color: white;
        border: none;
        padding: 18px 40px;
        border-radius: 15px;
        font-size: 1.1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
    }

    .update-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(76, 175, 80, 0.4);
    }

    .cancel-btn {
        display: flex;
        align-items: center;
        gap: 10px;
        background: transparent;
        color: #6c757d;
        border: 2px solid #e9ecef;
        padding: 16px 30px;
        border-radius: 15px;
        font-size: 1.1rem;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .cancel-btn:hover {
        background: #6c757d;
        color: white;
        transform: translateY(-2px);
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .profile-container {
            padding: 0 15px;
        }

        .profile-header {
            flex-direction: column;
            gap: 20px;
            text-align: center;
            padding: 30px 20px;
        }

        .profile-title {
            font-size: 2rem;
        }

        .profile-form {
            padding: 30px 20px;
        }

        .form-row {
            grid-template-columns: 1fr;
            gap: 20px;
        }

        .photo-upload-wrapper {
            flex-direction: column;
            gap: 20px;
        }

        .form-actions {
            flex-direction: column;
            align-items: center;
        }

        .update-btn,
        .cancel-btn {
            width: 100%;
            justify-content: center;
        }
    }

    @media (max-width: 480px) {
        .profile-icon {
            width: 60px;
            height: 60px;
            font-size: 1.5rem;
        }

        .current-photo,
        .default-avatar {
            width: 70px;
            height: 70px;
        }

        .photo-preview {
            width: 100px;
            height: 100px;
        }

        .section-title {
            font-size: 1.2rem;
        }
    }
</style>

<script>
    // Photo preview functionality
    document.getElementById('profile_photo').addEventListener('change', function(e) {
        const file = e.target.files[0];
        const preview = document.getElementById('photoPreview');
        
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = `<img src="${e.target.result}" alt="Preview" class="preview-image">`;
            };
            reader.readAsDataURL(file);
        }
    });

    // Password toggle functionality
    function togglePassword(fieldId) {
        const field = document.getElementById(fieldId);
        const toggle = field.nextElementSibling.querySelector('i');
        
        if (field.type === 'password') {
            field.type = 'text';
            toggle.classList.remove('fa-eye');
            toggle.classList.add('fa-eye-slash');
        } else {
            field.type = 'password';
            toggle.classList.remove('fa-eye-slash');
            toggle.classList.add('fa-eye');
        }
    }

    // Form validation
    document.querySelector('.profile-form').addEventListener('submit', function(e) {
        const newPassword = document.getElementById('new_password').value;
        const confirmPassword = document.getElementById('confirm_new_password').value;
        const currentPassword = document.getElementById('current_password').value;
        
        // If any password field is filled, all should be filled
        if (newPassword || confirmPassword || currentPassword) {
            if (!currentPassword) {
                alert('Please enter your current password to change it.');
                e.preventDefault();
                return;
            }
            
            if (newPassword !== confirmPassword) {
                alert('New passwords do not match.');
                e.preventDefault();
                return;
            }
            
            if (newPassword.length < 6) {
                alert('New password must be at least 6 characters long.');
                e.preventDefault();
                return;
            }
        }
    });
</script>

<?php include 'includes/footer.php'; ?>
