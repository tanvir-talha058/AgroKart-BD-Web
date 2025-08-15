<?php
// Image Recognition API for Chatbot
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    require_once '../includes/db_connect.php';
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Only POST method allowed');
    }
    
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== 0) {
        throw new Exception('No valid image uploaded');
    }
    
    $uploadedFile = $_FILES['image'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
    
    // Validate file
    if ($uploadedFile['size'] > $maxSize) {
        throw new Exception('Image size too large. Maximum 5MB allowed.');
    }
    
    if (!in_array($uploadedFile['type'], $allowedTypes)) {
        throw new Exception('Invalid image type. Only JPEG and PNG allowed.');
    }
    
    // Create unique filename
    $fileName = 'chatbot_' . time() . '_' . uniqid() . '.' . pathinfo($uploadedFile['name'], PATHINFO_EXTENSION);
    $uploadPath = '../uploads/chatbot/' . $fileName;
    
    // Create directory if it doesn't exist
    if (!is_dir('../uploads/chatbot/')) {
        mkdir('../uploads/chatbot/', 0755, true);
    }
    
    // Move uploaded file
    if (!move_uploaded_file($uploadedFile['tmp_name'], $uploadPath)) {
        throw new Exception('Failed to save uploaded image');
    }
    
    // Analyze image (simplified product recognition)
    $recognitionResult = analyzeProductImage($uploadPath, $conn);
    
    // Clean up uploaded file
    unlink($uploadPath);
    
    echo json_encode([
        'success' => true,
        'data' => $recognitionResult,
        'message' => 'Image analyzed successfully'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'message' => 'Image processing failed'
    ]);
}

function analyzeProductImage($imagePath, $conn) {
    // In a real application, this would use AI/ML services like:
    // - Google Cloud Vision API
    // - AWS Rekognition
    // - Azure Computer Vision
    // - Custom TensorFlow model
    
    // For demo purposes, we'll simulate product recognition
    $imageInfo = getimagesize($imagePath);
    $imageSize = filesize($imagePath);
    
    // Simulate AI analysis based on image properties
    $confidence = rand(75, 95) / 100;
    
    // Mock product recognition results
    $mockProducts = [
        [
            'product_name' => 'Fresh Tomatoes',
            'category' => 'Vegetables',
            'price' => 45.00,
            'unit' => 'kg',
            'stock' => 250,
            'confidence' => 0.92,
            'description' => 'Fresh red tomatoes, locally grown',
            'nutritional_info' => [
                'vitamins' => ['Vitamin C', 'Vitamin K', 'Folate'],
                'minerals' => ['Potassium', 'Manganese'],
                'calories_per_100g' => 18
            ]
        ],
        [
            'product_name' => 'Green Capsicum',
            'category' => 'Vegetables', 
            'price' => 55.00,
            'unit' => 'kg',
            'stock' => 180,
            'confidence' => 0.88,
            'description' => 'Fresh green bell peppers',
            'nutritional_info' => [
                'vitamins' => ['Vitamin C', 'Vitamin A'],
                'minerals' => ['Potassium'],
                'calories_per_100g' => 31
            ]
        ],
        [
            'product_name' => 'Carrots',
            'category' => 'Vegetables',
            'price' => 50.00,
            'unit' => 'kg',
            'stock' => 200,
            'confidence' => 0.85,
            'description' => 'Fresh orange carrots, high in beta-carotene',
            'nutritional_info' => [
                'vitamins' => ['Vitamin A', 'Vitamin K'],
                'minerals' => ['Potassium'],
                'calories_per_100g' => 41
            ]
        ],
        [
            'product_name' => 'Onions',
            'category' => 'Vegetables',
            'price' => 40.00,
            'unit' => 'kg',
            'stock' => 300,
            'confidence' => 0.83,
            'description' => 'Fresh yellow onions, perfect for cooking',
            'nutritional_info' => [
                'vitamins' => ['Vitamin C', 'Folate'],
                'minerals' => ['Potassium', 'Manganese'],
                'calories_per_100g' => 40
            ]
        ],
        [
            'product_name' => 'Potatoes',
            'category' => 'Vegetables',
            'price' => 35.00,
            'unit' => 'kg',
            'stock' => 400,
            'confidence' => 0.81,
            'description' => 'Fresh potatoes, great source of carbohydrates',
            'nutritional_info' => [
                'vitamins' => ['Vitamin C', 'Vitamin B6'],
                'minerals' => ['Potassium'],
                'calories_per_100g' => 77
            ]
        ],
        [
            'product_name' => 'Green Leafy Vegetables',
            'category' => 'Vegetables',
            'price' => 25.00,
            'unit' => 'bundle',
            'stock' => 150,
            'confidence' => 0.79,
            'description' => 'Mixed green leafy vegetables, rich in iron',
            'nutritional_info' => [
                'vitamins' => ['Vitamin A', 'Vitamin C', 'Folate'],
                'minerals' => ['Iron', 'Calcium'],
                'calories_per_100g' => 23
            ]
        ]
    ];
    
    // Select a random product for demo (in real app, this would be AI-determined)
    $selectedProduct = $mockProducts[array_rand($mockProducts)];
    
    // Try to find actual product in database
    $stmt = $conn->prepare("
        SELECT * FROM products 
        WHERE name LIKE ? OR category LIKE ?
        ORDER BY RAND()
        LIMIT 1
    ");
    $searchTerm = '%' . $selectedProduct['product_name'] . '%';
    $categoryTerm = '%' . $selectedProduct['category'] . '%';
    $stmt->bind_param("ss", $searchTerm, $categoryTerm);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $dbProduct = $result->fetch_assoc();
        $selectedProduct['product_name'] = $dbProduct['name'];
        $selectedProduct['price'] = (float)$dbProduct['price'];
        $selectedProduct['unit'] = $dbProduct['unit'];
        $selectedProduct['stock'] = $dbProduct['stock'];
        $selectedProduct['product_id'] = $dbProduct['id'];
        $selectedProduct['description'] = $dbProduct['description'] ?? $selectedProduct['description'];
    }
    
    // Add image analysis metadata
    $analysis = [
        'recognized_product' => $selectedProduct,
        'image_analysis' => [
            'width' => $imageInfo[0],
            'height' => $imageInfo[1],
            'type' => $imageInfo['mime'],
            'size_kb' => round($imageSize / 1024, 2),
            'quality_score' => rand(70, 95),
            'objects_detected' => rand(1, 3),
            'processing_time_ms' => rand(150, 500)
        ],
        'suggestions' => generateProductSuggestions($selectedProduct, $conn),
        'similar_products' => getSimilarProducts($selectedProduct, $conn),
        'bulk_pricing' => calculateBulkPricing($selectedProduct),
        'recipe_suggestions' => getRecipeSuggestions($selectedProduct['product_name'])
    ];
    
    return $analysis;
}

