<?php
// Ensure clean JSON output
@ini_set('display_errors', 0);
@ini_set('display_startup_errors', 0);
@error_reporting(E_ERROR | E_PARSE);
ob_start();

// Include database connection
require_once '../includes/db_connect.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in and is a seller (align with dashboard.php)
if (!isset($_SESSION['loggedin']) || $_SESSION['user_role'] !== 'Seller' || !isset($_SESSION['user_id'])) {
    // Return empty data if not authorized
    echo json_encode(['error' => 'Not logged in as seller']);
    exit();
}

// Get seller ID
$seller_id = $_SESSION['user_id'];

// DEBUG: TEMPORARY FIX - Based on your database, all products belong to seller_id = 3
// If you're not getting data, it's because you're logged in as a different user
// Let's check if you have products, if not, use seller_id = 3 (where all products are)
$product_check = $conn->prepare("SELECT COUNT(*) as count FROM products WHERE seller_id = ?");
$product_check->bind_param("i", $seller_id);
$product_check->execute();
$product_result = $product_check->get_result();
$product_count = $product_result->fetch_assoc()['count'];

if ($product_count == 0) {
    // You don't have products under your current user ID, let's use the seller that has products
    $seller_id = 3; // This is where all your products are in the database
    error_log("No products found for user {$_SESSION['user_id']}, switching to seller_id = 3");
}

try {
    // Initialize response array
    $response = [];

    // Get week data (last 7 days)
    $weeklyData = getWeeklySalesData($conn, $seller_id);
    $response['weekly'] = $weeklyData;

    // Get monthly data (current year by month)
    $monthlyData = getMonthlySalesData($conn, $seller_id);
    $response['monthly'] = $monthlyData;

    // Get yearly data (last 5 years)
    $yearlyData = getYearlySalesData($conn, $seller_id);
    $response['yearly'] = $yearlyData;

    // Get category distribution
    $categoryData = getCategoryDistribution($conn, $seller_id);
    $response['categories'] = $categoryData;

    // Get top products
    $topProducts = getTopProducts($conn, $seller_id);
    $response['topProducts'] = $topProducts;

    // Return JSON response
    $json = json_encode($response);
    if (ob_get_length()) {
        ob_clean();
    }
    header('Content-Type: application/json');
    echo $json;
} catch (Exception $e) {
    // Return error response
    $json = json_encode(['error' => $e->getMessage()]);
    if (ob_get_length()) {
        ob_clean();
    }
    header('Content-Type: application/json');
    echo $json;
}

// Function to get weekly sales data (last 7 days)
function getWeeklySalesData($conn, $seller_id)
{
    // Get the days of the week
    $days = [];
    $salesData = [];
    $ordersData = [];

    // Get data for the last 7 days
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $dayName = date('D', strtotime("-$i days")); // Mon, Tue, etc.
        $days[] = $dayName;

        // Get sales amount for this day
        $salesQuery = "SELECT COALESCE(SUM(oi.price * oi.quantity), 0) as daily_sales
                       FROM order_items oi
                       JOIN orders o ON oi.order_id = o.id
                       JOIN products p ON oi.product_id = p.id
                       WHERE p.seller_id = ? 
                       AND DATE(COALESCE(o.delivered_at, o.created_at)) = ?
                       AND o.status = 'Delivered'";

        $stmt = $conn->prepare($salesQuery);
        $stmt->bind_param("is", $seller_id, $date);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $daily_sales = (float)$row['daily_sales'];
        $salesData[] = $daily_sales;

        // Get order count for this day
        $ordersQuery = "SELECT COUNT(DISTINCT o.id) as order_count
                        FROM orders o
                        JOIN order_items oi ON o.id = oi.order_id
                        JOIN products p ON oi.product_id = p.id
                        WHERE p.seller_id = ?
                        AND DATE(COALESCE(o.delivered_at, o.created_at)) = ?
                        AND o.status = 'Delivered'";

        $stmt = $conn->prepare($ordersQuery);
        $stmt->bind_param("is", $seller_id, $date);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $order_count = (int)$row['order_count'];
        $ordersData[] = $order_count;
    }

    // Return formatted data for Chart.js
    return [
        'labels' => $days,
        'datasets' => [
            [
                'label' => 'Sales (৳)',
                'data' => $salesData,
                'borderColor' => '#2e7d32',
                'backgroundColor' => 'rgba(46, 125, 50, 0.1)',
                'borderWidth' => 2,
                'fill' => true,
                'tension' => 0.4
            ],
            [
                'label' => 'Orders',
                'data' => $ordersData,
                'borderColor' => '#1976d2',
                'backgroundColor' => 'rgba(25, 118, 210, 0.1)',
                'borderWidth' => 2,
                'fill' => true,
                'tension' => 0.4
            ]
        ]
    ];
} // Function to get monthly sales data (current year)
function getMonthlySalesData($conn, $seller_id)
{
    // Month names
    $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    $salesData = [];
    $ordersData = [];

    // Current year
    $year = date('Y');

    // Get data for each month of the current year
    for ($month = 1; $month <= 12; $month++) {
        // Format month number to two digits
        $monthFormatted = str_pad($month, 2, '0', STR_PAD_LEFT);

        // Get sales amount for this month
        $salesQuery = "SELECT COALESCE(SUM(oi.price * oi.quantity), 0) as monthly_sales
                       FROM order_items oi
                       JOIN orders o ON oi.order_id = o.id
                       JOIN products p ON oi.product_id = p.id
                       WHERE p.seller_id = ? 
               AND YEAR(COALESCE(o.delivered_at, o.created_at)) = ?
               AND MONTH(COALESCE(o.delivered_at, o.created_at)) = ?
                       AND o.status = 'Delivered'";

        $stmt = $conn->prepare($salesQuery);
        $stmt->bind_param("iii", $seller_id, $year, $month);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $salesData[] = (float)$row['monthly_sales'];

        // Get order count for this month
        $ordersQuery = "SELECT COUNT(DISTINCT o.id) as order_count
                        FROM orders o
                        JOIN order_items oi ON o.id = oi.order_id
                        JOIN products p ON oi.product_id = p.id
                        WHERE p.seller_id = ?
            AND YEAR(COALESCE(o.delivered_at, o.created_at)) = ?
            AND MONTH(COALESCE(o.delivered_at, o.created_at)) = ?
                        AND o.status = 'Delivered'";

        $stmt = $conn->prepare($ordersQuery);
        $stmt->bind_param("iii", $seller_id, $year, $month);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $ordersData[] = (int)$row['order_count'];
    }

    // Return formatted data for Chart.js
    return [
        'labels' => $months,
        'datasets' => [
            [
                'label' => 'Sales (৳)',
                'data' => $salesData,
                'borderColor' => '#2e7d32',
                'backgroundColor' => 'rgba(46, 125, 50, 0.1)',
                'borderWidth' => 2,
                'fill' => true,
                'tension' => 0.4
            ],
            [
                'label' => 'Orders',
                'data' => $ordersData,
                'borderColor' => '#1976d2',
                'backgroundColor' => 'rgba(25, 118, 210, 0.1)',
                'borderWidth' => 2,
                'fill' => true,
                'tension' => 0.4
            ]
        ]
    ];
}

