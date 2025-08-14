<?php
// Enhanced chatbot API for order management
header('Content-Type: application/json');

try {
    require_once '../includes/db_connect.php';
    
    $action = $_GET['action'] ?? $_POST['action'] ?? '';
    $response = ['success' => false, 'data' => null];
    
    switch($action) {
        case 'track_order':
            $response = handleOrderTracking($conn);
            break;
            
        case 'cancel_order':
            $response = handleOrderCancellation($conn);
            break;
            
        case 'get_bulk_pricing':
            $response = getBulkPricing($conn);
            break;
            
        case 'get_recommendations':
            $response = getRecommendations($conn);
            break;
            
        case 'check_delivery_status':
            $response = checkDeliveryStatus($conn);
            break;
            
        default:
            $response = ['success' => false, 'error' => 'Invalid action'];
    }
    
    echo json_encode($response);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Server error']);
}

function handleOrderTracking($conn) {
    $orderId = $_GET['order_id'] ?? $_POST['order_id'] ?? '';
    
    if (empty($orderId)) {
        return ['success' => false, 'error' => 'Order ID required'];
    }
    
    // In a real implementation, query your orders table
    $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? OR id = ?");
    $stmt->bind_param("ss", $orderId, $orderId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $order = $result->fetch_assoc();
        
        // Simulate tracking stages based on order creation time
        $trackingData = [
            'order_id' => $orderId,
            'status' => $order['status'],
            'stages' => [
                [
                    'name' => 'Order Confirmed',
                    'status' => 'completed',
                    'time' => date('Y-m-d H:i:s', strtotime($order['created_at']))
                ],
                [
                    'name' => 'Preparing Order',
                    'status' => 'completed',
                    'time' => date('Y-m-d H:i:s', strtotime($order['created_at'] . ' +30 minutes'))
                ],
                [
                    'name' => 'Out for Delivery',
                    'status' => $order['status'] === 'Delivered' ? 'completed' : 'current',
                    'time' => date('Y-m-d H:i:s', strtotime($order['created_at'] . ' +2 hours'))
                ],
                [
                    'name' => 'Delivered',
                    'status' => $order['status'] === 'Delivered' ? 'completed' : 'pending',
                    'time' => $order['status'] === 'Delivered' ? date('Y-m-d H:i:s') : null
                ]
            ],
            'estimated_delivery' => date('Y-m-d H:i:s', strtotime($order['created_at'] . ' +4 hours')),
            'delivery_contact' => '+880 1776-199963'
        ];
        
        return ['success' => true, 'data' => $trackingData];
    }
    
    return ['success' => false, 'error' => 'Order not found'];
}

function handleOrderCancellation($conn) {
    $orderId = $_GET['order_id'] ?? $_POST['order_id'] ?? '';
    $confirm = $_GET['confirm'] ?? $_POST['confirm'] ?? false;
    
    if (empty($orderId)) {
        return ['success' => false, 'error' => 'Order ID required'];
    }
    
    $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->bind_param("s", $orderId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $order = $result->fetch_assoc();
        $orderTime = strtotime($order['created_at']);
        $currentTime = time();
        $hoursSinceOrder = ($currentTime - $orderTime) / 3600;
        
        if ($confirm) {
            // Actually cancel the order
            $updateStmt = $conn->prepare("UPDATE orders SET status = 'Cancelled' WHERE id = ?");
            $updateStmt->bind_param("s", $orderId);
            
            if ($updateStmt->execute()) {
                $refundAmount = $order['total_amount'];
                if ($hoursSinceOrder > 2) {
                    $refundAmount *= 0.5; // 50% refund after 2 hours
                }
                
                return [
                    'success' => true,
                    'data' => [
                        'cancelled' => true,
                        'refund_amount' => $refundAmount,
                        'refund_days' => '3-5 business days',
                        'order_id' => $orderId
                    ]
                ];
            }
        } else {
            // Check cancellation eligibility
            $refundPercentage = 100;
            if ($hoursSinceOrder > 2 && $hoursSinceOrder <= 24) {
                $refundPercentage = 50;
            } elseif ($hoursSinceOrder > 24) {
                return ['success' => false, 'error' => 'Order cannot be cancelled after 24 hours'];
            }
            
            return [
                'success' => true,
                'data' => [
                    'can_cancel' => true,
                    'refund_percentage' => $refundPercentage,
                    'refund_amount' => $order['total_amount'] * ($refundPercentage / 100),
                    'hours_since_order' => round($hoursSinceOrder, 1),
                    'order_total' => $order['total_amount']
                ]
            ];
        }
    }
    
    return ['success' => false, 'error' => 'Order not found'];
}

function getBulkPricing($conn) {
    $productId = $_GET['product_id'] ?? $_POST['product_id'] ?? '';
    $quantity = (int)($_GET['quantity'] ?? $_POST['quantity'] ?? 1);
    
    if (empty($productId)) {
        return ['success' => false, 'error' => 'Product ID required'];
    }
    
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
        $originalPrice = $product['price'];
        
        // Calculate bulk discount
        $discount = 0;
        if ($quantity >= 50) $discount = 15;
        elseif ($quantity >= 25) $discount = 10;
        elseif ($quantity >= 10) $discount = 5;
        
        $discountedPrice = $originalPrice * (1 - $discount / 100);
        $totalSavings = ($originalPrice - $discountedPrice) * $quantity;
        
        return [
            'success' => true,
            'data' => [
                'product_name' => $product['name'],
                'original_price' => $originalPrice,
                'discount_percentage' => $discount,
                'discounted_price' => round($discountedPrice, 2),
                'quantity' => $quantity,
                'total_savings' => round($totalSavings, 2),
                'total_cost' => round($discountedPrice * $quantity, 2)
            ]
        ];
    }
    
    return ['success' => false, 'error' => 'Product not found'];
}

function getRecommendations($conn) {
    // Get popular products (high stock = popular)
    $popularQuery = "SELECT * FROM products WHERE stock > 20 ORDER BY stock DESC LIMIT 3";
    $popularResult = $conn->query($popularQuery);
    
    // Get recently added products
    $recentQuery = "SELECT * FROM products ORDER BY created_at DESC LIMIT 3";
    $recentResult = $conn->query($recentQuery);
    
    $recommendations = [
        'popular' => [],
        'recent' => [],
        'seasonal' => []
    ];
    
    if ($popularResult) {
        while ($row = $popularResult->fetch_assoc()) {
            $recommendations['popular'][] = $row;
        }
    }
    
    if ($recentResult) {
        while ($row = $recentResult->fetch_assoc()) {
            $recommendations['recent'][] = $row;
        }
    }
    
    return ['success' => true, 'data' => $recommendations];
}

function checkDeliveryStatus($conn) {
    $phone = $_GET['phone'] ?? $_POST['phone'] ?? '';
    
    if (empty($phone)) {
        return ['success' => false, 'error' => 'Phone number required'];
    }
    
    // In a real implementation, you'd query based on user phone/email
    // For demo, return mock delivery data
    return [
        'success' => true,
        'data' => [
            'active_deliveries' => 1,
            'next_delivery' => [
                'order_id' => 'AGR' . rand(10000, 99999),
                'estimated_time' => date('Y-m-d H:i:s', strtotime('+2 hours')),
                'status' => 'Out for delivery',
                'driver_contact' => '+880 1776-199963'
            ]
        ]
    ];
}
?>
