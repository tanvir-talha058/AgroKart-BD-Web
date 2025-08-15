<?php
// FILE: php/loyalty_manager.php
session_start();
include __DIR__ . '/../includes/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['loggedin'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in to access loyalty program']);
    exit;
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'get_status':
        getLoyaltyStatus($conn, $user_id);
        break;
    case 'add_points':
        addLoyaltyPoints($conn, $user_id, $_POST['points'] ?? 0, $_POST['reason'] ?? '');
        break;
    case 'redeem_points':
        redeemLoyaltyPoints($conn, $user_id, $_POST['points'] ?? 0);
        break;
    case 'get_history':
        getLoyaltyHistory($conn, $user_id);
        break;
    case 'get_rewards':
        getAvailableRewards($conn, $user_id);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

function getLoyaltyStatus($conn, $user_id) {
    try {
        // Initialize loyalty account if doesn't exist
        initializeLoyaltyAccount($conn, $user_id);
        
        $stmt = $conn->prepare("
            SELECT lp.*, 
                   COUNT(DISTINCT o.id) as total_orders,
                   COALESCE(SUM(o.total_amount), 0) as total_spent
            FROM loyalty_points lp
            LEFT JOIN orders o ON lp.user_id = o.user_id AND o.status = 'Delivered'
            WHERE lp.user_id = ?
            GROUP BY lp.user_id
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $loyalty = $result->fetch_assoc();
            
            // Calculate tier benefits
            $tier_benefits = getTierBenefits($loyalty['tier']);
            
            // Calculate next tier requirements
            $next_tier_info = getNextTierInfo($loyalty['tier'], $loyalty['total_spent']);
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'current_points' => $loyalty['points'],
                    'total_earned' => $loyalty['total_earned'],
                    'total_redeemed' => $loyalty['total_redeemed'],
                    'tier' => $loyalty['tier'],
                    'total_orders' => $loyalty['total_orders'],
                    'total_spent' => $loyalty['total_spent'],
                    'tier_benefits' => $tier_benefits,
                    'next_tier' => $next_tier_info,
                    'points_expiry' => date('Y-m-d', strtotime('+1 year')), // Points expire in 1 year
                    'member_since' => date('F Y', strtotime($loyalty['created_at']))
                ]
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Loyalty account not found']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}

function addLoyaltyPoints($conn, $user_id, $points, $reason) {
    try {
        // Initialize loyalty account if doesn't exist
        initializeLoyaltyAccount($conn, $user_id);
        
        // Add points
        $stmt = $conn->prepare("
            UPDATE loyalty_points 
            SET points = points + ?, 
                total_earned = total_earned + ?
            WHERE user_id = ?
        ");
        $stmt->bind_param("iii", $points, $points, $user_id);
        
        if ($stmt->execute()) {
            // Update tier if necessary
            updateUserTier($conn, $user_id);
            
            // Log the transaction
            logLoyaltyTransaction($conn, $user_id, $points, 'earned', $reason);
            
            echo json_encode([
                'success' => true,
                'message' => "You earned $points loyalty points!",
                'points_added' => $points
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add points']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}

function redeemLoyaltyPoints($conn, $user_id, $points) {
    try {
        // Check if user has enough points
        $checkStmt = $conn->prepare("SELECT points FROM loyalty_points WHERE user_id = ?");
        $checkStmt->bind_param("i", $user_id);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        
        if ($result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Loyalty account not found']);
            return;
        }
        
        $current_points = $result->fetch_assoc()['points'];
        
        if ($current_points < $points) {
            echo json_encode(['success' => false, 'message' => 'Insufficient points']);
            return;
        }
        
        // Redeem points
        $stmt = $conn->prepare("
            UPDATE loyalty_points 
            SET points = points - ?, 
                total_redeemed = total_redeemed + ?
            WHERE user_id = ?
        ");
        $stmt->bind_param("iii", $points, $points, $user_id);
        
        if ($stmt->execute()) {
            // Calculate discount amount (1 point = ৳0.10)
            $discount_amount = $points * 0.10;
            
            // Log the redemption
            logLoyaltyTransaction($conn, $user_id, $points, 'redeemed', 'Points redeemed for discount');
            
            echo json_encode([
                'success' => true,
                'message' => "Successfully redeemed $points points!",
                'discount_amount' => $discount_amount,
                'remaining_points' => $current_points - $points
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to redeem points']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}

function getLoyaltyHistory($conn, $user_id) {
    try {
        $stmt = $conn->prepare("
            SELECT * FROM loyalty_transactions 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT 20
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $history = [];
        while ($row = $result->fetch_assoc()) {
            $history[] = [
                'id' => $row['id'],
                'points' => $row['points'],
                'type' => $row['transaction_type'],
                'reason' => $row['reason'],
                'date' => date('M j, Y g:i A', strtotime($row['created_at']))
            ];
        }
        
        echo json_encode([
            'success' => true,
            'history' => $history
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}

function getAvailableRewards($conn, $user_id) {
    try {
        // Get user's current points
        $pointsStmt = $conn->prepare("SELECT points, tier FROM loyalty_points WHERE user_id = ?");
        $pointsStmt->bind_param("i", $user_id);
        $pointsStmt->execute();
        $pointsResult = $pointsStmt->get_result();
        
        if ($pointsResult->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Loyalty account not found']);
            return;
        }
        
        $userData = $pointsResult->fetch_assoc();
        $current_points = $userData['points'];
        $tier = $userData['tier'];
        
        // Define available rewards
        $rewards = [
            [
                'id' => 1,
                'title' => '5% Discount',
                'description' => 'Get 5% off your next order',
                'points_required' => 100,
                'discount_percentage' => 5,
                'type' => 'percentage_discount',
                'min_order' => 500,
                'available' => $current_points >= 100
            ],
            [
                'id' => 2,
                'title' => '৳50 Off',
                'description' => 'Get ৳50 off orders above ৳1000',
                'points_required' => 200,
                'discount_amount' => 50,
                'type' => 'fixed_discount',
                'min_order' => 1000,
                'available' => $current_points >= 200
            ],
            [
                'id' => 3,
                'title' => 'Free Delivery',
                'description' => 'Free delivery on your next order',
                'points_required' => 50,
                'type' => 'free_delivery',
                'min_order' => 0,
                'available' => $current_points >= 50
            ],
            [
                'id' => 4,
                'title' => '10% Discount',
                'description' => 'Get 10% off your next order',
                'points_required' => 300,
                'discount_percentage' => 10,
                'type' => 'percentage_discount',
                'min_order' => 1000,
                'available' => $current_points >= 300
            ],
            [
                'id' => 5,
                'title' => '৳100 Off',
                'description' => 'Get ৳100 off orders above ৳2000',
                'points_required' => 500,
                'discount_amount' => 100,
                'type' => 'fixed_discount',
                'min_order' => 2000,
                'available' => $current_points >= 500
            ]
        ];
        
        // Add tier-specific rewards
        if ($tier === 'Gold' || $tier === 'Platinum') {
            $rewards[] = [
                'id' => 6,
                'title' => 'Priority Support',
                'description' => 'Get priority customer support for 1 month',
                'points_required' => 150,
                'type' => 'service',
                'available' => $current_points >= 150
            ];
        }
        
        if ($tier === 'Platinum') {
            $rewards[] = [
                'id' => 7,
                'title' => 'Early Access',
                'description' => 'Early access to new products and sales',
                'points_required' => 250,
                'type' => 'access',
                'available' => $current_points >= 250
            ];
        }
        
        echo json_encode([
            'success' => true,
            'rewards' => $rewards,
            'current_points' => $current_points,
            'tier' => $tier
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}

function initializeLoyaltyAccount($conn, $user_id) {
    try {
        $stmt = $conn->prepare("
            INSERT IGNORE INTO loyalty_points (user_id, points, total_earned, total_redeemed, tier) 
            VALUES (?, 0, 0, 0, 'Bronze')
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
    } catch (Exception $e) {
        // Silently fail if account already exists
    }
}

function updateUserTier($conn, $user_id) {
    try {
        // Get total spent
        $stmt = $conn->prepare("
            SELECT COALESCE(SUM(total_amount), 0) as total_spent
            FROM orders 
            WHERE user_id = ? AND status = 'Delivered'
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $total_spent = $result->fetch_assoc()['total_spent'];
        
        // Determine tier based on spending
        $new_tier = 'Bronze';
        if ($total_spent >= 50000) {
            $new_tier = 'Platinum';
        } elseif ($total_spent >= 20000) {
            $new_tier = 'Gold';
        } elseif ($total_spent >= 5000) {
            $new_tier = 'Silver';
        }
        
        // Update tier
        $updateStmt = $conn->prepare("UPDATE loyalty_points SET tier = ? WHERE user_id = ?");
        $updateStmt->bind_param("si", $new_tier, $user_id);
        $updateStmt->execute();
        
    } catch (Exception $e) {
        // Silently fail for tier updates
    }
}

function getTierBenefits($tier) {
    $benefits = [
        'Bronze' => [
            'point_multiplier' => 1,
            'special_discounts' => false,
            'free_delivery_threshold' => 1000,
            'early_access' => false,
            'priority_support' => false
        ],
        'Silver' => [
            'point_multiplier' => 1.2,
            'special_discounts' => true,
            'free_delivery_threshold' => 800,
            'early_access' => false,
            'priority_support' => false
        ],
        'Gold' => [
            'point_multiplier' => 1.5,
            'special_discounts' => true,
            'free_delivery_threshold' => 500,
            'early_access' => true,
            'priority_support' => true
        ],
        'Platinum' => [
            'point_multiplier' => 2.0,
            'special_discounts' => true,
            'free_delivery_threshold' => 0,
            'early_access' => true,
            'priority_support' => true
        ]
    ];
    
    return $benefits[$tier] ?? $benefits['Bronze'];
}

function getNextTierInfo($current_tier, $total_spent) {
    $tiers = [
        'Bronze' => ['next' => 'Silver', 'required' => 5000],
        'Silver' => ['next' => 'Gold', 'required' => 20000],
        'Gold' => ['next' => 'Platinum', 'required' => 50000],
        'Platinum' => ['next' => null, 'required' => null]
    ];
    
    $tier_info = $tiers[$current_tier];
    
    if ($tier_info['next']) {
        return [
            'next_tier' => $tier_info['next'],
            'required_spending' => $tier_info['required'],
            'remaining_spending' => max(0, $tier_info['required'] - $total_spent),
            'progress_percentage' => min(100, ($total_spent / $tier_info['required']) * 100)
        ];
    }
    
    return null;
}

function logLoyaltyTransaction($conn, $user_id, $points, $type, $reason) {
    try {
        // Create loyalty_transactions table if it doesn't exist
        $createTable = "
            CREATE TABLE IF NOT EXISTS loyalty_transactions (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT NOT NULL,
                points INT NOT NULL,
                transaction_type ENUM('earned', 'redeemed') NOT NULL,
                reason VARCHAR(255),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )
        ";
        $conn->query($createTable);
        
        $stmt = $conn->prepare("
            INSERT INTO loyalty_transactions (user_id, points, transaction_type, reason) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("iiss", $user_id, $points, $type, $reason);
        $stmt->execute();
        
    } catch (Exception $e) {
        // Silently fail for transaction logging
    }
}

$conn->close();
?>
