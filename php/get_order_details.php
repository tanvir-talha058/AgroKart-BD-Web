<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/db_connect.php';

header('Content-Type: application/json');

// Create a log file for debugging
$log_file = fopen("order_details_log.txt", "a");
fwrite($log_file, "\n\n" . date('Y-m-d H:i:s') . " - New request received\n");

if (!isset($_SESSION['loggedin'])) {
    fwrite($log_file, "User not logged in\n");
    echo json_encode(['success' => false, 'message' => 'You must be logged in to view order details.']);
    fclose($log_file);
    exit();
}

if (!isset($_GET['order_id'])) {
    fwrite($log_file, "No order ID provided\n");
    echo json_encode(['success' => false, 'message' => 'Order ID is required.']);
    fclose($log_file);
    exit();
}

$order_id = intval($_GET['order_id']);
$user_id = $_SESSION['user_id'];

fwrite($log_file, "Processing request for order ID: $order_id, user ID: $user_id\n");

// Get order details with buyer information
$order_sql = "SELECT o.*, u.name as buyer_name, u.phone as buyer_phone, u.city, u.district, u.division 
              FROM orders o
              LEFT JOIN users u ON o.buyer_id = u.id
              WHERE o.id = ? AND (o.buyer_id = ? OR EXISTS (
                  SELECT 1 FROM order_items oi 
                  JOIN products p ON oi.product_id = p.id 
                  WHERE oi.order_id = o.id AND p.seller_id = ?
              ))";

fwrite($log_file, "Executing order query\n");

$order_stmt = $conn->prepare($order_sql);
$order_stmt->bind_param("iii", $order_id, $user_id, $user_id);
$order_stmt->execute();
$order_result = $order_stmt->get_result();
$order = $order_result->fetch_assoc();
$order_stmt->close();

if (!$order) {
    fwrite($log_file, "Order not found or access denied\n");
    echo json_encode(['success' => false, 'message' => 'Order not found or you don\'t have permission to view it.']);
    fclose($log_file);
    exit();
}

// Get order items
$items_sql = "SELECT oi.*, p.name, p.image_path, p.unit 
              FROM order_items oi
              JOIN products p ON oi.product_id = p.id
              WHERE oi.order_id = ?";

fwrite($log_file, "Fetching order items\n");

$items_stmt = $conn->prepare($items_sql);
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();

$items = [];
$total_amount = 0;

while ($item = $items_result->fetch_assoc()) {
    // Ensure image path is absolute
    if ($item['image_path'] && !filter_var($item['image_path'], FILTER_VALIDATE_URL)) {
        $item['image_path'] = '../' . ltrim($item['image_path'], '/');
    }

    $subtotal = $item['quantity'] * $item['price'];
    $total_amount += $subtotal;

    $items[] = [
        'id' => $item['id'],
        'name' => $item['name'],
        'quantity' => $item['quantity'],
        'price' => $item['price'],
        'unit' => $item['unit'],
        'image_path' => $item['image_path'],
        'subtotal' => $subtotal
    ];
}

$items_stmt->close();

fwrite($log_file, "Found " . count($items) . " items\n");

// Fetch payment details for this order
$payment_method = null;
$payment_status = null;
try {
    $pay_sql = "SELECT method, status FROM payments WHERE order_id = ? ORDER BY id DESC LIMIT 1";
    $pay_stmt = $conn->prepare($pay_sql);
    $pay_stmt->bind_param("i", $order_id);
    $pay_stmt->execute();
    $pay_res = $pay_stmt->get_result();
    if ($pay_row = $pay_res->fetch_assoc()) {
        $payment_method = $pay_row['method'] ?? null;
        $payment_status = $pay_row['status'] ?? null;
    }
    $pay_stmt->close();
} catch (Exception $e) {
    fwrite($log_file, "Payment fetch error: " . $e->getMessage() . "\n");
}

// Enforce display rule: bKash/Nagad/Card => Paid, COD => Pending
if ($payment_method) {
    if (strtoupper($payment_method) === 'COD') {
        $payment_status = 'Pending';
    } else {
        // For any non-COD online methods (bKash, Nagad, Card), show Paid
        $payment_status = 'Paid';
    }
}

// Prepare the response
// Build shipping details per requirement
$user_city = trim($order['city'] ?? '');
$shipping_name = 'AgroKart';
$shipping_phone = '01700000000';
$shipping_address = $user_city !== ''
    ? ($user_city . ' Bus Stand, AgroKart Shop')
    : (!empty($order['delivery_location']) ? $order['delivery_location'] : 'No address');

// Provide safe defaults for payment info to avoid undefined in UI
// Provide safe defaults for payment info to avoid undefined in UI
$payment_method = $payment_method ?? 'COD';
$payment_status = $payment_status ?? 'Pending';

$response = [
    'success' => true,
    'order' => [
        'order_id' => $order['id'],
        'status' => $order['status'],
        'created_at' => $order['created_at'],
        'notes' => $order['notes'] ?? '',
        'total_amount' => $total_amount,
        'delivery_fee' => $order['delivery_fee'] ?? 0,
        'delivery_option' => $order['delivery_option'] ?? 'standard',
        // Names used by UI
        'full_name' => $shipping_name,
        'phone' => $shipping_phone,
        'delivery_location' => $shipping_address,
        // Keep original buyer info for reference
        'buyer_name' => $order['buyer_name'] ?? 'Unknown',
        'buyer_phone' => $order['buyer_phone'] ?? 'No phone',
        'location' => !empty($order['city']) ? implode(', ', array_filter([$order['city'], $order['district'], $order['division']])) : 'No address',
        // Payment fields expected by UI
        'payment_method' => $payment_method,
        'payment_status' => $payment_status,
        'items' => $items
    ]
];

fwrite($log_file, "Sending response\n");
fwrite($log_file, "--------------------------------\n");
fclose($log_file);

echo json_encode($response);
$conn->close();
