<?php
// FILE: invoice_debug.php
// This file is for debugging invoice generation issues

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include necessary files
require_once 'includes/db_connect.php';
require_once 'php/invoice_utils.php';

// Include header
include 'includes/header.php';

// Check if user is an admin (optional security check)
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Admin') {
    // Uncomment to restrict access
    // header('Location: index.php');
    // exit;
}

// Process form submission for testing invoice generation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $test_order_id = $_POST['order_id'];

    echo "<div class='container mt-5'>";
    echo "<h3>Testing Invoice Generation for Order #$test_order_id</h3>";

    try {
        // Test order details retrieval
        echo "<h4>Step 1: Getting order details</h4>";
        $order_data = getOrderDetails($test_order_id);

        if ($order_data) {
            echo "<div class='alert alert-success'>Successfully retrieved order details</div>";
            echo "<pre>" . print_r($order_data, true) . "</pre>";

            // Test PDF generation
            echo "<h4>Step 2: Generating PDF</h4>";
            $invoice_path = __DIR__ . '/invoices/';
            if (!file_exists($invoice_path)) {
                if (mkdir($invoice_path, 0755, true)) {
                    echo "<div class='alert alert-success'>Created invoices directory</div>";
                } else {
                    echo "<div class='alert alert-danger'>Failed to create invoices directory</div>";
                }
            } else {
                echo "<div class='alert alert-info'>Invoices directory already exists</div>";
            }

            // Test PDF generation
            $pdf_path = generatePDFInvoice($test_order_id);

            if ($pdf_path && file_exists($pdf_path)) {
                echo "<div class='alert alert-success'>Successfully generated PDF at: $pdf_path</div>";
                echo "<p><a href='invoices/" . basename($pdf_path) . "' class='btn btn-primary' target='_blank'>View PDF</a></p>";

                // Test email sending
                echo "<h4>Step 3: Testing Email</h4>";
                if (isset($_POST['send_email']) && $_POST['send_email'] == '1') {
                    $email_sent = sendInvoiceEmail($test_order_id, $pdf_path);

                    if ($email_sent) {
                        echo "<div class='alert alert-success'>Successfully sent email with invoice attachment</div>";
                    } else {
                        echo "<div class='alert alert-danger'>Failed to send email</div>";
                    }
                } else {
                    echo "<div class='alert alert-info'>Email sending skipped (not requested)</div>";
                }
            } else {
                echo "<div class='alert alert-danger'>Failed to generate PDF</div>";
            }
        } else {
            echo "<div class='alert alert-danger'>Order details not found for ID: $test_order_id</div>";
        }
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
    }

    echo "</div>";
}

// Debug info
echo "<div class='container mt-5'>";
echo "<h2>Invoice System Debug</h2>";

// Show environment info
echo "<h3>Environment Information</h3>";
echo "<div class='card mb-4'>";
echo "<div class='card-body'>";
echo "<ul>";
echo "<li><strong>PHP Version:</strong> " . phpversion() . "</li>";
echo "<li><strong>TCPDF Version:</strong> " . (class_exists('TCPDF') ? TCPDF_STATIC::getTCPDFVersion() : 'Not installed') . "</li>";
echo "<li><strong>PHPMailer:</strong> " . (class_exists('PHPMailer\PHPMailer\PHPMailer') ? 'Installed' : 'Not installed') . "</li>";
echo "<li><strong>File Permissions (invoices):</strong> " . (is_writable(__DIR__ . '/invoices') ? 'Writable' : 'Not writable') . "</li>";
echo "</ul>";
echo "</div>";
echo "</div>";

// Show recent orders for testing
echo "<h3>Recent Orders</h3>";
$order_query = $conn->query("SELECT o.id, o.buyer_id, o.total_amount, o.created_at, o.status, u.name as buyer_name, u.email as buyer_email
                           FROM orders o 
                           JOIN users u ON o.buyer_id = u.id 
                           ORDER BY o.id DESC LIMIT 10");

echo "<div class='card mb-4'>";
echo "<div class='card-body'>";
echo "<table class='table'>";
echo "<thead><tr><th>Order ID</th><th>Customer</th><th>Amount</th><th>Date</th><th>Status</th><th>Action</th></tr></thead>";
echo "<tbody>";

if ($order_query && $order_query->num_rows > 0) {
    while ($order = $order_query->fetch_assoc()) {
        echo "<tr>";
        echo "<td>#AGR-" . $order['id'] . "</td>";
        echo "<td>" . htmlspecialchars($order['buyer_name']) . "<br><small>" . htmlspecialchars($order['buyer_email']) . "</small></td>";
        echo "<td>৳" . number_format($order['total_amount'], 2) . "</td>";
        echo "<td>" . (isset($order['created_at']) ? $order['created_at'] : 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($order['status'] ?? 'Pending') . "</td>";
        echo "<td>
                <form method='post'>
                    <input type='hidden' name='order_id' value='" . $order['id'] . "'>
                    <div class='form-check mb-2'>
                        <input class='form-check-input' type='checkbox' name='send_email' value='1' id='send_email_" . $order['id'] . "'>
                        <label class='form-check-label' for='send_email_" . $order['id'] . "'>Send Email</label>
                    </div>
                    <button type='submit' class='btn btn-sm btn-primary'>Test Invoice</button>
                </form>
            </td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='6'>No orders found</td></tr>";
}

echo "</tbody>";
echo "</table>";
echo "</div>";
echo "</div>";

// Test form
echo "<h3>Manual Test</h3>";
echo "<div class='card mb-4'>";
echo "<div class='card-body'>";
echo "<form method='post'>";
echo "<div class='form-group mb-3'>";
echo "<label for='order_id'>Order ID:</label>";
echo "<input type='number' class='form-control' id='order_id' name='order_id' required>";
echo "</div>";
echo "<div class='form-check mb-3'>";
echo "<input class='form-check-input' type='checkbox' name='send_email' value='1' id='send_email'>";
echo "<label class='form-check-label' for='send_email'>Send test email</label>";
echo "</div>";
echo "<button type='submit' class='btn btn-primary'>Test Invoice Generation</button>";
echo "</form>";
echo "</div>";
echo "</div>";

echo "</div>";

// Include footer
include 'includes/footer.php';
