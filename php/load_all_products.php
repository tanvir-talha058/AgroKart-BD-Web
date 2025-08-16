<?php
// FILE: php/load_all_products.php
require_once '../includes/db_connect.php';

// Fetch all products (no limit)
$sql = "SELECT id, name, price, unit, quantity, display_unit, image_path, stock, category FROM products WHERE stock > 0 ORDER BY created_at DESC";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $category_class = strtolower($row["category"]);

        // Use display_unit from the database, or format it if not set
        $price_unit = ' for ' . (isset($row["display_unit"]) ? $row["display_unit"] : (isset($row["quantity"]) ? $row["quantity"] . ' ' . $row["unit"] : $row["unit"]));
        echo '<div class="product-card" data-category="' . $category_class . '">';
        echo '<div class="product-image-container">';
        echo '<img src="' . htmlspecialchars($row["image_path"]) . '" alt="' . htmlspecialchars($row["name"]) . '" class="product-image">';
        echo '<div class="product-overlay">';
        echo '<div class="product-actions">';
        echo '<a href="product_details.php?id=' . $row["id"] . '" class="action-btn view-btn"><i class="fas fa-eye"></i></a>';
        echo '</div>';
        echo '</div>';
        echo '<div class="category-badge ' . $category_class . '">' . htmlspecialchars($row["category"]) . '</div>';
        if ($row["stock"] <= 5) {
            echo '<div class="stock-badge limited-stock"><i class="fas fa-exclamation-triangle"></i> Limited Stock</div>';
        }
        echo '</div>';
        echo '<div class="product-info">';
        echo '<h4 class="product-title">' . htmlspecialchars($row["name"]) . '</h4>';
        echo '<div class="product-meta">';
        echo '<span class="price">৳' . htmlspecialchars($row["price"]) . '<span class="price-unit">' . $price_unit . '</span></span>';
        echo '</div>';
        echo '</div>';
        echo '<form action="php/cart_manager.php" method="POST" class="product-form">';
        echo '<input type="hidden" name="product_id" value="' . $row["id"] . '">';
        echo '<input type="hidden" name="action" value="add">';
        echo '<button type="submit" class="add-to-cart-btn">';
        echo '<i class="fas fa-shopping-cart"></i>';
        echo '<span>Add to Cart</span>';
        echo '</button>';
        echo '</form>';
        echo '</div>';
    }
} else {
    echo '<div class="no-products">';
    echo '<i class="fas fa-seedling"></i>';
    echo '<h3>No products available</h3>';
    echo '<p>Check back soon for fresh arrivals!</p>';
    echo '</div>';
}

$conn->close();
