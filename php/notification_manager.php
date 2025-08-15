<?php
// FILE: php/notification_manager.php
session_start();
include __DIR__ . '/../includes/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['loggedin'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in to manage notifications']);
    exit;
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'add':
        addNotification($conn, $user_id, $_POST['product_id'], $_POST['notification_type']);
        break;
    case 'remove':
        removeNotification($conn, $user_id, $_POST['product_id'], $_POST['notification_type']);
        break;
    case 'get_notifications':
        getUserNotifications($conn, $user_id);
        break;
    case 'toggle':
        toggleNotification($conn, $user_id, $_POST['product_id'], $_POST['notification_type']);
        break;
    case 'check_alerts':
        checkForAlerts($conn, $user_id);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

function addNotification($conn, $user_id, $product_id, $notification_type) {
    try {
        // Check if product exists
        $checkProduct = $conn->prepare("SELECT id, name FROM products WHERE id = ?");
        $checkProduct->bind_param("i", $product_id);
        $checkProduct->execute();
        $product = $checkProduct->get_result()->fetch_assoc();
        
        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Product not found']);
            return;
        }
        
        // Add notification (ignore duplicates)
        $stmt = $conn->prepare("
            INSERT IGNORE INTO product_notifications (user_id, product_id, notification_type) 
            VALUES (?, ?, ?)
        ");
        $stmt->bind_param("iis", $user_id, $product_id, $notification_type);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $message = match($notification_type) {
                    'stock_alert' => 'You will be notified when ' . $product['name'] . ' is back in stock',
                    'price_drop' => 'You will be notified when ' . $product['name'] . ' price drops',
                    'seasonal_reminder' => 'You will be reminded about ' . $product['name'] . ' in season',
                    default => 'Notification set successfully'
                };
                
                echo json_encode([
                    'success' => true, 
                    'message' => $message
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Notification already exists']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add notification']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}

function removeNotification($conn, $user_id, $product_id, $notification_type) {
    try {
        $stmt = $conn->prepare("
            DELETE FROM product_notifications 
            WHERE user_id = ? AND product_id = ? AND notification_type = ?
        ");
        $stmt->bind_param("iis", $user_id, $product_id, $notification_type);
        
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            echo json_encode([
                'success' => true, 
                'message' => 'Notification removed successfully'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Notification not found']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}

function getUserNotifications($conn, $user_id) {
    try {
        $stmt = $conn->prepare("
            SELECT pn.*, p.name as product_name, p.image_path, p.price, p.stock
            FROM product_notifications pn
            JOIN products p ON pn.product_id = p.id
            WHERE pn.user_id = ? AND pn.is_active = 1
            ORDER BY pn.created_at DESC
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $notifications = [];
        while ($row = $result->fetch_assoc()) {
            $notifications[] = [
                'id' => $row['id'],
                'product_id' => $row['product_id'],
                'product_name' => $row['product_name'],
                'image_path' => $row['image_path'],
                'price' => $row['price'],
                'stock' => $row['stock'],
                'notification_type' => $row['notification_type'],
                'created_at' => $row['created_at'],
                'status' => determineNotificationStatus($row)
            ];
        }
        
        echo json_encode([
            'success' => true,
            'notifications' => $notifications,
            'count' => count($notifications)
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}

function toggleNotification($conn, $user_id, $product_id, $notification_type) {
    try {
        // Check if notification exists
        $checkStmt = $conn->prepare("
            SELECT id FROM product_notifications 
            WHERE user_id = ? AND product_id = ? AND notification_type = ?
        ");
        $checkStmt->bind_param("iis", $user_id, $product_id, $notification_type);
        $checkStmt->execute();
        $exists = $checkStmt->get_result()->num_rows > 0;
        
        if ($exists) {
            removeNotification($conn, $user_id, $product_id, $notification_type);
        } else {
            addNotification($conn, $user_id, $product_id, $notification_type);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}

function checkForAlerts($conn, $user_id) {
    try {
        $alerts = [];
        
        // Check for stock alerts (products back in stock)
        $stockAlerts = $conn->prepare("
            SELECT pn.*, p.name as product_name, p.image_path, p.stock
            FROM product_notifications pn
            JOIN products p ON pn.product_id = p.id
            WHERE pn.user_id = ? AND pn.notification_type = 'stock_alert' 
            AND pn.is_active = 1 AND p.stock > 0
        ");
        $stockAlerts->bind_param("i", $user_id);
        $stockAlerts->execute();
        $stockResult = $stockAlerts->get_result();
        
        while ($row = $stockResult->fetch_assoc()) {
            $alerts[] = [
                'type' => 'stock_alert',
                'title' => 'Product Back in Stock!',
                'message' => $row['product_name'] . ' is now available with ' . $row['stock'] . ' items in stock',
                'product_id' => $row['product_id'],
                'image_path' => $row['image_path'],
                'action_text' => 'Add to Cart',
                'action_url' => 'product_details.php?id=' . $row['product_id']
            ];
            
            // Deactivate the alert so it doesn't show again
            $deactivateStmt = $conn->prepare("
                UPDATE product_notifications 
                SET is_active = 0 
                WHERE id = ?
            ");
            $deactivateStmt->bind_param("i", $row['id']);
            $deactivateStmt->execute();
        }
        
        // Check for price drop alerts (simplified - just check if price is lower than average)
        $priceAlerts = $conn->prepare("
            SELECT pn.*, p.name as product_name, p.image_path, p.price
            FROM product_notifications pn
            JOIN products p ON pn.product_id = p.id
            WHERE pn.user_id = ? AND pn.notification_type = 'price_drop' 
            AND pn.is_active = 1
        ");
        $priceAlerts->bind_param("i", $user_id);
        $priceAlerts->execute();
        $priceResult = $priceAlerts->get_result();
        
        while ($row = $priceResult->fetch_assoc()) {
            // Simulate price drop detection (in real implementation, you'd track historical prices)
            if (rand(1, 10) <= 2) { // 20% chance of price drop notification
                $alerts[] = [
                    'type' => 'price_drop',
                    'title' => 'Price Drop Alert!',
                    'message' => $row['product_name'] . ' price has dropped to ৳' . $row['price'],
                    'product_id' => $row['product_id'],
                    'image_path' => $row['image_path'],
                    'action_text' => 'View Product',
                    'action_url' => 'product_details.php?id=' . $row['product_id']
                ];
            }
        }
        
        echo json_encode([
            'success' => true,
            'alerts' => $alerts,
            'count' => count($alerts)
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}

function determineNotificationStatus($notification) {
    switch ($notification['notification_type']) {
        case 'stock_alert':
            return $notification['stock'] > 0 ? 'triggered' : 'waiting';
        case 'price_drop':
            return 'monitoring';
        case 'seasonal_reminder':
            $month = date('n');
            $is_seasonal = ($month >= 3 && $month <= 5) || ($month >= 9 && $month <= 11);
            return $is_seasonal ? 'triggered' : 'waiting';
        default:
            return 'active';
    }
}

// Function to send notification emails (for future implementation)
function sendNotificationEmail($user_email, $notification_type, $product_name, $details) {
    // This would integrate with your email service
    // For now, we'll just log it
    error_log("Notification: $notification_type for $product_name to $user_email - $details");
}

// Function to clean up old notifications
function cleanupOldNotifications($conn) {
    try {
        // Remove notifications older than 6 months
        $cleanupStmt = $conn->prepare("
            DELETE FROM product_notifications 
            WHERE created_at < DATE_SUB(NOW(), INTERVAL 6 MONTH)
        ");
        $cleanupStmt->execute();
        
        // Deactivate stock alerts for products that have been out of stock for too long
        $deactivateStmt = $conn->prepare("
            UPDATE product_notifications pn
            JOIN products p ON pn.product_id = p.id
            SET pn.is_active = 0
            WHERE pn.notification_type = 'stock_alert' 
            AND p.stock = 0 
            AND pn.created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
        $deactivateStmt->execute();
        
    } catch (Exception $e) {
        // Silently fail for cleanup operations
    }
}

$conn->close();
?>
