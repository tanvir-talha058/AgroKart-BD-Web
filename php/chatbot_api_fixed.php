<?php
// Enhanced chatbot API for order management with intelligent product detection
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
            
        case 'smart_chat':
            $response = handleSmartChat($conn);
            break;
            
        case 'search_products':
            $response = searchProductsInChat($conn);
            break;
            
        default:
            $response = ['success' => false, 'error' => 'Invalid action'];
    }
    
    echo json_encode($response);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Server error']);
}

function handleOrderTracking($conn) {
    $order_id = $_GET['order_id'] ?? $_POST['order_id'] ?? '';
    
    if (empty($order_id)) {
        return ['success' => false, 'error' => 'Order ID required'];
    }
    
    try {
        $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? OR CONCAT('AGR', id) = ?");
        $stmt->bind_param("ss", $order_id, $order_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($order = $result->fetch_assoc()) {
            return [
                'success' => true,
                'data' => [
                    'order_id' => 'AGR' . $order['id'],
                    'status' => $order['status'],
                    'total_amount' => $order['total_amount'],
                    'order_date' => $order['created_at'],
                    'shipping_address' => $order['shipping_address'],
                    'payment_method' => $order['payment_method'],
                    'estimated_delivery' => date('Y-m-d', strtotime($order['created_at'] . ' +3 days'))
                ]
            ];
        } else {
            return ['success' => false, 'error' => 'Order not found'];
        }
    } catch (Exception $e) {
        return ['success' => false, 'error' => 'Database error'];
    }
}

function handleOrderCancellation($conn) {
    $order_id = $_GET['order_id'] ?? $_POST['order_id'] ?? '';
    
    if (empty($order_id)) {
        return ['success' => false, 'error' => 'Order ID required'];
    }
    
    try {
        // Check if order exists and can be cancelled
        $stmt = $conn->prepare("SELECT status FROM orders WHERE id = ? OR CONCAT('AGR', id) = ?");
        $stmt->bind_param("ss", $order_id, $order_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($order = $result->fetch_assoc()) {
            if (in_array($order['status'], ['Pending', 'Processing'])) {
                // Update order status to cancelled
                $stmt = $conn->prepare("UPDATE orders SET status = 'Cancelled' WHERE id = ? OR CONCAT('AGR', id) = ?");
                $stmt->bind_param("ss", $order_id, $order_id);
                $stmt->execute();
                
                return [
                    'success' => true,
                    'data' => [
                        'message' => 'Order cancelled successfully',
                        'order_id' => $order_id,
                        'refund_status' => 'Refund will be processed within 5-7 business days'
                    ]
                ];
            } else {
                return ['success' => false, 'error' => 'Order cannot be cancelled at this stage'];
            }
        } else {
            return ['success' => false, 'error' => 'Order not found'];
        }
    } catch (Exception $e) {
        return ['success' => false, 'error' => 'Database error'];
    }
}

function getBulkPricing($conn) {
    $product_id = $_GET['product_id'] ?? $_POST['product_id'] ?? '';
    $quantity = $_GET['quantity'] ?? $_POST['quantity'] ?? 1;
    
    if (empty($product_id)) {
        return ['success' => false, 'error' => 'Product ID required'];
    }
    
    try {
        $stmt = $conn->prepare("SELECT price, name, unit FROM products WHERE id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($product = $result->fetch_assoc()) {
            $base_price = $product['price'];
            $discount = 0;
            
            // Apply bulk discounts
            if ($quantity >= 100) {
                $discount = 0.15; // 15% discount
            } elseif ($quantity >= 50) {
                $discount = 0.10; // 10% discount
            } elseif ($quantity >= 20) {
                $discount = 0.05; // 5% discount
            }
            
            $unit_price = $base_price * (1 - $discount);
            $total_price = $unit_price * $quantity;
            
            return [
                'success' => true,
                'data' => [
                    'product_name' => $product['name'],
                    'unit' => $product['unit'],
                    'base_price' => $base_price,
                    'discount_percentage' => $discount * 100,
                    'unit_price_after_discount' => $unit_price,
                    'quantity' => $quantity,
                    'total_price' => $total_price,
                    'savings' => ($base_price - $unit_price) * $quantity
                ]
            ];
        } else {
            return ['success' => false, 'error' => 'Product not found'];
        }
    } catch (Exception $e) {
        return ['success' => false, 'error' => 'Database error'];
    }
}

function getRecommendations($conn) {
    $category = $_GET['category'] ?? '';
    $limit = $_GET['limit'] ?? 5;
    
    try {
        if (!empty($category)) {
            $stmt = $conn->prepare("SELECT id, name, price, unit, image_path, stock FROM products WHERE category = ? AND stock > 0 ORDER BY RAND() LIMIT ?");
            $stmt->bind_param("si", $category, $limit);
        } else {
            $stmt = $conn->prepare("SELECT id, name, price, unit, image_path, stock FROM products WHERE stock > 0 ORDER BY RAND() LIMIT ?");
            $stmt->bind_param("i", $limit);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $recommendations = [];
        while ($row = $result->fetch_assoc()) {
            $recommendations[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'price' => $row['price'],
                'unit' => $row['unit'],
                'image_path' => $row['image_path'],
                'stock' => $row['stock'],
                'order_link' => 'product_details.php?id=' . $row['id']
            ];
        }
        
        return [
            'success' => true,
            'data' => [
                'recommendations' => $recommendations,
                'category' => $category ?: 'All Categories'
            ]
        ];
    } catch (Exception $e) {
        return ['success' => false, 'error' => 'Database error'];
    }
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

// Smart chat handler with product detection
function handleSmartChat($conn) {
    $message = $_POST['message'] ?? $_GET['message'] ?? '';
    $userId = $_SESSION['user_id'] ?? null;
    
    if (empty($message)) {
        return ['success' => false, 'error' => 'Message required'];
    }
    
    // Detect products mentioned in the message
    $detectedProducts = detectProductsInMessage($conn, $message);
    
    // Generate intelligent response
    $response = generateSmartResponse($conn, $message, $detectedProducts, $userId);
    
    return [
        'success' => true,
        'data' => [
            'response' => $response,
            'detected_products' => $detectedProducts,
            'message_type' => determineMessageType($message),
            'timestamp' => date('Y-m-d H:i:s')
        ]
    ];
}

// Search products mentioned in chat
function searchProductsInChat($conn) {
    $query = $_POST['query'] ?? $_GET['query'] ?? '';
    
    if (empty($query)) {
        return ['success' => false, 'error' => 'Search query required'];
    }
    
    try {
        // Search products with stock information
        $stmt = $conn->prepare("
            SELECT id, name, price, unit, image_path, stock, category, description
            FROM products 
            WHERE (name LIKE ? OR category LIKE ? OR description LIKE ?) 
            AND stock >= 0
            ORDER BY 
                CASE 
                    WHEN stock > 0 THEN 1 
                    ELSE 2 
                END,
                name ASC
            LIMIT 10
        ");
        
        $searchTerm = "%$query%";
        $stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = formatProductForChat($row);
        }
        
        return [
            'success' => true,
            'data' => [
                'products' => $products,
                'total_found' => count($products),
                'search_query' => $query
            ]
        ];
        
    } catch (Exception $e) {
        return ['success' => false, 'error' => 'Search failed'];
    }
}

// Detect products mentioned in user message
function detectProductsInMessage($conn, $message) {
    $detectedProducts = [];
    $message = strtolower($message);
    
    try {
        // Get all products for matching
        $stmt = $conn->prepare("
            SELECT id, name, price, unit, image_path, stock, category
            FROM products 
            ORDER BY LENGTH(name) DESC
        ");
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $productName = strtolower($row['name']);
            $category = strtolower($row['category']);
            
            // Check if product name or category is mentioned
            if (strpos($message, $productName) !== false || 
                strpos($message, $category) !== false ||
                checkProductKeywords($message, $productName)) {
                
                $detectedProducts[] = formatProductForChat($row);
            }
        }
        
        // Remove duplicates and limit to 5 products
        $detectedProducts = array_slice(array_unique($detectedProducts, SORT_REGULAR), 0, 5);
        
    } catch (Exception $e) {
        // Return empty array on error
    }
    
    return $detectedProducts;
}

// Format product data for chat response
function formatProductForChat($product) {
    $stockStatus = determineStockStatus($product['stock']);
    
    return [
        'id' => $product['id'],
        'name' => $product['name'],
        'price' => $product['price'],
        'unit' => $product['unit'],
        'image_path' => $product['image_path'],
        'stock' => $product['stock'],
        'category' => $product['category'],
        'stock_status' => $stockStatus['status'],
        'stock_message' => $stockStatus['message'],
        'stock_color' => $stockStatus['color'],
        'order_link' => 'product_details.php?id=' . $product['id'],
        'can_order' => $product['stock'] > 0,
        'price_formatted' => '৳' . number_format($product['price'], 2),
        'availability_text' => $product['stock'] > 0 ? 
            "✅ In Stock ({$product['stock']} available)" : 
            "❌ Out of Stock"
    ];
}

// Determine stock status with colors and messages
function determineStockStatus($stock) {
    if ($stock <= 0) {
        return [
            'status' => 'out_of_stock',
            'message' => 'Currently out of stock',
            'color' => '#e74c3c'
        ];
    } elseif ($stock <= 5) {
        return [
            'status' => 'low_stock',
            'message' => "Only {$stock} left in stock!",
            'color' => '#f39c12'
        ];
    } elseif ($stock <= 20) {
        return [
            'status' => 'limited_stock',
            'message' => "{$stock} items available",
            'color' => '#3498db'
        ];
    } else {
        return [
            'status' => 'in_stock',
            'message' => "In stock ({$stock} available)",
            'color' => '#27ae60'
        ];
    }
}

// Check for product keywords and variations
function checkProductKeywords($message, $productName) {
    $keywords = explode(' ', $productName);
    $matchCount = 0;
    
    foreach ($keywords as $keyword) {
        if (strlen($keyword) > 2 && strpos($message, $keyword) !== false) {
            $matchCount++;
        }
    }
    
    // Return true if at least half of the keywords match
    return $matchCount >= (count($keywords) / 2);
}

// Generate intelligent chat response
function generateSmartResponse($conn, $message, $detectedProducts, $userId) {
    $message = strtolower($message);
    $messageType = determineMessageType($message);
    
    // If products are detected, generate product-focused response
    if (!empty($detectedProducts)) {
        return generateProductResponse($detectedProducts, $messageType);
    }
    
    // Handle different message types
    switch ($messageType) {
        case 'greeting':
            return generateGreetingResponse($userId);
            
        case 'question_stock':
            return "I'd be happy to help you check stock! Please tell me which product you're interested in.";
            
        case 'question_price':
            return "I can help you with pricing information! Which product would you like to know about?";
            
        case 'question_delivery':
            return "For delivery information, I can help! Do you have an order number to track, or would you like to know about delivery options?";
            
        case 'complaint':
            return "I'm sorry to hear you're having an issue. I'd like to help resolve this for you. Can you provide more details about the problem?";
            
        case 'help':
            return generateHelpResponse();
            
        default:
            return "I'm here to help! You can ask me about:\n\n• Product availability and stock\n• Pricing information\n• Order tracking\n• Delivery status\n• Product recommendations\n\nWhat would you like to know?";
    }
}

// Generate product-specific response
function generateProductResponse($products, $messageType) {
    $response = "I found information about the following products:\n\n";
    
    foreach ($products as $product) {
        $response .= "🌾 **{$product['name']}**\n";
        $response .= "💰 Price: {$product['price_formatted']} per {$product['unit']}\n";
        $response .= "📦 {$product['availability_text']}\n";
        
        if ($product['can_order']) {
            $response .= "🛒 [Order Now]({$product['order_link']})\n";
            
            if ($product['stock_status'] === 'low_stock') {
                $response .= "⚠️ {$product['stock_message']}\n";
            }
        } else {
            $response .= "🔔 Would you like to be notified when this item is back in stock?\n";
        }
        
        $response .= "\n";
    }
    
    // Add contextual suggestions based on message type
    if ($messageType === 'question_stock') {
        $response .= "💡 **Tip**: I can also help you find similar products if any of these are out of stock!";
    } elseif ($messageType === 'question_price') {
        $response .= "💡 **Tip**: I can check for bulk pricing discounts if you're ordering large quantities!";
    }
    
    return $response;
}

// Determine message type for intelligent responses
function determineMessageType($message) {
    $greetings = ['hello', 'hi', 'hey', 'good morning', 'good afternoon', 'good evening'];
    $stockQuestions = ['stock', 'available', 'availability', 'quantity', 'how many'];
    $priceQuestions = ['price', 'cost', 'how much', 'rate', 'pricing'];
    $deliveryQuestions = ['delivery', 'shipping', 'when will', 'track', 'order status'];
    $complaints = ['problem', 'issue', 'complaint', 'wrong', 'bad', 'poor', 'terrible'];
    $helpRequests = ['help', 'assist', 'support', 'can you', 'how to'];
    
    $message = strtolower($message);
    
    if (containsAny($message, $greetings)) return 'greeting';
    if (containsAny($message, $stockQuestions)) return 'question_stock';
    if (containsAny($message, $priceQuestions)) return 'question_price';
    if (containsAny($message, $deliveryQuestions)) return 'question_delivery';
    if (containsAny($message, $complaints)) return 'complaint';
    if (containsAny($message, $helpRequests)) return 'help';
    
    return 'general';
}

// Check if message contains any of the given keywords
function containsAny($message, $keywords) {
    foreach ($keywords as $keyword) {
        if (strpos($message, $keyword) !== false) {
            return true;
        }
    }
    return false;
}

// Generate greeting response
function generateGreetingResponse($userId) {
    $greetings = [
        "Hello! 👋 Welcome to AgroKart BD! How can I help you find fresh products today?",
        "Hi there! 🌾 I'm here to help you with all your agricultural product needs!",
        "Good day! 🌱 Looking for fresh vegetables, fruits, or spices? I'm here to assist!",
        "Welcome to AgroKart BD! 🥬 I can help you check product availability, prices, and more!"
    ];
    
    return $greetings[array_rand($greetings)];
}

// Generate help response
function generateHelpResponse() {
    return "🤖 **I'm your AgroKart BD assistant!** Here's how I can help:\n\n" .
           "🔍 **Product Search**: Just mention any product name\n" .
           "📦 **Stock Check**: Ask about availability\n" .
           "💰 **Pricing**: Get current prices and bulk discounts\n" .
           "🚚 **Order Tracking**: Track your deliveries\n" .
           "🛒 **Quick Order**: I'll provide direct links to order\n" .
           "🔔 **Notifications**: Set alerts for out-of-stock items\n\n" .
           "Try saying something like:\n" .
           "• 'Do you have tomatoes?'\n" .
           "• 'What's the price of rice?'\n" .
           "• 'Track my order AGR12345'";
}

?>
