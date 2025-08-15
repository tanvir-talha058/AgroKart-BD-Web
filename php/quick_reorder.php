<?php
// FILE: php/quick_reorder.php
session_start();
include __DIR__ . '/../includes/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['loggedin'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in to use quick reorder']);
    exit;
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'get_frequently_bought':
        getFrequentlyBoughtItems($conn, $user_id);
        break;
    case 'get_last_order':
        getLastOrderItems($conn, $user_id);
        break;
    case 'reorder_items':
        reorderItems($conn, $user_id, $_POST['items'] ?? []);
        break;
    case 'get_reorder_suggestions':
        getReorderSuggestions($conn, $user_id);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

function getFrequentlyBoughtItems($conn, $user_id) {
    try {
        $stmt = $conn->prepare("
            SELECT 
                p.id,
                p.name,
                p.price,
                p.unit,
                p.image_path,
                p.stock,
                p.category,
                COUNT(oi.product_id) as purchase_count,
                AVG(oi.quantity) as avg_quantity,
                MAX(o.created_at) as last_purchased,
                SUM(oi.quantity * oi.price) as total_spent_on_item
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.id
            JOIN products p ON oi.product_id = p.id
            WHERE o.user_id = ? AND o.status = 'Delivered'
            GROUP BY p.id
            HAVING purchase_count >= 2
            ORDER BY purchase_count DESC, last_purchased DESC
            LIMIT 10
        ");
        
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'price' => $row['price'],
                'unit' => $row['unit'],
                'image_path' => $row['image_path'],
                'stock' => $row['stock'],
                'category' => $row['category'],
                'purchase_count' => $row['purchase_count'],
                'suggested_quantity' => round($row['avg_quantity']),
                'last_purchased' => date('M j, Y', strtotime($row['last_purchased'])),
                'total_spent' => $row['total_spent_on_item'],
                'availability' => $row['stock'] > 0 ? 'available' : 'out_of_stock'
            ];
        }
        
        echo json_encode([
            'success' => true,
            'items' => $items,
            'count' => count($items),
            'title' => 'Your Frequently Bought Items'
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}

function getLastOrderItems($conn, $user_id) {
    try {
        // Get the last delivered order
        $orderStmt = $conn->prepare("
            SELECT id, created_at, total_amount
            FROM orders 
            WHERE user_id = ? AND status = 'Delivered'
            ORDER BY created_at DESC 
            LIMIT 1
        ");
        $orderStmt->bind_param("i", $user_id);
        $orderStmt->execute();
        $orderResult = $orderStmt->get_result();
        
        if ($orderResult->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'No previous orders found']);
            return;
        }
        
        $order = $orderResult->fetch_assoc();
        
        // Get items from that order
        $itemsStmt = $conn->prepare("
            SELECT 
                p.id,
                p.name,
                p.price,
                p.unit,
                p.image_path,
                p.stock,
                p.category,
                oi.quantity as ordered_quantity,
                oi.price as ordered_price
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = ?
            ORDER BY oi.id
        ");
        
        $itemsStmt->bind_param("i", $order['id']);
        $itemsStmt->execute();
        $itemsResult = $itemsStmt->get_result();
        
        $items = [];
        while ($row = $itemsResult->fetch_assoc()) {
            $items[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'current_price' => $row['price'],
                'ordered_price' => $row['ordered_price'],
                'price_change' => $row['price'] - $row['ordered_price'],
                'unit' => $row['unit'],
                'image_path' => $row['image_path'],
                'stock' => $row['stock'],
                'category' => $row['category'],
                'ordered_quantity' => $row['ordered_quantity'],
                'suggested_quantity' => $row['ordered_quantity'],
                'availability' => $row['stock'] > 0 ? 'available' : 'out_of_stock'
            ];
        }
        
        echo json_encode([
            'success' => true,
            'items' => $items,
            'order_info' => [
                'id' => $order['id'],
                'date' => date('M j, Y', strtotime($order['created_at'])),
                'total' => $order['total_amount']
            ],
            'count' => count($items),
            'title' => 'Items from Your Last Order'
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}

function reorderItems($conn, $user_id, $items) {
    try {
        if (empty($items)) {
            echo json_encode(['success' => false, 'message' => 'No items selected for reorder']);
            return;
        }
        
        $added_count = 0;
        $failed_items = [];
        $total_added = 0;
        
        foreach ($items as $item) {
            $product_id = $item['product_id'];
            $quantity = $item['quantity'];
            
            // Verify product exists and has stock
            $productStmt = $conn->prepare("
                SELECT name, price, stock, unit, image_path 
                FROM products 
                WHERE id = ?
            ");
            $productStmt->bind_param("i", $product_id);
            $productStmt->execute();
            $productResult = $productStmt->get_result();
            
            if ($productResult->num_rows === 0) {
                $failed_items[] = "Product ID $product_id not found";
                continue;
            }
            
            $product = $productResult->fetch_assoc();
            
            if ($product['stock'] < $quantity) {
                $failed_items[] = $product['name'] . " - insufficient stock (only " . $product['stock'] . " available)";
                continue;
            }
            
            // Add to cart
            if (isset($_SESSION['cart'][$product_id])) {
                $_SESSION['cart'][$product_id]['quantity'] += $quantity;
            } else {
                $_SESSION['cart'][$product_id] = [
                    'name' => $product['name'],
                    'price' => $product['price'],
                    'unit' => $product['unit'],
                    'image' => $product['image_path'],
                    'quantity' => $quantity,
                    'stock' => $product['stock']
                ];
            }
            
            // Update database cart if user is logged in
            if (isset($_SESSION['user_id'])) {
                $cartStmt = $conn->prepare("
                    INSERT INTO cart (user_id, product_id, quantity) 
                    VALUES (?, ?, ?) 
                    ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)
                ");
                $cartStmt->bind_param("iii", $user_id, $product_id, $quantity);
                $cartStmt->execute();
            }
            
            $added_count++;
            $total_added += $quantity;
        }
        
        // Calculate cart total
        $cart_total = 0;
        $cart_count = 0;
        if (isset($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $cart_item) {
                $cart_total += $cart_item['price'] * $cart_item['quantity'];
                $cart_count += $cart_item['quantity'];
            }
        }
        
        $message = $added_count > 0 ? 
            "Successfully added $total_added items from $added_count products to cart!" : 
            "No items could be added to cart";
        
        echo json_encode([
            'success' => $added_count > 0,
            'message' => $message,
            'added_count' => $added_count,
            'total_items' => $total_added,
            'failed_items' => $failed_items,
            'cart_count' => $cart_count,
            'cart_total' => $cart_total
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}

function getReorderSuggestions($conn, $user_id) {
    try {
        // Get items that user typically reorders (based on purchase frequency and time since last order)
        $stmt = $conn->prepare("
            SELECT 
                p.id,
                p.name,
                p.price,
                p.unit,
                p.image_path,
                p.stock,
                p.category,
                COUNT(oi.product_id) as purchase_count,
                AVG(oi.quantity) as avg_quantity,
                MAX(o.created_at) as last_purchased,
                DATEDIFF(NOW(), MAX(o.created_at)) as days_since_last_purchase,
                AVG(DATEDIFF(o2.created_at, LAG(o2.created_at) OVER (PARTITION BY p.id ORDER BY o2.created_at))) as avg_days_between_orders
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.id
            JOIN products p ON oi.product_id = p.id
            LEFT JOIN orders o2 ON o2.user_id = o.user_id
            WHERE o.user_id = ? AND o.status = 'Delivered'
            GROUP BY p.id
            HAVING purchase_count >= 2 
            AND days_since_last_purchase >= 7
            ORDER BY 
                CASE 
                    WHEN avg_days_between_orders IS NOT NULL AND days_since_last_purchase >= avg_days_between_orders 
                    THEN 1 
                    ELSE 2 
                END,
                purchase_count DESC,
                days_since_last_purchase DESC
            LIMIT 8
        ");
        
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $suggestions = [];
        while ($row = $result->fetch_assoc()) {
            $reorder_urgency = 'medium';
            if ($row['avg_days_between_orders'] && $row['days_since_last_purchase'] >= $row['avg_days_between_orders']) {
                $reorder_urgency = 'high';
            } elseif ($row['days_since_last_purchase'] >= 30) {
                $reorder_urgency = 'high';
            } elseif ($row['days_since_last_purchase'] >= 14) {
                $reorder_urgency = 'medium';
            } else {
                $reorder_urgency = 'low';
            }
            
            $suggestions[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'price' => $row['price'],
                'unit' => $row['unit'],
                'image_path' => $row['image_path'],
                'stock' => $row['stock'],
                'category' => $row['category'],
                'purchase_count' => $row['purchase_count'],
                'suggested_quantity' => round($row['avg_quantity']),
                'last_purchased' => date('M j, Y', strtotime($row['last_purchased'])),
                'days_since_last_purchase' => $row['days_since_last_purchase'],
                'reorder_urgency' => $reorder_urgency,
                'availability' => $row['stock'] > 0 ? 'available' : 'out_of_stock'
            ];
        }
        
        echo json_encode([
            'success' => true,
            'suggestions' => $suggestions,
            'count' => count($suggestions),
            'title' => 'Smart Reorder Suggestions'
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}

$conn->close();
?>
