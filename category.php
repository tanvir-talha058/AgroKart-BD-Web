<?php
// FILE: category.php

// Get the category from URL parameter
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$category_name = '';

// Validate category
$valid_categories = ['Vegetable', 'Fruit', 'Spice'];
if (!in_array($category, $valid_categories)) {
    header('Location: index.php');
    exit;
}


// Include header after checking for redirect
include 'includes/header.php';

// Set display name for category
switch ($category) {
    case 'Vegetable':
        $category_name = 'Vegetables';
        break;
    case 'Fruit':
        $category_name = 'Fruits';
        break;
    case 'Spice':
        $category_name = 'Spices';
        break;
}
?>

<section class="category-header">
    <div class="container">
        <h1><?php echo htmlspecialchars($category_name); ?></h1>
        <p>Fresh <?php echo strtolower($category_name); ?> delivered to your doorstep</p>
    </div>
</section>

<section class="product-section">
    <div class="container">
        <div class="product-row">
            <?php
            // First, check if hot_deals table exists
            $hot_deals_exists = false;
            $table_check = $conn->query("SHOW TABLES LIKE 'hot_deals'");
            if ($table_check && $table_check->num_rows > 0) {
                $hot_deals_exists = true;
            }

            // Fetch products by category, excluding those in hot deals
            if ($hot_deals_exists) {
                $sql = "SELECT p.id, p.name, p.price, p.unit, p.quantity, p.display_unit, p.image_path, p.stock, p.description 
                        FROM products p 
                        LEFT JOIN hot_deals hd ON p.id = hd.product_id AND hd.is_active = 1 
                        WHERE p.category = ? AND p.stock > 0 AND hd.product_id IS NULL 
                        ORDER BY p.created_at DESC";
            } else {
                // If hot_deals table doesn't exist, use the original query
                $sql = "SELECT id, name, price, unit, quantity, display_unit, image_path, stock, description 
                        FROM products WHERE category = ? AND stock > 0 ORDER BY created_at DESC";
            }

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $category);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    // Use display_unit from the database, or format it if not set
                    $price_unit = ' for ' . (isset($row["display_unit"]) ? $row["display_unit"] : (isset($row["quantity"]) ? $row["quantity"] . ' ' . $row["unit"] : $row["unit"]));

                    echo '<div class="product-card">';
                    echo '<a href="product_details.php?id=' . $row["id"] . '">';
                    echo '<img src="' . htmlspecialchars($row["image_path"]) . '" alt="' . htmlspecialchars($row["name"]) . '">';
                    echo '<h4>' . htmlspecialchars($row["name"]) . '</h4>';
                    echo '</a>';
                    echo '<p><span class="price">৳' . htmlspecialchars($row["price"]) . '<span class="price-unit">' . $price_unit . '</span></span></p>';
                    echo '<form action="php/cart_manager.php" method="POST">';
                    echo '<input type="hidden" name="product_id" value="' . $row["id"] . '">';
                    echo '<input type="hidden" name="action" value="add">';
                    echo '<button type="submit" class="add-to-cart-btn">Add to Cart</button>';
                    echo '</form>';
                    echo '</div>';
                }
            } else {
                echo '<div class="no-products">';
                echo '<p>No ' . strtolower($category_name) . ' available at the moment.</p>';
                echo '<a href="index.php" class="back-to-home">Back to All Products</a>';
                echo '</div>';
            }
            $stmt->close();
            ?>
        </div>
    </div>
</section>

<link rel="stylesheet" href="css/category-style.css">

<script>
    // Set category dropdown to current category
    document.addEventListener('DOMContentLoaded', function() {
        const categorySelect = document.getElementById('categorySelect');
        if (categorySelect) {
            categorySelect.value = '<?php echo htmlspecialchars($category); ?>';
        }
    });
</script>

<?php
include 'includes/footer.php';
$conn->close();
?>