function generateProductSuggestions($product, $conn) {
    $suggestions = [
        "Add {$product['product_name']} to your cart",
        "Compare prices with similar products",
        "Check bulk discounts available",
        "View nutritional information",
        "Get recipe suggestions"
    ];
    
    // Add context-aware suggestions
    if ($product['stock'] < 50) {
        $suggestions[] = "⚠️ Limited stock - order soon!";
    }
    
    if ($product['price'] < 50) {
        $suggestions[] = "💰 Great value for money!";
    }
    
    return $suggestions;
}

function getSimilarProducts($product, $conn) {
    try {
        $stmt = $conn->prepare("
            SELECT name, price, unit, stock
            FROM products
            WHERE category = ? AND name != ?
            ORDER BY RAND()
            LIMIT 3
        ");
        $stmt->bind_param("ss", $product['category'], $product['product_name']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $similar = [];
        while ($row = $result->fetch_assoc()) {
            $similar[] = $row;
        }
        
        return $similar;
        
    } catch (Exception $e) {
        return [];
    }
}

function calculateBulkPricing($product) {
    $price = $product['price'];
    
    return [
        [
            'quantity' => 5,
            'discount' => 5,
            'price_per_unit' => round($price * 0.95, 2),
            'total_savings' => round($price * 5 * 0.05, 2)
        ],
        [
            'quantity' => 10,
            'discount' => 10,
            'price_per_unit' => round($price * 0.90, 2),
            'total_savings' => round($price * 10 * 0.10, 2)
        ],
        [
            'quantity' => 25,
            'discount' => 15,
            'price_per_unit' => round($price * 0.85, 2),
            'total_savings' => round($price * 25 * 0.15, 2)
        ]
    ];
}

function getRecipeSuggestions($productName) {
    $recipes = [
        'Tomatoes' => [
            'Tomato Curry',
            'Fresh Tomato Salad',
            'Tomato Rice',
            'Stuffed Tomatoes'
        ],
        'Carrots' => [
            'Carrot Halwa',
            'Carrot Stir Fry',
            'Carrot Soup',
            'Mixed Vegetable Curry'
        ],
        'Onions' => [
            'Onion Bhaji',
            'Caramelized Onions',
            'Onion Soup',
            'Pickled Onions'
        ],
        'Potatoes' => [
            'Potato Curry',
            'Mashed Potatoes',
            'Potato Fry',
            'Stuffed Potatoes'
        ],
        'Capsicum' => [
            'Stuffed Capsicum',
            'Capsicum Stir Fry',
            'Mixed Vegetable Curry',
            'Capsicum Salad'
        ]
    ];
    
    // Find matching recipes
    foreach ($recipes as $ingredient => $recipeList) {
        if (stripos($productName, $ingredient) !== false) {
            return $recipeList;
        }
    }
    
    // Default recipes for any vegetable
    return [
        'Mixed Vegetable Curry',
        'Vegetable Stir Fry',
        'Fresh Salad',
        'Vegetable Soup'
    ];
}
?>
