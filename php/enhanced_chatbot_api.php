<?php
// Enhanced Chatbot API with advanced features
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    require_once '../includes/db_connect.php';
    
    $action = $_GET['action'] ?? $_POST['action'] ?? '';
    $response = ['success' => false, 'data' => null, 'message' => ''];
    
    switch($action) {
        case 'track_order':
            $response = handleAdvancedOrderTracking($conn);
            break;
            
        case 'cancel_order':
            $response = handleSmartOrderCancellation($conn);
            break;
            
        case 'get_bulk_pricing':
            $response = getAdvancedBulkPricing($conn);
            break;
            
        case 'get_recommendations':
            $response = getAIRecommendations($conn);
            break;
            
        case 'check_delivery_status':
            $response = getRealTimeDeliveryStatus($conn);
            break;
            
        case 'connect_agent':
            $response = initiateAgentConnection($conn);
            break;
            
        case 'analyze_sentiment':
            $response = analyzeSentiment();
            break;
            
        case 'get_notifications':
            $response = getUserNotifications($conn);
            break;
            
        case 'save_chat_session':
            $response = saveChatSession($conn);
            break;
            
        case 'get_chat_history':
            $response = getChatHistory($conn);
            break;
            
        default:
            $response = ['success' => false, 'error' => 'Invalid action', 'message' => 'Action not recognized'];
    }
    
} catch (Exception $e) {
    $response = ['success' => false, 'error' => 'Server error', 'message' => $e->getMessage()];
}

echo json_encode($response);

