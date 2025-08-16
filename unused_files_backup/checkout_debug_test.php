<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout Debug Test</title>
</head>
<body>
    <h1>Checkout Form Test</h1>
    
    <?php
    session_start();
    
    // Simulate a logged-in buyer with cart items for testing
    $_SESSION['loggedin'] = true;
    $_SESSION['user_role'] = 'Buyer';
    $_SESSION['user_id'] = 1;
    $_SESSION['user_location'] = 'Test Location';
    $_SESSION['cart'] = [
        1 => ['price' => 50, 'quantity' => 2, 'name' => 'Test Product']
    ];
    
    echo "<h2>Session Data:</h2>";
    echo "<pre>" . print_r($_SESSION, true) . "</pre>";
    ?>
    
    <form action="php/order_process.php" method="POST" id="testForm">
        <h3>Test Checkout Form</h3>
        
        <label for="location">Delivery Address:</label><br>
        <textarea name="location" id="location" required>Test Address, Dhaka, Bangladesh</textarea><br><br>
        
        <label for="payment_method">Payment Method:</label><br>
        <input type="radio" name="payment_method" value="Cash on Delivery" id="cod" required checked>
        <label for="cod">Cash on Delivery</label><br><br>
        
        <label for="order_notes">Order Notes (Optional):</label><br>
        <textarea name="order_notes" id="order_notes">Test order notes</textarea><br><br>
        
        <input type="checkbox" id="terms" required>
        <label for="terms">I agree to terms and conditions</label><br><br>
        
        <button type="submit">Place Test Order</button>
    </form>
    
    <script>
        document.getElementById('testForm').addEventListener('submit', function(e) {
            console.log('Form submission triggered');
            const formData = new FormData(this);
            console.log('Form data:', Object.fromEntries(formData));
        });
    </script>
</body>
</html>
