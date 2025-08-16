<?php
// FILE: php/invoice_utils.php

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once '../includes/db_connect.php';

// Include PHPMailer and TCPDF
require_once '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Function to generate a PDF invoice for an order
function generatePDFInvoice($order_id)
{
    global $conn;

    // Get order details
    $order_data = getOrderDetails($order_id);

    if (!$order_data) {
        error_log("Order data not found for ID: $order_id");
        return false;
    }

    try {
        // Create new TCPDF instance
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

        // Set document information
        $pdf->SetCreator('AgroKart BD');
        $pdf->SetAuthor('AgroKart BD');
        $pdf->SetTitle('Invoice #AGR-' . $order_id);
        $pdf->SetSubject('Order Invoice');
        $pdf->SetKeywords('invoice, agrokart, order, receipt');

        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Set default monospaced font
        $pdf->SetDefaultMonospacedFont('courier');

        // Set margins
        $pdf->SetMargins(20, 20, 20);

        // Set auto page breaks
        $pdf->SetAutoPageBreak(true, 20);

        // Add a page
        $pdf->AddPage();

        // Set color for heading
        $pdf->SetFillColor(76, 175, 80);
        $pdf->Rect(0, 0, $pdf->getPageWidth(), 40, 'F');

        // Company name
        $pdf->SetFont('helvetica', 'B', 24);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Text(20, 25, 'AgroKart BD');

        // Tagline
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Text(20, 32, 'Fresh Products, Direct from Farm');

        // Invoice title
        $pdf->SetTextColor(44, 62, 80);
        $pdf->SetFont('helvetica', 'B', 20);
        $pdf->Text(20, 60, 'PAYMENT RECEIPT');

        // Success indicator
        $pdf->SetFillColor(76, 175, 80);
        $pdf->Circle(180, 55, 8, 0, 360, 'F');
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Text(177, 58, '✓');

        // Order information section
        $pdf->SetFillColor(248, 249, 250);
        $pdf->Rect(20, 75, 170, 35, 'F');

        $pdf->SetTextColor(44, 62, 80);
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Text(25, 88, 'Order Information');

        $pdf->SetFont('helvetica', '', 11);
        $pdf->Text(25, 98, 'Order ID:');
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Text(70, 98, '#AGR-' . $order_id);

        $pdf->SetFont('helvetica', '', 11);
        $pdf->Text(25, 105, 'Order Date:');
        $pdf->SetFont('helvetica', 'B', 11);
        // Use current date if order_date is not available
        $order_date = isset($order_data['order_date']) ? $order_data['order_date'] : date('Y-m-d H:i:s');
        $pdf->Text(70, 105, date('F j, Y, g:i a', strtotime($order_date)));

        // Payment status
        $pdf->SetTextColor(76, 175, 80);
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Text(120, 98, 'Status: CONFIRMED ✓');

        // Customer information
        $pdf->SetTextColor(44, 62, 80);
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Text(25, 130, 'Customer Information');

        $pdf->SetFont('helvetica', '', 11);
        $pdf->Text(25, 140, 'Customer: ' . $order_data['customer_name']);
        $pdf->Text(25, 147, 'Email: ' . $order_data['customer_email']);
        if (isset($order_data['customer_phone'])) {
            $pdf->Text(25, 154, 'Phone: ' . $order_data['customer_phone']);
        }

        // Order items table header
        $pdf->SetFillColor(248, 249, 250);
        $pdf->Rect(20, 165, 170, 20, 'F');

        $pdf->SetTextColor(44, 62, 80);
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Text(25, 178, 'Order Summary');

        // Table header
        $pdf->SetFillColor(76, 175, 80);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Rect(20, 190, 170, 10, 'F');
        $pdf->Text(25, 197, 'Product');
        $pdf->Text(100, 197, 'Quantity');
        $pdf->Text(135, 197, 'Price');
        $pdf->Text(165, 197, 'Total');

        // Table rows
        $pdf->SetTextColor(44, 62, 80);
        $pdf->SetFont('helvetica', '', 10);
        $y_position = 200;
        $total = 0;

        foreach ($order_data['items'] as $item) {
            $pdf->Text(25, $y_position + 7, substr($item['product_name'], 0, 40));
            $pdf->Text(100, $y_position + 7, $item['quantity']);
            $pdf->Text(135, $y_position + 7, '৳' . number_format($item['price'], 2));
            $item_total = $item['price'] * $item['quantity'];
            $pdf->Text(165, $y_position + 7, '৳' . number_format($item_total, 2));
            $total += $item_total;

            // Draw line
            $pdf->Line(20, $y_position + 10, 190, $y_position + 10);

            $y_position += 15;
        }

        // Add delivery fee if present
        if ($order_data['delivery_fee'] > 0) {
            $pdf->Text(25, $y_position + 7, 'Delivery Fee');
            $pdf->Text(165, $y_position + 7, '৳' . number_format($order_data['delivery_fee'], 2));
            $total += $order_data['delivery_fee'];
            $pdf->Line(20, $y_position + 10, 190, $y_position + 10);
            $y_position += 15;
        }

        // Total
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Text(135, $y_position + 7, 'Total:');
        $pdf->Text(165, $y_position + 7, '৳' . number_format($total, 2));

        // Payment information
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Text(25, $y_position + 25, 'Payment Information');

        $pdf->SetFont('helvetica', '', 11);
        $pdf->Text(25, $y_position + 35, 'Payment Method: ' . $order_data['payment_method']);
        $pdf->Text(25, $y_position + 42, 'Payment Status: ' . $order_data['status']);
        if (isset($order_data['transaction_id'])) {
            $pdf->Text(25, $y_position + 49, 'Transaction ID: ' . $order_data['transaction_id']);
        }

        // Footer
        $pdf->SetFillColor(76, 175, 80);
        $pdf->Rect(0, 270, $pdf->getPageWidth(), 27, 'F');

        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Text(20, 283, 'Thank you for choosing AgroKart BD!');
        $pdf->Text(20, 290, 'For support: support@agrokartbd.com | +880-XXXXXXXXX');

        // Watermark
        $pdf->SetTextColor(230, 230, 230);
        $pdf->SetFont('helvetica', 'B', 50);
        $pdf->StartTransform();
        $pdf->Rotate(45, 105, 150);
        $pdf->Text(105, 150, 'PAID');
        $pdf->StopTransform();

        // Save PDF to a file
        $invoice_path = __DIR__ . '/../invoices/';
        if (!file_exists($invoice_path)) {
            if (!mkdir($invoice_path, 0755, true)) {
                error_log("Failed to create directory: $invoice_path");
                return false;
            }
        }

        $file_name = 'AgroKart-Invoice-' . $order_id . '.pdf';
        $file_path = $invoice_path . $file_name;

        // Get the PDF as a string
        $pdf_content = $pdf->Output('', 'S');

        // Write the PDF content to a file
        if (file_put_contents($file_path, $pdf_content) === false) {
            error_log("Failed to write PDF to file: $file_path");
            return false;
        }

        return $file_path;
    } catch (Exception $e) {
        error_log("PDF generation error: " . $e->getMessage());
        return false;
    }
}