function handleAdvancedOrderTracking($conn) {
    $orderId = $_GET['order_id'] ?? $_POST['order_id'] ?? '';
    $userId = $_SESSION['user_id'] ?? null;
    
    if (empty($orderId)) {
        return ['success' => false, 'error' => 'Order ID required', 'message' => 'Please provide a valid order ID'];
    }
    
    try {
        // Get order details with advanced tracking
        $stmt = $conn->prepare("
            SELECT o.*, 
                   u.name as customer_name, 
                   u.phone as customer_phone,
                   TIMESTAMPDIFF(MINUTE, o.created_at, NOW()) as minutes_since_order
            FROM orders o 
            LEFT JOIN users u ON o.user_id = u.id 
            WHERE o.id = ? OR o.order_number = ?
        ");
        $stmt->bind_param("ss", $orderId, $orderId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return ['success' => false, 'error' => 'Order not found', 'message' => 'No order found with the provided ID'];
        }
        
        $order = $result->fetch_assoc();
        
        // Generate realistic tracking stages based on order time and status
        $stages = generateTrackingStages($order);
        
        // Get estimated delivery time with AI prediction
        $estimatedDelivery = calculateEstimatedDelivery($order);
        
        // Get real-time GPS location (simulated)
        $gpsLocation = getSimulatedGPSLocation($order);
        
        $trackingData = [
            'order_id' => $orderId,
            'order_number' => $order['order_number'] ?? $orderId,
            'customer_name' => $order['customer_name'],
            'status' => $order['status'],
            'current_stage' => getCurrentStage($stages),
            'stages' => $stages,
            'estimated_delivery' => $estimatedDelivery,
            'actual_delivery' => $order['status'] === 'Delivered' ? $order['updated_at'] : null,
            'delivery_contact' => '+880 1776-199963',
            'gps_location' => $gpsLocation,
            'progress_percentage' => calculateProgressPercentage($stages),
            'can_cancel' => $order['minutes_since_order'] <= 120 && $order['status'] !== 'Delivered',
            'delivery_instructions' => 'Please call before delivery',
            'tracking_url' => "https://agrokart-bd.com/track/{$orderId}"
        ];
        
        return ['success' => true, 'data' => $trackingData, 'message' => 'Order tracking retrieved successfully'];
        
    } catch (Exception $e) {
        return ['success' => false, 'error' => 'Database error', 'message' => 'Unable to fetch order details'];
    }
}

function generateTrackingStages($order) {
    $orderTime = strtotime($order['created_at']);
    $currentTime = time();
    $status = $order['status'];
    $minutesSinceOrder = ($currentTime - $orderTime) / 60;
    
    $stages = [
        [
            'name' => 'Order Received',
            'description' => 'Your order has been received and is being processed',
            'status' => 'completed',
            'time' => date('Y-m-d H:i:s', $orderTime),
            'icon' => 'fas fa-shopping-cart',
            'estimated_duration' => '0 minutes'
        ],
        [
            'name' => 'Payment Confirmed',
            'description' => 'Payment has been verified and confirmed',
            'status' => 'completed',
            'time' => date('Y-m-d H:i:s', $orderTime + (5 * 60)),
            'icon' => 'fas fa-credit-card',
            'estimated_duration' => '5 minutes'
        ],
        [
            'name' => 'Preparing Order',
            'description' => 'Our team is carefully preparing your fresh products',
            'status' => $minutesSinceOrder >= 15 ? 'completed' : ($minutesSinceOrder >= 5 ? 'current' : 'pending'),
            'time' => $minutesSinceOrder >= 15 ? date('Y-m-d H:i:s', $orderTime + (15 * 60)) : null,
            'icon' => 'fas fa-box-open',
            'estimated_duration' => '15-30 minutes'
        ],
        [
            'name' => 'Quality Check',
            'description' => 'Final quality inspection before packaging',
            'status' => $minutesSinceOrder >= 45 ? 'completed' : ($minutesSinceOrder >= 30 ? 'current' : 'pending'),
            'time' => $minutesSinceOrder >= 45 ? date('Y-m-d H:i:s', $orderTime + (45 * 60)) : null,
            'icon' => 'fas fa-check-circle',
            'estimated_duration' => '10 minutes'
        ],
        [
            'name' => 'Out for Delivery',
            'description' => 'Your order is on the way to your location',
            'status' => $minutesSinceOrder >= 60 ? 'completed' : ($minutesSinceOrder >= 45 ? 'current' : 'pending'),
            'time' => $minutesSinceOrder >= 60 ? date('Y-m-d H:i:s', $orderTime + (60 * 60)) : null,
            'icon' => 'fas fa-truck',
            'estimated_duration' => '30-60 minutes'
        ],
        [
            'name' => 'Delivered',
            'description' => 'Order successfully delivered to your address',
            'status' => $status === 'Delivered' ? 'completed' : ($minutesSinceOrder >= 120 ? 'current' : 'pending'),
            'time' => $status === 'Delivered' ? $order['updated_at'] : null,
            'icon' => 'fas fa-home',
            'estimated_duration' => 'Complete'
        ]
    ];
    
    return $stages;
}

function calculateEstimatedDelivery($order) {
    $orderTime = strtotime($order['created_at']);
    $baseDeliveryTime = 2 * 3600; // 2 hours base
    
    // Adjust based on time of day
    $orderHour = (int)date('H', $orderTime);
    if ($orderHour >= 22 || $orderHour <= 6) {
        $baseDeliveryTime += 6 * 3600; // Add 6 hours for night orders
    } elseif ($orderHour >= 12 && $orderHour <= 14) {
        $baseDeliveryTime += 1 * 3600; // Add 1 hour for lunch time
    }
    
    // Adjust based on order value (premium handling for large orders)
    if ($order['total_amount'] > 2000) {
        $baseDeliveryTime -= 30 * 60; // Prioritize large orders
    }
    
    return date('Y-m-d H:i:s', $orderTime + $baseDeliveryTime);
}

function getSimulatedGPSLocation($order) {
    // Simulate GPS tracking (in real app, this would come from delivery partner API)
    $stages = ['warehouse', 'transit', 'nearby', 'delivered'];
    $minutesSinceOrder = (time() - strtotime($order['created_at'])) / 60;
    
    if ($minutesSinceOrder < 45) {
        return [
            'stage' => 'warehouse',
            'lat' => 23.8103,
            'lng' => 90.4125,
            'address' => 'AgroKart Warehouse, Dhaka',
            'last_updated' => date('Y-m-d H:i:s')
        ];
    } elseif ($minutesSinceOrder < 90) {
        return [
            'stage' => 'transit',
            'lat' => 23.7904,
            'lng' => 90.4037,
            'address' => 'Gulshan Avenue, Dhaka',
            'last_updated' => date('Y-m-d H:i:s')
        ];
    } else {
        return [
            'stage' => 'nearby',
            'lat' => 23.7808,
            'lng' => 90.4106,
            'address' => 'Near your delivery location',
            'last_updated' => date('Y-m-d H:i:s')
        ];
    }
}

function getCurrentStage($stages) {
    foreach ($stages as $index => $stage) {
        if ($stage['status'] === 'current') {
            return $index;
        }
    }
    return count($stages) - 1;
}

function calculateProgressPercentage($stages) {
    $completed = 0;
    foreach ($stages as $stage) {
        if ($stage['status'] === 'completed') {
            $completed++;
        }
    }
    return round(($completed / count($stages)) * 100);
}

function handleSmartOrderCancellation($conn) {
    $orderId = $_GET['order_id'] ?? $_POST['order_id'] ?? '';
    $confirm = $_GET['confirm'] ?? $_POST['confirm'] ?? false;
    $reason = $_POST['reason'] ?? 'Customer request';
    
    if (empty($orderId)) {
        return ['success' => false, 'error' => 'Order ID required'];
    }
    
    try {
        $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? OR order_number = ?");
        $stmt->bind_param("ss", $orderId, $orderId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return ['success' => false, 'error' => 'Order not found'];
        }
        
        $order = $result->fetch_assoc();
        $orderTime = strtotime($order['created_at']);
        $currentTime = time();
        $minutesSinceOrder = ($currentTime - $orderTime) / 60;
        
        // Smart cancellation logic
        if ($order['status'] === 'Delivered') {
            return ['success' => false, 'error' => 'Cannot cancel delivered order', 'message' => 'This order has already been delivered'];
        }
        
        if ($order['status'] === 'Cancelled') {
            return ['success' => false, 'error' => 'Order already cancelled'];
        }
        
        // Calculate refund percentage based on time and progress
        $refundPercentage = 100;
        $processingFee = 0;
        
        if ($minutesSinceOrder > 30 && $minutesSinceOrder <= 120) {
            $refundPercentage = 90; // 10% processing fee
            $processingFee = $order['total_amount'] * 0.1;
        } elseif ($minutesSinceOrder > 120 && $minutesSinceOrder <= 360) {
            $refundPercentage = 75; // 25% processing fee
            $processingFee = $order['total_amount'] * 0.25;
        } elseif ($minutesSinceOrder > 360) {
            return ['success' => false, 'error' => 'Cannot cancel order', 'message' => 'Order cannot be cancelled after 6 hours'];
        }
        
        if ($confirm === 'true' || $confirm === true) {
            // Actually cancel the order
            $updateStmt = $conn->prepare("
                UPDATE orders 
                SET status = 'Cancelled', 
                    cancellation_reason = ?, 
                    cancelled_at = NOW(),
                    refund_amount = ?
                WHERE id = ?
            ");
            
            $refundAmount = $order['total_amount'] * ($refundPercentage / 100);
            $updateStmt->bind_param("sdi", $reason, $refundAmount, $order['id']);
            
            if ($updateStmt->execute()) {
                // Create refund record
                $refundStmt = $conn->prepare("
                    INSERT INTO refunds (order_id, amount, status, processing_fee, created_at) 
                    VALUES (?, ?, 'Pending', ?, NOW())
                ");
                $refundStmt->bind_param("idd", $order['id'], $refundAmount, $processingFee);
                $refundStmt->execute();
                
                return [
                    'success' => true,
                    'data' => [
                        'cancelled' => true,
                        'order_id' => $orderId,
                        'refund_amount' => $refundAmount,
                        'processing_fee' => $processingFee,
                        'refund_percentage' => $refundPercentage,
                        'refund_timeline' => '3-5 business days',
                        'refund_method' => 'Original payment method',
                        'cancellation_id' => 'CAN' . time()
                    ],
                    'message' => 'Order cancelled successfully. Refund will be processed within 3-5 business days.'
                ];
            }
        } else {
            // Return cancellation preview
            return [
                'success' => true,
                'data' => [
                    'can_cancel' => true,
                    'refund_percentage' => $refundPercentage,
                    'refund_amount' => $order['total_amount'] * ($refundPercentage / 100),
                    'processing_fee' => $processingFee,
                    'order_total' => $order['total_amount'],
                    'minutes_since_order' => round($minutesSinceOrder, 1),
                    'cancellation_policy' => getCancellationPolicy($minutesSinceOrder)
                ],
                'message' => 'Cancellation preview generated'
            ];
        }
        
    } catch (Exception $e) {
        return ['success' => false, 'error' => 'Database error', 'message' => $e->getMessage()];
    }
}

function getCancellationPolicy($minutes) {
    if ($minutes <= 30) {
        return 'Free cancellation with full refund';
    } elseif ($minutes <= 120) {
        return '10% processing fee applied';
    } elseif ($minutes <= 360) {
        return '25% processing fee applied';
    } else {
        return 'Cancellation not allowed';
    }
}

function getAdvancedBulkPricing($conn) {
    $productId = $_GET['product_id'] ?? $_POST['product_id'] ?? '';
    $quantity = (int)($_GET['quantity'] ?? $_POST['quantity'] ?? 1);
    $userId = $_SESSION['user_id'] ?? null;
    
    if (empty($productId)) {
        return ['success' => false, 'error' => 'Product ID required'];
    }
    
    try {
        $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return ['success' => false, 'error' => 'Product not found'];
        }
        
        $product = $result->fetch_assoc();
        $originalPrice = $product['price'];
        
        // Advanced discount calculation
        $discountTiers = [
            ['min_qty' => 100, 'discount' => 20, 'title' => 'Wholesale Plus'],
            ['min_qty' => 50, 'discount' => 15, 'title' => 'Bulk Premium'],
            ['min_qty' => 25, 'discount' => 10, 'title' => 'Bulk Standard'],
            ['min_qty' => 10, 'discount' => 5, 'title' => 'Small Bulk']
        ];
        
        $appliedDiscount = 0;
        $discountTitle = 'Regular Price';
        
        foreach ($discountTiers as $tier) {
            if ($quantity >= $tier['min_qty']) {
                $appliedDiscount = $tier['discount'];
                $discountTitle = $tier['title'];
                break;
            }
        }
        
        // Customer loyalty bonus
        $loyaltyBonus = 0;
        if ($userId) {
            $loyaltyStmt = $conn->prepare("
                SELECT COUNT(*) as order_count, SUM(total_amount) as total_spent 
                FROM orders 
                WHERE user_id = ? AND status = 'Delivered'
            ");
            $loyaltyStmt->bind_param("i", $userId);
            $loyaltyStmt->execute();
            $loyalty = $loyaltyStmt->get_result()->fetch_assoc();
            
            if ($loyalty['order_count'] >= 10) {
                $loyaltyBonus = 3; // 3% loyalty bonus
            } elseif ($loyalty['order_count'] >= 5) {
                $loyaltyBonus = 2; // 2% loyalty bonus
            }
        }
        
        $totalDiscount = $appliedDiscount + $loyaltyBonus;
        $discountedPrice = $originalPrice * (1 - $totalDiscount / 100);
        $totalSavings = ($originalPrice - $discountedPrice) * $quantity;
        $totalCost = $discountedPrice * $quantity;
        
        // Calculate shipping
        $shippingCost = $totalCost >= 500 ? 0 : 50;
        $finalTotal = $totalCost + $shippingCost;
        
        return [
            'success' => true,
            'data' => [
                'product_name' => $product['name'],
                'original_price' => $originalPrice,
                'discount_percentage' => $totalDiscount,
                'bulk_discount' => $appliedDiscount,
                'loyalty_bonus' => $loyaltyBonus,
                'discount_title' => $discountTitle,
                'discounted_price' => round($discountedPrice, 2),
                'quantity' => $quantity,
                'subtotal' => round($totalCost, 2),
                'total_savings' => round($totalSavings, 2),
                'shipping_cost' => $shippingCost,
                'final_total' => round($finalTotal, 2),
                'next_tier' => getNextDiscountTier($quantity, $discountTiers),
                'bulk_benefits' => [
                    'Free shipping on orders over ৳500',
                    'Priority customer support',
                    'Flexible payment terms',
                    'Quality guarantee'
                ]
            ],
            'message' => 'Bulk pricing calculated successfully'
        ];
        
    } catch (Exception $e) {
        return ['success' => false, 'error' => 'Database error', 'message' => $e->getMessage()];
    }
}

function getNextDiscountTier($currentQty, $tiers) {
    foreach ($tiers as $tier) {
        if ($currentQty < $tier['min_qty']) {
            return [
                'quantity' => $tier['min_qty'],
                'discount' => $tier['discount'],
                'title' => $tier['title'],
                'additional_qty_needed' => $tier['min_qty'] - $currentQty
            ];
        }
    }
    return null;
}

function getAIRecommendations($conn) {
    $userId = $_SESSION['user_id'] ?? null;
    $category = $_GET['category'] ?? '';
    $budget = (float)($_GET['budget'] ?? 0);
    
    try {
        // Get user's purchase history for personalized recommendations
        $recommendations = [];
        
        if ($userId) {
            // Get frequently bought products
            $historyStmt = $conn->prepare("
                SELECT p.*, COUNT(oi.product_id) as purchase_count
                FROM order_items oi
                JOIN orders o ON oi.order_id = o.id
                JOIN products p ON oi.product_id = p.id
                WHERE o.user_id = ? AND o.status = 'Delivered'
                GROUP BY p.id
                ORDER BY purchase_count DESC
                LIMIT 5
            ");
            $historyStmt->bind_param("i", $userId);
            $historyStmt->execute();
            $history = $historyStmt->get_result();
            
            while ($product = $history->fetch_assoc()) {
                $recommendations[] = [
                    'type' => 'frequently_bought',
                    'product' => $product,
                    'reason' => 'You buy this often',
                    'confidence' => 0.9
                ];
            }
        }
        
        // Get trending products
        $trendingStmt = $conn->prepare("
            SELECT p.*, COUNT(oi.product_id) as order_count
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            JOIN orders o ON oi.order_id = o.id
            WHERE o.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY p.id
            ORDER BY order_count DESC
            LIMIT 5
        ");
        $trendingStmt->execute();
        $trending = $trendingStmt->get_result();
        
        while ($product = $trending->fetch_assoc()) {
            $recommendations[] = [
                'type' => 'trending',
                'product' => $product,
                'reason' => 'Popular this week',
                'confidence' => 0.7
            ];
        }
        
        // Get seasonal recommendations
        $season = getCurrentSeason();
        $seasonalStmt = $conn->prepare("
            SELECT * FROM products 
            WHERE category LIKE ? OR name LIKE ?
            ORDER BY RAND()
            LIMIT 3
        ");
        $seasonKeyword = "%{$season}%";
        $seasonalStmt->bind_param("ss", $seasonKeyword, $seasonKeyword);
        $seasonalStmt->execute();
        $seasonal = $seasonalStmt->get_result();
        
        while ($product = $seasonal->fetch_assoc()) {
            $recommendations[] = [
                'type' => 'seasonal',
                'product' => $product,
                'reason' => "Perfect for {$season}",
                'confidence' => 0.6
            ];
        }
        
        return [
            'success' => true,
            'data' => [
                'recommendations' => array_slice($recommendations, 0, 10),
                'total_count' => count($recommendations),
                'personalization_score' => $userId ? 0.8 : 0.3,
                'recommendation_reasons' => [
                    'Based on your purchase history',
                    'Currently trending products',
                    'Seasonal recommendations',
                    'Price-based suggestions'
                ]
            ],
            'message' => 'AI recommendations generated successfully'
        ];
        
    } catch (Exception $e) {
        return ['success' => false, 'error' => 'Database error', 'message' => $e->getMessage()];
    }
}

function getCurrentSeason() {
    $month = (int)date('n');
    if ($month >= 3 && $month <= 5) return 'spring';
    if ($month >= 6 && $month <= 8) return 'summer';
    if ($month >= 9 && $month <= 11) return 'autumn';
    return 'winter';
}

function initiateAgentConnection($conn) {
    $userId = $_SESSION['user_id'] ?? null;
    $sessionId = $_POST['session_id'] ?? '';
    $issue = $_POST['issue'] ?? 'general';
    
    try {
        // Check if agents are available
        $availableAgents = getAvailableAgents($conn);
        
        if (empty($availableAgents)) {
            return [
                'success' => false,
                'data' => [
                    'queue_position' => rand(1, 5),
                    'estimated_wait' => '5-10 minutes',
                    'callback_available' => true
                ],
                'message' => 'All agents are currently busy. You can wait in queue or request a callback.'
            ];
        }
        
        // Assign agent
        $agent = $availableAgents[0];
        $agentStmt = $conn->prepare("
            INSERT INTO agent_sessions (user_id, agent_id, session_id, issue_type, status, created_at)
            VALUES (?, ?, ?, ?, 'active', NOW())
        ");
        $agentStmt->bind_param("iiss", $userId, $agent['id'], $sessionId, $issue);
        $agentStmt->execute();
        
        return [
            'success' => true,
            'data' => [
                'agent_connected' => true,
                'agent_name' => $agent['name'],
                'agent_id' => $agent['id'],
                'specialization' => $agent['specialization'],
                'average_rating' => $agent['rating'],
                'session_id' => $sessionId,
                'connection_time' => date('Y-m-d H:i:s')
            ],
            'message' => 'Successfully connected to live agent'
        ];
        
    } catch (Exception $e) {
        return ['success' => false, 'error' => 'Connection error', 'message' => $e->getMessage()];
    }
}

function getAvailableAgents($conn) {
    // This would typically check a real agent management system
    // For demo purposes, returning mock data
    return [
        [
            'id' => 1,
            'name' => 'Md. Rahim',
            'specialization' => 'Order Support',
            'rating' => 4.8,
            'status' => 'available'
        ],
        [
            'id' => 2,
            'name' => 'Fatima Khatun',
            'specialization' => 'Product Expert',
            'rating' => 4.9,
            'status' => 'available'
        ]
    ];
}

function analyzeSentiment() {
    $text = $_POST['text'] ?? '';
    
    if (empty($text)) {
        return ['success' => false, 'error' => 'Text required for sentiment analysis'];
    }
    
    // Simple sentiment analysis (in production, use ML APIs like Google Cloud Natural Language)
    $positiveWords = ['good', 'great', 'excellent', 'amazing', 'love', 'happy', 'satisfied', 'wonderful', 'fantastic'];
    $negativeWords = ['bad', 'terrible', 'awful', 'hate', 'angry', 'frustrated', 'disappointed', 'horrible'];
    
    $text = strtolower($text);
    $positiveCount = 0;
    $negativeCount = 0;
    
    foreach ($positiveWords as $word) {
        $positiveCount += substr_count($text, $word);
    }
    
    foreach ($negativeWords as $word) {
        $negativeCount += substr_count($text, $word);
    }
    
    $sentiment = 'neutral';
    $confidence = 0.5;
    
    if ($positiveCount > $negativeCount) {
        $sentiment = 'positive';
        $confidence = min(0.9, 0.5 + ($positiveCount * 0.1));
    } elseif ($negativeCount > $positiveCount) {
        $sentiment = 'negative';
        $confidence = min(0.9, 0.5 + ($negativeCount * 0.1));
    }
    
    return [
        'success' => true,
        'data' => [
            'sentiment' => $sentiment,
            'confidence' => $confidence,
            'positive_words' => $positiveCount,
            'negative_words' => $negativeCount,
            'suggestions' => getSentimentBasedSuggestions($sentiment)
        ],
        'message' => 'Sentiment analysis completed'
    ];
}

function getSentimentBasedSuggestions($sentiment) {
    switch ($sentiment) {
        case 'positive':
            return [
                'Ask for product review',
                'Offer loyalty program',
                'Suggest related products'
            ];
        case 'negative':
            return [
                'Offer agent assistance',
                'Provide discount code',
                'Follow up with customer service'
            ];
        default:
            return [
                'Continue normal conversation',
                'Offer help if needed'
            ];
    }
}

function saveChatSession($conn) {
    $sessionId = $_POST['session_id'] ?? '';
    $userId = $_SESSION['user_id'] ?? null;
    $messages = json_decode($_POST['messages'] ?? '[]', true);
    $satisfaction = $_POST['satisfaction'] ?? null;
    
    if (empty($sessionId)) {
        return ['success' => false, 'error' => 'Session ID required'];
    }
    
    try {
        $stmt = $conn->prepare("
            INSERT INTO chat_sessions (session_id, user_id, messages, satisfaction_rating, created_at, updated_at)
            VALUES (?, ?, ?, ?, NOW(), NOW())
            ON DUPLICATE KEY UPDATE
            messages = VALUES(messages),
            satisfaction_rating = VALUES(satisfaction_rating),
            updated_at = NOW()
        ");
        
        $messagesJson = json_encode($messages);
        $stmt->bind_param("sisi", $sessionId, $userId, $messagesJson, $satisfaction);
        
        if ($stmt->execute()) {
            return [
                'success' => true,
                'data' => ['session_saved' => true],
                'message' => 'Chat session saved successfully'
            ];
        }
        
    } catch (Exception $e) {
        return ['success' => false, 'error' => 'Save error', 'message' => $e->getMessage()];
    }
}

function getRealTimeDeliveryStatus($conn) {
    $orderId = $_GET['order_id'] ?? '';
    
    if (empty($orderId)) {
        return ['success' => false, 'error' => 'Order ID required'];
    }
    
    try {
        // Get current delivery status with real-time updates
        $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? OR order_number = ?");
        $stmt->bind_param("ss", $orderId, $orderId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return ['success' => false, 'error' => 'Order not found'];
        }
        
        $order = $result->fetch_assoc();
        
        // Simulate real-time delivery tracking
        $deliveryStatus = [
            'order_id' => $orderId,
            'current_location' => getSimulatedGPSLocation($order),
            'estimated_arrival' => calculateEstimatedDelivery($order),
            'delivery_partner' => [
                'name' => 'Md. Karim',
                'phone' => '+880 1776-199963',
                'vehicle' => 'Motorcycle',
                'rating' => 4.8
            ],
            'live_updates' => [
                [
                    'time' => date('H:i'),
                    'status' => 'Package picked up from warehouse',
                    'location' => 'AgroKart Warehouse'
                ],
                [
                    'time' => date('H:i', strtotime('+30 minutes')),
                    'status' => 'En route to delivery address',
                    'location' => 'Gulshan Avenue'
                ]
            ],
            'delivery_window' => date('H:i', strtotime('+1 hour')) . ' - ' . date('H:i', strtotime('+2 hours')),
            'special_instructions' => 'Call before delivery',
            'can_reschedule' => true
        ];
        
        return [
            'success' => true,
            'data' => $deliveryStatus,
            'message' => 'Real-time delivery status retrieved'
        ];
        
    } catch (Exception $e) {
        return ['success' => false, 'error' => 'Database error', 'message' => $e->getMessage()];
    }
}

function getUserNotifications($conn) {
    $userId = $_SESSION['user_id'] ?? null;
    $type = $_GET['type'] ?? 'all'; // all, orders, promotions, system
    
    try {
        // Get user notifications
        $notifications = [];
        
        if ($userId) {
            // Order-related notifications
            $orderNotifs = $conn->prepare("
                SELECT CONCAT('Order #', o.order_number, ' is ', o.status) as message,
                       'order' as type,
                       o.updated_at as created_at,
                       o.id as related_id
                FROM orders o
                WHERE o.user_id = ? AND o.updated_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                ORDER BY o.updated_at DESC
                LIMIT 5
            ");
            $orderNotifs->bind_param("i", $userId);
            $orderNotifs->execute();
            $result = $orderNotifs->get_result();
            
            while ($notif = $result->fetch_assoc()) {
                $notifications[] = $notif;
            }
        }
        
        // System notifications (promotions, updates, etc.)
        $systemNotifs = [
            [
                'message' => '🎉 New Year Sale: Up to 30% off on all vegetables!',
                'type' => 'promotion',
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
                'related_id' => null
            ],
            [
                'message' => '📱 New feature: Voice ordering now available in Bengali!',
                'type' => 'system',
                'created_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
                'related_id' => null
            ],
            [
                'message' => '🚚 Free delivery extended to all areas in Dhaka!',
                'type' => 'system',
                'created_at' => date('Y-m-d H:i:s', strtotime('-3 days')),
                'related_id' => null
            ]
        ];
        
        $notifications = array_merge($notifications, $systemNotifs);
        
        // Filter by type if specified
        if ($type !== 'all') {
            $notifications = array_filter($notifications, function($notif) use ($type) {
                return $notif['type'] === $type;
            });
        }
        
        // Sort by date
        usort($notifications, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        return [
            'success' => true,
            'data' => [
                'notifications' => array_slice($notifications, 0, 10),
                'unread_count' => count(array_filter($notifications, function($n) {
                    return strtotime($n['created_at']) > strtotime('-1 hour');
                })),
                'total_count' => count($notifications)
            ],
            'message' => 'Notifications retrieved successfully'
        ];
        
    } catch (Exception $e) {
        return ['success' => false, 'error' => 'Database error', 'message' => $e->getMessage()];
    }
}

function getChatHistory($conn) {
    $userId = $_SESSION['user_id'] ?? null;
    $limit = (int)($_GET['limit'] ?? 10);
    
    if (!$userId) {
        return ['success' => false, 'error' => 'User not logged in'];
    }
    
    try {
        $stmt = $conn->prepare("
            SELECT session_id, messages, satisfaction_rating, created_at
            FROM chat_sessions
            WHERE user_id = ?
            ORDER BY created_at DESC
            LIMIT ?
        ");
        $stmt->bind_param("ii", $userId, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $history = [];
        while ($row = $result->fetch_assoc()) {
            $row['messages'] = json_decode($row['messages'], true);
            $history[] = $row;
        }
        
        return [
            'success' => true,
            'data' => [
                'history' => $history,
                'total_sessions' => count($history)
            ],
            'message' => 'Chat history retrieved successfully'
        ];
        
    } catch (Exception $e) {
        return ['success' => false, 'error' => 'Database error', 'message' => $e->getMessage()];
    }
}
?>
