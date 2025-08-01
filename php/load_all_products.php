<?php
// FILE: php/load_all_products.php
require_once '../includes/db_connect.php';

// Fetch all products (no limit)
$sql = "SELECT id, name, price, image_path, stock FROM products WHERE stock > 0 ORDER BY created_at DESC";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo '<div class="product-card">';
        echo '<a href="product_details.php?id=' . $row["id"] . '">';
        echo '<img src="' . htmlspecialchars($row["image_path"]) . '" alt="' . htmlspecialchars($row["name"]) . '">';
        echo '<h4>' . htmlspecialchars($row["name"]) . '</h4>';
        echo '</a>';
        echo '<p><span class="price">৳' . htmlspecialchars($row["price"]) . '</span></p>';
        echo '<form action="php/cart_manager.php" method="POST">';
        echo '<input type="hidden" name="product_id" value="' . $row["id"] . '">';
        echo '<input type="hidden" name="action" value="add">';
        echo '<button type="submit" class="add-to-cart-btn">Add to Cart</button>';
        echo '</form>';
        echo '</div>';
    }
} else {
    echo "<p>No products available at the moment.</p>";
}

$conn->close();