// Function to get order details for invoice
function getOrderDetails($order_id)
{
    global $conn;

    $result = array();

    // Get order details
    $order_stmt = $conn->prepare("
        SELECT o.*, p.transaction_id, p.method as payment_method,
        COALESCE(o.created_at, NOW()) as order_date
        FROM orders o 
        LEFT JOIN payments p ON o.id = p.order_id 
        WHERE o.id = ?
    ");
    $order_stmt->bind_param("i", $order_id);
    $order_stmt->execute();
    $order_result = $order_stmt->get_result();

    if ($order_result->num_rows === 0) {
        return false;
    }

    $order_data = $order_result->fetch_assoc();
    $result = $order_data;

    // Get customer details
    $user_stmt = $conn->prepare("
        SELECT name, email, phone 
        FROM users 
        WHERE id = ?
    ");
    $user_stmt->bind_param("i", $order_data['buyer_id']);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();

    if ($user_result->num_rows > 0) {
        $user_data = $user_result->fetch_assoc();
        $result['customer_name'] = $user_data['name'];
        $result['customer_email'] = $user_data['email'];
        $result['customer_phone'] = $user_data['phone'] ?? 'N/A';
    } else {
        $result['customer_name'] = 'Customer';
        $result['customer_email'] = 'customer@example.com';
        $result['customer_phone'] = 'N/A';
    }

    // Get order items
    $items_stmt = $conn->prepare("
        SELECT oi.*, p.name as product_name, p.unit 
        FROM order_items oi 
        JOIN products p ON oi.product_id = p.id 
        WHERE oi.order_id = ?
    ");
    $items_stmt->bind_param("i", $order_id);
    $items_stmt->execute();
    $items_result = $items_stmt->get_result();

    $result['items'] = array();
    while ($item = $items_result->fetch_assoc()) {
        $result['items'][] = $item;
    }

    return $result;
}

// Function to send invoice email
function sendInvoiceEmail($order_id, $pdf_path)
{
    global $conn;

    // Get order details for email
    $order_data = getOrderDetails($order_id);

    if (!$order_data) {
        return false;
    }

    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'fahimtalha79@gmail.com';                     // Change this to your Gmail address
        $mail->Password   = 'hswjveecysxdnesl';                        // Change this to your Gmail app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom('noreply@agrokartbd.com', 'AgroKart BD');
        $mail->addAddress($order_data['customer_email'], $order_data['customer_name']);

        // Attach the invoice PDF
        $mail->addAttachment($pdf_path, 'AgroKart-Invoice-' . $order_id . '.pdf');

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'AgroKart BD - Order #AGR-' . $order_id . ' Confirmation';

        // Calculate total amount
        $total = 0;
        foreach ($order_data['items'] as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        if (isset($order_data['delivery_fee'])) {
            $total += $order_data['delivery_fee'];
        }

        // Build email HTML body
        $mail->Body = "
        <html>
        <head>
            <title>Order Confirmation - AgroKart BD</title>
        </head>
        <body>
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
                <div style='text-align: center; margin-bottom: 30px;'>
                    <h2 style='color: #2c5530; margin-bottom: 10px;'>AgroKart BD</h2>
                    <p style='color: #666; font-size: 14px;'>Fresh From Farm</p>
                </div>
                
                <h3 style='color: #333; margin-bottom: 20px;'>Order Confirmation</h3>
                
                <p style='color: #555; line-height: 1.6; margin-bottom: 20px;'>
                    Hi " . htmlspecialchars($order_data['customer_name']) . ",
                </p>
                
                <p style='color: #555; line-height: 1.6; margin-bottom: 20px;'>
                    Thank you for your order! We're pleased to confirm that your order has been received and is being processed.
                </p>
                
                <div style='background-color: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px;'>
                    <h4 style='margin-top: 0; color: #2c5530;'>Order Summary</h4>
                    <p><strong>Order Number:</strong> AGR-" . $order_id . "</p>
                    <p><strong>Order Date:</strong> " . date('F j, Y, g:i a', strtotime(isset($order_data['order_date']) ? $order_data['order_date'] : date('Y-m-d H:i:s'))) . "</p>
                    <p><strong>Payment Method:</strong> " . $order_data['payment_method'] . "</p>
                    <p><strong>Order Total:</strong> ৳" . number_format($total, 2) . "</p>
                </div>
                
                <p style='color: #555; line-height: 1.6; margin-bottom: 20px;'>
                    You can view your order details in the attached invoice PDF.
                </p>
                
                <p style='color: #555; line-height: 1.6; margin-bottom: 20px;'>
                    If you have any questions about your order, please contact our customer service at support@agrokartbd.com.
                </p>
                
                <div style='border-top: 1px solid #ddd; padding-top: 20px; text-align: center;'>
                    <p style='color: #888; font-size: 12px; margin-bottom: 5px;'>
                        This is an automated message from AgroKart BD
                    </p>
                    <p style='color: #888; font-size: 12px;'>
                        Connecting farmers directly to consumers
                    </p>
                </div>
            </div>
        </body>
        </html>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("PHPMailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
