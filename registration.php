<?php 
// FILE: registration.php

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Redirect if already logged in
if (isset($_SESSION['loggedin'])) { 
    header('Location: index.php'); 
    exit; 
}

// Include header after checking for redirect
include 'includes/header.php';
?>
<div class="registration-page">
    <!-- Floating shapes for decoration -->
    <div class="floating-shape shape-1"></div>
    <div class="floating-shape shape-2"></div>
    <div class="floating-shape shape-3"></div>
    <div class="floating-shape shape-4"></div>
    
    <div class="registration-container">
        <div class="registration-card">
            <div class="registration-header">
                <div class="registration-logo">
                    <img src="images/AGrO.png" alt="AgroKart Logo" style="height: 60px;">
                </div>
                <h2>Create Your Account</h2>
                <p>Join AgroKart and start your agricultural journey</p>
            </div>
            
            <form action="php/register_process.php" method="post" enctype="multipart/form-data" onsubmit="return validateRegistrationForm()" class="registration-form">
                <?php
                if (isset($_SESSION['error'])) { 
                    echo '<div class="error-message"><i class="fas fa-exclamation-circle"></i>' . $_SESSION['error'] . '</div>'; 
                    unset($_SESSION['error']); 
                }
                ?>
                
                <div class="form-row">
                    <div class="form-group">
                        <div class="input-icon-wrapper">
                            <i class="fas fa-user"></i>
                            <input type="text" id="name" name="name" placeholder="Full Name" required>
                        </div>
                        <small class="error-text" id="name-error"></small>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <div class="input-icon-wrapper">
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="email" name="email" placeholder="Email Address" required>
                        </div>
                        <small class="error-text" id="email-error"></small>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <div class="input-icon-wrapper">
                            <i class="fas fa-phone"></i>
                            <input type="tel" id="phone" name="phone" placeholder="Phone Number" required>
                        </div>
                        <small class="error-text" id="phone-error"></small>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <div class="file-upload-wrapper">
                            <i class="fas fa-camera"></i>
                            <input type="file" id="profile_photo" name="profile_photo" accept="image/*">
                            <label for="profile_photo" class="file-upload-label">
                                <span>Choose Profile Photo (Optional)</span>
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <div class="input-icon-wrapper">
                            <i class="fas fa-map-marker-alt"></i>
                            <select id="division" name="division" required>
                                <option value="">Select Division</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <div class="input-icon-wrapper">
                            <i class="fas fa-city"></i>
                            <select id="district" name="district" required>
                                <option value="">Select District</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <div class="input-icon-wrapper">
                            <i class="fas fa-building"></i>
                            <select id="city" name="city" required>
                                <option value="">Select City/Upazila</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <div class="input-icon-wrapper">
                            <i class="fas fa-user-tag"></i>
                            <select id="role" name="role" required>
                                <option value="">Register as</option>
                                <option value="Buyer">Buyer</option>
                                <option value="Seller">Seller</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <div class="input-icon-wrapper">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="password" name="password" placeholder="Password" required>
                            <button type="button" class="password-toggle" onclick="togglePassword('password', 'toggleIcon1')">
                                <i class="fas fa-eye" id="toggleIcon1"></i>
                            </button>
                        </div>
                        <small class="error-text" id="password-error">8+ chars, with uppercase, number, special char.</small>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <div class="input-icon-wrapper">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
                            <button type="button" class="password-toggle" onclick="togglePassword('confirm_password', 'toggleIcon2')">
                                <i class="fas fa-eye" id="toggleIcon2"></i>
                            </button>
                        </div>
                        <small class="error-text" id="confirm-password-error"></small>
                    </div>
                </div>
                
                <button type="submit" class="register-button">
                    <span>Create Account</span>
                    <i class="fas fa-user-plus"></i>
                </button>
                
                <p class="switch-form">
                    Already have an account? <a href="login.php">Sign In</a>
                </p>
            </form>
        </div>
    </div>
</div>

<script>
// Bangladesh Location Data
const locations = {
    "Dhaka": { "Dhaka": ["Savar", "Gazipur", "Narayanganj"], "Faridpur": ["Faridpur Sadar", "Bhanga"], "Tangail": ["Tangail Sadar"] },
    "Chattogram": { "Chattogram": ["Chattogram Sadar", "Cox's Bazar", "Sitakunda"], "Cumilla": ["Cumilla Sadar"], "Noakhali": ["Noakhali Sadar"] },
    "Rajshahi": { "Rajshahi": ["Rajshahi Sadar", "Natore"], "Bogura": ["Bogura Sadar"], "Pabna": ["Pabna Sadar"] },
    "Khulna": { "Khulna": ["Khulna Sadar", "Jashore", "Satkhira"], "Kushtia": ["Kushtia Sadar"] },
    "Sylhet": { "Sylhet": ["Sylhet Sadar", "Moulvibazar"], "Habiganj": ["Habiganj Sadar"] },
    "Barishal": { "Barishal": ["Barishal Sadar", "Patuakhali"], "Bhola": ["Bhola Sadar"] },
    "Rangpur": { "Rangpur": ["Rangpur Sadar", "Dinajpur"], "Gaibandha": ["Gaibandha Sadar"] },
    "Mymensingh": { "Mymensingh": ["Mymensingh Sadar", "Jamalpur"], "Sherpur": ["Sherpur Sadar"] }
};

const divisionSelect = document.getElementById('division');
const districtSelect = document.getElementById('district');
const citySelect = document.getElementById('city');

divisionSelect.onchange = function() {
    districtSelect.innerHTML = '<option value="">Select District</option>';
    citySelect.innerHTML = '<option value="">Select City/Upazila</option>';
    if (this.value) {
        const districts = Object.keys(locations[this.value]);
        districts.forEach(district => {
            districtSelect.innerHTML += `<option value="${district}">${district}</option>`;
        });
    }
}

districtSelect.onchange = function() {
    citySelect.innerHTML = '<option value="">Select City/Upazila</option>';
    if (this.value) {
        const division = divisionSelect.value;
        const cities = locations[division][this.value];
        cities.forEach(city => {
            citySelect.innerHTML += `<option value="${city}">${city}</option>`;
        });
    }
}

// Populate initial divisions
Object.keys(locations).forEach(division => {
    divisionSelect.innerHTML += `<option value="${division}">${division}</option>`;
});

// Enhanced password toggle function
function togglePassword(inputId, iconId) {
    const passwordInput = document.getElementById(inputId);
    const toggleIcon = document.getElementById(iconId);
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    }
}
</script>
<script src="js/validation.js"></script>
<?php include 'includes/footer.php'; ?>
