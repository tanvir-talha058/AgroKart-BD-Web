<?php
// FILE: my_orders.php
include 'includes/header.php';
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}
$buyer_id = $_SESSION['user_id'];
?>
<div class="orders-container">
    <h1>My Orders</h1>
    <?php
    $sql = "SELECT * FROM orders WHERE buyer_id = ? ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $buyer_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($order = $result->fetch_assoc()) {
            echo '<div class="order-card">';
            echo '<div class="order-card-header">';
            echo '<span>Order ID: #' . $order['id'] . '</span>';
            echo '<span>Date: ' . date('d M Y', strtotime($order['created_at'])) . '</span>';
            echo '</div>';
            echo '<div class="order-card-body">';
            echo '<p><strong>Total:</strong> ৳' . number_format($order['total_amount'], 2) . '</p>';
            echo '<p><strong>Status:</strong> <span class="status-' . strtolower($order['status']) . '">' . $order['status'] . '</span></p>';
            echo '</div>';
            echo '</div>';
        }
    } else {
        echo '<p>You have not placed any orders yet.</p>';
    }
    $stmt->close();
    ?>
</div>
<?php include 'includes/footer.php'; ?>