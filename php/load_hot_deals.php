<?php
require_once '../includes/db_connect.php';

try {
    // Query to get active hot deals with product details
    $sql = "SELECT 
                hd.id as deal_id,
                hd.discount_percentage,
                hd.discount_price,
                hd.valid_until,
                p.id as product_id,
                p.name,
                p.price as original_price,
                p.unit,
                p.image_path,
                p.category,
                p.stock,
                u.name as seller_name,
                u.id as seller_id
            FROM hot_deals hd
            INNER JOIN products p ON hd.product_id = p.id
            INNER JOIN users u ON p.seller_id = u.id
            WHERE hd.is_active = 1 
            AND hd.valid_until > NOW()
            AND p.stock > 0
            ORDER BY hd.created_at DESC
            LIMIT 20";

    $result = $conn->query($sql);
    $hot_deals = [];

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Calculate savings
            $savings = $row['original_price'] - $row['discount_price'];
            $savings_percentage = round(($savings / $row['original_price']) * 100);

            $row['savings'] = $savings;
            $row['savings_percentage'] = $savings_percentage;

            // Format prices
            $row['original_price_formatted'] = number_format($row['original_price'], 2);
            $row['discount_price_formatted'] = number_format($row['discount_price'], 2);
            $row['savings_formatted'] = number_format($savings, 2);

            // Check if deal is expiring soon (within 24 hours)
            $expires_at = strtotime($row['valid_until']);
            $now = time();
            $hours_left = ($expires_at - $now) / 3600;
            $row['is_expiring_soon'] = $hours_left <= 24;
            $row['hours_left'] = max(0, floor($hours_left));

            $hot_deals[] = $row;
        }
    }

    // Return as JSON for AJAX requests
    if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'deals' => $hot_deals,
            'total' => count($hot_deals)
        ]);
        exit;
    }

    // Return array for include
    return $hot_deals;
} catch (Exception $e) {
    error_log("Error loading hot deals: " . $e->getMessage());

    if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Failed to load hot deals',
            'deals' => []
        ]);
        exit;
    }

    return [];
}

$conn->close();
