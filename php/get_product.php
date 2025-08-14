<?php
// php/get_product.php
require_once '../includes/db_connect.php';

// Check if user is logged in and is a seller
if (!isset($_SESSION['loggedin']) || $_SESSION['user_role'] !== 'Seller') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

$seller_id = $_SESSION['user_id'];

// Check if product_id is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Product ID is missing']);
    exit;
}

$product_id = (int)$_GET['id'];

// Get product details and ensure it belongs to the logged-in seller
$sql = "SELECT * FROM products WHERE id = ? AND seller_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $product_id, $seller_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'Product not found or access denied']);
    exit;
}

$product = $result->fetch_assoc();
$stmt->close();

// Return product details as JSON
header('Content-Type: application/json');
echo json_encode($product);
exit;
