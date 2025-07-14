<?php
// FILE: profile.php
include 'includes/header.php';
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}
// Fetch current user data for the form
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT name, email, phone, division, district, city FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>
<div class="form-container" style="padding-top: 40px; padding-bottom: 40px;">
    <form action="php/update_profile_process.php" method="post" enctype="multipart/form-data">
        <h2>Update Your Profile</h2>

        <?php
        if (isset($_SESSION['error'])) { echo '<p class="error-message">' . $_SESSION['error'] . '</p>'; unset($_SESSION['error']); }
        if (isset($_SESSION['message'])) { echo '<p class="success-message">' . $_SESSION['message'] . '</p>'; unset($_SESSION['message']); }
        ?>
        
        <div class="form-group">
            <label for="profile_photo">Profile Photo</label>
            <input type="file" id="profile_photo" name="profile_photo" accept="image/*">
            <small>Leave blank to keep your current photo.</small>
        </div>

        <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
        </div>

        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
            <small>Email cannot be changed.</small>
        </div>

        <div class="form-group">
            <label for="phone">Phone Number</label>
            <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
        </div>

        <hr style="margin: 20px 0;">
        <h3>Update Password</h3>
        <small>Leave these fields blank if you don't want to change your password.</small>
        
        <div class="form-group" style="margin-top: 15px;">
            <label for="current_password">Current Password</label>
            <input type="password" id="current_password" name="current_password">
        </div>
        <div class="form-group">
            <label for="new_password">New Password</label>
            <input type="password" id="new_password" name="new_password">
        </div>
        <div class="form-group">
            <label for="confirm_new_password">Confirm New Password</label>
            <input type="password" id="confirm_new_password" name="confirm_new_password">
        </div>

        <button type="submit" class="submit-button">Update Profile</button>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
