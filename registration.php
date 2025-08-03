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
<div class="form-container">
    <form action="php/register_process.php" method="post" enctype="multipart/form-data" onsubmit="return validateRegistrationForm()">
        <h2>Create Your Account</h2>
        <?php
        if (isset($_SESSION['error'])) { echo '<p class="error-message">' . $_SESSION['error'] . '</p>'; unset($_SESSION['error']); }
        ?>
        <div class="form-group"><label for="name">Full Name</label><input type="text" id="name" name="name" required><small class="error-text" id="name-error"></small></div>
        <div class="form-group"><label for="email">Email</label><input type="email" id="email" name="email" required><small class="error-text" id="email-error"></small></div>
        <div class="form-group"><label for="phone">Phone</label><input type="tel" id="phone" name="phone" required><small class="error-text" id="phone-error"></small></div>
        
        <div class="form-group">
            <label for="profile_photo">Profile Photo (Optional)</label>
            <input type="file" id="profile_photo" name="profile_photo" accept="image/*">
        </div>
        
        <div class="form-group">
            <label for="division">Division</label>
            <select id="division" name="division" required></select>
        </div>
        <div class="form-group">
            <label for="district">District</label>
            <select id="district" name="district" required></select>
        </div>
        <div class="form-group">
            <label for="city">City / Upazila</label>
            <select id="city" name="city" required></select>
        </div>

        <div class="form-group"><label for="role">Register as</label><select id="role" name="role" required><option value="Buyer">Buyer</option><option value="Seller">Seller</option></select></div>
        <div class="form-group"><label for="password">Password</label><input type="password" id="password" name="password" required><small class="error-text" id="password-error">8+ chars, with uppercase, number, special char.</small></div>
        <div class="form-group"><label for="confirm_password">Confirm Password</label><input type="password" id="confirm_password" name="confirm_password" required><small class="error-text" id="confirm-password-error"></small></div>
        <button type="submit" class="submit-button">Register</button>
        <p class="switch-form">Already have an account? <a href="login.php">Login</a></p>
    </form>
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
divisionSelect.innerHTML = '<option value="">Select Division</option>';
Object.keys(locations).forEach(division => {
    divisionSelect.innerHTML += `<option value="${division}">${division}</option>`;
});
</script>
<script src="js/validation.js"></script>
<?php include 'includes/footer.php'; ?>
