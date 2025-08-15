<?php
// FILE: php/wishlist_manager.php
session_start();
include '../includes/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['loggedin'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in to manage wishlist']);
    exit;
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';
$product_id = $_POST['product_id'] ?? 0;

switch ($action) {
    case 'add':
        addToWishlist($conn, $user_id, $product_id);
        break;
    case 'remove':
        removeFromWishlist($conn, $user_id, $product_id);
        break;
    case 'toggle':
        toggleWishlist($conn, $user_id, $product_id);
        break;
    case 'get_items':
        getWishlistItems($conn, $user_id);
        break;
    case 'check_status':
        checkWishlistStatus($conn, $user_id, $product_id);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

function addToWishlist($conn, $user_id, $product_id) {
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
        
        // Add to wishlist (ignore duplicates)
        $stmt = $conn->prepare("INSERT IGNORE INTO wishlist (user_id, product_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $user_id, $product_id);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                // Get wishlist count
                $countStmt = $conn->prepare("SELECT COUNT(*) as count FROM wishlist WHERE user_id = ?");
                $countStmt->bind_param("i", $user_id);
                $countStmt->execute();
                $count = $countStmt->get_result()->fetch_assoc()['count'];
                
                echo json_encode([
                    'success' => true, 
                    'message' => $product['name'] . ' added to wishlist!',
                    'wishlist_count' => $count,
                    'is_in_wishlist' => true
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Product already in wishlist']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add to wishlist']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}

function removeFromWishlist($conn, $user_id, $product_id) {
    try {
        $stmt = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $user_id, $product_id);
        
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            // Get updated wishlist count
            $countStmt = $conn->prepare("SELECT COUNT(*) as count FROM wishlist WHERE user_id = ?");
            $countStmt->bind_param("i", $user_id);
            $countStmt->execute();
            $count = $countStmt->get_result()->fetch_assoc()['count'];
            
            echo json_encode([
                'success' => true, 
                'message' => 'Item removed from wishlist',
                'wishlist_count' => $count,
                'is_in_wishlist' => false
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Item not found in wishlist']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}

function toggleWishlist($conn, $user_id, $product_id) {
    try {
        // Check if item exists in wishlist
        $checkStmt = $conn->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
        $checkStmt->bind_param("ii", $user_id, $product_id);
        $checkStmt->execute();
        $exists = $checkStmt->get_result()->num_rows > 0;
        
        if ($exists) {
            removeFromWishlist($conn, $user_id, $product_id);
        } else {
            addToWishlist($conn, $user_id, $product_id);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}

function getWishlistItems($conn, $user_id) {
    try {
        $stmt = $conn->prepare("
            SELECT w.*, p.name, p.price, p.unit, p.image_path, p.stock, p.category,
                   CASE WHEN p.stock > 0 THEN 'available' ELSE 'out_of_stock' END as availability
            FROM wishlist w
            JOIN products p ON w.product_id = p.id
            WHERE w.user_id = ?
            ORDER BY w.created_at DESC
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        
        echo json_encode([
            'success' => true,
            'items' => $items,
            'count' => count($items)
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}

function checkWishlistStatus($conn, $user_id, $product_id) {
    try {
        $stmt = $conn->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $user_id, $product_id);
        $stmt->execute();
        $exists = $stmt->get_result()->num_rows > 0;
        
        echo json_encode([
            'success' => true,
            'is_in_wishlist' => $exists
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}

$conn->close();
?>
