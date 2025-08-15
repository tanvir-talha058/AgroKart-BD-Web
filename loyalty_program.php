<?php
// FILE: loyalty_program.php
include 'includes/header.php';

// Redirect if not logged in
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loyalty Program - AgroKartBD</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/loyalty-style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <div class="loyalty-container">
        <!-- Loyalty Header -->
        <div class="loyalty-header">
            <div class="header-content">
                <div class="loyalty-icon">
                    <i class="fas fa-crown"></i>
                </div>
                <div class="loyalty-title-content">
                    <h1 class="loyalty-title">Loyalty Program</h1>
                    <p class="loyalty-subtitle">Earn points with every purchase and unlock exclusive rewards</p>
                </div>
            </div>
        </div>

        <!-- Notification Container -->
        <div id="notification-container"></div>

        <!-- Loading State -->
        <div id="loading-spinner" class="loading-spinner">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Loading your loyalty status...</p>
        </div>

        <!-- Loyalty Status Card -->
        <div id="loyalty-status" class="loyalty-status" style="display: none;">
            <div class="status-card">
                <div class="tier-badge">
                    <i class="fas fa-medal"></i>
                    <span id="user-tier">Bronze</span>
                </div>
                
                <div class="points-display">
                    <div class="current-points">
                        <span class="points-number" id="current-points">0</span>
                        <span class="points-label">Available Points</span>
                    </div>
                    
                    <div class="points-stats">
                        <div class="stat">
                            <span class="stat-value" id="total-earned">0</span>
                            <span class="stat-label">Total Earned</span>
                        </div>
                        <div class="stat">
                            <span class="stat-value" id="total-redeemed">0</span>
                            <span class="stat-label">Total Redeemed</span>
                        </div>
                    </div>
                </div>

                <div class="member-info">
                    <div class="member-detail">
                        <i class="fas fa-calendar"></i>
                        <span>Member since <span id="member-since">-</span></span>
                    </div>
                    <div class="member-detail">
                        <i class="fas fa-shopping-bag"></i>
                        <span><span id="total-orders">0</span> orders completed</span>
                    </div>
                    <div class="member-detail">
                        <i class="fas fa-money-bill"></i>
                        <span>৳<span id="total-spent">0</span> total spent</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Next Tier Progress -->
        <div id="tier-progress" class="tier-progress" style="display: none;">
            <h3><i class="fas fa-trophy"></i> Next Tier Progress</h3>
            <div class="progress-card">
                <div class="progress-info">
                    <span class="current-tier-name" id="current-tier-name">Bronze</span>
                    <i class="fas fa-arrow-right"></i>
                    <span class="next-tier-name" id="next-tier-name">Silver</span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" id="tier-progress-fill"></div>
                </div>
                <div class="progress-details">
                    <span>Spend ৳<span id="remaining-amount">0</span> more to reach <span id="next-tier-target">Silver</span></span>
                </div>
            </div>
        </div>

        <!-- Tier Benefits -->
        <div class="tier-benefits">
            <h3><i class="fas fa-star"></i> Your Tier Benefits</h3>
            <div class="benefits-grid" id="benefits-grid">
                <!-- Benefits will be loaded dynamically -->
            </div>
        </div>

        <!-- Available Rewards -->
        <div class="rewards-section">
            <h3><i class="fas fa-gift"></i> Available Rewards</h3>
            <div class="rewards-grid" id="rewards-grid">
                <!-- Rewards will be loaded dynamically -->
            </div>
        </div>

        <!-- How to Earn Points -->
        <div class="earn-points-section">
            <h3><i class="fas fa-coins"></i> How to Earn Points</h3>
            <div class="earn-methods">
                <div class="earn-method">
                    <div class="method-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="method-info">
                        <h4>Shop & Earn</h4>
                        <p>Earn 1 point for every ৳10 spent</p>
                    </div>
                </div>
                
                <div class="earn-method">
                    <div class="method-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="method-info">
                        <h4>Write Reviews</h4>
                        <p>Earn 10 points for each product review</p>
                    </div>
                </div>
                
                <div class="earn-method">
                    <div class="method-icon">
                        <i class="fas fa-share-alt"></i>
                    </div>
                    <div class="method-info">
                        <h4>Refer Friends</h4>
                        <p>Earn 100 points for each successful referral</p>
                    </div>
                </div>
                
                <div class="earn-method">
                    <div class="method-icon">
                        <i class="fas fa-birthday-cake"></i>
                    </div>
                    <div class="method-info">
                        <h4>Birthday Bonus</h4>
                        <p>Get 50 bonus points on your birthday</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="activity-section">
            <h3><i class="fas fa-history"></i> Recent Activity</h3>
            <div class="activity-list" id="activity-list">
                <!-- Activity will be loaded dynamically -->
            </div>
        </div>

        <!-- Tier Information -->
        <div class="tier-info-section">
            <h3><i class="fas fa-info-circle"></i> Tier Information</h3>
            <div class="tier-cards">
                <div class="tier-card bronze">
                    <div class="tier-header">
                        <i class="fas fa-medal"></i>
                        <h4>Bronze</h4>
                    </div>
                    <div class="tier-requirements">
                        <p>Starting tier for all members</p>
                    </div>
                    <div class="tier-perks">
                        <ul>
                            <li>Earn 1x points</li>
                            <li>Free delivery on orders ৳1000+</li>
                            <li>Basic customer support</li>
                        </ul>
                    </div>
                </div>

                <div class="tier-card silver">
                    <div class="tier-header">
                        <i class="fas fa-medal"></i>
                        <h4>Silver</h4>
                    </div>
                    <div class="tier-requirements">
                        <p>Spend ৳5,000 to unlock</p>
                    </div>
                    <div class="tier-perks">
                        <ul>
                            <li>Earn 1.2x points</li>
                            <li>Free delivery on orders ৳800+</li>
                            <li>Special member discounts</li>
                        </ul>
                    </div>
                </div>

                <div class="tier-card gold">
                    <div class="tier-header">
                        <i class="fas fa-medal"></i>
                        <h4>Gold</h4>
                    </div>
                    <div class="tier-requirements">
                        <p>Spend ৳20,000 to unlock</p>
                    </div>
                    <div class="tier-perks">
                        <ul>
                            <li>Earn 1.5x points</li>
                            <li>Free delivery on orders ৳500+</li>
                            <li>Early access to sales</li>
                            <li>Priority customer support</li>
                        </ul>
                    </div>
                </div>

                <div class="tier-card platinum">
                    <div class="tier-header">
                        <i class="fas fa-crown"></i>
                        <h4>Platinum</h4>
                    </div>
                    <div class="tier-requirements">
                        <p>Spend ৳50,000 to unlock</p>
                    </div>
                    <div class="tier-perks">
                        <ul>
                            <li>Earn 2x points</li>
                            <li>Always free delivery</li>
                            <li>Exclusive products access</li>
                            <li>Dedicated account manager</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Redeem Points Modal -->
    <div id="redeem-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-gift"></i> Redeem Reward</h3>
                <button class="close-btn" onclick="closeRedeemModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body" id="redeem-modal-body">
                <!-- Redemption details will be loaded here -->
            </div>
        </div>
    </div>

    <script src="js/loyalty-program.js"></script>
</body>
</html>

<?php include 'includes/footer.php'; ?>
