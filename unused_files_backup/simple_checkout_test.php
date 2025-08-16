<!DOCTYPE html>
<html>
<head>
    <title>Simple Form Test</title>
</head>
<body>
    <?php
    session_start();
    
    // Set up minimal session data for testing
    $_SESSION['loggedin'] = true;
    $_SESSION['user_role'] = 'Buyer';
    $_SESSION['user_id'] = 1;
    $_SESSION['cart'] = [1 => ['price' => 50, 'quantity' => 1]];
    ?>
    
    <h1>Simple Checkout Test</h1>
    
    <form action="php/order_process.php" method="POST">
        <p>
            <label>Address:</label><br>
            <input type="text" name="location" value="Test Address" required>
        </p>
        
        <p>
            <label>Payment:</label><br>
            <input type="radio" name="payment_method" value="Cash on Delivery" checked required> Cash on Delivery
        </p>
        
        <p>
            <input type="checkbox" id="terms" required> I agree to terms
        </p>
        
        <p>
            <button type="submit">Submit Order</button>
        </p>
    </form>
</body>
</html>
