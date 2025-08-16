<?php
// FILE: /php/order_process.php

// Debug: Log that this file was accessed
file_put_contents('../debug.log', date('Y-m-d H:i:s') . " - order_process.php accessed\n", FILE_APPEND);

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/db_connect.php';
require_once 'invoice_utils.php';

//--------------------------------------------------------//
require_once '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
//------------------------------------------------------//


// Check if user is logged in and is a buyer
if (!isset($_SESSION['loggedin']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Buyer') {
    $_SESSION['error'] = "Please log in as a buyer to place orders.";
    header('Location: ../login.php');
    exit;
}

// Check if request method is POST and cart is not empty
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $_SESSION['error'] = "Invalid request method.";
    header('Location: ../cart.php');
    exit;
}

if (empty($_SESSION['cart'])) {
    $_SESSION['error'] = "Your cart is empty.";
    header('Location: ../cart.php');
    exit;
}

// Validate required POST fields
if (empty($_POST['location']) || empty($_POST['payment_method'])) {
    $_SESSION['error'] = "Please fill in all required fields.";
    header('Location: ../checkout.php');
    exit;
}

// Process the order
try {
    $buyer_id = $_SESSION['user_id'];
    $delivery_location = trim($_POST['location']);
    $payment_method = trim($_POST['payment_method']);
    $order_notes = isset($_POST['order_notes']) ? trim($_POST['order_notes']) : '';
    $delivery_option = isset($_POST['delivery_option']) ? trim($_POST['delivery_option']) : 'standard';
    $delivery_fee = isset($_POST['delivery_fee']) ? floatval($_POST['delivery_fee']) : 0;
    $total_amount = 0;

    // Calculate subtotal
    foreach ($_SESSION['cart'] as $item) {
        if (isset($item['price']) && isset($item['quantity'])) {
            $total_amount += $item['price'] * $item['quantity'];
        }
    }

    // Add delivery fee if applicable
    $total_amount += $delivery_fee;

    // Use a transaction to ensure all queries succeed or none do
    $conn->begin_transaction();

    // 1. Insert into orders table
    $stmt_order = $conn->prepare("INSERT INTO orders (buyer_id, total_amount, delivery_location, notes, delivery_option, delivery_fee) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt_order->bind_param("idsssd", $buyer_id, $total_amount, $delivery_location, $order_notes, $delivery_option, $delivery_fee);
    $stmt_order->execute();
    $order_id = $stmt_order->insert_id;
    $stmt_order->close();

    // 2. Insert into order_items table and update product stock
    $stmt_item = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
    $stmt_stock = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");

    foreach ($_SESSION['cart'] as $product_id => $item) {
        $stmt_item->bind_param("iiid", $order_id, $product_id, $item['quantity'], $item['price']);
        $stmt_item->execute();

        $stmt_stock->bind_param("ii", $item['quantity'], $product_id);
        $stmt_stock->execute();
    }
    $stmt_item->close();
    $stmt_stock->close();

    // 3. Insert into payments table
    $transaction_id = "AGRO-" . strtoupper($payment_method) . "-" . time(); // Simulated TXN ID
    $stmt_payment = $conn->prepare("INSERT INTO payments (order_id, transaction_id, method) VALUES (?, ?, ?)");
    $stmt_payment->bind_param("iss", $order_id, $transaction_id, $payment_method);
    $stmt_payment->execute();
    $stmt_payment->close();

    // If all queries were successful, commit the transaction
    $conn->commit();

    // Clear the cart from session
    unset($_SESSION['cart']);

    // Clear the cart from database for this user
    $clear_cart_stmt = $conn->prepare("DELETE FROM user_cart WHERE user_id = ?");
    $clear_cart_stmt->bind_param("i", $buyer_id);
    $clear_cart_stmt->execute();
    $clear_cart_stmt->close();

    // Set order ID in session and redirect to success page
    $_SESSION['last_order_id'] = $order_id;
    $_SESSION['success'] = "Order placed successfully!";

    // Generate and send invoice email
    try {
        // Create invoices directory if it doesn't exist
        $invoice_dir = __DIR__ . '/../invoices';
        if (!file_exists($invoice_dir)) {
            mkdir($invoice_dir, 0755, true);
        }

        // For debugging, store some data in the session
        $_SESSION['order_debug'] = [
            'order_id' => $order_id,
            'timestamp' => date('Y-m-d H:i:s')
        ];

        // Generate PDF invoice
        $pdf_path = generatePDFInvoice($order_id);

        if ($pdf_path && file_exists($pdf_path)) {
            // Send invoice email
            $email_sent = sendInvoiceEmail($order_id, $pdf_path);

            if ($email_sent) {
                $_SESSION['email_sent'] = true;
            } else {
                // Log the error but continue with order confirmation
                error_log("Failed to send invoice email for order ID: $order_id");
                $_SESSION['email_sent'] = false;
            }
        } else {
            // Fallback to sending email without PDF attachment
            error_log("Failed to generate PDF invoice for order ID: $order_id. Path: " . ($pdf_path ?? 'null'));

            // Try to send a basic email notification without PDF
            $order_data = getOrderDetails($order_id);
            if ($order_data && isset($order_data['customer_email'])) {
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'fahimtalha79@gmail.com';
                    $mail->Password = 'hswjveecysxdnesl';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;
                    $mail->setFrom('noreply@agrokartbd.com', 'AgroKart BD');
                    $mail->addAddress($order_data['customer_email']);
                    $mail->isHTML(true);
                    $mail->Subject = 'AgroKart BD - Order #AGR-' . $order_id . ' Confirmation';
                    $mail->Body = "Thank you for your order! Your order #AGR-$order_id has been received and is being processed.";
                    $mail->send();
                    $_SESSION['email_sent'] = true;
                } catch (Exception $e) {
                    error_log("Fallback email failed: " . $e->getMessage());
                    $_SESSION['email_sent'] = false;
                }
            } else {
                $_SESSION['email_sent'] = false;
            }
        }
    } catch (Exception $e) {
        // Log the error but continue with order confirmation
        error_log("Error in invoice process: " . $e->getMessage());
        $_SESSION['email_sent'] = false;
    }
    header('Location: ../payment_success.php');
    exit();
} catch (mysqli_sql_exception $exception) {
    // If any query fails, roll back the transaction
    $conn->rollback();
    $_SESSION['error'] = "Order could not be placed. Please try again. Error: " . $exception->getMessage();
    header('Location: ../checkout.php');
    exit();
} catch (Exception $e) {
    // Handle any other errors
    $_SESSION['error'] = "An unexpected error occurred. Please try again.";
    header('Location: ../checkout.php');
    exit();
}

$conn->close();
