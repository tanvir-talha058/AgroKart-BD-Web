<?php
// FILE: php/comparison_manager.php
session_start();
include '../includes/db_connect.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$product_id = $_POST['product_id'] ?? $_GET['product_id'] ?? 0;

switch ($action) {
    case 'add':
        addToComparison($conn, $product_id);
        break;
    case 'remove':
        removeFromComparison($conn, $product_id);
        break;
    case 'get_items':
        getComparisonItems($conn);
        break;
    case 'clear':
        clearComparison($conn);
        break;
    case 'get_comparison_data':
        getComparisonData($conn);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

function addToComparison($conn, $product_id) {
    try {
        // Initialize comparison session
        if (!isset($_SESSION['comparison'])) {
            $_SESSION['comparison'] = [];
        }
        
        // Check if product exists
        $checkProduct = $conn->prepare("SELECT id, name, category FROM products WHERE id = ?");
        $checkProduct->bind_param("i", $product_id);
        $checkProduct->execute();
        $product = $checkProduct->get_result()->fetch_assoc();
        
        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Product not found']);
            return;
        }
        
        // Check if already in comparison
        if (in_array($product_id, $_SESSION['comparison'])) {
            echo json_encode(['success' => false, 'message' => 'Product already in comparison']);
            return;
        }
        
        // Limit to 4 products
        if (count($_SESSION['comparison']) >= 4) {
            echo json_encode(['success' => false, 'message' => 'Maximum 4 products can be compared']);
            return;
        }
        
        // Add to comparison
        $_SESSION['comparison'][] = $product_id;
        
        // Save to database if user is logged in
        if (isset($_SESSION['user_id'])) {
            saveComparisonToDatabase($conn, $_SESSION['user_id'], $_SESSION['comparison']);
        }
        
        echo json_encode([
            'success' => true,
            'message' => $product['name'] . ' added to comparison',
            'comparison_count' => count($_SESSION['comparison']),
            'comparison_items' => $_SESSION['comparison']
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}

function removeFromComparison($conn, $product_id) {
    try {
        if (!isset($_SESSION['comparison'])) {
            $_SESSION['comparison'] = [];
        }
        
        $key = array_search($product_id, $_SESSION['comparison']);
        if ($key !== false) {
            unset($_SESSION['comparison'][$key]);
            $_SESSION['comparison'] = array_values($_SESSION['comparison']); // Reindex array
            
            // Update database if user is logged in
            if (isset($_SESSION['user_id'])) {
                saveComparisonToDatabase($conn, $_SESSION['user_id'], $_SESSION['comparison']);
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Product removed from comparison',
                'comparison_count' => count($_SESSION['comparison']),
                'comparison_items' => $_SESSION['comparison']
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Product not in comparison']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}

function getComparisonItems($conn) {
    try {
        if (!isset($_SESSION['comparison']) || empty($_SESSION['comparison'])) {
            echo json_encode(['success' => true, 'items' => [], 'count' => 0]);
            return;
        }
        
        $placeholders = str_repeat('?,', count($_SESSION['comparison']) - 1) . '?';
        $stmt = $conn->prepare("
            SELECT id, name, price, unit, image_path, category, stock, description
            FROM products 
            WHERE id IN ($placeholders)
        ");
        
        $types = str_repeat('i', count($_SESSION['comparison']));
        $stmt->bind_param($types, ...$_SESSION['comparison']);
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

function clearComparison($conn) {
    try {
        $_SESSION['comparison'] = [];
        
        // Clear from database if user is logged in
        if (isset($_SESSION['user_id'])) {
            saveComparisonToDatabase($conn, $_SESSION['user_id'], []);
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Comparison cleared',
            'comparison_count' => 0
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}

function getComparisonData($conn) {
    try {
        if (!isset($_SESSION['comparison']) || empty($_SESSION['comparison'])) {
            echo json_encode(['success' => false, 'message' => 'No products to compare']);
            return;
        }
        
        $placeholders = str_repeat('?,', count($_SESSION['comparison']) - 1) . '?';
        $stmt = $conn->prepare("
            SELECT p.*, 
                   AVG(r.rating) as avg_rating,
                   COUNT(r.id) as review_count
            FROM products p
            LEFT JOIN reviews r ON p.id = r.product_id
            WHERE p.id IN ($placeholders)
            GROUP BY p.id
        ");
        
        $types = str_repeat('i', count($_SESSION['comparison']));
        $stmt->bind_param($types, ...$_SESSION['comparison']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'price' => $row['price'],
                'unit' => $row['unit'],
                'image_path' => $row['image_path'],
                'category' => $row['category'],
                'stock' => $row['stock'],
                'description' => $row['description'],
                'avg_rating' => $row['avg_rating'] ? round($row['avg_rating'], 1) : 0,
                'review_count' => $row['review_count'],
                'availability' => $row['stock'] > 0 ? 'In Stock' : 'Out of Stock',
                'price_per_unit' => $row['price'] . ' per ' . $row['unit']
            ];
        }
        
        // Calculate comparison insights
        $insights = generateComparisonInsights($products);
        
        echo json_encode([
            'success' => true,
            'products' => $products,
            'insights' => $insights,
            'comparison_count' => count($products)
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}

function saveComparisonToDatabase($conn, $user_id, $comparison_items) {
    try {
        // Remove old comparisons for this user
        $deleteStmt = $conn->prepare("DELETE FROM product_comparisons WHERE user_id = ?");
        $deleteStmt->bind_param("i", $user_id);
        $deleteStmt->execute();
        
        if (!empty($comparison_items)) {
            // Save new comparison
            $insertStmt = $conn->prepare("INSERT INTO product_comparisons (user_id, product_ids) VALUES (?, ?)");
            $product_ids_json = json_encode($comparison_items);
            $insertStmt->bind_param("is", $user_id, $product_ids_json);
            $insertStmt->execute();
        }
    } catch (Exception $e) {
        // Silently fail for database saves
    }
}

function generateComparisonInsights($products) {
    if (count($products) < 2) {
        return [];
    }
    
    $insights = [];
    
    // Price comparison
    $prices = array_column($products, 'price');
    $cheapest = min($prices);
    $expensive = max($prices);
    
    $cheapest_product = null;
    $expensive_product = null;
    
    foreach ($products as $product) {
        if ($product['price'] == $cheapest) {
            $cheapest_product = $product;
        }
        if ($product['price'] == $expensive) {
            $expensive_product = $product;
        }
    }
    
    if ($cheapest_product && $expensive_product) {
        $insights[] = [
            'type' => 'price',
            'title' => 'Price Comparison',
            'message' => $cheapest_product['name'] . ' is the most affordable at ৳' . $cheapest,
            'highlight' => $cheapest_product['id']
        ];
        
        if ($cheapest != $expensive) {
            $savings = $expensive - $cheapest;
            $insights[] = [
                'type' => 'savings',
                'title' => 'Potential Savings',
                'message' => 'You can save ৳' . number_format($savings, 2) . ' by choosing the cheapest option',
                'highlight' => $cheapest_product['id']
            ];
        }
    }
    
    // Rating comparison
    $ratings = array_filter(array_column($products, 'avg_rating'));
    if (!empty($ratings)) {
        $highest_rating = max($ratings);
        foreach ($products as $product) {
            if ($product['avg_rating'] == $highest_rating && $highest_rating > 0) {
                $insights[] = [
                    'type' => 'rating',
                    'title' => 'Highest Rated',
                    'message' => $product['name'] . ' has the highest rating of ' . $highest_rating . ' stars',
                    'highlight' => $product['id']
                ];
                break;
            }
        }
    }
    
    // Stock availability
    $in_stock = array_filter($products, function($p) { return $p['stock'] > 0; });
    $out_of_stock = array_filter($products, function($p) { return $p['stock'] <= 0; });
    
    if (!empty($out_of_stock)) {
        $insights[] = [
            'type' => 'availability',
            'title' => 'Stock Alert',
            'message' => count($out_of_stock) . ' product(s) are currently out of stock',
            'highlight' => null
        ];
    }
    
    return $insights;
}

$conn->close();
?>