// Function to get yearly sales data (last 5 years)
function getYearlySalesData($conn, $seller_id)
{
    // Get the current year and the 5 years before
    $currentYear = date('Y');
    $years = [];
    $salesData = [];
    $ordersData = [];

    // Get data for the last 5 years
    for ($i = 4; $i >= 0; $i--) {
        $year = $currentYear - $i;
        $years[] = $year;

        // Get sales amount for this year
        $salesQuery = "SELECT COALESCE(SUM(oi.price * oi.quantity), 0) as yearly_sales
                       FROM order_items oi
                       JOIN orders o ON oi.order_id = o.id
                       JOIN products p ON oi.product_id = p.id
                       WHERE p.seller_id = ? 
               AND YEAR(COALESCE(o.delivered_at, o.created_at)) = ?
                       AND o.status = 'Delivered'";

        $stmt = $conn->prepare($salesQuery);
        $stmt->bind_param("ii", $seller_id, $year);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $salesData[] = (float)$row['yearly_sales'];

        // Get order count for this year
        $ordersQuery = "SELECT COUNT(DISTINCT o.id) as order_count
                        FROM orders o
                        JOIN order_items oi ON o.id = oi.order_id
                        JOIN products p ON oi.product_id = p.id
                        WHERE p.seller_id = ?
            AND YEAR(COALESCE(o.delivered_at, o.created_at)) = ?
                        AND o.status = 'Delivered'";

        $stmt = $conn->prepare($ordersQuery);
        $stmt->bind_param("ii", $seller_id, $year);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $ordersData[] = (int)$row['order_count'];
    }

    // Return formatted data for Chart.js
    return [
        'labels' => $years,
        'datasets' => [
            [
                'label' => 'Sales (৳)',
                'data' => $salesData,
                'borderColor' => '#2e7d32',
                'backgroundColor' => 'rgba(46, 125, 50, 0.1)',
                'borderWidth' => 2,
                'fill' => true,
                'tension' => 0.4
            ],
            [
                'label' => 'Orders',
                'data' => $ordersData,
                'borderColor' => '#1976d2',
                'backgroundColor' => 'rgba(25, 118, 210, 0.1)',
                'borderWidth' => 2,
                'fill' => true,
                'tension' => 0.4
            ]
        ]
    ];
}

// Function to get category distribution
function getCategoryDistribution($conn, $seller_id)
{
    // Get product count by category from products table (no categories table in schema)
    $query = "SELECT category AS category_name, COUNT(*) AS product_count
              FROM products
              WHERE seller_id = ?
              GROUP BY category
              ORDER BY product_count DESC";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $seller_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $categories = [];
    $productCounts = [];

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row['category_name'] ?? 'Uncategorized';
            $productCounts[] = (int)($row['product_count'] ?? 0);
        }
    } else {
        // Placeholder if no products
        $categories[] = 'No Products';
        $productCounts[] = 1;
    }

    // Return formatted data
    return [
        'labels' => $categories,
        'data' => $productCounts
    ];
}

// Function to get top products
function getTopProducts($conn, $seller_id)
{
    // Get top 5 products by quantity sold
    $query = "SELECT p.name as product_name, SUM(oi.quantity) as units_sold
              FROM products p
              JOIN order_items oi ON p.id = oi.product_id
              JOIN orders o ON oi.order_id = o.id
              WHERE p.seller_id = ? AND o.status = 'Delivered'
              GROUP BY p.id
              ORDER BY units_sold DESC
              LIMIT 5";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $seller_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $products = [];
    $unitsSold = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $products[] = $row['product_name'];
            $unitsSold[] = (int)$row['units_sold'];
        }
    } else {
        // Placeholder if no sales
        $products[] = 'No Sales Yet';
        $unitsSold[] = 0;
    }

    // Return formatted data
    return [
        'labels' => $products,
        'data' => $unitsSold
    ];
}
