<?php
// FILE: php/recommendation_engine.php
session_start();
include __DIR__ . '/../includes/db_connect.php';

header('Content-Type: application/json');

$type = $_GET['type'] ?? 'general';
$user_id = $_SESSION['user_id'] ?? null;
$product_id = $_GET['product_id'] ?? null;
$limit = $_GET['limit'] ?? 6;

switch ($type) {
    case 'similar':
        getSimilarProducts($conn, $product_id, $limit);
        break;
    case 'recently_viewed':
        getRecentlyViewed($conn, $user_id, $limit);
        break;
    case 'frequently_bought':
        getFrequentlyBought($conn, $user_id, $limit);
        break;
    case 'trending':
        getTrendingProducts($conn, $limit);
        break;
    case 'seasonal':
        getSeasonalRecommendations($conn, $limit);
        break;
    case 'wishlist':
        getWishlistRecommendations($conn, $user_id, $limit);
        break;
    case 'personalized':
        getPersonalizedRecommendations($conn, $user_id, $limit);
        break;
    default:
        getGeneralRecommendations($conn, $limit);
}

function getSimilarProducts($conn, $product_id, $limit) {
    try {
        if (!$product_id) {
            echo json_encode(['success' => false, 'message' => 'Product ID required']);
            return;
        }
        
        // Get the current product's category
        $categoryStmt = $conn->prepare("SELECT category FROM products WHERE id = ?");
        $categoryStmt->bind_param("i", $product_id);
        $categoryStmt->execute();
        $categoryResult = $categoryStmt->get_result();
        
        if ($categoryResult->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Product not found']);
            return;
        }
        
        $category = $categoryResult->fetch_assoc()['category'];
        
        // Get similar products from the same category
        $stmt = $conn->prepare("
            SELECT p.*, AVG(r.rating) as avg_rating, COUNT(r.id) as review_count
            FROM products p
            LEFT JOIN reviews r ON p.id = r.product_id
            WHERE p.category = ? AND p.id != ? AND p.stock > 0
            GROUP BY p.id
            ORDER BY avg_rating DESC, p.stock DESC
            LIMIT ?
        ");
        $stmt->bind_param("sii", $category, $product_id, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $recommendations = [];
        while ($row = $result->fetch_assoc()) {
            $recommendations[] = formatProduct($row);
        }
        
        echo json_encode([
            'success' => true,
            'recommendations' => $recommendations,
            'type' => 'similar',
            'title' => 'Similar Products'
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}

function getRecentlyViewed($conn, $user_id, $limit) {
    try {
        if (!$user_id) {
            echo json_encode(['success' => false, 'message' => 'User not logged in']);
            return;
        }
        
        $stmt = $conn->prepare("
            SELECT DISTINCT p.*, AVG(r.rating) as avg_rating, COUNT(r.id) as review_count
            FROM recently_viewed rv
            JOIN products p ON rv.product_id = p.id
            LEFT JOIN reviews r ON p.id = r.product_id
            WHERE rv.user_id = ?
            GROUP BY p.id
            ORDER BY rv.viewed_at DESC
            LIMIT ?
        ");
        $stmt->bind_param("ii", $user_id, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $recommendations = [];
        while ($row = $result->fetch_assoc()) {
            $recommendations[] = formatProduct($row);
        }
        
        echo json_encode([
            'success' => true,
            'recommendations' => $recommendations,
            'type' => 'recently_viewed',
            'title' => 'Recently Viewed'
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}

function getFrequentlyBought($conn, $user_id, $limit) {
    try {
        if (!$user_id) {
            getTrendingProducts($conn, $limit);
            return;
        }
        
        $stmt = $conn->prepare("
            SELECT p.*, COUNT(oi.product_id) as purchase_count, AVG(r.rating) as avg_rating, COUNT(r.id) as review_count
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.id
            JOIN products p ON oi.product_id = p.id
            LEFT JOIN reviews r ON p.id = r.product_id
            WHERE o.user_id = ? AND o.status = 'Delivered'
            GROUP BY p.id
            ORDER BY purchase_count DESC, avg_rating DESC
            LIMIT ?
        ");
        $stmt->bind_param("ii", $user_id, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $recommendations = [];
        while ($row = $result->fetch_assoc()) {
            $product = formatProduct($row);
            $product['purchase_count'] = $row['purchase_count'];
            $recommendations[] = $product;
        }
        
        echo json_encode([
            'success' => true,
            'recommendations' => $recommendations,
            'type' => 'frequently_bought',
            'title' => 'Your Favorites'
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}

function getTrendingProducts($conn, $limit) {
    try {
        $stmt = $conn->prepare("
            SELECT p.*, COUNT(oi.product_id) as order_count, AVG(r.rating) as avg_rating, COUNT(r.id) as review_count
            FROM products p
            LEFT JOIN order_items oi ON p.id = oi.product_id
            LEFT JOIN orders o ON oi.order_id = o.id AND o.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            LEFT JOIN reviews r ON p.id = r.product_id
            WHERE p.stock > 0
            GROUP BY p.id
            ORDER BY order_count DESC, avg_rating DESC, p.stock DESC
            LIMIT ?
        ");
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $recommendations = [];
        while ($row = $result->fetch_assoc()) {
            $recommendations[] = formatProduct($row);
        }
        
        echo json_encode([
            'success' => true,
            'recommendations' => $recommendations,
            'type' => 'trending',
            'title' => 'Trending This Week'
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}

function getSeasonalRecommendations($conn, $limit) {
    try {
        $season = getCurrentSeason();
        $seasonal_keywords = getSeasonalKeywords($season);
        
        $keyword_conditions = [];
        $params = [];
        $types = "";
        
        foreach ($seasonal_keywords as $keyword) {
            $keyword_conditions[] = "(p.name LIKE ? OR p.category LIKE ? OR p.description LIKE ?)";
            $params[] = "%$keyword%";
            $params[] = "%$keyword%";
            $params[] = "%$keyword%";
            $types .= "sss";
        }
        
        $where_clause = implode(" OR ", $keyword_conditions);
        
        $stmt = $conn->prepare("
            SELECT p.*, AVG(r.rating) as avg_rating, COUNT(r.id) as review_count
            FROM products p
            LEFT JOIN reviews r ON p.id = r.product_id
            WHERE ($where_clause) AND p.stock > 0
            GROUP BY p.id
            ORDER BY avg_rating DESC, p.stock DESC
            LIMIT ?
        ");
        
        $params[] = $limit;
        $types .= "i";
        
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $recommendations = [];
        while ($row = $result->fetch_assoc()) {
            $recommendations[] = formatProduct($row);
        }
        
        echo json_encode([
            'success' => true,
            'recommendations' => $recommendations,
            'type' => 'seasonal',
            'title' => "Perfect for $season"
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}

function getWishlistRecommendations($conn, $user_id, $limit) {
    try {
        if (!$user_id) {
            getGeneralRecommendations($conn, $limit);
            return;
        }
        
        // Get categories from user's wishlist
        $categoryStmt = $conn->prepare("
            SELECT DISTINCT p.category
            FROM wishlist w
            JOIN products p ON w.product_id = p.id
            WHERE w.user_id = ?
        ");
        $categoryStmt->bind_param("i", $user_id);
        $categoryStmt->execute();
        $categoryResult = $categoryStmt->get_result();
        
        $categories = [];
        while ($row = $categoryResult->fetch_assoc()) {
            $categories[] = $row['category'];
        }
        
        if (empty($categories)) {
            getGeneralRecommendations($conn, $limit);
            return;
        }
        
        $placeholders = str_repeat('?,', count($categories) - 1) . '?';
        $stmt = $conn->prepare("
            SELECT p.*, AVG(r.rating) as avg_rating, COUNT(r.id) as review_count
            FROM products p
            LEFT JOIN reviews r ON p.id = r.product_id
            LEFT JOIN wishlist w ON p.id = w.product_id AND w.user_id = ?
            WHERE p.category IN ($placeholders) AND w.id IS NULL AND p.stock > 0
            GROUP BY p.id
            ORDER BY avg_rating DESC, p.stock DESC
            LIMIT ?
        ");
        
        $types = "i" . str_repeat('s', count($categories)) . "i";
        $params = array_merge([$user_id], $categories, [$limit]);
        
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $recommendations = [];
        while ($row = $result->fetch_assoc()) {
            $recommendations[] = formatProduct($row);
        }
        
        echo json_encode([
            'success' => true,
            'recommendations' => $recommendations,
            'type' => 'wishlist_based',
            'title' => 'Based on Your Wishlist'
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}

function getPersonalizedRecommendations($conn, $user_id, $limit) {
    try {
        if (!$user_id) {
            getGeneralRecommendations($conn, $limit);
            return;
        }
        
        // Combine multiple recommendation strategies
        $recommendations = [];
        
        // Get user's purchase history categories
        $historyStmt = $conn->prepare("
            SELECT p.category, COUNT(*) as frequency
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.id
            JOIN products p ON oi.product_id = p.id
            WHERE o.user_id = ? AND o.status = 'Delivered'
            GROUP BY p.category
            ORDER BY frequency DESC
            LIMIT 3
        ");
        $historyStmt->bind_param("i", $user_id);
        $historyStmt->execute();
        $historyResult = $historyStmt->get_result();
        
        $preferred_categories = [];
        while ($row = $historyResult->fetch_assoc()) {
            $preferred_categories[] = $row['category'];
        }
        
        if (!empty($preferred_categories)) {
            $placeholders = str_repeat('?,', count($preferred_categories) - 1) . '?';
            $productStmt = $conn->prepare("
                SELECT p.*, AVG(r.rating) as avg_rating, COUNT(r.id) as review_count
                FROM products p
                LEFT JOIN reviews r ON p.id = r.product_id
                LEFT JOIN order_items oi ON p.id = oi.product_id
                LEFT JOIN orders o ON oi.order_id = o.id AND o.user_id = ?
                WHERE p.category IN ($placeholders) AND o.id IS NULL AND p.stock > 0
                GROUP BY p.id
                ORDER BY avg_rating DESC, p.stock DESC
                LIMIT ?
            ");
            
            $types = "i" . str_repeat('s', count($preferred_categories)) . "i";
            $params = array_merge([$user_id], $preferred_categories, [$limit]);
            
            $productStmt->bind_param($types, ...$params);
            $productStmt->execute();
            $productResult = $productStmt->get_result();
            
            while ($row = $productResult->fetch_assoc()) {
                $recommendations[] = formatProduct($row);
            }
        }
        
        // Fill remaining slots with trending products
        if (count($recommendations) < $limit) {
            $remaining = $limit - count($recommendations);
            $existing_ids = array_column($recommendations, 'id');
            
            if (!empty($existing_ids)) {
                $id_placeholders = str_repeat('?,', count($existing_ids) - 1) . '?';
                $trendingStmt = $conn->prepare("
                    SELECT p.*, AVG(r.rating) as avg_rating, COUNT(r.id) as review_count
                    FROM products p
                    LEFT JOIN reviews r ON p.id = r.product_id
                    WHERE p.id NOT IN ($id_placeholders) AND p.stock > 0
                    GROUP BY p.id
                    ORDER BY avg_rating DESC, p.stock DESC
                    LIMIT ?
                ");
                
                $types = str_repeat('i', count($existing_ids)) . "i";
                $params = array_merge($existing_ids, [$remaining]);
                
                $trendingStmt->bind_param($types, ...$params);
                $trendingStmt->execute();
                $trendingResult = $trendingStmt->get_result();
                
                while ($row = $trendingResult->fetch_assoc()) {
                    $recommendations[] = formatProduct($row);
                }
            }
        }
        
        echo json_encode([
            'success' => true,
            'recommendations' => $recommendations,
            'type' => 'personalized',
            'title' => 'Recommended for You'
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}

function getGeneralRecommendations($conn, $limit) {
    try {
        $stmt = $conn->prepare("
            SELECT p.*, AVG(r.rating) as avg_rating, COUNT(r.id) as review_count
            FROM products p
            LEFT JOIN reviews r ON p.id = r.product_id
            WHERE p.stock > 0
            GROUP BY p.id
            ORDER BY avg_rating DESC, p.stock DESC
            LIMIT ?
        ");
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $recommendations = [];
        while ($row = $result->fetch_assoc()) {
            $recommendations[] = formatProduct($row);
        }
        
        echo json_encode([
            'success' => true,
            'recommendations' => $recommendations,
            'type' => 'general',
            'title' => 'Popular Products'
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}

function formatProduct($row) {
    return [
        'id' => $row['id'],
        'name' => $row['name'],
        'price' => $row['price'],
        'unit' => $row['unit'],
        'image_path' => $row['image_path'],
        'category' => $row['category'],
        'stock' => $row['stock'],
        'description' => $row['description'] ?? '',
        'avg_rating' => $row['avg_rating'] ? round($row['avg_rating'], 1) : 0,
        'review_count' => $row['review_count'] ?? 0
    ];
}

function getCurrentSeason() {
    $month = date('n');
    if ($month >= 3 && $month <= 5) return 'Spring';
    if ($month >= 6 && $month <= 8) return 'Summer';
    if ($month >= 9 && $month <= 11) return 'Autumn';
    return 'Winter';
}

function getSeasonalKeywords($season) {
    $keywords = [
        'Spring' => ['fresh', 'green', 'leafy', 'herbs', 'sprouts'],
        'Summer' => ['cool', 'fresh', 'watery', 'cucumber', 'melon', 'refreshing'],
        'Autumn' => ['harvest', 'root', 'pumpkin', 'squash', 'seasonal'],
        'Winter' => ['warming', 'hot', 'spiced', 'comfort', 'hearty']
    ];
    
    return $keywords[$season] ?? $keywords['Spring'];
}

// Track recently viewed products
function trackRecentlyViewed($conn, $user_id, $product_id) {
    try {
        if ($user_id) {
            // Remove old entry if exists
            $deleteStmt = $conn->prepare("DELETE FROM recently_viewed WHERE user_id = ? AND product_id = ?");
            $deleteStmt->bind_param("ii", $user_id, $product_id);
            $deleteStmt->execute();
            
            // Add new entry
            $insertStmt = $conn->prepare("INSERT INTO recently_viewed (user_id, product_id) VALUES (?, ?)");
            $insertStmt->bind_param("ii", $user_id, $product_id);
            $insertStmt->execute();
            
            // Keep only last 20 viewed products
            $cleanupStmt = $conn->prepare("
                DELETE FROM recently_viewed 
                WHERE user_id = ? 
                AND id NOT IN (
                    SELECT id FROM (
                        SELECT id FROM recently_viewed 
                        WHERE user_id = ? 
                        ORDER BY viewed_at DESC 
                        LIMIT 20
                    ) tmp
                )
            ");
            $cleanupStmt->bind_param("ii", $user_id, $user_id);
            $cleanupStmt->execute();
        }
    } catch (Exception $e) {
        // Silently fail for tracking
    }
}

$conn->close();
?>
