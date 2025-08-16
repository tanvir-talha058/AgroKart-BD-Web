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

        // Set margins - wider margins for better layout
        $pdf->SetMargins(15, 15, 15);

        // Set auto page breaks
        $pdf->SetAutoPageBreak(true, 25);

        // Add a page
        $pdf->AddPage();

        // Define colors
        $primaryColor = array(76, 175, 80); // Green
        $secondaryColor = array(44, 62, 80); // Dark blue
        $lightGray = array(248, 249, 250); // Light gray background

        // Set color for heading
        $pdf->SetFillColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
        $pdf->Rect(0, 0, $pdf->getPageWidth(), 30, 'F');

        // Company name
        $pdf->SetFont('helvetica', 'B', 22);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(0, 15, 'AgroKart BD', 0, 0, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Ln(8);

        // Tagline
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 10, 'Fresh Products, Direct from Farm', 0, 1, 'L');
        $pdf->Ln(5);

        // Invoice title with a cleaner design
        $pdf->SetTextColor($secondaryColor[0], $secondaryColor[1], $secondaryColor[2]);
        $pdf->SetFont('helvetica', 'B', 20);
        $pdf->Cell(140, 10, 'PAYMENT RECEIPT', 0, 0, 'L');

        // Success indicator - using cell for better positioning
        $pdf->SetFillColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(40, 10, 'CONFIRMED ✓', 0, 1, 'C', 1);
        $pdf->Ln(5);

        // Order information section
        $pdf->SetFillColor($lightGray[0], $lightGray[1], $lightGray[2]);
        $pdf->SetTextColor($secondaryColor[0], $secondaryColor[1], $secondaryColor[2]);
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, 'Order Information', 0, 1, 'L', 1);

        // Order info content
        $pdf->SetFont('helvetica', '', 11);
        $pdf->Cell(40, 8, 'Order ID:', 0, 0, 'L');
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(70, 8, '#AGR-' . $order_id, 0, 0, 'L');

        // Order Date
        $pdf->SetFont('helvetica', '', 11);
        $pdf->Cell(30, 8, 'Order Date:', 0, 0, 'L');

        // Use current date if order_date is not available
        $order_date = isset($order_data['order_date']) ? $order_data['order_date'] : date('Y-m-d H:i:s');
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(0, 8, date('F j, Y, g:i a', strtotime($order_date)), 0, 1, 'L');
        $pdf->Ln(5);

        // Customer information section
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->SetFillColor($lightGray[0], $lightGray[1], $lightGray[2]);
        $pdf->Cell(0, 10, 'Customer Information', 0, 1, 'L', 1);

        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(25, 7, 'Customer:', 0, 0, 'L');
        $pdf->SetFont('helvetica', '', 11);
        $pdf->Cell(155, 7, $order_data['customer_name'], 0, 1, 'L');

        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(25, 7, 'Email:', 0, 0, 'L');
        $pdf->SetFont('helvetica', '', 11);
        $pdf->Cell(155, 7, $order_data['customer_email'], 0, 1, 'L');

        if (isset($order_data['customer_phone'])) {
            $pdf->SetFont('helvetica', 'B', 11);
            $pdf->Cell(25, 7, 'Phone:', 0, 0, 'L');
            $pdf->SetFont('helvetica', '', 11);
            $pdf->Cell(155, 7, $order_data['customer_phone'], 0, 1, 'L');
        }
        $pdf->Ln(5);

        // Order summary section
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->SetFillColor($lightGray[0], $lightGray[1], $lightGray[2]);
        $pdf->Cell(0, 10, 'Order Summary', 0, 1, 'L', 1);

        // Table header - with better spacing and alignment
        $pdf->SetFillColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('helvetica', 'B', 11);

        // Table header row
        $pdf->Cell(90, 10, 'Product', 1, 0, 'L', 1);
        $pdf->Cell(25, 10, 'Quantity', 1, 0, 'C', 1);
        $pdf->Cell(30, 10, 'Price', 1, 0, 'R', 1);
        $pdf->Cell(35, 10, 'Total', 1, 1, 'R', 1);

        // Table rows - with proper borders and alignment
        $pdf->SetTextColor($secondaryColor[0], $secondaryColor[1], $secondaryColor[2]);
        $pdf->SetFont('helvetica', '', 11);
        $total = 0;

        // Loop through items with better formatting
        foreach ($order_data['items'] as $item) {
            $item_total = $item['price'] * $item['quantity'];
            $total += $item_total;

            // Handle long product names with cell instead of text
            $pdf->Cell(90, 10, substr($item['product_name'], 0, 35), 1, 0, 'L');
            $pdf->Cell(25, 10, $item['quantity'], 1, 0, 'C');
            $pdf->Cell(30, 10, '৳' . number_format($item['price'], 2), 1, 0, 'R');
            $pdf->Cell(35, 10, '৳' . number_format($item_total, 2), 1, 1, 'R');
        }

        // Add delivery fee if present
        if ($order_data['delivery_fee'] > 0) {
            $pdf->Cell(145, 10, 'Delivery Fee', 1, 0, 'R');
            $pdf->Cell(35, 10, '৳' . number_format($order_data['delivery_fee'], 2), 1, 1, 'R');
            $total += $order_data['delivery_fee'];
        }

        // Total with better formatting
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->SetFillColor(240, 240, 240);
        $pdf->Cell(145, 10, 'Total', 1, 0, 'R', 1);
        $pdf->Cell(35, 10, '৳' . number_format($total, 2), 1, 1, 'R', 1);
        $pdf->Ln(5);

        // Payment information section with better formatting
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->SetFillColor($lightGray[0], $lightGray[1], $lightGray[2]);
        $pdf->SetTextColor($secondaryColor[0], $secondaryColor[1], $secondaryColor[2]);
        $pdf->Cell(0, 10, 'Payment Information', 0, 1, 'L', 1);

        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(40, 8, 'Payment Method:', 0, 0, 'L');
        $pdf->SetFont('helvetica', '', 11);
        $pdf->Cell(140, 8, $order_data['payment_method'], 0, 1, 'L');

        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(40, 8, 'Payment Status:', 0, 0, 'L');
        $pdf->SetFont('helvetica', '', 11);
        $pdf->Cell(140, 8, $order_data['status'] ?? 'Pending', 0, 1, 'L');

        if (isset($order_data['transaction_id'])) {
            $pdf->SetFont('helvetica', 'B', 11);
            $pdf->Cell(40, 8, 'Transaction ID:', 0, 0, 'L');
            $pdf->SetFont('helvetica', '', 11);
            $pdf->Cell(140, 8, $order_data['transaction_id'], 0, 1, 'L');
        }

        // Add PAID watermark
        $pdf->SetTextColor(230, 230, 230);
        $pdf->SetFont('helvetica', 'B', 60);
        $pdf->StartTransform();
        $pdf->Rotate(45, 105, 120);
        $pdf->Text(70, 120, 'PAID');
        $pdf->StopTransform();

        // Footer with clean design
        $pdf->SetY(-30); // Position at 30mm from bottom
        $pdf->SetFillColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
        $pdf->Rect(0, $pdf->GetY(), $pdf->getPageWidth(), 30, 'F');

        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(0, 10, 'Thank you for choosing AgroKart BD!', 0, 1, 'C');

        $pdf->SetFont('helvetica', '', 9);
        $pdf->Cell(0, 5, 'For support: support@agrokartbd.com | +880-XXXXXXXXX', 0, 1, 'C');        // Save PDF to a file
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

        // Build email HTML body with improved design
        $mail->Body = "
        <html>
        <head>
            <title>Order Confirmation - AgroKart BD</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 0; padding: 0; color: #333; }
                .container { max-width: 600px; margin: 0 auto; }
                .header { background-color: #4CAF50; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
                .content { padding: 20px; background-color: white; border: 1px solid #ddd; border-top: none; border-radius: 0 0 5px 5px; }
                .footer { background-color: #f9f9f9; padding: 15px; text-align: center; border-top: 1px solid #ddd; font-size: 12px; color: #777; }
                h1 { color: white; margin: 0; font-size: 24px; }
                h2 { color: #4CAF50; margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 10px; }
                .tagline { color: rgba(255,255,255,0.8); margin-top: 5px; font-size: 14px; }
                .section { margin-bottom: 25px; }
                .summary { background-color: #f8f9fa; padding: 15px; border-radius: 5px; border-left: 4px solid #4CAF50; }
                .summary p { margin: 8px 0; }
                table { width: 100%; border-collapse: collapse; margin: 15px 0; }
                th { background-color: #4CAF50; color: white; text-align: left; padding: 10px; }
                td { padding: 10px; border-bottom: 1px solid #eee; }
                .total-row td { font-weight: bold; border-top: 2px solid #4CAF50; border-bottom: none; }
                .btn { display: inline-block; background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-top: 15px; }
                .highlight { background-color: #fcf8e3; border: 1px solid #faebcc; border-radius: 3px; padding: 15px; margin: 20px 0; }
                .status { display: inline-block; background-color: #4CAF50; color: white; padding: 3px 10px; border-radius: 15px; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>AgroKart BD</h1>
                    <div class='tagline'>Fresh Products, Direct from Farm</div>
                </div>
                
                <div class='content'>
                    <h2>Order Confirmation <span class='status'>Confirmed</span></h2>
                    
                    <div class='section'>
                        <p>Hi " . htmlspecialchars($order_data['customer_name']) . ",</p>
                        <p>Thank you for your order! We're pleased to confirm that your order has been received and is being processed.</p>
                    </div>
                    
                    <div class='section summary'>
                        <h3 style='margin-top: 0; color: #4CAF50;'>Order Summary</h3>
                        <p><strong>Order Number:</strong> AGR-" . $order_id . "</p>
                        <p><strong>Order Date:</strong> " . date('F j, Y, g:i a', strtotime(isset($order_data['order_date']) ? $order_data['order_date'] : date('Y-m-d H:i:s'))) . "</p>
                        <p><strong>Payment Method:</strong> " . $order_data['payment_method'] . "</p>
                        <p><strong>Order Total:</strong> ৳" . number_format($total, 2) . "</p>
                    </div>
                    
                    <div class='section'>
                        <p>You can view your complete order details in the attached invoice PDF. Please keep this receipt for your records.</p>
                        <p>If you have any questions about your order, please contact our customer service at <a href='mailto:support@agrokartbd.com'>support@agrokartbd.com</a>.</p>
                    </div>
                    
                    <div class='highlight'>
                        <p style='margin-top: 0;'><strong>What happens next?</strong></p>
                        <p style='margin-bottom: 0;'>Your order is being processed and will be delivered according to your selected delivery option. You can check your order status anytime by logging into your account.</p>
                    </div>
                    
                    <div style='text-align: center;'>
                        <a href='http://agrokartbd.com/my_orders.php' class='btn'>View Your Orders</a>
                    </div>
                </div>
                
                <div class='footer'>
                    <p>This is an automated message from AgroKart BD</p>
                    <p>Connecting farmers directly to consumers</p>
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
