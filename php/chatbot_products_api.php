<?php
// API endpoint for chatbot to get product data
header('Content-Type: application/json');

try {
    require_once '../includes/db_connect.php';
    
    $query = "SELECT id, name, description, price, stock, category, image_path FROM products ORDER BY created_at DESC";
    $result = $conn->query($query);
    
    $products = [];
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $products[] = [
                'id' => (int)$row['id'],
                'name' => $row['name'],
                'description' => $row['description'],
                'price' => (float)$row['price'],
                'stock' => (int)$row['stock'],
                'category' => $row['category'],
                'image_path' => $row['image_path']
            ];
        }
    }
    
    echo json_encode([
        'success' => true,
        'products' => $products,
        'total' => count($products)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database error',
        'products' => []
    ]);
}
?>
